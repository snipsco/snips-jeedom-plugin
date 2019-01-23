<?php
require_once dirname(__FILE__) . '/../../../core/class/log.class.php';

class SnipsTts{

    private $raw_message = '';
    private $var_list = array();

    private $raw_message_with_var = '';
    private $ready_messages = array();

    static function dump($_raw_message, $_var_list = array())
    {
        $T = new SnipsTts($_raw_message, $_var_list);
        return $T;
    }

    function __construct($_raw_message, $_var_list = array())
    {
        $this->raw_message = $_raw_message;
        $this->var_list = $_var_list;

        $this->fill_vars_to_raw();
        $this->generate();
    }

    public function get_message()
    {
        return $this->ready_messages[array_rand($this->ready_messages)];
    }

    private function fill_vars_to_raw()
    {
        log::add('snips', 'debug', 'fill vars to raw :'.$this->raw_message);

        $string_subs = explode('{#}', $this->raw_message);
        $speaking_text = '';
        if (!empty($string_subs)) {
            foreach($string_subs as $key => $sub) {
                if (isset($this->var_list[$key])) {
                    $cmd = cmd::byString($this->var_list[$key]['cmd']);
                    log::add('snips', 'debug', 'cmd is '.$this->var_list[$key]['cmd']);
                    if (is_object($cmd)) {
                        if ($cmd->getName() == 'intensity_percent' || $cmd->getName() == 'intensity_percentage') {
                            $sub.= $cmd->getConfiguration('orgVal');
                        }else if($cmd->getSubType() == 'binary'){
                            if($cmd->getCache('value', ' ') == 0) $sub .= $this->var_list[$key]['options']['zero'];
                            if($cmd->getCache('value', ' ') == 1) $sub .= $this->var_list[$key]['options']['one'];
                        }else {
                            if ($cmd->getValue()) {
                                $sub.= $cmd->getValue();
                            }
                            else {
                                $sub.= $cmd->getCache('value', 'NULL');
                            }
                        }
                    }
                }
                else {
                    $sub.= '';
                }
                $speaking_text.= $sub;
            }
            $this->raw_message_with_var = $speaking_text;
        }
        else {
            $this->raw_message_with_var = $this->raw_message;
        }
    }

    private function generate()
    {
        $this->ready_messages = interactDef::generateTextVariant($this->raw_message_with_var);
    }

}