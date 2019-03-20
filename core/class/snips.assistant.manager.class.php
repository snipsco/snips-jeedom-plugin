<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/snips.class.php';
require_once dirname(__FILE__) . '/../../3rdparty/Toml.php';

class SnipsAssistantManager
{
    /* create snips-intent object */
    static function create_snips_object($assistant)
    {
        // since 3.3.3, use jeeObject instead of object as class name
        if (version_compare(jeedom::version(), '3.3.3', '>=')) {
            $obj_field = 'jeeObject';
            SnipsUtils::logger('Jeedom >= 3.3.3');
        }else{
            $obj_field = 'object';
            SnipsUtils::logger('Jeedom <= 3.3.3');
        }

        $obj = object::byName('Snips-Intents');
        if (!isset($obj) || !is_object($obj)) {
            $obj = new $obj_field();
            $obj->setName('Snips-Intents');
            SnipsUtils::logger('Created object: Snips-Intents');
        }
        $obj->setIsVisible(1);
        $obj->setConfiguration('id', $assistant["id"]);
        $obj->setConfiguration('name',$assistant["name"]);
        $obj->setConfiguration('hotword', $assistant['hotword']);
        $obj->setConfiguration('language', $assistant['language']);
        $obj->setConfiguration('createdAt', $assistant['createdAt']);
        $obj->save();
    }

    /* create intent eq objects */
    static function create_intents_eq_cmd($raw_intents)
    {
        $object_id = object::byName('Snips-Intents')->getId();

        foreach ($raw_intents as $intent) {
            if (strpos(strtolower($intent['name']), 'jeedom')) {
                $elogic = snips::byLogicalId($intent['id'], 'snips');
                if (!is_object($elogic)) {
                    $elogic = new snips();
                    $elogic->setLogicalId($intent['id']);
                    $elogic->setName($intent['name']);
                    SnipsUtils::logger('Created intent entity: '.$intent['name']);
                }
                $elogic->setEqType_name('snips');
                $elogic->setIsEnable(1);
                $elogic->setConfiguration('snipsType', 'Intent');
                $elogic->setConfiguration('slots', $intent['slots']);
                $elogic->setConfiguration('isSnipsConfig', 1);
                $elogic->setConfiguration('isInteraction', 0);
                $elogic->setConfiguration('language', $intent['language']);
                $elogic->setObject_id($object_id);
                $elogic->save();
            }
            self::create_slots_cmd($elogic, $intent['slots']);
        }
    }

    /* create slots cmd for all the intent eq objects */
    static function create_slots_cmd($eq, $raw_slots)
    {
        foreach ($raw_slots as $slot) {
            $slot_cmd = $eq->getCmd(null, $slot['name']);
            if (!is_object($slot_cmd)) {
                SnipsUtils::logger('Created slot cmd: '.$slot['name']);
                $slot_cmd = new snipsCmd();
            }
            $slot_cmd->setName($slot['name']);
            $slot_cmd->setEqLogic_id($eq->getId());
            $slot_cmd->setLogicalId($slot['name']);
            $slot_cmd->setType('info');
            $slot_cmd->setSubType('string');
            $slot_cmd->setConfiguration('id', $slot['id']);
            $slot_cmd->setConfiguration('entityId', $slot['entityId']);
            $slot_cmd->setConfiguration('missingQuestion', $slot['missingQuestion']);
            $slot_cmd->setConfiguration('required', $slot['required']);
            $slot_cmd->save();
        }
    }

    /* load an assistant from assistant.json  */
    static function load_assistant()
    {
        SnipsUtils::logger('Assistant is being reloaded!');
        $assistant_file = dirname(__FILE__) . '/../../config_running/assistant.json';
        $json_string = file_get_contents($assistant_file);
        $assistant = json_decode($json_string, true);

        self::create_snips_object($assistant);

        self::create_intents_eq_cmd($assistant['intents']);

        self::load_snips_devices();

        self::recover_cenario_expressions();
        SnipsUtils::logger('Assistant loaded, restarting deamon');
        snips::deamon_start();
    }

    /* load snips devices from snips.toml */
    static function load_snips_devices()
    {
        // fetch the device that runs hotword detector
        $devices = Toml::parseFile(
            dirname(__FILE__) .'/../../config_running/snips.toml'
        )->{'snips-hotword'}->{'audio'};

        // fetch the site id of the master device
        $master = Toml::parseFile(
            dirname(__FILE__) . '/../../config_running/snips.toml'
        )->{'snips-audio-server'}->{'bind'};

        if (isset($master)) {
            $master = substr($master,0,strpos($master, '@'));
        } else {
            $master = 'default';
        }

        // add master device site id in to plugin config
        $res = config::save('masterSite', $master, 'snips');

        $lang = snips::get_assistant_language();
        if (count($devices) == 0) {
            self::create_snips_device('default', $lang);
        }else{
            foreach ($devices as $key => $device) {
                $site_id = str_replace('@mqtt', '', $device);
                self::create_snips_device($site_id, $lang);
            }
        }
    }

