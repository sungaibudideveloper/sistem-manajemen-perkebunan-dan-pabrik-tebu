{{-- resources/views/components/mobile-submenu-item.blade.php --}}
@props(['item', 'mainmenu', 'component'])

@if ($component->hasPermission($component->getPermission($mainmenu, $item)))
    <div class="space-y-1">
        {{-- Jika item ini punya anak, buat tombol akordion baru --}}
        @if($item->children->isNotEmpty())
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 text-sm text-red-100 hover:bg-red-900 hover:text-white transition-colors duration-150 rounded-md">
                    <span>{{ $item->name }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" x-transition class="pl-4 mt-1 space-y-1">
                    {{-- Panggil dirinya sendiri untuk membuat akordion bertingkat --}}
                    @foreach ($item->children as $child)
                        <x-mobile-submenu-item :item="$child" :mainmenu="$mainmenu" :component="$component"/>
                    @endforeach
                </div>
            </div>
        {{-- Jika tidak punya anak, buat link biasa --}}
        @else
            <a href="{{ $component->getUrl($component->generateRoute($mainmenu, $item)) }}" class="block px-4 py-2 text-sm rounded-md text-red-200 hover:bg-red-900 hover:text-white transition-colors duration-150 {{ $component->isActive($mainmenu, $item) ? 'bg-red-900 text-white font-semibold' : '' }}">
                {{ $item->name }}
            </a>
        @endif
    </div>
@endif