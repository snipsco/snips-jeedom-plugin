<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../3rdparty/Toml.php';
require_once dirname(__FILE__) . '/snips.hermes.class.php';
require_once dirname(__FILE__) . '/snips.tts.class.php';
require_once dirname(__FILE__) . '/snips.handler.class.php';
require_once dirname(__FILE__) . '/snips.utils.php';
// ini_set("display_errors","On");
// error_reporting(E_ALL);

class snips extends eqLogic
{
    const NAME_OBJECT = 'Snips-Intents';
    //const PATH_ASSISTANT_JSON = dirname(__FILE__). '/../../config_running/assistant.json';

    static function logger($str = '', $level = 'debug')
    {
        $function = debug_backtrace(false, 2)[1]['function'];
        $msg = '['.__CLASS__.'] <'. $function .'> '.$str;
        log::add('snips', $level, $msg);
    }

    /* get current assistant language */
    static function get_assistant_language()
    {
        $obj = object::byName(self::NAME_OBJECT);
        if (!$obj) {
            return false;
        }
        return $obj->getConfiguration('language', 'en');
    }

    /* static method, get all the intent eqLogic objects */
    static function dump_eq_intent()
    {
        $eq_intent = array();
        $eqs = self::byType('snips');
        foreach ($eqs as $eq) {
            if ($eq->getConfiguration('Intent')) {
                $eq_intent[] = $eq;
            }
        }
        return $eq_intent;
    }

    /* static method, get all the tts eqLogic objects */
    static function dump_eq_tts()
    {
        $eq_tts = array();
        $eqs = self::byType('snips');
        foreach($eqs as $eq){
            if ($eq->getConfiguration('TTS')) {
                $eq_TTS[] = $eq;
            }
        }
        return $eq_tts;
    }

    /* start to install denpendancy */
    static function dependancy_install()
    {
        self::logger('Installing dependencies..');
        log::remove(__CLASS__ . '_dep');
        $resource_path = realpath(dirname(__FILE__) . '/../../resources');
        passthru(
            'sudo /bin/bash '. $resource_path .'/install.sh '.
            $resource_path .' > '. log::getPathToLog('snips_dep') .' 2>&1 &'
        );
        return true;
    }

    /* return dependency status */
    static function dependancy_info()
    {
        $return = array();
        $return['log'] = 'snips_dep';
        $return['progress_file'] = jeedom::getTmpFolder('snips') . '/dependance';
        $return['state'] = 'nok';
        $cmd = "dpkg -l | grep mosquitto";
        exec($cmd, $output, $return_var);
        $libphp = extension_loaded('mosquitto');
        if ($output[0] != "" && $libphp) {
            $return['state'] = 'ok';
        }
        return $return;
    }

    /* return deamon status */
    static function deamon_info()
    {
        $return = array();
        $return['log'] = '';
        $return['state'] = 'nok';
        $cron = cron::byClassAndFunction('snips', 'deamon_hermes');
        if (is_object($cron) && $cron->running()) {
            $return['state'] = 'ok';
        }
        $dependancy_info = self::dependancy_info();
        if ($dependancy_info['state'] == 'ok') {
            $return['launchable'] = 'ok';
        }
        return $return;
    }

    /* start hermes client in a deamon */
    static function deamon_start($_debug = false)
    {
        self::deamon_stop();
        $deamon_info = self::deamon_info();
        if ($deamon_info['launchable'] != 'ok') {
            throw new Exception(__('Please check your configuration', __FILE__));
        }
        $cron = cron::byClassAndFunction('snips', 'deamon_hermes');
        if (!is_object($cron)) {
            throw new Exception(__('Can not find task corn ', __FILE__));
        }
        $cron->run();
    }

    /* stop hermes client */
    static function deamon_stop()
    {
        $cron = cron::byClassAndFunction('snips', 'deamon_hermes');
        if (!is_object($cron)) {
            throw new Exception(__('Can not find taks corn', __FILE__));
        }
        $cron->halt();
    }

    /* create hermes client and run */
    static function deamon_hermes()
    {
        snips::logger('Starting hermes deamon..');
        $addr = config::byKey('mqttAddr', 'snips', '127.0.0.1');
        $H = new SnipsHermes($addr, 1883);
        $H->subscribe_intents('SnipsHandler::intent_detected');
        $H->subscribe_session_ended('SnipsHandler::session_ended');
        $H->subscribe_session_started('SnipsHandler::session_started');
        $H->subscribe_hotword_detected('SnipsHandler::hotword_detected');
        $H->start();
    }

