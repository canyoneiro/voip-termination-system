<nav x-data="{ open: false }" class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                        <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-md">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-slate-800">VoIP Panel</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:flex sm:ml-10 space-x-1">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                        Panel
                    </a>
                    <a href="{{ route('customers.index') }}" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('customers.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Clientes
                    </a>
                    <a href="{{ route('carriers.index') }}" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('carriers.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
                        Carriers
                    </a>
                    <a href="{{ route('cdrs.index') }}" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('cdrs.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        CDRs
                    </a>
                    <a href="{{ route('alerts.index') }}" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('alerts.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        Alertas
                        @php $unackCount = \App\Models\Alert::where('acknowledged', false)->count(); @endphp
                        @if($unackCount > 0)
                            <span class="ml-1.5 px-1.5 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full animate-pulse">{{ $unackCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('blacklist.index') }}" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('blacklist.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                        Blacklist
                    </a>
                    <!-- More Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('qos.*') || request()->routeIs('fraud.*') || request()->routeIs('reports.*') || request()->routeIs('rates.*') || request()->routeIs('webhooks.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            Mas
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute left-0 mt-2 w-56 rounded-xl bg-white border border-slate-200 shadow-lg py-1 z-50">
                            <a href="{{ route('qos.index') }}" class="flex items-center px-4 py-2 text-sm {{ request()->routeIs('qos.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                QoS / Calidad
                            </a>
                            <a href="{{ route('fraud.index') }}" class="flex items-center px-4 py-2 text-sm {{ request()->routeIs('fraud.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Fraude
                                @php $pendingFraud = \App\Models\FraudIncident::where('status', 'pending')->count(); @endphp
                                @if($pendingFraud > 0)
                                    <span class="ml-auto px-1.5 py-0.5 text-xs font-bold bg-orange-500 text-white rounded-full">{{ $pendingFraud }}</span>
                                @endif
                            </a>
                            <a href="{{ route('reports.index') }}" class="flex items-center px-4 py-2 text-sm {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Reportes
                            </a>
                            <a href="{{ route('rates.index') }}" class="flex items-center px-4 py-2 text-sm {{ request()->routeIs('rates.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Tarifas / LCR
                            </a>
                            <hr class="my-1 border-slate-200">
                            <a href="{{ route('webhooks.index') }}" class="flex items-center px-4 py-2 text-sm {{ request()->routeIs('webhooks.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                Webhooks
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- Live Stats -->
                <div class="flex items-center space-x-4 mr-6">
                    @php $activeCallsCount = \App\Models\ActiveCall::count(); @endphp
                    <div class="flex items-center px-3 py-1.5 bg-slate-100 rounded-full">
                        <span class="w-2 h-2 rounded-full {{ $activeCallsCount > 0 ? 'bg-green-500 animate-pulse' : 'bg-slate-400' }} mr-2"></span>
                        <span class="font-semibold text-slate-700 text-sm">{{ $activeCallsCount }}</span>
                        <span class="ml-1 text-slate-500 text-sm">activas</span>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors px-3 py-2 rounded-lg hover:bg-slate-100">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold text-sm shadow-sm">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <span>{{ Auth::user()->name }}</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 rounded-xl bg-white border border-slate-200 shadow-lg py-1 z-50">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            Mi Perfil
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                Cerrar Sesion
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none transition-colors">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-slate-200">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-600' }}">Panel</a>
            <a href="{{ route('customers.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('customers.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600' }}">Clientes</a>
            <a href="{{ route('carriers.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('carriers.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600' }}">Carriers</a>
            <a href="{{ route('cdrs.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('cdrs.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600' }}">CDRs</a>
            <a href="{{ route('alerts.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('alerts.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600' }}">Alertas</a>
            <a href="{{ route('blacklist.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('blacklist.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600' }}">Blacklist</a>
            <hr class="my-2 border-slate-200">
            <a href="{{ route('qos.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('qos.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600' }}">QoS / Calidad</a>
            <a href="{{ route('fraud.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('fraud.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600' }}">Fraude</a>
            <a href="{{ route('reports.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600' }}">Reportes</a>
            <a href="{{ route('rates.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('rates.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600' }}">Tarifas / LCR</a>
            <a href="{{ route('webhooks.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->routeIs('webhooks.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600' }}">Webhooks</a>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-slate-200">
            <div class="px-4 flex items-center">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="ml-3">
                    <div class="font-medium text-base text-slate-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1 px-4">
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-600 hover:bg-slate-100">
                    Mi Perfil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-3 py-2 rounded-lg text-base font-medium text-slate-600 hover:bg-slate-100">
                        Cerrar Sesion
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
