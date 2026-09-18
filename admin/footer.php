</main>
    </div>
</div>

<script>
    // মোবাইল ড্রয়ার টগল
    function toggleAdminSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }

    // লগআউট প্রম্পট
    async function adminLogout() {
        Swal.fire({
            title: 'Logout Admin?',
            text: "Are you sure you want to end your administrative session?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Logout'
        }).then(async (result) => {
            if (result.isConfirmed) {
                await fetch('../ajax.php?action=logout');
                window.location.href = '../login.php';
            }
        });
    }

    // বর্তমান মেনু হাইলাইট
    const currentPath = window.location.pathname.split("/").pop();
    document.querySelectorAll('.nav-item').forEach(link => {
        if(link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });
</script>
</body>
</html>