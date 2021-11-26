<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Pending extends CI_Controller {

    public $settings;

    public function __construct() {
        parent::__construct();
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));

        $this->load->model("product_model", "product");
        $this->settings = native_session::retrieve();
        $isUserLogin = $this->session->userdata("user_id");
        if ($isUserLogin == "") {
            echo "<script>alert('Your session was expired. Please Log-in!')</script>";
            echo "<script>window.location.replace('" . site_url() . "');</script>";
        }
    }

    public function index() {
        $this->openForm($process_id = 7);
    }

    #Open Form

    function openForm($process_id, $present_state = 0) {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
//		$data_form = $this->workflow->openWorkflowForm($process_id,$present_state); // comment by kik เขียนโค้ดซ้ำกัน เลย comment ออกให้บรรทัดนี้

        $this->load->model("workflow_model", "flow");
        $this->load->model("pending_model", "pending");

        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state); // โค้ดที่เขียนซ้ำกัน
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowPendingList"); // Button Permission. Add by Ton! 20140131
        $query = $this->pending->getPendingOrder();
        $order_list = $query->result();
        $parameter = array();
        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['process_id'] = $process_id;
        $parameter['present_state'] = $present_state;
        $parameter['renter_id'] = $this->config->item('renter_id');
        $parameter['consignee_id'] = $this->config->item('owner_id');
        $parameter['owner_id'] = $this->config->item('owner_id');
        $parameter['data_form'] = (array) $data_form;
        $parameter['order_list'] = $order_list;

        # LOAD FORM
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);
//        p($query);
        # PUT FORM IN TEMPLATE WORKFLOW  
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form

            // Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target=""></i>'//form_pending Edit by Ton! 20131025
            // END Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'button_action' => $data_form->str_buttun
        ));
    }

    #Open Form With Data

    function openActionForm() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $flow_id = $this->input->post("id");
        $this->load->model("workflow_model", "flow");
        $this->load->model("contact_model", "contact");
        $this->load->model("company_model", "company");
        $this->load->model("system_management_model", "sys");
        $this->load->model("stock_model", "stock");
        $this->load->model("pending_model", "pending");
        $this->load->model("re_location_model", "relocate");

        #Retrive Data from Table
        $flow_detail = $this->pending->getPendingFlow($flow_id);
        $parameter['process_id'] = $flow_detail[0]->Process_Id;
        $parameter['present_state'] = $flow_detail[0]->Present_State;
        $parameter['process_type'] = $flow_detail[0]->Process_Type;
        $parameter['order_id'] = $flow_detail[0]->Order_Id;
        $parameter['assigned_id'] = $flow_detail[0]->Assigned_Id;


        $pending_detail = $this->pending->getPendingDetail($parameter['order_id']);  // get data pending order
        $parameter['pending_detail'] = $pending_detail;
        foreach ($pending_detail as $rows) {
            $parameter['doc_relocate'] = $rows->Doc_Relocate;
            $parameter['document_no'] = $rows->Document_No;
            $parameter['doc_refer_int'] = $rows->Doc_Refer_Int;
            $parameter['doc_refer_ext'] = $rows->Doc_Refer_Ext;
            $parameter['doc_refer_inv'] = $rows->Doc_Refer_Inv;
            $parameter['doc_refer_ce'] = $rows->Doc_Refer_CE;
            $parameter['doc_refer_bl'] = $rows->Doc_Refer_BL;
            $parameter['doc_refer_awb'] = $rows->Doc_Refer_AWB;
            $parameter['receive_date'] = $rows->Receive_Date;
            $parameter['is_urgent'] = $rows->Is_urgent;
        }

        $order_detail = $this->pending->getOrderByDocNo($parameter['document_no']);  // get data receive order
        $getRelocationOrder = $this->relocate->getRelocationOrder($flow_id);
        $remark = $getRelocationOrder[0]->Remark;

        foreach ($order_detail as $rows) {
            $parameter['owner_id'] = $rows->Owner_Id;
            $parameter['receive_type'] = $rows->Doc_Type;
            $parameter['renter_id'] = $rows->Renter_Id;
            $parameter['vendor_id'] = $rows->Vendor_Id;
            $parameter['driver_name'] = $rows->Vendor_Driver_Name;
            $parameter['car_no'] = $rows->Vendor_Car_No;
            $parameter['shipper_id'] = $rows->Source_Id;
            $parameter['consignee_id'] = $rows->Destination_Id;
            $parameter['remark'] = $remark;
            $parameter['is_pending'] = $rows->Is_Pending;
        }


        #Get Renter [Company Renter] list		
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY");
        $parameter['renter_list'] = $renter_list;

        #Get Shipper[Company Supplier] list
        $q_shipper = $this->company->getSupplierAll();
        $r_shipper = $q_shipper->result();
        $shipper_list = genOptionDropdown($r_shipper, "COMPANY");
        $parameter['shipper_list'] = $shipper_list;

        #Get Consignee[Company Owner]  list
        $q_consignee = $this->company->getOwnerAll();
        $r_consignee = $q_consignee->result();
        $consignee_list = genOptionDropdown($r_consignee, "COMPANY");
        $parameter['consignee_list'] = $consignee_list;

        #Get Receive Type list			
        $r_receive_type = $this->sys->getReceiveType();
        $receive_list = genOptionDropdown($r_receive_type, "SYS");
        $parameter['receive_list'] = $receive_list;

        #Get Vendor [Company Vendor] list		
        $q_vendor = $this->company->getVendorAll();
        $r_vendoe = $q_vendor->result();
        $vendor_list = genOptionDropdown($r_vendoe, "COMPANY");
        $parameter['vendor_list'] = $vendor_list;

        #Get Assined [Worker] list		
