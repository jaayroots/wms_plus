<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Return_document extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->settings = native_session::retrieve();
        $this->load->helper('form');
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("return_model", "return");
    }

    public function index() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $parameter = array();
        $parameter['renter_id'] = $this->config->item('renter_id');
        $str_form = $this->parser->parse('form/return_document', array("data" => $parameter), TRUE);
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Return Document'
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
        $response = $this->return->search_dispatch_document($params);
        $result = array();
        foreach ($response as $i => $v) {
            $v->Product_NameEN = tis620_to_utf8($v->Product_NameEN);
            $v->Product_Lot = tis620_to_utf8($v->Product_Lot);
            $v->Product_Serial = tis620_to_utf8($v->Product_Serial);
            $result[] = $v;
        }
        die(json_encode($result));
    }

    public function process_return_document() {
        $params = $this->input->post();
        $response = $this->return->return_dispatch_document($params);
        die(json_encode($response));
    }

}
