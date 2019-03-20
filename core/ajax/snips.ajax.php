<?php
try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    require_once dirname(__FILE__) . '/../class/snips.assistant.manager.class.php';
    require_once dirname(__FILE__) . '/../class/snips.utils.class.php';

    include_file('core', 'authentification', 'php');
    if (!isConnect('admin')) {
        throw new Exception(__('401 - Unauthorized access', __FILE__));
    }

    if (init('action') == 'reload') {
        $res = SnipsUtils::fetch_running_config_files(init('username') , init('password'));

        if ($res == 1) {
            $config_json = SnipsUtils::export_bindings(null, false);
            SnipsAssistantManager::delete_assistant();
            SnipsAssistantManager::load_assistant();
            if (init('option') == 'mode_2') {
                SnipsUtils::import_bindings(null, $config_json);
            }
        }
        ajax::success($res);
    }

    if (init('action') == 'tryToFetchDefault') {
        $res = SnipsUtils::fetch_running_config_files();

        SnipsUtils::logger('[Ajax] <tryToFetchDefault> Result code: '.$res);

        if ($res == 1) {
            $config_json = SnipsUtils::export_bindings(null, false);
            SnipsAssistantManager::delete_assistant();
            SnipsAssistantManager::load_assistant();
            if (init('option') == 'mode_2') {
                SnipsUtils::import_bindings(null, $config_json);
            }
        }
        ajax::success($res);
    }

    if (init('action') == 'exportConfigration') {
        SnipsUtils::export_bindings(init('name'));
        ajax::success();
    }

    if (init('action') == 'getConfigurationList') {
        $command = 'ls ' . dirname(__FILE__) . '/../../config_backup/';
        $res = exec($command, $output, $return_var);
        ajax::success($output);
    }

    if (init('action') == 'importConfigration') {
        SnipsUtils::import_bindings(init('configFileName'));
        ajax::success();
    }

    if (init('action') == 'removeAll') {
        SnipsAssistantManager::delete_assistant();
        ajax::success();
    }

    if (init('action') == 'playFeedback') {
        $text = SnipsTts::dump(init('text'), init('vars'))->get_message();

        $cmd = cmd::byString(init('cmd'));
        if (is_object($cmd)) {
            $options = array();
            $options['message'] = $text;
            $cmd->execCmd($options);
        }
        ajax::success();
    }

    if (init('action') == 'getSnipsType') {
        $cmd = cmd::byId(init('cmd'));
        $snips_type = $cmd->getConfiguration('entityId');
        ajax::success($snips_type);
    }

    if (init('action') == 'findSnipsDevice') {
        SnipsUtils::find_device(init('siteId'));
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