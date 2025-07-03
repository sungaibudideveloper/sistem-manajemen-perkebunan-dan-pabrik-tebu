<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    
    <!-- Wrapper div untuk layout sidebar + content -->
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <x-sidebar 
            :navigationMenus="$navigationMenus"
            :allSubmenus="$allSubmenus" 
            :userPermissions="$userPermissions"
            :companyName="$companyName"
        />

        <!-- Main Content Area -->
        <main class="flex-1 transition-all duration-300" 
              x-data="homeData()"
              x-init="init()">
            
            <!-- Content Container -->
            <div class="p-6">
                <!-- Modern Hero Section with Elegant Dark Theme -->
                <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-2xl shadow-xl mb-8">
                    <!-- Subtle pattern overlay -->
                    <div class="absolute inset-0 opacity-5">
                        <div class="absolute inset-0 bg-[url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><path d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/></g></g></svg>')] bg-repeat"></div>
                    </div>
                    
                    <!-- Decorative elements -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-500/10 to-purple-500/10 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-gradient-to-tr from-green-500/10 to-blue-500/10 rounded-full blur-3xl"></div>
                    
                    <div class="relative px-6 py-12 sm:px-8 sm:py-16 lg:px-12">
                        <div class="max-w-4xl">
                            <div class="mb-6">
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    <div class="w-2 h-2 bg-emerald-400 rounded-full mr-2 animate-pulse"></div>
                                    System Online
                                </span>
                            </div>
                            
                            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6">
                                Welcome back,
                                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">{{ $user }}</span>
                            </h1>
                            
                            <p class="text-xl text-slate-300 mb-8 max-w-3xl leading-relaxed">
                                Comprehensive sugarcane plantation management system for Sungai Budi. 
                                Monitor growth, analyze data, and optimize operations with intelligent insights.
                            </p>
                            
                            <div class="flex flex-col sm:flex-row gap-4">
                                <a href="{{ route('input.kerjaharian.rencanakerjaharian.index') }}" 
                                   class="inline-flex items-center px-8 py-4 text-base font-semibold rounded-xl text-slate-900 bg-gradient-to-r from-blue-400 to-blue-500 hover:from-blue-500 hover:to-blue-600 transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-[1.02] group">
                                    <svg class="w-5 h-5 mr-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                    Create Planning
                                </a>
                                <a href="{{ route('report.agronomi.index') }}" 
                                   class="inline-flex items-center px-8 py-4 text-base font-semibold rounded-xl text-slate-300 bg-slate-800/50 hover:bg-slate-700/50 transition-all duration-200 border border-slate-700 hover:border-slate-600 backdrop-blur-sm group">
                                    <svg class="w-5 h-5 mr-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modern Features Section -->
                <section class="py-8">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-slate-900 mb-4">Quick Access</h2>
                        <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                            Essential tools for comprehensive sugarcane plantation monitoring and management
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Agronomi Card -->
                        <a href="{{ route('input.agronomi.index') }}" class="group">
                            <div class="relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group-hover:scale-[1.02] border border-slate-200 hover:border-slate-300 h-64">
                                <div class="p-8 h-full flex flex-col">
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                            </svg>
                                        </div>
                                        <div class="text-xs font-medium text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full">Active</div>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-slate-900 mb-3">Agronomi Monitoring</h3>
                                        <p class="text-slate-600 leading-relaxed mb-6">
                                            Monitor plant growth, soil conditions, and weed competition for optimal crop development.
                                        </p>
                                    </div>
                                    
                                    <div class="flex items-center text-slate-700 font-semibold group-hover:text-emerald-600 transition-colors">
                                        <span>Start Monitoring</span>
                                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <!-- HPT Card -->
                        <a href="{{ route('input.hpt.index') }}" class="group">
                            <div class="relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group-hover:scale-[1.02] border border-slate-200 hover:border-slate-300 h-64">
                                <div class="p-8 h-full flex flex-col">
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        </div>
                                        <div class="text-xs font-medium text-orange-600 bg-orange-50 px-3 py-1 rounded-full">Analysis</div>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-slate-900 mb-3">HPT Analysis</h3>
                                        <p class="text-slate-600 leading-relaxed mb-6">
                                            Advanced pest infestation analysis and damage assessment from crop diseases.
                                        </p>
                                    </div>
                                    
                                    <div class="flex items-center text-slate-700 font-semibold group-hover:text-orange-600 transition-colors">
                                        <span>Analyze Pests</span>
                                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <!-- Rencana Kerja Harian Card -->
                        <a href="{{ route('input.kerjaharian.rencanakerjaharian.index') }}" class="group">
                            <div class="relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group-hover:scale-[1.02] border border-slate-200 hover:border-slate-300 h-64">
                                <div class="p-8 h-full flex flex-col">
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                            </svg>
                                        </div>
                                        <div class="text-xs font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Planning</div>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-slate-900 mb-3">Daily Work Plans</h3>
                                        <p class="text-slate-600 leading-relaxed mb-6">
                                            Create and manage daily work plans for efficient plantation operations.
                                        </p>
                                    </div>
                                    
                                    <div class="flex items-center text-slate-700 font-semibold group-hover:text-blue-600 transition-colors">
                                        <span>Create Plan</span>
                                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </section>

                <!-- Modern Stats Section -->
                <section class="py-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold text-slate-900 mb-2">Real-time</div>
                                <div class="text-slate-600">Data Collection</div>
                            </div>
                            
                            <div class="text-center">
                                <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold text-slate-900 mb-2">Accurate</div>
                                <div class="text-slate-600">Monitoring System</div>
                            </div>
                            
                            <div class="text-center">
                                <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold text-slate-900 mb-2">Fast</div>
                                <div class="text-slate-600">Response Time</div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Include Company Modal (Now Auto-shows on first load if needed) -->
    <x-company-modal :companies="$company" />

    <script>
        // Home page Alpine.js component
        function homeData() {
            return {
                sidebarMinimized: false,
                
                init() {
                    // Listen untuk sidebar toggle event
                    window.addEventListener('sidebar-toggle', (e) => {
                        this.sidebarMinimized = e.detail.minimized;
                    });
                    
                    // Set initial state dari store
                    if (Alpine.store('sidebar')) {
                        this.sidebarMinimized = Alpine.store('sidebar').isMinimized;
                    }
                    
                    // Auto-open modal jika showPopup true
                    if ({{ $showPopup ? 'true' : 'false' }}) {
                        this.$nextTick(() => {
                            this.$dispatch('open-company-modal');
                            
                            setTimeout(() => {
                                this.$dispatch('open-company-modal');
                                
                                setTimeout(() => {
                                    document.dispatchEvent(new CustomEvent('open-company-modal'));
                                }, 100);
                            }, 200);
                        });
                    }
                }
            }
        }
    </script>
</x-layout>