document.addEventListener('DOMContentLoaded', async function() {
    // Get name_id
    const nameId = await SessionManager.getNameId();
    if (!nameId) return;

    const navItems = document.querySelectorAll('.nav-item');
    
    // Get the full current URL path
    const fullPath = window.location.pathname;
    // Get just the filename without query parameters
    const currentPage = fullPath.split('/').pop().split('?')[0].toLowerCase();
    
    // Remove active class from all items first
    navItems.forEach(item => {
        item.classList.remove('active');
        
        // Get the href and normalize it
        const href = item.getAttribute('href');
        const itemPage = href.split('/').pop().toLowerCase();
        
        // Check for exact match or if both contain 'appointment'
        if (itemPage === currentPage || 
            (itemPage.includes('appointment') && currentPage.includes('appointment'))) {
            item.classList.add('active');
        }

        // Add name_id to URLs except logout
        if (href && !href.includes('logout.php')) {
            const separator = href.includes('?') ? '&' : '?';
            item.href = `${href}${separator}name_id=${nameId}`;
        }
    });
});
