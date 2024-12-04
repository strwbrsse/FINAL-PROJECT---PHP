document.addEventListener('DOMContentLoaded', function() {
    // Initialize dynamic tooltips
    function initTooltips() {
        const progressBar = document.querySelector('.progress-bar');
        if (progressBar) {
            const progress = progressBar.querySelector('.progress-fill');
            const percentage = progress.style.width;
            progressBar.setAttribute('data-tooltip', `Vaccination Progress: ${percentage}`);
        }

        // Add tooltips to status indicators
        const statusElements = document.querySelectorAll('.status');
        statusElements.forEach(element => {
            const status = element.textContent;
            let message = '';
            
            switch(status.toLowerCase()) {
                case 'completed':
                    message = 'This vaccination has been completed';
                    break;
                case 'scheduled':
                    message = 'Appointment has been scheduled';
                    break;
                case 'pending':
                    message = 'Waiting for your next dose';
                    break;
                default:
                    message = 'Current status of your vaccination';
            }
            
            element.setAttribute('data-tooltip', message);
        });
    }

    // Update tooltips when data changes
    function updateTooltips() {
        const vaccineCount = document.getElementById('completed-vaccines');
        if (vaccineCount) {
            const count = vaccineCount.textContent;
            vaccineCount.setAttribute('data-tooltip', 
                `You have completed ${count} of your required vaccinations`);
        }
    }

    // Initialize tooltips
    initTooltips();

    // Update tooltips when data changes
    const observer = new MutationObserver(updateTooltips);
    observer.observe(document.body, { 
        subtree: true, 
        childList: true, 
        characterData: true 
    });
}); 