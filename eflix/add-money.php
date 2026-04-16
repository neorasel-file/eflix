<?php
// add-money.php - Add Money / Deposit
require_once 'config/auth.php';
require_once 'config/functions.php';

requireLogin();
$user = getCurrentUser();

$db = getDB();

$message = null;
$error = null;

// Get packages for deposit
$packages = $db->query("SELECT * FROM packages WHERE is_active = true AND price > 0 ORDER BY price")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount'] ?? 0);
    $method = $_POST['method'] ?? '';
    $transaction_id = trim($_POST['transaction_id'] ?? '');
    
    if ($amount <= 0) {
        $error = "বৈধ পরিমাণ দিন";
    } elseif (empty($method)) {
        $error = "পেমেন্ট মেথড সিলেক্ট করুন";
    } elseif (empty($transaction_id)) {
        $error = "ট্রানজেকশন আইডি দিন";
    } else {
        // Create deposit request
        $bonus = 0;
        
        // First deposit bonus 50%
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM deposits WHERE user_id = ? AND status = 'approved'");
        $stmt->execute([$user['id']]);
        $depositCount = $stmt->fetch()['count'];
        
        if ($depositCount == 0) {
            $bonus = $amount * 0.5;
        }
        
        $stmt = $db->prepare("INSERT INTO deposits (user_id, amount, method, transaction_id, bonus_added, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        if ($stmt->execute([$user['id'], $amount, $method, $transaction_id, $bonus])) {
            $message = "ডিপোজিট অনুরোধ করা হয়েছে! প্রশাসন অনুমোদন দিলে আপনার ব্যালেন্স যোগ হবে।";
            header('refresh:2;url=dashboard.php');
        } else {
            $error = "ডিপোজিট ব্যর্থ হয়েছে। আবার চেষ্টা করুন।";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাড মানি - Eflix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
    <script>
        function updateAmount() {
            let select = document.getElementById('packageSelect');
            let customDiv = document.getElementById('customAmountDiv');
            if (select.value === 'custom') {
                customDiv.style.display = 'block';
            } else {
                customDiv.style.display = 'none';
                document.getElementById('amount').value = select.value;
            }
        }
    </script>
</head>
<body class="bg-gray-100">

    <div class="container mx-auto px-4 max-w-md mx-auto min-h-screen flex flex-col justify-center py-8">
        
        <a href="dashboard.php" class="text-gray-600 mb-4 inline-block">
            <i class="ri-arrow-left-line text-2xl"></i>
        </a>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="text-center mb-6">
                <i class="ri-add-circle-line text-5xl text-purple-600"></i>
                <h1 class="text-2xl font-bold mt-2">অ্যাড মানি</h1>
                <p class="text-gray-500">আপনার ওয়ালেট রিচার্জ করুন</p>
            </div>

            <?php if ($message): ?>
                <div class="bg-green-500 text-white rounded-lg p-3 mb-4"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-500 text-white rounded-lg p-3 mb-4"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="bg-yellow-50 rounded-lg p-3 mb-4">
                <p class="text-sm text-center text-yellow-800">
                    💡 প্রথম ডিপোজিটে ৫০% বোনাস!
                </p>
            </div>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">প্যাকেজ সিলেক্ট করুন</label>
                    <select id="packageSelect" name="package" class="w-full border rounded-lg px-3 py-2" onchange="updateAmount()">
                        <option value="custom">কাস্টম এমাউন্ট</option>
                        <?php foreach ($packages as $pkg): ?>
                        <option value="<?php echo $pkg['price']; ?>"><?php echo htmlspecialchars($pkg['name']); ?> - $<?php echo $pkg['price']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="customAmountDiv" class="mb-4">
                    <label class="block text-sm font-medium mb-1">পরিমাণ ($)</label>
                    <input type="number" id="amount" name="amount" class="w-full border rounded-lg px-3 py-2" placeholder="পরিমাণ লিখুন" step="1" min="1">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">পেমেন্ট মেথড</label>
                    <select name="method" class="w-full border rounded-lg px-3 py-2" required>
                        <option value="">সিলেক্ট করুন</option>
                        <option value="bKash">bKash</option>
                        <option value="Nagad">Nagad</option>
                        <option value="USDT">USDT (TRC20)</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-1">ট্রানজেকশন আইডি</label>
                    <input type="text" name="transaction_id" class="w-full border rounded-lg px-3 py-2" placeholder="bKash/Nagad ট্রানজেকশন আইডি" required>
                </div>

                <div class="bg-purple-50 rounded-lg p-3 mb-4">
                    <p class="text-sm text-center text-purple-800">
                        📞 bKash/Nagad: <strong>017XXXXXXXX</strong><br>
                        (পেমেন্ট করার পর ট্রানজেকশন আইডি দিন)
                    </p>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-indigo-700 text-white py-3 rounded-lg font-semibold">
                    পেমেন্ট করুন →
                </button>
            </form>
        </div>
    </div>

    <script>
        // Set initial state
        document.getElementById('customAmountDiv').style.display = 'block';
    </script>
</body>
</html>