document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebar-toggle');
    const content = document.querySelector('.content');
    
    // Load saved state
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed) {
        sidebar.classList.add('collapsed');
        content.classList.add('expanded');
        toggle.querySelector('i').classList.replace('fa-chevron-left', 'fa-chevron-right');
    }

    toggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        content.classList.toggle('expanded');
        
        // Toggle icon
        const icon = toggle.querySelector('i');
        if (sidebar.classList.contains('collapsed')) {
            icon.classList.replace('fa-chevron-left', 'fa-chevron-right');
        } else {
            icon.classList.replace('fa-chevron-right', 'fa-chevron-left');
        }
        
        // Save state
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });

    // Show tooltips when sidebar is collapsed
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        const span = item.querySelector('span');
        if (span) {
            const text = span.textContent;
            item.setAttribute('title', text);
        }
    });

    // Set active navigation item
    function setActiveNavItem() {
        const currentPath = window.location.pathname;
        const navItems = document.querySelectorAll('.nav-item');
        
        navItems.forEach(item => {
            // Remove active class from all items
            item.classList.remove('active');
            
            // Get the href path
            const href = item.getAttribute('href');
            
            // Check if current path matches the nav item's href
            if (currentPath.endsWith(href) || 
                (currentPath === '/' && href === 'dashboard.html')) {
                item.classList.add('active');
            }
        });
    }

    // Call on page load
    setActiveNavItem();

    // Update active state when using browser back/forward
    window.addEventListener('popstate', setActiveNavItem);
});

function setActivePage() {
    // Get current page filename
    const currentPage = window.location.pathname.split('/').pop() || 'dashboard.html';
    
    // Remove active class from all nav items
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => item.classList.remove('active'));
    
    // Add active class to current page's nav item
    const activeItem = document.querySelector(`.nav-item[href="${currentPage}"]`);
    if (activeItem) {
        activeItem.classList.add('active');
    }
} 