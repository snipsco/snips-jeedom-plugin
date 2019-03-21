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
        // exception: scenario
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

        // exception: percentage remapping
        if (
            key_exists('LT', $this->options) ||
            key_exists('HT', $this->options)
        ) {
            $intentEq = eqLogic::byLogicalId(
                $this->intent,
                'snips'
            );
            $cmds = $intentEq->getCmd();
            foreach ($cmds as $cmd) {
                $slot_type = $cmd->getConfiguration('entityId');
                if ($slot_type == 'snips/percentage') {
                    $org = $cmd->getValue();
                    $real_value = SnipsUtils::remap_percentage_to_value(
                        $this->options['LT'],
                        $this->options['HT'],
                        $org
                    );
                    $cmd->setConfiguration('orgVal', $org);
                    $intentEq->checkAndUpdateCmd($cmd, $real_value);
                    $cmd->setValue($real_value);
                    $cmd->save();
                }
            }
        }

        return scenarioExpression::createAndExec(
            'action',
            $this->cmd,
            $this->options
        );
    }
}