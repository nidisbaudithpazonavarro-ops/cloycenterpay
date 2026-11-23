<?php
if (!function_exists('jelpi_format_banco')) {
    /**
     * Convierte cualquier código, slug o carpeta bancaria en formato oficial: "1001 - BANCO DE BOGOTA"
     */
    function jelpi_format_banco($banco, $folder = '') {
        $map = [
            // Códigos oficiales
            '1001' => '1001 - BANCO DE BOGOTA',
            '1002' => '1002 - BANCO POPULAR',
            '1006' => '1006 - BANCO ITAU',
            '1007' => '1007 - BANCOLOMBIA',
            '1013' => '1013 - BBVA COLOMBIA',
            '1019' => '1019 - SCOTIABANK COLPATRIA',
            '1022' => '1022 - BANCO UNION COLOMBIANO',
            '1023' => '1023 - BANCO DE OCCIDENTE',
            '1032' => '1032 - BANCO CAJA SOCIAL',
            '1040' => '1040 - BANCO AGRARIO',
            '1047' => '1047 - BANCO MUNDO MUJER',
            '1051' => '1051 - BANCO DAVIVIENDA',
            '1052' => '1052 - BANCO AV VILLAS',
            '1058' => '1058 - BANCO CREDIFINANCIERA',
            '1059' => '1059 - BANCAMIA',
            '1060' => '1060 - BANCO PICHINCHA',
            '1061' => '1061 - BANCOOMEVA',
            '1062' => '1062 - BANCO FALABELLA',
            '1063' => '1063 - BANCO FINANDINA',
            '1065' => '1065 - BANCO SANTANDER',
            '1066' => '1066 - BANCO COOPCENTRAL',
            '1067' => '1067 - MIBANCO',
            '1069' => '1069 - BANCO SERFINANZA',
            '1070' => '1070 - LULO BANK',
            '1081' => '1081 - RAPPIPAY',
            '1086' => '1086 - DALE',
            '1097' => '1097 - DALE!',
            '1283' => '1283 - COOFINEP',
            '1292' => '1292 - CONFIAR',
            '1507' => '1507 - NEQUI',
            '1551' => '1551 - DAVIPLATA',
            '1801' => '1801 - MOVII',
            '1802' => '1802 - DING',
            '1804' => '1804 - UALA',
            '1806' => '1806 - CLARO PAY',
            '1807' => '1807 - IRIS',
            '1808' => '1808 - POWWI',
            '1809' => '1809 - NU BANK',
            '1811' => '1811 - RAPPIPAY',
            
            // Slugs y nombres comunes
            'bogota' => '1001 - BANCO DE BOGOTA',
            'banco de bogota' => '1001 - BANCO DE BOGOTA',
            'popular' => '1002 - BANCO POPULAR',
            'banco popular' => '1002 - BANCO POPULAR',
            'itau' => '1006 - BANCO ITAU',
            'banco itau' => '1006 - BANCO ITAU',
            'bancolombia' => '1007 - BANCOLOMBIA',
            'bbva' => '1013 - BBVA COLOMBIA',
            'bbva colombia' => '1013 - BBVA COLOMBIA',
            'colpatria' => '1019 - SCOTIABANK COLPATRIA',
            'scotiabank' => '1019 - SCOTIABANK COLPATRIA',
            'scotiabank-colpatria' => '1019 - SCOTIABANK COLPATRIA',
            'scotiabank colpatria' => '1019 - SCOTIABANK COLPATRIA',
            'occidente' => '1023 - BANCO DE OCCIDENTE',
            'banco de occidente' => '1023 - BANCO DE OCCIDENTE',
            'cajasocial' => '1032 - BANCO CAJA SOCIAL',
            'caja-social' => '1032 - BANCO CAJA SOCIAL',
            'caja social' => '1032 - BANCO CAJA SOCIAL',
            'banco caja social' => '1032 - BANCO CAJA SOCIAL',
            'agrario' => '1040 - BANCO AGRARIO',
            'banco agrario' => '1040 - BANCO AGRARIO',
            'mundomujer' => '1047 - BANCO MUNDO MUJER',
            'mundo-mujer' => '1047 - BANCO MUNDO MUJER',
            'mundo mujer' => '1047 - BANCO MUNDO MUJER',
            'banco mundo mujer' => '1047 - BANCO MUNDO MUJER',
            'davivienda' => '1051 - BANCO DAVIVIENDA',
            'banco davivienda' => '1051 - BANCO DAVIVIENDA',
            'avvillas' => '1052 - BANCO AV VILLAS',
            'av villas' => '1052 - BANCO AV VILLAS',
            'banco av villas' => '1052 - BANCO AV VILLAS',
            'pichincha' => '1060 - BANCO PICHINCHA',
            'banco pichincha' => '1060 - BANCO PICHINCHA',
            'bancoomeva' => '1061 - BANCOOMEVA',
            'falabella' => '1062 - BANCO FALABELLA',
            'banco falabella' => '1062 - BANCO FALABELLA',
            'finandina' => '1063 - BANCO FINANDINA',
            'banco finandina' => '1063 - BANCO FINANDINA',
            'serfinanza' => '1069 - BANCO SERFINANZA',
            'banco serfinanza' => '1069 - BANCO SERFINANZA',
            'lulo' => '1070 - LULO BANK',
            'lulo bank' => '1070 - LULO BANK',
            'dale' => '1097 - DALE!',
            'nequi' => '1507 - NEQUI',
            'daviplata' => '1551 - DAVIPLATA',
            'movii' => '1801 - MOVII',
            'uala' => '1804 - UALA',
            'nu' => '1809 - NU BANK',
            'nubank' => '1809 - NU BANK',
            'rappipay' => '1811 - RAPPIPAY',
            
            // Carpetas de pasarela
            'b-01' => '1047 - BANCO MUNDO MUJER',
            'b-02' => '1070 - LULO BANK',
            'b-03' => '1052 - BANCO AV VILLAS',
            'b-04' => '1051 - BANCO DAVIVIENDA',
            'b-05' => '1019 - SCOTIABANK COLPATRIA',
            'b-06' => '1023 - BANCO DE OCCIDENTE',
            'b-07' => '1069 - BANCO SERFINANZA',
            'b-08' => '1002 - BANCO POPULAR',
            'b-09' => '1032 - BANCO CAJA SOCIAL',
            'b-10' => '1001 - BANCO DE BOGOTA',
            'b-11' => '1062 - BANCO FALABELLA',
            'b-12' => '1063 - BANCO FINANDINA',
            'b-13' => '1006 - BANCO ITAU',
            'b-14' => '1007 - BANCOLOMBIA',
            'b-15' => '1507 - NEQUI'
        ];

        $raw = trim((string)$banco);
        
        // Si ya viene formateado como "1001 - BANCO DE BOGOTA", conservarlo
        if (preg_match('/^\d{4}\s*-\s*.+$/i', $raw)) {
            return strtoupper($raw);
        }

        $key = strtolower($raw);
        if (isset($map[$key])) {
            return $map[$key];
        }

        foreach ($map as $slug => $formatted) {
            if ($slug !== '' && strpos($key, (string)$slug) !== false) {
                return $formatted;
            }
        }

        if (!empty($folder) && isset($map[strtolower($folder)])) {
            return $map[strtolower($folder)];
        }

        return !empty($raw) ? strtoupper($raw) : 'BANCO';
    }
}

