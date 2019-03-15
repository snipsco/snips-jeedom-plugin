<?php

namespace Snips\utils;

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