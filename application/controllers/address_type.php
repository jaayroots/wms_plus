<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class address_type extends CI_Controller {

    public $mnu_NavigationUri;

    function __construct() {
        parent::__construct();
        $this->load->model('authen_model', 'atn');
        $this->load->model('menu_model', 'mnu');
        $this->load->model("address_type_model", "add");

        $this->mnu_NavigationUri = "address_type";
    }

    
    /**
     * Default 
     * 
     */
    public function index() {

        $this->output->enable_profiler($this->config->item('set_debug'));

        /**
         * Permission Button. by Ton! 20140130 #####
         * View = -1
         * Add = -2
         * Edit = -3
         * Delete = -4
         */
        
        $action_permission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        if (isset($action_permission['action_button'])):
            $action = $action_permission['action_button'];
        else:
            $action = array();
        endif;

        $button_add = "";

        if (in_array("-2", $action_permission)):
            $button_add = "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "' ONCLICK=\"openForm('position','address_type/address_type_processec/','A','')\">";
        endif;

        $q_address_type = $this->add->get_CTL_M_AddressType_List();
        $r_address_type = $q_address_type->result();

        $column = array("ID", "AddressType Code", "AddressType Name EN", "AddressType Name TH", "AddressType Desc", "Active");

        $datatable = $this->datatable->genTableFixColumn($q_address_type, $r_address_type, $column, "address_type/address_type_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'menu_title' => 'List of Address Type.'
            , 'datatable' => $datatable
            , 'button_add' => $button_add
        ));
    }

    function address_type_processec() {
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") {
            $Id = "";
            $this->address_type_management($mode, $Id);
        } elseif ($mode == "V" || $mode == "E") {
            $Id = $data['id'];
            $this->address_type_management($mode, $Id);
        } elseif ($mode == "D") {
            $Id = $data['id'];
            $result_inactive = $this->address_type_set_inactive($Id);
            if ($result_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Delete data Address Type Success.')</script>";
                redirect('address_type', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Delete data Address Type Unsuccess. Please check?')</script>";
                redirect('address_type', 'refresh');
            endif;
        }
    }

    function address_type_set_inactive($AddressType_Id) {

        if ($AddressType_Id !== NULL):
            $this->load->helper("date");
            $human = mdate("%Y-%m-%d %h:%i:%s", time());
            $where_Cou['AddressType_Id'] = $AddressType_Id;
            $data_Cou['Active'] = FALSE;
            $data_Cou['Modified_Date'] = $human;
            $data_Cou['Modified_By'] = $this->session->userdata('user_id');
            $this->transaction_db->transaction_start();
            $result_inactive = $this->add->save_CTL_M_AddressType('upd', $data_Cou, $where_Cou);
            if ($result_inactive === TRUE):
                $this->transaction_db->transaction_commit();
                return TRUE;
            else:
                log_message('error', 'save CTL_M_AddressType Unsuccess.');
                $this->transaction_db->transaction_rollback();
                return FALSE;
            endif;
        else:
            log_message('error', 'save CTL_M_AddressType Unsuccess. AddressType_Id NULL');
            return FALSE;
        endif;
    }

    function address_type_management($mode, $AddressTypeId) {

        $this->output->enable_profiler($this->config->item('set_debug'));

        /**
         * Permission Button. by Ton! 20140130 #####
         * View = -1
         * Add = -2
         * Edit = -3
         * Delete = -4
         */
        $action_permission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_permission) || in_array("-3", $action_permission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;

        $AddressType_Id = '';
        $AddressType_Code = '';
        $AddressType_NameEN = '';
        $AddressType_NameTH = '';
        $AddressType_Desc = '';
        $Active = TRUE;

        if (!empty($AddressTypeId)):
            $q_address_type = $this->add->get_CTL_M_AddressType($AddressTypeId, NULL, FALSE);
            $r_address_type = $q_address_type->result();
            if (count($r_address_type) > 0):
                foreach ($r_address_type as $value) :
                    $AddressType_Id = $AddressTypeId;
                    $AddressType_Code = $value->AddressType_Code;
                    $AddressType_NameEN = $value->AddressType_NameEN;
                    $AddressType_NameTH = $value->AddressType_NameTH;
                    $AddressType_Desc = $value->AddressType_Desc;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        $this->load->helper('form');
        $str_form = form_fieldset('AddressType.');
        $str_form.=$this->parser->parse('form/address_type_master', array("mode" => $mode
            , "AddressType_Id" => $AddressType_Id, "AddressType_Code" => $AddressType_Code
            , "AddressType_NameEN" => $AddressType_NameEN, "AddressType_NameTH" => $AddressType_NameTH
            , "AddressType_Desc" => $AddressType_Desc, "Active" => $Active), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add/Edit Address Type.'
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
            , 'button_save' => $button_save
        ));
    }

    function validation() {
        $data = $this->input->post();

        $result = array();
        $result['result'] = 1;
        $result['note'] = NULL;

        if ($data['AddressType_Code'] !== ""):
            if ($data['type'] === "E"):
                if ($data['Current_Code'] !== $data['AddressType_Code']):
                    $result_check = $this->check_address_type($data['AddressType_Code']);
                    if ($result_check === TRUE) :
                        $result['result'] = 0;
                        $result['note'] = "ADD_CODE_ALREADY";
                        echo json_encode($result);
                        return;
                    endif;
                endif;
            elseif ($data['type'] === "A"):
                $result_check = $this->check_address_type($data['AddressType_Code']);
                if ($result_check === TRUE) :
                    $result['result'] = 0;
                    $result['note'] = "ADD_CODE_ALREADY";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        echo json_encode($result);
        return;
    }

    function save_address_type() {
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Add['AddressType_Id'] = ($data['AddressType_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['AddressType_Id']);

        $data_Add['AddressType_Code'] = ($data['AddressType_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['AddressType_Code']);
        $data_Add['AddressType_NameEN'] = ($data['AddressType_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['AddressType_NameEN']);
        $data_Add['AddressType_NameTH'] = ($data['AddressType_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['AddressType_NameTH']);
        $data_Add['AddressType_Desc'] = ($data['AddressType_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['AddressType_Desc']);

        $Active = $this->input->post("Active");
        if ($Active == 'on'):
            $data_Add['Active'] = TRUE;
        else:
            $data_Add['Active'] = FALSE;
        endif;

        $this->transaction_db->transaction_start();
        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $data_Add['Created_Date'] = $human;
                    $data_Add['Created_By'] = $this->session->userdata('user_id');
                    $data_Add['Modified_Date'] = $human;
                    $data_Add['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->add->save_CTL_M_AddressType('ist', $data_Add);
                    if ($result > 0):
                        $result = TRUE;
                    else:
                        $result = FALSE;
                    endif;
                }break;
            case "E" : {
                    $data_Add['Modified_Date'] = $human;
                    $data_Add['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->add->save_CTL_M_AddressType('upd', $data_Add, $where_Add);
                }break;
        endswitch;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    function check_address_type($AddressType_Code = NULL) {
        $result = FALSE;
        if (!empty($result)):
            $r_address_type = $this->add->get_CTL_M_AddressType(NULL, $AddressType_Code, FALSE)->result();
            if (count($r_address_type) > 0):
                $result = TRUE;
            endif;
        endif;

        return $result;
    }

}
