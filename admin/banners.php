<?php 
include 'header.php'; 
$banners = $pdo->query("SELECT * FROM banners ORDER BY id DESC")->fetchAll();
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900">Banners Slider</h1>
        <p class="text-sm text-slate-500">Add or remove promotional banners shown on the home carousel.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- বামে: ব্যানার আপলোড ফর্ম -->
    <div class="admin-card p-6 h-fit">
        <h3 class="font-extrabold text-slate-800 text-base mb-1 flex items-center gap-2">
            <i class="ri-image-add-fill text-purple-600"></i> Upload New Banner
        </h3>
        <p class="text-xs text-slate-400 mb-5">Paste image link from PostImage or ImgBB.</p>

        <form id="bannerForm" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Image Direct URL <span class="text-rose-500">*</span></label>
                <input type="url" id="bImg" required placeholder="https://i.ibb.co/.../banner.jpg" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-purple-600 p-3 rounded-xl outline-none text-xs md:text-sm font-semibold text-slate-800 transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Target Action Link (Optional)</label>
                <input type="url" id="bLink" placeholder="https://t.me/channel" class="w-full bg-slate-50 border-2 border-slate-100 focus:border-purple-600 p-3 rounded-xl outline-none text-xs md:text-sm font-semibold text-slate-800 transition">
            </div>

            <!-- লাইভ প্রিভিউ -->
            <div id="previewBox" class="hidden">
                <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Live Preview</p>
                <img id="prevImg" src="" class="w-full h-32 object-cover rounded-xl border border-slate-200">
            </div>

            <button type="submit" id="saveBtn" class="w-full bg-slate-900 hover:bg-black text-white font-bold py-3.5 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                <i class="ri-upload-cloud-2-line text-lg"></i> Save Banner
            </button>
        </form>
    </div>

    <!-- ডানে: একটিভ ব্যানার কার্ডস -->
    <div class="lg:col-span-2 admin-card p-6">
        <h3 class="font-extrabold text-slate-800 text-base mb-4 flex items-center justify-between">
            <span>Active Banners</span>
            <span class="text-xs font-bold text-purple-600 bg-purple-50 px-2.5 py-1 rounded-full border border-purple-100"><?php echo count($banners); ?> Active</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php if(empty($banners)): ?>
                <div class="col-span-2 py-10 text-center text-slate-400">
                    <i class="ri-image-line text-4xl opacity-50 block mb-1"></i>
                    <p class="text-xs">No banners uploaded yet. Add one from the left form.</p>
                </div>
            <?php endif; ?>

            <?php foreach($banners as $b): ?>
                <div class="rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm bg-white group hover:border-purple-300 transition">
                    <div class="relative w-full h-36 bg-slate-100 overflow-hidden">
                        <img src="<?php echo htmlspecialchars($b['image']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.src='https://placehold.co/600x250?text=Invalid+Image+URL'">
                    </div>
                    <div class="p-3 flex justify-between items-center bg-white border-t border-slate-100">
                        <span class="text-xs text-slate-400 truncate max-w-[180px] font-mono">
                            <?php echo htmlspecialchars($b['link'] ?: 'No Redirect Link'); ?>
                        </span>
                        <button onclick="delBanner(<?php echo $b['id']; ?>)" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition flex items-center justify-center" title="Delete Banner">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    // লাইভ প্রিভিউ লজিক
    document.getElementById('bImg').addEventListener('input', (e) => {
        const val = e.target.value.trim();
        const box = document.getElementById('previewBox');
        if(val) {
            box.classList.remove('hidden');
            document.getElementById('prevImg').src = val;
        } else {
            box.classList.add('hidden');
        }
    });

    // ব্যানার আপলোড
    document.getElementById('bannerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i> Saving...';
        btn.disabled = true;

        const res = await fetch('admin_api.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                action: 'add_banner',
                image: document.getElementById('bImg').value.trim(),
                link: document.getElementById('bLink').value.trim()
            })
        });
        const d = await res.json();
        if(d.status === 'success') {
            Swal.fire({icon: 'success', title: 'Banner Uploaded!', timer: 1500, showConfirmButton: false}).then(() => location.reload());
        } else {
            Swal.fire('Error', d.message || 'Failed', 'error');
            btn.innerHTML = '<i class="ri-upload-cloud-2-line text-lg"></i> Save Banner';
            btn.disabled = false;
        }
    });

    // ব্যানার ডিলিট
    async function delBanner(id) {
        Swal.fire({
            title: 'Delete this banner?',
            text: "It will be immediately removed from home carousel.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Yes, Delete'
        }).then(async (res) => {
            if(res.isConfirmed) {
                await fetch('admin_api.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ action: 'delete_banner', id })
                });
                location.reload();
            }
        });
    }
</script>

<?php include 'footer.php'; ?>