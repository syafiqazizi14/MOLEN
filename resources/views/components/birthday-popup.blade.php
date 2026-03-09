{{-- Birthday Popup Component --}}
<div id="birthdayModal" class="bday-root is-hidden">

    {{-- Backdrop --}}
    <div class="bday-backdrop" onclick="closeBirthdayModal()"></div>

    {{-- Confetti --}}
    <div class="bday-confetti" id="bdayConfetti"></div>

    {{-- Floating emojis --}}
    <div class="bday-floats" id="bdayFloats"></div>

    {{-- === PHASE 1: Gift Box === --}}
    <div class="bday-gift-stage" id="bdayGiftStage">
        <div class="bday-gift" id="bdayGift">
            <div class="gift-lid" id="giftLid">
                <div class="gift-bow">
                    <div class="bow-loop bow-l"></div>
                    <div class="bow-loop bow-r"></div>
                    <div class="bow-knot"></div>
                </div>
                <div class="lid-ribbon-v"></div>
            </div>
            <div class="gift-body">
                <div class="body-ribbon-v"></div>
                <div class="body-ribbon-h"></div>
                <div class="body-shine"></div>
            </div>
        </div>
        <div class="gift-glow" id="giftGlow"></div>
        <p class="gift-label" id="giftLabel">🎁</p>
    </div>

    {{-- === PHASE 2: Modal Panel === --}}
    <div class="bday-panel" id="bdayPanel" role="dialog" aria-modal="true" aria-labelledby="bdayTitle">

        <div class="bday-header">
            <div class="bday-header-deco" id="bdayHeaderDeco">
                <span class="hd-balloon hd-balloon-1">🎈</span>
                <span class="hd-balloon hd-balloon-2">🎈</span>
            </div>
            <div class="bday-header-center">
                <div class="bday-header-icon">🎂</div>
                <h2 id="bdayTitle" class="bday-title">Selamat Ulang Tahun!</h2>
                <p class="bday-subtitle">Asyik..hari ini dapat nasi kotak sultan!! 🫢</p>
            </div>
        </div>

        <div class="bday-list" id="birthdayList"></div>

        <div class="bday-footer">
            <label class="bday-dismiss-label">
                <input type="checkbox" id="birthdayDismissCheck" class="bday-dismiss-check">
                <span>Jangan tampilkan lagi hari ini</span>
            </label>
            <button type="button" onclick="closeBirthdayModal()" class="bday-close-btn">
                <span class="btn-icon">✓</span> Tutup
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    function rnd(min, max) { return Math.random() * (max - min) + min; }

    function generateHeaderStars() {
        const container = document.getElementById('bdayHeaderDeco');
        if (!container) return;
        container.querySelectorAll('.hd-star-gen').forEach(el => el.remove());
        const emojis = ['✨','⭐','🌟','✨','⭐','✨','🌟'];
        // Grid: 5 cols x 4 rows = 20 cells, jitter kecil agar tidak kaku
        const cols = 5, rows = 4;
        let idx = 0;
        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < cols; c++) {
                const el = document.createElement('span');
                el.className = 'hd-star hd-star-gen';
                el.textContent = emojis[idx % emojis.length];
                // Posisi dasar per cell + jitter kecil agar tidak kaku
                const baseLeft = (c / (cols - 1)) * 92 + 2;
                const baseTop  = (r / (rows - 1)) * 80 + 5;
                const jitterL  = rnd(-6, 6);
                const jitterT  = rnd(-6, 6);
                const size = rnd(0.55, 1.0);
                el.style.cssText = `
                    left:${Math.min(96, Math.max(2, baseLeft + jitterL))}%;
                    top:${Math.min(90, Math.max(4, baseTop + jitterT))}%;
                    font-size:${size}rem;
                    animation-delay:${rnd(0, 2.5)}s;
                    animation-duration:${rnd(1.4, 2.8)}s;
                `;
                container.appendChild(el);
                idx++;
            }
        }
    }

    function generateConfetti() {
        const container = document.getElementById('bdayConfetti');
        if (!container || container.childElementCount > 0) return;
        const colors = ['#005596','#8dc63f','#f7941d','#e74c3c','#9b59b6','#f1c40f','#1abc9c','#e91e63'];
        const shapes = ['square','circle','triangle'];
        for (let i = 0; i < 70; i++) {
            const el = document.createElement('div');
            const shape = shapes[Math.floor(Math.random() * shapes.length)];
            const color = colors[Math.floor(Math.random() * colors.length)];
            const size  = rnd(6, 14);
            el.className = `bday-piece bday-piece-${shape}`;
            el.style.cssText = `left:${rnd(3,97)}%;width:${size}px;height:${size}px;background:${color};animation-delay:${rnd(0,.9)}s;animation-duration:${rnd(1.5,2.8)}s;`;
            if (shape === 'triangle') { el.style.background = 'transparent'; el.style.borderBottomColor = color; }
            container.appendChild(el);
        }
    }

    function generateFloats() {
        const container = document.getElementById('bdayFloats');
        if (!container || container.childElementCount > 0) return;
        const emojis = ['🎈','🎊','⭐','🌟','✨','🎉','🎀','🎆'];
        for (let i = 0; i < 14; i++) {
            const el = document.createElement('span');
            el.className = 'bday-float';
            el.textContent = emojis[Math.floor(Math.random() * emojis.length)];
            el.style.cssText = `left:${rnd(2,96)}%;font-size:${rnd(16,30)}px;animation-delay:${rnd(.1,1.6)}s;animation-duration:${rnd(2.8,5)}s;`;
            container.appendChild(el);
        }
    }

    function runGiftAnimation(htmlContent) {
        const modal  = document.getElementById('birthdayModal');
        const stage  = document.getElementById('bdayGiftStage');
        const gift   = document.getElementById('bdayGift');
        const lid    = document.getElementById('giftLid');
        const glow   = document.getElementById('giftGlow');
        const label  = document.getElementById('giftLabel');
        const panel  = document.getElementById('bdayPanel');

        document.getElementById('birthdayList').innerHTML = htmlContent;
        modal.classList.remove('is-hidden');
        modal.classList.add('is-visible');

        setTimeout(() => gift.classList.add('gift-drop-in'),    120);
        setTimeout(() => gift.classList.add('gift-shake'),      750);
        setTimeout(() => lid.classList.add('lid-pop'),         1180);
        setTimeout(() => glow.classList.add('glow-burst'),     1260);
        setTimeout(() => label.classList.add('label-bounce'),  1340);
        setTimeout(() => {
            generateConfetti();
            generateFloats();
            generateHeaderStars();
            stage.classList.add('stage-out');
            panel.classList.add('panel-in');
            document.getElementById('bdayConfetti').classList.add('confetti-fire');
            document.getElementById('bdayFloats').classList.add('floats-fire');
        }, 1580);
        setTimeout(() => { stage.style.display = 'none'; }, 2250);
    }

    /* ─── Main logic ─── */
    document.addEventListener('DOMContentLoaded', function () {
        const birthdays       = @json($globalBirthdays ?? []);
        const currentUserId   = '{{ auth()->id() }}';
        const popupSessionKey = 'birthday_popup_shown_in_tab_{{ session()->getId() }}';
        const currentPath     = window.location.pathname;
        const isDashboardPage = currentPath === '/dashboard';
        let isInternalNavigation = false;

        const today      = new Date().toISOString().slice(0, 10);
        const dismissKey = `birthday_dismissed_${currentUserId}_${today}`;

        function buildList() {
            const accents = ['bday-item-a','bday-item-b','bday-item-c'];
            const quotes  = ['bday-q-a','bday-q-b','bday-q-c'];
            let html = '';
            birthdays.forEach((user, i) => {
                const name = (user.name || '').trim();
                const panggilan = (user.panggilan || name).trim();
                html += `
                <div class="bday-user-item ${accents[i % 3]}">
                    <div class="bday-user-avatar">${name.charAt(0).toUpperCase()}</div>
                    <div class="bday-user-info">
                        <p class="bday-user-name">${name}</p>
                        <p class="bday-user-msg ${quotes[i % 3]}">Barakallahu fi umrik, ${panggilan}... 🎉🎉 Semoga selalu diberikan karunia berupa kesehatan, kebahagiaan, kecukupan, kemudahan, dan kesuksesan di dunia serta akhirat. Aamiin ya Rabbal alamin 🤲🏻🤲🏻</p>
                    </div>
                </div>`;
            });
            return html;
        }

        function showBirthdayPopupIfAllowed() {
            const alreadyShown = sessionStorage.getItem(popupSessionKey) === '1';
            const dismissed    = localStorage.getItem(dismissKey) === '1';
            if (birthdays.length === 0 || alreadyShown || isDashboardPage || dismissed) return;
            runGiftAnimation(buildList());
            sessionStorage.setItem(popupSessionKey, '1');
        }

        showBirthdayPopupIfAllowed();

        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[href]');
            if (!link) return;
            const href = link.getAttribute('href'), target = link.getAttribute('target');
            if (!href || href.startsWith('#') || target === '_blank') return;
            try {
                const url = new URL(link.href, window.location.origin);
                if (url.origin === window.location.origin) isInternalNavigation = true;
            } catch (_) {}
        }, true);

        document.addEventListener('submit', () => { isInternalNavigation = true; }, true);

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') {
                if (!isInternalNavigation) sessionStorage.removeItem(popupSessionKey);
                isInternalNavigation = false;
                return;
            }
            if (document.visibilityState === 'visible') showBirthdayPopupIfAllowed();
        });
    });

    /* ─── Close ─── */
    window.closeBirthdayModal = function () {
        const modal = document.getElementById('birthdayModal');
        const check = document.getElementById('birthdayDismissCheck');
        const panel = document.getElementById('bdayPanel');

        if (check && check.checked) {
            const today = new Date().toISOString().slice(0, 10);
            const uid   = '{{ auth()->id() }}';
            localStorage.setItem(`birthday_dismissed_${uid}_${today}`, '1');
        }

        panel.classList.add('panel-out');
        setTimeout(() => {
            modal.classList.add('is-hidden');
            modal.classList.remove('is-visible');
            /* Reset for next trigger */
            panel.classList.remove('panel-in','panel-out');
            const resetIds = { bdayGiftStage:'stage-out', bdayGift:'gift-drop-in gift-shake',
                giftLid:'lid-pop', giftGlow:'glow-burst', giftLabel:'label-bounce',
                bdayConfetti:'confetti-fire', bdayFloats:'floats-fire' };
            Object.entries(resetIds).forEach(([id, cls]) => {
                const el = document.getElementById(id);
                if (!el) return;
                cls.split(' ').forEach(c => el.classList.remove(c));
            });
            const stage = document.getElementById('bdayGiftStage');
            if (stage) stage.style.display = '';
            document.getElementById('bdayConfetti').innerHTML = '';
            document.getElementById('bdayFloats').innerHTML = '';
            document.getElementById('bdayHeaderDeco').querySelectorAll('.hd-star-gen').forEach(el => el.remove());
        }, 350);
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') window.closeBirthdayModal();
    });
})();
</script>

