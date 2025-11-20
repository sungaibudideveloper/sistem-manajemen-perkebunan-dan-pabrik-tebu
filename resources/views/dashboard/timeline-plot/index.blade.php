<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    
    <style>
        h1{text-align:center;color:#333;}
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

        .total-row td:not(.sticky-h){min-width:70px;}

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
                <a href="?crop=pc&activity={{$activityFilter}}" 
                class="py-2 px-4 border-b-2 font-medium text-sm {{$cropType==='pc'?'border-blue-600 text-blue-600':'border-transparent text-gray-500 hover:text-gray-700'}}">
                 📊 Data PC
                </a>
                
                <a href="?crop=rc&activity={{$activityFilter}}" 
                class="py-2 px-4 border-b-2 font-medium text-sm {{$cropType==='rc'?'border-blue-600 text-blue-600':'border-transparent text-gray-500 hover:text-gray-700'}}">
                 📊 Data RC
                </a>
                
                <a href="?crop=p&activity={{$activityFilter}}" 
                class="py-2 px-4 border-b-2 font-medium text-sm {{$cropType==='p'?'border-blue-600 text-blue-600':'border-transparent text-gray-500 hover:text-gray-700'}}">
                 🌾 Data Panen
                </a>
                
                <button 
                @click="activeTab = activeTab === 'map' ? 'table' : 'map'; activeTab === 'map' && $nextTick(() => initMapIfNeeded())" 
                class="py-2 px-4 border-b-2 font-medium text-sm"
                x-bind:class="activeTab === 'map' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                🗺️ Tampilan Map
                </button>
            
                <div class="ml-auto flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Filter Activity:</label>
                    <select 
                        onchange="window.location.href='?crop={{$cropType}}&activity=' + this.value"
                        class="py-1 px-3 rounded border border-gray-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="all" {{$activityFilter==='all'?'selected':''}}>📋 Semua Activity</option>
                        @foreach($activityMap as $code => $label)
                            <option value="{{$code}}" {{$activityFilter===$code?'selected':''}}>
                                {{$code}} - {{$label}}
                            </option>
                        @endforeach
                    </select>
                    
                    {{-- Display count --}}
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                        {{count($plotHeaders)}} plot
                    </span>
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
                                <th class="sticky-v" colspan="3" style="text-align:center;">
                                    <span style="{{ $isGrouped ? 'text-decoration: underline; text-decoration-color: #fbbf24; text-decoration-thickness: 2px; text-underline-offset: 3px;' : '' }}">
                                        {{ $activitycode }}
                                    </span>
                                    @if($isGrouped)
                                        <span style="color:#fbbf24;font-size:12px;" title="Gabungan dari {{ implode(' + ', $activityGrouping[$activitycode]) }}"></span>
                                    @endif
                                    <br>{{ $label }}<br>
                                    <small style="font-weight:normal;">HA / % / Tanggal</small>
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
                                $totalPercentage = 0;
                                $plotCount = 0;
                                $allDates = [];
                                
                                foreach($activityData as $plot => $activities) {
                                    if($act = $activities->get($activitycode)) {
                                        $totalActivity += $act->total_luas;
                                        $totalPercentage += ($act->avg_percentage ?? 0);
                                        $plotCount++;
                                        if($act->tanggal_terbaru) {
                                            $allDates[] = $act->tanggal_terbaru;
                                        }
                                    }
                                }
                                
                                $grandTotalRealisasi += $totalActivity;
                                $latestDate = !empty($allDates) ? max($allDates) : null;
                                
                                // Calculate average percentage for total
                                $avgPercentage = $plotCount > 0 ? $totalPercentage / $plotCount : 0;
                                $percentageColor = $avgPercentage >= 100 ? '#22c55e' : ($avgPercentage > 0 ? '#f97316' : '#6b7280');
                            @endphp
                        
                                <td style="text-align:right;">{{ $totalActivity > 0 ? number_format($totalActivity, 2) : '-' }}</td>
                                <td style="text-align:right; font-weight:bold; color: {{ $percentageColor }};">
                                    {{ $avgPercentage > 0 ? number_format($avgPercentage, 2) . '%' : '-' }}
                                </td>
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
                                        $percentage = $activity->avg_percentage ?? 0;
                                        $tanggal = $activity->tanggal_terbaru ?? null;
                                        $totalRealisasiPlot += $value;
                                        
                                        $percentageColor = $percentage >= 100 ? '#22c55e' : ($percentage > 0 ? '#f97316' : '#6b7280');
                                    @endphp
                                    
                                    <td style="text-align:right;">{{ $value > 0 ? number_format($value, 2) : '-' }}</td>
                                    <td style="text-align:right; font-weight:600; color: {{ $percentageColor }};">
                                        {{ $value > 0 ? number_format($percentage, 2) . '%' : '-' }}
                                    </td>
                                    <td style="text-align:center;font-size:11px;">
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
                                        // ✅ Hitung rata-rata persentase per activity
                                        $totalPercentage = 0;
                                        $activityCount = 0;
                                        
                                        foreach($activityMap as $activitycode => $label) {
                                            $activity = $activityData->get($plot->plot)?->get($activitycode);
                                            if ($activity) {
                                                $percentage = $activity->avg_percentage ?? 0;
                                                $totalPercentage += $percentage;
                                                $activityCount++;
                                            }
                                        }
                                        
                                        $avgPersen = $activityCount > 0 ? $totalPercentage / $activityCount : 0;
                                    @endphp
                                    {{ number_format($avgPersen, 2) }}%
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
        const plotActivityDetails = @json($plotActivityDetails ?? []);
        
        let map, markers = [], polygons = [];
        
        function initMapIfNeeded() {
            if (window.mapInitialized) return;
            
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: parseFloat(plotHeaders[0]?.centerlatitude || -4.12893), lng: parseFloat(plotHeaders[0]?.centerlongitude || 105.2971) },
                zoom: 13
            });
            
            createMapContent();
            window.mapInitialized = true;
        }
        
        function createMapContent() {
            // Markers
            plotHeaders.forEach(h => {
                const d = plotActivityDetails[h.plot] || {
                    marker_color:'black', 
                    avg_percentage:0, 
                    activities:[], 
                    luas_rkh:0, 
                    total_luas_hasil:0,
                    lifecyclestatus: '-',
                    umur_hari: 0
                };
                const color = d.marker_color === 'green' ? '#22c55e' : d.marker_color === 'orange' ? '#f97316' : '#000';
                
                const marker = new google.maps.Marker({
                    position: {lat: parseFloat(h.centerlatitude), lng: parseFloat(h.centerlongitude)},
                    map: map,
                    icon: {path: google.maps.SymbolPath.CIRCLE, scale: 25, fillColor: color, fillOpacity: 0.8, strokeColor: '#fff', strokeWeight: 3},
                    label: {text: h.plot, color: '#fff', fontSize: '11px', fontWeight: 'bold'}
                });

                let umurText = '-';
                if (d.umur_hari > 0) {
                    umurText = `${d.umur_hari} hari`;
                }

                let acts = '';
        if (d.activities?.length > 0) {
            acts = `<div style="margin-top:10px;border-top:1px solid #ddd;padding-top:10px"><strong>Activities (${d.activities.length}):</strong><div style="max-height:200px;overflow-y:auto;margin-top:5px">`;
            d.activities.forEach((a,i) => {
                const p = parseFloat(a.percentage).toFixed(2);
                const c = p >= 100 ? '#22c55e' : p > 0 ? '#f97316' : '#6b7280';
                
                acts += `<div style="margin:5px 0;padding:8px;background:#f9fafb;border-radius:4px;border-left:3px solid ${c}">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                        <div style="font-weight:600;color:#374151;font-size:11px">${i+1}. ${a.code} - ${a.label}</div>
                        <div style="font-weight:700;color:${c};font-size:12px">${p}%</div>
                    </div>
                    <div style="color:#6b7280;font-size:10px;margin-bottom:4px">
                        Luas: <strong>${parseFloat(a.luas_hasil).toFixed(2)} HA</strong> / ${parseFloat(d.luas_rkh).toFixed(2)} HA
                        ${a.lkh_details && a.lkh_details.length > 0 ? ` <span style="color:#9ca3af">(${a.lkh_details.length} LKH)</span>` : ''}
                    </div>`;
                
                // ✅ Detail LKH
                if (a.lkh_details && a.lkh_details.length > 0) {
                    acts += `<div style="margin-top:6px;padding:6px;background:#fff;border-radius:3px;border:1px solid #e5e7eb">
                        <div style="font-size:9px;color:#6b7280;font-weight:600;margin-bottom:3px">Detail LKH:</div>`;
                    
                    a.lkh_details.forEach((lkh) => {
                        acts += `<div style="font-size:9px;color:#374151;padding:2px 0;display:flex;justify-content:space-between;gap:8px">
                            <span style="flex:1">📄 ${lkh.lkhno}</span>
                            <span style="font-weight:600;white-space:nowrap">${parseFloat(lkh.luas_hasil).toFixed(2)} HA</span>
                            <span style="color:#9ca3af;white-space:nowrap">${new Date(lkh.tanggal).toLocaleDateString('id-ID', {day:'2-digit',month:'short'})}</span>
                        </div>`;
                    });
                    
                    acts += `</div>`;
                }
                
                acts += `<div style="background:#e5e7eb;height:6px;border-radius:3px;overflow:hidden;margin-top:4px">
                        <div style="background:${c};height:100%;width:${Math.min(p,100)}%"></div>
                    </div>
                    ${a.tanggal ? `<div style="color:#9ca3af;font-size:9px;margin-top:3px">📅 Terakhir: ${new Date(a.tanggal).toLocaleDateString('id-ID')}</div>` : ''}
                </div>`;
            });
            acts += '</div></div>';
        } else {
            acts = '<div style="margin-top:10px;padding:8px;background:#f3f4f6;border-radius:4px;color:#6b7280;font-size:11px;text-align:center">Tidak ada data</div>';
        }
                
                const info = new google.maps.InfoWindow({
                    content: `<div style="padding:12px;min-width:280px;max-width:350px">
                        <h3 style="margin:0 0 10px 0;color:#2c3e50;font-size:15px;font-weight:bold;border-bottom:2px solid #e5e7eb;padding-bottom:6px">📍 Plot ${h.plot}</h3>
                        <div style="font-size:11px;color:#6b7280;background:#f9fafb;padding:8px;border-radius:4px;margin-bottom:8px">
                            <div><strong>Luas RKH:</strong> ${parseFloat(d.luas_rkh).toFixed(2)} HA</div>
                            <div><strong>Total Hasil:</strong> ${parseFloat(d.total_luas_hasil).toFixed(2)} HA</div>
                            <div style="margin-top:6px;padding-top:6px;border-top:1px solid #e5e7eb">
                                <strong>Lifecycle:</strong> 
                                <span style="background:#e0f2fe;color:#0369a1;padding:2px 6px;border-radius:3px;font-weight:600">${d.lifecyclestatus}</span>
                            </div>
                            <div><strong>Umur:</strong> <span style="color:#059669;font-weight:600">${umurText}</span></div>
                        </div>
                        <div style="background:linear-gradient(135deg,${color}22,${color}11);padding:10px;border-radius:6px;border:2px solid ${color};margin-bottom:8px;text-align:center">
                            <div style="color:#6b7280;font-size:10px;font-weight:600;text-transform:uppercase;margin-bottom:4px">Progress</div>
                            <div style="color:${color};font-weight:700;font-size:24px">${parseFloat(d.avg_percentage).toFixed(1)}%</div>
                        </div>
                        ${acts}
                    </div>`
                });
                
                marker.addListener('click', () => info.open(map, marker));
                markers.push(marker);
            });
            
            // Polygons
            plotHeaders.forEach(h => {
                const pts = plotData.filter(p => p.plot === h.plot);
                if (pts.length < 3) return;
                
                const colors = {'A':'#2C3E50','B':'#16A085','C':'#D35400','D':'#8E44AD','E':'#27AE60','F':'#2980B9','G':'#C0392B','H':'#F39C12','I':'#34495E','J':'#7F8C8D','K':'#1ABC9C'};
                const color = colors[h.plot.charAt(0)] || '#000';
                
                polygons.push(new google.maps.Polygon({
                    paths: pts.map(p => ({lat: parseFloat(p.latitude), lng: parseFloat(p.longitude)})),
                    strokeColor: color, strokeOpacity: 0.8, strokeWeight: 2,
                    fillColor: color, fillOpacity: 0.12, map: map
                }));
            });
            
            
            // ✅ Zoom fokus ke area plot
            const bounds = new google.maps.LatLngBounds();
            plotHeaders.forEach(h => {
                bounds.extend(new google.maps.LatLng(parseFloat(h.centerlatitude), parseFloat(h.centerlongitude)));
            });

            if (plotHeaders.length > 0) {
                map.fitBounds(bounds);
                
                // ✅ Limit zoom level agar tidak terlalu dekat/jauh
                google.maps.event.addListenerOnce(map, 'bounds_changed', () => {
                    const z = map.getZoom();
                    if (z > 16) map.setZoom(16);  // Tidak terlalu dekat
                    if (z < 12) map.setZoom(12);  // Tidak terlalu jauh
                });
            }


        }
    </script>
    
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCc2vFD26wD5ox_5EwLJhR6U1jcfKibxBQ&callback=initMapIfNeeded"></script>
</x-layout>