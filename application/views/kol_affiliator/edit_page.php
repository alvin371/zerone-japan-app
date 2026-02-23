<div class="container-fluid py-3">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="color: rgba(0, 0, 0, 0.85);">Edit KOL & Affiliator</h5>
                <a href="<?= base_url('kol-affiliator') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="form-message"></div>

            <form action="<?= base_url('kol-affiliator/update') ?>" method="POST" id="form-kol">
                <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">

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
                        <input type="hidden" id="pic-utama-value" name="dt[pic_utama]" value="<?= htmlspecialchars($data['pic_utama'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <select id="pic-utama-select" class="form-select"></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Creator</label>
                        <input type="text" class="form-control" name="dt[creator]" value="<?= htmlspecialchars($data['creator'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Product</label>
                        <input type="hidden" id="product-value" name="dt[product]" value="<?= htmlspecialchars($data['product'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <select id="product-select" class="form-select"></select>
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
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="syncCampaignViews()">
                            <i class="bi bi-arrow-repeat"></i> Sync Semua Views
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="addPostRow()">
                            <i class="bi bi-plus"></i> Tambah Post
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 180px;">Tanggal Post</th>
                                <th>Link Post</th>
                                <th style="width: 140px;">Views</th>
                                <th style="width: 130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="posts-body"></tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-send">
                        <i class="bi bi-save me-1"></i> Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let postIndex = 0;
    const existingPosts = <?= json_encode($posts ?? [], JSON_UNESCAPED_UNICODE) ?>;

    function initPicUtamaSelect() {
        const $select = $('#pic-utama-select');
        const $hidden = $('#pic-utama-value');
        const initialValue = ($hidden.val() || '').trim();

        if (initialValue !== '') {
            $select.append(new Option(initialValue, initialValue, true, true));
        }

        $select.select2({
            width: '100%',
            allowClear: true,
            placeholder: 'Pilih PIC utama...',
            minimumInputLength: 0,
            ajax: {
                url: "<?= base_url('kol-affiliator/search-users') ?>",
                dataType: 'json',
                delay: 200,
                data: function(params) {
                    return {
                        q: params.term || '',
                        page: params.page || 1
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results || [],
                        pagination: (data.pagination || {
                            more: false
                        })
                    };
                }
            },
            language: {
                noResults: function() {
                    return 'User tidak ditemukan';
                }
            }
        });

        $select.on('select2:select', function(e) {
            const item = e.params && e.params.data ? e.params.data : {};
            $hidden.val(item.full_name || item.text || '');
        });

        $select.on('change', function() {
            if (!$select.val()) {
                $hidden.val('');
            }
        });
    }

    function initProductSelect() {
        const $select = $('#product-select');
        const $hidden = $('#product-value');
        const initialValue = ($hidden.val() || '').trim();

        if (initialValue !== '') {
            $select.append(new Option(initialValue, initialValue, true, true));
        }

        $select.select2({
            width: '100%',
            allowClear: true,
            placeholder: 'Cari product...',
            minimumInputLength: 0,
            ajax: {
                url: "<?= base_url('kol-affiliator/search-products') ?>",
                dataType: 'json',
                delay: 200,
                data: function(params) {
                    return {
                        q: params.term || '',
                        page: params.page || 1
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results || [],
                        pagination: (data.pagination || {
                            more: false
                        })
                    };
                }
            },
            language: {
                noResults: function() {
                    return 'Produk tidak ditemukan';
                }
            }
        });

        $select.on('select2:select', function(e) {
            const item = e.params && e.params.data ? e.params.data : {};
            $hidden.val(item.name || item.text || '');
        });

        $select.on('change', function() {
            if (!$select.val()) {
                $hidden.val('');
            }
        });
    }

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
                    <a href="#" class="me-2 text-primary" onclick="syncPostRow('${rowId}'); return false;" title="Sync views">
                        <i class="bi bi-arrow-repeat"></i>
                    </a>
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

    function syncPostRow(rowId) {
        const row = $('#' + rowId);
        const postId = parseInt(row.attr('data-post-id') || '0', 10);

        if (!postId) {
            Swal.fire({
                icon: 'info',
                text: 'Simpan data terlebih dahulu sebelum sync views per post.'
            });
            return;
        }

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

                row.find('input[name$="[views]"]').val(res.data.views || 0);
                if (res.data.tanggal_post) {
                    row.find('input[name$="[tanggal_post]"]').val(res.data.tanggal_post);
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
                $('.btn-send').addClass('disabled').attr('disabled', true).html('<div class="spinner-border spinner-border-sm text-white me-2" role="status"></div>Mengupdate...');
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

                $('.btn-send').removeClass('disabled').attr('disabled', false).html('<i class="bi bi-save me-1"></i> Update Data');
            },
            error: function(xhr) {
                $('.btn-send').removeClass('disabled').attr('disabled', false).html('<i class="bi bi-save me-1"></i> Update Data');
                $('.form-message').hide().html(xhr.responseText || 'Terjadi kesalahan').slideDown('fast');
            }
        });

        return false;
    });

    $(document).ready(function() {
        initPicUtamaSelect();
        initProductSelect();

        if (existingPosts.length > 0) {
            existingPosts.forEach(function(item) {
                addPostRow(item);
            });
        } else {
            addPostRow();
        }
    });
</script>
