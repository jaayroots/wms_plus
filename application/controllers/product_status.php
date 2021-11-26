<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Product_status extends CI_Controller {

    public $settings;

    public function __construct() {
        parent::__construct();
        $this->load->helper('util_helper');
        $this->load->library('getlocation_no_dispatch_area'); //add by kik : 11-09-2013
        $isUserLogin = $this->session->userdata("user_id");
        if ($isUserLogin == "") {
            echo "<script>alert('Your session was expired. Please Log-in!')</script>";
            echo "<script>window.location.replace('" . site_url() . "');</script>";

        
        }

        // Add By Akkarapol, 03/10/2013, นำการ load ต่างๆ เข้ามาไว้ใน function __construct เพื่อจะได้โหลดใช้งานทีเดียวและไม่มีปัญหาเวลาเรียกใช้
        $this->load->model("company_model", "company");
        $this->load->model("contact_model", "contact");
        $this->load->model("pending_model", "pending");
        $this->load->model("product_model", "product");
        $this->load->model("product_status_model", "prod_status");
        $this->load->model("re_location_model", "relocate");
        $this->load->model("stock_model", "stock");
        $this->load->model("system_management_model", "sys");
        $this->load->model("workflow_model", "flow");
        // END Add By Akkarapol, 03/10/2013, นำการ load ต่างๆ เข้ามาไว้ใน function __construct เพื่อจะได้โหลดใช้งานทีเดียวและไม่มีปัญหาเวลาเรียกใช้
        $this->load->model("pallet_model", "pallet"); // add for ISSUE 3334 : by kik : 20140221
        $this->load->model("inbound_model", "inbound");  // add by kik(11-10-2013)
        $this->load->controller('balance', 'balance'); // add By Akkarapol, 03/10/2013, เพิ่มการ Load Controller 'balance' เข้ามาเพื่อเรียกใช้งาน
        # Load Model

        $this->settings = native_session::retrieve();
    }

    public function index() {
        $this->openForm($process_id = 8);
    }

    #Open Form

    function openForm($process_id, $present_state = 0) {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowChangeStatusList"); // Button Permission. Add by Ton! 20140131
//        $query = $this->pending->getPendingOrder();
//        $order_list = $query->result();


        #2395
        #by kik (11-09-2013)
        #เพิ่มการค้นหาด้วย product status และ sub status
        #Start new code
        #==========================================================
        #Get Product Status
        $q_product_status = $this->product->selectProductStatus();
        $r_product_status = $q_product_status->result();
//        $product_status_list = genOptionDropdown($r_product_status, "SYS");
        $product_status_list = genOptionDropdown($r_product_status, "SYS", FALSE, TRUE); // Edit by kik! 20131107
        $productStatus_select = form_dropdown("productStatus_select", $product_status_list, "", "id=productStatus_select");

        #Get Product Sub Status
        $q_product_Substatus = $this->product->selectSubStatus();
        $r_product_Substatus = $q_product_Substatus->result();
//        $product_Substatus_list = genOptionDropdown($r_product_Substatus, "SYS");
        $product_Substatus_list = genOptionDropdown($r_product_Substatus, "SYS", FALSE, TRUE); // Edit by kik! 20131107
        $productSubStatus_select = form_dropdown("productSubStatus_select", $product_Substatus_list, "", "id=productSubStatus_select");

        #End new code
        #==========================================================


        $parameter = array();
        $parameter['token'] = "";
        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['process_id'] = $process_id;
        $parameter['present_state'] = $present_state;
        $parameter['renter_id'] = $this->config->item('renter_id');
        $parameter['consignee_id'] = $this->config->item('owner_id');
        $parameter['owner_id'] = $this->config->item('owner_id');
        $parameter['data_form'] = (array) $data_form;
        //$parameter['order_list'] = $order_list;

        $parameter['statusprice'] = $this->settings['price_per_unit']; #ADD BY POR 2014-01-16
        $parameter['config_pallet'] = $this->config->item('build_pallet'); #add for ISSUE 333 : by kik : 20140220
        //comment by por 2013-10-02 แก้ไขให้เรียกใช้ของ Akkarapol ที่เขียนไว้ เพื่อให้นำ user_id มาใช้ แทน contact_id
        /* start comment
          #Get Assined [Worker] list
          $q_assign = $this->contact->getWorkerAll();
          $r_assign = $q_assign->result();
          $assign_list = genOptionDropdown($r_assign, "CONTACT");
          end comment */

        //start by por 2013-10-02 นำ function ที่ Akkarapol สร้างไว้มาใช้ โดยนำ user_id มาเก็บแทน contact_id
        $r_assign = $this->contact->getWorkerAllWithUserLogin()->result();
        $assign_list = genOptionDropdown($r_assign, "CONTACTWITHUSERLOGIN");
        //end

        $parameter['assign_list'] = $assign_list;
        $parameter['assigned_id'] = $this->session->userdata('user_id');

// p($data_form->form_name); exit;
        # LOAD FORM
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);
        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form

            // Add By Akkarapol, 23/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_ch_status"></i>'
            // END Add By Akkarapol, 23/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'productStatus_select' => $productStatus_select
            , 'productSubStatus_select' => $productSubStatus_select
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'button_action' => $data_form->str_buttun
        ));
    }

    #Open Form With Data

    function openActionForm() {

        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        $conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];


        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $flow_id = $this->input->post("id");

        #2395
        #by kik (11-09-2013)
        #เพิ่มการค้นหาด้วย product status และ sub status
        #Start new code
        #==========================================================
        #Get Product Status
        $q_product_status = $this->product->selectProductStatus();
        $r_product_status = $q_product_status->result();
//        $product_status_list = genOptionDropdown($r_product_status, "SYS");
        $product_status_list = genOptionDropdown($r_product_status, "SYS", FALSE, TRUE); // Edit by kik! 20131107
        $productStatus_select = form_dropdown("productStatus_select", $product_status_list, "", "id=productStatus_select");

        #Get Product Sub Status
        $q_product_Substatus = $this->product->selectSubStatus();
        $r_product_Substatus = $q_product_Substatus->result();
//        $product_Substatus_list = genOptionDropdown($r_product_Substatus, "SYS");
        $product_Substatus_list = genOptionDropdown($r_product_Substatus, "SYS", FALSE, TRUE); // Edit by kik! 20131107
        $productSubStatus_select = form_dropdown("productSubStatus_select", $product_Substatus_list, "", "id=productSubStatus_select");

        #End new product
        #==========================================================
        #Retrive Data from Table
        $flow_detail = $this->prod_status->getChangeStatusFlow($flow_id);
    //    p($flow_detail);exit;
        $parameter['process_id'] = $flow_detail[0]->Process_Id;
        $parameter['document_no'] = $flow_detail[0]->Document_No;
        $parameter['est_action_date'] = $flow_detail[0]->Estimate_Action_Date;
        $parameter['action_date'] = $flow_detail[0]->Actual_Action_Date;
        $parameter['present_state'] = $flow_detail[0]->Present_State;
        $parameter['process_type'] = $flow_detail[0]->Process_Type;
        $parameter['order_id'] = $flow_detail[0]->Order_Id;
        $parameter['assigned_id'] = $flow_detail[0]->Assigned_Id;
        $parameter['remark'] = $flow_detail[0]->Remark;
        $parameter['is_urgent'] = $flow_detail[0]->Is_urgent;
        $parameter['document_ref'] = $flow_detail[0]->Custom_Doc_Ref;  //ADD BY POR 2014-11-17 for customs report รายงานการนำของออกเพื่อการอื่นเป็นการชั่วคราวและนำกลับ
        $parameter['config_pallet'] = $this->config->item('build_pallet'); #add for ISSUE 333 : by kik : 20140220

        $parameter['token'] = register_token($flow_id, $flow_detail[0]->Present_State, $flow_detail[0]->Process_Id);
        $order_detail = $this->prod_status->getChangeStatusDetial($parameter['order_id']);
        $parameter['order_detail'] = $order_detail;
// p($order_detail); exit;
        #Get Assined [Worker] list
//		$q_assign = $this->contact->getWorkerAll();  // Comment By Akkarapol, 23/09/2013,
        $q_assign = $this->contact->getWorkerAllWithUserLogin();  // Add By Akkarapol, 23/09/2013, เปลี่ยนมาใช้แบบนี้เพราะให้ตารางหลักเป็น UserLogin จะได้เอาค่าไปใช้ต่อได้ง่ายๆ
        $r_assign = $q_assign->result();
//		$assign_list = genOptionDropdown($r_assign,"CONTACT"); // Comment By Akkarapol, 23/09/2013,
        $assign_list = genOptionDropdown($r_assign, "CONTACTWITHUSERLOGIN"); // Add By Akkarapol, 23/09/2013, เปลี่ยนมาใช้เพราะต้องการให้ค่า Index ที่ได้ออกมาเป็น UserLogin_Id แทนที่จะเป็น Contact_Id เพราะเห็นว่า อย่างไรก็เอา UserLogin_Id ไปเก็บเป็น Detail อยู่แล้ว จะได้ไม่ผิดที่เอา UserLogin_Id มาใช้บ้าง Contact_Id มาใช้บ้าง เพื่อให้เป็น มาตรฐานเดียวกัน
        $parameter['assign_list'] = $assign_list;


//        $data_form = $this->workflow->openWorkflowForm($parameter['process_id'], $parameter['present_state']);
        $data_form = $this->workflow->openWorkflowForm($parameter['process_id'], $parameter['present_state'], $this->session->userdata('user_id'), "flow/flowChangeStatusList"); // Button Permission. Add by Ton! 20140131

        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['flow_id'] = $flow_id;
        $parameter['data_form'] = (array) $data_form;

        $parameter['statusprice'] = $conf_price_per_unit; #ADD BY POR 2014-01-16
        $parameter['config_pallet'] = $conf_pallet;   #ADD BY kik : 2014-02-21
        $parameter['set_suggest'] = empty($this->settings['suggest_locate'])?FALSE:$this->settings['suggest_locate']; #ADD BY POR 2014-06-10 เพิ่มให้ส่งค่า setting ของ suggest ไปด้วย
 
        // Add By Akkarapol, 20140516, เพิ่มปุ่มสำหรับทำ PDF สำหรับขั้นตอน putaway
        if($parameter['present_state'] >= 3):
            $data_form->str_buttun .= '<input class="button dark_blue" type="button" onclick="exportFile(\'PDF\')" value="PDF">';
        endif;
        // END Add By Akkarapol, 20140516, เพิ่มปุ่มสำหรับทำ PDF สำหรับขั้นตอน putaway

        // p($data_form->form_name); exit;
        # LOAD FORM
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);
        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form

            // Add By Akkarapol, 23/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_receive"></i>'
            // END Add By Akkarapol, 23/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'productStatus_select' => $productStatus_select// add by kik
            , 'productSubStatus_select' => $productSubStatus_select// add by kik
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'button_action' => $data_form->str_buttun
        ));
    }

    #Action Open Process

    function openAction() {


        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        $conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        # Parameter Get session data
        $renter_id = $this->session->userdata('renter_id');
        $owner_id = $this->session->userdata('owner_id');
        $user_id = $this->session->userdata('user_id');

        # Parameter Form
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $document_no = $this->input->post("document_no");
        $est_action_date = convert_date_format($this->input->post("est_action_date"));
        $remark = $this->input->post("remark");
        $assigned_id = $this->input->post("assigned_id");
        $is_urgent = $this->input->post("is_urgent");

        $document_ref = $this->input->post("document_ref"); //ADD BY POR 2014-11-16  use customs report รายงานการนำำของออกเพื่อการอื่นเป็นการชั่วคราวและการนำกลับ

        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Parameter Order Detail
        $prod_list = $this->input->post("prod_list");
        $prod_del_list = $this->input->post("prod_del_list");


        # Parameter Index Datatable
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_lot = $this->input->post("ci_lot");
        $ci_serial = $this->input->post("ci_serial");
        $ci_mfd = $this->input->post("ci_mfd");
        $ci_exp = $this->input->post("ci_exp");
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_confirm_qty = $this->input->post("ci_confirm_qty");
        $ci_remark = $this->input->post("ci_remark");
        $ci_prod_id = $this->input->post("ci_prod_id");
        $ci_prod_status = $this->input->post("ci_prod_status");
        $ci_unit_id = $this->input->post("ci_unit_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");
        $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        $ci_actual_loc = $this->input->post("ci_actual_loc");
        $ci_old_loc = $this->input->post("ci_old_loc");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_dp_type_pallet = $this->input->post("ci_dp_type_pallet");

        # Parameter price per uint
        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }

        # Check validate data
        $return = array();

        # Check Estimate balance ตรวจสอบค่าของ PD_Reserve ว่ามีค่าพอที่จะใช้ในการทำขั้นตอนที่ต้องการหรือไม่
        $resultCompare = $this->balance->_chkPDreservBeforeOpen($ci_confirm_qty, $ci_inbound_id, $prod_list, SEPARATOR);
        $return = array_merge_recursive($return, $resultCompare);
        if (!empty($resultCompare['critical'])):
        /*
         * LOG MSG 01
         */
        endif;


        #หากมี error ที่ไม่สามารถปล่อยผ่านได้ จะ return กลับไปยังหน้า view เพื่อบังคับให้แก้ไขข้อมูลดังกล่าว
        if (!empty($return['critical'])) :

            $json['status'] = "validation";
            $json['return_val'] = $return;

        else:

            $check_not_err = TRUE; //for check error all process , if process error set value = FALSE
            # ================== Start save data =========================

            $this->transaction_db->transaction_start();

            # generate Change Status Document No.
            if ($check_not_err):
                $prefix = "CH";
