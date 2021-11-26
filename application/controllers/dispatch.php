<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Dispatch extends CI_Controller {

    public $settings;

    public function __construct() {
        parent::__construct();
        $this->settings = native_session::retrieve();
        $this->load->model("workflow_model", "flow");
        $this->load->model("re_location_model", "rl");
        $this->load->model("invoice_model", "invoice");
        $this->load->model("container_model", "container");
        $this->load->model("order_movement_model", "order_movement");
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));
        $isUserLogin = $this->session->userdata("user_id");
        $status = @shell_exec('svnversion ' . realpath(__FILE__));
        if (preg_match('/\d+/', $status, $match)) {
            $this->revision = $match[0];
        }
    }

    /**
     *
     * 
     */
    function openActionForm() {
        $this->output->enable_profiler($this->config->item('set_debug'));

        $this->load->model("workflow_model", "flow");
        $this->load->model("pre_dispatch_model", "preDispModel");
        $this->load->model("contact_model", "contact");
        $this->load->model("company_model", "company");
        $this->load->model("system_management_model", "sys");
        $this->load->model("stock_model", "stock");


        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $conf_invoice_require = empty($conf['invoice_require']) ? false : @$conf['invoice_require'];
        $conf_change_dp_date = empty($conf['can_change_dispatch_date']) ? false : @$conf['can_change_dispatch_date'];


        $conf_pallet_tally = empty($conf['build_pallet_tally_sheet_outbound']) ? false : @$conf['build_pallet_tally_sheet_outbound'];
        $flow_id = $this->input->post("id");
        $owner_id = $this->config->item('owner_id');
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order');
        $process_id = $flow_detail[0]->Process_Id;
        $present_state = $flow_detail[0]->Present_State;
        $document_no = $flow_detail[0]->Document_No;
        $module = $flow_detail[0]->Module;

        $process_step = "";
        if ($process_id == 2 and $present_state == 5):
            $process_step = 'confirm';
        endif;

        // validate document exist state
        $valid_state = validate_state($module);
        if ($valid_state) :
            redirect($valid_state);
        endif;

        $parameter['token'] = register_token($flow_id, $present_state, $process_id);

        $order_id = $flow_detail[0]->Order_Id;
        $renter_id = $flow_detail[0]->Renter_Id;
        $warehouse_from = $flow_detail[0]->Source_Id;
        $warehouse_to = $flow_detail[0]->Destination_Id;
        $receive_type = $flow_detail[0]->Doc_Type;
        $est_dispatch_date = $flow_detail[0]->Est_Action_Date;
        $Real_Action_Date = $flow_detail[0]->Real_Action_Date;
        $dispatch_date = $flow_detail[0]->Action_Date;
        $Doc_Refer_Ext = $flow_detail[0]->Doc_Refer_Ext;
        $Doc_Refer_Int = $flow_detail[0]->Doc_Refer_Int;
        $Doc_Refer_Inv = $flow_detail[0]->Doc_Refer_Inv;
        $Doc_Refer_CE = $flow_detail[0]->Doc_Refer_CE;
        $Doc_Refer_BL = $flow_detail[0]->Doc_Refer_BL;
        $remark = $flow_detail[0]->Remark;
        $vendor_id = $flow_detail[0]->Vendor_Id;
        $driver_name = $flow_detail[0]->Vendor_Driver_Name;
        $car_no = $flow_detail[0]->Vendor_Car_No;
        $is_urgent = $flow_detail[0]->Is_urgent;

        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowDispatchList");
        $order_data = $this->preDispModel->queryDataFromFlowId($flow_id);

        foreach ($order_data as $OrderObject) {
            $order_data_value = $OrderObject;
        }

        $module_activity = 'picking';
        $sub_module_activity = $this->order_movement->get_submodule_activity($order_data_value->Order_Id, 'picking', $module_activity);

        $order_by = 'a.Product_Code';
        $order_detail_data = $this->preDispModel->query_from_OrderDetailId($order_data_value->Order_Id, $order_by, true, $sub_module_activity, $module_activity);
        $order_detail_data = $order_detail_data->result();

        foreach ($order_detail_data as $OrderDetailObject) {
            $order_detail_data_value = $OrderDetailObject;
        }

        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY");

        $q_shipper = $this->company->getOwnerAll();
        $r_shipper = $q_shipper->result();
        $shipper_list = genOptionDropdown($r_shipper, "COMPANY");

        //START BY POR 2013-10-08 เรียก Consignee Id ให้ถูกต้อง
        $q_consignee = $this->company->getBranchNotOwner($this->session->userdata('branch_id'));
        //END

        $r_consignee = $q_consignee->result();
        $consignee_list = genOptionDropdown($r_consignee, "COMPANY");

        #Get Dispatch Type list
        $dispatch_type = $this->sys->getDispatchType();
        $dispatch_type_list = genOptionDropdown($dispatch_type, "SYS");

        #Get Vendor [Company Vendor] list
        $q_vendor = $this->company->getVendorAll();
        $r_vendoe = $q_vendor->result();
        $vendor_list = genOptionDropdown($r_vendoe, "COMPANY");

        $renter_id_select = form_dropdown("renter_id_select", $renter_list, $order_data_value->Renter_Id, "id=renter_id_select class=required");
        $frm_warehouse_select = form_dropdown("frm_warehouse_select", $shipper_list, $order_data_value->Source_Id, "id=frm_warehouse_select class=required");
        $to_warehouse_select = form_dropdown("to_warehouse_select", $consignee_list, $order_data_value->Destination_Id, "id=to_warehouse_select class=required");
        $dispatch_type_select = form_dropdown("dispatch_type_select", $dispatch_type_list, $order_data_value->Doc_Type, "id=dispatch_type_select class=required");

        $query = $this->preDispModel->getPreDispatchAll();
        $pre_dispatch_list = $query->result();

        // register token
        $token = register_token($flow_id, $present_state, $process_id);
        // end config token
        #ADD BY POR 2014-06-11 เพิ่มให้สามารถกรอกราคาและเลือกหน่วยได้
        $price = '';

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
        //ADD BY POR 2014-09-09 get data show in container
        $doc_refer_container = array();
        $container_name = $this->container->getContainerByOrderIdOutbound($order_id)->result_array();
        if (!empty($container_name)):
            foreach ($container_name as $key_container => $container):
                $doc_refer_container[] = $container['Cont_No'] . " " . $container['Cont_Size_No'] . $container['Cont_Size_Unit_Code'];
            endforeach;
        endif;

        //ADD BY POR 2014-09-09 select container size for size list in container
        $container_size = $this->container->getContainerSize()->result();
        $_container_size = array();
        foreach ($container_size as $idx => $value) {
            $_container_size[$idx]['Id'] = $value->Cont_Size_Id;
            $_container_size[$idx]['No'] = $value->Cont_Size_No;
            $_container_size[$idx]['Unit_Code'] = $value->Cont_Size_Unit_Code;
        }
        $container_size_list = json_encode($_container_size);

        $container_list = $this->container->getContainerByOrderIdOutbound($order_id)->result();
        $_container_list = array();

        foreach ($container_list as $idx => $value) {
            $_container_list[$idx]['id'] = $value->Cont_Id;
            //$_container_list[$idx]['name'] = $value->Cont_No; comment by ball : 2014-11-27
            $_container_list[$idx]['name'] = iconv("tis-620", "utf-8", $value->Cont_No); //set read thai language :BY AKK 2014-12-11
            $_container_list[$idx]['size'] = $value->Cont_Size_Id;
        }
        $arrayobject = new ArrayObject($_container_list);
        $container_list = json_encode($arrayobject);

        $show_tally = FALSE;
        foreach ($order_detail_data as $key => $value):
            if (!empty($value->Cont_Id)):
                $show_tally = TRUE;
                break;
            endif;
        endforeach;

        list($group_tableList, $dispatch_group_item_by_product_code, $dispatch_group_item_by_lot, $dispatch_group_item_by_serial) = $this->grouping_item($order_detail_data);
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
            , "order_detail_data" => $order_detail_data
            , "remark" => trim($remark)
            , "est_action_date" => trim($est_dispatch_date)
            , "Real_Action_Date" => trim($Real_Action_Date)
            , "dispatch_date" => $dispatch_date
            , "flow_id" => $flow_id
            , "order_id" => $order_id
            , "owner_id" => $owner_id
            , "vendor_list" => $vendor_list
            , "vendor_id" => $vendor_id
            , "driver_name" => $driver_name
            , "car_no" => $car_no
            , "data_form" => (array) $data_form
            , "is_urgent" => $is_urgent
            , "token" => $token
//            , "price_per_unit" => $this->settings['price_per_unit'] //add by kik : 20140114
            , 'price' => $price
            , 'unitofprice' => $unitofprice
            , 'statusprice' => $conf_price_per_unit
            , 'conf_inv' => $conf_inv
            , 'conf_cont' => $conf_cont
            , 'conf_pallet' => $conf_pallet
            , 'conf_invoice_require' => $conf_invoice_require
            , 'conf_change_dp_date' => $conf_change_dp_date
            , 'doc_refer_container' => $doc_refer_container
            , 'container_size_list' => $container_size_list
            , 'container_list' => $container_list
            , 'check_container' => $_container_list
            , "group_tableList" => $group_tableList
            , "dispatch_group_item_by_product_code" => $dispatch_group_item_by_product_code
            , "dispatch_group_item_by_lot" => $dispatch_group_item_by_lot
            , "dispatch_group_item_by_serial" => $dispatch_group_item_by_serial
            , "process_step" => $process_step
                )
                , TRUE);

        #<<in case xml config build_pallet and build_pallet_tally_sheet == true =====> show tally sheet button : ADD BY POR 2015-07-14 (only approve dispatch)
        $tally_button = '';
        if ($process_id == 2 && $present_state == 6 && $conf_pallet && $conf_pallet_tally && $conf_cont && $show_tally):
            $tally_button = '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . _lang("tally_sheet") . '" title="' . _lang("tally_sheet") . '"  ONCLICK="exportFile(\'tally\')">';
        endif;
        #>>end case show tally sheet button


        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmDispatch"></i>'
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_action' => $data_form->str_buttun . $tally_button
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
        $param = $this->input->post();
        /**
         * Start token
         */
        #Retrive Data from Table
        $token = $param['token'];

        $flow_id = $param['flow_id'];
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        if (empty($flow_info)) :
            $set_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $set_return;
            $check_not_err = false;

        else :

            $process_id = $flow_info['0']->Process_Id;
            $present_state = $flow_info['0']->Present_State;

            // validate token
            $response = validate_token($token, $flow_id, $present_state, $process_id);


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
         * update Process
         */
        if ($check_not_err):
            $respond = $this->_updateProcess($param);
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

            $set_return['message'] = "Confirm Dispatch Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Confirm Dispatch Incomplete.";
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

        $param = $this->input->post();
//        p($param);exit();
        /**
         * Start token
         */
        #Retrive Data from Table
        $token = $param['token'];

        $flow_id = $param['flow_id'];
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        if (empty($flow_info)) :
            $set_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $set_return;
            $check_not_err = false;

        else :

            $process_id = $flow_info['0']->Process_Id;
            $present_state = $flow_info['0']->Present_State;

            // validate token
            $response = validate_token($token, $flow_id, $present_state, $process_id);


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
         * update Process
         */
        if ($check_not_err):
            $respond = $this->_updateProcess($param);
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
         * create Stock Dispatch Order
         */
        if ($check_not_err):
            $this->load->library('Stock_lib');
            $respond = $this->stock_lib->addStockDispatchOrder($param['order_id']);

            if (!empty($respond['critical'])) :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not create Stock Dispatch Order.";
                $return = array_merge_recursive($return, $respond);
            endif;
        endif;


        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):

            /**
             * check transaction_status by kik : 20140530
             */
            if ($this->transaction_db->transaction_status() === FALSE) :
                /**
                 * ================== Rollback Transaction =========================
                 */
                $this->transaction_db->transaction_rollback();

                $set_return['critical'][]['message'] = "Approve Dispatch Incomplete.";
                $json['status'] = "save";
                $json['return_val'] = array_merge_recursive($set_return, $return);

            else:
                /**
                 * ================== Auto End Transaction =========================
                 */
                $this->transaction_db->transaction_end();

                // CREATE POD
                $this->createPODFile($this->input->post());
                // END CREATE POD

                if ($this->config->item('approve_print_dispatch')) :
                    $set_return['document_no'] = $param['document_no'];
                    $json['status'] = "X003";
                else:
                    $json['status'] = "save";
                endif;

                $set_return['message'] = "Approve Dispatch Complete.";
                $return['success'][] = $set_return;
                $json['return_val'] = $return;
            endif;


        else :
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $set_return['critical'][]['message'] = "Approve Dispatch Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($set_return, $return);
        endif;


        echo json_encode($json);
    }

    function quickApproveAction() {

        /**
         * set Variable
         */
        $check_not_err = TRUE;
        $return = array();
        $param = $this->input->post();
//        p($param);exit();
        /**
         * Start token
         */
        #Retrive Data from Table
        $token = $param['token'];

        $flow_id = $param['flow_id'];
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));




        if (empty($flow_info)) :
            $set_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $set_return;
            $check_not_err = false;

        else :

            $process_id = $flow_info['0']->Process_Id;
            $present_state = $flow_info['0']->Present_State;

            // validate token
            $response = validate_token($token, $flow_id, $present_state, $process_id);


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
//        $this->transaction_db->transaction_start();


        /**
         * update Process
         */
        if ($check_not_err):
            $respond = $this->_updateProcess($param);
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
         * create Stock Dispatch Order
         */
        if ($check_not_err):
            $this->load->library('Stock_lib');
            $respond = $this->stock_lib->addStockDispatchOrder($param['order_id']);
            if (!empty($respond['critical'])) :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not create Stock Dispatch Order.";
                $return = array_merge_recursive($return, $respond);
            endif;
        endif;


        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):

            /**
             * check transaction_status by kik : 20140530
             */
            if ($this->transaction_db->transaction_status() === FALSE) :
                /**
                 * ================== Rollback Transaction =========================
                 */
                $this->transaction_db->transaction_rollback();

                $set_return['critical'][]['message'] = "Approve Dispatch Incomplete.";
                $json['status'] = "save";
                $json['return_val'] = array_merge_recursive($set_return, $return);

            else:
                /**
                 * ================== Auto End Transaction =========================
                 */
                $this->transaction_db->transaction_end();

                if ($this->config->item('approve_print_dispatch')) :
                    $set_return['document_no'] = $param['document_no'];
                    $json['status'] = "X003";
                else:
                    $json['status'] = "save";
                endif;

                $set_return['message'] = "Approve Dispatch Complete.";
                $return['success'][] = $set_return;
                $json['return_val'] = $return;
            endif;


        else :
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $set_return['critical'][]['message'] = "Approve Dispatch Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($set_return, $return);
        endif;


        echo json_encode($json);
    }

    function approveTransferStock() {

        /**
         * set Variable
         */
        $check_not_err = TRUE;
        $return = array();
        $param = $this->input->post();
        /**
         * Start token
         */
        #Retrive Data from Table
        $token = $param['token'];

        $flow_id = $param['flow_id'];
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        if (empty($flow_info)) :
            $set_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $set_return;
            $check_not_err = false;

        else :

            $process_id = $flow_info['0']->Process_Id;
            $present_state = $flow_info['0']->Present_State;

            // validate token
            $response = validate_token($token, $flow_id, $present_state, $process_id);


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
         * update Process
         */
        if ($check_not_err):
            $respond = $this->_updateProcess($param);
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
         * create Stock Dispatch Order
         */
        if ($check_not_err):
            $this->load->library('Stock_lib');
            $respond = $this->stock_lib->addStockDispatchOrder($param['order_id']);
            if (!empty($respond['critical'])) :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not create Stock Dispatch Order.";
                $return = array_merge_recursive($return, $respond);
            endif;
        endif;


        /**
         * create Transfer Stock
         */
        if ($check_not_err):
            $insert_to_inbound = $this->stock_lib->addTransferStock($param['order_id']);
            if (!empty($insert_to_inbound['critical'])) :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not create Transfer Stock.";
                $return = array_merge_recursive($return, $insert_to_inbound);
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

            if ($this->config->item('approve_print_dispatch')) :
                $set_return['document_no'] = $param['document_no'];
                $json['status'] = "X003";
            else:
                $json['status'] = "save";
            endif;

            $set_return['message'] = "Approve Dispatch Complete.";
            $return['success'][] = $set_return;
            $json['return_val'] = $return;
        else :
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $set_return['message'] = "Approve Dispatch Incomplete.";
            $return['critical'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        endif;

        echo json_encode($json);
    }

    function _updateProcess($param = null) {
        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $conf_change_dp_date = empty($conf['can_change_dispatch_date']) ? false : @$conf['can_change_dispatch_date']; //add real dispatch date for ISSUE 5265 : kik : 20141020
//        p($param);exit();
        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;

        $flow_id = $param['flow_id'];
        $order_id = $param['order_id'];
        $process_id = $param['process_id'];
        $present_state = $param['present_state'];
        $process_type = $param['process_type'];
        $action_type = $param['action_type'];
        $next_state = $param['next_state'];
        $user_id = $this->session->userdata("user_id");

        # Parameter of document number
        $document_no = $param['document_no'];
        $doc_refer_int = $param['doc_refer_int'];
        $doc_refer_ext = $param['doc_refer_ext'];
        $doc_refer_inv = $param['doc_refer_inv'];
        $doc_refer_ce = $param['doc_refer_ce'];
        $doc_refer_bl = $param['doc_refer_bl'];

        # Parameter Order Document
        $owner_id = $param['owner_id'];
        $renter_id = $param['renter_id_select'];
        $shipper_id = $param['frm_warehouse_select'];
        $consignee_id = $param['to_warehouse_select'];

//        Defect ID : 270 #1
//        DATE:2013-08-23
//        BY : Akkarapol
//        Desc : เพิ่ม ฟิลด์ Vendor_Driver_Name กับ Vendor_Car_No เพราะถ้าไม่เพิ่ม มันก็จะไม่เซฟ เข้า DB
        $driver_name = $param['driver_name'];
        $car_no = $param['car_no'];
//        END Defect ID : 270
        $remark = $param['remark'];
        $dispatch_type = $param['dispatch_type_select'];
        $est_dispatch_date = $param['estDispatchDate'];

        //add real dispatch date for ISSUE 5265 : kik : 20141020
        if ($conf_change_dp_date):
            $real_dispatch_date = $param['realDispatchDate'];
        else:
            $real_dispatch_date = date("Y-m-d H:i:s");
        endif;

        $is_urgent = @$param['is_urgent'];   //add for ISSUE 3312 : by kik : 20140120
        # Parameter Order Detail
        $prod_list = $param['prod_list'];


        # Parameter Index Datatable
        $ci_prod_code = $param['ci_prod_code'];
        $ci_lot = $param['ci_lot'];
        $ci_serial = $param['ci_serial'];
        $ci_mfd = $param['ci_mfd'];
        $ci_exp = $param['ci_exp'];
        $ci_invoice = $param['ci_invoice'];
        $ci_container = $param['ci_container'];
        $ci_cont_id = $param['ci_cont_id'];
        $ci_reserv_qty = $param['ci_reserv_qty'];
        $ci_confirm_qty = $param['ci_confirm_qty'];
        $ci_remark = $param['ci_remark'];
        $ci_prod_id = $param['ci_prod_id'];
        $ci_prod_status = $param['ci_prod_status'];
        $ci_unit_id = $param['ci_unit_id'];
        $ci_item_id = $param['ci_item_id'];
        $ci_inbound_id = $param['ci_inbound_id'];
        $ci_suggest_loc = $param['ci_suggest_loc'];
        $ci_actual_loc = $param['ci_actual_loc'];
        $ci_prod_sub_status = $param['ci_prod_sub_status'];

        //ADD BY KIK 2014-01-14 เพิ่มเกี่ยวกับราคา
        if ($conf_price_per_unit == TRUE) {
            $ci_price_per_unit = $param['ci_price_per_unit'];
            $ci_unit_price = $param['ci_unit_price'];
            $ci_all_price = $param['ci_all_price'];
            $ci_unit_price_id = $param['ci_unit_price_id'];
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
                , 'Actual_Action_Date' => date("Y-m-d H:i:s")
                , 'Real_Action_Date' => $real_dispatch_date//add real dispatch date for ISSUE 5265 : kik : 20141020
                , 'Source_Id' => $shipper_id
                , 'Destination_Id' => $consignee_id
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Modified_By' => $user_id
                , 'Modified_Date' => date("Y-m-d H:i:s")
                , 'Is_urgent' => $is_urgent
                , 'Vendor_Driver_Name' => iconv("UTF-8", "TIS-620", $driver_name)
                , 'Vendor_Car_No' => iconv("UTF-8", "TIS-620", $car_no)
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

            if (!empty($prod_list)) :

                foreach ($prod_list as $rows) :
                    $a_data = explode(SEPARATOR, $rows);
                    $is_new = $a_data[$ci_item_id];

                    if ($is_new != "" || $is_new != 0):

                        $detail = array();
                        $detail['Activity_Code'] = 'DISPATCH';      // Add By Akkarapol, 12/09/2013, เพิ่มใส่ค่าเข้าไปใน ฐานข้อมูล จะได้เรียกเอามาใช้ทีหลังได้ เนื่องจาก Activity_Code เกี่ยวเนื่องกับ Process หลายๆตัวด้วยกัน
                        $detail['Activity_By'] = $this->session->userdata("user_id");      // Add By Akkarapol, 12/09/2013, เพิ่มใส่ค่าเข้าไปใน จะได้รู้ว่าใครเป็นคนทำ Activity นี้
                        $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                        $detail['Inbound_Item_Id'] = $a_data[$ci_inbound_id];

                        //ADD BY POR 2014-09-10 : check invoice for add or edit
                        if ($conf_inv):
                            $Invoice_Id = NULL;
                            if (!empty($a_data[$ci_invoice])):
                                //หา Invoice_Id ว่า Invoice_No นี้มีหรือยัง ถ้ามีแล้วให้ใช้ Invoice_Id เดิมที่มี
                                $Invoice = $this->invoice->getInvoiceIdByInvoiceNo($order_id, $a_data[$ci_invoice])->result_array();

                                $Invoice_Id = (isset($Invoice[0]) ? $Invoice[0]['Invoice_Id'] : "" );

                                if (empty($Invoice_Id)):
                                    $Invoice_Id = $this->invoice->insertInvoiceByDetail($order_id, $a_data[$ci_invoice]);

                                    if ($Invoice_Id == 0):
                                        $check_not_err = FALSE;

                                        /**
                                         * Set Alert Zone (set Error Code, Message, etc.)
                                         */
                                        $return['critical'][]['message'] = "Invoice No can't insert.";

                                        break;  //ถ้าพบว่า error ให้ออกจาก loop เลย
                                    endif;
                                endif;
                            endif;

                            $detail['Invoice_Id'] = $Invoice_Id;
                        endif;
                        //END Insert Invoice

                        if ($conf_cont):
                            $detail['Cont_Id'] = $a_data[$ci_cont_id];
                        endif;

                        $detail['Confirm_Qty'] = str_replace(",", "", $a_data[$ci_reserv_qty]); //+++++ADD BY POR 2013-11-29 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                        //ADD BY KIK 2014-01-14 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                        if ($conf_price_per_unit == TRUE) :// comment by kik : 20140530
                            $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]); //+++++ADD BY KIK 2014-01-14 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                            $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                        endif;
                        //END ADD


                        if ($next_state == -2 and $present_state == 5)://check state if quick approve set DP_Confirm_Qty = Confirm_Qty : by kik : 20140923
                            $detail['DP_Confirm_Qty'] = str_replace(",", "", $a_data[$ci_reserv_qty]);

                            #calculate all price from confirm qty
                            if ($conf_price_per_unit == TRUE) :// comment by kik : 20140530
                                $All_Price = set_number_format(str_replace(",", "", $a_data[$ci_price_per_unit]) * str_replace(",", "", $a_data[$ci_reserv_qty]));
                                $detail['All_Price'] = str_replace(",", "", $All_Price);
                                unset($All_Price);
                            endif;

                        else:
                            $detail['DP_Confirm_Qty'] = str_replace(",", "", $a_data[$ci_confirm_qty]);

                            #calculate all price from dp_confirm qty
                            if ($conf_price_per_unit == TRUE) :// comment by kik : 20140530
                                $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]); //+++++ADD BY KIK 2014-01-14 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                            endif;

                        endif;

                        $where_detail['Item_Id'] = $is_new;

                        if (!empty($detail) && !empty($where)): //check not empty $detail and where by kik : 20140530
                            $result_updateOrderDetail = $this->stock->updateOrderDetail($detail, $where_detail);
                            if (!$result_updateOrderDetail):
                                $check_not_err = FALSE;

                                /**
                                 * Set Alert Zone (set Error Code, Message, etc.)
                                 */
                                $return['critical'][]['message'] = "Can not update Order Detail.";
                                break;
                            endif;
                        else:

                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not update Order Detail.";

                        endif;

                        unset($where_detail);
                        unset($detail);

                    else://no have item_id by kik : 20140530
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not update Order Detail.";
                    endif;

                endforeach;

            else://check not empty $prod_list by kik : 20140530
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update Order Detail.";

            endif;
        endif;



        //logOrderDetail($order_id, 'dispatch', $action_id, $action_type);      //COMMENT BY POR 2014-07-07
