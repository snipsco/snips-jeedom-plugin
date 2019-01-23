<?php

require_once dirname(__FILE__) . '/../../../core/class/cron.class.php';

class SnipsHandler
{
    static function logger($str)
    {
        log::add('snips', 'debug', $str);
    }

    static function intent_detected($payload){
        self::logger('['.__CLASS__.'] ['.__FUNCTION__.'] called : '.$payload);

    }

    static function session_started($payload){
        self::logger('['.__CLASS__.'] ['.__FUNCTION__.'] called : '.$payload);

    }

    static function session_ended($payload){
        self::logger('['.__CLASS__.'] ['.__FUNCTION__.'] called : '.$payload);

    }

    static function hotword_detected($payload){
        self::logger('['.__CLASS__.'] ['.__FUNCTION__.'] called : '.$payload);

    }
}