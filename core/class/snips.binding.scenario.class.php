<?php
require_once dirname(__FILE__) . '/snips.utils.class.php';
require_once dirname(__FILE__) . '/snips.class.php';

class SnipsBindingScenario
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
    private $isTagConfidenceScore;

    function __construct($scenario = array())
    {
        $this->scenario_id = $scenario['scenario'];
        $this->action = $scenario['action'];
        $this->user_tags = $scenario['user_tags'];
        $this->isTagPlugin = $scenario['isTagPlugin'];
        $this->isTagIdentifier = $scenario['isTagIdentifier'];
        $this->isTagIntent = $scenario['isTagIntent'];
        $this->isTagSlots = $scenario['isTagSlots'];
        $this->isTagSiteId = $scenario['isTagSiteId'];
        $this->isTagQuery = $scenario['isTagQuery'];
        $this->isTagConfidenceScore = $scenario['isTagConfidenceScore'];
    }

    function execute()
    {
        SnipsUtils::logger('scenario: '. scenario::byId($this->scenario_id)->getName(), 'info');
        $options = array();
        $options['scenario_id'] = $this->scenario_id;
        $options['action'] = $this->action;
        $options['tags'] = $this->get_all_scenario_tags();

        return scenarioExpression::createAndExec('action', 'scenario', $options);
    }

    private function get_all_scenario_tags()
    {
        $tags = array();
        $args = arg2array($thsi->user_tags);

        $run_variable = snips::get_run_variable();
        $payload = $run_variable['payload'];

        // attach user tags
        foreach ($args as $key => $value) {
            $tags['#' . trim(trim($key), '#') . '#'] = $value;
        }
        // attach system tags
        if ($this->isTagPlugin) {
            $tags['#plugin#'] = 'snips';
        }

        if ($this->isTagIdentifier) {
            $tags['#identifier#'] = 'snips::'.$payload['intent']['intentName'].'::Callback';
        }

        if ($this->isTagIntent) {
            $tags['#intent#'] = $payload['intent']['intentName'];
        }

        if ($this->isTagSiteId) {
            $tags['#siteId#'] = $payload['siteId'];
        }

        if ($this->isTagQuery) {
            $tags['#query#'] = $payload['input'];
        }

        if ($this->isTagConfidenceScore) {
            $tags['#confidenceScore#'] = $payload['intent']['confidenceScore'];
        }

        if ($this->isTagSlots) {
            foreach ($run_variable['slots_values'] as $slots_name => $value) {
                $tags['#'.$slots_name.'#'] = $value;
            }
        }

        return $tags;
    }
}