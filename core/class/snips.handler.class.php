<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

require_once dirname(__FILE__) . '/snips.class.php';
require_once dirname(__FILE__) . '/snips.binding.class.php';
require_once dirname(__FILE__) . '/snips.utils.class.php';
require_once dirname(__FILE__) . '/snips.binding.scenario.class.php';

class SnipsHandler
{
    /* check if this site is registered in the system */
    static function check_site_id_existnce($_site_id)
    {
        SnipsUtils::logger();
        $obj = object::byName('Snips-Intents');
        if (!$obj) {
            return;
        }
        $object_id = $obj->getId();

        $dev = eqLogic::byLogicalId('Snips-TTS-'.$_site_id, 'snips');
        if (!is_object($dev)){
            $dev = new snips();
            $dev->setName('Snips-TTS-'.$_site_id);
            $dev->setLogicalId('Snips-TTS-'.$_site_id);
            $dev->setEqType_name('snips');
            $dev->setIsEnable(1);
            $dev->setConfiguration('snipsType', 'TTS');
            $dev->setConfiguration('siteName', $_site_id);
            $dev->setObject_id($object_id);
            $dev->save();
            SnipsUtils::logger('Created untracked snips site: '. $_site_id);
        }
    }

    /* intent detected handler */
    static function intent_detected($hermes, $payload)
    {
        if (!stristr($payload->{'intent'}->{'intentName'}, 'jeedom')) {
            // not jeedom intent, no response
            return;
        } else {
            // jeedom intent, terminate the session to prevent block
            $hermes->publish_end_session($payload->{'sessionId'});
        }

        SnipsUtils::logger('found intent name is :'. $payload->{'intent'}->{'intentName'});
        $intentEq = eqLogic::byLogicalId(
            $payload->{'intent'}->{'intentName'},
            'snips'
        );

        if ($intentEq->getConfiguration('isInteraction')) {
            // jeedom interaction, forward input then return
            $res = interactQuery::tryToReply($payload->{'input'});
            $hermes->publish_start_session_notification(
                $payload->{'siteId'},
                $res['reply']
            );
            return;
        }

        // get all the usable values
        $slots_values = SnipsUtils::extract_slots_value($payload->{'slots'});

        // set all the slots, find binding will use
        SnipsUtils::set_slot_cmd(
            $slots_values,
            $payload->{'intent'}->{'intentName'}
        );

        // get all the bindings belong to detected intent
        $obj_bindings = $intentEq->get_bindings();

        // find bindings
        $good_bindings = SnipsBinding::get_bindings_match_condition(
            $obj_bindings,
            $slots_values
        );

        // execute all the actions for each good binding
        // reply message on the site where the message is received
        foreach ($good_bindings as $binding) {
            $binding->execute_all();
            $hermes->publish_start_session_notification(
                $payload->{'siteId'},
                $binding->get_tts_message()
            );
        }

        // sync with execution
        sleep(1);

        // reset all the slots
        SnipsUtils::reset_slots_cmd();

        // if multi-turn dialogue is enabled, turn on a new session
        if(config::byKey('isMultiDialog', 'snips', 0)){
            $hermes->publish_start_session_action($payload->{'siteId'});
        }
    }

    /* session started handler */
    static function session_started($hermes, $payload){
        SnipsUtils::logger();
        SnipsUtils::set_scenario_variable(
            'snipsMsgSiteId',
            $payload->{'siteId'}
        );
        SnipsUtils::set_scenario_variable(
            'snipsMsgSession',
            $payload->{'sessionId'}
        );
    }

    /* session ended handler */
    static function session_ended($hermes, $payload){
        SnipsUtils::logger();
        SnipsUtils::reset_scenario_variable('snipsMsgSiteId');
        SnipsUtils::reset_scenario_variable('snipsMsgSession');
    }

    /* hotword detected handler */
    static function hotword_detected($hermes, $payload){
        SnipsUtils::logger();
        SnipsUtils::set_scenario_variable(
            'snipsMsgHotwordId',
            $payload->{'modelId'}
        );
    }
}