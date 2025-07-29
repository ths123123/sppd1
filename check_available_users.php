<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== AVAILABLE USERS IN DATABASE ===\n";

$users = User::all();
echo "Total users: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo "ID: " . $user->id . " | Name: " . $user->name . " | Role: " . $user->role . "\n";
}

echo "\n=== TEST COMPLETE ===\n"; 