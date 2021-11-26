<?php // Create by Ton! 20130503     ?>
<?php

class receive_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function showProductStatus() {
        $select_p_status = $this->p->selectProductStatus();
        $pd_status_list = $select_p_status->result();
        $optionPS = genOptionDropdown($pd_status_list, "SYS");
        echo json_encode($optionPS);
    }
    
    /**
     * comment by kik : 20140805 ส่วนนี้ยังทำไม่สมบูรณ์ เพราะว่าต้องพักไปทำส่วนอื่นๆ ก่อน
     */
//    function check_receive_pallet_return($orderId) {
//        
//        $sql = "EXEC sp_PA_suggestLocation_Pallet ?,?,?,?,?,?,?";
//    	$parameter = array(
//    			"@order_id" 			=> $orderId
//    	);
//
//    	$query = $this->db->query($sql, $parameter);
//    	$result = $query->result();
//    	return $result;
//        
//        return $orderId;
//    }

//    Comment Out by Ton! Not Used. 20131030
//    function getReceiveAll() {
//        $this->db->select("f.Flow_Id AS Id, st.State_No, f.Document_No, f.Doc_Refer_Ext, f.Doc_Refer_Int, 
//            o.Estimate_Action_Date, wh1.Warehouse_NameEN AS Source_Id, wh2.Warehouse_NameEN AS Destination_Id");
//        $this->db->from("dbo.STK_T_Workflow AS f INNER JOIN dbo.SYS_M_Stateedge AS ste ON f.Process_Id = ste.Process_Id AND f.Present_State = ste.From_State 
//            INNER JOIN dbo.SYS_M_State AS st ON f.Present_State = st.State_No AND f.Process_Id = st.Process_Id 
//            INNER JOIN dbo.STK_T_Order AS o ON f.Document_No = o.Document_No 
//            LEFT OUTER JOIN dbo.STK_M_Warehouse AS wh1 ON o.Source_Id = wh1.Warehouse_Id 
//            LEFT OUTER JOIN dbo.STK_M_Warehouse AS wh2 ON o.Destination_Id = wh2.Warehouse_Id");
//        $this->db->where('ste.Module', 'receive');
//        $this->db->order_by("st.State_No, f.Document_No");
//        $query = $this->db->get();
//        return $query;
//    }
//    
//    Comment Out by Ton! Not Used. 20131030
//    function getReceiveOrder($product_code, $query_column = "") {
//        if ("" == $query_column) {
//            $this->db->select("dbo.STK_T_Order_Detail.Item_Id AS Id, dbo.STK_T_Order_Detail.Product_Code, dbo.STK_M_Product.Product_NameEN, 
//            dbo.STK_T_Order_Detail.Product_License, dbo.STK_T_Order_Detail.Product_Lot, dbo.STK_T_Order_Detail.Product_Serial, 
//            dbo.STK_T_Order_Detail.Product_Mfd, dbo.STK_T_Order_Detail.Product_Exp, dbo.STK_T_Order_Detail.Reserv_Qty");
//        } else {
//            $this->db->select($query_column);
//        }
//        $this->db->from("dbo.STK_T_Workflow LEFT OUTER JOIN dbo.STK_T_Order ON dbo.STK_T_Workflow.Document_No = dbo.STK_T_Order.Document_No 
//            RIGHT OUTER JOIN dbo.STK_T_Order_Detail ON dbo.STK_T_Order.Order_Id = dbo.STK_T_Order_Detail.Order_Id 
//            RIGHT OUTER JOIN dbo.STK_M_Product ON dbo.STK_M_Product.Product_Code = dbo.STK_T_Order_Detail.Product_Code");
//        if ($product_code != "") {
//            $this->db->where_in('dbo.STK_T_Order_Detail.Product_Code', $product_code);
//        }
//        $this->db->where('dbo.STK_T_Order_Detail.Product_Code IS NOT NULL');
//        $this->db->order_by("dbo.STK_T_Workflow.Flow_Id");
//        $query = $this->db->get();
//        return $query;
//    }
}

?>