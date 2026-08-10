<?php // Fixture-driven shell. It is intentionally not included by a production route yet. ?>
<section class="endorse-v2-analytics card mt-3" data-endorse-v2-analytics aria-labelledby="endorse-v2-analytics-title">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-1" id="endorse-v2-analytics-title">Analitik Pengamatan</h5>
                <p class="text-muted small mb-0">Metrik berdasarkan waktu data berhasil diamati, bukan waktu aktivitas terjadi.</p>
            </div>
            <span class="badge bg-secondary" data-analytics-state>Memuat</span>
        </div>
        <div class="row g-3 align-items-end mb-3">
            <div class="col-sm-4"><label class="form-label small" for="analytics-v2-from">Tanggal mulai</label><input class="form-control" id="analytics-v2-from" type="date" disabled></div>
            <div class="col-sm-4"><label class="form-label small" for="analytics-v2-until">Tanggal akhir</label><input class="form-control" id="analytics-v2-until" type="date" disabled></div>
            <div class="col-sm-4"><button type="button" class="btn btn-primary w-100" disabled data-analytics-apply>Terapkan</button></div>
        </div>
        <div class="row g-2" data-analytics-summary aria-live="polite"></div>
        <div class="mt-3" data-analytics-message></div>
        <div class="mt-3 d-none" data-analytics-chart-wrap><canvas data-analytics-chart role="img" aria-label="Grafik total dan pertumbuhan teramati"></canvas></div>
        <div class="table-responsive mt-3 d-none" data-analytics-table-wrap><table class="table table-sm mb-0"><thead><tr><th>Tanggal</th><th class="text-end">Total teramati</th><th class="text-end">Pertumbuhan teramati</th><th>Sumber</th></tr></thead><tbody data-analytics-table></tbody></table></div>
    </div>
</section>
<script>
(function () {
    function format(value) { return value === null || value === undefined ? '—' : Number(value).toLocaleString('id-ID'); }
    function render(root, payload) {
        const summary = payload && payload.summary;
        const state = root.querySelector('[data-analytics-state]');
        const message = root.querySelector('[data-analytics-message]');
        const container = root.querySelector('[data-analytics-summary]');
        const tableWrap = root.querySelector('[data-analytics-table-wrap]');
        const tbody = root.querySelector('[data-analytics-table]');
        if (payload && payload.error) { state.className = 'badge bg-danger'; state.textContent = 'Gagal dimuat'; message.innerHTML = '<div class="alert alert-danger mb-0">Analitik tidak dapat dimuat. Coba lagi nanti.</div>'; return; }
        if (!summary || !payload.daily || !payload.daily.length) { state.className = 'badge bg-secondary'; state.textContent = 'Belum ada observasi'; message.innerHTML = '<div class="alert alert-light border mb-0">Belum ada pengamatan tepercaya dalam rentang ini.</div>'; return; }
        state.className = 'badge bg-success'; state.textContent = 'Data teramati';
        container.innerHTML = [['Total akhir rentang', summary.observed_total_at_range_end], ['Pertumbuhan teramati', summary.observed_growth], ['Total tepercaya saat ini', summary.current_trusted_total]].map(function (item) { return '<div class="col-md-4"><div class="border rounded p-2 h-100"><div class="small text-muted">'+item[0]+'</div><strong>'+format(item[1])+'</strong></div></div>'; }).join('');
        tbody.innerHTML = payload.daily.map(function (d) { return '<tr><td>'+d.date+'</td><td class="text-end">'+format(d.observed_total)+'</td><td class="text-end">'+format(d.observed_growth)+'</td><td>'+d.provenance+'</td></tr>'; }).join('');
        tableWrap.classList.remove('d-none');
    }
    window.EndorseV2AnalyticsShell = { render: render };
}());
</script>
