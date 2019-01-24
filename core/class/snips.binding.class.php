<?php

class SnipsBinding
{
    public $name;
    public $enable;

    private $tts_player;
    private $tts_message;
    private $tts_vars = array();

    private $conditions = array();

    private $nsr_slots = array();


    static function dump($bindings_raw = array())
    {
        $bindings = array();

        foreach($bindings_raw as $key => $binding_raw){
            $temp = new self($binding_raw);
        }
        return $bindings;
    }

    function __construct($binding_raw)
    {
        ;//reserved to the next update
    }
}
