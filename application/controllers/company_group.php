<?php

/*
 * Create by Ton! 20131203
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class company_group extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model("company_model", "com");
        $this->load->model('menu_model', 'mnu');
        $this->load->model('authen_model', 'atn');

        $this->mnu_NavigationUri = "company_group";
    }

    public function index() {
        $this->company_group_list();
    }

    function company_group_list() {// Display List of Company Group.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        if (isset($action_parmission['action_button'])):
            $action = $action_parmission['action_button'];
        else:
            $action = array();
        endif;

        $button_add = "";
        if (in_array("-2", $action_parmission)):
            $button_add = "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
             ONCLICK=\"openForm('company_group','company_group/company_group_processec/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_company_group = $this->com->get_CTL_M_CompanyGroup_List();
        $r_company_group = $q_company_group->result();

        $column = array("ID", "CompanyGroup Code", "CompanyGroup Name EN", "CompanyGroup Name TH", "CompanyGroup Desc", "Active");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_company_group, $r_company_group, $column, "company_group/company_group_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Company Group.'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('company_group','company_group/company_group_processec/','A','')\">"
            , 'button_add' => $button_add # Permission Button. by Ton! 20140130
        ));
    }

    function company_group_processec() {// select processec (Add, Edit, Delete) Company Group.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") {// ADD.
            $Id = "";
            $this->company_group_management($mode, $Id);
        } elseif ($mode == "V" || $mode == "E") {// VIEW & EDIT.
            $Id = $data['id'];
            $this->company_group_management($mode, $Id);
        } elseif ($mode == "D") {// DELETE.
            $Id = $data['id'];
            $result_inactive = $this->company_group_set_inactive($Id);
            if ($result_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Delete data Company Group Success.')</script>";
                redirect('company_group', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Delete data Company Group Unsuccess. Please check?')</script>";
                redirect('company_group', 'refresh');
            endif;
        }
    }

    private function company_group_set_inactive($CompanyGroupId) {// Set Inactive User Group.
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Group['CompanyGroup_Id'] = $CompanyGroupId;

        $data_Group['Active'] = FALSE;

        $data_Group['Modified_Date'] = $human;
        $data_Group['Modified_By'] = $this->session->userdata('user_id');
        $this->transaction_db->transaction_start();
        $result_inactive = $this->com->save_CTL_M_CompanyGroup('upd', $data_Group, $where_Group);
        if ($result_inactive === TRUE):
            $this->transaction_db->transaction_commit();
            return TRUE;
        else:
            log_message('error', 'save CTL_M_CompanyGroup Unsuccess.');
            $this->transaction_db->transaction_rollback();
            return FALSE;
        endif;
    }

    function company_group_management($mode, $CompanyGroupId) {// Add & Edit User Group.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $CompanyGroup_Id = '';
        $CompanyGroup_Code = '';
        $CompanyGroup_NameEN = '';
        $CompanyGroup_NameTH = '';
        $CompanyGroup_Desc = '';
        $Active = TRUE; // Edit By Akkarapol, 23/01/2014, Set variable $Active = TRUE for set default checked in check_box

        $q_member_company_group = $this->com->get_member_company_group_list();
        $r_member_company_group = $q_member_company_group->result();

        $IDList = array();
        if ($mode != 'A'):
            $q_member_group_id = $this->com->get_CTL_R_CompanyGroupMembers(NULL, NULL, $CompanyGroupId, NULL, TRUE);
            $r_member_group_id = $q_member_group_id->result();

            $i = 0;
            foreach ($r_member_group_id as $value) {
                $IDList[$i] = $value->Company_Id;
                $i++;
            }
        endif;

        $memberGroupIDList = $IDList;

        if (!empty($CompanyGroupId)):
            $q_company_group = $this->com->get_CTL_M_CompanyGroup(NULL, $CompanyGroupId, NULL, FALSE);
            $r_company_group = $q_company_group->result();
            if (count($r_company_group) > 0):
                foreach ($r_company_group as $value) :
                    $CompanyGroup_Id = $CompanyGroupId;
                    $CompanyGroup_Code = $value->CompanyGroup_Code;
                    $CompanyGroup_NameEN = $value->CompanyGroup_NameEN;
                    $CompanyGroup_NameTH = $value->CompanyGroup_NameTH;
                    $CompanyGroup_Desc = $value->CompanyGroup_Desc;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        $this->load->helper('form');
        $str_form = ''; //form_fieldset('Company Group.');
        $str_form.=$this->parser->parse('form/company_group_master', array("mode" => $mode
            , "CompanyGroup_Id" => $CompanyGroup_Id, "CompanyGroup_Code" => $CompanyGroup_Code
            , "CompanyGroup_NameEN" => $CompanyGroup_NameEN, "CompanyGroup_NameTH" => $CompanyGroup_NameTH
            , "CompanyGroup_Desc" => $CompanyGroup_Desc, "Active" => $Active
            , "memberGroupList" => $r_member_company_group, "memberGroupIDList" => $memberGroupIDList), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Edit Company Group.'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    function validation() {// Add by Ton! 20140306
        $data = $this->input->post();

        $result = array();
        $result['result'] = 1;
        $result['note'] = NULL;

        # Check CompanyGroup Code already exists.
        if ($data['CompanyGroup_Code'] !== ""):
            if ($data['type'] === "E"):
                if ($data['CompanyGroup_Code'] !== $data['Current_Code']):
                    $result_check = $this->check_company_group($data['CompanyGroup_Code']);
                    if ($result_check === TRUE) :
                        $result['result'] = 0;
                        $result['note'] = "COM_G_CODE_ALREADY";
                        echo json_encode($result);
                        return;
                    endif;
                endif;
            elseif ($data['type'] === "A"):
                $result_check = $this->check_company_group($data['CompanyGroup_Code']);
                if ($result_check === TRUE) :
                    $result['result'] = 0;
                    $result['note'] = "COM_G_CODE_ALREADY";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        echo json_encode($result);
        return;
    }

    function save_CompanyGroup() {
        $data = $this->input->post();
        $datestring = "%Y-%m-%d %h:%i:%s";
        $time = time();
        $this->load->helper("date");
        $human = mdate($datestring, $time);

        $CompanyGroupId = ($data['CompanyGroup_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['CompanyGroup_Id']);

        $Members_Group_ID = NULL;
        if (isset($data['checked'])):
            $Members_Group_ID = explode(",", $data['checked']);
        endif;

        $where_Group['CompanyGroup_Id'] = ($data['CompanyGroup_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['CompanyGroup_Id']);

        $data_Group["CompanyGroup_Code"] = ($data['CompanyGroup_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['CompanyGroup_Code']);
        $data_Group["CompanyGroup_NameEN"] = ($data['CompanyGroup_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['CompanyGroup_NameEN']);
        $data_Group["CompanyGroup_NameTH"] = ($data['CompanyGroup_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['CompanyGroup_NameTH']);
        $data_Group["CompanyGroup_Desc"] = ($data['CompanyGroup_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['CompanyGroup_Desc']);

        $Active = $this->input->post("Active");
        if ($Active == 'on'):
            $data_Group['Active'] = TRUE;
        else:
            $data_Group['Active'] = FALSE;
        endif;

        $this->transaction_db->transaction_start();
        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $data_Group['Created_Date'] = $human;
                    $data_Group['Created_By'] = $this->session->userdata('user_id');
                    $data_Group['Modified_Date'] = $human;
                    $data_Group['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->com->save_CTL_M_CompanyGroup('ist', $data_Group);
                    if ($result > 0):
                        $CompanyGroupId = $result;
                        $result = TRUE;
                    endif;
                }break;
            case "E" : {
                    $data_Group['Modified_Date'] = $human;
                    $data_Group['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->com->save_CTL_M_CompanyGroup('upd', $data_Group, $where_Group);
                }break;
        endswitch;

        if ($result === TRUE) :
            $result = $this->save_CompanyGroupMembers($type, $CompanyGroupId, $Members_Group_ID);
        else:
            log_message('error', 'save _CTL_R_CompanyGroup Unsuccess.');
        endif;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            log_message('error', 'save _CTL_R_CompanyGroupMembers Unsuccess.');
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

//    function save_CompanyGroupMembers() {
    private function save_CompanyGroupMembers($type, $CompanyGroup_Id, $Members_Group_ID) {
        $result = TRUE;

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        # Set InActive ADM_R_UserGroupMembers Where CompanyGroup_Id.
        if ($type == 'E'):
            $where_Group['CompanyGroup_Id'] = $CompanyGroup_Id;

            $data_Group['Active'] = FALSE;
            $data_Group['Modified_Date'] = $human;
            $data_Group['Modified_By'] = $this->session->userdata('user_id');

            $q_member_group_id = $this->com->get_CTL_R_CompanyGroupMembers(NULL, NULL, $CompanyGroup_Id, NULL, FALSE);
            $r_member_group_id = $q_member_group_id->result();
            if (count($r_member_group_id) > 0):
                $result = $this->com->save_CTL_R_CompanyGroupMembers("upd", $data_Group, $where_Group);
            endif;
        endif;

        if ($result === TRUE):
            # Check ADM_R_UserGroupMembers Where CompanyGroup_Id.
            if (count($Members_Group_ID) > 0):

                foreach ($Members_Group_ID as $MembersID) :
                    $q_CompanyGroupMembers = $this->com->get_CTL_R_CompanyGroupMembers(NULL, NULL, $CompanyGroup_Id, $MembersID, FALSE);
                    $r_CompanyGroupMembers = $q_CompanyGroupMembers->result();
                    if (count($r_CompanyGroupMembers) > 0):#Have Data.
                        foreach ($r_CompanyGroupMembers as $CompanyMember) :
                            # Set Active ADM_R_UserGroupMembers Where CompanyGroupMember_Id.
                            $where_Member['CompanyGroupMember_Id'] = $CompanyMember->CompanyGroupMember_Id;

                            $data_Member['Active'] = 1;
                            $data_Member['Modified_Date'] = $human;
                            $data_Member['Modified_By'] = $this->session->userdata('user_id');

                            $result = $this->com->save_CTL_R_CompanyGroupMembers("upd", $data_Member, $where_Member);
                            //print_r($result);
                            //echo "\r\n";
                            if ($result === FALSE):
                                return $result;
                            endif;
                        endforeach;
                    else:#Not Have Data.
                        # Add New CTL_R_CompanyGroupMembers.
                        $data_Member['CompanyGroup_Id'] = $CompanyGroup_Id;
                        $data_Member['Company_Id'] = $MembersID;
                        $data_Member['Active'] = 1;

                        $data_Member['Created_Date'] = $human;
                        $data_Member['Created_By'] = $this->session->userdata('user_id');
                        $data_Member['Modified_Date'] = $human;
                        $data_Member['Modified_By'] = $this->session->userdata('user_id');

                        $result = $this->com->save_CTL_R_CompanyGroupMembers("ist", $data_Member);
                        //print_r($result);
                        //echo "\r\n";
                        if ($result > 0):
                            $result = TRUE;
                        else:
                            $result = FALSE;
                        endif;
                    endif;
                endforeach;
            endif;
        else:
            $result = FALSE;
        endif;

        return $result;
    }

    function check_company_group($CompanyGroup_Code = NULL) {// Check CompanyGroup_Code Already.
        $result = FALSE;
        if (!empty($CompanyGroup_Code)):
            $r_company_group = $this->com->get_CTL_M_CompanyGroup(NULL, NULL, $CompanyGroup_Code, FALSE)->result();
            if (count($r_company_group) > 0):
                $result = TRUE;
            endif;
        endif;

        return $result;
    }

}
