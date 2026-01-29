<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-800">Visor de Base de Datos</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Tables List -->
                <div class="lg:col-span-1">
                    <div class="dark-card">
                        <div class="px-5 py-4 border-b border-slate-200">
                            <h3 class="text-lg font-semibold text-slate-800">Tablas</h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-1 max-h-[50vh] overflow-y-auto">
                                @foreach($tables as $table)
                                    <a href="{{ route('system.database', ['table' => $table['name']]) }}"
                                       class="flex justify-between items-center px-3 py-2 rounded-lg hover:bg-slate-100 transition {{ $selectedTable === $table['name'] ? 'bg-blue-50 border border-blue-200' : '' }}">
                                        <span class="text-sm {{ $selectedTable === $table['name'] ? 'text-blue-700 font-medium' : 'text-slate-700' }}">{{ $table['name'] }}</span>
                                        <span class="text-xs text-slate-500">{{ number_format($table['count']) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Quick Query -->
                    <div class="dark-card mt-4">
                        <div class="px-5 py-4 border-b border-slate-200">
                            <h3 class="text-lg font-semibold text-slate-800">Consulta SQL</h3>
                        </div>
                        <div class="p-4">
                            <form action="{{ route('system.database.query') }}" method="POST">
                                @csrf
                                <textarea name="query" rows="4"
                                          class="w-full border-slate-300 rounded-lg font-mono text-xs p-2 text-slate-700 focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="SELECT * FROM cdrs LIMIT 10;">{{ session('executedQuery', '') }}</textarea>
                                <p class="text-xs text-slate-500 mt-1 mb-2">Solo SELECT, SHOW, DESCRIBE</p>
                                <button type="submit" class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium">Ejecutar</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="lg:col-span-3">
                    @if($selectedTable)
                        <div class="dark-card">
                            <div class="px-5 py-4 border-b border-slate-200 flex justify-between items-center">
                                <h3 class="text-lg font-semibold text-slate-800">{{ $selectedTable }}</h3>
                                <div class="flex items-center gap-4">
                                    <form method="GET" action="{{ route('system.database') }}" class="flex items-center gap-2">
                                        <input type="hidden" name="table" value="{{ $selectedTable }}">
                                        <input type="text" name="search" value="{{ $search }}"
                                               placeholder="Buscar..."
                                               class="form-input border-slate-300 rounded-lg text-sm w-48 text-slate-700 focus:border-blue-500 focus:ring-blue-500">
                                        <button type="submit" class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded-lg font-medium">Buscar</button>
                                        @if($search)
                                            <a href="{{ route('system.database', ['table' => $selectedTable]) }}" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm rounded-lg font-medium">Limpiar</a>
                                        @endif
                                    </form>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                @if($tableData && count($tableData) > 0)
                                    <table class="w-full text-xs">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                @foreach($columns as $col)
                                                    <th class="px-3 py-2 text-left text-slate-600 font-medium whitespace-nowrap border-b border-slate-200">
                                                        {{ $col->Field }}
                                                        <span class="text-slate-400 text-[10px] block">{{ $col->Type }}</span>
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($tableData as $row)
                                                <tr class="hover:bg-slate-50">
                                                    @foreach($columns as $col)
                                                        <td class="px-3 py-2 text-slate-700 max-w-xs truncate" title="{{ $row->{$col->Field} ?? '' }}">
                                                            @php
                                                                $value = $row->{$col->Field} ?? null;
                                                                if (is_null($value)) {
                                                                    echo '<span class="text-slate-400 italic">NULL</span>';
                                                                } elseif (strlen($value) > 50) {
                                                                    echo e(substr($value, 0, 50)) . '...';
                                                                } else {
                                                                    echo e($value);
                                                                }
                                                            @endphp
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="p-8 text-center text-slate-500">
                                        No hay datos en esta tabla
                                    </div>
                                @endif
                            </div>
                            @if($pagination)
                                <div class="p-4 border-t border-slate-200">
                                    {{ $pagination->links() }}
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Query Results -->
                        @if(session('queryResults'))
                            <div class="dark-card">
                                <div class="px-5 py-4 border-b border-slate-200">
                                    <h3 class="text-lg font-semibold text-slate-800">Resultados de la Consulta</h3>
                                </div>
                                <div class="overflow-x-auto">
                                    @php $results = session('queryResults'); @endphp
                                    @if(count($results) > 0)
                                        <table class="w-full text-xs">
                                            <thead class="bg-slate-50">
                                                <tr>
                                                    @foreach(array_keys((array)$results[0]) as $colName)
                                                        <th class="px-3 py-2 text-left text-slate-600 font-medium whitespace-nowrap border-b border-slate-200">{{ $colName }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach($results as $row)
                                                    <tr class="hover:bg-slate-50">
                                                        @foreach((array)$row as $value)
                                                            <td class="px-3 py-2 text-slate-700 max-w-xs truncate">
                                                                @if(is_null($value))
                                                                    <span class="text-slate-400 italic">NULL</span>
                                                                @elseif(strlen($value) > 100)
                                                                    {{ substr($value, 0, 100) }}...
                                                                @else
                                                                    {{ $value }}
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div class="p-4 text-sm text-slate-600 border-t border-slate-200">
                                            {{ count($results) }} fila(s) retornadas
                                        </div>
                                    @else
                                        <div class="p-8 text-center text-slate-500">
                                            La consulta no retorno resultados
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="dark-card">
                                <div class="p-12 text-center">
                                    <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                    </svg>
                                    <h3 class="text-lg font-medium text-slate-600 mb-2">Selecciona una tabla</h3>
                                    <p class="text-sm text-slate-500">Haz clic en una tabla de la lista para ver su contenido</p>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
