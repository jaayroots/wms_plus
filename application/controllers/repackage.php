<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Repackage extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));
        $this->load->model("product_model", "product");
        $isUserLogin = $this->session->userdata("user_id");
        if ($isUserLogin == "") {
            echo "<script>alert('Your session was expired. Please Log-in!')</script>";
            echo "<script>window.location.replace('" . site_url() . "');</script>";
        }
    }

    public function index() {
        $this->openForm($process_id = 12);
    }

    #Open Form

    function openForm($process_id, $present_state = 0) {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $this->load->model("workflow_model", "flow");
        $this->load->model("repackage_model", "repacakge");

//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowRepackageList"); // Button Permission. Add by Ton! 20140131|
        $query = $this->repacakge->getRepackageOrder();
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
        # PUT FORM IN TEMPLATE WORKFLOW  
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form

            // Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_receive"></i>'
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
        $this->load->model("repackage_model", "repackage");
        $this->load->model("re_location_model", "relocate");

        #Retrive Data from Table
        $flow_detail = $this->repackage->getRepackageFlow($flow_id);
        $parameter['process_id'] = $flow_detail[0]->Process_Id;
        $parameter['present_state'] = $flow_detail[0]->Present_State;
        $parameter['process_type'] = $flow_detail[0]->Process_Type;
        $parameter['order_id'] = $flow_detail[0]->Order_Id;
        $parameter['assigned_id'] = $flow_detail[0]->Assigned_Id;

        $pending_detail = $this->repackage->getRepackageDetail($parameter['order_id']);  // get data pending order
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
        }

        $order_detail = $this->repackage->getOrderByDocNo($parameter['document_no']);  // get data receive order
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
//        $renter_list = genOptionDropdown($r_renter, "COMPANY");
        $renter_list = genOptionDropdown($r_renter, "COMPANY", TRUE, FALSE); // Edit by kik! 20131107
        $parameter['renter_list'] = $renter_list;

        #Get Shipper[Company Supplier] list
        $q_shipper = $this->company->getSupplierAll();
        $r_shipper = $q_shipper->result();
//        $shipper_list = genOptionDropdown($r_shipper, "COMPANY");
        $shipper_list = genOptionDropdown($r_shipper, "COMPANY", TRUE, FALSE); // Edit by kik! 20131107
        $parameter['shipper_list'] = $shipper_list;

        #Get Consignee[Company Owner]  list
        $q_consignee = $this->company->getOwnerAll();
        $r_consignee = $q_consignee->result();
//        $consignee_list = genOptionDropdown($r_consignee, "COMPANY");
        $consignee_list = genOptionDropdown($r_consignee, "COMPANY", TRUE, FALSE); // Edit by kik! 20131107
        $parameter['consignee_list'] = $consignee_list;

        #Get Receive Type list			
        $r_receive_type = $this->sys->getReceiveType();
//        $receive_list = genOptionDropdown($r_receive_type, "SYS");
        $receive_list = genOptionDropdown($r_receive_type, "SYS", TRUE, TRUE); // Edit by kik! 20131107
        $parameter['receive_list'] = $receive_list;

        #Get Vendor [Company Vendor] list		
        $q_vendor = $this->company->getVendorAll();
        $r_vendoe = $q_vendor->result();
//        $vendor_list = genOptionDropdown($r_vendoe, "COMPANY");
        $vendor_list = genOptionDropdown($r_vendoe, "COMPANY", TRUE, FALSE); // Edit by kik! 20131107
        $parameter['vendor_list'] = $vendor_list;

        #Get Assined [Worker] list		
        $q_assign = $this->contact->getWorkerAll();
        $r_assign = $q_assign->result();
        $assign_list = genOptionDropdown($r_assign, "CONTACT");
        $parameter['assign_list'] = $assign_list;


