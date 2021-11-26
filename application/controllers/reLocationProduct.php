<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
# dev by Sureerat
# 1.show order relocation by product code see reLocationProductList() show table ->showReLocationList() show data from order
# 2.open Order Re-Location by product code -> openRLProductFrom() show form -> openRLProduct() save form
# 3.form for Approve and Confirm see openActionForm()
# 4.productList() for jquery show product on STK_T_Inbound
# 5.getSgProductLocation() for jquery show select option suggest location
# 6.getSgLocationAll() for jquery show select option action location
# 7.confirmRLProduct() confirm update re-location order but not move
# 8.approveRLProduct() approve and move location

class reLocationProduct extends CI_Controller {

    public $settings;       //add by kik : 20140115

    //put your code here

    public function __construct() {
        parent::__construct();
        $this->settings = native_session::retrieve();   //add by kik : 20140115
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->library('Stock_lib');
        $this->load->library("unit_test");	
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
        $this->load->library('getlocation_no_dispatch_area');
    }

    public function index() {

        // error_reporting(E_ALL);

        $this->reLocationProductList();
    }

    function reLocationProductList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $module = "reLocationProduct";

        $query = $this->rl->showReLocationList($module);
        $receive_list = $query->result();
        // p($receive_list); exit;
        $column = array("Flow ID", "State Name", "Reference External", "Reference Internal", "Document No.", "Process Day", "Worker Name"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow);
        $action = array(VIEW, DEL);  // add parameter DEL : by kik : 2013-12-26

        // Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย
        if(($key = array_search(DEL, $action)) !== false) {
            unset($action[$key]);
        }
        // END Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย

        $datatable = $this->datatable->genTableFixColumn($query, $receive_list, $column, "reLocationProduct/openActionForm", $action, "reLocationProduct/rejectAction");
        $module = "";
        $sub_module = "";
        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Re-Location Product Code'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
			 ONCLICK=\"openForm('Re-Location','reLocationProduct/openRLProductFrom','A','')\">"
        ));
    }

    #ISSUE 2190 Re-Location : Search Product by Status & Sub Status
    #DATE:2013-09-04
    #BY:KIK
    #เพิ่มการค้นหาด้วย Status & Sub Status ในส่วนของ get product detail
    #START New Comment Code #ISSUE 2190
    #=======================================================================================

    function openRLProductFrom() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        # show form open new order Re-Location
        $process_id = 10;
        $present_state = 0;

        #Load config (add by kik : 20140723)
        $conf = $this->config->item('_xml');
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];
        $parameter['conf_pallet'] = $conf_pallet;
        
        $parameter['token'] = '';
        $renter_id = $this->session->userdata('renter_id'); //add by kik : 2013-12-12
        $owner_id = $this->session->userdata('owner_id');   //add by kik : 2013-12-12
        #Get Renter [worker] list
        //comment by por 2013-10-02 แก้ไขให้เรียกใช้ของ Akkarapol ที่เขียนไว้ เพื่อให้นำ user_id มาใช้ แทน contact_id
        /* start comment
          $query_worker = $this->contact->getWorkerAll();
          $result_worker = $query_worker->result();
          $worker_list = genOptionDropdown($result_worker, "CONTACT");
          end comment */

        //start by por 2013-10-02 นำ function ที่ Akkarapol สร้างไว้มาใช้ โดยนำ user_id มาเก็บแทน contact_id
        $result_worker = $this->contact->getWorkerAllWithUserLogin()->result();
        $worker_list = genOptionDropdown($result_worker, "CONTACTWITHUSERLOGIN");
        //end

        $parameter['worker_list'] = $worker_list;
        //$parameter['worker_id'] = '';
        $parameter['worker_id'] = $this->session->userdata('user_id');

        #Get Product Status
        $q_product_status = $this->p->selectProductStatus();
        $r_product_status = $q_product_status->result();
//      $product_status_list = genOptionDropdown($r_product_status, "SYS");
        $product_status_list = genOptionDropdown($r_product_status, "SYS", FALSE, TRUE); // Edit by kik! 20131107
        $parameter['productStatus_select'] = form_dropdown("productStatus_select", $product_status_list, "", "id=productStatus_select");


        #Get Product Sub Status
        $q_product_Substatus = $this->p->selectSubStatus();
        $r_product_Substatus = $q_product_Substatus->result();
//      $product_Substatus_list = genOptionDropdown($r_product_Substatus, "SYS");
        $product_Substatus_list = genOptionDropdown($r_product_Substatus, "SYS", FALSE, TRUE); // Edit by kik! 20131107
        $parameter['productSubStatus_select'] = form_dropdown("productSubStatus_select", $product_Substatus_list, "", "id=productSubStatus_select");

//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "reLocationProduct"); // Button Permission. Add by Ton! 20140131|
        #start add code for ISSUE 3320 :  by kik : 20140116
        $price_per_unit_table = '';
        if ($this->settings['price_per_unit'] == TRUE):
            $price_per_unit_table = ',null
                    ,null
                    ,null';
        endif;
        #end add code for ISSUE 3320 :  by kik : 20140116

        $show_column = array(
            _lang('no'),
            _lang('product_code'),
            _lang('product_name'),
            _lang('product_status'),
            _lang('product_sub_status'),
            _lang('lot'),
            _lang('serial'),
            _lang('est_balance_qty'),
            _lang('move_qty'),
            _lang('price_per_unit'),
            _lang('unit_price'),
            _lang('all_price'),
            _lang('from_location'),
            _lang('suggest_location'),
            _lang('actual_location'),
            _lang('remark'),
            "Inbound",
            "Price/Unit ID",
            _lang('pallet_code'),
            "DP_Type_Pallet",
            "Item Id",
            "FromLocName",
            "ToLocName",
            "ActLocName",
            DEL
            ); //edit from Balance Qty  to Est. Balance Qty and add Item_Id by ki : 14-10-2013
        $parameter['show_column'] = $show_column;
        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['process_id'] = $process_id;
        $parameter['present_state'] = $present_state;
        $parameter['data_form'] = (array) $data_form;
        $parameter['renter_id'] = $renter_id;               //add by kik : 2013-12-12
        $parameter['owner_id'] = $owner_id;                 //add by kik : 2013-12-12
        $parameter['price_per_unit'] = $this->settings['price_per_unit'];                 //add by kik : 2013-12-12
// p($parameter); exit;
        $parameter['editable_suggestion'] = "{\"bSearchable\": true}";
        $parameter['editable_actual'] = "{\"bSearchable\": true}";
        // p($parameter); exit;
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Re-Location by Product Code'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'price_per_unit_table' => $price_per_unit_table
            // Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_receive"></i>'
            // END Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'button_action' => $data_form->str_buttun
        ));
    }

    #add function again for insert variable instead index number  : by kik : 2013-01-16

    function openRLProduct() {
        // p('openRLProduct');

        $this->load->controller('balance', 'balance');

        # show form for approve & confirm
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        //$user_id  = $this->input->post("user_id"); By Por 2013-10-02 comment post ค่าไว้ ใช้ session แทน
        //=====start by por 2013-10-02 ใช้ session แทนการ post
        $user_id = $this->session->userdata('user_id');
        //=====end
        $prod_list = $this->input->post("prod_list");
//      #Start add for set index from view : by kik : 20140115
        # Parameter Index Datatable
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_product_status = $this->input->post("ci_product_status");
        $ci_product_sub_status = $this->input->post("ci_product_sub_status");
        $ci_product_lot = $this->input->post("ci_product_lot");
        $ci_product_serial = $this->input->post("ci_product_serial");
        $ci_est_balance = $this->input->post("ci_est_balance");
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_old_loc_id = $this->input->post("ci_old_loc_id");
        $ci_sug_loc_id = $this->input->post("ci_sug_loc_id");
        $ci_act_loc_id = $this->input->post("ci_act_loc_id");
        $ci_remark = $this->input->post("ci_remark");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_dp_type_pallet = $this->input->post("ci_dp_type_pallet");
        // p( $ci_dp_type_pallet); exit;
//      #End add by kik : 20140115
        #ADD BY KIK 2014-01-16 เพิ่มเกี่ยวกับราคา
        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }
        #END ADD
        //p($_POST);
        # Parameter Order relocation header
        $est_relocate_date = $this->input->post("est_relocate_date");
        $relocate_date = $this->input->post("relocate_date");
        $worker_id = $this->input->post("worker_id");

        //$putaway_by = $this->rl->getUser($worker_id); comment by por 2013-10-02 สามารถใช้ worker_id ได้เลย เนื่องจากส่งค่ามาเป็น user_id อยู่แล้ว
        //start by por 2013-10-02 แก้ไขให้ใช้ $worker_id ได้เลย เนื่องจากส่งค่ามาเป็น user_id อยู่แล้วไม่จำเป็นต้องแปลงค่าอีกรอบ
        $putaway_by = $worker_id;
        //end

        $doc_type = '';
        $owner_id = $this->input->post("owner_id");
        $renter_id = $this->input->post("renter_id");
        $receive_type = '';
        $remark = '';
        $is_urgent = $this->input->post("is_urgent");   //add for ISSUE 3312 : by kik : 20140120\
        //add Urgent for ISSUE 3312 : by kik : 20140120
        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Parameter Order relocation detail
        $prod_list = $this->input->post("prod_list");

