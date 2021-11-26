<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of putaway
 *
 * @author P'Zex
 */
class putawaymodel extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getPutList($module, $DocReferExt = NULL) {
        $conf = $this->config->item('_xml');
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];


        $this->db->select("DISTINCT STK_T_Workflow.Flow_Id as Id
            , STK_T_Workflow.Present_State
            , STK_T_Order.Document_No
            , STK_T_Order.Doc_Refer_Ext
            , STK_T_Order.Doc_Refer_Int
            , STK_T_Order.Order_Id
            , CTL_M_Company.Company_NameEN AS Vendor_Name
            , STK_T_Order.Is_urgent
            , STK_T_Order.Create_Date");
        $this->db->from("STK_T_Workflow");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->join("CTL_M_Company", "CTL_M_Company.Company_Id = STK_T_Order.Source_Id", "left");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id");
        $this->db->where_in("SYS_M_Stateedge.Module", $module);
        $this->db->where("(STK_T_Order_Detail.Actual_Location_Id IS NULL)");
        if (!empty($DocReferExt)) :
            $this->db->where("STK_T_Order.Doc_Refer_Ext", $DocReferExt);
        endif;
        $this->db->where("STK_T_Order_Detail.Active", "Y");
        $this->db->where("STK_T_Order_Detail.Confirm_Qty > ", "0");

        if($conf_pallet):
             $this->db->where("STK_T_Order_Detail.Pallet_Id", NULL);
        endif;

        $this->db->order_by("STK_T_Order.Is_urgent desc, STK_T_Order.Create_Date asc");

        $query = $this->db->get();
//        echo $this->db->last_query(); exit;

        return $query;
    }

    function getPaByflowId($flowId) {// Add by Ton! 20130513
        //STK_T_Order_Detail, STK_M_Product
        $this->db->select("
            DISTINCT STK_T_Order_Detail.Item_Id
            , STK_T_Order_Detail.Product_Code
            , STK_M_Product.Product_NameEN
            , STK_T_Order_Detail.Reserv_Qty
            , STK_T_Order_Detail.Confirm_Qty
            , STK_T_Order_Detail.Order_Id
            , STK_T_Order_Detail.Product_Status
            , STK_T_Order_Detail.Product_Lot
            , STK_T_Order_Detail.Product_Serial
            , STK_T_Order_Detail.Product_Mfd
            , STK_T_Order_Detail.Product_Exp
            ");
        $this->db->from("STK_T_Order_Detail");
        $this->db->join("STK_M_Product", "STK_T_Order_Detail.Product_Code = STK_M_Product.Product_Code AND STK_M_Product.Active = 'Y'");
        $this->db->join("STK_T_Order", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id");
        $this->db->where("STK_T_Order.Flow_Id", $flowId);
        $this->db->where("STK_T_Order_Detail.Actual_Location_Id", NULL);
        $this->db->where("STK_T_Order_Detail.Active", "Y");
        $this->db->where("STK_T_Order_Detail.Pallet_Id", NULL);
        $this->db->where("STK_T_Order_Detail.Confirm_Qty >", 0);
        $query = $this->db->get();
        return $query;
    }

    function getPaByProdCode($sku = NULL) {// Add by Ton! 20130521
        //STK_T_Order_Detail, STK_M_Product
        $this->db->select("DISTINCT STK_T_Order_Detail.Item_Id, STK_T_Order_Detail.Product_Code, STK_M_Product.Product_NameEN
            , STK_T_Order_Detail.Confirm_Qty, STK_T_Order_Detail.Order_Id, STK_T_Order.Flow_Id, STK_T_Order_Detail.Product_Status");
        $this->db->from("STK_T_Order_Detail");
        $this->db->join("STK_M_Product", "STK_T_Order_Detail.Product_Code = STK_M_Product.Product_Code");
        $this->db->join("STK_T_Order", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id");
//        $this->db->where("STK_T_Order.Flow_Id",$flowId);
        // Add by Ton! 20131115
        $this->db->join("STK_T_Workflow", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->where("SYS_M_Stateedge.Module", 'putaway');

        $this->db->where("STK_T_Order_Detail.Actual_Location_Id", NULL);
        if (!empty($sku)):
            $this->db->where("STK_T_Order_Detail.Product_Code", $sku);
            $this->db->or_where_in("STK_M_Product.Internal_Barcode1", $prodCode);
            $this->db->or_where_in("STK_M_Product.Internal_Barcode2", $prodCode);
        endif;
        $this->db->where("STK_T_Order_Detail.Active", "Y");
        $query = $this->db->get();
        return $query;
    }

    function countPaByOrderID($orderID) {// Edit by Ton! 20130521
        $this->db->select("*");
        $this->db->from("STK_T_Order_Detail");
        $this->db->where("STK_T_Order_Detail.Order_Id", $orderID);
//        $this->db->where("STK_T_Order_Detail.Actual_Location_Id", NULL);
        $this->db->where("(STK_T_Order_Detail.Actual_Location_Id IS NULL AND STK_T_Order_Detail.Confirm_Qty <> 0)"); // Edit by Ton! 20140211
        $this->db->where("STK_T_Order_Detail.Active", "Y");
        $query = $this->db->get();
        return $rowcount = $query->num_rows();
    }

    //<<<<<----- START - ADD by Ton! 20130913 ----->>>>>
    function getPa($orderID = NULL, $itemID = NULL) {
        $this->db->select("domain.Dom_Id , STK_T_Order_Detail.*, STK_T_Order.Document_No, STK_T_Order.Doc_Refer_Ext
            , STK_T_Order.Flow_Id, STK_M_Product.ProductCategory_Id");
        $this->db->from("STK_T_Order");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id", "left");
        $this->db->join("STK_M_Product", "STK_T_Order_Detail.Product_Id = STK_M_Product.Product_Id", "left");
        $this->db->join("SYS_M_Domain domain", "domain.Dom_Code = STK_M_Product.ProductCategory_Id", "left");
        if (!empty($orderID)):
            $this->db->where("STK_T_Order_Detail.Order_Id", $orderID);
        endif;
        if (!empty($itemID)):
            $this->db->where("STK_T_Order_Detail.Item_Id", $itemID);
        endif;
        $this->db->where("STK_T_Order_Detail.Active", "Y");
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    
      function getPaProduct_Code($orderID = NULL, $prod_code = NULL) {
        $this->db->select("domain.Dom_Id , STK_T_Order_Detail.*, STK_T_Order.Document_No, STK_T_Order.Doc_Refer_Ext
            , STK_T_Order.Flow_Id, STK_M_Product.ProductCategory_Id");
        $this->db->from("STK_T_Order");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id", "left");
        $this->db->join("STK_M_Product", "STK_T_Order_Detail.Product_Id = STK_M_Product.Product_Id", "left");
        $this->db->join("SYS_M_Domain domain", "domain.Dom_Code = STK_M_Product.ProductCategory_Id", "left");
        if (!empty($orderID)):
            $this->db->where("STK_T_Order_Detail.Order_Id", $orderID);
        endif;
        if (!empty($prod_code)):
            $this->db->where("STK_T_Order_Detail.Product_Code", $prod_code);
        endif;
        $this->db->where("STK_T_Order_Detail.Active", "Y");
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    
        
    
    //<<<<<----- END - ADD by Ton! 20130913 ----->>>>>
}

?>