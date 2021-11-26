<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
# dev by sureerat
# 1.reLocationList show all order of re-location by location code -> showReLocationList() get detail
# 2.Add new order Re-Location go reLocationForm() show form -> openReLocation() save form -> addReLocationOrder() add order
# 3.openActionForm() form for confirm & approve step
# 4.locationList()  jquery  show location can move
# 5.showLocationChecked() jquery  show location user selected from popup and show in table
# 6.genLocationSelectOption() jquery show select option of location can move to
# 7.getSuggestLocation)() jquery show select option of Suggest Location
# 8.getSuggestLocationAll() jquery show select option of Action Location
# 9.createReLNo from helper
# 10.showProductInLocation() jquery when click icon glass show Product in this Location
# 11.confirmReLocation() confirm Re-Location
# 12.approveReLocation() approve Re-Location

class reLocation extends CI_Controller {

    //put your code here
    public function __construct() {
        parent::__construct();
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->library('stock_lib');
        $this->load->helper('form');
        $this->load->helper('util_helper'); //add by kik : 05-09-2013
        //$this->load->model("pre_dispatch_model", "preDispModel");
        $this->load->model("encoding_conversion", "conv");
        //$this->load->model("workflow_model", "wf");
        $this->load->model("stock_model", "stock");
        $this->load->model("product_model", "p");
        $this->load->model("contact_model", "contact");
        $this->load->model("re_location_model", "rl");
        $this->load->model("workflow_model", "flow");
        $this->load->model("location_model", "lc");
        $this->load->model("pallet_model", "pallet");
        //$this->config->set_item('renter_id', '1');  // Fix for TV DIRECT
        //$this->config->set_item('owner_id', '2');  // Fix for JWD
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));
        $this->load->library('getlocation_no_dispatch_area'); //add by kik : 11-09-2013
    }

    public function index() {

        $this->reLocationList();
    }

    function reLocationList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        # show oder Re-Location
        $module = "reLocation";

        $query = $this->rl->showReLocationList($module);
        $receive_list = $query->result();
        //p($receive_list);
        $column = array("Flow ID", "State Name", "Reference External", "Reference Internal", "Document No.", "Process Day", "Worker Name"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow);
        $action = array(VIEW, DEL);

        // Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย
        if(($key = array_search(DEL, $action)) !== false) {
            unset($action[$key]);
        }
        // END Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย

        $datatable = $this->datatable->genTableFixColumn($query, $receive_list, $column, "reLocation/openActionForm", $action, "reLocation/rejectAction");
        $module = "";
        $sub_module = "";
        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Re-Location Document'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
			 ONCLICK=\"openForm('Re-Location','reLocation/reLocationForm','A','')\">"
        ));
    }

    function reLocationForm() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        #Load config (add by kik : 20140723)
        $conf = $this->config->item('_xml');
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];
        $parameter['conf_pallet'] = $conf_pallet;

        # show Add new order Form
        $process_id = 5;
        $present_state = 0;

        $renter_id = $this->session->userdata('renter_id'); //add by kik : 2013-12-12
        $owner_id = $this->session->userdata('owner_id');   //add by kik : 2013-12-12
        //
        #Get Renter [worker] list
//		$query_worker = $this->contact->getWorkerAll();
        $result_worker = $this->contact->getWorkerAllWithUserLogin()->result();  // Add By Akkarapol, 19/09/2013, เปลี่ยนมาใช้แบบนี้เพราะให้ตารางหลักเป็น UserLogin จะได้เอาค่าไปใช้ต่อได้ง่ายๆ
//		$worker_list = genOptionDropdown($result_worker,"CONTACT");
        $worker_list = genOptionDropdown($result_worker, "CONTACTWITHUSERLOGIN"); // Add By Akkarapol, 19/09/2013, เปลี่ยนมาใช้เพราะต้องการให้ค่า Index ที่ได้ออกมาเป็น UserLogin_Id แทนที่จะเป็น Contact_Id เพราะเห็นว่า อย่างไรก็เอา UserLogin_Id ไปเก็บเป็น Detail อยู่แล้ว จะได้ไม่ผิดที่เอา UserLogin_Id มาใช้บ้าง Contact_Id มาใช้บ้าง เพื่อให้เป็น มาตรฐานเดียวกัน
        $parameter['worker_list'] = $worker_list;
        //$parameter['worker_id'] = '';
        $parameter['worker_id'] = $this->session->userdata('user_id');
        //$parameter=array();
        // Get warehouse
        $this->load->model("warehouse_model", "warehouse");
//        $result_warehouse = $this->warehouse->getWarehouseAll()->result();
//        $warehouse_list = genOptionDropdown($result_warehouse, "WH_LIST");
//
//
        // Edit by Ton! 20131107
        $result_warehouse = $this->warehouse->getWarehouseList()->result();
        $warehouse_list = genOptionDropdown($result_warehouse, "WH", FALSE);

        $parameter['token'] = '';
        $parameter['warehouse_list'] = $warehouse_list;
        $parameter['warehouse_id'] = '';

