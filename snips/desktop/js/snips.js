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

INTENT = null; 
INTENT_ID = null;

////--------------------Events Binding--------------------////
$(function () {
    $('[data-toggle="tooltip"]').tooltip();

    if($('input[name=reaction]').is(":checked")){
        $(this).closest('label').addClass('active');
    }else{
        $(this).closest('label').removeClass('active');
    }

    if($('#isEnable').is(":checked")){
        $(this).closest('label').addClass('active');
        $(this).closest('label').removeClass('btn-success');
        $(this).closest('label').addClass('btn-danger');
        $(this).closest('label').find('span').text('× Disable');
    }else{
        $(this).closest('label').removeClass('active');
        $(this).closest('label').removeClass('btn-danger');
        $(this).closest('label').addClass('btn-success');
        $(this).closest('label').find('span').text('Enable');
    }
})

//--This is used to import all the available slots when the page is ready.
$(document).on('change', '#intentName', function() {
	INTENT = $('#intentName').val(); 
    INTENT_ID = $('#intentId').val();
});

$(document).on('change', 'input[name=reaction]', function() {
    if($(this).is(":checked")){
        $(this).closest('label').addClass('active');

    }else{
        $(this).closest('label').removeClass('active');

    }
});

//<input value="" class="  "   data-cmd_id="105" data-uid="cmd105__937051297__">
//--This is used to toggle emable button when click
$(document).on('change', '#isEnable', function() {
    if($(this).is(":checked")){
        $(this).closest('label').addClass('active');
        $(this).closest('label').removeClass('btn-success');
        $(this).closest('label').addClass('btn-danger');
        $(this).closest('label').find('span').text('× Disable');
    }else{
        $(this).closest('label').removeClass('active');
        $(this).closest('label').removeClass('btn-danger');
        $(this).closest('label').addClass('btn-success');
        $(this).closest('label').find('span').text('Enable');
    }
});

//--This is the function used to hack system command select modal
$(document).on('change', '#table_mod_insertCmdValue_valueEqLogicToMessage .mod_insertCmdValue_object select', function() {
	console.log('input object changed captured, value is:'+$(this).find("option:selected").text());

	if($(this).find("option:selected").text() == 'Snips-Intents'){
		$('#table_mod_insertCmdValue_valueEqLogicToMessage').find('thead').html(
    		'<tr>'+
                '<th style="width: 150px;">Object</th>'+
                '<th style="width: 150px;">Intents</th>'+
                '<th style="width: 150px;">Slots</th>'+
            '</tr>'
			);

        // $('#table_mod_insertCmdValue_valueEqLogicToMessage').find('.mod_insertCmdValue_eqLogic').html(
        //     '<select class="form-control" disabled>'+
        //         '<option value="' + INTENT_ID + '">' + INTENT + '</option>'+
        //     '</select>'
        //     );

	}else{
		$('#table_mod_insertCmdValue_valueEqLogicToMessage').find('thead').html(
		'<tr>'+
            '<th style="width: 150px;">Object</th>'+
            '<th style="width: 150px;">Device</th>'+
            '<th style="width: 150px;">Command</th>'+
        '</tr>'
			);
	}
});

//--This is the function used to add an intent-command mapping
$('#bt_addNewBinding').off('click').on('click', function () {
    bootbox.prompt("{{Please give a name}}", function (result) {
        if (result !== null && result != '') {
            addBinding({name: result});
        }
    });
});

//--This is the function used to rename the intent
$('body').off('click','.rename').on('click','.rename',  function () {
    var el = $(this);
    bootbox.prompt("{{New name?}}", function (result) {
        if (result !== null && result != '') {
            var previousName = el.text();
            el.text(result);
            el.closest('.panel.panel-default').find('span.name').text(result);
        }
    });
});

//--This is the function used to select system action command
$("body").off('click','.listCmdAction').on( 'click','.listCmdAction', function () {
    var el = $(this);
    jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
        var input = el.closest('.action').find('.expressionAttr[data-l1key=cmd]');
        input.value(result.human);

        jeedom.cmd.displayActionOption(input.value(), '', function (html) {
         input.closest('.action').find('.actionOptions').html(html);
         //taAutosize();
     });
 });
});

//--This is the function used to select equipment action command
$("body").off('click','.listAction').on( 'click','.listAction',function () {
    var el = $(this);
    jeedom.getSelectActionModal({}, function (result) {
        var input = el.closest('.action').find('.expressionAttr[data-l1key=cmd]');
        input.value(result.human);

        jeedom.cmd.displayActionOption(input.value(), '', function (html) {
         input.closest('.action').find('.actionOptions').html(html);
         //taAutosize();
     });
 });
});

