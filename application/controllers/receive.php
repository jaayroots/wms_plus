<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Receive extends CI_Controller {

    public $settings;

    public function __construct() {
        parent::__construct();
        $this->load->model("workflow_model", "flow");
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));
        $this->load->model("product_model", "p");
        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", 'flow');
        $this->load->model("invoice_model", "invoice");
        $this->load->model("container_model", "container");
        $this->load->model("authen_model", "auth");
        $this->load->model("report_model", "r");
        $this->load->model("company_model", "company");
        $this->load->model("system_management_model", "sys");
        $this->load->model("pallet_model", "pallet");

        $this->settings = native_session::retrieve();
        $isUserLogin = $this->session->userdata("user_id");
        if ($isUserLogin == "") {
            echo "<script>alert('Your session was expired. Please Log-in!')</script>";
            echo "<script>window.location.replace('" . site_url() . "');</script>";
        }
        $this->disable_row_datatable = "disable_row_datatable";
    }

#  Open Form With Data

    function openActionForm() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        if ($_POST) {
            $flow_id = $this->input->post("id");
            $this->session->set_userdata(array('receive_id' => $flow_id));
        } else {
            $flow_id = $this->session->userdata("receive_id");
        }
        //

        $this->load->model("contact_model", "contact");
        $this->load->model("company_model", "company");
        $this->load->model("system_management_model", "sys");

        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $conf_pallet_tally = empty($conf['build_pallet_tally_sheet']) ? false : @$conf['build_pallet_tally_sheet'];
        $conf_ce_require = empty($conf['custom_entry_require']) ? false : @$conf['custom_entry_require'];

        #Retrive Data from Table
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order'); // Edit by Ak add 'STK_T_Order' for get data in this table
        $process_id = $flow_detail[0]->Process_Id;
        $order_id = $flow_detail[0]->Order_Id;
        $present_state = $flow_detail[0]->Present_State;

        $module = $flow_detail['0']->Module;

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
        $parameter['is_urgent'] = $flow_detail[0]->Is_urgent;

        $parameter['Sub_Module'] = $flow_detail[0]->Sub_Module; // Add by Ton! 20130829 For show split product button.

        /**
         * GET Order Detail
         */
        $order_by = "STK_T_Order_Detail.Item_Id ASC";
        $order_deatil = $this->stock->getOrderDetail($order_id, false, $order_by, NULL, NULL);  //add by kik : for change parameter to function : 20141004
//        $order_deatil = $this->stock->getOrderDetail($order_id, 67); //67 เธเธทเธญเธเธญเธเธงเนเธฒ step 67 เนเธเธฃเน€เธเนเธเธเธนเนเธ—เธณเธฃเธฒเธขเธเธฒเธฃ receive เธซเธเนเธฒ HH //comment by kik : 20141004
        $parameter['order_id'] = $order_id;
        $parameter['order_deatil'] = $order_deatil;
//        p($order_deatil,true);


        $show_tally = FALSE;
        foreach ($order_deatil as $key => $value):
            if (!empty($value->Cont_Id)):
                $show_tally = TRUE;
                break;
            endif;
        endforeach;

//        //ADD BY POR 2014-07-10 select container name by order_id
//        $parameter['doc_refer_container'] = "";
//        $container_name = $this->container->getContainerByOrderId($order_id,1)->result_array();
//
//        if(!empty($container_name)):
//            $parameter['doc_refer_container'] = $container_name[0]['Cont_No']." ".$container_name[0]['Cont_Size_No'].$container_name[0]['Cont_Size_Unit_Code'];
//        endif;
//
//        //END ADD
        // Change code for set Show Container by Multiple row because old code is show 1st row only.
        $parameter['doc_refer_container'] = array();
        $container_name = $this->container->getContainerByOrderId($order_id)->result_array();
        if (!empty($container_name)):
            foreach ($container_name as $key_container => $container):
                $parameter['doc_refer_container'][] = $container['Cont_No'] . " " . $container['Cont_Size_No'] . $container['Cont_Size_Unit_Code'];
            endforeach;
        endif;


        //ADD BY POR 2014-07-07 select container size for size list in container
        $container_size = $this->container->getContainerSize()->result();
        $_container_size = array();
        foreach ($container_size as $idx => $value) {
            $_container_size[$idx]['Id'] = $value->Cont_Size_Id;
            $_container_size[$idx]['No'] = $value->Cont_Size_No;
            $_container_size[$idx]['Unit_Code'] = $value->Cont_Size_Unit_Code;
        }
        $parameter['container_size_list'] = json_encode($_container_size);
        //END ADD
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
        $vendor_list = genOptionDropdown($r_vendoe, "COMPANY", TRUE, TRUE); // Edit by kik! 20131107
        $parameter['vendor_list'] = $vendor_list;

//        $data_form = $this->workflow->openWorkflowForm($process_id);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowReceiveList"); // Button Permission. Add by Ton! 20140131

        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['flow_id'] = $flow_id;
        $parameter['process_id'] = $process_id;
        $parameter['present_state'] = $present_state;
        $parameter['data_form'] = (array) $data_form;

        # Tark 3034 Reject Document
        # add code for check Is order partial or order_return?
        #   1. order เธ—เธตเนเธ–เธนเธ return เธเธฅเธฑเธเธกเธฒเธเธฒเธ putway module เธเธฅเธฑเธเธกเธฒเธ—เธตเน receive module
        #	1.1 เนเธกเนเธชเธฒเธกเธฒเธฃเธ–เน€เธเธดเนเธกเธฅเธ item เนเธ”เน
        #	1.2 เนเธกเนเธชเธฒเธกเธฒเธฃเธ– split เธเนเธญเธกเธนเธฅเนเธ”เน
        #	1.3 update เธเนเธญเธกเธนเธฅเนเธ inbound item เนเธ—เธเธเธฒเธฃเธชเธฃเนเธฒเธเนเธซเธกเน
        #   2. เธเธฃเธ“เธตเธ—เธตเนเน€เธเนเธ partial
        #	2.1 เนเธกเนเธชเธฒเธกเธฒเธฃเธ– return เธเธฅเธฑเธเนเธเธ—เธตเน state 3
        #	2.2 เนเธกเนเธชเธฒเธกเธฒเธฃเธ–เนเธเนเนเธ qty เนเธ”เน เนเธ”เน
        #	2.3 เธ•เธฃเธงเธเธชเธญเธเนเธเนเธ”เน€เธเธทเนเธญเนเธกเนเนเธซเนเธชเธฃเนเธฒเธ partial เนเธซเธกเน เธซเธฒเธ order เธเธตเนเธ–เธนเธเน€เธญเธฒเนเธเธ—เธณเน€เธเนเธ parent partial เนเธฅเนเธง
        # by kik : 2013-11-29

        $result_is_partial = $this->check_partial($order_id);
        if (!$result_is_partial) {
            $chk_is_partial = FALSE;
        } else {
            $chk_is_partial = TRUE;
        }

        $result_is_orderReturn = $this->check_order_return($order_id);
        if (!$result_is_orderReturn) {
            $chk_is_orderReturn = FALSE;
        } else {
            $chk_is_orderReturn = TRUE;
        }

        $parameter['is_partial'] = $chk_is_partial;            // Add by kik : 2013-11-29
        $parameter['is_orderReturn'] = $chk_is_orderReturn;   // Add by kik : 2013-11-29
//         echo $chk_is_partial."-";
//         echo $chk_is_orderReturn;
//          p($data_form->state_detail);
        if ($chk_is_partial) {

            $tmp_state_detail = new ArrayObject();
            foreach ($data_form->state_detail as $state):

                if ($state->To_State != -1) {
                    if ($state->To_State > 3) {
                        $tmp_state_detail[] = $state;
                    }
                } else {
                    $tmp_state_detail[] = $state;
                }

            endforeach;

            $str_button = $this->workflow->genButton($tmp_state_detail);
            $data_form->str_buttun = $str_button;
        }

        # end Tark 3034 Reject Document
        # by kik : 2013-11-29
        # Tark 3034 Reject Document
        # fix button sort : add by kik : 2013-12-03
        #เนเธเนเนเธเธเธฑเธเธซเธฒ sort button เธ—เธตเนเนเธกเนเน€เธฃเธตเธขเธเธเธฑเธ เธเธถเนเธเน€เธเธดเธ”เธกเธฒเธเธฒเธ query เธ—เธตเนเธ”เธถเธเธกเธฒ sort เธ”เนเธงเธข to_state เนเธ•เน state 7 เธเธฑเนเธเธ–เธนเธเน€เธเธดเนเธกเธกเธฒเธ—เธตเธซเธฅเธฑเธ เธ—เธณเนเธซเนเธเธฒเธฃเน€เธฃเธตเธขเธเธเธธเนเธกเธเธดเธ”เธเธฅเธฒเธ”
        #เธซเธฒเธงเนเธฒเธกเธตเธเธธเนเธก state 7 เธซเธฃเธทเธญเนเธกเน เธ–เนเธฒเนเธเน เนเธซเนเน€เธเนเธเธเนเธฒเนเธงเนเน€เธเธทเนเธญเธ—เธณเธเธฒเธฃ sort เนเธซเธกเน
        #เธ–เนเธฒเธขเธเนเธฒ array เธเธธเนเธก เน€เธเนเธฒ $tmp_sort_button เน€เธเธทเนเธญเนเธเน sort เธเธฃเธ“เธตเธ—เธตเนเธกเธต to_state 7
