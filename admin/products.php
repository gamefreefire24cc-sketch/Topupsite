<?php 
include 'header.php'; 
$products = $pdo->query("SELECT * FROM products ORDER BY category ASC, id DESC")->fetchAll();
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900">Games & Categories</h1>
        <p class="text-sm text-slate-500">হোমপেজে শো করা গেম বা ক্যাটাগরিগুলো এখান থেকে যোগ বা ডিলিট করুন।</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- বামে: ফর্ম -->
    <div class="admin-card p-6 h-fit lg:sticky lg:top-24">
        <h3 class="font-extrabold text-slate-800 text-base mb-4 flex items-center gap-2">
            <i class="ri-gamepad-fill text-purple-600"></i> Add New Game
        </h3>
        
        <form id="productForm" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Game/App Name <span class="text-rose-500">*</span></label>
                <input type="text" id="pName" required placeholder="e.g. Free Fire TopUp (BD)" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-purple-600 p-3 rounded-xl outline-none text-sm font-semibold text-slate-800 transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Main Category <span class="text-rose-500">*</span></label>
                <input type="text" id="pCategory" required placeholder="e.g. FREE FIRE or SUBSCRIPTION" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-purple-600 p-3 rounded-xl outline-none text-sm font-semibold text-slate-800 transition uppercase">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Unique Code <span class="text-rose-500">*</span></label>
                <input type="text" id="pCode" required placeholder="e.g. ff_diamond" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-purple-600 p-3 rounded-xl outline-none text-sm font-semibold text-slate-800 transition lowercase">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Image URL <span class="text-rose-500">*</span></label>
                <input type="url" id="pImage" required placeholder="https://i.ibb.co/..." class="w-full bg-slate-50 border-2 border-slate-100 focus:border-purple-600 p-3 rounded-xl outline-none text-sm font-semibold text-slate-800 transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Input Type Required <span class="text-rose-500">*</span></label>
                <select id="pInputType" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-purple-600 p-3 rounded-xl outline-none text-sm font-semibold text-slate-800 transition">
                    <option value="UID">Player ID (UID)</option>
                    <option value="LOGIN">Email/Password (LOGIN)</option>
                </select>
            </div>

            <button type="submit" id="saveBtn" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3.5 rounded-xl shadow-lg transition flex items-center justify-center gap-2 mt-2">
                <i class="ri-add-box-line text-lg"></i> Save Game
            </button>
        </form>
    </div>

    <!-- ডানে: প্রোডাক্ট লিস্ট -->
    <div class="lg:col-span-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php if(empty($products)): ?>
                <div class="col-span-2 py-10 text-center text-slate-400 bg-white rounded-2xl border-2 border-slate-100 border-dashed">
                    <i class="ri-gamepad-line text-4xl opacity-50 block mb-1"></i>
                    <p class="text-sm font-semibold">কোনো গেম যুক্ত করা নেই।</p>
                </div>
            <?php endif; ?>

            <?php foreach($products as $p): ?>
                <div class="bg-white p-4 rounded-2xl border-2 border-slate-100 flex items-center gap-4 hover:border-purple-200 transition">
                    <img src="<?php echo htmlspecialchars($p['image']); ?>" class="w-14 h-14 rounded-xl object-cover bg-slate-50 border border-slate-200 shrink-0">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-black uppercase text-purple-600 truncate"><?php echo htmlspecialchars($p['category']); ?></p>
                        <h4 class="text-sm font-bold text-slate-800 truncate"><?php echo htmlspecialchars($p['name']); ?></h4>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-mono font-bold"><?php echo htmlspecialchars($p['code']); ?></span>
                            <span class="text-[9px] bg-emerald-50 text-emerald-600 border border-emerald-100 px-1.5 py-0.5 rounded font-black"><?php echo $p['input_type']; ?></span>
                        </div>
                    </div>
                    <button onclick="delProduct(<?php echo $p['id']; ?>)" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition flex items-center justify-center shrink-0">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    document.getElementById('productForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i> Saving...';
        btn.disabled = true;

        const payload = {
            action: 'add_product',
            name: document.getElementById('pName').value,
            category: document.getElementById('pCategory').value,
            code: document.getElementById('pCode').value,
            image: document.getElementById('pImage').value,
            input_type: document.getElementById('pInputType').value
        };

        const res = await fetch('admin_api.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        });
        const d = await res.json();
        
        if(d.status === 'success') {
            Swal.fire({icon: 'success', title: 'Success!', timer: 1500, showConfirmButton: false}).then(() => location.reload());
        } else {
            Swal.fire('Error', d.message, 'error');
            btn.innerHTML = '<i class="ri-add-box-line text-lg"></i> Save Game';
            btn.disabled = false;
        }
    });

    async function delProduct(id) {
        Swal.fire({
            title: 'Delete this game?',
            text: "এই গেমটি ডিলিট করলে হোমপেজ থেকে কার্ডটি মুছে যাবে!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Yes, Delete'
        }).then(async (res) => {
            if(res.isConfirmed) {
                await fetch('admin_api.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ action: 'delete_product', id })
                });
                location.reload();
            }
        });
    }
</script>

<?php include 'footer.php'; ?>