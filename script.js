      // Interactividad básica
        document.querySelectorAll('.league-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.league-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Animación de carga para los botones
        document.querySelectorAll('.view-all').forEach(button => {
            button.addEventListener('click', function() {
                const originalText = this.textContent;
                this.innerHTML = '<span class="loading-animation"></span>';
                
                setTimeout(() => {
                    this.textContent = originalText;
                }, 1500);
            });
        });

        //  el menu XD
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            const navLinks = document.querySelector('.nav-links');
            navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
        });