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

class snips extends eqLogic {

    /*     * ***********************Methode static*************************** */
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
        $cron = cron::byClassAndFunction('snips', 'daemon');
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

        $cron = cron::byClassAndFunction('snips', 'daemon');

        if (!is_object($cron)) {
            throw new Exception(__('Can not find task corn ', __FILE__));
        }
        $cron->run();
    }

    public static function deamon_stop() {
        $cron = cron::byClassAndFunction('snips', 'daemon');

        if (!is_object($cron)) {
            throw new Exception(__('Can not find taks corn', __FILE__));
        }
        $cron->halt();
    }

    public static function dependancy_info() {
        $return = array();
        $return['log'] = 'MQTT_dep';
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
    
    public static function daemon() {
        
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

        //self::debug('receied message :'. $message);
        //log::add('snips', 'info', $message->topic);
        snips::debug('received something');
        if(in_array($message->topic, $topics) == false){
            //self::debug('snips mqtt client received something but not an intent');
            return;
        } 
        else{
            
            $payload = json_decode($message->payload);

            $intent_name = str_replace('hermes/intent/','',$message->topic);
            $site_id = $payload->{'siteId'}; //default
            $session_id = $payload->{'sessionId'};

            snips::debug('Intent received: ['.$intent_name.'] with payload: '.$message->payload);

            $slots_value = array();
            //get received slots
            foreach($payload->{'slots'} as $slot){
                $slots_value[$slot->{'slotName'}] = $slot->{'value'}->{'value'};
                snips::debug('slotName: '.$slot->{'value'}->{'value'});
            }

            $cmds_with_this_intent = cmd::byTypeSubType('snips_intent', $intent_name);

            $selected_cmds = array();
 
            if(!empty($cmds_with_this_intent)){ // if this intent from is an offical 'Smart Light' skill. Change later.
                snips::debug('cmds_with_this_intent: not empty');
                
                // Select all the matched commands
                if(isset($slots_value['house_room'])){
                    snips::debug('house_room: not empty');
                    foreach($cmds_with_this_intent as $cmd){
                        $temp_slots = array();
                        $temp_slots = $cmd->getConfiguration('house_room');

                        snips::debug('Target cmd type:'.gettype($cmd));
                        snips::debug('Target cmd name: '.$cmd->getName());
                        snips::debug('Target cmd room: '.$temp_slots['value']);
                        snips::debug('Received room: '.$slots_value['house_room']);

                        if($temp_slots['value'] == $slots_value['house_room']){
                            $selected_cmds[] = $cmd; 
                        }

                        unset($temp_slots);
                    }
                }else{
                    snips::debug('house_room: empty');
                    foreach($cmds_with_this_intent as $cmd){
                        $temp_slots = array();
                        $temp_slots = $cmd->getConfiguration('house_room');
                        if($temp_slots['value'] == 'default'){
                            $selected_cmds[] = $cmd; 
                        }

                        unset($temp_slots);
                    }
                }

                snips::debug('Found commands!');

                //If there are commands have been selected
                if(!empty($selected_cmds)){
                    // Activate all the matched commands
                    snips::debug('selected_cmds: not empty');
                    snips::debug('there are '.count($selected_cmds).' cmds found');
                    snips::debug('cmd is: '.gettype($cmd));

                    foreach($selected_cmds as $cmd){

                        snips::debug('cmd is: '.gettype($cmd));
                        snips::debug('target_command: '.$target_command);

                        $action_cmd = cmd::byId(str_replace('#','',$cmd->getConfiguration('action')));

                        $action_cmd->execute();

                        snips::sayFeedback($session_id, $cmd->getConfiguration('feedback'));
                    }

                    // Set value to its info command
                    if(isset($slots_value['intensity_number'])){
                        snips::debug('intensity_number: not empty');
                        foreach ($selected_cmds as $cmd) {
                            $temp_slots = array();
                            $temp_slots = $cmd->getConfiguration('intensity_number');

                            if(isset($temp_slots['value'])){

                                $info_cmd = cmd::byId(str_replace('#','',$temp_slots['value']));
                                //$info_cmd->setValue($slots_value['intensity_number']);

                                $eq_id = $info_cmd->getEqLogic_id();

                                snips::debug('eq is: '.$eq_id);

                                $eq = eqLogic::byLogicalId($eq_id, 'philipsHue');

                                snips::debug('eq is: '.$eq);

                                $eq->checkAndUpdateCmd($info_cmd->getLogicalId, $slots_value['intensity_number']);

                                snips::debug('eq is: '.$eq->getName());
                                snips::debug('cmd is: '.$info_cmd->getName());
                                snips::debug('after value: '.$info_cmd->getValue());
                            }
                            unset($temp_slots);
                        }
                    }

                    if(isset($slots_value['intensity_percent'])){
                        snips::debug('intensity_percent: not empty');
                        foreach ($selected_cmds as $cmd) {
                            $temp_slots = array();
                            $temp_slots = $cmd->getConfiguration('intensity_percent');


                            if(isset($temp_slots['value'])){
                                $info_cmd = cmd::byId(str_replace('#','',$temp_slots['value']));

                                $info_cmd->setValue($slots_value['intensity_percent']);
                            }
                            unset($temp_slots);
                        }
                    }
                }
            }
        }
    }

    public static function sayFeedback($session_id, $text){
        $topic = 'hermes/dialogueManager/endSession';
        $payload = array('text' => $text, "sessionId" => $session_id);

        self::publish($topic, json_encode($payload));
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
    public function debug($info){
        //log::add('snips', 'info', $info);
        fwrite(STDOUT, $info.'\n');
    }

    public function getIntents(){

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

    public function getTopics(){
         
        $intents = json_decode(self::getIntents(), true);

        $topics = array();
        foreach($intents as $intent => $slot){
            array_push($topics, 'hermes/intent/'.$intent);
            self::debug('hermes/intent/'.$intent);
        }

        //print_r($topics);
        return $topics;

    }

    public function freshSkills(){
        self::debug('fresh function has been entred! : )');
    }
    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {

    }

    public function postInsert() {
        //$logicalId = this->getName();

        //self::setLogicalId($logicalId);

        $intents = json_decode(self::getIntents(), true);
        //log::add('snips', 'debug', 'Intents detected.');

        foreach($intents as $intent => $slots){

            self::debug('Creating mapping for '.$intent.'with slots '.$slots);

            $rand = rand(1, 999999);
            //$rand = 0;

            $snipsObj = $this->getCmd(null, 'Snips_'.$intent.'_'.$rand); //getCmd(  $_type = null,   $_logicalId = null,   $_visible = null,   $_multiple = false)

            if (!is_object($snipsObj)) {
                $snipsObj = new snipsCmd();
                $snipsObj->setLogicalId('Snips_'.$intent.'_'.$rand);
                $snipsObj->setName('Snips_'.$intent.'_'.$rand);
            }

            $snipsObj->setEqLogic_id($this->getId());
            $snipsObj->setType('snips_intent');//Snips_Intent
            $snipsObj->setSubType($intent);
            $snipsObj->setConfiguration('intent', $intent);
            $snipsObj->setConfiguration('action', '');
            $snipsObj->setConfiguration('feedback', '');

            foreach($slots as $slot){

                $content = array();

                $content['type'] = 'location';
                $content['value'] = '';
                $snipsObj->setConfiguration( $slot, $content);

                unset($content);
            }
            $snipsObj->save();
        }    
    }

    public function preSave() {

    }

    public function postSave() {
        
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

        snips::sayFeedback($sessionId, $say);
    }
}


