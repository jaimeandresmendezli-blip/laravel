@php
    $sweetAlertErrors = $errors->all();
@endphp

@if(session('success') || session('error') || count($sweetAlertErrors))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const alerts = [
                @if(session('success'))
                    {
                        icon: 'success',
                        title: '¡Listo!',
                        text: @json(session('success')),
                    },
                @endif
                @if(session('error'))
                    {
                        icon: 'error',
                        title: 'Ocurrió un problema',
                        text: @json(session('error')),
                    },
                @endif
                @if(count($sweetAlertErrors))
                    {
                        icon: 'error',
                        title: 'Revisa los datos',
                        html: @json(implode('<br>', $sweetAlertErrors)),
                    },
                @endif
            ];

            alerts.forEach((alert) => Swal.fire({
                ...alert,
                confirmButtonColor: '#c8900a',
                buttonsStyling: true,
            }));
        });
    </script>
@endif

<script>
    document.addEventListener('submit', (event) => {
        const form = event.target;
        const message = form.dataset.swalConfirm;

        if (!message || form.dataset.swalConfirmed === 'true') {
            return;
        }

        event.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: '¿Estás seguro?',
            text: message,
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#c8900a',
            cancelButtonColor: '#78716c',
        }).then((result) => {
            if (result.isConfirmed) {
                form.dataset.swalConfirmed = 'true';
                form.requestSubmit();
            }
        });
    });
</script>
