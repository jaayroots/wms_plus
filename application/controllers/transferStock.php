<?php

/**
 * Description of pre_dispatch_controller
 * -------------------------------------
 * Put this class on project at 10/06/2013 
 * @author Pakkaphon P.(PHP PG) 
 * Create by NetBean IDE 7.3
 * SWA WMS PLUS Project.
 * Use with dispatch module function
 * Use Codeinigter Framework with combination of css and js.
 * --------------------------------------
 */
class transferStock extends CI_Controller {

    public $settings;       //add by kik : 20140115

    //put your code herereservPDReservQty

    public function __construct() {
        parent::__construct();
        $this->settings = native_session::retrieve();   //add by kik : 20140115
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->library('validate_data');  // add for get this libraries for check validation data
        $this->load->helper('form');

        $this->load->helper('util_helper'); //add by kik : 12-09-2013

        $this->load->model("transfer_stock_model", "transferStock");
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("workflow_model", "wf");
        $this->load->model("stock_model", "stock");
        $this->load->model("product_model", "p");
        $this->load->model("contact_model", "contact");

        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));

        $this->load->model("location_model", "loc"); // Add by Ton! 20130808
        $this->load->model("workflow_model", "flow");
        $this->load->model("company_model", "company");
        $this->load->model("system_management_model", "sys");
        $this->load->model("inbound_model", "inbound");  // add by kik(08-10-2013)
        $this->load->model("pre_dispatch_model", "preDispModel"); // add by kik(29-01-2014)

        $this->load->controller('balance', 'balance'); // add by kik (02-10-2013
    }

    public function index() {

        $this->transferStockList();
    }

    public function transferStockForm() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        //p($this->session->all_userdata());
        //p($this->session->userdata('branch_id'));
        $process_id = 9;
        $present_state = 0;

        $renter_id = $this->config->item('renter_id');

        $consignee_id = $this->config->item('owner_id');
        $owner_id = $this->config->item('owner_id');
        //echo ' renter = '.$renter_id;
//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "transferStock"); // Button Permission. Add by Ton! 20140131|

        $to_warehouse_opt = array("-1" => "--Please Select--");
        $frm_warehouse_opt_key = $this->transferStock->selectWarehouse();
        foreach ($frm_warehouse_opt_key as $key => $value) {
            $data[$value->Warehouse_Id] = $value->Warehouse_NameEN;
        }
        //from & to warehouse

        $frm_warehouse_opt = $data;
        $to_warehouse_opt = $data;




        #2395 
        #by kik (12-09-2013)
        #เพิ่มการค้นหาด้วย product status และ sub status
        #Start new code
        #==========================================================
        #Get Product Status	
        $q_product_status = $this->p->selectProductStatus();
        $r_product_status = $q_product_status->result();
//        $product_status_list = genOptionDropdown($r_product_status, "SYS");
        $product_status_list = genOptionDropdown($r_product_status, "SYS", FALSE, TRUE); // Edit by kik! 20131107
        $productStatus_select = form_dropdown("productStatus_select", $product_status_list, "", "id=productStatus_select");

        #Get Product Sub Status	
        $q_product_Substatus = $this->p->selectSubStatus();
        $r_product_Substatus = $q_product_Substatus->result();
//        $product_Substatus_list = genOptionDropdown($r_product_Substatus, "SYS");
        $product_Substatus_list = genOptionDropdown($r_product_Substatus, "SYS", FALSE, TRUE); // Edit by kik! 20131107
        $productSubStatus_select = form_dropdown("productSubStatus_select", $product_Substatus_list, "", "id=productSubStatus_select");

        #End new code
        #==========================================================
        #Get Renter [Company Renter] list		
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
//        $renter_list = genOptionDropdown($r_renter, "COMPANY");
        $renter_list = genOptionDropdown($r_renter, "COMPANY", TRUE, FALSE); // Edit by kik! 20131107
        #Get Shipper[Company Supplier] list
//        $q_shipper = $this->company->getSupplierAll(); // Comment By Akkarapol, 27/09/2013, shipper ของส่วนนี้ต้องใช้ที่เป็น Owner ไม่ใช่ของ Supplier
        $q_shipper = $this->company->getOwnerAll(); // Add By Akkarapol, 27/09/2013, shipper ของส่วนนี้ต้องใช้ที่เป็น Owner ไม่ใช่ของ Supplier จึงเพิ่ม query ส่วนนี้ขึ้น
        $r_shipper = $q_shipper->result();
//        $shipper_list = genOptionDropdown($r_shipper, "COMPANY");
        $shipper_list = genOptionDropdown($r_shipper, "COMPANY", TRUE, FALSE); // Edit by kik! 20131107
        #comment by kik (16-09-2013)
        #Get Consignee[Company Owner]  list
//        $q_consignee = $this->company->getNotOwner( $this->session->userdata('branch_id'));
//       // $q_consignee = $this->company->getNotOwnerAll();        
//        $r_consignee = $q_consignee->result();
//        $consignee_list = genOptionDropdown($r_consignee, "COMPANY");
        #add by kik (16-09-2013)
        $q_consignee = $this->company->getBranchNotOwner($this->session->userdata('branch_id')); // Add By Akkarapol, 12/09/2013, เพิ่มการ get ข้อมูลของ branch ที่ไม่ใช่ของตนเองเข้ามาเพื่อนำไปใส่ที่ Consignee
        $r_consignee = $q_consignee->result();
//        $consignee_list = genOptionDropdown($r_consignee, "COMPANY");
        $consignee_list = genOptionDropdown($r_consignee, "COMPANY", TRUE, FALSE); // Edit by kik! 20131107
        #end add by kik (16-09-2013)
        #Get Receive Type list			
        $dispatch_type = $this->sys->getTransferType(); #edit from getDispatchTypeByCondition to getTransferType by kik : 14-10-2013
//        $dispatch_type_list = genOptionDropdown($dispatch_type, "SYS");
        $dispatch_type_list = genOptionDropdown($dispatch_type, "SYS", TRUE, TRUE); // Edit by kik! 20131107
        //echo ' con = '.$consignee_id;
        $renter_id_select = form_dropdown("renter_id_select", $renter_list, $renter_id, "id=renter_id_select class=required");
        $frm_warehouse_select = form_dropdown("frm_warehouse_select", $shipper_list, "", "id=frm_warehouse_select  class=required");
        $to_warehouse_select = form_dropdown("to_warehouse_select", $consignee_list, $consignee_id, "id=to_warehouse_select  class=required");
        $dispatch_type_select = form_dropdown("dispatch_type_select", $dispatch_type_list, "TF1", "id=dispatch_type_select  class=required");

        $query = $this->transferStock->getTransferStockAll();
        $pre_dispatch_list = $query->result();

        #start add code for ISSUE 3320 :  by kik : 20140116
        $price_per_unit_table = '';
        if ($this->settings['price_per_unit'] == TRUE):
            $price_per_unit_table = ',null
                    ,null
                    ,null';
        else:
            $price_per_unit_table = "  , {
                    onblur: 'submit',
                    sUpdateURL: '" . site_url() . '/transferStock/saveEditedRecord' . "',
                    event: 'click',
                }
                ,null
                ,null";

        endif;
        #end add code for ISSUE 3320 :  by kik : 20140116