<style>
/* ═══════════════════════ ROOT ═══════════════════════ */
:root{
    --bd-blue:#005596; --bd-blue2:#0070c0;
    --bd-green:#8dc63f; --bd-orange:#f7941d;
}
.bday-root{
    position:fixed;inset:0;z-index:9999;
    align-items:center;justify-content:center;padding:1rem;
}
.bday-root.is-hidden{display:none;}
.bday-root.is-visible{display:flex;}

/* ═══════════════════════ BACKDROP ═══════════════════ */
.bday-backdrop{
    position:absolute;inset:0;
    background:rgba(5,15,35,.72);
    backdrop-filter:blur(7px);
    animation:bdBkFade .4s ease forwards;
}
@keyframes bdBkFade{from{opacity:0}to{opacity:1}}

/* ═══════════════════════ CONFETTI ════════════════════ */
.bday-confetti{position:absolute;inset:0;pointer-events:none;overflow:hidden;}
.bday-piece{position:absolute;top:-18px;opacity:0;border-radius:2px;}
.bday-piece-circle{border-radius:50%;}
.bday-piece-triangle{
    width:0!important;height:0!important;background:transparent!important;
    border-left:6px solid transparent;border-right:6px solid transparent;
    border-bottom:11px solid #f1c40f;
}
.confetti-fire .bday-piece{animation:bdCFall linear forwards;}
@keyframes bdCFall{
    0%{top:-18px;opacity:1;transform:rotate(0) translateX(0);}
    80%{opacity:1;}
    100%{top:108vh;opacity:0;transform:rotate(700deg) translateX(50px);}
}

