<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class SnipsUtils
{
    /* logger */
    static function logger($str = '', $level = 'debug')
    {
        $function_name = debug_backtrace(false, 2)[1]['function'];
        $class_name = debug_backtrace(false, 2)[1]['class'];
        $msg = '['.$class_name.'] <'. $function_name .'> '.$str;
        log::add('snips', $level, $msg);
    }

    /* return an array of jeedom intents (id or name) */
    static function get_intents_from_assistant_json($path, $if_id = false)
    {
        $assistant_json_string = file_get_contents($path);
        $intents = json_decode($assistant_json_string, true)['intents'];
        $res_name = array();
        $res_id = array();
        foreach($intents as $intent) {
            if (strpos( strtolower($intent['name']), 'jeedom')) {
                $res_name[] = $intent['name'];
                $res_id[] = $intent['id'];
            }
        }
        return $if_id ? $res_id : $res_name;
    }

    /* generate 10 characters client id */
    static function generate_client_id()
    {
        $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
        'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
        't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
        'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
        'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

        $keys =array_rand($chars, 10);
        $client_id ='';
        for ($i = 0; $i < 10; $i++) {
            $client_id .= $chars[$keys[$i]];
        }
        return $client_id;
    }

    /* fetch assistant.json & snips.toml from master device */
    static function fetch_running_config_files(
        $usrename = 'pi',
        $password = 'raspberry'
    ) {
        $ip_addr = config::byKey('mqttAddr', 'snips', '127.0.0.1');
        self::logger('Trying to connect to: '.$ip_addr);

        $connection = ssh2_connect($ip_addr, 22);
        if (!$connection) {
            self::logger('Connection: Faild code: -2');
            return -2;
        }

        $resp = ssh2_auth_password($connection, $usrename, $password);
        if ($resp) {
            self::logger('Verification: Success');
        }
        else {
            self::logger('Verification: Faild code: -1');
            return -1;
        }

        $res_assistant_json = ssh2_scp_recv(
            $connection,
            '/usr/share/snips/assistant/assistant.json',
            dirname(__FILE__) .'/../../config_running/assistant.json'
        );

        $res_snips_toml = ssh2_scp_recv(
            $connection,
            '/etc/snips.toml',
            dirname(__FILE__) .'/../../config_running/snips.toml'
        );
        if ($res_assistant_json && $res_snips_toml) {
            ssh2_exec($connection, 'exit');
            unset($connection);
            self::logger('Fecth resutlt : Success');
            return 1;
        }
        else {
            ssh2_exec($connection, 'exit');
            unset($connection);
            self::logger('Fecth resutlt : Faild code: 0');
            return 0;
        }
    }

    /* play test sound on the specified device */
    static function find_device($site_id){
        $lang = object::byName('Snips-Intents')->getConfiguration('language');

        switch ($lang) {
            case 'fr':
                $text = 'Dispositif '. $site_id .' est ici!';
                break;
            case 'en':
                $text = 'Device '. $site_id .' is here!';
                break;
            default:
                $text = 'Device '. $site_id .' is here!';
                break;
        }

        snips::hermes()->publish_start_session_notification($_site_id, $text);
    }

    /* remap percentage to a value */
    static function remap_percentage_to_value($low_offset, $high_offset, $percentage)
    {
        $real_value = ($high_offset - $low_offset) * ($percentage / 100);
        if ($real_value > $high_offset) {
            $real_value = $high_offset;
        }
        if ($real_value < $low_offset) {
            $real_value = $low_offset;
        }
        return $real_value;
    }

    /* either delete or create a variable */
    static function update_scenario_variable($var_name)
    {
        if(!config::byKey($var_name, 'snips', 0)){
            $var = dataStore::byTypeLinkIdKey('scenario', -1, $var_name);
            if (is_object($var)) {
                $var->remove();
            }
            self::logger('Removed variable '. $var_name);
        }else{
            $var = dataStore::byTypeLinkIdKey('scenario', -1, $var_name);
            if (!is_object($var)) {
                $var = new dataStore();
                $var->setKey($var_name);
                self::logger('Created variable '. $var_name);
            }
            $var->setValue('');
            $var->setType('scenario');
            $var->setLink_id(-1);
            $var->save();
        }
    }

    /* set a variable */
    static function set_scenario_variable($name, $value)
    {
        $var = dataStore::byTypeLinkIdKey('scenario', -1, $name);
        if (is_object($var)) {
            $var->setValue($value);
            $var->save();
        }
    }

    /* clear a variable */
    static function reset_scenario_variable($name)
    {
        $var = dataStore::byTypeLinkIdKey('scenario', -1, $name);
        if (is_object($var)) {
            $var->setValue('');
            $var->save();
        }
    }

    /* export bindings to a file or json string */
    static function export_bindings($name, $is_output = true)
    {
        $binding_conf = array();
        $eqs = eqLogic::byType('snips');
        foreach ($eqs as $eq) {
            $org_bindings = $eq->getConfiguration('bindings');
            $json_string = json_encode($org_bindings);

            preg_match_all('/#[^#]*[0-9]+#/', $json_string, $matches);
            $human_cmd = cmd::cmdToHumanReadable($matches[0]);
            foreach ($human_cmd as $key => $cmd_text) {
                $json_string = str_replace(
                    $matches[0][$key],
                    $cmd_text,
                    $json_string
                );
            }

            preg_match_all(
                '/("pre":")[^("pre":")]*[0-9]+"/',
                $json_string,
                $matches_c
            );
            $matches_c1 = $matches_c;
            foreach ($matches_c[0] as $key => $value) {
                $matches_c[0][$key] = str_replace('"pre":"', '#', $value);
                $matches_c[0][$key] = str_replace('"', '#', $matches_c[0][$key]);
            }

            $humand_cond = cmd::cmdToHumanReadable($matches_c[0]);
            foreach ($humand_cond as $key => $cmd_text) {
                $json_string = str_replace(
                    $matches_c1[0][$key],
                    '"pre":"'. $cmd_text .'"',
                    $json_string
                );
            }

            $aft_bindings = json_decode($json_string);
            $binding_conf[$eq->getName()] = $aft_bindings;
        }

        if ($is_output) {
            $file = fopen(
                dirname(__FILE__) .'/../../config_backup/'. $name .'.json',
                'w'
            );
            $res = fwrite($file, json_encode($binding_conf));
            self::logger($res ? 'Success' : 'Faild');
        } else {
            return json_encode($binding_conf);
        }
    }

    /* import bindings from file or json string */
    static function import_bindings($file_name = null, $config_json = null)
    {
        if (isset($config_json) && !isset($file_name)) {
            self::logger('Internally reload config info.');
            $json_string = $config_json;
        }else if (!isset($config_json) && isset($file_name)) {
            self::logger('Import config file: ' . $file_name);
            $json_string = file_get_contents(
                dirname(__FILE__) .'/../../config_backup/'. $file_name
            );
        }

        preg_match_all('/("pre":")(#.*?#)(")/', $json_string, $matches);
        $cmd_ids = cmd::humanReadableToCmd($matches[2]);

        foreach($cmd_ids as $key => $cmd_id) {
            $cmd_id = str_replace('#', '', $cmd_id);
            $json_string = str_replace(
                '"pre":"'.$matches[2][$key].'"',
                '"pre":"'.$cmd_id.'"',
                $json_string
            );
        }

        $data = json_decode($json_string, true);
        $eqs = eqLogic::byType('snips');
        foreach($eqs as $eq) {
            if (
                $data[$eq->getName()] != '' &&
                isset($data[$eq->getName()])
            ) {
                $eq->setConfiguration('bindings', $data[$eq->getName() ]);
                $eq->save(true);
            }
        }
    }

    /* convert raw slots to slot_values array */
    static function extract_slots_value($payload_slots_object)
    {
        self::logger();
        $res = array();

        foreach ($payload_slots_object as $slot) {
            switch ($slot->{'entity'}) {
                case 'snips/duration':
                    $total_seconds = 0;
                    $total_seconds += $slot->{'value'}->{'weeks'} * 604800;
                    $total_seconds += $slot->{'value'}->{'days'} * 86400;
                    $total_seconds += $slot->{'value'}->{'hours'} * 3600;
                    $total_seconds += $slot->{'value'}->{'minutes'} * 60;
                    $total_seconds += $slot->{'value'}->{'seconds'};
                    $value = (string)$total_seconds;
                    break;
                default:
                    $value = (string)$slot->{'value'}->{'value'};
                    break;
            }

            // multi-slots value is separated by '&&'
            if (array_key_exists($slot->{'slotName'}, $res)) {
                $res[$slot->{'slotName'}] .= '&&'.$value;
            } else {
                $res[$slot->{'slotName'}] = $value;
            }
        }
        return $res;
    }

    /* set received slot value to cmd object */
    static function set_slot_cmd($slots_values, $intent, $options = null)
    {
        self::logger();
        $eq = eqLogic::byLogicalId($intent, 'snips');

        if (!is_object($eq)) {
            self::logger('no entiry:' . $intent);
            return;
        }

        foreach ($slots_values as $slot => $value) {
            self::logger('slots name is :' . $slot);
            $cmd = $eq->getCmd(null, $slot);
            if (is_object($cmd)) {
                if ($options) {
                    $slot_type = $cmd->getConfiguration('entityId');
                    if ($slot_type == 'snips/percentage') {
                        $org = $value;
                        //change to utils
                        //$value = snips::percentageRemap($options['LT'], $options['HT'], $value);
                        $value = SnipsUtils::remap_percentage_to_value(
                            $options['LT'],
                            $options['HT'],
                            $value
                        );
                        $cmd->setConfiguration('orgVal', $org);
                        self::logger('percentage converted to :' . $value);
                    }
                }
                $eq->checkAndUpdateCmd($cmd, $value);
                $cmd->setValue($value);
                $cmd->save();
            }
        }
    }

    /* reset cmd object value to empty */
    static function reset_slots_cmd($slots = false, $intent = false)
    {
        self::logger();
        if ($slots == false && $intent == false) {
            // clear cmd value for all the intents
            $eqs = eqLogic::byType('snips');
            foreach ($eqs as $eq) {
                $cmds = $eq->getCmd();
                foreach ($cmds as $cmd) {
                    $cmd->setCache('value', null);
                    $cmd->setValue(null);
                    $cmd->setConfiguration('orgVal', null);
                    $cmd->save();
                }
            }

            $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSiteId');
            if (is_object($var)) {
                $var->setValue();
                $var->save();
                self::logger('set '.$var->getValue().' => snipsMsgSiteId');
            }
        } else {
            // clear cmd value for a specified intent
            $eq = eqLogic::byLogicalId($intent, 'snips');
            foreach ($slots as $slot) {
                $cmd = $eq->getCmd(null, $slot);
                $cmd->setCache('value');
                $cmd->setValue(null);
                $cmd->setConfiguration('orgVal', null);
                $cmd->save();
            }
        }
    }

    /* external functions (should be called from scenario code block)*/
    /* help user to realise light brightness shifting */
    static function light_brightness_shift($json_lights)
    {
        $json = json_decode($json_lights, true);
        $lights = $json['LIGHTS'];
        $operation = $json['OPERATION'];
        foreach ($lights as $light) {
            $cmd = cmd::byString($light['LIGHT_BRIGHTNESS_VALUE']);
            if (is_object($cmd)) {
                $val_temp = $cmd->getValue();
                $val_cache = $cmd->getCache('value', 'NULL');
                $current_val = $val_temp ? $val_temp : $val_cache;
            }

            $change = round(
                ($light['MAX_VALUE'] - $light['MIN_VALUE']) * $light['STEP_VALUE']
            );

            $options = array();
            if ($operation === 'UP') {
                $options['slider'] = $current_val + $change;
            } else if ($operation === 'DOWN'){
                $options['slider'] = $current_val - $change;
            }

            if ($options['slider'] < $light['MIN_VALUE']) {
                $options['slider'] = $light['MIN_VALUE'];
            }
            if ($options['slider'] > $light['MAX_VALUE']) {
                $options['slider'] = $light['MAX_VALUE'];
            }
            $cmdSet = cmd::byString($light['LIGHT_BRIGHTNESS_ACTION']);
            if (is_object($cmdSet)) {
                $cmdSet->execCmd($options);
                self::logger('Shift ' . $cmdSet->getHumanName() . ', from -> ' . $options['slider'] . ' to ->' . $current_val);
            }else{
                self::logger('Can not find cmd: '. $light['LIGHT_BRIGHTNESS_ACTION']);
            }
        }
    }
}