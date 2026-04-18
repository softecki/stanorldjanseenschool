<?php
/**
 * Route Checker Script
 * 
 * This script checks if specific routes are registered in Laravel.
 * Upload this file to your production server root and access it via browser.
 * 
 * IMPORTANT: Delete this file after use for security reasons!
 */

// Ensure this is the Laravel root directory
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// Routes to check
$routesToCheck = [
    'fees-collect.index',
    'fees-collect.collect-list',
    'fees-collect.collect-transactions',
    'fees-collect.collect-amendment',
];

echo "<!DOCTYPE html>
<html>
<head>
    <title>Laravel Route Checker</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .route { background: #f4f4f4; padding: 10px; margin: 10px 0; border-left: 4px solid #333; }
        .warning { background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #333; color: white; }
    </style>
</head>
<body>
    <h1>Laravel Route Checker</h1>
    <div class='warning'>
        <strong>⚠️ Security Warning:</strong> Delete this file immediately after use!
    </div>
    <h2>Checking Routes...</h2>
    <table>
        <tr>
            <th>Route Name</th>
            <th>Status</th>
            <th>URL</th>
        </tr>";

foreach ($routesToCheck as $routeName) {
    try {
        $url = route($routeName);
        $status = "<span class='success'>✓ Exists</span>";
        $urlDisplay = htmlspecialchars($url);
    } catch (Exception $e) {
        $status = "<span class='error'>✗ Not Found</span>";
        $urlDisplay = "<span class='error'>" . htmlspecialchars($e->getMessage()) . "</span>";
    }
    
    echo "<tr>";
    echo "<td><strong>{$routeName}</strong></td>";
    echo "<td>{$status}</td>";
    echo "<td>{$urlDisplay}</td>";
    echo "</tr>";
}

echo "
    </table>
    <h2>All Registered Routes</h2>
    <p>Total routes: " . count(Route::getRoutes()) . "</p>
    <details>
        <summary>Click to view all routes (may be long)</summary>
        <pre style='background: #f4f4f4; padding: 10px; overflow-x: auto;'>";

$routes = Route::getRoutes();
foreach ($routes as $route) {
    if ($route->getName()) {
        echo htmlspecialchars($route->getName()) . " => " . htmlspecialchars($route->uri()) . "\n";
    }
}

echo "
        </pre>
    </details>
    <p><strong>IMPORTANT:</strong> Please delete this file now for security reasons.</p>
</body>
</html>";
?>
