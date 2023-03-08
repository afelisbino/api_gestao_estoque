<?php

namespace App\Libraries;

class Encryption
{

    static function lerChaveArquivo(): string | null
    {
        if (!file_exists(WRITEPATH . "/.key")) {
            file_put_contents(WRITEPATH . "/.key", sodium_crypto_secretbox_keygen());
        }

        return file_get_contents(WRITEPATH . "/.key");
    }

    static function encrypt(string $texto, string $chave): string | null
    {
        $iv = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        if (empty($chave)) return null;

        return sodium_bin2hex($iv . sodium_crypto_secretbox($texto, $iv, $chave));
    }

    static function decrypt(string $textoCryptografado, string $chave): string | null
    {
        $texto = sodium_hex2bin($textoCryptografado);
        $nonce = substr($texto, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cifra = substr($texto, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        if (empty($chave)) return null;

        return sodium_crypto_secretbox_open($cifra, $nonce, $chave);
    }
}
