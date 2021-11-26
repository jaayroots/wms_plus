<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class replenishment extends CI_Controller {

    public $settings;
//    public $pallet;

//    public function __construct() {
//        error_reporting(E_ALL);
//        parent::__construct();
//        $this->settings = native_session::retrieve();
//        $this->load->library('encrypt');
//        $this->load->library('session');
//        $this->load->library('Stock_lib');
//        $this->load->library('validate_data');
//        $this->load->helper('form');
//        $this->load->helper('util_helper');
//
////        $this->load->model("encoding_conversion", "conv");
////        $this->load->model("workflow_model", "flow");
////        $this->load->model("stock_model", "stock");
////        $this->load->model("product_model", "product");
////        $this->load->model("contact_model", "contact");
////        $this->load->model("company_model", "company");
////        $this->load->model("system_management_model", "sys");
////        $this->load->model("inbound_model", "inbound");
////        $this->load->model("container_model", "container");
////        $this->load->model("invoice_model", "invoice");
////        $this->load->model("pallet_model", "pl");
////        $this->load->model("location_model", "location");
////
//        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
//        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));
////
////        $this->load->controller('balance', 'balance');
////        $this->pallet = $this->config->item('build_pallet');
//        $this->load->model("replenishment_model", "rpm");
//        $this->load->model("product_model", "product");
////         $this->load->model("product_model", "p");
//        $this->load->model("company_model", "company");
//        $this->load->model("system_management_model", "sys");
//        $this->load->model("container_model", "container");
//        $this->load->model("contact_model", "contact");
//        $this->load->model("inbound_model", "inbound");
//        $this->load->controller('balance', 'balance');       
//        $this->load->model("encoding_conversion", "conv");
//        $this->load->model("stock_model", "stock");
//        $this->load->model("re_location_model", "rl");
//        $this->load->model("workflow_model", "flow");
//        $this->load->model("location_model", "lc");
//    
//    }
    public function __construct() {
        parent::__construct();
//        error_reporting(E_ALL);
        $this->settings = native_session::retrieve();   //add by kik : 20140115
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->library('Stock_lib');
        $this->load->helper('form');
        //$this->load->model("pre_dispatch_model", "preDispModel");
        $this->load->model("encoding_conversion", "conv");
        //$this->load->model("workflow_model", "wf");
        $this->load->model("stock_model", "stock");
        $this->load->model("product_model", "p");
        $this->load->model("contact_model", "contact");
        $this->load->model("re_location_model", "rl");
        $this->load->model("workflow_model", "flow");
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));
        $this->load->model("inbound_model", "inbound");  // add by kik(14-10-2013)
        $this->load->model("location_model", "lc");
        $this->load->model("replenishment_model", "rpm");
        
        $this->load->model("pallet_model", "pl");
    }
    
    public function index() {
       $this->flowReplenishment();
    }
    
    
    //flowPreDispatchList to flowReplenishment
    function flowReplenishment() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140131 #####
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), "replenishment/flowReplenishment");
        if (!empty($action_parmission)):
            $VIEW = array(VIEW, DEL);
        else:
            $VIEW = array();
        endif;

        // Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย
        if (($key = array_search(DEL, $VIEW)) !== false) {
            unset($VIEW[$key]);
        }
        // END Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย


        $module = "replenishment";
        $this->load->model("workflow_model", "flow");
        $query = $this->rpm->getWorkFlowReplenishment($module);
        $replenishment_list = $query->result();
        $column = array("Flow ID", "State Name", "Product Code", "To Pick-Face ","Document No.", "Process Day"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow
        $VIEW = array(VIEW, DEL);    //add parameter DEL : by kik : 2013-12-13
        $datatable = $this->datatable->genTableFixColumn($query, $replenishment_list, $column, "replenishment/openActionForm", $VIEW, "replenishment/rejectAction"); //add parameter "pre_dispatch/rejectAction" : by kik : 2013-12-13
//        $datatable = $this->datatable->genTableFixColumn($query, $replenishment_list, $column, "pre_dispatch/preDispatchFormWithData", $VIEW, "pre_dispatch/rejectAction"); //add parameter "pre_dispatch/rejectAction" : by kik : 2013-12-13
        $module = "";
        $sub_module = "";
        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Replenishment Document'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' style='margin:0 0 0 0;' VALUE='" . ADD . "'
            , 'button_add' => "<INPUT TYPE='hidden' class='button dark_blue' style='margin:0 0 0 0;' VALUE='" . ADD . "'
			 ONCLICK=\"openForm('form_name','replenishment/replenishmentForm/','A','')\" disabled>"
        ));
    } 
    
    function openActionForm() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        #Load config (add by kik : 20140723)
        $conf = $this->config->item('_xml');
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];
        $parameter['conf_pallet'] = $conf_pallet;
        
        $flow_id = $this->input->post("id");


        //echo ' f = '.$flow_id;
        #Retrive Data from Table
        $flow_detail = $this->rpm->getRelocationOrder($flow_id);
//        p($flow_detail);
        $process_id = $flow_detail[0]->Process_Id;
        $order_id = $flow_detail[0]->Order_Id;
        $present_state = $flow_detail[0]->Present_State;
        $module = $flow_detail[0]->Module;

        // validate document exist state
        $valid_state = validate_state($module);
        if ($valid_state) :
            redirect($valid_state);
        endif;

        // register token
        $parameter['token'] = register_token($flow_id, $present_state, $process_id);
        // end config token

        $parameter['process_type'] = $flow_detail[0]->Process_Type;
        $parameter['document_no'] = $flow_detail[0]->Document_No;
        $parameter['doc_relocate'] = $flow_detail[0]->Doc_Relocate;
        $parameter['doc_type'] = $flow_detail[0]->Doc_Type;
        $parameter['owner_id'] = $flow_detail[0]->Owner_Id;
        $parameter['renter_id'] = $flow_detail[0]->Renter_Id;
        $parameter['assigned_id'] = $flow_detail[0]->Assigned_Id;
        $parameter['est_action_date'] = $flow_detail[0]->Est_Action_Date;
        $parameter['act_action_date'] = (!empty($flow_detail[0]->Action_Date) ? $flow_detail[0]->Action_Date : date('d/m/Y'));
        $parameter['is_urgent'] = $flow_detail[0]->Is_urgent;

        $order_detail = $this->rpm->getReLocationProductDetail($order_id);
        //p($order_detail);
        $parameter['order_detail'] = $order_detail;
//        p($order_detail);
        #Get Renter [worker] list
        //comment by por 2013-10-02 แก้ไขให้เรียกใช้ของ Akkarapol ที่เขียนไว้ เพื่อให้นำ user_id มาใช้ แทน contact_id
        /* start comment
          $query_worker = $this->contact->getWorkerAll();
          $result_worker = $query_worker->result();
          $worker_list = genOptionDropdown($result_worker, "CONTACT");
          end comment */

        //start by por 2013-10-02 นำ function ที่ Akkarapol สร้างไว้มาใช้
        $result_worker = $this->rpm->getWorkerAllWithUserLogin()->result();
        $worker_list = genOptionDropdown($result_worker, "CONTACTWITHUSERLOGIN");
        //end
        
//        p($result_worker);
//        p($worker_list);
        $parameter['worker_list'] = $worker_list;
        $parameter['worker_id'] = $flow_detail[0]->Assigned_Id;
     
