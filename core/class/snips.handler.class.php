<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

//require_once dirname(__FILE__) . '/../../../core/class/cron.class.php';
require_once dirname(__FILE__) . '/snips.class.php';

class SnipsHandler
{
    static function logger($str = '', $level = 'debug')
    {
        $function = debug_backtrace(false, 2)[1]['function'];
        $msg = '['.__CLASS__.'] <'. $function .'> '.$str;
        log::add('snips', 'debug', $msg);
    }

    static function check_site_id_existnce($_site_id)
    {
        self::logger();
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
            self::logger('Created untracked snips site: '. $_site_id);
        }
    }

    static function set_site_id($_site_id)
    {
        self::logger();
        self::check_site_id_existnce($_site_id);
        $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSiteId');
        if (is_object($var)) {
            $var->setValue($_site_id);
            $var->save();
        }
    }

    static function clear_site_id()
    {
        self::logger();
        $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSiteId');
        if (is_object($var)) {
            $var->setValue('');
            $var->save();
        }
    }

    static function intent_detected($hermes, $payload)
    {
        self::logger();
        if(!stristr($payload->{'intent'}->{'intentName'}, 'jeedom')){
            return;
        }
        $hermes->publish_end_session($payload->{'sessionId'});
        snips::findAndDoAction($payload);
        if(config::byKey('isMultiDialog', 'snips', 0)){
            snips::hermes()->publish_start_session_action($_payload->{'siteId'}, null, null, null, true);
        }
    }

    static function session_started($hermes, $payload){
        self::logger();
        SnipsUtils::set_scenario_variable(
            'snipsMsgSiteId',
            $payload->{'siteId'}
        );
        SnipsUtils::set_scenario_variable(
            'snipsMsgSession',
            $payload->{'sessionId'}
        );
        // $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSession');
        // if (is_object($var)) {
        //     $var->setValue($payload->{'sessionId'});
        //     $var->save();
        // }
    }

    static function session_ended($hermes, $payload){
        self::logger();
        SnipsUtils::reset_scenario_variable('snipsMsgSiteId');
        SnipsUtils::reset_scenario_variable('snipsMsgSession');
        // $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSession');
        // if (is_object($var)) {
        //     $var->setValue('');
        //     $var->save();
        // }
    }

    static function hotword_detected($hermes, $payload){
        self::logger();
        SnipsUtils::set_scenario_variable(
            'snipsMsgHotwordId',
            $payload->{'modelId'}
        );
        // $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgHotwordId');
        // if (is_object($var)) {
        //     $var->setValue($payload->{'modelId'});
        //     $var->save();
        // }
    }
}