//        $this->data_form->form_name
        $str_form = $this->parser->parse('form/transferStockForm', array("parameter" => form_input('warehouse_form', 'whs_form')
            , "renter_id_select" => $renter_id_select
            , "frm_warehouse_select" => $frm_warehouse_select
            , "to_warehouse_select" => $to_warehouse_select
            , "dispatch_type_select" => $dispatch_type_select
            , "process_id" => $process_id
            , "present_state" => $present_state
            , "price_per_unit" => $this->settings['price_per_unit']  //add by kik : 20140115
                ), TRUE);
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'price_per_unit_table' => $price_per_unit_table         //add by kik : 20140116
            // Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="transferStockForm"></i>'
            // END Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'productStatus_select' => $productStatus_select // add by kik : 12-09-2013
            , 'productSubStatus_select' => $productSubStatus_select // add by kik : 12-09-2013
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_action' => $data_form->str_buttun
            , 'user_login' => $this->session->userdata('username')
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
        ));
    }

    public function transferStockFormWithData() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        //-------------- Query From Order and Order Detail -----------------------------//
        $flow_id = $this->input->post("id");

//        $flow_detail = $this->wf->getFlowDetail($flow_id);
        $flow_detail = $this->wf->getFlowDetail($flow_id, 'STK_T_Order'); // Edit by Ton! 20130814


        $process_id = $flow_detail[0]->Process_Id;
        $present_state = $flow_detail[0]->Present_State;

        $document_no = $flow_detail[0]->Document_No;
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

//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "transferStock"); // Button Permission. Add by Ton! 20140131|

        $order_data = $this->transferStock->queryDataFromFlowId($flow_id);

        foreach ($order_data as $OrderObject) {
            $order_data_value = $OrderObject;
        }
        //p($order_data_value);
        $order_detail_data = $this->transferStock->queryDataFromOrderDetailId($order_data_value->Order_Id);
        $order_detail_data = $order_detail_data->result(); // Add By Akkarapol, 21/10/2013, เซ็ตค่าตัวแปรใหม่ เพราะค่าที่ Return มาจากฟังก์ชั่น queryDataFromOrderDetailId นั้นส่งมาเป็นแบบ $query เลย เพื่อให้รองรับการเรียกใช้งานที่หลากหลายขึ้น

        foreach ($order_detail_data as $OrderDetailObject) {
            $order_detail_data_value = $OrderDetailObject;
        }
//        p($order_detail_data);
        //--------------End of Query From Order and Order Detail-----------------------//

        $to_warehouse_opt = array("-1" => "--Please Select--");
        $frm_warehouse_opt_key = $this->transferStock->selectWarehouse();
        foreach ($frm_warehouse_opt_key as $key => $value) {
            $data[$value->Warehouse_Id] = $value->Warehouse_NameEN;
        }
        //from & to warehouse
        $frm_warehouse_opt = $data;
        $to_warehouse_opt = $data;

        $this->load->model("company_model", "company");
        $this->load->model("system_management_model", "sys");


        #2395 
        #by kik (12-09-2013)
        #เพิ่มการค้นหาด้วย product status และ sub status
        #Start new code
        #==========================================================
        #Get Product Status	
        $q_product_status = $this->p->selectProductStatus();
        $r_product_status = $q_product_status->result();
//        $product_status_list = genOptionDropdown($r_product_status, "SYS");
        $product_status_list = genOptionDropdown($r_product_status, "SYS", FALSE, TRUE); // Edit by kik! 20131107
        $productStatus_select = form_dropdown("productStatus_select", $product_status_list, "", "id=productStatus_select");

        #Get Product Sub Status	
        $q_product_Substatus = $this->p->selectSubStatus();
        $r_product_Substatus = $q_product_Substatus->result();
//        $product_Substatus_list = genOptionDropdown($r_product_Substatus, "SYS");
        $product_Substatus_list = genOptionDropdown($r_product_Substatus, "SYS", FALSE, TRUE); // Edit by kik! 20131107
        $productSubStatus_select = form_dropdown("productSubStatus_select", $product_Substatus_list, "", "id=productSubStatus_select");

        #End new code
        #==========================================================
        #Get Renter [Company Renter] list		
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
//        $renter_list = genOptionDropdown($r_renter, "COMPANY");
        $renter_list = genOptionDropdown($r_renter, "COMPANY", TRUE, FALSE); // Edit by kik! 20131107
        #Get Shipper[Company Supplier] list
        $q_shipper = $this->company->getOwnerAll(); // Edit BY Akkarapol, 19/11/2013, shipper มันต้องเป็น owner เท่านั้นไม่ใช่ supplier
        $r_shipper = $q_shipper->result();
//        $shipper_list = genOptionDropdown($r_shipper, "COMPANY");
        $shipper_list = genOptionDropdown($r_shipper, "COMPANY", TRUE, FALSE); // Edit by kik! 20131107
        #comment by kik (16-09-2013)
        #Get Consignee[Company Owner]  list
//        $q_consignee = $this->company->getOwnerAll();
//        $r_consignee = $q_consignee->result();
//        $consignee_list = genOptionDropdown($r_consignee, "COMPANY");
        #Get Consignee[Company Owner]  list
//        $q_consignee = $this->company->getNotOwner( $this->session->userdata('branch_id'));
        // $q_consignee = $this->company->getNotOwnerAll();        
//        $r_consignee = $q_consignee->result();
//        $consignee_list = genOptionDropdown($r_consignee, "COMPANY");
        #add by kik (16-09-2013)
        $q_consignee = $this->company->getBranchNotOwner($this->session->userdata('branch_id')); // Add By Akkarapol, 12/09/2013, เพิ่มการ get ข้อมูลของ branch ที่ไม่ใช่ของตนเองเข้ามาเพื่อนำไปใส่ที่ Consignee
        $r_consignee = $q_consignee->result();
//        $consignee_list = genOptionDropdown($r_consignee, "COMPANY");
        $consignee_list = genOptionDropdown($r_consignee, "COMPANY", TRUE, FALSE); // Edit by kik! 20131107
        #end add by kik (16-09-2013)
        #Get Receive Type list			
        $dispatch_type = $this->sys->getTransferType(); #edit from getDispatchTypeByCondition to getTransferType by kik : 14-10-2013
