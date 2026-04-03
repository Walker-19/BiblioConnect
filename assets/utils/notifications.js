/**
 * Helper utilitaire pour afficher des notifications
 * Utilisable depuis n'importe où dans l'application
 * 
 * Exemples:
 * showNotification('Opération réussie!', 'success')
 * showNotification('Une erreur s\'est produite', 'error')
 * showNotification('Attention!', 'warning')
 * showNotification('Information utile', 'info')
 */
export function showNotification(message, type = 'info', duration = 5000) {
    const container = document.querySelector('[data-controller="notification"]');
    if (!container) {
        console.warn('Notification container not found');
        return;
    }

    const controller = Stimulus.application.getControllerForElementAndIdentifier(
        container,
        'notification'
    );

    if (!controller) {
        console.warn('Notification controller not found');
        return;
    }

    controller.show(message, type, duration);
}

/**
 * Affiche une notification de succès
 */
export function showSuccess(message, duration = 5000) {
    showNotification(message, 'success', duration);
}

/**
 * Affiche une notification d'erreur
 */
export function showError(message, duration = 5000) {
    showNotification(message, 'error', duration);
}

/**
 * Affiche une notification d'avertissement
 */
export function showWarning(message, duration = 5000) {
    showNotification(message, 'warning', duration);
}

/**
 * Affiche une notification d'information
 */
export function showInfo(message, duration = 5000) {
    showNotification(message, 'info', duration);
}