// p( $prod_list); exit;
        # Check validate data
        $return = array();
        $check_not_err = TRUE; //for check error all process , if process error set value = FALSE
        // Ball
        // Add Update to PD
        // Transaction Start
        $this->transaction_db->transaction_start();

        $response = $this->balance->_chkPDreservBeforeOpen($ci_reserv_qty, $ci_inbound_id, $prod_list, SEPARATOR); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการส่งค่าของตัวแยกให้กับฟังก์ชั่น $this->balance->xxx จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
        $return = array_merge_recursive($return, $response);
        if (!empty($return['critical'])) : //ถ้าพบ error
            $json['status'] = "validation";
            $json['return_val'] = $return;
        else:
            # generate Relocation Number
//            $document_no = createReLNo();
            $document_no = create_document_no_by_type("REL"); // Add by Ton! 20140428

            if (empty($document_no) || $document_no == ''):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not create document.";
            endif;

            if ($check_not_err):
                #create new Order and Order Detail
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

            if ($check_not_err):
                $est_relocate_date = convertDate($est_relocate_date, "eng", "iso", "-");

                $order = array(
                    'Flow_Id' => $flow_id
                    , 'Doc_Relocate' => strtoupper($document_no)
                    , 'Doc_Type' => strtoupper(iconv("UTF-8", "TIS-620", $doc_type))
                    , 'Process_Type' => $process_type
                    , 'Estimate_Action_Date' => $est_relocate_date
                    //,'Actual_Action_Date'	=> strtoupper(iconv("UTF-8","TIS-620",$doc_refer_ce))
                    , 'Owner_Id' => $owner_id
                    , 'Renter_Id' => $renter_id
                    , 'Create_By' => $user_id
                    , 'Create_Date' => date("Y-m-d H:i:s")              #edit from date("Y-m-d H-i-s") to date("Y-m-d H:i:s") : by kik : 2013-12-12
                    , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                    , 'Assigned_Id' => $worker_id
                    , 'Is_urgent' => $is_urgent
                );
                // p( $order); exit;
                $order_id = $this->rl->addReLocationOrder($order);
                if (empty($order_id) || $order_id == ""):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not Create new Order.";
                endif;
            endif;

            if ($check_not_err):
                $product_list = array();
//                $inbound_list = array();
                $all_product_move = array();
                if (!empty($prod_list)) :
                    foreach ($prod_list as $rows) :
                        $cut_data = explode(SEPARATOR, strip_tags($rows)); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
                        
                        $set_data = array(
                            "Order_Id" => $order_id	
                            , "Document_No" => strtoupper($document_no)
                            , "Suggest_Locacion_Id" => $this->lc->getLocationIdByCode(strtoupper(trim($cut_data[$ci_sug_loc_id])), '')
                            , "Actual_Locacion_Id" => $this->lc->getLocationIdByCode(strtoupper(trim($cut_data[$ci_act_loc_id])), '')
                            , "Reserv_Qty" => str_replace(",", "", $cut_data[$ci_reserv_qty])
                            , "Confirm_Qty" => 0
                            , "Remark" => iconv("UTF-8", "TIS-620", $cut_data[$ci_remark])
                            , "DP_Type_Pallet" => $cut_data[$ci_dp_type_pallet]
                        );
                        // p($set_data); exit;
                        list($set_col_insert, $set_col_val) = $this->set_column_insert_select_relocation($set_data);                        
                        
                        $str_query = "INSERT INTO STK_T_Relocate_Detail({$set_col_insert}) SELECT {$set_col_val} FROM STK_T_Inbound WHERE Inbound_Id = {$cut_data[$ci_inbound_id]}";
                        $return_query = $this->util_query_db->query($str_query);
                        
                        if ($return_query < 1):
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $error_message = "Can not add ReLocation OrderDetail.";
                            $return['critical'][]['message'] = $error_message;
                            log_message("error", " {$error_message} : {$str_query}");
                        endif;
                        
                        
                        
                        
//                        $info = $this->rl->inboundDetail($cut_data[$ci_inbound_id]);
//                        
//                        $p_detail = array();
//                        $p_detail['Order_Id'] = $order_id;
//                        $p_detail['Inbound_Item_Id'] = $cut_data[$ci_inbound_id];
//                        $p_detail['Document_No'] = $info[0]->Document_No;
//                        $p_detail['Doc_Refer_Int'] = $info[0]->Doc_Refer_Int;
//                        $p_detail['Doc_Refer_Ext'] = $info[0]->Doc_Refer_Ext;
//                        $p_detail['Doc_Refer_Inv'] = $info[0]->Doc_Refer_Inv;
//                        $p_detail['Doc_Refer_CE'] = $info[0]->Doc_Refer_CE;
//                        $p_detail['Doc_Refer_BL'] = $info[0]->Doc_Refer_BL;
//                        $p_detail['Doc_Refer_AWB'] = $info[0]->Doc_Refer_AWB;
//                        $p_detail['Product_Id'] = $info[0]->Product_Id;
//                        $p_detail['Product_Code'] = $info[0]->Product_Code;
//                        $p_detail['Product_Status'] = $info[0]->Product_Status;
//                        $p_detail['Product_Sub_Status'] = $info[0]->Product_Sub_Status;
//                        $p_detail['Suggest_Location_Id'] = $this->lc->getLocationIdByCode(strtoupper(trim($cut_data[$ci_sug_loc_id])), '');
//                        $p_detail['Actual_Location_Id'] = $this->lc->getLocationIdByCode(strtoupper(trim($cut_data[$ci_act_loc_id])), '');
//                        $p_detail['Old_Location_Id'] = $info[0]->Actual_Location_Id;
//                        $p_detail['Pallet_Id'] = $info[0]->Pallet_Id;
//                        $p_detail['Product_License'] = $info[0]->Product_License;
//                        $p_detail['Product_Lot'] = $info[0]->Product_Lot;
//                        $p_detail['Product_Serial'] = $info[0]->Product_Serial;
//                        $p_detail['Product_Mfd'] = $info[0]->Product_Mfd;
//                        $p_detail['Product_Exp'] = $info[0]->Product_Exp;
//                        $p_detail['Receive_Date'] = $info[0]->Receive_Date;
//                        $p_detail['Reserv_Qty'] = str_replace(",", "", $cut_data[$ci_reserv_qty]); //+++++ADD BY POR 2013-11-29 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
//                        $p_detail['Confirm_Qty'] = 0;
//                        $p_detail['Unit_Id'] = $info[0]->Unit_Id;
//                        $p_detail['Remark'] = iconv("UTF-8", "TIS-620", $cut_data[$ci_remark]);
//                        $p_detail['DP_Type_Pallet'] = $cut_data[$ci_dp_type_pallet];
//                        //ADD BY KIK 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
//                        if ($this->settings['price_per_unit'] == TRUE) :
//                            $p_detail['Price_Per_Unit'] = str_replace(",", "", $cut_data[$ci_price_per_unit]); //+++++ADD BY KIK 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
//                            $p_detail['Unit_Price_Id'] = $cut_data[$ci_unit_price_id];
//                            $p_detail['All_Price'] = str_replace(",", "", $cut_data[$ci_all_price]); //+++++ADD BY KIK 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
//                        endif;
////                        //END KIK
//
////                        p($p_detail);
//                        
//                        $all_product_move[] = $p_detail;
//                        }
                    endforeach; // close for each
                    //echo '<br> - - - - - - --  <br>';
                    //p($all_product_move);
                endif; // close if have prod
                