//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "replenishment"); // Button Permission. Add by Ton! 20140131|
//        p($data_form); exit;
 //       start add code for ISSUE 3320 :  by kik : 20140116
        $price_per_unit_table = '';
//comment by por 2014-03-12 ไม่สร้างในนี้แล้ว ไปสร้างในหน้า form แทน
//        if ($this->settings['price_per_unit'] == TRUE):
//            $price_per_unit_table = ',null
//                    ,null
//                    ,null';
//        endif;
        #end add code for ISSUE 3320 :  by kik : 20140116

        $show_column = array(
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
                , "location_from" => _lang('from_location')
                , "suggest_location" => _lang('suggest_location')
                , "actual_location" => _lang('actual_location')
                , "remark" => _lang('remark')
                , "Inbound"
                , "price_per_unit_id" => "Price/Unit ID"
                , "pallet_code" => _lang('pallet_code')
                , "db_type_pallet" => "DP_Type_Pallet"
                , "item_id" => "Item Id"
                , "location_form_name" => "LocationFrom Name"
                , "location_to_name" => "LocationTo Name"
                , "actual_name" => "Actual Name"
                , "del" => DEL
            ); //edit from Balance Qty  to Est. Balance Qty and add Item_Id by ki : 14-10-2013
        $parameter['show_column'] = $show_column;
        //p($data_form);
        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['flow_id'] = $flow_id;
        $parameter['process_id'] = $process_id;
        $parameter['present_state'] = $present_state;
        $parameter['data_form'] = (array) $data_form;
        $parameter['price_per_unit'] = $this->settings['price_per_unit'];                 //add by kik : 2013-12-12
        $parameter['fast_set_confirm_relocation'] = $this->settings['fast_set_confirm_relocation'];

        $parameter['editable_suggestion'] = ($present_state == 1 || $present_state == 2 ? "null" : "{\"bSearchable\": true}");
        $parameter['editable_actual'] = "{\"bSearchable\": true}";
        // Add By Akkarapol, 17/10/2013, เพิ่มปุ่ม PRINT สำหรับ Generate PDF เพื่อออกใบ Relocation Job
        
        if($present_state == 0){
             $data_form->str_buttun = '';
            $data_form->str_buttun .= '<input class="button orange" type="button" onclick="ConfirmGenarate()" value="Generate Replenishment">';
        }
//        if($present_state == 1){
//            $onclick = "onClick=\"postRequestAction('replenishment','confirmRpmProduct',this.value,'2',this)\"";
//            $data_form->str_buttun .= '<input class="button orange" type="button" onclick="'.$onclick.'" value="Confirm">'; 
//        }
//         if($present_state == 2){
//            $data_form->str_buttun .= '<input class="button orange" type="button" onclick="" value="Approve">'; 
//        }
        if($present_state > 0){
            $data_form->str_buttun .= '<input class="button dark_blue" type="button" onclick="exportFile(\'PDF\')" value="PDF">'; 
        }
        
        
        // Add By Akkarapol, 07/05/2014, เพิ่มการ get show_column_relocation_job ที่เก็บไว้ใน cookie เพื่อดึงว่า ต้องการใช้ column ไหนบ้างในการแสดงผล ซึ่งจะต้องทำการ unserialize ข้อมูลออกมาด้วย
        $this->load->helper('cookie');
        $show_column_relocation_job = get_cookie('show_column_relocation_job');
        if(empty($show_column_relocation_job)):
            $show_column_relocation_job = array(
                "no" => TRUE
                , "product_code" => TRUE
                , "product_name" => TRUE
                , "product_status" => TRUE
                , "product_sub_status" => TRUE
                , "lot" => TRUE
                , "serial" => TRUE
                , "move_qty" => TRUE
                , "confirm_qty" => TRUE
                , "price_per_unit" => TRUE
                , "unit_price" => TRUE
                , "all_price" => TRUE
                , "location_from" => TRUE
                , "suggest_location" => TRUE
                , "actual_location" => TRUE
                , "remark" => TRUE
            );
        else:
            $show_column_relocation_job = unserialize(get_cookie('show_column_relocation_job'));
        endif;
        // END Add By Akkarapol, 07/05/2014, เพิ่มการ get show_column_relocation_job ที่เก็บไว้ใน cookie เพื่อดึงว่า ต้องการใช้ column ไหนบ้างในการแสดงผล ซึ่งจะต้องทำการ unserialize ข้อมูลออกมาด้วย

