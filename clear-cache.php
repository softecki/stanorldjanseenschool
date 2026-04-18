<?php
/**
 * Cache Clearing Script
 * 
 * This script helps clear Laravel caches when you cannot access the command line.
 * Upload this file to your production server root and access it via browser.
 * 
 * IMPORTANT: Delete this file after use for security reasons!
 */

// Ensure this is the Laravel root directory
$basePath = __DIR__;

// Function to execute artisan commands
function runArtisanCommand($command) {
    $output = [];
    $returnVar = 0;
    
    exec("php artisan {$command} 2>&1", $output, $returnVar);
    
    return [
        'command' => $command,
        'output' => implode("\n", $output),
        'success' => $returnVar === 0
    ];
}

// Commands to run
$commands = [
    'route:clear',
    'config:clear',
    'cache:clear',
    'view:clear',
    'route:cache',
    'config:cache'
];

echo "<!DOCTYPE html>
<html>
<head>
    <title>Laravel Cache Clearer</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .command { background: #f4f4f4; padding: 10px; margin: 10px 0; border-left: 4px solid #333; }
        .warning { background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Laravel Cache Clearer</h1>
    <div class='warning'>
        <strong>⚠️ Security Warning:</strong> Delete this file immediately after use!
    </div>
    <h2>Clearing Caches...</h2>";

foreach ($commands as $command) {
    $result = runArtisanCommand($command);
    $statusClass = $result['success'] ? 'success' : 'error';
    $statusText = $result['success'] ? '✓ Success' : '✗ Failed';
    
    echo "<div class='command'>";
    echo "<strong class='{$statusClass}'>{$statusText}</strong> - php artisan {$result['command']}<br>";
    echo "<pre>" . htmlspecialchars($result['output']) . "</pre>";
    echo "</div>";
}

echo "
    <h2>Done!</h2>
    <p><strong>IMPORTANT:</strong> Please delete this file now for security reasons.</p>
</body>
</html>";
?>
