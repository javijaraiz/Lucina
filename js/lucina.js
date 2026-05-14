/**
 * Lucina - Comparador Energético (v1.0 Entrega DAW)
 * Archivo: lucina.js
 * Descripción: Funciones JavaScript para la interactividad del front-end,
 * incluyendo validaciones, animaciones y gestión de subidas de archivos.
 */

/**
 * Muestra una notificación visual en la parte superior derecha de la pantalla.
 * @param {string} mensaje - El texto a mostrar.
 * @param {string} tipo - Clase de Bootstrap (success, danger, info, etc).
 */
function mostrarNotificacion(mensaje, tipo = 'success') {
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} position-fixed top-0 end-0 m-3 shadow-sm`;
    alerta.style.zIndex = '9999';
    alerta.innerHTML = `
        <div class="d-flex align-items-center">
            <span class="me-2">${tipo === 'success' ? '✓' : '⚠'}</span>
            <span>${mensaje}</span>
            <button type="button" class="btn-close ms-3" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(alerta);
    
    // Desvanecer y eliminar automáticamente tras 4 segundos
    setTimeout(() => {
        alerta.classList.add('fade');
        setTimeout(() => alerta.remove(), 500);
    }, 4000);
}

/**
 * Formatea un número como moneda local (Euro).
 * @param {number} valor - El número a formatear.
 */
function formatearEuros(valor) {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(valor);
}

/**
 * Valida si una cadena de texto tiene formato de email correcto.
 * @param {string} correo - El email a validar.
 */
function validarEmail(correo) {
    const patron = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return patron.test(correo);
}

/**
 * Configura una zona de 'Drag & Drop' para la subida de archivos PDF/Imágenes.
 * @param {string} idElemento - ID del contenedor que actuará como zona de soltado.
 * @param {function} accionAlSubir - Función que se ejecutará al recibir un archivo.
 */
function inicializarZonaSubida(idElemento, accionAlSubir) {
    const contenedor = document.getElementById(idElemento);
    
    if (!contenedor) return;
    
    // Prevenimos que el navegador abra el archivo al soltarlo
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(nombreEvento => {
        contenedor.addEventListener(nombreEvento, (e) => {
            e.preventDefault();
            e.stopPropagation();
        }, false);
    });
    
    // Efectos visuales al arrastrar sobre la zona
    contenedor.addEventListener('dragover', () => contenedor.classList.add('dragover'));
    contenedor.addEventListener('dragleave', () => contenedor.classList.remove('dragover'));
    contenedor.addEventListener('drop', (e) => {
        contenedor.classList.remove('dragover');
        const ficheros = e.dataTransfer.files;
        if (ficheros.length > 0) {
            accionAlSubir(ficheros[0]);
        }
    });
    
    // También permitimos seleccionar el archivo haciendo clic en la zona
    contenedor.addEventListener('click', () => {
        const inputOculto = document.createElement('input');
        inputOculto.type = 'file';
        inputOculto.accept = '.pdf,.jpg,.jpeg,.png';
        inputOculto.onchange = (e) => {
            if (e.target.files.length > 0) {
                accionAlSubir(e.target.files[0]);
            }
        };
        inputOculto.click();
    });
}

/**
 * Realiza la subida asíncrona del archivo al servidor.
 * @param {File} archivo - El archivo físico.
 * @param {string} endpoint - URL del script de procesamiento (analizar.php).
 */
async function procesarSubidaArchivo(archivo, endpoint) {
    const datosEnvio = new FormData();
    datosEnvio.append('factura', archivo);
    
    // Añadimos datos de contacto si están disponibles en el formulario
    const nombre = document.querySelector('input[name="nombre"]')?.value;
    const email = document.querySelector('input[name="email"]')?.value;
    const telefono = document.querySelector('input[name="telefono"]')?.value;

    if (nombre) datosEnvio.append('nombre', nombre);
    if (email) datosEnvio.append('email', email);
    if (telefono) datosEnvio.append('telefono', telefono);

    try {
        const respuesta = await fetch(endpoint, {
            method: 'POST',
            body: datosEnvio
        });
        
        if (!respuesta.ok) throw new Error('Error en la comunicación con el servidor.');
        
        return await respuesta.json();
    } catch (error) {
        console.error('Detalle del error:', error);
        mostrarNotificacion('No se pudo procesar el archivo. Revisa tu conexión.', 'danger');
        return null;
    }
}

/**
 * Suma los consumos de los periodos P1, P2 y P3 y actualiza el total en pantalla.
 */
function actualizarSumaConsumos() {
    const valorP1 = parseFloat(document.getElementById('consumo_p1')?.value) || 0;
    const valorP2 = parseFloat(document.getElementById('consumo_p2')?.value) || 0;
    const valorP3 = parseFloat(document.getElementById('consumo_p3')?.value) || 0;
    
    const total = valorP1 + valorP2 + valorP3;
    
    const campoTotal = document.getElementById('total_consumo');
    if (campoTotal) {
        campoTotal.value = total.toFixed(2);
    }
    
    return total;
}

// Inicialización de escuchadores de eventos al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    // Vincular recálculo automático en campos de consumo
    const camposId = ['consumo_p1', 'consumo_p2', 'consumo_p3'];
    camposId.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', actualizarSumaConsumos);
    });
    
    // Cálculo inicial
    actualizarSumaConsumos();
});

/**
 * Realiza una animación de conteo suave para valores numéricos.
 * @param {HTMLElement} elemento - El nodo del DOM.
 * @param {number} meta - El valor final.
 */
function animarContador(elemento, meta, tiempo = 800) {
    let inicio = 0;
    const paso = meta / (tiempo / 16); 
    
    const crono = setInterval(() => {
        inicio += paso;
        if (inicio >= meta) {
            elemento.textContent = meta.toFixed(2);
            clearInterval(crono);
        } else {
            elemento.textContent = inicio.toFixed(2);
        }
    }, 16);
}
