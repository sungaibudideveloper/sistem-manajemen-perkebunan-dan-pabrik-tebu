{{-- resources\views\input\nfc\index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="nfcData()" class="relative">
        <div class="mx-auto bg-white rounded-md shadow-md p-6">
            
            {{-- Header --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <h2 class="text-2xl font-bold text-gray-800">NFC Card Inventory</h2>
                <div class="flex flex-wrap gap-2">
                    {{-- Mandor Transactions --}}
                    <button @click="showInModal = true" 
                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 text-xs rounded">
                        In from Mandor
                    </button>
                    <button @click="showOutModal = true" 
                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 text-xs rounded">
                        Out to Mandor
                    </button>
                    
                    {{-- External Transactions --}}
                    <button @click="showExternalInModal = true" 
                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 text-xs rounded">
                        Stock In
                    </button>
                    <button @click="showExternalOutModal = true" 
                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 text-xs rounded">
                        Stock Out
                    </button>
                </div>
            </div>

            {{-- Kantor Balance Card --}}
            <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">Balance Kantor</h3>
                        <p class="text-sm text-gray-600">Stok kartu NFC di warehouse</p>
                    </div>
                    <div class="text-right">
                        <p class="text-4xl font-bold text-blue-600">{{ $kantorBalance }}</p>
                        <p class="text-sm text-gray-600">kartu</p>
                    </div>
                </div>
            </div>

            {{-- Mandor Balances Table --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-3">Balance Mandor</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-xs">
                                <th class="border px-4 py-2">No.</th>
                                <th class="border px-4 py-2">Mandor ID</th>
                                <th class="border px-4 py-2">Nama Mandor</th>
                                <th class="border px-4 py-2">Balance</th>
                                <th class="border px-4 py-2">Last Transaction</th>
                                <th class="border px-4 py-2">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mandorBalances as $index => $mandor)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="border px-4 py-2 text-center">{{ $index + 1 }}</td>
                                <td class="border px-4 py-2">{{ $mandor->mandorid }}</td>
                                <td class="border px-4 py-2">{{ $mandor->mandorname }}</td>
                                <td class="border px-4 py-2 text-center">
                                    <span class="font-semibold {{ $mandor->balance > 0 ? 'text-green-600' : 'text-gray-500' }}">
                                        {{ $mandor->balance }}
                                    </span>
                                </td>
                                <td class="border px-4 py-2 text-center">
                                    {{ $mandor->lasttransaction ? \Carbon\Carbon::parse($mandor->lasttransaction)->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="border px-4 py-2">{{ $mandor->notes ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="border px-4 py-4 text-center text-gray-500">
                                    Belum ada data balance mandor
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Transactions --}}
            <div>
                <h3 class="text-lg font-semibold mb-3">Transaksi Terakhir</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-xs">
                                <th class="border px-4 py-2">Transaction No</th>
                                <th class="border px-4 py-2">Tanggal</th>
                                <th class="border px-4 py-2">Type</th>
                                <th class="border px-4 py-2">Mandor/Source</th>
                                <th class="border px-4 py-2">Qty</th>
                                <th class="border px-4 py-2">Notes</th>
                                <th class="border px-4 py-2">Input By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $tx)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="border px-4 py-2">{{ $tx->transactionno }}</td>
                                <td class="border px-4 py-2 text-center">
                                    {{ \Carbon\Carbon::parse($tx->transactiondate)->format('d/m/Y') }}
                                </td>
                                <td class="border px-4 py-2 text-center">
                                    @if($tx->transactiontype === 'OUT')
                                        <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded">OUT</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded">IN</span>
                                    @endif
                                </td>
                                <td class="border px-4 py-2">
                                    @if($tx->mandorid === 'EXTERNAL')
                                        <span class="px-2 py-1 text-xs font-semibold bg-purple-100 text-purple-800 rounded">EXTERNAL</span>
                                    @else
                                        {{ $tx->mandorname ?? $tx->mandorid }}
                                    @endif
                                </td>
                                <td class="border px-4 py-2 text-center font-semibold">{{ $tx->qty }}</td>
                                <td class="border px-4 py-2">{{ $tx->notes ?? '-' }}</td>
                                <td class="border px-4 py-2">{{ $tx->inputby }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="border px-4 py-4 text-center text-gray-500">
                                    Belum ada transaksi
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- OUT Modal (Mandor) --}}
        <div x-show="showOutModal" 
             x-cloak
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Out to Mandor</h3>
                <form @submit.prevent="processOut">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Mandor</label>
                        <select x-model="outForm.mandorid" required
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="">Pilih Mandor</option>
                            @foreach($mandorList as $mandor)
                            <option value="{{ $mandor->userid }}">{{ $mandor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Jumlah Kartu</label>
                        <input type="number" x-model="outForm.qty" min="1" required
                               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Keterangan</label>
                        <textarea x-model="outForm.notes" rows="3"
                                  class="w-full border border-gray-300 rounded px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showOutModal = false"
                                class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-100">
                            Batal
                        </button>
                        <button type="submit" :disabled="isProcessing"
                                class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded disabled:opacity-50">
                            <span x-show="!isProcessing">Proses</span>
                            <span x-show="isProcessing">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- IN Modal (Mandor) --}}
        <div x-show="showInModal" 
             x-cloak
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">In from Mandor</h3>
                <form @submit.prevent="processIn">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Mandor</label>
                        <select x-model="inForm.mandorid" required
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="">Pilih Mandor</option>
                            @foreach($mandorList as $mandor)
                            <option value="{{ $mandor->userid }}">{{ $mandor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Jumlah Kartu</label>
                        <input type="number" x-model="inForm.qty" min="1" required
                               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Keterangan</label>
                        <textarea x-model="inForm.notes" rows="3"
                                  class="w-full border border-gray-300 rounded px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showInModal = false"
                                class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-100">
                            Batal
                        </button>
                        <button type="submit" :disabled="isProcessing"
                                class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded disabled:opacity-50">
                            <span x-show="!isProcessing">Proses</span>
                            <span x-show="isProcessing">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- External IN Modal (Pembelian/Stock In) --}}
        <div x-show="showExternalInModal" 
             x-cloak
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Stock In</h3>
                <form @submit.prevent="processExternalIn">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Jumlah Kartu</label>
                        <input type="number" x-model="externalInForm.qty" min="1" required
                               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Notes <span class="text-red-500">*</span></label>
                        <textarea x-model="externalInForm.notes" rows="3" required
                                  placeholder="e.g., Purchase from supplier XYZ"
                                  class="w-full border border-gray-300 rounded px-3 py-2 text-sm"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Explain source of stock addition</p>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showExternalInModal = false"
                                class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-100">
                            Batal
                        </button>
                        <button type="submit" :disabled="isProcessing"
                                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded disabled:opacity-50">
                            <span x-show="!isProcessing">Proses</span>
                            <span x-show="isProcessing">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- External OUT Modal (Rusak/Hilang) --}}
        <div x-show="showExternalOutModal" 
             x-cloak
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Stock Out</h3>
                <form @submit.prevent="processExternalOut">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Reason <span class="text-red-500">*</span></label>
                        <select x-model="externalOutForm.reason" required
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="">Select reason</option>
                            <option value="DAMAGED">Damaged</option>
                            <option value="LOST">Lost</option>
                            <option value="DISPOSAL">Disposal</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Quantity</label>
                        <input type="number" x-model="externalOutForm.qty" min="1" required
                               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Notes <span class="text-red-500">*</span></label>
                        <textarea x-model="externalOutForm.notes" rows="3" required
                                  placeholder="e.g., Cards damaged by water, unreadable"
                                  class="w-full border border-gray-300 rounded px-3 py-2 text-sm"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Explain card condition or incident</p>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mb-4">
                        <p class="text-xs text-yellow-800">
                            ⚠️ Warning: This will permanently reduce warehouse stock
                        </p>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showExternalOutModal = false"
                                class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-100">
                            Batal
                        </button>
                        <button type="submit" :disabled="isProcessing"
                                class="px-4 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded disabled:opacity-50">
                            <span x-show="!isProcessing">Proses</span>
                            <span x-show="isProcessing">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
    function nfcData() {
        return {
            showOutModal: false,
            showInModal: false,
            showExternalInModal: false,
            showExternalOutModal: false,
            isProcessing: false,
            
            outForm: {
                mandorid: '',
                qty: '',
                notes: ''
            },
            
            inForm: {
                mandorid: '',
                qty: '',
                notes: ''
            },
            
            externalInForm: {
                qty: '',
                notes: ''
            },
            
            externalOutForm: {
                reason: '',
                qty: '',
                notes: ''
            },
            
            resetForms() {
                this.outForm = { mandorid: '', qty: '', notes: '' };
                this.inForm = { mandorid: '', qty: '', notes: '' };
                this.externalInForm = { qty: '', notes: '' };
                this.externalOutForm = { reason: '', qty: '', notes: '' };
            },
            
            async processOut() {
                if (!confirm('Yakin ingin mengeluarkan kartu NFC untuk mandor ini?')) return;
                
                this.isProcessing = true;
                try {
                    const response = await fetch('{{ route("input.nfc.transaction-out") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.outForm)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses transaksi');
                } finally {
                    this.isProcessing = false;
                }
            },
            
            async processIn() {
                if (!confirm('Yakin ingin menerima kartu NFC dari mandor ini?')) return;
                
                this.isProcessing = true;
                try {
                    const response = await fetch('{{ route("input.nfc.transaction-in") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.inForm)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses transaksi');
                } finally {
                    this.isProcessing = false;
                }
            },
            
            async processExternalIn() {
                if (!confirm('Yakin ingin menambah stock kartu NFC?')) return;
                
                this.isProcessing = true;
                try {
                    const response = await fetch('{{ route("input.nfc.external-in") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.externalInForm)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses transaksi');
                } finally {
                    this.isProcessing = false;
                }
            },
            
            async processExternalOut() {
                if (!confirm('PERHATIAN: Yakin ingin mengurangi stock secara permanen?')) return;
                
                this.isProcessing = true;
                try {
                    const response = await fetch('{{ route("input.nfc.external-out") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.externalOutForm)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses transaksi');
                } finally {
                    this.isProcessing = false;
                }
            }
        };
    }
    </script>
</x-layout>