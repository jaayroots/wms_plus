<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Flow extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public $column = array("Flow ID", "State Name", "External Document", "Internal Document", "Document No.", "Process Day"); // Add By Akkarapol, 09/12/2013, ตั้งค่าตัวแปร $column ไว้เป็นแบบ Public เพื่อว่า function ใดก็ตามเรียกใช้ไป ก็จะง่ายต่อการเปลี่ยนแปลงค่าใน ตัวแปรนี้

    /*
     * Comment Out by Ton! 20140131 Not Used.
     *
     *  public function index() {
      $this->flowList();
      }

      function flowList() {
      $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

      $this->load->model("workflow_model", "flow");
      $query = $this->flow->getWorkFlowAll();
      $user_list = $query->result();
      $column = array("Flow ID", "State Name", "Reference External", "Reference Internal", "Document No.");
      $VIEW = array(VIEW);
      $datatable = $this->datatable->genTableFixColumn($query, $user_list, $column, "flow/flowForm", $VIEW);
      $this->parser->parse('list_template', array(
      //'menu' => $this->menu->loadMenuAuth()
      'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
      , 'menu_title' => 'List of Document'
      , 'datatable' => $datatable
      , 'button_add' => ""
      ));
      }
     *
     */

    function flowPreReceiveList() {  #Start Process of Inbound
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140131 #####
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), "flow/flowPreReceiveList");
        if (!empty($action_parmission)):
            $VIEW = array(VIEW, DEL);
        else:
            $VIEW = array();
        endif;

        // Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย
        if (($key = array_search(DEL, $VIEW)) !== false) {
            unset($VIEW[$key]);
        }
        // END Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย

        $module = "pre_receive";
        $this->load->model("workflow_model", "flow");
        $query = $this->flow->getWorkFlowByModule($module);
        $receive_list = $query->result();
        $column = $this->column; // Add By Akkarapol, 09/12/2013, เรียกใช้งาน column name จาก $this->column โดยเซ็ตค่าไว้เป็นแบบ Public เพื่อให้ง่ายต่อการเปลี่ยนแปลงค่าต่างๆของ column name
        $datatable = $this->datatable->genTableFixColumn($query, $receive_list, $column, "pre_receive/openActionForm", $VIEW, "pre_receive/rejectAction", register_token(0, 0, 0)); // add parameter "pre_receive/rejectAndReturnAction": by kik : 2013-12-02"";
        $this->parser->parse('list_template', array(
            'menu' => $this->menu_auth->loadMenuAuth()
            , 'menu_title' => 'List of Pre-Receive Document'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' style='margin:0 0 0 0;' VALUE='" . ADD . "'ONCLICK=\"openForm('form_name','pre_receive','A','')\">"
        ));
    }

    function flowReceiveList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140131 #####
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), "flow/flowReceiveList");
        if (!empty($action_parmission)):
            $VIEW = array(VIEW, DEL);
        else:
            $VIEW = array();
        endif;

        // Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย
        if (($key = array_search(DEL, $VIEW)) !== false) {
            unset($VIEW[$key]);
        }
        // END Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย
        // clean session first
        $this->session->unset_userdata('receive_id');
        // end

        $module = "receive";
        $this->load->model("workflow_model", "flow");
        $query = $this->flow->getWorkFlowByModule($module);
        $receive_list = $query->result();

//        $column = array("Flow ID", "State Name", "Reference External", "Reference Internal", "Document No.", "Process Day"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow
        $column = $this->column; // Add By Akkarapol, 09/12/2013, เรียกใช้งาน column name จาก $this->column โดยเซ็ตค่าไว้เป็นแบบ Public เพื่อให้ง่ายต่อการเปลี่ยนแปลงค่าต่างๆของ column name
//        $VIEW = array(VIEW, DEL);    // add parameter DEL : by kik : 2013-12-02
        $datatable = $this->datatable->genTableFixColumn($query, $receive_list, $column, "receive/openActionForm", $VIEW, "receive/rejectAction");
        $module = "";
        $sub_module = "";
        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Receive Document'
            , 'datatable' => $datatable
            , 'button_add' => ""
        ));
    }

