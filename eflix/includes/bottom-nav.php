<?php
// includes/bottom-nav.php - Mobile Bottom Navigation Bar
?>

<!-- Bottom Navigation (Mobile) -->
<div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 py-2 max-w-md mx-auto z-40">
    <div class="flex justify-around">
        <a href="index.php" class="flex flex-col items-center <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'text-purple-600' : 'text-gray-400'; ?>">
            <i class="ri-home-5-line text-xl"></i>
            <span class="text-xs">হোম</span>
        </a>
        <a href="dashboard.php" class="flex flex-col items-center <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'text-purple-600' : 'text-gray-400'; ?>">
            <i class="ri-dashboard-line text-xl"></i>
            <span class="text-xs">ড্যাশ</span>
        </a>
        <a href="packages.php" class="flex flex-col items-center <?php echo (basename($_SERVER['PHP_SELF']) == 'packages.php') ? 'text-purple-600' : 'text-gray-400'; ?>">
            <i class="ri-shopping-box-line text-xl"></i>
            <span class="text-xs">প্যাকেজ</span>
        </a>
        <a href="tasks.php" class="flex flex-col items-center <?php echo (basename($_SERVER['PHP_SELF']) == 'tasks.php') ? 'text-purple-600' : 'text-gray-400'; ?>">
            <i class="ri-task-line text-xl"></i>
            <span class="text-xs">টাস্ক</span>
        </a>
        <a href="history.php" class="flex flex-col items-center <?php echo (basename($_SERVER['PHP_SELF']) == 'history.php') ? 'text-purple-600' : 'text-gray-400'; ?>">
            <i class="ri-history-line text-xl"></i>
            <span class="text-xs">হিস্ট্রি</span>
        </a>
        <a href="account.php" class="flex flex-col items-center <?php echo (basename($_SERVER['PHP_SELF']) == 'account.php') ? 'text-purple-600' : 'text-gray-400'; ?>">
            <i class="ri-user-line text-xl"></i>
            <span class="text-xs">একাউন্ট</span>
        </a>
    </div>
</div>

<!-- Spacer for bottom navigation -->
<div style="height: 65px;"></div>