<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Misi</title>
    @vite(['resources/css/app.css'])
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#991b1b">
    <script>if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
</head>
<body class="bg-neutral-950 text-neutral-100 min-h-screen font-sans">

{{-- NAVBAR --}}
<nav class="bg-neutral-900 border-b border-red-900 px-4 h-14 flex items-center justify-between sticky top-0 z-50">
    <div class="flex items-center gap-2 min-w-0">
        <div class="w-8 h-8 shrink-0 bg-red-800 rounded-md flex items-center justify-center p-1.5">
            <div class="w-full h-full border-2 border-neutral-100 rounded-full flex items-center justify-center">
                <div class="w-2 h-2 bg-neutral-100 rounded-full"></div>
            </div>
        </div>
        <span class="text-sm font-semibold tracking-wide text-neutral-100 hidden sm:block">CyRoach Monitoring Dashboard</span>
        <span class="text-sm font-semibold text-neutral-100 sm:hidden">CyRoach</span>
    </div>
    <div class="flex gap-1 shrink-0">
        <button id="theme-toggle" onclick="toggleTheme()"
            class="text-xs px-2 py-1.5 rounded-md border border-neutral-700 text-neutral-400 hover:text-neutral-100 hover:bg-neutral-800 transition-colors"
            title="Toggle tema">
            <span id="theme-icon">
                <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
                <svg id="icon-moon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </span>
        </button>
        <a href="{{ route('dashboard') }}" class="text-xs px-3 py-1.5 rounded-md border border-transparent text-neutral-400 hover:text-neutral-100 hover:bg-neutral-800 transition-colors">Live</a>
        <a href="{{ route('missions.index') }}" class="text-xs px-3 py-1.5 rounded-md border border-transparent text-neutral-400 hover:text-neutral-100 hover:bg-neutral-800 transition-colors">Misi</a>
    </div>
</nav>

