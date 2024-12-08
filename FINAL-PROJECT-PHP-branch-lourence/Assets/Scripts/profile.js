document.addEventListener('DOMContentLoaded', async function() {
    try {
        const response = await fetch('../BackEnd/Main.php?action=get_profile', {
            method: 'GET',
            credentials: 'include'
        });
        const data = await response.json();
        
        console.log('Profile data:', data);

        if (data.success && data.userData) {
            // Map the fields to form inputs
            const fields = {
                'fname': 'firstName',
                'mname': 'middleName',
                'lname': 'lastName',
                'mail': 'email',
                'contact': 'contactNumber',
                'birthday': 'birthday',
                'sex': 'sex',
                'civilstat': 'civilStatus',
                'address': 'address',
                'nationality': 'nationality',
                'employmentstat': 'employmentStatus',
                'employer': 'employer',
                'profession': 'profession',
                'barangay': 'barangay',
                'allergy_description': 'allergyDescription',
                'disease_description': 'diseaseDescription',
                'allergy_check': 'allergyCheck',
                'disease_check': 'diseaseCheck'
            };

            // Fill in the form fields
            Object.entries(data.userData).forEach(([key, value]) => {
                const fieldId = fields[key];
                if (fieldId) {
                    const input = document.getElementById(fieldId);
                    if (input) {
                        if (input.type === 'checkbox') {
                            input.checked = value === '1' || value === true;
                        } else {
                            input.value = value || '';
                        }
                    }
                }
            });
        } else {
            console.error('Failed to load profile:', data.message);
        }
    } catch (error) {
        console.error('Error loading profile:', error);
    }
});

// Handle form submission
const profileForm = document.getElementById('profile-form');
if (profileForm) {
    profileForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            // Convert form data to match backend field names
            const formData = new FormData(profileForm);
            const data = {
                action: 'update_profile',
                fname: formData.get('firstName'),
                mname: formData.get('middleName'),
                lname: formData.get('lastName'),
                mail: formData.get('email'),
                contact: formData.get('contactNumber'),
                birthday: formData.get('birthday'),
                sex: formData.get('sex'),
                civilstat: formData.get('civilStatus'),
                address: formData.get('address'),
                nationality: formData.get('nationality'),
                employmentstat: formData.get('employmentStatus'),
                employer: formData.get('employer'),
                profession: formData.get('profession'),
                barangay: formData.get('barangay'),
                allergy_description: formData.get('allergyDescription'),
                disease_description: formData.get('diseaseDescription'),
                allergy_check: formData.get('allergyCheck') ? '1' : '0',
                disease_check: formData.get('diseaseCheck') ? '1' : '0'
            };

            const response = await fetch('../BackEnd/Main.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
                credentials: 'include'
            });

            const result = await response.json();
            
            if (result.success) {
                alert('Profile updated successfully');
                window.location.reload();
            } else {
                alert(result.message || 'Failed to update profile');
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            alert('Failed to update profile');
        }
    });
}
