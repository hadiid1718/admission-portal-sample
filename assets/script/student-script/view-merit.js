        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('meritTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const text = row.textContent.toLowerCase();
                
                if (text.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
             function filterByProgram(program) {
            window.location.href = 'view_merit.php?program=' + encodeURIComponent(program);
        }
      
                function filterByMeritList(meritListNumber) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('merit_list', meritListNumber);
            window.location.search = urlParams.toString();
        }