<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class adjust_stock extends CI_Controller {

    public $settings;

    public function __construct() {
        parent::__construct();
        $this->settings = native_session::retrieve();
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->helper('form');
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", "flow");
        $this->load->model("company_model", "company");
        $this->load->model("system_management_model", "sys");
        $this->load->model("adjust_stock_model", "ad");
        $this->load->model("product_model", "p");
        $this->load->model("location_model", "lc");
        $this->load->model("re_location_model", "rl");
        $this->load->model("pallet_model", "pallet");
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));
        $this->load->model("inbound_model", "inbound");
        $this->load->controller('balance', 'balance');
    }

    public function index() {

        $this->output->enable_profiler($this->config->item('set_debug'));
        $query = $this->ad->showAdjustStockList("adjust_stock");
    ;
        $receive_list = $query->result();
        $column = array("Flow ID", "State Name", "Document No.", "Adjust Date", "Adjust Remark", "Process Day");
        $action = array(VIEW, DEL);
        if (($key = array_search(DEL, $action)) !== false) {
            unset($action[$key]);
        }
      
        $datatable = $this->datatable->genTableFixColumn($query, $receive_list, $column, "adjust_stock/openFormData", $action, "adjust_stock/rejectAction");
        $module = "";
        $sub_module = "";
        $this->parser->parse('list_template', array(
            'menu' => $this->menu_auth->loadMenuAuth()
            , 'menu_title' => 'Stock Adjustment'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "' ONCLICK=\"openForm('Re-Location','adjust_stock/openForm','A','')\">"
        ));
    }

    function openForm() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $process_id = 11;
        $present_state = 0;
        $parameter['renter_id'] = $this->config->item('renter_id');
        $parameter['consignee_id'] = $this->config->item('owner_id');
        $parameter['owner_id'] = $this->config->item('owner_id');
        $parameter['token'] = "";

        $r_renter = $this->company->getRenterAll()->result();
        $parameter['renter_list'] = genOptionDropdown($r_renter, "COMPANY", TRUE, FALSE);
        
        $r_product_status = $this->p->selectProductStatus()->result();
        $parameter['productStatus_select'] = form_dropdown("productStatus_select", genOptionDropdown($r_product_status, "SYS", FALSE, TRUE), "", "id=productStatus_select");
        
        $r_product_Substatus = $this->p->selectSubStatus()->result();
        $parameter['productSubStatus_select'] = form_dropdown("productSubStatus_select", genOptionDropdown($r_product_Substatus, "SYS", FALSE, TRUE), "", "id=productSubStatus_select");
        
        $dispatch_type = $this->sys->getAdjustType(); 
        $parameter['dispatch_type_select'] = form_dropdown("dispatch_type_select", genOptionDropdown($dispatch_type, "SYS", TRUE, TRUE), "ADJ1", "id=dispatch_type_select  class=required");

        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "adjust_stock");
        $show_column = array(
            _lang('no'),
            _lang('product_code'),
            _lang('product_name'),
            _lang('product_status'),
            _lang('product_sub_status'),
            _lang('lot'),
            _lang('serial'),
            _lang('product_mfd'),
            _lang('product_exp'),
            _lang('location_code'),
            _lang('est_balance_qty'),
            _lang('adjust_qty'),
            _lang('unit'),
            _lang('price_per_unit'),
            _lang('unit_price'),
            _lang('all_price'),
            "Inbound",
            "Sub_Status_Code",
            "Item_Id",
            "Unit_Id",
            "Price/Unit ID",
            _lang('pallet_code'),
            "DP_Type_Pallet",
            _lang('del'),
        );
        $parameter['show_column'] = $show_column;
        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['process_id'] = $process_id;
        $parameter['present_state'] = $present_state;
        $parameter['data_form'] = (array) $data_form;
        $parameter['price_per_unit'] = $this->settings['price_per_unit'];
        $parameter['config_pallet'] = $this->config->item('build_pallet');
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);

        $this->parser->parse('workflow_template', array(
            'state_name' => 'Stock Adjustment'
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_receive"></i>'
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'button_action' => $data_form->str_buttun
        ));
    }

    function showSelectedProduct() {
        $inb_code = $this->input->post("post_val");
        $dp_type_pallet = $this->input->post("dp_type_pallet_val");
        if ($dp_type_pallet == "NULL"):
            $dp_type_pallet = NULL;
        endif;
        $product_list = $this->ad->getProductPostFromArray($inb_code);
        $new_list = array();
        foreach ($product_list as $rows) {
            $rows['Est_Balance_Qty'] = set_number_format($rows['Est_Balance_Qty']);
            $rows['DP_Type_Pallet'] = $dp_type_pallet;
            $new_list[] = thai_json_encode($rows);
        }
        $json['locations'] = $new_list;
        $json['status'] = "1";
        $json['error_msg'] = "";

        echo json_encode($json);
    }

    function openFormData() {
        
        $this->output->enable_profiler($this->config->item('set_debug'));
        $flow_id = $this->input->post("id");
        $flow_detail = $this->flow->getFlowDetail($flow_id);
        $process_id = $flow_detail[0]->Process_Id;
        $order_id = $flow_detail[0]->Order_Id;
        $present_state = $flow_detail[0]->Present_State;
        $module = $flow_detail[0]->Module;

        $valid_state = validate_state($module);
        if ($valid_state) :
            redirect($valid_state);
        endif;

        $parameter['token'] = register_token($flow_id, $present_state, $process_id);

        $parameter['process_type'] = $flow_detail[0]->Process_Type;
        $parameter['document_no'] = $flow_detail[0]->Document_No;
        $parameter['doc_refer_int'] = $flow_detail[0]->Doc_Refer_Int;
        $parameter['doc_refer_ext'] = $flow_detail[0]->Doc_Refer_Ext;
        $parameter['doc_refer_inv'] = $flow_detail[0]->Doc_Refer_Inv;
        $parameter['doc_refer_ce'] = $flow_detail[0]->Doc_Refer_CE;
        $parameter['doc_refer_bl'] = $flow_detail[0]->Doc_Refer_BL;
        $parameter['owner_id'] = $flow_detail[0]->Owner_Id;
        $parameter['renter_id'] = $flow_detail[0]->Renter_Id;
        $parameter['receive_type'] = $flow_detail[0]->Doc_Type;
        $parameter['est_receive_date'] = $flow_detail[0]->Est_Action_Date;
        $parameter['remark'] = $flow_detail[0]->Remark;
        $parameter['is_urgent'] = $flow_detail[0]->Is_urgent;

        $parameter['order_detail'] = $this->ad->getAdjustOrderDetail($order_id);

        $r_renter = $this->company->getRenterAll()->result();
        $parameter['renter_list'] = genOptionDropdown($r_renter, "COMPANY", TRUE, FALSE);

        $dispatch_type = $this->sys->getAdjustType();
        $parameter['dispatch_type_select'] = form_dropdown("dispatch_type_select", genOptionDropdown($dispatch_type, "SYS", TRUE, TRUE), $parameter['receive_type'], "id=dispatch_type_select  class=required");

        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state,$this->session->userdata('user_id'));
        // p($process_id); exit;
        // $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "adjust_stock");
        // p($data_form); exit;
        $show_column = array(
            _lang('no'),
            _lang('product_code'),
            _lang('product_name'),
            _lang('product_status'),
            _lang('product_sub_status'),
            _lang('lot'),
            _lang('serial'),
            _lang('product_mfd'),
            _lang('product_exp'),
            _lang('location_code'),
            _lang('est_balance_qty'),
            _lang('adjust_qty'),
            _lang('unit'),
            _lang('price_per_unit'),
            _lang('unit_price'),
            _lang('all_price'),
            "Inbound",
            "Sub_Status_Code",
            "Item_Id",
            "Unit_Id",
            "Price/Unit ID",
            _lang('pallet_code'),
            "DP_Type_Pallet",
            _lang('del'),
        );

        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['flow_id'] = $flow_id;
        $parameter['process_id'] = $process_id;
        $parameter['present_state'] = $present_state;
        $parameter['show_column'] = $show_column;
        $parameter['data_form'] = (array) $data_form;
        $parameter['price_per_unit'] = $this->settings['price_per_unit'];
        $parameter['config_pallet'] = $this->config->item('build_pallet');
