<?php include 'header.php'; ?>

<div class="pt-24 pb-12 min-h-screen px-4 bg-gray-50">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-gray-800">যোগাযোগ করুন</h1>
            <p class="text-gray-500 text-sm mt-1">যেকোনো সমস্যায় আমাদের সাথে যোগাযোগ করতে পারেন</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-200">
            <form id="msgForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" id="mName" required placeholder="আপনার নাম" class="border p-3 rounded-xl outline-none">
                    <input type="tel" id="mPhone" required placeholder="ফোন নাম্বার" class="border p-3 rounded-xl outline-none">
                </div>
                <input type="email" id="mEmail" required placeholder="ইমেইল অ্যাড্রেস" class="w-full border p-3 rounded-xl outline-none">
                <textarea id="mText" rows="4" required placeholder="আপনার বার্তা লিখুন..." class="w-full border p-3 rounded-xl outline-none"></textarea>
                <button type="submit" id="msgBtn" class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl shadow-lg">পাঠিয়ে দিন</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('msgForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('msgBtn');
        btn.innerText = 'পাঠানো হচ্ছে...'; btn.disabled = true;

        const res = await fetch('ajax.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                action: 'send_message',
                name: document.getElementById('mName').value,
                phone: document.getElementById('mPhone').value,
                email: document.getElementById('mEmail').value,
                message: document.getElementById('mText').value
            })
        });
        const d = await res.json();
        if(d.status === 'success') {
            Swal.fire('ধন্যবাদ!', d.message, 'success');
            document.getElementById('msgForm').reset();
        } else {
            Swal.fire('ত্রুটি!', 'মেসেজ পাঠানো সম্ভব হয়নি', 'error');
        }
        btn.innerText = 'পাঠিয়ে দিন'; btn.disabled = false;
    });
</script>

<?php include 'footer.php'; ?>