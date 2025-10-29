{{-- resources/views/input/rkh-panen/edit-hasil.blade.php --}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div class="bg-white rounded-lg shadow-md p-6" x-data="hasilPanenForm()" x-init="init()">
    
    <!-- Header -->
    <div class="flex justify-between items-start mb-6">
      <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Input Hasil Panen</h2>
        <div class="flex items-center gap-4 text-sm text-gray-600">
          <span class="font-semibold">{{ $rkhPanen->rkhpanenno }}</span>
          <span>•</span>
          <span>{{ \Carbon\Carbon::parse($rkhPanen->rkhdate)->format('d F Y') }}</span>
          <span>•</span>
          <span>Mandor: <strong>{{ $rkhPanen->mandor->name ?? '-' }}</strong></span>
        </div>
      </div>
      
      <a href="{{ route('input.rkh-panen.show', $rkhPanen->rkhpanenno) }}" 
         class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
        Kembali
      </a>
    </div>

    <!-- Form -->
    <form @submit.prevent="submitForm" id="hasilForm">
      @csrf
      @method('PUT')

      <!-- Table -->
      <div class="overflow-x-auto border border-gray-200 rounded-lg mb-6">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-800 text-white">
            <tr>
              <th class="px-4 py-3 text-left font-semibold" rowspan="2">No</th>
              <th class="px-4 py-3 text-left font-semibold" rowspan="2">Blok</th>
              <th class="px-4 py-3 text-left font-semibold" rowspan="2">Plot</th>
              <th class="px-4 py-3 text-center font-semibold" rowspan="2">Status</th>
              <th class="px-4 py-3 text-center font-semibold" rowspan="2">Hari</th>
              <th class="px-4 py-3 text-right font-semibold" rowspan="2">Luas (Ha)</th>
              <th class="px-4 py-3 text-right font-semibold" rowspan="2">STC (Ha)</th>
              <th class="px-4 py-3 text-right font-semibold" rowspan="2">HC (Ha)</th>
              <th class="px-4 py-3 text-right font-semibold" rowspan="2">BC (Ha)</th>
              <th class="px-4 py-3 text-center font-semibold border-l-2 border-gray-600" colspan="2">Field Balance</th>
              <th class="px-4 py-3 text-center font-semibold" rowspan="2">Premium</th>
              <th class="px-4 py-3 text-left font-semibold" rowspan="2">Keterangan</th>
            </tr>
            <tr class="bg-gray-700">
              <th class="px-4 py-2 text-center text-xs font-medium border-l-2 border-gray-600">Rit</th>
              <th class="px-4 py-2 text-center text-xs font-medium">Ton</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($hasilRows as $index => $row)
            <tr class="hover:bg-gray-50" x-data="hasilRow({{ $index }}, {{ $row->stc ?? 0 }})">
              <input type="hidden" name="hasil[{{ $index }}][plot]" value="{{ $row->plot }}">
              
              <td class="px-4 py-3 text-gray-700 font-medium">{{ $index + 1 }}</td>
              <td class="px-4 py-3 text-gray-900 font-semibold">{{ $row->blok }}</td>
              <td class="px-4 py-3 text-gray-900 font-semibold">{{ $row->plot }}</td>
              
              <td class="px-4 py-3 text-center">
                <span class="px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                  {{ $row->kodestatus }}
                </span>
              </td>
              
              <td class="px-4 py-3 text-center text-gray-700 font-medium">{{ $row->haritebang }}</td>
              <td class="px-4 py-3 text-right text-gray-700">{{ number_format($row->luasplot ?? 0, 2) }}</td>
              
              <!-- STC (Read-only) -->
              <td class="px-4 py-3 text-right">
                <input type="text" 
                       :value="stc.toFixed(2)"
                       readonly
                       class="w-24 text-right bg-gray-100 border border-gray-300 rounded px-2 py-1.5 text-sm font-semibold text-orange-700 cursor-not-allowed">
              </td>
              
              <!-- HC (Required, Empty by default) -->
              <td class="px-4 py-3 text-right">
                <input type="number" 
                       name="hasil[{{ $index }}][hc]" 
                       x-model.number="hc"
                       @input="onHcChange()"
                       step="0.01"
                       min="0.01"
                       :max="stc"
                       required
                       @if($row->hc > 0) value="{{ $row->hc }}" @endif
                       class="w-24 text-right border border-gray-300 rounded px-2 py-1.5 text-sm font-semibold text-green-700 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       placeholder="">
              </td>
              
              <!-- BC (Auto-calculated) -->
              <td class="px-4 py-3 text-right">
                <input type="text" 
                       :value="bc.toFixed(2)"
                       readonly
                       class="w-24 text-right bg-gray-100 border border-gray-300 rounded px-2 py-1.5 text-sm font-semibold text-gray-700 cursor-not-allowed">
              </td>
              
              <!-- FB Rit -->
              <td class="px-4 py-3 text-center border-l-2 border-gray-200">
                <select name="hasil[{{ $index }}][fbrit]" 
                        x-model.number="fbrit"
                        @change="onFbRitChange()"
                        class="w-20 text-center border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                  @for($i = 0; $i <= 20; $i++)
                    <option value="{{ $i }}" {{ old('hasil.'.$index.'.fbrit', $row->fbrit ?? 0) == $i ? 'selected' : '' }}>{{ $i }}</option>
                  @endfor
                </select>
              </td>
              
              <!-- FB Ton (Editable) -->
              <td class="px-4 py-3 text-right">
                <input type="number" 
                       name="hasil[{{ $index }}][fbton]" 
                       x-model.number="fbton"
                       @input="$dispatch('update-totals')"
                       step="0.01"
                       min="0"
                       class="w-24 text-right border border-blue-300 rounded px-2 py-1.5 text-sm font-semibold text-blue-700 focus:ring-2 focus:ring-blue-500"
                       placeholder="0.00">
              </td>
              
              <!-- Premium -->
              <td class="px-4 py-3 text-center">
                <input type="checkbox" 
                       name="hasil[{{ $index }}][ispremium]" 
                       value="1"
                       {{ old('hasil.'.$index.'.ispremium', $row->ispremium) ? 'checked' : '' }}
                       class="w-5 h-5 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500">
              </td>
              
              <!-- Keterangan -->
              <td class="px-4 py-3">
                <input type="text" 
                       name="hasil[{{ $index }}][keterangan]" 
                       value="{{ old('hasil.'.$index.'.keterangan', $row->keterangan) }}"
                       maxlength="100"
                       class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500"
                       placeholder="-">
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="13" class="px-4 py-8 text-center text-gray-500">Tidak ada plot untuk di-input hasilnya</td>
            </tr>
            @endforelse
          </tbody>

          <!-- Total -->
          @if($hasilRows->count() > 0)
          <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-300">
            <tr>
              <td colspan="5" class="px-4 py-3 text-right text-gray-800">TOTAL:</td>
              <td class="px-4 py-3 text-right text-gray-800">{{ number_format($hasilRows->sum('luasplot'), 2) }}</td>
              <td class="px-4 py-3 text-right text-orange-700" x-text="totalSTC">0.00</td>
              <td class="px-4 py-3 text-right text-green-700" x-text="totalHC">0.00</td>
              <td class="px-4 py-3 text-right text-gray-800" x-text="totalBC">0.00</td>
              <td class="px-4 py-3 text-center text-gray-800 border-l-2 border-gray-200" x-text="totalFbRit">0</td>
              <td class="px-4 py-3 text-right text-blue-700" x-text="totalFbTon">0.00</td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
          @endif
        </table>
      </div>

      <!-- Legend -->
      <div class="bg-gray-50 rounded-lg p-4 mb-6 text-xs text-gray-700 leading-relaxed">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
          <div><strong>STC (Standing Cane):</strong> Tebu yang masih berdiri di lahan, belum dipanen</div>
          <div><strong>HC (Hectare Cutting):</strong> Luas area yang dipanen pada hari ini</div>
          <div><strong>BC (Balance Cutting):</strong> Sisa luas yang belum dipanen (STC - HC)</div>
          <div><strong>FB (Field Balance):</strong> Sisa tebu di lapangan yang belum diangkut ke pabrik</div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-3">
        <a href="{{ route('input.rkh-panen.show', $rkhPanen->rkhpanenno) }}" 
           class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2.5 rounded-lg font-medium">
          Batal
        </a>
        
        <button type="submit" 
                :disabled="isSubmitting"
                :class="isSubmitting ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-700'"
                class="bg-green-600 text-white px-6 py-2.5 rounded-lg font-medium">
          <span x-show="!isSubmitting">Simpan</span>
          <span x-show="isSubmitting" class="flex items-center gap-2">
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Menyimpan...
          </span>
        </button>
      </div>
    </form>

  </div>

  <script>
    function hasilPanenForm() {
      return {
        isSubmitting: false,
        totalSTC: '0.00',
        totalHC: '0.00',
        totalBC: '0.00',
        totalFbRit: 0,
        totalFbTon: '0.00',

        init() {
          // Listen untuk update dari child rows
          this.$el.addEventListener('update-totals', () => this.updateTotals());
          
          // Initial calculation setelah semua row ter-render
          this.$nextTick(() => {
            setTimeout(() => this.updateTotals(), 100);
          });
        },

        updateTotals() {
          let stc = 0, hc = 0, bc = 0, rit = 0, ton = 0;
          
          // Loop semua row yang punya Alpine data
          document.querySelectorAll('tbody tr[x-data]').forEach(row => {
            const alpine = Alpine.$data(row);
            if (alpine) {
              // Parse setiap nilai dengan hati-hati
              const stcVal = parseFloat(alpine.stc) || 0;
              const hcVal = parseFloat(alpine.hc) || 0;
              const bcVal = parseFloat(alpine.bc) || 0;
              const ritVal = parseInt(alpine.fbrit) || 0;
              const tonVal = parseFloat(alpine.fbton) || 0;
              
              stc += stcVal;
              hc += hcVal;
              bc += bcVal;
              rit += ritVal;
              ton += tonVal;
            }
          });

          // Update display
          this.totalSTC = stc.toFixed(2);
          this.totalHC = hc.toFixed(2);
          this.totalBC = bc.toFixed(2);
          this.totalFbRit = rit;
          this.totalFbTon = ton.toFixed(2);
        },

        async submitForm() {
          if (this.isSubmitting) return;

          const form = document.getElementById('hasilForm');
          const hcInputs = form.querySelectorAll('input[name*="[hc]"]');
          let hasEmpty = false;
          
          // Validasi semua HC harus diisi
          hcInputs.forEach(input => {
            const value = parseFloat(input.value);
            if (!input.value || isNaN(value) || value <= 0) {
              hasEmpty = true;
              input.classList.add('border-red-500', 'ring-2', 'ring-red-300');
            } else {
              input.classList.remove('border-red-500', 'ring-2', 'ring-red-300');
            }
          });

          if (hasEmpty) {
            alert('Semua field HC (Hectare Cutting) wajib diisi dengan nilai lebih dari 0');
            return;
          }

          if (!confirm('Simpan hasil panen ini?')) return;

          this.isSubmitting = true;

          try {
            const response = await fetch('{{ route("input.rkh-panen.updateHasil", $rkhPanen->rkhpanenno) }}', {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
              },
              body: new FormData(form)
            });

            const data = await response.json();

            if (data.success) {
              window.location.href = data.redirect_url || '{{ route("input.rkh-panen.show", $rkhPanen->rkhpanenno) }}';
            } else {
              alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
              this.isSubmitting = false;
            }
          } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan');
            this.isSubmitting = false;
          }
        }
      };
    }

    function hasilRow(index, stcValue) {
      return {
        stc: parseFloat(stcValue) || 0,
        hc: 0,
        bc: 0,
        fbrit: 0,
        fbton: 0,

        init() {
          // Load nilai existing dari input/select
          const hcInput = document.querySelector(`input[name="hasil[${index}][hc]"]`);
          const ritSelect = document.querySelector(`select[name="hasil[${index}][fbrit]"]`);
          const tonInput = document.querySelector(`input[name="hasil[${index}][fbton]"]`);
          
          if (hcInput?.value) {
            this.hc = parseFloat(hcInput.value) || 0;
          }
          if (ritSelect?.value) {
            this.fbrit = parseInt(ritSelect.value) || 0;
          }
          if (tonInput?.value) {
            this.fbton = parseFloat(tonInput.value) || 0;
          }
          
          this.calculateBC();
          
          // Trigger initial total update
          this.$dispatch('update-totals');
        },

        onHcChange() {
          // Otomatis batasi HC tidak boleh lebih dari STC (tanpa alert)
          const hcNum = parseFloat(this.hc) || 0;
          if (hcNum > this.stc) {
            this.hc = this.stc;
          }
          this.calculateBC();
          this.$dispatch('update-totals');
        },

        onFbRitChange() {
          // Auto calculate fbton dari fbrit (5 ton per rit)
          this.fbton = this.fbrit * 5;
          this.$dispatch('update-totals');
        },

        calculateBC() {
          const hcNum = parseFloat(this.hc) || 0;
          this.bc = this.stc - hcNum;
        }
      };
    }
  </script>

</x-layout>