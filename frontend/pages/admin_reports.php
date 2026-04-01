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
    <title>System Reports | Admin | KashFlow</title>
    <?php include '../partials/head.php'; ?>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased overflow-hidden">

    <?php include '../partials/navbar.php'; ?>

    <?php include '../partials/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="md:ml-64 pt-16 h-screen overflow-y-auto pb-24 md:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Page Header -->
            <div class="mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">System Reports</h1>
                    <p class="text-sm text-gray-500 mt-1">Platform analytics, financial summaries, and data exports.</p>
                </div>
                
                <!-- Export Actions -->
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <a href="<?= $basePath ?? '' ?>/backend/api/admin/reports/export_loans_csv.php" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                        <i data-lucide="download" class="w-4 h-4 text-gray-400"></i> Export Loans
                    </a>
                    <a href="<?= $basePath ?? '' ?>/backend/api/admin/reports/export_kyc_csv.php" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                        <i data-lucide="download" class="w-4 h-4 text-gray-400"></i> Export KYC
                    </a>
                </div>
            </div>

            <!-- Global Loading State -->
            <div id="loadingState" class="flex flex-col items-center justify-center py-20">
                <i data-lucide="loader-2" class="w-10 h-10 animate-spin text-brand-500 mb-4"></i>
                <p class="text-gray-500 font-medium">Crunching the numbers...</p>
            </div>

            <div id="contentContainer" class="hidden space-y-8">
                
                <!-- Financial Overview Section -->
                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2"><i data-lucide="landmark" class="w-5 h-5 text-emerald-600"></i> Financial Overview</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Card 1 -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-start gap-4 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <i data-lucide="banknote" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Total Loan Amount</p>
                                <h3 class="text-2xl font-black text-gray-900 tracking-tight" id="statTotalAmount">Kes 0.00</h3>
                            </div>
                        </div>
                        
                        <!-- Card 2 -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-start gap-4 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Approved Amount</p>
                                <h3 class="text-2xl font-black text-gray-900 tracking-tight" id="statApprovedAmount">Kes 0.00</h3>
                            </div>
                        </div>

                        <!-- Card 3 -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-start gap-4 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                                <i data-lucide="x-circle" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Rejected Amount</p>
                                <h3 class="text-2xl font-black text-gray-900 tracking-tight" id="statRejectedAmount">Kes 0.00</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KYC & User Metrics Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- User & KYC Stats -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-semibold text-gray-800 flex items-center gap-2"><i data-lucide="users" class="w-4 h-4 text-blue-500"></i> Platform Adoption</h3>
                        </div>
                        <div class="p-6 grid grid-cols-2 gap-4 flex-1">
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2">Total Users</div>
                                <div class="text-3xl font-bold text-gray-900" id="statTotalUsers">0</div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2">Total KYC Docs</div>
                                <div class="text-3xl font-bold text-gray-900" id="statTotalKYC">0</div>
                            </div>
                            <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100">
                                <div class="text-emerald-700 text-xs font-semibold uppercase tracking-wider mb-2">Approved KYC</div>
                                <div class="text-3xl font-bold text-emerald-900" id="statApprovedKYC">0</div>
                            </div>
                            <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
                                <div class="text-amber-700 text-xs font-semibold uppercase tracking-wider mb-2">Pending KYC</div>
                                <div class="text-3xl font-bold text-amber-900" id="statPendingKYC">0</div>
                            </div>
                        </div>
                    </div>

                    <!-- Loan Application Stats -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-semibold text-gray-800 flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4 text-purple-500"></i> Application Volume</h3>
                        </div>
                        <div class="p-6 grid grid-cols-2 gap-4 flex-1">
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 col-span-2 sm:col-span-1">
                                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2">Total Loans</div>
                                <div class="text-3xl font-bold text-gray-900" id="statTotalLoans">0</div>
                            </div>
                            <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100 col-span-2 sm:col-span-1">
                                <div class="text-emerald-700 text-xs font-semibold uppercase tracking-wider mb-2">Approved</div>
                                <div class="text-3xl font-bold text-emerald-900" id="statApprovedLoans">0</div>
                            </div>
                            <div class="bg-amber-50 rounded-xl p-4 border border-amber-100 col-span-2">
                                <div class="text-amber-700 text-xs font-semibold uppercase tracking-wider mb-2 flex items-center gap-2">
                                    Pending Review <i data-lucide="clock" class="w-4 h-4"></i>
                                </div>
                                <div class="text-4xl font-black text-amber-900" id="statPendingLoans">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Tables Grid -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    <!-- Monthly Statistics -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-semibold text-gray-800 flex items-center gap-2"><i data-lucide="calendar" class="w-4 h-4 text-brand-500"></i> Monthly Origination</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                                        <th class="px-6 py-3">Month</th>
                                        <th class="px-6 py-3 text-right">Volume</th>
                                        <th class="px-6 py-3 text-right">Value (KES)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100" id="monthlyBody">
                                    <!-- Rows injected -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Latest Loans -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-semibold text-gray-800 flex items-center gap-2"><i data-lucide="history" class="w-4 h-4 text-brand-500"></i> Recent Activity</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                                        <th class="px-6 py-3">Borrower</th>
                                        <th class="px-6 py-3 text-right">Amount</th>
                                        <th class="px-6 py-3 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100" id="loansBody">
                                    <!-- Rows injected -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <script>
        lucide.createIcons();

        const loadingState = document.getElementById('loadingState');
        const contentContainer = document.getElementById('contentContainer');

        const fmtMoney = (v) => new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(v || 0);
        
        const safeInt = (v) => parseInt(v) || 0;

        function formatLoanStatus(status) {
            const s = (status || "").toLowerCase();
            if (s === 'approved') return `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">Approved</span>`;
            if (s === 'rejected') return `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 border border-red-200">Rejected</span>`;
            if (s === 'pending') return `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">Pending</span>`;
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">${status}</span>`;
        }

        async function loadReports(){
            try {
                const res = await fetch("<?= $basePath ?? '' ?>/backend/api/admin/reports/summary.php");
                const data = await res.json();

                if(!data.success){
                    showToast(data.message || "Failed to load reports", "error");
                    loadingState.innerHTML = `<div class="text-red-500 flex flex-col items-center"><i data-lucide="alert-triangle" class="w-10 h-10 mb-2"></i><p>Error loading reports.</p></div>`;
                    lucide.createIcons();
                    return;
                }

                const s = data.summary;

                // Populate KPIs
                document.getElementById('statTotalAmount').innerText = fmtMoney(s.total_loan_amount);
                document.getElementById('statApprovedAmount').innerText = fmtMoney(s.approved_loan_amount);
                document.getElementById('statRejectedAmount').innerText = fmtMoney(s.rejected_loan_amount);

                document.getElementById('statTotalUsers').innerText = safeInt(s.total_users).toLocaleString();
                document.getElementById('statTotalKYC').innerText = safeInt(s.total_kyc).toLocaleString();
                document.getElementById('statApprovedKYC').innerText = safeInt(s.approved_kyc).toLocaleString();
                document.getElementById('statPendingKYC').innerText = safeInt(s.pending_kyc).toLocaleString();

                document.getElementById('statTotalLoans').innerText = safeInt(s.total_loans).toLocaleString();
                document.getElementById('statApprovedLoans').innerText = safeInt(s.approved_loans).toLocaleString();
                document.getElementById('statPendingLoans').innerText = safeInt(s.pending_loans).toLocaleString();

                // Monthly Stats
                const monthlyBody = document.getElementById("monthlyBody");
                monthlyBody.innerHTML = "";
                if (Array.isArray(data.monthly) && data.monthly.length > 0) {
                    data.monthly.forEach(m => {
                        monthlyBody.innerHTML += `
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">${m.month}</td>
                                <td class="px-6 py-3 text-sm text-gray-500 text-right font-mono">${m.total}</td>
                                <td class="px-6 py-3 text-sm font-bold text-gray-900 text-right">${fmtMoney(m.amount)}</td>
                            </tr>
                        `;
                    });
                } else {
                    monthlyBody.innerHTML = `<tr><td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">No monthly data available.</td></tr>`;
                }

                // Latest Loans
                const loansBody = document.getElementById("loansBody");
                loansBody.innerHTML = "";
                if (Array.isArray(data.latest_loans) && data.latest_loans.length > 0) {
                    data.latest_loans.forEach(r => {
                        loansBody.innerHTML += `
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-3">
                                    <div class="text-sm font-medium text-gray-900">${r.full_name || '-'}</div>
                                    <div class="text-xs text-gray-500 flex items-center gap-1 mt-0.5"><i data-lucide="clock" class="w-3 h-3"></i> ${r.created_at ? new Date(r.created_at).toLocaleDateString() : '-'}</div>
                                </td>
                                <td class="px-6 py-3 text-sm font-bold text-gray-900 text-right">${fmtMoney(r.amount)}</td>
                                <td class="px-6 py-3 text-right">
                                    ${formatLoanStatus(r.status)}
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    loansBody.innerHTML = `<tr><td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">No recent activity.</td></tr>`;
                }

                loadingState.classList.add('hidden');
                contentContainer.classList.remove('hidden');
                lucide.createIcons();

            } catch(e) {
                console.error(e);
                showToast("Network error. Could not load reports.", "error");
                loadingState.innerHTML = `<div class="text-red-500 flex flex-col items-center"><i data-lucide="wifi-off" class="w-10 h-10 mb-2"></i><p>Network Error.</p></div>`;
                lucide.createIcons();
            }
        }

        document.addEventListener('DOMContentLoaded', loadReports);
    </script>
</body>
</html>