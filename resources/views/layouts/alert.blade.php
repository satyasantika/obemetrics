@once
    @php
        $toastItems = [];

        if (session('success')) {
            $toastItems[] = [
                'type' => 'success',
                'message' => session('success'),
                'delay' => 3500,
            ];
        }

        if (session('danger')) {
            $toastItems[] = [
                'type' => 'danger',
                'message' => session('danger'),
                'delay' => 5000,
            ];
        }

        if (session('warning')) {
            $toastItems[] = [
                'type' => 'danger',
                'message' => session('warning'),
                'delay' => 5000,
            ];
        }
    @endphp

    @if (!empty($toastItems))
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
            @foreach ($toastItems as $index => $toast)
                <div
                    id="globalFlashToast{{ $index }}"
                    class="toast text-bg-{{ $toast['type'] }} border-0 mb-2"
                    role="alert"
                    aria-live="assertive"
                    aria-atomic="true"
                    data-bs-delay="{{ $toast['delay'] }}"
                >
                    <div class="d-flex">
                        <div class="toast-body">{{ $toast['message'] }}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            @endforeach
        </div>

        @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.bootstrap || !window.bootstrap.Toast) {
                return;
            }

            document.querySelectorAll('[id^="globalFlashToast"]').forEach(function (element) {
                new window.bootstrap.Toast(element).show();
            });
        });
        </script>
        @endpush
    @endif
@endonce
