import "./bootstrap";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY || "0cf0f51ad598c475f466",
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || "ap1",
    forceTLS: true,
});

// =====================
// STATE
// =====================
const devices = {};
const notifications = [];
const trajectoryHistory = {};

const STATUS_MAP = {
    confirmed: {
        label: "Confirmed Live",
        cls: "",
        style: "background-color:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(16,185,129,0.3);",
    },
    probable: {
        label: "Probable Live",
        cls: "",
        style: "background-color:rgba(245,158,11,0.15);color:#fbbf24;border:1px solid rgba(245,158,11,0.3);",
    },
    possible: {
        label: "Possible Live",
        cls: "",
        style: "background-color:rgba(249,115,22,0.15);color:#fb923c;border:1px solid rgba(249,115,22,0.3);",
    },
    nosig: {
        label: "No signal",
        cls: "",
        style: "background-color:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border);",
    },
};

// =====================
// BILINEAR UPSCALE 8x8 → 64x64
// =====================
function bilinearUpscale(src, srcSize, dstSize) {
    const dst = new Float32Array(dstSize * dstSize);
    for (let oy = 0; oy < dstSize; oy++) {
        for (let ox = 0; ox < dstSize; ox++) {
            const fx = ((ox + 0.5) * srcSize) / dstSize - 0.5;
            const fy = ((oy + 0.5) * srcSize) / dstSize - 0.5;
            let x0 = Math.floor(fx),
                y0 = Math.floor(fy);
            let x1 = x0 + 1,
                y1 = y0 + 1;
            const tx = fx - x0,
                ty = fy - y0;
            x0 = Math.max(0, Math.min(srcSize - 1, x0));
            x1 = Math.max(0, Math.min(srcSize - 1, x1));
            y0 = Math.max(0, Math.min(srcSize - 1, y0));
            y1 = Math.max(0, Math.min(srcSize - 1, y1));
            const v00 = src[y0 * srcSize + x0],
                v10 = src[y0 * srcSize + x1];
            const v01 = src[y1 * srcSize + x0],
                v11 = src[y1 * srcSize + x1];
            dst[oy * dstSize + ox] =
                v00 +
                tx * (v10 - v00) +
                ty * (v01 + tx * (v11 - v01) - (v00 + tx * (v10 - v00)));
        }
    }
    return dst;
}

// =====================
// IRON COLORMAP
// =====================
const IRON_STOPS = [0, 0.2, 0.4, 0.6, 0.75, 0.9, 1.0];
const IRON_COLORS = [
    [0, 0, 0],
    [51, 0, 128],
    [153, 0, 153],
    [255, 0, 0],
    [255, 128, 0],
    [255, 255, 0],
    [255, 255, 255],
];

function ironColormap(norm) {
    norm = Math.max(0, Math.min(1, norm));
    let i = 0;
    while (i < IRON_STOPS.length - 2 && norm > IRON_STOPS[i + 1]) i++;
    const t = (norm - IRON_STOPS[i]) / (IRON_STOPS[i + 1] - IRON_STOPS[i]);
    const c0 = IRON_COLORS[i],
        c1 = IRON_COLORS[i + 1];
    return [
        Math.round(c0[0] + t * (c1[0] - c0[0])),
        Math.round(c0[1] + t * (c1[1] - c0[1])),
        Math.round(c0[2] + t * (c1[2] - c0[2])),
    ];
}

// =====================
// DRAW HEATMAP
// =====================
const heatmapCache = {};
const thermalSmoothed = {}; // temporal smoothing per device
const THERMAL_ALPHA = 0.4;  // sama dengan Android