//                if (!empty($all_product_move)):
//                    $countInsert = $this->rl->addReLocationOrderDetail($all_product_move);
//                    if ($countInsert < 1):
//                        $check_not_err = FALSE;
//
//                        /**
//                         * Set Alert Zone (set Error Code, Message, etc.)
//                         */
//                        $return['critical'][]['message'] = "Can not add ReLocation OrderDetail.";
//                    endif;
//                endif;

                if ($check_not_err):
                    // Ball
                    // Add Update to PD
                    $status_update = $this->balance->updateOpenPDReservQty($ci_reserv_qty, $ci_inbound_id, $prod_list, SEPARATOR); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการส่งค่าของตัวแยกให้กับฟังก์ชั่น $this->balance->_chkPDreservBeforeOpen จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
                    if (!$status_update): //ถ้าไม่สามารถ update ได้ครบทุก record
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not update STK_T_Inbound.";
                    endif;
                endif;

            endif;
            
            #Set Message Alert
            if ($check_not_err):

                $array_return['success'][]['message'] = "Save Re-Location Product Complete.";
                $json['return_val'] = $array_return;

                $this->transaction_db->transaction_end();

            else:

                $array_return['critical'][]['message'] = "Save Re-Location Product Incomplete.";
                $json['return_val'] = array_merge_recursive($array_return, $return);

                $this->transaction_db->transaction_rollback();

            endif; //end set message alert


            $json['status'] = "save";
        endif;

        echo json_encode($json);
    }

//    function openRLProduct() {
////        p($this->input->post());exit();
//        $this->load->controller('balance', 'balance');
//
//        # show form for approve & confirm
//        $process_id = $this->input->post("process_id");
//        $present_state = $this->input->post("present_state");
//        $process_type = $this->input->post("process_type");
//        $action_type = $this->input->post("action_type");
//        $next_state = $this->input->post("next_state");
//        //$user_id  = $this->input->post("user_id"); By Por 2013-10-02 comment post ค่าไว้ ใช้ session แทน
//        //=====start by por 2013-10-02 ใช้ session แทนการ post
//        $user_id = $this->session->userdata('user_id');
//        //=====end
//        $prod_list = $this->input->post("prod_list");
//
//        //p($_POST);
//        # Parameter Order relocation header
//        $est_relocate_date = $this->input->post("est_relocate_date");
//        $relocate_date = $this->input->post("relocate_date");
//        $worker_id = $this->input->post("worker_id");
//
//        //$putaway_by = $this->rl->getUser($worker_id); comment by por 2013-10-02 สามารถใช้ worker_id ได้เลย เนื่องจากส่งค่ามาเป็น user_id อยู่แล้ว
//        //start by por 2013-10-02 แก้ไขให้ใช้ $worker_id ได้เลย เนื่องจากส่งค่ามาเป็น user_id อยู่แล้วไม่จำเป็นต้องแปลงค่าอีกรอบ
//        $putaway_by = $worker_id;
//        //end
//
//        $doc_type = '';
//        $owner_id = $this->input->post("owner_id");
//        $renter_id = $this->input->post("renter_id");
//        $receive_type = '';
//        $remark = '';
//
//        # Parameter Order relocation detail
//        $prod_list = $this->input->post("prod_list");
//
//        /*echo $this->balance->_chkPDreservBeforeOpen(7, 12, $prod_list, ",");
//        print_r($prod_list);
//	        foreach ($prod_list as $rows) {
//	        	$cut_data = explode(",", strip_tags($rows));
//	        	//echo strtoupper(trim($cut_data[9])) . "<br/>";
//	        	//echo $this->lc->getLocationIdByCode(strtoupper(trim($cut_data[9])), '');
//	        }
//
//
//        exit();*/
//        // Debug Area
//        //$response = $this->balance->_chkPDreservBeforeOpen(7, 12, $prod_list, ",");
//        //foreach ($prod_list as $rows) {
//        //$cut_data = explode(",", strip_tags($rows));
//        //print_r($cut_data);
//        //print_r($cut_data);
//        //}
//        //print_r($response);
//        //exit();
//        // End Debug
//        // Ball
//        // Add Update to PD
//        $response = $this->balance->_chkPDreservBeforeOpen(7, 12, $prod_list, ",");
//        if ($response == 0) {
//            $json['status'] = "E001";
//            $json['error_msg'] = "ERROR, Stock not enought";
//            echo json_encode($json);
//            exit();
//            // ROLL BACK
//        }
//        // END
//        # generate Relocation Number
//        $document_no = createReLNo();
//
//        # create new Order and Order Detail
//        $data['Document_No'] = $document_no;
////        list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $user_id, $data);
//        list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data); //Edit by Ton! 20131021
//
//        $est_relocate_date = convertDate($est_relocate_date, "eng", "iso", "-");
//        //$this->load->model("stock_model","stock");
//        $order = array(
//            'Flow_Id' => $flow_id
//            , 'Doc_Relocate' => strtoupper($document_no)
//            , 'Doc_Type' => strtoupper(iconv("UTF-8", "TIS-620", $doc_type))
//            , 'Process_Type' => $process_type
//            , 'Estimate_Action_Date' => $est_relocate_date
//            //,'Actual_Action_Date'	=> strtoupper(iconv("UTF-8","TIS-620",$doc_refer_ce))
//            , 'Owner_Id' => $owner_id
//            , 'Renter_Id' => $renter_id
//            , 'Create_By' => $user_id
//            , 'Create_Date' => date("Y-m-d H:i:s")              #edit from date("Y-m-d H-i-s") to date("Y-m-d H:i:s") : by kik : 2013-12-12
//            , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
//            , 'Assigned_Id' => $worker_id
//        );
//
//        $order_id = $this->rl->addReLocationOrder($order);
//        //$order_id=1;
//        $product_list = array();
//        $inbound_list = array();
//        $all_product_move = array();
//        if (count($prod_list) > 0) {
//            foreach ($prod_list as $rows) {
//                $cut_data = explode(",", strip_tags($rows));
//
////                p($cut_data);
//                if (!in_array($cut_data[12], $inbound_list)) {
//                    $inbound_list[] = $cut_data[12];
//                    $info = $this->rl->inboundDetail($cut_data[12]);
//
//                    $p_detail = array();
//                    $p_detail['Order_Id'] = $order_id;
//                    $p_detail['Inbound_Item_Id'] = $cut_data[12];
//                    $p_detail['Document_No'] = $info[0]->Document_No;
//                    $p_detail['Doc_Refer_Int'] = $info[0]->Doc_Refer_Int;
//                    $p_detail['Doc_Refer_Ext'] = $info[0]->Doc_Refer_Ext;
//                    $p_detail['Doc_Refer_Inv'] = $info[0]->Doc_Refer_Inv;
//                    $p_detail['Doc_Refer_CE'] = $info[0]->Doc_Refer_CE;
//                    $p_detail['Doc_Refer_BL'] = $info[0]->Doc_Refer_BL;
//                    $p_detail['Doc_Refer_AWB'] = $info[0]->Doc_Refer_AWB;
//                    $p_detail['Product_Id'] = $info[0]->Product_Id;
//                    $p_detail['Product_Code'] = $info[0]->Product_Code;
//                    $p_detail['Product_Status'] = $info[0]->Product_Status;
//                    $p_detail['Product_Sub_Status'] = $info[0]->Product_Sub_Status;
//                    $p_detail['Suggest_Location_Id'] = $this->lc->getLocationIdByCode(strtoupper(trim($cut_data[9])), '');
//                    $p_detail['Actual_Location_Id'] = '';
//                    $p_detail['Old_Location_Id'] = $info[0]->Actual_Location_Id;
//                    $p_detail['Pallet_Id'] = $info[0]->Pallet_Id;
//                    $p_detail['Product_License'] = $info[0]->Product_License;
//                    $p_detail['Product_Lot'] = $info[0]->Product_Lot;
//                    $p_detail['Product_Serial'] = $info[0]->Product_Serial;
//                    $p_detail['Product_Mfd'] = $info[0]->Product_Mfd;
//                    $p_detail['Product_Exp'] = $info[0]->Product_Exp;
//                    $p_detail['Receive_Date'] = $info[0]->Receive_Date;
//                    $p_detail['Reserv_Qty'] = str_replace(",", "", $cut_data[7]); //+++++ADD BY POR 2013-11-29 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
//                    $p_detail['Confirm_Qty'] = 0;
//                    $p_detail['Unit_Id'] = $info[0]->Unit_Id;
//                    $p_detail['Remark'] = iconv("UTF-8", "TIS-620", $cut_data[11]);
//                    $all_product_move[] = $p_detail;
//                }
//            } // close for each
//            //echo '<br> - - - - - - --  <br>';
//            //p($all_product_move);
//        } // close if have prod
//        if (count($all_product_move) != 0) {
//            $this->rl->addReLocationOrderDetail($all_product_move);
//        }
//
//        // Ball
//        // Add Update to PD
//        $this->balance->updateOpenPDReservQty(7, 12, $prod_list, ",");
//
//        $json['status'] = "C001";
//        $json['error_msg'] = "";
//        echo json_encode($json);
//    }


