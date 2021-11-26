<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
# 1.stockMovement show search form -> getStockMovement() show search result
# 1.1.searchProductMovement  search for 1 product -> exportReportStockMovementToPDF() is create in PDF file
# 1.2.searchProductMovementAllProduct search all product or only Date range -> exportReportStockMovementAllProductPDF create PDF
# 2.showProductList for jquery auto complete box list product
# 3.Good Receiv note -> GRN() show result
#

class Report extends CI_Controller {

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
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("re_location_model", "reLocation");  // Add By Akkarapol, 16/10/2013, เพิ่มการ load model ชื่อ re_location_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        $this->load->model("workflow_model", "flow");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ workflow_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        $this->load->model("company_model", "company");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ company_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        $this->load->model("system_management_model", "sys");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ system_management_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        $this->load->model("pre_dispatch_model", "pre_dispatch");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ pre_dispatch_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        $this->load->model("pending_model", "pending");  // Add By Akkarapol, 11/11/2013, เพิ่มการ load model ชื่อ pending_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        $this->load->model("location_model", "lc");
        $this->load->model("product_model", "product");
        $this->load->model("order_movement_model", "order_movement");
        //$this->config->set_item('renter_id', '1');  // Fix for TV DIRECT
        //$this->config->set_item('owner_id', '2');  // Fix for JWD
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));
        $this->settings = native_session::retrieve();

        //ADD BY POR 2013-12-19 เพิ่ม revision สำหรับไปใช้ใน report
        //$this->config->set_item('svnversion', @shell_exec('svnversion '.realpath(__FILE__)));
        $status = @shell_exec('svnversion ' . realpath(__FILE__));
        if (preg_match('/\d+/', $status, $match)) {
            $this->revision = $match[0];
        }
        //END ADD

        $this->load->model("location_history_model", "locationHis"); // ADD BY POR 2013-10-25
    }

    public function index() {
        
    }

    # start stock Movement #

    public function stockMovement() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $parameter = array();
        $parameter['renter_id'] = $this->config->item('renter_id');

        #Get renter list
        $this->load->model("company_model", "company");
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE);
        $parameter['renter_list'] = $renter_list;

        $str_form = $this->parser->parse('form/stockMovement_report', array("data" => $parameter, "test_parse" => "teat pass by parse"), TRUE);

        /* COMMENT BY POR 2013-11-05 เปลี่ยนไปใช้ workflow_template เพื่อให้ปุ่มอยู่ด้านนอก
          $this->parser->parse('form_template', array(
          'user_login' => $this->session->userdata('user_id')
          , 'copyright' => COPYRIGHT
          , 'menu_title' => 'Stock Movement Report'
          , 'menu' => $this->menu->loadMenu()
          , 'form' => $str_form
          , 'button_back' => ''
          , 'button_clear' => ''
          , 'button_save' => ''
          ));
         */
        //ADD BY POR 2013-11-05 แก้ไขให้เรียกใช้ workflow_template แทน
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Stock Movement Report '
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmStockMove"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
        //END ADD
    }

    function getStockMovement() {

        $search = $this->input->post();
    //    p($search);exit();
        /**
         * show movement by item not group lot/sel
         */
        if ($search['show_by'] == "show_item") {

            if ($search['product_id'] == "") {
                $result = $this->r->searchProductMovementAllProduct_showItem($search);
                $view['datas'] = $result;
                $view['form_value'] = $search;
                $this->load->view("report/stockMovementReportDetailAllProduct_showItem", $view);
            } else {
                $result = $this->r->searchProductMovement_showItem($search);
                $view['datas'] = $result;
                $view['form_value'] = $search;
                $this->load->view("report/stockMovementReportDetail_showItem", $view);
            }
        }

        if ($search['show_by'] == "show_total") {

//            if ($search['product_id'] == "") {
            $result = $this->r->searchProductMovement_showTotal($search);
            $view['datas'] = $result;
            $view['form_value'] = $search;
            $this->load->view("report/stockMovementReportDetail_showTotal", $view);
//            } else {
//                $result = $this->r->searchProductMovement_showTotal($search);
//                $view['datas'] = $result;
//                $view['form_value'] = $search;
//                $this->load->view("report/stockMovementReportDetail_showTotal", $view);
//            }
        }

        /**
         * show movement by group lot / sel
         */
        if ($search['show_by'] == "show_movement") {

            if ($search['product_id'] == "") {
                $result = $this->r->searchProductMovementAllProduct($search);
                //  p($result); exit();
                //            $data = $this->r->groupProductResult($result);    #comment by kik (23-09-2013)
                //            $view['data'] = $data;                            #comment by kik (23-09-2013)
                $view['datas'] = $result;                            #add by kik (23-09-2013)
                $view['form_value'] = $search;
                $this->load->view("report/stockMovementReportDetailAllProduct", $view);
            } else {
                $result = $this->r->searchProductMovement($search);
                //            $view['balance'] = $result[0]; // start balance qty #comment by kik (20-09-2013)
                $view['datas'] = $result;  // result  #edit by kik (20-09-2013)
                $view['form_value'] = $search;
                $this->load->view("report/stockMovementReportDetail", $view);
            }
        }
    }

    function ajax_show_product_list() {
        $text_search = $this->input->post('text_search');
        $product = $this->r->searchProduct($text_search, 100);
        $list = array();
        foreach ($product as $key_p => $p) {
            $list[$key_p]['product_id'] = $p['product_id'];
            $list[$key_p]['product_code'] = $p['product_code'];
//            $list[$key_p]['product_name'] = '';
            $list[$key_p]['product_name'] = thai_json_encode($p['product_name']);
        }
        echo json_encode($list);
    }

    function showProductList() {
        $text_search = $this->input->post('text_search');
        $product = $this->r->searchProduct($text_search);
        $list = '';
        foreach ($product as $p) {
//            $list.='<li onClick="fill(\'' . $p['product_id'] . '\',\'' . $p['product_code'] . '\',\'' . $this->conv->tis620_to_utf8($p['product_name']) . '\');">' . $p['product_code'] . ' ' . $this->conv->tis620_to_utf8($p['product_name']) . '</li>';  // Comment By Akkarapol, 18/09/2013, คอมเม้นต์ทิ้งเพราะการส่งค่าไปแบบนี้ ถ้าเกิด ชื่อของ Product มีเครื่องหมาย ' จะทำให้มีปัญหา
            $list.='<li onClick="fill(\'' . $p['product_id'] . '\',\'' . $p['product_code'] . '\',\'\');">' . $p['product_code'] . ' ' . $this->conv->tis620_to_utf8($p['product_name']) . '</li>'; // Add By Akkarapol, 18/09/2013, ตัดการเอาชื่อ product ออกในส่วนของการเรียกฟังก์ชั่น fill เพราะทำให้มีปัญหา และช่องที่ใส่ค่านี้ มันคือช่อง Product Code ไม่จำเป็นต้องเอาชื่อ product ไปแสดง
        }
        echo $list;
    }

    //ADD BY POR 2013-10-28 เพิ่มกรณีมีการค้นหา location ในเมนู Location History
    function showLocationList() {
        $location = $this->locationHis->showLocationAll($this->input->post('text_search'));
        $list = '';
        foreach ($location as $p) {
            $list.='<li onClick="fill_location(\'' . $p['Location_Code'] . '\');">' . $p['Location_Code'] . '</li>';
        }
        echo $list;
    }

    //END ADD

    function exportExcelMovement_showItem() {
        $search = array(
            'product' => $this->input->post("product")
            , 'product_id' => $this->input->post("product_id2")
            , 'fdate' => $this->input->post("fdate")
            , 'tdate' => $this->input->post("tdate")
            , 'renter_id' => $this->input->post("renter_id")
            //ADD BY POR 2013-10-28 เพิ่มเติมให้สามารถค้นหาด้วย document ได้
            , 'doc_type' => $this->input->post("doc_type")
            , 'doc_value' => $this->input->post("doc_value")
                //END ADD
        );
        $datas = $this->r->searchProductMovement_showItem($search);
        $report_group = array();
        $report_val = array();
        if (!empty($datas['no_onhand'])) {
            
        } else if (!empty($datas)) {

            foreach ($datas as $k => $columns) {#edit by kik (20-09-2013)
                unset($report);
                $report['Running_No'] = $k + 1;
                $report['Product_Name'] = $columns['Product_NameEN'];
                $report['Receive_date'] = $columns['receive_date'];
                $report['Receive_Document_No'] = $columns['receive_doc_no'];
                $report['Receive_Refer_Ext'] = $columns['receive_refer_ext'];
                $report['Dispatch_Document_No'] = $columns['pay_doc_no'];
                $report['Dispatch_Refer_Ext'] = $columns['pay_refer_ext'];
                $report['Receive_Qty'] = array('align' => 'right', 'value' => set_number_format($columns['r_qty']));
                $report['Dispatch_Qty'] = array('align' => 'right', 'value' => set_number_format($columns['p_qty']));
                $report['Product_SerLot'] = $columns['Product_SerLot']; #add by kik (20-09-2013)
                $report['Product_Mfd'] = $columns['Product_Mfd'];       #add by kik (20-09-2013)
                $report['Product_Exp'] = $columns['Product_Exp'];       #add by kik (20-09-2013)
                $report['Branch'] = $columns['branch'];                 #edit by kik (20-09-2013)
                $report['Location_Code'] = $columns['Location_Code'];
                $report['Balance_Qty'] = array('align' => 'right', 'value' => set_number_format($columns['Balance_Qty'])); //ADD BY BALL 2013-10-28 กำหนดค่าให้ชิดขวา
                $report_group[] = $report;
            }
        }

        $view['file_name'] = 'stock_movement';
        $view['body'] = $report_group;
        #Edit by kik
        #23-09-2013
        #เรียกใช้ title table จาก plugin language แทนการ hardcode เพื่ออนาคต! จะได้ไม่ต้องแก้หลายที่
        $view['header'] = array(
            _lang('no')
            , _lang('product_name')
            , _lang('date')
            , _lang('number_received')
            , _lang('Reference_received')
            , _lang('Number_dispatch')
            , _lang('Reference_dispatch')
            , _lang('receive_qty')
            , _lang('dispatch_qty')
            , _lang('serial') . "/" . _lang('lot')
            , _lang('product_mfd')
            , _lang('product_exp')
            , _lang('branch')
            , _lang('location')
            , _lang('balance')
        );
        $this->load->view('excel_template', $view);
    }

    function exportExcelMovement_showTotal() {
        $search = array(
            'product' => $this->input->post("product")
            , 'product_id' => $this->input->post("product_id2")
            , 'fdate' => $this->input->post("fdate")
            , 'tdate' => $this->input->post("tdate")
            , 'renter_id' => $this->input->post("renter_id")
            , 'doc_type' => $this->input->post("doc_type")
            , 'doc_value' => $this->input->post("doc_value")
        );
        $datas = $this->r->searchProductMovement_showTotal($search);
        $report_val = array();

        if (!empty($datas['no_onhand'])) {
            
        } else if (!empty($datas)) {

            foreach ($datas as $k => $columns) {#edit by kik (20-09-2013)
                unset($report);
                $balance_qty = ($columns['incoming_qty'] + $columns['receive_qty']) - $columns['dispatch_qty'];
                $report['Running_No'] = $k + 1;
                $report['Product_Code'] = $columns['Product_Code'];
                $report['Product_Name'] = $columns['Product_NameEN'];
                $report['Income_Qty'] = array('align' => 'right', 'value' => set_number_format($columns['incoming_qty']));
                $report['Receive_Qty'] = array('align' => 'right', 'value' => set_number_format($columns['receive_qty']));
                $report['Dispatch_Qty'] = array('align' => 'right', 'value' => set_number_format($columns['dispatch_qty']));
                $report['Balance_Qty'] = array('align' => 'right', 'value' => set_number_format($balance_qty));
                $report_val[] = $report;
            }
        }
        $view['file_name'] = 'stock_movement';
        $view['body'] = $report_val;

        $view['header'] = array(
            _lang('no')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('income_qty')
            , _lang('receive_qty')
            , _lang('dispatch_qty')
            , _lang('balance')
        );
//        p($view);exit();
        $this->load->view('excel_template', $view);
    }

    function exportExcelMovement() {
        $search = array(
            'product' => $this->input->post("product")
            , 'product_id' => $this->input->post("product_id2")
            , 'fdate' => $this->input->post("fdate")
            , 'tdate' => $this->input->post("tdate")
            , 'renter_id' => $this->input->post("renter_id")
            //ADD BY POR 2013-10-28 เพิ่มเติมให้สามารถค้นหาด้วย document ได้
            , 'doc_type' => $this->input->post("doc_type")
            , 'doc_value' => $this->input->post("doc_value")
                //END ADD
        );
        $datas = $this->r->searchProductMovement($search);

        $report_group = array();
        $report_val = array();
        if (!empty($datas['no_onhand'])) {
            
        } else if (!empty($datas)) {

            foreach ($datas as $data) {        #add by kik (20-09-2013)
                foreach ($data as $columns) {#edit by kik (20-09-2013)
                    unset($report);
                    $report['Running_No'] = "";
                    $report['Product_Name'] = $columns['Product_NameEN'];
                    $report['Receive_date'] = $columns['receive_date'];
                    $report['Receive_Document_No'] = $columns['receive_doc_no'];
                    $report['Receive_Refer_Ext'] = $columns['receive_refer_ext'];
                    $report['Dispatch_Document_No'] = $columns['pay_doc_no'];
                    $report['Dispatch_Refer_Ext'] = $columns['pay_refer_ext'];
                    $report['Receive_Qty'] = array('align' => 'right', 'value' => set_number_format($columns['r_qty']));
                    $report['Dispatch_Qty'] = array('align' => 'right', 'value' => set_number_format($columns['p_qty']));
                    $report['Product_SerLot'] = $columns['Product_SerLot']; #add by kik (20-09-2013)
                    $report['Product_Mfd'] = $columns['Product_Mfd'];       #add by kik (20-09-2013)
                    $report['Product_Exp'] = $columns['Product_Exp'];       #add by kik (20-09-2013)
                    $report['Branch'] = $columns['branch'];                 #edit by kik (20-09-2013)
                    $report['Location_Code'] = $columns['Location_Code'];
                    $report['Balance_Qty'] = array('align' => 'right', 'value' => set_number_format($columns['Balance_Qty'])); //ADD BY BALL 2013-10-28 กำหนดค่าให้ชิดขวา
                    $report_group[] = $report;
                }
            }

            /**
             * Add for defect 686 Stock moment reprot ขอ Sort by Date and Product code
             * by kik : 20140313
             */
            #sort new array key by Receive_date
            $result_sort_date = sort_arr_by_index($report_group, 'Receive_date', 'asc');

            $count = 1;

            #จัดเรียง array ใหม่เพราะต้องกำหนด Running_No เพื่อส่งค่าไปแสดงผลที่ view อย่างเดียว
            foreach ($result_sort_date as $val_sort_date):
                $val_sort_date['Running_No'] = $count;
                array_push($report_val, $val_sort_date);
                $count++;
            endforeach;
            #end add for defect 686
        }




        $view['file_name'] = 'stock_movement';
        $view['body'] = $report_val;
        #Edit by kik
        #23-09-2013
        #เรียกใช้ title table จาก plugin language แทนการ hardcode เพื่ออนาคต! จะได้ไม่ต้องแก้หลายที่
        $view['header'] = array(
            _lang('no')
            , _lang('product_name')
            , _lang('date')
            , _lang('number_received')
            , _lang('Reference_received')
            , _lang('Number_dispatch')
            , _lang('Reference_dispatch')
            , _lang('receive_qty')
            , _lang('dispatch_qty')
            , _lang('serial') . "/" . _lang('lot')
            , _lang('product_mfd')
            , _lang('product_exp')
            , _lang('branch')
            , _lang('location')
            , _lang('balance')
        );
        $this->load->view('excel_template', $view);
    }

    function exportReportStockMovementToPDF_showItem() {
        $this->load->library('pdf/mpdf');
        $this->load->model("company_model", "company");     #add by kik (24-09-2013)
        date_default_timezone_set('Asia/Bangkok');

        #company
        #add by kik (24-09-2013)
        $temp_company = $this->company->getCompanyByID($this->input->post('renter_id'));
        $temp_company2 = $temp_company->result();

        $fdate = $this->input->post("fdate");
        $tdate = $this->input->post("tdate");
        $product = $this->input->post("product");
        $product_id = $this->input->post("product_id2");
        $where_product['product_id'] = $product_id;
        $data_select = array('Product_code', 'Product_NameEN');
        $product_data = $this->product->getProductData($where_product, $data_select);
        $product_code = "";
        $product_name = "";
//        p($product_data);exit();
        if (!empty($product_data)):
            $product_code = $product_data[0]->Product_code;
            $product_name = $product_data[0]->Product_NameEN;
        endif;

        $search = array('product' => $product
            , 'product_id' => $product_id
            , 'fdate' => $fdate
            , 'tdate' => $tdate
            , 'renter_id' => $this->input->post("renter_id")
            //ADD BY POR 2013-10-28 เพิ่มเติมให้สามารถค้นหาด้วย document ได้
            , 'doc_type' => $this->input->post("doc_type")
            , 'doc_value' => $this->input->post("doc_value")
                //END ADD
        );

        $data = $this->r->searchProductMovement_showItem($search);
        $view['search'] = $search;
//        $view['balance'] = $data[0];  #comment by kik (20-09-2013)
//        $view['data'] = $data[1];     #comment by kik (20-09-2013)
        $view['datas'] = $data;       #add by kik (20-09-2013)
        $view['product_code'] = $product_code;
        $view['product_name'] = $product_name;
        $view['Company_NameEN'] = $temp_company2[0]->Company_NameEN;     #add by kik (24-09-2013)
        // Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        // END Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report

        $this->load->view("report/exportStockMovement_showItem", $view);
    }

    function exportReportStockMovementToPDF_showTotal() {
        $this->load->library('pdf/mpdf');
        $this->load->model("company_model", "company");     #add by kik (24-09-2013)
        date_default_timezone_set('Asia/Bangkok');

        #company
        #add by kik (24-09-2013)
        $temp_company = $this->company->getCompanyByID($this->input->post('renter_id'));
        $temp_company2 = $temp_company->result();

        $fdate = $this->input->post("fdate");
        $tdate = $this->input->post("tdate");
        $product = $this->input->post("product");
        $product_id = $this->input->post("product_id2");
        $where_product['product_id'] = $product_id;
        $data_select = array('Product_code', 'Product_NameEN');
        $product_data = $this->product->getProductData($where_product, $data_select);
        $product_code = "";
        $product_name = "";
//        p($product_data);exit();
        if (!empty($product_data)):
            $product_code = $product_data[0]->Product_code;
            $product_name = $product_data[0]->Product_NameEN;
        endif;

        $search = array('product' => $product
            , 'product_id' => $product_id
            , 'fdate' => $fdate
            , 'tdate' => $tdate
            , 'renter_id' => $this->input->post("renter_id")
            , 'doc_type' => $this->input->post("doc_type")
            , 'doc_value' => $this->input->post("doc_value")
        );

        $data = $this->r->searchProductMovement_showTotal($search);
//        p($data);exit();
        $view['search'] = $search;
        $view['datas'] = $data;       #add by kik (20-09-2013)
        $view['product_code'] = $product_code;
        $view['product_name'] = $product_name;
        $view['Company_NameEN'] = $temp_company2[0]->Company_NameEN;     #add by kik (24-09-2013)
        #
        // Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        // END Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report

        $this->load->view("report/exportStockMovement_showTotal", $view);
    }

    function exportReportStockMovementToPDF() {
        $this->load->library('pdf/mpdf');
        $this->load->model("company_model", "company");     #add by kik (24-09-2013)
        date_default_timezone_set('Asia/Bangkok');

        #company
        #add by kik (24-09-2013)
        $temp_company = $this->company->getCompanyByID($this->input->post('renter_id'));
        $temp_company2 = $temp_company->result();

        $fdate = $this->input->post("fdate");
        $tdate = $this->input->post("tdate");
        $product = $this->input->post("product");
        $product_id = $this->input->post("product_id2");
        //$product_name=$this->r->getProductDetailByCode($product_code);
        $product_name = $this->r->getProductDetailById($product_id);

        $search = array('product' => $product
            , 'product_id' => $product_id
            , 'fdate' => $fdate
            , 'tdate' => $tdate
            , 'renter_id' => $this->input->post("renter_id")
            //ADD BY POR 2013-10-28 เพิ่มเติมให้สามารถค้นหาด้วย document ได้
            , 'doc_type' => $this->input->post("doc_type")
            , 'doc_value' => $this->input->post("doc_value")
                //END ADD
        );
        $data = $this->r->searchProductMovement($search);

        $view['search'] = $search;
//        $view['balance'] = $data[0];  #comment by kik (20-09-2013)
//        $view['data'] = $data[1];     #comment by kik (20-09-2013)
        $view['datas'] = $data;       #add by kik (20-09-2013)
        $view['product_code'] = $product_code;
        $view['product_name'] = $product_name;
        $view['Company_NameEN'] = $temp_company2[0]->Company_NameEN;     #add by kik (24-09-2013)
        // Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        // END Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report

        $this->load->view("report/exportStockMovement", $view);
    }

    function exportReportStockMovementAllProductPDF() {
        $this->load->library('pdf/mpdf');
        $this->load->model("company_model", "company");     #add by kik (24-09-2013)
        date_default_timezone_set('Asia/Bangkok');
        $fdate = $this->input->post("fdate");
        $tdate = $this->input->post("tdate");
        $product = $this->input->post("product");

        #company
        #add by kik (24-09-2013)
        $temp_company = $this->company->getCompanyByID($this->input->post('renter_id'));
        $temp_company2 = $temp_company->result();

        //$product_id=$this->input->post("product_id");
        $search = array('product' => $product
            , 'fdate' => $fdate
            , 'tdate' => $tdate
            , 'renter_id' => $this->input->post("renter_id")
            //ADD BY POR 2013-10-28 เพิ่มเติมให้สามารถค้นหาด้วย document ได้
            , 'doc_type' => $this->input->post("doc_type")
            , 'doc_value' => $this->input->post("doc_value")
                //END ADD
        );
        $result = $this->r->searchProductMovementAllProduct($search);

//        $data = $this->r->groupProductResult($result);    #comment by kik (23-09-2013)

        $view['search'] = $search;
        $view['Company_NameEN'] = $temp_company2[0]->Company_NameEN;     #add by kik (24-09-2013)
        $view['datas'] = $result;  #add by kik (23-09-2013)
//        $view['data'] = $data;#comment by kik (23-09-2013)
        // Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        // END Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report

        $this->load->view("report/exportStockMovementAllProduct", $view);
    }

    function exportReportStockMovementAllProductPDF_showItem() {
        $this->load->library('pdf/mpdf');
        $this->load->model("company_model", "company");     #add by kik (24-09-2013)
        date_default_timezone_set('Asia/Bangkok');
        $fdate = $this->input->post("fdate");
        $tdate = $this->input->post("tdate");
        $product = $this->input->post("product");

        #company
        #add by kik (24-09-2013)
        $temp_company = $this->company->getCompanyByID($this->input->post('renter_id'));
        $temp_company2 = $temp_company->result();

        //$product_id=$this->input->post("product_id");
        $search = array('product' => $product
            , 'fdate' => $fdate
            , 'tdate' => $tdate
            , 'renter_id' => $this->input->post("renter_id")
            //ADD BY POR 2013-10-28 เพิ่มเติมให้สามารถค้นหาด้วย document ได้
            , 'doc_type' => $this->input->post("doc_type")
            , 'doc_value' => $this->input->post("doc_value")
                //END ADD
        );
        $result = $this->r->searchProductMovementAllProduct_showItem($search);
//        p($result);exit();

        $view['search'] = $search;
        $view['Company_NameEN'] = $temp_company2[0]->Company_NameEN;     #add by kik (24-09-2013)
        $view['datas'] = $result;  #add by kik (23-09-2013)
        // Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        // END Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
//p($view);exit();
        $this->load->view("report/exportStockMovementAllProduct_showItem", $view);
    }

    function exportExcelMovementAll_showItem() {
        $search = array(
            'product' => $this->input->post("product")
            , 'fdate' => $this->input->post("fdate")
            , 'tdate' => $this->input->post("tdate")
            , 'renter_id' => $this->input->post("renter_id")
            //ADD BY POR 2013-10-28 เพิ่มเติมให้สามารถค้นหาด้วย document ได้
            , 'doc_type' => $this->input->post("doc_type")
            , 'doc_value' => $this->input->post("doc_value")
                //END ADD
        );
        $datas = $this->r->searchProductMovementAllProduct_showItem($search);
//       p($datas);exit();
        $report_group = array();
        $report_val = array();
        if (!empty($datas['no_onhand'])) {
            
        } else if (!empty($datas)) {
            $i = 1;
            foreach ($datas as $product_code => $data) {

                foreach ($data as $value) {

                    $report['Running_No'] = $i;
                    $report['Product_Code'] = $product_code;
                    $report['Product_Name'] = $value['Product_NameEN'];
                    $report['Receive_date'] = $value['receive_date'];
                    $report['Receive_Document_No'] = $value['receive_doc_no'];
                    $report['Receive_Refer_Ext'] = $value['receive_refer_ext']; //BY POR 2013-10-10 สลับที่กับ pay_doc_no เนื่องจากสลับที่กันอยู่ทำให้ข้อมูลผิดพลาด
                    $report['Dispatch_Document_No'] = $value['pay_doc_no'];  //BY POR 2013-10-10 สลับที่กับ receive_refer_ext เนื่องจากสลับที่กันอยู่ทำให้ข้อมูลผิดพลาด
                    $report['Dispatch_Refer_Ext'] = $value['pay_refer_ext'];
                    $report['Receive_Qty'] = array('align' => 'right', 'value' => set_number_format($value['r_qty']));
                    $report['Dispatch_Qty'] = array('align' => 'right', 'value' => set_number_format($value['p_qty']));
                    $report['Product_SerLot'] = $value['Product_SerLot']; #add by kik (20-09-2013)
                    $report['Product_Mfd'] = $value['Product_Mfd'];       #add by kik (20-09-2013)
                    $report['Product_Exp'] = $value['Product_Exp'];       #add by kik (20-09-2013)
                    $report['Branch'] = $value['branch'];
                    $report['Location_Code'] = $value['Location_Code'];
                    $report['Balance_Qty'] = array('align' => 'right', 'value' => set_number_format($value['Balance_Qty']));

                    $report_group[] = $report;

                    unset($report);

                    $i++;
                }
            }
        }

        $view['file_name'] = 'stock_movement';
        $view['body'] = $report_group;

        #Edit by kik
        #23-09-2013
        #เรียกใช้ title table จาก plugin language แทนการ hardcode เพื่ออนาคต! จะได้ไม่ต้องแก้หลายที่
        $view['header'] = array(
            _lang('no')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('date')
            , _lang('number_received')
            , _lang('Reference_received')
            , _lang('Number_dispatch')
            , _lang('Reference_dispatch')
            , _lang('receive_qty')
            , _lang('dispatch_qty')
            , _lang('serial') . "/" . _lang('lot')
            , _lang('product_mfd')
            , _lang('product_exp')
            , _lang('branch')
            , _lang('location')
            , _lang('balance')
        );
        $this->load->view('excel_template', $view);
    }

    function exportExcelMovementAll() {
        $search = array(
            'product' => $this->input->post("product")
            , 'fdate' => $this->input->post("fdate")
            , 'tdate' => $this->input->post("tdate")
            , 'renter_id' => $this->input->post("renter_id")
            //ADD BY POR 2013-10-28 เพิ่มเติมให้สามารถค้นหาด้วย document ได้
            , 'doc_type' => $this->input->post("doc_type")
            , 'doc_value' => $this->input->post("doc_value")
                //END ADD
        );
        $datas = $this->r->searchProductMovementAllProduct($search);
//       p($datas);exit();
        $report_group = array();
        $report_val = array();
        if (!empty($datas['no_onhand'])) {
            
        } else if (!empty($datas)) {

            foreach ($datas as $product_code => $data) {
                foreach ($data as $values) {   //p($values);exit();
                    foreach ($values as $value) {

                        $report['Running_No'] = "";
                        $report['Product_Code'] = $product_code;
                        $report['Product_Name'] = $value['Product_NameEN'];
                        $report['Receive_date'] = $value['receive_date'];
                        $report['Receive_Document_No'] = $value['receive_doc_no'];
                        $report['Receive_Refer_Ext'] = $value['receive_refer_ext']; //BY POR 2013-10-10 สลับที่กับ pay_doc_no เนื่องจากสลับที่กันอยู่ทำให้ข้อมูลผิดพลาด
                        $report['Dispatch_Document_No'] = $value['pay_doc_no'];  //BY POR 2013-10-10 สลับที่กับ receive_refer_ext เนื่องจากสลับที่กันอยู่ทำให้ข้อมูลผิดพลาด
                        $report['Dispatch_Refer_Ext'] = $value['pay_refer_ext'];
                        $report['Receive_Qty'] = array('align' => 'right', 'value' => set_number_format($value['r_qty']));
                        $report['Dispatch_Qty'] = array('align' => 'right', 'value' => set_number_format($value['p_qty']));
                        $report['Product_SerLot'] = $value['Product_SerLot']; #add by kik (20-09-2013)
                        $report['Product_Mfd'] = $value['Product_Mfd'];       #add by kik (20-09-2013)
                        $report['Product_Exp'] = $value['Product_Exp'];       #add by kik (20-09-2013)
                        $report['Branch'] = $value['branch'];
                        $report['Location_Code'] = $value['Location_Code'];
                        $report['Balance_Qty'] = array('align' => 'right', 'value' => set_number_format($value['Balance_Qty']));

                        $report_group[] = $report;

                        unset($report);
                    }
                }
            }

            /**
             * Add for defect 686 Stock moment reprot ขอ Sort by Date and Product code
             * by kik : 20140313
             */
            $result_sort_date = sort_arr_by_index($report_group, 'Receive_date', 'asc');

            /**
             * sort new array key by Receive_date
             */
            $distance_date = array();
            foreach ($result_sort_date as $val_sort_date):
                /**
                 * check key exists(Receive_date) in $distance_date  in array
                 */
                if (!key_exists($val_sort_date['Receive_date'], $distance_date)):
                    $distance_date[$val_sort_date['Receive_date']] = array();
                endif;

                #เรียง array ใหม่ โดยจัดให้ key เป็น Receive_date เพื่อง่ายต่อการเอาไป sort product code ในขั้นตอนต่อไป
                array_push($distance_date[$val_sort_date['Receive_date']], $val_sort_date);

            endforeach;

            /**
             * sort array by product code (sort each key array is Receive_date)
             */
            $report_val = array();
            $result_sort_products = array();

            $count = 1;
            foreach ($distance_date as $key => $val_in_date):

                $result_sort_products[$key] = sort_arr_by_index($val_in_date, 'Product_Code', 'asc');

                foreach ($result_sort_products[$key] as $val_sort_prod):
                    $val_sort_prod['Running_No'] = $count;
                    array_push($report_val, $val_sort_prod);
                    $count++;
                endforeach;
            endforeach;
            #end add for defect 686
        }

        $view['file_name'] = 'stock_movement';
        $view['body'] = $report_val;

        #Edit by kik
        #23-09-2013
        #เรียกใช้ title table จาก plugin language แทนการ hardcode เพื่ออนาคต! จะได้ไม่ต้องแก้หลายที่
        $view['header'] = array(
            _lang('no')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('date')
            , _lang('number_received')
            , _lang('Reference_received')
            , _lang('Number_dispatch')
            , _lang('Reference_dispatch')
            , _lang('receive_qty')
            , _lang('dispatch_qty')
            , _lang('serial') . "/" . _lang('lot')
            , _lang('product_mfd')
            , _lang('product_exp')
            , _lang('branch')
            , _lang('location')
            , _lang('balance')
        );
        $this->load->view('excel_template', $view);
    }

    # end stock movement
    # start report Good Receiving Note

    function GRN() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $report = '';
        $str_form = $this->parser->parse('form/grn_report', array("data" => $report, "test_parse" => "teat pass by parse"), TRUE);
        /* COMMENT BY POR 2013-11-05 เปลี่ยนไปใช้ workflow_template เพื่อให้ปุ่มอยู่ด้านนอก
          $this->parser->parse('form_template', array(
          'user_login' => $this->session->userdata('user_id')
          , 'copyright' => COPYRIGHT
          //			 ,'menu_title' => 'Report : Daily Report Good Receive '
          , 'menu_title' => 'Report : Daily Report Goods Receiving Note ' // Add By Akkarapol, 05/09/2013,  เธ•เน�เธญเธ�เน�เธ�เน�เธ�เธณเธงเน�เธฒ Goods Receiving Note เน�เธกเน�เน�เธ�เน� Good Receive
          , 'menu' => $this->menu->loadMenu()
          , 'form' => $str_form
          , 'button_back' => ''
          , 'button_clear' => ''
          , 'button_save' => ''
          ));
         */ //END COMMENT
        //ADD BY POR 2013-11-05 แก้ไขให้เรียกใช้ workflow_template แทน
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Daily Report Goods Receiving Note '
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_report"></i>' //EDIT BY POR 2013-11-05 แก้ไข toggle ให้รองรับกับที่พี่บอลสร้าง
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
        ));
        //END ADD
    }

    function searchGRN() {
        /* $search = $this->input->get();
          $data = $this->r->getGRN($search);
          $response = array();
          $idx = 0;
          $aoColumns = array("ASN Date","PO.NO","Supplier","Item","Product Name","Qty.PO.","Arrival Date","Receive Date/Informed","Qty. Receive","Remark");
          foreach ($data as $k => $v) :
          if ($idx >= $search['iDisplayStart'] && $idx <= $search['iDisplayLength']) :
          $response[] = array($v['0']->Estimate_Action_Date
          ,$v['0']->Doc_Refer_Ext
          ,$v['0']->supplier
          ,$v['0']->Product_Code
          ,thai_json_encode($v['0']->Product_NameEN)
          ,$v['reserv_quan']
          ,$v['0']->Actual_Action_Date
          ,$v['0']->Pending_Date
          ,$v['receive_quantity']
          ,thai_json_encode($v['remark_data'])
          );
          endif;
          $idx++;
          endforeach;
          $output = array(
          "sEcho" => intval($this->input->get('sEcho')),
          "iTotalRecords" => $idx,
          "iTotalDisplayRecords" => $idx,
          "aaData" => $response,
          "aoColumns" => $aoColumns
          );
          echo json_encode($output); */

        $search = $this->input->post();
        $report = $this->r->getGRN($search);
        $view['data'] = $report;
        // p($view['data']);exit;
        $view['search'] = $search;
        $this->load->view("report/grn_report.php", $view);
    }

    function exportGRNToPDF() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $search = $this->input->post($search);
        $report = $this->r->getGRN($search);
        $view['data'] = $report;
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        // p($view);exit;
        $this->load->view("report/exportGRN", $view);
    }

    function exportGRNToExcel() {
        $search = $this->input->post();
        $reports = $this->r->getGRN($search);
        // p($reports);exit();
        $data_views = array();

        foreach ($reports as $data):
            array_push($data_views, $data);


            if ($data[0]->Is_reject == 'Y'):

                $tmp_data_reject = new ArrayObject;
                $tmp_data_reject = $data;

                $tmp_data_reject['reserv_quan'] = - $tmp_data_reject['reserv_quan'];
                $tmp_data_reject['receive_quantity'] = - $tmp_data_reject['receive_quantity'];
                $tmp_data_reject['remark_data'] = ($tmp_data_reject['remark_data'] == "" || $tmp_data_reject['remark_data'] == " " || empty($tmp_data_reject['remark_data'])) ? 'Reject' : $tmp_data_reject['remark_data'];

                array_push($data_views, $tmp_data_reject);

                unset($tmp_data_reject);

            endif;


        endforeach;

       
        $view['file_name'] = 'grn_excel';

        // change header title : kik
        $view['header'] = array(
            "ASN DATE"
            , "PO.NO"
            , "Document_No"
            , "Doc_Refer_Int"
            , "Supplier"
            , "Item"
            , "Product Name"
            , "Qty. PO."
            , "Arrival Date"
            , "Receiving Date/Informed"
            , "Qty. Receive"
            , "Remark"
        );
        $view['body'] = $data_views;
    //    p($view['body']);exit;
        $this->load->view('excel_grn', $view);
    }

    # start report Daily Dispatching

    function DDR() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $report = '';
        $str_form = $this->parser->parse('form/ddr_report', array("data" => $report), TRUE);
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Daily Delivery Report ' . date("d/m/Y")
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_hide"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
        //END ADD
    }

    function searchDDR() {
        /* $search = $this->input->get();
          $data = $this->r->getDDR($search, (int) $search['iDisplayStart'], (int) $search['iDisplayLength']);
          $response = array();
          foreach ($data as $k => $v) :
          $response[] = array(($search['iDisplayStart'] + $k + 1)
          ,$v->Estimate_Action_Date
          ,$v->Doc_Refer_Int
          ,$v->Doc_Refer_Ext
          ,$v->consignee
          ,$v->supplier
          ,$v->Product_Code
          ,$v->Product_NameEN
          ,$v->Confirm_Qty
          ,$v->Remark
          );
          endforeach;
          $output = array(
          "sEcho" => intval($this->input->get('sEcho')),
          "iTotalRecords" => $this->r->count_ddr_report($search),
          "iTotalDisplayRecords" => count($data),
          "aaData" => $response
          );

          echo json_encode($output); */

        $search = $this->input->post();
        $iDisplayLength = (isset($search['iDisplayLength']) ? $search['iDisplayLength'] : NULL);
        $iDisplayStart = (isset($search['iDisplayStart']) ? $search['iDisplayStart'] : NULL);
        $length = ($iDisplayLength < 0 ? 999999 : (int) $iDisplayLength);
        $report = $this->r->getDDR($search, (int) $iDisplayStart, $length);
        // p($report);exit;
        $view['data'] = $report;
        $view['search'] = $search;
        $this->load->view("report/ddr_report.php", $view);
    }

    function exportDDRToPDF() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('mpdf/mpdf');
        $search = $this->input->post($search);
        $report = $this->r->getDDR($search);
        $view['data'] = $report;

        // Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        // END Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report

        $this->load->view("report/exportDDR", $view);
    }

    function exportDDRToExcel() {
        $search = $this->input->post();
        $report_list = $this->r->getDDR($search);

        //START BY POR 2013-10-09 เพิ่มเติมให้เลือกเฉพาะ column ที่ต้องการมาแสดง report
        $report = array();
        foreach ($report_list as $key => $r) {
            $report[$key]['Estimate_Action_Date'] = $r->Estimate_Action_Date;
            // $report[$key]['Doc_Refer_Ext'] = $r->Doc_Refer_Ext;
            $report[$key]['Document_No'] = $r->Document_No;
            $report[$key]['Doc_Refer_Int'] = $r->Doc_Refer_Int;
            $report[$key]['consignee'] = $r->consignee;
            $report[$key]['Product_Code'] = $r->Product_Code;
            $report[$key]['Product_NameEN'] = $r->Product_NameEN;
            //$report[$key]['Reserv_Qty'] = $r->Reserv_Qty; comment by por 2013-10-31 แก้ไขให้ส่งค่ากลับเป็น number_format
            $report[$key]['Reserv_Qty'] = array('align' => 'right', 'value' => set_number_format($r->Reserv_Qty));
            $report[$key]['Actual_Action_Date'] = $r->Actual_Action_Date;
            //$report[$key]['Confirm_Qty'] = $r->Confirm_Qty; comment by por 2013-10-31 แก้ไขให้ส่งค่ากลับเป็น number_format
            $report[$key]['Confirm_Qty'] = array('align' => 'right', 'value' => set_number_format($r->Confirm_Qty));
            $report[$key]['Remark'] = $r->Remark;
        }
        //END REPORT

        $view['file_name'] = 'ddr_report';
        $view['header'] = array(
            _lang('asn_date') //START BY POR 2013-10-09 แก้ไข Date เป็น
            , _lang('document_no') //START BY POR 2013-10-09 แก้ไข Supplier เป็น Document No
            , _lang('Document Refer Int')
            , _lang('consinee') //START BY POR 2013-10-09 เพิ่ม column นี้ใน excel
            , _lang('product_code')
            , _lang('product_name')
            , _lang('receive_qty') //START BY POR 2013-10-09 แก้ไข Reserv Quantity เป็น Reserv Qty
            , _lang('dispatch_date')
            , _lang('dispatch_qty') //START BY POR 2013-10-09 แก้ไข Dispatch Qty เป็น Dispatch Quantity
            , _lang('remark')
        );
        $view['body'] = $report;
        $this->load->view('excel_template', $view);
    }

    # start dispatch report

    function dispatch() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $conf = $this->config->item('_xml');
        $parameter['conf_change_dp_date'] = (!empty($conf['can_change_dispatch_date']) ? $conf['can_change_dispatch_date'] : false);
        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/dispatch_form_report", $parameter, TRUE);
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Dispatch '
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmDispatch"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
    }

    function dispatchReport() {
        $search = $this->input->post();

        $data = $this->r->search_dispatch($search);
        // p( $data); exit;
        $view['data'] = $data;
        $view['search'] = $search;
        $this->load->view("report/dispatch_report.php", $view);
    }

    function exportDispatchToPDF() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $search = $this->input->post($search);
        $report = $this->r->search_dispatch($search, 'PDF');
        // p($report); exit;
        $view['datas'] = $report;

        //เรียกชื่อของคนที่ออกรายงาน
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        $view['printBy'] = $printBy;

        $this->load->view("report/exportDispatch", $view);
    }

    function exportDispatchToExcel() {
        $search = $this->input->post();
        $report = $this->r->search_dispatch($search);
        $view['search'] = $search;
        $view['file_name'] = 'dispatch_report';
        $view['header'] = array(
            _lang('date')
            , _lang('document_no')
            , _lang('document_ext')
	        , _lang('doc_refer_int')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('lot')
            , _lang('Mfd_Date')
            , _lang('serial')
            , _lang('quantity')
            , _lang('unit')
            , _lang('from')
            , _lang('to')
            , _lang('Description')
            , _lang('remark')
        );

        // Add By Akkarapol, 06/02/2014, เพิ่มการตรวจสอบว่าถ้า config.build_pallet = TRUE ให้ทำการ แทรก Pallet Code เข้าไปก่อน Remark
        if ($this->config->item('build_pallet')):
            array_splice($view['header'], array_search('Remark', $view['header']), 0, array('Pallet Code'));
        endif;
        // END Add By Akkarapol, 06/02/2014, เพิ่มการตรวจสอบว่าถ้า config.build_pallet = TRUE ให้ทำการ แทรก Pallet Code เข้าไปก่อน Remark

        $view['body'] = $report;
        $this->load->view('report/dispatch_excel_template', $view);
    }

    # start report Aging

    function aging() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $this->load->model("company_model", "company");
        $this->load->model("warehouse_model", "w");
        $this->load->model("product_model", "p");
        $parameter['renter_id'] = $this->config->item('renter_id');
        $parameter['consignee_id'] = $this->config->item('owner_id');
        $parameter['owner_id'] = $this->config->item('owner_id');

        #Get renter list
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE);
        $parameter['renter_list'] = $renter_list;

        #Get warehouse list
        $q_warehouse = $this->w->getWarehouseList();
        $r_warehouse = $q_warehouse->result();
        $warehouse_list = genOptionDropdown($r_warehouse, "WH");
        $parameter['warehouse_list'] = $warehouse_list;

        # Get Product Category
        $q_category = $this->p->productCategory();
        $r_category = $q_category->result();
        $category_list = genOptionDropdown($r_category, "SYS");
        $parameter['category_list'] = $category_list;

        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/aging_report", $parameter, TRUE);
        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Aging'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form

            // Add By Akkarapol, 24/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPreDispatch"></i>'
            // END Add By Akkarapol, 24/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
    }

    function showAgingReport() {
        /*
          $renter_id=$this->input->post("renter_id");
          $warehouse_id=$this->input->post("warehouse_id");
          $category_id=$this->input->post("category_id");
          $product_id=$this->input->post("product_id");
          $as_date=$this->input->post("as_date");
          $period=$this->input->post("period");
          $step=$this->input->post("step");
          $by=$this->input->post("by");
         */
        $search = $this->input->post();
        //p($search);
        $age_rang = $this->r->getAgeRange($search['by'], $search['period'], $search['step'], $search['product_id']);
        $view['range'] = $age_rang;
        $view['search_by'] = $search['by'];
        $data = $this->r->searchAging($search);
        $view['data'] = $data;
        $view['search'] = $search;
        $this->load->view("report/aging_report.php", $view);
    }

    //--#ISSUE NO:2236/DEFECT 328
    //--#DATE:2013-08-26
    //--#BY:POR
    #-- START--
    function showAgingReportReceiveApp() {






        //exit();

        $search = $this->input->post();
        $age_rang = $this->r->getAgeRange($search['by'], $search['period'], $search['step'], $search['product_id']);
        $view['range'] = $age_rang;
        $view['search_by'] = $search['by'];
        $data = $this->r->searchAgingReceiveApp($search);
        $view['data'] = $data;
        $view['search'] = $search;
        $this->load->view("report/aging_report.php", $view);
    }

    #-- END #ISSUE NO:2236/DEFECT 328--

    function exportAgingPdf() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $search = $this->input->post();
        unset($search['tbreport_length']);
        $age_rang = $this->r->getAgeRange($search['by'], $search['period'], $search['step'], $search['product_id']);
        $view['range'] = $age_rang;
        $view['search_by'] = $search['by'];

