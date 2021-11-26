<?php

class re_location_model extends CI_Model {

    #add column Is_urgent,Create_Date for ISSUE 3312 : by kik : 20140121
    function showReLocationList($module) {
        $this->db->select("DISTINCT STK_T_Workflow.Flow_Id as Id,SYS_M_State.State_NameEn,STK_T_Relocate.Doc_Relocate
                            ,Doc_Refer_Int='',STK_T_Workflow.Document_No
                            , DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay
                             ,STK_T_Relocate.Is_urgent
                             ,STK_T_Relocate.Create_Date
        		,CTL_M_Contact.First_NameEN + ' ' + CTL_M_Contact.Last_NameEN As WorkerName");
        // Edit By Akkarapol, 16/09/2013, เพิ่ม SELECT DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay เข้าไปด้วย เนื่องจากต้องการค่าความต่างของวัน ระหว่างวันที่ Create Flow นี้ขึ้นมา กับ วันปัจจุบัน
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Relocate", "STK_T_Workflow.Flow_Id = STK_T_Relocate.Flow_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $this->db->join("ADM_M_UserLogin", "ADM_M_UserLogin.UserLogin_Id = STK_T_Relocate.Assigned_Id", "LEFT");
        $this->db->join("CTL_M_Contact", "CTL_M_Contact.Contact_Id = ADM_M_UserLogin.Contact_Id", "LEFT");
        $this->db->where("SYS_M_Stateedge.Module", $module);
        $this->db->order_by("STK_T_Relocate.Is_urgent desc, STK_T_Relocate.Create_Date asc");  // add for ISSUE 3312 : by kik : 20140121
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query;
    }

    function getReLocationDetail($order_id) {

        $this->load->model("location_model", "lc");  // Add By Akkarapol, 17/10/2013, เพิ่มการ load model ที่ชื่อ location_model เข้าไปเนื่องจากเรียกใช้ $this->lc->getLocationCodeById บลาๆๆ ด้านล่างไม่ได้

        $this->db->select('DISTINCT (Old_Location_Id) ,Suggest_Location_Id,Actual_Location_Id,Remark');
        $this->db->from('STK_T_Relocate_Detail');
        //$this->db->join('STK_M_Location','STK_T_Relocate_Detail.');
        $this->db->where('Order_Id', $order_id);
        $query = $this->db->get();
        $i = 0;
        $rows = array();
        foreach ($query->result() as $row) {
            $rows[$i]['from_location'] = $this->lc->getLocationCodeById($row->Old_Location_Id);
            $rows[$i]['to_location'] = $this->lc->getLocationCodeById($row->Suggest_Location_Id);
            $rows[$i]['act_location'] = $this->lc->getLocationCodeById($row->Actual_Location_Id);
            $rows[$i]['remark'] = $row->Remark;
            $i++;
        }
        return $rows;
    }

    #add select for ISSUE 3312 : by kik : 20140121
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

    /**
     * get location by criteria
     *
     * @param Array $criteria
     * @return query
     * @author Ball
     */
    function getLocationAll($criteria = NULL) {
		$this->db->select("DISTINCT(Actual_Location_Id) AS Location_Id, l.Location_Code AS Location_Code, status.Dom_Code as Product_Status, sub_status.Dom_Code as Product_Sub_Status, cate.Dom_Code as Dom_ID");
        $this->db->from("STK_T_Inbound i");
        $this->db->join("STK_M_Location l", "i.Actual_Location_Id=l.Location_Id", "LEFT");
        $this->db->join("STK_M_PUTAWAY putaway", "putaway.Id = l.Putaway_Id", "LEFT"); // Change from JOIN to LEFT JOIN
        $this->db->join("STK_M_Product product", "putaway.Product_Status_Id=product.Product_Id ", "LEFT");
        $this->db->join("STK_M_Storage d", "l.Storage_Id = d.Storage_Id");
        $this->db->join("STK_M_Storage_Type e", "d.StorageType_Id = e.StorageType_Id");
        $this->db->join("SYS_M_Domain status", "status.Dom_Id = putaway.Product_Status_Id AND status.Dom_Active = 'Y'", "LEFT");
        $this->db->join("SYS_M_Domain sub_status", "sub_status.Dom_Id = putaway.Product_Sub_Status_Id AND sub_status.Dom_Active = 'Y'", "LEFT");
        $this->db->join("SYS_M_Domain cate", "cate.Dom_Id = putaway.Product_Category_Id AND cate.Dom_Active = 'Y'", "LEFT");
        $this->db->where("StorageType_Code != ", "ST05");
		$this->db->where("StorageType_Code != ", "ST08");//edit funtion by kik : cos old function show all location (pre-receive , pre-dispatch)
        $this->db->where("i.Active", ACTIVE);
        if ($criteria) :
            foreach ($criteria as $key => $criteria) :
				$this->db->like($key, $criteria);
            endforeach;
        endif;
        $query = $this->db->get();
        //echo $this->db->last_query(); exit();
        return $query;
    }

    function getLocationByCode($code) {
        $this->db->select("DISTINCT(Actual_Location_Id) AS Location_Id,l.Location_Code AS Location_Code");
        $this->db->from("STK_T_Inbound i");
        //$this->db->join("STK_M_Product_Location pl","i.Actual_Location_Id=pl.Location_Id","left");
        $this->db->join("STK_M_Location l", "i.Actual_Location_Id=l.Location_Id", "left");
        $this->db->where("i.Active", ACTIVE);
        $this->db->like("l.Location_Code", $code, 'after');
        $query = $this->db->get();
        return $query;
    }

    function getLocationNameFromArray($location_code) {
		$this->db->select("DISTINCT(Actual_Location_Id) AS Location_Id, l.Location_Code AS Location_Code, status.Dom_Code as Product_Status, sub_status.Dom_Code as Product_Sub_Status, cate.Dom_Code as Dom_ID");
        $this->db->from("STK_T_Inbound i");
        $this->db->join("STK_M_Location l", "i.Actual_Location_Id=l.Location_Id", "LEFT");
        $this->db->join("STK_M_PUTAWAY putaway", "putaway.Id = l.Putaway_Id", "LEFT");
        $this->db->join("STK_M_Product product", "putaway.Product_Status_Id=product.Product_Id ", "LEFT");
        $this->db->join("STK_M_Storage d", "l.Storage_Id = d.Storage_Id");
        $this->db->join("STK_M_Storage_Type e", "d.StorageType_Id = e.StorageType_Id");
        $this->db->join("SYS_M_Domain status", "status.Dom_Id = putaway.Product_Status_Id  AND status.Dom_Active = 'Y'", "LEFT");
        $this->db->join("SYS_M_Domain sub_status", "sub_status.Dom_Id = putaway.Product_Sub_Status_Id AND sub_status.Dom_Active = 'Y'", "LEFT");
        $this->db->join("SYS_M_Domain cate", "cate.Dom_Id = putaway.Product_Category_Id AND cate.Dom_Active = 'Y'", "LEFT");
        $this->db->where("i.Active", ACTIVE);
        $this->db->where_in("Location_Id", $location_code);
        $query = $this->db->get();
        return $query;
    }

    #ISSUE 2190 Re-Location  : Search Product by Status & Sub Status
    #DATE:2013-09-04
    #BY:KIK
    #เพิ่มการแสดงผลของ Status & Sub Status ในส่วนของ get product detail และแก้ส่วนที่ยังผิดพลาดอยู่
    #START New Comment Code #ISSUE 2190
    #=======================================================================================
    // Add DISTINCT

    function getProductInLocationFromArray($inbound_id) {
        $this->db->select("DISTINCT
                    i.Inbound_Id
                    ,i.Actual_Location_Id
                    ,l.Location_Code
                    ,i.Product_Id
                    ,p.Product_Code
                    ,p.Product_NameEN
                    ,d.Dom_EN_Desc AS  Product_Status
                    ,i.Product_Sub_Status
                    ,i.Product_Lot
                    ,i.Product_Serial
                    ,i.Unit_Id
                    ,i.Product_Mfd
                    ,i.Product_Exp
                    ,i.Balance_Qty
                    ,(i.Receive_Qty - i.PD_Reserv_Qty - i.Dispatch_Qty - i.Adjust_Qty) as Allocate
                    ,SYS_M_Domain.Dom_EN_Desc AS Sub_Status_Value
                    ,SYS_M_Domain.Dom_ID,
                    ,i.Unit_Price_Id
                    ,i.Price_Per_Unit
                    ,domain2.Dom_EN_Desc AS Unit_Price_value
                    ,Pallet_Code
                    ,domain1.Dom_Id as Product_Category_Id");
        $this->db->from("STK_T_Inbound i");
        $this->db->join("STK_M_Location l", "i.Actual_Location_Id=l.Location_Id", "left");
        $this->db->join("STK_M_Product p", "i.Product_Id=p.Product_Id", "left");
        $this->db->join("SYS_M_Domain", "i.Product_Sub_Status=SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code='SUB_STATUS' AND SYS_M_Domain.Dom_Active = 'Y' ", "left");
        $this->db->join("SYS_M_Domain domain1", "p.ProductCategory_Id=domain1.Dom_Code AND domain1.Dom_Active = 'Y'", "left");
        $this->db->join("SYS_M_Domain domain2", "i.Unit_Price_Id=domain2.Dom_Code AND domain2.Dom_Host_Code='PRICE_UNIT' AND domain2.Dom_Active = 'Y' ", "left");
        $this->db->join("SYS_M_Domain d", "i.Product_Status = d.Dom_Code and d.Dom_Host_Code ='prod_status' and d.Dom_Active ='Y' ", "LEFT");
        $this->db->join("STK_T_Pallet", "i.Pallet_Id=STK_T_Pallet.Pallet_Id", "left");
        $this->db->where("i.Active", ACTIVE);
        $this->db->where_in("i.Inbound_Id", $inbound_id);
        //$this->db->where("(i.Receive_Qty - i.PD_Reserv_Qty - i.Dispatch_Qty - i.Adjust_Qty) > 0"); // Add where allocate must more than 0
        $query = $this->db->get();
        // p($this->db->last_query()); exit;
        //return $query;
        $result = array();
        foreach ($query->result() as $row) {
            $each_row['Inbound_Id'] = $row->Inbound_Id;
            $each_row['Actual_Location_Id'] = $row->Actual_Location_Id;
            $each_row['Location_Code'] = $row->Location_Code;
            $each_row['Product_Id'] = $row->Product_Id;
            $each_row['Product_Code'] = $row->Product_Code;
            $each_row['Product_NameEN'] = $row->Product_NameEN;
            $each_row['Product_Status'] = $row->Product_Status;
            $each_row['Product_Sub_Status'] = $row->Sub_Status_Value;
            $each_row['Product_Sub_Status_Code'] = $row->Product_Sub_Status;
            $each_row['Product_Lot'] = $row->Product_Lot;
            $each_row['Product_Serial'] = $row->Product_Serial;
            $each_row['Balance_Qty'] = $row->Allocate; // Change from Balance_Qty to Allocate
            $each_row['Unit_Id'] = $row->Unit_Id;
            $each_row['Product_Mfd'] = $row->Product_Mfd;
            $each_row['Product_Exp'] = $row->Product_Exp;
            $each_row['Dom_ID'] = $row->Dom_ID;
            $each_row['Unit_Price_Id'] = $row->Unit_Price_Id;
            $each_row['Price_Per_Unit'] = $row->Price_Per_Unit;
            $each_row['Unit_Price_value'] = $row->Unit_Price_value;
            $each_row['Pallet_Code'] = $row->Pallet_Code;
            $each_row['Product_Category_Id'] = ((int) $row->Product_Category_Id > 0 ? $row->Product_Category_Id : -1);
            $result[] = $each_row;
        }
        return $result;
    }

    #End New Comment Code #ISSUE 2190
    #=======================================================================================
    #Start Old Comment Code #ISSUE 2190
    #=======================================================================================
//        function getProductInLocationFromArray($inbound_id){
//		$this->db->select("i.Inbound_Id,i.Actual_Location_Id,l.Location_Code,i.Product_Id,p.Product_Code,p.Product_NameEN
//							,i.Product_Status,i.Product_Lot,i.Product_Serial,i.Unit_Id
//							,i.Product_Mfd,i.Product_Exp
//							,(i.Receive_Qty-(i.Dispatch_Qty+i.Adjust_Qty)) AS Balance_Qty");
//		$this->db->from("STK_T_Inbound i");
//		$this->db->join("STK_M_Location l","i.Actual_Location_Id=l.Location_Id","left");
//		$this->db->join("STK_M_Product p","i.Product_Id=p.Product_Id","left");
//		$this->db->where("i.Active",ACTIVE);
//		$this->db->where_in("i.Inbound_Id",$inbound_id);
//		$query=$this->db->get();
//		//return $query;
//		$result=array();
//		foreach($query->result() as $row){
//			$each_row['Inbound_Id']=$row->Inbound_Id;
//			$each_row['Actual_Location_Id']=$row->Actual_Location_Id;
//			$each_row['Location_Code']=$row->Location_Code;
//			$each_row['Product_Id']=$row->Product_Id;
//			$each_row['Product_Code']=$row->Product_Code;
//			$each_row['Product_NameEN']=$this->conv->tis620_to_utf8($row->Product_NameEN);
//			$each_row['Product_Status']=$row->Product_Status;
//			$each_row['Product_Lot']=$row->Product_Lot;
//			$each_row['Product_Serial']=$row->Product_Serial;
//			$each_row['Balance_Qty']=$row->Balance_Qty;
//			$each_row['Unit_Id']=$row->Unit_Id;
//			$each_row['Product_Mfd']=$row->Product_Mfd;
//			$each_row['Product_Exp']=$row->Product_Exp;
//			$result[]=$each_row;
//		}
//        return $result;
//
//	}
    #End Old Comment Code #ISSUE 2190
    #=======================================================================================

    function showSuggestLocationSameWarehouse($location_id, $location_code) {
        if ($location_id == "" && $location_code != "") {
            $location_id = $this->lc->getLocationIdByCode($location_code);
        }

        $this->db->select("Zone_Id");
        $this->db->from("STK_M_Location");
        $this->db->where("Location_Id", $location_id);
        $query = $this->db->get();
        $result = $query->result();
        $zone_id = $result[0]->Zone_Id;

        echo "zone id = " . $zone_id . " , location id = " . $location_id;
        $sql = "SELECT Location_Id,Location_Code FROM STK_M_Location l,STK_M_Storage_Detail s
				WHERE l.Zone_Id=" . $zone_id . " AND l.Location_Id <>" . $location_id . "
				 AND l.Storage_Detail_Id=s.Storage_Detail_Id AND Is_Full='N'
				 AND l.Active='" . ACTIVE . "'
				";
        $sug_query = $this->db->query($sql);
        $sug_result = $sug_query->result();
        return $sug_result;
    }

    function showLocationAll($location_code = NULL, $criteria = NULL, $limit = NULL) {
        $location_code = strip_tags($location_code);
        // p( $limit); exit;cd
        $this->db->select("Location_Id,Location_Code");
        $this->db->from("STK_M_Location l");
        $this->db->join("STK_M_Storage_Detail s", "l.Storage_Detail_Id=s.Storage_Detail_Id", "left");
        $this->db->join("STK_M_Storage d", "l.Storage_Id = d.Storage_Id");
        $this->db->join("STK_M_Storage_Type e", "d.StorageType_Id = e.StorageType_Id");
        $this->db->where("StorageType_Code != ", "ST05");
		$this->db->where("StorageType_Code != ", "ST08");//edit funtion by kik : cos old function show all location (pre-receive , pre-dispatch)
        $this->db->where("e.StorageType_Code NOT IN  (SELECT Dom_Code FROM    dbo.SYS_M_Domain  WHERE (Dom_Host_Code = 'NODISP_STORAGE') AND (Dom_Active = 'Y'))");
        if ($location_code) :
            $this->db->where("Location_Code!=", $location_code);
        endif;
        if ($criteria) :
            $this->db->like('Location_Code', strtoupper($criteria), 'after');
        endif;
        $this->db->where("l.Active", ACTIVE);
        $this->db->where("Is_Full", 'N');
        if ($limit) :
            $this->db->limit($limit, 0);
        endif;
        $sug_query = $this->db->get();
        $sug_result = $sug_query->result();
        // p($sug_result); exit;
// p($this->db->last_query()); exit;
        return $sug_result;
    }

    function showProductRL($order_id, $from_location) {
        $this->db->select('*');
        $this->db->from('STK_T_Relocate_Detail');
        $this->db->where('Order_Id', $order_id);
        $this->db->where('Old_Location_Id', $from_location);
        $q = $this->db->get();
        return $q;
    }

    /**
     *
     * @param unknown $order_id
     * @param unknown $from_location
     * @return unknown
     * @author Ball
     */
    public function show_product_relocation($order_id, $from_location) {
        $this->db->select('STK_T_Inbound.Balance_Qty, *,STK_T_Relocate_Detail.Item_Id');
        $this->db->from('STK_T_Relocate_Detail');
        $this->db->join('STK_T_Inbound', 'STK_T_Inbound.Inbound_Id = STK_T_Relocate_Detail.Inbound_Item_Id');
        $this->db->where('STK_T_Relocate_Detail.Order_Id', $order_id);
        $this->db->where('STK_T_Relocate_Detail.Old_Location_Id', $from_location);
        $q = $this->db->get();
        return $q;
    }

    function inboundDetail($inbound_id) {
        $this->db->select("*");
        $this->db->from("STK_T_Inbound");
        $this->db->where("Inbound_Id", $inbound_id);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    function showRLProduct($order_id, $inbound_id) {
        $this->db->select('*');
        $this->db->from('STK_T_Relocate_Detail');
        $this->db->where('Order_Id', $order_id);
        $this->db->where('Inbound_Item_Id', $inbound_id);
        $q = $this->db->get();
        $result = $q->result();
        return $result;
    }

    function getReLocationProductDetail($order_id) {
        #Load config
        $conf = $this->config->item('_xml');
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        $this->load->model("location_model", "lc");  // Add By Akkarapol, 17/10/2013, เพิ่มการ load model ที่ชื่อ location_model เข้าไปเนื่องจากเรียกใช้ $this->lc->getLocationCodeById บลาๆๆ ด้านล่างไม่ได้

        $this->db->select("STK_T_Relocate_Detail.*,SYS_M_Domain.Dom_EN_Desc AS Sub_Status_Value,d.Dom_TH_Desc as Dom_EN_Desc,domain2.Dom_EN_Desc AS Unit_Price_value
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
        // p($this->db->last_query()); exit;
        $i = 0;
        $rows = array();
        foreach ($query->result() as $row) {
        //    p($row);exit();  
            $rows[$i]['item_id'] = $row->Item_Id;
            $rows[$i]['product_code'] = $row->Product_Code;
            $rows[$i]['product_name'] = $row->product_name; // Add By Akkarapol, 18/10/2013, เพิ่ม product_name เข้าไปเรียกใช้ในหน้าอื่นด้วย
            // $rows[$i]['product_status'] = $row->Product_Status;
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
        // p($rows);exit;
        return $rows;
    }

    function addReLocationOrder($order) {
        $this->db->insert("STK_T_Relocate", $order);
        return $this->db->insert_id();
    }

    function addReLocationOrderDetail($order) {
        $this->db->insert_batch('STK_T_Relocate_Detail', $order);
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    function updateReLocationOrder($order, $where) {// Duplicate Function !!
     
        if (array_key_exists("Estimate_Action_Date", $order) && $order["Estimate_Action_Date"] != "") {
            $this->db->set("Estimate_Action_Date", "CONVERT(datetime, '" . $order["Estimate_Action_Date"] . "', 103)", FALSE);
            $this->db->where($where);
            $this->db->update("STK_T_Relocate");
        }
        if (array_key_exists("Actual_Action_Date", $order) && $order["Actual_Action_Date"] != "") {
            $this->db->set("Actual_Action_Date", "CONVERT(datetime, '" . $order["Actual_Action_Date"] . "', 103)", FALSE);
            $this->db->where($where);
            $this->db->update("STK_T_Relocate");
        }
        unset($order["Estimate_Action_Date"]);
        unset($order["Actual_Action_Date"]);
        $this->db->where($where);
        $this->db->update("STK_T_Relocate", $order);
        //start add by kik : 2013-27-12
        $afftectedRows = $this->db->affected_rows();

        if ($afftectedRows > 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }
         //end add by kik : 2013-27-12
    }

    function updateReLocationOrderDetail($order, $where) {

        $this->db->where($where);
        $this->db->update("STK_T_Relocate_Detail", $order);
        //start add by kik : 2013-27-12
        $afftectedRows = $this->db->affected_rows();
        if ($afftectedRows > 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }
         //end add by kik : 2013-27-12
    }

    function removeReLocationDetail($item_list, $order_id) {
        $this->db->where_in('Old_Location_Id', $item_list);
        $this->db->where('Order_Id', $order_id);
        $this->db->delete('STK_T_Relocate_Detail');
        //start add by kik : 2013-27-12
        $afftectedRows = $this->db->affected_rows();

        if ($afftectedRows > 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }
         //end add by kik : 2013-27-12
        //echo $this->db->last_query();
    }

    function removeRLProductDetail($item_list, $order_id) {
        $this->db->where_in('Inbound_Item_Id', $item_list);
        $this->db->where('Order_Id', $order_id);
        $this->db->delete('STK_T_Relocate_Detail');
        //echo $this->db->last_query();
        $afftectedRows = $this->db->affected_rows(); //Add by por 2014-03-18 เพิ่มให้สามารถ return ค่าด้วย
        if ($afftectedRows > 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }

    }

//    ----- Comment Out by Ton! Not Used. 20131030 -----
//    function getUser($contact_id) {
//        $this->db->select("UserLogin_Id");
//        $this->db->from("ADM_M_UserLogin");
//        $this->db->where("Contact_Id", $contact_id);
//        $q = $this->db->get();
//        //echo $this->db->last_query();
//        $r = $q->result();
//        return $r[0]->UserLogin_Id;
//    }

    function getProductCodeByItemId($item_id) {
        $this->db->select("STK_T_Relocate_Detail.Product_Code");
        $this->db->from("STK_T_Relocate_Detail");
        $this->db->where("STK_T_Relocate_Detail.item_id", $item_id);
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query->result();
    }

    /**
     * Execute store procedure
     * @param unknown $criteria
     * @return unknown
     * @author Ball
     */
    public function getRelocationList($criteria) {
        $query = $this->db->query("EXEC sp_PA_suggestLocation NULL,'" . $criteria['status'] . "',1, " . (isset($criteria['category']) ? "'" . $criteria['category'] . "'" : "NULL") . " ," . (isset($criteria['sub_status']) ? "'" . $criteria['sub_status'] . "'" : "NULL") . ",NULL ");
        return $query;
    }

    /**
     * Execute store procedure
     * @param unknown $criteria
     * @return unknown
     * @author Ball
     */
    public function get_suggest_by_location($criteria) {
    	$query = $this->db->query("EXEC sp_PA_suggestLocation_Relocation '" . $criteria['location_id'] . "'");
    	return $query;
    }

    /**
     * Execute store procedure
     * @param unknown $criteria
     * @return unknown
     * @author Ball
     */
    public function get_suggest_by_product($criteria) {
    	$query = $this->db->query("EXEC sp_PA_suggestLocation_Putaway '" . $criteria['id'] . "',  '" . $criteria['type'] . "',  '" . $criteria['qty'] . "' ");
    	return $query;
    }


    function get_product_master($inb){     

        $this->db->select("inb.Product_Code,ms.product_code");
        $this->db->from("master_tmp_ ms");
        $this->db->join("STK_T_Inbound inb","inb.Product_Code = ms.product_code");
        $this->db->where("inb.Inbound_id" ,$inb);
        $this->db->where("ms.Active = 'Y'");
        $query = $this->db->get();    
        //  echo $this->db->last_query(); exit;
    
        return $query->result();
        
    }


    function get_suggest_by_product_master($inb){     

        $this->db->select("loc.Location_Id,ms.Location_Code");
        $this->db->from("master_tmp_ ms");
        $this->db->join("STK_T_Inbound inb","inb.Product_Code = ms.product_code");
        $this->db->join("STK_M_Location loc","loc.Location_Code = ms.Location_Code");
        $this->db->where("inb.Inbound_id" ,$inb);
        $this->db->where("ms.Active = 'Y'");
        $query = $this->db->get();    
        //  echo $this->db->last_query(); exit;
    
        return $query->result();
        
    }

    /**
     * get SKU by location
     * @param int $location_id
     * @return unknown
     * @author Ball
     */
    public function getSKUByLocation($location_id) {
        $query = $this->db->where("Actual_Location_Id", $location_id)->get('vw_inbound');
        return $query;
    }

    public function getAllocateBySKU($inbound_id) {
        $query = $this->db->where("Inbound_Id", $inbound_id)->get('vw_inbound');
        return $query;
    }

    public function getSKUByLocationForRelocation($location_id) {
        $query = $this->db->where(array("Actual_Location_Id" => $location_id, "Active" => "Y"))->get('STK_T_Inbound');
        return $query;
    }

    public function getPreDispatchQTY($id) {
        $query = $this->db->select("PD_Reserv_Qty")->where("Inbound_Id", $id)->get('STK_T_Inbound');
        return $query;
    }

    public function updatePreDispatchQTY($new_pd, $inbound_id) {
        $data = array();
        $data['PD_Reserv_Qty'] = $new_pd;
        $this->db->where("Inbound_Id", $inbound_id)->update('STK_T_Inbound', $data);
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

//    public function getRelocationQTY($order_id, $inbound_id) {
    public function getRelocationQTY($inbound_id) {// Edit by Ton! 20131030
        $query = $this->db->select("Balance_Qty")->where(array("Inbound_Id" => $inbound_id))->get('STK_T_Inbound');
        return $query;
    }

    public function getInboundItem($inbound_id) {// Duplicate Function !!
        $query = $this->db->select("*")->where(array("Inbound_Id" => $inbound_id))->get('STK_T_Inbound');
        return $query;
    }

    public function movePreDispatchInbound($inbound_id, $pd_value) {
        $data = array();
        $data['PD_Reserv_Qty'] = 0;
        $data['Dispatch_Qty'] = $pd_value;
        $data['Balance_Qty'] = 0;
        $this->db->where("Inbound_Id", $inbound_id)->update('STK_T_Inbound', $data);
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    public function movePreDispatchInboundByProduct($inbound_id, $params) {
        $data = array();
        $data['PD_Reserv_Qty'] = $params['new_pd'];
        $data['Dispatch_Qty'] = $params['new_dp'];
        $data['Balance_Qty'] = $params['new_bl'];
        $this->db->where("Inbound_Id", $inbound_id)->update('STK_T_Inbound', $data);
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    public function createDuplicateInbound($inbound_id, $params) {
        $query = $this->db->select('
		Document_No
		,Doc_Refer_Int
		,Doc_Refer_Ext
		,Doc_Refer_Inv
		,Doc_Refer_CE
		,Doc_Refer_BL
		,Doc_Refer_AWB
		,Product_Id
		,Product_Code
		,Product_Status
		,Product_Sub_Status
		,Suggest_Location_Id
		,' . $params["act_to"] . '
		,' . $params["f_location_id"] . '
		,Pallet_Id
		,Receive_Type
		,Receive_Date
		,Putaway_Date
		,Putaway_By
		,Product_License
		,Product_Lot
		,Product_Serial
		,Product_Mfd
		,Product_Exp
		,Receive_Qty
		,PD_Reserv_Qty
		,PK_Reserv_Qty
		,0
		,Balance_Qty
		,Adjust_Qty
		,Unit_Id
		,Owner_Id
		,Renter_Id
		,History_Item_Id
		,Is_Pending
		,Is_Partial
		,Is_Repackage
		,Unlock_Pending_Date
		,Lock_Id
		,Active
		,Activity_Involve
		,Flow_Id')->where('Inbound_Id', $inbound_id)->get('STK_T_Inbound');

        if ($query->num_rows()) {
            foreach ($query->result_array() as $key => $value) :
            //$insert = $this->db->insert('STK_T_Inbound', $value);
            endforeach;
        }
    }

    public function updateRelocationData($data, $where) {// Duplicate Function !!
        $this->db->where("STK_T_Relocate.Order_Id = " . $where);
        $this->db->update("STK_T_Relocate", $data);
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    /**
     *
     * @param int $inbound_id
     * @return #resource
     * @author Ball
     * @date 2013-10-18
     */
    public function get_inbound_item_id($inbound_id = NULL) {
//        $query = array();// Comment Out by Ton! 20131030 Not Used.
        if (!is_null($inbound_id)) :
            $this->db->select("Item_Id");
            $this->db->where("Inbound_Item_Id", $inbound_id);
            $query = $this->db->get("STK_T_Relocate_Detail");
        endif;
        return $query;
    }

    /**
     * Manual Transaction Start
     */
    public function transaction_start () {
    	$this->db->trans_begin();
    }

    /**
     * Manual Transaction Roll Back
     */
    public function transaction_rollback() {
    	$this->db->trans_rollback();
    }

    /**
     * Manual Transaction End
     */
    public function transaction_end() {
    	if ($this->db->trans_status() === FALSE)
    	{
    		$this->db->trans_rollback();
    	} else {
    		$this->db->trans_commit();
    	}
    }

    /**
     * @param Actual_location
     * @author  POR
     * @description ตรวจสอบว่า location ที่ได้ใน inbound ยังมีสินค้าที่ยังไม่ถูกจองวางอยู่หรือไม่ (ตรวจสอบจาก EST BALANCE)
     */
    public function check_est_location($location_id){

        $this->db->select("Actual_Location_Id");
        $this->db->from("STK_T_Inbound");
        $this->db->where("Actual_Location_Id", $location_id);
        $this->db->where("(Receive_Qty - PD_Reserv_Qty - Dispatch_Qty - Adjust_Qty <> 0)");
        $this->db->where("Active",ACTIVE);
        $this->db->group_by("Actual_Location_Id");
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query;
    }
    
    #หา detail ของ order ในฝั่ง relocation โดยดึงเฉพาะรายการที่มี pallet เท่านั้น
    public function GetRelocateDetail($order_id = NULL){
            $this->db->select("RD.Item_Id,RD.Inbound_Item_Id,RD.Confirm_Qty,(inb.Receive_Qty - inb.Dispatch_Qty - inb.Adjust_Qty) as Remain_Qty,RD.Actual_Location_Id,RD.Pallet_Id");
            $this->db->from("STK_T_Relocate_Detail RD");
            $this->db->join("STK_T_Relocate R", "RD.Order_Id = R.Order_Id","left");
            $this->db->join("STK_T_Inbound inb", "RD.Inbound_Item_Id = inb.Inbound_Id","left");
            
            if(!empty($order_id)):  
                $this->db->where("RD.Order_Id", $order_id);
            endif;
            
            $this->db->where("(RD.Pallet_Id IS NOT NULL OR RD.Pallet_Id <> '')");
            $this->db->order_by("RD.Item_Id");
            $query = $this->db->get();
           //p($this->db->last_query());

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

    function order_detail_item_id($order_id){
        $this->db->select("odd.*");
        $this->db->from("STK_T_Relocate od");
        $this->db->join("STK_T_Relocate_Detail odd"," od.Order_Id = odd.Order_Id");
        $this->db->where("od.order_id",$order_id);
        $query = $this->db->get();
        // p($this->db->last_query());
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



}

?>