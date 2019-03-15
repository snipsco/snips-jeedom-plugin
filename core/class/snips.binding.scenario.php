<?php

class SnipsBindingScrnario
{
    private $scenario_id;
    private $action;

    private $user_tags;

    private $isTagPlugin;
    private $isTagIdentifier;
    private $isTagIntent;
    private $isTagSlots;
    private $isTagSiteId;
    private $isTagQuery;
    private $isTagProbability;

    private $payload;

    function __construct($_scenario, $_payload)
    {
        $this->payload = $_payload;
        $this->scenario = $_scenario['scenario_id'];
        $this->action = $_scenario['action'];
        $this->user_tags = $_scenario['tags']; 
    }

    public function execute()
    {
        $options = array();
        $options['scenario_id'] = $this->scenario_id;
        $options['action'] = $this->action;
        $options['tags'] = $this->get_all_scenario_tags();

        $ret_msg = scenarioExpression::createAndExec('action', 'scenario', $options);
    }

    private function get_all_scenario_tags()
    {
        $tags = array();
        $args = arg2array($thsi->user_tags);
        /* attach user tags */
        foreach ($args as $key => $value) {
            $tags['#' . trim(trim($key), '#') . '#'] = $value;
        }
        /* attach system tags */
        if($this->isTagPlugin)
            $tags['#plugin#'] = 'snips';

        if($this->isTagIdentifier)
            $tags['#identifier#'] = 'snips::'.$_payload->{'intent'}->{'intentName'}.'::Callback';

        if($this->isTagIntent)
            $tags['#intent#'] = substr($_payload->{'intent'}->{'intentName'}, strpos($_payload->{'intent'}->{'intentName'},':')+1);

        if($this->isTagSiteId)
            $tags['#siteId#'] = $_payload->{'siteId'};

        if($this->isTagQuery)
            $tags['#query#'] = $_payload->{'input'};

        if($this->isTagProbability)
            $tags['#probability#'];

        if($this->isTagSlots)
            ;// To do
            // foreach ($slots_values_org as $slots_name => $value)
            //     $tags['#'.$slots_name.'#'] = $value;

        return $tags;
    }
}