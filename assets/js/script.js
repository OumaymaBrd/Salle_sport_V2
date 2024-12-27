document.addEventListener('DOMContentLoaded', function() {
    var alertMessage = document.querySelector('[role="alert"]');
    if (alertMessage) {
        setTimeout(function() {
            alertMessage.style.opacity = '0';
            alertMessage.style.transition = 'opacity 0.5s ease-in-out';
            setTimeout(function() {
                alertMessage.style.display = 'none';
            }, 500);
        }, 5000);
    }
});