{{-- BODY --}}
<div class="p-4 sm:p-6 max-w-4xl mx-auto">

    {{-- BACK + EXPORT --}}
    <div class="flex items-center justify-between mb-5 gap-2">
        <a href="{{ route('missions.index') }}" class="inline-flex items-center gap-1 text-xs text-neutral-400 hover:text-neutral-100 transition-colors">
            ← Kembali ke Riwayat Misi
        </a>
        <a href="/missions/{{ $id }}/export-pdf"
            class="text-xs px-3 py-2 rounded-md bg-red-900 border border-red-700 text-neutral-100 hover:bg-red-800 transition-colors shrink-0">
            ↓ Export PDF
        </a>
    </div>

    {{-- HEADER MISI --}}
    <div id="mission-header" class="bg-neutral-900 border border-neutral-800 border-l-2 border-l-red-800 rounded-xl p-4 mb-4">
        <div class="text-xs text-neutral-500">Memuat data misi...</div>
    </div>

    {{-- SUMMARY --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-3 text-center">
            <div class="text-xs text-neutral-500 mb-1">Durasi Misi</div>
            <div class="text-base font-semibold" id="sum-durasi">—</div>
        </div>
        <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-3 text-center">
            <div class="text-xs text-neutral-500 mb-1">Korban Terdeteksi</div>
            <div class="text-base font-semibold text-red-400" id="sum-korban">—</div>
        </div>
        <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-3 text-center">
            <div class="text-xs text-neutral-500 mb-1">Status</div>
            <div id="sum-status">—</div>
        </div>
    </div>

    {{-- SECTION DETEKSI --}}
    <div class="text-xs text-neutral-500 mb-3 pb-2 border-b border-neutral-800">Riwayat Deteksi Korban</div>
    <div id="detections-list">
        <div class="text-xs text-neutral-500 text-center py-8">Memuat data deteksi...</div>
    </div>
    {{-- SECTION TRAJECTORY --}}
    <div class="text-xs text-neutral-500 mt-6 mb-3 pb-2 border-b border-neutral-800">Rekap Trajectory per Kecoa</div>
    <div id="trajectory-list">
        <div class="text-xs text-neutral-500 text-center py-4">Memuat trajectory...</div>
    </div>

</div>

<script>
const missionId = {{ $id }};

// =====================
// THEME TOGGLE
// =====================
function applyTheme(theme) {
    const sun  = document.getElementById('icon-sun');
    const moon = document.getElementById('icon-moon');
    if (theme === 'light') {
        document.documentElement.classList.add('light-mode');
        if (sun)  sun.style.display  = 'none';
        if (moon) moon.style.display = 'inline';
    } else {
        document.documentElement.classList.remove('light-mode');
        if (sun)  sun.style.display  = 'inline';
        if (moon) moon.style.display = 'none';
    }
}
function toggleTheme() {
    const current = localStorage.getItem('theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem('theme', next);
    applyTheme(next);
}
applyTheme(localStorage.getItem('theme') || 'dark');

// =====================
// HELPERS
// =====================
function formatTanggal(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}
function formatJam(dateStr) {
    if (!dateStr) return 'sekarang';
    return new Date(dateStr).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}
function formatDurasi(start, end) {
    if (!start) return '—';
    const diff = Math.floor((new Date(end || Date.now()) - new Date(start)) / 1000);
    const h = Math.floor(diff / 3600);
    const m = Math.floor((diff % 3600) / 60);
    return h > 0 ? `${h}j ${m}m` : `${m}m`;
}
function padNum(n) { return String(n).padStart(3, '0'); }

// =====================
// HEATMAP — IRON colormap + bilinear upscale
// =====================
function ironColor(ratio) {
    const r = Math.max(0, Math.min(1, ratio));
    const stops = [
        [0.00, [0,   0,   0  ]],
        [0.20, [80,  0,   130]],
        [0.40, [150, 0,   100]],
        [0.60, [220, 30,  0  ]],
        [0.75, [255, 120, 0  ]],
        [0.90, [255, 220, 0  ]],
        [1.00, [255, 255, 255]],
    ];
    let lo = stops[0], hi = stops[stops.length - 1];
    for (let i = 0; i < stops.length - 1; i++) {
        if (r >= stops[i][0] && r <= stops[i+1][0]) { lo = stops[i]; hi = stops[i+1]; break; }
    }
    const t = (r - lo[0]) / (hi[0] - lo[0] || 1);
    const c = lo[1].map((v, i) => Math.round(v + (hi[1][i] - v) * t));
    return c;
}

function bilinearUpscale(grid, outSize) {
    const src = 8;
    const out = [];
    for (let y = 0; y < outSize; y++) {
        const row = [];
        for (let x = 0; x < outSize; x++) {
            const gx = (x / (outSize - 1)) * (src - 1);
            const gy = (y / (outSize - 1)) * (src - 1);
            const x0 = Math.floor(gx), x1 = Math.min(x0 + 1, src - 1);
            const y0 = Math.floor(gy), y1 = Math.min(y0 + 1, src - 1);
            const tx = gx - x0, ty = gy - y0;
            const v = grid[y0][x0] * (1-tx)*(1-ty)
                    + grid[y0][x1] * tx*(1-ty)
                    + grid[y1][x0] * (1-tx)*ty
                    + grid[y1][x1] * tx*ty;
            row.push(v);
        }
        out.push(row);
    }
    return out;
}

function drawHeatmap(canvas, grid) {
    const SIZE = canvas.width  || 140;
    const H    = canvas.height || 140;
    canvas.width  = SIZE;
    canvas.height = H;
    canvas.style.imageRendering = 'pixelated';

    const ctx = canvas.getContext('2d');
    const upscaled = bilinearUpscale(grid, 64); // tetap upscale ke 64x64
    const flat = upscaled.flat();
    const mn = Math.min(...flat), mx = Math.max(...flat);
    const imgData = ctx.createImageData(64, 64);

    for (let i = 0; i < 64 * 64; i++) {
        const ratio = (flat[i] - mn) / (mx - mn || 1);
        const [r, g, b] = ironColor(ratio);
        imgData.data[i*4]   = r;
        imgData.data[i*4+1] = g;
        imgData.data[i*4+2] = b;
        imgData.data[i*4+3] = 255;
    }

    // Gambar ke offscreen 64x64 lalu stretch ke canvas ukuran fix
    const off = document.createElement('canvas');
    off.width = 64; off.height = 64;
    off.getContext('2d').putImageData(imgData, 0, 0);

    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(off, 0, 0, SIZE, H);

    // Label suhu — font lebih kecil karena canvas lebih kecil
    ctx.fillStyle = 'rgba(0,0,0,0.80)';
    ctx.fillRect(0, H - 16, SIZE, 16);
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 7px sans-serif';
    ctx.textAlign = 'left';
    ctx.fillText(`MAX ${mx.toFixed(1)}°C`, 3, H - 4);
    ctx.textAlign = 'right';
    ctx.fillText(`MIN ${mn.toFixed(1)}°C`, SIZE - 3, H - 4);
}

// =====================
// RENDER
// =====================
function renderHeader(m) {
    document.getElementById('mission-header').innerHTML = `
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-base font-semibold text-neutral-100 mb-1">
                    Misi #${padNum(m.mission_number)} · ${formatTanggal(m.started_at)}
                </div>
                <div class="text-xs text-neutral-500">
                    ${formatJam(m.started_at)} – ${m.ended_at ? formatJam(m.ended_at) : 'sekarang'}
                </div>
            </div>
            <span class="text-xs px-2 py-1 rounded-full shrink-0 ${m.status === 'berlangsung'
                ? 'bg-amber-900 text-amber-400 border border-amber-700'
                : 'bg-neutral-800 text-neutral-400 border border-neutral-700'}">
                ${m.status === 'berlangsung' ? '● Berlangsung' : '✓ Selesai'}
            </span>
        </div>
    `;
}

function renderSummary(m) {
    document.getElementById('sum-durasi').textContent = formatDurasi(m.started_at, m.ended_at);
    document.getElementById('sum-korban').textContent = m.detections?.length ?? 0;
    document.getElementById('sum-status').innerHTML = `
        <span class="text-xs px-2 py-1 rounded-full ${m.status === 'berlangsung'
            ? 'bg-amber-900 text-amber-400 border border-amber-700'
            : 'bg-neutral-800 text-neutral-400 border border-neutral-700'}">
            ${m.status === 'berlangsung' ? '● Berlangsung' : '✓ Selesai'}
        </span>`;
}

function renderDetections(detections) {
    const container = document.getElementById('detections-list');

    if (!detections || detections.length === 0) {
        container.innerHTML = `
            <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-6 text-center">
                <div class="text-xs text-neutral-500">Tidak ada deteksi korban pada misi ini</div>
            </div>`;
        return;
    }

    container.innerHTML = detections.map((d, idx) => `
        <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-4 mb-3">
            <div class="flex items-center justify-between mb-3 gap-2">
                <div class="text-xs font-semibold text-neutral-100">
                    Deteksi #${idx + 1} — ${d.device_id.replace('kecoa_', 'Kecoa #').replace(/^Kecoa #0+/, 'Kecoa #')}
                </div>
                <div class="text-xs text-neutral-500 shrink-0">${new Date(d.detected_at).toLocaleString('id-ID')}</div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-lg overflow-hidden border border-neutral-800 inline-block">
                    <canvas class="detection-hmap block" data-idx="${idx}" style="width:140px;height:140px;"></canvas>
                </div>
                <div class="flex flex-col gap-2">
                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-neutral-950 border border-neutral-800 rounded-lg p-2 text-center">
                            <div class="text-xs text-neutral-500 mb-1">Pitch</div>
                            <div class="text-xs text-neutral-300">${(d.pitch ?? 0).toFixed(1)}°</div>
                        </div>
                        <div class="bg-neutral-950 border border-neutral-800 rounded-lg p-2 text-center">
                            <div class="text-xs text-neutral-500 mb-1">Roll</div>
                            <div class="text-xs text-neutral-300">${(d.roll ?? 0).toFixed(1)}°</div>
                        </div>
                        <div class="bg-neutral-950 border border-neutral-800 rounded-lg p-2 text-center">
                            <div class="text-xs text-neutral-500 mb-1">Yaw</div>
                            <div class="text-xs text-neutral-300">${(d.yaw ?? 0).toFixed(1)}°</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-neutral-950 border border-neutral-800 rounded-lg p-2 text-center">
                            <div class="text-xs text-neutral-500 mb-1">Suhu Maks</div>
                            <div class="text-sm font-semibold text-red-400">${(d.suhu_max ?? 0).toFixed(1)}°C</div>
                        </div>
                        <div class="bg-neutral-950 border border-neutral-800 rounded-lg p-2 text-center">
                            <div class="text-xs text-neutral-500 mb-1">Suhu Min</div>
                            <div class="text-sm font-semibold text-blue-400">${(d.suhu_min ?? 0).toFixed(1)}°C</div>
                        </div>
                    </div>
                    <div class="bg-neutral-950 border border-red-900 rounded-lg p-2 text-center mt-auto">
                        <div class="text-xs text-red-400 font-semibold">⚠ Korban Terdeteksi</div>
                        <div class="text-xs text-neutral-500 mt-0.5">Suhu melebihi ambang batas 37.5°C</div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    setTimeout(() => {
        detections.forEach((d, idx) => {
            const canvas = document.querySelector(`.detection-hmap[data-idx="${idx}"]`);
            if (canvas && d.thermal_snapshot) drawHeatmap(canvas, d.thermal_snapshot);
        });
    }, 50);
}

// =====================
// TRAJECTORY (sama seperti app.js)
// =====================
function drawTrajectoryDetail(canvas, history) {
    const W = canvas.offsetWidth || 300;
    const H = 160;
    canvas.width = W;
    canvas.height = H;
    const ctx = canvas.getContext('2d');

    ctx.fillStyle = '#0a0a0a';
    ctx.fillRect(0, 0, W, H);

    ctx.strokeStyle = '#262626';
    ctx.lineWidth = 0.5;
    for (let x = 0; x <= W; x += W/4) {
        ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,H); ctx.stroke();
    }
    for (let y = 0; y <= H; y += H/4) {
        ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(W,y); ctx.stroke();
    }
    ctx.strokeStyle = '#404040';
    ctx.lineWidth = 1;
    ctx.beginPath(); ctx.moveTo(W/2,0); ctx.lineTo(W/2,H); ctx.stroke();
    ctx.beginPath(); ctx.moveTo(0,H/2); ctx.lineTo(W,H/2); ctx.stroke();

    if (!history || history.length < 2) {
        ctx.fillStyle = '#404040';
        ctx.font = '10px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Tidak ada data trajectory', W/2, H/2+4);
        return;
    }

    const scale = 2;
    ctx.strokeStyle = '#ef4444';
    ctx.lineWidth = 1.5;
    ctx.beginPath();
    history.forEach((pt, i) => {
        const x = W/2 + ((pt.roll ?? 0) * scale);
        const y = H/2 - ((pt.pitch ?? 0) * scale);
        if (i === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
    });
    ctx.stroke();

    // Titik start
    const first = history[0];
    ctx.fillStyle = '#22c55e';
    ctx.beginPath();
    ctx.arc(W/2 + (first.roll??0)*scale, H/2 - (first.pitch??0)*scale, 4, 0, Math.PI*2);
    ctx.fill();
    ctx.fillStyle = '#22c55e';
    ctx.font = '8px sans-serif';
    ctx.textAlign = 'left';
    ctx.fillText('START', W/2 + (first.roll??0)*scale + 6, H/2 - (first.pitch??0)*scale + 3);

    // Titik akhir
    const last = history[history.length-1];
    const lx = W/2 + (last.roll??0)*scale;
    const ly = H/2 - (last.pitch??0)*scale;
    ctx.fillStyle = '#ef4444';
    ctx.beginPath();
    ctx.arc(lx, ly, 4, 0, Math.PI*2);
    ctx.fill();

    ctx.fillStyle = '#525252';
    ctx.font = '8px sans-serif';
    ctx.textAlign = 'left';
    ctx.fillText('Roll →', 4, H-4);
    ctx.textAlign = 'right';
    ctx.fillText('↑ Pitch', W-4, 10);

    // Info titik
    ctx.fillStyle = '#737373';
    ctx.textAlign = 'left';
    ctx.fillText(`${history.length} titik`, 4, 12);
}

function renderTrajectory(trajectoryByDevice, telemetryByDevice) {
    const container = document.getElementById('trajectory-list');
    if (!container) return;

    const deviceIds = Object.keys(trajectoryByDevice);

    if (deviceIds.length === 0) {
        container.innerHTML = '<div class="text-xs text-neutral-500 text-center py-4">Tidak ada data trajectory</div>';
        return;
    }

    container.innerHTML = deviceIds.map(deviceId => {
        const num    = deviceId.replace('kecoa_', '').replace(/^0+/, '');
        const points = trajectoryByDevice[deviceId];
        const telem  = (telemetryByDevice && telemetryByDevice[deviceId]) || {};
        const avgSig = telem.avg_signal    != null ? telem.avg_signal.toFixed(1) + '%' : '—';
        const dist   = telem.distance_total_m != null ? telem.distance_total_m.toFixed(2) + ' m' : '—';

        // Warna progress bar signal
        const sigVal = telem.avg_signal ?? 0;
        const sigColor = sigVal >= 60 ? 'bg-green-500' : sigVal >= 30 ? 'bg-yellow-500' : 'bg-red-500';

        return `
            <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-4 mb-3">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs font-semibold text-neutral-100">Kecoa #${num}</div>
                    <div class="text-xs text-neutral-500">${points.length} titik data</div>
                </div>
                <div class="rounded-lg overflow-hidden border border-neutral-800">
                    <canvas class="trajectory-canvas block w-full" data-device="${deviceId}" style="height:160px;"></canvas>
                </div>
                <div class="grid grid-cols-3 gap-2 mt-2">
                    <div class="bg-neutral-950 border border-neutral-800 rounded p-2 text-center">
                        <div class="text-xs text-neutral-500 mb-0.5">Pitch awal</div>
                        <div class="text-xs text-neutral-300">${(points[0]?.pitch ?? 0).toFixed(1)}°</div>
                    </div>
                    <div class="bg-neutral-950 border border-neutral-800 rounded p-2 text-center">
                        <div class="text-xs text-neutral-500 mb-0.5">Roll awal</div>
                        <div class="text-xs text-neutral-300">${(points[0]?.roll ?? 0).toFixed(1)}°</div>
                    </div>
                    <div class="bg-neutral-950 border border-neutral-800 rounded p-2 text-center">
                        <div class="text-xs text-neutral-500 mb-0.5">Yaw awal</div>
                        <div class="text-xs text-neutral-300">${(points[0]?.yaw ?? 0).toFixed(1)}°</div>
                    </div>
                </div>

                {{-- BARIS BARU: Telemetri rata-rata signal & total jarak --}}
                <div class="grid grid-cols-2 gap-2 mt-2">
                    <div class="bg-neutral-950 border border-neutral-800 rounded p-2">
                        <div class="text-xs text-neutral-500 mb-1">Rata-rata Signal</div>
                        <div class="text-xs font-semibold text-neutral-200 mb-1">${avgSig}</div>
                        <div class="w-full bg-neutral-800 rounded-full h-1">
                            <div class="${sigColor} h-1 rounded-full transition-all" style="width:${Math.min(sigVal,100)}%"></div>
                        </div>
                    </div>
                    <div class="bg-neutral-950 border border-neutral-800 rounded p-2">
                        <div class="text-xs text-neutral-500 mb-1">Total Jarak Tempuh</div>
                        <div class="text-xs font-semibold text-neutral-200">${dist}</div>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    setTimeout(() => {
        deviceIds.forEach(deviceId => {
            const canvas = document.querySelector(`.trajectory-canvas[data-device="${deviceId}"]`);
            if (canvas) drawTrajectoryDetail(canvas, trajectoryByDevice[deviceId]);
        });
    }, 50);
}

// =====================
// LOAD DATA
// =====================
fetch(`/api/missions/${missionId}`)
    .then(r => r.json())
    .then(m => {
        renderHeader(m);
        renderSummary(m);
        renderDetections(m.detections);
        renderTrajectory(m.trajectory_by_device ?? {}, m.telemetry_by_device ?? {});
    })
    .catch(() => {
        document.getElementById('mission-header').innerHTML =
            '<div class="text-xs text-red-400">Gagal memuat data misi</div>';
        document.getElementById('detections-list').innerHTML = '';
        document.getElementById('trajectory-list').innerHTML = '';
    });
</script>

</body>
</html>