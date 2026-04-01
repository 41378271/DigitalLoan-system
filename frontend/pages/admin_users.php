<?php 
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  header("Location: /login");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users | Admin | KashFlow</title>
    <?php include '../partials/head.php'; ?>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased overflow-hidden">

    <?php include '../partials/navbar.php'; ?>

    <?php include '../partials/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="md:ml-64 pt-16 h-screen overflow-y-auto pb-24 md:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Page Header -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">User Management</h1>
                    <p class="text-sm text-gray-500 mt-1">View, search, and manage account statuses across the platform.</p>
                </div>
                
                <!-- Filters & Actions -->
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                        </div>
                        <input type="text" id="q" placeholder="Search name, email, phone..." class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors shadow-sm placeholder-gray-400">
                    </div>
                    
                    <select id="only" class="w-full sm:w-auto px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors shadow-sm appearance-none cursor-pointer pr-10 relative">
                        <option value="all">All Users</option>
                        <option value="active">Active Only</option>
                        <option value="deactivated">Deactivated Only</option>
                    </select>
                    <!-- Custom chevron for select -->
                    <div class="absolute right-4 sm:right-[112px] top-[14px] pointer-events-none hidden sm:block">
                        <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                    </div>

                    <button onclick="loadUsers()" class="w-full sm:w-auto text-white bg-brand-600 hover:bg-brand-700 transition-colors px-4 py-2 rounded-lg text-sm font-medium shadow-sm flex items-center justify-center gap-2">
                        <i data-lucide="search" class="w-4 h-4"></i> Search
                    </button>
                </div>
            </div>

            <!-- Global Loading State -->
            <div id="loadingState" class="flex flex-col items-center justify-center py-20">
                <i data-lucide="loader-2" class="w-10 h-10 animate-spin text-brand-500 mb-4"></i>
                <p class="text-gray-500 font-medium">Loading user directory...</p>
            </div>

            <!-- Main Content Container -->
            <div id="contentContainer" class="hidden">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="usersTable">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                                    <th class="px-6 py-4">User</th>
                                    <th class="px-6 py-4">Role & Status</th>
                                    <th class="px-6 py-4">Contact</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100" id="usersTableBody">
                                <!-- Rows injected here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="hidden flex-col items-center justify-center py-16 px-4 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="users" class="w-8 h-8 text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">No Users Found</h3>
                        <p class="text-gray-500 text-sm max-w-sm">Try adjusting your search query or filters.</p>
                        <button onclick="document.getElementById('q').value=''; document.getElementById('only').value='all'; loadUsers();" class="mt-4 text-brand-600 hover:text-brand-800 text-sm font-medium">
                            Clear Filters
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeConfirmModal()"></div>
        
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10" id="confirmIconBg">
                                <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600" id="confirmIcon"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-bold leading-6 text-gray-900" id="confirmTitle">Deactivate User</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500" id="confirmMessage">Are you sure you want to deactivate this account? They will lose access to the platform immediately.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" id="confirmActionBtn" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition-colors">
                            Yes, deactivate
                        </button>
                        <button type="button" onclick="closeConfirmModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        lucide.createIcons();

        const loadingState = document.getElementById('loadingState');
        const contentContainer = document.getElementById('contentContainer');
        const usersTableBody = document.getElementById('usersTableBody');
        const emptyState = document.getElementById('emptyState');
        const usersTable = document.getElementById('usersTable').parentNode;

        // Modal Elements
        const confirmModal = document.getElementById('confirmModal');
        const confirmTitle = document.getElementById('confirmTitle');
        const confirmMessage = document.getElementById('confirmMessage');
        const confirmActionBtn = document.getElementById('confirmActionBtn');
        const confirmIconBg = document.getElementById('confirmIconBg');
        const confirmIcon = document.getElementById('confirmIcon');

        let pendingUserId = null;
        let pendingActionIsActive = null;

        // Allow Enter key to trigger search
        document.getElementById('q').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                loadUsers();
            }
        });

        document.getElementById('only').addEventListener('change', loadUsers);

        function escapeHtml(s){
            return String(s || "")
                .replaceAll("&","&amp;")
                .replaceAll("<","&lt;")
                .replaceAll(">","&gt;")
                .replaceAll('"',"&quot;")
                .replaceAll("'","&#039;");
        }

        function formatStatusBadge(isActive) {
            if (isActive) return `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active</span>`;
            return `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200 uppercase tracking-wider"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Deactivated</span>`;
        }

        function formatRoleBadge(role) {
            const r = (role || "").toLowerCase();
            if (r === 'admin') return `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">Admin</span>`;
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200 capitalize">${escapeHtml(role)}</span>`;
        }

        async function loadUsers() {
            const q = document.getElementById("q").value.trim();
            const only = document.getElementById("only").value;

            loadingState.classList.remove('hidden');
            contentContainer.classList.add('hidden');
            usersTableBody.innerHTML = '';
            
            try {
                const url = `<?= $basePath ?? '' ?>/backend/api/admin/users/list.php?q=${encodeURIComponent(q)}&only=${encodeURIComponent(only)}`;
                const res = await fetch(url);
                const data = await res.json();

                if (!data.success) {
                    showToast(data.message || "Failed to load users", "error");
                    loadingState.classList.add('hidden');
                    return;
                }

                if (!data.users || data.users.length === 0) {
                    emptyState.classList.remove('hidden');
                    usersTable.classList.add('hidden');
                } else {
                    emptyState.classList.add('hidden');
                    usersTable.classList.remove('hidden');
                    
                    data.users.forEach(u => {
                        const tr = document.createElement('tr');
                        tr.className = "hover:bg-gray-50/50 transition-colors";
                        
                        const isActive = Number(u.is_active) === 1;
                        const isAdmin = (u.role || "").toLowerCase() === "admin";
                        
                        const dateStr = u.created_at ? new Date(u.created_at).toLocaleDateString('en-GB', { day: 'short', month: 'short', year:'numeric'}) : '-';

                        // Calculate initials for avatar
                        const nameParts = (u.full_name || "Unknown").split(' ');
                        const initials = nameParts.length > 1 
                            ? (nameParts[0][0] + nameParts[nameParts.length-1][0]).toUpperCase()
                            : nameParts[0].substring(0, 2).toUpperCase();

                        let actionBtn = "";
                        if (isAdmin) {
                            actionBtn = `<span class="text-xs font-medium text-gray-400 italic flex items-center justify-end gap-1"><i data-lucide="shield" class="w-3.5 h-3.5"></i> Protected</span>`;
                        } else {
                            if (isActive) {
                                actionBtn = `<button onclick="promptAction(${u.id}, 0, '${escapeHtml(u.full_name)}')" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1.5 text-xs font-medium border border-red-100">Deactivate</button>`;
                            } else {
                                actionBtn = `<button onclick="promptAction(${u.id}, 1, '${escapeHtml(u.full_name)}')" class="text-emerald-600 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1.5 text-xs font-medium border border-emerald-100">Reactivate</button>`;
                            }
                        }

                        tr.innerHTML = `
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="hidden sm:flex flex-shrink-0 w-10 h-10 rounded-full bg-brand-100 text-brand-700 items-center justify-center font-bold text-sm border border-brand-200">
                                        ${initials}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">${escapeHtml(u.full_name || "-")}</div>
                                        <div class="text-xs text-gray-400 font-mono mt-0.5">ID #${u.id} • Joined ${dateStr}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col items-start gap-1.5">
                                    ${formatRoleBadge(u.role)}
                                    ${formatStatusBadge(isActive)}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-700 flex items-center gap-1.5 mb-1"><i data-lucide="mail" class="w-3.5 h-3.5 text-gray-400"></i> ${escapeHtml(u.email || "-")}</div>
                                <div class="text-sm text-gray-700 flex items-center gap-1.5"><i data-lucide="phone" class="w-3.5 h-3.5 text-gray-400"></i> ${escapeHtml(u.phone || "-")}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                ${actionBtn}
                            </td>
                        `;
                        usersTableBody.appendChild(tr);
                    });
                }
                
                loadingState.classList.add('hidden');
                contentContainer.classList.remove('hidden');
                lucide.createIcons();

            } catch (error) {
                console.error("Users load error:", error);
                showToast("Network error. Could not load directory.", "error");
                loadingState.classList.add('hidden');
            }
        }

        // --- Modal Handling ---
        function promptAction(userId, isActive, userName) {
            pendingUserId = userId;
            pendingActionIsActive = isActive;
            
            if(isActive === 0) {
                // Deactivate
                confirmTitle.innerText = "Deactivate User";
                confirmTitle.className = "text-lg font-bold leading-6 text-red-900";
                confirmMessage.innerText = `Are you sure you want to deactivate ${userName}? They will immediately lose access to the platform.`;
                
                confirmActionBtn.className = "inline-flex w-full justify-center rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition-colors";
                confirmActionBtn.innerText = "Yes, deactivate";
                
                confirmIconBg.className = "mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10";
                confirmIcon.className = "h-6 w-6 text-red-600";
                // Wait to swap icon until feather re-renders
                setTimeout(() => { confirmIcon.setAttribute('data-lucide', 'alert-triangle'); lucide.createIcons(); }, 10);
                
            } else {
                // Reactivate
                confirmTitle.innerText = "Reactivate User";
                confirmTitle.className = "text-lg font-bold leading-6 text-emerald-900";
                confirmMessage.innerText = `Are you sure you want to reactivate ${userName}? Their platform access will be restored.`;
                
                confirmActionBtn.className = "inline-flex w-full justify-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:ml-3 sm:w-auto transition-colors";
                confirmActionBtn.innerText = "Yes, reactivate";
                
                confirmIconBg.className = "mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10";
                confirmIcon.className = "h-6 w-6 text-emerald-600";
                setTimeout(() => { confirmIcon.setAttribute('data-lucide', 'user-check'); lucide.createIcons(); }, 10);
            }

            confirmModal.classList.remove('hidden');
        }

        function closeConfirmModal() {
            confirmModal.classList.add('hidden');
            pendingUserId = null;
            pendingActionIsActive = null;
        }

        confirmActionBtn.addEventListener('click', async () => {
            if(!pendingUserId) return;
            
            const originalText = confirmActionBtn.innerText;
            confirmActionBtn.disabled = true;
            confirmActionBtn.innerText = "Processing...";

            try {
                const fd = new FormData();
                fd.append("user_id", pendingUserId);
                fd.append("is_active", pendingActionIsActive);

                const res = await fetch("<?= $basePath ?? '' ?>/backend/api/admin/users/set_active.php", {
                    method: "POST",
                    body: fd
                });

                const data = await res.json();
                
                if(data.success) {
                    showToast(data.message || (pendingActionIsActive ? "User reactivated" : "User deactivated"), "success");
                    loadUsers();
                } else {
                    showToast(data.message || "Action failed", "error");
                }
            } catch (err) {
                console.error(err);
                showToast("Network error occurred", "error");
            } finally {
                confirmActionBtn.disabled = false;
                confirmActionBtn.innerText = originalText;
                closeConfirmModal();
            }
        });

        // Init
        document.addEventListener('DOMContentLoaded', loadUsers);
    </script>
</body>
</html>