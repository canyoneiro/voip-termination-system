<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('dialing-plans.show', $dialingPlan) }}" class="text-slate-400 hover:text-slate-600 mr-3 p-1 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Editar: {{ $dialingPlan->name }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">Modifica la configuracion del plan</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dark-card p-6">
                <form action="{{ route('dialing-plans.update', $dialingPlan) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nombre *</label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $dialingPlan->name) }}"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Descripcion</label>
                        <textarea name="description" id="description" rows="2"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $dialingPlan->description) }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="default_action" class="block text-sm font-medium text-slate-700 mb-1">Accion por Defecto *</label>
                            <select name="default_action" id="default_action" required
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="allow" {{ old('default_action', $dialingPlan->default_action) === 'allow' ? 'selected' : '' }}>ALLOW - Permitir si no hay regla</option>
                                <option value="deny" {{ old('default_action', $dialingPlan->default_action) === 'deny' ? 'selected' : '' }}>DENY - Bloquear si no hay regla</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-3">Opciones</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="hidden" name="block_premium" value="0">
                                    <input type="checkbox" name="block_premium" value="1" {{ old('block_premium', $dialingPlan->block_premium) ? 'checked' : '' }}
                                        class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-slate-600">Bloquear destinos premium</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="hidden" name="active" value="0">
                                    <input type="checkbox" name="active" value="1" {{ old('active', $dialingPlan->active) ? 'checked' : '' }}
                                        class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-slate-600">Plan activo</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    @if($dialingPlan->customers()->exists())
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <p class="text-sm text-yellow-800">Este plan esta asignado a <strong>{{ $dialingPlan->customers()->count() }}</strong> cliente(s). Los cambios se aplicaran inmediatamente.</p>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-between pt-4 border-t border-slate-200">
                        <div>
                            @if(!$dialingPlan->customers()->exists())
                                <button type="button" onclick="document.getElementById('deleteModal').classList.remove('hidden')" class="btn-danger">
                                    Eliminar Plan
                                </button>
                            @endif
                        </div>
                        <div class="flex gap-3">
                            <a href="{{ route('dialing-plans.show', $dialingPlan) }}" class="btn-secondary">Cancelar</a>
                            <button type="submit" class="btn-primary">Guardar Cambios</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-bold text-slate-800 mb-2">Eliminar Dialing Plan</h3>
            <p class="text-slate-600 mb-4">¿Seguro que quieres eliminar <strong>{{ $dialingPlan->name }}</strong>? Esta accion no se puede deshacer.</p>
            <div class="flex justify-end gap-3">
                <button onclick="document.getElementById('deleteModal').classList.add('hidden')" class="btn-secondary">Cancelar</button>
                <form action="{{ route('dialing-plans.destroy', $dialingPlan) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
