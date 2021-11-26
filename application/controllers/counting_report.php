<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
# Author : Sureerat

class counting_report extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->helper('form');
        $this->load->model("report_model", "r");
        $this->load->model("counting_model", "counting");
        $this->load->model("encoding_conversion", "conv");
        //$this->config->set_item('renter_id', '1');  // Fix for TV DIRECT
        //$this->config->set_item('owner_id', '2');  // Fix for JWD
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));        
    }

    public function index() {
        $this->counting_form();
    }

    function counting_form() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        
        $parameter = array();
        $str_form = $this->parser->parse('form/counting_report', array("data" => $parameter, "test_parse" => "teat pass by parse"), TRUE);

        /* COMMENT BY POR 2013-11-05 เปลี่ยนไปใช้ workflow_template เพื่อให้ปุ่มอยู่ด้านนอก
          $this->parser->parse('form_template', array(
          'user_login' => $this->session->userdata('user_id')
          , 'copyright' => COPYRIGHT
          , 'menu_title' => 'Report : Counting Report'
          , 'menu' => $this->menu->loadMenu()
          , 'form' => $str_form
          , 'button_back' => ''
          , 'button_clear' => ''
          , 'button_save' => ''
          ));
         */

        //ADD BY POR 2013-11-05 แก้ไขให้เรียกใช้ workflow_template แทน
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Counting Report '
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPreDispatch"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
        //END ADD
    }

    function search_counting() {
        $search = $this->input->post();
        //p($search);
        /*
          [product_id] => 8
          [fdate] => 01/07/2013
          [tdate] => 30/07/2013
          [lot] =>
          [count_type] => 3
         */
        $data = $this->counting->searchCounting($search);
        //p($data);
        $view['search'] = $search;
        $view['data'] = $data;
        $this->load->view("report/counting_report", $view);
    }

    function export_counting_pdf() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $search = $this->input->post();
        $data = $this->counting->searchCounting($search);
        $view['data'] = $data;
//        Comment By Akkarapol, 19/11/2013, คอมเม้นต์ออกเพราะจะเอา บรรทัด "Qty.PO." ออก เนื่องจากไม่ต้องแสดงแล้ว
//        - เปลี่ยน Header Column จาก "Qty" เป็น "System"
//        - เปลี่ยน Header Column จาก "Counted Qty" เป็น "Physical"
//        $view['column'] = array(
//            "Date"
//            , "Product Code"
//            , "Product Name"
//            , "Qty.PO."
//            , "Qty"
//            , "Counted Qty"
//            , "Count By"
//            , "Variant (%)"
//        );
        
        $view['column'] = array(
            _lang('date')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('system')
            , _lang('physical')
            , _lang('count_by')
            , _lang('variant')
        );

        // Add By Akkarapol, 22/10/2013, เพิ่มโค๊ดในส่วนของการ query ชื่อ ของผู้ที่ทำการออก Report นี้
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $view['printBy'] = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        // END Add By Akkarapol, 22/10/2013, เพิ่มโค๊ดในส่วนของการ query ชื่อ ของผู้ที่ทำการออก Report นี้

        $this->load->view("report/export_counting_pdf.php", $view);
    }

    function export_counting_excel() {
        $search = $this->input->post();
        $data = $this->counting->searchCounting($search);
        $view['body'] = $data;
        $view['file_name'] = 'counting_report';
        
//        Comment By Akkarapol, 19/11/2013, คอมเม้นต์ออกเพราะจะเอา บรรทัด "Qty.PO." ออก เนื่องจากไม่ต้องแสดงแล้ว
//        - เปลี่ยน Header Column จาก "Qty" เป็น "System"
//        - เปลี่ยน Header Column จาก "Counted Qty" เป็น "Physical"
//        $view['header'] = array(
//            "Date"
//            , "Product Code"
//            , "Product Name"
//            , "Qty.PO."
//            , "Qty"
//            , "Counted Qty"
//            , "Count By"
//            , "Variant (%)"
//        );
        
        $view['header'] = array(
            _lang('date')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('system')
            , _lang('physical')
            , _lang('count_by')
            , _lang('variant')
        );
        //$view['body']  =  $report;
        $this->load->view('report/counting_excel_template', $view);
    }

}

?>