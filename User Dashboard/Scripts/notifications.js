class NotificationSystem {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.init();
    }

    init() {
        // Initialize DOM elements
        this.bell = document.querySelector('.notification-bell');
        this.panel = document.getElementById('notification-panel');
        this.list = document.getElementById('notification-list');
        this.badge = document.getElementById('notification-count');
        this.markAllRead = document.getElementById('mark-all-read');

        // Add event listeners
        this.bell.addEventListener('click', () => this.togglePanel());
        this.markAllRead.addEventListener('click', () => this.markAllAsRead());
        
        // Close panel when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.panel.contains(e.target) && !this.bell.contains(e.target)) {
                this.panel.classList.remove('active');
            }
        });

        // Load initial notifications
        this.loadNotifications();
        
        // Check for new notifications periodically
        setInterval(() => this.checkNewNotifications(), 60000); // Every minute
    }

    async loadNotifications() {
        try {
            const response = await fetch('get_notifications.php');
            const data = await response.json();
            this.notifications = data;
            this.updateNotificationCount();
            this.renderNotifications();
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    async checkNewNotifications() {
        try {
            const response = await fetch('check_notifications.php');
            const data = await response.json();
            
            if (data.length > 0) {
                this.addNewNotifications(data);
            }
        } catch (error) {
            console.error('Error checking notifications:', error);
        }
    }

    addNewNotifications(newNotifications) {
        this.notifications.unshift(...newNotifications);
        this.updateNotificationCount();
        this.renderNotifications();
        this.animateNewNotifications();
    }

    togglePanel() {
        this.panel.classList.toggle('active');
    }

    updateNotificationCount() {
        this.unreadCount = this.notifications.filter(n => !n.read).length;
        this.badge.textContent = this.unreadCount;
        this.badge.style.display = this.unreadCount > 0 ? 'block' : 'none';
    }

    renderNotifications() {
        this.list.innerHTML = this.notifications.map(notification => `
            <div class="notification-item ${notification.read ? '' : 'unread'}" 
                 data-id="${notification.id}">
                <i class="${this.getNotificationIcon(notification.type)}"></i>
                <div class="notification-content">
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-time">${this.formatTime(notification.time)}</div>
                </div>
            </div>
        `).join('');

        // Add click handlers
        this.list.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', () => this.markAsRead(item.dataset.id));
        });
    }

    getNotificationIcon(type) {
        const icons = {
            appointment: 'fas fa-calendar-check',
            vaccine: 'fas fa-syringe',
            reminder: 'fas fa-bell',
            alert: 'fas fa-exclamation-circle'
        };
        return icons[type] || 'fas fa-info-circle';
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return 'Just now';
        if (diff < 3600000) return `${Math.floor(diff/60000)}m ago`;
        if (diff < 86400000) return `${Math.floor(diff/3600000)}h ago`;
        return date.toLocaleDateString();
    }

    async markAsRead(id) {
        try {
            await fetch('mark_notification_read.php', {
                method: 'POST',
                body: JSON.stringify({ id })
            });
            
            const notification = this.notifications.find(n => n.id === id);
            if (notification) {
                notification.read = true;
                this.updateNotificationCount();
                this.renderNotifications();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            await fetch('mark_all_notifications_read.php', {
                method: 'POST'
            });
            
            this.notifications.forEach(n => n.read = true);
            this.updateNotificationCount();
            this.renderNotifications();
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    animateNewNotifications() {
        this.badge.classList.add('new');
        setTimeout(() => this.badge.classList.remove('new'), 1000);
    }
}

// Initialize notification system
document.addEventListener('DOMContentLoaded', () => {
    window.notificationSystem = new NotificationSystem();
}); 