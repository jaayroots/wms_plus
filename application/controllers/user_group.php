<?php

/*
 * Create by Ton! 20131115
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class user_group extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    public function __construct() {
        parent::__construct();
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");

        $this->mnu_NavigationUri = "user_group";
    }

    public function index() {
        $this->get_user_group_list();
    }

    function get_user_group_list() {// Display List of User Group.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        $action = $action_parmission['action_button'];

        $button_add = "";
        if (in_array("-2", $action_parmission)):
            $button_add = "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
             ONCLICK=\"openForm('user_group','user_group/user_group_processec/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_user_group = $this->atn->get_ADM_M_UserGroup_List();
        $r_user_group = $q_user_group->result();

        $column = array("ID", "UserGroup Code", "UserGroup Name EN", "UserGroup TH", "UserGroup_Desc", "Active");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_user_group, $r_user_group, $column, "user_group/user_group_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of User Group.'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('user_group','user_group/user_group_processec/','A','')\">"
            , 'button_add' => $button_add
        ));
    }

    function user_group_processec() {// select processec (Add, Edit, Delete) User Group.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") :// ADD.
            $Id = "";
            $this->user_group_management($mode, $Id);
        elseif ($mode == "V" || $mode == "E") :// VIEW & EDIT.
            $Id = $data['id'];
            $this->user_group_management($mode, $Id);
        elseif ($mode == "D") :// DELETE.
            $Id = $data['id'];
            $result_check_inactive = $this->check_user_group_set_inactive($Id);
            if ($result_check_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Can not be inactive User Group. User Group is already in used. Do not inactive!')</script>";
                redirect('user_group', 'refresh');
            else:
                $result_inactive = $this->user_group_set_inactive($Id);
                if ($result_inactive === TRUE):
                    echo "<script type='text/javascript'>alert('Set InActive ADM_M_UserGroup by UserGroup_Id = ' . $Id . ' Success.')</script>";
                    redirect('user_group', 'refresh');
                else:
                    log_message('error', 'Set InActive ADM_M_UserGroup by UserGroup_Id = ' . $Id . ' Unsuccess.');
                    echo "<script type='text/javascript'>alert('Set InActive ADM_M_UserGroup by UserGroup_Id = ' . $Id . ' Unsuccess. Please check?')</script>";
                    redirect('user_group', 'refresh');
                endif;
            endif;
        endif;
    }

    function user_group_management($mode, $UserGroupId) {// Add & Edit User Group.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $UserGroup_Id = '';
        $UserGroup_Code = '';
        $UserGroup_NameEN = '';
        $UserGroup_NameTH = '';
        $UserGroup_Desc = '';
        $Active = '';

        $q_member_group = $this->atn->get_member_list();
        $memberGroupList = $q_member_group->result();

        $memberGroupIDList = array();
        if ($mode != 'A'):
            $r_member_group_id = $this->atn->get_ADM_R_UserGroupMembers(NULL, NULL, $UserGroupId, NULL, TRUE)->result();

            $i = 0;
            foreach ($r_member_group_id as $value) :
                $memberGroupIDList[$i] = $value->UserLogin_Id;
                $i++;
            endforeach;
        endif;

        if (!empty($UserGroupId)):
            $r_user_group = $this->atn->get_ADM_M_UserGroup($UserGroupId, NULL, FALSE)->result();
            if (count($r_user_group) > 0):
                foreach ($r_user_group as $value) :
                    $UserGroup_Id = $UserGroupId;
                    $UserGroup_Code = $value->UserGroup_Code;
                    $UserGroup_NameEN = $value->UserGroup_NameEN;
                    $UserGroup_NameTH = $value->UserGroup_NameTH;
                    $UserGroup_Desc = $value->UserGroup_Desc;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        $this->load->helper('form');
        $str_form = ''; //form_fieldset('User Group.');
        $str_form.=$this->parser->parse('form/user_group_master', array("mode" => $mode, "UserGroup_Id" => $UserGroup_Id
            , "UserGroup_Code" => $UserGroup_Code, "UserGroup_NameEN" => $UserGroup_NameEN
            , "UserGroup_NameTH" => $UserGroup_NameTH, "UserGroup_Desc" => $UserGroup_Desc, "Active" => $Active
            , "memberGroupList" => $memberGroupList, "memberGroupIDList" => $memberGroupIDList), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Edit User Group.'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    function validation() {// Add by Ton! 20140228
        $data = $this->input->post();

        $result = array();
        $result['result'] = 1;
        $result['note'] = NULL;

        # Check Delete User Group.
        $Active = $this->input->post("Active");
        if ($data['type'] === "E"):
            if ($Active !== 'on'):// InActive
                $result_check = $this->check_user_group_set_inactive($data['UserGroup_Id']);
                if ($result_check === TRUE):
                    $result['result'] = 0;
                    $result['note'] = "GROUP_DEL";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        # Check User Group Code already exists.
        $result_check = $this->check_user_group($data["UserGroup_Code"]);
        if ($data['type'] === "A"):
            if ($result_check === TRUE):
                $result['result'] = 0;
                $result['note'] = "GROUP_CODE_ALREADY";
                echo json_encode($result);
                return;
            endif;
        else:
            if ($data["UserGroup_Code"] !== $data["Current_Code"]):
                if ($result_check === TRUE):
                    $result['result'] = 0;
                    $result['note'] = "GROUP_CODE_ALREADY";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        echo json_encode($result);
        return;
    }

    function save_user_group() {
        $this->transaction_db->transaction_start();
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $UserGroupID = ($data['UserGroup_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['UserGroup_Id']);

        $Members_Group_ID = NULL;
        if (isset($data['checked'])):
            if (!empty($data['checked'])):
                $Members_Group_ID = explode(",", $data['checked']);
            endif;
        endif;

        $where_Group['UserGroup_Id'] = ($data['UserGroup_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['UserGroup_Id']);

        $data_Group['UserGroup_Code'] = ($data['UserGroup_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['UserGroup_Code']);
        $data_Group['UserGroup_NameEN'] = ($data['UserGroup_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['UserGroup_NameEN']);
        $data_Group['UserGroup_NameTH'] = ($data['UserGroup_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['UserGroup_NameTH']);
        $data_Group['UserGroup_Desc'] = ($data['UserGroup_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['UserGroup_Desc']);

        $Active = $this->input->post("Active");
        if ($Active == 'on'):
            $data_Group['Active'] = TRUE;
        else:
            $data_Group['Active'] = FALSE;
        endif;

        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $data_Group['Created_Date'] = $human;
                    $data_Group['Created_By'] = $this->session->userdata('user_id');
                    $data_Group['Modified_Date'] = $human;
                    $data_Group['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->atn->save_ADM_M_UserGroup('ist', $data_Group);
                    if ($result > 0):
                        $UserGroupID = $result;
                        $result = TRUE;
                    else:
                        log_message('error', 'save ADM_M_UserGroup Unsuccess.');
                        $result = FALSE;
                    endif;
                }break;
            case "E" : {
                    $data_Group['Modified_Date'] = $human;
                    $data_Group['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->atn->save_ADM_M_UserGroup('upd', $data_Group, $where_Group);
                    if ($result === FALSE) :
                        log_message('error', 'save ADM_M_UserGroup Unsuccess.');
                    endif;
                }
        endswitch;

        if ($result === TRUE) :
//            $this->cache->memcached->clean();
            $result = $this->save_UserGroupMembers($UserGroupID, $Members_Group_ID);
            if ($result === FALSE):
                log_message('error', 'save ADM_R_UserGroupMembers Unsuccess.');
            endif;
        endif;

        if ($result === TRUE) :
            $this->transaction_db->transaction_commit();
        else:
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    private function check_user_group_set_inactive($UserGroup_Id) {// Add by Ton! 20140228
        $result = FALSE;
        $check_member_group = $this->atn->get_ADM_R_UserGroupMembers(NULL, NULL, $UserGroup_Id, NULL, TRUE)->result();
        if (count($check_member_group) > 0):
            $result = TRUE;
        endif;

        return $result;
    }

    private function user_group_set_inactive($UserGroup_Id) {// Set Inactive User Group.
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Group['UserGroup_Id'] = $UserGroup_Id;

        $data_Group['Active'] = FALSE;

        $data_Group['Modified_Date'] = $human;
        $data_Group['Modified_By'] = $this->session->userdata('user_id');
        $this->transaction_db->transaction_start();
        $result_inactive = $this->atn->save_ADM_M_UserGroup('upd', $data_Group, $where_Group);
        if ($result_inactive === TRUE):
            $this->transaction_db->transaction_commit();
            return TRUE;
        else:
            log_message('error', 'Inactive UserGroup_Id = ' . $UserGroup_Id . ' Table ADM_M_UserGroup Unsuccess.');
            $this->transaction_db->transaction_rollback();
            return FALSE;
        endif;
    }

    private function save_UserGroupMembers($UserGroup_Id, $Members_Group_ID) {
        $result = TRUE;
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Group['UserGroup_Id'] = $UserGroup_Id;
        $this->atn->save_ADM_R_UserGroupMembers("del", NULL, $where_Group);

        if (!empty($Members_Group_ID)):
            foreach ($Members_Group_ID as $MembersID) :
                $data_Member['UserGroup_Id'] = $UserGroup_Id;
                $data_Member['UserLogin_Id'] = $MembersID;
                $data_Member['Active'] = TRUE;

                $data_Member['Created_Date'] = $human;
                $data_Member['Created_By'] = $this->session->userdata('user_id');
                $data_Member['Modified_Date'] = $human;
                $data_Member['Modified_By'] = $this->session->userdata('user_id');

                $result_save = $this->atn->save_ADM_R_UserGroupMembers("ist", $data_Member);
                if ($result_save === FALSE):
                    $result = FALSE;
                endif;
            endforeach;
        endif;

        $this->cache->memcached->clean();
        return $result;
    }

    function check_user_group($UserGroup_Code) {// Check UserGroup_Code Already.
        $r_user_group = $this->atn->get_ADM_M_UserGroup(NULL, $UserGroup_Code, FALSE)->result();
        if (count($r_user_group) > 0):
            return TRUE;
        else:
            return FALSE;
        endif;
    }

}
