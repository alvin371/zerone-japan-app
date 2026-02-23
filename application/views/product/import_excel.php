<div class="form-message"></div>
<form action="<?= base_url('product/import_excel_process') ?>" method="POST" enctype="multipart/form-data" id="form-modal">
    <p class="mb-2">
        Upload file Excel produk untuk import ke daftar produk aktif.
        <br>
        Kolom yang dibaca: <b>ITEM CODE</b>, <b>PRODUK/SKU</b>, <b>VOLUME (ml)</b>, <b>Harga Jual</b>, <b>COGS</b>, <b>Berat (gram)</b>.
    </p>

    <div class="form-group mt-3">
        <label for="brand">Brand (untuk data baru)</label>
        <select class="form-control" id="brand" name="brand" required>
            <option value="">-- Pilih Brand --</option>
            <?php foreach ($brands as $brand): ?>
                <option value="<?= $brand['code'] ?>">
                    <?= $brand['code'] ?><?= !empty($brand['name']) ? ' - ' . $brand['name'] : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group mt-3">
        <label for="file">File Excel</label>
        <input type="file" id="file" name="file" class="form-control" accept=".xls,.xlsx" required>
        <small class="text-muted">Format yang didukung: .xls, .xlsx</small>
    </div>

    <div class="form-group mt-3">
        <button type="submit" class="btn btn-primary btn-send">Import Data</button>
    </div>
</form>

<script type="text/javascript">
    $("#form-modal").submit(function() {
        var form = $(this);
        var mydata = new FormData(this);

        $.ajax({
            type: "POST",
            url: form.attr("action"),
            data: mydata,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $(".btn-send")
                    .addClass("disabled")
                    .html('<div class="loading-ellipsis"><div></div><div></div><div></div><div></div></div>')
                    .attr('disabled', true);
                form.find(".form-message").slideUp().html("");
            },
            success: function(response) {
                var str = response || '';
                $(".form-message").hide().html(response).slideDown("fast");

                if (str.indexOf("success") !== -1) {
                    setTimeout(function() {
                        window.location.href = window.location.href;
                    }, 1500);
                } else {
                    $(".btn-send").removeClass("disabled").html('Import Data').attr('disabled', false);
                }
            },
            error: function(xhr) {
                $(".btn-send").removeClass("disabled").html('Import Data').attr('disabled', false);
                $(".form-message").hide().html(xhr.responseText || 'Terjadi kesalahan').slideDown("fast");
            }
        });

        return false;
    });
</script>
