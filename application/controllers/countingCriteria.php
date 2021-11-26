<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of countingCriteria
 *
 * @author Pong-macbook
 */
class countingCriteria extends CI_Controller {

    //put your code here
    public function __construct() {
        parent::__construct();
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->helper('form');
        $this->load->model("counting_criteria_model", "ctm");
        $this->load->model("counting_model", "cm");
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("workflow_model", "wf");
        $this->load->model("stock_model", "stock");
    }

    public function index() {
        $isUserLogin = $this->session->userdata("user_id");
        if ($isUserLogin == "") {
            echo "<script>alert('Your session was expired. Please Log-in!')</script>";
            echo "<script>window.location.replace('" . site_url() . "');</script>";
        } else {
            $this->countingCriteriaList();
        }
    }

    public function countingCriteriaList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $userLogin = $this->session->userdata("user_id");
        if (!isset($userLogin)) {
            $chkIsWarehouseAdmin = $this->ctm->checkIsWarehouseAdmin($userLogin = 0);
        } else {
            $chkIsWarehouseAdmin = $this->ctm->checkIsWarehouseAdmin($userLogin);
        }
        $module = "countingCriteria";
        $workflow_id = 4;
        $this->load->model("workflow_model", "flow");

        $isWarehouseAdmin = count($chkIsWarehouseAdmin);
        $query = $this->flow->getWorkFlowByCounting($workflow_id, $isWarehouseAdmin);
        $receive_list = $query->result();

        #assign to warehouse admin

        if ($isWarehouseAdmin == 1) {
            $column = array("No.", "Document No.", "Status Doc.", "Document Type", "Target Date", "Process Day", "Assign"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow รวมทั้งเพิ่ม Target Date เข้าไปด้วยเนื่องจาก query นี้มันไปเชื่อมกับที่หน้า สำหรับ Daily แต่ไม่ได้มาปรับที่หน้านี้
        } else {
            $column = array("No.", "Document No.", "Status Doc.", "Document Type", "Target Date", "Process Day"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow รวมทั้งเพิ่ม Target Date เข้าไปด้วยเนื่องจาก query นี้มันไปเชื่อมกับที่หน้า สำหรับ Daily แต่ไม่ได้มาปรับที่หน้านี้
        }

        $action = array(VIEW, DEL);
        
        // Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย        
        if(($key = array_search(DEL, $action)) !== false) {
            unset($action[$key]);
        }
        // END Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย
         
        $datatable = $this->datatable->genTableFixColumn($query, $receive_list, $column, "countingCriteria/countingCriteriaFormWithData/", $action, "countingCriteria/rejectAction");


        $this->parser->parse('list_template', array(

//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Counting'
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
			 ONCLICK=\"openForm('countingCriteria','countingCriteria/countingCriteriaForm/','A','')\">"
        ));
    }

    public function countingCriteriaFormWithData() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        //p($_POST);exit();
        
        $conf = $this->config->item('_xml');
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];
        
        
        $user_id = $this->session->userdata("user_id");
        $flow_id = $this->input->post("id");
        $listCounting = $this->ctm->countingCriteriaMovementListShow($flow_id); //$this->ctm->getCountingFlowData($flow_id);        
        $flow_detail = $this->wf->getFlowDetailForCounting($flow_id);
        // p($flow_detail);exit();
        $process_id = $flow_detail[0]->Process_Id;
        $present_state = $flow_detail[0]->Present_State;
        $document_no = $flow_detail[0]->Document_No;
        $order_id = $flow_detail[0]->Order_Id;
        $module = $flow_detail[0]->Module;
		
        // validate document exist state
        $valid_state = validate_state($module);
        if ($valid_state) :
            redirect($valid_state);
        endif;

        // register token
        $parameter['token'] = register_token($flow_id, $present_state, $process_id);
        // end config token		      
        
        $conditionDetail = '';
        if ($present_state == 1) {
            $currentstep = "Confirm";
        } else if ($present_state == 2) {
            $currentstep = "Approve";
        }

