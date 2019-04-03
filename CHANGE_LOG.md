## Update `0.1.3` - 02/04/2019

**Bug Fixes**

- `getCmd` on boolean by using `intent_id` issue. ([#13](https://github.com/snipsco/snips-jeedom-plugin/issues/13), [#16](https://github.com/snipsco/snips-jeedom-plugin/issues/16))
- `Ask` command not working issue. ([#14](https://github.com/snipsco/snips-jeedom-plugin/issues/14))
- Solved the error under PHP5: called to an undefined function. ([#15](https://github.com/snipsco/snips-jeedom-plugin/issues/15))
- `Say` command not working issue.
- Callback scenario returns '1' is being played issue.
- Warning `Illegal string offset: scenario`  on the eqLogic card issue.
- Scenario variables are not being created and updated. ([#17](https://github.com/snipsco/snips-jeedom-plugin/issues/18))


## Update `0.1.2` - 28/03/2019

**Bug Fixes**

- Only create cmd object for Jeedom intent. ([#11](https://github.com/snipsco/snips-jeedom-plugin/issues/11))
- Isolated task cron creation and deletion, added into `deamon_stop` function when no task cron found.


## Update `0.1.1` - 26/03/2019

**Bug Fixes**

- Create a new task cron if it can not be found after update.
- When creating/deleting `Snips-Intent` object, using class `object` if core version is under 3.3.3, using `jeeObject` if core version is over 3.3.3.


## Update `0.1.0` - 22/03/2019

**New Feature**

- Support dependency installation progress bar.

**Changes**

- Re-constructed source code, they are now collaborating in a more logical way.
- Callback scenario passes `#confidenceScore#` instead of `#probability#`.

**Known Issue**

- Temporarily disabled playing tts on the third part device. (New plugin structure doesn't support this feature yet)


## Update `0.0.16` - 24/01/2019

**New Features**

- Support multi-turn dialogue. Trigger once, play multiple times.
- Support passing multiple slots value to scenario.


## Update `0.0.15` - 07/11/2018

**New Features**

- Support `callback scenario` for each intent.
- Add variable `snipsMsgHotwordId`, which will be assigned with the detected hotword model id.

**Changes**

- UI: display callback scenario status instead of language.
- UI: intent card uses blue as main color.
- Scenario tag passing config options on the plugin configuration page.


## Update `0.0.14` - 21/09/2018

**New Features**

- Support snips-used variables on the configuration page.
- Support selecting scenario tags, which will be passed to execution.
- Support snips/built-in type `duration`. (@Kiboost)


## Update `0.0.13` - 03/09/2018

**New Feature**

- Click the Snips device icon can test sound output.

**Change**

- Passing tags as original format.


## Update `0.0.12` - 29/08/2018

**New Features**

- Support french interface translation.
- Passing necessary information as default tags when a scenario is triggered.

**Change**

- Removed `Reset MQTT` option from intent management panel.

**Bug Fix**

- Losing request command in scenario ask command.


## Update `0.0.11` - 24/08/2018

**New Features**

- Support 'ask' command.
- Automatically reply tts message to the expected device(The device that received commend).
- Support `dart sobre` ui theme.
- Support tts playing for 'scenario_return'.
- Support jeedom grammar for tts message, which use `[]` to contain the list and use `|` to separate.

**Bug Fix**

- ListEquipmentCmd button fills value to wrong input box for command options.


## Update `0.0.10` - 13/08/2018

**New Feature**

- Add variable `snipsMsgSiteId`, it will be assigned `siteId` when a new message is received. (@fwehrle)

**Changes**

- Versions checking (@Hugo)
- Repaired scenario losing effect when reload.
- Tts feedback wii automatically select site.

**Bug Fix**

- Message selection box issue. (@Hugo)


## Update `0.0.9` - 08/08/2018

**New Feature**

- Support multiple lights for light brightness shift function.

**Change**

- Only load intent which has `jeedom` sub-string in its name.

**Bug Fix**

- Binary value mapping is always "0".


## Update `0.0.8` - 07/08/2018

**New Feature**

- Support `Jeedom 3.3.3`.

**Change**

- Disable multi-dialog function for the moment.


## Update `0.0.7` - 05/08/2018

**New Features**

- Support reloading assistant with bindings option.
- Support multi-feedback.

**Change**

- Automatically detect site information.


## Update `0.0.6` - 01/08/2018

**New Features**

- Support using the third-party tts command.
- Support synonyms for condition preset value.


## Update `0.0.5` - 29/07/2018

**Changes**

- New logo.
- More debug output.


## Update `0.0.4` - 27/07/2018

**Change**

- Simplified loading assistant steps.


## Update `0.0.3` - 26/07/2018

**Changes**

- Plugin category has been moved to `communication` from `automation`.
- All the log information will be shown under snips(`debug` level).

**Bug Fixes**

- SSH2_disconnect not found. (@Cecece)
- Some of the imported binding data can not be displayed correctly.


## Update `0.0.2` - 25/07/2018

**Bug Fix**

- SSH can not fetch assistant. (@rudloffl)