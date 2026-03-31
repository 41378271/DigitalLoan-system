<?php
$user_name = $_SESSION['name'] ?? 'User';
$role = $_SESSION['role'] ?? 'borrower';
?>

<nav class="bg-white border-b border-gray-100 z-30 fixed w-full top-0">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex items-center flex-shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center">
                        <i data-lucide="wallet" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="ml-2 text-xl font-bold text-gray-900 tracking-tight">Kash<span class="text-brand-600">Flow</span></span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                
                <!-- Notification Bell -->
                <a href="/digital-loan-system/frontend/pages/notifications.php" class="relative p-2 text-gray-500 transition-colors bg-white rounded-full hover:bg-gray-100 hover:text-gray-700">
                    <span class="sr-only">View notifications</span>
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span id="globalNotifBadge" class="absolute top-1.5 right-1.5 flex hidden h-2.5 w-2.5">
                        <span class="absolute inline-flex w-full h-full bg-red-400 rounded-full opacity-75 animate-ping"></span>
                        <span class="relative inline-flex w-2.5 h-2.5 bg-red-500 rounded-full"></span>
                    </span>
                </a>

                <!-- Profile Dropdown (Simplified) -->
                <div class="relative ml-2">
                    <div class="flex items-center gap-3">
                        <div class="flex flex-col text-right hidden sm:block">
                            <span class="text-sm font-medium text-gray-900 leading-none"><?= htmlspecialchars($user_name) ?></span>
                            <span class="text-xs text-gray-500 mt-1 capitalize"><?= htmlspecialchars($role) ?></span>
                        </div>
                        <a href="/digital-loan-system/frontend/pages/profile.php" class="flex text-sm bg-brand-100 rounded-full focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 w-9 h-9 items-center justify-center">
                            <span class="text-brand-700 font-bold"><?= substr(htmlspecialchars($user_name), 0, 1) ?></span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</nav>

<script>
    // Global notification checker on all pages
    document.addEventListener("DOMContentLoaded", async () => {
        try {
            const res = await fetch("/digital-loan-system/backend/api/notifications/unread_count.php");
            const data = await res.json();
            if (data.success && data.count > 0) {
                document.getElementById('globalNotifBadge').classList.remove('hidden');
            }
        } catch (e) {
            console.error("Failed to load generic notifications");
        }
    });
</script>
