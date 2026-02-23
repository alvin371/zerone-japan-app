<style>
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #f0f0f0;
        border-radius: 2px;
    }

    .table thead th {
        background-color: #fafafa;
        color: rgba(0, 0, 0, 0.85);
        font-weight: 500;
        text-align: left;
        padding: 12px 8px;
        font-size: 14px;
        border-bottom: 1px solid #f0f0f0;
    }

    .table tbody td {
        padding: 12px 8px !important;
        font-size: 14px;
        color: rgba(0, 0, 0, 0.65);
        border-bottom: 1px solid #f0f0f0;
    }

    .table-hover tbody tr:hover {
        background-color: #fafafa;
    }

    .card {
        border-radius: 2px;
        border: 1px solid #f0f0f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.09);
        margin-bottom: 16px;
    }

    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #f0f0f0;
        padding: 16px;
        min-height: 56px;
    }

    .card-body {
        padding: 16px;
    }

    .btn {
        border-radius: 2px;
        padding: 4px 15px;
        font-size: 14px;
        height: 32px;
        line-height: 1.5;
    }

    .btn-primary {
        background-color: #1890ff;
        border-color: #1890ff;
    }

    .btn-primary:hover {
        background-color: #40a9ff;
        border-color: #40a9ff;
    }

    .btn-outline-secondary {
        color: rgba(0, 0, 0, 0.65);
        border-color: #d9d9d9;
        background: #fff;
    }

    .btn-outline-secondary:hover {
        color: #40a9ff;
        border-color: #40a9ff;
    }

    .form-control,
    .form-select {
        height: 32px;
        padding: 4px 11px;
        font-size: 14px;
        border: 1px solid #d9d9d9;
        border-radius: 2px;
    }

    .badge {
        font-size: 12px;
        height: 22px;
        padding: 0 8px;
        line-height: 22px;
        border-radius: 2px;
        font-weight: normal;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0" style="color: rgba(0, 0, 0, 0.85);">KOL & Affiliator</h5>

                <?php if (!empty($can_create)): ?>
                    <div class="d-flex gap-2">
                        <a href="#!" onclick="openImportModal()" class="btn btn-outline-secondary">
                            <i class="bi bi-file-earmark-excel me-1"></i> Import Excel
                        </a>
                        <a href="<?= base_url('kol-affiliator/create-page') ?>" class="btn btn-primary">
                            <i class="bi bi-plus me-1"></i> Create
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body">
            <form action="" method="GET" class="mb-3">
                <div class="row g-2">
                    <div class="col-lg-3 col-md-6">
                        <select class="form-select" name="keyword_category">
                            <?php
                            $keyword_options = ['Username KOL', 'PIC Utama', 'Product', 'Status Creator'];
                            foreach ($keyword_options as $opt):
                            ?>
                                <option value="<?= $opt ?>" <?= ($keyword_category == $opt ? 'selected' : '') ?>>
                                    <?= $opt ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <input type="text" name="keyword" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($_GET['keyword'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <input type="text" name="status_creator" class="form-control" placeholder="Filter Status Creator" value="<?= htmlspecialchars($status_creator ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-lg-2 col-md-6 d-grid">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>

            <?php if (!empty($notif)): ?>
                <div class="alert alert-info py-2 mb-3">
                    <?= strip_tags($notif) ?>
                </div>
            <?php endif; ?>

            <div class="table-responsive" id="table-item">
                <table class="table table-hover" id="tbody">
                    <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Username KOL</th>
                            <th>PIC Utama</th>
                            <th>Product</th>
                            <th>Status Creator</th>
                            <th class="text-center" style="width:100px">Post</th>
                            <th class="text-end" style="width:120px">Action</th>
                        </tr>
                        <tr id="tbody-loading" style="background:unset!important">
                            <td class="text-start p-0" colspan="7" style="background:unset!important">
                                <div class="mt-3">
                                    <?php $this->load->view('loading', true) ?>
                                </div>
                            </td>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <?= $pagination ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="modal-form">
    <div class="modal-dialog modal-lg" id="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title-form"></h5>
                <a class="close a-link" data-bs-dismiss="modal"><i class="bi bi-x-circle fs-24"></i></a>
            </div>
            <div class="modal-body">
                <div id="load-form"></div>
            </div>
        </div>
    </div>
</div>

<script>
    function openImportModal() {
        $('#load-form').html('Loading...');
        $('#modal-form').modal('show');
        $('#modal-dialog').removeClass('modal-xl').addClass('modal-lg');
        $('#title-form').html('Import Excel KOL & Affiliator');
        $('#load-form').load("<?= base_url('kol-affiliator/import-excel') ?>");
    }

    function removeCampaign(id) {
        $('#load-form').html('Loading...');
        $('#modal-form').modal('show');
        $('#modal-dialog').removeClass('modal-xl modal-lg').addClass('modal-md');
        $('#title-form').html('Hapus Data');
        $('#load-form').load("<?= base_url('kol-affiliator/remove?id=') ?>" + id);
    }

    function loadKolData() {
        $.ajax({
            type: 'GET',
            url: "<?= base_url() ?>/kol-affiliator/item<?= $param ?>",
            beforeSend: function() {
                $('#tbody-loading').show();
            },
            success: function(data) {
                $('#tbody-loading').hide();
                $('#tbody').append(data);
            },
            error: function(xhr, status, error) {
                $('#tbody-loading').html('<td colspan="7" class="text-center text-danger">Error loading data: ' + error + '</td>');
            }
        });
    }

    $(document).ready(function() {
        loadKolData();
    });
</script>
