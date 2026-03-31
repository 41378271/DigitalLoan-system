<?php
$role = $_SESSION['role'] ?? 'borrower';
$current_route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip base path to get just the route like /dashboard
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = ($scriptName === '/' || $scriptName === '\\') ? '' : $scriptName;
if ($basePath !== '' && strpos($current_route, $basePath) === 0) {
    $current_route = substr($current_route, strlen($basePath));
}
if ($current_route === '') $current_route = '/';

$borrower_links = [
    ['url' => '/dashboard', 'icon' => 'layout-dashboard', 'title' => 'Dashboard'],
    ['url' => '/apply-loan', 'icon' => 'plus-circle', 'title' => 'Apply for Loan'],
    ['url' => '/my-loans', 'icon' => 'credit-card', 'title' => 'My Loans'],
    ['url' => '/kyc', 'icon' => 'shield-check', 'title' => 'Verification (KYC)'],
    ['url' => '/profile', 'icon' => 'user', 'title' => 'Profile Settings'],
];

$admin_links = [
    ['url' => '/admin/dashboard', 'icon' => 'pie-chart', 'title' => 'Overview'],
    ['url' => '/admin/loans', 'icon' => 'briefcase', 'title' => 'Manage Loans'],
    ['url' => '/admin/kyc', 'icon' => 'file-search', 'title' => 'Review KYC'],
    ['url' => '/admin/users', 'icon' => 'users', 'title' => 'Manage Users'],
    ['url' => '/admin/reports', 'icon' => 'file-spreadsheet', 'title' => 'Reports & Export'],
    ['url' => '/profile', 'icon' => 'user', 'title' => 'Profile Settings'],
];

$links = ($role === 'admin') ? $admin_links : $borrower_links;
?>

<!-- Desktop Sidebar -->
<div class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 pt-16 bg-white border-r border-gray-100 z-10">
    <div class="flex flex-col flex-grow pt-5 overflow-y-auto no-scrollbar">
        <div class="px-4 pb-4">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Menu</h3>
            <nav class="flex-1 space-y-1 bg-white">
                <?php foreach ($links as $link): ?>
                    <?php 
                        $isActive = ($current_route === $link['url']); 
                        $baseClasses = "group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200";
                        $activeClasses = $isActive 
                            ? "bg-brand-50 text-brand-700 shadow-sm" 
                            : "text-gray-600 hover:bg-gray-50 hover:text-gray-900";
                        
                        $iconBaseClasses = "mr-3 flex-shrink-0 w-5 h-5";
                        $iconActiveClasses = $isActive
                            ? "text-brand-600"
                            : "text-gray-400 group-hover:text-gray-500";
                    ?>
                    <a href="<?= $basePath . $link['url'] ?>" class="<?= $baseClasses ?> <?= $activeClasses ?>">
                        <i data-lucide="<?= $link['icon'] ?>" class="<?= $iconBaseClasses ?> <?= $iconActiveClasses ?>"></i>
                        <?= $link['title'] ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <div class="px-4 mt-auto pb-6">
            <a href="<?= $basePath ?>/logout" class="group flex items-center px-3 py-2.5 text-sm font-medium text-red-600 rounded-xl hover:bg-red-50 hover:text-red-700 transition-colors">
                <i data-lucide="log-out" class="mr-3 flex-shrink-0 w-5 h-5 text-red-400 group-hover:text-red-600"></i>
                Sign Out
            </a>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="md:hidden fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-gray-200">
    <div class="grid h-full max-w-lg grid-cols-5 mx-auto font-medium">
        <?php 
        // Show only max 4 links on mobile bottom nav + profile
        $mobile_links = array_slice($links, 0, 4);
        foreach ($mobile_links as $link): 
            $isActive = ($current_route === $link['url']);
            $textClass = $isActive ? "text-brand-600" : "text-gray-500";
        ?>
            <a href="<?= $basePath . $link['url'] ?>" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 group">
                <i data-lucide="<?= $link['icon'] ?>" class="w-5 h-5 mb-1 <?= $textClass ?> group-hover:text-brand-600"></i>
            </a>
        <?php endforeach; ?>
        
        <a href="<?= $basePath ?>/profile" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 group">
            <i data-lucide="user" class="w-5 h-5 mb-1 <?= ($current_route === '/profile') ? 'text-brand-600' : 'text-gray-500' ?> group-hover:text-brand-600"></i>
        </a>
    </div>
</div>
