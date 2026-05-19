import './bootstrap';
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

// =====================
// STATE
// =====================
const devices = {};
const notifications = [];
const trajectoryHistory = {};

const STATUS_MAP = {
    confirmed: { label: 'Confirmed Live', cls: 'bg-emerald-900 text-emerald-400 border border-emerald-700' },
    probable:  { label: 'Probable Live',  cls: 'bg-amber-900 text-amber-400 border border-amber-700' },
    possible:  { label: 'Possible Live',  cls: 'bg-orange-900 text-orange-400 border border-orange-700' },
    nosig:     { label: 'No signal',      cls: 'bg-neutral-800 text-neutral-500 border border-neutral-700' },
};

// =====================
// BILINEAR UPSCALE 8x8 → 64x64
// =====================
function bilinearUpscale(src, srcSize, dstSize) {
    const dst = new Float32Array(dstSize * dstSize);
    for (let oy = 0; oy < dstSize; oy++) {
        for (let ox = 0; ox < dstSize; ox++) {
            const fx = (ox + 0.5) * srcSize / dstSize - 0.5;
            const fy = (oy + 0.5) * srcSize / dstSize - 0.5;
            let x0 = Math.floor(fx), y0 = Math.floor(fy);
            let x1 = x0 + 1, y1 = y0 + 1;
            const tx = fx - x0, ty = fy - y0;
            x0 = Math.max(0, Math.min(srcSize-1, x0));
            x1 = Math.max(0, Math.min(srcSize-1, x1));
            y0 = Math.max(0, Math.min(srcSize-1, y0));
            y1 = Math.max(0, Math.min(srcSize-1, y1));
            const v00 = src[y0*srcSize+x0], v10 = src[y0*srcSize+x1];
            const v01 = src[y1*srcSize+x0], v11 = src[y1*srcSize+x1];
            dst[oy*dstSize+ox] = (v00 + tx*(v10-v00)) + ty*((v01 + tx*(v11-v01)) - (v00 + tx*(v10-v00)));
        }
    }
    return dst;
}

// =====================
// IRON COLORMAP
// =====================
const IRON_STOPS  = [0, 0.20, 0.40, 0.60, 0.75, 0.90, 1.0];
const IRON_COLORS = [
    [0,   0,   0  ],
    [51,  0,   128],
    [153, 0,   153],
    [255, 0,   0  ],
    [255, 128, 0  ],
    [255, 255, 0  ],
    [255, 255, 255],
];

function ironColormap(norm) {
    norm = Math.max(0, Math.min(1, norm));
    let i = 0;
    while (i < IRON_STOPS.length - 2 && norm > IRON_STOPS[i+1]) i++;
    const t  = (norm - IRON_STOPS[i]) / (IRON_STOPS[i+1] - IRON_STOPS[i]);
    const c0 = IRON_COLORS[i], c1 = IRON_COLORS[i+1];
    return [
        Math.round(c0[0] + t*(c1[0]-c0[0])),
        Math.round(c0[1] + t*(c1[1]-c0[1])),
        Math.round(c0[2] + t*(c1[2]-c0[2])),
    ];
}

// =====================
// DRAW HEATMAP
// =====================
const heatmapCache = {};

function drawHeatmap(canvas, grid, w, h) {
    canvas.width  = w;
    canvas.height = h;
    const ctx = canvas.getContext('2d');

    const flat  = new Float32Array(grid.flat());
    const mn    = Math.min(...flat), mx = Math.max(...flat);
    const range = mx - mn || 1;
    const upscaled = bilinearUpscale(flat, 8, 64);

    const key = `${w}x${h}`;
    if (!heatmapCache[key]) {
        heatmapCache[key] = document.createElement('canvas');
        heatmapCache[key].width  = 64;
        heatmapCache[key].height = 64;
    }
    const offscreen = heatmapCache[key];
    const offCtx    = offscreen.getContext('2d');
    const imgData   = offCtx.createImageData(64, 64);

    for (let i = 0; i < 64*64; i++) {
        const norm = (upscaled[i] - mn) / range;
        const [r, g, b] = ironColormap(norm);
        imgData.data[i*4]   = r;
        imgData.data[i*4+1] = g;
        imgData.data[i*4+2] = b;
        imgData.data[i*4+3] = 255;
    }

    offCtx.putImageData(imgData, 0, 0);
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(offscreen, 0, 0, w, h);

    ctx.fillStyle = 'rgba(0,0,0,0.80)';
    ctx.fillRect(0, h-16, w, 16);
    ctx.fillStyle = '#fff';
    ctx.font = 'bold 8px sans-serif';
    ctx.textAlign = 'left';
    ctx.fillText(`MAX ${mx.toFixed(1)}°C`, 4, h-4);
    ctx.textAlign = 'right';
    ctx.fillText(`MIN ${mn.toFixed(1)}°C`, w-4, h-4);
}

