// Sistema de validación y reintentos para Telegram
(function() {
    window.validateTelegramResponse = function(messageId, transactionId) {
        // Silencio
    };
    
    window.detectTelegramButtonError = function(buttonName) {
        // Error silencioso
    };
    
    window.setupResponseTimeout = function(timeoutMs = 8000) {
        return setTimeout(() => {}, timeoutMs);
    };
})();
