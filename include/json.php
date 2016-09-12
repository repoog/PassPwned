<?php

class JSON
{
    // json output object
    private $api;
    // json format output
    public $json;
    
    public function __construct() {
        // Avoid warning about creating default object with empty value.
        if (!isset($api))
            $this->api = new stdClass();
        $this->api->state = "Fail";
    }
    
    public function set_success() {
        $this->api->state = "Success";
    }
    
    public function set_item($item_name, $item_value) {
        $this->api->$item_name = $item_value; 
    }
    
    public function output() {
        $this->json = json_encode($this->api);
        echo $this->json;
    }
}