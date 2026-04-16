<?php
// admin/users.php - User Management
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php?admin=eflix2024');
    exit();
}

$db = getDB();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    
    if ($action == 'delete' && $user_id) {
        $stmt = $db->prepare("DELETE FROM profiles WHERE id = ?");
        $stmt->execute([$user_id]);
        header('Location: users.php?admin=eflix2024');
        exit();
    }
    
    if ($action == 'update_balance' && $user_id) {
        $new_balance = floatval($_POST['balance'] ?? 0);
        $stmt = $db->prepare("UPDATE profiles SET balance = ? WHERE id = ?");
        $stmt->execute([$new_balance, $user_id]);
        header('Location: users.php?admin=eflix2024');
        exit();
    }
    
    if ($action == 'toggle_status' && $user_id) {
        $stmt = $db->prepare("UPDATE profiles SET status = CASE WHEN status = 'active' THEN 'blocked' ELSE 'active' END WHERE id = ?");
        $stmt->execute([$user_id]);
        header('Location: users.php?admin=eflix2024');
        exit();
    }
}

// Get all users
$users = $db->query("SELECT * FROM profiles ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ইউজার ম্যানেজ - Eflix Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-purple-800 to-indigo-900 text-white fixed h-full">
            <div class="p-4">
                <h1 class="text-xl font-bold">Eflix Admin</h1>
                <p class="text-xs text-white/60">ইউজার ম্যানেজমেন্ট</p>
            </div>
            <nav class="mt-8">
                <a href="index.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">📊 ড্যাশবোর্ড</a>
                <a href="users.php?admin=eflix2024" class="block py-2 px-4 bg-white/20">👥 ইউজার</a>
                <a href="packages.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💎 প্যাকেজ</a>
                <a href="tasks.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">📋 টাস্ক</a>
                <a href="withdrawals.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💸 উইথড্র</a>
                <a href="deposits.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💰 ডিপোজিট</a>
                <a href="settings.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">⚙️ সেটিংস</a>
                <a href="../logout.php" class="block py-2 px-4 mt-8 text-red-300 hover:bg-red-800">🚪 লগআউট</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 ml-64 p-6">
            <h1 class="text-2xl font-bold mb-6">👥 ইউজার ম্যানেজমেন্ট</h1>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="text-left p-3">নাম</th>
                                <th class="text-left p-3">ইমেইল</th>
                                <th class="text-left p-3">ফোন</th>
                                <th class="text-left p-3">ব্যালেন্স</th>
                                <th class="text-left p-3">প্যাকেজ</th>
                                <th class="text-left p-3">স্ট্যাটাস</th>
                                <th class="text-left p-3">জয়েন তারিখ</th>
                                <th class="text-left p-3">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3"><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                <td class="p-3 font-semibold text-purple-600">$<?php echo number_format($user['balance'], 2); ?></td>
                                <td class="p-3"><?php echo ucfirst($user['package']); ?></td>
                                <td class="p-3">
                                    <?php if (($user['status'] ?? 'active') == 'active'): ?>
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">সক্রিয়</span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">ব্লক</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3"><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                <td class="p-3">
                                    <button onclick="editBalance('<?php echo $user['id']; ?>', <?php echo $user['balance']; ?>)" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">ব্যালেন্স</button>
                                    <button onclick="toggleStatus('<?php echo $user['id']; ?>')" class="bg-yellow-500 text-white px-2 py-1 rounded text-xs">স্ট্যাটাস</button>
                                    <button onclick="deleteUser('<?php echo $user['id']; ?>')" class="bg-red-500 text-white px-2 py-1 rounded text-xs">ডিলিট</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Balance Modal -->
    <div id="balanceModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-96">
            <h3 class="font-bold text-lg mb-4">ব্যালেন্স এডিট</h3>
            <form method="POST" action="">
                <input type="hidden" name="user_id" id="editUserId">
                <input type="hidden" name="action" value="update_balance">
                <div class="mb-4">
                    <label class="block text-sm mb-1">নতুন ব্যালেন্স</label>
                    <input type="number" name="balance" id="editBalance" class="w-full border rounded-lg px-3 py-2" step="0.01" required>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded-lg">বাতিল</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg">সংরক্ষণ</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function editBalance(userId, currentBalance) {
        document.getElementById('editUserId').value = userId;
        document.getElementById('editBalance').value = currentBalance;
        document.getElementById('balanceModal').classList.remove('hidden');
        document.getElementById('balanceModal').classList.add('flex');
    }
    
    function closeModal() {
        document.getElementById('balanceModal').classList.add('hidden');
        document.getElementById('balanceModal').classList.remove('flex');
    }
    
    function deleteUser(userId) {
        if (confirm('আপনি কি এই ইউজার ডিলিট করতে চান?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="user_id" value="${userId}">`;
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    function toggleStatus(userId) {
        if (confirm('ইউজারের স্ট্যাটাস পরিবর্তন করতে চান?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="toggle_status"><input type="hidden" name="user_id" value="${userId}">`;
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>