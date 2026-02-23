<div class="container-fluid py-3">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0" style="color: rgba(0, 0, 0, 0.85);">Detail KOL & Affiliator</h5>
                <div class="d-flex gap-2">
                    <?php if (!empty($can_edit)): ?>
                        <a href="<?= base_url('kol-affiliator/edit-page?id=' . $data['id']) ?>" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('kol-affiliator') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <?php
                $fields = [
                    'Username KOL' => $data['username_kol'] ?? '-',
                    'PIC Utama' => $data['pic_utama'] ?? '-',
                    'Creator' => $data['creator'] ?? '-',
                    'Status Creator' => $data['status'] ?? '-',
                    'Product' => $data['product'] ?? '-',
                    'Platform' => $data['platform'] ?? '-',
                    'Niche' => $data['niche'] ?? '-',
                    'Spesifikasi' => $data['spesifikasi'] ?? '-',
                    'Output Video' => $data['output_video'] ?? '-',
                    'Toko' => $data['toko'] ?? '-',
                    'Kategori' => $data['kategori'] ?? '-',
                    'Tanggal Blast' => $data['tanggal_blast'] ?? '-',
                    'Tanggal Acc Sample' => $data['tanggal_acc_sample'] ?? '-',
                    'Actual Posting' => $data['actual_posting'] ?? '-',
                    'Nama Penerima' => $data['nama_penerima'] ?? '-',
                    'No HP' => $data['no_hp'] ?? '-',
                    'Alamat' => $data['alamat'] ?? '-',
                    'Keterangan' => $data['keterangan'] ?? '-'
                ];
                foreach ($fields as $label => $value):
                ?>
                    <div class="col-md-4">
                        <label class="form-label text-muted mb-1"><?= $label ?></label>
                        <div style="font-weight:500;"><?= htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Data Post</h6>
                <?php if (!empty($can_edit)): ?>
                    <button type="button" class="btn btn-outline-secondary" onclick="syncCampaignViews()">
                        <i class="bi bi-arrow-repeat"></i> Sync Semua Views
                    </button>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th style="width:170px;">Tanggal Post</th>
                            <th>Link Post</th>
                            <th class="text-end" style="width:140px;">Views</th>
                            <?php if (!empty($can_edit)): ?>
                                <th class="text-center" style="width:90px;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="<?= !empty($can_edit) ? 5 : 4 ?>" class="text-center text-muted">Belum ada post</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($posts as $idx => $post): ?>
                                <tr id="post-row-<?= (int)$post['id'] ?>">
                                    <td><?= $idx + 1 ?></td>
                                    <td class="post-date"><?= htmlspecialchars($post['tanggal_post'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php if (!empty($post['link_post'])): ?>
                                            <a href="<?= htmlspecialchars($post['link_post'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                                                <?= htmlspecialchars($post['link_post'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end post-views"><?= number_format((int)($post['views'] ?? 0), 0, ',', '.') ?></td>
                                    <?php if (!empty($can_edit)): ?>
                                        <td class="text-center">
                                            <a href="#" onclick="syncPost(<?= (int)$post['id'] ?>); return false;" title="Sync views">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($can_edit)): ?>
<script>
    function syncPost(postId) {
        $.ajax({
            type: 'POST',
            url: "<?= base_url('kol-affiliator/sync-post-views') ?>",
            dataType: 'json',
            data: { post_id: postId },
            success: function(res) {
                if (!res || !res.success) {
                    Swal.fire({ icon: 'warning', text: (res && res.message) ? res.message : 'Sync gagal' });
                    return;
                }

                const row = $('#post-row-' + postId);
                row.find('.post-views').text((res.data.views || 0).toLocaleString('id-ID'));
                if (res.data.tanggal_post) {
                    row.find('.post-date').text(res.data.tanggal_post);
                }

                Swal.fire({ icon: 'success', text: res.message, timer: 1200, showConfirmButton: false });
            },
            error: function() {
                Swal.fire({ icon: 'error', text: 'Terjadi kesalahan saat sync views.' });
            }
        });
    }

    function syncCampaignViews() {
        $.ajax({
            type: 'POST',
            url: "<?= base_url('kol-affiliator/sync-campaign-views') ?>",
            dataType: 'json',
            data: { campaign_id: "<?= (int)$data['id'] ?>" },
            success: function(res) {
                if (!res || !res.success) {
                    Swal.fire({ icon: 'warning', text: (res && res.message) ? res.message : 'Sync gagal' });
                    return;
                }

                Swal.fire({ icon: 'success', text: res.message, timer: 1200, showConfirmButton: false });
                setTimeout(function() {
                    window.location.reload();
                }, 1200);
            },
            error: function() {
                Swal.fire({ icon: 'error', text: 'Terjadi kesalahan saat sync campaign views.' });
            }
        });
    }
</script>
<?php endif; ?>
