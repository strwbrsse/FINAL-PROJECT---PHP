// Session management utility
class SessionManager {
    static async checkSession() {
        try {
            const response = await fetch('../BackEnd/check_session.php');
            const data = await response.json();
            
            if (!data.loggedIn) {
                window.location.href = data.redirect;
                return null;
            }
            
            return data.name_id;
        } catch (error) {
            console.error('Session check failed:', error);
            window.location.href = '../index.html';
            return null;
        }
    }

    static async getNameId() {
        const nameId = await this.checkSession();
        if (!nameId) return null;
        
        // Store in sessionStorage for quick access
        sessionStorage.setItem('name_id', nameId);
        return nameId;
    }

    static getStoredNameId() {
        return sessionStorage.getItem('name_id');
    }
}

// Initialize session check when page loads
document.addEventListener('DOMContentLoaded', async () => {
    await SessionManager.getNameId();
}); 