//--This is the function used to select system info command
$("body").delegate(".listInfoCmd",'click', function(){
    var el = $(this);
    jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
        var input = el.closest('.infoCmd').find('.bindingAttr[data-l1key=tts][data-l2key=vars]');
        input.value(result.human);
    });
});


//--This is the function used to reacte with action option selection 
$("#div_bindings").off('click', '.listEquipementInfo').on('click', '.listEquipementInfo', function(){
    var el = $(this);
    
    jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
        var input = el.closest('.actionOptions').find('input[data-cmd_id='+el.data("cmd_id")+'][data-uid='+el.data("uid")+']');
        input.value(result.human);

        $.ajax({
                type: "POST", // method to transmit request
                url: "plugins/snips/core/ajax/snips.ajax.php", 
                data: {
                    action: "getSnipsType",
                    cmd:result.cmd.id,
                },
                dataType: 'json',
                global: false,
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) {

                    if (data.result == 'snips/percentage') {
                        el.closest('.action').find('.slotsOptions').empty();
                        displayValueMap(el.closest('.action').find('.slotsOptions'));
                    }else{
                        el.closest('.action').find('.slotsOptions').empty();
                    }
                }
        });   
    });
});


//--This is the function used to remove an action
$("body").off('click', '.bt_removeAction').on( 'click', '.bt_removeAction',function () {
    var type = $(this).attr('data-type');
    $(this).closest('.' + type).remove();
});

//--This is the function used to remove a condition
$("body").off('click', '.bt_removeCondition').on( 'click', '.bt_removeCondition',function () {
    var type = $(this).attr('data-type');
    $(this).closest('.' + type).remove();
});

//--This is the function used to add condition for binding
$("#div_bindings").off('click','.bt_addCondition').on('click','.bt_addCondition',  function () {
    addCondition({}, $(this).closest('.binding'));
});

//--This is the function used to add action to binding
$("#div_bindings").off('click','.bt_addAction').on( 'click','.bt_addAction',function () {
    addAction({}, $(this).closest('.binding'));
});

//--This is the function used to check if an action has options
$('body').off('focusout','.cmdAction.expressionAttr[data-l1key=cmd]').on( 'focusout', '.cmdAction.expressionAttr[data-l1key=cmd]',function (event) {
    var type = $(this).attr('data-type')
    var expression = $(this).closest('.' + type).getValues('.expressionAttr');
    var el = $(this);
    jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
        el.closest('.' + type).find('.actionOptions').html(html);
        taAutosize();
    })
});

//--This is the function used to remove a binding
$("#div_bindings").off('click','.bt_removeBinding').on('click', '.bt_removeBinding',function () {
    $(this).closest('.binding').remove();
});

//--This is the function used to duplicate a binding
$('#div_bindings').off('click','.bt_duplicateBinding').on('click','.bt_duplicateBinding',  function () {
    var binding = $(this).closest('.binding').clone();
    bootbox.prompt("{{Please give a name to this new binding}}", function (result) {
        if (result !== null) {
            var random = Math.floor((Math.random() * 1000000) + 1);
            binding.find('a[data-toggle=collapse]').attr('href', '#collapse' + random);
            binding.find('.panel-collapse.collapse').attr('id', 'collapse' + random);
            binding.find('.bindingAttr[data-l1key=name]').html(result);
            binding.find('.name').html(result);
            $('#div_bindings').append(binding);
            $('.collapse').collapse();
        }
    });
});

//-- Hidden tabs
$('.nav-tabs li a').off('click').on('click',function(){
     setTimeout(function(){ 
        taAutosize();
    }, 50);
});

//-- Not clear yet
$('#div_bindings').off('click','.panel-heading').on('click','.panel-heading',function(){
     setTimeout(function(){ 
        taAutosize();
    }, 50);
})

//-- React when enable/disable
$("#div_bindings").delegate(".isActivated",'change', function(){
    var el = $(this);

    var btn = el.closest('div').find('label').find('a');

    if(el.is(":checked")){
        btn.removeClass('btn-success');
        btn.addClass('btn-danger');
        btn.text('× Disable');
    }else{
    	btn.removeClass('btn-danger');
        btn.addClass('btn-success');
        btn.text('Enable');
    }
});

