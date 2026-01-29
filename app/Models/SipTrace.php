<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SipTrace extends Model
{
    protected $table = 'sip_traces';
    public $timestamps = false;

    protected $fillable = [
        'call_id',
        'timestamp',
        'source_ip',
        'source_port',
        'dest_ip',
        'dest_port',
        'transport',
        'method',
        'response_code',
        'direction',
        'from_uri',
        'to_uri',
        'sip_message',
    ];

    protected $casts = [
        'timestamp' => 'datetime:Y-m-d H:i:s.v',
        'source_port' => 'integer',
        'dest_port' => 'integer',
        'response_code' => 'integer',
    ];

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

        return match($this->method) {
            'INVITE', 'ACK' => 'blue',
            'BYE', 'CANCEL' => 'orange',
            default => 'gray',
        };
    }
}
