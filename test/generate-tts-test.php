<?php

ini_set("display_errors","On");
error_reporting(E_ALL);

require(dirname(__FILE__) . '/../3rdparty/snips.tts.class.php');

$msg = snips_tts::dump('desole, [oui | non | bien sure | pas de problem], c\'est {#} degree', array(array('cmd' => '#85#')))->get_message();

echo $msg."\n";


