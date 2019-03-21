<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class SnipsBindingAction
{
    private $intent;
    private $cmd;
    private $options = array();

    static function dump($actions_raw = array(), $intent)
    {
        $actions = array();
        foreach($actions_raw as $action_raw){
            $actions[] = new self($action_raw, $intent);
        }
        return $actions;
    }

    function __construct($action = array(), $intent)
    {
        $this->intent = $intent;
        $this->cmd = $action['cmd'];
        $this->options = $action['options'];
    }

    public function execute()
    {
        $res = scenarioExpression::createAndExec(
            'action',
            $this->cmd,
            $this->options
        );

        return $res;
    }

    static function get_scenario_tags($_tags = array())
    {
        ;//reserved to the next update
    }
}