//                $document_no = createDocumentNo($prefix);
                $document_no = create_document_no_by_type($prefix); // Add by Ton! 20140428
                $data['Document_No'] = $document_no;
                if ($document_no == "" || empty($document_no)) {
                    $check_not_err = FALSE;

                    /*
                     * LOG MSG 1
                     */
                    $return['critical'][]['message'] = "Can not create Document No.";
                }
            endif;

            # add New workflow
            if ($check_not_err):
                list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data); //Edit by Ton! 20131021
                if ($flow_id == "" || $action_id == "") {
                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 2
                     */
                    $return['critical'][]['message'] = "Can not add new work flow.";
                } else {
                    $order['Flow_Id'] = $flow_id;
                }
            endif;

            # Save new order
            if ($check_not_err):
                $order = array(
                    'Doc_Relocate' => $data['Document_No']
                    , 'Flow_Id' => $flow_id
                    , 'Doc_Type' => $process_type
                    , 'Process_Type' => $process_type
                    , 'Estimate_Action_Date' => $est_action_date
                    , 'Owner_Id' => $owner_id
                    , 'Renter_Id' => $renter_id
                    , 'Create_By' => $user_id
                    , 'Create_date' => date("Y-m-d H:i:s")
                    , 'Assigned_Id' => $assigned_id
                    , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                    , 'Is_urgent' => $is_urgent
                    , 'Custom_Doc_Ref' => $document_ref  //ADD BY POR  2014-11-17 for customs report รายงานการนำของออกเพื่อการอื่นเป็นการชั่วคราวและการนำกลับ
                );
                $order_id = $this->relocate->addReLocationOrder($order);
                if ($order_id == "" || empty($order_id)) {
                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 3
                     */
                    $return['critical'][]['message'] = "Can not add new order";
                }
            endif; //end check update order
            # Order Detail  - Set data and Save data into table
            if ($check_not_err):

                # add data into order detail
                $order_detail = array();

                if (!empty($prod_list)):

                    #set parameter for savr order detail
                    foreach ($prod_list as $rows) {
                        $a_data = explode(SEPARATOR, $rows);
                        $inbound_id = $a_data[$ci_inbound_id];

                        // Add By Akkarapol, 24/01/2014, เปลี่ยนการ ส่งค่า $column ไปให้ stock->getStockDetailId นั้น query ค่า เพราะถ้าจะ query แค่ '*' ค่าที่เป็น dateTime จะผิดและทำให้นำไปใช้งานต่อไม่ได้ เลยต้อง CONVERT ออกมาให้เป็น Type 21
                        $column = array(
                            '*'
                            , 'CONVERT(VARCHAR,Product_Mfd,21) as Product_Mfd'
                            , 'CONVERT(VARCHAR,Product_Exp,21) as Product_Exp'
                            , 'CONVERT(VARCHAR,Receive_Date,21) as Receive_Date'
                            , 'CONVERT(VARCHAR,Putaway_Date,21) as Putaway_Date'
                        );
                        // END Add By Akkarapol, 24/01/2014,  เปลี่ยนการ ส่งค่า $column ไปให้ stock->getStockDetailId นั้น query ค่า เพราะถ้าจะ query แค่ '*' ค่าที่เป็น dateTime จะผิดและทำให้นำไปใช้งานต่อไม่ได้ เลยต้อง CONVERT ออกมาให้เป็น Type 21

                        $where['Inbound_Id'] = $inbound_id;
                        $item_detail = $this->stock->getStockDetailId($column, $where)->result();

                        if (!empty($item_detail)):

                            foreach ($item_detail as $item) {
                            //    p($item);exit;
                                $detail['Document_No'] = $item->Document_No;
                                $detail['Doc_Refer_Int'] = $item->Doc_Refer_Int;
                                $detail['Doc_Refer_Ext'] = $item->Doc_Refer_Ext;
                                $detail['Doc_Refer_Inv'] = $item->Doc_Refer_Inv;
                                $detail['Doc_Refer_CE'] = $item->Doc_Refer_CE;
                                $detail['Doc_Refer_BL'] = $item->Doc_Refer_BL;
                                $detail['Doc_Refer_AWB'] = $item->Doc_Refer_AWB;
                                $detail['Inbound_Item_Id'] = $item->Inbound_Id;
                                $detail['Product_Id'] = $item->Product_Id;
                                $detail['Product_Code'] = $item->Product_Code;
                                $detail['Product_License'] = $item->Product_License;


								$detail['Old_Location_Id'] = $item->Actual_Location_Id;
								/*
                                $detail['Product_Lot'] = $item->Product_Lot;
                                $detail['Product_Serial'] = $item->Product_Serial;
                                $detail['Product_Mfd'] = $item->Product_Mfd;
                                $detail['Product_Exp'] = $item->Product_Exp;
								*/
								
                                $detail['Product_Lot'] = $a_data[$ci_lot];
                                $detail['Product_Serial'] = $a_data[$ci_serial];
								
								if ($a_data[$ci_mfd] != "") {
									$detail['Product_Mfd'] = convertDate($a_data[$ci_mfd], "eng", "iso", "-");
								} else {
									$detail['Product_Mfd'] = $item->Product_Mfd;
								}								
								
								if ($a_data[$ci_exp] != "") {
									$detail['Product_Exp'] = convertDate($a_data[$ci_exp], "eng", "iso", "-");
								} else {
									$detail['Product_Exp'] = $item->Product_Exp;
								}
 
                                $detail['Receive_Date'] = $item->Receive_Date;
                                $detail['Unit_Id'] = $item->Unit_Id;

                                #add value of invoice_no if config open invoice by item
                                if($conf_inv):
                                    $detail['Invoice_Id'] = $item->Invoice_Id;
                                endif;

                                #add value of container_no if config open invoice by item
                                if($conf_cont):
                                    $detail['Cont_Id'] = $item->Cont_Id;
                                endif;

                                #add value of container_no if config open invoice by item
                                if($conf_pallet):
                                    $detail['Pallet_Id'] = $item->Pallet_Id;
                                endif;

                                /**
                                 * find product code in product table by product_id
                                 */
                                $product_code = $this->pending->getProductCodeByProductId($detail['Product_Id']);
                                if (!empty($product_code)):
                                    $product_code = $product_code[0]->Product_Code;
                                else:
                                    $check_not_err = FALSE;
                                    /*
                                     * LOG MSG 4
                                     */
                                    $return['critical'][]['message'] = "Not have this "._lang('product_code')." in Product table";
                                endif;

                                /**
                                 * find Suggest_Location_Id
                                 */
//                                $setSuggestLocation = $this->suggest_location->getSuggestLocationArray('suggestLocation', $product_code, $a_data[$ci_prod_status], '1');
//                                $detail['Suggest_Location_Id'] = (!empty($setSuggestLocation) ? $setSuggestLocation[0]['Location_Id'] : NULL);
                            }

                        else:
                            $check_not_err = FALSE;
                            /*
                             * LOG MSG 5
                             */
                            $return['critical'][]['message'] = "Not have data in Inbound table";
                        endif;


                        $detail['Order_Id'] = $order_id;
                        $detail['Product_Status'] = $a_data[$ci_prod_status];
                        $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                        $detail['Reserv_Qty'] = $a_data[$ci_confirm_qty];
//			$detail['Product_Mfd'] = ( $a_data[$ci_mfd] == "" ? $item->Product_Mfd : $a_data[$ci_mfd] );
//			$detail['Product_Exp'] = ( $a_data[$ci_exp] == "" ? $item->Product_Exp : $a_data[$ci_exp] );

                        //ADD BY POR 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                        if ($conf_price_per_unit) {
                            $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]);

                            // Add By Akkarapol, 24/01/2014, เพิ่มการตรวจสอบว่าถ้าค่าของ Price Per Unit ที่ได้มานั้น ไม่มีค่าอะไร ก็จะให้ค่าเป็น Null เพื่อให้สามารถเซฟเข้าฐานข้อมูลได้
                            if ($detail['Price_Per_Unit'] == '' or $detail['Price_Per_Unit'] == ' '):
                                $detail['Price_Per_Unit'] = NULL;
                            endif;
                            // END Add By Akkarapol, 24/01/2014, เพิ่มการตรวจสอบว่าถ้าค่าของ Price Per Unit ที่ได้มานั้น ไม่มีค่าอะไร ก็จะให้ค่าเป็น Null เพื่อให้สามารถเซฟเข้าฐานข้อมูลได้

                            $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                            $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]);
                        }
                        //END ADD

                        $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                        // $detail['Old_Location_Id'] = $a_data[$ci_actual_loc]; // Add By Akkarapol, 29/10/2013, Old_Location_Id ที่จะใช้ในขั้นตอนของการ Relocation มันต้องใช้เป็น Actual Location ที่มันถูกเก็บลง Inbound สิ จะมาใช้ Old_Location_Id ได้ยังไง ในเมื่อ เราต้องการรู้ว่า ปัจจุบันมันอยู่ที่ไหน ไม่ใช่ มันมาจากที่ไหน เชอะ!!!!!
                        $detail['Actual_Location_Id'] = $a_data[$ci_actual_loc];


                        //ADD For Issue 3334 : BY KIK 2014-02-17
                        if ($conf_pallet) {
                            if ($a_data[$ci_dp_type_pallet] == "") {
                                $detail['DP_Type_Pallet'] = NULL;
                            } else {
                                $detail['DP_Type_Pallet'] = $a_data[$ci_dp_type_pallet];
                            }
                        }
                        //END ADD

                        $order_detail[] = $detail;
                    }//end for product list
                    #save order detail and PD_Reserv_Qty in inbound table
                //    p($order_detail);exit();
                    if ($check_not_err):
                        if (!empty($order_detail)):
                            $result_order_detail = $this->relocate->addReLocationOrderDetail($order_detail);
                            if ($result_order_detail <= 0):
                                $check_not_err = FALSE;
                                /*
                                 * LOG MSG 6
                                 */

                                $return['critical'][]['message'] = "Can not add order detail.";
                            endif;

                            #update pd reserv into inbound
                            if ($check_not_err):
                                $result_PD_reserv_qty = $this->stock->reservPDReservQtyArray($order_detail);
                                if (!$result_PD_reserv_qty):
                                    $check_not_err = FALSE;
                                    /*
                                     * LOG MSG 7
                                     */
                                    $return['critical'][]['message'] = "Can not update QTY in Inbound table.";
                                endif;
                            endif;

                        endif; //end check not empty set data in $order detail

                    endif; //end check err before sava order detail

                endif; //end check empty $prod_list

            endif; //end check update order detail
            #Set Message Alert
            if ($check_not_err):

                $array_return['success'][]['message'] = "Open Job Change Status Complete";
                $json['return_val'] = $array_return;

                $this->transaction_db->transaction_end();

            else:

                $array_return['critical'][]['message'] = "Open Job Change Status Incomplete.";
                $json['return_val'] = $array_return;

                $this->transaction_db->transaction_rollback();

            endif; //end set message alert

            $json['status'] = "save";

        endif; //end check critical validate return

        echo json_encode($json);
    }

    #Action Confirm Process

    function confirmAction() {
// p($this->input->post()); exit;
        # Parameter Form
        $token = $this->input->post('token');
        $flow_id = $this->input->post("flow_id");
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Relocate');
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));
        $order_id = $flow_detail[0]->Order_Id;
        $prod_list = $this->input->post("prod_list");

        # Parameter Index Datatable
        $ci_confirm_qty = $this->input->post('ci_confirm_qty');
        $ci_inbound_id = $this->input->post('ci_inbound_id');
        $ci_item_id = $this->input->post('ci_item_id');

        # Check validate data
        $return = array();

        #Variable for check error
        $check_not_err = TRUE;

        // validate token
        $response = validate_token($token, $flow_id, $flow_info[0]->Present_State, $flow_info[0]->Process_Id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;


        if ($check_not_err):
            # Check Estimate balance
            $resultCompare = $this->balance->_chkPDreservBeforeApprove($ci_confirm_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Relocate_Detail');
            $return = array_merge_recursive($return, $resultCompare);
            if (!empty($resultCompare['critical'])):
                $check_not_err = FALSE;

            /**
             * 01.Set Alert Zone (set Error Code, Message, etc.)
             */
            endif;

        endif;

        #หากมี error ที่ไม่สามารถปล่อยผ่านได้ จะ return กลับไปยังหน้า view เพื่อบังคับให้แก้ไขข้อมูลดังกล่าว
        if (!empty($return['critical'])) :

            $json['status'] = "validation";
            $json['return_val'] = $return;

        else:

            # ================== Start save data =========================

            $this->transaction_db->transaction_start();

            $respond = $this->_updateProcess($this->input->post());

            if (!empty($respond['critical'])) :
                $check_not_err = FALSE;

                /**
                 * 1.Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update process";
                $return = array_merge_recursive($return, $respond);

            endif;
        endif;


        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):

            $set_return['message'] = "Confirm Change Status Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;

            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

        else:

            $array_return['critical'][]['message'] = "Confirm Change Status Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);

            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

        endif;

        echo json_encode($json);
    }

    function approveAction() {
       

        # Parameter Form
        $token = $this->input->post('token');
        $flow_id = $this->input->post("flow_id");
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Relocate');
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));
        $order_id = $this->input->post("order_id");
        $prod_list = $this->input->post("prod_list");

        # Parameter Index Datatable
        $ci_reserv_qty = $this->input->post("ci_confirm_qty");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_item_id = $this->input->post("ci_item_id");

        # Check validate data
        $return = array();

        #Variable for check error
        $check_not_err = TRUE;

        // validate token
        $response = validate_token($token, $flow_id, $flow_info[0]->Present_State, $flow_info[0]->Process_Id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;

        # ================== Start save data =========================

        $this->transaction_db->transaction_start();

        # Check Estimate balance
        if ($check_not_err):
            $resultCompare = $this->balance->_chkPDreservBeforeApprove($ci_reserv_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Relocate_Detail');
            $return = array_merge_recursive($return, $resultCompare);
            if (!empty($resultCompare['critical'])):
                $check_not_err = FALSE;

            /**
             * 01.Set Alert Zone (set Error Code, Message, etc.)
             */
            endif;

        endif;

        # Update Process
        if ($check_not_err):

            $respond = $this->_updateProcess($this->input->post());

            if (!empty($respond['critical'])) :
                $check_not_err = FALSE;

                /**
                 * 1.Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update process";
                $return = array_merge_recursive($return, $respond);
            endif;

        endif;

        # Update QTY in INBOUND table
        if ($check_not_err) :

            $result_updateInbound_query = $this->balance->updatePDReservQty($ci_reserv_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list);
            if (!$result_updateInbound_query):
                $check_not_err = FALSE;
                /*
                 * LOG MSG 2
                 */
                $return['critical'][]['message'] = "Can not update QTY in Inbound table.";
            endif;

        endif;

         #add  move product location (add new inbound)
        if ($check_not_err):
            // p($order_id);exit;
            $result_moveProductLocation = $this->stock_lib->moveProductLocation($order_id);
       
//        $this->transaction_db->transaction_rollback();exit();
            if (!$result_moveProductLocation):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not move product location.";
            /*
             * LOG MSG 5
             */
            endif;
        endif;

        #check if for return json and set transaction
        if ($check_not_err):

            $set_return['message'] = "Approve Change Status Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;

            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

        else:

            $array_return['critical'][]['message'] = "Approve Change Status Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);

            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

        endif;

        echo json_encode($json);
    }

    function _updateProcess() {
        
        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        $conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;

        #====================== Start Get Parameter ============================
        # Parameter Session and config
        $user_id = $this->session->userdata('user_id');
        $owner_id = $this->config->item('owner_id');
        $renter_id = $this->config->item('renter_id');

        # Parameter Form
        $flow_id = $this->input->post("flow_id");
        $order_id = $this->input->post("order_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $document_no = $this->input->post("document_no");
        $est_action_date = $this->input->post("est_action_date");
        $action_date = $this->input->post("action_date");
        $remark = $this->input->post("remark");
        $assigned_id = $this->input->post("assigned_id");
        $is_urgent = $this->input->post("is_urgent");
        $document_ref = $this->input->post("document_ref");

        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Parameter Order Detail
        $prod_list = $this->input->post("prod_list");
        $prod_del_list = $this->input->post("prod_del_list");

        # Parameter Index Datatable
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_lot = $this->input->post("ci_lot");
        $ci_serial = $this->input->post("ci_serial");
        $ci_mfd = $this->input->post("ci_mfd");
        $ci_exp = $this->input->post("ci_exp");
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_confirm_qty = $this->input->post("ci_confirm_qty");
        $ci_remark = $this->input->post("ci_remark");
        $ci_prod_id = $this->input->post("ci_prod_id");
        $ci_prod_status = $this->input->post("ci_prod_status");
        $ci_unit_id = $this->input->post("ci_unit_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");
        $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        $ci_actual_loc = $this->input->post("ci_actual_loc");
        $ci_old_loc = $this->input->post("ci_old_loc");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        

        # Parameter price per unit
        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }


        #------------------------ End Get Parameter ----------------------------
        #========================== Start Save Data ============================

        if (empty($flow_id) || $flow_id == "" || empty($prod_list) || $prod_list == ""):
            $check_not_err = FALSE;
            $return['critical'][]['message'] = "Not have flow_id or product list.";
        /*
         * LOG MSG 1
         */
        endif;

        # Update Order and Order Detail
        # Get workflow
        if ($check_not_err):
            $data['Document_No'] = $document_no;
            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021

            if ($action_id == "" || empty($action_id)):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update workflow.";
            /*
             * LOG MSG 2
             */
            endif;

        endif;

        #update Order Table
        if ($check_not_err):

            if ($next_state == 3 || $next_state == -2):

                $order = array(
                    'Modified_By' => $user_id
                    , 'Modified_Date' => date("Y-m-d H:i:s")
                    , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                    , 'Assigned_Id' => $assigned_id
                    , 'Actual_Action_Date' => date("Y-m-d H:i:s")
                    , 'Is_urgent' => $is_urgent
                    , 'Custom_Doc_Ref' => $document_ref //ADD BY POR 2014-11-17 for customs report
                );

            else:
                $order = array(
                    'Modified_By' => $user_id
                    , 'Modified_Date' => date("Y-m-d H:i:s")
                    , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                    , 'Assigned_Id' => $assigned_id
                    , 'Is_urgent' => $is_urgent
                    , 'Custom_Doc_Ref' => $document_ref //ADD BY POR 2014-11-17 for customs report
                );
            endif;

            $where['Flow_Id'] = $flow_id;

            #update order
            $result_order_query = $this->prod_status->updateOrder($order, $where);
            
            if (!$result_order_query):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update Order.";
            /*
             * LOG MSG 3
             */
            endif;

        endif;


        /**
         * update Order Detail Table
         */
        if ($check_not_err):

            $order_detail = array();

            if (!empty($prod_list)) {
                
                foreach ($prod_list as $rows) {
                   
                    

                    unset($where);
                    $a_data = explode(SEPARATOR, $rows);
                    $is_new = $a_data[$ci_item_id];
                    
                    if ("new" != $is_new) {
                        
                        $detail = array();
                        $detail['Product_Status'] = $a_data[$ci_prod_status];
                        $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                        $detail['Reserv_Qty'] = str_replace(",", "", $a_data[$ci_confirm_qty]); //+++++Edit BY POR 2013-12-03 ตัด comma ออกเพื่อให้สามารถบันทึกได้

                        $detail['Confirm_Qty'] = str_replace(",", "", $a_data[$ci_confirm_qty]); //ADD BY POR 2014-06-19 เพิ่มให้ insert ใน confirm ด้วย
			//$detail['Product_Mfd'] = $a_data[$ci_mfd];
			//$detail['Product_Exp'] = $a_data[$ci_exp];

                    // Update Actual_Location_Id 
                        $detail['Actual_Location_Id'] = $a_data[$ci_actual_loc]; 
                    //   END

                        //ADD BY POR 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                        if ($this->settings['price_per_unit'] == TRUE) {
                            $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]);
                            $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                            $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]);
                        }
                        //END ADD

                        $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                        $where['Item_Id'] = $a_data[$ci_item_id];

                        $product_code = $this->relocate->getProductCodeByItemId($a_data[$ci_item_id]);
                        $product_code = $product_code[0]->Product_Code;