//-- React when feedback tts has been updated
$("#div_bindings").delegate(".feedbackSpeech",'keyup', function(){
    
    var el = $(this).closest('.binding');

    var listVarCount = el.find('.div_infoCmd').find('.infoCmd').length;
    var textVarCount = $(this).val().split('{#}').length-1;

    // console.log('Count from text:'+textVarCount);
    // console.log('Count from exist:'+listVarCount);
    var count = 0 ;
    while(listVarCount != textVarCount){
        count += 1;
        if(listVarCount > textVarCount){
            // InfoCmd is more than needed
            // Reduct from the last one
            //debugger;
            el.find('.div_infoCmd').find('.infoCmd:last').remove();
            
        }else if(listVarCount < textVarCount){
            // InfoCmd is less than needed
            // Append new to the last one
            addInfoCmd({}, el);
            
        }
        var listVarCount = el.find('.div_infoCmd').find('.infoCmd').length;
        var textVarCount = $(this).val().split('{#}').length-1;
        if (count>10) {break;}
    }
});

//-- Sort all the bindings
$("#div_bindings").sortable({axis: "y", cursor: "move", items: ".binding", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});



////--------------------Ajax setup--------------------////

//--This is the function used to reload all the skills from installed assistant
$('.reload').on('click', function () {
    
    var reloadConfirm = 0;
    var username = '';
    var psssword = '';

    bootbox.confirm({
        title: "Attention",
        message: "Only do this operation when you do not updated snips assistant! Before reload, please export all yoru binding config file!",
        buttons: {
            confirm: {
                label: '<i class="fa fa-check"></i> Yes',
                className: 'btn-success'
            },
            cancel: {
                label: '<i class="fa fa-times"></i> No',
                className: 'btn-danger'
            }
        },
        callback: function (result) {
            if(result){
                bootbox.prompt({
                    title: "Username (default: pi)",
                    inputType: 'text',
                    callback: function (result) {
                        if (result) {
                            username = result;
                            bootbox.prompt({
                                title: "Password (default: raspberry)",
                                inputType: 'password',
                                callback: function (result) {
                                    if (result) {
                                        password = result;
                                        var loading = bootbox.dialog({
                                            message: '<div class="text-center"><i class="fa fa-spin fa-spinner"></i> Loading assistant from remote site...</div>',
                                            closeButton: false
                                         });

                                        console.log('username: '+ username);
                                        console.log('password: '+ password);
                                        $.ajax({
                                                type: "POST", // method to transmit request
                                                url: "plugins/snips/core/ajax/snips.ajax.php", 
                                                data: {
                                                    action: "reload",
                                                    username: username,
                                                    password: password,
                                                },
                                                dataType: 'json',
                                                global: false,
                                                error: function (request, status, error) {
                                                    handleAjaxError(request, status, error);
                                                },
                                                success: function (data) {
                                                    var msg = '';
                                                    var title = '';
                                                    loading.modal('hide');
                                                    if (data.result == 1) {
                                                        title = '<a style="color:#5cb85c;"><i class="fa fa-check"></i> Successed</a>';
                                                        msg = 'Assistant loaded!';
                                                    }
                                                    if (data.result == 0) {
                                                        title = '<a style="color:#d9534f;"><i class="fa fa-times"></i> Failed</a>';
                                                        msg = 'Can not fetch assistant!';
                                                    }
                                                    if (data.result == -1) {
                                                        title = '<a style="color:#d9534f;"><i class="fa fa-times"></i> Failed</a>';
                                                        msg = 'Wrong username/ password!';
                                                    }
                                                    if (data.result == -2) {
                                                        title = '<a style="color:#d9534f;"><i class="fa fa-times"></i> Failed</a>';
                                                        msg = 'Connection error. Please go -> [plugin] -> [snips] -> [configuration]. Check if you set a correct [Snips site IP address]!';
                                                    }

                                                    bootbox.alert({
                                                        title: title,
                                                        message: msg,
                                                        callback: function (result) {
                                                            location.reload();
                                                        },
                                                        closeButton: false
                                                    });
                                                }
                                            });
                                    }
                                }
                            });
                        }      
                    }
                });
            }
        }
    });                 	
});

//--This is the function used to export all the binding information
$('.exportConfigration').on('click', function () {
    bootbox.prompt("{{Please give a name to this export file}}", function (result) {
        if (result !== null && result != '') {
            $.ajax({
                        type: "POST", // method to transmit request
                        url: "plugins/snips/core/ajax/snips.ajax.php", 
                        data: {
                            action: "exportConfigration",
                            name: result,
                        },
                        dataType: 'json',
                        global: false,
                        error: function (request, status, error) {
                            handleAjaxError(request, status, error);
                        },
                        success: function (data) { 
                            if (data.state != 'ok') {
                                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                                return;
                            }
                            $('#div_alert').showAlert({message: 'Successfully exported!', level: 'success'});
                            location.reload();
                        }
                    });
        }else if(result = ''){
            $('#div_alert').showAlert({message: 'Please specify a name!', level: 'warning'});
        }

    });
            
});

