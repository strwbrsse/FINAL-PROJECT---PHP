document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  const MIN_PROCESSING_TIME = 800; // Minimum processing time in milliseconds

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

    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.textContent;
    submitButton.disabled = true;
    
    const formData = new FormData(form);
    const action = formData.get('action');

    if (action === 'signin') {
      submitButton.textContent = "Signing in...";
    } else if (action === 'register') {
      submitButton.textContent = "Registering...";
    } else if (action === 'signup') {
      submitButton.textContent = "Creating Account...";
    } else {
      submitButton.textContent = "Processing...";
    }

    const startTime = Date.now();

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
        const elapsedTime = Date.now() - startTime;
        const remainingTime = Math.max(0, MIN_PROCESSING_TIME - elapsedTime);

        return new Promise(resolve => {
          setTimeout(() => resolve(data), remainingTime);
        });
      })
      .then((data) => {
        console.log("Server response:", data);
        if (data.success) {
          swal("Success!", data.message, "success").then(() => {
            if (action === 'register') {
              window.location.href = "../FrontEnd/SignUp.html";
            } else if (action === 'signup') {
              window.location.href = data.redirect || "../FrontEnd/dashboard.html";
            } else if (action === 'signin') {
              window.location.href = "../FrontEnd/dashboard.html";
            }
          });
        } else {
          if (data.errors && Array.isArray(data.errors)) {
            const isDuplicateError = data.errors.some(error => 
                error.message.includes("already registered") || 
                error.message.includes("already exists")
            );
            
            if (isDuplicateError) {
              const errorMessage = data.errors.map(error => error.message).join('\n');
              swal("Error!", errorMessage, "error");
            } else {
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
      })
      .finally(() => {
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
      });
  });
});