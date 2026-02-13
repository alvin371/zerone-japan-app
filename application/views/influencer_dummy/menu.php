<?php
$page = strtolower(trim($_GET['p'] ?? 'tiktok'));
$btn_tiktok = $page === 'tiktok' ? 'btn-primary' : 'btn-edit';
$btn_instagram = $page === 'instagram' ? 'btn-primary' : 'btn-edit';
$btn_threads = $page === 'threads' ? 'btn-primary' : 'btn-edit';
?>
<div class="col-md-12">
    <a href="<?= base_url() ?>influencer-dummy?p=tiktok" class="btn <?= $btn_tiktok ?> me-1 mb-3" style="min-width:90px!important">TIKTOK</a>
    <a href="<?= base_url() ?>influencer-dummy?p=instagram" class="btn <?= $btn_instagram ?> me-1 mb-3" style="min-width:90px!important">INSTAGRAM</a>
    <a href="<?= base_url() ?>influencer-dummy?p=threads" class="btn <?= $btn_threads ?> me-1 mb-3" style="min-width:90px!important">THREADS</a>
</div>
