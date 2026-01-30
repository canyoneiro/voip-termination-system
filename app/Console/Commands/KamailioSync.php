<?php

namespace App\Console\Commands;

use App\Models\KamailioAddress;
use App\Models\KamailioDispatcher;
use Illuminate\Console\Command;

class KamailioSync extends Command
{
    protected $signature = 'kamailio:sync
                            {--addresses : Sincronizar solo direcciones de clientes}
                            {--dispatcher : Sincronizar solo dispatcher de carriers}
                            {--no-reload : No recargar Kamailio después de sincronizar}';

    protected $description = 'Recarga los módulos de Kamailio (las vistas se actualizan automáticamente)';

    public function handle(): int
    {
        $reloadAddresses = $this->option('addresses') || !$this->option('dispatcher');
        $reloadDispatcher = $this->option('dispatcher') || !$this->option('addresses');

        $this->info('Las vistas de Kamailio leen directamente de la base de datos.');
        $this->info('Solo es necesario recargar los módulos para aplicar cambios.');
        $this->newLine();

        if ($reloadAddresses) {
            $this->reloadAddresses();
        }

        if ($reloadDispatcher) {
            $this->reloadDispatcher();
        }

        $this->newLine();
        $this->info('✓ Operación completada');

        return Command::SUCCESS;
    }

    protected function reloadAddresses(): void
    {
        $count = KamailioAddress::getCount();
        $this->info("IPs autorizadas en vista: {$count}");

        $reloaded = KamailioAddress::reloadKamailio();
        if ($reloaded) {
            $this->line('  → Módulo permissions recargado ✓');
        } else {
            $this->warn('  ⚠ No se pudo recargar el módulo permissions');
        }
    }

    protected function reloadDispatcher(): void
    {
        $count = KamailioDispatcher::getCount();
        $this->info("Carriers activos en vista: {$count}");

        $reloaded = KamailioDispatcher::reloadKamailio();
        if ($reloaded) {
            $this->line('  → Módulo dispatcher recargado ✓');
        } else {
            $this->warn('  ⚠ No se pudo recargar el módulo dispatcher');
        }
    }
}
