<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>KYC Review | Admin | KashFlow</title>
    <?php include '../partials/head.php'; ?>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased overflow-hidden">

    <?php include '../partials/navbar.php'; ?>

    <?php include '../partials/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="md:ml-64 pt-16 h-screen overflow-y-auto pb-24 md:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Page Header -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">KYC Document Review</h1>
                    <p class="text-sm text-gray-500 mt-1">Verify user identities to ensure compliance and security.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <button onclick="loadKyc()" class="text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 transition-colors px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 shadow-sm border border-brand-100">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i> Refresh List
                    </button>
                </div>
            </div>

            <!-- Global Loading State -->
            <div id="loadingState" class="flex flex-col items-center justify-center py-20">
                <i data-lucide="loader-2" class="w-10 h-10 animate-spin text-brand-500 mb-4"></i>
                <p class="text-gray-500 font-medium">Loading documents...</p>
            </div>

            <!-- Main Content Container -->
            <div id="contentContainer" class="hidden">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    
                    <!-- Table Header / Filters area (future) -->
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-semibold text-gray-800">Submitted Documents</h3>
                        <!-- Placeholder for future search/filter -->
                        <div class="text-xs text-gray-500 flex items-center gap-1"><i data-lucide="filter" class="w-3 h-3"></i> Filter</div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="kycTable">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                                    <th class="px-6 py-4">ID</th>
                                    <th class="px-6 py-4">User</th>
                                    <th class="px-6 py-4">Doc Type</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100" id="kycTableBody">
                                <!-- Rows injected here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="hidden flex-col items-center justify-center py-16 px-4 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="shield-check" class="w-8 h-8 text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Queue Empty</h3>
                        <p class="text-gray-500 text-sm max-w-sm">There are no KYC documents requiring your review at this time.</p>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <!-- Document Preview Modal -->
    <div id="previewModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closePreviewModal()"></div>
        
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl flex flex-col max-h-[90vh]">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 py-4 border-b border-gray-100 sm:px-6 flex justify-between items-center shrink-0">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900" id="previewTitle">Document Preview</h3>
                            <p class="text-sm text-gray-500" id="previewSubtitle">User ID: -</p>
                        </div>
                        <button onclick="closePreviewModal()" class="text-gray-400 hover:text-gray-500 bg-gray-50 hover:bg-gray-100 rounded-full p-2 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    
                    <!-- Modal Body (Preview Area) -->
                    <div class="bg-gray-100 p-4 flex-1 overflow-auto flex items-center justify-center min-h-[300px]" id="previewContainer">
                        <div class="text-center text-gray-500 pb-2 flex flex-col items-center justify-center" id="previewLoader">
                            <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-brand-500 mb-2"></i>
                            Loading document...
                        </div>
                        <img id="previewImage" src="" alt="Document Preview" class="max-w-full max-h-[60vh] object-contain rounded-lg shadow-sm hidden">
                        <iframe id="previewPdf" src="" class="w-full h-[60vh] rounded-lg shadow-sm hidden" border="0"></iframe>
                        
                        <div id="previewError" class="hidden text-center text-red-500 flex flex-col items-center">
                            <i data-lucide="alert-triangle" class="w-10 h-10 mb-2 text-red-400"></i>
                            <p>Cannot preview this file type directly.</p>
                            <a id="previewDownloadLink" href="#" target="_blank" class="mt-4 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors inline-flex items-center gap-2 shadow-sm">
                                <i data-lucide="external-link" class="w-4 h-4"></i> Open in new tab
                            </a>
                        </div>
                    </div>

                    <!-- Modal Footer (Actions) -->
                    <div class="bg-gray-50 px-4 py-4 border-t border-gray-100 sm:flex sm:flex-row-reverse sm:px-6 shrink-0 gap-3">
                        <button type="button" id="modalApproveBtn" class="inline-flex w-full justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:w-auto transition-colors flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4"></i> Approve
                        </button>
                        <button type="button" id="modalRejectBtn" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-red-300 hover:bg-red-50 sm:mt-0 sm:w-auto transition-colors flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4 text-red-500"></i> Reject
                        </button>
                        <div class="flex-1"></div> <!-- Spacer -->
                        <button type="button" onclick="closePreviewModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        lucide.createIcons();

        const loadingState = document.getElementById('loadingState');
        const contentContainer = document.getElementById('contentContainer');
        const kycTableBody = document.getElementById('kycTableBody');
        const emptyState = document.getElementById('emptyState');
        const kycTable = document.getElementById('kycTable');

        // Modal Elements
        const previewModal = document.getElementById('previewModal');
        const previewTitle = document.getElementById('previewTitle');
        const previewSubtitle = document.getElementById('previewSubtitle');
        const previewImage = document.getElementById('previewImage');
        const previewPdf = document.getElementById('previewPdf');
        const previewLoader = document.getElementById('previewLoader');
        const previewError = document.getElementById('previewError');
        const previewDownloadLink = document.getElementById('previewDownloadLink');
        const modalApproveBtn = document.getElementById('modalApproveBtn');
        const modalRejectBtn = document.getElementById('modalRejectBtn');

        let currentActiveDocId = null;

        function formatStatusBadge(status) {
            const s = (status || "").toLowerCase();
            if (s === 'approved') return `<span class="inline-flex flex-shrink-0 items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Approved</span>`;
            if (s === 'rejected') return `<span class="inline-flex flex-shrink-0 items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200 uppercase tracking-wider"><i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Rejected</span>`;
            if (s === 'pending') return `<span class="inline-flex flex-shrink-0 items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider"><i data-lucide="clock" class="w-3.5 h-3.5"></i> Pending</span>`;
            return `<span class="inline-flex flex-shrink-0 items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200 uppercase tracking-wider">${status}</span>`;
        }

        function formatDocType(type) {
            const map = {
                'national_id': 'National ID',
                'passport': 'Passport',
                'drivers_license': "Driver's License",
                'proof_of_address': 'Proof of Address'
            };
            return map[type] || type;
        }

        async function loadKyc() {
            loadingState.classList.remove('hidden');
            contentContainer.classList.add('hidden');
            kycTableBody.innerHTML = '';
            
            try {
                const res = await fetch("<?= $basePath ?? '' ?>/backend/api/admin/kyc/list.php");
                const data = await res.json();

                if (!data.success) {
                    showToast(data.message || "Failed to load documents", "error");
                    loadingState.classList.add('hidden');
                    return;
                }

                if (!data.rows || data.rows.length === 0) {
                    emptyState.classList.remove('hidden');
                    kycTable.classList.add('hidden');
                } else {
                    emptyState.classList.add('hidden');
                    kycTable.classList.remove('hidden');
                    
                    data.rows.forEach(r => {
                        const dateStr = new Date(r.uploaded_at).toLocaleDateString('en-GB', { day: 'short', month: 'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
                        const tr = document.createElement('tr');
                        tr.className = "hover:bg-gray-50/50 transition-colors group";
                        
                        const isPending = (r.status || "").toLowerCase() === 'pending';
                        
                        // Pass needed info as data attributes to the row for modal usage
                        tr.setAttribute('data-id', r.id);
                        tr.setAttribute('data-user', r.full_name);
                        tr.setAttribute('data-type', formatDocType(r.doc_type));
                        tr.setAttribute('data-filepath', "<?= $basePath ?? '' ?>/" + r.file_path);

                        let actionsHtml = `
                            <button onclick="openPreviewModal(this)" class="text-brand-600 hover:text-brand-900 bg-brand-50 hover:bg-brand-100 p-2 rounded-lg transition-colors inline-flex items-center gap-1 text-sm font-medium mr-2" title="View Document">
                                <i data-lucide="eye" class="w-4 h-4"></i> View
                            </button>
                        `;

                        if(isPending) {
                            actionsHtml += `
                                <button onclick="updateStatus(${r.id}, 'approved')" class="text-emerald-600 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 p-2 rounded-lg transition-colors inline-flex items-center gap-1 text-sm font-medium mr-2" title="Quick Approve">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </button>
                                <button onclick="updateStatus(${r.id}, 'rejected')" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors inline-flex items-center gap-1 text-sm font-medium" title="Quick Reject">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            `;
                        }

                        tr.innerHTML = `
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">#${r.id}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">${r.full_name}</div>
                                <div class="text-xs text-gray-500">${r.email}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="file-text" class="w-4 h-4 text-gray-400"></i>
                                    ${formatDocType(r.doc_type)}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${dateStr}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${formatStatusBadge(r.status)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                ${actionsHtml}
                            </td>
                        `;
                        kycTableBody.appendChild(tr);
                    });
                }
                
                loadingState.classList.add('hidden');
                contentContainer.classList.remove('hidden');
                lucide.createIcons();

            } catch (error) {
                console.error("KYC load error:", error);
                showToast("Network error. Could not load documents.", "error");
                loadingState.classList.add('hidden');
            }
        }

        async function updateStatus(id, status) {
            try {
                const fd = new FormData();
                fd.append('id', id);
                fd.append('status', status);

                const res = await fetch("<?= $basePath ?? '' ?>/backend/api/admin/kyc/update_status.php", {
                    method: "POST",
                    body: fd
                });
                
                const data = await res.json();
                
                if (data.success) {
                    showToast(`Document ${status} successfully.`, "success");
                    loadKyc(); // reload table
                    
                    // If modal is open for this doc, close it
                    if(!previewModal.classList.contains('hidden') && currentActiveDocId == id) {
                        closePreviewModal();
                    }
                } else {
                    showToast(data.message || "Operation failed", "error");
                }
            } catch (err) {
                console.error("Update error:", err);
                showToast("Network error", "error");
            }
        }

        // --- Modal Handling ---
        function openPreviewModal(btnEl) {
            const tr = btnEl.closest('tr');
            currentActiveDocId = tr.getAttribute('data-id');
            const userName = tr.getAttribute('data-user');
            const docType = tr.getAttribute('data-type');
            const fileUrl = tr.getAttribute('data-filepath');

            // Set text
            previewTitle.innerText = `${docType}`;
            previewSubtitle.innerText = `Submitted by: ${userName} (ID: #${currentActiveDocId})`;

            // Reset view state
            previewLoader.classList.remove('hidden');
            previewImage.classList.add('hidden');
            previewPdf.classList.add('hidden');
            previewError.classList.add('hidden');
            previewImage.src = "";
            previewPdf.src = "";

            // Setup buttons
            modalApproveBtn.onclick = () => updateStatus(currentActiveDocId, 'approved');
            modalRejectBtn.onclick = () => updateStatus(currentActiveDocId, 'rejected');

            // Detect file type and load
            const ext = fileUrl.split('.').pop().toLowerCase();
            
            // show modal early to start animation
            previewModal.classList.remove('hidden');
            setTimeout(() => {
                previewModal.querySelector('.bg-gray-900\\/70').classList.add('opacity-100');
                previewModal.querySelector('.transform').classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                previewModal.querySelector('.transform').classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
            }, 10);

            // Load content
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                previewImage.onload = () => {
                    previewLoader.classList.add('hidden');
                    previewImage.classList.remove('hidden');
                };
                previewImage.onerror = () => {
                    previewLoader.classList.add('hidden');
                    showError(fileUrl);
                };
                previewImage.src = fileUrl;
            } else if (ext === 'pdf') {
                // PDFs are tricky in iframes occasionally, but we'll try
                previewPdf.onload = () => {
                     previewLoader.classList.add('hidden');
                     previewPdf.classList.remove('hidden');
                };
                 // some browsers fire onload immediately for iframe, add small delay just in case
                setTimeout(() => {
                    previewLoader.classList.add('hidden');
                    previewPdf.classList.remove('hidden');
                }, 800);
                previewPdf.src = fileUrl;
            } else {
                // Unknown type, just offer download
                 previewLoader.classList.add('hidden');
                 showError(fileUrl);
            }
        }

        function showError(fileUrl) {
            previewError.classList.remove('hidden');
            previewDownloadLink.href = fileUrl;
        }

        function closePreviewModal() {
            previewModal.querySelector('.bg-gray-900\\/70').classList.remove('opacity-100');
            previewModal.querySelector('.transform').classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
            previewModal.querySelector('.transform').classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            
            setTimeout(() => {
                previewModal.classList.add('hidden');
                previewImage.src = "";
                previewPdf.src = "";
                currentActiveDocId = null;
            }, 300);
        }

        // Init
        document.addEventListener('DOMContentLoaded', loadKyc);
    </script>
</body>
</html>