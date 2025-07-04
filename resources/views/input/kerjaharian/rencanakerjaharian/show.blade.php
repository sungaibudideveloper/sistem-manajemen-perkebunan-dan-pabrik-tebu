<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <!-- Header Information -->
  <div class="bg-gray-50 rounded-lg p-6 mb-8 border border-blue-100 shadow-sm">
    <div class="flex justify-between items-start">
      <!-- LEFT: RKH Info -->
      <div class="flex flex-col space-y-6 w-2/3">
        <!-- RKH Number -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            No RKH <span class="text-xs text-gray-500 ml-2">(View Only)</span>
          </label>
          <p class="text-5xl font-mono tracking-wider text-gray-800 font-bold">
            {{ $rkhHeader->rkhno ?? '-' }}
          </p>
        </div>

        <!-- Status Badges -->
        <div class="flex items-center space-x-6">
          @php
            // Status Approval
            $approvalStatus = 'Waiting';
            $approvalClass = 'bg-yellow-100 text-yellow-800';
            $approvalCount = '';
            
            if (isset($rkhHeader->jumlahapproval) && $rkhHeader->jumlahapproval > 0) {
              // Count approved levels
              $approvedCount = 0;
              if ($rkhHeader->approval1flag === '1') $approvedCount++;
              if ($rkhHeader->approval2flag === '1') $approvedCount++;
              if ($rkhHeader->approval3flag === '1') $approvedCount++;
              
              if ($rkhHeader->approval1flag === '0' || $rkhHeader->approval2flag === '0' || $rkhHeader->approval3flag === '0') {
                $approvalStatus = 'Declined';
                $approvalClass = 'bg-red-100 text-red-800';
                // Find which level was declined
                if ($rkhHeader->approval1flag === '0') {
                  $approvalStatus = 'Declined Level 1';
                } elseif ($rkhHeader->approval2flag === '0') {
                  $approvalStatus = 'Declined Level 2';
                } elseif ($rkhHeader->approval3flag === '0') {
                  $approvalStatus = 'Declined Level 3';
                }
              } elseif ($approvedCount === $rkhHeader->jumlahapproval) {
                $approvalStatus = 'Approved';
                $approvalClass = 'bg-green-100 text-green-800';
              } else {
                $approvalCount = "({$approvedCount}/{$rkhHeader->jumlahapproval})";
                $approvalStatus = "Waiting for Approval {$approvalCount}";
              }
            } else {
              $approvalStatus = 'No Approval Required';
              $approvalClass = 'bg-gray-100 text-gray-800';
            }
            
            // Status RKH
            $rkhStatus = $rkhHeader->status === 'Done' ? 'Done' : 'On Progress';
            $rkhStatusClass = $rkhHeader->status === 'Done' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
          @endphp
          
          <div class="flex items-center space-x-2">
            <span class="text-sm font-medium text-gray-600">Approval Status:</span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $approvalClass }}">
              {{ $approvalStatus }}
            </span>
          </div>
          
          <div class="flex items-center space-x-2">
            <span class="text-sm font-medium text-gray-600">RKH Status:</span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $rkhStatusClass }}">
              {{ $rkhStatus }}
            </span>
          </div>
        </div>

        <!-- Mandor & Date Info -->
        <div class="grid grid-cols-2 gap-6 max-w-md">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Mandor</label>
            <div class="w-full text-sm font-medium border-2 border-gray-200 rounded-lg px-4 py-3 bg-gray-50">
              {{ $rkhHeader->mandorid ?? '-' }} – {{ $rkhHeader->mandor_nama ?? '-' }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal</label>
            <div class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 bg-gray-50 text-sm font-medium">
              {{ \Carbon\Carbon::parse($rkhHeader->rkhdate)->format('d/m/Y') }}
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Absen Summary -->
      <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 w-[320px] md:w-[400px] lg:w-[430px]">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center">
            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
            <h3 class="text-sm font-bold text-gray-800">Data Absen</h3>
          </div>
          <div class="text-right">
            <p class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($rkhHeader->rkhdate)->format('d/m/Y') }}</p>
          </div>
        </div>
        <div class="grid grid-cols-3 gap-4 text-center">
          @php
            $absenSummary = collect($absentenagakerja ?? [])->where('mandorid', $rkhHeader->mandorid);
            $lakiCount = $absenSummary->where('gender', 'L')->count();
            $perempuanCount = $absenSummary->where('gender', 'P')->count();
            $totalCount = $lakiCount + $perempuanCount;
          @endphp
          <div class="bg-blue-50 rounded-lg p-3">
            <div class="text-lg font-bold">{{ $lakiCount }}</div>
            <div class="text-xs text-gray-600">Laki-laki</div>
          </div>
          <div class="bg-pink-50 rounded-lg p-3">
            <div class="text-lg font-bold">{{ $perempuanCount }}</div>
            <div class="text-xs text-gray-600">Perempuan</div>
          </div>
          <div class="bg-green-50 rounded-lg p-3">
            <div class="text-lg font-bold">{{ $totalCount }}</div>
            <div class="text-xs text-gray-600">Total</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Detail Table -->
  <div class="bg-white rounded-xl p-6 border border-gray-300 shadow-md">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-bold text-gray-800">Detail Rencana Kerja</h3>
      <div class="flex space-x-2">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
          </svg>
          Print
        </button>
      </div>
    </div>
    
    <div class="overflow-x-auto">
      <table class="table-fixed w-full border-collapse bg-white rounded-lg overflow-hidden shadow-sm">
        <colgroup>
          <col style="width: 40px"><!-- No. -->
          <col style="width: 80px"><!-- Blok -->
          <col style="width: 80px"><!-- Plot -->
          <col style="width: 250px"><!-- Aktivitas -->
          <col style="width: 80px"><!-- Luas -->
          <col style="width: 50px"><!-- L -->
          <col style="width: 50px"><!-- P -->
          <col style="width: 60px"><!-- Total -->
          <col style="width: 80px"><!-- Jenis -->
          <col style="width: 120px"><!-- Material -->
          <col style="width: 80px"><!-- Kendaraan -->
          <col style="width: 200px"><!-- Keterangan -->
        </colgroup>

        <thead class="bg-gradient-to-r from-gray-800 to-gray-700 text-white">
          <tr>
            <th rowspan="2" class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">No.</th>
            <th rowspan="2" class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Blok</th>
            <th rowspan="2" class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Plot</th>
            <th rowspan="2" class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Aktivitas</th>
            <th rowspan="2" class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Luas<br>(ha)</th>
            <th colspan="4" class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Tenaga Kerja</th>
            <th rowspan="2" class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Material</th>
            <th rowspan="2" class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Kendaraan</th>
            <th rowspan="2" class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Keterangan</th>
          </tr>
          <tr class="bg-gray-700">
            <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-center">L</th>
            <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-center">P</th>
            <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-center">Total</th>
            <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-center">Jenis</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
          @forelse ($rkhDetails as $index => $detail)
            <tr class="hover:bg-blue-50 transition-colors">
              <!-- No -->
              <td class="px-3 py-3 text-sm text-center font-medium text-gray-600 bg-gray-50">{{ $index + 1 }}</td>
              
              <!-- Blok -->
              <td class="px-3 py-3 text-sm text-center font-medium">{{ $detail->blok ?? '-' }}</td>
              
              <!-- Plot -->
              <td class="px-3 py-3 text-sm text-center font-medium">{{ $detail->plot ?? '-' }}</td>
              
              <!-- Aktivitas -->
              <td class="px-3 py-3 text-sm">
                <div class="flex flex-col">
                  <span class="font-medium text-blue-800">{{ $detail->activitycode ?? '-' }}</span>
                  <span class="text-xs text-gray-600">{{ $detail->activityname ?? '-' }}</span>
                </div>
              </td>
              
              <!-- Luas -->
              <td class="px-3 py-3 text-sm text-right font-medium">
                {{ $detail->luasarea ? number_format($detail->luasarea, 2) : '-' }}
              </td>
              
              <!-- Tenaga Kerja -->
              <td class="px-3 py-3 text-sm text-center font-medium bg-blue-50">{{ $detail->jumlahlaki ?? 0 }}</td>
              <td class="px-3 py-3 text-sm text-center font-medium bg-pink-50">{{ $detail->jumlahperempuan ?? 0 }}</td>
              <td class="px-3 py-3 text-sm text-center font-bold bg-green-50">{{ ($detail->jumlahlaki ?? 0) + ($detail->jumlahperempuan ?? 0) }}</td>
              
              <!-- Jenis Tenaga Kerja -->
              <td class="px-3 py-3 text-xs text-center">
                @if($detail->jenistenagakerja == 1)
                  <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Harian</span>
                @elseif($detail->jenistenagakerja == 2)
                  <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">Borongan</span>
                @else
                  <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full">-</span>
                @endif
              </td>
              
              <!-- Material -->
              <td class="px-3 py-3 text-xs text-center">
                @if($detail->usingmaterial == 1 && $detail->herbisidagroupname)
                  <div class="bg-green-100 text-green-800 px-2 py-1 rounded-lg">
                    <div class="font-semibold">{{ $detail->herbisidagroupname }}</div>
                  </div>
                @elseif($detail->usingmaterial == 1)
                  <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">Ya</span>
                @else
                  <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Tidak</span>
                @endif
              </td>
              
              <!-- Kendaraan -->
              <td class="px-3 py-3 text-xs text-center">
                @if($detail->usingvehicle == 1)
                  <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full font-medium">Ya</span>
                @else
                  <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Tidak</span>
                @endif
              </td>
              
              <!-- Keterangan -->
              <td class="px-3 py-3 text-sm">
                <div class="max-w-xs truncate" title="{{ $detail->description ?? '' }}">
                  {{ $detail->description ?? '-' }}
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="12" class="px-6 py-8 text-center text-gray-500">
                <div class="flex flex-col items-center">
                  <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  <p>Tidak ada data detail RKH</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>

        @if($rkhDetails->count() > 0)
        <tfoot class="bg-gray-100">
          <tr class="border-t-2 border-gray-200">
            <td colspan="4" class="px-3 py-3 text-center text-sm font-bold uppercase tracking-wider text-gray-700 bg-gray-100">Total</td>
            <td class="px-3 py-3 text-center text-sm font-bold bg-gray-50">
              {{ number_format($rkhDetails->sum('luasarea'), 2) }} ha
            </td>
            <td class="px-3 py-3 text-center text-sm font-bold bg-blue-50">
              {{ $rkhDetails->sum('jumlahlaki') }}
            </td>
            <td class="px-3 py-3 text-center text-sm font-bold bg-pink-50">
              {{ $rkhDetails->sum('jumlahperempuan') }}
            </td>
            <td class="px-3 py-3 text-center text-sm font-bold bg-green-50">
              {{ $rkhDetails->sum('jumlahlaki') + $rkhDetails->sum('jumlahperempuan') }}
            </td>
            <td colspan="4" class="px-3 py-3 bg-gray-100"></td>
          </tr>
        </tfoot>
        @endif
      </table>
    </div>
  </div>

  <!-- Meta Information -->
  <div class="mt-8 bg-gray-50 rounded-lg p-6 border border-gray-200">
    <h4 class="text-sm font-bold text-gray-700 mb-4">Informasi Sistem</h4>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
      @if($rkhHeader->inputby)
        <div>
          <span class="text-gray-600">Dibuat oleh:</span>
          <div class="font-medium">{{ $rkhHeader->inputby }}</div>
        </div>
      @endif
      
      @if($rkhHeader->createdat)
        <div>
          <span class="text-gray-600">Tanggal Dibuat:</span>
          <div class="font-medium">{{ \Carbon\Carbon::parse($rkhHeader->createdat)->format('d/m/Y H:i') }}</div>
        </div>
      @endif
      
      @if($rkhHeader->updateby)
        <div>
          <span class="text-gray-600">Diubah oleh:</span>
          <div class="font-medium">{{ $rkhHeader->updateby }}</div>
        </div>
      @endif
      
      @if($rkhHeader->updatedat)
        <div>
          <span class="text-gray-600">Tanggal Diubah:</span>
          <div class="font-medium">{{ \Carbon\Carbon::parse($rkhHeader->updatedat)->format('d/m/Y H:i') }}</div>
        </div>
      @endif
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="mt-8 flex flex-wrap justify-center gap-4">
    <!-- Back Button -->
    <button
      onclick="window.location.href = '{{ route('input.kerjaharian.rencanakerjaharian.index') }}';"
      class="bg-gray-600 hover:bg-gray-700 text-white px-8 py-3 rounded-lg text-sm font-medium transition-colors flex items-center"
    >
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
      </svg>
      Kembali
    </button>

    <!-- Edit Button (if allowed) -->
    @if($rkhHeader->status !== 'Done')
      <button
        onclick="window.location.href = '{{ route('input.kerjaharian.rencanakerjaharian.edit', $rkhHeader->rkhno) }}';"
        class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg text-sm font-medium transition-colors flex items-center"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        Edit RKH
      </button>
    @endif

    <!-- Print Button -->
    <button
      onclick="window.print()"
      class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg text-sm font-medium transition-colors flex items-center"
    >
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
      </svg>
      Print
    </button>

    <!-- Export Button -->
    <button
      class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-3 rounded-lg text-sm font-medium transition-colors flex items-center"
    >
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      Export
    </button>
  </div>

  <!-- Print Styles -->
  <style>
    @media print {
      body * {
        visibility: hidden;
      }
      .print-area, .print-area * {
        visibility: visible;
      }
      .print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }
      .no-print {
        display: none !important;
      }
      
      /* Print specific styling */
      table {
        page-break-inside: auto;
      }
      tr {
        page-break-inside: avoid;
        page-break-after: auto;
      }
      thead {
        display: table-header-group;
      }
      tfoot {
        display: table-footer-group;
      }
    }
  </style>

</x-layout>