//        return "C001";
        return $return;
    }

    public function export_dispatch_pdf() {
        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

        $this->load->model("report_model", "r");
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $search['document_no'] = $this->input->get('document_no');
        $search['showfooter'] = $this->input->get('showfooter');
        $report = $this->r->getDDR($search);
        $view['data'] = $report;

        // Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        // END Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
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

        $view['price_per_unit'] = $conf_price_per_unit; //add by kik : 20140114
        $view['build_pallet'] = $this->config->item('build_pallet'); //ADD by kik 2014-01-14
//        p($view);exit();
        $this->load->view("report/exportDDR", $view);
    }

    /**
     * Quick Export Dispatch PDF
     * Generate PDF by grouping from dispatch check box
     *
     * @return view pdf view
     */
    public function quick_export_dispatch_pdf() {

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
        $conf_show_column = empty($conf['show_column_report']['object']['consolidate_dispatch']) ? array() : @$conf['show_column_report']['object']['consolidate_dispatch'];

        $column_result = colspan_report($conf_show_column, $conf);
        $view['all_column'] = $column_result['all_column'];
        $view['colspan'] = $column_result['colspan_all'];
        $view['show_hide'] = json_decode($column_result['show_hide']);
        $view['set_css_for_show_column'] = $column_result['set_css_for_show_column'];

        $search['o'] = ( isset($params['o']) ? $params['o'] : NULL );

        $search['document_no'] = $document_no = $this->flow->get_document_from_flow(json_decode($params['flow_id']));

        $order_data = $this->flow->getOrderDetailByDocumentNoIn($document_no);

        $order_id = Array();
        $order_id_map = Array();
        $order_map_by_id = Array();
        foreach ($order_data as $idx => $val) :
            $order_id[] = $val->Order_Id;
            $order_map_by_id[$val->Order_Id]['Document_No'] = $val->Document_No;
            $order_id_map[$val->Document_No]['State_NameTh'] = $val->State_NameTh;
        endforeach;

        $order_detail_data = $this->preDispModel->query_from_OrderDetailId($order_id, 'STK_M_Product.Product_Code ASC', FALSE, FALSE, FALSE);

        $pdf_data = Array();
        $format_order_detail = formatDataForGroupDispatch($order_detail_data->result(), $pdf_data, $column_result, $order_map_by_id);

        if (empty($format_order_detail)):
            echo 'Sorry, can not find data.';
            exit();
        endif;

        $search['showfooter'] = 'show';
        $flow_detail = $this->flow->getFlowDescription($order_data[0]->Flow_Id);
        $view['state_description'] = $flow_detail['0']->State_Description;
        $view['document_list'] = $order_data;
        $view['order_mapping'] = $order_id_map;
        $view['data'] = $format_order_detail;

        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $view['printBy'] = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['revision'] = $this->revision;
        $view['price_per_unit'] = $conf_price_per_unit;
        $view['build_pallet'] = $this->config->item('build_pallet');
        $this->load->view("report/exportMultipleDispatch", $view);
    }

    #ISSUE 3034 Reject Document
    #DATE:2013-12-16
    #BY:KIK
    #เพิ่มในส่วนของ reject and (reject and return)
    #START New Comment Code #ISSUE 3034 Reject Document
    #add code for reject and return go to state 5(wait for Confirm Dispatch)

    function rejectAndReturnAction() {

        $this->load->model("stock_model", "stock");

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

        // Check step for reject and return.
        $checkStepDispatch = 0;
        $order_detail = $this->stock->getOrderDetailByOrderId($order_id);
        $group_order_detail = array();

        foreach ($order_detail as $idx => $val) {
            $checkStepDispatch += (float) $val->DP_Confirm_Qty;

            if (is_null($val->Pallet_From_Item_Id)) {
                if (!isset($group_order_detail[$val->Item_Id]))
                    $group_order_detail[$val->Item_Id] = 0;
                $group_order_detail[$val->Item_Id] += (float) $val->Confirm_Qty;
            } else {
                if (!isset($group_order_detail[$val->Pallet_From_Item_Id]))
                    $group_order_detail[$val->Pallet_From_Item_Id] = 0;
                $group_order_detail[$val->Pallet_From_Item_Id] += (float) $val->Confirm_Qty;
            }
        }

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

            $process_id = $flow_info['0']->Process_Id;
            $present_state = $flow_info['0']->Present_State;

            // validate token
            $response = validate_token($token, $flow_id, $present_state, $process_id);


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

        // Reject and Return from wait for Confirm Dispatch to wait for Approve Picking
        if ($present_state == 5 && $next_state == 4) {

            /**
             * update Order
             */
            if ($check_not_err):
                #update data order detail
                $detail_order['Actual_Action_Date'] = NULL;
                $where_order['Order_Id'] = $order_id;
                $result_order = $this->stock->_updateOrder($detail_order, $where_order);
                if (!$result_order) :
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not update Order.";
                endif;

            /**
             * Should add log for previous data.
             */
            endif;


            /**
             * update Order Detail
             */
            if ($check_not_err):

                // Set Active No to all detail
                $d['Active'] = 'N';
                $w['Order_Id'] = $order_id;
                $this->stock->updateOrderDetail($d, $w);

                // Update Active Y to item in master
                foreach ($group_order_detail as $idx => $val) {
                    $detail['Reserv_Qty'] = $val;
                    $detail['Confirm_Qty'] = $val;
                    $detail['DP_Confirm_Qty'] = NULL;
                    $detail['Active'] = 'Y';
                    $where['Item_Id'] = $idx;
                    $result_detail = $this->stock->updateOrderDetail($detail, $where);
                    if (!$result_detail) :
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Can not update Order Detail.";
                    endif;
                }

            /**
             * Should add log for previous data.
             */
            endif;
        }

        // Reject and Return from wait for Approve Dispatch to wait for Confirm Dispatch
        if ($present_state == 6 && $next_state == 5) {

            /**
             * update Order
             */
            if ($check_not_err):
                #update data order detail
                $detail_order['Actual_Action_Date'] = NULL;
                $where_order['Order_Id'] = $order_id;
                $result_order = $this->stock->_updateOrder($detail_order, $where_order);
                if (!$result_order) :
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not update Order.";
                endif;

            /**
             * Should add log for previous data.
             */
            endif;


            /**
             * update Order Detail
             */
            if ($check_not_err):

                // Set Active No to all detail
                $d['Active'] = 'N';
                $w['Order_Id'] = $order_id;
                $this->stock->updateOrderDetail($d, $w);

                // Update Active Y to item in master
                foreach ($group_order_detail as $idx => $val) {
                    $detail['Reserv_Qty'] = $val;
                    $detail['Confirm_Qty'] = $val;
                    $detail['DP_Confirm_Qty'] = NULL;
                    $detail['Active'] = 'Y';
                    $where['Item_Id'] = $idx;
                    $result_detail = $this->stock->updateOrderDetail($detail, $where);
                    if (!$result_detail) :
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Can not update Order Detail.";
                    endif;
                }

            /**
             * Should add log for previous data.
             */
            endif;
        }

        /**
         * update Workflow
         */
        if ($check_not_err):
            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
            if (empty($action_id) || $action_id == '') :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update Workflow.";
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

            $set_return['message'] = "Reject and Return Dispatch Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else :
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $set_return['critical'][]['message'] = "Reject and Return Dispatch Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($set_return, $return);
        endif;

        echo json_encode($json);
    }

    #ISSUE 3034 Reject Document
    #DATE:2013-12-16
    #BY:KIK
    #เพิ่มในส่วนของ reject and (reject and return)
    #START New Comment Code #ISSUE 3034 Reject Document
    #add code for reject and return go to state 2(wait for Approve Pre-Dispatch)

    function rejectAction() {

        $this->load->library('stock_lib');
        $this->load->model("stock_model", "stock");

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

            $process_id = $flow_info['0']->Process_Id;
            $present_state = $flow_info['0']->Present_State;

            // validate token
            $response = validate_token($token, $flow_id, $present_state, $process_id);


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
         * Read Order Detail Data
         */
        $order_detail = $this->stock->getOrderDetailByOrderId($order_id);
        if (empty($order_detail)) {
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
         * Clear Pallet to Active No
         */
        $result_clearPallet = $this->stock->clearPallet($order_id);
        if (!$result_clearPallet):
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

            $this->transaction_db->transaction_end();
            $set_return['message'] = "Reject Dispatch Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else :

            $this->transaction_db->transaction_rollback();
            $set_return['critical'][]['message'] = "Reject Dispatch Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($set_return, $return);
        endif;

        echo json_encode($json);
    }

    function createRelocat($order_id) {
        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

        /**
         * Set Variable
         */
        $check_not_err = TRUE;
        $return = array();

        /**
         * Condition =
         * $process_id = 16 >> relocation_by_pallet
         * $process_Id = 6 >> relocation_by_item
         */
        $process_id = 6;
        $present_state = 0;

        $prod_list = $this->stock->getOrderDetailByOrderId($order_id);
//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowDispatchList"); // Button Permission. Add by Ton! 20140131
        # show form for approve & confirm
        $process_type = $data_form->process_type;
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
//            $document_no = createReLNo();
            $document_no = create_document_no_by_type("REL"); // Add by Ton! 20140428
            if (empty($document_no) || $document_no == '') :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not generate Relocation Number.";
            endif;
        endif;


        /**
         * create Workflow
         */
        if ($check_not_err):
            $data['Document_No'] = $document_no;
            list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data);
            if (empty($flow_id) || $flow_id == '' || empty($action_id) || $action_id == '') :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not create Workflow.";
            endif;
        endif;


        /**
         * create ReLocation Order
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
            if (empty($order_id) || $order_id == '') :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not create ReLocation Order.";
            endif;
        endif;


        /**
         * create ReLocation Order Detail
         */
        if ($check_not_err):
            $all_product_move = array();
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

                    //ADD BY KIK 2014-01-28 เพิ่มเกี่ยวกับราคา
                    if ($conf_price_per_unit == TRUE) :
                        $p_detail['Price_Per_Unit'] = $info[0]->Price_Per_Unit;
                        $p_detail['Unit_Price_Id'] = $info[0]->Unit_Price_Id;
                        $p_detail['All_Price'] = $info[0]->Price_Per_Unit * str_replace(",", "", ($rows->Confirm_Qty));
                    endif;
                    //END ADD

                    $all_product_move[] = $p_detail;
                endforeach; // close for each
            endif; // close if have prod

            if (!empty($all_product_move)) :
                $result_addReLocationOrderDetail = $this->rl->addReLocationOrderDetail($all_product_move);
                if ($result_addReLocationOrderDetail <= 0) :
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not create ReLocation Order Detail.";
                endif;
            endif;
        endif;

        return $return;
    }

    function mQuickApproveDispatch() {

        /**
         * set Variable
         */
        $this->load->model("dispatch_model", "dispatch");
        $flow_id = json_decode($this->input->post("flow_id"));
        $check_not_err = TRUE;
        $return = array();

        foreach ($flow_id as $fid) :

            $data = Array();
            $data['flow_id'] = $fid;

            #get Data Order
            $order_data = 'Flow_Id,Order_Id,Document_No';
            $order_where['Flow_Id'] = $fid;
            $header = $this->util_query_db->select_table('STK_T_Order', $order_data, $order_where)->row();
            $data['order_id'] = $header->Order_Id;
            $data['Document_No'] = $header->Document_No;


            #get Data Order Detail
            $order_detail = 'Confirm_Qty,Inbound_Item_Id,Order_Id,Item_Id';
            $order_detail_where['Order_Id'] = $data['order_id'];
            $data['body'] = $this->util_query_db->select_table('STK_T_Order_Detail', $order_detail, $order_detail_where)->result();


            /**
             * ================== Start Transaction =========================
             */
            $this->transaction_db->transaction_start();

            /**
             * update Process
             */
            if ($check_not_err):
                //$respond = $this->_update_quick_approve($params);
                $respond = $this->_update_quick_approve($data);
                if (!empty($respond['critical'])) :
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    log_message("ERROR", "Can not update Process.");
                    $return['critical'][]['message'] = "Can not update Process.";
                    $return = array_merge_recursive($return, $respond);
                endif;
            endif;


            /**
             * create Stock Dispatch Order
             */
            if ($check_not_err):
                $this->load->library('Stock_lib');
                $respond = $this->stock_lib->addStockDispatchOrder($data['order_id']);

                if (!empty($respond['critical'])) :
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    log_message("ERROR", "Can not create Stock Dispatch Order.");
                    $return['critical'][]['message'] = "Can not create Stock Dispatch Order.";
                    $return = array_merge_recursive($return, $respond);
                endif;
            endif;


            /**
             * check if for return json and set transaction
             */
            if ($check_not_err):

                /**
                 * check transaction_status by kik : 20140530
                 */
                if ($this->transaction_db->transaction_status() === FALSE) :
                    /**
                     * ================== Rollback Transaction =========================
                     */
                    $this->transaction_db->transaction_rollback();

                    $set_return['critical'][]['message'] = "Approve Dispatch Incomplete.";
                    $json['status'] = "save";
                    $json['return_val'] = array_merge_recursive($set_return, $return);

                else:
                    /**
                     * ================== Auto End Transaction =========================
                     */
                    $this->transaction_db->transaction_end();

                    $json['status'] = "save";

                    $set_return['message'] = "Approve Dispatch Complete.";
                    $return['success'][] = $set_return;
                    $json['return_val'] = $return;
                endif;


            else :
                /**
                 * ================== Rollback Transaction =========================
                 */
                $this->transaction_db->transaction_rollback();

                $set_return['critical'][]['message'] = "Approve Dispatch Incomplete.";
                $json['status'] = "save";
                $json['return_val'] = array_merge_recursive($set_return, $return);
            endif;


            unset($data);

        endforeach; //end for loop flow_id

        echo json_encode($json);
    }

    private function _update_quick_approve($params) {

        /**
         * Load Model
         */
        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", "flow");

        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;


        if (!empty($params)):

            $body = $params["body"];

            /*
             * Set Data for update Workflow
             */
            $flow_id = $params['flow_id'];
            $order_id = $params['order_id'];
            $process_id = 2;
            $present_state = 5;
            $process_type = "OUTBOUND";
            $action_type = "Quick Approve";
            $next_state = -2;
            $user_id = $this->session->userdata("user_id");

            $data['Document_No'] = $params['Document_No'];

            /**
             * update Workflow
             */
            if ($check_not_err):
                $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
                if (empty($action_id) || $action_id == ''):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    log_message("ERROR", "Can not update Workflow.");
                    $return['critical'][]['message'] = "Can not update Workflow.";
                endif;
            endif;


            /**
             * update Order
             */
            if ($check_not_err):

                $order = array(
                    'Actual_Action_Date' => date("Y-m-d H:i:s")
                    , 'Modified_By' => $user_id
                    , 'Modified_Date' => date("Y-m-d H:i:s")
                );

                $where['Flow_Id'] = $flow_id;
                $where['Order_Id'] = $order_id;
                $result_updateOrder = $this->stock->updateOrder($order, $where);


                if (!$result_updateOrder):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    log_message("ERROR", "Can not update Order.");
                    $return['critical'][]['message'] = "Can not update Order.";
                endif;
            endif;


            /**
             * update Order Detail
             */
            if ($check_not_err):

                if (!empty($body)):

                    foreach ($body as $rows) :
//                   p($rows);exit();

                        if (!empty($rows->Item_Id)):

                            $detail = array();

                            $detail['Activity_Code'] = 'DISPATCH';
                            $detail['Activity_By'] = $this->session->userdata("user_id");
                            $detail['DP_Confirm_Qty'] = $rows->Confirm_Qty;

                            $where_detail['Item_Id'] = $rows->Item_Id;

                            if (!empty($detail)): //check not empty $detail and where by kik : 20140530
                                $result_updateOrderDetail = $this->stock->updateOrderDetail($detail, $where_detail);
                                if (!$result_updateOrderDetail):
                                    $check_not_err = FALSE;

                                    /**
                                     * Set Alert Zone (set Error Code, Message, etc.)
                                     */
                                    log_message("ERROR", "Can not update Order Detail.");
                                    $return['critical'][]['message'] = "Can not update Order Detail.";
                                    break;
                                endif;
                            else:

                                $check_not_err = FALSE;

                                /**
                                 * Set Alert Zone (set Error Code, Message, etc.)
                                 */
                                log_message("ERROR", "Can not update Order Detail.");
                                $return['critical'][]['message'] = "Can not update Order Detail.";

                            endif;

                            unset($where_detail);
                            unset($detail);

                        else://no have item_id by kik : 20140530


                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            log_message("ERROR", "Can not update Order Detail.");
                            $return['critical'][]['message'] = "Can not update Order Detail.";
                        endif;

                    endforeach; //end of for order detail

                endif; //end of check error before update detail

            else:
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                log_message("ERROR", "Can not update Order Detail.");
                $return['critical'][]['message'] = "Can not update Order Detail.";

            endif; // end if check have order detail

        else:

            $check_not_err = FALSE;

            /**
             * Set Alert Zone (set Error Code, Message, etc.)
             */
            log_message("ERROR", "No have data for update");
            $return['critical'][]['message'] = "No have data for update";
        endif; //end of check have data


        return $return;
    }

    private function _get_doc_header($doc_header) {
        $results = Array();
        foreach ($doc_header as $idx => $value) :
            if ($value->From_State == 5 && $value->To_State == -2) :
                $results = $value;
            endif;
        endforeach;
        return $results;
    }

    function grouping_item($data) {

        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];


        $dispatch_group_item_by_product_code = FALSE;
        $dispatch_group_item_by_lot = FALSE;
        $dispatch_group_item_by_serial = FALSE;
        if (!empty($this->settings['dispatch_group_item_by'])):
            foreach ($this->settings['dispatch_group_item_by'] as $dispatch_group_item_by):
                if ($dispatch_group_item_by == 'product_code'):
                    $dispatch_group_item_by_product_code = TRUE;
                endif;
                if ($dispatch_group_item_by == 'lot'):
                    $dispatch_group_item_by_lot = TRUE;
                endif;
                if ($dispatch_group_item_by == 'serial'):
                    $dispatch_group_item_by_serial = TRUE;
                endif;
            endforeach;
        endif;

        $group_result = array();
        $tmp_group_result = array();
        if ($dispatch_group_item_by_product_code):
            foreach ($data as $rs):

                if ($dispatch_group_item_by_lot && $dispatch_group_item_by_serial):
                    $set_index = $rs->Product_Code . "--|" . $rs->Product_Lot . "--|" . $rs->Product_Serial;
                elseif ($dispatch_group_item_by_lot):
                    $set_index = $rs->Product_Code . "--|" . $rs->Product_Lot;
                elseif ($dispatch_group_item_by_serial):
                    $set_index = $rs->Product_Code . "--|" . $rs->Product_Serial;
                else:
                    $set_index = $rs->Product_Code;
                endif;

                if (empty($group_result[$set_index])):
                    $group_result[$set_index] = (array) $rs;
                    $group_result[$set_index]['Cont_Data'] = $rs->Cont_No . ' ' . $rs->Cont_Size_No . $rs->Cont_Size_Unit_Code;
                else:
//                    $group_result[$set_index]['Item_Id'] .= "--|".$rs->Item_Id;
                    $group_result[$set_index]['Item_Id'] .= "__" . $rs->Item_Id;
                    $group_result[$set_index]['Order_Id'] .= "--|" . $rs->Order_Id;
                    $group_result[$set_index]['Product_Id'] .= "--|" . $rs->Product_Id;
//                    $group_result[$set_index]['Product_Code'] .= "--|".$rs->Product_Code;
                    $group_result[$set_index]['Product_Status'] .= "--|" . $rs->Product_Status;
                    $group_result[$set_index]['Product_Sub_Status'] .= "--|" . $rs->Product_Sub_Status;
                    $group_result[$set_index]['Suggest_Location_Id'] .= "--|" . $rs->Suggest_Location_Id;
                    $group_result[$set_index]['Actual_Location_Id'] .= "--|" . $rs->Actual_Location_Id;
                    $group_result[$set_index]['Old_Location_Id'] .= "--|" . $rs->Old_Location_Id;
//                    $group_result[$set_index]['Pallet_Id'] .= "--|".$rs->Pallet_Id;

                    if ($conf_pallet):
                        if (!empty($rs->Pallet_Code)):
                            if ($rs->Pallet_Code != '' and $rs->Pallet_Code != ' '):
                                if ($group_result[$set_index]['Pallet_Code'] != ' '):
                                    $group_result[$set_index]['Pallet_Code'] .= ", " . $rs->Pallet_Code;
                                else:
                                    $group_result[$set_index]['Pallet_Code'] .= $rs->Pallet_Code;
                                endif;
                            endif;
                        endif;
                    endif;


