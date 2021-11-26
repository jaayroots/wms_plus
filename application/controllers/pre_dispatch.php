<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class pre_dispatch extends CI_Controller {

    public $settings;
    public $pallet;

    public function __construct() {

        parent::__construct();
        $this->settings = native_session::retrieve();   //add by kik : 20140113
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->library('validate_data');
        $this->load->helper('form');
        $this->load->helper('util_helper');
        $this->load->model("pre_dispatch_model", "preDispModel");
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("workflow_model", "flow");
        $this->load->model("stock_model", "stock");
        $this->load->model("product_model", "product");
        $this->load->model("contact_model", "contact");
        $this->load->model("company_model", "company");
        $this->load->model("system_management_model", "sys");
        $this->load->model("inbound_model", "inbound");
        $this->load->model("container_model", "container");
        $this->load->model("invoice_model", "invoice");
        $this->load->model("pallet_model", "pl");
        $this->load->model("location_model", "location");
        $this->load->model("master_tmp", "master_rep");

        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));

        $this->load->controller('balance', 'balance');
        $this->pallet = $this->config->item('build_pallet');
    }

    public function index() {
        // $this->preDispatchList();
    }

    public function preDispatchForm() {
        $this->output->enable_profiler($this->config->item('set_debug'));

        // pre-load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $conf_invoice_require = empty($conf['invoice_require']) ? false : @$conf['invoice_require'];

        // define process configuration
        $process_id = 2;
        $present_state = 0;

        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowPreDispatchList");

        #Get Product Status
        $r_product_status = $this->product->selectProductStatus()->result();
        $product_status_list = genOptionDropdown($r_product_status, "SYS", FALSE, TRUE); // Edit by kik! 20131107
        #Get Product Sub Status
        $r_product_Substatus = $this->product->selectSubStatus()->result();
        $product_Substatus_list = genOptionDropdown($r_product_Substatus, "SYS", FALSE, TRUE); // Edit by kik! 20131107
        #Get Renter [Company Renter] list
        $r_renter = $this->company->getRenterAll()->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE); // Edit by kik! 20131107
        #Get Shipper[Company Owner] list
        $r_shipper = $this->company->getOwnerAll()->result();
        $shipper_list = genOptionDropdown($r_shipper, "COMPANY", FALSE, FALSE); // Edit by kik! 20131107
        #Get Consignee[Company Customer]  list
        $r_consignee = $this->company->getBranchNotOwner($this->session->userdata('branch_id'))->result();
        $consignee_list = genOptionDropdown($r_consignee, "COMPANY", FALSE, FALSE); // Edit by kik! 20131107
        
    //  p($consignee_list ); exit;
        #Get Dispatch Type list
        $dispatch_type_list = genOptionDropdown($this->sys->getDispatchType(), "SYS", TRUE, TRUE); // Edit by kik! 20131107

        $renter_id_select = form_dropdown("renter_id_select", $renter_list, '', "id=renter_id_select class=required"); //$renter_id  // Hard Code. Edit by Ton! 20131028
        $frm_warehouse_select = form_dropdown("frm_warehouse_select", $shipper_list, '', "id=frm_warehouse_select  class=required"); //$shipper_id  // Hard Code. Edit by Ton! 20131028
        $to_warehouse_select = form_dropdown("to_warehouse_select", $consignee_list, '', "id=to_warehouse_select  class=required");
    //   p($to_warehouse_select); exit;
        $dispatch_type_select = form_dropdown("dispatch_type_select", $dispatch_type_list, "DP1", "id=dispatch_type_select  class=required");
        $productStatus_select = form_dropdown("productStatus_select", $product_status_list, "", "id=productStatus_select");
        $productSubStatus_select = form_dropdown("productSubStatus_select", $product_Substatus_list, "", "id=productSubStatus_select");

        $query = $this->preDispModel->getPreDispatchAll();
        $pre_dispatch_list = $query->result();

        //ADD check show/hide datatable of container:BY POR 2014-09-08
        $init_invoice = array();
        if ($conf_inv):
//            $init_invoice['init'] = ", { //invoice
//                onblur: 'submit',
//                sUpdateURL: '".site_url("/pre_dispatch/saveEditedRecord")."',
//                event: 'click', //invoice
//            }";
            $init_invoice['init'] = ", { //invoice
                onblur: 'submit',
                sUpdateURL: function(value, settings) {return(value);},
                event: 'click', //invoice
            }";
            $init_invoice['reinit'] = ",null";
            $init_invoice['reinit_col'] = "";
        else :
            $init_invoice['init'] = "";
            $init_invoice['reinit'] = ",null";
            $init_invoice['reinit_col'] = ",null";
        endif;
        //END ADD

        $init_container = "";
        #start add code for ISSUE 3320 :  by kik : 20140113
        $price = '';
        if ($conf_price_per_unit):
            //$price = ',null';  //COMMENT BY POR 2014-06-02
            //ADD BY POR 2014-06-02 เพิ่มให้สามารถกรอกราคาและเลือกหน่วยได้
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
            $price = " ,{
                    sSortDataType: \"dom-text\",
                    sType: \"numeric\",
                    type: 'text',
                    onblur: \"submit\",
                    event: 'click',
                    is_required: true,
                    loadfirst: true,
                    cssclass: \"required number\",
                    sUpdateURL: function(value, settings) {return(value);},
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

        //END ADD;
        else:
//            $price = "  , {
//                    onblur: 'submit',
//                    sUpdateURL: '" . site_url() . '/pre_dispatch/saveEditedRecord' . "',
//                    event: 'click',
//                }";
            $price = "  , {
                    onblur: 'submit',
                    sUpdateURL: function(value, settings) {return(value);},
                    event: 'click',
                }";
            $unitofprice = ',null';
        endif;

        if ($this->settings['dp_type_link_prod_status']['active']):
            foreach ($this->settings['dp_type_link_prod_status']['object'] as $key_object => $object):
                $dp_type_link_prod_status[$object['dp_type']] = $object['prod_status'];
            endforeach;
            $dp_type_link_prod_status = json_encode($dp_type_link_prod_status);
        else:
            $dp_type_link_prod_status = FALSE;
        endif;

        //ADD BY POR 2014-08-13 select container size for size list in container
        $container_size = $this->container->getContainerSize()->result();
        $_container_list = array();
        foreach ($container_size as $idx => $value) {
            $_container_list[$idx]['Id'] = $value->Cont_Size_Id;
            $_container_list[$idx]['No'] = $value->Cont_Size_No;
            $_container_list[$idx]['Unit_Code'] = $value->Cont_Size_Unit_Code;
        }
        $container_size_list = json_encode($_container_list);

        $doc_refer_container = array();

        #end add code for ISSUE 3320 :  by kik : 20140113
//        p($data_form->form_name); exit;

        $str_form = $this->parser->parse('form/' . $data_form->form_name, array("parameter" => form_input('warehouse_form', 'whs_form')
            , "test_parse" => "test pass parse from controller"
            , "price_per_unit" => $conf_price_per_unit //add by kik : 20140113
            , "conf_inv" => $conf_inv //ADD BY POR : 2014-08-13
            , "conf_invoice_require" => $conf_invoice_require //ADD BY POR : 2014-10-14
            , "conf_cont" => $conf_cont //ADD BY POR : 2014-08-13
            , "conf_pallet" => $conf_pallet //ADD BY POR : 2014-08-13
            , "renter_id_select" => $renter_id_select
            , "frm_warehouse_select" => $frm_warehouse_select
            , "to_warehouse_select" => $to_warehouse_select
            , "dispatch_type_select" => $dispatch_type_select
            , "process_id" => $process_id
            , "present_state" => $present_state
            , "productStatus_select" => $productStatus_select
            , "productSubStatus_select" => $productSubStatus_select
            , "token" => ''
            , "owner_id" => ''//$owner_id // Hard Code. Edit by Ton! 20131028
            , "dp_type_link_prod_status" => $dp_type_link_prod_status
            , "container_list" => ""
            , "doc_refer_container" => $doc_refer_container
            , "container_size_list" => $container_size_list
            , "DeliveryTime" => ''
            , "DestinationDetail" => ''
            , "data_form" => (array) $data_form), TRUE);
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPreDispatch"></i>'
            , 'form' => $str_form
            , 'price' => $price         //add by kik : 20140113
            , 'init_container' => $init_container
            , 'init_invoice' => $init_invoice['init']
            , 'reinit_invoice' => $init_invoice['reinit']
            , 'reinit_invoice_col' => $init_invoice['reinit_col']
            , 'unitofprice' => $unitofprice //ADD BY POR 2014-06-02 ให้ส่งตัวแปรเกี่ยวกับการแสดงหน่วยของราคา
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_action' => $data_form->str_buttun
            , 'user_login' => $this->session->userdata('username')
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
        ));
    }


    public function preDispatchFormWithData() {
        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $conf_invoice_require = empty($conf['invoice_require']) ? false : @$conf['invoice_require'];

        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set เน�เธซเน� $this->output->enable_profiler เน€เธ�เน�เธ� เธ�เน�เธฒเธ•เธฒเธกเธ—เธตเน�เน€เธ�เน�เธ•เธกเธฒเธ�เธฒเธ� config เน€เธ�เธทเน�เธญเน�เธ�เน�เน�เธชเธ”เธ� DebugKit

        $flow_id = $this->input->post("id");
//        $owner_id = $this->config->item('owner_id'); // Hard Code. Comment Out by Ton! 20131028

        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order'); // Edit by Ak add 'STK_T_Order' for get data in this table
        // p($flow_detail); exit;
        
        $process_id = $flow_detail[0]->Process_Id;
        $present_state = $flow_detail[0]->Present_State;

        $document_no = $flow_detail[0]->Document_No;
        $owner_id = $flow_detail[0]->Owner_Id; // Add by Ton! 20131028
        $order_id = $flow_detail[0]->Order_Id;
        $renter_id = $flow_detail[0]->Renter_Id;
        $warehouse_from = $flow_detail[0]->Source_Id;
        $warehouse_to= $flow_detail[0]->Destination_Id;
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

        $DeliveryTime = $flow_detail[0]->Estimate_Action_Time;  //Add By Joke 21/6/2016
        $DestinationDetail = $flow_detail[0]->Destination_Detail; //Add By Joke 21/6/2016
//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowPreDispatchList"); // Button Permission. Add by Ton! 20140131
        $order_data = $this->preDispModel->queryDataFromFlowId($flow_id);
// p($order_data); exit;
        foreach ($order_data as $OrderObject) {
            $order_data_value = $OrderObject;
        }


        /**
         * GET Activity By  : show pick by
         * @add code by kik :  get sub_module for show activity by
         */
//        $module_activity = 'pre_dispatch';
//        $sub_module_activity = $this->order_movement->get_submodule_activity($order_data_value->Order_Id,$module_activity,'approve_pre_dispatch');
//        //end of get activity by

        $order_by = 'a.Product_Code';
//        $order_detail_data = $this->preDispModel->query_from_OrderDetailId($order_data_value->Order_Id); //ADD BY POR 2013-12-06 function เธ—เธตเน�เน�เธ�เน�เน�เธ—เธ� queryDataFromOrderDetailId เน�เธ”เธขเธ•เน�เธญเธ�เธชเน�เธ� parameter เน�เธ�เธญเธตเธ� 1 เธ•เธฑเธงเธ�เธทเธญ step เน�เธ�เธ�เธฒเธฃเธซเธฒเธ�เธทเน�เธญเธ�เธนเน�เธ—เธณเธฃเธฒเธขเธ�เธฒเธฃ 14 เธ�เธทเธญเธ�เธนเน�เธ—เธณเธฃเธฒเธขเธ�เธฒเธฃ picking
        $order_detail_data = $this->preDispModel->query_from_OrderDetailId($order_data_value->Order_Id, $order_by, false, NULL, NULL);
        $order_detail_data = $order_detail_data->result(); // Add By Akkarapol, 21/10/2013, เน€เธ�เน�เธ•เธ�เน�เธฒเธ•เธฑเธงเน�เธ�เธฃเน�เธซเธกเน� เน€เธ�เธฃเธฒเธฐเธ�เน�เธฒเธ—เธตเน� Return เธกเธฒเธ�เธฒเธ�เธ�เธฑเธ�เธ�เน�เธ�เธฑเน�เธ� queryDataFromOrderDetailId เธ�เธฑเน�เธ�เธชเน�เธ�เธกเธฒเน€เธ�เน�เธ�เน�เธ�เธ� $query เน€เธฅเธข เน€เธ�เธทเน�เธญเน�เธซเน�เธฃเธญเธ�เธฃเธฑเธ�เธ�เธฒเธฃเน€เธฃเธตเธขเธ�เน�เธ�เน�เธ�เธฒเธ�เธ—เธตเน�เธซเธฅเธฒเธ�เธซเธฅเธฒเธขเธ�เธถเน�เธ�
    // p($order_detail_data); exit;
//        foreach ($order_detail_data as $OrderDetailObject) {
//            $order_detail_data_value = $OrderDetailObject;
//        }

//   old.////////////////////////////////////////////////////////////////////
//        foreach ($order_detail_data as $OrderDetailObject) {
//            $order_detail_data_value = $OrderDetailObject;
//        }
//   old.////////////////////////////////////////////////////////////////////
    //   new.////////////////////////////////////////////////////////////////////
        $order_detail_data_group = array();
        $active_bot_gen = 1;
        foreach ($order_detail_data as $keyfg => $OrderDetailObject){
            $order_detail_data_value = $OrderDetailObject;
            
            if(($OrderDetailObject->Inbound_Item_Id != null) || ($OrderDetailObject->Inbound_Item_Id != '')){
                $active_bot_gen = 0;
            }
            if($keyfg == 0){
                $temp['Product_Code'] = $OrderDetailObject->Product_Code;
                $temp['Reserv_Qty'] = $OrderDetailObject->Reserv_Qty;             
                $order_detail_data_group[$OrderDetailObject->Product_Code] = (object) $temp;
                $temp = null;
            }
            else{
                if($OrderDetailObject->Product_Code == $order_detail_data[$keyfg-1]->Product_Code ){
                    $order_detail_data_group[$OrderDetailObject->Product_Code]->Reserv_Qty +=  $OrderDetailObject->Reserv_Qty;     
                }
                else{
                    $temp['Product_Code'] = $OrderDetailObject->Product_Code;
                    $temp['Reserv_Qty'] = $OrderDetailObject->Reserv_Qty;               
                    $order_detail_data_group[$OrderDetailObject->Product_Code] = (object) $temp;  
                    $temp = null;
                }
            }
        }
            $order_detail_data_group = array_values($order_detail_data_group);
//        p($active_bot_gen);
//        p($order_detail_data_group);
//        p($order_detail_data);
//   new.////////////////////////////////////////////////////////////////////
        
        
        
        // p($OrderDetailObject); exit;
        $this->load->model("company_model", "company");
        $this->load->model("system_management_model", "sys");

        #ISSUE 2772 Pre-Dispatch : Search Product by Status & Sub Status
        #DATE:2012-09-12
        #BY:KIK
        #เน€เธ�เธดเน�เธกเธ�เธฒเธฃเธ�เน�เธ�เธซเธฒเธ”เน�เธงเธข Status & Sub Status เน�เธ�เธชเน�เธงเธ�เธ�เธญเธ� get product detail
        #START New Comment Code #ISSUE 2772
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
        #End New Comment Code #ISSUE 2772
        #Get Renter [Company Renter] list
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
//        $renter_list = genOptionDropdown($r_renter, "COMPANY");
        $renter_list = genOptionDropdown($r_renter, "COMPANY", TRUE, FALSE); // Edit by kik! 20131107
        #Get Shipper[Company Owner] list
        $q_shipper = $this->company->getOwnerAll();
        $r_shipper = $q_shipper->result();
//        $shipper_list = genOptionDropdown($r_shipper, "COMPANY");
        $shipper_list = genOptionDropdown($r_shipper, "COMPANY", TRUE, FALSE); // Edit by kik! 20131107
        #Get Consignee[Company Customer]  list
//        $q_consignee = $this->company->getCustomerAll(); // Comment By Akkarapol, 12/09/2013, เธ�เธญเธกเน€เธกเน�เธ�เธ•เน�เธ—เธดเน�เธ�เน€เธ�เธฃเธฒเธฐเธ•เน�เธญเธ�เธ”เธถเธ�เธ�เน�เธญเธกเธนเธฅเธ�เธญเธ� Branch เธกเธฒเน�เธ�เน�เน�เธ—เธ�
        $q_consignee = $this->company->getBranchNotOwner($this->session->userdata('branch_id')); // Add By Akkarapol, 12/09/2013, เน€เธ�เธดเน�เธกเธ�เธฒเธฃ get เธ�เน�เธญเธกเธนเธฅเธ�เธญเธ� branch เธ—เธตเน�เน�เธกเน�เน�เธ�เน�เธ�เธญเธ�เธ•เธ�เน€เธญเธ�เน€เธ�เน�เธฒเธกเธฒเน€เธ�เธทเน�เธญเธ�เธณเน�เธ�เน�เธชเน�เธ—เธตเน� Consignee
        $r_consignee = $q_consignee->result();
        // p($r_consignee ); 
        // exit;
    //    $consignee_list = genOptionDropdown($r_consignee, "COMPANY");
        $consignee_list = genOptionDropdown($r_consignee, "COMPANY", TRUE, FALSE); // Edit by kik! 20131107
        $consignee_id = 1;
        // $consignee_name = $consignee_list['12'];

        #Get Dispatch Type list
        $dispatch_type = $this->sys->getDispatchType();
//        $dispatch_type_list = genOptionDropdown($dispatch_type, "SYS");
        $dispatch_type_list = genOptionDropdown($dispatch_type, "SYS", TRUE, TRUE); // Edit by kik! 20131107

        $renter_id_select = form_dropdown("renter_id_select", $renter_list, $order_data_value->Renter_Id, "id=renter_id_select class=required");
        $frm_warehouse_select = form_dropdown("frm_warehouse_select", $shipper_list, $order_data_value->Source_Id, "id=frm_warehouse_select class=required");
        $to_warehouse_select = form_dropdown("to_warehouse_select", $consignee_list, $order_data_value->Destination_Id, "id=to_warehouse_select class=required");
        $dispatch_type_select = form_dropdown("dispatch_type_select", $dispatch_type_list, $order_data_value->Doc_Type, "id=dispatch_type_select class=required");

        $query = $this->preDispModel->getPreDispatchAll();
        $pre_dispatch_list = $query->result();

        //ADD check show/hide datatable of container:BY POR 2014-09-08
        $init_invoice = array();
        if ($conf_inv):
//            $init_invoice['init'] = ", {
//                    onblur: 'submit',
//                    sUpdateURL: '".site_url() . '/pre_dispatch/saveEditedRecord'."',
//                    event: 'click'
//                }";
            $init_invoice['init'] = ", {
                    onblur: 'submit',
                    sUpdateURL: function(value, settings) {return(value);},
                    event: 'click'
                }";
            $init_invoice['reinit'] = ",null";
            $init_invoice['reinit_col'] = "";
        else :
            $init_invoice['init'] = "";
            $init_invoice['reinit'] = ",null";
            $init_invoice['reinit_col'] = ",null";
        endif;
        //END ADD
        //ADD check show/hide datatable of container:BY POR 2014-09-08
        $init_container = "";
        /*
          if($conf_cont):
          $init_container = ",{
          data : master_container_dropdown_list,
          event: 'click',
          type: 'select',
          onblur: 'submit',
          is_container: true,
          sUpdateURL: function(value, settings) {
          var oTable = $('#ShowDataTableForInsert').dataTable();
          var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
          oTable.fnUpdate(value, rowIndex, ci_cont_id);
          return value;
          }
          }";
          endif;
         */
        //END ADD
        #Start code for ISSUE 3320 : by kik : 20140113
        $price = '';
        if ($conf_price_per_unit):
            //$price = ',null';
            //ADD BY POR 2014-06-02 เพิ่มให้สามารถกรอกราคาและเลือกหน่วยได้
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
            $price = " ,{
                    sSortDataType: \"dom-text\",
                    sType: \"numeric\",
                    type: 'text',
                    onblur: \"submit\",
                    event: 'click',
                    is_required: true,
                    loadfirst: true,
                    cssclass: \"required number\",
                    sUpdateURL: function(value, settings) {return(value);},
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
        //END ADD;
        else:
//            $price = "  , {
//                    onblur: 'submit',
//                    sUpdateURL: '" . site_url() . '/pre_dispatch/saveEditedRecord' . "',
//                    event: 'click',
//                }";
            $price = "  , {
                    onblur: 'submit',
                    sUpdateURL: function(value, settings) {return(value);},
                    event: 'click',
                }";
            $unitofprice = ',null';

        endif;
        #end add code : by kik : 20140113
        //ADD BY POR 2014-08-19
        $doc_refer_container = array();
        $container_name = $this->container->getContainerByOrderId($order_id)->result_array();
        if (!empty($container_name)):
            foreach ($container_name as $key_container => $container):
                $doc_refer_container[] = $container['Cont_No'] . " " . $container['Cont_Size_No'] . $container['Cont_Size_Unit_Code'];
            endforeach;
        endif;


        //ADD BY POR 2014-08-19 select container size for size list in container
        $container_size = $this->container->getContainerSize()->result();
        $_container_size = array();
        foreach ($container_size as $idx => $value) {
            $_container_size[$idx]['Id'] = $value->Cont_Size_Id;
            $_container_size[$idx]['No'] = $value->Cont_Size_No;
            $_container_size[$idx]['Unit_Code'] = $value->Cont_Size_Unit_Code;
        }
        $container_size_list = json_encode($_container_size);
        //END ADD
        //ADD BY POR 2014-08-19
        $container_list = $this->container->get_container($order_id)->result();
        $_container_list = array();
        foreach ($container_list as $idx => $value) {
            $_container_list[$idx]['id'] = $value->Cont_Id;
            $_container_list[$idx]['name'] = tis620_to_utf8($value->Cont_No);
            $_container_list[$idx]['size'] = $value->Cont_Size_Id;
        }
        $arrayobject = new ArrayObject($_container_list);
        $container_list = json_encode($arrayobject);

        if ($this->settings['dp_type_link_prod_status']['active']):
            foreach ($this->settings['dp_type_link_prod_status']['object'] as $key_object => $object):
                $dp_type_link_prod_status[$object['dp_type']] = $object['prod_status'];
            endforeach;
            $dp_type_link_prod_status = json_encode($dp_type_link_prod_status);
        else:
            $dp_type_link_prod_status = FALSE;
        endif;
//        p($order_detail_data);
//        p($order_detail_data_group);
        
        #// register token
        $token = register_token($flow_id, $present_state, $process_id);
        // end config token
    //    p($to_warehouse_select);exit();
        $str_form = $this->parser->parse('form/' . $data_form->form_name, array("parameter" => form_input('warehouse_form', 'whs_form')
            , "test_parse" => "test pass parse from controller"
            , "renter_id_select" => $renter_id_select
            , "price_per_unit" => $conf_price_per_unit // add by kik : 20140113
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
            , "order_detail_data_group" => $order_detail_data_group
            , "active_bot_gen" => $active_bot_gen
            , "remark" => trim($remark)
            , "est_action_date" => trim($est_dispatch_date)
            , "flow_id" => $flow_id
            , "order_id" => $order_id
            , "owner_id" => $owner_id
            , "is_urgent" => $is_urgent
            , "token" => $token
            , "dp_type_link_prod_status" => $dp_type_link_prod_status
            , "doc_refer_container" => $doc_refer_container
            , "container_size_list" => $container_size_list
            , "container_list" => $container_list
            , "statusprice" => $conf_price_per_unit
            , "conf_inv" => $conf_inv
            , "conf_cont" => $conf_cont
            , "conf_pallet" => $conf_pallet
            , "conf_invoice_require" => $conf_invoice_require
            , "DeliveryTime" => iconv("UTF-8", "TIS-620", $DeliveryTime)  //Add By Joke 21/6/2016
            , "DestinationDetail" => iconv("UTF-8", "TIS-620", $DestinationDetail) //Add By Joke 21/6/2016
            , "data_form" => (array) $data_form
                )
                , TRUE);
        if($present_state == 0){
//            if($active_bot_gen == 1){
                $data_form->str_buttun .= '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="GEN" id="GEN" ONCLICK="gen()">';
//            }
//            else{
//                p( $this->config->item('css_button'));
//                $data_form->str_buttun .= '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="GEN2" id="GEN" ONCLICK="gen()">';
//            }
        }
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'init_container' => $init_container
            , 'init_invoice' => $init_invoice['init']
            , 'reinit_invoice' => $init_invoice['reinit']
            , 'reinit_invoice_col' => $init_invoice['reinit_col']
            , 'price' => $price      //add by kik : 20140113
            , 'unitofprice' => $unitofprice //ADD BY POR 2014-06-02 ให้ส่งตัวแปรเกี่ยวกับการแสดงหน่วยของราคา
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPreDispatch"></i>'
            , "productStatus_select" => $productStatus_select // add by kik 12-09-2013
            , "productSubStatus_select" => $productSubStatus_select// add by kik 12-09-2013
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'
//             , 'button_pre_dispatch_gen' => '<INPUT ID="GEN" TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . GEN . '" ONCLICK="gen()">'
            , 'button_action' => $data_form->str_buttun
            , 'user_login' => $this->session->userdata('username')
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
        ));
    }

    public function openPreDispatch() {
        /**
         * Set Variable
         */
        $check_not_err = TRUE;
        $return = array();

        $data = $this->input->post();
        // p($data ); exit;
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_code = $this->input->post("ci_prod_code");
        $order_id = $this->input->post("order_id");
        $prod_list = $this->input->post("prod_list");
        $renter_id_select = $this->input->post("renter_id_select");
        $warehouse_from = $this->input->post("frm_warehouse_select");
        $warehouse_to = $this->input->post("to_warehouse_select");
        $DeliveryTime = $this->input->post("DeliveryTime");  //Add By Joke 16/6/2016
        $DestinationDetail = $this->input->post("DestinationDetail"); //Add By Joke 16/6/2016
        
//        p($data);
//        exit();
        /**
         * Start token
         */
        #Retrive Data from Table
        $token = $this->input->post('token');
//        p($token);

        $flow_id = $this->input->post('flow_id');
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));
//        p($flow_info);
//        exit();

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
//            p($response);

            if (!$response) :
                $array_return['critical'][]['message'] = "Document already pass this step.";
                $json['status'] = "save";
                $json['return_val'] = $array_return;
                $check_not_err = false;
            endif;
        endif;
//exit();
        /**
         * ================== Start Transaction =========================
         */
        $this->transaction_db->transaction_start();


        /**
         * Save Order [Renter_Id, Owner_Id, Source_Id, Destination_Id] from Import Pre-Dispatch ----- Add by Ton! 20131028
         */
        if ($check_not_err):
            $rOrder['Renter_Id'] = $renter_id_select;
            $rOrder['Owner_Id'] = $warehouse_from; // ***** Owner กับ Source คือคนเดียวกันโดยเอาจากช่อง shipper
            $rOrder['Source_Id'] = $warehouse_from; // ***** Owner กับ Source คือคนเดียวกันโดยเอาจากช่อง shipper
            $rOrder['Destination_Id'] = $warehouse_to;
            $whereOrder['Order_Id'] = $order_id;
//            p($rOrder);
            $result_update_order = $this->stock->updateOrder($rOrder, $whereOrder);
            if (!$result_update_order):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not Save Order.";
            endif;
        endif;