    /* create snips tts eq object */
    static function create_snips_device($site_id, $lang){
        $elogic = snips::byLogicalId('Snips-TTS-'.$site_id, 'snips');
        if (!is_object($elogic)) {
            $elogic = new snips();
            $elogic->setName('Snips-TTS-'.$site_id);
            $elogic->setLogicalId('Snips-TTS-'.$site_id);
            SnipsUtils::logger('Created TTS device: Snips-TTS-'. $site_id);
        }
        $elogic->setEqType_name('snips');
        $elogic->setIsEnable(1);
        $elogic->setConfiguration('snipsType', 'TTS');
        $elogic->setConfiguration('language', $lang);
        $elogic->setConfiguration('siteName', $site_id);
        $elogic->setObject_id(object::byName('Snips-Intents')->getId());
        $elogic->save();

        self::create_tts_cmd_say($elogic);
        self::create_tts_cmd_ask($elogic);
    }

    /* create say command */
    static function create_tts_cmd_say($eq)
    {
        $tts_cmd = $eq->getCmd(null, 'say');
        if (!is_object($tts_cmd)) {
            SnipsUtils::logger();
            $tts_cmd = new snipsCmd();
            $tts_cmd->setName('say');
            $tts_cmd->setLogicalId('say');
        }
        $tts_cmd->setEqLogic_id($eq->getId());
        $tts_cmd->setType('action');
        $tts_cmd->setSubType('message');
        $tts_cmd->setDisplay('title_disable', 1);
        $tts_cmd->setDisplay('message_placeholder', 'Message');
        $tts_cmd->setConfiguration('siteId', $eq->getConfiguration('siteName'));
        $tts_cmd->save();
    }

    /* create ask command */
    static function create_tts_cmd_ask($eq)
    {
        $ask_cmd = $eq->getCmd(null, 'ask');
        if (!is_object($ask_cmd)) {
            SnipsUtils::logger();
            $ask_cmd = new snipsCmd();
            $ask_cmd->setName('ask');
            $ask_cmd->setLogicalId('ask');
        }
        $ask_cmd->setEqLogic_id($eq->getId());
        $ask_cmd->setType('action');
        $ask_cmd->setSubType('message');
        $ask_cmd->setDisplay('title_placeholder', 'expected intent');
        $ask_cmd->setDisplay('message_placeholder', 'Question');
        $ask_cmd->setConfiguration('siteId', $eq->getConfiguration('siteName'));
        $ask_cmd->save();
    }

    /* delete all the intents equipments, cmds, save a refer table */
    static function delete_assistant()
    {
        // intent [name] to jeedom [id]
        $intent_table = array();

        // slot [name] to [id]
        $slots_table = array();

        $eqLogics = eqLogic::byType('snips');
        foreach ($eqLogics as $eq) {
            $intent_table[$eq->getHumanName()] = $eq->getId();
            $cmds = cmd::byEqLogicId($eq->getId());
            foreach ($cmds as $cmd) {
                $slots_table[$cmd->getHumanName()] = $cmd->getId();
                $cmd->remove();
            }
            $eq->remove();
        }

        $reload_reference = array(
            "Intents" => $intent_table,
            "Slots" => $slots_table
        );

        // save the reference table for the next round reload
        $file = fopen(
            dirname(__FILE__).'/../../config_running/reload_reference.json',
            'w'
        );
        $res = fwrite($file, json_encode($reload_reference));
    }

    /* get the slots from current assistant */
    static function get_slots_table()
    {
        $slots_table = array();

        $eqLogics = eqLogic::byType('snips');
        foreach ($eqLogics as $eq) {
            $intent_table[$eq->getHumanName()] = $eq->getId();
            $cmds = cmd::byEqLogicId($eq->getId());
            foreach ($cmds as $cmd) {
                $slots_table[$cmd->getHumanName()] = $cmd->getId();
            }
        }
        return $slots_table;
    }

    /* change all the invalide options back to valide */
    static function recover_cenario_expressions()
    {
        $json_string = file_get_contents(
            dirname(__FILE__) .'/../../config_running/reload_reference.json'
        );
        $reference = json_decode($json_string, true);
        //$intent_table = $reference['Intents'];
        $slots_table = $reference['Slots'];
        $slots_table_curr = self::get_slots_table();

        SnipsUtils::logger('slots_table_curr'.json_encode($slots_table_curr));

        $expressions = scenarioExpression::all();

        // check all the expressions
        foreach ($expressions as $expression) {
            $old_expression_content = $expression->getExpression();
            $old_expression_option = $expression->getOptions();
            // replace all the old cmd ID to new cmd string in the expression
            foreach ($slots_table as $slots_string => $id) {
                // If the old intent is in the new assistant
                if (array_key_exists($slots_string, $slots_table_curr)) {
                    if (
                        strpos($old_expression_content, '#'. $id .'#') ||
                        strpos($old_expression_content, '#'. $id .'#') === 0
                    ) {
                        $new_expression = str_replace(
                            '#'. $id. '#',
                            '#'. $slots_string. '#',
                            $old_expression_content
                        );
                        SnipsUtils::logger('Old command entity: '.$slots_string.' with id: '.$id);
                        $expression->setExpression($new_expression);
                    }
                }
            }
            // replace all the old cmd ID to new cmd string in the options
            foreach ($old_expression_option as $option_name => $value) {
                preg_match_all("/#([0-9]*)#/", $value, $match);
                if (count($match[0]) == 1) {
                    if (in_array($match[1][0], $slots_table)) {
                        $slot_cmd_string = array_search($match[1][0], $slots_table);
                        $expression->setOptions(
                            $option_name,
                            '#' .$slot_cmd_string. '#'
                        );
                        SnipsUtils::logger('found option: '.$option_name. ' change to '.$slot_cmd_string);
                    }
                }
            }
            $expression->save();
        }
    }
}