<?php

namespace App\Services;

use App\Models\Customer;

/**
 * Servicio de normalización de números telefónicos
 *
 * Este servicio permite normalizar números telefónicos recibidos de clientes
 * en diferentes formatos a un formato estándar E.164 para el enrutamiento.
 *
 * Formatos soportados:
 * - Internacional E.164: 34666123456 o +34666123456
 * - Nacional España: 666123456 (9 dígitos)
 * - Detección automática
 */
class NumberNormalizationService
{
    /**
     * Patrones de números nacionales por país
     * Cada país tiene: código, longitud de número nacional, patrón regex
     */
    protected array $countryPatterns = [
        '34' => [ // España
            'national_length' => 9,
            'pattern' => '/^[6789]\d{8}$/', // Móviles empiezan por 6/7, fijos por 8/9
            'name' => 'España',
        ],
        '351' => [ // Portugal
            'national_length' => 9,
            'pattern' => '/^[29]\d{8}$/',
            'name' => 'Portugal',
        ],
        '33' => [ // Francia
            'national_length' => 9,
            'pattern' => '/^[1-9]\d{8}$/',
            'name' => 'Francia',
        ],
        '49' => [ // Alemania
            'national_length' => [10, 11], // Variable
            'pattern' => '/^[1-9]\d{9,10}$/',
            'name' => 'Alemania',
        ],
        '44' => [ // Reino Unido
            'national_length' => 10,
            'pattern' => '/^[1-9]\d{9}$/',
            'name' => 'Reino Unido',
        ],
        '39' => [ // Italia
            'national_length' => [9, 10],
            'pattern' => '/^[0-9]\d{8,9}$/',
            'name' => 'Italia',
        ],
        '1' => [ // USA/Canadá
            'national_length' => 10,
            'pattern' => '/^[2-9]\d{9}$/',
            'name' => 'USA/Canadá',
        ],
        '52' => [ // México
            'national_length' => 10,
            'pattern' => '/^[1-9]\d{9}$/',
            'name' => 'México',
        ],
        '54' => [ // Argentina
            'national_length' => 10,
            'pattern' => '/^[1-9]\d{9}$/',
            'name' => 'Argentina',
        ],
        '57' => [ // Colombia
            'national_length' => 10,
            'pattern' => '/^[3]\d{9}$/',
            'name' => 'Colombia',
        ],
    ];

    /**
     * Normaliza un número telefónico según la configuración del cliente
     *
     * @param string $number Número a normalizar
     * @param Customer $customer Cliente que envía la llamada
     * @return array Resultado con número normalizado e información
     */
    public function normalize(string $number, Customer $customer): array
    {
        $originalNumber = $number;
        $format = $customer->number_format ?? 'auto';
        $defaultCountry = $customer->default_country_code ?? '34';

        // Limpiar el número de caracteres no numéricos excepto +
        $cleanNumber = $this->cleanNumber($number);

        $result = match ($format) {
            'international' => $this->normalizeInternational($cleanNumber),
            'national_es' => $this->normalizeNational($cleanNumber, $defaultCountry),
            'auto' => $this->normalizeAuto($cleanNumber, $defaultCountry),
            default => $this->normalizeAuto($cleanNumber, $defaultCountry),
        };

        // Aplicar opciones adicionales del cliente
        $normalizedNumber = $result['normalized'];

        if ($customer->strip_plus_sign && str_starts_with($normalizedNumber, '+')) {
            $normalizedNumber = substr($normalizedNumber, 1);
        }

        if ($customer->add_plus_sign && !str_starts_with($normalizedNumber, '+')) {
            $normalizedNumber = '+' . $normalizedNumber;
        }

        return [
            'original' => $originalNumber,
            'normalized' => $normalizedNumber,
            'format_detected' => $result['format_detected'],
            'country_code' => $result['country_code'],
            'national_number' => $result['national_number'],
            'is_valid' => $result['is_valid'],
            'format_used' => $format,
            'message' => $result['message'] ?? null,
        ];
    }

