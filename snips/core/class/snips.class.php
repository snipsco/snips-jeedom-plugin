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
        $server = socket_connect ($socket , config::byKey('mqttAddr', 'snips', '127.0.0.1'), config::byKey('mqttPort', 'snips', '1883'));
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
            throw new Exception(__('Can not find task corn', __FILE__));
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
        log::add('snips','info','Installation of dependences');
        $resource_path = realpath(dirname(__FILE__) . '/../../resources');
        passthru('sudo /bin/bash ' . $resource_path . '/install.sh ' . $resource_path . ' > ' . log::getPathToLog('MQTT_dep') . ' 2>&1 &');
        return true;
    }
    
    public static function daemon() {
        log::add('snips', 'info', 'Connection Parameters, Host : ' . config::byKey('mqttAddr', 'snips', '127.0.0.1') . ', Port : ' . config::byKey('mqttPort', 'snips', '1883'));

        $client = new Mosquitto\Client("Jeedom_snips");
        $client->onConnect('snips::connect');
        $client->onDisconnect('snips::disconnect');
        $client->onSubscribe('snips::subscribe');
        $client->onMessage('snips::message');
        $client->onLog('snips::logmq');
        $client->setWill('/jeedom', "Client died :-(", 1, 0);
        
        
        try {
            
            //if (config::byKey('mqttUser', 'snips', 'none') != 'none') {
            //    $client->setCredentials(config::byKey('mqttUser', 'snips'), config::byKey('mqttPass', 'snips'));
            //}
            
            $client->connect(config::byKey('mqttAddr', 'snips', '127.0.0.1'), config::byKey('mqttPort', 'snips', '1883'), 60);

            $topics = array();
            $topics = self::getTopics();

            foreach($topics as $topic){
                $client->subscribe($topic, 0); // Subcribe to all intents with QoC = 0

                log::add('snips', 'debug', 'Subscribe to topic ' . $topic);
            }
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
        $topics = self::getTopics()

        if(in_array($message->topic, $topics) === false){
            log::add('snips', 'debug', 'snips mqtt client received something but not an intent');
            return;
        } else{
            log::add('snips', 'debug', 'Intent received: '.$message->topic.' with payload: '.$message->payload.);

            $cmd = $this->getCmd(null, str_replace('hermes/intent/','',$message->topic))
            if (is_object($cmd)) { //elle existe et on lance la commande
                $cmd->execCmd();
            } else {
                log::add('snips', 'debug', 'can not execute this command');
            }
        }



        ////React with coresponding actions. 

    }

    public function getIntents(){
        ////////Change when the Json file is available
        $intents = array("lightsTurnOff","lightsTurnUp","lightsTurnOnSet","lightsTurnDown");
        return $intents;
    }

    public function getTopics(){

        $intents = self::getIntents();

        $topics = array();
        foreach($intents as $intent){
            array_push($topics, 'hermes/intent/'.$intent);
        }

    }

    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {
        /*
        */
    }

    public function postSave() {
        $intents = self::getIntents();

        //log::add('snips', 'debug', 'Intents detected.');

        foreach($intents as $intent){
            $item = $this->getCmd(null, $intent);
            if (!is_object($item)) {
                $item = new snipsCmd();
                $item->setName(__($intent, __FILE__));
            }
            $item->setLogicalId($intent);
            $item->setEqLogic_id($this->getId());
            $item->setType('info');
            $item->setSubType('string');
            $item->save();
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

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class snipsCmd extends cmd {
    //Entrance
    
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = null) {

        $received_intent = $this->getLogicalId();
        $target_command = $this->getConfiguration('command');

        log::add('snips', 'debug', 'Command Handler has been entered with intent: '.$received_intent.' and its related command'.$target_command.' will be execuit');
    }

    /*     * **********************Getteur Setteur*************************** */
}


