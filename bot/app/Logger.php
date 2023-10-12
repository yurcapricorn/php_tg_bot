<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 17-Aug-22
 * Time: 15:06
 */

namespace App;

class Logger
{
    static function logMessage($message) {
        if(empty($message)){return;}
        if ($message->getFrom()->getUsername() === 'Yur_capricorn') {
            return;
        }

        $fh = fopen(__DIR__ . '/../logs/' . date('Y-m-d', time()) . '.log', 'a+');
        fwrite($fh, print_r([
            'time' => date('H:i:s', time()),
            'firstname' => $message->getFrom()->getFirstName(),
            'secondname' => $message->getFrom()->getLastName(),
            'nickname' => $message->getFrom()->getUsername(),
            'user_id' => $message->getFrom()->getId(),
            'group' => $message->getChat()->getTitle(),
            'group_id' => $message->getChat()->getId(),
            'text' => $message->getText()], true));
        fclose($fh);
    }
    static function log($str) {
        $fn = __DIR__ . '/../logs/' . date('Y-m-d', time()) . '.log';
        if(!file_exists($fn)){
            $fn = fopen($fn,'w');
            fclose($fn);
        }
        file_put_contents($fn, date('H:i:s', time()) . ' ' . $str . PHP_EOL, FILE_APPEND);
    }

}