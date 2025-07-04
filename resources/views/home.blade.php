<!-- DASHBOARD HOME PAGE -->
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    
    <!-- Wrapper div untuk layout sidebar + content -->
    <div class="flex min-h-screen bg-gray-200">
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
                <!-- Modern Hero Section with Green Theme -->
                <div class="relative overflow-hidden bg-gradient-to-br from-emerald-900 via-green-800 to-emerald-900 rounded-2xl shadow-xl mb-8">
                    <!-- Subtle pattern overlay -->
                    <div class="absolute inset-0 opacity-5">
                        <div class="absolute inset-0 bg-[url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><path d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/></g></g></svg>')] bg-repeat"></div>
                    </div>
                    
                    <!-- Decorative elements -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-emerald-500/10 to-green-500/10 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-gradient-to-tr from-green-500/10 to-emerald-500/10 rounded-full blur-3xl"></div>
                    
                    <div class="relative px-6 py-12 sm:px-8 sm:py-16 lg:px-12">
                        <div class="max-w-4xl">
                            <div class="mb-6">
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-white/10 text-white border border-white/20 backdrop-blur-sm">
                                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                    System Online
                                </span>
                            </div>
                            
                            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6">
                                Welcome back,
                                <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-green-400">{{ $user }}</span>
                            </h1>
                            
                            <p class="text-xl text-emerald-100 mb-8 max-w-3xl leading-relaxed">
                                Comprehensive sugarcane plantation management system for Sungai Budi. 
                                Monitor growth, analyze data, and optimize operations with intelligent insights.
                            </p>
                            
                            <div class="flex flex-col sm:flex-row gap-4">
                                <a href="{{ route('input.kerjaharian.rencanakerjaharian.index') }}" 
                                   class="inline-flex items-center px-8 py-4 text-base font-semibold rounded-xl text-white bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-[1.02] group">
                                    <svg class="w-5 h-5 mr-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                    Create Planning
                                </a>
                                <a href="{{ route('report.agronomi.index') }}" 
                                   class="inline-flex items-center px-8 py-4 text-base font-semibold rounded-xl text-emerald-200 bg-emerald-800/50 hover:bg-emerald-700/50 transition-all duration-200 border border-emerald-600 hover:border-emerald-500 backdrop-blur-sm group">
                                    <svg class="w-5 h-5 mr-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Announcements Section -->
                <section class="py-8 mb-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-emerald-50 to-green-50 border-b border-emerald-100">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                                    </svg>
                                </div>
                                <h2 class="text-xl font-bold text-emerald-900">System Announcements</h2>
                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            <!-- Announcement 1 -->
                            <div class="flex items-start space-x-4 p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="text-lg font-semibold text-emerald-900">System Development Update</h3>
                                        <span class="text-xs text-emerald-600 bg-emerald-100 px-2 py-1 rounded-full">New</span>
                                    </div>
                                    <p class="text-emerald-700 mb-3">
                                        We're continuously improving our sugarcane management system with new features and enhanced performance. Recent updates include improved data visualization and faster report generation.
                                    </p>
                                    <div class="flex items-center text-sm text-emerald-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>2 hours ago</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Announcement 2 -->
                            <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">Maintenance Scheduled</h3>
                                        <span class="text-xs text-gray-600 bg-gray-200 px-2 py-1 rounded-full">Scheduled</span>
                                    </div>
                                    <p class="text-gray-700 mb-3">
                                        Routine system maintenance will be performed this weekend to ensure optimal performance. The system will be briefly unavailable on Saturday, 2:00 AM - 4:00 AM.
                                    </p>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>1 day ago</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Announcement 3 -->
                            <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">Training Session Available</h3>
                                        <span class="text-xs text-gray-600 bg-gray-200 px-2 py-1 rounded-full">Info</span>
                                    </div>
                                    <p class="text-gray-700 mb-3">
                                        New user training sessions are now available for advanced reporting features. Contact your system administrator to schedule a session for your team.
                                    </p>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>3 days ago</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Modern Features Section -->
                <section class="py-8">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Quick Access</h2>
                        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                            Essential tools for comprehensive sugarcane plantation monitoring and management
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Agronomi Card -->
                        <a href="{{ route('input.agronomi.index') }}" class="group">
                            <div class="relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group-hover:scale-[1.02] border border-gray-200 hover:border-emerald-300 h-64">
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
                                        <h3 class="text-xl font-bold text-gray-900 mb-3">Agronomi Monitoring</h3>
                                        <p class="text-gray-600 leading-relaxed mb-6">
                                            Monitor plant growth, soil conditions, and weed competition for optimal crop development.
                                        </p>
                                    </div>
                                    
                                    <div class="flex items-center text-gray-700 font-semibold group-hover:text-emerald-600 transition-colors">
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
                            <div class="relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group-hover:scale-[1.02] border border-gray-200 hover:border-emerald-300 h-64">
                                <div class="p-8 h-full flex flex-col">
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        </div>
                                        <div class="text-xs font-medium text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full">Analysis</div>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-gray-900 mb-3">HPT Analysis</h3>
                                        <p class="text-gray-600 leading-relaxed mb-6">
                                            Advanced pest infestation analysis and damage assessment from crop diseases.
                                        </p>
                                    </div>
                                    
                                    <div class="flex items-center text-gray-700 font-semibold group-hover:text-emerald-600 transition-colors">
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
                            <div class="relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group-hover:scale-[1.02] border border-gray-200 hover:border-emerald-300 h-64">
                                <div class="p-8 h-full flex flex-col">
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                            </svg>
                                        </div>
                                        <div class="text-xs font-medium text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full">Planning</div>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-gray-900 mb-3">Daily Work Plans</h3>
                                        <p class="text-gray-600 leading-relaxed mb-6">
                                            Create and manage daily work plans for efficient plantation operations.
                                        </p>
                                    </div>
                                    
                                    <div class="flex items-center text-gray-700 font-semibold group-hover:text-emerald-600 transition-colors">
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
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold text-gray-900 mb-2">Real-time</div>
                                <div class="text-gray-600">Data Collection</div>
                            </div>
                            
                            <div class="text-center">
                                <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold text-gray-900 mb-2">Accurate</div>
                                <div class="text-gray-600">Monitoring System</div>
                            </div>
                            
                            <div class="text-center">
                                <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold text-gray-900 mb-2">Fast</div>
                                <div class="text-gray-600">Response Time</div>
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