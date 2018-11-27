<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require(dirname(__FILE__) . '/../../3rdparty/Toml.php');

// ini_set("display_errors","On");
// error_reporting(E_ALL);

class snips extends eqLogic

{
    public static

    function resetMqtt()
    {
        $cron = cron::byClassAndFunction('snips', 'mqttClient');
        if (is_object($cron)) {
            $cron->stop();
            $cron->remove();
        }

        $cron = cron::byClassAndFunction('snips', 'mqttClient');
        if (!is_object($cron)) {
            $cron = new cron();
            $cron->setClass('snips');
            $cron->setFunction('mqttClient');
            $cron->setEnable(1);
            $cron->setDeamon(1);
            $cron->setSchedule('* * * * *');
            $cron->setTimeout('1440');
            $cron->save();
            snips::debug(__FUNCTION__, 'Created snips cron: mqttClient');
        }

        snips::deamon_start();
    }

    public static

    function deamon_info()
    {
        $return = array();
        $return['log'] = '';
        $return['state'] = 'nok';
        $cron = cron::byClassAndFunction('snips', 'mqttClient');
        if (is_object($cron) && $cron->running())
            $return['state'] = 'ok';

        $dependancy_info = self::dependancy_info();
        if ($dependancy_info['state'] == 'ok')
            $return['launchable'] = 'ok';

        return $return;
    }

    public static

    function deamon_start($_debug = false)
    {
        self::deamon_stop();
        $deamon_info = self::deamon_info();
        if ($deamon_info['launchable'] != 'ok')
            throw new Exception(__('Please check your configuration', __FILE__));

        $cron = cron::byClassAndFunction('snips', 'mqttClient');
        if (!is_object($cron))
            throw new Exception(__('Can not find task corn ', __FILE__));

        $cron->run();
    }

    public static

    function deamon_stop()
    {
        $cron = cron::byClassAndFunction('snips', 'mqttClient');
        if (!is_object($cron))
            throw new Exception(__('Can not find taks corn', __FILE__));

        $cron->halt();
    }

    public static

    function dependancy_info()
    {
        $return = array();
        $return['log'] = 'snips_dep';
        $return['progress_file'] = jeedom::getTmpFolder('snips') . '/dependance';
        $return['state'] = 'nok';
        $cmd = "dpkg -l | grep mosquitto";
        exec($cmd, $output, $return_var);
        $libphp = extension_loaded('mosquitto');
        if ($output[0] != "" && $libphp)
            $return['state'] = 'ok';

        return $return;
    }

    public static

    function dependancy_install()
    {
        self::debug(__FUNCTION__, 'Installation of dependences');
        log::remove(__CLASS__ . '_dep');
        $resource_path = realpath(dirname(__FILE__) . '/../../resources');
        passthru('sudo /bin/bash ' . $resource_path . '/install.sh ' . $resource_path . ' > ' . log::getPathToLog('snips_dep') . ' 2>&1 &');
        return true;
    }

    public static

    function mqttClient()
    {
        $addr = config::byKey('mqttAddr', 'snips', '127.0.0.1');
        $port = 1883;
        snips::debug(__FUNCTION__, 'connection, Host: ' . $addr . ', Port: ' . $port);
        $client = new Mosquitto\Client();
        $client->onConnect('snips::connect');
        $client->onDisconnect('snips::disconnect');
        $client->onSubscribe('snips::subscribe');
        $client->onMessage('snips::message');
        $client->onLog('snips::logmq');
        $client->setWill('/jeedom', "Client died :-(", 1, 0);
        try {
            $client->connect($addr, $port, 60);
            $topics = array();
            $topics = snips::getTopics();
            foreach($topics as $topic) {
                $client->subscribe($topic, 0);
            }
            $client->subscribe('hermes/dialogueManager/sessionStarted', 0);
            $client->subscribe('hermes/dialogueManager/sessionEnded', 0);
            $client->subscribe('hermes/hotword/default/detected', 0);
            $client->loopForever();
        }

        catch(Exception $e) {
            snips::debug(__FUNCTION__, 'Exception: '.$e->getMessage());
        }
    }

    public static

    function connect($r, $message)
    {
        snips::debug(__FUNCTION__, 'Connected, code: ' . $r . ' message: ' . $message);
        config::save('status', '1', 'snips');
    }

    public static

    function disconnect($r)
    {
        snips::debug(__FUNCTION__, 'Disconnected, code: ' . $r);
        config::save('status', '0', 'snips');
    }

    public static

    function subscribe()
    {
        log::add('snips', 'inof', '[MQTT] Subscribeed to .'.$topic);
    }

    public static

    function logmq($code, $str)
    {
        if (strpos($str, 'PINGREQ') === false && strpos($str, 'PINGRESP') === false) {
            snips::debug(__FUNCTION__, 'code: '.$code . ' : ' . $str);
        }
    }

    public static

