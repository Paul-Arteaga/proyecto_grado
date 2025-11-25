let esEdicion = false;

function obtenerFormulario() {
    return document.getElementById('formAccesorio');
}

function asegurarMetodoFormulario(metodoSpoof) {
    const methodInput = document.getElementById('method_field');
    if (!methodInput) return;
    methodInput.value = metodoSpoof ?? '';
}

function abrirModalCrear() {
    esEdicion = false;
    document.getElementById('modalTitulo').textContent = 'Nuevo Accesorio';
    const form = obtenerFormulario();
    form.action = window.accesorioStoreUrl;
    form.method = 'POST';
    asegurarMetodoFormulario(null);
    document.getElementById('accesorio_id').value = '';
    form.reset();
    document.getElementById('imagenPreview').innerHTML = '';
    document.getElementById('activo').checked = true;
    asegurarMetodoFormulario('');
    document.getElementById('modalAccesorio').classList.remove('hidden');
    document.getElementById('modalAccesorio').classList.add('flex');
}

function abrirModalEditar(id, nombre, descripcion, precio, stock, activo, imagenUrl) {
    esEdicion = true;
    document.getElementById('modalTitulo').textContent = 'Editar Accesorio';
    const form = obtenerFormulario();
    form.action = window.accesorioUpdateUrl.replace(':id', id);
    form.method = 'POST';
    asegurarMetodoFormulario('PATCH');
    
    document.getElementById('accesorio_id').value = id;
    document.getElementById('nombre').value = nombre;
    document.getElementById('descripcion').value = descripcion || '';
    document.getElementById('precio').value = precio;
    document.getElementById('stock').value = stock || '';
    document.getElementById('activo').checked = activo;
    
    if (imagenUrl) {
        document.getElementById('imagenPreview').innerHTML = `<img src="${imagenUrl}" alt="Preview" class="w-32 h-32 object-cover rounded mt-2">`;
    } else {
        document.getElementById('imagenPreview').innerHTML = '';
    }
    
    document.getElementById('modalAccesorio').classList.remove('hidden');
    document.getElementById('modalAccesorio').classList.add('flex');
}

function cerrarModal() {
    document.getElementById('modalAccesorio').classList.add('hidden');
    document.getElementById('modalAccesorio').classList.remove('flex');
    // Recargar formulario para limpiar campos
    if (esEdicion) {
        location.reload();
    }
}

// Preview de imagen
document.addEventListener('DOMContentLoaded', function() {
    const imagenInput = document.getElementById('imagen');
    if (imagenInput) {
        imagenInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagenPreview').innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-32 h-32 object-cover rounded mt-2">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});


