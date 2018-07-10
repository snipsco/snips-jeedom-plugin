<?php
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

//Find Plugin
$plugin = plugin::byId('snips');
//Send plugin object to js
sendVarToJS('eqType', $plugin->getId()); //Id: snips
$eqLogics = eqLogic::byType($plugin->getId()); //Type: snips
?>



<!-- Side bar of equipments -->
<div class="row row-overflow">
    <div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                  <?php
                  foreach ($eqLogics as $eqLogic) {
                      $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
                      echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '" style="' . $opacity .'"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
                  }
                  ?>
           </ul>
       </div>
   </div>


  <!-- Name of Plugin and the section name-->
   <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
    <legend>{{Snips Voice Assistant}}</legend>
  <legend><i class="fa fa-cog"></i>  {{Manage}}</legend>

  <!--Management of plugin-->
  <div class="eqLogicThumbnailContainer">

      <div class="cursor eqLogicAction reload" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <i class="fa fa-refresh" style="font-size : 6em;color:#5cb85c;"></i>
        <br>
        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#5cb85c">{{Reload}}</span>
      </div>

      <div class="cursor eqLogicAction removeAll" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <i class="fa divers-slightly" style="font-size : 6em;color:#c9302c;"></i>
        <br>

        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#c9302c">{{Remove All}}</span>
      </div>

      
      <div class="cursor eqLogicAction resetMqtt" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <i class="fa fa-keyboard-o" style="font-size : 6em;color:#ec971f;"></i>
        <br>

        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#ec971f">{{Reset MQTT}}</span>
      </div>


      <div class="cursor eqLogicAction resetSlotsCmd" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <i class="fa techno-charging" style="font-size : 6em;color:#9b59b6;"></i>
        <br>

        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#9b59b6">{{Reset SlotCmd}}</span>
      </div>


      <div class="cursor eqLogicAction" data-action="gotoPluginConf" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;">
        <i class="fa fa-wrench" style="font-size : 6em;color:#337ab7;"></i>
        <br>

        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#337ab7">{{Configuration}}</span>
      </div>


  </div>
  <legend><i class="fa fa-bolt"></i> {{All Intents}}</legend>

  <!--Management of All the Intents (Objects)-->
  <div class="eqLogicThumbnailContainer">

    <?php
      foreach ($eqLogics as $eqLogic) {
      	$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
      	echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="text-align: center; background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
      	echo '<img src="' . $plugin->getPathImgIcon() . '" height="105" width="95" />';
      	echo "<br>";
      	echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;">' . $eqLogic->getHumanName(true, true) . '</span>';
      	echo '</div>';
      }
?>
</div>
</div>

<div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
  <a class="btn btn-success eqLogicAction pull-right" id="saveAll" data-action="save"><i class="fa fa-check-circle"></i> {{Save}}</a>
  <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> {{Pre-Configuration}}</a>
  <ul class="nav nav-tabs" role="tablist">



    <li role="presentation"><a class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>

  </ul>


  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>


    <form class="form-horizontal">
        <fieldset style="width: 1024px; margin:auto;">
          <legend>{{Intent Configuration}}</legend>
            <div class="form-group">
                <label class="col-sm-1 control-label">{{Name}}</label>
                <div class="col-sm-3">
                    <input type="text" class="eqLogicAttr form-control" id="intentId" data-l1key="id" style="display : none;" />
                    <input type="text" class="eqLogicAttr form-control input-sm" id="intentName" data-l1key="name" disabled="disabled" "/>
                </div>


                <label class="col-sm-1 control-label">{{Slots}}</label>
                <div class="col-sm-6">
                    <div id="table_cmd"> </div>
                </div>

                

            </div>
            
            <div class="form-group">

                <label class="col-sm-1 control-label">{{Status}}</label>
                <div class="col-sm-3">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                      <label class="btn btn-primary btn-sm">
                        <input type="checkbox" class="eqLogicAttr" data-l1key="isEnable"> Enable
                      </label>
                    </div>
                </div>

                <label class="col-sm-1 control-label">{{Reaction}}</label>
                <div class="col-sm-6">

                <div class="btn-group btn-group-toggle" data-toggle="buttons">

                  <label class="btn btn-default btn-sm active" style="width: 150px;">
                    <input type="radio" class="eqLogicAttr" data-l1key="configuration" data-l2key="isSnipsConfig"> Snips Binding
                  </label>
                  
                  <label class="btn btn-default btn-sm" style="width: 150px;">
                    <input type="radio" class="eqLogicAttr" data-l1key="configuration" data-l2key="isInteraction"> JeeDom Interaction
                  </label>

                </div>

                </div>
            </div>

        <legend>{{Action Binding}}</legend>

        <div class="panel-heading btn btn-default" id="bt_addNewBinding" style="width: 100%; border: 1.5px dashed #ddd; box-shadow: 0 1px 1px rgba(0,0,0,.05); background-color:#fff;"><i class="fa fa-plus-circle"></i> {{Attach a new binding}}</div>
        <!-- <a class="btn btn-success btn-sm cmdAction pull-left" id="bt_addNewBinding" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Attach a new binding}}</a><br/><br/> -->

        <div id="div_bindings" style="padding-top: 10px;"></div>
</fieldset>
</form>
</div>

</div>

</div>
</div>

<?php include_file('desktop', 'snips', 'js', 'snips');?>
<?php include_file('core', 'plugin.template', 'js');?>
<?php include_file('desktop', 'snips', 'css', 'snips'); ?>
<!--Passing necessary data to javascript-->
<script>
  var _snips_intents = <?php echo snips::getIntents();?>
</script>