//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "reLocation"); // Button Permission. Add by Ton! 20140131|

        $show_column = array("Running No.", "Location From", "Suggest Location To", "Actual Location To", "Remark", "from_location", "to_location", "act_location", DEL);
        $parameter['show_column'] = $show_column;
        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['process_id'] = $process_id;
        $parameter['present_state'] = $present_state;
        $parameter['data_form'] = (array) $data_form;
        $parameter['renter_id'] = $renter_id;               //add by kik : 2013-12-12
        $parameter['owner_id'] = $owner_id;                 //add by kik : 2013-12-12
        //
        // Add By Akkarapol, 20/09/2013, เพิ่มตัวแปรสำหรับตรวจสอบว่า เราจะเช็คค่า remark หรือไม่ หาก Suggest กับ Actual ไม่ตรงกัน
        $parameter['chk_if_remark'] = FALSE;
        // END Add By Akkarapol, 20/09/2013, เพิ่มตัวแปรสำหรับตรวจสอบว่า เราจะเช็คค่า remark หรือไม่ หาก Suggest กับ Actual ไม่ตรงกัน
        // START Test Language
        //$parameter["msg_first_name"] = $this->lang->line("msg_first_name");
        //$parameter["msg_last_name"] = $this->lang->line("msg_last_name");
        //$parameter["msg_dob"] = $this->lang->line("msg_dob");
        //$parameter["msg_address"] = $this->lang->line("msg_first_name");
        //print_r($parameter["msg_last_name"]);
        // END Test Language

        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Re-Location by Location'
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

    private function cleanLocationValue($value) {
        $text = str_replace('"', '', $value);
        $pos = strpos($text, 'value=');
        $remain = substr($text, $pos + 6);
        $response = explode(" ", $remain);
        return $response['0'];
    }

    function openReLocation() {

        # Parameter Index Datatable
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_location_from = $this->input->post("ci_location_from");
        $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        $ci_actual_loc = $this->input->post("ci_actual_loc");
        $ci_remark = $this->input->post("ci_remark");

        # Parameter Form
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->session->userdata('user_id');
        $prod_list = $this->input->post("prod_list");

        # Parameter Order relocation header
        $est_relocate_date = $this->input->post("est_relocate_date");
        $relocate_date = $this->input->post("relocate_date");
        $worker_id = $this->input->post("worker_id");
        $putaway_by = $worker_id; // Add By Akkarapol, 19/09/2013, เพิ่มใหม่ เพราะค่าที่เก็บไว้ใน DB มันเป็น UserLoginId อยู่แล้ว เลยดึงมาใช้ได้เลย
        $doc_type = '';
        $owner_id = $this->input->post("owner_id");
        $renter_id = $this->input->post("renter_id");
        $receive_type = '';
        $remark = '';
        $is_urgent = $this->input->post("is_urgent");
        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Parameter Order relocation detail
        $prod_list = $this->input->post("prod_list");


        # Check validate data
        $return = array();
        $check_not_err = TRUE; //for check error all process , if process error set value = FALSE
        # ================== Start save data =========================

        $this->transaction_db->transaction_start();

        # generate Relocation Number
        if ($check_not_err):
//            $document_no = createReLNo();
            $document_no = create_document_no_by_type("REL"); // Add by Ton! 20140428
            if ($document_no == "" || $document_no == NULL):
                $check_not_err = FALSE;
                /*
                 * LOG MSG 1
                 */
                $return['critical'][]['message'] = "Can not create Re-Locate No";
            else:
                $data['Document_No'] = $document_no;
            endif;
        endif;




        # add New workflow
        if ($check_not_err):
            list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data);
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

        # create new Order and Order Detail
        # Save new order
        if ($check_not_err):
            $est_relocate_date = convertDate($est_relocate_date, "eng", "iso", "-");
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
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Assigned_Id' => $worker_id
                , 'Is_urgent' => $is_urgent
            );

            $order_id = $this->rl->addReLocationOrder($order);
            if ($order_id == "" || empty($order_id)) {
                $check_not_err = FALSE;
                /*
                 * LOG MSG 3
                 */
                $return['critical'][]['message'] = "Can not add new order.";
            }

        endif;

        # Order Detail  - Set data and Save data into table
        if ($check_not_err):

            # add data into order detail
            $location_list = array();
            $location_id = array();
            if (!empty($prod_list)) {
                # get location id

                foreach ($prod_list as $rows) {

                    $cut_data = explode(SEPARATOR, $rows); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้

                    $location_code = strip_tags($cut_data[$ci_suggest_loc]);
                    $location_data = strip_tags($cut_data[$ci_location_from]);
                    $loc = array();
                    $f_loc_id = $this->lc->getLocationIdByCode(trim($location_data), '');
                    if (!in_array($f_loc_id, $location_id)) {
                        $loc['f_location_id'] = $f_loc_id;
                        $loc['location_to'] = $this->lc->getLocationIdByCode(trim($location_code), '');
                        $loc['remark'] = strip_tags($cut_data[$ci_remark]);
                        //p($loc);
                        $location_list[] = $loc;
                        $location_id[] = $f_loc_id;
                    }
                }// close loop get location array

                $all_product_move = array(); # save move product detail

                /**
                 * check not empty location list
                 */
                if (!empty($location_list)):

                    foreach ($location_list as $l) :

                        $product_query = $this->lc->showProductInLocationById($l['f_location_id'], '');
                        $product_list = $product_query->result();

                        /**
                         *  get product in this location
                         */
                        if (!empty($product_list)):

                            foreach ($product_list as $p) :

                                $set_data = array(
                                    "Order_Id" => $order_id
                                    , "Document_No" => strtoupper($document_no)
                                    , "Suggest_Location_Id" => $l['location_to']
                                    , "Actual_Location_Id" => ''
                                    , "Confirm_Qty" => 0
                                    , "Putaway_By" => $putaway_by
                                    , "Putaway_Date" => ''
                                    , "Remark" => iconv("UTF-8", "TIS-620", $l['remark'])
                                );

                                list($set_col_insert, $set_col_val) = $this->set_column_insert_select_relocation($set_data);
//                                p($set_col_insert);
//                                p($set_col_val);

                                $str_query = "INSERT INTO STK_T_Relocate_Detail({$set_col_insert}) SELECT {$set_col_val} FROM STK_T_Inbound WHERE Inbound_Id = {$p->Inbound_Id} AND (Receive_Qty-Dispatch_Qty-Adjust_Qty-PD_Reserv_Qty)>0";
//                                p($str_query);
                                $return_query = $this->util_query_db->query($str_query);

                                if ($return_query < 1):
                                    $check_not_err = FALSE;

                                    /**
                                     * Set Alert Zone (set Error Code, Message, etc.)
                                     */
                                    $error_message = "Can not add ReLocation OrderDetail.";
                                    $return['critical'][]['message'] = $error_message;
                                    log_message("error", "(Open Relocation by Location) {$error_message} : {$str_query}");
                                endif;

                                $set_update_est_balance['Inbound_Item_Id'] = $p->Inbound_Id;
                                $set_update_est_balance['Reserv_Qty'] = getCalculateAllowcate($p->Receive_Qty, $p->Dispatch_Qty, $p->Adjust_Qty, $p->PD_Reserv_Qty);
                                $set_update_est_balances[] = $set_update_est_balance;


//
//                                $p_detail = array();
//                                $p_detail['Order_Id'] = $order_id;
//                                $p_detail['Inbound_Item_Id'] = $p->Inbound_Id;
//                                $p_detail['Document_No'] = $p->Document_No;
//                                $p_detail['Doc_Refer_Int'] = $p->Doc_Refer_Int;
//                                $p_detail['Doc_Refer_Ext'] = $p->Doc_Refer_Ext;
//                                $p_detail['Doc_Refer_Inv'] = $p->Doc_Refer_Inv;
//                                $p_detail['Doc_Refer_CE'] = $p->Doc_Refer_CE;
//                                $p_detail['Doc_Refer_BL'] = $p->Doc_Refer_BL;
//                                $p_detail['Doc_Refer_AWB'] = $p->Doc_Refer_AWB;
//                                $p_detail['Product_Id'] = $p->Product_Id;
//                                $p_detail['Product_Code'] = $p->Product_Code;
//                                $p_detail['Product_Status'] = $p->Product_Status;
//                                $p_detail['Product_Sub_Status'] = $p->Sub_Status_Code;
//                                $p_detail['Suggest_Location_Id'] = $l['location_to'];
//                                $p_detail['Actual_Location_Id'] = '';
//                                $p_detail['Old_Location_Id'] = $p->Actual_Location_Id;
//                                $p_detail['Pallet_Id'] = $p->Pallet_Id;
//                                $p_detail['Product_License'] = $p->Product_License;
//                                $p_detail['Product_Lot'] = $p->Product_Lot;
//                                $p_detail['Product_Serial'] = $p->Product_Serial;
//                                $p_detail['Product_Mfd'] = $p->Product_Mfd_Ori;
//                                $p_detail['Product_Exp'] = $p->Product_Exp_Ori;
//                                $p_detail['Receive_Date'] = $p->Receive_Date_Ori;
//                                $p_detail['Reserv_Qty'] = getCalculateAllowcate($p->Receive_Qty, $p->Dispatch_Qty, $p->Adjust_Qty, $p->PD_Reserv_Qty);
//                                $p_detail['Confirm_Qty'] = 0;
//                                $p_detail['Unit_Id'] = $p->Unit_Id;
//                                $p_detail['Putaway_By'] = $putaway_by;
//                                $p_detail['Putaway_Date'] = '';
//                                $p_detail['Remark'] = iconv("UTF-8", "TIS-620", $l['remark']);
//
//                                //ADD BY POR 2014-06-16 เพิ่ม price per unit ด้วย
//                                $p_detail['Price_Per_Unit'] = $p->Price_Per_Unit;
//                                $p_detail['Unit_Price_Id'] = $p->Unit_Price_Id;
//                                $p_detail['All_Price'] = $p->All_Price;
//                                //END ADD
//
//                                $all_product_move[] = $p_detail;
                            endforeach;

                        endif; //end check not empty product_list
                    endforeach;//end for location list

                else:

                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 4
                     */
                    $error_message = "Not have location list";
                    $return['critical'][]['message'] = $error_message;
                    log_message("error", "(Open Relocation by Location) {$error_message}");

                endif; // end check not empty location list
            }// end check have location
            else {
                $check_not_err = FALSE;
                /*
                 * LOG MSG 5
                 */
                $error_message = "Not have location list";
                $return['critical'][]['message'] = $error_message;
                log_message("error", "(Open Relocation by Location) {$error_message}");
            }
            // remark for update pd to inbound
            // Use data allocate from view
            // By Ball

            if ($check_not_err):