//        $data = $this->r->search_aging_detail($search); // Comment By Akkarapol, 24/10/2013, ใน PDF จะต้องแสดง Report หน้าตาเหมือนกับหน้าเว็บไซต์ จึงต้องเปลี่ยนไปใช้การ query ด้วยฟังก์ชั่น searchAgingReceiveApp แทน
        $data = $this->r->searchAgingReceiveApp($search); // Add By Akkarapol, 24/10/2013, ใน PDF จะต้องแสดง Report หน้าตาเหมือนกับหน้าเว็บไซต์ จึงต้องเปลี่ยนมาใช้การ query ด้วยฟังก์ชั่น searchAgingReceiveApp แทน

        $view['data'] = $data;
        $view['search'] = $search;

        // Add By Akkarapol, 22/10/2013, เพิ่มโค๊ดในส่วนของการ query ชื่อ ของผู้ที่ทำการออก Report นี้
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        // END Add By Akkarapol, 22/10/2013, เพิ่มโค๊ดในส่วนของการ query ชื่อ ของผู้ที่ทำการออก Report นี้



        $this->load->view("report/exportAgingPdf.php", $view);
    }

    function exportAgingToExcel() {
        $search = $this->input->post();
        $age_rang = $this->r->getAgeRange($search['by'], $search['period'], $search['step'], $search['product_id']);
        $view['range'] = $age_rang;
        $column_name = array_keys($age_rang);
//        p($column_name);
        $view['header'] = $column_name;

        $view['search_by'] = $search['by'];
//        $data = $this->r->search_aging_detail($search);
        $result = $this->r->searchAgingReceiveApp($search);
//        p($result);exit();

        $data = array();
        foreach ($result as $row) {//p($row);exit();
            $r = array();
            $r['Product_Code'] = $row->Product_Code;
            $r['Product_NameEN'] = $row->Product_NameEN;
            $r['Sum_Row'] = 0;

            $i = 1;
            $sum_row = 0;
            foreach ($age_rang as $key => $value) {
                $r['count_' . $i] = $row->{'counts_' . $i};
                $sum_row+=$row->{'counts_' . $i};
                $r['Sum_Row'] = $sum_row;
                $i++;
            }
            $data[$row->Product_Code . ' ' . $row->Product_NameEN][] = $r;
        }

//        p($data);exit();
        $view['body'] = $data;
        $view['search'] = $search;

        $view['file_name'] = 'aging_report';

        //$view['body']  =  $report;
        $this->load->view('report/aging_excel_template', $view);
    }

//     Backup function for fix :excel aging report not same excel and view on web page : kik
//    function exportAgingToExcel() {
//        $search = $this->input->post();
//        $age_rang = $this->r->getAgeRange($search['by'], $search['period'], $search['step'], $search['product_id']);
//        $view['range'] = $age_rang;
//        $column_name = array_keys($age_rang);
//        //p($column_name);
//        $view['header'] = $column_name;
//
//        $view['search_by'] = $search['by'];
//        $data = $this->r->search_aging_detail($search);
//        //p($data);
//        $view['body'] = $data;
//        $view['search'] = $search;
//
//        $view['file_name'] = 'aging_report';
//
//        //$view['body']  =  $report;
//        $this->load->view('report/aging_excel_template', $view);
//    }
    # end aging report
    # start expired report

    function expire() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $this->load->model("company_model", "company");
        $this->load->model("warehouse_model", "w");
        $this->load->model("product_model", "p");
        $parameter['renter_id'] = $this->config->item('renter_id');
        $parameter['consignee_id'] = $this->config->item('owner_id');
        $parameter['owner_id'] = $this->config->item('owner_id');

        #Get renter list
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE);
        $parameter['renter_list'] = $renter_list;

        #Get warehouse list
        $q_warehouse = $this->w->getWarehouseList();
        $r_warehouse = $q_warehouse->result();
        $warehouse_list = genOptionDropdown($r_warehouse, "WH");
        $parameter['warehouse_list'] = $warehouse_list;

        # Get Product Category
        $q_category = $this->p->productCategory();
        $r_category = $q_category->result();
        $category_list = genOptionDropdown($r_category, "SYS");
        $parameter['category_list'] = $category_list;

        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/expire_form", $parameter, TRUE);
        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Expire'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form

            // Add By Akkarapol, 24/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPreDispatch"></i>'
            // END Add By Akkarapol, 24/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
        ));
    }

    function showExpireReport() {
        $search = $this->input->get();
        $length = ($search['iDisplayLength'] < 0 ? 999999 : (int) $search['iDisplayLength']);
        $data = $this->r->show_expire(TRUE, $search, (int) $search['iDisplayStart'], $length);
        $response = array();
        foreach ($data as $k => $v) :
            $response[] = array(($search['iDisplayStart'] + $k + 1)
                , $v->Product_Code
                , thai_json_encode($v->Product_NameEN)
                , (($v->Product_Lot != " ") ? thai_json_encode($v->Product_Lot) : "-") . "/" . (($v->Product_Serial != " ") ? thai_json_encode($v->Product_Serial) : "-")
                , $v->Product_Exp
                , $v->Remain_day
                , $v->Pallet_Code // Add By Akkarapol, 31/01/2014, Add ', $v->Pallet_Code' into $response[] for generate dataTable in view
                , set_number_format($v->Balance)
            );
        endforeach;

        if ($search['sSearch']) :
            $iTotalDisplayRecords = count($data);
        else :
            //$iTotalDisplayRecords = $this->r->count_expire_report($search);
            $iTotalDisplayRecords = count($this->r->show_expire(FALSE, $search)); //EDIT BY POR 2014-03-20 Change to use single query
        endif;

        $output = array(
            "sEcho" => intval($this->input->get('sEcho')),
            //"iTotalRecords" => $this->r->count_expire_report($search),
            "iTotalRecords" => count($this->r->show_expire(FALSE, $search)), //EDIT BY POR 2014-03-20 Change to use single query
            "iTotalDisplayRecords" => $iTotalDisplayRecords,
            "aaData" => $response
        );

        echo json_encode($output);
        /*
         * exit();
          $view['data'] = $data;
          $view['search'] = $search;
          $this->load->view("report/expire_report.php", $view);
         */
    }

    function exportExpiredPdf() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $search = $this->input->post();
        $data = $this->r->show_expire_detail($search);
//		p($data);exit();
        $view['data'] = $data;


        // Add By Akkarapol, 24/10/2013, เพิ่มโค๊ดในส่วนของการ query ชื่อ ของผู้ที่ทำการออก Report นี้
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        // END Add By Akkarapol, 24/10/2013, เพิ่มโค๊ดในส่วนของการ query ชื่อ ของผู้ที่ทำการออก Report นี้


        $this->load->view("report/exportExpiredPdf.php", $view);
    }

    function exportExpiredToExcel() {
        $search = $this->input->post();
        $data = $this->r->show_expire_detail($search);
        $view['body'] = $data;
        $view['header'] = array(
            _lang('no')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('lot_serial')
            , _lang('warehouse')
            , _lang('zone')
            , _lang('location')
            , _lang('expire_date')
            , _lang('remain_days')
            , _lang('pallet_code')
            , _lang('qty')
        );
        $view['file_name'] = 'expired_report';

        //$view['body']  =  $report;
        $this->load->view('report/expired_excel_template', $view);
    }

    # end expire report

    function confirm_location() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $parameter = array();
        $parameter['user_id'] = $this->session->userdata('user_id');

        $str_form = $this->parser->parse('form/location_pt_report', array("data" => $parameter, "test_parse" => "teat pass by parse"), TRUE);

        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Confirm Location'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPreDispatch"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" /><input type="button" value="Print" class="button dark_blue" id="printshow" style="display:none;" onClick="exportFile(' . "'PRINT'" . ')"  />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
        ));
    }

    /**
     * Search function Put Away Function
     */
    public function searchPTLocation() {
        $search = $this->input->post();
        $data = $this->r->show_pt_location($search);
        $view['data'] = $data;
        // p($view['data'] );exit;
        $view['search'] = $search;

        // Add By Akkarapol, 10/09/2013, เช็คว่า ถ้าเป็น Document_No ถึงจะให้แสดงปุ่ม Print
        $forShowButtonPrint = array('Document_No');
        $view['showButtonPrint'] = (!empty($search['doc_value'])) ? (in_array($search['doc_type'], $forShowButtonPrint)) ? '1' : '0' : '0';
        // END Add By Akkarapol, 10/09/2013, เช็คว่า ถ้าเป็น Document_No ถึงจะให้แสดงปุ่ม Print

        $view['statusprice'] = $this->settings['price_per_unit']; //ADD BY POR 2014-06-20 ADD Price per unit

        $this->load->view("report/location_pt_report.php", $view);
    }

    function exportCmLocationPdf() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $search = $this->input->post();
        $data = $this->r->show_pt_location_export($search);
        $view['data'] = $data;

        //Start Add by Por 2013-10-14 เรียกชื่อของคนที่ออกรายงาน
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        $view['printBy'] = $printBy;
        //END
        //Comment BY Por 2013-10-14 ไปเรียกใช้ _lang ที่กิ๊กสร้างไว้แทน
        /*
          $view['column'] = array(
          "Date"
          , "Document No"
          , "Product Code"
          , "Product Name"
          , "Product Status"
          , "Product Lot"
          , "Product Serial"
          , "Quantity"
          , "Unit"
          , "Suggest Location"
          , "Actual Location"
          , "By"
          , "Remark"
          );
         */
        $view['activity'] = $search['activity'];
        $view['statusprice'] = $this->settings['price_per_unit']; //ADD BY POR 2014-06-20 ADD Price per unit
//        p($view);
//        exit();
        $this->load->view("report/export_cfl_pdf.php", $view);
    }

    function exportCmLocationPrint() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $search = $this->input->post();
        $data = $this->r->show_pt_location_export($search);
        $view['data'] = $data;
        $view['column'] = array(
            "ลำดับ"
            , "รหัสสินค้า"
            , "ชื่อสินค้า"
            , "Serial No. & Lot"
            , "วันผลิต"
            , "วันหมดอายุ"
            , "จำนวน"
            , "Location"
        );
//        p($data);

        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;

        $spl = split('/', $data[0]->Receive_Date);
        $dateTime = date('d/F/Y', mktime(0, 0, 0, $spl[1], $spl[0], $spl[2])); // Edit By Akkarapol, 11/09/2013, แก้จาก mktime(0, 0, 0, $spl[0], $spl[1], $spl[2]) เป็น mktime(0, 0, 0, $spl[1], $spl[0], $spl[2]) เนื่องจาก ใช้แบบ 0 1 2 หรือก็คือ วัน เดือน ปี เวลาเข้า ฟังก์ชั่น mktime แล้วค่าออกมาผิด
        $view['Receive_Date'] = $dateTime;

        $this->load->view("report/export_cfl_print.php", $view);
    }

    // Add By Akkarapol, 18/10/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ Print ใบ Relocation Job
    function exportReLocationPDF() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $search = $this->input->post();
//       p($search);
//       exit();
//
        // Add By Akkarapol, 06/05/2014, เพิ่มการ set show_column_relocation_job ที่เก็บไว้ใน cookie เพื่อเก็บไว้ว่า ต้องการใช้ column ไหนบ้างในการแสดงผล โดยจะทำการ serialize ก่อนทำการ set
        $this->load->helper('cookie');
        $cookie = array(
            'name' => 'show_column_relocation_job',
            'value' => serialize($search['relocation_job']),
            'expire' => '0'
        );
        set_cookie($cookie);
        // Add By Akkarapol, 06/05/2014, เพิ่มการ set show_column_relocation_job ที่เก็บไว้ใน cookie เพื่อเก็บไว้ว่า ต้องการใช้ column ไหนบ้างในการแสดงผล โดยจะทำการ serialize ก่อนทำการ set
        //ADD BY POR 2014-03-11 รับข้อมูลหน้า form มาเพื่อแสดงข้อมูลแบบ realtime
        $prod_list = $search["prod_list"];

        # Parameter Order relocation header
        $est_relocate_date = $search["est_relocate_date"];
        $relocate_date = $search["relocate_date"];
        $worker_id = $search["worker_id"];
        $relocation_no = $search["relocation_no"];

        # Parameter Index Datatable
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_product_status = $this->input->post("ci_product_status");
        $ci_product_sub_status = $this->input->post("ci_product_sub_status");
        $ci_product_lot = $this->input->post("ci_product_lot");
        $ci_product_serial = $this->input->post("ci_product_serial");
        $ci_reserv_qty = $this->input->post("ci_est_balance");

        $ci_confirm_qty = $this->input->post("ci_reserv_qty");
        $ci_old_loc_id = $this->input->post("ci_old_loc_id");
        $ci_sug_loc_id = $this->input->post("ci_sug_loc_id");
        $ci_act_loc_id = $this->input->post("ci_act_loc_id");
        $ci_remark = $this->input->post("ci_remark");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_dp_type_pallet = $this->input->post("ci_dp_type_pallet");
        $ci_old_loc_name = $this->input->post("ci_old_loc_name");
        $ci_sug_location_name = $this->input->post("ci_sug_location_name");
        $ci_act_loc_name = $this->input->post("ci_act_loc_name");
        $ci_actual_loc = $this->input->post("ci_actual_loc"); //actual_location จาก relocation by location
        $ci_old_loc_name = $this->input->post("ci_old_loc_name"); //location เดิมก่อนที่จะย้าย

        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }

        #END ADD

        $data = $this->reLocation->getRelocationOrder($search["flow_id"]);

        $view['data'] = $data;
        $view['text_header'] = 'Relocation Job';

        //ADD BY POR 2014-06-12 ดึง actual location แบบ realtime
        if (!empty($data)):
            //if($ci_item_id != 0):

            $prod_list2 = $this->reLocation->getReLocationProductDetail($data[0]->Order_Id);

            if (!empty($prod_list2)):
                $data_views = array();
                foreach ($prod_list2 as $detail):

                    foreach ($prod_list as $detail2):
                        $k_data = explode(SEPARATOR, $detail2);

                        if ($ci_item_id != 0):    //กรณี relocation by product จะต้องให้ act_location,remark,confirm_qty แสดงแบบ realtime
                            if ($detail['item_id'] == $k_data[$ci_item_id]):
                                $detail['act_location'] = $k_data[$ci_act_loc_name];
                                $detail['remark'] = $k_data[$ci_remark];
                                $detail['confirm_qty'] = str_replace(",", "", $k_data[$ci_confirm_qty]);

                                //ADD BY POR 2014-06-16 เพิ่มเติมกรณี price per unit
                                if ($this->settings['price_per_unit'] == TRUE):
                                    $detail['Price_Per_Unit'] = str_replace(",", "", $k_data[$ci_price_per_unit]);
                                    $detail['Unit_Price_Id'] = $k_data[$ci_unit_price_id];
                                    $detail['All_Price'] = str_replace(",", "", $k_data[$ci_all_price]);
                                endif;
                                //END ADD

                                array_push($data_views, $detail);
                                break;
                            endif;
                        else: //กรณี relocation by location
                            if ($k_data[$ci_old_loc_name] == $detail['from_location']): //ถ้า location เดียวกันให้ย้ายไปที่เดียวกัน
                                $detail['confirm_qty'] = str_replace(",", "", $detail['reserv_qty']); //กำหนดค่า confirm_qty = reserv_qty
                                $detail['act_location'] = $k_data[$ci_act_loc_name]; //กำหนดค่า act_location = ค่าที่เลือกมา
                                $detail['remark'] = $k_data[$ci_remark];
                                array_push($data_views, $detail);
                            endif;
                        endif;
                    endforeach;

                endforeach;
                $prod_list = $data_views;

            endif;

//            else:
//                $data_views = array();
//                foreach($data as $detail2):
//                    $k_data = explode(SEPARATOR, $detail2);
//
//                    if($detail['item_id'] == $k_data[$ci_item_id]):
//                        $detail['act_location'] = $k_data[$ci_act_loc_name];
//                        $detail['remark'] = $k_data[$ci_remark];
//                        $detail['confirm_qty'] = $k_data[$ci_confirm_qty];
//                        array_push($data_views,$detail);
//                        break;
//                    endif;
//                endforeach;
//
//                $prod_list=$data_views;
//            endif;

        endif;
        //END ADD
