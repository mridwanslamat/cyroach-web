@extends('layouts.app')

@section('title', 'Mission Details')
@section('page-title', 'Mission Details')

@section('content')
<div class="p-6 max-w-5xl mx-auto">

    {{-- BACK + EXPORT --}}
    <div class="flex items-center justify-between mb-5 gap-2">
        <a href="{{ route('missions.index') }}"
            class="inline-flex items-center gap-1.5 text-xs font-mono cyroach-muted hover:cyroach-text transition-colors">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Kembali ke Riwayat Misi
        </a>
        <a href="/missions/{{ $id }}/export-pdf"
            class="inline-flex items-center gap-1.5 text-xs font-mono px-3 py-2 rounded-lg font-semibold transition-all shrink-0"
            style="background-color: var(--accent); color: white;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export PDF
        </a>
    </div>

    {{-- MISSION HEADER --}}
    <div class="cy-card p-5 mb-4" id="mission-header">
        <div class="text-xs font-mono cyroach-muted">Memuat data misi...</div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="cy-card p-4 text-center">
            <div class="text-xs font-mono cyroach-muted uppercase tracking-widest mb-2">Durasi Misi</div>
            <div class="text-2xl font-display font-bold cyroach-text" id="sum-durasi">—</div>
        </div>
        <div class="cy-card p-4 text-center">
            <div class="text-xs font-mono cyroach-muted uppercase tracking-widest mb-2">Korban Terdeteksi</div>
            <div class="text-2xl font-display font-bold text-red-400" id="sum-korban">—</div>
        </div>
        <div class="cy-card p-4 text-center">
            <div class="text-xs font-mono cyroach-muted uppercase tracking-widest mb-2">Status</div>
            <div id="sum-status">—</div>
        </div>
    </div>

    {{-- DETEKSI KORBAN --}}
    <div class="flex items-center justify-between mb-3">
        <div class="text-sm font-semibold cyroach-text">Riwayat Deteksi Korban</div>
        <div class="text-xs font-mono cyroach-muted">SENSORS ACTIVE</div>
    </div>
    <div id="detections-list" class="mb-6">
        <div class="text-xs font-mono cyroach-muted text-center py-8">Memuat data deteksi...</div>
    </div>

    {{-- TRAJECTORY --}}
    <div class="flex items-center justify-between mb-3">
        <div class="text-sm font-semibold cyroach-text">Rekap Trajectory per Kecoa</div>
        <div class="text-xs font-mono cyroach-muted">COORDINATE MAPPING ON</div>
    </div>
    <div id="trajectory-list">
        <div class="text-xs font-mono cyroach-muted text-center py-4">Memuat trajectory...</div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const missionId = {{ $id }};

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
// HEATMAP
// =====================
function ironColor(ratio) {
    const r = Math.max(0, Math.min(1, ratio));
    const stops = [
        [0.00, [0,0,0]],[0.20,[80,0,130]],[0.40,[150,0,100]],
        [0.60, [220,30,0]],[0.75,[255,120,0]],[0.90,[255,220,0]],[1.00,[255,255,255]],
    ];
    let lo = stops[0], hi = stops[stops.length-1];
    for (let i = 0; i < stops.length-1; i++) {
        if (r >= stops[i][0] && r <= stops[i+1][0]) { lo=stops[i]; hi=stops[i+1]; break; }
    }
    const t = (r-lo[0])/(hi[0]-lo[0]||1);
    return lo[1].map((v,i) => Math.round(v+(hi[1][i]-v)*t));
}

function bilinearUpscale(grid, outSize) {
    const src = 8;
    const out = [];
    for (let y = 0; y < outSize; y++) {
        const row = [];
        for (let x = 0; x < outSize; x++) {
            const gx = (x/(outSize-1))*(src-1);
            const gy = (y/(outSize-1))*(src-1);
            const x0=Math.floor(gx),x1=Math.min(x0+1,src-1);
            const y0=Math.floor(gy),y1=Math.min(y0+1,src-1);
            const tx=gx-x0,ty=gy-y0;
            row.push(grid[y0][x0]*(1-tx)*(1-ty)+grid[y0][x1]*tx*(1-ty)+grid[y1][x0]*(1-tx)*ty+grid[y1][x1]*tx*ty);
        }
        out.push(row);
    }
    return out;
}

