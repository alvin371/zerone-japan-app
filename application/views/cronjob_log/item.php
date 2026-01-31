<?php
if (empty($data)) {
    echo '<tr><td colspan="9" class="text-center text-muted py-4">No log entries found</td></tr>';
    return;
}

$offset = ($current_page - 1) * $limit;

foreach ($data as $index => $row):
    $status_badge = [
        'pending' => 'badge-pending',
        'running' => 'badge-running',
        'completed' => 'badge-completed',
        'failed' => 'badge-failed'
    ];

    $trigger_badge = [
        'scheduled' => 'badge-scheduled',
        'manual' => 'badge-manual',
        'webhook' => 'badge-webhook'
    ];

    $status_class = $status_badge[$row['status']] ?? 'badge-secondary';
    $trigger_class = $trigger_badge[$row['trigger_source']] ?? 'badge-secondary';

    // Calculate progress
    $total = (int)($row['total_items'] ?? 0);
    $processed = (int)($row['processed_items'] ?? 0);
    $failed = (int)($row['failed_items'] ?? 0);
    $skipped = (int)($row['skipped_items'] ?? 0);

    // Format duration
    $duration = $row['duration_seconds'];
    if ($duration !== null) {
        if ($duration < 60) {
            $duration_text = number_format($duration, 2) . 's';
        } elseif ($duration < 3600) {
            $duration_text = floor($duration / 60) . 'm ' . number_format($duration % 60, 0) . 's';
        } else {
            $duration_text = floor($duration / 3600) . 'h ' . floor(($duration % 3600) / 60) . 'm';
        }
    } else {
        $duration_text = '-';
    }

    // Format started_at
    $started_at = $row['started_at'] ? date('Y-m-d H:i:s', strtotime($row['started_at'])) : '-';
?>
<tr>
    <td><?= $offset + $index + 1 ?></td>
    <td>
        <strong><?= htmlspecialchars($row['job_name'] ?? '') ?></strong>
        <?php if (!empty($row['error_message'])): ?>
            <br><small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Has error</small>
        <?php endif; ?>
    </td>
    <td>
        <span class="badge bg-light text-dark"><?= htmlspecialchars($row['job_type'] ?? '') ?></span>
    </td>
    <td>
        <span class="badge-status <?= $status_class ?>"><?= ucfirst($row['status'] ?? '') ?></span>
    </td>
    <td>
        <span class="badge-trigger <?= $trigger_class ?>"><?= ucfirst($row['trigger_source'] ?? '') ?></span>
    </td>
    <td>
        <small><?= $started_at ?></small>
        <?php if (!empty($row['triggered_by_name'])): ?>
            <br><small class="text-muted">by <?= htmlspecialchars($row['triggered_by_name']) ?></small>
        <?php endif; ?>
    </td>
    <td>
        <?php if ($row['status'] === 'running'): ?>
            <span class="text-info"><i class="bi bi-hourglass-split me-1"></i>Running...</span>
        <?php else: ?>
            <?= $duration_text ?>
        <?php endif; ?>
    </td>
    <td>
        <?php if ($total > 0 || $processed > 0 || $failed > 0): ?>
            <div class="progress-text">
                <span class="success"><?= number_format($processed) ?></span> /
                <span class="failed"><?= number_format($failed) ?></span> /
                <span class="skipped"><?= number_format($skipped) ?></span>
                <?php if ($total > 0): ?>
                    <small class="text-muted">of <?= number_format($total) ?></small>
                <?php endif; ?>
            </div>
            <?php if ($total > 0): ?>
                <?php
                $success_percent = min(100, ($processed / $total) * 100);
                $fail_percent = min(100 - $success_percent, ($failed / $total) * 100);
                ?>
                <div class="progress" style="height: 4px; margin-top: 4px;">
                    <div class="progress-bar bg-success" style="width: <?= $success_percent ?>%"></div>
                    <div class="progress-bar bg-danger" style="width: <?= $fail_percent ?>%"></div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <span class="text-muted">-</span>
        <?php endif; ?>
    </td>
    <td>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="showDetail(<?= $row['id'] ?>)">
            <i class="bi bi-eye"></i>
        </button>
    </td>
</tr>
<?php endforeach; ?>
