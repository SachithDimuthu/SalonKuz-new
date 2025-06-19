<?php
// login.php - Role selection page for Salon Kuz
$pageTitle = "Login as...";
require_once 'includes/header.php';
?>

<div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-8 text-pink-600">Login as...</h2>
        <div class="flex flex-col gap-6">
            <a href="admin-login.php" class="block w-full text-center py-3 px-6 rounded bg-pink-500 text-white font-semibold text-lg hover:bg-pink-600 transition">Admin</a>
            <a href="user-login.php" class="block w-full text-center py-3 px-6 rounded bg-gray-200 text-pink-700 font-semibold text-lg hover:bg-pink-100 transition">User</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