//        $dispatch_type_list = genOptionDropdown($dispatch_type, "SYS");
        $dispatch_type_list = genOptionDropdown($dispatch_type, "SYS", TRUE, TRUE); // Edit by kik! 20131107

        $renter_id_select = form_dropdown("renter_id_select", $renter_list, $order_data_value->Renter_Id, "id=renter_id_select class=required");
        $frm_warehouse_select = form_dropdown("frm_warehouse_select", $shipper_list, $order_data_value->Source_Id, "id=frm_warehouse_select class=required");
        $to_warehouse_select = form_dropdown("to_warehouse_select", $consignee_list, $order_data_value->Destination_Id, "id=to_warehouse_select class=required");
        $dispatch_type_select = form_dropdown("dispatch_type_select", $dispatch_type_list, $order_data_value->Doc_Type, "id=dispatch_type_select class=required");

        $query = $this->transferStock->getTransferStockAll();
        $pre_dispatch_list = $query->result();

        #start add code for ISSUE 3320 :  by kik : 20140116
        $price_per_unit_table = '';
        if ($this->settings['price_per_unit'] == TRUE):
            $price_per_unit_table = ',null
                    ,null
                    ,null';
        else:
            $price_per_unit_table = "  , {
                    onblur: 'submit',
                    sUpdateURL: '" . site_url() . '/transferStock/saveEditedRecord' . "',
                    event: 'click',
                }
                ,null
                ,null";

        endif;
        #end add code for ISSUE 3320 :  by kik : 20140116

        $str_form = $this->parser->parse('form/transferStockFormWithData', array("parameter" => form_input('warehouse_form', 'whs_form')
            , "test_parse" => "test pass parse from controller"
            , "renter_id_select" => $renter_id_select
            , "frm_warehouse_select" => $frm_warehouse_select
            , "to_warehouse_select" => $to_warehouse_select
            , "dispatch_type_select" => $dispatch_type_select
            , "process_id" => $process_id
            , "present_state" => $present_state
            , "DocNo" => $document_no
            , "doc_refer_ext" => $Doc_Refer_Ext
            , "doc_refer_int" => $Doc_Refer_Int
            , "doc_refer_inv" => $Doc_Refer_Inv
            , "doc_refer_ce" => $Doc_Refer_CE
            , "doc_refer_bl" => $Doc_Refer_BL
            , "order_detail_data" => $order_detail_data
            , "remark" => $remark
            , "est_action_date" => $est_dispatch_date
            , "flow_id" => $flow_id
            , "order_id" => $order_id                               // add by kik(29-01-2014)
            , "price_per_unit" => $this->settings['price_per_unit']  //add by kik : 20140115
            , "is_urgent" => $is_urgent
            , "token" => register_token($flow_id, $present_state, $process_id)
                )
                , TRUE);

        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'price_per_unit_table' => $price_per_unit_table
            // Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPreDispatch"></i>'
            // END Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'productStatus_select' => $productStatus_select // add by kik : 12-09-2013
            , 'productSubStatus_select' => $productSubStatus_select // add by kik : 12-09-2013
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_action' => $data_form->str_buttun
            , 'user_login' => $this->session->userdata('username')
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
        ));
    }

    public function transferStockList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit


        $module = "transferStock";

        $query = $this->flow->getWorkFlowByModule($module);
        $receive_list = $query->result();
        //p($this->conv->tis620_to_utf8($receive_list));
        // $receive_list = $this->conv->tis620_to_utf8($receive_list);
        $column = array("Flow ID", "State Name", "Reference External", "Reference Internal", "Document No.", "Process Day"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow);
        $action = array(VIEW, DEL);
        
        // Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย        
        if(($key = array_search(DEL, $action)) !== false) {
            unset($action[$key]);
        }
        // END Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย
         
        $datatable = $this->datatable->genTableFixColumn($query, $receive_list, $column, "transferStock/transferStockFormWithData/", $action, "transferStock/rejectAction");
        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Transfer Stock Document'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
			 ONCLICK=\"openForm('transferStock','transferStock/transferStockForm/','A','')\">"
        ));
    }

    public function opentransferStock() {

        # Parameter Index Datatable
        $post = $this->input->post();
        $ci_item_id = $this->input->post("ci_item_id"); //add for edit No. to running number : by kik : 20140226
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_product_status = $this->input->post("ci_product_status");
        $ci_product_lot = $this->input->post("ci_product_lot");
        $ci_product_serial = $this->input->post("ci_product_serial");
        $ci_mfd = $this->input->post("ci_mfd");
        $ci_exp = $this->input->post("ci_exp");
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_remark = $this->input->post("ci_remark");
        $ci_location_code = $this->input->post("ci_location_code");
        $ci_product_sub_status = $this->input->post("ci_product_sub_status");
        $ci_unit_Id = $this->input->post("ci_unit_Id");

        # Parameter Index Datatable for PRICE PER UNIT
        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }

        # Parameter Item list in dataTable
        $prod_list = $this->input->post("prod_list");

        # Check validate data
        $return = array();

        # Check Estimate balance
        $resultCompare = $this->balance->_chkPDreservBeforeOpen($ci_reserv_qty, $ci_inbound_id, $prod_list, SEPARATOR);
        $return = array_merge_recursive($return, $resultCompare);
        if (!empty($resultCompare['critical'])):
        /*
         * LOG MSG 01
         */
        endif;

        # Check Re Order Point
        $chk_re_order_point = $this->balance->chk_re_order_point($ci_reserv_qty, $ci_prod_code, $prod_list, SEPARATOR);
        $return = array_merge_recursive($return, $chk_re_order_point);
        if (!empty($resultCompare['critical'])):
        /*
         * LOG MSG 02
         */
        endif;

        #หากมี error ที่ไม่สามารถปล่อยผ่านได้ จะ return กลับไปยังหน้า view เพื่อบังคับให้แก้ไขข้อมูลดังกล่าว
        if (!empty($return['critical'])) :

            $json['status'] = "validation";
            $json['return_val'] = $return;

        else:

            $check_not_err = TRUE; //for check error all process , if process error set value = FALSE
            # Parameter Order adjust header
            $renter_id_select = $post["renter_id_select"];
            $warehouse_from = $post["frm_warehouse_select"];
            $warehouse_to = $post["to_warehouse_select"];
            $dispatch_type_select = $post["dispatch_type_select"];
            $process_id = $this->input->post("process_id");
            $present_state = $this->input->post("present_state");
            $action_type = $this->input->post("action_type");
            $next_state = $this->input->post("next_state");
            $user_id = $this->session->userdata("user_id");
            $remark = $this->input->post("remark");

            $is_urgent = $this->input->post("is_urgent");
            if ($is_urgent != ACTIVE) {
                $is_urgent = INACTIVE;
            }

            # Parameter of document number
            $doc_refer_int = strtoupper($this->input->post("doc_refer_int"));
            $doc_refer_ext = strtoupper($this->input->post("doc_refer_ext"));
            $doc_refer_inv = strtoupper($this->input->post("doc_refer_inv"));
            $doc_refer_ce = strtoupper($this->input->post("doc_refer_ce"));
            $doc_refer_bl = strtoupper($this->input->post("doc_refer_bl"));

            $estDispatchDate = convertDate($post["estDispatchDate"], "eng", "iso", "-");

            # ================== Start save data =========================

            $this->transaction_db->transaction_start();

            # Generate Transfer Document No.
            if ($check_not_err):
                $prefix = "TO";
