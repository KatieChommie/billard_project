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
                if (window.innerWidth <= 768) {
                    navList.classList.remove('active');
                    hamburger.classList.remove('bx-x');
                    hamburger.classList.add('bx-menu');
                }
            });
        });
    }
});