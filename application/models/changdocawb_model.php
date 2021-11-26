<?php

class changdocawb_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function search_document($data) {

        //  error_report(E_ALL);
        $this->db->select("o.Order_id, o.Document_No, o.Doc_Refer_Ext, o.Doc_Refer_AWB");
        $this->db->from("STK_T_Order o");
        $this->db->join("STK_T_Workflow w ", "w.Flow_Id = o.Flow_Id");
        $this->db->where("o.Doc_Refer_Ext", $data);
        $this->db->where("w.Present_State not in (-1)");
        $query = $this->db->get();
        // p($this->db->last_query()); exit;
        $result = $query->result();
        return $result;
    }
 
    public function updatedocumentAWB($order_id,$Doc_Refer_AWB) {
    
        $update = array(
            'Doc_Refer_AWB' => $Doc_Refer_AWB 
        );
        $where = array(
            'order_id' => $order_id
        );
        $this->db->where($where);
        $this->db->update('STK_T_Order', $update);
        $afftectedRows = $this->db->affected_rows();
        $result = ($afftectedRows == 0 ? FALSE : TRUE);
        return $result;


}


}
