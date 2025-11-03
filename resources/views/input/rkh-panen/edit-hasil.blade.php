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
            <tr class="hover:bg-gray-50" 
                x-data="hasilRow({{ $index }}, {{ $row->stc }}, {{ $row->hc ?? 0 }}, {{ $row->fbrit ?? 0 }}, {{ $row->fbton ?? 0 }})"
                x-init="initRow()">
              
              <!-- Hidden input untuk plot identifier -->
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
              
              <!-- ✅ STC (Read-only, dari database) -->
              <td class="px-4 py-3 text-right">
                <input type="text" 
                       :value="stcDisplay"
                       readonly
                       class="w-24 text-right bg-gray-100 border border-gray-300 rounded px-2 py-1.5 text-sm font-semibold text-orange-700 cursor-not-allowed">
              </td>
              
              <!-- ✅ HC (Required, user input) -->
              <td class="px-4 py-3 text-right">
                <input type="number" 
                       name="hasil[{{ $index }}][hc]" 
                       x-model.number="hc"
                       @input="onHcChange()"
                       step="0.01"
                       min="0.01"
                       :max="stc"
                       required
                       class="w-24 text-right border border-gray-300 rounded px-2 py-1.5 text-sm font-semibold text-green-700 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       placeholder="0.00">
              </td>
              
              <!-- ✅ BC (Auto-calculated dari STC - HC) -->
              <td class="px-4 py-3 text-right">
                <input type="text" 
                       :value="bcDisplay"
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
                    <option value="{{ $i }}">{{ $i }}</option>
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
              <td class="px-4 py-3 text-right text-orange-700" x-text="totalSTCDisplay">0.00</td>
              <td class="px-4 py-3 text-right text-green-700" x-text="totalHCDisplay">0.00</td>
              <td class="px-4 py-3 text-right text-gray-800" x-text="totalBCDisplay">0.00</td>
              <td class="px-4 py-3 text-center text-gray-800 border-l-2 border-gray-200" x-text="totalFbRit">0</td>
              <td class="px-4 py-3 text-right text-blue-700" x-text="totalFbTonDisplay">0.00</td>
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
        totalSTC: 0,
        totalHC: 0,
        totalBC: 0,
        totalFbRit: 0,
        totalFbTon: 0,

        // Computed displays
        get totalSTCDisplay() { return this.totalSTC.toFixed(2); },
        get totalHCDisplay() { return this.totalHC.toFixed(2); },
        get totalBCDisplay() { return this.totalBC.toFixed(2); },
        get totalFbTonDisplay() { return this.totalFbTon.toFixed(2); },

        init() {
          // Listen untuk update dari child rows
          this.$el.addEventListener('update-totals', () => this.updateTotals());
          
          // Initial calculation setelah semua row ter-render
          this.$nextTick(() => {
            setTimeout(() => this.updateTotals(), 150);
          });
        },

        updateTotals() {
          let stc = 0, hc = 0, bc = 0, rit = 0, ton = 0;
          
          // Loop semua row yang punya Alpine data
          document.querySelectorAll('tbody tr[x-data]').forEach(row => {
            const alpine = Alpine.$data(row);
            if (alpine) {
              stc += parseFloat(alpine.stc) || 0;
              hc += parseFloat(alpine.hc) || 0;
              bc += parseFloat(alpine.bc) || 0;
              rit += parseInt(alpine.fbrit) || 0;
              ton += parseFloat(alpine.fbton) || 0;
            }
          });

          // Update totals
          this.totalSTC = stc;
          this.totalHC = hc;
          this.totalBC = bc;
          this.totalFbRit = rit;
          this.totalFbTon = ton;
        },

        async submitForm() {
          if (this.isSubmitting) return;

          const form = document.getElementById('hasilForm');
          const hcInputs = form.querySelectorAll('input[name*="[hc]"]');
          let hasEmpty = false;
          
          // ✅ Validasi semua HC harus diisi dengan nilai > 0
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
            alert('❌ Semua field HC (Hectare Cutting) wajib diisi dengan nilai lebih dari 0');
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
              alert('❌ Gagal: ' + (data.message || 'Terjadi kesalahan'));
              this.isSubmitting = false;
            }
          } catch (error) {
            console.error('Error:', error);
            alert('❌ Terjadi kesalahan saat menyimpan');
            this.isSubmitting = false;
          }
        }
      };
    }

    /**
     * ✅ FIXED: Alpine component untuk setiap row
     * @param {number} index - Row index
     * @param {number} stcValue - STC dari database (TIDAK BOLEH DIUBAH)
     * @param {number} existingHc - HC yang sudah ada (jika edit)
     * @param {number} existingFbRit - FB Rit yang sudah ada
     * @param {number} existingFbTon - FB Ton yang sudah ada
     */
    function hasilRow(index, stcValue, existingHc = 0, existingFbRit = 0, existingFbTon = 0) {
      return {
        // ✅ STC adalah konstanta, TIDAK BOLEH DIUBAH
        stc: parseFloat(stcValue) || 0,
        
        // User inputs
        hc: parseFloat(existingHc) || 0,
        fbrit: parseInt(existingFbRit) || 0,
        fbton: parseFloat(existingFbTon) || 0,
        
        // Calculated
        bc: 0,

        // Display helpers
        get stcDisplay() { return this.stc.toFixed(2); },
        get bcDisplay() { return this.bc.toFixed(2); },

        initRow() {
          // Calculate initial BC
          this.calculateBC();
          
          // Trigger parent total update
          this.$nextTick(() => {
            this.$dispatch('update-totals');
          });
        },

        onHcChange() {
          // ✅ Auto-limit HC: tidak boleh lebih dari STC
          const hcNum = parseFloat(this.hc) || 0;
          if (hcNum > this.stc) {
            this.hc = this.stc; // Auto-adjust
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
          // ✅ BC = STC - HC (STC tetap konstan)
          const hcNum = parseFloat(this.hc) || 0;
          this.bc = Math.max(0, this.stc - hcNum);
        }
      };
    }
  </script>

</x-layout>