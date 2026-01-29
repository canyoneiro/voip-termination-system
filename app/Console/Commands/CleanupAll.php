<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupAll extends Command
{
    protected $signature = 'cleanup:all';
    protected $description = 'Run all cleanup tasks';

    public function handle()
    {
        $this->call('cleanup:cdrs');
        $this->call('cleanup:traces');
        $this->call('cleanup:alerts');

        $this->info('All cleanup tasks completed');

        return 0;
    }
}
