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
    <title>My Loans | KashFlow</title>
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
                    <h1 class="text-2xl font-bold text-gray-900">My Loans</h1>
                    <p class="text-sm text-gray-500 mt-1">Track your active loans and complete your repayments.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <button onclick="loadLoans()" class="text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 transition-colors px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Global Loading State -->
            <div id="loadingState" class="flex flex-col items-center justify-center py-20">
                <i data-lucide="loader-2" class="w-10 h-10 animate-spin text-brand-500 mb-4"></i>
                <p class="text-gray-500 font-medium">Loading your loan records...</p>
            </div>

            <!-- Empty State (Hidden initially) -->
            <div id="emptyState" class="hidden flex-col items-center justify-center py-16 px-4 text-center bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                    <i data-lucide="file-x-2" class="w-10 h-10 text-gray-400"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No loans found</h3>
                <p class="text-gray-500 max-w-sm mb-6">You don't have any active or past loans on record.</p>
                <a href="apply_loan.php" class="bg-brand-600 text-white rounded-xl px-6 py-2.5 font-semibold hover:bg-brand-700 transition-colors shadow-sm inline-flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Apply Now
                </a>
            </div>

            <!-- Loans Grid -->
            <div id="loansGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                <!-- Loan cards will be injected here -->
            </div>

        </div>
    </main>

    <!-- Pay Loan Modal -->
    <div id="payModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-brand-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="credit-card" class="h-6 w-6 text-brand-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Make a Repayment</h3>
                                <div class="mt-2 text-sm text-gray-500">
                                    <p class="mb-2">Paying towards Loan #<span id="payLoanIdDisplay" class="font-bold text-gray-900">0</span></p>
                                    <div class="bg-gray-50 rounded-lg p-3 mb-4 border border-gray-100 flex justify-between items-center">
                                        <span>Remaining Balance</span>
                                        <span id="payLoanNote" class="font-bold text-red-600 font-mono">KES 0.00</span>
                                    </div>
                                    
                                    <div class="mb-4 text-left">
                                        <label for="payAmount" class="block text-sm font-medium text-gray-700 mb-1">Payment Amount (KES)</label>
                                        <div class="relative flex items-center">
                                           <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-medium">
                                                KES
                                            </div>
                                            <input type="number" id="payAmount" step="0.01" min="1" class="block w-full pl-12 pr-16 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors sm:text-sm outline-none font-mono">
                                            <button type="button" onclick="setMaxPay()" class="absolute inset-y-0 right-2 px-2 text-xs font-semibold text-brand-600 hover:text-brand-800 uppercase tracking-wider">Max</button>
                                        </div>
                                    </div>

                                    <div class="bg-brand-50 rounded-lg p-3 border border-brand-100 flex justify-between items-center">
                                        <div class="flex items-center gap-1.5 text-brand-700">
                                            <i data-lucide="wallet" class="w-4 h-4"></i>
                                            <span>Wallet Bal</span>
                                        </div>
                                        <span id="walletBalanceDisplay" class="font-bold text-brand-700 font-mono">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-100 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" id="payConfirmBtn" onclick="submitPayment()" class="inline-flex w-full justify-center rounded-xl bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 sm:ml-3 sm:w-auto transition-colors flex items-center gap-2">
                            Pay Now <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="closePayModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        lucide.createIcons();

        let activeLoan = null;
        let lastKnownWalletBalance = 0;
        let lastKnownRemaining = 0;

        const fmtMoney = (n) => Number(n || 0).toLocaleString('en-KE', {minimumFractionDigits:2, maximumFractionDigits:2});

        const loadingState = document.getElementById('loadingState');
        const emptyState = document.getElementById('emptyState');
        const loansGrid = document.getElementById('loansGrid');

        async function loadLoans() {
            // UI Reset
            loadingState.classList.remove('hidden');
            emptyState.classList.add('hidden');
            loansGrid.classList.add('hidden');
            loansGrid.innerHTML = '';

            try {
                // Using unified apiCall from navbar.php if available, otherwise fetch
                const res = await fetch("../../backend/api/loans/my_loans.php");
                const data = await res.json();

                if (!data.success) {
                    showToast(data.message || "Failed to load loans", "error");
                    loadingState.classList.add('hidden');
                    return;
                }

                const loans = data.loans || [];

                if (loans.length === 0) {
                    loadingState.classList.add('hidden');
                    emptyState.classList.remove('hidden');
                    return;
                }

                // Render Cards
                loans.forEach(l => {
                    renderLoanCard(l);
                });

                loadingState.classList.add('hidden');
                loansGrid.classList.remove('hidden');
                lucide.createIcons();

            } catch (err) {
                console.error("Loans error:", err);
                showToast("Network error while loading loans.", "error");
                loadingState.classList.add('hidden');
            }
        }

        function renderLoanCard(l) {
            let remaining = Number(l.remaining ?? l.amount);
            if(remaining < 0.01) remaining = 0;
            const total = Number(l.amount);
            
            // Progress Calculation
            let progressPercent = 0;
            if (total > 0) {
                progressPercent = ((total - remaining) / total) * 100;
                if (progressPercent < 0) progressPercent = 0;
                if (progressPercent > 100) progressPercent = 100;
            }

            const statusRaw = (l.status || "").toLowerCase();
            
            // Define Badge styling
            let badgeColors = "bg-gray-100 text-gray-800 border-gray-200";
            let displayStatus = statusRaw;
            let iconStr = "circle";
            
            if (['paid'].includes(statusRaw)) {
                badgeColors = "bg-emerald-50 text-emerald-700 border-emerald-100";
                displayStatus = "Paid Fully";
                iconStr = "check-circle-2";
                progressPercent = 100; // Force full bar if marked paid
            } else if (['approved', 'active', 'ongoing', 'partially_paid', 'partially-paid'].includes(statusRaw)) {
                badgeColors = "bg-blue-50 text-blue-700 border-blue-100";
                displayStatus = "Active";
                iconStr = "activity";
            } else if (['pending'].includes(statusRaw)) {
                badgeColors = "bg-amber-50 text-amber-700 border-amber-100";
                displayStatus = "Pending Review";
                iconStr = "clock";
            } else if (['rejected'].includes(statusRaw)) {
                badgeColors = "bg-red-50 text-red-700 border-red-100";
                displayStatus = "Rejected";
                iconStr = "x-circle";
            } else if (['defaulted'].includes(statusRaw)) {
                badgeColors = "bg-red-50 text-red-700 border-red-100";
                displayStatus = "Defaulted";
                iconStr = "alert-triangle";
            }

            // Date processing
            const applyDate = new Date(l.created_at).toLocaleDateString('en-GB', { day: 'short', month: 'short', year:'numeric' });

            // Button logic
            let actionBtn = '';
            if (['approved','active','ongoing','partially_paid','partially-paid'].includes(statusRaw) && remaining > 0) {
                actionBtn = `
                    <button onclick="openPayModal(${l.id}, ${remaining})" class="w-full bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700 transition-colors shadow-sm flex justify-center items-center gap-2">
                        <i data-lucide="wallet" class="w-4 h-4"></i> Make Repayment
                    </button>
                `;
            } else if (['paid'].includes(statusRaw)) {
                actionBtn = `
                    <button disabled class="w-full bg-gray-50 text-gray-400 border border-gray-100 rounded-xl py-2.5 text-sm font-semibold cursor-not-allowed flex justify-center items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i> Loan Settled
                    </button>
                `;
            } else if (['pending'].includes(statusRaw)) {
                 actionBtn = `
                    <button disabled class="w-full bg-amber-50 text-amber-600/50 rounded-xl py-2.5 text-sm font-semibold cursor-not-allowed flex justify-center items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4"></i> Awaiting Approval
                    </button>
                `;
            } else {
                 actionBtn = `
                    <button disabled class="w-full bg-gray-100 text-gray-400 rounded-xl py-2.5 text-sm font-semibold cursor-not-allowed">
                        N/A
                    </button>
                `;
            }

            const cardHtml = `
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition-shadow">
                    
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <p class="text-xs text-brand-600 font-bold uppercase tracking-widest mb-1">Loan #${l.id}</p>
                            <h3 class="text-xl font-bold text-gray-900 font-mono">KES ${fmtMoney(total)}</h3>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold uppercase tracking-wider border ${badgeColors}">
                            <i data-lucide="${iconStr}" class="w-3.5 h-3.5"></i> ${displayStatus}
                        </span>
                    </div>

                    <div class="space-y-4 mb-6 relative z-10">
                        <!-- Stat row 1 -->
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Duration</span>
                            <span class="font-medium text-gray-900">${l.term_months ?? "-"} Months</span>
                        </div>
                        
                        <!-- Stat row 2 -->
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Applied</span>
                            <span class="font-medium text-gray-900">${applyDate}</span>
                        </div>

                        <!-- Stat row 3 -->
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Remaining</span>
                            <span class="font-bold text-red-600 font-mono">KES ${fmtMoney(remaining)}</span>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="pt-2">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500 font-medium tracking-wide border px-1">Progress</span>
                                <span class="text-brand-600 font-bold">${progressPercent.toFixed(0)}%</span>
                            </div>
                            <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-brand-500 transition-all duration-1000 ease-out" style="width: ${progressPercent}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto pt-4 border-t border-gray-100">
                        ${actionBtn}
                    </div>
                </div>
            `;
            
            loansGrid.insertAdjacentHTML('beforeend', cardHtml);
        }


        // --- Repayment Modal Handlers ---

        async function openPayModal(id, remaining) {
            activeLoan = {id, remaining};
            lastKnownRemaining = remaining;
            
            document.getElementById('payLoanIdDisplay').innerText = id;
            document.getElementById('payLoanNote').innerText = `KES ${fmtMoney(remaining)}`;
            document.getElementById('payAmount').value = remaining;
            
            // Show modal with animation
            const modal = document.getElementById('payModal');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.querySelector('.bg-gray-900\\/50').classList.add('opacity-100');
                modal.querySelector('.transform').classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                modal.querySelector('.transform').classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
            }, 10);

            // Fetch live wallet balance dynamically
            try {
                // If using unified apiCall (loaded from navbar if available)
                const res = await fetch("../../backend/api/wallet/get_balance.php");
                const data = await res.json();
                
                if (data.success) {
                    lastKnownWalletBalance = parseFloat(data.balance);
                    document.getElementById('walletBalanceDisplay').innerText = `KES ${fmtMoney(lastKnownWalletBalance)}`;
                } else {
                    document.getElementById('walletBalanceDisplay').innerText = "Error loading";
                }
            } catch (e) {
                document.getElementById('walletBalanceDisplay').innerText = "Network Error";
            }
        }

        function closePayModal() {
            const modal = document.getElementById('payModal');
            modal.querySelector('.bg-gray-900\\/50').classList.remove('opacity-100');
            modal.querySelector('.transform').classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
            modal.querySelector('.transform').classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
            activeLoan = null;
        }

        function setMaxPay() {
            // Can't pay more than wallet balance OR remaining balance
            const maxVal = Math.min(lastKnownRemaining, lastKnownWalletBalance);
            if(maxVal < 0) maxVal = 0;
            document.getElementById('payAmount').value = maxVal;
        }

        async function submitPayment() {
            const amtStr = document.getElementById('payAmount').value;
            const amt = parseFloat(amtStr);

            if (!amt || amt <= 0) {
                showToast("Please enter a valid amount greater than 0.", "error");
                return;
            }
            if (amt > lastKnownRemaining) {
                showToast("You cannot pay more than the remaining balance.", "warning");
                return;
            }

            const btn = document.getElementById('payConfirmBtn');
            const ogHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Processing...`;
            lucide.createIcons();

            try {
                const fd = new FormData();
                fd.append('loan_id', activeLoan.id);
                fd.append('amount', amt);

                const res = await fetch("../../backend/api/wallet/pay_loan.php", {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();

                if (data.success) {
                    showToast(data.message, "success");
                    closePayModal();
                    loadLoans(); // Refresh grid
                } else {
                    showToast(data.message || "Payment failed", "error");
                    btn.disabled = false;
                    btn.innerHTML = ogHtml;
                    lucide.createIcons();
                }

            } catch (err) {
                console.error("Payment error:", err);
                showToast("Network error. Please try again.", "error");
                btn.disabled = false;
                btn.innerHTML = ogHtml;
                lucide.createIcons();
            }
        }


        // Init
        document.addEventListener('DOMContentLoaded', loadLoans);
    </script>
</body>
</html>