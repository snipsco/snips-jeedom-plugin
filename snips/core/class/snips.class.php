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

    public static function deamon_start() {
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
        $port = intval(config::byKey('mqttPort', 'snips', 1883));

        self::debug('Connection Parameters, Host : ' . $addr . ', Port : ' . $port);

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
            $topics = self::getTopics();
            
            foreach($topics as $topic){
                $client->subscribe($topic, 0); // Subcribe to all intents with QoC = 0

                //log::add('snips', 'info', 'topic :'.$topic );
                //self::debug('Subscribe to topic :' . $topic);
            }
            
            //$client->subscribe('hermes/intent/lightsTurnOff', 0);
            //$client->subscribe('hermes/intent/lightsTurnUp', 0);
            //$client->subscribe('hermes/intent/lightsTurnOnSet', 0);
            //$client->subscribe('hermes/intent/lightsTurnDown', 0);

            //$client->loopForever();
           while (true) { $client->loop(); }
        }
        catch (Exception $e){
            log::add('snips', 'error', $e->getMessage());
        }
    }
    
    public static function connect( $r, $message ) {
        log::add('snips', 'info', 'Connected to mosquitto with code ' . $r . ' ' . $message);
        config::save('status', '1',  'snips');
    }

    public static function disconnect( $r ) {
        log::add('snips', 'debug', 'Disconnected to mosquitto with code ' . $r);
        config::save('status', '0',  'snips');
    }

    public static function subscribe( ) {
        log::add('snips', 'debug', 'Subscribe to topics');
    }

    public static function logmq( $code, $str ) {
        if (strpos($str,'PINGREQ') === false && strpos($str,'PINGRESP') === false) {
            log::add('snips', 'debug', $code . ' : ' . $str);
        }
    }

    public static function message( $message ) {
        
        $topics = self::getTopics();
        //self::debug('receied message :'. $message);
        //log::add('snips', 'info', $message->topic);

        if(in_array($message->topic, $topics) == false){
            self::debug('snips mqtt client received something but not an intent');
            return;
        } 
        else{


            $intent = str_replace('hermes/intent/','',$message->topic);

            self::debug('Intent received: ['.$intent.'] with payload: '.$message->payload);

            //Start to find the actions command object

            $cmds = cmd::byLogicalId($intent);
            $cmd = $cmds[0];

            if (is_object($cmd)) { 

                $data = json_decode($message->payload);
                $session_id = $data->{"sessionId"};
                $cmd->setValue($session_id);

                log::add('snips', 'debug', 'LogicalId:'.$cmd->getName().'sessionId:'.$session_id.'(Message Call Back)');

                ////React with coresponding actions. 
                $cmd->execute();

            } else { 
                //otherwise, take a note to analyse..
                log::add('snips', 'debug', 'Device found: '.gettype($cmd));
                log::add('snips', 'debug', 'This command may not exist!');
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
        $port = intval(config::byKey('mqttPort', 'snips', '1883'));

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
        log::add('snips', 'info', $info);
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
        }

        return $topics;

    }

    public function freshSkills(){
        self::debug('fresh function has been entred! : )');
    }
    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {

    }

    public function postSave() {
        //$logicalId = this->getName();

        //self::setLogicalId($logicalId);

        $intents = json_decode(self::getIntents(), true);
        //log::add('snips', 'debug', 'Intents detected.');

        foreach($intents as $intent => $slots){

            //$rand = rand(1, 999999);
            $rand = 0;

            $snipsObj = $this->getCmd(null, 'Snips_'.$intent.'_'.$rand); //getCmd(  $_type = null,   $_logicalId = null,   $_visible = null,   $_multiple = false)

            if (!is_object($snipsObj)) {
                $snipsObj = new snipsCmd();
                $snipsObj->setLogicalId('Snips_'.$intent.'_'.$rand);
                $snipsObj->setName('Snips_'.$intent.'_'.$rand);
            }

            $snipsObj->setEqLogic_id($this->getId());
            $snipsObj->setType('info');//Snips_Intent
            $snipsObj->setSubType('string');
            $snipsObj->setConfiguration('intent', $intent);
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

        log::add('snips', 'debug', 'Command Handler has been entered with intent: ['.$received_intent.'] and its related command id: ['.$target_command.'] will be execute!');

        $cmd = cmd::byId(str_replace('#','',$target_command));

        $cmd->execute();

        $say = 'Your command '. $cmd->getId() .' has been executed successfully.';

        log::add('snips', 'debug', 'will publish, raw message: '. $message .' and text: '.$say);

        snips::sayFeedback($sessionId, $say);
    }
}


