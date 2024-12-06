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

document.addEventListener('DOMContentLoaded', loadVaccineOptions);