//        $tmp_sort_button = array();
//        $chk_state7 = FALSE;
//         foreach($data_form->state_detail as $state):
//
//                if($state->To_State == 7){
//                    #เนเธชเน index array เนเธเธงเนเธฒ เธ–เนเธฒเน€เธเนเธ to_state 7 เนเธซเนเน€เธเนเธ index 4 เน€เธเธทเนเธญเนเธเนเธซเธฅเธญเธเธ•เธญเธ sort
//                    $tmp_sort_button[4] = $state;
//                    $chk_state7= TRUE;
//                }else{
//                    $tmp_sort_button[$state->To_State] = $state;
//                }
//         endforeach;
//
//         #เธ–เนเธฒเธกเธต to_state 7 เนเธซเน เน€เธฃเธตเธขเธเธฅเธณเธ”เธฑเธเธเธญเธเธเธธเนเธกเนเธซเธกเนเธ—เธฑเนเธเธซเธกเธ”
//         if($chk_state7){
//             #เน€เธฃเธตเธขเธเธฅเธณเธ”เธฑเธเนเธซเธกเน เธ•เธฒเธก index เธ—เธตเนเน€เธเนเธ to_state
//             ksort($tmp_sort_button);
//             foreach($tmp_sort_button as $state):
//
//                    $tmp_state_detail[] = $state;
//
//            endforeach;
//
//            #gen button เนเธซเธกเน เธเธฃเธ“เธตเธ—เธตเนเธเนเธฒเธเธเธฒเธฃ sort เธกเธฒ
//            $str_button = $this->workflow->genButton($tmp_state_detail);
//            $data_form->str_buttun=$str_button;
//         }
        # add by kik for show data follow config (20140709)
        $parameter['statusprice'] = $conf_price_per_unit;
        $parameter['conf_inv'] = $conf_inv;
        $parameter['conf_cont'] = $conf_cont;
        $parameter['conf_pallet'] = $conf_pallet;


        // Add By Akkarapol, 11/03/2014, เน€เธเธดเนเธกเธเธฒเธฃ query เธเนเธฒ sub status เธเธฒเธ db เน€เธเธทเนเธญเนเธซเนเธเนเธฒเธเธญเธ dom_code เธ—เธตเนเนเธ”เนเธญเธญเธเธกเธฒเธเธฑเนเธ เธ–เธนเธเธ•เนเธญเธเธ—เธฑเนเธเธซเธกเธ” เนเธฅเธฐเนเธกเนเธ•เนเธญเธเนเธเธ•เธฒเธกเนเธเนเนเธเนเธเนเธ”เธญเธตเธ
        $where['Dom_Host_Code'] = 'SUB_STATUS';
        $sub_status_no_specefied = $this->sys->getDomCodeByDomENDesc('No Specified', $where)->row_array();
        $parameter['sub_status_no_specefied'] = $sub_status_no_specefied['Dom_Code'];
        $sub_status_return = $this->sys->getDomCodeByDomENDesc('Return', $where)->row_array();
        $parameter['sub_status_return'] = $sub_status_return['Dom_Code'];
        $sub_status_repackage = $this->sys->getDomCodeByDomENDesc('Repackage', $where)->row_array();
        $parameter['sub_status_repackage'] = $sub_status_repackage['Dom_Code'];
        // END Add By Akkarapol, 11/03/2014, เน€เธเธดเนเธกเธเธฒเธฃ query เธเนเธฒ sub status เธเธฒเธ db เน€เธเธทเนเธญเนเธซเนเธเนเธฒเธเธญเธ dom_code เธ—เธตเนเนเธ”เนเธญเธญเธเธกเธฒเธเธฑเนเธ เธ–เธนเธเธ•เนเธญเธเธ—เธฑเนเธเธซเธกเธ” เนเธฅเธฐเนเธกเนเธ•เนเธญเธเนเธเธ•เธฒเธกเนเธเนเนเธเนเธเนเธ”เธญเธตเธ

        $container_list = $this->container->get_container($order_id)->result();

        $_container_list = array();

        foreach ($container_list as $idx => $value) {
            $_container_list[$idx]['id'] = $value->Cont_Id;
            $_container_list[$idx]['name'] = tis620_to_utf8($value->Cont_No);
            $_container_list[$idx]['size'] = $value->Cont_Size_Id;
        }
        $arrayobject = new ArrayObject($_container_list);
        $parameter['container_list'] = json_encode($arrayobject);
        //ADD BY POR 2014-07-10 SET CONFIG
        $parameter['conf_inv'] = $conf_inv;
        $parameter['conf_cont'] = $conf_cont;
        //END ADD
        # Check Permision Change Receive Date
        $permission_change_receive_date = FALSE;
        $NavigationUri = array('flow/flowReceiveList');
        $check_user_permission = $this->auth->get_ADM_M_User_Permission($this->session->userdata("user_id"), NULL, NULL, NULL, $NavigationUri)->result();
        $check_role_permission = $this->auth->get_ADM_M_Role_Permission($this->session->userdata("user_id"), NULL, NULL, NULL, $NavigationUri)->result();
        $check_group_permission = $this->auth->get_ADM_M_Group_Permission($this->session->userdata("user_id"), NULL, NULL, NULL, $NavigationUri)->result();

        if (!empty($check_user_permission) || $check_user_permission !== FALSE):
            foreach ($check_user_permission as $value):
                if ($value->Action_Id == "-6"):
                    $permission_change_receive_date = TRUE;
                endif;
            endforeach;
        endif;

        if ($check_role_permission !== FALSE):
            if (!empty($check_role_permission)):
                foreach ($check_role_permission as $value):
                    if ($value->Action_Id == "-6"):
                        $permission_change_receive_date = TRUE;
                    endif;
                endforeach;
            endif;
        endif;

        if ($check_group_permission !== FALSE):
            if (!empty($check_group_permission)):
                foreach ($check_group_permission as $value):
                    if ($value->Action_Id == "-6"):
                        $permission_change_receive_date = TRUE;
                    endif;
                endforeach;
            endif;
        endif;

        $parameter['can_change_receive_date'] = FALSE;
        if (@$this->settings['can_change_receive_date'] && $permission_change_receive_date):
            $parameter['can_change_receive_date'] = TRUE;
        endif;
        # END Check Permision Change Receive Date
        # LOAD FORM
