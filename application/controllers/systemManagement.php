<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class systemManagement extends CI_Controller {

    public $mnu_NavigationUri;

    function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->model("system_management_model", "sys");
        $this->load->model('authen_model', "atn");
        $this->load->model("menu_model", "mnu");
        $this->mnu_NavigationUri = "systemManagement";
    }

    public function index() {
        $this->systemList();
    }

    function systemList() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        $action = $action_parmission['action_button'];
        $query = $this->sys->getSystemAll();
        $system_list = $query->result();
        $column = array("Item", "Code", "Description", "Description (Thai)");
        $datatable = $this->datatable->genTableFixColumn($query, $system_list, $column, "systemManagement/systemForm", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('user_id')
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'menu_title' => 'List of System'
            , 'datatable' => $datatable
            , 'button_add' => ""
        ));
    }

    function showDetail() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $id = $this->input->get('id');
        $code = $this->sys->getDomCode($id);
        $name = $this->sys->getDomName($id);
        $query = $this->sys->getSystemDetail($code);
        $system_list = $query->result();
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        $action = $action_parmission['action_button'];

        $button_add = "";
        if (in_array("-2", $action_parmission)):
            $button_add = "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "' ONCLICK=\"openForm('user','systemManagement/showForm?id=&code=" . $code . "','A','')\">";
        endif;

        $column = array("Process", "Code", "Description Thai", "Description English");
        $datatable = $this->datatable->genTableFixColumn($query, $system_list, $column, "systemManagement/systemForm", $action);

        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('user_id')
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'menu_title' => 'List of System :  ' . $name . ' (' . $code . ')'
            , 'datatable' => $datatable
            , 'button_add' => $button_add
        ));
    }

    function systemForm() {
        $id = $this->input->post('id');
        $mode = $this->input->post('mode');

        if ($mode == "V"):
            redirect('/systemManagement/showDetail?id=' . $id, 'refresh');
        elseif ($mode == "E") :
            redirect('/systemManagement/showForm?id=' . $id, 'refresh');
        elseif ($mode == "D"):
            redirect('/systemManagement/deleteSystem?id=' . $id, 'refresh');
        else:
            redirect('/systemManagement/showForm?id=' . $id, 'refresh');
        endif;
    }

    function showForm() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        $action = $action_parmission['action_button'];
        $id = $this->input->get('id');
        if ($id != "") :
            $action = "E";
            $value = $this->sys->getSystemValue($id);
            $code = $this->sys->getDomHostCode($id);
        else:
            $action = "A";
            $code = $this->input->get('code');
            $value = array();
        endif;
        if ($code == "*") :
            $main_id = $id;
            $selectSystem = array("*");
            $flag_back_to_main = TRUE;
        else :
            $main_id = $this->sys->getMainId($code);
            $selectSystem = $this->sys->selectSystem();
            $flag_back_to_main = FALSE;
        endif;

        $params = array(
            "selectSystem" => $selectSystem,
            "code" => $code,
            "action" => $action,
            "values" => $value,
            "main_id" => $main_id,
            "flag_back_to_main" => $flag_back_to_main
        );

        $str_form = $this->parser->parse('form/system_management_master', array("data" => $params), TRUE);

        if ($id != "") :
            $title = "Edit system";
        else :
            $title = "Add system";
        endif;

        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('user_id')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'List of System : ' . $title
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'button_back' => ''
            , 'button_clear' => ''
            , 'button_save' => ''
        ));
    }

    function saveSystem() {
        $this->transaction_db->transaction_start();
        $data = $this->input->post();
        if ($data['do_action'] == "A") :
            $result = $this->sys->save_system_code($data);
            if ($result === TRUE):
                $this->transaction_db->transaction_commit();
            else:
                log_message('error', 'save SYS_M_Domain Unsuccess.');
                $this->transaction_db->transaction_rollback();
            endif;
            redirect('/systemManagement/showDetail?id=' . $data['main_id'], 'refresh');
        elseif ($data['do_action'] == "E" && $data['edit_id'] != "") :
            $result = $this->sys->edit_system_code($data);
            if ($result === TRUE):
                $this->transaction_db->transaction_commit();
            else:
                log_message('error', 'save SYS_M_Domain Unsuccess.');
                $this->transaction_db->transaction_rollback();
            endif;
            redirect('/systemManagement/showDetail?id=' . $data['main_id'], 'refresh');
        else:
        endif;
    }

    function deleteSystem() {
        $id = $this->input->get('id');
        $result = $this->sys->delete_system_code($id);
        if ($result !== FALSE) :
            $this->transaction_db->transaction_commit();
            redirect('/systemManagement/showDetail?id=' . $result, 'refresh');
        else:
            log_message('error', 'delete SYS_M_Domain Unsuccess.');
            $this->transaction_db->transaction_rollback();
            redirect('/systemManagement', 'refresh');
        endif;
    }

}
