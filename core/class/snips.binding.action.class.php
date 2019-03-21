<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/snips.binding.scenario.class.php';
require_once dirname(__FILE__) . '/snips.class.php';
require_once dirname(__FILE__) . '/snips.utils.class.php';

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

    /* execute action */
    function execute()
    {
        if ($this->cmd == 'scenario') {
            $intentEq = eqLogic::byLogicalId(
                $this->intent,
                'snips'
            );
            return $intentEq->get_callback_scenario(
                $this->options['scenario_id'],
                $this->options['action'],
                $this->options['tags']
            )->execute();
        }

        return scenarioExpression::createAndExec(
            'action',
            $this->cmd,
            $this->options
        );
    }
}