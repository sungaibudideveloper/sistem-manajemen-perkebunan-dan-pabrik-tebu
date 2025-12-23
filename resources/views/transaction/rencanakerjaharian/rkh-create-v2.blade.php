{{-- resources/views/transaction/rencanakerjaharian/rkh-create-v2.blade.php --}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div x-data="rkhWizardApp()" class="max-w-7xl mx-auto px-4 py-6">
    
    {{-- Modern Progress Bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <div class="flex items-center justify-between relative">
        
        {{-- Progress Line Background --}}
        <div class="absolute top-5 left-0 right-0 h-1 bg-gray-200 -z-10"></div>
        
        {{-- Active Progress Line --}}
        <div class="absolute top-5 left-0 h-1 bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-500 -z-10"
             :style="`width: ${((currentStep - 1) / 6) * 100}%`"></div>

        {{-- Step Circles --}}
        <template x-for="(step, index) in steps" :key="step.id">
          <div class="flex flex-col items-center flex-1 relative">
            
            {{-- Circle --}}
            <div 
              class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 cursor-pointer hover:scale-110 z-10"
              :class="{
                'bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg': currentStep === step.id,
                'bg-gradient-to-br from-green-500 to-green-600 text-white shadow-md': currentStep > step.id,
                'bg-white border-2 border-gray-300 text-gray-400': currentStep < step.id
              }"
              @click="currentStep > step.id ? goToStep(step.id) : null"
            >
              <template x-if="currentStep > step.id">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
              </template>
              <template x-if="currentStep <= step.id">
                <span class="font-bold text-lg" x-text="step.id"></span>
              </template>
            </div>

            {{-- Step Label --}}
            <div class="mt-3 text-center">
              <p class="text-xs font-semibold transition-colors"
                 :class="currentStep >= step.id ? 'text-gray-800' : 'text-gray-400'"
                 x-text="step.title">
              </p>
              <p class="text-[10px] text-gray-500 mt-0.5" x-text="step.subtitle"></p>
            </div>

          </div>
        </template>

      </div>
    </div>

    {{-- Main Wizard Container --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
      
      {{-- Header Info (Always Visible) --}}
      <div class="bg-gradient-to-r from-blue-50 via-sky-50 to-blue-50 border-b border-gray-200 px-8 py-6">
        <div class="flex justify-between items-center">
          <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-1">Rencana Kerja Harian</h2>
            <div class="flex items-center gap-4 text-sm text-gray-600">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="font-medium">{{ $selectedMandor->name ?? 'Loading...' }}</span>
              </div>
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="font-medium">{{ $selectedDate }}</span>
              </div>
            </div>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-blue-700 bg-clip-text text-transparent">
              <span x-text="currentStep"></span>/<span>7</span>
            </div>
            <div class="text-xs text-gray-500 font-medium mt-1" x-text="steps[currentStep-1]?.title"></div>
          </div>
        </div>
      </div>

      {{-- Step Content Area --}}
      <div class="p-8 min-h-[600px]">

        {{-- STEP 1: Select Activities --}}
        <div x-show="currentStep === 1" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.wizard-steps.step1-activities')
        </div>

        {{-- STEP 2: Assign Plots --}}
        <div x-show="currentStep === 2"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.wizard-steps.step2-plots')
        </div>

        {{-- STEP 3: Plot Details --}}
        <div x-show="currentStep === 3"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.wizard-steps.step3-details')
        </div>

        {{-- STEP 4: Materials --}}
        <div x-show="currentStep === 4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.wizard-steps.step4-materials')
        </div>

        {{-- STEP 5: Vehicles --}}
        <div x-show="currentStep === 5"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.wizard-steps.step5-vehicles')
        </div>

        {{-- STEP 6: Manpower --}}
        <div x-show="currentStep === 6"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.wizard-steps.step6-manpower')
        </div>

        {{-- STEP 7: Review --}}
        <div x-show="currentStep === 7"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.wizard-steps.step7-review')
        </div>

      </div>

      {{-- Navigation Footer --}}
      <div class="bg-gray-50 border-t border-gray-200 px-8 py-5">
        <div class="flex justify-between items-center">
          
          {{-- Back Button --}}
          <button 
            type="button"
            @click="prevStep()" 
            x-show="currentStep > 1"
            class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-all duration-200 flex items-center gap-2 shadow-sm hover:shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Previous
          </button>

          {{-- Spacer --}}
          <div x-show="currentStep === 1"></div>

          {{-- Next Button --}}
          <button 
            type="button"
            @click="nextStep()" 
            x-show="currentStep < 7"
            :disabled="!canProceed()"
            :class="canProceed() ? 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg' : 'bg-gray-300 cursor-not-allowed'"
            class="px-6 py-2.5 text-white rounded-lg font-medium transition-all duration-200 flex items-center gap-2">
            <span x-text="getNextButtonText()"></span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>

          {{-- Submit Button --}}
          <button 
            type="button"
            @click="submitForm()" 
            x-show="currentStep === 7"
            :disabled="isSubmitting"
            class="px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg font-semibold transition-all duration-200 flex items-center gap-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span x-show="!isSubmitting">Submit RKH</span>
            <span x-show="isSubmitting">
              <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
          </button>

        </div>
      </div>

    </div>

  </div>

  @push('scripts')
<script>
  // ✅ Set global data FIRST - SAMAIN DENGAN V1
  window.activitiesData = @json($activities ?? []);
  window.bloksData = @json($bloks ?? []);
  window.masterlistData = @json($masterlist ?? []);  // ← GANTI INI
  window.herbisidaData = @json($herbisidagroups ?? []);
  window.vehiclesData = @json($vehiclesData ?? []);
  window.helpersData = @json($helpersData ?? []);
  window.absenData = @json($absenData ?? []);
  window.plotsData = @json($plotsData ?? []);  // ← Keep this juga buat backup
  window.rkhDate = '{{ $selectedDate }}';
  window.mandorId = '{{ $selectedMandor->userid ?? '' }}';

  console.log('🔥 Global Data Loaded:', {
    activities: window.activitiesData?.length,
    masterlist: window.masterlistData?.length,  // ← CEK INI
    plots: window.plotsData?.length,
    vehicles: window.vehiclesData?.length,
    helpers: window.helpersData?.length
  });
</script>

<script>
  // ✅ Register Alpine component
  document.addEventListener('alpine:init', () => {
    Alpine.data('rkhWizardApp', () => ({
      currentStep: 1,
      isSubmitting: false,

      steps: [
        { id: 1, title: 'Activities', subtitle: 'Select tasks' },
        { id: 2, title: 'Plots', subtitle: 'Assign locations' },
        { id: 3, title: 'Details', subtitle: 'Confirm area' },
        { id: 4, title: 'Materials', subtitle: 'Per plot' },
        { id: 5, title: 'Vehicles', subtitle: 'Per activity' },
        { id: 6, title: 'Manpower', subtitle: 'Per activity' },
        { id: 7, title: 'Review', subtitle: 'Final check' }
      ],

      // State management
      selectedActivities: {},
      plotAssignments: {},
      luasConfirmed: {},
      materials: {},
      vehicles: {},
      workers: {},
      
      // Step 1: Activities
      activitySearch: '',
      
      // Step 2: Plots
      currentActivityForPlots: null,
      selectedBlokForPlots: null,

      init() {
        console.log('🚀 RKH Wizard Initialized');
        console.log('Step:', this.currentStep);
        console.log('Activities:', this.selectedActivities);
        
        // Auto-watch untuk set first activity
        this.$watch('selectedActivities', (activities) => {
          const actCodes = Object.keys(activities);
          if (actCodes.length > 0 && !this.currentActivityForPlots) {
            this.currentActivityForPlots = actCodes[0];
          }
          if (actCodes.length === 0) {
            this.currentActivityForPlots = null;
          }
        });
        
        // Auto-set first blok
        this.$nextTick(() => {
          const bloks = this.availableBloks();
          if (bloks.length > 0 && !this.selectedBlokForPlots) {
            this.selectedBlokForPlots = bloks[0];
          }
        });
      },

      // ==================== STEP 1: ACTIVITIES ====================
      filteredActivities() {
        const activities = window.activitiesData || [];
        if (!this.activitySearch) return activities;
        
        const query = this.activitySearch.toLowerCase();
        return activities.filter(act => 
          act.activitycode.toLowerCase().includes(query) ||
          act.activityname.toLowerCase().includes(query)
        );
      },
      
      toggleActivity(activity) {
        if (this.selectedActivities[activity.activitycode]) {
          // Remove
          delete this.selectedActivities[activity.activitycode];
          delete this.plotAssignments[activity.activitycode];
          
          // Cascade delete
          Object.keys(this.luasConfirmed).forEach(key => {
            if (key.startsWith(activity.activitycode + '_')) {
              delete this.luasConfirmed[key];
            }
          });
          Object.keys(this.materials).forEach(key => {
            if (key.startsWith(activity.activitycode + '_')) {
              delete this.materials[key];
            }
          });
          
          delete this.vehicles[activity.activitycode];
          delete this.workers[activity.activitycode];
          
        } else {
          // Add
          this.selectedActivities[activity.activitycode] = {
            code: activity.activitycode,
            name: activity.activityname,
            usingvehicle: activity.usingvehicle || 0,
            usingmaterial: activity.usingmaterial || 0,
            isblokactivity: activity.isblokactivity || 0,
            jenistenagakerja: activity.jenistenagakerja
          };
          
          this.workers[activity.activitycode] = {
            laki: '',
            perempuan: '',
            total: 0
          };
        }
      },

      // ==================== STEP 2: PLOTS ====================
      availableBloks() {
        const bloksSet = new Set();
        (window.masterlistData || []).forEach(plot => {
          if (plot.blok) bloksSet.add(plot.blok);
        });
        return Array.from(bloksSet).sort();
      },
      
      getPlotsForBlok(blok) {
        if (!blok) return [];
        return (window.masterlistData || []).filter(plot => plot.blok === blok);
      },
      
      isPlotSelectedForActivity(actCode, plot) {
        const assignments = this.plotAssignments[actCode] || [];
        return assignments.some(p => p.blok === plot.blok && p.plot === plot.plot);
      },
      
      togglePlotForActivity(actCode, plot) {
        if (!this.plotAssignments[actCode]) {
          this.plotAssignments[actCode] = [];
        }
        
        const index = this.plotAssignments[actCode].findIndex(
          p => p.blok === plot.blok && p.plot === plot.plot
        );
        
        if (index > -1) {
          this.plotAssignments[actCode].splice(index, 1);
          
          const key = `${actCode}_${plot.blok}_${plot.plot}`;
          delete this.luasConfirmed[key];
          delete this.materials[key];
        } else {
          this.plotAssignments[actCode].push({
            blok: plot.blok,
            plot: plot.plot,
            luasplot: plot.luasplot,
            luassisa: plot.luassisa || plot.luasplot,
            batchno: plot.batchno || null,
            lifecyclestatus: plot.lifecyclestatus || null
          });
          
          const key = `${actCode}_${plot.blok}_${plot.plot}`;
          this.luasConfirmed[key] = plot.luassisa || plot.luasplot;
        }
      },

      getSelectedPlotsInBlok(blok, actCode) {
        const assignments = this.plotAssignments[actCode] || [];
        return assignments.filter(p => p.blok === blok);
      },
      
      selectAllPlotsInBlok(blok) {
        if (!blok || !this.currentActivityForPlots) return;
        
        const plots = this.getPlotsForBlok(blok);
        plots.forEach(plot => {
          if (!this.isPlotSelectedForActivity(this.currentActivityForPlots, plot)) {
            this.togglePlotForActivity(this.currentActivityForPlots, plot);
          }
        });
      },
      
      deselectAllPlotsInBlok(blok) {
        if (!blok || !this.currentActivityForPlots) return;
        
        const plots = this.getPlotsForBlok(blok);
        plots.forEach(plot => {
          if (this.isPlotSelectedForActivity(this.currentActivityForPlots, plot)) {
            this.togglePlotForActivity(this.currentActivityForPlots, plot);
          }
        });
      },

      clearPlotsForActivity(actCode) {
        (this.plotAssignments[actCode] || []).forEach(plot => {
          const key = `${actCode}_${plot.blok}_${plot.plot}`;
          delete this.luasConfirmed[key];
          delete this.materials[key];
        });
        
        this.plotAssignments[actCode] = [];
      },
      
      removePlotFromActivity(actCode, plot) {
        const index = this.plotAssignments[actCode].findIndex(
          p => p.blok === plot.blok && p.plot === plot.plot
        );
        if (index > -1) {
          this.plotAssignments[actCode].splice(index, 1);
          
          const key = `${actCode}_${plot.blok}_${plot.plot}`;
          delete this.luasConfirmed[key];
          delete this.materials[key];
        }
      },

      getTotalLuasForActivity(actCode) {
        const plots = this.plotAssignments[actCode] || [];
        const total = plots.reduce((sum, plot) => sum + parseFloat(plot.luassisa || 0), 0);
        return total.toFixed(2);
      },

      // ==================== STEP 3: DETAILS ====================
      getTotalPlotsCount() {
        let total = 0;
        Object.values(this.plotAssignments).forEach(plots => {
          total += plots?.length || 0;
        });
        return total;
      },
      
      getTotalLuasAll() {
        let total = 0;
        Object.keys(this.luasConfirmed).forEach(key => {
          total += parseFloat(this.luasConfirmed[key]) || 0;
        });
        return total.toFixed(2);
      },
      
      getTotalLuasForActivityConfirmed(actCode) {
        let total = 0;
        const plots = this.plotAssignments[actCode] || [];
        plots.forEach(plot => {
          const key = `${actCode}_${plot.blok}_${plot.plot}`;
          total += parseFloat(this.luasConfirmed[key]) || 0;
        });
        return total.toFixed(2);
      },

      validateLuasInput(event, actCode, plot) {
        const input = event.target;
        const value = parseFloat(input.value) || 0;
        const maxLuas = parseFloat(plot.luassisa);
        const key = `${actCode}_${plot.blok}_${plot.plot}`;
        
        if (value > maxLuas) {
          this.luasConfirmed[key] = maxLuas.toFixed(2);
          input.value = maxLuas.toFixed(2);
          showToast(`Luas tidak boleh melebihi ${maxLuas.toFixed(2)} Ha`, 'warning', 3000);
          
          input.classList.add('border-red-500', 'bg-red-50');
          setTimeout(() => {
            input.classList.remove('border-red-500', 'bg-red-50');
          }, 2000);
        } else {
          this.luasConfirmed[key] = value.toFixed(2);
        }
      },

      // ==================== STEP 4: MATERIALS ====================
      getAvailableMaterialGroups(actCode) {
        const herbisidaData = window.herbisidaData || [];
        const groups = {};
        
        herbisidaData.forEach(item => {
          if (item.activitycode === actCode) {
            if (!groups[item.herbisidagroupid]) {
              groups[item.herbisidagroupid] = {
                herbisidagroupid: item.herbisidagroupid,
                herbisidagroupname: item.herbisidagroupname,
                description: item.description || 'No description',
                items: []
              };
            }
            groups[item.herbisidagroupid].items.push(item);
          }
        });
        
        return Object.values(groups);
      },
      
      selectMaterial(actCode, plot, group) {
        const key = `${actCode}_${plot.blok}_${plot.plot}`;
        this.materials[key] = {
          groupid: group.herbisidagroupid,
          groupname: group.herbisidagroupname
        };
      },

      getRequiredMaterialCount() {
        let count = 0;
        Object.entries(this.selectedActivities).forEach(([actCode, activity]) => {
          if (activity.usingmaterial == 1) {
            count += this.plotAssignments[actCode]?.length || 0;
          }
        });
        return count;
      },
      
      getCompletedMaterialCount() {
        let count = 0;
        Object.entries(this.selectedActivities).forEach(([actCode, activity]) => {
          if (activity.usingmaterial == 1) {
            const plots = this.plotAssignments[actCode] || [];
            plots.forEach(plot => {
              const key = `${actCode}_${plot.blok}_${plot.plot}`;
              if (this.materials[key]) count++;
            });
          }
        });
        return count;
      },
      
      getMaterialProgress() {
        const required = this.getRequiredMaterialCount();
        if (required === 0) return 100;
        return Math.round((this.getCompletedMaterialCount() / required) * 100);
      },

      getMaterialCountForActivity(actCode) {
        const plots = this.plotAssignments[actCode] || [];
        let completed = 0;
        plots.forEach(plot => {
          const key = `${actCode}_${plot.blok}_${plot.plot}`;
          if (this.materials[key]) completed++;
        });
        return `${completed}/${plots.length}`;
      },

      allMaterialsSelected() {
        return this.getCompletedMaterialCount() === this.getRequiredMaterialCount();
      },

      // ==================== STEP 5: VEHICLES ====================
      getActivitiesRequiringVehicles() {
        return Object.values(this.selectedActivities).filter(act => act.usingvehicle == 1).length;
      },
      
      getActivitiesWithVehicles() {
        let count = 0;
        Object.entries(this.selectedActivities).forEach(([actCode, activity]) => {
          if (activity.usingvehicle == 1 && this.vehicles[actCode]?.length > 0) {
            count++;
          }
        });
        return count;
      },
      
      getTotalVehiclesAssigned() {
        let total = 0;
        Object.values(this.vehicles).forEach(actVehicles => {
          total += actVehicles?.length || 0;
        });
        return total;
      },

      openVehicleSelector(actCode) {
        window.dispatchEvent(new CustomEvent('open-vehicle-modal', { 
          detail: { activityCode: actCode } 
        }));
      },
      
      removeVehicle(actCode, index) {
        if (this.vehicles[actCode]) {
          this.vehicles[actCode].splice(index, 1);
          if (this.vehicles[actCode].length === 0) {
            delete this.vehicles[actCode];
          }
        }
      },

      allVehiclesAssigned() {
        return Object.entries(this.selectedActivities).every(([actCode, activity]) => {
          if (activity.usingvehicle == 1) {
            return this.vehicles[actCode] && this.vehicles[actCode].length > 0;
          }
          return true;
        });
      },

      // ==================== STEP 6: MANPOWER ====================
      updateWorkerTotal(actCode) {
        if (!this.workers[actCode]) return;
        
        const laki = parseInt(this.workers[actCode].laki) || 0;
        const perempuan = parseInt(this.workers[actCode].perempuan) || 0;
        
        this.workers[actCode].total = laki + perempuan;
      },
      
      getTotalWorkers(type) {
        let total = 0;
        Object.values(this.workers).forEach(worker => {
          if (type === 'laki') {
            total += parseInt(worker.laki) || 0;
          } else if (type === 'perempuan') {
            total += parseInt(worker.perempuan) || 0;
          } else if (type === 'total') {
            total += parseInt(worker.total) || 0;
          }
        });
        return total;
      },

      getJenisLabel(jenisId) {
        const labels = {
          1: 'Harian',
          2: 'Borongan',
          3: 'Operator',
          4: 'Helper'
        };
        return labels[jenisId] || '-';
      },

      getAbsenSuggestion(actCode) {
        const absenData = window.absenData || [];
        const mandorId = window.mandorId;
        
        const filtered = absenData.filter(a => a.mandorid === mandorId);
        
        let laki = 0, perempuan = 0;
        filtered.forEach(absen => {
          if (absen.gender === 'L') laki++;
          else if (absen.gender === 'P') perempuan++;
        });
        
        return { laki, perempuan, total: laki + perempuan };
      },
      
      applyAbsenSuggestion(actCode) {
        const suggestion = this.getAbsenSuggestion(actCode);
        if (!this.workers[actCode]) {
          this.workers[actCode] = { laki: '', perempuan: '', total: 0 };
        }
        
        this.workers[actCode].laki = suggestion.laki.toString();
        this.workers[actCode].perempuan = suggestion.perempuan.toString();
        this.updateWorkerTotal(actCode);
        
        showToast('Attendance data applied', 'success', 2000);
      },

      allWorkersCompleted() {
        return Object.keys(this.selectedActivities).every(actCode => {
          const worker = this.workers[actCode];
          return worker && (worker.laki !== '' || worker.perempuan !== '');
        });
      },

      // ==================== NAVIGATION ====================
      nextStep() {
        if (this.canProceed()) {
          let nextStep = this.currentStep + 1;
          
          if (nextStep === 4 && !this.hasActivityWithMaterial()) {
            nextStep = 5;
          }
          
          if (nextStep === 5 && !this.hasActivityWithVehicle()) {
            nextStep = 6;
          }
          
          this.currentStep = nextStep;
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      },

      prevStep() {
        let prevStep = this.currentStep - 1;
        
        if (prevStep === 5 && !this.hasActivityWithVehicle()) {
          prevStep = 4;
        }
        if (prevStep === 4 && !this.hasActivityWithMaterial()) {
          prevStep = 3;
        }
        
        this.currentStep = Math.max(1, prevStep);
        window.scrollTo({ top: 0, behavior: 'smooth' });
      },

      goToStep(stepId) {
        if (stepId < this.currentStep) {
          this.currentStep = stepId;
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      },

      // ==================== VALIDATION ====================
      canProceed() {
        switch(this.currentStep) {
          case 1: 
            return Object.keys(this.selectedActivities).length > 0;
          case 2:
            return Object.keys(this.plotAssignments).length > 0 &&
                   Object.values(this.plotAssignments).some(plots => plots.length > 0);
          case 3:
            return this.allLuasConfirmed();
          case 4:
            return this.validateMaterials();
          case 5:
            return this.validateVehicles();
          case 6:
            return this.validateWorkers();
          default:
            return true;
        }
      },

      getNextButtonText() {
        if (this.currentStep === 6) return 'Review';
        
        if (this.currentStep === 3 && !this.hasActivityWithMaterial()) {
          return this.hasActivityWithVehicle() ? 'Next: Vehicles' : 'Next: Manpower';
        }
        if (this.currentStep === 4 && !this.hasActivityWithVehicle()) {
          return 'Next: Manpower';
        }
        
        return 'Next';
      },

      hasActivityWithMaterial() {
        return Object.values(this.selectedActivities).some(act => act.usingmaterial == 1);
      },

      hasActivityWithVehicle() {
        return Object.values(this.selectedActivities).some(act => act.usingvehicle == 1);
      },

      allLuasConfirmed() {
        const allPlots = [];
        Object.entries(this.plotAssignments).forEach(([actCode, plots]) => {
          plots.forEach(plot => {
            allPlots.push(`${actCode}_${plot.blok}_${plot.plot}`);
          });
        });
        
        return allPlots.every(key => {
          const luas = this.luasConfirmed[key];
          return luas !== undefined && luas !== '' && parseFloat(luas) > 0;
        });
      },

      validateMaterials() {
        if (!this.hasActivityWithMaterial()) return true;
        
        const plotsNeedingMaterial = [];
        Object.entries(this.plotAssignments).forEach(([actCode, plots]) => {
          const activity = this.selectedActivities[actCode];
          if (activity?.usingmaterial == 1) {
            plots.forEach(plot => {
              plotsNeedingMaterial.push(`${actCode}_${plot.blok}_${plot.plot}`);
            });
          }
        });
        
        return plotsNeedingMaterial.every(key => this.materials[key]);
      },

      validateVehicles() {
        if (!this.hasActivityWithVehicle()) return true;
        
        return Object.entries(this.selectedActivities).every(([actCode, activity]) => {
          if (activity.usingvehicle == 1) {
            return this.vehicles[actCode] && this.vehicles[actCode].length > 0;
          }
          return true;
        });
      },

      validateWorkers() {
        return Object.keys(this.selectedActivities).every(actCode => {
          const worker = this.workers[actCode];
          return worker && (worker.laki !== '' || worker.perempuan !== '');
        });
      },

      // ==================== SUBMIT ====================
      async submitForm() {
        this.isSubmitting = true;
        
        const payload = this.transformToBackendFormat();
        
        console.log('📤 Submitting:', payload);
        
        try {
          const response = await fetch('{{ route("transaction.rencanakerjaharian.store") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
          });
          
          const result = await response.json();
          
          if (result.success) {
            showToast('RKH berhasil dibuat!', 'success', 3000);
            setTimeout(() => {
              window.location.href = result.redirect_url;
            }, 1000);
          } else {
            showToast(result.message || 'Gagal submit RKH', 'error', 4000);
            this.isSubmitting = false;
          }
        } catch (error) {
          console.error('Submit error:', error);
          showToast('Terjadi kesalahan sistem', 'error', 4000);
          this.isSubmitting = false;
        }
      },

      transformToBackendFormat() {
        const rows = [];
        
        Object.entries(this.plotAssignments).forEach(([activityCode, plots]) => {
          plots.forEach((plot) => {
            const key = `${activityCode}_${plot.blok}_${plot.plot}`;
            const material = this.materials[key];
            
            rows.push({
              nama: activityCode,
              blok: plot.blok,
              plot: plot.plot,
              luas: this.luasConfirmed[key] || plot.luassisa,
              material_group_id: material?.groupid || '',
              usingmaterial: material ? '1' : '0',
              batchno: plot.batchno || null
            });
          });
        });
        
        return {
          tanggal: window.rkhDate,
          mandor_id: window.mandorId,
          keterangan: '',
          rows: rows,
          workers: this.workers,
          kendaraan: this.vehicles
        };
      }
    }));
  });

  // Toast utility
  function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white transform transition-all duration-300 ${
      type === 'success' ? 'bg-green-500' : 
      type === 'error' ? 'bg-red-500' : 
      type === 'warning' ? 'bg-yellow-500' :
      'bg-blue-500'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), duration);
  }

  // Vehicle Selection Modal Component
  document.addEventListener('alpine:init', () => {
    Alpine.data('vehicleSelectionModal', () => ({
      showModal: false,
      currentActivityCode: '',
      searchQuery: '',
      useHelper: false,
      selectedVehicle: null,
      selectedHelper: null,
      
      init() {
        window.addEventListener('open-vehicle-modal', (e) => {
          this.currentActivityCode = e.detail.activityCode;
          this.showModal = true;
        });
      },
      
      filteredVehicles() {
        const vehicles = window.vehiclesData || [];
        if (!this.searchQuery) return vehicles;
        
        const q = this.searchQuery.toLowerCase();
        return vehicles.filter(v => 
          v.nokendaraan?.toLowerCase().includes(q) ||
          v.operator_name?.toLowerCase().includes(q) ||
          v.vehicle_type?.toLowerCase().includes(q)
        );
      },
      
      availableHelpers() {
        return window.helpersData || [];
      },
      
      selectVehicle(vehicle) {
        this.selectedVehicle = vehicle;
      },
      
      selectHelper(helper) {
        this.selectedHelper = helper;
      },
      
      confirmSelection() {
        if (!this.selectedVehicle) return;
        
        const wizardApp = Alpine.$data(document.querySelector('[x-data*="rkhWizardApp"]'));
        
        if (!wizardApp.vehicles[this.currentActivityCode]) {
          wizardApp.vehicles[this.currentActivityCode] = [];
        }
        
        // Check duplicate
        const isDuplicate = wizardApp.vehicles[this.currentActivityCode].some(
          v => v.nokendaraan === this.selectedVehicle.nokendaraan
        );
        
        if (isDuplicate) {
          showToast('Vehicle already assigned to this activity', 'warning', 3000);
          return;
        }
        
        wizardApp.vehicles[this.currentActivityCode].push({
          nokendaraan: this.selectedVehicle.nokendaraan,
          vehicle_type: this.selectedVehicle.vehicle_type,
          operatorid: this.selectedVehicle.operator_id,
          operator_name: this.selectedVehicle.operator_name,
          helperid: this.useHelper && this.selectedHelper ? this.selectedHelper.tenagakerjaid : null,
          helper_name: this.useHelper && this.selectedHelper ? this.selectedHelper.nama : null
        });
        
        showToast('Vehicle added successfully', 'success', 2000);
        this.closeModal();
      },
      
      closeModal() {
        this.showModal = false;
        this.searchQuery = '';
        this.useHelper = false;
        this.selectedVehicle = null;
        this.selectedHelper = null;
      }
    }));
  });
</script>
@endpush
</x-layout>