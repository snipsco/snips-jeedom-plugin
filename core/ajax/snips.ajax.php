<?php
try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    require_once dirname(__FILE__) . '/../class/snips.assistant.manager.class.php';

    include_file('core', 'authentification', 'php');
    if (!isConnect('admin')) {
        throw new Exception(__('401 - Unauthorized access', __FILE__));
    }

    if (init('action') == 'reload') {
        $res = snips::fetchAssistantJson(init('username') , init('password'));

        if ($res == 1) {
            $config_json = snips::exportConfigration(null, false);
            SnipsAssistantManager::delete_assistant();
            SnipsAssistantManager::load_assistant();
            if (init('option') == 'mode_2') {
                snips::importConfigration(null, $config_json);
            }
        }
        ajax::success($res);
    }

    if (init('action') == 'tryToFetchDefault') {
        $res = snips::fetchAssistantJson('pi', 'raspberry');
        snips::logger('[Ajax] <tryToFetchDefault> Result code: '.$res);

        if ($res == 1) {
            $config_json = snips::exportConfigration(null, false);
            SnipsAssistantManager::delete_assistant();
            SnipsAssistantManager::load_assistant();
            if (init('option') == 'mode_2') {
                snips::importConfigration(null, $config_json);
            }
        }
        ajax::success($res);
    }

    if (init('action') == 'exportConfigration') {
        snips::exportConfigration(init('name'));
        ajax::success();
    }

    if (init('action') == 'getConfigurationList') {
        $command = 'ls ' . dirname(__FILE__) . '/../../config_backup/';
        $res = exec($command, $output, $return_var);
        ajax::success($output);
    }

    if (init('action') == 'importConfigration') {
        snips::importConfigration(init('configFileName'));
        ajax::success();
    }

    if (init('action') == 'removeAll') {
        SnipsAssistantManager::delete_assistant();
        ajax::success();
    }

    if (init('action') == 'playFeedback') {
        snips::logger('['.__FUNCTION__.'] Testing Play...');

        $text = SnipsTts::dump(init('text'), init('vars'))->get_message();

        $cmd = cmd::byString(init('cmd'));
        if (is_object($cmd)) {
            $options = array();
            $options['message'] = $text;
            $cmd->execCmd($options);
        }
        ajax::success();
    }

    if (init('action') == 'resetMqtt') {
        snips::resetMqtt();
        ajax::success();
    }

    if (init('action') == 'resetSlotsCmd') {
        snips::resetSlotsCmd();
        ajax::success();
    }

    if (init('action') == 'getSnipsType') {
        $cmd = cmd::byId(init('cmd'));
        $snips_type = $cmd->getConfiguration('entityId');
        ajax::success($snips_type);
    }

    if (init('action') == 'fetchAssistant') {
        snips::fetchAssistantJson();
        ajax::success();
    }

    if (init('action') == 'getMasterDevices') {
        $res = config::byKey('masterSite', 'snips', 'default');
        ajax::success($res);
    }

    if (init('action') == 'findSnipsDevice') {
        snips::findDevice(init('siteId'));
        ajax::success();
    }

    if (init('action') == 'postConfiguration') {
        snips::postConfiguration();
        ajax::success();
    }

    throw new Exception(__('No method corresponding to : ', __FILE__) . init('action'));
}

catch(Exception $e) {
    ajax::error(displayExeption($e) , $e->getCode());
}