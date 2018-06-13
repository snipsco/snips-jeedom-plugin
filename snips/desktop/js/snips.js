
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

////////////////////////This is the button used to choose action command for a intent  
$("#table_cmd").delegate(".findAction", 'click', function () {
    var el = $(this);
    jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
        var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.data('input') + ']');
        calcul.value(result.human);
        //calcul.atCaret('insert',result.human)
    });
});

////////////////////////This is the button used to choose info command for a slots 
$("#table_cmd").delegate(".findSlotInfo", 'click', function () {
    var el = $(this);
    jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
        var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.data('input') + ']');
        calcul.value(result.human);
        //calcul.atCaret('insert',result.human)
    });
});

////////////////////////This is the checkbox used to specify slots type
$(document).on('change', '.isValue', function(){
    var checkbox = $(this);
    console.log('isValue function has been executed with :'+checkbox.attr('id'));

    if(checkbox.is(":checked")){
        var input = checkbox.closest('tr').find('input.invalue[id=' + checkbox.attr('id') + '_bak]');
        input.attr('placeholder',"Value");
        input.attr('readonly',"readonly");
        console.log('checked '+input.attr('placeholder'));

    }else{
        var input = checkbox.closest('tr').find("input.invalue");
        input.attr('placeholder',"Location");
        input.removeAttr("readonly");
        console.log('unchecked '+input.attr('placeholder'));

    }
});


////////////////////////This is the function used to add an intent-command mapping
$("#addIntent").on('click', function(event) { console.log("Insert function"); addNewIntent(); });

////////////////////////This is the function used to sort up command table
$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

////////////////////////This is the function will be called by JeeDom system when display exist commands
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '" name="cmd_' + init(_cmd.id) + '">';
    tr += '<td style="vertical-align:middle;">';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 150px;" readonly="readonly">';
    tr += '</td>';
    ////////////
    tr += '<td>';
    tr += '<div style="float: left;">           </div>';
    tr += '</td>';
    ////////////
    tr += '<td style="vertical-align:middle;">';
    tr += '<div><div style="float: left;"><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="command" style="width : 300px;" placeholder="{{Match an action or script}}"></div>';
    tr += '<div style="align: right;"><a class="btn btn-default btn-sm findAction" data-input="command" style="align: right;"><i class="fa fa-list-alt "></i></a></div></div>';
    tr += '</td>';
    ////////////

    tr += '<td style="vertical-align:middle;">';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-primary btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
    }
    tr += '<a class="btn btn-danger btn-xs cmdAction pull-right cursor" data-action="remove"><i class="fa fa-minus-circle"></i></a> ';
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}

////////////////////////This is the function used to add a new line of intent-command mapping
function addNewIntent(_cmd) {
    if (!isset(_cmd)) {
        //var _cmd = {type: 'info', subtype: 'string', configuration: {}};
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }

    //Generate an unique id for new row, which is used for locating this new row
    var temp_id = Math.round(Math.random() * 10000000);
    var ids = $(document).find('tr').attr('id');

    while($.inArray(temp_id, ids) + 1){ 
        temp_id = Math.round(Math.random() * 10000000);
    }

    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '" name="' + init(_cmd.id) + '" id="' + temp_id + '">';
    tr += '<td style="vertical-align:middle;">';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<div style="float: left;">'+availableIntents(_snips_intents, temp_id)+'</div>';
    tr += '</td>';

    ////////////
    tr += '<td style="vertical-align:middle;">';
    tr += '<div style="float: left;" id="slots_'+temp_id+'">            </div>';
    tr += '</td>';
    ////////////

    tr += '<td style="vertical-align:middle;">';
    tr += '<div><div style="float: left;"><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="command" style="width : 300px;" placeholder="{{Match an action or script}}"></div>';
    tr += '<div style="align: right;"><a class="btn btn-default btn-sm findAction" data-input="command" style="align: right;"><i class="fa fa-list-alt "></i></a></div></div>';
    tr += '</td>';
    ////////////

    tr += '<td style="vertical-align:middle;">';
    tr += '<a class="btn btn-danger btn-xs cmdAction pull-right cursor" data-action="remove"><i class="fa fa-minus-circle"></i></a> ';

    tr += '<input class="cmdAttr form-control input-sm" data-l1key="type" type="hidden" value="info" />';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="subtype" type="hidden" value="string" />';
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));

}

