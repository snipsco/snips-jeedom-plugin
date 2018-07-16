![Snips Voice Platform](https://s3.amazonaws.com/get.docs.snips.ai/static/images/wiki/snips_banner_prod.png)

# Snips-JeeDom-Plugin

The Snips Voice Platform allows anyone to integrate AI powered voice interaction in their devices with ease. The end-to-end pipeline - Hotword detection, Automatic Speech Recognition (ASR) and Natural Language Understanding (NLU) - runs fully on device, powered by state of the art deep learning. By using Snips, you can avoid cloud provider costs, cloud latency, and protect user's privacy.

# Objectives
To be able to use all your JeeDom devices by using voice! And, we should let you done this in a simple way!

# Features
- [x] Configurable MQTT client
- [x] Receive all the intents and its slots from the bus
- [x] Load all the intents automaticly
- [ ] Find out all the available devices automaticly
- [x] All the intents shell be managed by its objects(Do not have to configure by user)
- [x] Make the slots information useful
- [ ] Working separately with Snips site (Possible, but no test yet)
- [ ] Download 'assistant.json' file remotely from snips site (Via ssh)
- [x] Managing conditions
- [x] Dynamic TTS

# Todo list for the beta release
- [ ] 0 to 100 value max 99 (for all lights and so on) // mandatory
- [ ] find a way to reset slot value for scenario uses // mandatory
- [ ] find a way to map binary to text // improvement
- [ ] adapt to dark theme // improvement
- [ ] double check to load assistant // mandatory
- [ ] dynamic TTS selection // mandatory
- [ ] multi-intent, 1 slot multiple value // improvement
- [ ] export // mandatory
- [ ] load assistant remotely // mandatory

# Develop Diary
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


