<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Picking extends CI_Controller {

    public $settings;       //add by kik : 20140114

    public function __construct() {
        parent::__construct();
        $this->settings = native_session::retrieve();   //add by kik : 20140114

        $this->load->model("workflow_model", "flow");
        $this->load->model("pre_dispatch_model", "preDispModel");
        $this->load->model("re_location_model", "rl");
        $this->load->model("order_movement_model", "order_movement");

        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));

    }

#  Open Form With Data

    function openActionForm() {
        
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set เนเธซเน $this->output->enable_profiler เน€เธเนเธ เธเนเธฒเธ•เธฒเธกเธ—เธตเนเน€เธเนเธ•เธกเธฒเธเธฒเธ config เน€เธเธทเนเธญเนเธเนเนเธชเธ”เธ DebugKit

        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        $conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        #Load config XML
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        $conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        #Load Model
        $this->load->model("workflow_model", "flow");
        $this->load->model("pre_dispatch_model", "preDispModel");
        $this->load->model("contact_model", "contact");
        $this->load->model("company_model", "company");
        $this->load->model("system_management_model", "sys");
        $this->load->model("stock_model", "stock");


        $flow_id = $this->input->post("id");
        $owner_id = $this->config->item('owner_id');

        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order'); // Edit by Ton! 20130814
        //p($this->db->last_query());exit;
        $process_id = $flow_detail[0]->Process_Id;
        $present_state = $flow_detail[0]->Present_State;
        $document_no = $flow_detail[0]->Document_No;
	    $module = $flow_detail[0]->Module;

        // validate document exist state
        $valid_state = validate_state($module);
        if ($valid_state) :
            redirect($valid_state);
        endif;

        // register token
        $parameter['token'] = register_token($flow_id, $present_state, $process_id);
        // end config token
        
        $order_id = $flow_detail[0]->Order_Id;
        $renter_id = $flow_detail[0]->Renter_Id;
        $warehouse_from = $flow_detail[0]->Source_Id;
        $warehouse_to = $flow_detail[0]->Destination_Id;
        $receive_type = $flow_detail[0]->Doc_Type;
        $est_dispatch_date = $flow_detail[0]->Est_Action_Date;
        $dispatch_date = $flow_detail[0]->Action_Date;
        $Doc_Refer_Ext = $flow_detail[0]->Doc_Refer_Ext;
        $Doc_Refer_Int = $flow_detail[0]->Doc_Refer_Int;
        $Doc_Refer_Inv = $flow_detail[0]->Doc_Refer_Inv;
        $Doc_Refer_CE = $flow_detail[0]->Doc_Refer_CE;
        $Doc_Refer_BL = $flow_detail[0]->Doc_Refer_BL;
        $remark = $flow_detail[0]->Remark;
        $is_urgent = $flow_detail[0]->Is_urgent;

        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowPickingList"); // Button Permission. Add by Ton! 20140131
//p($this->db->last_query());exit;
//p($flow_detail); exit;
        #comment by kik : for move code to function get_picking_detail
        #add for fix defect #461 picking by in transfer stock order not show : by kik : 20140206
//        $sub_module = $this->order_movement->get_submodule_activity('picking');
////        p($sub_module) ;exit();
//        if ($process_id == 2):
//            $state_edge = 14;
////            $sub_module = 'confirmAction' ;
//        else:
////            $sub_module = 'confirmAction';
//            $state_edge = 58;
//        endif;
//        #end add for fix defect #461 picking by in transfer stock order not show : by kik : 20140206



        $order_data = $this->preDispModel->queryDataFromFlowId($flow_id);
    //    p($order_data);exit;

        foreach ($order_data as $OrderObject) {
            $order_data_value = $OrderObject;
        }
 //p($order_data_value);exit;
        #comment by kik : for move code to function get_picking_detail
//        $order_detail_data = $this->preDispModel->query_from_OrderDetailId($order_data_value->Order_Id, $state_edge ,'L1.Location_Code ASC',false,$sub_module,'picking');
//        $order_detail_data = $order_detail_data->result(); // Add By Akkarapol, 21/10/2013, เน€เธเนเธ•เธเนเธฒเธ•เธฑเธงเนเธเธฃเนเธซเธกเน เน€เธเธฃเธฒเธฐเธเนเธฒเธ—เธตเน Return เธกเธฒเธเธฒเธเธเธฑเธเธเนเธเธฑเนเธ query_from_OrderDetailId เธเธฑเนเธเธชเนเธเธกเธฒเน€เธเนเธเนเธเธ $query เน€เธฅเธข เน€เธเธทเนเธญเนเธซเนเธฃเธญเธเธฃเธฑเธเธเธฒเธฃเน€เธฃเธตเธขเธเนเธเนเธเธฒเธเธ—เธตเนเธซเธฅเธฒเธเธซเธฅเธฒเธขเธเธถเนเธ
//        foreach ($order_detail_data as $OrderDetailObject) {
//            $order_detail_data_value = $OrderDetailObject;
//            if ($OrderDetailObject->Actual_Location == "" || $OrderDetailObject->Actual_Location == NULL) :
//                $order_detail_data_value->Activity_By_Name = "";
//            endif;
//        }
//p($order_detail_data);exit();
        #Get Renter [Company Renter] list
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY");

        #Get Shipper[Company Owner] list
        $q_shipper = $this->company->getOwnerAll();
        $r_shipper = $q_shipper->result();
        $shipper_list = genOptionDropdown($r_shipper, "COMPANY");

        #Get Consignee[Company Consignee] list
        $q_consignee = $this->company->getBranchNotOwner($this->session->userdata('branch_id')); // Add By Akkarapol, 12/09/2013, เน€เธเธดเนเธกเธเธฒเธฃ get เธเนเธญเธกเธนเธฅเธเธญเธ branch เธ—เธตเนเนเธกเนเนเธเนเธเธญเธเธ•เธเน€เธญเธเน€เธเนเธฒเธกเธฒเน€เธเธทเนเธญเธเธณเนเธเนเธชเนเธ—เธตเน Consignee
        $r_consignee = $q_consignee->result();
        $consignee_list = genOptionDropdown($r_consignee, "COMPANY");
//        $consignee_id = 1;

//        p($consignee_list);
        #Get Dispatch Type list
        $dispatch_type = $this->sys->getDispatchType();
        $dispatch_type_list = genOptionDropdown($dispatch_type, "SYS");

        $renter_id_select = form_dropdown("renter_id_select", $renter_list, $order_data_value->Renter_Id, "id=renter_id_select class=required");
        $frm_warehouse_select = form_dropdown("frm_warehouse_select", $shipper_list, $order_data_value->Source_Id, "id=frm_warehouse_select class=required");
        $to_warehouse_select = form_dropdown("to_warehouse_select", $consignee_list, $order_data_value->Destination_Id, "id=to_warehouse_select class=required");
        $dispatch_type_select = form_dropdown("dispatch_type_select", $dispatch_type_list, $order_data_value->Doc_Type, "id=dispatch_type_select class=required");

//        $query = $this->preDispModel->getPreDispatchAll();
//        $pre_dispatch_list = $query->result();

        // register token
        $token = register_token($flow_id, $present_state, $process_id);
        // end config token

        /* Change export button by Ball */
        $data_form->str_buttun .= '<input class="button dark_blue" type="button" onclick="exportFile(\'PDF\',\''.$document_no.'\')" value="PDF">';

        if(empty($conf['picking_default_order_by'])):
            $conf['picking_default_order_by'] = 'item';
        endif;

        // Add By Akkarapol, 02/05/2014, เพิ่มการ get show_column_picking_job ที่เก็บไว้ใน cookie เพื่อดึงว่า ต้องการใช้ column ไหนบ้างในการแสดงผล ซึ่งจะต้องทำการ unserialize ข้อมูลออกมาด้วย
        $this->load->helper('cookie');
        $show_column_picking_job = get_cookie('show_column_picking_job');
        if(empty($show_column_picking_job)):
            $show_column_picking_job = array(
                "no" => TRUE
                , "product_code" => TRUE
                , "product_name" => TRUE
                , "product_status" => TRUE
                , "product_sub_status" => TRUE
                , "lot" => TRUE
                , "serial" => TRUE
                , "mfd" => TRUE
                , "exp" => TRUE
                , "reserve_qty" => TRUE
                , "confirm_qty" => TRUE
                , "unit" => TRUE
                , "price_per_unit" => TRUE
                , "unit_price" => TRUE
                , "all_price" => TRUE
                , "suggest_location" => TRUE
                , "actual_location" => TRUE
                , "pallet_code" => TRUE
                , "pick_by" => TRUE
                , "remark" => TRUE
            );
        else:
            $show_column_picking_job = unserialize(get_cookie('show_column_picking_job'));
        endif;
        // END Add By Akkarapol, 02/05/2014, เพิ่มการ get show_column_picking_job ที่เก็บไว้ใน cookie เพื่อดึงว่า ต้องการใช้ column ไหนบ้างในการแสดงผล ซึ่งจะต้องทำการ unserialize ข้อมูลออกมาด้วย


        #comment by kik : for move code to function get_picking_detail
        #ADD BY POR 2014-06-11 เพิ่มให้สามารถกรอกราคาและเลือกหน่วยได้
//        $price = '';

//        if ($conf_price_per_unit):
//            //$price = ',null';  //COMMENT BY POR 2014-06-02
//
//
//            $price = " ,{
//                    sSortDataType: \"dom-text\",
//                    sType: \"numeric\",
//                    type: 'text',
//                    onblur: \"submit\",
//                    event: 'click',
//                    is_required: true,
//                    loadfirst: true,
//                    cssclass: \"required number\",
//                    sUpdateURL: '" . site_url() . '/pre_dispatch/saveEditedRecord' . "',
//                    fnOnCellUpdated: function(sStatus, sValue, settings) {
//                        calculate_qty();
//                    }
//                }";
//
//
//            $unitofprice = ",
//                 {
//                    loadurl: '" . site_url() . '/pre_receive/getPriceUnit' . "',
//                    loadtype: 'POST',
//                    type: 'select',
//                    event: 'click',
//                    onblur: 'submit',
//                    sUpdateURL: function(value, settings) {
//                        var oTable = $('#ShowDataTableForInsert').dataTable();
//                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
//                        oTable.fnUpdate(value, rowIndex, ci_unit_price_id);
//                        return value;
//                    }
//                }
//                ";
//
//            //END ADD;
//        else:
//            $price = "  , {
//                    onblur: 'submit',
//                    sUpdateURL: '" . site_url() . '/pre_dispatch/saveEditedRecord' . "',
//                    event: 'click',
//                }";
//            $unitofprice = ',null';
//        endif;
        #END
//p($data_form->str_buttun);exit();


        $str_form = $this->parser->parse('form/' . $data_form->form_name, array("parameter" => form_input('warehouse_form', 'whs_form')
            , "test_parse" => "test pass parse from controller"
            , "renter_id_select" => $renter_id_select
            , "frm_warehouse_select" => $frm_warehouse_select
            , "to_warehouse_select" => $to_warehouse_select
            , "dispatch_type_select" => $dispatch_type_select
            , "process_id" => $process_id
            , "present_state" => $present_state
            , "DocNo" => trim($document_no)
            , "doc_refer_ext" => trim($Doc_Refer_Ext)
            , "doc_refer_int" => trim($Doc_Refer_Int)
            , "doc_refer_inv" => trim($Doc_Refer_Inv)
            , "doc_refer_ce" => trim($Doc_Refer_CE)
            , "doc_refer_bl" => trim($Doc_Refer_BL)
//            , "order_detail_data" => $order_detail_data
            , "remark" => trim($remark)
            , "est_action_date" => trim($est_dispatch_date)
            , "flow_id" => $flow_id
            , "order_id" => $order_id
            , "owner_id" => $owner_id
            , "is_urgent" => $is_urgent
            , "data_form" => (array) $data_form
            , "token" => $token
            , "show_column_picking_job" => $show_column_picking_job
//            , 'price' => $price         //ADD BY POR 2014-06-11
//            , 'unitofprice' => $unitofprice //ADD BY POR 2014-06-11ให้ส่งตัวแปรเกี่ยวกับการแสดงหน่วยของราคา
            , "price_per_unit" => $conf_price_per_unit
            , "conf_inv" => $conf_inv
            , "conf_cont" => $conf_cont
            , "conf_pallet" => $conf_pallet
            , "picking_default_order_by" => $conf['picking_default_order_by']
                )
                , TRUE);
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form

            // Add By Akkarapol, 21/09/2013, เน€เธเธดเนเธก Class toggle เน€เธเนเธฒเนเธ เนเธซเนเธฃเธญเธเธฃเธฑเธเธเธฑเธเธ—เธตเน K.Krip เน€เธเธตเธขเธเนเธเนเธ”เนเธงเน
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPicking"></i>'
            // END Add By Akkarapol, 21/09/2013, เน€เธเธดเนเธก Class toggle เน€เธเนเธฒเนเธ เนเธซเนเธฃเธญเธเธฃเธฑเธเธเธฑเธเธ—เธตเน K.Krip เน€เธเธตเธขเธเนเธเนเธ”เนเธงเน
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_action' => $data_form->str_buttun
            , 'user_login' => $this->session->userdata('username')
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
        ));
    }

    function confirmAction() {

        /**
         * Set Variable
         */
        $check_not_err = TRUE;
        $return = array();


        /**
         * Start token
         */
        #Retrive Data from Table
        $token = $this->input->post('token');

        $flow_id = $this->input->post('flow_id');
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        if (empty($flow_info)) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = false;

        else :

            $process_id = $flow_info['0']->Process_Id;
            $present_state = $flow_info['0']->Present_State;

            // validate token
            $response = validate_token($token, $flow_id, $present_state, $process_id);


            if (!$response) :
                $array_return['critical'][]['message'] = "Document already pass this step.";
                $json['status'] = "save";
                $json['return_val'] = $array_return;
                $check_not_err = false;
            endif;
        endif;


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

            $set_return['message'] = "Confirm Picking Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Confirm Picking Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
        endif;

        echo json_encode($json);
    }

    function approveAction() {

        $this->load->model("workflow_model", "flow");

        /**
         * set Variable
         */
        $check_not_err = TRUE;
        $return = array();

        /**
         * Start token
         */
        #Retrive Data from Table
        $token = $this->input->post('token');

        $flow_id = $this->input->post('flow_id');
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        if (empty($flow_info)) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = false;

        else :

            $process_id = $flow_info['0']->Process_Id;
            $present_state = $flow_info['0']->Present_State;

            // validate token
            $response = validate_token($token, $flow_id, $present_state, $process_id);


            if (!$response) :
                $array_return['critical'][]['message'] = "Document already pass this step.";
                $json['status'] = "save";
                $json['return_val'] = $array_return;
                $check_not_err = false;
            endif;
        endif;

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

            $set_return['message'] = "Approve Picking Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Approve Picking Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
        endif;

        echo json_encode($json);
    }

    function _updateProcess($params, $is_quick_approve = FALSE) {
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
        $renter_id = $this->input->post("renter_id_select");
        $shipper_id = $this->input->post("frm_warehouse_select");
        $consignee_id = $this->input->post("to_warehouse_select");
        $remark = $this->input->post("remark");
        $dispatch_type = $this->input->post("dispatch_type_select");
        $est_dispatch_date = $this->input->post("estDispatchDate");
        $is_urgent = $this->input->post("is_urgent");   //add for ISSUE 3312 : by kik : 20140120
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
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        $ci_actual_loc = $this->input->post("ci_actual_loc");
        $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");

        //ADD BY KIK 2014-01-14 เน€เธเธดเนเธกเน€เธเธตเนเธขเธงเธเธฑเธเธฃเธฒเธเธฒ
        if ($conf_price_per_unit == TRUE) {
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

        # Update Order and Order Detail
        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", "flow");

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
         * update Order
         */
        if ($check_not_err):
            $order = array(
                'Doc_Refer_Int' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_int))
                , 'Doc_Refer_Ext' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ext))
                , 'Doc_Refer_Inv' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_inv))
                , 'Doc_Refer_CE' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ce))
                , 'Doc_Refer_BL' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_bl))
                , 'Doc_Type' => $dispatch_type
                , 'Owner_Id' => $owner_id
                , 'Renter_Id' => $renter_id
                , 'Estimate_Action_Date' => $est_dispatch_date
                , 'Source_Id' => $shipper_id
                , 'Destination_Id' => $consignee_id
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Modified_By' => $user_id
                , 'Modified_Date' => date("Y-m-d H:i:s")
                , 'Is_urgent' => $is_urgent
            );
            $where['Flow_Id'] = $flow_id;
            $where['Order_Id'] = $order_id;
            $result_updateOrder = $this->stock->updateOrder($order, $where);
            if (!$result_updateOrder):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update Order.";
            endif;
        endif;


        /**
         * update Order Detail
         */
        if ($check_not_err):
            $order_detail = array();
            if (!empty($prod_list)) :
                foreach ($prod_list as $rows) :
                    $a_data = explode(SEPARATOR, $rows);
                    $is_new = $a_data[$ci_item_id];
                    $detail = array();

                    $detail['Activity_Code'] = 'PICKING';      // Add By Akkarapol, 11/09/2013, เน€เธเธดเนเธกเนเธชเนเธเนเธฒเน€เธเนเธฒเนเธเนเธ เธเธฒเธเธเนเธญเธกเธนเธฅ เธเธฐเนเธ”เนเน€เธฃเธตเธขเธเน€เธญเธฒเธกเธฒเนเธเนเธ—เธตเธซเธฅเธฑเธเนเธ”เน เน€เธเธทเนเธญเธเธเธฒเธ Activity_Code เน€เธเธตเนเธขเธงเน€เธเธทเนเธญเธเธเธฑเธ Process เธซเธฅเธฒเธขเนเธ•เธฑเธงเธ”เนเธงเธขเธเธฑเธ
                    $detail['Activity_By'] = $this->session->userdata("user_id");      // Add By Akkarapol, 11/09/2013, เน€เธเธดเนเธกเนเธชเนเธเนเธฒเน€เธเนเธฒเนเธเนเธ เธเธฐเนเธ”เนเธฃเธนเนเธงเนเธฒเนเธเธฃเน€เธเนเธเธเธเธ—เธณ Activity เธเธตเน
                    $detail['Activity_Date'] = date("Y-m-d H:i:s");

                    $detail['Product_Id'] = $a_data[$ci_prod_id];
                    $detail['Product_Code'] = $a_data[$ci_prod_code];
                    $detail['Reserv_Qty'] = str_replace(",", "", $a_data[$ci_reserv_qty]); //+++++ADD BY POR 2013-11-29 เนเธเธฅเธ qty เนเธซเนเธญเธขเธนเนเนเธเธฃเธนเธเนเธเธ float เนเธ”เธขเธ•เธฑเธ” comma เธญเธญเธ
                    $detail['Unit_Id'] = $a_data[$ci_unit_id];
                    $detail['Reason_Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                    $detail['Inbound_Item_Id'] = $a_data[$ci_inbound_id];
                    $detail['Suggest_Location_Id'] = $a_data[$ci_suggest_loc];

                    if ($is_quick_approve) :
                        $detail['Actual_Location_Id'] = $a_data[$ci_suggest_loc];
                        $detail['Confirm_Qty'] = str_replace(",", "", $a_data[$ci_reserv_qty]);
                    else :
                        $detail['Actual_Location_Id'] = $a_data[$ci_actual_loc];
                        $detail['Confirm_Qty'] = str_replace(",", "", $a_data[$ci_confirm_qty]);
                    endif;

                    if ($conf_price_per_unit == TRUE) :
                        $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]); //+++++ADD BY KIK 2014-01-14 เนเธเธฅเธ qty เนเธซเนเธญเธขเธนเนเนเธเธฃเธนเธเนเธเธ float เนเธ”เธขเธ•เธฑเธ” comma เธญเธญเธ
                        $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                        $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]); //+++++ADD BY KIK 2014-01-14 เนเธเธฅเธ qty เนเธซเนเธญเธขเธนเนเนเธเธฃเธนเธเนเธเธ float เนเธ”เธขเธ•เธฑเธ” comma เธญเธญเธ
                    endif;
                    //END ADD

                    if ("new" != $is_new) :
                        unset($where);
                        $where['Item_Id'] = $is_new;
                        $where['Order_Id'] = $order_id;
                        $where['Product_Code'] = $a_data[$ci_prod_code];
                        $where['Product_Id'] = $a_data[$ci_prod_id];
                        /**
                         * update Order Detail
                         */
                        $result_updateOrderDetail = $this->stock->updateOrderDetail($detail, $where);
                        if (!$result_updateOrderDetail):
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not update Order Detail.";
                            break;
                        endif;
                    else :
                        $detail['Order_Id'] = $order_id;
                        $detail['Product_Lot'] = $a_data[$ci_lot];
                        $detail['Product_Serial'] = $a_data[$ci_serial];
                        $detail['Product_Status'] = $a_data[$ci_prod_status];
                        $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];

                        if ($a_data[$ci_mfd] != "") :
                            $detail['Product_Mfd'] = convertDate($a_data[$ci_mfd], "eng", "iso", "-");
                        else :
                            $detail['Product_Mfd'] = null;
                        endif;
                        if ($a_data[$ci_exp] != "") :
                            $detail['Product_Exp'] = convertDate($a_data[$ci_exp], "eng", "iso", "-");
                        else :
                            $detail['Product_Exp'] = null;
                        endif;

                        $order_detail[] = $detail;
                    endif;
                endforeach;

                if (!empty($order_detail)) :

                    /**
                     * Create new OrderDetail
                     */
                    $result_addOrderDetail = $this->stock->addOrderDetail($order_detail);
                    if ($result_addOrderDetail <= 0):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not Create new OrderDetail.";
                    endif;

                    /**
                     * Update PK_Reserv_Qty
                     */
                    $result_reservPickingArray = $this->stock->reservPickingArray($order_detail);
                    if (!$result_reservPickingArray):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not Update PK_Reserv_Qty.";
                    endif;
                endif;
            endif;
        endif;


        /**
         * remove Order Detail
         */
        if ($check_not_err):
            if (is_array($prod_del_list) && (count($prod_del_list) > 0)) :
                unset($rows);
                unset($detail);
                $item_delete = array();
                foreach ($prod_del_list as $rows) :
                    $a_data = explode(SEPARATOR, $rows);
                    $item_delete[] = $a_data[$ci_item_id];
                    $res_qty = str_replace(",", "", $a_data[$ci_reserv_qty]); //+++++ADD BY POR 2013-11-29 เนเธเธฅเธ qty เนเธซเนเธญเธขเธนเนเนเธเธฃเธนเธเนเธเธ float เนเธ”เธขเธ•เธฑเธ” comma เธญเธญเธ

                    /**
                     * update PD_Reserv_Qty in STK_T_Inbound
                     */
                    $result_reservPDReservQty = $this->stock->reservPDReservQty($a_data[$ci_inbound_id], $res_qty, "-");
                    if (!$result_reservPDReservQty):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not update PD_Reserv_Qty in STK_T_Inbound.";
                        break;
                    endif;

                    /**
                     * update PK_Reserv_Qty in STK_T_Inbound
                     */
                    $result_reservPicking = $this->stock->reservPicking($a_data[$ci_inbound_id], $res_qty, "-");
                    if (!$result_reservPDReservQty):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not update PK_Reserv_Qty in STK_T_Inbound.";
                        break;
                    endif;
                endforeach;


                /**
                 * remove Order Detail
                 */
                $result_removeOrderDetail = $this->stock->removeOrderDetail($item_delete);
                if (!$result_removeOrderDetail):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not remove Order Detail.";
                endif;
            endif;
        endif;

        //logOrderDetail($order_id, 'picking', $action_id, $action_type);   //COMMENT BY POR 2014-07-07 NOT USER FUNCTION
