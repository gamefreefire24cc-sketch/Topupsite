<?php 
require_once 'db.php';
include 'header.php'; 

// ১. ব্যানার লোড (ডাটাবেস থেকে)
$banners = $pdo->query("SELECT * FROM banners ORDER BY id DESC")->fetchAll();

// ২. নোটিশ লোড (এডমিন সেটিংস থেকে)
$stmt = $pdo->prepare("SELECT setting_value FROM admin_settings WHERE setting_key = 'notice'");
$stmt->execute();
$noticeText = $stmt->fetchColumn() ?: "১৮ বছরের নিচে কেউ অর্ডার করবেন না! বাবা/মা বা পরিবারের টাকা চুরি করে অর্ডার করলে তার বিরুদ্ধে আইনগত ব্যবস্থা নেওয়া হবে!";

// ৩. ক্যাটাগরি ও প্রোডাক্ট লোড (ডাটাবেস থেকে)
$rawProducts = $pdo->query("SELECT * FROM products ORDER BY id ASC")->fetchAll();
$categories = [];
foreach ($rawProducts as $p) {
    $categories[$p['category']][] = $p;
}

// ৪. লাইভ আসল অর্ডার লোড (orders টেবিল থেকে শেষ ৮টি অর্ডার)
$recentOrders = $pdo->query("SELECT userName, product, package, price, status, timestamp FROM orders ORDER BY id DESC LIMIT 8")->fetchAll();
?>

