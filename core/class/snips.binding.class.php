<?php

class SnipsBinding
{
    public $name;
    public $enable;

    private $tts_player;
    private $tts_message;
    private $tts_vars = array();

    private $conditions = array();



    static function dump_bindings($bindings_raw = array())
    {
        $bindings = array();

        foreach($bindings_raw as $key => $binding_raw){
            $temp = new self($binding_raw);
        }
        return $bindings;
    }

    function __construct($binding_raw)
    {

    }


}
