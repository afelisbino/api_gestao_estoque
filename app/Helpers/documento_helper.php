<?php

if (!function_exists('removerCaracteres')) {

    function removerCaracteres($valor): string
    {
        $valor = trim($valor);
        $valor = str_replace(".", "", $valor);
        $valor = str_replace(",", "", $valor);
        $valor = str_replace("-", "", $valor);
        $valor = str_replace("/", "", $valor);
        $valor = str_replace("(", "", $valor);
        $valor = str_replace(")", "", $valor);
        $valor = str_replace(" ", "", $valor);

        return $valor;
    }
}

if (!function_exists('validarCpf')) {
    function validarCpf($cpf = null): bool
    {
        if (empty($cpf)) {
            return false;
        }

        $cpf = removerCaracteres($cpf);

        if (strlen($cpf) != 11) {
            return false;
        } else if (
            $cpf == '00000000000' ||
            $cpf == '11111111111' ||
            $cpf == '22222222222' ||
            $cpf == '33333333333' ||
            $cpf == '44444444444' ||
            $cpf == '55555555555' ||
            $cpf == '66666666666' ||
            $cpf == '77777777777' ||
            $cpf == '88888888888' ||
            $cpf == '99999999999'
        ) {
            return false;
        } else {

            for ($t = 9; $t < 11; $t++) {

                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf[$c] * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf[$c] != $d) {
                    return false;
                }
            }

            return true;
        }
    }
}

if (!function_exists('validarCnpj')) {
    function validarCnpj($cnpj)
    {
        if (empty($cnpj))
            return false;
        $j = 0;
        for ($i = 0; $i < (strlen($cnpj)); $i++) {
            if (is_numeric($cnpj[$i])) {
                $num[$j] = $cnpj[$i];
                $j++;
            }
        }
        if (count($num) != 14)
            return false;
        if ($num[0] == 0 && $num[1] == 0 && $num[2] == 0 && $num[3] == 0 && $num[4] == 0 && $num[5] == 0 && $num[6] == 0 && $num[7] == 0 && $num[8] == 0 && $num[9] == 0 && $num[10] == 0 && $num[11] == 0)
            $isCnpjValid = false;
        else {
            $j = 5;
            for ($i = 0; $i < 4; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $j = 9;
            for ($i = 4; $i < 12; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $resto = $soma % 11;
            if ($resto < 2)
                $dg = 0;
            else $dg = 11 - $resto;
            if ($dg != $num[12])
                $isCnpjValid = false;
        }

        if (!isset($isCnpjValid)) {
            $j = 6;
            for ($i = 0; $i < 5; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $j = 9;
            for ($i = 5; $i < 13; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }
            $soma = array_sum($multiplica);
            $resto = $soma % 11;
            if ($resto < 2)
                $dg = 0;
            else $dg = 11 - $resto;
            if ($dg != $num[13])
                $isCnpjValid = false;
            else $isCnpjValid = true;
        }
        return $isCnpjValid;
    }
}

if (!function_exists('formatarCpf')) {
    function formatarCpf($documento)
    {
        $documento = removerCaracteres($documento);

        $tipoDoc = strlen($documento);
        $tamanhoString = 11;

        if ($tipoDoc > 11) {
            return null;
        }

        if (substr($documento, -6, -3) != '000') {
            $mask = '###.###.###-##';
        } else {
            return null;
            $mask = '##.###.###/####-##';
            $tamanhoString = 14;
        }

        $documento = str_pad($documento, $tamanhoString, '0', STR_PAD_LEFT);

        foreach (str_split($documento) as $numero) {
            $mask = preg_replace('/\#/', $numero, $mask, 1);
        }

        return $mask;
    }
}

if (!function_exists('formatarCnpj')) {
    function formatarCnpj($documento)
    {
        $documento = removerCaracteres($documento);

        $tipoDoc = strlen($documento);

        if ($tipoDoc < 14) {
            return null;
        }

        $mask = '##.###.###/####-##';
        $tamanhoString = 14;

        $documento = str_pad($documento, $tamanhoString, '0', STR_PAD_LEFT);

        foreach (str_split($documento) as $numero) {
            $mask = preg_replace('/\#/', $numero, $mask, 1);
        }

        return $mask;
    }
}

if (!function_exists('mascararCpf')) {
    function mascararCpf($doc)
    {
        return (strlen($doc) == 11) ? substr_replace($doc, '******', 3, 6) : null;
    }
}

if (!function_exists('mascararCnpj')) {
    function mascararCnpj($doc)
    {
        return (strlen($doc) == 14) ? substr_replace($doc, '*********', 3, 9) : null;
    }
}
