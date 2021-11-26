<?php

/*
 * Create by Ton! 20131204
 */
?>

<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class country extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model('menu_model', 'mnu');
        $this->load->model('authen_model', 'atn');
        $this->load->model("country_model", "cou");

        $this->mnu_NavigationUri = "country";
    }

    public function index() {
        $this->country_list();
    }

    function country_list() {// Display List of Country.
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
             ONCLICK=\"openForm('position','country/country_processec/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_country = $this->cou->get_CTL_M_Country_List();
        $r_country = $q_country->result();

        $column = array("ID", "Country Code", "Country Name EN", "Country Name TH", "Country Desc", "Active");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_country, $r_country, $column, "country/country_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Country.'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('position','country/country_processec/','A','')\">"
            , 'button_add' => $button_add # Permission Button. by Ton! 20140130
        ));
    }

    function country_processec() {// select processec (Add, Edit, Delete) Country.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") :// ADD.
            $Id = "";
            $this->country_management($mode, $Id);
        elseif ($mode == "V" || $mode == "E") :// VIEW & EDIT.
            $Id = $data['id'];
            $this->country_management($mode, $Id);
        elseif ($mode == "D") :// DELETE.
            $Id = $data['id'];
            $result_inactive = $this->country_set_inactive($Id);
            if ($result_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Delete data Country Success.')</script>";
                redirect('country', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Delete data Country Unsuccess. Please check?')</script>";
                redirect('country', 'refresh');
            endif;
        endif;
    }

    function country_set_inactive($Country_Id) {// Set Inactive Country.
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Cou['Country_Id'] = $Country_Id;

        $data_Cou['Active'] = 0;

        $data_Cou['Modified_Date'] = $human;
        $data_Cou['Modified_By'] = $this->session->userdata('user_id');
        $this->transaction_db->transaction_start();
        $result_inactive = $this->cou->save_CTL_M_Country('upd', $data_Cou, $where_Cou);
        if ($result_inactive === TRUE):
            $this->transaction_db->transaction_commit();
            return TRUE;
        else:
            log_message('error', 'save CTL_M_Country Unsuccess.');
            $this->transaction_db->transaction_rollback();
            return FALSE;
        endif;
    }

    function country_management($mode, $CountryId) {// Add & Edit Position.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $Country_Id = '';
        $Country_Code = '';
        $Country_NameEN = '';
        $Country_NameTH = '';
        $Country_Desc = '';
        $Active = TRUE; // Edit By Akkarapol, 23/01/2014, Set variable $Active = TRUE for set default checked in check_box

        if (!empty($CountryId)):
            $q_country = $this->cou->get_CTL_M_Country($CountryId, NULL, FALSE);
            $r_country = $q_country->result();
            if (count($r_country) > 0):
                foreach ($r_country as $value) :
                    $Country_Id = $CountryId;
                    $Country_Code = $value->Country_Code;
                    $Country_NameEN = $value->Country_NameEN;
                    $Country_NameTH = $value->Country_NameTH;
                    $Country_Desc = $value->Country_Desc;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        $this->load->helper('form');
        $str_form = form_fieldset('Country.');
        $str_form.=$this->parser->parse('form/country_master', array("mode" => $mode
            , "Country_Id" => $Country_Id, "Country_Code" => $Country_Code
            , "Country_NameEN" => $Country_NameEN, "Country_NameTH" => $Country_NameTH
            , "Country_Desc" => $Country_Desc, "Active" => $Active), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add/Edit Country.'
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

        # Check Country Code already exists.
        if ($data['Country_Code'] !== ""):
            if ($data['type'] === "E"):
                if ($data['Country_Code'] !== $data['Current_Code']):
                    $result_check = $this->check_country($data['Country_Code']);
                    if ($result_check === TRUE) :
                        $result['result'] = 0;
                        $result['note'] = "COU_CODE_ALREADY";
                        echo json_encode($result);
                        return;
                    endif;
                endif;
            elseif ($data['type'] === "A"):
                $result_check = $this->check_country($data['Country_Code']);
                if ($result_check === TRUE) :
                    $result['result'] = 0;
                    $result['note'] = "COU_CODE_ALREADY";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        echo json_encode($result);
        return;
    }

    function save_country() {
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Cou['Country_Id'] = ($data['Country_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Country_Id']);

        $data_Cou['Country_Code'] = ($data['Country_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Country_Code']);
        $data_Cou['Country_NameEN'] = ($data['Country_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Country_NameEN']);
        $data_Cou['Country_NameTH'] = ($data['Country_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Country_NameTH']);
        $data_Cou['Country_Desc'] = ($data['Country_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Country_Desc']);

        $Active = $this->input->post("Active");
        if ($Active == 'on'):
            $data_Cou['Active'] = TRUE;
        else:
            $data_Cou['Active'] = FALSE;
        endif;

        $this->transaction_db->transaction_start();
        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $data_Cou['Created_Date'] = $human;
                    $data_Cou['Created_By'] = $this->session->userdata('user_id');
                    $data_Cou['Modified_Date'] = $human;
                    $data_Cou['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->cou->save_CTL_M_Country('ist', $data_Cou);
                    if ($result > 0):
                        $result = TRUE;
                    endif;
                }break;
            case "E" : {
                    $data_Cou['Modified_Date'] = $human;
                    $data_Cou['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->cou->save_CTL_M_Country('upd', $data_Cou, $where_Cou);
                }break;
        endswitch;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            log_message('error', 'save CTL_M_Country Unsuccess.');
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    function check_country($Country_Code = NULL) {// Check Country_Code Already.
        $result = FALSE;

        if (!empty($Country_Code)):
            $r_country = $this->cou->get_CTL_M_Country(NULL, $Country_Code, FALSE)->result();
            if (count($r_country) > 0):
                $result = TRUE;
            endif;
        endif;

        return $result;
    }

}
