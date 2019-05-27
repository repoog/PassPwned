<?php

/**
 * Data querying API class
 * Created by: Cooper Pei
 * Created date: 2016/9/1
 */

require(dirname(__FILE__) . '/db.php');
require_once(dirname(dirname(__FILE__)) . '/config.php');

class API
{
    // Database operation class instance object
    private $db_obj;
    // Account type array
    private $type_ary = array('email', 'mobile', 'qq', 'idcard');
    // Default type (username, nickname and real name) classes
    private $default_type;
    // Data query condition part
    private $condition_part;
    // Data set index
    private $data_index = 0;
    // Data set result
    private $data_set = array();
    // Site index table name
    private $index_table;
    // Table item table name
    private $item_table;
    // API call table name
    private $call_table;
    
    public function __construct()
    {
        $this->db_obj = DB::get_instance();
        $this->index_table = DB_PREFIX . 'site_index';
        $this->item_table = DB_PREFIX . 'site_item';
        $this->call_table = DB_PREFIX . 'api_call';
    }

    // Get parameter type within 'email','mobile','qq','id card'.
    private function get_parameter_type($account)
    {
        if (filter_var($account, FILTER_VALIDATE_EMAIL)) {      // Verify email type
            return $this->type_ary[0];
        }elseif (preg_match("/^1[34578]\d{9}$/", $account)){	// Verify mobile type
            return $this->type_ary[1];
        }elseif (preg_match("/^\d{5,15}$/", $account)) {      // Verify qq number type
            return $this->type_ary[2];
        }elseif (preg_match("/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{4}$/", $account) 
            || preg_match("/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/", $account)) {   // Verify id card type
            return $this->type_ary[3];
        }
    }

    // Get database table name set and site information according to account type.
    private function get_table_set($account)
    {
        $account_type = $this->get_parameter_type($account);
        switch ($account_type) {
            // Email
            case $this->type_ary[0]:
                $table_sql = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info " . 
                            "FROM " . $this->item_table . " a, " . $this->index_table . " b " . 
                            "WHERE a.s_id = b.s_id AND a.email_item = 1";
                break;
            // Mobile
            case $this->type_ary[1]:
                $table_sql = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info " .
                            "FROM " . $this->item_table . " a, " . $this->index_table . " b " .
                            "WHERE a.s_id = b.s_id AND a.mobile_item = 1";
                break;
            // QQ
            case $this->type_ary[2]:
                $table_sql = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info " .
                            "FROM " . $this->item_table . " a, " . $this->index_table . " b " .
                            "WHERE a.s_id = b.s_id AND a.qq_item = 1";
                break;
            // Id card number
            case $this->type_ary[3]:
                $table_sql = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info " .
                            "FROM " . $this->item_table . " a, " . $this->index_table . " b " .
                            "WHERE a.s_id = b.s_id AND a.idcard_item = 1";
                break;
            // Other types such as username, nickname and real name
            default:
                $table_sql['username'] = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info " .
                                        "FROM " . $this->item_table . " a, " . $this->index_table . " b " .
                                        "WHERE a.s_id = b.s_id AND a.username_item = 1";
                $table_sql['nickname'] = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info " .
                                        "FROM " . $this->item_table . " a, " . $this->index_table . " b " .
                                        "WHERE a.s_id = b.s_id AND a.nickname_item = 1";
                $table_sql['realname'] = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info " .
                                        "FROM " . $this->item_table . " a, " . $this->index_table . " b " .
                                        "WHERE a.s_id = b.s_id AND a.realname_item = 1";
                $this->condition_part = array("username" => "username = ?",
                                        "nickname" => "nickname = ?",
                                        "realname" => "realname = ?");

                break;
        }
       
        // Handle condition except default type.
        if (!isset($this->condition_part)) {
            $this->condition_part = $account_type . " = ?";
        }

        if (!is_array($table_sql)) {
            $table_set = $this->db_obj->query($table_sql);
        }else {
            foreach ($table_sql as $key => $value) {
                $table_set[$key] = $this->db_obj->query($table_sql[$key]);
            }
        }

        return $table_set;
    }
    
    // Search all data according to account.
    public function search($account)
    {
        $table_set = $this->get_table_set($account);

        // Regular account type
        if (count($table_set) == count($table_set, 1)) {
            for($i=0; $i<count($table_set); $i++) {
                $this->get_site_data($table_set[$i], $account);
            }
        }else {
            foreach ($table_set as $key => $table) {
                $this->default_type = $key;
                $this->get_site_data($table, $account);
            }
        }
        
        // Update api call amount
        $api_call_sql = "UPDATE " . $this->call_table . " SET count = count + 1";
        $this->db_obj->update($api_call_sql);
        
        return $this->data_set;
    }
    
    // Get particular site data item.
    private function get_site_data($site_item, $account)
    {
        $site_info[] = array('name' => $site_item['site_name'], 'domain' => $site_item['site_url'], 'intro' => $site_item['site_info']);
        if (!is_array($this->condition_part)) {
            $data_sql = "SELECT * FROM `" . $site_item['table_name'] . "` WHERE " . $this->condition_part;
        }else {
            $data_sql = "SELECT * FROM `" . $site_item['table_name'] . "` WHERE " . $this->condition_part[$this->default_type];
        }

        $data_set = $this->db_obj->query($data_sql, array('s', $account));
        if ($data_set != NULL) {
            $this->data_set[$this->data_index] = array($site_info, $data_set);
            $this->data_index++;
        }
    }

    // Get site data and api call amount.
    public function site_amount()
    {
        $amount_sql = "SELECT count(1) AS site_amount, SUM(a.data_amount) AS data_amount, b.count AS call_amount 
                      FROM " . $this->index_table . " a, " . $this->call_table . " b";
        $amount_set = $this->db_obj->query($amount_sql);
        if ($amount_set != NULL) {
            return $amount_set;
        }
    }
}