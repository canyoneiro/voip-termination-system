<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class KamailioService
{
    /**
     * Reload dispatcher (carriers/routing)
     * Safe for active calls - hot reload only
     */
    public static function reloadDispatcher(): bool
    {
        return self::executeCommand('kamcmd dispatcher.reload', 'dispatcher');
    }

    /**
     * Reload permissions/address table (customer IPs)
     * Safe for active calls - hot reload only
     */
    public static function reloadPermissions(): bool
    {
        return self::executeCommand('kamcmd permissions.addressReload', 'permissions');
    }

    /**
     * Reload htable (blacklist, rate limiting caches)
     * Safe for active calls - hot reload only
     */
    public static function reloadHtable(string $table = null): bool
    {
        if ($table) {
            return self::executeCommand("kamcmd htable.reload $table", "htable:$table");
        }
        // Reload all htables
        return self::executeCommand('kamcmd htable.reload blacklist', 'htable:blacklist');
    }

    /**
     * Reload domain table
     * Safe for active calls - hot reload only
     */
    public static function reloadDomain(): bool
    {
        return self::executeCommand('kamcmd domain.reload', 'domain');
    }

    /**
     * Reload all relevant Kamailio configs
     * Use with caution - reloads everything
     */
    public static function reloadAll(): array
    {
        return [
            'dispatcher' => self::reloadDispatcher(),
            'permissions' => self::reloadPermissions(),
            'htable' => self::reloadHtable(),
        ];
    }

    /**
     * Check if Kamailio is running
     */
    public static function isRunning(): bool
    {
        $result = \shell_exec('systemctl is-active kamailio 2>&1');
        return trim($result ?? '') === 'active';
    }

    /**
     * Get Kamailio statistics
     */
    public static function getStats(): array
    {
        $stats = [];

        // Get core stats
        $result = \shell_exec('kamcmd stats.get_statistics all 2>&1');
        if ($result) {
            // Parse stats output
            preg_match_all('/(\w+)\s*=\s*(\d+)/', $result, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $stats[$match[1]] = (int) $match[2];
            }
        }

        return $stats;
    }

    /**
     * Get dispatcher list status
     */
    public static function getDispatcherStatus(): array
    {
        $result = \shell_exec('kamcmd dispatcher.list 2>&1');
        return ['raw' => $result];
    }

    /**
     * Execute kamcmd command safely
     */
    private static function executeCommand(string $command, string $context): bool
    {
        try {
            $result = \shell_exec("$command 2>&1");
            $resultTrimmed = trim($result ?? '');

            // Success conditions:
            // - Empty output (normal success)
            // - Contains 'Ok' or 'success'
            // - "Ongoing reload" means it's already reloading, which is fine
            $success = $result !== null && (
                str_contains($result, 'Ok') ||
                str_contains($result, 'success') ||
                str_contains($result, 'Ongoing reload') ||
                $resultTrimmed === ''
            );

            // Only log if there's something notable
            if (!$success || $resultTrimmed !== '') {
                Log::info("Kamailio $context reload", [
                    'command' => $command,
                    'result' => $resultTrimmed,
                    'success' => $success
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error("Kamailio $context reload failed", [
                'command' => $command,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