//        p($show_column_relocation_job);

        $parameter['show_column_relocation_job'] = $show_column_relocation_job;

        # LOAD FORM
    //    p($data_form->form_name);  
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);
        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'price_per_unit_table' => $price_per_unit_table

            // Add By Akkarapol, 23/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_receive"></i>'
            // END Add By Akkarapol, 23/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'button_action' => $data_form->str_buttun
        ));
    }
    
    public function showAndGenSelectData() {
        $flow_id = $this->input->post("flow_id");
        $data_flow_order = $this->rpm->get_data_flow_order($flow_id)->result();
        $data_detail = $this->rpm->get_data_detail($data_flow_order[0]->Order_Id)->result();
        $order_id = $data_flow_order[0]->Order_Id;

        foreach($data_detail as $key => $val){
           
            $arr_select_inbound_id = null;
            $new_detail[$key] = $this->showAndGenSelectData_process($val->Product_Code,$val->Product_Status,$val->Product_Sub_Status,$val->Product_Lot,$val->Product_Serial,$val->Product_Mfd,$val->Product_Exp,$arr_select_inbound_id,$val->Reserv_Qty);
            $this->dup_and_book_relo_detail($new_detail[$key],$data_flow_order,$val->Suggest_Location_Id);
            
            foreach($new_detail[$key] as $s1_key => $s1_val){
                $can_book[$key] = $s1_val['Reserve_Qty'] ;       
            }        
            if(!empty( $can_book[$key])){
                if($val->Reserv_Qty <= $can_book[$key]){
                    $aff1 = $this->rpm->delect_detail($val->Item_Id);
                }
                else if($val->Reserv_Qty > $can_book[$key]){
                    $aff2 = $this->rpm->change_reserv($val->Item_Id,$can_book[$key]);
                }
            }
        
        }  
        
        $aff3 = $this->rpm->updateWorkFlowTrax($flow_id, $data_flow_order[0]->Process_Id, 1);

        exit();
    }   
    
    public function dup_and_book_relo_detail($ag_inb,$data_flow_order,$sug_lo){
        
        foreach($ag_inb as $row_inb => $data_inb_raw ){
            
            $data_inb = $this->rpm->get_data_inb($data_inb_raw['Inbound_Id'])->result();
            $data_inb = $data_inb[0];

                    $lar_data_pd[$row_inb]['Order_Id'] = $data_flow_order[0]->Order_Id ;
                    $lar_data_pd[$row_inb]['Document_No'] = $data_flow_order[0]->Document_No ;                   
                    $lar_data_pd[$row_inb]['Doc_Refer_Int'] = $data_inb->Doc_Refer_Int ;
                    $lar_data_pd[$row_inb]['Doc_Refer_Ext'] = $data_inb->Doc_Refer_Ext ;
                    $lar_data_pd[$row_inb]['Doc_Refer_Inv'] = $data_inb->Doc_Refer_Inv ;
                    $lar_data_pd[$row_inb]['Doc_Refer_CE'] = $data_inb->Doc_Refer_CE ;
                    $lar_data_pd[$row_inb]['Doc_Refer_BL'] = $data_inb->Doc_Refer_BL ;
                    $lar_data_pd[$row_inb]['Doc_Refer_AWB'] = $data_inb->Doc_Refer_AWB ;
                    $lar_data_pd[$row_inb]['Inbound_Item_Id'] =  $data_inb->Inbound_Id;
                    $lar_data_pd[$row_inb]['Product_Id'] = $data_inb->Product_Id;//$product_id[$row_inb] ;
                    $lar_data_pd[$row_inb]['Product_Code'] = $data_inb->Product_Code ;
                    $lar_data_pd[$row_inb]['Product_Status'] = $data_inb->Product_Status ;
                    $lar_data_pd[$row_inb]['Product_Sub_Status'] = $data_inb->Product_Sub_Status ;
                    $lar_data_pd[$row_inb]['Suggest_Location_Id'] = $sug_lo ;
//                    $lar_data_pd[$row_inb]['Actual_Location_Id'] = $new_locat_id ;
                    $lar_data_pd[$row_inb]['Old_Location_Id'] = $data_inb->Actual_Location_Id ;
                    $lar_data_pd[$row_inb]['Pallet_Id'] = $data_inb->Pallet_Id ;
                    $lar_data_pd[$row_inb]['Product_License'] = $data_inb->Product_License ;
                    $lar_data_pd[$row_inb]['Product_Lot'] = $data_inb->Product_Lot ;
                    $lar_data_pd[$row_inb]['Product_Serial'] = $data_inb->Product_Serial ;
                    $lar_data_pd[$row_inb]['Product_Mfd'] = $data_inb->Product_Mfd ;
                    $lar_data_pd[$row_inb]['Product_Exp'] = $data_inb->Product_Exp ;
                    $lar_data_pd[$row_inb]['Receive_Date'] = $data_inb->Receive_Date ;
                    $lar_data_pd[$row_inb]['Reserv_Qty'] = $data_inb_raw['Reserve_Qty'];
//                    $lar_data_pd[$row_inb]['Confirm_Qty'] = $lar_data_pd[$row_inb]['Reserv_Qty'];
                    $lar_data_pd[$row_inb]['Unit_Id'] = $data_inb->Unit_Id ;
                    $lar_data_pd[$row_inb]['Reason_Code'] = null ;
                    $lar_data_pd[$row_inb]['Reason_Remark'] = null;//$data_inb->Reason_Remark ;
                    $lar_data_pd[$row_inb]['Remark'] = $data_inb->Remark ;
                    $lar_data_pd[$row_inb]['Picking_By'] = null ;
                    $lar_data_pd[$row_inb]['Picking_Date'] = null ;
                    $lar_data_pd[$row_inb]['Putaway_By'] = $data_inb->Putaway_By ;
                    $lar_data_pd[$row_inb]['Putaway_Date'] = $data_inb->Putaway_Date ;
                    $lar_data_pd[$row_inb]['Owner_Id'] = $data_inb->Owner_Id ;
                    $lar_data_pd[$row_inb]['Renter_Id'] = $data_inb->Renter_Id ;
                    $lar_data_pd[$row_inb]['Price_Per_Unit'] = $data_inb->Price_Per_Unit ;
                    $lar_data_pd[$row_inb]['Unit_Price_Id'] = $data_inb->Unit_Price_Id ;
                    $lar_data_pd[$row_inb]['All_Price'] = $data_inb->All_Price ;
                    $lar_data_pd[$row_inb]['DP_Type_Pallet'] = null ;
                    $lar_data_pd[$row_inb]['Pallet_Id_Out'] = null ;
                    $lar_data_pd[$row_inb]['Cont_Id'] = $data_inb->Cont_Id ;
                    $lar_data_pd[$row_inb]['Invoice_Id'] = $data_inb->Invoice_Id ;
                    $lar_data_pd[$row_inb]['Vendor_Id'] = $data_inb->Vendor_Id ;
                    
                    $new_item_id[$row_inb] = $this->rpm->addRelocationDetail($lar_data_pd[$row_inb]);
                    $temp[] = $lar_data_pd[$row_inb];
                    $affrow[$row_inb] = $this->stock->reservPDReservQtyArray($temp);
        }
    }
    
    public function showAndGenSelectData_process($productCode,$productStatus,$productSubStatus,$productLot,$productSerial,$productMfd,$productExp,$arr_select_inbound_id2,$qty_of_sku) {             
//        $productCode = $this->input->post("productCode_val");
//        $productStatus = $this->input->post("productStatus_val");
//        $productSubStatus = $this->input->post("productSubStatus_val");
//        $productLot = $this->input->post("productLot_val");
//        $productSerial = $this->input->post("productSerial_val");
//        $productMfd = $this->input->post("productMfd_val");
//        $productExp = $this->input->post("productExp_val");
//        $arr_select_inbound_id = $this->input->post("arr_select_inbound_id");
//        $qty_of_sku = $this->input->post("qty_of_sku");
//        echo $productCode;
//        
//        exit();
//        START set $arr_select_inbound_id = null; for >> สามารถเลือกรายการเดิมที่ยังเหลือ Est.Balance อยู่ได้เพิ่มเติม ในเอกสารเดียวกัน
        //$arr_select_inbound_id = $this->input->post("arr_select_inbound_id");
        $arr_select_inbound_id = null;
        $productMfd = null ;
        $productExp = null ;
        $productSerial = null;
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

        //END ADD

        $result = $this->inbound->getDispatchProductDetails($productCode, $productStatus, $productSubStatus, $productLot, $productSerial, $productMfd, $productExp, NULL, NULL, "", $palletCode_val, $chkPallet_val, $palletIsFull_val, $palletDispatchType_val, 'Product_Code ASC', NULL);
        $result = $result->result(); // Add BY Akkarapol, 28/11/2013, เน€เธ�เธทเน�เธญเน�เธซเน� function getDispatchProductDetails เน€เธ�เน�เธ�เธชเธฒเธ�เธฅเธกเธฒเธ�เธ�เธถเน�เธ� เธ�เธถเธ�เธ—เธณเน�เธซเน� return เธ—เธตเน�เธ�เธฅเธฑเธ�เธกเธฒเน€เธ�เน�เธ�เน�เธ�เน� db->get() เน€เธ�เธทเน�เธญเน�เธซเน�เธ�เธณเน�เธ�เน�เธ�เน�เธ•เน�เธญเธขเธญเธ”เน�เธ”เน�เธซเธฅเธฒเธ�เธซเธฅเธฒเธขเธกเธฒเธ�เธ�เธถเน�เธ� เธ—เธณเน�เธซเน�เธ•เน�เธญเธ�เน€เธ�เธตเธขเธ� $result = $result->result(); เธกเธฒเธฃเธฑเธ�เธ�เน�เธฒเธญเธตเธ�เธฃเธญเธ�เธซเธ�เธถเน�เธ�
//p($result);
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

                $query = $this->rpm->getListPreDispatchByProdCodeViaAjax($post_val);
                $pre_dispatch_list = $query->result();
//p($pre_dispatch_list);
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
        return $new_list;
//        $json['product'] = $new_list;
//        $json['alert'] = 'OK';
//        if ($check_out_of_qty):
//            $system_qty = $need_qty_of_sku - $qty_of_sku;
//            $json['alert'] = "System QTY({$system_qty}) Less Your Input QTY({$need_qty_of_sku}) Please Check This.";
//        endif;
//
//        echo json_encode($json);
    }
    
