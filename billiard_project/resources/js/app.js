import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', function(){
    const hamburger = document.getElementById('hamburger-icon'); // Use your ID
    const navList = document.querySelector('.nav__list');     // Use your class

    if (hamburger && navList){
        hamburger.addEventListener('click', function(){
            navList.classList.toggle('active'); // Use your class
            // Toggle hamburger icon (assuming you use boxicons classes)
            this.classList.toggle('bx-x'); 
            this.classList.toggle('bx-menu'); 
        });
        
        // Close menu when link is clicked (optional but good UX)
        navList.querySelectorAll('.nav__link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 799) { // Check your breakpoint
                    navList.classList.remove('active');
                    hamburger.classList.remove('bx-x');
                    hamburger.classList.add('bx-menu');
                }
            });
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');
        const toggleIcon = document.getElementById('toggleIcon'); // อ้างอิงถึง i tag โดยตรง

        // Guard: only attach if all elements exist (prevents errors on pages without the login form)
        if (passwordInput && toggleButton && toggleIcon) {
            // find the slash element inside the inline SVG (we toggle its visibility)
            const iconSlash = toggleIcon.querySelector('#iconSlash');

            toggleButton.addEventListener('click', function () {
                // สลับประเภทของ input
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                // Toggle the slash visibility: when showing text (isPassword true -> now text), hide the slash
                if (iconSlash) {
                    iconSlash.style.display = isPassword ? 'none' : 'inline';
                }

                // Update accessible state
                toggleButton.setAttribute('aria-pressed', (!isPassword).toString());
            });
        }
    });
