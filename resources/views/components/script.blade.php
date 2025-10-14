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
    document.addEventListener("DOMContentLoaded", function() {
        let paginationContainer = document.getElementById("pagination-links");

        if (paginationContainer) {
            paginationContainer.addEventListener("click", function(event) {
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
    document.addEventListener("DOMContentLoaded", function() {
        const dataContainer = document.getElementById("ajax-data");
        if (!dataContainer) return;

        const baseUrl = dataContainer.dataset.url;
        const searchInput = document.getElementById("search");
        const perPageInput = document.getElementById("perPage");
        const startDateInput = document.getElementById("start_date");
        const endDateInput = document.getElementById("end_date");
        const tables = document.getElementById("tables");
        const pages = document.getElementById("pagination-links");

        let timeout = null;

        function fetchData(url = baseUrl) {
            const search = searchInput ? searchInput.value : "";
            const perPage = perPageInput ? perPageInput.value : 10;
            const startDate = startDateInput ? startDateInput.value : "";
            const endDate = endDateInput ? endDateInput.value : "";

            const formData = new FormData();
            formData.append("_token", document.querySelector('meta[name="csrf-token"]').content);
            formData.append("search", search);
            formData.append("perPage", perPage);
            formData.append("start_date", startDate);
            formData.append("end_date", endDate);

            fetch(url, {
                    method: "POST",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, "text/html");

                    const newTable = newDoc.querySelector("#tables");
                    const newPagination = newDoc.querySelector("#pagination-links");

                    if (newTable && newPagination) {
                        tables.innerHTML = newTable.innerHTML;
                        pages.innerHTML = newPagination.innerHTML;
                    }
                })
                .catch(error => console.error("AJAX Fetch Error:", error));
        }

        if (searchInput) {
            searchInput.addEventListener("input", () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => fetchData(), 300);
            });
        }

        if (perPageInput) {
            perPageInput.addEventListener("input", () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => fetchData(), 300);
            });
        }

        if (startDateInput) {
            startDateInput.addEventListener("change", function() {
                fetchData();
            });
        }

        if (endDateInput) {
            endDateInput.addEventListener("change", function() {
                fetchData();
            });
        }

        document.addEventListener("click", function(event) {
            const target = event.target.closest("#pagination-links a");
            if (target) {
                event.preventDefault();
                const url = target.href;
                fetchData(url);
            }
        });
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
