<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Pre_receive extends CI_Controller {

    public $settings;

    public function __construct() {
        // error_reporting(E_All);
        parent::__construct();
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));

        $this->load->model("product_model", "p");
        $this->load->model("system_management_model", "sys"); //ADD BY POR 2014-02-27
        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", "flow");
        $this->load->model("contact_model", "contact");
        $this->load->model("company_model", "company");
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("invoice_model", "invoice");
        $this->load->model("container_model", "container");

        $isUserLogin = $this->session->userdata("user_id");
        $this->settings = native_session::retrieve();
        if ($isUserLogin == "") {
            echo "<script>alert('Your session was expired. Please Log-in!')</script>";
            echo "<script>window.location.replace('" . site_url() . "');</script>";
        }
    }

    public function index() {
        $this->openForm($process_id = 1);
    }

# Start Process Pre-Receive
# 1. Open Blank Form

    function openForm($process_id, $present_state = 0) {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        #Load config
        $conf = $this->config->item('_xml'); // By ball : 20140707
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];


        $parameter['token'] = "";
//        $parameter['renter_id'] = $this->config->item('renter_id');
//        $parameter['consignee_id'] = $this->config->item('owner_id');
//        $parameter['owner_id'] = $this->config->item('owner_id');
        // Edit By Akkarapol, 25/12/2013, เปลี่ยนการใส่ค่าของ var ต่างๆ จากที่ใช้เป็นการเขียน config ก็เรียกเอาจาก session เลย
        $parameter['renter_id'] = $this->session->userdata('renter_id');
        $parameter['consignee_id'] = $this->session->userdata('branch_id');
        $parameter['owner_id'] = $this->session->userdata('owner_id');
        // END Edit By Akkarapol, 25/12/2013, เปลี่ยนการใส่ค่าของ var ต่างๆ จากที่ใช้เป็นการเขียน config ก็เรียกเอาจาก session เลย
        // Add By Akkarapol, 06/11/2013, เพิ่มการ query ค่า sub status จาก db เพื่อให้ค่าของ dom_code ที่ได้ออกมานั้น ถูกต้องทั้งหมด และไม่ต้องไปตามแก้ในโค๊ดอีก
        $where['Dom_Host_Code'] = 'SUB_STATUS';
        $sub_status_no_specefied = $this->sys->getDomCodeByDomENDesc('No Specified', $where)->row_array();
        $parameter['sub_status_no_specefied'] = $sub_status_no_specefied['Dom_Code'];
        $sub_status_return = $this->sys->getDomCodeByDomENDesc('Return', $where)->row_array();
        $parameter['sub_status_return'] = $sub_status_return['Dom_Code'];
        $sub_status_repackage = $this->sys->getDomCodeByDomENDesc('Repackage', $where)->row_array();
        $parameter['sub_status_repackage'] = $sub_status_repackage['Dom_Code'];
        // END Add By Akkarapol, 06/11/2013, เพิ่มการ query ค่า sub status จาก db เพื่อให้ค่าของ dom_code ที่ได้ออกมานั้น ถูกต้องทั้งหมด และไม่ต้องไปตามแก้ในโค๊ดอีก
        #Get Default Receive Type
        $result = $this->sys->getNormalReceiveType();
        if (count($result) > 0) {
            foreach ($result as $rows) {
                $parameter['receive_type'] = $rows->Dom_Code;
            }
        }

        #Get renter list
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
//        $renter_list = genOptionDropdown($r_renter, "COMPANY");
        $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE); // Edit by kik! 20131107
        $parameter['renter_list'] = $renter_list;

        #Get Shipper[Company Supplier] list
        $q_shipper = $this->company->getSupplierAll();
        $r_shipper = $q_shipper->result();
//        $shipper_list = genOptionDropdown($r_shipper, "COMPANY");
        $shipper_list = genOptionDropdown($r_shipper, "COMPANY", FALSE, FALSE); // Edit by kik! 20131107
        $parameter['shipper_list'] = $shipper_list;

        #Get Consignee[Company Owner]  list
        $q_consignee = $this->company->getOwnerAll();
        $r_consignee = $q_consignee->result();
//        $consignee_list = genOptionDropdown($r_consignee, "COMPANY");
        $consignee_list = genOptionDropdown($r_consignee, "COMPANY", FALSE, FALSE); // Edit by kik! 20131107
        $parameter['consignee_list'] = $consignee_list;


        #Get receive type list
        $r_receive_type = $this->sys->getReceiveType();
//        $receive_list = genOptionDropdown($r_receive_type, "SYS");
        $receive_list = genOptionDropdown($r_receive_type, "SYS", TRUE, TRUE); // Edit by kik! 20131107
        $parameter['receive_list'] = $receive_list;

        #Get workflow list
//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowPreReceiveList"); // Button Permission. Add by Ton! 20140131
        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['process_id'] = $process_id;
        $parameter['present_state'] = $present_state;
        $parameter['data_form'] = (array) $data_form;

        // Add By Akkarapol, 17/09/2013, Set DomCode ที่ไม่ต้องเช็ค product,suplier เพื่อเอาไปใช้กับฟังก์ชั่น ereg
        $getDomCodes = $this->sys->getNoChkSup();
        $noChkSup = '/^';
        foreach ($getDomCodes as $keyDomCode => $domCode):
            $noChkSup = ($keyDomCode == 0 ? $noChkSup : $noChkSup . '|^');
            $noChkSup = $noChkSup . '[' . $domCode->Dom_Code . ']';
        endforeach;
        $noChkSup = $noChkSup . '/';
        $parameter['noChkSup'] = $noChkSup;
        $parameter['statusprice'] = $conf_price_per_unit;

        // Invoice and Container List default
        $parameter['container_list'] = "";
        $parameter['doc_refer_container'] = "";

        //ADD BY POR 2014-07-08 select container size for size list in container
        $container_size = $this->container->getContainerSize()->result();
        $_container_list = array();
        foreach ($container_size as $idx => $value) {
            $_container_list[$idx]['Id'] = $value->Cont_Size_Id;
            $_container_list[$idx]['No'] = $value->Cont_Size_No;
            $_container_list[$idx]['Unit_Code'] = $value->Cont_Size_Unit_Code;
        }
        $parameter['container_size_list'] = json_encode($_container_list);
        //END ADD
        //ADD BY POR 2014-01-13 เพิ่มให้ตรวจสอบ config ว่าถ้ามี price_per_unit เป็น TRUE จะให้ระบุราคาต่อหน่วยด้วย
        $priceperunit = '';
        $unitofprice = '';
        if ($conf_price_per_unit):
            $priceperunit = " ,{
                    sSortDataType: \"dom-text\",
                    sType: \"numeric\",
                    type: 'text',
                    onblur: \"submit\",
                    event: 'click keyup',
                    is_required: true,
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
        // END Add By Akkarapol, 17/09/2013, Set DomCode ที่ไม่ต้องเช็ค product,suplier เพื่อเอาไปใช้กับฟังก์ชั่น ereg
        //ADD BY POR 2014-10-04
        if ($conf_inv):
            $init_invoice = ",{
                sSortDataType: \"dom-text\",
                type: 'text',
                onblur: \"submit\",
                event: 'click focusin',
                loadfirst: true,
                width: '75%',
             }";
            $reinit_invoice = $init_invoice;
        else :
            $init_invoice = ",null";
            $reinit_invoice = "";
        endif;
        //End add


        /**
         * Hide Column Set By XML
         */
        $hide_column = array();
        if (!empty($this->settings['show_column']['pre_receive'])):
            $hide_column = array_keys($this->settings['show_column']['pre_receive'], FALSE);
