<?php

namespace App;

class Curl
{

    public static $time;

    public static function makeRequest(String $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        if ($err !== '') {
            var_dump($err);
            static::makeRequest($url);
//            return ['err' => $err];
        }
        curl_close($ch);
        return $response;
    }


    public static function wait() {
        if (empty(static::$time)) {
            static::$time = microtime(true);
            return;
        } elseif (microtime(true) - static::$time < 1) {
            sleep(1 - microtime(true) - static::$time);
            return;
        } else {
            return;
        }


    }
}