<?php

namespace App\Jobs;

use App\Models\Carrier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

/**
 * Sync carrier states from Kamailio dispatcher to database
 * This ensures CarrierObserver gets triggered for state changes
 */
class SyncCarrierStatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 30;

    public function handle(): void
    {
        // Get dispatcher list from Kamailio
        $result = Process::run('kamcmd dispatcher.list 2>/dev/null');

        if (!$result->successful()) {
            Log::warning("Could not get dispatcher list from Kamailio: " . $result->errorOutput());
            return;
        }

        $output = $result->output();
        $carrierStates = $this->parseDispatcherOutput($output);

        if (empty($carrierStates)) {
            return;
        }

        foreach ($carrierStates as $carrierId => $kamState) {
            $carrier = Carrier::find($carrierId);
            if (!$carrier) {
                continue;
            }

            // Skip if carrier is manually disabled
            if ($carrier->state === 'disabled') {
                continue;
            }

            // Map Kamailio flags to our states
            $newState = $this->mapKamailioState($kamState);

            // Check if state changed (for logging)
            $stateChanged = $carrier->state !== $newState;

            if ($stateChanged) {
                Log::info("Carrier state change detected from Kamailio", [
                    'carrier_id' => $carrierId,
                    'carrier_name' => $carrier->name,
                    'old_state' => $carrier->state,
                    'new_state' => $newState,
                    'kamailio_flags' => $kamState,
                ]);
            }

            // Always update last_options_time for active carriers responding to probing
            // This fixes the bug where alerts were generated for healthy carriers
            if ($newState === 'active') {
                $carrier->update([
                    'state' => $newState,
                    'last_options_time' => now(),
                    'last_options_reply' => 200,
                ]);
            } elseif ($stateChanged) {
                // Only update state if it changed (for non-active states)
                $carrier->update([
                    'state' => $newState,
                ]);
            }
        }
    }

    /**
     * Parse kamcmd dispatcher.list output
     */
    protected function parseDispatcherOutput(string $output): array
    {
        $states = [];

        // Parse the dispatcher output format
        // Looking for lines like: DEST: {... FLAGS: ... attrs: {duid=123} ...}
        preg_match_all('/DEST:\s*\{[^}]*FLAGS:\s*(\w+)[^}]*attrs:\s*\{[^}]*duid=(\d+)/i', $output, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $flags = $match[1];
            $carrierId = (int) $match[2];
            $states[$carrierId] = $flags;
        }

        // Alternative parsing for different kamcmd output format
        if (empty($states)) {
            preg_match_all('/URI=.*?FLAGS=(\w+).*?DUID=(\d+)/i', $output, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $flags = $match[1];
                $carrierId = (int) $match[2];
                $states[$carrierId] = $flags;
            }
        }

        return $states;
    }

    /**
     * Map Kamailio dispatcher flags to our state enum
     */
    protected function mapKamailioState(string $flags): string
    {
        $flags = strtoupper($flags);

        // AP = Active/Probing (normal operation)
        // IP = Inactive/Probing
        // AX = Active/Disabled
        // IX = Inactive/Disabled

        if (str_contains($flags, 'I') && str_contains($flags, 'X')) {
            return 'inactive';
        }

        if (str_contains($flags, 'I')) {
            return 'probing';
        }

        if (str_contains($flags, 'X')) {
            return 'disabled';
        }

        return 'active';
    }
}