    function message($_message)
    {
        $topics = snips::getTopics();
        $intents_slots = snips::getIntents();
        snips::debug(__FUNCTION__, 'Received message. Topic:'.$_message->topic);
        $payload = json_decode($_message->payload);

        $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSiteId');
        if (is_object($var)) {
            $var->setValue($payload->{'siteId'});
            $var->save();
            snips::debug(__FUNCTION__, 'Set '.$var->getValue().' => snipsMsgSiteId');
        }

        if ($_message->topic == 'hermes/dialogueManager/sessionStarted'){
            $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSession');
            if (is_object($var)) {
                $var->setValue($payload->{'sessionId'});
                $var->save();
                snips::debug(__FUNCTION__, 'Set '.$var->getValue().' => snipsMsgSession');
            }
        }
        if ($_message->topic == 'hermes/dialogueManager/sessionEnded'){
            $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSession');
            if (is_object($var)) {
                $var->setValue('');
                $var->save();
                snips::debug(__FUNCTION__, 'Set '.$var->getValue().' => snipsMsgSession');
            }
        }
        if ($_message->topic == 'hermes/hotword/default/detected'){
            $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgHotwordId');
            if (is_object($var)) {
                $var->setValue($payload->{'modelId'});
                $var->save();
                snips::debug(__FUNCTION__, 'Set '.$var->getValue().' => snipsMsgHotwordId');
            }
        }
        if (in_array($_message->topic, $topics) == false) {
            return;
        }
        else {

            $site_id = $payload->{'siteId'};

            $exist_sites = array();
            $plugin = plugin::byId('snips');
            $intents_and_tts = eqLogic::byType($plugin->getId());
            foreach ($intents_and_tts as $key => $obj) {
                if ($obj->getConfiguration('snipsType') == 'TTS') {
                    $exist_sites = $obj->getConfiguration('siteName');
                }
            }

            if (!in_array($site_id, $exist_sites)) {
                snips::debug(__FUNCTION__, 'Find a snips device which is not in the list:'.$site_id);
                $obj_intent = object::byName('Snips-Intents');
                $lang = $obj_intent->getConfiguration('language');
                snips::createSnipsDevices($site_id, $lang);
            }

            snips::findAndDoAction($payload);
        }
    }

    public static

    function playTTS($_player_cmd, $_message, $_session_id = null, $_site_id = 'default'){

        //$messages_to_play = explode('//', $_message);
        $messages_to_play = interactDef::generateTextVariant($_message);
        $message_to_play = $messages_to_play[array_rand ($messages_to_play)];
        $cmd = cmd::byString($_player_cmd);
        if (is_object($cmd)) {
            $options = array();
            $options['message'] = $message_to_play;
            if (eqLogic::byId($cmd->getEqLogic_id())->getEqType_name() == 'snips') {
                $options['title'] = $_site_id;
            }else{
                $options['title'] = '50';
            }
            $options['sessionId'] = $_session_id;
            snips::debug(__FUNCTION__, 'Player: '.$_player_cmd.' Message: '.$options['message'].' Title: '.$options['title']);
            $cmd->execCmd($options);
            return;
        }else{
            snips::debug(__FUNCTION__, 'Can not find player cmd: '.$_player_cmd);
            return;
        }
    }

    public static

    function sayFeedback($_text, $_session_id = null, $_lang = 'en_GB', $_site_id = 'default')
    {
        if ($_session_id == null) {
            $topic = 'hermes/tts/say';
            $payload = array(
                'text' => str_replace('{#}', 'Value', $_text) ,
                "siteId" => $_site_id,
                "lang" => $_lang
            );
            snips::debug(__FUNCTION__, 'Publish: '.$text);
            self::publish($topic, json_encode($payload));
        }
        else {
            $topic = 'hermes/dialogueManager/endSession';
            $payload = array(
                'text' => $_text,
                "sessionId" => $_session_id
            );
            snips::debug(__FUNCTION__, 'Publish: '.$_text);
            self::publish($topic, json_encode($payload));
        }
    }

    public static

    function startRequest($_ans_intent, $_question_msg, $_site_id = 'default')
    {
        $topic = 'hermes/dialogueManager/startSession';
        $payload = array(
            "siteId" => $_site_id,
            "init" => array("type" => "action",
                            "text" => $_question_msg,
                            "canBeEnqueued" => true,
                            "intentFilter" => array($_ans_intent)
                            )
        );
        snips::debug(__FUNCTION__,'asked question: '.$_question_msg);
        self::publish($topic, json_encode($payload));
    }

    //------------ New Hermes Protocol Implementation START------------
    public static function hermes_publish_continue_session($_session_id, $_text, $_intent_filter, $_custom_data, $_send_intent_not_recognized){
        $topic = 'hermes/dialogueManager/continueSession';
        $payload = array();

        if ($_session_id)
            $payload['sessionId'] = $_session_id;
        else
            return 0;

        if ($_text)
            $payload['text'] = $_text;
        else
            return 0;

        if ($_intent_filter)
            $payload['intentFilter'] = $_intent_filter;

        if ($_custom_data)
            $payload['customData'] = $_custom_data;

        if ($_send_intent_not_recognized)
            $payload['sendIntentNotRecognized'] = $_send_intent_not_recognized;

        return self::mqtt_publish($topic, json_encode($payload));
    }

