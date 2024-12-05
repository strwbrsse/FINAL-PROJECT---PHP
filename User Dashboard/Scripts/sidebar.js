document.addEventListener('DOMContentLoaded', function() {
    // Get all nav items
    const navItems = document.querySelectorAll('.nav-item');
    
    // Add click handler for each nav item
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            // First remove active class from all items
            navItems.forEach(nav => {
                nav.classList.remove('active');
            });
            
            // Add active class only to the clicked item
            this.classList.add('active');
            
            // Store the clicked item's href
            const clickedHref = this.getAttribute('href');
            localStorage.setItem('activeNav', clickedHref);
        });
    });
    
    // Set active state based on current page URL
    const currentPage = window.location.pathname.split('/').pop();
    const activeNav = localStorage.getItem('activeNav');
    
    navItems.forEach(item => {
        const itemHref = item.getAttribute('href');
        if (itemHref === currentPage || itemHref === activeNav) {
            item.classList.add('active');
        }
    });
});
