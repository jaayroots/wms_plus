<?php

/*
 * Create by Ton! 20131206
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class province extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");
        $this->load->model("province_model", "pro");
        $this->load->model("country_model", "cou");

        $this->mnu_NavigationUri = "province";
    }

    public function index() {
        $this->province_list();
    }

    function province_list() {// Display List of Province.
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
             ONCLICK=\"openForm('position','province/province_processec/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_province = $this->pro->get_CTL_M_Province_List();
        $r_province = $q_province->result();

        $column = array("ID", "Province Code", "Province Name EN", "Province Name TH", "Province Desc", "Country", "Active");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_province, $r_province, $column, "province/province_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Province.'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('position','province/province_processec/','A','')\">"
            , 'button_add' => $button_add
        ));
    }

    function province_processec() {// select processec (Add, Edit, Delete) Province.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") :// ADD.
            $Id = "";
            $this->province_management($mode, $Id);
        elseif ($mode == "V" || $mode == "E") :// VIEW & EDIT.
            $Id = $data['id'];
            $this->province_management($mode, $Id);
        elseif ($mode == "D") :// DELETE.
            $Id = $data['id'];
            $result_inactive = $this->province_set_inactive($Id);
            if ($result_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Delete data Province Success.')</script>";
                redirect('province', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Delete data Province Unsuccess. Please check?')</script>";
                redirect('province', 'refresh');
            endif;
        endif;
    }

    function province_set_inactive($Province_Id) {// Set Inactive Province.
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Cou['Province_Id'] = $Province_Id;

        $data_Cou['Active'] = 0;

        $data_Cou['Modified_Date'] = $human;
        $data_Cou['Modified_By'] = $this->session->userdata('user_id');
        $this->transaction_db->transaction_start();
        $result_inactive = $this->pro->save_CTL_M_Province('upd', $data_Cou, $where_Cou);
        if ($result_inactive === TRUE):
            $this->transaction_db->transaction_commit();
            return TRUE;
        else:
            log_message('error', 'Inactive Province_Id = ' . $Province_Id . ' Unsuccess.');
            $this->transaction_db->transaction_rollback();
            return FALSE;
        endif;
    }

    function province_management($mode, $ProvinceId) {// Add & Edit Position.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $Province_Id = '';
        $Province_Code = '';
        $Country_Id = '';
        $Province_NameEN = '';
        $Province_NameTH = '';
        $Province_Desc = '';
        $Active = TRUE; // Edit By Akkarapol, 23/01/2014, Set variable $Active = TRUE for set default checked in check_box

        if (!empty($ProvinceId)):
            $q_province = $this->pro->get_CTL_M_Province($ProvinceId, NULL, NULL, FALSE);
            $r_province = $q_province->result();
            if (count($r_province) > 0):
                foreach ($r_province as $value) :
                    $Province_Id = $ProvinceId;
                    $Province_Code = $value->Province_Code;
                    $Country_Id = $value->Country_Id;
                    $Province_NameEN = $value->Province_NameEN;
                    $Province_NameTH = $value->Province_NameTH;
                    $Province_Desc = $value->Province_Desc;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        // get all Country for dispaly dropdown list.
        $q_country_list = $this->cou->get_CTL_M_Country(NULL, NULL, TRUE);
        $r_country_list = $q_country_list->result();
        $optionCountry = genOptionDropdown($r_country_list, "COUNTRY");

        $this->load->helper('form');
        $str_form = form_fieldset('Province.');
        $str_form.=$this->parser->parse('form/province_master', array("mode" => $mode
            , "Province_Id" => $Province_Id, "Province_Code" => $Province_Code, "Country_Id" => $Country_Id
            , "Province_NameEN" => $Province_NameEN, "Province_NameTH" => $Province_NameTH
            , "Province_Desc" => $Province_Desc, "Active" => $Active, "optionCountry" => $optionCountry), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add/Edit Province.'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    function validation() {// Add by Ton! 20140306
        $data = $this->input->post();

        $result = array();
        $result['result'] = 1;
        $result['note'] = NULL;

        # Check City Code already exists.
        if ($data['Province_Code'] !== ""):
            if ($data['type'] === "E"):
                if ($data['Current_Code'] !== $data['Province_Code']):
                    $result_check = $this->check_province($data['Province_Code'], $data['Country_Id']);
                    if ($result_check === TRUE) :
                        $result['result'] = 0;
                        $result['note'] = "PRO_CODE_ALREADY";
                        echo json_encode($result);
                        return;
                    endif;
                endif;
            elseif ($data['type'] === "A"):
                $result_check = $this->check_province($data['Province_Code'], $data['Country_Id']);
                if ($result_check === TRUE) :
                    $result['result'] = 0;
                    $result['note'] = "PRO_CODE_ALREADY";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        echo json_encode($result);
        return;
    }

    function save_province() {
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Pro['Province_Id'] = ($data['Province_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Province_Id']);

        $data_Pro['Province_Code'] = ($data['Province_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Province_Code']);
        $data_Pro['Country_Id'] = ($data['Country_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Country_Id']);
        $data_Pro['Province_NameEN'] = ($data['Province_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Province_NameEN']);
        $data_Pro['Province_NameTH'] = ($data['Province_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Province_NameTH']);
        $data_Pro['Province_Desc'] = ($data['Province_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Province_Desc']);

        $Active = $this->input->post("Active");
        if ($Active == 'on'):
            $data_Pro['Active'] = TRUE;
        else:
            $data_Pro['Active'] = FALSE;
        endif;

        $this->transaction_db->transaction_start();
        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $data_Pro['Created_Date'] = $human;
                    $data_Pro['Created_By'] = $this->session->userdata('user_id');
                    $data_Pro['Modified_Date'] = $human;
                    $data_Pro['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->pro->save_CTL_M_Province('ist', $data_Pro);
                    if ($result > 0):
                        $result = TRUE;
                    else:
                        $result = FALSE;
                    endif;
                }break;
            case "E" : {
                    $data_Pro['Modified_Date'] = $human;
                    $data_Pro['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->pro->save_CTL_M_Province('upd', $data_Pro, $where_Pro);
                }break;
        endswitch;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            log_message('error', 'save CTL_M_Province Unsuccess.');
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    function check_province($Province_Code = NULL, $Country_Id = NULL) {// Check Province_Code Already.
        $result = FALSE;

        if (!empty($Province_Code) && !empty($Province_Code)):
            $r_province = $this->pro->get_CTL_M_Province(NULL, $Province_Code, $Country_Id, FALSE)->result();
            if (count($r_province) > 0):
                $result = TRUE;
            endif;
        endif;

        return $result;
    }

}
