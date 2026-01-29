<nav x-data="{ open: false, moreOpen: false, billingOpen: false, configOpen: false }" class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <span class="text-base font-bold text-slate-800 hidden lg:block">VoIP Panel</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex md:ml-6 items-center space-x-1">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                        <span class="hidden xl:inline">Panel</span>
                    </a>

                    <!-- Operaciones Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="nav-link {{ request()->routeIs('customers.*') || request()->routeIs('carriers.*') || request()->routeIs('cdrs.*') ? 'nav-link-active' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span>Operaciones</span>
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="dropdown-menu">
                            <a href="{{ route('customers.index') }}" class="dropdown-item {{ request()->routeIs('customers.*') ? 'dropdown-item-active' : '' }}">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Clientes
                            </a>
                            <a href="{{ route('carriers.index') }}" class="dropdown-item {{ request()->routeIs('carriers.*') ? 'dropdown-item-active' : '' }}">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"></path></svg>
                                Carriers
                            </a>
                            <a href="{{ route('cdrs.index') }}" class="dropdown-item {{ request()->routeIs('cdrs.*') ? 'dropdown-item-active' : '' }}">
                                <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                CDRs
                            </a>
                            <hr class="my-1 border-slate-100">
                            <a href="{{ route('dialing-plans.index') }}" class="dropdown-item {{ request()->routeIs('dialing-plans.*') ? 'dropdown-item-active' : '' }}">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                Dialing Plans
                            </a>
                        </div>
                    </div>

                    <!-- Billing Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="nav-link {{ request()->routeIs('rates.*') || request()->routeIs('billing.*') ? 'nav-link-active' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Billing</span>
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="dropdown-menu">
                            <a href="{{ route('rates.index') }}" class="dropdown-item {{ request()->routeIs('rates.*') ? 'dropdown-item-active' : '' }}">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                Tarifas / LCR
                            </a>
                            @if(Route::has('billing.index'))
                            <a href="{{ route('billing.index') }}" class="dropdown-item {{ request()->routeIs('billing.*') ? 'dropdown-item-active' : '' }}">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                Saldos / Pagos
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- Monitoring Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="nav-link {{ request()->routeIs('qos.*') || request()->routeIs('fraud.*') || request()->routeIs('alerts.*') ? 'nav-link-active' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span>Monitoreo</span>
                            @php
                                $unackCount = \App\Models\Alert::where('acknowledged', false)->count();
                                $pendingFraud = \App\Models\FraudIncident::where('status', 'pending')->count();
                                $totalBadges = $unackCount + $pendingFraud;
                            @endphp
                            @if($totalBadges > 0)
                                <span class="ml-1 px-1.5 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full">{{ $totalBadges }}</span>
                            @endif
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="dropdown-menu">
                            <a href="{{ route('alerts.index') }}" class="dropdown-item {{ request()->routeIs('alerts.*') ? 'dropdown-item-active' : '' }}">
                                <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                Alertas
                                @if($unackCount > 0)
                                    <span class="ml-auto px-1.5 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full">{{ $unackCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('fraud.index') }}" class="dropdown-item {{ request()->routeIs('fraud.*') ? 'dropdown-item-active' : '' }}">
                                <svg class="w-4 h-4 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Fraude
                                @if($pendingFraud > 0)
                                    <span class="ml-auto px-1.5 py-0.5 text-xs font-bold bg-orange-500 text-white rounded-full">{{ $pendingFraud }}</span>
                                @endif
                            </a>
                            <a href="{{ route('qos.index') }}" class="dropdown-item {{ request()->routeIs('qos.*') ? 'dropdown-item-active' : '' }}">
                                <svg class="w-4 h-4 mr-2 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                QoS / Calidad
                            </a>
                            <hr class="my-1 border-slate-100">
                            <a href="{{ route('blacklist.index') }}" class="dropdown-item {{ request()->routeIs('blacklist.*') ? 'dropdown-item-active' : '' }}">
                                <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                Blacklist
                            </a>
                        </div>
                    </div>

                    <!-- Config Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="nav-link {{ request()->routeIs('reports.*') || request()->routeIs('webhooks.*') ? 'nav-link-active' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <span>Config</span>
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="dropdown-menu">
                            <a href="{{ route('reports.index') }}" class="dropdown-item {{ request()->routeIs('reports.*') ? 'dropdown-item-active' : '' }}">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Reportes
                            </a>
                            <a href="{{ route('webhooks.index') }}" class="dropdown-item {{ request()->routeIs('webhooks.*') ? 'dropdown-item-active' : '' }}">
                                <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                Webhooks
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="hidden md:flex md:items-center md:space-x-3">
                <!-- Live Stats -->
                @php $activeCallsCount = \App\Models\ActiveCall::count(); @endphp
                <div class="flex items-center px-3 py-1 bg-slate-100 rounded-lg text-sm">
                    <span class="w-2 h-2 rounded-full {{ $activeCallsCount > 0 ? 'bg-green-500 animate-pulse' : 'bg-slate-400' }} mr-2"></span>
                    <span class="font-semibold text-slate-700">{{ $activeCallsCount }}</span>
                    <span class="ml-1 text-slate-500 hidden lg:inline">activas</span>
                </div>

                <!-- User Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" class="flex items-center space-x-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors px-2 py-1.5 rounded-lg hover:bg-slate-100">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold text-xs shadow-sm">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <span class="hidden lg:block max-w-24 truncate">{{ Auth::user()->name }}</span>
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="absolute right-0 mt-2 w-48 rounded-lg bg-white border border-slate-200 shadow-lg py-1 z-50">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-sm font-medium text-slate-800">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            <svg class="w-4 h-4 inline mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Mi Perfil
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <svg class="w-4 h-4 inline mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Cerrar Sesion
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mobile Hamburger -->
            <div class="flex items-center md:hidden">
                <!-- Mobile Live Stats -->
                <div class="flex items-center px-2 py-1 bg-slate-100 rounded-lg text-xs mr-2">
                    <span class="w-1.5 h-1.5 rounded-full {{ $activeCallsCount > 0 ? 'bg-green-500' : 'bg-slate-400' }} mr-1"></span>
                    <span class="font-semibold text-slate-700">{{ $activeCallsCount }}</span>
                </div>
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none transition-colors">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden md:hidden bg-white border-t border-slate-200">
        <div class="pt-2 pb-3 space-y-1 px-3">
            <a href="{{ route('dashboard') }}" class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'mobile-nav-link-active' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"></path></svg>
                Panel
            </a>

            <!-- Mobile: Operaciones -->
            <div class="border-l-2 border-slate-200 ml-2 pl-3 space-y-1">
                <p class="px-3 py-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Operaciones</p>
                <a href="{{ route('customers.index') }}" class="mobile-nav-link {{ request()->routeIs('customers.*') ? 'mobile-nav-link-active' : '' }}">Clientes</a>
                <a href="{{ route('carriers.index') }}" class="mobile-nav-link {{ request()->routeIs('carriers.*') ? 'mobile-nav-link-active' : '' }}">Carriers</a>
                <a href="{{ route('cdrs.index') }}" class="mobile-nav-link {{ request()->routeIs('cdrs.*') ? 'mobile-nav-link-active' : '' }}">CDRs</a>
                <a href="{{ route('dialing-plans.index') }}" class="mobile-nav-link {{ request()->routeIs('dialing-plans.*') ? 'mobile-nav-link-active' : '' }}">Dialing Plans</a>
            </div>

            <!-- Mobile: Billing -->
            <div class="border-l-2 border-slate-200 ml-2 pl-3 space-y-1">
                <p class="px-3 py-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Billing</p>
                <a href="{{ route('rates.index') }}" class="mobile-nav-link {{ request()->routeIs('rates.*') ? 'mobile-nav-link-active' : '' }}">Tarifas / LCR</a>
                @if(Route::has('billing.index'))
                <a href="{{ route('billing.index') }}" class="mobile-nav-link {{ request()->routeIs('billing.*') ? 'mobile-nav-link-active' : '' }}">Saldos / Pagos</a>
                @endif
            </div>

            <!-- Mobile: Monitoreo -->
            <div class="border-l-2 border-slate-200 ml-2 pl-3 space-y-1">
                <p class="px-3 py-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Monitoreo</p>
                <a href="{{ route('alerts.index') }}" class="mobile-nav-link {{ request()->routeIs('alerts.*') ? 'mobile-nav-link-active' : '' }}">
                    Alertas
                    @if($unackCount > 0)<span class="ml-2 px-1.5 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full">{{ $unackCount }}</span>@endif
                </a>
                <a href="{{ route('fraud.index') }}" class="mobile-nav-link {{ request()->routeIs('fraud.*') ? 'mobile-nav-link-active' : '' }}">
                    Fraude
                    @if($pendingFraud > 0)<span class="ml-2 px-1.5 py-0.5 text-xs font-bold bg-orange-500 text-white rounded-full">{{ $pendingFraud }}</span>@endif
                </a>
                <a href="{{ route('qos.index') }}" class="mobile-nav-link {{ request()->routeIs('qos.*') ? 'mobile-nav-link-active' : '' }}">QoS / Calidad</a>
                <a href="{{ route('blacklist.index') }}" class="mobile-nav-link {{ request()->routeIs('blacklist.*') ? 'mobile-nav-link-active' : '' }}">Blacklist</a>
            </div>

            <!-- Mobile: Config -->
            <div class="border-l-2 border-slate-200 ml-2 pl-3 space-y-1">
                <p class="px-3 py-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Configuracion</p>
                <a href="{{ route('reports.index') }}" class="mobile-nav-link {{ request()->routeIs('reports.*') ? 'mobile-nav-link-active' : '' }}">Reportes</a>
                <a href="{{ route('webhooks.index') }}" class="mobile-nav-link {{ request()->routeIs('webhooks.*') ? 'mobile-nav-link-active' : '' }}">Webhooks</a>
            </div>
        </div>

        <!-- Mobile: User -->
        <div class="pt-3 pb-3 border-t border-slate-200 px-3">
            <div class="flex items-center mb-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="ml-3">
                    <div class="font-medium text-base text-slate-800">{{ Auth::user()->name }}</div>
                    <div class="text-sm text-slate-500">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="space-y-1">
                <a href="{{ route('profile.edit') }}" class="mobile-nav-link">Mi Perfil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mobile-nav-link w-full text-left text-red-600">Cerrar Sesion</button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .nav-link {
            @apply inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-100;
        }
        .nav-link-active {
            @apply bg-blue-50 text-blue-700 !important;
        }
        .dropdown-menu {
            @apply absolute left-0 mt-1 w-52 rounded-lg bg-white border border-slate-200 shadow-lg py-1 z-50;
        }
        .dropdown-item {
            @apply flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50;
        }
        .dropdown-item-active {
            @apply bg-blue-50 text-blue-700;
        }
        .mobile-nav-link {
            @apply block px-3 py-2 rounded-lg text-base font-medium text-slate-600 hover:bg-slate-100;
        }
        .mobile-nav-link-active {
            @apply bg-blue-50 text-blue-700;
        }
    </style>
</nav>