//                if (!empty($all_product_move)) {
//
//                    $result_relocationDetail = $this->rl->addReLocationOrderDetail($all_product_move);
//                    if ($result_relocationDetail < 1):
//                        $check_not_err = FALSE;
//                        /*
//                         * LOG MSG 6
//                         */
//                        $return['critical'][]['message'] = "Can not save relocate detail.";
//                    endif;
//
//                    #update pd reserv into inbound
//                    if ($check_not_err):
                        $result_PD_reserv_qty = $this->stock->reservPDReservQtyArray($set_update_est_balances);
                        if (!$result_PD_reserv_qty):
                            $check_not_err = FALSE;
                            /*
                             * LOG MSG 8
                             */
                            $error_message = "Can not update QTY in Inbound table.";
                            $return['critical'][]['message'] = $error_message;
                            log_message("error", "(Open Relocation by Location) {$error_message}");
                        endif;
//                    endif;
//                }else {
//
//                    $check_not_err = FALSE;
//                    /*
//                     * LOG MSG 7
//                     */
//                    $return['critical'][]['message'] = "Not have location list";
//                }

            endif; // end check not empty product move

        endif; // check status error before save
        #Set Message Alert
//        p($check_not_err);
//        $check_not_err = FALSE; //--------------------------------------------

        if ($check_not_err):

            $array_return['success'][]['message'] = "Save Re-Location Complete.";
            $json['return_val'] = $array_return;

            $this->transaction_db->transaction_end();

        else:

            $array_return['critical'][]['message'] = "Save Re-Location Incomplete.";
            $json['return_val'] = array_merge_recursive($array_return, $return);

            $this->transaction_db->transaction_rollback();

        endif; //end set message alert

        $json['status'] = "save";

        echo json_encode($json);
    }

    function openActionForm() {

        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        #Load config (add by kik : 20140723)
        $conf = $this->config->item('_xml');
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];
        $parameter['conf_pallet'] = $conf_pallet;
        
        $flow_id = $this->input->post("id");

        #Retrive Data from Table
        $flow_detail = $this->rl->getRelocationOrder($flow_id);
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
        $parameter['act_action_date'] = $flow_detail[0]->Action_Date;
        $parameter['is_urgent'] = $flow_detail[0]->Is_urgent;

        # Get Order detail
        $parameter['order_detail'] = $this->rl->getReLocationDetail($order_id);
        //p($order_detail);
        #Get Renter [worker] list
//		$query_worker = $this->contact->getWorkerAll();
        $query_worker = $this->contact->getWorkerAllWithUserLogin();  // Add By Akkarapol, 20/09/2013, เปลี่ยนมาใช้แบบนี้เพราะให้ตารางหลักเป็น UserLogin จะได้เอาค่าไปใช้ต่อได้ง่ายๆ
        $result_worker = $query_worker->result();
//		$worker_list = genOptionDropdown($result_worker,"CONTACT");
        $worker_list = genOptionDropdown($result_worker, "CONTACTWITHUSERLOGIN"); // Add By Akkarapol, 20/09/2013, เปลี่ยนมาใช้เพราะต้องการให้ค่า Index ที่ได้ออกมาเป็น UserLogin_Id แทนที่จะเป็น Contact_Id เพราะเห็นว่า อย่างไรก็เอา UserLogin_Id ไปเก็บเป็น Detail อยู่แล้ว จะได้ไม่ผิดที่เอา UserLogin_Id มาใช้บ้าง Contact_Id มาใช้บ้าง เพื่อให้เป็น มาตรฐานเดียวกัน
        $parameter['worker_list'] = $worker_list;
        $parameter['worker_id'] = $flow_detail[0]->Assigned_Id;

//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "reLocation"); // Button Permission. Add by Ton! 20140131|

        $show_column = array("Running No.", "Location From", "Suggest Location To", "Actual Location To", "Remark", "LocFromName", "SuggestLocToName", "ActualName", DEL);
        $parameter['show_column'] = $show_column;

        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['flow_id'] = $flow_id;
        $parameter['process_id'] = $process_id;
        $parameter['present_state'] = $present_state;
        $parameter['data_form'] = (array) $data_form;

        // Add By Akkarapol, 20/09/2013, เพิ่มตัวแปรสำหรับตรวจสอบว่า เราจะเช็คค่า remark หรือไม่ หาก Suggest กับ Actual ไม่ตรงกัน
        $parameter['chk_if_remark'] = TRUE;
        // END Add By Akkarapol, 20/09/2013, เพิ่มตัวแปรสำหรับตรวจสอบว่า เราจะเช็คค่า remark หรือไม่ หาก Suggest กับ Actual ไม่ตรงกัน
        // START Parse By Ball
        // END Parse

        $data_form->str_buttun .= '<input class="button dark_blue" type="button" onclick="exportFile(\'PDF\')" value="PDF">'; // Add By Akkarapol, 17/10/2013, เพิ่มปุ่ม PRINT สำหรับ Generate PDF เพื่อออกใบ Relocation Job