//                        $setSuggestLocation = $this->suggest_location->getSuggestLocationArray('suggestLocation', $product_code, $a_data[$ci_prod_status], '1', NULL, $a_data[$ci_prod_sub_status], str_replace(",", "", $a_data[$ci_confirm_qty])); // Add By Akkarapol, 04/11/2013, ใส่ค่าตามที่ sp_PA_suggestLocation ต้องการ ไม่งั้นโค๊ดเก่าที่เคยใช้ได้มันจะใช้งานไม่ได้
//                        $detail['Suggest_Location_Id'] = (!empty($setSuggestLocation) ? $setSuggestLocation[0]['Location_Id'] : NULL);

                        // Add By Akkarapol, 03/10/2013, เพิ่มการทำงานในส่วนของการตรวจสอบค่า PD_Reserv_Qty เก่าและใหม่ ว่าถ้าค่าไม่เท่ากัน จะให้ไปแก้ไขค่า PD_Reserv_Qty โดยการนำ ค่าเก่าไปลบออกก่อน และค่อยบวกค่าใหม่เข้าไป
                        $oldReservQty = $this->stock->getOldReservQtyRelocateDetailByItemId($is_new);
                        if ($oldReservQty['Reserv_Qty'] != str_replace(",", "", $a_data[$ci_confirm_qty])) {

                            if ($check_not_err):
                                $result_Del_PDrervqty = $this->stock->reservPDReservQty($a_data[$ci_inbound_id], $oldReservQty['Reserv_Qty'], "-");
                                if ($result_Del_PDrervqty < 1):
                                    $check_not_err = FALSE;
                                    $return['critical'][]['message'] = "Can not update quantity balance.";
                                /*
                                 * LOG MSG 4
                                 */
                                endif;
                            endif;

                            if ($check_not_err):
                                $result_Pos_PDrervqty = $this->stock->reservPDReservQty($a_data[$ci_inbound_id], str_replace(",", "", $a_data[$ci_confirm_qty]), "+"); //(4)
                                if ($result_Pos_PDrervqty < 1):
                                    $check_not_err = FALSE;
                                    $return['critical'][]['message'] = "Can not update quantity balance.";
                                /*
                                 * LOG MSG 5
                                 */
                                endif;
                            endif;
                        }

                        unset($oldReservQty);
                        // END Add By Akkarapol, 03/10/2013, เพิ่มการทำงานในส่วนของการตรวจสอบค่า PD_Reserv_Qty เก่าและใหม่ ว่าถ้าค่าไม่เท่ากัน จะให้ไปแก้ไขค่า PD_Reserv_Qty โดยการนำ ค่าเก่าไปลบออกก่อน และค่อยบวกค่าใหม่เข้าไป



                        if ($check_not_err):
                            // p($detail);exit;

                            $result_orderDetail_query = $this->prod_status->updateOrderDetail($detail, $where);

                            if ($result_orderDetail_query < 1):
                                $check_not_err = FALSE;
                                $return['critical'][]['message'] = "Can not update Order detail.";
                            /*
                             * LOG MSG 6
                             */
                            endif;

                        endif;
                    } else {
                        #Add Order Detail for new Item

                        $inbound_id = $a_data[$ci_inbound_id];
                        
                        //+++++ADD BY POR 2013-11-13 แก้ไขให้เรียกใช้ getStockDetailId รูปแบบใหม่
                        $column = array("*");
                        $where['Inbound_Id'] = $inbound_id;
                        $item_detail = $this->stock->getStockDetailId($column, $where)->result();
                        //+++++END ADD

                        if (count($item_detail) > 0) {
                            foreach ($item_detail as $item) {
                                $detail['Document_No'] = $item->Document_No;
                                $detail['Doc_Refer_Int'] = $item->Doc_Refer_Int;
                                $detail['Doc_Refer_Ext'] = $item->Doc_Refer_Ext;
                                $detail['Doc_Refer_Inv'] = $item->Doc_Refer_Inv;
                                $detail['Doc_Refer_CE'] = $item->Doc_Refer_CE;
                                $detail['Doc_Refer_BL'] = $item->Doc_Refer_BL;
                                $detail['Doc_Refer_AWB'] = $item->Doc_Refer_AWB;
                                $detail['Inbound_Item_Id'] = $item->Inbound_Id;
                                $detail['Product_Id'] = $item->Product_Id;
                                $detail['Product_Code'] = $item->Product_Code;
                                $detail['Product_License'] = $item->Product_License;
                                $detail['Product_Lot'] = $item->Product_Lot;
                                $detail['Product_Serial'] = $item->Product_Serial;
                                //$detail['Product_Mfd'] = $item->Product_Mfd;
                                //$detail['Product_Exp'] = $item->Product_Exp;
                                $detail['Receive_Date'] = $item->Receive_Date;
                                $detail['Unit_Id'] = $item->Unit_Id;

                                #add value of invoice_no if config open invoice by item
                                if($conf_inv):
                                    $detail['Invoice_Id'] = $item->Invoice_Id;
                                endif;

                                #add value of container_no if config open invoice by item
                                if($conf_cont):
                                    $detail['Cont_Id'] = $item->Cont_Id;
                                endif;

                                #add value of container_no if config open invoice by item
                                if($conf_pallet):
                                    $detail['Pallet_Id'] = $item->Pallet_Id;
                                endif;


                                $product_code = $this->prod_status->getProductCodeByProductId($detail['Product_Id']);
                                $product_code = $product_code[0]->Product_Code;
                                $setSuggestLocation = $this->suggest_location->getSuggestLocationArray('suggestLocation', $product_code, $a_data[$ci_prod_status], '1');
                                $detail['Suggest_Location_Id'] = (!empty($setSuggestLocation) ? $setSuggestLocation[0]['Location_Id'] : NULL);
                            }
                        }

                        $detail['Order_Id'] = $order_id;
                        $detail['Product_Status'] = $a_data[$ci_prod_status];
                        $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                        $detail['Reserv_Qty'] = str_replace(",", "", $a_data[$ci_confirm_qty]);
                        $detail['Old_Location_Id'] = $a_data[$ci_actual_loc]; // Add By Akkarapol, 29/10/2013, Old_Location_Id ที่จะใช้ในขั้นตอนของการ Relocation มันต้องใช้เป็น Actual Location ที่มันถูกเก็บลง Inbound สิ จะมาใช้ Old_Location_Id ได้ยังไง ในเมื่อ เราต้องการรู้ว่า ปัจจุบันมันอยู่ที่ไหน ไม่ใช่ มันมาจากที่ไหน เชอะ!!!!!
//			$detail['Product_Mfd'] = $a_data[$ci_mfd];
//			$detail['Product_Exp'] = $a_data[$ci_exp];
                        //
                        //ADD BY POR 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                        if ($this->settings['price_per_unit'] == TRUE) {
                            $detail['Price_Per_Unit'] = (float) str_replace(",", "", $a_data[$ci_price_per_unit]);
                            $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                            $detail['All_Price'] = (float) str_replace(",", "", $a_data[$ci_all_price]);
                        }
                        //END ADD
                        // p($detail);exit;
                        $order_detail[] = $detail;
                        
                    }
                }

                #add new Order Detail
                if ($check_not_err):
                    if (!empty($order_detail)):
                        #add order detail
                        $result_order_detail = $this->relocate->addReLocationOrderDetail($order_detail);
                        // p($result_order_detail);exit;
                        if ($result_order_detail <= 0):
                            $check_not_err = FALSE;
                            $return['critical'][]['message'] = "Can not add new order detail.";
                        /*
                         * LOG MSG 7
                         */
                        endif;

                        #update pd reserv into inbound
                        $result_PD_reserv_qty = $this->stock->reservPDReservQtyArray($order_detail);
                        if (!$result_PD_reserv_qty):
                            $check_not_err = FALSE;
                            $return['critical'][]['message'] = "Can not update quantity balance.";
                        /*
                         * LOG MSG 8
                         */
                        endif;

                        unset($result_PD_reserv_qty);

                    endif; //end add new order detail
                endif; //end check err before add new order detail
            }//if (!empty($prod_list))

        endif; //end update Order Detail Table


        /**
         * Delete Item in Order Detail
         */
        if ($check_not_err):

            if (is_array($prod_del_list) && !empty($prod_del_list)) {
                unset($rows);
                $item_delete = array();

                foreach ($prod_del_list as $rows) {
                    // p($rows);exit;

                    $a_data = explode(SEPARATOR, $rows); // Edit By Akkarapol, 21/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
                    $item_delete[] = $a_data[$ci_item_id];  /* Item_Id for Delete in STK_T_Order_Detail  */

                    #Update PD_Reserv_Qty in Inbound Table
                    if (!empty($a_data[$ci_inbound_id])) {

                        #update pd reserv into inbound
                        $result_PD_reserv_qty = $this->stock->reservPDReservQty($a_data[$ci_inbound_id], $a_data[$ci_reserv_qty], "-");
                        if (!$result_PD_reserv_qty):
                            $check_not_err = FALSE;
                            $return['critical'][]['message'] = "Can not update quantity balance.";
                        /*
                         * LOG MSG 9
                         */
                        endif;
                    }// end update PD_Reserv_Qty in Inbound Table
                }//foreach ($prod_del_list as $rows) {
                #query Delete Item in Order Detail
                if ($check_not_err && !empty($item_delete)):

                    $result_Del_item = $this->prod_status->removeOrderDetail($item_delete);
                    if (!$result_Del_item):
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Can not remove order detail.";
                    /*
                     * LOG MSG 10
                     */
                    endif;

                endif; // end check err before delete item in order detail table
            }// if (is_array($prod_del_list) && !empty($prod_del_list)) {

        endif; // end delete Item in Order Detail

        return $return;
    }

    function getSuggestLocRule($used_rule = 1) {
        $receive_type = $this->input->post("receive_type");
        $product_code = $this->input->post("product_code");
        $product_status = strtoupper($this->input->post("product_status"));
        $prod_mfd = $this->input->post("prod_mfd");
        $prod_exp = $this->input->post("prod_exp");
        $lot = $this->input->post("lot");
        $serial = $this->input->post("serial");

        switch (strtoupper($receive_type)) {
            case 'NORMAL' : $type = "suggestLocation";
                break;
            case 'RETURN' : $type = "returnLocation";
                break;
            default : $type = "suggestLocation";
                break;
        }
        $product_status = "NORMAL";
        $result = $this->suggest_location->getSuggestLocationArray($type, $product_code, $product_status, $used_rule);
        $location_list = array("No Specified");
        if (count($result) > 0) {
            foreach ($result as $rows) {
                $location_list[$rows['Location_Id']] = $rows['Location_Code'];
            }
        }
        echo json_encode($location_list);
    }

    function getSuggestLoc($used_rule = 0) {
        $receive_type = $this->input->post("receive_type");
        $product_code = $this->input->post("product_code");
        $product_status = strtoupper($this->input->post("product_status"));
        $prod_mfd = $this->input->post("prod_mfd");
        $prod_exp = $this->input->post("prod_exp");
        $lot = $this->input->post("lot");
        $serial = $this->input->post("serial");

        switch (strtoupper($receive_type)) {
            case 'NORMAL' : $type = "suggestLocation";
                break;
            case 'RETURN' : $type = "returnLocation";
                break;
            default : $type = "suggestLocation";
                break;
        }
        $product_status = "NORMAL";
        $result = $this->suggest_location->getSuggestLocationArray($type, $product_code, $product_status, $used_rule);
        $location_list = array("No Specified");
        if (count($result) > 0) {
            foreach ($result as $rows) {
                $location_list[$rows['Location_Id']] = $rows['Location_Code'];
            }
        }
        echo json_encode($location_list);
    }

