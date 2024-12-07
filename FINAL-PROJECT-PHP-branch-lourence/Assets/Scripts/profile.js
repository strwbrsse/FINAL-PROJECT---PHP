// Function to load user profile data
async function loadProfileData() {
    try {
        const response = await fetch('../BackEnd/Main.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'get_profile'
            })
        });
        
        const data = await response.json();
        
        if (data.success && data.userData) {
            // Fill form fields with user data
            document.querySelector('input[name="fname"]').value = data.userData.fname || '';
            document.querySelector('input[name="mname"]').value = data.userData.mname || '';
            document.querySelector('input[name="lname"]').value = data.userData.lname || '';
            document.querySelector('input[name="email"]').value = data.userData.email || '';
            document.querySelector('input[name="contact"]').value = data.userData.contact || '';
            document.querySelector('input[name="birthday"]').value = data.userData.birthday || '';
            document.querySelector('select[name="sex"]').value = data.userData.sex || '';
            document.querySelector('select[name="civilstat"]').value = data.userData.civilstat || '';
            document.querySelector('input[name="address"]').value = data.userData.address || '';
            
            // Update header display name if it exists
            const headerName = document.querySelector('.header .user-name');
            if (headerName) {
                headerName.textContent = `${data.userData.fname} ${data.userData.lname}`;
            }
        }
    } catch (error) {
        console.error('Error loading profile:', error);
        showAlert('Error loading profile data', 'error');
    }
}

// Function to update user profile
async function updateProfile(event) {
    event.preventDefault();
    
    try {
        const formData = new FormData(document.getElementById('profile-form'));
        formData.append('action', 'update_profile');
        
        const response = await fetch('../BackEnd/Main.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Profile updated successfully!', 'success');
            // Reload profile data to show updated information
            await loadProfileData();
        } else {
            showAlert(data.message || 'Failed to update profile', 'error');
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        showAlert('An error occurred while updating profile', 'error');
    }
}

// Function to delete user profile
async function deleteProfile() {
    // Show confirmation dialog
    if (!confirm('Are you sure you want to delete your profile? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch('../BackEnd/Main.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete_profile'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Profile deleted successfully', 'success');
            // Redirect to register page after short delay
            setTimeout(() => {
                window.location.href = '../FrontEnd/Register.html';
            }, 2000);
        } else {
            showAlert(data.message || 'Failed to delete profile', 'error');
        }
    } catch (error) {
        console.error('Error deleting profile:', error);
        showAlert('An error occurred while deleting profile', 'error');
    }
}

// Helper function to show alerts
function showAlert(message, type) {
    const alertDiv = document.getElementById('alert');
    alertDiv.textContent = message;
    alertDiv.className = `alert alert-${type}`;
    alertDiv.style.display = 'block';
    
    // Hide alert after 3 seconds
    setTimeout(() => {
        alertDiv.style.display = 'none';
    }, 3000);
}

// Add event listeners when document loads
document.addEventListener('DOMContentLoaded', () => {
    // Load initial profile data
    loadProfileData();
    
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', updateProfile);
    }
    
    const deleteButton = document.getElementById('delete-profile');
    if (deleteButton) {
        deleteButton.addEventListener('click', deleteProfile);
    }
});
