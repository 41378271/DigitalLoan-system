<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profile Settings - KashFlow</title>
    <?php include '../partials/head.php'; ?>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased overflow-hidden">

    <?php include '../partials/navbar.php'; ?>
    <?php include '../partials/sidebar.php'; ?>

    <main class="md:ml-64 pt-16 h-screen overflow-y-auto pb-24 md:pb-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Profile Settings</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your account details and security preferences.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Sidebar Nav -->
                <div class="md:col-span-1 border-r md:border-r md:border-gray-200 border-b border-gray-200 pb-6 md:pb-0 pr-0 md:pr-6 space-y-1">
                    <button id="tabPersonal" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-brand-700 bg-brand-50 transition-colors text-left" onclick="switchTheme('personal')">
                        <i data-lucide="user" class="w-5 h-5"></i> Personal Details
                    </button>
                    <button id="tabSecurity" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-600 hover:bg-gray-50 transition-colors text-left" onclick="switchTheme('security')">
                        <i data-lucide="shield" class="w-5 h-5 opacity-70"></i> Security
                    </button>
                </div>

                <!-- Settings Forms -->
                <div class="md:col-span-2 relative min-h-[400px]">
                    
                    <!-- Global Loading State -->
                    <div id="loadingState" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex flex-col items-center justify-center rounded-2xl border border-gray-100 shadow-sm hidden">
                        <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-brand-500 mb-2"></i>
                        <p class="text-sm text-gray-500 font-medium">Saving changes...</p>
                    </div>

                    <!-- Personal Information -->
                    <div id="panelPersonal" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                        <h2 class="text-lg font-bold text-gray-900">Personal Information</h2>
                        <p class="text-sm text-gray-500 mt-1 mb-6">Update your email address or phone number.</p>

                        <form id="profileForm" class="space-y-6">
                            <div class="space-y-4">
                                <!-- Readonly Name (from signup) -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Full Legal Name</label>
                                    <p class="text-sm text-gray-500 bg-gray-50 px-4 py-2.5 rounded-xl border border-gray-100"><?= htmlspecialchars($_SESSION['full_name'] ?? 'User Name'); ?></p>
                                    <p class="text-xs text-gray-400 mt-1"><i data-lucide="info" class="w-3 h-3 inline"></i> Contact support to change your verified legal name.</p>
                                </div>
                            
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1" for="email">Email Address <span class="text-brand-600">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                            <i data-lucide="mail" class="w-4 h-4 text-gray-400"></i>
                                        </div>
                                        <input type="email" id="email" required placeholder="john@example.com" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors" value="<?= htmlspecialchars($_SESSION['email'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1" for="phone">Phone Number</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                            <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                                        </div>
                                        <input type="tel" id="phone" placeholder="e.g. 0712345678" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors" value="<?= htmlspecialchars($_SESSION['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-100 flex justify-end">
                                <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl shadow-sm hover:shadow transition-all inline-flex items-center gap-2">
                                    <i data-lucide="save" class="w-4 h-4"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Security / Password -->
                    <div id="panelSecurity" class="hidden bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                        <h2 class="text-lg font-bold text-gray-900">Security</h2>
                        <p class="text-sm text-gray-500 mt-1 mb-6">Change your password to keep your account secure.</p>

                        <form id="passwordForm" class="space-y-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1" for="current_password">Current Password <span class="text-brand-600">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                            <i data-lucide="lock" class="w-4 h-4 text-gray-400"></i>
                                        </div>
                                        <input type="password" id="current_password" required placeholder="Enter current password" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1" for="new_password">New Password <span class="text-brand-600">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                            <i data-lucide="key" class="w-4 h-4 text-gray-400"></i>
                                        </div>
                                        <input type="password" id="new_password" required placeholder="Enter new password" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1" for="confirm_password">Confirm New Password <span class="text-brand-600">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                            <i data-lucide="check-circle" class="w-4 h-4 text-gray-400"></i>
                                        </div>
                                        <input type="password" id="confirm_password" required placeholder="Confirm new password" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors">
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-100 flex justify-end">
                                <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl shadow-sm hover:shadow transition-all inline-flex items-center gap-2">
                                    <i data-lucide="shield-check" class="w-4 h-4"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <script>
        lucide.createIcons();

        // Tab Switching Logic
        const tabPersonal = document.getElementById('tabPersonal');
        const tabSecurity = document.getElementById('tabSecurity');
        const panelPersonal = document.getElementById('panelPersonal');
        const panelSecurity = document.getElementById('panelSecurity');
        
        function switchTheme(tab) {
            if (tab === 'personal') {
                tabPersonal.className = "w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-brand-700 bg-brand-50 transition-colors text-left";
                tabSecurity.className = "w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-600 hover:bg-gray-50 transition-colors text-left";
                panelPersonal.classList.remove('hidden');
                panelSecurity.classList.add('hidden');
            } else {
                tabSecurity.className = "w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-brand-700 bg-brand-50 transition-colors text-left";
                tabPersonal.className = "w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-600 hover:bg-gray-50 transition-colors text-left";
                panelSecurity.classList.remove('hidden');
                panelPersonal.classList.add('hidden');
            }
        }

        // --- Simulated Profile Update ---
        // As the endpoints are not yet fully implemented, we'll simulate the UX for now
        // This makes the frontend fully interactive as requested in Phase 3/4 mockups
        
        const loadingState = document.getElementById('loadingState');

        document.getElementById('profileForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            loadingState.classList.remove('hidden');
            
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;

            // Simulate API latency
            setTimeout(() => {
                loadingState.classList.add('hidden');
                showToast("Profile details updated successfully", "success");
            }, 800);
        });

        document.getElementById('passwordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const curr = document.getElementById('current_password').value;
            const newp = document.getElementById('new_password').value;
            const conf = document.getElementById('confirm_password').value;

            if (newp !== conf) {
                showToast("New passwords do not match", "error");
                return;
            }

            loadingState.classList.remove('hidden');
            
            // Simulate API latency
            setTimeout(() => {
                loadingState.classList.add('hidden');
                showToast("Password updated successfully", "success");
                document.getElementById('passwordForm').reset();
            }, 800);
        });
    </script>
</body>
</html>