//        return "C001";
        return $return;
    }

    function rejectAndReturnAction() {
      //p("AAA"); exit;
        $this->load->model("stock_model", "stock");

        $check_not_err = TRUE;
        $return = array();

        $process_id = $this->input->post("process_id");
        $flow_id = $this->input->post("flow_id");
        $present_state = $this->input->post("present_state");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $document_no = $this->input->post("document_no");
        $order_id = $this->input->post("order_id");

        $data['Document_No'] = $document_no;

        $token = $this->input->post('token');

        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));
       
        if (empty($flow_info)) :
            $set_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $set_return;
            $check_not_err = false;

        else :

            $response = validate_token($token, $flow_id, $flow_info['0']->Present_State, $flow_info['0']->Process_Id);

            if (!$response) :
                $set_return['critical'][]['message'] = "Document already pass this step.";
                $json['status'] = "save";
                $json['return_val'] = $set_return;
                $check_not_err = false;
            endif;
        endif;

        $this->transaction_db->transaction_start();

        if ($check_not_err):

            #return qty to PD_reserv in inbound table

            // If return from Confirm Pickign to Wait for Approve Pre-Dispatch Start here
            if ( ($present_state == 3 && $next_state == 2) || ($present_state == 4 && $next_state == 3) ) {

                $order_detail = $this->stock->getOrderDetailByOrderId($order_id);
// p($this->db->last_query());exit;
                $result_reservPickingArray = $this->stock->decreasePickingReserv($order_detail);
               // p($this->db->last_query());exit;
                if (!$result_reservPickingArray):
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not update PK_Reserv_Qty in STK_T_Inbound.";
                endif;

                /**
                 * Create Relocate Data
                 */
                //p($order_id); exit;
                if ($check_not_err):
                    $result_createRelocat = $this->createRelocat($order_id , FALSE);
         
                    if (!empty($result_createRelocat['critical'])) :
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Can not Create Relocate Data.";
                        $return = array_merge_recursive($return, $result_createRelocat);
                    endif;
                endif;

            }

            /**
             * Update Order Detail
             */
            if ($check_not_err):
                #update data order detail
                //$detail['Suggest_Location_Id'] = NULL; // Dont' clear suggest because when picking it not shown in suggest box
                $detail['Actual_Location_Id'] = NULL;
                $detail['Confirm_Qty'] = NULL;
                $detail['Reason_Code'] = NULL;
                $detail['Reason_Remark'] = NULL;
                $detail['Activity_Date'] = NULL;
                $detail['Activity_By'] = NULL;
                $detail['Activity_Code'] = NULL;

                // reset inbound if return to wait for approve pre-dispatch
                if ( ($present_state == 3 && $next_state == 2) ) {
                    $detail['Inbound_Item_Id'] = NULL;
                }
                
                $where['Order_Id'] = $order_id;

                $result = $this->stock->updateOrderDetail($detail, $where);
                if (!$result):
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not Update Order Detail.";
                endif;
            endif;

            if ($check_not_err):
                $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
                if (empty($action_id) || $action_id == '') :
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not update Workflow.";
                endif;
            endif;

            /**
             * check if for return json and set transaction
             */
            if ($check_not_err) :
                /**
                 * ================== Auto End Transaction =========================
                 */
                $this->transaction_db->transaction_end();
                $set_return['message'] = "Reject and Return Picking Complete.";
                $return['success'][] = $set_return;
                $json['status'] = "save";
                $json['return_val'] = $return;
            else :
                /**
                 * ================== Rollback Transaction =========================
                 */
                $this->transaction_db->transaction_rollback();

                $set_return['critical'][]['message'] = "Reject and Return Picking Incomplete.";
                $json['status'] = "save";
                $json['return_val'] = array_merge_recursive($set_return, $return);
            endif;

        else :
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $set_return['critical'][]['message'] = "Reject and Return Picking Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($set_return, $return);
        endif;

        echo json_encode($json);
    }

    #ISSUE 3034 Reject Document
    #DATE:2013-12-13
    #BY:KIK
    #START New Comment Code #ISSUE 3034 Reject Document
    #add code for reject and return go to state 2(wait for Approve Picking)

    function rejectAction() {
        $this->load->library('stock_lib');
        $this->load->model("stock_model", "stock");

        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        $conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        /**
         * Set Variable
         */
        $check_not_err = TRUE;
        $return = array();

        $process_id = $this->input->post("process_id");
        $flow_id = $this->input->post("flow_id");
        $present_state = $this->input->post("present_state");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $document_no = $this->input->post("document_no");
        $order_id = $this->input->post("order_id");

        $data['Document_No'] = $document_no;

        /**
         * Start token
         */
        #Retrive Data from Table
        $token = $this->input->post('token');

        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        if (empty($flow_info)) :
            $set_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $set_return;
            $check_not_err = false;

        else :

            // validate token
            $response = validate_token($token, $flow_id, $flow_info['0']->Present_State, $flow_info['0']->Process_Id);


            if (!$response) :
                $set_return['critical'][]['message'] = "Document already pass this step.";
                $json['status'] = "save";
                $json['return_val'] = $set_return;
                $check_not_err = false;
            endif;
        endif;

        /**
         * ================== Start Transaction =========================
         */
        $this->transaction_db->transaction_start();

        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):

            #return qty to PD_reserv in inbound table
            $order_detail = $this->stock->getOrderDetailByOrderId($order_id);

            /**
             * update inbound qty in Inbound table
             */
            $result_reservPickingArray = $this->stock->reservPickingArray($order_detail, "-");
            if (!$result_reservPickingArray):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update PK_Reserv_Qty in STK_T_Inbound.";
            endif;

            /**
             * Read Order Detail Data
             */
            $order_detail = $this->stock->getOrderDetailByOrderId($order_id);
            if ( empty($order_detail) ) {
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "System can't find any data, Please check!";
            }

            /**
             * Clear PK Reserv in STK_T_Inbound
             */
            $result_reservPickingArray = $this->stock->decreasePickingReserv($order_detail);
            if (!$result_reservPickingArray):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update PK_Reserv_Qty in STK_T_Inbound.";
            endif;

            /**
             * 3. Clear Pre-Dispatch (PD Reserv)
             * Condition : Clear if didn't pick from location.
             */
            $result_reservPDArray = $this->stock->decreasePDReservPicking($order_detail);
            if (!$result_reservPDArray):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update PD_Reserv_Qty in STK_T_Inbound.";
            endif;

            /**
             * Create Relocate Data
             */          
            if ($check_not_err):
                $result_createRelocat = $this->createRelocat($order_id);
                if (!empty($result_createRelocat['critical'])) :
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not Create Relocate Data.";
                    $return = array_merge_recursive($return, $result_createRelocat);
                endif;
            endif;

            /**
             * Update Order Detail
             */
            if ($check_not_err):
                #update data order detail
                $detail['Active'] = 'N';
                $where['Order_Id'] = $order_id;
                $result = $this->stock->updateOrderDetail($detail, $where);
                if (!$result):
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not Update Order Detail.";
                endif;
            endif;

            /**
             * update Workflow
             */
            if ($check_not_err):
                $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
                if (empty($action_id) || $action_id == '') :
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not update Workflow.";
                endif;
            endif;

            /**
             * check if for return json and set transaction
             */
            if ($check_not_err) :
                /**
                 * ================== Auto End Transaction =========================
                 */
                $this->transaction_db->transaction_end();

                $set_return['message'] = "Reject Picking Complete.";
                $return['success'][] = $set_return;
                $json['status'] = "save";
                $json['return_val'] = $return;
            else :
                /**
                 * ================== Rollback Transaction =========================
                 */
                $this->transaction_db->transaction_rollback();

                $set_return['critical'][]['message'] = "Reject Picking Incomplete.";
                $json['status'] = "save";
                $json['return_val'] = array_merge_recursive($set_return, $return);
            endif;

        else :
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $set_return['critical'][]['message'] = "Reject Picking Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($set_return, $return);
        endif;

        echo json_encode($json);
    }

    function createRelocat($order_id , $allDetail = TRUE) {

        $this->load->library('stock_lib');

        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        $conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        /**
         * Set Variable
         */
        $check_not_err = TRUE;
        $return = array();

//        $process_id = 17;
        $process_id = 6;
        $present_state = 0;
        
        $prod_list = $this->stock->getOrderDetailByOrderId($order_id);
      
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowPickingList");
        
        $process_type = $data_form->process_type;
        //p($process_type);exit;
        $action_type = "Generate Re-Location";
        $next_state = 1;
        $user_id = $this->session->userdata("user_id");
        $est_relocate_date = date("Y-m-d H:i:s");
        $worker_id = $user_id;
        $doc_type = '';
        $owner_id = $this->session->userdata('owner_id');
        $renter_id = $this->session->userdata('renter_id');


        /**
         * generate Relocation Number
         */
        if ($check_not_err):
            $document_no = create_document_no_by_type("REL");
            if (empty($document_no) || $document_no == ''):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not create 'REL No'.";
            endif;
        endif;


        /**
         * Create new Workflow
         */
        if ($check_not_err):
            $data['Document_No'] = $document_no;
            list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data);
            if (empty($flow_id) || $flow_id == '' || empty($action_id) || $action_id == ''):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not Create new Workflow.";
            endif;
        endif;


        /**
         * Create new Order
         */
        if ($check_not_err):
            $order = array(
                'Flow_Id' => $flow_id
                , 'Doc_Relocate' => strtoupper($document_no)
                , 'Doc_Type' => strtoupper(iconv("UTF-8", "TIS-620", $doc_type))
                , 'Process_Type' => $process_type
                , 'Estimate_Action_Date' => $est_relocate_date
                , 'Owner_Id' => $owner_id
                , 'Renter_Id' => $renter_id
                , 'Create_By' => $user_id
                , 'Create_Date' => date("Y-m-d H:i:s")
                , 'Assigned_Id' => $worker_id
            );

            $order_id = $this->rl->addReLocationOrder($order);
            if (empty($order_id) || $order_id == ""):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not Create new Order.";
            endif;
        endif;


        /**
         * Create new OrderDetail
         * Update PD_Reserve_Qty
         */
        if ($check_not_err):
            $all_product_move = array();
        //p($prod_list); exit;
            if (!empty($prod_list)) :
                foreach ($prod_list as $rows) :

                    $info = $this->rl->inboundDetail($rows->Inbound_Item_Id);
                    $suggest_location = $this->stock_lib->getPreDispatchArea();
                    
                    $p_detail = array();
                    $p_detail['Order_Id'] = $order_id;
                    $p_detail['Inbound_Item_Id'] = $rows->Inbound_Item_Id;
                    $p_detail['Document_No'] = $info[0]->Document_No;
                    $p_detail['Doc_Refer_Int'] = $info[0]->Doc_Refer_Int;
                    $p_detail['Doc_Refer_Ext'] = $info[0]->Doc_Refer_Ext;
                    $p_detail['Doc_Refer_Inv'] = $info[0]->Doc_Refer_Inv;
                    $p_detail['Doc_Refer_CE'] = $info[0]->Doc_Refer_CE;
                    $p_detail['Doc_Refer_BL'] = $info[0]->Doc_Refer_BL;
                    $p_detail['Doc_Refer_AWB'] = $info[0]->Doc_Refer_AWB;
                    $p_detail['Product_Id'] = $info[0]->Product_Id;
                    $p_detail['Product_Code'] = $info[0]->Product_Code;
                    $p_detail['Product_Status'] = $info[0]->Product_Status;
                    $p_detail['Product_Sub_Status'] = $info[0]->Product_Sub_Status;
                    $p_detail['Suggest_Location_Id'] = NULL;
                    $p_detail['Actual_Location_Id'] = NULL;
                    $p_detail['Old_Location_Id'] = $suggest_location;
                    $p_detail['Pallet_Id'] = $info[0]->Pallet_Id;
                    $p_detail['Product_License'] = $info[0]->Product_License;
                    $p_detail['Product_Lot'] = $info[0]->Product_Lot;
                    $p_detail['Product_Serial'] = $info[0]->Product_Serial;
                    $p_detail['Product_Mfd'] = $info[0]->Product_Mfd;
                    $p_detail['Product_Exp'] = $info[0]->Product_Exp;
                    $p_detail['Receive_Date'] = $info[0]->Receive_Date;
                    $p_detail['Reserv_Qty'] = $rows->Confirm_Qty;
                    $p_detail['Confirm_Qty'] = NULL;
                    $p_detail['Unit_Id'] = $info[0]->Unit_Id;
                    $p_detail['Remark'] = NULL;
                    $p_detail['Owner_Id'] = $info[0]->Owner_Id;
                    $p_detail['Renter_Id'] = $info[0]->Renter_Id;

                    if ($conf_price_per_unit == TRUE) :
                        $p_detail['Price_Per_Unit'] = $info[0]->Price_Per_Unit;
                        $p_detail['Unit_Price_Id'] = $info[0]->Unit_Price_Id;
                        $p_detail['All_Price'] = $info[0]->Price_Per_Unit * str_replace(",", "", ($rows->Confirm_Qty));
                    endif;

                    // Add condition from reject for create relocation item for some item that picking already.
                    if (!empty($rows->Confirm_Qty)) {
                        if ( $allDetail ) {
                            $all_product_move[] = $p_detail;
                        } else {
                            if ( strtoupper ($rows->Activity_Code) == 'PICKING' && (int) $rows->Confirm_Qty > 0) {
                                $all_product_move[] = $p_detail;
                            }
                        }
                    }

                endforeach; // close for each
            endif; // close if have prod

           if (!empty($all_product_move)) :
                /**
                 * Create new ReLocation OrderDetail
                 */
                $result_addReLocationOrderDetail = $this->rl->addReLocationOrderDetail($all_product_move);
                if ($result_addReLocationOrderDetail <= 0):
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not Create new ReLocation OrderDetail.";
                endif;
            endif;

        endif;

        return $return;
    }

    function get_picking_detail(){

        $conf = $this->config->item('_xml');

        $flow_id = $this->input->post('flow_id');
        $set_order_by = $this->input->post('set_order_by');

        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order'); // Edit by Ton! 20130814
        $process_id = $flow_detail[0]->Process_Id;

        $order_data = $this->preDispModel->queryDataFromFlowId($flow_id);

        foreach ($order_data as $OrderObject) {
            $order_data_value = $OrderObject;
        }

//        $set_order_by = "pallet";

        if ($set_order_by == "location") {
            $order_by = "L1.Location_Code ASC";
            if($this->config->item('build_pallet')):
                $order_by .= ", a.Pallet_Id ASC";
            endif;
            $order_by .= ", a.Item_Id ASC";
        } else if ($set_order_by == "pallet") {
            $order_by = "a.Pallet_Id ASC, a.Item_Id ASC";
        } else {
            $order_by = "a.Item_Id ASC";
        }


        /**
         * GET Activity By  : show pick by
         * @add code by kik : get sub_module for show activity by
         */
        $module_activity = 'picking';
        $sub_module_activity = $this->order_movement->get_submodule_activity($order_data_value->Order_Id,$module_activity,'cf_pick_by');
        //end of get activity by

        #get Order Detail
        $order_detail_data = $this->preDispModel->query_from_OrderDetailId($order_data_value->Order_Id ,$order_by,false,$sub_module_activity,$module_activity);
        $order_detail_data = $order_detail_data->result(); // Add By Akkarapol, 21/10/2013, เน€เธเนเธ•เธเนเธฒเธ•เธฑเธงเนเธเธฃเนเธซเธกเน เน€เธเธฃเธฒเธฐเธเนเธฒเธ—เธตเน Return เธกเธฒเธเธฒเธเธเธฑเธเธเนเธเธฑเนเธ query_from_OrderDetailId เธเธฑเนเธเธชเนเธเธกเธฒเน€เธเนเธเนเธเธ $query เน€เธฅเธข เน€เธเธทเนเธญเนเธซเนเธฃเธญเธเธฃเธฑเธเธเธฒเธฃเน€เธฃเธตเธขเธเนเธเนเธเธฒเธเธ—เธตเนเธซเธฅเธฒเธเธซเธฅเธฒเธขเธเธถเนเธ
        foreach ($order_detail_data as $OrderDetailObject) {
            $order_detail_data_value = $OrderDetailObject;
            if ($OrderDetailObject->Actual_Location == "" || $OrderDetailObject->Actual_Location == NULL) :
                $order_detail_data_value->Activity_By_Name = "";
            endif;
        }


        #ADD BY POR 2014-06-11 เพิ่มให้สามารถกรอกราคาและเลือกหน่วยได้
        $price = '';

        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        if ($conf_price_per_unit):
            //$price = ',null';  //COMMENT BY POR 2014-06-02


            $price = " ,{
                    sSortDataType: \"dom-text\",
                    sType: \"numeric\",
                    type: 'text',
                    onblur: \"submit\",
                    event: 'click',
                    is_required: true,
                    loadfirst: true,
                    cssclass: \"required number\",
                    sUpdateURL: '" . site_url() . '/pre_dispatch/saveEditedRecord' . "',
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
                        var oTable = $('#ShowDataTableForInsert').dataTable();
                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                        oTable.fnUpdate(value, rowIndex, ci_unit_price_id);
                        return value;
                    }
                }
                ";

            //END ADD;
        else:
            $price = "  , {
                    onblur: 'submit',
                    sUpdateURL: '" . site_url() . '/pre_dispatch/saveEditedRecord' . "',
                    event: 'click',
                }";
            $unitofprice = ',null';
        endif;
        #END

        $view['process_id'] = $process_id;  // result  #edit by kik (20-09-2013)
        $view['order_detail_data'] = $order_detail_data;  // result  #edit by kik (20-09-2013)
        $view['price'] = $price;  // result  #edit by kik (20-09-2013)
        $view['unitofprice'] = $unitofprice;  // result  #edit by kik (20-09-2013)
        $view['price_per_unit'] = $conf_price_per_unit;  // result  #edit by kik (20-09-2013)
        $view['set_order_by'] = $set_order_by;  // result  #edit by kik (20-09-2013)