//        if(!empty($data)):
//            //BY POR 2014-03-17 แสดง Actual Location To  และ remark  แบบ realtime
//            if(!empty($prod_list)):
//
//                foreach ($prod_list as $key):
//
//                    $k_data = explode(SEPARATOR, $key);
//                    $actual_location = $k_data[$ci_act_loc_name];
//                    $remark = $k_data[$ci_remark];
//                endforeach;
//            endif;
//
//            $prod_list = $this->reLocation->getReLocationProductDetail($data[0]->Order_Id);   //COMMENT BY POR 2014-03-12 กรณีเรียกข้อมูลมาแสดงโดยผ่าน database แต่เนื่องจากเปลี่ยนเป็นแบบ realtime เลย comment ส่วนนี้ไว้
//        endif;
        //นำ detail ที่ได้มาแทนค่าในตัวแปรตาม index ที่ต้องการ
        if (!empty($prod_list)) {
            $order_detail = array();
            foreach ($prod_list as $key => $rows) {
                //p($rows);
                if (is_array($rows)):
                    $a_data = $rows;
                else:
                    $a_data = explode(SEPARATOR, $rows);
                endif;
                //p($rows);
                //หารายละเอียดอื่นๆ ที่ไม่สามารถนำค่ามาแสดงได้ จึงต้องหาใน function เพิ่มเติม เช่น Product_Name เนื่องจากหน้า from แสดงไม่เต็มจึงต้องหาค่าใหม่, Location From และ Suggest Location เนื่องจากค่าที่ได้มาติดรูปแว่นขยาย จึงต้องหาค่าใหม่
                $pro_detail = $this->pending->getRelocateDetail()->result_array((!empty($a_data[$ci_item_id]) ? $a_data[$ci_item_id] : $rows['item_id']));
                $order_detail[$key]['product_code'] = (empty($data) ? $a_data[$ci_prod_code] : $rows['product_code'] );
                $order_detail[$key]['product_name'] = (empty($data) ? $pro_detail[0]["Product_NameEN"] : $rows['product_name']);
                $order_detail[$key]['product_status'] = (empty($data) ? $a_data[$ci_product_status] : $rows['product_status']);
                $order_detail[$key]['Product_Sub_Status'] = (empty($data) ? $pro_detail[0]["Sub_Status_Value"] : $rows['Product_Sub_Status']);
                $order_detail[$key]['product_lot'] = (empty($data) ? $a_data[$ci_product_lot] : $rows['product_lot']);
                $order_detail[$key]['product_serial'] = (empty($data) ? $a_data[$ci_product_serial] : $rows['product_serial']);

                $order_detail[$key]['product_exp'] = (empty($data) ? $pro_detail[0]["P_Exp"] : $rows['product_exp']);
                $order_detail[$key]['reserv_qty'] = (empty($data) ? $a_data[$ci_reserv_qty] : $rows['reserv_qty']);
                $order_detail[$key]['confirm_qty'] = (empty($data) ? $a_data[$ci_confirm_qty] : $rows['confirm_qty']); //ถ้ากรณี re-location by location จะ confirm เท่ากับจำนวน reserv เนื่องจากย้ายไปทั้งหมด

                if ($this->settings['price_per_unit'] == TRUE):
                    $order_detail[$key]['Price_Per_Unit'] = (empty($data) ? $a_data[$ci_price_per_unit] : $rows['Price_Per_Unit']);
                    $order_detail[$key]['Unit_Price_value'] = (empty($data) ? $a_data[$ci_unit_price] : $rows['Unit_Price_Id']); // Add By Akkarapol, 10/01/2014, เพิ่มการเซ็ตค่า $pending_detail[$key]['Unit_Value'] = $a_data[$ci_unit_value]; เพื่อนำไปแสดงผลต่อด้วย Unit_Value
                    $order_detail[$key]['All_Price'] = (empty($data) ? $a_data[$ci_all_price] : $rows['All_Price']);
                endif;

                $order_detail[$key]['from_location'] = (empty($data) ? $a_data[$ci_old_loc_name] : $rows['from_location']);
                $order_detail[$key]['to_location'] = (empty($data) ? $a_data[$ci_sug_location_name] : $rows['to_location']);
//                p($actual_location);
//                p($a_data[$ci_act_loc_name]);
                $order_detail[$key]['act_location'] = (empty($data) ? $a_data[$ci_act_loc_name] : $rows['act_location']);
                $order_detail[$key]['remark'] = (empty($data) ? $a_data[$ci_remark] : $rows['remark']);
            }
            //exit();
        }

        $view['order_detail'] = $order_detail;
        //END POR ADD
        #check if price_per_unit for show column Price / Unit,Unit Price,All Price
        // Edit By Akkarapol, 29/04/2014, add index ของ array  $view['column']  เพื่อนำไปใช้งานในส่วนของการทำ show/hide column
        if ($this->settings['price_per_unit'] == TRUE):
            $view['column'] = array(
                "no" => _lang('no')
                , "product_code" => _lang('product_code')
                , "product_name" => _lang('product_name')
                , "product_status" => _lang('product_status')
                , "product_sub_status" => _lang('product_sub_status')
                , "lot" => _lang('lot')
                , "serial" => _lang('serial')
//                , "exp" => _lang('product_exp')
                , "move_qty" => _lang('move_qty')
                , "confirm_qty" => _lang('confirm_qty')
                , "price_per_unit" => _lang('price_per_unit')
                , "unit_price" => _lang('unit_price')
                , "all_price" => _lang('all_price')
                , "location_form" => _lang('from_location')
                , "suggest_location" => _lang('suggest_location')
                , "actual_location" => _lang('actual_location')
                , "remark" => _lang('remark')
            );
        else:
            $view['column'] = array(
                "no" => _lang('no')
                , "product_code" => _lang('product_code')
                , "product_name" => _lang('product_name')
                , "product_status" => _lang('product_status')
                , "product_sub_status" => _lang('product_sub_status')
                , "lot" => _lang('lot')
                , "serial" => _lang('serial')
//                , "exp" => _lang('product_exp')
                , "move_qty" => _lang('move_qty')
                , "confirm_qty" => _lang('confirm_qty')
                , "location_form" => _lang('form_location')
                , "suggest_location" => _lang('suggest_location')
                , "actual_location" => _lang('actual_location')
                , "remark" => _lang('remark')
            );
        endif;

        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        $view['price_per_unit'] = $this->settings['price_per_unit']; //add by kik : 20140120
        // Add By Akkarapol, 29/04/2014, เพิ่มส่วนสำหรับจัดการ show/hide column ใน PDF ที่เป็นส่วนของ CSS, class div, class td, colspan และส่วนที่เกี่ยวข้อง

        $all_column = array(
            "no" => _lang('no')
            , "product_code" => _lang('product_code')
            , "product_name" => _lang('product_name')
            , "product_status" => _lang('product_status')
            , "product_sub_status" => _lang('product_sub_status')
            , "lot" => _lang('lot')
            , "serial" => _lang('serial')
            , "move_qty" => _lang('move_qty')
            , "confirm_qty" => _lang('confirm_qty')
            , "price_per_unit" => _lang('price_per_unit')
            , "unit_price" => _lang('unit_price')
            , "all_price" => _lang('all_price')
            , "location_form" => _lang('from_location')
            , "suggest_location" => _lang('suggest_location')
            , "actual_location" => _lang('actual_location')
            , "remark" => _lang('remark')
        );

        $set_css_for_show_column = '';
        $count_colspan = 0;
        $count_colspan_after_sum = 0;

        if (!empty($this->settings['show_column_report']['object']['relocation_job'])):
            $set_colspan = TRUE;
            $set_colspan_after_sum = FALSE;
            foreach ($all_column as $key_show_column => $show_column):
                if ($key_show_column == 'move_qty'):
                    $set_colspan = FALSE;
                endif;

                $tmp_chk_in_array = (@$this->settings['show_column_relocation_job']) ? array_key_exists($key_show_column, $search['relocation_job']) : TRUE;

                if ($set_colspan && $tmp_chk_in_array):
                    $count_colspan += 1;
                endif;
                if ($set_colspan_after_sum && $tmp_chk_in_array):
                    if ($this->config->item('build_pallet')):
                        $count_colspan_after_sum += 1;
                    else:
                        if ($key_show_column != 'pallet_code'):
                            $count_colspan_after_sum += 1;
                        endif;
                    endif;
                endif;
                if ($key_show_column == 'confirm_qty'):
                    $set_colspan_after_sum = TRUE;
                endif;
                if (!$tmp_chk_in_array):
                    if ($set_css_for_show_column == ''):
                        $set_css_for_show_column .= " .{$key_show_column}";
                    else:
                        $set_css_for_show_column .= ", .{$key_show_column}";
                    endif;
                endif;
            endforeach;
            if ($set_css_for_show_column != ''):
                $set_css_for_show_column .= "{display:none;}";
            endif;
            $count_colspan_after_sum = ($this->settings['price_per_unit'] == TRUE ? $count_colspan_after_sum : 4);
        else :
            $count_colspan = 7;
            $count_colspan_after_sum = ($this->settings['price_per_unit'] == TRUE ? 7 : 4);
        endif;

        $view['count_colspan'] = $count_colspan;
        $view['count_colspan_after_sum'] = $count_colspan_after_sum;
        $view['set_css_for_show_column'] = $set_css_for_show_column;

        // END Add By Akkarapol, 29/04/2014,  เพิ่มส่วนสำหรับจัดการ show/hide column ใน PDF ที่เป็นส่วนของ CSS, class div, class td, colspan และส่วนที่เกี่ยวข้อง

        $detail = $this->user->getDetailByUserId($search['worker_id'], 'user');
        $view['workerName'] = $detail['First_NameTH'] . ' ' . $detail['Last_NameTH'];
        $view['est_relocate_date'] = $est_relocate_date;
        $view['relocate_date'] = $relocate_date;
        $view['relocation_no'] = $relocation_no;
        $this->load->view("report/export_relocation_print.php", $view);
    }

    // END Add By Akkarapol, 18/10/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ Print ใบ Relocation Job
    // Add By Akkarapol, 22/10/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ Print ใบ Picking Job
    function export_picking_pdf() {

        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('mpdf/mpdf');

        $this->settings['price_per_unit'] = FALSE; // force set price per unit is FALSE because operation can't see price of item

        $search = $this->input->get();

        if ($search['o'] == "location") :
            $this->export_picking_pdf_by_location($search);
        else:
            $this->export_picking_pdf_by_other($search);
        endif;
    }

    function export_picking_pdf_by_location($search = NULL) {
//        p($search);
        #Load config
        $conf = $this->config->item('_xml');

        $column_report = empty($conf['show_column_report']['object']['picking_job']) ? false : @$conf['show_column_report']['object']['picking_job'];
        $conf_uom_qty = ($column_report['uom_qty']['value']) ? true : false;
        $conf_uom_unit_prod = ($column_report['uom_unit_prod']['value']) ? true : false;


        if (empty($search)):
            $search = $this->input->post();
            if (!isset($search['document_no'])):
                $flow_detail = $this->flow->getFlowDetail($search['flow_id'], 'STK_T_Order');
            else:
                $order_data = $this->flow->getOrderDetailByDocumentNo($search['document_no']);
                if (empty($order_data)):
                    echo 'Sorry, can not find data.';
                    exit();
                endif;
                $search['flow_id'] = $order_data[0]->Flow_Id;
                $flow_detail = $this->flow->getFlowDetail_abnormal_flow($search['flow_id'], 'STK_T_Order');
            endif;
        else:
            $search = $this->input->get();
            $flow_detail = $this->flow->getFlowDetail_abnormal_flow($search['flow_id'], 'STK_T_Order');
        endif;
//        p($flow_detail);
        $data = $flow_detail[0];

        #add for fix defect #461 picking by in transfer stock order not show : by kik : 20140206
        if ($data->Process_Id == 2):
            $state_edge = 14;
        else:
            $state_edge = 58;
        endif;
        #end add for fix defect #461 picking by in transfer stock order not show : by kik : 20140206

        $tmp = $this->company->getCompanyByID($data->Renter_Id);
        $tmp = $tmp->row_array();
        $data->Renter_Name = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($data->Source_Id);
        $tmp = $tmp->row_array();
        $data->Source_Name = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($data->Destination_Id);
        $tmp = $tmp->row_array();
        $data->Consignee_Name = $tmp['Company_NameEN'];

        $tmp = $this->sys->getDomDetailByDomCode($data->Doc_Type);
        $tmp = $tmp->row_array();
        $data->Dispatch_Type = $tmp['Dom_EN_Desc'];

        $view['data'] = $data;

        $order_by = "L1.Location_Code ASC";
        if ($this->config->item('build_pallet')):
            $order_by .= ", a.Pallet_Id ASC";
        endif;

        $module_activity = 'picking';
        $sub_module_activity = $this->order_movement->get_submodule_activity($data->Order_Id, $module_activity, 'cf_pick_by');

        $order_detail_data = $this->pre_dispatch->query_from_OrderDetailId($data->Order_Id, $order_by, false, $sub_module_activity, $module_activity)->result();

        foreach ($order_detail_data as $key_detail => $detail):
            $view['order_detail'][$detail->Suggest_Location][$key_detail]['product_code'] = $detail->Product_Code;
            $view['order_detail'][$detail->Suggest_Location][$key_detail]['product_name'] = $detail->Full_Product_Name;
            $view['order_detail'][$detail->Suggest_Location][$key_detail]['product_status'] = $detail->Status_Value;
            $view['order_detail'][$detail->Suggest_Location][$key_detail]['product_sub_status'] = $detail->Sub_Status_Value;
            $view['order_detail'][$detail->Suggest_Location][$key_detail]['product_lot'] = $detail->Product_Lot;
            $view['order_detail'][$detail->Suggest_Location][$key_detail]['product_serial'] = $detail->Product_Serial;
            $view['order_detail'][$detail->Suggest_Location][$key_detail]['product_mfd'] = $detail->Product_Mfd;
            $view['order_detail'][$detail->Suggest_Location][$key_detail]['product_exp'] = $detail->Product_Exp;
            $view['order_detail'][$detail->Suggest_Location][$key_detail]['reserv_qty'] = $detail->Reserv_Qty;
            $view['order_detail'][$detail->Suggest_Location][$key_detail]['confirm_qty'] = $detail->Confirm_Qty;
//            $view['order_detail'][$detail->Suggest_Location][$key_detail]['unit'] = $detail->Unit_Id;
            $view['order_detail'][$detail->Suggest_Location][$key_detail]['unit'] = $detail->Unit_Value; // Add By Akkarapol, 10/01/2014, เปลี่ยนจากการแสดง Unit ด้วย Unit_Id ซึ่งเป็นการเก็บข้อมูลที่ผิดอยู่ มาเป็นแสดงด้วย Unit_Value ที่ได้ถูกดึงค่ามาจากการทำ UOM แล้ว

            if ($detail->Unit_Id != NULL and $detail->Reserv_Qty != NULL and $detail->Standard_Unit_In_Id != NULL):
                $view['order_detail'][$detail->Suggest_Location][$key_detail]['uom_qty'] = $this->r->convert_uom($detail->Unit_Id, $detail->Reserv_Qty, $detail->Standard_Unit_In_Id);
            else:
                $view['order_detail'][$detail->Suggest_Location][$key_detail]['uom_qty'] = $detail->Uom_Qty;
            endif;

            $view['order_detail'][$detail->Suggest_Location][$key_detail]['uom_unit_prod'] = $detail->Uom_Unit_Val;

            if ($this->settings['price_per_unit'] == TRUE):
                $view['order_detail'][$detail->Suggest_Location][$key_detail]['price_per_unit'] = $detail->Price_Per_Unit;
                $view['order_detail'][$detail->Suggest_Location][$key_detail]['unit_price_value'] = $detail->Unit_Price_value;
                $view['order_detail'][$detail->Suggest_Location][$key_detail]['all_price'] = $detail->All_Price;
            endif;

            $view['order_detail'][$detail->Suggest_Location][$key_detail]['suggest_location'] = $detail->Suggest_Location;
            $view['order_detail'][$detail->Suggest_Location][$key_detail]['actual_location'] = $detail->Actual_Location;
            $view['order_detail'][$detail->Suggest_Location][$key_detail]['pick_by'] = $detail->Activity_By_Name;
            $view['order_detail'][$detail->Suggest_Location][$key_detail]['remark'] = $detail->Remark;

            if ($detail->Actual_Location == "" || $detail->Actual_Location == NULL):
                $view['order_detail'][$detail->Suggest_Location][$key_detail]['pick_by'] = "";
            else:
                $view['order_detail'][$detail->Suggest_Location][$key_detail]['pick_by'] = $detail->Activity_By_Name;
            endif;

            if ($this->config->item('build_pallet')):
                $view['order_detail'][$detail->Suggest_Location][$key_detail]['pallet_code'] = $detail->Pallet_Code;
            endif;

        endforeach;

        $view['text_header'] = 'Picking Job';

        if ($this->settings['price_per_unit'] == TRUE):
            $view['column'] = array(
                'no' => _lang('no')
                , 'product_code' => _lang('product_code')
                , 'product_name' => _lang('product_name')
                , 'product_status' => _lang('product_status')
                , 'product_sub_status' => _lang('product_sub_status')
                , 'lot' => _lang('lot')
                , 'serial' => _lang('serial')
                , 'mfd' => _lang('product_mfd')
                , 'exp' => _lang('product_exp')
                , 'reserve_qty' => _lang('reserve_qty')
                , 'confirm_qty' => _lang('confirm_qty')
                , 'unit' => _lang('unit')
                , 'uom_qty' => _lang('uom_qty')
                , 'uom_unit_prod' => _lang('uom_unit_prod')
                , 'price_per_unit' => _lang('price_per_unit')
                , 'unit_price' => _lang('unit_price')
                , 'all_price' => _lang('all_price')
                , 'suggest_location' => _lang('suggest_location')
                , 'actual_location' => _lang('actual_location')
                , 'pick_by' => _lang('pick_by')
                , 'remark' => _lang('remark')
            );

        else:
            $view['column'] = array(
                'no' => _lang('no')
                , 'product_code' => _lang('product_code')
                , 'product_name' => _lang('product_name')
                , 'product_status' => _lang('product_status')
                , 'product_sub_status' => _lang('product_sub_status')
                , 'lot' => _lang('lot')
                , 'serial' => _lang('serial')
                , 'mfd' => _lang('product_mfd')
                , 'exp' => _lang('product_exp')
                , 'reserve_qty' => _lang('reserve_qty')
                , 'confirm_qty' => _lang('confirm_qty')
                , 'unit' => _lang('unit')
                , 'uom_qty' => _lang('uom_qty')
                , 'uom_unit_prod' => _lang('uom_unit_prod')
                , 'actual_location' => _lang('actual_location')
                , 'pick_by' => _lang('pick_by')
                , 'remark' => _lang('remark')
            );
        endif;

        if ($this->config->item('build_pallet')):
            array_insert($view['column'], array_search('pick_by', array_keys($view['column'])), array('pallet_code' => 'Pallet Code In'));
        endif;

        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;

        $revision = $this->revision;
        $view['revision'] = $revision;
        $view['price_per_unit'] = $this->settings['price_per_unit'];
        $view['build_pallet'] = $this->config->item('build_pallet');

        if (!empty($search['showfooter'])):
            $view['showfooter'] = $search['showfooter'];
        else:
            $view['showfooter'] = 'no';
        endif;

        $view['Process_Id'] = $data->Process_Id; //ADD BY POR 2014-03-26 Send process for check qty. not show 0 but show null if qty step confirm

        if (!isset($this->settings['show_column_picking_job'])) :
            $count_colspan = 7;
            $count_colspan_after_sum = 4;
        else :
            $count_colspan = 0;
            $count_colspan_after_sum = 0;
        endif;

        $conf = $this->config->item('_xml');
        $view['conf_show_column'] = $conf_show_column = empty($conf['show_column_report']['object']['picking_job']) ? array() : @$conf['show_column_report']['object']['picking_job'];
        $view['signature_report'] = empty($conf['signature_report']['picking_job']) ? array() : @$conf['signature_report']['picking_job'];

        if (!empty($conf_show_column)):

            $column_config = array();

            foreach ($conf_show_column as $k => $column):
                if (is_array($column)):  //check xml is array
                    foreach ($column as $keys => $val): //วนหาค่าที่อยู่ใน array เพิ่มเติม
                        if ($keys == 'value'):  //value = ค่าของ column ที่ต้องการให้ show hide
                            if (!$val):
                                if ($set_css_for_show_column == ''):
                                    $set_css_for_show_column .= " .{$k}";
                                else:
                                    $set_css_for_show_column .= ", .{$k}";
                                endif;
                            else:
                                array_push($column_config, $k);
                            endif;
                        endif;
                    endforeach;
                else:
                    if (!$column):
                        if ($set_css_for_show_column == ''):
                            $set_css_for_show_column .= " .{$k}";
                        else:
                            $set_css_for_show_column .= ", .{$k}";
                        endif;
                    else:
                        array_push($column_config, $k);
                    endif;

                endif; // end of check show column


            endforeach;  // end of foreach

            if ($set_css_for_show_column != ''):

                $set_css_for_show_column .= "{display:none;}";

            endif;

            $view['set_css_for_show_column'] = $set_css_for_show_column;

            #calculator colspan for Total buttom TEXT
            $view['colspan_total'] = array_search('reserve_qty', $column_config);
//
//            $view['colspan_total'] = 8;
            $view['colspan_blank'] = 4;

            if ($conf_pallet):
                if ($conf_show_column['pallet_code']):
                    $view['colspan_blank'] = 5;
                endif;
            endif;

        endif;

//        p($view);
//        exit();

        $this->load->view("report/export_picking_pdf_by_location.php", $view);
    }

    function export_picking_pdf_by_other($search = NULL) {

        $conf = $this->config->item('_xml');
        if (empty($search)):
            $search = $this->input->post();
            if (!isset($search['document_no'])):
                $flow_detail = $this->flow->getFlowDetail($search['flow_id'], 'STK_T_Order');
            else:
                $order_data = $this->flow->getOrderDetailByDocumentNo($search['document_no']);
                if (empty($order_data)):
                    echo 'Sorry, can not find data.';
                    exit();
                endif;
                $search['flow_id'] = $order_data[0]->Flow_Id;
                $flow_detail = $this->flow->getFlowDetail_abnormal_flow($search['flow_id'], 'STK_T_Order');
            endif;
        else:
            $search = $this->input->get();
            $flow_detail = $this->flow->getFlowDetail_abnormal_flow($search['flow_id'], 'STK_T_Order');
        endif;

        // Add By Akkarapol, 02/05/2014, เพิ่มการ set show_column_picking_job ที่เก็บไว้ใน cookie เพื่อเก็บไว้ว่า ต้องการใช้ column ไหนบ้างในการแสดงผล โดยจะทำการ serialize ก่อนทำการ set
        $this->load->helper('cookie');
        $cookie = array(
            'name' => 'show_column_picking_job',
            'value' => serialize($search['picking_job']),
            'expire' => '0'
        );
        set_cookie($cookie);
        // Add By Akkarapol, 02/05/2014, เพิ่มการ set show_column_picking_job ที่เก็บไว้ใน cookie เพื่อเก็บไว้ว่า ต้องการใช้ column ไหนบ้างในการแสดงผล โดยจะทำการ serialize ก่อนทำการ set

        $data = $flow_detail[0];

        #add for fix defect #461 picking by in transfer stock order not show : by kik : 20140206
        if ($data->Process_Id == 2):
            $state_edge = 14;
        else:
            $state_edge = 58;
        endif;
        #end add for fix defect #461 picking by in transfer stock order not show : by kik : 20140206

        $tmp = $this->company->getCompanyByID($data->Renter_Id);
        $tmp = $tmp->row_array();
        $data->Renter_Name = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($data->Source_Id);
        $tmp = $tmp->row_array();
        $data->Source_Name = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($data->Destination_Id);
        $tmp = $tmp->row_array();
        $data->Consignee_Name = $tmp['Company_NameEN'];

        $tmp = $this->sys->getDomDetailByDomCode($data->Doc_Type);
        $tmp = $tmp->row_array();
        $data->Dispatch_Type = $tmp['Dom_EN_Desc'];

        $view['data'] = $data;

        /* Add Query Order By >> Ball */
        if ($search['o'] == "location") {
            $order_by = "L1.Location_Code ASC";
            if ($this->config->item('build_pallet')):
                $order_by .= ", a.Pallet_Id ASC";
            endif;
        } else if ($search['o'] == "pallet") {
            $order_by .= "a.Pallet_Id ASC, a.Item_Id ASC";
        } else {
//            $order_by = "a.Product_Code ASC";
            $order_by = "a.Item_Id ASC";
        }
//        p($order_by);
//        exit();


        /**
         * GET Activity By  : show pick by
         * @add code by kik : get sub_module for show activity by
         */
        $module_activity = 'picking';
        $sub_module_activity = $this->order_movement->get_submodule_activity($data->Order_Id, $module_activity, 'cf_pick_by');
        //end of get activity by
        #get Order Detail
        $order_detail_data = $this->pre_dispatch->query_from_OrderDetailId($data->Order_Id, $order_by, false, $sub_module_activity, $module_activity);
//        $order_detail_data = $this->pre_dispatch->query_from_OrderDetailId($data->Order_Id, $state_edge, $order_by); //ADD BY POR 2013-12-06 function ที่ใช้แทน queryDataFromOrderDetailId โดยต้องส่ง parameter ไปอีก 1 ตัวคือ step ในการหาชื่อผู้ทำรายการ 14 คือผู้ทำรายการ picking ซึ่งใช้กับ report นี้//comment by kik : 20141004
        $order_detail_data = $order_detail_data->result(); // Add By Akkarapol, 21/10/2013, เซ็ตค่าตัวแปรใหม่ เพราะค่าที่ Return มาจากฟังก์ชั่น queryDataFromOrderDetailId นั้นส่งมาเป็นแบบ $query เลย เพื่อให้รองรับการเรียกใช้งานที่หลากหลายขึ้น
//                p($order_detail_data);exit();
        foreach ($order_detail_data as $key_detail => $detail):
            $view['order_detail'][$key_detail]['product_code'] = $detail->Product_Code;
            $view['order_detail'][$key_detail]['product_name'] = $detail->Full_Product_Name;
            $view['order_detail'][$key_detail]['product_status'] = $detail->Status_Value;
            $view['order_detail'][$key_detail]['product_sub_status'] = $detail->Sub_Status_Value;
            $view['order_detail'][$key_detail]['product_lot'] = $detail->Product_Lot;
            $view['order_detail'][$key_detail]['product_serial'] = $detail->Product_Serial;
            $view['order_detail'][$key_detail]['product_mfd'] = $detail->Product_Mfd;
            $view['order_detail'][$key_detail]['product_exp'] = $detail->Product_Exp;
            $view['order_detail'][$key_detail]['reserv_qty'] = $detail->Reserv_Qty;
            $view['order_detail'][$key_detail]['confirm_qty'] = $detail->Confirm_Qty;
//            $view['order_detail'][$key_detail]['unit'] = $detail->Unit_Id;
            $view['order_detail'][$key_detail]['unit'] = $detail->Unit_Value; // Add By Akkarapol, 10/01/2014, เปลี่ยนจากการแสดง Unit ด้วย Unit_Id ซึ่งเป็นการเก็บข้อมูลที่ผิดอยู่ มาเป็นแสดงด้วย Unit_Value ที่ได้ถูกดึงค่ามาจากการทำ UOM แล้ว

            if ($detail->Unit_Id != NULL and $detail->Reserv_Qty != NULL and $detail->Standard_Unit_In_Id != NULL):
                $view['order_detail'][$key_detail]['uom_qty'] = $this->r->convert_uom($detail->Unit_Id, $detail->Reserv_Qty, $detail->Standard_Unit_In_Id);
            else:
                $view['order_detail'][$key_detail]['uom_qty'] = $detail->Uom_Qty;
            endif;

            $view['order_detail'][$key_detail]['uom_unit_prod'] = $detail->Uom_Unit_Val;


            if ($this->settings['price_per_unit'] == TRUE):
                $view['order_detail'][$key_detail]['price_per_unit'] = $detail->Price_Per_Unit;
                $view['order_detail'][$key_detail]['unit_price_value'] = $detail->Unit_Price_value;
                $view['order_detail'][$key_detail]['all_price'] = $detail->All_Price;
            endif;


            $view['order_detail'][$key_detail]['suggest_location'] = $detail->Suggest_Location;
            $view['order_detail'][$key_detail]['actual_location'] = $detail->Actual_Location;
            $view['order_detail'][$key_detail]['pick_by'] = $detail->Activity_By_Name;
            $view['order_detail'][$key_detail]['remark'] = $detail->Remark;

            if ($detail->Actual_Location == "" || $detail->Actual_Location == NULL):
                $view['order_detail'][$key_detail]['pick_by'] = "";
            else:
                $view['order_detail'][$key_detail]['pick_by'] = $detail->Activity_By_Name;
            endif;


            if ($this->config->item('build_pallet')):
                $view['order_detail'][$key_detail]['pallet_code'] = $detail->Pallet_Code;
            endif;

        endforeach;

//        p($view['order_detail']);exit();

        $view['text_header'] = 'Picking Job';

        #check if price_per_unit for show column Price / Unit,Unit Price,All Price
        if ($this->settings['price_per_unit'] == TRUE):
            $view['column'] = array(
                'no' => _lang('no')
                , 'product_code' => _lang('product_code')
                , 'product_name' => _lang('product_name')
                , 'product_status' => _lang('product_status')
                , 'product_sub_status' => _lang('product_sub_status')
                , 'lot' => _lang('lot')
                , 'serial' => _lang('serial')
                , 'mfd' => _lang('product_mfd')
                , 'exp' => _lang('product_exp')
                , 'reserve_qty' => _lang('reserve_qty')
                , 'confirm_qty' => _lang('confirm_qty')
                , 'unit' => _lang('unit')
                , 'uom_qty' => _lang('uom_qty')
                , 'uom_unit_prod' => _lang('uom_unit_prod')
                , 'price_per_unit' => _lang('price_per_unit')
                , 'unit_price' => _lang('unit_price')
                , 'all_price' => _lang('all_price')
                , 'suggest_location' => _lang('suggest_location')
                , 'actual_location' => _lang('actual_location')
                , 'pick_by' => _lang('pick_by')
                , 'remark' => _lang('remark')
            );

        else:
            $view['column'] = array(
                'no' => _lang('no')
                , 'product_code' => _lang('product_code')
                , 'product_name' => _lang('product_name')
                , 'product_status' => _lang('product_status')
                , 'product_sub_status' => _lang('product_sub_status')
                , 'lot' => _lang('lot')
                , 'serial' => _lang('serial')
                , 'mfd' => _lang('product_mfd')
                , 'exp' => _lang('product_exp')
                , 'reserve_qty' => _lang('reserve_qty')
                , 'confirm_qty' => _lang('confirm_qty')
                , 'unit' => _lang('unit')
                , 'uom_qty' => _lang('uom_qty')
                , 'uom_unit_prod' => _lang('uom_unit_prod')
                , 'suggest_location' => _lang('suggest_location')
                , 'actual_location' => _lang('actual_location')
                , 'pick_by' => _lang('pick_by')
                , 'remark' => _lang('remark')
            );
        endif;


        #add for ISSUE 3323 : by kik : 20140130
        if ($this->config->item('build_pallet')):
//            $view['order_detail'][$key_detail]['pallet_code'] = $detail->Pallet_Code;
            array_insert($view['column'], array_search('pick_by', array_keys($view['column'])), array('pallet_code' => 'Pallet Code In'));
        endif;

        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;

        //ADD BY POR 2013-12-19 เพิ่มข้อมูลสำหรับประกอบการแสดง footer ให้เซ็นชื่อของ PDF
        //----เพิ่มตัวแปร $revision สำหรับบอกว่าตอนนี้ PDF เป็นของ revision ไหน
        $revision = $this->revision;
        $view['revision'] = $revision;
        $view['price_per_unit'] = $this->settings['price_per_unit']; //add by kik : 20140114
        $view['build_pallet'] = $this->config->item('build_pallet'); //ADD by kik 2014-01-14
        //---เงื่อนไขในการแสดง footer ถ้า showfooter เป็น show ให้แสดง ถ้าเป็นเป็น no ไม่แสดง
        if (!empty($search['showfooter'])):
            $view['showfooter'] = $search['showfooter'];
        else:
            $view['showfooter'] = 'no';
        endif;
        //END ADD
        $view['Process_Id'] = $data->Process_Id; //ADD BY POR 2014-03-26 Send process for check qty. not show 0 but show null if qty step confirm

        $all_column = array(
            'no' => _lang('no')
            , 'product_code' => _lang('product_code')
            , 'product_name' => _lang('product_name')
            , 'product_status' => _lang('product_status')
            , 'product_sub_status' => _lang('product_sub_status')
            , 'lot' => _lang('lot')
            , 'serial' => _lang('serial')
            , 'mfd' => _lang('product_mfd')
            , 'exp' => _lang('product_exp')
            , 'reserve_qty' => _lang('reserve_qty')
            , 'confirm_qty' => _lang('confirm_qty')
            , 'unit' => _lang('unit')
            , 'uom_qty' => _lang('uom_qty')
            , 'uom_unit_prod' => _lang('uom_unit_prod')
            , 'price_per_unit' => _lang('price_per_unit')
            , 'unit_price' => _lang('unit_price')
            , 'all_price' => _lang('all_price')
            , 'suggest_location' => _lang('suggest_location')
            , 'actual_location' => _lang('actual_location')
            , 'pallet_code' => _lang('pallet_code_in')
            , 'pick_by' => _lang('pick_by')
            , 'remark' => _lang('remark')
        );

        $set_css_for_show_column = '';

        if ($search['o'] == "location"):
            unset($view['column']['suggest_location']);
//            unset($all_column['suggest_location']);
        endif;
        if ($search['o'] == "pallet"):
            unset($view['column']['pallet_code']);
            unset($view['column']['suggest_location']);
//            unset($all_column['suggest_location']);
        endif;

        if (!isset($this->settings['show_column_picking_job'])) :
            $count_colspan = 7;
            $count_colspan_after_sum = 4;
        else :
            $count_colspan = 0;
            $count_colspan_after_sum = 0;
        endif;

        if (!empty($this->settings['show_column_report']['object']['picking_job'])):
            $set_colspan = TRUE;
            $set_colspan_after_sum = FALSE;
            $active_columns = $this->settings['show_column_report']['object']['picking_job'];

            foreach ($all_column as $key_show_column => $show_column):
                if ($key_show_column == 'reserve_qty'):
                    $set_colspan = FALSE;
                endif;

                if (isset($this->settings['show_column_picking_job'])) {
                    if ($active_columns[$key_show_column] == TRUE) {
                        $tmp_chk_in_array = TRUE;
                    } else {
                        $tmp_chk_in_array = FALSE;
                    }
                }

                if ($set_colspan && $tmp_chk_in_array):
                    $count_colspan += 1;
                endif;

                if ($set_colspan_after_sum && $tmp_chk_in_array):
                    if ($this->config->item('build_pallet')):
                        $count_colspan_after_sum += 1;
                    else:
                        if ($key_show_column != 'pallet_code'):
                            $count_colspan_after_sum += 1;
                        endif;
                    endif;
                endif;

                if ($key_show_column == 'all_price'):
                    $set_colspan_after_sum = TRUE;
                endif;
                if (!$tmp_chk_in_array):
                    if ($set_css_for_show_column == ''):
                        $set_css_for_show_column .= " .{$key_show_column}";
                    else:
                        $set_css_for_show_column .= ", .{$key_show_column}";
                    endif;
                endif;
            endforeach;
            if ($set_css_for_show_column != ''):
                $set_css_for_show_column .= "{display:none;}";
            endif;
        endif;

        $view['count_colspan'] = $count_colspan;
        $view['count_colspan_after_sum'] = $count_colspan_after_sum;
        $view['set_css_for_show_column'] = $set_css_for_show_column;

        $conf = $this->config->item('_xml');
        $view['conf_show_column'] = $conf_show_column = empty($conf['show_column_report']['object']['picking_job']) ? array() : @$conf['show_column_report']['object']['picking_job'];
        $view['signature_report'] = empty($conf['signature_report']['picking_job']) ? array() : @$conf['signature_report']['picking_job'];

        if (!empty($conf_show_column)):

            $column_config = array();

            foreach ($conf_show_column as $k => $column):
                if (is_array($column)):  //check xml is array
                    foreach ($column as $keys => $val): //วนหาค่าที่อยู่ใน array เพิ่มเติม
                        if ($keys == 'value'):  //value = ค่าของ column ที่ต้องการให้ show hide
                            if (!$val):
                                if ($set_css_for_show_column == ''):
                                    $set_css_for_show_column .= " .{$k}";
                                else:
                                    $set_css_for_show_column .= ", .{$k}";
                                endif;
                            else:
                                array_push($column_config, $k);
                            endif;
                        endif;
                    endforeach;
                else:
                    if (!$column):
                        if ($set_css_for_show_column == ''):
                            $set_css_for_show_column .= " .{$k}";
                        else:
                            $set_css_for_show_column .= ", .{$k}";
                        endif;
                    else:
                        array_push($column_config, $k);
                    endif;

                endif; // end of check show column


            endforeach;  // end of foreach

            if ($set_css_for_show_column != ''):

                $set_css_for_show_column .= "{display:none;}";

            endif;

            $view['set_css_for_show_column'] = $set_css_for_show_column;

            #calculator colspan for Total buttom TEXT
            $view['colspan_total'] = array_search('reserve_qty', $column_config);
//
//            $view['colspan_total'] = 8;
            $view['colspan_blank'] = 4;

            if ($conf_pallet):
                if ($conf_show_column['pallet_code']):
                    $view['colspan_blank'] = 5;
                endif;
            endif;

        endif;

        $this->load->view("report/export_picking_pdf.php", $view);
    }

