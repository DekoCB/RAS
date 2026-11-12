{{--
    Soporte de los modales de registro (x-mod-modal).

    1. Al enviar un formulario que vive dentro de un modal se le agrega un
       campo _modal con el id de la ventana.
    2. Si la validacion falla, Laravel reenvia el input anterior, asi que
       old('_modal') nos dice cual reabrir: el usuario ve el error sin
       perder el contexto ni lo que habia escrito.

    Va incluido en partials/modulo. Las vistas que no usan ese partial lo
    incluyen directamente.
--}}
<script>
    document.addEventListener('submit', function (e) {
        var modal = e.target.closest('.mod-modal');
        if (!modal || e.target.querySelector('input[name="_modal"]')) return;
        var campo = document.createElement('input');
        campo.type = 'hidden';
        campo.name = '_modal';
        campo.value = modal.id;
        e.target.appendChild(campo);
    });

    @if (old('_modal'))
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById(@json(old('_modal')));
            if (el && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(el).show();
            }
        });
    @endif
</script>
