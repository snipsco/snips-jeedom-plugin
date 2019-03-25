<?php

class SnipsFrontendRender
{
    private $tts_eqs;
    private $intent_eqs;

    private $manage_icons = array(
        '{{Load Assistant}}' => array(
            'ajax_action' => 'reload',
            'icon' => '/plugins/snips/3rdparty/icons/rocket.png'
        ),
        '{{Export Binding}}' => array(
            'ajax_action' => 'exportConfigration',
            'icon' => '/plugins/snips/3rdparty/icons/cloud-upload.png'
        ),
        '{{Import Binding}}' => array(
            'ajax_action' => 'importConfigration',
            'icon' => '/plugins/snips/3rdparty/icons/cloud-download.png'
        ),
        '{{Configuration}}' => array(
            'ajax_action' => 'gotoPluginConf',
            'icon' => '/plugins/snips/3rdparty/icons/gear.png'
        )
    );

    function __construct()
    {
        ; // reserved for the next update
    }

    function get_manage_icons()
    {
        ; // reserved for the next update
    }

    function get_tts_cards()
    {
        ; // reserved for the next update
    }

    function get_intent_cards()
    {
        ; // reserved for the next update
    }
}

class ManageItem
{
    private $name;
    private $ajax_action;
    private $icon;

    function __construct($name, $ajax_action, $icon)
    {
        $this->name = $name;
        $this->ajax_action = $ajax_action;
        $this->icon = $icon;
    }

    function render()
    {
        return <<<html
            <div
                class="cursor eqLogicAction {$this->ajax_action}"
                style="
                    text-align: center;
                    background-color: #ffffff;
                    height: 120px;
                    margin-bottom: 10px;
                    padding: 5px;
                    border-radius: 2px;
                    width: 160px;
                    margin-left: 10px;"
            >
                <img
                    src="{$this->icon}"
                    height="95px"
                    width="95px"
                />
                <br>
                <span
                    style="
                        font-size: 1.1em;
                        position:relative;
                        top: 15px;
                        word-break: break-all;
                        white-space: pre-wrap;
                        word-wrap: break-word;"
                >
                    {{{$this->name}}}
                </span>
            </div>
html;
    }
}

class TtsItem
{
    const MASTER_ICON = '';
    const SATELLITE_ICON = '';

    private $name;
    private $id;
    private $is_master;
    private $icon;

    private $opacity_str;

    function __construct($tts_eq)
    {
        $this->name = $tts_eq->getConfiguration('siteName');
        $this->id = $tts_eq->getLogicalId();

        $master_name = config::byKey('masterSite', 'snips', 'default');
        $this->is_master = ($this->name == $master_name) ? true : false;

        $this->icon = $this->is_master ? self::MASTER_ICON : self::SATELLITE_ICON;

        $this->opacity_str = jeedom::getConfiguration('eqLogic:style:noactive');
    }

    function render()
    {
        return <<<html
        <div
            class="cursor testSite"
            data-site="{$this->name}"
            data-eqLogic_id="{$this->id}"
            style="
                text-align: center;
                background-color: #ffffff;
                height: 160px;
                padding: 5px;
                border-radius: 2px;
                width: 160px;
                margin-left: 10px;
                {$this->opacity_str}"
        >
            <img
                src="{$this->icon}"
                height="95px"
                width="95px"
            />
            <br>
            <span
                style="font-size: 1.1em;
                position:relative;
                top: 15px;
                word-break: break-all;
                white-space: pre-wrap;
                word-wrap: break-word"
            >
                <span class="badge">
                    {$this->name}
                </span>
            </span>
        </div>
html;
    }
}

class IntentItem
{
    private $card;
    private $side_bar;

    function __construct()
    {
        ; // reserved for the next update
    }
}