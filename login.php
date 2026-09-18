<?php 
require_once 'db.php'; 
if(isset($_SESSION['user_uid'])) {
    header("Location: " . ($_SESSION['user_role'] === 'admin' ? 'admin/dashboard.php' : 'profile.php'));
    exit();
}
include 'header.php'; 
?>
<div class="min-h-screen pt-24 pb-12 flex items-center justify-center px-4 bg-gray-50">
    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full border border-purple-100">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-black text-gray-800">লগইন করুন</h1>
            <p class="text-sm text-gray-500 mt-1">আপনার অফার টপ-আপ অ্যাকাউন্টে প্রবেশ করুন</p>
        </div>
        <form id="loginForm" class="space-y-5">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                <input type="email" id="loginEmail" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-purple-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                <input type="password" id="loginPassword" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-purple-500 outline-none">
            </div>
            <button type="submit" id="loginBtn" class="w-full bg-purple-600 text-white py-3.5 rounded-lg font-bold hover:bg-purple-700 transition-all shadow-lg">Login</button>
        </form>
        <div class="mt-6 text-center text-sm text-gray-600">
            অ্যাকাউন্ট নেই? <a href="register.php" class="text-purple-600 font-bold hover:underline">রেজিস্টার করুন</a>
        </div>
    </div>
</div>
<script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('loginBtn');
        btn.innerText = 'Checking...'; btn.disabled = true;

        try {
            const res = await fetch('ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'login',
                    email: document.getElementById('loginEmail').value,
                    password: document.getElementById('loginPassword').value
                })
            });
            const data = await res.json();
            if (data.status === 'success') {
                window.location.href = data.role === 'admin' ? 'admin/dashboard.php' : 'profile.php';
            } else {
                Swal.fire('Error', data.message, 'error');
                btn.innerText = 'Login'; btn.disabled = false;
            }
        } catch(err) {
            Swal.fire('Error', 'নেটওয়ার্ক ইরর হয়েছে!', 'error');
            btn.innerText = 'Login'; btn.disabled = false;
        }
    });
</script>
<?php include 'footer.php'; ?>