//            p($hide_column);
        endif;

        $parameter['hide_column'] = json_encode($hide_column);
        //ADD BY POR 2014-07-10 SET CONFIG
        $parameter['conf_inv'] = $conf_inv;
        $parameter['conf_cont'] = $conf_cont;
        $parameter['DeliveryTime'] = '';
        $parameter['DestinationDetail'] = '';
        //END ADD
        # LOAD FORM and PUT FORM IN TEMPLATE WORKFLOW
        // p($data_form->form_name); exit;
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);
// p($data_form->from_state_name); exit;
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'priceperunit' => $priceperunit //ADD BY POR 2014-01-13 ให้ส่งตัวแปรเกี่ยวกับการแสดงราคาต่อหน่วย
            , 'unitofprice' => $unitofprice //ADD BY POR 2014-01-13 ให้ส่งตัวแปรเกี่ยวกับการแสดงราคาต่อหน่วย
            , 'init_invoice' => $init_invoice  //sent inittable for invoice :ADD BY POR 2014-10-04
            , 'reinit_invoice' => $reinit_invoice  //sent reinittable for invoice
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_receive"></i>'
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'button_action' => $data_form->str_buttun
        ));
    }

# 2. Open Form With Data

    function openActionForm() {
        #Load config
        $conf = $this->config->item('_xml'); // By ball : 20140707
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        $flow_id = $this->input->post("id");

        #Retrive Data from Table
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order'); // Edit by Ak add 'STK_T_Order' for get data in this table
        $process_id = $flow_detail['0']->Process_Id;
        $order_id = $flow_detail['0']->Order_Id;
        $present_state = $flow_detail['0']->Present_State;
        $module = $flow_detail['0']->Module;

        // validate document exist state
        $valid_state = validate_state($module);
        if ($valid_state) :
            redirect($valid_state);
        endif;

        // register token
        $parameter['token'] = register_token($flow_id, $present_state, $process_id);
        // end config token

        $present_state = $flow_detail[0]->Present_State;
        $parameter['process_type'] = $flow_detail[0]->Process_Type;
        $parameter['document_no'] = $flow_detail[0]->Document_No;
        $parameter['doc_refer_int'] = $flow_detail[0]->Doc_Refer_Int;
        $parameter['doc_refer_ext'] = $flow_detail[0]->Doc_Refer_Ext;
        $parameter['doc_refer_inv'] = $flow_detail[0]->Doc_Refer_Inv;
        $parameter['doc_refer_ce'] = $flow_detail[0]->Doc_Refer_CE;
        $parameter['doc_refer_bl'] = $flow_detail[0]->Doc_Refer_BL;
        //$parameter['doc_refer_container'] = $flow_detail[0]->Doc_Refer_Container;
        $parameter['owner_id'] = $flow_detail[0]->Owner_Id;
        $parameter['renter_id'] = $flow_detail[0]->Renter_Id;
        $parameter['shipper_id'] = $flow_detail[0]->Source_Id;
        $parameter['consignee_id'] = $flow_detail[0]->Destination_Id;
        $parameter['receive_type'] = $flow_detail[0]->Doc_Type;
        $parameter['est_receive_date'] = $flow_detail[0]->Est_Action_Date;
        $parameter['remark'] = $flow_detail[0]->Remark;
        $parameter['is_pending'] = $flow_detail[0]->Is_Pending;
        $parameter['is_repackage'] = $flow_detail[0]->Is_Repackage;
        $parameter['is_urgent'] = $flow_detail[0]->Is_urgent;

        $parameter['DeliveryTime'] = $flow_detail[0]->Estimate_Action_Time;
        $parameter['DestinationDetail'] = $flow_detail[0]->Destination_Detail;
//        //ADD BY POR 2014-07-07 select container name by order_id
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

        /**
         * GET Order Detail
         */
        $order_by = "STK_T_Order_Detail.Item_Id ASC";
        $order_deatil = $this->stock->getOrderDetail($order_id, FALSE, $order_by, NULL, NULL); //add by kik : for change parameter to function : 20141004
//        $order_deatil = $this->stock->getOrderDetail($order_id, 67); //67 คือบอกว่า step 67 ใครเป็นผู้ทำรายการ receive หน้า HH //comment by kik : 20141004

        $parameter['order_id'] = $order_id;
        $parameter['order_deatil'] = $order_deatil;

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

//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowPreReceiveList"); // Button Permission. Add by Ton! 20140131

        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['flow_id'] = $flow_id;
        $parameter['process_id'] = $process_id;
        $parameter['present_state'] = $present_state;
        $parameter['data_form'] = (array) $data_form;


        // Add By Akkarapol, 06/11/2013, เพิ่มการ query ค่า sub status จาก db เพื่อให้ค่าของ dom_code ที่ได้ออกมานั้น ถูกต้องทั้งหมด และไม่ต้องไปตามแก้ในโค๊ดอีก
        $where['Dom_Host_Code'] = 'SUB_STATUS';
        $sub_status_no_specefied = $this->sys->getDomCodeByDomENDesc('No Specified', $where)->row_array();
        $parameter['sub_status_no_specefied'] = $sub_status_no_specefied['Dom_Code'];
        $sub_status_return = $this->sys->getDomCodeByDomENDesc('Return', $where)->row_array();
        $parameter['sub_status_return'] = $sub_status_return['Dom_Code'];
        $sub_status_repackage = $this->sys->getDomCodeByDomENDesc('Repackage', $where)->row_array();
        $parameter['sub_status_repackage'] = $sub_status_repackage['Dom_Code'];
        // END Add By Akkarapol, 06/11/2013, เพิ่มการ query ค่า sub status จาก db เพื่อให้ค่าของ dom_code ที่ได้ออกมานั้น ถูกต้องทั้งหมด และไม่ต้องไปตามแก้ในโค๊ดอีก
        // Add By Akkarapol, 17/09/2013, Set DomCode ที่ไม่ต้องเช็ค product,suplier เพื่อเอาไปใช้กับฟังก์ชั่น ereg
        $getDomCodes = $this->sys->getNoChkSup();
        $noChkSup = '/^';
        foreach ($getDomCodes as $keyDomCode => $domCode):
            $noChkSup = ($keyDomCode == 0 ? $noChkSup : $noChkSup . '|^');
            $noChkSup = $noChkSup . '[' . $domCode->Dom_Code . ']';
        endforeach;
        $noChkSup = $noChkSup . '/';
        $parameter['noChkSup'] = $noChkSup;
        $parameter['statusprice'] = $conf_price_per_unit;

        //ADD BY POR 2014-01-13 เพิ่มให้ตรวจสอบ config ว่าถ้ามี price_per_unit เป็น TRUE จะให้ระบุราคาต่อหน่วยด้วย
        $priceperunit = '';
        $unitofprice = '';
        if ($conf_price_per_unit):
            $priceperunit = " ,{
                    sSortDataType: \"dom-text\",
                    sType: \"numeric\",
                    type: 'text',
                    onblur: \"submit\",
                    event: 'click keyup',
                    is_required: true,
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
        //ADD BY POR 2014-10-04
        if ($conf_inv):
            $init_invoice = ",{
                sSortDataType: \"dom-text\",
                type: 'text',
                onblur: \"submit\",
                event: 'click focusin',
                loadfirst: true,
                width: '75%',
             }";
            $reinit_invoice = $init_invoice;
        else :
            $init_invoice = ",null";
            $reinit_invoice = "";
        endif;
        //End add

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
        // END Add By Akkarapol, 17/09/2013, Set DomCode ที่ไม่ต้องเช็ค product,suplier เพื่อเอาไปใช้กับฟังก์ชั่น ereg
        # LOAD FORM
        // p($data_form->form_name); exit;
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);
# PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'priceperunit' => $priceperunit //ADD BY POR 2014-01-13 ให้ส่งตัวแปรเกี่ยวกับการแสดงราคาต่อหน่วย
            , 'unitofprice' => $unitofprice //ADD BY POR 2014-01-13 ให้ส่งตัวแปรเกี่ยวกับการแสดงราคาต่อหน่วย
            , 'init_invoice' => $init_invoice //sent inittable for invoice :ADD BY POR 2014-10-04
            , 'reinit_invoice' => $reinit_invoice //sent inittable for invoice
            // Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_receive"></i>'
            // END Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'button_action' => $data_form->str_buttun
        ));
    }

