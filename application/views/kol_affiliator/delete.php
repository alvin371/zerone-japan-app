<div class="modal-body">
    <div class="text-center">
        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
        <h5 class="mt-3">Apakah Anda yakin?</h5>
        <p class="text-muted mb-0">
            Data KOL & Affiliator ini akan dihapus permanen.
            <?php if (!empty($data['username_kol'])): ?>
                <br><strong><?= htmlspecialchars($data['username_kol'], ENT_QUOTES, 'UTF-8') ?></strong>
            <?php endif; ?>
        </p>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
    <button type="button" class="btn btn-danger" onclick="deleteCampaign(<?= (int)$data['id'] ?>)">
        <i class="bi bi-trash me-1"></i> Ya, Hapus
    </button>
</div>

<script>
    function deleteCampaign(id) {
        $.ajax({
            type: 'POST',
            url: "<?= base_url('kol-affiliator/delete') ?>",
            data: { id: id },
            beforeSend: function() {
                $('.btn-danger').addClass('disabled').attr('disabled', true).html('<div class="spinner-border spinner-border-sm text-white me-2" role="status"></div>Menghapus...');
            },
            success: function(response) {
                const str = String(response || '').toLowerCase();
                if (str.indexOf('success') !== -1) {
                    $('#modal-form').modal('hide');
                    window.location.reload();
                    return;
                }

                $('#load-form').prepend('<div class="alert alert-danger">' + response + '</div>');
                $('.btn-danger').removeClass('disabled').attr('disabled', false).html('<i class="bi bi-trash me-1"></i> Ya, Hapus');
            },
            error: function() {
                $('.btn-danger').removeClass('disabled').attr('disabled', false).html('<i class="bi bi-trash me-1"></i> Ya, Hapus');
            }
        });
    }
</script>
