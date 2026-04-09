<?php
/**
 * Plugin Name: Med Africa Dashboard
 * Plugin URI: https://medafrica.ma
 * Description: لوحة تحكم لإدارة شكاوي عملاء Med Africa عبر واتساب
 * Version: 1.0.0
 * Author: Med Africa
 * Author URI: https://medafrica.ma
 * Text Domain: med-africa-dashboard
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MEDAFRICA_VERSION', '1.0.0');
define('MEDAFRICA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MEDAFRICA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MEDAFRICA_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoload classes
require_once MEDAFRICA_PLUGIN_DIR . 'includes/class-google-sheets.php';
require_once MEDAFRICA_PLUGIN_DIR . 'includes/class-dashboard.php';
require_once MEDAFRICA_PLUGIN_DIR . 'includes/class-rest-api.php';
require_once MEDAFRICA_PLUGIN_DIR . 'includes/class-notifications.php';

/**
 * Main plugin class
 */
final class MedAfrica_Dashboard {

    private static ?self $instance = null;

    private MedAfrica_Google_Sheets $sheets;
    private MedAfrica_Dashboard_Pages $dashboard;
    private MedAfrica_REST_API $rest_api;
    private MedAfrica_Notifications $notifications;

    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks(): void {
        add_action('init', [$this, 'load_textdomain']);
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        add_action('admin_init', [$this, 'register_settings']);

        // Initialize components
        $this->sheets = new MedAfrica_Google_Sheets();
        $this->dashboard = new MedAfrica_Dashboard_Pages($this->sheets);
        $this->rest_api = new MedAfrica_REST_API($this->sheets);
        $this->notifications = new MedAfrica_Notifications();
    }

    public function load_textdomain(): void {
        load_plugin_textdomain('med-africa-dashboard', false, dirname(MEDAFRICA_PLUGIN_BASENAME) . '/languages');
    }

    public function register_admin_menu(): void {
        // Main menu
        add_menu_page(
            'Med Africa',
            'Med Africa',
            'manage_options',
            'medafrica-dashboard',
            [$this->dashboard, 'render_dashboard_page'],
            'dashicons-megaphone',
            25
        );

        // Submenus
        add_submenu_page(
            'medafrica-dashboard',
            'لوحة التحكم',
            'لوحة التحكم',
            'manage_options',
            'medafrica-dashboard',
            [$this->dashboard, 'render_dashboard_page']
        );

        add_submenu_page(
            'medafrica-dashboard',
            'قائمة الشكاوي',
            'قائمة الشكاوي',
            'manage_options',
            'medafrica-complaints',
            [$this->dashboard, 'render_complaints_page']
        );

        add_submenu_page(
            'medafrica-dashboard',
            'الإحصائيات',
            'الإحصائيات',
            'manage_options',
            'medafrica-stats',
            [$this->dashboard, 'render_stats_page']
        );

        add_submenu_page(
            'medafrica-dashboard',
            'الإعدادات',
            'الإعدادات',
            'manage_options',
            'medafrica-settings',
            [$this->dashboard, 'render_settings_page']
        );
    }

    public function enqueue_admin_assets(string $hook): void {
        // Only load on our plugin pages
        if (strpos($hook, 'medafrica') === false) {
            return;
        }

        // Google Fonts - Cairo
        wp_enqueue_style(
            'google-fonts-cairo',
            'https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap',
            [],
            null
        );

        // Chart.js
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
            [],
            '4.4.0',
            true
        );

        // Plugin CSS
        wp_enqueue_style(
            'medafrica-dashboard',
            MEDAFRICA_PLUGIN_URL . 'assets/css/dashboard.css',
            [],
            MEDAFRICA_VERSION
        );

        // Plugin JS
        wp_enqueue_script(
            'medafrica-dashboard',
            MEDAFRICA_PLUGIN_URL . 'assets/js/dashboard.js',
            ['chartjs'],
            MEDAFRICA_VERSION,
            true
        );

        // Localize script
        wp_localize_script('medafrica-dashboard', 'medafricaData', [
            'restUrl' => rest_url('medafrica/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'refreshInterval' => (int) get_option('medafrica_refresh_interval', 5) * 60 * 1000,
            'strings' => [
                'loading' => 'جاري التحميل...',
                'error' => 'حدث خطأ',
                'saved' => 'تم الحفظ',
                'confirm_bulk' => 'هل أنت متأكد من تطبيق هذا الإجراء على العناصر المحددة؟',
            ],
        ]);
    }

    public function register_rest_routes(): void {
        $this->rest_api->register_routes();
    }

    public function register_settings(): void {
        register_setting('medafrica_settings', 'medafrica_sheets_api_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting('medafrica_settings', 'medafrica_sheets_id', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting('medafrica_settings', 'medafrica_dialog360_api_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting('medafrica_settings', 'medafrica_manager_whatsapp', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting('medafrica_settings', 'medafrica_refresh_interval', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 5,
        ]);
    }
}

// Initialize plugin
function medafrica_dashboard(): MedAfrica_Dashboard {
    return MedAfrica_Dashboard::instance();
}

add_action('plugins_loaded', 'medafrica_dashboard');

// Activation hook
register_activation_hook(__FILE__, function (): void {
    // Set default options
    add_option('medafrica_refresh_interval', 5);
    // Flush rewrite rules for REST API
    flush_rewrite_rules();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function (): void {
    flush_rewrite_rules();
});
