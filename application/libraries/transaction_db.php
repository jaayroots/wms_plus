<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class transaction_db {

    function __construct() {

    }

    /**
    * Manual Transaction Start
    */
    public function transaction_start () {
        $CI = & get_instance();
    	$CI->db->trans_begin();
    }

    /**
    * Manual Transaction Commit
    */
    public function transaction_commit() {
        $CI = & get_instance();
    	$CI->db->trans_commit();
    }

    /**
    * Manual Transaction Roll Back
    */
    public function transaction_rollback() {
        $CI = & get_instance();
    	$CI->db->trans_rollback();
    }

    public function transaction_status(){
        $CI = & get_instance();
        $CI->db->trans_status();
    }
    /**
    * Manual Transaction End
    */
    public function transaction_end() {
        $CI = & get_instance();
    	if ($CI->db->trans_status() === FALSE)
    	{
    		$CI->db->trans_rollback();
    	} else {
    		$CI->db->trans_commit();
    	}
    }

    public function set_transaction_level ( $level = "SNAPSHOT") {
        $CI = & get_instance();
        $CI->db->query("SET TRANSACTION ISOLATION LEVEL " . $level);
    }

}