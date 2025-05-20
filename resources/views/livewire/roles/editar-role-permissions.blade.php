<div class="container mt-3" style="max-width: 600px;">
    <h2 class="mb-3 text-primary font-weight-bold text-center">
        Permisos para el rol: <span class="text-dark">{{ $role->nombre }}</span>
    </h2>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar" style="line-height:1;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form wire:submit.prevent="save">
        <div class="accordion" id="permissionsAccordion">
            @foreach ($groupedPermissions as $category => $permissions)
                <div class="card mb-2 shadow-sm">
                    <div 
                        class="card-header bg-dark text-light p-2" 
                        id="heading-{{ Str::slug($category) }}"
                        style="cursor: pointer;"
                        data-toggle="collapse" 
                        data-target="#collapse-{{ Str::slug($category) }}" 
                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}" 
                        aria-controls="collapse-{{ Str::slug($category) }}"
                    >
                        <h5 class="mb-0 d-flex justify-content-between align-items-center" style="font-weight:700; font-size:1rem;">
                            {{ $category }}
                            <span class="badge badge-light badge-pill">{{ count($permissions) }}</span>
                        </h5>
                    </div>

                    <div 
                        id="collapse-{{ Str::slug($category) }}" 
                        class="collapse @if($loop->first) show @endif" 
                        aria-labelledby="heading-{{ Str::slug($category) }}" 
                        data-parent="#permissionsAccordion"
                    >
                        <div class="card-body p-2">
                            <div class="row no-gutters">
                                @foreach ($permissions as $permission)
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <div class="custom-control custom-checkbox py-1">
                                            <input 
                                                type="checkbox" 
                                                class="custom-control-input" 
                                                id="perm-{{ $permission->id }}" 
                                                wire:model="selectedPermissions" 
                                                value="{{ $permission->id }}">
                                            <label class="custom-control-label" for="perm-{{ $permission->id }}" style="font-size: 0.9rem;">
                                                {{ $permission->nombre }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <button 
            type="submit" 
            class="btn btn-primary btn-block mt-3 py-2" 
            style="font-weight: 600; font-size: 1.1rem;">
            Guardar cambios
        </button>
    </form>
</div>
