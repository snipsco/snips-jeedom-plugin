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