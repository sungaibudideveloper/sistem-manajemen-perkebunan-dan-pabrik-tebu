@extends('errors::layout')

@section('title', '500 - Server Error')

@section('content')
    <!-- Error Icon -->
    <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-red-100">
        <svg class="h-16 w-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
        </svg>
    </div>

    <!-- Error Code -->
    <div class="mt-6">
        <h1 class="text-6xl font-bold text-gray-900">500</h1>
    </div>

    <!-- Error Message -->
    <div class="mt-2">
        <h2 class="text-2xl font-semibold text-gray-900">Internal Server Error</h2>
        <p class="mt-2 text-base text-gray-600">
            Oops! Something went wrong on our end. We're working to fix it.
        </p>
    </div>

    <!-- Action Buttons -->
    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ route('home') }}" 
           class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Back to Home
        </a>
        <button onclick="window.location.reload()" 
                class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Try Again
        </button>
    </div>

    <!-- Additional Help -->
    <div class="mt-8 text-sm text-gray-500">
        <p>If the problem persists, please contact technical support.</p>
    </div>

    @if(config('app.debug'))
    <!-- Debug Info (Only in Development) -->
    <div class="mt-8 p-4 bg-gray-100 rounded-lg text-left">
        <details>
            <summary class="cursor-pointer font-semibold text-gray-700 hover:text-gray-900">Debug Information</summary>
            <div class="mt-2 text-xs text-gray-600 space-y-1">
                @if(isset($exception))
                    <p><strong>Error:</strong> {{ $exception->getMessage() }}</p>
                    <p><strong>File:</strong> {{ $exception->getFile() }}</p>
                    <p><strong>Line:</strong> {{ $exception->getLine() }}</p>
                @endif
            </div>
        </details>
    </div>
    @endif
@endsection