function get_location(){
    header('Access-control-Allow-0rigin: *');
    $params = $this->input->get();
    $this->load->library('getlocation_no_dispatch_area');
    $data = $this->getlocation_no_dispatch_area->get_location($params);
}


    function showProduct() {
        $conf = $this->config->item('_xml');
        $conf_re_location_mover_qty = empty($conf['re_location_mover_qty'])?false:@$conf['re_location_mover_qty']; 
        if($conf_re_location_mover_qty == TRUE){
        $move_qty = "TRUE";
        }else{
         $move_qty = "FALSE";
        }
    //  p( $move_qty); exit;
        $inb_code = $this->input->post("post_val");
        $inb = $inb_code[0];
        $getmaster = $this->rl->get_suggest_by_product_master($inb);
        $locmaster =  $getmaster[0]->Location_Code;
                // p( $locmaster); exit;
        //ADD BY POR 2014-02-19 รับค่า dispatch type เพื่อส่งกลับไปใน datatable ด้วย
        $dp_type_pallet = $this->input->post("dp_type_pallet_val");
        if ($dp_type_pallet == "NULL"):
            $dp_type_pallet = NULL;
        endif;
        //END ADD;

        $json['status'] = "1";
        $json['error_msg'] = "";

        $product_list = $this->rl->getProductInLocationFromArray($inb_code);
        // p($product_list); exit;
        $new_list = array();
        foreach ($product_list as $rows) {
            $rows['DP_Type_Pallet'] = $dp_type_pallet; //ADD BY POR 2014-02-19 เพิ่มให้ส่งค่า dispatch type กลับไปด้วย
            $rows['Balance_Qty'] = set_number_format($rows['Balance_Qty']);
            $rows['Location_Code_master'] = $locmaster;
            $rows['xml_move_qty'] =  $move_qty;
            $new_list[] = thai_json_encode($rows);
        }

        $json['locations'] = $new_list; // โค้ดใหม่ส่งค่าโดยใช้ตัวแปรที่แปลง array เป็นรูปแบบของ json แล้ว

// p($new_list); exit;
        echo json_encode($json);
    }

    #End New Comment Code #ISSUE 2190
    #=======================================================================================
    #Start Old Comment Code #ISSUE 2190
    #=======================================================================================
