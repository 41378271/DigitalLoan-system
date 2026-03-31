<?php 
session_start();
require_once "../../backend/config/db.php";

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['borrower','user'])) {
    header("Location: login_page.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Latest KYC record
$stmt = $conn->prepare("
    SELECT status, doc_type, uploaded_at, admin_comment
    FROM kyc_documents
    WHERE user_id = ?
    ORDER BY uploaded_at DESC
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$kyc = $stmt->get_result()->fetch_assoc();

$kyc_status = $kyc['status'] ?? 'not_uploaded';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard | KashFlow</title>
    <?php include '../partials/head.php'; ?>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased overflow-hidden">

    <?php include '../partials/navbar.php'; ?>
    <?php include '../partials/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="md:ml-64 pt-16 h-screen overflow-y-auto pb-24 md:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Welcome Header -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Borrower Dashboard</h1>
                    <p class="text-sm text-gray-500 mt-1">Welcome back, <?= htmlspecialchars($_SESSION['name'] ?? 'Guest') ?>. Here's what's happening.</p>
                </div>
                
                <?php if ($kyc_status !== 'approved'): ?>
                <div class="mt-4 sm:mt-0 flex items-center gap-2 bg-amber-50 text-amber-800 px-4 py-2 rounded-lg border border-amber-200 text-sm font-medium">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-amber-600"></i>
                    Action Required: Verify KYC
                </div>
                <?php endif; ?>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Wallet Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-brand-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                    <div>
                        <div class="flex items-center justify-between mb-4 relative z-10">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-widest">Available Balance</h3>
                            <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center text-brand-600">
                                <i data-lucide="wallet" class="w-5 h-5"></i>
                            </div>
                        </div>
                        <div class="relative z-10">
                            <h2 class="text-3xl font-bold text-gray-900 tracking-tight" id="walletBalDisplay">KES 0.00</h2>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-3 relative z-10">
                        <button onclick="openModal('depositModal')" class="flex-1 bg-brand-600 text-white rounded-xl py-2 text-sm font-semibold hover:bg-brand-700 transition-colors shadow-sm">Deposit</button>
                        <button onclick="openModal('withdrawModal')" class="flex-1 bg-white border border-gray-300 text-gray-700 rounded-xl py-2 text-sm font-semibold hover:bg-gray-50 transition-colors">Withdraw</button>
                    </div>
                </div>

                <!-- KYC Status Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-widest">Verification</h3>
                            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </div>
                        </div>
                        
                        <?php if ($kyc_status === 'approved'): ?>
                            <div class="flex items-center gap-2 text-emerald-600 mb-2">
                                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                                <span class="text-xl font-bold">Approved</span>
                            </div>
                            <p class="text-sm text-gray-500">Your account is fully verified.</p>
                        <?php elseif ($kyc_status === 'pending'): ?>
                            <div class="flex items-center gap-2 text-blue-600 mb-2">
                                <i data-lucide="clock" class="w-6 h-6 animate-pulse"></i>
                                <span class="text-xl font-bold">In Review</span>
                            </div>
                            <p class="text-sm text-gray-500">We are reviewing your documents.</p>
                        <?php elseif ($kyc_status === 'rejected'): ?>
                            <div class="flex items-center gap-2 text-red-600 mb-2">
                                <i data-lucide="x-circle" class="w-6 h-6"></i>
                                <span class="text-xl font-bold">Rejected</span>
                            </div>
                            <p class="text-sm text-gray-500 line-clamp-2" title="<?= htmlspecialchars($kyc['admin_comment'] ?? '') ?>">Reason: <?= htmlspecialchars($kyc['admin_comment'] ?? '') ?></p>
                        <?php else: ?>
                            <div class="flex items-center gap-2 text-gray-400 mb-2">
                                <i data-lucide="minus-circle" class="w-6 h-6"></i>
                                <span class="text-xl font-bold">Unverified</span>
                            </div>
                            <p class="text-sm text-gray-500">Please upload your ID to apply for loans.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-6">
                        <?php if (in_array($kyc_status, ['not_uploaded', 'rejected'])): ?>
                            <a href="upload_kyc.php" class="w-full flex justify-center bg-gray-900 text-white rounded-xl py-2 text-sm font-semibold hover:bg-gray-800 transition-colors shadow-sm">Upload Documents</a>
                        <?php else: ?>
                            <button disabled class="w-full bg-gray-100 text-gray-400 rounded-xl py-2 text-sm font-semibold cursor-not-allowed">Docs Uploaded</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions / Loan Stats -->
                <div class="bg-gray-900 rounded-2xl p-6 shadow-sm flex flex-col justify-between text-white relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-brand-500 opacity-20 rounded-bl-full -mr-8 -mt-8 blur-2xl"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-400 uppercase tracking-widest">Active Loans</h3>
                            <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white">
                                <i data-lucide="credit-card" class="w-5 h-5"></i>
                            </div>
                        </div>
                        <h2 class="text-3xl font-bold tracking-tight">Need Cash?</h2>
                        <p class="text-sm text-gray-400 mt-2">Apply for a low-interest digital loan today.</p>
                    </div>
                    
                    <div class="mt-6 relative z-10">
                        <?php if ($kyc_status === 'approved'): ?>
                            <a href="apply_loan.php" class="w-full flex justify-center bg-brand-500 text-white rounded-xl py-2 text-sm font-semibold hover:bg-brand-400 transition-colors shadow-sm border border-brand-400">Apply for a Loan</a>
                        <?php else: ?>
                            <button onclick="showToast('You must verify your KYC documents before applying for a loan.', 'warning')" class="w-full flex justify-center bg-white/10 text-white/50 rounded-xl py-2 text-sm font-semibold cursor-not-allowed border border-white/5">KYC Required to Apply</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Wallet Transactions</h3>
                        <p class="text-sm text-gray-500">Your recent deposits, withdrawals, and loan payments.</p>
                    </div>
                    <button onclick="loadWalletHistory()" class="text-gray-400 hover:text-brand-600 transition-colors p-2 rounded-lg hover:bg-brand-50">
                        <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-xs">
                            <tr>
                                <th class="px-6 py-4 font-medium">Description</th>
                                <th class="px-6 py-4 font-medium">Type</th>
                                <th class="px-6 py-4 font-medium">Amount</th>
                                <th class="px-6 py-4 font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody id="walletHistoryBody" class="divide-y divide-gray-100">
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2"></i>
                                    Loading transactions...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- Deposit Modal -->
    <div id="depositModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="download" class="h-6 w-6 text-emerald-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Deposit to Wallet</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">You will receive an M-Pesa STK Push prompt on your registered phone.</p>
                                    <div>
                                        <label for="depositAmount" class="block text-sm font-medium text-gray-700 mb-1">Amount (KES)</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-medium">
                                                KES
                                            </div>
                                            <input type="number" id="depositAmount" min="10" step="1" class="block w-full pl-12 pr-3 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors sm:text-sm outline-none font-mono" placeholder="1000">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" onclick="submitDeposit()" class="inline-flex w-full justify-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:ml-3 sm:w-auto transition-colors">Confirm Deposit</button>
                        <button type="button" onclick="closeModal('depositModal')" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Withdraw Modal -->
    <div id="withdrawModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-brand-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="upload" class="h-6 w-6 text-brand-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Withdraw from Wallet</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">Funds will be sent to your M-Pesa immediately.</p>
                                    <div>
                                        <label for="withdrawAmount" class="block text-sm font-medium text-gray-700 mb-1">Amount (KES)</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-medium">
                                                KES
                                            </div>
                                            <input type="number" id="withdrawAmount" min="10" step="1" class="block w-full pl-12 pr-3 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors sm:text-sm outline-none font-mono" placeholder="500">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" onclick="submitWithdraw()" class="inline-flex w-full justify-center rounded-xl bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 sm:ml-3 sm:w-auto transition-colors">Confirm Withdraw</button>
                        <button type="button" onclick="closeModal('withdrawModal')" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Support Chatbot Floating Button & Panel -->
    <button id="chatToggleBtn" class="fixed bottom-24 md:bottom-8 right-6 w-14 h-14 bg-gray-900 text-white rounded-full flex items-center justify-center shadow-2xl hover:bg-gray-800 transition-transform hover:scale-105 z-40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">
        <i data-lucide="message-square" class="w-6 h-6"></i>
    </button>

    <div id="chatbotPanel" class="fixed bottom-40 md:bottom-28 right-6 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col z-50 transform transition-all duration-300 translate-y-4 opacity-0 pointer-events-none max-h-[500px] h-[calc(100vh-120px)]">
        <!-- Header -->
        <div class="bg-gray-900 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                    <i data-lucide="bot" class="w-4 h-4 text-white"></i>
                </div>
                <div>
                    <h3 class="text-white font-medium text-sm">KashBot Assistance</h3>
                    <p class="text-gray-400 text-xs text-brand-300">Online</p>
                </div>
            </div>
            <button id="chatCloseBtn" class="text-gray-400 hover:text-white transition-colors focus:outline-none">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <!-- Chat Area -->
        <div id="chatBox" class="flex-1 p-4 overflow-y-auto bg-gray-50 flex flex-col gap-3 font-medium text-sm">
            <!-- Initial Bot Message -->
            <div class="flex gap-2 mr-6">
                <div class="w-6 h-6 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0 mt-1">
                    <i data-lucide="bot" class="w-3 h-3 text-brand-600"></i>
                </div>
                <div class="bg-white border border-gray-100 text-gray-800 rounded-2xl rounded-tl-sm px-4 py-2 shadow-sm relative">
                    Hi <?= htmlspecialchars($_SESSION['name'] ?? 'there') ?>! I'm your KashBot assistant. Ask me about loans, KYC status, or how to navigate the platform.
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-white border-t border-gray-100">
            <form id="chatForm" class="flex items-center gap-2">
                <input type="text" id="chatInput" class="flex-1 bg-gray-50 border border-gray-200 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-colors placeholder-gray-400" placeholder="Type a message..." autocomplete="off">
                <button type="submit" class="w-10 h-10 rounded-full bg-brand-600 text-white flex items-center justify-center hover:bg-brand-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 flex-shrink-0 shadow-sm">
                    <i data-lucide="send" class="w-4 h-4 ml-0.5"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        lucide.createIcons();

        // Utility classes
        const fmtMoney = (n) => Number(n || 0).toLocaleString('en-KE', {minimumFractionDigits:2, maximumFractionDigits:2});

        // Initialize dashboard state
        document.addEventListener('DOMContentLoaded', () => {
            loadWallet();
            // Start polling every 15s for wallet updates
            setInterval(loadWallet, 15000);
        });

        // Fetch Wallet Info
        async function loadWallet() {
            try {
                const data = await apiCall('wallet/get_balance.php');
                const currency = data.currency || "KES";
                const bal = fmtMoney(data.balance);
                
                document.getElementById('walletBalDisplay').innerText = `${currency} ${bal}`;
                
                // Only load history once on load to prevent UI jumping every 15s unless requested
                if (document.getElementById('walletHistoryBody').children.length <= 1) {
                    await loadWalletHistory();
                }
            } catch (e) {
                console.error("Wallet loading error", e);
            }
        }

        // Fetch Wallet History
        async function loadWalletHistory() {
            const tbody = document.getElementById("walletHistoryBody");
            
            try {
                const data = await apiCall('wallet/history.php?limit=5');
                const txs = data.transactions || [];
                
                if (txs.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500 bg-white">No transactions found.</td></tr>`;
                    return;
                }

                tbody.innerHTML = "";
                txs.forEach(t => {
                    // Badge styles based on type
                    let badgeClass = "bg-gray-100 text-gray-800";
                    let prefix = "";
                    
                    if (t.type === 'deposit' || t.type === 'loan_disbursement') {
                        badgeClass = "bg-emerald-50 text-emerald-700 border border-emerald-100";
                        prefix = "+";
                    } else if (t.type === 'withdraw' || t.type === 'loan_repayment') {
                        badgeClass = "bg-red-50 text-red-700 border border-red-100";
                        prefix = "-";
                    }

                    const displayType = t.type.replace('_', ' ');

                    const tr = document.createElement("tr");
                    tr.className = "hover:bg-gray-50 transition-colors group bg-white";
                    tr.innerHTML = `
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">${t.description}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold uppercase tracking-wider ${badgeClass} capitalize">
                                ${displayType}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900 font-mono">${prefix}${t.currency} ${fmtMoney(t.amount)}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500">${new Date(t.created_at).toLocaleString()}</div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-8 text-center text-red-500 bg-red-50">Error loading transactions.</td></tr>`;
            }
        }

        // Modal Controls
        function openModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
            // Small timeout to allow display:block to apply before animating opacity
            setTimeout(() => {
                modal.querySelector('.bg-gray-900\\/50').classList.add('opacity-100');
                modal.querySelector('.transform').classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                modal.querySelector('.transform').classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
            }, 10);
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            modal.querySelector('.bg-gray-900\\/50').classList.remove('opacity-100');
            modal.querySelector('.transform').classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
            modal.querySelector('.transform').classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Transactions
        async function submitDeposit() {
            const amtStr = document.getElementById('depositAmount').value;
            const amount = parseFloat(amtStr);
            if (!amount || amount <= 0) {
                showToast("Please enter a valid amount", "error");
                return;
            }

            // In real system, this would trigger STK push api. For now, hitting deposit testing api
            try {
                // To simulate M-Pesa integration realistically, you would call M-PESA
                // const fd = new FormData(); fd.append("phone", "SESSION_PHONE"); fd.append("amount", amount);
                // await apiCall('../mpesa/stkpush.php', fd);
                
                const data = await apiCall('wallet/deposit.php', { amount: amount });
                showToast(data.message, 'success');
                closeModal('depositModal');
                document.getElementById('depositAmount').value = '';
                
                await loadWallet();
                await loadWalletHistory();
            } catch (e) {
                // handled by apiCall toast
            }
        }

        async function submitWithdraw() {
            const amtStr = document.getElementById('withdrawAmount').value;
            const amount = parseFloat(amtStr);
            if (!amount || amount <= 0) {
                showToast("Please enter a valid amount", "error");
                return;
            }

            try {
                const data = await apiCall('wallet/withdraw.php', { amount: amount });
                showToast(data.message, 'success');
                closeModal('withdrawModal');
                document.getElementById('withdrawAmount').value = '';
                
                await loadWallet();
                await loadWalletHistory();
            } catch (e) {
                // handled by apiCall toast
            }
        }

        // Chatbot Controls
        const chatToggleBtn = document.getElementById("chatToggleBtn");
        const chatbotPanel  = document.getElementById("chatbotPanel");
        const chatCloseBtn  = document.getElementById("chatCloseBtn");
        const chatBox   = document.getElementById("chatBox");
        const chatForm  = document.getElementById("chatForm");
        const chatInput = document.getElementById("chatInput");

        function toggleChat() {
            const isHidden = chatbotPanel.classList.contains('opacity-0');
            if (isHidden) {
                chatbotPanel.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
                chatbotPanel.classList.add('opacity-100', 'translate-y-0');
                chatToggleBtn.querySelector('i').setAttribute('data-lucide', 'x');
                setTimeout(() => chatInput.focus(), 300);
            } else {
                chatbotPanel.classList.remove('opacity-100', 'translate-y-0');
                chatbotPanel.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
                chatToggleBtn.querySelector('i').setAttribute('data-lucide', 'message-square');
            }
            lucide.createIcons();
        }

        chatToggleBtn.addEventListener("click", toggleChat);
        chatCloseBtn.addEventListener("click", toggleChat);

        function appendUserMessage(text) {
            const div = document.createElement('div');
            div.className = "flex gap-2 ml-6 justify-end";
            div.innerHTML = `
                <div class="bg-brand-600 text-white rounded-2xl rounded-tr-sm px-4 py-2 shadow-sm relative text-sm">
                    ${escapeHtml(text)}
                </div>
            `;
            chatBox.appendChild(div);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function appendBotMessage(text) {
            const div = document.createElement('div');
            div.className = "flex gap-2 mr-6";
            div.innerHTML = `
                <div class="w-6 h-6 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0 mt-1">
                    <i data-lucide="bot" class="w-3 h-3 text-brand-600"></i>
                </div>
                <div class="bg-white border border-gray-100 text-gray-800 rounded-2xl rounded-tl-sm px-4 py-2 shadow-sm relative text-sm">
                    ${text}
                </div>
            `;
            chatBox.appendChild(div);
            lucide.createIcons();
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function escapeHtml(unsafe) {
            return (unsafe || '').replace(/[&<"']/g, function(m) {
                switch (m) {
                    case '&': return '&amp;';
                    case '<': return '&lt;';
                    case '"': return '&quot;';
                    default: return '&#039;';
                }
            });
        }

        chatForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const msg = chatInput.value.trim();
            if(!msg) return;

            appendUserMessage(msg);
            chatInput.value = "";
            chatInput.focus();

            // Setup loading indicator
            const loadingDiv = document.createElement('div');
            loadingDiv.className = "flex gap-2 mr-6";
            loadingDiv.id = "chatLoading";
            loadingDiv.innerHTML = `
                <div class="w-6 h-6 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0 mt-1">
                    <i data-lucide="bot" class="w-3 h-3 text-brand-600"></i>
                </div>
                <div class="bg-white border border-gray-100 text-gray-800 rounded-2xl rounded-tl-sm px-4 py-2 shadow-sm flex items-center gap-1">
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"></div>
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
            `;
            chatBox.appendChild(loadingDiv);
            chatBox.scrollTop = chatBox.scrollHeight;

            try {
                // Fallback to legacy chatbot api structure or new one if modified
                const fd = new FormData();
                fd.append("message", msg);
                const res = await fetch("/digital-loan-system/backend/api/chatbot/respond.php", {
                    method: 'POST',
                    body: fd
                });
                
                const data = await res.json();
                
                // remove loading
                document.getElementById('chatLoading').remove();
                
                appendBotMessage(data.reply || "I didn't quite catch that.");
            } catch (err) {
                document.getElementById('chatLoading').remove();
                appendBotMessage("Sorry, I'm having trouble connecting right now.");
            }
        });

    </script>
</body>
</html>