//--This is the function used to import binding config
$('.importConfigration').on('click', function () {
    $.ajax({
        type: "POST", 
        url: "plugins/snips/core/ajax/snips.ajax.php", 
        data: {
            action: "getConfigurationList",
        },
        dataType: 'json',
        global: false,
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { 
            if (isset(data.result) && data.result!= null && data.result != '') {

                var options = [{text: 'Choose a configuration file...',value: '',}];

                for(var n in data.result){
                    options.push({text: data.result[n] ,value: data.result[n]});
                }

                bootbox.prompt({
                    title: '<a><i class="fa fa-download"></i> Import configuration</a>',
                    inputType: 'select',
                    inputOptions: options,
                    callback: function (result) {
                        if (result != null && result != '') {
                            console.log('going to import file :'+result);
                            $.ajax({
                                type: "POST", 
                                url: "plugins/snips/core/ajax/snips.ajax.php", 
                                data: {
                                    action: "importConfigration",
                                    configFileName: result,
                                },
                                dataType: 'json',
                                global: false,
                                error: function (request, status, error) {
                                    handleAjaxError(request, status, error);
                                },
                                success: function (data) { 
                                    bootbox.alert({
                                        title: '<a style="color:#5cb85c;"><i class="fa fa-check"></i> Successed</a>',
                                        message: 'Configuration file ['+result+'] has been imported!',
                                        callback: function (result) {
                                            location.reload();
                                        },
                                        closeButton: false
                                    });
                                }
                            });
                        }
                    },
                });
            }else{
                bootbox.alert({
                    title: '<a style="color:#d9534f;"><i class="fa fa-times"></i> Failed</a>',
                    message: 'Can not find any configuration file !',
                    closeButton: false
                });
            }
        }
    }); 
});


//--This function is used to remove all the loaded skills
$('.removeAll').on('click', function () {
	bootbox.confirm({
		message: "ATTENTION: This operation will delete all the intents and its binding records! Would you continue?",
		buttons: {
			confirm: {
				label: '<i class="fa fa-check"></i> Yes',
				className: 'btn-success'
			},
			cancel: {
				label: '<i class="fa fa-times"></i> No',
				className: 'btn-danger'
			}
		},
		callback: function (result) {
		if(result){
				$('#div_alert').showAlert({message: 'Removing all the skills', level: 'danger'});

				$.ajax({
			                type: "POST", // méthode de transmission des données au fichier php
			                url: "plugins/snips/core/ajax/snips.ajax.php", 
			                data: {
			                    action: "removeAll",
			                },
			                dataType: 'json',
			                global: false,
			                error: function (request, status, error) {
			                    handleAjaxError(request, status, error);
			                },
			                success: function (data) { 
			                    if (data.state != 'ok') {
			                        $('#div_alert').showAlert({message: data.result, level: 'danger'});
			                        return;
			                    }
			                    $('#div_alert').showAlert({message: 'Successfully removed all the skills!', level: 'success'});
			                    location.reload();
			                }
			            });
				}
			}
	});
});

//--This function is used to preview the feedback speech
$("#div_bindings").delegate(".playFeedback",'click', function(){

    var org_text = $(this).closest('.binding').find('.feedbackSpeech').val();
    var vars = [];

    $(this).closest('.binding').find('.infoCmd').find('.bindingAttr').each(function(){
        vars.push($(this).val());
    });

    var language = $('span[data-l1key=configuration][data-l2key=language]').text();

	//console.log('Play feedback');
	$.ajax({
                type: "POST", // méthode de transmission des données au fichier php
                url: "plugins/snips/core/ajax/snips.ajax.php", 
                data: {
                    action: "playFeedback",
                    text: org_text,
                    vars: vars,
                    lang: language,
                },
                dataType: 'json',
                global: false,
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) { 
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({message: data.result, level: 'danger'});
                        return;
                    }
                }
            });
});

//--This function is used to reset MQTT deamon
$('.resetMqtt').on('click', function(){

	$.ajax({
                type: "POST", // méthode de transmission des données au fichier php
                url: "plugins/snips/core/ajax/snips.ajax.php", 
                data: {
                    action: "resetMqtt",
                },
                dataType: 'json',
                global: false,
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) { 
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({message: data.result, level: 'danger'});
                        return;
                    }
                    $('#div_alert').showAlert({message: 'reseting MQTT client', level: 'success'});
                }
            });
});