<div class="pt-16 md:pt-20 pb-20 bg-slate-50 min-h-screen">

    <!-- 📢 আধুনিক নোটিশ বার -->
    <section class="bg-gradient-to-r from-purple-700 via-indigo-600 to-purple-800 py-2.5 px-4 shadow-md sticky top-14 md:top-16 z-20">
        <div class="container mx-auto max-w-6xl">
            <div class="flex items-center gap-2">
                <span class="bg-white/20 text-white text-[11px] font-black px-2.5 py-1 rounded-full uppercase shrink-0 flex items-center gap-1.5 backdrop-blur-sm shadow-sm">
                    <i class="ri-broadcast-fill text-yellow-300 animate-pulse"></i> Notice
                </span>
                <div class="overflow-hidden w-full">
                    <marquee class="text-white text-xs md:text-sm font-semibold tracking-wide" scrollamount="5" onmouseover="this.stop();" onmouseout="this.start();">
                        <?php echo htmlspecialchars($noticeText); ?>
                    </marquee>
                </div>
            </div>
        </div>
    </section>

    <!-- 🎮 ব্যানার স্লাইডার (Auto Play + Touch Friendly) -->
    <?php if(!empty($banners)): ?>
    <section class="container mx-auto max-w-6xl mt-4 md:mt-6 px-4">
        <div class="relative overflow-hidden rounded-3xl shadow-xl border border-purple-100/60 bg-gray-900 group">
            <div id="slider" class="flex transition-transform duration-700 ease-in-out w-full">
                <?php foreach($banners as $banner): ?>
                    <div class="w-full flex-shrink-0">
                        <a href="<?php echo htmlspecialchars($banner['link'] ?? '#'); ?>" target="_blank" class="block w-full">
                            <img src="<?php echo htmlspecialchars($banner['image']); ?>" 
                                 alt="Banner" 
                                 class="w-full h-44 sm:h-64 md:h-84 object-cover select-none"
                                 onerror="this.style.display='none'">
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Dots Indicator -->
            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5 z-10" id="sliderDots">
                <?php foreach($banners as $idx => $b): ?>
                    <button onclick="goToSlide(<?php echo $idx; ?>)" class="w-2.5 h-2.5 rounded-full transition-all duration-300 <?php echo $idx === 0 ? 'bg-purple-500 w-6' : 'bg-white/60'; ?>" id="dot_<?php echo $idx; ?>"></button>
                <?php endforeach; ?>
            </div>

            <!-- Arrow Navigation -->
            <button onclick="prevSlide()" class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/40 text-white flex items-center justify-center hover:bg-black/70 transition backdrop-blur-md shadow-lg">
                <i class="ri-arrow-left-s-line text-2xl"></i>
            </button>
            <button onclick="nextSlide()" class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/40 text-white flex items-center justify-center hover:bg-black/70 transition backdrop-blur-md shadow-lg">
                <i class="ri-arrow-right-s-line text-2xl"></i>
            </button>
        </div>
    </section>
    <?php endif; ?>

    <!-- 💎 প্রোডাক্ট ও গেম সেকশন (ক্যাটাগরি ভিত্তিক ডায়নামিক গ্রিড) -->
    <div class="container mx-auto max-w-6xl px-4 mt-6">
        <?php foreach($categories as $catTitle => $items): ?>
        <section class="my-8 md:my-10">
            <div class="flex items-center justify-between mb-5 border-b border-purple-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-7 bg-purple-600 rounded-full"></span>
                    <h2 class="text-lg md:text-2xl font-black text-gray-800 uppercase tracking-wide">
                        <?php echo htmlspecialchars($catTitle); ?>
                    </h2>
                </div>
                <span class="text-xs font-bold text-purple-600 bg-purple-50 px-2.5 py-1 rounded-full border border-purple-100">
                    <?php echo count($items); ?> Items
                </span>
            </div>

            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3 md:gap-5">
                <?php foreach($items as $prod): ?>
                    <a href="topup.php?id=<?php echo $prod['id']; ?>" class="group block bg-white rounded-2xl p-2.5 md:p-3 border border-gray-100 shadow-sm hover:shadow-xl hover:border-purple-200 transition-all duration-300 transform hover:-translate-y-1">
                        <div class="relative overflow-hidden rounded-xl bg-gray-100 aspect-square shadow-inner">
                            <img src="<?php echo htmlspecialchars($prod['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($prod['name']); ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 onerror="this.src='https://placehold.co/300x300?text=Game'">
                            <span class="absolute bottom-1.5 right-1.5 bg-black/60 backdrop-blur-sm text-white text-[9px] font-bold px-1.5 py-0.5 rounded">
                                <?php echo htmlspecialchars($prod['input_type']); ?>
                            </span>
                        </div>
                        <h3 class="text-[12px] md:text-sm text-center font-bold text-gray-800 mt-2.5 truncate group-hover:text-purple-600 transition-colors">
                            <?php echo htmlspecialchars($prod['name']); ?>
                        </h3>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endforeach; ?>
    </div>

    <!-- 🔥 সাম্প্রতিক আসল অর্ডারসমূহ (Real Orders From Database) -->
    <section class="container mx-auto max-w-6xl px-4 my-10">
        <div class="bg-white rounded-3xl shadow-sm border border-purple-100/70 p-5 md:p-8">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
                <div>
                    <h3 class="text-lg md:text-2xl font-black text-gray-800 flex items-center gap-2">
                        <i class="ri-fire-fill text-orange-500"></i> Recent Purchases
                    </h3>
                    <p class="text-gray-500 text-xs md:text-sm mt-0.5">আমাদের গ্রাহকদের সর্বশেষ লাইভ অর্ডারসমূহ</p>
                </div>
                <span class="px-3 py-1 bg-green-50 text-green-700 text-xs font-bold rounded-full border border-green-100 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-ping"></span> Live Updates
                </span>
            </div>

            <?php if(!empty($recentOrders)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php foreach($recentOrders as $order): ?>
                        <div class="bg-slate-50/80 rounded-2xl p-3.5 flex items-center justify-between border border-slate-100 hover:border-purple-200 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-md">
                                    <?php echo strtoupper(substr($order['userName'] ?: 'U', 0, 1)); ?>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-gray-800 text-xs md:text-sm truncate">
                                        <?php echo htmlspecialchars($order['userName'] ?: 'User'); ?>
                                    </h4>
                                    <p class="text-[11px] text-gray-500 truncate mt-0.5">
                                        <?php echo htmlspecialchars($order['product']); ?> • <span class="text-purple-600 font-semibold"><?php echo htmlspecialchars($order['package']); ?></span>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right shrink-0 ml-3">
                                <span class="font-black text-gray-900 text-sm md:text-base">৳<?php echo $order['price']; ?></span>
                                <span class="block text-[10px] font-bold uppercase mt-0.5 <?php echo $order['status'] === 'Completed' ? 'text-green-600' : ($order['status'] === 'Rejected' ? 'text-red-500' : 'text-orange-500'); ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-slate-400">
                    <i class="ri-shopping-bag-3-line text-4xl mb-2 opacity-50 block"></i>
                    <p class="text-sm font-medium">এখনও কোনো অর্ডার সম্পন্ন হয়নি। প্রথম অর্ডারটি আপনিই করুন!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 🛡️ ট্রাস্ট ও ফিচার সেকশন -->
    <section class="container mx-auto max-w-6xl px-4 my-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-gray-100 text-center shadow-sm">
                <i class="ri-flashlight-fill text-2xl text-yellow-500 mb-1 block"></i>
                <h4 class="font-bold text-gray-800 text-xs md:text-sm">Instant Delivery</h4>
                <p class="text-[10px] text-gray-400 mt-0.5">স্বয়ংক্রিয় দ্রুত ডেলিভারি</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-gray-100 text-center shadow-sm">
                <i class="ri-shield-check-fill text-2xl text-green-500 mb-1 block"></i>
                <h4 class="font-bold text-gray-800 text-xs md:text-sm">100% Secure</h4>
                <p class="text-[10px] text-gray-400 mt-0.5">নিরাপদ ট্রানজেকশন</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-gray-100 text-center shadow-sm">
                <i class="ri-customer-service-2-fill text-2xl text-purple-500 mb-1 block"></i>
                <h4 class="font-bold text-gray-800 text-xs md:text-sm">24/7 Support</h4>
                <p class="text-[10px] text-gray-400 mt-0.5">সার্বক্ষণিক কাস্টমার কেয়ার</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-gray-100 text-center shadow-sm">
                <i class="ri-wallet-3-fill text-2xl text-blue-500 mb-1 block"></i>
                <h4 class="font-bold text-gray-800 text-xs md:text-sm">Multiple Payments</h4>
                <p class="text-[10px] text-gray-400 mt-0.5">বিকাশ, নগদ ও রকেট</p>
            </div>
        </div>
    </section>

</div>

<!-- স্লাইডার জাভাস্ক্রিপ্ট -->
<script>
    let currentSlide = 0;
    const slider = document.getElementById('slider');
    const slides = slider ? slider.children : [];
    const total = slides.length;
    let autoSlideTimer;

    function updateSlider() {
        if(!slider || total === 0) return;
        slider.style.transform = `translateX(-${currentSlide * 100}%)`;
        for (let i = 0; i < total; i++) {
            const dot = document.getElementById(`dot_${i}`);
            if(dot) {
                dot.className = (i === currentSlide) 
                    ? "w-6 h-2.5 bg-purple-500 rounded-full transition-all duration-300" 
                    : "w-2.5 h-2.5 bg-white/60 rounded-full transition-all duration-300";
            }
        }
    }

    function nextSlide() {
        if(total <= 1) return;
        currentSlide = (currentSlide + 1) % total;
        updateSlider();
        resetTimer();
    }

    function prevSlide() {
        if(total <= 1) return;
        currentSlide = (currentSlide - 1 + total) % total;
        updateSlider();
        resetTimer();
    }

    function goToSlide(index) {
        currentSlide = index;
        updateSlider();
        resetTimer();
    }

    function resetTimer() {
        clearInterval(autoSlideTimer);
        if(total > 1) {
            autoSlideTimer = setInterval(nextSlide, 4000);
        }
    }

    if(total > 1) resetTimer();
</script>

<?php include 'footer.php'; ?>