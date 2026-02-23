<?php
if (empty($data)) {
    echo '<tbody><tr><td colspan="7" class="text-center text-muted">Tidak ada data</td></tr></tbody>';
    return;
}

$k = $start;
foreach ($data as $row):
?>
    <tbody>
        <tr>
            <td><strong><?= $k + 1 ?></strong></td>
            <td>
                <div style="font-weight: 500; color: #1890ff;"><?= htmlspecialchars($row['username_kol'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                <?php if (!empty($row['creator'])): ?>
                    <small class="text-muted">Creator: <?= htmlspecialchars($row['creator'], ENT_QUOTES, 'UTF-8') ?></small>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['pic_utama'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($row['product'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <?php if (!empty($row['status'])): ?>
                    <span class="badge bg-primary"><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                    <span class="text-muted">-</span>
                <?php endif; ?>
            </td>
            <td class="text-center"><?= (int)($row['total_posts'] ?? 0) ?></td>
            <td class="text-end">
                <a href="<?= base_url('kol-affiliator/detail?id=' . $row['id']) ?>"
                   class="me-2" style="color: #1890ff; text-decoration:none;" title="Detail">
                    <i class="bi bi-eye"></i>
                </a>

                <?php if (!empty($can_edit)): ?>
                    <a href="<?= base_url('kol-affiliator/edit-page?id=' . $row['id']) ?>"
                       class="me-2" style="color: #1890ff; text-decoration:none;" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                <?php endif; ?>

                <?php if (!empty($can_delete)): ?>
                    <a href="#!" onclick="removeCampaign('<?= $row['id'] ?>')"
                       style="color: #ff4d4f; text-decoration:none;" title="Hapus">
                        <i class="bi bi-trash"></i>
                    </a>
                <?php endif; ?>
            </td>
        </tr>
    </tbody>
<?php
$k++;
endforeach;
