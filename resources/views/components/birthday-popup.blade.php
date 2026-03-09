<div id="birthdayModal" class="birthday-modal-root is-hidden">
    <div class="birthday-modal-backdrop" onclick="closeBirthdayModal()"></div>

    <div class="birthday-modal-panel animate-slide-up" role="dialog" aria-modal="true" aria-labelledby="birthdayTitle">
        <div class="birthday-modal-header">
            <div class="birthday-header-content">
                <h2 id="birthdayTitle" class="birthday-title">Selamat Ulang Tahun</h2>
                <p class="birthday-subtitle">BPS Kabupaten Mojokerto menyampaikan apresiasi dan doa terbaik.</p>
            </div>
        </div>

        <div class="birthday-list-area" id="birthdayList"></div>

        <div class="birthday-footer">
            <label class="birthday-dismiss-label">
                <input type="checkbox" id="birthdayDismissCheck" class="birthday-dismiss-check">
                <span>Jangan tampilkan lagi hari ini</span>
            </label>
            <button type="button" onclick="closeBirthdayModal()" class="birthday-close-btn">✓ Tutup</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const birthdays = @json($globalBirthdays ?? []);
        const currentUserId = '{{ auth()->id() }}';
        const popupSessionKey = 'birthday_popup_shown_in_tab_{{ session()->getId() }}';
        const currentPath = window.location.pathname;
        const isDashboardPage = currentPath === '/dashboard';
        let isInternalNavigation = false;

        const today = new Date().toISOString().slice(0, 10);
        const dismissKey = `birthday_dismissed_${currentUserId}_${today}`;

        function showBirthdayPopupIfAllowed() {
            const alreadyShownInThisTab = sessionStorage.getItem(popupSessionKey) === '1';
            const permanentlyDismissed = localStorage.getItem(dismissKey) === '1';

            if (birthdays.length === 0 || alreadyShownInThisTab || isDashboardPage || permanentlyDismissed) {
                return;
            }

            let html = '';
            birthdays.forEach((user, index) => {
                const name = (user.name || '').trim();
                const accentStyles = [
                    'birthday-item-accent-a',
                    'birthday-item-accent-b',
                    'birthday-item-accent-c'
                ];
                const quoteStyles = [
                    'birthday-quote-a',
                    'birthday-quote-b',
                    'birthday-quote-c'
                ];

                html += `
                    <div class="birthday-user-item ${accentStyles[index % accentStyles.length]}">
                        <div class="birthday-user-content">
                            <p class="birthday-user-name">${name}</p>
                            <p class="birthday-user-msg ${quoteStyles[index % quoteStyles.length]}">
                                Semoga sehat, bahagia, dan sukses selalu.
                            </p>
                        </div>
                    </div>
                `;
            });

            const modal = document.getElementById('birthdayModal');
            document.getElementById('birthdayList').innerHTML = html;
            modal.classList.remove('is-hidden');
            modal.classList.add('is-visible');
            sessionStorage.setItem(popupSessionKey, '1');
        }

        // Initial load.
        showBirthdayPopupIfAllowed();

        // Mark internal navigation so switching menu/submenu does not retrigger popup.
        document.addEventListener('click', function(event) {
            const link = event.target.closest('a[href]');
            if (!link) {
                return;
            }

            const href = link.getAttribute('href');
            const target = link.getAttribute('target');
            if (!href || href.startsWith('#') || target === '_blank') {
                return;
            }

            try {
                const url = new URL(link.href, window.location.origin);
                if (url.origin === window.location.origin) {
                    isInternalNavigation = true;
                }
            } catch (error) {
                // Ignore malformed URLs.
            }
        }, true);

        document.addEventListener('submit', function() {
            isInternalNavigation = true;
        }, true);

        // If user leaves this tab and comes back, allow popup again.
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden') {
                if (!isInternalNavigation) {
                    sessionStorage.removeItem(popupSessionKey);
                }
                return;
            }

            if (document.visibilityState === 'visible') {
                showBirthdayPopupIfAllowed();
            }
        });
    });

    function closeBirthdayModal() {
        const modal = document.getElementById('birthdayModal');
        const check = document.getElementById('birthdayDismissCheck');
        if (check && check.checked) {
            const today = new Date().toISOString().slice(0, 10);
            const currentUserId = '{{ auth()->id() }}';
            localStorage.setItem(`birthday_dismissed_${currentUserId}_${today}`, '1');
        }
        modal.classList.add('is-hidden');
        modal.classList.remove('is-visible');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBirthdayModal();
        }
    });
