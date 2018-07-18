<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

include 'ChromePhp.php';

//ini_set("display_errors","On");
//error_reporting(E_ALL);

//ChromePhp::log('Hello console!');

class snips extends eqLogic {

    /*     * ***********************Methode static*************************** */

    public static function resetMqtt(){

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
            snips::debug('Created snips cron: mqttClient');
        }
    }


    public static function health() {
        $return = array();
        $socket = socket_create(AF_INET, SOCK_STREAM, 0);

        $addr = config::byKey('mqttAddr', 'snips', '127.0.0.1');
        $port = config::byKey('mqttPort', 'snips', '1883');

        $server = socket_connect ($socket , $addr, $port);

        $return[] = array(
          'test' => __('Mosquitto', __FILE__),
          'result' => ($server) ? __('OK', __FILE__) : __('NOK', __FILE__),
          'advice' => ($server) ? '' : __('Indique si Mosquitto est disponible', __FILE__),
          'state' => $server,
        );
        return $return;
    }
    
    public static function deamon_info() {
        $return = array();
        $return['log'] = '';
        $return['state'] = 'nok';
        $cron = cron::byClassAndFunction('snips', 'mqttClient');
        if (is_object($cron) && $cron->running()) {
            $return['state'] = 'ok';
        }

        $dependancy_info = self::dependancy_info();

        if ($dependancy_info['state'] == 'ok') {
            $return['launchable'] = 'ok';
        }

        return $return;
    }

    public static function deamon_start($_debug = false) {
        self::deamon_stop();
        $deamon_info = self::deamon_info();
        if ($deamon_info['launchable'] != 'ok') {
            throw new Exception(__('Please check your configuration', __FILE__));
        }

        $cron = cron::byClassAndFunction('snips', 'mqttClient');

        if (!is_object($cron)) {
            throw new Exception(__('Can not find task corn ', __FILE__));
        }
        $cron->run();
    }

    public static function deamon_stop() {
        $cron = cron::byClassAndFunction('snips', 'mqttClient');

        if (!is_object($cron)) {
            throw new Exception(__('Can not find taks corn', __FILE__));
        }
        $cron->halt();
    }

    public static function dependancy_info() {
        $return = array();
        $return['log'] = 'SNIPS_dep';
        $return['state'] = 'nok';
        $cmd = "dpkg -l | grep mosquitto";
        exec($cmd, $output, $return_var);
        //lib PHP exist
        $libphp = extension_loaded('mosquitto');
        if ($output[0] != "" && $libphp) {
            $return['state'] = 'ok';
        }
        return $return;
    }

    public static function dependancy_install() {
        self::debug('Installation of dependences');
        
        $resource_path = realpath(dirname(__FILE__) . '/../../resources');
        passthru('sudo /bin/bash ' . $resource_path . '/install.sh ' . $resource_path . ' > ' . log::getPathToLog('MQTT_dep') . ' 2>&1 &');
        return true;
    }
    
    public static function mqttClient() {
        
        $addr = config::byKey('mqttAddr', 'snips', '127.0.0.1');
        $port = intval(config::byKey('mqttPort', 'snips', '1883'));
        //fwrite(STDOUT, "deamonFuncion!");

        snips::debug('Connection Parameters, Host : ' . $addr . ', Port : ' . $port);

        $client = new Mosquitto\Client("JeedomSnips");
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
            
            foreach($topics as $topic){
                $client->subscribe($topic, 0); // Subcribe to all intents with QoC = 0
                //fwrite(STDOUT, $topic);
                //log::add('snips', 'info', 'topic :'.$topic );
                snips::debug('Subscribe to topic :' . $topic);
            }
            //self::debug('trying to connect');
            //$client->subscribe('#', 0);
            
            //$client->subscribe('hermes/intent/lightsTurnOff', 0);
            //$client->subscribe('hermes/intent/lightsTurnUp', 0);
            //$client->subscribe('hermes/intent/lightsTurnOnSet', 0);
            //$client->subscribe('hermes/intent/lightsTurnDown', 0);

            //$client->loopForever();
           while (true) { $client->loop(); }
        }
        catch (Exception $e){
            snips::debug($e->getMessage());
        }
    }
    
    public static function connect( $r, $message ) {
        snips::debug('Connected to mosquitto with code ' . $r . ' ' . $message);
        config::save('status', '1',  'snips');
    }

    public static function disconnect( $r ) {
        snips::debug('Disconnected to mosquitto with code ' . $r);
        config::save('status', '0',  'snips');
    }

    public static function subscribe( ) {
        snips::debug( 'Subscribe to topics');
    }

    public static function logmq( $code, $str ) {
        if (strpos($str,'PINGREQ') === false && strpos($str,'PINGRESP') === false) {
            snips::debug( $code . ' : ' . $str);
        }
    }

    public static function message( $message ) {
        
        $topics = snips::getTopics();
        $intents_slots = snips::getIntents();

        snips::debug('received something');
        if(in_array($message->topic, $topics) == false){

            return;
        } 
        else{
            
            snips::findAndDoAction( json_decode($message->payload) );
        }
    }

    public static function sayFeedback($text, $session_id = null, $lang = 'en_GB'){
        if($session_id == null){
            ChromePhp::log('Only say: '.$text);
            $topic = 'hermes/tts/say';
            $payload = array('text' => str_replace('{#}', 'Value', $text), "siteId" => "default", "lang" => $lang);

            self::publish($topic, json_encode($payload));
        }else{
            ChromePhp::log('Endsession say: '.$text);
            $topic = 'hermes/dialogueManager/endSession';
            $payload = array('text' => $text, "sessionId" => $session_id);

            self::publish($topic, json_encode($payload));
        }
       
    }

    public static function generateFeedback($org_text, $vars, $test_play = false){
        snips::debug('generating feedback text');
        //snips::debug('vars is '.var_dump($vars));

        $string_subs = explode('{#}',$org_text);

        $speaking_text = '';
        if (!empty($string_subs)) {
            foreach($string_subs as $key => $sub){
                if($test_play) {
                    if (isset($vars[$key])){

                        $cmd = cmd::byString($vars[$key]);
                        snips::debug('[string] cmd id is '.$cmd_id);

                        if(is_object($cmd)){
                            if ($cmd->getName() == 'intensity_percent' || $cmd->getName() == 'intensity_percentage'){
                                $sub .= $cmd->getConfiguration('orgVal');
                            }else{
                                if($cmd->getValue()){
                                    $sub .= $cmd->getValue();
                                }else{ 
                                    $sub .= $cmd->getCache('value','NULL');
                                }
                            }
                        }
                        
                            

                    }else{
                        snips::debug('[string] cmd id is not set');
                        $sub .= '';
                    }
                }else{
                    if (isset($vars[$key])) {

                        $cmd_id = str_replace('#', '', $vars[$key]);
                        $cmd = cmd::byId($cmd_id);

                        snips::debug('[number] cmd id is '.$cmd_id);
                        

                        if(is_object($cmd)){
                            if ($cmd->getName() == 'intensity_percent' || $cmd->getName() == 'intensity_percentage'){
                                $sub .= $cmd->getConfiguration('orgVal');
                            }else{

                                if($cmd->getValue()){
                                    $sub .= $cmd->getValue();
                                    
                                }else{ 
                                    $sub .= $cmd->getCache('value','NULL');
                                }
                            }
                        }

                    }else{
                        snips::debug('[number] cmd id not set');
                        $sub .= '';
                    }
                }
                    
                $speaking_text .= $sub;
            }
            return $speaking_text;
        }else{
            return $org_text;
        }
    }

    public static function publish($topic, $payload){
        $addr = config::byKey('mqttAddr', 'snips', '127.0.0.1');
        $port = intval(config::byKey('mqttPort', 'snips', 1883));

        $client = new Mosquitto\Client('JeedomSnipsPub');
        $client->connect($addr, $port, 60);
        $client->publish($topic, $payload);
        for ($i = 0; $i < 100; $i++) {
      
            $client->loop(1);
        }

        $client->disconnect();
        unset($client);
    }

    public static function parseSessionId($message){

        $data = json_decode($message);

        return $data['sessionId'];
    }

    public static function parseSlots($message){
        $data = json_decode($message);

        return $data["slots"];
    }

    //add log
    public static function debug($info, $in_console = false){
        if($in_console){ChromePhp::log($info);}
        //log::add('snips', 'debug', $info);
        else{fwrite(STDOUT, $info.'\n');}
    }

    public static function getIntents(){

        $intents_file = "/var/www/html/plugins/snips/assistant.json";

        $json_string = file_get_contents($intents_file);
        //snips::debug($json_string, true);
        $json_obj = json_decode($json_string,true);

        $intents = $json_obj["intents"];

        $intents_slots = array();

        foreach($intents as $intent){

            $slots = array();

            foreach($intent["slots"] as $slot){

                if($slot["required"] == true){
                    $slots[] = $slot["name"]; 
                    //Required or not should be identified by last character of this slot, but using '*' will have bugs. 
                    //Should find another way to seperate 
                }else{
                    $slots[] = $slot["name"];
                }
                
            }
            $intents_slots[$intent["id"]] = $slots;

            unset($slots);
        }

        return json_encode($intents_slots);
    }

    public static function getTopics(){
         
        $intents = json_decode(self::getIntents(), true);

        $topics = array();
        foreach($intents as $intent => $slot){
            array_push($topics, 'hermes/intent/'.$intent);
            self::debug('hermes/intent/'.$intent);
        }

        //print_r($topics);
        return $topics;

    }

    public static function reloadAssistant(){
        // Create an object to contant all the intent
        $obj = object::byName('Snips-Intents');
        if (!isset($obj) || !is_object($obj)) {
            $obj = new object();
        }
        $obj->setName('Snips-Intents');
        $obj->setIsVisible(1);
        $obj->save();

        // Get assistant.json file, build assistant following the contains of this file
        $assistant_file = "/var/www/html/plugins/snips/assistant.json";

        $json_string = file_get_contents($assistant_file);
        snips::debug($json_string, true);
        $assistant = json_decode($json_string,true);

        $language = $assistant["language"];

        // Add all the intents to configuration
        //$intents = json_decode(self::getIntents(), true);

        foreach($assistant['intents'] as $intent){

            if(is_object(snips::byLogicalId($intent['id'], 'snips'))){
                return;
            }else{
                // Create intent as devices 
                $elogic = new snips();
                $elogic->setEqType_name('snips');
                $elogic->setLogicalId($intent['id']);
                $elogic->setName($intent['name']);
                $elogic->setIsEnable(1);
                $elogic->setConfiguration('slots', $intent['slots']);
                $elogic->setConfiguration('isSnipsConfig', 1);
                $elogic->setConfiguration('isInteraction', 0);
                $elogic->setConfiguration('language', $intent['language']);
                $elogic->setObject_id(object::byName('Snips-Intents')->getId());
                $elogic->save();
            }
        }

    }

    public static function deleteAssistant(){
        //self::debug("remove assistant");
        $obj = object::byName('Snips-Intents');
        if (is_object($obj)) {
            $obj->remove();
        }


        $eqLogics = eqLogic::byType('snips');

        //self::debug('Current equiopments is :'.gettype($eqLogics));

        foreach($eqLogics as $eq){
            //self::debug('Removing equipment: '.$eq->getName());
            $cmds = snipsCmd::byEqLogicId($eq->getLogicalId);

            foreach ($cmds as $cmd) {
                //self::debug('Removing cmd: '.$cmd->getName());
                $cmd->remove();
            }
            
            $eq->remove();
        }
    }

    public static function findAndDoAction($payload){

        $intent_name = $payload->{'intent'}->{'intentName'};
        $site_id = $payload->{'siteId'}; // Value: default
        $session_id = $payload->{'sessionId'};
        $query_input = $payload->{'input'};

        snips::debug('findAndDoAction, Intent:'.$intent_name.' siteId:'.$site_id.' sessionId:'.$session_id);

        $slots_values = array();

        foreach ($payload->{'slots'} as $slot) {

            if(is_string($slot->{'value'}->{'value'})){
                // Not speace sensitive and case sensitive
                $slots_values[$slot->{'slotName'}] = strtolower(str_replace(' ', '', $slot->{'value'}->{'value'}));
            }else{
                $slots_values[$slot->{'slotName'}] = $slot->{'value'}->{'value'};
            }

        }

        snips::setSlotsCmd($slots_values, $intent_name);

        // Get all the binding configuration by [intentName]
        $eqLogic = eqLogic::byLogicalId($intent_name, 'snips');

        $bindings = $eqLogic->getConfiguration('bindings');

        // If this equipment is enabled, go with snips configuration, otherwise send input text to interaction
        if(!$eqLogic->getConfiguration('isSnipsConfig')){
            // Use system interaction querys 
            $param = array();
            // Send to interaction Queryer 
            $reply = interactQuery::tryToReply($query_input, $param);
            // Play feedback text
            snips::sayFeedback($reply['reply'], $session_id);

        }else{
            // Use snips binding configuration table

            // Find the bindings whose necessary slots match with incoming payload
            $bindings_match_coming_slots = array();
            foreach ($bindings as $binding) {
                // If the number of slots match with each other
                snips::debug('[Slots] Cur binding name : '.$binding['name']);
                snips::debug('[Slots] Binding count is : '.count($binding['nsr_slots']));
                snips::debug('[Slots] Snips count is : '.count($slots_values));


                if(count($binding['nsr_slots']) === count($slots_values)){
                    snips::debug('[Slots] Binding has corr number of slot: '.$binding['name']);
                    // If the name of slots match with each other
                    $slot_all_exists_indicator = 1; 
                    foreach ($binding['nsr_slots'] as $slot) {
                        if(array_key_exists($slot, $slots_values)){
                            $slot_all_exists_indicator *= 1;
                        }else{
                            $slot_all_exists_indicator *= 0;
                        }
                    }

                    if($slot_all_exists_indicator){
                        $bindings_match_coming_slots[] = $binding;
                    }
                }
            }

            // Find bindings which has correct condition or no condition required
            $bindings_with_correct_condition = array();
            foreach ($bindings_match_coming_slots as $bindings_match_coming_slot) {

                if(!empty($bindings_match_coming_slot['condition'])){
                    $condition_all_true_indicator = 1; 
                    foreach ($bindings_match_coming_slot['condition'] as $condition) {

                        // Condition setup
                        $cmd = cmd::byId($condition['pre']);

                        if (is_string($cmd->getCache('value','NULL'))) {
                            $pre_value = strtolower(str_replace(' ', '', $cmd->getCache('value','NULL')));
                        }else{
                            $pre_value = $cmd->getCache('value','NULL');
                        }

                        // If the condition is match to a string, desensitive of 'case' and 'speace'
                        if (is_string($condition['aft'])) {
                            $aft_value = strtolower(str_replace(' ', '', $condition['aft']));
                        }else{
                            $aft_value = $condition['aft'];
                        }
                        
                        snips::debug('[Condition] Condition Aft value is : '.$aft_value);

                        if($pre_value == $aft_value){
                            $condition_all_true_indicator *= 1;
                        }else{
                            $condition_all_true_indicator *= 0;
                        }

                    }
                    if($condition_all_true_indicator){
                        if ($bindings_match_coming_slot['enable']) {
                            $bindings_with_correct_condition[] = $bindings_match_coming_slot;
                        }
                    }
                }else{
                    if ($bindings_match_coming_slot['enable']) {
                        $bindings_with_correct_condition[] = $bindings_match_coming_slot;
                    }
                }
                
            } 

            // Execute all the possible bindings
            foreach ($bindings_with_correct_condition as $binding) {
                foreach ($binding['action'] as $action) {

                    $options = $action['options'];

                    // Deleted enable function for actions $action['options']['enable']
                    if(true){
                        snips::setSlotsCmd($slots_values, $intent_name, $options);

                        scenarioExpression::createAndExec('action', $action['cmd'], $options);
                        
                    }else{
                        snips::debug('Found binding action, but it is not enabled');
                    }

                }
                // Feed back when all the action are done
                $text = snips::generateFeedback($binding['tts']['text'], (array)$binding['tts']['vars'], false);

                snips::debug('Text generated is '.$text.' |||| Orginal text is '.$binding['tts']['text']);
                snips::sayFeedback($text, $session_id);   
            }
        }

        //snips::resetSlotsCmd($slots_values, $intent_name);
        //snips::resetSlotsCmd();
        /// ----- works
    }

    public static function setSlotsCmd($slots_values, $intent, $_options = null){
        snips::debug('Set slots cmd values');

        $eq = eqLogic::byLogicalId($intent, 'snips');

        foreach ($slots_values as $slot => $value) {

            snips::debug('[setSlotsCmd] Slots name is :'.$slot);
            
            $cmd = $eq->getCmd(null, $slot);

            // adaption for percentage
            if ($_options) {
                if ($cmd->getSubType() == 'snips/percentage') {

                    $org = $value; 
                    $value = snips::percentageRemap($_options['LT'], $_options['HT'], $value);
                    $cmd->setConfiguration('orgVal', $org);

                    snips::debug('[setSlotsCmd] Slots is percentage, value after convert:'.$value);
                }
            }
            


            $eq->checkAndUpdateCmd($cmd, $value);
            $cmd->setValue($value);
            $cmd->save();
            //snips::debug('Setting slots: '.$slot.' with value: '.$value);
        }
    }

    public static function percentageRemap($_LT, $_HT, $_percentage ){

        $real_value = ($_HT - $_LT) * ($_percentage/100);

        // Prevent from overflow
        if ($real_value > $_HT) {
            $real_value = $_HT;
        }else if ($real_value < $_LT) {
            $real_value = $_LT;
        }

        return $real_value;
    }

    public static function resetSlotsCmd($slots_values = false , $intent = false){
        if ($slots_values == false && $intent == false) {
            
            $eqs = eqLogic::byType('snips');

            foreach ($eqs as $eq) {
                $cmds = $eq->getCmd();

                foreach ($cmds as $cmd) {
                    $cmd->setCache('value',null);
                    $cmd->setValue(null);
                    $cmd->setConfiguration('orgVal', null);
                    $cmd->save();
                }
            }

            
        }else{

            $eq = eqLogic::byLogicalId($intent, 'snips');

            foreach ($slots_values as $slot => $value) {

                $cmd = $eq->getCmd(null, $slot);
                $cmd->setCache('value');
                $cmd->setValue(null);
                $cmd->setConfiguration('orgVal', null);
                $cmd->save();
            } 
        }
        
    }

    static function exportConfigration($name){

        $binding_conf = array();

        $eqs = eqLogic::byType('snips');
        foreach ($eqs as $eq) {
            $binding_conf[$eq->getLogicalId()] = $eq->getConfiguration('bindings');
        }


        $file = fopen(dirname(__FILE__).'/../../config_backup/'.$name.'.json', 'w');
        $res = fwrite($file, json_encode($binding_conf));

        //$json_string = json_encode($binding_conf);
        //$res = file_put_contents('/../../config_backup/'.$name.'.json', $json_string);
        if ($res) {
            snips::debug('success output', true);
            # code...
        }else{
            snips::debug('faild output', true);
        }

        //'/../../config_backup/'.
    }

    static function importConfigration($name){

    }
    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {

    }

    public function postInsert() {
        
    }

    public function preSave() {
        $slots = $this->getConfiguration('slots');
        $slotSet = array();
        foreach ($slots as $slot) {
            $slotSet[] = $slot['name'];
        }

        $bindings = $this->getConfiguration('bindings');
        
        foreach ($bindings as $key => $binding) {
            $necessary_slots = array();

            $conditions = $binding['condition'];
            foreach ($conditions as $condition) {
                if(!in_array(cmd::byId($condition['pre'])->getName(), $necessary_slots)){
                    $necessary_slots[] = cmd::byId($condition['pre'])->getName();
                }
            }

            $actions = $binding['action'];
            foreach ($actions as $action) {
                $options = $action['options'];
                foreach ($options as $option) {
                    if (preg_match("/^#.*#$/", $option)) {
                    if (in_array(cmd::byId(str_replace('#', '', $option))->getName(), $slotSet)) {
                    if (!in_array(cmd::byId(str_replace('#', '', $option))->getName(), $necessary_slots)) {
                        $necessary_slots[] = cmd::byId(str_replace('#', '', $option))->getName();
                    }
                    }
                    }   
                }
            }

            if (!empty($necessary_slots)) {
                $bindings[$key]['nsr_slots'] = $necessary_slots;
            }
            
            unset($necessary_slots);
            //snips::debug('Untracked slot :'.var_dump($necessary_slots), true);
        }

        $this->setConfiguration('bindings', $bindings);

    }

    public function postSave() {
        // Do after saving configurations

        $slots = $this->getConfiguration('slots');

        // Generate related slots(Info command)
        foreach ($slots as $slot) {

            $slotCmd = $this->getCmd(null, $slot['name']);

            snips::debug('Type is '.gettype($slotCmd), true );

            if(!is_object($slotCmd)){
                $slotCmd = new snipsCmd();
            }
            $slotCmd->setName($slot['name']);
            $slotCmd->setEqLogic_id($this->getId());
            // ID does not work
            $slotCmd->setLogicalId($slot['name']);
            $slotCmd->setType('info');
            $slotCmd->setSubType($slot['entityId']);
            //$slotCmd->setValue();
            $slotCmd->setConfiguration('missingQuestion', $slot['missingQuestion']);
            $slotCmd->setConfiguration('required', $slot['required']);
            $slotCmd->save();
        }
    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
        
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }
}

class snipsCmd extends cmd {
    
    
}