//                $check_not_err = FALSE;


        /**
         * @author Thida
         * 2013-11-14
         * for check data not have inbound id and update order detail form data inbound table
         */
        if ($check_not_err):
            $status = $this->chk_update_orderDetail($prod_list, $ci_item_id, $ci_inbound_id, $ci_reserv_qty);
            if (!empty($status['critical'])):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update data.";
            endif;
        endif;


        /**
         * Check validate Est.Balance and ReOrderPoint
         */
        if ($check_not_err):
            $resultCompare = $this->balance->_chkPDreservBeforeApprove($ci_reserv_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Order_Detail');
            $return = array_merge_recursive($return, $resultCompare);
            if (!empty($resultCompare['critical'])):
                $check_not_err = FALSE;

            /**
             * Set Alert Zone (set Error Code, Message, etc.)
             */
//                    $return['critical'][]['message'] = "Can not Save Order.'";
            endif;

            $chk_re_order_point = $this->balance->chk_re_order_point_before_approve($ci_reserv_qty, $ci_prod_code, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Order_Detail');
            $return = array_merge_recursive($return, $chk_re_order_point);
            if (!empty($chk_re_order_point['critical'])):
                $check_not_err = FALSE;

            /**
             * Set Alert Zone (set Error Code, Message, etc.)
             */
//                    $return['critical'][]['message'] = "Can not Save Order.'";
            endif;
        endif;


        /**
         * updateProcess
         */
        if ($check_not_err):
            $respond = $this->_updateProcess($this->input->post());
//                if ("C001" != $respond) :
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

            $set_return['message'] = "Confirm Pre-Dispatch Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Confirm Pre-Dispatch Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
        endif;

        echo json_encode($json);
    }

    public function _openPreDispatch() {

        $this->confirmPreDispatch();

        exit();

        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

        // add by kik (03-10-2013)
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_prod_code = $this->input->post("ci_prod_code");
        $prod_list = $this->input->post("prod_list");


        /**
         * set Variable
         */
        $return = array();

        $resultCompare = $this->balance->_chkPDreservBeforeOpen($ci_reserv_qty, $ci_inbound_id, $prod_list, SEPARATOR);
        $return = array_merge_recursive($return, $resultCompare);

        $chk_re_order_point = $this->balance->chk_re_order_point($ci_reserv_qty, $ci_prod_code, $prod_list, SEPARATOR);
        $return = array_merge_recursive($return, $chk_re_order_point);

        if (!empty($return['critical'])) :

            $json['status'] = "validation";
            $json['return_val'] = $return;

        else:

            $renter_id_select = $this->input->post("renter_id_select");
            $warehouse_from = $this->input->post("frm_warehouse_select");
            $warehouse_to = $this->input->post("to_warehouse_select");
            $dispatch_type_select = $this->input->post("dispatch_type_select");
            $estDispatchDate = $this->input->post("estDispatchDate");

            # Parameter of document number
            $doc_refer_int = strtoupper(iconv("UTF-8", "TIS-620", $this->input->post("doc_refer_int")));
            $doc_refer_ext = strtoupper(iconv("UTF-8", "TIS-620", $this->input->post("doc_refer_ext")));
            $doc_refer_inv = strtoupper(iconv("UTF-8", "TIS-620", $this->input->post("doc_refer_inv")));
            $doc_refer_ce = strtoupper(iconv("UTF-8", "TIS-620", $this->input->post("doc_refer_ce")));
            $doc_refer_bl = strtoupper(iconv("UTF-8", "TIS-620", $this->input->post("doc_refer_bl")));

            $process_id = $this->input->post("process_id");
            $process_type = $this->input->post("process_type");
            $present_state = $this->input->post("present_state");
            $action_type = $this->input->post("action_type");
            $next_state = $this->input->post("next_state");
            $remark = $this->input->post("remark");
            $user_id = $this->session->userdata("user_id");
            $is_urgent = $this->input->post("is_urgent");   //add for ISSUE 3312 : by kik : 20140120
            # Parameter Order Detail
            $prod_list = $this->input->post("prod_list");
            //p($prod_list); exit();
            //$prod_del_list		= $this->input->post("prod_del_list");
            # Parameter Index Datatable
            $ci_prod_code = $this->input->post("ci_prod_code");
            $ci_lot = $this->input->post("ci_lot");
            $ci_serial = $this->input->post("ci_serial");
            $ci_mfd = $this->input->post("ci_mfd");
            $ci_exp = $this->input->post("ci_exp");
            $ci_reserv_qty = $this->input->post("ci_reserv_qty");
            $ci_balance_qty = $this->input->post("ci_balance_qty");
            $ci_remark = $this->input->post("ci_remark");
            $ci_prod_id = $this->input->post("ci_prod_id");
            $ci_prod_status = $this->input->post("ci_prod_status");
            $ci_unit_id = $this->input->post("ci_unit_id");
            $ci_inbound_id = $this->input->post("ci_inbound_id");
            $ci_suggest_loc = $this->input->post("ci_suggest_loc");
            $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");
            $ci_invoice = $this->input->post("ci_invoice");
            $ci_container = $this->input->post("ci_container");
            $ci_cont_id = $this->input->post("ci_cont_id");
            $ci_pallet_code = $this->input->post("ci_pallet_code");

            // Parameter for Invoice and Container
            $container_list = $this->input->post("container_list");     //ADD BY POR 2014-07-09 size of next first record
        
            $container_list = json_decode($container_list);

            if ($conf_price_per_unit) :
                $ci_price_per_unit = $this->input->post("ci_price_per_unit");
                $ci_unit_price = $this->input->post("ci_unit_price");
                $ci_all_price = $this->input->post("ci_all_price");
                $ci_unit_price_id = $this->input->post("ci_unit_price_id");
            endif;
            //END ADD
            //add Urgent for ISSUE 3312 : by kik : 20140120
            if ($is_urgent != ACTIVE) :
                $is_urgent = INACTIVE;
            endif;


            /**
             * ================== Start Transaction =========================
             */
            $this->transaction_db->transaction_start();


            /**
             * Set Variable
             */
            $check_not_err = TRUE;


            /**
             * Generate DDR Number
             */
            if ($check_not_err):
//                $document_no = createDDRNo();
                $document_no = create_document_no_by_type("DDR"); // Add by Ton! 20140428
                if (empty($document_no) || $document_no == ''):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not create 'DDR No'.";
                endif;
            endif;


            /**
             * Create new Workflow
             */
            if ($check_not_err):
                $data['Document_No'] = $document_no;
                list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data); //Edit by Ton! 20131021
                if (empty($flow_id) || $flow_id == '' || empty($action_id) || $action_id == ''):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not Create new Workflow.";
                endif;
            endif;


            /**
             * Create new Order
             */
            if ($check_not_err):
                $estDispatchDate = convertDate($estDispatchDate, "eng", "iso", "-");
                $order = array(
                    'Flow_Id' => $flow_id
                    , 'Document_No' => $document_no
                    , 'Doc_Refer_Ext' => $doc_refer_ext
                    , 'Doc_Refer_AWB' => $doc_refer_ext
                    , 'Doc_Refer_Int' => $doc_refer_int
                    , 'Doc_Refer_Inv' => $doc_refer_inv
                    , 'Doc_Refer_CE' => $doc_refer_ce
                    , 'Doc_Refer_BL' => $doc_refer_bl
                    , 'Doc_Type' => $dispatch_type_select
                    , 'Estimate_Action_Date' => $estDispatchDate
                    , 'Source_Type' => 'Warehouse'
                    , 'Destination_Type' => 'Customer'
                    , 'Create_By' => $user_id
                    , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                    , 'Create_Date' => date("Y-m-d H:i:s")
                    , 'Process_Type' => $process_type
                    , 'Renter_Id' => $renter_id_select
                    , 'Source_Id' => $warehouse_to
                    , 'Destination_Id' => $warehouse_to
                    , 'Owner_Id' => $warehouse_from
                    , 'Is_urgent' => $is_urgent
                    , 'Estimate_Action_Time' => iconv("UTF-8", "TIS-620", $DeliveryTime) // Add By Joke 20/6/2016
                    , 'Destination_Detail' => iconv("UTF-8", "TIS-620", $DestinationDetail) // Add By Joke 20/6/2016
                );
                $order_id = $this->stock->addOrder($order);
                if (empty($order_id) || $order_id == ""):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not Create new Order.";
                endif;
            endif;


            /**
             * Insert Container List : BY POR 2014-08-19
             */
            if ($conf_cont):
                if ($check_not_err && $container_list != "") :
                    //return value is array because use id on container in datatable
                    $result_container_list = $this->container->save_container($order_id, $container_list, TRUE);
                    if (is_array($result_container_list)):
                    else:
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Save container failed.";
                        log_message("ERROR", "Save container failed");
                    endif;
                endif;
            endif;


            /**
             * Create new OrderDetail
             * Update PD_Reserve_Qty
             */
            if ($check_not_err):
                $order_detail = array();
                $data = array();
                if (!empty($prod_list)) :
                    foreach ($prod_list as $rows) :
                        $detail = array();
                        $params = array();
                        $a_data = explode(SEPARATOR, $rows);

                        $detail['Order_Id'] = $order_id;
                        $detail['Product_Id'] = $a_data[$ci_prod_id];
                        $detail['Product_Code'] = $a_data[$ci_prod_code];
                        $detail['Reserv_Qty'] = str_replace(",", "", $a_data[$ci_reserv_qty]);
                        $detail['Unit_Id'] = $a_data[$ci_unit_id];
                        $detail['Product_Lot'] = iconv("UTF-8", "TIS-620", $a_data[$ci_lot]);
                        $detail['Product_Serial'] = iconv("UTF-8", "TIS-620", $a_data[$ci_serial]);
                        $detail['Product_Status'] = $a_data[$ci_prod_status];
                        $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                        $detail['Inbound_Item_Id'] = $a_data[$ci_inbound_id];
                        $detail['Suggest_Location_Id'] = $a_data[$ci_suggest_loc];
                        $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);

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

                        /* DO NOT REMOVE */
                        /*
                          $params['Inbound_Id'] = $a_data[$ci_inbound_id];
                          $params['Reserv_Qty'] = str_replace(",", "", $a_data[$ci_reserv_qty]);
                          $params['Order_Id'] = $order_id;
                          $params['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]); */

                        if ($conf_price_per_unit) :
                            $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]);
                            $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                            $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]);

                        /* DO NOT REMOVE */
                        /* $params['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]);
                          $params['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                          $params['All_Price'] = $params['Reserv_Qty'] * $params['Price_Per_Unit']; */
                        endif;

                        if ($conf_pallet) :
                            if ($a_data[$ci_pallet_code] != "" && !empty($a_data[$ci_pallet_code])):
                                $detail['Pallet_Id'] = $this->pl->get_palletId_by_code($a_data[$ci_pallet_code]);
                            else:
                                $detail['Pallet_Id'] = NULL;
                            endif;
                        endif;

                        //check invoice for insert or update :ADD BY POR 2014-08-19
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
                            $params['Invoice_Id'] = $Invoice_Id;
                        endif;
                        //END Insert Invoice

                        $order_detail[] = $detail;
                        $data[] = $params;
                    endforeach;
                endif;

                if (!empty($order_detail)):

                    /**
                     * Create new OrderDetail
                     */
                    $result_order_detail = $this->stock->addOrderDetail($order_detail);
                    /* DO NOT REMOVE */
                    //$result_order_detail = $this->stock->addOrderDetailByInbound($data);

                    if ($result_order_detail <= 0):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not Create new OrderDetail.";
                    endif;


                    /**
                     * Update PD_Reserve_Qty
                     */
                    $result_PD_reserv_qty = $this->stock->reservPDReservQtyArray($order_detail);
                    if (!$result_PD_reserv_qty):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not Update PD_Reserve_Qty.";
                    endif;
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

                $set_return['message'] = "Save Pre-Dispatch Complete.";
                $return['success'][] = $set_return;
                $json['status'] = "save";
                $json['return_val'] = $return;
            else:
                /**
                 * ================== Rollback Transaction =========================
                 */
                $this->transaction_db->transaction_rollback();

                $array_return['critical'][]['message'] = "Save Pre-Dispatch Incomplete.";
                $json['status'] = "save";
                $json['return_val'] = array_merge_recursive($array_return, $return);
            endif;

        endif;

        echo json_encode($json);
    }

    function update_Inbound_ItemId() {
        $order_id = $this->input->post("order_id");
        $order_detail_items = $this->input->post("order_detail");

        $order_detail_data = "";
        $order_detail_data.="{" . $order_detail_items . ",}";
        $query = $this->preDispModel->process_update_Inbound_ItemId($order_id, $order_detail_data)->result_array();
// p($query ); exit;
//	p($query); exit;

        foreach ($query as $key_rs => $rs):
            $query[$key_rs]['Location_Code'] = @$this->location->get_location_code_by_inbound_id($rs['Inbound_item_Id'])->row()->Location_Code;
            // p($rs); exit;
        endforeach;
        // p($query ); exit;
        echo json_encode($query);
    }

    //END
    # DEV-Task #1675
    # Pre-Dispatch>Import Pre-Dispatch from Excel
    # add function chk_update_orderDetail for check data not have inbound id and update order detail form data inbound table
    # by kik : 2013-11-14

    function chk_update_orderDetail($prod_list, $ci_item_id, $ci_inbound_id, $ci_reserv_qty) {

        $chk_status = TRUE;
        $return = array();

        foreach ($prod_list as $rows) :
            $a_data = explode(SEPARATOR, $rows);
            $is_new = $a_data[$ci_item_id]; // add by kik : 05-11-2013

            if ("new" != $is_new) {     // add by kik : 05-11-2013
                $column = array(
                    "Actual_Location_Id"
                    , "Product_Status"
                    , "Product_Sub_Status"
                    , "Product_Lot"
                    , "Product_Serial"
                    , "Price_Per_Unit"
                    , "Unit_Price_Id"
                    , "convert(varchar(25),Product_Mfd,120) as Product_Mfd"
                    , "convert(varchar(25),Product_Exp,120) as Product_Exp"
                );

                $where['Inbound_Id'] = $a_data[$ci_inbound_id];

                $detailForUpdate = $this->stock->getStockDetailId($column, $where)->result();

                if (!empty($detailForUpdate)):

                    unset($where);

                    $detail['Suggest_Location_Id'] = $detailForUpdate[0]->Actual_Location_Id;
                    $detail['Product_Status'] = $detailForUpdate[0]->Product_Status;
                    $detail['Product_Sub_Status'] = $detailForUpdate[0]->Product_Sub_Status;
                    $detail['Product_Lot'] = $detailForUpdate[0]->Product_Lot;
                    $detail['Product_Serial'] = $detailForUpdate[0]->Product_Serial;
                    $detail['Product_Mfd'] = $detailForUpdate[0]->Product_Mfd;
                    $detail['Product_Exp'] = $detailForUpdate[0]->Product_Exp;
                    $detail['Price_Per_Unit'] = $detailForUpdate[0]->Price_Per_Unit;
                    $detail['Unit_Price_Id'] = $detailForUpdate[0]->Unit_Price_Id;
                    $detail['All_Price'] = $detailForUpdate[0]->Price_Per_Unit * $a_data[$ci_reserv_qty];
                    $detail['Inbound_Item_Id'] = $a_data[$ci_inbound_id];

                    $where['Item_Id'] = $a_data[$ci_item_id];
                    $where_detail = 'Inbound_Item_Id IS NULL';

                    # by kik : 2013-11-14
                    # updateInboundItemId return data 3 case
                    # 0 : can update data > 0 record. update success
                    # 1 : not have updata data because does not meet the conditions.
                    # 2 : update unsuccess.

                    $upinbound = $this->preDispModel->updateInboundItemId($detail, $where, $where_detail);
                    unset($where);

                    if ($upinbound == 0) :
                        $result_Update_qty = $this->stock->reservPDReservQty($a_data[$ci_inbound_id], $a_data[$ci_reserv_qty], "+");
                        if (!$result_Update_qty):
                            $chk_status = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not update PD_Reserv_Qty in Inbound.";
                            break;
                        endif;

                    elseif ($upinbound == 2):

                        $chk_status = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not update Inbound.";
                        break;

                    endif;

                else:
                    # not have inbound data

                    $chk_status = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Not have Inbound data.";
                    break;

                endif; //end check have inbound data
            }

        endforeach;

        return $return;
        //###END
    }

    #End New Comment Code #ISSUE 1675 : 2013-11-14

    function confirmPreDispatch() {
        /**
         * Set Variable
         */
//        p('test');
//        exit();
        
       
        $check_not_err = TRUE;
        $return = array();

        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_code = $this->input->post("ci_prod_code");
        $order_id = $this->input->post("order_id");
        $prod_list = $this->input->post("prod_list");
        $renter_id_select = $this->input->post("renter_id_select");
        $warehouse_from = $this->input->post("frm_warehouse_select");
        $warehouse_to = $this->input->post("to_warehouse_select");

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
         * Save Order [Renter_Id, Owner_Id, Source_Id, Destination_Id] from Import Pre-Dispatch ----- Add by Ton! 20131028
         */
        if ($check_not_err):
            $rOrder['Renter_Id'] = $renter_id_select;
            $rOrder['Owner_Id'] = $warehouse_from;
            $rOrder['Source_Id'] = $warehouse_to;
            $rOrder['Destination_Id'] = $warehouse_to;
            $whereOrder['Order_Id'] = $order_id;
            $result_update_order = $this->stock->updateOrder($rOrder, $whereOrder);
            if (!$result_update_order):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not Save Order.";
            endif;
        endif;


        /**
         * @author Thida
         * 2013-11-14
         * for check data not have inbound id and update order detail form data inbound table
         */
        if ($check_not_err):
            $status = $this->chk_update_orderDetail($prod_list, $ci_item_id, $ci_inbound_id, $ci_reserv_qty);
            if (!empty($status['critical'])):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update data.";
            endif;
        endif;


        /**
         * Check validate Est.Balance and ReOrderPoint
         */
        if ($check_not_err):
            $resultCompare = $this->balance->_chkPDreservBeforeApprove($ci_reserv_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Order_Detail');
            $return = array_merge_recursive($return, $resultCompare);
            if (!empty($resultCompare['critical'])):
                $check_not_err = FALSE;

            /**
             * Set Alert Zone (set Error Code, Message, etc.)
             */
//                    $return['critical'][]['message'] = "Can not Save Order.'";
            endif;

            $chk_re_order_point = $this->balance->chk_re_order_point_before_approve($ci_reserv_qty, $ci_prod_code, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Order_Detail');
            $return = array_merge_recursive($return, $chk_re_order_point);
            if (!empty($chk_re_order_point['critical'])):
                $check_not_err = FALSE;

            /**
             * Set Alert Zone (set Error Code, Message, etc.)
             */
//                    $return['critical'][]['message'] = "Can not Save Order.'";
            endif;
        endif;


        /**
         * updateProcess
         */
        if ($check_not_err):
            $respond = $this->_updateProcess($this->input->post());
//                if ("C001" != $respond) :
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

            $set_return['message'] = "Confirm Pre-Dispatch Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Confirm Pre-Dispatch Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
        endif;

        echo json_encode($json);
    }

    function approvePreDispatch() {

        /**
         * set Variable
         */
        $check_not_err = TRUE;
        $return = array();

        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_code = $this->input->post("ci_prod_code");
        $order_id = $this->input->post("order_id");
        $prod_list = $this->input->post("prod_list");

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
         * @author Thida
         * 2013-11-14
         * for check data not have inbound id and update order detail form data inbound table
         */
        if ($check_not_err):
            $status = $this->chk_update_orderDetail($prod_list, $ci_item_id, $ci_inbound_id, $ci_reserv_qty);
            if (!empty($status['critical'])) :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update data.";
                $return = array_merge_recursive($return, $status);
            endif;
        endif;


        /**
         * Check validate Est.Balance and ReOrderPoint
         */
        if ($check_not_err):
            $resultCompare = $this->balance->_chkPDreservBeforeApprove($ci_reserv_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Order_Detail');
            $return = array_merge_recursive($return, $resultCompare);
            if (!empty($resultCompare['critical'])):
                $check_not_err = FALSE;

            /**
             * Set Alert Zone (set Error Code, Message, etc.)
             */
//                        $return['critical'][]['message'] = "Can not Save Order.'";
            endif;

            $chk_re_order_point = $this->balance->chk_re_order_point_before_approve($ci_reserv_qty, $ci_prod_code, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Order_Detail');
            $return = array_merge_recursive($return, $chk_re_order_point);
            if (!empty($chk_re_order_point['critical'])):
                $check_not_err = FALSE;

            /**
             * Set Alert Zone (set Error Code, Message, etc.)
             */
//                    $return['critical'][]['message'] = "Can not Save Order.'";
            endif;
        endif;


        /**
         * updateProcess
         */
        if ($check_not_err):
            $respond = $this->_updateProcess($this->input->post());
//            if ("C001" != $respond) :
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

            $set_return['message'] = "Approve Pre-Dispatch Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Approve Pre-Dispatch Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
        endif;

        echo json_encode($json);
    }

    function _updateProcess() {
        #Load config
        $conf = $this->config->item('_xml'); // By ball : 20140707
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

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
        if (empty($user_id)) {
            $user_id = $this->session->userdata("user_id");
        }

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
        $is_urgent = $this->input->post("is_urgent");

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
        $ci_balance_qty = $this->input->post("ci_balance_qty");
        $ci_remark = $this->input->post("ci_remark");
        $ci_prod_id = $this->input->post("ci_prod_id");
        $ci_prod_status = $this->input->post("ci_prod_status");
        $ci_unit_id = $this->input->post("ci_unit_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");
        $ci_invoice = $this->input->post("ci_invoice");
        $ci_container = $this->input->post("ci_container");
        $ci_cont_id = $this->input->post("ci_cont_id");
        $ci_pallet_code = $this->input->post("ci_pallet_code");

        $DeliveryTime = $this->input->post("DeliveryTime");  //Add By Joke 16/6/2016
        $DestinationDetail = $this->input->post("DestinationDetail"); //Add By Joke 16/6/2016

        if ($conf_price_per_unit) {
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
//                , 'Owner_Id' => $owner_id // ***** Owner กับ Source คือคนเดียวกันโดยเอาจากช่อง shipper
                , 'Owner_Id' => $shipper_id
                , 'Renter_Id' => $renter_id
                , 'Estimate_Action_Date' => $est_dispatch_date
                , 'Source_Id' => $shipper_id
                , 'Destination_Id' => $consignee_id
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Modified_By' => $user_id
                , 'Modified_Date' => date("Y-m-d H:i:s")
                , 'Is_urgent' => $is_urgent
                , 'Destination_Detail' => $DestinationDetail // Add By Joke 20/6/2016
                , 'Estimate_Action_Time' => iconv("UTF-8", "TIS-620", $DeliveryTime) // Add By Joke 20/6/2016
                , 'Destination_Detail' => iconv("UTF-8", "TIS-620", $DestinationDetail)  // Add By Joke 20/6/2016
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

                    $detail['Product_Id'] = $a_data[$ci_prod_id];
                    $detail['Product_Code'] = $a_data[$ci_prod_code];
                    $detail['Reserv_Qty'] = $a_data[$ci_reserv_qty];
                    $detail['Unit_Id'] = $a_data[$ci_unit_id];
                    $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);

                    if ($conf_price_per_unit) :
                        /* COMMENT BY POR 2014-06-02 ยกเลิกการหาข้อมูลเกี่ยวกับราคา เนื่องจากให้บันทึกโดยนำค่ามาจาก form เลย */
                        //ADD BY POR 2014-06-02 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                        $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]); //+++++ADD BY POR 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                        $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                        $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]); //+++++ADD BY POR 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                    //END ADD
                    endif;


                    if ($conf_pallet) :
                        if ($a_data[$ci_pallet_code] != "" && !empty($a_data[$ci_pallet_code])):
                            $detail['Pallet_Id'] = $this->pl->get_palletId_by_code($a_data[$ci_pallet_code]);
                        else:
                            $detail['Pallet_Id'] = NULL;
                        endif;
                    endif;


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

                    if ("new" != $is_new) :
                        unset($where);
                        $where['Item_Id'] = $is_new;
                        $where['Order_Id'] = $order_id;
                        $where['Product_Code'] = $a_data[$ci_prod_code];
                        $where['Product_Id'] = $a_data[$ci_prod_id];

                        $oldReservQty = $this->stock->getOldReservQtyOrderDetailByItemId($is_new);

                        if ($oldReservQty['Reserv_Qty'] != $detail['Reserv_Qty']) ://(1)
                            $this->stock->reservPDReservQty($a_data[$ci_inbound_id], $oldReservQty['Reserv_Qty'], "-"); //(3)
                            $this->stock->reservPDReservQty($a_data[$ci_inbound_id], $detail['Reserv_Qty'], "+"); //(4)
                        endif;
                        //(2)

                        unset($oldReservQty);

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
                        $detail['Product_Lot'] = iconv("UTF-8", "TIS-620", $a_data[$ci_lot]);
                        $detail['Product_Serial'] = iconv("UTF-8", "TIS-620", $a_data[$ci_serial]);
                        $detail['Product_Status'] = $a_data[$ci_prod_status];
                        $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                        $detail['Inbound_Item_Id'] = $a_data[$ci_inbound_id];
                        $detail['Suggest_Location_Id'] = $a_data[$ci_suggest_loc];

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


                if (!empty($order_detail)):

                    /**
                     * Create new OrderDetail
                     */
                    $result_order_detail = $this->stock->addOrderDetail($order_detail);
                    if ($result_order_detail <= 0):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not Create new OrderDetail.";
                    endif;

                    /**
                     * Update PD_Reserve_Qty
                     */
                    $result_PD_reserv_qty = $this->stock->reservPDReservQtyArray($order_detail);
                    if (!$result_PD_reserv_qty):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not Update PD_Reserve_Qty.";
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
                    $item_delete[] = @$a_data[$ci_item_id];
                    if (@$a_data[$ci_inbound_id] != "" || @$a_data[$ci_inbound_id] != NULL) :
                        /**
                         * update PD_Reserv_Qty in STK_T_Inbound
                         */
                        $result_reservPDReservQty = $this->stock->reservPDReservQty($a_data[$ci_inbound_id], $a_data[$ci_reserv_qty], "-");
                        if (!$result_reservPDReservQty):
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not update PD_Reserv_Qty in STK_T_Inbound.";
                            break;
                        endif;
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

        //logOrderDetail($order_id, 'pre_dispatch', $action_id, $action_type);  //COMMENT BY POR 2014-07-07 NOT USER FUNCTION
//        return "C001";
        return $return;
    }

#================================================
# Customize
    /*
      public function getSelectPreDispatchData() {
      $param = $this->input->post('post_val');
      $table_name = $this->input->post('tableName');
      $query = $this->preDispModel->getPreDispatchByProdCode($param);
      $pre_dispatch_list_modal = $query->result();
      if (empty($pre_dispatch_list_modal)) {
      echo "<div style='text-align:center;'>No Result Found!</div>";
      } else {
      $this->load->library('table');
      $array_result = array();
      if (count($pre_dispatch_list_modal) > 0) {
      $header_name = array(
      "Select"
      , "Product Code", "Product NameEN"
      , "Product Status", "Location Code"
      , "Product lot", "Product Serial", "Product Mfd"
      , "Product Exp ", "Balance Qty"
      );
      foreach ($header_name as $field) {
      $array_header[] = $field;
      }
      foreach ($pre_dispatch_list_modal as $rows) {
      $rows->Inbound_id = "<input ID=chkBoxVal type=checkbox name=chkBoxVal[] value='$rows->Inbound_id' id=chkBoxVal" . $rows->Inbound_id . " >";
      $array_result[] = (array) $rows;
      }
      }
      $tmpl = array('table_open' => '<table cellpadding="1" cellspacing="0" border="1" class="well table display" id="defDataTableModal" >');
      $this->table->set_template($tmpl);
      $this->table->set_heading($array_header);
      $table_html = $this->table->generate($array_result);
      $table = $table_html;
      echo $this->conv->tis620_to_utf8($table_html);
      // $this->parser->parse('ajaxTable_Template', array("test_parse" => $table, "table_name" => $table_name, "search_value" => $param));
      }
      }
     */



    #ISSUE 2772 Pre-Dispatch : Search Product by Status & Sub Status
    #DATE:10-09-2013
    #BY:KIK
    #เน€เธ�เธดเน�เธกเธ�เธฒเธฃเธ�เน�เธ�เธซเธฒเธ”เน�เธงเธข product lot , product serial , product Mfd , product Exp
    #START New Comment Code #ISSUE 2772

    public function getSelectPreDispatchData() {

        $data = $this->input->get();
        // Add By Akkarapol, 28/11/2013, เน€เธ�เน�เธ•เธ�เน�เธฒเน€เธ�เธทเน�เธญเธชเน�เธ�เน�เธซเน�เธ�เธฑเธ�เธ�เน�เธ�เธฑเน�เธ� getDispatchProductDetails เธ—เธณเธ�เธฒเธฃ query เธ�เน�เธญเธกเธนเธฅเธ�เธฅเธฑเธ�เธกเธฒเธ�เธฑเธ”เธ�เธฒเธฃเธ•เน�เธญ
        $conf = $this->config->item('_xml');
        $conf_fefo = empty($conf['suggest_rule']['FEFO']) ? false : @$conf['suggest_rule']['FEFO'];
        
        $productCode = $data["productCode_val"];
        $productStatus = $data["productStatus_val"];
        $productSubStatus = $data["productSubStatus_val"];
        $productLot = $data["productLot_val"];
        $productSerial = $data["productSerial_val"];
        $productMfd = $data["productMfd_val"];
        $productExp = $data["productExp_val"];
        $limit_max = ($data['iDisplayLength'] < 0 ? '999999' : $data['iDisplayLength']); // Edit By Akkarapol, 29/11/2013, เน€เธ�เน�เธ�เธงเน�เธฒเธ–เน�เธฒ $data['iDisplayLength']<0 (เน�เธ�เธ—เธตเน�เธ�เธตเน� เธ–เน�เธฒเน€เธฅเธทเธญเธ� Show All เธกเธฑเธ�เธ�เธฐเน€เธ�เน�เธ� -1) เธ�เน�เน�เธซเน�เน€เธ�เน�เธ•เธ�เน�เธฒเน€เธ�เน�เธ� 999999 เน�เธ•เน�เธ–เน�เธฒเน�เธกเน�เน�เธ�เน� < 0 เธ�เน�เธฃเธฑเธ�เธ�เน�เธฒเธ•เธฒเธกเธ�เธ�เธ•เธด
        $limit_start = $data['iDisplayStart'];

        $arr_select_inbound_id = array();
        $dp_type = $data['dp_type'];
        
        $arr_select_inbound_id = $data['arr_select_inbound_id'];
        $arr_select_inbound_id = str_replace("-","",$arr_select_inbound_id);
        $arr_select_inbound_id = explode("|", $arr_select_inbound_id);

        $arr_select_reserv_qty = $data['arr_select_reserv_qty'];
        $arr_select_reserv_qty = str_replace("-","",$arr_select_reserv_qty);
        $arr_select_reserv_qty = explode("|", $arr_select_reserv_qty);


        $palletCode_val = "";
        $palletIsFull_val = "";
        $palletDispatchType_val = "NULL";
        $chkPallet_val = "";
        //ADD BY POR เน€เธ�เธดเน�เธกเน€เธ•เธดเธกเน�เธ�เธ�เธฒเธฃเธฃเธฑเธ�เธ�เน�เธฒเธ�เธฃเธ“เธต filter เธ”เน�เธงเธข pallet
        if ($this->pallet == TRUE && !empty($data["chkPallet_val"])):
            $palletCode_val = trim($data["palletCode_val"]); //pallet code
            $palletIsFull_val = $data["palletIsFull_val"]; //pallet type
            $palletDispatchType_val = $data["palletDispatchType_val"]; //pallet type
            $chkPallet_val = $data["chkPallet_val"]; //เธ•เธฑเธงเน€เธฅเธทเธญเธ�เธงเน�เธฒเน€เธฅเธทเธญเธ� filter เธ”เน�เธงเธข pallet เธซเธฃเธทเธญเน�เธกเน�
        endif;
        //END ADD

        $s_search = iconv("UTF-8", "TIS-620", $data['sSearch']);

        if($conf_fefo == "Product_Mfd_sort") {
        $query = $this->inbound->getDispatchProductDetails_FEFO($productCode, $productStatus, $productSubStatus, $productLot, $productSerial, $productMfd, $productExp, $limit_start, $limit_max, $s_search, $palletCode_val, $chkPallet_val, $palletIsFull_val, $palletDispatchType_val, $conf_fefo , $dp_type, NULL);
        } else {
        $query = $this->inbound->getDispatchProductDetails($productCode, $productStatus, $productSubStatus, $productLot, $productSerial, $productMfd, $productExp, $limit_start, $limit_max, $s_search, $palletCode_val, $chkPallet_val, $palletIsFull_val, $palletDispatchType_val, 'Product_Code ASC' , $dp_type, NULL);
    }
        $count_result = $query->num_rows();
        // p($count_result);exit;
        $result = $query->result();
        $response = array();


        foreach ($result as $k => $v) :

            $arr_inbound = array();

            if ($palletDispatchType_val == 'FULL'): //ADD BY POR 2014-02-18 เธ�เธฃเธ“เธตเน€เธฅเธทเธญเธ� dispatch full เธ�เธฐเน�เธชเธ”เธ�เน�เธ�เธ� pallet
                //เธซเธฒเธงเน�เธฒ pallet เธ—เธตเน�เน€เธฅเธทเธญเธ� เธกเธต inbound_id เธญเธฐเน�เธฃเธ�เน�เธฒเธ�เน€เธ�เธทเน�เธญเธชเน�เธ�เธ�เน�เธฒเธ�เธฅเธฑเธ�เน�เธ�
                $column["Inbound_Id"] = "Inbound_Id";
                $qry_inbound = $this->inbound->get_inbound_by_palletID($v->Pallet_Id, $column);
                $result_inbound = $qry_inbound->result_array();
                foreach ($result_inbound as $key => $inboundid):
                    //p($inboundid);exit();

                    if (empty($arr_select_inbound_id) || !in_array($inboundid["Inbound_Id"], $arr_select_inbound_id)):

                        array_push($arr_inbound, $inboundid["Inbound_Id"]);

                    endif; // end of check pallet dispatch type full

                endforeach;

                $arr_inbound = json_encode($arr_inbound);

                $response[] = array("<input CLASS=chkBoxValClass type=checkbox name=chkBoxVal[] value='" . $v->Pallet_Id . "' id='chkBoxVal" . $v->Pallet_Id . "' onClick=getCheckValue(this,'" . $palletDispatchType_val . "'," . $arr_inbound . ")>"
                    , $v->Pallet_Code
                    , $v->Pallet_Type
                    , thai_json_encode($v->Pallet_Name)
                );

            else:

                if (empty($arr_select_inbound_id) || !in_array(@$v->Inbound_Id, $arr_select_inbound_id)):
                    if ($v->Product_Exp != "" && $v->Product_Exp != NULL) {
                        $Product_Exp = explode('/', $v->Product_Exp);
                        $agingDate = (mktime(0, 0, 0, $Product_Exp[1], $Product_Exp[0], $Product_Exp[2]) - mktime(0, 0, 0)) / ( 60 * 60 * 24 );
                    } else {
                        $agingDate = 'N/A';
                    }

                    $arr_inbound = json_encode($arr_inbound); // Add By Akkarapol, 25/02/2014, Add $arr_inbound=json_encode($arr_inbound); in else of if($palletDispatchType_val=='FULL') because response need json_encode for use in VIEW
                    if($conf_fefo == "Product_Mfd_sort"){
                        $response[] = array("<input CLASS=chkBoxValClass type=checkbox name=chkBoxVal[] value='" . $v->Inbound_Id . "' id='chkBoxVal" . $v->Inbound_Id . "' onClick=getCheckValue(this,'" . $palletDispatchType_val . "'," . $arr_inbound . ")>"
                        , $v->Product_Code
                        , thai_json_encode($v->Product_NameEN)
                        , $v->Dom_TH_Desc
                        , $v->Product_Sub_Status
                        , $v->Location_Code
                        , thai_json_encode($v->Product_Lot)
                        , thai_json_encode($v->Product_Serial)
                        , $v->Receive_Date
                        , $v->Product_Mfd
                        , $v->Product_Exp
                        , $agingDate
                        , "<span class='est_of_inbound_" . $v->Inbound_Id . "' >" . set_number_format($v->Est_Balance_Qty) . "</span>"
                        , set_number_format($v->Balance_Qty)
                        , $v->Pallet_Code
                    );}
                        else{
                            $response[] = array("<input CLASS=chkBoxValClass type=checkbox name=chkBoxVal[] value='" . $v->Inbound_Id . "' id='chkBoxVal" . $v->Inbound_Id . "' onClick=getCheckValue(this,'" . $palletDispatchType_val . "'," . $arr_inbound . ")>"
                            , $v->Product_Code
                            , thai_json_encode($v->Product_NameEN)
                            , $v->Product_Status
                            , $v->Product_Sub_Status
                            , $v->Location_Code
                            , thai_json_encode($v->Product_Lot)
                            , thai_json_encode($v->Product_Serial)
                            , $v->Receive_Date
                            , $v->Product_Mfd
                            , $v->Product_Exp
                            , $agingDate
                            , "<span class='est_of_inbound_" . $v->Inbound_Id . "' >" . set_number_format($v->Est_Balance_Qty) . "</span>"
                            , set_number_format($v->Balance_Qty)
                            , $v->Pallet_Code
                        );
                    }
                        
                   
                endif; // end of check have inbound_id in item list pre-dispatch

            endif; // end of check pallet dispatch type full


        endforeach;

        if(!empty($conf_fefo)) {
            
            $total_filter_product   = $this->inbound->getDispatchProductDetails_FEFO($productCode, $productStatus, $productSubStatus, $productLot, $productSerial, $productMfd, $productExp, '0', '999999', $s_search, $palletCode_val, $chkPallet_val, $palletIsFull_val, $palletDispatchType_val)->num_rows();
                     $total_product = $this->inbound->getDispatchProductDetails('', '', '', '', '', '', '', '0', '999999', $s_search, $palletCode_val, $chkPallet_val, $palletIsFull_val, $palletDispatchType_val)->num_rows(); // Add By Akkarapol, 27/11/2013, เน€เธ�เธดเน�เธกเน�เธ�เน�เธ”เธ�เธฑเธ�เธ�เธณเธ�เธงเธ� product เธ—เธฑเน�เธ�เธซเธกเธ”
            // p($total_filter_product);
        }
        else{
        
            $total_filter_product = $this->inbound->getDispatchProductDetails($productCode, $productStatus, $productSubStatus, $productLot, $productSerial, $productMfd, $productExp, '0', '999999', $s_search, $palletCode_val, $chkPallet_val, $palletIsFull_val, $palletDispatchType_val)->num_rows(); // Add By Akkarapol, 27/11/2013, เน€เธ�เธดเน�เธกเน�เธ�เน�เธ”เธ�เธฑเธ�เธ�เธณเธ�เธงเธ�เธ—เธฑเน�เธ�เธซเธกเธ”เธ�เธญเธ� product เธ—เธตเน� query เธ�เธฒเธ� filter เธ—เธตเน�เธชเน�เธ�เธกเธฒ
            $total_product = $this->inbound->getDispatchProductDetails('', '', '', '', '', '', '', '0', '999999', $s_search, $palletCode_val, $chkPallet_val, $palletIsFull_val, $palletDispatchType_val)->num_rows(); // Add By Akkarapol, 27/11/2013, เน€เธ�เธดเน�เธกเน�เธ�เน�เธ”เธ�เธฑเธ�เธ�เธณเธ�เธงเธ� product เธ—เธฑเน�เธ�เธซเธกเธ”
            // p($total_filter_product);
        }
        // $size = sizeof($response);
        $output = array(
            "sEcho" => (int) $data['sEcho'],
            "iTotalRecords" => $total_product,
            "iTotalDisplayRecords" =>  $total_filter_product,
            "aaData" => $response
        );
            // p($total_filter_product);
        echo json_encode($output);
        exit();
        // END Add By Akkarapol, 28/11/2013, เน€เธ�เธดเน�เธกเน�เธ�เน�เธ”เน�เธ�เธชเน�เธงเธ�เธ�เธญเธ�เธ�เธฒเธฃเน€เธ�เน�เธ—เธ�เน�เธฒเน€เธ�เธทเน�เธญ return เธ�เธฅเธฑเธ�เน�เธ�เน�เธซเน� view เน€เธ�เธทเน�เธญเธ�เธณเน�เธ� gen เธ�เน�เธญเธกเธนเธฅเน�เธ�เธ•เธฒเธฃเธฒเธ� เธ•เน�เธญเน�เธ�
//            p($result);
        #########################################
//        $result = $query->result();
        $data = array();
        $data_list = array();
        $action = array();
        $action_module = "";
        $column = array(
            _lang('select')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('product_status')
            , _lang('product_sub_status')
            , _lang('location_code')
            , _lang('product_lot')
            , _lang('serial')
            , _lang('receive_date')
            , _lang('product_mfd')
            , _lang('product_exp')
            , _lang('aging')
            , _lang('est_balance_qty')
            , _lang('balance')
        );
        if (is_array($result) && count($result)) {
            $count = 1;
            foreach ($result as $rows) {

                // add by kik : 2013-11-20
                if ($rows->Product_Exp != "" && $rows->Product_Exp != NULL) {
                    $Product_Exp = explode('/', $rows->Product_Exp);
                    $agingDate = (mktime(0, 0, 0, $Product_Exp[1], $Product_Exp[0], $Product_Exp[2]) - mktime(0, 0, 0)) / ( 60 * 60 * 24 );
                } else {
                    $agingDate = 'N/A';
                }
                // end add by kik : 2013-11-20

                $data['Id'] = "<input ID=chkBoxVal type=checkbox name=chkBoxVal[] value='" . $rows->Inbound_Id . "' id=chkBoxVal" . $rows->Inbound_Id . " onClick='getCheckValue(this)'>";
                $data['Product_Code'] = $rows->Product_Code;
                $data['Product_NameEN'] = $rows->Product_NameEN;
                if($conf_fefo == "Product_Mfd_sort"){
                $data['Product_Status'] = $rows->Dom_TH_Desc; }
                else{
               $data['Product_Status'] = $rows->Product_Status; }
             
                $data['Product_Sub_Status'] = $rows->Product_Sub_Status;
                $data['Location_Code'] = $rows->Location_Code;
                $data['Product_Lot'] = $rows->Product_Lot;
                $data['Product_Serial'] = $rows->Product_Serial;
                $data['Receive_Date'] = $rows->Receive_Date;
                $data['Product_Mfd'] = $rows->Product_Mfd;
                $data['Product_Exp'] = $rows->Product_Exp;
                $data['Aging'] = $agingDate;
                $data['Est_Product_Balance'] = $rows->Est_Balance_Qty; //Edit by kik : 06-09-2013  :#ISSUE 2790
                $data['Product_Balance'] = $rows->Balance_Qty;
                $count++;
                $data_list[] = (object) $data;
            }
        }
        // p($datatable);exit;
        $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);
        
        #########################################
        $this->load->model("encoding_conversion", "conv");
        echo $this->conv->tis620_to_utf8($datatable);
    }

    #End New Comment Code #ISSUE 2772 : 10-09-2013
//    comment by kik : 10-09-2013
//    #ISSUE 2772 Pre-Dispatch : Search Product by Status & Sub Status
//    #DATE:2013-09-03
//    #BY:KIK
//    #เน€เธ�เธดเน�เธกเธ�เธฒเธฃเธ�เน�เธ�เธซเธฒเธ”เน�เธงเธข Status & Sub Status เน�เธ�เธชเน�เธงเธ�เธ�เธญเธ� get product detail
//
//    #START New Comment Code #ISSUE 2772
//    public function getSelectPreDispatchData() {// add by
//        $productCode = $this->input->post("productCode_val");
//        $productStatus = $this->input->post("productStatus_val");
//        $productSubStatus = $this->input->post("productSubStatus_val");
//
//        $query = $this->preDispModel->getPreDispatchByProdCode($productCode,$productStatus,$productSubStatus);
//        #########################################
//        $result = $query->result();
//        $data = array();
//        $data_list = array();
//        $action = array();
//        $action_module = "";
//        $column = array("Select", "Product Code", "Product NameEN"
//            , "Product Status", "Product Sub Status", "Location Code", "Product lot"
//            , "Product Serial", "Product Mfd", "Product Exp "
//            , "Est. Balance Qty", "Balance Qty");
//        if (is_array($result) && count($result)) {
//            $count = 1;
//            foreach ($result as $rows) {
//                /* $data['Id'] = "<input ID=chkBoxVal type=checkbox name=chkBoxVal[] value='" . $rows->Inbound_Id . "' id=chkBoxVal" . $rows->Inbound_Id . " onClick='getCheckValue(this)'>"; */
//                $data['Id'] = "<input ID=chkBoxVal type=checkbox name=chkBoxVal[] value='" . $rows->Inbound_Id . "' id=chkBoxVal" . $rows->Inbound_Id . " onClick='getCheckValue(this)'>";
//                $data['Product_Code'] = $rows->Product_Code;
//                $data['Product_NameEN'] = $rows->Product_NameEN;
//                $data['Product_Status'] = $rows->Product_Status;
//                $data['Product_Sub_Status'] = $rows->Product_Sub_Status;
//                $data['Location_Code'] = $rows->Location_Code;
//                $data['Product_Lot'] = $rows->Product_Lot;
//                $data['Product_Serial'] = $rows->Product_Serial;
//                $data['Product_Mfd'] = $rows->Product_Mfd;
//                $data['Product_Exp'] = $rows->Product_Exp;
//                $data['Est_Product_Balance'] = getCalculateAllowcate($rows->Receive_Qty,$rows->Dispatch_Qty,$rows->Adjust_Qty,$rows->PD_Reserv_Qty);//Edit by kik : 06-09-2013  :#ISSUE 2790
//                $data['Product_Balance'] = $rows->Balance_Qty;
//                $count++;
//                $data_list[] = (object) $data;
//            }
//        }
//        echo $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);
//    }
    #End New Comment Code #ISSUE 2772
    #Start Old Comment Code #ISSUE 2772
#================================================
# Edit By Sinthara Date 2013-0520
//    public function getSelectPreDispatchData() {
//        $product_code = $this->input->post("post_val");;
//
//        $query = $this->preDispModel->getPreDispatchByProdCode($product_code);
//        #########################################
//        $result = $query->result();
//        $data = array();
//        $data_list = array();
//        $action = array();
//        $action_module = "";
//        $column = array("Select", "Product Code", "Product NameEN"
//            , "Product Status", "Location Code", "Product lot"
//            , "Product Serial", "Product Mfd", "Product Exp "
//            , "Est. Balance Qty", "Balance Qty");
//        if (is_array($result) && count($result)) {
//            $count = 1;
//            foreach ($result as $rows) {
//                /* $data['Id'] = "<input ID=chkBoxVal type=checkbox name=chkBoxVal[] value='" . $rows->Inbound_Id . "' id=chkBoxVal" . $rows->Inbound_Id . " onClick='getCheckValue(this)'>"; */
//                $data['Id'] = "<input ID=chkBoxVal type=checkbox name=chkBoxVal[] value='" . $rows->Inbound_Id . "' id=chkBoxVal" . $rows->Inbound_Id . " onClick='getCheckValue(this)'>";
//                $data['Product_Code'] = $rows->Product_Code;
//                $data['Product_NameEN'] = $rows->Product_NameEN;
//                $data['Product_Status'] = $rows->Product_Status;
//                $data['Location_Code'] = $rows->Location_Code;
//                $data['Product_Lot'] = $rows->Product_Lot;
//                $data['Product_Serial'] = $rows->Product_Serial;
//                $data['Product_Mfd'] = $rows->Product_Mfd;
//                $data['Product_Exp'] = $rows->Product_Exp;
//                $data['Est_Product_Balance'] = $rows->Est_Balance_Qty;
//                $data['Product_Balance'] = $rows->Balance_Qty;
//                $count++;
//                $data_list[] = (object) $data;
//            }
//        }
//        echo $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);
//        #########################################
//        #$this->load->model("encoding_conversion", "conv");
//        #echo $this->conv->tis620_to_utf8($datatable);
//    }
    #End Old Comment Code #ISSUE 2772

    public function showAndGenSelectData() {

        $conf = $this->config->item('_xml');
        $conf_fefo = empty($conf['suggest_rule']['FEFO']) ? false : @$conf['suggest_rule']['FEFO'];
        $productCode = $this->input->post("productCode_val");
        $productStatus = $this->input->post("productStatus_val");
        $productSubStatus = $this->input->post("productSubStatus_val");
        $productLot = $this->input->post("productLot_val");
        $productSerial = $this->input->post("productSerial_val");
        $productMfd = $this->input->post("productMfd_val");
        $productExp = $this->input->post("productExp_val");
        $arr_select_inbound_id = $this->input->post("arr_select_inbound_id");
        $qty_of_sku = $this->input->post("qty_of_sku");
        $stage = $this->input->post("stage");

//        START set $arr_select_inbound_id = null; for >> สามารถเลือกรายการเดิมที่ยังเหลือ Est.Balance อยู่ได้เพิ่มเติม ในเอกสารเดียวกัน
        //$arr_select_inbound_id = $this->input->post("arr_select_inbound_id");
        $arr_select_inbound_id = null;
//        END set $arr_select_inbound_id = null; for >> สามารถเลือกรายการเดิมที่ยังเหลือ Est.Balance อยู่ได้เพิ่มเติม ในเอกสารเดียวกัน

        $tmp_select_inbound_id = $this->input->post("arr_select_inbound_id");
        $arr_select_reserv_qty = $this->input->post("arr_select_reserv_qty");
//        p($tmp_select_inbound_id);
//        p($arr_select_reserv_qty);
        $tmp_inbound_and_reserve = array();
        if (!empty($tmp_select_inbound_id)):
            foreach ($tmp_select_inbound_id as $key => $val):
                $tmp_inbound_and_reserve[$val] = (empty($tmp_inbound_and_reserve[$val]) ? $arr_select_reserv_qty[$key] : $tmp_inbound_and_reserve[$val] + $arr_select_reserv_qty[$key]);
            endforeach;
        endif;
//        p($tmp_inbound_and_reserve);
        $palletCode_val = "";
        $palletIsFull_val = "";
        $palletDispatchType_val = "NULL";
        $chkPallet_val = "";
        //ADD BY POR เน€เธ�เธดเน�เธกเน€เธ•เธดเธกเน�เธ�เธ�เธฒเธฃเธฃเธฑเธ�เธ�เน�เธฒเธ�เธฃเธ“เธต filter เธ”เน�เธงเธข pallet
        if ($this->pallet == TRUE && $this->input->post("chkPallet_val") == 1):
            $palletCode_val = trim($this->input->post("palletCode_val")); //pallet code
            $palletIsFull_val = $this->input->post("palletIsFull_val"); //pallet type
            $palletDispatchType_val = $this->input->post("palletDispatchType_val"); //pallet type
            $chkPallet_val = $this->input->post("chkPallet_val"); //เธ•เธฑเธงเน€เธฅเธทเธญเธ�เธงเน�เธฒเน€เธฅเธทเธญเธ� filter เธ”เน�เธงเธข pallet เธซเธฃเธทเธญเน�เธกเน�
        endif;


        $criteria = new stdClass();
        $criteria->product_code = $productCode;
        $criteria->productStatus = $productStatus;
        $criteria->productSubStatus = $productSubStatus;
        $criteria->productLot = $productLot;
        $criteria->productSerial = $productSerial;
        $criteria->productMfd = $productMfd;
        $criteria->productExp = $productExp;
        $criteria->limit_start = $limit_start;
        $criteria->limit_max = $limit_max;
        $criteria->s_search = $s_search;
        $criteria->palletCode_val = $palletCode_val;
        $criteria->chkPallet_val = $chkPallet_val;
        $criteria->palletIsFull_val = $palletIsFull_val;
        $criteria->palletDispatchType_val = $palletDispatchType_val;
        $criteria->order_by = 'Product_Code ASC';
        $criteria->dp_type = $dp_type;
        $criteria->invoiceNo = $invoiceNo;


        //END ADD
          if($conf_fefo == "Product_Mfd_sort"){
            $result = $this->inbound->getDispatchProductDetails_FEFO($productCode, $productStatus, $productSubStatus, $productLot, $productSerial, $productMfd, $productExp, NULL, NULL, "", $palletCode_val, $chkPallet_val, $palletIsFull_val, $palletDispatchType_val, 'Product_Mfd', NULL,NULL,$stage,$tmp_select_inbound_id,$arr_select_reserv_qty);
          } else{
            $result = $this->inbound->getDispatchProductDetails($criteria);
          }

        $result = $result->result(); // Add BY Akkarapol, 28/11/2013, เน€เธ�เธทเน�เธญเน�เธซเน� function getDispatchProductDetails เน€เธ�เน�เธ�เธชเธฒเธ�เธฅเธกเธฒเธ�เธ�เธถเน�เธ� เธ�เธถเธ�เธ—เธณเน�เธซเน� return เธ—เธตเน�เธ�เธฅเธฑเธ�เธกเธฒเน€เธ�เน�เธ�เน�เธ�เน� db->get() เน€เธ�เธทเน�เธญเน�เธซเน�เธ�เธณเน�เธ�เน�เธ�เน�เธ•เน�เธญเธขเธญเธ”เน�เธ”เน�เธซเธฅเธฒเธ�เธซเธฅเธฒเธขเธกเธฒเธ�เธ�เธถเน�เธ� เธ—เธณเน�เธซเน�เธ•เน�เธญเธ�เน€เธ�เธตเธขเธ� $result = $result->result(); เธกเธฒเธฃเธฑเธ�เธ�เน�เธฒเธญเธตเธ�เธฃเธญเธ�เธซเธ�เธถเน�เธ�
            // p($result); exit;
	$putaway_rule = $result[0]->PutAway_Rule;
        $new_list = array();
        $post_val = array();
        $check_out_of_qty = TRUE;
        $need_qty_of_sku = $qty_of_sku;
        if (!empty($result)) :
            if ($palletDispatchType_val == 'FULL'):

                //เธซเธฒเธงเน�เธฒ pallet เธ—เธตเน�เน€เธฅเธทเธญเธ� เธกเธต inbound_id เธญเธฐเน�เธฃเธ�เน�เธฒเธ�เน€เธ�เธทเน�เธญเธชเน�เธ�เธ�เน�เธฒเธ�เธฅเธฑเธ�เน�เธ�
                $column["Inbound_Id"] = "Inbound_Id";
                $qry_inbound = $this->inbound->get_inbound_by_palletID($result[0]->Pallet_Id, $column);
                $result_inbound = $qry_inbound->result_array();

                $count = count($result_inbound);
                $i = 1;
                foreach ($result_inbound as $key => $inboundid):
                    if (empty($arr_select_inbound_id) || !in_array($rs->Inbound_Id, $arr_select_inbound_id)):
//                        if ($count == $i):
//                            $post_val.=$inboundid["Inbound_Id"];
//                        else:
//                            $post_val.=$inboundid["Inbound_Id"] . ",";
//                        endif;
                        $post_val[] = $inboundid["Inbound_Id"];
                        $i++;
                    endif;
                endforeach;

            else:
                $count = count($result);
                $i = 1;
                foreach ($result as $key_rs => $rs):
//                    if (!empty($tmp_select_inbound_id) and (in_array($rs->Inbound_Id, $tmp_select_inbound_id))) {
//                        if($rs->Est_Balance_Qty <= $tmp_inbound_and_reserve[$rs->Inbound_Id]):
//                            p($rs->Est_Balance_Qty);
//                            p($tmp_inbound_and_reserve[$rs->Inbound_Id]);
////                            p('lol');
//                            $arr_select_inbound_id[] = $rs->Inbound_Id;
//                        endif;
//                    }
//                    if (empty($arr_select_inbound_id) || !in_array($rs->Inbound_Id, $arr_select_inbound_id)):
////                        $post_val = $rs->Inbound_Id;
////                        break;
////                        if ($count == $i):
////                            $post_val.=$rs->Inbound_Id;
////                        else:
////                            $post_val.=$rs->Inbound_Id . ",";
////                        endif;
//                        $post_val[] = $rs->Inbound_Id;
//                        $i++;
//                    endif;

                    $post_val[] = $rs->Inbound_Id;

                endforeach;
            endif;
//            p($qty_of_sku);
//p($post_val);
            $post_val = implode(',', $post_val);
            if (!empty($post_val)):

                $query = $this->preDispModel->getListPreDispatchByProdCodeViaAjax($post_val, $putaway_rule);
                $pre_dispatch_list = $query->result();
// p($pre_dispatch_list); exit;
                foreach ($pre_dispatch_list as $rows) :
                    $check_est_bal = $rows->Est_Balance_Qty = floatval(($rows->Receive_Qty) - ($rows->PD_Reserv_Qty) - ($rows->Dispatch_Qty) - ($rows->Adjust_Qty));
                    $rows->Est_Balance_Qty = "<span class='est_of_inbound_" . $rows->Inbound_Id . "' >" . floatval($rows->Est_Balance_Qty) . "</span>";
                    $rows->DP_Type_Pallet = $palletDispatchType_val; //ADD BY POR 2014-02-19 เน€เธ�เธดเน�เธกเน�เธซเน�เธชเน�เธ�เธ�เน�เธฒ dispatch type เธ�เธฅเธฑเธ�เน�เธ�เธ”เน�เธงเธข

                    if ($check_est_bal < $qty_of_sku):
                        $rows->Reserve_Qty = $check_est_bal;
                    else:
                        $rows->Reserve_Qty = $qty_of_sku;
                    endif;
                    $qty_of_sku -= $rows->Reserve_Qty;


                    $new_list[] = thai_json_encode((array) $rows);


                    if ($qty_of_sku == 0):
                        $check_out_of_qty = FALSE;
                        break;
                    endif;

                endforeach;
            endif;
        endif;
//p($new_list);
        $json['product'] = $new_list;
        $json['alert'] = 'OK';
        if ($check_out_of_qty):
            $system_qty = $need_qty_of_sku - $qty_of_sku;
            $json['alert'] = "System QTY({$system_qty}) Less Your Input QTY({$need_qty_of_sku}) Please Check This.";
        endif;

        echo json_encode($json);
    }

    
     public function batchShowAndGenSelectData_bk() {


        $data = $this->input->post();

        $return = array();
        $returns = array();

        $remain_balance_by_inbound = array();
        
        /////////////////////
//         p($data['dataSet']); exit();
        $all_product_list = array();
        if(!empty($data['dataSet'])):
            foreach($data['dataSet'] as $key_dataSet => $dataSet):

                $problem_row = array();
                $sum_all_reserve_qty = 0;

                $productCode = $dataSet["productCode_val"];
                $productStatus = $dataSet["productStatus_val"];
                $productSubStatus = $dataSet["productSubStatus_val"];
                $productLot = $dataSet["productLot_val"];
                $productSerial = $dataSet["productSerial_val"];
                $productMfd = $dataSet["productMfd_val"];
                $productExp = $dataSet["productExp_val"];
                $invoiceNo = (empty($dataSet["invoiceNo_val"])?NULL:$dataSet["invoiceNo_val"]);
                $qty_of_sku = str_replace(',', '', $dataSet["qty_of_sku"]);
                $arr_select_inbound_id = null;
                $arr_error_rule = null;
                $tmp_select_inbound_id = $dataSet["arr_select_inbound_id"];
                $arr_select_reserv_qty = str_replace(',', '', $dataSet["arr_select_reserv_qty"]);
                $tmp_inbound_and_reserve = array();
                if(!empty($tmp_select_inbound_id)):
                    foreach($tmp_select_inbound_id as $key => $val):
                        $tmp_inbound_and_reserve[$val] = (empty($tmp_inbound_and_reserve[$val])?$arr_select_reserv_qty[$key]:$tmp_inbound_and_reserve[$val]+$arr_select_reserv_qty[$key]);
                    endforeach;
                endif;

                $palletCode_val = "";
                $palletIsFull_val = "";
                $palletDispatchType_val = "NULL";
                $chkPallet_val = "";
                if ($this->pallet == TRUE && @$dataSet["chkPallet_val"] == 1):
                    $palletCode_val = trim($dataSet["palletCode_val"]);
                    $palletIsFull_val = $dataSet["palletIsFull_val"];
                    $palletDispatchType_val = $dataSet["palletDispatchType_val"];
                    $chkPallet_val = $dataSet["chkPallet_val"];
                endif;

                $criteria = new stdClass();
                $criteria->product_code = $productCode;
                $criteria->productStatus = $this->inbound->getStatusByCode(strtoupper($productStatus));
                $criteria->productSubStatus = $this->inbound->getSubStatusByCode(strtoupper($productSubStatus));
                $criteria->productLot = $productLot;
                $criteria->productSerial = $productSerial;
                $criteria->productMfd = $productMfd;
                $criteria->productExp = $productExp;
                $criteria->limit_start = 0;
                $criteria->limit_max = 999;
                $criteria->s_search = NULL;
                $criteria->palletCode_val = $palletCode_val;
                $criteria->chkPallet_val = $chkPallet_val;
                $criteria->palletIsFull_val = $palletIsFull_val;
                $criteria->palletDispatchType_val = $palletDispatchType_val;
                $criteria->order_by = 'Product_Code ASC';
                $criteria->dp_type = NULL;
                $criteria->invoiceNo = $invoiceNo;
                $criteria->exact_match = TRUE;
//                p($criteria);exit();
                $result = $this->inbound->getDispatchProductDetails($criteria);
                $result = $result->result();
                 p($this->db->last_query());
                p($result);
                if(empty($result)):

                    $json['alert'] = "System QTY  Less Your Input QTY Please Check This.";
                    $json['est_qty_after_cut'] = 0;
                    $json['qty_after_cut'] = 0;
                    $json['product'] = array();
                    $json['item_id'] = $dataSet['item_id'];
                    $json['row_count'] = $dataSet['row_count'];


                    $problem_row['item_id'] = $dataSet['item_id'];
                    $problem_row['row_count'] = $dataSet['row_count'];
                    $problem_row['less_qty'] = floatval($qty_of_sku);
                    $returns['arr_problem_row'][] = $problem_row;

                else:
                    $putaway_rule = $result['0']->PutAway_Rule;

                    $new_list = array();
                    $post_val = array();
                    $check_out_of_qty = TRUE;
                    $need_qty_of_sku = $qty_of_sku;

                    if (!empty($result)) :
                        if ($palletDispatchType_val == 'FULL'):

                            $column["Inbound_Id"] = "Inbound_Id";
                            $qry_inbound = $this->inbound->get_inbound_by_palletID($result[0]->Pallet_Id, $column);
                            $result_inbound = $qry_inbound->result_array();

                            $count = count($result_inbound);
                            $i = 1;
                            foreach ($result_inbound as $key => $inboundid):
                                if (empty($arr_select_inbound_id) || !in_array($rs->Inbound_Id, $arr_select_inbound_id)):
                                    $post_val[] = $inboundid["Inbound_Id"];
                                    $i++;
                                endif;
                            endforeach;

                        else:
                            $count = count($result);
                            $i = 1;
                            foreach ($result as $key_rs => $rs):
                                if (!empty($tmp_select_inbound_id) and (in_array($rs->Inbound_Id, $tmp_select_inbound_id))) {
                                    if($rs->Est_Balance_Qty <= $tmp_inbound_and_reserve[$rs->Inbound_Id]):
                                        $arr_select_inbound_id[] = $rs->Inbound_Id;
                                    else :
                                        $arr_error_rule[$rs->Inbound_Id]['product_code'] = $rs->Product_Code;
                                    endif;
                                }
                                if (empty($arr_select_inbound_id) || !in_array($rs->Inbound_Id, $arr_select_inbound_id)):
                                    $post_val[] = $rs->Inbound_Id;
                                    $i++;
                                endif;
                            endforeach;
                        endif;

                        $post_val = implode(',',$post_val);
                        if(!empty($post_val)):

                            $query = $this->preDispModel->getListPreDispatchByProdCodeViaAjax($post_val , $putaway_rule, FALSE);
                            $pre_dispatch_list = $query->result();

                            $first = TRUE;

                            // Re-Calculate
                            if (count($remain_balance_by_inbound) > 0) {
                                foreach ($pre_dispatch_list as $rows) {
                                    if (array_key_exists($rows->Inbound_Id, $remain_balance_by_inbound)) {
                                        if ($remain_balance_by_inbound[$rows->Inbound_Id] == 0) {
                                            $rows->PD_Reserv_Qty = $rows->Receive_Qty;
                                        } else {
                                            $rows->PD_Reserv_Qty =  ($rows->Receive_Qty - $remain_balance_by_inbound[$rows->Inbound_Id]);
                                        }
                                    }
                                }
                            }
                            // END ======

                            foreach ($pre_dispatch_list as $rows) :

                                $rows->ByRule = TRUE;
                                $check_est_bal = $rows->Est_Balance_Qty = floatval (($rows->Receive_Qty) - ($rows->PD_Reserv_Qty) - ($rows->Dispatch_Qty) - ($rows->Adjust_Qty) - @$tmp_inbound_and_reserve[$rows->Inbound_Id]);

                                // ADD temp qty depend on inbound id
                                if (!array_key_exists($rows->Inbound_Id, $remain_balance_by_inbound)) {
                                    $remain_balance_by_inbound[$rows->Inbound_Id] = $check_est_bal;
                                }
                                // END temp qty depend on inbound id

                                if ($rows->Est_Balance_Qty > 0) {
                                    $rows->Est_Balance_Qty = "<span class='est_of_inbound_" . $rows->Inbound_Id . "' >" . floatval($rows->Est_Balance_Qty) . "</span>";
                                    $rows->DP_Type_Pallet = $palletDispatchType_val;

                                    if ($check_est_bal < $qty_of_sku):
                                        $rows->Reserve_Qty = $check_est_bal;
                                    else:
                                        $rows->Reserve_Qty = $qty_of_sku;
                                    endif;

                                    if ($rows->PD_Reserv_Qty > 0 && !$first) {
                                        $arr_error_rule['product_code'][] = $rows->Product_Code;
                                        $arr_error_rule['location'][] = $rows->Actual_Location;
                                        $rows->ByRule = FALSE;
                                    }

                                    $sum_all_reserve_qty += $rows->Reserve_Qty;
                                    $qty_of_sku -= $rows->Reserve_Qty;

                                    $remain_balance_by_inbound[$rows->Inbound_Id] -= $rows->Reserve_Qty; // ADD to Temp for next loop
                                    $new_list[] = thai_json_encode((array) $rows);

                                    if ($qty_of_sku == 0):
                                        $check_out_of_qty = FALSE;
                                        break;
                                    endif;
                                } else {
                                    $first = FALSE; // If use another item set to FALSE for collect warning.
                                }

                            endforeach;
                        endif;
                    endif;
                    $json['product'] = $new_list;
                    $json['alert'] = 'OK';
                    if (!is_null($arr_error_rule)) {
                        $returns['arr_problem_rule'][] = json_encode($arr_error_rule);
                    }
                    if($check_out_of_qty):
                        $system_qty = $need_qty_of_sku - $qty_of_sku;
                        $json['alert'] = "System QTY({$system_qty}) Less Your Input QTY({$need_qty_of_sku}) Please Check This.";
                        $qty_after_cut = $need_qty_of_sku - $system_qty;
                        $json['est_qty_after_cut'] = 0;
                        $json['qty_after_cut'] = $qty_after_cut;
                        $json['item_id'] = $dataSet['item_id'];
                        $json['row_count'] = $dataSet['row_count'];

                        $problem_row['item_id'] = $dataSet['item_id'];
                        $problem_row['row_count'] = $dataSet['row_count'];
                        $problem_row['less_qty'] = $need_qty_of_sku - $sum_all_reserve_qty;
                        $returns['arr_problem_row'][] = $problem_row;
                    else:
                        $returns['can_del_row'][] = $dataSet['row_count'];
                    endif;
                    $all_product_list = array_merge_recursive($all_product_list, $new_list);
                endif;

                    $return[] = $json;
            endforeach;
        endif;
        $returns['all_product_list'] = $all_product_list;
        p($returns); exit();
        echo json_encode($returns);

    }
    
    
    /**
     * 
     * 
     * 
     * 
     */
    public function batchShowAndGenSelectData() {


        $data = $this->input->post();

        $return = array();
        $returns = array();

        $remain_balance_by_inbound = array();
        
        /////////////////////
     if(!empty($data['dataSet'] )){
//        p($data); exit();
        ///// group data
        $data2['dataSet'] = array();
        $text_query = "select temp_a.productCode_val"
                    . ",(select top 1 mp.Unit_Per_Pallet  from STK_M_Product mp where   temp_a.productCode_val = mp.Product_Code) as fpl"
                    . ",trim(temp_a.productStatus_val) as productStatus_val"
                    . ",trim(temp_a.productSubStatus_val) as productSubStatus_val"
                    . ",trim(temp_a.productLot_val) as productLot_val"
                    . ",trim(temp_a.productSerial_val) as productSerial_val"
                    . ",trim(temp_a.productMfd_val) as productMfd_val"
                    . ",trim(temp_a.productExp_val) as productExp_val"
                    . ",sum(temp_a.qty_of_sku) as qty_of_sku "
                    . ",count(1) as row_loop"
                    . " from (";
        foreach($data['dataSet'] as $key_dataSet1 => $dataSet1 ){
            if($key_dataSet1 == 0){
                $text_query .= " select '".$dataSet1['productCode_val']."' as productCode_val " ;
                $text_query .= " , '".$dataSet1['productStatus_val']."' as productStatus_val " ;
                $text_query .= " , '".$dataSet1['productSubStatus_val']."' as productSubStatus_val " ;
                $text_query .= " , '".$dataSet1['productLot_val']."' as productLot_val " ;
                $text_query .= " , '".$dataSet1['productSerial_val']."' as productSerial_val " ;
                $text_query .= " , '".$dataSet1['productMfd_val']."' as productMfd_val " ;
                $text_query .= " , '".$dataSet1['productExp_val']."' as productExp_val " ;
                $text_query .= " , ".$dataSet1['qty_of_sku']." as qty_of_sku " ;
                
                
                $temp_arr_select_inbound_id = $dataSet1['arr_select_inbound_id'];
                $temp_arr_select_reserv_qty = $dataSet1['arr_select_reserv_qty'];
            }
            else{
                $text_query .= " union all" ;
                $text_query .= " select '".$dataSet1['productCode_val']."' as productCode_val " ;
                $text_query .= " , '".$dataSet1['productStatus_val']."' as productStatus_val " ;
                $text_query .= " , '".$dataSet1['productSubStatus_val']."' as productSubStatus_val " ;
                $text_query .= " , '".$dataSet1['productLot_val']."' as productLot_val " ;
                $text_query .= " , '".$dataSet1['productSerial_val']."' as productSerial_val " ;
                $text_query .= " , '".$dataSet1['productMfd_val']."' as productMfd_val " ;
                $text_query .= " , '".$dataSet1['productExp_val']."' as productExp_val " ;
                $text_query .= " , ".$dataSet1['qty_of_sku']." as qty_of_sku " ;
            }
        }
        $text_query .= ") as temp_a";
        $text_query .= " group by"
                    . " temp_a.productCode_val"
                    . ",temp_a.productStatus_val "
                    . ",temp_a.productSubStatus_val "
                    . ",temp_a.productLot_val "
                    . ",temp_a.productSerial_val "
                    . ",temp_a.productMfd_val "
                    . ",temp_a.productExp_val ";
//                    p($text_query); exit();
            $res = $this->db->query($text_query);
            $data2['dataSet'] = $res->result_array();
                
//          foreach($data2['dataSet'] as $n_key0 => $non_act0){
//////              $non_act0
////              $data1['dataSet'][$n_key0]['use_full'] = 1 ;
//          }  
        $some_temp = array();
        $new_runing_key = 0 ;
        foreach($data2['dataSet'] as $n_key => $non_act){
            
             if($non_act['qty_of_sku'] > $non_act['fpl']){
                $temp_for_new_row = $non_act['qty_of_sku'] - $non_act['fpl'];
                $data2['dataSet'][$new_runing_key]['qty_of_sku'] = $non_act['fpl'];
                array_push($some_temp, $data2['dataSet'][$new_runing_key]['qty_of_sku'] );
                $data2['dataSet'][$new_runing_key]['arr_select_inbound_id'] = $temp_arr_select_inbound_id ;
                $data2['dataSet'][$new_runing_key]['arr_select_reserv_qty'] = $temp_arr_select_reserv_qty ;
                $data2['dataSet'][$new_runing_key]['row_count'] = $new_runing_key ;
                $data2['dataSet'][$new_runing_key]['use_full'] = 'TRUE';
                $data2['dataSet'][$new_runing_key]['item_id'] = $data['dataSet'][$new_runing_key]['item_id'];
                
//                $for_run_loop = ceil($temp_for_new_row/$non_act['fpl']);
                $for_run_loop = $non_act['row_loop'];
                //loop for add row
                // 0  and <
                // 1  and <=
                for($tlp = 1 ; $tlp < $for_run_loop ;$tlp++){
                    $new_runing_key += 1;
                    $data2['dataSet'][$new_runing_key] = $non_act;

                    
                    $data2['dataSet'][$new_runing_key]['qty_of_sku'] = null;           
                    $data2['dataSet'][$new_runing_key]['arr_select_inbound_id'] = $temp_arr_select_inbound_id ;
                    $data2['dataSet'][$new_runing_key]['arr_select_reserv_qty'] = $temp_arr_select_reserv_qty ;
                    $data2['dataSet'][$new_runing_key]['row_count'] = $new_runing_key ;
                    if($temp_for_new_row >= $non_act['fpl']){
                        $data2['dataSet'][$new_runing_key]['qty_of_sku'] = $non_act['fpl'];
                        array_push($some_temp, $data2['dataSet'][$new_runing_key]['qty_of_sku'] );
                        $data2['dataSet'][$new_runing_key]['use_full'] = 'TRUE';
                        $temp_for_new_row = $temp_for_new_row - $non_act['fpl'];
                    }
                    else{
                        if($temp_for_new_row <= 0 ){
                            $temp_for_new_row = 0;
                        }
                        $data2['dataSet'][$new_runing_key]['qty_of_sku'] = $temp_for_new_row;
                        array_push($some_temp, $data2['dataSet'][$new_runing_key]['qty_of_sku'] );
                        $data2['dataSet'][$new_runing_key]['use_full'] = 'FALSE';
                        $temp_for_new_row = $temp_for_new_row - $non_act['fpl'];
                    }
                    $data2['dataSet'][$new_runing_key]['item_id'] = $data['dataSet'][$new_runing_key]['item_id'];
                }
                
                 $temp_for_new_row = 0 ;// for clear data
                 $new_runing_key++;      
             }
             else{
//                 p($non_act);
                $data2['dataSet'][$new_runing_key] =    $non_act;
                array_push($some_temp,  $non_act['qty_of_sku'] );
                $data2['dataSet'][$new_runing_key]['arr_select_inbound_id'] = $temp_arr_select_inbound_id ;
                $data2['dataSet'][$new_runing_key]['arr_select_reserv_qty'] = $temp_arr_select_reserv_qty ;
                $data2['dataSet'][$new_runing_key]['row_count'] = $new_runing_key ; 
                $data2['dataSet'][$new_runing_key]['use_full'] = 'FALSE';
                $data2['dataSet'][$new_runing_key]['item_id'] = $data['dataSet'][$new_runing_key]['item_id'];
                $new_runing_key++;
             }
                        
             
        }
        
        foreach($data2['dataSet'] as $n_key => $non_act){
            $data2['dataSet'][$n_key]['arr_select_reserv_qty']  = $some_temp;
        }
        
        $data['dataSet'] = $data2['dataSet'] ;
        
        
    }    
        
//        p($some_temp); exit();
//
//          
//          
        /////
        $all_product_list = array();
        if(!empty($data['dataSet'])):
            foreach($data['dataSet'] as $key_dataSet => $dataSet):

                $problem_row = array();
                $sum_all_reserve_qty = 0;

                $productCode = $dataSet["productCode_val"];
                $productStatus = $dataSet["productStatus_val"];
                $productSubStatus = $dataSet["productSubStatus_val"];
                $productLot = $dataSet["productLot_val"];
                $productSerial = $dataSet["productSerial_val"];
                $productMfd = $dataSet["productMfd_val"];
                $productExp = $dataSet["productExp_val"];
                $invoiceNo = (empty($dataSet["invoiceNo_val"])?NULL:$dataSet["invoiceNo_val"]);
                $qty_of_sku = str_replace(',', '', $dataSet["qty_of_sku"]);
                $arr_select_inbound_id = null;
                $arr_error_rule = null;
                $tmp_select_inbound_id = $dataSet["arr_select_inbound_id"];
                $arr_select_reserv_qty = str_replace(',', '', $dataSet["arr_select_reserv_qty"]);
                $tmp_inbound_and_reserve = array();
                if(!empty($tmp_select_inbound_id)):
                    foreach($tmp_select_inbound_id as $key => $val):
                        $tmp_inbound_and_reserve[$val] = (empty($tmp_inbound_and_reserve[$val])?$arr_select_reserv_qty[$key]:$tmp_inbound_and_reserve[$val]+$arr_select_reserv_qty[$key]);
                    endforeach;
                endif;

                $palletCode_val = "";
                $palletIsFull_val = "";
                $palletDispatchType_val = "NULL";
                $chkPallet_val = "";
                if ($this->pallet == TRUE && @$dataSet["chkPallet_val"] == 1):
                    $palletCode_val = trim($dataSet["palletCode_val"]);
                    $palletIsFull_val = $dataSet["palletIsFull_val"];
                    $palletDispatchType_val = $dataSet["palletDispatchType_val"];
                    $chkPallet_val = $dataSet["chkPallet_val"];
                endif;

                $criteria = new stdClass();
                $criteria->product_code = $productCode;
                $criteria->productStatus = $this->inbound->getStatusByCode(strtoupper($productStatus));
                $criteria->productSubStatus = $this->inbound->getSubStatusByCode(strtoupper($productSubStatus));
                $criteria->productLot = $productLot;
                $criteria->productSerial = $productSerial;
                $criteria->productMfd = $productMfd;
                $criteria->productExp = $productExp;
                $criteria->limit_start = 0;
                $criteria->limit_max = 999;
                $criteria->s_search = NULL;
                $criteria->palletCode_val = $palletCode_val;
                $criteria->chkPallet_val = $chkPallet_val;
                $criteria->palletIsFull_val = $palletIsFull_val;
                $criteria->palletDispatchType_val = $palletDispatchType_val;
                $criteria->order_by = 'Product_Code ASC';
                $criteria->dp_type = NULL;
                $criteria->invoiceNo = $invoiceNo;
                $criteria->exact_match = TRUE;
                $criteria->use_full = $dataSet["use_full"];
//p($criteria);exit();
                $result = $this->inbound->getDispatchProductDetails($criteria);
                $result = $result->result();
//                p($this->db->last_query());
                $result2 = array();
                if($dataSet["use_full"] == 'TRUE'){
                    if(!empty($result)){
                        $temp_fpl_cut = 0 ; //////$dataSet["fpl"];
//                        p($temp_fpl_cut);exit();
                        foreach($result as $key_cutoff => $data_cutoff){
                            $temp_fpl_cut += $data_cutoff->Est_Balance_Qty;
                            array_push($result2,$data_cutoff);
                            if($temp_fpl_cut >= $dataSet["fpl"]){
                                break;
                            }
                        }
//                        array_push($result2,$result[0]);
                        $temp_fpl_cut = 0;
                        $result = $result2;
                    }
                }
//                p($result2);
//                p($result); //exit();
                if(empty($result)):

                    $json['alert'] = "System QTY  Less Your Input QTY Please Check This.";
                    $json['est_qty_after_cut'] = 0;
                    $json['qty_after_cut'] = 0;
                    $json['product'] = array();
                    $json['item_id'] = $dataSet['item_id'];
                    $json['row_count'] = $dataSet['row_count'];


                    $problem_row['item_id'] = $dataSet['item_id'];
                    $problem_row['row_count'] = $dataSet['row_count'];
                    $problem_row['less_qty'] = floatval($qty_of_sku);
                    $returns['arr_problem_row'][] = $problem_row;

                else:
                    $putaway_rule = $result['0']->PutAway_Rule;

                    $new_list = array();
                    $post_val = array();
                    $check_out_of_qty = TRUE;
                    $need_qty_of_sku = $qty_of_sku;
                
                    if (!empty($result)) :
                        if ($palletDispatchType_val == 'FULL'):

                            $column["Inbound_Id"] = "Inbound_Id";
                            $qry_inbound = $this->inbound->get_inbound_by_palletID($result[0]->Pallet_Id, $column);
                            $result_inbound = $qry_inbound->result_array();

                            $count = count($result_inbound);
                            $i = 1;
                            foreach ($result_inbound as $key => $inboundid):
                                if (empty($arr_select_inbound_id) || !in_array($rs->Inbound_Id, $arr_select_inbound_id)):
                                    $post_val[] = $inboundid["Inbound_Id"];
                                    $i++;
                                endif;
                            endforeach;

                        else:
                            $count = count($result);
                            $i = 1;
                            foreach ($result as $key_rs => $rs):
                                if (!empty($tmp_select_inbound_id) and (in_array($rs->Inbound_Id, $tmp_select_inbound_id))) {
                                    if($rs->Est_Balance_Qty <= $tmp_inbound_and_reserve[$rs->Inbound_Id]):
                                        $arr_select_inbound_id[] = $rs->Inbound_Id;
                                    else :
                                        $arr_error_rule[$rs->Inbound_Id]['product_code'] = $rs->Product_Code;
                                    endif;
                                }
                                if (empty($arr_select_inbound_id) || !in_array($rs->Inbound_Id, $arr_select_inbound_id)):
                                    $post_val[] = $rs->Inbound_Id;
                                    $i++;
                                endif;
                            endforeach;
                        endif;
                        
                        $post_val = implode(',',$post_val);
//                        p($post_val);
                        if(!empty($post_val)):
//                            p($post_val);exit();
                            $query = $this->preDispModel->getListPreDispatchByProdCodeViaAjax($post_val , $putaway_rule, FALSE);
                            $pre_dispatch_list = $query->result();
//                            p($pre_dispatch_list);
                            $first = TRUE;

                            // Re-Calculate
                            if (count($remain_balance_by_inbound) > 0) {
                                foreach ($pre_dispatch_list as $rows) {
                                    if (array_key_exists($rows->Inbound_Id, $remain_balance_by_inbound)) {
                                        if ($remain_balance_by_inbound[$rows->Inbound_Id] == 0) {
                                            $rows->PD_Reserv_Qty = $rows->Receive_Qty;
                                        } else {
                                            $rows->PD_Reserv_Qty =  ($rows->Receive_Qty - $remain_balance_by_inbound[$rows->Inbound_Id]);
                                        }
                                    }
                                }
                            }
                            // END ======
//                              p($pre_dispatch_list);
                            foreach ($pre_dispatch_list as $rows) :
//                                if($qty_of_sku = )
                                $rows->ByRule = TRUE;
                                $check_est_bal = $rows->Est_Balance_Qty = floatval (($rows->Receive_Qty) - ($rows->PD_Reserv_Qty) - ($rows->Dispatch_Qty) - ($rows->Adjust_Qty) - @$tmp_inbound_and_reserve[$rows->Inbound_Id]);

                                // ADD temp qty depend on inbound id
                                if (!array_key_exists($rows->Inbound_Id, $remain_balance_by_inbound)) {
                                    $remain_balance_by_inbound[$rows->Inbound_Id] = $check_est_bal;
                                }
                                // END temp qty depend on inbound id

                                if ($rows->Est_Balance_Qty > 0) {
                                    $rows->Est_Balance_Qty = "<span class='est_of_inbound_" . $rows->Inbound_Id . "' >" . floatval($rows->Est_Balance_Qty) . "</span>";
                                    $rows->DP_Type_Pallet = $palletDispatchType_val;

                                    if ($check_est_bal < $qty_of_sku):
                                        $rows->Reserve_Qty = $check_est_bal;
                                    else:
                                        $rows->Reserve_Qty = $qty_of_sku;
                                    endif;

                                    if ($rows->PD_Reserv_Qty > 0 && !$first) {
//                                        $arr_error_rule['product_code'][] = $rows->Product_Code;
//                                        $arr_error_rule['location'][] = $rows->Actual_Location;
//                                        $rows->ByRule = FALSE;
                                    }

                                    $sum_all_reserve_qty += $rows->Reserve_Qty;
                                    $qty_of_sku -= $rows->Reserve_Qty;

                                    $remain_balance_by_inbound[$rows->Inbound_Id] -= $rows->Reserve_Qty; // ADD to Temp for next loop
                                    $new_list[] = thai_json_encode((array) $rows);

                                    if ($qty_of_sku == 0):
                                        $check_out_of_qty = FALSE;
                                        break;
                                    endif;
                                } else {
                                    $first = FALSE; // If use another item set to FALSE for collect warning.
                                }

                            endforeach;
                        endif;
                    endif;
                    $json['product'] = $new_list;
                    $json['alert'] = 'OK';
                    if (!is_null($arr_error_rule)) {
                        $returns['arr_problem_rule'][] = json_encode($arr_error_rule);
                    }
                    if($check_out_of_qty):
                        $system_qty = $need_qty_of_sku - $qty_of_sku;
                        $json['alert'] = "System QTY({$system_qty}) Less Your Input QTY({$need_qty_of_sku}) Please Check This.";
                        $qty_after_cut = $need_qty_of_sku - $system_qty;
                        $json['est_qty_after_cut'] = 0;
                        $json['qty_after_cut'] = $qty_after_cut;
                        $json['item_id'] = $dataSet['item_id'];
                        $json['row_count'] = $dataSet['row_count'];

                        $problem_row['item_id'] = $dataSet['item_id'];
                        $problem_row['row_count'] = $dataSet['row_count'];
                        $problem_row['less_qty'] = $need_qty_of_sku - $sum_all_reserve_qty;
                        $returns['arr_problem_row'][] = $problem_row;
                    else:
                        $returns['can_del_row'][] = $dataSet['row_count'];
                    endif;
                    $all_product_list = array_merge_recursive($all_product_list, $new_list);
                endif;

                    $return[] = $json;
//                    if($key_dataSet == 1){
//                          break;
//                    }
//                    break;
            endforeach;
        endif;
                 foreach($all_product_list as $key => $val){
                        if($val['Reserve_Qty'] == 0){
                            unset($all_product_list[$key]);
                        } 
                    }
        $returns['all_product_list'] = $all_product_list;
//        array_push($returns['arr_problem_row'], array('item_id' =>null,'row_count'=>2,'less_qty'=>0));
//        p($returns);exit();

//         p($all_product_list); //exit();
        echo json_encode($returns);
        

    }
    
    public function showSelectData() {
        $post_val = $this->input->post();
        $table_name = $this->input->post('tableName');
        $string_to_explode = $post_val["post_val"];

        //ADD BY POR 2014-02-19 เธฃเธฑเธ�เธ�เน�เธฒ dispatch type เน€เธ�เธทเน�เธญเธชเน�เธ�เธ�เธฅเธฑเธ�เน�เธ�เน�เธ� datatable เธ”เน�เธงเธข
        $dp_type_pallet = $this->input->post("dp_type_pallet_val");
        if ($dp_type_pallet == "NULL"):
            $dp_type_pallet = NULL;
        endif;
        //END ADD;
        //$array_for_extract = explode(",",$string_to_explode);
//        p($string_to_explode);
        $query = $this->preDispModel->getListPreDispatchByProdCodeViaAjax($string_to_explode);
        $pre_dispatch_list = $query->result();

        $new_list = array();
        foreach ($pre_dispatch_list as $rows) {
            $rows->DP_Type_Pallet = $dp_type_pallet; //ADD BY POR 2014-02-19 เน€เธ�เธดเน�เธกเน�เธซเน�เธชเน�เธ�เธ�เน�เธฒ dispatch type เธ�เธฅเธฑเธ�เน�เธ�เธ”เน�เธงเธข
            $rows->Est_Balance_Qty = "<span class='est_of_inbound_" . $rows->Inbound_Id . "' >" . set_number_format($rows->Est_Balance_Qty) . "</span>";
            $new_list[] = thai_json_encode((array) $rows);
        }

        $json['product'] = $new_list;
        echo json_encode($json);
    }

    public function saveEditedRecord() {

        $editedValue = $_REQUEST['value'];
        //Always accepts update(return posted value)
        echo iconv("UTF-8", "TIS-620", $editedValue);
    }

    public function ajaxCreateDocument() {

        /**
         * Set Variable
         */
        $check_not_err = TRUE;
        $return = array();
        $set_return = array();
        $data = $this->input->post();
    //    p($data);
    //    exit();


        $renter_id_select = $this->input->post("renter_id_select");
        $warehouse_from = $this->input->post("frm_warehouse_select");
        $warehouse_to = $this->input->post("to_warehouse_select");
        $dispatch_type_select = $this->input->post("dispatch_type_select");
        $estDispatchDate = $this->input->post("estDispatchDate");
//        # Parameter of document number
        $doc_refer_int = strtoupper(iconv("UTF-8", "TIS-620", $this->input->post("doc_refer_int")));
        $doc_refer_ext = strtoupper(iconv("UTF-8", "TIS-620", $this->input->post("doc_refer_ext")));
        $doc_refer_inv = strtoupper(iconv("UTF-8", "TIS-620", $this->input->post("doc_refer_inv")));
        $doc_refer_ce = strtoupper(iconv("UTF-8", "TIS-620", $this->input->post("doc_refer_ce")));
        $doc_refer_bl = strtoupper(iconv("UTF-8", "TIS-620", $this->input->post("doc_refer_bl")));
//
        $process_id = $this->input->post("process_id");
        $process_type = $this->input->post("process_type");
        $present_state = $this->input->post("present_state");
        $remark = $this->input->post("remark");

//        $action_type = $this->input->post("action_type");
        $action_type = "Pre-Dispatch";
//        $next_state = $this->input->post("next_state");
        $next_state = 0;

        $user_id = $this->session->userdata("user_id");
        $is_urgent = $this->input->post("is_urgent");   //add for ISSUE 3312 : by kik : 20140120
        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        /**
         * chk Duplicate Document
         */
        $data_chk_doc_ext_duplicate['doc_refer_ext'] = $doc_refer_ext;
        $data_chk_doc_ext_duplicate['Process_Type'] = $process_type;
        $chk_doc_ext_duplicate = $this->validate_data->chk_doc_ext_duplicate($data_chk_doc_ext_duplicate);
        if (!empty($chk_doc_ext_duplicate)):
            $check_not_err = FALSE;
            $set_return = array(
                'message' => "Document External is Duplicate",
                'id' => 'doc_refer_ext'
            );
            $return['critical'][] = $set_return;
        endif;

//        p($check_not_err);
//        exit();

        /**
         * ================== Start Transaction =========================
         */
        $this->transaction_db->transaction_start();


        /**
         * Generate DDR Number
         */
        if ($check_not_err):
            $set_return['document_no'] = $document_no = create_document_no_by_type("DDR"); // Add by Ton! 20140428
            if (empty($document_no) || $document_no == ''):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not create 'DDR No'.";
            endif;
        endif;


        /**
         * Create new Workflow
         */
        if ($check_not_err):
            $dataAddNewWorkflow['Document_No'] = $document_no;
            list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $dataAddNewWorkflow); //Edit by Ton! 20131021
            $set_return['flow_id'] = $flow_id;
            $set_return['action_id'] = $action_id;

            if (empty($flow_id) || $flow_id == '' || empty($action_id) || $action_id == ''):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not Create new Workflow.";
            endif;
        endif;


        /**
         * Create new Order
         */
        if ($check_not_err):
            $estDispatchDate = convertDate($estDispatchDate, "eng", "iso", "-");
            $order = array(
                'Flow_Id' => $flow_id
                , 'Document_No' => $document_no
                , 'Doc_Refer_Ext' => $doc_refer_ext
                , 'Doc_Refer_AWB' => $doc_refer_ext
                , 'Doc_Refer_Int' => $doc_refer_int
                , 'Doc_Refer_Inv' => $doc_refer_inv
                , 'Doc_Refer_CE' => $doc_refer_ce
                , 'Doc_Refer_BL' => $doc_refer_bl
                , 'Doc_Type' => $dispatch_type_select
                , 'Estimate_Action_Date' => $estDispatchDate
                , 'Source_Type' => 'Warehouse'
                , 'Destination_Type' => 'Customer'
                , 'Create_By' => $user_id
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Create_Date' => date("Y-m-d H:i:s")
                , 'Process_Type' => $process_type
                , 'Renter_Id' => $renter_id_select
                , 'Source_Id' => $warehouse_from
                , 'Destination_Id' => $warehouse_to
                , 'Owner_Id' => $warehouse_from
                , 'Is_urgent' => $is_urgent
            );
        //    p($order); exit;
            $set_return['order_id'] = $order_id = $this->stock->addOrder($order);
            if (empty($order_id) || $order_id == ""):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not Create new Order.";
            endif;
        endif;


        /**
         * register_token
         */
        if ($check_not_err):
            $token = register_token($flow_id, $present_state, $process_id);
            $set_return['token'] = $token;
            if (empty($token) || $token == ""):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not Register Token.";
            endif;
        endif;



//        p($set_return);
//        p($check_not_err);
//
//        $check_not_err = false;
        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):
            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

//            $set_return['message'] = "Create Document Pre-Dispatch Complete";
//            $return['success'][] = $set_return;
//            $json['status'] = "save";
//            $json['return_val'] = $return;
            $set_return['success'] = 'OK';
            $json = $set_return;

        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $set_return_error['critical'][]['message'] = "Create Document Pre-Dispatch Incomplete.";
            $return = array_merge_recursive($set_return_error, $return);
            $json['status'] = "save";
            $json['return_val'] = $return;
        endif;

//            $json = $set_return;
//        exit();
        echo json_encode($json);
    }

    public function ajaxSaveEditedRecordReservQty() {

        /**
         * Set Variable
         */
        $check_not_err = TRUE;
        $return = array();
        $set_return = array();
        $data = $this->input->post();
        $data['reserv_qty'] = floatval(str_replace(',', '', $data['reserv_qty']));

        $this->transaction_db->transaction_start();

        if ($data['item_id'] == 'new'):
            $order_detail = array(
                'Order_Id' => $data['order_id']
                , 'Product_Code' => $data['product_code']
                , 'Inbound_Item_Id' => $data['inbound_id']
                , 'Reserv_Qty' => $data['reserv_qty']
            );
            $data['item_id'] = $addOrderDetailByOneRecord = $this->stock->addOrderDetailByOneRecord($order_detail);
            if (empty($addOrderDetailByOneRecord) || $addOrderDetailByOneRecord == ""):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not Save Order Detail.";
            endif;


            /**
             * getInboundDetail
             */
            if ($check_not_err):
                $getInboundDetail = $this->util_query_db->select_table("STK_T_Inbound", "Inbound_Id, (Receive_Qty - (PD_Reserv_Qty + Dispatch_Qty + Adjust_Qty)) AS Est_Balance_Qty", array("Inbound_Id" => $data['inbound_id']))->row_array();
                if (empty($getInboundDetail)):
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not Get Stock Data.";
                endif;
            endif;


            /**
             * reservPDReservQtyArray
             */
            if ($check_not_err):
                /**
                 * Update PD_Reserve_Qty
                 */
                if (( number_format($getInboundDetail['Est_Balance_Qty'], 3, '.', '') - number_format($data['reserv_qty'], 3, '.', '') ) < number_format(0, 3, '.', '')):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $alert_est = set_number_format($getInboundDetail['Est_Balance_Qty']);
                    $alert_reserv = set_number_format($data['reserv_qty']);
                    $return['critical'][]['message'] = "'" . _lang('est_balance_qty') . "' ในขณะที่เหลืออยู่ {$alert_est} แต่คุณต้องการจองทั้งหมด {$alert_reserv} คำนวนแล้วของมีจำนวนไม่พอ กรุณาตรวจสอบอีกครั้ง.";
                else:
                    $tmpSetReservPDReservQtyArray['Inbound_Item_Id'] = $data['inbound_id'];
                    $tmpSetReservPDReservQtyArray['Reserv_Qty'] = $data['reserv_qty'];
                    $SetReservPDReservQtyArray[] = $tmpSetReservPDReservQtyArray;
                    unset($tmpSetReservPDReservQtyArray);
                    $result_PD_reserv_qty = $this->stock->reservPDReservQtyArray($SetReservPDReservQtyArray);
                    unset($SetReservPDReservQtyArray);
                    if (!$result_PD_reserv_qty):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not Update PD_Reserve_Qty.";
                    endif;
                endif;

            endif;

        endif;
        $set_return['item_id'] = $data['item_id'];


        /**
         * getOrderDetailByItemId
         */
        if ($check_not_err):
            $getOrderDetailByItemId = $this->stock->getOrderDetailByItemId($data['item_id']);
//            p($getOrderDetailByItemId);
            if (empty($getOrderDetailByItemId)):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not Get Order Detail.";
            endif;
        endif;


        /**
         * getOrderDetailByItemId
         */
        if ($check_not_err):


            if ($data['mfd'] != "") :
                $data['mfd'] = convertDate($data['mfd'], "eng", "iso", "-");
            else :
                $data['mfd'] = null;
            endif;

            if ($data['exp'] != "") :
                $data['exp'] = convertDate($data['exp'], "eng", "iso", "-");
            else :
                $data['exp'] = null;
            endif;

            if ($data['pallet_code'] != "" && !empty($data['pallet_code'])):
                $data['pallet_id'] = $this->pl->get_palletId_by_code($data['pallet_code']);
            else:
                $data['pallet_id'] = NULL;
            endif;



//            $getOrderDetailByItemId['Reserv_Qty']
            $order_detail = array(
                'Order_Id' => $data['order_id']
                , 'Product_Id' => $data['product_id']
                , 'Product_Code' => $data['product_code']
                , 'Product_Status' => $data['product_status']
                , 'Product_Sub_Status' => $data['product_sub_status']
                , 'Suggest_Location_Id' => $data['suggest_loc']
                , 'Product_Lot' => $data['lot']
                , 'Product_Serial' => $data['serial']
                , 'Product_Mfd' => $data['mfd']
                , 'Product_Exp' => $data['exp']
                , 'Reserv_Qty' => $data['reserv_qty']
                , 'Unit_Id' => $data['unit_id']
                , 'Price_Per_Unit' => str_replace(",", "", $data['price_per_unit'])
                , 'Unit_Price_Id' => $data['unit_price_id']
                , 'All_Price' => floatval(str_replace(",", "", $data['all_price']))
                , 'Remark' => $data['remark']
                , 'Pallet_Id' => $data['pallet_id']
                , 'Inbound_Item_Id' => $data['inbound_id']
            );
//        p($order_detail);
            $updateOrderDetail = $this->stock->updateOrderDetail($order_detail, array('Item_Id' => $data['item_id']));
//            p($updateOrderDetail);
            if (empty($updateOrderDetail) || $updateOrderDetail == ""):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not Update Order Detail.";
            endif;


            /**
             * getInboundDetail
             */
            if ($check_not_err):
                $getInboundDetail = $this->util_query_db->select_table("STK_T_Inbound", "Inbound_Id,( Receive_Qty - (PD_Reserv_Qty + Dispatch_Qty + Adjust_Qty)) AS Est_Balance_Qty", array("Inbound_Id" => $data['inbound_id']))->row_array();
                if (empty($getInboundDetail)):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not Get Stock Data.";
                endif;
            endif;


            /**
             * reservPDReservQtyArray
             */
            if ($check_not_err):
                /**
                 * Update PD_Reserve_Qty
                 */
                $operand = '+';
                $cal_pd_reserv = number_format($data['reserv_qty'], 3, '.', '') - number_format($getOrderDetailByItemId['Reserv_Qty'], 3, '.', '');
                if (( number_format($getInboundDetail['Est_Balance_Qty'], 3, '.', '') - number_format($cal_pd_reserv, 3, '.', '') ) < number_format(0, 3, '.', '')):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $alert_est = set_number_format($getInboundDetail['Est_Balance_Qty']);
                    $alert_reserv = set_number_format($data['reserv_qty']);
                    $alert_cal_pd_reserv = set_number_format($cal_pd_reserv);
                    $return['critical'][]['message'] = "'" . _lang('est_balance_qty') . "' ในขณะที่เหลืออยู่ {$alert_est} แต่คุณต้องการจองทั้งหมด {$alert_reserv} ซึ่งเป็นการเพิ่มการจองอีก {$alert_cal_pd_reserv} คำนวนแล้วของมีจำนวนไม่พอ กรุณาตรวจสอบอีกครั้ง.";
                else:
                    if ($cal_pd_reserv < number_format(0, 3, '.', '')):
                        $operand = '-';
                        $cal_pd_reserv = abs($cal_pd_reserv);
                    endif;
                    $tmpSetReservPDReservQtyArray['Inbound_Item_Id'] = $data['inbound_id'];
                    $tmpSetReservPDReservQtyArray['Reserv_Qty'] = $cal_pd_reserv;
                    $SetReservPDReservQtyArray[] = $tmpSetReservPDReservQtyArray;
                    unset($tmpSetReservPDReservQtyArray);
                    //                p($SetReservPDReservQtyArray);
                    //                p($operand);
                    $getInboundDetail['Est_Balance_Qty'];
                    //                if():
                    //                    $getInboundDetail
                    //                endif;

                    $result_PD_reserv_qty = $this->stock->reservPDReservQtyArray($SetReservPDReservQtyArray, $operand);
                    unset($SetReservPDReservQtyArray);
                    if (!$result_PD_reserv_qty):
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not Update PD_Reserve_Qty.";
                    endif;
                endif;
            endif;


        endif;





//        $this->transaction_db->transaction_rollback();
//        exit();
//


        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):
            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

//            $set_return['message'] = "Reject and Return Pre-Dispatch Complete";
//            $return['success'][] = $set_return;
//            $json['status'] = "save";
//            $json['return_val'] = $return;
            $set_return['success'] = 'OK';
            $json = $set_return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            unset($set_return['item_id']);
            $set_return['critical'][]['message'] = "Save Order Detail in Pre-Dispatch Incomplete.";
            $return = array_merge_recursive($set_return, $return);
            $json['status'] = "save";

            $json['return_val'] = $return;
        endif;

        echo json_encode($json);

//        exit();
    }

    public function ajaxRemoveRecordReservQty() {


//        $editedValue = $_REQUEST['value'];
//        //Always accepts update(return posted value)
//        echo iconv("UTF-8", "TIS-620", $editedValue);


        /**
         * Set Variable
         */
        $check_not_err = TRUE;
        $return = array();
        $set_return = array();
        $data = $this->input->post();
//        p($data);
//        exit();



        /**
         * ================== Start Transaction =========================
         */
        $this->transaction_db->transaction_start();



        /**
         * getOrderDetailByItemId
         */
        if ($check_not_err):
            $getOrderDetailByItemId = $this->stock->getOrderDetailByItemId($data['item_id']);
//            p($getOrderDetailByItemId);
            if (empty($getOrderDetailByItemId)):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not Get Order Detail.";
            endif;
        endif;

        /**
         * reservPDReservQtyArray
         */
        if ($check_not_err):
            /**
             * Update PD_Reserve_Qty
             */
            if ($getOrderDetailByItemId['Active'] == ACTIVE):
                $SetReservPDReservQtyArray[] = $getOrderDetailByItemId;
                $result_PD_reserv_qty = $this->stock->reservPDReservQtyArray($SetReservPDReservQtyArray, '-');
                unset($SetReservPDReservQtyArray);
                if (!$result_PD_reserv_qty):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not Update PD_Reserve_Qty.";
                endif;
            endif;
        endif;

        /**
         * updateOrderDetail
         */
        if ($check_not_err):
            $order_detail = array(
                "Active" => INACTIVE
            );
            $updateOrderDetail = $this->stock->updateOrderDetail($order_detail, array('Item_Id' => $data['item_id']));
//            p($updateOrderDetail);
            if (empty($updateOrderDetail) || $updateOrderDetail == ""):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not Update Order Detail.";
            endif;
        endif;






//        $this->transaction_db->transaction_rollback();
//        exit();
        //$check_not_err = FALSE;
//        $check_not_err = false;
        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):
            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