function drawHeatmap(canvas, grid) {
    const SIZE = canvas.width || 160;
    const H = canvas.height || 160;
    canvas.width = SIZE; canvas.height = H;
    const ctx = canvas.getContext('2d');
    const upscaled = bilinearUpscale(grid, 64);
    const flat = upscaled.flat();
    const mn = Math.min(...flat), mx = Math.max(...flat);
    const imgData = ctx.createImageData(64, 64);
    for (let i = 0; i < 64*64; i++) {
        const [r,g,b] = ironColor((flat[i]-mn)/(mx-mn||1));
        imgData.data[i*4]=r; imgData.data[i*4+1]=g; imgData.data[i*4+2]=b; imgData.data[i*4+3]=255;
    }
    const off = document.createElement('canvas');
    off.width=64; off.height=64;
    off.getContext('2d').putImageData(imgData,0,0);
    ctx.imageSmoothingEnabled=true; ctx.imageSmoothingQuality='high';
    ctx.drawImage(off,0,0,SIZE,H);

    // Label overlay
    ctx.fillStyle='rgba(0,0,0,0.75)';
    ctx.fillRect(0,H-18,SIZE,18);
    ctx.fillStyle='#fff';
    ctx.font='bold 8px monospace';
    ctx.textAlign='left';
    ctx.fillText(`MAX ${mx.toFixed(1)}°C`,4,H-5);
    ctx.textAlign='right';
    ctx.fillText(`MIN ${mn.toFixed(1)}°C`,SIZE-4,H-5);

    // CALIB tag
    ctx.fillStyle='rgba(0,0,0,0.6)';
    ctx.fillRect(2,2,62,14);
    ctx.fillStyle='#ef4444';
    ctx.font='bold 7px monospace';
    ctx.textAlign='left';
    ctx.fillText('CALIB: AUTO',4,12);

    // RECORDED FEED badge
    ctx.fillStyle='rgba(220,38,38,0.85)';
    ctx.fillRect(2,18,80,13);
    ctx.fillStyle='#fff';
    ctx.font='bold 7px monospace';
    ctx.fillText('RECORDED FEED',5,28);
}