//                    $group_result[$set_index]['Pallet_Code'] .= "--|".$rs->Pallet_Code;
                    $group_result[$set_index]['Product_License'] .= "--|" . $rs->Product_License;

                    $rs->Product_Lot = trim($rs->Product_Lot);
                    if ($rs->Product_Lot != '' and $rs->Product_Lot != ' '):
                        if ($group_result[$set_index]['Product_Lot'] != ' '):
                            if (!@in_array($rs->Product_Lot, @$tmp_group_result[$set_index]['Product_Lot'])):
                                $group_result[$set_index]['Product_Lot'] .= ", " . $rs->Product_Lot;
                            endif;
                            $tmp_group_result[$set_index]['Product_Lot'][] = $rs->Product_Lot;
                        else:
                            $group_result[$set_index]['Product_Lot'] .= $rs->Product_Lot;
                            $tmp_group_result[$set_index]['Product_Lot'][] = $rs->Product_Lot;
                        endif;
                    endif;
//                    if($rs->Product_Lot != '' and $rs->Product_Lot != ' '):
//                        if($group_result[$set_index]['Product_Lot'] != ' '):
//                            $group_result[$set_index]['Product_Lot'] .= ", ".$rs->Product_Lot;
//                        else:
//                            $group_result[$set_index]['Product_Lot'] .= $rs->Product_Lot;
//                        endif;
//                    endif;


                    $rs->Product_Serial = trim($rs->Product_Serial);
                    if ($rs->Product_Serial != '' and $rs->Product_Serial != ' '):
                        if ($group_result[$set_index]['Product_Serial'] != ' '):
                            if (!@in_array($rs->Product_Serial, @$tmp_group_result[$set_index]['Product_Serial'])):
                                $group_result[$set_index]['Product_Serial'] .= ", " . $rs->Product_Serial;
                            endif;
                            $tmp_group_result[$set_index]['Product_Serial'][] = $rs->Product_Serial;
                        else:
                            $group_result[$set_index]['Product_Serial'] .= $rs->Product_Serial;
                            $tmp_group_result[$set_index]['Product_Serial'][] = $rs->Product_Serial;
                        endif;
                    endif;
