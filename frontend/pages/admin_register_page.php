<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $dashboard = ($_SESSION['role'] === 'admin') ? '/admin/dashboard' : '/dashboard';
    header("Location: $dashboard");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Setup | KashFlow</title>
    <?php include '../partials/head.php'; ?>
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen overflow-hidden">
    <div class="w-full max-w-md p-8 bg-white rounded-3xl shadow-2xl">
        <div class="text-center mb-10">
            <div class="w-16 h-16 bg-brand-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl shadow-brand-200">
                <i data-lucide="shield-check" class="w-8 h-8 text-white"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">Admin Setup</h2>
            <p class="text-gray-500 mt-2">Create a new administrator account</p>
        </div>

        <form id="adminRegisterForm" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name</label>
                <input type="text" name="full_name" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-brand-500 outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-brand-500 outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Secret Key</label>
                <input type="password" name="secret_key" required placeholder="Enter admin secret key" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-brand-500 outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-brand-500 outline-none transition-all">
            </div>

            <button type="submit" id="btnSubmit" class="w-full py-4 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 transition-all flex items-center justify-center">
                <span>Create Admin Account</span>
                <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
            </button>
        </form>

        <p class="text-center mt-6 text-sm text-gray-500">
            Already have an account? <a href="login" class="text-brand-600 font-bold">Log in</a>
        </p>
    </div>

    <script>
        lucide.createIcons();
        document.getElementById('adminRegisterForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmit');
            const formData = new FormData(this);

            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader-2" class="w-5 h-5 animate-spin mr-2"></i> Processing...`;
            lucide.createIcons();

            try {
                // Using the specific admin register API
                const data = await apiCall('auth/admin_register.php', formData);
                showToast(data.message, 'success');
                setTimeout(() => window.location.href = 'login', 2000);
            } catch (error) {
                btn.disabled = false;
                btn.innerHTML = `<span>Create Admin Account</span><i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>`;
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>