<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>2Text AI</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:weight@400;600;700&display=swap">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:ital,wght@0,400;0,700;1,400;1,700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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

<body class="font-sans antialiased w-full">
    <x-notifications />
    <x-jet-banner />
    @livewire('common.notifications')

    <main class="flex w-full md:grid md:grid-cols-5 min-h-screen bg-zinc-100">
        <div class="hidden md:block md:col-span-1 h-full p-6 bg-white border-r-2">
            @include('livewire.common.sidebar')
        </div>
        <!-- Page Content -->
        <div class="col-span-5 md:col-span-4 h-full px-0 mb-8 pb-6">
            @livewire('navigation-menu')
            <div class="p-3 md:p-6 md:rounded-lg bg-zinc-100">
                {{ $slot }}
            </div>
        </div>

        <footer class="w-full fixed bottom-0 left-0 text-start h-8 py-2 px-4 bg-white border border-t text-xs font-bold border-gray-200">2Text.ai (beta)</footer>
    </main>

    @livewireScripts
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
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
        })

        document.addEventListener('livewire:load', function() {
            window.livewire.on('addToClipboard', function(message) {
                navigator.clipboard.writeText(message);
            });
        });
    </script>
</body>

</html>