//                p($data_form->str_buttun);
        # LOAD FORM
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);
//                p($parameter);
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

    function locationList() {
        # show Location can move
        //$location_code = $this->input->post("post_val");
        //echo '<br> code = '.$location_code;
        /*
          if($location_code!=""){
          $location_result=$this->rl->getLocationByCode($location_code);
          $location_list=$location_result->result();
          }
          else{l.Warehouse_Id
          l.Location_Code
         */

        $criteria = array();

        if ($this->input->post('warehouse')) {
            $criteria['l.Warehouse_Id'] = $this->input->post('warehouse');
        }

        if ($this->input->post('post_val')) {
            $criteria['l.Location_Code'] = $this->input->post('post_val');
        }

        $location_result = $this->rl->getLocationAll($criteria);
        $location_list = $location_result->result();
        //}
        $data = array();
        $data_list = array();
        $action = array();
        $action_module = "";
        $column = array("Selection", "Location Code");
        if (is_array($location_list) && count($location_list)) {
            $count = 1;
            foreach ($location_list as $rows) {
                //ADD BY POR 2014-06-13 ตรวจสอบว่า location ที่ได้มี product วางอยู่หรือไม่ (ไม่ได้แก้ไขใน getLocationAll() เนื่องจากกลัวว่าจะกระทบในส่วนอื่น)
                $est_location=$this->rl->check_est_location($rows->Location_Id)->result();
                //END ADD
                if(!empty($est_location)):
                    $data['Id'] = "<input ID=chkBoxVal type=checkbox name=chkBoxVal[] value='" . $rows->Location_Id . "' id=chkBoxVal" . $rows->Location_Id . " onClick='getCheckValue(this)'>";
                    $data['Location_Code'] = $rows->Location_Code;
                    $count++;
                    $data_list[] = (object) $data;
                endif;
            }
        }
        $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);
        $this->load->model("encoding_conversion", "conv");
        echo $this->conv->tis620_to_utf8($datatable);
    }

    function showLocationChecked() {
        # jquery show location select from popup
        $location_code = $this->input->post("post_val");
        //p($location_code);
        //echo '<br> location = '.$location_code;
        $location_list = $this->rl->getLocationNameFromArray($location_code)->result();
        $json['status'] = "1";
        $json['error_msg'] = "";
        $json['locations'] = $location_list;
        echo json_encode($json);
    }

    function genLocationSelectOption() {
        # jquery show select option location can move to
        $location_result = $this->rl->getLocationAll();
        $location_list = $location_result->result();
        $option[''] = 'Please select';
        foreach ($location_list as $data) {
            $option[$data->Location_Code] = $data->Location_Code;
        }
        echo json_encode($option);
    }

    function getLocationSelectList() {
        # jquer
        $text_search = $this->input->post('text_search');
        $location_q = $this->rl->getLocationByCode($text_search);
        $location_list = $location_q->result();
        //p($location_list);
        $list = '';
        foreach ($location_list as $loc) {
            $list.='<li onClick="fill(\'' . $loc->Location_Id . '\',\'' . $loc->Location_Code . '\',\'' . $loc->Location_Code . '\');">' . $loc->Location_Code . '</li>';
        }
        echo $list;
    }

    function getSuggestLocation() {
        # jquery show suggest location option
        $location_id = $this->input->post('location_id');
        $location_code = $this->input->post('location_code');
        $select_id = trim($this->input->post('select_id'));
        $location_list = $this->rl->showSuggestLocationSameWarehouse($location_id, $location_code);
        $list = '';
        //p($location_list);
        //onchange="showSuggestProduct(this.value);"
        //$list='<select name="suggest_location" id="suggest_location" >';
        $list.='
				<option value="">Suggest Location</option>
				';
        foreach ($location_list as $loc) {
            //$list.='<li onClick="fill(\''.$loc->Location_Id.'\',\''.$loc->Location_Code.'\',\''.$loc->Location_Code.'\');">'.$loc->Location_Code.'</li>';
            $check = '';
            if ($loc->Location_Code == $select_id) {
                $check = ' selected="selected"';
            }
            $list.='<option value="' . $loc->Location_Code . '" ' . $check . '>' . $loc->Location_Code . '</option>';
        }
        //$list.='</select>';
        echo $list;
    }
    


    public function getLocationAll() {
        # jquery show selec option for Action Location
        $criteria = $this->input->post('criteria');
        $location_list = $this->rl->showLocationAll(NULL, $criteria, 10);
        $this->output->set_content_type('application/json')->set_output(json_encode($location_list));
    }

    function getSuggestLocationAll() {
        # jquery show selec option for Action Location
        $location_id = $this->input->post('location_id');
        $location_code = $this->input->post('location_code');
        $select_id = trim($this->input->post('select_id'));
        $location_list = $this->rl->showLocationAll($location_code);

        $list = '';

        $list.='
				<option value="">Action Location</option>
				';
        foreach ($location_list as $loc) :
            $check = '';
            if ($loc->Location_Code == $select_id) :
                $check = ' selected="selected"';
            endif;
            $list.='<option value="' . $loc->Location_Code . '" ' . $check . '>' . $loc->Location_Code . '</option>';
        endforeach;
        echo $list;
    }

    function editDataTable() {
        # show value when edit in row datatable
        $editedValue = $_REQUEST['value'];
        //echo '<a onclick="showProduct(\'\',\''.$editedValue.'\');">'.$editedValue.'</a>';
        //echo $editedValue;
        echo iconv("UTF-8", "TIS-620", $editedValue);
    }

    function showProductInLocation() {
        # click icon glass show product in that location
        $location_id = $this->input->post("location_id");
        $location_code = $this->input->post("location_code");
// p( $location_code ); exit;
        $product_query = $this->lc->showProductInLocationById($location_id, $location_code);
		
        $all_product = $product_query->result();
//        p($all_product);exit();
        $data = array();
        $data_list = array();
        $action = array();
        $action_module = "";
        $column = array(
            _lang('no')
            , _lang('document_no')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('product_status')
            , _lang('product_sub_status')
            , _lang('receive_date')
            , _lang('lot')
            , _lang('serial')
            , _lang('product_mfd')
            , _lang('product_exp')
            , _lang('pallet_code')
            , _lang('avaliable_qty')
            ); // add Product Sub Status by kik : 12-09-2013
        if (is_array($all_product) && count($all_product)) {
            $count = 1;
            foreach ($all_product as $rows) {
                $data['Id'] = $count;
                $data['Document_No'] = $rows->Document_No;
                $data['Product_Code'] = $rows->Product_Code;
                $data['Product_NameEN'] = $rows->Product_NameEN;
                $data['Product_Status'] = $rows->Product_Status;
                $data['Product_Sub_Status'] = $rows->Product_Sub_Status; // add Product Sub Status by kik : 12-09-2013
                $data['Receive_Date'] = $rows->Receive_Date;
                $data['Product_Lot'] = $rows->Product_Lot;
                $data['Product_Serial'] = $rows->Product_Serial;
                $data['Product_Mfd'] = $rows->Product_Mfd;
                $data['Product_Exp'] = $rows->Product_Exp;
                $data['Pallet_Code'] = @$rows->Pallet_Code;
                $data['qty'] = getCalculateAllowcate($rows->Receive_Qty, $rows->Dispatch_Qty, $rows->Adjust_Qty, $rows->PD_Reserv_Qty); // แก้ไขการคำนวนเป็น หาค่าของ Available Qty ตามสูตร : by kik : 05-09-2013
//                              $data['qty']	= $rows->Receive_Qty-($rows->Dispatch_Qty + $rows->Adjust_Qty); // แก้ไขการคำนวน ของเดิมคำนวนเพื่อหาค่า balance qty : by kik : 05-09-2013

                $count++;
                $data_list[] = (object) $data;
            }
        } else {
            $datatable = '';
        }
        $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);

        #########################################
        $this->load->model("encoding_conversion", "conv");
        echo $this->conv->tis620_to_utf8($datatable);
    }

    function confirmReLocation() {
        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;

        # Parameter Form
        #====================== Start Get Parameter ============================
        $token = $this->input->post("token");
        $flow_id = $this->input->post("flow_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->session->userdata('user_id');
        $prod_list = $this->input->post("prod_list");

        # Parameter Order relocation header
        $est_relocate_date = $this->input->post("est_relocate_date");
        $relocate_date = $this->input->post("relocate_date");
        $worker_id = $this->input->post("worker_id");
        $putaway_by = $worker_id; // Add By Akkarapol, 20/09/2013, เพิ่มใหม่ เพราะค่าที่เก็บไว้ใน DB มันเป็น UserLoginId อยู่แล้ว เลยดึงมาใช้ได้เลย
        $doc_type = '';
        $process_type = '';
        $owner_id = $this->input->post("owner_id");
        $renter_id = $this->input->post("renter_id");
        $receive_type = '';
        $remark = '';
        $is_urgent = $this->input->post("is_urgent");
        $re_date = convertDate($relocate_date, "eng", "iso", "-");

        // validate token
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        $response = validate_token($token, $flow_id, $flow_info[0]->Present_State, $flow_info[0]->Process_Id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;

        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Parameter Order relocation detail
        $prod_list = $this->input->post("prod_list");
        $prod_del_list = $this->input->post("prod_del_list");

        # Parameter Index Datatable
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_location_from = $this->input->post("ci_location_from");
        $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        $ci_actual_loc = $this->input->post("ci_actual_loc");
        $ci_remark = $this->input->post("ci_remark");

        #------------------------ End Get Parameter ----------------------------



        if (empty($flow_id) || $flow_id == "" || empty($prod_list) || $prod_list == ""):
            $check_not_err = FALSE;
            $return['critical'][]['message'] = "Not have flow_id or location list.";
        /*
         * LOG MSG 1
         */
        endif;

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

        #========================== Start Save Data ============================

        $this->transaction_db->transaction_start();

        # Update workflow
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

        # Update Order Table
        if ($check_not_err):

            $order = array(
                'Doc_Relocate' => strtoupper($document_no)
                , 'Doc_Type' => strtoupper(iconv("UTF-8", "TIS-620", $doc_type))
                , 'Estimate_Action_Date' => $est_relocate_date
                , 'Actual_Action_Date' => $relocate_date
                , 'Owner_Id' => $owner_id
                , 'Renter_Id' => $renter_id
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Assigned_Id' => $worker_id
                , 'Modified_By' => $user_id
                , 'Modified_Date' => date("Y-m-d H:i:s")
                , 'Is_urgent' => $is_urgent
            );

            $where['Flow_Id'] = $flow_id;
            $where['Order_Id'] = $order_id;

            #update order
            $result_order_query = $this->rl->updateReLocationOrder($order, $where);

            if (!$result_order_query):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update Order.";
            /*
             * LOG MSG 4
             */
            endif;

        endif;


        /**
         * update Order Detail Table
         */
        if ($check_not_err):

            $location_list = array();
            $location_id = array();

            if (!empty($prod_list)) {

                foreach ($prod_list as $rows) {

                    $cut_data = explode(SEPARATOR, strip_tags($rows)); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
                    // p($cut_data);
                    $loc = array();

                    $f_loc_id = $this->lc->getLocationIdByCode(trim($cut_data[$ci_location_from]), '');

                    if (!in_array($f_loc_id, $location_id)) {
                        $loc['f_location_id'] = $f_loc_id;
                        $loc['location_to'] = $this->lc->getLocationIdByCode(trim($cut_data[$ci_suggest_loc]), '');
                        $loc['act_to'] = $this->lc->getLocationIdByCode(trim($cut_data[$ci_actual_loc]), '');
                        $loc['remark'] = strip_tags($cut_data[$ci_remark]);

                        $location_list[] = $loc;
                        $location_id[] = $f_loc_id;
                    }
                }// close loop get location array

                if (!empty($location_list)):

                    $all_product_move = array();

                    if (!empty($location_list)):
                        foreach ($location_list as $l) {
                            $product_query = $this->rl->show_product_relocation($order_id, $l['f_location_id']);
                            $product_list = $product_query->result();

                            foreach ($product_list as $p) {
                                $p_detail = array();
                                $p_detail['Suggest_Location_Id'] = $l['location_to'];
                                $p_detail['Actual_Location_Id'] = $l['act_to'];
                                $p_detail['Confirm_Qty'] = $p->Reserv_Qty;
                                $p_detail['Putaway_By'] = $putaway_by;
                                $p_detail['Putaway_Date'] = $re_date;
                                $p_detail['Remark'] = iconv("UTF-8", "TIS-620", $l['remark']);

                                $all_product_move[] = $p_detail;

                                // Add check actual location if is null
                                if (is_null($p_detail['Actual_Location_Id'])) :
                                        $check_not_err = FALSE;
                                        $return['critical'][]['message'] = "Location is NULL";
                                        log_message("INFO", "ERROR - Location is NULL");
                                endif;
                                // Add check actual location if is null

                                if ($p->Item_Id != "") {
                                    $where = array();
                                    $where['Item_Id'] = $p->Item_Id;
                                    $where['Order_Id'] = $order_id;
                                    $result_relocate_detail = $this->rl->updateReLocationOrderDetail($p_detail, $where);

                                    if (!$result_relocate_detail):

                                        $check_not_err = FALSE;
                                        /*
                                         * LOG MSG 5
                                         */
                                        $return['critical'][]['message'] = "Can Not update relocate detail.";

                                    endif;
                                }
                            }
                        }

                    endif;

                else:

                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 6
                     */
                    $return['critical'][]['message'] = "Not have location list";

                endif; // end check not empty location list
            } // have location


        endif;

        /**
         * Delete data
         */
        if ($check_not_err):

            if (is_array($prod_del_list) && (!empty($prod_del_list))) {
                //p($prod_del_list);
                $item_delete = array();

                foreach ($prod_del_list as $rows) {
                    $a_data = explode(SEPARATOR, strip_tags($rows));
                    $location_id = $this->lc->getLocationIdByCode($a_data[$ci_location_from]);

                    if ($location_id == "" || $location_id = NULL):
                        $check_not_err = FALSE;
                        /*
                         * LOG MSG 6
                         */
                        $return['critical'][]['message'] = "Not have product in location for delete.";

                    else:
                        #Update PD_Reserv_Qty in Inbound Table
                        $product_del_query = $this->rl->show_product_relocation($order_id, $location_id);
                        $product_del_list = $product_del_query->result();

                        foreach ($product_del_list as $p) {

                            if (!empty($p->Inbound_Item_Id)) {

                                #update pd reserv into inbound
                                $result_PD_reserv_qty = $this->stock->reservPDReservQty($p->Inbound_Item_Id, $p->Confirm_Qty, "-");
                                if (!$result_PD_reserv_qty):
                                    $check_not_err = FALSE;
                                    $return['critical'][]['message'] = "Can not update quantity balance.";
                                /*
                                 * LOG MSG 10
                                 */
                                endif;
                            }
                        }// end update PD_Reserv_Qty in Inbound Table

                        $item_delete[] = $location_id;

                    endif; //end check find $location_id
                }//end for prod_del_list

                if ($check_not_err):
                    if (!empty($item_delete)):
                        $result_remove_location = $this->rl->removeReLocationDetail($item_delete, $order_id);
                        if (!$result_remove_location):
                            $check_not_err = FALSE;
                            $return['critical'][]['message'] = "Can not delete location list.";
                        /*
                         * LOG MSG 7
                         */
                        endif;
                    else:
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Not found location list for delete data.";
                    /*
                     * LOG MSG 8
                     */
                    endif;

                endif; //end check status err befor delete item
            }//end check empty $prod_del_list

        endif; //check status err before delete data


        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):

            $set_return['message'] = "Confirm Re-Location Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;

            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

        else:

            $array_return['critical'][]['message'] = "Confirm Re-Location Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);

            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

        endif;

        echo json_encode($json);
    }

    function approveReLocation() {

        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;

        # approve re-location order
        $token = $this->input->post("token");
        $flow_id = $this->input->post("flow_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->input->post("user_id");
        $prod_list = $this->input->post("prod_list");

        # Parameter Order relocation header
        $est_relocate_date = $this->input->post("est_relocate_date");
        $relocate_date = $this->input->post("relocate_date");
        $re_date = convertDate($relocate_date, "eng", "iso", "-");
        $worker_id = $this->input->post("worker_id");
        $putaway_by = $worker_id; // Add By Akkarapol, 21/09/2013, เพิ่มใหม่ เพราะค่าที่เก็บไว้ใน DB มันเป็น UserLoginId อยู่แล้ว เลยดึงมาใช้ได้เลย

        $doc_type = '';
        $owner_id = $this->input->post("owner_id");
        $renter_id = $this->input->post("renter_id");
        $receive_type = '';
        $remark = '';
        $is_urgent = $this->input->post("is_urgent");

        // validate token
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        $response = validate_token($token, $flow_id, $flow_info[0]->Present_State, $flow_info[0]->Process_Id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;

        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Parameter Order relocation detail
        $prod_list = $this->input->post("prod_list");
        $prod_del_list = $this->input->post("prod_del_list");

        # Parameter Index Datatable
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_location_from = $this->input->post("ci_location_from");
        $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        $ci_actual_loc = $this->input->post("ci_actual_loc");
        $ci_remark = $this->input->post("ci_remark");

        #------------------------ End Get Parameter ----------------------------

        if (empty($flow_id) || $flow_id == "" || empty($prod_list) || $prod_list == ""):
            $check_not_err = FALSE;
            $return['critical'][]['message'] = "Not have flow_id or location list.";
        /*
         * LOG MSG 1
         */
        endif;

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
        //   echo json_encode($check_not_err);
          $response = array_key_exists($Suggest_Location_Id, $temp);
           if($response == 1){
           $check_not_err = FALSE;
           $return['critical'][]['message'] = "SUGGEST_LOCATION NO_DISPATCH_AREA! ";
           }else{
           $check_not_err =TRUE;  
           }

             # ================== NO_DISPATCH_AREA  =========================


        #========================== Start Save Data ============================

        $this->transaction_db->transaction_start();

        # Update workflow
        if ($check_not_err):
            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $user_id, $data);

            if ($action_id == "" || empty($action_id)):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update workflow.";
            /*
             * LOG MSG 3
             */
            endif;

        endif;

        # Update Order Table
        if ($check_not_err):
            $order = array(
                'Doc_Relocate' => strtoupper($document_no)
                , 'Doc_Type' => strtoupper(iconv("UTF-8", "TIS-620", $doc_type))
                , 'Estimate_Action_Date' => $est_relocate_date
                , 'Actual_Action_Date' => $relocate_date
                , 'Owner_Id' => $owner_id
                , 'Renter_Id' => $renter_id
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Assigned_Id' => $worker_id
                , 'Modified_By' => $user_id
                , 'Modified_Date' => date("Y-m-d H:i:s")
                , 'Is_urgent' => $is_urgent
            );

            $where['Flow_Id'] = $flow_id;
            $where['Order_Id'] = $order_id;
            $result_order = $this->rl->updateReLocationOrder($order, $where);

            if (!$result_order):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update order.";
            /*
             * LOG MSG 4
             */
            endif;

        endif;

        /**
         * update Order Detail Table
         */
        if ($check_not_err):

            $location_list = array();
            $location_id = array();

            if (!empty($prod_list)) {

                foreach ($prod_list as $rows) {
                    $cut_data = explode(SEPARATOR, strip_tags($rows));
                    $loc = array();
                    $f_loc_id = $this->lc->getLocationIdByCode(trim($cut_data[$ci_location_from]), '');
                    if (!in_array($f_loc_id, $location_id)) {
                        $loc['f_location_id'] = $f_loc_id;
                        $loc['location_to'] = $this->lc->getLocationIdByCode(trim($cut_data[$ci_suggest_loc]), '');
                        $loc['act_to'] = $this->lc->getLocationIdByCode(trim($cut_data[$ci_actual_loc]), '');
                        $loc['remark'] = strip_tags($cut_data[$ci_remark]);
                        $location_list[] = $loc;
                        $location_id[] = $f_loc_id;
                    }
                }// close loop get location array


                $all_product_move = array();
                $product_old_location = array();

                if (!empty($location_list)):
                    foreach ($location_list as $l) {
                        $product_query = $this->rl->showProductRL($order_id, $l['f_location_id']);
                        $product_list = $product_query->result();
                        $inbound_list = array();
                        foreach ($product_list as $p) {
                            $p_detail = array();
                            $p_detail['Suggest_Location_Id'] = $l['location_to'];
                            $p_detail['Actual_Location_Id'] = $l['act_to'];
                            $p_detail['Remark'] = iconv("UTF-8", "TIS-620", $l['remark']);
                            $p_detail['Putaway_By'] = $putaway_by;
                            $p_detail['Putaway_Date'] = $re_date;

                            $all_product_move[] = $p_detail;
                            if ($p->Item_Id != "") {
                                $where = array();
                                $where['Item_Id'] = $p->Item_Id;
                                $where['Order_Id'] = $order_id;
                                $result_relocat_detail = $this->rl->updateReLocationOrderDetail($p_detail, $where);
                                if (!$result_relocat_detail):
                                    $check_not_err = FALSE;
                                    /*
                                     * LOG MSG 7
                                     */
                                    $return['critical'][]['message'] = "Can not update relocate detail.";
                                endif;

                                /**
                                 * Cut off dispatch and PD_Reserv_Qty in inbound table
                                 */
                                if ($check_not_err):
                                    $result_update_DP_qty = $this->stock->reservDispatch($p->Inbound_Item_Id, $p->Confirm_Qty, "+");

                                    if (!$result_update_DP_qty) :
                                        $check_not_err = FALSE;

                                        /**
                                         * 5
                                         */
                                        $return['critical'][]['message'] = "Can not update QTY in Inbound table.";
                                    else:
                                        $inbound_list[] = $p->Inbound_Item_Id;

                                    endif;

                                endif;
                            }
                        }
                        if ($check_not_err):
                            if (!empty($inbound_list)):
                                /**
                                 * update Balance Qty
                                 */
                                $result_updateBalanceQty = $this->stock_lib->updateBalanceQty($inbound_list);
                                if ($result_updateBalanceQty <= 0) :
                                    $check_not_err = FALSE;
                                    /**
                                     * Set Alert Zone (set Error Code, Message, etc.)
                                     */
                                    $return['critical'][]['message'] = "Can not update Balance Qty.";
                                endif;
                            endif;
                        endif;

                        #add for ISSUE 3335 : by kik : 2014-02-11
                        if ($check_not_err):
                            if ($this->config->item('build_pallet')):
                                $result_pallets = $this->pallet->get_pallet_in_location($l['f_location_id']);

                                if (!empty($result_pallets)):
                                    foreach ($result_pallets as $result_pallet):
                                        $colunm_pallet['Suggest_Location_Id'] = $l['location_to'];
                                        $colunm_pallet['Actual_Location_Id'] = $l['act_to'];
                                        $where_pallet['Pallet_Id'] = $result_pallet->Pallet_Id;
                                        $update_pallet = $this->pallet->update_pallet_colunm($colunm_pallet, $where_pallet);
                                        if (!$update_pallet):
                                            $check_not_err = FALSE;
                                            /*
                                             * LOG MSG 8
                                             */
                                            $return['critical'][]['message'] = "Can not update pallet.";
                                        endif;
                                        $colunm_pall_his_loc['Pallet_Id'] = $result_pallet->Pallet_Id;
                                        $colunm_pall_his_loc['Old_Location_Id'] = $l['f_location_id'];
                                        $colunm_pall_his_loc['Suggest_Location_Id'] = $l['location_to'];
                                        $colunm_pall_his_loc['Actual_Location_Id'] = $l['act_to'];
                                        $colunm_pall_his_loc['Create_Date'] = date("Y-m-d H:i:s");
                                        $colunm_pall_his_loc['Create_By'] = $this->input->post("user_id");
                                        $colunm_pall_his_loc['Remark'] = iconv("UTF-8", "TIS-620", $l['remark']);
                                        $update_his_loc = $this->pallet->insert_pallet_history_location($colunm_pall_his_loc);
                                        if (!$update_pallet):
                                            $check_not_err = FALSE;
                                            /*
                                             * LOG MSG 9
                                             */
                                            $return['critical'][]['message'] = "Can not update pallet detail.";
                                        endif;

                                    endforeach;
                                endif;

                            endif;

                        endif;
                        #end add for ISSUE 3335 : by kik : 2014-02-11
                    }
                else:
                    $check_not_err = FALSE;
                    /*
                     * LOG MSG 10
                     */
                    $return['critical'][]['message'] = "Not have location list.";
                endif;
            } // have location


        endif;

        /**
         * Delete data
         */
        if ($check_not_err):

            if (is_array($prod_del_list) && (!empty($prod_del_list))) {
                //p($prod_del_list);
                $item_delete = array();

                foreach ($prod_del_list as $rows) {
                    $a_data = explode(SEPARATOR, strip_tags($rows));
                    $location_id = $this->lc->getLocationIdByCode($a_data[$ci_location_from]);

                    if ($location_id == "" || $location_id = NULL):
                        $check_not_err = FALSE;
                        /*
                         * LOG MSG 6
                         */
                        $return['critical'][]['message'] = "Not have product in location for delete.";

                    else:
                        #Update PD_Reserv_Qty in Inbound Table
                        $product_del_query = $this->rl->show_product_relocation($order_id, $location_id);
                        $product_del_list = $product_del_query->result();

                        foreach ($product_del_list as $p) {

                            if (!empty($p->Inbound_Item_Id)) {

                                #update pd reserv into inbound
                                $result_PD_reserv_qty = $this->stock->reservPDReservQty($p->Inbound_Item_Id, $p->Confirm_Qty, "-");
                                if (!$result_PD_reserv_qty):
                                    $check_not_err = FALSE;
                                    $return['critical'][]['message'] = "Can not update quantity balance.";
                                /*
                                 * LOG MSG 10
                                 */
                                endif;
                            }
                        }// end update PD_Reserv_Qty in Inbound Table

                        $item_delete[] = $location_id;

                    endif; //end check find $location_id
                }//end for prod_del_list

                /**
                 * delete item list
                 */
                if ($check_not_err):
                    if (!empty($item_delete)):
                        $result_remove_location = $this->rl->removeReLocationDetail($item_delete, $order_id);
                        if (!$result_remove_location):
                            $check_not_err = FALSE;
                            $return['critical'][]['message'] = "Can not delete location list.";
                        /*
                         * LOG MSG 7
                         */
                        endif;
                    else:
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Not found location list for delete data.";
                    /*
                     * LOG MSG 8
                     */
                    endif;

                endif; //end check status err befor delete item
            }//end check empty $prod_del_list

        endif; //check status err before delete data

        /**
         * Add new inbound
         */
        if ($check_not_err):
            $this->load->library('Stock_lib');
            $respond = $this->stock_lib->move_location_approve($order_id);

            if ($respond != "C001"):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not insert new inbound.";
            /*
             * LOG MSG 13
             */
            endif;
        endif;

        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):

            $set_return['message'] = "Approve Re-Location Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;

            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

        else:

            $array_return['critical'][]['message'] = "Approve Re-Location Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);

            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

        endif;

        echo json_encode($json);
    }

    public function showZoneList() {

        $this->load->model("zone_model", "zone");
        $id = $this->input->post('warehouse_id');
        $zone_list = $this->zone->getZoneListByWarehouseID($id)->result();
        $data = array();

        foreach ((array) $zone_list as $index => $rows) :
            $data[$index]['Id'] = $rows->Zone_Id;
            $data[$index]['Zone_Name'] = $rows->Zone_NameEn;
        endforeach;

        $data = $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Show location for auto complete
     */
    public function showLocationList() {

        $criteria = array();

        if ($this->input->post('location_id')) :
            $criteria['l.Location_Code'] = $this->input->post('location_id');
        endif;

        if ((int) $this->input->post('warehouse_id') > 0) :
            $criteria['l.Warehouse_Id'] = $this->input->post('warehouse_id');
        endif;

        if ((int) $this->input->post('zone_id') > 0) :
            $criteria['l.Zone_Id'] = $this->input->post('zone_id');
        endif;

        $location_list = $this->rl->getLocationAll($criteria)->result();
        $data = array();

        foreach ((array) $location_list as $index => $rows) :
            $data[$index]['Id'] = $rows->Location_Id;
            $data[$index]['Location_Code'] = $rows->Location_Code;
            $data[$index]['Product_Status'] = $rows->Product_Status;
            $data[$index]['Product_Sub_Status'] = $rows->Product_Sub_Status;
            $data[$index]['Category'] = $rows->Dom_ID;
        endforeach;

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    public function showSuggestList() {
        $data = array();
        $criteria = array();
        if ($this->input->post('status')) :
            $criteria['status'] = $this->input->post('status');
        endif;

        if ($this->input->post('sub_status')) :
            $criteria['sub_status'] = $this->input->post('sub_status');
        endif;

        if ($this->input->post('category')) :
            $criteria['category'] = $this->input->post('category');
        endif;

        $pattern = '/^' . strtoupper($this->input->post('criteria')) . '/';

        $re_location_list = $this->rl->getRelocationList($criteria)->result();
        foreach ((array) $re_location_list as $index => $rows) :
            if (preg_match($pattern, $rows->Location_Code)) :
                $data[$index]['Id'] = $rows->Location_Id;
                $data[$index]['Location_Code'] = $rows->Location_Code;
            endif;
        endforeach;
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    public function show_suggest_list_by_location() {
     
    	$data = array();
    	$criteria = array();
    	if ($this->input->post('location_id')) :
            $criteria['location_id'] = $this->input->post('location_id');
    	endif;
    	$pattern = '/^' . strtoupper($this->input->post('criteria')) . '/';
    	$re_location_list = $this->rl->get_suggest_by_location($criteria)->result();
 
    	foreach ((array) $re_location_list as $index => $rows) :
    	if (preg_match($pattern, $rows->Location_Code)) :
    	$data[$index]['Id'] = $rows->Location_Id;
    	$data[$index]['Location_Code'] = $rows->Location_Code;
    	endif;
    	endforeach;
    	$this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    function get_location(){
        header('Access-control-Allow-0rigin: *');
        $params = $this->input->get();
        $this->load->library('getlocation_no_dispatch_area');
        $data = $this->getlocation_no_dispatch_area->get_location($params);
    }
  

    function show_suggest_list_by_product() {


    	$data = array();
 
    	$criteria = array();
  
        if($this->input->post('type')):
            if ($this->input->post('type') == 1) {
                if ($this->input->post('inbound_id')) :
                        $criteria['id'] = $this->input->post('inbound_id');
                endif;
            } else if ($this->input->post('type') == 2) {
                if ($this->input->post('item_id')) :
                        $criteria['id'] = $this->input->post('item_id');
                endif;
            }

            $criteria['type'] = $this->input->post('type');
        endif;

    	if ($this->input->post('qty')) :
    		$criteria['qty'] = $this->input->post('qty');
    	endif;

    	$pattern = '/^' . strtoupper($this->input->post('criteria')) . '/';


        $inb = $this->input->post('inbound_id');
        $getmaster = $this->rl->get_product_master($inb);
// p($getmaster); exit;
        if(!empty($getmaster)){
             $re_location_list = $this->rl->get_suggest_by_product_master($inb);
        } else{
            $re_location_list = $this->rl->get_suggest_by_product($criteria)->result();
        }
        // p($getmaster); exit;
    	foreach ((array) $re_location_list as $index => $rows) :
	    	if (preg_match($pattern, $rows->Location_Code)) :
	    		$data[$index]['Id'] = $rows->Location_Id;
	    		$data[$index]['Location_Code'] = $rows->Location_Code;
	    	endif;
    	endforeach;

   	$this->output->set_content_type('application/json')->set_output(json_encode($data));

    }

    // Add By Akkarapol, 29/10/2013, เพิ่มฟังก์ชั่นสำหรับ AutoComplete ใส่ใน controller 'reLocation' นี้เพราะเห็นว่ามันเกี่ยวข้องกับ Location เพื่อความเป็นสัดเป็นส่วนของ ฟังก์ชั่น นั่นเอง
    function autoCompleteActualLocation() {
        $text_search = $this->input->post('criteria');
        $tr_data_no = $this->input->post('tr_data_no');
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("location_model", "location");
//		$product=$this->p->searchProduct($text_search,$supplier_id);
        //$result = $this->location->getLocationByLikeCode($text_search);
		$result = $this->rl->showLocationAll($text_search);//edit funtion by kik : cos old function show all location (pre-receive , pre-dispatch)
		
//p($result);exit();
        $results = array();
        foreach ((array) $result as $idx => $data) :
            $results[$idx]['location_id'] = $data->Location_Id;
            $results[$idx]['location_code'] = $data->Location_Code;
            //$list.='<li onClick="fill(\''.$tr_data_no.'\',\''.$data->Location_Id.'\',\''.$data->Location_Code.'\');">'.$this->conv->tis620_to_utf8($data->Location_Code).'</li>';
        endforeach;
        $this->output->set_content_type('application/json')->set_output(json_encode($results));
        //echo $list;
    }

    // END Add By Akkarapol, 29/10/2013, เพิ่มฟังก์ชั่นสำหรับ AutoComplete ใส่ใน controller 'reLocation' นี้เพราะเห็นว่ามันเกี่ยวข้องกับ Location เพื่อความเป็นสัดเป็นส่วนของ ฟังก์ชั่น นั่นเอง
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
        $token = $this->input->post("token");
        $process_id = $this->input->post("process_id");
        $flow_id = $this->input->post("flow_id");
        $present_state = $this->input->post("present_state");
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

        /**
         * ================== Start Transaction =========================
         */
        $this->transaction_db->transaction_start();

        /**
         * get order data
         */
        if ($check_not_err):
            $flow_detail = $this->rl->getRelocationOrder($flow_id);
            if (empty($flow_detail)):
                $check_not_err = FALSE;

                /**
                 * 1
                 */
                $return['critical'][]['message'] = "Can not get relocation order data.";
            else:
                $order_id = $flow_detail[0]->Order_Id;
                $data['Document_No'] = $flow_detail[0]->Document_No;
            endif;

        endif;

        /**
         * update Workflow
         */
        if ($check_not_err):
            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
            if (empty($action_id) || $action_id == '') :
                $check_not_err = FALSE;

                /**
                 * 2
                 */
                $return['critical'][]['message'] = "Can not update Workflow.";
            endif;
        endif;

        /**
         * update Order
         */
        if ($check_not_err):
            #update data order
            $detail_order['Actual_Action_Date'] = NULL;
            $result_orders = $this->rl->updateRelocationData($detail_order, $order_id);
            if ($result_orders < 1) :
                $check_not_err = FALSE;

                /**
                 * 3
                 */
                $return['critical'][]['message'] = "Can not update relocate.";
            endif;

        endif;

        /**
         * update order detail
         */
        if ($check_not_err):

            #update data order detail
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
                 * 4
                 */
                $return['critical'][]['message'] = "Can not update relocate details.";
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

            $set_return['message'] = "Reject and Return Re-Location Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else :
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $set_return['critical'][]['message'] = "Reject and Return Re-Location Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($set_return, $return);

        endif;

        echo json_encode($json);
    }

    function rejectAction() {

        $token = $this->input->post("token");

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

            $flow_detail = $this->rl->getRelocationOrder($flow_id);
            if ($flow_detail) :
                $order_id = $flow_detail[0]->Order_Id;
                $data['Document_No'] = $flow_detail[0]->Document_No;
            endif;
        }

        #Variable for check error
        $check_not_err = TRUE;

        // validate token
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        $response = validate_token($token, $flow_id, $flow_info[0]->Present_State, $flow_info[0]->Process_Id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
            echo json_encode($json);
            exit();
        endif;


        #Check value not empty in variable
        if (empty($flow_id) || $flow_id == ""):
            $check_not_err = FALSE;

            /*
             * LOG MSG 1
             */
            $return['critical'][]['message'] = "Not have flow Id.";
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
                echo "<script>alert('Delete Re-Location Complete.');</script>";
                redirect('reLocation', 'refresh');
                #if data send from form page When return data to form page : add by kik : 2013-12-02
            } else {
                $array_return['success'][]['message'] = "Reject Re-Location Complete";
                $json['return_val'] = $array_return;
            }

        else:

            $this->transaction_db->transaction_rollback();

            #if data send from list page When refesh page  : add by kik : 2013-12-02
            if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {
                echo "<script>alert('Delete Re-Location not complete. Please check?');</script>";
                redirect('reLocation', 'refresh');

                #if data send from form page When return data to form page   : add by kik : 2013-12-02
            } else {
                $array_return['critical'][]['message'] = "Save Re-Location Incomplete";
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
//        unset($set_col_merge['Owner_Id']);
//        unset($set_col_merge['Renter_Id']);
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

        $set_col_merge['Order_Id'] = $set_data['Order_Id'];
        $set_col_merge['Document_No'] = "'".$set_data['Document_No']."'";
        $set_col_merge['Inbound_Item_Id'] = 'Inbound_Id';
        $set_col_merge['Old_Location_Id'] = 'Actual_Location_Id';

        $set_col_merge['Suggest_Location_Id'] = $set_data['Suggest_Location_Id'];
        if(empty($set_col_merge['Suggest_Location_Id'])):
            $set_col_merge['Suggest_Location_Id'] = "''";
        endif;

        $set_col_merge['Actual_Location_Id'] = $set_data['Actual_Location_Id'];
        if(empty($set_col_merge['Actual_Location_Id'])):
            $set_col_merge['Actual_Location_Id'] = "''";
        endif;

        $set_col_merge['Reserv_Qty'] = "(Receive_Qty-Dispatch_Qty-Adjust_Qty-PD_Reserv_Qty)";

        $set_col_merge['Confirm_Qty'] = $set_data['Confirm_Qty'];

        $set_col_merge['Putaway_By'] = $set_data['Putaway_By'];
        if(empty($set_col_merge['Putaway_By'])):
            $set_col_merge['Putaway_By'] = "''";
        endif;

        $set_col_merge['Putaway_Date'] = $set_data['Putaway_Date'];
        if(empty($set_col_merge['Putaway_Date'])):
            $set_col_merge['Putaway_Date'] = "''";
        endif;

        $set_col_merge['Remark'] = $set_data['Remark'];
        if(empty($set_col_merge['Remark'])):
            $set_col_merge['Remark'] = "''";
        endif;

        $set_col_insert = array_keys($set_col_merge);
        $set_col_insert = implode(',', $set_col_insert);
        $set_col_val = implode(',', $set_col_merge);

        return array($set_col_insert, $set_col_val);

    }

}

?>