//     END Add By Akkarapol, 22/10/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ Print ใบ Picking Job


    function exportCmLocationExcel() {
        $search = $this->input->post();
        $data = $this->r->show_pt_location_export($search);
        $view['body'] = $data;
        // p($view['body']);exit;
        $view['file_name'] = 'confirm_location_report';
        $view['header'] = array(
            _lang('date')
            , _lang('document_no')
            , _lang('document_int')
            , _lang('document_ext')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('product_status')
            , _lang('lot')
            , _lang('serial')
            , _lang('quantity')
            , _lang('unit')
            , _lang('suggest_location')
            , _lang('actual_location')
            , _lang($search['activity'] . "_by") // Edit By Akkarapol, 20/01/2013, เพิ่มว่า activity ที่ทำไปนั้นคืออะไร
            , _lang('remark')
        );

        // Add By Akkarapol, 06/02/2014, เพิ่มการตรวจสอบว่าถ้า config.build_pallet = TRUE ให้ทำการ แทรก Pallet Code เข้าไปก่อน Remark
        if ($this->config->item('build_pallet')):
            array_splice($view['header'], array_search('Remark', $view['header']), 0, array('Pallet Code'));
        endif;
        // END Add By Akkarapol, 06/02/2014, เพิ่มการตรวจสอบว่าถ้า config.build_pallet = TRUE ให้ทำการ แทรก Pallet Code เข้าไปก่อน Remark
        //ADD BY POR 2014-06-20 เพิ่มการตรวจสอบว่าถ้าเป็น price per unit ให้แทรก ข้อมูล price per unit เข้าไปหลัง  unit
        if ($this->settings['price_per_unit'] == TRUE):
            array_splice($view['header'], array_search('Unit', $view['header']) + 1, 0, array(_lang('price_per_unit'), _lang('unit_price'), _lang('all_price')));
        endif;
        $view['statusprice'] = $this->settings['price_per_unit'];
        //END ADD
        // $view['body']  =  $report;
        $this->load->view('report/cfl_excel_template', $view);
    }

    public function exportReportHistoryReportToExcel() {
        $product_id = $this->input->post("product_id");
        $current_location = $this->input->post("current_location");
        $lot = $this->input->post("lot");
        $serial = $this->input->post("serial");
        $doc_type = $this->input->post("doc_type");
        $ref_value = $this->input->post("ref_value");
        $date_from = $this->input->post("date_from");
        //$product_name=$this->r->getProductDetailByCode($product_code);

        $condition_value = array(
            "ref_value" => $ref_value
            , "current_location" => $current_location
            , "lot" => $lot
            , "product_id" => $product_id
            , "date_from" => convertDate($date_from, "eng", "iso", "-")
            , "serial" => $serial
            , "doc_type" => $doc_type
        );
        //p($condition_value);//exit();
        //  $search = $this->input->post();
        //  p($_POST);exit();
        //  $condition_value = $_POST["search_criteria"];
        //   $location_history = $this->r->locationHistoryReport($condition_value);

        $location_history = $this->locationHis->locationHistoryReport($this->input->post()); //ADD BY POR 2013-10-25 เรียกใช้เหมือน exportLocationHistoryReportToPDF

        $data = $location_history;
        $view['data'] = $data;
        $view['condition_value'] = $condition_value;
        $view['doc_ref_td'] = $doc_type;
        //$data = $this->r->countProductShelfLife($search);
        //p($data);
        $view['body'] = $data;
        $view['file_name'] = 'location_history_report';

        $view['statusprice'] = $this->settings['price_per_unit']; //ADD BY POR 2014-06-23 config price per unit

        $this->load->view("report/location_history_report_excel", $view);
    }

    // Add By Akkarapol, 18/09/2013, เพิ่มฟังก์ชั่น Gen PDF ของ Report Location History
    public function exportLocationHistoryReportToPDF() {
        //$this->load->model("location_history_model", "locationHis"); //COMMENT BY POR 2013-10-24 เนื่องจากไปประกาศไว้ด้านบนแทน

        $this->load->library('pdf/mpdf');

        $view['data'] = $this->locationHis->locationHistoryReport($this->input->post());

        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $view['printBy'] = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        $view['statusprice'] = $this->settings['price_per_unit']; //ADD BY POR 2014-06-23 config price per unit
        $this->load->view("report/exportLocationHistoryReportToPDF.php", $view);
    }

    // END Add By Akkarapol, 18/09/2013, เพิ่มฟังก์ชั่น Gen PDF ของ Report Location History
    //=================Inventory Report====================
    //By Por->2013-09-20
    //==Start==
//    public function inventory_temp() {
    public function inventory() {

        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $this->load->model("company_model", "company");
        $this->load->model("warehouse_model", "w");
        $this->load->model("product_model", "p");
        $this->load->model("product_status_model", "ps");

        $parameter['renter_id'] = $this->config->item('renter_id');
        $parameter['consignee_id'] = $this->config->item('owner_id');
        $parameter['owner_id'] = $this->config->item('owner_id');

        #Get renter list
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE);
        $parameter['renter_list'] = $renter_list;

        # Get Product Category
        $q_category = $this->p->productCategory();
        $r_category = $q_category->result();
        $category_list = genOptionDropdown($r_category, "SYS");
        $parameter['category_list'] = $category_list;

//        #comment by kik (24-09-2013)
//        #Get renter branch list
//        $q_renterbranch = $this->company->getRenterBranchAll();
//        $r_renterbranch = $q_renterbranch->result();
//        $renterbranch_list = genOptionDropdown($r_renterbranch, "COMPANY");
//        $parameter['renterbranch_list'] = $renterbranch_list;
        #Get Status list
        $q_status = $this->ps->getProductStatus();
        $r_status = $q_status->result();
        $status_list = genOptionDropdown($r_status, "SYS");
        $parameter['status_list'] = $status_list;

//        #comment by kik (24-09-2013)
//        #Get warehouse list
//        $q_warehouse = $this->w->getWarehouseList();
//        $r_warehouse = $q_warehouse->result();
//        $warehouse_list = genOptionDropdown($r_warehouse, "WH");
//        $parameter['warehouse_list'] = $warehouse_list;

        $parameter['user_id'] = $this->session->userdata('user_id');

        if (!empty($_POST['show_auto'])):

            $product_id = $this->input->post('product_id');
            if ($this->input->post('product_id') == "" && $this->input->post('product_code') != ""):
                $product_id = $this->product->getProductIDByProdCode($this->input->post('product_code'));
                if (empty($product_id)):
                    $product_id = "";
                endif;
            endif;

            $parameter['product_name'] = $this->input->post('product_name');

            if ($this->input->post('product_code') != ""):
                $parameter['product_name'] = $this->input->post('product_code') . " " . $this->input->post('product_name');
            endif;

            $parameter['product_id'] = $product_id;
            $parameter['tdate'] = $this->input->post('tdate');
            $parameter['renter_id'] = $this->input->post('renter_id');
            $parameter['show_auto'] = $this->input->post('show_auto');

        else:
            $parameter['show_auto'] = 'N';
            $parameter['product_id'] = '';
            $parameter['tdate'] = '';
            $parameter['product_name'] = '';
        endif;


        $str_form = $this->parser->parse("form/inventory_report", $parameter, TRUE);


        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Inventory'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form

            // Add By Akkarapol, 25/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPreDispatch"></i>'
            // END Add By Akkarapol, 25/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
        ));
    }

//    function showInventoryReport() {
//    	/*
//    	$search = $this->input->get();
//    	$status_range = $this->r->getStatusRange($search['status_id']);
//    	$view['range'] = $status_range;
//    	$data = $this->r->searchInventory($search, $search['iDisplayStart'], ($search['iDisplayStart'] + $search['iDisplayLength']));
//    	$response = array();
//    	foreach ($data as $k => $v) :
//    		$response[] = array(($search['iDisplayStart'] + $k + 1)
//    				,$v->Product_Code
//    				,thai_json_encode($v->Product_NameEN)
//    				,$v->Product_Lot
//    				,$v->Product_Serial
//    				,$v->Product_Mfd
//    				,$v->Product_Exp
//    				,$v->counts_1
//    				,$v->estimate_1
//    				,$v->counts_2
//    				,$v->estimate_2
//    				,$v->counts_3
//    				,$v->estimate_3
//    				,$v->counts_4
//    				,$v->estimate_4
//    				,$v->counts_5
//    				,$v->estimate_6
//    				,$v->counts_6
//    				,$v->estimate_6
//    				,$v->counts_7
//    				,$v->estimate_7
//    				,$v->counts_8
//    				,$v->estimate_8
//    		);
//    	endforeach;
//		$output = array(
//			"sEcho" => intval($this->input->get('sEcho')),
//			"iTotalRecords" => 100,
//			"iTotalDisplayRecords" => $this->r->count_inventory($search),
//			"aaData" => $response
//		);
//		echo json_encode($output);
//    	exit();*/
//
//    	$search = $this->input->post();
//    	$status_range = $this->r->getStatusRange($search['status_id']);
//    	$view['range'] = $status_range;
//    	$data = $this->r->searchInventory($search);
//    	$view['data'] = $data;
//    	$view['search'] = $search;
//
//    	// Add By Akkarapol, 04/11/2013, เพิ่มการจัดการตัว parser ที่จะไปกำหนดค่า aoColumns[{ "sSortDataType": "dom-text", "sType": "numeric-comma"}] ใน column ต่างๆ เนื่องจากไม่สามารถกำหนดค่าตายตัวได้ เพราะการแสดง column นั้นจะอยู่กับการเลือก  Product Status ที่จะแสดงด้วย
//    	$parser_ao_column = '';
//    	$count_range = count($status_range);
//    	for ($i = 0; $i < $count_range; ++$i):
//    	$parser_ao_column = $parser_ao_column . '{ "sSortDataType": "dom-text", "sType": "numeric-comma"},{ "sSortDataType": "dom-text", "sType": "numeric-comma"},';
//    	endfor;
//    	$view['parser_ao_column'] = $parser_ao_column;
//    	// END Add By Akkarapol, 04/11/2013, เพิ่มการจัดการตัว parser ที่จะไปกำหนดค่า aoColumns[{ "sSortDataType": "dom-text", "sType": "numeric-comma"}] ใน column ต่างๆ เนื่องจากไม่สามารถกำหนดค่าตายตัวได้ เพราะการแสดง column นั้นจะอยู่กับการเลือก  Product Status ที่จะแสดงด้วย
//
//    	$view['s_scroll_x_inner'] = ($count_range > 1 ? '350%' : '100%'); // Add By Akkarapol, 04/11/2013, เซ็ตค่าความกว้างของ sScrollXInner ใน dataTable โดยปรับการตั้งค่านิดนึง เนื่องจาก จำนวนของ column ที่จะแสดงไม่เท่ากันในกรณีที่เลือก  Product Status มา ไม่เหมือนกัน
//
//    	$this->load->view("report/inventory_report.php", $view);
//
//    }


    function showInventoryReport() {

        $search = $this->input->post();
//p($search);exit();
        $status_range = $this->r->getStatusRange($search['status_id']);
        $view['range'] = $status_range;
        if ($search['as_date'] == date("d/m/Y")) {
            $data = $this->r->searchInventoryToday($search);
            $view['is_today'] = TRUE;
        } else {
            $data = $this->r->searchInventory($search);
            $view['is_today'] = FALSE;
        }

        $view['data'] = $data;
        $view['search'] = $search;

        // Add By Akkarapol, 04/11/2013, เพิ่มการจัดการตัว parser ที่จะไปกำหนดค่า aoColumns[{ "sSortDataType": "dom-text", "sType": "numeric-comma"}] ใน column ต่างๆ เนื่องจากไม่สามารถกำหนดค่าตายตัวได้ เพราะการแสดง column นั้นจะอยู่กับการเลือก  Product Status ที่จะแสดงด้วย
        $parser_ao_column = '';
        $count_range = count($status_range);
        for ($i = 0; $i < $count_range; ++$i):
            $parser_ao_column = $parser_ao_column . '{ "sSortDataType": "dom-text", "sType": "numeric-comma"},{ "sSortDataType": "dom-text", "sType": "numeric-comma"},';
        endfor;
        $view['parser_ao_column'] = $parser_ao_column;
        // END Add By Akkarapol, 04/11/2013, เพิ่มการจัดการตัว parser ที่จะไปกำหนดค่า aoColumns[{ "sSortDataType": "dom-text", "sType": "numeric-comma"}] ใน column ต่างๆ เนื่องจากไม่สามารถกำหนดค่าตายตัวได้ เพราะการแสดง column นั้นจะอยู่กับการเลือก  Product Status ที่จะแสดงด้วย

        $view['s_scroll_x_inner'] = ($count_range > 1 ? '350%' : '100%'); // Add By Akkarapol, 04/11/2013, เซ็ตค่าความกว้างของ sScrollXInner ใน dataTable โดยปรับการตั้งค่านิดนึง เนื่องจาก จำนวนของ column ที่จะแสดงไม่เท่ากันในกรณีที่เลือก  Product Status มา ไม่เหมือนกัน
//        $this->load->view("report/inventory_report_swa.php", $view);
        $this->load->view("report/inventory_report.php", $view);
    }

    #add by kik  (24-09-2013)

    function exportInventoryToExcel() {

        $search = $this->input->post();
        $status_range = $this->r->getStatusRange($search['status_id']);
        $ranges = $status_range;

        $view['range'] = $status_range;
//        add by kik : 2013-11-15
        if ($search['as_date'] == date("d/m/Y")) {
            $datas = $this->r->searchInventoryToday($search);
        } else {
            $datas = $this->r->searchInventory($search);
        }

//        end add by kik : 2013-11-15
//        comment by kik : 2013-11-15
//        $datas = $this->r->searchInventory($search);

        $reports = array();
        foreach ($datas as $key => $data) {

            // Add By Akkarapol, 06/02/2014, เพิ่มการตรวจสอบว่าถ้า config.build_pallet != TRUE ให้ทำการ unset ค่า Pallet_Code ในตัวแปรออกไป
            if (!$this->config->item('build_pallet')):
                unset($data->Pallet_Code);
            endif;
            // Add By Akkarapol, 06/02/2014, เพิ่มการตรวจสอบว่าถ้า config.build_pallet != TRUE ให้ทำการ unset ค่า Pallet_Code ในตัวแปรออกไป

            $report['key'] = $key + 1;
            foreach ($data as $key_index => $value) {
                if ($key_index != 'Product_Status') {

                    //+++++ADD BY POR 2013-11-26
                    $counts = strpos($key_index, 'counts_'); //ตรวจสอบว่า key มีคำว่า counts_ หรือไม่ ถ้ามีแสดงว่าเป็นการแสดงจำนวน ให้กำหนดให้ชิดขวา
                    $estimate = strpos($key_index, 'estimate_'); //ตรวจสอบว่า key มีคำว่า estimate_ หรือไม่ ถ้ามีแสดงว่าเป็นการแสดงจำนวน ให้กำหนดให้ชิดขวา
                    //ถ้าเป็นจำนวนหรือ totalbal (จำนวน total ของ product)
                    if ($counts !== FALSE || $estimate !== FALSE || $key_index == 'totalbal') {
                        $value = array('align' => 'right', 'value' => set_number_format($value));
                    }
                    //+++++END ADD
                    //-----COMMENT BY POR 2013-11-26 เนื่องจากเปลี่ยนไปใช้วิธีใหม่ เนื่องจากอันเก่ารองรับแค่ 2 column
                    //ADD BY POR 2013-10-31 เพิ่มเติมว่าถ้าเป็น ตัวเลข ให้ชิดขวา และจัด format
                    //if ($key_index == counts_1 || $key_index == estimate_1){
                    //  $value = array('align' => 'right', 'value' => number_format($value,2));
                    //}
                    //END ADD
                    //-----END COMMENT

                    array_push($report, $value);
                }
            }

            array_push($reports, $report);
            unset($report);
        }

        $view['file_name'] = 'Invertory';
        $view['body'] = $reports;
        $view['header'] = array(
            _lang('no')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('lot')
            , _lang('serial')
            , _lang('product_mfd')
            , _lang('product_exp')
            , _lang('total') //++++++ADD BY POR 2013-11-26 เพิ่มเติมให้แสดง total
        );

        // Add By Akkarapol, 06/02/2014, เพิ่มการตรวจสอบว่าถ้า config.build_pallet = TRUE ให้ทำการ แทรก Pallet Code เข้าไปก่อน Remark
        if ($this->config->item('build_pallet')):
            array_splice($view['header'], array_search(_lang('total'), $view['header']), 0, array(_lang('pallet_code')));
        endif;
        // END Add By Akkarapol, 06/02/2014, เพิ่มการตรวจสอบว่าถ้า config.build_pallet = TRUE ให้ทำการ แทรก Pallet Code เข้าไปก่อน Remark


        foreach ($ranges as $col) {
            array_push($view['header'], "BL_" . $col);
            array_push($view['header'], "EST_" . $col);
        }

        $this->load->view('excel_template', $view);
    }

    #add by kik (25-09-2013)

    function exportInventoryPdf() {

        $this->load->library('pdf/mpdf');
        $this->load->model("company_model", "company");
        date_default_timezone_set('Asia/Bangkok');
        $search = $this->input->post();

        #company
        $temp_company = $this->company->getCompanyByID($this->input->post('renter_id'));
        $temp_company2 = $temp_company->result();


        $status_range = $this->r->getStatusRange($search['status_id']);
        $ranges = $status_range;
//        add by kik : 2013-11-15
        if ($search['as_date'] == date("d/m/Y")) {
            $datas = $this->r->searchInventoryToday($search);
        } else {
            $datas = $this->r->searchInventory($search);
        }
//        end add by kik : 2013-11-15
//        comment by kik : 2013-11-15
//        $datas = $this->r->searchInventory($search);

        $product_id = $search["product_id"];
        if ($product_id != "") {
            $view['product_name'] = $this->r->getProductDetailById($product_id);
        }

        $view['upload_path'] = $this->settings['uploads']['upload_path']; // Add By Akkarapol, 03/02/2014, เพิ่มการส่งค่าของ upload_path จาก native_session เพื่อนำไปใช้กับ view ให้สร้างไฟล์ตาม path ที่ตั้งค่าไว้

        $view['search'] = $search;
        $view['ranges'] = $ranges;
        $view['datas'] = $datas; //p($datas);exit();
        $view['Company_NameEN'] = $temp_company2[0]->Company_NameEN;

        // Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
//        p($view);exit();
        $this->load->view("report/exportInventory", $view);
    }

    // Add By Akkarapol, 11/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ Print ใบ Pending Job
    function exportPendingPDF() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('mpdf/mpdf');
        $search = $this->input->post();
        $Assigned_Id = $search["Assigned_Id"]; //ชื่อ worker_id

        $prod_list = $search["prod_list"]; //detail ทั้งหมดที่เลือก realtime
        # Parameter Index Datatable
        $ci_prod_id = $search["ci_prod_id"];
        $ci_prod_code = $search["ci_prod_code"];
        $ci_lot = $search["ci_lot"];
        $ci_serial = $search["ci_serial"];
        $ci_mfd = $search["ci_mfd"];
        $ci_exp = $search["ci_exp"];
        $ci_prod_status = $search["ci_prod_status"];
        $ci_item_id = $search["ci_item_id"];
        $ci_suggest_loc = $search["ci_suggest_loc"];
        $ci_actual_loc = $search["ci_actual_loc"];
        $ci_unit_id = $search["ci_unit_id"];
        $ci_unit_value = $search["ci_unit_value"]; // Add By Akkarapol, 10/01/2014, เพิ่ม index $ci_unit_value = $search["ci_unit_value"] ของ unit name เข้าไปด้วย เพื่อนำไปใช้แสดงผลต่อ
        $ci_balance_qty = $search["ci_balance_qty"];
        $ci_confirm_qty = $search["ci_confirm_qty"];
        $ci_remark = $search["ci_remark"];
        $ci_old_loc = $search["ci_old_loc"];
        $ci_prod_status_name = $search["ci_prod_status_name"];
        $ci_prod_sub_status_name = $search["ci_prod_sub_status_name"];
        $ci_actual_location_name = $search["ci_actual_location_name"];
        $ci_sugest_loc_name = $search["ci_sugest_loc_name"];

        //นำ detail ที่ได้มาแทนค่าในตัวแปรตาม index ที่ต้องการ
        if (count($prod_list) > 0) {
            foreach ($prod_list as $key => $rows) {
                $a_data = explode(SEPARATOR, $rows);

                //หารายละเอียดอื่นๆ ที่ไม่สามารถนำค่ามาแสดงได้ จึงต้องหาใน function เพิ่มเติม เช่น Product_Name เนื่องจากหน้า from แสดงไม่เต็มจึงต้องหาค่าใหม่, Location From และ Suggest Location เนื่องจากค่าที่ได้มาติดรูปแว่นขยาย จึงต้องหาค่าใหม่
                $pro_detail = $this->pending->getRelocateDetail($a_data[$ci_item_id])->result_array();

                $pending_detail[$key]['Product_Code'] = $a_data[$ci_prod_code];
                $pending_detail[$key]['Full_Product_Name'] = $pro_detail[0]["Product_NameEN"];
                $pending_detail[$key]['Status_Value'] = $a_data[$ci_prod_status_name];
                $pending_detail[$key]['Sub_Status_Value'] = $a_data[$ci_prod_sub_status_name];
                $pending_detail[$key]['Product_Lot'] = $a_data[$ci_lot];
                $pending_detail[$key]['Product_Serial'] = $a_data[$ci_serial];
                $pending_detail[$key]['Product_Mfd'] = $a_data[$ci_mfd];
                $pending_detail[$key]['Product_Exp'] = $a_data[$ci_exp];
                $pending_detail[$key]['Reserv_Qty'] = str_replace(",", "", $a_data[$ci_balance_qty]); //Edit by POR 2014-03-14 ตัด comma ออกก่อนส่งค่าไปหน้า PDF
                $pending_detail[$key]['Confirm_Qty'] = str_replace(",", "", $a_data[$ci_confirm_qty]); //Edit by POR 2014-03-14 ตัด comma ออกก่อนส่งค่าไปหน้า PDF
                $pending_detail[$key]['Unit_Id'] = $a_data[$ci_unit_id];
                $pending_detail[$key]['Unit_Value'] = $a_data[$ci_unit_value]; // Add By Akkarapol, 10/01/2014, เพิ่มการเซ็ตค่า $pending_detail[$key]['Unit_Value'] = $a_data[$ci_unit_value]; เพื่อนำไปแสดงผลต่อด้วย Unit_Value
                $pending_detail[$key]['Old_Location'] = $pro_detail[0]["loc_old"];

                if ($search['method'] == 'confirm'):
                    $pending_detail[$key]['Suggest_Location'] = $a_data[$ci_sugest_loc_name];
                else:
                    $pending_detail[$key]['Suggest_Location'] = $pro_detail[0]["loc_sug"];
                endif;

                $pending_detail[$key]['Actual_Location'] = $a_data[$ci_actual_location_name];
                $pending_detail[$key]['Remark'] = $a_data[$ci_remark];
            }
        }
        //p($pending_detail);
        //exit();
        $view['pending_detail'] = $pending_detail;

        $flow_detail = $this->pending->getPendingFlow($search['flow_id']);
        $view['flow_detail'] = $flow_detail;
        $pending_detail2 = $this->pending->getPendingDetail($flow_detail[0]->Order_Id);  // get data pending order
        $view['pending_detail2'] = $pending_detail2;
        foreach ($pending_detail2 as $key_rows => $rows) {
            $view['doc_relocate'] = $rows->Doc_Relocate;
            $view['document_no'] = $rows->Document_No;
            $view['doc_refer_int'] = $rows->Doc_Refer_Int;
            $view['doc_refer_ext'] = $rows->Doc_Refer_Ext;
            $view['doc_refer_inv'] = $rows->Doc_Refer_Inv;
            $view['doc_refer_ce'] = $rows->Doc_Refer_CE;
            $view['doc_refer_bl'] = $rows->Doc_Refer_BL;
            $view['doc_refer_awb'] = $rows->Doc_Refer_AWB;
            $view['receive_date'] = $rows->Receive_Date;

            // Add By Akkarapol, 12/12/2013, เพิ่มการหาค่าให้กับ Suggest Location และเซ็ตค่าส่งไปยัง PDF เพื่อให้ Operator นำไปทำการ Putaway / Picking ต่อไป
//            $qty = str_replace(",", "", $rows->Confirm_Qty);
//            $setSuggestLocation = $this->suggest_location->getSuggestLocationArray('suggestLocation', $rows->Product_Code, $rows->Product_Status, '1', NULL, $rows->Product_Sub_Status, $qty);
//            $pending_detail[$key_rows]->Suggest_Location_Id = (!empty($setSuggestLocation) ? $setSuggestLocation[0]['Location_Id'] : NULL);
//            $pending_detail[$key_rows]->Suggest_Location = (!empty($setSuggestLocation) ? $setSuggestLocation[0]['Location_Code'] : NULL);
            // End Add By Akkarapol, 12/12/2013, เพิ่มการหาค่าให้กับ Suggest Location และเซ็ตค่าส่งไปยัง PDF เพื่อให้ Operator นำไปทำการ Putaway / Picking ต่อไป
        }

        $order_detail = $this->pending->getOrderByDocNo($view['document_no']);  // get data receive order
        $getRelocationOrder = $this->reLocation->getRelocationOrder($search['flow_id']);
        //$remark = $getRelocationOrder[0]->Remark;

        foreach ($order_detail as $rows) {
            $view['owner_id'] = $rows->Owner_Id;
            $view['receive_type'] = $rows->Doc_Type;
            $view['renter_id'] = $rows->Renter_Id;
            $view['vendor_id'] = $rows->Vendor_Id;
            $view['driver_name'] = $rows->Vendor_Driver_Name;
            $view['car_no'] = $rows->Vendor_Car_No;
            $view['shipper_id'] = $rows->Source_Id;
            $view['consignee_id'] = $rows->Destination_Id;
            //$view['remark'] = $remark;
            $view['is_pending'] = $rows->Is_Pending;
        }

        $tmp = $this->company->getCompanyByID($view['renter_id']);
        $tmp = $tmp->row_array();
        $view['renter_name'] = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($view['shipper_id']);
        $tmp = $tmp->row_array();
        $view['shipper_name'] = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($view['consignee_id']);
        $tmp = $tmp->row_array();
        $view['consignee_name'] = $tmp['Company_NameEN'];

        $tmp = $this->sys->getDomDetailByDomCode($view['receive_type']);
        $tmp = $tmp->row_array();
        $view['receive_type_name'] = $tmp['Dom_EN_Desc'];

        $view['text_header'] = _lang('pending_job');
//        $view['order_detail'] = $this->reLocation->getReLocationProductDetail($data[0]->Order_Id);
//        $view['data'] = $data;


        $view['column'] = array(
            _lang('no')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('product_status')
            , _lang('product_sub_status')
            , _lang('lot')
            , _lang('serial')
            , _lang('product_mfd')
            , _lang('product_exp')
            , _lang('receive_qty')
            , _lang('confirm_qty')
            , _lang('unit')
            , _lang('from_location')
            , _lang('suggest_location')
            , _lang('actual_location')
            , _lang('remark')
        );

        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;

        //$detail = $this->user->getDetailByUserId($flow_detail[0]->Assigned_Id, 'user'); COMMENT BY POR 2013-12-18 ยกเลิกตัวแปรเนื่องจากใช้ตัวแปร realtime แทน เพราะต้องการแสดงผลแบบ realtime
        $detail = $this->user->getDetailByUserId($Assigned_Id, 'user'); //ADD BY POR 2013-12-18 เปลี่ยนให้ไปใช้ตัวแปรแบบ realtime
        $view['worker_name'] = $detail['First_NameTH'] . ' ' . $detail['Last_NameTH'];

        //ADD BY POR 2013-12-19 เพิ่มข้อมูลสำหรับประกอบการแสดง footer ให้เซ็นชื่อของ PDF
        //----เพิ่มตัวแปร $revision สำหรับบอกว่าตอนนี้ PDF เป็นของ revision ไหน
        $revision = $this->revision;
        $view['revision'] = $revision;

        //---เงื่อนไขในการแสดง footer ถ้า showfooter เป็น show ให้แสดง ถ้าเป็นเป็น no ไม่แสดง
        if (!empty($search['showfooter'])):
            $view['showfooter'] = $search['showfooter'];
        else:
            $view['showfooter'] = 'no';
        endif;
        //END ADD

        $this->load->view("report/export_pending_pdf.php", $view);
    }

    // END Add By Akkarapol, 11/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ Print ใบ Pending Job
    //==End==
    //+++++ADD BY POR 2013-11-14 receive report
    //=====เรียกหน้าแรกของ report
    function receive() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
// p($parameter); exit;
        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/receive_form_report", $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Receiving '
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmReceive"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
    }

    //=====แสดง report หลังจากกด search
    function receiveReport() {
        $search = $this->input->get();
        ###################################################
        #	Pagination
        ###################################################
        $pages = new Pagination_ajax;

        $num_rows = sizeof($this->r->search_receive($search, null, null)); // this is the COUNT(*) query that gets the total record count from the table you are querying
        $pages->items_total = $num_rows;
        $pages->mid_range = 5; // number of links you want to show in the pagination before the "..."
        $pages->paginate();

        $limit = json_decode($pages->limit);
        $view['low'] = $pages->low;
        $view['items_total'] = $num_rows;

        if ($pages->current_page == $pages->num_pages):
            $view['show_to'] = $num_rows;
        else:
            $view['show_to'] = $pages->low + $pages->items_per_page;
        endif;


        $view['display_items_per_page'] = $pages->display_items_per_page();

        if (!empty($limit)):
            $view['data'] = $this->r->search_receive($search, $limit[0], $limit[1]);
        else:
            $view['data'] = $this->r->search_receive($search, null, null);
        endif;

        $view['pagination'] = $pages->display_pages();

        $view['search'] = $search;

        #Load config
        $conf = $this->config->item('_xml'); // By ball : 20140707

        $receiving_report = empty($conf['show_column_report']['object']['receiving_report']) ? false : @$conf['show_column_report']['object']['receiving_report'];
        $column_result = colspan_report($receiving_report, $conf);


        $view['all_column'] = $column_result['all_column']; //column ทั้งหมดที่แสดง
        $view['colspan'] = $column_result['colspan_all']; //ส่ง colspan ทั้งหมดไป
        $view['show_hide'] = $column_result['show_hide']; //column show hide column
        // p($view);
        $this->load->view("report/receive_report.php", $view);
    }

    //=====แสดงรายงาน PDF
    function exportreceiveToPDF() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('mpdf/mpdf');
        $search = $this->input->post();

        #Load config (by kik : 20140708)
        $conf = $this->config->item('_xml');
        $conf_show_column = empty($conf['show_column_report']['object']['receiving_pdf']) ? array() : @$conf['show_column_report']['object']['receiving_pdf'];
        // p($conf_show_column );
    
        // exit;
        $column_result = colspan_report($conf_show_column, $conf);
        // p($column_result ); exit;
        $view['all_column'] = $column_result['all_column']; //column ทั้งหมดที่แสดง
        $view['colspan'] = $column_result['colspan_all']; //ส่ง colspan ทั้งหมดไป
        $view['show_hide'] = json_decode($column_result['show_hide']);
        $view['set_css_for_show_column'] = $column_result['set_css_for_show_column'];

