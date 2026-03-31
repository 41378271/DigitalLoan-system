<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
    exit;
}

$loan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($loan_id <= 0) {
    header("Location: my_loans.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Repayment Schedule - KashFlow</title>
    <?php include '../partials/head.php'; ?>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased overflow-hidden">

    <?php include '../partials/navbar.php'; ?>
    <?php include '../partials/sidebar.php'; ?>

    <main class="md:ml-64 pt-16 h-screen overflow-y-auto pb-24 md:pb-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="mb-8 flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
                        <a href="my-loans" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i data-lucide="arrow-left" class="w-6 h-6"></i>
                        </a>
                        Repayment Schedule
                    </h1>
                    <p class="text-sm text-gray-500 mt-1 ml-9">Amortization details for Loan #<?= $loan_id ?></p>
                </div>
                <!-- Status Badge Injected Here -->
                <div id="loanStatusBadge"></div>
            </div>

            <!-- Global Loading State -->
            <div id="loadingState" class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
                <i data-lucide="loader-2" class="w-10 h-10 animate-spin text-brand-500 mb-4"></i>
                <p class="text-gray-500 font-medium">Loading schedule...</p>
            </div>

            <div id="contentContainer" class="hidden space-y-6">
                
                <!-- Summary Card -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                            <i data-lucide="calculator" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-0.5">Principal Amount</p>
                            <h3 class="text-xl font-bold text-gray-900" id="statPrincipal">KES 0.00</h3>
                        </div>
                    </div>
                    <div class="hidden sm:block w-px h-12 bg-gray-100"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-0.5">Total Repayable</p>
                        <h3 class="text-xl font-bold text-gray-900" id="statTotalRepayable">KES 0.00</h3>
                    </div>
                    <div class="hidden sm:block w-px h-12 bg-gray-100"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-0.5">Remaining Balance</p>
                        <h3 class="text-xl font-bold text-brand-600" id="statRemaining">KES 0.00</h3>
                    </div>
                </div>

                <!-- Schedule Table -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="calendar-clock" class="w-5 h-5 text-brand-500"></i> Amortization Table
                        </h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left whitespace-nowrap">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                                    <th class="px-6 py-4">Instalment</th>
                                    <th class="px-6 py-4">Due Date</th>
                                    <th class="px-6 py-4 text-right">Amount Due</th>
                                    <th class="px-6 py-4 text-right">Principal</th>
                                    <th class="px-6 py-4 text-right">Interest</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm" id="scheduleTableBody">
                                <!-- Rows injected via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="hidden bg-white rounded-2xl border border-gray-100 shadow-sm py-16 px-6 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                        <i data-lucide="calendar-x" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">No Schedule Found</h3>
                    <p class="text-gray-500 max-w-md mx-auto">This loan does not have an amortization schedule yet. It may not be approved or completely generated.</p>
                </div>

            </div>
        </div>
    </main>

    <?php include '../partials/chatbot_widget.php'; ?>

    <script>
        lucide.createIcons();
        updateActiveSidebar('sidebar-my-loans');

        const LOAN_ID = <?= $loan_id ?>;
        const fmtMoney = (v) => new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(v || 0);

        async function loadSchedule() {
            try {
                // Fetch the loan details
                const res = await fetch(`../../backend/api/loans/my_loans.php`);
                const data = await res.json();

                if (!data.success) {
                    showToast(data.message || "Failed to load loan data", "error");
                    return;
                }

                // Find the specific loan
                const loan = data.loans.find(l => parseInt(l.id) === LOAN_ID);
                if (!loan) {
                    showToast("Loan not found", "error");
                    setTimeout(() => window.location.href = "my_loans.php", 1500);
                    return;
                }

                // Header Stats
                document.getElementById('statPrincipal').textContent = fmtMoney(loan.amount);
                document.getElementById('statTotalRepayable').textContent = fmtMoney(loan.total_repayable);
                document.getElementById('statRemaining').textContent = fmtMoney(loan.remaining_balance);

                // Status Badge
                const statusBadge = document.getElementById('loanStatusBadge');
                let badgeClass = "bg-gray-100 text-gray-800";
                let s = loan.status.toLowerCase();
                if (s === 'approved') badgeClass = "bg-emerald-100 text-emerald-800 border-emerald-200";
                else if (s === 'rejected') badgeClass = "bg-red-100 text-red-800 border-red-200";
                else if (s === 'paid') badgeClass = "bg-blue-100 text-blue-800 border-blue-200";
                else if (s === 'pending') badgeClass = "bg-amber-100 text-amber-800 border-amber-200";

                statusBadge.innerHTML = `<span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border ${badgeClass}">${loan.status}</span>`;


                // Schedule Table
                const tbody = document.getElementById('scheduleTableBody');
                const emptyState = document.getElementById('emptyState');
                
                if (!loan.schedule || loan.schedule.length === 0) {
                    emptyState.classList.remove('hidden');
                    document.querySelector('.overflow-x-auto').classList.add('hidden');
                } else {
                    document.querySelector('.overflow-x-auto').classList.remove('hidden');
                    emptyState.classList.add('hidden');
                    
                    let html = '';
                    loan.schedule.forEach(inst => {
                        const isPaid = inst.status.toLowerCase() === 'paid';
                        const statusColor = isPaid ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600';
                        const statusIcon = isPaid ? '<i data-lucide="check-circle-2" class="w-4 h-4 mr-1"></i> Paid' : '<i data-lucide="circle-dashed" class="w-4 h-4 mr-1"></i> Pending';
                        
                        html += `
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                #${inst.instalment_number}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                ${new Date(inst.due_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900">
                                ${fmtMoney(inst.amount_due)}
                            </td>
                            <td class="px-6 py-4 text-right text-gray-500 font-mono text-xs">
                                ${fmtMoney(inst.principal_component)}
                            </td>
                            <td class="px-6 py-4 text-right text-gray-500 font-mono text-xs">
                                ${fmtMoney(inst.interest_component)}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium ${statusColor}">
                                    ${statusIcon}
                                </span>
                            </td>
                        </tr>`;
                    });
                    tbody.innerHTML = html;
                }

                // Reveal content
                document.getElementById('loadingState').classList.add('hidden');
                document.getElementById('contentContainer').classList.remove('hidden');
                
                lucide.createIcons();

            } catch (err) {
                console.error(err);
                showToast("Network error. Please try again.", "error");
                document.getElementById('loadingState').innerHTML = `
                    <div class="text-red-500 flex flex-col items-center">
                        <i data-lucide="alert-triangle" class="w-10 h-10 mb-2"></i>
                        <p>Error loading schedule.</p>
                    </div>`;
                lucide.createIcons();
            }
        }

        document.addEventListener('DOMContentLoaded', loadSchedule);
    </script>
</body>
</html>
