<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Get admin user
    $user = App\Models\User::find(1);
    
    if (!$user) {
        echo "Admin user not found!\n";
        exit;
    }
    
    // Get all permissions
    $permissions = Spatie\Permission\Models\Permission::all();
    
    // Give all permissions to admin
    $user->givePermissionTo($permissions);
    
    echo "All permissions assigned to admin user successfully!\n";
    echo "User: " . $user->name . " (" . $user->email . ")\n";
    echo "Total permissions: " . $permissions->count() . "\n";
    
    // Clear permission cache
    Artisan::call('permission:cache-reset');
    Artisan::call('cache:clear');
    
    echo "Cache cleared successfully!\n";
    echo "You can now login to the application.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}