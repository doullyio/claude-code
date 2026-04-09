/**
 * Med Africa Dashboard - Live Refresh + Charts
 * Vanilla JS, no jQuery dependency
 */

(function () {
    'use strict';

    const API_URL = medafricaData.restUrl;
    const NONCE = medafricaData.nonce;
    const REFRESH_INTERVAL = medafricaData.refreshInterval || 300000;

    // State
    let currentPage = 1;
    const perPage = 25;
    let allComplaints = [];
    let refreshTimer = null;
    let dailyChart = null;
    let categoryChart = null;

    // ============================
    // API Helpers
    // ============================
    async function apiGet(endpoint, params = {}) {
        const url = new URL(API_URL + endpoint, window.location.origin);
        Object.entries(params).forEach(([k, v]) => {
            if (v !== '' && v !== null && v !== undefined) {
                url.searchParams.set(k, v);
            }
        });

        const res = await fetch(url.toString(), {
            headers: { 'X-WP-Nonce': NONCE },
        });

        if (!res.ok) throw new Error('API Error: ' + res.status);
        return res.json();
    }

    async function apiPut(endpoint, body) {
        const res = await fetch(API_URL + endpoint, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': NONCE,
            },
            body: JSON.stringify(body),
        });

        if (!res.ok) throw new Error('API Error: ' + res.status);
        return res.json();
    }

    // ============================
    // Badge Helpers
    // ============================
    function priorityBadge(priority) {
        const map = {
            'عاجل': 'badge-urgent',
            'مهم': 'badge-important',
            'عادي': 'badge-normal',
        };
        return `<span class="badge ${map[priority] || 'badge-normal'}">${priority || 'عادي'}</span>`;
    }

    function statusBadge(status) {
        const map = {
            'جديد': 'badge-new',
            'قيد_المعالجة': 'badge-processing',
            'في_الانتظار': 'badge-waiting',
            'محلول': 'badge-resolved',
            'مغلق': 'badge-closed',
        };
        const label = (status || 'جديد').replace('_', ' ');
        return `<span class="badge ${map[status] || 'badge-new'}">${label}</span>`;
    }

    function typeBadge(type) {
        const cls = type === 'شكوى' ? 'badge-complaint' : 'badge-inquiry';
        return `<span class="badge ${cls}">${type || '--'}</span>`;
    }

    // ============================
    // Dashboard Page
    // ============================
    async function loadDashboard() {
        try {
            const [statsRes, complaintsRes] = await Promise.all([
                apiGet('stats'),
                apiGet('complaints', { per_page: 10, page: 1 }),
            ]);

            const stats = statsRes.data;
            const complaints = complaintsRes.data;

            // Update KPIs
            setText('kpi-total', stats.total);
            setText('kpi-open', stats.open);
            setText('kpi-resolved', stats.resolved);
            setText('kpi-urgent', stats.urgent);

            // Urgent alert banner
            const urgentBanner = document.getElementById('urgent-alert');
            const urgentText = document.getElementById('urgent-alert-text');
            if (urgentBanner && stats.urgent_unresolved_48h && stats.urgent_unresolved_48h.length > 0) {
                urgentBanner.style.display = 'flex';
                urgentText.textContent =
                    `تنبيه: يوجد ${stats.urgent_unresolved_48h.length} شكوى عاجلة لم تحل منذ أكثر من 48 ساعة!`;
            }

            // Recent complaints table
            renderRecentComplaints(complaints);

            // Charts
            renderDailyChart(stats.daily_last_7);
            renderCategoryChart(stats.by_category);

            // Last update time
            setText('last-update', new Date().toLocaleTimeString('ar-MA'));
        } catch (err) {
            console.error('Dashboard load error:', err);
        }
    }

    function renderRecentComplaints(complaints) {
        const tbody = document.getElementById('recent-complaints-body');
        if (!tbody) return;

        if (!complaints || complaints.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="loading-cell">لا توجد شكاوي</td></tr>';
            return;
        }

        tbody.innerHTML = complaints.map(c => `
            <tr>
                <td><strong>${esc(c.ticket_id)}</strong></td>
                <td>${esc(c.date)}</td>
                <td>${esc(c.first_name)} ${esc(c.last_name)}</td>
                <td>${esc(c.city)}</td>
                <td>${esc(c.category)}</td>
                <td>${priorityBadge(c.priority)}</td>
                <td>${statusBadge(c.status)}</td>
            </tr>
        `).join('');
    }

    function renderDailyChart(dailyData) {
        const canvas = document.getElementById('chart-daily');
        if (!canvas || !dailyData) return;

        const labels = Object.keys(dailyData);
        const data = Object.values(dailyData);

        if (dailyChart) dailyChart.destroy();

        dailyChart = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels.map(d => {
                    const date = new Date(d);
                    return date.toLocaleDateString('ar-MA', { weekday: 'short', day: 'numeric' });
                }),
                datasets: [{
                    label: 'عدد الشكاوي',
                    data: data,
                    backgroundColor: '#1D9E75',
                    borderRadius: 6,
                    borderSkipped: false,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { family: 'Cairo' },
                        },
                    },
                    x: {
                        ticks: { font: { family: 'Cairo' } },
                    },
                },
            },
        });
    }

    function renderCategoryChart(categoryData) {
        const canvas = document.getElementById('chart-category');
        if (!canvas || !categoryData) return;

        const labels = Object.keys(categoryData);
        const data = Object.values(categoryData);
        const colors = ['#1D9E75', '#E53935', '#FB8C00', '#8E24AA', '#1E88E5', '#43A047', '#FDD835', '#78909C'];

        if (categoryChart) categoryChart.destroy();

        categoryChart = new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        rtl: true,
                        labels: { font: { family: 'Cairo', size: 12 } },
                    },
                },
                cutout: '55%',
            },
        });
    }

    // ============================
    // Complaints List Page
    // ============================
    async function loadComplaints() {
        const params = getFilters();
        params.page = currentPage;
        params.per_page = perPage;

        try {
            const res = await apiGet('complaints', params);
            allComplaints = res.data;
            renderComplaintsTable(res.data);
            renderPagination(res.pagination);
        } catch (err) {
            console.error('Complaints load error:', err);
        }
    }

    function getFilters() {
        return {
            search: getVal('filter-search'),
            type: getVal('filter-type'),
            category: getVal('filter-category'),
            priority: getVal('filter-priority'),
            status: getVal('filter-status'),
            city: getVal('filter-city'),
            date_from: getVal('filter-date-from'),
            date_to: getVal('filter-date-to'),
        };
    }

    function renderComplaintsTable(complaints) {
        const tbody = document.getElementById('complaints-body');
        if (!tbody) return;

        if (!complaints || complaints.length === 0) {
            tbody.innerHTML = '<tr><td colspan="12" class="loading-cell">لا توجد نتائج</td></tr>';
            return;
        }

        tbody.innerHTML = complaints.map(c => `
            <tr data-id="${esc(c.ticket_id)}">
                <td class="col-check"><input type="checkbox" class="row-check" value="${esc(c.ticket_id)}"></td>
                <td><strong>${esc(c.ticket_id)}</strong></td>
                <td>${esc(c.date)} ${esc(c.time)}</td>
                <td>${esc(c.first_name)} ${esc(c.last_name)}</td>
                <td>${esc(c.city)}</td>
                <td dir="ltr">${esc(c.phone)}</td>
                <td dir="ltr">${esc(c.tracking)}</td>
                <td>${esc(c.category)}</td>
                <td>${priorityBadge(c.priority)}</td>
                <td>
                    <select class="status-select" data-id="${esc(c.ticket_id)}" onchange="medafricaUpdateStatus(this)">
                        ${statusOptions(c.status)}
                    </select>
                </td>
                <td>${esc(c.assignee) || '--'}</td>
                <td>
                    <button class="btn-view" onclick="medafricaViewDetail('${esc(c.ticket_id)}')">عرض</button>
                </td>
            </tr>
        `).join('');

        updateSelectedCount();
    }

    function statusOptions(current) {
        const statuses = ['جديد', 'قيد_المعالجة', 'في_الانتظار', 'محلول', 'مغلق'];
        return statuses.map(s =>
            `<option value="${s}" ${s === current ? 'selected' : ''}>${s.replace('_', ' ')}</option>`
        ).join('');
    }

    function renderPagination(pagination) {
        const info = document.getElementById('page-info');
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');

        if (!info) return;

        info.textContent = `صفحة ${pagination.page} من ${pagination.total_pages} (${pagination.total} شكوى)`;

        if (btnPrev) btnPrev.disabled = pagination.page <= 1;
        if (btnNext) btnNext.disabled = pagination.page >= pagination.total_pages;
    }

    // ============================
    // Stats Page
    // ============================
    async function loadStats() {
        try {
            const res = await apiGet('stats');
            const stats = res.data;

            setText('stat-today', stats.today);
            setText('stat-week', stats.this_week);
            setText('stat-month', stats.this_month);
            setText('stat-all', stats.total);

            // Category chart
            renderPieChart('stats-chart-category', stats.by_category,
                ['#1D9E75', '#E53935', '#FB8C00', '#8E24AA', '#1E88E5', '#43A047', '#FDD835', '#78909C']);

            // Priority chart
            renderPieChart('stats-chart-priority', stats.by_priority,
                ['#E53935', '#FB8C00', '#4CAF50']);

            // City chart
            renderBarChart('stats-chart-city', stats.by_city, '#1D9E75');

            // Status chart
            renderPieChart('stats-chart-status', stats.by_status,
                ['#1E88E5', '#FB8C00', '#8E24AA', '#4CAF50', '#78909C']);
        } catch (err) {
            console.error('Stats load error:', err);
        }
    }

    function renderPieChart(canvasId, data, colors) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !data) return;

        const labels = Object.keys(data);
        const values = Object.values(data);

        new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        rtl: true,
                        labels: { font: { family: 'Cairo', size: 12 } },
                    },
                },
                cutout: '50%',
            },
        });
    }

    function renderBarChart(canvasId, data, color) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !data) return;

        const labels = Object.keys(data);
        const values = Object.values(data);

        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: color,
                    borderRadius: 6,
                    borderSkipped: false,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { family: 'Cairo' } },
                    },
                    y: {
                        ticks: { font: { family: 'Cairo' } },
                    },
                },
            },
        });
    }

    // ============================
    // Detail Modal
    // ============================
    window.medafricaViewDetail = function (ticketId) {
        const complaint = allComplaints.find(c => c.ticket_id === ticketId);
        if (!complaint) return;

        const modal = document.getElementById('complaint-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalBody = document.getElementById('modal-body');

        if (!modal) return;

        modalTitle.textContent = `تفاصيل الشكوى: ${complaint.ticket_id}`;

        modalBody.innerHTML = `
            <div class="detail-row">
                <span class="detail-label">رقم التذكرة</span>
                <span class="detail-value"><strong>${esc(complaint.ticket_id)}</strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">التاريخ</span>
                <span class="detail-value">${esc(complaint.date)} ${esc(complaint.time)}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">الاسم الكامل</span>
                <span class="detail-value">${esc(complaint.first_name)} ${esc(complaint.last_name)}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">المدينة</span>
                <span class="detail-value">${esc(complaint.city)}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">رقم الهاتف</span>
                <span class="detail-value" dir="ltr">${esc(complaint.phone)}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">رقم التتبع</span>
                <span class="detail-value" dir="ltr">${esc(complaint.tracking)}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">النوع</span>
                <span class="detail-value">${typeBadge(complaint.type)}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">الفئة</span>
                <span class="detail-value">${esc(complaint.category)}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">الأولوية</span>
                <span class="detail-value">${priorityBadge(complaint.priority)}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">الوصف</span>
                <span class="detail-value">${esc(complaint.description)}</span>
            </div>
            <hr style="margin: 16px 0; border-color: #eee;">
            <div class="detail-row">
                <span class="detail-label">الحالة</span>
                <span class="detail-value">
                    <select id="modal-status" class="medafrica-select">
                        ${statusOptions(complaint.status)}
                    </select>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">المسؤول</span>
                <span class="detail-value">
                    <input type="text" id="modal-assignee" value="${esc(complaint.assignee)}" placeholder="اسم المسؤول">
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">ملاحظات</span>
                <span class="detail-value">
                    <textarea id="modal-notes" placeholder="أضف ملاحظات...">${esc(complaint.notes)}</textarea>
                </span>
            </div>
        `;

        modal.style.display = 'flex';
        modal.dataset.ticketId = ticketId;
    };

    // ============================
    // Status Update (inline)
    // ============================
    window.medafricaUpdateStatus = async function (selectEl) {
        const ticketId = selectEl.dataset.id;
        const newStatus = selectEl.value;

        try {
            await apiPut('complaints/' + encodeURIComponent(ticketId), { status: newStatus });
        } catch (err) {
            console.error('Status update error:', err);
            alert('فشل تحديث الحالة');
        }
    };

    // ============================
    // Utility Functions
    // ============================
    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value ?? '--';
    }

    function getVal(id) {
        const el = document.getElementById(id);
        return el ? el.value : '';
    }

    function esc(str) {
        if (str === null || str === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    function updateSelectedCount() {
        const checks = document.querySelectorAll('.row-check:checked');
        const countEl = document.getElementById('selected-count');
        if (countEl) {
            countEl.textContent = checks.length + ' محدد';
        }
    }

    // ============================
    // CSV Export
    // ============================
    function exportCSV(complaints) {
        const headers = ['رقم التذكرة', 'التاريخ', 'الوقت', 'الاسم', 'اللقب', 'المدينة',
            'رقم الهاتف', 'رقم التتبع', 'النوع', 'الفئة', 'الأولوية', 'الحالة', 'الوصف', 'المسؤول', 'ملاحظات'];

        const rows = complaints.map(c => [
            c.ticket_id, c.date, c.time, c.first_name, c.last_name, c.city,
            c.phone, c.tracking, c.type, c.category, c.priority, c.status,
            c.description, c.assignee, c.notes,
        ]);

        // BOM for Arabic support in Excel
        let csv = '\uFEFF' + headers.join(',') + '\n';
        rows.forEach(row => {
            csv += row.map(cell => '"' + String(cell || '').replace(/"/g, '""') + '"').join(',') + '\n';
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'medafrica-complaints-' + new Date().toISOString().slice(0, 10) + '.csv';
        link.click();
    }

    // ============================
    // Event Listeners
    // ============================
    function bindEvents() {
        // Refresh button
        const btnRefresh = document.getElementById('btn-refresh');
        if (btnRefresh) {
            btnRefresh.addEventListener('click', () => {
                loadDashboard();
            });
        }

        // Filter buttons
        const btnApply = document.getElementById('btn-apply-filters');
        if (btnApply) {
            btnApply.addEventListener('click', () => {
                currentPage = 1;
                loadComplaints();
            });
        }

        const btnClear = document.getElementById('btn-clear-filters');
        if (btnClear) {
            btnClear.addEventListener('click', () => {
                document.querySelectorAll('.medafrica-filters-bar input, .medafrica-filters-bar select').forEach(el => {
                    el.value = '';
                });
                currentPage = 1;
                loadComplaints();
            });
        }

        // Search on Enter
        const searchInput = document.getElementById('filter-search');
        if (searchInput) {
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    currentPage = 1;
                    loadComplaints();
                }
            });
        }

        // Pagination
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        if (btnPrev) {
            btnPrev.addEventListener('click', () => {
                if (currentPage > 1) { currentPage--; loadComplaints(); }
            });
        }
        if (btnNext) {
            btnNext.addEventListener('click', () => {
                currentPage++;
                loadComplaints();
            });
        }

        // Select all checkboxes
        ['select-all', 'thead-select-all'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', () => {
                    document.querySelectorAll('.row-check').forEach(cb => {
                        cb.checked = el.checked;
                    });
                    updateSelectedCount();
                });
            }
        });

        // Row checkbox changes
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('row-check')) {
                updateSelectedCount();
            }
        });

        // Bulk action
        const btnBulk = document.getElementById('btn-bulk-apply');
        if (btnBulk) {
            btnBulk.addEventListener('click', async () => {
                const action = getVal('bulk-action');
                const assignee = getVal('bulk-assignee');
                const checks = document.querySelectorAll('.row-check:checked');

                if (checks.length === 0) {
                    alert('يرجى تحديد شكوى واحدة على الأقل');
                    return;
                }

                if (action === 'export-csv') {
                    const selected = Array.from(checks).map(cb => cb.value);
                    const filtered = allComplaints.filter(c => selected.includes(c.ticket_id));
                    exportCSV(filtered);
                    return;
                }

                if (!action && !assignee) {
                    alert('يرجى اختيار إجراء');
                    return;
                }

                if (!confirm(medafricaData.strings.confirm_bulk)) return;

                const ids = Array.from(checks).map(cb => cb.value);
                const updateData = {};
                if (action && action.startsWith('status-')) {
                    updateData.status = action.replace('status-', '');
                }
                if (assignee) {
                    updateData.assignee = assignee;
                }

                for (const id of ids) {
                    try {
                        await apiPut('complaints/' + encodeURIComponent(id), updateData);
                    } catch (err) {
                        console.error('Bulk update error for', id, err);
                    }
                }

                loadComplaints();
            });
        }

        // Modal close
        const modalClose = document.getElementById('modal-close');
        const modalCancel = document.getElementById('modal-cancel');
        const modalOverlay = document.getElementById('complaint-modal');

        [modalClose, modalCancel].forEach(el => {
            if (el) el.addEventListener('click', () => {
                if (modalOverlay) modalOverlay.style.display = 'none';
            });
        });

        if (modalOverlay) {
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) modalOverlay.style.display = 'none';
            });
        }

        // Modal save
        const modalSave = document.getElementById('modal-save');
        if (modalSave) {
            modalSave.addEventListener('click', async () => {
                const ticketId = modalOverlay?.dataset.ticketId;
                if (!ticketId) return;

                const data = {
                    status: getVal('modal-status'),
                    assignee: getVal('modal-assignee'),
                    notes: getVal('modal-notes'),
                };

                try {
                    await apiPut('complaints/' + encodeURIComponent(ticketId), data);
                    if (modalOverlay) modalOverlay.style.display = 'none';
                    loadComplaints();
                } catch (err) {
                    alert('فشل حفظ التغييرات');
                }
            });
        }

        // Settings - Test Sheets connection
        const btnTestSheets = document.getElementById('btn-test-sheets');
        if (btnTestSheets) {
            btnTestSheets.addEventListener('click', async () => {
                const result = document.getElementById('sheets-test-result');
                if (result) result.textContent = 'جاري الاختبار...';

                try {
                    const res = await apiGet('stats');
                    if (result) {
                        result.textContent = '✅ تم الاتصال بنجاح';
                        result.style.color = '#1D9E75';
                    }
                } catch (err) {
                    if (result) {
                        result.textContent = '❌ فشل الاتصال';
                        result.style.color = '#E53935';
                    }
                }
            });
        }

        // Settings - Test WhatsApp connection
        const btnTestWA = document.getElementById('btn-test-whatsapp');
        if (btnTestWA) {
            btnTestWA.addEventListener('click', () => {
                const result = document.getElementById('whatsapp-test-result');
                if (result) {
                    result.textContent = 'يرجى اختبار الاتصال من n8n مباشرة';
                    result.style.color = '#FB8C00';
                }
            });
        }

        // Sortable headers
        document.querySelectorAll('.sortable').forEach(th => {
            th.addEventListener('click', () => {
                const field = th.dataset.sort;
                if (!field) return;
                // Simple client-side sort toggle
                const isAsc = th.classList.contains('sort-asc');
                document.querySelectorAll('.sortable').forEach(h => {
                    h.classList.remove('sort-asc', 'sort-desc');
                });
                th.classList.add(isAsc ? 'sort-desc' : 'sort-asc');

                allComplaints.sort((a, b) => {
                    const va = a[field] || '';
                    const vb = b[field] || '';
                    return isAsc ? vb.localeCompare(va) : va.localeCompare(vb);
                });

                renderComplaintsTable(allComplaints);
            });
        });
    }

    // ============================
    // Auto-refresh
    // ============================
    function startAutoRefresh() {
        if (refreshTimer) clearInterval(refreshTimer);

        refreshTimer = setInterval(() => {
            // Detect which page is active
            if (document.getElementById('kpi-cards')) {
                loadDashboard();
            } else if (document.getElementById('complaints-body')) {
                loadComplaints();
            } else if (document.getElementById('stat-today')) {
                loadStats();
            }
        }, REFRESH_INTERVAL);
    }

    // ============================
    // Init
    // ============================
    function init() {
        bindEvents();

        // Detect which page we're on and load data
        if (document.getElementById('kpi-cards')) {
            loadDashboard();
        }

        if (document.getElementById('complaints-body')) {
            loadComplaints();
        }

        if (document.getElementById('stat-today')) {
            loadStats();
        }

        // Start auto-refresh
        startAutoRefresh();
    }

    // Wait for DOM and Chart.js
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