#=====================================================
#
#=====================================================

    function relocateConfirm() {

        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;

        #====================== Start Get Parameter ============================
        # Parameter Index Datatable
        $token = $this->input->post('token');
        $flow_id = $this->input->post("flow_id");
        $order_id = $this->input->post("order_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->session->userdata('user_id');
        $document_no = $this->input->post("document_no");
        $owner_id = $this->input->post("owner_id");
        $renter_id = $this->input->post("renter_id");
        $remark = $this->input->post("remark");
        $action_date = $this->input->post("action_date");
        $assigned_id = $this->input->post("assigned_id");
        $is_urgent = $this->input->post("is_urgent");

        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }


        #Variable for check error
        $check_not_err = TRUE;

        // validate token
        $response = validate_token($token, $flow_id, $present_state, $process_id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;


        # Parameter Order Detail
        $prod_list = $this->input->post("prod_list");

        # Parameter Index Datatable
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_lot = $this->input->post("ci_lot");
        $ci_serial = $this->input->post("ci_serial");
        $ci_mfd = $this->input->post("ci_mfd");
        $ci_exp = $this->input->post("ci_exp");
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_confirm_qty = $this->input->post("ci_confirm_qty");
        $ci_remark = $this->input->post("ci_remark");
        $ci_prod_id = $this->input->post("ci_prod_id");
        $ci_prod_status = $this->input->post("ci_prod_status");
        $ci_unit_id = $this->input->post("ci_unit_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");
        $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        $ci_actual_loc = $this->input->post("ci_actual_loc");

        # Parameter Index Datatable for PRICE PER UNIT
        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }

        #------------------------ End Get Parameter ----------------------------
        #========================== Start Save Data ============================

        $this->transaction_db->transaction_start();

        if ($check_not_err):

            if (empty($flow_id) || $flow_id == "" || empty($prod_list) || $prod_list == ""):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Not have flow_id or product list.";
            /*
             * LOG MSG 1
             */
            endif;

        endif;


        # Update Workflow
        if ($check_not_err):
            $data['Document_No'] = $document_no;
            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
            if ($action_id == "" || empty($action_id)):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update workflow.";
            /*
             * LOG MSG 2
             */
            endif;
        endif;


        #update Order Table
        if ($check_not_err):

            $order = array(
                'Modified_By' => $user_id
                , 'Modified_Date' => date("Y-m-d H:i:s")
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Actual_Action_Date' => date("Y-m-d H:i:s")
                , 'Assigned_Id' => $assigned_id
                , 'Is_urgent' => $is_urgent
            );

            $where['Flow_Id'] = $flow_id;

            #update order
            $result_order_query = $this->pending->updatePendingOrder($order, $where);

            if ($result_order_query < 1):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update Order.";
            /*
             * LOG MSG 3
             */
            endif;

        endif;


        #update Order Detail Table
        if ($check_not_err):

            $order_detail = array();


            if (!empty($prod_list)) {
                foreach ($prod_list as $rows) {
                    unset($where);
                    $a_data = explode(SEPARATOR, $rows);
                    $detail = array();

                    $detail['Suggest_Location_Id'] = (empty($a_data[$ci_suggest_loc]) || $a_data[$ci_suggest_loc] == "" || $a_data[$ci_suggest_loc] == null)?'':$a_data[$ci_suggest_loc];  //EDIT BY POR 2014-05-28 แก้ไขเพิ่มเติมไม่ให้ส่งค่า null ไป เนื่องจากหากไป update CI จะมองเห็นเป็น 'null'
                    $detail['Actual_Location_Id'] = (empty($a_data[$ci_actual_loc]) || $a_data[$ci_actual_loc] == "" || $a_data[$ci_actual_loc] == null) ? '' : $a_data[$ci_actual_loc];
                    $detail['Confirm_Qty'] = str_replace(",", "", $a_data[$ci_confirm_qty]); //+++++Edit BY POR 2013-12-03 ตัด comma ออกเพื่อให้สามารถบันทึกได้
                    $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                    $where['Item_Id'] = $a_data[$ci_item_id];
                    $where['Product_Id'] = $a_data[$ci_prod_id];

                    //ADD BY POR 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                    if ($this->settings['price_per_unit'] == TRUE) {
                        $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]);
                        $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                        $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]);
                    }
                    //END ADD
                    $result_orderDetail_query = $this->pending->updatePendingDetail($detail, $where);
                    //$result_orderDetail_query = 0;
                    if ($result_orderDetail_query < 1):
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Can not update Order detail.";
                    /*
                     * LOG MSG 4
                     */
                    endif;
                }
            }

        endif;

        #check if for return json and set transaction
        if ($check_not_err):

            $set_return['message'] = "Confirm Putaway in Change Product Status Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;

            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

        else:

            $array_return['critical'][]['message'] = "Confirm Putaway in Change Product Status Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);

            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

        endif;

        echo json_encode($json);
    }

    function relocateApprove() {

        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;

        #====================== Start Get Parameter ============================
        # Parameter Form
        $token = $this->input->post('token');
        $flow_id = $this->input->post("flow_id");
        $order_id = $this->input->post("order_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");

        #Variable for check error
        // validate token
        $response = validate_token($token, $flow_id, $present_state, $process_id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;

        # Parameter of session data
        $user_id = $this->session->userdata('user_id');

        # Parameter of document number
        $document_no = $this->input->post("document_no");
        $doc_refer_int = $this->input->post("doc_refer_int");
        $doc_refer_ext = $this->input->post("doc_refer_ext");
        $doc_refer_inv = $this->input->post("doc_refer_inv");
        $doc_refer_ce = $this->input->post("doc_refer_ce");
        $doc_refer_bl = $this->input->post("doc_refer_bl");

        # Parameter Order Document
        $owner_id = $this->input->post("owner_id");
        $renter_id = $this->input->post("renter_id");
        $shipper_id = $this->input->post("shipper_id");
        $consignee_id = $this->input->post("consignee_id");
        $receive_type = $this->input->post("receive_type");
        $remark = $this->input->post("remark");
        $receive_date = $this->input->post("receive_date");
        $assigned_id = $this->input->post("assigned_id");
        $is_urgent = $this->input->post("is_urgent");

        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Parameter Order Detail
        $prod_list = $this->input->post("prod_list");

        # Parameter Index Datatable
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_lot = $this->input->post("ci_lot");
        $ci_serial = $this->input->post("ci_serial");
        $ci_mfd = $this->input->post("ci_mfd");
        $ci_exp = $this->input->post("ci_exp");
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_confirm_qty = $this->input->post("ci_confirm_qty");
        $ci_remark = $this->input->post("ci_remark");
        $ci_prod_id = $this->input->post("ci_prod_id");
        $ci_prod_status = $this->input->post("ci_prod_status");
        $ci_unit_id = $this->input->post("ci_unit_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");
        $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        $ci_actual_loc = $this->input->post("ci_actual_loc");
        $ci_old_loc = $this->input->post("ci_old_loc");
        $ci_inbound_id = $this->input->post("ci_inbound_id");

        # Parameter Price per uint
        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }


           # ================== NO_DISPATCH_AREA  =========================
        //    p($order_id); 
        //    p('----------'); 
        //    p($flow_id); 
            $get_order = $this->prod_status->getlocation_order($order_id);
            foreach ($get_order as $key => $value) {
                $Actual_Location_Id = $value['Actual_Location_Id'];
                $Suggest_Location_Id = $value['Suggest_Location_Id'];
              }
            //   $temp1 = 6717;
            // $Actual_Location_Id  ='6717';
            // $Suggest_Location_Id ='2';
            
            $getloc = $this->prod_status->location_id_donotmove(); 
            foreach ($getloc as $key => $value) {
            $temp[$value['Location_Id']] = $value['Location_Id'];
          }
               $response = array_key_exists($Actual_Location_Id, $temp);
               if($response == 1){
               $check_not_err = FALSE;
               $return['critical'][]['message'] = "ACTUAL_LOCATION NO_DISPATCH_AREA! ";
               }else{
               $check_not_err =TRUE;  
               }
            //   echo json_encode($check_not_err);
              $response = array_key_exists($Suggest_Location_Id, $temp);
               if($response == 1){
               $check_not_err = FALSE;
               $return['critical'][]['message'] = "SUGGEST_LOCATION NO_DISPATCH_AREA! ";
               }else{
               $check_not_err =TRUE;  
               }
            //   echo json_encode($check_not_err);
                 # ================== NO_DISPATCH_AREA  =========================



# ================== Start save data =========================

        $this->transaction_db->transaction_start();

        if ($check_not_err):
            if (empty($flow_id) || $flow_id == ""):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Not have flow_id or product list.";
            /*
             * LOG MSG 1
             */
            endif;
        endif;


        # Update Workflow
        if ($check_not_err):
            $data['Document_No'] = $document_no;
            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
            if ($action_id == "" || empty($action_id)):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update workflow.";
            /*
             * LOG MSG 2
             */
            endif;
        endif;

        #update Order Table
        if ($check_not_err):
            $order = array(
                'Modified_By' => $user_id
                , 'Modified_Date' => date("Y-m-d H:i:s")
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Assigned_Id' => $assigned_id
                , 'Is_urgent' => $is_urgent
            );

            $where['Flow_Id'] = $flow_id;

            $result_order_query = $this->pending->updatePendingOrder($order, $where);

            if (!$result_order_query):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update Order.";
            /*
             * LOG MSG 3
             */
            endif;

        endif;

        #update Order Detail Table
        if ($check_not_err):

            if (!empty($prod_list)) {

                foreach ($prod_list as $rows) {
                    unset($where);
                    $a_data = explode(SEPARATOR, $rows);
                    $detail = array();
                    $detail['Suggest_Location_Id'] = $a_data[$ci_suggest_loc];
                    $detail['Actual_Location_Id'] = $a_data[$ci_actual_loc];
                    $detail['Confirm_Qty'] = str_replace(",", "", $a_data[$ci_confirm_qty]);
                    $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                    $detail['Putaway_By'] = $assigned_id;
                    $detail['Putaway_Date'] = date("Y-m-d H:i:s");

                    //ADD BY POR 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                    if ($this->settings['price_per_unit'] == TRUE) {
                        $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]);
                        $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                        $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]);
                    }
                    //END ADD

                    $where['Item_Id'] = $a_data[$ci_item_id];
                    $where['Product_Id'] = $a_data[$ci_prod_id];

                    if ($check_not_err):

                        $result_orderDetail_query = $this->pending->updatePendingDetail($detail, $where);

                        if ($result_orderDetail_query < 1):
                            $check_not_err = FALSE;
                            $return['critical'][]['message'] = "Can not update Order detail.";
                        /*
                         * LOG MSG 4
                         */
                        endif;

                    endif;
                }//foreach ($prod_list as $rows)
            }//if (!empty($prod_list))
        endif;


        #update  move product location (update new inbound)
        if ($check_not_err):
            $result_moveProductLocation = $this->stock_lib->update_inbound_ch_prod_status($order_id);
