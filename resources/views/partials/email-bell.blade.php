@php
    $__canMail = auth()->check() && (auth()->user()->is_admin || auth()->user()->is_staff);
@endphp

@if ($__canMail)
    @php
        $__mailboxes = \App\Services\MailboxMonitor::breakdown();
        $__mailTotal = (int) $__mailboxes->sum('last_unread_count');
    @endphp

    <div class="dropdown" id="mailBellWrap">
        <button class="btn-icon-header position-relative" type="button" data-bs-toggle="dropdown"
            data-bs-auto-close="outside" aria-expanded="false" id="mailBell" title="Mailboxes">
            <i class="fas fa-envelope"></i>
            <span id="mailUnreadBadge"
                style="display:{{ $__mailTotal > 0 ? 'flex' : 'none' }};position:absolute;top:-4px;right:-6px;background:var(--danger,#ef4444);color:#fff;font-size:0.6rem;font-weight:700;min-width:16px;height:16px;border-radius:8px;align-items:center;justify-content:center;padding:0 4px;box-shadow:0 2px 4px rgba(0,0,0,0.2);">{{ $__mailTotal }}</span>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="min-width:320px;">
            <li class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                <strong style="font-size:13px;"><i class="fas fa-envelope me-1"></i> Mailboxes</strong>
                <span>
                    <span id="mailLastChecked" class="text-muted me-2" style="font-size:11px;"></span>
                    <a href="{{ route('mail.index') }}" style="font-size:12px;">Manage</a>
                </span>
            </li>
            <li class="p-0">
                <div id="mailDropdownList" style="max-height:360px;overflow-y:auto;">
                    @forelse ($__mailboxes as $mb)
                        <div class="d-flex justify-content-between align-items-center gap-2 px-3 py-2 border-bottom">
                            <div style="min-width:0;">
                                <div class="text-truncate" style="font-size:13px;">{{ $mb->label }}</div>
                                <div class="text-muted text-truncate" style="font-size:11px;">{{ $mb->email }}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                @if ($mb->last_unread_count > 0)
                                    <span class="badge bg-danger">{{ $mb->last_unread_count }}</span>
                                @endif
                                @if ($mb->webmail_url)
                                    <a href="{{ $mb->webmail_url }}" target="_blank" rel="noopener"
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 py-0" style="font-size:11px;">Open</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-3 py-3 text-muted text-center" style="font-size:12px;">No mailboxes configured.</div>
                    @endforelse
                </div>
            </li>
        </ul>
    </div>

    <script>
        (function () {
            'use strict';

            var lastTotal = {{ $__mailTotal }};
            var badge = document.getElementById('mailUnreadBadge');
            var listBox = document.getElementById('mailDropdownList');
            var lastChecked = document.getElementById('mailLastChecked');
            var audioCtx = null;

            // ---- audio (persistent context, resumed on first user gesture) ----
            function initAudio() {
                try {
                    if (!audioCtx || audioCtx.state === 'closed') {
                        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    if (audioCtx && audioCtx.state === 'suspended') audioCtx.resume();
                } catch (e) {}
            }
            initAudio();
            ['click', 'keydown', 'touchstart', 'scroll'].forEach(function (ev) {
                document.addEventListener(ev, initAudio, { passive: true });
            });

            function playChime() {
                if (!audioCtx) initAudio();
                if (!audioCtx) return;
                if (audioCtx.state === 'suspended') audioCtx.resume();

                var now = audioCtx.currentTime;
                // Two-note chime, played twice, loud.
                var notes = [
                    [880, 0.00], [1174, 0.18],
                    [880, 0.60], [1174, 0.78]
                ];
                notes.forEach(function (n) {
                    var osc = audioCtx.createOscillator();
                    var gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.value = n[0];
                    gain.gain.setValueAtTime(0.0001, now + n[1]);
                    gain.gain.exponentialRampToValueAtTime(0.5, now + n[1] + 0.03);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + n[1] + 0.42);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now + n[1]);
                    osc.stop(now + n[1] + 0.45);
                });
            }

            function esc(s) {
                var d = document.createElement('div');
                d.textContent = s == null ? '' : s;
                return d.innerHTML;
            }

            function setBadge(total) {
                if (!badge) return;
                badge.textContent = total > 99 ? '99+' : total;
                badge.style.display = total > 0 ? 'flex' : 'none';
            }

            function renderList(mailboxes) {
                if (!listBox) return;
                if (!mailboxes || !mailboxes.length) {
                    listBox.innerHTML = '<div class="px-3 py-3 text-muted text-center" style="font-size:12px;">No mailboxes configured.</div>';
                    return;
                }
                listBox.innerHTML = mailboxes.map(function (m) {
                    var unread = m.unread > 0 ? '<span class="badge bg-danger">' + m.unread + '</span>' : '';
                    var open = m.url
                        ? '<a href="' + esc(m.url) + '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-0" style="font-size:11px;">Open</a>'
                        : '<span class="text-muted" style="font-size:11px;">No link</span>';
                    var err = m.error ? ' <span class="text-danger" title="' + esc(m.error) + '">⚠</span>' : '';
                    return '<div class="d-flex justify-content-between align-items-center gap-2 px-3 py-2 border-bottom">'
                        + '<div style="min-width:0;"><div class="text-truncate" style="font-size:13px;">' + esc(m.label) + err + '</div>'
                        + '<div class="text-muted text-truncate" style="font-size:11px;">' + esc(m.email) + '</div></div>'
                        + '<div class="d-flex align-items-center gap-2 flex-shrink-0">' + unread + open + '</div></div>';
                }).join('');
            }

            function notifyNewMail(mailboxes) {
                if (typeof Swal === 'undefined') return;
                var lines = (mailboxes || [])
                    .filter(function (m) { return m.unread > 0; })
                    .map(function (m) { return m.label + ': ' + m.unread; })
                    .join('<br>');
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'New email received',
                    html: lines || 'You have new mail.',
                    showConfirmButton: false,
                    timer: 8000,
                    timerProgressBar: true
                });
            }

            function loginPopup(mailboxes, total) {
                if (typeof Swal === 'undefined') return;
                if (sessionStorage.getItem('mailPopupShown') === '1') return;
                if (!total || total < 1) return;
                sessionStorage.setItem('mailPopupShown', '1');
                var lines = (mailboxes || [])
                    .filter(function (m) { return m.unread > 0; })
                    .map(function (m) {
                        return '<div style="display:flex;justify-content:space-between;padding:2px 0;">' +
                            '<span>' + esc(m.label) + '</span><strong>' + m.unread + '</strong></div>';
                    }).join('');
                Swal.fire({
                    icon: 'info',
                    title: 'You have unread email',
                    html: lines || 'You have new mail.',
                    showCancelButton: true,
                    confirmButtonText: 'Open Mailboxes',
                    cancelButtonText: 'Dismiss'
                }).then(function (r) {
                    if (r.isConfirmed) window.location.href = '{{ route('mail.index') }}';
                });
            }

            function check(initial) {
                fetch('{{ route('mail.unread') }}', { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        if (!d) return;
                        renderList(d.mailboxes);
                        if (lastChecked && d.checked_at) {
                            var dtc = new Date(d.checked_at);
                            if (!isNaN(dtc)) lastChecked.textContent = 'checked ' + dtc.toLocaleTimeString();
                        }
                        if (initial) {
                            lastTotal = d.total;
                            setBadge(d.total);
                            loginPopup(d.mailboxes, d.total);
                            return;
                        }
                        if (d.total > lastTotal) {
                            playChime();
                            notifyNewMail(d.mailboxes);
                        }
                        lastTotal = d.total;
                        setBadge(d.total);
                    })
                    .catch(function () {});
            }

            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () { check(true); }, 600);
                setInterval(function () { check(false); }, 30000);

                window.addEventListener('focus', function () { check(false); });
                document.addEventListener('visibilitychange', function () {
                    if (!document.hidden) check(false);
                });

                var bell = document.getElementById('mailBell');
                if (bell) {
                    bell.addEventListener('click', function () {
                        check(false);
                        setTimeout(function () { check(false); }, 2500);
                    });
                }
            });
        })();
    </script>
@endif
