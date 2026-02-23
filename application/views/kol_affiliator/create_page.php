<div class="container-fluid py-3">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="color: rgba(0, 0, 0, 0.85);">Create KOL & Affiliator</h5>
                <a href="<?= base_url('kol-affiliator') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="form-message"></div>

            <form action="<?= base_url('kol-affiliator/store') ?>" method="POST" id="form-kol">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Blast</label>
                        <input type="date" class="form-control" name="dt[tanggal_blast]" value="<?= $data['tanggal_blast'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Acc Sample</label>
                        <input type="date" class="form-control" name="dt[tanggal_acc_sample]" value="<?= $data['tanggal_acc_sample'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Actual Posting</label>
                        <input type="date" class="form-control" name="dt[actual_posting]" value="<?= $data['actual_posting'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Platform</label>
                        <select class="form-select" name="dt[platform]">
                            <option value="">Pilih Platform</option>
                            <?php foreach ($platform_options as $opt): ?>
                                <option value="<?= $opt ?>" <?= (($data['platform'] ?? '') == $opt ? 'selected' : '') ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Username KOL <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="dt[username_kol]" value="<?= htmlspecialchars($data['username_kol'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">PIC Utama</label>
                        <input type="text" class="form-control" name="dt[pic_utama]" value="<?= htmlspecialchars($data['pic_utama'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Creator</label>
                        <input type="text" class="form-control" name="dt[creator]" value="<?= htmlspecialchars($data['creator'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control" name="dt[product]" value="<?= htmlspecialchars($data['product'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status Creator</label>
                        <select class="form-select" name="dt[status]">
                            <option value="">Pilih Status Creator</option>
                            <?php foreach ($status_creator_options as $opt): ?>
                                <option value="<?= $opt ?>" <?= (($data['status'] ?? '') == $opt ? 'selected' : '') ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Spesifikasi</label>
                        <select class="form-select" name="dt[spesifikasi]">
                            <option value="">Pilih Spesifikasi</option>
                            <?php foreach ($spesifikasi_options as $opt): ?>
                                <option value="<?= $opt ?>" <?= (($data['spesifikasi'] ?? '') == $opt ? 'selected' : '') ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Output Video</label>
                        <select class="form-select" name="dt[output_video]">
                            <option value="">Pilih Output</option>
                            <?php foreach ($output_video_options as $opt): ?>
                                <option value="<?= $opt ?>" <?= (($data['output_video'] ?? '') == $opt ? 'selected' : '') ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Toko</label>
                        <select class="form-select" name="dt[toko]">
                            <option value="">Pilih Toko</option>
                            <?php foreach ($toko_options as $opt): ?>
                                <option value="<?= $opt ?>" <?= (($data['toko'] ?? '') == $opt ? 'selected' : '') ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Niche</label>
                        <input type="text" class="form-control" name="dt[niche]" value="<?= htmlspecialchars($data['niche'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori</label>
                        <input type="text" class="form-control" name="dt[kategori]" value="<?= htmlspecialchars($data['kategori'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nama Penerima</label>
                        <input type="text" class="form-control" name="dt[nama_penerima]" value="<?= htmlspecialchars($data['nama_penerima'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No HP</label>
                        <input type="text" class="form-control" name="dt[no_hp]" value="<?= htmlspecialchars($data['no_hp'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" rows="2" name="dt[alamat]"><?= htmlspecialchars($data['alamat'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" rows="2" name="dt[keterangan]"><?= htmlspecialchars($data['keterangan'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Data Post</h6>
                    <button type="button" class="btn btn-outline-secondary" onclick="addPostRow()">
                        <i class="bi bi-plus"></i> Tambah Post
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 180px;">Tanggal Post</th>
                                <th>Link Post</th>
                                <th style="width: 140px;">Views</th>
                                <th style="width: 90px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="posts-body"></tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-send">
                        <i class="bi bi-save me-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let postIndex = 0;

    function addPostRow(item = {}) {
        const rowId = 'post-row-' + postIndex;
        const postId = item.id ? parseInt(item.id, 10) : 0;

        const html = `
            <tr id="${rowId}" data-post-id="${postId}">
                <td>
                    <input type="hidden" name="posts[${postIndex}][id]" value="${postId}">
                    <input type="date" class="form-control" name="posts[${postIndex}][tanggal_post]" value="${item.tanggal_post || ''}">
                </td>
                <td>
                    <input type="text" class="form-control" name="posts[${postIndex}][link_post]" value="${item.link_post || ''}" placeholder="https://...">
                </td>
                <td>
                    <input type="number" min="0" class="form-control" name="posts[${postIndex}][views]" value="${item.views || 0}">
                </td>
                <td class="text-center">
                    <a href="#" class="text-danger" onclick="removePostRow('${rowId}'); return false;" title="Hapus row">
                        <i class="bi bi-trash"></i>
                    </a>
                </td>
            </tr>
        `;

        $('#posts-body').append(html);
        postIndex++;
    }

    function removePostRow(rowId) {
        $('#' + rowId).remove();
    }

    $('#form-kol').submit(function() {
        const form = $(this);
        const payload = new FormData(this);

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: payload,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('.btn-send').addClass('disabled').attr('disabled', true).html('<div class="spinner-border spinner-border-sm text-white me-2" role="status"></div>Menyimpan...');
                form.find('.form-message').slideUp().html('');
            },
            success: function(response) {
                const str = String(response || '');
                $('.form-message').hide().html(response).slideDown('fast');

                if (str.toLowerCase().indexOf('success') !== -1) {
                    setTimeout(function() {
                        window.location.href = "<?= base_url('kol-affiliator') ?>";
                    }, 1200);
                    return;
                }

                $('.btn-send').removeClass('disabled').attr('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Data');
            },
            error: function(xhr) {
                $('.btn-send').removeClass('disabled').attr('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Data');
                $('.form-message').hide().html(xhr.responseText || 'Terjadi kesalahan').slideDown('fast');
            }
        });

        return false;
    });

    $(document).ready(function() {
        addPostRow();
    });
</script>
