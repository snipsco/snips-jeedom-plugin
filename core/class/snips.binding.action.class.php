<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/snips.binding.scenario.class.php';
require_once dirname(__FILE__) . '/snips.class.php';
require_once dirname(__FILE__) . '/snips.utils.class.php';

class SnipsBindingAction
{
    private $intent_id;
    private $cmd;
    private $options = array();

    static function dump($actions_raw = array(), $intent_id)
    {
        $actions = array();
        foreach($actions_raw as $action_raw){
            $actions[] = new self($action_raw, $intent_id);
        }
        return $actions;
    }

    function __construct($action = array(), $intent_id)
    {
        $this->intent_id = $intent_id;
        $this->cmd = $action['cmd'];
        $this->options = $action['options'];
    }

    /* execute action */
    function execute()
    {
        // exception: scenario
        if ($this->cmd == 'scenario') {
            $intentEq = eqLogic::byLogicalId(
                $this->intent_id,
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
                $this->intent_id,
                'snips'
            );
            if (!is_object($intentEq)) {
                throw new Exception(__('Can not find eqLogic by intent id: '. $this->intent_id, __FILE__));
            }
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