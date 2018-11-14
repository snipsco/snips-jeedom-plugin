# Instruction

This file is used to track the development of this plugin. If you are a user, this is not something very interesting.

# Objectives
To be able to use all your Jeedom devices by using voice! And, we should let you done this in a simple way!

# To do list
- [ ] Allow binding passing `*`(any) for none fixed slots value
- [ ] Allow use binary remapping in the scenario
- [ ] Configurable for passing tags for each intent 
- [x] Configurable for snips variables (Beta)

# Features
## Planned
- [x] Configurable MQTT client
- [x] Receive all the intents and its slots from the bus
- [x] Load all the intents automaticly
- [x] All the intents shell be managed by its objects(Do not have to configure by user)
- [x] Make the slots information useful
- [x] Working separately with Snips site
- [x] Download 'assistant.json' file remotely from snips site (Via ssh)
- [x] Managing conditions
- [x] Dynamic TTS contents

## Todo list for the **beta** release
- [x] Create the snips-intent object automaticly when load assistant // mandatory
- [x] 0 to 100 value max 99 (for all lights and so on) // mandatory
- [x] Find a way to reset slot value for scenario uses // mandatory
- [x] Double check to load assistant // mandatory
- [x] Load assistant remotely // mandatory
- [x] Import & export // mandatory
- [x] Light shift function // mandatory
- [x] Dynamic TTS player selection // mandatory
- [x] Find a way to map binary to text // improvement

## Todo list for the **public** release
- [x] Adapt to dark sobre theme
- [x] Clean code rules apply to all the previous code
- [ ] Output bindings should be able to manage (Able to delete)
- [x] Re-arrange debug log output

## Founded Bugs
- [x] losing tts command in scenario after reload (27 Aug)
- [x] listEquipmentInfo button does not work very well. Need to double check (23 Aug)

# Develop Diary
5, Seo, 2018
- [x] Better design for displaying snips devices

3, Sep, 2018
- [x] Fixed a small bug of passing tags

31, Aug, 2018
- [x] Look through the whole documentaiton
- [x] Prepare both English screenshoot and French one

30, Aug, 2018
- [x] Change 'identifier' value to snips::intent_id::binding_name
- [x] Change the ask documentation part
- [x] Separate snips device reload and assistant reload

29, Aug, 2018
- [x] Documentation for new features
- [x] Documentation picture for scenario usecase
- [x] Release
```
Version: 2018-08-29 12:21:31

Improvement: support french interface translation
Improvement: pass all necessary infos as tags when snips plugin trigger an scenario
Change: removed [Reset MQTT] option
Fix: losing request command in scenario ask command
```

28, Aug, 2018
- [x] Finished French translation
- [x] Add feature: pass some necessary tags when snips launch a scenario

27, Aug, 2018
- [x] Fix the bug: losing tts command in scenario after reload
- [x] Start to implement multi-language system

24, Aug, 2018
- [x] Troubleshooting with 'listEquipment' issue
- [x] Last beta release
- [x] Clean code

```
Version: 2018-08-24 18:16:52

New feature: support 'ask' command
New feature: automatic snips tts reply (Find the detail in the configuration page of the plugin)
Change: adapt jeedom grammar for tts message (use [] to contain the list and use | to separate)
Fixed bug: listEquipmentCmd button fills value to wrong input box for command options
Improvement: adapt to [dart sobre] black theme
Improvement: support tts play for 'scenario_return'
```

23, Aug, 2018
- [x] Support the 'ask' feature
- [x] Add a global dynamic tts command (Or option to enable)

22, Aug, 2018
- [x] Add support for 'Scenario Return'
- [x] Adapted to 'Dark Sobre' theme
- [x] Code clean for public release

21, Aug, 2018
- [x] Documentation for using bindings in scenario(from jeedom user)
- [x] Functional test

13, Aug, 2018
- [x] Release testing
- [x] Adapt tts grammar to Jeedom formation