    public static function hermes_publish_end_session($_session_id, $_text){
        $topic = 'hermes/dialogueManager/endSession';
        $payload = array();

        if ($_session_id)
            $payload['sessionId'] = $_session_id;
        else
            return 0;

        if ($_text)
            $payload['text'] = $_text;

        return self::mqtt_publish($topic, json_encode($payload));
    }

    public static function hermes_publish_start_session_action($_site_id, $_session_init_text, $_session_init_can_be_enqueued, $_session_init_intent_filter, $_session_init_send_intent_not_recognized, $_custom_data){
        $topic = 'hermes/dialogueManager/startSession';
        $payload = array();
        $init = array('type' => 'action');

        if ($_site_id)
            $payload['siteId'] = $_session_id;

        if ($_session_init_text)
            $init['text'] = $_session_init_text;

        if ($_session_init_can_be_enqueued)
            $init['canBeEnqueued'] = $_session_init_can_be_enqueued;

        if ($_session_init_intent_filter)
            $init['intentFilter'] = $_session_init_intent_filter;

        if ($_session_init_send_intent_not_recognized)
            $init['sendIntentNotRecognized'] = $_session_init_send_intent_not_recognized;

        if ($_custom_data)
            $payload['customData'] = $_custom_data

        $payload['init'] = $init;

        return self::mqtt_publish($topic, json_encode($payload));
    }

    public static function hermes_publish_start_session_notification($_site_id, $_session_init_text, $_custom_data){
        $topic = 'hermes/dialogueManager/startSession';
        $payload = array();
        $init = array('type' => 'notification');

        if ($_site_id)
            $payload['siteId'] = $_session_id;

        if ($_session_init_text)
            $init['text'] = $_session_init_text;

        if ($_custom_data)
            $payload['customData'] = $_custom_data

        $payload['init'] = $init;

        return self::mqtt_publish($topic, json_encode($payload));
    }

    //------------ New Hermes Protocol Implementation END------------

    //------------ New Mqtt Operation API START------------

    private function mqtt_publish($_topic, $_payload){
        $addr = config::byKey('mqttAddr', 'snips', '127.0.0.1');
        $port = 1883;
        $client = new Mosquitto\Client();
        $client->connect($addr, $port, 60);
        $client->publish($_topic, $_payload);
        $client->disconnect();
        snips::debug(__FUNCTION__, 'published message: '.$_payload.' to topic: '.$_topic);
        unset($client);

        return 1;
    }

    //------------ New Mqtt Operation API END------------
    public static

    function publish($_topic, $_payload)
    {
        $addr = config::byKey('mqttAddr', 'snips', '127.0.0.1');
        $port = 1883;
        $client = new Mosquitto\Client();
        $client->connect($addr, $port, 60);
        $client->publish($_topic, $_payload);
        $client->disconnect();
        snips::debug(__FUNCTION__, 'published message: '.$_payload.' to topic: '.$_topic);
        unset($client);
    }

    public static

