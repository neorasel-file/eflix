<?php
// includes/navbar.php - Navigation Bar
// This file should be included after header.php
?>

<!-- Top Navigation Bar -->
<nav class="bg-white dark:bg-gray-900 shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4 max-w-md mx-auto">
        <div class="flex justify-between items-center h-14">
            <!-- Logo -->
            <a href="index.php" class="flex items-center space-x-2">
                <i class="ri-movie-2-fill text-2xl text-purple-600"></i>
                <span class="text-xl font-bold gradient-text">Eflix</span>
            </a>
            
            <!-- Right Menu -->
            <div class="flex items-center space-x-3">
                <!-- Theme Toggle -->
                <button onclick="toggleTheme()" class="p-2 rounded-full bg-gray-100 dark:bg-gray-800">
                    <i class="ri-sun-line text-xl dark:hidden"></i>
                    <i class="ri-moon-line text-xl hidden dark:inline"></i>
                </button>
                
                <!-- Notification -->
                <button class="p-2 rounded-full bg-gray-100 dark:bg-gray-800 relative">
                    <i class="ri-notification-3-line text-xl"></i>
                    <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                    <span class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full text-white text-[8px] flex items-center justify-center">
                        <?php echo $unreadCount; ?>
                    </span>
                    <?php endif; ?>
                </button>
                
                <!-- User Menu (if logged in) -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="relative">
                    <button onclick="toggleUserMenu()" class="flex items-center space-x-1">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-r from-purple-600 to-indigo-700 flex items-center justify-center text-white">
                            <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                        </div>
                        <i class="ri-arrow-down-s-line text-gray-500"></i>
                    </button>
                    
                    <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg hidden z-50">
                        <div class="p-3 border-b dark:border-gray-700">
                            <p class="font-semibold"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
                        </div>
                        <div class="py-1">
                            <a href="dashboard.php" class="flex items-center px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="ri-dashboard-line mr-2"></i> ড্যাশবোর্ড
                            </a>
                            <a href="account.php" class="flex items-center px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="ri-user-settings-line mr-2"></i> একাউন্ট
                            </a>
                            <a href="history.php" class="flex items-center px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="ri-history-line mr-2"></i> হিস্ট্রি
                            </a>
                            <hr class="my-1">
                            <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="ri-logout-box-line mr-2"></i> লগআউট
                            </a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="flex space-x-2">
                    <a href="login.php" class="px-3 py-1 rounded-full text-purple-600 border border-purple-600 text-sm">লগইন</a>
                    <a href="signup.php" class="px-3 py-1 rounded-full bg-purple-600 text-white text-sm">সাইনআপ</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script>
    function toggleUserMenu() {
        const menu = document.getElementById('userMenu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('userMenu');
        if (menu && !menu.contains(event.target) && !event.target.closest('[onclick="toggleUserMenu()"]')) {
            menu.classList.add('hidden');
        }
    });
</script>