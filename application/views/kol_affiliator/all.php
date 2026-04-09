<style>
    .kol-card {
        border: 1px solid #e4e7ec;
        border-radius: 18px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .kol-card .card-header {
        background:
            linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(30, 64, 175, 0.94)),
            linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0));
        border-bottom: 0;
        padding: 20px 24px;
    }

    .kol-card .card-body {
        padding: 24px;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 20%);
    }

    .kol-title {
        color: #ffffff;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .kol-subtitle {
        color: rgba(255, 255, 255, 0.72);
        font-size: 13px;
    }

    .kol-toolbar {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
        padding: 16px 18px;
        border: 1px solid #e4e7ec;
        border-radius: 16px;
        background: #ffffff;
    }

    .kol-summary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #eef4ff;
        color: #1d4ed8;
        font-size: 13px;
        font-weight: 600;
    }

    .kol-filter-grid {
        display: grid;
        grid-template-columns: 1.1fr 1.4fr 1.2fr auto;
        gap: 12px;
        width: 100%;
    }

    .kol-input-wrap {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }

    .kol-input-wrap label {
        margin-bottom: 0;
        font-size: 12px;
        font-weight: 600;
        color: #344054;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .kol-filter-actions {
        display: flex;
        align-items: end;
        gap: 10px;
    }

    .kol-filter-grid .form-select,
    .kol-filter-grid .form-control,
    .kol-filter-actions .btn {
        height: 44px;
        min-height: 44px;
        border-radius: 12px;
        font-size: 14px;
    }

    .kol-filter-grid .form-select,
    .kol-filter-grid .form-control {
        border-color: #d0d5dd;
        padding: 10px 14px;
        box-shadow: none;
    }

    .kol-filter-grid .form-select:focus,
    .kol-filter-grid .form-control:focus {
        border-color: #84adff;
        box-shadow: 0 0 0 4px rgba(23, 92, 211, 0.12);
    }

    .kol-filter-actions .btn {
        min-width: 120px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 16px;
    }

    .kol-status-overview {
        border: 1px solid #e4e7ec;
        border-radius: 16px;
        background: #ffffff;
        padding: 18px;
        margin-bottom: 18px;
    }

    .kol-status-overview-head {
        display: flex;
        align-items: start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
    }

    .kol-panel-title {
        color: #101828;
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .kol-panel-subtitle {
        color: #667085;
        font-size: 13px;
        margin-bottom: 14px;
    }

    .kol-progress-track {
        display: flex;
        width: 100%;
        height: 18px;
        border-radius: 999px;
        overflow: hidden;
        background: #f2f4f7;
        border: 1px solid #eaecf0;
        margin-bottom: 12px;
    }

    .kol-progress-note {
        color: #667085;
        font-size: 12px;
        margin-bottom: 14px;
    }

    .kol-progress-segment {
        position: relative;
        min-width: 8px;
        transition: transform 0.18s ease, filter 0.18s ease;
        cursor: default;
    }

    .kol-progress-segment:hover {
        transform: translateY(-1px);
        filter: brightness(0.97);
    }

    .kol-status-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        border-color: #cfd4dc;
    }

    .kol-status-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        max-height: 252px;
        overflow: auto;
        padding-right: 2px;
    }

    .kol-status-stat {
        border: 1px solid #eaecf0;
        border-radius: 14px;
        padding: 12px;
        background: #fcfcfd;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        cursor: default;
    }

    .kol-status-stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
    }

    .kol-status-stat-name {
        color: #344054;
        font-size: 13px;
        font-weight: 600;
        min-width: 0;
    }

    .kol-status-stat-count {
        color: #101828;
        font-size: 18px;
        font-weight: 700;
        white-space: nowrap;
    }

    .kol-status-stat-bar {
        height: 8px;
        width: 100%;
        border-radius: 999px;
        background: #f2f4f7;
        overflow: hidden;
        margin-bottom: 6px;
    }

    .kol-status-stat-fill {
        height: 100%;
        border-radius: 999px;
        min-width: 8px;
    }

    .kol-status-stat-meta {
        color: #667085;
        font-size: 12px;
    }

    .kol-table-shell {
        border: 1px solid #e4e7ec;
        border-radius: 18px;
        background: #ffffff;
        overflow: hidden;
    }

    #kol-affiliator-table thead th {
        background: #f8fafc;
        color: #475467;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 14px 12px;
        border-bottom: 1px solid #e4e7ec;
    }

    #kol-affiliator-table tbody td {
        padding: 14px 12px;
        vertical-align: middle;
        border-color: #eaecf0;
        color: #344054;
    }

    #kol-affiliator-table tbody tr:hover {
        background: #f8fbff;
    }

    .kol-username {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .kol-username-main {
        color: #175cd3;
        font-weight: 700;
    }

    .kol-username-meta {
        color: #667085;
        font-size: 12px;
    }

    .kol-status-badge {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        padding: 6px 10px;
        border-radius: 999px;
        border: 1px solid transparent;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.2;
        white-space: normal;
    }

    .kol-status-empty {
        color: #98a2b3;
        font-size: 13px;
    }

    .kol-action-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        margin-left: 6px;
        text-decoration: none;
        transition: all 0.18s ease;
    }

    .kol-action-link:hover {
        transform: translateY(-1px);
    }

    .kol-action-view {
        color: #175cd3;
        background: rgba(23, 92, 211, 0.1);
    }

    .kol-action-edit {
        color: #0f766e;
        background: rgba(15, 118, 110, 0.12);
    }

    .kol-action-delete {
        color: #b42318;
        background: rgba(180, 35, 24, 0.12);
    }

    div.dataTables_wrapper div.dataTables_length label,
    div.dataTables_wrapper div.dataTables_info,
    div.dataTables_wrapper div.dataTables_filter label {
        color: #667085;
        font-size: 13px;
    }

    div.dataTables_wrapper div.dataTables_paginate ul.pagination {
        gap: 4px;
    }

    div.dataTables_wrapper div.dataTables_paginate .page-link {
        border-radius: 10px;
        border-color: #d0d5dd;
        color: #344054;
    }

    div.dataTables_wrapper div.dataTables_paginate .page-item.active .page-link {
        background: #175cd3;
        border-color: #175cd3;
        color: #ffffff;
    }

    @media (max-width: 991.98px) {
        .kol-filter-grid {
            grid-template-columns: 1fr 1fr;
        }

    }

    @media (max-width: 767.98px) {
        .kol-card .card-header,
        .kol-card .card-body {
            padding: 16px;
        }

        .kol-filter-grid {
            grid-template-columns: 1fr;
        }

        .kol-filter-actions {
            width: 100%;
        }

        .kol-filter-actions .btn {
            flex: 1 1 0;
            width: 100%;
        }

        .kol-status-stats {
            grid-template-columns: 1fr;
        }

        .kol-status-overview-head {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div class="container-fluid">
    <div class="card kol-card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h5 class="mb-1 kol-title">KOL & Affiliator</h5>
                    <div class="kol-subtitle">Status lebih terbaca, filter konsisten, dan interaksi tabel lebih cepat.</div>
                </div>

                <?php if (!empty($can_create)): ?>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="#!" onclick="openImportModal()" class="btn btn-outline-light">
                            <i class="bi bi-file-earmark-excel me-1"></i> Import Excel
                        </a>
                        <a href="<?= base_url('kol-affiliator/create-page') ?>" class="btn btn-light text-primary">
                            <i class="bi bi-plus me-1"></i> Create
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body">
            <div class="kol-toolbar">
                <div class="kol-summary" id="kol-summary">
                    <i class="bi bi-table"></i>
                    <span>Memuat data...</span>
                </div>

                <form id="kol-filter-form" class="w-100">
                    <div class="kol-filter-grid">
                        <div class="kol-input-wrap">
                            <label for="keyword-category">Kategori Search</label>
                            <select class="form-select" id="keyword-category" name="keyword_category">
                                <?php
                                $keyword_options = ['Username KOL', 'PIC Utama', 'Product', 'Status Creator'];
                                foreach ($keyword_options as $opt):
                                ?>
                                    <option value="<?= $opt ?>" <?= ($keyword_category == $opt ? 'selected' : '') ?>>
                                        <?= $opt ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="kol-input-wrap">
                            <label for="keyword-input">Keyword</label>
                            <input
                                type="text"
                                id="keyword-input"
                                name="keyword"
                                class="form-control"
                                placeholder="Cari data..."
                                value="<?= htmlspecialchars($_GET['keyword'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>

                        <div class="kol-input-wrap">
                            <label for="status-filter">Filter Status Creator</label>
                            <select class="form-select" id="status-filter" name="status_creator">
                                <option value="">Semua Status Creator</option>
                                <?php foreach ($status_creator_options as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" <?= (($status_creator ?? '') === $opt ? 'selected' : '') ?>>
                                        <?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="kol-filter-actions">
                            <button type="button" class="btn btn-outline-secondary" id="reset-filter">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="kol-status-overview">
                <div class="kol-status-overview-head">
                    <div>
                        <div class="kol-panel-title">Status Overview</div>
                        <div class="kol-panel-subtitle">Ringkasan urgency dan statistik status untuk data yang sedang tampil di tabel.</div>
                    </div>
                </div>

                <div>
                    <div class="kol-panel-title">Status Progress</div>
                    <div class="kol-progress-track" id="kol-progress-track"></div>
                    <div class="kol-progress-note">Hover pada tiap segmen untuk lihat jumlah dan persentase per urgency.</div>
                </div>

                <div class="kol-panel-title">Status Statistic</div>
                <div class="kol-status-stats" id="kol-status-stats"></div>
            </div>

            <div class="kol-table-shell">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="kol-affiliator-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th style="width:60px">#</th>
                                <th>Username KOL</th>
                                <th>PIC Utama</th>
                                <th>Product</th>
                                <th>Status Creator</th>
                                <th class="text-center" style="width:100px">Post</th>
                                <th class="text-end" style="width:120px">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="modal-form">
    <div class="modal-dialog modal-lg" id="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title-form"></h5>
                <a class="close a-link" data-bs-dismiss="modal"><i class="bi bi-x-circle fs-24"></i></a>
            </div>
            <div class="modal-body">
                <div id="load-form"></div>
            </div>
        </div>
    </div>
</div>

<script>
    function openImportModal() {
        $('#load-form').html('Loading...');
        $('#modal-form').modal('show');
        $('#modal-dialog').removeClass('modal-xl').addClass('modal-lg');
        $('#title-form').html('Import Excel KOL & Affiliator');
        $('#load-form').load("<?= base_url('kol-affiliator/import-excel') ?>");
    }

    function removeCampaign(id) {
        $('#load-form').html('Loading...');
        $('#modal-form').modal('show');
        $('#modal-dialog').removeClass('modal-xl modal-lg').addClass('modal-md');
        $('#title-form').html('Hapus Data');
        $('#load-form').load("<?= base_url('kol-affiliator/remove?id=') ?>" + id);
    }

    $(document).ready(function() {
        var canEdit = <?= !empty($can_edit) ? 'true' : 'false' ?>;
        var canDelete = <?= !empty($can_delete) ? 'true' : 'false' ?>;
        var baseUrl = "<?= base_url() ?>";
        var statusPalette = {
            'Listing': {
                bg: '#F3F4F6',
                color: '#475467',
                border: '#D0D5DD'
            },
            'Dealing sample tiktok': {
                bg: '#DBEAFE',
                color: '#1D4ED8',
                border: '#93C5FD'
            },
            'On process dealing': {
                bg: '#DBEAFE',
                color: '#1D4ED8',
                border: '#93C5FD'
            },
            'dealing no sampel': {
                bg: '#DBEAFE',
                color: '#1D4ED8',
                border: '#93C5FD'
            },
            'Kirim brief': {
                bg: '#DBEAFE',
                color: '#1D4ED8',
                border: '#93C5FD'
            },
            'Undang Scratch': {
                bg: '#DBEAFE',
                color: '#1D4ED8',
                border: '#93C5FD'
            },
            'Pengiriman manual': {
                bg: '#FEF3C7',
                color: '#B45309',
                border: '#FCD34D'
            },
            'Sampel Refundable': {
                bg: '#FEF3C7',
                color: '#B45309',
                border: '#FCD34D'
            },
            'Tawaran Refundable': {
                bg: '#FEF3C7',
                color: '#B45309',
                border: '#FCD34D'
            },
            'Output Video': {
                bg: '#FEF3C7',
                color: '#B45309',
                border: '#FCD34D'
            },
            'Cancel': {
                bg: '#FEE2E2',
                color: '#B91C1C',
                border: '#FCA5A5'
            },
            'kadaluwarsa': {
                bg: '#FEE2E2',
                color: '#B91C1C',
                border: '#FCA5A5'
            },
            'Utang Konten': {
                bg: '#FEE2E2',
                color: '#B91C1C',
                border: '#FCA5A5'
            },
            'Done': {
                bg: '#DCFCE7',
                color: '#15803D',
                border: '#86EFAC'
            }
        };
        var urgencyGroups = {
            neutral: {
                label: 'Neutral',
                color: '#98a2b3',
                bg: '#F3F4F6',
                statuses: ['Listing']
            },
            active: {
                label: 'Active',
                color: '#1D4ED8',
                bg: '#DBEAFE',
                statuses: ['Dealing sample tiktok', 'On process dealing', 'dealing no sampel', 'Kirim brief', 'Undang Scratch']
            },
            warning: {
                label: 'Warning',
                color: '#B45309',
                bg: '#FEF3C7',
                statuses: ['Pengiriman manual', 'Sampel Refundable', 'Tawaran Refundable', 'Output Video']
            },
            danger: {
                label: 'Urgent',
                color: '#B91C1C',
                bg: '#FEE2E2',
                statuses: ['Cancel', 'kadaluwarsa', 'Utang Konten']
            },
            success: {
                label: 'Done',
                color: '#15803D',
                bg: '#DCFCE7',
                statuses: ['Done']
            }
        };
        var keywordFieldMap = {
            'Username KOL': 'username_kol',
            'PIC Utama': 'pic_utama',
            'Product': 'product',
            'Status Creator': 'status'
        };

        function escapeHtml(value) {
            return $('<div>').text(value || '').html();
        }

        function getStatusStyle(status) {
            return statusPalette[status] || statusPalette['Listing'];
        }

        function renderStatusBadge(status) {
            var cleanStatus = $.trim(status || '');
            if (cleanStatus === '') {
                return '<span class="kol-status-empty">-</span>';
            }

            var style = getStatusStyle(cleanStatus);
            return '<span class="kol-status-badge" style="background:' + style.bg + ';color:' + style.color + ';border-color:' + style.border + ';">' + escapeHtml(cleanStatus) + '</span>';
        }

        function updateSummary(table) {
            var info = table.page.info();
            $('#kol-summary span').text(info.recordsDisplay + ' data ditemukan');
        }

        function formatPercent(value) {
            return (Math.round(value * 10) / 10).toLocaleString('id-ID', {
                minimumFractionDigits: value % 1 === 0 ? 0 : 1,
                maximumFractionDigits: 1
            });
        }

        function getFilteredRows(table) {
            return table.rows({
                search: 'applied'
            }).data().toArray();
        }

        function getUrgencyKey(status) {
            var cleanStatus = $.trim(status || '');
            var foundKey = 'neutral';

            $.each(urgencyGroups, function(key, group) {
                if (group.statuses.indexOf(cleanStatus) !== -1) {
                    foundKey = key;
                    return false;
                }
            });

            return foundKey;
        }

        function renderStatusOverview(table) {
            var rows = getFilteredRows(table);
            var total = rows.length;
            var urgencyCounts = {
                neutral: 0,
                active: 0,
                warning: 0,
                danger: 0,
                success: 0
            };
            var statusCounts = {};

            rows.forEach(function(row) {
                var status = $.trim((row.status || '').toString());
                var urgencyKey = getUrgencyKey(status);
                urgencyCounts[urgencyKey] += 1;

                if (status !== '') {
                    statusCounts[status] = (statusCounts[status] || 0) + 1;
                }
            });

            var progressSegments = '';
            $.each(urgencyGroups, function(key, group) {
                var count = urgencyCounts[key] || 0;
                var percent = total > 0 ? (count / total) * 100 : 0;
                var safeWidth = count > 0 ? Math.max(percent, 2) : 0;
                var title = group.label + ': ' + count + ' data (' + formatPercent(percent) + '%)';

                if (count > 0) {
                    progressSegments += '<div class="kol-progress-segment" title="' + escapeHtml(title) + '" style="width:' + safeWidth + '%; background:' + group.color + ';"></div>';
                }
            });

            $('#kol-progress-track').html(progressSegments || '<div class="kol-progress-segment" style="width:100%;background:#eaecf0"></div>');

            var orderedStatusEntries = Object.keys(statusCounts)
                .sort(function(a, b) {
                    return statusCounts[b] - statusCounts[a];
                });

            if (!orderedStatusEntries.length) {
                $('#kol-status-stats').html('<div class="kol-status-stat"><div class="kol-status-stat-name">Belum ada status pada hasil filter.</div></div>');
                return;
            }

            var statusCards = '';
            orderedStatusEntries.forEach(function(status) {
                var count = statusCounts[status];
                var percent = total > 0 ? (count / total) * 100 : 0;
                var style = getStatusStyle(status);
                var title = status + ': ' + count + ' data (' + formatPercent(percent) + '%)';

                statusCards +=
                    '<div class="kol-status-stat" title="' + escapeHtml(title) + '">' +
                        '<div class="kol-status-stat-top">' +
                            '<div class="kol-status-stat-name">' + escapeHtml(status) + '</div>' +
                            '<div class="kol-status-stat-count">' + count.toLocaleString('id-ID') + '</div>' +
                        '</div>' +
                        '<div class="kol-status-stat-bar"><div class="kol-status-stat-fill" style="width:' + Math.max(percent, 4) + '%; background:' + style.color + ';"></div></div>' +
                        '<div class="kol-status-stat-meta">' + formatPercent(percent) + '% dari hasil filter</div>' +
                    '</div>';
            });

            $('#kol-status-stats').html(statusCards);
        }

        $.fn.dataTable.ext.search.push(function(settings, searchData, index, rowData) {
            if (settings.nTable.id !== 'kol-affiliator-table') {
                return true;
            }

            var selectedStatus = $('#status-filter').val();
            var selectedCategory = $('#keyword-category').val();
            var keyword = $.trim($('#keyword-input').val()).toLowerCase();
            var field = keywordFieldMap[selectedCategory] || 'username_kol';
            var fieldValue = ((rowData && rowData[field]) || '').toString().toLowerCase();
            var rowStatus = ((rowData && rowData.status) || '').toString();

            if (selectedStatus !== '' && rowStatus !== selectedStatus) {
                return false;
            }

            if (keyword !== '' && fieldValue.indexOf(keyword) === -1) {
                return false;
            }

            return true;
        });

        var table = $('#kol-affiliator-table').DataTable({
            processing: true,
            serverSide: false,
            deferRender: true,
            ajax: {
                url: "<?= base_url('kol-affiliator/data') ?>",
                type: 'GET',
                dataSrc: 'data'
            },
            columns: [
                {
                    data: 'id',
                    visible: false
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center fw-semibold',
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'username_kol',
                    render: function(data, type, row) {
                        var creatorHtml = row.creator ? '<div class="kol-username-meta">Creator: ' + escapeHtml(row.creator) + '</div>' : '';
                        return '<div class="kol-username"><div class="kol-username-main">' + escapeHtml(data || '-') + '</div>' + creatorHtml + '</div>';
                    }
                },
                {
                    data: 'pic_utama',
                    render: function(data) {
                        return escapeHtml(data || '-');
                    }
                },
                {
                    data: 'product',
                    render: function(data) {
                        return escapeHtml(data || '-');
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        return renderStatusBadge(data);
                    }
                },
                {
                    data: 'total_posts',
                    className: 'text-center fw-semibold',
                    render: function(data, type) {
                        var total = parseInt(data, 10) || 0;
                        return type === 'sort' || type === 'type' ? total : total.toLocaleString('id-ID');
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function(data) {
                        var actions = '';
                        actions += '<a href="' + baseUrl + 'kol-affiliator/detail?id=' + data + '" class="kol-action-link kol-action-view" title="Detail"><i class="bi bi-eye"></i></a>';

                        if (canEdit) {
                            actions += '<a href="' + baseUrl + 'kol-affiliator/edit-page?id=' + data + '" class="kol-action-link kol-action-edit" title="Edit"><i class="bi bi-pencil"></i></a>';
                        }

                        if (canDelete) {
                            actions += '<a href="#!" onclick="removeCampaign(' + data + ')" class="kol-action-link kol-action-delete" title="Hapus"><i class="bi bi-trash"></i></a>';
                        }

                        return actions;
                    }
                }
            ],
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [10, 20, 50, 100, 500],
            dom: "<'row align-items-center px-3 pt-3'<'col-sm-12 col-md-6'><'col-sm-12 col-md-6 d-flex justify-content-md-end mt-2 mt-md-0'l>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row align-items-center px-3 pb-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-md-end'p>>",
            language: {
                processing: "Memuat data...",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                zeroRecords: "Tidak ada data yang ditemukan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                paginate: {
                    first: '<i class="bi bi-chevron-double-left"></i>',
                    last: '<i class="bi bi-chevron-double-right"></i>',
                    next: '<i class="bi bi-chevron-right"></i>',
                    previous: '<i class="bi bi-chevron-left"></i>'
                }
            },
            initComplete: function() {
                updateSummary(this.api());
                renderStatusOverview(this.api());
            },
            drawCallback: function() {
                updateSummary(this.api());
                renderStatusOverview(this.api());
            }
        });

        var keywordTimer = null;

        function applyFilters() {
            table.draw();
        }

        $('#kol-filter-form').on('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });

        $('#keyword-category, #status-filter').on('change', function() {
            applyFilters();
        });

        $('#keyword-input').on('input', function() {
            clearTimeout(keywordTimer);
            keywordTimer = setTimeout(function() {
                applyFilters();
            }, 180);
        });

        $('#reset-filter').on('click', function() {
            $('#keyword-category').val('Username KOL');
            $('#keyword-input').val('');
            $('#status-filter').val('');
            applyFilters();
        });
    });
</script>
