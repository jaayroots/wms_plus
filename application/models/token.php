<?php

/**
 * Description of Token
 * -------------------------------------
 * Put this class on project at 22/04/2013 
 * @author KRIP 
 * Create by Eclipse
 * SWA WMS PLUS Project.
 * Use with token module function
 * --------------------------------------
 */
class Token extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function register($params) {
    	$query = $this->db->insert("SYS_L_Token", $params);
		return $this->db->affected_rows();
    }
    
    public function validate($token) {
    	$this->db->where('token', $token);
    	$query = $this->db->get("SYS_L_Token");
    	return $query->result();
    }

}
