<x-layout>
  <x-slot:title>{{ $title }} yo</x-slot>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>
  
  <style>
      h1 { text-align: center; color: #333; margin-bottom: 40px; }
      table { width: 100%; border-collapse: collapse; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
      th, td { border: 1px solid #ddd; padding: 8px; font-size: 13px; }
      tr:hover { background-color: #e9f4ff; }
      .status-ontime { color: #28a745; font-weight: bold; }
      .status-late { color: #dc3545; font-weight: bold; }
      .status-early { color: #17a2b8; font-weight: bold; }
      #map { height: 600px; width: 100%; }
      .sticky-col { position: sticky; background: white; z-index: 10; }
      .sticky-col:first-child { left: 0; }
      .sticky-col:nth-child(2) { left: 60px; }
  </style>

  <div class="mx-auto px-6" x-data="{ 
      activeTab: 'table', 
      map: null,
      markers: [],
      polygons: []
  }">
      
      <!-- Tabs Navigation -->
      <div class="mb-6 border-b border-gray-200">
          <nav class="flex space-x-4">
              <button @click="activeTab = 'table'" 
                  :class="activeTab === 'table' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                  class="py-2 px-4 border-b-2 font-medium text-sm">
                  📊 Data Per Plot
              </button>
              <button @click="activeTab = 'map'; $nextTick(() => initMapIfNeeded())" 
                  :class="activeTab === 'map' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                  class="py-2 px-4 border-b-2 font-medium text-sm">
                  🗺️ Tampilan Map
              </button>
          </nav>
      </div>

      <!-- Tab: Table View -->
      <div x-show="activeTab === 'table'" x-transition>
          <div class="overflow-x-auto">
              <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                  <thead class="bg-blue-600 text-white">
                      <tr>
                          <th class="py-2 px-3 border border-gray-300 sticky-col">Blok</th>
                          <th class="py-2 px-3 border border-gray-300 sticky-col">Plot</th>
                          <th class="py-2 px-3 border border-gray-300">Saldo<br><small>HA</small></th>
                          <th class="py-2 px-3 border border-gray-300">Brushing<br><small>HA</small></th>
                          <th class="py-2 px-3 border border-gray-300">Plough I<br><small>HA</small></th>
                          <th class="py-2 px-3 border border-gray-300">Marrow<br><small>HA</small></th>
                          <th class="py-2 px-3 border border-gray-300">Plough II<br><small>HA</small></th>
                          <th class="py-2 px-3 border border-gray-300">Harrow<br><small>HA</small></th>
                          <th class="py-2 px-3 border border-gray-300">Ridger<br><small>HA</small></th>
                          <th class="py-2 px-3 border border-gray-300">Basalt<br><small>HA</small></th>
                          <th class="py-2 px-3 border border-gray-300">Selisih<br><small>HA</small></th>
                          <th class="py-2 px-3 border border-gray-300">Replanting<br><small>HA</small></th>
                          <th class="py-2 px-3 border border-gray-300">Pre Dressing<br><small>HX</small></th>
                          <th class="py-2 px-3 border border-gray-300">Spring<br><small>DISC</small></th>
                          <th class="py-2 px-3 border border-gray-300">Multi<br><small>DISC</small></th>
                          <th class="py-2 px-3 border border-gray-300">Top<br><small>Dressing</small></th>
                      </tr>
                  </thead>
                  <tbody>
                    @php
                        $blokPlots = $plotHeaders->groupBy(fn($item) => substr($item->plot, 0, 1));
                        $activityNames = ['Saldo', 'Brushing', 'Plough I', 'Marrow', 'Plough II', 'Harrow', 'Ridger', 'Basalt', 'Selisih', 'Replanting', 'Pre Dressing', 'Spring', 'Multi', 'Top'];
                    @endphp
                    
                    @foreach($blokPlots as $blok => $plots)
                        @foreach($plots as $index => $plot)
                            <tr class="hover:bg-blue-50">
                                @if($index === 0)
                                    <td rowspan="{{ count($plots) }}" class="py-2 px-3 border border-gray-300 font-bold text-center bg-gray-50 sticky-col">
                                        {{ $blok }}
                                    </td>
                                @endif
                                <td class="py-2 px-3 border border-gray-300 font-semibold sticky-col bg-white">
                                    {{ $plot->plot }}
                                </td>
                                
                                @foreach($activityNames as $actName)
                                    @php
                                        $value = $activityData->get($plot->plot)?->get($actName)?->total_luas ?? 0;
                                        $displayValue = $value > 0 ? number_format($value, 2) : '-';
                                    @endphp
                                    <td class="py-2 px-3 border border-gray-300 text-right">
                                        {{ $displayValue }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
              </table>
          </div>
      </div>

      <!-- Tab: Map View -->
      <div x-show="activeTab === 'map'" x-transition class="bg-white shadow-md rounded-lg p-6">
          <h3 class="text-xl font-bold mb-4">Peta Lokasi Plot</h3>
          <div id="map" class="border border-gray-300 rounded-lg"></div>
      </div>

  </div>

  <script>
    const plotHeaders = @json($plotHeaders ?? []);
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
        
        // 1. Buat MARKERS (1 per plot di center)
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
                        <h3 style="margin: 0 0 8px 0; color: #2c3e50; font-size: 14px;">Plot ${header.plot}</h3>
                        <div style="font-size: 12px; color: #7f8c8d;">
                            <div style="margin: 4px 0;"><strong>Block:</strong> ${blockLetter}</div>
                            <div style="margin: 4px 0;"><strong>Latitude:</strong> ${parseFloat(header.centerlatitude).toFixed(6)}</div>
                            <div style="margin: 4px 0;"><strong>Longitude:</strong> ${parseFloat(header.centerlongitude).toFixed(6)}</div>
                        </div>
                    </div>
                `
            });
            
            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });
            
            markers.push(marker);
        });
        
        // 2. Buat POLYGONS (outline area dari semua GPS points)
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
                        <h3 style="margin: 0 0 8px 0; color: #2c3e50; font-size: 14px;">Plot ${header.plot}</h3>
                        <div style="font-size: 12px; color: #7f8c8d;">
                            <div style="margin: 4px 0;"><strong>Block:</strong> ${blockLetter}</div>
                            <div style="margin: 4px 0;"><strong>GPS Points:</strong> ${gpsPoints.length}</div>
                            <div style="margin: 4px 0;"><strong>Area Status:</strong> Mapped</div>
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