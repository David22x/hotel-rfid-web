// Abre en un modal los enlaces con [data-modal] (crear, editar, cambiar estado).
// El servidor devuelve solo el formulario cuando recibe la cabecera X-Requested-With: fetch.
(function () {
    const modal = document.getElementById('modal');
    const cuerpo = document.getElementById('modal-cuerpo');
    const cabecera = { 'X-Requested-With': 'fetch' };
    let urlActual = ''; // URL del formulario abierto (crear/editar/cambiarEstado)

    function mostrar(html) {
        cuerpo.innerHTML = html;
        const primero = cuerpo.querySelector('input:not([type=hidden]), select');
        if (primero) primero.focus();
    }

    async function abrir(url) {
        urlActual = url;
        try {
            const res = await fetch(url, { headers: cabecera });
            mostrar(await res.text());
            if (!modal.open) modal.showModal();
        } catch (e) {
            window.location.href = url; // sin modal: página completa
        }
    }

    document.addEventListener('click', (e) => {
        const enlace = e.target.closest('a[data-modal]');
        if (enlace) {
            e.preventDefault();
            abrir(enlace.href);
            return;
        }
        if (modal.open && e.target.closest('[data-cerrar]')) {
            e.preventDefault();
            modal.close();
        } else if (e.target === modal) { // clic en el fondo
            modal.close();
        }
    });

    cuerpo.addEventListener('submit', async (e) => {
        const form = e.target.closest('form[data-modal-form]');
        if (!form) return;
        e.preventDefault();
        // redirect: 'manual' evita seguir la redirección, así el aviso de la sesión
        // sigue intacto y lo muestra el listado al recargar.
        const res = await fetch(urlActual, {
            method: 'POST',
            headers: cabecera,
            body: new FormData(form),
            redirect: 'manual',
        });
        if (res.type === 'opaqueredirect') {
            window.location.reload();
        } else {
            mostrar(await res.text()); // formulario con el error
        }
    });
})();