//--This function is used to reset slot cmd value
$('.resetSlotsCmd').on('click', function(){

	$.ajax({
                type: "POST", // méthode de transmission des données au fichier php
                url: "plugins/snips/core/ajax/snips.ajax.php", 
                data: {
                    action: "resetSlotsCmd",
                },
                dataType: 'json',
                global: false,
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) { 
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({message: data.result, level: 'danger'});
                        return;
                    }
                    $('#div_alert').showAlert({message: 'reseting slot value to NULL', level: 'success'});
                }
            });
});

//--This function is used to reset slot cmd value
$('.fetchAssistant').on('click', function(){

    $.ajax({
                type: "POST", // méthode de transmission des données au fichier php
                url: "plugins/snips/core/ajax/snips.ajax.php", 
                data: {
                    action: "fetchAssistant",
                },
                dataType: 'json',
                global: false,
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) { 
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({message: data.result, level: 'danger'});
                        return;
                    }
                    $('#div_alert').showAlert({message: 'File has been fetched', level: 'success'});
                }
            });
});


////--------------------Syetem function rewrite--------------------////
function printEqLogic(_eqLogic) {
    $('#div_bindings').empty();
    if (isset(_eqLogic.configuration) && isset(_eqLogic.configuration.bindings)) {
    	actionOptions = []

	    for (var i in _eqLogic.configuration.bindings) {
	        addBinding(_eqLogic.configuration.bindings[i],false);
	    }

	    jeedom.cmd.displayActionsOption({
	        params : actionOptions,
	        async : false,
	        error: function (error) {
	          	$('#div_alert').showAlert({message: error.message, level: 'danger'});
	      	},
	      	success : function(data){
	        	for(var i in data){
	            	$('#'+data[i].id).append(data[i].html.html);

	        	}

	        	taAutosize();
	    	}
		});

	}
}

function saveEqLogic(_eqLogic) {
    //debugger;
    if (!isset(_eqLogic.configuration)) {
    	_eqLogic.configuration = {};
    }
    
    _eqLogic.configuration.bindings = [];
    $('#div_bindings .binding').each(function () {
        var binding = $(this).getValues('.bindingAttr')[0];
        binding.condition = $(this).find('.condition').getValues('.conditionAttr');
        binding.action = $(this).find('.action').getValues('.expressionAttr');
        //binding.tts.vars = $(this).find('.infoCmd').getValues('.infoCmdAttr');
        _eqLogic.configuration.bindings.push(binding);
    });
    return _eqLogic;
}

////--------------------Snips Functions--------------------////

