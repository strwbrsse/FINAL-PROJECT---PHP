document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            navItems.forEach(nav => {
                nav.classList.remove('active');
            });
            
            this.classList.add('active');
            
            const clickedHref = this.getAttribute('href');
            localStorage.setItem('activeNav', clickedHref);
        });
    });
    
    const currentPage = window.location.pathname.split('/').pop();
    const activeNav = localStorage.getItem('activeNav');
    
    navItems.forEach(item => {
        const itemHref = item.getAttribute('href');
        if (itemHref === currentPage || itemHref === activeNav) {
            item.classList.add('active');
        }
    });
});
