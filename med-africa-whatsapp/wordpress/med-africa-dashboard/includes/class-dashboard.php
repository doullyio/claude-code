<?php
/**
 * Dashboard Pages Rendering
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedAfrica_Dashboard_Pages {

    private MedAfrica_Google_Sheets $sheets;

    public function __construct(MedAfrica_Google_Sheets $sheets) {
        $this->sheets = $sheets;
    }

    /**
     * Main Dashboard Page
     */
    public function render_dashboard_page(): void {
        ?>
        <div class="wrap medafrica-wrap" dir="rtl">
            <div class="medafrica-header">
                <h1>Med Africa - لوحة التحكم</h1>
                <span class="medafrica-badge">نظام إدارة الشكاوي</span>
            </div>

            <?php $this->render_urgent_alert_banner(); ?>

            <!-- KPI Cards -->
            <div class="medafrica-kpi-grid" id="kpi-cards">
                <div class="medafrica-kpi-card">
                    <div class="kpi-icon kpi-total">📋</div>
                    <div class="kpi-content">
                        <span class="kpi-value" id="kpi-total">--</span>
                        <span class="kpi-label">الإجمالي</span>
                    </div>
                </div>
                <div class="medafrica-kpi-card">
                    <div class="kpi-icon kpi-open">📂</div>
                    <div class="kpi-content">
                        <span class="kpi-value" id="kpi-open">--</span>
                        <span class="kpi-label">المفتوحة</span>
                    </div>
                </div>
                <div class="medafrica-kpi-card">
                    <div class="kpi-icon kpi-resolved">✅</div>
                    <div class="kpi-content">
                        <span class="kpi-value" id="kpi-resolved">--</span>
                        <span class="kpi-label">المحلولة</span>
                    </div>
                </div>
                <div class="medafrica-kpi-card kpi-urgent-card">
                    <div class="kpi-icon kpi-urgent">🚨</div>
                    <div class="kpi-content">
                        <span class="kpi-value" id="kpi-urgent">--</span>
                        <span class="kpi-label">العاجلة</span>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="medafrica-charts-grid">
                <div class="medafrica-chart-card">
                    <h3>شكاوي آخر 7 أيام</h3>
                    <canvas id="chart-daily" height="250"></canvas>
                </div>
                <div class="medafrica-chart-card">
                    <h3>توزيع حسب الفئة</h3>
                    <canvas id="chart-category" height="250"></canvas>
                </div>
            </div>

            <!-- Recent Complaints Table -->
            <div class="medafrica-card">
                <h3>آخر 10 شكاوي</h3>
                <div class="medafrica-table-wrapper">
                    <table class="medafrica-table" id="recent-complaints">
                        <thead>
                            <tr>
                                <th>رقم التذكرة</th>
                                <th>التاريخ</th>
                                <th>الاسم</th>
                                <th>المدينة</th>
                                <th>الفئة</th>
                                <th>الأولوية</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody id="recent-complaints-body">
                            <tr><td colspan="7" class="loading-cell">جاري التحميل...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="medafrica-footer">
                <span>آخر تحديث: <span id="last-update">--</span></span>
                <button class="button medafrica-btn-refresh" id="btn-refresh">تحديث الآن</button>
            </div>
        </div>
        <?php
    }

    /**
     * Urgent alert banner
     */
    private function render_urgent_alert_banner(): void {
        ?>
        <div class="medafrica-alert-banner" id="urgent-alert" style="display:none;">
            <span class="alert-icon">⚠️</span>
            <span class="alert-text" id="urgent-alert-text"></span>
            <button class="alert-dismiss" onclick="this.parentElement.style.display='none'">✕</button>
        </div>
        <?php
    }

    /**
     * Complaints List Page
     */
    public function render_complaints_page(): void {
        ?>
        <div class="wrap medafrica-wrap" dir="rtl">
            <div class="medafrica-header">
                <h1>قائمة الشكاوي</h1>
            </div>

            <!-- Filters Bar -->
            <div class="medafrica-filters-bar">
                <div class="filter-group">
                    <input type="text" id="filter-search" class="medafrica-input"
                           placeholder="بحث بالاسم، الهاتف، رقم التتبع، أو التذكرة...">
                </div>
                <div class="filter-group">
                    <select id="filter-type" class="medafrica-select">
                        <option value="">كل الأنواع</option>
                        <option value="شكوى">شكوى</option>
                        <option value="استفسار">استفسار</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select id="filter-category" class="medafrica-select">
                        <option value="">كل الفئات</option>
                        <option value="تأخير">تأخير</option>
                        <option value="ضياع">ضياع</option>
                        <option value="تلف">تلف</option>
                        <option value="منتج_خطأ">منتج خطأ</option>
                        <option value="تتبع">تتبع</option>
                        <option value="إرجاع">إرجاع</option>
                        <option value="توصيل">توصيل</option>
                        <option value="عام">عام</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select id="filter-priority" class="medafrica-select">
                        <option value="">كل الأولويات</option>
                        <option value="عاجل">عاجل</option>
                        <option value="مهم">مهم</option>
                        <option value="عادي">عادي</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select id="filter-status" class="medafrica-select">
                        <option value="">كل الحالات</option>
                        <option value="جديد">جديد</option>
                        <option value="قيد_المعالجة">قيد المعالجة</option>
                        <option value="في_الانتظار">في الانتظار</option>
                        <option value="محلول">محلول</option>
                        <option value="مغلق">مغلق</option>
                    </select>
                </div>
                <div class="filter-group">
                    <input type="text" id="filter-city" class="medafrica-input" placeholder="المدينة">
                </div>
                <div class="filter-group">
                    <input type="date" id="filter-date-from" class="medafrica-input" placeholder="من تاريخ">
                    <input type="date" id="filter-date-to" class="medafrica-input" placeholder="إلى تاريخ">
                </div>
                <button class="button medafrica-btn-primary" id="btn-apply-filters">تطبيق</button>
                <button class="button" id="btn-clear-filters">مسح</button>
            </div>

            <!-- Bulk Actions -->
            <div class="medafrica-bulk-bar">
                <label><input type="checkbox" id="select-all"> تحديد الكل</label>
                <select id="bulk-action" class="medafrica-select">
                    <option value="">إجراء جماعي...</option>
                    <option value="status-قيد_المعالجة">تغيير الحالة: قيد المعالجة</option>
                    <option value="status-محلول">تغيير الحالة: محلول</option>
                    <option value="status-مغلق">تغيير الحالة: مغلق</option>
                    <option value="export-csv">تصدير CSV</option>
                </select>
                <input type="text" id="bulk-assignee" class="medafrica-input" placeholder="تعيين مسؤول" style="width:150px;">
                <button class="button" id="btn-bulk-apply">تنفيذ</button>
                <span class="bulk-count" id="selected-count">0 محدد</span>
            </div>

            <!-- Complaints Table -->
            <div class="medafrica-table-wrapper">
                <table class="medafrica-table medafrica-table-full" id="complaints-table">
                    <thead>
                        <tr>
                            <th class="col-check"><input type="checkbox" id="thead-select-all"></th>
                            <th class="sortable" data-sort="ticket_id">رقم التذكرة</th>
                            <th class="sortable" data-sort="date">التاريخ</th>
                            <th>الاسم الكامل</th>
                            <th>المدينة</th>
                            <th>رقم الهاتف</th>
                            <th>رقم التتبع</th>
                            <th class="sortable" data-sort="category">الفئة</th>
                            <th class="sortable" data-sort="priority">الأولوية</th>
                            <th>الحالة</th>
                            <th>المسؤول</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="complaints-body">
                        <tr><td colspan="12" class="loading-cell">جاري التحميل...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="medafrica-pagination" id="pagination">
                <button class="button" id="btn-prev" disabled>السابق</button>
                <span id="page-info">صفحة 1</span>
                <button class="button" id="btn-next">التالي</button>
            </div>

            <!-- Detail Modal -->
            <div class="medafrica-modal-overlay" id="complaint-modal" style="display:none;">
                <div class="medafrica-modal">
                    <div class="modal-header">
                        <h2 id="modal-title">تفاصيل الشكوى</h2>
                        <button class="modal-close" id="modal-close">✕</button>
                    </div>
                    <div class="modal-body" id="modal-body">
                        <!-- Filled by JS -->
                    </div>
                    <div class="modal-footer">
                        <button class="button medafrica-btn-primary" id="modal-save">حفظ التغييرات</button>
                        <button class="button" id="modal-cancel">إلغاء</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Statistics Page
     */
    public function render_stats_page(): void {
        ?>
        <div class="wrap medafrica-wrap" dir="rtl">
            <div class="medafrica-header">
                <h1>الإحصائيات</h1>
            </div>

            <!-- Period Stats -->
            <div class="medafrica-card">
                <h3>إحصائيات حسب الفترة</h3>
                <div class="medafrica-stats-grid">
                    <div class="stat-box">
                        <span class="stat-period">اليوم</span>
                        <span class="stat-value" id="stat-today">--</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-period">هذا الأسبوع</span>
                        <span class="stat-value" id="stat-week">--</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-period">هذا الشهر</span>
                        <span class="stat-value" id="stat-month">--</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-period">الإجمالي</span>
                        <span class="stat-value" id="stat-all">--</span>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="medafrica-charts-grid">
                <div class="medafrica-chart-card">
                    <h3>توزيع حسب الفئة</h3>
                    <canvas id="stats-chart-category" height="300"></canvas>
                </div>
                <div class="medafrica-chart-card">
                    <h3>توزيع حسب الأولوية</h3>
                    <canvas id="stats-chart-priority" height="300"></canvas>
                </div>
            </div>

            <div class="medafrica-charts-grid">
                <div class="medafrica-chart-card">
                    <h3>توزيع حسب المدينة</h3>
                    <canvas id="stats-chart-city" height="300"></canvas>
                </div>
                <div class="medafrica-chart-card">
                    <h3>توزيع حسب الحالة</h3>
                    <canvas id="stats-chart-status" height="300"></canvas>
                </div>
            </div>

            <div class="medafrica-card">
                <h3>شكاوي آخر 30 يوم</h3>
                <canvas id="stats-chart-trend" height="200"></canvas>
            </div>
        </div>
        <?php
    }

    /**
     * Settings Page
     */
    public function render_settings_page(): void {
        // Handle form submission
        if (isset($_POST['medafrica_save_settings']) && check_admin_referer('medafrica_settings_nonce')) {
            update_option('medafrica_sheets_api_key', sanitize_text_field($_POST['medafrica_sheets_api_key'] ?? ''));
            update_option('medafrica_sheets_id', sanitize_text_field($_POST['medafrica_sheets_id'] ?? ''));
            update_option('medafrica_dialog360_api_key', sanitize_text_field($_POST['medafrica_dialog360_api_key'] ?? ''));
            update_option('medafrica_manager_whatsapp', sanitize_text_field($_POST['medafrica_manager_whatsapp'] ?? ''));
            update_option('medafrica_refresh_interval', absint($_POST['medafrica_refresh_interval'] ?? 5));
            echo '<div class="notice notice-success is-dismissible"><p>تم حفظ الإعدادات بنجاح!</p></div>';
        }
        ?>
        <div class="wrap medafrica-wrap" dir="rtl">
            <div class="medafrica-header">
                <h1>الإعدادات</h1>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('medafrica_settings_nonce'); ?>

                <!-- Google Sheets Settings -->
                <div class="medafrica-card">
                    <h3>إعدادات Google Sheets</h3>
                    <table class="form-table medafrica-settings-table">
                        <tr>
                            <th><label for="medafrica_sheets_api_key">Google Sheets API Key</label></th>
                            <td>
                                <input type="password" id="medafrica_sheets_api_key" name="medafrica_sheets_api_key"
                                       class="regular-text" dir="ltr"
                                       value="<?php echo esc_attr(get_option('medafrica_sheets_api_key', '')); ?>">
                                <button type="button" class="button" id="btn-test-sheets">اختبار الاتصال</button>
                                <span id="sheets-test-result"></span>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="medafrica_sheets_id">Google Sheets ID</label></th>
                            <td>
                                <input type="text" id="medafrica_sheets_id" name="medafrica_sheets_id"
                                       class="regular-text" dir="ltr"
                                       value="<?php echo esc_attr(get_option('medafrica_sheets_id', '')); ?>">
                                <p class="description">معرف الجدول من رابط Google Sheets</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- WhatsApp Settings -->
                <div class="medafrica-card">
                    <h3>إعدادات واتساب (360dialog)</h3>
                    <table class="form-table medafrica-settings-table">
                        <tr>
                            <th><label for="medafrica_dialog360_api_key">360dialog API Key</label></th>
                            <td>
                                <input type="password" id="medafrica_dialog360_api_key" name="medafrica_dialog360_api_key"
                                       class="regular-text" dir="ltr"
                                       value="<?php echo esc_attr(get_option('medafrica_dialog360_api_key', '')); ?>">
                                <button type="button" class="button" id="btn-test-whatsapp">اختبار الاتصال</button>
                                <span id="whatsapp-test-result"></span>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="medafrica_manager_whatsapp">رقم واتساب المدير</label></th>
                            <td>
                                <input type="text" id="medafrica_manager_whatsapp" name="medafrica_manager_whatsapp"
                                       class="regular-text" dir="ltr" placeholder="212600000000"
                                       value="<?php echo esc_attr(get_option('medafrica_manager_whatsapp', '')); ?>">
                                <p class="description">للتنبيهات العاجلة (بدون + أو 00)</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- General Settings -->
                <div class="medafrica-card">
                    <h3>إعدادات عامة</h3>
                    <table class="form-table medafrica-settings-table">
                        <tr>
                            <th><label for="medafrica_refresh_interval">فترة التحديث التلقائي</label></th>
                            <td>
                                <select id="medafrica_refresh_interval" name="medafrica_refresh_interval" class="medafrica-select">
                                    <?php
                                    $current = get_option('medafrica_refresh_interval', 5);
                                    foreach ([1 => 'دقيقة واحدة', 3 => '3 دقائق', 5 => '5 دقائق'] as $val => $label) {
                                        printf(
                                            '<option value="%d" %s>%s</option>',
                                            $val,
                                            selected($current, $val, false),
                                            $label
                                        );
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <p class="submit">
                    <input type="submit" name="medafrica_save_settings" class="button medafrica-btn-primary"
                           value="حفظ الإعدادات">
                </p>
            </form>

            <!-- API Info -->
            <div class="medafrica-card">
                <h3>معلومات REST API</h3>
                <table class="medafrica-settings-table">
                    <tr>
                        <td><code dir="ltr">GET <?php echo esc_html(rest_url('medafrica/v1/complaints')); ?></code></td>
                        <td>قائمة الشكاوي</td>
                    </tr>
                    <tr>
                        <td><code dir="ltr">GET <?php echo esc_html(rest_url('medafrica/v1/complaints/{id}')); ?></code></td>
                        <td>تفاصيل شكوى</td>
                    </tr>
                    <tr>
                        <td><code dir="ltr">PUT <?php echo esc_html(rest_url('medafrica/v1/complaints/{id}')); ?></code></td>
                        <td>تحديث شكوى</td>
                    </tr>
                    <tr>
                        <td><code dir="ltr">GET <?php echo esc_html(rest_url('medafrica/v1/stats')); ?></code></td>
                        <td>الإحصائيات</td>
                    </tr>
                    <tr>
                        <td><code dir="ltr">POST <?php echo esc_html(rest_url('medafrica/v1/webhook/sheets')); ?></code></td>
                        <td>Webhook من n8n</td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }
}
