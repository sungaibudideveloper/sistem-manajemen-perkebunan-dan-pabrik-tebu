@extends('errors::layout')

@section('title', '404 - Page Not Found')

@section('content')
    <!-- Error Icon -->
    <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-red-100">
        <svg class="h-16 w-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
    </div>

    <!-- Error Code -->
    <div class="mt-6">
        <h1 class="text-6xl font-bold text-gray-900">404</h1>
    </div>

    <!-- Error Message -->
    <div class="mt-2">
        <h2 class="text-2xl font-semibold text-gray-900">Page Not Found</h2>
        <p class="mt-2 text-base text-gray-600">
            Sorry, the page you are looking for doesn't exist or has been moved.
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
        <button onclick="window.history.back()" 
                class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Go Back
        </button>
    </div>

    <!-- Additional Help -->
    <div class="mt-8 text-sm text-gray-500">
        <p>If you believe this is a mistake, please contact support.</p>
    </div>
@endsection