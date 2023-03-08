<?php

/**
 * UUID Class
 *
 * This implements the abilities to create UUID's for CodeIgniter.
 * Code has been borrowed from the followinf comments on php.net
 * and has been optimized for CodeIgniter use.
 * http://www.php.net/manual/en/function.uniqid.php#94959
 *
 * @category Libraries
 * @author Dan Storm
 * @link http://catalystcode.net/
 * @license GNU LPGL
 * @version 2.1
 */

namespace App\Libraries;

class Uuid
{
    static function v4(bool $trim = false): string
    {

        $format = ($trim == false) ? '%04x%04x-%04x-%04x-%04x-%04x%04x%04x' : '%04x%04x-%04x-%04x-%04x-%04x%04x%04x';

        return sprintf(
            $format,

            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    static function is_valid(string $uuid): bool
    {
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid);
    }
}
