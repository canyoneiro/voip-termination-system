<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Formato de numeración que envía el cliente
            // - international: El cliente envía siempre en formato E.164 (34XXXXXXXXX o +34XXXXXXXXX)
            // - national_es: El cliente envía en formato nacional español (9 dígitos sin prefijo)
            // - auto: Detectar automáticamente según el formato recibido
            $table->enum('number_format', ['international', 'national_es', 'auto'])
                ->default('auto')
                ->after('dialing_plan_id');

            // País por defecto para normalización cuando se usa formato nacional
            $table->char('default_country_code', 3)
                ->default('34')
                ->after('number_format');

            // Opciones adicionales de normalización
            $table->boolean('strip_plus_sign')->default(true)->after('default_country_code');
            $table->boolean('add_plus_sign')->default(false)->after('strip_plus_sign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'number_format',
                'default_country_code',
                'strip_plus_sign',
                'add_plus_sign',
            ]);
        });
    }
};
