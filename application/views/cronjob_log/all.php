<style>
    .stat-card {
        background: #fff;
        border-radius: 8px;
        padding: 16px;
        border: 1px solid #e9ecef;
        text-align: center;
    }
    .stat-card .stat-value {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .stat-card .stat-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
    }
    .stat-card.completed .stat-value { color: #28a745; }
    .stat-card.failed .stat-value { color: #dc3545; }
    .stat-card.running .stat-value { color: #17a2b8; }
    .stat-card.pending .stat-value { color: #ffc107; }

    .badge-status {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-pending { background: #fff3cd; color: #856404; }
    .badge-running { background: #d1ecf1; color: #0c5460; }
    .badge-completed { background: #d4edda; color: #155724; }
    .badge-failed { background: #f8d7da; color: #721c24; }

    .badge-trigger {
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11px;
    }
    .badge-scheduled { background: #e2e3e5; color: #383d41; }
    .badge-manual { background: #cce5ff; color: #004085; }
    .badge-webhook { background: #d4edda; color: #155724; }

    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        font-size: 13px;
    }
    .table tbody td {
        vertical-align: middle;
        font-size: 13px;
    }

    .filter-form .form-control,
    .filter-form .form-select {
        font-size: 13px;
        height: 36px;
    }
    .filter-form label {
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .progress-text {
        font-size: 12px;
    }
    .progress-text .success { color: #28a745; }
    .progress-text .failed { color: #dc3545; }
    .progress-text .skipped { color: #6c757d; }

    .sortable-header {
        cursor: pointer;
        user-select: none;
    }
    .sortable-header:hover {
        background-color: #e9ecef !important;
    }
    .sortable-header i {
        font-size: 10px;
        margin-left: 4px;
    }
</style>

<div class="container-fluid px-4">
    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['total']) ?></div>
                <div class="stat-label">Total Runs</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card completed">
                <div class="stat-value"><?= number_format($stats['completed']) ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card failed">
                <div class="stat-value"><?= number_format($stats['failed']) ?></div>
                <div class="stat-label">Failed</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card running">
                <div class="stat-value"><?= number_format($stats['running']) ?></div>
                <div class="stat-label">Running</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['avg_duration'], 1) ?>s</div>
                <div class="stat-label">Avg Duration</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['total_processed']) ?></div>
                <div class="stat-label">Items Processed</div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= base_url('cronjob-log') ?>" class="filter-form">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="job_name">Job Name</label>
                        <select name="job_name" id="job_name" class="form-select">
                            <option value="">All Jobs</option>
                            <?php foreach ($job_names as $jn): ?>
                                <option value="<?= htmlspecialchars($jn['job_name']) ?>" <?= $job_name == $jn['job_name'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($jn['job_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="running" <?= $status == 'running' ? 'selected' : '' ?>>Running</option>
                            <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="failed" <?= $status == 'failed' ? 'selected' : '' ?>>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="job_type">Job Type</label>
                        <select name="job_type" id="job_type" class="form-select">
                            <option value="">All Types</option>
                            <?php foreach ($job_types as $jt): ?>
                                <option value="<?= htmlspecialchars($jt['job_type']) ?>" <?= $job_type == $jt['job_type'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($jt['job_type']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="trigger_source">Trigger</label>
                        <select name="trigger_source" id="trigger_source" class="form-select">
                            <option value="">All Triggers</option>
                            <option value="scheduled" <?= $trigger_source == 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                            <option value="manual" <?= $trigger_source == 'manual' ? 'selected' : '' ?>>Manual</option>
                            <option value="webhook" <?= $trigger_source == 'webhook' ? 'selected' : '' ?>>Webhook</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="until_date">End Date</label>
                        <input type="date" name="until_date" id="until_date" class="form-control" value="<?= htmlspecialchars($until_date) ?>">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                        <a href="<?= base_url('cronjob-log') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm float-end" onclick="showClearLogsModal()">
                            <i class="bi bi-trash me-1"></i> Clear Old Logs
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-body p-0">
            <?= $notif ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th class="sortable-header" data-sort="job_name">
                                Job Name <i class="bi bi-arrow-down-up"></i>
                            </th>
                            <th width="80">Type</th>
                            <th width="100" class="sortable-header" data-sort="status">
                                Status <i class="bi bi-arrow-down-up"></i>
                            </th>
                            <th width="80">Trigger</th>
                            <th width="150" class="sortable-header" data-sort="started_at">
                                Started At <i class="bi bi-arrow-down-up"></i>
                            </th>
                            <th width="90" class="sortable-header" data-sort="duration_seconds">
                                Duration <i class="bi bi-arrow-down-up"></i>
                            </th>
                            <th width="150">Progress</th>
                            <th width="80">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tbody">
                        <!-- Content loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0 justify-content-end" id="pagination">
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Clear Logs Modal -->
<div class="modal fade" id="clearLogsModal" tabindex="-1" aria-labelledby="clearLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clearLogsModalLabel">Clear Old Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Delete log entries older than:</p>
                <div class="input-group">
                    <input type="number" class="form-control" id="clearDays" value="30" min="1">
                    <span class="input-group-text">days</span>
                </div>
                <p class="text-muted small mt-2">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="clearLogs()">Delete Logs</button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= base_url() ?>';
let currentPage = <?= $current_page ?>;
let totalPages = <?= $page ?>;
let sortBy = '<?= $sort_by ?? 'started_at' ?>';
let sortDir = '<?= $sort_dir ?? 'DESC' ?>';

document.addEventListener('DOMContentLoaded', function() {
    loadData();
    initSortableHeaders();
});

function loadData() {
    const params = new URLSearchParams(window.location.search);
    params.set('page', currentPage);
    params.set('sort_by', sortBy);
    params.set('sort_dir', sortDir);

    fetch(`${BASE_URL}cronjob-log/item?${params.toString()}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('tbody').innerHTML = html;
            renderPagination();
        })
        .catch(error => {
            console.error('Error loading data:', error);
            document.getElementById('tbody').innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error loading data</td></tr>';
        });
}

function initSortableHeaders() {
    document.querySelectorAll('.sortable-header').forEach(header => {
        header.addEventListener('click', function() {
            const field = this.dataset.sort;
            if (sortBy === field) {
                sortDir = sortDir === 'ASC' ? 'DESC' : 'ASC';
            } else {
                sortBy = field;
                sortDir = 'DESC';
            }
            currentPage = 1;
            loadData();
            updateSortIcons();
        });
    });
}

function updateSortIcons() {
    document.querySelectorAll('.sortable-header').forEach(header => {
        const icon = header.querySelector('i');
        if (header.dataset.sort === sortBy) {
            icon.className = sortDir === 'ASC' ? 'bi bi-arrow-up' : 'bi bi-arrow-down';
        } else {
            icon.className = 'bi bi-arrow-down-up';
        }
    });
}

function renderPagination() {
    const pagination = document.getElementById('pagination');
    let html = '';

    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }

    // Previous
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="goToPage(${currentPage - 1}); return false;">Prev</a>
    </li>`;

    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(1); return false;">1</a></li>`;
        if (startPage > 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>
        </li>`;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        html += `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(${totalPages}); return false;">${totalPages}</a></li>`;
    }

    // Next
    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="goToPage(${currentPage + 1}); return false;">Next</a>
    </li>`;

    pagination.innerHTML = html;
}

function goToPage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    loadData();
}

function showDetail(id) {
    fetch(`${BASE_URL}cronjob-log/detail?id=${id}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detailModalBody').innerHTML = html;
            new bootstrap.Modal(document.getElementById('detailModal')).show();
        })
        .catch(error => {
            console.error('Error loading detail:', error);
            alert('Error loading log details');
        });
}

function showClearLogsModal() {
    new bootstrap.Modal(document.getElementById('clearLogsModal')).show();
}

function clearLogs() {
    const days = document.getElementById('clearDays').value;

    if (!days || days < 1) {
        alert('Please enter a valid number of days');
        return;
    }

    fetch(`${BASE_URL}cronjob-log/clear`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `days=${days}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('clearLogsModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Error clearing logs');
        }
    })
    .catch(error => {
        console.error('Error clearing logs:', error);
        alert('Error clearing logs');
    });
}
</script>
