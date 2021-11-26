<?php

/*
 * Create by Ton! 20131204
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class department extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model("department_model", "dep");
        $this->load->model('menu_model', 'mnu');
        $this->load->model('authen_model', 'atn');

        $this->mnu_NavigationUri = "department";
    }

    public function index() {
        $this->department_list();
    }

    function department_list() {// Display List of Department.
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
             ONCLICK=\"openForm('position','department/department_processec/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_department = $this->dep->get_CTL_M_Department_List();
        $r_department = $q_department->result();

        $column = array("ID", "Department Code", "Department Name EN", "Department Name TH", "Department Desc", "Active");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_department, $r_department, $column, "department/department_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Department.'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('position','department/department_processec/','A','')\">"
            , 'button_add' => $button_add # Permission Button. by Ton! 20140130
        ));
    }

    function department_processec() {// select processec (Add, Edit, Delete) Department.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") {// ADD.
            $Id = "";
            $this->department_management($mode, $Id);
        } elseif ($mode == "V" || $mode == "E") {// VIEW & EDIT.
            $Id = $data['id'];
            $this->department_management($mode, $Id);
        } elseif ($mode == "D") {// DELETE.
            $Id = $data['id'];
            $result_inactive = $this->department_set_inactive($Id);
            if ($result_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Delete data Department Success.')</script>";
                redirect('department', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Delete data Department Unsuccess. Please check?')</script>";
                redirect('department', 'refresh');
            endif;
        }
    }

    function department_set_inactive($Department_Id) {// Set Inactive Department.
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Dep['Department_Id'] = $Department_Id;

        $data_Dep['Active'] = 0;

        $data_Dep['Modified_Date'] = $human;
        $data_Dep['Modified_By'] = $this->session->userdata('user_id');
        $this->transaction_db->transaction_start();
        $result_inactive = $this->dep->save_CTL_M_Department('upd', $data_Dep, $where_Dep);
        if ($result_inactive === TRUE):
            $this->transaction_db->transaction_commit();
            return TRUE;
        else:
            log_message('error', 'save CTL_M_Department Unsuccess.');
            $this->transaction_db->transaction_rollback();
            return FALSE;
        endif;
    }

    function department_management($mode, $DepartmentId) {// Add & Edit Position.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $Department_Id = '';
        $Department_Code = '';
        $Department_NameEN = '';
        $Department_NameTH = '';
        $Department_Desc = '';
        $Active = TRUE; // Edit By Akkarapol, 23/01/2014, Set variable $Active = TRUE for set default checked in check_box

        if (!empty($DepartmentId)):
            $q_department = $this->dep->get_CTL_M_Department($DepartmentId, NULL, FALSE);
            $r_department = $q_department->result();
            if (count($r_department) > 0):
                foreach ($r_department as $value) :
                    $Department_Id = $DepartmentId;
                    $Department_Code = $value->Department_Code;
                    $Department_NameEN = $value->Department_NameEN;
                    $Department_NameTH = $value->Department_NameTH;
                    $Department_Desc = $value->Department_Desc;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        $this->load->helper('form');
        $str_form = form_fieldset('Department.');
        $str_form.=$this->parser->parse('form/department_master', array("mode" => $mode
            , "Department_Id" => $Department_Id, "Department_Code" => $Department_Code
            , "Department_NameEN" => $Department_NameEN, "Department_NameTH" => $Department_NameTH
            , "Department_Desc" => $Department_Desc, "Active" => $Active), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add/Edit Department.'
//        , 'menu' => $this->menu->loadMenuAuth()
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

        # Check Department Code already exists.
        if ($data['Department_Code'] !== ""):
            if ($data['type'] === "E"):
                if ($data['Department_Code'] !== $data['Current_Code']):
                    $result_check = $this->check_department($data['Department_Code']);
                    if ($result_check === TRUE) :
                        $result['result'] = 0;
                        $result['note'] = "DEP_CODE_ALREADY";
                        echo json_encode($result);
                        return;
                    endif;
                endif;
            elseif ($data['type'] === "A"):
                $result_check = $this->check_department($data['Department_Code']);
                if ($result_check === TRUE) :
                    $result['result'] = 0;
                    $result['note'] = "DEP_CODE_ALREADY";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        echo json_encode($result);
        return;
    }

    function save_department() {
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Dep['Department_Id'] = ($data['Department_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Department_Id']);

        $data_Dep['Department_Code'] = ($data['Department_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Department_Code']);
        $data_Dep['Department_NameEN'] = ($data['Department_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Department_NameEN']);
        $data_Dep['Department_NameTH'] = ($data['Department_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Department_NameTH']);
        $data_Dep['Department_Desc'] = ($data['Department_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Department_Desc']);

        $Active = $this->input->post("Active");
        if ($Active == 'on'):
            $data_Dep['Active'] = TRUE;
        else:
            $data_Dep['Active'] = FALSE;
        endif;

        $this->transaction_db->transaction_start();
        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $data_Dep['Created_Date'] = $human;
                    $data_Dep['Created_By'] = $this->session->userdata('user_id');
                    $data_Dep['Modified_Date'] = $human;
                    $data_Dep['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->dep->save_CTL_M_Department('ist', $data_Dep);
                    if ($result > 0):
                        $result = TRUE;
                    endif;
                }break;
            case "E" : {
                    $data_Dep['Modified_Date'] = $human;
                    $data_Dep['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->dep->save_CTL_M_Department('upd', $data_Dep, $where_Dep);
                }break;
        endswitch;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            log_message('error', 'save CTL_M_Department Unsuccess.');
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    function check_department($Department_Code = NULL) {// Check Department_Code Already.
        $result = FALSE;

        if (!empty($Department_Code)):
            $r_department = $this->dep->get_CTL_M_Department(NULL, $Department_Code, FALSE)->result();
            if (count($r_department) > 0):
                $result = TRUE;
            endif;
        endif;

        return $result;
    }

}
