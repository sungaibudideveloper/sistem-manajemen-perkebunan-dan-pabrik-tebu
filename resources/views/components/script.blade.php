<script>
    window.addEventListener('load', function() {

        updateNavbarDot();
    });

    function updateNavbarDot() {
        const url = '{{ route('notifications.unread-count') }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const navbarDots = [
            document.getElementById('notification-dot'),
            document.getElementById('notification-dot-mobile')
        ];

        fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                const unreadCount = data.unread_count || 0;
                const isNotificationPage = window.location.pathname.includes('{{ route('notifications.index') }}');
                const dialog = document.getElementById('unread-notification-dialog');

                if (dialog) {
                    unreadCount > 0 && !isNotificationPage ? showNotificationDialog() : dismissNotificationDialog();
                }

                navbarDots.forEach(dot => {
                    if (dot) {
                        dot.textContent = unreadCount;
                        dot.style.display = unreadCount > 0 ? 'inline-block' : 'none';
                    }
                });
            })
            .catch(error => console.error('Error fetching unread notifications count:', error));
    }


    function showNotificationDialog() {
        const dialog = document.getElementById('unread-notification-dialog');
        dialog.classList.remove('invisible', 'opacity-0');
        dialog.classList.add('opacity-100');
    }

    function dismissNotificationDialog() {
        const dialog = document.getElementById('unread-notification-dialog');
        const wrapper = document.getElementById('notification-wrapper');
        dialog.classList.add('invisible', 'opacity-0');
        dialog.classList.remove('opacity-100');
        dialog.style.display = 'none';
        wrapper.style.display = 'none';
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let paginationContainer = document.getElementById("pagination-links");

        if (paginationContainer) {
            paginationContainer.addEventListener("click", function (event) {
                event.preventDefault();

                let target = event.target;
                if (target.tagName === "A") {
                    let url = target.href;
                    fetchData(url);
                }
            });
        }

        function fetchData(url) {
            fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
                .then(response => response.text())
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, "text/html");
                    let newTable = doc.querySelector("#tables");
                    document.querySelector("#tables").innerHTML = newTable.innerHTML;

                    let newPagination = doc.querySelector("#pagination-links");
                    document.querySelector("#pagination-links").innerHTML = newPagination.innerHTML;
                })
                .catch(error => console.error("Error fetching data:", error));
        }
    });
</script>

<script>
    if (document.getElementById("sorting-table") && typeof simpleDatatables.DataTable !== 'undefined') {
        const dataTable = new simpleDatatables.DataTable("#sorting-table", {
            searchable: false,
            perPageSelect: false,
            ordering: true,
            paging: false
        });
    }
</script>

<script>
    document.querySelectorAll('.numeric-input').forEach((input) => {
        input.addEventListener('input', function(e) {
            const value = e.target.value;
            if (!/^\d*\.?\d{0,2}$/.test(value)) {
                e.target.value = value.slice(0, -1);
            }
        });
    });
</script>

<script>
    window.addEventListener('scroll', function() {
        const scrollToTopButton = document.getElementById('scrollToTop');
        if (window.scrollY > 100) {
            scrollToTopButton.style.display = 'block';
        } else {
            scrollToTopButton.style.display = 'none';
        }
    });

    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
</script>
