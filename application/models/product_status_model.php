<?php

class product_status_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    // select Estimate_Action_Date and Actual_Action_Date : by kik : 2013-11-26
    // add select column Is_urgent for ISSUE 3312 : by kik : 20140121
    function getChangeStatusFlow($flow_id) {
        $this->db->select("STK_T_Workflow.Process_Id as Id
							,STK_T_Workflow.Present_State
							,STK_T_Workflow.Process_Id 
							,STK_T_Workflow.Document_No
                                                        ,CONVERT(VARCHAR(10),STK_T_Relocate.Estimate_Action_Date,103) as Estimate_Action_Date
                                                        ,CONVERT(VARCHAR(10),STK_T_Relocate.Actual_Action_Date,103) as Actual_Action_Date
							,SYS_M_Process.Process_Type
							,STK_T_Relocate.Order_Id
							,STK_T_Relocate.Assigned_Id
							,STK_T_Relocate.Remark
                                                        ,STK_T_Relocate.Is_urgent
                                                        ,STK_T_Relocate.Custom_Doc_Ref
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

    #add DP_Type_Pallet for ISSUE 3334 : by kik : 20140217
    function getChangeStatusDetial($order_id) {
        $this->db->select(" 
			STK_T_Relocate_Detail.Product_Code
			,STK_T_Relocate_Detail.Product_Status
			,STK_T_Relocate_Detail.Product_Sub_Status
			,STK_T_Relocate_Detail.Product_Lot
			,STK_T_Relocate_Detail.Product_Serial
			,STK_T_Relocate_Detail.Pallet_Id
			,STK_T_Relocate_Detail.Reserv_Qty
			,STK_T_Relocate_Detail.Confirm_Qty
			,STK_T_Relocate_Detail.Unit_Id
			,STK_T_Relocate_Detail.Picking_By
			,STK_T_Relocate_Detail.Inbound_Item_Id
			,STK_T_Relocate_Detail.Suggest_Location_Id
			,STK_T_Relocate_Detail.Actual_Location_Id
			,STK_T_Relocate_Detail.Old_Location_Id
			,STK_T_Relocate_Detail.Product_Id
			,STK_T_Relocate_Detail.Item_Id
                        ,STK_T_Relocate_Detail.DP_Type_Pallet 
			,CONVERT(VARCHAR(10),STK_T_Relocate_Detail.Product_Mfd,103) as Product_Mfd
			,CONVERT(VARCHAR(10),STK_T_Relocate_Detail.Product_Exp,103) as Product_Exp
			,CONVERT(VARCHAR(10),STK_T_Relocate_Detail.Receive_Date,103) as Receive_Date
			,STK_T_Relocate_Detail.Remark
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
			,I.Balance_Qty
                        ,I.Receive_Qty
                        ,I.PD_Reserv_Qty
                        ,I.Dispatch_Qty
                        ,I.Adjust_Qty
                        ,(I.Receive_Qty - I.PD_Reserv_Qty - I.Dispatch_Qty - I.Adjust_Qty) as Est_Balance_Qty
                        ,STK_M_Product.ProductCategory_Id
                        ,STK_T_Relocate_Detail.Price_Per_Unit
                        ,unitprice.Dom_EN_Desc as unit_price
                        ,STK_T_Relocate_Detail.All_Price
                        ,STK_T_Relocate_Detail.Unit_Price_Id
                        ,pallet.Pallet_Code
		");
        $this->db->from("STK_T_Relocate");
        $this->db->join("STK_T_Relocate_Detail", "STK_T_Relocate_Detail.Order_Id = STK_T_Relocate.Order_Id");
        $this->db->join("STK_M_Product", "STK_T_Relocate_Detail.Product_Id=STK_M_Product.Product_Id");
        $this->db->join("STK_M_Location L1 ", "STK_T_Relocate_Detail.Suggest_Location_Id = L1.Location_Id", "LEFT");
        $this->db->join("STK_M_Location L2 ", "STK_T_Relocate_Detail.Actual_Location_Id  = L2.Location_Id", "LEFT");
        $this->db->join("STK_M_Location L3 ", "STK_T_Relocate_Detail.Old_Location_Id = L3.Location_Id", "LEFT");

        // Edit By Akkarapol, 13/01/2014, เปลี่ยนจากการ join กับตาราง SYS_M_Domain เพื่อหา Unit Name เป็นการ join กับ  CTL_M_UOM_Template_Language แทน เพื่อรองรับกับการทำ UOM
        // $this->db->join("SYS_M_Domain S1", "STK_T_Relocate_Detail.Unit_Id = S1.Dom_Code AND S1.Dom_Host_Code='PROD_UNIT'");
        $this->db->join("CTL_M_UOM_Template_Language S1", "STK_T_Relocate_Detail.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'");
        // End Edit By Akkarapol, 13/01/2014, เปลี่ยนจากการ join กับตาราง SYS_M_Domain เพื่อหา Unit Name เป็นการ join กับ  CTL_M_UOM_Template_Language แทน เพื่อรองรับกับการทำ UOM
        
        $this->db->join("SYS_M_Domain S2", "STK_T_Relocate_Detail.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS' AND S2.Dom_Active = 'Y'");
        $this->db->join("SYS_M_Domain S3", "STK_T_Relocate_Detail.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS' AND S3.Dom_Active = 'Y'", "LEFT");
        $this->db->join("STK_T_Inbound I", "STK_T_Relocate_Detail.Inbound_Item_Id = I.Inbound_Id", "LEFT");
        //ADD BY POR 2014-01-16 เพิ่มให้แสดงชื่อ unit price ให้ถูกต้อง 
        $this->db->join("SYS_M_Domain unitprice", "STK_T_Relocate_Detail.Unit_Price_Id = unitprice.Dom_Code AND unitprice.Dom_Host_Code='PRICE_UNIT' AND unitprice.Dom_Active = 'Y'", "LEFT");
        $this->db->join("STK_T_Pallet pallet", "pallet.Pallet_Id=STK_T_Relocate_Detail.Pallet_Id", "left"); //add for ISSUE 3334 : by kik : 20140220
        $this->db->where("STK_T_Relocate.Order_Id", $order_id);
        $query = $this->db->get();
    //    echo $this->db->last_query();  exit;
        
        
        //ADD BY POR 2014-06-10 ดึง suggest location แรกมาแสดงในหน้า view
        $data = $query->result();
       
        $data_views = array();
        foreach ($data as $detail): 
            // p($detail); exit;
            if(empty($detail->Suggest_Location_Id)): //ADD BY POR 2014-06-19 เพิ่มการ check กรณีที่มี suggest อยู่แล้วไม่ต้องไปทำอะไร ให้แสดงค่าเดิม แต่ถ้าไม่มี ให้แสดง suggest ที่กำหนด
            // if(!empty($detail->Suggest_Location_Id)): //เทส
                //หา Location ที่แนะนำทั้งหมด 20 รายการต้นๆ
                $sql = "EXEC sp_PA_suggestLocation_Putaway ?,?";
                $parameter = array(
                    "@id" => $detail->Item_Id
                    , "@type_item" => 2
                );    
                $query = $this->db->query($sql, $parameter);
                        // p($query); exit;
                $result = $query->result();

                //วนหาแค่ 1 record แรก
                foreach ($result as $row) {
                    $detail->Suggest_Location_Id = $row->Location_Id;
                    $detail->Suggest_Location = $row->Location_Code; 
                    break;
                }
            endif;
             
            array_push($data_views,$detail);
        endforeach;
        //END ADD
        
        return $data_views;
    }

    function updateOrder($order, $where) {
        $this->db->set('Modified_Date', 'GETDATE()', FALSE);
        unset($order["Modified_Date"]);
        $this->db->where($where);
        $this->db->update("STK_T_Relocate", $order);
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    function updateOrderDetail($order_detail, $where) {
        $this->db->where($where);
        $this->db->update("STK_T_Relocate_Detail", $order_detail);
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    /**
     * @function removeOrderDetail for work delete item in relocate detail table
     * @param array $item_list
     * @return int
     * 
     * @last_modified kik : 20140307
     */
    function removeOrderDetail($item_list) {
        $this->db->where_in('Item_Id', $item_list);
        $this->db->delete('STK_T_Relocate_Detail');
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
        $this->db->where("Is_Pending", INACTIVE);
        $this->db->update("STK_T_Inbound");
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    function getProductCodeByProductId($Product_Id) {
        $this->db->select("STK_M_Product.Product_Code");
        $this->db->from("STK_M_Product");
        $this->db->where("STK_M_Product.Product_Id", $Product_Id);
        $query = $this->db->get();
        return $query->result();
    }

//    ----- START Add by Ton! 20130821 -----
    function getProductStatus() {
//        Select for Table SYS_M_Domain (Dom_Host_Code=PROD_STATUS)
        $this->db->select("*");
        $this->db->from("SYS_M_Domain");
        $this->db->where("SYS_M_Domain.Dom_Host_Code", "PROD_STATUS");
        //$this->db->where("Active", ACTIVE);    //Coment by POR 2013-09-20 เนื่องจากในตารางไม่มีฟิล์ด Active
        //By Por 2013-09-20 เพิ่มเติมเงื่อนไข Dom_Active แทน Active
        //=========start
        $this->db->where("Dom_Active", ACTIVE);
        //=========end
        $this->db->order_by("SYS_M_Domain.Dom_ID");
        $query = $this->db->get();
        return $query;
    }

//    ----- END Add by Ton! 20130821 -----


function location_donotmove(){
    $this->db->select("l.Location_Code");
    $this->db->from("STK_M_Location l");
    $this->db->join("STK_M_Storage s "," s.Storage_Id = l.Storage_Id");
    $this->db->join("STK_M_Storage_Type t "," t.StorageType_Id = s.StorageType_Id");
    $this->db->join("SYS_M_Domain d "," d.Dom_Code = t.StorageType_Code");
    $this->db->where("d.Dom_Host_Code ='NODISP_STORAGE' and d.Dom_Active ='Y'");
    $query = $this->db->get();
    // p($this->db->last_query()); 
    // exit;
    return $query->result_array();
    }

    function location_id_donotmove(){
        $this->db->select("l.Location_Code,l.Location_Id");
        $this->db->from("STK_M_Location l");
        $this->db->join("STK_M_Storage s "," s.Storage_Id = l.Storage_Id");
        $this->db->join("STK_M_Storage_Type t "," t.StorageType_Id = s.StorageType_Id");
        $this->db->join("SYS_M_Domain d "," d.Dom_Code = t.StorageType_Code");
        $this->db->where("d.Dom_Host_Code ='NODISP_STORAGE' and d.Dom_Active ='Y'");
        $query = $this->db->get();
        // p($this->db->last_query()); 
        // exit;
        return $query->result_array();
        }

    function getlocation_order($order_id){
        $this->db->select(" rd.Actual_Location_Id, rd.Suggest_Location_Id");
        $this->db->from("STK_T_Relocate r");
        $this->db->join("STK_T_Relocate_Detail rd" , "r.Order_Id = rd.Order_Id", "left"); 
        $this->db->where("r.Order_Id",$order_id);
        $query = $this->db->get();
        // p($this->db->last_query()); 
        // exit;
        return $query->result_array();
        }
   

}

?>