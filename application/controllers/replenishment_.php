<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class replenishment_ extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->library('pagination_ajax');
        $this->load->helper('form');
        $this->load->model("report_model", "r");
        // $this->load->model("encoding_conversion", "conv");
        // $this->load->model("re_location_model", "reLocation");  // Add By Akkarapol, 16/10/2013, เพิ่มการ load model ชื่อ re_location_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        // $this->load->model("workflow_model", "flow");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ workflow_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        // $this->load->model("company_model", "company");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ company_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        // $this->load->model("system_management_model", "sys");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ system_management_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        // $this->load->model("pre_dispatch_model", "pre_dispatch");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ pre_dispatch_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        // $this->load->model("pending_model", "pending");  // Add By Akkarapol, 11/11/2013, เพิ่มการ load model ชื่อ pending_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        // $this->load->model("location_model", "lc");
        // $this->load->model("product_model", "product");
        // $this->load->model("order_movement_model", "order_movement");
        $this->load->model("master_tmp");
    }

    function replenishment_form() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        $parameter['list_view'] = $this->master_tmp->data_list();
        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("temp_ball_view", $parameter, TRUE);
        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Add : Master Replenishment '
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form

            // Add By Akkarapol, 24/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmShipment"></i>'
            // END Add By Akkarapol, 24/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
        ));
    }

    function replenishment_adding() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("temp_ball_adding", $parameter, TRUE);
        $this->load->model("product_model", "p");

        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Add : List '
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmShipment"></i>'
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว

        ));
    }

    function get_list(){
        $list = $this->input->post();
        if(!empty($list)){
            $aList  = array_chunk($list, 4); 
            foreach ($aList as $key => $value) {
                $list_array[$key]['location_code'] = $value[0];
                $list_array[$key]['product_code'] = $value[1];
                $list_array[$key]['re_order_point'] = $value[2];
                $list_array[$key]['max'] = $value[3];
            }
            foreach ($list_array as $key => $by_list) {
                $this->master_tmp->insert_tmp($by_list);
            }
        }
        echo '<script>location.replace("'.base_url().'index.php/replenishment_/replenishment_form")</script>';
    }

    function get_location(){
        $location_like = $this->input->post();
        $location = trim($location_like['text_search']);
        $location_ = $this->master_tmp->get_location($location);
        echo json_encode($location_);
    }

    function recheck_location_product(){
        $text_search = $this->input->post();

        $res = json_decode($text_search['Recheck_location_product']);
        $list['product_id'] = $res[0];
        $list['location_code'] = $res[1];
        // Model
        $res_product = $this->master_tmp->check_product($list['product_id']);
        $res_location = $this->master_tmp->check_location($list['location_code']);

        if(!empty($res_product)){
            $res_product = TRUE;
        }else{
            $res_product = FALSE;
        }
        if(!empty($res_location)){
            $res_location = TRUE;
        }else{
            $res_location = FALSE;
        }
        $res_status['product_id'] = $res_product;
        $res_status['location_code'] = $res_location;
       
        echo json_encode($res_status);
    }
    function delete_list(){
        $id_master = $this->input->GET();
    
        $parameter = $this->master_tmp->delete_list($id_master['id']);

        if(empty($parameter)){
            echo json_encode(TRUE);
        }else{
            echo json_encode(FALSE);
        }
    }


}
