<?php

namespace App\Traits;

use App\Services\KamailioService;

trait ReloadsKamailio
{
    /**
     * Boot the trait - automatically reload Kamailio on model changes
     */
    public static function bootReloadsKamailio(): void
    {
        static::saved(function ($model) {
            $model->triggerKamailioReload();
        });

        static::deleted(function ($model) {
            $model->triggerKamailioReload();
        });
    }

    /**
     * Get the Kamailio reload type for this model
     * Override in model to specify: 'dispatcher', 'permissions', 'htable', 'all'
     */
    protected function getKamailioReloadType(): string
    {
        return 'none';
    }

    /**
     * Trigger the appropriate Kamailio reload
     */
    protected function triggerKamailioReload(): void
    {
        $type = $this->getKamailioReloadType();

        match ($type) {
            'dispatcher' => KamailioService::reloadDispatcher(),
            'permissions' => KamailioService::reloadPermissions(),
            'htable' => KamailioService::reloadHtable(),
            'all' => KamailioService::reloadAll(),
            default => null,
        };
    }
}