//-- Add binding configurations to table
function addBinding(_binding) {
    if (init(_binding.name) == '') {
        return;
    }

    if (init(_binding.enable) == '') {
        _binding.enable = 1;
    }

    var random = Math.floor((Math.random() * 1000000) + 1);

    var div = '<div class="binding panel panel-default" style="margin-bottom:10px;border-radius: 0px;">';

    //** Name section
    div += '<div class="panel-heading" style="background-color:#fff;">';

    div += '<h4 class="panel-title">';
    div += '<a data-toggle="collapse" data-parent="#div_bindings" href="#collapse' + random + '">';
    div += '<div class="name" style="display:inline-block;">' + _binding.name + '</div>';
    div += '</a>';
            div += '<span class="pull-right">';

            if (isset(_binding.action)) {
                div += '<span class="badge" style="margin-right: 20px;font-size: 0.8em; background-color: #337ab7;">'+_binding.action.length+' actions</span>';                
            }else{
                div += '<span class="badge" style="margin-right: 20px;font-size: 0.8em; background-color: #f0ad4e;">No action</span>';
            }

            if (isset(_binding.nsr_slots)) {
                div += '<span class="badge" style="margin-right: 20px;font-size: 0.8em; background-color: #337ab7;">'+_binding.nsr_slots.length+' slots</span>';                
            }else{
                div += '<span class="badge" style="margin-right: 20px;font-size: 0.8em; background-color: #f0ad4e;">No slot</span>';
            }

            if (_binding.enable == 1) {
                div += '<span class="badge" style="margin-right: 20px;font-size: 0.8em; background-color: #5cb85c;">Enabled</span>';
            }else{
                div += '<span class="badge" style="margin-right: 20px;font-size: 0.8em; background-color: #d9534f;">Disabled</span>';
            }   

            div += '<span class="btn-group" role="group">';
            div += '<a class="btn btn-xs btn-default bt_duplicateBinding"><i class="fa fa-files-o"></i> {{Duplicate}}</a>';
            div += '<a class="btn btn-xs btn-danger bt_removeBinding"><i class="fa fa-minus-circle"></i></a>';
            div += '</span>';
            div += '</span>';
    div += '</h4>';
    div += '</div>';


    //** Content section
    div += '<div id="collapse' + random + '" class="panel-collapse collapse in">';
    div += '<div class="panel-body"  style="background-color:#f5f7f9;>';
    div += '<div>'; // well

    div += '<form class="form-horizontal" role="form">';

		//** Basic infomation -- Name
		div += '<div class="form-group">';
		div += '<label class="col-sm-1 control-label">{{Name}}</label>';
		div += '<div class="col-sm-2">';
		div += '<span class="bindingAttr btn btn-sm btn-default rename cursor" data-l1key="name"></span>';
		div += '</div>';

        if (isset(_binding.nsr_slots)) {
            //** Required slots
            div += '<label class="col-sm-2 control-label">{{Required Slots}}</label>';
            div += '<div class="col-sm-4">';
            for(x in _binding.nsr_slots){
                div += '<span class="label label-primary" style="margin-right: 10px;font-size: 0.9em;">'+_binding.nsr_slots[x]+'</span>';
            }
            div += '</div>';
        }

    	//** Basic infomation -- Status
    	div += '<label class="col-sm-1 control-label">{{Status}}</label>';
    	div += '<div class="col-sm-2">';
    	if(!_binding.isActive){
    		div += '<span>';
    		div += '<input style="display:none;" class="bindingAttr isActivated" type="checkbox" id="isActivated_'+ random +'" data-l1key="enable">';
            div += '<label for="isActivated_'+ random +'">';
            div += '<a class="btn btn-success btn-sm">Enable</a>';
            div += '</span>';
	    div += '</div>';
    	}else{
    		div += '<span>';
    		div += '<input style="display:none;" class="bindingAttr isActivated" type="checkbox" id="isActivated_'+ random +'" data-l1key="enable" checked>';
            div += '<label for="isActivated_'+ random +'">';
            div += '<a class="btn btn-danger btn-sm"><i class="fa fa-times"></i> {{Disable}}</a>';
            div += '</span>';
	    div += '</div>';
    	}
    	div += '</div>';
    
    div += '<hr/>';

    //** Condition Section
    div += '<div class="section-title"><strong>{{Condition(s)}}</strong>';
    div += '<a class="btn btn-xs btn-success bt_addCondition" style="margin-left: 15px;" data-toggle="tooltip" data-placement="top" title="Multiple conditions will be in \'AND\' relation"><i class="fa fa-plus-circle"></i> {{Add Condition}}</a>';
    //div += '<span style="margin-left: 20px;"><code>(Multiple conditions will be in \'AND\' relation)</code></span>';
    div += '</div>';
    div += '<div class="div_condition"></div>';

    div += '<hr/>';

    //** Action Section
    div += '<div class="section-title"><strong>{{Action(s)}}</strong>';
    div += '<a class="btn btn-success btn-xs bt_addAction" style="margin-left: 15px;" '+
            'data-toggle="tooltip" data-placement="top" title="Multiple actions will be executed in order from top to down"><i class="fa fa-plus-circle"></i> {{Add Action}}</a>';
    
    //div += '<span style="margin-left: 20px;"><code>(Multiple actions will be executed in order from top to down)</code></span>';
    
    div += '</div>';
    div += '<div class="div_action"></div>';

    div += '<hr/>';

    //** Feedback Section
    div += '<div class="section-title" ><strong>{{Feedback Tts}}</strong>';
    div += '<a class="btn btn-primary btn-xs playFeedback" style="margin-left: 15px;"><i class="fa fa-play"></i> {{Test Play}}</a>';
    //div += '<a class="btn btn-default btn-xs" style="margin-left: 15px;"><i class="fa fa-times"></i> {{Enable}}</a>';
    //div += '<span style="margin-left: 20px;"><code>(Use \'{#}\' to add dynamic variable, then setup follwing the same order)</code></span>';
    div += '</div>';

    div += '<div class="div_feedback form-group">'

    div += '<div class="col-sm-6">';
    div += '<textarea class="bindingAttr form-control ta_autosize feedbackSpeech"'+
    		'data-l1key="tts" data-l2key="text" rows="2"'+
    		'style="resize: none; overflow: hidden; word-wrap: break-word; height: 30px; font-size:12px;"'+
    		'placeholder="Speech text" data-toggle="tooltip" '+
            'data-placement="bottom" title="Use \'{#}\' to add dynamic variable, then setup variables follwing the same order">';
   	div += '</textarea>';
    div += '</div>';

    div += '<div class="col-sm-6 div_infoCmd"></div>';

    div += '</div>';

    div += '</form>';
    div += '</div>';
    div += '</div>';
    div += '</div>';
    div += '</div>';

    $('#div_bindings').append(div);
    $('#div_bindings .binding:last').setValues(_binding, '.bindingAttr');
    // Add all the conditions
    if (is_array(_binding.condition)) {
        for (var i in _binding.condition) {
            addCondition(_binding.condition[i], $('#div_bindings .binding:last'));
        }
    } else {
        if ($.trim(_binding.condition) != '') {
            addCondition(_binding.condition[i], $('#div_bindings .binding:last'));
        }
    }
    // Add all the actions
    if (is_array(_binding.action)) {
        for (var i in _binding.action) {
            addAction(_binding.action[i], $('#div_bindings .binding:last'));
        }
    } else {
        if ($.trim(_binding.action) != '') {
            addAction(_binding.action, $('#div_bindings .binding:last'));
        }
    }

    if(!isset(_binding.tts)){

        _binding.tts= { "vars": {}};
    }

    // Add all the tts infoCmds
    if (is_array(_binding.tts.vars)) {
        for (var i in _binding.tts.vars) {
            addInfoCmd(_binding.tts.vars[i], $('#div_bindings .binding:last'));
        }
    } else {
        if ($.trim(_binding.tts.vars) != '') {
            addInfoCmd(_binding.tts.vars, $('#div_bindings .binding:last'));
        }
    }

    $('[data-toggle="tooltip"]').tooltip();

    $('.collapse').collapse();
    $("#div_bindings .binding:last .div_condition").sortable({axis: "y", cursor: "move", items: ".condition", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
    $("#div_bindings .binding:last .div_action").sortable({axis: "y", cursor: "move", items: ".action", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
    $("#div_bindings .binding:last .div_infoCmd").sortable({axis: "y", cursor: "move", items: ".infoCmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
}

//-- This function is used to add action/condition to the binding configuration
function addAction(_action, _el) {
    if (!isset(_action)) {
        _action = {};
    }
    if (!isset(_action.options)) {
        _action.options = {};
    }

    var div = '<div class="action">';
    div += '<div class="form-group ">';

    div += '<div class="col-sm-6">';
    div += '<div class="input-group">';
    div += '<span class="input-group-btn">';
    div += '<a class="btn btn-default bt_removeAction btn-sm" data-type="action"><i class="fa fa-minus-circle"></i></a>';
    div += '</span>';
    div += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="action" />';
    div += '<span class="input-group-btn">';
    div += '<a class="btn btn-default btn-sm listAction" data-type="action" title="{{Select an action}}"><i class="fa fa-tasks"></i></a>';
    div += '<a class="btn btn-default btn-sm listCmdAction" data-type="action"><i class="fa fa-list-alt"></i></a>';
    div += '</span>';
    div += '</div>';
    div += '</div>';

    var actionOption_id = uniqId();
    div += '<div class="col-sm-6">';
    div += '<span class="actionOptions" id="'+actionOption_id+'"></span>';

    div += '<span class="slotsOptions">';

    if (isset(_action.options.HT) && isset(_action.options.LT)) {

        div += '<span class="input-group input-group-sm">';
        div += '<span class="input-group-addon" style="width: 100px">0% => </span>';
        div += '<input class="expressionAttr form-control input-sm" data-l1key="options" data-l2key="LT">';

        div += '<span class="input-group-addon" style="width: 100px">100% => </span>';
        div += '<input class="expressionAttr form-control input-sm" data-l1key="options" data-l2key="HT">';
        div += '<span>'

    }

    div += '</span>';

    div += '</div>';

    // div += '<div class="col-sm-1">';
    // div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Enable this action}}" />';
    // div += '<a>Enable</a>';
    // div += '</div>';

    div += '</div>';

    if (isset(_el)) {
        _el.find('.div_action').append(div);
        _el.find('.action:last').setValues(_action, '.expressionAttr');

    } else {
        $('#div_action').append(div);
        $('#div_action .action:last').setValues(_action, '.expressionAttr');
    }

    actionOptions.push({
        expression : init(_action.cmd, ''),
        options : _action.options,
        id : actionOption_id
    });
}

//--This function is used to add condition
function addCondition(_condition, _el){
	if (!isset(_condition)) {
        _condition = {};
    }
    if (!isset(_condition.options)) {
        _condition.options = {};
    }

    var div = '<div class="condition">';
    div += '<div class="form-group ">';

    div += '<div class="col-sm-6">';
    div += '<div class="input-group input-group-sm">';

    // remove button 
    div += '<span class="input-group-btn">';
    div += '<a class="btn btn-default bt_removeCondition btn-sm" data-type="condition"><i class="fa fa-minus-circle"></i></a>';
    div += '</span>';

    // IF
    div += '<span class="input-group-addon" style="width: 100px" >ONLY IF</span>';

	var selectSlotsId = uniqId();
    // pre operante

    div += '<select class="conditionAttr form-control input-sm" data-l1key="pre" id="'+selectSlotsId+'" style="-webkit-appearance: none; border-radius: 0;">';
    div += '</select>';

    div += '</div></div>'

    div += '<div class="col-sm-6">';
    div += '<div class="input-group input-group-sm" style="width: 100%;">';
    // EQUAL TO =
    div += '<span class="conditionAttr input-group-addon" data-l1key="relation" style="width: 100px" >=</span>';

    // aft operante
    div += '<input class="conditionAttr form-control input-sm" data-l1key="aft">';
    div += '</div>';
    div += '</div>';

    if (isset(_el)) {
        _el.find('.div_condition').append(div);
        
        displaySlots(selectSlotsId);
        _el.find('.condition:last').setValues(_condition, '.conditionAttr');

    } else {
        $('#div_condition').append(div);

        displaySlots(selectSlotsId);
        $('#div_condition .condition:last').setValues(_condition, '.conditionAttr');
    } 
}

//-- This is function used to attach new info command
function addInfoCmd(_infoCmd, _el){

    var div = '<div class="input-group input-group-sm infoCmd" style="width: 100%">';
    div += '<span class="input-group-btn ">';
    div += '<a class="btn btn-default btn-sm" style="width: 100px" data-toggle="tooltip" data-placement="top" title="Drag to change order"><span class="glyphicon glyphicon-sort" aria-hidden="true"></span>'+"&nbsp;&nbsp;&nbsp;"+'Variable</a></span>'
    div += '<input value="" class="bindingAttr form-control input-sm" data-l1key="tts" data-l2key="vars" >';
    div += '<span class="input-group-btn">';
    div += '    <button class="btn btn-default listInfoCmd" type="button" title="{{Select a value}}">';
    div += '    <i class="fa fa-list-alt"></i>';
    div += '    </button>';
    div += '</span>';

    // div += '<span class="input-group-addon">';
    // div += '<i class="fa fa-chevron-right"></i>';
    // div += '</span>';
    div += '</div>';

    if ($.isEmptyObject(_infoCmd)) {
        _el.find('.div_infoCmd').append(div);
        _el.find('.infoCmd:last').find('.bindingAttr').val('');
    }else{
        _el.find('.div_infoCmd').append(div);
        _el.find('.infoCmd:last').find('.bindingAttr').val(_infoCmd);
    }
}

function addCmdToTable(_cmd) {

    var tr = '<div class="cmd" data-cmd_id="' + init(_cmd.id) + '" style="float:left; margin-right: 10px;margin-bottom: 10px;">';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<span class="cmdAttr" data-l1key="logicalId" style="display:none;"></span>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" disabled="disabled" >';
    tr += '</div>';

    $('#table_cmd').append(tr);
    $('#table_cmd div:last').setValues(_cmd, '.cmdAttr');

    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd div:last'), init(_cmd.subType));
}


function displaySlots(_selectSlotsId){
    jeedom.eqLogic.getCmd({
        id: $('#intentId').val(),
        async: false,
        success: function (cmds) {
            var result = '';
            for (var i in cmds) {
                if (cmds[i].type == 'info') {
                    result += '<option value="' + cmds[i].id + '" data-type="' + cmds[i].type + '"  data-subType="' + cmds[i].subType + '" >#[' + cmds[i].name + ']#</option>';
                }
            }
            var select = $('#'+_selectSlotsId);
            select.empty();
            var selectCmd = '<option value="-1">Select a Slot &#8681;</option>';
            selectCmd += result;
            select.append(selectCmd);
        }
    });
}

function displayValueMap(_el){

    var span = '<span class="input-group input-group-sm">';
    span += '<span class="input-group-addon" style="width: 100px">0% => </span>';
    span += '<input value="0" class="expressionAttr form-control input-sm" data-l1key="options" data-l2key="LT">';

    span += '<span class="input-group-addon" style="width: 100px">100% => </span>';
    span += '<input value="100" class="expressionAttr form-control input-sm" data-l1key="options" data-l2key="HT">';
    span += '<span>'

    _el.append(span);
}
