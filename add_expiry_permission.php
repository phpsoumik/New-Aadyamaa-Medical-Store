<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "=== ADDING EXPIRY RETURN REPORT PERMISSION ===\n\n";
    
    // Check if permission already exists
    $permission = Spatie\Permission\Models\Permission::where('name', 'Expiry Return Report')->first();
    
    if (!$permission) {
        // Create permission
        $permission = Spatie\Permission\Models\Permission::create([
            'name' => 'Expiry Return Report',
            'guard_name' => 'web'
        ]);
        echo "Permission 'Expiry Return Report' created successfully!\n";
    } else {
        echo "Permission 'Expiry Return Report' already exists.\n";
    }
    
    // Get admin user
    $adminUser = App\Models\User::first();
    
    if ($adminUser) {
        // Give permission to admin
        $adminUser->givePermissionTo('Expiry Return Report');
        echo "Permission assigned to admin user: {$adminUser->email}\n";
    }
    
    // Clear permission cache
    Artisan::call('permission:cache-reset');
    
    echo "Permission cache cleared!\n";
    echo "Menu should now be visible in Reports page.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}