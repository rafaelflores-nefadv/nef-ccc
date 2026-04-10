<?php

namespace App\Support;

class ConfiguracaoSistema
{
    /**
     * @return array<string, string>
     */
    public static function timezonesBrasil(): array
    {
        return [
            'America/Rio_Branco' => 'Acre (UTC-05:00)',
            'America/Manaus' => 'Amazonas (UTC-04:00)',
            'America/Cuiaba' => 'Mato Grosso (UTC-04:00)',
            'America/Sao_Paulo' => 'Brasília / São Paulo (UTC-03:00)',
            'America/Noronha' => 'Fernando de Noronha (UTC-02:00)',
        ];
    }

    /**
     * @return list<string>
     */
    public static function valoresTimezoneBrasil(): array
    {
        return array_keys(self::timezonesBrasil());
    }

    /**
     * @return list<string>
     */
    public static function slugsProvedoresWhatsappSuportados(): array
    {
        return ['meta', 'waha'];
    }
}