//        p($data_form->form_name,true);
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);

        // Parser to form Add by Ton! 20130924
        $receive_qty = '';
        $confirm_qty = '';

        $lot = '';
        $serial = '';

        if ($parameter['Sub_Module'] == "confirmAction" || $parameter['Sub_Module'] == "approveAction") {
            $receive_qty = ',null';
            $confirm_qty = ',null';
            $lot = ',null';
            $serial = ',null';
        } else {
            $receive_qty = ", {
                    sSortDataType: 'dom-text',
                    sType: 'numeric',
                    type: 'text',
                    onblur: 'submit',
                    event: 'click',
                    cssclass: 'required number',
                    fnOnCellUpdated: function(sStatus, sValue, settings){   // add fnOnCellUpdated for update total qty : by kik : 25-10-2013
                        calculate_qty();
                    }
                }";

            $confirm_qty = ", {
                    sSortDataType: 'dom-text',
                    sType: 'numeric',
                    type: 'text',
                    onblur: 'submit',
                    event: 'click',
                    cssclass: 'required number',
                    fnOnCellUpdated: function(sStatus, sValue, settings){   // add fnOnCellUpdated for update total qty : by kik : 25-10-2013
                        calculate_qty();
                    }
                }";

            if ($parameter['Sub_Module'] == "updateInfo") {
                $receive_qty = ',null'; //ADD BY POR 2014-01-13 เธเธฃเธ“เธต confirm information เธเธฐเนเธกเนเธชเธฒเธกเธฒเธฃเธ–เนเธเนเนเธ receive qty เนเธฅเธฐ confirm qty เนเธ”เน
                $confirm_qty = ',null'; //ADD BY POR 2014-01-13 เธเธฃเธ“เธต confirm information เธเธฐเนเธกเนเธชเธฒเธกเธฒเธฃเธ–เนเธเนเนเธ receive qty เนเธฅเธฐ confirm qty เนเธ”เน

                $lot = ", {
                    sSortDataType: 'dom-text',
                    type: 'text',
                    onblur: 'submit',
                    event: 'click',
                    loadfirst: true,
                }";

                $serial = " , {
                    sSortDataType: 'dom-text',
                    type: 'text',
                    onblur: 'submit',
                    event: 'click',
                    loadfirst: true,
                }";
            } else {
                $lot = ", {onblur: 'submit'}";
                $serial = ", {onblur: 'submit'}";
            }
        }
        #add code if order is partial for do not edit qty :by kik : 2013-12-02
        if ($chk_is_partial) {
            $receive_qty = ',null';
            $confirm_qty = ',null';
        }


        //ADD BY POR 2014-01-13 เน€เธเธดเนเธกเนเธซเนเธ•เธฃเธงเธเธชเธญเธ config เธงเนเธฒเธ–เนเธฒเธกเธต price_per_unit เน€เธเนเธ TRUE เธเธฐเนเธซเนเธฃเธฐเธเธธเธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธขเธ”เนเธงเธข
        $priceperunit = '';
        $unitofprice = '';
        if ($conf_price_per_unit == TRUE):
            $priceperunit = " ,{
                    sSortDataType: \"dom-text\",
                    sType: \"numeric\",
                    type: 'text',
                    onblur: \"submit\",
                    event: 'click keyup',
                    loadfirst: true,
                    cssclass: \"required number\",
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
        else:
            $priceperunit = ",{
                    onblur: 'submit',
                    event: 'click',
                }";
            $unitofprice = ',null';
        endif;
        //END ADD
        //check show/hide invoice in datatable: ADD BY POR 2014-10-04
        //$init_invoice = ",null";
        $init_invoice = "";
        if ($conf_inv):
            $init_invoice = ",{
                    onblur: 'submit',
                    event: 'click',
                }
                ";
        endif;
        //end check invoice
        //check show/hide container in datatable: ADD BY POR 2014-10-04
        $init_container = ",null";
        if ($conf_cont):
            $init_container = "
                ,{
                    data : master_container_dropdown_list,
                    event: 'click',
                    type: 'select',
                    onblur: 'submit',
                    is_container: true,
                    sUpdateURL: function(value, settings) {
                        var oTable = $('#showProductTable').dataTable();
                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                        oTable.fnUpdate(value, rowIndex, ci_cont_id);
                        return value;
                    }
                }
            ";
        endif;
        //end check container
        #<<in case xml config build_pallet and build_pallet_tally_sheet == true =====> show tally sheet button : ADD BY POR 2015-05-28 (only confirm and approve receive)
        $tally_button = '';
        if ($process_id == 1 && ($present_state == 7 || $present_state == 4) && $conf_pallet && $conf_pallet_tally && $conf_cont && $show_tally):
            $tally_button = '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . _lang("tally_sheet") . '" title="' . _lang("tally_sheet") . '"  ONCLICK="exportFile(\'tally\')">';
        endif;
        #>>end case show tally sheet button
        // p($str_form);exit;
        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'receive_qty' => $receive_qty// Add by Ton! 20130924
            , 'confirm_qty' => $confirm_qty// Add by Ton! 20130924
            , 'lot' => $lot// Add by Ton! 20130924
            , 'serial' => $serial// Add by Ton! 20130924
            , 'priceperunit' => $priceperunit //ADD BY POR 2014-01-13 เนเธซเนเธชเนเธเธ•เธฑเธงเนเธเธฃเน€เธเธตเนเธขเธงเธเธฑเธเธเธฒเธฃเนเธชเธ”เธเธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธข
            , 'unitofprice' => $unitofprice //ADD BY POR 2014-01-13 เนเธซเนเธชเนเธเธ•เธฑเธงเนเธเธฃเน€เธเธตเนเธขเธงเธเธฑเธเธเธฒเธฃเนเธชเธ”เธเธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธข
            , 'init_invoice' => $init_invoice //ADD BY POR 2014-10-04
            , 'init_container' => $init_container //ADD BY POR 2014-10-04
            // Add By Akkarapol, 21/09/2013, เน€เธเธดเนเธก Class toggle เน€เธเนเธฒเนเธ เนเธซเนเธฃเธญเธเธฃเธฑเธเธเธฑเธเธ—เธตเน K.Krip เน€เธเธตเธขเธเนเธเนเธ”เนเธงเน
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_receive"></i>'
            // END Add By Akkarapol, 21/09/2013, เน€เธเธดเนเธก Class toggle เน€เธเนเธฒเนเธ เนเธซเนเธฃเธญเธเธฃเธฑเธเธเธฑเธเธ—เธตเน K.Krip เน€เธเธตเธขเธเนเธเนเธ”เนเธงเน
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '"    ONCLICK="cancel()">'
            , 'button_action' => $data_form->str_buttun . $tally_button
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

        #Retrive Data from Table
        $token = $this->input->post('token');
        $flow_id = $this->input->post('flow_id');
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));
   	//  p($this->input->post());exit();
        if (empty($flow_info)) :
            $json['status'] = "E001";
            $json['error_msg'] = "Document already pass this step.";
        else :
            $process_id = $flow_info['0']->Process_Id;
            $present_state = $flow_info['0']->Present_State;

            // validate token
            $response = validate_token($token, $flow_id, $present_state, $process_id);
	    	// p($response);exit();
            if (!$response) :
                $json['status'] = "E001";
                $json['error_msg'] = "Document already pass this step.";
            else :

                $this->transaction_db->transaction_start(); //ADD BY POR 2014-03-07

                $check_not_err = TRUE;

                $respond = $this->_updateProcess($this->input->post());
                if (!empty($respond['critical'])) :
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not update Process.";
                //$return = array_merge_recursive($return,$respond);
                endif;

                /**
                 * check if for return json and set transaction
                 */
                if ($check_not_err):
                    /**
                     * ================== Auto End Transaction =========================
                     */
                    $this->transaction_db->transaction_end();
                    $this->session->unset_userdata('receive_id');
                    $set_return['message'] = "Save Receive Complete.";
                    $return['success'][] = $set_return;
                    $json['status'] = "save";
                    $json['return_val'] = $return;
                else:
                    /**
                     * ================== Rollback Transaction =========================
                     */
                    $this->transaction_db->transaction_rollback();

                    $array_return['critical'][]['message'] = "Save Receive Incomplete";
                    $json['status'] = "save";
                    $json['return_val'] = array_merge_recursive($array_return, $return);
                endif;

            endif;

        endif;

        echo json_encode($json);
    }

    public function check_last_mfd(){
        $prod_mfd = $this->input->post('prod_mfd');
        $prod_id = $this->input->post('prod_id');
        $this->load->library('validate_data');
        $respond = $this->validate_data->check_last_mfd($prod_id,convertDate($prod_mfd,'eng','iso','-'));
        if(!empty($respond)){
            
                     $date = strtotime($respond['last_mfd']);
                      $respond['last_mfd'] = date("d/m/Y", $date);
        }
    //    p('sss');
    //    p($respond);
    //    p('sss');
        
        echo json_encode($respond); 
    }
   
    public function edit_mfd_to_exp(){
            $datemfd = $this->input->post('data');
            $prodId = $this->input->post('prodid');
            $mfd  = convertDate($datemfd, "eng", "iso", "-");
            $result = $this->p->get_product_rule($prodId);
            $min_aging = $result[0]->Min_Aging;
            $fefo = $result[0]->PutAway_Rule;
            if($fefo == "FEFO" && $min_aging != "" && $mfd != "" ){
                $datemin_aging = round($min_aging);
                $datedate = date("Y-m-d",strtotime("+".$datemin_aging."day",strtotime($mfd))); 
                $diff = round(abs(strtotime("$mfd") - strtotime("$datedate"))/60/60/24);
                // p($diff); 
                 // exit;
                $date = strtotime($datedate);
                $exp = date("d/m/Y", $date);
            }else{
                $exp = "";
            }
      
        echo json_encode($exp);
        return $exp;
        die(json_encode($exp));

   
    }

    public function edit_documentAWB() {
        $params = $this->input->post();
        // p($params); exit;
        foreach ($params['data'] as $key => $value) {
            $fefo =$value[24];
            $datemfd = $value[7];
            $prodId = $value[19];
        }
            $mfd  = convertDate($datemfd, "eng", "iso", "-");
            $result = $this->p->get_product_rule($prodId);
            $min_aging = $result[0]->Min_Aging;
            if($fefo == "FEFO" && $min_aging != "" && $mfd != "" ){
                $datemin_aging = round($min_aging);
                $datedate = date("Y-m-d",strtotime("+".$datemin_aging."day",strtotime($mfd))); 
                $diff = round(abs(strtotime("$mfd") - strtotime("$datedate"))/60/60/24);
               
                $exp = $datedate;
            }else{
                $exp = null;
            }
           
        
// p($datedate);
// p('--------');
// p($diff); 
// exit;
        // $result = $this->model->updatedocumentAWB($order_id,$Doc_Refer_AWB);
        echo json_encode($exp);
        return $exp;
        die(json_encode($exp));
    }
    #DEV-Task #3034 Reject Document
    #เน€เธเธดเนเธก function check_partial เน€เธเธทเนเธญเนเธงเนเธ•เธฃเธงเธเธชเธญเธเธเนเธญเธกเธนเธฅเนเธเธเธฃเธ“เธตเธ—เธตเนเธกเธตเธเธฒเธฃ reject&return เธกเธฒเธเธฒเธ putaway module เน€เธเธทเนเธญเธเนเธญเธเธเธฑเธเนเธกเนเนเธซเนเธชเธฃเนเธฒเธ partial เธเนเธณ เธซเธฒเธ order เธเธตเนเธกเธตเธเธฃเธฐเธงเธฑเธ•เธดเธเธฒเธฃเธ—เธณ partial เนเธเนเธฅเนเธง เนเธกเนเธงเนเธฒเธเธฐเน€เธเนเธ parent partial or child partial
    #by kik : 2013-11029 : by kik : 2013-11-29

    function check_partial($order_id) {

        $is_parent_partial = FALSE;
        $is_child_partial = FALSE;

        #check parent partial
        $column_parent = 'Order_Id';
        $where_parent['Parent_Order_Id'] = $order_id;
        $query_order_parent = $this->stock->getOrderTable($column_parent, $where_parent);

        if (!empty($query_order_parent)) {
            $is_parent_partial = TRUE;
        }

        #check child partial
        $column_child = 'Parent_Order_Id';
        $where_child['Parent_Order_Id IS NOT NULL'] = NULL;
        $where_child['Order_Id'] = $order_id;
        $query_order_child = $this->stock->getOrderTable($column_child, $where_child);

        if (!empty($query_order_child)) {
            $is_child_partial = TRUE;
        }

        if ($is_parent_partial || $is_child_partial) {
//        if($is_parent_partial){
            return TRUE;
        } else {
            return FALSE;
        }
    }

    #by kik : 2013-11029 : by kik : 2013-11-29

    function check_partial_for_crePartial($order_id) {

        $is_parent_partial = FALSE;
//        $is_child_partial = FALSE;
        #check parent partial
        $column_parent = 'Order_Id';
        $where_parent['Parent_Order_Id'] = $order_id;
        $query_order_parent = $this->stock->getOrderTable($column_parent, $where_parent);

        if (!empty($query_order_parent)) {
            $is_parent_partial = TRUE;
        }

//        #check child partial
//        $column_child ='Parent_Order_Id';
//        $where_child['Parent_Order_Id IS NOT NULL'] = NULL;
//        $where_child['Order_Id'] = $order_id;
//        $query_order_child = $this->stock->getOrderTable($column_child,$where_child);
//
//        if(!empty($query_order_child)){
//            $is_child_partial = TRUE;
//        }
//        if($is_parent_partial || $is_child_partial){
        if ($is_parent_partial) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    #by kik : 2013-11029 : by kik : 2013-11-29
    #add condition for check child partial : by kik : 2013-12-11

    function check_order_return($order_id) {

        $is_order_return = FALSE;
        $is_child_partial_no_return = FALSE;

        $query_order = $this->stock->getOrderDetailByOrderId($order_id);
//        p($query_order);

        if (!empty($query_order)) {
            foreach ($query_order as $order_detail):
                if (!empty($order_detail->Inbound_Item_Id)):

                    #Start check for child partial
                    $where['Order_Id'] = $order_detail->Order_Id;
                    $colunm = array('Order_Id', 'Parent_Order_Id');

                    $query_order_parent = $this->stock->getOrderTable($colunm, $where);

                    #check child partial
                    if (!empty($query_order_parent[0]->Parent_Order_Id)) {

                        $query_parent_partial = $this->stock->getOrderDetailByOrderId($query_order_parent[0]->Parent_Order_Id);

                        foreach ($query_parent_partial as $parent_partial):
                            if ($parent_partial->Inbound_Item_Id == $order_detail->Inbound_Item_Id):
                                $is_child_partial_no_return = TRUE;
                            endif;
                        endforeach;

                        #this is child partail, no order return
                        if ($is_child_partial_no_return):
                            $is_order_return = FALSE;

                        #this is order return
                        else:
                            $is_order_return = TRUE;
                        endif;
                        #End check for child partial
                    }else {
                        $is_order_return = TRUE;
                    }

                endif;

            endforeach;
        }

        return $is_order_return;
    }

    function approveAction() {
        $this->transaction_db->transaction_start(); //ADD BY POR 2014-03-07
        $check_not_err = TRUE;
// p('ddd'); 
        #Retrive Data from Table
        $token = $this->input->post('token');
        $flow_id = $this->input->post('flow_id');
        $flow_info = $this->flow->getWorkflowTable("Present_State, Process_Id", array("Flow_Id" => $flow_id));

        if (empty($flow_info)) :
            /**
             * Set Alert Zone (set Error Code, Message, etc.)
             */
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
                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $array_return['critical'][]['message'] = "Document already pass this step.";
                $json['status'] = "save";
                $json['return_val'] = $array_return;
                $check_not_err = false;
            else :

                $respond = $this->_updateProcess($this->input->post());

                if (!empty($respond['critical'])) :
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not update Process.";
                //$return = array_merge_recursive($return,$respond);

                endif;

                if ($check_not_err):
                    $this->load->library('Stock_lib');
                    #DEV-Task #3034 Reject Document
                    #by kik : 2013-11029 : by kik : 2013-11-29
                    #start add code for #3034 Reject Document

                    $result_is_partial = $this->check_partial_for_crePartial($this->input->post('order_id'));
                    if (!$result_is_partial):
                        $respond = $this->stock_lib->updatePartialReceive($this->input->post('order_id'));
                        if (!$respond):
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not update Process.";
//							$return = array_merge_recursive($return,$respond);
                        endif;
                    endif;

                    $result_is_orderReturn = $this->check_order_return($this->input->post('order_id'));

                    if (!$result_is_orderReturn):
                        $respond = $this->stock_lib->updateStockReceiveOrder($this->input->post('order_id'));
                        if (!$respond): //ADD BY POR 2014-03-10
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not update Stock.";
//							$return = array_merge_recursive($return,$respond);
                        endif;
                    else:
                        $respond = $this->stock_lib->updateinboundReceiveOrder($this->input->post('order_id'));
                        if (!$respond): //ADD BY POR 2014-03-10
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not update Inbound.";
                        //$return = array_merge_recursive($return,$respond);
                        endif;
                    endif;
                #end add code for #3034 Reject Document : by kik : 2013-11-29
                // Add check config about allow print after approve
                // If true return X001, If not return C003
                // Ball
                endif;

                if ($check_not_err):
                    $this->transaction_db->transaction_end();

                    // CREATE HXORD CSV FILE
                    //$this->createORDFile( $this->input->post() );
                    // END HXORD CSV FILE

                    if ($this->config->item('approve_print_receive')) :
                        $set_return['document_no'] = $this->input->post('document_no');
                        $json['status'] = "X002";
                    else:
                        $json['status'] = "save";
                    endif;

                    $set_return['message'] = "Approve Receive Complete.";
                    $return['success'][] = $set_return;
                    $json['return_val'] = $return;
                else:
                    $this->transaction_db->transaction_rollback();

                    $set_return['critical'][]['message'] = "Approve Receive Incomplete.";
                    $json['status'] = "save";
                    $json['return_val'] = array_merge_recursive($set_return, $return);
                endif;

            endif;

        endif;

        echo json_encode($json);
    }

    /**
     *
     * Function Convert Date Format
     *
     */
    public function convertDate($date) {
        $ex = explode("/", $date);
        return $ex['2'] . "-" . $ex['1'] . "-" . $ex['0'] . " 00:00:00";
    }

    function _updateProcess() {
// p($this->input->post()); exit;
        #Load config
        $conf = $this->config->item('_xml'); // By ball : 20140707
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

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
//        $owner_id = $this->input->post("owner_id");
	$owner_id = $this->input->post("consignee_id");
        $renter_id = $this->input->post("renter_id");
        $shipper_id = $this->input->post("shipper_id");
        $consignee_id = $this->input->post("consignee_id");
        $est_receive_date = $this->input->post("est_receive_date");
        $receive_type = $this->input->post("receive_type");
        $is_pending = $this->input->post("is_pending");
        $is_repackage = $this->input->post("is_repackage");
        $is_urgent = $this->input->post("is_urgent");
        $remark = $this->input->post("remark");

        $vendor_id = $this->input->post("vendor_id");
        $driver_name = $this->input->post("driver_name");
        $car_no = $this->input->post("car_no");
        $receive_date = $this->input->post("receive_date");
        if (empty($receive_date)):
            $receive_date = date("d/m/Y");
        endif;
        $receive_date = $this->convertDate($receive_date);
        $receive_date = substr($receive_date, 0, 10);

        # Parameter Order Detail
        $prod_list = $this->input->post("prod_list");
        //p($prod_list); exit();
        $prod_del_list = $this->input->post("prod_del_list");

        # Parameter Index Datatable
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_lot = $this->input->post("ci_lot");
        $ci_serial = $this->input->post("ci_serial");
        $ci_mfd = $this->input->post("ci_mfd");
        $ci_exp = $this->input->post("ci_exp");
        $ci_invoice = $this->input->post("ci_invoice");
        $ci_container = $this->input->post("ci_container");
        $ci_cont_id = $this->input->post("ci_cont_id");
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_remark = $this->input->post("ci_remark");
        $ci_prod_id = $this->input->post("ci_prod_id");
        $ci_prod_status = $this->input->post("ci_prod_status");
        $ci_unit_id = $this->input->post("ci_unit_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");
        $ci_confirm_qty = $this->input->post("ci_confirm_qty");
        //ADD BY POR 2014-01-10 เน€เธเธดเนเธกเน€เธเธตเนเธขเธงเธเธฑเธเธฃเธฒเธเธฒ
        if ($conf_price_per_unit == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }
        //END ADD

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

        //add Urgent for ISSUE 3312 : by kik : 20140120
        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;

        $data['Document_No'] = $document_no;

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
                , 'Doc_Refer_AWB' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ext))
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
                , 'Is_urgent' => $is_urgent
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Modified_By' => $user_id
                , 'Modified_Date' => date("Y-m-d H:i:s")
                , 'Vendor_Id' => $vendor_id
                , 'Vendor_Driver_Name' => iconv("UTF-8", "TIS-620", $driver_name)
                , 'Vendor_Car_No' => iconv("UTF-8", "TIS-620", $car_no)
