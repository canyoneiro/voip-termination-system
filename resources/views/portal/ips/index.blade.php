<x-portal.layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                IPs Autorizadas
            </h2>
            @if($settings?->allow_ip_requests)
                <a href="{{ route('portal.ips.create') }}" class="btn-primary text-sm">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Solicitar IP
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Current IPs -->
            <div class="dark-card mb-6">
                <div class="p-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800">IPs Autorizadas Actualmente</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="dark-table">
                        <thead>
                            <tr>
                                <th>IP</th>
                                <th>Descripcion</th>
                                <th>Estado</th>
                                <th>Fecha Autorizacion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ips as $ip)
                                <tr>
                                    <td class="mono font-medium">{{ $ip->ip_address }}</td>
                                    <td>{{ $ip->description ?? '-' }}</td>
                                    <td>
                                        @if($ip->active)
                                            <span class="badge badge-green">Activa</span>
                                        @else
                                            <span class="badge badge-gray">Inactiva</span>
                                        @endif
                                    </td>
                                    <td class="text-slate-500 text-sm">{{ $ip->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-slate-500 py-8">
                                        No hay IPs autorizadas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($settings?->allow_ip_requests)
                <!-- Pending Requests -->
                @if($pendingRequests->count() > 0)
                <div class="dark-card mb-6">
                    <div class="p-4 border-b border-slate-200">
                        <h3 class="font-semibold text-slate-800">Solicitudes Pendientes</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="dark-table">
                            <thead>
                                <tr>
                                    <th>IP Solicitada</th>
                                    <th>Descripcion</th>
                                    <th>Justificacion</th>
                                    <th>Fecha Solicitud</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRequests as $request)
                                    <tr>
                                        <td class="mono font-medium">{{ $request->ip_address }}</td>
                                        <td>{{ $request->description ?? '-' }}</td>
                                        <td class="max-w-xs truncate">{{ $request->justification }}</td>
                                        <td class="text-slate-500 text-sm">{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('portal.ips.cancel', $request) }}" class="inline"
                                                  onsubmit="return confirm('Cancelar esta solicitud?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                                    Cancelar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Request History -->
                @if($requestHistory->count() > 0)
                <div class="dark-card">
                    <div class="p-4 border-b border-slate-200">
                        <h3 class="font-semibold text-slate-800">Historial de Solicitudes</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="dark-table">
                            <thead>
                                <tr>
                                    <th>IP</th>
                                    <th>Estado</th>
                                    <th>Fecha Solicitud</th>
                                    <th>Fecha Resolucion</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requestHistory as $request)
                                    <tr>
                                        <td class="mono">{{ $request->ip_address }}</td>
                                        <td>
                                            @if($request->status === 'approved')
                                                <span class="badge badge-green">Aprobada</span>
                                            @else
                                                <span class="badge badge-red">Rechazada</span>
                                            @endif
                                        </td>
                                        <td class="text-slate-500 text-sm">{{ $request->created_at->format('d/m/Y') }}</td>
                                        <td class="text-slate-500 text-sm">{{ $request->resolved_at?->format('d/m/Y') ?? '-' }}</td>
                                        <td class="text-sm">{{ $request->resolution_notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            @else
                <div class="dark-card p-6">
                    <div class="text-center text-slate-500">
                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <p>Las solicitudes de IP no estan habilitadas para su cuenta.</p>
                        <p class="text-sm mt-1">Contacte al administrador para solicitar cambios en sus IPs autorizadas.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-portal.layouts.app>