function drawHeatmap(canvas, grid, w, h) {
    canvas.width = w;
    canvas.height = h;
    const ctx = canvas.getContext("2d");

    const raw = new Float32Array(grid.flat());
    const key2 = JSON.stringify(canvas.id||canvas.width);
    if (!thermalSmoothed[key2]) thermalSmoothed[key2] = new Float32Array(raw);
    else for (let i=0;i<64;i++) thermalSmoothed[key2][i] = THERMAL_ALPHA*raw[i] + (1-THERMAL_ALPHA)*thermalSmoothed[key2][i];
    const flat = thermalSmoothed[key2];
    const mn = Math.min(...flat),
        mx = Math.max(...flat);
    const range = mx - mn || 1;
    const upscaled = bilinearUpscale(flat, 8, 64);

    const key = `${w}x${h}`;
    if (!heatmapCache[key]) {
        heatmapCache[key] = document.createElement("canvas");
        heatmapCache[key].width = 64;
        heatmapCache[key].height = 64;
    }
    const offscreen = heatmapCache[key];
    const offCtx = offscreen.getContext("2d");
    const imgData = offCtx.createImageData(64, 64);

    for (let i = 0; i < 64 * 64; i++) {
        const norm = (upscaled[i] - mn) / range;
        const [r, g, b] = ironColormap(norm);
        imgData.data[i * 4] = r;
        imgData.data[i * 4 + 1] = g;
        imgData.data[i * 4 + 2] = b;
        imgData.data[i * 4 + 3] = 255;
    }

    offCtx.putImageData(imgData, 0, 0);
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = "high";
    ctx.save();
    ctx.translate(w/2, h/2);
    ctx.rotate(-Math.PI / 2);
    ctx.scale(-1, 1);
    ctx.drawImage(offscreen, -h/2, -w/2, h, w);
    ctx.restore();

    ctx.fillStyle = "rgba(0,0,0,0.80)";
    ctx.fillRect(0, h - 16, w, 16);
    ctx.fillStyle = "#fff";
    ctx.font = "bold 8px sans-serif";
    ctx.textAlign = "left";
    ctx.fillText(`MAX ${mx.toFixed(1)}°C`, 4, h - 4);
    ctx.textAlign = "right";
    ctx.fillText(`MIN ${mn.toFixed(1)}°C`, w - 4, h - 4);
}