    /* start a new hermes client for calling publish related APIs */
    static public function hermes()
    {
        $addr = config::byKey('mqttAddr', 'snips', '127.0.0.1');
        $H = new SnipsHermes($addr, 1883);
        return $H;
    }

    /* delete assistant */
    static function delete_assistant()
    {
        // intent [name] to jeedom [id]
        $intent_table = array();

        // slot [name] to [id]
        $slots_table = array();

        $eqLogics = eqLogic::byType('snips');
        foreach($eqLogics as $eq) {
            $intent_table[$eq->getHumanName()] = $eq->getId();
            $cmds = cmd::byEqLogicId($eq->getId());
            foreach($cmds as $cmd) {
                $slots_table[$cmd->getHumanName()] = $cmd->getId();
                $cmd->remove();
            }
            $eq->remove();
        }

        $reload_reference = array(
            "Intents" => $intent_table,
            "Slots" => $slots_table
        );

        // save the reference table for the next round reload
        $file = fopen(dirname(__FILE__) . '/../../config_running/reload_reference.json', 'w');
        $res = fwrite($file, json_encode($reload_reference));
    }

    /* get a list of bindings of this intent */
    function get_bindings()
    {
        return SnipsBinding::dump($this->getConfiguration('bindings'));
    }

    /* get the callback scenario of this intent */
    function get_callback_scenario()
    {
        $raw_array = $this->getConfiguration('callbackScenario');
        $callback_scenario = new SnipsBindingScenario($raw_array);
        if (!is_object($callback_scenario)) {
            snips::logger('No callback scenario found.');
            return false;
        }
        return $callback_scenario;
    }

    public function get_slots()
    {
        ;//reserved to the next update
    }

    /* Check if this intent is using Snips binding */
    public function is_snips_config()
    {
        $res = $this->getConfiguration('isSnipsConfig', false);
        return ($res === '1') ? true : false;
    }

    /* Check if this intent is using Jeedom interaction */
    public function is_interaction()
    {
        $res = $this->getConfiguration('isInteraction', false);
        return ($res === '1') ? true : false;
    }

    ///////////////////////////////////////////////////////////

    public static

    function extractSlotsValues($_payload_slots){
        $result = array('slots_values' => array(),
                        'slots_values_org' => array());

        foreach ($_payload_slots as $slot) {
            self::logger('['.__FUNCTION__.'] Checking slots: '.$slot->{'slotName'});

            if ($slot->{'entity'} == 'snips/duration') {
                $total_seconds = 0;
                $total_seconds += $slot->{'value'}->{'weeks'} * 604800;
                $total_seconds += $slot->{'value'}->{'days'} * 86400;
                $total_seconds += $slot->{'value'}->{'hours'} * 3600;
                $total_seconds += $slot->{'value'}->{'minutes'} * 60;
                $total_seconds += $slot->{'value'}->{'seconds'};
                $single_ready_value = (string)$total_seconds;
                $single_ready_value_org = (string)$total_seconds;
            }else{
                $single_ready_value = strtolower(str_replace(' ', '', (string)$slot->{'value'}->{'value'}));
                $single_ready_value_org = (string)$slot->{'value'}->{'value'};
            }

            if (array_key_exists($slot->{'slotName'}, $result['slots_values'])) {
                // does not support use this slot value in the slot commond
                self::logger('['.__FUNCTION__.'] Yes, this exists in the array :'.$slot->{'slotName'});
                $result['slots_values'][$slot->{'slotName'}] = $single_ready_value;
                $result['slots_values_org'][$slot->{'slotName'}] .= '&'.$single_ready_value_org;
            }else{
                self::logger('['.__FUNCTION__.'] No, this does not exist in the array :'.$slot->{'slotName'});
                $result['slots_values'][$slot->{'slotName'}] = $single_ready_value;
                $result['slots_values_org'][$slot->{'slotName'}] = $single_ready_value_org;
            }
        }
        return $result;
    }

    public static

