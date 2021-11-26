<?php

/**
 * Description of dispatch_model
 * -------------------------------------
 * Put this class on project at 22/04/2013
 * @author Pakkaphon P.(PHP PG)
 * Create by NetBean IDE 7.3
 * SWA WMS PLUS Project.
 * Use with dispatch module function
 * Use Codeinigter Framework with combination of css and js.
 * --------------------------------------
 */
class dispatch_model extends CI_Model {

    //put your code here
    function __construct() {
        parent::__construct();
    }

    public function find_order ($flow_id) {
        $this->db->join("STK_T_Order_Detail d", "o.Order_Id = d.Order_Id");
        $this->db->where("o.Flow_Id = " . $flow_id);
        $query = $this->db->get("STK_T_Order o");
        return $query->result();
    }

}

?>
