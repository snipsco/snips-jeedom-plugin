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
            <label class="col-lg-4 control-label" >{{IP address of the master snips device}}</label>
            <div class="col-lg-4">
                <input class="configKey form-control" data-l1key="mqttAddr" placeholder="127.0.0.1"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label" >{{Default feedback}}</label>
            <div class="col-lg-4">
                <textarea class="configKey form-control" data-l1key="defaultTTS" placeholder="Sorry I don't understand"></textarea>
            </div>
            <div class="alert alert-info col-lg-3" role="alert">{{Use <kbd>[A|B|C]</kbd> formate to make dynamic sentences}}</div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label" >{{Dynamic Snips TTS}}</label>
            <div class="col-lg-4">
                <input type="checkbox" class="configKey" data-l1key="dynamicSnipsTTS"> {{Enable}}</input>
            </div>
            <div class="alert alert-info col-lg-3" role="alert"> {{When selecting a snips tts command in binding configuration with this option checked, the reply tts will be played on the device which received the command.}}</div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label" >{{Snips Variables}}</label>
            <div class="col-lg-4">
                <input type="checkbox" class="configKey" data-l1key="isVarMsgSession"> {{snipsMsgSession}}</input>
            </div>
            <div class="alert alert-info col-lg-3" role="alert"> {{Updated when a session is started and ended.}}</div>

            <label class="col-lg-4 control-label" ></label>
            <div class="col-lg-4">
                <input type="checkbox" class="configKey" data-l1key="isVarMsgSiteId"> {{snipsMsgSiteId}}</input>
            </div>
            <div class="alert alert-info col-lg-3" role="alert"> {{Assigned with snips device id which received the command when triggered.}}</div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label" >{{Scenario Tags}}</label>
            <div class="col-lg-4">
                <li style="list-style-type:none;"><input type="checkbox" class="configKey" data-l1key="isTagPlugin"> {{#plugin#}}</input></li>
                <li style="list-style-type:none;"><input type="checkbox" class="configKey" data-l1key="isTagIdentifier"> {{#identifier#}}</input></li>
                <li style="list-style-type:none;"><input type="checkbox" class="configKey" data-l1key="isTagIntent"> {{#intent#}}</input></li>
                <li style="list-style-type:none;"><input type="checkbox" class="configKey" data-l1key="isTagSlots"> {{#slots#}}</input></li>
                <li style="list-style-type:none;"><input type="checkbox" class="configKey" data-l1key="isTagSiteId"> {{#siteId#}}</input></li>
                <li style="list-style-type:none;"><input type="checkbox" class="configKey" data-l1key="isTagQuery"> {{#query#}}</input></li>
                <li style="list-style-type:none;"><input type="checkbox" class="configKey" data-l1key="isTagProbability"> {{#probability#}}</input></li>
            </div>
            <div class="alert alert-info col-lg-3" role="alert"> {{Tags that will be passed to the scenario which is trigger by snips binding engine.}}</div>
            <div class="alert alert-warning col-lg-3" role="alert"> {{Beware that if you enable all the tags, some of the scenario blocks will not work due to the total tag size can overflow.}}</div>
        </div>

        <!-- <div class="form-group">
            <label class="col-lg-4 control-label" >{{Multi-dialog conversation}}</label>
            <div class="col-lg-4">
                <input type="checkbox" class="configKey" data-l1key="multiDialog"> {{Enable}}</input>
            </div>
        </div> -->

    </fieldset>
</form>