//    Add By Akkarapol, 02/09/2013, เพิ่มฟังก์ชั่น flowPartialReceiveList สำหรับใช้กับ Partial Receive
    function flowPartialReceiveList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140131 #####
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), "flow/flowPartialReceiveList");
        if (!empty($action_parmission)):
            $action = array(VIEW);
        else:
            $action = array();
        endif;

        redirect(site_url() . '/partial_receive/', 'refresh'); // Add By Akkarapol, 03/09/2013, เพิ่ม redirect ให้ไปที่หน้า list ของ partial receive

        $module = "partial_receive";
        $this->load->model("workflow_model", "flow");
        $query = $this->flow->getWorkFlowPartialReceive($module);
        $result = $query->result();

        $column = array("Flow ID", "State Name", "Reference External", "Reference Internal", "Document No.");
//        $action = array(VIEW);

        $action_module = "partial_receive/openActionForm";
        $datatable = $this->datatable->genTableFixColumn($query, $result, $column, $action_module, $action);

        $module = "";
        $sub_module = "";
        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Partial Receive Document'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' style='margin:0 0 0 0;' VALUE='" . ADD . "'
			 ONCLICK=\"openForm('partial_receive_form','partial_receive/','A','')\">"
        ));
    }

//    END Add By Akkarapol, 02/09/2013, เพิ่มฟังก์ชั่น flowPartialReceiveList สำหรับใช้กับ Partial Receive

    function flowPutawayList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140131 #####
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), "flow/flowPutawayList");
        if (!empty($action_parmission)):
            $VIEW = array(VIEW);
        else:
            $VIEW = array();
        endif;

        $module = "putaway";
        $this->load->model("workflow_model", "flow");
        $query = $this->flow->getWorkFlowByModule($module);
        $receive_list = $query->result();

//        $column = array("Flow ID", "State Name", "Reference External", "Reference Internal", "Document No.", "Process Day"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow
        $column = $this->column; // Add By Akkarapol, 09/12/2013, เรียกใช้งาน column name จาก $this->column โดยเซ็ตค่าไว้เป็นแบบ Public เพื่อให้ง่ายต่อการเปลี่ยนแปลงค่าต่างๆของ column name
