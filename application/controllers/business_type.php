<?php

/*
 * Create by Ton! 20131204
 */
?>

<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class business_type extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model("business_type_model", "bis");
        $this->load->model("company_model", "com");
        $this->load->model('menu_model', 'mnu');
        $this->load->model('authen_model', 'atn');

        $this->mnu_NavigationUri = "business_type";
    }

    public function index() {
        $this->business_type_list();
    }

    function business_type_list() {// Display List of Company Group.
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
             ONCLICK=\"openForm('business_type','business_type/business_type_processec/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_business_type = $this->bis->get_CTL_M_BusinessType_List();
        $r_business_type = $q_business_type->result();

        $column = array("ID", "BusinessType Code", "BusinessType Name EN", "BusinessType Name TH", "BusinessType Desc", "Active");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_business_type, $r_business_type, $column, "business_type/business_type_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Business Type.'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('business_type','business_type/business_type_processec/','A','')\">"
            , 'button_add' => $button_add # Permission Button. by Ton! 20140130
        ));
    }

    function business_type_processec() {// select processec (Add, Edit, Delete) Company Group.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") :// ADD.
            $Id = "";
            $this->business_type_management($mode, $Id);
        elseif ($mode == "V" || $mode == "E") :// VIEW & EDIT.
            $Id = $data['id'];
            $this->business_type_management($mode, $Id);
        elseif ($mode == "D") :// DELETE.
            $Id = $data['id'];
            $result_inactive = $this->business_type_set_inactive($Id);
            if ($result_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Delete data Business Type Success.')</script>";
                redirect('business_type', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Delete data Business Type Unsuccess. Please check?')</script>";
                redirect('business_type', 'refresh');
            endif;
        endif;
    }

    function business_type_set_inactive($BusinessType_Id) {// Set Inactive Business Type.
        if ($BusinessType_Id !== NULL):
            $this->load->helper("date");
            $human = mdate("%Y-%m-%d %h:%i:%s", time());

            $where_Bis['BusinessType_Id'] = $BusinessType_Id;

            $data_Bis['Active'] = 0;
            $data_Bis['Modified_Date'] = $human;
            $data_Bis['Modified_By'] = $this->session->userdata('user_id');
            $this->transaction_db->transaction_start();
            $result_inactive = $this->bis->save_CTL_M_BusinessType('upd', $data_Bis, $where_Bis);
            if ($result_inactive === TRUE):
                $this->transaction_db->transaction_commit();
                return TRUE;
            else:
                log_message('error', 'save CTL_M_BusinessType Unsuccess.');
                $this->transaction_db->transaction_rollback();
                return FALSE;
            endif;
        else:
            log_message('error', 'save CTL_M_BusinessType Unsuccess. BusinessType_Id IS NULL.');
            return FALSE;
        endif;
    }

    function business_type_management($mode, $BusinessTypeId) {// Add & Edit Business Type.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $BusinessType_Id = '';
        $BusinessType_Code = '';
        $BusinessType_NameEN = '';
        $BusinessType_NameTH = '';
        $BusinessType_Desc = '';
        $Active = TRUE; // Edit By Akkarapol, 23/01/2014, Set variable $Active = TRUE for set default checked in check_box

        if (!empty($BusinessTypeId)):
            $q_business_type = $this->com->getBusinessType($BusinessTypeId, NULL, NULL, FALSE);
            $r_business_type = $q_business_type->result();
            if (count($r_business_type) > 0):
                foreach ($r_business_type as $value) :
                    $BusinessType_Id = $BusinessTypeId;
                    $BusinessType_Code = $value->BusinessType_Code;
                    $BusinessType_NameEN = $value->BusinessType_NameEN;
                    $BusinessType_NameTH = $value->BusinessType_NameTH;
                    $BusinessType_Desc = $value->BusinessType_Desc;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        $this->load->helper('form');
        $str_form = form_fieldset('Business Type.');
        $str_form.=$this->parser->parse('form/business_type_master', array("mode" => $mode
            , "BusinessType_Id" => $BusinessType_Id, "BusinessType_Code" => $BusinessType_Code
            , "BusinessType_NameEN" => $BusinessType_NameEN, "BusinessType_NameTH" => $BusinessType_NameTH
            , "BusinessType_Desc" => $BusinessType_Desc, "Active" => $Active), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add/Edit Business Type.'
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

        # Check BusinessType Code already exists.
        if ($data['BusinessType_Code'] !== ""):
            if ($data['type'] === "E"):
                if ($data['BusinessType_Code'] !== $data['Current_Code']):
                    $result_check = $this->check_business_type($data['BusinessType_Code']);
                    if ($result_check === TRUE) :
                        $result['result'] = 0;
                        $result['note'] = "BUSI_CODE_ALREADY";
                        echo json_encode($result);
                        return;
                    endif;
                endif;
            elseif ($data['type'] === "A"):
                $result_check = $this->check_business_type($data['BusinessType_Code']);
                if ($result_check === TRUE) :
                    $result['result'] = 0;
                    $result['note'] = "BUSI_CODE_ALREADY";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        echo json_encode($result);
        return;
    }

    function save_business_type() {
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Bis['BusinessType_Id'] = ($data['BusinessType_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['BusinessType_Id']);

        $data_Bis['BusinessType_Code'] = ($data['BusinessType_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['BusinessType_Code']);
        $data_Bis['BusinessType_NameEN'] = ($data['BusinessType_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['BusinessType_NameEN']);
        $data_Bis['BusinessType_NameTH'] = ($data['BusinessType_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['BusinessType_NameTH']);
        $data_Bis['BusinessType_Desc'] = ($data['BusinessType_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['BusinessType_Desc']);

        $Active = $this->input->post("Active");
        if ($Active == 'on'):
            $data_Bis['Active'] = TRUE;
        else:
            $data_Bis['Active'] = FALSE;
        endif;

        $this->transaction_db->transaction_start();
        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $data_Bis['Created_Date'] = $human;
                    $data_Bis['Created_By'] = $this->session->userdata('user_id');
                    $data_Bis['Modified_Date'] = $human;
                    $data_Bis['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->bis->save_CTL_M_BusinessType('ist', $data_Bis);
                    if ($result > 0):
                        $result = TRUE;
                    endif;
                }break;
            case "E" : {
                    $data_Bis['Modified_Date'] = $human;
                    $data_Bis['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->bis->save_CTL_M_BusinessType('upd', $data_Bis, $where_Bis);
                }break;
        endswitch;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            log_message('error', 'save CTL_M_BusinessType Unsuccess.');
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    function check_business_type($BusinessType_Code = NULL) {// Check BusinessType_Code Already.
        $result = FALSE;
        if (!empty($BusinessType_Code)):
            $r_business_type = $this->com->getBusinessType(NULL, $BusinessType_Code, NULL, FALSE)->result();
            if (count($r_business_type) > 0):
                $result = TRUE;
            endif;
        endif;

        return $result;
    }

}
