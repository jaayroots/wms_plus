<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of shelfLifeReport
 *
 * @author Pong-macbook
 */
class location_history_report extends CI_Controller {
    public $settings;       //ADD BY POR 2014-06-20
    //put your code here
    public function __construct() {
        parent::__construct();
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->settings = native_session::retrieve();   //ADD BY POR 2014-06-20
        $this->load->helper('form');
        $this->load->model("location_history_model", "r");
        $this->load->model("encoding_conversion", "conv");
        $this->config->set_item('renter_id', '1');  // Fix for TV DIRECT
        $this->config->set_item('owner_id', '2');  // Fix for JWD
    }

    public function index() {
        $isUserLogin = $this->session->userdata("user_id");
        if ($isUserLogin == "") {
            echo "<script>alert('Your session was expired. Please Log-in!')</script>";
            echo "<script>window.location.replace('" . site_url() . "');</script>";
        } else {
            $this->locationHistoryForm();
        }
    }

    public function locationHistoryForm() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        

        $this->load->model("company_model", "company");
        $this->load->model("warehouse_model", "w");
        $this->load->model("product_model", "p");

        # Get Location
        $parameter['current_location'] = $q_current_location = $this->getSgLocationAll();

        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/location_history_report", $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW  
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Location History'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="formLocationHistoryReport"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
        ));
    }

    function showLocationHistoryReport() {
//        p($_POST);
//        exit();
//        Array
//        (
//            [renter_id] => undefined
//            [warehouse_id] => undefined
//            [category_id] => undefined
//            [product_id] => 
//            [as_date] => undefined
//            [period] => undefined
//            [step] => undefined
//        )
        $ref_value = $this->input->post("ref_value");
        $current_location = $this->input->post("current_location");
        if ($current_location == 'Actual Location') {
            $current_location = "";
        }
        $lot = $this->input->post("lot");
        $product_id = $this->input->post("product_id");
        $date_from = convertDate($this->input->post("date_from"), "eng", "iso", "-");
        ;
        // echo $date_from;
        $serial = $this->input->post("serial");
        $doc_type = $this->input->post("doc_type");
        //echo convertDate( $as_date, "eng", "iso", "-"); 
        $condition_value = array(
            "ref_value" => $ref_value
            , "current_location" => $current_location
            , "lot" => $lot
            , "product_id" => $product_id
            , "date_from" => $date_from
            , "serial" => $serial
            , "doc_type" => $doc_type
            , "pallet_code" => $this->input->post("pallet_code")
        );
        // p($condition_value);exit();
        $search = $this->input->post();
        $location_history = $this->r->locationHistoryReport($condition_value);

        $data = $location_history;
        // p($data);exit();
        // $data = $this->r->searchAging($search);
        $view['data'] = $data;
        $view['condition_value'] = $condition_value;
        $view['doc_ref_td'] = $doc_type;

        // Add By Akkarapol, 05/11/2013, เพิ่มการจัดการตัว parser ที่จะไปกำหนดค่า aoColumns[{ "sSortDataType": "dom-text", "sType": "numeric-comma"}] ใน column ต่างๆ
        $parser_ao_column = '';
        for ($i = 0; $i < 5; ++$i):
            $parser_ao_column = $parser_ao_column . 'null,null,{ "sSortDataType": "dom-text", "sType": "numeric-comma"},{ "sClass": "td_price_per_unit" },{ "sClass": "td_price_per_unit" },{ "sClass": "td_price_per_unit" },';  //EDIT BY POR 2014-06-20 แก้ไขเพิ่ม "sClass": "td_price_per_unit" เข้าไปเนื่องจากมีการเพิ่มฟิลล์แสดงผลเพิ่มเติม
        endfor;
        $view['parser_ao_column'] = $parser_ao_column;
        // END Add By Akkarapol, 05/11/2013, เพิ่มการจัดการตัว parser ที่จะไปกำหนดค่า aoColumns[{ "sSortDataType": "dom-text", "sType": "numeric-comma"}] ใน column ต่างๆ
        $view['statusprice'] = $this->settings['price_per_unit']; //ADD BY POR 2014-06-20 ADD Price per unit

        $this->load->view("report/location_history_report.php", $view);
    }

    function exportReportHistoryReportToPDF() {
//        $this->load->library('pdf/mpdf');
//        date_default_timezone_set('Asia/Bangkok');
//        $fdate = $this->input->post("fdate");
//        $tdate = $this->input->post("tdate");
//        $product = $this->input->post("product");
//        $product_id = $this->input->post("product_id2");
//        $current_location = $this->input->post("current_location");
//        $lot= $this->input->post("lot");
//        $serial = $this->input->post("serial");
//        $doc_type = $this->input->post("doc_type");
//        $ref_value = $this->input->post("ref_value");
//        $date_from = $this->input->post("date_from");
//        //$product_name=$this->r->getProductDetailByCode($product_code);
//        $condition_value = array(
//                                    "ref_value"=>$ref_value
//                                    ,"current_location"=>$current_location
//                                    ,"lot"=>$lot
//                                    ,"product_id"=>$product_id
//                                    ,"date_from"=>$date_from
//                                    ,"serial"=>$serial
//                                    ,"doc_type"=>$doc_type
//                                ); 
        // p($_POST);exit();
        $search = $this->input->post();
        $location_history = $this->r->locationHistoryReport($condition_value);

        $data = $location_history;
        $view['data'] = $data;
        $view['condition_value'] = $condition_value;
        $view['doc_ref_td'] = $doc_type;

        //$data = $this->r->countProductShelfLife($search);
        //p($data);
        $view['body'] = $data;
        $view['file_name'] = 'location_history_report';
        $this->load->view("report/location_history_report", $view);
    }

    function getSgLocationAll() {
        # jquery show select option location can put select all location not full
        $location_list = $this->r->showLocationAll();

        $list = '<select id="current_location" name="current_location" >';
        $list.='<option value="" selected>Actual Location</option>';
        foreach ($location_list as $loc) {
            $list.='<option value="' . $loc->Location_Code . '">' . $loc->Location_Code . '</option>';
        }
        $list .="</select>";
        return $list;
    }
    
    
    
    function direct_link() {
        $report_list = $this->r->select_vw_location_history_new()->result();        
        
        $report = array();
        $sum_balance = 0;
        $sum_booked = 0;
        $sum_dispatch = 0;
        $sum_remain = 0;
        foreach ($report_list as $key_rl => $rl) :
            $report[$key_rl]['No'] = $key_rl+1;
            $report[$key_rl]['Product_code'] = $rl->Product_Code;
            $report[$key_rl]['Product_name'] = $rl->Product_NameEN;
            $report[$key_rl]['Lot'] = $rl->Product_Lot;
            $report[$key_rl]['Serial'] = $rl->Product_Serial;
            $report[$key_rl]['Document_No'] = $rl->Document_No;
            
            if($rl->Product_Status == 'booked'):
                $report[$key_rl]['Product_Status'] = 'Booked';
                $report[$key_rl]['Balance'] = array('align' => 'right', 'value' => set_number_format(0));
                $report[$key_rl]['Booked'] = array('align' => 'right', 'value' => set_number_format($rl->Balance_Qty));
                $report[$key_rl]['Dispatch'] = array('align' => 'right', 'value' => set_number_format(0));
                $report[$key_rl]['Remain'] = array('align' => 'right', 'value' => set_number_format($rl->remain));
                $report[$key_rl]['Location'] = $rl->location;
            elseif($rl->Product_Status == 'dispatch'):
                $report[$key_rl]['Product_Status'] = 'Dispatch';
                $report[$key_rl]['Balance'] = array('align' => 'right', 'value' => set_number_format(0));
                $report[$key_rl]['Booked'] = array('align' => 'right', 'value' => set_number_format(0));
                $report[$key_rl]['Dispatch'] = array('align' => 'right', 'value' => set_number_format($rl->Balance_Qty));
                $report[$key_rl]['Remain'] = array('align' => 'right', 'value' => set_number_format($rl->remain));
                $report[$key_rl]['Location'] = 'Pre-Dispatch';
            else:
                $report[$key_rl]['Product_Status'] = $rl->Product_Status;
                $report[$key_rl]['Balance'] = array('align' => 'right', 'value' => set_number_format($rl->Balance_Qty));
                $report[$key_rl]['Booked'] = array('align' => 'right', 'value' => set_number_format($rl->booked));
                $report[$key_rl]['Dispatch'] = array('align' => 'right', 'value' => set_number_format($rl->dispatch));
                $report[$key_rl]['Remain'] = array('align' => 'right', 'value' => set_number_format($rl->remain));
                $report[$key_rl]['Location'] = $rl->location;
                
                $sum_balance += $rl->Balance_Qty;
                $sum_booked += $rl->booked;
                $sum_dispatch += $rl->dispatch;
                $sum_remain += $rl->remain;
        
            endif;
            
        endforeach;
        
            $report_total['No'] = '';
            $report_total['Product_code'] = '';
            $report_total['Product_name'] = '';
            $report_total['Lot'] = '';
            $report_total['Serial'] = '';
            $report_total['Document_No'] = '';
            $report_total['Product_Status'] = '';
            $report_total['Balance'] = array('align' => 'right', 'value' => set_number_format($sum_balance));
            $report_total['Booked'] = array('align' => 'right', 'value' => set_number_format($sum_booked));
            $report_total['Dispatch'] = array('align' => 'right', 'value' => set_number_format($sum_dispatch));
            $report_total['Remain'] = array('align' => 'right', 'value' => set_number_format($sum_remain));
            $report_total['Location'] = '';
        
            $report[] = $report_total;
            
//        p($report);
//        exit();

//        $view['file_name'] = 'location_history_report_direct_link-' . date('Ymd') . '-' . generateRandomString(3);
        $view['file_name'] = 'location_history_report-' . date('Ymd-His');
        $view['header'] = array(
            _lang('no'),
            _lang('product_code'),
            _lang('product_name'),
            _lang('lot'),
            _lang('serial'),
            _lang('document_no'),
            _lang('product_status'),
            _lang('balance'),
            _lang('Booked'),
            _lang('Dispatch'),
            _lang('Remain'),
            _lang('location')
            
        );
                
        $view['body'] = $report;
        $this->load->view('excel_template', $view);
    }

}

?>