//                , 'Actual_Action_Date' => $receive_date //comment by kik : 20140512
//                , 'Actual_Action_Date' => date("Y-m-d H:i:s") //add by kik : 20140512
                , 'Actual_Action_Date' => date("$receive_date H:i:s") //edit by por add time 2014-10-09
                , 'Real_Action_Date' => date("Y-m-d H:i:s")//edit real dispatch date for ISSUE 5265 : kik : 20141020
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
//            p($prod_list);
            if (count($prod_list) > 0) {
                foreach ($prod_list as $rows) {
                    $a_data = explode(SEPARATOR, $rows);
                    $is_new = $a_data[$ci_item_id]; // Edit By  Akkarapol, 30/08/2013, เน€เธญเธฒ @ เธญเธญเธ เน€เธเธฃเธฒเธฐเนเธเนเธเนเธ—เธตเนเธ•เนเธเธ•เธญเธเธถเนเธ index เธกเธฑเธเนเธกเนเธ•เธฃเธเนเธ”เนเนเธฅเนเธง เนเธเธซเธเนเธฒ form/receive.php
                    $detail = array();

                    $detail['Activity_Code'] = 'RECEIVE';      // Add By Akkarapol, 11/09/2013, เน€เธเธดเนเธกเนเธชเนเธเนเธฒเน€เธเนเธฒเนเธเนเธ เธเธฒเธเธเนเธญเธกเธนเธฅ เธเธฐเนเธ”เนเน€เธฃเธตเธขเธเน€เธญเธฒเธกเธฒเนเธเนเธ—เธตเธซเธฅเธฑเธเนเธ”เน เน€เธเธทเนเธญเธเธเธฒเธ Activity_Code เน€เธเธตเนเธขเธงเน€เธเธทเนเธญเธเธเธฑเธ Process เธซเธฅเธฒเธขเนเธ•เธฑเธงเธ”เนเธงเธขเธเธฑเธ
                    $detail['Activity_By'] = $this->session->userdata("user_id");      // Add By Akkarapol, 11/09/2013, เน€เธเธดเนเธกเนเธชเนเธเนเธฒเน€เธเนเธฒเนเธเนเธ เธเธฐเนเธ”เนเธฃเธนเนเธงเนเธฒเนเธเธฃเน€เธเนเธเธเธเธ—เธณ Activity เธเธตเน

                    $detail['Product_Id'] = $a_data[$ci_prod_id];
                    $detail['Product_Code'] = $a_data[$ci_prod_code];
                    $detail['Product_Status'] = $a_data[$ci_prod_status];
                    $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                    
                    
                    ////////////////////////
//                        $process_id = $this->input->post("process_id");
//                        $present_state = $this->input->post("present_state");
//                        $next_state = $this->input->post("next_state");
//                    p($process_id);
//                    p($present_state);
//                    p($next_state);
//                    exit();
                    if($process_id == 1 && $present_state == 4  && $next_state == 5){
                        $this->load->model("putawaymodel", "pa");
                        $r_PA = $this->pa->getPaProduct_Code($order_id, $detail['Product_Code']);
                            if (count($r_PA) > 0) :
                                foreach ($r_PA as $PAList) :
                                    $prodCate = $PAList->Dom_Id;
                                    $prodSubStatus = $PAList->Product_Sub_Status;
                                    $prodQty = $PAList->Reserv_Qty;
                                    $inb_id=$PAList->Inbound_Item_Id; //ADD BY POR 2014-05-27 ดึงค่า inbound เพื่อส่งค่าเข้าไป suggest ด้วย
                                    $product_lot = $PAList->Product_Lot;
                                    $product_serial = $PAList->Product_Serial;
                                    $remark = $PAList->Remark;
                                    $pallet_data = $PAList->Pallet_Id;
                                endforeach;
                            endif;
                        $temp_suggest  = $this->suggest_location->getSuggestLocationArray('suggestLocation',  $detail['Product_Code'], $detail['Product_Status'], 1, $prodCate, $detail['Product_Sub_Status'], str_replace(",", "", $a_data[$ci_confirm_qty]), null, 1,$pallet_data); 
//                        $temp_suggest  = $this->suggest_location->getSuggestLocationArray('suggestLocation',  $detail['Product_Code'], $detail['Product_Status'], 1, $prodCate, $detail['Product_Sub_Status'], str_replace(",", "", $a_data[$ci_confirm_qty]), null, 1); 
                        if(!empty($temp_suggest)){
                            $detail['Suggest_Location_Id'] = $temp_suggest[0]['Location_Id']; /// first index only
                        }
                    }
                    ////////////////////////
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

                    //$detail['Reserv_Qty'] = $a_data[$ci_reserv_qty]; //-----COMMENT BY POR 2013-11-28 เนเธเนเนเธเนเธ”เธขเนเธซเนเธฃเธญเธเธฃเธฑเธ qty เนเธเธ float
                    $detail['Reserv_Qty'] = str_replace(",", "", $a_data[$ci_reserv_qty]); //+++++ADD BY POR 2013-11-28 เนเธเธฅเธ qty เนเธซเนเธญเธขเธนเนเนเธเธฃเธนเธเนเธเธ float เนเธ”เธขเธ•เธฑเธ” comma เธญเธญเธ
                    //$detail['Confirm_Qty'] = $a_data[$ci_confirm_qty]; //-----COMMENT BY POR 2013-11-28 เนเธเนเนเธเนเธ”เธขเนเธซเนเธฃเธญเธเธฃเธฑเธ qty เนเธเธ float
                    $detail['Confirm_Qty'] = str_replace(",", "", $a_data[$ci_confirm_qty]); //+++++ADD BY POR 2013-11-28 เนเธเธฅเธ qty เนเธซเนเธญเธขเธนเนเนเธเธฃเธนเธเนเธเธ float เนเธ”เธขเธ•เธฑเธ” comma เธญเธญเธ
                    $detail['Unit_Id'] = $a_data[$ci_unit_id];
                    //ADD BY POR 2014-01-09 เน€เธเธดเนเธกเน€เธเธตเนเธขเธงเธเธฑเธเธเธฑเธเธ—เธถเธเธฃเธฒเธเธฒเธ”เนเธงเธข
                    if ($conf_price_per_unit == TRUE) {
                        $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]); //+++++ADD BY POR 2014-01-10 เนเธเธฅเธ qty เนเธซเนเธญเธขเธนเนเนเธเธฃเธนเธเนเธเธ float เนเธ”เธขเธ•เธฑเธ” comma เธญเธญเธ
                        $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                        $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]); //+++++ADD BY POR 2014-01-10 เนเธเธฅเธ qty เนเธซเนเธญเธขเธนเนเนเธเธฃเธนเธเนเธเธ float เนเธ”เธขเธ•เธฑเธ” comma เธญเธญเธ
                    }
                    //END ADD