#==================================================
#			Action Process Update data Base
#==================================================

    function openPreReceive() {
        #Load config
    //   p($this->input->post()); exit;
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

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
//        $owner_id = $this->session->userdata('owner_id');
	$owner_id = $this->input->post("consignee_id");
        $renter_id = $this->input->post("renter_id");
        $shipper_id = $this->input->post("shipper_id");
	$vendor_id = $this->input->post("shipper_id");
        $consignee_id = $this->input->post("consignee_id");
        $est_receive_date = $this->input->post("est_receive_date");
        $receive_type = $this->input->post("receive_type");
        $is_pending = $this->input->post("is_pending");
        $is_repackage = $this->input->post("is_repackage");
        $remark = $this->input->post("remark");
        $is_urgent = $this->input->post("is_urgent");
        # Parameter Order Detail
        $prod_list = $this->input->post("prod_list");

        # Parameter Index Datatable
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_lot = $this->input->post("ci_lot");
        $ci_serial = $this->input->post("ci_serial");
        $ci_mfd = $this->input->post("ci_mfd");
        $ci_exp = $this->input->post("ci_exp");
        $ci_invoice = $this->input->post("ci_invoice");
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_remark = $this->input->post("ci_remark");
        $ci_prod_id = $this->input->post("ci_prod_id");
        $ci_prod_status = $this->input->post("ci_prod_status");
        $ci_unit_id = $this->input->post("ci_unit_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");

        //ADD BY POR 2014-01-10 เพิ่มเกี่ยวกับราคา
        if ($conf_price_per_unit == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }
        //END ADD
        // Parameter for Invoice and Container
        $container_list = $this->input->post("container_list");
        $container_list = json_decode($container_list);

        $DeliveryTime = $this->input->post("DeliveryTime");
        $DestinationDetail = $this->input->post("DestinationDetail");

        $this->transaction_db->transaction_start();
        /**
         * Set Variable
         */
        $check_not_err = TRUE;


        /**
         * generate GRN Number
         */
        if ($check_not_err):
//            $document_no = strtoupper(createGRNNo());
            $document_no = strtoupper(create_document_no_by_type("GRN")); // Add by Ton! 20140428
            if (empty($document_no) || $document_no == ''):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not create 'GRN No'.";
            endif;
        endif;

        /**
         * create new Order and Order Detail
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

        $pending_status_code = "";
        if ($is_pending != ACTIVE) {
            $is_pending = INACTIVE;
        } else {
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
         * Create new Order
         */
        if ($check_not_err):
            $est_receive_date = convertDate($est_receive_date, "eng", "iso", "-");

            $order = array(
                'Flow_Id' => $flow_id
                , 'Document_No' => $document_no
                , 'Doc_Refer_Int' => (strlen(trim($doc_refer_int)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_int)))
                , 'Doc_Refer_Ext' => (strlen(trim($doc_refer_ext)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ext)))
                , 'Doc_Refer_AWB' => (strlen(trim($doc_refer_ext)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ext)))
                , 'Doc_Refer_Inv' => (strlen(trim($doc_refer_inv)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_inv)))
                , 'Doc_Refer_CE' => (strlen(trim($doc_refer_ce)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ce)))
                , 'Doc_Refer_BL' => (strlen(trim($doc_refer_bl)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_bl)))
                , 'Doc_Type' => $receive_type
                , 'Owner_Id' => $owner_id
                , 'Renter_Id' => $renter_id
                , 'Estimate_Action_Date' => $est_receive_date
                , 'Source_Id' => $shipper_id
		, 'Vendor_Id' => $shipper_id
                , 'Destination_Id' => $consignee_id
                , 'Source_Type' => 'Supplier'
                , 'Destination_Type' => 'Owner'
                , 'Is_Pending' => $is_pending
                , 'Is_Repackage' => $is_repackage
                , 'Is_urgent' => $is_urgent
                , 'Create_By' => $user_id
                , 'Remark' => (strlen(trim($remark)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $remark)))
                , 'Process_Type' => $process_type
                , 'Estimate_Action_Time' => (strlen(trim($DeliveryTime)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $DeliveryTime)))
                , 'Destination_Detail' => (strlen(trim($DestinationDetail)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $DestinationDetail)))
            );
            // p($order); exit;
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
         * Create new order_detail
         */
        if ($check_not_err):
            $order_detail = array();
            if (!empty($prod_list)) {
                foreach ($prod_list as $rows) {
                    $detail = array();
                    $a_data = explode(SEPARATOR, $rows);
                    $detail['Order_Id'] = $order_id;
                    $detail['Product_Id'] = $this->p->getProductIDByProdCode($a_data[$ci_prod_code]);

                    if (empty($detail['Product_Id']) || $detail['Product_Id'] == ""):
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Product ID not found.";
                        break;
                    endif;

                    $detail['Product_Code'] = $a_data[$ci_prod_code];

                    // BeGIN Insert Invoice
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
                                    $return['critical'][]['message'] = "Invoice No can't insert.";
                                    break;
                                endif;
                            endif;
                        endif;

                        $detail['Invoice_Id'] = $Invoice_Id;
                    endif;
                    //END Insert Invoice

                    $detail['Reserv_Qty'] = str_replace(",", "", $a_data[$ci_reserv_qty]);
                    $detail['Unit_Id'] = $a_data[$ci_unit_id];
                    if ($conf_price_per_unit == TRUE) {
                        $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]);
                        $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                        $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]);
                    }
                    // END Price Per Unit

                    $detail['Product_Lot'] = iconv("UTF-8", "TIS-620", strtoupper($a_data[$ci_lot]));
                    $detail['Product_Serial'] = iconv("UTF-8", "TIS-620", strtoupper($a_data[$ci_serial]));
                    $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                    $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);
                    $detail['Inbound_Item_Id'] = null;

                    if ($a_data[$ci_mfd] != "") {
                        $detail['Product_Mfd'] = convertDate($a_data[$ci_mfd], "eng", "iso", "-");
                    } else {
                        $detail['Product_Mfd'] = null;
                    }

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
                            // $diff = round(abs(strtotime("$mfd") - strtotime("$datedate"))/60/60/24);
                            // p($diff); exit;
                            $detail['Product_Exp'] = $datedate; 
                        }else{
                            $detail['Product_Exp'] = null;
                        }
                          
                    }

                    if ($is_pending == ACTIVE) {
                        $detail['Product_Status'] = $pending_status_code;
                    } else {
                        $detail['Product_Status'] = $a_data[$ci_prod_status];
                    }
                    $order_detail[] = $detail;
                }
            }
                    
                    /**
                     * check product_status empty
                     */

                if (empty($detail['Product_Status'] ) || empty($detail['Product_Sub_Status'])){
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Product status & Product sub status is empty.";
                }
                    /**
                     * end check product_status empty
                     */
      
            
            
                     // BEGIN INSERT ORDER DETAIL
                //  p($order_detail); exit;
             
            if ($check_not_err && !empty($order_detail)):
                $result_order_detail = $this->stock->addOrderDetail($order_detail);
                if ($result_order_detail <= 0):
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not Create new Order.";
                endif;
            endif;
        // END INSERT ORDER DETAIL

        endif;

        /**
         * Insert Container List
         */
        if ($conf_cont):
            if ($check_not_err && $container_list != "") :
                $result_container_list = $this->container->save_container($order_id, $container_list);
                if ($result_container_list <= 0) :
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Save container failed.";
                    log_message("ERROR", "Save container failed");
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

            $set_return['message'] = "Save Pre-Receive Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Save Pre-Receive Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
        endif;

        echo json_encode($json);
    }

    function confirmPreReceive() {

        $token = $this->input->post('token');
        $flow_id = $this->input->post('flow_id');
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order');
// p($flow_detail);
        if (empty($flow_detail)) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
        else :

            $process_id = $flow_detail['0']->Process_Id;
            $present_state = $flow_detail['0']->Present_State;

            // validate token
            $response = validate_token($token, $flow_id, $present_state, $process_id);

            if (!$response) :
                $array_return['critical'][]['message'] = "Document already pass this step.";
                $json['status'] = "save";
                $json['return_val'] = $array_return;
            else :

                $this->transaction_db->transaction_start();

                $check_not_err = TRUE;
    // p( $this->input->post());
                $respond = $this->_updateProcess($this->input->post());
                p( $respond);
                if (!empty($respond['critical'])) :
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not update Process.";
                    $return = array_merge_recursive($return, $respond);

                endif;

                /**
                 * check if for return json and set transaction
                 */
                if ($check_not_err):
                    /**
                     * ================== Auto End Transaction =========================
                     */
                    $this->transaction_db->transaction_end();

                    $set_return['message'] = "Confirm Pre-Receive Complete.";
                    $return['success'][] = $set_return;
                    $json['status'] = "save";
                    $json['return_val'] = $return;
                else:
                    /**
                     * ================== Rollback Transaction =========================
                     */
                    $this->transaction_db->transaction_rollback();

                    $array_return['critical'][]['message'] = "Confirm Pre-Receive Incomplete";
                    $json['status'] = "save";
                    $json['return_val'] = array_merge_recursive($array_return, $return);
                endif;

            endif;

        endif;

        echo json_encode($json);
    }

    function approvePreReceive() {

        #Retrive Data from Table
        $conf = $this->config->item('_xml');
        $print_barcode = empty($conf['print_barcode']) ? FALSE : $conf['print_barcode'];
        $token = $this->input->post('token');
        $flow_id = $this->input->post('flow_id');
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order');

        if (empty($flow_detail)) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
        else :
            $process_id = $flow_detail['0']->Process_Id;
            $present_state = $flow_detail['0']->Present_State;
            $document_no = $flow_detail['0']->Document_No;

            // validate token
            $response = validate_token($token, $flow_id, $present_state, $process_id);

            if (!$response) :
                $array_return['critical'][]['message'] = "Document already pass this step.";
                $json['status'] = "save";
                $json['return_val'] = $array_return;
            else :


                $this->transaction_db->transaction_start(); //ADD BY POR 2014-02-27

                $check_not_err = TRUE;

                $respond = $this->_updateProcess($this->input->post());
                if (!empty($respond['critical'])) :
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not update Process.";
                    $return = array_merge_recursive($return, $respond);

                endif;

                /**
                 * check if for return json and set transaction
                 */
                if ($check_not_err):
                    /**
                     * ================== Auto End Transaction =========================
                     */
                    $this->transaction_db->transaction_end();

                    $set_return['message'] = "Approve Pre-Receive Complete";
                    if ($print_barcode) {
                        $set_return['document_no'] = $document_no;
                    }
                    $return['success'][] = $set_return;
                    $json['status'] = "save";
                    $json['return_val'] = $return;
                else:
                    /**
                     * ================== Rollback Transaction =========================
                     */
                    $this->transaction_db->transaction_rollback();

                    $array_return['critical'][]['message'] = "Approve Pre-Receive Incomplete";
                    $json['status'] = "save";
                    $json['return_val'] = array_merge_recursive($array_return, $return);
                endif;

            endif;

        endif;

        echo json_encode($json);
    }

    function _updateProcess() {
        #Load config

        // p($this->input->post()); exit;
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

        #Parameter of document number
        $document_no = $this->input->post("document_no");
          
        $doc_refer_int = $this->input->post("doc_refer_int");
        $doc_refer_ext = $this->input->post("doc_refer_ext");
        $doc_refer_inv = $this->input->post("doc_refer_inv");
        $doc_refer_ce = $this->input->post("doc_refer_ce");
        $doc_refer_bl = $this->input->post("doc_refer_bl");

        # Parameter Order Document
//        $owner_id = $this->session->userdata('owner_id');
	    $owner_id = $this->input->post("consignee_id");
        $renter_id = $this->input->post("renter_id");
        $shipper_id = $this->input->post("shipper_id");
	    $vendor_id = $this->input->post("shipper_id");
        $consignee_id = $this->input->post("consignee_id");
        $est_receive_date = $this->input->post("est_receive_date");
        $receive_type = $this->input->post("receive_type");
        $is_pending = $this->input->post("is_pending");
        $is_repackage = $this->input->post("is_repackage");
        $is_urgent = $this->input->post("is_urgent");
        $remark = $this->input->post("remark");

        # Parameter Order Detail
        $prod_list = $this->input->post("prod_list");
        $prod_del_list = $this->input->post("prod_del_list");

        # Parameter Index Datatable
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_lot = $this->input->post("ci_lot");
        $ci_serial = $this->input->post("ci_serial");
        $ci_mfd = $this->input->post("ci_mfd");
        $ci_exp = $this->input->post("ci_exp");
        if ($conf_inv): $ci_invoice = $this->input->post("ci_invoice");
        endif; //invoice no :BY POR 2014-07-10
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_remark = $this->input->post("ci_remark");
        $ci_prod_id = $this->input->post("ci_prod_id");
        $ci_prod_status = $this->input->post("ci_prod_status");
        $ci_unit_id = $this->input->post("ci_unit_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");
        //ADD BY POR 2014-01-10 เพิ่มเกี่ยวกับราคา
        if ($conf_price_per_unit == TRUE) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }
        //END ADD

        $container_list = "";
        if ($conf_cont):
            $container_list = $this->input->post("container_list");     //ADD BY POR 2014-07-09
            $container_list = json_decode($container_list);
        endif;

        $DeliveryTime = $this->input->post("DeliveryTime");  //Add By Joke 16/6/2016
        $DestinationDetail = $this->input->post("DestinationDetail"); //Add By Joke 16/6/2016


        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;

        $pending_status_code = "";
        if ($is_pending != ACTIVE) {
            $is_pending = INACTIVE;
        } else {
            $result = $this->sys->getPendingStatus();
            if (!empty($result)) {
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
                'Doc_Refer_Int' => (strlen(trim($doc_refer_int)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_int)))
                , 'Doc_Refer_Ext' => (strlen(trim($doc_refer_ext)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ext)))
                , 'Doc_Refer_AWB' => (strlen(trim($doc_refer_ext)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ext)))
                , 'Doc_Refer_Inv' => (strlen(trim($doc_refer_inv)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_inv)))
                , 'Doc_Refer_CE' => (strlen(trim($doc_refer_ce)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ce)))
                , 'Doc_Refer_BL' => (strlen(trim($doc_refer_bl)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_bl)))
                , 'Doc_Type' => $receive_type
                , 'Owner_Id' => $owner_id
                , 'Renter_Id' => $renter_id
                , 'Estimate_Action_Date' => $est_receive_date
                , 'Source_Id' => $shipper_id
		        , 'Vendor_Id' => $shipper_id
                , 'Destination_Id' => $consignee_id
                , 'Is_Pending' => $is_pending
                , 'Is_Repackage' => $is_repackage
                , 'Is_urgent' => $is_urgent
                , 'Remark' => (strlen(trim($remark)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $remark)))
                , 'Modified_By' => $user_id
                , 'Modified_Date' => date("Y-m-d H:i:s")
                , 'Estimate_Action_Time' => (strlen(trim($DeliveryTime)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $DeliveryTime)))
                , 'Destination_Detail' => (strlen(trim($DestinationDetail)) == 0 ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $DestinationDetail)))
            );

            $where['Flow_Id'] = $flow_id;
            $where['Order_Id'] = $order_id;
            $result_updateOrder = $this->stock->updateOrder($order, $where);
            if (!$result_updateOrder):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update Order.";
            endif;
        endif;

        /**
         * Update Container List
         */
        if ($check_not_err && $container_list != "" && $conf_cont) :
            $result_container_list = $this->container->update_container($order_id, $container_list);
            //$result_container_list =0;
            if ($result_container_list <= 0) :
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Save container failed.";
                log_message("ERROR", "Save container failed");
            endif;

        endif;


        /**
         * update Order Detail
         */
        if ($check_not_err):
            $order_detail = array();
            if (!empty($prod_list)) {
                foreach ($prod_list as $rows) {
                    $a_data = explode(SEPARATOR, $rows);
                    $is_new = $a_data[$ci_item_id];
                    $detail = array();
                    $detail['Product_Id'] = $a_data[$ci_prod_id];
                    $detail['Product_Code'] = $a_data[$ci_prod_code];
                    $detail['Product_Status'] = $a_data[$ci_prod_status];
                    p($detail['Product_Status']); exit;
                    $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                    //$detail['Reserv_Qty'] = $a_data[$ci_reserv_qty]; //-----COMMENT BY POR 2013-11-28 แก้ไขโดยให้รองรับ qty แบบ float
                    //Insert Invoice_No in  STK_T_Invoice table and return Invoice_Id for insert into STK_T_Order_Detail table : BY POR 2014-07-10

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

                    $detail['Reserv_Qty'] = str_replace(",", "", $a_data[$ci_reserv_qty]); //+++++ADD BY POR 2013-11-28 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                    $detail['Unit_Id'] = $a_data[$ci_unit_id];
                    //ADD BY POR 2014-01-09 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                    if ($conf_price_per_unit == TRUE) {
                        $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]); //+++++ADD BY POR 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                        $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                        $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]); //+++++ADD BY POR 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
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
                        ( $detail['Product_Status']); exit;
                    }

                    if ("new" != $is_new) {
                        unset($where);
                        $where['Item_Id'] = $is_new;
                        $where['Order_Id'] = $order_id;
                        $where['Product_Code'] = trim($detail['Product_Code']);
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
                        if (empty($afftectedRows) || $afftectedRows <= 0): //ถ้ามากกว่าถือว่ามีการ insert ข้อมูล
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
            if (is_array($prod_del_list) && (!empty($prod_del_list))) {
                unset($rows);
                unset($detail);
                $item_delete = array();
                foreach ($prod_del_list as $rows) {
                    //$a_data = explode(",", $rows); //COMMENT BY POR 2014-01-09 ยกเลิก ,
                    $a_data = explode(SEPARATOR, $rows); //ADD BY POR 2014-01-09 ให้ explode |-- แทน เนื่องจากค่าที่ส่งมาใช้ |-- คั่น
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

        //logOrderDetail($order_id, 'pre_receive', $action_id, $action_type); //COMMENT BY POR 2014-07-07

        return $return;
    }

#==================================================
#			Get Selection Data
#==================================================

    function getProductStatus() {
        $is_pending = $this->input->post('is_pending');
        $this->load->model("product_model", "product");
        $query = $this->product->selectProductStatus($is_pending);
        $result = $query->result();
        $status_list = array();
        if (count($result) > 0) {
            foreach ($result as $rows) {
                $status_list[$rows->Dom_Code] = $rows->Dom_EN_Desc;
            }
        } else {
            $status_list = array("No Specified");
        }
        // p($status_list); exit;
        echo json_encode($status_list);
    }

    function getSubStatus() {
        $this->load->model("product_model", "product");
        $query = $this->product->selectSubStatus();
        $result = $query->result();
        $status_list = array();
        if (count($result) > 0) {
            foreach ($result as $rows) {
                $status_list[$rows->Dom_Code] = $rows->Dom_EN_Desc;
            }
        } else {
            $status_list = array("No Specified");
        }
        echo json_encode($status_list);
    }

    function getProductUnit() {
        $this->load->model("product_model", "product");
        $query = $this->product->selectProductUnitName();
        $result = $query->result();
        $status_list = array();
        if (count($result) > 0) {
            foreach ($result as $rows) {
                $status_list[$rows->Dom_Code] = tis620_to_utf8($rows->Dom_EN_Desc);  //edit can read thai language : ADD BY POR 2015-01-13
            }
        } else {
            $status_list = array("No Specified");
        }
        echo json_encode($status_list);
    }

    //ADD BY POR 2014-01-09 เพิ่มให้เรียก unit ของราคา
    function getPriceUnit() {
        $this->load->model("product_model", "product");
        $query = $this->product->selectPriceUnitName();
        $result = $query->result();

        $status_list = array();
        if (count($result) > 0) {
            foreach ($result as $rows) {
                $status_list[$rows->Dom_Code] = tis620_to_utf8($rows->Dom_EN_Desc); //edit can read thai language : ADD BY POR 2015-01-13
            }
        } else {
            $status_list = array("No Specified");
        }
        echo json_encode($status_list);
    }

    function getContainerSize() {

        //ADD BY POR 2014-07-08 select container size for size list in container
        $container_size = $this->container->getContainerSize()->result();
        $_container_list = array();
        foreach ($container_size as $idx => $value) {
            $_container_list[$idx]['Id'] = $value->Cont_Size_Id;
            $_container_list[$idx]['No'] = $value->Cont_Size_No;
            $_container_list[$idx]['Unit_Code'] = $value->Cont_Size_Unit_Code;
        }
        echo json_encode($_container_list);
    }

#==================================================
#			Get List Datatable
#==================================================

    function productList() {
        $data = $this->input->get();
        $product_code = @$data['post_val'];
        $supplier_id = @$data['supplier_id'];
        $limit_max = ($data['iDisplayLength'] < 0 ? '999999' : $data['iDisplayLength']);
        $limit_start = $data['iDisplayStart'];
        $s_search = iconv("UTF-8", "TIS-620", $data['sSearch']);
        $this->load->model("product_model", "prod");
        $query = $this->prod->getProductAll($product_code, '', $supplier_id, $limit_start, $limit_max, $s_search, 'STK_M_Product.Product_Code ASC');
        $count_result = $query->num_rows();
        $result = $query->result();
        $response = array();
        foreach ($result as $k => $v) :
            $response[] = array("<input CLASS=chkBoxValClass type=checkbox name=chkBoxVal[] value='" . $v->Product_Id . "' id='chkBoxVal" . $v->Product_Id . "' onClick='getCheckValue(this)'>"
                , $v->Product_Code
                , thai_json_encode($v->Product_NameEN)
                , thai_json_encode($v->Product_NameTH)
                , $v->public_name
            );
        endforeach;
        $total_filter_product = $this->prod->getProductAll($product_code, '', $supplier_id, '0', '999999', $s_search)->num_rows();
        $total_product = $this->prod->getProductAll('', '', '', '0', '999999', $s_search, NULL, " ORDER BY CASE IsNumeric(Product_Code) WHEN 1 THEN Replicate('0', 100 - Len(Product_Code)) + Product_Code ELSE Product_Code END")->num_rows();
        $output = array(
            "sEcho" => (int) $data['sEcho'],
            "iTotalRecords" => $total_product,
            "iTotalDisplayRecords" => $total_filter_product,
            "aaData" => $response
        );
        echo json_encode($output);
        exit();
        $data = array();
        $data_list = array();
        $action = array();
        $action_module = "";
        $column = array(
            _lang('select')
            , _lang('product_code')
            , _lang('product_name_en')
            , _lang('product_name_th')
            , _lang('unit')
        );

        if (is_array($result) && count($result)) {
            $count = 1;
            foreach ($result as $rows) {
                $data['Id'] = "<input ID=chkBoxVal type=checkbox name=chkBoxVal[] value='" . $rows->Product_Id . "' id=chkBoxVal" . $rows->Product_Id . " onClick='getCheckValue(this)'>";
                $data['Product_Code'] = $rows->Product_Code;
                $data['Product_NameEN'] = $rows->Product_NameEN;
                $data['Product_NameTH'] = $rows->Product_NameTH;
                $data['Unit'] = $rows->Dom_EN_Desc;
                $count++;
                $data_list[] = (object) $data;
            }
        }
        echo $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);
    }

    function showProduct() {
        $product_code = $this->input->post("post_val");
        $receive_type = $this->input->post("receive_type");
        $this->load->model("system_management_model", "sys");
        $result = $this->sys->getNormalStatus();
        $status_code = "";
        $status_value = "";
        if (count($result) > 0) {
            foreach ($result as $rows) {
                $status_code = $rows->Dom_Code;
                $status_value = $rows->Dom_EN_Desc;
            }
        }
        unset($result);
        $result = $this->sys->getNotSpecifiedStatus();

        $sub_status_code = "";
        $sub_status_value = "";
        if (count($result) > 0) {
            foreach ($result as $rows) {
                $sub_status_code = $rows->Dom_Code;
                $sub_status_value = $rows->Dom_EN_Desc;
            }
        }

//      Add By Akkarapol, 07/11/2013, Defect 438, เพิ่มการหาค่าที่ถูกต้องของ sub status ให้ตอนที่ สร้าง item เพื่อให้ sub status dom code ส่งไปได้ถูกต้อง และจะทำให้เช็คค่าได้ถูกต้อง        $where['Dom_Host_Code'] = 'RCV_TYPE';
        $where['Dom_Host_Code'] = 'RCV_TYPE';
        $db_receive_type = $this->sys->getDomCodeByDomENDesc('Return', $where)->row_array();
        if ($receive_type == $db_receive_type['Dom_Code']):
            $where['Dom_Host_Code'] = 'SUB_STATUS';
            $sub_status_code = $this->sys->getDomCodeByDomENDesc('Return', $where)->row_array();
            $sub_status_code = $sub_status_code['Dom_Code'];
            $sub_status_value = 'Return';
        endif;
//        END Add By Akkarapol, 07/11/2013, Defect 438, เพิ่มการหาค่าที่ถูกต้องของ sub status ให้ตอนที่ สร้าง item เพื่อให้ sub status dom code ส่งไปได้ถูกต้อง และจะทำให้เช็คค่าได้ถูกต้อง
        //ADD BY POR 2014-01-17 หา unit price ตั้งต้นมาแสดง

        $where['Dom_Host_Code'] = 'PRICE_UNIT';
        $where['Dom_Active'] = 'Y';

        $select[] = 'TOP 1 Dom_EN_Desc';
        $order_by = 'Sequence ASC';
        $unitprice = $this->sys->getDomCodeByDomENDesc(NULL, $where, $select, $order_by)->row_array(); //edit from fixed to select order by Sequence:COMMENT BY POR : 2014-09-16

        $unitprice_id = $unitprice['Dom_Code'];
        $unitprice_des = $unitprice['Dom_EN_Desc'];
        //END ADD

        $this->load->model("product_model", "prod");
        $query = $this->prod->getProductArray($product_code);
        $product_list = $query->result_array();
        $new_list = array();
        foreach ($product_list as $rows) {
            $rows['Prod_Status'] = $status_code;
            $rows['Prod_Status_Value'] = $status_value;
            $rows['Prod_Sub_Status'] = $sub_status_code;
            $rows['Prod_Sub_Status_Value'] = $sub_status_value;
            $rows['unitprice_id'] = $unitprice_id; //ส่ง DOM ของ Unit price กลับไปด้วย
            $rows['unitprice'] = $unitprice_des; //ส่ง DESC ของ Unit price กลับไปด้วย
            $new_list[] = thai_json_encode($rows);
        }
        $json['product'] = $new_list;
        $json['status'] = "1";
        $json['error_msg'] = "";
        echo json_encode($json);
    }

    function showProductWhenEnterKey() {
        $product_code = $this->input->post("post_val");
        $supplier_id = $this->input->post("supplier_id");
        $this->load->model("system_management_model", "sys");
        $result = $this->sys->getNormalStatus();
        $status_code = "";
        $status_value = "";
        if (count($result) > 0) {
            foreach ($result as $rows) {
                $status_code = $rows->Dom_Code;
                $status_value = $rows->Dom_EN_Desc;
            }
        }
        unset($result);
        $result = $this->sys->getNotSpecifiedStatus();
        $sub_status_code = "";
        $sub_status_value = "";
        if (count($result) > 0) {
            foreach ($result as $rows) {
                $sub_status_code = $rows->Dom_Code;
                $sub_status_value = $rows->Dom_EN_Desc;
            }
        }

        //ADD BY POR 2014-01-17 หา unit price ตั้งต้นมาแสดง
        $where['Dom_Host_Code'] = 'PRICE_UNIT';
        $where['Dom_Active'] = 'Y';

        $select[] = 'TOP 1 Dom_EN_Desc';
        $order_by = 'Sequence ASC';
        $unitprice = $this->sys->getDomCodeByDomENDesc(NULL, $where, $select, $order_by)->row_array(); //edit from fixed to select order by Sequence:COMMENT BY POR : 2014-09-16

        $unitprice_id = $unitprice['Dom_Code'];
        $unitprice_des = $unitprice['Dom_EN_Desc'];
        //END ADD

        $this->load->model("product_model", "prod");
        $query = $this->prod->getFirstProductArrayByProductCode($product_code, $supplier_id, array(), 'Product_Code ASC');
        $product_list = $query->row_array();

        $new_list = array();
        if (!empty($product_list)):
            $product_list['Prod_Status'] = $status_code;
            $product_list['Prod_Status_Value'] = $status_value;
            $product_list['Prod_Sub_Status'] = $sub_status_code;
            $product_list['Prod_Sub_Status_Value'] = $sub_status_value;
            $product_list['unitprice_id'] = $unitprice_id; //ส่ง DOM ของ Unit price กลับไปด้วย
            $product_list['unitprice'] = $unitprice_des; //ส่ง DESC ของ Unit price กลับไปด้วย
            $new_list[] = thai_json_encode($product_list);
        endif;


        $json['product'] = $new_list;
        $json['status'] = "1";
        $json['error_msg'] = (empty($product_list) ? $json['error_msg'] = _lang('product_code') . " Not Found In Supplier" : "");

        echo json_encode($json);
    }

    function ajax_show_product_list() {
        $text_search = $this->input->post('text_search');
        $supplier_id = $this->input->post("supplier_id");
        $this->load->model("encoding_conversion", "conv");
        $product = $this->p->searchProduct($text_search, $supplier_id, 100, 0, 'Product_Code ASC');
        $list = array();
        foreach ($product as $key_p => $p) {
            $list[$key_p]['product_id'] = $p['product_id'];
            $list[$key_p]['product_code'] = $p['product_code'];
//            $list[$key_p]['product_name'] = '';
            $list[$key_p]['product_name'] = thai_json_encode($p['product_name']);
        }
        echo json_encode($list);
    }

//        Add by Akkarapol, 27/08/2013, ทำ autocomplete ในส่วนของ Product Code และเมื่อคลิกที่ list product ดังกล่าว ก็จะให้ auto input to datatable
    function showProductList() {
        $text_search = $this->input->post('text_search');
        $supplier_id = $this->input->post("supplier_id");
        $this->load->model("encoding_conversion", "conv");
        $product = $this->p->searchProduct($text_search, $supplier_id);
        $list = '';
        foreach ($product as $p) {
            $list.='<li onClick="fill(\'' . $p['product_id'] . '\',\'' . $p['product_code'] . '\');">' . $p['product_code'] . ' ' . $this->conv->tis620_to_utf8($p['product_name']) . '</li>';
        }
        echo $list;
    }

//        END Add by Akkarapol, 27/08/2013, ทำ autocomplete ในส่วนของ Product Code และเมื่อคลิกที่ list product ดังกล่าว ก็จะให้ auto input to datatable
    // Add By Akkarapol, 16/09/2013, เพิ่มฟังก์ชั่น สำหรับ ตรวจสอบว่า Product MFD. ที่เลือกมานั้น มี ShelfLife ตามที่เซ็ตค่าใน Master หรือไม่
    function chkMFDDateOfProduct() {
        $chk_prod_code = $this->input->post("chk_prod_code");
        $chk_mfd = $this->input->post("chk_mfd");
        $this->load->model("location_model", "sys");
        $result = $this->p->getProductDetailByProductCode($chk_prod_code);
        $result = $result->row_array();
        $spl_date_mfd = preg_split('/\//', $chk_mfd); // Edit By Akkarapol, 16/09/2013, เปลี่ยนจากการเรียกใช้ ฟังก์ชั่น split เป็น preg_split เนื่องจาก PHP V 5.3.0+ ไม่รองรับ ฟังก์ชั่น split แล้ว
        $mktime = (mktime(0, 0, 0) - mktime(0, 0, 0, $spl_date_mfd[1], $spl_date_mfd[0], $spl_date_mfd[2])) / ( 60 * 60 * 24 );

        if ($mktime >= $result['Min_ShelfLife']):
            echo 'Ok';
        else:
	    echo 'Ok';
            //echo 'Min ShelfLife of "' . $result['Product_NameEN'] . '" = ' . (int) $result['Min_ShelfLife'] . ' but you choose day = ' . $mktime;
        endif;
    }

    // END Add By Akkarapol, 16/09/2013, เพิ่มฟังก์ชั่น สำหรับ ตรวจสอบว่า Product MFD. ที่เลือกมานั้น มี ShelfLife ตามที่เซ็ตค่าใน Master หรือไม่
    // Add By Akkarapol, 16/09/2013, เพิ่มฟังก์ชั่น สำหรับ ตรวจสอบว่า Product Exp. ที่เลือกมานั้น มี Aging ตามที่เซ็ตค่าใน Master หรือไม่
    function chkExpDateOfProduct() {
        $chk_prod_code = $this->input->post("chk_prod_code");
        $chk_exp = $this->input->post("chk_exp");
        $this->load->model("location_model", "sys");
        $result = $this->p->getProductDetailByProductCode($chk_prod_code);
        $result = $result->row_array();
        $spl_date_exp = preg_split('/\//', $chk_exp); // Edit By Akkarapol, 16/09/2013, เปลี่ยนจากการเรียกใช้ ฟังก์ชั่น split เป็น preg_split เนื่องจาก PHP V 5.3.0+ ไม่รองรับ ฟังก์ชั่น split แล้ว
        $mktime = (mktime(0, 0, 0, $spl_date_exp[1], $spl_date_exp[0], $spl_date_exp[2]) - mktime(0, 0, 0)) / ( 60 * 60 * 24 );
       

	echo 'Ok';
 
	// TEMP REMOVE P'Danai Request
	/*
	if ($result['Min_Aging'] == 0 || $mktime >= $result['Min_Aging']):
            echo 'Ok';
        else:
            echo 'Min Aging of "' . $result['Product_NameEN'] . '" = ' . (int) $result['Min_Aging'] . ' but you choose day = ' . $mktime;
        endif;
	*/
    }

    // END Add By Akkarapol, 16/09/2013, เพิ่มฟังก์ชั่น สำหรับ ตรวจสอบว่า Product Exp. ที่เลือกมานั้น มี Aging ตามที่เซ็ตค่าใน Master หรือไม่
    #ISSUE 3034 Reject Document
    #DATE:2013-11-11
    #BY:KIK
    #เพิ่มในส่วนของ reject and (reject and return)
    #START New Comment Code #ISSUE 3034 Reject Document

    function rejectAction() {
        /**
         * Set Variable
         */
        $check_not_err = TRUE;
        $return = array();

        // Validate Concurrent
        #Retrive Data from Table
        $flow_id = $this->input->post("flow_id");
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order');

        if (empty($flow_detail)) :
            $array_return['critical'][]['message'] = "Document already pass this step.";
            $json['status'] = "save";
            $json['return_val'] = $array_return;
        else :

            $token = $this->input->post('token');
            $process_id = $flow_detail['0']->Process_Id;
            $present_state = $flow_detail['0']->Present_State;

            // validate token
            $response = validate_token($token, $flow_id, $present_state, $process_id);
            // End

            if (!$response) :
                $array_return['critical'][]['message'] = "Document already pass this step.";
                $json['status'] = "save";
                $json['return_val'] = $array_return;
            else :



                $this->load->model("stock_model", "stock");
                $this->load->model("workflow_model", 'wf_model');

                #add condition check data from list page or form page : add by kik : 2013-12-02
                if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") :

                    #if data send from list page When find data in database
                    if ($this->input->post('id') != "") :

                        $column_workflow_ = 'Document_No,Process_Id,Present_State';
                        $where_workflow_['Flow_Id'] = $this->input->post('id');
                        $workflow_query = $this->wf_model->getWorkflowTable($column_workflow_, $where_workflow_);
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
                    endif;
                #if data send from form page When get data in post() : add by kik : 2013-12-02
                else :
                    $process_id = $this->input->post("process_id");
                    $flow_id = $this->input->post("flow_id");
                    $present_state = $this->input->post("present_state");
                    $action_type = $this->input->post("action_type");
                    $next_state = $this->input->post("next_state");
                    $document_no = $this->input->post("document_no");
                    $data['Document_No'] = $document_no;
                    $order_id = $this->input->post("order_id");
                endif;

                #------------------------------------------------------------
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

                if ($check_not_err) :
                    //update order detail = N
                    $detail['Active'] = 'N';
                    $where['Order_Id'] = $order_id;

                    $result = $this->stock->updateOrderDetail($detail, $where);

                    if (!$result) :
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Can not update Order Detail.";
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
                        echo "<script>alert('Delete Pre-Receive complete.');</script>";
                        redirect('flow/flowPreReceiveList', 'refresh');
                    else :
                        $set_return['message'] = "Reject Pre-Receive Complete.";
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
                        echo "<script>alert('Delete Pre-Receive not complete. Please check?');</script>";
                        redirect('flow/flowPreReceiveList', 'refresh');
                    else :
                        $set_return['critical'][]['message'] = "Reject Pre-Receive Incomplete.";
                        $return = array_merge_recursive($set_return, $return);
                        $json['status'] = "save";
                        $json['return_val'] = $return;
                    endif;
                endif;
            endif;
        endif;

        echo json_encode($json);
    }

    function rejectAndReturnAction() {
        $process_id = $this->input->post("process_id");
        $flow_id = $this->input->post("flow_id");
        $present_state = $this->input->post("present_state");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $document_no = $this->input->post("document_no");

        $data['Document_No'] = $document_no;

        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order');

        if (empty($flow_detail)) :
            $json['status'] = "ERROR";
            $json['msg'] = "Document already pass this step.";
        else :
            $token = $this->input->post('token');
            $process_id = $flow_detail['0']->Process_Id;
            $present_state = $flow_detail['0']->Present_State;

            // validate token
            $response = validate_token($token, $flow_id, $present_state, $process_id);
            // End

            if (!$response) :
                $json['status'] = "ERROR";
                $json['msg'] = "Document already pass this step.";
            else :

                $check_not_err = TRUE;
                $return = array();

                $this->transaction_db->transaction_start();

                $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
                if ($check_not_err):
                    if (empty($action_id) || $action_id == '') :
                        $check_not_err = FALSE;
                        $return['critical'][]['message'] = "Can not update Workflow.";
                    endif;
                endif;

                if ($check_not_err):
                    $this->transaction_db->transaction_end();
                    $set_return['message'] = "Reject and Return Pre-Receive Complete.";
                    $return['success'][] = $set_return;
                    $json['status'] = "save";
                    $json['return_val'] = $return;
                else:
                    $this->transaction_db->transaction_rollback();
                    $set_return['critical'][]['message'] = "Reject and Return Pre-Receive Incomplete.";
                    $return = array_merge_recursive($set_return, $return);
                    $json['status'] = "save";
                    $json['return_val'] = $return;
                endif;

            endif;

        endif;

        echo json_encode($json);
    }

    #End New Comment Code #ISSUE 3034 Reject Document
    // Add By Akkarapol, 03/12/2013, add function 'chk_doc_ext_duplicate' for check duplicate Document External when submit form

    function chk_doc_ext_duplicate() {
        $data = $this->input->post();
//p($data);exit();

        $set_data['column'] = array('Doc_Refer_Ext');
        $set_data['where']['Doc_Refer_Ext'] = $data['doc_refer_ext'];
        $set_data['where']['Process_Type'] = $data['Process_Type'];
        if (!empty($data['flow_id'])):
            $set_data['where']['STK_T_Order.Flow_Id !='] = $data['flow_id'];
        endif;

//        $result = $this->stock->getOrderTable($set_data['column'], $set_data['where']);
        $result = $this->stock->get_duplicate_ext_doc($set_data['column'], $set_data['where'], @$data['flow_id'])->result(); // Add By Akkarapol, 16/12/2013, เพิ่มการเรียกฟังก์ชั่น get_duplicate_ext_doc เพื่อเช็คว่า ext doc ที่กรอกมานั้น มีค่าอยู่แล้วหรือไม่ในระบบ จะได้ alert แจ้งเตือนไป
        if (!empty($result)):
            echo FALSE;
        else:
            echo TRUE;
        endif;
    }

    // END Add By Akkarapol, 03/12/2013, add function 'chk_doc_ext_duplicate' for check duplicate Document External when submit form


    public function validation_openPreReceive() {
        $json['status'] = "pass";
        $json['return_val'] = '';
        echo json_encode($json);
    }

    public function validation_confirmPreReceive() {
        $json['status'] = "pass";
        $json['return_val'] = '';
        echo json_encode($json);
    }

    public function validation_approvePreReceive() {
        $json['status'] = "pass";
        $json['return_val'] = '';
        echo json_encode($json);
    }

    /**
     * Function for get column show or hide
     */
    public function getColumnShowHide() {
        $page = $this->input->post('page');
        $result = $this->datatable->genDynamicColumn($page);
        echo json_encode($result);
    }

    public function printBarcode() {

        $this->load->library('pdf/mpdf');
        $this->load->helper('file');

        $params = $this->input->get();
        $settings = $this->settings;
        $result = $this->p->searchProductForGenBarcode($params);
        if (empty($result)) {
            exit("DATA NOT FOUND!");
        }

        if ($params['paper_size'] == "A4") {
            $stylesheet = '<style>
            .box {width: 80%; margin: 0 auto; text-align: center;}
            .title {font-size: 44pt;}
            .sub_title {font-size: 24pt;}
            </style>';
            $bsize = "2.0";
        } else if ($params['paper_size'] == "2x3") {
            $stylesheet = '<style>
            .box {text-align: center;}
            .title {font-size: 8pt;}
            .sub_title {font-size: 8pt; padding-bottom: 2px;}
            </style>';
            $bsize = "1.0";
        } else {
            $stylesheet = '<style>
            .box {width: 80%; margin: 0 auto; text-align: center;}
            .title {font-size: 44pt;}
            .sub_title {font-size: 24pt;}
            </style>';
            $bsize = "2.0";
        }

        $page = $stylesheet;

        $start = 0;

        foreach ($result as $idx => $val) {

//            for ($i = 0; $i < $val->Reserv_Qty; $i++) {
            for ($i = 0; $i < 1; $i++) {

                if ($start > 0) {
                    $page .= "<pagebreak />";
                }

                $page .= '<div class="box">'
                        . '<barcode code="' . strtoupper($val->Product_Barcode) . '" type="C128A" size="1" height="'.$bsize.'" />'
                        . '<div class="title">' . strtoupper($val->Product_Barcode) . '</div>'
                        . '<div class="sub_title">' . strtoupper(tis620_to_utf8($val->Product_NameEN)) . '</div>'
                        . '</div>';

                if (!empty($val->Product_Lot)) {
                    $page .= '<div class="box">'
                            . '<barcode code="' . strtoupper($val->Product_Lot) . '" type="C128A" size="1" height="'.$bsize.'" />'
                            . '<div class="sub_title">' . strtoupper(tis620_to_utf8($val->Product_Lot)) . '</div>'
                            . '</div>';
                }

                if (!empty($val->Product_Serial)) {
                    $page .= '<div class="box">'
                            . '<barcode code="' . strtoupper($val->Product_Serial) . '" type="C128A" size="1" height="'.$bsize.'" />'
                            . '<div class="sub_title">' . strtoupper(tis620_to_utf8($val->Product_Serial)) . '</div>'
                            . '</div>';
                }

                $start++;
            }
        }

        $stylesheet = read_file('../libraries/pdf/style.css');

        if ($params['paper_size'] == "A4") {
            $mpdf = new mPDF('th', 'A4', 11, '', 10, 10, 30, 33, 10, 10);
        } else if ($params['paper_size'] == "2x3") {
            $mpdf = new mPDF('th', array(76, 51), 10, '', 1, 1, 3, 1, 0, 0);
        } else {
            $mpdf = new mPDF('th', 'A4', 11, '', 10, 10, 30, 33, 10, 10);
        }
        $mpdf->WriteHTML($stylesheet, TRUE);
        $mpdf->WriteHTML($page);

        $filename = 'Barcode-' . date('Ymd') . '-' . date('His') . '.pdf';
        $strSaveName = $settings['uploads']['upload_path'] . $filename;
        $tmp = $mpdf->Output($strSaveName, 'F');
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $filename . "\"");
        readfile($strSaveName);
    }

    public function printBarcodeByItem() {

        $this->load->library('pdf/mpdf');
        $this->load->helper('file');

        $params = $this->input->get();
        $settings = $this->settings;
        $result = $this->p->searchProductCodeForGenBarcode($params);
        if (empty($result)) {
            exit("DATA NOT FOUND!");
        }

        if ($params['paper_size'] == "A4") {
            $stylesheet = '<style>
            .box {width: 80%; margin: 0 auto; text-align: center;}
            .title {font-size: 44pt;}
            .sub_title {font-size: 24pt;}
            </style>';
            $bsize = "2.0";
        } else if ($params['paper_size'] == "2x3") {
            $stylesheet = '<style>
            .box {text-align: center;}
            .title {font-size: 8pt;}
            .sub_title {font-size: 8pt; padding-bottom: 2px;}
            </style>';
            $bsize = "1.0";
        }

        $page = $stylesheet;

        $start = 0;

        foreach ($result as $idx => $val) {

            for ($i = 0; $i < $params['quantity']; $i++) {

                if ($start > 0) {
                    $page .= "<pagebreak />";
                }

                $page .= '<div class="box">'
                        . '<barcode code="' . strtoupper($val->Product_Barcode) . '" type="C128A" size="2" height="'.$bsize.'" />'
                        . '<div class="title">' . strtoupper($val->Product_Barcode) . '</div>'
                        . '<div class="sub_title">' . tis620_to_utf8($val->Product_NameEN) . '</div>'
                        . '</div>';

                $start++;
            }
        }

        $stylesheet = read_file('../libraries/pdf/style.css');
        if ($params['paper_size'] == "A4") {
            $mpdf = new mPDF('th', 'A4', 11, '', 10, 10, 30, 33, 10, 10);
        } else if ($params['paper_size'] == "2x3") {
            $mpdf = new mPDF('th', array(76, 51), 10, '', 1, 1, 3, 1, 0, 0);
        }
        $mpdf->WriteHTML($stylesheet, TRUE);
        $mpdf->WriteHTML($page);

        $filename = 'Barcode-' . date('Ymd') . '-' . date('His') . '.pdf';
        $strSaveName = $settings['uploads']['upload_path'] . $filename;
        $tmp = $mpdf->Output($strSaveName, 'F');
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $filename . "\"");
        readfile($strSaveName);
    }


    public function edit_mfd_to_exp(){
// p($this->input->post()); 
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
            //  exit;
            $date = strtotime($datedate);
            $exp = date("d/m/Y", $date);
        }
        // else{
        //     $exp = "";
        // }
    echo json_encode($exp);
    return $exp;
    die(json_encode($exp));


}
}
