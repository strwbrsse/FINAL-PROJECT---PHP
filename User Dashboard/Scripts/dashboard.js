const MESSAGES = {
    LOADING: {
        vaccines: 'Loading your vaccination records...',
        appointments: 'Checking upcoming appointments...',
        progress: 'Calculating your progress...'
    },
    ERROR: {
        vaccines: 'Unable to load vaccination records. Please try refreshing the page.',
        appointments: 'Unable to load appointment details. Please check back in a moment.',
        progress: 'Unable to update progress. Please try again later.',
        network: 'Please check your internet connection and try again.',
        general: 'Something went wrong. Please try again later.',
        session: 'Your session has expired. Please log in again.'
    },
    EMPTY: {
        vaccines: 'No vaccination records found. Your records will appear here once added.',
        appointments: 'No upcoming appointments scheduled.',
        progress: 'Start your vaccination journey by scheduling your first appointment.'
    }
};

function showLoading(elementId, type = 'spinner') {
    const element = document.getElementById(elementId);
    if (!element) return;

    // Clear existing content
    element.innerHTML = '';

    switch (type) {
        case 'spinner':
            element.innerHTML = `
                <div class="loading-spinner"></div>
                <div class="loading-text">Loading...</div>
            `;
            break;
        case 'bar':
            element.innerHTML = `
                <div class="loading-bar"></div>
                <div class="loading-text">Loading data...</div>
            `;
            break;
        case 'pulse':
            element.innerHTML = `
                <div style="text-align: center;">
                    <div class="loading-pulse"></div>
                    <div class="loading-pulse"></div>
                    <div class="loading-pulse"></div>
                </div>
            `;
            break;
    }
}

function hideLoading(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;

    // Remove loading indicators
    const loadingElements = element.querySelectorAll('.loading-spinner, .loading-bar, .loading-pulse, .loading-text');
    loadingElements.forEach(el => el.remove());
}

async function updateDashboardData() {
    try {
        // Show loading states
        showLoading('completed-vaccines', 'spinner');
        showLoading('next-appointment', 'pulse');
        showLoading('upcoming-doses', 'bar');
        showLoading('vaccine-progress', 'bar');

        // Fetch vaccine progress
        const vaccineResponse = await fetch('completed_vaccines.php?action=get_progress');
        if (!vaccineResponse.ok) throw new Error('vaccine_error');
        const vaccines = await vaccineResponse.json();

        // Update completed vaccines
        hideLoading('completed-vaccines');
        if (vaccines.length === 0) {
            document.getElementById('completed-vaccines').textContent = 'No vaccines completed';
        } else {
            let completed = 0;
            let total = 0;
            vaccines.forEach(vaccine => {
                completed += parseInt(vaccine.completed_doses);
                total += parseInt(vaccine.total_doses);
            });
            document.getElementById('completed-vaccines').textContent = `${completed}/${total}`;
        }

        // Fetch appointments
        const appointmentResponse = await fetch('appointments.php?action=get_upcoming');
        if (!appointmentResponse.ok) throw new Error('appointment_error');
        const appointments = await appointmentResponse.json();

        // Update appointments
        hideLoading('next-appointment');
        if (appointments.length === 0) {
            document.getElementById('next-appointment').textContent = 'No upcoming appointments';
        } else {
            const nextDate = new Date(appointments[0].appointment_date);
            document.getElementById('next-appointment').textContent = nextDate.toLocaleDateString();
        }

        // Update other sections...
        hideLoading('upcoming-doses');
        hideLoading('vaccine-progress');

    } catch (error) {
        console.error('Error updating dashboard:', error);
        // Handle errors and hide loading indicators
        ['completed-vaccines', 'next-appointment', 'upcoming-doses', 'vaccine-progress'].forEach(id => {
            hideLoading(id);
            document.getElementById(id).textContent = 'Error loading data';
        });
    }
}

function showAlert(message, type) {
    const alert = document.getElementById('alert');
    if (!alert) return;

    alert.textContent = message;
    alert.className = `alert alert-${type}`;
    alert.style.display = 'block';

    // Add retry button for errors
    if (type === 'error') {
        const retryButton = document.createElement('button');
        retryButton.className = 'btn btn-small';
        retryButton.innerHTML = '<i class="fas fa-redo"></i> Retry';
        retryButton.onclick = () => {
            alert.style.display = 'none';
            updateDashboardData();
        };
        alert.appendChild(retryButton);
    }

    // Auto-hide success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 5000);
    }
}

async function updateVaccinationProgress() {
    try {
        const response = await fetch('api/vaccination-progress.php');
        const data = await response.json();
        
        // Update completed vaccines count
        const completedVaccines = document.getElementById('completed-vaccines');
        completedVaccines.textContent = `${data.completed}/${data.total}`;
        
        // Update progress bar
        const progressBar = document.getElementById('vaccine-progress');
        const progressPercentage = (data.completed / data.total) * 100;
        progressBar.style.width = `${progressPercentage}%`;
        
        // Add color coding based on progress
        if (progressPercentage === 100) {
            progressBar.style.background = 'var(--success-color)';
        } else if (progressPercentage > 50) {
            progressBar.style.background = 'var(--accent-color)';
        } else {
            progressBar.style.background = 'var(--warning-color)';
        }

        // Update overall progress bar
        const overallProgressBar = document.getElementById('progress-bar');
        overallProgressBar.style.width = `${progressPercentage}%`;
        
        // Add animation class for smooth transition
        progressBar.classList.add('progress-updating');
        setTimeout(() => progressBar.classList.remove('progress-updating'), 600);
        
    } catch (error) {
        console.error('Error updating vaccination progress:', error);
    }
}

// Function to be called after adding a new vaccination record
function onVaccinationAdded() {
    updateVaccinationProgress();
    loadUpcomingDoses();
    updateRecentActivity();
}

// Set up real-time updates
document.addEventListener('vaccinationAdded', onVaccinationAdded);

// Initial load
document.addEventListener('DOMContentLoaded', () => {
    updateVaccinationProgress();
});

// Refresh progress periodically (optional)
setInterval(updateVaccinationProgress, 5 * 60 * 1000); // Every 5 minutes

// Search functionality
const searchInput = document.querySelector('.header input');
searchInput.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.cards .card');
    
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});

function toggleVaccineList() {
    const list = document.getElementById('vaccine-list');
    const button = document.querySelector('.toggle-vaccines');
    const icon = button.querySelector('i');
    
    list.classList.toggle('hidden');
    button.classList.toggle('collapsed');
}