</script>

<style>
    :root {
        --bps-blue: #005596;
        --bps-green: #8dc63f;
        --bps-orange: #f7941d;
        --bps-soft-bg: #f5f9ff;
    }

    @keyframes slide-up {
        from {
            opacity: 0;
            transform: translateY(50px) scale(0.95);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .animate-slide-up {
        animation: slide-up 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .birthday-modal-root {
        position: fixed;
        inset: 0;
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .birthday-modal-root.is-hidden {
        display: none;
    }

    .birthday-modal-root.is-visible {
        display: flex;
    }

    .birthday-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.56);
        backdrop-filter: blur(4px);
    }

    .birthday-modal-panel {
        position: relative;
        width: min(620px, calc(100vw - 2rem));
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.12);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 22px 42px rgba(2, 21, 43, 0.2);
    }

    .birthday-modal-header {
        position: relative;
        background: linear-gradient(90deg, #f8fbff 0%, #f3f9f1 100%);
        padding: 1.35rem 1.4rem;
        text-align: left;
        border-bottom: 1px solid #dbe8f4;
    }

    .birthday-modal-header::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        background: linear-gradient(180deg, var(--bps-blue) 0%, var(--bps-green) 100%);
    }

    .birthday-header-content {
        position: relative;
        z-index: 1;
    }

    .birthday-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.35rem, 2.2vw, 1.6rem);
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .birthday-subtitle {
        margin: 0.25rem 0 0;
        color: #334155;
        font-size: 0.92rem;
        font-weight: 500;
        line-height: 1.4;
    }

    .birthday-list-area {
        background: #ffffff;
        padding: 1rem 1rem 0.7rem;
        max-height: min(48vh, 360px);
        overflow: auto;
    }

    .birthday-user-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 10px;
        padding: 12px 14px;
        border-radius: 10px;
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid #e2e8f0;
        background: #fcfdff;
    }

    .birthday-user-item:hover {
        background: #f8fbff;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06);
    }

    .birthday-item-accent-a {
        border-left: 5px solid var(--bps-blue);
    }

    .birthday-item-accent-b {
        border-left: 5px solid var(--bps-green);
    }

    .birthday-item-accent-c {
        border-left: 5px solid var(--bps-orange);
    }

    .birthday-user-content {
        flex: 1;
        min-width: 0;
        position: relative;
        padding-left: 2px;
    }

    .birthday-user-name {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.25;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .birthday-user-msg {
        margin: 5px 0 0;
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 1.35;
    }

    .birthday-quote-a {
        color: #0369a1;
    }

    .birthday-quote-b {
        color: #3f7b0f;
    }

    .birthday-quote-c {
        color: #b45309;
    }

    .birthday-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.9rem 1rem;
        gap: 0.75rem;
    }

    .birthday-dismiss-label {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.82rem;
        font-weight: 500;
        color: #64748b;
        cursor: pointer;
        user-select: none;
    }

    .birthday-dismiss-label:hover {
        color: #334155;
    }

    .birthday-dismiss-check {
        width: 15px;
        height: 15px;
        accent-color: var(--bps-blue);
        cursor: pointer;
        flex-shrink: 0;
    }

    .birthday-close-btn {
        border: 0;
        color: #ffffff;
        font-size: 0.9rem;
        font-weight: 700;
        padding: 0.62rem 1.2rem;
        border-radius: 8px;
        cursor: pointer;
        background: var(--bps-blue);
        box-shadow: 0 6px 14px rgba(0, 85, 150, 0.24);
    }

    .birthday-close-btn:hover {
        background: #004a84;
    }

    @media (max-width: 640px) {
        .birthday-modal-header {
            padding: 1.15rem 0.95rem;
        }

        .birthday-list-area {
            padding: 0.8rem 0.8rem 0.6rem;
        }

        .birthday-user-item {
            padding: 10px;
            gap: 10px;
        }

        .birthday-user-name {
            font-size: 0.9rem;
        }

        .birthday-user-msg {
            font-size: 0.76rem;
        }

        .birthday-footer {
            padding: 0.75rem 0.8rem;
            flex-wrap: wrap;
        }

        .birthday-dismiss-label {
            font-size: 0.78rem;
        }
    }
</style>