//        function openRLProductFrom(){
//		# show form open new order Re-Location
//		$process_id=10;
//		$present_state=0;
//		#Get Renter [worker] list
//		$query_worker = $this->contact->getWorkerAll();
//		$result_worker = $query_worker->result();
//		$worker_list = genOptionDropdown($result_worker,"CONTACT");
//		$parameter['worker_list'] = $worker_list;
//		$parameter['worker_id']='';
//		//$parameter=array();
//
//		$data_form = $this->workflow->openWorkflowForm($process_id,$present_state);
//		//p($data_form);
//
//		$show_column  = array( "Running No.","Product Code","Product Status","Lot","Serial","Balance Qty","Move Qty","Location From","Suggest Location To","Actual Location To","Remark","Inbound",DEL);
//		$parameter['show_column']=$show_column;
//		$parameter['user_id']		= $this->session->userdata('user_id');
//		$parameter['process_id']	= $process_id;
//		$parameter['present_state'] = $present_state;
//		$parameter['data_form']		= (array)$data_form;
//
//		$str_form = $this->parser->parse("form/".$data_form->form_name,$parameter,TRUE);
//
//		# PUT FORM IN TEMPLATE WORKFLOW
//		$this->parser->parse('workflow_template', array(
//			 'state_name' => 'Re-Location by Product Code'
//			 ,'menu' => $this->menu->loadMenu()
//			 ,'form' => $str_form
//			 ,'button_back'  => '<INPUT TYPE="button" class="'.$this->config->item('css_button').'"	VALUE="'.BACK.'" ONCLICK="history.back()">'
//			 ,'button_cancel'  => '<INPUT TYPE="button" class="'.$this->config->item('css_button').'"	VALUE="'.CANCEL.'" ONCLICK="cancel()">'
//			 ,'button_action' => $data_form->str_buttun
//		));
//	}
//
//	function productList(){
//		# jquery show product on STK_T_Inbound
//		$product_code = $this->input->post("post_val");
//		//echo '<br> code = '.$location_code;
//
//		$product_result=$this->p->getProductStock($product_code);
//		$product_list=$product_result->result();
//
//		$data		= array();
//		$data_list	= array();
//		$action		= array();
//		$action_module = "";
//		$column = array("Selection","Product Code","Product Name","Product Status","Lot","Serial","Balance QTY","Location");
//		if(is_array($product_list) && count($product_list)){
//			$count = 1;
//			foreach($product_list as $rows){
//				$data['Id']	= "<input ID=chkBoxVal type=checkbox name=chkBoxVal[] value='".$rows->Inbound_Id."' id=chkBoxVal" . $rows->Location_Id . " onClick='getCheckValue(this)'>";
//				$data['Product_Code']	= $rows->Product_Code;
//				$data['Product_Name']	= $rows->Product_NameEN;
//				$data['Product_Status']	= $rows->Product_Status;
//				$data['Lot']	= $rows->Product_Lot;
//				$data['Serial']	= $rows->Product_Serial;
//				$data['Balance_Qty']=$rows->Receive_Qty - ($rows->Dispatch_Qty + $rows->Adjust_Qty);
//				$data['Location']	= $rows->Location_Code;
//				$count++;
//				$data_list[] = (object)$data;
//			}
//		}
//		$datatable = $this->datatable->genCustomiztable($data_list ,$column ,$action_module ,$action);
//
//		#########################################
//		$this->load->model("encoding_conversion", "conv");
//		echo $this->conv->tis620_to_utf8($datatable);
//	}
//        function openRLProduct(){
//		# show form for approve & confirm
//		$process_id		= $this->input->post("process_id");
//		$present_state	= $this->input->post("present_state");
//		$process_type	= $this->input->post("process_type");
//		$action_type	= $this->input->post("action_type");
//		$next_state		= $this->input->post("next_state");
//		$user_id		= $this->input->post("user_id");
//		$prod_list		= $this->input->post("prod_list");
//		//p($_POST);
//
//		# Parameter Order relocation header
//		$est_relocate_date=$this->input->post("est_relocate_date");
//		$relocate_date=$this->input->post("relocate_date");
//		$worker_id=$this->input->post("worker_id");
//		$putaway_by=$this->rl->getUser($worker_id);
//		$doc_type='';
//		$owner_id=$this->input->post("owner_id");
//		$renter_id=$this->input->post("renter_id");
//		$receive_type='';
//		$remark='';
//
//		# Parameter Order relocation detail
//		$prod_list			= $this->input->post("prod_list");
//
//		# generate Relocation Number
//		$document_no = createReLNo();
//
//		# create new Order and Order Detail
//		$data['Document_No']	= $document_no;
//		list($flow_id,$action_id) = $this->workflow->addNewWorkflow($process_id ,$present_state ,$action_type ,$next_state ,$user_id ,$data);
//
//		$est_relocate_date = convertDate($est_relocate_date,"eng","iso","-");
//		//$this->load->model("stock_model","stock");
//		$order = array(
//			 'Flow_Id'			=> $flow_id
//		    ,'Doc_Relocate'		=> strtoupper($document_no)
//			,'Doc_Type'			=> strtoupper(iconv("UTF-8","TIS-620",$doc_type))
//			,'Process_Type'		=> $process_type
//			,'Estimate_Action_Date' =>$est_relocate_date
//			//,'Actual_Action_Date'	=> strtoupper(iconv("UTF-8","TIS-620",$doc_refer_ce))
//			,'Owner_Id'			=> $owner_id
//			,'Renter_Id'		=> $receive_type
//			,'Create_By'		=> $user_id
//			,'Create_Date'		=> date("Y-m-d H-i-s")
//			,'Remark'			=> iconv("UTF-8","TIS-620",$remark)
//			,'Assigned_Id'		=> $worker_id
//		);
//
//		$order_id = $this->rl->addReLocationOrder($order);
//		//$order_id=1;
//		$product_list=array();
//		$inbound_list=array();
//		$all_product_move=array();
//		if(count($prod_list)>0){
//			foreach($prod_list as $rows){
//				 $cut_data  = explode(",",strip_tags($rows));
//
//				 //p($cut_data);
//				 if(!in_array($cut_data[11],$inbound_list)){
//					 $inbound_list[]=$cut_data[11];
//					 $info=$this->rl->inboundDetail($cut_data[11]);
//					 $p_detail=array();
//						$p_detail['Order_Id']=$order_id;
//						$p_detail['Inbound_Item_Id']=$cut_data[11];
//						$p_detail['Document_No']=$info[0]->Document_No;
//						$p_detail['Doc_Refer_Int']=$info[0]->Doc_Refer_Int;
//						$p_detail['Doc_Refer_Ext']=$info[0]->Doc_Refer_Ext;
//						$p_detail['Doc_Refer_Inv']=$info[0]->Doc_Refer_Inv;
//						$p_detail['Doc_Refer_CE']=$info[0]->Doc_Refer_CE;
//						$p_detail['Doc_Refer_BL']=$info[0]->Doc_Refer_BL;
//						$p_detail['Doc_Refer_AWB']=$info[0]->Doc_Refer_AWB;
//						$p_detail['Product_Id']=$info[0]->Product_Id;
//						$p_detail['Product_Code']=$info[0]->Product_Code;
//						$p_detail['Product_Status']=$info[0]->Product_Status;
//						$p_detail['Product_Sub_Status']=$info[0]->Product_Sub_Status;
//						$p_detail['Suggest_Location_Id']=$this->lc->getLocationIdByCode(trim($cut_data[8]),'');
//						$p_detail['Actual_Location_Id']='';
//						$p_detail['Old_Location_Id']=$info[0]->Actual_Location_Id;
//						$p_detail['Pallet_Id']=$info[0]->Pallet_Id;
//						$p_detail['Product_License']=$info[0]->Product_License;
//						$p_detail['Product_Lot']=$info[0]->Product_Lot;
//						$p_detail['Product_Serial']=$info[0]->Product_Serial;
//						$p_detail['Product_Mfd']=$info[0]->Product_Mfd;
//						$p_detail['Product_Exp']=$info[0]->Product_Exp;
//						$p_detail['Receive_Date']=$info[0]->Receive_Date;
//						$p_detail['Reserv_Qty']=$cut_data[6];
//						$p_detail['Confirm_Qty']=0;
//						$p_detail['Unit_Id']=$info[0]->Unit_Id;
//						$p_detail['Remark']=iconv("UTF-8","TIS-620",$cut_data[10]);
//						$all_product_move[]=$p_detail;
//				 }
//			} // close for each
//			//echo '<br> - - - - - - --  <br>';
//			//p($all_product_move);
//		} // close if have prod
//		if(count($all_product_move)!=0){
//			$this->rl->addReLocationOrderDetail($all_product_move);
//		}
//		$json['status'] = "C001";
//		$json['error_msg'] = "";
//		echo json_encode($json);
//	}
//
//	function showProduct() {
//            $inb_code = $this->input->post("post_val");
//
//            //$query = $this->rl->getProductInLocationFromArray($inb_code);
//            //$location_list = $query->result();;
//            $location_list = $this->rl->getProductInLocationFromArray($inb_code);
//            //p($location_list);
//
//            $json['status'] = "1";
//            $json['error_msg'] = "";
//
//            $json['locations'] = $location_list;
//
//            echo json_encode($json);
//        }
    #End New Comment Code #ISSUE 2190
    #=======================================================================================

    function getSgProductLocation() {
        # jquery show select option suggest location
        $product_code = $this->input->post('product_code');
        $location_code = $this->input->post('location_code');
        $select_id = trim($this->input->post('select_id'));
        $product_status = $this->input->post('product_status');
//        $result = $this->suggest_location->getSuggestLocationArray("suggestLocation", $product_code, $product_status, 0); // Comment By Akkarapol, 23/09/2013,
        $result = $this->suggest_location->getSuggestLocationArray("suggestLocation", $product_code, $product_status, 1); // Add By Akkarapol, 23/09/2013, เพิ่มเข้าไปเพื่อให้ใช้ rule ในการคำนวนหา Suggest Location เพราะตอนนี้ การหา SuggestLocaion มันจะต้องใช้ rule = 1 เท่านั้น ใส่ 0 ไปแล้วค่าไม่ออก
        //p($result);
        $list = '';
        //$location_list=$location_result->result();
        $list.='
				<option value="">Suggest Location</option>
				';
        foreach ($result as $data) {
            $check = '';
            if ($data['Location_Code'] == $select_id) {
                $check = ' selected="selected"';
            }
            $list.='<option value="' . $data['Location_Code'] . '" ' . $check . '>' . $data['Location_Code'] . '</option>';
        }
        echo $list;
        //getSuggestLocation($type,$product_code,$product_status,$used_rule)
    }

    function getSgLocationAll() {
        # jquery show select option location can put select all location not full
        $product_code = $this->input->post('product_code');
        $location_code = $this->input->post('location_code');
        $select_id = trim($this->input->post('select_id'));
        $product_status = $this->input->post('product_status');
        $location_list = $this->rl->showLocationAll($location_code);

        $list = '';
        $list.='
				<option value="">Actual Location</option>
				';
        foreach ($location_list as $loc) {
            $check = '';
            if ($loc->Location_Code == $select_id) {
                $check = ' selected="selected"';
            }
            $list.='<option value="' . $loc->Location_Code . '" ' . $check . '>' . $loc->Location_Code . '</option>';
        }
        echo $list;
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
        $flow_detail = $this->rl->getRelocationOrder($flow_id);
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

        $order_detail = $this->rl->getReLocationProductDetail($order_id);
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
        $result_worker = $this->contact->getWorkerAllWithUserLogin()->result();
        $worker_list = genOptionDropdown($result_worker, "CONTACTWITHUSERLOGIN");
        //end


        $parameter['worker_list'] = $worker_list;
        $parameter['worker_id'] = $flow_detail[0]->Assigned_Id;

//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "reLocationProduct"); // Button Permission. Add by Ton! 20140131|
        // p($data_form);
        // exit;
        #start add code for ISSUE 3320 :  by kik : 20140116
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
        $data_form->str_buttun .= '<input class="button dark_blue" type="button" onclick="exportFile(\'PDF\')" value="PDF">'; // Add By Akkarapol, 17/10/2013, เพิ่มปุ่ม PRINT สำหรับ Generate PDF เพื่อออกใบ Relocation Job



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
// p($data_form->form_name); exit;

        # LOAD FORM
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

    function confirmRLProduct() {

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
            $set_return['message'] = "Confirm Re-Location Product Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;

            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();
        else:
            $array_return['critical'][]['message'] = "Confirm Re-Location Product Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);

            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();
        endif;

        echo json_encode($json);
    }

    function quickApproveAction(){
        $this->approveRLProduct();
    }
    
    
    function approveRLProduct() {
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
        $post_data = $this->input->post(); 
        // p($post_data);
        // exit;
        // validate token
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));
        // p('approveRLProduct');
        // p($token);
        // p($flow_id);
        // p($flow_info[0]->Present_State);
        // p($flow_info[0]->Process_Id);
        // exit;
        $response = validate_token($token, $flow_id, $flow_info[0]->Present_State, $flow_info[0]->Process_Id);
        // p($response);
        // exit;

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

        // p($this->input->post()); exit();
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
            // p( $flow_detail); exit;
            if ($flow_detail == "" || empty($flow_detail)):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not get data Relocation order.";
            /*
             * LOG MSG 2
             */
            else:
                $document_no = $flow_detail[0]->Document_No;
                $order_id = $flow_detail[0]->Order_Id;
                // p($order_id); exit;
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
                    // p($result_order_query); exit;
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
                                // p($p_detail); exit;
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

                $set_return['message'] = "Approve Re-Location Product Complete.";
                $return['success'][] = $set_return;
                $json['status'] = "save";
                $json['return_val'] = $return;

                /**
                 * ================== Auto End Transaction =========================
                 */
                $this->transaction_db->transaction_end();

            else:

                $array_return['critical'][]['message'] = "Approve Re-Location Product Incomplete.";
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

    #ISSUE 3034 Reject Document
    #DATE:2013-12-26
    #BY:KIK
    #เพิ่มในส่วนของ reject
    #START New Comment Code #ISSUE 3034 Reject Document
    #add code for reject

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
                $return['critical'][]['message'] = "Can not update Relocation Detail.";
            endif;
        endif;


        if ($check_not_err):
            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

            $set_return['message'] = "Reject and Return Re-Location Product Complete.";
            $return['success'][] = $set_return;
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();
            $set_return['critical'][]['message'] = "Reject and Return Re-Location Incomplete.";
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
                echo "<script>alert('Delete Re-Location Product Complete.');</script>";
                redirect('reLocationProduct', 'refresh');
                #if data send from form page When return data to form page
            } else {
                $array_return['success'][]['message'] = "Reject Re-Location Product Complete";
                $json['return_val'] = $array_return;
            }
        else:
            $this->transaction_db->transaction_rollback();

            #if insert data not done . when return rollback and not use database
            #if data send from list page When refesh page
            if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {
                echo "<script>alert('Delete Re-Location Product not complete. Please check?');</script>";
                redirect('reLocationProduct', 'refresh');
                #if data send from form page When return data to form page
            } else {
                $array_return['critical'][]['message'] = "Reject Re-Location Product Incomplete";
                $json['return_val'] = $array_return;
            }
        endif;

        $json['status'] = "save";
        echo json_encode($json);
    }
    
    
    /**
     * function for return column of insert by select STK_T_Relocate_Detail
     * @param type $set_data
     * @return type
     */
    function set_column_insert_select_relocation($set_data){
        
        $all_column_inbound = $this->util_query_db->select_table("INFORMATION_SCHEMA.COLUMNS", "COLUMN_NAME", array("TABLE_NAME" => "STK_T_Inbound"));
        $all_column_relocate_detail = $this->util_query_db->select_table("INFORMATION_SCHEMA.COLUMNS", "COLUMN_NAME", array("TABLE_NAME" => "STK_T_Relocate_Detail"));

        $set_col_merge = array();
        foreach($all_column_inbound->result_array() as $key_col_inbound => $col_inbound):
            $set_col_merge[$col_inbound['COLUMN_NAME']] = '';
        endforeach;

        unset($set_col_merge['Inbound_Id']);
        unset($set_col_merge['Receive_Type']);
        unset($set_col_merge['Putaway_Date']);
        unset($set_col_merge['Putaway_By']);
        unset($set_col_merge['Receive_Qty']);
        unset($set_col_merge['PD_Reserv_Qty']);
        unset($set_col_merge['PK_Reserv_Qty']);
        unset($set_col_merge['Dispatch_Qty']);
        unset($set_col_merge['Balance_Qty']);
        unset($set_col_merge['Adjust_Qty']);
        unset($set_col_merge['Owner_Id']);
        unset($set_col_merge['Renter_Id']);
        unset($set_col_merge['History_Item_Id']);
        unset($set_col_merge['Is_Pending']);
        unset($set_col_merge['Is_Partial']);
        unset($set_col_merge['Is_Repackage']);
        unset($set_col_merge['Unlock_Pending_Date']);
        unset($set_col_merge['Lock_Id']);
        unset($set_col_merge['Active']);
        unset($set_col_merge['Flow_Id']);
        unset($set_col_merge['Activity_Involve']);
        unset($set_col_merge['Is_Count']);
        unset($set_col_merge['Old_Location_Code']);
        unset($set_col_merge['Item_Id']);

        foreach($all_column_relocate_detail->result_array() as $key_col_relocate_detail => $col_relocate_detail):
            if (array_key_exists($col_relocate_detail['COLUMN_NAME'], $set_col_merge)) :
                $set_col_merge[$col_relocate_detail['COLUMN_NAME']] = $col_relocate_detail['COLUMN_NAME'];
            endif;
        endforeach;

        $set_col_merge['Old_Location_Id'] = 'Actual_Location_Id';
        $set_col_merge['Inbound_Item_Id'] = 'Inbound_Id';
        $set_col_merge['Order_Id'] = $set_data['Order_Id'];
        $set_col_merge['Document_No'] = "'".$set_data['Document_No']."'";
        $set_col_merge['Reserv_Qty'] = $set_data['Reserv_Qty'];
        $set_col_merge['Confirm_Qty'] = $set_data['Confirm_Qty'];
        $set_col_merge['Suggest_Location_Id'] = $set_data['Suggest_Locacion_Id'];
        if(empty($set_col_merge['Suggest_Location_Id'])):
            $set_col_merge['Suggest_Location_Id'] = "''";
        endif;
        $set_col_merge['Actual_Location_Id'] = $set_data['Actual_Locacion_Id'];
        if(empty($set_col_merge['Actual_Location_Id'])):
            $set_col_merge['Actual_Location_Id'] = "''";
        endif;
        $set_col_merge['Remark'] = $set_data['Remark'];
        if(empty($set_col_merge['Remark'])):
            $set_col_merge['Remark'] = "''";
        endif;
        $set_col_merge['DP_Type_Pallet'] = $set_data['DP_Type_Pallet'];
        if(empty($set_col_merge['DP_Type_Pallet'])):
            $set_col_merge['DP_Type_Pallet'] = "''";
        endif;

        $set_col_insert = array_keys($set_col_merge);
        $set_col_insert = implode(',', $set_col_insert);
        $set_col_val = implode(',', $set_col_merge);    
        
        return array($set_col_insert, $set_col_val);
        
    }

    private function division($a,$b){
        return $a/$b;
    }
    
    public function unit_test(){
        echo "Using Unit Test Library";	
        $test = $this->division(6,3);
        $expected_result = 2;
        $test_name = "Division test function";
        echo $this->unit->run($test,$expected_result,$test_name);
    }

       
    function approveRLProduct_complete() {
        #Load config
        $conf = $this->config->item('_xml');
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];
        /**
         * set Variable
         */

        // ###############################################################################################################

        
        $this->load->controller('balance', 'balance');

        # show form for approve & confirm
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        //$user_id  = $this->input->post("user_id"); By Por 2013-10-02 comment post ค่าไว้ ใช้ session แทน
        //=====start by por 2013-10-02 ใช้ session แทนการ post
        $user_id = $this->session->userdata('user_id');
        //=====end
        $prod_list = $this->input->post("prod_list");