// =====================
// TRAJECTORY
// =====================
function drawTrajectoryDetail(canvas, history) {
    const W = canvas.offsetWidth || 500;
    const H = 200;
    canvas.width = W; canvas.height = H;
    const ctx = canvas.getContext('2d');

    // Background grid
    ctx.fillStyle = '#0a0a0a';
    ctx.fillRect(0,0,W,H);
    ctx.strokeStyle='#1a1a1a'; ctx.lineWidth=0.5;
    for(let x=0;x<=W;x+=W/8){ctx.beginPath();ctx.moveTo(x,0);ctx.lineTo(x,H);ctx.stroke();}
    for(let y=0;y<=H;y+=H/4){ctx.beginPath();ctx.moveTo(0,y);ctx.lineTo(W,y);ctx.stroke();}
    ctx.strokeStyle='#2a2a2a'; ctx.lineWidth=1;
    ctx.beginPath();ctx.moveTo(0,H/2);ctx.lineTo(W,H/2);ctx.stroke();

    if (!history || history.length < 2) {
        ctx.fillStyle='#404040'; ctx.font='10px monospace'; ctx.textAlign='center';
        ctx.fillText('Tidak ada data trajectory',W/2,H/2+4);
        return;
    }

    // Auto-rescale
    // Pakai roll sebagai X, pitch sebagai Y
    let minX=Infinity,maxX=-Infinity,minY=Infinity,maxY=-Infinity;
    history.forEach(pt => {
        const x = pt.x ?? pt.roll ?? 0;
        const y = pt.y ?? pt.pitch ?? 0;
        if(x<minX)minX=x; if(x>maxX)maxX=x;
        if(y<minY)minY=y; if(y>maxY)maxY=y;
    });
    const range=Math.max(maxX-minX,maxY-minY)||0.01;
    const pad=range*0.18;
    const dX=maxX-minX+2*pad||1, dY=maxY-minY+2*pad||1;
    const toC = (x,y) => ({
        cx:((x-minX+pad)/dX)*W,
        cy:H-((y-minY+pad)/dY)*H
    });

    // Trajectory line gradient
    for(let i=1;i<history.length;i++){
        const alpha=0.3+0.7*(i/history.length);
        ctx.strokeStyle=`rgba(239,68,68,${alpha.toFixed(2)})`;
        ctx.lineWidth=1.5; ctx.lineJoin='round'; ctx.lineCap='round';
        ctx.beginPath();
        const p0=toC(history[i-1].x??history[i-1].roll??0, history[i-1].y??history[i-1].pitch??0);
        const p1=toC(history[i].x??history[i].roll??0, history[i].y??history[i].pitch??0);
        ctx.moveTo(p0.cx,p0.cy); ctx.lineTo(p1.cx,p1.cy); ctx.stroke();
    }


    // Start dot
    const fp=toC(history[0].x??history[0].roll??0, history[0].y??history[0].pitch??0);
    ctx.fillStyle='#22c55e';
    ctx.beginPath(); ctx.arc(fp.cx,fp.cy,5,0,Math.PI*2); ctx.fill();

    // End dot
    const lp=toC(history[history.length-1].x??history[history.length-1].roll??0, history[history.length-1].y??history[history.length-1].pitch??0);
    ctx.fillStyle='#ef4444';
    ctx.beginPath(); ctx.arc(lp.cx,lp.cy,5,0,Math.PI*2); ctx.fill();

    // Scale label
    ctx.fillStyle='#404040'; ctx.font='8px monospace'; ctx.textAlign='left';
    ctx.fillText(`NAV SCALE: 1:${Math.ceil(range*10)/10}`,4,H-4);

    // Coord label
    const last=history[history.length-1];
    ctx.fillStyle='#525252'; ctx.textAlign='right';
    ctx.fillText(`LAT: ${(last.x??last.roll??0).toFixed(4)}`,W-4,12);
    ctx.fillText(`LNG: ${(last.y??last.pitch??0).toFixed(4)}`,W-4,22);
    // Bearing dari start ke end
    const sx=history[0].x??history[0].roll??0, sy=history[0].y??history[0].pitch??0;
    const ex=history[history.length-1].x??history[history.length-1].roll??0, ey=history[history.length-1].y??history[history.length-1].pitch??0;
    const angleDeg=Math.atan2(ex-sx, ey-sy)*(180/Math.PI);
    const angleStr=(angleDeg>=0?'+':'')+angleDeg.toFixed(1)+'°';
    const bearingLabel='Kemiringan Kecoa: '+angleStr+(angleDeg>0?' (kanan)':angleDeg<0?' (kiri)':' (lurus)');
    ctx.fillStyle='#a3a3a3'; ctx.font='bold 9px monospace'; ctx.textAlign='left';
    ctx.fillText(bearingLabel,4,12);
    // Bearing dari start ke end
    const sx=history[0].x??history[0].roll??0, sy=history[0].y??history[0].pitch??0;
    const ex=history[history.length-1].x??history[history.length-1].roll??0, ey=history[history.length-1].y??history[history.length-1].pitch??0;
    const angleDeg=Math.atan2(ex-sx, ey-sy)*(180/Math.PI);
    const angleStr=(angleDeg>=0?'+':'')+angleDeg.toFixed(1)+'°';
    const bearingLabel='Kemiringan Kecoa: '+angleStr+(angleDeg>0?' (kanan)':angleDeg<0?' (kiri)':' (lurus)');
    ctx.fillStyle='#a3a3a3'; ctx.font='bold 9px monospace'; ctx.textAlign='left';
    ctx.fillText(bearingLabel,4,12);
}

// =====================
// RENDER HEADER
// =====================
function renderHeader(m) {
    const isSelesai = m.status !== 'berlangsung';
    const badgeStyle = isSelesai
        ? 'background-color:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(16,185,129,0.35);'
        : 'background-color:rgba(245,158,11,0.15);color:#fbbf24;border:1px solid rgba(245,158,11,0.35);';
    document.getElementById('mission-header').innerHTML = `
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-xl font-display font-bold cyroach-text mb-1">
                    Misi #${padNum(m.mission_number)} · ${formatTanggal(m.started_at)}
                </div>
                <div class="text-xs cyroach-muted" style="font-family:var(--font-mono);">
                    ${formatJam(m.started_at)} – ${m.ended_at ? formatJam(m.ended_at) : 'sekarang'} (UTC+7)
                </div>
            </div>
            <span class="text-xs px-3 py-1.5 rounded-full shrink-0 font-medium"
                style="font-family:var(--font-mono);${badgeStyle}">
                ● ${isSelesai ? 'Selesai' : 'Berlangsung'}
            </span>
        </div>
    `;
}

