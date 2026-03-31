<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $dashboard = ($_SESSION['role'] === 'admin') ? 'admin/dashboard' : 'dashboard';
    header("Location: $dashboard");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login | KashFlow Digital Loans</title>
    <?php include '../partials/head.php'; ?>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <!-- Left Hero Section (Hidden on mobile) -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-brand-900 overflow-hidden items-center justify-center">
        <!-- Background Decoration -->
        <div class="absolute w-[800px] h-[800px] bg-brand-600 rounded-full blur-3xl opacity-20 -top-40 -left-40 animate-pulse"></div>
        <div class="absolute w-[600px] h-[600px] bg-emerald-500 rounded-full blur-3xl opacity-20 bottom-0 right-0"></div>
        
        <div class="relative z-10 w-full max-w-lg px-12">
            <div class="flex items-center mb-8">
                <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center mr-4 shadow-xl">
                    <i data-lucide="wallet" class="w-6 h-6 text-brand-300"></i>
                </div>
                <h1 class="text-4xl font-extrabold text-white tracking-tight">Kash<span class="text-brand-400">Flow</span></h1>
            </div>
            
            <h2 class="text-3xl font-bold text-white mb-6 leading-tight">Fast, secure,<br>and completely digital.</h2>
            <p class="text-brand-100 text-lg mb-8 leading-relaxed">
                Access micro-loans instantly. Manage your wallet, track your repayments, and grow your credit limit all from your phone or PC.
            </p>
            
            <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-2xl">
                <div class="flex items-center gap-4 text-brand-100 mb-4">
                    <div class="w-10 h-10 rounded-full bg-brand-500/30 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="shield-check" class="w-5 h-5 text-brand-300"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white">Bank-Grade Security</h4>
                        <p class="text-sm opacity-80">Your data is encrypted and safe.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-brand-100">
                    <div class="w-10 h-10 rounded-full bg-brand-500/30 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="zap" class="w-5 h-5 text-brand-300"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white">Instant Approvals</h4>
                        <p class="text-sm opacity-80">Get funds directly to your M-Pesa immediately.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Login Section -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 relative overflow-y-auto">
        <!-- Mobile Logo (Visible only on mobile) -->
        <div class="absolute top-8 left-6 sm:left-12 lg:hidden flex items-center">
            <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center mr-2 shadow-lg">
                <i data-lucide="wallet" class="w-4 h-4 text-white"></i>
            </div>
            <span class="text-xl font-bold text-gray-900 tracking-tight">Kash<span class="text-brand-600">Flow</span></span>
        </div>

        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="mb-10 text-center lg:text-left mt-12 lg:mt-0">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome back</h2>
                <p class="text-gray-500">Please enter your details to sign in.</p>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="space-y-6">
                <!-- Identifier Field -->
                <div>
                    <label for="identifier" class="block text-sm font-medium text-gray-700 mb-1">Phone Number or Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input type="text" id="identifier" name="identifier" required
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors sm:text-sm outline-none" 
                            placeholder="Enter phone or email">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password" required
                            class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors sm:text-sm outline-none" 
                            placeholder="••••••••">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i data-lucide="eye" class="w-5 h-5" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 rounded cursor-pointer">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700 cursor-pointer">
                            Remember for 30 days
                        </label>
                    </div>
                    <div class="text-sm">
                        <a href="#" class="font-semibold text-brand-600 hover:text-brand-500 transition-colors">Forgot password?</a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" id="btnSubmit"
                        class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold tracking-wide text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-all duration-200">
                        <span>Sign In</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                    </button>
                </div>
            </form>

            <!-- Footer Link -->
            <p class="mt-8 text-center text-sm text-gray-500">
                Don't have an account? 
                <a href="register" class="font-semibold text-brand-600 hover:text-brand-500 transition-colors">Create one now</a>
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
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('btnSubmit');
            const formData = new FormData(this);

            btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin mr-2"></i> Logging in...`;
            btn.disabled = true;
            lucide.createIcons();

            try {
                const data = await apiCall('auth/login.php', formData);
                showToast(data.message, 'success');
                
                setTimeout(() => {
                    // Navigate correctly based on role
                    if (data.redirect) {
                        // We must convert backend PHP path to frontend router path
                        if (data.redirect === 'admin_dashboard.php' || data.redirect.includes('admin')) {
                            window.location.href = 'admin/dashboard';
                        } else {
                            window.location.href = 'dashboard';
                        }
                    } else {
                        window.location.href = 'dashboard';
                    }
                }, 1000);
            } catch (error) {
                // Revert button on error
                btn.innerHTML = `<span>Sign In</span><i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>`;
                btn.disabled = false;
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>