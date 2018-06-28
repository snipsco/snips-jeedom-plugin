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

MODE_LIST = null;
INTENT = null; 

////--------------------Events Binding--------------------////
//--This is used to import all the available slots when the page is ready.
$(document).on('change', '#intentName', function() {
	$('#availableSlots').empty();

	INTENT = $('#intentName').val(); 
});

//--This is the function used to hack system command select modal
$(document).on('change', '#table_mod_insertCmdValue_valueEqLogicToMessage .mod_insertCmdValue_object select', function() {
	console.log('input object changed captured, value is:'+$(this).find("option:selected").text());

	if($(this).find("option:selected").text() == 'snips-intents'){
		$('#table_mod_insertCmdValue_valueEqLogicToMessage').find('thead').html(
		'<tr>'+
            '<th style="width: 150px;">Object</th>'+
            '<th style="width: 150px;">Intents</th>'+
            '<th style="width: 150px;">Slots</th>'+
        '</tr>'
			);

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
    var type = $(this).attr('data-type');
    var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
    jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
        el.value(result.human);
        jeedom.cmd.displayActionOption(el.value(), '', function (html) {
            el.closest('.' + type).find('.actionOptions').html(html);
            taAutosize();
        });
    });
});

//--This is the function used to select equipment action command
$("body").off('click','.listAction').on( 'click','.listAction',function () {
	var type = $(this).attr('data-type');
	var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
	jeedom.getSelectActionModal({}, function (result) {
		el.value(result.human);
		jeedom.cmd.displayActionOption(el.value(), '', function (html) {
			el.closest('.' + type).find('.actionOptions').html(html);
			taAutosize();
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

//-- Not clear yet
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
        btn.text('Disable');
    }else{
    	btn.removeClass('btn-danger');
        btn.addClass('btn-success');
        btn.text('Enable');
    }
});

//-- Sort all the bindings
$("#div_bindings").sortable({axis: "y", cursor: "move", items: ".binding", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});



////--------------------Ajax setup--------------------////

//--This is the function used to reload all the skills from installed assistant
$('.reload').on('click', function () {
	$('#div_alert').showAlert({message: 'Reloading all the skills', level: 'warning'});
	$.ajax({
                type: "POST", // method to transmit request
                url: "plugins/snips/core/ajax/snips.ajax.php", 
                data: {
                    action: "reload",
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
                    $('#div_alert').showAlert({message: 'Successfully reloaded!', level: 'success'});
                    location.reload();
                }
            });
});

//--This function is used to remove all the loaded skills
$('.removeAll').on('click', function () {
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
});

//--This function is used to preview the feedback speech
$("#div_bindings").delegate(".playFeedback",'click', function(){

	//console.log('Play feedback');
	$.ajax({
                type: "POST", // méthode de transmission des données au fichier php
                url: "plugins/snips/core/ajax/snips.ajax.php", 
                data: {
                    action: "playFeedback",
                    text: $(this).closest('div').find('textarea').val(),
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


////--------------------Syetem function rewrite--------------------////
function printEqLogic(_eqLogic) {
    $('#div_bindings').empty();
    MODE_LIST = [];
    if (isset(_eqLogic.configuration) && isset(_eqLogic.configuration.bindings)) {
    	actionOptions = []
	    for (var i in _eqLogic.configuration.bindings) {
	        MODE_LIST.push(_eqLogic.configuration.bindings[i].name)
	    }
	    for (var i in _eqLogic.configuration.bindings) {
	        addBinding(_eqLogic.configuration.bindings[i],false);
	    }
	    MODE_LIST = null
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
    if (!isset(_eqLogic.configuration)) {
    	_eqLogic.configuration = {};
    }
    _eqLogic.configuration.bindings = [];
    $('#div_bindings .binding').each(function () {
        var binding = $(this).getValues('.bindingAttr')[0];
        binding.condition = $(this).find('.condition').getValues('.conditionAttr');
        binding.action = $(this).find('.action').getValues('.expressionAttr');
        _eqLogic.configuration.bindings.push(binding);
    });
    return _eqLogic;
}

////--------------------Snips Functions--------------------////

//
function addBinding(_binding) {
    if (init(_binding.name) == '') {
        return;
    }

    if (init(_binding.enable) == '') {
        _binding.enable = 1;
    }

    var random = Math.floor((Math.random() * 1000000) + 1);

    var div = '<div class="binding panel panel-default">';

    //** Name section
    div += '<div class="panel-heading">';
    div += '<h4 class="panel-title">';
    div += '<a data-toggle="collapse" data-parent="#div_bindings" href="#collapse' + random + '">';
    div += '<span class="name">' + _binding.name + '</span>';
    div += '</a>';
    div += '</h4>';
    div += '</div>';


    //** Content section
    div += '<div id="collapse' + random + '" class="panel-collapse collapse in">';
    div += '<div class="panel-body">';
    div += '<div class="well">';

    div += '<form class="form-horizontal" role="form">';

		//** Basic infomation -- Name
		div += '<div class="form-group">';
		div += '<label class="col-sm-1 control-label">{{Name:}}</label>';
		div += '<div class="col-sm-2">';
		div += '<span class="bindingAttr btn btn-sm btn-default rename cursor" data-l1key="name" style="font-size : 1em;" ></span>';
		div += '</div>';

    	//** Basic infomation -- Status
    	div += '<label class="col-sm-1 control-label">{{Status:}}</label>';
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
            div += '<a class="btn btn-danger btn-sm">Disable</a>';
            div += '</span>';
	    	div += '</div>';
    	}
    	
    	//** Managing operations 
    	div += '<div class="col-sm-4">';
    	div += '<label class="col-sm-1 control-label">{{Manage:}}</label>';
    	div += '<div class="btn-group pull-right" role="group">';
    	div += '<a class="btn btn-sm btn-primary bt_removeBinding"><i class="fa fa-minus-circle"></i> {{Delete}}</a>';
    	div += '<a class="btn btn-sm btn-success bt_addCondition"><i class="fa fa-plus-circle"></i> {{Add Condition}}</a>';
    	div += '<a class="btn btn-danger btn-sm bt_addAction"><i class="fa fa-plus-circle"></i> {{Add Action}}</a>';
    	div += '<a class="btn btn-sm btn-default bt_duplicateBinding"><i class="fa fa-files-o"></i> {{Duplicate}}</a>';
    	div += '</div>';
    	div += '</div>';

	div += '</div>';

	div += '<hr/>';

    //** Condition Section
    div += '<div><strong>{{Condition(s)}}</strong></div>';
    div += '<div class="div_condition"></div>';

    div += '<hr/>';

    //** Action Section
    div += '<div><strong>{{Action(s)}}</strong></div>';
    div += '<div class="div_action"></div>';

    div += '<hr/>';

    //** Action Section
    div += '<div><strong>{{Feedback Tts}}</strong></div>';
    div += '<div class="div_feedback input-group">';

    div += '<textarea class="tags bindingAttr form-control ta_autosize"'+
    		'data-l1key="tts" rows="1"'+
    		'style="resize: none; overflow: hidden; word-wrap: break-word; height: 30px;"'+
    		'placeholder="Speech text">';
   	div += '</textarea>';

   	div += '<span class="input-group-btn"><a class="btn btn-default btn-sm playFeedback"><i class="fa fa-play"></i></a></span>';

    div += '</div>';


    div += '</form>';
    div += '</div>';
    div += '</div>';
    div += '</div>';
    div += '</div>';

    $('#div_bindings').append(div);
    $('#div_bindings .binding:last').setValues(_binding, '.bindingAttr');

    if (is_array(_binding.condition)) {
        for (var i in _binding.condition) {
            addCondition(_binding.condition[i], $('#div_bindings .binding:last'));
        }
    } else {
        if ($.trim(_binding.condition) != '') {
            addCondition(_binding.condition[i], $('#div_bindings .binding:last'));
        }
    }

    if (is_array(_binding.action)) {
        for (var i in _binding.action) {
            addAction(_binding.action[i], $('#div_bindings .binding:last'));
        }
    } else {
        if ($.trim(_binding.action) != '') {
            addAction(_binding.action, $('#div_bindings .binding:last'));
        }
    }
    $('.collapse').collapse();
    $("#div_bindings .binding:last .div_condition").sortable({axis: "y", cursor: "move", items: ".condition", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
    $("#div_bindings .binding:last .div_action").sortable({axis: "y", cursor: "move", items: ".action", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
    
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

    div += '<div class="col-sm-4">';
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
    div += '<div class="col-sm-5 actionOptions" id="'+actionOption_id+'">';
    div += '</div>';

    div += '<div class="col-sm-1">';
    div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Enable this action}}" />';
    div += '<a>Enable</a>';
    div += '</div>';

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

    div += '<div class="col-sm-5">';
    div += '<div class="input-group input-group-sm">';

    // remove button 
    div += '<span class="input-group-btn">';
    div += '<a class="btn btn-default bt_removeCondition btn-sm" data-type="condition"><i class="fa fa-minus-circle"></i></a>';
    div += '</span>';

    // IF
    div += '<span class="input-group-addon">If</span>';

    // pre operante
    div += '<select class="conditionAttr form-control input-sm" data-l1key="pre">';
    div += '<option value="0">Select a Slot &#8681;</option>';

		for(x in _snips_intents[INTENT]){
			div += '<option value="'+_snips_intents[INTENT][x]+'">'+_snips_intents[INTENT][x]+'</option>';
		}

    div += '</select>';

    // EQUAL TO =
    div += '<span class="conditionAttr input-group-addon" data-l1key="relation">=</span>';

    // aft operante
    div += '<input class="conditionAttr form-control" data-l1key="aft">';
    div += '</div>';
    div += '</div>';



    // div += '<div class="col-sm-4">';
    // div += '<div class="input-group">';
    // div += '<span class="input-group-btn">';
    // div += '<a class="btn btn-default bt_removeAction btn-sm" data-type="action"><i class="fa fa-minus-circle"></i></a>';
    // div += '</span>';
    // div += '<input class="expressionAttr form-control input-sm" data-l1key="cmd" data-type="action" />';
    // div += '<span class="input-group-btn">';
    // div += '<a class="btn btn-default btn-sm listAction" data-type="action" title="{{Select an action}}"><i class="fa fa-tasks"></i></a>';
    // div += '<a class="btn btn-default btn-sm listCmdAction" data-type="action"><i class="fa fa-list-alt"></i></a>';
    // div += '</span>';
    // div += '</div>';
    // div += '</div>';

    // var actionOption_id = uniqId();
    // div += '<div class="col-sm-5 actionOptions" id="'+actionOption_id+'">';
    // div += '</div>';

    // div += '<div class="col-sm-1">';
    // div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Enable this action}}" />';
    // div += '<a>Enable</a>';
    // div += '</div>';

    // div += '</div>';

    if (isset(_el)) {
        _el.find('.div_condition').append(div);
        _el.find('.condition:last').setValues(_condition, '.conditionAttr');

    } else {
        $('#div_condition').append(div);
        $('#div_condition .condition:last').setValues(_condition, '.conditionAttr');
    }

    // actionOptions.push({
    //     expression : init(_action.cmd, ''),
    //     options : _action.options,
    //     id : actionOption_id
    // });
}

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }

    var tr = '<div class="cmd" data-cmd_id="' + init(_cmd.id) + '" style="float:left;">';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<span class="cmdAttr form-control input-sm" data-l1key="name" style="font-size : 1em;">';
    tr += '</div>';

    $('#table_cmd').append(tr);
    $('#table_cmd div:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init('info'));
    }
    jeedom.cmd.changeType($('#table_cmd div:last'), init('string'));
}
