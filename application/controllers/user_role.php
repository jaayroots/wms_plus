<?php

/*
 * Create by Ton! 20140123
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class user_role extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    public function __construct() {
        parent::__construct();
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");

        $this->mnu_NavigationUri = "user_role";
    }

    public function index() {
        $this->get_user_role_list();
    }

    function get_user_role_list() {// Display List of User Role.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        $action = $action_parmission['action_button'];

        $button_add = "";
        if (in_array("-2", $action_parmission)):
            $button_add = "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
             ONCLICK=\"openForm('user_role','user_role/user_role_processec/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_user_role = $this->atn->get_ADM_M_UserRole_List();
        $r_user_role = $q_user_role->result();

        $column = array("ID", "UserRole Code", "UserRole Name EN", "UserRole TH", "UserRole_Desc", "Active");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_user_role, $r_user_role, $column, "user_role/user_role_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of User Role.'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('user_role','user_role/user_role_processec/','A','')\">"
            , 'button_add' => $button_add
        ));
    }

    function user_role_processec() {// select processec (Add, Edit, Delete) User Role.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") {// ADD.
            $Id = "";
            $this->user_role_management($mode, $Id);
        } elseif ($mode == "V" || $mode == "E") {// VIEW & EDIT.
            $Id = $data['id'];
            $this->user_role_management($mode, $Id);
        } elseif ($mode == "D") {// DELETE.
            $Id = $data['id'];
            $result_check_inactive = $this->check_user_role_set_inactive($Id);
            if ($result_check_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Can not be inactive User Role. User Role is already in used. Do not inactive!')</script>";
            else:
                $result_inactive = $this->user_role_set_inactive($Id);
                if ($result_inactive === TRUE):
                    echo "<script type='text/javascript'>alert('Delete data User Role Success.')</script>";
                else:
                    echo "<script type='text/javascript'>alert('Delete data User Role Unsuccess. Please check?')</script>";
                endif;
            endif;

            redirect('user_role', 'refresh');
        }
    }

    function user_role_management($mode, $UserRoleId) {// Add & Edit User Role.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $UserRole_Id = '';
        $UserRole_Code = '';
        $UserRole_NameEN = '';
        $UserRole_NameTH = '';
        $UserRole_Desc = '';
        $Active = '';

        $q_member_role = $this->atn->get_member_list();
        $memberRoleList = $q_member_role->result();

        $IDList = array();
        if ($mode != 'A'):
            $r_member_role_id = $this->atn->get_ADM_R_UserRoleMembers(NULL, NULL, $UserRoleId, NULL, TRUE)->result();

            $i = 1;
            foreach ($r_member_role_id as $value) :
                $IDList[$i] = $value->UserLogin_Id;
                $i++;
            endforeach;
        endif;

        $memberRoleIDList = $IDList;

        if (!empty($UserRoleId)):
            $q_user_role = $this->atn->get_ADM_M_UserRole(NULL, $UserRoleId, NULL, FALSE);
            $r_user_role = $q_user_role->result();
            if (count($r_user_role) > 0):
                foreach ($r_user_role as $value) :
                    $UserRole_Id = $UserRoleId;
                    $UserRole_Code = $value->UserRole_Code;
                    $UserRole_NameEN = $value->UserRole_NameEN;
                    $UserRole_NameTH = $value->UserRole_NameTH;
                    $UserRole_Desc = $value->UserRole_Desc;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        $this->load->helper('form');
        $str_form = ''; //form_fieldset('User Role.');

        $str_form.=$this->parser->parse('form/user_role_master', array("mode" => $mode, "UserRole_Id" => $UserRole_Id
            , "UserRole_Code" => $UserRole_Code, "UserRole_NameEN" => $UserRole_NameEN
            , "UserRole_NameTH" => $UserRole_NameTH, "UserRole_Desc" => $UserRole_Desc, "Active" => $Active
            , "memberRoleList" => $memberRoleList, "memberRoleIDList" => $memberRoleIDList), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add/Edit User Role.'
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
                $result_check = $this->check_user_role_set_inactive($data['UserRole_Id']);
                if ($result_check === TRUE):
                    $result['result'] = 0;
                    $result['note'] = "ROLE_DEL";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        # Check User Group Code already exists.
        $result_check = $this->check_user_role($data["UserRole_Code"]);
        if ($data['type'] === "A"):
            if ($result_check === TRUE):
                $result['result'] = 0;
                $result['note'] = "ROLE_CODE_ALREADY";
                echo json_encode($result);
                return;
            endif;
        else:
            if ($data["UserRole_Code"] !== $data["Current_Code"]):
                if ($result_check === TRUE):
                    $result['result'] = 0;
                    $result['note'] = "ROLE_CODE_ALREADY";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        echo json_encode($result);
        return;
    }

    function save_user_role() {
        $this->transaction_db->transaction_start();
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $UserRoleID = ($data['UserRole_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['UserRole_Id']);

        $Members_Role_ID = NULL;
        if (isset($data['checked'])):
            $Members_Role_ID = explode(",", $data['checked']);
        endif;

        $where_Role['UserRole_Id'] = ($data['UserRole_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['UserRole_Id']);

        $data_Role['UserRole_Code'] = ($data['UserRole_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['UserRole_Code']);
        $data_Role['UserRole_NameEN'] = ($data['UserRole_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['UserRole_NameEN']);
        $data_Role['UserRole_NameTH'] = ($data['UserRole_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['UserRole_NameTH']);
        $data_Role['UserRole_Desc'] = ($data['UserRole_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['UserRole_Desc']);

        $Active = $this->input->post("Active");
        if ($Active == 'on'):
            $data_Role['Active'] = TRUE;
        else:
            $data_Role['Active'] = FALSE;
        endif;

        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $data_Role['Created_Date'] = $human;
                    $data_Role['Created_By'] = $this->session->userdata('user_id');
                    $data_Role['Modified_Date'] = $human;
                    $data_Role['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->atn->save_ADM_M_UserRole('ist', $data_Role);
                    if ($result > 0):
                        $UserRoleID = $result;
                        $result = TRUE;
                    else:
                        $result = FALSE;
                        $this->transaction_db->transaction_rollback();
                    endif;
                }break;
            case "E" : {
                    $data_Role['Modified_Date'] = $human;
                    $data_Role['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->atn->save_ADM_M_UserRole('upd', $data_Role, $where_Role);
                }
        endswitch;

        if ($result === TRUE) :
//            $this->cache->memcached->clean();
            $result = $this->save_UserRoleMembers($UserRoleID, $Members_Role_ID);
            if ($result === TRUE) :
                $this->transaction_db->transaction_commit();
            else:
                $this->transaction_db->transaction_rollback();
            endif;

        else:
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    private function check_user_role_set_inactive($UserRole_Id) {// Add by Ton! 20140228
        $result = FALSE;
        $check_member_role = $this->atn->get_ADM_R_UserRoleMembers(NULL, NULL, $UserRole_Id, NULL, TRUE)->result();
        if (count($check_member_role) > 0):
            $result = TRUE;
        endif;

        return $result;
    }

    private function user_role_set_inactive($UserRole_Id) {// Set Inactive User Role.
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Role['UserRole_Id'] = $UserRole_Id;

        $data_Role['Active'] = FALSE;

        $data_Role['Modified_Date'] = $human;
        $data_Role['Modified_By'] = $this->session->userdata('user_id');
        $result_inactive = $this->atn->save_ADM_M_UserRole('upd', $data_Role, $where_Role);
        if ($result_inactive === TRUE):
            return TRUE;
        else:
            return FALSE;
        endif;
    }

    private function save_UserRoleMembers($UserRole_Id, $Members_Role_ID) {
        $result = TRUE;
        
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Role['UserRole_Id'] = $UserRole_Id;
        $this->atn->save_ADM_R_UserRoleMembers("del", NULL, $where_Role);

        if (!empty($Members_Role_ID)):
            foreach ($Members_Role_ID as $MembersID) :
                $data_Member['UserRole_Id'] = $UserRole_Id;
                $data_Member['UserLogin_Id'] = $MembersID;
                $data_Member['Active'] = TRUE;

                $data_Member['Created_Date'] = $human;
                $data_Member['Created_By'] = $this->session->userdata('user_id');
                $data_Member['Modified_Date'] = $human;
                $data_Member['Modified_By'] = $this->session->userdata('user_id');

                $result_save = $this->atn->save_ADM_R_UserRoleMembers("ist", $data_Member);
                if ($result_save === FALSE):
                    $result = FALSE;
                endif;
            endforeach;
        endif;

        $this->cache->memcached->clean();
        return $result;
    }

    function check_user_role($UserRole_Code) {// Check UserRole_Code Already.
        $r_user_role = $this->atn->get_ADM_M_UserRole(NULL, NULL, $UserRole_Code, FALSE)->result();
        if (count($r_user_role) > 0):
            return TRUE;
        else:
            return FALSE;
        endif;
    }

}
