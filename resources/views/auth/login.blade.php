<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - Sungai Budi Group Sugarcane Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        'primary': {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #065f46 0%, #047857 25%, #059669 50%, #10b981 75%, #34d399 100%);
        }
        
        .glass-effect {
            backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-animation:nth-child(2) {
            animation-delay: -2s;
        }
        
        .floating-animation:nth-child(3) {
            animation-delay: -4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(1deg); }
            66% { transform: translateY(10px) rotate(-1deg); }
        }
        
        .input-focus {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(34, 197, 94, 0.15);
        }
        
        .btn-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(34, 197, 94, 0.3);
        }
        
        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .slide-in-left {
            animation: slideInLeft 0.8s ease-out;
        }
        
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-40px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .sugar-cane-icon {
            background: linear-gradient(45deg, #22c55e, #16a34a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>

<body class="h-full bg-gray-50 overflow-hidden">
    <div class="min-h-screen flex">
        <!-- Left Side - Brand/Visual -->
        <div class="hidden lg:flex lg:w-1/2 gradient-bg relative overflow-hidden">
            <!-- Floating Elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="floating-animation absolute top-20 left-20 w-32 h-32 bg-white bg-opacity-10 rounded-full blur-xl"></div>
                <div class="floating-animation absolute top-60 right-32 w-24 h-24 bg-white bg-opacity-10 rounded-full blur-xl"></div>
                <div class="floating-animation absolute bottom-32 left-40 w-40 h-40 bg-white bg-opacity-10 rounded-full blur-xl"></div>
            </div>
            
            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center px-16 py-20">
                <div class="slide-in-left">
                    <!-- Logo/Icon -->
                    <div class="mb-12">
                        <div class="w-20 h-20 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM12 3c4.96 0 9 4.04 9 9s-4.04 9-9 9-9-4.04-9-9 4.04-9 9-9zm0 2c-3.87 0-7 3.13-7 7s3.13 7 7 7 7-3.13 7-7-3.13-7-7-7zm0 2c2.76 0 5 2.24 5 5s-2.24 5-5 5-5-2.24-5-5 2.24-5 5-5zm0 2c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Title -->
                    <h1 class="text-3xl font-bold text-white mb-6 leading-tight">
                        Sungai Budi Group<br>
                        <span class="text-primary-200">Sugarcane Management System</span>
                    </h1>
                    
                    <!-- Description -->
                    <p class="text-xl text-primary-100 mb-8 max-w-md leading-relaxed">
                        Streamline your sugarcane operations with our comprehensive monitoring and management system.
                    </p>
                    
                    <!-- Features -->
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-primary-200 rounded-full"></div>
                            <span class="text-primary-100">Real-time field monitoring</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-primary-200 rounded-full"></div>
                            <span class="text-primary-100">Harvest optimization</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-primary-200 rounded-full"></div>
                            <span class="text-primary-100">Quality control tracking</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="flex-1 flex items-center justify-center px-6 py-12 lg:px-8">
            <div class="w-full max-w-md fade-in">
                <!-- Mobile Logo -->
                <div class="lg:hidden mb-8 text-center">
                    <div class="w-16 h-16 bg-primary-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM12 3c4.96 0 9 4.04 9 9s-4.04 9-9 9-9-4.04-9-9 4.04-9 9-9zm0 2c-3.87 0-7 3.13-7 7s3.13 7 7 7 7-3.13 7-7-3.13-7-7-7zm0 2c2.76 0 5 2.24 5 5s-2.24 5-5 5-5-2.24-5-5 2.24-5 5-5zm0 2c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Sungai Budi Group<br>Sugarcane Management System</h2>
                </div>
                
                <!-- Login Header -->
                <div class="text-center mb-8">
                    <h3 class="text-3xl font-bold text-gray-900 mb-2">Welcome back</h3>
                    <p class="text-gray-600">Please sign in to your account</p>
                </div>
                
                <!-- Login Form -->
                <div class="glass-effect rounded-3xl p-8 shadow-2xl">
                    <!-- Error Display -->
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="text-red-700 text-sm">
                                    @foreach ($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <form class="space-y-6" action="{{ route('login') }}" method="POST">
                        @csrf
                        <!-- Username Field -->
                        <div>
                            <label for="userid" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="userid" 
                                    name="userid" 
                                    value="{{ old('userid') }}"
                                    required 
                                    class="input-focus w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-500 focus:ring-opacity-20 outline-none transition-all duration-300 {{ $errors->has('login_error') ? 'border-red-500 ring-red-500 focus:ring-red-500' : '' }}"
                                    placeholder="Enter your username"
                                >
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Password Field -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    value="{{ old('password') }}"
                                    required 
                                    class="input-focus w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-500 focus:ring-opacity-20 outline-none transition-all duration-300 {{ $errors->has('login_error') ? 'border-red-500 ring-red-500 focus:ring-red-500' : '' }}"
                                    placeholder="Enter your password"
                                >
                                <button 
                                    type="button" 
                                    onclick="togglePasswordVisibility()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                    <svg id="eye-icon" class="w-5 h-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                            <a href="#" class="text-sm text-primary-600 hover:text-primary-500 transition-colors">
                                Forgot password?
                            </a>
                        </div>
                        
                        <!-- Login Button -->
                        <button 
                            type="submit" 
                            class="btn-hover w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-3 px-6 rounded-2xl transition-all duration-300 flex items-center justify-center space-x-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            <span>Sign In</span>
                        </button>
                    </form>
                    
                    <!-- Footer -->
                    <div class="mt-8 text-center">
                        <p class="text-sm text-gray-500 mb-1">
                            <span class="font-semibold">Sungai Budi Group</span>
                        </p>
                        <p class="text-xs text-gray-400">
                            Sugarcane Management System
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464m1.414 1.414L12 12l2.122-2.122m-5.256 5.256L12 12l2.122 2.122m-5.256-5.256L12 12"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6"></path>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        }
        
        // Add smooth focus effects
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('scale-105');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('scale-105');
            });
        });
    </script>
</body>

</html>