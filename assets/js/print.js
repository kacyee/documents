function printLabel(documentId) {
    const button = event.target;
    const originalText = button.textContent;
    
    button.disabled = true;
    button.textContent = 'Otwieranie...';
    
    const barcodeUrl = 'barcode_image.php?id=' + documentId;
    
    // Otwórz kod kreskowy w nowej karcie
    const newWindow = window.open(barcodeUrl, '_blank');
    
    if (newWindow) {
        // Poczekaj na załadowanie obrazu, a następnie wywołaj Ctrl+P
        newWindow.onload = function() {
            setTimeout(() => {
                try {
                    newWindow.print();
                } catch (e) {
                    console.log('Automatyczne drukowanie nie działa, użytkownik musi nacisnąć Ctrl+P');
                }
            }, 500);
        };
        
        showMessage('Kod kreskowy został otwarty. Naciśnij Ctrl+P aby wydrukować.', 'success');
    } else {
        showMessage('Nie można otworzyć kodu kreskowego. Sprawdź blokadę wyskakujących okien.', 'error');
    }
    
    button.disabled = false;
    button.textContent = originalText;
}



function showMessage(message, type) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger');
    messageDiv.textContent = message;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(messageDiv, container.firstChild);
    
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
} 