        $data['Document_No'] = $document_no;

//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "countingCriteria");  // Button Permission. Add by Ton! 20140131
        //$action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $user_id, $data);
        $str_form = $this->parser->parse('form/countingCriteriaFormStep2', array(
            "process_id" => $process_id
            , "present_state" => $present_state
            , "listCounting" => $listCounting
            , "selected_chk" => 0
            , "conditionDetail" => $conditionDetail
            , "currentstep" => $currentstep
            , "flow_id" => $flow_id
            , "conf_pallet" => $conf_pallet
                ), TRUE);

        #start comment for hide button but add button in libraries/workflow: by kik : 2014-01-08
        // Defect 473
        // Remove confirm from PC when not confirm from HH
        // Check present state if more than 1 show it.
        $button = $data_form->str_buttun;
        
        // Add Excel and PDF to generate document
        // BALL
        $button .= '<input class="button dark_blue" type="button" onclick="exportFile(\'PDF\',\''.$document_no.'\')" value="PDF" style="margin-right: 30px;">';
        $button .= '<input class="button dark_blue" type="button" onclick="exportFile(\'Excel\',\''.$document_no.'\')" value="Excel">';        
       

        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form

            // Add By Akkarapol, 23/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_search_criteria"></i>'
            // END Add By Akkarapol, 23/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            // If present state is less than 1 not show !
            // , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'     
            , 'button_excel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'button_action' => $button
            , 'user_login' => $this->session->userdata('username')
        ));
    }

    public function countingCriteriaForm() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $process_id = 4;
        $present_state = 0;

        //$data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "countingCriteria");  // Button Permission. Add by Ton! 20140131
        //p($data_form);exit;
        $counting_type = $this->ctm->get_count_list()->result();
      // p($counting_type);exit;
        
        //$listMovement = $this->ctm->countingCriteriaMovementList();
        $str_form = $this->parser->parse('form/countingCriteriaForm', array(
            "parameter" => form_input('counting_criteria_form', 'counting_criteria_form')
            , "test_parse" => "test pass parse from controller"
            , "process_id" => $process_id
            , "present_state" => $present_state
            , 'counting_type' => $counting_type
            , 'token' => ''
                ), TRUE);
               
                
        $this->parser->parse('workflow_template', array(
                                                        'state_name' => $data_form->from_state_name
                                            //, 'menu' => $this->menu->loadMenuAuth()
                                                        , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
                                                        , 'form' => $str_form
                                                        // Add By Akkarapol, 23/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
                                                        , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_search_criteria"></i>'
                                                        // END Add By Akkarapol, 23/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
                                                        , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'
                                                        , 'button_action' => $data_form->str_buttun
                                                        
                                                       //, 'button_action' => '<input TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="NEXT" ONCLICK="postRequestAction();" / >'
                                                        , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL. '" ONCLICK="cancel()">'
                                                        , 'button_export' => '<INPUT id="export_button" style="display: none;" TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="Export To Excel"  ONCLICK="exportFileExcel()">'
                                                        , 'user_login' => $this->session->userdata('username')
                                                        ));
      



    }

    public function openCriteriaCountJob() {
        //p($_POST);exit();
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");

        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->session->userdata("user_id");
        $counting_type = $this->input->post("counting_type");
        $is_urgent = $this->input->post("is_urgent");   //add for ISSUE 3312 : by kik : 20140120\
        //add Urgent for ISSUE 3312 : by kik : 20140120
        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        $man_power = $this->input->post("manPower");
        $work_load = $this->input->post("workingLoad");
        $working_day = $this->input->post("workingDay");
        $taskAverage = $this->input->post("taskAverage");
        $total = $this->input->post("total");

        // DEBUG AREA 
        $prod_list = $this->input->post("prod_list");

        // grouping product item by location
        $aorder_detail = array();
        $jobs_by_location = array();
        foreach ($prod_list as $index => $value) {
            $exp = explode(SEPARATOR, $value); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
            $index_name = '';
            $jobs_by_location[$exp['3']][] = $exp;
        }

        //print_r($jobs_by_location);
        //Find maximum jobs per man
        $jobs_per_man = array();
        $max_index = 1;
        foreach ($prod_list as $index => $value) {
            $jobs_per_man[$max_index][] = true;
            ($max_index >= $man_power ? $max_index = 1 : $max_index++);
        }

        $man_jobs = array();
        $man_index = 1;
        foreach ($jobs_by_location as $index => $value) {
            foreach ($value as $jobs_index => $jobs_value) {
                $man_max_jobs = count($jobs_per_man[$man_index]);
                $countnum = isset($man_jobs[$man_index]) ? count($man_jobs[$man_index]) : 0;
                if ($countnum < ($man_max_jobs - 1)) {
                    $man_jobs[$man_index][] = $jobs_value;
                } else {
                    $man_jobs[$man_index][] = $jobs_value;
                    $man_index++;
                }
            }
        }

        // DEBUG AREA
        /* foreach ($man_jobs as $index => $value) {

          foreach ($value as $index2 => $value2) :

          $product_code = $value2['1'];
          $location = $value2['3'];
          $details = $this->ctm->get_product_location($product_code, $location)->result_array();

          $product_mfd = (empty($detail['Product_Mfd']) ? "NULL" : "'" . $detail['Product_Mfd'] . "'");
          $product_exp = (empty($detail['Product_Exp']) ? "NULL" : "'" . $detail['Product_Exp'] . "'");
          $counting_id = 0;

          foreach ($details as $detail) :
          $insert_select_query = "INSERT INTO STK_T_Counting_Detail(Order_Id
          ,Product_Id
          ,Product_Code
          ,Actual_Location_Id
          ,Product_Mfd
          ,Product_Lot
          ,Product_Serial
          ,Reserv_Qty
          ,Product_Status
          ,Product_Sub_Status
          ,Product_License
          ,Product_Exp
          ,Unit_Id) Values('" . $counting_id . "'
          ,'" . $detail['Product_Id'] . "'
          ,'" . $detail['Product_Code'] . "'
          ,'" . $detail['Actual_Location_Id'] . "'
          ,{$product_mfd}
          ,'" . $detail['Lot'] . "'
          ,'" . $detail['Serial'] . "'
          ,'" . $detail['QTY'] . "'
          ,'" . $detail['Product_Status'] . "'
          ,'" . $detail['Product_Sub_Status'] . "'
          ,'" . $detail['Product_License'] . "'
          ,{$product_exp}
          ,'" . $detail['Unit_Id'] . "')";
          //$order_detail_result = $this->ctm->queryFromString($insert_select_query);
          endforeach;

          endforeach;

          }

          exit(); */
        // END DEBUG AREA 

        foreach ($man_jobs as $index => $value) {
//            $docNo = $this->generateCountingDocNo($counting_type);
            $docNo = create_document_no_by_type("CC"); // Add by Ton! 20140428
            $counting = array(
                'Document_No' => $docNo
                , 'Counting_Type' => $counting_type
                , 'Counting_Desc' => ''
                , 'Estimate_Action_Date' => date("Y-m-d H:i:s")
                , 'Working_Day' => $working_day
                , 'Create_By' => $user_id
                , 'Modified_By' => $user_id
                , 'Create_Date' => date("Y-m-d H:i:s")
                , 'Is_urgent' => $is_urgent
            );
            $list_flow_action = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $counting);
            $flow_id = $list_flow_action[0];
            $action_id = $list_flow_action[1];

            // ADD BALL
            // update workflow to step 2
            $this->workflow->update_workflow_counting($flow_id, array("Present_State" => 1));
            // END BALL

            $counting['Flow_Id'] = $flow_id;
            $counting_id = $this->stock->getCountingStock($counting); //Call Fn to insert and return back counting_id
            $flow_arr[] = $flow_id;

            foreach ($value as $index2 => $value2) {

                // NEW
                $product_code = $value2['1'];
                $location = $value2['3'];
                $details = $this->ctm->get_product_location($product_code, $location)->result_array();

                // Comment By Akkarapol, 22/01/2014, โค๊ดส่วนนี้มันต้องนำไปใส่ใน loop ของ $details แทน เพราะถ้าใช้ตรงนี้ ค่าที่ต้องการจะใช้ไม่ได้เลย
//              $product_mfd = (empty($detail['Product_Mfd']) ? "NULL" : "'" . $detail['Product_Mfd'] . "'");
//            	$product_exp = (empty($detail['Product_Exp']) ? "NULL" : "'" . $detail['Product_Exp'] . "'");
                // END Comment By Akkarapol, 22/01/2014, โค๊ดส่วนนี้มันต้องนำไปใส่ใน loop ของ $details แทน เพราะถ้าใช้ตรงนี้ ค่าที่ต้องการจะใช้ไม่ได้เลย
                foreach ($details as $detail) :

                    // Add By Akkarapol, 22/01/2014, เพิ่มโค๊ดในส่วนของการเซ็ตค่าให้กับ MFD และ EXP เพื่อนำไป insert ข้อมูลต่อไป
                    $product_mfd = (empty($detail['Product_Mfd']) ? NULL : convert_date_format($detail['Product_Mfd']));
                    $product_exp = (empty($detail['Product_Exp']) ? NULL : convert_date_format($detail['Product_Exp']));
                    // END Add By Akkarapol, 22/01/2014, เพิ่มโค๊ดในส่วนของการเซ็ตค่าให้กับ MFD และ EXP เพื่อนำไป insert ข้อมูลต่อไป

                    $insert_select_query = "INSERT INTO STK_T_Counting_Detail(Order_Id
                		,Product_Id
                		,Product_Code
                		,Actual_Location_Id
                		,Product_Lot
                		,Product_Serial
                		,Reserv_Qty
                		,Product_Status
                		,Product_Sub_Status
                		,Product_License
                		,Product_Mfd
                		,Product_Exp
                		,Unit_Id
            			,Inbound_Id) Values('" . $counting_id . "'
        				,'" . $detail['Product_Id'] . "'
        				,'" . $detail['Product_Code'] . "'
        				,'" . $detail['Actual_Location_Id'] . "'
						,'" . $detail['Lot'] . "'
						,'" . $detail['Serial'] . "'
						,'" . $detail['QTY'] . "'
						,'" . $detail['Product_Status'] . "'
						,'" . $detail['Product_Sub_Status'] . "'
						,'" . $detail['Product_License'] . "'
						,'" . $product_mfd . "'
						,'" . $product_exp . "'
						,'" . $detail['Unit_Id'] . "'
            			,'" . $detail['Id'] . "');";
                    $order_detail_result = $this->ctm->queryFromString($insert_select_query);
                endforeach;
                
            }
        }

        foreach ($flow_arr as $k => $v) :

            $log_counting = array(
                'Man_Power' => $this->input->post("manPower")
                , 'Count_Id' => $v
                , 'Count_Type' => $counting_type
                , 'Workload' => $work_load
                , 'Work_Day' => $working_day
                , 'Date_Form' => date("Y-m-d H:i:s")
                , 'Date_To' => date('Y-m-d', strtotime(' +' . $working_day . ' day'))
                , 'Create_By' => $user_id
                , 'Work_Detail' => implode(",", $flow_arr)
                , 'Create_Date' => date("Y-m-d H:i:s")
                , 'Create_By' => $this->session->userdata("user_id")
            );

            $log_counting_result = $this->ctm->saveCountingOrder($log_counting);

        endforeach;

        $json['status'] = "C001";
        $json['error_msg'] = "";
        echo json_encode($json);

        exit();

        // END DEBUG
    }

    public function confirmCount() {
        //echo "confirmCount>>>".p($this->input->post()); 

        $flow_id = $this->input->post("flow_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->session->userdata("user_id");

        $flow_detail = $this->wf->getFlowDetailForCounting($flow_id);
        //p($flow_detail);exit();
        $process_id = $flow_detail[0]->Process_Id;
        $present_state = $flow_detail[0]->Present_State;
        $document_no = $flow_detail[0]->Document_No;
        // $order_id = $flow_detail[0]->Order_Id;
        $data = "";

//        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $user_id, $data);
        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021

        $json['status'] = "C002";
        $json['error_msg'] = "";
        echo json_encode($json);
    }

    public function approveCount() {
        $flow_id = $this->input->post("flow_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->session->userdata("user_id");

        $flow_detail = $this->wf->getFlowDetailForCounting($flow_id);
        //p($flow_detail);exit();
        $process_id = $flow_detail[0]->Process_Id;
        $present_state = $flow_detail[0]->Present_State;
        $document_no = $flow_detail[0]->Document_No;
        // $order_id = $flow_detail[0]->Order_Id;
        $data = "";

//        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $user_id, $data);
        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021

        $json['status'] = "C003";
        $json['error_msg'] = "";
        echo json_encode($json);
    }

    private function generateCountingDocNo() {
        $doc_type = $this->ctm->checkCountingDocumentType();

        if (count($doc_type) > 0) {
            $doc_date = substr($doc_type[0]->Document_No, -13, 8);
            $running = substr($doc_type[0]->Document_No, -5);
            if (date($doc_date) == date("Ymd")) {
                return "CC" . date("Ymd") . sprintf("%05d", $running + 1);
            } else {
                return "CC" . date("Ymd") . sprintf("%05d", 1);
            }
        } else {
            return "CC" . date("Ymd") . sprintf("%05d", 1);
            exit();
        }
    }

    public function movementAjax() {
        $type = $this->input->get("type");
        $value1 = $this->input->get("val1");
        $value2 = $this->input->get("val2");
        $value3 = $this->input->get("val3");
		        
        if ($type == "top_movement") {
            $value1 = convertDate($value1, "eng", "iso", "-");
            $value2 = convertDate($value2, "eng", "iso", "-");
        } else if ($type == "product_code" || $type == "location_code") {
        	if (($value1 > $value2) && $value2 != "") {
        		// SWAP
        		//list($value1,$value2) = array($value2,$value1); // Less efficient
        		// Disable for temp because it must correct params
        		/*
        		$tmp = $value2;
        		$value2 = $value1;
        		$value1 = $tmp;
        		*/
        	}
        }
		
        $listMovement = $this->ctm->countingCriteriaMovementList($type, $value1, $value2, $value3);
        // p($listMovement);
        $json["aaData"] = array();
        $i = 0;
        foreach ($listMovement as $row) {
            $json["aaData"][] = array(
                //$row->Id
                $row->Product_Code
                , thai_json_encode($row->Product_NameEN)
                , $row->Location_Code
                //, $row->QTY
                // , $row->Product_Id
                // , $row->Actual_Location_Id
                // , $row->product_name
                // , $row->actual_location
                    //, $row->Item_From
                    //, $row->Item_From_Id
                    //, $row->Product_Status
                    //, $row->Product_Sub_Status
                    //, $row->Product_License
                    //, $row->Product_Mfd
                    //, $row->Product_Exp
                    //, $row->Unit_Id
                    //, $row->Lot
                    //, $row->Serial
                   
            );
            $i++;
            // p($row);
        }
        // p($json);exit;
        echo json_encode($json);
         //$this->receiveDateExportExcel($type,$listMovement);

        //header("Content-type: application/json");
      
        // echo json_encode($json);
   
    }


    function exportExcelReceive(){

                $receive = $this->input->post();

                if(empty($receive)){
                    $receive = $this->input->get();
                }
                $from_date = $receive['from_date']; 
                $to_date = $receive['to_date']; 

                $tmp = $this->ctm->export_excel($from_date,$to_date);
                
                $view['file_name'] = 'Counting_Receive_'. date('Ymd-His');
                $view['header'] = array(
                                        _lang('Receive Date')
                                        , _lang('Doc Refer_Ext')
                                        , _lang('Product Code')
                                        , _lang('Product NameEN')
                                        , _lang('Product Lot')
                                        , _lang('Product Mfd')
                                        , _lang('Product Exp')
                                        , _lang('Pallet Code')
                                        , _lang('Location Code')
                                        , _lang('Balance Qty')
                                        , _lang('UOM')
                                        
                                    );
                $view['body'] = $tmp;
                //  p($view);exit;
                $this->load->view('report/export_counting_receive_date_excel',$view);
            
    }

    public function criteriaCountSelectedAjax() {
        $selected_chk = $_REQUEST["selected_chk"];
        //echo $selected_chk;exit();
        $listMovement = $this->ctm->countingCriteriaMovementListStep2($selected_chk);
        $json["aaData"] = array();

        $i = 0;
        foreach ($listMovement as $row) {
            $json["aaData"][] = array(
                ($i + 1) . " <input type='hidden' name='countingCriteriaId[]' value=" . $row->Id . " id='chkCountingCriteria" . $row->Id . "'/>",
                $row->Product_Code,
                thai_json_encode($row->Product_NameEN),
                $row->Location_Code,
                set_number_format($row->QTY),
                $row->Id,
                $row->Actual_Location_Id,
                $row->Lot,
                $row->Serial,
                $row->Product_Mfd,
                $row->Product_Exp,
                $row->Pallet_Code,
            );
            $i++;
        }
        header("Content-type: application/json");
        echo json_encode($json);
    }

    public function criteriaCountSelected() {
        $selected_chk = $_REQUEST["selected_chk"];
        $listMovement = $this->ctm->countingCriteriaMovementListShow($selected_chk);
        //p($listMovement);exit();
        $json["aaData"] = array();
        $i = 0;
        foreach ($listMovement as $row) {
            $json["aaData"][] = array(
                ($i + 1) . " <input type='hidden' name='countingCriteriaId[]' value=" . $row->Id . " id='chkCountingCriteria" . $row->Id . "'/>",
                $row->Product_Code,
                thai_json_encode($row->Product_NameEN),
                $row->Lot,
                $row->Serial,
                $row->Product_Mfd,
                $row->Product_Exp,
		$row->Product_Status,
            	$row->Location_Code,
                $row->Pallet_Code,      
            	$row->booked,
            	$row->dispatch,        
                $row->Id,
                $row->Actual_Location_Id,
                set_number_format($row->QTY),
                set_number_format($row->Confirm_Qty),
            );
            $i++;
        }
        //header("Content-type: application/json");
        echo json_encode($json);
    }

    //step 2 section 
    public function countingCriteriaStep2() {
        //p($_POST);exit();
        $process_id = $_POST['process_id'];
        $present_state = $_POST['present_state'];
        //POST VALUE Intitial 
        $conditionDetail = $_POST["conditionDetail"];
        $chkCountingCriteria = $_POST["checkedElement"];

        //get checked value 
        $selected_chk = implode(",", $chkCountingCriteria);
        // echo $selected_chk;
        if ($conditionDetail == 0) {
            $value1 = $_POST["txtfrom"];
            $value2 = $_POST["txtto"];
        } elseif ($conditionDetail == 1) {
            $value1 = $_POST["selectOperand"];
            $value2 = $_POST["aging"];
        } elseif ($conditionDetail == 2) {
            $value1 = $_POST["date_from"];
            $value2 = $_POST["date_to"];
            $value3 = $_POST["top"];
        } elseif ($conditionDetail == 3) {
            $value1 = $_POST["txtProductCode"];
        }

        if (!isset($value1)) {
            $value1 = "";
        }
        if (!isset($value2)) {
            $value2 = "";
        }
        if (!isset($value3)) {
            $value3 = "";
        }
//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "countingCriteria");  // Button Permission. Add by Ton! 20140131
        //$action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $user_id, $data);
        $str_form = $this->parser->parse('form/countingCriteriaFormStep2', array(
            "process_id" => $process_id
            , "present_state" => $present_state
            , 'conditionDetail' => $conditionDetail
            , 'value1' => $value1
            , 'value2' => $value2
            , 'value3' => $value3
            , 'selected_chk' => $selected_chk
            , 'flow_id' => ''
            , 'total' => count($chkCountingCriteria)
                // , 'data_form' => $data_form
                ), TRUE);
        //echo $selected_chk;
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_action' => $data_form->str_buttun
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'user_login' => $this->session->userdata('username')
        ));
    }

    public function saveEditedRecord() {
        $editedValue = $_REQUEST['value'];
        //Always accepts update(return posted value)
        echo $editedValue;
    }

    #ISSUE 3034 Reject Document 
    #DATE:2014-01-08
    #BY:KIK
    #เพิ่มในส่วนของ reject
    #START New Comment Code #ISSUE 3034 Reject Document 
    #add code for reject

    function rejectAction() {
//         p($this->input->post());exit();
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
            }
            #if data send from form page When get data in post()
        } else {
            $process_id = $this->input->post("process_id");
            $flow_id = $this->input->post("flow_id");
            $present_state = $this->input->post("present_state");
            $action_type = $this->input->post("action_type");
            $next_state = $this->input->post("next_state");
        }