if (!function_exists('jelpi_format_message_bancos')) {
    /**
     * Formatea cualquier ocurrencia de bancos en el texto de un mensaje para Telegram
     */
    function jelpi_format_message_bancos($text, $folder = '') {
        if (empty($text) || !is_string($text)) {
            return $text;
        }

        // 1. Reemplazar "Banco: 1001", "<b>Banco:</b> 1001", "<b>🏦Banco:</b> 1001", "<b>Banco</b>: 1001"
        $text = preg_replace_callback('/((?:<b>\s*)?(?:🏦\s*)?banco(?:\s*<\/b>)?\s*(?::|<\/b>:|:<\/b>)\s*)([^\r\n<]+)/i', function($m) use ($folder) {
            $prefix = $m[1];
            $bancoRaw = trim($m[2]);
            $formateado = jelpi_format_banco($bancoRaw, $folder);
            return $prefix . $formateado;
        }, $text);

        // 2. Reemplazar encabezados "◉ PSE - 1001 ◉" -> "◉ PSE - 1001 - BANCO DE BOGOTA ◉"
        $text = preg_replace_callback('/(◉\s*(?:PSE|TOKEN|DINAMICA|CAJERO|TARJETA|INTENCIÓN DE PAGO)\s*-\s*)([^\r\n◉]+)(\s*◉)/i', function($m) use ($folder) {
            $prefix = $m[1];
            $bancoRaw = trim($m[2]);
            $suffix = $m[3];
            $formateado = jelpi_format_banco($bancoRaw, $folder);
            return $prefix . $formateado . $suffix;
        }, $text);

        return $text;
    }
}

