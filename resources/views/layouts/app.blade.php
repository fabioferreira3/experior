<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Experior AI - {{ $title ?? 'Welcome' }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:weight@400;600;700&display=swap">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Courier+Prime:ital,wght@0,400;0,700;1,400;1,700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Scripts -->
    @wireUiScripts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</head>

<body class="font-sans antialiased w-full bg-main">
    <x-notifications />
    <x-jet-banner />
    @livewire('common.notifications')

    <main class="flex w-full md:grid md:grid-cols-7 xl:grid-cols-6 min-h-screen">
        <div class="hidden sm:block md:col-span-2 xl:col-span-1 h-full p-6 bg-main">
            @livewire('common.sidebar')
        </div>
        <!-- Page Content -->
        <div class="w-full md:col-span-5 h-full px-0 mb-8 pb-6">
            @livewire('navigation-menu')
            <div class='h-0.5 sm:px-8'>
                <div class='h-full bg-secondary rounded-lg'></div>
            </div>
            <div class="p-8 md:p-6 md:rounded-l-lg h-full bg-white">
                {{ $slot }}
            </div>
        </div>

        @livewire('chat')
        @include('components.footer')
    </main>

    @livewireScripts
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    @vite(['resources/js/typewriter.js'])
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top',
            showConfirmButton: false,
            showCloseButton: true,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Define the 'ended' event handler.
        function audioEndedHandler() {
            window.livewire.emit('stop-audio');
        }

        window.addEventListener('alert', ({
            detail: {
                type,
                message
            }
        }) => {
            Toast.fire({
                icon: type,
                title: message
            })
        })

        window.addEventListener('refresh-page', () => {
            window.location.reload();
        });

        let currentAudio = null;

        window.addEventListener('play-audio', ({
            detail: {
                id
            }
        }) => {
            if (currentAudio) {
                // Remove existing listener to avoid duplicate event triggers.
                currentAudio.removeEventListener('ended', audioEndedHandler);
                currentAudio.pause();
                currentAudio.load();

            }

            currentAudio = document.getElementById(id);

            // Add the 'ended' event listener to the current audio clip.
            currentAudio.addEventListener('ended', audioEndedHandler);

            currentAudio.play().catch((error) => {
                console.error("Play error:", error);
            });
        });

        window.addEventListener('stop-audio', () => {
            if (currentAudio) {
                currentAudio.pause();
                currentAudio.load();
            }
        });

        document.addEventListener('livewire:load', function() {
            window.livewire.on('addToClipboard', function(message) {
                navigator.clipboard.writeText(message);
            });
        });

        window.livewire.on('openLinkInNewTab', link => {
            window.open(link, '_blank');
        });
    </script>
    @stack('scripts')
</body>

</html>