////////////////////////This is the function used to display a select box with all the available intents 
function availableIntents(_snips_intents, parent_tr_id){
    var op = '<select class="cmdAttr form-control" onchange="updateSlots('+ parent_tr_id +', _snips_intents)" id="sel_'+parent_tr_id+'" parentname="' +parent_tr_id+ '" data-l1key="configuration" data-l2key="intent" style="width : 150px;">';
    
    var intents = _snips_intents;

    op += '<option value="0">Select an Intent &#8681;</option>';

    for(x in intents){
        op += '<option value="'+ x +'">'+ x +'</option>';
    }
    op += '</select>';

    //$("sel_" + parent_tr_id).on('change', updateSlots(parent_tr_id ,_snips_intents) );

    //document.getElementById("sel_" + parent_tr_id ).onchange = updateSlots(parent_tr_id ,_snips_intents)；

    return op;
}

////////////////////////This is the function used to update related slots when the intent has been changed
function updateSlots(parent_tr_id, _snips_intents){

    $('#slots_'+parent_tr_id).empty();

    console.log("update function has been executed!");

    //var parent_tr_id = event.target.getAttribute("parentname");

    console.log('With parent id :'+ parent_tr_id);

    var intent = $('#sel_'+parent_tr_id + ' option:selected').val();

    //var intent = document.getElementById("sel_" + parent_tr_id )

    console.log('Selected item is :'+ intent);

    var intents = _snips_intents;

    console.log('Slots detected: '+ intents[intent]);

    var div = '';
    for(slot in intents[intent]){

        div += '<div><input readonly="readonly" class="cmdAttr form-control input-sm" style="width: 100px;  display:inline;" value="'+intents[intent][slot]+'">';
        div += '<input class="cmdAttr form-control input-sm invalue" id="che_'+parent_tr_id+'_'+intent+'_'+intents[intent][slot]+'_bak" style="width: 100px;  display:inline;" placeholder="Location">';
        div += '<div style="display: inline;vertical-align: middle;margin-left: 5px;">';
        div += '<input type="checkbox" onchange="updateSlotsConfig('+parent_tr_id+' ,\''+intent+'\', \''+intents[intent][slot]+'\')"class="cmdAttr isValue" id="che_'+parent_tr_id+'_'+intent+'_'+intents[intent][slot]+'">Value';
        div += '<div id="div_'+parent_tr_id+'_'+intent+'_'+intents[intent][slot]+'"></div></div>';
        div += '</div>';

        
    }

    $('#slots_'+parent_tr_id).append(div);
    
}

////////////////////////This is the function used to display command input box when a slot is specificed with 'value' type
function updateSlotsConfig(parent_tr_id, intent, slot){

    console.log("update config function has been executed!");

    $('#div_'+parent_tr_id+'_'+intent+'_'+slot).empty();

    var div = '';

    if ($('#che_'+parent_tr_id+'_'+intent+'_'+slot).is(":checked"))
    {
        console.log("This is a value");
        div += '<div><div style="float: left;"><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="'+slot+'" style="width : 300px;" placeholder="{{Assign this value to an info}}"></div>';
        div += '<div style="align: right;"><a class="btn btn-default btn-sm findSlotInfo" data-input="'+slot+'" style="align: right;"><i class="fa fa-list-alt "></i></a></div></div>';
    }else{
        console.log('#che_'+parent_tr_id+'_'+intent+'_'+slot+' is being detected');
        console.log("This is a location");
        div += '<div style="float: left;"><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="'+slot+'" type="hidden" value="location"></div>';
    }

    $('#div_'+parent_tr_id+'_'+intent+'_'+slot).append(div);

}




