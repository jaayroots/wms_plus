<?php

// Create by Ton! 20130614 

class product_info_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getSplitInfo($itemId) {
        $this->db->select("dbo.STK_T_Order.Flow_Id, dbo.STK_T_Order.Document_No, dbo.STK_T_Order.Doc_Refer_Ext, dbo.STK_T_Order.Doc_Type
            , dbo.STK_T_Order.Process_Type, dbo.STK_T_Order.Vendor_Id, dbo.STK_T_Order_Detail.*, dbo.STK_M_Product.Product_NameEN, dbo.STK_M_Product.Product_NameTH, STK_M_Product.PutAway_Rule");
        $this->db->from("dbo.STK_T_Order");
        $this->db->join("dbo.STK_T_Order_Detail", "dbo.STK_T_Order.Order_Id = dbo.STK_T_Order_Detail.Order_Id");
        $this->db->join("dbo.STK_M_Product", "dbo.STK_T_Order_Detail.Product_Code = dbo.STK_M_Product.Product_Code");
        $this->db->where("dbo.STK_T_Order_Detail.Item_Id", $itemId);
        $query = $this->db->get();
        return $query;
    }

    function getDetailSplitInfo($itemId) {
        $this->db->select("*
            , DAY(Product_Mfd) AS dayMFD, MONTH(Product_Mfd) AS monthMFD, YEAR(Product_Mfd) AS yearMFD
            , DAY(Product_Exp) AS dayEXP, MONTH(Product_Exp) AS monthEXP, YEAR(Product_Exp) AS yearEXP");
        $this->db->from("dbo.STK_T_Order_Detail");
        $this->db->where("dbo.STK_T_Order_Detail.Item_Id", $itemId);
        $this->db->where("dbo.STK_T_Order_Detail.Active", ACTIVE);
        $this->db->order_by("dbo.STK_T_Order_Detail.Item_Id");
        $query = $this->db->get();
        return $query;
    }

}
?>

