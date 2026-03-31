<?php
// Simple Router for PHP Built-in Server & Apache (.htaccess)

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Detect base path dynamically if running in a subdirectory
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = ($scriptName === '/' || $scriptName === '\\') ? '' : $scriptName;

// Strip base path from request URI
if ($basePath !== '' && strpos($requestUri, $basePath) === 0) {
    $route = substr($requestUri, strlen($basePath));
} else {
    $route = $requestUri;
}

if ($route === '') $route = '/';

// Serve actual files directly if they exist (for css, js, images, api)
if (file_exists(__DIR__ . $route) && !is_dir(__DIR__ . $route)) {
    return false; // let the web server handle it
}

// Redirect old .php URLs to new clean URLs
if (preg_match('/^\/(.*)\.php$/', $route, $matches)) {
    $cleanPath = '/' . $matches[1];
    
    // Some specific mapping
    $redirects = [
        '/frontend/pages/index' => '/',
        '/frontend/pages/login_page' => '/login',
        '/frontend/pages/register_page' => '/register',
        '/frontend/pages/borrower_dashboard' => '/dashboard',
        '/frontend/pages/apply_loan' => '/apply-loan',
        '/frontend/pages/my_loans' => '/my-loans',
        '/frontend/pages/upload_kyc' => '/kyc',
        '/frontend/pages/profile' => '/profile',
        '/frontend/pages/notifications' => '/notifications',
        '/frontend/pages/loan_schedule' => '/loan-schedule',
        '/frontend/pages/admin_dashboard' => '/admin/dashboard',
        '/frontend/pages/admin_loans' => '/admin/loans',
        '/frontend/pages/admin_kyc' => '/admin/kyc',
        '/frontend/pages/admin_users' => '/admin/users',
        '/frontend/pages/admin_reports' => '/admin/reports',
        '/backend/api/auth/logout' => '/logout'
    ];
    
    if (isset($redirects[$cleanPath])) {
        // Create full URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        header("Location: $protocol://$host$basePath" . $redirects[$cleanPath], true, 301);
        exit;
    }
}

// Map custom routes to their respective files
$routes = [
    '/' => 'frontend/pages/index.php',
    '/login' => 'frontend/pages/login_page.php',
    '/register' => 'frontend/pages/register_page.php',
    '/dashboard' => 'frontend/pages/borrower_dashboard.php',
    '/apply-loan' => 'frontend/pages/apply_loan.php',
    '/my-loans' => 'frontend/pages/my_loans.php',
    '/kyc' => 'frontend/pages/upload_kyc.php',
    '/profile' => 'frontend/pages/profile.php',
    '/notifications' => 'frontend/pages/notifications.php',
    '/loan-schedule' => 'frontend/pages/loan_schedule.php', // requires ?id=...
    '/admin/dashboard' => 'frontend/pages/admin_dashboard.php',
    '/admin/loans' => 'frontend/pages/admin_loans.php',
    '/admin/kyc' => 'frontend/pages/admin_kyc.php',
    '/admin/users' => 'frontend/pages/admin_users.php',
    '/admin/reports' => 'frontend/pages/admin_reports.php',
    '/logout' => 'backend/api/auth/logout.php'
];

if (isset($routes[$route])) {
    $file = __DIR__ . '/' . $routes[$route];
    if (file_exists($file)) {
        // Important: change directory so relative includes inside pages work!
        chdir(dirname($file));
        require $file;
        return true;
    }
}

// 404 Not Found
http_response_code(404);
echo "<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The requested route <strong>" . htmlspecialchars($route) . "</strong> could not be found.</p></body></html>";
return true;
