<?php 
require_once 'db.php';
if(!isset($_SESSION['user_uid'])) { header("Location: login.php"); exit(); }
include 'header.php'; 
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f3f4f6; }
    .wallet-gradient {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        position: relative; overflow: hidden;
    }
</style>

<div class="min-h-screen pt-24 pb-12 px-4 sm:px-6">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4 w-full md:w-auto">
                <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-purple-500 to-indigo-500 flex items-center justify-center text-2xl font-bold text-white shadow-lg" id="avatarText">U</div>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-800" id="displayName">Loading...</h1>
                    <p class="text-sm text-gray-500" id="displayEmail">...</p>
                </div>
            </div>

            <div class="flex gap-3 w-full md:w-auto">
                <button onclick="document.getElementById('editModal').classList.remove('hidden')" class="flex-1 md:flex-none px-4 py-2 bg-gray-50 text-gray-700 font-semibold rounded-xl hover:bg-gray-100 border border-gray-200 transition flex items-center justify-center gap-2">
                    <i class="ri-edit-line"></i> Edit
                </button>
                <button onclick="logout()" class="flex-1 md:flex-none px-4 py-2 bg-red-50 text-red-600 font-semibold rounded-xl hover:bg-red-100 border border-red-100 transition flex items-center justify-center gap-2">
                    <i class="ri-logout-box-r-line"></i> Logout
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 space-y-6">
                <div class="wallet-gradient rounded-2xl p-6 text-white shadow-xl">
                    <p class="text-indigo-100 text-xs font-bold uppercase tracking-wider">Available Balance</p>
                    <h2 class="text-4xl font-bold mt-1">৳ <span id="displayBalance">0.00</span></h2>
                    <div class="grid grid-cols-2 gap-3 mt-6">
                        <a href="add-money.php" class="bg-white text-indigo-700 py-2.5 rounded-xl font-bold text-sm text-center shadow-sm flex items-center justify-center gap-1">
                            <i class="ri-add-circle-fill"></i> Add Money
                        </a>
                        <a href="history.php" class="bg-indigo-600/50 text-white py-2.5 rounded-xl font-bold text-sm text-center border border-white/20">History</a>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 h-full space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="ri-user-settings-line text-purple-600"></i> ব্যক্তিগত তথ্য</h3>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 flex justify-between items-center">
                        <div>
                            <p class="text-xs text-gray-400 font-bold uppercase mb-1">User ID (UID)</p>
                            <p class="text-gray-800 font-mono font-bold text-lg select-all" id="displayUid">...</p>
                        </div>
                        <button onclick="navigator.clipboard.writeText(document.getElementById('displayUid').innerText); Swal.fire({toast:true, position:'top', icon:'success', title:'UID Copied!', timer:1500, showConfirmButton:false});" class="text-gray-400 hover:text-purple-600"><i class="ri-file-copy-line text-xl"></i></button>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <p class="text-xs text-gray-400 font-bold uppercase mb-1">Phone Number</p>
                        <p class="text-gray-800 font-bold" id="displayPhone">...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden bg-black/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4">
        <h3 class="text-lg font-bold text-gray-800">Edit Profile</h3>
        <input type="text" id="inputName" placeholder="Name" class="w-full border p-3 rounded-xl outline-none">
        <input type="tel" id="inputPhone" placeholder="Phone" class="w-full border p-3 rounded-xl outline-none">
        <div class="flex gap-2">
            <button onclick="document.getElementById('editModal').classList.add('hidden')" class="w-1/2 bg-gray-100 py-3 rounded-xl font-bold">Cancel</button>
            <button onclick="saveProfile()" class="w-1/2 bg-purple-600 text-white py-3 rounded-xl font-bold">Save</button>
        </div>
    </div>
</div>

<script>
    async function loadProfile() {
        const res = await fetch('ajax.php?action=get_profile');
        const d = await res.json();
        if(d.status === 'success') {
            const u = d.data;
            document.getElementById('displayName').innerText = u.username;
            document.getElementById('displayEmail').innerText = u.email;
            document.getElementById('avatarText').innerText = u.username.charAt(0).toUpperCase();
            document.getElementById('displayBalance').innerText = parseFloat(u.balance || 0).toFixed(2);
            document.getElementById('displayUid').innerText = u.uid;
            document.getElementById('displayPhone').innerText = u.phone || 'Not Set';
            document.getElementById('inputName').value = u.username;
            document.getElementById('inputPhone').value = u.phone || '';
        } else {
            window.location.href = 'login.php';
        }
    }

    async function saveProfile() {
        const name = document.getElementById('inputName').value;
        const phone = document.getElementById('inputPhone').value;
        const res = await fetch('ajax.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ action: 'update_profile', name, phone })
        });
        const d = await res.json();
        if(d.status === 'success') {
            Swal.fire('Success', d.message, 'success').then(() => location.reload());
        }
    }

    async function logout() {
        await fetch('ajax.php?action=logout');
        window.location.href = 'login.php';
    }

    loadProfile();
</script>
<?php include 'footer.php'; ?>