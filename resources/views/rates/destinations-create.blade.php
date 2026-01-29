<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Nuevo Destino
            </h2>
            <a href="{{ route('rates.destinations') }}" class="btn-secondary text-sm">Cancelar</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dark-card p-6">
                <form method="POST" action="{{ route('rates.destinations.store') }}">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label for="prefix" class="block text-sm font-medium text-slate-700 mb-1">Prefijo *</label>
                            <input type="text" name="prefix" id="prefix" value="{{ old('prefix') }}" required
                                   class="input-field w-full font-mono" placeholder="34, 346, 3460...">
                            @error('prefix')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-slate-500">Prefijo internacional sin el signo +</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="country" class="block text-sm font-medium text-slate-700 mb-1">Pais</label>
                                <input type="text" name="country" id="country" value="{{ old('country') }}"
                                       class="input-field w-full" placeholder="Espana">
                                @error('country')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="region" class="block text-sm font-medium text-slate-700 mb-1">Region</label>
                                <input type="text" name="region" id="region" value="{{ old('region') }}"
                                       class="input-field w-full" placeholder="Madrid">
                                @error('region')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Descripcion</label>
                            <input type="text" name="description" id="description" value="{{ old('description') }}"
                                   class="input-field w-full" placeholder="Espana Movil Movistar">
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_mobile" value="1" {{ old('is_mobile') ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-slate-700">Es movil</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="is_premium" value="1" {{ old('is_premium') ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-slate-700">Es premium</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <a href="{{ route('rates.destinations') }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Crear Destino</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
