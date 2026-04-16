<?php
// admin/withdrawals.php - Withdrawal Management
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php?admin=eflix2024');
    exit();
}

$db = getDB();

// Handle withdrawal approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $withdraw_id = $_POST['withdraw_id'] ?? '';
    
    if ($action == 'approve' && $withdraw_id) {
        $stmt = $db->prepare("UPDATE withdrawals SET status = 'approved', approved_at = NOW() WHERE id = ?");
        $stmt->execute([$withdraw_id]);
        header('Location: withdrawals.php?admin=eflix2024');
        exit();
    }
    
    if ($action == 'reject' && $withdraw_id) {
        // Get withdrawal amount and user
        $stmt = $db->prepare("SELECT user_id, amount FROM withdrawals WHERE id = ?");
        $stmt->execute([$withdraw_id]);
        $wd = $stmt->fetch();
        
        if ($wd) {
            // Return balance to user
            $stmt = $db->prepare("UPDATE profiles SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$wd['amount'], $wd['user_id']]);
            
            // Update withdrawal status
            $stmt = $db->prepare("UPDATE withdrawals SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$withdraw_id]);
        }
        header('Location: withdrawals.php?admin=eflix2024');
        exit();
    }
}

// Get all withdrawals
$withdrawals = $db->query("
    SELECT w.*, p.full_name, p.email, p.phone 
    FROM withdrawals w 
    JOIN profiles p ON w.user_id = p.id 
    ORDER BY w.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>উইথড্র ম্যানেজ - Eflix Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-purple-800 to-indigo-900 text-white fixed h-full">
            <div class="p-4">
                <h1 class="text-xl font-bold">Eflix Admin</h1>
                <p class="text-xs text-white/60">উইথড্র ম্যানেজমেন্ট</p>
            </div>
            <nav class="mt-8">
                <a href="index.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">📊 ড্যাশবোর্ড</a>
                <a href="users.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">👥 ইউজার</a>
                <a href="packages.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💎 প্যাকেজ</a>
                <a href="tasks.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">📋 টাস্ক</a>
                <a href="withdrawals.php?admin=eflix2024" class="block py-2 px-4 bg-white/20">💸 উইথড্র</a>
                <a href="deposits.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">💰 ডিপোজিট</a>
                <a href="settings.php?admin=eflix2024" class="block py-2 px-4 hover:bg-white/10">⚙️ সেটিংস</a>
                <a href="../logout.php" class="block py-2 px-4 mt-8 text-red-300 hover:bg-red-800">🚪 লগআউট</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 ml-64 p-6">
            <h1 class="text-2xl font-bold mb-6">💸 উইথড্র ম্যানেজমেন্ট</h1>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="text-left p-3">ইউজার</th>
                                <th class="text-left p-3">পরিমাণ</th>
                                <th class="text-left p-3">পদ্ধতি</th>
                                <th class="text-left p-3">একাউন্ট</th>
                                <th class="text-left p-3">তারিখ</th>
                                <th class="text-left p-3">স্ট্যাটাস</th>
                                <th class="text-left p-3">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($withdrawals as $wd): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3">
                                    <?php echo htmlspecialchars($wd['full_name']); ?><br>
                                    <span class="text-xs text-gray-500"><?php echo htmlspecialchars($wd['email']); ?></span>
                                </td>
                                <td class="p-3 font-bold text-red-600">$<?php echo number_format($wd['amount'], 2); ?></td>
                                <td class="p-3"><?php echo $wd['method']; ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($wd['account_number']); ?></td>
                                <td class="p-3"><?php echo date('d M Y, h:i A', strtotime($wd['created_at'])); ?></td>
                                <td class="p-3">
                                    <?php if ($wd['status'] == 'pending'): ?>
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">পেন্ডিং</span>
                                    <?php elseif ($wd['status'] == 'approved'): ?>
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">অ্যাপ্রুভড</span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">রিজেক্টেড</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3">
                                    <?php if ($wd['status'] == 'pending'): ?>
                                    <form method="POST" action="" class="inline">
                                        <input type="hidden" name="withdraw_id" value="<?php echo $wd['id']; ?>">
                                        <button type="submit" name="action" value="approve" class="bg-green-500 text-white px-2 py-1 rounded text-xs">✅ অ্যাপ্রুভ</button>
                                        <button type="submit" name="action" value="reject" class="bg-red-500 text-white px-2 py-1 rounded text-xs">❌ রিজেক্ট</button>
                                    </form>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs"><?php echo $wd['status']; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>