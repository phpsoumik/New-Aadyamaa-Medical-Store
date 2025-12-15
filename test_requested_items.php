<?php
require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'adyamaa_new',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    $requestedItems = Capsule::table('requested_items')
        ->where('order_status', 'pending')
        ->where('status', 'Active')
        ->select('medicine_name')
        ->get();
    
    echo "Total pending requested items: " . $requestedItems->count() . "\n\n";
    
    foreach ($requestedItems as $item) {
        echo "Medicine: " . $item->medicine_name . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>