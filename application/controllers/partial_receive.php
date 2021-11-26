<?php

// Akkarapol, 02/09/2013, เขียนไฟล์นี้ใหม่ เพื่อใช้กับ partial receive โดยเฉพาะ

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class partial_receive extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));
        $this->load->model("product_model", "p");
        $this->load->model("workflow_model", "flow");
        $this->load->model("partial_receive_model", "partialReceive");
    }

    public function index() {
        $this->openForm($process_id = 13);
    }

    #Open Form

    function openForm($process_id, $present_state = 0) {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowPartialReceiveList"); // Button Permission. Add by Ton! 20140131

        $query = $this->partialReceive->getPartialReceiveOrder();
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

    #Action Open Process

    function openAction() {

        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;

        $partial_receive_doc_list = $this->input->post("chkBoxVal");
        $process_id = 1;
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->input->post("user_id");

        $this->load->model("partial_receive_model", "partialReceive");
        $this->load->model("stock_model", "stock");
        $this->load->model("re_location_model", "relocate");

        /**
         * ================== Start Transaction =========================
         */
        $this->transaction_db->set_transaction_level();

        $this->transaction_db->transaction_start();


        /**
         * check partial_receive_doc_list
         */
        if ($check_not_err):
            if ((!is_array($partial_receive_doc_list)) || empty($partial_receive_doc_list)) :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Empty list.";
            endif;
        endif;


        /**
         * check partial_receive_doc_list
         */
        if ($check_not_err):
            foreach ($partial_receive_doc_list as $doc_no) :
                # generate Partial Receive Document No.
                $prefix = "PAR";


                /**
                 * Get Detail From DocumentNo($doc_no)
                 */
                if ($check_not_err):
                    $query = $this->partialReceive->getOrderByDocumentNo(array($doc_no));
                    $doc_detail = $query->row_array();
                    if (empty($doc_detail) || $doc_detail['Is_Partial'] == 'N'):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not Get Detail From DocumentNo($doc_no).";
                        break;
                    endif;
                endif;


                /**
                 * create new DocumentNo
                 */
                if ($check_not_err):
//                    $data['Document_No'] = createDocumentNo($prefix);
                    $data['Document_No'] = create_document_no_by_type($prefix); // Add by Ton! 20140428
                    if (empty($doc_detail)):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not create new DocumentNo.";
                        break;
                    endif;
                endif;


                /**
                 * create new Workflow
                 */
                if ($check_not_err):
                    list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data, @$doc_detail['Flow_Id']); //Edit by Ton! 20131021
                    if (empty($flow_id) || $flow_id == '' || empty($action_id) || $action_id == ''):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not Create new Workflow.";
                        break;
                    endif;
                endif;


                /**
                 * change Old Document (Is_Partial='N')
                 */
                if ($check_not_err):
                    $order_id = "";
                    //ดึงรายละเอียดของเอกสารออกมา (ในตาราง order)
                    $order_detail_remark = $this->flow->getOrderDetailByDocumentNo($partial_receive_doc_list[0]);
                    $remark = $order_detail_remark[0]->Remark;
                    $order = $doc_detail;

                    $result_changeIsNotPartialByOrderId = $this->partialReceive->changeIsNotPartialByOrderId($order['Order_Id']);
                    if ($result_changeIsNotPartialByOrderId <= 0):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not change Old Document (Is_Partial='N').";
                        break;
                    endif;
                endif;


                /**
                 * Create PartialReceive To Order
                 */
                if ($check_not_err):
                    unset($order['Order_Id']);
                    unset($order['Modified_By']);
                    unset($order['Modified_Date']);
                    $order['Flow_Id'] = $flow_id;
                    $order['Document_No'] = $data['Document_No'];
                    $order['Estimate_Action_Date'] = date('Y-m-d H:i:s');
                    $order['Actual_Action_Date'] = date('Y-m-d H:i:s');
                    $order['Create_By'] = $user_id;
                    $order['Create_Date'] = date('Y-m-d H:i:s');
                    $order['Is_Partial'] = 'N';
                    $order['Parent_Order_Id'] = $doc_detail['Order_Id'];

                    $order_id = $this->partialReceive->addPartialReceiveToOrder($order);
                    if (empty($order_id) || $order_id == ""):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not Create PartialReceive To Order.";
                        break;
                    endif;
                endif;


                /**
                 * Get Detail from Order_Id
                 */
                if ($check_not_err):
                    $orderDetails = $this->partialReceive->getOrderDetailPartial($doc_detail['Order_Id']);
                    $orderDetails = $orderDetails->result_array();
                    if (empty($orderDetails)):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $tmp_order_id = $doc_detail['Order_Id'];
                        $return['critical'][]['message'] = "Can not Get Detail From Order_Id($tmp_order_id).";
                        break;
                    endif;
                endif;


                /**
                 * Create PartialReceive To OrderDetail
                 */
                if ($check_not_err):
                    foreach ($orderDetails as $keyOrderDetail => $orderDetail):
                        unset($orderDetails[$keyOrderDetail]['Item_Id']);
                        $orderDetails[$keyOrderDetail]['Order_Id'] = $order_id;
                        $orderDetails[$keyOrderDetail]['Active'] = ACTIVE;
                        $orderDetails[$keyOrderDetail]['Reserv_Qty'] = $orderDetails[$keyOrderDetail]['Reserv_Qty'] - $orderDetails[$keyOrderDetail]['Confirm_Qty'];

                        //ADD BY POR 2014-06-03 แก้ไขให้คำนวณ all price ใหม่ (เกี่ยวกับเรื่อง price per unit)
                        $orderDetails[$keyOrderDetail]['All_Price'] = $orderDetails[$keyOrderDetail]['Reserv_Qty'] * $orderDetails[$keyOrderDetail]['Price_Per_Unit'];

                        unset($orderDetails[$keyOrderDetail]['Confirm_Qty']);
                        unset($orderDetails[$keyOrderDetail]['Suggest_Location_Id']);
                        unset($orderDetails[$keyOrderDetail]['Actual_Location_Id']);
                        unset($orderDetails[$keyOrderDetail]['Remark']);
                    endforeach;
                    if ($order_id != "") :
                        $result_addPartialReceiveToOrderDetail = $this->partialReceive->addPartialReceiveToOrderDetail($orderDetails);
                        if ($result_addPartialReceiveToOrderDetail <= 0):
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not Create PartialReceive To Order Detail.";
                            break;
                        endif;
                    endif;
                endif;
            endforeach;
        endif;

        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):
            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

            $set_return['message'] = "Unlock Partial Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Unlock Partial Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
        endif;

        echo json_encode($json);



