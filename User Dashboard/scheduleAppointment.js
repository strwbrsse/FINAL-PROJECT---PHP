function submitAppointment(event) {
    event.preventDefault();

    const formData = new FormData(document.getElementById("appointmentForm"));
    const data = Object.fromEntries(formData.entries());

    fetch("scheduleAppointment.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        const responseMessage = document.getElementById("responseMessage");
        if (result.success) {
            responseMessage.innerText = "Appointment scheduled successfully!";
            responseMessage.style.color = "green";
            document.getElementById("appointmentForm").reset();
        } else {
            responseMessage.innerText = "Failed to schedule appointment. Please try again.";
            responseMessage.style.color = "red";
        }
    })
    .catch(error => {
        console.error("Error:", error);
        document.getElementById("responseMessage").innerText = "An error occurred. Please try again.";
    });
}