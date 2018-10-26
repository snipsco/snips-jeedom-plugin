<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function snips_install() {
    $cron = cron::byClassAndFunction('snips', 'mqttClient');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('snips');
        $cron->setFunction('mqttClient');
        $cron->setEnable(1);
        $cron->setDeamon(1);
        $cron->setSchedule('* * * * *');
        $cron->setTimeout('1440');
        $cron->save();
    }
    
    $lang = translate::getLanguage();
    if ($lang == 'fr_FR') {
        config::save('defaultTTS', 'Désolé, je ne trouve pas les actions!', 'snips');
    }else if ($lang == 'en_US') {
        config::save('defaultTTS', 'Sorry, I cant find any actions!', 'snips');
    }

    config::save('isTagPlugin',0,'snips');
    config::save('isTagIdentifier',0,'snips');
    config::save('isTagIntent',1,'snips');
    config::save('isTagSlots',1,'snips');
    config::save('isTagSiteId',1,'snips');
    config::save('isTagQuery',0,'snips');
    config::save('isTagProbability',0,'snips');

    config::save('isVarMsgSession',0,'snips');
    config::save('isVarMsgSiteId',0,'snips');
}

function snips_update() {
    $cron = cron::byClassAndFunction('snips', 'mqttClient');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('snips');
        $cron->setFunction('mqttClient');
        $cron->setEnable(1);
        $cron->setDeamon(1);
        $cron->setSchedule('* * * * *');
        $cron->setTimeout('1440');
        $cron->save();
    }

    if (config::byKey('isTagPlugin', 'snips', "NULL") == "NULL")
        config::save('isTagPlugin',0,'snips');
    if (config::byKey('isTagIdentifier', 'snips', "NULL") == "NULL")
        config::save('isTagIdentifier',0,'snips');
    if (config::byKey('isTagIntent', 'snips', "NULL") == "NULL")
        config::save('isTagIntent',1,'snips');
    if (config::byKey('isTagSlots', 'snips', "NULL") == "NULL")
        config::save('isTagSlots',1,'snips');
    if (config::byKey('isTagSiteId', 'snips', "NULL") == "NULL")
        config::save('isTagSiteId',1,'snips');
    if (config::byKey('isTagQuery', 'snips', "NULL") == "NULL")
        config::save('isTagQuery',0,'snips');
    if (config::byKey('isTagProbability', 'snips', "NULL") == "NULL")
        config::save('isTagProbability',0,'snips');

    if (config::byKey('isVarMsgSession', 'snips', "NULL") == "NULL")
        config::save('isVarMsgSession',0,'snips');
    if (config::byKey('isVarMsgSiteId', 'snips', "NULL") == "NULL")
        config::save('isVarMsgSiteId',0,'snips');
}

function snips_remove() {
    $cron = cron::byClassAndFunction('snips', 'mqttClient');
    if (is_object($cron)) {
        $cron->stop();
        $cron->remove();
    }

    $obj = object::byName('Snips-Intents');
    if (is_object($obj)) {
        $obj->remove();
        snips::debug(__FUNCTION__, 'Removed object: Snips-Intents');
    }

    $eqLogics = eqLogic::byType('snips');
    foreach($eqLogics as $eq) {
        $cmds = snipsCmd::byEqLogicId($eq->getLogicalId);
        foreach($cmds as $cmd) {
            snips::debug(__FUNCTION__, 'Removed slot cmd: '.$cmd->getName());
            $cmd->remove();
        }
        snips::debug(__FUNCTION__, 'Removed intent entity: '.$eq->getName());
        $eq->remove();
    }

    snips::debug(__FUNCTION__, 'Removed Snips Voice assistant!');

    //log::add('snips','info','Suppression extension');
    $resource_path = realpath(dirname(__FILE__) . '/../resources');
    passthru('sudo /bin/bash ' . $resource_path . '/remove.sh ' . $resource_path . ' > ' . log::getPathToLog('SNIPS_dep') . ' 2>&1 &');
    return true;
}

?>
