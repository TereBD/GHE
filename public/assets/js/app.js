document.addEventListener('DOMContentLoaded', () => {
    // Confirmación antes de borrar
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', e => {
            if (!confirm(form.dataset.confirm || '¿Estás seguro de eliminar este registro?')) {
                e.preventDefault();
            }
        });
    });

    // Validación de formularios
    document.querySelectorAll('form.validate').forEach(form => {
        form.addEventListener('submit', e => {
            let valid = true;
            form.querySelectorAll('[required]').forEach(input => {
                const error = input.parentElement.querySelector('.error-text');
                if (!input.value.trim()) {
                    valid = false;
                    input.style.borderColor = '#d32f2f';
                    if (error) error.textContent = 'Este campo es obligatorio';
                } else {
                    input.style.borderColor = '#ccc';
                    if (error) error.textContent = '';
                }
            });
            if (!valid) e.preventDefault();
        });
    });

    // Resaltar fila al pasar el ratón en tablas
    document.querySelectorAll('table tbody tr').forEach(row => {
        row.addEventListener('mouseenter', () => row.style.background = '#f5f5f5');
        row.addEventListener('mouseleave', () => row.style.background = '');
    });
});
