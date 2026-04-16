<?php
// withdraw.php - Withdraw Money
require_once 'config/auth.php';
require_once 'config/functions.php';

requireLogin();
$user = getCurrentUser();

$db = getDB();

$minWithdraw = $_ENV['MIN_WITHDRAW'] ?? 10;
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount'] ?? 0);
    $method = $_POST['method'] ?? '';
    $account_number = trim($_POST['account_number'] ?? '');
    
    if ($amount < $minWithdraw) {
        $error = "ন্যূনতম উইথড্র $ $minWithdraw";
    } elseif ($amount > $user['balance']) {
        $error = "অপর্যাপ্ত ব্যালেন্স! আপনার ব্যালেন্স: $" . number_format($user['balance'], 2);
    } elseif (empty($method) || empty($account_number)) {
        $error = "সব তথ্য পূরণ করুন";
    } else {
        // Create withdrawal request
        $stmt = $db->prepare("INSERT INTO withdrawals (user_id, amount, method, account_number, status) VALUES (?, ?, ?, ?, 'pending')");
        if ($stmt->execute([$user['id'], $amount, $method, $account_number])) {
            // Deduct balance
            updateUserBalance($user['id'], $amount, 'subtract');
            
            // Add transaction
            addTransaction($user['id'], 'withdraw', $amount, 'উইথড্র অনুরোধ', 'pending');
            
            $message = "উইথড্র অনুরোধ করা হয়েছে! প্রশাসন অনুমোদন দিলে আপনার একাউন্টে টাকা চলে যাবে।";
            header('refresh:2;url=dashboard.php');
        } else {
            $error = "উইথড্র ব্যর্থ হয়েছে। আবার চেষ্টা করুন।";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>উইথড্র - Eflix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100">

    <div class="container mx-auto px-4 max-w-md mx-auto min-h-screen flex flex-col justify-center py-8">
        
        <a href="dashboard.php" class="text-gray-600 mb-4 inline-block">
            <i class="ri-arrow-left-line text-2xl"></i>
        </a>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="text-center mb-6">
                <i class="ri-money-dollar-circle-line text-5xl text-purple-600"></i>
                <h1 class="text-2xl font-bold mt-2">উইথড্র মানি</h1>
                <p class="text-gray-500">আপনার আয় তুলে নিন</p>
            </div>

            <div class="bg-gray-50 rounded-lg p-3 mb-4 text-center">
                <p class="text-sm text-gray-500">উপলব্ধ ব্যালেন্স</p>
                <p class="text-2xl font-bold text-purple-600">$<?php echo number_format($user['balance'], 2); ?></p>
                <p class="text-xs text-gray-400">মিনিমাম উইথড্র: $<?php echo $minWithdraw; ?></p>
            </div>

            <?php if ($message): ?>
                <div class="bg-green-500 text-white rounded-lg p-3 mb-4"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-500 text-white rounded-lg p-3 mb-4"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">পরিমাণ ($)</label>
                    <input type="number" name="amount" class="w-full border rounded-lg px-3 py-2" placeholder="পরিমাণ লিখুন" min="<?php echo $minWithdraw; ?>" step="0.01" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">উইথড্র পদ্ধতি</label>
                    <select name="method" class="w-full border rounded-lg px-3 py-2" required>
                        <option value="">সিলেক্ট করুন</option>
                        <option value="bKash">bKash</option>
                        <option value="Nagad">Nagad</option>
                        <option value="USDT">USDT (TRC20)</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-1">একাউন্ট নম্বর</label>
                    <input type="text" name="account_number" class="w-full border rounded-lg px-3 py-2" placeholder="একাউন্ট নম্বর দিন" required>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-indigo-700 text-white py-3 rounded-lg font-semibold">
                    কনফার্ম উইথড্র
                </button>
            </form>
        </div>
    </div>

</body>
</html>