//    public function replenishmentForm() {
//        $this->output->enable_profiler($this->config->item('set_debug'));
//
//        // pre-load config
//        $conf = $this->config->item('_xml');
//        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
//        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
//        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
//        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
//        $conf_invoice_require = empty($conf['invoice_require']) ? false : @$conf['invoice_require'];
//
//        // define process configuration
//        $process_id = 20;
//        $present_state = 0;
//
//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowPreDispatchList");
//
//        #Get Product Status
//        $r_product_status = $this->product->selectProductStatus()->result();
//        $product_status_list = genOptionDropdown($r_product_status, "SYS", FALSE, TRUE); // Edit by kik! 20131107
//        #Get Product Sub Status
//        $r_product_Substatus = $this->product->selectSubStatus()->result();
//        $product_Substatus_list = genOptionDropdown($r_product_Substatus, "SYS", FALSE, TRUE); // Edit by kik! 20131107
//        #Get Renter [Company Renter] list
//        $r_renter = $this->company->getRenterAll()->result();
//        $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE); // Edit by kik! 20131107
//        #Get Shipper[Company Owner] list
//        $r_shipper = $this->company->getOwnerAll()->result();
//        $shipper_list = genOptionDropdown($r_shipper, "COMPANY", FALSE, FALSE); // Edit by kik! 20131107
//        #Get Consignee[Company Customer]  list
//        $r_consignee = $this->company->getBranchNotOwner($this->session->userdata('branch_id'))->result();
//        $consignee_list = genOptionDropdown($r_consignee, "COMPANY", FALSE, FALSE); // Edit by kik! 20131107
//        #Get Dispatch Type list
//        $dispatch_type_list = genOptionDropdown($this->sys->getDispatchType(), "SYS", TRUE, TRUE); // Edit by kik! 20131107
//
//        $renter_id_select = form_dropdown("renter_id_select", $renter_list, '', "id=renter_id_select class=required"); //$renter_id  // Hard Code. Edit by Ton! 20131028
//        $frm_warehouse_select = form_dropdown("frm_warehouse_select", $shipper_list, '', "id=frm_warehouse_select  class=required"); //$shipper_id  // Hard Code. Edit by Ton! 20131028
//        $to_warehouse_select = form_dropdown("to_warehouse_select", $consignee_list, '', "id=to_warehouse_select  class=required");
//        $dispatch_type_select = form_dropdown("dispatch_type_select", $dispatch_type_list, "DP1", "id=dispatch_type_select  class=required");
//        $productStatus_select = form_dropdown("productStatus_select", $product_status_list, "", "id=productStatus_select");
//        $productSubStatus_select = form_dropdown("productSubStatus_select", $product_Substatus_list, "", "id=productSubStatus_select");
//
//        $query = $this->rpm->replenishmentAll();
//        $pre_dispatch_list = $query->result();
//
//        //ADD check show/hide datatable of container:BY POR 2014-09-08
//        $init_invoice = array();
//        if ($conf_inv):
////            $init_invoice['init'] = ", { //invoice
////                onblur: 'submit',
////                sUpdateURL: '".site_url("/pre_dispatch/saveEditedRecord")."',
////                event: 'click', //invoice
////            }";
//            $init_invoice['init'] = ", { //invoice
//                onblur: 'submit',
//                sUpdateURL: function(value, settings) {return(value);},
//                event: 'click', //invoice
//            }";
//            $init_invoice['reinit'] = ",null";
//            $init_invoice['reinit_col'] = "";
//        else :
//            $init_invoice['init'] = "";
//            $init_invoice['reinit'] = ",null";
//            $init_invoice['reinit_col'] = ",null";
//        endif;
//        //END ADD
//
//        $init_container = "";
//        #start add code for ISSUE 3320 :  by kik : 20140113
//        $price = '';
//        if ($conf_price_per_unit):
//            //$price = ',null';  //COMMENT BY POR 2014-06-02
//            //ADD BY POR 2014-06-02 เพิ่มให้สามารถกรอกราคาและเลือกหน่วยได้
////            $price = " ,{
////                    sSortDataType: \"dom-text\",
////                    sType: \"numeric\",
////                    type: 'text',
////                    onblur: \"submit\",
////                    event: 'click',
////                    is_required: true,
////                    loadfirst: true,
////                    cssclass: \"required number\",
////                    sUpdateURL: '" . site_url() . '/pre_dispatch/saveEditedRecord' . "',
////                    fnOnCellUpdated: function(sStatus, sValue, settings) {
////                        calculate_qty();
////                    }
////                }";
//            $price = " ,{
//                    sSortDataType: \"dom-text\",
//                    sType: \"numeric\",
//                    type: 'text',
//                    onblur: \"submit\",
//                    event: 'click',
//                    is_required: true,
//                    loadfirst: true,
//                    cssclass: \"required number\",
//                    sUpdateURL: function(value, settings) {return(value);},
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
//                        var oTable = $('#showProductTable').dataTable();
//                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
//                        oTable.fnUpdate(value, rowIndex, ci_unit_price_id);
//                        return value;
//                    }
//                }
//                ";
//
//        //END ADD;
//        else:
////            $price = "  , {
////                    onblur: 'submit',
////                    sUpdateURL: '" . site_url() . '/pre_dispatch/saveEditedRecord' . "',
////                    event: 'click',
////                }";
//            $price = "  , {
//                    onblur: 'submit',
//                    sUpdateURL: function(value, settings) {return(value);},
//                    event: 'click',
//                }";
//            $unitofprice = ',null';
//        endif;
//
//        if ($this->settings['dp_type_link_prod_status']['active']):
//            foreach ($this->settings['dp_type_link_prod_status']['object'] as $key_object => $object):
//                $dp_type_link_prod_status[$object['dp_type']] = $object['prod_status'];
//            endforeach;
//            $dp_type_link_prod_status = json_encode($dp_type_link_prod_status);
//        else:
//            $dp_type_link_prod_status = FALSE;
//        endif;
//
//        //ADD BY POR 2014-08-13 select container size for size list in container
//        $container_size = $this->container->getContainerSize()->result();
//        $_container_list = array();
//        foreach ($container_size as $idx => $value) {
//            $_container_list[$idx]['Id'] = $value->Cont_Size_Id;
//            $_container_list[$idx]['No'] = $value->Cont_Size_No;
//            $_container_list[$idx]['Unit_Code'] = $value->Cont_Size_Unit_Code;
//        }
//        $container_size_list = json_encode($_container_list);
//
//        $doc_refer_container = array();
//
//        #end add code for ISSUE 3320 :  by kik : 20140113
////        p($data_form->form_name); exit;
//
//        $str_form = $this->parser->parse('form/' . $data_form->form_name, array("parameter" => form_input('warehouse_form', 'whs_form')
//            , "test_parse" => "test pass parse from controller"
//            , "price_per_unit" => $conf_price_per_unit //add by kik : 20140113
//            , "conf_inv" => $conf_inv //ADD BY POR : 2014-08-13
//            , "conf_invoice_require" => $conf_invoice_require //ADD BY POR : 2014-10-14
//            , "conf_cont" => $conf_cont //ADD BY POR : 2014-08-13
//            , "conf_pallet" => $conf_pallet //ADD BY POR : 2014-08-13
//            , "renter_id_select" => $renter_id_select
//            , "frm_warehouse_select" => $frm_warehouse_select
//            , "to_warehouse_select" => $to_warehouse_select
//            , "dispatch_type_select" => $dispatch_type_select
//            , "process_id" => $process_id
//            , "present_state" => $present_state
//            , "productStatus_select" => $productStatus_select
//            , "productSubStatus_select" => $productSubStatus_select
//            , "token" => ''
//            , "owner_id" => ''//$owner_id // Hard Code. Edit by Ton! 20131028
//            , "dp_type_link_prod_status" => $dp_type_link_prod_status
//            , "container_list" => ""
//            , "doc_refer_container" => $doc_refer_container
//            , "container_size_list" => $container_size_list
//            , "DeliveryTime" => ''
//            , "DestinationDetail" => ''
//            , "data_form" => (array) $data_form), TRUE);
//        $this->parser->parse('workflow_template', array(
//            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
//            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPreDispatch"></i>'
//            , 'form' => $str_form
//            , 'price' => $price         //add by kik : 20140113
//            , 'init_container' => $init_container
//            , 'init_invoice' => $init_invoice['init']
//            , 'reinit_invoice' => $init_invoice['reinit']
//            , 'reinit_invoice_col' => $init_invoice['reinit_col']
//            , 'unitofprice' => $unitofprice //ADD BY POR 2014-06-02 ให้ส่งตัวแปรเกี่ยวกับการแสดงหน่วยของราคา
//            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'
//            , 'button_action' => $data_form->str_buttun
//            , 'user_login' => $this->session->userdata('username')
//            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
//        ));
//    }

    function confirmRpmProduct() {

        # Check validate data
        $return = array();
        $check_not_err = TRUE; //for check error all process , if process error set value = FALSE

        $this->load->controller('balance', 'balance');

        $token = $this->input->post("token");
        $flow_id = $this->input->post("flow_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");

        // validate token
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        $response = validate_token($token, $flow_id, $flow_info[0]->Present_State, $flow_info[0]->Process_Id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;

        //$user_id  = $this->input->post("user_id"); By Por 2013-10-02 comment post ค่าไว้ ใช้ session แทน
        //=====start by por 2013-10-02 ใช้ session แทนการ post
        $user_id = $this->session->userdata('user_id');
        //=====end

        $prod_list = $this->input->post("prod_list");

        # Parameter Order relocation header
        $est_relocate_date = $this->input->post("est_relocate_date");
        $relocate_date = $this->input->post("relocate_date");
        $worker_id = $this->input->post("worker_id");

        //$putaway_by = $this->rl->getUser($worker_id); comment by por 2013-10-02 สามารถใช้ worker_id ได้เลย เนื่องจากส่งค่ามาเป็น user_id อยู่แล้ว
        //start by por 2013-10-02 แก้ไขให้ใช้ $worker_id ได้เลย เนื่องจากส่งค่ามาเป็น user_id อยู่แล้วไม่จำเป็นต้องแปลงค่าอีกรอบ
        $putaway_by = $worker_id;
        //end

        $doc_type = '';
        $process_type = '';
        $owner_id = $this->input->post("owner_id");
        $renter_id = $this->input->post("renter_id");
        $receive_type = '';
        $remark = '';
        $re_date = convertDate($relocate_date, "eng", "iso", "-");
        $is_urgent = $this->input->post("is_urgent");   //add for ISSUE 3312 : by kik : 20140120
        //add Urgent for ISSUE 3312 : by kik : 20140120
        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }


        # Parameter Order relocation detail
        $prod_list = $this->input->post("prod_list");
        $prod_del_list = $this->input->post("prod_del_list");

//      #Start add for set index from view : by kik : 20140115
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

//      #End add by kik : 20140115
        #ADD BY KIK 2014-01-16 เพิ่มเกี่ยวกับราคา
        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }
        #END ADD
        #========================== Start Save Data ============================
        $this->transaction_db->transaction_start();

        $flow_detail = $this->rl->getRelocationOrder($flow_id);

        $document_no = $flow_detail[0]->Document_No;
        $order_id = $flow_detail[0]->Order_Id;

        $data['Document_No'] = $document_no;

        $response = $this->balance->_chkPDreservBeforeApprove($ci_confirm_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, SEPARATOR, "STK_T_Relocate_Detail"); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการส่งค่าของตัวแยกให้กับฟังก์ชั่น $this->balance->xxx จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
        $return = array_merge_recursive($return, $response);

        if (!empty($return['critical'])):

            $json['status'] = "validation";
            $json['return_val'] = $return;

        else:
            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021
            if (empty($action_id)):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update workflow.";
            endif;

            if ($check_not_err):
                $order = array(
                    'Doc_Relocate' => strtoupper($document_no)
                    , 'Doc_Type' => strtoupper(iconv("UTF-8", "TIS-620", $doc_type))
                    , 'Estimate_Action_Date' => $est_relocate_date
                    , 'Actual_Action_Date' => $relocate_date
                    , 'Owner_Id' => $owner_id
                    , 'Renter_Id' => $renter_id                         #edit from $receive_type to $owner_id : by kik : 2013-12-12
                    , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                    , 'Assigned_Id' => $worker_id
                    , 'Modified_By' => $user_id
                    , 'Modified_Date' => date("Y-m-d H:i:s")            #edit from date("Y-m-d H-i-s") to date("Y-m-d H:i:s") : by kik : 2013-12-12
                    , 'Is_urgent' => $is_urgent
                );

                $where['Flow_Id'] = $flow_id;
                $where['Order_Id'] = $order_id;
                $result_order_query = $this->rl->updateReLocationOrder($order, $where);
                if (!$result_order_query):
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not update Order.";
                /*
                 * LOG MSG 1
                 */
                endif;
            endif;
            ///////////end check

            if ($check_not_err):
                $product_list = array();
