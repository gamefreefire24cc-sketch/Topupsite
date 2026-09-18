<?php
$main_site_url = "https://topeupsite.xo.je"; 
$api_token = "f867c3f3805c7b809987a0ff61d297bdc2343e9f1a068b10";

$response_message = "";
$response_type = "";

// ১. ফর্ম সাবমিট হলে cURL দিয়ে অর্ডার পাঠানো
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package_id = $_POST['package_id'] ?? '';
    $game_uid = trim($_POST['game_uid'] ?? '');
    $customer_ref = "REF-" . rand(10000, 99999);

    if (empty($package_id) || empty($game_uid)) {
        $response_message = "সবগুলো ফিল্ড পূরণ করুন!";
        $response_type = "error";
    } else {
        $order_url = $main_site_url . "/api/v1/order.php";
        $post_data = json_encode([
            "api_token"    => $api_token,
            "package_id"   => (int)$package_id,
            "game_uid"     => $game_uid,
            "customer_ref" => $customer_ref,
            "payment_mode" => "wallet"
        ]);

        $ch = curl_init($order_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // কুকি হ্যান্ডেল করার জন্য যাতে JS Challenge লুপে না পড়ে
        curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_token,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
        ]);
        $order_response = curl_exec($ch);
        curl_close($ch);

        $res_data = json_decode($order_response, true);

        if ($res_data && isset($res_data['status']) && $res_data['status'] === 'success') {
            $response_message = "অর্ডার সফল হয়েছে! Order ID: " . $res_data['order_id'] . " | নতুন ওয়ালেট ব্যালেন্স: ৳" . $res_data['wallet_balance'];
            $response_type = "success";
        } else {
            $response_message = "অর্ডার ব্যর্থ হয়েছে: " . ($res_data['message'] ?? 'সার্ভার রেসপন্স দেয়নি। রেসপন্স: ' . $order_response);
            $response_type = "error";
        }
    }
}

// ২. cURL দিয়ে মূল সাইট থেকে প্যাকেজ লিস্ট ফেচ করা (কুকি ও ইউজার এজেন্ট সহ)
$packages = [];
$api_error_debug = "";
$products_url = $main_site_url . "/api/v1/products.php?api_token=" . $api_token . "&i=1";

$ch = curl_init($products_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
$prod_response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    $api_error_debug = "cURL Error: " . $curl_error;
} else {
    $prod_data = json_decode($prod_response, true);
    if ($prod_data && isset($prod_data['status']) && $prod_data['status'] === 'success') {
        $packages = $prod_data['data'] ?? [];
    } else {
        $api_error_debug = "API Error: সার্ভার থেকে সঠিক JSON আসেনি। রেসপন্স ছিল: " . htmlspecialchars($prod_response);
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Auto TopUp Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-6 border border-slate-200">
        <h2 class="text-xl font-black text-slate-800 mb-2 text-center">API TopUp Form</h2>
        <p class="text-xs text-slate-500 text-center mb-6">রিসেলার প্যানেল থেকে সরাসরি অটো অর্ডার করুন</p>

        <?php if (!empty($api_error_debug)): ?>
            <div class="p-3 mb-4 rounded-xl text-xs font-bold text-center bg-amber-50 text-amber-700 border border-amber-200 break-words">
                ⚠️ <?php echo $api_error_debug; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($response_message)): ?>
            <div class="p-3 mb-4 rounded-xl text-xs font-bold text-center <?php echo $response_type === 'success' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-rose-50 text-rose-600 border border-rose-200'; ?>">
                <?php echo $response_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Select Package</label>
                <select name="package_id" required class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm outline-none focus:border-purple-600 font-semibold bg-slate-50">
                    <option value="">-- প্যাকেজ বেছে নিন --</option>
                    <?php if (!empty($packages)): ?>
                        <?php foreach ($packages as $pkg): ?>
                            <option value="<?php echo $pkg['id']; ?>">
                                <?php echo htmlspecialchars(($pkg['mainCategory'] ?? 'Package') . ' - ' . $pkg['name'] . ' (৳' . $pkg['price'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>কোনো প্যাকেজ পাওয়া যায়নি</option>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Player UID / Game ID</label>
                <input type="text" name="game_uid" required placeholder="উদাহরণ: 7093351051" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm outline-none focus:border-purple-600 font-bold bg-slate-50">
            </div>

            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3.5 rounded-xl shadow-lg transition text-sm">
                Submit Order Now
            </button>
        </form>
    </div>

</body>
</html>