(function () {
    function photoId(url) {
        var match = String(url || '').match(/\/photo\/(\d+)/);
        return match ? match[1] : '';
    }

    function videoHtml(playUrl) {
        return '<div style="width:320px;height:520px;overflow:hidden;border-radius:8px;background:#fff;">' +
            '<video src="' + playUrl + '" style="width:100%;height:100%;object-fit:cover;" controls autoplay muted loop playsinline preload="metadata"></video>' +
            '</div>';
    }

    function photoHtml(images) {
        var slides = images.map(function (url) {
            return '<img src="' + url + '" style="width:100%;max-height:520px;object-fit:contain;display:block;">';
        }).join('');
        return '<div style="width:320px;max-height:520px;overflow-y:auto;background:#fff;border-radius:8px;">' + slides + '</div>';
    }

    function request(url, data) {
        return window.jQuery ? window.jQuery.ajax({url: url, type: 'POST', data: data, dataType: 'json'}) : Promise.reject();
    }

    function attach(anchor) {
        if (anchor.dataset.tiktokPreviewReady || typeof window.tippy !== 'function') return;
        anchor.dataset.tiktokPreviewReady = '1';
        window.tippy(anchor, {
            content: '<div class="p-2"><div class="text-center py-2"><i class="fa fa-spinner fa-spin"></i> Memuat media...</div></div>',
            allowHTML: true,
            interactive: true,
            maxWidth: 360,
            onShow: function (instance) {
                var url = anchor.getAttribute('data-tiktok-url') || anchor.href;
                var id = photoId(url);
                if (id) {
                    request(window.base_url + 'endorse/get_tiktok_photo_images', {content_id: id, url: url})
                        .done(function (result) {
                            instance.setContent(result && result.status && Array.isArray(result.data) && result.data.length
                                ? photoHtml(result.data)
                                : '<div class="p-2 text-muted">Foto TikTok tidak ditemukan.</div>');
                        })
                        .fail(function () { instance.setContent('<div class="p-2 text-danger">Gagal memuat foto TikTok.</div>'); });
                    return;
                }
                request(window.base_url + 'endorse/get_tiktok_video_play', {url: url})
                    .done(function (result) {
                        instance.setContent(result && result.status && result.data && result.data.play
                            ? videoHtml(result.data.play)
                            : '<div class="p-2 text-muted">Video TikTok tidak ditemukan dari link upload.</div>');
                    })
                    .fail(function () { instance.setContent('<div class="p-2 text-danger">Gagal memuat video TikTok.</div>'); });
            }
        });
    }

    function initialize() {
        document.querySelectorAll('a[data-tiktok-url], a[href*="tiktok.com/"]').forEach(attach);
    }

    window.initTiktokMediaPreviews = initialize;
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
    if (window.jQuery) window.jQuery(document).ajaxComplete(initialize);
}());
