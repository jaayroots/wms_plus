<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class reprint_documents extends CI_Controller {

    public $mnu_NavigationUri;

    function __construct() {
        parent::__construct();
        $this->load->model("menu_model", "mnu");
        $this->mnu_NavigationUri = "reprint_documents";
    }

    public function index() {
        
        $this->output->enable_profiler($this->config->item('set_debug'));

        $documents_list = array();
        $documents_list[""] = "::: Please Select ::: ";

//        $dataCheck = $this->mnu->check_module_reprint_doc();
//        if (!empty($dataCheck)):
//            foreach ($dataCheck as $value) :
//                if ($value->IsReceive !== 0):
//                    $documents_list["rc"] = "Receive Approve Report";
//                endif;
//                if ($value->IsPutaway !== 0):
//                    $documents_list["pa"] = "Putaway Job";
//                endif;
//                if ($value->IsPicking !== 0):
//                    $documents_list["pk"] = "Picking Job";
//                endif;
//                if ($value->IsDispatch !== 0):
//                    $documents_list["dp"] = "Dispatch Approve Report";
//                endif;
//            endforeach;
//        endif;

        $documents_list["BARCODE_BY_DOC"] = "Print Barcode by Document";
        $documents_list["BARCODE_BY_ITEM"] = "Print Barcode by Item";
        $parameter["documents_list"] = $documents_list;

        $conf = $this->config->item('_xml');
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $parameter["conf_pallet"] = $conf_pallet;

        $this->load->helper('form');
        $str_form = form_fieldset('Select Documents.');
        $str_form.=$this->parser->parse('form/reprint_documents_form', array("parameter" => $parameter), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Reprint Documents.'
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" id="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" id="btn_clear">'
            , 'button_save' => '<INPUT TYPE="button" class="button dark_blue" VALUE="Print" id="btn_print">'
        ));
    }

}