    function findAndDoAction($_payload)
    {
        $intent_name = $_payload->{'intent'}->{'intentName'};
        $probability = $_payload->{'intent'}->{'probability'};
        $site_id = $_payload->{'siteId'};
        $session_id = $_payload->{'sessionId'};
        $query_input = $_payload->{'input'};
        self::logger('['.__FUNCTION__.'] Intent:' . $intent_name . ' siteId:' . $site_id . ' sessionId:' . $session_id);

        $slots_values_dual = snips::extractSlotsValues($_payload->{'slots'});

        $slots_values = $slots_values_dual['slots_values'];
        $slots_values_org = $slots_values_dual['slots_values_org'];

        snips::setSlotsCmd($slots_values, $intent_name);
        $eqLogic = eqLogic::byLogicalId($intent_name, 'snips');

        $callback_scenario_parameters = $eqLogic->getConfiguration('callbackScenario');
        $callback_called = snips::executeCallbackScenario($callback_scenario_parameters, $slots_values_org, $_payload);

        $bindings = $eqLogic->getConfiguration('bindings');
        if (!$eqLogic->getConfiguration('isSnipsConfig') && $eqLogic->getConfiguration('isInteraction')) {
            $param = array();
            $reply = interactQuery::tryToReply($query_input, $param);
            //snips::sayFeedback($reply['reply'], $session_id); // old api
            self::hermes()->publish_start_session_notification($session_id, $reply['reply']);
            //self::hermes()->publish_end_session($session_id, $reply['reply']);
        }
        else {
            $bindings_match_coming_slots = array();
            foreach($bindings as $binding) {
                self::logger('['.__FUNCTION__.'] Cur binding name : ' . $binding['name']);
                self::logger('['.__FUNCTION__.'] Binding count is : ' . count($binding['nsr_slots']));
                self::logger('['.__FUNCTION__.'] Snips count is : ' . count($slots_values));
                if (count($binding['nsr_slots']) === count($slots_values)) {
                    self::logger('['.__FUNCTION__.'] Binding has corr number of slot: ' . $binding['name']);
                    $slot_all_exists_indicator = 1;
                    foreach($binding['nsr_slots'] as $slot) {
                        if (array_key_exists($slot, $slots_values)) {
                            $slot_all_exists_indicator*= 1;
                        }
                        else {
                            $slot_all_exists_indicator*= 0;
                        }
                    }

                    if ($slot_all_exists_indicator) {
                        $bindings_match_coming_slots[] = $binding;
                    }
                }
            }

            $bindings_with_correct_condition = array();
            foreach($bindings_match_coming_slots as $bindings_match_coming_slot) {
                if (!empty($bindings_match_coming_slot['condition'])) {
                    $condition_all_true_indicator = 1;
                    foreach($bindings_match_coming_slot['condition'] as $condition) {
                        $cmd = cmd::byId($condition['pre']);
                        if (is_string($cmd->getCache('value', 'NULL'))) {
                            $pre_value = strtolower(str_replace(' ', '', $cmd->getCache('value', 'NULL')));
                        }
                        else {
                            $pre_value = $cmd->getCache('value', 'NULL');
                        }
                        self::logger('['.__FUNCTION__.'] [Condition] Condition Aft string: '.$condition['aft']);
                        if (is_string($condition['aft'])) {
                            $aft_value = explode(',',strtolower(str_replace(' ', '', $condition['aft'])));
                            foreach ($aft_value as $key => $value) {
                                self::logger('['.__FUNCTION__.'] [Condition] Condition Aft value index: '.$key.' value: ' . $value);
                            }
                            //$aft_value = strtolower(str_replace(' ', '', $condition['aft']));
                        }
                        else {
                            $aft_value = array($condition['aft']);
                            self::logger('['.__FUNCTION__.'] [Condition] Condition Aft value is : ' . $aft_value);
                        }


                        if (in_array($pre_value , $aft_value)) {
                            $condition_all_true_indicator*= 1;
                        }
                        else {
                            $condition_all_true_indicator*= 0;
                        }
                    }

                    if ($condition_all_true_indicator) {
                        if ($bindings_match_coming_slot['enable']) {
                            $bindings_with_correct_condition[] = $bindings_match_coming_slot;
                        }
                    }
                }
                else {
                    if ($bindings_match_coming_slot['enable']) {
                        $bindings_with_correct_condition[] = $bindings_match_coming_slot;
                    }
                }
            }

            if (count($bindings_with_correct_condition) != 0) {
                foreach($bindings_with_correct_condition as $binding) {
                    foreach($binding['action'] as $action) {
                        $options = $action['options'];

                        if ($action['cmd'] == 'scenario') {
                            $tags = array();
                            $args = arg2array($options['tags']);
                            foreach ($args as $key => $value) {
                                $tags['#' . trim(trim($key), '#') . '#'] = $value;
                            }

                            if($callback_scenario_parameters['isTagPlugin'])
                                $tags['#plugin#'] = 'snips';

                            if($callback_scenario_parameters['isTagIdentifier'])
                                $tags['#identifier#'] = 'snips::'.$_payload->{'intent'}->{'intentName'}.'::'.$binding['name'];

                            if($callback_scenario_parameters['isTagIntent'])
                                if(strpos($_payload->{'intent'}->{'intentName'},':'))
                                    $tags['#intent#'] = substr($_payload->{'intent'}->{'intentName'},strpos($_payload->{'intent'}->{'intentName'},':')+1);
                                else
                                    $tags['#intent#'] = $_payload->{'intent'}->{'intentName'};

                            if($callback_scenario_parameters['isTagSiteId'])
                                $tags['#siteId#'] = $_payload->{'siteId'};

                            if($callback_scenario_parameters['isTagQuery'])
                                $tags['#query#'] = $_payload->{'input'};

                            if($callback_scenario_parameters['isTagProbability'])
                                $tags['#probability#'] = $_payload->{'intent'}->{'probability'};

                            if($callback_scenario_parameters['isTagSlots'])
                                foreach ($slots_values_org as $slots_name => $value)
                                    $tags['#'.$slots_name.'#'] = $value;
                            $options['tags'] = $tags;
                        }

                        snips::setSlotsCmd($slots_values, $intent_name, $options);
                        self::logger('['.__FUNCTION__.'] Current action: ' . $action['cmd']);
                        $execution_return_msg = scenarioExpression::createAndExec('action', $action['cmd'], $options);
                        if (is_string($execution_return_msg) && $execution_return_msg!='') {
                            if (config::byKey('dynamicSnipsTTS', 'snips', 0) && cmd::byString($binding['ttsPlayer'])->getConfiguration('snipsType') == 'TTS') {
                                self::hermes()->publish_start_session_notification($site_id, $execution_return_msg);
                            }else{
                                $cmd = cmd::byString($binding['ttsPlayer']);
                                if (is_object($cmd)) {
                                    $cmd->execCmd(array('message' => $execution_return_msg));
                                }
                            }
                        }
                    }

                    $text = SnipsTts::dump($binding['ttsMessage'],(array)$binding['ttsVar'])->get_message();
                    self::hermes()->publish_start_session_notification($_payload->{'siteId'}, $text);

                    self::logger('['.__FUNCTION__.'] [Binding Execution] Generated text is ' . $text);
                    self::logger('['.__FUNCTION__.'] [Binding Execution] Orginal text is ' . $binding['ttsMessage']);

                    self::logger('['.__FUNCTION__.'] [Binding Execution] Player is ' . $binding['ttsPlayer']);

                    $tts_player_cmd = cmd::byString($binding['ttsPlayer']);

                    if (config::byKey('dynamicSnipsTTS', 'snips', 0) && $tts_player_cmd->getConfiguration('snipsType') == 'TTS') {

                        self::hermes()->publish_start_session_notification($site_id, $text);
                    }else{
                        $cmd = cmd::byString($binding['ttsPlayer']);
                        if (is_object($cmd)) {
                            $cmd->execCmd(array('message' => $execution_return_msg));
                        }
                    }
                }
            }else if(count($bindings_with_correct_condition) == 0 && !$callback_called){

                $orgMessage = config::byKey('defaultTTS', 'snips', 'Désolé, je n’ai pas compris');

                $messages = SnipsTts::dump($orgMessage)->get_message();
                self::hermes()->publish_start_session_notification($site_id, $messages);
            }
        }
        sleep(1);
        snips::resetSlotsCmd();
    }

