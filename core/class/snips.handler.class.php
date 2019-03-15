<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

//require_once dirname(__FILE__) . '/../../../core/class/cron.class.php';
require_once dirname(__FILE__) . '/snips.class.php';

class SnipsHandler
{
    static function logger($_str)
    {
        $msg = '['.__CLASS__.'] '.$_str;
        log::add('snips', 'debug', $msg);
        echo $msg."\n";
    }

    static function check_site_id_existnce($_site_id)
    {
        self::logger('<'.__FUNCTION__.'>');
        $dev = eqLogic::byLogicalId('Snips-TTS-'.$_site_id, 'snips');
        if (!is_object($dev)){
            $dev = new snips();
            $dev->setName('Snips-TTS-'.$_site_id);
            $dev->setLogicalId('Snips-TTS-'.$_site_id);
            $dev->setEqType_name('snips');
            $dev->setIsEnable(1);
            $dev->setConfiguration('snipsType', 'TTS');
            $dev->setConfiguration('siteName', $_site_id);
            $dev->setObject_id(object::byName('Snips-Intents')->getId());
            $dev->save();
            self::logger('<'.__FUNCTION__.'> created untracked snips site: '.$_site_id);
        }
    }

    static function set_site_id($_site_id)
    {
        self::logger('<'.__FUNCTION__.'>');
        self::check_site_id_existnce($_site_id);
        $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSiteId');
        if (is_object($var)) {
            $var->setValue($_site_id);
            $var->save();
        }
    }

    static function clear_site_id()
    {
        self::logger('<'.__FUNCTION__.'>');
        $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSiteId');
        if (is_object($var)) {
            $var->setValue('');
            $var->save();
        }
    }

    static function intent_detected($hermes, $payload)
    {
        self::logger('<'.__FUNCTION__.'>');

        if(!stristr($payload->{'intent'}->{'intentName'}, 'jeedom')){
            return;
        }
        self::logger('using hermes to turn *******************************');
        $hermes->publish_end_session($payload->{'sessionId'});
        snips::findAndDoAction($payload);
        if(config::byKey('isMultiDialog', 'snips', 0)){
            snips::hermes()->publish_start_session_action($_payload->{'siteId'}, null, null, null, true);
        }
    }

    static function session_started($hermes, $payload){
        self::logger('<'.__FUNCTION__.'>');
        self::set_site_id($payload->{'siteId'});
        $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSession');
        if (is_object($var)) {
            $var->setValue($payload->{'sessionId'});
            $var->save();
        }
    }

    static function session_ended($hermes, $payload){
        self::logger('<'.__FUNCTION__.'>');
        self::clear_site_id();
        $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgSession');
        if (is_object($var)) {
            $var->setValue('');
            $var->save();
        }
    }

    static function hotword_detected($hermes, $payload){
        self::logger('<'.__FUNCTION__.'>');
        $var = dataStore::byTypeLinkIdKey('scenario', -1, 'snipsMsgHotwordId');
        if (is_object($var)) {
            $var->setValue($payload->{'modelId'});
            $var->save();
        }
    }
}