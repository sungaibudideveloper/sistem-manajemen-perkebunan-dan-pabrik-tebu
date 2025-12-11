@extends('errors::layout')

@section('title', '503 - Service Unavailable')

@section('content')
    <!-- Error Icon -->
    <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-blue-100">
        <svg class="h-16 w-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
    </div>

    <!-- Error Code -->
    <div class="mt-6">
        <h1 class="text-6xl font-bold text-gray-900">503</h1>
    </div>

    <!-- Error Message -->
    <div class="mt-2">
        <h2 class="text-2xl font-semibold text-gray-900">Under Maintenance</h2>
        <p class="mt-2 text-base text-gray-600">
            We're currently performing scheduled maintenance. We'll be back shortly.
        </p>
    </div>

    <!-- Action Buttons -->
    <div class="mt-8 flex justify-center">
        <button onclick="window.location.reload()" 
                class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Refresh Page
        </button>
    </div>

    <!-- Additional Help -->
    <div class="mt-8 text-sm text-gray-500">
        <p>Thank you for your patience. Please check back soon.</p>
    </div>
@endsection