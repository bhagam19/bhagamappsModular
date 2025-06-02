{{-- Firmas --}}
<div class="firmas mb-4 px-3">

    <p class="text-justify pb-2 pt-2">
        Quien recibe se compromete a administrar de manera eficiente el recurso entregado y a darle el uso
        adecuado para su conservación y aprovechamiento.
    </p>

    <div class="row text-center mt-5">
        <div class="col-md-6">
            <hr style="border-top: 1px solid #000; width: 80%; margin: auto;">
            <p class="mb-0">Recibe: {{ $nombreCompleto }}</p>
            <p class="mb-0">{{ $user->role->nombre }}</p>
            <p>CC. {{ number_format($user->userID, 0, ',', '.') }}</p>
        </div>
        <div class="col-md-6">
            <hr style="border-top: 1px solid #000; width: 80%; margin: auto;">
            <p class="mb-0">Entrega: ADOLFO LEÓN RUIZ HERNÁNDEZ</p>
            <p class="mb-0">Rector</p>
            <p>CC. 71.379.517</p>
        </div>
    </div>

</div>
