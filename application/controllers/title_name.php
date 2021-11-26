<?php

/*
 * Create by Ton! 20131206
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class title_name extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");
        $this->load->model("title_name_model", "ttn");

        $this->mnu_NavigationUri = "title_name";
    }

    public function index() {
        $this->title_name_list();
    }

    function title_name_list() {// Display List of Title Name.
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
                ONCLICK=\"openForm('position','title_name/title_name_processec/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_title_name = $this->ttn->get_CTL_M_TitleName_List();
        $r_title_name = $q_title_name->result();

        $column = array("ID", "TitleName Code", "TitleName Name EN", "TitleName Name TH", "TitleName Desc", "Active");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_title_name, $r_title_name, $column, "title_name/title_name_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of TitleName.'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('position','title_name/title_name_processec/','A','')\">"
            , 'button_add' => $button_add
        ));
    }

    function title_name_processec() {// select processec (Add, Edit, Delete) TitleName.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") {// ADD.
            $Id = "";
            $this->title_name_management($mode, $Id);
        } elseif ($mode == "V" || $mode == "E") {// VIEW & EDIT.
            $Id = $data['id'];
            $this->title_name_management($mode, $Id);
        } elseif ($mode == "D") {// DELETE.
            $Id = $data['id'];
            $result_inactive = $this->title_name_set_inactive($Id);
            if ($result_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Delete data Title Name Success.')</script>";
                redirect('title_name', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Delete data Title Name Unsuccess. Please check?')</script>";
                redirect('title_name', 'refresh');
            endif;
        }
    }

    function title_name_set_inactive($TitleName_Id) {// Set Inactive TitleName.
        $datestring = "%Y-%m-%d %h:%i:%s";
        $time = time();
        $this->load->helper("date");
        $human = mdate($datestring, $time);

        $where_Title['TitleName_Id'] = $TitleName_Id;

        $data_Title['Active'] = 0;

        $data_Title['Modified_Date'] = $human;
        $data_Title['Modified_By'] = $this->session->userdata('user_id');
        $this->transaction_db->transaction_start();
        $result_inactive = $this->ttn->save_CTL_M_TitleName('upd', $data_Title, $where_Title);
        if ($result_inactive === TRUE):
            $this->transaction_db->transaction_commit();
            return TRUE;
        else:
            log_message('error', 'Inactive TitleName_Id = ' . $TitleName_Id . ' Unsuccess.');
            $this->transaction_db->transaction_rollback();
            return FALSE;
        endif;
    }

    function title_name_management($mode, $TitleNameId) {// Add & Edit Position.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $TitleName_Id = '';
        $TitleName_Code = '';
        $TitleName_EN = '';
        $TitleName_TH = '';
        $TitleName_Desc = '';
        $Active = TRUE; // Edit By Akkarapol, 23/01/2014, Set variable $Active = TRUE for set default checked in check_box
        if (!empty($TitleNameId)):
            $q_title_name = $this->ttn->get_CTL_M_TitleName($TitleNameId, NULL, NULL, FALSE);
            $r_title_name = $q_title_name->result();
            if (count($r_title_name) > 0):
                foreach ($r_title_name as $value) :
                    $TitleName_Id = $TitleNameId;
                    $TitleName_Code = $value->TitleName_Code;
                    $TitleName_EN = $value->TitleName_EN;
                    $TitleName_TH = $value->TitleName_TH;
                    $TitleName_Desc = $value->TitleName_Desc;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        $this->load->helper('form');
        $str_form = form_fieldset('TitleName.');
        $str_form.=$this->parser->parse('form/title_name_master', array("mode" => $mode
            , "TitleName_Id" => $TitleName_Id, "TitleName_Code" => $TitleName_Code
            , "TitleName_EN" => $TitleName_EN, "TitleName_TH" => $TitleName_TH
            , "TitleName_Desc" => $TitleName_Desc, "Active" => $Active), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add/Edit TitleName.'
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

        # Check TitleName Code already exists.
        if ($data['TitleName_Code'] !== ""):
            if ($data['type'] === "E"):
                if ($data['Current_Code'] !== $data['TitleName_Code']):
                    $result_check = $this->check_title_name($data['TitleName_Code']);
                    if ($result_check === TRUE) :
                        $result['result'] = 0;
                        $result['note'] = "TITLE_CODE_ALREADY";
                        echo json_encode($result);
                        return;
                    endif;
                endif;
            elseif ($data['type'] === "A"):
                $result_check = $this->check_title_name($data['TitleName_Code']);
                if ($result_check === TRUE) :
                    $result['result'] = 0;
                    $result['note'] = "TITLE_CODE_ALREADY";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        echo json_encode($result);
        return;
    }

    function save_title_name() {
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Dep['TitleName_Id'] = ($data['TitleName_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['TitleName_Id']);

        $data_Dep['TitleName_Code'] = ($data['TitleName_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['TitleName_Code']);
        $data_Dep['TitleName_EN'] = ($data['TitleName_EN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['TitleName_EN']);
        $data_Dep['TitleName_TH'] = ($data['TitleName_TH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['TitleName_TH']);
        $data_Dep['TitleName_Desc'] = ($data['TitleName_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['TitleName_Desc']);

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
                    $result = $this->ttn->save_CTL_M_TitleName('ist', $data_Dep);
                    if ($result > 0):
                        $result = TRUE;
                    else:
                        $result = FALSE;
                    endif;
                }break;
            case "E" : {
                    $data_Dep['Modified_Date'] = $human;
                    $data_Dep['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->ttn->save_CTL_M_TitleName('upd', $data_Dep, $where_Dep);
                }break;
        endswitch;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            log_message('error', 'save CTL_M_TitleName Unsuccess.');
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    function check_title_name($TitleName_Code = NULL) {// Check TitleName_Code Already.
        $result = FALSE;

        if (!empty($TitleName_Code)):
            $r_title_name = $this->ttn->get_CTL_M_TitleName(NULL, $TitleName_Code, NULL, FALSE)->result();
            if (count($r_title_name) > 0):
                $result = TRUE;
            endif;
        endif;

        return $result;
    }

}
