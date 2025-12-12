<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "=== DEBUGGING PERMISSIONS ===\n\n";
    
    // Get admin user
    $adminUser = App\Models\User::first();
    echo "Admin User: {$adminUser->name} ({$adminUser->email})\n\n";
    
    // Check specific permissions
    $checkPermissions = [
        'Stock Expiry Report',
        'Expiry Return Report',
        'Report',
        'Stock Report',
        'Stock Alert Report'
    ];
    
    echo "Checking permissions:\n";
    foreach ($checkPermissions as $permName) {
        $hasPermission = $adminUser->hasPermissionTo($permName);
        $permExists = Spatie\Permission\Models\Permission::where('name', $permName)->exists();
        
        echo "- {$permName}: ";
        echo "Exists=" . ($permExists ? 'YES' : 'NO') . ", ";
        echo "HasPermission=" . ($hasPermission ? 'YES' : 'NO') . "\n";
        
        // If permission exists but user doesn't have it, assign it
        if ($permExists && !$hasPermission) {
            $adminUser->givePermissionTo($permName);
            echo "  -> Permission assigned!\n";
        }
        
        // If permission doesn't exist, create it
        if (!$permExists) {
            Spatie\Permission\Models\Permission::create([
                'name' => $permName,
                'guard_name' => 'web'
            ]);
            $adminUser->givePermissionTo($permName);
            echo "  -> Permission created and assigned!\n";
        }
    }
    
    echo "\n=== FINAL CHECK ===\n";
    foreach ($checkPermissions as $permName) {
        $hasPermission = $adminUser->hasPermissionTo($permName);
        echo "- {$permName}: " . ($hasPermission ? 'YES' : 'NO') . "\n";
    }
    
    // Clear all caches
    Artisan::call('permission:cache-reset');
    Artisan::call('cache:clear');
    
    echo "\nAll caches cleared!\n";
    echo "Now refresh browser and check Reports page.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}