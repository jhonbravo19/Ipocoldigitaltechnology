function verifyCertificate(seriesNumber) {
    const modal = new bootstrap.Modal(document.getElementById('verificationModal'));
    const content = document.getElementById('verificationContent');

    content.innerHTML = `
        <i class="fas fa-spinner fa-spin text-primary fa-3x mb-3"></i>
        <p>Verificando certificado...</p>
    `;
    modal.show();

    fetch(`/api/certificates/verify`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ series_number: seriesNumber })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.valid) {
                content.innerHTML = `
                <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                <p class="text-success">${data.message}</p>
            `;
            } else {
                content.innerHTML = `
                <i class="fas fa-times-circle text-danger fa-3x mb-3"></i>
                <p class="text-danger">${data.message || 'Certificado no válido'}</p>
            `;
            }
        })
        .catch(() => {
            content.innerHTML = `
            <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
            <p class="text-warning">Error al verificar el certificado.</p>
        `;
        });
}