//                    if($rs->Product_Serial != '' and $rs->Product_Serial != ' '):
//                        if($group_result[$set_index]['Product_Serial'] != ' '):
//                            $group_result[$set_index]['Product_Serial'] .= ", ".$rs->Product_Serial;
//                        else:
//                            $group_result[$set_index]['Product_Serial'] .= $rs->Product_Serial;
//                        endif;
//                    endif;


                    $rs->Product_Mfd = trim($rs->Product_Mfd);
                    if ($rs->Product_Mfd != '' and $rs->Product_Mfd != ' '):
                        if ($group_result[$set_index]['Product_Mfd'] != ' '):
                            if (!@in_array($rs->Product_Mfd, @$tmp_group_result[$set_index]['Product_Mfd'])):
                                $group_result[$set_index]['Product_Mfd'] .= ", " . $rs->Product_Mfd;
                            endif;
                            $tmp_group_result[$set_index]['Product_Mfd'][] = $rs->Product_Mfd;
                        else:
                            $group_result[$set_index]['Product_Mfd'] .= $rs->Product_Mfd;
                            $tmp_group_result[$set_index]['Product_Mfd'][] = $rs->Product_Mfd;
                        endif;
                    endif;
//                    if($rs->Product_Mfd != '' and $rs->Product_Mfd != ' '):
//                        if($group_result[$set_index]['Product_Mfd'] != ' '):
//                            $group_result[$set_index]['Product_Mfd'] .= ", ".$rs->Product_Mfd;
//                        else:
//                            $group_result[$set_index]['Product_Mfd'] .= $rs->Product_Mfd;
//                        endif;
//                    endif;


                    $rs->Product_Exp = trim($rs->Product_Exp);
                    if ($rs->Product_Exp != '' and $rs->Product_Exp != ' '):
                        if ($group_result[$set_index]['Product_Exp'] != ' '):
                            if (!@in_array($rs->Product_Exp, @$tmp_group_result[$set_index]['Product_Exp'])):
                                $group_result[$set_index]['Product_Exp'] .= ", " . $rs->Product_Exp;
                            endif;
                            $tmp_group_result[$set_index]['Product_Exp'][] = $rs->Product_Exp;
                        else:
                            $group_result[$set_index]['Product_Exp'] .= $rs->Product_Exp;
                            $tmp_group_result[$set_index]['Product_Exp'][] = $rs->Product_Exp;
                        endif;
                    endif;
