@extends('layouts.app')

@section('title', 'About Project')
@section('page-title', 'About Project')

@section('content')
<div class="overflow-y-auto h-full">

    {{-- HERO BANNER --}}
    <div class="relative mx-6 mt-6 rounded-xl overflow-hidden" style="height:200px;background:linear-gradient(135deg,#0a0a0a 0%,#1a0a0a 50%,#0a0a0a 100%);">
        <div class="absolute inset-0 flex flex-col justify-end p-6" style="background:linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 60%);">
            <div class="text-xs font-mono px-2 py-1 rounded mb-2 inline-block w-fit" style="background-color:var(--accent);color:white;">
                MISSION CRITICAL RESEARCH
            </div>
            <h2 class="text-2xl font-display font-bold cyroach-text mb-1">CyRoach: Bio-Hybrid Robotics for Disaster Rescue</h2>
            <p class="text-xs font-mono cyroach-muted">Menciptakan jembatan antara organisme biologis dan kontrol digital untuk navigasi di medan bencana.</p>
        </div>
        {{-- Grid pattern overlay --}}
        <div class="absolute inset-0 opacity-5" style="background-image:repeating-linear-gradient(0deg,transparent,transparent 30px,#fff 30px,#fff 31px),repeating-linear-gradient(90deg,transparent,transparent 30px,#fff 30px,#fff 31px);"></div>
    </div>

    <div class="p-6 grid grid-cols-3 gap-5 max-w-6xl">

        {{-- KOLOM KIRI (2/3) --}}
        <div class="col-span-2 flex flex-col gap-5">

            {{-- PROJECT OVERVIEW --}}
            <div class="cy-card p-5">
                <div class="flex items-center gap-2 mb-4">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="cyroach-accent-text">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/>
                    </svg>
                    <span class="text-xs font-mono cyroach-accent-text uppercase tracking-widest font-semibold">Project Overview</span>
                </div>
                <p class="text-sm cyroach-text leading-relaxed mb-3">
                    <!-- Isi konten di sini -->
                    Proyek ini merupakan Tugas Akhir yang berfokus pada pengembangan sistem monitoring hibrida menggunakan kecoa (<em>Periplaneta americana</em>) sebagai platform robotika biologis.
                </p>
                <p class="text-sm cyroach-text leading-relaxed">
                    <!-- Isi konten di sini -->
                    Inti dari penelitian ini adalah integrasi sensor termal dan sistem kendali saraf nirkabel yang memungkinkan operator untuk mengarahkan serangga menuju sumber panas yang terdeteksi, mengidentifikasi korban manusia di bawah reruntuhan atau area berbahaya.
                </p>
            </div>

            {{-- FEATURE CARDS --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="cy-card p-4">
                    <div class="w-8 h-8 rounded-lg mb-3 flex items-center justify-center" style="background-color:rgba(220,38,38,0.15);border:1px solid rgba(220,38,38,0.3);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.8">
                            <circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                        </svg>
                    </div>
                    <div class="text-xs font-semibold cyroach-text mb-1">Bio-Hybrid Control</div>
                    <div class="text-xs cyroach-muted leading-relaxed">
                        <!-- Isi deskripsi fitur di sini -->
                        Neural interfacing untuk navigasi presisi melalui stimulasi antena serangga secara artifisial.
                    </div>
                </div>
                <div class="cy-card p-4">
                    <div class="w-8 h-8 rounded-lg mb-3 flex items-center justify-center" style="background-color:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.8">
                            <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </div>
                    <div class="text-xs font-semibold cyroach-text mb-1">Thermal Imaging</div>
                    <div class="text-xs cyroach-muted leading-relaxed">
                        <!-- Isi deskripsi fitur di sini -->
                        Deteksi panas tubuh real-time dengan algoritma filtering untuk membedakan manusia dari objek lain.
                    </div>
                </div>
                <div class="cy-card p-4">
                    <div class="w-8 h-8 rounded-lg mb-3 flex items-center justify-center" style="background-color:rgba(59,130,246,0.15);border:1px solid rgba(59,130,246,0.3);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.8">
                            <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                        </svg>
                    </div>
                    <div class="text-xs font-semibold cyroach-text mb-1">Trajectory Analysis</div>
                    <div class="text-xs cyroach-muted leading-relaxed">
                        <!-- Isi deskripsi fitur di sini -->
                        Pemetaan pergerakan 1:1 untuk melacak area yang telah disisir di bawah reruntuhan.
                    </div>
                </div>
            </div>

            {{-- SYSTEM PURPOSE --}}
            <div class="cy-card p-5">
                <div class="text-xs font-mono cyroach-muted uppercase tracking-widest mb-3">System Purpose</div>
                <blockquote class="text-sm font-semibold cyroach-accent-text italic leading-relaxed border-l-2 pl-4" style="border-color:var(--accent);">
                    <!-- Isi quote di sini -->
                    "Solusi biaya rendah dengan kemampuan manuver tinggi untuk tim Search and Rescue (SAR)."
                </blockquote>
            </div>

        </div>

        {{-- KOLOM KANAN (1/3) --}}
        <div class="flex flex-col gap-4">

            {{-- THERMAL ANALYTICS PANEL --}}
            <div class="cy-card p-4">
                <div class="text-xs font-mono cyroach-muted uppercase tracking-widest mb-3 flex items-center gap-1.5">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/></svg>
                    Thermal Analytics
                </div>
                <div class="rounded-lg overflow-hidden border cyroach-border mb-2" style="background:#0a0a0a;height:120px;display:flex;align-items:center;justify-content:center;">
                    <canvas id="about-thermal" width="180" height="100" style="display:block;"></canvas>
                </div>
                <div class="text-xs font-mono cyroach-muted text-center">1:1 THERMAL FEED ACCURACY</div>
            </div>

            {{-- STATS --}}
            <div class="cy-card p-4">
                <div class="text-xs font-mono cyroach-muted uppercase tracking-widest mb-3">Engineering Profile</div>
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center py-1.5 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">Platform</span>
                        <span class="text-xs font-mono cyroach-text">ESP32 + AMG8833</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">Komunikasi</span>
                        <span class="text-xs font-mono cyroach-text">2.4GHz ACTIVE</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">Sensor Grid</span>
                        <span class="text-xs font-mono cyroach-text">8×8 = 64 pixel</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">Threshold</span>
                        <span class="text-xs font-mono text-red-400">37.5°C</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5">
                        <span class="text-xs cyroach-muted">Backend</span>
                        <span class="text-xs font-mono cyroach-text">Laravel + Pusher</span>
                    </div>
                </div>
            </div>

            {{-- ID PANEL --}}
            <div class="cy-card p-3 text-center">
                <div class="text-xs font-mono cyroach-muted mb-1">Project ID</div>
                <div class="text-xs font-mono cyroach-accent-text font-semibold">TA-2024-ERX</div>
            </div>

        </div>
    </div>

    {{-- FOOTER --}}
    <div class="mx-6 mb-6 flex items-center justify-between border-t cyroach-border pt-4">
        <div class="flex items-center gap-4 text-xs font-mono cyroach-muted">
            <span>● Neural: CALIBRATED</span>
            <span>● PCB: &lt;500mg</span>
            <span>● Comm: 2.4GHz ACTIVE</span>
        </div>
        <div class="text-xs font-mono cyroach-muted">CYROACH V1.0.4-BETA // DISASTER RELIEF PROTOCOL</div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// Demo thermal heatmap di panel About
function ironColor(ratio) {
    const r = Math.max(0, Math.min(1, ratio));
    const stops = [
        [0.00,[0,0,0]],[0.20,[80,0,130]],[0.40,[150,0,100]],
        [0.60,[220,30,0]],[0.75,[255,120,0]],[0.90,[255,220,0]],[1.00,[255,255,255]],
    ];
    let lo=stops[0],hi=stops[stops.length-1];
    for(let i=0;i<stops.length-1;i++){
        if(r>=stops[i][0]&&r<=stops[i+1][0]){lo=stops[i];hi=stops[i+1];break;}
    }
    const t=(r-lo[0])/(hi[0]-lo[0]||1);
    return lo[1].map((v,i)=>Math.round(v+(hi[1][i]-v)*t));
}

// Generate demo grid — panas di tengah seperti deteksi manusia
const demoGrid = Array.from({length:8},(_,r)=>
    Array.from({length:8},(_,c)=>{
        const dx=c-3.5,dy=r-3.5;
        const d=Math.sqrt(dx*dx+dy*dy);
        return 25+Math.max(0,(3.5-d)/3.5)*15+Math.random()*1.5;
    })
);

requestAnimationFrame(()=>{
    const canvas=document.getElementById('about-thermal');
    if(!canvas)return;
    const W=canvas.width,H=canvas.height;
    canvas.style.width=W+'px';
    canvas.style.height=H+'px';
    const flat=demoGrid.flat();
    const mn=Math.min(...flat),mx=Math.max(...flat);
    const imgData=canvas.getContext('2d').createImageData(8,8);
    for(let i=0;i<64;i++){
        const[r,g,b]=ironColor((flat[i]-mn)/(mx-mn||1));
        imgData.data[i*4]=r;imgData.data[i*4+1]=g;imgData.data[i*4+2]=b;imgData.data[i*4+3]=255;
    }
    const off=document.createElement('canvas');off.width=8;off.height=8;
    off.getContext('2d').putImageData(imgData,0,0);
    const ctx=canvas.getContext('2d');
    ctx.imageSmoothingEnabled=true;ctx.imageSmoothingQuality='high';
    ctx.drawImage(off,0,0,W,H);
    ctx.fillStyle='rgba(0,0,0,0.6)';ctx.fillRect(2,2,40,12);
    ctx.fillStyle='#ef4444';ctx.font='bold 7px monospace';ctx.textAlign='left';
    ctx.fillText(`T: ${mx.toFixed(1)}°C`,4,11);
});
</script>
@endpush