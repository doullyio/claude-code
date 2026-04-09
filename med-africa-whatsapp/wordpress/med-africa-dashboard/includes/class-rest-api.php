<?php
/**
 * REST API Endpoints
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedAfrica_REST_API {

    private MedAfrica_Google_Sheets $sheets;
    private string $namespace = 'medafrica/v1';

    public function __construct(MedAfrica_Google_Sheets $sheets) {
        $this->sheets = $sheets;
    }

    /**
     * Register REST API routes
     */
    public function register_routes(): void {
        // GET /complaints - list with filters
        register_rest_route($this->namespace, '/complaints', [
            'methods' => 'GET',
            'callback' => [$this, 'get_complaints'],
            'permission_callback' => [$this, 'check_admin_permission'],
            'args' => [
                'search' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'type' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'category' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'priority' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'status' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'city' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'date_from' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'date_to' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'page' => ['type' => 'integer', 'default' => 1, 'minimum' => 1],
                'per_page' => ['type' => 'integer', 'default' => 25, 'minimum' => 1, 'maximum' => 100],
                'sort' => ['type' => 'string', 'default' => 'date', 'sanitize_callback' => 'sanitize_text_field'],
                'order' => ['type' => 'string', 'default' => 'desc', 'enum' => ['asc', 'desc']],
            ],
        ]);

        // GET /complaints/{id} - single complaint
        register_rest_route($this->namespace, '/complaints/(?P<id>[A-Za-z0-9\-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_complaint'],
            'permission_callback' => [$this, 'check_admin_permission'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // PUT /complaints/{id} - update status/assignee
        register_rest_route($this->namespace, '/complaints/(?P<id>[A-Za-z0-9\-]+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'update_complaint'],
            'permission_callback' => [$this, 'check_admin_permission'],
            'args' => [
                'id' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'status' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'priority' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'assignee' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'notes' => ['type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field'],
            ],
        ]);

        // GET /stats - KPI numbers
        register_rest_route($this->namespace, '/stats', [
            'methods' => 'GET',
            'callback' => [$this, 'get_stats'],
            'permission_callback' => [$this, 'check_admin_permission'],
        ]);

        // POST /webhook/sheets - receive update from n8n
        register_rest_route($this->namespace, '/webhook/sheets', [
            'methods' => 'POST',
            'callback' => [$this, 'webhook_sheets'],
            'permission_callback' => '__return_true', // Public endpoint for n8n
        ]);
    }

    /**
     * Check admin permission
     */
    public function check_admin_permission(): bool {
        return current_user_can('manage_options');
    }

    /**
     * GET /complaints
     */
    public function get_complaints(WP_REST_Request $request): WP_REST_Response {
        $filters = [
            'search' => $request->get_param('search'),
            'type' => $request->get_param('type'),
            'category' => $request->get_param('category'),
            'priority' => $request->get_param('priority'),
            'status' => $request->get_param('status'),
            'city' => $request->get_param('city'),
            'date_from' => $request->get_param('date_from'),
            'date_to' => $request->get_param('date_to'),
        ];

        // Remove empty filters
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        $all_complaints = $this->sheets->get_complaints($filters);

        // Sorting
        $sort_field = $request->get_param('sort') ?: 'date';
        $sort_order = $request->get_param('order') ?: 'desc';

        usort($all_complaints, function ($a, $b) use ($sort_field, $sort_order) {
            $va = $a[$sort_field] ?? '';
            $vb = $b[$sort_field] ?? '';
            $cmp = strcmp($va, $vb);
            return $sort_order === 'desc' ? -$cmp : $cmp;
        });

        // Pagination
        $page = max(1, (int) $request->get_param('page'));
        $per_page = (int) $request->get_param('per_page') ?: 25;
        $total = count($all_complaints);
        $total_pages = max(1, ceil($total / $per_page));
        $offset = ($page - 1) * $per_page;

        $complaints = array_slice($all_complaints, $offset, $per_page);

        $response = new WP_REST_Response([
            'data' => $complaints,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $total,
                'total_pages' => $total_pages,
            ],
        ]);

        $response->header('X-WP-Total', $total);
        $response->header('X-WP-TotalPages', $total_pages);

        return $response;
    }

    /**
     * GET /complaints/{id}
     */
    public function get_complaint(WP_REST_Request $request): WP_REST_Response {
        $id = $request->get_param('id');
        $complaint = $this->sheets->get_complaint($id);

        if (!$complaint) {
            return new WP_REST_Response(['message' => 'لم يتم العثور على الشكوى'], 404);
        }

        return new WP_REST_Response(['data' => $complaint]);
    }

    /**
     * PUT /complaints/{id}
     */
    public function update_complaint(WP_REST_Request $request): WP_REST_Response {
        $id = $request->get_param('id');

        $data = [];
        if ($request->has_param('status')) $data['status'] = $request->get_param('status');
        if ($request->has_param('priority')) $data['priority'] = $request->get_param('priority');
        if ($request->has_param('assignee')) $data['assignee'] = $request->get_param('assignee');
        if ($request->has_param('notes')) $data['notes'] = $request->get_param('notes');

        if (empty($data)) {
            return new WP_REST_Response(['message' => 'لا توجد بيانات للتحديث'], 400);
        }

        $success = $this->sheets->update_complaint($id, $data);

        if (!$success) {
            return new WP_REST_Response(['message' => 'فشل تحديث الشكوى'], 500);
        }

        return new WP_REST_Response([
            'message' => 'تم التحديث بنجاح',
            'data' => $this->sheets->get_complaint($id),
        ]);
    }

    /**
     * GET /stats
     */
    public function get_stats(WP_REST_Request $request): WP_REST_Response {
        return new WP_REST_Response(['data' => $this->sheets->get_stats()]);
    }

    /**
     * POST /webhook/sheets - receives new complaint data from n8n
     */
    public function webhook_sheets(WP_REST_Request $request): WP_REST_Response {
        $body = $request->get_json_params();

        if (empty($body)) {
            return new WP_REST_Response(['message' => 'بيانات فارغة'], 400);
        }

        // Map incoming data to sheet columns
        $data = [
            'ticket_id' => sanitize_text_field($body['ticketId'] ?? $body['ticket_id'] ?? ''),
            'date' => sanitize_text_field($body['date'] ?? date('Y-m-d')),
            'time' => sanitize_text_field($body['time'] ?? date('H:i')),
            'first_name' => sanitize_text_field($body['firstName'] ?? $body['first_name'] ?? ''),
            'last_name' => sanitize_text_field($body['lastName'] ?? $body['last_name'] ?? ''),
            'city' => sanitize_text_field($body['city'] ?? ''),
            'phone' => sanitize_text_field($body['phoneNumber'] ?? $body['phone'] ?? ''),
            'tracking' => sanitize_text_field($body['trackingNumber'] ?? $body['tracking'] ?? ''),
            'type' => sanitize_text_field($body['type'] ?? ''),
            'category' => sanitize_text_field($body['category'] ?? ''),
            'priority' => sanitize_text_field($body['priority'] ?? 'عادي'),
            'status' => sanitize_text_field($body['status'] ?? 'جديد'),
            'description' => sanitize_textarea_field($body['description'] ?? $body['summary'] ?? ''),
            'assignee' => sanitize_text_field($body['assignee'] ?? ''),
            'notes' => sanitize_textarea_field($body['notes'] ?? ''),
        ];

        // Check for urgent complaints and trigger notification
        if ($data['priority'] === 'عاجل') {
            $notifications = new MedAfrica_Notifications();
            $notifications->send_urgent_alert($data);
        }

        return new WP_REST_Response([
            'message' => 'تم الاستلام بنجاح',
            'ticket_id' => $data['ticket_id'],
        ], 201);
    }
}
