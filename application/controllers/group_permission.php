<?php

/*
 * Create by Ton! 20140123
 * Set Group Permission Menu.
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class group_permission extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.
    public $settings;

    function __construct() {
        parent::__construct();
        $this->load->model("contact_model", "con");
        $this->load->model("user_model", "user");
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");
        $this->load->model("workflow_model", "flow");

        $this->mnu_NavigationUri = "group_permission";
        $this->settings = native_session::retrieve();
    }

    public function index() {
        $this->user_group_list();
    }

    function user_group_list() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set เนเธซเน $this->output->enable_profiler เน€เธเนเธ เธเนเธฒเธ•เธฒเธกเธ—เธตเนเน€เธเนเธ•เธกเธฒเธเธฒเธ config เน€เธเธทเนเธญเนเธเนเนเธชเธ”เธ DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        if (isset($action_parmission['action_button'])):
            $action = $action_parmission['action_button'];
        else:
            $action = array();
        endif;

        ##### END Permission Button. by Ton! 20140130 #####

        $q_user_group = $this->atn->get_ADM_M_UserGroup_List();
        $r_user_group = $q_user_group->result();

        $column = array("ID", "UserGroup Code", "UserGroup Name EN", "UserGroup TH", "UserGroup_Desc", "Active");
//        $action = array(VIEW, EDIT);// Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_user_group, $r_user_group, $column, "group_permission/group_permission_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('user_id')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Group Permission.'
            , 'datatable' => $datatable
            , 'button_add' => ''
        ));
    }

    function group_permission_processec() {
        $data = $this->input->post();

        $mode = $data['mode'];
        if ($mode == "V" || $mode == "E") :// View & Edit.
            $ID = $data['id'];
            $this->group_permission_management($mode, $ID);
        endif;
    }

    function group_permission_management($mode, $UserGroupId) {
        //p($UserGroupId);
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set เนเธซเน $this->output->enable_profiler เน€เธเนเธ เธเนเธฒเธ•เธฒเธกเธ—เธตเนเน€เธเนเธ•เธกเธฒเธเธฒเธ config เน€เธเธทเนเธญเนเธเนเนเธชเธ”เธ DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="submitForm()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $index_id = 0;
        $permission_list = array();

        $r_Parent_Menu = $this->mnu->getMenuAuthParent();
        if (count($r_Parent_Menu) > 0):
            foreach ($r_Parent_Menu as $value_Parent) :
                $r_Child_Menu = $this->mnu->getMenuAuthChild($value_Parent->MenuBar_Id);
                if (count($r_Child_Menu) > 0):
                    $permission_list[$index_id]['MenuBar_Code'] = $value_Parent->MenuBar_Code;
                    $permission_list[$index_id]['MenuBar_Id'] = $value_Parent->MenuBar_Id;
                    $permission_list[$index_id]['MenuBar_NameEn'] = str_replace(' ', '_', $value_Parent->MenuBar_NameEn);

                    $parent_index = 0;
                    foreach ($r_Child_Menu as $index_Child => $value_Child) :
                        $permission_list[$index_id]['Child_Menu'][] = (array) $value_Child;
                        if ($value_Child->Module != NULL):
                            $r_Stateedge = $this->flow->get_SYS_M_Stateedge_by_Module($value_Child->Module)->result();
                            if (count($r_Stateedge) > 0):
                                $del_job = FALSE;
                                foreach ($r_Stateedge as $index_Stateedge => $value_Stateedge) :
                                    $permission_list[$index_id]['Child_Menu'][$index_Child]['Child_Module'][] = array('Description' => $value_Stateedge->Description, 'Edge_Id' => $value_Stateedge->Edge_Id);
                                    if (($value_Stateedge->Action_Type == "Reject") || ($value_Stateedge->Action_Type == "Reject & Return") || ($value_Stateedge->Action_Type == "Delete")):
                                        $del_job = TRUE;
                                    endif;
                                endforeach;
                                if ($del_job === TRUE):
                                    $permission_list[$index_id]['Child_Menu'][$index_Child]['Child_Module'][] = array('Description' => 'Delete Jobs', 'Edge_Id' => '-4');
                                endif;
                                
                                # Module_Change_Receive_Date
                                if (isset($this->settings['can_change_receive_date'])):
                                    $chk_can_change_receive_date = (@$this->settings['can_change_receive_date']? TRUE : FALSE);
                                    if ($value_Child->Module === "receive"):
                                        if ($chk_can_change_receive_date === TRUE):
                                            $permission_list[$index_id]['Child_Menu'][$index_Child]['Child_Module'][] = array('Description' => 'Can Used Module "Change Receive Date"', 'Action_Id' => '-6');
                                        endif;
                                    endif;
                                endif;
                                
                            endif;
                            $parent_index++;
                        else :
                            $r_mnu_act = $this->atn->get_SYS_M_Action_Menu($value_Child->MenuBar_Id)->result(); //Check Action of Menu by MenuBar_Id.
                            if (count($r_mnu_act) > 0):
                                foreach ($r_mnu_act as $value_mnu_act) :
                                    if ($value_mnu_act->IsView == TRUE):
                                        $permission_list[$index_id]['Child_Menu'][$index_Child]['Child_Module'][] = array('Description' => 'VIEW', 'Action_Id' => '-1');
                                    endif;
                                    if ($value_mnu_act->IsAdd == TRUE):
                                        $permission_list[$index_id]['Child_Menu'][$index_Child]['Child_Module'][] = array('Description' => 'ADD', 'Action_Id' => '-2');
                                    endif;
                                    if ($value_mnu_act->IsEdit == TRUE):
                                        $permission_list[$index_id]['Child_Menu'][$index_Child]['Child_Module'][] = array('Description' => 'EDIT', 'Action_Id' => '-3');
                                    endif;
                                    if ($value_mnu_act->IsDelete == TRUE):
                                        $permission_list[$index_id]['Child_Menu'][$index_Child]['Child_Module'][] = array('Description' => 'DELETE', 'Action_Id' => '-4');
                                    endif;
                                endforeach;
                                $parent_index++;
                            else :
                                unset($permission_list[$index_id]['Child_Menu'][$index_Child]); // Delete child of child.
                            endif;
                        endif;
                    endforeach;

                    if ($parent_index == 0) :
                        unset($permission_list[$index_id]); // Delete Parent.
                    endif;

                else :
                endif;
                $index_id++;
            endforeach;
        endif;

        # Permission HH App. // Add by Ton! 20140317
        $r_Menu_HH = $this->mnu->getMenuAuthParent(NULL, "HH");
        if (count($r_Menu_HH) > 0):
            foreach ($r_Menu_HH as $value_Menu_HH) :
                $permission_list[$index_id]['MenuBar_Code'] = $value_Menu_HH->MenuBar_Code;
                $permission_list[$index_id]['MenuBar_Id'] = $value_Menu_HH->MenuBar_Id;
                $permission_list[$index_id]['MenuBar_NameEn'] = "Operation" . "_" . str_replace(' ', '_', $value_Menu_HH->MenuBar_NameEn);

                $permission_list[$index_id]['Child_Menu'][0]['MenuBar_Id'] = $value_Menu_HH->MenuBar_Id;
                $permission_list[$index_id]['Child_Menu'][0]['MenuBar_NameEn'] = "Menu" . "_" . str_replace(' ', '_', $value_Menu_HH->MenuBar_NameEn);
                $permission_list[$index_id]['Child_Menu'][0]['Child_Module'][] = array('Description' => 'Authorized', 'Action_Id' => '-5');

                # Module_Picking_Auto_All
                if (isset($this->settings['show_picking_all'])):
                    $chk_Picking_All = ($this->settings['show_picking_all'] == "TRUE" ? TRUE : FALSE);
                    if ($value_Menu_HH->NavigationUri === "picking/pkList" || $value_Menu_HH->NavigationUri === "picking/pkMain"):
                        if ($chk_Picking_All === TRUE):
                            $permission_list[$index_id]['Child_Menu'][1]['MenuBar_Id'] = $value_Menu_HH->MenuBar_Id;
                            $permission_list[$index_id]['Child_Menu'][1]['MenuBar_NameEn'] = "Module_Picking_Auto_All";
                            $permission_list[$index_id]['Child_Menu'][1]['Child_Module'][] = array('Description' => 'Used Module', 'Action_Id' => '-6');
                        endif;
                    endif;
                endif;

                # Module_Confirm_Qty_All
                if (isset($this->settings['fast_confirm_dispatch'])):
                    $chk_fast_confirm_dp = ($this->settings['fast_confirm_dispatch'] == "TRUE" ? TRUE : FALSE);
                    if ($value_Menu_HH->NavigationUri === "dispatch/dispatchList" || $value_Menu_HH->NavigationUri === "dispatch/dispatchMain"):
                        if ($chk_fast_confirm_dp === TRUE):
                            $permission_list[$index_id]['Child_Menu'][1]['MenuBar_Id'] = $value_Menu_HH->MenuBar_Id;
                            $permission_list[$index_id]['Child_Menu'][1]['MenuBar_NameEn'] = "Module_Confirm_Qty_All";
                            $permission_list[$index_id]['Child_Menu'][1]['Child_Module'][] = array('Description' => 'Used Module', 'Action_Id' => '-6');
                        endif;
                    endif;
                endif;

                $index_id++;
            endforeach;
        endif;

        # Group Detail
        $UserGroup_Id = '';
        $UserGroup_Code = '';
        $UserGroup_NameEN = '';
        $UserGroup_NameTH = '';
        $UserGroup_Desc = '';
        //$r_user_group = $this->atn->get_ADM_M_UserGroup(NULL, $UserGroupId, NULL, FALSE)->result(); comment by por 2014-03-08 เธชเธฅเธฑเธเธ—เธตเน id เธเธฑเธ code เนเธซเนเธ•เธฃเธเธ•เธฒเธกเธ—เธตเนเธเธณเธซเธเธ”เนเธ model
        $r_user_group = $this->atn->get_ADM_M_UserGroup($UserGroupId, NULL, NULL, FALSE)->result();
        if (count($r_user_group) > 0):
            foreach ($r_user_group as $value) :
                $UserGroup_Id = $UserGroupId;
                $UserGroup_Code = $value->UserGroup_Code;
                $UserGroup_NameEN = $value->UserGroup_NameEN;
                $UserGroup_NameTH = $value->UserGroup_NameTH;
                $UserGroup_Desc = $value->UserGroup_Desc;
            endforeach;
        endif;

        # Permission Detail
        $Permission = array();
        $r_Permission = $this->atn->get_ADM_M_Group_Permission($UserGroupId)->result();
        if (count($r_Permission) > 0):
            foreach ($r_Permission as $value) :
                if ($value->Edge_Id == 0):
                    $Permission[$value->MenuBar_Id][] = $value->Action_Id;
                else:
                    $Permission[$value->MenuBar_Id][] = $value->Edge_Id;
                endif;
            endforeach;
        endif;

        $this->load->helper('form');
        $str_form = $this->parser->parse('form/set_group_permission_menu', array("mode" => $mode, "permission_list" => $permission_list
            , "UserGroup_Id" => $UserGroup_Id, "UserGroup_Code" => $UserGroup_Code, "UserGroup_NameEN" => $UserGroup_NameEN, "UserGroup_NameTH" => $UserGroup_NameTH
            , "UserGroup_Desc" => $UserGroup_Desc, "Permission" => $Permission), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Set Group Permission Menu.'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => ''
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="submitForm()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    function save_group_permission() {// Add by Ton! 20140116
        $data = $this->input->post();

        if ($data['UserGroup_Id'] == NULL || $data['UserGroup_Id'] == ''):
            echo FALSE;
            exit();
        else:
            $this->transaction_db->transaction_start();

            $permission_where['UserGroup_Id'] = $data['UserGroup_Id'];
            $this->atn->save_ADM_M_Group_Permission('del', NULL, $permission_where); // Delete Data All by UserGroup_Id.
            if (isset($data['mnu_child_module'])):
                $saveFlag = TRUE;

                $permission_data['UserGroup_Id'] = $data['UserGroup_Id'];
                foreach ($data['mnu_child_module'] as $idx => $val) :
                    $permission_data['MenuBar_Id'] = $idx;
                    foreach ($val as $v2) :
                        $tmp = explode("|", $v2);
                        $id = $tmp['0'];
                        $action = $tmp['1'];

                        if ($action == 'W'): // by Workflow
                            $permission_data['Edge_Id'] = $id;
                            $permission_data['Action_Id'] = 0;
                        elseif ($action == 'M'): // by Management
                            $permission_data['Action_Id'] = $id;
                            $permission_data['Edge_Id'] = 0;
                        endif;

                        if ($this->atn->save_ADM_M_Group_Permission('ist', $permission_data, NULL) == FALSE):// Insert Data One by One.
                            $saveFlag = FALSE;
                        endif;
                    endforeach;
                endforeach;

                if ($saveFlag === TRUE):
                    $this->transaction_db->transaction_commit();
                    $this->cache->memcached->clean();
                else:
                    log_message('error', 'save ADM_M_Group_Permission Unsuccess.');
                    $this->transaction_db->transaction_rollback();
                endif;

                echo $saveFlag;
            else:
                echo TRUE; // Save Ok. Not Permission.
                exit();
            endif;
        endif;
    }

}
