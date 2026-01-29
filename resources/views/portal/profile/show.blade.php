<x-portal.layouts.app>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Mi Perfil
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Profile Info -->
            <div class="dark-card p-6 mb-6">
                <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">
                    Informacion Personal
                </h3>

                <form method="POST" action="{{ route('portal.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                               class="dark-input w-full" required maxlength="100">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">
                            Email
                        </label>
                        <input type="email" id="email" value="{{ $user->email }}"
                               class="dark-input w-full bg-slate-100" disabled>
                        <p class="text-slate-500 text-xs mt-1">El email no puede ser modificado</p>
                    </div>

                    <div class="mb-6">
                        <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">
                            Telefono
                        </label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="dark-input w-full" maxlength="50">
                        @error('phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>

            <!-- Account Info -->
            <div class="dark-card p-6 mb-6">
                <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">
                    Informacion de la Cuenta
                </h3>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Cliente</dt>
                        <dd class="text-slate-800 font-medium">{{ $user->customer->name }}</dd>
                    </div>
                    @if($user->customer->company)
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Empresa</dt>
                        <dd class="text-slate-800">{{ $user->customer->company }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Rol</dt>
                        <dd>
                            <span class="badge badge-blue">{{ ucfirst($user->role) }}</span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Cuenta creada</dt>
                        <dd class="text-slate-800">{{ $user->created_at->format('d/m/Y') }}</dd>
                    </div>
                    @if($user->last_login_at)
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Ultimo acceso</dt>
                        <dd class="text-slate-800">{{ $user->last_login_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Security -->
            <div class="dark-card p-6">
                <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">
                    Seguridad
                </h3>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-800 font-medium">Password</p>
                        <p class="text-slate-500 text-sm">Ultima actualizacion: {{ $user->password_changed_at?->format('d/m/Y') ?? 'Nunca' }}</p>
                    </div>
                    <a href="{{ route('portal.profile.password') }}" class="btn-secondary text-sm">
                        Cambiar Password
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-portal.layouts.app>
