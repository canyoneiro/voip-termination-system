<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class KamailioReload extends Command
{
    protected $signature = 'kamailio:reload';
    protected $description = 'Reload Kamailio configuration without restart';

    public function handle()
    {
        // Reload dispatcher
        $result1 = Process::run('kamcmd dispatcher.reload');
        if ($result1->successful()) {
            $this->info('Dispatcher reloaded');
        } else {
            $this->error('Failed to reload dispatcher: ' . $result1->errorOutput());
        }

        // Reload permissions (address table)
        $result2 = Process::run('kamcmd permissions.addressReload');
        if ($result2->successful()) {
            $this->info('Permissions reloaded');
        } else {
            $this->error('Failed to reload permissions: ' . $result2->errorOutput());
        }

        return 0;
    }
}
