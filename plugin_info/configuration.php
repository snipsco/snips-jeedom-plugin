<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>

<form class="form-horizontal">
    <fieldset>
        <div class="form-group">
            <label class="col-lg-4 control-label" >{{Host}}
				<sup><i class="fas fa-question-circle" title="{{IP address / hostname of the master Snips device.}}"></i></sup>
			</label>
            <div class="col-lg-4">
                <input class="configKey form-control" data-l1key="mqttAddr" placeholder="127.0.0.1"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label" >{{Default feedback}}
				<sup><i class="fas fa-question-circle" title="{{Use <kbd>[A|B|C]</kbd> formate to make dynamic sentences.}}"></i></sup>
			</label>
            <div class="col-lg-4">
                <textarea class="configKey form-control" data-l1key="defaultTTS" placeholder="Sorry I don't understand"></textarea>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label" >{{Dynamic Snips TTS}}
				<sup><i class="fas fa-question-circle" title="{{When selecting a snips tts command in binding configuration with this option checked, the reply tts will be played on the device which received the command.}}"></i></sup>
			</label>
            <div class="col-lg-4">
                <input type="checkbox" class="configKey" data-l1key="dynamicSnipsTTS" disabled="disabled"></input>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label" >{{Multi-dialog conversation}}
				<sup><i class="fas fa-question-circle" title="{{By checking this option, snips will keep listening to your instructions once its hotword is triggered. If you want to terminate the conversion, simply ignore its new conversion prompt.}}"></i></sup>
			</label>
            <div class="col-lg-4">
                <input type="checkbox" class="configKey" data-l1key="isMultiDialog"></input>
            </div>
        </div>
        
        <div class="form-group">
            <legend><center>{{Snips Variables}}</center></legend>
            <label class="col-lg-4 control-label" >{{snipsMsgSession}} <sup><i class="fas fa-question-circle" title="{{Updated when a session is started and ended.}}"></i></sup></label>
            <div class="col-lg-8">
                   <input type="checkbox" class="configKey" data-l1key="snipsMsgSession"></input>
            </div>

            <label class="col-lg-4 control-label" >{{snipsMsgSiteId}} <sup><i class="fas fa-question-circle" title="{{Assigned with snips device id which received the command when triggered.}}"></i></sup></label>
            <div class="col-lg-8">
                 <input type="checkbox" class="configKey" data-l1key="snipsMsgSiteId"></input>
            </div>

            <label class="col-lg-4 control-label" >{{snipsMsgHotwordId}} <sup><i class="fas fa-question-circle" title="{{Assigned with detected hotword model id when triggered.}}"></i></sup></label>
            <div class="col-lg-8">
                 <input type="checkbox" class="configKey" data-l1key="snipsMsgHotwordId"></input>
            </div>
        </div>
    </fieldset>
</form>

<script type="text/javascript">
function snips_postSaveConfiguration(){
    $.ajax({
        type: "POST",
        url: "plugins/snips/core/ajax/snips.ajax.php",
        data: {
            action: "postConfiguration"
        },
        dataType: 'json',
        global: false,
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({
                    message: data.result,
                    level: 'danger'
                });
                return;
            }
        }
    });
}
</script>
