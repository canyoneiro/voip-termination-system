<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fix: Include carriers in 'probing' state in the dispatcher view
     * so they can receive OPTIONS pings and recover to 'active' state.
     *
     * Flag values:
     * - 8 = AP (Active + Probing) - for active carriers
     * - 9 = IP (Inactive + Probing) - for probing carriers
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS kamailio_dispatcher');

        DB::statement("
            CREATE VIEW kamailio_dispatcher AS
            SELECT
                1 AS setid,
                id,
                CONCAT('sip:', host, ':', port, ';transport=', transport) AS destination,
                CASE
                    WHEN state = 'active' THEN 8
                    WHEN state = 'probing' THEN 9
                    ELSE 8
                END AS flags,
                priority,
                CONCAT('weight=', weight, ';duid=', id) AS attrs,
                name AS description
            FROM carriers
            WHERE state IN ('active', 'probing')
              AND probing_enabled = 1
            ORDER BY priority, id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS kamailio_dispatcher');

        // Restore original view (only active carriers)
        DB::statement("
            CREATE VIEW kamailio_dispatcher AS
            SELECT
                1 AS setid,
                id,
                CONCAT('sip:', host, ':', port, ';transport=', transport) AS destination,
                8 AS flags,
                priority,
                CONCAT('weight=', weight, ';duid=', id) AS attrs,
                name AS description
            FROM carriers
            WHERE state = 'active'
            ORDER BY priority, id
        ");
    }
};