//            $set_return['message'] = "Reject and Return Pre-Dispatch Complete";
//            $return['success'][] = $set_return;
//            $json['status'] = "save";
//            $json['return_val'] = $return;
            $set_return['success'] = 'OK';
            $json = $set_return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $set_return['critical'][]['message'] = "Save Order Detail in Pre-Dispatch Incomplete.";
            $return = array_merge_recursive($set_return, $return);
            $json['status'] = "save";
            $json['return_val'] = $return;
        endif;

        echo json_encode($json);
    }

    public function AddData() {
        $editedValue = iconv("UTF-8", "TIS-620", $_REQUEST['value']);
        //Always accepts update(return posted value)
        echo $editedValue;
    }

    public function DeleteData() {
        $editedValue = $_REQUEST['value'];
        //Always accepts update(return posted value)
        echo $editedValue;
    }

    public function sentToModel($str) {
        $result = $this->preDispModel->queryFromString($str);
        return $result;
    }

    function rejectAndReturnAction() {

        $this->load->model("stock_model", "stock");

        $check_not_err = TRUE;
        $return = array();

        $process_id = $this->input->post("process_id");
        $flow_id = $this->input->post("flow_id");
        $present_state = $this->input->post("present_state");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $document_no = $this->input->post("document_no");

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

            $set_return['message'] = "Reject and Return Pre-Dispatch Complete";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $set_return['critical'][]['message'] = "Reject and Return Pre-Dispatch Incomplete.";
            $return = array_merge_recursive($set_return, $return);
            $json['status'] = "save";
            $json['return_val'] = $return;
        endif;

        echo json_encode($json);
    }

    function rejectAction() {

        $this->load->model("stock_model", "stock");


        /**
         * Set Variable
         */
        $check_not_err = TRUE;
        $return = array();

        #add condition check data from list page or form page : add by kik : 2013-12-02
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
                $query_order = $this->stock->getOrderTable($column_order, $where_order);
                $order_id = $query_order[0]->Order_Id;
            }
            #if data send from form page When get data in post() : add by kik : 2013-12-02
        } else {
            $process_id = $this->input->post("process_id");
            $flow_id = $this->input->post("flow_id");
            $present_state = $this->input->post("present_state");
            $action_type = $this->input->post("action_type");
            $next_state = $this->input->post("next_state");
            $document_no = $this->input->post("document_no");
            $data['Document_No'] = $document_no;
            $order_id = $this->input->post("order_id");
        }


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

            #return qty to PD_reserv in inbound table
            $order_detail = $this->stock->getOrderDetailByOrderId($order_id);