// p($data_form->form_name); exit;
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_receive"></i>'
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'button_action' => $data_form->str_buttun
        ));
    }

    function openAction() {

        $ci_product_Code = $this->input->post("ci_prod_code");
        $ci_product_status = $this->input->post("ci_product_status");
        $ci_product_lot = $this->input->post("ci_product_lot");
        $ci_product_serial = $this->input->post("ci_product_serial");
        $ci_suggest_location_id = $this->input->post("ci_suggest_location_id");
        $ci_confirm_Qty = $this->input->post("ci_confirm_qty");
        $ci_unit_Id = $this->input->post("ci_unit_Id");
        $ci_unit_Value = $this->input->post("ci_unit_Value");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_product_sub_status = $this->input->post("ci_product_sub_status");
        $ci_item_id = $this->input->post("ci_item_id");

        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }

        if ($this->config->item('build_pallet') == TRUE) {
            $ci_dp_type_pallet = $this->input->post("ci_dp_type_pallet");
        }

        $prod_list = $this->input->post("prod_list");

        $return = array();

        $resultCompare = $this->balance->_chkPDreservBeforeOpen($ci_confirm_Qty, $ci_inbound_id, $prod_list, SEPARATOR);
        $return = array_merge_recursive($return, $resultCompare);

        if (!empty($return['critical'])) :
            $json['status'] = "validation";
            $json['return_val'] = $return;
        else:

            $check_not_err = TRUE;
            $process_id = $this->input->post("process_id");
            $present_state = $this->input->post("present_state");
            $process_type = $this->input->post("process_type");
            $action_type = $this->input->post("action_type");
            $next_state = $this->input->post("next_state");
            $user_id = $this->input->post("user_id");
            $prod_list = $this->input->post("prod_list");
            $est_action_date = date("Y-m-d H:i:s");
            $renter_id = $this->input->post("renter_id");
            $doc_type = $this->input->post("dispatch_type_select");
            $owner_id = $this->input->post("owner_id");
            $receive_type = '';
            $remark = $this->input->post("remark");
            $is_urgent = $this->input->post("is_urgent");

            if ($is_urgent != ACTIVE) {
                $is_urgent = INACTIVE;
            }

            $this->transaction_db->transaction_start();

            if ($check_not_err):
                $document_no = create_document_no_by_type("ADJ");
                $data['Document_No'] = $document_no;
                if ($data['Document_No'] == "" || empty($data['Document_No'])) {
                    log_message('error', 'adjust stock - cannot create document');
                    $check_not_err = FALSE;
                }
            endif;

            if ($check_not_err):
                list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data);
                if ( empty($flow_id) || empty($action_id) ) {
                    log_message('error', 'adjust stock - cannot add new workflow');
                    $check_not_err = FALSE;
                }
            endif;

            if ($check_not_err):
                $order = array(
                    'Flow_Id' => $flow_id
                    , 'Document_No' => strtoupper($document_no)
                    , 'Doc_Refer_Ext' => ''
                    , 'Doc_Refer_Int' => ''
                    , 'Doc_Refer_Inv' => ''
                    , 'Doc_Refer_CE' => ''
                    , 'Doc_Refer_BL' => ''
                    , 'Doc_Type' => $doc_type
                    , 'Owner_Id' => $owner_id
                    , 'Renter_Id' => $renter_id
                    , 'Estimate_Action_Date' => $est_action_date
                    , 'Source_Id' => ''
                    , 'Destination_Id' => ''
                    , 'Source_Type' => 'Warehouse'
                    , 'Destination_Type' => 'Customer'
                    , 'Create_By' => $user_id
                    , 'Create_Date' => date("Y-m-d H:i:s")
                    , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                    , 'Process_Type' => $process_type
                    , 'Is_urgent' => $is_urgent
                );


                $order_id = $this->stock->addOrder($order);
                if ($order_id == "" || empty($order_id)) {
                    log_message('error', 'adjust stock - cannot add new order');
                    $check_not_err = FALSE;
                }

            endif;

            if ($check_not_err):
                $order_detail = array();
                $inb_id = array();

                if (!empty($prod_list)) {
                    foreach ($prod_list as $rows) {
                        $detail = array();
                        $a_data = explode(SEPARATOR, $rows);

                        if (!in_array($a_data[$ci_inbound_id], $inb_id)) {
                            $info = $this->rl->inboundDetail($a_data[$ci_inbound_id]);
                            $detail['Order_Id'] = $order_id;
                            $detail['Product_Id'] = $this->p->getProductIDByProdCode($a_data[$ci_product_Code]);
                            $detail['Product_Code'] = $a_data[$ci_product_Code];
                            $detail['Product_Status'] = $a_data[$ci_product_status];
                            $detail['Product_Sub_Status'] = $a_data[$ci_product_sub_status];
                            $detail['Product_Lot'] = $a_data[$ci_product_lot];
                            $detail['Product_Serial'] = $a_data[$ci_product_serial];
                            $detail['Product_License'] = $info[0]->Product_Serial;
                            $detail['Product_Mfd'] = $info[0]->Product_Mfd;
                            $detail['Product_Exp'] = $info[0]->Product_Exp;
                            $detail['Pallet_Id'] = $info[0]->Pallet_Id;
                            $detail['Reserv_Qty'] = str_replace(",", "", $a_data[$ci_confirm_Qty]);
                            $detail['Unit_Id'] = $a_data[$ci_unit_Id];
                            $detail['Suggest_Location_Id'] = $this->lc->getLocationIdByCode(trim($a_data[$ci_suggest_location_id]), '');
                            $detail['Inbound_Item_Id'] = $a_data[$ci_inbound_id];
                            $inb_id[] = $a_data[$ci_inbound_id];

                            if ($this->settings['price_per_unit'] == TRUE) {
                                $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]);
                                $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                                $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]);
                            }

                            $detail['Cont_Id'] = $info[0]->Cont_Id;
                            $detail['Invoice_Id'] = $info[0]->Invoice_Id;

                            if ($this->config->item('build_pallet') == TRUE) {
                                if ($a_data[$ci_dp_type_pallet] == "") {
                                    $detail['DP_Type_Pallet'] = NULL;
                                } else {
                                    $detail['DP_Type_Pallet'] = $a_data[$ci_dp_type_pallet];
                                }
                            }

                            $order_detail[] = $detail;
                        }
                    }
                }

                if (!empty($order_detail)):

                    $result_order_detail = $this->stock->addOrderDetail($order_detail);
                    if ($result_order_detail <= 0):
                        log_message('error', 'adjust stock - cannot add new order detail');
                        $check_not_err = FALSE;
                    endif;

                    $result_PD_reserv_qty = $this->stock->reservPDReservQtyArray($order_detail);
                    if (!$result_PD_reserv_qty):
                        log_message('error', 'adjust stock - cannot reserv PD');
                        $check_not_err = FALSE;
                    endif;

                endif; // END Order Detail

            endif; // END Check error

            if ($check_not_err):

                $array_return['success'][]['message'] = "Save Adjust Stock Complete";
                $json['return_val'] = $array_return;
                $this->transaction_db->transaction_end();

            else:

                $array_return['critical'][]['message'] = "Save Adjust Stock Incomplete";
                $json['return_val'] = $array_return;
                $this->transaction_db->transaction_rollback();

            endif;

            $json['status'] = "save";


        endif; // END Critical Return 

        echo json_encode($json);
        
    }

    function confirmAction() {

        $token = $this->input->post('token');
        $flow_id = $this->input->post("flow_id");
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order');
        $order_id = $flow_detail[0]->Order_Id;
        $prod_list = $this->input->post("prod_list");

        $ci_confirm_Qty = $this->input->post("ci_confirm_qty");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_product_Code = $this->input->post("ci_prod_code");

        $return = array();

        $resultCompare = $this->balance->_chkPDreservBeforeApprove($ci_confirm_Qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Order_Detail');
        $return = array_merge_recursive($return, $resultCompare);

        $check_not_err = TRUE;

        $response = validate_token($token, $flow_id, $flow_detail[0]->Present_State, $flow_detail[0]->Process_Id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;

        if (!empty($return['critical'])) :

            $json['status'] = "validation";
            $json['return_val'] = $return;

        else:

            $this->transaction_db->transaction_start();

            if ($check_not_err):
                $respond = $this->_updateProcess($this->input->post());
                if (!$respond):
                    $check_not_err = FALSE;
                endif;
            endif;

            if ($check_not_err):

                $array_return['success'][]['message'] = "Confirm Adjust Stock Complete";
                $json['return_val'] = $array_return;

                $this->transaction_db->transaction_end();

            else:

                $array_return['critical'][]['message'] = "Save Adjust Stock Incomplete";
                $json['return_val'] = $array_return;

                $this->transaction_db->transaction_rollback();

            endif;

            $json['status'] = "save";

        endif;

        echo json_encode($json);
        
    }


    function quickApproveAction() {
        $this->approveAction();
    }

    function approveAction() {

        $check_not_err = TRUE;
        $token = $this->input->post("token");
        $flow_id = $this->input->post("flow_id");
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order');
        $order_id = $flow_detail[0]->Order_Id;
        $prod_list = $this->input->post("prod_list");

        $ci_confirm_Qty = $this->input->post("ci_confirm_qty");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_product_Code = $this->input->post("ci_prod_code");

        $response = validate_token($token, $flow_id, $flow_detail['0']->Present_State, $flow_detail['0']->Process_Id);

        if (!$response) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
            $check_not_err = FALSE;
        endif;

        $return = array();

        $resultCompare = $this->balance->_chkPDreservBeforeApprove($ci_confirm_Qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, SEPARATOR, 'STK_T_Order_Detail');
        $return = array_merge_recursive($return, $resultCompare);

        if (!empty($return['critical'])) :
            $json['status'] = "validation";
            $json['return_val'] = $return;

        else:

            $this->transaction_db->transaction_start();

            if ($check_not_err):
                $respond = $this->_updateProcess($this->input->post());
                if (!$respond):
                    log_message('error', 'adjust stock - cannot update process');
                    $check_not_err = FALSE;
                endif;
            endif;

            if ($check_not_err) :
                $result_updateInbound_query = $this->stock_lib->adjust_update_order($order_id);
                if (!$result_updateInbound_query):
                    log_message('error', 'adjust stock - cannot update order');
                    $check_not_err = FALSE;
                endif;
            endif;

            if ($check_not_err) :

                if ($this->config->item('build_pallet') == TRUE) :
                    $result_updatePallet_query = $this->_update_pallet_adjust($order_id);
                    if (!$result_updatePallet_query):
                        log_message('error', 'adjust stock - cannot update pallet adjust');
                        $check_not_err = FALSE;
                    endif;
                endif;

            endif;

            if ($check_not_err):

                $array_return['success'][]['message'] = "Approve Adjust Stock Complete";
                $json['return_val'] = $array_return;

                $this->transaction_db->transaction_end();

            else:

                $array_return['critical'][]['message'] = "Save Adjust Stock Incomplete";
                $json['return_val'] = $array_return;

                $this->transaction_db->transaction_rollback();

            endif;

            $json['status'] = "save";

        endif;

        echo json_encode($json);
        
    }

    function _update_pallet_adjust($order_id) {

        $check_not_err = TRUE;

        $details = $this->pallet->insert_pallet_detail_order($order_id);

        $result_db_fulls = $this->pallet->get_pallet_in_orderDetail($order_id);

        if (!empty($result_db_fulls)):

            foreach ($result_db_fulls as $result_db_full):
                $colunm_pallet['Active'] = 'N';
                $where_pallet['Pallet_Id'] = $result_db_full->Pallet_Id;
                $result_pallet = $this->pallet->update_pallet_colunm($colunm_pallet, $where_pallet);
                if ($result_pallet):
                    $check_not_err = FALSE;
                endif;

            endforeach;

        endif;

        return $check_not_err;
    }

    public function _updateProcess() {

        $flow_id = $this->input->post("flow_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->input->post("user_id");
        $prod_list = $this->input->post("prod_list");
        $renter_id = $this->input->post("renter_id");
        $doc_type = $this->input->post("dispatch_type_select");
        $owner_id = $this->input->post("owner_id");
        $remark = $this->input->post("remark");
        $is_urgent = $this->input->post("is_urgent");

        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Parameter Index Datatable
        $ci_product_Code = $this->input->post("ci_prod_code");
        $ci_product_status = $this->input->post("ci_product_status");
        $ci_product_lot = $this->input->post("ci_product_lot");
        $ci_product_serial = $this->input->post("ci_product_serial");
        $ci_suggest_location_id = $this->input->post("ci_suggest_location_id");
        $ci_confirm_Qty = $this->input->post("ci_confirm_qty");
        $ci_unit_Id = $this->input->post("ci_unit_Id");
        $ci_unit_Value = $this->input->post("ci_unit_Value");
        $ci_inbound_id = $this->input->post("ci_inbound_id");
        $ci_product_sub_status = $this->input->post("ci_product_sub_status");
        $ci_item_id = $this->input->post("ci_item_id");

        # Parameter Price per uint
        if ($this->settings['price_per_unit'] == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }

        # Parameter Build pallet
        if ($this->config->item('build_pallet') == TRUE) {
            $ci_dp_type_pallet = $this->input->post("ci_dp_type_pallet");
        }

        $check_not_err = TRUE;
        # ================== Start save data =========================
        # ============================================================

        if (empty($flow_id) || $flow_id == "" || empty($prod_list) || $prod_list == ""):
            $check_not_err = FALSE;
        /*
         * LOG MSG 1
         */
        endif;

        # Get workflow
        if ($check_not_err):
            $flow_detail = $this->flow->getFlowDetail($flow_id);

            #check not empty data in workflow
            if (empty($flow_detail)):
                $check_not_err = FALSE;
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
            /*
             * LOG MSG 3
             */
            endif;
        endif;

        #update Order Table
        if ($check_not_err):
            $order = array(
                'Doc_Type' => $doc_type
                , 'Owner_Id' => $owner_id
                , 'Renter_Id' => $renter_id
                , 'Modified_By' => $user_id
                , 'Modified_Date' => date("Y-m-d H:i:s")
                , 'Actual_Action_Date' => date("Y-m-d H:i:s")
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Is_urgent' => $is_urgent
            );

            $where['Order_Id'] = $order_id;
            $result_order_query = $this->stock->updateOrder($order, $where);
            if (!$result_order_query):
                $check_not_err = FALSE;
            /*
             * LOG MSG 4
             */
            endif;
        endif;

        #update Order Detail Table
        if ($check_not_err):

            if (!empty($prod_list)) {

                foreach ($prod_list as $rows) {

                    unset($where);

                    $a_data = explode(SEPARATOR, $rows); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้

                    $detail['Reserv_Qty'] = $a_data[$ci_confirm_Qty];

                    if ($next_state < 0) {
                        $detail['Confirm_Qty'] = $a_data[$ci_confirm_Qty];
                    }

                    $detail['Activity_Code'] = 'ADJ-STOCK';
                    $detail['Activity_By'] = $this->session->userdata("user_id");
                    $detail['Activity_Date'] = date('Y-m-d H:i:s');

                    //ADD BY KIK 2014-01-14 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                    if ($this->settings['price_per_unit'] == TRUE) {
                        $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]); //+++++ADD BY KIK 2014-01-14 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                        $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                        $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]); //+++++ADD BY KIK 2014-01-14 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                    }
                    //END ADD
                    # by kik , 08-10-2013
                    # หลักการ update PD reserv ใน inbound กรณีที่ เคยเพิ่มค่าเข้าไปแล้ว
                    # 1.เช็คค่า reserv_qty ใน order detail ว่า ค่าที่รับมาใหม่ มีค่าเท่ากับที่เคย add เข้าไปแล้วหรือไม่ 
                    # 2.ถ้าใช่ ให้อัพเดตตามปกติ ไม่มีการเปลี่ยนแปลงค่าใน PD_reserv ใน inbound
                    # 3.ถ้าไม่ใช่ ให้ลบ PD_reserv ใน inbound ออก ตามจำนวนเก่าที่ดึงมาจาก orderDetail ก่อนที่จะอัพเดตค่าให้เข้าไปในตาราง 
                    # 4.และบวกค่า reservQty ใหม่เข้าไปใน PD_reserv ของ inbound เพื่อให้ค่าเป็นปัจจุบันที่สุด

                    $oldReservQty = $this->stock->getOldReservQtyOrderDetailByItemId($a_data[$ci_item_id]);

                    if ($oldReservQty['Reserv_Qty'] != $detail['Reserv_Qty']) {//(1)
                        if ($check_not_err):
                            $result_Del_PDrervqty = $this->stock->reservPDReservQty($a_data[$ci_inbound_id], $oldReservQty['Reserv_Qty'], "-"); //(3)
                            if ($result_Del_PDrervqty < 1):
                                $check_not_err = FALSE;
                            /*
                             * LOG MSG 5
                             */
                            endif;
                        endif;

                        if ($check_not_err):
                            $result_Pos_PDrervqty = $this->stock->reservPDReservQty($a_data[$ci_inbound_id], $a_data[$ci_confirm_Qty], "+"); //(4)
                            if ($result_Pos_PDrervqty < 1):
                                $check_not_err = FALSE;
                            /*
                             * LOG MSG 6
                             */
                            endif;
                        endif;
                    }
                    //(2)

                    unset($oldReservQty);

                    $where['Item_Id'] = $a_data[$ci_item_id];
                    $where['Order_Id'] = $order_id;
                    $where['Product_Code'] = $a_data[$ci_product_Code];

                    $result_orderDetail_query = $this->stock->updateOrderDetail($detail, $where);

                    if (!$result_orderDetail_query):
                        $check_not_err = FALSE;
                    /*
                     * LOG MSG 7
                     */
                    endif;
                }//foreach ($prod_list as $rows)
            }//if (!empty($prod_list))

        endif;


        return $check_not_err;
    }

    #End New Comment Code #ISSUE 2233
    #---------------------------------------------------------------------------------------
    #=======================================================================================
    #ISSUE 3034 Reject Document 
    #DATE:2014-01-07
    #BY:KIK
    #เพิ่มในส่วนของ reject
    #START New Comment Code #ISSUE 3034 Reject Document 
    #add code for reject

    function rejectAction() {

        $this->load->model("stock_model", "stock");

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
        // p($next_state); exit;

#------------------------------------------------------------
        #Variable for check error
        $check_not_err = TRUE;

        #Check value not empty in variable
        if (empty($flow_id) || $flow_id == ""):
            $check_not_err = FALSE;

        /*
         * LOG MSG 1
         */
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
            endif;
        endif;
        if ($check_not_err):

            #return qty to PD_reserv in Inbound table
            $order_detail = $this->stock->getOrderDetailByOrderId($order_id);

            #update inbound qty in Inbound table
            if (!empty($order_detail)):
                $result_inboundQty_query = $this->stock->reservPDReservQtyArray($order_detail, "-");
                if (!$result_inboundQty_query):
                    $check_not_err = FALSE;
                /*
                 * LOG MSG 4
                 */
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
                endif;
            endif;

            if ($check_not_err):

                $this->transaction_db->transaction_end();

                #if data send from list page When refesh page 
                if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {
                    echo "<script>alert('Delete Adjust Stock Complete.');</script>";
                    redirect('adjust_stock', 'refresh');
                    #if data send from form page When return data to form page 
                } else {
                    $array_return['success'][]['message'] = "Reject Adjust Stock Complete";
                    $json['return_val'] = $array_return;
                }

            else:

                $this->transaction_db->transaction_rollback();

                #if data send from list page When refesh page
                if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {
                    echo "<script>alert('Delete Adjust Stock not complete. Please check?');</script>";
                    redirect('adjust_stock', 'refresh');

                    #if data send from form page When return data to form page  
                } else {
                    $array_return['critical'][]['message'] = "Reject Adjust Stock Complete";
                    $json['return_val'] = $array_return;
                }

            endif;

        else:

            $this->transaction_db->transaction_rollback();

            #if data send from list page When refesh page
            if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {
                echo "<script>alert('Delete Adjust Stock not complete. Please check?');</script>";
                redirect('adjust_stock', 'refresh');

                #if data send from form page When return data to form page  
            } else {
                $array_return['critical'][]['message'] = "Save Adjust Stock Incomplete";
                $json['return_val'] = $array_return;
            }

        endif;

        $json['status'] = "save";
        echo json_encode($json);
    }

    /**
     * @function validation_openAction for work validation in Open stock adjustment 
     * @author kik : 20140304
     * @return validation data format
     */
    function validation_openAction() {

        $data = $this->input->post();
        /**
         * set Variable
         */
        $return = array();

        /**
         * chk PD_reserv Before Open
         */
        $resultCompare = $this->balance->_chkPDreservBeforeOpen($data['ci_confirm_qty'], $data['ci_inbound_id'], $data['prod_list'], SEPARATOR);
        $return = array_merge_recursive($return, $resultCompare);


        /**
         * chk Re order Point Before Open
         */
        $chk_re_order_point = $this->balance->chk_re_order_point($data['ci_confirm_qty'], $data['ci_prod_code'], $data['prod_list'], SEPARATOR);

        if (!empty($chk_re_order_point['critical'])):
            $chk_re_order_point['warning'] = $chk_re_order_point['critical'];
            unset($chk_re_order_point['critical']);
            $return = array_merge_recursive($return, $chk_re_order_point);
        endif;

//        p($return);
//        exit();

        if (!empty($return)):
            $json['status'] = "validation";
            $json['return_val'] = $return;
        else:
            $json['status'] = 'pass';
            $json['return_val'] = '';
        endif;

        echo json_encode($json);
    }

    /**
     * @function validation_confirmAction for work validation in Confirm stock adjustment 
     * @author kik : 20140304
     * @return validation data format
     */
    function validation_confirmAction() {

        $data = $this->input->post();
        $return = $this->check_validate_data($data);

        echo json_encode($return);
    }

    /**
     * @function validation_approveAction for work validation in Approve stock adjustment 
     * @author kik : 20140304
     * @return validation data format
     */
    function validation_approveAction() {

        $data = $this->input->post();
        $return = $this->check_validate_data($data);

        echo json_encode($return);
    }

    /**
     * @function validation_approveAction for work validation in Approve stock adjustment 
     * @author kik : 20140304
     * @return validation data format
     */
    function validation_quickApproveAction() {

        $data = $this->input->post();
        $return = $this->check_validate_data($data);

        echo json_encode($return);
    }

    /**
     * @function check_validate_data for work check validate data in adjust stock form (use 3 fucntion >> open,confirm,approve adjust stock)
     * @author kik : 20140304
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

        # Parameter Form
        $flow_id = $this->input->post("flow_id");
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order');
        $order_id = @$flow_detail[0]->Order_Id;

        /**
         * chk PD_reserv Before Confirm,Approve
         */
        $resultCompare = $this->balance->_chkPDreservBeforeApprove($data['ci_confirm_qty'], $data['ci_inbound_id'], $data['ci_item_id'], $order_id, $data['prod_list'], SEPARATOR, 'STK_T_Order_Detail');
        $return = array_merge_recursive($return, $resultCompare);

        /**
         * chk Re order Point Before Confirm,Approve
         */
        $chk_re_order_point = $this->balance->chk_re_order_point($data['ci_confirm_qty'], $data['ci_prod_code'], $data['prod_list'], SEPARATOR);

        if (!empty($chk_re_order_point['critical'])):
            $chk_re_order_point['warning'] = $chk_re_order_point['critical'];
            unset($chk_re_order_point['critical']);
            $return = array_merge_recursive($return, $chk_re_order_point);
        endif;

//        p($return);
//        exit();

        if (!empty($return)):
            $json['status'] = "validation";
            $json['return_val'] = $return;
        else:
            $json['status'] = 'pass';
            $json['return_val'] = '';
        endif;

        return $json;
    }

}