//		$q_assign = $this->contact->getWorkerAll();  // Comment By Akkarapol,17/09/2013,
        $q_assign = $this->contact->getWorkerAllWithUserLogin();  // Add By Akkarapol, 17/09/2013, เปลี่ยนมาใช้แบบนี้เพราะให้ตารางหลักเป็น UserLogin จะได้เอาค่าไปใช้ต่อได้ง่ายๆ
        $r_assign = $q_assign->result();
//		$assign_list = genOptionDropdown($r_assign,"CONTACT"); // Comment By Akkarapol,17/09/2013,
        $assign_list = genOptionDropdown($r_assign, "CONTACTWITHUSERLOGIN"); // Add By Akkarapol, 17/09/2013, เปลี่ยนมาใช้เพราะต้องการให้ค่า Index ที่ได้ออกมาเป็น UserLogin_Id แทนที่จะเป็น Contact_Id เพราะเห็นว่า อย่างไรก็เอา UserLogin_Id ไปเก็บเป็น Detail อยู่แล้ว จะได้ไม่ผิดที่เอา UserLogin_Id มาใช้บ้าง Contact_Id มาใช้บ้าง เพื่อให้เป็น มาตรฐานเดียวกัน
        $parameter['assign_list'] = $assign_list;

//        $data_form = $this->workflow->openWorkflowForm($parameter['process_id'], $parameter['present_state']);
        $data_form = $this->workflow->openWorkflowForm($parameter['process_id'], $parameter['present_state'], $this->session->userdata('user_id'), "flow/flowPendingList"); // Button Permission. Add by Ton! 20140131

        $show_column = array(
            _lang('no'),
            _lang('product_code'),
            _lang('product_name'),
            _lang('product_status'),
            _lang('lot'),
            _lang('serial'),
            _lang('product_mfd'),
            _lang('product_exp'),
            _lang('receive_qty'),
            _lang('unit'),
            _lang('remark'),
            "Item_Id");

        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['flow_id'] = $flow_id;
        $parameter['show_column'] = $show_column;
        $parameter['data_form'] = (array) $data_form;

        if ($parameter['present_state'] >= 3):
            $data_form->str_buttun .= '<input class="button dark_blue" type="button" onclick="exportFile(\'PDF\')" value="PDF">'; // Add By Akkarapol, 17/10/2013, เพิ่มปุ่ม PRINT สำหรับ Generate PDF เพื่อออกใบ Relocation Job
        endif;

        //ADD BY POR 2014-01-15 เพิ่มให้ตรวจสอบ config ว่าถ้ามี price_per_unit เป็น TRUE จะให้ระบุราคาต่อหน่วยด้วย
        $priceperunit = '';
        $unitofprice = '';
        if ($this->settings['price_per_unit'] == TRUE):
            $priceperunit = " ,{
                    sSortDataType: \"dom-text\",
                    sType: \"numeric\",
                    type: 'text',
                    onblur: \"submit\",
                    event: 'click keyup',
                    loadfirst: true,
                    cssclass: \"required number\",
                    fnOnCellUpdated: function(sStatus, sValue, settings) {  
                        calculate_qty();
                    }
                }";



            $unitofprice = ",
                 {
                    loadurl: '" . site_url() . '/pre_receive/getPriceUnit' . "',
                    loadtype: 'POST',
                    type: 'select',
                    event: 'click', 
                    onblur: 'submit',
                    sUpdateURL: function(value, settings) {
                        var oTable = $('#showProductTable').dataTable();
                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                        oTable.fnUpdate(value, rowIndex, ci_unit_price_id);
                        return value;
                    }
                }
                ";
        else:
            $priceperunit = ', null';
            $unitofprice = ',null';
        endif;
        //END ADD