    function generateFeedback($_org_text, $_vars)
    {
        // Support round() function
        // Support binary remapping grammar {0,1}(yes,no)
        snips::debug(__FUNCTION__, 'Generating feedback text');
        $string_subs = explode('{#}', $_org_text);
        $speaking_text = '';
        if (!empty($string_subs)) {
            foreach($string_subs as $key => $sub) {
                if (isset($_vars[$key])) {
                    $cmd = cmd::byString($_vars[$key]['cmd']);
                    snips::debug(__FUNCTION__, 'The '.$key.' variable cmd id: ' . $cmd->getId());
                    if (is_object($cmd)) {
                        if ($cmd->getName() == 'intensity_percent' || $cmd->getName() == 'intensity_percentage') {
                            $sub.= $cmd->getConfiguration('orgVal');
                        }else if($cmd->getSubType() == 'binary'){
                            if($cmd->getCache('value', ' ') == 0) $sub .= $_vars[$key]['options']['zero'];
                            if($cmd->getCache('value', ' ') == 1) $sub .= $_vars[$key]['options']['one'];
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
                    snips::debug(__FUNCTION__, 'The '.$key.' variable cmd is not set');
                    $sub.= '';
                }
                $speaking_text.= $sub;
            }
            return $speaking_text;
        }
        else {
            return $org_text;
        }
    }

    public static

    function parseSessionId($_message)
    {
        $data = json_decode($_message);
        return $data['sessionId'];
    }

    public static

    function parseSlots($_message)
    {
        $data = json_decode($_message);
        return $data["slots"];
    }

    public static

    function debug($_source_function, $_info)
    {
        log::add("snips", 'debug', '['.$_source_function.'] '.$_info);
    }

    public static

    function getIntents()
    {
        $intents_file = dirname(__FILE__) . '/../../config_running/assistant.json';
        $json_string = file_get_contents($intents_file);
        $json_obj = json_decode($json_string, true);
        $intents = $json_obj["intents"];
        $intents_slots = array();
        foreach($intents as $intent) {
            if (strpos( strtolower($intent['name']), 'jeedom')) {
                $slots = array();
                foreach($intent["slots"] as $slot) {
                    if ($slot["required"] == true) {
                        $slots[] = $slot["name"];
                    }
                    else {
                        $slots[] = $slot["name"];
                    }
                }
                $intents_slots[$intent["id"]] = $slots;
                unset($slots);
            }
        }
        return json_encode($intents_slots);
    }

    public static

    function getTopics()
    {
        $intents = json_decode(self::getIntents() , true);
        $topics = array();
        foreach($intents as $intent => $slot) {
            array_push($topics, 'hermes/intent/' . $intent);
        }

        return $topics;
    }

    public static

    function recoverScenarioExpressions(){
        $json_string = file_get_contents(dirname(__FILE__) . '/../../config_running/reload_reference.json');
        $reference = json_decode($json_string, true);
        $intent_table = $reference['Intents'];
        $slots_table = $reference['Slots'];
        $slots_table_curr = snips::getCurrentReferTable();

        snips::debug(__FUNCTION__, 'slots_table_curr'.json_encode($slots_table_curr));

        $expressions = scenarioExpression::all();
        foreach ($expressions as $expression) {
            $old_expression_content = $expression->getExpression();
            $old_expression_option = $expression->getOptions();
            foreach ($slots_table as $slots_string => $id) {

                if (array_key_exists($slots_string, $slots_table_curr)) { // If the old intent is in the new assistant

                    if ( strpos($old_expression_content, '#'.$id.'#') || strpos($old_expression_content, '#'.$id.'#') === 0 ) {
                        $new_expression = str_replace('#'.$id.'#', '#'.$slots_string.'#', $old_expression_content);
                        snips::debug(__FUNCTION__, 'Old command entity: '.$slots_string.' with id: '.$id);
                        $expression->setExpression($new_expression);
                    }
                }
            }
            foreach ($old_expression_option as $option_name => $value) {
                preg_match_all("/#([0-9]*)#/", $value, $match);
                if (count($match[0]) == 1) {
                    if ( in_array($match[1][0], $slots_table) ) {
                        $slot_cmd_string = array_search($match[1][0], $slots_table);
                        $expression->setOptions($option_name, '#'.$slot_cmd_string.'#');
                        snips::debug(__FUNCTION__, 'found option: '.$option_name. ' change to '.$slot_cmd_string);
                    }
                }
            }
            $expression->save();
        }
    }

    public static

    function getCurrentReferTable(){
        $slots_table = array();

        $eqLogics = eqLogic::byType('snips');
        foreach($eqLogics as $eq) {
            $intent_table[$eq->getHumanName()] = $eq->getId();
            $cmds = cmd::byEqLogicId($eq->getId());
            foreach($cmds as $cmd) {
                snips::debug(__FUNCTION__, 'slot cmd: '.$cmd->getName());
                $slots_table[$cmd->getHumanName()] = $cmd->getId();
            }
            snips::debug(__FUNCTION__, 'Intent entity: '.$eq->getName());
        }

        return $slots_table;
    }

    public static

    function reloadAssistant()
    {
        snips::debug(__FUNCTION__, 'Assistant is being reloaded!');
        $assistant_file = dirname(__FILE__) . '/../../config_running/assistant.json';
        $json_string = file_get_contents($assistant_file);
        $assistant = json_decode($json_string, true);

        if ( version_compare(jeedom::version(), '3.3.3', '>=') ) {
            $obj_field = 'jeeObject';
            snips::debug(__FUNCTION__, 'Jeedom >= 3.3.3');
        }else{
            $obj_field = 'object';
            snips::debug(__FUNCTION__, 'Jeedom <= 3.3.3');
        }
        $obj = object::byName('Snips-Intents');
        if (!isset($obj) || !is_object($obj)) {
            $obj = new $obj_field();
            $obj->setName('Snips-Intents');
            snips::debug(__FUNCTION__, 'Created object: Snips-Intents');
        }
        $obj->setIsVisible(1);
        $obj->setConfiguration('id', $assistant["id"]);
        $obj->setConfiguration('name',$assistant["name"]);
        $obj->setConfiguration('hotword', $assistant['hotword']);
        $obj->setConfiguration('language', $assistant['language']);
        $obj->setConfiguration('createdAt', $assistant['createdAt']);
        $obj->save();

        foreach($assistant['intents'] as $intent) {
            if (strpos( strtolower($intent['name']), 'jeedom')) {
                $elogic = snips::byLogicalId($intent['id'], 'snips');
                if (!is_object($elogic)) {
                    $elogic = new snips();
                    $elogic->setLogicalId($intent['id']);
                    $elogic->setName($intent['name']);
                    snips::debug(__FUNCTION__, 'Created intent entity: '.$intent['name']);
                }
                $elogic->setEqType_name('snips');
                $elogic->setIsEnable(1);
                $elogic->setConfiguration('snipsType', 'Intent');
                $elogic->setConfiguration('slots', $intent['slots']);
                $elogic->setConfiguration('isSnipsConfig', 1);
                $elogic->setConfiguration('isInteraction', 0);
                $elogic->setConfiguration('language', $intent['language']);
                $elogic->setObject_id(object::byName('Snips-Intents')->getId());
                $elogic->save();
            }
        }

        snips::reloadSnipsDevices($intent['language']);

        snips::recoverScenarioExpressions();
        snips::debug(__FUNCTION__, 'Assistant loaded, restarting deamon');
        snips::deamon_start();
    }

    public static

    function reloadSnipsDevices($_lang = 'en_GB'){
        $devices = Toml::parseFile(dirname(__FILE__) . '/../../config_running/snips.toml')->{'snips-hotword'}->{'audio'};

        $master = Toml::parseFile(dirname(__FILE__) . '/../../config_running/snips.toml')->{'snips-audio-server'}->{'bind'};
        snips::debug(__FUNCTION__, '########## Type is:'.gettype($master));
        snips::debug(__FUNCTION__, '########## Value is:'.$master);
        if (isset($master)) {
            $master = substr($master,0,strpos($master, '@'));
            snips::debug(__FUNCTION__, '########## Not default:'.$master);
        }else{
            $master = 'default';
            snips::debug(__FUNCTION__, '########## Default:'.$master);
        }
        $res = config::save('masterSite', $master, 'snips');

        snips::debug(__FUNCTION__, '########## Saved with result:'.$res);

        if (count($devices) == 0) {
            snips::createSnipsDevices('default', $_lang);
        }else{
            foreach ($devices as $key => $device) {
                $siteName = str_replace('@mqtt', '', $device);
                snips::createSnipsDevices($siteName, $_lang);
            }
        }
    }

    public static

    function createSnipsDevices($_site_name, $_lang){
        $elogic = snips::byLogicalId('Snips-TTS-'.$_site_name, 'snips');
        if (!is_object($elogic)) {
            $elogic = new snips();
            $elogic->setName('Snips-TTS-'.$_site_name);
            $elogic->setLogicalId('Snips-TTS-'.$_site_name);
            snips::debug(__FUNCTION__, 'Created TTS entity: Snips-TTS-'.$_site_name);
        }
        $elogic->setEqType_name('snips');
        $elogic->setIsEnable(1);
        $elogic->setConfiguration('snipsType', 'TTS');
        $elogic->setConfiguration('language', $_lang);
        $elogic->setConfiguration('siteName', $_site_name);
        $elogic->setObject_id(object::byName('Snips-Intents')->getId());
        $elogic->save();
    }

    public static

    function deleteAssistant()
    {
        $intent_table = array();
        $slots_table = array();

        $eqLogics = eqLogic::byType('snips');
        foreach($eqLogics as $eq) {
            $intent_table[$eq->getHumanName()] = $eq->getId();
            $cmds = cmd::byEqLogicId($eq->getId());
            foreach($cmds as $cmd) {
                snips::debug(__FUNCTION__, 'Removed slot cmd: '.$cmd->getName());
                $slots_table[$cmd->getHumanName()] = $cmd->getId();
                $cmd->remove();
            }
            snips::debug(__FUNCTION__, 'Removed intent entity: '.$eq->getName());
            $eq->remove();
        }

        $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSiteId');
        if (is_object($var)) {
            snips::debug(__FUNCTION__, 'Removed variable: '.$var->getKey());
            $var->remove();
        }

        $obj = object::byName('Snips-Intents');
        if (is_object($obj)) {
            $obj->remove();
            snips::debug(__FUNCTION__, 'Removed object: Snips-Intents');
        }

        $reload_reference = array(
            "Intents" => $intent_table,
            "Slots" => $slots_table
        );
        $file = fopen(dirname(__FILE__) . '/../../config_running/reload_reference.json', 'w');
        $res = fwrite($file, json_encode($reload_reference));
    }

    public static

    function extractSlotsValues($_payload_slots){
        $result = array('slots_values' => array(),
                        'slots_values_org' => array());

        foreach ($_payload_slots as $slot) {
            snips::debug(__FUNCTION__, 'Checking slots: '.$slot->{'slotName'});

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
                snips::debug(__FUNCTION__, 'Yes, this exists in the array :'.$slot->{'slotName'});
                $result['slots_values'][$slot->{'slotName'}] = $single_ready_value;
                $result['slots_values_org'][$slot->{'slotName'}] .= '&'.$single_ready_value_org;
            }else{
                snips::debug(__FUNCTION__, 'No, this does not exist in the array :'.$slot->{'slotName'});
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
        snips::debug(__FUNCTION__, 'Intent:' . $intent_name . ' siteId:' . $site_id . ' sessionId:' . $session_id);

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
            snips::sayFeedback($reply['reply'], $session_id);
        }
        else {
            $bindings_match_coming_slots = array();
            foreach($bindings as $binding) {
                snips::debug(__FUNCTION__, 'Cur binding name : ' . $binding['name']);
                snips::debug(__FUNCTION__, 'Binding count is : ' . count($binding['nsr_slots']));
                snips::debug(__FUNCTION__, 'Snips count is : ' . count($slots_values));
                if (count($binding['nsr_slots']) === count($slots_values)) {
                    snips::debug(__FUNCTION__, 'Binding has corr number of slot: ' . $binding['name']);
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
                        snips::debug(__FUNCTION__, '[Condition] Condition Aft string: '.$condition['aft']);
                        if (is_string($condition['aft'])) {
                            $aft_value = explode(',',strtolower(str_replace(' ', '', $condition['aft'])));
                            foreach ($aft_value as $key => $value) {
                                snips::debug(__FUNCTION__, '[Condition] Condition Aft value index: '.$key.' value: ' . $value);
                            }
                            //$aft_value = strtolower(str_replace(' ', '', $condition['aft']));
                        }
                        else {
                            $aft_value = array($condition['aft']);
                            snips::debug(__FUNCTION__, '[Condition] Condition Aft value is : ' . $aft_value);
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

                        snips::setSlotsCmd($slots_values, $intent_name, $options);

                        $execution_return_msg = scenarioExpression::createAndExec('action', $action['cmd'], $options);
                        if (is_string($execution_return_msg) && $execution_return_msg!='') {
                            if (config::byKey('dynamicSnipsTTS', 'snips', 0) && cmd::byString($binding['ttsPlayer'])->getConfiguration('snipsType') == 'TTS') {
                                snips::playTTS('#[Snips-Intents][Snips-TTS-'.$site_id.'][say]#', $execution_return_msg);
                            }else{
                                snips::playTTS($binding['ttsPlayer'], $execution_return_msg);
                            }
                        }
                    }

                    $text = snips::generateFeedback($binding['ttsMessage'], (array)$binding['ttsVar'], false);

                    snips::debug(__FUNCTION__, '[Binding Execution] Generated text is ' . $text);
                    snips::debug(__FUNCTION__, '[Binding Execution] Orginal text is ' . $binding['ttsMessage']);

                    snips::debug(__FUNCTION__, '[Binding Execution] Player is ' . $binding['ttsPlayer']);

                    $tts_player_cmd = cmd::byString($binding['ttsPlayer']);

                    if (config::byKey('dynamicSnipsTTS', 'snips', 0) && $tts_player_cmd->getConfiguration('snipsType') == 'TTS') {
                        snips::playTTS('#[Snips-Intents][Snips-TTS-'.$site_id.'][say]#', $text, $session_id);
                    }else{
                        snips::playTTS($binding['ttsPlayer'], $text, $session_id);
                    }
                }
            }else if(count($bindings_with_correct_condition) == 0 && !$callback_called){

                $orgMessage = config::byKey('defaultTTS', 'snips', 'Désolé, je n’ai pas compris');

                $messages = snips::generateFeedback($orgMessage, null, false);
                snips::playTTS('#[Snips-Intents][Snips-TTS-'.$site_id.'][say]#', $messages, $session_id);
            }
        }
        sleep(1);
        snips::resetSlotsCmd();
    }

    public static

    function executeCallbackScenario($_parameters, $_slots_values_org, $_payload){
        snips::debug(__FUNCTION__, ' Intent: ' . $_payload->{'intent'}->{'intentName'});
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
            $tags['#intent#'] = substr($_payload->{'intent'}->{'intentName'},strpos($_payload->{'intent'}->{'intentName'},':')+1);

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

            snips::playTTS('#[Snips-Intents][Snips-TTS-'.$_payload->{'siteId'}.'][say]#', $execution_return_msg, $_payload->{'sessionId'});
        }
        return $execution_return_msg;
    }

    public static

    function setSlotsCmd($_slots_values, $_intent, $_options = null)
    {
        snips::debug(__FUNCTION__, 'Set slots cmd values');
        $eq = eqLogic::byLogicalId($_intent, 'snips');
        if (is_object($eq)) {
            foreach($_slots_values as $slot => $value) {
                snips::debug(__FUNCTION__, 'Slots name is :' . $slot);
                $cmd = $eq->getCmd(null, $slot);
                if (is_object($cmd)) {
                    if ($_options) {
                        snips::debug(__FUNCTION__, 'Slots option entered, entityId is:' . $cmd->getConfiguration('entityId'));
                        if ($cmd->getConfiguration('entityId') == 'snips/percentage') {
                            $org = $value;
                            $value = snips::percentageRemap($_options['LT'], $_options['HT'], $value);
                            $cmd->setConfiguration('orgVal', $org);
                            snips::debug(__FUNCTION__, 'Slots is percentage, value after convert:' . $value);
                        }
                    }
                    $eq->checkAndUpdateCmd($cmd, $value);
                    $cmd->setValue($value);
                    $cmd->save();
                }
            }
        }else{
            snips::debug(__FUNCTION__, 'Did not find entiry:' . $_intent);
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
        snips::debug(__FUNCTION__, 'Reset all the slots');
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
                snips::debug(__FUNCTION__, 'Set '.$var->getValue().' => snipsMsgSiteId');
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
            snips::debug(__FUNCTION__, 'json_string type is: ' . gettype($json_string));
            preg_match_all('/#[^#]*[0-9]+#/', $json_string, $matches);
            $human_cmd = cmd::cmdToHumanReadable($matches[0]);
            foreach($human_cmd as $key => $cmd_text) {
                $json_string = str_replace($matches[0][$key], $cmd_text, $json_string);
            }

            preg_match_all('/("pre":")[^("pre":")]*[0-9]+"/', $json_string, $matches_c);
            $matches_c1 = $matches_c;
            foreach($matches_c[0] as $key => $value) {
                $matches_c[0][$key] = str_replace('"pre":"', '#', $value);
                snips::debug(__FUNCTION__, '1st : ' . $matches_c[0][$key]);
                $matches_c[0][$key] = str_replace('"', '#', $matches_c[0][$key]);
                snips::debug(__FUNCTION__, '2nd : ' . $matches_c[0][$key]);
            }

            $humand_cond = cmd::cmdToHumanReadable($matches_c[0]);
            foreach($humand_cond as $key => $cmd_text) {
                snips::debug(__FUNCTION__, 'key word : ' . $matches_c1[0][$key] . ' replace ' . '"pre":"' . $cmd_text . '"');
                $json_string = str_replace($matches_c1[0][$key], '"pre":"' . $cmd_text . '"', $json_string);
            }

            $aft_bindings = json_decode($json_string);
            snips::debug(__FUNCTION__, 'vars: ' . $aft_bindings);
            $binding_conf[$eq->getName() ] = $aft_bindings;
        }

        if ($_output) {
            $file = fopen(dirname(__FILE__) . '/../../config_backup/' . $_name . '.json', 'w');
            $res = fwrite($file, json_encode($binding_conf));
            if ($res) {
                snips::debug(__FUNCTION__, 'Success output');
            }
            else {
                snips::debug(__FUNCTION__, 'Faild output');
            }
        }else{
            return json_encode($binding_conf);
        }

    }

    public static

    function importConfigration($_configFileName = null, $_configurationJson = null)
    {
        if (isset($_configurationJson) && !isset($_configFileName)) {
            snips::debug(__FUNCTION__, 'Asked to internally reload config file');
            $json_string = $_configurationJson;
        }else if (!isset($_configurationJson) && isset($_configFileName)){
            snips::debug(__FUNCTION__, 'Asked to import file: ' . $_configFileName);
            $json_string = file_get_contents(dirname(__FILE__) . '/../../config_backup/' . $_configFileName);
        }

        preg_match_all('/("pre":")(#.*?#)(")/', $json_string, $matches);
        $cmd_ids = cmd::humanReadableToCmd($matches[2]);

        foreach($cmd_ids as $key => $cmd_id) {
            $cmd_id = str_replace('#', '', $cmd_id);

            snips::debug(__FUNCTION__, 'key word : ' . '"pre":"'.$matches[2][$key].'"' . ' replace ' . '"pre":"'.$cmd_id.'"');

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
        snips::debug(__FUNCTION__, 'Result code: '.$res);
        return $res;
    }

    public static

    function fetchAssistantJson($_usrename, $_password)
    {
        $ip_addr = config::byKey('mqttAddr', 'snips', '127.0.0.1');
        snips::debug(__FUNCTION__, 'Connection : Trying to connect to: '.$ip_addr);
        $connection = ssh2_connect($ip_addr, 22);
        if (!$connection) {
            snips::debug(__FUNCTION__, 'Connection: Faild code: -2');
            return -2;
        }

        $resp = ssh2_auth_password($connection, $_usrename, $_password);
        if ($resp) {
            snips::debug(__FUNCTION__, 'Verification: Success');
        }
        else {
            snips::debug(__FUNCTION__, 'Verification: Faild code: -1');
            return -1;
        }

        $res = ssh2_scp_recv($connection, '/usr/share/snips/assistant/assistant.json', dirname(__FILE__) . '/../../config_running/assistant.json');
        $res0 = ssh2_scp_recv($connection, '/etc/snips.toml', dirname(__FILE__) . '/../../config_running/snips.toml');
        if ($res && $res0) {
            ssh2_exec($connection, 'exit');
            unset($connection);
            snips::debug(__FUNCTION__, 'Fecth resutlt : Success');
            return 1;
        }
        else {
            ssh2_exec($connection, 'exit');
            unset($connection);
            snips::debug(__FUNCTION__, 'Fecth resutlt : Faild code: 0');
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
                snips::debug(__FUNCTION__, 'Shift action: ' . $cmdSet->getHumanName() . ', from -> ' . $options['slider'] . ' to ->' . $current_val);
            }else{
                snips::debug(__FUNCTION__, 'Can not find cmd: '. $light['LIGHT_BRIGHTNESS_ACTION']);
            }
        }
    }

    public static

    function findDevice($_site_id){
        $lang = translate::getLanguage();
        if ($lang == 'fr_FR') {
            $msg = 'Dispositif '.$_site_id.' est ici!';
            snips::sayFeedback($msg, $_session_id = null, 'fr-FR', $_site_id);
        }else if ($lang == 'en_US') {
            $msg = 'Device '.$_site_id.' is here!';
            snips::sayFeedback($msg, $_session_id = null, 'en-GB', $_site_id);
        }
        snips::debug(__FUNCTION__, 'Test device: '.$_site_id);
    }

    public static

    function postConfiguration(){
        if(!config::byKey('isVarMsgSession', 'snips', 0)){
            $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSession');
            if (is_object($var)) {
                $var->remove();
            }
            snips::debug(__FUNCTION__, 'Removed variable snipsMsgSession');
        }else{
            $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSession');
            if (!is_object($var)) {
                $var = new dataStore();
                $var->setKey('snipsMsgSession');
                snips::debug(__FUNCTION__, 'Created variable snipsMsgSession');
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
            snips::debug(__FUNCTION__, 'Removed variable snipsMsgSiteId');
        }else{
            $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSiteId');
            if (!is_object($var)) {
                $var = new dataStore();
                $var->setKey('snipsMsgSiteId');
                snips::debug(__FUNCTION__, 'Created variable snipsMsgSiteId');
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
            snips::debug(__FUNCTION__, 'Removed variable snipsMsgHotwordId');
        }else{
            $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgHotwordId');
            if (!is_object($var)) {
                $var = new dataStore();
                $var->setKey('snipsMsgHotwordId');
                snips::debug(__FUNCTION__, 'Created variable snipsMsgHotwordId');
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
        snips::debug(__FUNCTION__, "post saved");
        if($this->getConfiguration('snipsType') == 'Intent'){
            $slots = $this->getConfiguration('slots');
            foreach($slots as $slot) {
                $slot_cmd = $this->getCmd(null, $slot['name']);
                if (!is_object($slot_cmd)) {
                    snips::debug(__FUNCTION__, 'Created slot cmd: ' . $slot['name']);
                    $slot_cmd = new snipsCmd();
                }
                $slot_cmd->setName($slot['name']);
                $slot_cmd->setEqLogic_id($this->getId());
                $slot_cmd->setLogicalId($slot['name']);
                $slot_cmd->setType('info');
                $slot_cmd->setSubType('string');
                $slot_cmd->setConfiguration('id', $slot['id']);
                $slot_cmd->setConfiguration('entityId', $slot['entityId']);
                $slot_cmd->setConfiguration('missingQuestion', $slot['missingQuestion']);
                $slot_cmd->setConfiguration('required', $slot['required']);
                $slot_cmd->save();
            }
        }else if($this->getConfiguration('snipsType') == 'TTS'){
            $tts_cmd = $this->getCmd(null, 'say');
            if (!is_object($tts_cmd)) {
                snips::debug(__FUNCTION__, 'Created tts cmd: say');
                $tts_cmd = new snipsCmd();
                $tts_cmd->setName('say');
                $tts_cmd->setLogicalId('say');
            }
            $tts_cmd->setEqLogic_id($this->getId());
            $tts_cmd->setType('action');
            $tts_cmd->setSubType('message');
            $tts_cmd->setDisplay('title_disable', 1);
            $tts_cmd->setDisplay('message_placeholder', 'Message');
            $tts_cmd->setConfiguration('siteId', $this->getConfiguration('siteName'));
            $tts_cmd->save();

            $ask_cmd = $this->getCmd(null, 'ask');
            if (!is_object($ask_cmd)) {
                snips::debug(__FUNCTION__, 'Created ask cmd: ask');
                $ask_cmd = new snipsCmd();
                $ask_cmd->setName('ask');
                $ask_cmd->setLogicalId('ask');
            }
            $ask_cmd->setEqLogic_id($this->getId());
            $ask_cmd->setType('action');
            $ask_cmd->setSubType('message');
            $ask_cmd->setDisplay('title_placeholder', 'expected intent');
            $ask_cmd->setDisplay('message_placeholder', 'Question');
            $ask_cmd->setConfiguration('siteId', $this->getConfiguration('siteName'));
            $ask_cmd->save();
        }
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
    public

    function execute($_options = array())
    {
        $eqlogic = $this->getEqLogic();
        switch ($this->getLogicalId()) {
            case 'say':
                $site_id = $this->getConfiguration('siteId');
                snips::debug(__FUNCTION__, 'cmd: say');
                snips::debug(__FUNCTION__, 'siteId: '.$site_id.' asked to say :'.$_options['message']);
                snips::sayFeedback($_options['message'], $_options['sessionId'], $eqlogic->getConfiguration('language'), $site_id);
                break;
            case 'ask':
                snips::debug(__FUNCTION__, 'cmd: ask');
                $site_id = $this->getConfiguration('siteId');
                preg_match_all("/(\[.*?\])/", $_options['answer'][0], $match_intent);
                $_ans_intent = str_replace('[', '', $match_intent[0][0]);
                $_ans_intent = str_replace(']', '', $_ans_intent);

                snips::startRequest($_ans_intent, $_options['message'], $site_id);
                break;
        }
    }
}
