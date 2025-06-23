<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPS Mapping dengan Google Maps</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4285F4, #34A853);
            color: white;
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
            background: #f8f9fa;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗺️ GPS Mapping dengan Google Maps</h1>
            <p>Pemetaan Data GPS dengan Marker Header dan Polyline Detail</p>
        </div>

        <div class="controls">
            <div class="info-panel">
                <div class="info-item">
                    <div class="info-icon marker-icon"></div>
                    <span><strong>1</strong> Marker dari Header</span>
                </div>
                <div class="info-item">
                    <div class="info-icon polyline-icon"></div>
                    <span><strong>21</strong> Titik Polyline</span>
                </div>
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <div class="map-type-controls">
                    <button class="map-type-btn active" onclick="setMapType('roadmap')">Roadmap</button>
                    <button class="map-type-btn" onclick="setMapType('satellite')">Satellite</button>
                    <button class="map-type-btn" onclick="setMapType('hybrid')">Hybrid</button>
                </div>

                <button class="toggle-btn" onclick="togglePolyline()">
                    <span id="toggleText">Sembunyikan Polyline</span>
                </button>
            </div>
        </div>

        <div id="map">
            <div class="error">
                <div>⚠️ Google Maps tidak dapat dimuat dalam environment ini</div>
                <div class="error-note">
                    <strong>Catatan:</strong> Google Maps API memerlukan konfigurasi khusus dan tidak dapat dijalankan langsung dalam artifact ini.
                    Untuk menggunakan Google Maps, Anda perlu:
                    <ul style="text-align: left; margin-top: 10px;">
                        <li>Mendapatkan API key dari Google Cloud Console</li>
                        <li>Mengaktifkan Google Maps JavaScript API</li>
                        <li>Menjalankan kode di server atau hosting web</li>
                    </ul>
                    <p>Sebagai alternatif, versi dengan Leaflet dapat digunakan langsung tanpa konfigurasi tambahan.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Data GPS Header (Marker)
        const headerData = [
            { table: 'TBL1', code: 'AE034', lat: -4.128930493841100, lng: 105.297104824610000 }
        ];

        // Data GPS List (Polyline)
        const listData = [
            { table: 'TBL1', code: 'AE034', lat: -4.128277193385700, lng: 105.297749602920000 },
            { table: 'TBL1', code: 'AE034', lat: -4.128403192813300, lng: 105.297751175240000 },
            { table: 'TBL1', code: 'AE034', lat: -4.128570632765200, lng: 105.297735332540000 },
            { table: 'TBL1', code: 'AE034', lat: -4.128702759705500, lng: 105.297736453940000 },
            { table: 'TBL1', code: 'AE034', lat: -4.128868276609900, lng: 105.297741522390000 },
            { table: 'TBL1', code: 'AE034', lat: -4.129087413385300, lng: 105.297737074790000 },
            { table: 'TBL1', code: 'AE034', lat: -4.129224924188000, lng: 105.297744992540000 },
            { table: 'TBL1', code: 'AE034', lat: -4.129411387183900, lng: 105.297742249160000 },
            { table: 'TBL1', code: 'AE034', lat: -4.129422935619200, lng: 105.297584917860000 },
            { table: 'TBL1', code: 'AE034', lat: -4.129415378676200, lng: 105.297313155120000 },
            { table: 'TBL1', code: 'AE034', lat: -4.129406838926200, lng: 105.297107185610000 },
            { table: 'TBL1', code: 'AE034', lat: -4.129397337501800, lng: 105.296910751790000 },
            { table: 'TBL1', code: 'AE034', lat: -4.129395529277300, lng: 105.296627548650000 },
            { table: 'TBL1', code: 'AE034', lat: -4.129388911838100, lng: 105.296399648050000 },
            { table: 'TBL1', code: 'AE034', lat: -4.129389986097500, lng: 105.296088792910000 },
            { table: 'TBL1', code: 'AE034', lat: -4.129327714345200, lng: 105.296082730520000 },
            { table: 'TBL1', code: 'AE034', lat: -4.128686200695800, lng: 105.296147334010000 },
            { table: 'TBL1', code: 'AE034', lat: -4.128387372823700, lng: 105.296159857780000 },
            { table: 'TBL1', code: 'AE034', lat: -4.128242550558800, lng: 105.296183046810000 },
            { table: 'TBL1', code: 'AE034', lat: -4.128256640881100, lng: 105.296908341250000 },
            { table: 'TBL1', code: 'AE034', lat: -4.128277193385700, lng: 105.297749602920000 }
        ];

        let map;
        let polyline;
        let markers = [];
        let polylineVisible = true;

        // Fungsi untuk inisialisasi Google Maps
        function initMap() {
            try {
                // Center map pada rata-rata koordinat
                const centerLat = (headerData[0].lat + listData[0].lat) / 2;
                const centerLng = (headerData[0].lng + listData[0].lng) / 2;

                map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 16,
                    center: { lat: centerLat, lng: centerLng },
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    styles: [
                        {
                            featureType: "poi",
                            elementType: "labels",
                            stylers: [{ visibility: "on" }]
                        }
                    ]
                });

                // Tambahkan marker untuk header data
                headerData.forEach((point, index) => {
                    const marker = new google.maps.Marker({
                        position: { lat: point.lat, lng: point.lng },
                        map: map,
                        title: `Header Marker - ${point.code}`,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 12,
                            fillColor: '#EA4335',
                            fillOpacity: 1,
                            strokeColor: '#ffffff',
                            strokeWeight: 3
                        }
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div class="info-window">
                                <div class="info-title">📍 Header Marker</div>
                                <div class="info-detail">
                                    <strong>Table:</strong> ${point.table}<br>
                                    <strong>Code:</strong> ${point.code}<br>
                                    <strong>Latitude:</strong> ${point.lat.toFixed(6)}<br>
                                    <strong>Longitude:</strong> ${point.lng.toFixed(6)}
                                </div>
                            </div>
                        `
                    });

                    marker.addListener('click', () => {
                        infoWindow.open(map, marker);
                    });

                    markers.push(marker);
                });

                // Buat polyline dari list data
                const polylineCoordinates = listData.map(point => ({
                    lat: point.lat,
                    lng: point.lng
                }));

                polyline = new google.maps.Polyline({
                    path: polylineCoordinates,
                    geodesic: true,
                    strokeColor: '#4285F4',
                    strokeOpacity: 0.8,
                    strokeWeight: 4
                });

                polyline.setMap(map);

                // Tambahkan marker kecil untuk setiap titik polyline
                listData.forEach((point, index) => {
                    const marker = new google.maps.Marker({
                        position: { lat: point.lat, lng: point.lng },
                        map: map,
                        title: `Polyline Point ${index + 1}`,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 4,
                            fillColor: '#4285F4',
                            fillOpacity: 1,
                            strokeColor: '#ffffff',
                            strokeWeight: 2
                        }
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div class="info-window">
                                <div class="info-title">🔵 Polyline Point ${index + 1}</div>
                                <div class="info-detail">
                                    <strong>Table:</strong> ${point.table}<br>
                                    <strong>Code:</strong> ${point.code}<br>
                                    <strong>Latitude:</strong> ${point.lat.toFixed(6)}<br>
                                    <strong>Longitude:</strong> ${point.lng.toFixed(6)}
                                </div>
                            </div>
                        `
                    });

                    marker.addListener('click', () => {
                        infoWindow.open(map, marker);
                    });

                    markers.push(marker);
                });

                // Fit bounds untuk menampilkan semua data
                const bounds = new google.maps.LatLngBounds();
                [...headerData, ...listData].forEach(point => {
                    bounds.extend(new google.maps.LatLng(point.lat, point.lng));
                });
                map.fitBounds(bounds);

            } catch (error) {
                console.error('Error initializing Google Maps:', error);
                document.getElementById('map').innerHTML = `
                    <div class="error">
                        <div>❌ Error loading Google Maps</div>
                        <div class="error-note">
                            Google Maps API tidak tersedia atau tidak dikonfigurasi dengan benar.
                        </div>
                    </div>
                `;
            }
        }

        // Fungsi toggle polyline
        function togglePolyline() {
            if (!polyline) return;

            const toggleText = document.getElementById('toggleText');

            if (polylineVisible) {
                polyline.setMap(null);
                // Sembunyikan marker polyline
                markers.slice(1).forEach(marker => marker.setMap(null));
                toggleText.textContent = 'Tampilkan Polyline';
                polylineVisible = false;
            } else {
                polyline.setMap(map);
                // Tampilkan marker polyline
                markers.slice(1).forEach(marker => marker.setMap(map));
                toggleText.textContent = 'Sembunyikan Polyline';
                polylineVisible = true;
            }
        }

        // Fungsi untuk mengubah tipe peta
        function setMapType(type) {
            if (!map) return;

            // Update active button
            document.querySelectorAll('.map-type-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            // Set map type
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

    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCc2vFD26wD5ox_5EwLJhR6U1jcfKibxBQ&callback=initMap">
    </script>
</body>
</html>