/* ═══════════════════════ FLOATS ══════════════════════ */
.bday-floats{position:absolute;inset:0;pointer-events:none;overflow:hidden;}
.bday-float{position:absolute;bottom:-60px;opacity:0;user-select:none;}
.floats-fire .bday-float{animation:bdFloat ease-out forwards;}
@keyframes bdFloat{
    0%{bottom:-60px;opacity:0;transform:scale(.4) rotate(-10deg);}
    20%{opacity:1;}
    80%{opacity:.6;}
    100%{bottom:110%;opacity:0;transform:scale(1.1) rotate(18deg) translateX(25px);}
}

/* ═══════════════════════ GIFT STAGE ═════════════════ */
.bday-gift-stage{
    position:relative;z-index:10;
    display:flex;flex-direction:column;align-items:center;gap:14px;
}
.stage-out{animation:bdStageOut .55s ease forwards;}
@keyframes bdStageOut{to{opacity:0;transform:scale(.45) translateY(-50px);}}

/* Gift wrapper */
.bday-gift{position:relative;opacity:0;transform:translateY(-130px) scale(.5);}
.gift-drop-in{animation:bdGiftDrop .55s cubic-bezier(.34,1.56,.64,1) .12s forwards;}
@keyframes bdGiftDrop{to{opacity:1;transform:translateY(0) scale(1);}}
.gift-shake{animation:bdGiftShake .52s ease forwards;}
@keyframes bdGiftShake{
    0%{transform:rotate(0);}15%{transform:rotate(-9deg);}
    30%{transform:rotate(9deg);}45%{transform:rotate(-5deg);}
    60%{transform:rotate(5deg);}75%{transform:rotate(-2deg);}100%{transform:rotate(0);}
}

