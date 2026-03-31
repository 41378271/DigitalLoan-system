<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Upload KYC | KashFlow</title>
    <?php include '../partials/head.php'; ?>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased overflow-hidden">

    <?php include '../partials/navbar.php'; ?>
    <?php include '../partials/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="md:ml-64 pt-16 h-screen overflow-y-auto pb-24 md:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Upload KYC Documents</h1>
                <p class="text-sm text-gray-500 mt-1">Verify your identity to unlock higher loan limits and faster approvals.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Main Form Column -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <form id="kycForm" class="p-6 sm:p-8" enctype="multipart/form-data">
                            
                            <!-- Document Type Selector -->
                            <div class="mb-8">
                                <label for="doc_type" class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i data-lucide="file-text" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <select id="doc_type" name="doc_type" required class="block w-full pl-12 pr-10 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors sm:text-sm outline-none appearance-none font-medium text-gray-700">
                                        <option value="" disabled selected>Select a document type to upload</option>
                                        <option value="national_id">National ID Card</option>
                                        <option value="passport">International Passport</option>
                                        <option value="drivers_license">Driver's License</option>
                                        <option value="proof_of_address">Proof of Address (Utility Bill)</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-500">
                                        <i data-lucide="chevron-down" class="w-5 h-5"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Drag and Drop Upload Zone -->
                            <div class="mb-8">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Upload File</label>
                                <div id="dropZone" class="mt-1 flex justify-center px-6 pt-10 pb-12 border-2 border-gray-300 border-dashed rounded-2xl transition-all duration-200 hover:border-brand-500 hover:bg-brand-50 bg-gray-50 relative group cursor-pointer group">
                                    <div class="space-y-2 text-center" id="uploadStateUI">
                                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm mb-4 border border-gray-100 group-hover:scale-110 transition-transform">
                                            <i data-lucide="upload-cloud" class="h-8 w-8 text-brand-600"></i>
                                        </div>
                                        <div class="flex text-sm text-gray-600 justify-center font-medium">
                                            <label for="kyc_file" class="relative cursor-pointer rounded-md bg-transparent text-brand-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-brand-500 focus-within:ring-offset-2 hover:text-brand-500">
                                                <span>Click to upload</span>
                                                <input id="kyc_file" name="kyc_file" type="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png" required>
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-2">PNG, JPG, PDF up to 10MB</p>
                                    </div>
                                    
                                    <!-- Selected state (Hidden initially) -->
                                    <div id="fileSelectedUI" class="hidden flex-col items-center justify-center space-y-3 w-full">
                                        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-2 text-emerald-600 shadow-sm border border-emerald-200">
                                            <i data-lucide="file-check-2" class="h-8 w-8"></i>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900 truncate max-w-xs" id="fileNameDisplay">filename.pdf</p>
                                        <p class="text-xs text-brand-600 font-medium hover:text-brand-800 cursor-pointer" id="changeFileBtn">Choose a different file</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                                <button type="reset" onclick="resetUI()" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">Clear</button>
                                <button type="submit" id="submitBtn" class="px-8 py-2.5 bg-brand-600 text-white rounded-xl text-sm font-semibold hover:bg-brand-700 shadow-sm transition-colors flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                                    Upload Document <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Guidance / Info Column -->
                <div class="lg:col-span-1">
                    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 relative overflow-hidden">
                        <!-- Decorative background -->
                        <div class="absolute right-0 top-0 w-32 h-32 bg-blue-500 opacity-5 rounded-bl-full -mr-8 -mt-8 pointer-events-none"></div>
                        
                        <h3 class="text-blue-900 font-bold text-lg mb-4 flex items-center gap-2">
                            <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
                            Upload Guidelines
                        </h3>
                        
                        <ul class="space-y-4 text-sm text-blue-800">
                            <li class="flex items-start gap-3">
                                <i data-lucide="check-circle" class="w-5 h-5 text-blue-500 shrink-0 mt-0.5"></i>
                                <span>Ensure the document is clear, well-lit, and all text is easily readable.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i data-lucide="check-circle" class="w-5 h-5 text-blue-500 shrink-0 mt-0.5"></i>
                                <span>All four corners of the document must be visible within the frame.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i data-lucide="check-circle" class="w-5 h-5 text-blue-500 shrink-0 mt-0.5"></i>
                                <span>For ID cards, you may upload a PDF containing both the front and back, or merge them into a single image.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i data-lucide="check-circle" class="w-5 h-5 text-blue-500 shrink-0 mt-0.5"></i>
                                <span>Documents must be valid and not expired.</span>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();

        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('kyc_file');
        const uploadStateUI = document.getElementById('uploadStateUI');
        const fileSelectedUI = document.getElementById('fileSelectedUI');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const changeFileBtn = document.getElementById('changeFileBtn');
        const form = document.getElementById('kycForm');
        const submitBtn = document.getElementById('submitBtn');

        // Drag and Drop Effects
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('border-brand-500', 'bg-brand-50');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('border-brand-500', 'bg-brand-50');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files; // Assign files to input
                updateFileUI(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                updateFileUI(e.target.files[0]);
            }
        });

        function updateFileUI(file) {
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);
            fileNameDisplay.innerText = `${file.name} (${sizeMB} MB)`;
            uploadStateUI.classList.add('hidden');
            fileSelectedUI.classList.remove('hidden');
            fileSelectedUI.classList.add('flex');
        }

        function resetUI() {
            fileInput.value = '';
            uploadStateUI.classList.remove('hidden');
            fileSelectedUI.classList.add('hidden');
            fileSelectedUI.classList.remove('flex');
            // reset select is handled natively by form type="reset" if triggered that way
        }

        changeFileBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // prevent triggering dropzone click
            fileInput.click();
        });

        // Trigger file input click when clicking anywhere on dropzone EXCEPT the change button
        dropZone.addEventListener('click', (e) => {
            if(e.target !== changeFileBtn && e.target !== fileInput) {
                fileInput.click();
            }
        });

        // Form Submit
        form.addEventListener("submit", async function(e) {
            e.preventDefault();

            if(fileInput.files.length === 0){
                showToast("Please select a file to upload.", "error");
                return;
            }

            const docType = document.getElementById('doc_type').value;
             if(!docType){
                showToast("Please select a document type.", "error");
                return;
            }

            const formData = new FormData(this);
            
            // UI Loading state
            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = `<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Uploading...`;
            lucide.createIcons();

            try {
                // Determine base URL depending on if we use apiCall helper or raw fetch.
                // We'll use robust raw fetch matching legacy paths.
                const res = await fetch("<?= $basePath ?? '' ?>/backend/api/kyc/upload.php", {
                    method: "POST",
                    body: formData
                });

                const text = await res.text();
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (jsonErr) {
                    console.error("Non-JSON Response: ", text);
                    showToast("Server returned invalid response. Check console.", "error");
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    lucide.createIcons();
                    return;
                }

                if (data.success || data.message.toLowerCase().includes('success')) {
                   // Success
                   showToast(data.message || "Document uploaded successfully", "success");
                   form.reset();
                   resetUI();
                   
                   // optional redirect after a moment
                   setTimeout(() => {
                        window.location.href = "borrower_dashboard.php";
                   }, 2000);
                } else {
                   // Application error
                   showToast(data.message || "Upload failed", "error");
                   submitBtn.disabled = false;
                   submitBtn.innerHTML = originalText;
                   lucide.createIcons();
                }

            } catch (err) {
                console.error("Upload error:", err);
                showToast("Network request failed.", "error");
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>