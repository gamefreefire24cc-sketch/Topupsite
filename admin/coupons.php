<?php 
include 'header.php'; 
$coupons = $pdo->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900">Promo & Discount Coupons</h1>
        <p class="text-sm text-slate-500">গ্রাহকদের জন্য ডিসকাউন্ট কুপন তৈরি ও পরিচালনা করুন।</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- বামে: কুপন তৈরির ফর্ম -->
    <div class="admin-card p-6 h-fit lg:sticky lg:top-24">
        <h3 class="font-extrabold text-slate-800 text-base mb-4 flex items-center gap-2">
            <i class="ri-coupon-3-fill text-purple-600"></i> Create New Coupon
        </h3>
        
        <form id="couponForm" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Coupon Code <span class="text-rose-500">*</span></label>
                <input type="text" id="cCode" required placeholder="e.g. EID2026" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-purple-600 p-3 rounded-xl outline-none text-sm font-bold uppercase text-slate-800 transition">
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Discount Type</label>
                    <select id="cType" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-purple-600 p-3 rounded-xl outline-none text-xs font-semibold text-slate-800 transition">
                        <option value="percent">Percentage (%)</option>
                        <option value="flat">Flat Amount (৳)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Value <span class="text-rose-500">*</span></label>
                    <input type="number" id="cValue" required placeholder="e.g. 10 or 50" min="1" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-purple-600 p-3 rounded-xl outline-none text-sm font-black text-slate-800 transition">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Min Spend (৳)</label>
                    <input type="number" id="cMin" value="0" min="0" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-purple-600 p-3 rounded-xl outline-none text-sm font-semibold text-slate-800 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Usage Limit</label>
                    <input type="number" id="cLimit" value="100" min="1" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-purple-600 p-3 rounded-xl outline-none text-sm font-semibold text-slate-800 transition">
                </div>
            </div>

            <button type="submit" id="saveCouponBtn" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3.5 rounded-xl shadow-lg transition flex items-center justify-center gap-2 mt-2">
                <i class="ri-add-box-line text-lg"></i> Save Coupon
            </button>
        </form>
    </div>

    <!-- ডানে: কুপন লিস্ট -->
    <div class="lg:col-span-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php if(empty($coupons)): ?>
                <div class="col-span-2 py-10 text-center text-slate-400 bg-white rounded-2xl border-2 border-slate-100 border-dashed">
                    <i class="ri-coupon-line text-4xl opacity-50 block mb-1"></i>
                    <p class="text-sm font-semibold">কোনো কুপন তৈরি করা হয়নি।</p>
                </div>
            <?php endif; ?>

            <?php foreach($coupons as $c): ?>
                <div class="bg-white p-5 rounded-2xl border-2 border-slate-100 relative overflow-hidden flex flex-col justify-between hover:border-purple-200 transition">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-purple-50 rounded-full -z-0"></div>
                    
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-2">
                            <span class="px-3 py-1 bg-purple-100 text-purple-700 font-black font-mono text-xs rounded-lg uppercase tracking-wider border border-purple-200">
                                <?php echo htmlspecialchars($c['code']); ?>
                            </span>
                            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">
                                <?php echo $c['discount_type'] === 'percent' ? $c['discount_value'] . '% OFF' : '৳' . $c['discount_value'] . ' OFF'; ?>
                            </span>
                        </div>
                        
                        <div class="text-xs text-slate-500 space-y-1 mt-3">
                            <p>Min Spend: <span class="font-bold text-slate-700">৳<?php echo $c['min_spend']; ?></span></p>
                            <p>Used: <span class="font-bold text-slate-700"><?php echo $c['used_count']; ?> / <?php echo $c['usage_limit']; ?></span></p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 mt-4 border-t border-slate-100 relative z-10">
                        <button onclick="delCoupon(<?php echo $c['id']; ?>)" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition text-xs font-bold flex items-center gap-1">
                            <i class="ri-delete-bin-line"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    document.getElementById('couponForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('saveCouponBtn');
        btn.innerHTML = 'Saving...'; btn.disabled = true;

        const res = await fetch('admin_api.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                action: 'add_coupon',
                code: document.getElementById('cCode').value.trim(),
                type: document.getElementById('cType').value,
                value: document.getElementById('cValue').value,
                min: document.getElementById('cMin').value,
                limit: document.getElementById('cLimit').value
            })
        });
        const d = await res.json();
        if(d.status === 'success') {
            Swal.fire({icon: 'success', title: 'Coupon Created!', timer: 1200, showConfirmButton: false}).then(() => location.reload());
        } else {
            Swal.fire('Error', d.message, 'error');
            btn.innerHTML = '<i class="ri-add-box-line text-lg"></i> Save Coupon';
            btn.disabled = false;
        }
    });

    async function delCoupon(id) {
        if(confirm("Delete this coupon?")) {
            await fetch('admin_api.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ action: 'delete_coupon', id })
            });
            location.reload();
        }
    }
</script>

<?php include 'footer.php'; ?>