//                    if($rs->Product_Exp != '' and $rs->Product_Exp != ' '):
//                        if($group_result[$set_index]['Product_Exp'] != ' '):
//                            $group_result[$set_index]['Product_Exp'] .= ", ".$rs->Product_Exp;
//                        else:
//                            $group_result[$set_index]['Product_Exp'] .= $rs->Product_Exp;
//                        endif;
//                    endif;
                    // Container                  
                    if ($rs->Cont_Id != '' and $rs->Cont_Id != ' '):
                        $container_data = "";
                        if ($group_result[$set_index]['Cont_Data'] != ' '):
                            $container_data = $rs->Cont_No . ' ' . $rs->Cont_Size_No . $rs->Cont_Size_Unit_Code;
                            if (!@in_array($container_data, @$tmp_group_result[$set_index]['Cont_Data'])):
                                $group_result[$set_index]['Cont_Data'] .= ', ' . $container_data;
                            endif;
                            $tmp_group_result[$set_index]['Cont_Data'][] = $container_data;
                        else:
                            $group_result[$set_index]['Cont_Data'] .= $container_data;
                            $tmp_group_result[$set_index]['Cont_Data'][] = $container_data;
                        endif;
                    endif;


//                    $group_result[$set_index]['Product_Mfd'] .= "--|".$rs->Product_Mfd;
//                    $group_result[$set_index]['Product_Exp'] .= "--|".$rs->Product_Exp;
                    $group_result[$set_index]['Reserv_Qty'] += $rs->Reserv_Qty;
                    $group_result[$set_index]['Confirm_Qty'] += $rs->Confirm_Qty;
                    $group_result[$set_index]['DP_Confirm_Qty'] += $rs->DP_Confirm_Qty;
                    $group_result[$set_index]['Unit_Id'] .= "--|" . $rs->Unit_Id;
                    $group_result[$set_index]['Reason_Code'] .= "--|" . $rs->Reason_Code;
                    $group_result[$set_index]['Reason_Remark'] .= "--|" . $rs->Reason_Remark;

                    $rs->Remark = trim($rs->Remark);
                    if ($rs->Remark != '' and $rs->Remark != ' '):
                        if ($group_result[$set_index]['Remark'] != ' '):
                            if (!@in_array($rs->Remark, @$tmp_group_result[$set_index]['Remark'])):
                                $group_result[$set_index]['Remark'] .= ", " . $rs->Remark;
                            endif;
                            $tmp_group_result[$set_index]['Remark'][] = $rs->Remark;
                        else:
                            $group_result[$set_index]['Remark'] .= $rs->Remark;
                            $tmp_group_result[$set_index]['Remark'][] = $rs->Remark;
                        endif;
                    endif;