//        p($column_result);exit();
        $report = $this->r->search_receive_pdf($search);
        $view['datas'] = $report;
        // p($view['datas']);exit();
        //เรียกชื่อของคนที่ออกรายงาน
        $view['from_date'] = $this->input->post("fdate"); //วันที่เริ่มค้น
        $view['to_date'] = $this->input->post("tdate"); //วันที่สิ้นสุด
        //เรียกชื่อของคนที่ออกรายงาน
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        $view['printBy'] = $printBy;

        $revision = $this->revision; //ADD BY POR 2013-12-19 กำหนดตัวแปรสำหรับเรียก revision
        $view['revision'] = $revision;
// p($view);exit();
        $view['statusprice'] = $this->settings['price_per_unit']; //ADD BY POR 2014-06-18 config price per unit
        $this->load->view("report/exportReceive", $view);
    }

    //=====แสดงรายงาน EXCEL
    function exportreceiveToExcel() {

        $search = $this->input->post();
        #Load config (by kik : 20140708)
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $conf_show_column = empty($conf['show_column_report']['object']['receiving_report']) ? array() : @$conf['show_column_report']['object']['receiving_report'];

        $reports = $this->r->search_receive($search, null, null);
//        p($reports);exit();
        $data_views = array();
        //
        foreach ($reports as $data):

            array_push($data_views, $data);

            if ($data->Is_reject == 'Y'):

                $tmp_data_reject = new StdClass;
                $tmp_data_reject = clone $data;
                // If use $a = $b in object it mean object $a and object $b is same object, if want to duplicate object it use clone example ($a = clone $b)
                // Reference is http://www.php.net/manual/en/language.oop5.cloning.php

                $tmp_data_reject->Receive_Qty = -$tmp_data_reject->Receive_Qty;

                //ADD BY POR 2014-06-18 เพิ่มเรื่อง price per unit
                if ($conf_price_per_unit):
                    $tmp_data_reject->Price_Per_Unit = -$tmp_data_reject->Price_Per_Unit;
                    $tmp_data_reject->All_price = -$tmp_data_reject->All_price;
                endif;
                //END ADD

                $tmp_data_reject->Remark = ($tmp_data_reject->Remark == "" || $tmp_data_reject->Remark == " " || empty($tmp_data_reject->Remark)) ? 'Reject' : $tmp_data_reject->Remark;
                array_push($data_views, $tmp_data_reject);
                unset($tmp_data_reject);

            endif;

        endforeach;
        //exit();
        // p($data_views);exit();

        $view['search'] = $search;
        $view['file_name'] = 'receiving_report';


        $view['header'] = array(
            _lang('date')
            , _lang('document_no')
            , _lang('document_ext')
            , _lang('document_int')
            , _lang('document_inv')
            , _lang('document_ce')
            , _lang('document_bl')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('product_status')
            , _lang('lot')
            , _lang('serial')
            , _lang('product_mfd')
            , _lang('product_exp')
            , _lang('quantity')
            , _lang('unit')
            , _lang('from')
            , _lang('to')
            , _lang('remark')
        );

        if ($conf_inv):
            array_splice($view['header'], array_search('Quantity', $view['header']), 0, array(_lang('invoice_no')));
        endif;

        if ($conf_cont):
            array_splice($view['header'], array_search('Quantity', $view['header']), 0, array(_lang('container')));
        endif;


        // Add By Akkarapol, 06/02/2014, เพิ่มการตรวจสอบว่าถ้า config.build_pallet = TRUE ให้ทำการ แทรก Pallet Code เข้าไปก่อน Remark
        if ($this->config->item('build_pallet')):
            array_splice($view['header'], array_search('Remark', $view['header']), 0, array(_lang('pallet_code')));
        endif;
        // END Add By Akkarapol, 06/02/2014, เพิ่มการตรวจสอบว่าถ้า config.build_pallet = TRUE ให้ทำการ แทรก Pallet Code เข้าไปก่อน Remark
        //ADD BY POR 2014-06-18 เพิ่มการตรวจสอบว่าถ้าเป็น price per unit ให้แทรก ข้อมูล price per unit เข้าไปหลัง  unit
        if ($this->settings['price_per_unit'] == TRUE):
            array_splice($view['header'], array_search('Unit', $view['header']) + 1, 0, array(_lang('price_per_unit'), _lang('unit_price'), _lang('all_price')));
        endif;

        $view['statusprice'] = $this->settings['price_per_unit'];
        //END ADD
//        p($view);exit();

        $view['body'] = $data_views;

        $this->load->view('report/receive_excel_template', $view);
    }

    //+++++END Receive report



    function export_putaway_pdf_by_pallet($search = NULL) {

        $search = $this->input->post();
        p($search);
        exit;

        #Load config (by kik : 20140708)
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
//        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_price_per_unit = FALSE; // force set price per unit is FALSE because operation can't see price of item
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $conf_show_column = empty($conf['show_column_report']['object']['putaway_job']) ? array() : @$conf['show_column_report']['object']['putaway_job'];

        if (empty($search)):
            $search = $this->input->post();
            if (!isset($search['document_no'])):
                $flow_detail = $this->flow->getFlowDetail($search['flow_id'], 'STK_T_Order');
            else:
                $order_data = $this->flow->getOrderDetailByDocumentNo($search['document_no']);
                if (empty($order_data)):
                    echo 'Sorry, can not find data.';
                    exit();
                endif;
                $search['flow_id'] = $order_data[0]->Flow_Id;
                $flow_detail = $this->flow->getFlowDetail_abnormal_flow($search['flow_id'], 'STK_T_Order');
            endif;
        else:
            $search = $this->input->get();
            $flow_detail = $this->flow->getFlowDetail_abnormal_flow($search['flow_id'], 'STK_T_Order');
        endif;

        $data = $flow_detail[0];

        $tmp = $this->company->getCompanyByID($data->Renter_Id);
        $tmp = $tmp->row_array();
        $data->Renter_Name = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($data->Source_Id);
        $tmp = $tmp->row_array();
        $data->Source_Name = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($data->Vendor_Id);
        $tmp = $tmp->row_array();
        $data->Vendor_Name = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($data->Destination_Id);
        $tmp = $tmp->row_array();
        $data->Consignee_Name = $tmp['Company_NameEN'];

        $tmp = $this->sys->getDomDetailByDomCode($data->Doc_Type);
        $tmp = $tmp->row_array();
        $data->Dispatch_Type = $tmp['Dom_EN_Desc'];

        $view['data'] = $data;

        $order_by = "STK_T_Order_Detail.Pallet_Id ASC, STK_T_Order_Detail.Item_Id ASC";

        /**
         * GET Activity By  : show pick by
         * @add code by kik : get sub_module for show activity by
         */
        $module_activity = 'putaway';
        $sub_module_activity = $this->order_movement->get_submodule_activity($data->Order_Id, $module_activity, 'cf_putaway_by');
        //end of get activity by

        /**
         * GET Order Detail
         */
        $order_detail_data = $this->stock->getOrderDetail($data->Order_Id, true, $order_by, $sub_module_activity, $module_activity); //add by kik : for change parameter to function : 20141004
//        $order_detail_data = $this->stock->getOrderDetail($data->Order_Id, TRUE, $order_by );// add by kik : 20140708 เรียกฟังก์ชั่นเดียวกับที่ใช้ที่หน้า PC//comment by kik : 20141004

        foreach ($order_detail_data as $key_detail => $detail):
            $view['order_detail'][$detail->Pallet_Code][$key_detail]['product_code'] = $detail->Product_Code;
            $view['order_detail'][$detail->Pallet_Code][$key_detail]['product_name'] = $detail->Full_Product_Name;
            $view['order_detail'][$detail->Pallet_Code][$key_detail]['product_status'] = $detail->Status_Value;
            $view['order_detail'][$detail->Pallet_Code][$key_detail]['product_sub_status'] = $detail->Sub_Status_Value;
            $view['order_detail'][$detail->Pallet_Code][$key_detail]['product_lot'] = $detail->Product_Lot;
            $view['order_detail'][$detail->Pallet_Code][$key_detail]['product_serial'] = $detail->Product_Serial;
            $view['order_detail'][$detail->Pallet_Code][$key_detail]['product_mfd'] = $detail->Product_Mfd;
            $view['order_detail'][$detail->Pallet_Code][$key_detail]['product_exp'] = $detail->Product_Exp;

            # add for show column Invoice No. (by kik : 20140708)
            if ($conf_inv):
                $view['order_detail'][$detail->Pallet_Code][$key_detail]['invoice_no'] = $detail->Invoice_No;
            endif;


            # add for show column Container (by kik : 20140708)
            if ($conf_cont):
                $view['order_detail'][$detail->Pallet_Code][$key_detail]['cont_no'] = $detail->Cont_No . " " . $detail->Cont_Size_No . " " . $detail->Cont_Size_Unit_Code;
            endif;

            $view['order_detail'][$detail->Pallet_Code][$key_detail]['reserv_qty'] = $detail->Reserv_Qty;
            $view['order_detail'][$detail->Pallet_Code][$key_detail]['confirm_qty'] = $detail->Confirm_Qty;
            $view['order_detail'][$detail->Pallet_Code][$key_detail]['unit'] = $detail->Unit_Value; // Add By Akkarapol, 10/01/2014, เปลี่ยนจากการแสดง Unit ด้วย Unit_Id ซึ่งเป็นการเก็บข้อมูลที่ผิดอยู่ มาเป็นแสดงด้วย Unit_Value ที่ได้ถูกดึงค่ามาจากการทำ UOM แล้ว
            //ADD BY POR 2014-01-14 เพิ่มให้ส่งค่าเกี่ยวกับ price unit ไปด้วย
            if ($conf_price_per_unit):
                $view['order_detail'][$detail->Pallet_Code][$key_detail]['price_per_unit'] = $detail->Price_Per_Unit;
                $view['order_detail'][$detail->Pallet_Code][$key_detail]['unit_price_value'] = $detail->Unit_Price_Id;
                $view['order_detail'][$detail->Pallet_Code][$key_detail]['all_price'] = $detail->All_Price;
            endif;
            //END ADD


            if (!empty($detail->Suggest_Location)):
                $view['order_detail'][$detail->Pallet_Code][$key_detail]['suggest_location'] = $detail->Suggest_Location;
            else:
                $setSuggestLocation = $this->suggest_location->getSuggestLocationArray('suggestLocation', $detail->Product_Code, $detail->Product_Status, '1', NULL, $detail->Sub_Status_Code, $detail->Confirm_Qty, $detail->Inbound_Item_Id); //EDIT BY POR 2014-05-27 เพิ่งให้ส่งค่า Item_Id และ 1 ไป (1 = type_item คือให้ค้นหาข้อมูลจากตาราง order_detail , 2 คือ ให้ค้นหาข้อมูลจากตาราง relocation)
                $view['order_detail'][$detail->Pallet_Code][$key_detail]['suggest_location'] = (!empty($setSuggestLocation) ? $setSuggestLocation[0]['Location_Code'] : NULL);
            endif;

            $view['order_detail'][$detail->Pallet_Code][$key_detail]['actual_location'] = $detail->Actual_Location;


            # add for show column Pallet. (by kik : 20140708)
            if ($conf_pallet):
                $view['order_detail'][$detail->Pallet_Code][$key_detail]['pallet_Code'] = $detail->Pallet_Code;
            endif;

//              $view['order_detail'][$detail->Pallet_Code][$key_detail]['pick_by'] = ($order_column->Activity_Code != 'PUTAWAY' ? '' : $order_column->Activity_By_Name); // Edit By Akkarapol, 20/11/2013, เพิ่มการตรวจสอบว่า ถ้า Activity_Code ไม่ใช่ PUTAWAY ล่ะก็ ไม่ต้องมาแสดง Activity By  // comment by kik : 20131203
            $view['order_detail'][$detail->Pallet_Code][$key_detail]['pick_by'] = ($detail->Activity_Code != 'PUTAWAY' ? '' : $detail->Activity_By_Name); // fix Activity by not show because add by kik : 20131203
            $view['order_detail'][$detail->Pallet_Code][$key_detail]['remark'] = $detail->Remark;

        endforeach;


        /**
         * Zone set data Header
         */
        $view['text_header'] = 'Putaway Job';
        $all_column = array();

        $all_column["no"] = _lang('no');
        $all_column["product_code"] = _lang('product_code');
        $all_column["product_name"] = _lang('product_name');
        $all_column["product_status"] = _lang('product_status');
        $all_column["product_sub_status"] = _lang('product_sub_status');
        $all_column["lot"] = _lang('lot');
        $all_column["serial"] = _lang('serial');
        $all_column["mfd"] = _lang('product_mfd');
        $all_column["exp"] = _lang('product_exp');


        # add for show column Invoice No. (by kik : 20140708)
        if ($conf_inv):
            $all_column["invoice"] = _lang('invoice_no');
        endif;


        # add for show column Container (by kik : 20140708)
        if ($conf_cont):
            $all_column["container"] = _lang('container');
        endif;

        $all_column["reserve_qty"] = _lang('reserve_qty');
        $all_column["confirm_qty"] = _lang('confirm_qty');
        $all_column["unit"] = _lang('unit');


        # add for show column Price per unit (by kik : 20140708)
        if ($conf_price_per_unit):
            $all_column["price_per_unit"] = _lang('price_per_unit');
            $all_column["unit_price"] = _lang('unit_price');
            $all_column["all_price"] = _lang('all_price');
        endif;

        $all_column["suggest_location"] = _lang('suggest_location');
        $all_column["actual_location"] = _lang('actual_location');
        $all_column["pallet_code"] = _lang('pallet_code');
        $all_column["pick_by"] = _lang('putaway_by');
        $all_column["remark"] = _lang('remark');

        # add for show column Pallet. (by kik : 20140708)
        if ($conf_pallet):
            $all_column["pallet_code"] = _lang('pallet_code');
        endif;

        $all_column["pick_by"] = _lang('putaway_by');
        $all_column["remark"] = _lang('remark');

        //SET COLSPAN :ADD BY POR : 2014-09-05
        $putaway_report = empty($conf['show_column_report']['object']['putaway_job']) ? false : @$conf['show_column_report']['object']['putaway_job'];
        $column_result = colspan_report($putaway_report, $conf);
        //p($column_result); exit();
        $view['all_column'] = $column_result['all_column']; //column ทั้งหมดที่แสดง
        $view['colspan'] = $column_result['colspan_all']; //ส่ง colspan ทั้งหมดไป
        //END SET COLSPAN

        $view['column'] = $all_column;
        $view['conf_show_column'] = $conf_show_column;
        $view['set_css_for_show_column'] = "";
        $view['colspan_total'] = 8;
        $view['colspan_blank'] = 4;
        $set_css_for_show_column = '';

//        $key_pallet = array_search('green', $array); // $key = 2;
        if (!empty($conf_show_column)):

            $column_config = array();

            foreach ($conf_show_column as $k => $column):
                if (is_array($column)):  //check xml is array
                    foreach ($column as $keys => $val): //วนหาค่าที่อยู่ใน array เพิ่มเติม
                        if ($keys == 'value'):  //value = ค่าของ column ที่ต้องการให้ show hide
                            if (!$val):
                                if ($set_css_for_show_column == ''):
                                    $set_css_for_show_column .= " .{$k}";
                                else:
                                    $set_css_for_show_column .= ", .{$k}";
                                endif;
                            else:
                                array_push($column_config, $k);
                            endif;
                        endif;
                    endforeach;
                else:
                    if (!$column):
                        if ($set_css_for_show_column == ''):
                            $set_css_for_show_column .= " .{$k}";
                        else:
                            $set_css_for_show_column .= ", .{$k}";
                        endif;
                    else:
                        array_push($column_config, $k);
                    endif;

                endif; // end of check show column


            endforeach;  // end of foreach

            if ($set_css_for_show_column != ''):

                $set_css_for_show_column .= "{display:none;}";

            endif;

            $view['set_css_for_show_column'] = $set_css_for_show_column;

            #calculator colspan for Total buttom TEXT
            $view['colspan_total'] = array_search('reserve_qty', $column_config);

            if ($conf_pallet):
                if ($conf_show_column['pallet_code']):
                    $view['colspan_blank'] = 5;
                endif;
            endif;

        endif; // end of check conf hide/show column

        /**
         * Zone set data Footer
         */
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
//p($view);exit();
        //ADD BY POR 2013-12-19 เพิ่มข้อมูลสำหรับประกอบการแสดง footer ให้เซ็นชื่อของ PDF
        //----เพิ่มตัวแปร $revision สำหรับบอกว่าตอนนี้ PDF เป็นของ revision ไหน
        $revision = $this->revision;
        $view['revision'] = $revision;

        //---เงื่อนไขในการแสดง footer ถ้า showfooter เป็น show ให้แสดง ถ้าเป็นเป็น no ไม่แสดง
        if (!empty($search['showfooter'])):
            $view['showfooter'] = $search['showfooter'];
        else:
            $view['showfooter'] = 'no';
        endif;
        //END ADD
        $view['signature_report'] = empty($conf['signature_report']['putaway_job']) ? array() : @$conf['signature_report']['putaway_job'];

//        p($view);exit();

        $view['statusprice'] = $conf_price_per_unit;
        $view['conf_inv'] = $conf_inv;
        $view['conf_cont'] = $conf_cont;
        $view['conf_pallet'] = $conf_pallet;

//        p($view);exit();
        $this->load->view("report/export_putaway_pdf_by_pallet.php", $view);
    }

    function export_putaway_pdf_by_other($search = NULL) {

        #Load config (by kik : 20140708)
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
//        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_price_per_unit = FALSE; // force set price per unit is FALSE because operation can't see price of item
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $conf_show_column = empty($conf['show_column_report']['object']['putaway_job']) ? array() : @$conf['show_column_report']['object']['putaway_job'];


        if (empty($search)):
            $search = $this->input->post();
            if (!isset($search['document_no'])):
                $flow_detail = $this->flow->getFlowDetail($search['flow_id'], 'STK_T_Order');
            else:
                $order_data = $this->flow->getOrderDetailByDocumentNo($search['document_no']);
                if (empty($order_data)):
                    echo 'Sorry, can not find data.';
                    exit();
                endif;
                $search['flow_id'] = $order_data[0]->Flow_Id;
                $flow_detail = $this->flow->getFlowDetail_abnormal_flow($search['flow_id'], 'STK_T_Order');
            endif;
        else:
            $search = $this->input->get();
            $flow_detail = $this->flow->getFlowDetail_abnormal_flow($search['flow_id'], 'STK_T_Order');
        endif;

        $data = $flow_detail[0];


        $tmp = $this->company->getCompanyByID($data->Renter_Id);
        $tmp = $tmp->row_array();
        $data->Renter_Name = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($data->Source_Id);
        $tmp = $tmp->row_array();
        $data->Source_Name = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($data->Vendor_Id);
        $tmp = $tmp->row_array();
        $data->Vendor_Name = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($data->Destination_Id);
        $tmp = $tmp->row_array();
        $data->Consignee_Name = $tmp['Company_NameEN'];

        $tmp = $this->sys->getDomDetailByDomCode($data->Doc_Type);
        $tmp = $tmp->row_array();
        $data->Dispatch_Type = $tmp['Dom_EN_Desc'];

        $view['data'] = $data;


        /**
         * Zone set data Order Detail
         */
        #comment by kik : 20140708 for use function เดียวกับที่เรียกในหน้า putaway ปกติ
        //$order_detail_data = $this->pre_dispatch->queryDataFromOrderDetailId($data->Order_Id); //COMMENT BY POR 2013-12-06 ยกเลิก queryDataFromOrderDetailId เนื่องจากมีการเปลี่ยนการแสดงชื่อผู้ทำรายการโดยไปเรียกจาก log
//        $order_detail_data = $this->pre_dispatch->query_from_OrderDetailId($data->Order_Id, 8); //ADD BY POR 2013-12-06 function ที่ใช้แทน queryDataFromOrderDetailId โดยต้องส่ง parameter ไปอีก 1 ตัวคือ step ในการหาชื่อผู้ทำรายการ 8 คือผู้ทำรายการ putaway ซึ่งใช้กับ report นี้
//        $order_detail_data = $order_detail_data->result(); // Add By Akkarapol, 21/10/2013, เซ็ตค่าตัวแปรใหม่ เพราะค่าที่ Return มาจากฟังก์ชั่น queryDataFromOrderDetailId นั้นส่งมาเป็นแบบ $query เลย เพื่อให้รองรับการเรียกใช้งานที่หลากหลายขึ้น

        $order_by = '';
        if ($conf_pallet) {
            $order_by = "STK_T_Order_Detail.Pallet_Id ASC, STK_T_Order_Detail.Item_Id ASC";
        } else {
            $order_by = "STK_T_Order_Detail.Item_Id ASC";
        }

        /**
         * GET Activity By  : show pick by
         * @add code by kik : get sub_module for show activity by
         */
        $module_activity = 'putaway';
        $sub_module_activity = $this->order_movement->get_submodule_activity($data->Order_Id, $module_activity, 'cf_putaway_by');
        //end of get activity by

        /**
         * GET Order Detail
         */
        $order_detail_data = $this->stock->getOrderDetail($data->Order_Id, true, $order_by, $sub_module_activity, $module_activity); //add by kik : for change parameter to function : 20141004
//        $order_detail_data = $this->stock->getOrderDetail($data->Order_Id, TRUE, $order_by );// add by kik : 20140708 เรียกฟังก์ชั่นเดียวกับที่ใช้ที่หน้า PC//comment by kik : 20141004
//        p($order_detail_data);exit();
        foreach ($order_detail_data as $key_detail => $detail):
            $view['order_detail'][$key_detail]['product_code'] = $detail->Product_Code;
            $view['order_detail'][$key_detail]['product_name'] = $detail->Full_Product_Name;
            $view['order_detail'][$key_detail]['product_status'] = $detail->Status_Value;
            $view['order_detail'][$key_detail]['product_sub_status'] = $detail->Sub_Status_Value;
            $view['order_detail'][$key_detail]['product_lot'] = $detail->Product_Lot;
            $view['order_detail'][$key_detail]['product_serial'] = $detail->Product_Serial;
            $view['order_detail'][$key_detail]['product_mfd'] = $detail->Product_Mfd;
            $view['order_detail'][$key_detail]['product_exp'] = $detail->Product_Exp;

            # add for show column Invoice No. (by kik : 20140708)
            if ($conf_inv):
                $view['order_detail'][$key_detail]['invoice_no'] = $detail->Invoice_No;
            endif;


            # add for show column Container (by kik : 20140708)
            if ($conf_cont):
                $view['order_detail'][$key_detail]['cont_no'] = $detail->Cont_No . " " . $detail->Cont_Size_No . " " . $detail->Cont_Size_Unit_Code;
            endif;

            $view['order_detail'][$key_detail]['reserv_qty'] = $detail->Reserv_Qty;
            $view['order_detail'][$key_detail]['confirm_qty'] = $detail->Confirm_Qty;
            $view['order_detail'][$key_detail]['unit'] = $detail->Unit_Value; // Add By Akkarapol, 10/01/2014, เปลี่ยนจากการแสดง Unit ด้วย Unit_Id ซึ่งเป็นการเก็บข้อมูลที่ผิดอยู่ มาเป็นแสดงด้วย Unit_Value ที่ได้ถูกดึงค่ามาจากการทำ UOM แล้ว
            //ADD BY POR 2014-01-14 เพิ่มให้ส่งค่าเกี่ยวกับ price unit ไปด้วย
            if ($conf_price_per_unit):
                $view['order_detail'][$key_detail]['price_per_unit'] = $detail->Price_Per_Unit;
                $view['order_detail'][$key_detail]['unit_price_value'] = $detail->Unit_Price_Id;
                $view['order_detail'][$key_detail]['all_price'] = $detail->All_Price;
            endif;
            //END ADD


            if (!empty($detail->Suggest_Location)):
                $view['order_detail'][$key_detail]['suggest_location'] = $detail->Suggest_Location;
            else:
                $setSuggestLocation = $this->suggest_location->getSuggestLocationArray('suggestLocation', $detail->Product_Code, $detail->Product_Status, '1', NULL, $detail->Sub_Status_Code, $detail->Confirm_Qty, $detail->Inbound_Item_Id); //EDIT BY POR 2014-05-27 เพิ่งให้ส่งค่า Item_Id และ 1 ไป (1 = type_item คือให้ค้นหาข้อมูลจากตาราง order_detail , 2 คือ ให้ค้นหาข้อมูลจากตาราง relocation)
                $view['order_detail'][$key_detail]['suggest_location'] = (!empty($setSuggestLocation) ? $setSuggestLocation[0]['Location_Code'] : NULL);
            endif;

            $view['order_detail'][$key_detail]['actual_location'] = $detail->Actual_Location;


            # add for show column Pallet. (by kik : 20140708)
            if ($conf_pallet):
                $view['order_detail'][$key_detail]['pallet_Code'] = $detail->Pallet_Code;
            endif;

//              $view['order_detail'][$key_detail]['pick_by'] = ($order_column->Activity_Code != 'PUTAWAY' ? '' : $order_column->Activity_By_Name); // Edit By Akkarapol, 20/11/2013, เพิ่มการตรวจสอบว่า ถ้า Activity_Code ไม่ใช่ PUTAWAY ล่ะก็ ไม่ต้องมาแสดง Activity By  // comment by kik : 20131203
            $view['order_detail'][$key_detail]['pick_by'] = ($detail->Activity_Code != 'PUTAWAY' ? '' : $detail->Activity_By_Name); // fix Activity by not show because add by kik : 20131203
            $view['order_detail'][$key_detail]['remark'] = $detail->Remark;

        endforeach;


        /**
         * Zone set data Header
         */
        $view['text_header'] = 'Putaway Job';
        $all_column = array();

        $all_column["no"] = _lang('no');
        $all_column["product_code"] = _lang('product_code');
        $all_column["product_name"] = _lang('product_name');
        $all_column["product_status"] = _lang('product_status');
        $all_column["product_sub_status"] = _lang('product_sub_status');
        $all_column["lot"] = _lang('lot');
        $all_column["serial"] = _lang('serial');
        $all_column["mfd"] = _lang('product_mfd');
        $all_column["exp"] = _lang('product_exp');


        # add for show column Invoice No. (by kik : 20140708)
        if ($conf_inv):
            $all_column["invoice"] = _lang('invoice_no');
        endif;


        # add for show column Container (by kik : 20140708)
        if ($conf_cont):
            $all_column["container"] = _lang('container');
        endif;

        $all_column["reserve_qty"] = _lang('reserve_qty');
        $all_column["confirm_qty"] = _lang('confirm_qty');
        $all_column["unit"] = _lang('unit');


        # add for show column Price per unit (by kik : 20140708)
        if ($conf_price_per_unit):
            $all_column["price_per_unit"] = _lang('price_per_unit');
            $all_column["unit_price"] = _lang('unit_price');
            $all_column["all_price"] = _lang('all_price');
        endif;

        $all_column["suggest_location"] = _lang('suggest_location');
        $all_column["actual_location"] = _lang('actual_location');
        $all_column["pallet_code"] = _lang('pallet_code');
        $all_column["pick_by"] = _lang('putaway_by');
        $all_column["remark"] = _lang('remark');

        # add for show column Pallet. (by kik : 20140708)
        if ($conf_pallet):
            $all_column["pallet_code"] = _lang('pallet_code');
        endif;

        $all_column["pick_by"] = _lang('putaway_by');
        $all_column["remark"] = _lang('remark');

        //SET COLSPAN :ADD BY POR : 2014-09-05
        $putaway_report = empty($conf['show_column_report']['object']['putaway_job']) ? false : @$conf['show_column_report']['object']['putaway_job'];
        $column_result = colspan_report($putaway_report, $conf);
        //p($column_result); exit();
        $view['all_column'] = $column_result['all_column']; //column ทั้งหมดที่แสดง
        $view['colspan'] = $column_result['colspan_all']; //ส่ง colspan ทั้งหมดไป
        //END SET COLSPAN

        $view['column'] = $all_column;
        $view['conf_show_column'] = $conf_show_column;
        $view['set_css_for_show_column'] = "";
        $view['colspan_total'] = 8;
        $view['colspan_blank'] = 4;
        $set_css_for_show_column = '';

//        $key_pallet = array_search('green', $array); // $key = 2;
        if (!empty($conf_show_column)):

            $column_config = array();

            foreach ($conf_show_column as $k => $column):
                if (is_array($column)):  //check xml is array
                    foreach ($column as $keys => $val): //วนหาค่าที่อยู่ใน array เพิ่มเติม
                        if ($keys == 'value'):  //value = ค่าของ column ที่ต้องการให้ show hide
                            if (!$val):
                                if ($set_css_for_show_column == ''):
                                    $set_css_for_show_column .= " .{$k}";
                                else:
                                    $set_css_for_show_column .= ", .{$k}";
                                endif;
                            else:
                                array_push($column_config, $k);
                            endif;
                        endif;
                    endforeach;
                else:
                    if (!$column):
                        if ($set_css_for_show_column == ''):
                            $set_css_for_show_column .= " .{$k}";
                        else:
                            $set_css_for_show_column .= ", .{$k}";
                        endif;
                    else:
                        array_push($column_config, $k);
                    endif;

                endif; // end of check show column


            endforeach;  // end of foreach

            if ($set_css_for_show_column != ''):

                $set_css_for_show_column .= "{display:none;}";

            endif;

            $view['set_css_for_show_column'] = $set_css_for_show_column;

            #calculator colspan for Total buttom TEXT
            $view['colspan_total'] = array_search('reserve_qty', $column_config);

            if ($conf_pallet):
                if ($conf_show_column['pallet_code']):
                    $view['colspan_blank'] = 5;
                endif;
            endif;

        endif; // end of check conf hide/show column

        /**
         * Zone set data Footer
         */
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
//p($view);exit();
        //ADD BY POR 2013-12-19 เพิ่มข้อมูลสำหรับประกอบการแสดง footer ให้เซ็นชื่อของ PDF
        //----เพิ่มตัวแปร $revision สำหรับบอกว่าตอนนี้ PDF เป็นของ revision ไหน
        $revision = $this->revision;
        $view['revision'] = $revision;

        //---เงื่อนไขในการแสดง footer ถ้า showfooter เป็น show ให้แสดง ถ้าเป็นเป็น no ไม่แสดง
        if (!empty($search['showfooter'])):
            $view['showfooter'] = $search['showfooter'];
        else:
            $view['showfooter'] = 'no';
        endif;
        //END ADD
//        p($view);exit();

        $view['statusprice'] = $conf_price_per_unit;
        $view['conf_inv'] = $conf_inv;
        $view['conf_cont'] = $conf_cont;
        $view['conf_pallet'] = $conf_pallet;

//        p($view);exit();
        $this->load->view("report/export_putaway_pdf.php", $view);
    }


    function export_putaway_pallet () {

        date_default_timezone_set('Asia/Bangkok');
        $conf = $this->config->item('_xml');
        $this->load->model("pallet_model", "pallet");
        $this->load->model("user_model", "user");
        $this->load->library('pdf/mpdf');
        $document_no = $this->input->get("d");
	    $download_list = Array();

        #find all data in pallet and pallet detail
        $pallet_info = $this->pallet->get_pallet_detail_packing($document_no);
        $arr_pallet_id = array();

        foreach ($pallet_info as $pallet):
            $datas = $this->pallet->get_packingList_receive_reprint($pallet->Pallet_Id);
	    //p($datas); exit;
            $order_info = array();

            $order_info['Document_No'] = array();
            $order_info['Doc_Refer_Ext'] = array();

            $item_lists = array();
            $sum_all_qty = 0;
            $check_same_unit = true;
            $check_unit = '';
            foreach ($datas as $key => $data):
                if (!in_array($data['Document_No'], $order_info['Document_No'])):
                    array_push($order_info['Document_No'], $data['Document_No']);
                endif;

                if (!in_array($data['Doc_Refer_Ext'], $order_info['Doc_Refer_Ext'])):
                    array_push($order_info['Doc_Refer_Ext'], $data['Doc_Refer_Ext']);
                endif;
                $item_lists[$data['Product_Code']] = $data['Product_NameEN'];
                $sum_all_qty += $data['Confirm_Qty'];
                if ($key != 0):
                    if ($check_unit != $data['Unit_Value']):
                        $check_same_unit = false;
                    endif;
                endif;
                $check_unit = $data['Unit_Value'];
            endforeach;

            #get printby
            $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
            $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

            #get revision
            $revision = $this->revision;

            #add data in parameter for send view
            $view['order_info'] = $order_info;
            $view['pallet_info'] = $pallet;
            $view['datas'] = $datas;
            $view['printBy'] = $printBy;
            $view['revision'] = $revision;
            $view['statusprice'] = $conf['price_per_unit'];
            $view['status_cont'] = $conf['container'];
            $view['pallet_id'] = $pallet_id;
            $view['item_lists'] = $item_lists;
            $view['sum_all_qty'] = $sum_all_qty;
            $view['check_unit'] = ($check_same_unit ? $check_unit : '');
	    $file_name =  '/uploads/default/files/' . $pallet->Pallet_Code . time() . ".pdf";
	    $strSaveName = getcwd() . str_replace("./", "/", $settings['uploads']['upload_path']) . $file_name;
	    $strSaveName = str_replace("\\", "/", $strSaveName);
	    $download_list[] = $file_name;
	    $view['file_name'] = $strSaveName;
            $this->load->view("report/export_packingList_receive_lcb.php", $view , TRUE);

        endforeach;

	echo json_encode($download_list);

    }





    // Add By Akkarapol, 20/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ Print ใบ Putaway Job
    // MOdified by kik : 20140708 add config hide/show column and show follow config true/false in xml
    function export_putaway_pdf() {

        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $this->load->model("stock_model", "stock");
        $this->load->model("user_model", "user");


        $search = $this->input->get();

//        p($search);exit();
        if ($search['o'] == "pallet") :
            $this->export_putaway_pdf_by_pallet($search);
        else:
            $this->export_putaway_pdf_by_other($search);
        endif;
    }

    // END Add By Akkarapol, 20/11/2013, เพิ่มฟังก์ชั่นที่ใช้สำหรับ Print ใบ Putaway Job
    // Add By Akkarapol, 21/01/2013, เพิ่มฟังก์ชั่นสำหรับแสดงหน้า Report Stock Transfer
    function stock_transfer() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $report = '';
        $str_form = $this->parser->parse('form/stock_transfer_report', array("data" => $report), TRUE);
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Stock Transfer ' . date("d/m/Y")
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_hide"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
    }

    // END Add By Akkarapol, 21/01/2013, เพิ่มฟังก์ชั่นสำหรับแสดงหน้า Report Stock Transfer
    // Add By Akkarapol, 21/01/2013, เพิ่มฟังก์ชั่นสำหรับ Search Report Stock Transfer เพื่อนำไปแสดงใน dataTable
    function search_stock_transfer() {
        $search = $this->input->post();
        $report = $this->r->get_stock_trasfer($search);
        $view['data'] = $report;
        $view['search'] = $search;
        $this->load->view("report/stock_transfer_report.php", $view);
    }

    // END Add By Akkarapol, 21/01/2013, เพิ่มฟังก์ชั่นสำหรับ Search Report Stock Transfer เพื่อนำไปแสดงใน dataTable
    // Add By Akkarapol, 21/01/2013, Add function for export 'Report Stock Transfer' to PDF
    function export_stock_transfer_to_pdf() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $search = $this->input->post($search);
        $report = $this->r->get_stock_trasfer($search);
        $view['data'] = $report;
        $view['search'] = $search;

        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;

        $this->load->view("report/stock_transfer_pdf", $view);
    }

    // END Add By Akkarapol, 21/01/2013, Add function for export 'Report Stock Transfer' to PDF
    // Add By Akkarapol, 21/01/2013, Add function for export 'Report Stock Transfer' to Excel
    function export_stock_transfer_to_excel() {
        $search = $this->input->post();
        $report_list = $this->r->get_stock_trasfer($search);

        $report = array();
        foreach ($report_list as $key => $r) {
            $report[$key]['Estimate_Action_Date'] = $r->Estimate_Action_Date;
            $report[$key]['Doc_Refer_Ext'] = $r->Doc_Refer_Ext;
            $report[$key]['consignee'] = $r->consignee;
            $report[$key]['Product_Code'] = $r->Product_Code;
            $report[$key]['Product_NameEN'] = $r->Product_NameEN;
            //$report[$key]['Reserv_Qty'] = $r->Reserv_Qty; comment by por 2013-10-31 แก้ไขให้ส่งค่ากลับเป็น number_format
            $report[$key]['Reserv_Qty'] = array('align' => 'right', 'value' => set_number_format($r->Reserv_Qty));
            $report[$key]['Actual_Action_Date'] = $r->Actual_Action_Date;
            //$report[$key]['Confirm_Qty'] = $r->Confirm_Qty; comment by por 2013-10-31 แก้ไขให้ส่งค่ากลับเป็น number_format
            $report[$key]['Confirm_Qty'] = array('align' => 'right', 'value' => set_number_format($r->Confirm_Qty));
            if ($this->config->item('build_pallet')):
                $report[$key]['Pallet_Code'] = $r->Pallet_Code;
            endif;
            $report[$key]['Remark'] = $r->Remark;
        }

        $view['file_name'] = 'stock_transfer_report-' . date('Ymd') . '-' . generateRandomString(3);
        $view['header'] = array(
            _lang('asn_date')
            , _lang('document_no')
            , _lang('consinee')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('reserve_qty')
            , _lang('transfer_date')
            , _lang('transfer_qty') //START BY POR 2013-10-09 แก้ไข Dispatch Qty เป็น Dispatch Quantity
            , _lang('remark')
        );

        if ($this->config->item('build_pallet')):
            array_splice($view['header'], array_search('Remark', $view['header']), 0, array('Pallet Code'));
        endif;

        $view['body'] = $report;
        $this->load->view('excel_template', $view);
    }

    // END Add By Akkarapol, 21/01/2013, Add function for export 'Report Stock Transfer' to Excel
    #add for ISSUE3323 Build Pallet6 (print PDF,excel) : 20140203 : by kik
    function packingList_receive() {

        $conf = $this->config->item('_xml'); // By ball : 20140707
        #load model
        $this->load->model("pallet_model", "pallet");
        $this->load->model("user_model", "user");
        $this->load->library('pdf/mpdf');

        #get data
//      $pallet_id = $this->input->post();
        $pallet_id = 1;

        #find all data in pallet and pallet detail
        $pallet_info = $this->pallet->get_pallet_detail($pallet_id);
//        p($pallet_info);exit();
        $datas = $this->r->get_packingList_receive($pallet_id);
//        p($datas);exit();

        $order_info = array();

        $order_info['Document_No'] = array();
        $order_info['Doc_Refer_Ext'] = array();


        foreach ($datas as $data):
            if (!in_array($data['Document_No'], $order_info['Document_No'])):
                array_push($order_info['Document_No'], $data['Document_No']);
            endif;

            if (!in_array($data['Doc_Refer_Ext'], $order_info['Doc_Refer_Ext'])):
                array_push($order_info['Doc_Refer_Ext'], $data['Doc_Refer_Ext']);
            endif;

        endforeach;
//         p($order_info);exit();


        /**
         * set hide/show column
         */
        $all_column = array(
            "no" => _lang('no')
            , "product_code" => _lang('product_code')
            , "product_name" => _lang('product_name')
            , "product_status" => _lang('product_status')
            , "product_sub_status" => _lang('product_sub_status')
            , "lot" => _lang('lot')
            , "serial" => _lang('serial')
            , "mfd" => _lang('product_mfd')
            , "exp" => _lang('product_exp')
            , "qty" => _lang('qty')
            , "unit" => _lang('unit')
        );

        if ($conf['price_per_unit'] == TRUE):
            $all_column['price_per_unit'] = _lang('price_per_unit');
            $all_column['unit_price'] = _lang('unit_price');
            $all_column['all_price'] = _lang('all_price');
        endif;


        $view['column'] = $all_column;
        $view['conf_show_column'] = array();
        $view['set_css_for_show_column'] = "";
        $set_css_for_show_column = '';
        $view['colspan_total'] = 9;
//        p($all_column);exit();

        if (!empty($conf['show_column_report']['object']['packing_list'])):

            $conf_show_column = $conf['show_column_report']['object']['packing_list'];

            $column_config = array();

            foreach ($conf_show_column as $k => $column):


                if (!$column):

                    if ($set_css_for_show_column == ''):
                        $set_css_for_show_column .= " .{$k}";
                    else:
                        $set_css_for_show_column .= ", .{$k}";
                    endif;

                else:

                    array_push($column_config, $k);

                endif; // end of check show column


            endforeach;  // end of foreach


            if ($set_css_for_show_column != ''):

                $set_css_for_show_column .= "{display:none;}";

            endif;

            $view['set_css_for_show_column'] = $set_css_for_show_column;
            $view['conf_show_column'] = $conf['show_column_report']['object']['packing_list'];


            #calculator colspan for Total buttom TEXT
            $view['colspan_total'] = array_search('qty', $column_config);


        endif; // end of check conf hide/show column
        #get printby
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];


        #get revision
        $revision = $this->revision;


        #add data in parameter for send view
        $view['order_info'] = $order_info;
        $view['pallet_info'] = $pallet_info; //p($pallet_info);exit();
        $view['datas'] = $datas;
        $view['printBy'] = $printBy;
        $view['revision'] = $revision;
        $view['statusprice'] = $this->settings['price_per_unit'];
        $view['status_cont'] = $conf['container'];
        $view['pallet_id'] = $pallet_id;

