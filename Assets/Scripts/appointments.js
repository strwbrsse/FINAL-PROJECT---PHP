document.addEventListener('DOMContentLoaded', function() {
    loadAppointments('upcoming');
    checkSession();

    // Add form submission handler
    const appointmentForm = document.getElementById('appointment-form');
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                action: 'create_appointment',
                vaccine_type: document.getElementById('vaccine_type').value,
                appointment_date: document.getElementById('appointment_date').value,
                appointment_time: document.getElementById('appointment_time').value
            };

            console.log('Submitting appointment:', formData);

            try {
                const response = await fetch('../BackEnd/Main.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData),
                    credentials: 'include' 
                });
                
                console.log('Response status:', response.status);
                const result = await response.json();
                console.log('Response data:', result);
                
                if (result.success) {
                    closeModal();
                    appointmentForm.reset();
                    await loadAppointments('upcoming');
                    alert('Appointment scheduled successfully!');
                } else {
                    alert(result.message || 'Failed to create appointment');
                }
            } catch (error) {
                console.error('Error creating appointment:', error);
                alert('Failed to create appointment. Please try again.');
            }
        });
    } else {
        console.error('Appointment form not found');
    }
});

async function loadAppointments(type) {
    try {
        console.log('Loading appointments for type:', type);
        const action = type === 'upcoming' ? 'get_upcoming' : 'get_past';
        const response = await fetch(`../BackEnd/Main.php?action=${action}`, {
            method: 'GET',
            credentials: 'include' 
        });
        const data = await response.json();
        console.log(`${type} appointments data:`, data);
        
        const container = document.getElementById(`${type}-appointments`);
        if (!container) {
            console.error(`Container for ${type} appointments not found`);
            return;
        }
        container.innerHTML = '';

        if (!data.success) {
            console.error('Failed to load appointments:', data.message);
            container.innerHTML = '<div class="error">Failed to load appointments</div>';
            return;
        }

        if (!data.data || data.data.length === 0) {
            console.log(`No ${type} appointments found`);
            container.innerHTML = `<div class="no-appointments">No ${type} appointments found</div>`;
            return;
        }

        data.data.forEach(appointment => {
            console.log('Processing appointment:', appointment);
            try {
                const date = new Date(appointment.appointment_date);
                const formattedDate = date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                const time = appointment.appointment_time.substring(0, 5);
                
                const appointmentEl = document.createElement('div');
                appointmentEl.className = 'appointment-item';
                appointmentEl.innerHTML = `
                    <div class="appointment-info">
                        <div class="appointment-date">${formattedDate}</div>
                        <div class="appointment-time">${time}</div>
                        <div class="appointment-vaccine">${appointment.vaccine_type}</div>
                        <div class="appointment-status">${appointment.status || 'scheduled'}</div>
                    </div>
                    <div class="appointment-actions">
                        ${type === 'upcoming' && (!appointment.status || appointment.status === 'scheduled') ? `
                            <button class="btn-cancel" onclick="cancelAppointment(${appointment.id})">
                                Cancel
                            </button>
                        ` : ''}
                    </div>
                `;
                container.appendChild(appointmentEl);
            } catch (err) {
                console.error('Error processing appointment:', err, appointment);
            }
        });
    } catch (error) {
        console.error('Error loading appointments:', error);
        const container = document.getElementById(`${type}-appointments`);
        if (container) {
            container.innerHTML = '<div class="error">Failed to load appointments</div>';
        }
    }
}

function switchTab(tab) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.tab:nth-child(${tab === 'upcoming' ? '1' : '2'})`).classList.add('active');
    
    document.getElementById('upcoming-appointments').style.display = tab === 'upcoming' ? 'block' : 'none';
    document.getElementById('past-appointments').style.display = tab === 'past' ? 'block' : 'none';
    
    loadAppointments(tab);
}

function openNewAppointmentModal() {
    const modal = document.getElementById('appointment-modal');
    if (modal) {
        modal.style.display = 'block';
        // Set minimum date to today
        const dateInput = document.getElementById('appointment_date');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.min = today;
            dateInput.value = today;
        }
    } else {
        console.error('Modal not found');
    }
}

function closeModal() {
    const modal = document.getElementById('appointment-modal');
    if (modal) {
        modal.style.display = 'none';
    } else {
        console.error('Modal not found');
    }
}

async function cancelAppointment(appointmentId) {
    if (!confirm('Are you sure you want to cancel this appointment?')) {
        return;
    }
    
    try {
        const response = await fetch('../BackEnd/Main.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'cancel_appointment',
                appointment_id: appointmentId,
                status: 'cancelled'
            }),
            credentials: 'include'
        });
        
        const result = await response.json();
        console.log('Cancel appointment result:', result);
        
        if (result.success) {
            await loadAppointments('upcoming');
            alert('Appointment cancelled successfully');
        } else {
            alert(result.message || 'Failed to cancel appointment');
        }
    } catch (error) {
        console.error('Error cancelling appointment:', error);
        alert('Failed to cancel appointment');
    }
}

async function checkSession() {
    try {
        const response = await fetch('../BackEnd/check_session.php');
        const data = await response.json();
        console.log('Session check:', data);
    } catch (error) {
        console.error('Session check failed:', error);
    }
}
