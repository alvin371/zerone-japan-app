<div class="form-message"></div>
<form action="<?= base_url() ?>/endorse/sync-process" method="POST" id="form-modal">
	<input type="hidden" name="id" value="<?= $data['id'] ?>">
	<p>Apakah kamu yakin ingin melakukan refresh data?</p>
	<div class="col-md-12 mt-3">
		<button type="submit" class="btn btn-primary btn-send">Refresh Data</button>
	</div>
</form>
<script type="text/javascript">
	$("#form-modal").submit(function() {
		var form = $(this);
		var formMessage = form.prev(".form-message");
		var sendButton = form.find(".btn-send");
		var mydata = new FormData(this);
		if (!formMessage.length) {
			formMessage = $(".form-message").first();
		}

		function resetButton() {
			sendButton.removeClass("disabled").html('Refresh Data').attr('disabled', false);
		}

		function showError(message) {
			formMessage.hide().html('<div class="alert alert-danger mb-0" role="alert">' + message + '</div>').slideDown("fast");
		}

		$.ajax({
			type: "POST",
			url: form.attr("action"),
			data: mydata,
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function() {
				sendButton.addClass("disabled").html('<div class="loading-ellipsis"><div></div><div></div><div></div><div></div></div>').attr('disabled', true);
				formMessage.slideUp().html("");
			},
			success: function(response, textStatus, xhr) {
				var str = response;
				console.log(str);
				if (str.indexOf("success") != -1) {
					formMessage.hide().html(response).slideDown("fast");
					setTimeout(function() {
						window.location.href = "";
						resetButton();
					}, 2500);
				} else {
					formMessage.hide().html(response).slideDown("fast");
					resetButton();
				}
			},
			error: function(xhr, textStatus, errorThrown) {
				var message = 'Refresh data gagal. Periksa koneksi lalu coba lagi.';
				if (xhr.status === 504) {
					message = 'Permintaan refresh melebihi batas waktu server. Status pembaruan belum dapat dipastikan. Muat ulang halaman sebelum mencoba lagi.';
				} else if (xhr.status === 0) {
					message = 'Koneksi ke server terputus. Periksa jaringan lalu coba lagi.';
				}
				resetButton();
				showError(message);
			}
		});
		return false;
	});
</script>