//        $data_form = $this->workflow->openWorkflowForm($parameter['process_id'], $parameter['present_state']);
        $data_form = $this->workflow->openWorkflowForm($parameter['process_id'], $parameter['present_state'], $this->session->userdata('user_id'), "flow/flowRepackageList"); // Button Permission. Add by Ton! 20140131|

        $show_column = array(
            _lang('no')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('product_status')
            , _lang('lot')
            , _lang('serial')
            , _lang('product_mfd')
            , _lang('product_exp')
            , _lang('receive_qty')
            , _lang('unit')
            , _lang('remark')
            , "Item_Id"
            );

        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['flow_id'] = $flow_id;
        $parameter['show_column'] = $show_column;
        $parameter['data_form'] = (array) $data_form;

        # LOAD FORM
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);
        # PUT FORM IN TEMPLATE WORKFLOW 
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form

            // Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_receive"></i>'
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

        $this->load->model("repackage_model", "repackage");
        $this->load->model("stock_model", "stock");
        $this->load->model("re_location_model", "relocate");
        if ((!is_array($pending_doc_list)) || (count($pending_doc_list) <= 0)) {
            $json['status'] = "E001";
            $json['error_msg'] = "";
            echo json_encode($json);
        }
        foreach ($pending_doc_list as $doc_no) {
            # Generate Re-Package Document No.
            $prefix = "RP";
//            $data['Document_No'] = createDocumentNo($prefix);
            $data['Document_No'] = create_document_no_by_type($prefix); // Add by Ton! 20140428
//			list($flow_id,$action_id)  = $this->workflow->addNewWorkflow($process_id ,$present_state ,$action_type ,$next_state ,$user_id ,$data);
            list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data); //Edit by Ton! 20131021
            $query = $this->repackage->getRepackageDocument(array($doc_no));
            $doc_detail = $query->result();
            $order_id = "";
            $order_detail_remark = $this->flow->getOrderDetailByDocumentNo($pending_doc_list[0]);
            $remark = $order_detail_remark[0]->Remark;
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
                        , 'Create_date' => date("Y-m-d")
                        , 'Remark' => $remark
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
                $detail['Product_Status'] = 'NORMAL'; // Ak, เปลี่ยนเพราะ ลำดับการทำงานมันต้อง เปลี่ยนจาก Re-Package แล้วเซ็ตให้เป็น NORMAL ก่อน เพราะตอนนี้มันจะถูก relocate แล้ว
                $detail['Product_Sub_Status'] = $rows->Product_Sub_Status;

                $setSuggestLocation = $this->suggest_location->getSuggestLocationArray('suggestLocation', $rows->Product_Code, $detail['Product_Status'], '1');
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
                $order_detail[] = $detail;
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
        $respond = $this->_updateProcess($this->input->post());
        if ("C001" == $respond) {
            $json['status'] = "C002";
            $json['error_msg'] = "";
        } else {
            $json['status'] = "E001";
            $json['error_msg'] = "Incomplete Data";
        }
        $json['respond'] = "$respond";
        echo json_encode($json);
    }

    function approveAction() {
        $respond = $this->_updateProcess($this->input->post());
        if ("C001" == $respond) {
            $json['status'] = "C003";
            $json['error_msg'] = "";
        } else {
            $json['status'] = "E001";
            $json['error_msg'] = "Incomplete Data";
        }
        $json['respond'] = "$respond";
        echo json_encode($json);
    }

    function _updateProcess() {
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
        $ci_remark = $this->input->post("ci_remark");
        $ci_prod_id = $this->input->post("ci_prod_id");
        $ci_prod_status = $this->input->post("ci_prod_status");
        $ci_unit_id = $this->input->post("ci_unit_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");

        # Load Model
        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", "flow");
        $this->load->model("product_model", "product");
        $this->load->model("repackage_model", "repackage");

        # Update Order and Order Detail
        $flow_detail = $this->flow->getFlowDetail($flow_id);

        $data['Document_No'] = $document_no;
//        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $user_id, $data);
        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021

        $order = array(
            'Modified_By' => $user_id
            , 'Modified_Date' => date("Y-m-d H:i:s")
            , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
        );

        $where['Flow_Id'] = $flow_id;
        $this->repackage->updateRepackageOrder($order, $where);

        $order_detail = array();
        if (count($prod_list) > 0) {
            foreach ($prod_list as $rows) {
                unset($where);
                $a_data = explode(SEPARATOR, $rows);
                $detail = array();
                $detail['Product_Id'] = $a_data[$ci_prod_id];
                $detail['Product_Status'] = $a_data[$ci_prod_status];
                $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                $where['Item_Id'] = $a_data[$ci_item_id];

                $product_code = $this->repackage->getProductCodeByProductId($detail['Product_Id']);
                $product_code = $product_code[0]->Product_Code;
                $setSuggestLocation = $this->suggest_location->getSuggestLocationArray('suggestLocation', $product_code, $detail['Product_Status'], '1');
//                                p($setSuggestLocation[0]['Location_Id']);exit();
                $detail['Suggest_Location_Id'] = (!empty($setSuggestLocation) ? $setSuggestLocation[0]['Location_Id'] : NULL);

                $this->repackage->updateRepackageDetail($detail, $where);
            }
        }
        //logOrderDetail($order_id,'repackage',$action_id,$action_type);
        return "C001";
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
//		$product_status  = "NORMAL";// Comment Out by Ton! 20130813
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
//		$product_status  = "NORMAL";// Comment Out by Ton! 20130813
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

        # Load Model
        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", "flow");
        $this->load->model("product_model", "product");
        $this->load->model("repackage_model", "repackage");

        # Update Order and Order Detail
        $flow_detail = $this->flow->getFlowDetail($flow_id);

        $data['Document_No'] = $document_no;
//        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $user_id, $data);
        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021

        $order = array(
            'Modified_By' => $user_id
            , 'Modified_Date' => date("Y-m-d H:i:s")
            , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
            , 'Assigned_Id' => $assigned_id
        );

        $where['Flow_Id'] = $flow_id;
        $this->repackage->updateRepackageOrder($order, $where);

        $order_detail = array();
        if (count($prod_list) > 0) {
            foreach ($prod_list as $rows) {
                unset($where);
                $a_data = explode(SEPARATOR, $rows);
                $detail = array();
                $detail['Suggest_Location_Id'] = $a_data[$ci_suggest_loc];
                $detail['Confirm_Qty'] = $a_data[$ci_confirm_qty];
                $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                $where['Item_Id'] = $a_data[$ci_item_id];
                $where['Product_Id'] = $a_data[$ci_prod_id];
                $this->repackage->updateRepackageDetail($detail, $where);
            }
        }
        //logOrderDetail($order_id,'repackage',$action_id,$action_type);
        $json['status'] = "C001";
        $json['error_msg'] = "";
        echo json_encode($json);
    }

    function relocateApprove() {
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
        $ci_actual_loc = $this->input->post("ci_actual_loc");
        $ci_old_loc = $this->input->post("ci_old_loc");

        # Load Model
        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", "flow");
        $this->load->model("product_model", "product");
        $this->load->model("repackage_model", "repackage");

        # Update Order and Order Detail
        $flow_detail = $this->flow->getFlowDetail($flow_id);

        $data['Document_No'] = $document_no;
//        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $user_id, $data);
        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021

        $order = array(
            'Modified_By' => $user_id
            , 'Modified_Date' => date("Y-m-d H:i:s")
            , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
            , 'Assigned_Id' => $assigned_id
        );

        $where['Flow_Id'] = $flow_id;
        $this->repackage->updateRepackageOrder($order, $where);

        $order_detail = array();
        if (!empty($prod_list)) {
            foreach ($prod_list as $rows) {
                unset($where);
                $a_data = explode(SEPARATOR, $rows);
                $detail = array();
                $detail['Suggest_Location_Id'] = $a_data[$ci_suggest_loc];
                $detail['Actual_Location_Id'] = $a_data[$ci_actual_loc];
                $detail['Confirm_Qty'] = $a_data[$ci_confirm_qty];
                $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                $detail['Putaway_By'] = $assigned_id;
                $detail['Putaway_Date'] = date("Y-m-d H:i:s");
                $where['Item_Id'] = $a_data[$ci_item_id];
                $where['Product_Id'] = $a_data[$ci_prod_id];
                $this->repackage->updateRepackageDetail($detail, $where);
            }
        }
        $this->stock_lib->moveLocation($order_id);
        $this->repackage->updateActivityVAS($flow_id, '1');
        //logOrderDetail($order_id,'repackage',$action_id,$action_type);
        $json['status'] = "C001";
        $json['error_msg'] = "";
        echo json_encode($json);
    }

    // Add By Akkarapol, 03/09/2013, เพิ่มการเช็ค ActualLocattion เข้าไป จากการที่ User กรอก Location Code ผ่าน textbox และทำการ Get ค่า ID มาเพื่อเปรียบเทียบกับ Suggest

    function chkActualLocattion() {
        $chk_actual_loc = $this->input->post("chk_actual_loc");
        $this->load->model("location_model", "sys");
        $result = $this->sys->getLocationIdByCode($chk_actual_loc);
        echo $result;
    }

    // END Add By Akkarapol, 03/09/2013, เพิ่มการเช็ค ActualLocattion เข้าไป จากการที่ User กรอก Location Code ผ่าน textbox และทำการ Get ค่า ID มาเพื่อเปรียบเทียบกับ Suggest


    function autoCompleteActualLocation() {
        $text_search = $this->input->post('text_search');
        $tr_data_no = $this->input->post('tr_data_no');
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("location_model", "location");
//		$product=$this->p->searchProduct($text_search,$supplier_id);
        $result = $this->location->getLocationByLikeCode($text_search);

        $results = array();
        foreach ($result as $idx => $data) {
            $results[$idx]['location_id'] = $data->Location_Id;
            $results[$idx]['location_code'] = $data->Location_Code;
            //$list.='<li onClick="fill(\''.$tr_data_no.'\',\''.$data->Location_Id.'\',\''.$data->Location_Code.'\');">'.$this->conv->tis620_to_utf8($data->Location_Code).'</li>';
        }
        echo json_encode($results);
        //echo $list;
    }

}