//        $view['form_value'] = $search;
        $this->load->view("form/view_picking", $view);


    }

    function quickApproveAction() {


        $this->load->model("workflow_model", "flow");

        /**
         * set Variable
         */
        $check_not_err = TRUE;
        $return = array();

        /**
         * Start token
         */
        #Retrive Data from Table
        $token = $this->input->post('token');

        $flow_id = $this->input->post('flow_id');
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        if (empty($flow_info)) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = false;

        else :

            $process_id = $flow_info['0']->Process_Id;
            $present_state = $flow_info['0']->Present_State;

            // validate token
            $response = validate_token($token, $flow_id, $present_state, $process_id);


            if (!$response) :
                $array_return['critical'][]['message'] = "Document already pass this step.";
                $json['status'] = "save";
                $json['return_val'] = $array_return;
                $check_not_err = false;
            endif;
        endif;

        /**
         * ================== Start Transaction =========================
         */
        $this->transaction_db->transaction_start();


        /**
         * update Process
         */

        if ($check_not_err):
            $respond = $this->_updateProcess($this->input->post(), TRUE);
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

            $set_return['message'] = "Approve Picking Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Approve Picking Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
        endif;

        echo json_encode($json);
		exit();
    }

    function mQuickApproveAction() {

    	$this->load->model("workflow_model", "flow");
    	$check_not_err = TRUE;
    	$return = array();

    	$flow_list = json_decode($this->input->post("flow_id"));

    	/**
    	 * ================== Start Transaction =========================
    	*/
    	$this->transaction_db->transaction_start();

    	foreach (@$flow_list as $fid) {

    		$data = Array();
    		$data['flow_id'] = $fid;

    		$flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $fid));

    		#get Data Order
    		$order_where['Flow_Id'] = $fid;
    		$header = $this->preDispModel->get_document_property($fid);
    		$data['order_id'] = $header['0']->Order_Id;
    		$data['flow_id'] = $header['0']->Flow_Id;
    		$data['process_id'] = $header['0']->Process_Id;
    		$data['present_state'] = $header['0']->Present_State;
    		$data['process_type'] = $header['0']->Process_Type;

    		#get Data Order Detail
    		$data['content'] = $this->preDispModel->get_document_data($header['0']->Order_Id);

    		/**
    		 * update Process
    		*/

    		if ($check_not_err):
    		$respond = $this->_update_quick_approve($data, TRUE);
    		if (!empty($respond['critical'])) :
    		$check_not_err = FALSE;

    		/**
    		 * Set Alert Zone (set Error Code, Message, etc.)
    		 */
    		$return['critical'][]['message'] = "Can not update Process.";
    		$return = array_merge_recursive($return, $respond);
    		endif;
    		endif;

    	}

    	/**
    	 * check if for return json and set transaction
    	 */
    	if ($check_not_err):
    	/**
    	 * ================== Auto End Transaction =========================
    	*/
    	$this->transaction_db->transaction_end();

    	$set_return['message'] = "Approve Picking Complete.";
    	$return['success'][] = $set_return;
    	$json['status'] = "save";
    	$json['return_val'] = $return;
    	else:
    	/**
    	 * ================== Rollback Transaction =========================
    	*/
    	$this->transaction_db->transaction_rollback();

    	$array_return['critical'][]['message'] = "Approve Picking Incomplete.";
    	$json['status'] = "save";
    	$json['return_val'] = array_merge_recursive($array_return, $return);
    	endif;

    	echo json_encode($json);
    	exit();
    }

    /**
     * Consolidate Picking
     * Generate PDF by grouping from picking check box
     *
     * @return view pdf view
     */

    function consolidate_picking ()
    {
        date_default_timezone_set('Asia/Bangkok');

        // Manual Load Config and Library
        $this->load->library('mpdf/mpdf');

        $this->load->model("report_model", "r");
        $this->load->model("company_model", "company");
        $this->load->model("system_management_model", "sys");
        $this->load->model("user_model", "user");
        $this->load->model("pre_dispatch_model", "preDispModel");

        // Set variable for HTTP Request
        $params = $this->input->get();

        $conf = $this->config->item('_xml');
        $conf_show_column = empty($conf['show_column_report']['object']['consolidate_picking'])?array():@$conf['show_column_report']['object']['consolidate_picking'];

        $column_result = colspan_report($conf_show_column,$conf);
        $view['all_column'] = $column_result['all_column'];
        $view['colspan'] = $column_result['colspan_all'];
        $view['show_hide'] = json_decode($column_result['show_hide']);
        $view['set_css_for_show_column'] =$column_result['set_css_for_show_column'];

        $search['o'] = ( isset( $params['o'] ) ? $params['o'] : NULL );

        $search['document_no'] = $document_no = $this->flow->get_document_from_flow( json_decode( $params['flow_id'] ) );

        $order_data = $this->flow->getOrderDetailByDocumentNoIn( $document_no );

        $order_id = Array();
        $order_id_map = Array();
        $order_map_by_id = Array();
        foreach ( $order_data as $idx => $val ) :
            $order_id[]  = $val->Order_Id;
            $order_map_by_id[$val->Order_Id]['Document_No'] = $val->Doc_Refer_Ext;
            $order_id_map[$val->Doc_Refer_Ext]['State_NameTh'] = $val->State_NameTh;
        endforeach;

        $order_detail_data = $this->preDispModel->query_from_OrderDetailId( $order_id ,'b.Product_Code ASC',FALSE,FALSE,FALSE);

        $pdf_data = Array();
        //$format_order_detail = formatDataForGroupDispatch( $order_detail_data->result() , $pdf_data , $column_result , $order_map_by_id);
        $format_order_detail = formatDataForGroupPicking( $order_detail_data->result() , $pdf_data , $column_result , $order_map_by_id);

        if( empty( $format_order_detail ) ):
            echo 'Sorry, can not find data.';
            exit();
        endif;

        $search['showfooter'] = 'show';
        $flow_detail = $this->flow->getFlowDescription( $order_data[0]->Flow_Id );
        $view['state_description'] = $flow_detail['0']->State_Description;
        $view['document_list'] = $order_data;
        $view['order_mapping'] = $order_id_map;
        $view['data'] = $format_order_detail;

        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $view['printBy'] = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['revision'] = $this->revision;
        $view['price_per_unit'] = $conf_price_per_unit;
        $view['build_pallet'] = $this->config->item('build_pallet');
        $this->load->view("report/exportConsolidatePicking", $view);
    }

    function delivery_note (){
    
        date_default_timezone_set('Asia/Bangkok');

        // Manual Load Config and Library
        $this->load->library('mpdf/mpdf');

        $this->load->model("report_model", "r");
        $this->load->model("company_model", "company");
        $this->load->model("system_management_model", "sys");
        $this->load->model("user_model", "user");
        $this->load->model("pre_dispatch_model", "preDispModel");

        // Set variable for HTTP Request
        $params = $this->input->get();

        $conf = $this->config->item('_xml');
        $conf_show_column = empty($conf['show_column_report']['object']['consolidate_picking'])?array():@$conf['show_column_report']['object']['consolidate_picking'];
        // $view['signature_report'] = (isset($conf['signature_report']['dn_report']) && !empty($conf['signature_report']['dn_report']) ? $conf['signature_report']['dn_report'] : array() );
        $column_result = colspan_report($conf_show_column,$conf);
        $view['all_column'] = $column_result['all_column'];
        $view['colspan'] = $column_result['colspan_all'];
        $view['show_hide'] = json_decode($column_result['show_hide']);
        $view['set_css_for_show_column'] =$column_result['set_css_for_show_column'];

        $search['o'] = ( isset( $params['o'] ) ? $params['o'] : NULL );

        $search['document_no'] = $document_no = $this->flow->get_document_from_flow( json_decode( $params['flow_id'] ) );
        
        $order_data = $this->flow->getOrderDetailByDocumentNoIn( $document_no );
        
        foreach ( $order_data as $idx => $val ) {
            
            $Doc_int = $val->Doc_Refer_Int;
            $document_No =  $val->Document_No;
            
            if (strlen($Doc_int) > 0) {
                $aData[$Doc_int][] = $val;
            }

            
        }
    
        $order_data1 = $this->flow->getDNData($document_no);
        
        foreach ( $order_data1 as $idx => $vall ) {

            $Doc_no = $vall->Document_No;
            $Doc_int = $vall->Doc_Refer_Int;

            if (strlen($Doc_int) > 0){
                $Data_detail[$Doc_int][] = $vall;
            }

        }

        // p($aData);
        // p($Data_detail);exit;
    
        $view['showfooter'] = 'show';
        $view['document_list'] = $aData;
        $view['data'] = $Data_detail;

        // p($view['document_list']['21/E0001']);
        // p($view['data']['21/E0001']);
        // exit;

        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $view['printBy'] = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['revision'] = $this->revision;
        $view['price_per_unit'] = $conf_price_per_unit;
        $view['build_pallet'] = $this->config->item('build_pallet');
        foreach ($view['data'] as $key2 => $data) {
            $res[$key2] = $data;
            $std = $view['document_list'][$key2];
        }
        // p($res);exit;
        // foreach ($res as $key => $print) {
            // $view['datas'] = $print;
            $view['datas'] = $res;
            $this->load->view("report/exportDelivery_note", $view);
        // }

  
    }

    function _update_quick_approve($params, $is_quick_approve = FALSE) {

    	# Update Order and Order Detail
    	$this->load->model("stock_model", "stock");
    	$this->load->model("workflow_model", "flow");

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

    	$flow_id = $params["flow_id"];
    	$order_id = $params["order_id"];
    	$process_id = $params["process_id"];
    	$present_state = $params["present_state"];
    	$process_type = $params["process_type"];
    	$action_type = "";
    	$next_state = 5;
    	$user_id = $this->session->userdata("user_id");

    	/**
    	 * update Workflow
    	*/
    	if ($check_not_err):
    	$action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, NULL);
    	if (empty($action_id) || $action_id == ''):
    	$check_not_err = FALSE;
    	$return['critical'][]['message'] = "Can not update Workflow.";
    	endif;
    	endif;

    	/**
    	 * update Order Detail
    	 */
    	if ($check_not_err):
    	$order_detail = array();
    	if (!empty($params['content'])) :
    	foreach ($params['content'] as $rows) :

    	$detail = array();

    	$detail['Activity_Code'] = 'PICKING';
    	$detail['Activity_By'] = $this->session->userdata("user_id");
    	$detail['Activity_Date'] = date("Y-m-d H:i:s");
    	$detail['Actual_Location_Id'] = $rows->Suggest_Location_Id;
    	$detail['Confirm_Qty'] = $rows->Reserv_Qty;

    	unset($where);
    	$where['Item_Id'] = $rows->Item_Id;
    	$where['Order_Id'] = $order_id;
    	$where['Product_Code'] = $rows->Product_Code;
    	$where['Product_Id'] = $rows->Product_Id;
    	/**
    	 * update Order Detail
    	 */
    	$result_updateOrderDetail = $this->stock->updateOrderDetail($detail, $where);
    	if (!$result_updateOrderDetail):
    	$check_not_err = FALSE;

    	/**
    	 * Set Alert Zone (set Error Code, Message, etc.)
    	 */
    	$return['critical'][]['message'] = "Can not update Order Detail.";
    	break;
    	endif;

    	endforeach;

    	endif;
    	endif;

    	//return "C001";
    	return $return;
    }

}
