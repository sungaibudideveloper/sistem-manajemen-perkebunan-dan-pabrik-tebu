@props(['active' => false])
<a {{ $attributes }}
    class="{{ $active ? 'bg-gray-100 text-blue-500 m-2' : 'text-gray-700 hover:bg-gray-50 m-2' }} block px-4 py-2 text-sm rounded-md"
    aria-current="{{ $active ? 'page' : false }}">{{ $slot }}</a>
