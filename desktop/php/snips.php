<?php
    include_file('core', 'authentification', 'php');
    if (!isConnect('admin')) {
    	throw new Exception('{{401 - Unauthorized}}');
    }
    $plugin = plugin::byId('snips');
    sendVarToJS('eqType', $plugin->getId());
    $eqLogics = eqLogic::byType($plugin->getId());
    $scenarios = scenario::all();
?>

<div class="row row-overflow">
  <div class="col-xs-12 eqLogicThumbnailDisplay">

    <legend><i class="fa fa-cog"></i> {{Manage}} | {{Snips Voice Assistant}} - 0.1.6</legend>
    <div class="eqLogicThumbnailContainer">
        <div class="cursor eqLogicAction reload">
          <img src="/plugins/snips/3rdparty/icons/rocket.png" style="width:55px!important">
          <br><br>
          <span>{{Load Assistant}}</span>
        </div>
        <div class="cursor eqLogicAction exportConfigration">
          <img src="/plugins/snips/3rdparty/icons/cloud-upload.png" style="width:55px!important">
          <br><br>
          <span>{{Export Binding}}</span>
        </div>
        <div class="cursor eqLogicAction importConfigration">
          <img src="/plugins/snips/3rdparty/icons/cloud-download.png" style="width:55px!important">
          <br><br>
          <span>{{Import Binding}}</span>
        </div>
        <div class="cursor eqLogicAction" data-action="gotoPluginConf">
          <img src="/plugins/snips/3rdparty/icons/gear.png" style="width:55px!important">
          <br><br>
          <span>{{Configuration}}</span>
        </div>
    </div>

  <legend><i class="fa fa-bullhorn"></i> {{Snips Devices}}</legend>
  <div class="eqLogicThumbnailContainer">
  <?php
      if (!$eqLogics) {
          echo '<center>{{Please load assistant}}</center>';
      } else {
          $tts_eqs = snips::dump_eq_tts();
          foreach ($tts_eqs as $tts_eq) {
              $opacity = ($tts_eq->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
              $master = config::byKey('masterSite', 'snips', 'default');
              $icon = ($master == $tts_eq->getConfiguration('siteName')) ? 'master': 'satellite';
              echo '<div class="cursor testSite" data-site="'.$tts_eq->getConfiguration('siteName').'" data-eqLogic_id="' . $tts_eq->getId() . '" style="' . $opacity . '" >';
              echo '<img src="/plugins/snips/3rdparty/icons/'.$icon.'.png" style="width:55px!important">';
              echo "<br><br>";
              echo '<span>'.$tts_eq->getConfiguration('siteName').'</span>';
              echo '</div>';
          }
      }
  ?>
  </div>

  <legend><i class="fa fa-bolt"></i> {{Intents (Response by Jeedom)}}</legend>
  <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
  <div class="eqLogicThumbnailContainer" >
  <?php
      if (!$eqLogics) {
          echo '<center>{{Please load assistant}}</center>';
      } else {
          $intent_eqs = snips::dump_eq_intent();

          foreach ($intent_eqs as $intent_eq) {
              $opacity = ($intent_eq->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');

              $intent = '';
              $intent .= '<span class="panel panel-info eqLogicDisplayCard cursor snips_intent" data-eqLogic_id="' . $intent_eq->getId() . '" style="width: 230px; height: 142px !important; text-align: left; margin: 5px!important' . $opacity . '" >';
              $intent .= '<li class="panel-heading" style="padding: 5px 5px;">'. $intent_eq->getName() .'<span class="name" style="display:none">'. $intent_eq->getName() .'</span></li>';
              $intent .= '<li class="panel-body" style="padding: 0px; list-style-type: none;">';

              $intent .= '<ul class="list-group" style="margin: 0;">';
              if ($intent_eq->get_callback_scenario()) {
                $intent .= '<li class="list-group-item" style="padding: 4px 10px;"><span class="label label-success pull-right"> {{Yes}}</span>{{Callback Scenario}}</li>';
              } else {
                $intent .= '<li class="list-group-item" style="padding: 4px 10px;"><span class="label label-warning pull-right"> {{No}}</span>{{Callback Scenario}}</li>';
              }
              if (count($intent_eq->getConfiguration('slots'))) {
                $intent .= '<li class="list-group-item" style="padding: 4px 10px;"><span class="label label-success pull-right">'.count($intent_eq->getConfiguration('slots')).'</span>Slots</li>';
              } else {
                $intent .= '<li class="list-group-item" style="padding: 4px 10px;"><span class="label label-warning pull-right">0</span>Slots</li>';
              }
              if ($intent_eq->getConfiguration('bindings')) {
                $intent .= '<li class="list-group-item" style="padding: 4px 10px;"><span class="label label-success pull-right">'.count($intent_eq->getConfiguration('bindings')).'</span>{{Bindings}}</li>';
              } else {
                $intent .= '<li class="list-group-item" style="padding: 4px 10px;"><span class="label label-warning pull-right">0</span>{{Bindings}}</li>';
              }
              if ($intent_eq->getConfiguration('isSnipsConfig')) {
                $intent .= '<li class="list-group-item" style="padding: 4px 10px;"><span class="label label-success pull-right">{{Snips Binding}}</span>{{Reaction}}</li>';
              } else if($intent_eq->getConfiguration('isInteraction')) {
                $intent .= '<li class="list-group-item" style="padding: 4px 10px;"><span class="label label-success pull-right">{{Interaction}}</span>{{Reaction}}</li>';
              } else {
                $intent .= '<li class="list-group-item" style="padding: 4px 10px;"><span class="label label-warning pull-right">{{Not set}}</span>{{Reaction}}</li>';
              }
              $intent .= '</ul>';
              $intent .= '</li></span>';
              echo $intent;
          }
        }
  ?>
  </div>
</div>

<div class="col-lg-12 eqLogic" style="display: none;">
  <div class="input-group pull-right" style="display:inline-flex">
      <span class="input-group-btn">
        <a class="btn btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}
        </a><a class="btn btn-success eqLogicAction roundedRight" id="saveAll" data-action="save"><i class="fa fa-check-circle"></i> {{Save}}</a>
      </span>
    </div>

  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <li role="presentation" class="active"><a href="#intenttab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-brain"></i> {{Intent}}</a></li>
  </ul>
  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
      <form class="form-horizontal">
        <fieldset>
          <legend>{{Intent}}</legend>
          <div class="form-group">
              <label class="col-sm-1 control-label">{{Name}}</label>
              <div class="col-sm-3">
                  <input type="text" class="eqLogicAttr form-control" id="intentId" data-l1key="id" style="display : none;" />
                  <input type="text" class="eqLogicAttr form-control input-sm" id="intentName" data-l1key="name"  disabled/>
              </div>
              <label class="col-sm-1 control-label">{{Slots}}</label>
              <div class="col-sm-6">
                  <div id="table_cmd"> </div>
              </div>
          </div>
          <div class="form-group">
              <label class="col-sm-1 control-label">{{Status}}</label>
              <div class="col-sm-3">
                  <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-success btn-sm">
                      <input type="checkbox" class="eqLogicAttr" id="isEnable" data-l1key="isEnable" style="display: none;"><span>{{Enable}}</span>
                    </label>
                  </div>
              </div>
              <label class="col-sm-1 control-label">{{Reaction}}</label>
              <div class="col-sm-4">
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                  <label class="btn btn-default btn-sm" style="width: 150px;">
                    <input name="reaction" type="radio" class="eqLogicAttr" data-l1key="configuration" data-l2key="isSnipsConfig"> {{Snips Binding}}
                  </label>
                  <label class="btn btn-default btn-sm" style="width: 150px;">
                    <input name="reaction" type="radio" class="eqLogicAttr" data-l1key="configuration" data-l2key="isInteraction"> {{JeeDom Interaction}}
                  </label>
                </div>
              </div>
              <label class="col-sm-1 control-label">{{Language}}</label>
              <div class="col-sm-1">
                <span class="label label-primary eqLogicAttr" data-l1key="configuration" data-l2key="language"></span>
              </div>
          </div>
          <legend>{{Callback Scenario}}</legend>
          <div class="form-group">
              <label class="col-sm-1 control-label">{{Scenario}}</label>
              <div class="col-sm-4">
                  <select class="eqLogicAttr form-control input-sm" data-l1key="configuration" data-l2key="callbackScenario" data-l3key="scenario">
                      <option value="-1">{{None}}</option>
                      <?php
                      foreach ($scenarios as $scenario) {
                        echo '<option value="'.$scenario->getId().'">'.$scenario->getName().'</option>';
                      }
                      ?>
                  </select>
              </div>
              <label class="col-sm-1 control-label">{{Action}}</label>
              <div class="col-sm-2">
                  <select class="eqLogicAttr form-control input-sm" data-l1key="configuration" data-l2key="callbackScenario" data-l3key="action">
                      <option value="start">{{Start}}</option>
                      <option value="startsync">{{Start (sync)}}</option>
                      <option value="stop">{{Stop}}</option>
                      <option value="activate">{{Activer}}</option>
                      <option value="deactivate">{{Désactiver}}</option>
                      <option value="resetRepeatIfStatus">{{Remise à zero des SI}}</option>
                  </select>
              </div>
              <label class="col-sm-1 control-label">{{Tags}}</label>
              <div class="col-sm-2">
                  <textarea class="eqLogicAttr" style="height: 30px;width: 236px;" data-l1key="configuration" data-l2key="callbackScenario" data-l3key="user_tags"></textarea>
              </div>
              <div class="col-sm-4"></div>
          </div>
          <div class="form-group">
              <label class="col-sm-1 control-label">{{SnipsTags}}
                <sup><i class="fas fa-question-circle" title="{{In the Jeedom system, the maximum length of all the tags passed to the scenario is limited. Since the <kbd>#query#</kbd> and <kbd>#intent#</kbd> tag can take significant space, the other tags may not be passed as expected. Please only select the tags that will be used in the callback scenario.}}"></i></sup>
              </label>
              <div class="col-sm-11">
                  <span class="callbackScenarioTags"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="callbackScenario" data-l3key="isTagPlugin">{{#plugin#}}</span>
                  <span class="callbackScenarioTags"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="callbackScenario" data-l3key="isTagIdentifier"> {{#identifier#}}</span>
                  <span class="callbackScenarioTags"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="callbackScenario" data-l3key="isTagIntent"> {{#intent#}}</span>
                  <span class="callbackScenarioTags"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="callbackScenario" data-l3key="isTagSlots"> {{#slots#}}</span>
                  <span class="callbackScenarioTags"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="callbackScenario" data-l3key="isTagSiteId"> {{#siteId#}}</span>
                  <span class="callbackScenarioTags"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="callbackScenario" data-l3key="isTagQuery"> {{#query#}}</span>
                  <span class="callbackScenarioTags"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="callbackScenario" data-l3key="isTagConfidenceScore"> {{#confidenceScore#}}</span>
              </div>
          </div>
          <legend>{{Action Binding}}</legend>
          <div class="panel-heading cursor btn-default" id="bt_addNewBinding" style="width: 100%; border: 1.5px dashed #ddd;">
            <i class="fa fa-plus-circle"></i> {{Attach a new binding}}
          </div>
          <div id="div_bindings" style="padding-top: 10px;">
          </div>
        </fieldset>
      </form>
    </div>
  </div>
</div>
</div>

<?php
  include_file('desktop', 'snips', 'js', 'snips');
  include_file('core', 'plugin.template', 'js');
  include_file('desktop', 'snips', 'css', 'snips');
?>
