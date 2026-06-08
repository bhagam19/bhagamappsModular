{{-- Texto inicial --}}
<div style="margin-top: 0.5rem; margin-bottom: 0.5rem;">

    <p style="text-align: left; padding-bottom: 0.25rem; margin: 0;">
        Entrerríos, {{ $miFecha }}
    </p>

    <p style="text-align: justify; padding-bottom: 0.25rem; margin: 0;">
        El Rector de la IE Entrerríos hace entrega del siguiente inventario al docente
        <span style="font-weight: bold;">{{ $nombreCompleto }}</span>, identificado con
        <span style="font-weight: bold;">CC. {{ number_format($user->userID, 0, ',', '.') }}</span>.
    </p>
</div>
