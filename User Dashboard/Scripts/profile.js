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