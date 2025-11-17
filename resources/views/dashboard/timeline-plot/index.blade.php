<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    
    <style>
        h1{text-align:center;color:#333;margin-bottom:40px;}
        table{width:100%;border-collapse:separate;border-spacing:0;box-shadow:0 3px 8px rgba(0,0,0,.1);animation:fadeIn 0.4s ease-in;}
        th,td{border:1px solid #ddd;padding:6px 8px;font-size:13px;vertical-align:middle;background-clip:padding-box;}
        tbody tr{transition:background-color .2s;}
        tbody tr:hover td{background:#24874a !important;color:white;}
         
        /* ✅ Sticky Vertical */
        .sticky-v{position:sticky;background:#166534;color:#fff;}
        thead tr:first-child .sticky-v{top:0;z-index:16;}
        
        /* ✅ Sticky Horizontal */
        .sticky-h{position:sticky;font-weight:600;}
        thead .sticky-h{background:#166534;color:white;z-index:20;}
        tbody .sticky-h{background:white;color:#333;z-index:9;}

        /* ✅ Minimum width untuk kolom-kolom */
        th.sticky-h.blok{min-width:50px;}
        th.sticky-h:not(.blok){min-width:80px;}  /* Plot */
        th.sticky-v[rowspan="2"]{min-width:70px;}  /* Saldo, Realisasi, % */
        th.sticky-v[colspan="2"]{min-width:140px;}  /* Activity headers */

        /* ✅ Khusus blok */
        .sticky-h.blok{background:#14532d;color:white;text-align:center;}
        tbody .sticky-h.blok{background:#14532d;}
        
        /* ✅ Total row - nempel di bawah header */
        .total-row{position:sticky;top:52px;z-index:15;}
        .total-row td{background:#166534;color:white;font-weight:bold;}
        .total-row .sticky-h{z-index:19;}
        .total-row .sticky-h.blok{background:#14532d;}
        
        tbody td{background:#fff;}
        #map{height:600px;width:100%;}
        @keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
    </style>
    <div class="mx-auto px-6" x-data="{activeTab:'table',map:null,markers:[],polygons:[]}">
        
        <div class="mb-6 border-b border-gray-200">
            <nav class="flex space-x-4">
                <a href="?crop=pc&fill={{$fillFilter}}" 
                   class="py-2 px-4 border-b-2 font-medium text-sm {{$cropType==='pc'?'border-blue-600 text-blue-600':'border-transparent text-gray-500 hover:text-gray-700'}}">
                    📊 Data PC
                </a>
                <a href="?crop=rc&fill={{$fillFilter}}" 
                   class="py-2 px-4 border-b-2 font-medium text-sm {{$cropType==='rc'?'border-blue-600 text-blue-600':'border-transparent text-gray-500 hover:text-gray-700'}}">
                    📊 Data RC
                </a>
                
                <button 
                @click="activeTab = activeTab === 'map' ? 'table' : 'map'; activeTab === 'map' && $nextTick(() => initMapIfNeeded())" 
                class="py-2 px-4 border-b-2 font-medium text-sm"
                x-bind:class="activeTab === 'map' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                🗺️ Tampilan Map
                </button>
            
                <div class="ml-auto flex gap-2">
                    <a href="?crop={{$cropType}}&fill=all" class="py-1 px-3 rounded text-xs font-medium {{$fillFilter==='all'?'bg-green-600 text-white':'bg-gray-200 hover:bg-gray-300'}}">Semua</a>
                    <a href="?crop={{$cropType}}&fill=filled" class="py-1 px-3 rounded text-xs font-medium {{$fillFilter==='filled'?'bg-green-600 text-white':'bg-gray-200 hover:bg-gray-300'}}">Terisi</a>
                    <a href="?crop={{$cropType}}&fill=empty" class="py-1 px-3 rounded text-xs font-medium {{$fillFilter==='empty'?'bg-green-600 text-white':'bg-gray-200 hover:bg-gray-300'}}">Kosong</a>
                </div>
            </nav>
        </div>
        
        <div x-show="activeTab==='table'" x-transition>
            <div style="height:90vh;overflow:auto;">
                <table>
                    <thead>
                        <tr>
                            <th class="sticky-v sticky-h blok" style="left:0;" rowspan="2">Blok</th>
                            <th class="sticky-v sticky-h" style="left:60px;" rowspan="2">Plot</th>
                            <th class="sticky-v" rowspan="2">Saldo<br><small>HA</small></th>
                            
                            {{-- DINAMIS: Loop dari $activityMap --}}
                            @foreach($activityMap as $activitycode => $label)
                                @php
                                    $isGrouped = isset($activityGrouping[$activitycode]);
                                @endphp
                                <th class="sticky-v" colspan="2" style="text-align:center;">
                                    <span style="{{ $isGrouped ? 'text-decoration: underline; text-decoration-color: #fbbf24; text-decoration-thickness: 2px; text-underline-offset: 3px;' : '' }}">
                                        {{ $activitycode }}
                                    </span>
                                    @if($isGrouped)
                                        <span style="color:#fbbf24;font-size:12px;" title="Gabungan dari {{ implode(' + ', $activityGrouping[$activitycode]) }}"></span>
                                    @endif
                                    <br>{{ $label }}<br>
                                    <small style="font-weight:normal;">HA / Tanggal</small>
                                </th>
                            @endforeach
                            
                            {{-- 2 Kolom Terakhir --}}
                            <th class="sticky-v" rowspan="2">Realisasi<br>Tanam<br><small>HA</small></th>
                            <th class="sticky-v" rowspan="2">%</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        @php
                            $blokPlots = $plotHeaders->groupBy(fn($item)=>substr($item->plot,0,1));
                        @endphp
                        
                        {{-- BARIS TOTAL SUMMARY --}}
                        <tr class="total-row">
                            <td class="sticky-v sticky-h blok" style="left:0;">TOTAL</td>
                            <td class="sticky-v sticky-h" style="left:60px;">ALL</td>
                            <td class="sticky-h" style="text-align:right; left:120px;" >{{ number_format($plotHeaders->sum('luasarea'), 2) }}</td>
                            
                            @php
                                $grandTotalRealisasi = 0;
                            @endphp
                            
                            @foreach($activityMap as $activitycode => $label)
                                @php 
                                    $totalActivity = 0;
                                    $allDates = [];
                                    
                                    foreach($activityData as $plot => $activities) {
                                        if($act = $activities->get($activitycode)) {
                                            $totalActivity += $act->total_luas;
                                            if($act->tanggal_terbaru) {
                                                $allDates[] = $act->tanggal_terbaru;
                                            }
                                        }
                                    }
                                    
                                    $grandTotalRealisasi += $totalActivity;
                                    $latestDate = !empty($allDates) ? max($allDates) : null;
                                @endphp
                                
                                <td style="text-align:right;">{{ $totalActivity > 0 ? number_format($totalActivity, 2) : '-' }}</td>
                                <td style="text-align:center;font-size:11px;">
                                    {{ $latestDate ? \Carbon\Carbon::parse($latestDate)->format('d M y') : '-' }}
                                </td>
                            @endforeach
                            
                            {{-- Total Realisasi Tanam --}}
                            <td style="text-align:right;">
                                {{ number_format($grandTotalRealisasi, 2) }}
                            </td>
                            
                            {{-- Total Persentase --}}
                            <td style="text-align:right;">
                                @php
                                    $totalSaldo = $plotHeaders->sum('luasarea');
                                    $persenTotal = $totalSaldo > 0 ? ($grandTotalRealisasi / $totalSaldo) * 100 : 0;
                                @endphp
                                {{ number_format($persenTotal, 2) }}%
                            </td>
                        </tr>
                        
                        {{-- DATA PER PLOT --}}
                        @foreach($blokPlots as $blok=>$plots)
                            @foreach($plots as $index=>$plot)
                                <tr>
                                    @if($index===0)<td rowspan="{{count($plots)}}" class="sticky-h blok" style="left:0;">{{$blok}}</td>@endif
                                    <td class="sticky-h" style="left:60px;">{{$plot->plot}}</td>
                                    <td class="sticky-h" style="left:120px;text-align:right;">{{$plot->luasarea?number_format($plot->luasarea,2):'-'}}</td>
                                    
                                    @php
                                        $totalRealisasiPlot = 0;
                                    @endphp
                                    
                                    @foreach($activityMap as $activitycode => $label)
                                        @php 
                                            $activity = $activityData->get($plot->plot)?->get($activitycode);
                                            $value = $activity->total_luas ?? 0;
                                            $tanggal = $activity->tanggal_terbaru ?? null;
                                            $totalRealisasiPlot += $value;
                                        @endphp
                                        
                                        <td style="text-align:right;">{{ $value > 0 ? number_format($value, 2) : '-' }}</td>
                                        <td style="text-align:right;font-size:11px;">
                                            {{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d M y') : '-' }}
                                        </td>
                                    @endforeach
                                    
                                    {{-- Realisasi Tanam (Total semua activity untuk plot ini) --}}
                                    <td style="text-align:right;">
                                        {{ $totalRealisasiPlot > 0 ? number_format($totalRealisasiPlot, 2) : '-' }}
                                    </td>
                                    
                                    {{-- Persentase --}}
                                    <td style="text-align:right;">
                                        @php
                                            $persen = $plot->luasarea > 0 ? ($totalRealisasiPlot / $plot->luasarea) * 100 : 0;
                                        @endphp
                                        {{ number_format($persen, 2) }}%
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
  
        <div x-show="activeTab==='map'" x-transition class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-xl font-bold mb-4">Peta Lokasi Plot</h3>
            <div id="map" class="border border-gray-300 rounded-lg"></div>
        </div>
  
    </div>
  
    <script>
        const plotHeaders = @json($plotHeadersForMap ?? []);
        const plotData = @json($plotData ?? []);
        
        let map;
        let markers = [];
        let polygons = [];
        
        function initMapIfNeeded() {
            if (window.mapInitialized) return;
            
            const centerLat = plotHeaders.length > 0 ? parseFloat(plotHeaders[0].centerlatitude) : -4.12893;
            const centerLng = plotHeaders.length > 0 ? parseFloat(plotHeaders[0].centerlongitude) : 105.2971;
            
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: centerLat, lng: centerLng },
                zoom: 13,
                mapTypeId: 'roadmap'
            });
            
            createMapContent();
            
            window.mapInitialized = true;
        }
        
        function createMapContent() {
            const blockColorMap = createBlockColorMap();
            
            plotHeaders.forEach(header => {
                const blockLetter = header.plot.charAt(0);
                const blockColor = blockColorMap[blockLetter];
                
                const marker = new google.maps.Marker({
                    position: { 
                        lat: parseFloat(header.centerlatitude), 
                        lng: parseFloat(header.centerlongitude) 
                    },
                    map: map,
                    title: `Plot ${header.plot}`,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 8,
                        fillColor: blockColor,
                        fillOpacity: 0.9,
                        strokeColor: '#2c3e50',
                        strokeWeight: 2
                    },
                    label: {
                        text: header.plot,
                        color: '#2c3e50',
                        fontSize: '11px',
                        fontWeight: '600'
                    }
                });
                
                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div style="padding: 12px; font-family: Arial, sans-serif;">
                            <h3 style="margin: 0 0 8px 0; color: #2c3e50; font-size: 13px;">Plot ${header.plot}</h3>
                            <div style="font-size: 12px; color: #7f8c8d;">
                                <div style="margin: 3px 0;"><strong>Block:</strong> ${blockLetter}</div>
                                <div style="margin: 3px 0;"><strong>Latitude:</strong> ${parseFloat(header.centerlatitude).toFixed(6)}</div>
                                <div style="margin: 3px 0;"><strong>Longitude:</strong> ${parseFloat(header.centerlongitude).toFixed(6)}</div>
                            </div>
                        </div>
                    `
                });
                
                marker.addListener('click', () => {
                    infoWindow.open(map, marker);
                });
                
                markers.push(marker);
            });
            
            plotHeaders.forEach(header => {
                const blockLetter = header.plot.charAt(0);
                const blockColor = blockColorMap[blockLetter];
                
                const gpsPoints = plotData.filter(item => item.plot === header.plot);
                
                if (gpsPoints.length < 3) return;
                
                const polygonCoordinates = gpsPoints.map(item => ({
                    lat: parseFloat(item.latitude),
                    lng: parseFloat(item.longitude)
                }));
                
                const polygon = new google.maps.Polygon({
                    paths: polygonCoordinates,
                    strokeColor: blockColor,
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: blockColor,
                    fillOpacity: 0.12,
                    map: map
                });
                
                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div style="padding: 12px; font-family: Arial, sans-serif;">
                            <h3 style="margin: 0 0 8px 0; color: #2c3e50; font-size: 13px;">Plot ${header.plot}</h3>
                            <div style="font-size: 12px; color: #7f8c8d;">
                                <div style="margin: 3px 0;"><strong>Block:</strong> ${blockLetter}</div>
                                <div style="margin: 3px 0;"><strong>GPS Points:</strong> ${gpsPoints.length}</div>
                                <div style="margin: 3px 0;"><strong>Area Status:</strong> Mapped</div>
                            </div>
                        </div>
                    `
                });
                
                polygon.addListener('click', (e) => {
                    infoWindow.setPosition(e.latLng);
                    infoWindow.open(map);
                });
                
                polygons.push(polygon);
            });
            
            const bounds = new google.maps.LatLngBounds();
            plotData.forEach(point => {
                bounds.extend(new google.maps.LatLng(
                    parseFloat(point.latitude), 
                    parseFloat(point.longitude)
                ));
            });
            map.fitBounds(bounds);
        }
        
        function createBlockColorMap() {
            const blocks = [...new Set(plotHeaders.map(item => item.plot.charAt(0)))].sort();
            const colorMap = {};
            
            const professionalColors = [
                '#2C3E50', '#16A085', '#D35400', '#8E44AD', '#27AE60', '#2980B9',
                '#C0392B', '#F39C12', '#34495E', '#7F8C8D', '#1ABC9C', '#E74C3C'
            ];
            
            blocks.forEach((block, index) => {
                colorMap[block] = professionalColors[index % professionalColors.length];
            });
            
            return colorMap;
        }
    </script>
    
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCc2vFD26wD5ox_5EwLJhR6U1jcfKibxBQ&callback=initMapIfNeeded"></script>
</x-layout>