<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="current-username" content="{{ Auth::user()->usernm }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" href="{{ asset('Logo-1.png') }}" type="image/png">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3"></script>

    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css"> --}}

    <title>{{ $title }}</title>
</head>

<body class="h-full flex flex-col">

    <div class="min-h-full flex flex-col flex-grow">
        <x-navbar></x-navbar>

        <x-header>{{ $title }}
            <x-slot:navhint><x-nav-hint>
                    {{ $navbar ?? 'Not Defined' }}
                    @isset($nav)
                        <x-slot:secondarySlot>
                            {{ $nav }}
                        </x-slot:secondarySlot>
                    @endisset
                    @isset($navnav)
                        @if (isset($navnav) && isset($routeName))
                            <x-slot:routeName>
                                {{ $routeName }}
                            </x-slot:routeName>
                            <x-slot:tertiarySlot>
                                {{ $navnav }}
                            </x-slot:tertiarySlot>
                        @else
                            <x-slot:tertiarySlot>
                                {{ $navnav }}
                            </x-slot:tertiarySlot>
                        @endif
                    @endisset
                </x-nav-hint></x-slot:navhint>
        </x-header>
        <main class="flex-grow">
            {{ $hero ?? null }}
            <div class="mx-auto px-4 py-6 sm:px-6 lg:px-8">
                @error('duplicateClosing')
                    <div
                        class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100 dark:bg-gray-800 dark:text-red-400 w-fit">
                        {{ $message }}</div>
                @enderror

                @if (session('success1'))
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            alert("{{ session('success1') }}");
                        });
                    </script>
                @endif

                @if (session('error'))
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            alert("{{ session('error') }}");
                        });
                    </script>
                @endif

                {{ $slot }}
            </div>
        </main>

        <x-footer></x-footer>
    </div>

</body>
<x-script></x-script>
<x-style></x-style>

</html>