//        p($view);exit();

        $this->load->view("report/export_packingList_receive.php", $view);
    }

    #add for ISSUE3323 Build Pallet6 (print PDF,excel) : 20140203 : by kik

    function packingList_putaway() {

        #load model
        $this->load->model("pallet_model", "pallet");
        $this->load->model("user_model", "user");
        $this->load->library('pdf/mpdf');

        #get data
        $pallet_id = $this->input->post();
        $pallet_id = 2;
        #find all data in pallet and pallet detail
        $pallet_info = $this->pallet->get_pallet_detail($pallet_id);
        $datas = $this->r->get_packingList_putaway($pallet_id);
//        p($datas);exit();
        $order_info = array();
        $order_info['Document_No'] = array();
        $order_info['Doc_Refer_Ext'] = array();
        foreach ($datas as $data):
            if (!in_array($data['Document_No'], $order_info['Document_No'])):
                array_push($order_info['Document_No'], $data['Document_No']);
            endif;

            if (!in_array($data['Doc_Refer_Ext'], $order_info['Doc_Refer_Ext'])):
                array_push($order_info['Doc_Refer_Ext'], $data['Doc_Refer_Ext']);
            endif;

        endforeach;
//         p($order_info);exit();
        $view['column'] = array(
            "No."
            , "Product Code"
            , "Product Name"
            , "Product Status"
            , "Product Sub Status"
            , "Lot"
            , "Serial"
            , "Product Mfd."
            , "Product Exp."
            , "Qty."
            , "Unit"
        );

        if ($this->settings['price_per_unit'] == TRUE):
            array_push($view['column'], "Price/Unit");
            array_push($view['column'], "Unit Price");
            array_push($view['column'], "All Price");
        endif;

        #get printby
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        #get revision
        $revision = $this->revision;

        #add data in parameter for send view
        $view['order_info'] = $order_info;
        $view['pallet_info'] = $pallet_info; //p($pallet_info);exit();
        $view['datas'] = $datas;
        $view['printBy'] = $printBy;
        $view['revision'] = $revision;
        $view['statusprice'] = $this->settings['price_per_unit'];
        $view['pallet_id'] = $pallet_id;

        $this->load->view("report/export_packingList_putaway.php", $view);
    }

    #add for ISSUE3323 Build Pallet6 (print PDF,excel) : 20140203 : by kik

    function packingList_picking() {

        #get data
        $pallet = $this->input->post();

        #tmp get data
        #========================================================================
//        $pallet_Tmp=array();
//        array_push($pallet_Tmp, '2');
//        array_push($pallet_Tmp, '3');
//
//        $pallet = json_encode($pallet_Tmp);
        #------------------------------------------------------------------------
        #end tmp get data

        $pallet_ids = json_decode($pallet);

        foreach ($pallet_ids as $pallet_id) :
            $this->export_packingList_picking($pallet_id);
        endforeach;
    }

    #add for ISSUE3323 Build Pallet6 (print PDF,excel) : 20140203 : by kik

    function export_packingList_picking($pallet_id) {

        #load model
        $this->load->model("pallet_model", "pallet");
        $this->load->model("user_model", "user");
        $this->load->library('pdf/mpdf');

        #find all data in pallet and pallet detail
        $pallet_info = $this->pallet->get_pallet_detail($pallet_id);
        $datas = $this->r->get_packingList_putaway($pallet_id);

        $order_info = array();
        $order_info['Document_No'] = array();
        $order_info['Doc_Refer_Ext'] = array();
        foreach ($datas as $data):
            if (!in_array($data['Document_No'], $order_info['Document_No'])):
                array_push($order_info['Document_No'], $data['Document_No']);
            endif;

            if (!in_array($data['Doc_Refer_Ext'], $order_info['Doc_Refer_Ext'])):
                array_push($order_info['Doc_Refer_Ext'], $data['Doc_Refer_Ext']);
            endif;

        endforeach;

        $view['column'] = array(
            _lang('no')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('product_status')
            , _lang('product_sub_status')
            , _lang('lot')
            , _lang('serial')
            , _lang('product_mfd')
            , _lang('product_exp')
            , _lang('qty')
            , _lang('unit')
        );

        if ($this->settings['price_per_unit'] == TRUE):
            array_push($view['column'], "Price/Unit");
            array_push($view['column'], "Unit Price");
            array_push($view['column'], "All Price");
        endif;

        #============================= Start data picking ===========================================
        $picking_details = array();

        #add data of pallet detail and calculate
        $picking_datas = $this->r->get_packingList_picking($pallet_id);
        foreach ($picking_datas as $key => $value) :
            $picking_details[$value['Parent_Id']]['pallet_detail'][$key + 1] = $value;

        endforeach;
//p($datas);exit();
        #add data of order detail in $packing_details
        foreach ($datas as $key => $data):
            if (array_key_exists($data['Id'], $picking_details)):
                $picking_details[$data['Id']]['No.'] = $key + 1;
                $picking_details[$data['Id']]['Confirm_Qty'] = $data['Confirm_Qty'];
                $picking_details[$data['Id']]['Price_Per_Unit'] = $data['Price_Per_Unit'];
                $picking_details[$data['Id']]['Unit_Price_value'] = $data['Unit_Price_value'];
            endif;
        endforeach;

        #----------------------------- End data picking ---------------------------------------------
        #get printby
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        #get revision
        $revision = $this->revision;
//        p($packing_details);exit();
        #add data in parameter for send view
        $view['order_info'] = $order_info;
        $view['pallet_info'] = $pallet_info;
        $view['datas'] = $datas;
        $view['picking_details'] = $picking_details;
        $view['printBy'] = $printBy;
        $view['revision'] = $revision;
        $view['statusprice'] = $this->settings['price_per_unit'];
        $view['pallet_id'] = $pallet_id;

        $this->load->view("report/export_packingList_picking.php", $view);
    }

    #add for ISSUE3323 Build Pallet6 (print PDF,excel) : 20140205 : by kik

    function packingList_dispatch() {

        $pallet_id = $this->input->post();

        #load model
        $this->load->model("pallet_model", "pallet");
        $this->load->model("user_model", "user");
        $this->load->model("stock_model", "stock");
        $this->load->library('pdf/mpdf');

        #find all data in pallet and pallet detail
        $pallet_info = $this->pallet->get_pallet_detail($pallet_id);
        $datas = $this->r->get_packingList_dispatch($pallet_id);

        #order info data
        $column_order = "Document_No
            ,Doc_Refer_Ext
            ,(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Destination_Id) AS Consignee
            ,(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Source_Id) AS Supplier
            ,Vendor_Driver_Name";
        $where_order = "Order_Id = " . $datas[0]['Order_Id'];
        $order_info = $this->stock->getOrderTable($column_order, $where_order);

        if (!empty($order_info)):
            $order_info[0]->Pallet_code = $datas[0]['Pallet_Code'];
            $order_info[0]->Pallet_date = $datas[0]['Pallet_date'];
        endif;
//        p($order_info);
//        exit();
        $view['column'] = array(
            _lang('no')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('product_status')
            , _lang('product_sub_status')
            , _lang('lot')
            , _lang('serial')
            , _lang('product_mfd')
            , _lang('product_exp')
            , _lang('qty')
            , _lang('unit')
        );

        if ($this->settings['price_per_unit'] == TRUE):
            array_push($view['column'], "Price/Unit");
            array_push($view['column'], "Unit Price");
            array_push($view['column'], "All Price");
        endif;

        #get printby
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        #get revision
        $revision = $this->revision;

        #add data in parameter for send view
        $view['order_info'] = $order_info;
        $view['pallet_info'] = $pallet_info;
        $view['datas'] = $datas;
        $view['printBy'] = $printBy;
        $view['revision'] = $revision;
        $view['statusprice'] = $this->settings['price_per_unit'];
        $view['pallet_id'] = $pallet_id;

        $this->load->view("report/export_packingList_dispatch.php", $view);
    }

    /**
     * @function inventory_swa : this function use for swa user and show total , booked (booked is process in pre-dispatch - picking module)
     * @author kik
     * @created : 20140428
     * @return Api Gen
     *
     */
    public function inventory_swa() {

        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set เน�เธซเน� $this->output->enable_profiler เน€เธ�เน�เธ� เธ�เน�เธฒเธ•เธฒเธกเธ—เธตเน�เน€เธ�เน�เธ•เธกเธฒเธ�เธฒเธ� config เน€เธ�เธทเน�เธญเน�เธ�เน�เน�เธชเธ”เธ� DebugKit

        $this->load->model("warehouse_model", "wh");
        $this->load->model("product_status_model", "prod_status");

        $parameter['renter_id'] = $this->config->item('renter_id');
        $parameter['consignee_id'] = $this->config->item('owner_id');
        $parameter['owner_id'] = $this->config->item('owner_id');

        #Get renter list
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE);
        $parameter['renter_list'] = $renter_list;

        # Get Product Category
        $q_category = $this->product->productCategory();
        $r_category = $q_category->result();
        $category_list = genOptionDropdown($r_category, "SYS");
        $parameter['category_list'] = $category_list;

        #Get Status list
        $q_status = $this->prod_status->getProductStatus();
        $r_status = $q_status->result();
        // p($q_status->result());exit;
        $status_list = genOptionDropdown($r_status, "SYS");
        $parameter['status_list'] = $status_list;
        // p($parameter['status_list']);exit;

        $parameter['user_id'] = $this->session->userdata('user_id');

        if (!empty($_POST['show_auto'])):

            $product_id = $this->input->post('product_id');
            if ($this->input->post('product_id') == "" && $this->input->post('product_code') != ""):
                $product_id = $this->product->getProductIDByProdCode($this->input->post('product_code'));
                if (empty($product_id)):
                    $product_id = "";
                endif;
            endif;

            $parameter['product_name'] = $this->input->post('product_name');

            if ($this->input->post('product_code') != ""):
                $parameter['product_name'] = $this->input->post('product_code') . " " . $this->input->post('product_name');
            endif;

            $parameter['product_id'] = $product_id;
            $parameter['tdate'] = $this->input->post('tdate');
            $parameter['renter_id'] = $this->input->post('renter_id');
            $parameter['show_auto'] = $this->input->post('show_auto');

        else:
            $parameter['show_auto'] = 'N';
            $parameter['product_id'] = '';
            $parameter['tdate'] = '';
            $parameter['product_name'] = '';
        endif;


        $str_form = $this->parser->parse("form/inventory_report_swa", $parameter, TRUE);


        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Inventory'
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPreDispatch"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />' //ADD BY POR 2013-11-05 เธ�เน�เธญเธ�เธ�เธธเน�เธก print เธ�เน�เธญเธ� เธ�เน�เธญเธขเน�เธ�เน€เธ�เธดเธ”เธ•เธญเธ�เธ�เน�เธ�เธซเธฒเธ�เน�เธญเธกเธนเธฅเน�เธฅเน�เธง
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />' //ADD BY POR 2013-11-05 เธ�เน�เธญเธ�เธ�เธธเน�เธก print เธ�เน�เธญเธ� เธ�เน�เธญเธขเน�เธ�เน€เธ�เธดเธ”เธ•เธญเธ�เธ�เน�เธ�เธซเธฒเธ�เน�เธญเธกเธนเธฅเน�เธฅเน�เธง
        ));
    }

    /**
     * @function inventory_swa : this function use for swa user and show total , booked (booked is process in pre-dispatch - picking module)
     * @author kik
     * @created : 20140428
     * @return Api Gen
     *
     */
//    public function inventory() {
    public function inventory_backup() {

        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $this->load->model("warehouse_model", "wh");
        $this->load->model("product_status_model", "prod_status");

        $parameter['renter_id'] = $this->config->item('renter_id');
        $parameter['consignee_id'] = $this->config->item('owner_id');
        $parameter['owner_id'] = $this->config->item('owner_id');

        #Get renter list
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE);
        $parameter['renter_list'] = $renter_list;

        # Get Product Category
        $q_category = $this->product->productCategory();
        $r_category = $q_category->result();
        $category_list = genOptionDropdown($r_category, "SYS");
        $parameter['category_list'] = $category_list;

        #Get Status list
        $q_status = $this->prod_status->getProductStatus();
        $r_status = $q_status->result();
        $status_list = genOptionDropdown($r_status, "SYS");
        $parameter['status_list'] = $status_list;

        $parameter['user_id'] = $this->session->userdata('user_id');

        if (!empty($_POST['show_auto'])):

            $product_id = $this->input->post('product_id');
            if ($this->input->post('product_id') == "" && $this->input->post('product_code') != ""):
                $product_id = $this->product->getProductIDByProdCode($this->input->post('product_code'));
                if (empty($product_id)):
                    $product_id = "";
                endif;
            endif;

            $parameter['product_name'] = $this->input->post('product_name');

            if ($this->input->post('product_code') != ""):
                $parameter['product_name'] = $this->input->post('product_code') . " " . $this->input->post('product_name');
            endif;

            $parameter['product_id'] = $product_id;
            $parameter['tdate'] = $this->input->post('tdate');
            $parameter['renter_id'] = $this->input->post('renter_id');
            $parameter['show_auto'] = $this->input->post('show_auto');

        else:
            $parameter['show_auto'] = 'N';
            $parameter['product_id'] = '';
            $parameter['tdate'] = '';
            $parameter['product_name'] = '';
        endif;


        $str_form = $this->parser->parse("form/inventory_report_swa", $parameter, TRUE);


        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Inventory'
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPreDispatch"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
        ));
    }

    /**
     * @function showInventoryReport_swa สำหรับ ค้นหา balance , booked , dispatch realtime ของ swa เท่านั้น!
     * @author : kik : 2014-04-29
     */
    function showInventoryReport_swa() {

        #Load config
        $conf = $this->config->item('_xml');

        $inventory_report = empty($conf['show_column_report']['object']['inventory_swa_report']) ? false : @$conf['show_column_report']['object']['inventory_swa_report'];
        $conf_uom_qty = ($inventory_report['uom_qty']['value']) ? true : false;
        $conf_uom_unit_prod = ($inventory_report['uom_unit_prod']['value']) ? true : false;

        $search = $this->input->post();
        // p($search);
        $status_range = $this->r->getStatusRange($search['status_id']);
        //   p($status_range);exit;
        if (array_key_exists('PENDING', $status_range)):
            
            if (array_key_exists('NORMAL', $status_range)):
              
                $status_range = array_swap_assoc('NORMAL', 'PENDING', $status_range);
               
            endif;
        endif;
     
        $view['range'] = $status_range;
    
// p($view['range']);
// p($search);  

        // if($search['status_id'] == 'NORMAL'){

        // }
        $data = $this->r->searchInventoryToday_swa($search);
        // p($data);exit;
        $view['is_today'] = TRUE;

        $datas = array();
        if ($conf_uom_qty and $conf_uom_unit_prod):
            foreach ($data as $key => $row):
                $row->Uom_Qty = $this->r->convert_uom($row->Unit_Id, $row->totalbal, $row->Standard_Unit_In_Id);
                array_push($datas, $row);
            endforeach;
            $view['data'] = $datas;
        else:
            $view['data'] = $data;
        endif;

        $view['search'] = $search;
// p($view); exit;
        /**
         * set show/hide column calculate
         */
        $column_result = colspan_report($inventory_report, $conf);
        // p($column_result);exit;
        $view['all_column'] = $column_result['all_column'];
        $view['colspan'] = $column_result['colspan_all'];
        $view['show_hide'] = $column_result['show_hide'];

        $this->load->view("report/inventory_report_swa.php", $view);
    }

    /*     * edit show/hide column : by kik : 20140910 */

function exportInventoryToExcel_swa() {

         #Load config (add by kik : 20140723)
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        $conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];
        $inventory_report = empty($conf['show_column_report']['object']['inventory_swa_report'])?false:@$conf['show_column_report']['object']['inventory_swa_report'];
//        p($inventory_report);//exit();
        $conf_uom_qty = ($inventory_report['uom_qty']['value'])?true:false;
        $conf_uom_unit_prod = ($inventory_report['uom_unit_prod']['value'])?true:false;

        if(!empty($inventory_report)):
            $inventory_report['invoice'] = ($conf_inv && $inventory_report['invoice'])?true:false;
            $inventory_report['container'] = ($conf_cont && $inventory_report['container'])?true:false;
            $inventory_report['pallet_code'] = ($conf_pallet && $inventory_report['pallet_code'])?true:false;
        endif;



        $search = $this->input->post();
        $status_range = $this->r->getStatusRange($search['status_id']);

        if(array_key_exists('PENDING', $status_range)):
            $status_range = array_swap_assoc('NORMAL', 'PENDING', $status_range);
        endif;

        $ranges = $status_range;

        $view['range'] = $status_range;
        $data = $this->r->searchInventoryToday_swa($search);

        $datas = array();

        if($conf_uom_qty and $conf_uom_unit_prod):
            foreach ($data as $key => $row):
                $row->Uom_Qty = $this->r->convert_uom($row->Unit_Id,$row->totalbal,$row->Standard_Unit_In_Id);
                array_push($datas, $row);
            endforeach;
        else:
            $datas = $data;
        endif;

        $reports = array();
        $sum_balance_all=0;
        $sum_booked_all=0;
        $sum_dispatch_all=0;
        $sum_qty_uom_all=0;
        $sum_qty_root_unit=0;
        $sum_balance = array();
        $key_all = array();
        $key_status = array();

        foreach ($datas as $key => $data) {
                // p($data);
                // exit;
                unset($data->Invoice_Id);
                unset($data->Cont_Id);
                unset($data->Pallet_Id);
                unset($data->Standard_Unit_In_Id);
                unset($data->Unit_Id);
                unset($data->PKG);
                unset($data->Root_unit);

                $report['key'] = $key + 1;
                // p($key);
                // exit;
                foreach ($data as $key_index => $value) {
//                    echo $key_index."<br/>";
              
                    if ($key_index != 'Product_Status') {

                        //+++++ADD BY POR 2013-11-26
                        $counts = strpos($key_index, 'counts_'); //à¸•à¸£à¸§à¸ˆà¸ªà¸­à¸šà¸§à¹ˆà¸² key à¸¡à¸µà¸„à¸³à¸§à¹ˆà¸² counts_ à¸«à¸£à¸·à¸­à¹„à¸¡à¹ˆ à¸–à¹‰à¸²à¸¡à¸µà¹à¸ªà¸”à¸‡à¸§à¹ˆà¸²à¹€à¸›à¹‡à¸™à¸à¸²à¸£à¹à¸ªà¸”à¸‡à¸ˆà¸³à¸™à¸§à¸™ à¹ƒà¸«à¹‰à¸à¸³à¸«à¸™à¸”à¹ƒà¸«à¹‰à¸Šà¸´à¸”à¸‚à¸§à¸²

                        if($counts !== FALSE):
                            if(!key_exists($key_index, $sum_balance)):
                                $sum_balance[$key_index] = 0;
                                $sum_balance[$key_index] += $value;
                            else:
                                $sum_balance[$key_index] += $value;
                            endif;

                            if($key == 0):
                                array_push($key_status, $key_index);
                            endif;

                        else:
                            if($key == 0):
                                array_push($key_all, $key_index);
                            endif;

                        endif;

                        //à¸–à¹‰à¸²à¹€à¸›à¹‡à¸™à¸ˆà¸³à¸™à¸§à¸™à¸«à¸£à¸·à¸­ totalbal (à¸ˆà¸³à¸™à¸§à¸™ total à¸‚à¸­à¸‡ product)
                        if ($counts !== FALSE  || $key_index == 'totalbal' || $key_index == 'Booked' || $key_index == 'Dispatch') {
                            $value = array('align' => 'right', 'value' => set_number_format($value));
                        }


                    }

                     if(!empty($inventory_report)):

                            if($key_index == 'Product_Code'):
                                    ($inventory_report['product_code'])?array_push($report, $value):"";
                                elseif($key_index == 'Product_NameEN') :
                                        ($inventory_report['product_name'])?array_push($report, $value):"";
                                elseif($key_index == 'Product_Lot') :
                                        ($inventory_report['lot'])?array_push($report, $value):"";
                                elseif($key_index == 'Product_Serial') :
                                        ($inventory_report['serial'])?array_push($report, $value):"";
                                elseif($key_index == 'Product_Mfd') :
                                        ($inventory_report['product_mfd'])?array_push($report, $value):"";
                                elseif($key_index == 'Product_Exp') :
                                        ($inventory_report['product_exp'])?array_push($report, $value):"";
                                elseif($key_index == 'Invoice_No') :
                                       ($inventory_report['invoice'])?array_push($report, $value):"";
                                elseif($key_index == 'Cont') :
                                        ($inventory_report['container'])?array_push($report, $value):"";
                                elseif($key_index == 'Pallet_Code') :
                                        ($inventory_report['pallet_code'])?array_push($report, $value):"";
                                elseif($key_index == 'Unit_Value') :
                                        ($inventory_report['unit']['value'])?array_push($report, $value):"";
                                elseif($key_index == 'Uom_Qty') :
                                        $value = array('align' => 'right', 'value' => set_number_format($value));
                                        ($inventory_report['uom_qty']['value'])?array_push($report, $value):"";
                                elseif($key_index == 'Uom_Unit_Val') :
                                        ($inventory_report['uom_unit_prod']['value'])?array_push($report, $value):"";
                                else :array_push($report, $value);

                            endif;

                        endif; // end of check show/hide column for data
                }
            //    p($report);
            //    exit();
                array_push($reports, $report);
                unset($report);

             $sum_balance_all+= $data->totalbal;
             $sum_booked_all+= $data->Booked;
             $sum_dispatch_all+= $data->Dispatch;
             $sum_qty_uom_all+= $data->Uom_Qty;
             $sum_qty_root_unit+= $data->Root_Unit;

            }
    //    p($reports);exit();

            /** header area*/
             $view['header'] = array(
                'no' => _lang('no')
            ,'product_code' => _lang('product_code')
            ,'product_name' => _lang('product_name')
            ,'Doc_Refer_Ext' => _lang('doc_refer_ext')
            ,'lot' => _lang('lot')
            ,'serial' => _lang('serial')
            ,'product_mfd' => _lang('product_mfd')
            ,'product_exp' => _lang('product_exp')
            ,'invoice' => _lang('invoice_no')
            ,'container' =>_lang('container')
            ,'pallet_code' =>  _lang('pallet_code')

        );

        #check show/hide column
        if(!empty($inventory_report)):

            foreach ($inventory_report as $key_col => $column):

                if(!$column):

                    if(!empty($view['header'][$key_col])):
                        unset($view['header'][$key_col]);
                    endif; // end of check have in key

                endif;// end of check hide column

            endforeach; // end of for column config show/hide

        endif; //end of check inventory show/hide column

