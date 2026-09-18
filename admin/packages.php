<?php 
include 'header.php'; 

// ১. ডাটাবেস থেকে সব গেম/প্রোডাক্ট লোড করা (ড্রপডাউনের জন্য এবং ছবির জন্য)
$products = $pdo->query("SELECT id, name, code, category, image FROM products ORDER BY category ASC, name ASC")->fetchAll();

// ২. ডাটাবেসে থাকা সব প্যাকেজ এবং সেই প্যাকেজের গেমের ছবি (JOIN করে) লোড করা
$sql = "
    SELECT 
        pkg.*, 
        prd.image as product_image, 
        prd.name as product_name
    FROM packages pkg
    LEFT JOIN products prd ON pkg.category = prd.code
    ORDER BY pkg.id DESC
";
$packages = $pdo->query($sql)->fetchAll();

// ৩. প্যাকেজগুলোকে ক্যাটাগরি (গেম) অনুযায়ী গ্রুপ করা
$groupedPackages = [];
$totalPackages = 0;
$totalValue = 0;

foreach($packages as $p) {
    $totalPackages++;
    $totalValue += $p['price'];
    
    $catCode = $p['category'];
    if(!isset($groupedPackages[$catCode])) {
        $groupedPackages[$catCode] = [
            'product_name' => $p['product_name'] ?: $p['category'],
            'mainCategory' => $p['mainCategory'],
            'product_image' => $p['product_image'] ?: 'https://placehold.co/100x100?text=Game',
            'items' => []
        ];
    }
    // গেমের আন্ডারে প্যাকেজগুলো ঢোকানো হচ্ছে
    $groupedPackages[$catCode]['items'][] = $p;
}
$totalCategories = count($groupedPackages);
?>