//        $VIEW = array(VIEW);
        $datatable = $this->datatable->genTableFixColumn($query, $receive_list, $column, "putaway/openActionForm", $VIEW);
        $module = "";
        $sub_module = "";
        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Putaway Document'
            , 'datatable' => $datatable
            , 'button_add' => ""
        ));
    }

    function flowPreDispatchList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140131 #####
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), "flow/flowPreDispatchList");
        if (!empty($action_parmission)):
            $VIEW = array(VIEW, DEL);
        else:
            $VIEW = array();
        endif;

        // Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย
        if (($key = array_search(DEL, $VIEW)) !== false) {
            unset($VIEW[$key]);
        }
        // END Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย


        $module = "pre_dispatch";
        $this->load->model("workflow_model", "flow");
        $query = $this->flow->getWorkFlowByModuleV2($module);
        $receive_list = $query->result();
        $column = array("Flow ID", "State Name", "Reference Internal", "Reference External", "Document No.", "Process Day"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow
//        $VIEW = array(VIEW, DEL);    //add parameter DEL : by kik : 2013-12-13
        $datatable = $this->datatable->genTableFixColumn($query, $receive_list, $column, "pre_dispatch/preDispatchFormWithData", $VIEW, "pre_dispatch/rejectAction"); //add parameter "pre_dispatch/rejectAction" : by kik : 2013-12-13
        $module = "";
        $sub_module = "";
        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Pre-Dispatch Document'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' style='margin:0 0 0 0;' VALUE='" . ADD . "'
			 ONCLICK=\"openForm('form_name','pre_dispatch/preDispatchForm/','A','')\">"
        ));
    }

    function flowPickingList() {

        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        $_xml = $this->config->item("_xml");
        ##### Permission Button. by Ton! 20140131 #####
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), "flow/flowPickingList");
        if (!empty($action_parmission)):
            $VIEW = array(VIEW);
        else:
            $VIEW = array();
        endif;

        $module = "picking";
        $parameter['quick_picking_approve'] = FALSE;
        $parameter['consolidate_picking'] = FALSE;
        $parameter['delivery_note'] = FALSE;
        
      
        if (isset($_xml['consolidate_picking']) && $_xml['consolidate_picking'] == TRUE):
            
            $parameter['consolidate_picking'] = TRUE;
        endif;

        if (isset($_xml['delivery_note']) && $_xml['delivery_note'] == TRUE):
               
                $parameter['delivery_note'] = TRUE;
        endif;
  

        $this->load->model("workflow_model", "flow");
        $query = $this->flow->getWorkFlowByModuleV2($module);
        $receive_list = $query->result();

        $quick_picking = $this->flow->getQuickPickingApprove();

        # Query group list of this user login
        $get_ADM_R_UserGroupMembers = $this->auth->get_ADM_R_UserGroupMembers("ADM_R_UserGroupMembers.UserGroup_Id", NULL, NULL, $this->session->userdata("user_id"), TRUE)->result();
        $group_list = array();
        foreach ($get_ADM_R_UserGroupMembers as $key_group_id => $group_id):
            $group_list[] = $group_id->UserGroup_Id;
        endforeach;
        $group_list = implode(',', $group_list);

        # Query role list of this user login
        $get_ADM_R_UserRoleMembers = $this->auth->get_ADM_R_UserRoleMembers("ADM_R_UserRoleMembers.UserRole_Id", NULL, NULL, $this->session->userdata("user_id"), TRUE)->result();
        $role_list = array();
        foreach ($get_ADM_R_UserRoleMembers as $key_role_id => $role_id):
            $role_list[] = $role_id->UserRole_Id;
        endforeach;
        $role_list = implode(',', $role_list);

        #Check Permision Quick Approve Picking on listPage
        if (@$_xml['quick_picking_approve'] && $this->uri->uri_string == "flow/flowPickingList"):
            $permission_quick_approve_on_list_page = FALSE;
            $NavigationUri = array('flow/flowPickingList');
            $check_user_permission = $this->auth->get_ADM_M_User_Permission($this->session->userdata("user_id"), NULL, NULL, NULL, $NavigationUri)->result();
            $check_group_permission = $this->auth->get_ADM_M_Group_Permission($group_list, NULL, NULL, NULL, $NavigationUri)->result();
            $check_role_permission = $this->auth->get_ADM_M_Role_Permission($role_list, NULL, NULL, NULL, $NavigationUri)->result();

            if (isset($quick_picking['0'])) {
                $check_quick_picking = $quick_picking['0']->Edge_Id;
            } else {
                $check_quick_picking = NULL;
            }

            if (!empty($check_user_permission) || $check_user_permission !== FALSE):
                foreach ($check_user_permission as $value):
                    if ($value->Edge_Id == $check_quick_picking) {
                        if ($value->Action_Id == "0") {
                            $permission_quick_approve_on_list_page = TRUE;
                        }
                    }
                endforeach;
            endif;

            if ($check_role_permission !== FALSE):
                if (!empty($check_role_permission)):
                    foreach ($check_role_permission as $value):
                        if ($value->Edge_Id == $check_quick_picking) {
                            if ($value->Action_Id == "0") {
                                $permission_quick_approve_on_list_page = TRUE;
                            }
                        }
                    endforeach;
                endif;
            endif;

            if ($check_group_permission !== FALSE):
                if (!empty($check_group_permission)):
                    foreach ($check_group_permission as $value):
                        if ($value->Edge_Id == $check_quick_picking) {
                            if ($value->Action_Id == "0") {
                                $permission_quick_approve_on_list_page = TRUE;
                            }
                        }
                    endforeach;
                endif;
            endif;

            $parameter['quick_picking_approve'] = FALSE;

            if (@$_xml['quick_picking_approve'] && $permission_quick_approve_on_list_page):
                $parameter['quick_picking_approve'] = TRUE;
            endif;

        endif;
        # END Check Permision Quick Approve Picking on listPage

        $column = array("Flow ID", "State Name", "Reference Internal", "Reference External", "Document No.", "Process Day"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow
        // $VIEW = array(VIEW);
        // If enable quick approve dispatch add new column for checkbox
        $gen_other_column = NULL;
        if ($parameter['quick_picking_approve'] || $parameter['consolidate_picking'] || $parameter['delivery_note']   ) :
            $column = array_merge(array("<input type=\"checkbox\" name=\"check_all\" class=\"checkbox_all\">"), $column);
            $gen_other_column = 'checkbox_picking';
        endif;
        // end

        $datatable = $this->datatable->genTableFixColumn($query, $receive_list, $column, "picking/openActionForm", $VIEW, "", $gen_other_column);
       
        $module = "";
        $sub_module = "";
        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Picking Document'
            , 'datatable' => $datatable
            , 'button_add' => ""
            , 'quick_picking_approve' => $parameter['quick_picking_approve']
            , 'consolidate_picking' => $parameter['consolidate_picking']
            , 'delivery_note' => $parameter['delivery_note']
            
        ));
    }

    function flowDispatchList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140131 #####
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), "flow/flowDispatchList");
        $_xml = $this->config->item("_xml");
        if (!empty($action_parmission)):
            $VIEW = array(VIEW);
        else:
            $VIEW = array();
        endif;

        $module = "dispatch";
        $this->load->model("workflow_model", "flow");
        $query = $this->flow->getWorkFlowByModuleV2($module);

        $receive_list = $query->result();

        $column = array("Flow ID", "State Name", "Reference Internal", "Reference External", "Document No.", "Process Day"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow
        // If enable quick approve dispatch add new column for checkbox
        if (@$_xml['quick_dispatch_approve'] == TRUE || @$_xml['consolidate_dispatch'] == TRUE) :
            $column = array_merge(array("<input type=\"checkbox\" name=\"check_all\" class=\"checkbox_all\">"), $column);
        endif;
        // end
//        $VIEW = array(VIEW);
        $datatable = $this->datatable->genTableFixColumn($query, $receive_list, $column, "dispatch/openActionForm", $VIEW);
        $module = "";
        $sub_module = "";
        $this->parser->parse('list_template', array(
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Dispatch Document'
            , 'datatable' => $datatable
            , 'button_add' => ""
            , "_xml" => $_xml
        ));
    }

    function flowPendingList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140131 #####
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), "flow/flowPendingList");
        if (!empty($action_parmission)):
            $action = array(VIEW);
        else:
            $action = array();
        endif;

        $module = "pending";
        $this->load->model("workflow_model", "flow");
        $query = $this->flow->getWorkFlowPending($module);
        $result = $query->result();
        $column = array("Flow ID", "State Name", "Document No.", "Create By", "Assign To", "Remark", "Process Day"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow);
