<?php
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('snips');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
    <div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                  <?php
                  foreach ($eqLogics as $eqLogic) {
                      if ($eqLogic->getConfiguration('snipsType') == 'Intent') {
                        $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
                        echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '" style="' . $opacity .'"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
                      }
                  }
                  ?>
           </ul>
       </div>
   </div>

   <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
    <legend>{{Snips Voice Assistant}}</legend>
  <legend><i class="fa fa-cog"></i>  {{Manage}}</legend>

  <div class="eqLogicThumbnailContainer">

      <div class="cursor eqLogicAction reload" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <img src="/plugins/snips/3rdparty/icons/rocket.png" height="85" width="85" />
        <br>
        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;">{{Load Assistant}}</span>
      </div>

      <div class="cursor eqLogicAction exportConfigration" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <img src="/plugins/snips/3rdparty/icons/cloud-upload.png" height="85" width="85" />
        <br>
        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676">{{Export Binding}}</span>
      </div>

      <div class="cursor eqLogicAction importConfigration" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <img src="/plugins/snips/3rdparty/icons/cloud-download.png" height="85" width="85" />
        <br>
        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676">{{Import Binding}}</span>
      </div>
      
      <div class="cursor eqLogicAction resetMqtt" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <img src="/plugins/snips/3rdparty/icons/link.png" height="85" width="85" />
        <br>

        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676">{{Reset MQTT}}</span>
      </div>

      <div class="cursor eqLogicAction" data-action="gotoPluginConf" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;">
        <img src="/plugins/snips/3rdparty/icons/gear.png" height="85" width="85" />
        <br>

        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676">{{Configuration}}</span>
      </div>
  </div> 

  <legend><i class="fa fa-bullhorn"></i> {{Snips sites}}</legend>

  <div class="eqLogicThumbnailContainer">
    <?php
      if (!$eqLogics) {
          echo '<center><span style="color:#767676;font-size:1.2em;font-weight: bold;">Please load assistant</span></center>';
      }else{
        foreach ($eqLogics as $eqLogic) {
          if ($eqLogic->getConfiguration('snipsType') == 'TTS') {

            $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');

            echo '<div data-eqLogic_id="' . $eqLogic->getId() . '" style="text-align: center; background-color : #ffffff; height : 160px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
            echo '<img src="/plugins/snips/3rdparty/icons/speaker.png" height="85" width="85x" />';
            echo "<br>";
            echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><span class="label label-primary" style="text-shadow : none;">'.$eqLogic->getConfiguration('siteName').' :: '.config::byKey('mqttAddr', 'snips', '127.0.0.1').'</span><br><strong> Snips-TTS</strong></span>';
            echo '</div>';
          }
        }
      }
  ?>
  </div>

  <legend><i class="fa fa-bolt"></i> {{Intents (Response by Jeedom)}}</legend>
  <div class="eqLogicThumbnailContainer" >

    <?php
      if (!$eqLogics) {
          echo '<center><span style="color:#767676;font-size:1.2em;font-weight: bold;">Please load assistant</span></center>';
      }else{
        foreach ($eqLogics as $eqLogic) {
          if ($eqLogic->getConfiguration('snipsType') == 'Intent' && $eqLogic->getIsEnable()) {
            $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');

            echo '<span class="panel panel-success eqLogicDisplayCard cursor snips_intent" data-eqLogic_id="' . $eqLogic->getId() . '" style="width: 230px; height: 142px !important; margin-left : 20px; border-radius: 0px;' . $opacity . '" >';
            echo '<li class="panel-heading" style="padding: 5px 15px;list-style:none;"><strong style="font-size: 1em;">'. $eqLogic->getName() .'</strong></li>';
            echo '<li class="panel-body" style="padding: 0px;list-style:none;">';

            echo '<ul class="list-group" style="margin: 0;">';

            echo '<li class="list-group-item" style="padding: 4px 10px; border: 0px;"><span class="badge" style="background-color: #337ab7;">'.$eqLogic->getConfiguration('language').'</span>Language</li>';

            echo '<li class="list-group-item" style="padding: 4px 10px; border: 0px;"><span class="badge" style="background-color: #337ab7;">'.count($eqLogic->getConfiguration('slots')).'</span>Slots</li>';

            if ($eqLogic->getConfiguration('bindings')) {
              echo '<li class="list-group-item" style="padding: 4px 10px; border: 0px;"><span class="badge" style="background-color: #337ab7;">'.count($eqLogic->getConfiguration('bindings')).'</span>Bindings</li>';
            }else{
              echo '<li class="list-group-item" style="padding: 4px 10px; border: 0px;"><span class="badge" style="background-color: #c9302c;">0</span>Bindings</li>';
            }

            if ($eqLogic->getConfiguration('isSnipsConfig')) {
              echo '<li class="list-group-item" style="padding: 4px 10px; border: 0px;"><span class="badge" style="background-color: #337ab7;">Snips Binding</span>Reaction</li>';
            }else if($eqLogic->getConfiguration('isInteraction')){
              echo '<li class="list-group-item" style="padding: 4px 10px; border: 0px;"><span class="badge" style="background-color: #f0ad4e;">Interaction</span>Reaction</li>';
            }else{
              echo '<li class="list-group-item" style="padding: 4px 10px; border: 0px;"><span class="badge" style="background-color: #c9302c;">Not set</span>Reaction</li>';
            }
            echo '</ul>';
            echo '</li></span>';
          }
        }
      }
    ?>
  </div>

  
    <?php
      if (!$eqLogics) {
          echo '<center><span style="color:#767676;font-size:1.2em;font-weight: bold;">Please load assistant</span></center>';
      }else{
        echo '<legend><i class="fa fa-bolt"></i> {{Intents (Disabled)}}</legend>
  <div class="eqLogicThumbnailContainer" >';
        foreach ($eqLogics as $eqLogic) {
          if ($eqLogic->getConfiguration('snipsType') == 'Intent' && !$eqLogic->getIsEnable()) {
            $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
            echo '<span class="panel panel-success eqLogicDisplayCard cursor snips_intent" data-eqLogic_id="' . $eqLogic->getId() . '" style="width: 230px; height: 142px !important; margin-left : 20px; border-radius: 0px;' . $opacity . '" >';
            echo '<li class="panel-heading" style="padding: 5px 15px;"><strong style="font-size: 1em;">'. $eqLogic->getName() .'</strong></li>';
            echo '<li class="panel-body" style="padding: 0px;">';

            echo '<ul class="list-group" style="margin: 0;">';

            echo '<li class="list-group-item" style="padding: 4px 10px; border: 0px;"><span class="badge" style="background-color: #337ab7;">'.$eqLogic->getConfiguration('language').'</span>Language</li>';

            echo '<li class="list-group-item" style="padding: 4px 10px; border: 0px;"><span class="badge" style="background-color: #337ab7;">'.count($eqLogic->getConfiguration('slots')).'</span>Slots</li>';

            if ($eqLogic->getConfiguration('bindings')) {
              echo '<li class="list-group-item" style="padding: 4px 10px; border: 0px;"><span class="badge" style="background-color: #337ab7;">'.count($eqLogic->getConfiguration('bindings')).'</span>Bindings</li>';
            }else{
              echo '<li class="list-group-item" style="padding: 4px 10px; border: 0px;"><span class="badge" style="background-color: #c9302c;">0</span>Bindings</li>';
            }

            if ($eqLogic->getConfiguration('isSnipsConfig')) {
              echo '<li class="list-group-item" style="padding: 4px 10px; border: 0px;"><span class="badge" style="background-color: #337ab7;">Snips Binding</span>Reaction</li>';
            }else if($eqLogic->getConfiguration('isInteraction')){
              echo '<li class="list-group-item" style="padding: 4px 10px; border: 0px;"><span class="badge" style="background-color: #f0ad4e;">Interaction</span>Reaction</li>';
            }else{
              echo '<li class="list-group-item" style="padding: 4px 10px; border: 0px;"><span class="badge" style="background-color: #c9302c;">Not set</span>Reaction</li>';
            }
            echo '</ul>';
            echo '</li></span>';
          }
        }
      }
    ?>


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
          <legend>{{Intent}}</legend>
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
                      <label class="btn btn-success btn-sm">
                        <input type="checkbox" class="eqLogicAttr" id="isEnable" data-l1key="isEnable"><span>Enable</span>
                      </label>
                    </div>
                </div>

                <label class="col-sm-1 control-label">{{Reaction}}</label>
                <div class="col-sm-4">
                  <div class="btn-group btn-group-toggle" data-toggle="buttons">

                    <label class="btn btn-default btn-sm" style="width: 150px;">
                      <input name="reaction" type="radio" class="eqLogicAttr" data-l1key="configuration" data-l2key="isSnipsConfig"> Snips Binding
                    </label>
                    <label class="btn btn-default btn-sm" style="width: 150px;">
                      <input name="reaction" type="radio" class="eqLogicAttr" data-l1key="configuration" data-l2key="isInteraction"> JeeDom Interaction
                    </label>
                  </div>
                </div>



                <label class="col-sm-1 control-label">{{Language}}</label>
                <div class="col-sm-1">
                  <span class="label label-primary eqLogicAttr" data-l1key="configuration" data-l2key="language"style="margin-left: 10px;font-size: 0.9em;"></span>
                </div>
            </div>

        <legend>{{Action Binding}}</legend>

        <div class="panel-heading btn btn-default" id="bt_addNewBinding" style="width: 100%; border: 1.5px dashed #ddd; box-shadow: 0 1px 1px rgba(0,0,0,.05); background-color:#fff;"><i class="fa fa-plus-circle"></i> {{Attach a new binding}}</div>

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


