<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#3b82f6">
    
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/icon-sb-tebu-circle.png') }}">
    
    <title>Login - Sungai Budi Group Sugarcane Management System</title>
    
    @vite(['resources/css/login.css'])
    
    <!-- Alpine.js for modal -->
    <script defer src="{{ asset('asset/alpinejs.min.js') }}"></script>
    
    <!-- Google reCAPTCHA v2 -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body class="h-full bg-gray-50">
    <div class="login-container min-h-screen flex" x-data="forgotPasswordData()">
        <!-- Left Side - Brand/Visual -->
        <div class="hidden lg:flex lg:w-1/2 gradient-bg relative overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="floating-animation absolute top-20 left-20 w-32 h-32 bg-white bg-opacity-10 rounded-full blur-xl"></div>
                <div class="floating-animation absolute top-60 right-32 w-24 h-24 bg-white bg-opacity-10 rounded-full blur-xl"></div>
                <div class="floating-animation absolute bottom-32 left-40 w-40 h-40 bg-white bg-opacity-10 rounded-full blur-xl"></div>
            </div>
            
            <div class="relative z-10 flex flex-col justify-center px-16 py-20">
                <div class="slide-in-left">
                    <div class="mb-12">
                        <div class="logo-container">
                            <img src="{{ asset('img/logo-tebu-white.png') }}" alt="Sungai Budi Group Logo">
                        </div>
                    </div>
                    
                    <h1 class="text-3xl font-bold text-white mb-6 leading-tight">
                        Sungai Budi Group<br>
                        <span class="text-green-200">Sugarcane Management System</span>
                    </h1>
                    
                    <p class="text-xl text-green-100 mb-8 max-w-md leading-relaxed">
                        Streamline your sugarcane operations with our comprehensive monitoring and management system.
                    </p>
                    
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-green-200 rounded-full"></div>
                            <span class="text-green-100">Real-time field monitoring</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-green-200 rounded-full"></div>
                            <span class="text-green-100">Harvest optimization</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-green-200 rounded-full"></div>
                            <span class="text-green-100">Quality control tracking</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="login-right-side flex-1 flex lg:items-center items-start justify-center">
            <div class="w-full max-w-md login-content-wrapper">
                <div class="mobile-scale">
                    <div class="lg:hidden mb-8 text-center">
                        <div class="logo-container mx-auto mb-4">
                            <img src="{{ asset('img/logo-tebu-white.png') }}" alt="Sungai Budi Group Logo">
                        </div>
                    </div>
                
                    <div class="text-center mb-6 sm:mb-8">
                        <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Welcome back</h3>
                        <p class="text-sm sm:text-base text-gray-600">Please sign in to your account</p>
                    </div>
                    
                    <div class="glass-effect rounded-3xl p-6 sm:p-8 shadow-2xl">
                        @if ($errors->any())
                            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-2xl">
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="text-red-700 text-xs sm:text-sm">
                                        @foreach ($errors->all() as $error)
                                            <div>{{ $error }}</div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-2xl">
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="text-green-700 text-xs sm:text-sm">
                                        {{ session('success') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    
                    <form class="space-y-4 sm:space-y-6" 
                        action="{{ route('login') }}" 
                        method="POST"
                        x-data="{ loading: false }"
                        @submit="loading = true">
                        @csrf
                        
                        <div>
                            <label for="userid" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="userid" 
                                    name="userid" 
                                    value="{{ old('userid') }}"
                                    required 
                                    autocomplete="username"
                                    class="input-focus w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-opacity-20 outline-none transition-all duration-300"
                                    placeholder="Enter your username"
                                >
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    required 
                                    autocomplete="current-password"
                                    class="input-focus w-full px-4 py-3 pr-12 rounded-2xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-opacity-20 outline-none transition-all duration-300"
                                    placeholder="Enter your password"
                                >
                                <button 
                                    type="button" 
                                    onclick="togglePasswordVisibility()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    aria-label="Toggle password visibility"
                                >
                                    <svg id="eye-icon" class="w-5 h-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="remember" class="cursor-pointer">
                                <span class="text-sm text-gray-600">Remember me</span>
                            </label>
                            <button type="button" @click="forgotPasswordModal = true" 
                                class="text-sm text-green-600 hover:text-green-500 transition-colors inline-block py-2">
                                Forgot password?
                            </button>
                        </div>
                        
                        <button 
                            type="submit"
                            :disabled="loading"
                            class="btn-hover w-full bg-green-600 hover:bg-green-700 disabled:bg-green-400 disabled:cursor-not-allowed text-white font-medium py-3 px-6 rounded-2xl transition-all duration-300 flex items-center justify-center space-x-2 active:scale-95"
                        >
                            <svg x-show="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            
                            <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            
                            <span x-text="loading ? 'Signing in...' : 'Sign In'"></span>
                        </button>
                    </form>
                    
                    <div class="mt-6 sm:mt-8 text-center pb-4">
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

        <!-- Forgot Password Modal -->
        <div x-show="forgotPasswordModal" 
             class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 p-4" 
             x-cloak
             @keydown.window.escape="forgotPasswordModal = false"
             style="margin: 0 !important;">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-blue-50">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">Forgot Password</h3>
                            <p class="text-sm text-gray-600">Submit a support request</p>
                        </div>
                    </div>
                    <button @click="forgotPasswordModal = false"
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm text-blue-700">
                                <p class="font-medium mb-1">Need help with your password?</p>
                                <p>Fill out this form and our admin team will contact you shortly to reset your password.</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('support.ticket.submit') }}" 
                          method="POST"
                          @submit.prevent="handleSubmit($event)">
                        @csrf
                        <input type="hidden" name="category" value="forgot_password">

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" 
                                   name="fullname" 
                                   value="{{ old('fullname') }}"
                                   required 
                                   maxlength="100"
                                   :disabled="loading || isDisabled"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
                                   placeholder="Enter your full name">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                            <input type="text" 
                                   name="username" 
                                   value="{{ old('username') }}"
                                   required 
                                   maxlength="50"
                                   :disabled="loading || isDisabled"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
                                   placeholder="Enter your username">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Company <span class="text-red-500">*</span></label>
                            <select name="companycode" 
                                    required
                                    :disabled="loading || isDisabled"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed">
                                <option value="">-- Select Company --</option>
                                <option value="SB">SB - Sungai Budi</option>
                                <option value="TBL1">TBL1 - Tunas Baru Lampung 1</option>
                                <option value="TBL2">TBL2 - Tunas Baru Lampung 2</option>
                                <option value="TBL3">TBL3 - Tunas Baru Lampung Divisi 3</option>
                                <option value="TBL4">TBL4 - TBL TEST</option>
                            </select>
                        </div>

                        <!-- reCAPTCHA v2 Widget -->
                        <div class="mb-4 flex justify-center">
                            <div class="g-recaptcha" 
                                 data-sitekey="6LcXPugrAAAAAJkru9yNj0fIm9S7c_LzJAm6ie6Y"
                                 data-theme="light"
                                 data-size="normal"
                                 data-callback="onRecaptchaSuccess"
                                 data-expired-callback="onRecaptchaExpired"></div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0 mt-6">
                            <button type="button" 
                                    @click="forgotPasswordModal = false"
                                    :disabled="loading"
                                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                                Cancel
                            </button>
                            
                            <button type="submit"
                                    :disabled="loading || isDisabled || !recaptchaVerified"
                                    :class="(loading || isDisabled || !recaptchaVerified) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-700'"
                                    class="w-full sm:w-auto px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150 flex items-center justify-center space-x-2">
                                
                                <svg x-show="loading" 
                                     class="animate-spin w-4 h-4" 
                                     fill="none" 
                                     viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                
                                <svg x-show="!loading && !isDisabled && recaptchaVerified" 
                                     class="w-4 h-4" 
                                     fill="none" 
                                     stroke="currentColor" 
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                
                                <span x-text="getButtonText()"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Forgot Password Modal Data
        function forgotPasswordData() {
            return {
                forgotPasswordModal: false,
                loading: false,
                isDisabled: false,
                recaptchaVerified: false, // ? NEW
                cooldownEnd: null,
                remainingTime: 0,
                countdownInterval: null,
                
                init() {
                    this.checkCooldown();
                    
                    // Setup reCAPTCHA callback
                    window.onRecaptchaSuccess = () => {
                        this.recaptchaVerified = true;
                    };
                    
                    window.onRecaptchaExpired = () => {
                        this.recaptchaVerified = false;
                    };
                },
                
                checkCooldown() {
                    const cooldown = localStorage.getItem('ticket_cooldown');
                    if (cooldown && Date.now() < parseInt(cooldown)) {
                        this.isDisabled = true;
                        this.cooldownEnd = parseInt(cooldown);
                        this.startCountdown();
                    }
                },
                
                startCountdown() {
                    this.updateRemainingTime();
                    
                    this.countdownInterval = setInterval(() => {
                        if (Date.now() >= this.cooldownEnd) {
                            this.isDisabled = false;
                            this.remainingTime = 0;
                            localStorage.removeItem('ticket_cooldown');
                            clearInterval(this.countdownInterval);
                        } else {
                            this.updateRemainingTime();
                        }
                    }, 1000);
                },
                
                updateRemainingTime() {
                    const remaining = Math.ceil((this.cooldownEnd - Date.now()) / 1000);
                    this.remainingTime = remaining > 0 ? remaining : 0;
                },
                
                handleSubmit(e) {
                    if (this.isDisabled) {
                        alert('Please wait for cooldown to finish');
                        return;
                    }
                    
                    if (!this.recaptchaVerified) {
                        alert('Please complete the reCAPTCHA verification');
                        return;
                    }
                    
                    this.loading = true;
                    
                    // Set 10 menit cooldown
                    const cooldownTime = Date.now() + (10 * 60 * 1000);
                    localStorage.setItem('ticket_cooldown', cooldownTime);
                    this.cooldownEnd = cooldownTime;
                    this.isDisabled = true;
                    
                    // Manual submit form
                    e.target.submit();
                },
                
                getButtonText() {
                    if (this.loading) {
                        return 'Submitting...';
                    }
                    if (this.isDisabled && this.remainingTime > 0) {
                        const minutes = Math.floor(this.remainingTime / 60);
                        const seconds = this.remainingTime % 60;
                        return `Wait ${minutes}:${seconds.toString().padStart(2, '0')}`;
                    }
                    return 'Submit Request';
                }
            }
        }
        
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
        
        window.addEventListener('DOMContentLoaded', function() {
            const errorElement = document.querySelector('.bg-red-50');
            if (errorElement) {
                errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>
</body>

</html>