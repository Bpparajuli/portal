@php
    $currentPopupPage = null;
    $routeName = request()->route()?->getName();

    if ($routeName === 'home' || str_starts_with($routeName ?? '', 'guest.')) {
        $currentPopupPage = 'guest';
    } elseif (str_starts_with($routeName ?? '', 'agent.')) {
        $currentPopupPage = 'agent';
    }

    $popup = null;
    $previewId = request()->get('preview_popup');

    if ($previewId) {
        $popup = \App\Models\Popup::find((int) $previewId);
    } else {
        $popup = \App\Models\Popup::active()
            ->get()
            ->first(function ($p) use ($currentPopupPage) {
                $pages = $p->display_on ?? [];
                if (in_array('all', $pages)) {
                    return true;
                }
                if (!$currentPopupPage) {
                    return false;
                }
                return in_array($currentPopupPage, $pages);
            });
    }
@endphp

@if ($popup)
    <div id="sitePopupOverlay"
        style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:99990;display:none;align-items:center;justify-content:center;backdrop-filter:blur(2px);"
        onclick="if(event.target===this) closeSitePopup()">

        <div id="sitePopupCard"
            style="background:#fff;border-radius:16px;width:max-content;max-width:95%;max-height:95vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,.3);animation:popupIn .3s ease-out;position:relative;">

            @if ($popup->show_close)
                <button onclick="closeSitePopup()"
                    style="position:absolute;top:8px;right:10px;background:none;border:none;font-size:1.2rem;color:#6b7280;cursor:pointer;z-index:1;line-height:1;padding:4px 6px;">&times;</button>
            @endif

            @if ($popup->image)
                <img src="{{ Storage::url($popup->image) }}" alt=""
                    style="display:block;width:auto;height:auto;max-width:100%;max-height:80vh;object-fit:contain;border-radius:16px 16px 0 0;">
            @endif

            <div style="padding:1.25rem 1.5rem 1.5rem;">
                @if ($popup->title)
                    <h5 style="margin:0 0 .5rem;font-weight:700;color:#1a0262;font-size:1rem;">{{ $popup->title }}</h5>
                @endif
                @if ($popup->description)
                    <p style="margin:0 0 1rem;font-size:.82rem;color:#4b5563;line-height:1.5;">{{ $popup->description }}
                    </p>
                @endif
                @if ($popup->button_text && $popup->button_link)
                    <a href="{{ $popup->button_link }}" target="{{ $popup->button_target }}"
                        style="display:inline-block;padding:.5rem 1.25rem;background:linear-gradient(135deg,#1a0262,#820b5c);color:#fff;border-radius:8px;font-size:.78rem;font-weight:600;text-decoration:none;">
                        {{ $popup->button_text }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <style>
        @keyframes popupIn {
            from {
                opacity: 0;
                transform: scale(.92) translateY(20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>

    <script>
        function closeSitePopup() {
            document.getElementById('sitePopupOverlay').style.display = 'none';
        }
        @if ($popup->display_duration > 0 && !$previewId)
            setTimeout(closeSitePopup, {{ $popup->display_duration * 1000 }});
        @endif
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('sitePopupOverlay').style.display = 'flex';
            }, 500);
        });
    </script>
@endif