    public static

    function executeCallbackScenario($_parameters, $_slots_values_org, $_payload){
        self::logger('['.__FUNCTION__.']  Intent: ' . $_payload->{'intent'}->{'intentName'});
        if ($_parameters['scenario'] == -1 || $_parameters['scenario'] == NULL)
            return;

        $options = array();
        $options['scenario_id'] = $_parameters['scenario'];
        $options['action'] = $_parameters['action'];

        $tags = array();
        $args = arg2array($_parameters['user_tags']);

        foreach ($args as $key => $value)
            $tags['#' . trim(trim($key), '#') . '#'] = $value;

        if($_parameters['isTagPlugin'])
            $tags['#plugin#'] = 'snips';

        if($_parameters['isTagIdentifier'])
            $tags['#identifier#'] = 'snips::'.$_payload->{'intent'}->{'intentName'}.'::Callback';

        if($_parameters['isTagIntent'])
            if(strpos($_payload->{'intent'}->{'intentName'},':'))
                $tags['#intent#'] = substr($_payload->{'intent'}->{'intentName'},strpos($_payload->{'intent'}->{'intentName'},':')+1);
            else {
                $tags['#intent#'] = $_payload->{'intent'}->{'intentName'};
            }
        if($_parameters['isTagSiteId'])
            $tags['#siteId#'] = $_payload->{'siteId'};

        if($_parameters['isTagQuery'])
            $tags['#query#'] = $_payload->{'input'};

        if($_parameters['isTagProbability'])
            $tags['#probability#'] = $_payload->{'intent'}->{'probability'};

        if($_parameters['isTagSlots'])
            foreach ($_slots_values_org as $slots_name => $value)
                $tags['#'.$slots_name.'#'] = $value;

        $options['tags'] = $tags;

        $execution_return_msg = False;
        $execution_return_msg = scenarioExpression::createAndExec('action', 'scenario', $options);

        if (is_string($execution_return_msg) &&
            $execution_return_msg!='' &&
            config::byKey('dynamicSnipsTTS', 'snips', 0)) {

            self::hermes()->publish_start_session_notification($_payload->{'siteId'}, $execution_return_msg);
        }
        return $execution_return_msg;
    }

