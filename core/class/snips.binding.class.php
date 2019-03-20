<?php
require_once dirname(__FILE__) . '/snips.utils.class.php';
require_once dirname(__FILE__) . '/snips.tts.class.php';
require_once dirname(__FILE__) . '/snips.binding.condition.class.php';
require_once dirname(__FILE__) . '/snips.binding.action.class.php';

class SnipsBinding
{
    public $name;
    public $enable;

    private $tts_player;
    private $tts_message;
    private $tts_vars = array();

    private $conditions = array();
    private $actions = array();

    private $callback_scenario;
    private $nsr_slots = array();

    /* generate a list of binding objects by raw configuration array */
    static function dump($bindings_raw = array())
    {
        $bindings = array();
        foreach($bindings_raw as $key => $binding_raw){
            $bindings[] = new self($binding_raw);
        }
        return $bindings;
    }

    /* select some bindings from the given list which can pass condtiion */
    static function get_bindings_match_condition($bindings, $slots)
    {
        SnipsUtils::logger('bindings found :'.count($bindings));
        SnipsUtils::logger('coming slot number :'.count($slots));
        $res_by_slot = self::get_bindings_match_slots_name($bindings, $slots);

        $res = array();
        // check each binding
        foreach($res_by_slot as $binding) {
            if ($binding->is_all_condition_matched() && $binding->enable) {
                $res[] = $binding;
            }
        }

        return $res;
    }

    /* select some bindings from the given list by detected slots */
    static function get_bindings_match_slots_name($bindings, $slots)
    {
        SnipsUtils::logger();
        $res = array();
        foreach ($bindings as $binding) {
            // compare the slots number with necessary slots number
            if (count($binding->nsr_slots) == count($slots)) {
                SnipsUtils::logger('Binding has good number of slot: '. $binding->name);
                // slot all exist indicator
                $indicator = 1;
                foreach ($binding->nsr_slots as $nsr_slot) {
                    $indicator *= array_key_exists($nsr_slot, $slots) ? 1 : 0;
                }
                if ($indicator) {
                    $res[] = $binding;
                }
            }
        }
        return $res;
    }

    function __construct($binding_raw)
    {
        $this->name = $binding_raw['name'];
        $this->enable = $binding_raw['enable'] == '0' ? false : true;

        $this->tts_player = $binding_raw['ttsPlayer'];
        $this->tts_message = $binding_raw['ttsMessage'];
        $this->tts_vars = $binding_raw['ttsVar'];

        $this->conditions = SnipsBindingCondition::dump($binding_raw['condition']);
        $this->actions = SnipsBindingAction::dump($binding_raw['action']);

        $this->nsr_slots = $binding_raw['nsr_slots'];
    }

    /* generate tts message */
    function get_tts_message()
    {
        $tts = new SnipsTts($this->tts_message, (array)$this->tts_vars);
        return $tts->get_message();
    }

    function execute_all()
    {
        foreach ($this->actions as $action) {
            $action->execute();
        }
    }

    /* check if all the condition are true */
    function is_all_condition_matched()
    {
        $res = 1;
        // check each condition expression
        foreach ($this->conditions as $condition) {
            $res *= $condition->is_true() ? 1 : 0;
        }
        return $res ? true : false;
    }
}
