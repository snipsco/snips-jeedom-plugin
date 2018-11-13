<img align="right" src="/snips/plugin_info/snips_icon.png" width="180">

## User Guide

Please reach ***[Snips Dev Center](https://snips.gitbook.io/)*** for the user documentation.
- ***[English Version](https://snips.gitbook.io/documentation/home-automation-platforms/jeedom)***
- ***[French Version](https://snips.gitbook.io/documentation/home-automation-platforms/jeedom-fr)***

## Snips-Jeedom-Plugin

[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/snipsco/snips-skill-respeaker/blob/master/LICENSE)

The Snips Voice Platform allows anyone to integrate AI powered voice interaction in their devices with ease. The end-to-end pipeline - Hotword detection, Automatic Speech Recognition (ASR) and Natural Language Understanding (NLU) - runs fully on device, powered by state of the art deep learning. By using Snips, you can avoid cloud provider costs, cloud latency, and protect user's privacy.

## Installation (As a developer)

### Step 1. Download plugin
For this step, I would recommand you use `ssh` login your Jeedom site through console:
```
ssh <username>@<hostnme>
```
For example, if your Jeedom is running on a Raspberry, you can then use:
```
ssh pi@raspberry.local
```
As long as you have successfully loged in, you can simply use `git clone` command to download this repository:
```
git clone https://github.com/snipsco/Snips-Jeedom-Plugin.git
```
Now you can do `ls` to check if `Snips-Jeedom-Plugin` is under your current directory:
```
ls Snips-Jeedom-Plugin
```
You should see the result:
```
README.md	    snips
```
### Step 2. Copy plugin to Jeedom folder
Now you have the plugin file on your Jeedom site, but Jeedom an not detect this yet.

Run the following command to copy snips plugin to Jeedom directory:
```
sudo cp -r Snips-Jeedom-Plugin/snips/ /var/www/html/plugins/
```

### Step 3. Change permission
Run following to add correct permission to all the file:
```
sudo chmod -R 775 /var/www/html/plugins/snips/
```
Run following to change correct user group to all the file:
```
sudo chgrp -R www-data /var/www/html/plugins/snips/
```
Run following to change correct ownership to all the file:
```
sudo chown -R www-data /var/www/html/plugins/snips/
```

### Step 4. Activate Snips plugin
Now you can direct to your Jeedom platform, go to **Plugin Management**, find **Snips**, and then activate it.

Then you need to set the correct IP address on the plugin configuration page.

(Use `sam devices` command to see the IP address of snips site)

## Contributing

Please see the [Contribution Guidelines](https://github.com/snipsco/Snips-Jeedom-Plugin/blob/master/CONTRIBUTING.md).

## Copyright

This library is provided by [Snips](https://www.snips.ai) as Open Source software. See [LICENSE](https://github.com/snipsco/Snips-Jeedom-Plugin/blob/master/LICENSE) for more information.
