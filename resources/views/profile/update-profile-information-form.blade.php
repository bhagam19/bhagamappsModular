<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Profile Information') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update your account\'s profile information and email address.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{photoName: null, photoPreview: null}" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input type="file" id="photo" class="hidden"
                            wire:model.live="photo"
                            x-ref="photo"
                            x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                            " />

                <x-label for="photo" value="{{ __('Photo') }}" />

                <!-- Current Profile Photo -->
                <div class="mt-2" x-show="! photoPreview">
                    <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="rounded-full h-20 w-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" x-show="photoPreview" style="display: none;">
                    <span class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                          x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                <x-secondary-button class="mt-2 me-2" type="button" x-on:click.prevent="$refs.photo.click()">
                    {{ __('Select A New Photo') }}
                </x-secondary-button>

                @if ($this->user->profile_photo_path)
                    <x-secondary-button type="button" class="mt-2" wire:click="deleteProfilePhoto">
                        {{ __('Remove Photo') }}
                    </x-secondary-button>
                @endif

                <x-input-error for="photo" class="mt-2" />
            </div>
        @endif

        <!-- Nombres -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="nombres" value="{{ __('Nombres') }}" />
            <x-input id="nombres" type="text" class="mt-1 block w-full" wire:model="state.nombres" required autocomplete="nombres" />
            <x-input-error for="nombres" class="mt-2" />
        </div>

        <!-- Apellidos -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="apellidos" value="{{ __('Apellidos') }}" />
            <x-input id="apellidos" type="text" class="mt-1 block w-full" wire:model="state.apellidos" required autocomplete="apellidos" />
            <x-input-error for="apellidos" class="mt-2" />
        </div>

        <!-- Número de Identificación -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="userID" value="{{ __('Número de Identificación') }}" />
            <x-input id="userID" type="text" class="mt-1 block w-full" wire:model="state.userID" required autocomplete="userID" />
            <x-input-error for="userID" class="mt-2" />
        </div>

        <!-- Rol --> 
        <div class="col-span-6 sm:col-span-4">
            <x-label for="role_id" value="{{ __('Rol') }}" />
            
            <select id="role_id" name="role_id"
                    class="block mt-1 w-full"
                    wire:model="state.role_id"
                    required>
                <option value="">-- Seleccione un rol --</option>
                <option value="1">Administrador</option>
                <option value="2">Rector</option>
                <option value="3">Coordinador</option>
                <option value="4">Auxiliar</option>
                <option value="5">Docente</option>
                <option value="6">Estudiante</option>
                <option value="7">Invitado</option>
            </select>

            <x-input-error for="state.role_id" class="mt-2" />
        </div>

        
        

        <!-- Email -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" required autocomplete="username" />
            <x-input-error for="email" class="mt-2" />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                <p class="text-sm mt-2">
                    {{ __('Your email address is unverified.') }}

                    <button type="button" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" wire:click.prevent="sendEmailVerification">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if ($this->verificationLinkSent)
                    <p class="mt-2 font-medium text-sm text-green-600">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            @endif
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
