<?php

?>
<style>
.progress-container {
    margin-top: 10px;
}
.progress {
    height: 20px;
    border-radius: 4px;
    overflow: hidden;
    background-color: #e9ecef;
}
.progress-bar {
    transition: width 0.3s ease-in-out;
}
.sync-status {
    font-size: 12px;
    margin-top: 5px;
}
.sync-status.text-success {
    color: #28a745 !important;
}
.sync-status.text-danger {
    color: #dc3545 !important;
}
</style>

<div class="form-message"></div>
<form action="<?= base_url() ?>/product-3rd/sync-process" method="POST" id="form-modal">
    <input type="hidden" name="id" value="<?= $data['id'] ?>">
    <p class="mb-0">Apakah kamu yakin ingin melakukan sync data produk?</p>
	<div class="mb-1 mt-1">
        <button id="btn-sync-all" type="button" class="btn btn-success">Sync All</button>
    </div>

    <?php foreach ($store as $k => $v) {
	if ($v['marketplace'] == "TIKTOK") {
		$v['img'] = base_url() . '/assets/img/marketplace/3.png';
	} else if ($v['marketplace'] == "SHOPEE") {
		$v['img'] = base_url() . '/assets/img/marketplace/1.png';
	} else if ($v['marketplace'] == "LAZADA") {
		$v['img'] = base_url() . '/assets/img/marketplace/2.png';
	} else if ($v['marketplace'] == "META") {
		$v['img'] = base_url() . '/assets/img/marketplace/5.png';
	}
	?>

        <hr class="mb-2">
        <div class="d-flex align-items-center mb-2">
			<span class="me-2 fw-600"><?= $k + 1 ?>.</span>
			<img src="<?= $v['img'] ?>" class="rounded-circle border me-2" style="width: 35px; height: 35px;">
			<div>
				<div class="fw-600"><?= $v['opt'] ?></div>
				<div class="text-muted small"><?= !empty($v['shop_code']) ? $v['shop_code'] : '-' ?></div>
			</div>
		</div>

        <!-- Progress bar for each store -->
        <div id="progress-container-<?= $k ?>" class="progress-container" style="display: none;">
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                     role="progressbar" style="width: 0%" id="progress-bar-<?= $k ?>"></div>
            </div>
            <div class="sync-status text-muted" id="sync-status-<?= $k ?>">Memulai sync...</div>
        </div>

        <div class="mb-1 mt-1">
            <button form="form-modal-refresh-<?= $k ?>" type="submit" class="btn btn-edit btn-send-refresh">Refresh Token</button>
            <button type="button" class="btn btn-primary btn-send-sync" onclick="startChunkedSync(<?= $k ?>, '<?= $v['marketplace'] ?>', '<?= $v['id'] ?>')">Sync Data</button>
        </div>
    <?php } ?>

    <hr class="mb-2">

</form>


<?php

$arr = array();
foreach ($store as $k => $v) {
?>
	<form action="<?= base_url() ?>/product-3rd/sync-process?marketplace=<?= $v['marketplace'] ?>&shop_id=<?= $v['id'] ?>" method="POST" id="form-modal-sync-<?= $k ?>"></form>
	<form action="<?= base_url() ?>/marketplace-account/refresh-token-process?marketplace=<?= $v['marketplace'] ?>&shop_id=<?= $v['id'] ?>" method="POST" id="form-modal-refresh-<?= $k ?>"></form>

	<script type="text/javascript">
		$("#form-modal-refresh-<?= $k ?>").submit(function() {
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
					$(".btn-send-refresh")
						.addClass("disabled")
						.html(
							'<div class="loading-ellipsis"><div></div><div></div><div></div><div></div></div>'
						)
						.attr("disabled", true);
					form.find(".form-message").slideUp().html("");
				},
				success: function(response, textStatus, xhr) {
					var str = response;
					console.log(str);
					if (str.indexOf("success") != -1) {
						$(".form-message").hide().html(response).slideDown("fast");
						setTimeout(function() {
							window.location.href = "";
							$(".btn-send-refresh")
								.removeClass("disabled")
								.html("Refresh Token")
								.attr("disabled", false);
						}, 2500);
					} else {
						$(".form-message").hide().html(response).slideDown("fast");
						$(".btn-send-refresh")
							.removeClass("disabled")
							.html("Refresh Token")
							.attr("disabled", false);
					}
				},
				error: function(xhr, textStatus, errorThrown) {
					$(".btn-send-refresh")
						.removeClass("disabled")
						.html("Refresh Token")
						.attr("disabled", false);
					$(".form-message").hide().html(xhr).slideDown("fast");
				},
			});
			return false;
		});
	</script>

<?php } ?>

