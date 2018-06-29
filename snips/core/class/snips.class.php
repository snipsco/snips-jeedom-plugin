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
    public static function resetSlotsCmd($slots_values, $intent){
        
        $eq = eqLogic::byLogicalId($intent, 'snips');

        foreach ($slots_values as $slot => $value) {

            $eq->checkAndUpdateCmd($slot, '');

        }
    }

    public static function resetMqtt(){

        $cron = cron::byClassAndFunction('snips', 'deamon');
        if (is_object($cron)) {

            snips::debug('Removed snips cron: deamon');

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

    public static function sayFeedback($text, $session_id = null){
        if($session_id == null){
            ChromePhp::log('Only say: '.$text);
            $topic = 'hermes/tts/say';
            $payload = array('text' => $text, "siteId" => "default");

            self::publish($topic, json_encode($payload));
        }else{
            ChromePhp::log('Endsession say: '.$text);
            $topic = 'hermes/dialogueManager/endSession';
            $payload = array('text' => $text, "sessionId" => $session_id);

            self::publish($topic, json_encode($payload));
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
    public static function debug($info){
        //ChromePhp::log($info);
        //log::add('snips', 'debug', $info);
        fwrite(STDOUT, $info.'\n');
    }

    public static function getIntents(){

        $intents_file = "/usr/share/snips/assistant/assistant.json";
        $json_string = file_get_contents($intents_file);
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
            $intents_slots[$intent["name"]] = $slots;

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
        //self::debug("reload assistant");
        // Add all the intents to configuration
        $intents = json_decode(self::getIntents(), true);

        //self::debug('Current intents is :'.gettype($intents));

        foreach($intents as $intent => $slots){

            //self::debug('Creating intent equipment: '.$intent);

            if(is_object(snips::byLogicalId($intent, 'snips'))){
                return;
            }else{
                // Create intent as devices 
                $elogic = new snips();
                $elogic->setEqType_name('snips');
                $elogic->setLogicalId($intent);
                $elogic->setName($intent);
                $elogic->setIsEnable(1);
                $elogic->setConfiguration('slots', $slots);
                $elogic->setObject_id(object::byName('snips-intents')->getId());
                $elogic->save();
            }
        }

    }

    public static function deleteAssistant(){
        //self::debug("remove assistant");

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

        //snips::debug('slots and value:'.var_dump($slots_values));

        // Get all the binding configuration by [intentName]
        $eqLogic = eqLogic::byLogicalId($intent_name, 'snips');

        $bindings = $eqLogic->getConfiguration('bindings');

        // Find bindings which does not require condition
        $bindings_without_condition = array();
        foreach ($bindings as $binding) {
            
            if (empty($binding['condition'])) {
                $bindings_without_condition[] = $binding;
                snips::debug('NC binding s name: '.$binding['name']);
            }
        } 

        // Find bindings which has condition and match the coming data
        $bindings_with_condition = array();
        foreach ($bindings as $binding) {

            $all_true_indicator = 1; 
            foreach ($binding['condition'] as $condition) {

                // Condition setup
                $pre_value = $slots_values[$condition['pre']]; // find received value by its name

                if (is_string($condition['aft'])) {
                    $aft_value = strtolower(str_replace(' ', '', $condition['aft']));
                }else{
                    $aft_value = $condition['aft'];
                }
                

                if($pre_value == $aft_value){
                    $all_true_indicator *= 1;
                }else{
                    $all_true_indicator *= 0;
                }

            }
            if($all_true_indicator){
                $bindings_with_condition[] = $binding;
                snips::debug('HC binding s name: '.$binding['name']);
            }
        }

        

        $bindings_to_perform = array_merge($bindings_without_condition, $bindings_with_condition);
        // Execute all the possible bindings

        snips::setSlotsCmd($slots_values, $intent_name);

        foreach ($bindings_to_perform as $binding) {

            

            foreach ($binding['action'] as $action) {

                $options = array();
                if (isset($action['options'])) {
                    $options = $action['options'];
                }

                snips::debug('Executing cmd: '.$action['cmd']);

                scenarioExpression::createAndExec('action', $action['cmd'], $options);

            }
        }
        snips::resetSlotsCmd($slots_values, $intent_name);

        /// ----- works
    }

    public static function setSlotsCmd($slots_values, $intent){
        
        $eq = eqLogic::byLogicalId($intent, 'snips');

        foreach ($slots_values as $slot => $value) {

            $eq->checkAndUpdateCmd($slot, $value);

            snips::debug('Setting slots: '.$slot.' with value: '.$value);
            //snips::debug('Result :'.$eq->getCmd($slot)->getValue());
        }
    }

    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {

    }

    public function postInsert() {
        // //$logicalId = this->getName();

        // //self::setLogicalId($logicalId);
        // $intents = json_decode(self::getIntents(), true);
        // //log::add('snips', 'debug', 'Intents detected.');

        // foreach($intents as $intent => $slots){

        //     self::debug('Creating mapping for '.$intent.'with slots '.$slots);

        //     $rand = rand(1, 999999);
        //     //$rand = 0;

        //     $snipsObj = $this->getCmd(null, 'Snips_'.$intent.'_'.$rand); //getCmd(  $_type = null,   $_logicalId = null,   $_visible = null,   $_multiple = false)

        //     if (!is_object($snipsObj)) {
        //         $snipsObj = new snipsCmd();
        //         $snipsObj->setLogicalId('Snips_'.$intent.'_'.$rand);
        //         $snipsObj->setName('Snips_'.$intent.'_'.$rand);
        //     }

        //     $snipsObj->setEqLogic_id($this->getId());
        //     $snipsObj->setType('snips_intent');//Snips_Intent
        //     $snipsObj->setSubType($intent);
        //     $snipsObj->setConfiguration('intent', $intent); // intent name
        //     $snipsObj->setConfiguration('actionCmd', ''); // target action command
        //     $sniosObj->setConfiguration('actionVal',''); // target action command value (if slider/ color/ etc..)
        //     $sniosObj->setConfiguration('actionLocSlot',''); // slot to represent locaton
        //     $sniosObj->setConfiguration('actionLocVal',''); // pre-defined location value
        //     $snipsObj->setConfiguration('actionTts', '');  // action feedback sound
        //     $snipsObj->setConfiguration('actionTtsEnable', '1');  // action feedback sound

        //     /*foreach($slots as $slot){
        //         $content = array();
        //         $content['type'] = 'location';
        //         $content['value'] = '';
        //         $snipsObj->setConfiguration( $slot, $content);
        //         unset($content);
        //     }
        //     $snipsObj->save();
        // }    */
    }

    public function preSave() {
        
    }

    public function postSave() {
        $intent = $this->getLogicalId();

        self::debug('postSave, current equipment name:'.$intent);

        $slotSet = $this->getConfiguration('slots');

        foreach ($slotSet as $slot) {
            $slotCmd = $this->getCmd(null, $slot);
            if(!is_object($slotCmd)){
                self::debug('postSave, NOT EXIST, Slot Name: '.$slot);
                $slotCmd = new snipsCmd();
            }
            $slotCmd->setName($slot);
            $slotCmd->setEqLogic_id($this->getId());
            $slotCmd->setLogicalId($slot);
            $slotCmd->setType('info');
            $slotCmd->setSubType('string');
            $slotCmd->setValue('NULL');
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
    
    // Rewrite
    public function execute($_options = null) {

        $received_intent = $this->getLogicalId();
        $target_command = $this->getConfiguration('action');
        $sessionId = $this->getValue();

        self::debug('Command Handler has been entered with intent: ['.$received_intent.'] and its related command id: ['.$target_command.'] will be execute!');

        $cmd = cmd::byId(str_replace('#','',$target_command));

        $cmd->execute();

        //$say = 'Your command '. $cmd->getId() .' has been executed successfully.';
        $say = $this->getConfiguration('feeback');

        self::debug('will publish, raw message: '. $message .' and text: '.$say);

        snips::sayFeedback($say, $sessionId);
    }
}


