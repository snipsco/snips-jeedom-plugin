<?php

class SnipsBindingCondition
{
    private $pre;
    private $relation;
    private $aft;

    static function dump($_conditions_raw = array())
    {
        $conditions = array();
        foreach($_conditions_raw as $condition_raw){
            $condition[] = new self($condition_raw);
        }
        return $conditions;
    }

    function __construct($_condition = array())
    {
        $this->pre = $_condition['pre'];
        $this->relation = $_condition['relation'];
        $this->aft = $_condition['aft'];
    }

    public function is_true()
    {
        if('=' == $this->condition_relation)
            return $this->if_equal();
    }

    /* relations to compare */
    private function if_equal()
    {
        $pv = $this->get_pre_value();
        $av = $this->get_aft_value();

        return in_array($pv, $av);
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

    private function get_aft_value()
    {
        /* remove speace, all lower case */
        return explode(',',strtolower(str_replace(' ', '', $this->aft)));
    }
}