import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/* hamburger toggle */
document.addEventListener('DOMContentLoaded', function(){
    const hamburger = document.getElementById('hamburger-icon');
    const navList = document.querySelector('.nav__list');

    if (hamburger && navList){
        hamburger.addEventListener('click', function(){
            navList.classList.toggle('active'); 
            this.classList.toggle('bx-x'); 
            this.classList.toggle('bx-menu'); 
        });
        
        navList.querySelectorAll('.nav__link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 799) { 
                    navList.classList.remove('active');
                    hamburger.classList.remove('bx-x');
                    hamburger.classList.add('bx-menu');
                }
            });
        });
    }
});

/* password toggle */
document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');
        const toggleIcon = document.getElementById('toggleIcon'); 

        if (passwordInput && toggleButton && toggleIcon) {
            const iconSlash = toggleIcon.querySelector('#iconSlash');

            toggleButton.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                if (iconSlash) {
                    iconSlash.style.display = isPassword ? 'none' : 'inline';
                }

                toggleButton.setAttribute('aria-pressed', (!isPassword).toString());
            });
        }
        const passwordInputConfirm = document.getElementById('password_confirmation');
        const toggleButtonConfirm = document.getElementById('togglePasswordConfirmation');
        const toggleIconConfirm = document.getElementById('toggleIconConfirmation');

        if (passwordInputConfirm && toggleButtonConfirm && toggleIconConfirm) {
            const iconSlashConfirm = toggleIconConfirm.querySelector('#iconSlashConfirmation');

            toggleButtonConfirm.addEventListener('click', function () {
                const isPassword = passwordInputConfirm.getAttribute('type') === 'password';
                passwordInputConfirm.setAttribute('type', isPassword ? 'text' : 'password');

                if (iconSlashConfirm) {
                    iconSlashConfirm.style.display = isPassword ? 'none' : 'inline';
                }

                toggleButtonConfirm.setAttribute('aria-pressed', (!isPassword).toString());
            });
        }
});

/* menus */
document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab-item');
        const menuGrids = document.querySelectorAll('.menu-grid');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // จัดการคลาส Active บน Tabs
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const filter = this.getAttribute('data-filter');
                const isAll = (filter === 'All');

                /*
                วนลูป menuGrids (กลุ่มสินค้า)
                 */
                menuGrids.forEach(grid => { // <-- แก้ไขตรงนี้
                    const category = grid.getAttribute('data-category');
                    
                    if (isAll || category === filter) {
                        // ใช้ 'display: grid' เพื่อแสดงผล (เพราะใน CSS คุณใช้ 'display: grid')
                        // หรือใช้ '' เพื่อให้กลับไปใช้ค่าจาก .css
                        grid.style.display = 'grid'; 
                    } else {
                        grid.style.display = 'none'; // ซ่อนทั้งกลุ่ม
                    }
                });
            });
        });
        
        // กำหนดให้ Tab 'ทั้งหมด' ถูกเลือกและทำการกรองตั้งแต่แรกเมื่อโหลดหน้า
        document.querySelector('.tab-item[data-filter="All"]').click(); 
});

