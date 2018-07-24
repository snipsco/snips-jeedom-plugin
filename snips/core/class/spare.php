//$payload = json_decode($message->payload);

            // $intent_name = str_replace('hermes/intent/','',$message->topic);
            // $site_id = $payload->{'siteId'}; //default
            // $session_id = $payload->{'sessionId'};

            // snips::debug('Intent received: ['.$intent_name.'] with payload: '.$message->payload);

            // $slots_value = array();
            // //get received slots
            // foreach($payload->{'slots'} as $slot){
            //     $slots_value[$slot->{'slotName'}] = $slot->{'value'}->{'value'};
            //     snips::debug('slotName: '.$slot->{'value'}->{'value'});
            // }

            // $cmds_with_this_intent = cmd::byTypeSubType('snips_intent', $intent_name);

            // $selected_cmds = array();
 
            // if(!empty($cmds_with_this_intent)){ // if this intent form is an offical 'Smart Light' skill. Change later.
            //     snips::debug('cmds_with_this_intent: not empty');
                
            //     // Select all the matched commands
            //     if(isset($slots_value['house_room'])){
            //         snips::debug('house_room: not empty');
            //         foreach($cmds_with_this_intent as $cmd){
            //             $temp_slots = array();
            //             $temp_slots = $cmd->getConfiguration('house_room');

            //             snips::debug('Target cmd type:'.gettype($cmd));
            //             snips::debug('Target cmd name: '.$cmd->getName());
            //             snips::debug('Target cmd room: '.$temp_slots['value']);
            //             snips::debug('Received room: '.$slots_value['house_room']);

            //             if($temp_slots['value'] == $slots_value['house_room']){
            //                 $selected_cmds[] = $cmd; 
            //             }

            //             unset($temp_slots);
            //         }
            //     }else{
            //         snips::debug('house_room: empty');
            //         foreach($cmds_with_this_intent as $cmd){
            //             $temp_slots = array();
            //             $temp_slots = $cmd->getConfiguration('house_room');
            //             if($temp_slots['value'] == 'default'){
            //                 $selected_cmds[] = $cmd; 
            //             }

            //             unset($temp_slots);
            //         }
            //     }

            //     snips::debug('Found commands!');

            //     //If there are commands have been selected
            //     if(!empty($selected_cmds)){
            //         // Activate all the matched commands
            //         snips::debug('selected_cmds: not empty');
            //         snips::debug('there are '.count($selected_cmds).' cmds found');
            //         snips::debug('cmd is: '.gettype($cmd));

            //         foreach($selected_cmds as $cmd){

            //             snips::debug('cmd is: '.gettype($cmd));
            //             snips::debug('target_command: '.$target_command);

            //             $action_cmd = cmd::byId(str_replace('#','',$cmd->getConfiguration('action')));

            //             $action_cmd->execute();

            //             snips::sayFeedback($cmd->getConfiguration('feedback'), $session_id);
            //         }

            //         // Set value to its info command
            //         if(isset($slots_value['intensity_number'])){
            //             snips::debug('intensity_number: not empty');
            //             foreach ($selected_cmds as $cmd) {
            //                 $temp_slots = array();
            //                 $temp_slots = $cmd->getConfiguration('intensity_number');

            //                 if(isset($temp_slots['value'])){

            //                     $info_cmd = cmd::byId(str_replace('#','',$temp_slots['value']));
            //                     //$info_cmd->setValue($slots_value['intensity_number']);

            //                     $eq_id = $info_cmd->getEqLogic_id();

            //                     snips::debug('eq id is: '.$eq_id);

            //                     /// Problem is here !!!
            //                     $eqLogic = eqLogic::byId($eq_id);

            //                     snips::debug('eq is: '.gettype($eqLogic));

            //                     snips::debug('before value: '.$info_cmd->getValue());
            //                     snips::debug('data is '.$slots_value['intensity_number']);

            //                     $options = array('slider' => $slots_value['intensity_number']);
            //                     $test_cmd = cmd::byId(105);

            //                     $test_cmd->execCmd($options);
            //                     //$r = $eqLogic->checkAndUpdateCmd($info_cmd, $slots_value['intensity_number']);
            //                     //$info_cmd->setValue($slots_value['intensity_number']);

            //                     snips::debug('result is: '.$r);
            //                     snips::debug('eq is: '.$eqLogic->getName());
            //                     snips::debug('cmd is: '.$info_cmd->getName()); //
            //                     snips::debug('after value: '.$info_cmd->getValue());
            //                 }
            //                 unset($temp_slots);
            //             }
            //         }

            //         if(isset($slots_value['intensity_percent'])){
            //             snips::debug('intensity_percent: not empty');
            //             foreach ($selected_cmds as $cmd) {
            //                 $temp_slots = array();
            //                 $temp_slots = $cmd->getConfiguration('intensity_percent');


            //                 if(isset($temp_slots['value'])){
            //                     $info_cmd = cmd::byId(str_replace('#','',$temp_slots['value']));

            //                     $info_cmd->setValue($slots_value['intensity_percent']);
            //                 }
            //                 unset($temp_slots);
            //             }
            //         }
            //     }
            // }