//      #Start add for set index from view : by kik : 20140115
        # Parameter Index Datatable
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_product_status = $this->input->post("ci_product_status");
        $ci_product_sub_status = $this->input->post("ci_product_sub_status");
        $ci_product_lot = $this->input->post("ci_product_lot");
        $ci_product_serial = $this->input->post("ci_product_serial");
        $ci_est_balance = $this->input->post("ci_est_balance");
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_old_loc_id = $this->input->post("ci_old_loc_id");
        $ci_sug_loc_id = $this->input->post("ci_sug_loc_id");
        $ci_act_loc_id = $this->input->post("ci_act_loc_id");
        $ci_remark = $this->input->post("ci_remark");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_dp_type_pallet = $this->input->post("ci_dp_type_pallet");

        // p( $ci_dp_type_pallet); exit;
//      #End add by kik : 20140115
        #ADD BY KIK 2014-01-16 เพิ่มเกี่ยวกับราคา
        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }
        #END ADD
        //p($_POST);
        # Parameter Order relocation header
        $est_relocate_date = $this->input->post("est_relocate_date");
        $relocate_date = $this->input->post("relocate_date");
        $worker_id = $this->input->post("worker_id");

        //$putaway_by = $this->rl->getUser($worker_id); comment by por 2013-10-02 สามารถใช้ worker_id ได้เลย เนื่องจากส่งค่ามาเป็น user_id อยู่แล้ว
        //start by por 2013-10-02 แก้ไขให้ใช้ $worker_id ได้เลย เนื่องจากส่งค่ามาเป็น user_id อยู่แล้วไม่จำเป็นต้องแปลงค่าอีกรอบ
        $putaway_by = $worker_id;
        //end

        $doc_type = '';
        $owner_id = $this->input->post("owner_id");
        $renter_id = $this->input->post("renter_id");
        $receive_type = '';
        $remark = '';
        $is_urgent = $this->input->post("is_urgent");   //add for ISSUE 3312 : by kik : 20140120\
        //add Urgent for ISSUE 3312 : by kik : 20140120
        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Parameter Order relocation detail
        $prod_list = $this->input->post("prod_list");

