{{-- Firmas --}}
<div style="margin-bottom: 1.5rem; padding-left: 1rem; padding-right: 1rem;">

    <p style="text-align: justify; padding-top: 0.5rem; padding-bottom: 0.5rem; margin: 0;">
        Quien recibe se compromete a administrar de manera eficiente el recurso entregado y a darle el uso
        adecuado para su conservación y aprovechamiento.
    </p>

    <div style="display: flex; justify-content: center; margin-top: 3rem; gap: 2rem; flex-wrap: wrap;">

        <div style="flex: 1 1 45%; max-width: 45%; text-align: center;">
            <hr style="border-top: 1px solid #000; width: 80%; margin: 0 auto 0.5rem auto;">
            <p style="margin: 0;">Recibe: {{ $nombreCompleto }}</p>
            <p style="margin: 0;">{{ $user->role->nombre }}</p>
            <p style="margin: 0;">CC. {{ number_format($user->userID, 0, ',', '.') }}</p>
        </div>

        <div style="flex: 1 1 45%; max-width: 45%; text-align: center;">
            <hr style="border-top: 1px solid #000; width: 80%; margin: 0 auto 0.5rem auto;">
            <p style="margin: 0;">Entrega: ADOLFO LEÓN RUIZ HERNÁNDEZ</p>
            <p style="margin: 0;">Rector</p>
            <p style="margin: 0;">CC. 71.379.517</p>
        </div>

    </div>

</div>
