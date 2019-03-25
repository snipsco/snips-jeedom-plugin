<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class SnipsBindingCondition
{
    private $pre;
    private $relation;
    private $aft;

    static function dump($conditions_raw = array())
    {
        $conditions = array();
        foreach($conditions_raw as $condition_raw){
            $conditions[] = new self($condition_raw);
        }
        return $conditions;
    }

    function __construct($condition = array())
    {
        // slot cmd id
        $this->pre = $condition['pre'];
        $this->relation = $condition['relation'];

        // slot cmd value
        $this->aft = $condition['aft'];
    }

    public function is_true($slots)
    {
        switch ($this->relation) {
            case '=':
                return $this->is_equal();
            default:
                return false;
        }
    }

    /* relations to compare */
    private function is_equal()
    {
        $value_received = $this->get_pre_value();
        $value_set = $this->get_aft_value();

        return in_array($value_received, $value_set);
    }

    /* get the numeric values */
    private function get_pre_value()
    {
        $cmd = cmd::byId($this->pre);
        if (is_string($cmd->getCache('value', 'NULL')))
            /* remove speace, all lower case */
            return strtolower(str_replace(' ', '', $cmd->getCache('value', 'NULL')));
        else
            return $cmd->getCache('value', 'NULL');
    }

    /* get split synonymes to an array */
    private function get_aft_value()
    {
        /* remove speace, all lower case */
        return explode(',',strtolower(str_replace(' ', '', $this->aft)));
    }
}