    public static

    function setSlotsCmd($_slots_values, $_intent, $_options = null)
    {
        self::logger('['.__FUNCTION__.'] Set slots cmd values');
        $eq = eqLogic::byLogicalId($_intent, 'snips');
        if (is_object($eq)) {
            foreach($_slots_values as $slot => $value) {
                self::logger('['.__FUNCTION__.'] Slots name is :' . $slot);
                $cmd = $eq->getCmd(null, $slot);
                if (is_object($cmd)) {
                    if ($_options) {
                        self::logger('['.__FUNCTION__.'] Slots option entered, entityId is:' . $cmd->getConfiguration('entityId'));
                        if ($cmd->getConfiguration('entityId') == 'snips/percentage') {
                            $org = $value;
                            $value = snips::percentageRemap($_options['LT'], $_options['HT'], $value);
                            $cmd->setConfiguration('orgVal', $org);
                            self::logger('['.__FUNCTION__.'] Slots is percentage, value after convert:' . $value);
                        }
                    }
                    $eq->checkAndUpdateCmd($cmd, $value);
                    $cmd->setValue($value);
                    $cmd->save();
                }
            }
        }else{
            self::logger('['.__FUNCTION__.'] Did not find entiry:' . $_intent);
        }
    }

    public static

    function percentageRemap($_LT, $_HT, $_percentage)
    {
        $real_value = ($_HT - $_LT) * ($_percentage / 100);
        if ($real_value > $_HT) {
            $real_value = $_HT;
        }
        else
        if ($real_value < $_LT) {
            $real_value = $_LT;
        }

        return $real_value;
    }

    public static

