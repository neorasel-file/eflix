<?php
// error.php - Custom Error Page
$errorCode = $_GET['code'] ?? 404;
$errorMessages = [
    400 => 'Bad Request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Page Not Found',
    500 => 'Internal Server Error',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable'
];
$errorMessage = $errorMessages[$errorCode] ?? 'Unknown Error';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?php echo $errorCode; ?> - Eflix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body class="bg-gradient-to-br from-purple-600 to-indigo-700 min-h-screen flex items-center justify-center">

    <div class="text-center text-white">
        <div class="text-9xl font-bold mb-4"><?php echo $errorCode; ?></div>
        <div class="text-2xl mb-2"><?php echo $errorMessage; ?></div>
        <p class="text-white/70 mb-6">দুঃখিত, পৃষ্ঠাটি পাওয়া যায়নি</p>
        <a href="index.php" class="inline-block bg-yellow-500 text-black px-6 py-3 rounded-full font-semibold">
            <i class="ri-home-line"></i> হোম পেজে ফিরুন
        </a>
    </div>

</body>
</html>