<!-- Company Selection Modal Component -->
<!-- File: resources/views/components/company-modal.blade.php -->

<div x-data="companyModalData()" 
     x-show="showModal" 
     @open-company-modal.window="openModal()"
     @close-company-modal.window="closeModal()"
     @keydown.escape.window="closeModal()"
     style="display: none;"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" 
     x-cloak>
    
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 transform transition-all"
         x-show="showModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.away="closeModal()">
        
        <div class="flex justify-between items-center border-b border-slate-200 px-6 py-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h5 class="text-lg font-semibold text-slate-900">Select Your Company</h5>
            </div>
            <button @click="closeModal()" 
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form action="{{ route('setSession') }}" 
            method="POST" 
            @submit="Alpine.store('loading').start(); handleSubmit()">
            @csrf
            <div class="px-6 py-6">
                <label class="block text-sm font-medium text-slate-700 mb-3">Choose Company</label>
                <select name="dropdown_value" 
                        x-model="selectedCompany"
                        class="w-full border border-slate-300 rounded-xl px-4 py-3 text-slate-900 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                        required>
                    <option value="" disabled class="text-slate-400">--Select Company--</option>
                    @foreach ($companies as $comp)
                        <option value="{{ $comp }}" class="text-slate-900">
                            {{ formatCompanyCode($comp) }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex justify-end space-x-3 border-t border-slate-200 px-6 py-4">
                <button type="button" 
                        @click="closeModal()"
                        class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 focus:ring-2 focus:ring-slate-200 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        :disabled="!selectedCompany"
                        :class="selectedCompany ? 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700' : 'bg-gray-300 cursor-not-allowed'"
                        class="px-6 py-2 text-sm font-medium text-white border border-transparent rounded-xl focus:ring-2 focus:ring-green-500 transition-all shadow-sm">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Company Modal Alpine.js Component
function companyModalData() {
    return {
        showModal: false,
        selectedCompany: '{{ session("companycode") }}',
        
        // Computed property for formatted company code
        get formattedCompany() {
            return this.formatCode(this.selectedCompany);
        },
        
        openModal() {
            this.showModal = true;
            this.selectedCompany = '{{ session("companycode") }}';
            document.body.style.overflow = 'hidden';
        },
        
        closeModal() {
            this.showModal = false;
            document.body.style.overflow = 'auto';
        },
        
        handleSubmit() {
            console.log('Switching to company:', this.selectedCompany, '(formatted:', this.formattedCompany + ')');
        },
        
        // Format company code to Roman numerals (client-side)
        formatCode(code) {
            if (!code) return '';
            
            const match = code.match(/^([A-Z]+)(\d+)$/);
            if (match) {
                const prefix = match[1];
                const number = parseInt(match[2]);
                
                const romans = {
                    1: 'I', 2: 'II', 3: 'III', 4: 'IV', 5: 'V',
                    6: 'VI', 7: 'VII', 8: 'VIII', 9: 'IX', 10: 'X',
                    11: 'XI', 12: 'XII', 13: 'XIII', 14: 'XIV', 15: 'XV',
                    16: 'XVI', 17: 'XVII', 18: 'XVIII', 19: 'XIX', 20: 'XX'
                };
                
                if (romans[number]) {
                    return prefix + ' ' + romans[number];
                }
            }
            
            return code; // Return original if no match
        }
    }
}

// Make it available globally
window.companyModalData = companyModalData;
</script>

<style>
select:invalid {
    color: #64748b;
}

select:valid {
    color: #0f172a;
}

[x-cloak] {
    display: none !important;
}
</style>