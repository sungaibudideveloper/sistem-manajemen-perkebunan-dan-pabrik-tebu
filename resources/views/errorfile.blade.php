{{-- resources/views/errorfile.blade.php --}}

@if($errors->any())
  <div x-data="{ show: true }" 
       x-show="show"
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 transform scale-95"
       x-transition:enter-end="opacity-100 transform scale-100"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100 transform scale-100"
       x-transition:leave-end="opacity-0 transform scale-95"
       class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm relative"
       role="alert">
    <div class="flex">
      <div class="flex-shrink-0">
        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <div class="ml-3 flex-1">
        <h3 class="text-sm font-medium text-red-800">
          {{ $errors->count() === 1 ? 'There was 1 error with your submission' : "There were {$errors->count()} errors with your submission" }}
        </h3>
        <div class="mt-2 text-sm text-red-700">
          <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      </div>
      <div class="ml-auto pl-3">
        <button @click="show = false" 
                class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 focus:ring-offset-red-50 transition-colors">
          <span class="sr-only">Dismiss</span>
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
@endif

@if(session('flash_message'))
  <div x-data="{ show: true }" 
       x-show="show"
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 transform scale-95"
       x-transition:enter-end="opacity-100 transform scale-100"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100 transform scale-100"
       x-transition:leave-end="opacity-0 transform scale-95"
       @class([
         'mb-4 border-l-4 p-4 rounded-md shadow-sm relative',
         'bg-green-50 border-green-500' => session('flash_message_level') === 'success',
         'bg-red-50 border-red-500' => session('flash_message_level') === 'danger',
         'bg-yellow-50 border-yellow-500' => session('flash_message_level') === 'warning',
         'bg-blue-50 border-blue-500' => session('flash_message_level') === 'info',
       ])
       role="alert">
    <div class="flex">
      <div class="flex-shrink-0">
        @if(session('flash_message_level') === 'success')
          <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        @elseif(session('flash_message_level') === 'danger')
          <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        @elseif(session('flash_message_level') === 'warning')
          <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
        @else
          <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        @endif
      </div>
      <div class="ml-3 flex-1">
        <p @class([
          'text-sm font-medium',
          'text-green-800' => session('flash_message_level') === 'success',
          'text-red-800' => session('flash_message_level') === 'danger',
          'text-yellow-800' => session('flash_message_level') === 'warning',
          'text-blue-800' => session('flash_message_level') === 'info',
        ])>
          {!! session('flash_message') !!}
        </p>
      </div>
      <div class="ml-auto pl-3">
        <button @click="show = false" 
                @class([
                  'inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors',
                  'text-green-500 hover:bg-green-100 focus:ring-green-600 focus:ring-offset-green-50' => session('flash_message_level') === 'success',
                  'text-red-500 hover:bg-red-100 focus:ring-red-600 focus:ring-offset-red-50' => session('flash_message_level') === 'danger',
                  'text-yellow-500 hover:bg-yellow-100 focus:ring-yellow-600 focus:ring-offset-yellow-50' => session('flash_message_level') === 'warning',
                  'text-blue-500 hover:bg-blue-100 focus:ring-blue-600 focus:ring-offset-blue-50' => session('flash_message_level') === 'info',
                ])>
          <span class="sr-only">Dismiss</span>
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
@endif