// =====================
// RENDER SUMMARY
// =====================
function renderSummary(m) {
    const isSelesai = m.status !== 'berlangsung';
    const badgeStyle = isSelesai
        ? 'background-color:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(16,185,129,0.35);'
        : 'background-color:rgba(245,158,11,0.15);color:#fbbf24;border:1px solid rgba(245,158,11,0.35);';
    document.getElementById('sum-durasi').textContent = formatDurasi(m.started_at, m.ended_at);
    document.getElementById('sum-korban').textContent = m.detections?.length ?? 0;
    document.getElementById('sum-status').innerHTML = `
        <span class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 rounded-full font-medium"
            style="font-family:var(--font-mono);${badgeStyle}">
            <span class="w-2 h-2 rounded-full" style="background-color:${isSelesai ? '#34d399' : '#fbbf24'};"></span>
            ${isSelesai ? 'Selesai' : 'Berlangsung'}
        </span>`;
}

// =====================
// RENDER DETEKSI
// =====================
function renderDetections(detections) {
    const container = document.getElementById('detections-list');
    if (!detections || detections.length === 0) {
        container.innerHTML = `
            <div class="cy-card p-8 text-center">
                <div class="text-xs font-mono cyroach-muted">Tidak ada deteksi korban pada misi ini</div>
            </div>`;
        return;
    }

    container.innerHTML = detections.map((d, idx) => {
        const devNum = d.device_id.replace('kecoa_','').replace(/^0+/,'');
        return `
        <div class="cy-card p-4 mb-3">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-0.5 h-4 rounded" style="background-color:var(--accent);"></div>
                    <span class="text-sm font-semibold cyroach-text">Deteksi #${idx+1} — Kecoa #${devNum}</span>
                </div>
                <div class="text-xs font-mono cyroach-muted">${new Date(d.detected_at).toLocaleString('id-ID')}</div>
            </div>
            <div class="flex gap-4">
                {{-- Thermal --}}
                <div class="shrink-0 rounded-lg overflow-hidden border cyroach-border relative" style="width:180px;height:180px;">
                    <canvas class="detection-hmap block" data-idx="${idx}" width="180" height="180"></canvas>
                </div>
                {{-- Data --}}
                <div class="flex-1 flex flex-col gap-2.5 min-w-0">
                    <div>
                        <div class="text-xs cyroach-muted uppercase tracking-widest mb-1.5" style="font-family:var(--font-mono);font-size:9px;">Suhu</div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="cy-card-raised p-2.5 text-center">
                                <div class="text-xs cyroach-muted mb-0.5" style="font-size:9px;font-family:var(--font-mono);">SUHU MAKS</div>
                                <div class="text-xl font-display font-bold text-red-400">${(parseFloat(d.suhu_max)??0).toFixed(1)}°C</div>
                            </div>
                            <div class="cy-card-raised p-2.5 text-center">
                                <div class="text-xs cyroach-muted mb-0.5" style="font-size:9px;font-family:var(--font-mono);">SUHU MIN</div>
                                <div class="text-xl font-display font-bold text-blue-400">${(parseFloat(d.suhu_min)??0).toFixed(1)}°C</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs cyroach-muted uppercase tracking-widest mb-1.5" style="font-family:var(--font-mono);font-size:9px;">Orientasi</div>
                        <div class="grid grid-cols-3 gap-1.5">
                            <div class="cy-card-raised p-2 text-center">
                                <div class="text-xs cyroach-muted mb-0.5" style="font-size:9px;font-family:var(--font-mono);">PITCH</div>
                                <div class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">${(parseFloat(d.pitch)??0).toFixed(1)}°</div>
                            </div>
                            <div class="cy-card-raised p-2 text-center">
                                <div class="text-xs cyroach-muted mb-0.5" style="font-size:9px;font-family:var(--font-mono);">ROLL</div>
                                <div class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">${(parseFloat(d.roll)??0).toFixed(1)}°</div>
                            </div>
                            <div class="cy-card-raised p-2 text-center">
                                <div class="text-xs cyroach-muted mb-0.5" style="font-size:9px;font-family:var(--font-mono);">YAW</div>
                                <div class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">${(parseFloat(d.yaw)??0).toFixed(1)}°</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-auto rounded-lg p-3 flex items-center gap-2"
                        style="background-color:rgba(127,29,29,0.2);border:1px solid rgba(185,28,28,0.3);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        <div>
                            <div class="text-xs font-semibold text-red-400">Korban Terdeteksi</div>
                            <div class="text-xs cyroach-muted" style="font-size:10px;">Suhu melebihi ambang batas 37.5°C</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');

    setTimeout(() => {
        detections.forEach((d, idx) => {
            const canvas = document.querySelector(`.detection-hmap[data-idx="${idx}"]`);
            if (canvas && d.thermal_snapshot) drawHeatmap(canvas, d.thermal_snapshot);
        });
    }, 50);
}

// =====================
// RENDER TRAJECTORY
// =====================
function renderTrajectory(trajectoryByDevice, telemetryByDevice) {
    const container = document.getElementById('trajectory-list');
    if (!container) return;
    const deviceIds = Object.keys(trajectoryByDevice);

    if (deviceIds.length === 0) {
        container.innerHTML = '<div class="text-xs font-mono cyroach-muted text-center py-4">Tidak ada data trajectory</div>';
        return;
    }

    container.innerHTML = deviceIds.map(deviceId => {
        const num = deviceId.replace('kecoa_','').replace(/^0+/,'');
        const points = trajectoryByDevice[deviceId];
        const telem = (telemetryByDevice && telemetryByDevice[deviceId]) || {};
        const avgSig = telem.avg_signal != null ? parseFloat(telem.avg_signal).toFixed(1)+'%' : '—';
        const dist = telem.distance_total_m != null ? parseFloat(telem.distance_total_m).toFixed(2)+' m' : '—';
        const sigVal = telem.avg_signal ?? 0;
        const sigColor = sigVal>=60 ? '#22c55e' : sigVal>=30 ? '#f59e0b' : '#ef4444';

        return `
        <div class="cy-card p-4 mb-3">
            <div class="flex items-center justify-between mb-3">
                <div class="text-sm font-semibold cyroach-text">Kecoa #${num}</div>
                <div class="text-xs font-mono cyroach-muted">${points.length} titik data</div>
            </div>
            <div class="rounded-lg overflow-hidden border cyroach-border mb-3" style="background-color:#0a0a0a;">
                <canvas class="trajectory-canvas block w-full" data-device="${deviceId}" style="height:200px;"></canvas>
            </div>
            <div class="grid grid-cols-5 gap-2">
                <div class="cy-card-raised p-2.5">
                    <div class="text-xs cyroach-muted mb-1" style="font-family:var(--font-mono);font-size:9px;">Pitch Awal</div>
                    <div class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">${(parseFloat(points[0]?.pitch)??0).toFixed(1)}°</div>
                </div>
                <div class="cy-card-raised p-2.5">
                    <div class="text-xs cyroach-muted mb-1" style="font-family:var(--font-mono);font-size:9px;">Roll Awal</div>
                    <div class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">${(parseFloat(points[0]?.roll)??0).toFixed(1)}°</div>
                </div>
                <div class="cy-card-raised p-2.5">
                    <div class="text-xs cyroach-muted mb-1" style="font-family:var(--font-mono);font-size:9px;">Yaw Awal</div>
                    <div class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">${(parseFloat(points[0]?.yaw)??0).toFixed(1)}°</div>
                </div>
                <div class="cy-card-raised p-2.5">
                    <div class="text-xs cyroach-muted mb-1" style="font-family:var(--font-mono);font-size:9px;">Rata Sinyal</div>
                    <div class="text-xs font-semibold cyroach-text mb-1.5" style="font-family:var(--font-mono);">${avgSig}</div>
                    <div class="w-full rounded-full h-1" style="background-color:var(--bg-hover);">
                        <div class="h-1 rounded-full" style="width:${Math.min(sigVal,100)}%;background-color:${sigColor};"></div>
                    </div>
                </div>
                <div class="cy-card-raised p-2.5">
                    <div class="text-xs cyroach-muted mb-1" style="font-family:var(--font-mono);font-size:9px;">Total Jarak</div>
                    <div class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">${dist}</div>
                </div>
            </div>
        </div>`;
    }).join('');

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            deviceIds.forEach(deviceId => {
                const canvas = document.querySelector(`.trajectory-canvas[data-device="${deviceId}"]`);
                if (!canvas) return;
                // Force ukuran sebelum draw
                canvas.style.display = 'block';
                const w = canvas.parentElement?.offsetWidth || 800;
                canvas.width = w;
                canvas.height = 200;
                drawTrajectoryDetail(canvas, trajectoryByDevice[deviceId]);
            });
        });
    });
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
            '<div class="text-xs font-mono text-red-400">Gagal memuat data misi</div>';
    });
</script>
@endpush
