// p( $prod_list); exit;
        # Check validate data
        $return = array();
        $check_not_err = TRUE; //for check error all process , if process error set value = FALSE
        // Ball
        // Add Update to PD
        // Transaction Start
        $this->transaction_db->transaction_start();

        $response = $this->balance->_chkPDreservBeforeOpen($ci_reserv_qty, $ci_inbound_id, $prod_list, SEPARATOR); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการส่งค่าของตัวแยกให้กับฟังก์ชั่น $this->balance->xxx จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
        $return = array_merge_recursive($return, $response);
        if (!empty($return['critical'])) : //ถ้าพบ error
            $json['status'] = "validation";
            $json['return_val'] = $return;
        else:
            # generate Relocation Number
//            $document_no = createReLNo();
            $document_no = create_document_no_by_type("REL"); // Add by Ton! 20140428

            if (empty($document_no) || $document_no == ''):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not create document.";
            endif;

            if ($check_not_err):
                #create new Order and Order Detail

                #Condition for ShotProcess To Complet
                // if($next_state == -2){
                //     $next_state = 0;
                // }
                #Condition for ShotProcess To Complet

                $data['Document_No'] = $document_no;
                list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data); //Edit by Ton! 20131021

                $flow_id_from_create = $flow_id;

                if (empty($flow_id) || $flow_id == '' || empty($action_id) || $action_id == ''):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not Create new Workflow.";
                endif;
            endif;

            if ($check_not_err):
                $est_relocate_date = convertDate($est_relocate_date, "eng", "iso", "-");

                $order = array(
                    'Flow_Id' => $flow_id
                    , 'Doc_Relocate' => strtoupper($document_no)
                    , 'Doc_Type' => strtoupper(iconv("UTF-8", "TIS-620", $doc_type))
                    , 'Process_Type' => $process_type
                    , 'Estimate_Action_Date' => $est_relocate_date
                    //,'Actual_Action_Date'	=> strtoupper(iconv("UTF-8","TIS-620",$doc_refer_ce))
                    , 'Owner_Id' => $owner_id
                    , 'Renter_Id' => $renter_id
                    , 'Create_By' => $user_id
                    , 'Create_Date' => date("Y-m-d H:i:s")              #edit from date("Y-m-d H-i-s") to date("Y-m-d H:i:s") : by kik : 2013-12-12
                    , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                    , 'Assigned_Id' => $worker_id
                    , 'Is_urgent' => $is_urgent
                );
                // p( $order); exit;
                $order_id = $this->rl->addReLocationOrder($order);
               
                if (empty($order_id) || $order_id == ""):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not Create new Order.";
                endif;
            endif;

            if ($check_not_err):
                $product_list = array();
//                $inbound_list = array();
                $all_product_move = array();
                if (!empty($prod_list)) :
                    foreach ($prod_list as $rows) :
                        $cut_data = explode(SEPARATOR, strip_tags($rows)); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
                        
                        $set_data = array(
                            "Order_Id" => $order_id	
                            , "Document_No" => strtoupper($document_no)
                            , "Suggest_Locacion_Id" => $this->lc->getLocationIdByCode(strtoupper(trim($cut_data[$ci_sug_loc_id])), '')
                            , "Actual_Locacion_Id" => $this->lc->getLocationIdByCode(strtoupper(trim($cut_data[$ci_act_loc_id])), '')
                            , "Reserv_Qty" => str_replace(",", "", $cut_data[$ci_reserv_qty])
                            , "Confirm_Qty" => 0
                            , "Remark" => iconv("UTF-8", "TIS-620", $cut_data[$ci_remark])
                            , "DP_Type_Pallet" => $cut_data[$ci_dp_type_pallet]
                        );
                        // p($set_data); exit;
                        list($set_col_insert, $set_col_val) = $this->set_column_insert_select_relocation($set_data);                        

                        $str_query = "INSERT INTO STK_T_Relocate_Detail({$set_col_insert}) SELECT {$set_col_val} FROM STK_T_Inbound WHERE Inbound_Id = {$cut_data[$ci_inbound_id]}";

                        $return_query = $this->util_query_db->query($str_query);

                        if ($return_query < 1):
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $error_message = "Can not add ReLocation OrderDetail.";
                            $return['critical'][]['message'] = $error_message;
                            log_message("error", " {$error_message} : {$str_query}");
                        endif;
                        
                        
                        
                        