//        p($this->config->item('owner_id'));
//        p($this->config->item('renter_id'));
        #ADD BY POR 2014-01-15 
        $parameter['statusprice'] = $this->settings['price_per_unit'];

        # LOAD FORM
//	p($data_form->form_name);
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);
        # PUT FORM IN TEMPLATE WORKFLOW 
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'priceperunit' => $priceperunit //ADD BY POR 2014-01-15 ให้ส่งตัวแปรเกี่ยวกับการแสดงราคาต่อหน่วย
            , 'unitofprice' => $unitofprice //ADD BY POR 2014-01-15 ให้ส่งตัวแปรเกี่ยวกับการแสดงราคาต่อหน่วย
            // Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_pending"></i>'// Edit by Ton! 20131025
            // END Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'button_action' => $data_form->str_buttun
        ));
    }

    #Action Open Process 

    function openAction() {
        $pending_doc_list = $this->input->post("chkBoxVal");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->input->post("user_id");

        $this->load->model("pending_model", "pending");
        $this->load->model("stock_model", "stock");
        $this->load->model("re_location_model", "relocate");
        if ((!is_array($pending_doc_list)) || (count($pending_doc_list) <= 0)) {
            $json['status'] = "E001";
            $json['error_msg'] = "";
            echo json_encode($json);
        }
        foreach ($pending_doc_list as $doc_no) {
            # generate Pending Document No.
            $prefix = "PE";

//              --#ISSUE 2158
//              --#DATE:2012-08-29
//              --#BY:KIK
//              --#ปัญหา:ของเก่าไม่มีการส่งค่า flow_id เก่าไปเพื่อใส่ใน parent_id ของ flow ใหม่ ทำให้อ้างอิงค่ากันไม่ได้
//              --#สาเหตุ:ของเก่าไม่มีการส่งค่า flow_id เก่าไปเพื่อใส่ใน parent_id ของ flow ใหม่ ทำให้อ้างอิงค่ากันไม่ได้
//              --#วิธีการแก้:ส่งค่า parameter เป็น  flow_id เก่า ไปให้ยังฟังก์ชั่น addNewWorkflow เพื่อเอาไปเก็บไว้ใน ฟิวด์ parent_id 
//              -- START Old Comment Code #ISSUE 2158
//            
//			$data['Document_No']		= createDocumentNo($prefix);
//			list($flow_id,$action_id)	= $this->workflow->addNewWorkflow($process_id ,$present_state ,$action_type ,$next_state ,$user_id ,$data);
//			$query = $this->pending->getPendingDocument(array($doc_no));
//			$doc_detail = $query->result();   
//			          
//              -- END Old Comment Code #ISSUE 2158
//              -- START New Code #ISSUE 2158

            $query = $this->pending->getPendingDocument(array($doc_no));
            $doc_detail = $query->result();
//            $data['Document_No'] = createDocumentNo($prefix);
            $data['Document_No'] = create_document_no_by_type($prefix); // Add by Ton! 20140428
//            list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $user_id, $data, @$doc_detail[0]->Flow_Id); // ส่ง parameter ของ flow_id ไปให้เพื่อใส่ในช่อง parent_id ของ flow ใหม่
            list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data, @$doc_detail[0]->Flow_Id); //Edit by Ton! 20131021
            //             -- END New Code #ISSUE 2158



            $order_id = "";
            $order_detail_remark = $this->flow->getOrderDetailByDocumentNo($pending_doc_list[0]);
            $remark = $order_detail_remark[0]->Remark;
            $is_urgent = $order_detail_remark[0]->Is_urgent;
            foreach ($doc_detail as $rows) {
                if ($order_id == "") {

                    $order = array(
                        'Doc_Relocate' => $data['Document_No']
                        , 'Flow_Id' => $flow_id
                        , 'Doc_Type' => $process_type
                        , 'Process_Type' => $process_type
                        , 'Estimate_Action_Date' => date("Y-m-d")
                        , 'Owner_Id' => $rows->Owner_Id
                        , 'Renter_Id' => $rows->Renter_Id
                        , 'Create_By' => $user_id
                        , 'Create_Date' => date("Y-m-d")
                        , 'Remark' => $remark
                        , 'Is_urgent' => $is_urgent
                    );
                    $order_id = $this->relocate->addReLocationOrder($order);
                }

                unset($detail);
                $detail['Order_Id'] = $order_id;
                $detail['Document_No'] = $rows->Document_No;
                $detail['Doc_Refer_Int'] = $rows->Doc_Refer_Int;
                $detail['Doc_Refer_Ext'] = $rows->Doc_Refer_Ext;
                $detail['Doc_Refer_Inv'] = $rows->Doc_Refer_Inv;
                $detail['Doc_Refer_CE'] = $rows->Doc_Refer_CE;
                $detail['Doc_Refer_BL'] = $rows->Doc_Refer_BL;
                $detail['Doc_Refer_AWB'] = $rows->Doc_Refer_AWB;
                $detail['Inbound_Item_Id'] = $rows->Inbound_Id;
                $detail['Product_Id'] = $rows->Product_Id;
                $detail['Product_Code'] = $rows->Product_Code;
//				$detail['Product_Status']	= $rows->Product_Status;
                $detail['Product_Status'] = 'NORMAL'; // Ak, เปลี่ยนเพราะ ลำดับการทำงานมันต้อง เปลี่ยนจาก PENDING แล้วเซ็ตให้เป็น NORMAL ก่อน เพราะตอนนี้มันจะถูก relocate แล้ว
                $detail['Product_Sub_Status'] = $rows->Product_Sub_Status;

                $setSuggestLocation = $this->suggest_location->getSuggestLocationArray('suggestLocation', $rows->Product_Code, $detail['Product_Status'], '1','','','',$detail['Inbound_Item_Id'],1); //EDIT BY POR 2014-06-04 แก้ไขให้เพิ่มตัวแปรที่ต้องการส่งไป
                $setSuggestLocation = (!empty($setSuggestLocation) ? $setSuggestLocation[0]['Location_Id'] : NULL);

                $detail['Suggest_Location_Id'] = $setSuggestLocation;   # Data will be in Re-Location 
                # $detail['Suggest_Location_Id']= $rows->Suggest_Location_Id;   # Data will be in Re-Location 
                # $detail['Actual_Location_Id'] = $rows->Actual_Location_Id;	# Data will be in Re-Location  
                $detail['Old_Location_Id'] = $rows->Actual_Location_Id;  # Crrent Location from Inbound to Old Location (Keep Log)
                $detail['Pallet_Id'] = $rows->Pallet_Id;
                $detail['Product_License'] = $rows->Product_License;
                $detail['Product_Lot'] = $rows->Product_Lot;
                $detail['Product_Serial'] = $rows->Product_Serial;
                $detail['Product_Mfd'] = $rows->Product_Mfd;
                $detail['Product_Exp'] = $rows->Product_Exp;
                $detail['Receive_Date'] = $rows->Receive_Date;
                $detail['Reserv_Qty'] = ($rows->Receive_Qty - $rows->Dispatch_Qty);
                $detail['Unit_Id'] = $rows->Unit_Id;
                //ADD BY POR 2014-01-09 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                if ($this->settings['price_per_unit'] == TRUE):
                    $detail['Price_Per_Unit'] = $rows->Price_Per_Unit;
                    $detail['Unit_Price_Id'] = $rows->Unit_Price_Id;
                    $detail['All_Price'] = $rows->All_Price;
                endif;
                //END ADD

                $order_detail[] = $detail;

                $this->pending->updateUnlockPending($detail['Document_No']); // Add By Akkarapol, 04/09/2013, เพิ่มเพื่อทำการ Update ข้อมูลฟิลด์ Unlock_Pending_Date เข้าไปใน Inbound ตัวที่เป็น Pending เลยจะได้ใช้ในการเรียก report ได้
            }
            if ($order_id != "") {
                $this->relocate->addReLocationOrderDetail($order_detail);
            }
        }
        $json['status'] = "C001";
        $json['error_msg'] = "";
        echo json_encode($json);
    }

    #Action Confirm Process

    function confirmAction() {

        /**
         * Set Variable
         */
        $check_not_err = TRUE;
        $return = array();


        /**
         * ================== Start Transaction =========================
         */
        $this->transaction_db->transaction_start();


        /**
         * update Process
         */
        if ($check_not_err):
            $respond = $this->_updateProcess($this->input->post());
            if (!empty($respond['critical'])) :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update Process.";
                $return = array_merge_recursive($return, $respond);
            endif;
        endif;


        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):
            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

            $set_return['message'] = "Confirm Pending Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Confirm Pending Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
        endif;

        echo json_encode($json);
    }

    function approveAction() {

        /**
         * set Variable
         */
        $check_not_err = TRUE;
        $return = array();


        /**
         * ================== Start Transaction =========================
         */
        $this->transaction_db->transaction_start();


        /**
         * update Process
         */
        if ($check_not_err):
            $respond = $this->_updateProcess($this->input->post());
            if (!empty($respond['critical'])) :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update Process.";
                $return = array_merge_recursive($return, $respond);
            endif;
        endif;


        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):
            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

            $set_return['message'] = "Approve Pending Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Approve Pending Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
        endif;

        echo json_encode($json);
    }

    function _updateProcess() {

        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;

        $flow_id = $this->input->post("flow_id");
        $order_id = $this->input->post("order_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->input->post("user_id");

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
        $est_receive_date = $this->input->post("est_receive_date");
        $receive_type = $this->input->post("receive_type");
        $is_pending = $this->input->post("is_pending");
        $is_repackage = $this->input->post("is_repackage");
        $is_urgent = $this->input->post("is_urgent");
        $remark = $this->input->post("remark");

        $vendor_id = $this->input->post("vendor_id");
        $driver_name = $this->input->post("driver_name");
        $car_no = $this->input->post("car_no");
        $receive_date = $this->input->post("receive_date");

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

        //ADD BY POR 2014-01-15 เพิ่มเกี่ยวกับราคา
        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }
        //END ADD
        //add Urgent for ISSUE 3312 : by kik : 20140120
        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Load Model
        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", "flow");
        $this->load->model("product_model", "product");
        $this->load->model("pending_model", "pending");

        # Update Order and Order Detail
        $flow_detail = $this->flow->getFlowDetail($flow_id);

        $data['Document_No'] = $document_no;


        /**
         * update Workflow
         */
        if ($check_not_err):
            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021
            if (empty($action_id) || $action_id == ''):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update Workflow.";
            endif;
        endif;


        /**
         * update Pending Order
         */
        if ($check_not_err):
            $order = array(
                'Modified_By' => $user_id
                ,'Actual_Action_Date'	=> date("Y-m-d H:i:s")
                , 'Modified_Date' => date("Y-m-d H:i:s") // Edit By Akkarapol, 04/09/2013, เปลี่ยนจาก date("Y-m-d H-i-s") เป็น date("Y-m-d H:i:s") เพราะไม่งั้นมันจะเซฟเวลาเข้า ฐานข้อมูล ไม่ได้ เพราะ รูปแบบไม่ตรงกันกับฐานข้อมูล
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Is_urgent' => $is_urgent
            );
            $where['Flow_Id'] = $flow_id;
            $result_updatePendingOrder = $this->pending->updatePendingOrder($order, $where);
            if ($result_updatePendingOrder <= 0):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update Pending Order.";
            endif;
        endif;


        /**
         * update Pending Order Detail
         */
        if ($check_not_err):
            $order_detail = array();
            if (!empty($prod_list)) :
                foreach ($prod_list as $rows) :
                    unset($where);
                    $a_data = explode(SEPARATOR, $rows);
                    $detail = array();
                    $detail['Product_Id'] = $a_data[$ci_prod_id];
                    $detail['Product_Status'] = $a_data[$ci_prod_status];
                    $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                    $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                    $where['Item_Id'] = $a_data[$ci_item_id];

                    $product_code = $this->pending->getProductCodeByProductId($detail['Product_Id']);
                    $product_code = $product_code[0]->Product_Code;
                    $qty = str_replace(",", "", $a_data[$ci_confirm_qty]);
                    $setSuggestLocation = $this->suggest_location->getSuggestLocationArray('suggestLocation', $product_code, $a_data[$ci_prod_status], '1', NULL, $a_data[$ci_prod_sub_status], $qty, $a_data[$ci_item_id],2);  //EDIT BY POR แก้ไขให้ส่งตัวแปรเพิ่มเติม $id,type_item
                    $detail['Suggest_Location_Id'] = (!empty($setSuggestLocation) ? $setSuggestLocation[0]['Location_Id'] : NULL);

                    $detail['Picking_By'] = $this->session->userdata('user_id');
                    $detail['Picking_Date'] = date('m/d/Y H:i:s');

                    //ADD BY POR 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                    if ($this->settings['price_per_unit'] == TRUE) :
                        $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]);
                        $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                        $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]);
                    endif;
                    //END ADD


                    /**
                     * update Pending Order Detail
                     */
                    $result_updatePendingDetail = $this->pending->updatePendingDetail($detail, $where);
                    if ($result_updatePendingDetail <= 0):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not update Pending Order Detail.";
                    endif;
                endforeach;
            endif;
        endif;

        //logOrderDetail($order_id, 'pending', $action_id, $action_type);   //COMMENT BY POR 2014-07-07
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
         * Set Variable
         */
        $check_not_err = TRUE;
        $return = array();

        $flow_id = $this->input->post("flow_id");
        $order_id = $this->input->post("order_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->input->post("user_id");

        # Parameter of document number
        $document_no = $this->input->post("document_no");
        $doc_refer_int = $this->input->post("doc_refer_int");
        $doc_refer_ext = $this->input->post("doc_refer_ext");
        $doc_refer_inv = $this->input->post("doc_refer_inv");
        $doc_refer_ce = $this->input->post("doc_refer_ce");
        $doc_refer_bl = $this->input->post("doc_refer_bl");
        $is_urgent = $this->input->post("is_urgent");

        # Parameter Order Document
        $owner_id = $this->input->post("owner_id");
        $renter_id = $this->input->post("renter_id");
        $shipper_id = $this->input->post("shipper_id");
        $consignee_id = $this->input->post("consignee_id");
        $receive_type = $this->input->post("receive_type");
        $remark = $this->input->post("remark");
        $receive_date = $this->input->post("receive_date");
        $assigned_id = $this->input->post("assigned_id");

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

        //ADD BY POR 2014-01-16 เพิ่มเกี่ยวกับราคา
        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }

        //add Urgent for ISSUE 3312 : by kik : 20140120
        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Load Model
        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", "flow");
        $this->load->model("product_model", "product");
        $this->load->model("pending_model", "pending");


        /**
         * ================== Start Transaction =========================
         */
        $this->transaction_db->transaction_start();


        /**
         * Get Flow Detail
         */
        if ($check_not_err):
            $flow_detail = $this->flow->getFlowDetail($flow_id);
            if (!empty($flow_detail)) :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not Get Flow Detail.";
            endif;
        endif;


        /**
         * update Workflow
         */
        if ($check_not_err):
            $data['Document_No'] = $document_no;
            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021
            if (empty($action_id) || $action_id == ''):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update Workflow.";
            endif;
        endif;


        /**
         * update Pending Order
         */
        if ($check_not_err):
            $order = array(
                'Modified_By' => $user_id
                , 'Modified_Date' => date("Y-m-d H:i:s")
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Assigned_Id' => $assigned_id
                , 'Is_urgent' => $is_urgent
            );

            $where['Flow_Id'] = $flow_id;
            $result_updatePendingOrder = $this->pending->updatePendingOrder($order, $where);
            if ($result_updatePendingOrder <= 0):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update Pending Order.";
            endif;
        endif;


        /**
         * update Pending Order Detail
         */
        if ($check_not_err):
            $order_detail = array();
            if (!empty($prod_list)) :
                foreach ($prod_list as $rows) :
                    unset($where);
                    $a_data = explode(SEPARATOR, $rows);
                    $detail = array();
                    $detail['Suggest_Location_Id'] = $a_data[$ci_suggest_loc];
                    $detail['Confirm_Qty'] = $a_data[$ci_confirm_qty];
                    $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                    //ADD BY POR 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                    if ($this->settings['price_per_unit'] == TRUE) :
                        $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]); //+++++ADD BY POR 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                        $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                        $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]); //+++++ADD BY POR 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                    endif;
                    //END ADD
                    $where['Item_Id'] = $a_data[$ci_item_id];
                    $where['Product_Id'] = $a_data[$ci_prod_id];
                    $result_updatePendingDetail = $this->pending->updatePendingDetail($detail, $where);
                    if ($result_updatePendingDetail <= 0):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not update Pending Order Detail.";
                        break;
                    endif;
                endforeach;
            endif;
        endif;


        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):
            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

            $set_return['message'] = "Confirm Pending Putaway Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Confirm Pending Putaway Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
        endif;

