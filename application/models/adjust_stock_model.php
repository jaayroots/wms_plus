<?php

class adjust_stock_model extends CI_Model {

    #add column Is_urgent,Create_Date for ISSUE 3312 : by kik : 20140121
    function showAdjustStockList($module) {
        $this->db->select("DISTINCT STK_T_Workflow.flow_Id as Id,SYS_M_State.State_NameEn,STK_T_Order.Document_No
							,STK_T_Order.Document_No
							,CONVERT(VARCHAR(10),STK_T_Order.Estimate_Action_Date,103) AS Estimate_Action_Date
							,STK_T_Order.Remark
							,STK_T_Workflow.Document_No
                                                        ,STK_T_Order.Is_urgent
                                                        ,STK_T_Order.Create_Date
                            , DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay");
        // Edit By Akkarapol, 16/09/2013, เพิ่ม SELECT DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay เข้าไปด้วย เนื่องจากต้องการค่าความต่างของวัน ระหว่างวันที่ Create Flow นี้ขึ้นมา กับ วันปัจจุบัน

        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $this->db->where("SYS_M_Stateedge.module", $module);
        $this->db->order_by("STK_T_Order.Is_urgent desc, STK_T_Order.Create_Date asc");  // add for ISSUE 3312 : by kik : 20140121
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function getAdjustStockOrder($flow_id) {
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

    #ISSUE 2233 Stock  Adjustment  : Search Product by Status & Sub Status
    #DATE:2013-09-04
    #BY:KIK
    #เพิ่มการแสดงผลของ Status & Sub Status ในส่วนของ get product detail และแก้ส่วนที่ยังผิดพลาดอยู่ 
    #START New Comment Code #ISSUE 2233
    #=======================================================================================

    #add Unit_Price_Id,Price_Per_Unit,Unit_Price_value for ISSUE 3302 : by kik : 20140115
    function getProductPostFromArray($inbound_id) {

        $this->db->select("
                    i.Inbound_Id,
                    i.Actual_Location_Id,
                    l.Location_Code,
                    i.Product_Id,
                    p.Product_Code,
                    p.Product_NameEN,
                    d.Dom_EN_Desc AS Product_Status,
                    i.Product_Lot,
                    i.Product_Serial,
                    i.Balance_Qty,
                    (i.Receive_Qty - i.PD_Reserv_Qty - i.Dispatch_Qty - i.Adjust_Qty) as Est_Balance_Qty,
                    S1.CTL_M_UOM_Template_id AS Unit_Id,
                    S1.public_name AS Unit_Value,
                    CONVERT(VARCHAR(20), i.Product_Mfd, 103) AS Product_Mfd,
                    CONVERT(VARCHAR(20), i.Product_Exp, 103) AS Product_Exp,
                    ,SYS_M_Domain.Dom_Code   AS Sub_Status_Code
                    ,SYS_M_Domain.Dom_EN_Desc AS Sub_Status_Value
                    ,i.Unit_Price_Id
                    ,i.Price_Per_Unit
                    ,domain2.Dom_EN_Desc AS Unit_Price_value,
                    ,pallet.Pallet_Code
                    ");

        $this->db->from("STK_T_Inbound i");
        $this->db->join("STK_M_Location l", "i.Actual_Location_Id=l.Location_Id", "left");
        $this->db->join("STK_M_Product p", "i.Product_Id=p.Product_Id", "left");
        $this->db->join("SYS_M_Domain", "i.Product_Sub_Status=SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code='SUB_STATUS' AND SYS_M_Domain.Dom_Active = 'Y' ", "left");
        $this->db->join("SYS_M_Domain d", "i.Product_Status = d.Dom_Code and d.Dom_Host_Code ='prod_status' and d.Dom_Active ='Y' ", "LEFT");
        $this->db->join("SYS_M_Domain domain2", "domain2.Dom_Code=i.Unit_Price_Id AND domain2.Dom_Host_Code='PRICE_UNIT' AND domain2.Dom_Active = 'Y' ", "left"); //add for ISSUE 3302 : by kik : 20140115 
        $this->db->join("CTL_M_UOM_Template_Language S1", "i.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'"); // Add By Akkarapol, 10/01/2014, เพิ่มการ join กับ CTL_M_UOM_Template_Language ด้วยเพื่อหา Unit Name มาแสดง
        $this->db->join("STK_T_Pallet pallet", "pallet.Pallet_Id=i.Pallet_Id", "left");
        $this->db->where("i.Active", ACTIVE);
        $this->db->where_in("i.Inbound_Id", $inbound_id);
        $query = $this->db->get();
        // p($this->db->last_query()); exit;
        $result = array();
        
        foreach ($query->result() as $row) {
            $each_row['Inbound_Id'] = $row->Inbound_Id;
            $each_row['Actual_Location_Id'] = $row->Actual_Location_Id;
            $each_row['Location_Code'] = $row->Location_Code;
            $each_row['Product_Id'] = $row->Product_Id;
            $each_row['Product_Code'] = $row->Product_Code;
            $each_row['Product_NameEN'] = $row->Product_NameEN;
            $each_row['Product_Status'] = $row->Product_Status;
            $each_row['Sub_Status_Value'] = $row->Sub_Status_Value;
            $each_row['Product_Lot'] = $row->Product_Lot;
            $each_row['Product_Serial'] = $row->Product_Serial;
            $each_row['Est_Balance_Qty'] = $row->Est_Balance_Qty;
            $each_row['Unit_Id'] = $row->Unit_Id;
            $each_row['Unit_Value'] = $row->Unit_Value;
            $each_row['Product_Mfd'] = $row->Product_Mfd;
            $each_row['Product_Exp'] = $row->Product_Exp;
            $each_row['Sub_Status_Code'] = $row->Sub_Status_Code;
            $each_row['Unit_Price_Id'] = $row->Unit_Price_Id;
            $each_row['Price_Per_Unit'] = $row->Price_Per_Unit;
            $each_row['Unit_Price_value'] = $row->Unit_Price_value;
            $each_row['Pallet_Code'] = $row->Pallet_Code;
            $result[] = $each_row;
        }
        // echo $this->db->last_query();
        return $result;
    }

    #End New Comment Code #ISSUE 2233
    #=======================================================================================
    #Start Old Comment Code #ISSUE 2233
    #=======================================================================================
//        function getProductPostFromArray($inbound_id){
//		
//       // $this->db->from("STK_T_Inbound");
//        //$this->db->where_in("Inbound_Id",$inbound_id);
//                
//                
//                
////            --#ISSUE 2233
////            --#DATE:2013-08-28
////            --#BY:KIK
////            --#ปัญหา:Product_Mfd และ Product_Exp แสดงผิดรูปแบบ
////            --#สาเหตุ:Product_Mfd และ Product_Exp ขึ้นใน format ที่ดึงมากจากฐานข้อมูลโดยตรง ไม่ได้ convert
////            --#วิธีการแก้:ทำการ convert date ทั้งสองตัว ตามโค้ด
////            -- START Old Comment Code ISSUE 2233
////            
//        
////              $query = $this->db->get();
////		$this->db->select("i.Inbound_Id,i.Actual_Location_Id,l.Location_Code,i.Product_Id,p.Product_Code,p.Product_NameEN
////							,i.Product_Status,i.Product_Lot,i.Product_Serial,(Receive_Qty-(Dispatch_Qty+Adjust_Qty)) AS Balance_Qty,i.Unit_Id,i.Product_Mfd,i.Product_Exp");
//           
//            
////            -- END Old Comment Code ISSUE 2233
////            -- START New Code ISSUE 2233
//
//                $this->db->select("
//                    i.Inbound_Id,
//                    i.Actual_Location_Id,
//                    l.Location_Code,
//                    i.Product_Id,
//                    p.Product_Code,
//                    p.Product_NameEN,
//                    i.Product_Status,
//                    i.Product_Lot,
//                    i.Product_Serial,
//                    i.Balance_Qty,
//                    i.Unit_Id,
//                    CONVERT(VARCHAR(20), i.Product_Mfd, 103) AS Product_Mfd,
//                    CONVERT(VARCHAR(20), i.Product_Exp, 103) AS Product_Exp
//                    ");
//                
// //            -- END New Code ISSUE 2233
//                
//                
//                
//                
//		$this->db->from("STK_T_Inbound i");
//		$this->db->join("STK_M_Location l","i.Actual_Location_Id=l.Location_Id","left");
//		$this->db->join("STK_M_Product p","i.Product_Id=p.Product_Id","left");
//		$this->db->where("i.Active",ACTIVE);
//		$this->db->where_in("i.Inbound_Id",$inbound_id);
//		$query=$this->db->get();
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
//	}
    #End Old Comment Code #ISSUE 2233
    #=======================================================================================
    # add Est_Balance_Qty and join table inbound by kik (11-10-2013)

    function getAdjustOrderDetail($order_id) {

//            --#ISSUE 2233
//            --#DATE:2013-08-28
//            --#BY:KIK
//            --#ปัญหา:Product_Mfd และ Product_Exp แสดงผิดรูปแบบ
//            --#สาเหตุ:Product_Mfd และ Product_Exp ขึ้นใน format ที่ดึงมากจากฐานข้อมูลโดยตรง ไม่ได้ convert
//            --#วิธีการแก้:ทำการ convert date ทั้งสองตัว ตามโค้ด
//            -- START Old Comment Code ISSUE 2233
//		$this->db->select('*,Product_NameEN,Location_Code');       
//            -- END Old Comment Code ISSUE 2233
//            -- START New Code ISSUE 2233
        
                #Load config

	

        //ADD BY POR 2014-07-30 เพิ่ม config
        $conf = $this->config->item('_xml'); 
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];
        //END ADD


        $this->db->select('order_detail.Item_Id as detail_item_id,
                    order_detail.*
                    ,S1.public_name AS Unit_Value
                    ,product.Product_NameEN
                    ,location.Location_Code
                    ,order_detail.Product_Sub_Status as Sub_Status_Code
                    ,CONVERT(VARCHAR(20), order_detail.Product_Mfd, 103) AS Product_Mfd
                    ,CONVERT(VARCHAR(20), order_detail.Product_Exp, 103) AS Product_Exp
                    ,domain.Dom_EN_Desc AS Sub_Status_Value
                    ,(inbound.Receive_Qty - inbound.PD_Reserv_Qty - inbound.Dispatch_Qty - inbound.Adjust_Qty) as Est_Balance_Qty
                    ,domain2.Dom_EN_Desc AS Unit_Price_value
                        ');
//            -- END New Code ISSUE 2233           

        $this->db->from('STK_T_Order_Detail order_detail');
        $this->db->join('STK_M_Product product', 'order_detail.Product_Id=product.Product_Id', "left");
        $this->db->join('STK_T_Inbound inbound', 'order_detail.Inbound_Item_Id=inbound.Inbound_Id');
        $this->db->join('STK_M_Location location', 'order_detail.Suggest_Location_Id=location.Location_Id', "left");
        $this->db->join("SYS_M_Domain domain", "order_detail.Product_Sub_Status=domain.Dom_Code AND domain.Dom_Host_Code='SUB_STATUS' AND domain.Dom_Active = 'Y' ", "left");
        $this->db->join("SYS_M_Domain domain2", "order_detail.Unit_Price_Id=domain2.Dom_Code AND domain2.Dom_Host_Code='PRICE_UNIT' AND domain2.Dom_Active = 'Y' ", "left");
        $this->db->join("SYS_M_Domain d", "inbound.Product_Status = d.Dom_Code and d.Dom_Host_Code ='prod_status' and d.Dom_Active ='Y' ", "LEFT");
        $this->db->join("CTL_M_UOM_Template_Language S1", "order_detail.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'"); // Add By Akkarapol, 10/01/2014, เพิ่มการ join กับ CTL_M_UOM_Template_Language ด้วยเพื่อหา Unit Name มาแสดง
        $this->db->where('order_detail.Order_Id', $order_id);
        
        if($conf_pallet):
            $this->db->select("pallet.Pallet_Code");
            $this->db->join("STK_T_Pallet pallet", "pallet.Pallet_Id=order_detail.Pallet_Id", "left");
        endif;

        $query = $this->db->get();
        // echo $this->db->last_query();
        return $query->result();
    }

    function adjustOrderItem($order_id, $inbound_id) {
        $this->db->select('*');
        $this->db->from('STK_T_Order_Detail');
        $this->db->where('Order_Id', $order_id);
        $this->db->where('Inbound_Item_Id', $inbound_id);
        $q = $this->db->get();
        $result = $q->result();
        
        return $result;
    }

    /*
      function getLocationAll(){
      $this->db->select("DISTINCT(Actual_Location_Id) AS Location_Id,l.Location_Code AS Location_Code");
      $this->db->from("STK_T_Inbound i");
      //$this->db->join("STK_M_Product_Location pl","i.Actual_Location_Id=pl.Location_Id","left");
      $this->db->join("STK_M_Location l","i.Actual_Location_Id=l.Location_Id","left");
      $this->db->where("i.Active",ACTIVE);
      $query=$this->db->get();
      return $query;
      }

      function getLocationByCode($code){
      $this->db->select("DISTINCT(Actual_Location_Id) AS Location_Id,l.Location_Code AS Location_Code");
      $this->db->from("STK_T_Inbound i");
      //$this->db->join("STK_M_Product_Location pl","i.Actual_Location_Id=pl.Location_Id","left");
      $this->db->join("STK_M_Location l","i.Actual_Location_Id=l.Location_Id","left");
      $this->db->where("i.Active",ACTIVE);
      $this->db->like("l.Location_Code",$code, 'after');
      $query=$this->db->get();
      return $query;
      }

      function getLocationNameFromArray($location_code){

      $this->db->from("STK_M_Location");
      $this->db->where_in("Location_Id",$location_code);
      $query = $this->db->get();
      return $query;
      }

      function getProductInLocationFromArray($inbound_id){

      // $this->db->from("STK_T_Inbound");
      //$this->db->where_in("Inbound_Id",$inbound_id);
      //$query = $this->db->get();
      $this->db->select("Inbound_Id,Actual_Location_Id,Location_Code,Product_Id,Product_Code,Product_Status,Product_Lot,Product_Serial,(Receive_Qty-(Dispatch_Qty+Adjust_Qty)) AS Balance_Qty");
      $this->db->from("STK_T_Inbound");
      $this->db->join("STK_M_Location","Actual_Location_Id=Location_Id");
      $this->db->where("STK_T_Inbound.Active",ACTIVE);
      $this->db->where_in("Inbound_Id",$inbound_id);
      $query=$this->db->get();
      return $query;
      }

      function showSuggestLocationSameWarehouse($location_id,$location_code){
      if($location_id=="" && $location_code!=""){
      $location_id=$this->lc->getLocationIdByCode($location_code);
      }

      $this->db->select("Zone_Id");
      $this->db->from("STK_M_Location");
      $this->db->where("Location_id",$location_id);
      $query=$this->db->get();
      $result=$query->result();
      $zone_id=$result[0]->Zone_Id;

      echo "zone id = ".$zone_id." , location id = ".$location_id;
      $sql="SELECT Location_Id,Location_Code FROM STK_M_Location l,STK_M_Storage_Detail s
      WHERE l.Zone_Id=".$zone_id." AND l.Location_Id <>".$location_id."
      AND l.Storage_Detail_Id=s.Storage_Detail_Id AND Is_Full='N'
      AND l.Active='".ACTIVE."'
      ";
      $sug_query=$this->db->query($sql);
      $sug_result=$sug_query->result();
      return $sug_result;
      }

      function showLocationAll($location_code){
      $location_code=strip_tags($location_code);
      $this->db->select("Location_Id,Location_Code");
      $this->db->from("STK_M_Location l");
      $this->db->join("STK_M_Storage_Detail s","l.Storage_Detail_Id=s.Storage_Detail_Id","left");
      $this->db->where("Location_Code!=",$location_code);
      $this->db->where("l.Active",ACTIVE);
      $this->db->where("Is_Full",'N');
      $sug_query=$this->db->get();
      $sug_result=$sug_query->result();
      //p($sug_result);
      return $sug_result;

      }

      function showProductRL($order_id,$from_location){
      $this->db->select('*');
      $this->db->from('STK_T_Relocate_Detail');
      $this->db->where('Order_Id',$order_id);
      $this->db->where('Old_Location_Id',$from_location);
      $q=$this->db->get();
      //$result=$q->result();
      return $q;
      }

      function inboundDetail($inbound_id){
      $this->db->select("*");
      $this->db->from("STK_T_Inbound");
      $this->db->where("Inbound_Id",$inbound_id);
      $query=$this->db->get();
      $result=$query->result();
      return $result;
      }

      function showRLProduct($order_id,$inbound_id){
      $this->db->select('*');
      $this->db->from('STK_T_Relocate_Detail');
      $this->db->where('Order_Id',$order_id);
      $this->db->where('Inbound_Item_Id',$inbound_id);
      $q=$this->db->get();
      $result=$q->result();
      return $result;
      }

      function getReLocationProductDetail($order_id){
      $this->db->select('*');
      $this->db->from('STK_T_Relocate_Detail');
      //$this->db->join('STK_M_Location','STK_T_Relocate_Detail.');
      $this->db->where('Order_Id',$order_id);
      $query=$this->db->get();
      $i=0;
      $rows=array();
      foreach($query->result() as $row){
      $rows[$i]['item_id']=$row->Item_Id;
      $rows[$i]['product_code']=$row->Product_Code;
      $rows[$i]['product_status']=$row->Product_Status;
      $rows[$i]['product_lot']=$row->Product_Lot;
      $rows[$i]['product_serial']=$row->Product_Serial;
      $rows[$i]['reserv_qty']=$row->Reserv_Qty;
      $rows[$i]['confirm_qty']=$row->Confirm_Qty;
      $rows[$i]['from_location']=$this->lc->getLocationCodeById($row->Old_Location_Id);
      $rows[$i]['to_location']=$this->lc->getLocationCodeById($row->Suggest_Location_Id);
      $rows[$i]['act_location']=$this->lc->getLocationCodeById($row->Actual_Location_Id);
      $rows[$i]['inbound_id']=$row->Inbound_Item_Id;
      $rows[$i]['remark']=$row->Remark;
      $i++;
      }
      return $rows;
      }


      function addReLocationOrder($order){
      $this->db->insert("STK_T_Relocate",$order);
      return $this->db->insert_id();
      }

      function addReLocationOrderDetail($order){

      $this->db->insert_batch('STK_T_Relocate_Detail', $order);
      $afftectedRows=$this->db->affected_rows();
      return $afftectedRows;
      }

      function updateReLocationOrder($order,$where){
      if (array_key_exists("Estimate_Action_Date",$order) && $order["Estimate_Action_Date"]!=""){
      $this->db->set("Estimate_Action_Date", "CONVERT(datetime, '".$order["Estimate_Action_Date"]."', 103)", FALSE);
      $this->db->where($where);
      $this->db->update("STK_T_Relocate");
      }
      if (array_key_exists("Actual_Action_Date",$order) && $order["Actual_Action_Date"]!=""){
      $this->db->set("Actual_Action_Date", "CONVERT(datetime, '".$order["Actual_Action_Date"]."', 103)", FALSE);
      $this->db->where($where);
      $this->db->update("STK_T_Relocate");
      }
      unset($order["Estimate_Action_Date"]);
      unset($order["Actual_Action_Date"]);
      $this->db->where($where);
      $this->db->update("STK_T_Relocate",$order);
      }

      function updateReLocationOrderDetail($order,$where){

      $this->db->where($where);
      $this->db->update("STK_T_Relocate_Detail",$order);
      }

      function removeReLocationDetail($item_list,$order_id){
      $this->db->where_in('Old_Location_Id', $item_list);
      $this->db->where('Order_Id',$order_id);
      $this->db->delete('STK_T_Relocate_Detail');
      //echo $this->db->last_query();
      }

      function removeRLProductDetail($item_list,$order_id){
      $this->db->where_in('Inbound_Item_Id', $item_list);
      $this->db->where('Order_Id',$order_id);
      $this->db->delete('STK_T_Relocate_Detail');
      //echo $this->db->last_query();
      }
     */
}

?>