//                $inbound_list = array();
                $all_product_move = array();

                if (!empty($prod_list)):
                    foreach ($prod_list as $rows):
                        //$rows = str_replace("<a", "", $rows);
                        $cut_data = explode(SEPARATOR, strip_tags($rows)); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
                        //p($cut_data);
//                        if (!in_array($cut_data[$ci_inbound_id], $inbound_list)):
//                            $inbound_list[] = $cut_data[$ci_inbound_id];
//                            $info = $this->rl->showRLProduct($order_id, $cut_data[$ci_inbound_id]);  //comment by kik : 20140326 เพราะว่าการ update ค่าควรใช้ item_id ที่ส่งมาจากหน้า form ไม่ใช่หาเอง กรณีที่ 1 order มีหลายๆ inbound id จะ update ข้อมูลผิดหมด
                        $p_detail = array();
                        //                    $p_detail['Suggest_Location_Id'] = $this->lc->getLocationIdByCode(trim($cut_data[9]), ''); // Comment By Akkarapol, 18/10/2013, คอมเม้นต์ โค๊ดที่ update ค่าของ Suggest_Location_Id ทิ้งเพราะว่าค่า Suggest_Location_Id ตัวนี้มันไม่ต้องไป update ค่าเพราะพอ update ค่าไปแล้ว ค่าที่ลง DB เข้าไปมันเป็นค่า 0 ซึ่งมันผิด
                        $p_detail['Actual_Location_Id'] = $this->lc->getLocationIdByCode(trim($cut_data[$ci_act_loc_id]), '');
                        $p_detail['Reserv_Qty'] = str_replace(",", "", $cut_data[$ci_reserv_qty]); //+++++ADD BY POR 2013-11-29 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                        $p_detail['Confirm_Qty'] = str_replace(",", "", $cut_data[$ci_confirm_qty]); //+++++ADD BY POR 2013-11-29 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                        $p_detail['Remark'] = iconv("UTF-8", "TIS-620", $cut_data[$ci_remark]);
                        $p_detail['Putaway_By'] = $putaway_by;
                        $p_detail['Putaway_Date'] = $re_date;

                        // Add check actual location if is null
                        if (is_null($p_detail['Actual_Location_Id'])) :
                                $check_not_err = FALSE;
                                $return['critical'][]['message'] = "Location is NULL";
                                log_message("INFO", "ERROR - Location is NULL");
                        endif;
                        // Add check actual location if is null

                        //ADD BY KIK 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                        if ($this->settings['price_per_unit'] == TRUE):
                            $p_detail['Price_Per_Unit'] = str_replace(",", "", $cut_data[$ci_price_per_unit]); //+++++ADD BY KIK 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                            $p_detail['Unit_Price_Id'] = $cut_data[$ci_unit_price_id];
                            $p_detail['All_Price'] = str_replace(",", "", $cut_data[$ci_all_price]); //+++++ADD BY KIK 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                        endif;
                        //END KIK

                        if ($cut_data[$ci_item_id]): //add '$cut_data[$ci_item_id]' by kik : 20140326 เพราะว่าการ update ค่าควรใช้ item_id ที่ส่งมาจากหน้า form ไม่ใช่หาเอง กรณีที่ 1 order มีหลายๆ inbound id จะ update ข้อมูลผิดหมด
                            $where = array();
                            $where['Item_Id'] = $cut_data[$ci_item_id]; //add '$cut_data[$ci_item_id]' by kik : 20140326 เพราะว่าการ update ค่าควรใช้ item_id ที่ส่งมาจากหน้า form ไม่ใช่หาเอง กรณีที่ 1 order มีหลายๆ inbound id จะ update ข้อมูลผิดหมด
                            $where['Order_Id'] = $order_id;
                            $result_relocate_detail = $this->rl->updateReLocationOrderDetail($p_detail, $where);

                            if (!$result_relocate_detail):

                                $check_not_err = FALSE;
                                $return['critical'][]['message'] = "Can Not update relocate detail.";
                            /*
                             * LOG MSG 2
                             */
                            endif;
                        endif;
                        $all_product_move[] = $p_detail;
