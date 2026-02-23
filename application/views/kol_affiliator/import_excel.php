<div class="form-message"></div>

<form action="<?= base_url('kol-affiliator/import-excel-process') ?>" method="POST" enctype="multipart/form-data" id="form-modal">
    <p class="mb-2">
        Upload file Excel data KOL & Affiliator.
        <br>
        Minimal wajib ada kolom <b>Username KOL</b>.
        <br>
        Contoh template: <a href="<?= base_url('kol-affiliator/download-template') ?>" target="_blank">Zerone Japan Database Product (1).xlsx</a>
    </p>

    <div class="form-group mt-3">
        <label for="file">File Excel</label>
        <input type="file" id="file" name="file" class="form-control" accept=".xls,.xlsx" required>
        <small class="text-muted">Format yang didukung: .xls, .xlsx</small>
    </div>

    <div class="form-group mt-3">
        <button type="submit" class="btn btn-primary btn-send">Import Data</button>
    </div>
</form>

<script>
    $('#form-modal').submit(function() {
        const form = $(this);
        const payload = new FormData(this);
        let isSuccess = false;

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: payload,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('.btn-send')
                    .addClass('disabled')
                    .html('<div class="loading-ellipsis"><div></div><div></div><div></div><div></div></div>')
                    .attr('disabled', true);
                form.find('.form-message').slideUp().html('');
            },
            success: function(response) {
                const str = String(response || '');
                $('.form-message').hide().html(response).slideDown('fast');

                isSuccess =
                    str.toLowerCase().indexOf('alert-success') !== -1 ||
                    str.toLowerCase().indexOf('success') !== -1;

                if (!isSuccess) {
                    return;
                }

                setTimeout(function() {
                    $('.btn-send').removeClass('disabled').html('Import Data').attr('disabled', false);
                    $('#modal-form').modal('hide');
                    $('#load-form').html('');
                    window.location.reload();
                }, 1000);
            },
            error: function(xhr) {
                $('.btn-send').removeClass('disabled').html('Import Data').attr('disabled', false);
                $('.form-message').hide().html(xhr.responseText || 'Terjadi kesalahan').slideDown('fast');
            },
            complete: function() {
                if (!isSuccess) {
                    $('.btn-send').removeClass('disabled').html('Import Data').attr('disabled', false);
                }
            }
        });

        return false;
    });
</script>
