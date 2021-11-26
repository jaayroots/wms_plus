<?php

class pending_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

//   --#ISSUE 2158
//   --#DATE:2012-08-29
//   --#BY:KIK
//   --#ปัญหา:เนื่องมาจากการแก้ไขให้ข้อมูลจาก order table ใส่ใน inbound ตอน approve recive จึงทำให้ ข้อมูลใน table inbound มีอยู่ตั้งแต่ยังไม่ผ่านการ approve putaway จึงทำให้สามารถ unlock pending ได้ตั้งแต่ยังไม่ได้ approve putaway
//   --#สาเหตุ:ของเดิม process flow ผิด เพราะว่าเอาข้อมูลจาก order table มาใส่ inbound table ตอน approve putaway การเขียน sql ในลักษณะนี้จึงไม่ผิด แต่เมื่อแก้ไขขั้นตอนทั้งหมด sql ส่วนนี้จึงได้รับผลกระทบด้วย
//   --#วิธีการแก้:ยังคงดึงข้อมูลมาจาก inbound table เช่นเดิม แต่เพิ่มเงื่อนไขเพื่อเช็คว่าข้อมูลนั้นผ่านการ approve putaway มาแล้ว จึงจะสามารถ unlock pendding ได้ 
//   -- START Old Comment Code #ISSUE 2158
//    function getPendingOrder(){
//		#Create where clause
//		$this->db->select('STK_T_Relocate_Detail.Document_No');
//		$this->db->from('STK_T_Relocate_Detail');
//		$query = $this->db->get();
//		$result = $query->result();
//		$where_clause = array();
//		if(count($result)>0){
//			foreach($result as $rows){
//				$where_clause[] = $rows->Document_No;
//			}
//		}
//		unset($result);
//		$this->db->select("STK_T_Inbound.Document_No
//			,STK_T_Inbound.Doc_Refer_Ext
//			,STK_T_Inbound.Doc_Refer_Int
//			,CONVERT(VARCHAR(10),STK_T_Inbound.Receive_Date,103) AS Receive_Date
//		");
//		$this->db->from("STK_T_Inbound");
//		$this->db->where("STK_T_Inbound.Is_Pending",ACTIVE);
//		$this->db->where("STK_T_Inbound.Renter_Id",$this->config->item('renter_id'));
//		$this->db->where("STK_T_Inbound.Owner_Id",$this->config->item('owner_id'));
//		if(count($where_clause)>0){
//			$this->db->where_not_in("STK_T_Inbound.Document_No",$where_clause);
//		}
//		$this->db->group_by("STK_T_Inbound.Document_No,STK_T_Inbound.Doc_Refer_Ext,STK_T_Inbound.Doc_Refer_Int,STK_T_Inbound.Receive_Date");
//		$query = $this->db->get();
//		//echo $this->db->last_query(); 
//		return $query;
//	}
//     -- END Old Comment Code #ISSUE 2158
//     -- START New Code #ISSUE 2158

    function getPendingOrder() {
        #Create where clause
        // Use distinct for perfomance
/*
        $this->db->select('DISTINCT STK_T_Relocate_Detail.Document_No');
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
*/
        $this->db->select("STK_T_Inbound.Document_No
                            ,STK_T_Inbound.Doc_Refer_Ext
                            ,STK_T_Inbound.Doc_Refer_Int
                            ,CONVERT(VARCHAR(10),STK_T_Inbound.Receive_Date,103) AS Receive_Date
                    ");
        $this->db->from("STK_T_Inbound");
        $this->db->join('STK_T_Order', 'STK_T_Inbound.Document_No = STK_T_Order.Document_No', 'INNER'); // #ISSUE 2158 เช็คเงื่อนไขเพิ่มเติมจาก STK_T_Order
        $this->db->join('STK_T_Workflow', 'STK_T_Order.Flow_Id = STK_T_Workflow.Flow_Id', 'INNER');     // #ISSUE 2158 เช็คเงื่อนไขเพิ่มเติมจาก STK_T_Workflow
        $this->db->where("STK_T_Workflow.Process_Id = '1' AND STK_T_Workflow.Present_State = '-2'");      // #ISSUE 2158 เช็คเพื่อให้ดึงข้อมูลเฉพาะที่ผ่านการ approve putaway มาแล้วเท่านั้น!
        $this->db->where("STK_T_Inbound.Is_Pending", ACTIVE);
//        $this->db->where("STK_T_Inbound.Renter_Id", $this->config->item('renter_id'));
//        $this->db->where("STK_T_Inbound.Owner_Id", $this->config->item('owner_id'));
//        $this->db->where("STK_T_Inbound.Renter_Id", $this->session->userdata('renter_id'));
//        $this->db->where("STK_T_Inbound.Owner_Id", $this->session->userdata('owner_id'));
//        if (count($where_clause) > 0) {
//            $this->db->where_not_in("STK_T_Inbound.Document_No", $where_clause);
//        }
	$this->db->where("STK_T_Inbound.Document_No Not In (SELECT DISTINCT Document_No From STK_T_Relocate_Detail)");
        $this->db->group_by("STK_T_Inbound.Document_No,STK_T_Inbound.Doc_Refer_Ext,STK_T_Inbound.Doc_Refer_Int,STK_T_Inbound.Receive_Date");

        $query = $this->db->get();
//	echo $this->db->last_query(); 
        return $query;
    }

//     -- END New Code #ISSUE 2158     

    function getPendingDocument($document_list = '') {
        if ($document_list != ''):
            $this->db->select("*");
            $this->db->from("STK_T_Inbound");
            $this->db->where("Is_Pending", ACTIVE);
            #$this->db->where("renter_id",$this->config->item('renter_id'));
            #$this->db->where("owner_id",$this->config->item('owner_id'));
            $this->db->where_in("Document_No", $document_list);
            $query = $this->db->get();
            //echo $this->db->last_query(); 
            return $query;
        else:
            return NULL;
        endif;
    }

    function getPendingFlow($flow_id) {
        $this->db->select("STK_T_Workflow.Process_Id as Id
            , STK_T_Workflow.Present_State
            , STK_T_Workflow.Process_Id
            , SYS_M_Process.Process_Type
            , STK_T_Relocate.Order_Id
            , STK_T_Relocate.Assigned_Id ");
        $this->db->from("STK_T_Workflow");
        $this->db->join("SYS_M_Process", "STK_T_Workflow.Process_Id = SYS_M_Process.Process_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("STK_T_Relocate", "STK_T_Workflow.Document_No = STK_T_Relocate.Doc_Relocate");
        $this->db->where("STK_T_Workflow.Flow_Id", $flow_id);
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query->result();
    }

    function getPendingDetail($order_id) {
        $this->db->select("*
			,CONVERT(VARCHAR(10),STK_T_Relocate_Detail.Product_Mfd,103) as Product_Mfd
			,CONVERT(VARCHAR(10),STK_T_Relocate_Detail.Product_Exp,103) as Product_Exp
			,CONVERT(VARCHAR(10),STK_T_Relocate_Detail.Receive_Date,103) as Receive_Date
			,STK_M_Product.Product_NameEN AS Full_Product_Name
			,SUBSTRING(STK_M_Product.Product_NameEN,0,30)  AS Product_Name
			,L1.Location_Code AS Suggest_Location
			,L2.Location_Code AS Actual_Location
			,L3.Location_Code AS Old_Location
			,S1.public_name AS Unit_Value
			,S2.Dom_Code   AS Status_Code
			,S2.Dom_EN_Desc AS Status_Value
			,S3.Dom_Code   AS Sub_Status_Code
			,S3.Dom_EN_Desc AS Sub_Status_Value
			,L1.Location_Code AS Suggest_Location
			,L2.Location_Code AS Actual_Location
			,L3.Location_Code AS Old_Location
                        ,Price_Per_Unit
                        ,Unit_Price_Id
                        ,All_Price
                        ,unitprice.Dom_EN_Desc unitprice_name
		");
        $this->db->from("STK_T_Relocate");
        $this->db->join("STK_T_Relocate_Detail", "STK_T_Relocate_Detail.Order_Id = STK_T_Relocate.Order_Id");
        $this->db->join("STK_M_Product", "STK_T_Relocate_Detail.Product_Id=STK_M_Product.Product_Id");
        $this->db->join("STK_M_Location L1 ", "STK_T_Relocate_Detail.Suggest_Location_Id = L1.Location_Id", "LEFT");
        $this->db->join("STK_M_Location L2 ", "STK_T_Relocate_Detail.Actual_Location_Id  = L2.Location_Id", "LEFT");
        $this->db->join("STK_M_Location L3 ", "STK_T_Relocate_Detail.Old_Location_Id = L3.Location_Id", "LEFT");

        // Edit By Akkarapol, 10/01/2014, เปลี่ยนจากการ join กับตาราง SYS_M_Domain เพื่อหา Unit Name เป็นการ join กับ  CTL_M_UOM_Template_Language แทน เพื่อรองรับกับการทำ UOM
//        $this->db->join("SYS_M_Domain S1", "STK_T_Relocate_Detail.Unit_Id = S1.Dom_Code AND S1.Dom_Host_Code='PROD_UNIT'");
        $this->db->join("CTL_M_UOM_Template_Language S1", "STK_T_Relocate_Detail.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'");
        // End Edit By Akkarapol, 10/01/2014, เปลี่ยนจากการ join กับตาราง SYS_M_Domain เพื่อหา Unit Name เป็นการ join กับ  CTL_M_UOM_Template_Language แทน เพื่อรองรับกับการทำ UOM

        $this->db->join("SYS_M_Domain S2", "STK_T_Relocate_Detail.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS' AND S2.Dom_Active = 'Y'");
        $this->db->join("SYS_M_Domain S3", "STK_T_Relocate_Detail.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS' AND S3.Dom_Active = 'Y'", "LEFT");
        $this->db->join("SYS_M_Domain unitprice", "STK_T_Relocate_Detail.Unit_Price_Id = unitprice.Dom_Code AND unitprice.Dom_Active = 'Y'", "LEFT"); //ADD BY POR 2014-01-17 เพิ่มให้ดึง DESC ของ unit price ออกมาด้วย
        $this->db->where("STK_T_Relocate.Order_Id", $order_id);
        $query = $this->db->get();
//        echo $this->db->last_query(); 
        return $query->result();
    }

    function getOrderByDocNo($document_no) {
        $this->db->select("*");
        $this->db->from("STK_T_Order");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id  = STK_T_Order.Order_Id");
        $this->db->where("STK_T_Order.Document_No", $document_no);
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query->result();
    }

    function getProductCodeByProductId($Product_Id) {
        $this->db->select("STK_M_Product.Product_Code");
        $this->db->from("STK_M_Product");
        $this->db->where("STK_M_Product.Product_Id", $Product_Id);
        $query = $this->db->get();
        return $query->result();
    }

    function updatePendingOrder($order, $where) {
        $this->db->where($where);
        $this->db->update("STK_T_Relocate", $order);
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    function updatePendingDetail($order_detail, $where) {
        $this->db->where($where);
        $this->db->update("STK_T_Relocate_Detail", $order_detail);
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    function addNewLocation($order_detial) {
        $this->db->insert("STK_T_Inbound", $order_detial);
        return $this->db->insert_id();
    }

    function inactiveOldLoation($item_id) {
        $this->db->set("ACTIVE", INACTIVE);
        $this->db->where("Item_Id", $item_id);
        $this->db->update("STK_T_Inbound");
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    function updateUnlockPending($document_no) {
        $this->db->set('Unlock_Pending_Date', 'GETDATE()', FALSE);
        $this->db->where("Document_No", $document_no);
        $this->db->where("Active", ACTIVE);
//		$this->db->where("Is_Pending",INACTIVE); // Comment By Akkarapol, 04/09/2013, คอมเม้นต์ทิ้งเนื่องจากไม่จำเป็นต้อง filter ตัวนี้
        $this->db->update("STK_T_Inbound");
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }
    
    //CREATE BY POR 2013-12-17 สร้าง function สำหรับดึงรายละเอียดของรายการ Relocate
    function getRelocateDetail($itemId=""){
        $this->db->select("dbo.STK_T_Relocate.Flow_Id
        ,dbo.STK_T_Relocate.Doc_Type, dbo.STK_T_Relocate.Process_Type, dbo.STK_T_Relocate_Detail.*
        ,dbo.STK_M_Product.Product_NameEN, dbo.STK_M_Product.Product_NameTH,lo_sug.Location_Code loc_sug,lo_old.Location_Code loc_old
        ,convert(varchar(10),dbo.STK_T_Relocate_Detail.Product_Mfd,103) as P_Mfd,convert(varchar(10),dbo.STK_T_Relocate_Detail.Product_Exp,103) as P_Exp,Dom_EN_Desc as Sub_Status_Value");
        $this->db->from("dbo.STK_T_Relocate");
        $this->db->join("dbo.STK_T_Relocate_Detail", "dbo.STK_T_Relocate.Order_Id = dbo.STK_T_Relocate_Detail.Order_Id","LEFT");
        $this->db->join("dbo.STK_M_Product", "dbo.STK_T_Relocate_Detail.Product_Code = dbo.STK_M_Product.Product_Code AND STK_M_Product.Active='Y'","LEFT");
        $this->db->join("dbo.STK_M_Location lo_sug", "STK_T_Relocate_Detail.Suggest_Location_Id = lo_sug.Location_Id","LEFT");
        $this->db->join("dbo.STK_M_Location lo_old", "STK_T_Relocate_Detail.Old_Location_Id = lo_old.Location_Id","LEFT");
        $this->db->join("dbo.SYS_M_Domain dom","dbo.STK_T_Relocate_Detail.Product_Sub_Status = dom.Dom_Code AND dom.Dom_Host_Code='SUB_STATUS' AND dom.Dom_Active = 'Y'","LEFT"); //ADD BY POR 2014-03-11 เพิ่มให้ดึงชื่อ sub_status ขึ้นมาด้วย
        if(!empty($itemId)):
            $this->db->where("dbo.STK_T_Relocate_Detail.Item_Id", $itemId);
        endif;
        
        $query = $this->db->get();
        //p($this->db->last_query()); exit();
        return $query;
    }
    //END ADD
}

?>
