<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<section class="endorse-v2-analytics card mt-3" data-endorse-v2-analytics
    data-endpoint="<?= html_escape($analytics_endpoint) ?>"
    data-campaign-id="<?= (int) $analytics_campaign_id ?>"
    data-default-from="<?= html_escape($analytics_default_from) ?>"
    data-default-until="<?= html_escape($analytics_default_until) ?>"
    aria-labelledby="endorse-v2-analytics-title">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-1" id="endorse-v2-analytics-title">Analitik Pengamatan V2</h5>
                <p class="text-muted small mb-0">Metrik berdasarkan waktu data berhasil diamati, bukan waktu aktivitas terjadi.</p>
            </div>
            <span class="badge bg-secondary" data-analytics-state>Siap dimuat</span>
        </div>
        <form data-analytics-form>
            <div class="row g-3 align-items-end mb-3">
                <div class="col-sm-4"><label class="form-label small" for="analytics-v2-from">Tanggal mulai</label><input class="form-control" id="analytics-v2-from" data-analytics-from type="date" required></div>
                <div class="col-sm-4"><label class="form-label small" for="analytics-v2-until">Tanggal akhir</label><input class="form-control" id="analytics-v2-until" data-analytics-until type="date" required></div>
                <div class="col-sm-4"><button type="submit" class="btn btn-primary w-100" data-analytics-apply>Terapkan</button></div>
            </div>
        </form>
        <div class="row g-2" data-analytics-summary aria-live="polite"></div>
        <div class="mt-3" data-analytics-message aria-live="polite"></div>
        <div class="mt-3 d-none" data-analytics-chart-wrap><canvas data-analytics-chart role="img" aria-label="Grafik total dan pertumbuhan teramati"></canvas></div>
        <div class="table-responsive mt-3 d-none" data-analytics-table-wrap><table class="table table-sm mb-0"><thead><tr><th>Tanggal</th><th class="text-end">Total teramati</th><th class="text-end">Pertumbuhan teramati</th><th>Sumber</th></tr></thead><tbody data-analytics-table></tbody></table></div>
    </div>
