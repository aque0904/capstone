<?php
include 'database.php';

// Only allow this to run once
if (file_exists('users_created.flag')) {
    die("Accounts already created!");
}

// Admin account
$admin_username = 'admin';
$admin_password = password_hash('admin123', PASSWORD_DEFAULT); // Change 'admin123'

// Staff account
$staff_username = 'staff';
$staff_password = password_hash('staff123', PASSWORD_DEFAULT); // Change 'staff123'

// Insert admin
$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
$stmt->bind_param("ss", $admin_username, $admin_password);
$stmt->execute();

// Insert staff
$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'staff')");
$stmt->bind_param("ss", $staff_username, $staff_password);
$stmt->execute();

// Create flag file
file_put_contents('users_created.flag', 'Accounts created on '.date('Y-m-d H:i:s'));

echo "Admin and staff accounts created successfully!";
echo "<br>Admin username: admin";
echo "<br>Staff username: staff";
echo "<br>IMPORTANT: Delete this file after use!";
?>