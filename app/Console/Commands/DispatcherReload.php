<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Carrier;

class DispatcherReload extends Command
{
    protected $signature = 'kamailio:dispatcher-reload';
    protected $description = 'Reload Kamailio dispatcher configuration';

    public function handle()
    {
        $this->info('Reloading Kamailio dispatcher...');

        if (Carrier::reloadDispatcher()) {
            $this->info('Dispatcher reloaded successfully.');
            return 0;
        } else {
            $this->error('Failed to reload dispatcher.');
            return 1;
        }
    }
}
