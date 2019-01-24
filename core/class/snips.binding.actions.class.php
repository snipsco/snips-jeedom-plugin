<?php

class SnipsBindingAction
{
    private $cmd;
    private $options;

    static function dump($_actions_raw = array())
    {
        $actions = array();
        foreach($_actions_raw as $action_raw){
            $actions[] = new self($action_raw);
        }
        return $actions;
    }

    function __construct($_action = array())
    {

    }

    public function execute()
    {
        if ($this->cmd == 'scenario') {

        }
    }

    static function get_scenario_tags($_tags = array())
    {

    }
}