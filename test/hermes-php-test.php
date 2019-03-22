<?php

ini_set("display_errors","On");
error_reporting(E_ALL);

require(dirname(__FILE__) . '/../3rdparty/snips.hermes.class.php');
require(dirname(__FILE__) . '/../3rdparty/snips.handler.class.php');

$H = new SnipsHermes('snips-seeed-master.local', 1883);


$H->subscribe_intents('SnipsHandler::intent_detected');
$H->subscribe_session_ended('SnipsHandler::session_ended');
$H->subscribe_session_started('SnipsHandler::session_started');
$H->subscribe_hotword_detected('SnipsHandler::hotword_detected');

//$H->set_on_message_callback('callback');
//$H->set_logger('echo');

//$H->publish_start_session_notification('default', "Commencer debug");

echo "start blocking\n";
$H->start();
echo "end blocking";

?>