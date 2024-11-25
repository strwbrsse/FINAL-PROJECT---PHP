document.addEventListener("DOMContentLoaded", loadProfileData);

function loadProfileData() {
    fetch('getProfile.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('fname').value = data.fname || '';
            document.getElementById('mname').value = data.mname || '';
            document.getElementById('lname').value = data.lname || '';
            document.getElementById('birthday').value = data.birthday || '';
            document.getElementById('mail').value = data.mail || '';
            document.getElementById('contact').value = data.contact || '';
            document.getElementById('address').value = data.address || '';
            document.getElementById('barangay').value = data.barangay || '';
            document.getElementById('sex').value = data.sex || '';
            document.getElementById('civilstat').value = data.civilstat || '';
            document.getElementById('employmentstat').value = data.employmentstat || '';
            document.getElementById('employer').value = data.employer || '';
            document.getElementById('profession').value = data.profession || '';
        });
}

function submitProfileUpdate(event) {
    event.preventDefault(); 

    const formData = new FormData(document.getElementById("profileForm"));
    const data = Object.fromEntries(formData.entries());

    fetch("updateProfile.php", {
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
            responseMessage.innerText = "Profile updated successfully!";
            responseMessage.style.color = "green";
        } else {
            responseMessage.innerText = "Failed to update profile. Please try again.";
            responseMessage.style.color = "red";
        }
    })
    .catch(error => {
        console.error("Error:", error);
        document.getElementById("responseMessage").innerText = "An error occurred. Please try again.";
    });
}