/* Lid */
.gift-lid{
    position:relative;width:130px;height:38px;left:-7px;
    background:linear-gradient(140deg,#e63946 0%,#c1121f 100%);
    border-radius:8px 8px 4px 4px;
    display:flex;align-items:center;justify-content:center;
    box-shadow:0 4px 16px rgba(200,18,30,.4);
    transform-origin:center bottom;
}
.lid-pop{animation:bdLidPop .52s cubic-bezier(.47,1.64,.41,.8) forwards;}
@keyframes bdLidPop{
    0%{transform:translateY(0) rotate(0) scale(1);opacity:1;}
    55%{transform:translateY(-90px) rotate(-22deg) scale(1.12);opacity:1;}
    100%{transform:translateY(-160px) rotate(-35deg) scale(.4);opacity:0;}
}
.lid-ribbon-v{width:14px;height:100%;background:rgba(255,255,255,.35);border-radius:3px;}

/* Bow */
.gift-bow{position:absolute;top:-28px;left:50%;transform:translateX(-50%);display:flex;align-items:flex-end;gap:2px;}
.bow-loop{width:24px;height:24px;border-radius:50% 50% 0 50%;background:linear-gradient(135deg,#ffb3b3,#e63946);box-shadow:0 2px 7px rgba(230,57,70,.45);}
.bow-r{transform:scaleX(-1);}
.bow-knot{position:absolute;bottom:-3px;left:50%;transform:translateX(-50%);width:13px;height:13px;background:#c1121f;border-radius:50%;box-shadow:0 2px 6px rgba(193,18,31,.4);z-index:1;}

/* Body */
.gift-body{
    position:relative;width:116px;height:106px;
    background:linear-gradient(160deg,#e63946 0%,#ad1625 100%);
    border-radius:4px 4px 14px 14px;overflow:hidden;
    box-shadow:0 14px 32px rgba(173,22,37,.42),inset 0 1px 0 rgba(255,255,255,.14);
}
.body-ribbon-v{position:absolute;left:50%;top:0;bottom:0;width:14px;background:rgba(255,255,255,.32);transform:translateX(-50%);}
.body-ribbon-h{position:absolute;top:28%;left:0;right:0;height:14px;background:rgba(255,255,255,.32);}
.body-shine{position:absolute;top:8px;left:12px;width:26px;height:42px;background:rgba(255,255,255,.11);border-radius:50%;transform:rotate(-20deg);}

/* Glow */
.gift-glow{position:absolute;inset:0;pointer-events:none;border-radius:50%;
    background:radial-gradient(circle,rgba(241,196,15,.75) 0%,rgba(247,148,29,.3) 40%,transparent 70%);
    opacity:0;transform:scale(.3);}
.glow-burst{animation:bdGlow .55s ease forwards;}
@keyframes bdGlow{0%{opacity:0;transform:scale(.3);}50%{opacity:1;transform:scale(2.8);}100%{opacity:0;transform:scale(4.2);}}

/* Gift label */
.gift-label{font-size:2.1rem;margin:0;opacity:0;transition:opacity .2s;}
.label-bounce{opacity:1!important;animation:bdLabelBounce .55s cubic-bezier(.34,1.56,.64,1) forwards;}
@keyframes bdLabelBounce{0%{transform:scale(0);}100%{transform:scale(1);}}

/* ═══════════════════════ PANEL ══════════════════════ */
.bday-panel{
    position:absolute;z-index:10;
    width:min(560px,calc(100vw - 2rem));
    background:#fff;border-radius:20px;overflow:hidden;
    box-shadow:0 32px 64px rgba(0,20,50,.28),0 0 0 1px rgba(0,85,150,.1);
    opacity:0;transform:translateY(70px) scale(.88);pointer-events:none;
}
.panel-in{pointer-events:auto;animation:bdPanelIn .55s cubic-bezier(.34,1.28,.64,1) forwards;}
@keyframes bdPanelIn{to{opacity:1;transform:translateY(0) scale(1);}}
.panel-out{animation:bdPanelOut .3s ease forwards;}
@keyframes bdPanelOut{to{opacity:0;transform:translateY(30px) scale(.94);}}

/* ─ Header ─ */
.bday-header{
    position:relative;
    background:linear-gradient(135deg,#003d6e 0%,#005596 55%,#0070c0 100%);
    padding:1.35rem 1.4rem 1.2rem;overflow:hidden;text-align:center;
}
.bday-header::before{content:'';position:absolute;top:-45px;right:-45px;width:170px;height:170px;background:rgba(255,255,255,.06);border-radius:50%;}
.bday-header::after{content:'';position:absolute;bottom:-55px;left:-30px;width:140px;height:140px;background:rgba(255,255,255,.05);border-radius:50%;}
.bday-header-deco{position:absolute;inset:0;pointer-events:none;overflow:hidden;}
.hd-balloon{position:absolute;font-size:1.3rem;animation:bdBalloonSway 3s ease-in-out infinite alternate;}
.hd-balloon-1{top:8px;left:10px;animation-delay:.3s;}
.hd-balloon-2{top:8px;right:10px;animation-delay:.9s;}
@keyframes bdBalloonSway{from{transform:rotate(-9deg);}to{transform:rotate(9deg);}}
.hd-star{position:absolute;animation:bdStarPulse 1.9s ease-in-out infinite;pointer-events:none;}
@keyframes bdStarPulse{0%,100%{transform:scale(1) rotate(0);opacity:.9;}50%{transform:scale(1.5) rotate(22deg);opacity:.4;}}
.bday-header-center{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;gap:.2rem;}
.bday-header-icon{font-size:2.5rem;line-height:1;margin-bottom:.25rem;display:inline-block;animation:bdCakeWobble 2.2s ease-in-out infinite;}
@keyframes bdCakeWobble{0%,100%{transform:rotate(0) scale(1);}25%{transform:rotate(-5deg) scale(1.06);}75%{transform:rotate(5deg) scale(1.06);}}
.bday-title{margin:0;color:#fff;font-size:clamp(1.8rem,3.5vw,2.2rem);font-weight:800;letter-spacing:-.02em;text-shadow:0 2px 8px rgba(0,0,0,.28);}
.bday-subtitle{margin:.3rem 0 0;color:rgba(255,255,255,.85);font-size:1rem;font-weight:600;line-height:1.4;}

/* ─ List ─ */
.bday-list{padding:1rem 1rem .6rem;max-height:min(46vh,340px);overflow-y:auto;background:#fff;}
.bday-list::-webkit-scrollbar{width:5px;}
.bday-list::-webkit-scrollbar-track{background:#f1f5f9;border-radius:10px;}
.bday-list::-webkit-scrollbar-thumb{background:#c4d4e8;border-radius:10px;}

.bday-user-item{
    display:flex;align-items:center;gap:12px;margin-bottom:10px;
    padding:12px 14px;border-radius:12px;border:1px solid #e8edf5;background:#fafcff;
    transition:transform .18s ease,box-shadow .18s ease;
}
.bday-user-item:last-child{margin-bottom:0;}
.bday-user-item:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,60,120,.08);}
.bday-item-a{border-left:5px solid var(--bd-blue);}
.bday-item-b{border-left:5px solid var(--bd-green);}
.bday-item-c{border-left:5px solid var(--bd-orange);}

.bday-user-avatar{
    width:42px;height:42px;flex-shrink:0;border-radius:50%;
    background:linear-gradient(135deg,var(--bd-blue),var(--bd-blue2));
    color:#fff;font-size:1.1rem;font-weight:800;
    display:flex;align-items:center;justify-content:center;
    box-shadow:0 4px 10px rgba(0,85,150,.25);
}
.bday-item-b .bday-user-avatar{background:linear-gradient(135deg,#6aa520,#8dc63f);box-shadow:0 4px 10px rgba(109,166,32,.28);}
.bday-item-c .bday-user-avatar{background:linear-gradient(135deg,#d4800f,#f7941d);box-shadow:0 4px 10px rgba(215,120,15,.28);}

.bday-user-info{flex:1;min-width:0;}
.bday-user-name{margin:0;font-size:.97rem;font-weight:800;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.bday-user-msg{margin:.3rem 0 0;font-size:.78rem;font-weight:600;line-height:1.35;}
.bday-q-a{color:#0369a1;}.bday-q-b{color:#3f7b0f;}.bday-q-c{color:#b45309;}

/* ─ Footer ─ */
.bday-footer{
    display:flex;align-items:center;justify-content:space-between;gap:.75rem;
    padding:.85rem 1rem;background:#f8fafc;border-top:1px solid #e8edf5;
}
.bday-dismiss-label{display:flex;align-items:center;gap:.45rem;font-size:.81rem;font-weight:500;color:#64748b;cursor:pointer;user-select:none;}
.bday-dismiss-label:hover{color:#334155;}
.bday-dismiss-check{width:15px;height:15px;accent-color:var(--bd-blue);cursor:pointer;flex-shrink:0;}
.bday-close-btn{
    display:flex;align-items:center;gap:.35rem;border:0;cursor:pointer;
    color:#fff;font-size:.88rem;font-weight:700;padding:.58rem 1.25rem;border-radius:10px;
    background:linear-gradient(135deg,var(--bd-blue),var(--bd-blue2));
    box-shadow:0 6px 18px rgba(0,85,150,.28);
    transition:transform .15s ease,box-shadow .15s ease;
}
.bday-close-btn:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(0,85,150,.38);}
.bday-close-btn:active{transform:translateY(0);}

/* ─ Mobile ─ */
@media(max-width:480px){
    .bday-header{padding:1.1rem .9rem 1rem;}
    .bday-list{padding:.8rem .8rem .5rem;}
    .bday-footer{flex-wrap:wrap;padding:.7rem .8rem;}
    .bday-dismiss-label{font-size:.76rem;}
    .bday-user-avatar{width:36px;height:36px;font-size:.95rem;}
}
</style>
