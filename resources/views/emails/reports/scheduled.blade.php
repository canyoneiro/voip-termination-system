<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report->name }}</title>
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
            border-bottom: 2px solid #3b82f6;
        }
        .header h1 {
            color: #1e40af;
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #64748b;
            font-size: 14px;
            margin-top: 5px;
        }
        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .summary-card h3 {
            margin: 0 0 15px 0;
            color: #334155;
            font-size: 16px;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-item .value {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
        }
        .stat-item .label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .data-table th {
            background: #f1f5f9;
            padding: 10px;
            text-align: left;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 12px;
        }
        .attachment-info {
            background: #dbeafe;
            border: 1px solid #93c5fd;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
        }
        .attachment-info svg {
            width: 24px;
            height: 24px;
            color: #1e40af;
            margin-bottom: 5px;
        }
        .btn {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $report->name }}</h1>
            <div class="subtitle">
                Reporte {{ ucfirst($report->frequency) }} - {{ now()->format('d/m/Y') }}
            </div>
        </div>

        @if(isset($data['summary']))
        <div class="summary-card">
            <h3>Resumen del Periodo</h3>
            <div class="stat-grid">
                @if(isset($data['summary']['total_calls']))
                <div class="stat-item">
                    <div class="value">{{ number_format($data['summary']['total_calls']) }}</div>
                    <div class="label">Llamadas Totales</div>
                </div>
                @endif
                @if(isset($data['summary']['answered_calls']))
                <div class="stat-item">
                    <div class="value">{{ number_format($data['summary']['answered_calls']) }}</div>
                    <div class="label">Contestadas</div>
                </div>
                @endif
                @if(isset($data['summary']['total_minutes']))
                <div class="stat-item">
                    <div class="value">{{ number_format($data['summary']['total_minutes']) }}</div>
                    <div class="label">Minutos</div>
                </div>
                @endif
                @if(isset($data['summary']['asr']))
                <div class="stat-item">
                    <div class="value">{{ $data['summary']['asr'] }}%</div>
                    <div class="label">ASR</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if(isset($data['top_destinations']) && count($data['top_destinations']) > 0)
        <div class="summary-card">
            <h3>Top Destinos</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Destino</th>
                        <th>Llamadas</th>
                        <th>Minutos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($data['top_destinations'], 0, 5) as $dest)
                    <tr>
                        <td>{{ $dest['destination'] ?? $dest['prefix'] ?? '-' }}</td>
                        <td>{{ number_format($dest['calls'] ?? 0) }}</td>
                        <td>{{ number_format($dest['minutes'] ?? 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="attachment-info">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
            </svg>
            <p style="margin: 5px 0 0 0; font-size: 14px; color: #1e40af;">
                Este email incluye el reporte completo en formato
                @foreach($report->formats as $format)
                    <strong>{{ strtoupper($format) }}</strong>@if(!$loop->last), @endif
                @endforeach
                como adjunto.
            </p>
        </div>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}" class="btn">Ver en el Panel</a>
        </div>

        <div class="footer">
            <p>Este es un email automatico generado por {{ config('app.name', 'VoIP Panel') }}.</p>
            <p>No responda a este email.</p>
        </div>
    </div>
</body>
</html>
