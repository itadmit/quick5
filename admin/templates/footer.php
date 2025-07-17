<!-- Mobile sidebar backdrop -->
<div class="fixed inset-0 z-40 hidden backdrop-blur-sm" id="sidebar-backdrop" onclick="toggleSidebar()" style="background: rgba(26, 42, 66, 0.8);"></div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        
        if (window.innerWidth < 1024) {
            sidebar.classList.toggle('hidden');
            backdrop.classList.toggle('hidden');
        }
    }

    // Close sidebar on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            document.getElementById('sidebar').classList.remove('hidden');
            document.getElementById('sidebar-backdrop').classList.add('hidden');
        }
    });
</script> 