10, Aug, 2018
- [x] Improved versions checking (version_compare)
- [x] Site Id is available to access (Variable name: snipsMsgSiteId)
- [x] Repaired scenario lose efficacy (Check previous intent list when load)

9, Aug, 2018
- [x] Documentation finsied
- [x] Fixed bug: wrong message command selection
- [x] TTS site automatic selection

8, Aug, 2018
- [x] Support multi-light brightness shift
```php
// User configuration
$VARS = array(
"OPERATION" => "UP", // Use "UP" or "DOWN"
"LIGHTS" => array(

array(
    "LIGHT_BRIGHTNESS_VALUE" => "#[Apartment][Mirror Strip Right][Etat Luminosité]#",
    "LIGHT_BRIGHTNESS_ACTION" => "#[Apartment][Mirror Strip Right][Luminosité]#",
    "MIN_VALUE" => 0,   // Min brightness value
    "MAX_VALUE" => 255, // Max brightness value
    "STEP_VALUE" => 0.2 // Change in percentage, if 20%, then put 0.2
),
array(
    "LIGHT_BRIGHTNESS_VALUE" => "#[Apartment][Mirror Strip Left][Etat Luminosité]#",
    "LIGHT_BRIGHTNESS_ACTION" => "#[Apartment][Mirror Strip Left][Luminosité]#",
    "MIN_VALUE" => 0,   // Min brightness value
    "MAX_VALUE" => 255,	// Max brightness value
    "STEP_VALUE" => 0.2 // Change in percentage, if 20%, then put 0.2
),

));
// Execution (Do not change)
snips::lightBrightnessShift(json_encode($VARS));
```
- [x] Support reset info command
```php
$VAR = '#[Apartment][Lamp Desk][Online]#';  // command which need to be reseted

$cmd = cmd::byString($VAR);
snips::debug("[Test] Before reset value: ".$cmd->getCache('value', ''));
$cmd->setCache('value', '');
snips::debug("[Test] After reset value: ".$cmd->getCache('value', ''));
```
- [x] Support separate Jeedom intents


7, Aug, 2018
- [x] Adaption for Jeedom 3.3.3
- [ ] Multi-dialog feature
```
Version: 2018-08-07 12:52:50

Adaption: Jeedom 3.3.3
Disabled multi-dialog function for the moment
```

6, Aug, 2018
- [x] Fxied 'Snips-Intents-ç' bugs
- [x] Multi-dialog features: end session by intent

3, Aug, 2018
- [x] Changed functional icons

2, Aug, 2018
- [x] Non-linear tts feadback
- [x] TTS binary value remapping

1, Aug, 2018
- [x] Support synonyms in condtion
```
Beta release
Version: 2018-08-01 18:25:37

Add feature: allow to select a non-snips tts command.
Add feature: support synonyms in conditon.

Improved stability.
```

31, Jul, 2018
- [x] Replant dynamic tts function

30, Jul, 2018
- [x] Merge [beta-release] to [master]
- [x] Add feature: tts command

29, Jul, 2018
```
Beta release
Version: 2018-07-29 20:55:36

Updated Logo.
Added more debug output.
```

27, Jul, 2018
```
Beta release
Version: 2018-07-27 18:21:29

Add feature: simplfied steps for loading assistant.
```

26, Jul, 2018
```
Beta release
Version: 2018-07-26 16:08:40

Fixed bug: ssh2_disconnect not found. (Reported by @Cecece)
Fixed bug: some of the import binding data can not be displayed correctly.
Change: moved to 'communication' cotegory from 'automation'.
Change: all the log will be shown udner snips(debug level).
Improved stability.
```

25, Jul, 2018
- [x] Added examples in tutorial
```
Beta release
Version: 2018-07-25 18:42:36

Fixed bug: ssh can not fetch assistant. (Reported by @rudloffl)
```

24, Jul, 2018
- [x] Finished tutorial documentation

23, Jul, 2018
- [x] Add feature: support lightShift intent