//        $action = array(VIEW);
        $action_module = "pending/openActionForm";
        $data_list = array();
        if (is_array($result) && count($result)) {
            $count = 1;
            foreach ($result as $rows) {
                $data['Id'] = $rows->Id;
                $data['State_Name'] = $rows->State_NameEn;
                $data['Document_No'] = $rows->Document_No;
                $data['Create_Name'] = $rows->Create_Name;
                $data['Assigned_Name'] = $rows->Assigned_Name;
                $data['Remark'] = $rows->Remark;
                $data['ProcessDay'] = $rows->ProcessDay; // Add By Akkarapol, 16/09/2013, เพิ่ม "ProcessDay" เข้าไปเพื่อนำไปแสดงที่หน้า flow);
                $data['Is_urgent'] = $rows->Is_urgent;
                $count++;
                $data_list[] = (object) $data;
            }
        }
        $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);
        $module = "";
        $sub_module = "";
        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Pending Document'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' style='margin:0 0 0 0;' VALUE='" . ADD . "'
			 ONCLICK=\"openForm('pending_form','pending/','A','')\">"
        ));
    }

    function flowRelocateList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140131 #####
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), "reLocation");
        if (!empty($action_parmission)):
            $action = array(VIEW);
        else:
            $action = array();
        endif;

        $module = "relocate";
        $this->load->model("workflow_model", "flow");
        $query = $this->flow->getWorkFlowByModule($module);
        $receive_list = $query->result();

        $column = array("Flow ID", "State Name", "Reference External", "Reference Internal", "Document No.");