// =====================
// DRAW TRAJECTORY — auto-rescale, gradient, pulse animation
// =====================
function drawTrajectory(canvas, history) {
    const W = 480;
    const H = 480;
    canvas.width = W;
    canvas.height = H;
    const ctx = canvas.getContext("2d");

    // Background
    ctx.fillStyle = "#0a0a0a";
    ctx.fillRect(0, 0, W, H);

    // Grid
    ctx.strokeStyle = "#1a1a1a";
    ctx.lineWidth = 0.5;
    for (let x = 0; x <= W; x += W / 4) {
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, H);
        ctx.stroke();
    }
    for (let y = 0; y <= H; y += H / 4) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(W, y);
        ctx.stroke();
    }
    ctx.strokeStyle = "#2a2a2a";
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(W / 2, 0);
    ctx.lineTo(W / 2, H);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(0, H / 2);
    ctx.lineTo(W, H / 2);
    ctx.stroke();

    if (!history || history.length < 2) {
        ctx.fillStyle = "#404040";
        ctx.font = "10px sans-serif";
        ctx.textAlign = "center";
        ctx.fillText("Menunggu data gerakan...", W / 2, H / 2 + 4);
        return;
    }

    // Auto-rescale bounding box
    let minX = Infinity,
        maxX = -Infinity;
    let minY = Infinity,
        maxY = -Infinity;
    history.forEach((pt) => {
        if (pt.x < minX) minX = pt.x;
        if (pt.x > maxX) maxX = pt.x;
        if (pt.y < minY) minY = pt.y;
        if (pt.y > maxY) maxY = pt.y;
    });

    const range = Math.max(maxX - minX, maxY - minY) || 0.01;
    const pad = range * 0.18;
    const dMinX = minX - pad,
        dMaxX = maxX + pad;
    const dMinY = minY - pad,
        dMaxY = maxY + pad;
    const dRX = dMaxX - dMinX;
    const dRY = dMaxY - dMinY;

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
        ctx.lineWidth = 1.5;
        ctx.lineJoin = "round";
        ctx.lineCap = "round";
        ctx.beginPath();
        const p0 = toCanvas(history[i - 1].x, history[i - 1].y);
        const p1 = toCanvas(history[i].x, history[i].y);
        ctx.moveTo(p0.cx, p0.cy);
        ctx.lineTo(p1.cx, p1.cy);
        ctx.stroke();
    }

    // Titik START (hijau)
    const fp = toCanvas(history[0].x, history[0].y);
    ctx.fillStyle = "#22c55e";
    ctx.beginPath();
    ctx.arc(fp.cx, fp.cy, 4, 0, Math.PI * 2);
    ctx.fill();
    ctx.fillStyle = "#22c55e";
    ctx.font = "bold 8px sans-serif";
    ctx.textAlign = "center";
    ctx.fillText("S", fp.cx, fp.cy - 7);

    // Titik AKHIR dengan efek pulse
    const last = history[history.length - 1];
    const lp = toCanvas(last.x, last.y);
    const pulse = (Math.sin(Date.now() / 300) + 1) / 2; // 0–1 oscillating
    ctx.fillStyle = `rgba(239, 68, 68, ${(0.15 + 0.25 * pulse).toFixed(2)})`;
    ctx.beginPath();
    ctx.arc(lp.cx, lp.cy, 6 + pulse * 7, 0, Math.PI * 2);
    ctx.fill();
    ctx.fillStyle = "#ef4444";
    ctx.beginPath();
    ctx.arc(lp.cx, lp.cy, 4, 0, Math.PI * 2);
    ctx.fill();
    ctx.fillStyle = "#ef4444";
    ctx.font = "bold 8px sans-serif";
    ctx.textAlign = "center";
    ctx.fillText("E", lp.cx, lp.cy - 10);

    // Label koordinat terakhir
    ctx.fillStyle = "#525252";
    ctx.font = "8px sans-serif";
    ctx.textAlign = "left";
    ctx.fillText(`(${last.x.toFixed(2)}, ${last.y.toFixed(2)}) m`, 4, H - 4);
    const bSx=history[0].x, bSy=history[0].y;
    const bEx=last.x, bEy=last.y;
    const bAngle=Math.atan2(bEx-bSx, bEy-bSy)*(180/Math.PI);
    const bStr=(bAngle>=0?'+':'')+bAngle.toFixed(1)+'°';
    const bLabel='Kemiringan: '+bStr+(bAngle>0?' (kanan)':bAngle<0?' (kiri)':' (lurus)');
    ctx.fillStyle='#a3a3a3'; ctx.font='bold 8px monospace';
    ctx.fillText(bLabel, 4, 12);
}

// =====================
// TRAJECTORY ANIMATION LOOP — jalan terus saat modal terbuka
// =====================
function trajectoryAnimLoop() {
    if (currentDeviceId) {
        const tcanvas = document.getElementById("modal-trajectory");
        const hist = trajectoryHistory[currentDeviceId] || [];
        if (tcanvas) drawTrajectory(tcanvas, hist);
    }
    requestAnimationFrame(trajectoryAnimLoop);
}

// =====================
// FORMAT ANGKA
// =====================
function fmt(val, suffix = "°") {
    if (val === undefined || val === null) return "—";
    return parseFloat(val).toFixed(2) + suffix;
}

// =====================
// STATUS HELPER
// =====================
function getStatus(device) {
    if (device.suhu_max === undefined || device.suhu_max === null)
        return "nosig";
    const t = device.suhu_max;
    if (t >= 37.5) return "confirmed";
    if (t >= 36.0) return "probable";
    if (t >= 34.0) return "possible";
    return "nosig";
}