//                    if($rs->Remark != '' and $rs->Remark != ' '):
//                        if($group_result[$set_index]['Remark'] != ' '):
//                            $group_result[$set_index]['Remark'] .= ", ".$rs->Remark;
//                        else:
//                            $group_result[$set_index]['Remark'] .= $rs->Remark;
//                        endif;
//                    endif;
//                    $group_result[$set_index]['Remark'] .= "--|".$rs->Remark;
                    $group_result[$set_index]['Inbound_Item_Id'] .= "--|" . $rs->Inbound_Item_Id;
//                    $group_result[$set_index]['Split_From_Item_Id'] .= "--|".$rs->Split_From_Item_Id;
//                    $group_result[$set_index]['Active'] .= "--|".$rs->Active;
                    $group_result[$set_index]['Activity_Code'] .= "--|" . $rs->Activity_Code;
//                    $group_result[$set_index]['Activity_By'] .= "--|".$rs->Activity_By;


                    $rs->Suggest_Location = trim($rs->Suggest_Location);
                    if ($rs->Suggest_Location != '' and $rs->Suggest_Location != ' '):
                        if ($group_result[$set_index]['Suggest_Location'] != ' '):
                            if (!@in_array($rs->Suggest_Location, @$tmp_group_result[$set_index]['Suggest_Location'])):
                                $group_result[$set_index]['Suggest_Location'] .= ", " . $rs->Suggest_Location;
                            endif;
                            $tmp_group_result[$set_index]['Suggest_Location'][] = $rs->Suggest_Location;
                        else:
                            $group_result[$set_index]['Suggest_Location'] .= $rs->Suggest_Location;
                            $tmp_group_result[$set_index]['Suggest_Location'][] = $rs->Suggest_Location;
                        endif;
                    endif;


                    $rs->Actual_Location = trim($rs->Actual_Location);
                    if ($rs->Actual_Location != '' and $rs->Actual_Location != ' '):
                        if ($group_result[$set_index]['Actual_Location'] != ' '):
                            if (!@in_array($rs->Actual_Location, @$tmp_group_result[$set_index]['Actual_Location'])):
                                $group_result[$set_index]['Actual_Location'] .= ", " . $rs->Actual_Location;
                            endif;
                            $tmp_group_result[$set_index]['Actual_Location'][] = $rs->Actual_Location;
                        else:
                            $group_result[$set_index]['Actual_Location'] .= $rs->Actual_Location;
                            $tmp_group_result[$set_index]['Actual_Location'][] = $rs->Actual_Location;
                        endif;
                    endif;

                    if (!empty($rs->Activity_By_Name)) {
                        $rs->Activity_By_Name = trim($rs->Activity_By_Name);
                        if ($rs->Activity_By_Name != '' and $rs->Activity_By_Name != ' '):
                            if ($group_result[$set_index]['Activity_By_Name'] != ' '):
                                if (!@in_array($rs->Activity_By_Name, @$tmp_group_result[$set_index]['Activity_By_Name'])):
                                    $group_result[$set_index]['Activity_By_Name'] .= ", " . $rs->Activity_By_Name;
                                endif;
                                $tmp_group_result[$set_index]['Activity_By_Name'][] = $rs->Activity_By_Name;
                            else:
                                $group_result[$set_index]['Activity_By_Name'] .= $rs->Activity_By_Name;
                                $tmp_group_result[$set_index]['Activity_By_Name'][] = $rs->Activity_By_Name;
                            endif;
                        endif;
                    }



