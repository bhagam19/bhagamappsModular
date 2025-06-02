{{-- Texto inicial --}}
<div class="texto-inicial mb-2 mt-2">

    <p class="text-left pb-1">
        Entrerríos, {{ $miFecha }}
    </p>

    <p class="text-justify pb-1">
        El Rector de la IE Entrerríos hace entrega del siguiente inventario al docente
        <span class="font-weight-bold">{{ $nombreCompleto }}</span>, identificado con <span class="font-weight-bold">CC.
            {{ number_format($user->userID, 0, ',', '.') }}</span>.
    </p>
</div>