// p($ranges);
        foreach ($ranges as $col) {
            // p($col);
            array_push($view['header'], $col);
        }

        array_push($view['header'], _lang('Root Unit'));
        array_push($view['header'], _lang('total'));
        array_push($view['header'], _lang('booked'));
        array_push($view['header'], _lang('dispatch_qty'));

        if($inventory_report['unit']['value']):
            $view['header']['unit'] = _lang('unit');
        endif;

        if($inventory_report['uom_qty']['value']):
            $view['header']['uom_qty'] = _lang('uom_qty');
        endif;

        if($inventory_report['uom_unit_prod']['value']):
            $view['header']['uom_unit_prod'] = _lang('uom_unit_prod');
        endif;



        /**
         * Total area
         */

        $report_total = array();
        $report_total['key'] = 'Total';
    //     p($report_total);
    //    p($key_all);
    //    exit;

    //    p($view['header']);exit();
        foreach ($key_all as $key=>$val):
    
                if($val != 'Booked' && $val != 'Dispatch' && $val != 'totalbal'):

                if(!empty($inventory_report)):

                            if($value == 'Product_Code'):
                                    ($inventory_report['product_code'])?$report_total[] = " ":"";
                                    elseif($val == 'Product_NameEN') :
                                            ($inventory_report['product_name'])?$report_total[] = " ":"";
                                        elseif($val == 'Doc_Refer_Ext') :
                                              ($inventory_report['Doc_Refer_Ext'])?$report_total[] = " ":"";
                                          elseif($val == 'Product_Lot') :
                                                ($inventory_report['lot'])?$report_total[] = " ":"";
                                             elseif($val == 'Product_Serial') :
                                                    ($inventory_report['serial'])?$report_total[] = " ":"";
                                                elseif($val == 'Product_Mfd') :
                                                        ($inventory_report['product_mfd'])?$report_total[] = " ":"";
                                                    elseif($val == 'Product_Exp') :
                                                            ($inventory_report['product_exp'])?$report_total[] = " ":"";
                                                         elseif($val == 'Invoice_No') :
                                                               ($inventory_report['invoice'])?$report_total[] = " ":"";
                                                            elseif($val == 'Cont') :
                                                                    ($inventory_report['container'])?$report_total[] = " ":"";
                                                                elseif($val == 'Pallet_Code') :
                                                                        ($inventory_report['pallet_code'])?$report_total[] = " ":"";
                                elseif($val != 'Unit_Value' and $val != 'Uom_Qty' and $val != 'Uom_Unit_Val') :
                                    $report_total[] = " ";
                                

                            endif;

                        endif; // end of check show/hide column for data
                endif;

        endforeach;

        foreach ($key_status as $key=>$val):
        
            
                if(key_exists($val, $sum_balance)):
                    $report_total[] = array('align' => 'right', 'value' => set_number_format($sum_balance[$val]));
                endif;
        endforeach;

        foreach ($key_all as $key=>$val):
                if($val == 'Booked'):
                    $report_total[] = array('align' => 'right', 'value' => set_number_format($sum_booked_all));
                elseif($val == 'Dispatch'):
                    $report_total[] = array('align' => 'right', 'value' => set_number_format($sum_dispatch_all));
                elseif($val == 'Root_Unit'):
                    $report_total[] = array('align' => 'right', 'value' => set_number_format($sum_qty_root_unit));
                elseif($val == 'totalbal'):
                    $report_total[] = array('align' => 'right', 'value' => set_number_format($sum_balance_all));
                elseif($val == 'Unit_Value') :
                    ($inventory_report['unit'])?$report_total[] = " ":"";
                elseif($val == 'Uom_Qty'):
                    ($inventory_report['uom_qty']['value'])?$report_total[] = array('align' => 'right', 'value' => set_number_format($sum_qty_uom_all)):"";
                elseif($val == 'Uom_Unit_Val') :
                    ($inventory_report['uom_unit_prod'])?$report_total[] = " ":"";
                endif;
        endforeach;
    //    p($report_total);exit();
        array_push($reports, $report_total);
        // p($report_total);exit;
        /**
         * End Total area
         */

        $view['file_name'] = 'Invertory-'.date('Ymd-His');
        $view['body'] = $reports;

        $this->load->view('excel_template', $view);

    }
    function exportInventoryPdf_swa() {

        $this->load->library('pdf/mpdf');
        $this->load->model("company_model", "company");
        date_default_timezone_set('Asia/Bangkok');

        #Load config
        $conf = $this->config->item('_xml');
        $inventory_report = empty($conf['show_column_report']['object']['inventory_swa_report']) ? false : @$conf['show_column_report']['object']['inventory_swa_report'];

        $conf_uom_qty = ($inventory_report['uom_qty']['value']) ? true : false;
        $conf_uom_unit_prod = ($inventory_report['uom_unit_prod']['value']) ? true : false;

        $search = $this->input->post();

        #company
        $temp_company = $this->company->getCompanyByID($this->input->post('renter_id'));
        $temp_company2 = $temp_company->result();

        $status_range = $this->r->getStatusRange($search['status_id']);

        if (array_key_exists('PENDING', $status_range)):
            $status_range = array_swap_assoc('NORMAL', 'PENDING', $status_range);
        endif;

        $ranges = $status_range;
        $data = $this->r->searchInventoryToday_swa($search);

        $datas = array();
        if ($conf_uom_qty and $conf_uom_unit_prod):
            foreach ($data as $key => $row):
                $row->Uom_Qty = $this->r->convert_uom($row->Unit_Id, $row->totalbal, $row->Standard_Unit_In_Id);
                array_push($datas, $row);
            endforeach;
        else:
            $datas = $data;
        endif;

        $product_id = $search["product_id"];
        if ($product_id != "") {
            $view['product_name'] = $this->r->getProductDetailById($product_id);
        }

        $view['upload_path'] = $this->settings['uploads']['upload_path']; // Add By Akkarapol, 03/02/2014, เพิ่มการส่งค่าของ upload_path จาก native_session เพื่อนำไปใช้กับ view ให้สร้างไฟล์ตาม path ที่ตั้งค่าไว้

        $view['search'] = $search;
        $view['ranges'] = $ranges;
        $view['datas'] = $datas; //p($datas);exit();
        $view['Company_NameEN'] = $temp_company2[0]->Company_NameEN;

        // Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;


        /**
         * set show/hide column calculate
         */
        $column_result = colspan_report($inventory_report, $conf);


        $view['all_column'] = $column_result['all_column']; //column ทั้งหมดที่แสดง
        $view['colspan'] = $column_result['colspan_all']; //ส่ง colspan ทั้งหมดไป
        $view['show_hide'] = $column_result['show_hide']; //column show hide column
        $view['set_css_for_show_column'] = $column_result['set_css_for_show_column'];
//        p($view);exit();
        $this->load->view("report/exportInventory_swa", $view);
    }
 
       
        /**
         * inventory onhand report
        */

        public function inventory_onhand() {

            $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
            # Get Location
    //        $parameter['current_location'] = $q_current_location = $this->getSgLocationAll();
            #Get renter list
            $parameter['renter_id'] = $this->config->item('renter_id');
            $q_renter = $this->company->getRenterAll();
            $r_renter = $q_renter->result();
            $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE);
            $parameter['renter_list'] = $renter_list;
    
            $parameter['user_id'] = $this->session->userdata('user_id');
            $str_form = $this->parser->parse("form/inventory_onhand_form", $parameter, TRUE);
            // p($str_form);exit;
    
    
            # PUT FORM IN TEMPLATE WORKFLOW
            $this->parser->parse('workflow_template', array(
                'state_name' => 'Report : Inventory Onhand'
    //            , 'menu' => $this->menu->loadMenuAuth()
                , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
                , 'form' => $str_form
                , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="formLocationHistoryReport"></i>'
                , 'button_back' => ''
    //            , 'button_cancel' => ''
                , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
                , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
            ));
        }

        function get_inventory_onhand() {
            #Load config
            $conf = $this->config->item('_xml');
            $view['build_pallet'] = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
    
            $search = $this->input->get();
            $product_code = $search['txt_keyword'];
            // exit;
            $view['search'] = $search;
         
            //$this->load->library('pagination_ajax');    //COMMENT BY POR 2014-06-24 ย้ายไปไว้ข้างบน
            ###################################################
            #	Pagination
            ###################################################
            //$this->load->library('pagination_ajax');   //COMMENT BY POR 2014-06-24 ย้ายไปไว้ข้างบน
            $pages = new Pagination_ajax;
    
            $num_rows = sizeof($this->r->inventory_onhand($search, null, null)); // this is the COUNT(*) query that gets the total record count from the table you are querying
        //    p($num_rows); exit;
            $pages->items_total = $num_rows;
            $pages->mid_range = 5; // number of links you want to show in the pagination before the "..."
            $pages->paginate();
            $limit = json_decode($pages->limit);
            $view['low'] = $pages->low;
            $view['items_total'] = $num_rows;
    
            if ($pages->current_page == $pages->num_pages):
                $view['show_to'] = $num_rows;
            else:
                $view['show_to'] = $pages->low + $pages->items_per_page;
            endif;
    
    
            $view['display_items_per_page'] = $pages->display_items_per_page();
        //    p($limit);
            if (!empty($limit)):
                $view['data'] = $this->r->inventory_onhand($search, $limit[0], $limit[1]);
            else:
                $view['data'] = $this->r->inventory_onhand($search, null, null);
            endif;
    
            $view['pagination'] = $pages->display_pages();
            // p($view['data']); exit;
            ###################################################
            // Update Second Unit
            foreach ($view['data'] as $key => $detail) :
    
                // if ($detail->Unit_Id != NULL and $detail->Balance_Qty != NULL and $detail->Standard_Unit_In_Id != NULL):
                //     $view['data'][$key]->uom_qty = $this->r->convert_uom($detail->Unit_Id, $detail->Balance_Qty, $detail->Standard_Unit_In_Id);
                // else:
                //     $view['data'][$key]->uom_qty = $detail->Uom_Qty;
                // endif;
    
                // $view['data'][$key]->uom_unit_prod = $detail->Uom_Unit_Val;
                $view['data'][$key]->unit = $detail->Unit_Value;
    
            endforeach;
    // P($view); exit;
            $this->load->view("report/inventory_onhand_report.php", $view);
        }
    
        function inventory_onhand_toexcel() {
            $search = $this->input->post();
            $report = $this->r->inventory_onhand($search, null, null);
            $view['search'] = $search;
            $view['file_name'] = 'Inventory Oonhand Report-' . date('Ymd-His');
            $view['header'] = array(
                _lang('no')
                , _lang('Refer External No.')
                , _lang('Receive Date')
                , _lang('product_code')
                , _lang('product_name')
                , _lang('Lot/Serial')
                , _lang('Mfd.')
                , _lang('Exp.')
                , _lang('Product Status')
                , _lang('Type')
                , _lang('location')
                , _lang('Qty.')
                , _lang('remark')
                , _lang('unit')
                , _lang('Refer Internal No.')
            );
    
            // Add By Akkarapol, 06/02/2014, เพิ่มการตรวจสอบว่าถ้า config.build_pallet = TRUE ให้ทำการ แทรก Pallet Code เข้าไปก่อน Remark
            if ($this->config->item('build_pallet')):
                array_splice($view['header'], array_search('Remark', $view['header']), 0, array('Pallet Code'));
            endif;
            // END Add By Akkarapol, 06/02/2014, เพิ่มการตรวจสอบว่าถ้า config.build_pallet = TRUE ให้ทำการ แทรก Pallet Code เข้าไปก่อน Remark
    
            $view['body'] = $report;
            // p($view); exit;
            $this->load->view('report/inventory_onhand_excel_template', $view);
        
}


    public function location_swa() {

        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        # Get Location
//        $parameter['current_location'] = $q_current_location = $this->getSgLocationAll();
        #Get renter list
        $parameter['renter_id'] = $this->config->item('renter_id');
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE);
        $parameter['renter_list'] = $renter_list;

        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/location_report_swa", $parameter, TRUE);
        // p($str_form);exit;


        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Location'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="formLocationHistoryReport"></i>'
            , 'button_back' => ''
//            , 'button_cancel' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />' //ADD BY POR 2013-11-05 ซ่อนปุ่ม print ก่อน ค่อยไปเปิดตอนค้นหาข้อมูลแล้ว
        ));
    }

    function get_location_swa() {
        #Load config
        $conf = $this->config->item('_xml');
        $view['build_pallet'] = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

        $search = $this->input->get();
        // p($search); exit;
        // exit;
        $view['search'] = $search;
     
        //$this->load->library('pagination_ajax');    //COMMENT BY POR 2014-06-24 ย้ายไปไว้ข้างบน
        ###################################################
        #	Pagination
        ###################################################
        //$this->load->library('pagination_ajax');   //COMMENT BY POR 2014-06-24 ย้ายไปไว้ข้างบน
        $pages = new Pagination_ajax;

        $num_rows = sizeof($this->r->searchLocation_swa($search, null, null)); // this is the COUNT(*) query that gets the total record count from the table you are querying
        $pages->items_total = $num_rows;
        $pages->mid_range = 5; // number of links you want to show in the pagination before the "..."
        $pages->paginate();
        $limit = json_decode($pages->limit);
        $view['low'] = $pages->low;
        $view['items_total'] = $num_rows;

        if ($pages->current_page == $pages->num_pages):
            $view['show_to'] = $num_rows;
        else:
            $view['show_to'] = $pages->low + $pages->items_per_page;
        endif;


        $view['display_items_per_page'] = $pages->display_items_per_page();
    //    p($limit);
        if (!empty($limit)):
            $view['data'] = $this->r->searchLocation_swa($search, $limit[0], $limit[1]);
        else:
            $view['data'] = $this->r->searchLocation_swa($search, null, null);
        endif;

        $view['pagination'] = $pages->display_pages();

        ###################################################
        // Update Second Unit
        foreach ($view['data'] as $key => $detail) :

            if ($detail->Unit_Id != NULL and $detail->Balance_Qty != NULL and $detail->Standard_Unit_In_Id != NULL):
                $view['data'][$key]->uom_qty = $this->r->convert_uom($detail->Unit_Id, $detail->Balance_Qty, $detail->Standard_Unit_In_Id);
            else:
                $view['data'][$key]->uom_qty = $detail->Uom_Qty;
            endif;

            $view['data'][$key]->uom_unit_prod = $detail->Uom_Unit_Val;
            $view['data'][$key]->unit = $detail->Unit_Value;

        endforeach;

        $this->load->view("report/location_report_swa.php", $view);
    }

    function exportLocationToExcel_swa() {

        $search = $this->input->post();
        // p($search);exit();
     
        $view['search'] = $search;
        $datas = $this->r->searchLocation_swa($search, null, null);
        $view['data'] = $datas;
        $reports = array();
        $key_col = array();
        $sum_balance_all = 0;
        $sum_booked_all = 0;
        $sum_dispatch_all = 0;
        $sum_remain = 0;

        foreach ($datas as $k => $data) {

            $report = array();
            $report[] = $k + 1;

            $data->unit = $data->Unit_Value;

            if ($data->Unit_Id != NULL and $data->Balance_Qty != NULL and $data->Standard_Unit_In_Id != NULL):
                $data->uom_qty = $this->r->convert_uom($data->Unit_Id, $data->Balance_Qty, $data->Standard_Unit_In_Id);
            else:
                $data->uom_qty = $data->Uom_Qty;
            endif;

            $data->uom_unit_prod = $data->Uom_Unit_Val;

            if (property_exists($data, "Unit_Id"))
                unset($data->Unit_Id);
            if (property_exists($data, "Standard_Unit_In_Id"))
                unset($data->Standard_Unit_In_Id);
            if (property_exists($data, "Uom_Qty"))
                unset($data->Uom_Qty);
            if (property_exists($data, "Unit_Value"))
                unset($data->Unit_Value);
            if (property_exists($data, "Uom_Unit_Val"))
                unset($data->Uom_Unit_Val);
//            if (property_exists($data, "location"))
//                unset($data->location);

            foreach ($data as $key => $value) {

                if ($k == 0):
                    $key_col[] = $key;
                endif;

                if ($key == 'Balance_Qty' || $key == 'booked' || $key == 'dispatch' || $key == 'remain'):
                    $report[] = array('align' => 'right', 'value' => set_number_format($value));
                else:
                    array_push($report, $value);
                endif;
            }

            $sum_balance_all+= $data->Balance_Qty;
            $sum_booked_all+= $data->booked;
            $sum_dispatch_all+= $data->dispatch;
            $sum_remain+= $data->remain;

            array_push($reports, $report);

            unset($report);
        }
//        p($reports);
//        exit();
        /**
         * Total area
         */
        $report_total = array();
        $report_total['key'] = 'Total';

        foreach ($key_col as $key => $val):
            if ($val == 'Balance_Qty'):
                $report_total[] = array('align' => 'right', 'value' => set_number_format($sum_balance_all));
            elseif ($val == 'booked'):
                $report_total[] = array('align' => 'right', 'value' => set_number_format($sum_booked_all));
            elseif ($val == 'dispatch'):
                $report_total[] = array('align' => 'right', 'value' => set_number_format($sum_dispatch_all));
            elseif ($val == 'remain'):
                $report_total[] = array('align' => 'right', 'value' => set_number_format($sum_remain));
            else:
                $report_total[] = '';
            endif;
        endforeach;

//        p($report_total);exit();
        array_push($reports, $report_total);
        /**
         * End Total area
         */
        $view['file_name'] = 'Location report-' . date('Ymd-His');
        $view['body'] = $reports;

           $view['header'] = array(
            _lang('no')
            , _lang('Refer External No.')
            , _lang('Receive Date')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('lot')
            , _lang('serial')
            , _lang('Mfd.')
            , _lang('Exp.')
            , _lang('Product Status')
	        , _lang('Type')
            , _lang('location')
            , _lang('Balance Qty.')
            , _lang('RSV')
            , _lang('Pre-Dispatch')
            , _lang('Remain')
            , _lang('remark')
            , _lang('unit')
            , _lang('uom_qty')
            , _lang('uom_unit_prod')
        );
 

        if ($this->config->item('build_pallet')):
            $position_behind = array_search(_lang('remark'), $view['header']);  //find position for put pallet code (put before remark) : ADD BY POR 2015-02-23
            $view['header'] = array_merge(array_slice($view['header'], 0, $position_behind), array(_lang('pallet_code')), array_slice($view['header'], $position_behind));
        endif;

        //$view['header'] = array_merge($view['header'], array(_lang('remark'))); comment by por :2014-11-11 because have old remark in array header
        // p($view);
        // exit;
        $this->load->view('excel_template', $view);
    }

    function export_location_swa_ToPDF() {

        $this->load->library('pdf/mpdf');
        $this->load->model("company_model", "company");
        date_default_timezone_set('Asia/Bangkok');


        $search = $this->input->post();
        // p($search);
        // exit;
        $datas = $this->r->searchLocation_swa($search, null, null);

       // p($datas);exit();

        $report = array();

        #sort by location
        if ($search['sort_by'] == 'sort_loc'):

            foreach ($datas as $key => $value) {
                if (!array_key_exists($value->LocationReport, $report)):
                    $report[$value->LocationReport]['prod'] = array();
                    $report[$value->LocationReport]['sum_balance'] = 0;
                    $report[$value->LocationReport]['sum_booked'] = 0;
                    $report[$value->LocationReport]['sum_dispatch'] = 0;
                    $report[$value->LocationReport]['sum_remain'] = 0;
                    $report[$value->LocationReport]['sum_uom_qty'] = 0;
                endif;

                $hash_key = md5($value->Product_Code . $value->Uom_Unit_Val);

                if ($value->Unit_Id != NULL and $value->Balance_Qty != NULL and $value->Standard_Unit_In_Id != NULL):
                    $value->uom_qty = $this->r->convert_uom($value->Unit_Id, $value->Balance_Qty, $value->Standard_Unit_In_Id);
                else:
                    $value->uom_qty = $value->Uom_Qty;
                endif;

                if (!array_key_exists($hash_key, $report[$value->LocationReport]['prod'])):
                    $report[$value->LocationReport]['prod'][$hash_key]['product_code'] = $value->Product_Code;
                    $report[$value->LocationReport]['prod'][$hash_key]['product_name'] = $value->Product_NameEN;
                    $report[$value->LocationReport]['prod'][$hash_key]['product_status'] = $value->Product_Status;
                    $report[$value->LocationReport]['prod'][$hash_key]['balance_qty'] = $value->Balance_Qty;
                    $report[$value->LocationReport]['prod'][$hash_key]['booked_qty'] = $value->booked;
                    $report[$value->LocationReport]['prod'][$hash_key]['dispatch_qty'] = $value->dispatch;
                    $report[$value->LocationReport]['prod'][$hash_key]['remain_qty'] = $value->remain;
                    $report[$value->LocationReport]['prod'][$hash_key]['unit_value'] = $value->Unit_Value;
                    $report[$value->LocationReport]['prod'][$hash_key]['uom_unit_val'] = $value->Uom_Unit_Val;
                    $report[$value->LocationReport]['prod'][$hash_key]['uom_qty'] = $value->uom_qty;
                    $report[$value->LocationReport]['sum_balance'] += $value->Balance_Qty;
                    $report[$value->LocationReport]['sum_booked'] += $value->booked;
                    $report[$value->LocationReport]['sum_dispatch'] += $value->dispatch;
                    $report[$value->LocationReport]['sum_remain'] += $value->remain;
                    $report[$value->LocationReport]['sum_uom_qty'] += $value->uom_qty;
                else:
                    $report[$value->LocationReport]['prod'][$hash_key]['balance_qty'] += $value->Balance_Qty;
                    $report[$value->LocationReport]['prod'][$hash_key]['booked_qty'] += $value->booked;
                    $report[$value->LocationReport]['prod'][$hash_key]['dispatch_qty'] += $value->dispatch;
                    $report[$value->LocationReport]['prod'][$hash_key]['remain_qty'] += $value->remain;
                    $report[$value->LocationReport]['prod'][$hash_key]['uom_qty'] += $value->uom_qty;
                    $report[$value->LocationReport]['sum_balance'] += $value->Balance_Qty;
                    $report[$value->LocationReport]['sum_booked'] += $value->booked;
                    $report[$value->LocationReport]['sum_dispatch'] += $value->dispatch;
                    $report[$value->LocationReport]['sum_remain'] += $value->remain;
                    $report[$value->LocationReport]['sum_uom_qty'] += $value->uom_qty;
                endif;
            }

        #sort by product
        else:

            foreach ($datas as $key => $value) {

                if (!array_key_exists($value->Product_Code, $report)):
                    $report[$value->Product_Code]['loc'] = array();
                    $report[$value->Product_Code]['sum_balance'] = 0;
                    $report[$value->Product_Code]['sum_booked'] = 0;
                    $report[$value->Product_Code]['sum_dispatch'] = 0;
                    $report[$value->Product_Code]['sum_remain'] = 0;
//                       p($report);exit();
                endif;

                if (!array_key_exists($value->location, $report[$value->Product_Code]['loc'])):
//                          echo 'xxxx';exit();
                    $report[$value->Product_Code]['loc'][$value->location]['location'] = $value->location;
                    $report[$value->Product_Code]['loc'][$value->location]['balance_qty'] = $value->Balance_Qty;
                    $report[$value->Product_Code]['loc'][$value->location]['booked_qty'] = $value->booked;
                    $report[$value->Product_Code]['loc'][$value->location]['dispatch_qty'] = $value->dispatch;
                    $report[$value->Product_Code]['loc'][$value->location]['remain_qty'] = $value->remain;
                    $report[$value->Product_Code]['product_name'] = $value->Product_NameEN;
                    $report[$value->Product_Code]['loc'][$value->location]['product_status'] = $value->Product_Status;
                    $report[$value->Product_Code]['sum_balance'] += $value->Balance_Qty;
                    $report[$value->Product_Code]['sum_booked'] += $value->booked;
                    $report[$value->Product_Code]['sum_dispatch'] += $value->dispatch;
                    $report[$value->Product_Code]['sum_remain'] += $value->remain;

                else:
//                          echo 'yyyy';exit();
                    $report[$value->Product_Code]['loc'][$value->location]['balance_qty'] += $value->Balance_Qty;
                    $report[$value->Product_Code]['loc'][$value->location]['booked_qty'] += $value->booked;
                    $report[$value->Product_Code]['loc'][$value->location]['dispatch_qty'] += $value->dispatch;
                    $report[$value->Product_Code]['loc'][$value->location]['remain_qty'] += $value->remain;
                    $report[$value->Product_Code]['sum_balance'] += $value->Balance_Qty;
                    $report[$value->Product_Code]['sum_booked'] += $value->booked;
                    $report[$value->Product_Code]['sum_dispatch'] += $value->dispatch;
                    $report[$value->Product_Code]['sum_remain'] += $value->remain;
                endif;
            }

        endif;
//        p($report);exit();
        #company
        $temp_company = $this->company->getCompanyByID($this->input->post('renter_id'));
        $temp_company2 = $temp_company->result();
        $view['Company_NameEN'] = $temp_company2[0]->Company_NameEN;
        $view['upload_path'] = $this->settings['uploads']['upload_path']; // Add By Akkarapol, 03/02/2014, เพิ่มการส่งค่าของ upload_path จาก native_session เพื่อนำไปใช้กับ view ให้สร้างไฟล์ตาม path ที่ตั้งค่าไว้

        $view['search'] = $search;
        $view['datas'] = $report;

        // Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
// p($view); exit;
        if ($search['sort_by'] == 'sort_loc'):
            $this->load->view("report/exportLocation_swa_sortLoc", $view);
        else:
            $this->load->view("report/exportLocation_swa_sortProd", $view);
        endif;
    }

    function export_change_product_status_pdf() {
        $conf = $this->config->item('_xml');
        $conf_show_column = empty($conf['show_column_report']['object']['change_status_relocate']) ? false : @$conf['show_column_report']['object']['change_status_relocate'];

        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $search = $this->input->post();
//       p($search);
//       exit();
//
        // Add By Akkarapol, 06/05/2014, เพิ่มการ set show_column_relocation_job ที่เก็บไว้ใน cookie เพื่อเก็บไว้ว่า ต้องการใช้ column ไหนบ้างในการแสดงผล โดยจะทำการ serialize ก่อนทำการ set
        $this->load->helper('cookie');
        $cookie = array(
            'name' => 'show_column_change_product_status_job',
            'value' => serialize($search['show_column_change_product_status_job']),
            'expire' => '0'
        );
        set_cookie($cookie);
        // Add By Akkarapol, 06/05/2014, เพิ่มการ set show_column_relocation_job ที่เก็บไว้ใน cookie เพื่อเก็บไว้ว่า ต้องการใช้ column ไหนบ้างในการแสดงผล โดยจะทำการ serialize ก่อนทำการ set
        //ADD BY POR 2014-03-11 รับข้อมูลหน้า form มาเพื่อแสดงข้อมูลแบบ realtime
        $prod_list = $search["prod_list"];
        //p($prod_list);//exit();
        # Parameter Order relocation header
        $view['est_action_date'] = $search["est_action_date"];
        $view['action_date'] = $search["action_date"];
        $view['document_no'] = $search["document_no"];
        $view['worker_name'] = $search["worker_name"];

        # Parameter Index Datatable
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_product_status = $this->input->post("ci_prod_status");
        $ci_product_status_val = $this->input->post("ci_status_val");
        $ci_product_sub_status = $this->input->post("ci_prod_sub_status");
        $ci_product_sub_status_val = $this->input->post("ci_sub_status_val");
        $ci_product_lot = $this->input->post("ci_lot");
        $ci_product_serial = $this->input->post("ci_serial");
        $ci_mfd = $this->input->post("ci_mfd");
        $ci_exp = $this->input->post("ci_exp");
        $ci_reserv_qty = $this->input->post("ci_balance_qty");
        $ci_confirm_qty = $this->input->post("ci_confirm_qty");
        $ci_old_loc_id = $this->input->post("ci_old_loc_id");
        $ci_sug_loc_id = $this->input->post("ci_suggest_loc");
        $ci_act_loc_id = $this->input->post("ci_actual_loc");
        $ci_remark = $this->input->post("ci_remark");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_dp_type_pallet = $this->input->post("ci_dp_type_pallet");
        $ci_old_loc_name = $this->input->post("ci_old_loc_name");
        $ci_suggest_name = $this->input->post("ci_suggest_name");
        $ci_act_loc_name = $this->input->post("ci_actual_name");
        $ci_location_from = $this->input->post("ci_location_from");

        #ADD BY KIK 2014-01-16 เพิ่มเกี่ยวกับราคา
        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }
        #END ADD

        $data = $this->reLocation->getRelocationOrder($search["flow_id"]);
        $view['data'] = $data;
        $view['text_header'] = 'Change Product Status Job';

        if (!empty($data)):
            //BY POR 2014-03-17 แสดง Actual Location To  และ remark  แบบ realtime
            if (!empty($prod_list)):
                foreach ($prod_list as $key):
                    $k_data = explode(SEPARATOR, $key);
//            p($k_data);
                    $actual_location = $k_data[$ci_act_loc_name];
                    $remark = $k_data[$ci_remark];
                endforeach;
            endif;