//                    if(!empty($rs->Activity_By_Name)):
//                        if($rs->Activity_By_Name != '' and $rs->Activity_By_Name != ' '):
//                            if($group_result[$set_index]['Activity_By_Name'] != ' '):
//                                $group_result[$set_index]['Activity_By_Name'] .= ", ".$rs->Activity_By_Name;
//                            else:
//                                $group_result[$set_index]['Activity_By_Name'] .= $rs->Activity_By_Name;
//                            endif;
//                        endif;
//                    endif;
//                    $group_result[$set_index]['Activity_By_Name'] .= "--|".$rs->Activity_By_Name;
//                    $group_result[$set_index]['Activity_Date'] .= "--|".$rs->Activity_Date;

                    if ($conf_price_per_unit):
                        if (!empty($rs->Price_Per_Unit)):
                            $group_result[$set_index]['Price_Per_Unit'] .= "--|" . $rs->Price_Per_Unit;
                            $group_result[$set_index]['Unit_Price_Id'] .= "--|" . $rs->Unit_Price_Id;
                            $group_result[$set_index]['All_Price'] .= "--|" . $rs->All_Price;
                        endif;
                    endif;

                    if ($conf_cont):
                        $group_result[$set_index]['Cont_Id'] .= "--|" . $rs->Cont_Id;
                    endif;

                    if ($conf_inv):
                        $group_result[$set_index]['Invoice_Id'] .= "--|" . $rs->Invoice_Id;
                    endif;

                    if ($conf_pallet):
                        $group_result[$set_index]['DP_Type_Pallet'] .= "--|" . $rs->DP_Type_Pallet;
                        $group_result[$set_index]['Pallet_Id_Out'] .= "--|" . $rs->Pallet_Id_Out;
                    endif;

                endif;
            endforeach;
        endif;

        return array($group_result, $dispatch_group_item_by_product_code, $dispatch_group_item_by_lot, $dispatch_group_item_by_serial);
    }

    private function createPODFile($params) {

        $this->load->model("stock_model", "stock");
        $upload_path = $this->settings['uploads']['upload_path'];

        $order_id = $params['order_id'];
        $doc_refer_ext = $params['doc_refer_ext'];
        $dispatchDate = $params['dispatchDate'];
        $order_detail = $this->stock->getOrderDetail($order_id, false, "STK_T_Order_Detail.Item_Id ASC", NULL, NULL);
        $fp = fopen($upload_path . 'HXPOD303' . date('ymd') . '.csv', 'a');

        foreach ($order_detail as $k => $val) {
            fputcsv($fp, array("POD", NULL, $doc_refer_ext, NULL, NULL, ($k + 1), $val->Product_Code, $val->Confirm_Qty, $val->Product_Lot, $dispatchDate), ";");
        }

        fclose($fp);
    }

}