    /**
     * Limpia un número de caracteres no válidos
     */
    protected function cleanNumber(string $number): string
    {
        // Preservar el + inicial si existe
        $hasPlus = str_starts_with($number, '+');

        // Eliminar todo excepto dígitos
        $cleaned = preg_replace('/[^0-9]/', '', $number);

        // Restaurar el + si lo tenía
        if ($hasPlus) {
            $cleaned = '+' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Normaliza asumiendo que el número viene en formato internacional
     */
    protected function normalizeInternational(string $number): array
    {
        // Quitar el + si existe
        $number = ltrim($number, '+');

        // Intentar detectar el código de país
        $countryInfo = $this->detectCountryCode($number);

        if ($countryInfo) {
            return [
                'normalized' => $number,
                'format_detected' => 'international',
                'country_code' => $countryInfo['code'],
                'national_number' => $countryInfo['national'],
                'is_valid' => true,
                'message' => "Número internacional válido para {$countryInfo['name']}",
            ];
        }

        // No se reconoce el país, pero asumimos que es válido
        return [
            'normalized' => $number,
            'format_detected' => 'international',
            'country_code' => null,
            'national_number' => null,
            'is_valid' => true,
            'message' => 'Número internacional (país no reconocido)',
        ];
    }

    /**
     * Normaliza asumiendo que el número viene en formato nacional
     */
    protected function normalizeNational(string $number, string $countryCode): array
    {
        // Quitar el + si existe (no debería en formato nacional)
        $number = ltrim($number, '+');

        // Si el número ya tiene el código de país, extraerlo
        if (str_starts_with($number, $countryCode)) {
            $nationalNumber = substr($number, strlen($countryCode));
            return [
                'normalized' => $number,
                'format_detected' => 'international_with_country',
                'country_code' => $countryCode,
                'national_number' => $nationalNumber,
                'is_valid' => true,
                'message' => 'El número ya incluía el código de país',
            ];
        }

        // Verificar si parece un número nacional válido
        $countryPattern = $this->countryPatterns[$countryCode] ?? null;

        if ($countryPattern) {
            $expectedLength = $countryPattern['national_length'];
            $lengths = is_array($expectedLength) ? $expectedLength : [$expectedLength];

            if (in_array(strlen($number), $lengths)) {
                // Añadir código de país
                $normalized = $countryCode . $number;
                return [
                    'normalized' => $normalized,
                    'format_detected' => 'national',
                    'country_code' => $countryCode,
                    'national_number' => $number,
                    'is_valid' => true,
                    'message' => "Número nacional convertido a internacional ({$countryPattern['name']})",
                ];
            }
        }

        // No coincide con el patrón esperado, añadir código de país de todas formas
        $normalized = $countryCode . $number;
        return [
            'normalized' => $normalized,
            'format_detected' => 'national_forced',
            'country_code' => $countryCode,
            'national_number' => $number,
            'is_valid' => true,
            'message' => 'Número nacional convertido (longitud no estándar)',
        ];
    }

    /**
     * Detecta automáticamente el formato y normaliza
     */
    protected function normalizeAuto(string $number, string $defaultCountry): array
    {
        // Quitar el + si existe
        $hasPlus = str_starts_with($number, '+');
        $number = ltrim($number, '+');

        // Si tenía +, es claramente internacional
        if ($hasPlus) {
            return $this->normalizeInternational($number);
        }

        // Detectar si ya tiene código de país
        $countryInfo = $this->detectCountryCode($number);
        if ($countryInfo) {
            return [
                'normalized' => $number,
                'format_detected' => 'auto_international',
                'country_code' => $countryInfo['code'],
                'national_number' => $countryInfo['national'],
                'is_valid' => true,
                'message' => "Detectado como número internacional ({$countryInfo['name']})",
            ];
        }

        // Verificar si parece un número nacional del país por defecto
        $countryPattern = $this->countryPatterns[$defaultCountry] ?? null;

        if ($countryPattern) {
            $expectedLength = $countryPattern['national_length'];
            $lengths = is_array($expectedLength) ? $expectedLength : [$expectedLength];

            if (in_array(strlen($number), $lengths)) {
                // Verificar patrón si existe
                if (isset($countryPattern['pattern']) && preg_match($countryPattern['pattern'], $number)) {
                    $normalized = $defaultCountry . $number;
                    return [
                        'normalized' => $normalized,
                        'format_detected' => 'auto_national',
                        'country_code' => $defaultCountry,
                        'national_number' => $number,
                        'is_valid' => true,
                        'message' => "Detectado como número nacional ({$countryPattern['name']})",
                    ];
                }
            }
        }

        // No se pudo determinar, asumir que es internacional
        return [
            'normalized' => $number,
            'format_detected' => 'auto_unknown',
            'country_code' => null,
            'national_number' => null,
            'is_valid' => true,
            'message' => 'Formato no determinado, se envía sin modificar',
        ];
    }

    /**
     * Detecta el código de país de un número
     */
    protected function detectCountryCode(string $number): ?array
    {
        // Ordenar códigos de país por longitud descendente para evitar falsos positivos
        // (ej: 34 vs 349)
        $sortedCodes = $this->countryPatterns;
        uksort($sortedCodes, fn($a, $b) => strlen($b) - strlen($a));

        foreach ($sortedCodes as $code => $info) {
            if (str_starts_with($number, $code)) {
                $national = substr($number, strlen($code));
                $expectedLength = $info['national_length'];
                $lengths = is_array($expectedLength) ? $expectedLength : [$expectedLength];

                // Verificar que la longitud del número nacional sea correcta
                if (in_array(strlen($national), $lengths)) {
                    return [
                        'code' => $code,
                        'national' => $national,
                        'name' => $info['name'],
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Obtiene información de normalización para mostrar en la UI
     */
    public function getFormatInfo(): array
    {
        return [
            'formats' => [
                'auto' => [
                    'name' => 'Detección Automática',
                    'description' => 'El sistema detecta automáticamente si el número viene en formato nacional o internacional.',
                    'examples' => [
                        '666123456 → 34666123456 (detectado como nacional España)',
                        '34666123456 → 34666123456 (detectado como internacional)',
                        '+34666123456 → 34666123456 (internacional con +)',
                    ],
                ],
                'international' => [
                    'name' => 'Internacional (E.164)',
                    'description' => 'El cliente siempre envía números con código de país. Se acepta con o sin el signo +.',
                    'examples' => [
                        '34666123456 → 34666123456',
                        '+34666123456 → 34666123456',
                        '1234567890 → 1234567890 (se asume internacional)',
                    ],
                ],
                'national_es' => [
                    'name' => 'Nacional España',
                    'description' => 'El cliente envía números en formato nacional español (9 dígitos). Se añade automáticamente el prefijo 34.',
                    'examples' => [
                        '666123456 → 34666123456',
                        '911234567 → 34911234567',
                        '34666123456 → 34666123456 (ya tiene prefijo, no se duplica)',
                    ],
                ],
            ],
            'countries' => array_map(fn($info, $code) => [
                'code' => $code,
                'name' => $info['name'],
                'national_length' => $info['national_length'],
            ], $this->countryPatterns, array_keys($this->countryPatterns)),
        ];
    }

    /**
     * Prueba la normalización de un número con una configuración dada
     */
    public function testNormalization(string $number, string $format, string $countryCode = '34'): array
    {
        // Crear un customer temporal para la prueba
        $customer = new Customer([
            'number_format' => $format,
            'default_country_code' => $countryCode,
            'strip_plus_sign' => true,
            'add_plus_sign' => false,
        ]);

        return $this->normalize($number, $customer);
    }
}
