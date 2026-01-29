<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('dialing-plans.index') }}" class="text-slate-400 hover:text-slate-600 mr-3 p-1 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Nuevo Dialing Plan</h2>
                <p class="text-sm text-slate-500 mt-0.5">Crea un plan para restringir destinos</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dark-card p-6">
                <form action="{{ route('dialing-plans.store') }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nombre *</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Ej: Solo Nacional, Sin Premium...">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Descripcion</label>
                        <textarea name="description" id="description" rows="2"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Describe que hace este plan...">{{ old('description') }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="default_action" class="block text-sm font-medium text-slate-700 mb-1">Accion por Defecto *</label>
                            <select name="default_action" id="default_action" required
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="allow" {{ old('default_action', 'allow') === 'allow' ? 'selected' : '' }}>ALLOW - Permitir si no hay regla</option>
                                <option value="deny" {{ old('default_action') === 'deny' ? 'selected' : '' }}>DENY - Bloquear si no hay regla</option>
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Que hacer cuando ningun patron coincide</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-3">Opciones</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="hidden" name="block_premium" value="0">
                                    <input type="checkbox" name="block_premium" value="1" {{ old('block_premium', true) ? 'checked' : '' }}
                                        class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-slate-600">Bloquear destinos premium</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="hidden" name="active" value="0">
                                    <input type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}
                                        class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-slate-600">Plan activo</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-sm text-blue-800">Despues de crear el plan, podras añadir reglas ALLOW/DENY para controlar destinos especificos.</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                        <a href="{{ route('dialing-plans.index') }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Crear Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
