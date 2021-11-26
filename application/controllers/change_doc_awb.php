<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class change_doc_awb extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->settings = native_session::retrieve();
        $this->load->helper('form');
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("changdocawb_model", "model");
    }

    public function index() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $parameter = array();
        $parameter['renter_id'] = $this->config->item('renter_id');
        $str_form = $this->parser->parse('form/formchange_doc_awb', array("data" => $parameter), TRUE);
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Change DocumentAWB'
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm"></i>'
            , 'button_back' => ''
            , 'button_cancel' => ''
            , 'button_action' => ''
        ));
    }

    public function get_document() {
        $params = $this->input->post();
        $data = $params['fdate'];
        if(empty($data)){
        die(json_encode("Please click search")); 
        }else{
        $response = $this->model->search_document($data);
        }
        // p($response); exit;
        $result = array();
        foreach ($response as $i => $v) {
            $v->Document_No = tis620_to_utf8($v->Document_No);
            $v->Doc_Refer_Ext = tis620_to_utf8($v->Doc_Refer_Ext);
            $v->Doc_Refer_AWB = tis620_to_utf8($v->Doc_Refer_AWB);
            $result[] = $v;
        }
        //  p($result); exit;
        $view['data'] = $result;
        $view['search'] = $data;
        $this->load->view("report/change_doc_awb.php", $view);
 
    }


    
    function editDataTable($response){
        $data = $response;
        $editedValue = $_REQUEST['value'];
        echo iconv("UTF-8", "TIS-620", $editedValue);
        return $editedValue;

    }
    public function edit_documentAWB() {
        $params = $this->input->post();
        foreach ($params['data'] as $key => $value) {
            $order_id = $value[0];
            $Doc_Refer_AWB = $value[3];
        }

        $result = $this->model->updatedocumentAWB($order_id,$Doc_Refer_AWB);
        echo json_encode($result);
        return $result;
        die(json_encode($response));
    }

}
