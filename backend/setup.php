<?php
echo "<h1>Laravel Setup Script</h1>";
echo "<pre>";

// Change to the correct directory
chdir(__DIR__);

// Generate APP_KEY
echo "Generating APP_KEY...\n";
$output = shell_exec('php artisan key:generate');
echo $output . "\n";

// Run migrations
echo "Running migrations...\n";
$output = shell_exec('php artisan migrate --force');
echo $output . "\n";

echo "</pre>";
echo "Setup complete! You can delete this file now.";
?> 