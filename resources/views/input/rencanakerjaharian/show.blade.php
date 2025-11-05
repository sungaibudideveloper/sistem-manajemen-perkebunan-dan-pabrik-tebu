{{--resources\views\input\rencanakerjaharian\show.blade.php--}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <!-- HEADER CONTENT -->
  <div class="bg-gray-50 rounded-lg p-6 mb-8 border border-blue-100">
    <div class="flex flex-col lg:flex-row gap-6">
      <!-- KIRI: No RKH, Status, Mandor, Tanggal, Keterangan -->
      <div class="flex flex-col flex-1 space-y-4">
        <!-- No RKH -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">
            No RKH <span class="text-xs text-gray-500 ml-2">(View Only)</span>
          </label>
          <p class="text-5xl font-mono tracking-wider text-gray-800 font-bold">
            {{ $rkhHeader->rkhno ?? '-' }}
          </p>
        </div>

        <!-- Status Badges -->
        <div class="flex flex-wrap items-center gap-4">
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
            $rkhStatus = $rkhHeader->status === 'Completed' ? 'Completed' : 'On Progress';
            $rkhStatusClass = $rkhHeader->status === 'Completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Mandor</label>
            <div class="w-full text-sm font-medium border-2 border-gray-200 rounded-lg px-4 py-2 bg-gray-50">
              {{ $rkhHeader->mandorid ?? '-' }} – {{ $rkhHeader->mandor_nama ?? '-' }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal</label>
            <div class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 bg-gray-50 text-sm font-medium">
              {{ \Carbon\Carbon::parse($rkhHeader->rkhdate)->format('d/m/Y') }}
            </div>
          </div>
        </div>

        <!-- Keterangan Dokumen -->
        @if($rkhHeader->keterangan)
        <div class="max-w-2xl">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan Dokumen</label>
          <div class="w-full text-sm border-2 border-gray-200 rounded-lg px-4 py-2 bg-gray-50 min-h-[60px]">
            {{ $rkhHeader->keterangan }}
          </div>
        </div>
        @endif
      </div>

      <!-- RIGHT: Cards Section -->
      <div class="flex flex-col space-y-4 lg:w-[400px]">

        <!-- Card 1: Data Absen -->
        <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200">
          <div class="flex items-center justify-between mb-3">
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

        <!-- Card 2: Info Pekerja (NEW SECTION) -->
        <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center">
              <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
              <h3 class="text-sm font-bold text-gray-800">Info Pekerja</h3>
            </div>
            <span class="text-xs text-gray-600">{{ $workersByActivity->count() }} Aktivitas</span>
          </div>

          <div>
            @if($workersByActivity->isEmpty())
              <div class="text-center py-8 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="text-xs">Tidak ada data pekerja</p>
              </div>
            @else
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($workersByActivity as $worker)
                  <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-3 border border-purple-200">

                    <div class="mb-3">
                      <p class="text-sm font-semibold text-purple-900 truncate"
                        title="{{ $worker->activitycode }} - {{ $worker->activityname }}">
                        {{ $worker->activitycode }} - {{ $worker->activityname }}
                      </p>
                    </div>

                    <div class="flex items-end gap-2">
                      <div class="flex-1">
                        <label class="text-[10px] text-gray-600 block mb-0.5">L</label>
                        <div class="w-full text-xs border border-gray-300 rounded px-1.5 py-1 bg-gray-100 text-center font-medium">
                          {{ $worker->jumlahlaki ?? 0 }}
                        </div>
                      </div>

                      <div class="flex-1">
                        <label class="text-[10px] text-gray-600 block mb-0.5">P</label>
                        <div class="w-full text-xs border border-gray-300 rounded px-1.5 py-1 bg-gray-100 text-center font-medium">
                          {{ $worker->jumlahperempuan ?? 0 }}
                        </div>
                      </div>

                      <div class="flex-1">
                        <label class="text-[10px] text-gray-600 block mb-0.5">Tot</label>
                        <div class="w-full text-xs border border-gray-300 rounded px-1.5 py-1 bg-green-100 text-center font-bold">
                          {{ $worker->jumlahtenagakerja ?? 0 }}
                        </div>
                      </div>
                    </div>

                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>

      </div>
    </div>

    <!-- Detail Table -->
    <div class="bg-white mt-6 rounded-xl border border-gray-300 shadow-md">
      <div class="flex justify-between items-center p-6 pb-4">
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

      <div class="overflow-x-auto px-6 pb-6">
        <table class="table-fixed w-full border-collapse bg-white rounded-lg overflow-hidden shadow-sm">
          <colgroup>
            <col style="width: 40px"><!-- No. -->
            <col style="width: 250px"><!-- Aktivitas -->
            <col style="width: 80px"><!-- Blok -->
            <col style="width: 80px"><!-- Plot -->
            <col style="width: 80px"><!-- Info Panen -->
            <col style="width: 80px"><!-- Luas -->
            <col style="width: 120px"><!-- Material -->
            <col style="width: 120px"><!-- Kendaraan -->
          </colgroup>

          <thead class="bg-gradient-to-r from-gray-800 to-gray-700 text-white">
            <tr>
              <th class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">No.</th>
              <th class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Aktivitas</th>
              <th class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Blok</th>
              <th class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Plot</th>
              <th class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Info Panen</th>
              <th class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Luas<br>(ha)</th>
              <th class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Material</th>
              <th class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-center">Alat</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            @forelse ($rkhDetails as $index => $detail)
              <tr class="hover:bg-blue-50 transition-colors">
                <!-- No -->
                <td class="px-3 py-3 text-sm text-center font-medium text-gray-600 bg-gray-50">{{ $index + 1 }}</td>

                <!-- Aktivitas -->
                <td class="px-3 py-3 text-sm">
                  <div class="flex flex-col">
                    <span class="font-medium text-blue-800">{{ $detail->activitycode ?? '-' }}</span>
                    <span class="text-xs text-gray-600">{{ $detail->activityname ?? '-' }}</span>
                  </div>
                </td>

                <!-- Blok -->
                <td class="px-3 py-3 text-sm text-center font-medium">{{ $detail->blok ?? '-' }}</td>

                <!-- Plot -->
                <td class="px-3 py-3 text-sm text-center font-medium">{{ $detail->plot ?? '-' }}</td>

                <!-- Info Panen -->
                <td class="px-3 py-3 text-xs text-center">
                  @if($detail->batchno && $detail->kodestatus)
                    <div class="space-y-1">
                      <!-- Lifecycle Status Badge -->
                      <div class="flex items-center justify-center">
                        <span
                          class="px-2 py-0.5 rounded text-[10px] font-semibold
                          {{ $detail->kodestatus === 'PC' ? 'bg-yellow-100 text-yellow-800' : '' }}
                          {{ $detail->kodestatus === 'RC1' ? 'bg-green-100 text-green-800' : '' }}
                          {{ $detail->kodestatus === 'RC2' ? 'bg-blue-100 text-blue-800' : '' }}
                          {{ $detail->kodestatus === 'RC3' ? 'bg-purple-100 text-purple-800' : '' }}">
                          {{ $detail->kodestatus }}
                        </span>
                      </div>

                      <!-- Batch Info -->
                      <div class="text-gray-700">
                        <span class="font-semibold">Batch:</span>
                        <span>{{ $detail->batchno }}</span>
                      </div>
                    </div>
                  @else
                    <span class="text-gray-400">-</span>
                  @endif
                </td>

                <!-- Luas -->
                <td class="px-3 py-3 text-sm text-right font-medium">
                  {{ $detail->luasarea ? number_format($detail->luasarea, 2) : '-' }}
                </td>

                <!-- Material -->
                <td class="px-3 py-3 text-xs text-center">
                  @if($detail->usingmaterial == 1 && $detail->herbisidagroupname)
                    <div
                      class="bg-green-100 text-green-800 px-2 py-1 rounded-lg cursor-pointer hover:bg-green-200 transition-colors"
                      onclick="openMaterialModal({
                        activitycode: '{{ $detail->activitycode }}',
                        activityname: '{{ addslashes($detail->activityname ?? '') }}',
                        blok: '{{ $detail->blok }}',
                        plot: '{{ $detail->plot }}',
                        luasarea: {{ $detail->luasarea ?? 0 }},
                        herbisidagroupid: {{ $detail->herbisidagroupid ?? 'null' }},
                        herbisidagroupname: '{{ addslashes($detail->herbisidagroupname) }}'
                      })"
                      title="Klik untuk melihat detail material"
                    >
                      <div class="font-semibold">{{ $detail->herbisidagroupname }}</div>
                      <div class="text-[10px] text-green-600 mt-1">
                        (klik untuk detail)
                      </div>
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
                    @if($detail->operatorid)
                      @php
                        $operatorData = collect($operatorsData ?? [])->firstWhere('tenagakerjaid', $detail->operatorid);
                      @endphp

                      @if($operatorData)
                        <div class="bg-green-100 text-green-800 px-2 py-1 rounded-lg">
                          <div class="font-semibold text-xs">{{ $operatorData->nokendaraan ?? 'N/A' }}</div>
                          <div class="text-sm text-green-600">{{ $detail->operator_name }}</div>
                          <div class="text-xs text-gray-600">ID: {{ $detail->operatorid }}</div>

                          @if($detail->usinghelper == 1 && $detail->helperid)
                            <div class="mt-1 bg-purple-100 text-purple-800 px-1 py-0.5 rounded text-sm">
                              + Helper: {{ $detail->helper_name ?? 'ID: ' . $detail->helperid }}
                            </div>
                          @endif
                        </div>
                      @else
                        <div class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-lg">
                          <div class="font-semibold text-sm">{{ $detail->operator_name }}</div>
                          <div class="text-xs">ID: {{ $detail->operatorid }}</div>

                          @if($detail->usinghelper == 1 && $detail->helperid)
                            <div class="mt-1 bg-purple-100 text-purple-800 px-1 py-0.5 rounded text-sm">
                              + Helper: {{ $detail->helper_name ?? 'ID: ' . $detail->helperid }}
                            </div>
                          @endif
                        </div>
                      @endif
                    @else
                      <div class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-lg">
                        <div class="font-semibold text-xs">Perlu Operator</div>
                        <div class="text-[10px]">Alat: Ya</div>
                      </div>
                    @endif
                  @else
                    <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Tidak</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
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
              <td colspan="5" class="px-3 py-3 text-center text-sm font-bold uppercase tracking-wider text-gray-700 bg-gray-100">Total</td>
              <td class="px-3 py-3 text-center text-sm font-bold bg-gray-50">
                {{ number_format($rkhDetails->sum('luasarea'), 2) }} ha
              </td>
              <td colspan="2" class="px-3 py-3 bg-gray-100"></td>
            </tr>
          </tfoot>
          @endif
        </table>
      </div>
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
      onclick="window.location.href = '{{ route('input.rencanakerjaharian.index') }}';"
      class="bg-gray-600 hover:bg-gray-700 text-white px-8 py-3 rounded-lg text-sm font-medium transition-colors flex items-center"
    >
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
      </svg>
      Kembali
    </button>

    <!-- Edit Button (if allowed) -->
    @if($rkhHeader->status !== 'Completed')
      <button
        onclick="window.location.href = '{{ route('input.rencanakerjaharian.edit', $rkhHeader->rkhno) }}';"
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

  <!-- Material Detail Modal -->
  <div x-data="materialShowModal()" x-cloak>
    <div
      x-show="open"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
      style="display: none;"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
    >
      <div
        @click.away="open = false"
        class="bg-white rounded-lg shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col"
      >
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Detail Material</h2>
            <button @click="open = false" type="button" class="text-gray-400 hover:text-gray-600 rounded-full p-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="mt-2 text-sm text-gray-600">
            <p><strong>Aktivitas:</strong> <span x-text="selectedDetail.activitycode"></span> - <span x-text="selectedDetail.activityname"></span></p>
            <p><strong>Lokasi:</strong> <span x-text="selectedDetail.blok"></span> - <span x-text="selectedDetail.plot"></span></p>
            <p><strong>Luas Area:</strong> <span x-text="selectedDetail.luasarea"></span> ha</p>
          </div>
        </div>

        {{-- Content --}}
        <div class="flex-1 overflow-y-auto p-6">
          <!-- Group Information -->
          <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-green-800 mb-1" x-text="selectedDetail.herbisidagroupname"></h3>
            <p class="text-sm text-green-700" x-text="materialList.length > 0 ? materialList[0].description || 'Material yang direncanakan untuk aktivitas ini' : 'Material yang direncanakan untuk aktivitas ini'"></p>
          </div>

          <!-- Loading State -->
          <div x-show="loading" class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
            <span class="ml-2 text-gray-600">Memuat detail material...</span>
          </div>

          <!-- Material List -->
          <div x-show="!loading && materialList.length > 0" class="space-y-4">
            <template x-for="(material, index) in materialList" :key="material.itemcode">
              <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="flex items-center mb-2">
                      <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-800 text-sm font-medium mr-3" x-text="index + 1"></span>
                      <h4 class="font-semibold text-gray-900" x-text="material.itemname"></h4>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm text-gray-600 ml-9">
                      <div>
                        <span class="font-medium">Kode:</span>
                        <span class="font-mono ml-1" x-text="material.itemcode"></span>
                      </div>
                      <div>
                        <span class="font-medium">Dosis per Ha:</span>
                        <span class="ml-1" x-text="`${material.dosageperha} ${material.measure}`"></span>
                      </div>
                      <div>
                        <span class="font-medium">Total Kebutuhan:</span>
                        <span class="ml-1 font-semibold text-green-700" x-text="`${(material.dosageperha * selectedDetail.luasarea).toFixed(2)} ${material.measure}`"></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- No Material State -->
          <div x-show="!loading && materialList.length === 0" class="text-center py-8">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-500">Tidak ada detail material yang ditemukan</p>
          </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 bg-gray-50 border-t flex justify-end">
          <button @click="open = false" type="button" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
            Tutup
          </button>
        </div>
      </div>
    </div>
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

  <script>
    // Global data untuk material modal
    window.herbisidaData = @json($herbisidagroups ?? []);

    // Fungsi Alpine untuk Material Show Modal
    function materialShowModal() {
      return {
        open: false,
        loading: false,
        selectedDetail: {
          activitycode: '',
          activityname: '',
          blok: '',
          plot: '',
          luasarea: 0,
          herbisidagroupid: null,
          herbisidagroupname: ''
        },
        materialList: [],

        showMaterialDetail(detail) {
          this.selectedDetail = {
            activitycode: detail.activitycode || '',
            activityname: detail.activityname || '',
            blok: detail.blok || '',
            plot: detail.plot || '',
            luasarea: parseFloat(detail.luasarea) || 0,
            herbisidagroupid: detail.herbisidagroupid || null,
            herbisidagroupname: detail.herbisidagroupname || ''
          };

          this.open = true;
          this.loadMaterialDetail();
        },

        async loadMaterialDetail() {
          if (!this.selectedDetail.herbisidagroupid || !this.selectedDetail.activitycode) {
            this.materialList = [];
            return;
          }

          this.loading = true;

          try {
            if (window.herbisidaData) {
              const materials = window.herbisidaData.filter(item =>
                item.herbisidagroupid == this.selectedDetail.herbisidagroupid &&
                item.activitycode === this.selectedDetail.activitycode
              );

              this.materialList = materials.map(item => ({
                itemcode: item.itemcode,
                itemname: item.itemname,
                dosageperha: parseFloat(item.dosageperha) || 0,
                measure: item.measure || 'unit',
                description: item.description || ''
              }));
            } else {
              this.materialList = [];
            }
          } catch (error) {
            console.error('Error loading material detail:', error);
            this.materialList = [];
          } finally {
            this.loading = false;
          }
        }
      }
    }

    // Global function untuk membuka modal material
    window.openMaterialModal = function(detail) {
      const modalComponent = document.querySelector('[x-data*="materialShowModal"]');
      if (modalComponent && modalComponent._x_dataStack && modalComponent._x_dataStack[0]) {
        modalComponent._x_dataStack[0].showMaterialDetail(detail);
      }
    };
  </script>

</x-layout>