    function resetSlotsCmd($_slots_values = false, $_intent = false)
    {
        self::logger('['.__FUNCTION__.'] Reset all the slots');
        if ($_slots_values == false && $_intent == false) {
            $eqs = eqLogic::byType('snips');
            foreach($eqs as $eq) {
                $cmds = $eq->getCmd();
                foreach($cmds as $cmd) {
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
                self::logger('['.__FUNCTION__.'] Set '.$var->getValue().' => snipsMsgSiteId');
            }
        }
        else {
            $eq = eqLogic::byLogicalId($_intent, 'snips');
            foreach($_slots_values as $slot => $value) {
                $cmd = $eq->getCmd(null, $slot);
                $cmd->setCache('value');
                $cmd->setValue(null);
                $cmd->setConfiguration('orgVal', null);
                $cmd->save();
            }
        }
    }

    public static

    function exportConfigration($_name, $_output = true)
    {
        $binding_conf = array();
        $eqs = eqLogic::byType('snips');
        foreach($eqs as $eq) {
            $org_bindings = $eq->getConfiguration('bindings');
            $json_string = json_encode($org_bindings);
            self::logger('['.__FUNCTION__.'] json_string type is: ' . gettype($json_string));
            preg_match_all('/#[^#]*[0-9]+#/', $json_string, $matches);
            $human_cmd = cmd::cmdToHumanReadable($matches[0]);
            foreach($human_cmd as $key => $cmd_text) {
                $json_string = str_replace($matches[0][$key], $cmd_text, $json_string);
            }

            preg_match_all('/("pre":")[^("pre":")]*[0-9]+"/', $json_string, $matches_c);
            $matches_c1 = $matches_c;
            foreach($matches_c[0] as $key => $value) {
                $matches_c[0][$key] = str_replace('"pre":"', '#', $value);
                self::logger('['.__FUNCTION__.'] 1st : ' . $matches_c[0][$key]);
                $matches_c[0][$key] = str_replace('"', '#', $matches_c[0][$key]);
                self::logger('['.__FUNCTION__.'] 2nd : ' . $matches_c[0][$key]);
            }

            $humand_cond = cmd::cmdToHumanReadable($matches_c[0]);
            foreach($humand_cond as $key => $cmd_text) {
                self::logger('['.__FUNCTION__.'] key word : ' . $matches_c1[0][$key] . ' replace ' . '"pre":"' . $cmd_text . '"');
                $json_string = str_replace($matches_c1[0][$key], '"pre":"' . $cmd_text . '"', $json_string);
            }

            $aft_bindings = json_decode($json_string);
            self::logger('['.__FUNCTION__.'] vars: ' . $aft_bindings);
            $binding_conf[$eq->getName() ] = $aft_bindings;
        }

        if ($_output) {
            $file = fopen(dirname(__FILE__) . '/../../config_backup/' . $_name . '.json', 'w');
            $res = fwrite($file, json_encode($binding_conf));
            if ($res) {
                self::logger('['.__FUNCTION__.'] Success output');
            }
            else {
                self::logger('['.__FUNCTION__.'] Faild output');
            }
        }else{
            return json_encode($binding_conf);
        }

    }

    public static

    function importConfigration($_configFileName = null, $_configurationJson = null)
    {
        if (isset($_configurationJson) && !isset($_configFileName)) {
            self::logger('['.__FUNCTION__.'] Asked to internally reload config file');
            $json_string = $_configurationJson;
        }else if (!isset($_configurationJson) && isset($_configFileName)){
            self::logger('['.__FUNCTION__.'] Asked to import file: ' . $_configFileName);
            $json_string = file_get_contents(dirname(__FILE__) . '/../../config_backup/' . $_configFileName);
        }

        preg_match_all('/("pre":")(#.*?#)(")/', $json_string, $matches);
        $cmd_ids = cmd::humanReadableToCmd($matches[2]);

        foreach($cmd_ids as $key => $cmd_id) {
            $cmd_id = str_replace('#', '', $cmd_id);

            self::logger('['.__FUNCTION__.'] key word : ' . '"pre":"'.$matches[2][$key].'"' . ' replace ' . '"pre":"'.$cmd_id.'"');

            $json_string = str_replace('"pre":"'.$matches[2][$key].'"', '"pre":"'.$cmd_id.'"', $json_string);
        }

        $data = json_decode($json_string, true);
        $eqs = eqLogic::byType('snips');
        foreach($eqs as $eq) {
            if ($data[$eq->getName() ] != '' && isset($data[$eq->getName() ])) {
                $eq->setConfiguration('bindings', $data[$eq->getName() ]);
                $eq->save(true);
            }
        }
    }

    public static

    function displayAvailableConfigurations()
    {
        $command = 'ls ' . dirname(__FILE__) . '/../../config_backup/';
        $res = exec($command, $output, $return_var);
        return $output;
    }

    public static

    function isSnipsRunLocal()
    {
        $addr = config::byKey('mqttAddr', 'snips', '127.0.0.1');
        if ($addr == '127.0.0.1' || $addr == 'localhost');
    }

    public static

    function tryToFetchDefault(){
        $res = snips::fetchAssistantJson('pi', 'raspberry');
        self::logger('['.__FUNCTION__.'] Result code: '.$res);
        return $res;
    }

    public static

    function fetchAssistantJson($_usrename, $_password)
    {
        $ip_addr = config::byKey('mqttAddr', 'snips', '127.0.0.1');
        self::logger('['.__FUNCTION__.'] Connection : Trying to connect to: '.$ip_addr);
        $connection = ssh2_connect($ip_addr, 22);
        if (!$connection) {
            self::logger('['.__FUNCTION__.'] Connection: Faild code: -2');
            return -2;
        }

        $resp = ssh2_auth_password($connection, $_usrename, $_password);
        if ($resp) {
            self::logger('['.__FUNCTION__.'] Verification: Success');
        }
        else {
            self::logger('['.__FUNCTION__.'] Verification: Faild code: -1');
            return -1;
        }

        $res = ssh2_scp_recv($connection, '/usr/share/snips/assistant/assistant.json', dirname(__FILE__) . '/../../config_running/assistant.json');
        $res0 = ssh2_scp_recv($connection, '/etc/snips.toml', dirname(__FILE__) . '/../../config_running/snips.toml');
        if ($res && $res0) {
            ssh2_exec($connection, 'exit');
            unset($connection);
            self::logger('['.__FUNCTION__.'] Fecth resutlt : Success');
            return 1;
        }
        else {
            ssh2_exec($connection, 'exit');
            unset($connection);
            self::logger('['.__FUNCTION__.'] Fecth resutlt : Faild code: 0');
            return 0;
        }
    }

    public static

    function lightBrightnessShift($_jsonLights)
    {
        $json = json_decode($_jsonLights, true);
        $lights = $json['LIGHTS'];
        $_up_down = $json['OPERATION'];
        foreach ($lights as $light) {
            $cmd = cmd::byString($light['LIGHT_BRIGHTNESS_VALUE']);
            if (is_object($cmd))
            if ($cmd->getValue()) $current_val = $cmd->getValue();
            else $current_val = $cmd->getCache('value', 'NULL');
            $options = array();
            $change = round(($light['MAX_VALUE'] - $light['MIN_VALUE']) * $light['STEP_VALUE']);
            if ($_up_down === 'UP') $options['slider'] = $current_val + $change;
            else
            if ($_up_down === 'DOWN') $options['slider'] = $current_val - $change;
            if ($options['slider'] < $light['MIN_VALUE']) $options['slider'] = $light['MIN_VALUE'];
            if ($options['slider'] > $light['MAX_VALUE']) $options['slider'] = $light['MAX_VALUE'];
            $cmdSet = cmd::byString($light['LIGHT_BRIGHTNESS_ACTION']);
            if (is_object($cmdSet)) {
                $cmdSet->execCmd($options);
                self::logger('['.__FUNCTION__.'] Shift action: ' . $cmdSet->getHumanName() . ', from -> ' . $options['slider'] . ' to ->' . $current_val);
            }else{
                self::logger('['.__FUNCTION__.'] Can not find cmd: '. $light['LIGHT_BRIGHTNESS_ACTION']);
            }
        }
    }

    public static

    function findDevice($_site_id){
        $lang = object::byName('Snips-Intents')->getConfiguration('language');
        if ($lang == 'fr') {
            $text = 'Dispositif '.$_site_id.' est ici!';

        }else if ($lang == 'en') {
            $text = 'Device '.$_site_id.' is here!';
        }
        self::hermes()->publish_start_session_notification($_site_id, $text);
    }

    public static

    function postConfiguration(){
        if(!config::byKey('isVarMsgSession', 'snips', 0)){
            $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSession');
            if (is_object($var)) {
                $var->remove();
            }
            self::logger('['.__FUNCTION__.'] Removed variable snipsMsgSession');
        }else{
            $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSession');
            if (!is_object($var)) {
                $var = new dataStore();
                $var->setKey('snipsMsgSession');
                self::logger('['.__FUNCTION__.'] Created variable snipsMsgSession');
            }
            $var->setValue('');
            $var->setType('scenario');
            $var->setLink_id(-1);
            $var->save();
        }

        if(!config::byKey('isVarMsgSiteId', 'snips', 0)){
            $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSiteId');
            if (is_object($var)) {
                $var->remove();
            }
            self::logger('['.__FUNCTION__.'] Removed variable snipsMsgSiteId');
        }else{
            $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSiteId');
            if (!is_object($var)) {
                $var = new dataStore();
                $var->setKey('snipsMsgSiteId');
                self::logger('['.__FUNCTION__.'] Created variable snipsMsgSiteId');
            }
            $var->setValue('');
            $var->setType('scenario');
            $var->setLink_id(-1);
            $var->save();
        }

        if(!config::byKey('isVarMsgHotwordId', 'snips', 0)){
            $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgHotwordId');
            if (is_object($var)) {
                $var->remove();
            }
            self::logger('['.__FUNCTION__.'] Removed variable snipsMsgHotwordId');
        }else{
            $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgHotwordId');
            if (!is_object($var)) {
                $var = new dataStore();
                $var->setKey('snipsMsgHotwordId');
                self::logger('['.__FUNCTION__.'] Created variable snipsMsgHotwordId');
            }
            $var->setValue('');
            $var->setType('scenario');
            $var->setLink_id(-1);
            $var->save();
        }
    }

    public

    function preInsert()
    {
    }

    public

    function postInsert()
    {
    }

    public

    function preSave()
    {
        if($this->getConfiguration('snipsType') == 'Intent'){
            $slots = $this->getConfiguration('slots');
            $slotSet = array();
            foreach($slots as $slot) {
                $slotSet[] = $slot['name'];
            }

            $bindings = $this->getConfiguration('bindings');
            foreach($bindings as $key => $binding) {
                $necessary_slots = array();
                $conditions = $binding['condition'];
                foreach($conditions as $condition) {
                    if (!in_array(cmd::byId($condition['pre'])->getName() , $necessary_slots)) {
                        $necessary_slots[] = cmd::byId($condition['pre'])->getName();
                    }
                }

                $actions = $binding['action'];
                foreach($actions as $action) {
                    $options = $action['options'];
                    foreach($options as $option) {
                        if (preg_match("/#.*#/", $option, $match_res)) {
                            $cmd_name = cmd::byId(str_replace('#', '', $match_res[0]))->getName();
                            if (in_array($cmd_name, $slotSet)) {
                                if (!in_array($cmd_name, $necessary_slots)) {
                                    $necessary_slots[] = $cmd_name;
                                }
                            }
                        }
                    }
                }

                if (!empty($necessary_slots)) {
                    $bindings[$key]['nsr_slots'] = $necessary_slots;
                }

                unset($necessary_slots);
            }
            $this->setConfiguration('bindings', $bindings);
        }
    }

    public

    function postSave()
    {
    }

    public

    function preUpdate()
    {
    }

    public

    function postUpdate()
    {
    }

    public

    function preRemove()
    {
    }

    public

    function postRemove()
    {
    }
}

class snipsCmd extends cmd

{
    public function execute($_options = array())
    {
        $eqlogic = $this->getEqLogic();
        switch ($this->getLogicalId()) {
            case 'say':
                $this->snips_say($_options);
                break;
            case 'ask':
                $this->snips_ask($_options);
                break;
        }
    }

    public function snips_say($_options = array())
    {
        snips::logger('['.__FUNCTION__.'] cmd: say, text:'.$_options['message']);
        snips::hermes()->publish_start_session_notification($this->getConfiguration('siteId'), $_options['message']);
    }

    public function snips_ask()
    {
        snips::logger('['.__FUNCTION__.'] cmd: ask');
        preg_match_all("/(\[.*?\])/", $_options['answer'][0], $match_intent);
        $_ans_intent = str_replace('[', '', $match_intent[0][0]);
        $_ans_intent = str_replace(']', '', $_ans_intent);
        snips::hermes()->publish_start_session_action($this->getConfiguration('siteId'), $_options['message'], null, array($_ans_intent));
        //snips::startRequest($_ans_intent, $_options['message'], $site_id);
    }
}