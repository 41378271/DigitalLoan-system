<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $dashboard = ($_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'borrower_dashboard.php';
    header("Location: $dashboard");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register | KashFlow Digital Loans</title>
    <?php include '../partials/head.php'; ?>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <!-- Right Hero Section (Hidden on mobile) -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-brand-900 overflow-hidden items-center justify-center order-2">
        <!-- Background Decoration -->
        <div class="absolute w-[800px] h-[800px] bg-brand-600 rounded-full blur-3xl opacity-20 -top-40 -left-40 animate-pulse"></div>
        <div class="absolute w-[600px] h-[600px] bg-amber-500 rounded-full blur-3xl opacity-10 bottom-0 right-0"></div>
        
        <div class="relative z-10 w-full max-w-lg px-12">
            <h2 class="text-3xl font-bold text-white mb-6 leading-tight">Join Kash<span class="text-brand-400">Flow</span> today.</h2>
            <p class="text-brand-100 text-lg mb-8 leading-relaxed">
                Experience the fastest way to get credit. Zero paperwork, zero hidden fees, and absolute transparency.
            </p>
            
            <div class="space-y-6">
                <!-- Benefit 1 -->
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center shrink-0 shadow-lg border border-white/5">
                        <i data-lucide="rocket" class="w-6 h-6 text-brand-300"></i>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold text-lg">Lightning Fast</h4>
                        <p class="text-brand-200/80 text-sm mt-1">Get funds directly to your M-Pesa within minutes.</p>
                    </div>
                </div>
                <!-- Benefit 2 -->
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center shrink-0 shadow-lg border border-white/5">
                        <i data-lucide="percent" class="w-6 h-6 text-brand-300"></i>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold text-lg">Low Interest</h4>
                        <p class="text-brand-200/80 text-sm mt-1">Flat 3% monthly rate. Pay only for what you borrow.</p>
                    </div>
                </div>
                <!-- Benefit 3 -->
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center shrink-0 shadow-lg border border-white/5">
                        <i data-lucide="layout-dashboard" class="w-6 h-6 text-brand-300"></i>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold text-lg">Beautiful Dashboard</h4>
                        <p class="text-brand-200/80 text-sm mt-1">Seamlessly track loans, payments, and schedules.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Left Register Section -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 relative overflow-y-auto order-1">
        <!-- Mobile Logo -->
        <div class="absolute top-8 left-6 sm:left-12 flex items-center">
            <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center mr-2 shadow-lg">
                <i data-lucide="wallet" class="w-4 h-4 text-white"></i>
            </div>
            <span class="text-xl font-bold text-gray-900 tracking-tight">Kash<span class="text-brand-600">Flow</span></span>
        </div>

        <div class="w-full max-w-md mt-16 sm:mt-12 lg:mt-0">
            <!-- Header -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Create an account</h2>
                <p class="text-gray-500">Let's get you set up to access instant credit.</p>
            </div>

            <!-- Registration Form -->
            <form id="registerForm" class="space-y-5">
                <!-- Full Name Field -->
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input type="text" id="full_name" name="full_name" required
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors sm:text-sm outline-none" 
                            placeholder="John Doe">
                    </div>
                </div>

                <!-- Phone Field -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-medium sm:text-sm">
                            +254
                        </div>
                        <input type="tel" id="phone" name="phone" required
                            class="block w-full pl-12 pr-3 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors sm:text-sm outline-none font-mono" 
                            placeholder="712345678">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">M-Pesa registered number required.</p>
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-gray-400 font-normal">(Optional)</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input type="email" id="email" name="email"
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors sm:text-sm outline-none" 
                            placeholder="you@example.com">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Create Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password" required minlength="6"
                            class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors sm:text-sm outline-none" 
                            placeholder="At least 6 characters">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i data-lucide="eye" class="w-5 h-5" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" id="btnSubmit"
                        class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold tracking-wide text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all duration-200">
                        <span>Create Account</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                    </button>
                </div>
            </form>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="hidden justify-center items-center space-x-2 mt-4 text-brand-600 font-medium pt-2">
                <i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i>
                <span>Creating your account & wallet...</span>
            </div>

            <!-- Footer Link -->
            <p class="mt-8 text-center text-sm text-gray-500">
                Already have an account? 
                <a href="login" class="font-semibold text-brand-600 hover:text-brand-500 transition-colors">Sign in here</a>
            </p>
        </div>
    </div>

    <!-- Initialize Logic -->
    <script>
        lucide.createIcons();

        // Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            if (type === 'text') {
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        });

        // Form Submit
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('btnSubmit');
            const loading = document.getElementById('loadingIndicator');
            
            // Format phone before sending
            const formData = new FormData(this);
            const rawPhone = formData.get('phone').trim();
            if (rawPhone && !rawPhone.startsWith('0') && !rawPhone.startsWith('254') && !rawPhone.startsWith('+254')) {
                 // Assume they typed national number without leading 0 as requested by UX
                 formData.set('phone', '0' + rawPhone);
            }

            btn.classList.add('hidden');
            loading.classList.remove('hidden');
            loading.classList.add('flex');

            try {
                const data = await apiCall('auth/register.php', formData);
                
                showToast(data.message, 'success');
                
                // Redirect immediately since register logs them in
                setTimeout(() => {
                    window.location.href = 'borrower_dashboard.php';
                }, 1000);
            } catch (error) {
                // Revert buttons on error
                btn.classList.remove('hidden');
                loading.classList.add('hidden');
                loading.classList.remove('flex');
            }
        });
    </script>
</body>
</html>