// =====================
// DRAW TRAJECTORY — auto-rescale, gradient, pulse animation
// =====================
function drawTrajectory(canvas, history) {
    // Resize canvas sesuai ukuran elemen (responsive)
    const size = canvas.offsetWidth || 200;
    if (canvas.width !== size || canvas.height !== size) {
        canvas.width  = size;
        canvas.height = size;
    }
    const ctx = canvas.getContext('2d');
    const W = size, H = size;

    // Background
    ctx.fillStyle = '#0a0a0a';
    ctx.fillRect(0, 0, W, H);

    // Grid
    ctx.strokeStyle = '#1a1a1a';
    ctx.lineWidth = 0.5;
    for (let x = 0; x <= W; x += W/4) {
        ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, H); ctx.stroke();
    }
    for (let y = 0; y <= H; y += H/4) {
        ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(W, y); ctx.stroke();
    }
    ctx.strokeStyle = '#2a2a2a';
    ctx.lineWidth = 1;
    ctx.beginPath(); ctx.moveTo(W/2, 0); ctx.lineTo(W/2, H); ctx.stroke();
    ctx.beginPath(); ctx.moveTo(0, H/2); ctx.lineTo(W, H/2); ctx.stroke();

    if (!history || history.length < 2) {
        ctx.fillStyle = '#404040';
        ctx.font = '10px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Menunggu data gerakan...', W/2, H/2 + 4);
        return;
    }

    // Auto-rescale bounding box
    let minX = Infinity, maxX = -Infinity;
    let minY = Infinity, maxY = -Infinity;
    history.forEach(pt => {
        if (pt.x < minX) minX = pt.x;
        if (pt.x > maxX) maxX = pt.x;
        if (pt.y < minY) minY = pt.y;
        if (pt.y > maxY) maxY = pt.y;
    });

    const range = Math.max(maxX - minX, maxY - minY) || 0.01;
    const pad   = range * 0.18;
    const dMinX = minX - pad, dMaxX = maxX + pad;
    const dMinY = minY - pad, dMaxY = maxY + pad;
    const dRX   = dMaxX - dMinX;
    const dRY   = dMaxY - dMinY;

    function toCanvas(x, y) {
        return {
            cx: ((x - dMinX) / dRX) * W,
            cy: H - ((y - dMinY) / dRY) * H,
        };
    }

    // Garis trajectory dengan gradient opacity (makin baru makin terang)
    for (let i = 1; i < history.length; i++) {
        const alpha = 0.25 + 0.75 * (i / history.length);
        ctx.strokeStyle = `rgba(239, 68, 68, ${alpha.toFixed(2)})`;
        ctx.lineWidth   = 1.5;
        ctx.lineJoin    = 'round';
        ctx.lineCap     = 'round';
        ctx.beginPath();
        const p0 = toCanvas(history[i-1].x, history[i-1].y);
        const p1 = toCanvas(history[i].x,   history[i].y);
        ctx.moveTo(p0.cx, p0.cy);
        ctx.lineTo(p1.cx, p1.cy);
        ctx.stroke();
    }

    // Titik START (hijau)
    const fp = toCanvas(history[0].x, history[0].y);
    ctx.fillStyle = '#22c55e';
    ctx.beginPath();
    ctx.arc(fp.cx, fp.cy, 4, 0, Math.PI*2);
    ctx.fill();
    ctx.fillStyle = '#22c55e';
    ctx.font = 'bold 8px sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('S', fp.cx, fp.cy - 7);

    // Titik AKHIR dengan efek pulse
    const last = history[history.length - 1];
    const lp   = toCanvas(last.x, last.y);
    const pulse = (Math.sin(Date.now() / 300) + 1) / 2; // 0–1 oscillating
    ctx.fillStyle = `rgba(239, 68, 68, ${(0.15 + 0.25 * pulse).toFixed(2)})`;
    ctx.beginPath();
    ctx.arc(lp.cx, lp.cy, 6 + pulse * 7, 0, Math.PI*2);
    ctx.fill();
    ctx.fillStyle = '#ef4444';
    ctx.beginPath();
    ctx.arc(lp.cx, lp.cy, 4, 0, Math.PI*2);
    ctx.fill();
    ctx.fillStyle = '#ef4444';
    ctx.font = 'bold 8px sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('E', lp.cx, lp.cy - 10);

    // Label koordinat terakhir
    ctx.fillStyle = '#525252';
    ctx.font = '8px sans-serif';
    ctx.textAlign = 'left';
    ctx.fillText(`(${last.x.toFixed(2)}, ${last.y.toFixed(2)}) m`, 4, H - 4);
}

