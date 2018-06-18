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
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Add an Assistant}}</a>
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

      <div class="cursor eqLogicAction" data-action="add" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <i class="fa fa-refresh" style="font-size : 6em;color:#f0ad4e;"></i>
        <br>
        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#94ca02">{{Add Test}}</span>
      </div>


      <div class="cursor eqLogicAction" data-action="gotoPluginConf" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;">
        <i class="fa fa-wrench" style="font-size : 6em;color:#767676;"></i>
        <br>
        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676">{{Configuration}}</span>
      </div>


  </div>
  <legend><i class="fa fa-bolt"></i> {{My Skills}}</legend>

  <!--Management of All the skills(Objects)-->
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
	<a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Save}}</a>
  <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Delete}}</a>
  <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> {{Pre-Configuration}}</a>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Settings}}</a></li>
    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
  </ul>
  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
    <form class="form-horizontal">
        <fieldset>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Name of Assistant}}</label>
                <div class="col-sm-3">
                    <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Smart Snips}}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" >{{Parent Object}}</label>
                <div class="col-sm-3">
                    <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                        <option value="">{{Any}}</option>
                        <?php
                            foreach (object::all() as $object) {
                                echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                            }
                        ?>
                   </select>
               </div>
           </div>
	   <div class="form-group">
                <label class="col-sm-3 control-label">{{Category}}</label>
                <div class="col-sm-9" style="vertical-align: middle">
                 <?php
                    foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                    echo '<label class="checkbox-inline">';
                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                    echo '</label>';
                    }
                  ?>
               </div>
           </div>
	<div class="form-group">
		<label class="col-sm-3 control-label">{{Status}}</label>
		<div class="col-sm-9">
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Active}}</label>
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
		</div>
	</div>
       <div class="form-group">
        <label class="col-sm-3 control-label">{{Assistant Directory}}</label>
        <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="intentSetAddr" placeholder="/var/share/snips/assistant"/>
        </div>
    </div>
</fieldset>
</form>
</div>


  <div role="tabpanel" class="tab-pane" id="commandtab">

      <div class="alert alert-info" style="margin: 10px 0pxl"> 
        <h3 class="panel-title" style="padding: 10px 0px;">
          <a style="text-decoration:none;">Tips</a>
        </h3>
            <p>- Match each intent with a specific device command.</p>
            <p>- Put '*' if the slot is not concerned.</p>
            <p>- Example:</p>
            <p>  Intent: lightsTurnOff Slots: house_room = living room Command:[Apartment][Test Lamp][Off]</p>
            <p>- Explanation:</p>
            <p>  When the snips reveive intent 'lightsTurnOff' with slot value 'living room', the lights in Appartment named Test Lamp will be turned off. </p>

      </div>

      <a class="btn btn-success btn-sm cmdAction pull-left" id="addIntent" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Attach a new intent-command mapping}}</a><br/><br/>
    

      <table id="table_cmd" class="table table-bordered table-condensed">
          <thead>
              <tr>
                  <th style="width: 210px;">{{Intent}}</th>
                  <th style="width: 400px;">{{Slots Configuration}}</th>
                  <th style="width: 350px;">{{Action}}</th>
                  <th style="width: 250px">{{Feedback}}</th>
                  <th style="width: auto;">{{Setting}}</th>
              </tr>
          </thead>

          <tbody>

          </tbody>
      </table>
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


