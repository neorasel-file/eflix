<?php
// includes/footer.php - Site Footer
?>

    <!-- jQuery (if needed) -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    
    <!-- Custom JS -->
    <script src="js/main.js"></script>
    
    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        }
        
        // Theme toggle
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
        
        // Load saved theme
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
        
        // Copy to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('কপি হয়েছে!', 'success');
            }).catch(() => {
                alert('কপি করতে ব্যর্থ হয়েছে');
            });
        }
        
        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-20 left-4 right-4 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white p-3 rounded-lg text-center z-50 fade-in`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        // Confirm action
        function confirmAction(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }
        
        // Format number
        function formatNumber(num, decimals = 2) {
            return parseFloat(num).toFixed(decimals);
        }
        
        // Format currency
        function formatCurrency(amount, currency = '$') {
            return currency + formatNumber(amount);
        }
    </script>
</body>
</html>