//p($order_detail);
//exit();
            /**
             * update inbound qty in Inbound table
             */
            $check_inbound_item_id = FALSE;
            foreach ($order_detail as $key_od => $od):
                if (!empty($od->Inbound_Item_Id)):
                    $check_inbound_item_id = TRUE;
                endif;
            endforeach;

            if ($check_inbound_item_id):
                $result_reservPDReservQtyArray = $this->stock->reservPDReservQtyArray($order_detail, "-");
                if (!$result_reservPDReservQtyArray):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not update Inbound QTY.";
                endif;
            endif;


            /**
             * update order detail = N
             */
            if ($check_not_err):
                $detail['Active'] = 'N';
                $where['Order_Id'] = $order_id;

                $result = $this->stock->updateOrderDetail($detail, $where);
                if (!$result):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not Set InActive Order Detail.";
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

                if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") :
                    echo "<script>alert('Delete Pre-Dispatch Complete.');</script>";
                    redirect('flow/flowPreDispatchList', 'refresh');
                else :
                    $set_return['message'] = "Reject Pre-Dispatch Complete.";
                    $return['success'][] = $set_return;
                    $json['status'] = "save";
                    $json['return_val'] = $return;
                endif;
            else :
                /**
                 * ================== Rollback Transaction =========================
                 */
                $this->transaction_db->transaction_rollback();

                if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") :
                    echo "<script>alert('Delete Pre-Dispatch not complete. Please check?');</script>";
                    redirect('flow/flowPreDispatchList', 'refresh');
                else :
                    $set_return['critical'][]['message'] = "Reject Pre-Dispatch Incomplete.";
                    $return = array_merge_recursive($set_return, $return);
                    $json['status'] = "save";
                    $json['return_val'] = $return;
                endif;
            endif;
        else :
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") :
                echo "<script>alert('Delete Pre-Dispatch not complete. Please check?');</script>";
                redirect('flow/flowPreDispatchList', 'refresh');
            else :
                $set_return['critical'][]['message'] = "Reject Pre-Dispatch Incomplete.";
                $return = array_merge_recursive($set_return, $return);
                $json['status'] = "save";
                $json['return_val'] = $return;
            endif;
        endif;

        echo json_encode($json);
    }

    public function validation_openPreDispatch() {

        $this->validation_confirmPreDispatch();

        exit();

        /**
         * set Variable
         */
        $data = $this->input->post();
        $return = array();

//        p($data);exit();

        /**
         * chk Duplicate Document
         */
        $data['Process_Type'] = 'OUTBOUND';
        $chk_doc_ext_duplicate = $this->validate_data->chk_doc_ext_duplicate($data);
        if (!empty($chk_doc_ext_duplicate)):
            $set_return = array(
                'message' => "Document External is Duplicate",
                'id' => 'doc_refer_ext'
            );
            $return['critical'][] = $set_return;
        endif;


        /**
         * chk PD_reserv Before Open
         */
        $resultCompare = $this->balance->_chkPDreservBeforeOpen($data['ci_reserv_qty'], $data['ci_inbound_id'], $data['prod_list'], SEPARATOR);
        $return = array_merge_recursive($return, $resultCompare);


        /**
         * chk Re order Point Before Open
         */
        $chk_re_order_point = $this->balance->chk_re_order_point($data['ci_reserv_qty'], $data['ci_prod_code'], $data['prod_list'], SEPARATOR);
        $return = array_merge_recursive($return, $chk_re_order_point);



        /**
         * Start loop
         */
        foreach ($data['prod_list'] as $key_product_list => $product_list):
            $product = explode(SEPARATOR, $product_list);


            /**
             * chk product expire
             */
            $chk_product_expire = $this->validate_data->product_expire($product[$data['ci_prod_code']], $product[$data['ci_exp']], $key_product_list, $data['ci_exp']);
            $return = array_merge_recursive($return, $chk_product_expire);



        endforeach;


//        p($return);
//        exit();

        if (!empty($return)):
            $json['status'] = "validation";
            $json['return_val'] = $return;
        else:
            $json['status'] = 'pass';
            $json['return_val'] = '';
        endif;

    //    p($json);
    //    exit();

        echo json_encode($json);
    }

    public function validation_confirmPreDispatch() {
        /**
         * set Variable
         */
        $data = $this->input->post();
//         p($data);exit;
        $return = array();

//        p($data);exit();

        /**
         * chk Duplicate Document
         */
        $data['Process_Type'] = 'OUTBOUND';
        $chk_doc_ext_duplicate = $this->validate_data->chk_doc_ext_duplicate($data);

 
        if (!empty($chk_doc_ext_duplicate)):
            $set_return = array(
                'message' => "Document External is Duplicate",
                'id' => 'doc_refer_ext'
            );
            $return['critical'][] = $set_return;
        endif;

        ////////////////////////////////////////////////////////////////////////////
        $res_response =  $this->validate_data->check_last_mfd_for_dispatch($data);
        $return = array_merge_recursive($return , $res_response);
        ////////////////////////////////////////////////////////////////////////////
        
        /**
         * chk PD_reserv Before Open
         */
        $resultCompare = $this->balance->_chkPDreservBeforeApprove($data['ci_reserv_qty'], $data['ci_inbound_id'], $data['ci_item_id'], $data['order_id'], $data['prod_list'], SEPARATOR, 'STK_T_Order_Detail');
 
        $return = array_merge_recursive($return, $resultCompare);
 

        /**
         * chk Re order Point Before Open
         */
        $chk_re_order_point = $this->balance->chk_re_order_point_before_approve($data['ci_reserv_qty'], $data['ci_prod_code'], $data['ci_item_id'], $data['order_id'], $data['prod_list'], SEPARATOR, 'STK_T_Order_Detail');
    
        $return = array_merge_recursive($return, $chk_re_order_point);



        /**
         * Start loop
         */
     
        foreach ($data['prod_list'] as $key_product_list => $product_list):

            $product = explode(SEPARATOR, $product_list);
            $m_data['Product_Code'] = $product[1];
            $m_data['Location_Code'] = $product[5];

            $chk_res_master = $this->master_rep->chk_master($m_data);
            $line_no = $key_product_list+1;
            if(!empty($chk_res_master)){
                $set_return = array(
                    'message' => 'In line NO. '.$line_no.' Location Match on Pickface Location ('.$m_data['Product_Code'].') ('.$m_data['Location_Code'].')',
                    'id' => 'doc_refer_ext'
                );
                //$return['critical'][] = $set_return;
            }
 
            /**
             * chk product expire
             */
            $chk_product_expire = $this->validate_data->product_expire($product[$data['ci_prod_code']], $product[$data['ci_exp']], $key_product_list, $data['ci_exp']);
            $return = array_merge_recursive($return, $chk_product_expire);

       

        endforeach;

        /**
         * Start loop
         */
        $line = 1;
        foreach ($data['prod_list'] as $key_product_list => $product_list):
            $product = explode(SEPARATOR, $product_list);
            $location = $product[$data['ci_location_code']];
            $tmp = explode("-",$location);
            $loc = $tmp[count($tmp) - 1];
            if (trim($loc) != "A") {
                $response = array();
                $set_return = array(
                    'message' => "Product " . $product[$data['ci_product_code']] . " in line " . $line . " not match pick face  streatery [" . $location .  "].",
                    'row' => (string) '1',
                    'col' => (string) '1'
                );

                $response['warning'][] = $set_return;
                $return = array_merge_recursive($return , $response);
            }
        $line++;
        endforeach;



    //    p( $return);
    //    exit();

        if (!empty($return)):
            $json['status'] = "validation";
            $json['return_val'] = $return;
        else:
            $json['status'] = 'pass';
            $json['return_val'] = '';
        endif;

    //    p($json);
    //    exit();

        echo json_encode($json);
    }

    public function validation_approvePreDispatch() {

        $data = $this->input->post();
        $return = array();

        /**
         * chk Duplicate Document
         */
        $data['Process_Type'] = 'OUTBOUND';
        $chk_doc_ext_duplicate = $this->validate_data->chk_doc_ext_duplicate($data);
        if (!empty($chk_doc_ext_duplicate)):
            $set_return = array(
                'message' => "Document External is Duplicate",
                'id' => 'doc_refer_ext'
            );
            $return['critical'][] = $set_return;
        endif;

        ////////////////////////////////////////////////////////////////////////////
        $res_response =  $this->validate_data->check_last_mfd_for_dispatch($data);
        $return = array_merge_recursive($return , $res_response);
        ////////////////////////////////////////////////////////////////////////////

        /**
         * chk PD_reserv Before Open
         */
        $resultCompare = $this->balance->_chkPDreservBeforeApprove($data['ci_reserv_qty'], $data['ci_inbound_id'], $data['ci_item_id'], $data['order_id'], $data['prod_list'], SEPARATOR, 'STK_T_Order_Detail');
        $return = array_merge_recursive($return, $resultCompare);


        /**
         * Check If Return From Approve Picking check relocation in HH first.
         */
        //$resultCompare = $this->balance->checkRelocationHH( $data['ci_reserv_qty'], $data['ci_inbound_id'], $data['ci_item_id'], $data['order_id'], $data['prod_list'], SEPARATOR, 'STK_T_Relocate_Detail');
        //$return = array_merge_recursive($return, $resultCompare);

        /**
         * chk Re order Point Before Open
         */
        $chk_re_order_point = $this->balance->chk_re_order_point_before_approve($data['ci_reserv_qty'], $data['ci_prod_code'], $data['ci_item_id'], $data['order_id'], $data['prod_list'], SEPARATOR, 'STK_T_Order_Detail');
        $return = array_merge_recursive($return, $chk_re_order_point);



        /**
         * Start loop
         */
        foreach ($data['prod_list'] as $key_product_list => $product_list):
            $product = explode(SEPARATOR, $product_list);


            /**
             * chk product expire
             */
            $chk_product_expire = $this->validate_data->product_expire($product[$data['ci_prod_code']], $product[$data['ci_exp']], $key_product_list, $data['ci_exp']);
            $return = array_merge_recursive($return, $chk_product_expire);



        endforeach;

        /**
         * Start loop
         */
        $line = 1;
        foreach ($data['prod_list'] as $key_product_list => $product_list):
            $product = explode(SEPARATOR, $product_list);
            $location = $product[$data['ci_location_code']];
            $tmp = explode("-",$location);
            $loc = $tmp[count($tmp) - 1];
            if (trim($loc) != "A") {
                $response = array();
                $set_return = array(
                    'message' => "Product " . $product[$data['ci_product_code']] . " in line " . $line . " not match pick face  streatery [" . $location .  "].",
                    'row' => (string) '1',
                    'col' => (string) '1'
                );

                $response['warning'][] = $set_return;
                $return = array_merge_recursive($return , $response);
            }
        $line++;
        endforeach;


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

        echo json_encode($json);
    }
    
    private function _smt_group_by($key, $data) {
    $result = array();

    foreach($data as $val) {
        if(array_key_exists($key, $val)){
            $result[$val[$key]][] = $val;
        }else{
            $result[""][] = $val;
        }
    }

    return $result;
}
}

?>