//                    $detail['Product_Lot'] = $a_data[$ci_lot];
//                    $detail['Product_Serial'] = $a_data[$ci_serial];
                    $detail['Product_Lot'] = iconv("UTF-8", "TIS-620", strtoupper($a_data[$ci_lot]));
                    $detail['Product_Serial'] = iconv("UTF-8", "TIS-620", strtoupper($a_data[$ci_serial]));
                    $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);


                    if ($a_data[$ci_mfd] != "") {
                        $detail['Product_Mfd'] = convertDate($a_data[$ci_mfd], "eng", "iso", "-");
                    } else {
                        $detail['Product_Mfd'] = null;
                    }

                   
                    /**
                     * check productstatus empty
                     */

                    if (empty($detail['Product_Status'] ) || empty($detail['Product_Sub_Status'])){
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Product status & Product sub status is empty.";
                    }

                     /**
                     * end check productstatus empty
                     */
      



                    // if ($a_data[$ci_exp] != "") {
                    //     $detail['Product_Exp'] = convertDate($a_data[$ci_exp], "eng", "iso", "-");
                    // } else {
                    //      $detail['Product_Exp'] = null;
                     
                    // }
                    if ($a_data[$ci_exp] != "") {
                        
                        $detail['Product_Exp'] = convertDate($a_data[$ci_exp], "eng", "iso", "-");

                    } else {
                          $prodId =  $detail['Product_Id'];
                           $result = $this->p->get_product_rule($prodId);
                           $rule = $result[0]->PutAway_Rule; 
                           $min_aging = $result[0]->Min_Aging;
                           $mfd =  $detail['Product_Mfd'];
                        if($rule == "FEFO" && $min_aging !="" && $mfd != "" ){
                            $datemin_aging = round($min_aging);
                            $datedate = date("Y-m-d", strtotime("+$datemin_aging day", strtotime($mfd))); 
                            $diff = round(abs(strtotime("$mfd") - strtotime("$datedate"))/60/60/24);
                            // p($diff); exit;
                            $detail['Product_Exp'] = $datedate; 
                        }else{
                            $detail['Product_Exp'] = null;
                        }
                    }

                    if ($is_pending == ACTIVE) {
                        $detail['Product_Status'] = $pending_status_code;
                    }

                    if ("new" != $is_new) {
                        unset($where);
                        $where['Item_Id'] = $is_new;
                        $where['Order_Id'] = $order_id;
                        $where['Product_Code'] = $detail['Product_Code'];
                        $statusupdate = $this->stock->updateOrderDetail($detail, $where);
                        if (!$statusupdate):
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not Update STK_T_Order_Detail.";
                            break;
                        endif;
                    } else {
                        $detail['Order_Id'] = $order_id;
                        //$detail['Confirm_Qty']	= 0;
                        $order_detail[] = $detail;
                    }
                }

                if ($check_not_err):
                    if (!empty($order_detail)) {
                        $afftectedRows = $this->stock->addOrderDetail($order_detail);
                        if (empty($afftectedRows) || $afftectedRows <= 0): //เธ–เนเธฒเธกเธฒเธเธเธงเนเธฒเธ–เธทเธญเธงเนเธฒเธกเธตเธเธฒเธฃ insert เธเนเธญเธกเธนเธฅ
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not insert STK_T_Order_Detail.";
                        endif;
                    }
                endif;
            }
        endif;

       




        /**
         * remove Order Detail
         */
        if ($check_not_err):
            if (is_array($prod_del_list) && (count($prod_del_list) > 0)) {
                unset($rows);
                unset($detail);
                $item_delete = array();
                foreach ($prod_del_list as $rows) {
                    $a_data = explode(SEPARATOR, $rows);
                    $item_delete[] = $a_data[$ci_item_id];  /* Item_Id for Delete in STK_T_Order_Detail  */
                }
                $statusremove = $this->stock->removeOrderDetail($item_delete);
                if (!$statusremove):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not remove Order Detail.";
                endif;
            }
        endif;

        //logOrderDetail($order_id, 'receive', $action_id, $action_type);   //COMMENT BY POR 2014-07-07
        return $return;
    }

    // Add By Akkarapol, 18/09/2013, เน€เธเธดเนเธกเธเธฑเธเธเนเธเธฑเนเธ เธชเธณเธซเธฃเธฑเธ เธ•เธฃเธงเธเธชเธญเธเธงเนเธฒ Product MFD. เธ—เธตเนเน€เธฅเธทเธญเธเธกเธฒเธเธฑเนเธ เธกเธต ShelfLife เธ•เธฒเธกเธ—เธตเนเน€เธเนเธ•เธเนเธฒเนเธ Master เธซเธฃเธทเธญเนเธกเน
    function chkMFDDateOfProduct() {
        $chk_prod_code = $this->input->post("chk_prod_code");
        $chk_mfd = $this->input->post("chk_mfd");
        $this->load->model("location_model", "sys");
        $result = $this->p->getProductDetailByProductCode($chk_prod_code);
        $result = $result->row_array();
        $spl_date_mfd = preg_split('/\//', $chk_mfd); // Edit By Akkarapol, 16/09/2013, เน€เธเธฅเธตเนเธขเธเธเธฒเธเธเธฒเธฃเน€เธฃเธตเธขเธเนเธเน เธเธฑเธเธเนเธเธฑเนเธ split เน€เธเนเธ preg_split เน€เธเธทเนเธญเธเธเธฒเธ PHP V 5.3.0+ เนเธกเนเธฃเธญเธเธฃเธฑเธ เธเธฑเธเธเนเธเธฑเนเธ split เนเธฅเนเธง
        $mktime = (mktime(0, 0, 0) - mktime(0, 0, 0, $spl_date_mfd[1], $spl_date_mfd[0], $spl_date_mfd[2])) / ( 60 * 60 * 24 );

        if ($mktime >= $result['Min_ShelfLife']):
            echo 'Ok';
        else:
            echo 'Min ShelfLife of "' . $result['Product_NameEN'] . '" = ' . (int) $result['Min_ShelfLife'] . ' but you choose day = ' . $mktime;
        endif;
    }

    // END Add By Akkarapol, 18/09/2013, เน€เธเธดเนเธกเธเธฑเธเธเนเธเธฑเนเธ เธชเธณเธซเธฃเธฑเธ เธ•เธฃเธงเธเธชเธญเธเธงเนเธฒ Product MFD. เธ—เธตเนเน€เธฅเธทเธญเธเธกเธฒเธเธฑเนเธ เธกเธต ShelfLife เธ•เธฒเธกเธ—เธตเนเน€เธเนเธ•เธเนเธฒเนเธ Master เธซเธฃเธทเธญเนเธกเน
    // Add By Akkarapol, 18/09/2013, เน€เธเธดเนเธกเธเธฑเธเธเนเธเธฑเนเธ เธชเธณเธซเธฃเธฑเธ เธ•เธฃเธงเธเธชเธญเธเธงเนเธฒ Product Exp. เธ—เธตเนเน€เธฅเธทเธญเธเธกเธฒเธเธฑเนเธ เธกเธต Aging เธ•เธฒเธกเธ—เธตเนเน€เธเนเธ•เธเนเธฒเนเธ Master เธซเธฃเธทเธญเนเธกเน
    function chkExpDateOfProduct() {
        $chk_prod_code = $this->input->post("chk_prod_code");
        $chk_exp = $this->input->post("chk_exp");
        $this->load->model("location_model", "sys");
        $result = $this->p->getProductDetailByProductCode($chk_prod_code);
        $result = $result->row_array();
        $spl_date_exp = preg_split('/\//', $chk_exp); // Edit By Akkarapol, 16/09/2013, เน€เธเธฅเธตเนเธขเธเธเธฒเธเธเธฒเธฃเน€เธฃเธตเธขเธเนเธเน เธเธฑเธเธเนเธเธฑเนเธ split เน€เธเนเธ preg_split เน€เธเธทเนเธญเธเธเธฒเธ PHP V 5.3.0+ เนเธกเนเธฃเธญเธเธฃเธฑเธ เธเธฑเธเธเนเธเธฑเนเธ split เนเธฅเนเธง
        $mktime = (mktime(0, 0, 0, $spl_date_exp[1], $spl_date_exp[0], $spl_date_exp[2]) - mktime(0, 0, 0)) / ( 60 * 60 * 24 );

        if ($mktime >= $result['Min_Aging']):
            echo 'Ok';
        else:
            echo 'Min Aging of "' . $result['Product_NameEN'] . '" = ' . (int) $result['Min_Aging'] . ' but you choose day = ' . $mktime;
        endif;
    }

    // END Add By Akkarapol, 18/09/2013, เน€เธเธดเนเธกเธเธฑเธเธเนเธเธฑเนเธ เธชเธณเธซเธฃเธฑเธ เธ•เธฃเธงเธเธชเธญเธเธงเนเธฒ Product Exp. เธ—เธตเนเน€เธฅเธทเธญเธเธกเธฒเธเธฑเนเธ เธกเธต Aging เธ•เธฒเธกเธ—เธตเนเน€เธเนเธ•เธเนเธฒเนเธ Master เธซเธฃเธทเธญเนเธกเน

    /**
     * export after approve to PDF
     * @author Ball
     */
    public function export_to_pdf() {

        #Load config (by kik : 20140708)
        $conf = $this->config->item('_xml');
        $conf_show_column = empty($conf['show_column_report']['object']['receiving_pdf']) ? array() : @$conf['show_column_report']['object']['receiving_pdf'];

        $column_result = colspan_report($conf_show_column, $conf);
        $view['all_column'] = $column_result['all_column']; //column ทั้งหมดที่แสดง
        $view['colspan'] = $column_result['colspan_all']; //ส่ง colspan ทั้งหมดไป
        $view['show_hide'] = json_decode($column_result['show_hide']);
        $view['set_css_for_show_column'] = $column_result['set_css_for_show_column'];

        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('mpdf/mpdf');

        $search['Document_No'] = $this->input->get('document_no');

        //+++++ADD BY POR 2013-11-20 เนเธซเนเนเธชเธ”เธ receiving report
        $report = $this->r->search_receive_pdf($search);

        $view['datas'] = $report;
        foreach ($report as $value):
            $receive_date = $value[0]['Receive_Date'];
            break;
        endforeach;


        $view['from_date'] = $receive_date; //เธงเธฑเธเธ—เธตเนเธฃเธฑเธ
        $view['to_date'] = $receive_date; //เธงเธฑเธเธ—เธตเนเธฃเธฑเธ
        //เน€เธฃเธตเธขเธเธเธทเนเธญเธเธญเธเธเธเธ—เธตเนเธญเธญเธเธฃเธฒเธขเธเธฒเธ
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        $view['printBy'] = $printBy;
        $view['showfooter'] = 'show'; //ADD BY POR 2013-12-18 เน€เธเธดเนเธกเน€เธเธทเนเธญเธเธญเธเธงเนเธฒเนเธซเนเนเธชเธ”เธเธชเนเธงเธเธ—เนเธฒเธขเธ”เนเธงเธข (เธ•เธญเธเน€เธเนเธ)

        $this->load->view("report/exportReceive", $view);
        //END ADD
    }

    #ISSUE 3034 Reject Document
    #DATE:2013-11-12
    #BY:KIK
    #เน€เธเธดเนเธกเนเธเธชเนเธงเธเธเธญเธ reject and (reject and return)
    #START New Comment Code #ISSUE 3034 Reject Document

    function rejectAction() {
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


#------------------------------------------------------------
        /**
         * ================== Start Transaction =========================
         */
        $this->transaction_db->transaction_start();


        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
        if ($check_not_err):
            if (empty($action_id) || $action_id == '') :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update Workflow.";
            endif;

            #update inbound active N when reject document
            if ($check_not_err) :
                // update inbound when have inbound
                $result_order_details = $this->stock->getOrderDetailByOrderId($order_id);

                $inbound_arr = array();
                if (!empty($result_order_details)):
                    foreach ($result_order_details as $result_order_detail):
                        if ($result_order_detail->Inbound_Item_Id != "" && $result_order_detail->Inbound_Item_Id != NULL):
                            array_push($inbound_arr, $result_order_detail->Inbound_Item_Id);
                        endif;
                    endforeach;
                endif;

                if (!empty($inbound_arr)):
                    foreach ($inbound_arr as $inbound_id):
                        $setDataWhere['Inbound_Id'] = $inbound_id;
                        $setDataForUpdateInboundDetail['Active'] = 'N';
                        $result_upd_inbound = $this->stock->updateInboundDetail($setDataForUpdateInboundDetail, $setDataWhere);
                        if (!$result_upd_inbound) {
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not update Inbound table";
                        }
                    endforeach;
                endif;

            endif;

            if ($check_not_err):
                //update order detail = N
                $detail['Active'] = 'N';
                $where['Order_Id'] = $order_id;

                $result = $this->stock->updateOrderDetail($detail, $where);
                if (!$result) {
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not update Order detail.";
                }
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

            #if data send from list page When refesh page  : add by kik : 2013-12-02
            if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") :
                echo "<script>alert('Delete Pre-Receive complete.');</script>";
                redirect('flow/flowPreReceiveList', 'refresh');
            else :
                $set_return['message'] = "Reject Receive Complete.";
                $return['success'][] = $set_return;
                $json['status'] = "save";
                $json['return_val'] = $return;
            endif;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") :
                P("Deleting data company not complete. Please check?");
                redirect('flow/flowPreReceiveList', 'refresh');

            #if data send from form page When return data to form page   : add by kik : 2013-12-02
            else :
                $set_return['critical'][]['message'] = "Reject Receive Incomplete.";
                $return = array_merge_recursive($set_return, $return);
                $json['status'] = "save";
                $json['return_val'] = $return;
            endif;
        endif;

        echo json_encode($json);
    }

    function rejectAndReturnAction() {

        #1. state 7 (wait for Manage Product Infomation)  >> state 3 (wait for Confirm Receive Product)
        #1.1 update Present_State in Workflow table = state 3
        #1.2 update Confirm_Qty in order detail table = NULL
        #2. state 4 (wait for Approve Receive Product) >> 7 (wait for Confirm Receive Product)
        #2.1 update Present_State in Workflow table = state 7

        $process_id = $this->input->post("process_id");
        $flow_id = $this->input->post("flow_id");
        $present_state = $this->input->post("present_state");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $document_no = $this->input->post("document_no");
        $order_id = $this->input->post("order_id");

        $data['Document_No'] = $document_no;

        $check_not_err = TRUE;
        $return = array();

        $this->transaction_db->transaction_start();

        if ($check_not_err):
            //update order detail = N
            if ($next_state == 2 || $next_state == 3) {
                $result_order_details = $this->stock->getOrderDetailByOrderId($order_id);ผผ
                $pallet_arr = array();
                if (!empty($result_order_details)):
                    foreach ($result_order_details as $result_order_detail):
                        if ($result_order_detail->Pallet_Id != "" && $result_order_detail->Pallet_Id != NULL):
                            array_push($pallet_arr, $result_order_detail->Pallet_Id);
                        endif;
                    endforeach;
                endif;

                #update pallet
                if (!empty($pallet_arr)) {

                    if (!empty($result_order_details)) {

                        foreach ($pallet_arr as $pallet_id):
                            $setDataWherePallet['Pallet_Id'] = $pallet_id;
                            $setDataForUpdatePallet['Active'] = 'N';
                            $result_upd_pallet = $this->pallet->update_pallet_colunm($setDataForUpdatePallet, $setDataWherePallet);
                            if (!$result_upd_pallet) {
                                $check_not_err = FALSE;
                                $return['critical'][]['message'] = "Can not update Pallet table";
                            }

                            #update pallet detail
                            if ($check_not_err) {
                                $setDataWherePalletDetail['Pallet_Id'] = $pallet_id;
                                $setDataForUpdatePalletDetail['Active'] = 0;
                                $result_upd_pallet_detail = $this->pallet->update_pallet_detail_colunm($setDataForUpdatePalletDetail, $setDataWherePalletDetail);
                                if (!$result_upd_pallet_detail) {
                                    $check_not_err = FALSE;
                                    $return['critical'][]['message'] = "Can not update Pallet Detail table";
                                }
                            }

                            #Clear Order Detail
                            if ($check_not_err) {
                                $setDataWhereOrderDetail['Pallet_Id'] = $pallet_id;
                                $setDataForUpdateOrderDetail['Pallet_Id'] = NULL;
                                $result_upd_order_detail = $this->pallet->update_order_detail_colunm($setDataForUpdateOrderDetail, $setDataWhereOrderDetail);
                                if (!$result_upd_order_detail) {
                                    $check_not_err = FALSE;
                                    $return['critical'][]['message'] = "Can not update Order Detail table";
                                }
                            }

                        endforeach;
                    }
                }


                if ($check_not_err):

                    $detail['Confirm_Qty'] = NULL;
                    $where['Order_Id'] = $order_id;

                    $result = $this->stock->updateOrderDetail($detail, $where);
                    if (!$result):
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Can not update Order Detail.";
                    endif;

                endif;
            } else {

                $result = TRUE;
            }// end check state = 3

        endif; // end update order detail , pallet , pallet_detail

        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
        if ($check_not_err):
            if (empty($action_id) || $action_id == '') :
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update Workflow.";
            endif;
        endif;

        if ($check_not_err):
            $this->transaction_db->transaction_end();
            $set_return['message'] = "Reject and Return Receive Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            $this->transaction_db->transaction_rollback();
            $set_return['critical'][]['message'] = "Reject and Return Receive incomplete";
            $return = array_merge_recursive($set_return, $return);
            $json['status'] = "save";
            $json['return_val'] = $return;
        endif;

        echo json_encode($json);
    }

    #End New Comment Code #ISSUE 3034 Reject Document

    public function validation_updateInfo() {
        $json['status'] = "pass";
        $json['return_val'] = '';
        echo json_encode($json);
    }

    public function validation_approveAction() {
        $json['status'] = "pass";
        $json['return_val'] = '';
        echo json_encode($json);
    }

    public function getContainerDropdownList($order_id = NULL) {
        //GET Container Size
        if (empty($order_id)):
            $order_id = $this->input->post('order_id');
        endif;

        $container_dropdown_list = $this->container->get_container_dropdown_list($order_id)->result();

        $_container_dropdown_list = array();
        if (count($container_dropdown_list) > 0) {
            foreach ($container_dropdown_list as $value) {
                $_container_dropdown_list[$value->Cont_Id] = tis620_to_utf8($value->Cont_No) . " " . $value->Cont_Size_No . $value->Cont_Size_Unit_Code;
            }
        }
        echo json_encode($_container_dropdown_list);
    }

    //ADD BY POR 2014-10-04:
    public function getContainerDropdownListOutbound($order_id = NULL) {
        //GET Container Size
        if (empty($order_id)):
            $order_id = $this->input->post('order_id');
        endif;

        $container_dropdown_list = $this->container->get_container_dropdown_list_outbound($order_id)->result();

        $_container_dropdown_list = array();
        if (count($container_dropdown_list) > 0) {
            foreach ($container_dropdown_list as $value) {
                $_container_dropdown_list[$value->Cont_Id] = tis620_to_utf8($value->Cont_No) . " " . $value->Cont_Size_No . $value->Cont_Size_Unit_Code;
            }
        }
        echo json_encode($_container_dropdown_list);
    }

    //update container in database :BY POR 2014-07-13
    public function updateContainer() {
        $order_id = $this->input->post("order_id");
        $flow_id = $this->input->post("flow_id");
        $type = $this->input->post("type") == "" ? 'INBOUND' : $this->input->post("type");

        $container_list = $this->input->post("container_list");     //ADD BY POR 2014-07-09
        $container_list = json_decode($container_list);
        $result_container_list = $this->container->update_container($order_id, $container_list, $type);

        if ($result_container_list <= 0) :  //case not success
            $json['val'] = 'NO';
            echo json_encode($json);
        else:
            if ($type == "OUTBOUND"):
                $this->getContainerDropdownListOutbound($order_id);
            else:
                $this->getContainerDropdownList($order_id);
            endif;

        //$dropdown_list =
        //$json['val'] = $dropdown_list;
        endif;


        //$json['id'] = $flow_id;
    }

    #<<export Tally Sheet : Add by Por 2015-05-28

    public function exportReceiveTallySheet() {
        $conf = $this->config->item('_xml');
        $config_perpage = !empty($conf['config_perpage_tally']) ? @$conf['config_perpage_tally'] : 23;
        $config_column = empty($conf['config_column_tally']) ? false : @$conf['config_column_tally'];

        //Add by Ken :20150810 => add column QTY/UOM, Unit/Product
        $inventory_report = empty($conf['show_column_report']['object']['inventory_swa_report']) ? false : @$conf['show_column_report']['object']['inventory_swa_report'];
        $conf_uom_qty = ($inventory_report['uom_qty']['value']) ? true : false;
        $conf_uom_unit_prod = ($inventory_report['uom_unit_prod']['value']) ? true : false;
//        p($inventory_report,true);

        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('mpdf/mpdf');

        $order_data = $this->flow->getOrderDetailByDocumentNo($this->input->get('document_no'));
        if (empty($order_data)):
            echo 'Sorry, can not find data.';
            exit();
        endif;
//        p($order_data,true);

        $search['order_id'] = $order_data[0]->Order_Id;
        $search['flow_id'] = $order_data[0]->Flow_Id;
        $flow_detail = $this->flow->getFlowDetail_abnormal_flow($search['flow_id'], 'STK_T_Order');
        $data = $flow_detail[0];

        //detail header
        $tmp = $this->company->getCompanyByID($data->Renter_Id);
        $tmp = $tmp->row_array();
        $data->Renter_Name = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($data->Source_Id);
        $tmp = $tmp->row_array();
        $data->Source_Name = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($data->Vendor_Id);
        $tmp = $tmp->row_array();
        $data->Vendor_Name = $tmp['Company_NameEN'];

        $tmp = $this->company->getCompanyByID($data->Destination_Id);
        $tmp = $tmp->row_array();
        $data->Consignee_Name = $tmp['Company_NameEN'];

        $tmp = $this->sys->getDomDetailByDomCode($data->Doc_Type);
        $tmp = $tmp->row_array();
        $data->Dispatch_Type = $tmp['Dom_EN_Desc'];

        $view['data'] = $data;
        //end detail header
        //find detail for show in pdf
        $report = $this->r->searchTallySheet($search)->result_array();
//        p($report,true);
        //Add by ken:20150810
        $datas = array();
        if ($conf_uom_qty and $conf_uom_unit_prod):
            foreach ($report as $key => $row):
//                echo "<br />Unit_Id :".$row['Unit_Id']."  >>qty:".$row['qty']."  >>Standard_Unit_In_Id:".$row['Standard_Unit_In_Id'] ;
                $row['Uom_Qty'] = $this->r->convert_uom($row['Unit_Id'], $row['qty'], $row['Standard_Unit_In_Id']);
                array_push($datas, $row);
            endforeach;
        else:
            $datas = $report;
        endif;
//        p($datas,true);

        $report_type_pallet = $this->r->searchTallySheet_palletType($search)->result_array();

        $remarkArr = $this->r->getRemark($search['order_id']);  //Add by ken:20150810
//        p($remarkArr);
//        p($order_data);
//        p($report,true);
//        p($report_type_pallet,true);

        $chk_type_pallet = array();
        $chk_pallet_mix = array();
        foreach ($report_type_pallet as $type_pallet) {
            if ($type_pallet['qty_item_in_pallet'] > 1):
                $chk_type_pallet[$type_pallet['Pallet_Code']] = 'mix';
                array_push($chk_pallet_mix, $type_pallet['Pallet_Code']);
            else:
                $chk_type_pallet[$type_pallet['Pallet_Code']] = 'full';
            endif;
        }


        $temp_product_set_pallet_mix = array();
        if (!empty($chk_pallet_mix)):

            $prod_pallet_mix = $this->r->searchProductInPallet($chk_pallet_mix)->result_array();

            if (!empty($prod_pallet_mix)):
                foreach ($prod_pallet_mix as $row):
                    if (key_exists($row['Pallet_Code'], $temp_product_set_pallet_mix)):
                        $temp_product_set_pallet_mix[$row['Pallet_Code']] = $temp_product_set_pallet_mix[$row['Pallet_Code']] . $row['Product_Code'];
                    else:
                        $temp_product_set_pallet_mix[$row['Pallet_Code']] = $row['Product_Code'];
                    endif;
                endforeach;
            else:

            endif;

        endif;

//        p($report,true);
        $temp = array();
        //#New by Ken 20150810
        $report = $datas;
        if (!empty($report)) {
            foreach ($report as $ke => $val) {
                $groupProducts = !empty($val['Product_Id']) ? $val['Product_Id'] : "0";
                $groupProducts .=!empty($val['Product_Lot']) ? "_" . trim($val['Product_Lot']) : "0";
                //Add serial column
                $groupProducts .=!empty($val['Product_Serial']) ? "_" . trim($val['Product_Serial']) : "0";
                $groupProducts .=!empty($val['Unit_Id']) ? "_" . trim($val['Unit_Id']) : "0";
                $groupPallet[$val['Cont_No']][] = $val['Pallet_Code'];

                $temp[$val['Cont_No']]['Cont_Size_Unit'] = $val['Cont_Size_Unit'];
                $temp[$val['Cont_No']]['data'][$groupProducts]['code'] = $val['Product_Code'];
                $temp[$val['Cont_No']]['data'][$groupProducts]['lot_batch'] = $val['Product_Lot'] . "/" . $val['Product_Serial'];
                $temp[$val['Cont_No']]['data'][$groupProducts]['Product_NameEN'] = $val['Product_NameEN'];
                $temp[$val['Cont_No']]['data'][$groupProducts]['unit'] = $val['Unit_Value'];
                $temp[$val['Cont_No']]['data'][$groupProducts]['unit_product'] = $val['Uom_Unit_Val'];

                $temp[$val['Cont_No']]['data'][$groupProducts]['pallet_list'][$val['Pallet_Code']] = $val['qty'];

                //**** Tset build data
//                $pallet_test = array("I15-28614"=>"100"
//                    , "I15-28615"=>"200"
//                    ,"I15-28616"=>"300"
//                    ,"I15-28617"=>"400"
//                    ,"I15-28618"=>"400"
//                    ,"I15-28619"=>"400"
//                    ,"I15-28620"=>"400"
//                    ,"I15-28621"=>"400"
//                    ,"I15-28622"=>"400"
//                    ,"I15-28623"=>"400"
//                    ,"I15-28624"=>"400"
//                    ,"I15-28625"=>"400"
//                );
//                $temp[$val['Cont_No']]['data'][$groupProducts]['pallet_list'] = array_merge($temp[$val['Cont_No']]['data'][$groupProducts]['pallet_list'], $pallet_test);
                //#Sum total QTY
                if (!empty($temp[$val['Cont_No']]['data'][$groupProducts]['total_qty'])) {
                    $temp[$val['Cont_No']]['data'][$groupProducts]['total_qty'] += $val['qty'];
                } else {
                    $temp[$val['Cont_No']]['data'][$groupProducts]['total_qty'] = $val['qty'];
                }

                //#Sum total Uom_Qty
                if (!empty($temp[$val['Cont_No']]['data'][$groupProducts]['total_Uom_Qty'])) {
                    $temp[$val['Cont_No']]['data'][$groupProducts]['total_Uom_Qty'] += $val['Uom_Qty'];
                } else {
                    $temp[$val['Cont_No']]['data'][$groupProducts]['total_Uom_Qty'] = $val['Uom_Qty'];
                }

                //#group remark
                $temp[$val['Cont_No']]['data'][$groupProducts]['remark'] = !empty($remarkArr[$groupProducts]) ? implode(",", $remarkArr[$groupProducts]) : "";
                $temp[$val['Cont_No']]['totalAll_qty'][$groupProducts] = $temp[$val['Cont_No']]['data'][$groupProducts]['total_qty'];
                $temp[$val['Cont_No']]['totalAll_Uom_qty'][$groupProducts] = $temp[$val['Cont_No']]['data'][$groupProducts]['total_Uom_Qty'];
            }


//            p($groupPallet,true);
            //*** Sort groupPallet by Cont_No
            $totalAll_pallet = array();
            if (!empty($groupPallet)) {
                foreach ($groupPallet as $g_conNo => $g_val) {
                    //# Sort key array
                    $totalAll_pallet = array_unique($g_val);
                    sort($totalAll_pallet);
                    $temp[$g_conNo]['totalAll_pallet'] = $totalAll_pallet;
                }
            }
//            p($temp[1]['totalAll_pallet'],true);
//            p($temp,true);
            //# Check Count products in pallet
            //================================
            $palletNum = array();
            if (!empty($temp)) {
                foreach ($temp as $k_con => $data_pallet) {
                    $totalAll_pallet = !empty($temp[$k_con]['totalAll_pallet']) ? $temp[$k_con]['totalAll_pallet'] : "";
                    if (!empty($data_pallet['data'])) {
                        foreach ($data_pallet['data'] as $sub_pallet) {
                            krsort($sub_pallet['pallet_list']);

                            if (!empty($sub_pallet['pallet_list'])) {
                                foreach ($sub_pallet['pallet_list'] as $k_pallet => $sub_val) {
                                    if (array_search($k_pallet, $totalAll_pallet) !== FALSE) {
                                        if (!empty($palletNum[$k_con][$k_pallet])) {
                                            $palletNum[$k_con][$k_pallet] = $palletNum[$k_con][$k_pallet] + 1;
                                        } else {
                                            $palletNum[$k_con][$k_pallet] = 1;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

//            p($palletNum,false);
            //#New sort array by count pallet duplicate
            //=========================================
            if (!empty($palletNum)) {
                foreach ($palletNum as $k => $valSort) {
                    asort($valSort);
                    $palletNum[$k] = ($valSort);
//                    p($valSort);
//                    echo '<br />--------------<br/>';
                }
            }
//            p($palletNum,false);

            $palletNum_Arr = array();
            if (!empty($palletNum)) {
                foreach ($palletNum as $p_conNo => $palletAll) {
                    $p_num = 1;
                    foreach ($palletAll as $p_key => $p_val) {
                        if ($p_val > 1) {
                            $palletNum_Arr[$p_conNo][$p_key]['sort'] = $p_num;
                            $p_num++;
                        } else {
                            $palletNum_Arr[$p_conNo][$p_key]['sort'] = 0;
                        }
                        $palletNum_Arr[$p_conNo][$p_key]['count'] = $p_val;
                    }
                    $temp[$p_conNo]['dup_pallet'] = !empty($palletNum_Arr[$p_conNo]) ? $palletNum_Arr[$p_conNo] : array();
                }
            }

//            p($palletNum ,false);
//            echo "<Br />===========================<br/>";
//            p($palletNum_Arr,true);
//            p($temp[$val['Cont_No']]['data'],true);
        }

        #หา container ทั้งหมด
        $temp_count = array();
        foreach ($report as $idx_count => $val_count) :
            $temp_count[$val_count['Cont_No']] = $val_count['Cont_No'];
        endforeach;
//        p($temp,true);

        $view['line_per_page'] = $config_perpage;
        $view['config_column'] = $config_column;  //config coloumn of tally sheet
        $view['tmp_count'] = count($temp_count);  //count all container
        $view['datas'] = $temp; //detail
        //find receive date for show in pdf
        foreach ($report as $value):
            $receive_date = $value[0]['Receive_Date'];
            break;
        endforeach;

        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;

        $this->load->view("report/exportTallySheet", $view);
    }

    private function createORDFile($params) {

        $this->load->model("stock_model", "stock");
        $upload_path = $this->settings['uploads']['upload_path'];

        $order_id = $params['order_id'];
        $document_no = $params['document_no'];
        $doc_refer_ext = $params['doc_refer_ext'];
        $est_receive_date = $params['est_receive_date'];
        $order_header = $this->stock->getOrderHeaderHensley($order_id);
        $order_detail = $this->stock->getOrderDetail($order_id, false, "STK_T_Order_Detail.Item_Id ASC", NULL, NULL);
        $fp = fopen($upload_path . 'HXORD303' . date('ymd') . '.csv', 'a');

        fputcsv($fp, json_decode($order_header->Optional), ";");

        foreach ($order_detail as $k => $val) {
            fputcsv($fp, array("DTL", $doc_refer_ext, ($k + 1), $val->Product_Code, $val->Product_Serial, $val->Reserv_Qty, $val->Confirm_Qty, $document_no, $est_receive_date), ";");
        }

        fclose($fp);
    }

}
