<div class="pb-4 mx-4" id="notification-wrapper">
    <div id="unread-notification-dialog"
        class="w-full bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded shadow-md transition opacity-0"
        role="alert">
        <div class="flex items-center">
            <div>
                <strong>Notifikasi Baru!</strong>
                <span class="block sm:inline">
                    Anda memiliki notifikasi yang belum dibaca, silahkan cek
                    <a href="{{ route('notifications.index') }}" class="underline hover:text-blue-700"><span class="font-medium">di sini</span></a>.
                </span>
            </div>
            <button onclick="dismissNotificationDialog()" class="ml-auto text-gray-500 hover:text-gray-900 focus:outline-none text-2xl">
                &times;
            </button>
        </div>
    </div>
</div>
