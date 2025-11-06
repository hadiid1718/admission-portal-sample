        // Auto-focus on print button when page loads
        window.onload = function() {
            document.querySelector('.print-btn').focus();
        }
        
        // Optional: Show print dialog automatically
        // Uncomment the line below if you want auto-print
        // window.onload = function() { window.print(); }