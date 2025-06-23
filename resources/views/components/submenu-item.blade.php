{{-- resources/views/components/submenu-item.blade.php (VERSI KLIK) --}}
@props(['item', 'mainmenu', 'component'])

@if ($component->hasPermission($component->getPermission($mainmenu, $item)))
    @if ($item->children->isNotEmpty())
        {{-- Gunakan @click.away untuk menutup saat klik di luar --}}
        <div x-data="{ open: false }" class="relative" @click.away="open = false">
            {{-- Gunakan @click untuk toggle buka/tutup --}}
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100 rounded-md">
                <span>{{ $item->name }}</span>
                <svg class="h-4 w-4 transform transition-transform" :class="{'rotate-90': open}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            </button>
            <div x-show="open" x-transition class="absolute top-0 left-full w-56 mt-0 ml-1 bg-white rounded-lg shadow-xl ring-1 ring-black ring-opacity-5" style="display: none;">
                <div class="py-1">
                    @foreach ($item->children as $child)
                        <x-submenu-item :item="$child" :mainmenu="$mainmenu" :component="$component" />
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <a href="{{ $component->getUrl($component->generateRoute($mainmenu, $item)) }}"
           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md {{ $component->isActive($mainmenu, $item) ? 'bg-red-50 text-red-700 font-medium' : '' }}">
            {{ $item->name }}
        </a>
    @endif
@endif  