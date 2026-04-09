<?php
/**
 * Auto-alerts and Notification Logic
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedAfrica_Notifications {

    /**
     * Send urgent alert via WhatsApp to manager
     */
    public function send_urgent_alert(array $complaint): bool {
        $api_key = get_option('medafrica_dialog360_api_key', '');
        $manager_number = get_option('medafrica_manager_whatsapp', '');

        if (empty($api_key) || empty($manager_number)) {
            return false;
        }

        $message = sprintf(
            "🚨 تنبيه عاجل - Med Africa\n\nتذكرة: %s\nالعميل: %s %s\nالمدينة: %s\nالفئة: %s\nرقم التتبع: %s\nالوصف: %s\n\nالأولوية: عاجل ⚠️\n\nيرجى المتابعة فوراً.",
            $complaint['ticket_id'] ?? '',
            $complaint['first_name'] ?? '',
            $complaint['last_name'] ?? '',
            $complaint['city'] ?? '',
            $complaint['category'] ?? '',
            $complaint['tracking'] ?? '',
            mb_substr($complaint['description'] ?? '', 0, 200)
        );

        $response = wp_remote_post('https://waba.360dialog.io/v1/messages', [
            'headers' => [
                'D360-API-KEY' => $api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode([
                'messaging_product' => 'whatsapp',
                'to' => $manager_number,
                'type' => 'text',
                'text' => ['body' => $message],
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            error_log('Med Africa: WhatsApp alert failed - ' . $response->get_error_message());
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            error_log('Med Africa: WhatsApp alert HTTP error - ' . $code);
            return false;
        }

        return true;
    }

    /**
     * Send important alert via email
     */
    public function send_important_email(array $complaint): bool {
        $manager_email = get_option('admin_email');

        $subject = sprintf(
            '⚠️ شكوى مهمة - %s - %s',
            $complaint['ticket_id'] ?? '',
            $complaint['category'] ?? ''
        );

        $body = sprintf(
            "تفاصيل الشكوى المهمة:\n\n" .
            "رقم التذكرة: %s\n" .
            "التاريخ: %s %s\n\n" .
            "بيانات العميل:\n" .
            "- الاسم: %s %s\n" .
            "- المدينة: %s\n" .
            "- رقم الهاتف: %s\n" .
            "- رقم التتبع: %s\n\n" .
            "تفاصيل الشكوى:\n" .
            "- النوع: %s\n" .
            "- الفئة: %s\n" .
            "- الأولوية: مهم\n" .
            "- الوصف: %s\n\n" .
            "يرجى المتابعة في أقرب وقت.",
            $complaint['ticket_id'] ?? '',
            $complaint['date'] ?? '',
            $complaint['time'] ?? '',
            $complaint['first_name'] ?? '',
            $complaint['last_name'] ?? '',
            $complaint['city'] ?? '',
            $complaint['phone'] ?? '',
            $complaint['tracking'] ?? '',
            $complaint['type'] ?? '',
            $complaint['category'] ?? '',
            $complaint['description'] ?? ''
        );

        $headers = ['Content-Type: text/plain; charset=UTF-8'];

        return wp_mail($manager_email, $subject, $body, $headers);
    }

    /**
     * Check for urgent unresolved complaints older than 48 hours
     * Can be called via WP Cron
     */
    public function check_overdue_urgent(): array {
        $sheets = new MedAfrica_Google_Sheets();
        $stats = $sheets->get_stats();

        return $stats['urgent_unresolved_48h'] ?? [];
    }
}
