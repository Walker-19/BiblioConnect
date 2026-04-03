import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['container'];

    connect() {
        // Affiche les flash messages au chargement
        this.showFlashMessages();
    }

    showFlashMessages() {
        const flashes = document.getElementById('flashes');
        if (!flashes) return;

        const alerts = flashes.querySelectorAll('[role="alert"]');
        alerts.forEach((alert) => {
            const type = alert.dataset.type || 'info';
            const message = alert.textContent.trim();
            this.show(message, type);
            alert.remove();
        });
    }

    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            ${this.getIcon(type)}
            <span>${message}</span>
            <button class="notification-close" type="button" data-action="notification#close">✕</button>
        `;

        this.element.appendChild(notification);

        // Auto-close après la durée spécifiée
        if (duration > 0) {
            setTimeout(() => this.close(notification), duration);
        }
    }

    close(notification = event.currentTarget.closest('.notification')) {
        if (!notification) return;
        notification.classList.add('removing');
        setTimeout(() => notification.remove(), 300);
    }

    getIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return `<span style="font-size: 18px; font-weight: bold;">${icons[type] || icons.info}</span>`;
    }
}
