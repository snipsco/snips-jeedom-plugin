<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class SnipsUtils{
    static function logger($str = '', $level = 'debug')
    {
        $function = debug_backtrace(false, 2)[1]['function'];
        $msg = '['.__CLASS__.'] <'. $function .'> '.$str;
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
    static function fetch_running_config_files($usrename, $password)
    {
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
}