//        p($result_moveProductLocation);$this->transaction_db->transaction_rollback();exit();
            if (!$result_moveProductLocation):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update location.";
            /*
             * LOG MSG 5
             */
            endif;
        endif;

        #add  move product location (add new inbound)
        if ($check_not_err):
            //ADD For Issue 3334 : BY KIK 2014-02-13
            if ($this->config->item('build_pallet') == TRUE) {
                $result_pallet_DPpartail = $this->pallet->insert_pallet_detail_relocatPT($order_id);
                $result_pallet_DPfull = $this->_update_pallet_DPfull($order_id);
                if (!empty($result_pallet_DPfull['critical'])) :
                    $check_not_err = FALSE;

                    /**
                     * 6.Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not update pallet data.";
                    $return = array_merge_recursive($return, $result_pallet_DPfull);
                endif;
            }
        //END ADD

        endif;

        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):

            $set_return['message'] = "Approve Putaway in Change Product Status Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

        else:

            $array_return['critical'][]['message'] = "Approve Putaway in Change Product Status Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);

            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

        endif;

        echo json_encode($json);
    }

    public function _update_pallet_DPfull($order_id = NULL) {

        $return = array();
        $check_not_err = TRUE;

        #get item for update new inbound on pallet detail table (dispatch type full)
        $item_update_inbounds = $this->pallet->get_item_update_inbound($order_id);

        if (!empty($item_update_inbounds)) {

            foreach ($item_update_inbounds as $item_update_inbound) {

                #set data for update new inbound_id in pallet detail table
                $colunm_pallet_detail['Inbound_Id'] = $item_update_inbound->New_Inbound_Id;
                $where_pallet_detail['Inbound_Id'] = $item_update_inbound->Inbound_Item_Id;
                $where_pallet_detail['Pallet_Id'] = $item_update_inbound->Pallet_Id;

                #update inbound_id in pallet detail table
                $result_update_inbound = $this->pallet->update_pallet_detail_colunm($colunm_pallet_detail, $where_pallet_detail);
                if (!$result_update_inbound):
                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 1
                     */
                    $return['critical'][]['message'] = "Can not update pallet detail";
                endif;
            }
        }

        #get pallet data in relocate detail
        $result_db_fulls = $this->pallet->get_pallet_in_orderDetail($order_id, 'STK_T_Relocate_Detail');

        #check have data
        if (!empty($result_db_fulls)):

            foreach ($result_db_fulls as $result_db_full):

                #set data for update pallet location in pallet table
                $colunm_pallet['Suggest_Location_Id'] = $result_db_full->Suggest_Location_Id;
                $colunm_pallet['Actual_Location_Id'] = $result_db_full->Actual_Location_Id;
                $where_pallet['Pallet_Id'] = $result_db_full->Pallet_Id;

                #update pallet all location
                $update_pallet = $this->pallet->update_pallet_colunm($colunm_pallet, $where_pallet);

                if (!$update_pallet):
                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 2
                     */
                    $return['critical'][]['message'] = "Can not update pallet";
                endif;

                #==========================================================================
                #set data for insert pallet history location
                $colunm_pall_his_loc['Pallet_Id'] = $result_db_full->Pallet_Id;
                $colunm_pall_his_loc['Old_Location_Id'] = $result_db_full->Old_Location_Id;
                $colunm_pall_his_loc['Suggest_Location_Id'] = $result_db_full->Suggest_Location_Id;
                $colunm_pall_his_loc['Actual_Location_Id'] = $result_db_full->Actual_Location_Id;
                $colunm_pall_his_loc['Create_Date'] = date("Y-m-d H:i:s");
                $colunm_pall_his_loc['Create_By'] = $this->session->userdata('user_id');

                #insert pallet history location detail
                $update_his_loc = $this->pallet->insert_pallet_history_location($colunm_pall_his_loc);
                if (!$update_his_loc):
                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 3
                     */
                    $return['critical'][]['message'] = "Can not update pallet history location";
                endif;

            endforeach;

        endif;

        return $return;
    }

    function showProduct() {
        $inbound_id = $this->input->post("post_val");

        #add for ISSUE 3334 : by kik : 20140220
        $dp_type_pallet = $this->input->post("dp_type_pallet_val");
        if ($dp_type_pallet == "NULL"):
            $dp_type_pallet = NULL;
        endif;
        #end add for ISSUE 3334 : by kik : 20140220

        $this->load->model("stock_model", "stock");
        $query = $this->stock->getProductInStockArray($inbound_id);
        $product_list = $query->result_array();
        // p($product_list);exit();
        $new_list = array();
        foreach ($product_list as $rows) {
            $rows['Prod_Status'] = $rows['Status_Code'];
            $rows['Prod_Status_Value'] = $rows['Status_Value'];
            $rows['Prod_Sub_Status'] = $rows['Sub_Status_Code'];
            $rows['Prod_Sub_Status_Value'] = $rows['Sub_Status_Value'];
            $rows['Est_Balance_Qty'] = set_number_format($rows['Est_Balance_Qty']);
            $rows['DP_Type_Pallet'] = $dp_type_pallet; //add for ISSUE 3334 : by kik : 20140217
            $new_list[] = thai_json_encode($rows);
        }
       
        $json['product'] = $new_list;
        $json['status'] = "1";
        $json['error_msg'] = "";
        echo json_encode($json);
    }

    function rejectAction() {
        #Variable for check error
        $check_not_err = TRUE;

//        p($this->input->post());exit();
        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", "wf");

        // Validate Concurrent
        #Retrive Data from Table
        $flow_id = $this->input->post("flow_id");
        $flow_detail = $this->prod_status->getChangeStatusFlow($flow_id);


        #add condition check data from list page or form page
        if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {

            #if data send from list page When find data in database
            if ($this->input->post('id') != "") {

                $column_workflow_ = 'Document_No,Process_Id,Present_State';
                $where_workflow_['Flow_Id'] = $this->input->post('id');
                $workflow_query = $this->flow->getWorkflowTable($column_workflow_, $where_workflow_);
                $process_id = $workflow_query[0]->Process_Id;
                $flow_id = $this->input->post('id');
                $present_state = $workflow_query[0]->Present_State;
                $action_type = 'Reject';
                $next_state = -1;
                $data['Document_No'] = $workflow_query[0]->Document_No;

                $column_order = 'Order_Id';
                $where_order['Flow_Id'] = $this->input->post('id');
                $query_order = $this->stock->getRelocateTable($column_order, $where_order);
                $order_id = $query_order[0]->Order_Id;
            }
            #if data send from form page When get data in post()
        } else {
            $process_id = $this->input->post("process_id");
            $flow_id = $this->input->post("flow_id");
            $present_state = $this->input->post("present_state");
            $action_type = $this->input->post("action_type");
            $next_state = $this->input->post("next_state");

            $flow_detail = $this->prod_status->getChangeStatusFlow($flow_id);
            $order_id = $flow_detail[0]->Order_Id;
            $data['Document_No'] = $flow_detail[0]->Document_No;
        }

        // Validate Concurrent
        #Retrive Data from Table
        $token = $this->input->post('token');

        // validate token
        $response = validate_token($token, $flow_id, $present_state, $process_id);
        // End

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = false;

        endif;

        #----------------------------------------------------

        if ($check_not_err):
            #Check value not empty in variable
            if (empty($flow_id) || $flow_id == ""):
                $check_not_err = FALSE;

                /*
                 * LOG MSG 1
                 */
                $return['critical'][]['message'] = "Not have flow Id.";
            endif;
        endif;



        #=================== Start Update Data ===================================

        $this->transaction_db->transaction_start();

        #Update workflow and insert data in to STK_T_Action
        if ($check_not_err):

            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
            if (empty($action_id) || $action_id == ""):
                $check_not_err = FALSE;
                /*
                 * LOG MSG 2
                 */
                $return['critical'][]['message'] = "Can not update work flow.";
            endif;

        endif;


        if ($check_not_err):
            #return qty to PD_reserv in inbound table
            $order_detail = $this->stock->getRelocateDetailByOrderId($order_id);

            #update inbound qty in Inbound table
            if (!empty($order_detail)):
                $result_inboundQty_query = $this->stock->reservPDReservQtyArray($order_detail, "-");
                if (!$result_inboundQty_query):
                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 3
                     */
                    $return['critical'][]['message'] = "Can not update QTY in Inbound table.";
                endif;
            endif;

        endif;


        if ($check_not_err):

            $this->transaction_db->transaction_end();

            #if data send from list page When refesh page  : add by kik : 2013-12-02
            if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {
                echo "<script>alert('Delete Change Status Complete.');</script>";
                redirect('flow/flowChangeStatusList', 'refresh');
                #if data send from form page When return data to form page : add by kik : 2013-12-02
            } else {
                $array_return['success'][]['message'] = "Reject Change Status Complete";
                $json['return_val'] = $array_return;
            }

        else:

            $this->transaction_db->transaction_rollback();

            #if data send from list page When refesh page  : add by kik : 2013-12-02
            if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {
                echo "<script>alert('Delete Change Status not complete. Please check?');</script>";
                redirect('flow/flowChangeStatusList', 'refresh');

                #if data send from form page When return data to form page   : add by kik : 2013-12-02
            } else {
                $array_return['critical'][]['message'] = "Save Change Status Incomplete";
                $json['return_val'] = $array_return;
            }

        endif;

        $json['status'] = "save";
        echo json_encode($json);
    }

    /**
     * @function validation_openAction for work validation in Open Change product status
     * @author kik : 20140306
     * @return validation data format
     */
    public function validation_openAction() {
        /**
         * set Variable
         */
        $data = $this->input->post();


        $return = array();

        /**
         * chk PD_reserv Before Open
         */
        $resultCompare = $this->balance->_chkPDreservBeforeOpen($data['ci_confirm_qty'], $data['ci_inbound_id'], $data['prod_list'], SEPARATOR);
        $return = array_merge_recursive($return, $resultCompare);


        /**
         * chk change activity change status & sub_status on flow 'Change Product Status' if not change, validation will alert.
         */
        //$result_chk_status = $this->chk_activity_change_status_and_sub_status($data['ci_prod_status'], $data['ci_prod_sub_status'], $data['ci_prod_status_val'], $data['ci_prod_sub_status_val'], $data['ci_inbound_id'], $data['prod_list'], SEPARATOR);
        //$return = array_merge_recursive($return, $result_chk_status);

		
        if (!empty($return)):
            $json['status'] = "validation";
            $json['return_val'] = $return;
        else:
            $json['status'] = 'pass';
            $json['return_val'] = '';
        endif;
			
        echo json_encode($json);
    }

    /**
     * @function validation_confirmAction for work validation in Confirm Change product status
     * @author kik : 20140306
     * @return validation data format
     */
    public function validation_confirmAction() {
        $data = $this->input->post();
        $return = $this->check_validate_data($data);

        echo json_encode($return);
    }

    /**
     * @function validation_approveAction for work validation in Approve Change product status
     * @author kik : 20140306
     * @return validation data format
     */
    public function validation_approveAction() {
        $data = $this->input->post();

        $return = $this->check_validate_data($data);

        echo json_encode($return);
    }


    /**
     * @function validation_approveAction for work validation in Approve Change product status
     * @author kik : 20140306
     * @return validation data format
     */
    public function validation_approveAndNotmoveAction() {
        $data = $this->input->post();
        $return = $this->check_validate_data($data);

        echo json_encode($return);
    }



    /**
     * @function check_validate_data for work check validate data in ch_status_form form (use 2 fucntion >> confirm,approve change product status)
     * @author kik : 20140306
     * @param array $data
     * @return string (status validate, all massage , index of form for highlight object
     *
     * @last_modified xxxx (date)
     *
     */
    function check_validate_data($data) {

        /**
         * set Variable
         */
        $return = array();



        /**
         * chk PD_reserv Before Confirm,Approve
         */
        $resultCompare = $this->balance->_chkPDreservBeforeApprove($data['ci_confirm_qty'], $data['ci_inbound_id'], $data['ci_item_id'], $data['order_id'], $data['prod_list'], SEPARATOR, 'STK_T_Relocate_Detail');
        $return = array_merge_recursive($return, $resultCompare);

//        p($return);
//        exit();

        if (!empty($return)):
            $json['status'] = "validation";
            $json['return_val'] = $return;
        else:
            $json['status'] = 'pass';
            $json['return_val'] = '';
        endif;

//        p($json);
//        exit();

        return $json;
    }

    /**
     * @function check change activity change status & sub_status on flow 'Change Product Status' if not change, validation will alert.
     * @author Akkarapol : 20140516
     *
     * @param type $ci_prod_status
     * @param type $ci_prod_sub_status
     * @param type $ci_inbound_id
     * @param type $prod_list
     * @param type $delimiter
     * @return type
     *
     * @last_modified xxxx (date)
     */
    public function chk_activity_change_status_and_sub_status($ci_prod_status, $ci_prod_sub_status, $ci_prod_status_val, $ci_prod_sub_status_val, $ci_inbound_id, $prod_list, $delimiter){
        if (!empty($prod_list)):

            /**
             * Set Variable
             */
            $return = array();

            foreach ($prod_list as $key_rows => $rows) :
                $a_data = explode($delimiter, $rows);

                $query = array(
                    'select' => array(
                        'Product_Status',
                        'Product_Sub_Status'
                    ),
                    'where' => array(
                        'Inbound_Id' => $a_data[$ci_inbound_id]
                    )
                );

                $getProductDetailsByInboundId = $this->inbound->get_inbound_detail($query['select'], $query['where'])->row();

                if(trim($a_data[$ci_prod_status])==trim($getProductDetailsByInboundId->Product_Status)):
                    if(trim($a_data[$ci_prod_sub_status])==trim($getProductDetailsByInboundId->Product_Sub_Status)):
                        $line_no = $key_rows+1;
                        $set_return = array(
                            'message' => "In line NO. '{$line_no}' Product Status is not change. ",
                            'row' => array(
                                (string)$key_rows,
                                (string)$key_rows,
                            ),
                            'col' => array(
                                (string)$ci_prod_status_val,
                                (string)$ci_prod_sub_status_val
                            )
                        );
                        $return['critical'][] = $set_return;
                    endif;
                endif;
            endforeach;

            return $return;

        endif;
    }


    function approveAndNotmoveAction() {
        $conf = $this->config->item('_xml');
        $conf_build_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        # Parameter Form
        $token = $this->input->post('token');
        $flow_id = $this->input->post("flow_id");
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Relocate');
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));
        $order_id = $this->input->post("order_id");
        $prod_list = $this->input->post("prod_list");

        # Parameter Index Datatable
        $ci_reserv_qty = $this->input->post("ci_confirm_qty");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        $ci_actual_loc = $this->input->post("ci_actual_loc");
        $ci_remark = $this->input->post("ci_remark");

        $assigned_id = $this->input->post("assigned_id");


        # Check validate data
        $return = array();

        #Variable for check error
        $check_not_err = TRUE;
        // validate token
        $response = validate_token($token, $flow_id, $flow_info[0]->Present_State, $flow_info[0]->Process_Id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;

        # ================== Start save data =========================

        $this->transaction_db->transaction_start();

        # Check Estimate balance
        if ($check_not_err):
            $resultCompare = $this->balance->_chkPDreservBeforeApprove($ci_reserv_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Relocate_Detail');
            $return = array_merge_recursive($return, $resultCompare);
            if (!empty($resultCompare['critical'])):
                $check_not_err = FALSE;

            /**
             * 01.Set Alert Zone (set Error Code, Message, etc.)
             */
            endif;

        endif;

        # Update Process
        if ($check_not_err):

            $respond = $this->_updateProcess($this->input->post());

            if (!empty($respond['critical'])) :
                $check_not_err = FALSE;

                /**
                 * 1.Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update process";
                $return = array_merge_recursive($return, $respond);
            endif;

        endif;

         #update Order Detail Table
        if ($check_not_err):

            if (!empty($prod_list)) {

                foreach ($prod_list as $rows) {

                    unset($where);

                    $a_data = explode(SEPARATOR, $rows);
                    $detail = array();
                    $get_data_inbound = "Suggest_Location_Id,Actual_Location_Id";
                    $where_inbound['Inbound_Id']= $a_data[$ci_inbound_id];

                    $data_inbound = $this->inbound->get_inbound_detail($get_data_inbound,$where_inbound);
                    $data_inb = $data_inbound->row();

//                    $this->transaction_db->transaction_rollback();exit();

                    if(!empty($data_inbound)):
                        $detail['Suggest_Location_Id'] = $data_inb->Suggest_Location_Id;
                        $detail['Actual_Location_Id'] = $data_inb->Actual_Location_Id;
                    endif;

                    $detail['Confirm_Qty'] = str_replace(",", "", $a_data[$ci_reserv_qty]);
                    $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                    $detail['Putaway_By'] = $assigned_id;
                    $detail['Putaway_Date'] = date("Y-m-d H:i:s");

                    $where['Item_Id'] = $a_data[$ci_item_id];

                    if ($check_not_err):

                        $result_orderDetail_query = $this->pending->updatePendingDetail($detail, $where);

                        if ($result_orderDetail_query < 1):
                            $check_not_err = FALSE;
                            $return['critical'][]['message'] = "Can not update Order detail.";
                        /*
                         * LOG MSG 2
                         */
                        endif;

                    endif;
                }//foreach ($prod_list as $rows)
            }//if (!empty($prod_list))
        endif;


        # Update QTY in INBOUND table
        if ($check_not_err) :

            $result_updateInbound_query = $this->balance->updatePDReservQty($ci_reserv_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list);
            if (!$result_updateInbound_query):
                $check_not_err = FALSE;
                /*
                 * LOG MSG 3
                 */
                $return['critical'][]['message'] = "Can not update QTY in Inbound table.";
            endif;

        endif;

         #add  move product location (add new inbound)
        if ($check_not_err):
            $result_moveProductLocation = $this->stock_lib->moveProductLocation($order_id);
