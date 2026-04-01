<?php  
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: /login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Loan Applications | Admin | KashFlow</title>
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
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Loan Applications Review</h1>
                    <p class="text-sm text-gray-500 mt-1">Review borrower applications, verify collateral, and process approvals.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <button onclick="loadLoans()" class="text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 transition-colors px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 shadow-sm border border-brand-100">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i> Refresh List
                    </button>
                </div>
            </div>

            <!-- Global Loading State -->
            <div id="loadingState" class="flex flex-col items-center justify-center py-20">
                <i data-lucide="loader-2" class="w-10 h-10 animate-spin text-brand-500 mb-4"></i>
                <p class="text-gray-500 font-medium">Loading applications...</p>
            </div>

            <!-- Main Content Container -->
            <div id="contentContainer" class="hidden">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    
                    <!-- Table Header / Filters area -->
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-semibold text-gray-800">Pending & Active Applications</h3>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="loansTable">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                                    <th class="px-6 py-4">Borrower</th>
                                    <th class="px-6 py-4">Loan Details</th>
                                    <th class="px-6 py-4">Collateral Info</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100" id="loansTableBody">
                                <!-- Rows injected here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="hidden flex-col items-center justify-center py-16 px-4 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Queue Empty</h3>
                        <p class="text-gray-500 text-sm max-w-sm">There are no loan applications requiring your review at this time.</p>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <!-- Document Preview Modal (Reused pattern) -->
    <div id="previewModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closePreviewModal()"></div>
        
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl flex flex-col max-h-[90vh]">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 py-4 border-b border-gray-100 sm:px-6 flex justify-between items-center shrink-0">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Collateral Preview</h3>
                            <p class="text-sm text-gray-500" id="previewSubtitle">Loading...</p>
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
                        <button type="button" id="modalVerifyBtn" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:w-auto transition-colors flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-4 h-4"></i> Verify Collateral
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
        const loansTableBody = document.getElementById('loansTableBody');
        const emptyState = document.getElementById('emptyState');
        const loansTable = document.getElementById('loansTable').parentNode;

        // Formatter functions
        const formatMoney = (val) => new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(val);

        function formatLoanStatus(status) {
            const s = (status || "").toLowerCase();
            if (s === 'approved') return `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider"><i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Approved</span>`;
            if (s === 'rejected') return `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200 uppercase tracking-wider"><i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Rejected</span>`;
            if (s === 'pending') return `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider"><i data-lucide="clock" class="w-3.5 h-3.5"></i> Pending</span>`;
            return `<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200 uppercase tracking-wider">${status}</span>`;
        }

        function formatCollateralStatus(status) {
            const s = (status || "").toLowerCase();
            if (s === 'verified') return `<span class="text-xs font-medium text-emerald-600 flex items-center gap-1"><i data-lucide="shield-check" class="w-3 h-3"></i> Verified</span>`;
            if (s === 'pending') return `<span class="text-xs font-medium text-amber-600 flex items-center gap-1"><i data-lucide="shield-alert" class="w-3 h-3"></i> Unverified</span>`;
            if (s === 'rejected') return `<span class="text-xs font-medium text-red-600 flex items-center gap-1"><i data-lucide="shield-x" class="w-3 h-3"></i> Rejected</span>`;
            return `<span class="text-xs font-medium text-gray-500">${status || "Not Applicable"}</span>`;
        }

        async function loadLoans() {
            loadingState.classList.remove('hidden');
            contentContainer.classList.add('hidden');
            loansTableBody.innerHTML = '';
            
            try {
                const res = await fetch("<?= $basePath ?? '' ?>/backend/api/admin/loans/list.php");
                const data = await res.json();

                if (!data.success) {
                    showToast(data.message || "Failed to load loans", "error");
                    loadingState.classList.add('hidden');
                    return;
                }

                if (!data.loans || data.loans.length === 0) {
                    emptyState.classList.remove('hidden');
                    loansTable.classList.add('hidden');
                } else {
                    emptyState.classList.add('hidden');
                    loansTable.classList.remove('hidden');
                    
                    data.loans.forEach(loan => {
                        const tr = document.createElement('tr');
                        tr.className = "hover:bg-gray-50/50 transition-colors";
                        
                        const isPending = (loan.status || "").toLowerCase() === 'pending';
                        const colStatus = (loan.collateral_status || "").toLowerCase();
                        const isCollateralVerified = colStatus === 'verified';
                        
                        const proofHtml = loan.proof_file_path
                            ? `<button onclick="openPreviewModal('${loan.proof_file_path}', ${loan.collateral_id}, '${loan.full_name}')" class="text-brand-600 hover:text-brand-800 text-xs font-medium inline-flex items-center gap-1 mt-1"><i data-lucide="paperclip" class="w-3.5 h-3.5"></i> View Proof</button>`
                            : `<span class="text-xs text-gray-400 italic">No proof provided</span>`;

                        // Actions logic
                        let actionsHtml = `<div class="flex flex-col items-end gap-2">`;
                        
                        if(isPending) {
                            // If collateral is needed but not verified
                            if (loan.collateral_id && !isCollateralVerified) {
                                actionsHtml += `
                                    <button onclick="verifyCollateral(${loan.collateral_id})" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1.5 text-xs font-medium w-full justify-center border border-blue-200">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Verify Collateral
                                    </button>
                                `;
                            }
                            
                            // Approval buttons
                            const approveDisabled = (loan.collateral_id && !isCollateralVerified) ? 'disabled opacity-50 cursor-not-allowed' : 'hover:bg-emerald-100 hover:text-emerald-900 bg-emerald-50 text-emerald-700 border-emerald-200';
                            
                            actionsHtml += `
                                <div class="flex gap-2 w-full justify-end">
                                    <button onclick="updateLoan(${loan.id}, 'approved')" class="px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1.5 text-xs font-medium border ${approveDisabled}" ${loan.collateral_id && !isCollateralVerified ? 'disabled title="Verify collateral first"' : 'title="Approve Loan"'}>
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i> Approve
                                    </button>
                                    <button onclick="updateLoan(${loan.id}, 'rejected')" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1.5 text-xs font-medium border border-red-200" title="Reject Loan">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i> Reject
                                    </button>
                                </div>
                            `;
                        } else {
                            actionsHtml += `<span class="text-xs text-gray-500 italic">Actioned</span>`;
                        }
                        
                        actionsHtml += `</div>`;

                        tr.innerHTML = `
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">${loan.full_name || "-"}</div>
                                <div class="text-xs text-gray-500 flex items-center gap-1 mt-0.5"><i data-lucide="phone" class="w-3 h-3"></i> ${loan.phone || "-"}</div>
                                <div class="text-xs text-gray-400 font-mono mt-0.5">App #${loan.id}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">${formatMoney(loan.amount)}</div>
                                <div class="text-xs text-gray-600 mt-0.5">${loan.term_months} Months Term</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-800 capitalize">${(loan.collateral_type || "-").replace('_', ' ')}</div>
                                <div class="text-xs text-gray-500 mt-0.5">Est. Value: ${formatMoney(loan.collateral_value || 0)}</div>
                                <div class="mt-1 flex items-center justify-between">
                                    ${formatCollateralStatus(colStatus)}
                                    ${proofHtml}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ${formatLoanStatus(loan.status)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ${actionsHtml}
                            </td>
                        `;
                        loansTableBody.appendChild(tr);
                    });
                }
                
                loadingState.classList.add('hidden');
                contentContainer.classList.remove('hidden');
                lucide.createIcons();

            } catch (error) {
                console.error("Loans load error:", error);
                showToast("Network error. Could not load applications.", "error");
                loadingState.classList.add('hidden');
            }
        }

        async function verifyCollateral(collateralId) {
            if (!collateralId) return;

            try {
                const res = await fetch("<?= $basePath ?? '' ?>/backend/api/admin/loans/verify_collateral.php", {
                    method: "POST",
                    headers: {"Content-Type": "application/json"},
                    body: JSON.stringify({ collateral_id: collateralId })
                });
                
                const data = await res.json();
                if(data.success) {
                    showToast("Collateral verified successfully.", "success");
                    loadLoans();
                    closePreviewModal();
                } else {
                    showToast(data.message || "Failed to verify collateral", "error");
                }
            } catch (err) {
                console.error(err);
                showToast("Network error", "error");
            }
        }

        async function updateLoan(id, status) {
            try {
                const res = await fetch("<?= $basePath ?? '' ?>/backend/api/admin/loans/update_status.php", {
                    method: "POST",
                    headers: {"Content-Type": "application/json"},
                    body: JSON.stringify({ loan_id: id, status: status })
                });

                const data = await res.json();
                
                if (data.success) {
                    showToast(`Loan ${status} successfully.`, "success");
                    loadLoans();
                } else {
                    showToast(data.message || "Operation failed", "error");
                }
            } catch (err) {
                console.error("Update error:", err);
                showToast("Network error", "error");
            }
        }

        // --- Modal Handling ---
        const previewModal = document.getElementById('previewModal');
        const previewSubtitle = document.getElementById('previewSubtitle');
        const previewImage = document.getElementById('previewImage');
        const previewPdf = document.getElementById('previewPdf');
        const previewLoader = document.getElementById('previewLoader');
        const previewError = document.getElementById('previewError');
        const previewDownloadLink = document.getElementById('previewDownloadLink');
        const modalVerifyBtn = document.getElementById('modalVerifyBtn');

        function openPreviewModal(filePath, collateralId, userName) {
            const fileUrl = "<?= $basePath ?? '' ?>/" + filePath;
            
            previewSubtitle.innerText = `Borrower: ${userName}`;

            // Reset view state
            previewLoader.classList.remove('hidden');
            previewImage.classList.add('hidden');
            previewPdf.classList.add('hidden');
            previewError.classList.add('hidden');
            previewImage.src = "";
            previewPdf.src = "";

            // Setup button
            if(collateralId) {
                modalVerifyBtn.classList.remove('hidden');
                modalVerifyBtn.onclick = () => verifyCollateral(collateralId);
            } else {
                modalVerifyBtn.classList.add('hidden');
            }

            // Detect file type and load
            const ext = fileUrl.split('.').pop().toLowerCase();
            
            previewModal.classList.remove('hidden');
            setTimeout(() => {
                previewModal.querySelector('.bg-gray-900\\/70').classList.add('opacity-100');
                previewModal.querySelector('.transform').classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                previewModal.querySelector('.transform').classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
            }, 10);

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
                previewPdf.onload = () => {
                     previewLoader.classList.add('hidden');
                     previewPdf.classList.remove('hidden');
                };
                setTimeout(() => {
                    previewLoader.classList.add('hidden');
                    previewPdf.classList.remove('hidden');
                }, 800);
                previewPdf.src = fileUrl;
            } else {
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
            }, 300);
        }

        // Init
        document.addEventListener('DOMContentLoaded', loadLoans);
    </script>
</body>
</html>