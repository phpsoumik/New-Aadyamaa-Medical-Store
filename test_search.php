<?php
// Simple test script to check database connection and stock data
require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Database configuration
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'adyamaa_new', // Change this to your database name
    'username' => 'root', // Change this to your database username
    'password' => '', // Change this to your database password
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    // Test database connection
    $connection = Capsule::connection();
    echo "Database connection: OK\n";
    
    // Check if stocks table exists
    $tables = $connection->select("SHOW TABLES LIKE 'stocks'");
    if (empty($tables)) {
        echo "Error: 'stocks' table does not exist\n";
        exit;
    }
    echo "Stocks table: EXISTS\n";
    
    // Count total stocks
    $totalStocks = $connection->table('stocks')->count();
    echo "Total stocks: $totalStocks\n";
    
    // Count active stocks with quantity > 0
    $activeStocks = $connection->table('stocks')
        ->where('status', 'Active')
        ->where('qty', '>', 0)
        ->count();
    echo "Active stocks with qty > 0: $activeStocks\n";
    
    // Show sample stock data
    $sampleStocks = $connection->table('stocks')
        ->where('status', 'Active')
        ->where('qty', '>', 0)
        ->limit(5)
        ->get(['id', 'product_name', 'qty', 'branch_id']);
    
    echo "\nSample stock data:\n";
    foreach ($sampleStocks as $stock) {
        echo "ID: {$stock->id}, Name: {$stock->product_name}, Qty: {$stock->qty}, Branch: {$stock->branch_id}\n";
    }
    
    // Test search functionality
    $keyword = 'a'; // Simple search
    $searchResults = $connection->table('stocks')
        ->where('status', 'Active')
        ->where('qty', '>', 0)
        ->where('product_name', 'LIKE', '%' . $keyword . '%')
        ->limit(3)
        ->get(['id', 'product_name', 'qty']);
    
    echo "\nSearch results for '$keyword':\n";
    foreach ($searchResults as $result) {
        echo "ID: {$result->id}, Name: {$result->product_name}, Qty: {$result->qty}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>