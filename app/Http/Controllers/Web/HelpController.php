<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\KamailioAddress;
use App\Models\KamailioDispatcher;

class HelpController extends Controller
{
    public function index()
    {
        // Estadísticas para la página de ayuda
        $stats = [
            'customers' => Customer::count(),
            'customers_active' => Customer::where('active', true)->count(),
            'carriers' => Carrier::count(),
            'carriers_active' => Carrier::where('state', 'active')->count(),
            'ips_authorized' => KamailioAddress::count(),
            'dispatcher_entries' => KamailioDispatcher::count(),
        ];

        return view('help.index', compact('stats'));
    }
}
