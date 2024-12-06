document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  const MIN_PROCESSING_TIME = 800;

  function clearErrors() {
    document.querySelectorAll(".error-message").forEach((element) => {
      element.textContent = "";
      element.style.display = "none";
    });
    document.querySelectorAll("input, select, textarea").forEach((element) => {
      element.classList.remove("error");
    });
  }

  function showError(fieldname, message) {
    const errorElement = document.getElementById(`${fieldname.toLowerCase()}-error`);
    if (errorElement) {
      errorElement.textContent = message;
      errorElement.style.display = "block";
      
      const inputField = document.querySelector(`[name="${fieldname.toLowerCase()}"]`);
      if (inputField) {
        inputField.classList.add("error");
      }
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

    fetch("../BackEnd/Main.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        console.log("Server response:", data);
        
        if (data.success) {
          if (data.userData) {
            console.log("Setting user data:", data.userData);
            sessionStorage.setItem('userId', data.userData.userId);
            sessionStorage.setItem('userName', data.userData.userName);
          }
          
          swal("Success!", data.message, "success").then(() => {
            if (data.redirect) {
              window.location.href = data.redirect;
            }
          });
        } else {
          if (data.errors && Array.isArray(data.errors)) {
            data.errors.forEach(error => {
              showError(error.field, error.message);
            });
          } else {
            swal("Error!", data.message || "An unexpected error occurred", "error");
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