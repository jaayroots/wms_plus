<!--Create By Joke 9/10/57-->

<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class order_tracking extends CI_Controller {

    public $settings;       //add by kik : 20140114
    public $revision; //กำหนดตัวแปรสำหรับเรียก revision ของ SVN

    public function __construct() {
        parent::__construct();
        $this->settings = native_session::retrieve();   //add by kik : 20140114
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->library('pagination_ajax');
        $this->load->helper('form');
        $this->load->model("report_model", "r");
//        $this->load->model("encoding_conversion", "conv");
//        $this->load->model("re_location_model", "reLocation");  // Add By Akkarapol, 16/10/2013, เพิ่มการ load model ชื่อ re_location_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
//        $this->load->model("workflow_model", "flow");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ workflow_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
//        $this->load->model("company_model", "company");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ company_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
//        $this->load->model("system_management_model", "sys");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ system_management_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
//        $this->load->model("pre_dispatch_model", "pre_dispatch");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ pre_dispatch_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
//        $this->load->model("pending_model", "pending");  // Add By Akkarapol, 11/11/2013, เพิ่มการ load model ชื่อ pending_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
//        $this->load->model("location_model", "lc");  
//        $this->load->model("product_model", "product"); 
        $this->load->model("order_movement_model", "order_movement");
//$this->config->set_item('renter_id', '1');  // Fix for TV DIRECT
//$this->config->set_item('owner_id', '2');  // Fix for JWD
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));


//ADD BY POR 2013-12-19 เพิ่ม revision สำหรับไปใช้ใน report
//$this->config->set_item('svnversion', @shell_exec('svnversion '.realpath(__FILE__)));
        $status = @shell_exec('svnversion ' . realpath(__FILE__));
        if (preg_match('/\d+/', $status, $match)) {
            $this->revision = $match[0];
        }
//END ADD
//        $this->load->model("location_history_model", "locationHis"); // ADD BY POR 2013-10-25 
    }

    public function index() {
        
    }

    public function OrderTracking() {
//        p('ddd'); exit();
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['display_items_per_page'] = 1;

        $str_form = $this->parser->parse("form/order_form_tracking", $parameter, TRUE);

//        $str_form = $this->parser->parse('report/order_tracking', array("data" => $parameter, "test_parse" => "teat pass by parse"), TRUE);
//
//        # PUT FORM IN TEMPLATE WORKFLOW  
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Order Tracking'
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="formLocationHistoryReport"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
            , 'button_action' => '' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
        ));
    }

    function get_OrderTracking() {

        $search = $this->input->get();
//          p($search);        exit();
        $view['search'] = $search;
        $data = $this->order_movement->get_data_OT($search);
        $view['data'] = $data;
//        p($view);
        //$view['Document_No'] = $data->Document_No;;
        //$view['Doc_Refer_Ext'] = $data->Doc_Refer_Ext;
//        $view['Doc_Refer_Int'];
//        $view['Doc_Refer_Inv'];
//        $view['Doc_Refer_CE'];
//        $view['Doc_Refer_BL'];
//        $view['Order_Id'];
//        $view['Edge_Id'];
//        $view['Sub_Module'];
//        $view['Module'];
//        $view['First_NameTH'];
//        $view['Last_NameTH'];
//        $view['Description'];
// $view['First_NameTH'];
        // p($view);
//        $view['data'] = $this->r->searchLocation_swa($search, $limit[0], $limit[1]);
//        p($view);exit();
        $this->load->view("report/order_tracking.php", $view);
    }

    function get_OrderTracking_detail() {

        $flow_id = $this->input->get('flow_id'); // รับค่า flow_id
        $type = $this->input->get('type'); // รับค่า type 
        $order_id = $this->input->get('order_id');

        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['display_items_per_page'] = 1;

        //$parameter['search'] = $search;
        $datas = $this->order_movement->detail_order_tracking($flow_id, $type)->result_array();
        $data = $this->order_movement->get_Order_Tracking($order_id, $flow_id)->result_array();
//       p($data);
        $parameter['data'] = $datas[0];
        $parameter['data1'] = $data;

        $str_form = $this->parser->parse("report/search_order_tracking.php", $parameter, TRUE);
//        $str_form = $this->parser->parse('report/order_tracking', array("data" => $parameter, "test_parse" => "teat pass by parse"), TRUE);
//
//        # PUT FORM IN TEMPLATE WORKFLOW  
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Order Tracking'
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="formLocationHistoryReport"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
            , 'button_action' => '' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
        ));
    }

}
