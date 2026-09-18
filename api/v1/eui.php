<?php
// রিসেলারের নিজস্ব ওয়েবসাইট বা প্রজেক্টের কোড
$api_message = "";
$packages = [];
$api_error_warning = "";

// রিসেলারের নিজস্ব সিক্রেট এপিআই টোকেন
$reseller_token = "f867c3f3805c7b809987a0ff61d297bdc2343e9f1a068b10";
$domain_url     = "https://topeupsite.xo.je"; // আপনার মূল সাইটের ডোমেইন

// ১. আপনার মূল সাইট থেকে লাইভ প্রোডাক্ট/প্যাকেজ ফেচ করা
$products_api_url = $domain_url . "/api/v1/products.php?api_token=" . urlencode($reseller_token);

$ch = curl_init($products_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$prod_response = curl_exec($ch);
$curl_error    = curl_error($ch);
curl_close($ch);

if (!$curl_error && $prod_response) {
    $prod_result = json_decode($prod_response, true);
    if ($prod_result && isset($prod_result['status']) && $prod_result['status'] === 'success') {
        $packages = $prod_result['data'];
        if (empty($packages)) {
            $api_error_warning = "⚠️ মূল সাইটে বর্তমানে কোনো স্টক বা প্যাকেজ যুক্ত করা হয়নি!";
        }
    } else {
        $api_error_warning = "⚠️ API Error: " . ($prod_result['message'] ?? 'প্যাকেজ লোড করা যায়নি!');
    }
} else {
    $api_error_warning = "⚠️ সার্ভার কানেকشن ফেইল করেছে: " . $curl_error;
}

// ২. কাস্টমার অর্ডার সাবমিট করলে তা আপনার মূল API-তে পাঠানো
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package_id   = $_POST['package_id'] ?? 0;
    $game_uid     = trim($_POST['game_uid'] ?? '');
    $customer_ref = "CUST_" . rand(10000, 99999);

    $order_api_url = $domain_url . "/api/v1/order.php";

    $post_data = json_encode([
        "api_token"    => $reseller_token, 
        "package_id"   => (int)$package_id,
        "game_uid"     => $game_uid,
        "customer_ref" => $customer_ref,
        "payment_mode" => "wallet", 
        "selling_price"=> 0         
    ]);

    $ch = curl_init($order_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $order_curl_error = curl_error($ch);
    curl_close($ch);

    if (!$order_curl_error && $response) {
        $result = json_decode($response, true);
        if (isset($result['status']) && $result['status'] === 'success') {
            $api_message = "<div class='bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-xl text-sm font-bold text-center mb-4'>✅ অর্ডার সফলভাবে সম্পন্ন হয়েছে!<br>অর্ডার আইডি: #" . $result['order_id'] . "<br>অবশিষ্ট এপিআই ব্যালেন্স: ৳" . $result['wallet_balance'] . "</div>";
        } else {
            $api_message = "<div class='bg-rose-500/10 border border-rose-500/30 text-rose-400 p-4 rounded-xl text-sm font-bold text-center mb-4'>❌ ত্রুটি: " . ($result['message'] ?? 'অর্ডার প্রসেস করা যায়নি!') . "</div>";
        }
    } else {
        $api_message = "<div class='bg-rose-500/10 border border-rose-500/30 text-rose-400 p-4 rounded-xl text-sm font-bold text-center mb-4'>❌ সার্ভার কানেকشن এরর: " . $order_curl_error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Top-Up Store - Order Now</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen py-10 px-4">

    <div class="max-w-md mx-auto bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        
        <div class="text-center mb-6">
            <h1 class="text-2xl font-black text-white">🎮 Instant Top-Up</h1>
            <p class="text-xs text-slate-400 mt-1">আপনার গেম আইডি দিয়ে দ্রুত ডায়মন্ড বা প্যাক নিন</p>
        </div>

        <?php echo $api_message; ?>

        <?php if (!empty($api_error_warning)): ?>
            <div class='bg-amber-500/10 border border-amber-500/30 text-amber-400 p-3 rounded-xl text-xs font-bold text-center mb-4'>
                <?php echo $api_error_warning; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-400 mb-1">Player ID (UID)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500"><i class="ri-user-game-line"></i></span>
                    <input type="text" name="game_uid" required placeholder="আপনার গেম ইউআইডি লিখুন" class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-10 pr-4 py-3 text-sm font-semibold outline-none focus:border-purple-500 text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 mb-1">Select Package</label>
                <select name="package_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm font-semibold outline-none focus:border-purple-500 text-white cursor-pointer">
                    <option value="">-- প্যাকেজ নির্বাচন করুন --</option>
                    <?php if (!empty($packages)): ?>
                        <?php foreach ($packages as $pkg): ?>
                            <option value="<?php echo $pkg['id']; ?>">
                                <?php echo htmlspecialchars($pkg['name']); ?> - ৳<?php echo $pkg['price']; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>স্টক খালি বা প্যাকেজ নেই</option>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-purple-600/25 mt-2 flex items-center justify-center gap-2">
                <i class="ri-shopping-cart-2-line"></i> Place Order Now
            </button>
        </form>

        <div class="mt-6 text-center text-[11px] text-slate-500 border-t border-slate-800/80 pt-4">
            Powered by Automated API Engine
        </div>

    </div>

</body>
</html>