//                $document_no = createDocumentNo($prefix);
                $document_no = create_document_no_by_type($prefix); // Add by Ton! 20140428
                if ($document_no == "" || empty($document_no)) {
                    $check_not_err = FALSE;

                    /*
                     * LOG MSG 1
                     */
                    $return['critical'][]['message'] = "Can not create Document No.";
                }
            endif;

            # Flow - gen new flow
            if ($check_not_err):

                $order = array(
                    'Document_No' => $document_no
                    , 'Doc_Refer_Ext' => $doc_refer_ext
                    , 'Doc_Refer_Int' => $doc_refer_int
                    , 'Doc_Refer_Inv' => $doc_refer_inv
                    , 'Doc_Refer_CE' => $doc_refer_ce
                    , 'Doc_Refer_BL' => $doc_refer_bl
                    , 'Doc_Type' => $dispatch_type_select
                    , 'Renter_Id' => $renter_id_select
                    , 'Estimate_Action_Date' => $estDispatchDate//Delivery Date
                    , 'Source_Id' => $warehouse_from
                    , 'Destination_Id' => $warehouse_to
                    , 'Source_Type' => 'Warehouse'
                    , 'Destination_Type' => 'Customer'
                    , 'Create_By' => $user_id
                    , 'Remark' => $remark
                    , 'Create_Date' => date("Y-m-d H:i:s")
                    , 'Process_Type' => 'OUTBOUND'
                    , 'Is_urgent' => $is_urgent
                );

                $list_flow_action = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $order);
                $flow_id = $list_flow_action[0];
                $action_id = $list_flow_action[1];

                if ($flow_id == "" || $action_id == "") {
                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 2
                     */
                    $return['critical'][]['message'] = "Can not add new work flow.";
                } else {
                    $order['Flow_Id'] = $flow_id;
                }

            endif;  # end - gen new flow
            # Start create new Order and Order Detail
            # Save new order
            if ($check_not_err):
                $order_id = $this->stock->addOrder($order);
                if ($order_id == "" || empty($order_id)) {
                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 3
                     */
                    $return['critical'][]['message'] = "Can not add new order";
                }
            endif;

            # Order Detail  - Set data and Save data into table
            if ($check_not_err):

                # add data into order detail
                $order_detail = array();

                if (!empty($prod_list)) {

                    foreach ($prod_list as $rows) {
                        $detail = array();
                        $a_data = explode(SEPARATOR, $rows);

                        $detail['Order_Id'] = $order_id;
                        $detail['Product_Id'] = $this->p->getProductIDByProdCode($a_data[$ci_prod_code]);
                        $detail['Product_Code'] = $a_data[$ci_prod_code];
                        $detail['Product_Status'] = $a_data[$ci_product_status];
                        $detail['Product_Sub_Status'] = $a_data[$ci_product_sub_status];
                        $detail['Product_Lot'] = $a_data[$ci_product_lot];
                        $detail['Product_Serial'] = $a_data[$ci_product_serial];
                        $detail['Product_Mfd'] = $a_data[$ci_mfd];
                        $detail['Product_Exp'] = $a_data[$ci_exp];
                        $detail['Reserv_Qty'] = $a_data[$ci_reserv_qty];
                        $detail['Inbound_Item_Id'] = $a_data[$ci_inbound_id];
                        $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                        $detail['Unit_Id'] = $a_data[$ci_unit_Id];

                        #set price per uint
                        if ($this->settings['price_per_unit'] == TRUE) {
                            $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]);
                            $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                            $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]);
                        }

                        #set Date format
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

                        # Set Location Id
                        if ($a_data[$ci_location_code] != "") {
                            $locateID = $this->loc->getLocationIdByCode($a_data[$ci_location_code]);
                            if ($locateID != "") {
                                $detail['Suggest_Location_Id'] = $locateID;
                            } else {
                                $detail['Suggest_Location_Id'] = NULL;
                            }
                        } else {
                            $detail['Suggest_Location_Id'] = NULL;
                        }

                        $order_detail[] = $detail;
                    }//foreach ($prod_list as $rows)


                    if (!empty($order_detail)):

                        #add order detail
                        $result_order_detail = $this->stock->addOrderDetail($order_detail);
                        if ($result_order_detail <= 0):
                            $check_not_err = FALSE;
                            /*
                             * LOG MSG 4
                             */

                            $return['critical'][]['message'] = "Can not add order detail";
                        else:

                            $order_detail_data = $this->stock->getOrderDetailByOrderId($order_id);

                            if (empty($order_detail_data)):
                                $check_not_err = FALSE;
                                /*
                                 * LOG MSG 5
                                 */
                                $return['critical'][]['message'] = "Can not get data order detail";
                            endif;

                        endif; //if(!empty($order_detail))
                        #update pd reserv into inbound
                        $result_PD_reserv_qty = $this->stock->reservPDReservQtyArray($order_detail_data);
                        if (!$result_PD_reserv_qty):
                            $check_not_err = FALSE;
                            /*
                             * LOG MSG 6
                             */
                            $return['critical'][]['message'] = "Can not update QTY in Inbound table";
                        endif;


                    endif; // if(!empty($order_detail))
                }//if (!empty($prod_list))

            endif; //end Set data and Save Order Detail Table
            #Set Message Alert
            if ($check_not_err):

                $array_return['success'][]['message'] = "Save Transfer Stock Complete";
                $json['return_val'] = $array_return;

                $this->transaction_db->transaction_end();

            else:

                $array_return['critical'][]['message'] = "Save Transfer Stock Incomplete.";
                $json['return_val'] = $array_return;

                $this->transaction_db->transaction_rollback();

            endif; //end set message alert

            $json['status'] = "save";

        endif; //if (!empty($return['critical']))

        echo json_encode($json);
    }

    public function confirmTransferStock() {

        #Variable for check error
        $check_not_err = TRUE;

        # Parameter Form
        $token = $this->input->post('token');
        $flow_id = $this->input->post("flow_id");
        $flow_detail = $this->wf->getFlowDetail($flow_id, 'STK_T_Order');
        $order_id = $flow_detail[0]->Order_Id;
        $prod_list = $this->input->post("prod_list");

        # Parameter Index Datatable
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_inbound_id = $this->input->post("ci_inbound_id");

        $process_id = $flow_detail['0']->Process_Id;
        $present_state = $flow_detail['0']->Present_State;

        // validate token
        $response = validate_token($token, $flow_id, $present_state, $process_id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;

        # Check validate data
        $return = array();



        # ================== Start save data =========================       

        $this->transaction_db->transaction_start();


        if ($check_not_err):
            $status = $this->chk_update_orderDetail($prod_list, $ci_item_id, $ci_inbound_id, $ci_reserv_qty);
            if (!$status):
                $check_not_err = FALSE;

                /**
                 * 1.Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update inbound data in order detail.";

            endif;

        endif;

        # Check Estimate balance And Re Order Point
        if ($check_not_err):

            # Check Estimate balance
            $resultCompare = $this->balance->_chkPDreservBeforeApprove($ci_reserv_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Order_Detail');
            $return = array_merge_recursive($return, $resultCompare);
            if (!empty($resultCompare['critical'])):
                $check_not_err = FALSE;

            /**
             * 2.Set Alert Zone (set Error Code, Message, etc.)
             */
            endif;

            # Check Re Order Point
            $chk_re_order_point = $this->balance->chk_re_order_point_before_approve($ci_reserv_qty, $ci_prod_code, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Order_Detail');
            $return = array_merge_recursive($return, $chk_re_order_point);
            if (!empty($chk_re_order_point['critical'])):
                $check_not_err = FALSE;

            /**
             * 3.Set Alert Zone (set Error Code, Message, etc.)
             */
            endif;

        endif; //end check estimate balance and re-order point
        # Update Process 
        if ($check_not_err):

            $respond = $this->_updateProcess($this->input->post());

            if (!empty($respond['critical'])) :
                $check_not_err = FALSE;

                /**
                 * 4.Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update process";
                $return = array_merge_recursive($return, $respond);
            endif;

        endif;

        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):

            $set_return['message'] = "Confirm Transfer Stock Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;

            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();


        else:

            $array_return['critical'][]['message'] = "Confirm Transfer Stock Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);

            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

        endif;

        echo json_encode($json);
    }

    public function approveTransferStock() {

        $check_not_err = TRUE;

        # Parameter Form
        $token = $this->input->post('token');
        $flow_id = $this->input->post("flow_id");
        $flow_detail = $this->wf->getFlowDetail($flow_id, 'STK_T_Order');
        $order_id = $flow_detail[0]->Order_Id;
        $prod_list = $this->input->post("prod_list");

        # Parameter Index Datatable
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_prod_code = $this->input->post("ci_prod_code");

        $process_id = $flow_detail['0']->Process_Id;
        $present_state = $flow_detail['0']->Present_State;

        // validate token
        $response = validate_token($token, $flow_id, $present_state, $process_id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;

        /**
         * set Variable
         */
        $return = array();

        /**
         * ================== Start Transaction =========================
         */
        $this->transaction_db->transaction_start();

        #for check data not have inbound id and update order detail form data inbound table 
        if ($check_not_err):
            $status = $this->chk_update_orderDetail($prod_list, $ci_item_id, $ci_inbound_id, $ci_reserv_qty);
            if (!$status):
                $check_not_err = FALSE;

                /**
                 * 1.Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update inbound data in order detail.";

            endif;
        endif;

        #Check validate Est.Balance and ReOrderPoint
        if ($check_not_err):
            $resultCompare = $this->balance->_chkPDreservBeforeApprove($ci_reserv_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Order_Detail');
            $return = array_merge_recursive($return, $resultCompare);
            if (!empty($resultCompare['critical'])):
                $check_not_err = FALSE;

            /**
             * 2.Set Alert Zone (set Error Code, Message, etc.)
             */
            endif;

            $chk_re_order_point = $this->balance->chk_re_order_point_before_approve($ci_reserv_qty, $ci_prod_code, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Order_Detail');
            $return = array_merge_recursive($return, $chk_re_order_point);
            if (!empty($chk_re_order_point['critical'])):
                $check_not_err = FALSE;

            /**
             * 3.Set Alert Zone (set Error Code, Message, etc.)
             */
            endif;
        endif;


        #updateProcess
        if ($check_not_err):
            $respond = $this->_updateProcess($this->input->post());

            if (!empty($respond['critical'])) :
                $check_not_err = FALSE;

                /**
                 * 4.Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update Process.";
                $return = array_merge_recursive($return, $respond);
            endif;
        endif;


        #check if for return json and set transaction
        if ($check_not_err):

            $set_return['message'] = "Approve Transfer Stock Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;

            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

        else:

            $array_return['critical'][]['message'] = "Approve Transfer Stock Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);

            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

        endif;

        echo json_encode($json);
    }

    # add function chk_update_orderDetail for check data not have inbound id and update order detail form data inbound table 
    # by kik : 20140129

    function chk_update_orderDetail($prod_list, $ci_item_id, $ci_inbound_id, $ci_reserv_qty) {


        //###START BY POR 2013-10-24 เพิ่มให้ update inbound_item_id ในตาราง order_detail ใน item_id ที่ยังไม่มี inbound_item_id 
        //update record ที่ยังไม่มี  inbound_item_id โดยเรียก function updateInboundItemId และส่งค่า inbound_id และ item_id ที่ได้เข้า function ด้วย

        $chk_status = TRUE;

        foreach ($prod_list as $rows) :
            $a_data = explode(SEPARATOR, $rows);
            $is_new = $a_data[$ci_item_id];

            if ("new" != $is_new) {

                //+++++เตรียมข้อมูลที่ต้องการดึงขึ้นมาจากตาราง inbound
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


                //+++++เตรียมเงื่อนไขในการค้นหาข้อมูลจากตาราง inbound

                $where['Inbound_Id'] = $a_data[$ci_inbound_id];

                //+++++นำข้อมูลที่เตรียมไปค้นหาข้อมูลออกมา
                $detailForUpdate = $this->stock->getStockDetailId($column, $where)->result();

                if (!empty($detailForUpdate)):

                    unset($where); //clear $where เพื่อนำไปใช้ต่อไป
                    //+++++เตรียมข้อมูลเพื่อนำไป update ในตาราง order_detail
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


                    //+++++เตรียมเงื่อนไขเพื่อนำไปใช้ในการ update order_detail

                    $where['Item_Id'] = $a_data[$ci_item_id];
                    $where_detail = 'Inbound_Item_Id IS NULL';


                    //END ADD
                    # by kik : 2013-11-14
                    # updateInboundItemId return data 3 case 
                    # 0 : can update data > 0 record. update success
                    # 1 : not have updata data because does not meet the conditions.
                    # 2 : update unsuccess.
                    $upinbound = $this->preDispModel->updateInboundItemId($detail, $where, $where_detail);

                    unset($where); //clear $where เพื่อนำไปใช้ต่อไป

                    if ($upinbound == 0) :  # ในกรณีที่ order detail นั้นหา inbound เจอ และ update ข้อมูลสำเร็จแล้ว จะต้องทำการจอง qty ที่จะใช้ในการ dispatch ด้วย

                        $result_Update_qty = $this->stock->reservPDReservQty($a_data[$ci_inbound_id], $a_data[$ci_reserv_qty], "+");
                        if (!$result_Update_qty):

                            $chk_status = FALSE;

                            /**
                             * 1. Set Alert Zone (set Error Code, Message, etc.)
                             */
                            break;

                        endif;

                    elseif ($upinbound == 2):  # ในกรณีที่ update data ไม่ผ่าน จะบวกค่าเข้าไปในตัวแปร status เพื่อ return กลับไปให้ยัง function หลัก ใช้แสดงผล error ให้ user ทราบต่อไป

                        $chk_status = FALSE;
                        /**
                         * 2. Set Alert Zone (set Error Code, Message, etc.)
                         */
                        break;

                    endif;
                else:
                    # not have inbound data 

                    $chk_status = FALSE;

                    /**
                     * 3.Set Alert Zone (set Error Code, Message, etc.)
                     */
                    break;

                endif; //end check have inbound data
            }

        endforeach;

        return $chk_status;
        //###END
    }

    #End New Comment Code : 20140129
    #ISSUE 2232 Transfer Stock
    #DATE:2013-10-08
    #BY:KIK
    #รวมโปรเซสการ update data ตอน confirm & approve transfer stock จากเดิมที่เขียนแยกกันไว้
    #START New Comment Code #ISSUE 2232
    #=======================================================================================

    public function _updateProcess() {

        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;

        #====================== Start Get Parameter ============================
        # Parameter Form
        $flow_id = $this->input->post("flow_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->input->post("user_id");
        $prod_list = $this->input->post("prod_list");
        $prod_del_list = $this->input->post("prod_del_list");
        $order_detail_data = $this->input->post("order_detail_data");
        $estDispatchDate = $this->input->post("estDispatchDate");
        $is_urgent = $this->input->post("is_urgent");
        $dispatch_type = $this->input->post("dispatch_type_select");
        $renter_id = $this->input->post("renter_id_select");
        $warehouse_from = $this->input->post("frm_warehouse_select");
        $warehouse_to = $this->input->post("to_warehouse_select");
        $remark = $this->input->post("remark");

        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Parameter of document number
        $document_no = $this->input->post("Document_no");
        $doc_refer_int = $this->input->post("doc_refer_int");
        $doc_refer_ext = $this->input->post("doc_refer_ext");
        $doc_refer_inv = $this->input->post("doc_refer_inv");
        $doc_refer_ce = $this->input->post("doc_refer_ce");
        $doc_refer_bl = $this->input->post("doc_refer_bl");

        # Parameter Index Datatable
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_product_id = $this->input->post("ci_prod_code");
        $ci_product_status = $this->input->post("ci_product_status");
        $ci_product_lot = $this->input->post("ci_product_lot");
        $ci_product_serial = $this->input->post("ci_product_serial");
        $ci_product_Mfd = $this->input->post("ci_mfd");
        $ci_product_exp = $this->input->post("ci_exp");
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_product_remark = $this->input->post("ci_remark");
        $ci_Suggest_Location_Id = $this->input->post("ci_location_code");
        $ci_product_sub_status = $this->input->post("ci_product_sub_status");
        $ci_product_unit_id = $this->input->post("ci_unit_Id");
        $ci_inbound_id = $this->input->post("ci_inbound_id");

        # Parameter Price per uint
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

        # Get workflow
        if ($check_not_err):

            $flow_detail = $this->wf->getFlowDetail($flow_id, 'STK_T_Order');

            #check not empty data in workflow
            if (empty($flow_detail)):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not get flow detail in Order table";
            /*
             * LOG MSG 2
             */
            else:
                $document_no = $flow_detail[0]->Document_No;
                $order_id = $flow_detail[0]->Order_Id;
                $data['Document_No'] = $document_no;
            endif;

        endif;

        # Update Workflow
        if ($check_not_err):
            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
            if ($action_id == "" || empty($action_id)):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update workflow.";
            /*
             * LOG MSG 3
             */
            endif;
        endif;

        #update Order Table
        if ($check_not_err):
            $order = array(
                'Doc_Refer_Ext' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ext))
                , 'Doc_Refer_Int' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_int))
                , 'Doc_Refer_Inv' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_inv))
                , 'Doc_Refer_CE' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ce))
                , 'Doc_Refer_BL' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_bl))
                , 'Doc_Type' => $dispatch_type
                , 'Renter_Id' => $renter_id
                , 'Estimate_Action_Date' => $estDispatchDate
                , 'Source_Id' => $warehouse_from
                , 'Destination_Id' => $warehouse_to
                , 'Source_Type' => 'Warehouse'
                , 'Destination_Type' => 'Warehouse'
                , 'Modified_By' => $user_id
                , 'Modified_Date' => date("Y-m-d H:i:s")
                , 'Remark' => $remark
                , 'Is_urgent' => $is_urgent
            );

            $where['Flow_Id'] = $flow_id;

            #update order
            $result_order_query = $this->stock->updateOrder($order, $where);

            if (!$result_order_query):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update Order.";
            /*
             * LOG MSG 4
             */
            endif;

        endif;


        #update Order Detail Table
        if ($check_not_err):

            $order_detail = array();

            if (!empty($prod_list)) {

                foreach ($prod_list as $rows) {

                    #============ Set data for Insert or Update data ===============
                    $a_data = explode(SEPARATOR, $rows);

                    $is_new = $a_data[$ci_item_id];
                    $detail = array();
                    $detail['Product_Id'] = $this->p->getProductIDByProdCode($a_data[$ci_product_id]);
                    $detail['Product_Code'] = $a_data[$ci_product_id];
                    $detail['Reserv_Qty'] = $a_data[$ci_reserv_qty];
                    $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_product_remark]);
                    $detail['Inbound_Item_Id'] = $a_data[$ci_inbound_id];
                    $detail['Unit_Id'] = $a_data[$ci_product_unit_id];

                    //ADD BY KIK 2014-01-14 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                    if ($this->settings['price_per_unit'] == TRUE) {
                        //+++++เตรียมข้อมูลที่ต้องการดึงขึ้นมาจากตาราง inbound
                        $columnInb = array("Price_Per_Unit", "Unit_Price_Id");
                        //+++++เตรียมเงื่อนไขในการค้นหาข้อมูลจากตาราง inbound
                        $whereFindInb['Inbound_Id'] = $a_data[$ci_inbound_id];
                        //+++++นำข้อมูลที่เตรียมไปค้นหาข้อมูลออกมา
                        $detailForUpdate = $this->stock->getStockDetailId($columnInb, $whereFindInb)->result();

                        if (!empty($detailForUpdate)):
                            $detail['Price_Per_Unit'] = $detailForUpdate[0]->Price_Per_Unit; //+++++ADD BY KIK 2014-01-14 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                            $detail['Unit_Price_Id'] = $detailForUpdate[0]->Unit_Price_Id;
                            $detail['All_Price'] = $detailForUpdate[0]->Price_Per_Unit * str_replace(",", "", $detail['Reserv_Qty']); //+++++ADD BY KIK 2014-01-14 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                        endif;
                    }
                    //END ADD
                    #------------------- end Set data for Insert or Update data ------------------
                    #Update Order Detail for old Item
                    if ("new" != $is_new) {
                        unset($where);
                        $where['Item_Id'] = $is_new;
                        $where['Order_Id'] = $order_id;
                        $where['Product_Code'] = $detail['Product_Code'];


                        # by kik , 08-10-2013
                        # หลักการ update PD reserv ใน inbound กรณีที่ เคยเพิ่มค่าเข้าไปแล้ว
                        # 1.เช็คค่า reserv_qty ใน order detail ว่า ค่าที่รับมาใหม่ มีค่าเท่ากับที่เคย add เข้าไปแล้วหรือไม่ 
                        # 2.ถ้าใช่ ให้อัพเดตตามปกติ ไม่มีการเปลี่ยนแปลงค่าใน PD_reserv ใน inbound
                        # 3.ถ้าไม่ใช่ ให้ลบ PD_reserv ใน inbound ออก ตามจำนวนเก่าที่ดึงมาจาก orderDetail ก่อนที่จะอัพเดตค่าให้เข้าไปในตาราง 
                        # 4.และบวกค่า reservQty ใหม่เข้าไปใน PD_reserv ของ inbound เพื่อให้ค่าเป็นปัจจุบันที่สุด

                        $oldReservQty = $this->stock->getOldReservQtyOrderDetailByItemId($is_new);
                        if ($oldReservQty['Reserv_Qty'] != $detail['Reserv_Qty']) {//(1)
                            if ($check_not_err):
                                $result_Del_PDrervqty = $this->stock->reservPDReservQty($detail['Inbound_Item_Id'], $oldReservQty['Reserv_Qty'], "-"); //(3)
                                if ($result_Del_PDrervqty < 1):
                                    $check_not_err = FALSE;
                                    $return['critical'][]['message'] = "Can not update quantity balance.";
                                /*
                                 * LOG MSG 5
                                 */
                                endif;
                            endif;

                            if ($check_not_err):
                                $result_Pos_PDrervqty = $this->stock->reservPDReservQty($detail['Inbound_Item_Id'], $detail['Reserv_Qty'], "+"); //(4)
                                if ($result_Pos_PDrervqty < 1):
                                    $check_not_err = FALSE;
                                    $return['critical'][]['message'] = "Can not update quantity balance.";
                                /*
                                 * LOG MSG 6
                                 */
                                endif;
                            endif;
                        }
                        //(2)

                        unset($oldReservQty);

                        if ($check_not_err):

                            $result_orderDetail_query = $this->stock->updateOrderDetail($detail, $where);

                            if (!$result_orderDetail_query):
                                $check_not_err = FALSE;
                                $return['critical'][]['message'] = "Can not update Order detail.";
                            /*
                             * LOG MSG 7
                             */
                            endif;

                        endif;
                    } else {
                        #Add Order Detail for new Item    
                        #edit by kik (16-09-2013)
                        $detail['Product_Status'] = $a_data[$ci_product_status];
                        $detail['Product_Sub_Status'] = $a_data[$ci_product_sub_status];
                        $detail['Product_Lot'] = $a_data[$ci_product_lot];
                        $detail['Product_Serial'] = $a_data[$ci_product_serial];
                        $detail['Suggest_Location_Id'] = $a_data[$ci_Suggest_Location_Id];
                        if ($a_data[$ci_product_Mfd] != "") {
                            $detail['Product_Mfd'] = convertDate($a_data[$ci_product_Mfd], "eng", "iso", "-");
                        } else {
                            $detail['Product_Mfd'] = null;
                        }
                        if ($a_data[$ci_product_exp] != "") {
                            $detail['Product_Exp'] = convertDate($a_data[$ci_product_exp], "eng", "iso", "-");
                        } else {
                            $detail['Product_Exp'] = null;
                        }

                        // add by kik for insert Suggest_Location_Id (08-10-2013)
                        if ($detail['Suggest_Location_Id'] != "") {
                            $locateID = $this->loc->getLocationIdByCode($detail['Suggest_Location_Id']);
                            if ($locateID != "") {
                                $detail['Suggest_Location_Id'] = $locateID;
                            } else {
                                $detail['Suggest_Location_Id'] = NULL;
                            }
                        } else {
                            $detail['Suggest_Location_Id'] = NULL;
                        }
                        // end insert Suggest_localtion_id

                        $detail['Order_Id'] = $order_id;
                        $order_detail[] = $detail;
                    }
                }

                #add new Order Detail
                if ($check_not_err):
                    if (!empty($order_detail)):

                        #add order detail
                        $result_order_detail = $this->stock->addOrderDetail($order_detail);
                        if ($result_order_detail <= 0):
                            $check_not_err = FALSE;
                            $return['critical'][]['message'] = "Can not add new order detail.";
                        /*
                         * LOG MSG 8
                         */
                        endif;

                        #update pd reserv into inbound
                        $result_PD_reserv_qty = $this->stock->reservPDReservQtyArray($order_detail);
                        if (!$result_PD_reserv_qty):
                            $check_not_err = FALSE;
                            $return['critical'][]['message'] = "Can not update quantity balance.";
                        /*
                         * LOG MSG 9
                         */
                        endif;

                        unset($result_PD_reserv_qty);

                    endif; //end add new order detail

                endif; //end check err before add new order detail
            }//if (!empty($prod_list))
            #Delete Item in Order Detail
            if ($check_not_err):

                if (is_array($prod_del_list) && !empty($prod_del_list)) {
                    unset($rows);
                    unset($detail);
                    $item_delete = array();

                    #Update PD_Reserv_Qty in Inbound Table
                    foreach ($prod_del_list as $rows) {
                        $a_data = explode(SEPARATOR, $rows); // Edit By Akkarapol, 21/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้

                        $item_delete[] = $a_data[$ci_item_id];  /* Item_Id for Delete in STK_T_Order_Detail  */

                        if (!empty($a_data[$ci_inbound_id])) {  // add condition by kik : 20140128
                            #update pd reserv into inbound
                            $result_PD_reserv_qty = $this->stock->reservPDReservQty($a_data[$ci_inbound_id], $a_data[$ci_reserv_qty], "-");
                            if (!$result_PD_reserv_qty):
                                $check_not_err = FALSE;
                                $return['critical'][]['message'] = "Can not update quantity balance.";
                            /*
                             * LOG MSG 10
                             */
                            endif;
                        }
                    }// end update PD_Reserv_Qty in Inbound Table
                    #query Delete Item in Order Detail
                    if ($check_not_err):
                        if (!empty($a_data[$ci_inbound_id])) {  // add condition by kik : 20140128
                            $result_Del_item = $this->stock->removeOrderDetail($item_delete);
                            if (!$result_Del_item):
                                $check_not_err = FALSE;
                                $return['critical'][]['message'] = "Can not remove order detail.";
                            /*
                             * LOG MSG 11
                             */
                            endif;
                        }
                    endif; // end check err before delete item in order detail table 
                }// if (is_array($prod_del_list) && !empty($prod_del_list)) {

            endif; // end delete data

        endif; // end update order detail table
        #--------------------------- End Save Data -----------------------------
        return $return;
    }

    #End New Comment Code #ISSUE 2232
    #=======================================================================================

    public function showSelectData() {
        $post_val = $this->input->post();
        $table_name = $this->input->post('tableName');
        $string_to_explode = $post_val["post_val"];
        //$array_for_extract = explode(",",$string_to_explode);
        $query = $this->transferStock->getListTransferStockByProdCodeViaAjax($string_to_explode);

        $pre_dispatch_list = $query->result();

        $new_list = array();
        foreach ($pre_dispatch_list as $rows) {
            $rows->Est_Balance_Qty = set_number_format($rows->Est_Balance_Qty);
            $new_list[] = thai_json_encode((array) $rows);
        }
//        p($new_list);
        $json['product'] = $new_list;
        echo json_encode($json);
    }

    public function saveEditedRecord() {
        $editedValue = $_REQUEST['value'];
        //Always accepts update(return posted value)
        echo iconv("UTF-8", "TIS-620", $_REQUEST['value']);
    }

    public function sentToModel($str) {
        $result = $this->transferStock->queryFromString($str);
        return $result;
    }

    function rejectAction() {

        #Variable for check error
        $check_not_err = TRUE;

        #add condition check data from list page or form page : add by kik : 2013-12-02
        if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {

            #if data send from list page When find data in database
            if ($this->input->post('id') != "") {

                $column_workflow_ = 'Document_No,Process_Id,Present_State';
                $where_workflow_['Flow_Id'] = $this->input->post('id');
                $workflow_query = $this->wf->getWorkflowTable($column_workflow_, $where_workflow_);
                $process_id = $workflow_query[0]->Process_Id;
                $flow_id = $this->input->post('id');
                $present_state = $workflow_query[0]->Present_State;
                $action_type = 'Reject';
                $next_state = -1;
                $data['Document_No'] = $workflow_query[0]->Document_No;
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
        }

        $token = $this->input->post('token');

        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        // validate token
        $response = validate_token($token, $flow_id, $flow_info['0']->Present_State, $flow_info['0']->Process_Id);
        // End

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;

#------------------------------------------------------------
        #Check value not empty in variable
        if (empty($flow_id) || $flow_id == ""):
            $check_not_err = FALSE;

            /*
             * LOG MSG 1
             */
            $return['critical'][]['message'] = "Not have flow Id.";
        endif;

        #Get data in 
        if ($check_not_err):

            #Variable for update data
            $column_order = 'Order_Id';
            $where_order['Flow_Id'] = $flow_id;
            $query_order = $this->stock->getOrderTable($column_order, $where_order);
            if (empty($query_order) || $query_order == ""):
                $check_not_err = FALSE;
                /*
                 * LOG MSG 2
                 */
                $return['critical'][]['message'] = "Can not get order data";
            else:
                $order_id = $query_order[0]->Order_Id;
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
                 * LOG MSG 3
                 */
                $return['critical'][]['message'] = "Can not update work flow.";
            endif;

        endif;

        if ($check_not_err):

            #return qty to PD_reserv in inbound table
            $order_detail = $this->stock->getOrderDetailByOrderId($order_id);

            #update inbound qty in Inbound table
            if (!empty($order_detail)):
                $result_inboundQty_query = $this->stock->reservPDReservQtyArray($order_detail, "-");
                if (!$result_inboundQty_query):
                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 4
                     */
                    $return['critical'][]['message'] = "Can not update QTY in Inbound table.";
                endif;
            endif;

            #update order detail = N
            if ($check_not_err):
                $detail['Active'] = 'N';
                $where['Order_Id'] = $order_id;

                $result = $this->stock->updateOrderDetail($detail, $where);
                if (!$result):
                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 5
                     */
                    $return['critical'][]['message'] = "Can not update order detail table.";
                endif;
            endif;

            if ($check_not_err):

                $this->transaction_db->transaction_end();

                #if data send from list page When refesh page  : add by kik : 2013-12-02
                if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {
                    echo "<script>alert('Delete Transfer Stock Complete.');</script>";
                    redirect('transferStock', 'refresh');
                    #if data send from form page When return data to form page : add by kik : 2013-12-02 
                } else {
                    $array_return['success'][]['message'] = "Reject Adjust Stock Complete";
                    $json['return_val'] = $array_return;
                }

            else:

                $this->transaction_db->transaction_rollback();

                #if data send from list page When refesh page  : add by kik : 2013-12-02
                if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {
                    echo "<script>alert('Delete Transfer Stock not complete. Please check?');</script>";
                    redirect('transferStock', 'refresh');

                    #if data send from form page When return data to form page   : add by kik : 2013-12-02  
                } else {
                    $array_return['critical'][]['message'] = "Save Adjust Stock Incomplete";
                    $json['return_val'] = $array_return;
                }

            endif;

        else:

            $this->transaction_db->transaction_rollback();

            #if data send from list page When refesh page  : add by kik : 2013-12-02
            if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {
                echo "<script>alert('Delete Transfer Stock not complete. Please check?');</script>";
                redirect('transferStock', 'refresh');
                #if data send from form page When return data to form page : add by kik : 2013-12-02
            } else {
                $array_return['critical'][]['message'] = "Save Adjust Stock Incomplete";
                $json['return_val'] = $array_return;
            }



        endif;

        $json['status'] = "save";
        echo json_encode($json);
    }

    /**
     * @function validation_openTransferStock for work validation in Open Transfer Stock 
     * @author kik : 20140305
     * @return validation data format
     */
    public function validation_openTransferStock() {

        $data = $this->input->post();

        /**
         * set Variable
         */
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
         * chk PD_reserv Before Confirm,Approve
         */
        $resultCompare = $this->balance->_chkPDreservBeforeOpen($data['ci_reserv_qty'], $data['ci_inbound_id'], $data['prod_list'], SEPARATOR);
        $return = array_merge_recursive($return, $resultCompare);


        /**
         * chk Re order Point Before Confirm,Approve
         */
        $chk_re_order_point = $this->balance->chk_re_order_point($data['ci_reserv_qty'], $data['ci_prod_code'], $data['prod_list'], SEPARATOR);
        $return = array_merge_recursive($return, $chk_re_order_point);

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

    /**
     * @function validation_confirmTransferStock for work validation in Confirm Transfer Stock 
     * @author kik : 20140305
     * @return validation data format
     */
    public function validation_confirmTransferStock() {
        $data = $this->input->post();
        $return = $this->check_validate_data($data);

        echo json_encode($return);
    }

    /**
     * @function validation_approveTransferStock for work validation in Approve Transfer Stock 
     * @author kik : 20140305
     * @return validation data format
     */
    public function validation_approveTransferStock() {
        $data = $this->input->post();
        $return = $this->check_validate_data($data);

        echo json_encode($return);
    }

    /**
     * @function check_validate_data for work check validate data in transferStockForm and transferStockFormWithData form (use 2 fucntion >> confirm,approve transfer stock)
     * @author kik : 20140305
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
         * chk PD_reserv Before Confirm,Approve
         */
        $resultCompare = $this->balance->_chkPDreservBeforeApprove($data['ci_reserv_qty'], $data['ci_inbound_id'], $data['ci_item_id'], $data['order_id'], $data['prod_list'], SEPARATOR, 'STK_T_Order_Detail');
        $return = array_merge_recursive($return, $resultCompare);


        /**
         * chk Re order Point Before Confirm,Approve
         */
        $chk_re_order_point = $this->balance->chk_re_order_point($data['ci_reserv_qty'], $data['ci_prod_code'], $data['prod_list'], SEPARATOR);
        $return = array_merge_recursive($return, $chk_re_order_point);

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

}

?>
