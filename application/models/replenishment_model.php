<?php

class replenishment_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

   function replenishmentAll() {
        $this->db->select("r.Order_Id as Id,r.Doc_Relocate,r.Doc_Type,c.Contact_Code,r.Estimate_Action_Date
        ,rd.Product_Code,rd.Reserv_Qty,rd.Confirm_Qty,rd.Unit_Id,rd.Price_Per_Unit,rd.Unit_Price_Id,rd.All_Price,rd.Remark");
        $this->db->from("STK_T_Relocate r");
        $this->db->join("STK_T_Relocate_Detail rd", "r.Order_Id = rd.Order_Id ", "left outer");
        $this->db->join("CTL_M_Contact c", "r.Renter_Id = c.Contact_Id", "left");

        $query = $this->db->get();
//          echo $this->db->last_query(); exit();
        return $query;
    }

//		,isnull(r.Doc_Refer_Int,'') as Doc_Refer_Int
//		,isnull(r.Doc_Refer_Ext,'') as Doc_Refer_Ext    
    function getWorkFlowReplenishment($module) {  #Check Query Againt
        $this->db->select("DISTINCT STK_T_Workflow.Flow_Id as Id
		,SYS_M_State.State_NameEn
                ,(select top 1 product_code  from STK_T_Relocate_Detail rd where rd.Order_Id = r.Order_Id ) as product_code
                ,(select top 1 lo.Location_Code  from  STK_T_Relocate_Detail rd2 
                                                join STK_M_Location lo on lo.Location_Id = rd2.Suggest_Location_Id 
                                                where rd2.Order_Id = r.Order_Id ) as location_code
             
                
                ,STK_T_Workflow.Document_No
                , DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay
                ,r.Is_urgent
                ,r.Create_Date
                "); // Edit By Akkarapol, 16/09/2013, เพิ่ม SELECT DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay เข้าไปด้วย เนื่องจากต้องการค่าความต่างของวัน ระหว่างวันที่ Create Flow นี้ขึ้นมา กับ วันปัจจุบัน
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Relocate r", "STK_T_Workflow.Flow_Id = r.Flow_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $this->db->where("SYS_M_Stateedge.Module", $module);
        $this->db->order_by("r.Is_urgent desc, r.Create_Date asc");  // add for ISSUE 3312 : by kik : 20140121
        $query = $this->db->get();
//         echo $this->db->last_query();
        return $query;
    }
    
    function getRelocationOrder($flow_id) {
        $this->db->select("STK_T_Workflow.Process_Id as Id
		,STK_T_Workflow.Present_State
		,STK_T_Workflow.Document_No
		,STK_T_Workflow.Process_Id
		,STK_T_Relocate.Doc_Relocate
		,STK_T_Relocate.Doc_Type
		,STK_T_Relocate.Process_Type
		,STK_T_Relocate.Order_Id
		,STK_T_Relocate.Owner_Id
		,STK_T_Relocate.Renter_Id
		,STK_T_Relocate.Assigned_Id
		,CONVERT(VARCHAR(10),STK_T_Relocate.Estimate_Action_Date,103) AS Est_Action_Date
		,CONVERT(VARCHAR(10),STK_T_Relocate.Actual_Action_Date,103) AS Action_Date
		,SYS_M_Process.Process_Type
                ,STK_T_Relocate.Remark
                ,STK_T_Relocate.Is_urgent
                ,SYS_M_Stateedge.Module   --BY BALL : 2014-09-04
		");
        $this->db->from("STK_T_Workflow");
        $this->db->join("SYS_M_Process", "STK_T_Workflow.Process_Id = SYS_M_Process.Process_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("STK_T_Relocate", "STK_T_Workflow.Document_No = STK_T_Relocate.Doc_Relocate");
        $this->db->where("STK_T_Workflow.Flow_Id", $flow_id);

        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query->result();
    }
    
    function getReLocationProductDetail($order_id) {
        #Load config
        $conf = $this->config->item('_xml');
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        $this->load->model("location_model", "lc");  // Add By Akkarapol, 17/10/2013, เพิ่มการ load model ที่ชื่อ location_model เข้าไปเนื่องจากเรียกใช้ $this->lc->getLocationCodeById บลาๆๆ ด้านล่างไม่ได้

        $this->db->select("STK_T_Relocate_Detail.*,SYS_M_Domain.Dom_EN_Desc AS Sub_Status_Value,domain2.Dom_EN_Desc AS Unit_Price_value,d.Dom_TH_Desc as Dom_EN_Desc
                    ,CONVERT(VARCHAR(10),STK_T_Relocate_Detail.Product_Mfd,103) AS Product_Mfd
                    ,CONVERT(VARCHAR(10),STK_T_Relocate_Detail.Product_Exp,103) AS Product_Exp
                    ,(SELECT Product_NameEN FROM STK_M_Product WHERE STK_M_Product.Product_Code = STK_T_Relocate_Detail.Product_Code AND Active='Y') AS product_name
                    ,STK_T_Relocate_Detail.DP_Type_Pallet
                    ");
// Edit By Akkarapol, 17/10/2013, เพิ่ม CONVERT(VARCHAR(10),STK_T_Relocate_Detail.Product_Exp,103) AS Product_Exp เพื่อเอาค่า EXP ของ Product ไปแสดงด้วย
// Edit By Akkarapol, 18/10/2013, เพิ่ม (SELECT Product_NameEN FROM STK_M_Product WHERE STK_M_Product.Product_Code = STK_T_Relocate_Detail.Product_Code) AS product_name เพื่อเอาชื่อของ Product ไปแสดงด้วย
        $this->db->from('STK_T_Relocate_Detail');

        if($conf_pallet):
            $this->db->select("Pallet_Code");
            $this->db->join("STK_T_Pallet","STK_T_Relocate_Detail.Pallet_Id=STK_T_Pallet.Pallet_Id","left");
        endif;

        $this->db->where('STK_T_Relocate_Detail.Order_Id', $order_id);

        $this->db->join("SYS_M_Domain", "STK_T_Relocate_Detail.Product_Sub_Status=SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code='SUB_STATUS' AND SYS_M_Domain.Dom_Active = 'Y' ", "left");
        $this->db->join("SYS_M_Domain domain2", "STK_T_Relocate_Detail.Unit_Price_Id=domain2.Dom_Code AND domain2.Dom_Host_Code='PRICE_UNIT' AND domain2.Dom_Active = 'Y' ", "left");
        $this->db->join("SYS_M_Domain d", "STK_T_Relocate_Detail.Product_Status = d.Dom_Code and d.Dom_Host_Code ='prod_status' and d.Dom_Active ='Y' ", "LEFT");

        $this->db->where('Reserv_Qty <> 0'); //ADD BY POR 2014-06-13 ให้แสดงเฉพาะที่มีค่า Reserv_Qty เท่านั้น ถ้าไม่มีแสดงว่ารายการถูกย้ายไปที่อื่นแล้ว
        $query = $this->db->get();

        $i = 0;
        $rows = array();
        foreach ($query->result() as $row) {
        //    p($row);exit();
            $rows[$i]['item_id'] = $row->Item_Id;
            $rows[$i]['product_code'] = $row->Product_Code;
            $rows[$i]['product_name'] = $row->product_name; // Add By Akkarapol, 18/10/2013, เพิ่ม product_name เข้าไปเรียกใช้ในหน้าอื่นด้วย
            $rows[$i]['product_status'] = $row->Dom_EN_Desc;
            $rows[$i]['Product_Sub_Status'] = $row->Sub_Status_Value;
            $rows[$i]['product_lot'] = $row->Product_Lot;
            $rows[$i]['product_serial'] = $row->Product_Serial;
            $rows[$i]['reserv_qty'] = $row->Reserv_Qty;
            $rows[$i]['confirm_qty'] = $row->Confirm_Qty;
            $rows[$i]['product_mfd'] = $row->Product_Mfd;
            $rows[$i]['product_exp'] = $row->Product_Exp; // Add By Akkarapol, 17/10/2013, เพิ่ม Product_Exp เข้าไปเรียกใช้ในหน้าอื่นด้วย
            $rows[$i]['from_location'] = $this->lc->getLocationCodeById($row->Old_Location_Id);
            $rows[$i]['to_location'] = $this->lc->getLocationCodeById($row->Suggest_Location_Id);
            $rows[$i]['act_location'] = $this->lc->getLocationCodeById($row->Actual_Location_Id);
            $rows[$i]['inbound_id'] = $row->Inbound_Item_Id;
            $rows[$i]['remark'] = $row->Remark;
            $rows[$i]['Price_Per_Unit'] = $row->Price_Per_Unit;
            $rows[$i]['Unit_Price_Id'] = $row->Unit_Price_Id;
            $rows[$i]['All_Price'] = $row->All_Price;
            $rows[$i]['Unit_Price_value'] = $row->Unit_Price_value;
            if($conf_pallet):
                $rows[$i]['Pallet_Code'] = $row->Pallet_Code;
            endif;
            $rows[$i]['DP_Type_Pallet'] = $row->DP_Type_Pallet;
            $i++;
        }
        return $rows;
    }
       
    public function get_data_flow_order($flow) {        
        $this->db->select(" * ");
        $this->db->from("STK_T_WorkFlow wf");
        $this->db->join("STK_T_Relocate r","r.flow_id = wf.flow_id ");
        $this->db->where("wf.flow_id",$flow);
        $query = $this->db->get();   
       // p($this->db->last_query());
        return $query;
    }
    
    public function get_data_detail($order_id) {
        $this->db->select(" rd.* ");
        $this->db->from("STK_T_Relocate_Detail rd");
        $this->db->where("rd.order_id",$order_id);
        $this->db->where("rd.Inbound_Item_Id IS NULL");
        $query = $this->db->get();   
        return $query;
    }
    
    function getListPreDispatchByProdCodeViaAjax($param = "") {
        $val = trim($param);

        #Load config
        $conf = $this->config->item('_xml');
        //$conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        //$conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        //edit format to CI : BY POR 2014-08-18
        $this->db->select("a.Inbound_Id
                ,a.Product_Id
                ,a.Product_Code
                ,b.Product_NameEN
                ,a.Product_Status
                ,a.Product_Sub_Status ,a.Unit_Id
                ,a.Product_License,a.Product_Lot,a.Product_Serial,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd',CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp'
                ,a.Balance_Qty
                ,(a.Receive_Qty - a.PD_Reserv_Qty - a.Dispatch_Qty - a.Adjust_Qty) as Est_Balance_Qty
                ,S1.public_name AS Unit_Value
                ,S2.Dom_Code   AS Status_Code
                ,S2.Dom_EN_Desc AS Status_Value
                ,S3.Dom_Code   AS Sub_Status_Code
                ,S3.Dom_EN_Desc AS Sub_Status_Value
                ,L1.Location_Code AS Suggest_Location
                ,L2.Location_Code AS Actual_Location
                ,a.Actual_Location_Id
                ,a.Receive_Qty
                ,a.PD_Reserv_Qty
                ,a.Dispatch_Qty
                ,a.Adjust_Qty
		,CONVERT(varchar(10), a.Receive_Date, 103) As Receive_Date_sort
		,CONVERT(varchar(10), a.Product_Exp, 103) As Product_Expire_sort");

        if($conf_price_per_unit):
            $this->db->select("a.Unit_Price_Id
                ,a.Price_Per_Unit
                ,S4.Dom_EN_Desc AS Unit_Price_value");
        endif;

        if($conf_pallet):
            $this->db->select("Pallet_Code");
        endif;

        $this->db->from("STK_T_Inbound a");
        $this->db->join("STK_M_Product b","a.Product_Code = b.Product_Code","LEFT OUTER");
        $this->db->join("CTL_M_UOM_Template_Language S1","a.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->join("SYS_M_Domain S2","a.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS' AND S2.Dom_Active = 'Y' ");
        $this->db->join("SYS_M_Domain S3","a.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS' AND S3.Dom_Active = 'Y'","LEFT");
        $this->db->join("SYS_M_Domain S4","a.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code='PRICE_UNIT' AND S4.Dom_Active = 'Y'","LEFT");
        $this->db->join("STK_M_Location L1","a.Suggest_Location_Id = L1.Location_Id","LEFT");
        $this->db->join("STK_M_Location L2","a.Actual_Location_Id = L2.Location_Id","LEFT");

        if($conf_pallet):
            $this->db->join("STK_T_Pallet","a.Pallet_Id=STK_T_Pallet.Pallet_Id","LEFT");
        endif;

        $this->db->where("(Receive_Qty - PD_Reserv_Qty - Dispatch_Qty - Adjust_Qty) > 0");
        $this->db->where("b.ACTIVE",'Y');
        $this->db->where("a.Inbound_Id IN (" . $val . ")");

        $this->db->order_by("CASE b.PutAway_Rule
                                WHEN 'FIFO'
                                    THEN CASE a.Receive_Date WHEN NULL THEN 0 ELSE a.Receive_Date END
                                WHEN 'FEFO'
                                    THEN CASE a.Product_Exp WHEN NULL THEN 0 ELSE a.Product_Exp END
                                ELSE a.Actual_Location_Id
                               END");

        /*  เปลี่ยนโครงสร้างการ query : COMMENT BY POR 2014-08-18
        $str = "SELECT DISTINCT a.Inbound_Id
                                ,a.Product_Id
                                ,a.Product_Code
                                ,b.Product_NameEN
                                ,a.Product_Status
				,a.Product_Sub_Status ,a.Unit_Id
                                ,a.Product_License,a.Product_Lot,a.Product_Serial,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd',CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp'
                                ,a.Balance_Qty
                                ,(a.Receive_Qty - a.PD_Reserv_Qty - a.Dispatch_Qty - a.Adjust_Qty) as Est_Balance_Qty
				,S1.public_name AS Unit_Value
				,S2.Dom_Code   AS Status_Code
				,S2.Dom_EN_Desc AS Status_Value
				,S3.Dom_Code   AS Sub_Status_Code
				,S3.Dom_EN_Desc AS Sub_Status_Value
				,L1.Location_Code AS Suggest_Location
				,L2.Location_Code AS Actual_Location
				,a.Actual_Location_Id
                                ,a.Unit_Price_Id
                                ,a.Price_Per_Unit
                                ,S4.Dom_EN_Desc AS Unit_Price_value
                                ,Pallet_Code
                                ,a.Receive_Qty
                                ,a.PD_Reserv_Qty
                                ,a.Dispatch_Qty
                                ,a.Adjust_Qty
                 FROM STK_T_Inbound a
                 LEFT OUTER JOIN STK_M_Product b ON a.Product_Code = b.Product_Code
                    JOIN CTL_M_UOM_Template_Language S1 ON a.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'
                    JOIN SYS_M_Domain S2 ON a.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS'

                    LEFT JOIN SYS_M_Domain S3 ON a.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS'
                    LEFT JOIN SYS_M_Domain S4 ON a.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code='PRICE_UNIT'
                    LEFT JOIN STK_M_Location L1 ON a.Suggest_Location_Id = L1.Location_Id
                    LEFT JOIN STK_M_Location L2 ON a.Actual_Location_Id = L2.Location_Id
                    LEFT JOIN STK_T_Pallet ON a.Pallet_Id=STK_T_Pallet.Pallet_Id
                 WHERE
                 (Receive_Qty - PD_Reserv_Qty - Dispatch_Qty - Adjust_Qty) > 0
                 AND b.ACTIVE ='Y'
                 AND a.Inbound_Id IN (" . $val . ")"; //Edit by kik (27-09-2013)
// Edit By Akkarapol, 10/01/2014, เปลี่ยนจากการ join กับตาราง SYS_M_Domain เพื่อหา Unit Name เป็นการ join กับ  CTL_M_UOM_Template_Language แทน เพื่อรองรับกับการทำ UOM
         */

        //$query = $this->db->query($str);
        $query = $this->db->get();
        //echo $this->db->last_query();//exit();
        return $query;
    }
    
    function addRelocationDetail($aar_data){
        $this->db->insert("STK_T_Relocate_detail", $aar_data);
        return $this->db->insert_id();
    }
    
    function get_data_inb($inb){
        $this->db->select(" inb.* ");
        $this->db->from("STK_T_Inbound inb");
        $this->db->where("inb.Inbound_id",$inb);
//        $this->db->where("rd.Inbound_Item_Id IS NULL");
        $query = $this->db->get();   
        return $query;
    }
    
    function change_reserv($item_id,$reserv){
        $data = new stdClass();
        $data->Reserv_Qty = $reserv;
        $this->db->where('Item_id', $item_id);
        $this->db->update('STK_T_Relocate_detail', $data);
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }
    
    function delect_detail($item_id){
        $this->db->where('Item_id', $item_id);
        $this->db->delete('STK_T_Relocate_detail');
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }
    
     function updateWorkFlowTrax($flow_id, $process_id, $next_state) {
        $update = array(
            'Present_State' => $next_state
            , 'Modified_By' => $this->_usr["user_id"]
            , 'Modified_Date' => date("Y-m-d H:i:s")
        );
        $where = array(
            'Process_Id' => $process_id
            , 'Flow_Id' => $flow_id
        );
        $this->db->where($where);
        $this->db->update('STK_T_Workflow', $update);
        $afftectedRows = $this->db->affected_rows();
        $result = ($afftectedRows == 0 ? FALSE : TRUE);
        return $result;
    }
    
    function getWorkerAllWithUserLogin() {
        $this->db->select("*");
        $this->db->from("ADM_M_UserLogin");
        $this->db->join("CTL_M_Contact", "CTL_M_Contact.Contact_Id = ADM_M_UserLogin.Contact_Id  AND ADM_M_UserLogin.Active = 1 "); // Add Active = 1
        $query = $this->db->get();
        return $query;
    }
    function location_donotmove(){
        $this->db->select("l.Location_Code");
        $this->db->from("STK_M_Location l");
        $this->db->join("STK_M_Storage s "," s.Storage_Id = l.Storage_Id");
        $this->db->join("STK_M_Storage_Type t "," t.StorageType_Id = s.StorageType_Id");
        $this->db->join("SYS_M_Domain d "," d.Dom_Code = t.StorageType_Code");
        $this->db->where("d.Dom_Host_Code ='NODISP_STORAGE' and d.Dom_Active ='Y'");
        $query = $this->db->get();
        // p($this->db->last_query()); exit;
        return $query->result_array();
    }
}
