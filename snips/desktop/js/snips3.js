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

////--------------------Events Binding--------------------////

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

//--This is the function used to add condition for binding
$("#div_bindings").off('click','.bt_addCondition').on('click','.bt_addCondition',  function () {
    //addAction({}, 'condition', '{{Condition}}', $(this).closest('.binding'));
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
                type: "POST", // méthode de transmission des données au fichier php
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
        binding.Condition = $(this).find('.Condition').getValues('.expressionAttr');
        binding.Action = $(this).find('.Action').getValues('.expressionAttr');
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
    		div += '<input style="display:none;" class="isActivated" type="checkbox" id="isActivated_'+ random +'" data-l1key="emable">';
            div += '<label for="isActivated_'+ random +'">';
            div += '<a class="btn btn-success btn-sm">Enable</a>';
            div += '</span>';
	    	div += '</div>';
    	}else{
    		div += '<span>';
    		div += '<input style="display:none;" class="isActivated" type="checkbox" id="isActivated_'+ random +'" data-l1key="emable" checked>';
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
    div += '<div><span>{{Condition(s)}}</span></div>';
    div += '<div class="div_Condition"></div>';

    div += '<hr/>';

    //** Action Section
    div += '<div><span>{{Action(s)}}</span></div>';
    div += '<div class="div_Action"></div>';

    div += '</form>';
    div += '</div>';
    div += '</div>';
    div += '</div>';
    div += '</div>';

    $('#div_bindings').append(div);
    $('#div_bindings .binding:last').setValues(_binding, '.bindingAttr');

    if (is_array(_binding.Condition)) {
        for (var i in _binding.Condition) {
            addCondition(_binding.Condition[i], $('#div_bindings .binding:last'));
        }
    } else {
        if ($.trim(_binding.Condition) != '') {
            addCondition(_binding.Condition[i], $('#div_bindings .binding:last'));
        }
    }

    if (is_array(_binding.Action)) {
        for (var i in _binding.Action) {
            addAction(_binding.Action[i], $('#div_bindings .binding:last'));
        }
    } else {
        if ($.trim(_binding.Action) != '') {
            addAction(_binding.Action, $('#div_bindings .binding:last'));
        }
    }
    $('.collapse').collapse();
    $("#div_bindings .binding:last .div_Condition").sortable({axis: "y", cursor: "move", items: ".Condition", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
    $("#div_bindings .binding:last .div_Action").sortable({axis: "y", cursor: "move", items: ".Action", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
    
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
        _el.find('.div_Action').append(div);
        _el.find('.action:last').setValues(_action, '.expressionAttr');

    } else {
        $('#div_Action').append(div);
        $('#div_Action .action:last').setValues(_action, '.expressionAttr');
    }

    actionOptions.push({
        expression : init(action.cmd, ''),
        options : action.options,
        id : actionOption_id
    });
}

//--This function is used to add condition
function addCondition(){

}
