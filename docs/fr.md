# Jeedom \(Fr\)

**This documentation is probably out-of-date, please refer to the [English version](https://docs.snips.ai/articles/raspberrypi/jeedom/en)**

## Introduction

Afin d'intégrer la technologie vocale Snips à votre plate-forme Jeedom, nous avons développé un plug-in Snips officiel qui vous permet de déclencher des actions / scénarios et d'obtenir une valeur ou une information de vos appareils / capteurs connectés à Jeedom.

Si vous avez des problèmes / erreurs / retours ou si vous voulez parler avec d'autres Snipsters et Jeedomiens, vous pouvez utiliser:

* Forum category **\#jeedom\(FR\) --&gt;** [**Forum**](https://forum.snips.ai)\*\*\*\*
* Le repo Github: [https://github.com/snipsco/Snips-Jeedom-Plugin](https://github.com/snipsco/Snips-Jeedom-Plugin)

Si vous souhaitez un boitier en impression 3D pour vos satellites, veuillez trouver ci-dessous deux fichiers .stl:

{% file src="../../../.gitbook/assets/rpi0\_cover\_maker.stl" %}

{% file src="../../../.gitbook/assets/rpi0\_body\_maker.stl" %}

## Pour commencer

Voici une vidéo explicative détaillant étape par étape l'installation et la configuration du plugin Snips pour Jeedom:

{% embed url="https://youtu.be/6CprakYJZEU" %}

Snips est un assistant vocal pouvant fonctionner sur un certain nombre de plateformes. Il y a 4 étapes à suivre pour connecter votre Assistant Snips avec votre plateforme Jeedom.

1. [Configuration d'un appareil](en.md#1-setup-a-snips-device)
2. [Application Jeedom sur la console Snips](en.md#2-having-your-jeedom-app-and-deploy-assistant)
3. [Installation du plugin Snips sur Jeedom](en.md#3-installation-of-snips-plugin-on-jeedom)
4. [Configuration du plugin ](en.md#4-configurate-snips-plugin)
5. [Commencer](en.md#5-start-to-play)

### 1. Configuration d'un appareil Snips

Il y'a deux manières de configurer votre système Snips-Jeedom:

* Snips et Jeedom fonctionnant sur deux appareils séparés \(recommandé\)
* Snips et Jeedom fonctionnant sur le meme appareil

Peu importe la configuration choisie, l'installation de Snips est identique, merci de vous référer à cette [section ](../manual-setup.md)\(En Anglais\) afin de configurer Snips sur votre appareil

### 2. Application JeedomOfficiel dans votre assistant

Pour cette étape, vous allez utiliser la [web console](https://console.snips.ai/) Snips

Créer un nouvel assistant, donnez lui un nom, puis choisissez la langue Française comme indiqué dans l'image 2.2.1

{% hint style="info" %}
l'App est en Français pour le moment , elle sera traduite dans d'autres langues prochainement
{% endhint %}

![Figure 2.2.1 Cr&#xE9;ation d&apos;un nouvel assistant](../../../.gitbook/assets/image%20%2832%29.png)

Cliquez sur \[**Add an App**\], puis cherchez \[**JeedomOfficiel**\], ajoutez  la à votre assistant comme indiqué dans l'image 2.2.2

{% hint style="info" %}
Vous devez décochez  le paramètre "App with Action" puisque JeedomOfficiel n'est pas une application avec des actions \(Jeedom se charge des actions\). Si vous ne décochez pas ce paramètre vous ne trouverez pas l'application.
{% endhint %}

![Figure 2.2.2 Ajoutez l&apos;App JeedomOfficiel ](../../../.gitbook/assets/image%20%2896%29.png)

Une fois que vous avez réalisé ces étapes, votre assistant sera automatiquement sauvegardé. Si vous souhaitez testes des requêtes directement sur votre navigateur web, vous pouvez simplement cliquer sur l'icône microphone en haut à droite. Vous pouvez ensuite parler à travers votre microphone ou bien écrire votre requête comme indiqué sur l'image 2.2.3.

![Figure 2.2.3 Tester une requ&#xEA;te](../../../.gitbook/assets/image%20%28105%29.png)

Il est maintenant temps de déployer votre assistant sur votre appareil \(Raspberry PI\), vous pouvez suivre ces[ étapes](../../console/actions/deploy-your-assistant.md) expliqués lors des chapitres précédents.

### 3. Installation du plugin Snips

Vous pouvez trouver le Plugin Snips sur le Market Jeedom. Snips est dans la catégorie gratuite comme montré dans l'image 2.3.1

![Figure 2.3.1 A Direct to Jeedom Market](../../../.gitbook/assets/image%20%2837%29.png)

![Figure 2.3.2 Find Snips plugin](../../../.gitbook/assets/image%20%2839%29.png)

### 4. Configuration du plugin Snips

À ce stade vous devez avoir correctement installé le plugin Snips sur votre Jeedom. Si ce n'est pas le cas, nous vous invitons à relire les étapes précédentes.

L' une des étapes les plus importantes est d'indiquer la bonne \[**Adresse IP Snips**\] et le bon \[**Port**\] dans la section configuration \(page de configuration du plugin\). L' adresse IP à indiquer est celle de l'appareil sur lequel est installé Snips.

* Si vous avez Snips sur un appareil et Jeedom sur un autre, vous pouvez utilisez la commande `sam devices` sur votre terminal afin de trouver la bonne addresse ip dur votre réseau. Sinon, vous pouvez aller la page admin de votre routeur pour la trouver. \(Si vous ne connaissez pas nos commandes `sam` veuillez lire ce [chapitre](../../../reference/sam.md)\)
* Si Snips et Jeedom fonctionnent sur le meme appareil, l'adresse IP à indiquer sera toujours`127.0.0.1`

{% hint style="warning" %}
N'oubliez pas de **sauvegarder.**
{% endhint %}

La page du plugin Snips devrait être vide. En revanche, si les informations ont correctement été remplis dans la page de configuration, nous pouvons maintenant charger l'assistant.

Cliquer sur \[Charger Assistant\], puis suivez les instructions afin d'avoir tous les intents de l'assistant chargés sur la plateforme Jeedom. Étant donné que c'est la première fois, vous devriez cliquer sur \[Chargement de l'assistant sans liaison\]. Nous expliquerions ce qu'est une  \[liaison\] plus tard.

Ces étapes sont montrés dans l'image 2.4.1

![Figure 2.4.1 Chargement de l&apos;assistant](../../../.gitbook/assets/image%20%2858%29.png)

Une fois l'assistant chargé, tous les intents \(intentions\) seront listés sur la page du plugin Snips \(image 2.4.2\)

{% hint style="warning" %}
Si l'adresse IP de Snips est incorrect, cette étape ne chargera pas votre assistant.
{% endhint %}

![Figure 2.4.2 Assistant charg&#xE9;](../../../.gitbook/assets/image%20%2824%29.png)

La configuration est terminé, vous pouvez commencer à créer vos actions.

### 5. Commencer

Dans cette section, nous montrerons quelques examples qui illustreront les différentes fonctions du plugin Snips.

Si vous ne savez pas comment fonctionne Snips, nous vous invitons à lire le reste de la documentation Voici tout de même une brève explication: Snips capture la requête vocale, la transforme en une donnée structuré composé de slots et d'intents \(intention\). Snips extrait les informations importantes de cette phrase and vous fournit un set de donnée \(Json\).

Voici un exemple: "Hey snips, allume la lumière dans le salon", cette phrase sera converti en un set de donnée comme ceci:

```text
{
  "input": "Hey snips, allume la lumière dans le salon",
  "intent": {
    "intentName": "coorfang:lightsSetjeedom",
    "probability": 0.83969486
  },
  "slots": [
    {
      "rawValue": "salon",
      "value": {
        "kind": "Custom",
        "value": "salon"
      },
      "range": {
        "start": 25,
        "end": 36
      },
      "entity": "house_room",
      "slotName": "house_room"
    }
  ]
}
```

Dans ce set de donnée, l'intent est bien détecté et les valeurs de slots seront utilisés pour déclencher un\(e\) scénario/action spécifique dans le plugin Snips.

#### Allumer/Eteindre une lumière

Cliquer sur l'intent qui correspond à "allumer la lumière", cela vous dirigera sur la page de configuration de liaison. Dans cet example,  l'intent `lightSetJeedom`sera celui détecté et envoyé à Jeedom.

Sur cette page, cliquez sur \[**Créer une nouvelle liaison**\], donnez lui un nom, vous verrez ensuite le tableau de configuration \(image 2.5.1\)

![Figure 2.5.1 Nouvelle page de de configuration d&apos;une liaison](../../../.gitbook/assets/image%20%2897%29.png)

Dans cette page de configuration, vous pouvez spécifier une condition \(ou pas\). Dans cet intent, nous considérons que la condition est "salon". Ce qui veux dire que lorsque vous direz "allume la lumière dans le salon", cette liaison sera exécuté car vous avez prononcé le mot "salon", que vous avez indiquer "salon" comme condition et que cette liaison se trouve bien dans l'intent "LightSetjeedom"

Pour faire simple, Snips va écouter votre phrase puis reconnaitre que votre phrase appartient à l'un des intents de votre assistant. Snips va ensuite chercher dans cet intent s'il y a une configuration avec une ou plusieurs conditions qui correspond et si oui Jeedom va exécuter l'action associé.

Voici un exemple \(2.5.2\):

![Figure 2.5.2 Une liaison correctement configur&#xE9;](../../../.gitbook/assets/image%20%2860%29.png)

Comme vous pouvez le voir, nous avons ajouté une action comme vous avez l'habitude d'en créer avec Jeedom. Cette action sera exécuté si la condition est validé.  

{% hint style="warning" %}
Si vous ne mettez aucune condition, cette liaison sera exécuté lorsque vous direz "Allume la lumière" mais ne le sera pas lorsque vous direz "Allume la lumière dans le salon" .
{% endhint %}

Concernant le \[**Retour TTS**\], vous pouvez utilisez`{#}`afin d'indiquer où vous souhaitez insérer une variable spécifique dans la phrase.   
Par exemple, ici nous souhaiterions  que le TTS dise "Ok, j'allume la lumière dans le salon" donc nous allons écrire ceci: "Ok, j'allume la lumière dans le {\#}".   
Vous l'aurez compris {\#} prendra la valeur de la condition que vous avez indiqué.  

{% hint style="info" %}
Vous pouvez voir qu'il y'a un bouton \[**Tester le TTS**\]. Celui-ci vous permet de tester le TTS que vous écrivez. En revanche si vous n'avez pas encore prononcé de phrase vocalement qui aura activé cette liaison alors la valeur de la variable`#[Snips-Intents][lightsSetJeedom][house_room]#`sera NULL.

Vous entendrez donc "La Lumière dans le NULL a été allumée."
{% endhint %}

{% hint style="success" %}
Ceci marque la fin de la partie guidé "pas à pas". La suite consiste en une documentation des configurations et des différentes fonctionnalités du plugin.
{% endhint %}

## Documentation

### Adresse IP de l'appareil Snips principal

Vous pouvez très bien utilisez une RPI 3 pour votre appareil principal et des PI0 pour chaque pièce de votre habitation.

Pour la configuration de l'appareil principal, vous pouvez indiquez une adresse IP ou un bien un hostname. Si vous n'avez qu'un appareil Snips, il utilisera sa propre adresse IP, en revanche si vous avez des satellites \(dans chaque pièce par exemple\), vous devriez indiquer l'adresse IP de l'appareil **principal**.

Par exemple, si Snips et Jeedom fonctionnent sur le même appareil, vous pouvez mettre "**localhost**" ou bien "**127.0.0.1**".

### Retour TTS par défaut

Lorsque jeedom reçoit une requête de Snips mais qu'il ne trouve pas d'intents ni de liaisons associés vous pouvez lui faire jouer un message TTS par défaut pour qu'il vous indique ne pas avoir compris votre requête.   
Vous pouvez utilisez le format `[A|B|C]`tili pour séparer les différentes réponses. Le système choisira aléatoirement une des réponses pour que cela soit plus "humain". Exemple \(3.2.1\), ici la réponse sera "désolé, je n'ai pas compris" ou bien "désolé, je ne trouve pas les actions".

![Figure 3.2.1 Retour TTS par d&#xE9;faut](../../../.gitbook/assets/image%20%28103%29.png)

## Fonctionnalités

Cette partie de la documentation va principalement traité des fonctionnalités offerts par le plugin. Chaque sous-partie traitera d'une fonction spécifique.  

### A. Charger / Supprimer un assistant.

#### 1. Charger / Recharger

L'action de Charger ou de Recharger est appelé "Charger assistant" dans le plugin. Si vous voulez charger toutes vos liaisons précédemment créer dans le nouvel assistant, sélectionnez l'option "Recharger l'assistant avec les liaisons existantes".  
Si vous souhaitez chercher un nouvel assistant et recommencer vos liaisons du début, utilisez "Recharger l'assistant sans liaisons".   
Peu importe ce que vous choisissez, nous vous encourageons à toujours **exporter** vos liaisons avant de recharger afin d'avoir une sauvegarde au cas où.



![Figure 4.1.1 Screenshoot of loading options](../../../.gitbook/assets/image%20%2898%29.png)

Il est préférable de recharger un assistant uniquement si l'une des 3 conditions ci-dessous ont été modifiés dans votre assistant:

* Vous avez modifier le nom d'un intent
* Vous avez ajouter / supprimer un intent
* Vous avez modifier / supprimer les slots d'un intent

{% hint style="info" %}
Un assistant peut être constitué de plusieurs Apps \(JeedomOfficiel, méteo, calculette ect ...\) mais pour éviter de charger tous les intents de toutes ces applications dans Jeedom, nous ne chargeons que les intents qui contiennent le mot "jeedom" ou "Jeedom" dans le nom.   

Voici quelques exemples de nom d'intents valides:

* _Monnomdintentjeedom_
* _Jeedommonnomdintent_

_Par exemple, si vous avez une application de calculatrice qui n'a rien à faire avec Jeedom, il n'est pas nécessaire de charger ses intents et de surcharger visuellement la page, cette solution est donc un bon moyen de sélectionner les intents qui seront chargés de votre assistant à Jeedom._
{% endhint %}

#### 2. Suppression

Les opérations de suppression vont complètement enlever les appareils / commandes liés à Snips. Si vous souhaitez supprimer le plugin Snips, vous devriez trouver un bouton de  "suppression" sur la page de configuration du plugin.

### B. Condition "OR" / Synonymes

La technologie de Snips peut très bien gérer les synonymes. Vous pouvez gérer ça dans les valeurs de slots dans la console web. Mais si vous souhaitez ajouter plusieurs synonymes pour les conditions afin de déclencher une liaison en utilisant plusieurs synonymes, c'est tout à fait possible.

Par exemple, chez vous, le salon et la salle à manger peuvent partager la même lampe. Vous allez peut être avoir besoin de dire "hey snips, allume la lumière du salon" ou bien "hey snips, il fais sombre dans la salle à manger". Ces deux phrases ont deux valeurs de slots "house\_room" différents mais ils partagent la même action \(allumer la même lumière\).

Dans ce cas de figure, vous pouvez utiliser "salon" et "salle à manger" comme conditions dans la même liaison, il suffit de séparer les valeurs en utilisant une virgule: `,`

Vous n'avez donc pas à créer deux liaisons pour la même action, un exemple de liaison est affiché dans l'image 4.2.1

![Figure 4.2.1. Synonymes](../../../.gitbook/assets/image%20%2887%29.png)

### C. Ajustez la luminosité d'une lampe

Dans cet exemple, nous allons montrer comment effectuer une requête en utilisant des pourcentages tel que "**hey snips, mets la lumière à 50% dans le salon**". La principale différence entre "**allume la lumière**" et "**mets la lumière à un X%**"' est l'assignation d'une valeur de slot dans l'action à exécuter.

Cette opération dépend de l'intent`lightSetJeedom`, cliquons sur cet intent et ajoutons une nouvelle liaison. Pour cela, il faut choisir l'action de vos lampes correspondant à "changement de luminosité" \(si vos lampes vous le permettent\).   
Exemple ci-dessous \(4.3.1\).

![Figure 4.3.1 Configuration pour changer la luminosit&#xE9; d&apos;une lampe](../../../.gitbook/assets/image%20%2861%29.png)

Comme vous pouvez le voir, l'action utilise bien la commande `Luminosité` à la place de `on` ou `off` de l'exemple précédent. Pour cet exemple, nous utilisons la valeur de slot `intensity_percentage`

{% hint style="info" %}
Pourquoi y'a t'il un emplacement "value remapping" en dessous de la valeur de slot?

Certaines lampes utilisent leurs échelles d'intensités par exemple 0~100 ou 0~255. Le problème est que si vous prononcez "allume la lumière à 100% pour une lampe qui peut aller jusqu'à 255 alors vous ne serez qu'à la moitié de l'intensité. C'est pourquoi vous pouvez "remapper" les valeurs pour que 100% = 255.  

Pour le savoir, vous pouvez vérifier la fourchette de valeur de votre appareil dans Jeedom \(Par exemple Philips Hue va de 0 à  255, Fibaro FGD-2120 à 99 ect...\)
{% endhint %}

### D. Augmenter / Réduire l'intensité des lumières sans pourcentage

Un utilisateur devrait pouvoir dire "_**Mets plus de lumière dans la salle de bain**_" ou bien "**Baisse la lumière dans la chambre**". Pour cela, nous utilisons l'intent `LightsTurnUpJeedom`pour augmenter l'intensité et `LightsTurnDownJeedom`pour la réduire.  

En revanche, la difficulté réside lorsque vous voulez faire une liaison pour ces cas de figures car il n'existe généralement pas d'actions pour ceci avec vos lampes dans Jeedom. Vous devez donc créer un scénario qui va réaliser ces actions. Ne vous inquiétez pas, nous l'avons écrit pour vous :\)

Rendez vous dans la page des scénarios et créer en un nouveau. Il vous en faudra un pour réduire la lumière et un pour l'augmenter. Ces scénarios seront déclenchés par Snips donc pas besoin de créer un événement déclencheur.

![Figure 4.4.1 Sc&#xE9;nario &quot;r&#xE9;duire la lumi&#xE8;re&quot;](../../../.gitbook/assets/image%20%2852%29.png)

Puis cliquez sur l'onglet "scénario", et ajoutez un nouveau bloque de \[**code**\]. Ajoutez ce code:

```php
// User configuration
$VARS = array(
"OPERATION" => "DOWN", // Use "UP" (monter) or "DOWN" (réduire)
"LIGHTS" => array(
// Lumière 1
array(
    "LIGHT_BRIGHTNESS_VALUE" => "#[Apartment][Mirror Strip Right][Etat Luminosité]#",
    "LIGHT_BRIGHTNESS_ACTION" => "#[Apartment][Mirror Strip Right][Luminosité]#",
    "MIN_VALUE" => 0,
    "MAX_VALUE" => 255, // Intensité maximale
    "STEP_VALUE" => 0.2 // Pourcentage, si 20%, mettez 0.2. A chaque appel, cela réduira (ou montera) de 20% l'intensité
),
// Lumière 2 - facultatif
array(
    "LIGHT_BRIGHTNESS_VALUE" => "#[Apartment][Mirror Strip Left][Etat Luminosité]#",
    "LIGHT_BRIGHTNESS_ACTION" => "#[Apartment][Mirror Strip Left][Luminosité]#",
    "MIN_VALUE" => 0,
    "MAX_VALUE" => 255,	// Intensité maximale
    "STEP_VALUE" => 0.2
),
// Ajoutez autant de lumière que nécessaire - Si vous souhaitez controler plusieurs lampes avec la même commande

));

// Execution
snips::lightBrightnessShift(json_encode($VARS));
```

Dans ce code, deux lumières sont considérés comme un "groupe". Ces deux lampes seront donc controlé au même moment. Si vous en avez qu'une, vous pouvez retirez la deuxième:

```text
// Lumière 2 - facultatif
array(
    "LIGHT_BRIGHTNESS_VALUE" => "#[Apartment][Mirror Strip Left][Etat Luminosité]#",
    "LIGHT_BRIGHTNESS_ACTION" => "#[Apartment][Mirror Strip Left][Luminosité]#",
    "MIN_VALUE" => 0,
    "MAX_VALUE" => 255,	// Max brightness value
    "STEP_VALUE" => 0.2
),
```

Dans le code, si vous avez plus de 2 lumières dans la pièce, ajoutez simplement plus de configurations `array`après le commentaire:  `Ajoutez autant de lumière que nécessaire`

{% hint style="warning" %}
Utilisez les noms de vos lampes et non ceux données dans le code, ceux-ci servent juste d'exemple: _\#\[Apartment\]\[Mirror Strip Left\]\[Luminosité\]\#_
{% endhint %}

Un bloque de code avec un groupe pour 4 lumières ressemblerait donc à ça:

```php
// User configuration
$VARS = array(
"OPERATION" => "DOWN", // Use "UP" (monter) or "DOWN" (réduire)
"LIGHTS" => array(
// Lumière 1
array(
    "LIGHT_BRIGHTNESS_VALUE" => "#[Apartment][Mirror Strip Right][Etat Luminosité]#",
    "LIGHT_BRIGHTNESS_ACTION" => "#[Apartment][Mirror Strip Right][Luminosité]#",
    "MIN_VALUE" => 0,
    "MAX_VALUE" => 255, // Intensité maximale
    "STEP_VALUE" => 0.2 // Pourcentage, si 20%, mettez 0.2. A chaque appel, cela réduira (ou montera) de 20% l'intensité
),
// Lumière 2 - facultatif
array(
    "LIGHT_BRIGHTNESS_VALUE" => "#[Apartment][Mirror Strip Left][Etat Luminosité]#",
    "LIGHT_BRIGHTNESS_ACTION" => "#[Apartment][Mirror Strip Left][Luminosité]#",
    "MIN_VALUE" => 0,
    "MAX_VALUE" => 255,	// Intensité maximale
    "STEP_VALUE" => 0.2
),
// Lumière 3 - facultatif
array(
    "LIGHT_BRIGHTNESS_VALUE" => "#[Apartment][Mirror Strip center][Etat Luminosité]#",
    "LIGHT_BRIGHTNESS_ACTION" => "#[Apartment][Mirror Strip center][Luminosité]#",
    "MIN_VALUE" => 0,
    "MAX_VALUE" => 255,	// Intensité maximale
    "STEP_VALUE" => 0.2
),
// Lumière 4 - facultatif
array(
    "LIGHT_BRIGHTNESS_VALUE" => "#[Apartment][Mirror Strip back][Etat Luminosité]#",
    "LIGHT_BRIGHTNESS_ACTION" => "#[Apartment][Mirror Strip back][Luminosité]#",
    "MIN_VALUE" => 0,
    "MAX_VALUE" => 255,	// Intensité maximale
    "STEP_VALUE" => 0.2
),
// Ajoutez autant de lumière que nécessaire - Si vous souhaitez controler plusieurs lampes avec la même commande
));

// Execution
snips::lightBrightnessShift(json_encode($VARS));
```

{% hint style="warning" %}
Avant de sauvegarder, il y a plusieurs éléments dans \[**User configuration**\] dans le code qui doivent être modifié. Il faut le faire pour chaque lumière "array".
{% endhint %}

* `"OPERATION"`
  * Mettez **DOWN** ou **UP** pour réduire ou monter.
* `"LIGHT_BRIGHTNESS_VALUE"`  
  * Cette commande contient la valeur d'intensité de la lumière.
* `"LIGHT_BRIGHTNESS_ACTION"`
  * Cette action actionne la lampe à la bonne valeur d'intensité.
* `"MIN_VALUE"` and `"MAX_VALUE"`
  * Ces deux valeurs définissent l'échelle d'intensité \(généralement 0~99 ou 0~255\).
* `"STEP_VALUE"`
  * Cette valeur corresponds à la valeur que vous voulez modifier à chaque "pas". Si vous voulez réduire par pas de 10% mettez 0.1 ect.

{% hint style="danger" %}
Attention, ne changez rien en dessous de \[**Execution**\].
{% endhint %}

Un code correctement utilisé pour augmenter l'intensité de 2 lumières devrait ressembler à l'image 4.4.2:

![Figure 4.4.2 Un bloque de code pour augmenter l&apos;intensit&#xE9; de 2 lumi&#xE8;res.](../../../.gitbook/assets/image%20%2892%29.png)

{% hint style="info" %}
Ce code est en faite une action pour augmenter/réduire l'intensité. Si vous avez compris comment cela fonctionne, vous pouvez utilisez ce bloque de code pour une autre pièce de votre maison ou créer votre propre code pour d'autres actions.
{% endhint %}

### E. TTS dynamique

Le plugin Snips vous permet de créer des réponses TTS facilement. Vous n'avez qu'à utiliser `{#}` pour remplacer l'endroit dans la phrase où vous souhaiteriez avoir la valeur comme dans l'exemple 4.5.1.

![Figure 4.5.1 Exemple de TTS dynamique](../../../.gitbook/assets/image%20%2883%29.png)

Avec le TTS dynamique, vous pouvez aussi "remapper" les valeurs.   
Par exemple, les lumières ou les capteurs de vos fenêtres ont généralement une valeur binaire "0" ou "1" pour indiquer si elles sont allumés/ouvertes ou éteintes/fermés.   

Vous ne souhaitez pas que le TTS dise "La lumière dans la chambre est 1 mais plutôt "La lumière dans la chambre est allumée". Pour cela vous pouvez "remapper" une valeur à un mot comme dans l'exemple 4.5.2

![Figure 4.5.2 Exemple de &quot;remapping&quot; de valeur](../../../.gitbook/assets/image%20%2827%29.png)

### F. Réponse TTS aléatoire

Si vous avez mis une réponse TTS, celle-ci sera toujours jouée si la liaison a été exécuté. Si vous n'en voulez pas, laissez le TTS vide.   
En revanche avoir une seule phrase TTS peut vous sembler un peu "robotique".    

Le TTS aléatoire vous permet de jouer plusieurs réponses de manière aléatoire. À chaque fois que la liaison sera exécutée, une des réponses TTS sera jouée aléatoirement parmi celles que vous avez configuré.

Pour cette fonction, vous devez utilisez `[ ]`pour contenir les réponses TTS dynamiques et `|` pour séparer chaque réponse, comme dans l'exemple 4.6.1

![Figure 4.6.1 Exemple de TTS al&#xE9;atoire](../../../.gitbook/assets/image%20%28104%29.png)

### G. Utilisez le TTS de Snips dans vos scénarios

Le lecteur TTS de Snips est construit comme une action dans le système Jeedom ce qui veux dire que vous pouvez aussi l'utiliser dans d'autres plugins / scénarios comme dans l'exemple 4.7.1

![Figure 4.7.1 Jouez &quot;bonjour&quot; &#xE0; partir du satellite de la chambre](../../../.gitbook/assets/image%20%2844%29.png)

### H. importer / Exporter des configurations \(liaisons\)

Un plugin Snips bien configuré peu avoir plusieurs liaisons pour chaque intent. Nous vous encourageons fortement à les exporter avant de recharger un assistant. Cela va exporter un fichier Json avec toutes vos liaisons pour que vous puissiez les réimportez si nécessaire.

Si vous souhaitez avoir accès à ces fichiers, vous pouvez le faire en utilisant SSH, ils se trouvent ici:

`/var/www/html/plugins/snips/config_backup/xxx.json`

Ou vous pouvez les télécharger dans votre navigateur \(remplacez "hostname" par le vôtre\):

`http://hostname/plugins/snips/config_backup/xxx.json`

### I. Combiner une liaison Snips avec un scénario Jeedom

Dans la page de configuration des liaisons, vous pouvez remarquer que les liaisons Snips ne sont en faite qu'une sorte de sous-fonction d'un scénario Jeedom.

Par conséquent, au lieu de créer plusieurs liaisons, vous pouvez avoir un/plusieurs scénario\(s\) pour gérer les requêtes vocales pour un ou plusieurs intent\(s\). Pour cela, nous devons passer tous les paramètres du plugin Snips au scénario correspondant.  
Voici une liste des "tags":

| Tags | Description |
| :--- | :--- |
| `#plugin#` | La valeur est toujours: snips |
| `#identifier#` | La valeur est composé de Plugin::IntentId::Binding |
| `#intent#` | La valeur est Intent\_Name |
| `#siteId#` | La valeur est Site\_Id |
| `#query#` | La valeur est le contenu de la requête |
| `#SLOTS_NAME#` | Ce tag est dynamique en fonction du/des slot\(s\) contenu dans les requêtes |

L'exemple suivant utilise 2 intents \(`LightsSetJeedom` et `LightTurnOffJeedom`\), chaque intent a 2 liaisons dans la page de configuration, l'un pour gérer les requêtes conditionnel \(où vous précisez le nom de la pièce\), et le deuxième pour les non-conditionnels \(où vous dites simplement "allumez la lumière" par exemple, sans préciser la pièce\)

Les liaisons pour `LightsSetJeedom` est montré dans l'image 4.9.1 et 4.9.2. Nous allons utiliser qu'un scénario pour gérer ces deux intents donc pour `LightsTurnOffJeedom`  la configuration est la même.

![Figure 4.9.1 Configuration non-conditionnel](../../../.gitbook/assets/image%20%289%29.png)

![Figure 4.9.2 Configuration conditionnel](../../../.gitbook/assets/image%20%2828%29.png)


Comme vous pouvez le constater, la configuration conditionnel prends tous les mots possibles dans le slot "house\_room" contrairement au non-conditionnel qui n'a aucune valeur. Le point commun de ces deux liaisons est qu'elles appellent toutes les deux le même scénario `LightsHandler.`

Nous allons maintenant créer le scénario `LightsHandler` qui va gérer toutes les requêtes. Il n'y a rien de particulier pour ce scénario, il n'y a même pas besoin de déclencheur puisque il sera déclencher par la liaison Snips. Voici un exemple d'un scénario fonctionnant avec ces bindings:.

![Figure 4.9.3](../../../.gitbook/assets/image%20%2880%29.png)

Il y a deux sections principales dans ce scénario, le premier `IF/THEN/ELSE` bloc et les deux derniers `IF/THEN/ELSE`blocs.

Le premier sert à gérer les situations par défaut. Par exemple s'il n y a pas de `house_room`utilisé cela utilisera alors  `site_name` par défaut. S'il y a une valeur, cela la mettra dans une variable que le reste du code pourra utilisé.

Le deuxième permet de r"agir aux différentes requêtes en fonction des intents et valeur de slots. Il n'y a pas d'actions spécifiés dans cet exemple, vous pouvez en ajouter en fonction de vos pièces.

### J. Effacer les valeurs d'informations de commande

Cela vous permet d'insérer les liaisons Snips ainsi que les valeurs de slots avec vos scénarios existants.

Prenons un exemple, nous avons un scénario qui, suite en fonction du type de clic sur un bouton Xiaomi \(click, double\_click, long\_press\_click\) différentes actions seront effectués et ce que vous ayez cliqué sur el bouton ou bien prononcé une requête vocale comme dans l'exemple 4.10.1

![Figure 4.10.1 Sc&#xE9;nario utilis&#xE9; pour allumer une Tv, home cin&#xE9;ma ...](../../../.gitbook/assets/image%20%2819%29.png)

Dans l'emplacement des conditions du scénario, la valeur de slot Snips peut être ajouté après une condition "OR" ou "ET". Ce scénario sera déclenché avec le click d'un bouton ou bien par une requête vocale Snips.

En revanche cela crée un problème car certains boutons / appareils ne sont pas rafraîchis une fois utilisé.  
Par exemple, le bouton "Xiaomi" peut être pressé de 3 manières différentes, et la dernière pression sera assigné à une variable jusqu'à la prochaine utilisation. Cela risque de déclencher le reste de votre scénario car il pensera que le bouton a été cliqué. La solution est donc "d'effacer" la valeur de la variable avec un petit code:

```php
$VAR = '#[Apartment][Cub button][click]#';  // commande qui sera effacé

$cmd = cmd::byString($VAR);
$cmd->setCache('value', '');
```

**Alternativement,** vous pouvez utilisez une fonction de déclenchement dans le scénario pour ne pas avoir à effacer votre valeur.  ****

Au lieu d'avoir une condition simple comme ceci:

`#[salon][netflix_button][clic]#=="click" OU #[snips-intents][TurnOnJeedom][device_name]#=="télévision"`

Vous pouvez faire cela:

`(trigger(#[salon][netflix_button][clic]#)==1 ET #[salon][netflix_button][clic]#=="click") OU (trigger(#[snips-intents][TurnOnJeedom][device_name]#)==1 ET #[snips-intents][TurnOnJeedom][device_name]#=="télévision" )`

### K. Commande "Ask"

Parfois, vous souhaitez que votre scénario vous pose une questions et puis déclencher une action en fonction de votre réponse.

Par exemple, Snips TTS pourrait vous demander si vous souhaitez allumer la lumière au coucher du soleil. Vous pouvez alors faire un scénario qui vérifie si le "flag" coucher du soleil est bien mis, si oui, cela déclenchera la command" ask". Exemple 4.11.1

![Figure 4.11.1 Exemple d&apos;une commande &quot;ask&quot; avec Snips ](../../../.gitbook/assets/image%20%2853%29.png)

Pour cela, nous devons créer un intent qui contiendra les réponses après une question TTS.   
Sur la console Snips, forker l'App JeedomOfficiel, créer un nouvel intent `GetAskResponseJeedom` \(vous pouvez l'appeler comme bon vous semble mais **ATTENTION**, **il vous faut mettre "Jeedom" quelque part dans le nom**\) comme l'image 4.11.2.

![Figure 4.11.2 GetAskResponseJeedom intent ](../../../.gitbook/assets/image%20%2877%29.png)

Nous utiliserons cet intent pour avoir toutes les réponses possibles, vous devrez donc créer un slot pour représenter ces réponses, celui ci sera surement un "custom" car vous l'aurez personnalisé.   
Dans l'exemple nous l'avons appeler `answer.`Nous avons par ailleurs coché "required slot" car nous voulons forcément une réponse à ce slot, s'il n'a pas compris votre réponse, il vous reposera la question.

Vous pouvez voir ici un exemple avec "Oui" et "Non" donc le nom de notre slot est "OuiNon". Vous pouvez ensuite ajouter des valeurs ainsi que des synonymes. 4.11.3

![Figure 4.11.3 R&#xE9;ponse de Slot](../../../.gitbook/assets/image%20%2865%29.png)

Avec toutes ces modifications, vous devez re-déployer votre assistant et le recharger sur le plugin Jeedom.

Dans votre plugin Snips, vous allez maintenant avoir l'intent`GetAskResponseJeedom` chargé. Vous pouvez vous rendre sur la page de configuration de l'intent et créer une liaison qui va assigner la réponse à une variable Jeedom. Voir l'exemple 4.11.4 avec une variable appelé "answer":

![Figure 4.11.4 GetAskResponseJeedom intent binding configration ](../../../.gitbook/assets/image%20%28101%29.png)

Après toutes ces étapes, vous pouvez commencer à construire votre propre scénario pour utiliser cette fonction. Vous devrez remplir cette commande "ask" avec toutes les informations nécessaires:

<table>
  <thead>
    <tr>
      <th style="text-align:left">Input box</th>
      <th style="text-align:left">Value</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align:left">Question</td>
      <td style="text-align:left">Message de la question</td>
    </tr>
    <tr>
      <td style="text-align:left">Réponse</td>
      <td style="text-align:left">Réponse de l'intent et le nom du slot. Suivez ce format:<code>[YOUR_SNIPS_USERNAME:GetAskResponseJeedom](answer)</code> Attention,
        vous devez utilisé votre pseudonyme Snips venant de votre console.</td>
    </tr>
    <tr>
      <td style="text-align:left">Variable</td>
      <td style="text-align:left">La variable pour laquelle vous sauvegardé la valeur de la réponse, elle
        devrait être identique à celle de la liaison<code>GetAskResoonseJeedom</code>
      </td>
    </tr>
    <tr>
      <td style="text-align:left">délai</td>
      <td style="text-align:left">This is the timeout protecting jeedom from infinite waiting. 10 seconds
        is recommended.</td>
    </tr>
    <tr>
      <td style="text-align:left">Commande</td>
      <td style="text-align:left">
        <p>The command to perform this function. It's your snips TTS devices, called <code>ask</code>
        </p>
        <p>Typically you should put something like <code>#[Snips-Intents][Snips-TTS-default][ask]#</code>
        </p>
      </td>
    </tr>
  </tbody>
</table>Voici un exemple d'une commande "ask" bien configuré:

![Figure 4.11.5 Exemple d&apos;une commande &quot;ask&quot;](../../../.gitbook/assets/image%20%286%29.png)

{% hint style="warning" %}
N'oubliez pas d'utiliser votre username Snips avant votre intent sinon cela ne fonctionnera pas.
{% endhint %}

Voici une démo d'un scénario pour illustrer un ensemble complet:

![Figure 4.11.6 Demo d&apos;un sc&#xE9;nario avec la commande &quot;ask&quot;](../../../.gitbook/assets/image%20%2850%29.png)

## JeedomOfficial APP

Voici la liste des intents contenu dans l'app:

* **OpenCoverJeedom**: Pour ouvrir vos fenêtres, stores, ect.
* **CloveCoverJeedom**: ****Pour fermer vos fenêtres, stores, ect.
* **WindowDevicePauseJeedom**: Pour arrêter les fenêtres, stores là où ils sont.
* **ThermostatShiftJeedom**: Pour augmenter/réduire la température.
* **ThermosatSetJeedom**: Pour mettre votre thermostat à une valeur spécifique.
* **EntityStateValueJeedom**: Pour collecter des informations sur l'état de vos capteurs \(ex: ma fenêtre est-elle ouverte? Quel est le taux d'humidité dans la chambre?", "L 'alarme est-elle activée? ect.
* **TurnOnJeedom**: Pour allumer un appareil.
* **TurnOffJeedom**: Pour éteindre un appareil.
* **VolumeDownJeedom**: Pour réduire le volume de vos appareils \(TV, radios, Sonos ...\).
* **VolumeUpJeedom**: Pour augmenter le volume de vos appareils \(TV, radios, Sonos ...\).
* **VolumeMuteJeedom**: Pour muter vos appareils \(TV, radios, Sonos ...\).
* **TvChannelJeedom**: Pour choisir la bonne chaine sur votre TV.
* **LightsSetJeedom**: Pour mettre vos lumières à une intensité spécifique.
* **LightsTurnOffJeedom**: Pour éteindre vos lumières.
* **LightShiftUpJeedom**: Pour augmenter l'intensité de vos lumières sans donner de valeur.
* **LightShiftDownJeedom**: Pour diminuer l'intensité de vos lumières sans donner de valeur.

Vous pouvez tout à fait "forker" l'app, la modifier, ajouter des intents, des valeur de slots afin de la personnaliser.

En plus de cette application, vous pouvez ajouter autant d'applications que vous le souhaitez \(public ou les vôtres\).