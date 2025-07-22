function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });

    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

function printDocument(documentId) {
    const button = event.target;
    const originalText = button.textContent;
    
    button.disabled = true;
    button.textContent = 'Drukowanie...';
    
    fetch('print_document.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'document_id=' + documentId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Kod kreskowy został wysłany do drukarki!');
        } else {
            alert('Błąd: ' + data.message);
        }
    })
    .catch(error => {
        alert('Błąd połączenia: ' + error.message);
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = originalText;
    });
}

function updateLocationOptions() {
    const caseType = document.getElementById('case_type').value;
    const locationSelect = document.getElementById('location_id');
    locationSelect.innerHTML = '<option value="">Wybierz miejsce</option>';

    if (caseType === 'civil') {
        civilLocations.forEach(location => {
            const option = document.createElement('option');
            option.value = location.id;
            option.textContent = location.location_code;
            locationSelect.appendChild(option);
        });
    } else if (caseType === 'criminal') {
        criminalLocations.forEach(location => {
            const option = document.createElement('option');
            option.value = location.id;
            option.textContent = location.location_code;
            locationSelect.appendChild(option);
        });
    }
} 