//        p($result_moveProductLocation);
//        $this->transaction_db->transaction_rollback();exit();
            if (!$result_moveProductLocation):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not move product location.";
            /*
             * LOG MSG 4
             */
            endif;
        endif;

        #add pallet
        if ($check_not_err):
            //ADD For Issue 3334 : BY KIK 2014-02-13
            if ($conf_build_pallet == TRUE) {
                $result_pallet_DPpartail = $this->pallet->insert_pallet_detail_relocatPT($order_id);
                $result_pallet_DPfull = $this->_update_pallet_DPfull($order_id);
                if (!empty($result_pallet_DPfull['critical'])) :
                    $check_not_err = FALSE;

                    /**
                     * 5.Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not update pallet data.";
                    $return = array_merge_recursive($return, $result_pallet_DPfull);
                endif;
            }
        //END ADD

        endif;


        #check if for return json and set transaction
        if ($check_not_err):

            $set_return['message'] = "Approve Change Status Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;

            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

        else:

            $array_return['critical'][]['message'] = "Approve Change Status Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);

            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

        endif;

        echo json_encode($json);
    }

    function get_location(){
        // error_reporting(E_ALL);
        header('Access-control-Allow-0rigin: *');
        $params = $this->input->get();
        // $this->load->library('getlocation_no_dispatch_area');
        $data = $this->getlocation_no_dispatch_area->get_location($params);
    }


        function changeProductStatusToCompleteNotMove($input_data){

        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        $conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];
         
        # Parameter Get session data
        $renter_id = $this->session->userdata('renter_id');
        $owner_id = $this->session->userdata('owner_id');
        $user_id = $this->session->userdata('user_id');

        // $input_data[''];
        # Parameter Form

        // $process_id = $this->input->post("process_id");
        // $present_state = $this->input->post("present_state");
        // $process_type = $this->input->post("process_type");
        // $action_type = $this->input->post("action_type");
        // $next_state = $this->input->post("next_state");
        // $document_no = $this->input->post("document_no");
        // $est_action_date = convert_date_format($this->input->post("est_action_date"));
        // $remark = $this->input->post("remark");
        // $assigned_id = $this->input->post("assigned_id");
        // $is_urgent = $this->input->post("is_urgent");
      
        $process_id = $input_data['process_id'];
        // $present_state = $input_data['present_state'];
        $present_state = -2;
        $process_type =$input_data['process_type'];
        $action_type = $input_data['action_type'];
        $next_state = $input_data['next_state'];
        $document_no = $input_data['document_no'];
        $est_action_date = convert_date_format($input_data['est_action_date']);
        $remark = $input_data['remark'];
        $assigned_id = $input_data['assigned_id'];
        $is_urgent = $input_data['is_urgent'];

        // $document_ref = $this->input->post("document_ref"); //ADD BY POR 2014-11-16  use customs report รายงานการนำำของออกเพื่อการอื่นเป็นการชั่วคราวและการนำกลับ
        $document_ref = $input_data['document_ref'];

        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Parameter Order Detail
        // $prod_list = $this->input->post("prod_list");
        // $prod_del_list = $this->input->post("prod_del_list");

        $prod_list = $input_data['prod_list'];
        $prod_del_list = $input_data['prod_del_list'];


        # Parameter Index Datatable
        // $ci_prod_code = $this->input->post("ci_prod_code");
        // $ci_lot = $this->input->post("ci_lot");
        // $ci_serial = $this->input->post("ci_serial");
        // $ci_mfd = $this->input->post("ci_mfd");
        // $ci_exp = $this->input->post("ci_exp");
        // $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        // $ci_confirm_qty = $this->input->post("ci_confirm_qty");
        // $ci_remark = $this->input->post("ci_remark");
        // $ci_prod_id = $this->input->post("ci_prod_id");
        // $ci_prod_status = $this->input->post("ci_prod_status");
        // $ci_unit_id = $this->input->post("ci_unit_id");
        // $ci_item_id = $this->input->post("ci_item_id");
        // $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");
        // $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        // $ci_actual_loc = $this->input->post("ci_actual_loc");
        // $ci_old_loc = $this->input->post("ci_old_loc");
        // $ci_inbound_id = $this->input->post("ci_inbound_id");
        // $ci_dp_type_pallet = $this->input->post("ci_dp_type_pallet");

        $ci_prod_code = $input_data['ci_prod_code'];
        $ci_lot = $input_data['ci_lot'];
        $ci_serial = $input_data['ci_serial'];
        $ci_mfd = $input_data['ci_mfd'];
        $ci_exp = $input_data['ci_exp'];
        $ci_reserv_qty = $input_data['ci_reserv_qty'];
        $ci_confirm_qty = $input_data['ci_confirm_qty'];
        $ci_remark = $input_data['ci_remark'];
        $ci_prod_id = $input_data['ci_prod_id'];
        $ci_prod_status = $input_data['ci_prod_status'];
        $ci_unit_id = $input_data['ci_unit_id'];
        $ci_item_id = $input_data['ci_item_id'];
        $ci_prod_sub_status = $input_data['ci_prod_sub_status'];
        $ci_suggest_loc = $input_data['ci_suggest_loc'];
        $ci_actual_loc = $input_data['ci_actual_loc'];
        $ci_old_loc = $input_data['ci_old_loc'];
        $ci_inbound_id = $input_data['ci_inbound_id'];
        $ci_dp_type_pallet = $input_data['ci_dp_type_pallet'];

    
        # Parameter price per uint
        if ($this->settings['price_per_unit'] == TRUE) {
            // $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            // $ci_unit_price = $this->input->post("ci_unit_price");
            // $ci_all_price = $this->input->post("ci_all_price");
            // $ci_unit_price_id = $this->input->post("ci_unit_price_id");
            
            $ci_price_per_unit = $input_data['ci_price_per_unit'];
            $ci_unit_price = $input_data['ci_unit_price'];
            $ci_all_price = $input_data['ci_all_price'];
            $ci_unit_price_id = $input_data['ci_unit_price_id'];
        }

        // Shot Process To Change Status Not Move

        // Shot Process To Change Status Not Move
        # Check validate data

        // $array_sp = array('process_id' => $process_id = $input_data['process_id']
        //                  ,'present_state' => $present_state = $input_data['present_state']
        //                  ,'process_type' => $process_type =$input_data['process_type']
        //                  ,'action_type' => $action_type = $input_data['action_type']
        //                  ,'next_state' => $next_state = $input_data['next_state']
        //                  ,'document_no' => $document_no = $input_data['document_no']
        //                  ,'est_action_date' => $est_action_date = convert_date_format($input_data['est_action_date'])
        //                  ,'remark' => $remark = $input_data['remark']
        //                  ,'assigned_id' => $assigned_id = $input_data['assigned_id']
        //                  ,'is_urgent' => $is_urgent = $input_data['is_urgent']
        //                  ,'document_ref' => $document_ref = $input_data['document_ref']
        //                  ,'prod_list' => $prod_list = $input_data['prod_list']
        //                  ,'prod_del_list' => $prod_del_list = $input_data['prod_del_list']
        //                  ,'ci_prod_code' => $ci_prod_code = $input_data['ci_prod_code']
        //                  ,'ci_lot' => $ci_lot = $input_data['ci_lot']
        //                  ,'ci_serial' => $ci_serial = $input_data['ci_serial']
        //                  ,'ci_mfd' => $ci_mfd = $input_data['ci_mfd']
        //                  ,'ci_exp' => $ci_exp = $input_data['ci_exp']
        //                  ,'ci_reserv_qty' => $ci_reserv_qty = $input_data['ci_reserv_qty']
        //                  ,'ci_confirm_qty' => $ci_confirm_qty = $input_data['ci_confirm_qty']
        //                  ,'ci_remark' => $ci_remark = $input_data['ci_remark']
        //                  ,'ci_prod_id' => $ci_prod_id = $input_data['ci_prod_id']
        //                  ,'ci_prod_status' => $ci_prod_status = $input_data['ci_prod_status']
        //                  ,'ci_unit_id' => $ci_unit_id = $input_data['ci_unit_id']
        //                  ,'ci_item_id' => $ci_item_id = $input_data['ci_item_id']
        //                  ,'ci_prod_sub_status' => $ci_prod_sub_status = $input_data['ci_prod_sub_status']
        //                  ,'ci_suggest_loc' => $ci_suggest_loc = $input_data['ci_suggest_loc']
        //                  ,'ci_actual_loc' => $ci_actual_loc = $input_data['ci_actual_loc']
        //                  ,'ci_old_loc' => $ci_old_loc = $input_data['ci_old_loc']
        //                  ,'ci_inbound_id' => $ci_inbound_id = $input_data['ci_inbound_id']
        //                  ,'ci_dp_type_pallet' => $ci_dp_type_pallet = $input_data['ci_dp_type_pallet']
        //                  ,'ci_price_per_unit' => $ci_price_per_unit = $input_data['ci_price_per_unit']
        //                  ,'ci_unit_price' => $ci_unit_price = $input_data['ci_unit_price']
        //                  ,'ci_all_price' => $ci_all_price = $input_data['ci_all_price']
        //                  ,'ci_unit_price_id' => $ci_unit_price_id = $input_data['ci_unit_price_id']);



        $return = array();

        # Check Estimate balance ตรวจสอบค่าของ PD_Reserve ว่ามีค่าพอที่จะใช้ในการทำขั้นตอนที่ต้องการหรือไม่

        $resultCompare = $this->balance->_chkPDreservBeforeOpen($ci_confirm_qty, $ci_inbound_id, $prod_list, SEPARATOR);
        $return = array_merge_recursive($return, $resultCompare);

        if (!empty($resultCompare['critical'])):
        /*
         * LOG MSG 01
         */
        endif;


        #หากมี error ที่ไม่สามารถปล่อยผ่านได้ จะ return กลับไปยังหน้า view เพื่อบังคับให้แก้ไขข้อมูลดังกล่าว
        if (!empty($return['critical'])) :

            $json['status'] = "validation";
            $json['return_val'] = $return;

        else:

            $check_not_err = TRUE; //for check error all process , if process error set value = FALSE
            # ================== Start save data =========================

            // $this->transaction_db->transaction_start();

            # generate Change Status Document No.
            if ($check_not_err):
                $prefix = "CH";
//                $document_no = createDocumentNo($prefix);
                $document_no = create_document_no_by_type($prefix); // Add by Ton! 20140428
  // p($document_no);exit;
                $data['Document_No'] = $document_no;
                if ($document_no == "" || empty($document_no)) {
                    $check_not_err = FALSE;

                    /*
                     * LOG MSG 1
                     */
                    $return['critical'][]['message'] = "Can not create Document No.";
                }
            endif;

            # add New workflow
            if ($check_not_err):
                list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, 2, $data); //Edit by Ton! 20131021
                if ($flow_id == "" || $action_id == "") {
                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 2
                     */
                    $return['critical'][]['message'] = "Can not add new work flow.";
                } else {
                    $order['Flow_Id'] = $flow_id;
                }
            endif;

            # Save new order
            if ($check_not_err):
                $order = array(
                    'Doc_Relocate' => $data['Document_No']
                    , 'Flow_Id' => $flow_id
                    , 'Doc_Type' => $process_type
                    , 'Process_Type' => $process_type
                    , 'Estimate_Action_Date' => $est_action_date
                    , 'Owner_Id' => $owner_id
                    , 'Renter_Id' => $renter_id
                    , 'Create_By' => $user_id
                    , 'Create_date' => date("Y-m-d H:i:s")
                    , 'Assigned_Id' => $assigned_id
                    , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                    , 'Is_urgent' => $is_urgent
                    , 'Custom_Doc_Ref' => $document_ref  //ADD BY POR  2014-11-17 for customs report รายงานการนำของออกเพื่อการอื่นเป็นการชั่วคราวและการนำกลับ
                );
                $order_id = $this->relocate->addReLocationOrder($order);

                if ($order_id == "" || empty($order_id)) {
                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 3
                     */
                    $return['critical'][]['message'] = "Can not add new order";
                }
            endif; //end check update order
            # Order Detail  - Set data and Save data into table
            if ($check_not_err):

                # add data into order detail
                $order_detail = array();

                if (!empty($prod_list)):

                    #set parameter for savr order detail
                    foreach ($prod_list as $rows) {
                        $a_data = explode(SEPARATOR, $rows);
                        $inbound_id = $a_data[$ci_inbound_id];

                        // Add By Akkarapol, 24/01/2014, เปลี่ยนการ ส่งค่า $column ไปให้ stock->getStockDetailId นั้น query ค่า เพราะถ้าจะ query แค่ '*' ค่าที่เป็น dateTime จะผิดและทำให้นำไปใช้งานต่อไม่ได้ เลยต้อง CONVERT ออกมาให้เป็น Type 21
                        $column = array(
                            '*'
                            , 'CONVERT(VARCHAR,Product_Mfd,21) as Product_Mfd'
                            , 'CONVERT(VARCHAR,Product_Exp,21) as Product_Exp'
                            , 'CONVERT(VARCHAR,Receive_Date,21) as Receive_Date'
                            , 'CONVERT(VARCHAR,Putaway_Date,21) as Putaway_Date'
                        );
                        // END Add By Akkarapol, 24/01/2014,  เปลี่ยนการ ส่งค่า $column ไปให้ stock->getStockDetailId นั้น query ค่า เพราะถ้าจะ query แค่ '*' ค่าที่เป็น dateTime จะผิดและทำให้นำไปใช้งานต่อไม่ได้ เลยต้อง CONVERT ออกมาให้เป็น Type 21

                        $where['Inbound_Id'] = $inbound_id;
                        $item_detail = $this->stock->getStockDetailId($column, $where)->result();

                        if (!empty($item_detail)):

                            foreach ($item_detail as $item) {

                                $detail['Document_No'] = $item->Document_No;
                                $detail['Doc_Refer_Int'] = $item->Doc_Refer_Int;
                                $detail['Doc_Refer_Ext'] = $item->Doc_Refer_Ext;
                                $detail['Doc_Refer_Inv'] = $item->Doc_Refer_Inv;
                                $detail['Doc_Refer_CE'] = $item->Doc_Refer_CE;
                                $detail['Doc_Refer_BL'] = $item->Doc_Refer_BL;
                                $detail['Doc_Refer_AWB'] = $item->Doc_Refer_AWB;
                                $detail['Inbound_Item_Id'] = $item->Inbound_Id;
                                $detail['Product_Id'] = $item->Product_Id;
                                $detail['Product_Code'] = $item->Product_Code;
                                $detail['Product_License'] = $item->Product_License;
								
								/*
                                $detail['Product_Lot'] = $item->Product_Lot;
                                $detail['Product_Serial'] = $item->Product_Serial;
                                $detail['Product_Mfd'] = $item->Product_Mfd;
                                $detail['Product_Exp'] = $item->Product_Exp;
								*/
								
                                $detail['Product_Lot'] = $a_data[$ci_lot];
                                $detail['Product_Serial'] = $a_data[$ci_serial];
								
								if ($a_data[$ci_mfd] != "") {
									$detail['Product_Mfd'] = convertDate($a_data[$ci_mfd], "eng", "iso", "-");
								} else {
									$detail['Product_Mfd'] = $item->Product_Mfd;
								}								
								
								if ($a_data[$ci_exp] != "") {
									$detail['Product_Exp'] = convertDate($a_data[$ci_exp], "eng", "iso", "-");
								} else {
									$detail['Product_Exp'] = $item->Product_Exp;
								}
 
                                $detail['Receive_Date'] = $item->Receive_Date;
                                $detail['Unit_Id'] = $item->Unit_Id;

                                #add value of invoice_no if config open invoice by item
                                if($conf_inv):
                                    $detail['Invoice_Id'] = $item->Invoice_Id;
                                endif;

                                #add value of container_no if config open invoice by item
                                if($conf_cont):
                                    $detail['Cont_Id'] = $item->Cont_Id;
                                endif;

                                #add value of container_no if config open invoice by item
                                if($conf_pallet):
                                    $detail['Pallet_Id'] = $item->Pallet_Id;
                                endif;

                                /**
                                 * find product code in product table by product_id
                                 */
                                $product_code = $this->pending->getProductCodeByProductId($detail['Product_Id']);
                                if (!empty($product_code)):
                                    $product_code = $product_code[0]->Product_Code;
                                else:
                                    $check_not_err = FALSE;
                                    /*
                                     * LOG MSG 4
                                     */
                                    $return['critical'][]['message'] = "Not have this "._lang('product_code')." in Product table";
                                endif;

                                /**
                                 * find Suggest_Location_Id
                                 */
//                                $setSuggestLocation = $this->suggest_location->getSuggestLocationArray('suggestLocation', $product_code, $a_data[$ci_prod_status], '1');
//                                $detail['Suggest_Location_Id'] = (!empty($setSuggestLocation) ? $setSuggestLocation[0]['Location_Id'] : NULL);
                            }

                        else:
                            $check_not_err = FALSE;
                            /*
                             * LOG MSG 5
                             */
                            $return['critical'][]['message'] = "Not have data in Inbound table";
                        endif;


                        $detail['Order_Id'] = $order_id;
                        $detail['Product_Status'] = $a_data[$ci_prod_status];
                        $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                        $detail['Reserv_Qty'] = $a_data[$ci_confirm_qty];
                        $detail['Confirm_Qty'] = $a_data[$ci_confirm_qty];
//			$detail['Product_Mfd'] = ( $a_data[$ci_mfd] == "" ? $item->Product_Mfd : $a_data[$ci_mfd] );
//			$detail['Product_Exp'] = ( $a_data[$ci_exp] == "" ? $item->Product_Exp : $a_data[$ci_exp] );

                        //ADD BY POR 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                        if ($conf_price_per_unit) {
                            $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]);

                            // Add By Akkarapol, 24/01/2014, เพิ่มการตรวจสอบว่าถ้าค่าของ Price Per Unit ที่ได้มานั้น ไม่มีค่าอะไร ก็จะให้ค่าเป็น Null เพื่อให้สามารถเซฟเข้าฐานข้อมูลได้
                            if ($detail['Price_Per_Unit'] == '' or $detail['Price_Per_Unit'] == ' '):
                                $detail['Price_Per_Unit'] = NULL;
                            endif;
                            // END Add By Akkarapol, 24/01/2014, เพิ่มการตรวจสอบว่าถ้าค่าของ Price Per Unit ที่ได้มานั้น ไม่มีค่าอะไร ก็จะให้ค่าเป็น Null เพื่อให้สามารถเซฟเข้าฐานข้อมูลได้

                            $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                            $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]);
                        }
                        //END ADD

                        $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                        // $detail['Old_Location_Id'] = $a_data[$ci_actual_loc]; // Add By Akkarapol, 29/10/2013, Old_Location_Id ที่จะใช้ในขั้นตอนของการ Relocation มันต้องใช้เป็น Actual Location ที่มันถูกเก็บลง Inbound สิ จะมาใช้ Old_Location_Id ได้ยังไง ในเมื่อ เราต้องการรู้ว่า ปัจจุบันมันอยู่ที่ไหน ไม่ใช่ มันมาจากที่ไหน เชอะ!!!!!
                        $detail['Old_Location_Id'] = $item->Actual_Location_Id; // Add By Akkarapol, 29/10/2013, Old_Location_Id ที่จะใช้ในขั้นตอนของการ Relocation มันต้องใช้เป็น Actual Location ที่มันถูกเก็บลง Inbound สิ จะมาใช้ Old_Location_Id ได้ยังไง ในเมื่อ เราต้องการรู้ว่า ปัจจุบันมันอยู่ที่ไหน ไม่ใช่ มันมาจากที่ไหน เชอะ!!!!!
                        $detail['Actual_Location_Id'] = $a_data[$ci_actual_loc];
                        //ADD For Issue 3334 : BY KIK 2014-02-17
                        if ($conf_pallet) {
                            if ($a_data[$ci_dp_type_pallet] == "") {
                                $detail['DP_Type_Pallet'] = NULL;
                            } else {
                                $detail['DP_Type_Pallet'] = $a_data[$ci_dp_type_pallet];
                            }
                        }
                        //END ADD

                        $order_detail[] = $detail;
                    }//end for product list
                    #save order detail and PD_Reserv_Qty in inbound table
                //    p($order_detail);exit();
                    if ($check_not_err):
                        if (!empty($order_detail)):
                            $result_order_detail = $this->relocate->addReLocationOrderDetail($order_detail);
                            if ($result_order_detail <= 0):
                                $check_not_err = FALSE;
                                /*
                                 * LOG MSG 6
                                 */

                                $return['critical'][]['message'] = "Can not add order detail.";
                            endif;

                            #update pd reserv into inbound

                            if ($check_not_err):
                                $result_PD_reserv_qty = $this->stock->reservPDReservQtyArray($order_detail);
                                if (!$result_PD_reserv_qty):
                                    $check_not_err = FALSE;
                                    /*
                                     * LOG MSG 7
                                     */
                                    $return['critical'][]['message'] = "Can not update QTY in Inbound table.";
                                endif;
                            endif;

                        endif; //end check not empty set data in $order detail

                    endif; //end check err before sava order detail

                endif; //end check empty $prod_list

            endif; //end check update order detail
            #Set Message Alert
            if ($check_not_err):

                $array_return['success'][]['message'] = "Open Job Change Status Complete";
                $json['return_val'] = $array_return;

                // $this->transaction_db->transaction_end();

            else:

                $array_return['critical'][]['message'] = "Open Job Change Status Incomplete.";
                $json['return_val'] = $array_return;

                $this->transaction_db->transaction_rollback();

            endif; //end set message alert

            $json['status'] = "save";

        endif; //end check critical validate return

        $array_sp = array('flow_id' => $flow_id
            ,'oder_id' => $order_id
        ,'present_state' => 2
        ,'process_type' => $process_type =$input_data['process_type']
        ,'action_type' => $action_type = $input_data['action_type']
        ,'next_state' => -2
        ,'document_no' => $document_no
        ,'est_action_date' => $est_action_date = convert_date_format($input_data['est_action_date'])
        ,'remark' => $remark = $input_data['remark']
        ,'assigned_id' => $assigned_id = $input_data['assigned_id']
        ,'is_urgent' => $is_urgent = $input_data['is_urgent']
        ,'document_ref' => $document_ref = $input_data['document_ref']
        ,'prod_list' => $prod_list = $input_data['prod_list']
        ,'prod_del_list' => $prod_del_list = $input_data['prod_del_list']
        ,'ci_prod_code' => $ci_prod_code = $input_data['ci_prod_code']
        ,'ci_lot' => $ci_lot = $input_data['ci_lot']
        ,'ci_serial' => $ci_serial = $input_data['ci_serial']
        ,'ci_mfd' => $ci_mfd = $input_data['ci_mfd']
        ,'ci_exp' => $ci_exp = $input_data['ci_exp']
        ,'ci_reserv_qty' => $ci_reserv_qty = $input_data['ci_reserv_qty']
        ,'ci_confirm_qty' => $ci_confirm_qty = $input_data['ci_confirm_qty']
        ,'ci_remark' => $ci_remark = $input_data['ci_remark']
        ,'ci_prod_id' => $ci_prod_id = $input_data['ci_prod_id']
        ,'ci_prod_status' => $ci_prod_status = $input_data['ci_prod_status']
        ,'ci_unit_id' => $ci_unit_id = $input_data['ci_unit_id']
        ,'ci_item_id' => $ci_item_id = $input_data['ci_item_id']
        ,'ci_prod_sub_status' => $ci_prod_sub_status = $input_data['ci_prod_sub_status']
        ,'ci_suggest_loc' => $ci_suggest_loc = $input_data['ci_suggest_loc']
        ,'ci_actual_loc' => $ci_actual_loc = $input_data['ci_actual_loc']
        ,'ci_old_loc' => $ci_old_loc = $input_data['ci_old_loc']
        ,'ci_inbound_id' => $ci_inbound_id = $input_data['ci_inbound_id']
        ,'ci_dp_type_pallet' => $ci_dp_type_pallet = $input_data['ci_dp_type_pallet']
        ,'ci_price_per_unit' => $ci_price_per_unit = $input_data['ci_price_per_unit']
        ,'ci_unit_price' => $ci_unit_price = $input_data['ci_unit_price']
        ,'ci_all_price' => $ci_all_price = $input_data['ci_all_price']
        ,'ci_unit_price_id' => $ci_unit_price_id = $input_data['ci_unit_price_id']);

        $conf = $this->config->item('_xml');
        $conf_build_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        # Parameter Form
        // $token = $this->input->post('token');
        $flow_id = $flow_id;
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Relocate');
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $order_id));
        $order_id = $order_id;
        $prod_list = $this->input->post("prod_list");

        # Parameter Index Datatable
        // $ci_reserv_qty = $this->input->post("ci_confirm_qty");
        // $ci_inbound_id = $this->input->post("ci_inbound_id");
        // $ci_item_id = $this->input->post("ci_item_id");
        // $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        // $ci_actual_loc = $this->input->post("ci_actual_loc");
        // $ci_remark = $this->input->post("ci_remark");
        $ci_reserv_qty = $input_data['ci_confirm_qty'];
        $ci_inbound_id = $input_data['ci_inbound_id'];
        $ci_item_id = $input_data['ci_item_id'];
        $ci_suggest_loc = $input_data['ci_suggest_loc'];
        $ci_actual_loc = $input_data['ci_actual_loc'];
        $ci_remark = $input_data['remark'];

        // $assigned_id = $this->input->post("assigned_id");
        $assigned_id = $input_data['assigned_id'];


        # Check validate data
        $return = array();

        #Variable for check error
        $check_not_err = TRUE;

        // validate token
        $token = register_token($flow_id, -2, $process_id);

        $response = validate_token($token, $flow_id, -2, $process_id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;

        # ================== Start save data =========================

        // $this->transaction_db->transaction_start();

        # Check Estimate balance
        if ($check_not_err):
            $resultCompare = $this->balance->_chkPDreservBeforeApprove($ci_reserv_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Relocate_Detail');
            $return = array_merge_recursive($return, $resultCompare);
            if (!empty($resultCompare['critical'])):
                $check_not_err = FALSE;

            /**
             * 01.Set Alert Zone (set Error Code, Message, etc.)
             */
            endif;

        endif;

        # Update Process
        if ($check_not_err):
            // _updateProcess_shotprocess
            $respond = $this->_updateProcess_shotprocess($array_sp);

            if (!empty($respond['critical'])) :
                $check_not_err = FALSE;

                /**
                 * 1.Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update process";
                $return = array_merge_recursive($return, $respond);
            endif;

        endif;

         #update Order Detail Table
        if ($check_not_err):

            if (!empty($prod_list)) {

                foreach ($prod_list as $rows) {

                    unset($where);

                    $a_data = explode(SEPARATOR, $rows);
                    $detail = array();
                    $get_data_inbound = "Suggest_Location_Id,Actual_Location_Id";
                    $where_inbound['Inbound_Id']= $a_data[$ci_inbound_id];

                    $data_inbound = $this->inbound->get_inbound_detail($get_data_inbound,$where_inbound);
                    $data_inb = $data_inbound->row();
   
//                    $this->transaction_db->transaction_rollback();exit();

                    if(!empty($data_inbound)):
                        $detail['Suggest_Location_Id'] = $data_inb->Suggest_Location_Id;
                        $detail['Actual_Location_Id'] = $data_inb->Actual_Location_Id;
                    endif;

                    $detail['Confirm_Qty'] = str_replace(",", "", $a_data[$ci_reserv_qty]);
                    $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                    $detail['Putaway_By'] = $assigned_id;
                    $detail['Putaway_Date'] = date("Y-m-d H:i:s");

                    $where['Item_Id'] = $a_data[$ci_item_id];

                    if ($check_not_err):

                        // $result_orderDetail_query = $this->pending->updatePendingDetail($detail, $where);
                        // if ($result_orderDetail_query < 1):
                        //     $check_not_err = FALSE;
                        //     $return['critical'][]['message'] = "Can not update Order detail.";
                        // /*
                        //  * LOG MSG 2
                        //  */
                        // endif;

                    endif;
                }//foreach ($prod_list as $rows)
            }//if (!empty($prod_list))
        endif;


        # Update QTY in INBOUND table
        if ($check_not_err) :

            $result_updateInbound_query = $this->balance->updatePDReservQty($ci_reserv_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list);
            if (!$result_updateInbound_query):
                $check_not_err = FALSE;
                /*
                 * LOG MSG 3
                 */
                $return['critical'][]['message'] = "Can not update QTY in Inbound table.";
            endif;

        endif;

         #add  move product location (add new inbound)
        if ($check_not_err):
            $result_moveProductLocation = $this->stock_lib->moveProductLocation($order_id);
