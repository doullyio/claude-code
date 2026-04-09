<?php
/**
 * Google Sheets API Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedAfrica_Google_Sheets {

    private string $api_key;
    private string $sheet_id;
    private string $sheet_name = 'الشكاوي';
    private string $base_url = 'https://sheets.googleapis.com/v4/spreadsheets';

    public function __construct() {
        $this->api_key = get_option('medafrica_sheets_api_key', '');
        $this->sheet_id = get_option('medafrica_sheets_id', '');
    }

    /**
     * Check if API credentials are configured
     */
    public function is_configured(): bool {
        return !empty($this->api_key) && !empty($this->sheet_id);
    }

    /**
     * Test connection to Google Sheets
     */
    public function test_connection(): array {
        if (!$this->is_configured()) {
            return ['success' => false, 'message' => 'لم يتم تكوين مفاتيح API'];
        }

        $url = sprintf(
            '%s/%s?key=%s&fields=properties.title',
            $this->base_url,
            $this->sheet_id,
            $this->api_key
        );

        $response = wp_remote_get($url, ['timeout' => 15]);

        if (is_wp_error($response)) {
            return ['success' => false, 'message' => $response->get_error_message()];
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            return [
                'success' => false,
                'message' => $body['error']['message'] ?? 'خطأ في الاتصال (HTTP ' . $code . ')',
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return [
            'success' => true,
            'message' => 'تم الاتصال بنجاح: ' . ($body['properties']['title'] ?? ''),
        ];
    }

    /**
     * Fetch all complaints from Google Sheets
     */
    public function get_complaints(array $filters = []): array {
        if (!$this->is_configured()) {
            return [];
        }

        $range = urlencode($this->sheet_name . '!A:O');
        $url = sprintf(
            '%s/%s/values/%s?key=%s&majorDimension=ROWS',
            $this->base_url,
            $this->sheet_id,
            $range,
            $this->api_key
        );

        $response = wp_remote_get($url, ['timeout' => 30]);

        if (is_wp_error($response)) {
            return [];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $rows = $body['values'] ?? [];

        if (count($rows) < 2) {
            return [];
        }

        // First row is headers
        $headers = $rows[0];
        $complaints = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $complaint = [];

            // Map columns
            $complaint['ticket_id'] = $row[0] ?? '';
            $complaint['date'] = $row[1] ?? '';
            $complaint['time'] = $row[2] ?? '';
            $complaint['first_name'] = $row[3] ?? '';
            $complaint['last_name'] = $row[4] ?? '';
            $complaint['city'] = $row[5] ?? '';
            $complaint['phone'] = $row[6] ?? '';
            $complaint['tracking'] = $row[7] ?? '';
            $complaint['type'] = $row[8] ?? '';
            $complaint['category'] = $row[9] ?? '';
            $complaint['priority'] = $row[10] ?? '';
            $complaint['status'] = $row[11] ?? '';
            $complaint['description'] = $row[12] ?? '';
            $complaint['assignee'] = $row[13] ?? '';
            $complaint['notes'] = $row[14] ?? '';
            $complaint['row_index'] = $i + 1; // 1-based, accounting for header

            // Apply filters
            if (!empty($filters['search'])) {
                $search = mb_strtolower($filters['search']);
                $searchable = mb_strtolower(implode(' ', [
                    $complaint['ticket_id'],
                    $complaint['first_name'],
                    $complaint['last_name'],
                    $complaint['phone'],
                    $complaint['tracking'],
                ]));
                if (mb_strpos($searchable, $search) === false) {
                    continue;
                }
            }

            if (!empty($filters['type']) && $complaint['type'] !== $filters['type']) continue;
            if (!empty($filters['category']) && $complaint['category'] !== $filters['category']) continue;
            if (!empty($filters['priority']) && $complaint['priority'] !== $filters['priority']) continue;
            if (!empty($filters['status']) && $complaint['status'] !== $filters['status']) continue;
            if (!empty($filters['city']) && $complaint['city'] !== $filters['city']) continue;
            if (!empty($filters['date_from']) && $complaint['date'] < $filters['date_from']) continue;
            if (!empty($filters['date_to']) && $complaint['date'] > $filters['date_to']) continue;

            $complaints[] = $complaint;
        }

        // Sort by date descending (newest first)
        usort($complaints, function ($a, $b) {
            return strcmp($b['date'] . $b['time'], $a['date'] . $a['time']);
        });

        return $complaints;
    }

    /**
     * Get a single complaint by ticket ID
     */
    public function get_complaint(string $ticket_id): ?array {
        $complaints = $this->get_complaints();

        foreach ($complaints as $complaint) {
            if ($complaint['ticket_id'] === $ticket_id) {
                return $complaint;
            }
        }

        return null;
    }

    /**
     * Update a complaint row in Google Sheets
     */
    public function update_complaint(string $ticket_id, array $data): bool {
        if (!$this->is_configured()) {
            return false;
        }

        // Find the row
        $complaint = $this->get_complaint($ticket_id);
        if (!$complaint) {
            return false;
        }

        $row_index = $complaint['row_index'];

        // Build the update values
        $values = [
            $complaint['ticket_id'],
            $complaint['date'],
            $complaint['time'],
            $complaint['first_name'],
            $complaint['last_name'],
            $complaint['city'],
            $complaint['phone'],
            $complaint['tracking'],
            $complaint['type'],
            $complaint['category'],
            $data['priority'] ?? $complaint['priority'],
            $data['status'] ?? $complaint['status'],
            $complaint['description'],
            $data['assignee'] ?? $complaint['assignee'],
            $data['notes'] ?? $complaint['notes'],
        ];

        $range = urlencode($this->sheet_name . '!A' . $row_index . ':O' . $row_index);
        $url = sprintf(
            '%s/%s/values/%s?key=%s&valueInputOption=USER_ENTERED',
            $this->base_url,
            $this->sheet_id,
            $range,
            $this->api_key
        );

        $response = wp_remote_request($url, [
            'method' => 'PUT',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->get_access_token(),
            ],
            'body' => wp_json_encode([
                'range' => $this->sheet_name . '!A' . $row_index . ':O' . $row_index,
                'majorDimension' => 'ROWS',
                'values' => [$values],
            ]),
            'timeout' => 15,
        ]);

        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }

    /**
     * Get access token from service account credentials
     */
    private function get_access_token(): string {
        $credentials_json = get_option('medafrica_google_credentials', '');
        if (empty($credentials_json)) {
            return $this->api_key; // Fallback to API key
        }

        // For service account auth, implement JWT token exchange
        // This is a simplified version - in production use google/apiclient
        return $this->api_key;
    }

    /**
     * Get KPI statistics
     */
    public function get_stats(): array {
        $complaints = $this->get_complaints();

        $today = date('Y-m-d');
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $month_start = date('Y-m-01');

        $stats = [
            'total' => count($complaints),
            'open' => 0,
            'resolved' => 0,
            'urgent' => 0,
            'today' => 0,
            'this_week' => 0,
            'this_month' => 0,
            'by_category' => [],
            'by_city' => [],
            'by_priority' => ['عاجل' => 0, 'مهم' => 0, 'عادي' => 0],
            'by_status' => [],
            'daily_last_7' => [],
            'urgent_unresolved_48h' => [],
        ];

        $category_counts = [];
        $city_counts = [];
        $status_counts = [];
        $daily_counts = [];

        // Initialize last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $daily_counts[$date] = 0;
        }

        foreach ($complaints as $c) {
            // Status counts
            if (!in_array($c['status'], ['محلول', 'مغلق'])) {
                $stats['open']++;
            }
            if ($c['status'] === 'محلول') {
                $stats['resolved']++;
            }
            if ($c['priority'] === 'عاجل') {
                $stats['urgent']++;

                // Check if urgent and unresolved > 48h
                if (!in_array($c['status'], ['محلول', 'مغلق'])) {
                    $complaint_date = strtotime($c['date'] . ' ' . $c['time']);
                    if ($complaint_date && (time() - $complaint_date) > 48 * 3600) {
                        $stats['urgent_unresolved_48h'][] = $c;
                    }
                }
            }

            // Date filters
            if ($c['date'] === $today) $stats['today']++;
            if ($c['date'] >= $week_start) $stats['this_week']++;
            if ($c['date'] >= $month_start) $stats['this_month']++;

            // Category counts
            $cat = $c['category'] ?: 'عام';
            $category_counts[$cat] = ($category_counts[$cat] ?? 0) + 1;

            // City counts
            $city = $c['city'] ?: 'غير محدد';
            $city_counts[$city] = ($city_counts[$city] ?? 0) + 1;

            // Priority counts
            $pri = $c['priority'] ?: 'عادي';
            $stats['by_priority'][$pri] = ($stats['by_priority'][$pri] ?? 0) + 1;

            // Status counts
            $st = $c['status'] ?: 'جديد';
            $status_counts[$st] = ($status_counts[$st] ?? 0) + 1;

            // Daily counts (last 7 days)
            if (isset($daily_counts[$c['date']])) {
                $daily_counts[$c['date']]++;
            }
        }

        arsort($category_counts);
        arsort($city_counts);

        $stats['by_category'] = $category_counts;
        $stats['by_city'] = $city_counts;
        $stats['by_status'] = $status_counts;
        $stats['daily_last_7'] = $daily_counts;

        return $stats;
    }

    /**
     * Append a new complaint row (used by webhook)
     */
    public function append_complaint(array $data): bool {
        if (!$this->is_configured()) {
            return false;
        }

        $range = urlencode($this->sheet_name . '!A:O');
        $url = sprintf(
            '%s/%s/values/%s:append?key=%s&valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS',
            $this->base_url,
            $this->sheet_id,
            $range,
            $this->api_key
        );

        $values = [
            $data['ticket_id'] ?? '',
            $data['date'] ?? date('Y-m-d'),
            $data['time'] ?? date('H:i'),
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['city'] ?? '',
            $data['phone'] ?? '',
            $data['tracking'] ?? '',
            $data['type'] ?? '',
            $data['category'] ?? '',
            $data['priority'] ?? 'عادي',
            $data['status'] ?? 'جديد',
            $data['description'] ?? '',
            $data['assignee'] ?? '',
            $data['notes'] ?? '',
        ];

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->get_access_token(),
            ],
            'body' => wp_json_encode([
                'range' => $this->sheet_name . '!A:O',
                'majorDimension' => 'ROWS',
                'values' => [$values],
            ]),
            'timeout' => 15,
        ]);

        return !is_wp_error($response) && in_array(wp_remote_retrieve_response_code($response), [200, 201]);
    }
}
