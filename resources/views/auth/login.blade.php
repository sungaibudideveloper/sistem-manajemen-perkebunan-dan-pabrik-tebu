<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <link rel="icon" href="{{ asset('Logo-1.png') }}" type="image/png">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>Login</title>
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: background-image 1.5s ease-in-out;
        }
    </style>
    <style>
        .text-shadow {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
    </style>
    <style>
        .img-shadow {
            box-shadow: 0px 0px 4px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body class="h-full" id="dynamic-background">
    <div class="min-h-full">
        <div class="flex min-h-full flex-col justify-center px-6 pt-12 pb-6 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                <img class="mx-auto h-14 w-auto img-shadow" src="{{ asset('img/Logo-1.png') }}" alt="Sungai Budi">
                <h2 class="mt-8 pb-8 text-center text-2xl font-bold tracking-tight text-gray-50 text-shadow">Log in to
                    your account</h2>

                <div class="bg-white px-8 pt-4 pb-8 rounded-lg shadow-lg">
                    @if ($errors->any())
                        <div class="p-4 text-red-700 bg-red-100 rounded-lg mb-2 mt-2 text-sm">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="space-y-6" action="{{ route('login') }}" method="POST">
                        @csrf
                        <div>
                            <label for="userid" class="block text-sm font-medium text-gray-900">Username</label>
                            <div class="mt-2">
                                <input id="usernm" name="userid" value="{{ old('userid') }}" type="text" required
                                    placeholder="Enter Username"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm
                                {{ $errors->has('login_error') ? 'border-red-500 ring-red-500 focus:ring-red-500' : 'ring-gray-300 focus:ring-gray-600' }}">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-sm font-medium text-gray-900">Password</label>
                            </div>
                            <div class="mt-2 relative">
                                <input id="password" name="password" type="password" autocomplete="current-password"
                                    placeholder="Enter password" value="{{ old('password') }}" required
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm
                                    {{ $errors->has('login_error') ? 'border-red-500 ring-red-500 focus:ring-red-500' : 'ring-gray-300 focus:ring-gray-600' }}">
                                <button type="button" id="toggle-password-visibility"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <svg id="eye-icon" class="w-6 h-6 text-gray-400 dark:text-white" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd"
                                            d="M4.998 7.78C6.729 6.345 9.198 5 12 5c2.802 0 5.27 1.345 7.002 2.78a12.713 12.713 0 0 1 2.096 2.183c.253.344.465.682.618.997.14.286.284.658.284 1.04s-.145.754-.284 1.04a6.6 6.6 0 0 1-.618.997 12.712 12.712 0 0 1-2.096 2.183C17.271 17.655 14.802 19 12 19c-2.802 0-5.27-1.345-7.002-2.78a12.712 12.712 0 0 1-2.096-2.183 6.6 6.6 0 0 1-.618-.997C2.144 12.754 2 12.382 2 12s.145-.754.284-1.04c.153-.315.365-.653.618-.997A12.714 12.714 0 0 1 4.998 7.78ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <button type="submit"
                                class="flex items-center gap-2 w-full justify-center rounded-md bg-gray-800 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
                                <svg class="w-4 h-4" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                    viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="3"
                                        d="M16 12H4m12 0-4 4m4-4-4-4m3-4h2a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3h-2" />
                                </svg>
                                <span>
                                    Log in
                                </span>
                            </button>
                        </div>
                    </form>
                    <p class="mt-10 text-center text-sm font-bold text-gray-500">
                        Monitoring
                    </p>
                    <p class="text-center text-xs text-gray-500">
                        PT. Sungai Budi Group
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const images = [
            // "{{ asset('img/bg/3.jpg') }}",
            "{{ asset('img/bg/4.jpg') }}",
            "{{ asset('img/bg/5.jpg') }}",
        ];

        let currentIndex = 0;
        const body = document.getElementById('dynamic-background');

        function changeBackground() {
            currentIndex = (currentIndex + 1) % images.length;
            body.style.backgroundImage = `url('${images[currentIndex]}')`;
        }

        body.style.backgroundImage = `url('${images[0]}')`;

        setInterval(changeBackground, 5000);
    </script>
    <script>
        const passwordInput = document.getElementById('password');
        const togglePasswordVisibility = document.getElementById('toggle-password-visibility');
        const eyeIcon = document.getElementById('eye-icon');

        togglePasswordVisibility.addEventListener('click', () => {
            const isPasswordVisible = passwordInput.type === 'text';
            passwordInput.type = isPasswordVisible ? 'password' : 'text';

            // Update SVG icon
            eyeIcon.innerHTML = isPasswordVisible ?
                `<path fill-rule="evenodd" d="M4.998 7.78C6.729 6.345 9.198 5 12 5c2.802 0 5.27 1.345 7.002 2.78a12.713 12.713 0 0 1 2.096 2.183c.253.344.465.682.618.997.14.286.284.658.284 1.04s-.145.754-.284 1.04a6.6 6.6 0 0 1-.618.997 12.712 12.712 0 0 1-2.096 2.183C17.271 17.655 14.802 19 12 19c-2.802 0-5.27-1.345-7.002-2.78a12.712 12.712 0 0 1-2.096-2.183 6.6 6.6 0 0 1-.618-.997C2.144 12.754 2 12.382 2 12s.145-.754.284-1.04c.153-.315.365-.653.618-.997A12.714 12.714 0 0 1 4.998 7.78ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>` :
                `<path d="m4 15.6 3.055-3.056A4.913 4.913 0 0 1 7 12.012a5.006 5.006 0 0 1 5-5c.178.009.356.027.532.054l1.744-1.744A8.973 8.973 0 0 0 12 5.012c-5.388 0-10 5.336-10 7A6.49 6.49 0 0 0 4 15.6Z"/>
                   <path d="m14.7 10.726 4.995-5.007A.998.998 0 0 0 18.99 4a1 1 0 0 0-.71.305l-4.995 5.007a2.98 2.98 0 0 0-.588-.21l-.035-.01a2.981 2.981 0 0 0-3.584 3.583c0 .012.008.022.01.033.05.204.12.402.211.59l-4.995 4.983a1 1 0 1 0 1.414 1.414l4.995-4.983c.189.091.386.162.59.211.011 0 .021.007.033.01a2.982 2.982 0 0 0 3.584-3.584c0-.012-.008-.023-.011-.035a3.05 3.05 0 0 0-.21-.588Z"/>
                   <path d="m19.821 8.605-2.857 2.857a4.952 4.952 0 0 1-5.514 5.514l-1.785 1.785c.767.166 1.55.25 2.335.251 6.453 0 10-5.258 10-7 0-1.166-1.637-2.874-2.179-3.407Z"/>`;
        });
    </script>

</body>

</html>