//                        endif;
                    endforeach; // close for each
                endif; // close if have prod list
            endif;
            ///////////end check

            if ($check_not_err):
                // Ball
                // Add Update to PD
                $status_update = $this->balance->updateConfirmPDReservQty($ci_confirm_qty, $ci_inbound_id, $order_id, $prod_list, SEPARATOR); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการส่งค่าของตัวแยกให้กับฟังก์ชั่น $this->balance->_chkPDreservBeforeOpen จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
                if (!$status_update):
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can Not update Inbound.";
                /*
                 * LOG MSG 3
                 */
                endif;
            // END
            endif;
            ///////////end check

            if ($check_not_err):
                if (is_array($prod_del_list) && !empty($prod_del_list)) :
                    $item_delete = array();
                    foreach ($prod_del_list as $rows) :
                        $a_data = explode(SEPARATOR, strip_tags($rows)); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
                        $item_delete[] = $a_data[$ci_inbound_id];  /* Item_Id for Delete in STK_T_Order_Detail  */
                    endforeach;

                    $status_remove = $this->rl->removeRLProductDetail($item_delete, $order_id);
                    if (!$status_remove):
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Can Not delete STK_T_Relocate_Detail.";
                    /*
                     * LOG MSG 4
                     */
                    endif;
                endif;
            endif;
        ///////////end check
        endif;

        if ($check_not_err):
            $set_return['message'] = "Confirm Replenishment Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;

            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();
        else:
            $array_return['critical'][]['message'] = "Confirm Replenishment Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);

            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();
        endif;

        echo json_encode($json);
    }
    
    function approveRpmProduct() {
        #Load config
        $conf = $this->config->item('_xml');
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];
        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;

        $this->load->controller('balance', 'balance');

        $token = $this->input->post("token");
        $flow_id = $this->input->post("flow_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");

        // validate token
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        $response = validate_token($token, $flow_id, $flow_info[0]->Present_State, $flow_info[0]->Process_Id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;

        //$user_id  = $this->input->post("user_id"); By Por 2013-10-02 comment post ค่าไว้ ใช้ session แทน
        //=====start by por 2013-10-02 ใช้ session แทนการ post
        $user_id = $this->session->userdata('user_id');
        //=====end

        $prod_list = $this->input->post("prod_list");

        //p($prod_list); exit();
        //p($_POST);
        # Parameter Order relocation header
        $est_relocate_date = $this->input->post("est_relocate_date");
        $relocate_date = $this->input->post("relocate_date");
        $putaway_by = $worker_id = $this->input->post("worker_id");
        //$putaway_by = $this->rl->getUser($worker_id); // remove to use same as worker_id
        $re_date = convertDate($relocate_date, "eng", "iso", "-");
        $doc_type = '';
        $owner_id = $this->input->post("owner_id");
        $renter_id = $this->input->post("renter_id");
        $receive_type = '';
        $remark = '';
        $is_urgent = $this->input->post("is_urgent");   //add for ISSUE 3312 : by kik : 20140120
        //add Urgent for ISSUE 3312 : by kik : 20140120
        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Parameter Order relocation detail
        $prod_list = $this->input->post("prod_list");
        $prod_del_list = $this->input->post("prod_del_list");

//      #Start add for set index from view : by kik : 20140115
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
//      #End add by kik : 20140115
        #ADD BY KIK 2014-01-16 เพิ่มเกี่ยวกับราคา
        if ($conf_price_per_unit) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }
        #END ADD
        #========================== Start Save Data ============================
        $this->transaction_db->transaction_start();

        # Get relocate order
        if ($check_not_err):
            $flow_detail = $this->rl->getRelocationOrder($flow_id);
            if ($flow_detail == "" || empty($flow_detail)):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not get data Relocation order.";
            /*
             * LOG MSG 2
             */
            else:
                $document_no = $flow_detail[0]->Document_No;
                $order_id = $flow_detail[0]->Order_Id;
                $data['Document_No'] = $document_no;
            endif;
        endif;

        # ================== NO_DISPATCH_AREA  =========================
        if ($check_not_err):
            $get_order = $this->rl->getlocation_order($order_id);

            foreach ($get_order as $key => $value) {
                $Actual_Location_Id = $value['Actual_Location_Id'];
                $Suggest_Location_Id = $value['Suggest_Location_Id'];
              }
            // $Actual_Location_Id  ='6717';
            // $Suggest_Location_Id ='2';
             $getloc = $this->rl->location_id_donotmove(); 
         
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
          
              $response = array_key_exists($Suggest_Location_Id, $temp);
               if($response == 1){
               $check_not_err = FALSE;
               $return['critical'][]['message'] = "SUGGEST_LOCATION NO_DISPATCH_AREA! ";
               }else{
               $check_not_err =TRUE;  
               }
              endif;
                 # ================== NO_DISPATCH_AREA  =========================
              
        // BALL
        if ($check_not_err):
            $response = $this->balance->_chkPDreservBeforeApprove($ci_confirm_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, SEPARATOR, "STK_T_Relocate_Detail"); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการส่งค่าของตัวแยกให้กับฟังก์ชั่น $this->balance->xxx จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
            $return = array_merge_recursive($return, $response);

            if (!empty($return['critical'])):

                $json['status'] = "validation";
                $json['return_val'] = $return;

            else:
                $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021
                if ($action_id == "" || empty($action_id)):
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not update workflow.";
                /*
                 * LOG MSG 1
                 */
                endif;

                if ($check_not_err):
                    $order = array(
                        'Doc_Relocate' => strtoupper($document_no)
                        , 'Doc_Type' => strtoupper(iconv("UTF-8", "TIS-620", $doc_type))
                        , 'Estimate_Action_Date' => $est_relocate_date
                        , 'Actual_Action_Date' => $relocate_date
                        , 'Owner_Id' => $owner_id
                        , 'Renter_Id' => $renter_id                     #edit from $receive_type to $owner_id : by kik : 2013-12-12
                        , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                        , 'Assigned_Id' => $worker_id
                        , 'Modified_By' => $user_id
                        , 'Modified_Date' => date("Y-m-d H:i:s")        #edit from date("Y-m-d H-i-s") to date("Y-m-d H:i:s") : by kik : 2013-12-12
                        , 'Is_urgent' => $is_urgent
                    );
                    $where['Flow_Id'] = $flow_id;
                    $where['Order_Id'] = $order_id;
                    $result_order_query = $this->rl->updateReLocationOrder($order, $where);
                    if (!$result_order_query):
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Can not update Order.";
                    /*
                     * LOG MSG 4
                     */
                    endif;
                endif;

                if ($check_not_err):
                    $product_list = array();