// =====================
// RENDER KARTU
// =====================
function renderDevices() {
    const grid = document.getElementById("cards-grid");
    const empty = document.getElementById("empty-state");
    if (!grid) return;

    const list = Object.values(devices);

    if (list.length === 0) {
        grid.innerHTML = "";
        empty?.classList.remove("hidden");
        updateStats();
        return;
    }
    empty?.classList.add("hidden");

    list.forEach((device) => {
        let card = document.getElementById(`card-${device.device_id}`);
        const status = getStatus(device);
        const s = STATUS_MAP[status];
        const isDetected = status === "confirmed";
        const num = device.device_id.replace("kecoa_", "").replace(/^0+/, "");

        if (!card) {
            card = document.createElement("div");
            card.id = `card-${device.device_id}`;
            card.className = `cy-card rounded-xl p-3 cursor-pointer transition-all hover:opacity-90`;
            card.style.borderColor = isDetected ? 'var(--border-accent)' : 'var(--border)';
            card.style.boxShadow = isDetected ? '0 4px 24px rgba(127,29,29,0.2)' : 'none';
            card.onclick = () => openModal(device.device_id);

            card.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-semibold cyroach-text">Kecoa #${num}</span>
                    <span class="card-status text-xs px-2 py-0.5 rounded-full"></span>
                </div>
                <div class="flex gap-2 mb-2">
                    <div class="shrink-0 rounded-lg overflow-hidden border cyroach-border" style="width:120px;height:120px;position:relative;">
                        <canvas class="hmap-card" data-id="${device.device_id}" style="display:block;width:120px;height:120px;"></canvas>
                        <div class="hmap-nosignal hidden absolute inset-0 flex items-center justify-center text-xs cyroach-muted" style="background-color:var(--bg-raised);">No signal</div>
                    </div>
                    <div class="flex-1 flex flex-col gap-1.5 min-w-0">
                        <div class="grid grid-cols-3 gap-1">
                            <div class="cy-card-raised rounded py-1.5 text-center">
                                <div class="cyroach-sub" style="font-size:9px;">Pitch</div>
                                <div class="card-pitch cyroach-text font-medium" style="font-size:10px;font-family:var(--font-mono);">—</div>
                            </div>
                            <div class="cy-card-raised rounded py-1.5 text-center">
                                <div class="cyroach-sub" style="font-size:9px;">Roll</div>
                                <div class="card-roll cyroach-text font-medium" style="font-size:10px;font-family:var(--font-mono);">—</div>
                            </div>
                            <div class="cy-card-raised rounded py-1.5 text-center">
                                <div class="cyroach-sub" style="font-size:9px;">Yaw</div>
                                <div class="card-yaw cyroach-text font-medium" style="font-size:10px;font-family:var(--font-mono);">—</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-1">
                            <div class="cy-card-raised rounded py-1.5 text-center">
                                <div class="cyroach-sub" style="font-size:9px;">Suhu maks</div>
                                <div class="card-suhu-max font-semibold text-red-400" style="font-size:11px;font-family:var(--font-mono);">—</div>
                            </div>
                            <div class="cy-card-raised rounded py-1.5 text-center">
                                <div class="cyroach-sub" style="font-size:9px;">Suhu min</div>
                                <div class="card-suhu-min font-semibold text-blue-400" style="font-size:11px;font-family:var(--font-mono);">—</div>
                            </div>
                        </div>
                        <div class="cy-card-raised rounded px-2 py-1.5 flex items-center justify-between">
                            <span class="cyroach-sub" style="font-size:9px;">Jarak</span>
                            <span class="card-distance cyroach-text font-medium" style="font-size:10px;font-family:var(--font-mono);">—</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-1 border-t cyroach-border">
                    <span class="card-badge text-xs px-2 py-0.5 rounded-full"></span>
                    <span class="card-ts text-xs cyroach-muted" style="font-family:var(--font-mono);">—</span>
                </div>
            `;
            grid.appendChild(card);
        }

        // Update nilai tanpa re-render innerHTML
        card.querySelector(".card-status").textContent = device.online
            ? "● Online"
            : "○ Offline";
        card.querySelector(".card-status").className = "card-status text-xs px-2 py-0.5 rounded-full";
        card.querySelector(".card-status").style.cssText = device.online
            ? "background-color:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(16,185,129,0.3);"
            : "background-color:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border);";
        card.querySelector(".card-pitch").textContent =
            device.pitch !== undefined ? device.pitch.toFixed(1) + "°" : "—";
        card.querySelector(".card-roll").textContent =
            device.roll !== undefined ? device.roll.toFixed(1) + "°" : "—";
        card.querySelector(".card-yaw").textContent =
            device.yaw !== undefined ? device.yaw.toFixed(1) + "°" : "—";
        card.querySelector(".card-suhu-max").textContent =
            device.suhu_max !== undefined
                ? device.suhu_max.toFixed(1) + "°C"
                : "—";
        card.querySelector(".card-suhu-min").textContent =
            device.suhu_min !== undefined
                ? device.suhu_min.toFixed(1) + "°C"
                : "—";
        // Jarak dari Pusher (distance_total_m) — stabil, bukan dari sensor VL53
        card.querySelector(".card-distance").textContent =
            (device.distance_total_m ?? 0).toFixed(1) + " m";
        card.querySelector(".card-badge").textContent = s.label;
        card.querySelector(".card-badge").className = "card-badge text-xs px-2 py-0.5 rounded-full";
        card.querySelector(".card-badge").style.cssText = s.style;
        card.querySelector(".card-ts").textContent = device.timestamp ?? "—";

        const canvas = card.querySelector(".hmap-card");
        const noSignal = card.querySelector(".hmap-nosignal");
        if (device.thermal && device.online) {
            canvas.classList.remove("hidden");
            noSignal.classList.add("hidden");
            drawHeatmap(canvas, device.thermal, 120, 120);
        } else {
            canvas.classList.add("hidden");
            noSignal.classList.remove("hidden");
        }
    });

    grid.querySelectorAll('[id^="card-"]').forEach((card) => {
        const id = card.id.replace("card-", "");
        if (!devices[id]) card.remove();
    });

    updateStats();
}

// =====================
// UPDATE STAT BAR
// =====================
function updateStats() {
    const list = Object.values(devices);
    const total = list.length;
    const online = list.filter((d) => d.online).length;
    const deteksi = list.filter((d) => getStatus(d) === "confirmed").length;
    const suhuMax =
        list.length > 0 ? Math.max(...list.map((d) => d.suhu_max ?? 0)) : 0;

    const el = (id) => document.getElementById(id);
    if (el("stat-total")) el("stat-total").textContent = total || "—";
    if (el("stat-online")) el("stat-online").textContent = online;
    if (el("stat-deteksi")) el("stat-deteksi").textContent = deteksi;
    if (el("stat-suhu"))
        el("stat-suhu").textContent =
            total > 0 ? suhuMax.toFixed(1) + "°C" : "—";
}

// =====================
// RENDER NOTIFIKASI
// =====================
function renderNotifications() {
    const html =
        notifications.length === 0
            ? '<div class="text-xs text-neutral-600 text-center py-4">Belum ada notifikasi</div>'
            : notifications
                  .slice(0, 10)
                  .map(
                      (n) => `
            <div class="cy-card-raised rounded-lg px-3 py-2 mb-2" style="border-left:2px solid var(--accent);">
                <div class="text-xs cyroach-text leading-relaxed">${n.message}</div>
                <div class="text-xs cyroach-muted mt-1" style="font-family:var(--font-mono);">${n.time}</div>
            </div>
        `,
                  )
                  .join("");

    const desktop = document.getElementById("notif-container");
    const mobile = document.getElementById("notif-container-mobile");
    if (desktop) desktop.innerHTML = html;
    if (mobile) mobile.innerHTML = html;
}

// =====================
// MODAL
// =====================
let currentDeviceId = null;

window.openModal = function (deviceId) {
    const device = devices[deviceId];
    if (!device) return;
    currentDeviceId = deviceId;
    _populateModal(device);
    const modal = document.getElementById("modal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
};

function updateModalIfOpen(deviceId) {
    if (currentDeviceId !== deviceId) return;
    const device = devices[deviceId];
    if (!device) return;
    _populateModal(device);
}

function _populateModal(device) {
    const num = device.device_id.replace("kecoa_", "").replace(/^0+/, "");
    const status = getStatus(device);
    const s = STATUS_MAP[status];

    document.getElementById("modal-title").textContent =
        `Detail — Kecoa #${num}`;
    const fmtShort = v => (v === undefined || v === null) ? "—" : parseFloat(v).toFixed(1) + "°";
    document.getElementById("modal-pitch").textContent = fmtShort(device.pitch);
    document.getElementById("modal-roll").textContent  = fmtShort(device.roll);
    document.getElementById("modal-yaw").textContent   = fmtShort(device.yaw);
    document.getElementById("modal-suhu-max").textContent =
        device.suhu_max !== undefined ? device.suhu_max.toFixed(1) + "°C" : "—";
    document.getElementById("modal-suhu-min").textContent =
        device.suhu_min !== undefined ? device.suhu_min.toFixed(1) + "°C" : "—";
    document.getElementById("modal-ts").textContent = device.timestamp ?? "—";

    const deteksiEl = document.getElementById("modal-deteksi-status");
    if (deteksiEl) {
        if (status === "confirmed") {
            deteksiEl.textContent = "Korban Terdeteksi";
            deteksiEl.className = "text-xs font-mono cyroach-accent-text";
        } else {
            deteksiEl.textContent = "Tidak ada deteksi";
            deteksiEl.className = "text-xs font-mono cyroach-muted";
        }
    }

    const dot = document.getElementById("modal-status-dot");
    if (dot) {
        dot.className = `w-2 h-2 rounded-full ${device.online ? "bg-emerald-400" : "bg-neutral-600"}`;
    }

    const bat = device.battery ?? 0;
    const batEl = document.getElementById("modal-battery");
    if (batEl) {
        batEl.textContent = bat + "%";
        batEl.className = `text-sm font-semibold ${bat > 50 ? "text-emerald-400" : bat > 20 ? "text-amber-400" : "text-red-400"}`;
    }
    const batBar = document.getElementById("modal-battery-bar");
    if (batBar) {
        batBar.style.width = bat + "%";
        batBar.style.backgroundColor =
            bat > 50 ? "#16a34a" : bat > 20 ? "#d97706" : "#dc2626";
    }

    const sig = device.signal_strength ?? 0;
    const sigEl = document.getElementById("modal-signal");
    if (sigEl) {
        sigEl.textContent = sig + "%";
        sigEl.className = `text-sm font-semibold ${sig > 60 ? "text-emerald-400" : sig > 30 ? "text-amber-400" : "text-red-400"}`;
    }
    const sigBar = document.getElementById("modal-signal-bar");
    if (sigBar) {
        sigBar.style.width = sig + "%";
        sigBar.style.backgroundColor =
            sig > 60 ? "#16a34a" : sig > 30 ? "#d97706" : "#dc2626";
    }

    const distEl = document.getElementById("modal-distance");
    if (distEl)
        distEl.textContent = (device.distance_total_m ?? 0).toFixed(1) + " m";

    const badge = document.getElementById("modal-status-badge");
    if (badge) {
        badge.className = `inline-block text-xs px-3 py-1 rounded-full ${s.cls}`;
        badge.textContent = s.label;
    }

    // Heatmap modal — trajectory dihandle oleh animloop, tidak perlu dipanggil di sini
    requestAnimationFrame(() => {
        const canvas = document.getElementById("modal-canvas");
        if (canvas) {
            if (device.thermal) {
                drawHeatmap(canvas, device.thermal, 480, 480);
            } else {
                canvas.width = 480;
                canvas.height = 480;
                const ctx = canvas.getContext("2d");
                ctx.fillStyle = "#0a0a0a";
                ctx.fillRect(0, 0, 280, 280);
                ctx.fillStyle = "#404040";
                ctx.font = "12px sans-serif";
                ctx.textAlign = "center";
                ctx.fillText("Tidak ada sinyal", 240, 240);
            }
        }
    });
}

window.closeModal = function () {
    document.getElementById("modal").classList.add("hidden");
    document.getElementById("modal").classList.remove("flex");
    currentDeviceId = null;
};

document.getElementById("modal")?.addEventListener("click", function (e) {
    if (e.target === this) window.closeModal();
});

// =====================
// PUSHER LISTENER — untuk data yang tidak real-time (battery, signal, jarak tempuh, status online)
// =====================
window.Echo.channel("cyroach-channel").listen(".sensor-data", (e) => {
    if (!missionActive) return;
    const data = e.data;

    if (!trajectoryHistory[data.device_id]) {
        trajectoryHistory[data.device_id] = [{ x: 0, y: 0 }];
    }
    if (
        data.dx !== undefined &&
        data.dy !== undefined &&
        (data.dx !== 0 || data.dy !== 0)
    ) {
        const hist = trajectoryHistory[data.device_id];
        const last = hist[hist.length - 1];
        hist.push({ x: last.x + data.dx, y: last.y + data.dy });
        if (hist.length > 300) hist.shift();
    }

    // Hanya update field yang tidak datang dari ESP32 WebSocket langsung
    if (!devices[data.device_id]) {
        devices[data.device_id] = {
            device_id: data.device_id,
            online: true,
            suhu_max: data.suhu_max,
            suhu_min: data.suhu_min,
            pitch: data.pitch,
            roll: data.roll,
            yaw: data.yaw,
            thermal: data.thermal,
            battery: data.battery ?? 0,
            signal_strength: data.signal_strength ?? 0,
            distance_total_m: data.distance_total_m ?? 0,
            timestamp: new Date().toLocaleTimeString("id-ID"),
        };
    } else {
        devices[data.device_id].online           = true;
        devices[data.device_id].battery          = data.battery ?? 0;
        devices[data.device_id].signal_strength  = data.signal_strength ?? 0;
        devices[data.device_id].distance_total_m = data.distance_total_m ?? 0;
        devices[data.device_id].timestamp        = new Date().toLocaleTimeString("id-ID");
        devices[data.device_id].thermal          = data.thermal;
        devices[data.device_id].suhu_max         = data.suhu_max;
        devices[data.device_id].suhu_min         = data.suhu_min;
        devices[data.device_id].pitch            = data.pitch;
        devices[data.device_id].roll             = data.roll;
        devices[data.device_id].yaw              = data.yaw;
    }

    if (data.suhu_max >= 37.5) {
        const num = data.device_id.replace("kecoa_", "").replace(/^0+/, "");
        const existing = notifications.find(
            (n) => n.device_id === data.device_id,
        );
        if (!existing || Date.now() - existing.ts > 300000) {
            notifications.unshift({
                device_id: data.device_id,
                message: `Kecoa #${num} menemukan korban`,
                time: new Date().toLocaleTimeString("id-ID"),
                ts: Date.now(),
            });
            renderNotifications();
        }
    }

    renderDevices();
    updateModalIfOpen(data.device_id);

// Listener mission-ended
window.Echo.channel("cyroach-channel").listen(".mission-ended", () => {
    missionActive = false;
    Object.keys(devices).forEach(id => { devices[id].online = false; });
    renderDevices();
    const grid = document.getElementById("cards-grid");
    const empty = document.getElementById("empty-state");
    if (empty) {
        empty.classList.remove("hidden");
        empty.innerHTML = `<div class="text-center py-12"><div class="text-neutral-500 text-sm mb-1">Misi telah selesai</div></div>`;
    }
});
});

// =====================
// STATE misi aktif
// =====================
let missionActive = false;

// =====================
// INITIAL LOAD — cek status misi dulu
// =====================
fetch('/api/mission-status')
    .then(r => r.json())
    .then(status => {
        missionActive = status.active;

        if (!missionActive) {
            // Tidak ada misi berlangsung
            const grid  = document.getElementById('cards-grid');
            const empty = document.getElementById('empty-state');
            if (grid)  grid.innerHTML = '';
            if (empty) {
                empty.classList.remove('hidden');
                empty.innerHTML = `
                    <div class="text-center py-12">
                        <div class="text-neutral-500 text-sm mb-1">Tidak ada misi yang sedang berlangsung</div>
                        <div class="text-neutral-700 text-xs">Misi akan dimulai otomatis saat kecoa mulai mengirim data</div>
                    </div>
                `;
            }
            renderNotifications();
            return; // ← PENTING: stop di sini, jangan lanjut ke bawah
        }

        // Ada misi berlangsung — load data normal
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
            });
    })
    .catch(err => console.error('Gagal load data awal:', err));

trajectoryAnimLoop();
