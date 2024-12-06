function loadVaccineOptions() {
    fetch('your_backend_endpoint.php?action=get_available_vaccines') // Make sure this path is correct
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const vaccineSelect = document.getElementById('vaccine-type'); // Make sure this ID matches your dropdown
                vaccineSelect.innerHTML = ''; 

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Select Vaccine Type';
                vaccineSelect.appendChild(defaultOption);

                data.data.forEach(vaccine => {
                    const option = document.createElement('option');
                    option.value = vaccine.vaccine_name; 
                    option.textContent = vaccine.vaccine_name;
                    vaccineSelect.appendChild(option);
                });
            } else {
                console.error('Failed to load vaccines:', data.error);
            }
        })
        .catch(error => {
            console.error('Error fetching vaccines:', error);
        });
}

document.addEventListener('DOMContentLoaded', function() {
    loadVaccineOptions();

    // Add form submission handler
    const appointmentForm = document.getElementById('appointment-form');
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                action: 'create_appointment',
                vaccine_type: document.getElementById('vaccine-type').value,
                appointment_date: document.getElementById('appointment-date').value,
                appointment_time: document.getElementById('appointment-time').value
            };

            fetch('../BackEnd/Main.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    swal("Success!", data.message, "success");
                    appointmentForm.reset();
                } else {
                    swal("Error!", data.message || "Failed to create appointment", "error");
                }
            })
            .catch(error => {
                console.error('Error creating appointment:', error);
                swal("Error!", "Failed to create appointment. Please try again.", "error");
            });
        });
    }
});
