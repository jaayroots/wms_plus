<?php

/*
 * Create by Ton! 20131204
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class position extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model("position_model", "pos");
        $this->load->model('menu_model', 'mnu');
        $this->load->model('authen_model', 'atn');

        $this->mnu_NavigationUri = "position";
    }

    public function index() {
        $this->position_list();
    }

    function position_list() {// Display List of Position.
        #### Permission Button. by Ton! 20140124 ####
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
             ONCLICK=\"openForm('position','position/position_processec/','A','')\">";
        endif;
        #### END Permission Button. ####

        $q_position = $this->pos->get_CTL_M_Position_List();
        $r_position = $q_position->result();

        $column = array("ID", "Position Code", "Position Name EN", "Position Name TH", "Position Desc", "Active");
//        $action = array(VIEW, EDIT, DEL);// Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_position, $r_position, $column, "position/position_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Position.'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('position','position/position_processec/','A','')\">"
            , 'button_add' => $button_add # Example. Permission Button. by Ton! 20140124
        ));
    }

    function position_processec() {// select processec (Add, Edit, Delete) Position.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") {// ADD.
            $Id = "";
            $this->position_management($mode, $Id);
        } elseif ($mode == "V" || $mode == "E") {// VIEW & EDIT.
            $Id = $data['id'];
            $this->position_management($mode, $Id);
        } elseif ($mode == "D") {// DELETE.
            $Id = $data['id'];
            $result_inactive = $this->position_set_inactive($Id);
            if ($result_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Delete data Position Success.')</script>";
                redirect('position', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Delete data Position Unsuccess. Please check?')</script>";
                redirect('position', 'refresh');
            endif;
        }
    }

    function position_set_inactive($Position_Id) {// Set Inactive Position.
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Pos['Position_Id'] = $Position_Id;

        $data_Pos['Active'] = FALSE;

        $data_Pos['Modified_Date'] = $human;
        $data_Pos['Modified_By'] = $this->session->userdata('user_id');
        $this->transaction_db->transaction_start();
        $result_inactive = $this->pos->save_CTL_M_Position('upd', $data_Pos, $where_Pos);
        if ($result_inactive === TRUE):
            $this->transaction_db->transaction_commit();
            return TRUE;
        else:
            log_message('error', 'save CTL_M_Position Unsuccess.');
            $this->transaction_db->transaction_rollback();
            return FALSE;
        endif;
    }

    function position_management($mode, $PositionId) {// Add & Edit Position.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        #### Permission Button. by Ton! 20140124 ####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        #### END Permission Button. by Ton! 20140124 ####

        $Position_Id = '';
        $Position_Code = '';
        $Position_NameEN = '';
        $Position_NameTH = '';
        $Position_Desc = '';
        $Active = TRUE; // Edit By Akkarapol, 23/01/2014, Set variable $Active = TRUE for set default checked in check_box

        if (!empty($PositionId)):
            $q_position = $this->pos->get_CTL_M_Position($PositionId, NULL, FALSE);
            $r_position = $q_position->result();
            if (count($r_position) > 0):
                foreach ($r_position as $value) :
                    $Position_Id = $PositionId;
                    $Position_Code = $value->Position_Code;
                    $Position_NameEN = $value->Position_NameEN;
                    $Position_NameTH = $value->Position_NameTH;
                    $Position_Desc = $value->Position_Desc;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        $this->load->helper('form');
        $str_form = form_fieldset('Position.');
        $str_form.=$this->parser->parse('form/position_master', array("mode" => $mode
            , "Position_Id" => $Position_Id, "Position_Code" => $Position_Code
            , "Position_NameEN" => $Position_NameEN, "Position_NameTH" => $Position_NameTH
            , "Position_Desc" => $Position_Desc, "Active" => $Active), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add/Edit Position.'
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

        # Check Position Code already exists.
        if ($data['Position_Code'] !== ""):
            if ($data['type'] === "E"):
                if ($data['Position_Code'] !== $data['Current_Code']):
                    $result_check = $this->check_position($data['Position_Code']);
                    if ($result_check === TRUE) :
                        $result['result'] = 0;
                        $result['note'] = "POS_CODE_ALREADY";
                        echo json_encode($result);
                        return;
                    endif;
                endif;
            elseif ($data['type'] === "A"):
                $result_check = $this->check_position($data['Position_Code']);
                if ($result_check === TRUE) :
                    $result['result'] = 0;
                    $result['note'] = "POS_CODE_ALREADY";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        echo json_encode($result);
        return;
    }

    function save_position() {
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Pos['Position_Id'] = ($data['Position_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Position_Id']);

        $data_Pos['Position_Code'] = ($data['Position_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Position_Code']);
        $data_Pos['Position_NameEN'] = ($data['Position_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Position_NameEN']);
        $data_Pos['Position_NameTH'] = ($data['Position_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Position_NameTH']);
        $data_Pos['Position_Desc'] = ($data['Position_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Position_Desc']);

        $Active = $this->input->post("Active");
        if ($Active == 'on'):
            $data_Pos['Active'] = TRUE;
        else:
            $data_Pos['Active'] = FALSE;
        endif;

        $this->transaction_db->transaction_start();
        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $data_Pos['Created_Date'] = $human;
                    $data_Pos['Created_By'] = $this->session->userdata('user_id');
                    $data_Pos['Modified_Date'] = $human;
                    $data_Pos['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->pos->save_CTL_M_Position('ist', $data_Pos);
                    if ($result > 0):
                        $result = TRUE;
                    else:
                        $result = FALSE;
                    endif;
                }break;
            case "E" : {
                    $data_Pos['Modified_Date'] = $human;
                    $data_Pos['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->pos->save_CTL_M_Position('upd', $data_Pos, $where_Pos);
                }break;
        endswitch;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            log_message('error', 'save CTL_M_Position Unsuccess.');
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    function check_position($Position_Code = NULL) {// Check Position_Code Already.
        $result = FALSE;

        if (!empty($Position_Code)):
            $r_position = $this->pos->get_CTL_M_Position(NULL, $Position_Code, FALSE)->result();
            if (count($r_position) > 0):
                $result = TRUE;
            endif;
        endif;

        return $result;
    }

}
