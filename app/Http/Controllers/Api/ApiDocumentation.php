<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Info(
 *     title="VoIP Termination System API",
 *     version="1.0.0",
 *     description="API REST para el sistema de terminacion VoIP. Permite gestionar clientes, carriers, CDRs, tarifas, reportes, fraude y mas.",
 *     @OA\Contact(
 *         email="admin@tellmetelecom.com",
 *         name="Soporte Tecnico"
 *     )
 * )
 *
 * @OA\Server(
 *     url="/api/v1",
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token"
 * )
 *
 * @OA\Tag(name="Health", description="Endpoints de estado del sistema")
 * @OA\Tag(name="Customers", description="Gestion de clientes")
 * @OA\Tag(name="Carriers", description="Gestion de carriers/proveedores")
 * @OA\Tag(name="CDRs", description="Registros de llamadas")
 * @OA\Tag(name="Active Calls", description="Llamadas en curso")
 * @OA\Tag(name="Stats", description="Estadisticas y metricas")
 * @OA\Tag(name="Alerts", description="Sistema de alertas")
 * @OA\Tag(name="Rates", description="Tarifas y LCR")
 * @OA\Tag(name="QoS", description="Calidad de servicio")
 * @OA\Tag(name="Reports", description="Reportes programados")
 * @OA\Tag(name="Fraud", description="Deteccion de fraude")
 * @OA\Tag(name="Webhooks", description="Webhooks y notificaciones")
 * @OA\Tag(name="System", description="Configuracion del sistema")
 *
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="data", type="object")
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(
 *         property="error",
 *         type="object",
 *         @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
 *         @OA\Property(property="message", type="string", example="The field is required")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="per_page", type="integer", example=50),
 *     @OA\Property(property="total", type="integer", example=1234)
 * )
 *
 * @OA\Schema(
 *     schema="Customer",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string", example="Cliente Ejemplo"),
 *     @OA\Property(property="company", type="string", example="Empresa S.L."),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="phone", type="string", example="+34612345678"),
 *     @OA\Property(property="max_channels", type="integer", example=10),
 *     @OA\Property(property="max_cps", type="integer", example=5),
 *     @OA\Property(property="max_daily_minutes", type="integer", nullable=true),
 *     @OA\Property(property="max_monthly_minutes", type="integer", nullable=true),
 *     @OA\Property(property="used_daily_minutes", type="integer", example=0),
 *     @OA\Property(property="used_monthly_minutes", type="integer", example=0),
 *     @OA\Property(property="active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Carrier",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string", example="Carrier Principal"),
 *     @OA\Property(property="host", type="string", example="sip.carrier.com"),
 *     @OA\Property(property="port", type="integer", example=5060),
 *     @OA\Property(property="transport", type="string", enum={"udp","tcp","tls"}, example="udp"),
 *     @OA\Property(property="codecs", type="string", example="G729,PCMA,PCMU"),
 *     @OA\Property(property="priority", type="integer", example=1),
 *     @OA\Property(property="weight", type="integer", example=100),
 *     @OA\Property(property="tech_prefix", type="string", nullable=true),
 *     @OA\Property(property="strip_digits", type="integer", example=0),
 *     @OA\Property(property="max_cps", type="integer", example=10),
 *     @OA\Property(property="max_channels", type="integer", example=50),
 *     @OA\Property(property="state", type="string", enum={"active","inactive","probing","disabled"}, example="active"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="CDR",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="call_id", type="string"),
 *     @OA\Property(property="customer_id", type="integer"),
 *     @OA\Property(property="carrier_id", type="integer", nullable=true),
 *     @OA\Property(property="source_ip", type="string", example="192.168.1.100"),
 *     @OA\Property(property="caller", type="string", example="+34612345678"),
 *     @OA\Property(property="callee", type="string", example="+34987654321"),
 *     @OA\Property(property="start_time", type="string", format="date-time"),
 *     @OA\Property(property="answer_time", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="end_time", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="duration", type="integer", example=120),
 *     @OA\Property(property="billable_duration", type="integer", example=120),
 *     @OA\Property(property="pdd", type="integer", example=1500),
 *     @OA\Property(property="sip_code", type="integer", example=200),
 *     @OA\Property(property="sip_reason", type="string", example="OK")
 * )
 *
 * @OA\Schema(
 *     schema="Alert",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="type", type="string", example="carrier_down"),
 *     @OA\Property(property="severity", type="string", enum={"info","warning","critical"}, example="warning"),
 *     @OA\Property(property="source_type", type="string", enum={"customer","carrier","system"}),
 *     @OA\Property(property="source_id", type="integer", nullable=true),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="acknowledged", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
class ApiDocumentation
{
    // This class only contains OpenAPI annotations
}
