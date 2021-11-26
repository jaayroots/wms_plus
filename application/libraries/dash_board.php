<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class dash_board {

    function __construct() {
        $CI = & get_instance();
        $CI->load->helper('util_helper');
        $this->set_memcached = FALSE;
    }

    // Add By Akkarapol, 13/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ get flow ของ process ที่เป็น Normal Flow เพื่อนำไปแสดงที่ dash board
    function get_workflow_by_module($module) {

        $CI = & get_instance();
        $CI->db->select(" STK_T_Workflow.Flow_Id as Id,SYS_M_State.Sequence,SYS_M_State.State_NameEn");
        $CI->db->from("STK_T_Workflow");
        $CI->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $CI->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $CI->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $CI->db->where("SYS_M_Stateedge.Module", $module);
        $CI->db->order_by("SYS_M_State.Sequence", "ASC");

        // Edit By Akkarapol, 21/01/2013, เนื่องจาก dashboard นั้นต้องใช้ข้อมูลแบบ realtime จึงทำให้การใช้งาน memcached ไม่สามารถนำเอามาใช้งานในส่วนนี้ได้ จึงสร้างตัวแปร $this->set_memcached มาเพื่อใช้ตรวจสอบเผื่อในกรณีที่อยากจะเปิดใช้งาน memcached ก็ยังสามารถมาปรับใช้ได้
        if ($this->set_memcached):
            $sql = $CI->db->return_query(FALSE);
            $key = md5($sql);
            $cache = $CI->cache->memcached->get($key);
            if (!$cache) :
                $query = $CI->db->query($sql);
                $response = $query->result_array();
                $CI->cache->memcached->save($key, $response, NULL, 600);
            else :
                $response = $cache;
            endif;
        else:
            $response = $CI->db->get()->result_array();
        endif;

        return $response;
        // END Edit By Akkarapol, 21/01/2013, เนื่องจาก dashboard นั้นต้องใช้ข้อมูลแบบ realtime จึงทำให้การใช้งาน memcached ไม่สามารถนำเอามาใช้งานในส่วนนี้ได้ จึงสร้างตัวแปร $this->set_memcached มาเพื่อใช้ตรวจสอบเผื่อในกรณีที่อยากจะเปิดใช้งาน memcached ก็ยังสามารถมาปรับใช้ได้
    }

    // END Add By Akkarapol, 13/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ get flow ของ process ที่เป็น Normal Flow เพื่อนำไปแสดงที่ dash board
    // Add By Akkarapol, 13/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ get flow ของ process ที่เป็น Pending Flow เพื่อนำไปแสดงที่ dash board
    function get_workflow_pending($module) {
        $CI = & get_instance();
//        $CI->load->database();

        $CI->db->select("W.Flow_Id as Id
            ,S.State_NameEn
            ,R.Doc_Relocate as Doc_Refer_Ext
            ,R.Doc_Relocate as Doc_Refer_Int
            ,W.Document_No
            ,R.Assigned_Id
            ,R.Remark
            ,R.Create_By as Create_Name 
            ,R.Assigned_Id as Assigned_Name
            ,DATEDIFF(W.Create_Date,CURDATE()) as ProcessDay");
        $CI->db->from("STK_T_Workflow W");
        $CI->db->join("STK_T_Relocate R", "W.Flow_Id = R.Flow_Id");
        $CI->db->join("SYS_M_Stateedge ST", "W.Process_Id = ST.Process_Id AND ST.From_State = W.Present_State");
        $CI->db->join("SYS_M_State S", "W.Present_State = S.State_No AND W.Process_Id = S.Process_Id");
        $CI->db->where("ST.Module", $module);

        // Edit By Akkarapol, 21/01/2013, เนื่องจาก dashboard นั้นต้องใช้ข้อมูลแบบ realtime จึงทำให้การใช้งาน memcached ไม่สามารถนำเอามาใช้งานในส่วนนี้ได้ จึงสร้างตัวแปร $this->set_memcached มาเพื่อใช้ตรวจสอบเผื่อในกรณีที่อยากจะเปิดใช้งาน memcached ก็ยังสามารถมาปรับใช้ได้
        if ($this->set_memcached):
            $sql = $CI->db->return_query(FALSE);
            $key = md5($sql);
            $cache = $CI->cache->memcached->get($key);
            if (!$cache) :
                $query = $CI->db->query($sql);
                $response = $query->result_array();
                $CI->cache->memcached->save($key, $response, NULL, 600);
            else :
                $response = $cache;
            endif;
        else:
            $response = $CI->db->get()->result_array();
        endif;

        return $response;
        // END Edit By Akkarapol, 21/01/2013, เนื่องจาก dashboard นั้นต้องใช้ข้อมูลแบบ realtime จึงทำให้การใช้งาน memcached ไม่สามารถนำเอามาใช้งานในส่วนนี้ได้ จึงสร้างตัวแปร $this->set_memcached มาเพื่อใช้ตรวจสอบเผื่อในกรณีที่อยากจะเปิดใช้งาน memcached ก็ยังสามารถมาปรับใช้ได้
    }

    // END Add By Akkarapol, 13/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ get flow ของ process ที่เป็น Pending Flow เพื่อนำไปแสดงที่ dash board
    // Add By Akkarapol, 13/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ get flow ของ process ที่เป็น Adjust Flow เพื่อนำไปแสดงที่ dash board
    function get_workflow_adjust($module) {
        $CI = & get_instance();
//        $CI->load->database();
// , DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay");
// ,CONVERT(VARCHAR(10),STK_T_Order.Estimate_Action_Date,103) AS Estimate_Action_Date
        $CI->db->select(" STK_T_Workflow.flow_Id as Id,SYS_M_State.State_NameEn,STK_T_Order.Document_No
            ,STK_T_Order.Document_No
            ,STK_T_Order.Estimate_Action_Date AS Estimate_Action_Date
            ,STK_T_Order.Remark
            ,STK_T_Workflow.Document_No
            ,STK_T_Workflow.Create_Date as ProcessDay");
        $CI->db->from("STK_T_Workflow");
        $CI->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $CI->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $CI->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $CI->db->where("SYS_M_Stateedge.module", $module);

        // Edit By Akkarapol, 21/01/2013, เนื่องจาก dashboard นั้นต้องใช้ข้อมูลแบบ realtime จึงทำให้การใช้งาน memcached ไม่สามารถนำเอามาใช้งานในส่วนนี้ได้ จึงสร้างตัวแปร $this->set_memcached มาเพื่อใช้ตรวจสอบเผื่อในกรณีที่อยากจะเปิดใช้งาน memcached ก็ยังสามารถมาปรับใช้ได้
        if ($this->set_memcached):
            $sql = $CI->db->return_query(FALSE);
            $key = md5($sql);
            $cache = $CI->cache->memcached->get($key);
            if (!$cache) :
                $query = $CI->db->query($sql);
                $response = $query->result_array();
                $CI->cache->memcached->save($key, $response, NULL, 600);
            else :
                $response = $cache;
            endif;
        else:
            $response = $CI->db->get()->result_array();
        endif;

        return $response;
        // END Edit By Akkarapol, 21/01/2013, เนื่องจาก dashboard นั้นต้องใช้ข้อมูลแบบ realtime จึงทำให้การใช้งาน memcached ไม่สามารถนำเอามาใช้งานในส่วนนี้ได้ จึงสร้างตัวแปร $this->set_memcached มาเพื่อใช้ตรวจสอบเผื่อในกรณีที่อยากจะเปิดใช้งาน memcached ก็ยังสามารถมาปรับใช้ได้
    }

    // END Add By Akkarapol, 13/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ get flow ของ process ที่เป็น Adjust Flow เพื่อนำไปแสดงที่ dash board
    // Add By Akkarapol, 13/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ get flow ของ process ที่เป็น Change Status Flow เพื่อนำไปแสดงที่ dash board
    function get_workflow_change_status($module) {
        $CI = & get_instance();
//        $CI->load->database();

$CI->db->select("W.Flow_Id as Id
,S.State_NameEn
,R.Doc_Relocate as Doc_Refer_Ext
,R.Doc_Relocate as Doc_Refer_Int
,W.Document_No
,R.Assigned_Id
,R.Remark
,(C1.First_NameTH+' '+C1.Last_NameTH) as Create_Name
,(C2.First_NameTH+' '+C2.Last_NameTH) as Assigned_Name
, DATEDIFF(W.Create_Date,CURDATE()) as ProcessDay");
$CI->db->from("STK_T_Workflow W");
$CI->db->join("STK_T_Relocate R", "W.Flow_Id = R.Flow_Id");
$CI->db->join("SYS_M_Stateedge ST", "W.Process_Id = ST.Process_Id AND ST.From_State = W.Present_State");
$CI->db->join("SYS_M_State S", "W.Present_State = S.State_No AND W.Process_Id = S.Process_Id");
$CI->db->join("ADM_M_UserLogin U1", "R.Create_By = U1.UserLogin_Id");
$CI->db->join("ADM_M_UserLogin U2", "R.Assigned_Id = U2.UserLogin_Id", "LEFT");
$CI->db->join("CTL_M_Contact C1", "U1.Contact_Id = C1.Contact_Id");
$CI->db->join("CTL_M_Contact C2", "U2.Contact_Id = C2.Contact_Id", "LEFT");
$CI->db->where("ST.Module", $module);
$CI->db->where("ST.Active", ACTIVE);

        // Edit By Akkarapol, 21/01/2013, เนื่องจาก dashboard นั้นต้องใช้ข้อมูลแบบ realtime จึงทำให้การใช้งาน memcached ไม่สามารถนำเอามาใช้งานในส่วนนี้ได้ จึงสร้างตัวแปร $this->set_memcached มาเพื่อใช้ตรวจสอบเผื่อในกรณีที่อยากจะเปิดใช้งาน memcached ก็ยังสามารถมาปรับใช้ได้
        if ($this->set_memcached):
            $sql = $CI->db->return_query(FALSE);
            $key = md5($sql);
            $cache = $CI->cache->memcached->get($key);
            if (!$cache) :
                $query = $CI->db->query($sql);
                $response = $query->result_array();
                $CI->cache->memcached->save($key, $response, NULL, 600);
            else :
                $response = $cache;
            endif;
        else:
            $response = $CI->db->get()->result_array();
        endif;

        return $response;
        // END Edit By Akkarapol, 21/01/2013, เนื่องจาก dashboard นั้นต้องใช้ข้อมูลแบบ realtime จึงทำให้การใช้งาน memcached ไม่สามารถนำเอามาใช้งานในส่วนนี้ได้ จึงสร้างตัวแปร $this->set_memcached มาเพื่อใช้ตรวจสอบเผื่อในกรณีที่อยากจะเปิดใช้งาน memcached ก็ยังสามารถมาปรับใช้ได้
    }

    // END Add By Akkarapol, 13/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ get flow ของ process ที่เป็น Change Status Flow เพื่อนำไปแสดงที่ dash board
    // Add By Akkarapol, 13/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ get flow ของ process ที่เป็น Re Location Flow เพื่อนำไปแสดงที่ dash board
    function get_workflow_re_location($module) {
        $CI = & get_instance();
        // , DATEDIFF(day,STK_T_Workflow.Create_Date,GETDATE()) as ProcessDay");
        $CI->db->select(" STK_T_Workflow.Flow_Id as Id
            , SYS_M_State.State_NameEn
            , STK_T_Relocate.Doc_Relocate
             ,STK_T_Relocate.Doc_Relocate as Doc_Refer_Int
            , STK_T_Workflow.Document_No
            ,STK_T_Workflow.Create_Date as ProcessDay");
        $CI->db->from("STK_T_Workflow");
        $CI->db->join("STK_T_Relocate", "STK_T_Workflow.Flow_Id = STK_T_Relocate.Flow_Id");
        $CI->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $CI->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $CI->db->where("SYS_M_Stateedge.Module", $module);

        // Edit By Akkarapol, 21/01/2013, เนื่องจาก dashboard นั้นต้องใช้ข้อมูลแบบ realtime จึงทำให้การใช้งาน memcached ไม่สามารถนำเอามาใช้งานในส่วนนี้ได้ จึงสร้างตัวแปร $this->set_memcached มาเพื่อใช้ตรวจสอบเผื่อในกรณีที่อยากจะเปิดใช้งาน memcached ก็ยังสามารถมาปรับใช้ได้
        if ($this->set_memcached):
            $sql = $CI->db->return_query(FALSE);
            $key = md5($sql);
            $cache = $CI->cache->memcached->get($key);
            if (!$cache) :
                $query = $CI->db->query($sql);
                $response = $query->result_array();
                $CI->cache->memcached->save($key, $response, NULL, 600);
            else :
                $response = $cache;
            endif;
        else:
            $response = $CI->db->get()->result_array();
        endif;

        return $response;
        // END Edit By Akkarapol, 21/01/2013, เนื่องจาก dashboard นั้นต้องใช้ข้อมูลแบบ realtime จึงทำให้การใช้งาน memcached ไม่สามารถนำเอามาใช้งานในส่วนนี้ได้ จึงสร้างตัวแปร $this->set_memcached มาเพื่อใช้ตรวจสอบเผื่อในกรณีที่อยากจะเปิดใช้งาน memcached ก็ยังสามารถมาปรับใช้ได้
    }

    // END Add By Akkarapol, 13/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ get flow ของ process ที่เป็น Re Location Flow เพื่อนำไปแสดงที่ dash board
// Add By Akkarapol, 18/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ get flow ของ process ที่เป็น Counting Daily เพื่อนำไปแสดงที่ dash board
    public function get_workflow_counting_daily() {
        $CI = & get_instance();

        $userLogin = $CI->session->userdata("user_id");
        $module = "counting";
        $workflow_id = 3;
        $CI->load->model("counting_model", "ctm");
        $CI->load->model("workflow_model", "flow");

        $chkIsWarehouseAdmin = $CI->ctm->checkIsWarehouseAdmin($userLogin = 0);
        $isWarehouseAdmin = count($chkIsWarehouseAdmin);
        $query = $CI->flow->getWorkFlowByCounting($workflow_id, $isWarehouseAdmin)->result_array();
        return $query;
    }

    // END Add By Akkarapol, 18/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ get flow ของ process ที่เป็น Counting Daily เพื่อนำไปแสดงที่ dash board
// Add By Akkarapol, 18/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ get flow ของ process ที่เป็น Counting Criteria เพื่อนำไปแสดงที่ dash board
    public function get_workflow_counting_criteria() {
        $CI = & get_instance();

        $userLogin = $CI->session->userdata("user_id");
        $CI->load->model("counting_model", "ctm");
        $CI->load->model("workflow_model", "flow");

        if (!isset($userLogin)) :
            $chkIsWarehouseAdmin = $CI->ctm->checkIsWarehouseAdmin($userLogin = 0);
        else :
            $chkIsWarehouseAdmin = $CI->ctm->checkIsWarehouseAdmin($userLogin);
        endif;
        $module = "countingCriteria";
        $workflow_id = 4;
        $isWarehouseAdmin = count($chkIsWarehouseAdmin);
        $query = $CI->flow->getWorkFlowByCounting($workflow_id, $isWarehouseAdmin)->result_array();

        return $query;
    }

    // END Add By Akkarapol, 18/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ get flow ของ process ที่เป็น Counting Criteria เพื่อนำไปแสดงที่ dash board

    function dash_board() {
        $CI->db->select('Process_Id, Process_NameEn');
        $CI->db->from("SYS_M_Process");
        $query = $CI->db->get();
        $process = $query->result_array();
        foreach ($process as $key_proc => $proc):
            $CI->db->select('SYS_M_Stateedge.Module');
            $CI->db->from("STK_T_Workflow");
            $CI->db->join('SYS_M_Stateedge', "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND STK_T_Workflow.Present_State = SYS_M_Stateedge.From_State AND SYS_M_Stateedge.To_State > 0 AND SYS_M_Stateedge.Action_Type != 'Quick Approve' AND SYS_M_Stateedge.Action_Type != 'Reject' AND SYS_M_Stateedge.Action_Type != 'Reject and Return'", 'INNER');
            $CI->db->where(array('STK_T_Workflow.Process_Id' => $proc['Process_Id'], 'STK_T_Workflow.Present_State >' => 0));
            $CI->db->group_by('SYS_M_Stateedge.Module');
            $CI->db->having('COUNT(SYS_M_Stateedge.Module) > 0');
            $query_modules = $CI->db->get()->result_array();
            $sum_count_module = 0;
            foreach ($query_modules as $key_module => $module):
                $module_name = $module['Module'];

                $CI->db->select('SYS_M_State.State_NameEn, COUNT(SYS_M_State.State_NameEn) AS count_state');
                $CI->db->from("STK_T_Workflow");
                $CI->db->join('SYS_M_Stateedge', "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND STK_T_Workflow.Present_State = SYS_M_Stateedge.From_State AND SYS_M_Stateedge.Action_Type != 'Quick Approve' AND SYS_M_Stateedge.Action_Type != 'Reject' AND SYS_M_Stateedge.Action_Type != 'Reject and Return' AND SYS_M_Stateedge.Module = '" . $module_name . "' ", 'INNER');
                $CI->db->join('SYS_M_State', 'STK_T_Workflow.Process_Id = SYS_M_State.Process_Id AND STK_T_Workflow.Present_State = SYS_M_State.State_No ', 'INNER');
                $CI->db->where(array('STK_T_Workflow.Process_Id' => $proc['Process_Id'], 'STK_T_Workflow.Present_State >' => 0));
                $CI->db->group_by('SYS_M_State.State_NameEn');
                $CI->db->having('COUNT(SYS_M_State.State_NameEn) > 0');
                $query_state = $CI->db->get()->result_array();

                unset($arr_state);
                $sum_count_state = 0;
                foreach ($query_state as $key_state => $state):
                    $state_name = $state['State_NameEn'];
                    $arr_state[$state_name] = (!empty($state['count_state']) ? $state['count_state'] : 0);

                    $sum_count_module = $sum_count_module + $state['count_state'];
                    $sum_count_state = $sum_count_state + (!empty($state['count_state']) ? $state['count_state'] : 0);
                endforeach;

                if (!empty($arr_state)):
                    $process[$key_proc]['stateedge'][$module_name]['count'] = $sum_count_state;
                    $process[$key_proc]['stateedge'][$module_name]['state'] = $arr_state;
                endif;

            endforeach;
            $process[$key_proc]['count'] = $sum_count_module;
        endforeach;

        $dash_boards['process'] = $process;

        return $dash_boards;
    }

    function get_workflow_re_location_in_HH($Present_State = "") {// Edit by Ton! 20140508
        $CI = & get_instance();
        $CI->db->select(" STK_T_Workflow.Flow_Id, STK_T_Relocate_Detail.Item_Id, STK_T_Relocate_Detail.Product_Code
            , STK_T_Relocate_Detail.Inbound_Item_Id, STK_T_Relocate_Detail.Actual_Location_Id, STK_M_Location.Location_Code");
        $CI->db->from("STK_T_Workflow");
        $CI->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id 
            AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $CI->db->join("STK_T_Relocate", "STK_T_Workflow.flow_Id = STK_T_Relocate.Flow_Id");
        $CI->db->join("STK_T_Relocate_Detail", "STK_T_Relocate_Detail.Order_Id = STK_T_Relocate.Order_Id");
        $CI->db->join("STK_M_Location", "STK_T_Relocate_Detail.Old_Location_Id = STK_M_Location.Location_Id ");
        $CI->db->where("SYS_M_Stateedge.module", "relocate");
        $CI->db->where("STK_T_Workflow.Process_Id", 6);
//        $CI->db->where("STK_T_Workflow.Present_State", 1);
        if ($Present_State !== ""):// Add by Ton! 20140508
            $CI->db->where("STK_T_Workflow.Present_State", $Present_State);
        endif;
        $CI->db->where("SYS_M_Stateedge.Active", "Y");
        $CI->db->where("STK_T_Relocate_Detail.Actual_Location_Id IS NULL");
        $CI->db->where_not_in('STK_T_Workflow.Present_State', "-1");
        $CI->db->order_by("STK_T_Workflow.Flow_Id");
        $query = $CI->db->get();
//        echo $CI->db->last_query();exit();
        return $query->result();
    }

}