<!-- 📊 টপ স্ট্যাটাস বার -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border-2 border-slate-200 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-bold shrink-0">
            <i class="ri-price-tag-3-fill"></i>
        </div>
        <div class="min-w-0">
            <p class="text-[10px] font-bold text-slate-500 uppercase truncate">Total Packages</p>
            <h3 class="text-lg font-black text-slate-800"><?php echo $totalPackages; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border-2 border-slate-200 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold shrink-0">
            <i class="ri-gamepad-fill"></i>
        </div>
        <div class="min-w-0">
            <p class="text-[10px] font-bold text-slate-500 uppercase truncate">Active Games</p>
            <h3 class="text-lg font-black text-slate-800"><?php echo $totalCategories; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl border-2 border-slate-200 flex items-center gap-3 col-span-2 md:col-span-2">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold shrink-0">
            <i class="ri-money-dollar-circle-fill"></i>
        </div>
        <div class="min-w-0">
            <p class="text-[10px] font-bold text-slate-500 uppercase truncate">Total Store Value</p>
            <h3 class="text-lg font-black text-slate-800">৳ <?php echo number_format($totalValue); ?></h3>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-12 gap-8 pb-12">
    
    <!-- 🟢 LEFT: ADD PACKAGE FORM (মোবাইলে আর আটকে থাকবে না) -->
    <!-- xl:sticky ব্যবহার করা হয়েছে, অর্থাৎ শুধু কম্পিউটারে আটকে থাকবে, মোবাইলে নরমাল স্ক্রল হবে -->
    <div class="xl:col-span-4 h-fit xl:sticky xl:top-24">
        <div class="bg-white p-6 rounded-xl border-2 border-slate-200">
            <div class="mb-5 border-b border-slate-100 pb-4">
                <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                    <i class="ri-add-box-line text-purple-600"></i> Create Package
                </h3>
                <p class="text-xs text-slate-500 mt-1">স্টোরে নতুন অফার বা প্যাক যোগ করুন</p>
            </div>

            <form id="pkgForm" class="space-y-4">
                
                <!-- গেম সিলেক্ট ও স্থির ছবি -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Select Game / App <span class="text-rose-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <!-- স্থির ছবি বক্স -->
                        <div class="w-12 h-12 rounded-lg bg-slate-100 border-2 border-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
                            <img id="gIconImg" src="https://placehold.co/100x100?text=Select" class="w-full h-full object-cover">
                        </div>
                        <select id="pSelect" onchange="updateFormIcon()" required class="w-full border-2 border-slate-200 focus:border-purple-600 bg-slate-50 px-4 py-3 rounded-lg outline-none text-sm font-semibold text-slate-800 cursor-pointer">
                            <option value="" data-cat="" data-img="https://placehold.co/100x100?text=Select">-- গেম সিলেক্ট করুন --</option>
                            <?php foreach($products as $prod): ?>
                                <option value="<?php echo $prod['code']; ?>" data-cat="<?php echo htmlspecialchars($prod['category']); ?>" data-img="<?php echo htmlspecialchars($prod['image']); ?>">
                                    <?php echo htmlspecialchars($prod['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- প্যাকেজের নাম -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Package Details <span class="text-rose-500">*</span></label>
                    <input type="text" id="pName" placeholder="প্যাকেজের নাম (যেমন: Weekly Membership)" required class="w-full border-2 border-slate-200 focus:border-purple-600 bg-slate-50 px-4 py-3 rounded-lg outline-none text-sm font-semibold text-slate-800">
                </div>

                <!-- দাম ও ট্যাগ -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Price (৳) <span class="text-rose-500">*</span></label>
                        <input type="number" id="pPrice" placeholder="0" min="1" required class="w-full border-2 border-slate-200 focus:border-purple-600 bg-slate-50 px-4 py-3 rounded-lg outline-none text-base font-black text-purple-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Badge (Tag)</label>
                        <input type="text" id="pTag" placeholder="e.g. 10% OFF" class="w-full border-2 border-slate-200 focus:border-purple-600 bg-slate-50 px-4 py-3 rounded-lg outline-none text-sm font-semibold text-slate-700">
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="w-full bg-slate-900 hover:bg-black text-white font-bold py-3.5 rounded-lg transition-colors mt-2">
                    Publish Package
                </button>
            </form>
        </div>
    </div>

    <!-- 🔵 RIGHT: CATEGORY BASED PACKAGE LIST -->
    <div class="xl:col-span-8">
        
        <!-- সার্চ বার -->
        <div class="bg-white p-3 rounded-xl border-2 border-slate-200 mb-6">
            <div class="relative w-full">
                <i class="ri-search-line absolute left-4 top-3 text-slate-400 text-lg"></i>
                <input type="text" id="pkgSearch" onkeyup="searchPackages()" placeholder="প্যাকেজ বা গেমের নাম দিয়ে সার্চ করুন..." class="w-full bg-slate-50 border-2 border-slate-200 rounded-lg pl-11 pr-4 py-2.5 text-sm font-semibold text-slate-800 outline-none focus:border-purple-600">
            </div>
        </div>

        <!-- 📦 গ্রুপ করা কার্ড গ্রিড (Grouped by Game) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="pkgGrid">
            <?php if(empty($groupedPackages)): ?>
                <div class="col-span-full py-16 text-center text-slate-400 bg-white rounded-xl border-2 border-slate-200 border-dashed">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="ri-price-tag-3-line text-3xl text-slate-300"></i>
                    </div>
                    <p class="font-bold text-slate-600 text-base">কোনো গেম বা প্যাকেজ নেই!</p>
                </div>
            <?php endif; ?>

            <?php foreach($groupedPackages as $catCode => $gameInfo): ?>
            <!-- 🎮 গেম কার্ড (এর ভেতরে প্যাকেজগুলো লিস্ট করা থাকবে) -->
            <div class="bg-white rounded-xl border-2 border-slate-200 flex flex-col category-card overflow-hidden">
                
                <!-- Card Header (Game Details) -->
                <div class="p-4 bg-slate-50 border-b-2 border-slate-200 flex items-center gap-4">
                    <img src="<?php echo htmlspecialchars($gameInfo['product_image']); ?>" class="w-12 h-12 rounded-lg bg-white border-2 border-slate-200 object-cover shrink-0">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase text-purple-600 tracking-wide truncate">
                            <?php echo htmlspecialchars($gameInfo['mainCategory']); ?>
                        </p>
                        <h4 class="text-base font-bold text-slate-800 truncate leading-tight">
                            <?php echo htmlspecialchars($gameInfo['product_name']); ?>
                        </h4>
                    </div>
                    <div class="bg-white px-2.5 py-1 rounded border-2 border-slate-200 text-xs font-bold text-slate-500 text-center shrink-0">
                        <span class="block text-slate-800 font-black text-sm"><?php echo count($gameInfo['items']); ?></span> Items
                    </div>
                </div>

                <!-- Card Body (List of Packages) -->
                <div class="flex-1 flex flex-col bg-white">
                    <ul class="divide-y-2 divide-slate-100">
                        <?php foreach($gameInfo['items'] as $p): ?>
                        <li class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                            <div class="min-w-0 pr-4">
                                <h5 class="text-sm font-bold text-slate-800 leading-tight">
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </h5>
                                <div class="flex items-center flex-wrap gap-2 mt-1.5">
                                    <span class="text-sm font-black text-purple-700 leading-none">৳ <?php echo $p['price']; ?></span>
                                    <?php if(!empty($p['tag'])): ?>
                                        <span class="px-1.5 py-0.5 rounded bg-rose-50 border border-rose-100 text-rose-600 text-[9px] font-bold uppercase leading-none">
                                            <?php echo htmlspecialchars($p['tag']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button onclick="copyPkg('<?php echo htmlspecialchars($p['name'].' - ৳'.$p['price']); ?>')" class="w-8 h-8 flex items-center justify-center rounded bg-slate-50 border-2 border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 transition-colors" title="Copy">
                                    <i class="ri-file-copy-line"></i>
                                </button>
                                <button onclick="delPkg(<?php echo $p['id']; ?>)" class="w-8 h-8 flex items-center justify-center rounded bg-slate-50 border-2 border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-colors" title="Delete">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    // 📸 গেম সিলেক্ট করলে স্থির বক্সে ছবি শো করবে
    function updateFormIcon() {
        const select = document.getElementById('pSelect');
        const iconImg = document.getElementById('gIconImg');
        
        if (select.selectedIndex >= 0) {
            const imgUrl = select.options[select.selectedIndex].getAttribute('data-img');
            iconImg.src = imgUrl || 'https://placehold.co/100x100?text=Select';
        }
    }

    // পেজ লোড হওয়ার সাথে সাথে প্রথম অপশনের ছবিটি সেট করা
    window.onload = function() {
        updateFormIcon();
    };

    // ১. প্যাকেজ সেভ করা
    document.getElementById('pkgForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const selectEl = document.getElementById('pSelect');
        const code = selectEl.value;
        const mainCat = selectEl.options[selectEl.selectedIndex].getAttribute('data-cat') || 'Games';
        const name = document.getElementById('pName').value.trim();
        const price = document.getElementById('pPrice').value.trim();
        const tag = document.getElementById('pTag').value.trim();

        if(!code) return Swal.fire('সতর্কতা', 'অনুগ্রহ করে একটি গেম নির্বাচন করুন!', 'warning');

        const btn = document.getElementById('submitBtn');
        btn.innerHTML = 'Processing...';
        btn.disabled = true;

        try {
            const res = await fetch('admin_api.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({
                    action: 'add_package',
                    category: code,
                    mainCategory: mainCat,
                    name: name,
                    price: parseInt(price),
                    tag: tag
                })
            });
            const d = await res.json();
            if(d.status === 'success') {
                Swal.fire({
                    icon: 'success', 
                    title: 'প্যাকেজ তৈরি হয়েছে!', 
                    timer: 1500, 
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                throw new Error(d.message || 'ব্যর্থ হয়েছে!');
            }
        } catch(err) {
            Swal.fire('Error', err.message, 'error');
            btn.innerHTML = 'Publish Package';
            btn.disabled = false;
        }
    });

    // ২. প্যাকেজ ডিলিট করা
    async function delPkg(id) {
        Swal.fire({
            title: 'প্যাকেজটি ডিলিট করবেন?',
            text: "এটি স্টোর থেকে মুছে যাবে!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'হ্যাঁ, ডিলিট করুন'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const res = await fetch('admin_api.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ action: 'delete_package', id })
                });
                const d = await res.json();
                if(d.status === 'success') {
                    location.reload();
                }
            }
        });
    }

    // ৩. ইনস্ট্যান্ট সার্চ (ক্যাটাগরি বা প্যাকেজ খুঁজুন)
    function searchPackages() {
        const input = document.getElementById("pkgSearch").value.toUpperCase();
        const cards = document.querySelectorAll(".category-card");
        
        cards.forEach(card => {
            const text = card.textContent || card.innerText;
            if (text.toUpperCase().indexOf(input) > -1) {
                card.style.display = "flex";
            } else {
                card.style.display = "none";
            }
        });
    }

    // ৪. কপি প্যাকেজ ডিটেইলস
    function copyPkg(text) {
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Copied!', showConfirmButton: false, timer: 1000 });
        });
    }
</script>

<?php include 'footer.php'; ?>