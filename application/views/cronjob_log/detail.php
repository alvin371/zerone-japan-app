<?php
$log = $log ?? [];

$status_badge = [
    'pending' => 'badge-pending',
    'running' => 'badge-running',
    'completed' => 'badge-completed',
    'failed' => 'badge-failed'
];

$status_class = $status_badge[$log['status']] ?? 'badge-secondary';

// Format duration
$duration = $log['duration_seconds'] ?? null;
if ($duration !== null) {
    if ($duration < 60) {
        $duration_text = number_format($duration, 3) . ' seconds';
    } elseif ($duration < 3600) {
        $duration_text = floor($duration / 60) . 'm ' . number_format($duration % 60, 0) . 's';
    } else {
        $duration_text = floor($duration / 3600) . 'h ' . floor(($duration % 3600) / 60) . 'm ' . number_format($duration % 60, 0) . 's';
    }
} else {
    $duration_text = '-';
}

// Parse details JSON
$details = null;
if (!empty($log['details'])) {
    $details = json_decode($log['details'], true);
}
?>

<style>
    .detail-table th {
        width: 150px;
        background-color: #f8f9fa;
        font-weight: 500;
    }
    .detail-table td, .detail-table th {
        padding: 10px 12px;
        border: 1px solid #dee2e6;
    }
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
    .error-box {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
        padding: 12px;
        color: #721c24;
        font-family: monospace;
        font-size: 13px;
        white-space: pre-wrap;
        word-break: break-word;
        max-height: 200px;
        overflow-y: auto;
    }
    .details-box {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 12px;
        font-family: monospace;
        font-size: 12px;
        max-height: 300px;
        overflow-y: auto;
    }
    .details-box pre {
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
    }
</style>

<table class="table detail-table mb-4">
    <tr>
        <th>ID</th>
        <td><?= htmlspecialchars($log['id'] ?? '') ?></td>
    </tr>
    <tr>
        <th>Job Name</th>
        <td><strong><?= htmlspecialchars($log['job_name'] ?? '') ?></strong></td>
    </tr>
    <tr>
        <th>Job Type</th>
        <td><span class="badge bg-light text-dark"><?= htmlspecialchars($log['job_type'] ?? '') ?></span></td>
    </tr>
    <tr>
        <th>Status</th>
        <td><span class="badge-status <?= $status_class ?>"><?= ucfirst($log['status'] ?? '') ?></span></td>
    </tr>
    <tr>
        <th>Trigger Source</th>
        <td><?= ucfirst($log['trigger_source'] ?? '') ?></td>
    </tr>
    <tr>
        <th>Triggered By</th>
        <td>
            <?php if (!empty($log['triggered_by_name'])): ?>
                <?= htmlspecialchars($log['triggered_by_name']) ?> (ID: <?= $log['triggered_by'] ?>)
            <?php elseif (!empty($log['triggered_by'])): ?>
                User ID: <?= $log['triggered_by'] ?>
            <?php else: ?>
                <span class="text-muted">System/Scheduler</span>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th>IP Address</th>
        <td><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
    </tr>
</table>

<h6 class="mb-3">Timing</h6>
<table class="table detail-table mb-4">
    <tr>
        <th>Started At</th>
        <td><?= $log['started_at'] ? date('Y-m-d H:i:s', strtotime($log['started_at'])) : '-' ?></td>
    </tr>
    <tr>
        <th>Completed At</th>
        <td><?= $log['completed_at'] ? date('Y-m-d H:i:s', strtotime($log['completed_at'])) : '-' ?></td>
    </tr>
    <tr>
        <th>Duration</th>
        <td><?= $duration_text ?></td>
    </tr>
</table>

<h6 class="mb-3">Processing Stats</h6>
<table class="table detail-table mb-4">
    <tr>
        <th>Total Items</th>
        <td><?= number_format($log['total_items'] ?? 0) ?></td>
    </tr>
    <tr>
        <th>Processed</th>
        <td><span class="text-success"><?= number_format($log['processed_items'] ?? 0) ?></span></td>
    </tr>
    <tr>
        <th>Failed</th>
        <td><span class="text-danger"><?= number_format($log['failed_items'] ?? 0) ?></span></td>
    </tr>
    <tr>
        <th>Skipped</th>
        <td><span class="text-muted"><?= number_format($log['skipped_items'] ?? 0) ?></span></td>
    </tr>
</table>

<?php if (!empty($log['error_message'])): ?>
<h6 class="mb-3 text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Error Message</h6>
<div class="error-box mb-4">
<?= htmlspecialchars($log['error_message']) ?>
</div>
<?php endif; ?>

<?php if ($details !== null): ?>
<h6 class="mb-3">Additional Details</h6>
<div class="details-box">
<pre><?= htmlspecialchars(json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
</div>
<?php endif; ?>

<div class="mt-4">
    <small class="text-muted">
        Created: <?= $log['created_at'] ? date('Y-m-d H:i:s', strtotime($log['created_at'])) : '-' ?> |
        Updated: <?= $log['updated_at'] ? date('Y-m-d H:i:s', strtotime($log['updated_at'])) : '-' ?>
    </small>
</div>