//        p($result_moveProductLocation);
//        $this->transaction_db->transaction_rollback();exit();
            if (!$result_moveProductLocation):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not move product location.";
            /*
             * LOG MSG 4
             */
            endif;
        endif;

        #add pallet

        if ($check_not_err):
            //ADD For Issue 3334 : BY KIK 2014-02-13
            if ($conf_build_pallet == TRUE) {
                $result_pallet_DPpartail = $this->pallet->insert_pallet_detail_relocatPT($order_id);
                $result_pallet_DPfull = $this->_update_pallet_DPfull($order_id);
                if (!empty($result_pallet_DPfull['critical'])) :
                    $check_not_err = FALSE;

                    /**
                     * 5.Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not update pallet data.";
                    $return = array_merge_recursive($return, $result_pallet_DPfull);
                endif;
            }
        //END ADD

        endif;

        #check if for return json and set transaction
        if ($check_not_err):

            $set_return['message'] = "Approve Change Status Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;

            /**
             * ================== Auto End Transaction =========================
             */
            // $this->transaction_db->transaction_end();

        else:

            $array_return['critical'][]['message'] = "Approve Change Status Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);

            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

        endif;
        return json_encode($json);

    }

    function _updateProcess_shotprocess($data_in){

        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        $conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;

        #====================== Start Get Parameter ============================
        # Parameter Session and config
        $user_id = $this->session->userdata('user_id');
        $owner_id = $this->config->item('owner_id');
        $renter_id = $this->config->item('renter_id');

        # Parameter Form
        // $flow_id = $this->input->post("flow_id");
        // $order_id = $this->input->post("order_id");
        // $process_id = $this->input->post("process_id");
        // $present_state = $this->input->post("present_state");
        // $process_type = $this->input->post("process_type");
        // $action_type = $this->input->post("action_type");
        // $next_state = $this->input->post("next_state");
        // $document_no = $this->input->post("document_no");
        // $est_action_date = $this->input->post("est_action_date");
        // $action_date = $this->input->post("action_date");
        // $remark = $this->input->post("remark");
        // $assigned_id = $this->input->post("assigned_id");
        // $is_urgent = $this->input->post("is_urgent");
        // $document_ref = $this->input->post("document_ref");

        $flow_id 			= $data_in['flow_id'];
        $order_id           = $data_in['oder_id'];
        $process_id         = 8;
        $present_state      = 3;
        $process_type       = $data_in['process_type'];
        $action_type        = $data_in['action_type'];
        $next_state         = -2;
        $document_no        = $data_in['document_no'];
        $est_action_date    = $data_in['est_action_date'];
        $action_date        = '';
        $remark             = $data_in['remark'];
        $assigned_id        = $data_in['assigned_id'];
        $is_urgent          = $data_in['is_urgent'];
        $document_ref       = $data_in['document_ref'];






        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Parameter Order Detail
        // $prod_list = $this->input->post("prod_list");
        // $prod_del_list = $this->input->post("prod_del_list");
        $prod_list = $data_in['prod_list'];
        $prod_del_list = $data_in['prod_del_list'];

        # Parameter Index Datatable
        // $ci_prod_code = $this->input->post("ci_prod_code");
        // $ci_lot = $this->input->post("ci_lot");
        // $ci_serial = $this->input->post("ci_serial");
        // $ci_mfd = $this->input->post("ci_mfd");
        // $ci_exp = $this->input->post("ci_exp");
        // $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        // $ci_confirm_qty = $this->input->post("ci_confirm_qty");
        // $ci_remark = $this->input->post("ci_remark");
        // $ci_prod_id = $this->input->post("ci_prod_id");
        // $ci_prod_status = $this->input->post("ci_prod_status");
        // $ci_unit_id = $this->input->post("ci_unit_id");
        // $ci_item_id = $this->input->post("ci_item_id");
        // $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");
        // $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        // $ci_actual_loc = $this->input->post("ci_actual_loc");
        // $ci_old_loc = $this->input->post("ci_old_loc");
        // $ci_inbound_id = $this->input->post("ci_inbound_id");

        $ci_prod_code = $data_in['ci_prod_code'];
        $ci_lot = $data_in['ci_lot'];
        $ci_serial = $data_in['ci_serial'];
        $ci_mfd = $data_in['ci_mfd'];
        $ci_exp = $data_in['ci_exp'];
        $ci_reserv_qty = $data_in['ci_reserv_qty'];
        $ci_confirm_qty = $data_in['ci_confirm_qty'];
        $ci_remark = $data_in['ci_remark'];
        $ci_prod_id = $data_in['ci_prod_id'];
        $ci_prod_status = $data_in['ci_prod_status'];
        $ci_unit_id = $data_in['ci_unit_id'];
        $ci_item_id = $data_in['ci_item_id'];
        $ci_prod_sub_status = $data_in['ci_prod_sub_status'];
        $ci_suggest_loc = $data_in['ci_suggest_loc'];
        $ci_actual_loc = $data_in['ci_actual_loc'];
        $ci_old_loc = $data_in['ci_old_loc'];
        $ci_inbound_id = $data_in['ci_inbound_id'];

        # Parameter price per unit
        if ($this->settings['price_per_unit'] == TRUE) {
            // $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            // $ci_unit_price = $this->input->post("ci_unit_price");
            // $ci_all_price = $this->input->post("ci_all_price");
            // $ci_unit_price_id = $this->input->post("ci_unit_price_id");

            $ci_price_per_unit = $data_in['ci_price_per_unit'];
            $ci_unit_price = $data_in['ci_unit_price'];
            $ci_all_price = $data_in['ci_all_price'];
            $ci_unit_price_id = $data_in['ci_unit_price_id'];
        }


        #------------------------ End Get Parameter ----------------------------
        #========================== Start Save Data ============================

        if (empty($flow_id) || $flow_id == "" || empty($prod_list) || $prod_list == ""):
            $check_not_err = FALSE;
            $return['critical'][]['message'] = "Not have flow_id or product list.";
        /*
         * LOG MSG 1
         */
        endif;

        # Update Order and Order Detail
        # Get workflow
        if ($check_not_err):
            $data['Document_No'] = $document_no;
            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021

            if ($action_id == "" || empty($action_id)):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update workflow.";
            /*
             * LOG MSG 2
             */
            endif;

        endif;

        #update Order Table
        if ($check_not_err):

            if ($next_state == 3 || $next_state == -2):

                $order = array(
                    'Modified_By' => $user_id
                    , 'Modified_Date' => date("Y-m-d H:i:s")
                    , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                    , 'Assigned_Id' => $assigned_id
                    , 'Actual_Action_Date' => date("Y-m-d H:i:s")
                    , 'Is_urgent' => $is_urgent
                    , 'Custom_Doc_Ref' => $document_ref //ADD BY POR 2014-11-17 for customs report
                );

            else:
                $order = array(
                    'Modified_By' => $user_id
                    , 'Modified_Date' => date("Y-m-d H:i:s")
                    , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                    , 'Assigned_Id' => $assigned_id
                    , 'Is_urgent' => $is_urgent
                    , 'Custom_Doc_Ref' => $document_ref //ADD BY POR 2014-11-17 for customs report
                );
            endif;

            $where['Flow_Id'] = $flow_id;

            #update order
            $result_order_query = $this->prod_status->updateOrder($order, $where);

            if (!$result_order_query):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update Order.";
            /*
             * LOG MSG 3
             */
            endif;

        endif;


        /**
         * update Order Detail Table
         */
        if ($check_not_err):

            $order_detail = array();

            if (!empty($prod_list)) {

                foreach ($prod_list as $rows) {

                    unset($where);
                    $a_data = explode(SEPARATOR, $rows);
                    $is_new = $a_data[$ci_item_id];

                    if ("new" != $is_new) {

                        $detail = array();
                        $detail['Product_Status'] = $a_data[$ci_prod_status];
                        $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                        $detail['Reserv_Qty'] = str_replace(",", "", $a_data[$ci_confirm_qty]); //+++++Edit BY POR 2013-12-03 ตัด comma ออกเพื่อให้สามารถบันทึกได้

                        $detail['Confirm_Qty'] = str_replace(",", "", $a_data[$ci_confirm_qty]); //ADD BY POR 2014-06-19 เพิ่มให้ insert ใน confirm ด้วย
			//$detail['Product_Mfd'] = $a_data[$ci_mfd];
			//$detail['Product_Exp'] = $a_data[$ci_exp];

                        //ADD BY POR 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                        if ($this->settings['price_per_unit'] == TRUE) {
                            $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]);
                            $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                            $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]);
                        }
                        //END ADD

                        $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                        $where['Item_Id'] = $a_data[$ci_item_id];

                        $product_code = $this->relocate->getProductCodeByItemId($a_data[$ci_item_id]);
                        $product_code = $product_code[0]->Product_Code;
