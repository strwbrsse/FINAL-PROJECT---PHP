document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(form);
        console.log('Form Data:', Object.fromEntries(formData));

        fetch('../BackEnd/Main.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                swal("Success!", data.message, "success").then(() => {
                    window.location.href = "dashboard.html";
                });
            } else {
                swal("Error!", data.message, "error");
            }
        })  
        .catch(error => {
            console.error('Error:', error);
            swal("Error!", "An unexpected error occurred.", "error");
        });
    });
});
