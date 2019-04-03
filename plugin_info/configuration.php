<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>
<script type="text/javascript">
function snips_postSaveConfiguration(){
    console.log("Yes, I am snips_postSaveConfiguration");

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
<form class="form-horizontal">
    <fieldset>
        <div class="form-group">
            <label class="col-lg-4 control-label" >{{Host}}</label>
            <div class="col-lg-4">
                <input class="configKey form-control" data-l1key="mqttAddr" placeholder="127.0.0.1"/>
            </div>
            <div class="alert alert-info col-lg-3" role="alert">{{IP address / hostname of the master Snips device.}}</div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label" >{{Default feedback}}</label>
            <div class="col-lg-4">
                <textarea class="configKey form-control" data-l1key="defaultTTS" placeholder="Sorry I don't understand"></textarea>
            </div>
            <div class="alert alert-info col-lg-3" role="alert">{{Use <kbd>[A|B|C]</kbd> formate to make dynamic sentences.}}</div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label" >{{Dynamic Snips TTS}}</label>
            <div class="col-lg-4">
                <input type="checkbox" class="configKey" data-l1key="dynamicSnipsTTS" disabled="disabled"> {{Enable}}</input>
            </div>
            <div class="alert alert-info col-lg-3" role="alert"> {{When selecting a snips tts command in binding configuration with this option checked, the reply tts will be played on the device which received the command.}}</div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label" >{{Multi-dialog conversation}}</label>
            <div class="col-lg-4">
                <input type="checkbox" class="configKey" data-l1key="isMultiDialog"> {{Enable}}</input>
            </div>
            <div class="alert alert-info col-lg-3" role="alert"> {{By checking this option, snips will keep listening to your instructions once its hotword is triggered. If you want to terminate the conversion, simply ignore its new conversion prompt.}}</div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label" >{{Snips Variables}}</label>
            <div class="col-lg-4">
                <input type="checkbox" class="configKey" data-l1key="snipsMsgSession"> {{snipsMsgSession}}</input>
            </div>
            <div class="alert alert-info col-lg-3" role="alert"> {{Updated when a session is started and ended.}}</div>

            <label class="col-lg-4 control-label" ></label>
            <div class="col-lg-4">
                <input type="checkbox" class="configKey" data-l1key="snipsMsgSiteId"> {{snipsMsgSiteId}}</input>
            </div>
            <div class="alert alert-info col-lg-3" role="alert"> {{Assigned with snips device id which received the command when triggered.}}</div>

            <label class="col-lg-4 control-label" ></label>
            <div class="col-lg-4">
                <input type="checkbox" class="configKey" data-l1key="snipsMsgHotwordId"> {{snipsMsgHotwordId}}</input>
            </div>
            <div class="alert alert-info col-lg-3" role="alert"> {{Assigned with detected hotword model id when triggered.}}</div>
        </div>
    </fieldset>
</form>