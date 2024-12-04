document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
});

async function loadSettings() {
    try {
        const response = await fetch('../BackEnd/settings.php?action=get_settings');
        const settings = await response.json();
        
        // Load notification settings
        document.getElementById('emailNotifications').checked = settings.emailNotifications;
        document.getElementById('smsNotifications').checked = settings.smsNotifications;
        document.getElementById('reminderFrequency').value = settings.reminderFrequency;
        
        // Load appearance settings
        document.getElementById('language').value = settings.language;
        document.getElementById('theme').value = settings.theme;
        document.getElementById('timezone').value = settings.timezone;
        
        // Load privacy settings
        document.getElementById('profileVisibility').value = settings.profileVisibility;
        document.getElementById('recordsVisibility').value = settings.recordsVisibility;
        
        // Load security settings
        document.getElementById('twoFactorAuth').checked = settings.twoFactorAuth;
        
    } catch (error) {
        console.error('Error loading settings:', error);
        showAlert('Error loading settings', 'error');
    }
}

async function saveNotificationSettings() {
    const settings = {
        emailNotifications: document.getElementById('emailNotifications').checked,
        smsNotifications: document.getElementById('smsNotifications').checked,
        reminderFrequency: document.getElementById('reminderFrequency').value
    };
    await saveSettings('notifications', settings);
}

async function saveAppearanceSettings() {
    const settings = {
        language: document.getElementById('language').value,
        theme: document.getElementById('theme').value,
        timezone: document.getElementById('timezone').value
    };
    await saveSettings('appearance', settings);
}

async function savePrivacySettings() {
    const settings = {
        profileVisibility: document.getElementById('profileVisibility').value,
        recordsVisibility: document.getElementById('recordsVisibility').value
    };
    await saveSettings('privacy', settings);
}

async function saveSecuritySettings() {
    const settings = {
        twoFactorAuth: document.getElementById('twoFactorAuth').checked
    };
    await saveSettings('security', settings);
}

async function saveSettings(type, settings) {
    try {
        const response = await fetch('../BackEnd/settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: `save_${type}`,
                settings: settings
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showAlert(`${type.charAt(0).toUpperCase() + type.slice(1)} settings saved successfully`, 'success');
        } else {
            showAlert(result.message || `Error saving ${type} settings`, 'error');
        }
    } catch (error) {
        console.error(`Error saving ${type} settings:`, error);
        showAlert(`Error saving ${type} settings`, 'error');
    }
}

async function exportData() {
    try {
        const response = await fetch('../BackEnd/settings.php?action=export_data');
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'shotsafe_data.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Error exporting data:', error);
        showAlert('Error exporting data', 'error');
    }
}

function showAlert(message, type) {
    // Implement your alert/notification system here
    alert(message);
}