//            $prod_list = $this->reLocation->getReLocationProductDetail($data[0]->Order_Id);   //COMMENT BY POR 2014-03-12 กรณีเรียกข้อมูลมาแสดงโดยผ่าน database แต่เนื่องจากเปลี่ยนเป็นแบบ realtime เลย comment ส่วนนี้ไว้
        endif;

        //นำ detail ที่ได้มาแทนค่าในตัวแปรตาม index ที่ต้องการ
        if (!empty($prod_list)) {
            $order_detail = array();
            foreach ($prod_list as $key => $rows) {
                $a_data = explode(SEPARATOR, $rows);
                //p($a_data[$ci_item_id]); exit();
//                p($a_data);
                //หารายละเอียดอื่นๆ ที่ไม่สามารถนำค่ามาแสดงได้ จึงต้องหาใน function เพิ่มเติม เช่น Product_Name เนื่องจากหน้า from แสดงไม่เต็มจึงต้องหาค่าใหม่, Location From และ Suggest Location เนื่องจากค่าที่ได้มาติดรูปแว่นขยาย จึงต้องหาค่าใหม่
                $pro_detail = $this->pending->getRelocateDetail((!empty($a_data[$ci_item_id]) ? $a_data[$ci_item_id] : $rows['item_id']))->result_array();

//                p($pro_detail);
                $order_detail[$key]['product_code'] = $a_data[$ci_prod_code];
                $order_detail[$key]['product_name'] = $pro_detail[0]["Product_NameEN"];
                $order_detail[$key]['product_status'] = $a_data[$ci_product_status_val];
//                $order_detail[$key]['Product_Sub_Status'] = $pro_detail[0]["Sub_Status_Value"] : $rows['Product_Sub_Status']);
                $order_detail[$key]['Product_Sub_Status'] = $a_data[$ci_product_sub_status_val];
                $order_detail[$key]['product_lot'] = $a_data[$ci_product_lot];
                $order_detail[$key]['product_serial'] = $a_data[$ci_product_serial];

//                $order_detail[$key]['product_mfd'] = $pro_detail[0]["P_Mfd"]:$rows['product_mfd']);
//                $order_detail[$key]['product_exp'] = $pro_detail[0]["P_Exp"]:$rows['product_exp']);
                $order_detail[$key]['product_mfd'] = $a_data[$ci_mfd];
                $order_detail[$key]['product_exp'] = $a_data[$ci_exp];
                $order_detail[$key]['reserv_qty'] = $a_data[$ci_reserv_qty];
                $order_detail[$key]['confirm_qty'] = $a_data[$ci_confirm_qty]; //ถ้ากรณี re-location by location จะ confirm เท่ากับจำนวน reserv เนื่องจากย้ายไปทั้งหมด

                if ($this->settings['price_per_unit'] == TRUE):
                    $order_detail[$key]['Price_Per_Unit'] = $a_data[$ci_price_per_unit];
                    $order_detail[$key]['Unit_Price_value'] = $a_data[$ci_unit_price]; // Add By Akkarapol, 10/01/2014, เพิ่มการเซ็ตค่า $pending_detail[$key]['Unit_Value'] = $a_data[$ci_unit_value]; เพื่อนำไปแสดงผลต่อด้วย Unit_Value
                    $order_detail[$key]['All_Price'] = $a_data[$ci_all_price];
                endif;

                $a_data[$ci_location_from] = explode('<a ', $a_data[$ci_location_from]);
                $a_data[$ci_location_from] = $a_data[$ci_location_from][0];
                $order_detail[$key]['from_location'] = ($a_data[$ci_location_from] == 'Edit...' ? '' : $a_data[$ci_location_from]);
                $a_data[$ci_suggest_name] = explode('<a ', $a_data[$ci_suggest_name]);
                $a_data[$ci_suggest_name] = $a_data[$ci_suggest_name][0];
                $order_detail[$key]['to_location'] = ($a_data[$ci_suggest_name] == 'Edit...' ? '' : $a_data[$ci_suggest_name]);
                $a_data[$ci_act_loc_name] = explode('<a ', $a_data[$ci_act_loc_name]);
                $a_data[$ci_act_loc_name] = $a_data[$ci_act_loc_name][0];
                $order_detail[$key]['act_location'] = ($a_data[$ci_act_loc_name] == 'Edit...' ? '' : $a_data[$ci_act_loc_name]);
                $order_detail[$key]['remark'] = $a_data[$ci_remark];
            }
        }

        $view['order_detail'] = $order_detail;

        #check if price_per_unit for show column Price / Unit,Unit Price,All Price
        // Edit By Akkarapol, 29/04/2014, add index ของ array  $view['column']  เพื่อนำไปใช้งานในส่วนของการทำ show/hide column
        $view['column'] = array(
            "no" => _lang('no')
            , "product_code" => _lang('product_code')
            , "product_name" => _lang('product_name')
            , "product_status" => _lang('product_status')
            , "product_sub_status" => _lang('product_sub_status')
            , "lot" => _lang('lot')
            , "serial" => _lang('serial')
            , "mfd" => _lang('product_mfd')
            , "exp" => _lang('product_exp')
            , "change_qty" => _lang('change_qty')
            , "confirm_qty" => _lang('confirm_qty')
//                , "unit" => "Unit"
            , "price_per_unit" => _lang('price_per_unit')
            , "unit_price" => _lang('unit_price')
            , "all_price" => _lang('all_price')
            , "location_form" => _lang('from_location')
            , "suggest_location" => _lang('suggest_location')
            , "actual_location" => _lang('actual_location')
            , "remark" => _lang('remark')
        );


        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;

        $column_result = colspan_report($conf_show_column, $conf, 1);

        $view['all_column'] = $column_result['all_column']; //column ทั้งหมดที่แสดง
        $view['colspan'] = $column_result['colspan_all']; //ส่ง colspan ทั้งหมดไป
        $view['setColspan'] = $column_result['colspan_all']; //ส่ง colspan ทั้งหมดไป
        $view['show_hide'] = $column_result['show_hide']; //column show hide column
        $view['set_css_for_show_column'] = $column_result['set_css_for_show_column'];

        // END Add By Akkarapol, 29/04/2014,  เพิ่มส่วนสำหรับจัดการ show/hide column ใน PDF ที่เป็นส่วนของ CSS, class div, class td, colspan และส่วนที่เกี่ยวข้อง



        $this->load->view("report/export_change_product_status_pdf.php", $view);
    }

    //ADD BY POR 2014-05-07
    function ajax_autocomplete_location_swa() {
        $text_search = $this->input->post('text_search'); //ข้อความที่ค้นหา
        $show_cond = $this->input->post('show_condition'); //เงื่อนไขในการค้นหา

        if ($show_cond == 'cond_lot_sel'):
            $product = $this->r->search_auto_lot_serial($show_cond, $text_search, 100);
        else:
            $product = $this->r->search_auto_inbound($show_cond, $text_search, 100);
        endif;


        $list = array();
        foreach ($product as $key_p => $p) {
            $list[$key_p]['value_select'] = $p['value_select'];
        }

        echo json_encode($list);
    }
    function booking_report(){

        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set เน�เธซเน� $this->output->enable_profiler เน€เธ�เน�เธ� เธ�เน�เธฒเธ•เธฒเธกเธ—เธตเน�เน€เธ�เน�เธ•เธกเธฒเธ�เธฒเธ� config เน€เธ�เธทเน�เธญเน�เธ�เน�เน�เธชเธ”เธ� DebugKit

        $this->load->model("warehouse_model", "wh");
        $this->load->model("product_status_model", "prod_status");

        $parameter['renter_id'] = $this->config->item('renter_id');
        $parameter['consignee_id'] = $this->config->item('owner_id');
        $parameter['owner_id'] = $this->config->item('owner_id');

        #Get renter list
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE);
        $parameter['renter_list'] = $renter_list;

        # Get Product Category
        $q_category = $this->product->productCategory();
        $r_category = $q_category->result();
        $category_list = genOptionDropdown($r_category, "SYS");
        $parameter['category_list'] = $category_list;

        #Get Status list
        $q_status = $this->prod_status->getProductStatus();
        $r_status = $q_status->result();
        $status_list = genOptionDropdown($r_status, "SYS");
        $parameter['status_list'] = $status_list;

        $parameter['user_id'] = $this->session->userdata('user_id');

        if (!empty($_POST['show_auto'])):

            $product_id = $this->input->post('product_id');
            if ($this->input->post('product_id') == "" && $this->input->post('product_code') != ""):
                $product_id = $this->product->getProductIDByProdCode($this->input->post('product_code'));
                if (empty($product_id)):
                    $product_id = "";
                endif;
            endif;

            $parameter['product_name'] = $this->input->post('product_name');

            if ($this->input->post('product_code') != ""):
                $parameter['product_name'] = $this->input->post('product_code') . " " . $this->input->post('product_name');
            endif;

            $parameter['product_id'] = $product_id;
            $parameter['tdate'] = $this->input->post('tdate');
            $parameter['renter_id'] = $this->input->post('renter_id');
            $parameter['show_auto'] = $this->input->post('show_auto');

        else:
            $parameter['show_auto'] = 'N';
            $parameter['product_id'] = '';
            $parameter['tdate'] = '';
            $parameter['product_name'] = '';
        endif;


        $str_form = $this->parser->parse("form/booking_form_report", $parameter, TRUE);


        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Booking'
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPreDispatch"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />' //ADD BY POR 2013-11-05 เธ�เน�เธญเธ�เธ�เธธเน�เธก print เธ�เน�เธญเธ� เธ�เน�เธญเธขเน�เธ�เน€เธ�เธดเธ”เธ•เธญเธ�เธ�เน�เธ�เธซเธฒเธ�เน�เธญเธกเธนเธฅเน�เธฅเน�เธง
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />' //ADD BY POR 2013-11-05 เธ�เน�เธญเธ�เธ�เธธเน�เธก print เธ�เน�เธญเธ� เธ�เน�เธญเธขเน�เธ�เน€เธ�เธดเธ”เธ•เธญเธ�เธ�เน�เธ�เธซเธฒเธ�เน�เธญเธกเธนเธฅเน�เธฅเน�เธง
        ));

    }

    function showBookingReport_swa() {

        #Load config
        $conf = $this->config->item('_xml');

        $inventory_report = empty($conf['show_column_report']['object']['inventory_swa_report']) ? false : @$conf['show_column_report']['object']['inventory_swa_report'];
//        p($inventory_report);exit();
        $conf_uom_qty = ($inventory_report['uom_qty']['value']) ? true : false;
        $conf_uom_unit_prod = ($inventory_report['uom_unit_prod']['value']) ? true : false;

        $search = $this->input->post();
        $status_range = $this->r->getStatusRange($search['status_id']);

        if (array_key_exists('PENDING', $status_range)):
            if (array_key_exists('NORMAL', $status_range)):
                $status_range = array_swap_assoc('NORMAL', 'PENDING', $status_range);
            endif;
        endif;

        $view['range'] = $status_range;

        $data = $this->r->searchBooking($search);
        $view['is_today'] = TRUE;

        //P('this>'.$data.'<');
        $datas = array();
        if ($conf_uom_qty and $conf_uom_unit_prod):
            foreach ($data as $key => $row):
                $row->Uom_Qty = $this->r->convert_uom($row->Unit_Id, $row->totalbal, $row->Standard_Unit_In_Id);
                if($row->Booked > 0){
                    array_push($datas, $row);
                }
                else{
                }
                
            endforeach;
            $view['data'] = $datas;
        else:
            $view['data'] = $data;
        endif;

        //p($view['data']);exit();
//        p($view['data']);exit();
        $view['search'] = $search;

        /**
         * set show/hide column calculate
         */
        $column_result = colspan_report($inventory_report, $conf);
//p($column_result);exit();
        $view['all_column'] = $column_result['all_column']; //column ทั้งหมดที่แสดง
        $view['colspan'] = $column_result['colspan_all']; //ส่ง colspan ทั้งหมดไป
        $view['show_hide'] = $column_result['show_hide']; //column show hide column
        //
        // p($view);
        // exit;
        $this->load->view("report/booking_report_swa.php", $view);
    }

    /*     * edit show/hide column : by kik : 20140910 */

    function exportBookingToExcel_swa() {

        #Load config (add by kik : 20140723)
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $inventory_report = empty($conf['show_column_report']['object']['inventory_swa_report']) ? false : @$conf['show_column_report']['object']['inventory_swa_report'];
//        p($inventory_report);//exit();
        $conf_uom_qty = ($inventory_report['uom_qty']['value']) ? true : false;
        $conf_uom_unit_prod = ($inventory_report['uom_unit_prod']['value']) ? true : false;

        if (!empty($inventory_report)):
            $inventory_report['invoice'] = ($conf_inv && $inventory_report['invoice']) ? true : false;
            $inventory_report['container'] = ($conf_cont && $inventory_report['container']) ? true : false;
            $inventory_report['pallet_code'] = ($conf_pallet && $inventory_report['pallet_code']) ? true : false;
        endif;



        $search = $this->input->post();
        $status_range = $this->r->getStatusRange($search['status_id']);

        if (array_key_exists('PENDING', $status_range)):
            $status_range = array_swap_assoc('NORMAL', 'PENDING', $status_range);
        endif;

        $ranges = $status_range;

        $view['range'] = $status_range;
        $data = $this->r->searchBooking($search);

        $datas = array();

        if ($conf_uom_qty and $conf_uom_unit_prod):
            foreach ($data as $key => $row):
                $row->Uom_Qty = $this->r->convert_uom($row->Unit_Id, $row->totalbal, $row->Standard_Unit_In_Id);
                array_push($datas, $row);
            endforeach;
        else:
            $datas = $data;
        endif;

        $reports = array();
        $sum_balance_all = 0;
        $sum_booked_all = 0;
        $sum_dispatch_all = 0;
        $sum_qty_uom_all = 0;
        $sum_balance = array();
        $key_all = array();
        $key_status = array();

        foreach ($datas as $key => $data) {
               
                unset($data->Invoice_Id);
                unset($data->Cont_Id);
                unset($data->Pallet_Id);
                unset($data->Standard_Unit_In_Id);
                unset($data->Unit_Id);

            $report['key'] = $key + 1;

            foreach ($data as $key_index => $value) {

                if ($key_index != 'Product_Status') {

                    //+++++ADD BY POR 2013-11-26
                    $counts = strpos($key_index, 'counts_'); //ตรวจสอบว่า key มีคำว่า counts_ หรือไม่ ถ้ามีแสดงว่าเป็นการแสดงจำนวน ให้กำหนดให้ชิดขวา

                    if ($counts !== FALSE):
                        if (!key_exists($key_index, $sum_balance)):
                            $sum_balance[$key_index] = 0;
                            $sum_balance[$key_index] += $value;
                        else:
                            $sum_balance[$key_index] += $value;
                        endif;

                        if ($key == 0):
                            array_push($key_status, $key_index);
                        endif;

                    else:
                        if ($key == 0):
                            array_push($key_all, $key_index);
                        endif;

                    endif;

                    //ถ้าเป็นจำนวนหรือ totalbal (จำนวน total ของ product)
                    if ($counts !== FALSE || $key_index == 'totalbal' || $key_index == 'Booked' || $key_index == 'Dispatch') {
                        $value = array('align' => 'right', 'value' => set_number_format($value));
                    }
                }

                if (!empty($inventory_report)):

                    if ($key_index == 'Product_Code'):
                        ($inventory_report['product_code']) ? array_push($report, $value) : "";
                    elseif ($key_index == 'Product_NameEN') :
                        ($inventory_report['product_name']) ? array_push($report, $value) : "";
                    elseif ($key_index == 'Product_Lot') :
                        ($inventory_report['lot']) ? array_push($report, $value) : "";
                    elseif ($key_index == 'Product_Serial') :
                        ($inventory_report['serial']) ? array_push($report, $value) : "";
                    elseif ($key_index == 'Product_Mfd') :
                        ($inventory_report['product_mfd']) ? array_push($report, $value) : "";
                    elseif ($key_index == 'Product_Exp') :
                        ($inventory_report['product_exp']) ? array_push($report, $value) : "";
                    elseif ($key_index == 'Invoice_No') :
                        ($inventory_report['invoice']) ? array_push($report, $value) : "";
                    elseif ($key_index == 'Cont') :
                        ($inventory_report['container']) ? array_push($report, $value) : "";
                    elseif ($key_index == 'Pallet_Code') :
                        ($inventory_report['pallet_code']) ? array_push($report, $value) : "";
                    elseif ($key_index == 'Unit_Value') :
                        ($inventory_report['unit']['value']) ? array_push($report, $value) : "";
                    elseif ($key_index == 'Uom_Qty') :
                        $value = array('align' => 'right', 'value' => set_number_format($value));
                        ($inventory_report['uom_qty']['value']) ? array_push($report, $value) : "";
                    elseif ($key_index == 'Uom_Unit_Val') :
                        ($inventory_report['uom_unit_prod']['value']) ? array_push($report, $value) : "";
                    else :array_push($report, $value);

                    endif;

                endif; // end of check show/hide column for data
            }

                array_push($reports, $report);
                unset($report);


            $sum_balance_all+= $data->totalbal;
            $sum_booked_all+= $data->Booked;
            $sum_dispatch_all+= $data->Dispatch;
            $sum_qty_uom_all+= $data->Uom_Qty;
        }
//        p($reports);exit();

        /** header area */
        $view['header'] = array(
            'no' => _lang('no')
            , 'Document External' => _lang('Document External')
            , 'product_code' => _lang('product_code')
            , 'product_name' => _lang('product_name')
            , 'lot' => _lang('lot')
            , 'serial' => _lang('serial')
            , 'product_mfd' => _lang('product_mfd')
            , 'product_exp' => _lang('product_exp')
            , 'invoice' => _lang('invoice_no')
            , 'container' => _lang('container')
            , 'pallet_code' => _lang('pallet_code')
        );


        #check show/hide column
        if (!empty($inventory_report)):

            foreach ($inventory_report as $key_col => $column):

                if (!$column):

                    if (!empty($view['header'][$key_col])):
                        unset($view['header'][$key_col]);
                    endif; // end of check have in key

                endif; // end of check hide column

            endforeach; // end of for column config show/hide

        endif; //end of check inventory show/hide column


        foreach ($ranges as $col) {
            array_push($view['header'], $col);
        }

        array_push($view['header'], _lang('total'));
        array_push($view['header'], _lang('booked'));
        array_push($view['header'], _lang('dispatch_qty'));

        if ($inventory_report['unit']['value']):
            $view['header']['unit'] = _lang('unit');
        endif;

        if ($inventory_report['uom_qty']['value']):
            $view['header']['uom_qty'] = _lang('uom_qty');
        endif;

        $view['header']['remark'] = _lang('remark');

        if($inventory_report['uom_unit_prod']['value']):
            $view['header']['uom_unit_prod'] = _lang('uom_unit_prod');
        endif;



        /**
         * Total area
         */
        $report_total = array();
        $report_total['key'] = 'Total';
        foreach ($key_all as $key => $val):

            if ($val != 'Booked' && $val != 'Dispatch' && $val != 'totalbal'):

                if (!empty($inventory_report)):

                    if ($value == 'Product_Code'):
                        ($inventory_report['product_code']) ? $report_total[] = " " : "";
                    elseif ($val == 'Product_NameEN') :
                        ($inventory_report['product_name']) ? $report_total[] = " " : "";
                    elseif ($val == 'Product_Lot') :
                        ($inventory_report['lot']) ? $report_total[] = " " : "";
                    elseif ($val == 'Product_Serial') :
                        ($inventory_report['serial']) ? $report_total[] = " " : "";
                    elseif ($val == 'Product_Mfd') :
                        ($inventory_report['product_mfd']) ? $report_total[] = " " : "";
                    elseif ($val == 'Product_Exp') :
                        ($inventory_report['product_exp']) ? $report_total[] = " " : "";
                    elseif ($val == 'Invoice_No') :
                        ($inventory_report['invoice']) ? $report_total[] = " " : "";
                    elseif ($val == 'Cont') :
                        ($inventory_report['container']) ? $report_total[] = " " : "";
                    elseif ($val == 'Pallet_Code') :
                        ($inventory_report['pallet_code']) ? $report_total[] = " " : "";
                    elseif ($val != 'Unit_Value' and $val != 'Uom_Qty' and $val != 'Uom_Unit_Val') :
                        $report_total[] = " ";

                    endif;

                endif; // end of check show/hide column for data
            endif;

        endforeach;

        array_pop($report_total);

        foreach ($key_status as $key=>$val):
                if(key_exists($val, $sum_balance)):
                    $report_total[] = array('align' => 'right', 'value' => set_number_format($sum_balance[$val]));
                endif;
        endforeach;

        foreach ($key_all as $key => $val):
            if ($val == 'Booked'):
                $report_total[] = array('align' => 'right', 'value' => set_number_format($sum_booked_all));
            elseif ($val == 'Dispatch'):
                $report_total[] = array('align' => 'right', 'value' => set_number_format($sum_dispatch_all));
            elseif ($val == 'totalbal'):
                $report_total[] = array('align' => 'right', 'value' => set_number_format($sum_balance_all));
            elseif ($val == 'Unit_Value') :
                ($inventory_report['unit']) ? $report_total[] = " " : "";
            elseif ($val == 'Uom_Qty'):
                ($inventory_report['uom_qty']['value']) ? $report_total[] = array('align' => 'right', 'value' => set_number_format($sum_qty_uom_all)) : "";
            elseif ($val == 'Uom_Unit_Val') :
                ($inventory_report['uom_unit_prod']) ? $report_total[] = " " : "";
            endif;
        endforeach;

        array_push($reports, $report_total);
        /**
         * End Total area
         */
        $view['file_name'] = 'Invertory-' . date('Ymd-His');
        $view['body'] = $reports;


        // p($view);exit();
        $this->load->view('excel_template', $view);
    }

    function exportBookingPdf_swa() {

        $this->load->library('pdf/mpdf');
        $this->load->model("company_model", "company");
        date_default_timezone_set('Asia/Bangkok');

        #Load config
        $conf = $this->config->item('_xml');
        $inventory_report = empty($conf['show_column_report']['object']['inventory_swa_report']) ? false : @$conf['show_column_report']['object']['inventory_swa_report'];

        $conf_uom_qty = ($inventory_report['uom_qty']['value']) ? true : false;
        $conf_uom_unit_prod = ($inventory_report['uom_unit_prod']['value']) ? true : false;

        $search = $this->input->post();

        #company
        $temp_company = $this->company->getCompanyByID($this->input->post('renter_id'));
        $temp_company2 = $temp_company->result();


        $status_range = $this->r->getStatusRange($search['status_id']);

        if (array_key_exists('PENDING', $status_range)):
            $status_range = array_swap_assoc('NORMAL', 'PENDING', $status_range);
        endif;
//        p($status_range);exit();
        $ranges = $status_range;
        $data = $this->r->searchBooking($search);

        $datas = array();
        if ($conf_uom_qty and $conf_uom_unit_prod):
            foreach ($data as $key => $row):

                $row->Uom_Qty = $this->r->convert_uom($row->Unit_Id, $row->totalbal, $row->Standard_Unit_In_Id);
                if($row->Booked > 0){
                    array_push($datas, $row);
                }
                else{

                }
                
            endforeach;
        else:
            $datas = $data;
        endif;



        $product_id = $search["product_id"];
        if ($product_id != "") {
            $view['product_name'] = $this->r->getProductDetailById($product_id);
        }

        $view['upload_path'] = $this->settings['uploads']['upload_path']; // Add By Akkarapol, 03/02/2014, เพิ่มการส่งค่าของ upload_path จาก native_session เพื่อนำไปใช้กับ view ให้สร้างไฟล์ตาม path ที่ตั้งค่าไว้

        $view['search'] = $search;
        $view['ranges'] = $ranges;
        $view['datas'] = $datas; //p($datas);exit();
        $view['Company_NameEN'] = $temp_company2[0]->Company_NameEN;

        // Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;


        /**
         * set show/hide column calculate
         */
        $column_result = colspan_report($inventory_report, $conf);


        $view['all_column'] = $column_result['all_column']; //column ทั้งหมดที่แสดง
        $view['colspan'] = $column_result['colspan_all']; //ส่ง colspan ทั้งหมดไป
        $view['show_hide'] = $column_result['show_hide']; //column show hide column
        $view['set_css_for_show_column'] = $column_result['set_css_for_show_column'];
//        p($view);exit();
        $this->load->view("report/exportBooking", $view);
    }
    function expired_ecolab(){
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $this->load->model("company_model", "company");
        $this->load->model("warehouse_model", "w");
        $this->load->model("product_model", "p");
        $this->load->model("product_status_model", "prod_status");
        $parameter['renter_id'] = $this->config->item('renter_id');
        $parameter['consignee_id'] = $this->config->item('owner_id');
        $parameter['owner_id'] = $this->config->item('owner_id');

        # Get Product Category
        $q_category = $this->p->productCategory();
        $r_category = $q_category->result();
        $category_list = genOptionDropdown($r_category, "SYS");
        $parameter['category_list'] = $category_list;

        #Get Status list
        $q_status = $this->prod_status->getProductStatus();
        $r_status = $q_status->result();
        $status_list = genOptionDropdown($r_status, "SYS");
        $parameter['status_list'] = $status_list;

        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/expire_form_ecolab", $parameter, TRUE);
        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Expire (Ecolab)'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form

            // Add By Akkarapol, 24/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPreDispatch"></i>'
            // END Add By Akkarapol, 24/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_back' => ''
            , 'button_cancel' => ''
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));

    }

    function expired_ecolab_data() {
        $search = $this->input->post();
        $data = $this->r->get_expired_ecolab($search);
            //    p(  $data); exit;
        $view['data'] = $data;
        $view['search'] = $search;
        $this->load->view("report/expired_ecolab_list.php", $view);
    }
    function expiredToExcel_ecolab() {
        $search = $this->input->post();
        $data = $this->r->get_expired_ecolab($search);
        // p($data);exit;
        $view['body'] = $data;
        $view['search'] = $search;
        $view['file_name'] = 'aging_report';
        $this->load->library('excel');
       // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Create a first sheet, representing sales data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Receipt Date');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Aging');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Receipt No');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Material No.');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Part Name');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Lot No.');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Remain Shelf Life');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Remain Product Life');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', 'MFD. Date');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', 'EXP. Date');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Shelf Life');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Life');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', 'Invoice No.');
        $objPHPExcel->getActiveSheet()->setCellValue('N1', 'BeginQty');
        $objPHPExcel->getActiveSheet()->setCellValue('O1', 'Qty.');
        $objPHPExcel->getActiveSheet()->setCellValue('P1', 'Location Alias');
        $objPHPExcel->getActiveSheet()->setCellValue('Q1', 'SubInventoryName');
        $objPHPExcel->getActiveSheet()->setCellValue('R1', 'Product Life Status');

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('N1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('O1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('P1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('Q1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('R1')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('J1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('K1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('L1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('M1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('N1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('O1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('P1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('Q1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('R1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

        $objPHPExcel->getActiveSheet()->getStyle('A1' . ':R1')->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);

        
        
        $objPHPExcel->getActiveSheet()->getStyle('R')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $rows = 2;
        foreach ($data as $key => $value) :
             $objPHPExcel->getActiveSheet()->getStyle('A'. $rows. ':R'.$rows)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
          if($value->Product_Life_Status == 'Life > 180 Days'){
              $color = '0EE000';
              $type = PHPExcel_Style_Fill::FILL_SOLID;
             
            }
            elseif($value->Product_Life_Status == '0 - 120 Days'){
              $color = 'EEE600';
              $type = PHPExcel_Style_Fill::FILL_SOLID;
            }
            elseif($value->Product_Life_Status == '120 - 180 Days'){
              $color = '96DF6D';
              $type = PHPExcel_Style_Fill::FILL_SOLID;
            }
            elseif($value->Product_Life_Status == 'Expired'){
              $color = 'FF0000';
              $type = PHPExcel_Style_Fill::FILL_SOLID;
            }
            
           
            if($value->Product_Life_Status == 'Expired' or $value->Product_Life_Status == '120 - 180 Days' 
            or $value->Product_Life_Status == '0 - 120 Days' or $value->Product_Life_Status == 'Life > 180 Days'){
            $objPHPExcel->getActiveSheet()->getStyle('R'. $rows)->applyFromArray(
            array(
                'fill' => array(
                    'type' => $type,
                    'color' => array('rgb' => $color)
                    
                )
            )
            );
            $objPHPExcel->getActiveSheet()->getStyle('A' . $rows)->getNumberFormat()->setFormatCode('mm-dd-yy');
            $objPHPExcel->getActiveSheet()->getStyle('I' . $rows)->getNumberFormat()->setFormatCode('mm-dd-yy');
            $objPHPExcel->getActiveSheet()->getStyle('J' . $rows)->getNumberFormat()->setFormatCode('mm-dd-yy');

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $rows, $value->Receive_Date);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $rows, $value->Aging);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $rows, $value->Pallet_Code);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $rows, $value->Product_Code);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $rows, $value->Product_NameEN);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $rows, $value->Product_Lot);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $rows, $value->Remain_Shelf_life);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $rows, $value->Remain_Product_life);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $rows, $value->MFD_Date);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $rows, $value->EXP_DATE);
	        $objPHPExcel->getActiveSheet()->setCellValue('K' . $rows, $value->Shelf_life);
  	        $objPHPExcel->getActiveSheet()->setCellValue('L' . $rows, $value->Life);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $rows, $value->INVOICENO);
	        $objPHPExcel->getActiveSheet()->setCellValue('N' . $rows, $value->BeginQty);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $rows, $value->BalQty);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $rows, $value->Location_Alias);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $rows, $value->SubInventoryName);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $rows, $value->Product_Life_Status);
            
            ++$rows;
          } 
           else{
                $objPHPExcel->getActiveSheet()->getStyle('R')->applyFromArray(
            array(
                'fill' => array(
                    'type' =>  PHPExcel_Style_Fill::FILL_NONE  
                )
            )
            );}
          
        endforeach;
    
        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Inventory Report');
        
        // Create a new worksheet, after the default sheet
        $objPHPExcel->createSheet();

        // Add some data to the second sheet, resembling some different data types
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Receipt Date');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Aging');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Receipt No');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Material No.');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Part Name');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Lot No.');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Remain Shelf Life');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Remain Product Life');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', 'MFD. Date');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', 'EXP. Date');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Shelf Life');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Life');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', 'Invoice No.');
        $objPHPExcel->getActiveSheet()->setCellValue('N1', 'BeginQty');
        $objPHPExcel->getActiveSheet()->setCellValue('O1', 'Qty.');
        $objPHPExcel->getActiveSheet()->setCellValue('P1', 'Location Alias');
        $objPHPExcel->getActiveSheet()->setCellValue('Q1', 'SubInventoryName');
        $objPHPExcel->getActiveSheet()->setCellValue('R1', 'Product Life Status');

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('N1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('O1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('P1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('Q1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('R1')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('J1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('K1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('L1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('M1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('N1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('O1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('P1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('Q1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('R1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

        $objPHPExcel->getActiveSheet()->getStyle('A1' . ':R1')->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        
        $objPHPExcel->getActiveSheet()->getStyle('R')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

        $rows = 2;
        foreach ($data as $key => $value) :
            
            if($value->Product_Life_Status == 'Expired'){
                $objPHPExcel->getActiveSheet()->getStyle('A'. $rows. ':R'.$rows)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
                $objPHPExcel->getActiveSheet()->getStyle('R'. $rows)->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FF0000')
                )
            )
            );
            $objPHPExcel->getActiveSheet()->getStyle('A' . $rows)->getNumberFormat()->setFormatCode('mm-dd-yy');
            $objPHPExcel->getActiveSheet()->getStyle('I' . $rows)->getNumberFormat()->setFormatCode('mm-dd-yy');
            $objPHPExcel->getActiveSheet()->getStyle('J' . $rows)->getNumberFormat()->setFormatCode('mm-dd-yy');

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $rows, $value->Receive_Date);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $rows, $value->Aging);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $rows, $value->Pallet_Code);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $rows, $value->Product_Code);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $rows, $value->Product_NameEN);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $rows, $value->Product_Lot);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $rows, $value->Remain_Shelf_life);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $rows, $value->Remain_Product_life);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $rows, $value->MFD_Date);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $rows, $value->EXP_DATE);
	        $objPHPExcel->getActiveSheet()->setCellValue('K' . $rows, $value->Shelf_life);
  	        $objPHPExcel->getActiveSheet()->setCellValue('L' . $rows, $value->Life);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $rows, $value->INVOICENO);
	        $objPHPExcel->getActiveSheet()->setCellValue('N' . $rows, $value->BeginQty);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $rows, $value->BalQty);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $rows, $value->Location_Alias);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $rows, $value->SubInventoryName);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $rows, $value->Product_Life_Status);
            
            ++$rows;
        }
        else{
                $objPHPExcel->getActiveSheet()->getStyle('R')->applyFromArray(
            array(
                'fill' => array(
                    'type' =>  PHPExcel_Style_Fill::FILL_NONE
                )
            )
            );}
        endforeach;
       

        // Rename 2nd sheet
        
        $objPHPExcel->getActiveSheet()->setTitle('Product Life = Expired');
        $objPHPExcel->getActiveSheet()->getTabColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
        $objPHPExcel->createSheet();

        // Add some data to the second sheet, resembling some different data types
        $objPHPExcel->setActiveSheetIndex(2);
        
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Receipt Date');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Aging');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Receipt No');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Material No.');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Part Name');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Lot No.');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Remain Shelf Life');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Remain Product Life');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', 'MFD. Date');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', 'EXP. Date');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Shelf Life');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Life');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', 'Invoice No.');
        $objPHPExcel->getActiveSheet()->setCellValue('N1', 'BeginQty');
        $objPHPExcel->getActiveSheet()->setCellValue('O1', 'Qty.');
        $objPHPExcel->getActiveSheet()->setCellValue('P1', 'Location Alias');
        $objPHPExcel->getActiveSheet()->setCellValue('Q1', 'SubInventoryName');
        $objPHPExcel->getActiveSheet()->setCellValue('R1', 'Product Life Status');

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('N1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('O1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('P1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('Q1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('R1')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('J1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('K1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('L1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('M1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('N1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('O1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('P1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('Q1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('R1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

        $objPHPExcel->getActiveSheet()->getStyle('A1' . ':R1')->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
        
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        
        $objPHPExcel->getActiveSheet()->getStyle('R')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $rows = 2;
        foreach ($data as $key => $value) :
             
            if($value->Product_Life_Status == '0 - 120 Days' ){
                 
                $objPHPExcel->getActiveSheet()->getStyle('R'. $rows)->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'EEE600')     
                )
                
            )
            );

            $objPHPExcel->getActiveSheet()->getStyle('A' . $rows)->getNumberFormat()->setFormatCode('mm-dd-yy');
            $objPHPExcel->getActiveSheet()->getStyle('I' . $rows)->getNumberFormat()->setFormatCode('mm-dd-yy');
            $objPHPExcel->getActiveSheet()->getStyle('J' . $rows)->getNumberFormat()->setFormatCode('mm-dd-yy');

            $objPHPExcel->getActiveSheet()->getStyle('A'. $rows. ':R'.$rows)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $rows, $value->Receive_Date);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $rows, $value->Aging);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $rows, $value->Pallet_Code);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $rows, $value->Product_Code);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $rows, $value->Product_NameEN);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $rows, $value->Product_Lot);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $rows, $value->Remain_Shelf_life);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $rows, $value->Remain_Product_life);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $rows, $value->MFD_Date);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $rows, $value->EXP_DATE);
	        $objPHPExcel->getActiveSheet()->setCellValue('K' . $rows, $value->Shelf_life);
  	        $objPHPExcel->getActiveSheet()->setCellValue('L' . $rows, $value->Life);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $rows, $value->INVOICENO);
	        $objPHPExcel->getActiveSheet()->setCellValue('N' . $rows, $value->BeginQty);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $rows, $value->BalQty);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $rows, $value->Location_Alias);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $rows, $value->SubInventoryName);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $rows, $value->Product_Life_Status);
            
            ++$rows;
        }
        else{
                $objPHPExcel->getActiveSheet()->getStyle('R')->applyFromArray(
            array(
                'fill' => array(
                   'type' =>  PHPExcel_Style_Fill::FILL_NONE
                )
            )
            );}
        
        endforeach;
        

        // Rename 2nd sheet
        $objPHPExcel->getActiveSheet()->setTitle('Product Life = 0 - 120 Days');
        $objPHPExcel->getActiveSheet()->getTabColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);

        
        // Redirect output to a client’s web browser (Excel5)
         $fileName = 'Inventory & Product Life Status' . date('Ymd') . '_' . time();
        $uploaddir = $this->settings['uploads']['upload_path'];

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($uploaddir . $fileName . '.xls');

        unset($data_prod);

        # Read Excel file.
        header('Content-Type: application/octet-stream charset=UTF-8');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $fileName . ".xls\"");
        readfile($uploaddir . $fileName . '.xls');
        // $this->load->view('report/aging_excel_template', $view);
        // $this->load->view('report/excel_ecolab_list', $view);
    }



}
