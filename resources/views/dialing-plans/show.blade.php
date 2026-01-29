<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('dialing-plans.index') }}" class="text-slate-400 hover:text-slate-600 mr-3 p-1 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">{{ $dialingPlan->name }}</h2>
                    <p class="text-sm text-slate-500 mt-0.5">{{ $dialingPlan->rules->count() }} reglas configuradas</p>
                </div>
            </div>
            <a href="{{ route('dialing-plans.edit', $dialingPlan) }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Editar Plan
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Plan Details -->
                    <div class="dark-card p-5">
                        <h3 class="text-sm font-semibold text-slate-700 mb-4">Configuracion</h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Estado</dt>
                                <dd>
                                    @if($dialingPlan->active)
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-700">Activo</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-600">Inactivo</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Default</dt>
                                <dd>
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $dialingPlan->default_action === 'allow' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ strtoupper($dialingPlan->default_action) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Premium</dt>
                                <dd>
                                    @if($dialingPlan->block_premium)
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">Bloqueado</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-600">Permitido</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Clientes</dt>
                                <dd class="font-mono">{{ $dialingPlan->customers->count() }}</dd>
                            </div>
                        </dl>
                        @if($dialingPlan->description)
                            <div class="mt-4 pt-4 border-t border-slate-100">
                                <p class="text-xs text-slate-500">{{ $dialingPlan->description }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Test Number -->
                    <div class="dark-card p-5">
                        <h3 class="text-sm font-semibold text-slate-700 mb-4">Probar Numero</h3>
                        <div class="flex gap-2">
                            <input type="text" id="testNumber" placeholder="34612345678"
                                class="flex-1 rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <button onclick="testNumber()" class="btn-primary px-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                        <div id="testResult" class="mt-3 hidden"></div>
                    </div>

                    <!-- Assigned Customers -->
                    @if($dialingPlan->customers->isNotEmpty())
                    <div class="dark-card p-5">
                        <h3 class="text-sm font-semibold text-slate-700 mb-4">Clientes Asignados</h3>
                        <ul class="space-y-2">
                            @foreach($dialingPlan->customers as $customer)
                            <li class="flex items-center justify-between text-sm">
                                <a href="{{ route('customers.show', $customer) }}" class="text-blue-600 hover:text-blue-800">{{ $customer->name }}</a>
                                @if($customer->active)
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>

                <!-- Rules -->
                <div class="lg:col-span-2">
                    <div class="dark-card">
                        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                            <h3 class="text-sm font-semibold text-slate-700">Reglas</h3>
                            <div class="flex gap-2">
                                <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="btn-secondary text-xs py-1.5 px-3">
                                    Importar
                                </button>
                                <button onclick="document.getElementById('addRuleModal').classList.remove('hidden')" class="btn-primary text-xs py-1.5 px-3">
                                    + Añadir Regla
                                </button>
                            </div>
                        </div>

                        @if($dialingPlan->rules->isEmpty())
                            <div class="p-12 text-center">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <p class="text-slate-500 mb-3">Sin reglas configuradas</p>
                                <button onclick="document.getElementById('addRuleModal').classList.remove('hidden')" class="btn-primary text-sm">
                                    Añadir Primera Regla
                                </button>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-slate-50">
                                        <tr class="text-xs font-medium text-slate-500 uppercase">
                                            <th class="px-4 py-3 text-center w-16">Prior.</th>
                                            <th class="px-4 py-3 text-center w-20">Tipo</th>
                                            <th class="px-4 py-3 text-left">Patron</th>
                                            <th class="px-4 py-3 text-left">Descripcion</th>
                                            <th class="px-4 py-3 text-center w-20">Estado</th>
                                            <th class="px-4 py-3 text-center w-24">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($dialingPlan->rules as $rule)
                                        <tr class="{{ !$rule->active ? 'bg-slate-50 opacity-60' : '' }} hover:bg-slate-50">
                                            <td class="px-4 py-3 text-center">
                                                <span class="text-xs font-mono text-slate-500">{{ $rule->priority }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $rule->type === 'allow' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                    {{ strtoupper($rule->type) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <code class="text-sm font-mono text-slate-700 bg-slate-100 px-2 py-0.5 rounded">{{ $rule->pattern }}</code>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-slate-500">{{ $rule->description ?: '-' }}</td>
                                            <td class="px-4 py-3 text-center">
                                                @if($rule->active)
                                                    <span class="w-2 h-2 inline-block rounded-full bg-green-500"></span>
                                                @else
                                                    <span class="w-2 h-2 inline-block rounded-full bg-slate-300"></span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button onclick="editRule({{ json_encode($rule) }})" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    </button>
                                                    <form action="{{ route('dialing-plans.rules.destroy', [$dialingPlan, $rule]) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta regla?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <!-- How it works -->
                    <div class="dark-card p-5 mt-6">
                        <h3 class="text-sm font-semibold text-slate-700 mb-3">Como funciona</h3>
                        <ol class="text-sm text-slate-600 space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-xs flex-shrink-0">1</span>
                                @if($dialingPlan->block_premium)
                                    <span>Los destinos <strong>premium</strong> se bloquean automaticamente.</span>
                                @else
                                    <span>Los destinos premium estan permitidos.</span>
                                @endif
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-xs flex-shrink-0">2</span>
                                <span>Las reglas se evaluan por <strong>prioridad</strong> (menor primero). La primera que coincide gana.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-xs flex-shrink-0">3</span>
                                <span>Si ninguna regla coincide, se aplica la accion por defecto: <strong class="{{ $dialingPlan->default_action === 'allow' ? 'text-green-600' : 'text-red-600' }}">{{ strtoupper($dialingPlan->default_action) }}</strong></span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Rule Modal -->
    <div id="addRuleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Añadir Regla</h3>
            <form action="{{ route('dialing-plans.rules.store', $dialingPlan) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tipo</label>
                        <select name="type" class="w-full rounded-lg border-slate-300">
                            <option value="allow">ALLOW - Permitir</option>
                            <option value="deny">DENY - Bloquear</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Patron</label>
                        <input type="text" name="pattern" required placeholder="34* o 1800*" class="w-full rounded-lg border-slate-300">
                        <p class="mt-1 text-xs text-slate-500">Usa * como comodin. Ej: 346* = moviles España</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Descripcion</label>
                        <input type="text" name="description" placeholder="Ej: España Movil" class="w-full rounded-lg border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Prioridad</label>
                        <input type="number" name="priority" value="100" min="1" max="9999" class="w-full rounded-lg border-slate-300">
                        <p class="mt-1 text-xs text-slate-500">Menor = se evalua primero</p>
                    </div>
                    <label class="flex items-center">
                        <input type="hidden" name="active" value="0">
                        <input type="checkbox" name="active" value="1" checked class="rounded border-slate-300 text-blue-600">
                        <span class="ml-2 text-sm text-slate-600">Regla activa</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('addRuleModal').classList.add('hidden')" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-primary">Añadir</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Rule Modal -->
    <div id="editRuleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Editar Regla</h3>
            <form id="editRuleForm" method="POST">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tipo</label>
                        <select name="type" id="editType" class="w-full rounded-lg border-slate-300">
                            <option value="allow">ALLOW - Permitir</option>
                            <option value="deny">DENY - Bloquear</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Patron</label>
                        <input type="text" name="pattern" id="editPattern" required class="w-full rounded-lg border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Descripcion</label>
                        <input type="text" name="description" id="editDescription" class="w-full rounded-lg border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Prioridad</label>
                        <input type="number" name="priority" id="editPriority" min="1" max="9999" class="w-full rounded-lg border-slate-300">
                    </div>
                    <label class="flex items-center">
                        <input type="hidden" name="active" value="0">
                        <input type="checkbox" name="active" id="editActive" value="1" class="rounded border-slate-300 text-blue-600">
                        <span class="ml-2 text-sm text-slate-600">Regla activa</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('editRuleModal').classList.add('hidden')" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Importar Reglas</h3>
            <form action="{{ route('dialing-plans.rules.import', $dialingPlan) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de Reglas</label>
                        <select name="type" class="w-full rounded-lg border-slate-300">
                            <option value="allow">ALLOW - Permitir</option>
                            <option value="deny">DENY - Bloquear</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Patrones (uno por linea)</label>
                        <textarea name="patterns" rows="8" class="w-full rounded-lg border-slate-300 font-mono text-sm" placeholder="34*&#10;33*&#10;44*"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-primary">Importar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function testNumber() {
        const number = document.getElementById('testNumber').value.trim();
        if (!number) return;

        fetch('{{ route("dialing-plans.test", $dialingPlan) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ number: number })
        })
        .then(r => r.json())
        .then(data => {
            const div = document.getElementById('testResult');
            div.classList.remove('hidden');
            const color = data.allowed ? 'green' : 'red';
            const icon = data.allowed ? '✓' : '✗';
            let html = `<div class="p-3 rounded-lg bg-${color}-50 border border-${color}-200">`;
            html += `<p class="font-semibold text-${color}-700">${icon} ${data.allowed ? 'PERMITIDO' : 'BLOQUEADO'}</p>`;
            html += `<p class="text-xs text-${color}-600 mt-1">${data.message}</p>`;
            if (data.prefix) {
                html += `<p class="text-xs text-slate-500 mt-2">Prefijo: ${data.prefix.prefix} (${data.prefix.country || 'Desconocido'})`;
                if (data.prefix.is_premium) html += ' <span class="text-yellow-600">[Premium]</span>';
                html += '</p>';
            }
            html += '</div>';
            div.innerHTML = html;
        });
    }

    function editRule(rule) {
        document.getElementById('editRuleForm').action = '/dialing-plans/{{ $dialingPlan->id }}/rules/' + rule.id;
        document.getElementById('editType').value = rule.type;
        document.getElementById('editPattern').value = rule.pattern;
        document.getElementById('editDescription').value = rule.description || '';
        document.getElementById('editPriority').value = rule.priority;
        document.getElementById('editActive').checked = rule.active;
        document.getElementById('editRuleModal').classList.remove('hidden');
    }
    </script>
</x-app-layout>
