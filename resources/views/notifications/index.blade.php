<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $title }}</x-slot:navbar>

    @if (auth()->user() && in_array('Create Notifikasi', json_decode(auth()->user()->permissions ?? '[]')))
        <a href="{{ route('notifications.create') }}"
            class="flex items-center text-sm w-fit bg-blue-500 rounded-md shadow-sm text-white py-2 px-4 font-medium hover:bg-blue-600">
            <svg class="-ml-1 w-5 h-5 text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M17.133 12.632v-1.8a5.406 5.406 0 0 0-4.154-5.262.955.955 0 0 0 .021-.106V3.1a1 1 0 0 0-2 0v2.364a.955.955 0 0 0 .021.106 5.406 5.406 0 0 0-4.154 5.262v1.8C6.867 15.018 5 15.614 5 16.807 5 17.4 5 18 5.538 18h12.924C19 18 19 17.4 19 16.807c0-1.193-1.867-1.789-1.867-4.175ZM8.823 19a3.453 3.453 0 0 0 6.354 0H8.823Z" />
            </svg>
            <span class="ml-2">
                Add Notification
            </span>
        </a>
    @endif

    <div class="py-3">
        <ol id="data-list">
            @foreach ($notif as $index => $item)
                <div class="py-1">
                    <li
                        class="p-3 bg-white rounded-md border border-gray-300 shadow-md relative {{ $index >= 5 ? 'hide' : '' }}">
                        <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-red-500"
                            id="dot-{{ $item->id }}" data-notif-id="{{ $item->id }}"></span>

                        <div class="flex items-center justify-between gap-2">
                            <div class="flex gap-2 items-center">
                                <span class="lg:text-sm text-xs text-gray-500">{{ $item->createdat->diffForHumans() }}</span>
                                @if (auth()->user() && in_array('Admin', json_decode(auth()->user()->permissions ?? '[]')))
                                    <span class="font-medium text-sm text-gray-500"> - </span>
                                    <div class="text-gray-500 text-xs font-medium">
                                        {{ $item->companycode }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                @if (auth()->user() && in_array('Edit Notifikasi', json_decode(auth()->user()->permissions ?? '[]')))
                                    <a href="{{ route('notifications.edit', $item->id) }}" class="group flex">
                                        <svg class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden"
                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2"
                                                d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z" />
                                        </svg>
                                        <svg class="w-6 h-6 text-blue-500 dark:text-white hidden group-hover:block"
                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd"
                                                d="M19.846 4.318a2.148 2.148 0 0 0-.437-.692 2.014 2.014 0 0 0-.654-.463 1.92 1.92 0 0 0-1.544 0 2.014 2.014 0 0 0-.654.463l-.546.578 2.852 3.02.546-.579a2.14 2.14 0 0 0 .437-.692 2.244 2.244 0 0 0 0-1.635ZM17.45 8.721 14.597 5.7 9.82 10.76a.54.54 0 0 0-.137.27l-.536 2.84c-.07.37.239.696.588.622l2.682-.567a.492.492 0 0 0 .255-.145l4.778-5.06Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                @endif
                                @if (auth()->user() && in_array('Hapus Notifikasi', json_decode(auth()->user()->permissions ?? '[]')))
                                    <form action="{{ route('notifications.destroy', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('Yakin ingin menghapus notifikasi ini?')"
                                            class="group flex">
                                            <svg class="w-6 h-6 text-red-500 dark:text-white group-hover:hidden"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                                height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2"
                                                    d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                            </svg>
                                            <svg class="w-6 h-6 text-red-500 dark:text-white hidden group-hover:block"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                                height="24" fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd"
                                                    d="M8.586 2.586A2 2 0 0 1 10 2h4a2 2 0 0 1 2 2v2h3a1 1 0 1 1 0 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a1 1 0 0 1 0-2h3V4a2 2 0 0 1 .586-1.414ZM10 6h4V4h-4v2Zm1 4a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Zm4 0a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <div class="gap-2">
                            <div>
                                <h2 class="lg:text-lg text-sm font-bold tracking-tight text-gray-900 dark:text-white">
                                    {{ $item->title }}</h2>
                                <div class="flex items-center justify-between">

                                    <div>
                                        <p class="font-light text-sm text-gray-500 dark:text-gray-400">
                                            {{ Str::limit($item->body, 100) }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <button onclick="showModal('{{ $item->id }}')"
                                            class="inline-flex items-center font-medium text-primary-600 dark:text-primary-500 hover:underline text-xs lg:text-sm">
                                            Read more
                                            <svg class="ml-2 lg:w-4 lg:h-4 w-3 h-3" fill="currentColor" viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd"
                                                    d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </li>
                </div>
            @endforeach
        </ol>
    </div>
    @if ($notifCount > 5)
        <div class="text-center pb-2">
            <button id="more-lists-btn" class="text-blue-600 hover:underline">More Notifications
                &raquo;</button>
            <button id="less-lists-btn" class="hide pt-4 text-blue-600 hover:underline">Less Notifications
                &laquo;</button>
        </div>
    @endif

    <div id="modal"
        class="fixed inset-0 z-50 flex top-0 left-0 w-full h-full bg-black bg-opacity-50 invisible items-center justify-center">
        <div id="modal-content"
            class="bg-white rounded-lg shadow-lg p-6 w-11/12 md:w-1/2 transform opacity-0 scale-90 transition-all duration-300">
            <div class="flex justify-between items-center">
                <h3 id="modal-title" class="text-xl font-semibold"></h3>
                <button onclick="closeModal()" class="text-gray-600 hover:text-gray-900 text-3xl">&times;</button>
            </div>
            <div id="modal-body" class="mt-4 text-gray-600"></div>
            <div class="mt-6 text-right">
                <button onclick="closeModal()" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Close
                </button>
            </div>
        </div>
    </div>
    <style>
        .hide {
            display: none;
        }
    </style>

    <script>
        function showModal(id) {
            const notif = @json($notif);
            const item = notif.find(n => n.id === parseInt(id));

            if (item) {
                document.getElementById('modal-title').innerText = item.title;
                document.getElementById('modal-body').innerText = item.body;

                const modal = document.getElementById('modal');
                const modalContent = document.getElementById('modal-content');

                modal.classList.remove('invisible');
                modal.classList.add('visible');

                setTimeout(() => {
                    modalContent.classList.add('opacity-100', 'scale-100');
                    modalContent.classList.remove('opacity-0', 'scale-90');
                }, 10);

                markAsRead(id);
            }

        }

        function closeModal() {
            const modal = document.getElementById('modal');
            const modalContent = document.getElementById('modal-content');

            modalContent.classList.add('opacity-0', 'scale-90');
            modalContent.classList.remove('opacity-100', 'scale-100');

            setTimeout(() => {
                modal.classList.add('invisible');
                modal.classList.remove('visible');
            }, 300);
            updateNavbarDot();
        }

        function markAsRead(id) {
            const url = `{{ route('notifications.read', ':id') }}`.replace(':id', id);

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                })
                .then(response => {
                    if (response.ok) {
                        const dot = document.querySelector(`[data-notif-id='${id}']`);
                        if (dot) {
                            dot.classList.add('hidden');
                        }
                    } else {
                        console.error('Failed to mark notification as read.');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        window.addEventListener('load', function() {
            const currentUser = document.querySelector('meta[name="current-username"]').getAttribute('content');

            document.querySelectorAll('.bg-red-500').forEach(dot => {
                const notifId = dot.getAttribute('data-notif-id');
                const notif = @json($notif).find(n => n.id === parseInt(notifId));

                if (notif && notif.readby && JSON.parse(notif.readby).includes(currentUser)) {
                    dot.classList.add('hidden');
                }
            });
            updateNavbarDot();
        });
    </script>

    <script>
        const moreListsBtn = document.getElementById('more-lists-btn');
        const lessListsBtn = document.getElementById('less-lists-btn');

        moreListsBtn.addEventListener('click', function() {
            document.querySelectorAll('#data-list .hide').forEach(function(item) {
                item.classList.remove('hide');
            });

            moreListsBtn.style.display = 'none';
            lessListsBtn.style.display = 'inline-block';
        });

        lessListsBtn.addEventListener('click', function() {
            const items = document.querySelectorAll('#data-list li');
            items.forEach(function(item, index) {
                if (index >= 5) {
                    item.classList.add('hide');
                }
            });

            moreListsBtn.style.display = 'inline-block';
            lessListsBtn.style.display = 'none';
        });
    </script>

</x-layout>
