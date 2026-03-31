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
    <title>Notifications | KashFlow</title>
    <?php include '../partials/head.php'; ?>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased overflow-hidden">

    <?php include '../partials/navbar.php'; ?>
    <?php include '../partials/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="md:ml-64 pt-16 h-screen overflow-y-auto pb-24 md:pb-8 flex justify-center">
        <div class="w-full max-w-4xl px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Page Header -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                    <p class="text-sm text-gray-500 mt-1">Stay updated on your loan statuses, payments, and account activity.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <button onclick="loadNotifs()" class="text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 transition-colors px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i> Refresh
                    </button>
                    <!-- Future enhancement: Mark all as read -->
                    <button onclick="markAllRead()" class="text-gray-600 hover:text-gray-900 bg-white border border-gray-200 hover:bg-gray-50 transition-colors px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 shadow-sm">
                        <i data-lucide="check-check" class="w-4 h-4"></i> Mark all read
                    </button>
                </div>
            </div>

            <!-- Global Loading State -->
            <div id="loadingState" class="flex flex-col items-center justify-center py-20">
                <i data-lucide="loader-2" class="w-10 h-10 animate-spin text-brand-500 mb-4"></i>
                <p class="text-gray-500 font-medium">Fetching notifications...</p>
            </div>

            <!-- Empty State (Hidden initially) -->
            <div id="emptyState" class="hidden flex-col items-center justify-center py-20 px-4 text-center bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 relative">
                    <i data-lucide="bell-off" class="w-12 h-12 text-gray-400"></i>
                    <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-white rounded-full flex items-center justify-center border-2 border-gray-50">
                        <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                            <i data-lucide="check" class="w-3 h-3 text-green-600"></i>
                        </div>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">You're all caught up!</h3>
                <p class="text-gray-500 max-w-sm">There are no new notifications or alerts for your account right now.</p>
            </div>

            <!-- Notifications Feed Container -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hidden" id="listContainer">
                <ul id="list" class="divide-y divide-gray-100">
                    <!-- Notifications injected here -->
                </ul>
            </div>

        </div>
    </main>

    <script>
        lucide.createIcons();

        const loadingState = document.getElementById('loadingState');
        const emptyState = document.getElementById('emptyState');
        const listContainer = document.getElementById('listContainer');
        const listEl = document.getElementById('list');

        function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);
            
            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
            if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;
            
            return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
        }

        async function loadNotifs(){
            // Reset UI
            loadingState.classList.remove('hidden');
            emptyState.classList.add('hidden');
            listContainer.classList.add('hidden');
            listEl.innerHTML = "";

            try {
                const res = await fetch("../../backend/api/notifications/list.php");
                const data = await res.json();

                if(!data.success){
                    showToast(data.message || "Failed to load notifications.", "error");
                    loadingState.classList.add('hidden');
                    return;
                }

                const rows = data.rows || [];

                if(rows.length === 0){
                    loadingState.classList.add('hidden');
                    emptyState.classList.remove('hidden');
                    return;
                }

                // Render Notifications
                rows.forEach(n => {
                    renderNotification(n);
                });

                loadingState.classList.add('hidden');
                listContainer.classList.remove('hidden');
                lucide.createIcons();

            } catch (err) {
                console.error("Notifs error:", err);
                showToast("Network error. Could not load notifications.", "error");
                loadingState.classList.add('hidden');
            }
        }

        function renderNotification(n) {
            const isRead = parseInt(n.is_read) === 1;
            const bgClass = isRead ? "bg-white hover:bg-gray-50" : "bg-blue-50/50 hover:bg-blue-50";
            const borderClass = isRead ? "border-transparent" : "border-brand-500";
            const titleColor = isRead ? "text-gray-900" : "text-brand-900 font-bold";
            const iconBg = isRead ? "bg-gray-100 text-gray-500" : "bg-brand-100 text-brand-600";
            
            // Determine icon based on title keywords (simple heuristics)
            let iconStr = "bell";
            const t = (n.title || "").toLowerCase();
            if (t.includes('loan') || t.includes('approve') || t.includes('reject')) iconStr = "file-text";
            if (t.includes('pay') || t.includes('wallet') || t.includes('deposit') || t.includes('fund')) iconStr = "wallet";
            if (t.includes('kyc') || t.includes('document') || t.includes('profile')) iconStr = "user-check";
            if (t.includes('alert') || t.includes('warning') || t.includes('fail')) iconStr = "alert-circle";

            const timeAgo = formatTimeAgo(n.created_at);

            const li = document.createElement("li");
            li.className = `p-6 transition-colors border-l-4 ${borderClass} ${bgClass} group`;
            
            // Generate HTML
            li.innerHTML = `
                <div class="flex gap-4">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 ${iconBg} rounded-full flex items-center justify-center">
                            <i data-lucide="${iconStr}" class="w-5 h-5"></i>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-4 mb-1">
                            <h4 class="text-sm ${titleColor} truncate">${n.title}</h4>
                            <span class="text-xs text-gray-500 whitespace-nowrap flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i> ${timeAgo}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed mb-3">${n.message}</p>
                        
                        <!-- Actions -->
                        ${!isRead ? `
                            <div class="flex items-center justify-start">
                                <button onclick="markRead(${n.id}, this)" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-800 transition-colors bg-white px-3 py-1.5 rounded-md shadow-sm border border-brand-100 hover:border-brand-200">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Mark as read
                                </button>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
            
            listEl.appendChild(li);
        }

        async function markRead(id, btnElement){
            if(btnElement) {
                const ogHtml = btnElement.innerHTML;
                btnElement.disabled = true;
                btnElement.innerHTML = `<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>`;
                lucide.createIcons();
            }

            try {
                const form = new FormData();
                form.append("id", id);
                
                const res = await fetch("../../backend/api/notifications/mark_read.php", {
                    method:"POST",
                    body: form
                });
                
                const data = await res.json();
                
                if(data.success) {
                    // Update UI immediately without full reload for smoother UX
                    const li = btnElement.closest('li');
                    if(li) {
                        li.classList.remove('bg-blue-50/50', 'hover:bg-blue-50', 'border-brand-500');
                        li.classList.add('bg-white', 'hover:bg-gray-50', 'border-transparent');
                        
                        const title = li.querySelector('h4');
                        if(title) {
                            title.classList.remove('text-brand-900', 'font-bold');
                            title.classList.add('text-gray-900');
                        }
                        
                        const iconWrapper = li.querySelector('.flex-shrink-0 > div');
                        if(iconWrapper) {
                            iconWrapper.classList.remove('bg-brand-100', 'text-brand-600');
                            iconWrapper.classList.add('bg-gray-100', 'text-gray-500');
                        }
                        
                        // remove button container
                        btnElement.closest('.flex.items-center.justify-start').remove();
                    }
                    
                    // Force update global unread count if we have access to it via navbar script
                    if(typeof loadUnreadCount === 'function') {
                        loadUnreadCount();
                    }
                } else {
                    showToast(data.message || "Failed to mark as read", "error");
                    if(btnElement) {
                        btnElement.disabled = false;
                        btnElement.innerHTML = `<i data-lucide="check" class="w-3.5 h-3.5"></i> Mark as read`;
                        lucide.createIcons();
                    }
                }
            } catch(e) {
                console.error(e);
                showToast("Network error", "error");
                if(btnElement) {
                        btnElement.disabled = false;
                        btnElement.innerHTML = `<i data-lucide="check" class="w-3.5 h-3.5"></i> Mark as read`;
                        lucide.createIcons();
                    }
            }
        }

        async function markAllRead() {
            // Future enhancement handler: could loop over unread IDs or need a new backend endpoint
            showToast("Marking all as read is currently being implemented.", "info");
        }

        // Init
        document.addEventListener('DOMContentLoaded', loadNotifs);
    </script>
</body>
</html>