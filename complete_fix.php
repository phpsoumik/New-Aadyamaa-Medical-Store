<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "=== FIXING USER AND PERMISSIONS ===\n\n";
    
    // Check existing users
    $users = App\Models\User::all();
    echo "Found " . $users->count() . " users:\n";
    foreach ($users as $user) {
        echo "- ID: {$user->id}, Email: {$user->email}, Name: {$user->name}\n";
    }
    echo "\n";
    
    // Get or create admin user
    $adminUser = App\Models\User::where('email', 'sam.gupta@deltaminds.com')->first();
    
    if (!$adminUser) {
        echo "Creating new admin user...\n";
        $adminUser = App\Models\User::create([
            'branch_id' => 1,
            'role' => 3,
            'name' => 'Admin',
            'email' => 'admin@spantiklab.com',
            'email_verified_at' => now(),
            'password' => bcrypt('123456'),
            'status' => 'Active',
            'address' => '',
            'contact' => '',
            'image' => 'default.jpg',
            'description' => '',
        ]);
    } else {
        echo "Using existing admin user: {$adminUser->email}\n";
        // Reset password
        $adminUser->update([
            'password' => bcrypt('123456'),
            'status' => 'Active'
        ]);
    }
    
    // Get all permissions
    $permissions = Spatie\Permission\Models\Permission::all();
    echo "Found " . $permissions->count() . " permissions\n";
    
    // Clear existing permissions for admin
    $adminUser->permissions()->detach();
    
    // Give all permissions to admin
    $adminUser->givePermissionTo($permissions);
    
    echo "All permissions assigned to admin user!\n";
    echo "Login credentials:\n";
    echo "Email: " . $adminUser->email . "\n";
    echo "Password: 123456\n\n";
    
    // Clear caches
    Artisan::call('permission:cache-reset');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    
    echo "All caches cleared!\n";
    echo "You can now login to the application.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}