#------------------------------------------------------------
//        $this->db->query("SET TRANSACTION ISOLATION LEVEL SNAPSHOT");
//        $this->db->trans_start();


        $flow_detail = $this->wf->getFlowDetailForCounting($flow_id);
        $data['Document_No'] = $flow_detail[0]->Document_No;

        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
//        p($this->input->post());
//        exit();
        if (!empty($action_id)) {

#if insert data done. when return commit for runing database again
//            $this->db->trans_commit();
            #if data send from list page When refesh page
            if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {
                echo "<script>alert('Delete Criteria Counting Complete.');</script>";
                redirect('countingCriteria', 'refresh');
                #if data send from form page When return data to form page
            } else {
                $json['status'] = "C004";
                $json['error_msg'] = "";
            }
        } else {

#if insert data not done . when return rollback and not use database
//            $this->db->trans_rollback();
            #if data send from list page When refesh page
            if ($this->input->post("process_id") == "" && $this->input->post("flow_id") == "") {
                echo "<script>alert('Delete Criteria Counting not complete. Please check?');</script>";
                redirect('countingCriteria', 'refresh');
                #if data send from form page When return data to form page
            } else {
                $json['status'] = "E001";
                $json['error_msg'] = "Incomplete Data";
            }
        }

        echo json_encode($json);
    }

    function rejectAndReturnAction() {

        $flow_id = $this->input->post("flow_id");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");

        $flow_detail = $this->wf->getFlowDetailForCounting($flow_id);
//        p($flow_detail);exit();
        $process_id = $flow_detail[0]->Process_Id;
        $present_state = $flow_detail[0]->Present_State;
        $order_id = $flow_detail[0]->Order_Id;
        $data['Document_No'] = $flow_detail[0]->Document_No;

#------------------------------------------------------------
//        $this->db->query("SET TRANSACTION ISOLATION LEVEL SNAPSHOT");
//        $this->db->trans_start();


        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
//p($action_id);exit();
        if (!empty($action_id)) {

            #update data order detail 
            $where['Order_Id'] = $order_id;

            $detail_order['Actual_Action_Date'] = NULL;
            $detail_order['Count_By'] = NULL;
            $detail_order['Count_Date'] = NULL;

            $result_orders = $this->cm->updateCounting($detail_order, $where);

            $detail['Confirm_Qty'] = NULL;
            $result_detail = $this->cm->updateCounting_Detail($detail, $where);

            if ($result_orders && $result_detail) {

#if insert data done. when return commit for runing database again
//$this->db->trans_commit();

                $json['status'] = "C005";
                $json['error_msg'] = "";
            } else {

#if insert data not done . when return rollback and not use database
//$this->db->trans_rollback();

                $json['status'] = "E001";
                $json['error_msg'] = "Incomplete Data";
            }
        } else {

#if insert data not done . when return rollback and not use database
//            $this->db->trans_rollback();

            $json['status'] = "E001";
            $json['error_msg'] = "Incomplete Data";
        }

        echo json_encode($json);
    }

    #END New Comment Code #ISSUE 3034 Reject Document 
    
	/**
	 * Export counting 
	 * 
	 */    
    public function exportCounting() {
        
        #Load config (add by kik : 20140723)
        $conf = $this->config->item('_xml');
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];
        
    	$params = $this->input->get();
    	$data = $this->ctm->countingCriteriaMovementListShow($params['f'], $params['o']);

    	if (strtolower($params['t']) == "excel") {
	    	
    		$report_group = array();
	    	$report_val = array();
	    	
			if (!empty($data)) {
	    	
	    		foreach ($data as $k=> $columns) {
	    			unset($report);
		    			$report['Running_No'] = $k+1;
		    			$report['Product_Code'] = $columns->Product_Code;
		    			$report['Product_NameEN'] = $columns->Product_NameEN;
		    			$report['Lot'] = $columns->Lot;
		    			$report['Serial'] = $columns->Serial;	    			
		    			$report['Product_Mfd'] = $columns->Product_Mfd;
		    			$report['Product_Exp'] = $columns->Product_Exp;
		    			$report['Product_Status'] = $columns->Product_Status;
		    			$report['Location_Code'] = $columns->Location_Code;
                                        if($conf_pallet):
                                            $report['Pallet_Code'] = $columns->Pallet_Code;	   
                                        endif;
                                                                             
		    			$report['booked'] = array('align' => 'right', 'value' => set_number_format($columns->booked));
		    			$report['dispatch'] = array('align' => 'right', 'value' => set_number_format($columns->dispatch));	    			
		    			$report['QTY'] = array('align' => 'right', 'value' => set_number_format($columns->QTY));
		    			$report['Confirm_Qty'] = array('align' => 'right', 'value' => set_number_format($columns->Confirm_Qty));
		    			$report_group[] = $report;
	    		}
	    	
	    	}
	    	
	    	$view['file_name'] = 'counting_criteria';
	    	$view['body'] = $report_group;
	    	$view['header'] = array(
		    	_lang('no')
		    	, _lang('product_code')
		    	, _lang('product_name')
		    	, _lang('lot')
		    	, _lang('serial')
		    	, _lang('MFD')
		    	, _lang('EXP')
		    	, _lang('Status')
		    	, _lang('Location_Code')
	    	);
                
                if($conf_pallet):
                    array_push($view['header'],  _lang('Pallet_Code'));
                endif;
	    	    
                    array_push($view['header'],  _lang('Booked'));
                    array_push($view['header'],  _lang('Dispatch'));
                    array_push($view['header'],  _lang('System'));
                    array_push($view['header'],  _lang('Physical'));
                         
                
	    	$this->load->view("excel_template", $view);
    	
    	} else if (strtolower($params['t']) == "pdf") {
    		
    		$this->load->library('pdf/mpdf');
    		$this->load->model("company_model", "company");     #add by kik (24-09-2013)
    		date_default_timezone_set('Asia/Bangkok');
    		    		
    		$view['data'] = $data;
    		
    		// Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
    		$this->load->model("user_model", "user");
    		$detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
    		$printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
    		$view['printBy'] = $printBy;
    		// END Add By Akkarapol, 12/09/2013, เพิ่มฟังก์ชั่นการเรียกข้อมูล ชื่อ-สกุล ของคนที่ทำการออก report
    		
    		$this->load->view("report/export_counting_criteria_pdf", $view);    		
    		
    	}
    }
}
