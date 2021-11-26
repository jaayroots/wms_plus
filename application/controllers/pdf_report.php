<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class pdf_report extends CI_Controller {


    function __construct() {
        parent::__construct();
        $this->load->model("location_model", "loc");
        $this->load->model("warehouse_model", "wh");
        $this->load->model("zone_model", "zone");
        $this->load->model("storage_model", "stor");
        $this->load->model("stock_model", "stock");
        $this->load->model("product_info_model", "info");
        $this->load->model("encoding_conversion", "encode");
        $this->load->model("inbound_model", "inbound");  // add by kik(16-12-2013)
        $this->load->model("system_management_model", "sys");//add by kik (20140312)
        $this->load->model("product_model", "product");
        
        $this->load->model("report_model", "r");
        $this->load->model("pdf_report_model", "pdf_r");

    }

    function ajax_show_location_list() {
//    	$text_search = $this->input->post('text_search');
//    	$locations = $this->loc->searchLocation($text_search, '', 100);
//    	$list = array();
//    	foreach ($locations as $key => $location) {
//    		$list[$key]['Location_Id'] = $location->Location_Id;
//    		$list[$key]['Location_Code'] = $location->Location_Code;
//    	}
//    	echo json_encode($list);
    }
    
    function get_zone(){
        error_reporting(E_ALL);
        $data = $this->pdf_r->getZone()->result_array();
        echo json_encode($data);
    }
    
    function get_storage(){
        $zone = $this->input->post('zone');
        $data = $this->pdf_r->getStorage(null,$zone)->result_array();
        echo json_encode($data);
    }
    
    function get_location(){
        $zone = $this->input->post('zone');
        $storage = $this->input->post('storage');
        $data = $this->pdf_r->getLocationList($zone,$storage)->result_array();
        echo json_encode($data);
    }
    
    function gen_pdf(){
        ini_set('max_execution_time', 0);
        error_reporting(E_ALL);
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $zone = $this->input->post('zone');
        $storage = $this->input->post('storage');
        $new_file_name = $this->input->post('new_file_name');
        
        $data = $this->pdf_r->get_data_tag($zone,$storage)->result_array();
        $view['data'] = $data;
        $view['new_filename'] = $new_file_name;
//        echo json_encode($view['new_filename']) ;
        $this->load->view("pdf_report/v_stock_count_tag.php",$view);
        
        
    }

}
