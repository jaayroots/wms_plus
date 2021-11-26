<?php

class repackage_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getRepackageOrder() {
        #Create where clause
        $this->db->select('STK_T_Relocate_Detail.Document_No');
        $this->db->from('STK_T_Relocate_Detail');
        $query = $this->db->get();
        $result = $query->result();
        $where_clause = array();
        if (count($result) > 0) {
            foreach ($result as $rows) {
                $where_clause[] = $rows->Document_No;
            }
        }
        unset($result);
        $this->db->select("STK_T_Inbound.Document_No
			,STK_T_Inbound.Doc_Refer_Ext
			,STK_T_Inbound.Doc_Refer_Int
			,CONVERT(VARCHAR(10),STK_T_Inbound.Receive_Date,103) AS Receive_Date
			,DATEDIFF(DAY,STK_T_Inbound.Receive_Date,getdate()) AS Diff_Date
		");
        $this->db->from("STK_T_Inbound");
        $this->db->where("STK_T_Inbound.Is_Repackage", ACTIVE);
        $this->db->where("STK_T_Inbound.Renter_Id", $this->config->item('renter_id'));
        $this->db->where("STK_T_Inbound.Owner_Id", $this->config->item('owner_id'));
        if (count($where_clause) > 0) {
            $this->db->where_not_in("STK_T_Inbound.Document_No", $where_clause);
        }
        $this->db->group_by("STK_T_Inbound.Document_No,STK_T_Inbound.Doc_Refer_Ext,STK_T_Inbound.Doc_Refer_Int,STK_T_Inbound.Receive_Date");
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function getRepackageDocument($document_list) {
        $this->db->select("*");
        $this->db->from("STK_T_Inbound");
        $this->db->where("Is_Repackage", ACTIVE);
        #$this->db->where("renter_id",$this->config->item('renter_id'));
        #$this->db->where("owner_id",$this->config->item('owner_id'));
        $this->db->where_in("Document_No", $document_list);
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function getRepackageFlow($flow_id) {
        $this->db->select("STK_T_Workflow.Process_Id as Id
							,STK_T_Workflow.Present_State
							,STK_T_Workflow.Process_Id 
							,SYS_M_Process.Process_Type
							,STK_T_Relocate.Order_Id
							,STK_T_Relocate.Assigned_Id
							");
        $this->db->from("STK_T_Workflow");
        $this->db->join("SYS_M_Process", "STK_T_Workflow.Process_Id = SYS_M_Process.Process_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("STK_T_Relocate", "STK_T_Workflow.Document_No = STK_T_Relocate.Doc_Relocate");
        $this->db->where("STK_T_Workflow.Flow_Id", $flow_id);
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query->result();
    }

    function getRepackageDetail($order_id) {
        $this->db->select("*
			,CONVERT(VARCHAR(10),STK_T_Relocate_Detail.Product_Mfd,103) as Product_Mfd
			,CONVERT(VARCHAR(10),STK_T_Relocate_Detail.Product_Exp,103) as Product_Exp
			,CONVERT(VARCHAR(10),STK_T_Relocate_Detail.Receive_Date,103) as Receive_Date
			,STK_M_Product.Product_NameEN AS Full_Product_Name
			,SUBSTRING(STK_M_Product.Product_NameEN,0,30)  AS Product_Name
			,L1.Location_Code AS Suggest_Location
			,L2.Location_Code AS Actual_Location
			,L3.Location_Code AS Old_Location
			,S1.Dom_EN_Desc AS Unit_Value
			,S2.Dom_Code   AS Status_Code
			,S2.Dom_EN_Desc AS Status_Value
			,S3.Dom_Code   AS Sub_Status_Code
			,S3.Dom_EN_Desc AS Sub_Status_Value
			,L1.Location_Code AS Suggest_Location
			,L2.Location_Code AS Actual_Location
			,L3.Location_Code AS Old_Location
		");
        $this->db->from("STK_T_Relocate");
        $this->db->join("STK_T_Relocate_Detail", "STK_T_Relocate_Detail.Order_Id = STK_T_Relocate.Order_Id");
        $this->db->join("STK_M_Product", "STK_T_Relocate_Detail.Product_Id=STK_M_Product.Product_Id");
        $this->db->join("STK_M_Location L1 ", "STK_T_Relocate_Detail.Suggest_Location_Id = L1.Location_Id", "LEFT");
        $this->db->join("STK_M_Location L2 ", "STK_T_Relocate_Detail.Actual_Location_Id  = L2.Location_Id", "LEFT");
        $this->db->join("STK_M_Location L3 ", "STK_T_Relocate_Detail.Old_Location_Id = L3.Location_Id", "LEFT");

        $this->db->join("SYS_M_Domain S1", "STK_T_Relocate_Detail.Unit_Id = S1.Dom_Code AND S1.Dom_Host_Code='PROD_UNIT' AND S1.Dom_Active = 'Y'");
        $this->db->join("SYS_M_Domain S2", "STK_T_Relocate_Detail.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS' AND S2.Dom_Active = 'Y'");
        $this->db->join("SYS_M_Domain S3", "STK_T_Relocate_Detail.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS' AND S3.Dom_Active = 'Y'", "LEFT");

        $this->db->where("STK_T_Relocate.Order_Id", $order_id);
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query->result();
    }

    function getOrderByDocNo($document_no) {// ???
        $this->db->select("*");
        $this->db->from("STK_T_Order");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id  = STK_T_Order.Order_Id");
        $this->db->where("Document_No", $document_no);
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query->result();
    }

    function getProductCodeByProductId($id) {// Duplicate Function !!
        $this->db->select("STK_M_Product.Product_Code");
        $this->db->from("STK_M_Product");
        $this->db->where("STK_M_Product.Product_Id", $id);
        $query = $this->db->get();
        return $query->result();
    }

    function updateRepackageOrder($order, $where) {// Duplicate Function !!
        $this->db->where($where);
        $this->db->update("STK_T_Relocate", $order);
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    function updateRepackageDetail($order_detail, $where) {// Duplicate Function !!
        $this->db->where($where);
        $this->db->update("STK_T_Relocate_Detail", $order_detail);
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    function addNewLocation($order_detial) {// Duplicate Function !!
        $this->db->insert("STK_T_Inbound", $order_detial);
        return $this->db->insert_id();
    }

    function inactiveOldLoation($item_id) {// Duplicate Function !!
        $this->db->set("ACTIVE", INACTIVE);
        $this->db->where("Item_Id", $item_id);
        $this->db->update("STK_T_Inbound");
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    function updateActivityVAS($flow_id, $vas_id) {
        $sql = "UPDATE STK_T_Inbound SET Activity_Involve = (Activity_Involve + ',' + '" . $vas_id . "'  ) 
				  WHERE Flow_Id = " . $flow_id . " ";
        $query = $this->db->query($sql);
    }

}

?>