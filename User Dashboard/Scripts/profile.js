// Update fetch paths to include proper directory structure
async function loadProfile() {
    try {
        const response = await fetch('../BackEnd/profile.php?action=get_profile');
        const profile = await response.json();
        
        fillProfileData(profile);
        updateProfilePicture(profile.profile_picture);
        document.getElementById('user-name').textContent = 
            `${profile.fname} ${profile.mname ? profile.mname + ' ' : ''}${profile.lname}`;
    } catch (error) {
        console.error('Error loading profile:', error);
        showAlert('Error loading profile data', 'error');
    }
}

// Update profile picture upload path
async function uploadProfilePicture(file) {
    const formData = new FormData();
    formData.append('profile_picture', file);

    try {
        const response = await fetch('../BackEnd/profile.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            updateProfilePicture(result.filename);
            showAlert('Profile picture updated successfully', 'success');
        } else {
            showAlert(result.message || 'Error uploading profile picture', 'error');
        }
    } catch (error) {
        console.error('Error uploading profile picture:', error);
        showAlert('Error uploading profile picture', 'error');
    }
}