//        $json['status'] = "C001";
//        $json['error_msg'] = "";
//        #if insert data not done . when return rollback and not use database
//        if ($this->db->trans_status() === FALSE) {
//            $this->db->trans_rollback();
//        }
//        #if insert data done. when return commit for runing database again
//        else {
//            $this->db->trans_commit();
//            echo json_encode($json);
//        }
    }

#  Open Form With Data

    function openActionForm() {
        $flow_id = $this->input->post("id");
        $this->load->model("workflow_model", "flow");
        $this->load->model("contact_model", "contact");
        $this->load->model("company_model", "company");
        $this->load->model("system_management_model", "sys");
        $this->load->model("stock_model", "stock");

        #Retrive Data from Table
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order'); // Edit by Ak add 'STK_T_Order' for get data in this table
//        p($flow_detail);
        $process_id = $flow_detail[0]->Process_Id;
        $order_id = $flow_detail[0]->Order_Id;
        $present_state = $flow_detail[0]->Present_State;

        $parameter['process_type'] = $flow_detail[0]->Process_Type;
        $parameter['document_no'] = $flow_detail[0]->Document_No;
        $parameter['doc_refer_int'] = $flow_detail[0]->Doc_Refer_Int;
        $parameter['doc_refer_ext'] = $flow_detail[0]->Doc_Refer_Ext;
        $parameter['doc_refer_inv'] = $flow_detail[0]->Doc_Refer_Inv;
        $parameter['doc_refer_ce'] = $flow_detail[0]->Doc_Refer_CE;
        $parameter['doc_refer_bl'] = $flow_detail[0]->Doc_Refer_BL;
        $parameter['owner_id'] = $flow_detail[0]->Owner_Id;
        $parameter['renter_id'] = $flow_detail[0]->Renter_Id;
        $parameter['shipper_id'] = $flow_detail[0]->Source_Id;
        $parameter['consignee_id'] = $flow_detail[0]->Destination_Id;
        $parameter['receive_type'] = $flow_detail[0]->Doc_Type;
        $parameter['est_receive_date'] = $flow_detail[0]->Est_Action_Date;
        $parameter['remark'] = $flow_detail[0]->Remark;
        $parameter['vendor_id'] = $flow_detail[0]->Vendor_Id;
        $parameter['driver_name'] = $flow_detail[0]->Vendor_Driver_Name;
        $parameter['car_no'] = $flow_detail[0]->Vendor_Car_No;
        $parameter['receive_date'] = $flow_detail[0]->Action_Date;
        $parameter['is_pending'] = $flow_detail[0]->Is_Pending;
        $parameter['is_repackage'] = $flow_detail[0]->Is_Repackage;
        $parameter['Sub_Module'] = $flow_detail[0]->Sub_Module;

        /**
         * GET Order Detail
         */
        $order_by = "STK_T_Order_Detail.Item_Id ASC";
        $order_deatil = $this->stock->getOrderDetail($order_id, false,$order_by,NULL,NULL);  //add by kik : for change parameter to function : 20141004
//        $order_deatil = $this->stock->getOrderDetail($order_id, 82); //ADD BY BALL 2013-12-06 เพิ่มรับ parameter ตัวสุดท้าย เพื่อบอกว่าต้องการให้แสดงใครทำรายการ //comment by kik : 20141004

        $parameter['order_id'] = $order_id;
        $parameter['order_deatil'] = $order_deatil;

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

//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowPartialReceiveList"); // Button Permission. Add by Ton! 20140131

        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['flow_id'] = $flow_id;
        $parameter['process_id'] = $process_id;
        $parameter['present_state'] = $present_state;
        $parameter['data_form'] = (array) $data_form;

        # LOAD FORM
//                p($data_form);
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '"    ONCLICK="cancel()">'
            , 'button_action' => $data_form->str_buttun
        ));
    }

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

    function updateInfo() {
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
            $this->load->library('Stock_lib');
            $respond = $this->stock_lib->updatePartialReceive($this->input->post('order_id'));
//                        Edit By Akkarapol, 28/08/2013, เพิ่มไปเพราะการอัพเดทสต๊อกเข้า Inbound นั้นไม่ได้ทำที่ PutAway แต่ต้องมาทำตั้งแต่ขั้นของการ Receive นี่แล้ว
            $respond = $this->stock_lib->updateStockReceiveOrder($this->input->post('order_id'));
//                        END Edit By Akkarapol, 28/08/2013, เพิ่มไปเพราะการอัพเดทสต๊อกเข้า Inbound นั้นไม่ได้ทำที่ PutAway แต่ต้องมาทำตั้งแต่ขั้นของการ Receive นี่แล้ว
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
        $ci_confirm_qty = $this->input->post("ci_confirm_qty");

        $pending_status_code = "";
        if ($is_pending != ACTIVE) {
            $is_pending = INACTIVE;
        } else {
            $this->load->model("system_management_model", "sys");
            $result = $this->sys->getPendingStatus();
            if (count($result) > 0) {
                foreach ($result as $rows) {
                    $pending_status_code = $rows->Dom_Code;
                }
            }
        }

        if ($is_repackage != ACTIVE) {
            $is_repackage = INACTIVE;
        }

        # Update Order and Order Detail
        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", "flow");

        $data['Document_No'] = $document_no;
//        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $user_id, $data);
        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021

        $order = array(
            'Doc_Refer_Int' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_int))
            , 'Doc_Refer_Ext' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ext))
            , 'Doc_Refer_Inv' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_inv))
            , 'Doc_Refer_CE' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ce))
            , 'Doc_Refer_BL' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_bl))
            , 'Doc_Type' => $receive_type
            , 'Owner_Id' => $owner_id
            , 'Renter_Id' => $renter_id
            , 'Estimate_Action_Date' => $est_receive_date
            , 'Source_Id' => $shipper_id
            , 'Destination_Id' => $consignee_id
            , 'Is_Pending' => $is_pending
            , 'Is_Repackage' => $is_repackage
            , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
            , 'Modified_By' => $user_id
            , 'Modified_Date' => date("Y-m-d H:i:s")
            , 'Vendor_Id' => $vendor_id
            , 'Vendor_Driver_Name' => iconv("UTF-8", "TIS-620", $driver_name)
            , 'Vendor_Car_No' => iconv("UTF-8", "TIS-620", $car_no)
            , 'Actual_Action_Date' => $receive_date
        );
        $where['Flow_Id'] = $flow_id;
        $where['Order_Id'] = $order_id;
        $this->stock->updateOrder($order, $where);

        $order_detail = array();
        if (count($prod_list) > 0) {
            foreach ($prod_list as $rows) {
                $a_data = explode(SEPARATOR, $rows);
                $is_new = $a_data[$ci_item_id];
                $detail = array();
                $detail['Product_Id'] = $a_data[$ci_prod_id];
                $detail['Product_Code'] = $a_data[$ci_prod_code];
                $detail['Product_Status'] = $a_data[$ci_prod_status];
                $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                $detail['Reserv_Qty'] = $a_data[$ci_reserv_qty];
                if (!empty($ci_confirm_qty)):
                    $detail['Confirm_Qty'] = $a_data[$ci_confirm_qty];
                endif;
                $detail['Unit_Id'] = $a_data[$ci_unit_id];
                $detail['Product_Lot'] = $a_data[$ci_lot];
                $detail['Product_Serial'] = $a_data[$ci_serial];
                $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);


                if ($a_data[$ci_mfd] != "") {
                    $detail['Product_Mfd'] = convertDate($a_data[$ci_mfd], "eng", "iso", "-");
                } else {
                    $detail['Product_Mfd'] = null;
                }

                if ($a_data[$ci_exp] != "") {
                    $detail['Product_Exp'] = convertDate($a_data[$ci_exp], "eng", "iso", "-");
                } else {
                    $detail['Product_Exp'] = null;
                }

                if ($is_pending == ACTIVE) {
                    $detail['Product_Status'] = $pending_status_code;
                }

                if ("new" != $is_new) {
                    unset($where);
                    $where['Item_Id'] = $is_new;
                    $where['Order_Id'] = $order_id;
                    $where['Product_Code'] = $detail['Product_Code'];
                    $this->stock->updateOrderDetail($detail, $where);
                } else {
                    $detail['Order_Id'] = $order_id;
                    //$detail['Confirm_Qty']	= 0;
                    $order_detail[] = $detail;
                }
            }
            if (count($order_detail) > 0) {
                $this->stock->addOrderDetail($order_detail);
            }
        }

        if (is_array($prod_del_list) && (count($prod_del_list) > 0)) {
            unset($rows);
            unset($detail);
            $item_delete = array();
            foreach ($prod_del_list as $rows) {
                $a_data = explode(SEPARATOR, $rows);
                $item_delete[] = $a_data[$ci_item_id];  /* Item_Id for Delete in STK_T_Order_Detail  */
            }
            $this->stock->removeOrderDetail($item_delete);
        }
        //logOrderDetail($order_id, 'receive', $action_id, $action_type);   //COMMENT BY POR 2014-07-07
        return "C001";
    }

    function confirmPreReceive() {
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

    function approvePreReceive() {
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

    function confirmReceive() {
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

    function approveReceive() {
        $respond = $this->_updateProcess($this->input->post());
        if ("C001" == $respond) {
            $this->load->library('Stock_lib');
            $respond = $this->stock_lib->updatePartialReceive($this->input->post('order_id'));
//                        Edit By Akkarapol, 28/08/2013, เพิ่มไปเพราะการอัพเดทสต๊อกเข้า Inbound นั้นไม่ได้ทำที่ PutAway แต่ต้องมาทำตั้งแต่ขั้นของการ Receive นี่แล้ว
            $respond = $this->stock_lib->updateStockReceiveOrder($this->input->post('order_id'));
//                        END Edit By Akkarapol, 28/08/2013, เพิ่มไปเพราะการอัพเดทสต๊อกเข้า Inbound นั้นไม่ได้ทำที่ PutAway แต่ต้องมาทำตั้งแต่ขั้นของการ Receive นี่แล้ว
            $json['status'] = "C003";
            $json['error_msg'] = "";
        } else {
            $json['status'] = "E001";
            $json['error_msg'] = "Incomplete Data";
        }
        $json['respond'] = "$respond";
        echo json_encode($json);
    }

    function confirmPutaway() {
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

    function approvePutaway() {
        $respond = $this->_updateProcess($this->input->post());
        if ("C001" == $respond) {
            $this->load->library('Stock_lib');

//            Edit By Akkarapol, 28/08/2013, ปิดไปเพราะการอัพเดทสต๊อกเข้า Inbound นั้นไม่ได้ทำที่ PutAway แต่ต้องไปทำตั้งแต่ขั้นของการ Receive โน่นแล้ว
//            $respond = $this->stock_lib->updateStockPutawayOrder($this->input->post('order_id'));
//            END Edit By Akkarapol, 28/08/2013, ปิดไปเพราะการอัพเดทสต๊อกเข้า Inbound นั้นไม่ได้ทำที่ PutAway แต่ต้องไปทำตั้งแต่ขั้นของการ Receive โน่นแล้ว

            $json['status'] = "C003";
            $json['error_msg'] = "";
        } else {
            $json['status'] = "E001";
            $json['error_msg'] = "Incomplete Data";
        }
        $json['respond'] = "$respond";
        echo json_encode($json);
    }

}
