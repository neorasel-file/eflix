<?php
// includes/header.php - Site Header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get site settings from database if needed
$siteName = 'Eflix';
$siteDescription = 'ভিডিও দেখে আয় করুন';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="<?php echo $siteDescription; ?>">
    <meta name="theme-color" content="#6366f1">
    <title><?php echo $siteName; ?> - <?php echo $pageTitle ?? 'হোম'; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #6366f1;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        .carousel::-webkit-scrollbar {
            display: none;
        }
        .carousel {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    
    <!-- Firebase SDK (if needed) -->
    <!-- <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script> -->
</head>
<body class="bg-gray-50">