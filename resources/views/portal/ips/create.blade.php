<x-portal.layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Solicitar Nueva IP
            </h2>
            <a href="{{ route('portal.ips.index') }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dark-card p-6">
                <div class="mb-6">
                    <h3 class="font-semibold text-slate-800 mb-2">Solicitud de Autorizacion de IP</h3>
                    <p class="text-slate-600 text-sm">
                        Complete el formulario para solicitar la autorizacion de una nueva IP.
                        La solicitud sera revisada por nuestro equipo y recibira una notificacion con el resultado.
                    </p>
                </div>

                <form method="POST" action="{{ route('portal.ips.store') }}">
                    @csrf

                    <!-- IP Address -->
                    <div class="mb-4">
                        <label for="ip_address" class="block text-sm font-medium text-slate-700 mb-1">
                            Direccion IP <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="ip_address" name="ip_address" value="{{ old('ip_address') }}"
                               class="dark-input w-full" placeholder="ej: 192.168.1.100"
                               pattern="^((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4}$"
                               required>
                        @error('ip_address')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">
                            Descripcion
                        </label>
                        <input type="text" id="description" name="description" value="{{ old('description') }}"
                               class="dark-input w-full" placeholder="ej: Servidor PBX oficina central"
                               maxlength="255">
                        <p class="text-slate-500 text-xs mt-1">Descripcion breve para identificar esta IP</p>
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Justification -->
                    <div class="mb-6">
                        <label for="justification" class="block text-sm font-medium text-slate-700 mb-1">
                            Justificacion <span class="text-red-500">*</span>
                        </label>
                        <textarea id="justification" name="justification" rows="4"
                                  class="dark-input w-full" placeholder="Explique por que necesita autorizar esta IP..."
                                  maxlength="1000" required>{{ old('justification') }}</textarea>
                        <p class="text-slate-500 text-xs mt-1">Explique el motivo de esta solicitud (max 1000 caracteres)</p>
                        @error('justification')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-medium mb-1">Informacion importante:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Solo se aceptan IPs publicas estaticas</li>
                                    <li>La solicitud sera procesada en un plazo de 24-48 horas</li>
                                    <li>Recibira una notificacion por email con el resultado</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('portal.ips.index') }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Enviar Solicitud</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-portal.layouts.app>
