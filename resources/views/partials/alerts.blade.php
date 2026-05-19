{{-- Toast Notifications --}}
@php
    $notifications = [];

    if ($errors->any()) {
        $notifications[] = [
            'type' => 'danger',
            'icon' => '⚠️',
            'title' => 'Validation Error',
            'message' => $errors->all(),
            'isList' => true,
        ];
    }

    if (session('success')) {
        $notifications[] = [
            'type' => 'success',
            'icon' => '✅',
            'title' => 'Success!',
            'message' => session('success'),
            'isList' => false,
        ];
    }
@endphp

@if (!empty($notifications))
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
        @foreach ($notifications as $notification)
            <div class="toast align-items-center text-white bg-{{ $notification['type'] }} border-0 mb-3" role="alert"
                data-bs-autohide="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-start gap-2">
                        <span class="fs-5">{{ $notification['icon'] }}</span>
                        <div>
                            <strong class="d-block mb-1">{{ $notification['title'] }}</strong>
                            @if ($notification['isList'])
                                <ul class="mb-0 ps-3 small">
                                    @foreach ($notification['message'] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="small">{{ $notification['message'] }}</span>
                            @endif
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.toast').forEach(toastEl => {
                const toast = new bootstrap.Toast(toastEl, {
                    autohide: true,
                    delay: 4000
                });
                toast.show();
            });
        });
    </script>
@endif
