<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Editar Reporte: {{ $report->name }}
            </h2>
            <a href="{{ route('reports.show', $report) }}" class="btn-secondary text-sm">Cancelar</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="dark-card p-6">
                <form method="POST" action="{{ route('reports.update', $report) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nombre del reporte *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $report->name) }}" required
                                   class="input-field w-full">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de reporte</label>
                                <div class="input-field w-full bg-slate-100">{{ $types[$report->type] ?? $report->type }}</div>
                                <p class="mt-1 text-xs text-slate-500">El tipo no se puede modificar</p>
                            </div>

                            <div>
                                <label for="frequency" class="block text-sm font-medium text-slate-700 mb-1">Frecuencia *</label>
                                <select name="frequency" id="frequency" required class="input-field w-full">
                                    @foreach($frequencies as $key => $label)
                                        <option value="{{ $key }}" {{ old('frequency', $report->frequency) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="customer_id" class="block text-sm font-medium text-slate-700 mb-1">Cliente (opcional)</label>
                            <select name="customer_id" id="customer_id" class="input-field w-full">
                                <option value="">Todos los clientes</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id', $report->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Destinatarios *</label>
                            <div id="recipients-container">
                                @foreach(old('recipients', $report->recipients ?? []) as $recipient)
                                    <div class="flex gap-2 mb-2 recipient-row">
                                        <input type="email" name="recipients[]" value="{{ $recipient }}"
                                               class="input-field flex-1" placeholder="email@ejemplo.com" required>
                                        <button type="button" onclick="removeRecipient(this)" class="btn-danger text-sm">X</button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" onclick="addRecipient()" class="btn-secondary text-sm mt-2">+ Agregar destinatario</button>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Formatos de exportacion *</label>
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="formats[]" value="pdf"
                                           {{ in_array('pdf', old('formats', $report->formats ?? [])) ? 'checked' : '' }}
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-slate-700">PDF</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="formats[]" value="csv"
                                           {{ in_array('csv', old('formats', $report->formats ?? [])) ? 'checked' : '' }}
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-slate-700">CSV</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="active" value="1" {{ old('active', $report->active) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-slate-700">Reporte activo</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <a href="{{ route('reports.show', $report) }}" class="btn-secondary">Cancelar</a>
                        <button type="submit" class="btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function addRecipient() {
            const container = document.getElementById('recipients-container');
            const div = document.createElement('div');
            div.className = 'flex gap-2 mb-2 recipient-row';
            div.innerHTML = `
                <input type="email" name="recipients[]" class="input-field flex-1" placeholder="email@ejemplo.com" required>
                <button type="button" onclick="removeRecipient(this)" class="btn-danger text-sm">X</button>
            `;
            container.appendChild(div);
        }

        function removeRecipient(btn) {
            const rows = document.querySelectorAll('.recipient-row');
            if (rows.length > 1) {
                btn.parentElement.remove();
            }
        }
    </script>
</x-app-layout>
