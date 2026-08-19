<?php
$total_group = count($groups);
$total_item = 0;
$cross_campaign = 0;
foreach ($groups as $group) {
    $total_item += count($group['items']);
    if ($group['campaign_count'] > 1) {
        $cross_campaign += 1;
    }
}
?>
<?php if ($id_campaign <= 0) { ?>
    <div class="alert alert-danger mb-0">Campaign tidak ditemukan.</div>
<?php } else if ($total_group == 0) { ?>
    <div class="alert alert-success mb-0">
        <div class="fw-600"><i class="bi bi-check-circle me-1"></i> Tidak ada duplicate post</div>
        <div class="small"><?= $checked_count ?> konten pada campaign ini dicek dan tidak ada yang terpakai ganda.</div>
    </div>
<?php } else { ?>
    <div class="alert alert-warning">
        <div class="fw-600"><i class="bi bi-copy me-1"></i> <?= $total_group ?> konten terindikasi duplicate</div>
        <div class="small">
            <?= $total_item ?> baris endorse memakai konten yang sama<?= $cross_campaign > 0 ? ', ' . $cross_campaign . ' di antaranya lintas campaign' : '' ?>.
            Total <?= $checked_count ?> konten dicek pada campaign <?= html_escape((string) $campaign_title) ?>.
        </div>
    </div>

    <?php foreach ($groups as $group) { ?>
        <div class="card mb-3">
            <div class="row">
                <div class="col-lg-12">
                    <p class="mb-1 fw-600 text-black">
                        <?= html_escape((string) $group['platform']) ?>
                        <span class="badge bg-secondary ms-1"><?= count($group['items']) ?> endorse</span>
                        <span class="badge bg-<?= $group['campaign_count'] > 1 ? 'danger' : 'warning' ?> ms-1">
                            <?= $group['campaign_count'] > 1 ? $group['campaign_count'] . ' campaign' : 'Dalam campaign ini' ?>
                        </span>
                    </p>
                    <p class="mb-2 small text-truncate">
                        <a href="<?= html_escape((string) $group['link_upload']) ?>" target="_blank" rel="noopener noreferrer">
                            <?= html_escape((string) $group['link_upload']) ?>
                        </a>
                    </p>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Campaign</th>
                                    <th>Creator</th>
                                    <th>Status</th>
                                    <th>Tgl Posting</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($group['items'] as $item) {
                                    $campaign_title_item = trim((string) $item['campaign_title']);
                                    if ($campaign_title_item === '') {
                                        $campaign_title_item = 'Campaign #' . intval($item['id_campaign']);
                                    }
                                    $posting_at = !empty($item['posting_at']) ? date('d/m/Y', strtotime($item['posting_at'])) : '-';
                                ?>
                                    <tr>
                                        <td>
                                            <a href="<?= base_url() ?>endorse?id_campaign=<?= intval($item['id_campaign']) ?>" class="fw-600 text-decoration-none">
                                                <?= html_escape($campaign_title_item) ?>
                                            </a>
                                            <?php if ($item['is_current_campaign']) { ?>
                                                <span class="badge bg-primary ms-1">Campaign ini</span>
                                            <?php } ?>
                                            <div class="small text-muted">Campaign: <?= html_escape((string) $item['campaign_status']) ?></div>
                                        </td>
                                        <td><?= html_escape((string) $item['nama_creator']) ?></td>
                                        <td>
                                            <div><?= html_escape((string) $item['status_endorse']) ?></div>
                                            <div class="small text-muted">Data: <?= html_escape((string) $item['status']) ?></div>
                                        </td>
                                        <td><?= $posting_at ?></td>
                                        <td class="text-end">
                                            <a href="<?= base_url() ?>endorse/detail?id=<?= intval($item['id']) ?>" class="btn btn-sm btn-outline-primary mb-1">Detail</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
<?php } ?>
