<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\CustomerIp;
use App\Models\Carrier;
use App\Models\Cdr;
use App\Models\ActiveCall;
use App\Models\Alert;
use App\Models\IpBlacklist;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Crear Clientes de prueba
        $customers = [
            [
                'uuid' => Str::uuid(),
                'name' => 'TelcoMax España',
                'company' => 'TelcoMax S.L.',
                'email' => 'admin@telcomax.es',
                'phone' => '+34 911 234 567',
                'max_channels' => 50,
                'max_cps' => 10,
                'max_daily_minutes' => 10000,
                'max_monthly_minutes' => 200000,
                'used_daily_minutes' => 3450,
                'used_monthly_minutes' => 78500,
                'active' => 1,
                'notes' => 'Cliente premium con soporte prioritario',
                'alert_email' => 'alertas@telcomax.es',
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'VozNet Solutions',
                'company' => 'VozNet Solutions S.A.',
                'email' => 'operaciones@voznet.com',
                'phone' => '+34 932 345 678',
                'max_channels' => 30,
                'max_cps' => 5,
                'max_daily_minutes' => 5000,
                'max_monthly_minutes' => 100000,
                'used_daily_minutes' => 1200,
                'used_monthly_minutes' => 45000,
                'active' => 1,
                'notes' => 'Especializado en llamadas internacionales',
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'CallCenter Pro',
                'company' => 'CallCenter Pro Madrid',
                'email' => 'it@callcenterpro.es',
                'phone' => '+34 915 678 901',
                'max_channels' => 100,
                'max_cps' => 20,
                'max_daily_minutes' => null,
                'max_monthly_minutes' => null,
                'used_daily_minutes' => 8900,
                'used_monthly_minutes' => 156000,
                'active' => 1,
                'notes' => 'Call center grande - sin límite de minutos',
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'MicroTel',
                'company' => 'MicroTel Comunicaciones',
                'email' => 'soporte@microtel.es',
                'phone' => '+34 961 234 567',
                'max_channels' => 10,
                'max_cps' => 2,
                'max_daily_minutes' => 1000,
                'max_monthly_minutes' => 20000,
                'used_daily_minutes' => 890,
                'used_monthly_minutes' => 18500,
                'active' => 1,
                'notes' => 'Cliente pequeño - atención al consumo',
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Empresa Suspendida',
                'company' => 'Suspendida S.L.',
                'email' => 'admin@suspendida.com',
                'phone' => '+34 900 000 000',
                'max_channels' => 5,
                'max_cps' => 1,
                'active' => 0,
                'notes' => 'Suspendido por impago - revisar en febrero',
            ],
        ];

        foreach ($customers as $customerData) {
            $customer = Customer::create($customerData);

            // Añadir IPs a cada cliente
            $ipCount = rand(1, 3);
            for ($i = 0; $i < $ipCount; $i++) {
                CustomerIp::create([
                    'customer_id' => $customer->id,
                    'ip_address' => '10.0.' . $customer->id . '.' . ($i + 1),
                    'description' => $i === 0 ? 'IP Principal' : 'IP Secundaria ' . $i,
                    'active' => 1,
                ]);
            }
        }

        // Crear Carriers de prueba
        $carriers = [
            [
                'uuid' => Str::uuid(),
                'name' => 'Carrier España Principal',
                'host' => '185.45.120.100',
                'port' => 5060,
                'transport' => 'udp',
                'codecs' => 'G729,PCMA,PCMU',
                'priority' => 1,
                'weight' => 100,
                'max_cps' => 50,
                'max_channels' => 200,
                'state' => 'active',
                'daily_calls' => 1250,
                'daily_minutes' => 4580,
                'notes' => 'Carrier principal para destinos nacionales',
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Carrier Internacional EU',
                'host' => '91.210.45.80',
                'port' => 5060,
                'transport' => 'udp',
                'codecs' => 'G729,PCMA',
                'priority' => 2,
                'weight' => 80,
                'tech_prefix' => '00',
                'max_cps' => 30,
                'max_channels' => 100,
                'state' => 'active',
                'daily_calls' => 450,
                'daily_minutes' => 2100,
                'notes' => 'Para destinos Europa',
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Carrier LATAM',
                'host' => '45.78.90.120',
                'port' => 5060,
                'transport' => 'udp',
                'codecs' => 'G729',
                'priority' => 2,
                'weight' => 70,
                'strip_digits' => 2,
                'max_cps' => 20,
                'max_channels' => 80,
                'state' => 'active',
                'daily_calls' => 320,
                'daily_minutes' => 1890,
                'notes' => 'Especializado en Latinoamérica',
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Carrier Backup',
                'host' => '192.168.100.50',
                'port' => 5080,
                'transport' => 'tcp',
                'codecs' => 'PCMA,PCMU',
                'priority' => 10,
                'weight' => 50,
                'max_cps' => 10,
                'max_channels' => 50,
                'state' => 'probing',
                'failover_count' => 5,
                'notes' => 'Solo para emergencias - carrier caro',
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'Carrier Desactivado',
                'host' => '10.0.0.1',
                'port' => 5060,
                'transport' => 'udp',
                'codecs' => 'G729',
                'priority' => 99,
                'weight' => 1,
                'max_cps' => 5,
                'max_channels' => 20,
                'state' => 'disabled',
                'notes' => 'Carrier antiguo - pendiente de eliminar',
            ],
        ];

        foreach ($carriers as $carrierData) {
            Carrier::create($carrierData);
        }

        // Obtener IDs para CDRs
        $customerIds = Customer::pluck('id')->toArray();
        $carrierIds = Carrier::where('state', 'active')->pluck('id')->toArray();

        // Crear CDRs de prueba (últimas 48 horas)
        $sipCodes = [200, 200, 200, 200, 200, 486, 487, 503, 408, 404, 480, 200, 200, 200];
        $hangupCauses = ['caller', 'callee', 'caller', 'callee', 'system', 'timeout'];

        for ($i = 0; $i < 150; $i++) {
            $customerId = $customerIds[array_rand($customerIds)];
            $carrierId = !empty($carrierIds) ? $carrierIds[array_rand($carrierIds)] : null;
            $sipCode = $sipCodes[array_rand($sipCodes)];
            $answered = $sipCode === 200;
            $startTime = Carbon::now()->subMinutes(rand(1, 2880));
            $duration = $answered ? rand(10, 600) : 0;

            Cdr::create([
                'uuid' => Str::uuid(),
                'call_id' => Str::random(32) . '@' . rand(100, 999) . '.example.com',
                'customer_id' => $customerId,
                'carrier_id' => $carrierId,
                'source_ip' => '10.0.' . rand(1, 5) . '.' . rand(1, 254),
                'caller' => '+34' . rand(600000000, 699999999),
                'caller_original' => '+34' . rand(600000000, 699999999),
                'callee' => '+34' . rand(900000000, 999999999),
                'callee_original' => '+34' . rand(900000000, 999999999),
                'destination_ip' => $carrierId ? '185.45.120.100' : null,
                'start_time' => $startTime,
                'progress_time' => $answered ? $startTime->copy()->addSeconds(rand(1, 3)) : null,
                'answer_time' => $answered ? $startTime->copy()->addSeconds(rand(3, 8)) : null,
                'end_time' => $startTime->copy()->addSeconds($duration + rand(3, 8)),
                'duration' => $duration,
                'billable_duration' => $answered ? (int)ceil($duration / 6) * 6 : 0,
                'pdd' => $answered ? rand(100, 3000) : null,
                'sip_code' => $sipCode,
                'sip_reason' => match($sipCode) {
                    200 => 'OK',
                    486 => 'Busy Here',
                    487 => 'Request Terminated',
                    503 => 'Service Unavailable',
                    408 => 'Request Timeout',
                    404 => 'Not Found',
                    480 => 'Temporarily Unavailable',
                    default => 'Unknown',
                },
                'hangup_cause' => $answered ? $hangupCauses[array_rand($hangupCauses)] : 'failed',
                'codec_used' => $answered ? ['G729', 'PCMA', 'PCMU'][array_rand(['G729', 'PCMA', 'PCMU'])] : null,
                'user_agent' => ['Odin/2.1', 'FreeSWITCH/1.10', 'Asterisk/18.0', 'Odin/2.0'][array_rand(['Odin/2.1', 'FreeSWITCH/1.10', 'Asterisk/18.0', 'Odin/2.0'])],
            ]);
        }

        // Crear algunas llamadas activas
        for ($i = 0; $i < 8; $i++) {
            $customerId = $customerIds[array_rand($customerIds)];
            $carrierId = !empty($carrierIds) ? $carrierIds[array_rand($carrierIds)] : null;
            $answered = rand(0, 1);
            $startTime = Carbon::now()->subMinutes(rand(1, 30));

            ActiveCall::create([
                'call_id' => Str::random(32) . '@active.example.com',
                'customer_id' => $customerId,
                'carrier_id' => $carrierId,
                'caller' => '+34' . rand(600000000, 699999999),
                'callee' => '+34' . rand(900000000, 999999999),
                'source_ip' => '10.0.' . rand(1, 5) . '.' . rand(1, 254),
                'start_time' => $startTime,
                'answered' => $answered,
                'answer_time' => $answered ? $startTime->copy()->addSeconds(rand(3, 8)) : null,
            ]);
        }

        // Crear Alertas de prueba
        $alerts = [
            [
                'uuid' => Str::uuid(),
                'type' => 'carrier_down',
                'severity' => 'critical',
                'source_type' => 'carrier',
                'source_id' => 4,
                'source_name' => 'Carrier Backup',
                'title' => 'Carrier no responde',
                'message' => 'El carrier "Carrier Backup" no ha respondido a OPTIONS en los últimos 90 segundos',
                'acknowledged' => 0,
                'created_at' => Carbon::now()->subMinutes(15),
            ],
            [
                'uuid' => Str::uuid(),
                'type' => 'minutes_warning',
                'severity' => 'warning',
                'source_type' => 'customer',
                'source_id' => 4,
                'source_name' => 'MicroTel',
                'title' => '90% de minutos diarios consumidos',
                'message' => 'El cliente "MicroTel" ha consumido 890 de 1000 minutos diarios (89%)',
                'acknowledged' => 0,
                'created_at' => Carbon::now()->subMinutes(45),
            ],
            [
                'uuid' => Str::uuid(),
                'type' => 'high_failure_rate',
                'severity' => 'warning',
                'source_type' => 'carrier',
                'source_id' => 3,
                'source_name' => 'Carrier LATAM',
                'title' => 'Tasa de fallos alta',
                'message' => 'ASR del carrier "Carrier LATAM" ha bajado al 65% en la última hora',
                'acknowledged' => 1,
                'acknowledged_at' => Carbon::now()->subMinutes(20),
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'uuid' => Str::uuid(),
                'type' => 'security_flood_detected',
                'severity' => 'critical',
                'source_type' => 'system',
                'title' => 'Flood de INVITE detectado',
                'message' => 'Se detectaron 150 INVITE/segundo desde IP 45.33.32.156. IP bloqueada automáticamente.',
                'metadata' => json_encode(['ip' => '45.33.32.156', 'rate' => 150, 'action' => 'blocked']),
                'acknowledged' => 1,
                'acknowledged_at' => Carbon::now()->subHours(5),
                'created_at' => Carbon::now()->subHours(6),
            ],
            [
                'uuid' => Str::uuid(),
                'type' => 'carrier_recovered',
                'severity' => 'info',
                'source_type' => 'carrier',
                'source_id' => 2,
                'source_name' => 'Carrier Internacional EU',
                'title' => 'Carrier recuperado',
                'message' => 'El carrier "Carrier Internacional EU" ha vuelto a responder correctamente',
                'acknowledged' => 1,
                'created_at' => Carbon::now()->subHours(8),
            ],
        ];

        foreach ($alerts as $alertData) {
            Alert::create($alertData);
        }

        // Crear IPs en Blacklist
        $blacklistEntries = [
            [
                'ip_address' => '45.33.32.156',
                'reason' => 'Flood de INVITE detectado - 150/segundo',
                'source' => 'flood_detection',
                'attempts' => 150,
                'permanent' => 0,
                'expires_at' => Carbon::now()->addHours(23),
            ],
            [
                'ip_address' => '185.234.72.100',
                'reason' => 'Escaneo SIP detectado',
                'source' => 'scanner',
                'attempts' => 45,
                'permanent' => 0,
                'expires_at' => Carbon::now()->addHours(12),
            ],
            [
                'ip_address' => '91.200.14.124',
                'reason' => 'Múltiples intentos de autenticación fallidos',
                'source' => 'fail2ban',
                'attempts' => 25,
                'permanent' => 0,
                'expires_at' => Carbon::now()->addMinutes(30),
            ],
            [
                'ip_address' => '5.188.86.10',
                'reason' => 'Conocido por ataques VoIP - bloqueado permanentemente',
                'source' => 'manual',
                'attempts' => 0,
                'permanent' => 1,
            ],
        ];

        foreach ($blacklistEntries as $entry) {
            IpBlacklist::create($entry);
        }

        // Crear Webhooks de prueba
        $webhooks = [
            [
                'uuid' => Str::uuid(),
                'customer_id' => 1,
                'url' => 'https://webhook.telcomax.es/voip/events',
                'secret' => Str::random(64),
                'events' => ['call.started', 'call.ended', 'customer.minutes_warning'],
                'active' => 1,
            ],
            [
                'uuid' => Str::uuid(),
                'customer_id' => 3,
                'url' => 'https://api.callcenterpro.es/webhooks/voip',
                'secret' => Str::random(64),
                'events' => ['call.started', 'call.answered', 'call.ended'],
                'active' => 1,
            ],
            [
                'uuid' => Str::uuid(),
                'customer_id' => null,
                'url' => 'https://monitoring.internal/alerts',
                'secret' => Str::random(64),
                'events' => ['carrier.down', 'carrier.recovered', 'alert.created'],
                'active' => 1,
            ],
        ];

        foreach ($webhooks as $webhook) {
            WebhookEndpoint::create($webhook);
        }

        $this->command->info('Datos de demostración creados exitosamente!');
    }
}
