<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SipTrace extends Model
{
    // Use Kamailio's sip_trace table (singular)
    protected $table = 'sip_trace';
    public $timestamps = false;

    // Disable snake_case to camelCase conversion for attributes
    public static $snakeAttributes = false;

    // Kamailio's column names
    protected $fillable = [
        'time_stamp',
        'time_us',
        'callid',
        'traced_user',
        'msg',
        'method',
        'status',
        'fromip',
        'toip',
        'fromtag',
        'totag',
        'direction',
    ];

    // Tell Laravel about the date columns
    protected $dates = [
        'time_stamp',
    ];

    // Accessor mappings for compatibility with views

    public function getCallIdAttribute(): string
    {
        return $this->attributes['callid'] ?? '';
    }

    public function getTimestampAttribute(): ?Carbon
    {
        $ts = $this->attributes['time_stamp'] ?? null;
        if ($ts === null) {
            return null;
        }
        return Carbon::parse($ts);
    }

    public function getSourceIpAttribute(): string
    {
        // fromip format: "udp:127.0.0.1:5060"
        return $this->parseIp($this->attributes['fromip'] ?? '');
    }

    public function getSourcePortAttribute(): int
    {
        return $this->parsePort($this->attributes['fromip'] ?? '');
    }

    public function getDestIpAttribute(): string
    {
        return $this->parseIp($this->attributes['toip'] ?? '');
    }

    public function getDestPortAttribute(): int
    {
        return $this->parsePort($this->attributes['toip'] ?? '');
    }

    public function getTransportAttribute(): string
    {
        // Extract from fromip format: "udp:127.0.0.1:5060"
        $fromip = $this->attributes['fromip'] ?? '';
        if (preg_match('/^(udp|tcp|tls):/i', $fromip, $m)) {
            return strtoupper($m[1]);
        }
        return 'UDP';
    }

    public function getResponseCodeAttribute(): ?int
    {
        // status can be empty, "503", or "503 Service Unavailable"
        $status = $this->attributes['status'] ?? '';
        if (empty($status)) {
            return null;
        }
        // Extract first number
        if (preg_match('/^(\d{3})/', $status, $m)) {
            return (int)$m[1];
        }
        return null;
    }

    public function getSipMessageAttribute(): string
    {
        return $this->attributes['msg'] ?? '';
    }

    public function getFromUriAttribute(): string
    {
        // Extract From header from message
        $msg = $this->attributes['msg'] ?? '';
        if (preg_match('/^From:\s*(.+?)$/mi', $msg, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    public function getToUriAttribute(): string
    {
        // Extract To header from message
        $msg = $this->attributes['msg'] ?? '';
        if (preg_match('/^To:\s*(.+?)$/mi', $msg, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    public function getTypeAttribute(): string
    {
        if ($this->response_code) {
            return 'response';
        }
        return 'request';
    }

    public function getColorAttribute(): string
    {
        if ($this->response_code) {
            return match(true) {
                $this->response_code >= 200 && $this->response_code < 300 => 'green',
                $this->response_code >= 300 && $this->response_code < 400 => 'yellow',
                $this->response_code >= 100 && $this->response_code < 200 => 'gray',
                default => 'red',
            };
        }

        $method = $this->attributes['method'] ?? '';
        return match($method) {
            'INVITE', 'ACK' => 'blue',
            'BYE', 'CANCEL' => 'orange',
            default => 'gray',
        };
    }

    // Helper methods
    private function parseIp(?string $value): string
    {
        // Format: "udp:127.0.0.1:5060"
        if (empty($value)) {
            return '';
        }
        if (preg_match('/:([^:]+):(\d+)$/', $value, $m)) {
            return $m[1];
        }
        return $value;
    }

    private function parsePort(?string $value): int
    {
        // Format: "udp:127.0.0.1:5060"
        if (empty($value)) {
            return 0;
        }
        if (preg_match('/:(\d+)$/', $value, $m)) {
            return (int)$m[1];
        }
        return 0;
    }
}
