## Version: 2018-11-07 17:53:59

### Added
- Callback scenario for each intent
- Variable: snipsMsgHotwordId. This variable will be assigned with the detected hotword id

### Changed
- Display callback scenario status instead of language
- Intent card is shown in the different color setup

### Removed
- Tag passing config items on the configuration page 

## Version: 2018-09-21 19:12:12 [Beta]

### Added
- Added config options for snips-used variables/ tags

### Fix
- Support snips/built-in type `duration` (Thanks to Kiboost)

## Version: 2018-09-03 17:49:57

### Added
- Added satellite test button

### Improved
- Passing tags as original format


## `Version: 2018-08-29 12:21:31`

### Improved
- Support french interface translation
- Pass all necessary infos as tags when snips plugin trigger an scenario

### Changed
- Removed [Reset MQTT] option

### Fixed
- Losing request command in scenario ask command

## `Version: 2018-08-24 18:16:52`

### Added
- Support 'ask' command
- Automatic snips tts reply (Find the detail in the configuration page of the plugin)

### Improved
- Adapt to [dart sobre] black theme
- Support tts play for 'scenario_return'

### Changed
- Adapt jeedom grammar for tts message (use [] to contain the list and use | to separate)

### Fixed
- ListEquipmentCmd button fills value to wrong input box for command options


## `Version: 2018-08-13 13:12:19`
### Addred
- Variable name "snipsMsgSiteId", access from scenario (Thanks to @fwehrle)

### Improved
- Versions checking (Thanks to @Hugo)
- Repaired scenario lose efficacy when reload (If a intent name is not changed, this should still be useful in scenario)
- TTS feedback, automatically select site

### Fixed
- Message selection box (Thanks to @Hugo)

## `Version: 2018-08-08 18:15:31`
### Added
- Only load intent which has Jeedom as a part of its name
- Light shift for multi-light

### Fixed
- Binary value mapping is always "0"

## `Version: 2018-08-07 12:52:50`
### Improved
- Adaption: Jeedom 3.3.3

### Changed
- Disabled multi-dialog function for the moment

## `Version: 2018-08-05 15:07:49`
### Added
- Support multi-feedback
- Sites automatically detection

### Improved
- Reload assistant with bidings

## `Version: 2018-08-01 18:25:37`
### Added
- Allow to select a non-snips tts command
- Support synonyms in conditon

### Improved
- Improved stability

## `Version: 2018-07-29 20:55:36`
### Changed
- Updated Logo
- More debug output

## `Version: 2018-07-27 18:21:29`
### Changed
- Simplfied steps for loading assistant. 

## `Version: 2018-07-26 16:08:40`
### Changed
- Moved to 'communication' cotegory from 'automation'
- All the log will be shown udner snips(debug level)

### Fixed
- SSH2_disconnect not found. (Reported by @Cecece)
- Some of the import binding data can not be displayed correctly.

### Improved
- Improved stability.

## `Version: 2018-07-25 18:42:36`
### Fixed
- SSH can not fetch assistant. (Reported by @rudloffl)