//                        $setSuggestLocation = $this->suggest_location->getSuggestLocationArray('suggestLocation', $product_code, $a_data[$ci_prod_status], '1', NULL, $a_data[$ci_prod_sub_status], str_replace(",", "", $a_data[$ci_confirm_qty])); // Add By Akkarapol, 04/11/2013, ใส่ค่าตามที่ sp_PA_suggestLocation ต้องการ ไม่งั้นโค๊ดเก่าที่เคยใช้ได้มันจะใช้งานไม่ได้
//                        $detail['Suggest_Location_Id'] = (!empty($setSuggestLocation) ? $setSuggestLocation[0]['Location_Id'] : NULL);

                        // Add By Akkarapol, 03/10/2013, เพิ่มการทำงานในส่วนของการตรวจสอบค่า PD_Reserv_Qty เก่าและใหม่ ว่าถ้าค่าไม่เท่ากัน จะให้ไปแก้ไขค่า PD_Reserv_Qty โดยการนำ ค่าเก่าไปลบออกก่อน และค่อยบวกค่าใหม่เข้าไป
                        $oldReservQty = $this->stock->getOldReservQtyRelocateDetailByItemId($is_new);
                        if ($oldReservQty['Reserv_Qty'] != str_replace(",", "", $a_data[$ci_confirm_qty])) {

                            if ($check_not_err):
                                $result_Del_PDrervqty = $this->stock->reservPDReservQty($a_data[$ci_inbound_id], $oldReservQty['Reserv_Qty'], "-");
                                if ($result_Del_PDrervqty < 1):
                                    $check_not_err = FALSE;
                                    $return['critical'][]['message'] = "Can not update quantity balance.";
                                /*
                                 * LOG MSG 4
                                 */
                                endif;
                            endif;

                            if ($check_not_err):
                                $result_Pos_PDrervqty = $this->stock->reservPDReservQty($a_data[$ci_inbound_id], str_replace(",", "", $a_data[$ci_confirm_qty]), "+"); //(4)
                                if ($result_Pos_PDrervqty < 1):
                                    $check_not_err = FALSE;
                                    $return['critical'][]['message'] = "Can not update quantity balance.";
                                /*
                                 * LOG MSG 5
                                 */
                                endif;
                            endif;
                        }

                        unset($oldReservQty);
                        // END Add By Akkarapol, 03/10/2013, เพิ่มการทำงานในส่วนของการตรวจสอบค่า PD_Reserv_Qty เก่าและใหม่ ว่าถ้าค่าไม่เท่ากัน จะให้ไปแก้ไขค่า PD_Reserv_Qty โดยการนำ ค่าเก่าไปลบออกก่อน และค่อยบวกค่าใหม่เข้าไป



                        if ($check_not_err):

                            $result_orderDetail_query = $this->prod_status->updateOrderDetail($detail, $where);

                            if ($result_orderDetail_query < 1):
                                $check_not_err = FALSE;
                                $return['critical'][]['message'] = "Can not update Order detail.";
                            /*
                             * LOG MSG 6
                             */
                            endif;

                        endif;
                    } else {
                        #Add Order Detail for new Item

                        $inbound_id = $a_data[$ci_inbound_id];

                        //+++++ADD BY POR 2013-11-13 แก้ไขให้เรียกใช้ getStockDetailId รูปแบบใหม่
                        $column = array("*");
                        $where['Inbound_Id'] = $inbound_id;
                        $item_detail = $this->stock->getStockDetailId($column, $where)->result();
                        //+++++END ADD

                        if (count($item_detail) > 0) {
                            foreach ($item_detail as $item) {
                                $detail['Document_No'] = $item->Document_No;
                                $detail['Doc_Refer_Int'] = $item->Doc_Refer_Int;
                                $detail['Doc_Refer_Ext'] = $item->Doc_Refer_Ext;
                                $detail['Doc_Refer_Inv'] = $item->Doc_Refer_Inv;
                                $detail['Doc_Refer_CE'] = $item->Doc_Refer_CE;
                                $detail['Doc_Refer_BL'] = $item->Doc_Refer_BL;
                                $detail['Doc_Refer_AWB'] = $item->Doc_Refer_AWB;
                                $detail['Inbound_Item_Id'] = $item->Inbound_Id;
                                $detail['Product_Id'] = $item->Product_Id;
                                $detail['Product_Code'] = $item->Product_Code;
                                $detail['Product_License'] = $item->Product_License;
                                $detail['Product_Lot'] = $item->Product_Lot;
                                $detail['Product_Serial'] = $item->Product_Serial;
                                //$detail['Product_Mfd'] = $item->Product_Mfd;
                                //$detail['Product_Exp'] = $item->Product_Exp;
                                $detail['Receive_Date'] = $item->Receive_Date;
                                $detail['Unit_Id'] = $item->Unit_Id;

                                #add value of invoice_no if config open invoice by item
                                if($conf_inv):
                                    $detail['Invoice_Id'] = $item->Invoice_Id;
                                endif;

                                #add value of container_no if config open invoice by item
                                if($conf_cont):
                                    $detail['Cont_Id'] = $item->Cont_Id;
                                endif;

                                #add value of container_no if config open invoice by item
                                if($conf_pallet):
                                    $detail['Pallet_Id'] = $item->Pallet_Id;
                                endif;


                                $product_code = $this->prod_status->getProductCodeByProductId($detail['Product_Id']);
                                $product_code = $product_code[0]->Product_Code;
                                $setSuggestLocation = $this->suggest_location->getSuggestLocationArray('suggestLocation', $product_code, $a_data[$ci_prod_status], '1');
                                $detail['Suggest_Location_Id'] = (!empty($setSuggestLocation) ? $setSuggestLocation[0]['Location_Id'] : NULL);
                            }
                        }

                        $detail['Order_Id'] = $order_id;
                        $detail['Product_Status'] = $a_data[$ci_prod_status];
                        $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                        $detail['Reserv_Qty'] = str_replace(",", "", $a_data[$ci_confirm_qty]);
                        $detail['Old_Location_Id'] = $a_data[$ci_actual_loc]; // Add By Akkarapol, 29/10/2013, Old_Location_Id ที่จะใช้ในขั้นตอนของการ Relocation มันต้องใช้เป็น Actual Location ที่มันถูกเก็บลง Inbound สิ จะมาใช้ Old_Location_Id ได้ยังไง ในเมื่อ เราต้องการรู้ว่า ปัจจุบันมันอยู่ที่ไหน ไม่ใช่ มันมาจากที่ไหน เชอะ!!!!!
//			$detail['Product_Mfd'] = $a_data[$ci_mfd];
//			$detail['Product_Exp'] = $a_data[$ci_exp];
                        //
                        //ADD BY POR 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                        if ($this->settings['price_per_unit'] == TRUE) {
                            $detail['Price_Per_Unit'] = (float) str_replace(",", "", $a_data[$ci_price_per_unit]);
                            $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                            $detail['All_Price'] = (float) str_replace(",", "", $a_data[$ci_all_price]);
                        }
                        //END ADD

                        $order_detail[] = $detail;
                    }
                }

                #add new Order Detail
                // if ($check_not_err):
                //     if (!empty($order_detail)):
                //         #add order detail
                //         $result_order_detail = $this->relocate->addReLocationOrderDetail($order_detail);
                //         if ($result_order_detail <= 0):
                //             $check_not_err = FALSE;
                //             $return['critical'][]['message'] = "Can not add new order detail.";
                //         /*
                //          * LOG MSG 7
                //          */
                //         endif;

                //         #update pd reserv into inbound
                //         $result_PD_reserv_qty = $this->stock->reservPDReservQtyArray($order_detail);
                //         if (!$result_PD_reserv_qty):
                //             $check_not_err = FALSE;
                //             $return['critical'][]['message'] = "Can not update quantity balance.";
                //         /*
                //          * LOG MSG 8
                //          */
                //         endif;

                //         unset($result_PD_reserv_qty);

                //     endif; //end add new order detail
                // endif; //end check err before add new order detail
            }//if (!empty($prod_list))

        endif; //end update Order Detail Table


        /**
         * Delete Item in Order Detail
         */
        if ($check_not_err):

            if (is_array($prod_del_list) && !empty($prod_del_list)) {
                unset($rows);
                $item_delete = array();

                // foreach ($prod_del_list as $rows) {

                //     $a_data = explode(SEPARATOR, $rows); // Edit By Akkarapol, 21/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
                //     $item_delete[] = $a_data[$ci_item_id];  /* Item_Id for Delete in STK_T_Order_Detail  */

                //     #Update PD_Reserv_Qty in Inbound Table
                //     if (!empty($a_data[$ci_inbound_id])) {

                //         #update pd reserv into inbound
                //         $result_PD_reserv_qty = $this->stock->reservPDReservQty($a_data[$ci_inbound_id], $a_data[$ci_reserv_qty], "-");
                //         if (!$result_PD_reserv_qty):
                //             $check_not_err = FALSE;
                //             $return['critical'][]['message'] = "Can not update quantity balance.";
                //         /*
                //          * LOG MSG 9
                //          */
                //         endif;
                //     }// end update PD_Reserv_Qty in Inbound Table
                // }//foreach ($prod_del_list as $rows) {
                #query Delete Item in Order Detail
                if ($check_not_err && !empty($item_delete)):

                    $result_Del_item = $this->prod_status->removeOrderDetail($item_delete);
                    if (!$result_Del_item):
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Can not remove order detail.";
                    /*
                     * LOG MSG 10
                     */
                    endif;

                endif; // end check err before delete item in order detail table
            }// if (is_array($prod_del_list) && !empty($prod_del_list)) {

        endif; // end delete Item in Order Detail

        return $return;
    }

    public function validation_openAction_changeStatus_notMove() {
        /**
         * set Variable
         */
        $data = $this->input->post();

        $return = array();

        /**
         * chk PD_reserv Before Open
         */
        $resultCompare = $this->balance->_chkPDreservBeforeOpen($data['ci_confirm_qty'], $data['ci_inbound_id'], $data['prod_list'], SEPARATOR);
        $return = array_merge_recursive($return, $resultCompare);


        /**
         * chk change activity change status & sub_status on flow 'Change Product Status' if not change, validation will alert.
         */
        //$result_chk_status = $this->chk_activity_change_status_and_sub_status($data['ci_prod_status'], $data['ci_prod_sub_status'], $data['ci_prod_status_val'], $data['ci_prod_sub_status_val'], $data['ci_inbound_id'], $data['prod_list'], SEPARATOR);
        //$return = array_merge_recursive($return, $result_chk_status);

		
        if (!empty($return)):
            $json['status'] = "validation";
            $json['return_val'] = $return;
        else:
            $json['status'] = 'pass';
            $json['return_val'] = '';
        endif;
			
        echo json_encode($json);
    }

    function openAction_changeStatus_notMove(){
    
        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        $conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        # Parameter Get session data
        $renter_id = $this->session->userdata('renter_id');
        $owner_id = $this->session->userdata('owner_id');
        $user_id = $this->session->userdata('user_id');

        # Parameter Form
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $document_no = $this->input->post("document_no");
        $est_action_date = convert_date_format($this->input->post("est_action_date"));
        $remark = $this->input->post("remark");
        $assigned_id = $this->input->post("assigned_id");
        $is_urgent = $this->input->post("is_urgent");

        $document_ref = $this->input->post("document_ref"); //ADD BY POR 2014-11-16  use customs report รายงานการนำำของออกเพื่อการอื่นเป็นการชั่วคราวและการนำกลับ

        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Parameter Order Detail
        $prod_list = $this->input->post("prod_list");
        $prod_del_list = $this->input->post("prod_del_list");


        # Parameter Index Datatable
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_lot = $this->input->post("ci_lot");
        $ci_serial = $this->input->post("ci_serial");
        $ci_mfd = $this->input->post("ci_mfd");
        $ci_exp = $this->input->post("ci_exp");
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_confirm_qty = $this->input->post("ci_confirm_qty");
        $ci_remark = $this->input->post("ci_remark");
        $ci_prod_id = $this->input->post("ci_prod_id");
        $ci_prod_status = $this->input->post("ci_prod_status");
        $ci_unit_id = $this->input->post("ci_unit_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");
        $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        $ci_actual_loc = $this->input->post("ci_actual_loc");
        $ci_old_loc = $this->input->post("ci_old_loc");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_dp_type_pallet = $this->input->post("ci_dp_type_pallet");

        # Parameter price per uint
        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }

        // Shot Process To Change Status Not Move
        $passdata_post = $this->input->post();
        // p($passdata_post);exit;
        $pass_data_action_type = 'Open Change Product Status To Complete Not Move';
        $set_up_next_stage = 1;
        if($passdata_post['process_id'] == 8 && $passdata_post['process_type'] ==  'CH-STATUS' && $passdata_post['action_type'] ==  $pass_data_action_type && $passdata_post['next_state'] == $set_up_next_stage){
            $retrun_json = $this->changeProductStatusToCompleteNotMove($passdata_post);
            // p('final');
            // echo json_encode($retrun_json);
            echo $retrun_json;

        }

    }
}