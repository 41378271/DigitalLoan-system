<?php
session_start();
// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    $dashboard = ($_SESSION['role'] === 'admin') ? '/admin/dashboard' : '/dashboard';
    header("Location: $dashboard");
    exit;
}

// Define basePath for clean URL routing
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = ($scriptName === '/' || $scriptName === '\\') ? '' : $scriptName;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KashFlow | Digital Loan System</title>
    <?php include '../partials/head.php'; ?>
</head>
<body class="bg-white font-sans text-gray-900 overflow-x-hidden">

    <!-- Header / Nav -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-xl bg-brand-600 flex items-center justify-center mr-3 shadow-lg shadow-brand-200">
                        <i data-lucide="wallet" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="text-2xl font-bold tracking-tight text-gray-900">Kash<span class="text-brand-600">Flow</span></span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#benefits" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">Benefits</a>
                    <a href="#how-it-works" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">How it Works</a>
                    <div class="h-6 w-px bg-gray-200"></div>
                    <a href="login" class="text-sm font-semibold text-gray-900 hover:text-brand-600 transition-colors">Login</a>
                    <a href="register" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-bold hover:bg-brand-700 transition-all shadow-lg shadow-brand-100">
                        Get Started
                    </a>
                </div>
                <div class="md:hidden">
                    <button class="p-2 text-gray-600">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full -z-10">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-brand-50 rounded-full blur-[120px] opacity-60"></div>
            <div class="absolute bottom-[10%] right-[-5%] w-[30%] h-[30%] bg-emerald-50 rounded-full blur-[100px] opacity-60"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-brand-100 text-brand-700 text-xs font-bold uppercase tracking-wider mb-6">
                        <span class="flex h-2 w-2 rounded-full bg-brand-600 mr-2 animate-pulse"></span>
                        Trusted by 10,000+ Users
                    </div>
                    <h1 class="text-5xl lg:text-7xl font-extrabold text-gray-900 leading-[1.1] mb-8">
                        Instant Loans, <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-emerald-500">Zero Paperwork.</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-10 leading-relaxed max-w-xl">
                        Access micro-loans instantly with KashFlow. Apply in minutes, get approved in seconds, and receive funds directly to your wallet.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="login" class="flex items-center justify-center px-8 py-4 rounded-2xl bg-brand-600 text-white font-bold text-lg hover:bg-brand-700 hover:scale-[1.02] transition-all shadow-xl shadow-brand-200">
                            Borrower Login
                            <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                        </a>
                        <a href="login" class="flex items-center justify-center px-8 py-4 rounded-2xl bg-white text-gray-900 border border-gray-200 font-bold text-lg hover:bg-gray-50 transition-all">
                            Admin Portal
                            <i data-lucide="shield-check" class="w-5 h-5 ml-2"></i>
                        </a>
                    </div>
                </div>
                <div class="relative lg:ml-12">
                    <div class="relative z-10 bg-white rounded-[2.5rem] p-8 shadow-2xl border border-gray-100">
                        <!-- Mock App Interface -->
                        <div class="space-y-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm text-gray-500">Available Balance</p>
                                    <h3 class="text-3xl font-bold text-gray-900">KES 45,200.00</h3>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-brand-50 flex items-center justify-center">
                                    <i data-lucide="plus" class="w-6 h-6 text-brand-600"></i>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                    <p class="text-xs text-gray-500 mb-1">Active Loan</p>
                                    <p class="text-lg font-bold text-gray-900">KES 12,000</p>
                                </div>
                                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                    <p class="text-xs text-gray-500 mb-1">Credit Limit</p>
                                    <p class="text-lg font-bold text-brand-600">KES 150k</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <p class="text-sm font-semibold text-gray-900">Recent Transactions</p>
                                <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center mr-3">
                                            <i data-lucide="arrow-down-left" class="w-5 h-5 text-emerald-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold">Loan Disbursement</p>
                                            <p class="text-xs text-gray-500">Today, 10:45 AM</p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-emerald-600">+12,000</p>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-lg bg-brand-100 flex items-center justify-center mr-3">
                                            <i data-lucide="arrow-up-right" class="w-5 h-5 text-brand-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold">Wallet Withdrawal</p>
                                            <p class="text-xs text-gray-500">Yesterday, 4:20 PM</p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900">-5,000</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Decorative shapes -->
                    <div class="absolute -top-6 -right-6 w-32 h-32 bg-brand-600/10 rounded-3xl -z-10 rotate-12"></div>
                    <div class="absolute -bottom-6 -left-6 w-48 h-48 bg-emerald-500/10 rounded-full -z-10 blur-2xl"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits -->
    <section id="benefits" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-sm font-bold text-brand-600 uppercase tracking-widest mb-3">Why KashFlow</h2>
                <h3 class="text-3xl lg:text-5xl font-extrabold text-gray-900">Borrowing made simple.</h3>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 hover:shadow-xl transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-brand-100 flex items-center justify-center mb-6">
                        <i data-lucide="zap" class="w-7 h-7 text-brand-600"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-4">Instant Payouts</h4>
                    <p class="text-gray-600 leading-relaxed">No waiting in lines. Once approved, funds are sent instantly to your digital wallet for withdrawal.</p>
                </div>
                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 hover:shadow-xl transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center mb-6">
                        <i data-lucide="shield" class="w-7 h-7 text-emerald-600"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-4">Secure & Private</h4>
                    <p class="text-gray-600 leading-relaxed">We use bank-grade encryption to ensure your personal data and transactions are always protected.</p>
                </div>
                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 hover:shadow-xl transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-purple-100 flex items-center justify-center mb-6">
                        <i data-lucide="trending-up" class="w-7 h-7 text-purple-600"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-4">Flexible Repayments</h4>
                    <p class="text-gray-600 leading-relaxed">Choose a repayment schedule that works for you. No hidden fees, just transparent terms.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-4 md:mb-0">
                    <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center mr-2">
                        <i data-lucide="wallet" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-900">KashFlow</span>
                </div>
                <p class="text-gray-500 text-sm">© 2026 KashFlow Digital Loan System. All rights reserved.</p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-brand-600"><i data-lucide="twitter" class="w-5 h-5"></i></a>
                    <a href="#" class="text-gray-400 hover:text-brand-600"><i data-lucide="linkedin" class="w-5 h-5"></i></a>
                    <a href="#" class="text-gray-400 hover:text-brand-600"><i data-lucide="github" class="w-5 h-5"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
