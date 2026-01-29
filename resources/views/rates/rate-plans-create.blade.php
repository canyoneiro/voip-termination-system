<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Nuevo Plan de Tarifas
            </h2>
            <a href="{{ route('rates.rate-plans') }}" class="btn-secondary text-sm">Cancelar</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dark-card p-6">
                <form method="POST" action="{{ route('rates.rate-plans.store') }}">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nombre del plan *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="input-field w-full" placeholder="Plan Standard">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Descripcion</label>
                            <textarea name="description" id="description" rows="3"
                                      class="input-field w-full" placeholder="Descripcion del plan...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="default_markup_percent" class="block text-sm font-medium text-slate-700 mb-1">Markup por defecto (%) *</label>
                            <div class="relative w-32">
                                <input type="number" name="default_markup_percent" id="default_markup_percent"
                                       value="{{ old('default_markup_percent', '20') }}" required
                                       step="0.01" min="0" max="500"
                                       class="input-field w-full pr-8">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">%</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Porcentaje a aplicar sobre el coste del carrier</p>
                            @error('default_markup_percent')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-slate-700">Plan activo</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <a href="{{ route('rates.rate-plans') }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Crear Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
