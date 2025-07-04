<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <style>
        .header {
            background: linear-gradient(135deg, #4285F4, #34A853);
            color: #b6b9bd;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 300;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .controls {
            padding: 20px;
            background: #b6b9bd;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .info-panel {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .info-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
        }

        .marker-icon {
            background: #EA4335;
        }

        .polyline-icon {
            background: #4285F4;
        }

        .toggle-btn {
            background: linear-gradient(135deg, #34A853, #2E7D32);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 168, 83, 0.3);
        }

        .toggle-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 168, 83, 0.4);
        }

        .toggle-btn:active {
            transform: translateY(0);
        }

        .map-type-controls {
            display: flex;
            gap: 10px;
        }

        .map-type-btn {
            background: white;
            border: 2px solid #ddd;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .map-type-btn.active {
            background: #4285F4;
            color: white;
            border-color: #4285F4;
        }

        .map-type-btn:hover {
            border-color: #4285F4;
        }

        #map {
            height: 600px;
            width: 100%;
        }

        .info-window {
            font-family: 'Segoe UI', sans-serif;
            line-height: 1.6;
            max-width: 300px;
        }

        .info-title {
            font-weight: bold;
            color: #4285F4;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .info-detail {
            color: #666;
            font-size: 0.9rem;
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 600px;
            background: #f8f9fa;
            color: #666;
            font-size: 1.2rem;
        }

        .error {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 600px;
            background: #f8f9fa;
            color: #666;
            font-size: 1.2rem;
            text-align: center;
        }

        .error-note {
            margin-top: 20px;
            padding: 15px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            color: #856404;
            max-width: 500px;
        }

        @media (max-width: 768px) {
            .controls {
                flex-direction: column;
                text-align: center;
            }

            .info-panel {
                justify-content: center;
            }

            .header h1 {
                font-size: 2rem;
            }

            #map {
                height: 500px;
            }
        }

        /* Only minimal styles for the new features */
        .pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }
        .blink { animation: blink 1.5s infinite; }
        @keyframes blink { 0%, 50% { opacity: 1; } 51%, 100% { opacity: 0.4; } }
        .info-metrics { display: flex; gap: 15px; margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 8px; }
        .info-metric { display: flex; align-items: center; gap: 5px; font-size: 0.9rem; }

        /* NEW: Modern Tab Navigation */
        .modern-nav-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .modern-nav-tabs {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-bottom: 2px solid #dee2e6;
            padding: 0;
            margin: 0;
            display: flex;
            border-radius: 0;
        }

        .modern-nav-tabs .nav-link {
            background: none;
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border-radius: 0;
            position: relative;
        }

        .modern-nav-tabs .nav-link:hover {
            background: rgba(66, 133, 244, 0.1);
            color: #4285F4;
        }

        .modern-nav-tabs .nav-link.active {
            background: white;
            color: #4285F4;
            border-bottom: 3px solid #4285F4;
        }

        .modern-nav-tabs .nav-link i {
            font-size: 1.1rem;
        }

        .beta-badge-tab {
            background: #ff4757;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 4px;
        }

        /* NEW: Advanced Analytics Tab */
        .analytics-tab-content {
            padding: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            margin: 20px 0;
        }

        .analytics-showcase {
            text-align: center;
            margin-bottom: 30px;
        }

        .showcase-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .showcase-icon {
            background: rgba(255,255,255,0.2);
            padding: 20px;
            border-radius: 50%;
            font-size: 2rem;
        }

        .showcase-title {
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 10px;
        }

        .showcase-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin: 0;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .feature-card {
            background: rgba(255,255,255,0.1);
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.15);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #ffd700;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .feature-desc {
            opacity: 0.9;
            line-height: 1.6;
        }

        .launch-section {
            text-align: center;
            margin-top: 40px;
        }

        .launch-btn {
            background: linear-gradient(45deg, #ff6b6b, #ee5a52);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(255,107,107,0.4);
            position: relative;
            overflow: hidden;
        }

        .launch-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(255,107,107,0.6);
        }

        .launch-btn:active {
            transform: translateY(0);
        }

        .launch-info {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0.8;
        }

        .beta-notice {
            background: rgba(255,193,7,0.1);
            border: 1px solid rgba(255,193,7,0.3);
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }

        .notice-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notice-icon {
            color: #ffc107;
            font-size: 1.5rem;
        }

        /* NEW: Enhanced Map Legend */
        .map-legend {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .legend-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
            padding: 8px 0;
        }

        .legend-marker {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .legend-marker.red { background: #EA4335; }
        .legend-marker.purple { background: purple; }
        .legend-marker.orange { background: #FFA500; }
        .legend-marker.blue { background: #4285F4; }
        .legend-marker.green { background: #34A853; }

        /* NEW: Modal Enhancement */
        .modal-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            border-left: 4px solid #4285F4;
        }

        .detail-label {
            font-weight: 600;
            color: #666;
            font-size: 0.9rem;
            display: block;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #333;
            font-size: 1rem;
        }

        .detail-value.highlight {
            color: #4285F4;
            font-weight: 600;
        }

        /* NEW: Distance Gap Enhancement */
        .distance-gap {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        /* NEW: Polyline Connection Enhancement */
        .connection-info {
            background: rgba(128,0,128,0.1);
            border: 1px solid rgba(128,0,128,0.3);
            border-radius: 6px;
            padding: 8px;
            margin-top: 8px;
            font-size: 0.85rem;
        }

        /* NEW: Map Container Enhancement */
        .map-container {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .map-wrapper {
            position: relative;
        }

        .map-overlay {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(255,255,255,0.95);
            padding: 10px;
            border-radius: 8px;
            z-index: 1000;
            font-size: 0.9rem;
        }
    </style>
    <div class="container">
        <!-- Dynamic Title showing all blocks -->
        <div class="bg-gradient-to-r from-blue-500 to-green-500 text-white p-4 rounded-lg shadow-lg mb-5">
            <h2 class="text-2xl font-bold text-center mb-2">🌾 Agricultural GPS Monitoring System</h2>
            <div class="text-center">
                <span class="text-lg font-semibold">Available Blocks: </span>
                <span id="blocksList" class="text-xl font-bold tracking-wider">Loading...</span>
            </div>
        </div>

        <!-- NEW: Modern Tab Navigation -->
        <div class="modern-nav-container">
            <nav>
                <div class="modern-nav-tabs nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-overview-tab" data-bs-toggle="tab" data-bs-target="#nav-overview" type="button" role="tab" aria-controls="nav-overview" aria-selected="true">
                        <i class="fas fa-map-marked-alt"></i>
                        Map View
                    </button>
                    <button class="nav-link" id="nav-filters-tab" data-bs-toggle="tab" data-bs-target="#nav-filters" type="button" role="tab" aria-controls="nav-filters" aria-selected="false">
                        <i class="fas fa-filter"></i>
                        Filters & Legend
                    </button>
                    <button class="nav-link" id="nav-analytics-tab" data-bs-toggle="tab" data-bs-target="#nav-analytics" type="button" role="tab" aria-controls="nav-analytics" aria-selected="false">
                        <i class="fas fa-chart-line"></i>
                        Advanced Analytics
                        <span class="beta-badge-tab">BETA</span>
                    </button>
                </div>
            </nav>
            
            <div class="tab-content" id="nav-tabContent">
                <!-- Map View Tab -->
                <div class="tab-pane fade show active" id="nav-overview" role="tabpanel" aria-labelledby="nav-overview-tab">
                    <div class="controls">
                        <div class="info-panel">
                            <div class="flex items-center justify-center w-full">
                                <label for="avatar" class="flex flex-col items-center justify-center w-full max-w-lg border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                                    <form action="{{ route('dashboard.maps.upload') }}" id="frm-submit" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
                                        @csrf
                                        <input id="gpxFile" type="file" name="gpxFile" accept=".gpx" />
                                    </form>
                                </label>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <div class="map-type-controls">
                                <button class="map-type-btn active" onclick="setMapType('roadmap')">Roadmap</button>
                                <button class="map-type-btn" onclick="setMapType('satellite')">Satellite</button>
                                <button class="map-type-btn" onclick="setMapType('hybrid')">Hybrid</button>
                            </div>

                            <button class="toggle-btn" onclick="togglePolyline()">
                                <span id="toggleText">Hide Polygons</span>
                            </button>
                        </div>
                    </div>

                    <div class="map-container">
                        <div class="map-wrapper">
                            <div class="map-overlay">
                                <div style="font-weight: bold; margin-bottom: 5px;">📊 Live Stats</div>
                                <div id="totalBlocks">Total Blocks: Loading...</div>
                                <div id="burnedCount">Burned: Loading...</div>
                                <div id="healthyCount">Healthy: Loading...</div>
                            </div>
                            <div id="map">
                                <div class="loading">
                                    🌍 Loading Agricultural GPS Map...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters & Legend Tab -->
                <div class="tab-pane fade" id="nav-filters" role="tabpanel" aria-labelledby="nav-filters-tab">
                    <!-- Enhanced Filter Section -->
                    <div class="bg-white p-5 rounded-lg shadow-md mb-5">
                        <!-- View Mode Controls -->
                        <div class="flex flex-wrap gap-3 items-center mb-4">
                            <span class="font-semibold text-gray-700">View Mode:</span>
                            <button class="px-4 py-2 rounded-full bg-green-500 text-white font-medium hover:bg-green-600 transition-colors" onclick="setViewMode('combined')" id="viewCombined">📊 Combined</button>
                            <button class="px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-blue-500 hover:text-white transition-colors" onclick="setViewMode('progress')" id="viewProgress">🌱 Progress</button>
                            <button class="px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-blue-500 hover:text-white transition-colors" onclick="setViewMode('infection')" id="viewInfection">🐛 Infection</button>
                            <button class="px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-blue-500 hover:text-white transition-colors" onclick="setViewMode('success')" id="viewSuccess">⭐ Success</button>
                        </div>

                        <!-- Filter Controls -->
                        <div class="flex flex-wrap gap-3 items-center mb-4">
                            <span class="font-semibold text-gray-700">Filter by Health:</span>
                            <button class="px-3 py-1 rounded-full bg-green-100 text-green-700 border-2 border-green-300 hover:bg-green-200 transition-colors" onclick="toggleFilter('healthy')" id="filterHealthy">🟢 Healthy</button>
                            <button class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 border-2 border-yellow-300 hover:bg-yellow-200 transition-colors" onclick="toggleFilter('moderate')" id="filterModerate">🟡 Moderate</button>
                            <button class="px-3 py-1 rounded-full bg-red-100 text-red-700 border-2 border-red-300 hover:bg-red-200 transition-colors" onclick="toggleFilter('high')" id="filterHigh">🔴 High Risk</button>
                            <button class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 border-2 border-gray-300 hover:bg-gray-200 transition-colors" onclick="toggleFilter('burned')" id="filterBurned">🔥 Burned</button>
                        </div>

                        <div class="flex flex-wrap gap-3 items-center mb-4">
                            <span class="font-semibold text-gray-700">Filter by Progress:</span>
                            <button class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200 transition-colors" onclick="toggleFilter('planning')" id="filterPlanning">🌱 Planning</button>
                            <button class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200 transition-colors" onclick="toggleFilter('growing')" id="filterGrowing">🌿 Growing</button>
                            <button class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200 transition-colors" onclick="toggleFilter('harvest')" id="filterHarvest">🌾 Harvest</button>
                        </div>

                        <!-- Enhanced Legend -->
                        <div class="map-legend">
                            <div class="legend-title">
                                <i class="fas fa-map-pin"></i>Agricultural Field Legend
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="font-semibold text-gray-700 text-sm mb-2">Progress Stages</div>
                                    <div class="legend-item">
                                        <div class="legend-marker green">🌱</div>
                                        <span>Planning/Planting (0-33%)</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-marker blue">🌿</div>
                                        <span>Growing (34-66%)</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-marker orange">🌾</div>
                                        <span>Harvest Ready (67-100%)</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-marker red">🔥</div>
                                        <span>Burned (101%+)</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="font-semibold text-gray-700 text-sm mb-2">Infection Levels</div>
                                    <div class="legend-item">
                                        <div class="legend-marker green">🟢</div>
                                        <span>Healthy (0-10%)</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-marker orange">🟡</div>
                                        <span>Moderate (11-30%)</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-marker red">🔴</div>
                                        <span>High Risk (31-100%)</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="font-semibold text-gray-700 text-sm mb-2">Success Rates</div>
                                    <div class="legend-item">
                                        <div class="legend-marker green">⭐</div>
                                        <span>Excellent (90%+)</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-marker blue">✅</div>
                                        <span>Good (70-89%)</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-marker orange">⚠️</div>
                                        <span>Needs Attention (<70%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Analytics Tab -->
                <div class="tab-pane fade" id="nav-analytics" role="tabpanel" aria-labelledby="nav-analytics-tab">
                    <div class="analytics-tab-content">
                        <!-- Feature Showcase Section -->
                        <div class="analytics-showcase">
                            <div class="showcase-header">
                                <div class="showcase-icon">
                                    <i class="fas fa-seedling"></i>
                                </div>
                                <div class="showcase-info">
                                    <h4 class="showcase-title">Advanced Agricultural Analytics</h4>
                                    <p class="showcase-subtitle">
                                        Enhanced field monitoring with real-time crop health analysis and predictive insights
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Feature Grid -->
                            <div class="features-grid">
                                <div class="feature-card">
                                    <div class="feature-icon">
                                        <i class="fas fa-chart-area"></i>
                                    </div>
                                    <h6 class="feature-title">Crop Health Trends</h6>
                                    <p class="feature-desc">Real-time monitoring of infection rates, growth patterns, and yield predictions across all agricultural blocks</p>
                                </div>
                                <div class="feature-card">
                                    <div class="feature-icon">
                                        <i class="fas fa-route"></i>
                                    </div>
                                    <h6 class="feature-title">Harvest Route Optimization</h6>
                                    <p class="feature-desc">AI-powered route planning for efficient harvesting operations and resource allocation</p>
                                </div>
                                <div class="feature-card">
                                    <div class="feature-icon">
                                        <i class="fas fa-thermometer-half"></i>
                                    </div>
                                    <h6 class="feature-title">Weather Integration</h6>
                                    <p class="feature-desc">Combine GPS data with weather patterns for irrigation scheduling and disease prevention</p>
                                </div>
                                <div class="feature-card">
                                    <div class="feature-icon">
                                        <i class="fas fa-brain"></i>
                                    </div>
                                    <h6 class="feature-title">Predictive Analytics</h6>
                                    <p class="feature-desc">Machine learning models to predict crop yields, disease outbreaks, and optimal harvest timing</p>
                                </div>
                            </div>
                            
                            <!-- Launch Button -->
                            <div class="launch-section">
                                <button onclick="launchAdvancedAnalytics()" class="launch-btn">
                                    <i class="fas fa-rocket"></i>
                                    <span>Launch Advanced Analytics</span>
                                </button>
                                
                                <div class="launch-info">
                                    <div class="info-item">
                                        <i class="fas fa-external-link-alt"></i>
                                        <span>Opens in new window</span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-clock"></i>
                                        <span>Best viewed on desktop</span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-database"></i>
                                        <span id="dataPointsCount">Loading data points...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Beta Notice -->
                        <div class="beta-notice">
                            <div class="notice-content">
                                <div class="notice-icon">
                                    <i class="fas fa-flask"></i>
                                </div>
                                <div class="notice-text">
                                    <strong>BETA Feature:</strong> Advanced analytics are currently in development. 
                                    Features include predictive modeling, yield forecasting, and automated alerts for crop health issues.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Modal for Plot Details -->
    <div class="modal fade" id="plotDetailModal" tabindex="-1" role="dialog" aria-labelledby="plotDetailModalLabel">
        <div class="modal-dialog modal-xl" role="document" style="height:100vh; max-height:90vh;">
            <div class="modal-content" style="height:100%; display:flex; flex-direction:column;">
                <div class="modal-header">
                    <h4 class="modal-title" id="plotDetailModalLabel">
                        <i class="fas fa-seedling me-1"></i>Plot Details
                    </h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="flex-grow:1; overflow-y:auto;">
                    <div class="modal-detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Plot Code</span>
                            <span class="detail-value highlight" id="modal-plot-code"></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Block</span>
                            <span class="detail-value" id="modal-block"></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Progress Stage</span>
                            <span class="detail-value" id="modal-progress"></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Infection Rate</span>
                            <span class="detail-value" id="modal-infection"></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Success Rate</span>
                            <span class="detail-value" id="modal-success"></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Coordinates</span>
                            <span class="detail-value" id="modal-coordinates"></span>
                        </div>
                    </div>
                    
                    <div class="map-container">
                        <div class="map-wrapper">
                            <div id="modal-map" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
        // Data GPS Header (Marker)
        document.addEventListener("DOMContentLoaded", () => {
            const fileInput = document.getElementById("gpxFile");
            const dropArea = document.querySelector("label[for='gpxFile']");
            const uploadPlaceholder = document.getElementById("uploadPlaceholder");
            const filePreview = document.getElementById("filePreview");
            const fileNameElement = document.getElementById("fileName");

            const handleFile = (file) => {
                if (file) {
                    uploadPlaceholder?.classList.add("invisible");
                    filePreview?.classList.remove("invisible");
                    if (fileNameElement) fileNameElement.textContent = file.name;
                } else {
                    uploadPlaceholder?.classList.remove("invisible");
                    filePreview?.classList.add("invisible");
                }
            };

            fileInput.addEventListener("change", (e) => handleFile(e.target.files[0]));

            dropArea.addEventListener("dragover", (e) => {
                e.preventDefault();
                dropArea.classList.add("border-green-500", "bg-green-100");
            });

            dropArea.addEventListener("dragleave", () => dropArea.classList.remove("border-green-500",
                "bg-green-100"));

            dropArea.addEventListener("drop", (e) => {
                e.preventDefault();
                dropArea.classList.remove("border-green-500", "bg-green-100");

                const file = e.dataTransfer.files[0];
                if (file) {
                    fileInput.files = e.dataTransfer.files;
                    handleFile(file);
                }
            });
        });

        $('#gpxFile').change(function(){
          $('#frm-submit').submit();
        })

        function validateForm() {
            const file = document.getElementById("gpxFile").files[0];
            if (!file) return alert("No file selected."), false;

            const fileExtension = file.name.split(".").pop().toLowerCase();
            if (file.type !== "application/gpx+xml" && fileExtension !== "gpx")
                return alert("Please upload a valid GPX file."), false;

            if (file.size > 20 * 1024 * 1024)
                return alert("File size exceeds the maximum limit of 20MB."), false;

            return true;
        }

        // Helper function to get progress percentage
        function getProgressPercentage(plotCode) {
            const blockLetter = plotCode.charAt(0);
            
            // Special cases for Q and S = 50%
            if (blockLetter === 'Q' || blockLetter === 'S') {
                return 50;
            }
            
            const blockIndex = blockLetter.charCodeAt(0) - 65;
            return (blockIndex / 22) * 100;
        }

        // Dummy data generation functions based on alphabetical position
        function getDummyProgress(plotCode) {
            const blockLetter = plotCode.charAt(0);
            
            // Special cases for Q and S = 50%
            if (blockLetter === 'Q' || blockLetter === 'S') {
                return 'growing'; // 50% falls in growing category (34-66%)
            }
            
            // Special case for W = burned
            if (blockLetter === 'W') {
                return 'burned';
            }
            
            const blockIndex = blockLetter.charCodeAt(0) - 65; // A=0, B=1, C=2, etc.
            const progressPercentage = (blockIndex / 22) * 100; // W is position 22 (W-A=22)
            
            // Check for 101%+ progress (burned)
            if (progressPercentage > 100) return 'burned';
            if (progressPercentage <= 33) return 'planning';     // 0-33% = Planning
            if (progressPercentage <= 66) return 'growing';      // 34-66% = Growing  
            return 'harvest';                                    // 67-100% = Harvest
        }

        function getDummyInfectionRate(plotCode) {
            const blockLetter = plotCode.charAt(0);
            
            // Special cases for Q and S = 50%
            if (blockLetter === 'Q' || blockLetter === 'S') {
                return 50;
            }
            
            const blockIndex = blockLetter.charCodeAt(0) - 65; // A=0, B=1, C=2, etc.
            
            // A=0%, W=100%, others proportional to position
            // W is at position 22 (since W-A = 22)
            const infectionRate = Math.round((blockIndex / 22) * 100);
            return Math.min(infectionRate, 100);
        }

        function getDummySuccessRate(plotCode) {
            const blockLetter = plotCode.charAt(0);
            
            // Special cases for Q and S = 50%
            if (blockLetter === 'Q' || blockLetter === 'S') {
                return 50;
            }
            
            const blockIndex = blockLetter.charCodeAt(0) - 65; // A=0, B=1, C=2, etc.
            
            // FIXED: Burned plots always have 0% success rate
            if (blockLetter === 'W' || (blockIndex / 22) * 100 > 100) {
                return 0;
            }
            
            // A=100%, W=0%, others inversely proportional to position
            const successRate = Math.round(100 - ((blockIndex / 22) * 100));
            return Math.max(successRate, 0);
        }

        // FIXED: Check if plot is burned based on PROGRESS (not infection rate)
        function isBurned(plotCode) {
            const blockLetter = plotCode.charAt(0);
            // Special case for W = burned
            if (blockLetter === 'W') {
                return true;
            }
            const blockIndex = blockLetter.charCodeAt(0) - 65;
            const progressPercentage = (blockIndex / 22) * 100;
            return progressPercentage > 100; // 101%+ progress = burned
        }

        // Enhanced utility functions
        function getProgressIcon(progress) {
            const icons = {
                'planning': '🌱',
                'growing': '🌿', 
                'harvest': '🌾',
                'burned': '🔥'
            };
            return icons[progress] || '🌱';
        }

        function getInfectionIcon(rate) {
            if (rate <= 10) return '🟢';
            if (rate <= 30) return '🟡';
            return '🔴';
        }

        function getInfectionLevel(rate) {
            // FIXED: Remove burned from infection levels, it's now progress-based
            if (rate <= 10) return 'healthy';
            if (rate <= 30) return 'moderate';
            return 'high';
        }

        function getSuccessIcon(rate) {
            if (rate >= 90) return '⭐';     // 90%+ = Excellent
            if (rate >= 70) return '✅';     // 70-89% = Good
            return '⚠️';                    // <70% = Needs Attention
        }

        function getProgressIconByPercentage(percentage) {
            // Special handling for W block (should be burned even at 100%)
            if (percentage === 100) return '🔥';       // W = 100% = Burned (special case)
            if (percentage > 100) return '🔥';         // 101%+ = Burned
            if (percentage <= 33) return '🌱';        // 0-33% = Planning
            if (percentage <= 66) return '🌿';        // 34-66% = Growing
            return '🌾';                              // 67-99% = Harvest
        }

        // Enhanced header data with dummy values
        const headerData = [
          @foreach( $header as $item )
            { 
                code: '{{ $item->plot }}', 
                lat: {{ $item->centerlatitude }}, 
                lng: {{ $item->centerlongitude }},
                progress: getDummyProgress('{{ $item->plot }}'),
                infection_rate: getDummyInfectionRate('{{ $item->plot }}'),
                success_rate: getDummySuccessRate('{{ $item->plot }}')
            },
          @endforeach
        ];

        // Data GPS List (Polyline)
        const listData = [
          @foreach($list as $item)
            { code: '{{ $item->plot }}', lat: {{ $item->latitude }}, lng: {{ $item->longitude }} },
          @endforeach
        ];

        let map;
        let polyline;
        let markers = [];
        let polygons = [];
        let polylineVisible = true;
        let currentViewMode = 'combined';
        let activeFilters = {
            healthy: true,
            moderate: true,
            high: true,
            planning: true,
            growing: true,
            harvest: true,
            burned: true
        };

        // NEW: Geocoding cache system
        let geocodeCache = {};
        let geocoder;

        // NEW: Advanced info window system
        function getGeocodedAddress(lat, lng, callback) {
            const key = `${lat}_${lng}`;
            
            if (geocodeCache[key]) {
                callback(geocodeCache[key]);
                return;
            }
            
            const latlng = { lat: parseFloat(lat), lng: parseFloat(lng) };
            geocoder.geocode({ location: latlng }, function(results, status) {
                const address = (status === "OK" && results[0]) 
                    ? results[0].formatted_address 
                    : "Address not found";
                
                geocodeCache[key] = address;
                callback(address);
            });
        }

        // NEW: Enhanced marker info window content
        function buildPlotInfoContent(point, address) {
            return `
                <div style="text-align:left; max-width:350px; font-size:13px; word-wrap:break-word;">
                    <div style="font-size:18px; font-weight:bold;">🌾 PLOT ${point.code}</div>
                    <div style="margin-bottom: 10px;"><strong>Location:</strong> ${address}</div>
                    
                    <div style="margin-top: 15px; border-top: 1px solid #ccc; padding-top: 10px;">
                        <div style="margin: 8px 0;font-size:15px;">
                            <strong>Block:</strong> ${point.code.charAt(0)} | 
                            <strong>Plot:</strong> ${point.code}
                        </div>
                        <div style="margin-bottom: 5px;">
                            <strong>Coordinates:</strong> ${point.lat.toFixed(6)}, ${point.lng.toFixed(6)}
                        </div>
                    </div>
                    
                    <div class="info-metrics">
                        <div class="info-metric">
                            ${getProgressIcon(point.progress)} ${point.progress.charAt(0).toUpperCase() + point.progress.slice(1)}
                        </div>
                        <div class="info-metric">
                            ${getInfectionIcon(point.infection_rate)}🐛 ${point.infection_rate}%
                        </div>
                        <div class="info-metric">
                            ${getSuccessIcon(point.success_rate)} ${point.success_rate}%
                        </div>
                    </div>
                    
                    <div style="margin-top: 10px; text-align: center;">
                        <button onclick="openPlotDetail('${point.code}')" 
                                style="background: #4285F4; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
                            View Details
                        </button>
                    </div>
                </div>
            `;
        }

        // NEW: Enhanced marker creation with distance calculations
        function createCustomMarkerIcon(point) {
            const progressIcon = getProgressIcon(point.progress);
            const infectionIcon = getInfectionIcon(point.infection_rate);
            const successIcon = getSuccessIcon(point.success_rate);
            
            let iconText = '';
            let fillColor = '#ffffff';
            let fillPercentage = 0;
            
            // FIXED: Always show burn icon with red fill for burned plots in ALL modes
            if (point.progress === 'burned') {
                iconText = '🔥';
                fillColor = '#EF4444'; // Red for burned
                fillPercentage = 100;  // Full red fill for burned
            } else {
                switch (currentViewMode) {
                    case 'progress':
                        // Special handling for Q and S = 50%
                        const blockLetter = point.code.charAt(0);
                        let progressPercentage;
                        
                        if (blockLetter === 'Q' || blockLetter === 'S') {
                            progressPercentage = 50;
                        } else {
                            const blockIndex = blockLetter.charCodeAt(0) - 65;
                            progressPercentage = (blockIndex / 22) * 100;
                        }
                        
                        iconText = getProgressIconByPercentage(progressPercentage);
                        fillColor = '#10B981'; // Green for progress
                        fillPercentage = progressPercentage;
                        break;
                    case 'infection':
                        iconText = '🐛';
                        fillColor = '#EF4444'; // Red for infection
                        fillPercentage = point.infection_rate;
                        break;
                    case 'success':
                        iconText = getSuccessIcon(point.success_rate);
                        fillColor = '#10B981'; // Green for success
                        fillPercentage = point.success_rate;
                        break;
                    default: // combined
                        iconText = progressIcon;
                        fillPercentage = 0; // No fill for combined mode
                        if (point.infection_rate > 30) fillColor = '#FEF3C7';
                        else if (point.success_rate > 80) fillColor = '#D1FAE5';
                        break;
                }
            }

            let svg = '';
            
            if (currentViewMode === 'combined' && point.progress !== 'burned') {
                // Combined mode - simple background color (except for burned)
                svg = `
                    <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="20" cy="20" r="18" fill="${fillColor}" stroke="#333" stroke-width="2"/>
                        <text x="20" y="26" font-size="16" text-anchor="middle" fill="#333">${iconText}</text>
                    </svg>
                `;
            } else {
                // Progress, Infection, Success modes - percentage fill OR burned with red fill
                svg = `
                    <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="fillGradient${point.code}" x1="0%" y1="100%" x2="0%" y2="0%">
                                <stop offset="0%" style="stop-color:${fillColor};stop-opacity:0.8" />
                                <stop offset="${fillPercentage}%" style="stop-color:${fillColor};stop-opacity:0.8" />
                                <stop offset="${fillPercentage}%" style="stop-color:#ffffff;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#ffffff;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                        <circle cx="20" cy="20" r="18" fill="url(#fillGradient${point.code})" stroke="#333" stroke-width="2"/>
                        <text x="20" y="26" font-size="16" text-anchor="middle" fill="#333">${iconText}</text>
                    </svg>
                `;
            }
            
            return {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
                scaledSize: new google.maps.Size(50, 50),
                anchor: new google.maps.Point(25, 25)
            };
        }

        // NEW: Enhanced marker event handling
        function addMarkerEvents(marker, point, infoWindow) {
            const eventHandler = function() {
                getGeocodedAddress(point.lat, point.lng, function(address) {
                    const content = buildPlotInfoContent(point, address);
                    infoWindow.setContent(content);
                    infoWindow.open(map, marker);
                });
            };
            
            marker.addListener('mouseover', eventHandler);
            marker.addListener('click', eventHandler);
        }

        function shouldShowMarker(point) {
            const infectionLevel = getInfectionLevel(point.infection_rate || 0);
            const progressLevel = point.progress || 'planning';
            
            return activeFilters[infectionLevel] && activeFilters[progressLevel];
        }

        // NEW: Enhanced polyline connections with distance calculations
        function createPolylineConnections() {
            // Clear existing polylines
            polygons.forEach(polygon => {
                if (polygon.polylines) {
                    polygon.polylines.forEach(polyline => polyline.setMap(null));
                }
            });

            // Create new polylines for adjacent plots
            headerData.forEach((point, index) => {
                if (!shouldShowMarker(point)) return;
                
                const adjacentPlots = findAdjacentPlots(point);
                adjacentPlots.forEach(adjacentPoint => {
                    if (shouldShowMarker(adjacentPoint)) {
                        const polyline = new google.maps.Polyline({
                            path: [
                                { lat: point.lat, lng: point.lng },
                                { lat: adjacentPoint.lat, lng: adjacentPoint.lng }
                            ],
                            geodesic: true,
                            strokeColor: getPolylineColor(point, adjacentPoint),
                            strokeOpacity: 0.6,
                            strokeWeight: 2,
                            map: polylineVisible ? map : null
                        });
                        
                        if (!point.polylines) point.polylines = [];
                        point.polylines.push(polyline);
                    }
                });
            });
        }

        // NEW: Find adjacent plots (simplified example)
        function findAdjacentPlots(point) {
            const blockLetter = point.code.charAt(0);
            const plotNumber = parseInt(point.code.slice(1));
            
            const adjacent = [];
            
            // Find plots in same block with consecutive numbers
            headerData.forEach(otherPoint => {
                if (otherPoint.code.charAt(0) === blockLetter && 
                    Math.abs(parseInt(otherPoint.code.slice(1)) - plotNumber) === 1) {
                    adjacent.push(otherPoint);
                }
            });
            
            return adjacent;
        }

        // NEW: Get polyline color based on plot health
        function getPolylineColor(point1, point2) {
            const avgInfection = (point1.infection_rate + point2.infection_rate) / 2;
            if (avgInfection > 50) return '#FF0000'; // Red for high infection
            if (avgInfection > 25) return '#FFA500'; // Orange for moderate
            return '#32CD32'; // Green for healthy
        }

        function updateMarkers() {
            // Clear existing markers
            markers.forEach(marker => marker.setMap(null));
            markers = [];

            const infoWindow = new google.maps.InfoWindow();

            headerData.forEach((point, index) => {
                if (!shouldShowMarker(point)) return;

                const blockLetter = point.code.charAt(0);
                const customIcon = createCustomMarkerIcon(point);

                const marker = new google.maps.Marker({
                    position: { lat: point.lat, lng: point.lng },
                    map: map,
                    title: `${point.code} - ${point.progress} (${point.success_rate}% success)`,
                    icon: customIcon,
                    zIndex: point.progress === 'burned' ? 1000 : (point.infection_rate > 30 ? 500 : 100)
                });

                const content = `
                    <div class="info-window">
                        <div class="info-title">📍 Block ${blockLetter} - ${point.code}</div>
                        <div class="info-detail">
                            <strong>Block:</strong> ${blockLetter}<br>
                            <strong>Plot:</strong> ${point.code}<br>
                            <strong>Latitude:</strong> ${point.lat.toFixed(6)}<br>
                            <strong>Longitude:</strong> ${point.lng.toFixed(6)}
                        </div>
                        <div class="info-metrics">
                            <div class="info-metric">
                                ${getProgressIcon(point.progress)} ${point.progress.charAt(0).toUpperCase() + point.progress.slice(1)}
                            </div>
                            <div class="info-metric">
                                ${getInfectionIcon(point.infection_rate)}🐛 ${point.infection_rate}%
                            </div>
                            <div class="info-metric">
                                ${getSuccessIcon(point.success_rate)} ${point.success_rate}%
                            </div>
                        </div>
                    </div>
                `;

                marker.addListener('click', () => {
                    infoWindow.setContent(content);
                    infoWindow.open(map, marker);
                });

                markers.push(marker);
            });
        }

        // NEW: Live statistics update
        function updateLiveStats() {
            const visiblePlots = headerData.filter(shouldShowMarker);
            const burnedCount = visiblePlots.filter(p => p.progress === 'burned').length;
            const healthyCount = visiblePlots.filter(p => p.infection_rate <= 10).length;
            
            document.getElementById('totalBlocks').textContent = `Total Blocks: ${visiblePlots.length}`;
            document.getElementById('burnedCount').textContent = `Burned: ${burnedCount}`;
            document.getElementById('healthyCount').textContent = `Healthy: ${healthyCount}`;
            document.getElementById('dataPointsCount').textContent = `${headerData.length} plots available`;
        }

        function updatePolygons(blockColorMap) {
            // Clear existing polygons
            polygons.forEach(polygon => polygon.setMap(null));
            polygons = [];

            headerData.forEach((point, index) => {
                if (!shouldShowMarker(point)) return;

                const blockLetter = point.code.charAt(0);
                let blockColor = blockColorMap[blockLetter] || '#FF0000';
                
                // FIXED: Override color to BLACK for burned plots (based on progress)
                if (point.progress === 'burned') {
                    blockColor = '#000000'; // Black for burned plots
                }
                
                const filtered = filterByCodes(point.code);

                if (filtered.length < 3) return;

                const polygonCoordinates = filtered.map(item => ({
                    lat: parseFloat(item.lat),
                    lng: parseFloat(item.lng)
                }));

                let fillOpacity = 0.35;
                if (point.progress === 'burned') fillOpacity = 0.8; // Higher opacity for burned
                else if (point.infection_rate > 30) fillOpacity = 0.6;
                else if (point.infection_rate > 10) fillOpacity = 0.45;

                const polygon = new google.maps.Polygon({
                    paths: polygonCoordinates,
                    strokeColor: blockColor,
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: blockColor,
                    fillOpacity: fillOpacity,
                    map: polylineVisible ? map : null
                });

                const infoWindow = new google.maps.InfoWindow();
                
                polygon.addListener('click', (e) => {
                    const content = `
                        <div class="info-window">
                            <div class="info-title">🏞️ Block ${blockLetter} - Plot ${point.code}</div>
                            <div class="info-detail">
                                <strong>Block:</strong> ${blockLetter}<br>
                                <strong>Plot:</strong> ${point.code}<br>
                                <strong>Points:</strong> ${filtered.length}<br>
                                <strong>Status:</strong> ${point.progress}<br>
                                <strong>Health:</strong> ${point.progress === 'burned' ? 'Burned 🔥' : getInfectionLevel(point.infection_rate)}
                            </div>
                            <div class="info-metrics">
                                <div class="info-metric">
                                    ${getProgressIcon(point.progress)} ${point.progress}
                                </div>
                                <div class="info-metric">
                                    ${getInfectionIcon(point.infection_rate)}🐛 ${point.infection_rate}%
                                </div>
                                <div class="info-metric">
                                    ${getSuccessIcon(point.success_rate)} ${point.success_rate}%
                                </div>
                            </div>
                        </div>
                    `;
                    infoWindow.setContent(content);
                    infoWindow.setPosition(e.latLng);
                    infoWindow.open(map);
                });

                polygons.push(polygon);
            });
        }

        // Enhanced control functions
        function setViewMode(mode) {
            currentViewMode = mode;
            
            // Update button states
            document.querySelectorAll('#viewCombined, #viewProgress, #viewInfection, #viewSuccess').forEach(btn => {
                btn.classList.remove('bg-green-500', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            
            const activeBtn = document.getElementById('view' + mode.charAt(0).toUpperCase() + mode.slice(1));
            if (activeBtn) {
                activeBtn.classList.remove('bg-gray-200', 'text-gray-700');
                activeBtn.classList.add('bg-green-500', 'text-white');
            }
            
            updateMarkers();
            updateBlocksList();
        }

        function updateBlocksList() {
            const blocks = getUniqueBlocks();
            const blocksListElement = document.getElementById('blocksList');
            
            if (blocksListElement) {
                // Create a colorful display of blocks
                const blockElements = blocks.map(block => {
                    const blockData = headerData.find(item => item.code.charAt(0) === block);
                    let statusColor = '#10B981'; // Default green
                    
                    if (blockData) {
                        // FIXED: Check progress for burned status, not infection
                        if (blockData.progress === 'burned') statusColor = '#FF0000'; // Red for burned
                        else if (blockData.infection_rate > 30) statusColor = '#EF4444'; // Red for high infection
                        else if (blockData.infection_rate > 10) statusColor = '#F59E0B'; // Orange for moderate
                    }
                    
                    return `<span style="color: ${statusColor}; font-weight: bold; margin: 0 2px;">${block}</span>`;
                }).join(' • ');
                
                blocksListElement.innerHTML = blockElements;
            }
        }

        function toggleFilter(filterType) {
            activeFilters[filterType] = !activeFilters[filterType];
            
            // Update button state
            const btn = document.getElementById('filter' + filterType.charAt(0).toUpperCase() + filterType.slice(1));
            if (btn) {
                if (activeFilters[filterType]) {
                    btn.style.opacity = '1';
                    btn.style.borderWidth = '2px';
                } else {
                    btn.style.opacity = '0.5';
                    btn.style.borderWidth = '1px';
                }
            }
            
            updateMarkers();
            updatePolygons(createBlockColorMap());
        }

        // Extract unique blocks and create color mapping
        function getUniqueBlocks() {
            const blocks = [...new Set(headerData.map(item => item.code.charAt(0)))];
            return blocks.sort();
        }

        function createBlockColorMap() {
            const blocks = getUniqueBlocks();
            const colors = [
                '#FF0000', '#00FF00', '#0000FF', '#FFFF00', 
                '#FF00FF', '#00FFFF', '#FFA500', '#800080',
                '#FFC0CB', '#A52A2A', '#808080', '#000080',
                '#008000', '#800000', '#808000', '#C0C0C0',
                '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4',
                '#FFEAA7', '#DDA0DD', '#F0E68C'
            ];
            
            const colorMap = {};
            blocks.forEach((block, index) => {
                colorMap[block] = colors[index % colors.length];
            });
            
            return colorMap;
        }

        function filterByCodes(codes) {
            return listData.filter(item => item.code === codes);
        }

        // NEW: Plot detail modal
        function openPlotDetail(plotCode) {
            const plot = headerData.find(p => p.code === plotCode);
            if (!plot) return;
            
            // Populate modal fields
            document.getElementById('modal-plot-code').textContent = plot.code;
            document.getElementById('modal-block').textContent = plot.code.charAt(0);
            document.getElementById('modal-progress').textContent = plot.progress.charAt(0).toUpperCase() + plot.progress.slice(1);
            document.getElementById('modal-infection').textContent = plot.infection_rate + '%';
            document.getElementById('modal-success').textContent = plot.success_rate + '%';
            document.getElementById('modal-coordinates').textContent = `${plot.lat.toFixed(6)}, ${plot.lng.toFixed(6)}`;
            
            // Show modal
            $('#plotDetailModal').modal('show');
            
            // Initialize modal map after modal is shown
            $('#plotDetailModal').on('shown.bs.modal', function() {
                setTimeout(() => initModalMap(plot), 300);
            });
        }

        // NEW: Initialize modal map
        function initModalMap(plot) {
            const modalMapDiv = document.getElementById('modal-map');
            if (!modalMapDiv) return;
            
            const modalMap = new google.maps.Map(modalMapDiv, {
                center: { lat: plot.lat, lng: plot.lng },
                zoom: 15,
                mapTypeId: google.maps.MapTypeId.HYBRID
            });
            
            // Add marker for the plot
            const marker = new google.maps.Marker({
                position: { lat: plot.lat, lng: plot.lng },
                map: modalMap,
                icon: createCustomMarkerIcon(plot),
                title: plot.code
            });
            
            // Add polygon if data exists
            const filtered = filterByCodes(plot.code);
            if (filtered.length >= 3) {
                const polygonCoordinates = filtered.map(item => ({
                    lat: parseFloat(item.lat),
                    lng: parseFloat(item.lng)
                }));
                
                const polygon = new google.maps.Polygon({
                    paths: polygonCoordinates,
                    strokeColor: plot.progress === 'burned' ? '#000000' : '#4285F4',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: plot.progress === 'burned' ? '#000000' : '#4285F4',
                    fillOpacity: 0.35,
                    map: modalMap
                });
                
                // Fit map to show polygon
                const bounds = new google.maps.LatLngBounds();
                polygonCoordinates.forEach(coord => bounds.extend(coord));
                modalMap.fitBounds(bounds);
            }
            
            // Add info window
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="text-align: center;">
                        <h5>${plot.code}</h5>
                        <p>Progress: ${plot.progress}</p>
                        <p>Infection: ${plot.infection_rate}%</p>
                        <p>Success: ${plot.success_rate}%</p>
                    </div>
                `
            });
            
            marker.addListener('click', () => {
                infoWindow.open(modalMap, marker);
            });
        }

        // NEW: Advanced analytics launcher
        function launchAdvancedAnalytics() {
            const btn = document.querySelector('.launch-btn');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Analyzing Data...</span>';
            btn.style.pointerEvents = 'none';
            
            // Simulate processing
            setTimeout(() => {
                // Show analytics summary
                const totalPlots = headerData.length;
                const burnedPlots = headerData.filter(p => p.progress === 'burned').length;
                const healthyPlots = headerData.filter(p => p.infection_rate <= 10).length;
                const avgSuccessRate = headerData.reduce((sum, p) => sum + p.success_rate, 0) / totalPlots;
                
                alert(`📊 Advanced Analytics Summary\n\nTotal Plots: ${totalPlots}\nBurned Plots: ${burnedPlots}\nHealthy Plots: ${healthyPlots}\nAverage Success Rate: ${avgSuccessRate.toFixed(1)}%\n\nFull analytics dashboard coming soon!`);
                
                // Reset button
                btn.innerHTML = originalText;
                btn.style.pointerEvents = 'auto';
            }, 2000);
        }

        function initMap() {
            try {
                // Initialize geocoder
                geocoder = new google.maps.Geocoder();
                
                // Simple map initialization
                map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 10,
                    center: { lat: -6.3088, lng: 106.9456 },
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    gestureHandling: 'greedy'
                });

                const blockColorMap = createBlockColorMap();
                updateMarkers();
                updatePolygons(blockColorMap);
                updateBlocksList();

                // Fit bounds to show all data
                const bounds = new google.maps.LatLngBounds();
                headerData.forEach(point => {
                    bounds.extend(new google.maps.LatLng(point.lat, point.lng));
                });
                map.fitBounds(bounds);

                // Set max zoom
                const currentZoom = map.getZoom();
                map.fitBounds(bounds, { maxZoom: 6 });

                console.log('Agricultural GPS system initialized successfully');

            } catch (error) {
                console.error('Error initializing map:', error);
                document.getElementById('map').innerHTML = `
                    <div class="error">
                        <div>❌ Error loading map</div>
                        <div class="error-note">
                            This demo requires Google Maps API to be properly configured.
                        </div>
                    </div>
                `;
            }
        }

        function togglePolyline() {
            const toggleText = document.getElementById('toggleText');

            if (polylineVisible) {
                polygons.forEach(polygon => polygon.setMap(null));
                toggleText.textContent = 'Show Polygons';
                polylineVisible = false;
            } else {
                polygons.forEach(polygon => polygon.setMap(map));
                toggleText.textContent = 'Hide Polygons';
                polylineVisible = true;
            }
        }

        function setMapType(type) {
            if (!map) return;

            document.querySelectorAll('.map-type-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            switch(type) {
                case 'roadmap':
                    map.setMapTypeId(google.maps.MapTypeId.ROADMAP);
                    break;
                case 'satellite':
                    map.setMapTypeId(google.maps.MapTypeId.SATELLITE);
                    break;
                case 'hybrid':
                    map.setMapTypeId(google.maps.MapTypeId.HYBRID);
                    break;
            }
        }

        // Initialize the map when page loads
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initMap, 1000);
        });

        // NEW: Enhanced distance calculation for polylines
        function calculateDistance(point1, point2) {
            const lat1 = point1.lat;
            const lng1 = point1.lng;
            const lat2 = point2.lat;
            const lng2 = point2.lng;
            
            const R = 6371; // Earth's radius in kilometers
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLng/2) * Math.sin(dLng/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const distance = R * c;
            
            return distance * 1000; // Convert to meters
        }

        // NEW: Enhanced info window with weather integration (placeholder)
        function enhancedInfoWindow(point, address) {
            const weatherInfo = getWeatherInfo(point.lat, point.lng); // Placeholder
            
            return `
                <div style="text-align:left; max-width:400px; font-size:13px;">
                    <div style="font-size:18px; font-weight:bold;">🌾 ${point.code} - Enhanced View</div>
                    <div style="margin-bottom: 10px;"><strong>Location:</strong> ${address}</div>
                    
                    <div style="background: #f0f8ff; padding: 10px; border-radius: 5px; margin: 10px 0;">
                        <div style="font-weight: bold; margin-bottom: 5px;">📊 Agricultural Metrics</div>
                        <div>Progress: ${point.progress} (${getProgressPercentage(point.code).toFixed(1)}%)</div>
                        <div>Health Status: ${getInfectionLevel(point.infection_rate)}</div>
                        <div>Success Rate: ${point.success_rate}%</div>
                    </div>
                    
                    <div style="background: #f0fff0; padding: 10px; border-radius: 5px; margin: 10px 0;">
                        <div style="font-weight: bold; margin-bottom: 5px;">🌤️ Environmental Conditions</div>
                        <div>Soil Moisture: ${weatherInfo.soilMoisture}%</div>
                        <div>Temperature: ${weatherInfo.temperature}°C</div>
                        <div>Humidity: ${weatherInfo.humidity}%</div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 15px;">
                        <button onclick="openPlotDetail('${point.code}')" 
                                style="background: #4285F4; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-right: 10px;">
                            📊 Detailed Analysis
                        </button>
                        <button onclick="scheduleInspection('${point.code}')" 
                                style="background: #34A853; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                            📅 Schedule Inspection
                        </button>
                    </div>
                </div>
            `;
        }

        // NEW: Placeholder functions for future features
        function getWeatherInfo(lat, lng) {
            // Placeholder for weather API integration
            return {
                soilMoisture: Math.floor(Math.random() * 100),
                temperature: Math.floor(Math.random() * 20) + 20,
                humidity: Math.floor(Math.random() * 50) + 40
            };
        }

        function scheduleInspection(plotCode) {
            alert(`🔬 Inspection scheduled for plot ${plotCode}.\n\nThis feature will integrate with your agricultural management system to schedule field inspections and maintenance activities.`);
        }

        // NEW: Keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            if (event.ctrlKey || event.metaKey) {
                switch(event.key) {
                    case '1':
                        event.preventDefault();
                        setViewMode('combined');
                        break;
                    case '2':
                        event.preventDefault();
                        setViewMode('progress');
                        break;
                    case '3':
                        event.preventDefault();
                        setViewMode('infection');
                        break;
                    case '4':
                        event.preventDefault();
                        setViewMode('success');
                        break;
                    case 'h':
                        event.preventDefault();
                        toggleFilter('healthy');
                        break;
                    case 'b':
                        event.preventDefault();
                        toggleFilter('burned');
                        break;
                }
            }
        });

    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCc2vFD26wD5ox_5EwLJhR6U1jcfKibxBQ&callback=initMap&libraries=geometry"></script>
</x-layout>