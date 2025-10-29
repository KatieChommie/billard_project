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