//                        $info = $this->rl->inboundDetail($cut_data[$ci_inbound_id]);
//                        
//                        $p_detail = array();
//                        $p_detail['Order_Id'] = $order_id;
//                        $p_detail['Inbound_Item_Id'] = $cut_data[$ci_inbound_id];
//                        $p_detail['Document_No'] = $info[0]->Document_No;
//                        $p_detail['Doc_Refer_Int'] = $info[0]->Doc_Refer_Int;
//                        $p_detail['Doc_Refer_Ext'] = $info[0]->Doc_Refer_Ext;
//                        $p_detail['Doc_Refer_Inv'] = $info[0]->Doc_Refer_Inv;
//                        $p_detail['Doc_Refer_CE'] = $info[0]->Doc_Refer_CE;
//                        $p_detail['Doc_Refer_BL'] = $info[0]->Doc_Refer_BL;
//                        $p_detail['Doc_Refer_AWB'] = $info[0]->Doc_Refer_AWB;
//                        $p_detail['Product_Id'] = $info[0]->Product_Id;
//                        $p_detail['Product_Code'] = $info[0]->Product_Code;
//                        $p_detail['Product_Status'] = $info[0]->Product_Status;
//                        $p_detail['Product_Sub_Status'] = $info[0]->Product_Sub_Status;
//                        $p_detail['Suggest_Location_Id'] = $this->lc->getLocationIdByCode(strtoupper(trim($cut_data[$ci_sug_loc_id])), '');
//                        $p_detail['Actual_Location_Id'] = $this->lc->getLocationIdByCode(strtoupper(trim($cut_data[$ci_act_loc_id])), '');
//                        $p_detail['Old_Location_Id'] = $info[0]->Actual_Location_Id;
//                        $p_detail['Pallet_Id'] = $info[0]->Pallet_Id;
//                        $p_detail['Product_License'] = $info[0]->Product_License;
//                        $p_detail['Product_Lot'] = $info[0]->Product_Lot;
//                        $p_detail['Product_Serial'] = $info[0]->Product_Serial;
//                        $p_detail['Product_Mfd'] = $info[0]->Product_Mfd;
//                        $p_detail['Product_Exp'] = $info[0]->Product_Exp;
//                        $p_detail['Receive_Date'] = $info[0]->Receive_Date;
//                        $p_detail['Reserv_Qty'] = str_replace(",", "", $cut_data[$ci_reserv_qty]); //+++++ADD BY POR 2013-11-29 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
//                        $p_detail['Confirm_Qty'] = 0;
//                        $p_detail['Unit_Id'] = $info[0]->Unit_Id;
//                        $p_detail['Remark'] = iconv("UTF-8", "TIS-620", $cut_data[$ci_remark]);
//                        $p_detail['DP_Type_Pallet'] = $cut_data[$ci_dp_type_pallet];
//                        //ADD BY KIK 2014-01-16 เพิ่มเกี่ยวกับบันทึกราคาด้วย
//                        if ($this->settings['price_per_unit'] == TRUE) :
//                            $p_detail['Price_Per_Unit'] = str_replace(",", "", $cut_data[$ci_price_per_unit]); //+++++ADD BY KIK 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
//                            $p_detail['Unit_Price_Id'] = $cut_data[$ci_unit_price_id];
//                            $p_detail['All_Price'] = str_replace(",", "", $cut_data[$ci_all_price]); //+++++ADD BY KIK 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
//                        endif;
////                        //END KIK
//
////                        p($p_detail);
//                        
//                        $all_product_move[] = $p_detail;
//                        }
                    endforeach; // close for each
                    //echo '<br> - - - - - - --  <br>';
                    //p($all_product_move);
                endif; // close if have prod
                
//                if (!empty($all_product_move)):
//                    $countInsert = $this->rl->addReLocationOrderDetail($all_product_move);
//                    if ($countInsert < 1):
//                        $check_not_err = FALSE;
//
//                        /**
//                         * Set Alert Zone (set Error Code, Message, etc.)
//                         */
//                        $return['critical'][]['message'] = "Can not add ReLocation OrderDetail.";
//                    endif;
//                endif;

                if ($check_not_err):
                    // Ball
                    // Add Update to PD
                    $status_update = $this->balance->updateOpenPDReservQty($ci_reserv_qty, $ci_inbound_id, $prod_list, SEPARATOR); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการส่งค่าของตัวแยกให้กับฟังก์ชั่น $this->balance->_chkPDreservBeforeOpen จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
                    if (!$status_update): //ถ้าไม่สามารถ update ได้ครบทุก record
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not update STK_T_Inbound.";
                    endif;
                endif;

            endif;
            
            #Set Message Alert
            if ($check_not_err):

                $array_return['success'][]['message'] = "Save Re-Location Product Complete.";
                $json['return_val'] = $array_return;

                $this->transaction_db->transaction_end();

            else:

                $array_return['critical'][]['message'] = "Save Re-Location Product Incomplete.";
                $json['return_val'] = array_merge_recursive($array_return, $return);

                $this->transaction_db->transaction_rollback();

            endif; //end set message alert


            $json['status'] = "save";
        endif;


        // ###############################################################################################################

     
        $return = array();
        $check_not_err = TRUE;
       
        // $this->load->controller('balance', 'balance');
        // p('DONE GEN');
        // exit;
        // $token = $this->input->post("token");
        // $flow_id = $this->input->post("flow_id");
        $flow_id = $flow_id_from_create;
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $post_data = $this->input->post(); 

        $token = register_token($flow_id, $present_state, $process_id);

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

        // p($this->input->post()); exit();
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
                // p($order_id); exit;
                $data['Document_No'] = $document_no;
                $check_not_err = TRUE;
            endif;
        endif;

        // BALL

       
        if ($check_not_err):
            $response = $this->balance->_chkPDreservBeforeApprove($ci_confirm_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, SEPARATOR, "STK_T_Relocate_Detail"); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการส่งค่าของตัวแยกให้กับฟังก์ชั่น $this->balance->xxx จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
            $return = array_merge_recursive($return, $response);

            if (!empty($return['critical'])):

                $json['status'] = "validation";
                $json['return_val'] = $return;

            else:
                $next_state = -2;

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
                    // p($result_order_query); exit;
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

                        foreach ($prod_list as $rows_key => $rows):
                        // foreach ($prod_list as $rows):
                            $cut_data = explode(SEPARATOR, strip_tags($rows)); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
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
                            // Find OrderDetail Item_id
                            $order_detail_item_id = $this->rl->order_detail_item_id($order_id);

                            // p($order_detail_item_id[$rows_key]['Item_Id']);
                            // p($cut_data[$ci_item_id]);
                            // exit;
                            // Find OrderDetail Item_id
                            if ($order_detail_item_id[$rows_key]['Item_Id']): //add '$cut_data[$ci_item_id]' by kik : 20140326 เพราะว่าการ update ค่าควรใช้ item_id ที่ส่งมาจากหน้า form ไม่ใช่หาเอง กรณีที่ 1 order มีหลายๆ inbound id จะ update ข้อมูลผิดหมด
                                $where = array();
                                $where['Item_Id'] = $order_detail_item_id[$rows_key]['Item_Id']; //add '$cut_data[$ci_item_id]' by kik : 20140326 เพราะว่าการ update ค่าควรใช้ item_id ที่ส่งมาจากหน้า form ไม่ใช่หาเอง กรณีที่ 1 order มีหลายๆ inbound id จะ update ข้อมูลผิดหมด
                                $where['Order_Id'] = $order_id;
                                // p($where); exit;
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

                $set_return['message'] = "Approve Re-Location Product Complete.";
                $return['success'][] = $set_return;
                $json['status'] = "save";
                $json['return_val'] = $return;

                /**
                 * ================== Auto End Transaction =========================
                 */
                $this->transaction_db->transaction_end();

            else:

                $array_return['critical'][]['message'] = "Approve Re-Location Product Incomplete.";
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

    

}

?>