<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Incidente de Fraude #{{ $incident->id }}
            </h2>
            <a href="{{ route('fraud.incidents') }}" class="btn-secondary text-sm">Volver</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Info principal -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="dark-card p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-800">{{ $incident->description }}</h3>
                                <p class="text-sm text-slate-500">{{ $incident->created_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                            @php
                                $sevColors = ['critical' => 'red', 'high' => 'orange', 'medium' => 'yellow', 'low' => 'blue'];
                                $statusColors = ['pending' => 'yellow', 'investigating' => 'blue', 'false_positive' => 'gray', 'confirmed' => 'red', 'resolved' => 'green'];
                            @endphp
                            <div class="flex gap-2">
                                <span class="badge badge-{{ $sevColors[$incident->severity] ?? 'gray' }}">
                                    {{ ucfirst($incident->severity) }}
                                </span>
                                <span class="badge badge-{{ $statusColors[$incident->status] ?? 'gray' }}">
                                    {{ ucfirst(str_replace('_', ' ', $incident->status)) }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wide">Tipo</p>
                                <p class="font-medium text-slate-800">{{ ucfirst(str_replace('_', ' ', $incident->type)) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wide">Cliente</p>
                                @if($incident->customer)
                                    <a href="{{ route('customers.show', $incident->customer) }}" class="font-medium text-blue-600 hover:text-blue-800">
                                        {{ $incident->customer->name }}
                                    </a>
                                @else
                                    <p class="text-slate-500">N/A</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wide">Regla</p>
                                <p class="font-medium text-slate-800">{{ $incident->rule->name ?? 'Manual' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wide">Score de Riesgo</p>
                                <p class="font-bold text-lg text-{{ $incident->risk_score >= 80 ? 'red' : ($incident->risk_score >= 50 ? 'yellow' : 'slate') }}-600">
                                    {{ $incident->risk_score ?? 0 }}
                                </p>
                            </div>
                        </div>

                        @if($incident->details)
                            <div class="border-t border-slate-200 pt-4 mt-4">
                                <p class="text-xs text-slate-500 uppercase tracking-wide mb-2">Detalles</p>
                                <pre class="bg-slate-50 p-3 rounded-lg text-sm text-slate-700 overflow-x-auto">{{ json_encode($incident->details, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif

                        @if($incident->cdr)
                            <div class="border-t border-slate-200 pt-4 mt-4">
                                <p class="text-xs text-slate-500 uppercase tracking-wide mb-2">CDR Relacionado</p>
                                <div class="bg-slate-50 p-3 rounded-lg">
                                    <div class="grid grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <p class="text-slate-500">Caller</p>
                                            <p class="font-mono">{{ $incident->cdr->caller }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-500">Callee</p>
                                            <p class="font-mono">{{ $incident->cdr->callee }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-500">Duracion</p>
                                            <p>{{ $incident->cdr->duration }}s</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('cdrs.show', $incident->cdr) }}" class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                                        Ver CDR completo
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if($incident->resolved_at)
                            <div class="border-t border-slate-200 pt-4 mt-4">
                                <p class="text-xs text-slate-500 uppercase tracking-wide mb-2">Resolucion</p>
                                <div class="bg-{{ $incident->status === 'false_positive' ? 'gray' : 'green' }}-50 p-3 rounded-lg">
                                    <p class="text-sm"><strong>Resuelto por:</strong> {{ $incident->resolvedBy->name ?? 'Sistema' }}</p>
                                    <p class="text-sm"><strong>Fecha:</strong> {{ $incident->resolved_at->format('d/m/Y H:i') }}</p>
                                    @if($incident->resolution_notes)
                                        <p class="text-sm mt-2"><strong>Notas:</strong> {{ $incident->resolution_notes }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Incidentes relacionados -->
                    @if($relatedIncidents->count() > 0)
                        <div class="dark-card p-6">
                            <h3 class="font-semibold text-slate-800 mb-4">Incidentes relacionados (mismo cliente, 30 dias)</h3>
                            <div class="space-y-2">
                                @foreach($relatedIncidents as $related)
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-slate-800 text-sm">{{ Str::limit($related->description, 50) }}</p>
                                            <p class="text-xs text-slate-500">{{ $related->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="badge badge-{{ $sevColors[$related->severity] ?? 'gray' }} text-xs">
                                                {{ ucfirst($related->severity) }}
                                            </span>
                                            <a href="{{ route('fraud.incidents.show', $related) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                                Ver
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Panel de acciones -->
                <div class="space-y-6">
                    <div class="dark-card p-6">
                        <h3 class="font-semibold text-slate-800 mb-4">Actualizar Estado</h3>
                        <form method="POST" action="{{ route('fraud.incidents.update', $incident) }}">
                            @csrf
                            @method('PUT')

                            <div class="space-y-4">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
                                    <select name="status" id="status" class="input-field w-full">
                                        <option value="pending" {{ $incident->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="investigating" {{ $incident->status == 'investigating' ? 'selected' : '' }}>Investigando</option>
                                        <option value="false_positive" {{ $incident->status == 'false_positive' ? 'selected' : '' }}>Falso Positivo</option>
                                        <option value="confirmed" {{ $incident->status == 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                                        <option value="resolved" {{ $incident->status == 'resolved' ? 'selected' : '' }}>Resuelto</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="resolution_notes" class="block text-sm font-medium text-slate-700 mb-1">Notas de resolucion</label>
                                    <textarea name="resolution_notes" id="resolution_notes" rows="3"
                                              class="input-field w-full" placeholder="Descripcion de las acciones tomadas...">{{ $incident->resolution_notes }}</textarea>
                                </div>

                                <button type="submit" class="btn-primary w-full">Actualizar</button>
                            </div>
                        </form>
                    </div>

                    <div class="dark-card p-6">
                        <h3 class="font-semibold text-slate-800 mb-4">Acciones Rapidas</h3>
                        <div class="space-y-2">
                            @if($incident->customer)
                                <a href="{{ route('customers.show', $incident->customer) }}" class="btn-secondary w-full text-sm text-center block">
                                    Ver Cliente
                                </a>
                            @endif
                            @if($incident->rule)
                                <a href="{{ route('fraud.rules.edit', $incident->rule) }}" class="btn-secondary w-full text-sm text-center block">
                                    Editar Regla
                                </a>
                            @endif
                            <a href="{{ route('cdrs.index', ['customer_id' => $incident->customer_id]) }}" class="btn-secondary w-full text-sm text-center block">
                                Ver CDRs del Cliente
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
