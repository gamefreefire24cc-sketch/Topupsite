<?php 
require_once 'db.php'; 
if(isset($_SESSION['user_uid'])) { header("Location: profile.php"); exit(); }
include 'header.php'; 
?>
<div class="pt-32 pb-12 flex items-center justify-center px-4 bg-gray-50 min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full border border-purple-100">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Create Account</h2>
        </div>
        <form id="registerForm" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Full Name</label>
                <input type="text" id="regName" required class="w-full px-4 py-3 rounded-lg border border-gray-300 outline-none focus:border-purple-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Phone</label>
                <input type="tel" id="regPhone" required class="w-full px-4 py-3 rounded-lg border border-gray-300 outline-none focus:border-purple-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Email</label>
                <input type="email" id="regEmail" required class="w-full px-4 py-3 rounded-lg border border-gray-300 outline-none focus:border-purple-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Password</label>
                <input type="password" id="regPassword" required minlength="6" class="w-full px-4 py-3 rounded-lg border border-gray-300 outline-none focus:border-purple-500">
            </div>
            <button type="submit" id="regBtn" class="w-full bg-purple-600 text-white py-3.5 rounded-lg font-bold shadow-lg hover:bg-purple-700 transition">Sign Up</button>
        </form>
        <div class="mt-6 text-center text-sm text-gray-600">Already have an account? <a href="login.php" class="text-purple-600 font-bold">Login here</a></div>
    </div>
</div>
<script>
    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('regBtn');
        btn.innerText = "Creating..."; btn.disabled = true;

        const data = {
            action: 'register',
            name: document.getElementById('regName').value,
            phone: document.getElementById('regPhone').value,
            email: document.getElementById('regEmail').value,
            password: document.getElementById('regPassword').value
        };

        try {
            const res = await fetch('ajax.php', { 
                method: 'POST', 
                headers: {'Content-Type': 'application/json'}, 
                body: JSON.stringify(data) 
            });
            const result = await res.json();
            
            if(result.status === 'success') {
                Swal.fire('Success', result.message, 'success').then(() => window.location.href = 'profile.php');
            } else {
                Swal.fire('Error', result.message, 'error');
                btn.innerText = "Sign Up"; btn.disabled = false;
            }
        } catch(err) {
            Swal.fire('Error', 'নেটওয়ার্ক ইরর হয়েছে!', 'error');
            btn.innerText = "Sign Up"; btn.disabled = false;
        }
    });
</script>
<?php include 'footer.php'; ?>