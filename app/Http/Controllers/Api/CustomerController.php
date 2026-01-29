<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\CustomerIp;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use OpenApi\Annotations as OA;

class CustomerController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/customers",
     *     summary="Listar clientes",
     *     description="Obtiene una lista paginada de clientes",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="active", in="query", description="Filtrar por estado activo", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="search", in="query", description="Buscar por nombre, empresa o email", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", description="Resultados por pagina (max 100)", @OA\Schema(type="integer", default=50)),
     *     @OA\Parameter(name="page", in="query", description="Numero de pagina", @OA\Schema(type="integer", default=1)),
     *     @OA\Response(response=200, description="Lista de clientes", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::with('ips');

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->input('per_page', 50), 100);
        $customers = $query->orderBy('name')->paginate($perPage);

        return $this->paginated($customers);
    }

    /**
     * @OA\Get(
     *     path="/customers/{id}",
     *     summary="Obtener cliente",
     *     description="Obtiene los detalles de un cliente especifico",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del cliente", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalle del cliente"),
     *     @OA\Response(response=404, description="Cliente no encontrado")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $customer = Customer::with('ips')->find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        // Add real-time stats
        $customer->active_calls = $customer->activeCalls()->count();
        $customer->current_cps = (int) Redis::get("voip:cps:{$id}") ?: 0;

        return $this->success($customer);
    }

    /**
     * @OA\Post(
     *     path="/customers",
     *     summary="Crear cliente",
     *     description="Crea un nuevo cliente",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name"},
     *         @OA\Property(property="name", type="string", example="Nuevo Cliente"),
     *         @OA\Property(property="company", type="string"),
     *         @OA\Property(property="email", type="string", format="email"),
     *         @OA\Property(property="phone", type="string"),
     *         @OA\Property(property="max_channels", type="integer", default=10),
     *         @OA\Property(property="max_cps", type="integer", default=5),
     *         @OA\Property(property="max_daily_minutes", type="integer"),
     *         @OA\Property(property="max_monthly_minutes", type="integer")
     *     )),
     *     @OA\Response(response=201, description="Cliente creado"),
     *     @OA\Response(response=422, description="Error de validacion")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'company' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'max_channels' => 'integer|min:1|max:1000',
            'max_cps' => 'integer|min:1|max:100',
            'max_daily_minutes' => 'nullable|integer|min:0',
            'max_monthly_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'alert_email' => 'nullable|email|max:255',
            'alert_telegram_chat_id' => 'nullable|string|max:100',
        ]);

        $customer = Customer::create($validated);

        AuditLog::log('customer.created', 'customer', $customer->id, null, $validated);

        return $this->success($customer, [], 201);
    }

    /**
     * @OA\Put(
     *     path="/customers/{id}",
     *     summary="Actualizar cliente",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Customer")),
     *     @OA\Response(response=200, description="Cliente actualizado"),
     *     @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        $validated = $request->validate([
            'name' => 'string|max:100',
            'company' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'max_channels' => 'integer|min:1|max:1000',
            'max_cps' => 'integer|min:1|max:100',
            'max_daily_minutes' => 'nullable|integer|min:0',
            'max_monthly_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'alert_email' => 'nullable|email|max:255',
            'alert_telegram_chat_id' => 'nullable|string|max:100',
        ]);

        $oldValues = $customer->toArray();
        $customer->update($validated);

        AuditLog::log('customer.updated', 'customer', $customer->id, $oldValues, $validated);

        return $this->success($customer);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        $validated = $request->validate([
            'active' => 'required|boolean',
        ]);

        $oldActive = $customer->active;
        $customer->update(['active' => $validated['active']]);

        AuditLog::log(
            $validated['active'] ? 'customer.enabled' : 'customer.disabled',
            'customer',
            $customer->id,
            ['active' => $oldActive],
            ['active' => $validated['active']]
        );

        return $this->success(['active' => $customer->active]);
    }

    /**
     * @OA\Delete(
     *     path="/customers/{id}",
     *     summary="Eliminar cliente",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Cliente eliminado"),
     *     @OA\Response(response=404, description="No encontrado"),
     *     @OA\Response(response=409, description="Tiene llamadas activas")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        // Check for active calls
        if ($customer->activeCalls()->count() > 0) {
            return $this->error('Cannot delete customer with active calls', 'HAS_ACTIVE_CALLS', [], 409);
        }

        $customerData = $customer->toArray();
        $customer->delete();

        AuditLog::log('customer.deleted', 'customer', $id, $customerData, null);

        return $this->success(['deleted' => true]);
    }

    public function ips(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        return $this->success($customer->ips);
    }

    public function addIp(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'description' => 'nullable|string|max:100',
        ]);

        // Check if IP already exists for this customer
        $existing = CustomerIp::where('customer_id', $id)
            ->where('ip_address', $validated['ip_address'])
            ->first();

        if ($existing) {
            return $this->error('IP address already exists for this customer', 'DUPLICATE_IP', [], 409);
        }

        $ip = CustomerIp::create([
            'customer_id' => $id,
            'ip_address' => $validated['ip_address'],
            'description' => $validated['description'] ?? null,
            'active' => true,
        ]);

        AuditLog::log('customer.ip.added', 'customer', $id, null, $validated);

        return $this->success($ip, [], 201);
    }

    public function removeIp(int $id, int $ipId): JsonResponse
    {
        $ip = CustomerIp::where('customer_id', $id)->where('id', $ipId)->first();

        if (!$ip) {
            return $this->notFound('IP not found');
        }

        $ipData = $ip->toArray();
        $ip->delete();

        AuditLog::log('customer.ip.removed', 'customer', $id, $ipData, null);

        return $this->success(['deleted' => true]);
    }

    public function activeCalls(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        $calls = $customer->activeCalls()->with('carrier')->get();

        return $this->success($calls);
    }

    public function usage(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        $activeCalls = $customer->activeCalls()->count();
        $currentCps = (int) Redis::get("voip:cps:{$id}") ?: 0;

        return $this->success([
            'active_calls' => $activeCalls,
            'max_channels' => $customer->max_channels,
            'channels_usage_pct' => $customer->max_channels > 0
                ? round(($activeCalls / $customer->max_channels) * 100, 2)
                : 0,
            'current_cps' => $currentCps,
            'max_cps' => $customer->max_cps,
            'used_daily_minutes' => $customer->used_daily_minutes,
            'max_daily_minutes' => $customer->max_daily_minutes,
            'daily_minutes_pct' => $customer->daily_minutes_percentage,
            'used_monthly_minutes' => $customer->used_monthly_minutes,
            'max_monthly_minutes' => $customer->max_monthly_minutes,
            'monthly_minutes_pct' => $customer->monthly_minutes_percentage,
        ]);
    }

    public function resetMinutes(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        $resetDaily = $request->boolean('daily', true);
        $resetMonthly = $request->boolean('monthly', false);

        $updates = [];
        if ($resetDaily) {
            $updates['used_daily_minutes'] = 0;
        }
        if ($resetMonthly) {
            $updates['used_monthly_minutes'] = 0;
        }

        if (!empty($updates)) {
            $oldValues = $customer->only(array_keys($updates));
            $customer->update($updates);
            AuditLog::log('customer.minutes.reset', 'customer', $id, $oldValues, $updates);
        }

        return $this->success(['reset' => true]);
    }
}
