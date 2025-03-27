<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $title }}</x-slot:navbar>
    <div class="p-4 bg-white shadow-md rounded-md w-full">
        <form action="{{ route('upload.gpx') }}" method="post" enctype="multipart/form-data"
            onsubmit="return validateForm()">
            @csrf
            <div class="mb-4">
                <label class="font-medium block mb-2 text-lg">Select GPX File:</label>
                <div class="flex items-center justify-center w-full">
                    <label for="gpxFile"
                        class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600 relative">

                        <!-- Default Content -->
                        <div id="uploadPlaceholder"
                            class="flex flex-col items-center justify-center pt-5 pb-6 transition-opacity duration-300">
                            <svg class="w-12 h-12 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                            </svg>
                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click
                                    to upload</span> or drag and drop</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">GPX (max. 20MB)</p>
                        </div>

                        <!-- Preview File Container -->
                        <div id="filePreview"
                            class="invisible absolute flex flex-col items-center justify-center transition-opacity duration-300">
                            <img src="https://cdn-icons-png.flaticon.com/512/337/337946.png" alt="File Icon"
                                class="w-12 h-12 mb-2">
                            <p id="fileName" class="text-sm text-gray-700 font-medium"></p>
                        </div>

                        <input id="gpxFile" type="file" name="gpxFile" class="hidden" accept=".gpx" />
                    </label>
                </div>
            </div>

            <div class="mb-2">
                <button type="submit"
                    class="flex items-center gap-2 bg-green-600 rounded-md shadow-sm px-4 py-2 text-white font-medium hover:bg-green-700 justify-end">
                    <svg class="w-5 h-5 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M13.383 4.076a6.5 6.5 0 0 0-6.887 3.95A5 5 0 0 0 7 18h3v-4a2 2 0 0 1-1.414-3.414l2-2a2 2 0 0 1 2.828 0l2 2A2 2 0 0 1 14 14v4h4a4 4 0 0 0 .988-7.876 6.5 6.5 0 0 0-5.605-6.048Z" />
                        <path
                            d="M12.707 9.293a1 1 0 0 0-1.414 0l-2 2a1 1 0 1 0 1.414 1.414l.293-.293V19a1 1 0 1 0 2 0v-6.586l.293.293a1 1 0 0 0 1.414-1.414l-2-2Z" />
                    </svg>

                    <span>
                        Submit
                    </span>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const fileInput = document.getElementById("gpxFile");
            const dropArea = document.querySelector("label[for='gpxFile']");
            const uploadPlaceholder = document.getElementById("uploadPlaceholder");
            const filePreview = document.getElementById("filePreview");
            const fileNameElement = document.getElementById("fileName");

            const handleFile = (file) => {
                if (file) {
                    uploadPlaceholder.classList.add("invisible");
                    filePreview.classList.remove("invisible");
                    fileNameElement.textContent = file.name;
                } else {
                    uploadPlaceholder.classList.remove("invisible");
                    filePreview.classList.add("invisible");
                }
            };

            fileInput.addEventListener("change", (e) => handleFile(e.target.files[0]));

            dropArea.addEventListener("dragover", (e) => {
                e.preventDefault();
                dropArea.classList.add("border-green-500", "bg-green-100");
            });

            dropArea.addEventListener("dragleave", () => dropArea.classList.remove("border-green-500",
                "bg-green-100"));

            dropArea.addEventListener("drop", (e) => {
                e.preventDefault();
                dropArea.classList.remove("border-green-500", "bg-green-100");

                const file = e.dataTransfer.files[0];
                if (file) {
                    fileInput.files = e.dataTransfer.files;
                    handleFile(file);
                }
            });
        });

        function validateForm() {
            const file = document.getElementById("gpxFile").files[0];
            if (!file) return alert("No file selected."), false;

            const fileExtension = file.name.split(".").pop().toLowerCase();
            if (file.type !== "application/gpx+xml" && fileExtension !== "gpx")
                return alert("Please upload a valid GPX file."), false;

            if (file.size > 20 * 1024 * 1024)
                return alert("File size exceeds the maximum limit of 20MB."), false;

            return true;
        }
    </script>


</x-layout>
