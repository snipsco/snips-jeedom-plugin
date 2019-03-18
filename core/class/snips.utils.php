<?php

namespace SnipsUtils;

function get_intents_from_assistant_json($path)
{
    //$intents_file = dirname(__FILE__) . '/../../config_running/assistant.json';
    $assistant_json_string = file_get_contents($path);
    $intents = json_decode($assistant_json_string, true)['intents'];

    $intents_slots = array();

    foreach($intents as $intent) {
        if (strpos( strtolower($intent['name']), 'jeedom')) {
            $slots = array();
            foreach($intent['slots'] as $slot) {
                $slots[] = $slot['name'];
            }
            $intents_slots[$intent['id']] = $slots;
            unset($slots);
        }
    }
    return json_encode($intents_slots);
}

function generate_client_id()
{
    $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
    'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
    't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
    'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
    'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z',
    '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    
    $keys =array_rand($chars, 10);
    $client_id ='';
    for ($i = 0; $i < 10; $i++) {
        $client_id .= $chars[$keys[$i]];
    }
    return $client_id;
}