<?php

class Workflow_model extends CI_Model {

    private $_work_flow = "STK_T_Workflow";
    private $_stk_t_order = "STK_T_Order";
    private $_m_state = "SYS_M_State";

    function __construct() {
        parent::__construct();
    }

    function getWorkFlowAll() {
        $this->db->select("STK_T_Order.Flow_Id as Id,STK_T_Order.Present_State,STK_T_Order.Document_No,STK_T_Order.Doc_Refer_Ext,STK_T_Order.Doc_Refer_Int");
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query;
    }

    #add column Is_urgent,Create_Date for ISSUE 3312 : by kik : 20140121

    function getWorkFlowByModule($module) {  #Check Query Againt
        $this->db->select("STK_T_Workflow.Flow_Id as Id,SYS_M_State.State_NameEn,STK_T_Order.Doc_Refer_Ext,STK_T_Order.Doc_Refer_Int
		,STK_T_Workflow.Document_No
                , DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay
                ,STK_T_Order.Is_urgent
                ,STK_T_Order.Create_Date
                "); // Edit By Akkarapol, 16/09/2013, เพิ่ม SELECT DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay เข้าไปด้วย เนื่องจากต้องการค่าความต่างของวัน ระหว่างวันที่ Create Flow นี้ขึ้นมา กับ วันปัจจุบัน
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $this->db->where("SYS_M_Stateedge.Module", $module);
        $this->db->order_by("STK_T_Order.Is_urgent desc, STK_T_Order.Create_Date asc");  // add for ISSUE 3312 : by kik : 20140121
        $query = $this->db->get();
//         echo $this->db->last_query();
        return $query;
    }

    function getWorkFlowByModuleV2($module) {  #Check Query Againt
        $this->db->select("STK_T_Workflow.Flow_Id as Id
		,SYS_M_State.State_NameEn
		,STK_T_Order.Doc_Refer_Int
		,STK_T_Order.Doc_Refer_Ext
                ,STK_T_Workflow.Document_No
                , DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay
                ,STK_T_Order.Is_urgent
                ,STK_T_Order.Create_Date
                "); // Edit By Akkarapol, 16/09/2013, เพิ่ม SELECT DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay เข้าไปด้วย เนื่องจากต้องการค่าความต่างของวัน ระหว่างวันที่ Create Flow นี้ขึ้นมา กับ วันปัจจุบัน
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $this->db->where("SYS_M_Stateedge.Module", $module);
        $this->db->order_by("STK_T_Order.Is_urgent desc, STK_T_Order.Create_Date asc");  // add for ISSUE 3312 : by kik : 20140121
        $query = $this->db->get();
//         echo $this->db->last_query();
        return $query;
    }


    // Add By Akkarapol, 01/10/2013, เพิ่มฟังก์ชั่นการ query สำหรับ workflow Change Status
    #add column Is_urgent,Create_Date for ISSUE 3312 : by kik : 20140121
    function getWorkFlowChangeStatus($module) {  #Check Query Againt  >> Use for List Work Flow Process
        $this->db->select("  W.Flow_Id as Id
						,S.State_NameEn
						,R.Doc_Relocate as Doc_Refer_Ext
						,R.Doc_Relocate as Doc_Refer_Int
						,W.Document_No
						,R.Assigned_Id
						,R.Remark
						,(C1.First_NameTH+' '+C1.Last_NameTH) as Create_Name
						,(C2.First_NameTH+' '+C2.Last_NameTH) as Assigned_Name
                            , DATEDIFF(day,W.Create_Date,GETDATE()) as ProcessDay
                            ,R.Is_urgent
                            ,R.Create_Date");
        // Edit By Akkarapol, 16/09/2013, เพิ่ม SELECT DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay เข้าไปด้วย เนื่องจากต้องการค่าความต่างของวัน ระหว่างวันที่ Create Flow นี้ขึ้นมา กับ วันปัจจุบัน

        $this->db->from("STK_T_Workflow W");
        $this->db->join("STK_T_Relocate R", "W.Flow_Id = R.Flow_Id");
        $this->db->join("SYS_M_Stateedge ST", "W.Process_Id = ST.Process_Id AND ST.From_State = W.Present_State");
        $this->db->join("SYS_M_State S", "W.Present_State = S.State_No AND W.Process_Id = S.Process_Id");
        $this->db->join("ADM_M_UserLogin U1", "R.Create_By = U1.UserLogin_Id");
        $this->db->join("ADM_M_UserLogin U2", "R.Assigned_Id = U2.UserLogin_Id", "LEFT");
        $this->db->join("CTL_M_Contact C1", "U1.Contact_Id = C1.Contact_Id");
        $this->db->join("CTL_M_Contact C2", "U2.Contact_Id = C2.Contact_Id", "LEFT");
        $this->db->where("ST.Module", $module);
        $this->db->where("ST.Active", ACTIVE);
        $this->db->order_by("R.Is_urgent desc, R.Create_Date asc");  // add for ISSUE 3312 : by kik : 20140121
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    // END Add By Akkarapol, 01/10/2013, เพิ่มฟังก์ชั่นการ query สำหรับ workflow Change Status

    function getWorkFlowPending($module) {  #Check Query Againt  >> Use for List Work Flow Process
        $this->db->select("  W.Flow_Id as Id
						,S.State_NameEn
						,R.Doc_Relocate as Doc_Refer_Ext
						,R.Doc_Relocate as Doc_Refer_Int
						,W.Document_No
						,R.Assigned_Id
						,R.Remark
                                                ,(select dbo.[fNameUser](R.Create_By,1)) as Create_Name
						,(select dbo.[fNameUser](R.Assigned_Id,1)) as Assigned_Name
                                                ,R.Is_urgent
                                                ,R.Create_Date
                            , DATEDIFF(day,W.Create_Date,GETDATE()) as ProcessDay");
        // Edit By Akkarapol, 16/09/2013, เพิ่ม SELECT DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay เข้าไปด้วย เนื่องจากต้องการค่าความต่างของวัน ระหว่างวันที่ Create Flow นี้ขึ้นมา กับ วันปัจจุบัน
        $this->db->from("STK_T_Workflow W");
        $this->db->join("STK_T_Relocate R", "W.Flow_Id = R.Flow_Id");
        $this->db->join("SYS_M_Stateedge ST", "W.Process_Id = ST.Process_Id AND ST.From_State = W.Present_State");
        $this->db->join("SYS_M_State S", "W.Present_State = S.State_No AND W.Process_Id = S.Process_Id");

        /* comment by por 2013-10-02 เลิกใช้การ join เพื่อหาชื่อ เนื่องจากมี function ให้เรียกใช้อยู่แล้ว
         * และการ join นั้นไม่ถูกต้องเนื่องจากเลิกใช้ contact_id ไปแล้ว
          $this->db->join("ADM_M_UserLogin U1", "R.Create_By = U1.UserLogin_Id");
          $this->db->join("ADM_M_UserLogin U2", "R.Assigned_Id = U2.UserLogin_Id", "LEFT");
          $this->db->join("CTL_M_Contact C1", "U1.Contact_Id = C1.Contact_Id");
          end comment */
        //$this->db->join("ADM_M_UserLogin U2","R.Assigned_Id = U2.UserLogin_Id","LEFT");

        /* comment by por 2013-10-02 เลิกใช้การ join เพื่อหาชื่อ เนื่องจากมี function ให้เรียกใช้อยู่แล้ว
         * และการ join นั้นไม่ถูกต้องเนื่องจากเลิกใช้ contact_id ไปแล้ว
          $this->db->join("CTL_M_Contact C2", "R.Assigned_Id = C2.Contact_Id", "LEFT");
         */
        $this->db->where("ST.Module", $module);
        $this->db->order_by("R.Is_urgent desc, R.Create_Date asc");  // add for ISSUE 3312 : by kik : 20140121
        /*
          comment by por 2013-10-02 ตอน query ได้เปลี่ยนไปใช้ function แทน เนื่องจากข้อมูลที่เก็บในเบส เก็บเป็น user_id แล้ว
          โดยได้แก้ไขจาก
          ,(C1.First_NameTH+' '+C1.Last_NameTH) as Create_Name
          ,(C2.First_NameTH+' '+C2.Last_NameTH) as Assigned_Name
          เป็น
          ,(select dbo.[fNameUser](R.Create_By,1)) as Create_Name
          ,(select dbo.[fNameUser](R.Assigned_Id,1)) as Assigned_Name
         */
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

//        Add By Akkarapol, 02/09/2013, à¹€à¸žà¸´à¹ˆà¸¡à¸Ÿà¸±à¸‡à¸�à¹Œà¸Šà¸±à¹ˆà¸™à¹€à¸žà¸·à¹ˆà¸­ query à¹€à¸‰à¸žà¸²à¸° Partial Receive
    function getWorkFlowPartialReceive($module) {  #Check Query Againt  >> Use for List Work Flow Process
        $this->db->select("  W.Flow_Id as Id,
                            S.State_NameEn,
                            O.Doc_Refer_Ext,
                            O.Doc_Refer_Int,
                            W.Document_No
		");
        $this->db->from("STK_T_Workflow W");
        $this->db->join("SYS_M_Stateedge ST", "W.Process_Id = ST.Process_Id AND ST.From_State = W.Present_State");
        $this->db->join("SYS_M_State S", "W.Present_State = S.State_No AND W.Process_Id = S.Process_Id");
        $this->db->join("STK_T_Order O", "W.Flow_Id = O.Flow_Id");
        $this->db->where("ST.Module", $module);
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

//        END Add By Akkarapol, 02/09/2013, à¹€à¸žà¸´à¹ˆà¸¡à¸Ÿà¸±à¸‡à¸�à¹Œà¸Šà¸±à¹ˆà¸™à¹€à¸žà¸·à¹ˆà¸­ query à¹€à¸‰à¸žà¸²à¸° Partial Receive

    function getWorkFlowRepackage($module) {  #Check Query Againt  >> Use for List Work Flow Process
        $this->db->select("  W.Flow_Id as Id
						,S.State_NameEn
						,R.Doc_Relocate as Doc_Refer_Ext
						,R.Doc_Relocate as Doc_Refer_Int
						,W.Document_No
						,R.Assigned_Id
						,R.Remark
						,(C1.First_NameTH+' '+C1.Last_NameTH) as Create_Name
						,(C2.First_NameTH+' '+C2.Last_NameTH) as Assigned_Name
						,DATEDIFF(DAY,RD.Receive_Date,getdate()) AS Diff_Date
                            , DATEDIFF(day,W.Create_Date,GETDATE()) as ProcessDay");
        // Edit By Akkarapol, 16/09/2013, เพิ่ม SELECT DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay เข้าไปด้วย เนื่องจากต้องการค่าความต่างของวัน ระหว่างวันที่ Create Flow นี้ขึ้นมา กับ วันปัจจุบัน

        $this->db->from("STK_T_Workflow W");
        $this->db->join("STK_T_Relocate R", "W.Flow_Id = R.Flow_Id");
        $this->db->join("STK_T_Relocate_Detail RD", "R.Order_Id = RD.Order_Id");
        $this->db->join("SYS_M_Stateedge ST", "W.Process_Id = ST.Process_Id AND ST.From_State = W.Present_State");
        $this->db->join("SYS_M_State S", "W.Present_State = S.State_No AND W.Process_Id = S.Process_Id");
        $this->db->join("ADM_M_UserLogin U1", "R.Create_By = U1.UserLogin_Id");
        $this->db->join("CTL_M_Contact C1", "U1.Contact_Id = C1.Contact_Id");
        //$this->db->join("ADM_M_UserLogin U2","R.Assigned_Id = U2.UserLogin_Id","LEFT");
        $this->db->join("CTL_M_Contact C2", "R.Assigned_Id = C2.Contact_Id", "LEFT");
        $this->db->where("ST.Module", $module);
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query;
    }

    #add select  STK_T_Order.Is_urgent for ISSUE 3312 : by kik : 20140120
    #add real dispatch date for ISSUE 5265 : kik : 20141020
    function getFlowDetail($flow_id, $table = "STK_T_Order") {// Edit by kik : 27/08/2013 à¹ƒà¸ªà¹ˆ default à¸‚à¸­à¸‡ $table à¹€à¸›à¹‡à¸™ STK_T_Order à¹€à¸žà¸·à¹ˆà¸­à¹ƒà¸«à¹‰à¹‚à¸„à¹‰à¸”à¸—à¸µà¹ˆà¹€à¸£à¸µà¸¢à¸�à¹ƒà¸Šà¹‰à¸—à¸³à¸‡à¸²à¸™à¹„à¸”à¹‰à¸­à¸¢à¹ˆà¸²à¸‡à¸–à¸¹à¸�à¸•à¹‰à¸­à¸‡
//    function getFlowDetail($flow_id, $table="") {// Edit by Ton! 20130723 Add $table // comment by kik : 27/08/2013
        if ($table == "STK_T_Order") {// Edit by kik : 27/08/2013 à¹€à¸­à¸²à¹€à¸‡à¸·à¹ˆà¸­à¸™à¹„à¸‚ table = "" à¸­à¸­à¸�
//        if ($table=="STK_T_Order" || $table="") {// Edit by Ton! 20130814 || $table=""  //comment by kik : 27/08/2013
            $this->db->select("STK_T_Workflow.Process_Id as Id, STK_T_Workflow.Present_State, STK_T_Workflow.Document_No, STK_T_Workflow.Process_Id
                , STK_T_Order.Doc_Refer_Ext, STK_T_Order.Doc_Refer_Int, STK_T_Order.Doc_Refer_Inv, STK_T_Order.Doc_Refer_CE, STK_T_Order.Doc_Refer_BL
		, STK_T_Order.Order_Id, STK_T_Order.Owner_Id, STK_T_Order.Renter_Id, STK_T_Order.Vendor_Id, STK_T_Order.Source_Id, STK_T_Order.Destination_Id
		, STK_T_Order.Doc_Type, CONVERT(VARCHAR(10),STK_T_Order.Estimate_Action_Date,103) AS Est_Action_Date
                , CONVERT(VARCHAR(10),STK_T_Order.Real_Action_Date,103) AS Real_Action_Date
		, CONVERT(VARCHAR(10),STK_T_Order.Actual_Action_Date,103) AS Action_Date, STK_T_Order.Delivery_Date, STK_T_Order.Remark
		,SYS_M_Process.Process_Type, STK_T_Order.Vendor_Driver_Name, STK_T_Order.Vendor_Car_No, STK_T_Order.Is_Pending, STK_T_Order.Is_Repackage, STK_T_Order.Is_urgent
                , SYS_M_Stateedge.Module, SYS_M_Stateedge.Action_Type, SYS_M_Stateedge.Process_Id, SYS_M_Stateedge.Sub_Module
                , SYS_M_Stateedge.From_State, SYS_M_Stateedge.To_State ,STK_T_Order.Estimate_Action_Time , STK_T_Order.Destination_Detail");
//            Add , SYS_M_Stateedge.Module, SYS_M_Stateedge.Action_Type, SYS_M_Stateedge.Process_Id, SYS_M_Stateedge.Sub_Module, SYS_M_Stateedge.From_State, SYS_M_Stateedge.To_State // by Ton! 20130829
        } else if ($table == "STK_T_Counting") {
            $this->db->select("STK_T_Workflow.Process_Id as Id, STK_T_Workflow.Present_State, STK_T_Workflow.Document_No
                , STK_T_Workflow.Process_Id, SYS_M_Process.Process_Type, CONVERT(VARCHAR(10), STK_T_Counting.Estimate_Action_Date, 103) AS Est_Action_Date
                , CONVERT(VARCHAR(10), STK_T_Counting.Actual_Action_Date, 103) AS Action_Date, STK_T_Counting.*");
        } else if ($table == "STK_T_Relocate") {
            $this->db->select("STK_T_Workflow.Process_Id as Id, STK_T_Workflow.Present_State, STK_T_Workflow.Document_No
                , STK_T_Workflow.Process_Id, SYS_M_Process.Process_Type, CONVERT(VARCHAR(10), STK_T_Relocate.Estimate_Action_Date, 103) AS Est_Action_Date
                , CONVERT(VARCHAR(10), STK_T_Relocate.Actual_Action_Date, 103) AS Action_Date, STK_T_Relocate.*");
        }
        $this->db->from("STK_T_Workflow");
        $this->db->join("SYS_M_Process", "STK_T_Workflow.Process_Id = SYS_M_Process.Process_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        if ($table == "STK_T_Order") {
            $this->db->join("STK_T_Order", "STK_T_Workflow.Document_No = STK_T_Order.Document_No");
        } else if ($table == "STK_T_Counting") {
            $this->db->join("STK_T_Counting", "STK_T_Workflow.Document_No = STK_T_Counting.Document_No");
        } else if ($table == "STK_T_Relocate") {
//            $this->db->join("STK_T_Relocate", "STK_T_Workflow.Document_No = STK_T_Relocate.Document_No"); // Comment By Akkarapol, 04/11/2013, ใน DB ใช้เป็น STK_T_Relocate.Doc_Relocate ไม่ใช่ STK_T_Relocate.Document_No เพราะงั้น เวลาเอามา Join กันมันถึงไม่ได้ค่าสักที เพราะการเรียกใช้ผิด
            $this->db->join("STK_T_Relocate", "STK_T_Workflow.Document_No = STK_T_Relocate.Doc_Relocate"); // Add By Akkarapol, 04/11/2013, ใน DB ใช้เป็น STK_T_Relocate.Doc_Relocate ไม่ใช่ STK_T_Relocate.Document_No เพราะงั้น เวลาเอามา Join กันมันถึงไม่ได้ค่าสักที เพราะการเรียกใช้ผิด จึงต้องเปลี่ยนให้เป็นไปตามใน DB
        }
        $this->db->where("STK_T_Workflow.Flow_Id", $flow_id);
        $query = $this->db->get();
//        echo $this->db->last_query(); exit;
        $response = $query->result();

        // Check for redirect
        /* if (!isset($_COOKIE['connection'])) {
          $current_data = $this->uri->segment(1);
          $current_module = $response['0']->Module;
          if ($current_data != $current_module) {
          log_message('DEBUG', 'State not correct -> redirect');
          $result = $this->get_return_path($current_data);
          redirect($result['0']->NavigationUri);
          exit();
          } else {
          return $response;
          }
          } else { */
        return $response;
        //}
        // END Check
    }

    function getFlowDetail_abnormal_flow($flow_id, $table = "STK_T_Order") {
        if ($table == "STK_T_Order") {
            $this->db->select("STK_T_Workflow.Process_Id as Id, STK_T_Workflow.Present_State, STK_T_Workflow.Document_No, STK_T_Workflow.Process_Id
                , STK_T_Order.Doc_Refer_Ext, STK_T_Order.Doc_Refer_Int, STK_T_Order.Doc_Refer_Inv, STK_T_Order.Doc_Refer_CE, STK_T_Order.Doc_Refer_BL
		, STK_T_Order.Order_Id, STK_T_Order.Owner_Id, STK_T_Order.Renter_Id, STK_T_Order.Vendor_Id, STK_T_Order.Source_Id, STK_T_Order.Destination_Id
		, STK_T_Order.Doc_Type, CONVERT(VARCHAR(10),STK_T_Order.Estimate_Action_Date,103) AS Est_Action_Date
		, CONVERT(VARCHAR(10),STK_T_Order.Actual_Action_Date,103) AS Action_Date, STK_T_Order.Delivery_Date, STK_T_Order.Remark
		, SYS_M_Process.Process_Type, STK_T_Order.Vendor_Driver_Name, STK_T_Order.Vendor_Car_No, STK_T_Order.Is_Pending, STK_T_Order.Is_Repackage, STK_T_Order.Is_urgent
                --, SYS_M_Stateedge.Module, SYS_M_Stateedge.Action_Type, SYS_M_Stateedge.Process_Id, SYS_M_Stateedge.Sub_Module
                --, SYS_M_Stateedge.From_State, SYS_M_Stateedge.To_State
                ");
        } else if ($table == "STK_T_Counting") {
            $this->db->select("STK_T_Workflow.Process_Id as Id, STK_T_Workflow.Present_State, STK_T_Workflow.Document_No
                , STK_T_Workflow.Process_Id, SYS_M_Process.Process_Type, CONVERT(VARCHAR(10), STK_T_Counting.Estimate_Action_Date, 103) AS Est_Action_Date
                , CONVERT(VARCHAR(10), STK_T_Counting.Actual_Action_Date, 103) AS Action_Date, STK_T_Counting.*");
        } else if ($table == "STK_T_Relocate") {
            $this->db->select("STK_T_Workflow.Process_Id as Id, STK_T_Workflow.Present_State, STK_T_Workflow.Document_No
                , STK_T_Workflow.Process_Id, SYS_M_Process.Process_Type, CONVERT(VARCHAR(10), STK_T_Relocate.Estimate_Action_Date, 103) AS Est_Action_Date
                , CONVERT(VARCHAR(10), STK_T_Relocate.Actual_Action_Date, 103) AS Action_Date, STK_T_Relocate.*");
        }
        $this->db->from("STK_T_Workflow");
        $this->db->join("SYS_M_Process", "STK_T_Workflow.Process_Id = SYS_M_Process.Process_Id");
//        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        if ($table == "STK_T_Order") {
            $this->db->join("STK_T_Order", "STK_T_Workflow.Document_No = STK_T_Order.Document_No");
        } else if ($table == "STK_T_Counting") {
            $this->db->join("STK_T_Counting", "STK_T_Workflow.Document_No = STK_T_Counting.Document_No");
        } else if ($table == "STK_T_Relocate") {
            $this->db->join("STK_T_Relocate", "STK_T_Workflow.Document_No = STK_T_Relocate.Doc_Relocate"); // Add By Akkarapol, 04/11/2013, ใน DB ใช้เป็น STK_T_Relocate.Doc_Relocate ไม่ใช่ STK_T_Relocate.Document_No เพราะงั้น เวลาเอามา Join กันมันถึงไม่ได้ค่าสักที เพราะการเรียกใช้ผิด จึงต้องเปลี่ยนให้เป็นไปตามใน DB
        }
        $this->db->where("STK_T_Workflow.Flow_Id", $flow_id);
        $query = $this->db->get();
//		echo $this->db->last_query();

        $response = $query->result();

        return $response;
    }

    // Ak, create function for get detail in tb order by Document_No
    function getOrderDetailByDocumentNo($documentNo) {// Duplicate Function !!
        $this->db->select('*');
        $this->db->from("STK_T_Order");
        $this->db->where("STK_T_Order.Document_No", $documentNo);
        $query = $this->db->get();
        return $query->result();
    }

//    Comment Out by Ton! Not Used. 20131031
//    function getFlowRelocate($flow_id) {
//        $this->db->select("STK_T_Workflow.Process_Id as Id
//							,STK_T_Workflow.Present_State
//							,STK_T_Workflow.Process_Id
//							,SYS_M_Process.Process_Type
//							,STK_T_Relocate.Order_Id
//							");
//        $this->db->from("STK_T_Workflow");
//        $this->db->join("SYS_M_Process", "STK_T_Workflow.Process_Id = SYS_M_Process.Process_Id");
//        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
//        $this->db->join("STK_T_Relocate", "STK_T_Workflow.Document_No = STK_T_Relocate.Doc_Relocate");
//        $this->db->where("STK_T_Workflow.Flow_Id", $flow_id);
//        $query = $this->db->get();
//        //echo $this->db->last_query();
//        return $query->result();
//    }

    /**
     * Get Order Detail By Document In
     * Search order detail by set of document no ** duplicate from getOrderDetailByDocumentNo for search in group
     *
     * @param Array $documentno Document No for search detail of order
     * @return Object List of order detail
     */
    function getOrderDetailByDocumentNoIn( $documentNo ) {// Duplicate Function !!
        $this->db->select( $this->_stk_t_order . ".* , " . $this->_m_state . ".State_NameTh");
        $this->db->from( $this->_stk_t_order );
        $this->db->join( $this->_work_flow  , $this->_work_flow . ".Flow_Id = " . $this->_stk_t_order . ".Flow_Id " );
        $this->db->join( $this->_m_state  , $this->_m_state . ".Process_Id = " . $this->_work_flow . ".Process_Id AND " . $this->_m_state . ".State_No = " . $this->_work_flow . ".Present_State");
        $this->db->where( $this->_stk_t_order . ".Document_No In (". implodeArrayQuote( $documentNo ) .")");
        $query = $this->db->get();
        return $query->result();
    }

    function getPresentState($process_id, $present_state) {// Duplicate Function !!
        $this->db->select("*");
        $this->db->from("SYS_M_Stateedge");
        $this->db->where("Process_Id", $process_id);
        $this->db->where("From_State", $present_state);
        $this->db->where("Active", ACTIVE);
        $query = $this->db->get();
        return $query->result();
    }

    function getProcessIDbyFlowID($flow_id) {
        $this->db->select("Process_Id");
        $this->db->from("STK_T_Workflow");
        $this->db->where("Flow_Id", $flow_id);
        $query = $this->db->get();
        return $query;
    }

    function getPresentStatebyFlowID($flow_id) {
        $this->db->select("Present_State");
        $this->db->from("STK_T_Workflow");
        $this->db->where("Flow_Id", $flow_id);
        $this->db->where("Active", ACTIVE);
        $query = $this->db->get();
        return $query;
    }

    function getNextState($process_id, $to_state) {// Duplicate Function !!
        $this->db->select("*");
        $this->db->from("SYS_M_Stateedge");
        $this->db->where("Process_Id", $process_id);
        $this->db->where("From_State", $to_state);
        $this->db->where("Active", ACTIVE);
        $query = $this->db->get();
        // p($this->db->last_query());
        // exit;
        return $query->result();
    }

    function getStateName($process_id, $present_state) {
        $this->db->select("*");
        $this->db->from("SYS_M_State");
        $this->db->where("Process_Id", $process_id);
        $this->db->where("State_No", $present_state);
        $query = $this->db->get();
        return $query->result();
    }

    //add order button asc : by kik : 2013-11-28
    //add order button by Sequence : by kik : 2013-12-03
    function getStateedgeDetail($process_id, $present_state) {
        $this->db->select("ste.Edge_Id
            ,ste.Description
            ,p.Process_Id
            ,p.Process_Type
            ,p.Process_NameEn as Process_Name
            ,ste.From_State
            ,st1.State_NameEn as From_State_Name
            ,ste.Action_Type
            ,ste.To_State
            ,st2.State_NameEn as To_State_Name
            ,ste.Module
            ,ste.Sub_Module
            ,ste.Form
            ,st2.Sequence");
        $this->db->from("SYS_M_Stateedge ste");
        $this->db->join("SYS_M_Process p", "ste.Process_Id = p.Process_Id");
        $this->db->join("SYS_M_State st1", "ste.Process_Id = st1.Process_Id AND ste.From_State = st1.State_No ");
        $this->db->join("SYS_M_State st2", "ste.Process_Id = st2.Process_Id AND ste.To_State = st2.State_No ");
        $this->db->where("ste.Process_Id", $process_id);
        $this->db->where("ste.From_State", $present_state);
//        $this->db->order_by("CASE To_State WHEN -2 THEN 1 ELSE 0 END ASC , To_State ASC");
        $this->db->order_by("Sequence ASC");
        $this->db->where("ste.Active", ACTIVE);
        $query = $this->db->get();
        return $query->result();
    }

    function getStateActionDetail($process_id, $present_state, $next_state) {
        $this->db->select("ste.Edge_Id
							,ste.Process_Id
							,ste.From_State
							,st1.State_NameEn as From_State_Name
							,ste.Action_Type
							,ste.To_State
							,st2.State_NameEn as To_State_Name
							,ste.Module
							,ste.Sub_Module
							,ste.Form");
        $this->db->from("SYS_M_Stateedge ste");
        $this->db->join("SYS_M_State st1", "ste.Process_Id = st1.Process_Id AND ste.From_State = st1.State_No ");
        $this->db->join("SYS_M_State st2", "ste.Process_Id = st2.Process_Id AND ste.To_State = st2.State_No ");
        $this->db->where("ste.Process_Id", $process_id);
        $this->db->where("ste.From_State", $present_state);
        $this->db->where("ste.To_State", $next_state);
        $this->db->where("ste.Active", ACTIVE);
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query->result();
    }

//              --#ISSUE 2158
//              --#DATE:2012-08-29
//              --#BY:KIK
//              --#à¸›à¸±à¸�à¸«à¸²:à¹„à¸¡à¹ˆà¸¡à¸µà¸�à¸²à¸£ add à¸„à¹ˆà¸² parent_id à¹€à¸‚à¹‰à¸²à¹„à¸›à¸¢à¸±à¸‡ table
//              --#à¸ªà¸²à¹€à¸«à¸•à¸¸:à¸‚à¸­à¸‡à¹€à¸�à¹ˆà¸²à¹„à¸¡à¹ˆà¸¡à¸µà¸�à¸²à¸£à¸£à¸±à¸šà¸„à¹ˆà¸² flow_id à¹€à¸�à¹ˆà¸²à¹„à¸›à¹€à¸žà¸·à¹ˆà¸­à¹ƒà¸ªà¹ˆà¹ƒà¸™ parent_id à¸‚à¸­à¸‡ flow à¹ƒà¸«à¸¡à¹ˆ à¸—à¸³à¹ƒà¸«à¹‰à¸­à¹‰à¸²à¸‡à¸­à¸´à¸‡à¸„à¹ˆà¸²à¸�à¸±à¸™à¹„à¸¡à¹ˆà¹„à¸”à¹‰
//              --#à¸§à¸´à¸˜à¸µà¸�à¸²à¸£à¹�à¸�à¹‰:à¸£à¸±à¸šà¸„à¹ˆà¸² parameter à¹€à¸›à¹‡à¸™  flow_id à¹€à¸�à¹ˆà¸² à¹€à¸‚à¹‰à¸²à¸¡à¸²à¹€à¸�à¹‡à¸šà¹„à¸§à¹‰à¹ƒà¸™à¸•à¸±à¸§à¹�à¸›à¸£ $parent_id
//              -- START Old Comment Code #ISSUE 2158
//
//    function addWorkFlowTrax($process_id, $present_state, $next_state, $data) {
//        $data_flow = array(
//            'Process_Id' => $process_id
//            , 'Present_State' => $next_state
//            , 'Document_No' => $data["Document_No"]
//            , 'Create_By' => '1'
//            , 'Create_Date' => date("Y-m-d")
//            , 'Modified_By' => '1'
//            , 'Modified_Date' => date("Y-m-d")
//        );
//        $this->db->insert('STK_T_Workflow', $data_flow);
//        return $this->db->insert_id();
//    }
//              -- END Old Comment Code #ISSUE 2158
//              -- START New Code #ISSUE 2158
//    function addWorkFlowTrax($process_id, $present_state, $next_state, $data, $parent_id = NULL) {// à¸£à¸±à¸šà¸žà¸²à¸£à¸²à¸¡à¸´à¹€à¸•à¸­à¸£à¹Œà¹€à¸žà¸´à¹ˆà¸¡à¹€à¸›à¹‡à¸™ $parent_id (Flow_id à¸‚à¸­à¸‡à¹€à¸�à¹ˆà¸²)
    function addWorkFlowTrax($process_id, $next_state, $data, $parent_id = NULL) {// Edit by Ton! 20131021
        $data_flow = array(
            'Process_Id' => $process_id
            , 'Present_State' => $next_state
            , 'Document_No' => $data["Document_No"]
            //--#Comment 2013-09-10 BY POR à¹�à¸�à¹‰à¹„à¸‚à¹„à¸¡à¹ˆà¹ƒà¸«à¹‰à¸Ÿà¸´à¸�à¹‚à¸„à¹‰à¸”
            /* , 'Create_By' => '1' */

            //--#à¹�à¸�à¹‰à¹„à¸‚à¹ƒà¸«à¹‰à¹€à¸£à¸µà¸¢à¸�à¸£à¸«à¸±à¸ª user à¸ˆà¸²à¸� session #DATE:2013-09-10 #BY:POR
            //-- START --
            , 'Create_By' => $this->session->userdata("user_id")
            //-- END --
            , 'Create_Date' => date("Y-m-d")
            //--#Comment 2013-09-10 BY POR à¹�à¸�à¹‰à¹„à¸‚à¹„à¸¡à¹ˆà¹ƒà¸«à¹‰à¸Ÿà¸´à¸�à¹‚à¸„à¹‰à¸”
            /* , 'Modified_By' => '1' */

            //--#à¹�à¸�à¹‰à¹„à¸‚à¹ƒà¸«à¹‰à¹€à¸£à¸µà¸¢à¸�à¸£à¸«à¸±à¸ª user à¸ˆà¸²à¸� session #DATE:2013-09-10 #BY:POR
            //-- START --
            , 'Modified_By' => $this->session->userdata("user_id")
            //-- END --
            , 'Modified_Date' => date("Y-m-d")
            , 'Parent_Flow' => $parent_id // à¹€à¸žà¸´à¹ˆà¸¡ $parent_id à¹€à¸‚à¹‰à¸²à¹„à¸›à¸¢à¸±à¸‡à¸Ÿà¸´à¸§à¸”à¹Œ Parent_Flow
        );

        $this->db->insert('STK_T_Workflow', $data_flow);
        return $this->db->insert_id();
    }

//             -- END New Code #ISSUE 2158


    function addWorkFlowAction($process_id, $flow_id, $action_type, $present_state) {// Edit by Ton! 20131021
        $data_action = array(
            'Process_Id' => $process_id
            , 'Flow_Id' => $flow_id
            , 'Action_State' => $present_state
            , 'Action_Type' => $action_type
            //--#Comment 2013-09-10 BY POR à¹�à¸�à¹‰à¹„à¸‚à¹„à¸¡à¹ˆà¹ƒà¸«à¹‰à¸Ÿà¸´à¸�à¹‚à¸„à¹‰à¸”
            /* , 'Action_By' => '1' */

            //--#à¹�à¸�à¹‰à¹„à¸‚à¹ƒà¸«à¹‰à¹€à¸£à¸µà¸¢à¸�à¸£à¸«à¸±à¸ª user à¸ˆà¸²à¸� session #DATE:2013-09-10 #BY:POR
            //-- START --
            , 'Action_By' => $this->session->userdata("user_id")
            //-- END --
            , 'Action_Date' => date("Y-m-d")
        );
        $this->db->insert('STK_T_Action', $data_action);
        return $this->db->insert_id();
    }

    /**
     * @function updateWorkFlowTrax for work update workflow table and add data into STK_T_Action table
     * @param int $process_id
     * @param int $flow_id
     * @param string $action_type
     * @param int $present_state
     * @param int $next_state
     * @param array $data
     * @return int (action_id)
     *
     * @last_modified : kik : 20140304
     */
    function updateWorkFlowTrax($process_id, $flow_id, $action_type, $present_state, $next_state, $data) {
        $update = array(
            'Present_State' => $next_state
            , 'Modified_By' => $this->session->userdata("user_id")
            , 'Modified_Date' => date("Y-m-d")
        );
        $where = array(
            'Process_Id' => $process_id
            , 'Flow_Id' => $flow_id
        );
        $this->db->where($where);
        $this->db->update('STK_T_Workflow', $update);
        unset($update);
        $afftectedRows = $this->db->affected_rows();
        $action_id = "";

        if ($afftectedRows > 0):
            $action_id = $this->addWorkFlowAction($process_id, $flow_id, $action_type, $present_state, $next_state, $data);
        endif;

        return $action_id;
    }

    function getWorkFlowByCounting($module, $id) {  #Check Query Againt
        if ($id == 1) {
            $this->db->select('STK_T_Workflow.Flow_Id as Id,STK_T_Workflow.Document_No');
            $this->db->select('SYS_M_State.State_NameEn');
            $this->db->select('STK_T_Counting.Create_Date');
            $this->db->select('STK_T_Counting.Is_urgent');
            $this->db->select('STK_T_Counting.Counting_Type');
            $this->db->select('CONVERT(VARCHAR,Date_To,103) as Date_To');
            $this->db->select("CASE
                                            WHEN STK_T_Counting.Counting_Type = 'CT01'
                                                THEN
                                                    CASE
                                                        WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > '0'
                                                        THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
                                                        ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
                                                    END
                                            WHEN STK_T_Counting.Counting_Type = 'CT02'
                                                THEN
                                                    CASE
                                                        WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 7
                                                        THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
                                                        ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
                                                    END
                                            WHEN STK_T_Counting.Counting_Type = 'CT03'
                                                THEN
                                                    CASE
                                                        WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 30
                                                        THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
                                                        ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
                                                    END
                                            WHEN STK_T_Counting.Counting_Type = 'CT04'
                                                THEN
                                                    CASE
                                                        WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 90
                                                        THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
                                                        ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
                                                    END
                                            WHEN STK_T_Counting.Counting_Type = 'CT05'
                                                THEN
                                                    CASE
                                                        WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 365
                                                        THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
                                                        ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
                                                    END
                                            ELSE  CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
                                        END
                                        AS ProcessDay");
            $this->db->from('STK_T_Workflow');
            $this->db->join('SYS_M_Stateedge', 'STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State');
            $this->db->join('SYS_M_State','SYS_M_State.Process_Id = STK_T_Workflow.Process_Id AND STK_T_Workflow.Present_State =  SYS_M_State.State_No');
            $this->db->join('STK_T_Counting','STK_T_Counting.Flow_Id = STK_T_Workflow.Flow_Id');
            $this->db->join('STK_L_Counting_Order','STK_T_Counting.Flow_Id=STK_L_Counting_Order.Count_Id');
            $this->db->where('STK_T_Workflow.Process_Id',$module);
            $this->db->order_by('ORDER BY STK_T_Counting.Is_urgent desc, STK_T_Counting.Create_Date asc');
            p($this->db->last_query());
            exit;
            $str = " SELECT
                            -- STK_T_Workflow.Flow_Id as Id,STK_T_Workflow.Document_No
                            -- ,SYS_M_State.State_NameEn
                            -- ,STK_T_Counting.Create_Date
                            -- ,STK_T_Counting.Is_urgent
                            -- Edit By Akkarapol, 22/01/2014, Comment CASE WHEN THEN ELSE END ทิ้ง เพราะ Document Type มันต้องโชว์ ที่หน้า List มันต้องโชว์ Counting_Type ไม่ใช่ Working Day ซึ่ง Working Day นั้น จะให้ไปใช้ที่ Process Day แทน
                            -- ,STK_T_Counting.Counting_Type
                            --, CASE
				--WHEN STK_T_Counting.Counting_Type = 'CT01'
				--THEN
                                        --#Comment Date:2013-09-10 #1814 Edit working_Day to Over Due not use +1 #By POR
					--CASE WHEN CAST(DATEDIFF(day,STK_T_Counting.Create_Date,GETDATE()) AS VARCHAR) > '1'
						--THEN CAST(DATEDIFF(day,STK_T_Counting.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
                                                --ELSE CAST(DATEDIFF(day,STK_T_Counting.Create_Date,GETDATE()+1)  AS VARCHAR)
                                        --END
                                        --#End comment

                                        --#1814 2013-09-10 #1814 Edit working_Day to Over Due not use +1 #By POR
                                        --CASE WHEN CAST(DATEDIFF(day,STK_T_Counting.Create_Date,GETDATE()) AS VARCHAR) > '0'
						--THEN CAST(DATEDIFF(day,STK_T_Counting.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
                                               -- ELSE CAST(DATEDIFF(day,STK_T_Counting.Create_Date,GETDATE())  AS VARCHAR)
                                         --END
                                        --END Code

					--WHEN STK_T_Counting.Counting_Type = 'CT02'
					--	THEN 'CT02'
					--WHEN STK_T_Counting.Counting_Type = 'CT03'
					--	THEN 'CT03'
            		--ELSE STK_T_Counting.Counting_Type

					--END AS 'Working_Day'
                            -- END Edit By Akkarapol, 22/01/2014, Comment CASE WHEN THEN ELSE END ทิ้ง เพราะ Document Type มันต้องโชว์ ที่หน้า List มันต้องโชว์ Counting_Type ไม่ใช่ Working Day ซึ่ง Working Day นั้น จะให้ไปใช้ที่ Process Day แทน

                            --#1814 2013-09-10 #Show Date_To By POR
                            -- ,CONVERT(VARCHAR,Date_To,103) as Date_To
                            --#END CODE
                            -- Add By Akkarapol, 16/09/2013, เพิ่ม SELECT  DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay เข้าไปด้วย เนื่องจากต้องการค่าความต่างของวัน ระหว่างวันที่ Create Flow นี้ขึ้นมา กับ วันปัจจุบัน
                            -- Add By Akkarapol, 22/01/2014, เพิ่ม CASE WHEN THEN ELSE END เข้าไปเพื่อตรวจสอบว่า ProcessDay ที่ได้นั้น เป็น Delayed หรือไม่ ถ้าเป็นก็จะมี text ว่า (Delayed) ต่อท้าย ProcessDay ด้วยในการแสดงผลที่หน้า List
                            --, DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay
                                        -- ,CASE
                                        --     WHEN STK_T_Counting.Counting_Type = 'CT01'
                                        --         THEN
                                        --             CASE
                                        --                 WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > '0'
                                        --                 THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
                                        --                 ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
                                        --             END
                                        --     WHEN STK_T_Counting.Counting_Type = 'CT02'
                                        --         THEN
                                        --             CASE
                                        --                 WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 7
                                        --                 THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
                                        --                 ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
                                        --             END
                                        --     WHEN STK_T_Counting.Counting_Type = 'CT03'
                                        --         THEN
                                        --             CASE
                                        --                 WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 30
                                        --                 THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
                                        --                 ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
                                        --             END
                                        --     WHEN STK_T_Counting.Counting_Type = 'CT04'
                                        --         THEN
                                        --             CASE
                                        --                 WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 90
                                        --                 THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
                                        --                 ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
                                        --             END
                                        --     WHEN STK_T_Counting.Counting_Type = 'CT05'
                                        --         THEN
                                        --             CASE
                                        --                 WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 365
                                        --                 THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
                                        --                 ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
                                        --             END
                                        --     ELSE  CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
                                        -- END
                                        -- AS ProcessDay
                            -- END Add By Akkarapol, 22/01/2014, เพิ่ม CASE WHEN THEN ELSE END เข้าไปเพื่อตรวจสอบว่า ProcessDay ที่ได้นั้น เป็น Delayed หรือไม่ ถ้าเป็นก็จะมี text ว่า (Delayed) ต่อท้าย ProcessDay ด้วยในการแสดงผลที่หน้า List

                            -- END Add By Akkarapol, 16/09/2013, เพิ่ม SELECT  DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay เข้าไปด้วย เนื่องจากต้องการค่าความต่างของวัน ระหว่างวันที่ Create Flow นี้ขึ้นมา กับ วันปัจจุบัน
                            ,'Assign To?' as Assign
                        FROM STK_T_Workflow
                        JOIN SYS_M_Stateedge  ON STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id
                        AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State
                        JOIN SYS_M_State ON SYS_M_State.Process_Id = STK_T_Workflow.Process_Id
                        AND STK_T_Workflow.Present_State =  SYS_M_State.State_No
                        JOIN STK_T_Counting ON STK_T_Counting.Flow_Id = STK_T_Workflow.Flow_Id
                        --#1814 2013-09-10 #Show Date_To By POR
                        --JOIN STK_L_Counting_Order ON STK_T_Counting.Counting_Type=STK_L_Counting_Order.Count_Type
						-- Change By Ball
            			JOIN STK_L_Counting_Order ON STK_T_Counting.Flow_Id=STK_L_Counting_Order.Count_Id
                        --#END CODE
                        WHERE STK_T_Workflow.Process_Id = " . $module;
            $str.=" ORDER BY STK_T_Counting.Is_urgent desc, STK_T_Counting.Create_Date asc";
        } else if ($id == 0) {

            $this->db->select('STK_T_Workflow.Flow_Id as Id,STK_T_Workflow.Document_No');
            $this->db->select('SYS_M_State.State_NameEn');
            $this->db->select('STK_T_Counting.Create_Date');
            $this->db->select('STK_T_Counting.Is_urgent');
            $this->db->select('STK_T_Counting.Counting_Type');
            // $this->db->select('CONVERT(VARCHAR,Date_To,103) as Date_To');
            // $this->db->select("CASE
            //                                 WHEN STK_T_Counting.Counting_Type = 'CT01'
            //                                     THEN
            //                                         CASE
            //                                             WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > '0'
            //                                             THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
            //                                             ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
            //                                         END
            //                                 WHEN STK_T_Counting.Counting_Type = 'CT02'
            //                                     THEN
            //                                         CASE
            //                                             WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 7
            //                                             THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
            //                                             ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
            //                                         END
            //                                 WHEN STK_T_Counting.Counting_Type = 'CT03'
            //                                     THEN
            //                                         CASE
            //                                             WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 30
            //                                             THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
            //                                             ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
            //                                         END
            //                                 WHEN STK_T_Counting.Counting_Type = 'CT04'
            //                                     THEN
            //                                         CASE
            //                                             WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 90
            //                                             THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
            //                                             ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
            //                                         END
            //                                 WHEN STK_T_Counting.Counting_Type = 'CT05'
            //                                     THEN
            //                                         CASE
            //                                             WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 365
            //                                             THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
            //                                             ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
            //                                         END
            //                                 ELSE  CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
            //                             END
            //                             AS ProcessDay");
            $this->db->from('STK_T_Workflow');
            $this->db->join('SYS_M_Stateedge', 'STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State');
            $this->db->join('SYS_M_State','SYS_M_State.Process_Id = STK_T_Workflow.Process_Id AND STK_T_Workflow.Present_State =  SYS_M_State.State_No');
            $this->db->join('STK_T_Counting','STK_T_Counting.Flow_Id = STK_T_Workflow.Flow_Id');
            $this->db->join('STK_L_Counting_Order','STK_T_Counting.Flow_Id=STK_L_Counting_Order.Count_Id');
            $this->db->where('STK_T_Workflow.Process_Id',$module);
            $this->db->order_by('STK_T_Counting.Is_urgent desc, STK_T_Counting.Create_Date asc');
            // p($this->db->last_query());
            // exit;
            // $str = " SELECT
            //                   STK_T_Workflow.Flow_Id as Id,STK_T_Workflow.Document_No
            //                 ,SYS_M_State.State_NameEn
            //                 ,STK_T_Counting.Create_Date
            //                 ,STK_T_Counting.Is_urgent
            //                 ,STK_T_Counting.Counting_Type
            //                 ,CONVERT(VARCHAR,Date_To,103) as Date_To
            //                 ,CASE
            //                                 WHEN STK_T_Counting.Counting_Type = 'CT01'
            //                                     THEN
            //                                         CASE
            //                                             WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > '0'
            //                                             THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
            //                                             ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
            //                                         END
            //                                 WHEN STK_T_Counting.Counting_Type = 'CT02'
            //                                     THEN
            //                                         CASE
            //                                             WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 7
            //                                             THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
            //                                             ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
            //                                         END
            //                                 WHEN STK_T_Counting.Counting_Type = 'CT03'
            //                                     THEN
            //                                         CASE
            //                                             WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 30
            //                                             THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
            //                                             ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
            //                                         END
            //                                 WHEN STK_T_Counting.Counting_Type = 'CT04'
            //                                     THEN
            //                                         CASE
            //                                             WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 90
            //                                             THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
            //                                             ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
            //                                         END
            //                                 WHEN STK_T_Counting.Counting_Type = 'CT05'
            //                                     THEN
            //                                         CASE
            //                                             WHEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) > 365
            //                                             THEN CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR) + '&nbsp<span style=\"color:red;\">(Delayed)</span>'
            //                                             ELSE CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
            //                                         END
            //                                 ELSE  CAST(DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) AS VARCHAR)
            //                             END
            //                             AS ProcessDay
            //             FROM STK_T_Workflow
            //             JOIN SYS_M_Stateedge  ON STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id
            //             AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State
            //             JOIN SYS_M_State ON SYS_M_State.Process_Id = STK_T_Workflow.Process_Id
            //             AND STK_T_Workflow.Present_State =  SYS_M_State.State_No
            //             JOIN STK_T_Counting ON STK_T_Counting.Flow_Id = STK_T_Workflow.Flow_Id
			// 			JOIN STK_L_Counting_Order ON STK_T_Counting.Flow_Id=STK_L_Counting_Order.Count_Id
            //             WHERE STK_T_Workflow.Process_Id = " . $module;
            // $str.=" ORDER BY STK_T_Counting.Is_urgent desc, STK_T_Counting.Create_Date asc";
        }
        $query = $this->db->get();

        // $query = $this->db->query();
        // p($query);
        // exit;

        return $query;
    }

    /**
     *
     * @param unknown $flow_id
     */
    function getFlowDetailForCounting($flow_id) {
        $str = "SELECT STK_T_Workflow.Process_Id as Id
		,STK_T_Workflow.Present_State
		,STK_T_Workflow.Document_No
		,STK_T_Workflow.Process_Id
		,STK_T_Counting.Order_Id
		,STK_T_Counting.Estimate_Action_Date as  Est_Receive_Date
		,STK_T_Counting.Actual_Action_Date as  Receive_Date
		,SYS_M_Stateedge.Module as Module
            FROM STK_T_Workflow
            JOIN SYS_M_Stateedge ON STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State
            JOIN STK_T_Counting ON STK_T_Workflow.Document_No = STK_T_Counting.Document_No
            WHERE STK_T_Workflow.Flow_Id  = " . $flow_id;
        $query = $this->db->query($str);
        //echo $this->db->last_query();
        return $query->result();
    }

    /**
     *
     * @param unknown $ProcessID
     * @return unknown
     */
    function getProcess($ProcessID) {
        $this->db->select("*");
        $this->db->from("SYS_M_Process");
        $this->db->where("SYS_M_Process.Process_Id", $ProcessID);
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query;
    }

    public function update_relocation_workflow($flow_id, $process_id, $present_state) {
        $update = array(
            'Present_State' => $present_state
            , 'Modified_By' => $this->session->userdata("user_id")
            , 'Modified_Date' => date("Y-m-d")
        );
        $where = array(
            'Process_Id' => $process_id
            , 'Flow_Id' => $flow_id
        );
        $this->db->where($where);
        $this->db->update('STK_T_Workflow', $update);
        return $this->db->affected_rows();
    }

    public function update_workflow_counting($flow_id, $data) {
        $this->db->where(array('Flow_Id' => $flow_id));
        $this->db->update('STK_T_Workflow', $data);
        return $this->db->affected_rows();
    }

    function getProcessDetailByProcessId($ProcessID) {
        $this->db->select("*");
        $this->db->from("SYS_M_Process");
        $this->db->where("SYS_M_Process.Process_Id", $ProcessID);
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query;
    }

    //ADD function getWorkflowTable :BY KIK 2013-12-02 สร้าง function ใหม่โดยให้ส่งค่าดาต้าที่ต้องการ select และ เงื่อนไข where ออกมา
    function getWorkflowTable($column, $where) {
        $this->db->select($column);
        $this->db->where($where);
        $this->db->from("STK_T_Workflow");
        $query = $this->db->get();
        return $query->result();
    }

    //END ADD

    function get_SYS_M_Stateedge_Module() {
        $this->db->select("SYS_M_Stateedge.Module");
        $this->db->from("SYS_M_Stateedge");
        $this->db->where("SYS_M_Stateedge.Active", "Y");
        $this->db->group_by("SYS_M_Stateedge.Module");
        $this->db->order_by("SYS_M_Stateedge.Module");
        $query = $this->db->get();
        return $query;
    }

    function get_SYS_M_Stateedge_by_Module($Module = NULL) {
        $this->db->select("SYS_M_Stateedge.*");
        $this->db->from("SYS_M_Stateedge");
        if ($Module != NULL):
            $this->db->where("SYS_M_Stateedge.Module", $Module);
        endif;
        $this->db->where("SYS_M_Stateedge.Active", "Y");
        $this->db->order_by("SYS_M_Stateedge.Process_Id, SYS_M_Stateedge.From_State, SYS_M_Stateedge.To_State");
        $query = $this->db->get();
        return $query;
    }

    public function get_return_path($controller) {
        $this->db->select("NavigationUri");
        $this->db->where("Module", $controller);
        $response = $this->db->get("ADM_M_MenuBar");
        return $response->result();
    }

	    /**
     *
     * Get Document From Flow
     * Search for Document No from FlowID
     *
     * @param Array $flow_id flowId for input for search document
     * @return
     */
    public function get_document_from_flow( $flow_id ) {

        $this->db->select("Document_No");
        $this->db->where("Flow_Id In (". implodeArrayQuote( $flow_id ) .")");
        $response = $this->db->get( $this->_work_flow);
        $results = Array();
        foreach ( $response->result() as $idx => $val ) :
            $results[] = $val->Document_No;
        endforeach;
        return $results;
    }

    /**
     * Get Flow Description
     * Query State description from DB
     *
     * @return object State description from Flow Id
     */

    function getFlowDescription( $flow_id ) {

        $this->db->select("TOP 1 STK_T_Workflow.Process_Id as Id, , SYS_M_Stateedge.Description as State_Description");
        $this->db->from("STK_T_Workflow");
        $this->db->join("SYS_M_Process", "STK_T_Workflow.Process_Id = SYS_M_Process.Process_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->where("STK_T_Workflow.Flow_Id", $flow_id);
        $query = $this->db->get();
        $response = $query->result();
        return $response;
    }

    public function getQuickPickingApprove() {
        $this->db->select("Edge_Id");
        $this->db->from("SYS_M_Stateedge");
        $this->db->where("Action_Type = 'Quick Approve' ");
        $this->db->where("Module = 'picking' ");
        $query = $this->db->get();
        $response = $query->result();
        return $response;
    }

    function clearActionLogs( $flow_id, $process_id, $present_state, $next_state ) {
        $this->db->where('Flow_Id', $flow_id);
        $query = $this->db->get('STK_T_Order');
        $order = $query->row();

        if ( $order ):
            $this->db->select('  TOP 1 STK_T_Logs_Action.Edge_Id ,Order_Id ,Activity_Date ,Process_Id ,SYS_M_Stateedge.From_State');
            $this->db->from('STK_T_Logs_Action');
            $this->db->join('SYS_M_Stateedge', 'STK_T_Logs_Action.Edge_Id = SYS_M_Stateedge.Edge_Id');
            $this->db->order_by('Activity_Date', 'DESC');
            $this->db->where('Order_Id', $order->Order_Id);
            $this->db->where('Process_Id', $process_id);
            $this->db->where('From_State', $next_state);
            $query_logs = $this->db->get();
            $result = $query_logs->row();
            if ( $result ) {
                $this->db->delete('STK_T_Logs_Action', array('Order_Id' => $order->Order_Id, 'Edge_Id' => $result->Edge_Id));
            }

        endif;

    }

    function getDNData($document_no) {
        // p($document_no);exit;

        $this->db->select("CONVERT(VARCHAR(20), r.Estimate_Action_Date, 103) AS Estimate_Action_Date");
        $this->db->select("r.Doc_Refer_Int");
        $this->db->select("r.Doc_Refer_Ext");
        $this->db->select("r.Document_No");
        $this->db->select("(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=r.Destination_Id) AS consignee");
        $this->db->select("(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=r.Source_Id) AS supplier");
        $this->db->select("d.Product_Code");
        $this->db->select("(SELECT Product_NameEN FROM STK_M_Product p WHERE p.Product_Id=d.Product_Id) AS Product_NameEN");
        $this->db->select("SUM(d.Reserv_Qty) AS Reserv_Qty , CONVERT(VARCHAR(20), r.Actual_Action_Date, 103) AS Actual_Action_Date");
        $this->db->select("SUM(d.Confirm_Qty) AS Confirm_Qty ");
        $this->db->select("d.Remark");
        $this->db->select("d.Cont_Id");
	    $this->db->select("d.Product_Lot");
        $this->db->select("d.Invoice_Id");
  //$this->db->select("CAST(CTL_M_Container.Cont_No AS VARCHAR(50)) + ' '+ CAST(CTL_M_Container_Size.Cont_Size_No AS VARCHAR(5))+ ' '+CTL_M_Container_Size.Cont_Size_Unit_Code AS Cont");
        $this->db->select("STK_T_Invoice.Invoice_No");
        $this->db->select("d.Price_Per_Unit");
        $this->db->select("domain.Dom_EN_Desc AS Unit_Price_value");
        $this->db->select("d.All_Price");
        $this->db->select("d.Pallet_Id_Out");
        $this->db->select("plt.Pallet_Code");
	    $this->db->select("unit.public_name AS Unit");

        $this->db->join("STK_T_Order_Detail d", "d.Order_Id = r.Order_Id");
        $this->db->join("SYS_M_Domain domain", "d.Unit_Price_Id = domain.Dom_Code AND domain.Dom_Active = 'Y'", "LEFT");
        $this->db->join("STK_T_Pallet plt", "d.Pallet_Id_Out = plt.Pallet_Id", "LEFT");
        $this->db->join("CTL_M_Container", "d.Cont_Id = CTL_M_Container.Cont_Id", "LEFT");
        $this->db->join("CTL_M_Container_Size", "CTL_M_Container.Cont_Size_Id = CTL_M_Container_Size.Cont_Size_Id", "LEFT");
        $this->db->join("STK_T_Invoice", "d.Invoice_Id = STK_T_Invoice.Invoice_Id", "LEFT");
	    $this->db->join("CTL_M_UOM_Template_Language unit", "unit.CTL_M_UOM_Template_id = d.Unit_Id AND unit.language = '" . $this->config->item('lang3digit') . "'", 'LEFT');

        $this->db->where("r.Process_Type", "OUTBOUND");
        $this->db->where("d.Active", "Y");
        $this->db->order_by("r.Estimate_Action_Date ASC,r.Destination_Id ASC");
        $this->db->where("r.Document_No In (". implodeArrayQuote( $document_no ) .")");
        // $this->db->group_by("CONVERT(VARCHAR(20), r.Estimate_Action_Date, 103)");
        $this->db->group_by("r.Estimate_Action_Date,r.Doc_Refer_Int,r.Doc_Refer_Ext,r.Document_No,r.Destination_Id,r.Source_Id,d.Product_Id,d.Product_Code,r.Actual_Action_Date,d.Confirm_Qty,
             d.Cont_Id,
             d.Product_Lot,
             d.Invoice_Id,STK_T_Invoice.Invoice_No,
             d.Price_Per_Unit,
             domain.Dom_EN_Desc,
             d.All_Price,
             d.Pallet_Id_Out,
             plt.Pallet_Code,
              d.Remark,
             unit.public_name");
        // $this->db->group_by("r.Doc_Refer_Ext");
        // $this->db->group_by("d.Product_Code");
        // $this->db->group_by("d.Remark");
        // $this->db->group_by("d.Product_Lot");
        // $this->db->group_by("d.All_Price");
        // $this->db->group_by("unit.public_name"); 
        // $this->db->group_by("d.Product_Id");
        // $this->db->group_by("r.Estimate_Action_Date");
        // $this->db->group_by("r.Destination_Id");
            $query = $this->db->get("STK_T_Order r");
            $result = $query->result();
    
        // echo $this->db->last_query(); exit;
    
            return $result;
       
        
    }
}
