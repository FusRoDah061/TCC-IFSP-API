<?php

namespace App\Util;

use Hashids\Hashids;

/**
 * Source: https://medium.com/sammich-shop/using-hashids-and-laravel-route-binding-to-obfuscate-auto-incrementing-ids-e6c0a328dfb5
 */
class Hasher
{
    public static function encode(...$args)
    {
        return app(Hashids::class)->encode(...$args);
    }

    public static function decode($enc)
    {
        if (is_int($enc)) {
            return $enc;
        }
        return app(Hashids::class)->decode($enc)[0];
    }
    
    public static function decodeAll($array) {
        $decoded = array();
        
        foreach ($array as $value) {
            array_push($decoded, Hasher::decode($value));
        }
        
        return $decoded;
    }
    
    public static function encodeAll($array) {
        $encoded = array();
        
        foreach ($array as $value) {
            array_push($encoded, Hasher::encode($value));
        }
        
        return $encoded;
    }
}
