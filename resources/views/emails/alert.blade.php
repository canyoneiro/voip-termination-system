<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $alert->title }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid {{ $alert->severity === 'critical' ? '#dc2626' : ($alert->severity === 'warning' ? '#f59e0b' : '#3b82f6') }};
        }
        .header .severity-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            @if($alert->severity === 'critical')
            background: #fef2f2;
            color: #dc2626;
            @elseif($alert->severity === 'warning')
            background: #fffbeb;
            color: #d97706;
            @else
            background: #eff6ff;
            color: #2563eb;
            @endif
        }
        .header h1 {
            color: #1e293b;
            margin: 0;
            font-size: 22px;
        }
        .alert-type {
            color: #64748b;
            font-size: 14px;
            margin-top: 8px;
        }
        .content-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .content-section h3 {
            margin: 0 0 15px 0;
            color: #334155;
            font-size: 16px;
        }
        .message-text {
            font-size: 15px;
            color: #475569;
            line-height: 1.7;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #64748b;
            font-size: 13px;
        }
        .detail-value {
            color: #1e293b;
            font-weight: 500;
            font-size: 13px;
        }
        .metadata-section {
            background: #f1f5f9;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
            font-family: monospace;
            font-size: 12px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin-top: 20px;
        }
        .btn-danger {
            background: #dc2626;
        }
        .btn-warning {
            background: #f59e0b;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 12px;
        }
        .timestamp {
            color: #94a3b8;
            font-size: 12px;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="severity-badge" style="background: {{ $alert->severity === 'critical' ? '#fef2f2' : ($alert->severity === 'warning' ? '#fffbeb' : '#eff6ff') }}; color: {{ $alert->severity === 'critical' ? '#dc2626' : ($alert->severity === 'warning' ? '#d97706' : '#2563eb') }};">
                {{ strtoupper($alert->severity) }}
            </div>
            <h1>{{ $alert->title }}</h1>
            <div class="alert-type">
                Tipo: {{ str_replace('_', ' ', ucwords($alert->type)) }}
            </div>
        </div>

        <div class="content-section">
            <h3>Mensaje</h3>
            <div class="message-text">
                {{ $alert->message }}
            </div>
        </div>

        <div class="content-section">
            <h3>Detalles</h3>
            <div class="detail-row">
                <span class="detail-label">Severidad</span>
                <span class="detail-value">{{ ucfirst($alert->severity) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Tipo</span>
                <span class="detail-value">{{ str_replace('_', ' ', ucwords($alert->type)) }}</span>
            </div>
            @if($alert->source_type)
            <div class="detail-row">
                <span class="detail-label">Origen</span>
                <span class="detail-value">{{ ucfirst($alert->source_type) }}: {{ $alert->source_name ?? $alert->source_id ?? '-' }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="detail-label">Fecha/Hora</span>
                <span class="detail-value">{{ $alert->created_at->format('d/m/Y H:i:s') }} UTC</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">ID de Alerta</span>
                <span class="detail-value">{{ $alert->uuid }}</span>
            </div>

            @if($alert->metadata && count($alert->metadata) > 0)
            <div class="metadata-section">
                <strong>Metadata:</strong><br>
                @foreach($alert->metadata as $key => $value)
                {{ $key }}: {{ is_array($value) ? json_encode($value) : $value }}<br>
                @endforeach
            </div>
            @endif
        </div>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/alerts" class="btn {{ $alert->severity === 'critical' ? 'btn-danger' : ($alert->severity === 'warning' ? 'btn-warning' : '') }}">
                Ver en el Panel
            </a>
        </div>

        <div class="timestamp">
            Alerta generada el {{ $alert->created_at->format('d/m/Y') }} a las {{ $alert->created_at->format('H:i:s') }} UTC
        </div>

        <div class="footer">
            <p>Este es un email automatico generado por {{ config('app.name', 'VoIP Panel') }}.</p>
            <p>Para gestionar las notificaciones, accede a la configuracion del panel.</p>
        </div>
    </div>
</body>
</html>