</section>
<script>
(function ($) {
    function formatNumber(value) {
        return value === null || value === undefined ? '—' : Number(value).toLocaleString('id-ID');
    }

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function isDate(value) {
        return /^\d{4}-\d{2}-\d{2}$/.test(value || '') && !Number.isNaN(Date.parse(value + 'T00:00:00'));
    }

    function readableProvenance(value) {
        return value === 'baseline' ? 'Baseline' : value === 'observed' ? 'Teramati' : 'Tidak tersedia';
    }

    function create(root) {
        var request = null;
        var generation = 0;
        var chart = null;
        var $root = $(root);
        var $form = $root.find('[data-analytics-form]');
        var $from = $root.find('[data-analytics-from]');
        var $until = $root.find('[data-analytics-until]');
        var $apply = $root.find('[data-analytics-apply]');
        var $state = $root.find('[data-analytics-state]');
        var $summary = $root.find('[data-analytics-summary]');
        var $message = $root.find('[data-analytics-message]');
        var $chartWrap = $root.find('[data-analytics-chart-wrap]');
        var $tableWrap = $root.find('[data-analytics-table-wrap]');
        var $table = $root.find('[data-analytics-table]');

        function destroyChart() {
            if (chart) {
                chart.destroy();
                chart = null;
            }
        }

        function setState(label, className) {
            $state.attr('class', 'badge ' + className).text(label);
        }

        function setLoading() {
            destroyChart();
            $apply.prop('disabled', true);
            $summary.empty();
            $message.html('<div class="text-muted small">Memuat analitik pengamatan...</div>');
            $chartWrap.addClass('d-none');
            $tableWrap.addClass('d-none');
            setState('Memuat', 'bg-secondary');
        }

        function renderEmpty() {
            destroyChart();
            $summary.empty();
            $message.html('<div class="alert alert-light border mb-0">Belum ada pengamatan tepercaya dalam rentang ini.</div>');
            $chartWrap.addClass('d-none');
            $tableWrap.addClass('d-none');
            setState('Belum ada observasi', 'bg-secondary');
        }

        function renderError(message) {
            destroyChart();
            $summary.empty();
            $message.html('<div class="alert alert-danger mb-0">' + escapeHtml(message || 'Analitik tidak dapat dimuat.') + ' <button type="button" class="btn btn-sm btn-outline-danger ms-2" data-analytics-retry>Coba lagi</button></div>');
            $chartWrap.addClass('d-none');
            $tableWrap.addClass('d-none');
            setState('Gagal dimuat', 'bg-danger');
        }

        function renderSummary(summary) {
            var cards = [
                ['Total awal teramati', summary.opening_observed_total],
                ['Total teramati akhir rentang', summary.observed_total_at_range_end],
                ['Pertumbuhan teramati', summary.observed_growth],
                ['Total tepercaya saat ini', summary.current_trusted_total],
                ['Pengamatan berhasil terakhir', summary.last_successful_observation_at || null]
            ];
            $summary.html(cards.map(function (card) {
                var value = card[0] === 'Pengamatan berhasil terakhir'
                    ? (card[1] ? escapeHtml(card[1] + ' UTC') : '—')
                    : formatNumber(card[1]);
                return '<div class="col-12 col-sm-6 col-lg"><div class="border rounded p-2 h-100"><div class="small text-muted">' + card[0] + '</div><strong>' + value + '</strong></div></div>';
            }).join(''));
        }

        function renderTable(daily) {
            $table.html(daily.map(function (day) {
                return '<tr><td>' + escapeHtml(day.date) + '</td><td class="text-end">' + formatNumber(day.observed_total) + '</td><td class="text-end">' + formatNumber(day.observed_growth) + '</td><td>' + readableProvenance(day.provenance) + '</td></tr>';
            }).join(''));
            $tableWrap.removeClass('d-none');
        }

        function renderChart(daily) {
            if (typeof window.Chart !== 'function') {
                renderError('Grafik analitik tidak tersedia.');
                return;
            }
            destroyChart();
            var canvas = $root.find('[data-analytics-chart]').get(0);
            chart = new window.Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: daily.map(function (day) { return day.date; }),
                    datasets: [
                        { label: 'Total teramati', type: 'line', data: daily.map(function (day) { return day.observed_total; }), borderColor: '#0d6efd', backgroundColor: '#0d6efd', spanGaps: false, tension: 0.15 },
                        { label: 'Pertumbuhan teramati', type: 'bar', data: daily.map(function (day) { return day.observed_growth; }), backgroundColor: '#6c757d' }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { x: { ticks: { maxRotation: 0, autoSkip: true } } } }
            });
            $chartWrap.removeClass('d-none').css('min-height', '300px');
        }

        function readState() {
            var params = new URLSearchParams(window.location.search);
            return {
                from: params.get('analytics_start_date') || root.dataset.defaultFrom,
                until: params.get('analytics_until_date') || root.dataset.defaultUntil
            };
        }

        function updateUrl(from, until) {
            var url = new URL(window.location.href);
            url.searchParams.set('analytics_start_date', from);
            url.searchParams.set('analytics_until_date', until);
            window.history.pushState(null, '', url.toString());
        }

        function load(options) {
            var settings = options || {};
            var from = $from.val();
            var until = $until.val();
            if (!isDate(from) || !isDate(until) || from > until) {
                renderError('Rentang tanggal tidak valid.');
                return;
            }
            if (settings.pushState) {
                updateUrl(from, until);
            }
            var thisGeneration = ++generation;
            if (request && request.readyState !== 4) {
                request.abort();
            }
            setLoading();
            request = $.ajax({
                dataType: 'json',
                method: 'GET',
                url: root.dataset.endpoint,
                data: { id_campaign: root.dataset.campaignId, start_date: from, until_date: until }
            }).done(function (payload) {
                if (thisGeneration !== generation) return;
                if (!payload || payload.status !== true || !payload.summary || !Array.isArray(payload.daily)) {
                    renderError((payload && payload.message) || 'Respons analitik tidak valid.');
                    return;
                }
                var hasObservation = payload.daily.some(function (day) { return day.observed_total !== null && day.observed_total !== undefined; });
                if (!hasObservation) {
                    renderEmpty();
                    return;
                }
                renderSummary(payload.summary);
                $message.empty();
                renderChart(payload.daily);
                renderTable(payload.daily);
                setState('Data teramati', 'bg-success');
            }).fail(function (xhr, status) {
                if (status === 'abort' || thisGeneration !== generation) return;
                var payload = xhr.responseJSON;
                renderError((payload && payload.message) || 'Analitik tidak dapat dimuat.');
            }).always(function () {
                if (thisGeneration === generation) {
                    request = null;
                    $apply.prop('disabled', false);
                }
            });
        }

        function loadFromUrl() {
            var state = readState();
            $from.val(state.from);
            $until.val(state.until);
            load({ pushState: false });
        }

        $form.on('submit', function (event) {
            event.preventDefault();
            load({ pushState: true });
        });
        $root.on('click', '[data-analytics-retry]', function () { load({ pushState: false }); });
        window.addEventListener('popstate', loadFromUrl);
        loadFromUrl();
    }

    $(function () {
        $('[data-endorse-v2-analytics]').each(function () { create(this); });
    });
}(jQuery));
</script>
