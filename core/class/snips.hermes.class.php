<?php
/*
 * Brif: hermes protocol APIs
 *
 * Note: there is no official snips hermes-php extension released yet.
 *       The purpose of this library is making a hermes liked API for
 *       snips-jeedom-plugin, whihc is based on the official Mosquitto
 *       extension from [https://github.com/mgdm/Mosquitto-PHP].
 *
 *       Since this library is made for snips-jeedom-plugin, the error/info output
 *       will be shown by using jeedom loger under the section [snips-hermes].
 *       If you need this library for some other purpose, simply changet it.
 *
 * Author: Ke Fang
 * Version:
 * Last Update:
*/

/* for using jeedom logger */
//require_once dirname(__FILE__) . '/../../../core/class/cron.class.php';
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class SnipsHermes{

    static private const TOP_INTENTS = 'hermes/intent/#';
    static private const TOP_SESSION_STARTED = 'hermes/dialogueManager/sessionStarted';
    static private const TOP_SESSION_ENDED = 'hermes/dialogueManager/sessionEnded';
    static private const TOP_HOTWORD_DETECTED = 'hermes/hotword/default/detected';

    static private const TOP_START_SESSION = 'hermes/dialogueManager/startSession';
    static private const TOP_CONTINUE_SESSION = 'hermes/dialogueManager/continueSession';
    static private const TOP_END_SESSION = 'hermes/dialogueManager/endSession';

    private $connected = 0;

    private $client;
    private $host;
    private $port;

    private $callback_hotword_detected;
    private $callback_intents;
    private $callback_session_started;
    private $callback_session_ended;

    /* static methods */
    static public function logger($_str){
        $msg = '['.__CLASS__.'] '.$_str;
        log::add('snips', 'debug', $msg);
        //echo $str."\n";
        //$this->logger($str);
    }

    /* private method */
    private function mqtt_publish($_topic, $_payload)
    {
        $this->client->publish($_topic, $_payload);
        self::logger('<'.__FUNCTION__.'> Published message: ' . $_payload . ' to topic: ' . $_topic);
    }

    static private function generate_client_id(){
        $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
        'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
        't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
        'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
        'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

        $keys =array_rand($chars, 10);
        $client_id ='';
        for($i = 0; $i < 10; $i++)
        {
            $client_id .= $chars[$keys[$i]];
        }
        return $client_id;
    }

    /* constructor */
    function __construct($_host, $_port){
        $this->host = $_host;
        $this->port = $_port;
        $this->client = new Mosquitto\Client('snips-jeedom-'.self::generate_client_id());
        $this->client->onConnect([$this, 'mqtt_on_connect']);
        $this->client->onDisconnect([$this, 'mqtt_on_disconnect']);
        $this->client->onSubscribe([$this, 'mqtt_on_subscribe']);
        $this->client->onLog([$this, 'mqtt_on_log']);
        $this->client->onMessage([$this, 'mqtt_on_message']);
        $this->client->connect($this->host, $this->port, 60);
    }

    /* deconstructor */
    function __destruct(){
        $this->client->disconnect();
        unset($this->client);
    }

    /* subscribe to needed topics */
    public function subscribe_intents($callback)
    {
        $this->callback_intents = $callback;
        //$this->client->subscribe('hermes/intent/#', 0);
        $this->client->subscribe(self::$TOP_INTENTS, 0);
    }

    public function subscribe_session_started($callback)
    {
        $this->callback_session_started = $callback;
        //$this->client->subscribe('hermes/dialogueManager/sessionStarted', 0);
        $this->client->subscribe(self::$TOP_SESSION_STARTED, 0);
    }

    public function subscribe_session_ended($callback)
    {
        $this->callback_session_ended = $callback;
        //$this->client->subscribe('hermes/dialogueManager/sessionEnded', 0);
        $this->client->subscribe(self::$TOP_SESSION_ENDED, 0);
    }

    public function subscribe_hotword_detected($callback)
    {
        $this->callback_hotword_detected = $callback;
        //$this->client->subscribe('hermes/hotword/default/detected', 0);
        $this->client->subscribe(self::$TOP_HOTWORD_DETECTED, 0);
    }

    /* blocking mqtt client */
    public function start()
    {
        try {
            $this->client->loopForever();
        }
        catch(Exception $e){
            self::logger('<'.__FUNCTION__.'> Connection exception: ' . $e->getMessage());
        }
    }

    /* hermes protocol APIs */
    public function publish_start_session_action(
        $_site_id,
        $_session_init_text = null,
        $_session_init_can_be_enqueued = null,
        $_session_init_intent_filter = null,
        $_session_init_send_intent_not_recognized = null,
        $_custom_data = null
    ) {
        //$topic = 'hermes/dialogueManager/startSession';
        $payload = array();
        $init = array(
            'type' => 'action'
        );
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
            $payload['customData'] = $_custom_data;
        $payload['init'] = $init;
        return $this->mqtt_publish(self::$TOP_START_SESSION, json_encode($payload));
    }

    public function publish_start_session_notification(
        $_site_id,
        $_session_init_text,
        $_custom_data = null
    ) {
        //$topic = 'hermes/dialogueManager/startSession';
        $payload = array();
        $init = array(
            'type' => 'notification'
        );
        if ($_site_id)
            $payload['siteId'] = $_site_id;
        if ($_session_init_text)
            $init['text'] = $_session_init_text;
        if ($_custom_data)
            $payload['customData'] = $_custom_data;
        $payload['init'] = $init;
        return $this->mqtt_publish(self::$TOP_START_SESSION, json_encode($payload));
    }

    public function publish_continue_session(
        $_session_id,
        $_text,
        $_intent_filter = null,
        $_custom_data = null,
        $_send_intent_not_recognized = false
    ) {
        //$topic = 'hermes/dialogueManager/continueSession';
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
        return $this->mqtt_publish(self::$TOP_CONTINUE_SESSION, json_encode($payload));
    }

    public function publish_end_session(
        $_session_id,
        $_text = null
    ) {
        // $topic = 'hermes/dialogueManager/endSession';
        $payload = array();
        if ($_session_id)
            $payload['sessionId'] = $_session_id;
        else
            return 0;
        if ($_text)
            $payload['text'] = $_text;
        return $this->mqtt_publish(self::$TOP_END_SESSION, json_encode($payload));
    }

    // mqtt callbacks
    public function mqtt_on_connect($_r, $_message)
    {
        self::logger('<'.__FUNCTION__.'> Connected to the broker with code: ' . $_r . ' message: ' . $_message);
        $this->connected = 1;
    }

    public function mqtt_on_disconnect($_r)
    {
        self::logger('<'.__FUNCTION__.'> Disconnected from the broker with code: ' . $_r);
        $this->connected = 0;
    }

    public function mqtt_on_subscribe($_r)
    {
        self::logger('<'.__FUNCTION__.'> Subscribeed with code '. $_r);
    }

    public function mqtt_on_log($_r, $_str)
    {
        if (strpos($_str, 'PINGREQ') === false && strpos($_str, 'PINGRESP') === false) {
            self::logger('<'.__FUNCTION__.'> Log code: ' . $_r . ' : ' . $_str);
        }
    }

    public function mqtt_on_message($message)
    {
        self::logger('<'.__FUNCTION__.'> Received message. Topic:' . $message->topic);
        $payload_array = json_decode($message->payload);

        switch ($message->topic) {
            case self::TOP_SESSION_STARTED:
                $callback = $this->callback_session_started;
                break;
            case self::TOP_SESSION_ENDED:
                $callback = $this->callback_session_ended;
                break;
            case self::TOP_HOTWORD_DETECTED:
                $callback = $this->callback_hotword_detected;
                break;
            default:
                $callback = $this->callback_intents;
                break;
        }

        try{
            $callback($this, json_decode($message->payload));
        }
        catch(Exception $e){
            self::logger('<'.__FUNCTION__.'>  Callback execution: ' . $e->getMessage());
        }
    }
}