//        $action = array(VIEW);
        $datatable = $this->datatable->genTableFixColumn($query, $receive_list, $column, "reLocation/openActionForm", $action);
        $module = "";
        $sub_module = "";
        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Pending Document'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
			 ONCLICK=\"openForm('pending_form','relocate/','A','')\">"
        ));
    }

    function flowChangeStatusList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140131 #####
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), "flow/flowChangeStatusList");
        if (!empty($action_parmission)):
            $action = array(VIEW, DEL);
        else:
            $action = array();
        endif;

        // Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย
        if (($key = array_search(DEL, $action)) !== false) {
            unset($action[$key]);
        }
        // END Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย


        $module = "product_status";
        $this->load->model("workflow_model", "flow");
//        $query = $this->flow->getWorkFlowPending($module); // Comment By Akkarapol, 01/10/2013, คอมเม้นต์ไปเพราะอันนี้เป็นส่วนของการ change status ไม่ใช่ pending ซึ่งการ query ข้อมูลจะคนละแบบกันจะใช้ด้วยกันไม่ได้
        $query = $this->flow->getWorkFlowChangeStatus($module); // Add By Akkarapol, 01/10/2013, เรียกใช้ฟังก์ชั่น query ข้อมูลในส่วนของการ change status เท่านั้นขึ้นมาแสดง
        $result = $query->result();
        $column = array("Flow ID", "State Name", "Document No.", "Create By", "Assign To", "Remark", "Process Day"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow);
//        $action = array(VIEW, DEL);
        $action_module = "product_status/openActionForm";
        $data_list = array();
        if (is_array($result) && count($result)) {
            $count = 1;
            foreach ($result as $rows) {
                $data['Id'] = $rows->Id;
                $data['State_Name'] = $rows->State_NameEn;
                $data['Document_No'] = $rows->Document_No;
                $data['Create_Name'] = $rows->Create_Name;
                $data['Assigned_Name'] = $rows->Assigned_Name;
                $data['Remark'] = $rows->Remark;
                $data['ProcessDay'] = $rows->ProcessDay; // Add By Akkarapol, 16/09/2013, เพิ่ม "ProcessDay" เข้าไปเพื่อนำไปแสดงที่หน้า flow);
                $data['Is_urgent'] = $rows->Is_urgent;
                $count++;
                $data_list[] = (object) $data;
            }
        }

        $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action, "product_status/rejectAction");
        $module = "";
        $sub_module = "";
        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Change Status Document'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
			 ONCLICK=\"openForm('my_form','product_status/','A','')\">"
        ));
    }

    function flowRepackageList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140131 #####
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), "flow/flowRepackageList");
        if (!empty($action_parmission)):
            $action = array(VIEW);
        else:
            $action = array();
        endif;

        $module = "repackage";
        $this->load->model("workflow_model", "flow");
        $query = $this->flow->getWorkFlowRepackage($module);
        $result = $query->result();
        $column = array("Flow ID", "State Name", "Document No.", "Create By", "Assign To", "Remark", "Process Day"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow);
//        $action = array(VIEW);
        $action_module = "repackage/openActionForm";
        $data_list = array();
        if (is_array($result) && count($result)) {
            $count = 1;
            foreach ($result as $rows) {
                $warning_msg = "";
                if (($rows->Diff_Date >= REPAKAGE_DAY)) {
                    $warning_msg = "<font color='red'>(Over " . REPAKAGE_DAY . " Days)</font>&nbsp;&nbsp;";
                }
                $data['Id'] = $rows->Id;
                $data['State_Name'] = $rows->State_NameEn;
                $data['Document_No'] = $rows->Document_No;
                $data['Create_Name'] = $rows->Create_Name;
                $data['Assigned_Name'] = $rows->Assigned_Name;
                $data['Remark'] = $rows->Remark . $warning_msg;
                $data['ProcessDay'] = $rows->ProcessDay; // Add By Akkarapol, 16/09/2013, เพิ่ม "ProcessDay" เข้าไปเพื่อนำไปแสดงที่หน้า flow);
                $count++;
                $data_list[] = (object) $data;
            }
        }
        $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);
        $module = "";
        $sub_module = "";
        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Re-Package Document'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' style='margin:0 0 0 0;' VALUE='" . ADD . "'
			 ONCLICK=\"openForm('repackage_form','repackage/','A','')\">"
        ));
    }

}
