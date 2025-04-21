<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="current-username" content="{{ Auth::user()->usernm }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('asset/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/font-awesome-6.5.1-all.min.css') }}">
    <link rel="icon" href="{{ asset('Logo-1.png') }}" type="image/png">
    <style>[x-cloak] { display: none !important; }</style>
    <script defer src="{{ asset('asset/alpinejs.min.js') }}"></script>
    <script src="{{ asset('asset/chart.js') }}"></script>
    <script src="{{ asset('asset/chartjs-plugin-datalabels@2.0.0.js') }}"></script>
    <script src="{{ asset('asset/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('asset/simple-datatables@9.0.3.js') }}"></script>

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
