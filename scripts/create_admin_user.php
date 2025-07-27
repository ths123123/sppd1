<?php
// scripts/create_admin_user.php

App\Models\User::create([
    'name' => 'Fillahi',
    'email' => 'admin@kpu.go.id',
    'password' => bcrypt('72e82b77'),
    'role' => 'admin',
    'is_active' => true,
]); 