// =====================
// TRAJECTORY ANIMATION LOOP — jalan terus saat modal terbuka
// =====================
function trajectoryAnimLoop() {
    if (currentDeviceId) {
        const tcanvas = document.getElementById('modal-trajectory');
        const hist    = trajectoryHistory[currentDeviceId] || [];
        if (tcanvas) drawTrajectory(tcanvas, hist);
    }
    requestAnimationFrame(trajectoryAnimLoop);
}

// =====================
// FORMAT ANGKA
// =====================
function fmt(val, suffix = '°') {
    if (val === undefined || val === null) return '—';
    return parseFloat(val).toFixed(2) + suffix;
}

// =====================
// STATUS HELPER
// =====================
function getStatus(device) {
    if (device.suhu_max === undefined || device.suhu_max === null) return 'nosig';
    const t = device.suhu_max;
    if (t >= 37.5) return 'confirmed';
    if (t >= 36.0) return 'probable';
    if (t >= 34.0) return 'possible';
    return 'nosig';
}

// =====================
// RENDER KARTU
// =====================
function renderDevices() {
    const grid  = document.getElementById('cards-grid');
    const empty = document.getElementById('empty-state');
    if (!grid) return;

    const list = Object.values(devices);

    if (list.length === 0) {
        grid.innerHTML = '';
        empty?.classList.remove('hidden');
        updateStats();
        return;
    }
    empty?.classList.add('hidden');

    list.forEach(device => {
        let card = document.getElementById(`card-${device.device_id}`);
        const status     = getStatus(device);
        const s          = STATUS_MAP[status];
        const isDetected = status === 'confirmed';
        const num        = device.device_id.replace('kecoa_', '').replace(/^0+/, '');

        if (!card) {
            card = document.createElement('div');
            card.id = `card-${device.device_id}`;
            card.className = `bg-neutral-900 border rounded-xl p-3 cursor-pointer transition-all hover:border-red-800 ${isDetected ? 'border-red-800 shadow-lg shadow-red-950/30' : 'border-neutral-800'}`;
            card.onclick = () => openModal(device.device_id);

            card.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-semibold text-neutral-100">Kecoa #${num}</span>
                    <span class="card-status text-xs px-2 py-0.5 rounded-full"></span>
                </div>
                <div class="flex gap-2 mb-2">
                    <div class="shrink-0 rounded-lg overflow-hidden border border-neutral-800" style="width:120px;height:120px;position:relative;">
                        <canvas class="hmap-card" data-id="${device.device_id}" style="display:block;width:120px;height:120px;"></canvas>
                        <div class="hmap-nosignal hidden absolute inset-0 flex items-center justify-center text-xs text-neutral-600 bg-neutral-950">No signal</div>
                    </div>
                    <div class="flex-1 flex flex-col gap-1.5 min-w-0">
                        <div class="grid grid-cols-3 gap-1">
                            <div class="bg-neutral-950 border border-neutral-800 rounded py-1.5 text-center">
                                <div class="text-neutral-600" style="font-size:9px;">Pitch</div>
                                <div class="card-pitch text-xs font-medium text-neutral-300" style="font-size:10px;">—</div>
                            </div>
                            <div class="bg-neutral-950 border border-neutral-800 rounded py-1.5 text-center">
                                <div class="text-neutral-600" style="font-size:9px;">Roll</div>
                                <div class="card-roll text-xs font-medium text-neutral-300" style="font-size:10px;">—</div>
                            </div>
                            <div class="bg-neutral-950 border border-neutral-800 rounded py-1.5 text-center">
                                <div class="text-neutral-600" style="font-size:9px;">Yaw</div>
                                <div class="card-yaw text-xs font-medium text-neutral-300" style="font-size:10px;">—</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-1">
                            <div class="bg-neutral-950 border border-neutral-800 rounded py-1.5 text-center">
                                <div class="text-neutral-600" style="font-size:9px;">Suhu maks</div>
                                <div class="card-suhu-max font-semibold text-red-400" style="font-size:11px;">—</div>
                            </div>
                            <div class="bg-neutral-950 border border-neutral-800 rounded py-1.5 text-center">
                                <div class="text-neutral-600" style="font-size:9px;">Suhu min</div>
                                <div class="card-suhu-min font-semibold text-blue-400" style="font-size:11px;">—</div>
                            </div>
                        </div>
                        <div class="bg-neutral-950 border border-neutral-800 rounded px-2 py-1.5 flex items-center justify-between">
                            <span class="text-neutral-600" style="font-size:9px;">Jarak</span>
                            <span class="card-distance font-medium text-neutral-300" style="font-size:10px;">—</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-1 border-t border-neutral-800">
                    <span class="card-badge text-xs px-2 py-0.5 rounded-full"></span>
                    <span class="card-ts text-xs text-neutral-600">—</span>
                </div>
            `;
            grid.appendChild(card);
        }

        // Update nilai tanpa re-render innerHTML
        card.querySelector('.card-status').textContent = device.online ? '● Online' : '○ Offline';
        card.querySelector('.card-status').className   = `card-status text-xs px-2 py-0.5 rounded-full ${device.online ? 'bg-emerald-900 text-emerald-400 border border-emerald-800' : 'bg-neutral-800 text-neutral-500 border border-neutral-700'}`;
        card.querySelector('.card-pitch').textContent   = device.pitch !== undefined ? device.pitch.toFixed(2) + '°' : '—';
        card.querySelector('.card-roll').textContent    = device.roll  !== undefined ? device.roll.toFixed(2)  + '°' : '—';
        card.querySelector('.card-yaw').textContent     = device.yaw   !== undefined ? device.yaw.toFixed(2)   + '°' : '—';
        card.querySelector('.card-suhu-max').textContent = device.suhu_max !== undefined ? device.suhu_max.toFixed(1) + '°C' : '—';
        card.querySelector('.card-suhu-min').textContent = device.suhu_min !== undefined ? device.suhu_min.toFixed(1) + '°C' : '—';
        // Jarak dari Pusher (distance_total_m) — stabil, bukan dari sensor VL53
        card.querySelector('.card-distance').textContent = (device.distance_total_m ?? 0).toFixed(1) + ' m';
        card.querySelector('.card-badge').textContent   = s.label;
        card.querySelector('.card-badge').className     = `card-badge text-xs px-2 py-0.5 rounded-full ${s.cls}`;
        card.querySelector('.card-ts').textContent      = device.timestamp ?? '—';

        const canvas   = card.querySelector('.hmap-card');
        const noSignal = card.querySelector('.hmap-nosignal');
        if (device.thermal && device.online) {
            canvas.classList.remove('hidden');
            noSignal.classList.add('hidden');
            drawHeatmap(canvas, device.thermal, 120, 120);
        } else {
            canvas.classList.add('hidden');
            noSignal.classList.remove('hidden');
        }
    });

    grid.querySelectorAll('[id^="card-"]').forEach(card => {
        const id = card.id.replace('card-', '');
        if (!devices[id]) card.remove();
    });

    updateStats();
}

// =====================
// UPDATE STAT BAR
// =====================
function updateStats() {
    const list    = Object.values(devices);
    const total   = list.length;
    const online  = list.filter(d => d.online).length;
    const deteksi = list.filter(d => getStatus(d) === 'confirmed').length;
    const suhuMax = list.length > 0 ? Math.max(...list.map(d => d.suhu_max ?? 0)) : 0;

    const el = id => document.getElementById(id);
    if (el('stat-total'))   el('stat-total').textContent   = total || '—';
    if (el('stat-online'))  el('stat-online').textContent  = online;
    if (el('stat-deteksi')) el('stat-deteksi').textContent = deteksi;
    if (el('stat-suhu'))    el('stat-suhu').textContent    = total > 0 ? suhuMax.toFixed(1) + '°C' : '—';
}

// =====================
// RENDER NOTIFIKASI
// =====================
function renderNotifications() {
    const html = notifications.length === 0
        ? '<div class="text-xs text-neutral-600 text-center py-4">Belum ada notifikasi</div>'
        : notifications.slice(0, 10).map(n => `
            <div class="bg-neutral-950 border border-neutral-800 border-l-2 border-l-red-700 rounded-lg px-3 py-2 mb-2">
                <div class="text-xs text-neutral-200 leading-relaxed">${n.message}</div>
                <div class="text-xs text-neutral-600 mt-1">${n.time}</div>
            </div>
        `).join('');

    const desktop = document.getElementById('notif-container');
    const mobile  = document.getElementById('notif-container-mobile');
    if (desktop) desktop.innerHTML = html;
    if (mobile)  mobile.innerHTML  = html;
}

// =====================
// MODAL
// =====================
let currentDeviceId = null;

window.openModal = function(deviceId) {
    const device = devices[deviceId];
    if (!device) return;
    currentDeviceId = deviceId;
    _populateModal(device);
    const modal = document.getElementById('modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
};

function updateModalIfOpen(deviceId) {
    if (currentDeviceId !== deviceId) return;
    const device = devices[deviceId];
    if (!device) return;
    _populateModal(device);
}

function _populateModal(device) {
    const num    = device.device_id.replace('kecoa_', '').replace(/^0+/, '');
    const status = getStatus(device);
    const s      = STATUS_MAP[status];

    document.getElementById('modal-title').textContent    = `Detail — Kecoa #${num}`;
    document.getElementById('modal-pitch').textContent    = fmt(device.pitch);
    document.getElementById('modal-roll').textContent     = fmt(device.roll);
    document.getElementById('modal-yaw').textContent      = fmt(device.yaw);
    document.getElementById('modal-suhu-max').textContent = device.suhu_max !== undefined ? device.suhu_max.toFixed(1) + '°C' : '—';
    document.getElementById('modal-suhu-min').textContent = device.suhu_min !== undefined ? device.suhu_min.toFixed(1) + '°C' : '—';
    document.getElementById('modal-ts').textContent       = device.timestamp ?? '—';

    const bat   = device.battery ?? 0;
    const batEl = document.getElementById('modal-battery');
    if (batEl) {
        batEl.textContent = bat + '%';
        batEl.className   = `text-sm font-semibold ${bat > 50 ? 'text-emerald-400' : bat > 20 ? 'text-amber-400' : 'text-red-400'}`;
    }
    const batBar = document.getElementById('modal-battery-bar');
    if (batBar) {
        batBar.style.width           = bat + '%';
        batBar.style.backgroundColor = bat > 50 ? '#16a34a' : bat > 20 ? '#d97706' : '#dc2626';
    }

    const sig   = device.signal_strength ?? 0;
    const sigEl = document.getElementById('modal-signal');
    if (sigEl) {
        sigEl.textContent = sig + '%';
        sigEl.className   = `text-sm font-semibold ${sig > 60 ? 'text-emerald-400' : sig > 30 ? 'text-amber-400' : 'text-red-400'}`;
    }
    const sigBar = document.getElementById('modal-signal-bar');
    if (sigBar) {
        sigBar.style.width           = sig + '%';
        sigBar.style.backgroundColor = sig > 60 ? '#16a34a' : sig > 30 ? '#d97706' : '#dc2626';
    }

    const distEl = document.getElementById('modal-distance');
    if (distEl) distEl.textContent = (device.distance_total_m ?? 0).toFixed(1) + ' m';

    const badge = document.getElementById('modal-status-badge');
    if (badge) {
        badge.className   = `inline-block text-xs px-3 py-1 rounded-full ${s.cls}`;
        badge.textContent = s.label;
    }

    // Heatmap modal — trajectory dihandle oleh animloop, tidak perlu dipanggil di sini
    requestAnimationFrame(() => {
        const canvas = document.getElementById('modal-canvas');
        if (canvas) {
            if (device.thermal) {
                drawHeatmap(canvas, device.thermal, 240, 240);
            } else {
                canvas.width = 240; canvas.height = 240;
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#0a0a0a';
                ctx.fillRect(0, 0, 240, 240);
                ctx.fillStyle = '#404040';
                ctx.font = '12px sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('Tidak ada sinyal', 120, 120);
            }
        }
    });
}

window.closeModal = function () {
    document.getElementById('modal').classList.add('hidden');
    document.getElementById('modal').classList.remove('flex');
    currentDeviceId = null;
};

document.getElementById('modal')?.addEventListener('click', function (e) {
    if (e.target === this) window.closeModal();
});

// =====================
// PUSHER LISTENER — untuk data yang tidak real-time (battery, signal, jarak tempuh, status online)
// =====================
window.Echo.channel("cyroach-channel")
    .listen(".sensor-data", (e) => {
        const data = e.data;

        if (!trajectoryHistory[data.device_id]) {
            trajectoryHistory[data.device_id] = [{ x: 0, y: 0 }];
        }
        if (data.dx !== undefined && data.dy !== undefined && (data.dx !== 0 || data.dy !== 0)) {
            const hist = trajectoryHistory[data.device_id];
            const last = hist[hist.length - 1];
            hist.push({ x: last.x + data.dx, y: last.y + data.dy });
            if (hist.length > 300) hist.shift();
        }

        // Hanya update field yang tidak datang dari ESP32 WebSocket langsung
        if (!devices[data.device_id]) {
            devices[data.device_id] = {
                device_id:        data.device_id,
                online:           true,
                suhu_max:         data.suhu_max,
                suhu_min:         data.suhu_min,
                pitch:            data.pitch,
                roll:             data.roll,
                yaw:              data.yaw,
                thermal:          data.thermal,
                battery:          data.battery ?? 0,
                signal_strength:  data.signal_strength ?? 0,
                distance_total_m: data.distance_total_m ?? 0,
                timestamp:        new Date().toLocaleTimeString('id-ID'),
            };
        } else {
            // Device sudah ada — hanya update field dari Pusher yang tidak di-override ESP32 WS
            devices[data.device_id].online           = true;
            devices[data.device_id].battery          = data.battery ?? 0;
            devices[data.device_id].signal_strength  = data.signal_strength ?? 0;
            devices[data.device_id].distance_total_m = data.distance_total_m ?? 0;
            devices[data.device_id].timestamp        = new Date().toLocaleTimeString('id-ID');
        }

        if (data.suhu_max >= 37.5) {
            const num      = data.device_id.replace('kecoa_', '').replace(/^0+/, '');
            const existing = notifications.find(n => n.device_id === data.device_id);
            if (!existing || (Date.now() - existing.ts) > 300000) {
                notifications.unshift({
                    device_id: data.device_id,
                    message:   `Kecoa #${num} menemukan korban`,
                    time:      new Date().toLocaleTimeString('id-ID'),
                    ts:        Date.now(),
                });
                renderNotifications();
            }
        }

        renderDevices();
        updateModalIfOpen(data.device_id);
    });

// =====================
// INITIAL LOAD
// =====================
fetch('/api/devices/live')
    .then(r => r.json())
    .then(data => {
        data.devices.forEach(d => {
            if (!d.latest) return;
            devices[d.device_id] = {
                device_id:        d.device_id,
                online:           d.status === 'online',
                suhu_max:         d.latest.suhu_max,
                suhu_min:         d.latest.suhu_min,
                pitch:            d.latest.pitch,
                roll:             d.latest.roll,
                yaw:              d.latest.yaw,
                thermal:          d.latest.thermal_grid,
                battery:          d.latest.battery ?? 0,
                signal_strength:  d.latest.signal_strength ?? 0,
                distance_total_m: d.latest.distance_total_m ?? 0,
                timestamp:        d.last_seen ? new Date(d.last_seen).toLocaleTimeString('id-ID') : '—',
            };
        });

        data.notifications?.forEach(n => {
            notifications.push({
                device_id: n.device_id,
                message:   n.message,
                time:      new Date(n.notified_at).toLocaleTimeString('id-ID'),
                ts:        new Date(n.notified_at).getTime(),
            });
        });

        renderDevices();
        renderNotifications();
    })
    .catch(err => console.error('Gagal load data awal:', err));

trajectoryAnimLoop();