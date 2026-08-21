<?php

namespace App\Libraries;

class NumeroALetras
{
    private const UNIDADES = [
        '', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
        'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE',
    ];

    private const DECENAS = [
        '', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA',
    ];

    private const CENTENAS = [
        '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS',
        'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS',
    ];

    private const MONEDAS = [
        'PEN' => 'SOLES',
        'USD' => 'DOLARES AMERICANOS',
    ];

    public function convertir(float $monto, string $moneda = 'PEN'): string
    {
        $entero = (int) floor(round($monto, 2));
        $centimos = (int) round((round($monto, 2) - $entero) * 100);

        $enteroTexto = $entero === 0 ? 'CERO' : $this->convertirEntero($entero);
        $nombreMoneda = self::MONEDAS[$moneda] ?? $moneda;

        return sprintf('SON %s CON %02d/100 %s', $enteroTexto, $centimos, $nombreMoneda);
    }

    private function convertirEntero(int $numero): string
    {
        if ($numero < 1000000) {
            return $this->convertirMiles($numero);
        }

        $millones = intdiv($numero, 1000000);
        $resto = $numero % 1000000;

        $textoMillones = $millones === 1
            ? 'UN MILLON'
            : $this->convertirMiles($millones) . ' MILLONES';

        return trim($textoMillones . ($resto > 0 ? ' ' . $this->convertirMiles($resto) : ''));
    }

    private function convertirMiles(int $numero): string
    {
        if ($numero < 1000) {
            return $this->convertirCentenas($numero);
        }

        $miles = intdiv($numero, 1000);
        $resto = $numero % 1000;

        $textoMiles = $miles === 1 ? 'MIL' : $this->convertirCentenas($miles) . ' MIL';

        return trim($textoMiles . ($resto > 0 ? ' ' . $this->convertirCentenas($resto) : ''));
    }

    private function convertirCentenas(int $numero): string
    {
        if ($numero === 100) {
            return 'CIEN';
        }

        $centenas = intdiv($numero, 100);
        $resto = $numero % 100;

        $texto = self::CENTENAS[$centenas] ?? '';
        if ($resto > 0) {
            $texto = trim($texto . ' ' . $this->convertirDecenas($resto));
        }

        return $texto;
    }

    private function convertirDecenas(int $numero): string
    {
        if ($numero < 20) {
            return self::UNIDADES[$numero];
        }

        if ($numero === 20) {
            return 'VEINTE';
        }

        $decena = intdiv($numero, 10);
        $unidad = $numero % 10;

        if ($decena === 2) {
            return $unidad > 0 ? 'VEINTI' . self::UNIDADES[$unidad] : 'VEINTE';
        }

        $texto = self::DECENAS[$decena];
        if ($unidad > 0) {
            $texto .= ' Y ' . self::UNIDADES[$unidad];
        }

        return $texto;
    }
}
