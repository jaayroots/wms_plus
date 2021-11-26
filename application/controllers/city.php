<?php

/*
 * Create by Ton! 20131206
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class city extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model("city_model", "city");
        $this->load->model("province_model", "pro");
        $this->load->model('menu_model', 'mnu');
        $this->load->model('authen_model', 'atn');

        $this->mnu_NavigationUri = "city";
    }

    public function index() {
        $this->city_list();
    }

    function city_list() {// Display List of City.
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
             ONCLICK=\"openForm('position','city/city_processec/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_city = $this->city->get_CTL_M_City_List();
        $r_city = $q_city->result();

        $column = array("ID", "City Code", "City Name EN", "City Name TH", "City Desc", "Province", "Active");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_city, $r_city, $column, "city/city_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of City.'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('position','city/city_processec/','A','')\">"
            , 'button_add' => $button_add # Permission Button. by Ton! 20140130
        ));
    }

    function city_processec() {// select processec (Add, Edit, Delete) City.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") {// ADD.
            $Id = "";
            $this->city_management($mode, $Id);
        } elseif ($mode == "V" || $mode == "E") {// VIEW & EDIT.
            $Id = $data['id'];
            $this->city_management($mode, $Id);
        } elseif ($mode == "D") {// DELETE.
            $Id = $data['id'];
            $result_inactive = $this->city_set_inactive($Id);
            if ($result_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Delete data City Success.')</script>";
                redirect('city', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Delete data City Unsuccess. Please check?')</script>";
                redirect('city', 'refresh');
            endif;
        }
    }

    function city_set_inactive($City_Id) {// Set Inactive City.
        if ($City_Id !== NULL):
            $this->load->helper("date");
            $human = mdate("%Y-%m-%d %h:%i:%s", time());

            $where_Cou['City_Id'] = $City_Id;

            $data_Cou['Active'] = 0;

            $data_Cou['Modified_Date'] = $human;
            $data_Cou['Modified_By'] = $this->session->userdata('user_id');
            $this->transaction_db->transaction_start();
            $result_inactive = $this->city->save_CTL_M_City('upd', $data_Cou, $where_Cou);
            if ($result_inactive === TRUE):
                $this->transaction_db->transaction_commit();
                return TRUE;
            else:
                log_message('error', 'save CTL_M_City Unsuccess.');
                $this->transaction_db->transaction_rollback();
                return FALSE;
            endif;
        else:
            log_message('error', 'save CTL_M_City Unsuccess. City_Id IS NULL.');
            return FALSE;
        endif;
    }

    function city_management($mode, $CityId) {// Add & Edit Position.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $City_Id = '';
        $City_Code = '';
        $Province_Id = '';
        $City_NameEN = '';
        $City_NameTH = '';
        $City_Desc = '';
        $Active = TRUE; // Edit By Akkarapol, 23/01/2014, Set variable $Active = TRUE for set default checked in check_box

        if (!empty($CityId)):
            $q_city = $this->city->get_CTL_M_City($CityId, NULL, NULL, FALSE);
            $r_city = $q_city->result();
            if (count($r_city) > 0):
                foreach ($r_city as $value) :
                    $City_Id = $CityId;
                    $City_Code = $value->City_Code;
                    $Province_Id = $value->Province_Id;
                    $City_NameEN = $value->City_NameEN;
                    $City_NameTH = $value->City_NameTH;
                    $City_Desc = $value->City_Desc;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        // get all Province for dispaly dropdown list.
        $q_province_list = $this->pro->get_CTL_M_Province(NULL, NULL, NULL, TRUE);
        $r_province_list = $q_province_list->result();
        $optionProvince = genOptionDropdown($r_province_list, "PROVINCE");

        $this->load->helper('form');
        $str_form = form_fieldset('City.');
        $str_form.=$this->parser->parse('form/city_master', array("mode" => $mode
            , "City_Id" => $City_Id, "City_Code" => $City_Code, "Province_Id" => $Province_Id
            , "City_NameEN" => $City_NameEN, "City_NameTH" => $City_NameTH
            , "City_Desc" => $City_Desc, "Active" => $Active, "optionProvince" => $optionProvince), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add/Edit City.'
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

        # Check City Code already exists.
        if ($data['City_Code'] !== ""):
            if ($data['type'] === "E"):
                if ($data['Current_Code'] !== $data['City_Code']):
                    $result_check = $this->check_city($data['City_Code'], $data['Province_Id']);
                    if ($result_check === TRUE) :
                        $result['result'] = 0;
                        $result['note'] = "CITY_CODE_ALREADY";
                        echo json_encode($result);
                        return;
                    endif;
                endif;
            elseif ($data['type'] === "A"):
                $result_check = $this->check_city($data['City_Code'], $data['Province_Id']);
                if ($result_check === TRUE) :
                    $result['result'] = 0;
                    $result['note'] = "CITY_CODE_ALREADY";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        echo json_encode($result);
        return;
    }

    function save_city() {
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_City['City_Id'] = ($data['City_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['City_Id']);

        $data_City['City_Code'] = ($data['City_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['City_Code']);
        $data_City['Province_Id'] = ($data['Province_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Province_Id']);
        $data_City['City_NameEN'] = ($data['City_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['City_NameEN']);
        $data_City['City_NameTH'] = ($data['City_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['City_NameTH']);
        $data_City['City_Desc'] = ($data['City_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['City_Desc']);

        $Active = $this->input->post("Active");
        if ($Active == 'on'):
            $data_City['Active'] = TRUE;
        else:
            $data_City['Active'] = FALSE;
        endif;

        $this->transaction_db->transaction_start();
        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $data_City['Created_Date'] = $human;
                    $data_City['Created_By'] = $this->session->userdata('user_id');
                    $data_City['Modified_Date'] = $human;
                    $data_City['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->city->save_CTL_M_City('ist', $data_City);
                    if ($result > 0):
                        $result = TRUE;
                    endif;
                }break;
            case "E" : {
                    $data_City['Modified_Date'] = $human;
                    $data_City['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->city->save_CTL_M_City('upd', $data_City, $where_City);
                }break;
        endswitch;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            log_message('error', 'save CTL_M_City Unsuccess.');
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    function check_city($City_Code = NULL, $Province_Id = NULL) {// Check City_Code Already.
        $result = FALSE;

        if (!empty($City_Code) && !empty($Province_Id)):
            $r_city = $this->city->get_CTL_M_City(NULL, $City_Code, $Province_Id, FALSE)->result();
            if (count($r_city) > 0):
                $result = TRUE;
            endif;
        endif;

        return $result;
    }

}
