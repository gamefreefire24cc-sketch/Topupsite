<?php require_once 'db.php'; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Offer TopUp - Premium Gaming Services</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.onkeydown = function(e) {
            if (e.keyCode === 123 || (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) || (e.ctrlKey && e.keyCode === 85)) {
                e.preventDefault(); return false;
            }
        };
    </script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">
<header id="main-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-white shadow-sm">
    <div class="container mx-auto px-4 py-3 md:py-4">
        <nav class="flex items-center justify-between">
            <a href="index.php" class="flex-shrink-0">
                <img src="https://i.ibb.co.com/kg8tFxRM/Generated-Image-March-06-2026-7-17-PM-removebg-preview.png" alt="Logo" class="w-28 md:w-40 h-auto">
            </a>
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-6 font-medium text-gray-600">
                    <a href="index.php" class="hover:text-purple-600 transition-colors">Home</a>
                    <a href="topup.php" class="hover:text-purple-600 transition-colors">TopUp</a>
                    <a href="contact.php" class="hover:text-purple-600 transition-colors">Contact</a>
                </div>
                <div id="authButtons" class="flex items-center">
                    <?php if(isset($_SESSION['user_uid'])): ?>
                        <?php if($_SESSION['user_role'] === 'admin'): ?>
                            <a href="admin/dashboard.php" class="px-4 py-2 bg-gray-900 text-white text-sm font-bold rounded-lg hover:bg-black transition-colors shadow-md flex items-center gap-2 border border-gray-700">
                                <i class="ri-admin-fill text-purple-400"></i> Admin
                            </a>
                        <?php else: ?>
                            <a href="profile.php" class="relative group cursor-pointer">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-600 to-indigo-600 flex items-center justify-center text-white font-bold border-2 border-purple-200 shadow-md transform hover:scale-105">
                                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                                </div>
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="px-5 py-2 bg-purple-600 text-white text-sm font-semibold rounded-lg hover:bg-purple-700 shadow-md">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>
</header>