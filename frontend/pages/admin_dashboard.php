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
    <title>Admin Dashboard | KashFlow</title>
    <?php include '../partials/head.php'; ?>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased overflow-hidden">

    <?php include '../partials/navbar.php'; ?>

    <?php include '../partials/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="md:ml-64 pt-16 h-screen overflow-y-auto pb-24 md:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Page Header -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between bg-white p-6 sm:p-8 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
                <!-- Decorative background elements -->
                <div class="absolute right-0 top-0 w-64 h-64 bg-brand-50 rounded-full translate-x-1/2 -translate-y-1/2 blur-3xl opacity-50 pointer-events-none"></div>
                <div class="absolute right-0 bottom-0 w-32 h-32 bg-blue-50 rounded-full translate-x-1/4 translate-y-1/4 blur-2xl opacity-50 pointer-events-none"></div>

                <div class="relative z-10">
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Admin Portal Overview</h1>
                    <p class="text-sm text-gray-500 mt-1 max-w-2xl">Welcome back, <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($_SESSION['name']); ?></span>. Monitor system health, verify credentials, and process loans.</p>
                </div>
            </div>

            <!-- Dashboard Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <!-- Users Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col hover:shadow-md transition-shadow group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center border border-blue-100 group-hover:bg-blue-600 transition-colors">
                            <i data-lucide="users" class="w-6 h-6 text-blue-600 group-hover:text-white transition-colors"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-gray-500 text-sm font-medium mb-1">User Management</h3>
                        <p class="text-2xl font-bold text-gray-900 mb-4">Users</p>
                        <a href="users" class="text-sm font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                            Go to directory <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- Applications Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col hover:shadow-md transition-shadow group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-full bg-brand-50 flex items-center justify-center border border-brand-100 group-hover:bg-brand-600 transition-colors">
                            <i data-lucide="file-text" class="w-6 h-6 text-brand-600 group-hover:text-white transition-colors"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-gray-500 text-sm font-medium mb-1">Loan Processing</h3>
                        <p class="text-2xl font-bold text-gray-900 mb-4">Applications</p>
                        <a href="loans" class="text-sm font-semibold text-brand-600 hover:text-brand-800 flex items-center gap-1">
                            Review pipeline <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- KYC Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col hover:shadow-md transition-shadow group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center border border-amber-100 group-hover:bg-amber-500 transition-colors">
                            <i data-lucide="shield-alert" class="w-6 h-6 text-amber-600 group-hover:text-white transition-colors"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-gray-500 text-sm font-medium mb-1">Identity Verification</h3>
                        <p class="text-2xl font-bold text-gray-900 mb-4">KYC Review</p>
                        <a href="kyc" class="text-sm font-semibold text-amber-600 hover:text-amber-800 flex items-center gap-1">
                            Verify documents <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- Reports Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col hover:shadow-md transition-shadow group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center border border-emerald-100 group-hover:bg-emerald-600 transition-colors">
                            <i data-lucide="trending-up" class="w-6 h-6 text-emerald-600 group-hover:text-white transition-colors"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-gray-500 text-sm font-medium mb-1">System Analytics</h3>
                        <p class="text-2xl font-bold text-gray-900 mb-4">Reports</p>
                        <a href="reports" class="text-sm font-semibold text-emerald-600 hover:text-emerald-800 flex items-center gap-1">
                            View insights <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Future Dashboard Expansion Placeholder -->
             <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 flex flex-col items-center justify-center text-center opacity-70">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="bar-chart-3" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Detailed Analytics Coming Soon</h3>
                <p class="text-gray-500 max-w-md">System wide charts, loan disbursement metrics, and repayment tracking visualizations will appear here in a future update.</p>
            </div>

        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>