//                    $inbound_list = array();
                    $all_product_move = array();
                    if (!empty($prod_list)):
                        foreach ($prod_list as $rows):
                            $cut_data = explode(SEPARATOR, strip_tags($rows)); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
                            //p($cut_data);
//                            if (!in_array($cut_data[$ci_inbound_id], $inbound_list)):
//                                $inbound_list[] = $cut_data[$ci_inbound_id];
//                                $info = $this->rl->showRLProduct($order_id, $cut_data[$ci_inbound_id]); //comment by kik : 20140326 เพราะว่าการ update ค่าควรใช้ item_id ที่ส่งมาจากหน้า form ไม่ใช่หาเอง กรณีที่ 1 order มีหลายๆ inbound id จะ update ข้อมูลผิดหมด
                            $p_detail = array();
                            $p_detail['Suggest_Location_Id'] = $this->lc->getLocationIdByCode(trim($cut_data[$ci_sug_loc_id]), '');
                            $p_detail['Actual_Location_Id'] = $this->lc->getLocationIdByCode(trim($cut_data[$ci_act_loc_id]), '');
                            $p_detail['Reserv_Qty'] = $cut_data[$ci_reserv_qty];
                            $p_detail['Confirm_Qty'] = $cut_data[$ci_confirm_qty];
                            $p_detail['Remark'] = iconv("UTF-8", "TIS-620", $cut_data[$ci_remark]);
                            $p_detail['Putaway_By'] = $putaway_by;
                            $p_detail['Putaway_Date'] = $re_date;
                            //ADD BY KIK 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                            if ($this->settings['price_per_unit'] == TRUE):
                                $p_detail['Price_Per_Unit'] = str_replace(",", "", $cut_data[$ci_price_per_unit]); //+++++ADD BY KIK 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                                $p_detail['Unit_Price_Id'] = $cut_data[$ci_unit_price_id];
                                $p_detail['All_Price'] = str_replace(",", "", $cut_data[$ci_all_price]); //+++++ADD BY KIK 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                            endif;
                            //END KIK

                            if ($cut_data[$ci_item_id]): //add '$cut_data[$ci_item_id]' by kik : 20140326 เพราะว่าการ update ค่าควรใช้ item_id ที่ส่งมาจากหน้า form ไม่ใช่หาเอง กรณีที่ 1 order มีหลายๆ inbound id จะ update ข้อมูลผิดหมด
                                $where = array();
                                $where['Item_Id'] = $cut_data[$ci_item_id]; //add '$cut_data[$ci_item_id]' by kik : 20140326 เพราะว่าการ update ค่าควรใช้ item_id ที่ส่งมาจากหน้า form ไม่ใช่หาเอง กรณีที่ 1 order มีหลายๆ inbound id จะ update ข้อมูลผิดหมด
                                $where['Order_Id'] = $order_id;
                                $result_relocate_detail = $this->rl->updateReLocationOrderDetail($p_detail, $where);
                                if (!$result_relocate_detail):
                                    $check_not_err = FALSE;
                                    $return['critical'][]['message'] = "Can Not update relocate detail.";
                                endif;
                            endif;
                            $all_product_move[] = $p_detail;
