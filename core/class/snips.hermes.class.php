<?php
/*
 * brif: Hermes protocol APIs
 *
 * note: There is no official snips hermes-php extension released yet.
 *       The purpose of this library is making a hermes liked API for
 *       snips-jeedom-plugin, whihc is based on the official Mosquitto
 *       extension.
 *
 * reference: Hermes protocol: https://docs.snips.ai/reference/dialogue
 *            Mosquitto PHP: https://github.com/mgdm/Mosquitto-PHP
 *
 * author: Ke Fang
 *
*/

require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/snips.utils.class.php';
require_once dirname(__FILE__) . '/snips.handler.class.php';

class SnipsHermes{

    const TOP_INTENTS = 'hermes/intent/#';
    const TOP_SESSION_STARTED = 'hermes/dialogueManager/sessionStarted';
    const TOP_SESSION_ENDED = 'hermes/dialogueManager/sessionEnded';
    const TOP_HOTWORD_DETECTED = 'hermes/hotword/default/detected';

    const TOP_START_SESSION = 'hermes/dialogueManager/startSession';
    const TOP_CONTINUE_SESSION = 'hermes/dialogueManager/continueSession';
    const TOP_END_SESSION = 'hermes/dialogueManager/endSession';

    private $connected = false;

    private $client;
    private $host;
    private $port;

    private $callback_hotword_detected;
    private $callback_intents;
    private $callback_session_started;
    private $callback_session_ended;

    /* private method */
    private function mqtt_publish($topic, $payload)
    {
        $this->client->publish($topic, $payload);
        SnipsUtils::logger('published message: '. $payload .' to topic: '. $topic);
    }

    /* constructor */
    function __construct($host, $port)
    {
        $client_id = SnipsUtils::generate_client_id();
        $this->host = $host;
        $this->port = $port;
        $this->client = new Mosquitto\Client('snips-jeedom-'. $client_id);
        $this->client->onConnect([$this, 'mqtt_on_connect']);
        $this->client->onDisconnect([$this, 'mqtt_on_disconnect']);
        $this->client->onSubscribe([$this, 'mqtt_on_subscribe']);
        //$this->client->onLog([$this, 'mqtt_on_log']);
        $this->client->onMessage([$this, 'mqtt_on_message']);
        try {
            SnipsUtils::logger('connecting to : '. $this->host .':'. $this->port, 'info');
            $this->client->connect($this->host, $this->port, 60);
        } catch (Exception $e) {
            SnipsUtils::logger('ip address of the master snips device maybe wrong!', 'error');
        }
    }

    /* deconstructor */
    function __destruct()
    {
        //$this->client->disconnect();
        unset($this->client);
    }

    /* status checker */
    function is_connected()
    {
        return $this->connected;
    }

    /* subscribe to needed topics */
    public function subscribe_intents($callback)
    {
        $this->callback_intents = $callback;
        $this->client->subscribe(self::TOP_INTENTS, 0);
    }

    public function subscribe_session_started($callback)
    {
        $this->callback_session_started = $callback;
        $this->client->subscribe(self::TOP_SESSION_STARTED, 0);
    }

    public function subscribe_session_ended($callback)
    {
        $this->callback_session_ended = $callback;
        $this->client->subscribe(self::TOP_SESSION_ENDED, 0);
    }

    public function subscribe_hotword_detected($callback)
    {
        $this->callback_hotword_detected = $callback;
        $this->client->subscribe(self::TOP_HOTWORD_DETECTED, 0);
    }

    /* blocking mqtt client */
    public function start()
    {
        try {
            $this->client->loopForever();
        }
        catch (Exception $e) {
            SnipsUtils::logger('mqtt blocking error', 'error');
        }
    }

    /* hermes protocol APIs */
    public function publish_start_session_action(
        $site_id,
        $session_init_text = null,
        $session_init_can_be_enqueued = null,
        $session_init_intent_filter = null,
        $session_init_send_intent_not_recognized = true,
        $custom_data = null
    ) {
        $payload = array();
        if ($site_id) {
            $payload['siteId'] = $site_id;
        }

        $init = array(
            'type' => 'action'
        );
        if ($session_init_text) {
            $init['text'] = $session_init_text;
        }
        if ($session_init_can_be_enqueued) {
            $init['canBeEnqueued'] = $session_init_can_be_enqueued;
        }
        if ($session_init_intent_filter) {
            $init['intentFilter'] = $session_init_intent_filter;
        }
        if ($session_init_send_intent_not_recognized) {
            $init['sendIntentNotRecognized'] = $session_init_send_intent_not_recognized;
        }
        if ($custom_data) {
            $payload['customData'] = $custom_data;
        }

        $payload['init'] = $init;
        return $this->mqtt_publish(
            self::TOP_START_SESSION,
            json_encode($payload)
        );
    }

    public function publish_start_session_notification(
        $site_id,
        $session_init_text,
        $custom_data = null
    ) {
        $payload = array();
        if ($site_id) {
            $payload['siteId'] = $site_id;
        }

        $init = array(
            'type' => 'notification'
        );
        if ($session_init_text) {
            $init['text'] = $session_init_text;
        }
        if ($custom_data) {
            $payload['customData'] = $custom_data;
        }

        $payload['init'] = $init;
        return $this->mqtt_publish(
            self::TOP_START_SESSION,
            json_encode($payload)
        );
    }

    public function publish_continue_session(
        $session_id,
        $text,
        $intent_filter = null,
        $custom_data = null,
        $send_intent_not_recognized = false
    ) {
        $payload = array();
        if ($session_id) {
            $payload['sessionId'] = $session_id;
        } else {
            return false;
        }
        if ($text) {
            $payload['text'] = $text;
        }
        if ($intent_filter) {
            $payload['intentFilter'] = $intent_filter;
        }
        if ($custom_data) {
            $payload['customData'] = $custom_data;
        }
        if ($send_intent_not_recognized) {
            $payload['sendIntentNotRecognized'] = $send_intent_not_recognized;
        }
        return $this->mqtt_publish(
            self::TOP_CONTINUE_SESSION,
            json_encode($payload)
        );
    }

    public function publish_end_session(
        $session_id,
        $text = null
    ) {
        $payload = array();
        if ($session_id) {
            $payload['sessionId'] = $session_id;
        } else {
            return false;
        }
        if ($text) {
            $payload['text'] = $text;
        }
        return $this->mqtt_publish(
            self::TOP_END_SESSION,
            json_encode($payload)
        );
    }

    /* mqtt callbacks */
    public function mqtt_on_connect($r, $message)
    {
        SnipsUtils::logger('connected to the broker with code: '. $r .' message: '. $message);
        $this->connected = true;
    }

    public function mqtt_on_disconnect($r)
    {
        SnipsUtils::logger('disconnected from the broker with code: '. $r);
        $this->connected = false;
    }

    public function mqtt_on_subscribe($r)
    {
        SnipsUtils::logger('subscribeed with code '. $r);
    }

    public function mqtt_on_log($r, $str)
    {
        if (
            strpos($str, 'PINGREQ') === false &&
            strpos($str, 'PINGRESP') === false
        ) {
            SnipsUtils::logger('log code: '. $r .' : '. $str);
        }
    }

    public function mqtt_on_message($message)
    {
        SnipsUtils::logger('received message. topic:'. $message->topic);
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

        try {
            $callback($this, json_decode($message->payload));
        }
        catch (Exception $e) {
            SnipsUtils::logger('callback execution: '. $e->getMessage(), 'error');
        }
    }
}