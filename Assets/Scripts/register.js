document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");

  function clearErrors() {
    document.querySelectorAll(".error-message").forEach((element) => {
      element.textContent = "";
      element.style.display = "none";
    });
  }

  function showError(fieldname, message) {
    const errorElement = document.getElementById(`${fieldname}-error`);
    if (errorElement) {
      errorElement.textContent = message;
      errorElement.style.display = "block";
      errorElement.style.color = "red";
    }
  }

  form.addEventListener("submit", function (event) {
    event.preventDefault();
    clearErrors();

    const formData = new FormData(form);
    const action = formData.get('action'); // Get the form action (register or signup)
    console.log("Form Data:", Object.fromEntries(formData));

    fetch("../BackEnd/Main.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        console.log("Server response:", data);
        if (data.success) {
          swal("Success!", data.message, "success").then(() => {
            // Check action type for redirect
            if (action === 'register') {
              window.location.href = "../FrontEnd/SignUp.html";
            } else if (action === 'signup') {
              window.location.href = data.redirect || "../FrontEnd/dashboard.html";
            }
          });
        } else {
          if (data.errors && Array.isArray(data.errors)) {
            // Check if it's a duplicate error
            const isDuplicateError = data.errors.some(error => 
                error.message.includes("already registered") || 
                error.message.includes("already exists")
            );
            
            if (isDuplicateError) {
                // Show sweet alert for duplicates
                const errorMessage = data.errors.map(error => error.message).join('\n');
                swal("Error!", errorMessage, "error");
            } else {
                // Display field-level errors
                data.errors.forEach((error) => {
                    showError(error.field.toLowerCase(), error.message);
                });
            }
          } else if (data.field && data.message) {
            showError(data.field.toLowerCase(), data.message);
          } else {
            swal("Error!", data.message || "An error occurred", "error");
          }
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        swal("Error!", "An unexpected error occurred.", "error");
      });
  });
});
