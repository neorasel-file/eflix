<?php
// index.php - Landing Page
require_once 'config/database.php';
session_start();

$siteName = 'Eflix';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo $siteName; ?> - ভিডিও দেখে আয় করুন</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .float {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-600 to-indigo-700 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white/10 backdrop-blur-lg fixed top-0 w-full z-50">
        <div class="container mx-auto px-4 py-3 max-w-md mx-auto">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <i class="ri-movie-2-fill text-2xl text-yellow-400"></i>
                    <span class="text-xl font-bold text-white">Eflix</span>
                </div>
                <div class="flex space-x-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php" class="bg-yellow-500 text-black px-4 py-1 rounded-full font-semibold">ড্যাশবোর্ড</a>
                    <?php else: ?>
                        <a href="login.php" class="text-white px-4 py-1 rounded-full border border-white/30">লগইন</a>
                        <a href="signup.php" class="bg-yellow-500 text-black px-4 py-1 rounded-full font-semibold">সাইনআপ</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container mx-auto px-4 max-w-md mx-auto pt-20">
        <div class="text-center text-white py-12">
            <div class="float mb-4">
                <div class="text-7xl">🎬</div>
            </div>
            <h1 class="text-4xl font-bold mb-3">Eflix</h1>
            <p class="text-xl mb-2">ভিডিও দেখে আয় করুন</p>
            <p class="text-white/70 text-sm mb-8">ছোট ভিডিও দেখুন, টাকা উপার্জন করুন</p>
            
            <div class="flex justify-center gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold">10K+</div>
                    <div class="text-xs text-white/60">সক্রিয় ইউজার</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold">$50K+</div>
                    <div class="text-xs text-white/60">পেমেন্ট</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold">24/7</div>
                    <div class="text-xs text-white/60">সাপোর্ট</div>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="bg-white/10 backdrop-blur rounded-2xl p-6 mb-6">
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <i class="ri-flashlight-fill text-yellow-400 text-xl"></i>
                    <span class="text-white">তাৎক্ষণিক উইথড্র (bKash/Nagad/USDT)</span>
                </div>
                <div class="flex items-center space-x-3">
                    <i class="ri-gift-fill text-pink-400 text-xl"></i>
                    <span class="text-white">প্রথম ডিপোজিটে ৫০% বোনাস</span>
                </div>
                <div class="flex items-center space-x-3">
                    <i class="ri-user-add-fill text-green-400 text-xl"></i>
                    <span class="text-white">প্রতি রেফারে $2 বোনাস</span>
                </div>
            </div>
        </div>

        <!-- CTA Button -->
        <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="signup.php" class="block">
            <div class="bg-yellow-500 rounded-2xl p-4 text-center">
                <p class="font-bold text-black text-lg">📧 ইমেইল দিয়ে শুরু করুন</p>
                <p class="text-black/70 text-sm">ফ্রি সাইনআপ, কোনো চার্জ নেই</p>
            </div>
        </a>
        <?php else: ?>
        <a href="dashboard.php" class="block">
            <div class="bg-yellow-500 rounded-2xl p-4 text-center">
                <p class="font-bold text-black text-lg">🎬 ড্যাশবোর্ডে যান</p>
                <p class="text-black/70 text-sm">আপনার আয় দেখুন</p>
            </div>
        </a>
        <?php endif; ?>
    </div>

    <!-- Bottom Navigation -->
    <div class="fixed bottom-0 left-0 right-0 bg-white/10 backdrop-blur-lg py-2 max-w-md mx-auto">
        <div class="flex justify-around">
            <a href="index.php" class="flex flex-col items-center text-yellow-400">
                <i class="ri-home-5-line text-xl"></i>
                <span class="text-xs">হোম</span>
            </a>
            <a href="packages.php" class="flex flex-col items-center text-white/60">
                <i class="ri-shopping-box-line text-xl"></i>
                <span class="text-xs">প্যাকেজ</span>
            </a>
            <a href="tasks.php" class="flex flex-col items-center text-white/60">
                <i class="ri-task-line text-xl"></i>
                <span class="text-xs">টাস্ক</span>
            </a>
            <a href="history.php" class="flex flex-col items-center text-white/60">
                <i class="ri-history-line text-xl"></i>
                <span class="text-xs">হিস্ট্রি</span>
            </a>
            <a href="account.php" class="flex flex-col items-center text-white/60">
                <i class="ri-user-line text-xl"></i>
                <span class="text-xs">একাউন্ট</span>
            </a>
        </div>
    </div>

</body>
</html>