```php
// User configuration

$LIGHT_BRIGHTNESS_VALUE = '#[Apartment][Mirror Strip Right][Etat Luminosité]#';
$LIGHT_BRIGHTNESS_ACTION = '#[Apartment][Mirror Strip Right][Luminosité]#';
$OPERATION = 'DOWN'; // 'ON' or 'DOWN', case sensitive
$MIN_VALUE = 0;
$MAX_VALUE = 255;
$STEP_VALUE = 0.2; //Change 20% of MAX_VALUE each time

// Execution

$cmd = cmd::byString($LIGHT_BRIGHTNESS_VALUE);

if (is_object($cmd))
if ($cmd->getValue()) $current_val = $cmd->getValue();
else $current_val = $cmd->getCache('value', 'NULL');
$options = array();

if ($OPERATION === 'UP') $options['slider'] = $current_val + round(($MAX_VALUE - $MIN_VALUE) * $STEP_VALUE);
else
if ($OPERATION === 'DOWN') $options['slider'] = $current_val - round(($MAX_VALUE - $MIN_VALUE) * $STEP_VALUE);

if ($options['slider'] < $MIN_VALUE) $options['slider'] = $MIN_VALUE;

if ($options['slider'] > $MAX_VALUE) $options['slider'] = $MAX_VALUE;
fwrite(STDOUT, '[Scenario] Light shift for [' . $LIGHT_BRIGHTNESS_ACTION . '], from -> ' . $options['slider'] . ' to ->' . $current_val . '\n');
$cmdSet = cmd::byString($LIGHT_BRIGHTNESS_ACTION);

if (is_object($cmdSet)) $cmdSet->execCmd($options);
```

20, Jul, 2018
- [x] Add feature: support import user binding configuration

19, Jul, 2018
- [x] Add feature: support fetch assistant remotely from snips

18, Jul, 2018
- [x] Add feature: support reset value after execute a scenario

17, Jul, 2018
- [x] Add feature: remap percentage value to real range

16, Jul, 2018
- [x] Improved functional code(Create object when reload assistant)

15, Jul, 2018
- [x] Field text at home implmentation

12, Jul, 2018
- [x] Tested all the functional part, make it ready for demo.

11, Jul, 2018
- [x] Backend support for dynamic tts, senario etc..

10, Jul, 2018
- [x] Polishing user interface
- [x] Changed default style, more visible for useful information

9, Jul, 2018
- [x] Troubleshooting with dynamic feature, fixed died loop problem.
- [x] Transfered repository to 'snipsco'.

6, Jul, 2018
- [x] Add feature: dynamic tts message. User can use '{#}' to represent a value from system.

4, Jul, 2018
- [x] Clearfeid backend binding configuration selecting logic.
- [x] Add feature that can tell the user which slots is necessary for a specific binding.

3, Jul, 2018
- [x] Fixed bug: executing command without a correct value.
- [x] Add feature: support using system interaction.

2, Jul, 2018
- [x] Change the condition expression into info command format
- [x] Test out exist bugs

29, Jun, 2018
- [x] Add Ctrl+S shortcut suoport to save on the binding configuration page
- [ ] Backend working

28, Jun, 2018
- [x] 3rd alpha version frontend is done.
- [x] Fixed bugs, lossing binding rocords etc..

25, Jun, 2018
- [x] Start building frontend for 3rd αVersion.

22, Jun, 2018
- [x] Redesigned working flow and archtecture.
- [x] Enable user to manage common conditions and mutil actions.

18, Jun, 2018
- [x] 2nd alpha version works.

8, Jun, 2018
- [x] 1st alpha version is done.

5, Jun, 2018
- [x] Add voice feed back once an action has been triggered.
- [x] Unified the idea of skills management.  

4, Jun, 2018
- [x] Beta version has been done.

1, Jun, 2018
- [x] Finished the code, troubleshooting.

31, May, 2018
- [x] Learn how to use configuration patameters.
