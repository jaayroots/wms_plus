<?php

class onhand_R_To_R_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }


    // function getBalanceJWD(){
      
    //     $sql = "EXEC Result_Onhand_R_To_R";
    //     $query = $this->db->query($sql);
    //     $result = $query->result();
     
    //     return $result;
    // }

    function getBalanceJWD(){
        $this->db->select("STK_T_Onhand_R_To_R.Product_Code");
        $this->db->select("SUM(STK_T_Onhand_R_To_R.Balance_Qty) As Balance_Qty");
        $this->db->select("STK_M_Product.Product_NameEN");
        $this->db->select("CTL_M_UOM_Template_Language.public_name UOM");
        $this->db->from("STK_T_Onhand_R_To_R");
        $this->db->join("STK_M_Product", "STK_T_Onhand_R_To_R.Product_Code = STK_M_Product.Product_Code", "LEFT");
        $this->db->join("CTL_M_UOM_Template_Language", "STK_M_Product.Standard_Unit_Id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id AND CTL_M_UOM_Template_Language.[language] = 'eng'", "LEFT");
        $this->db->where("CONVERT(VARCHAR(10),STK_T_Onhand_R_To_R.Available_Date,103) = CONVERT(VARCHAR(10),DATEADD(DAY,-1,GETDATE()),103)");
        $this->db->group_by("STK_T_Onhand_R_To_R.Product_Code");
        $this->db->group_by("STK_M_Product.Product_NameEN");
        $this->db->group_by("CTL_M_UOM_Template_Language.public_name");
        $this->db->group_by("CTL_M_UOM_Template_Language.public_name");
        $this->db->order_by("STK_T_Onhand_R_To_R.Product_Code ASC");

         $query = $this->db->get();
        $result = $query->result_array();
     // p($this->db->last_query()); exit;
        return $result;

    }
    
}
    ?>