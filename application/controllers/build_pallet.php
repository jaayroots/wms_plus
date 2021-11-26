<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of counting
 *
 * @author Sureerat
 */
class build_pallet extends CI_Controller {

    //put your code here
    public function __construct() {
        parent::__construct();
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->helper('form');
        $this->load->model("pallet_model", "pallet");
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("workflow_model", "wf");
        $this->load->model("stock_model", "stock");
        $this->load->model("system_management_model", "sys");
    }

    public function index() {
        //echo ' hello ';
        $this->job_list();
    }

    function job_list() {
        // show order state putaway

        $query = $this->pallet->show_putaway();
        $putaway_list = $query->result();

        $column = array("Running No.", "Document No.", "Build Pallet");

        $parameter['column'] = $column;
        $parameter['data'] = $putaway_list;
        $str_form = $this->parser->parse("form/build_pallet_job", $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW 
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Build Pallet : List Job'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
			 ONCLICK=\"location.href='build_pallet/openForm'\">"
            , 'button_cancel' => ''
            , 'button_action' => ''
        ));
    }

    function show_job() {
        // show order state putaway
        $pallet_id = $this->input->get('pallet_id');
        $query = $this->pallet->show_putaway();
        $putaway_list = $query->result();

        $column = array("Running No.", "Document No.", "Build Pallet");

        $parameter['column'] = $column;
        $parameter['data'] = $putaway_list;
        $parameter['pallet_id'] = $pallet_id;

        $str_form = $this->parser->parse("form/build_pallet_job", $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW 
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Build Pallet : List Job'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_add' => ""
            , 'button_cancel' => ""
            , 'button_action' => ""
        ));
    }

    function pallet_list() {
        $doc_no = $this->input->get('doc_no');
        $data = $this->pallet->show_pallet();
        $column = array("Running No.", "Pallet Code", "Pallet Name", "Edit", "Build Pallet");

        $parameter['column'] = $column;
        $parameter['data'] = $data;
        $parameter['doc_no'] = $doc_no;
        $str_form = $this->parser->parse("form/build_pallet", $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW 
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Build Pallet'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
			 ONCLICK=\"location.href='" . site_url() . "/build_pallet/openForm'\">"
            , 'button_cancel' => ''
            , 'button_action' => ''
        ));
    }

    function openForm() {
        //$parameter['column']=$column;
        #Get receive type list
        $pallet_id = $this->input->get("pallet_id");
        //echo ' pallet_id='.$pallet_id;
        $parameter['pallet_type'] = '';
        if ($pallet_id != "") {
            $data = $this->pallet->get_pallet_detail($pallet_id);

            $parameter['pallet_code'] = $data[0]->Pallet_Code;
            $parameter['pallet_type'] = $data[0]->Pallet_Type;
            $parameter['pallet_name'] = $data[0]->Pallet_Name;
            $parameter['pallet_width'] = $data[0]->Pallet_Width;
            $parameter['pallet_lenght'] = $data[0]->Pallet_Lenght;
            $parameter['pallet_height'] = $data[0]->Pallet_Height;
            $parameter['min_load'] = $data[0]->Min_Load;
            $parameter['max_load'] = $data[0]->Max_Load;
            $parameter['capacity'] = $data[0]->Capacity_Max;
            $parameter['weight'] = $data[0]->Weight_Max;
            $parameter['pallet_id'] = $data[0]->Pallet_Id;
        }
        //p($data);
        $pallet_q = $this->sys->getSystemDetail("PALLET_TYPE");
        $pallet_r = $pallet_q->result();
        $pallet_list = genOptionDropdown($pallet_r, "SYS");
        $parameter['pallet_list'] = $pallet_list;
        //p($pallet_list);

        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/build_pallet_new", $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW 
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Build Pallet : New Pallet'
//            , 'menu' => $this->menu->loadMenu()
            , 'menu' => $this->menu->loadMenuAuth()// Edit by Ton! 20131111
//            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'button_action' => '<INPUT TYPE="button" name="add" id="add" class="' . $this->config->item('css_button') . '"	VALUE="' . SAVE . '" >'
        ));
    }

    function action() {
        $input = $this->input->post();
        if ($input['pallet_id'] == "") {
            $status = $this->pallet->save_pallet($input);
        } else {
            $status = $this->pallet->update_pallet($input);
        }
        $json['status'] = $status[1];
        $json['id'] = $status[0];
        $json['error_msg'] = "";
        echo json_encode($json);
    }

    function openjob() {
        $pallet_id = $this->input->get("pallet_id");
        $doc_no = $this->input->get("doc_no");
        //echo ' pallet = '.$pallet_id;
        $parameter['pallet_id'] = $pallet_id;
        $parameter['doc_no'] = $doc_no;
        $parameter['oinfo'] = $this->pallet->get_order_info($doc_no);
        $parameter['pinfo'] = $this->pallet->get_pallet_detail($pallet_id);
        $order_detail = $this->stock->getOrderDetail($doc_no);
        $parameter['order_detail'] = $order_detail;
        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/build_pallet_openjob", $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW 
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Build Pallet : '
//            , 'menu' => $this->menu->loadMenu()
            , 'menu' => $this->menu->loadMenuAuth()// Edit by Ton! 20131111
//            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'button_action' => '<INPUT TYPE="button" name="add" id="add" class="' . $this->config->item('css_button') . '"	VALUE="' . SAVE . '" >
			 <INPUT TYPE="button" name="confirm" id="confirm" class="' . $this->config->item('css_button') . '"	VALUE="Confirm/ Full" >
			 '
        ));
    }

}