<script type="text/javascript">
    var baseUrl = "<?= base_url() ?>";
    var syncInProgress = {};

    /**
     * Start chunked sync for a single store
     */
    function startChunkedSync(formIndex, marketplace, shopId) {
        if (syncInProgress[formIndex]) {
            console.log('Sync already in progress for form ' + formIndex);
            return;
        }

        syncInProgress[formIndex] = true;

        var progressContainer = $("#progress-container-" + formIndex);
        var progressBar = $("#progress-bar-" + formIndex);
        var syncStatus = $("#sync-status-" + formIndex);
        var btn = $(".btn-send-sync").eq(formIndex);

        // Show progress UI
        progressContainer.show();
        progressBar.css("width", "0%");
        syncStatus.removeClass("text-success text-danger").addClass("text-muted").text("Memulai sync...");
        btn.addClass("disabled").attr("disabled", true).html(
            '<div class="loading-ellipsis"><div></div><div></div><div></div><div></div></div>'
        );

        // Step 1: Initialize sync job
        $.ajax({
            type: "GET",
            url: baseUrl + "product-3rd/sync-process-init?marketplace=" + encodeURIComponent(marketplace) + "&shop_id=" + encodeURIComponent(shopId),
            dataType: "json",
            timeout: 30000,
            success: function(response) {
                if (response.status) {
                    syncStatus.text("Job dibuat, memulai proses...");
                    // Step 2: Process chunks
                    processChunk(response.job_id, formIndex, 0);
                } else {
                    showSyncError(formIndex, response.msg || "Gagal menginisialisasi sync");
                }
            },
            error: function(xhr, status, error) {
                showSyncError(formIndex, "Koneksi error: " + (error || status));
            }
        });
    }

    /**
     * Process one chunk of products
     */
    function processChunk(jobId, formIndex, retryCount) {
        var progressBar = $("#progress-bar-" + formIndex);
        var syncStatus = $("#sync-status-" + formIndex);

        $.ajax({
            type: "GET",
            url: baseUrl + "product-3rd/sync-process-chunk?job_id=" + jobId + "&chunk_size=15",
            dataType: "json",
            timeout: 150000, // 2.5 minute timeout per chunk
            success: function(response) {
                if (response.status) {
                    // Update progress
                    var percent = response.progress_percent || 0;
                    progressBar.css("width", percent + "%");
                    syncStatus.text("Memproses: " + response.processed + " produk (" + percent + "%)");

                    if (response.has_more) {
                        // Continue with next chunk after small delay
                        setTimeout(function() {
                            processChunk(jobId, formIndex, 0);
                        }, 300);
                    } else {
                        // Sync complete
                        showSyncSuccess(formIndex, "Sync berhasil! " + response.processed + " produk diproses.");
                    }
                } else {
                    showSyncError(formIndex, response.msg || "Error saat memproses");
                }
            },
            error: function(xhr, status, error) {
                if (status === 'timeout' && retryCount < 2) {
                    // Retry on timeout (max 2 retries)
                    syncStatus.text("Timeout, mencoba ulang... (" + (retryCount + 1) + "/2)");
                    setTimeout(function() {
                        processChunk(jobId, formIndex, retryCount + 1);
                    }, 1000);
                } else {
                    showSyncError(formIndex, "Koneksi timeout setelah beberapa percobaan. Silakan coba lagi.");
                }
            }
        });
    }

    /**
     * Show sync error
     */
    function showSyncError(formIndex, message) {
        syncInProgress[formIndex] = false;

        var progressBar = $("#progress-bar-" + formIndex);
        var syncStatus = $("#sync-status-" + formIndex);
        var btn = $(".btn-send-sync").eq(formIndex);

        progressBar.removeClass("bg-primary").addClass("bg-danger");
        syncStatus.removeClass("text-muted text-success").addClass("text-danger").text("Error: " + message);
        btn.removeClass("disabled").attr("disabled", false).html("Sync Data");

        $(".form-message").html('<div class="alert alert-danger">' + message + '</div>').slideDown("fast");
    }

    /**
     * Show sync success
     */
    function showSyncSuccess(formIndex, message) {
        syncInProgress[formIndex] = false;

        var progressBar = $("#progress-bar-" + formIndex);
        var syncStatus = $("#sync-status-" + formIndex);
        var btn = $(".btn-send-sync").eq(formIndex);

        progressBar.removeClass("bg-primary bg-danger").addClass("bg-success").css("width", "100%");
        syncStatus.removeClass("text-muted text-danger").addClass("text-success").text(message);
        btn.removeClass("disabled").attr("disabled", false).html("Sync Data");

        $(".form-message").html('<div class="alert alert-success">' + message + '</div>').slideDown("fast");

        // Reload page after success
        setTimeout(function() {
            window.location.reload();
        }, 2000);
    }

    /**
     * Sync All Stores sequentially
     */
    $("#btn-sync-all").click(function() {
        var btn = $(this);
        var stores = [];

        // Collect all stores info
        <?php foreach ($store as $k => $v) { ?>
            stores.push({
                index: <?= $k ?>,
                marketplace: "<?= $v['marketplace'] ?>",
                shopId: "<?= $v['id'] ?>"
            });
        <?php } ?>

        if (stores.length === 0) {
            $(".form-message").html('<div class="alert alert-warning">Tidak ada toko untuk disync</div>').slideDown("fast");
            return;
        }

        btn.addClass("disabled").attr("disabled", true).html(
            '<div class="loading-ellipsis"><div></div><div></div><div></div><div></div></div> Sync All'
        );

        $(".form-message").html('<div class="alert alert-info">Memulai sync semua toko...</div>').slideDown("fast");

        // Process stores sequentially
        var processStore = function(storeIndex) {
            if (storeIndex >= stores.length) {
                // All stores processed
                btn.removeClass("disabled").attr("disabled", false).html("Sync All");
                $(".form-message").html('<div class="alert alert-success">Semua toko berhasil disync!</div>').slideDown("fast");
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
                return;
            }

            var store = stores[storeIndex];
            $(".form-message").html('<div class="alert alert-info">Memproses toko ' + (storeIndex + 1) + ' dari ' + stores.length + '...</div>').slideDown("fast");

            // Start sync for this store
            syncSingleStoreForAll(store.index, store.marketplace, store.shopId, function(success) {
                // Wait a bit before processing next store
                setTimeout(function() {
                    processStore(storeIndex + 1);
                }, 1000);
            });
        };

        // Start processing from first store
        processStore(0);
    });

    /**
     * Sync single store for Sync All (with callback)
     */
    function syncSingleStoreForAll(formIndex, marketplace, shopId, callback) {
        var progressContainer = $("#progress-container-" + formIndex);
        var progressBar = $("#progress-bar-" + formIndex);
        var syncStatus = $("#sync-status-" + formIndex);

        // Show progress UI
        progressContainer.show();
        progressBar.removeClass("bg-success bg-danger").addClass("bg-primary").css("width", "0%");
        syncStatus.removeClass("text-success text-danger").addClass("text-muted").text("Memulai sync...");

        // Initialize sync job
        $.ajax({
            type: "GET",
            url: baseUrl + "product-3rd/sync-process-init?marketplace=" + encodeURIComponent(marketplace) + "&shop_id=" + encodeURIComponent(shopId),
            dataType: "json",
            timeout: 30000,
            success: function(response) {
                if (response.status) {
                    processChunkForAll(response.job_id, formIndex, 0, callback);
                } else {
                    syncStatus.removeClass("text-muted").addClass("text-danger").text("Error: " + (response.msg || "Init gagal"));
                    progressBar.removeClass("bg-primary").addClass("bg-danger");
                    callback(false);
                }
            },
            error: function(xhr, status, error) {
                syncStatus.removeClass("text-muted").addClass("text-danger").text("Error: Koneksi gagal");
                progressBar.removeClass("bg-primary").addClass("bg-danger");
                callback(false);
            }
        });
    }

    /**
     * Process chunk for Sync All (with callback)
     */
    function processChunkForAll(jobId, formIndex, retryCount, callback) {
        var progressBar = $("#progress-bar-" + formIndex);
        var syncStatus = $("#sync-status-" + formIndex);

        $.ajax({
            type: "GET",
            url: baseUrl + "product-3rd/sync-process-chunk?job_id=" + jobId + "&chunk_size=15",
            dataType: "json",
            timeout: 150000,
            success: function(response) {
                if (response.status) {
                    var percent = response.progress_percent || 0;
                    progressBar.css("width", percent + "%");
                    syncStatus.text("Memproses: " + response.processed + " produk (" + percent + "%)");

                    if (response.has_more) {
                        setTimeout(function() {
                            processChunkForAll(jobId, formIndex, 0, callback);
                        }, 300);
                    } else {
                        progressBar.removeClass("bg-primary").addClass("bg-success").css("width", "100%");
                        syncStatus.removeClass("text-muted").addClass("text-success").text("Selesai! " + response.processed + " produk");
                        callback(true);
                    }
                } else {
                    syncStatus.removeClass("text-muted").addClass("text-danger").text("Error: " + (response.msg || "Proses gagal"));
                    progressBar.removeClass("bg-primary").addClass("bg-danger");
                    callback(false);
                }
            },
            error: function(xhr, status, error) {
                if (status === 'timeout' && retryCount < 2) {
                    syncStatus.text("Timeout, retry... (" + (retryCount + 1) + "/2)");
                    setTimeout(function() {
                        processChunkForAll(jobId, formIndex, retryCount + 1, callback);
                    }, 1000);
                } else {
                    syncStatus.removeClass("text-muted").addClass("text-danger").text("Error: Timeout");
                    progressBar.removeClass("bg-primary").addClass("bg-danger");
                    callback(false);
                }
            }
        });
    }
</script>