//                            endif;
                        endforeach; // close for each
                    endif; // close if have prod list
                endif;
                
                #######################################
                #update pallet code ให้ถูกต้อง ต้องอยู่ก่อน insert inbound เนื่องจากจะมีการจัดการข้อมูล ในตาราง relo ให้เรียบร้อยก่อน dup: ADD BY POR 2015-09-07
                if ($check_not_err && $conf_pallet):
                    $respond = $this->balance->updatePalletLocation($order_id);
                    if (!$respond):
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Can not update location on Pallet.";
                    endif;
                endif;
                ####################################### 

                //ADD BY POR 2014-06-16 update PD inbound ให้เป็นค่าที่ Approve โดยนำค่าที่ Confrim มาลบออกก่อนที่ update ค่าใหม่เข้าไป
                if ($check_not_err):
                    $status_update = $this->balance->updateaInboundPDReservQty($ci_confirm_qty, $ci_inbound_id, $order_id, $prod_list, SEPARATOR); // Edit By Akkarapol, 22/01/2014, เน€เธเธฅเธตเนเธขเธเธเธฒเธฃเธชเนเธเธเนเธฒเธเธญเธเธ•เธฑเธงเนเธขเธเนเธซเนเธเธฑเธเธเธฑเธเธเนเธเธฑเนเธ $this->balance->_chkPDreservBeforeOpen เธเธฒเธเธ—เธตเนเน€เธเธขเนเธเน "," เนเธซเนเน€เธเนเธ SEPATATOR เนเธ—เธเน€เธเธทเนเธญเธเธเธฒเธ "," เธเธฑเนเธเน€เธเนเธเนเธเน text เธเธฃเธฃเธกเธ”เธฒ เธซเธฒเธเธกเธตเธเธฒเธฃเนเธเนเธเธฒเธเธ•เธฑเธงเธเธตเนเธเธฐเธ—เธณเนเธซเนเธเธฒเธฃ explode เธเธตเนเธเธดเธ”เธเธฅเธฒเธ”เนเธ”เน
                    if (!$status_update):
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Can Not update Inbound.";
                    endif;
                endif;
                //END ADD
                
                //EDIT BY POR 2014-06-16 สลับตำแหน่งให้มาอยู่ตรงนี้ เพื่อป้องกันการ Approve ไม่ตรง Confrim จึงต้องไป update PD ให้เป็นค่าที่ Approve จริงก่อน
                if ($check_not_err):
                    // Ball
                    // Add Update to PD
                    $status_update = $this->balance->updatePDReservQty($ci_confirm_qty, $ci_inbound_id, $ci_item_id, 0, $prod_list, SEPARATOR); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการส่งค่าของตัวแยกให้กับฟังก์ชั่น $this->balance->_chkPDreservBeforeOpen จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
                    if (!$status_update):
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Can not update Inbound.";
                    endif;
                // END
                endif;
                //END ADD

                if ($check_not_err):
                    if (is_array($prod_del_list) && (count($prod_del_list) > 0)):
                        $item_delete = array();
                        foreach ($prod_del_list as $rows) {
                            $a_data = explode(SEPARATOR, strip_tags($rows)); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
                            $item_delete[] = $a_data[$ci_inbound_id];  /* Item_Id for Delete in STK_T_Order_Detail  */
                        }
                        $result_remove_location = $this->rl->removeRLProductDetail($item_delete, $order_id);
                        if (!$result_remove_location):
                            $check_not_err = FALSE;
                            $return['critical'][]['message'] = "Can not delete location list.";
                        /*
                         * LOG MSG 7
                         */
                        endif;
                    endif;
                endif;
                

                if ($check_not_err):
                    # move location
                    $respond = $this->stock_lib->moveProductLocation($order_id);
                    if (!$respond):
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Can not move Product.";
                    endif;
                endif;              
            endif;

            /**
             * check if for return json and set transaction
             */
            if ($check_not_err):

                $set_return['message'] = "Approve Replenishment Complete.";
                $return['success'][] = $set_return;
                $json['status'] = "save";
                $json['return_val'] = $return;

                /**
                 * ================== Auto End Transaction =========================
                 */
                $this->transaction_db->transaction_end();

            else:

                $array_return['critical'][]['message'] = "Approve Replenishment Incomplete.";
                $json['status'] = "save";
                $json['return_val'] = array_merge_recursive($array_return, $return);

                /**
                 * ================== Rollback Transaction =========================
                 */
                $this->transaction_db->transaction_rollback();
            endif;

            echo json_encode($json);

        else :

            echo json_encode($json);

        endif;
    }
    
        function rejectAndReturnAction() {

        /**
         * Set Variable
         */
        $check_not_err = TRUE;
        $return = array();

        $this->load->model("stock_model", "stock");

        $token = $this->input->post("token");
        $process_id = $this->input->post("process_id");
        $flow_id = $this->input->post("flow_id");
        $present_state = $this->input->post("present_state");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");

        $flow_detail = $this->rl->getRelocationOrder($flow_id);
        $order_id = $flow_detail[0]->Order_Id;
        $data['Document_No'] = $flow_detail[0]->Document_No;

        // validate token
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        $response = validate_token($token, $flow_id, $flow_info[0]->Present_State, $flow_info[0]->Process_Id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;


#------------------------------------------------------------

        /**
         * ================== Start Transaction =========================
         */
        $this->transaction_db->transaction_start();


        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
        if (empty($action_id) || $action_id == '') :
            $check_not_err = FALSE;

            /**
             * 1
             */
            $return['critical'][]['message'] = "Can not update Workflow.";
        endif;

        if ($check_not_err):
            #update data order detail
            $detail_order['Actual_Action_Date'] = NULL;
            $result_orders = $this->rl->updateRelocationData($detail_order, $order_id);
            if ($result_orders < 0):
                $check_not_err = FALSE;

                /**
                 * 2
                 */
                $return['critical'][]['message'] = "Can not update Relocation.";
            endif;
        endif;

        if ($check_not_err):
            $detail['Actual_Location_Id'] = NULL;
            $detail['Confirm_Qty'] = 0;
            $detail['Remark'] = NULL;
            $detail['Putaway_By'] = NULL;
            $detail['Putaway_Date'] = NULL;
            $where['Order_Id'] = $order_id;
            $result_detail = $this->rl->updateReLocationOrderDetail($detail, $where);
            if (!$result_detail):
                $check_not_err = FALSE;

                /**
                 * 3
                 */
                $return['critical'][]['message'] = "Can not update Replenishment Detail.";
            endif;
        endif;


        if ($check_not_err):
            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

            $set_return['message'] = "Reject and Return Replenishment Complete.";
            $return['success'][] = $set_return;
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();
            $set_return['critical'][]['message'] = "Reject and Return Replenishment Incomplete.";
            $json['return_val'] = array_merge_recursive($set_return, $return);
        endif;

        $json['status'] = "save";
        echo json_encode($json);
    }

    function rejectAction() {

        #Variable for check error
        $check_not_err = TRUE;
        $return = array();

        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", "wf");

        $token = $this->input->post("token");

        #add condition check data from list page or form page
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

            $flow_detail = $this->rl->getRelocationOrder($flow_id);
            if ($flow_detail) :
                $order_id = $flow_detail[0]->Order_Id;
                $data['Document_No'] = $flow_detail[0]->Document_No;
            endif;
        }


        // validate token
        $flow_info = $this->wf->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        $response = validate_token($token, $flow_id, $flow_info[0]->Present_State, $flow_info[0]->Process_Id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
            echo json_encode($json);
            exit();
        endif;


        #------------------------------------------------------------
        #=================== Start Update Data ===================================
        $this->transaction_db->transaction_start();

        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);

        if (empty($action_id)):
            $check_not_err = FALSE;
            $return['critical'][]['message'] = "Can not update work flow.";
        /*
         * LOG MSG 1
         */
        endif;

        if ($check_not_err):
            #return qty to PD_reserv in inbound table
            $order_detail = $this->stock->getRelocateDetailByOrderId($order_id);
            $afftectedRows = $this->stock->reservPDReservQtyArray($order_detail, "-");
            if ($afftectedRows == 0): //กรณีเท่ากับ 0 แสดงว่าไม่มีการ update ข้อมูล
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update Inbound.";
            /*
             * LOG MSG 1
             */
            endif;
        endif;

        if ($check_not_err):
            $this->transaction_db->transaction_end();

            #if insert data done. when return commit for runing database again
            #if data send from list page When refesh page
            if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {
                echo "<script>alert('Delete Replenishment Complete.');</script>";
                redirect('reLocationProduct', 'refresh');
                #if data send from form page When return data to form page
            } else {
                $array_return['success'][]['message'] = "Reject Replenishment Complete";
                $json['return_val'] = $array_return;
            }
        else:
            $this->transaction_db->transaction_rollback();

            #if insert data not done . when return rollback and not use database
            #if data send from list page When refesh page
            if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {
                echo "<script>alert('Delete Replenishment not complete. Please check?');</script>";
                redirect('reLocationProduct', 'refresh');
                #if data send from form page When return data to form page
            } else {
                $array_return['critical'][]['message'] = "Reject Replenishment Incomplete";
                $json['return_val'] = $array_return;
            }
        endif;

        $json['status'] = "save";
        echo json_encode($json);
    }

    function get_location(){
        header('Access-control-Allow-0rigin: *');
        $params = $this->input->get();
        $this->load->library('getlocation_no_dispatch_area');
        $data = $this->getlocation_no_dispatch_area->get_location($params);
    }

}

?>