//        logOrderDetail($order_id,'pending',$action_id,$action_type);

        echo json_encode($json);
    }

    function relocateApprove() {

        /**
         * set Variable
         */
        $check_not_err = TRUE;
        $return = array();

        $flow_id = $this->input->post("flow_id");
        $order_id = $this->input->post("order_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->input->post("user_id");

        # Parameter of document number
        $document_no = $this->input->post("document_no");
        $doc_refer_int = $this->input->post("doc_refer_int");
        $doc_refer_ext = $this->input->post("doc_refer_ext");
        $doc_refer_inv = $this->input->post("doc_refer_inv");
        $doc_refer_ce = $this->input->post("doc_refer_ce");
        $doc_refer_bl = $this->input->post("doc_refer_bl");
        $is_urgent = $this->input->post("is_urgent");

        # Parameter Order Document
        $owner_id = $this->input->post("owner_id");
        $renter_id = $this->input->post("renter_id");
        $shipper_id = $this->input->post("shipper_id");
        $consignee_id = $this->input->post("consignee_id");
        $receive_type = $this->input->post("receive_type");
        $remark = $this->input->post("remark");
        $receive_date = $this->input->post("receive_date");
//		$assigned_id		= $this->input->post("assigned_id");
        //Add by Ton! 20130815
        $this->load->model("contact_model", "contact");
        $assigned_id = NULL;
        $q_User = $this->contact->getUserLogin($this->input->post("assigned_id"));
        $r_User = $q_User->result();
        if (count($r_User) > 0) {
            foreach ($r_User as $v_User) {
                $assigned_id = $v_User->UserLogin_Id;
            }
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
        //ADD BY POR 2014-01-16 เพิ่มเกี่ยวกับราคา
        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }

        //add Urgent for ISSUE 3312 : by kik : 20140120
        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Load Model
        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", "flow");
        $this->load->model("product_model", "product");
        $this->load->model("pending_model", "pending");


        /**
         * ================== Start Transaction =========================
         */
        $this->transaction_db->transaction_start();


        /**
         * Get Flow Detail
         */
        if ($check_not_err):
            $flow_detail = $this->flow->getFlowDetail($flow_id);
            if (!empty($flow_detail)) :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not Get Flow Detail.";
            endif;
        endif;


        /**
         * update Workflow
         */
        if ($check_not_err):
            $data['Document_No'] = $document_no;
            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021
            if (empty($action_id) || $action_id == '') :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update Workflow.";
            endif;
        endif;


        /**
         * update Pending Order
         */
        if ($check_not_err):
            $order = array(
                'Modified_By' => $user_id
//			,'Modified_Date'	=> date("Y-m-d H-i-s")
                , 'Modified_Date' => date("Y-m-d H:i:s") // Edit By Akkarapol, 04/09/2013, เปลี่ยนจาก date("Y-m-d H-i-s") เป็น date("Y-m-d H:i:s") เพราะไม่งั้นมันจะเซฟเวลาเข้า ฐานข้อมูล ไม่ได้ เพราะ รูปแบบไม่ตรงกันกับฐานข้อมูล
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Assigned_Id' => $assigned_id
                , 'Is_urgent' => $is_urgent
            );
            $where['Flow_Id'] = $flow_id;

            $result_updatePendingOrder = $this->pending->updatePendingOrder($order, $where);
            if ($result_updatePendingOrder <= 0) :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update Pending Order.";
            endif;
        endif;


        /**
         * update Pending Order Detail
         */
        if ($check_not_err):
            $order_detail = array();
            if (!empty($prod_list)) :
                foreach ($prod_list as $rows) :
                    unset($where);
                    $a_data = explode(SEPARATOR, $rows);
                    $detail = array();
                    $detail['Suggest_Location_Id'] = $a_data[$ci_suggest_loc];
                    $detail['Actual_Location_Id'] = $a_data[$ci_actual_loc];
                    $detail['Confirm_Qty'] = str_replace(",", "", $a_data[$ci_confirm_qty]); //+++++ADD BY POR 2013-11-29 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                    $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                    $detail['Putaway_By'] = $assigned_id;
                    $detail['Putaway_Date'] = date("Y-m-d H:i:s");
                    //ADD BY POR 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                    if ($this->settings['price_per_unit'] == TRUE) :
                        $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]); //+++++ADD BY POR 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                        $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                        $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]); //+++++ADD BY POR 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                    endif;
                    //END ADD
                    $where['Item_Id'] = $a_data[$ci_item_id];
                    $where['Product_Id'] = $a_data[$ci_prod_id];


                    $detail['Putaway_By'] = $this->session->userdata('user_id');
                    $detail['Putaway_Date'] = date('m/d/Y H:i:s');

                    $result_updatePendingDetail = $this->pending->updatePendingDetail($detail, $where);
                    if ($result_updatePendingDetail <= 0) :
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not update Pending Order Detail.";
                        $return = array_merge_recursive($return, $respond);
                    endif;
                endforeach;
            endif;
        endif;


        /**
         * move Location
         */
        if ($check_not_err):
            $result_moveLocation = $this->stock_lib->moveLocation($order_id);
            if (!$result_moveLocation) :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not move Location.";
            endif;
        endif;


        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):
            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

            $set_return['message'] = "Approve Pending Putaway Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Approve Pending Putaway Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
        endif;

        echo json_encode($json);



//		$this->pending->updateUnlockPending($document_no); // Comment By Akkarapol, 04/09/2013, คอมเม้นต์ทิ้งเพราะเปลี่ยนขั้นตอนแล้วโดย เอา Unlock_Pending_Date จาก Inbound เก่า มาใส่ในฟังก์ชั่น moveLocation เลย เพราะได้ทำการเอาข้อมูล Unlock_Pending_Date  ใส่เข้าไปใน Inbound เก่าแล้วตั้งแต่ตอนที่ Unlock Pending ออกมา
        //logOrderDetail($order_id,'pending',$action_id,$action_type);
//        $json['status'] = "C001";
//        $json['error_msg'] = "";
//        echo json_encode($json);
    }

}
