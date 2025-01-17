// resources/js/notification.js
window.notificationSystem = function() {
    return {
        isOpen: false,
        notifications: [],
        unreadCount: 0,

        init() {
            this.fetchUnreadCount();
            this.fetchNotifications();
            
            setInterval(() => {
                this.fetchUnreadCount();
            }, 30000);
        },

        toggleNotifications() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.fetchNotifications();
            }
        },

        async fetchUnreadCount() {
            try {
                const response = await fetch('/notifications/unread-count');
                const data = await response.json();
                this.unreadCount = data.count;
            } catch (error) {
                console.error('Error fetching unread count:', error);
            }
        },

        async fetchNotifications() {
            try {
                const response = await fetch('/notifications');
                const data = await response.json();
                this.notifications = data.notifications;
            } catch (error) {
                console.error('Error fetching notifications:', error);
            }
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}