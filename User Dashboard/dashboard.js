function scheduleAppointment() {
    window.location.href = 'scheduleAppointment.html';
}

function updateInformation() {
    window.location.href = 'updateProfile.html';
}

function logout() {
    window.location.href = 'logout.php';
}

function submitFeedback() {
    const feedback = document.getElementById('feedback').value;
    if (feedback) {
        fetch('submitFeedback.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ feedback }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Thank you for your feedback!');
                document.getElementById('feedback').value = '';
            } else {
                alert('Error submitting feedback. Please try again.');
            }
        });
    } else {
        alert('Please enter your feedback.');
    }
}

document.addEventListener("DOMContentLoaded", loadVaccinationHistory);

function loadVaccinationHistory() {
    fetch('getVaccinationHistory.php')
        .then(response => response.json())
        .then(data => {
            const historyContainer = document.getElementById('vaccination-history-content');
            historyContainer.innerHTML = data.map(record => `
                <div>
                    <strong>${record.vaccine_name}</strong> - Dose ${record.dose_number}
                    <br>Date: ${record.date_administered}
                    <br>Location: ${record.location || "N/A"}
                </div>
                <hr>
            `).join('');
        });
}

document.addEventListener("DOMContentLoaded", loadUpcomingAppointments);

function loadUpcomingAppointments() {
    fetch('getUpcomingAppointments.php')
        .then(response => response.json())
        .then(data => {
            const appointmentsContainer = document.getElementById('upcoming-appointments-content');
            appointmentsContainer.innerHTML = data.length > 0 ? data.map(appointment => `
                <div>
                    <strong>${appointment.vaccine_name}</strong>
                    <br>Date Booked: ${appointment.date_booked}
                    <br>Location: ${appointment.location || "N/A"}
                </div>
                <hr>
            `).join('') : "No upcoming appointments.";
        });
}

document.addEventListener("DOMContentLoaded", loadReminders);

function loadReminders() {
    fetch('getReminders.php')
        .then(response => response.json())
        .then(data => {
            const reminderList = document.getElementById('reminder-list');
            reminderList.innerHTML = data.map(reminder => `<li>${reminder}</li>`).join('');
        });
}