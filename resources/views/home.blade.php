<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $title }}</x-slot:navbar>

    <x-dialog-bar></x-dialog-bar>

    <div class="flex gap-1 justify-end text-gray-800 mx-4">
        <svg class="w-6 h-6 text-green-700 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
            width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z" />
        </svg>
        <div class="text-right mb-4 font-bold">Periode :
            <span class="font-medium text-gray-600">
                {{ $period ?? 'N/A' }} s.d {{ $now }}
            </span>
        </div>
    </div>

    <section class="relative bg-cover bg-center py-16 rounded-md shadow-2xl mx-4"
        style="background-image: url('{{ asset('img/bg/10.jpg') }}');">
        <div class="absolute inset-0 bg-black bg-opacity-50 rounded-md"></div>
        <div class="relative mx-auto px-6 text-center">
            <h1 class="text-4xl font-bold text-white leading-tight mb-4">Welcome Back, {{ $user }}!</h1>
            <p class="text-gray-200 mb-8">Monitoring the sugarcane plantation ensures healthy growth and prevents pests.
            </p>
        </div>
    </section>

    <div x-data="{ showModal: {{ $showPopup ? 'true' : 'false' }} }" x-show="showModal" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" x-cloak>
        <div class="bg-white rounded-lg shadow-lg w-96">

            <div class="flex justify-between items-center border-b border-gray-200 px-6 py-3">
                <h5 class="text-lg font-semibold">Select Your Company</h5>
            </div>
            <form action="{{ route('setSession') }}" method="POST">
                @csrf
                <div class="px-6 py-4">
                    <label class="block text-sm font-medium mb-2">Pilih Company</label>
                    <select name="dropdown_value" class="w-full border border-gray-300 rounded p-2" required>
                        <option value="" disabled selected>--Pilih Company--</option>
                        @foreach ($company as $comp)
                            <option value="{{ $comp }}" class="text-black"
                                {{ $comp == session('companycode') ? 'selected' : '' }}>
                                {{ $comp }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end border-t border-gray-200 px-4 py-2">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        OK
                    </button>
                </div>
            </form>
        </div>
    </div>

    <section id="features" class="pt-10 pb-4">
        <div class="mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800">Features</h2>
                <p class="text-gray-600">Here are the aspects we observed in this monitoring information system.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <a href="{{ route('input.agronomi.index') }}">
                    <div class="bg-white shadow-lg rounded-lg p-6 text-center">
                        <div class="mb-4">
                            <i class="fas fa-seedling text-green-500 text-5xl"></i> 
                        </div>
                        <h3 class="font-bold text-lg">Agronomi</h3>
                        <p class="text-gray-600">Gather insights on plant growth, soil conditions, and weed competition
                            for
                            sugarcane crops.</p>
                    </div>
                </a>
                <a href="{{ route('input.hpt.index') }}">
                    <div class="bg-white shadow-lg rounded-lg p-6 text-center">
                        <div class="mb-4">
                            <i class="fas fa-bug text-red-500 text-5xl"></i>
                        </div>
                        <h3 class="font-bold text-lg">HPT</h3>
                        <p class="text-gray-600">Analyze pest infestations and damage from sugarcane borers and other
                            diseases.</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if ({{ $showPopup ? 'true' : 'false' }}) {
                document.querySelector('[x-data]').__x.$data.showModal = true;
            }
        });
    </script>

    <style>
        select:invalid {
            color: gray;
        }
    </style>

</x-layout>
