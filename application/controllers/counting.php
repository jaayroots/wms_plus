<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of counting
 *
 * @author Pong-macbook
 */
class counting extends CI_Controller {

    //put your code here
    public function __construct() {
        parent::__construct();
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->helper('form');
        $this->load->model("counting_model", "ctm");
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
            $this->countingList();
        }
    }

    private function countingList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $userLogin = $this->session->userdata("user_id");
        $module = "counting";
        $workflow_id = 3;
        $this->load->model("workflow_model", "flow");

        $chkIsWarehouseAdmin = $this->ctm->checkIsWarehouseAdmin($userLogin = 0);
        $isWarehouseAdmin = count($chkIsWarehouseAdmin);
        $query = $this->flow->getWorkFlowByCounting($workflow_id, $isWarehouseAdmin);
        $receive_list = $query->result();
        #assign to warehouse admin

        if ($isWarehouseAdmin == 1) {
            #Comment 2013-09-10 #1814  not word "Working Day" By POR
            /*
              $column = array("No.", "Document No.", "Status Doc.", "Working Day", "Assign");
             */

            #1814 Date:2013-09-10 Change word from "Working Day" is "Over Due" and Add "Target Date" By POR
            $column = array("No.", "Document No.", "Status Doc.", "Document Type", "Target Date", "Process Day", "Assign"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow รวมทั้งเพิ่ม Target Date เข้าไปด้วยเนื่องจาก query นี้มันไปเชื่อมกับที่หน้า สำหรับ Daily แต่ไม่ได้มาปรับที่หน้านี้
            #End code
        } else {
            #Comment 2013-09-10 #1814  not word "Working Day" By POR
            /*
              $column = array("No.", "Document No.", "Status Doc.", "Working Day");
             */
            #1814 Date:2013-09-10 Change word from "Working Day" is "Over Due" and Add "Target Date" By POR
            $column = array("No.", "Document No.", "Status Doc.", "Document Type", "Target Date", "Process Day"); // Edit By Akkarapol, 16/09/2013, เพิ่ม "Process Day" เข้าไปในตัวแปร $column เพื่อนำไปแสดงที่หน้า flow รวมทั้งเพิ่ม Target Date เข้าไปด้วยเนื่องจาก query นี้มันไปเชื่อมกับที่หน้า สำหรับ Daily แต่ไม่ได้มาปรับที่หน้านี้
            #End code
        }

        $action = array(VIEW, DEL);
        
        // Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย        
        if(($key = array_search(DEL, $action)) !== false) {
            unset($action[$key]);
        }
        // END Add By Akkarapol, 29/04/2014, ทำการ Unset ตัว Delete ของหน้า List ทิ้งไปก่อน เนื่องจากยังหาข้อสรุปของการทำ Token จากหน้า List ไม่ได้ ก็จำเป็นที่จะต้องปิด column Delete ซ่อนไปซะ แล้วหลังจากที่หาวิธีการทำ Token ของหน้า List ได้แล้ว ก็สามารถลบฟังก์ชั่น if ตรงนี้ทิ้งได้เลย
         
        $datatable = $this->datatable->genTableFixColumn($query, $receive_list, $column, "counting/countingFormWithData/", $action, "counting/rejectAction");

        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140124
            , 'menu_title' => 'List of Counting'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
			 ONCLICK=\"openForm('counting','counting/countingForm/','A','')\">"
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
        ));
    }

    public function countingFormWithData() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $user_id = $this->session->userdata("user_id");
        $flow_id = $this->input->post("id");
        $listCounting = $this->ctm->getCountingFlowData($flow_id);

        $flow_detail = $this->wf->getFlowDetailForCounting($flow_id);
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
        
        $data['Document_No'] = $document_no;

//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "counting");  // Button Permission. Add by Ton! 20140131
        //$action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $user_id, $data);
        $str_form = $this->parser->parse('form/countingFormWithData', array(
            "parameter" => form_input('counting_form', 'counting_form')
            , "test_parse" => "test pass parse from controller"
            , "process_id" => $process_id
            , "present_state" => $present_state
            , "listCounting" => $listCounting
            , 'withData' => "false"
            , "flow_id" => $flow_id
            , "token" => register_token($flow_id, $present_state, $process_id)
                ), TRUE);


//      $button_action = ($present_state == 2 ? $data_form->str_buttun : ''); //comment for hide button but add button in libraries/workflow: by kik : 2014-01-09
        $button_action = $data_form->str_buttun;

        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            // Add By Akkarapol, 23/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_receive"></i>'
            // END Add By Akkarapol, 23/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_action' => $button_action
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'user_login' => $this->session->userdata('username')
        ));
    }

    function getcountingMovementList(){
        $type = 'excel';
        $listMovement = $this->ctm->countingMovementList($type);
        foreach ($listMovement as $key => $pallet_list) {
            if(is_null($pallet_list->Pallet_Code) != TRUE){
                $list['data'][] = $pallet_list;
            }
        }
        $date = date("Y-m-d");
        $list['file_name'] = 'Reprint_PalletTag'.$date;
        $list['header'] = array(
            'No.'
            ,'Room'
            ,'Row'
            ,'Column'
            ,'Level'
            ,'Location'
            ,'Pallet Code'
        );

        $this->load->view('report/getcountingMovementListExcel',$list);
    }

    public function reprint_pallet_tag() {
        $this->load->model("company_model", "company");
        $this->output->enable_profiler($this->config->item('set_debug'));
        $parameter['user_id'] = $this->session->userdata('user_id');

        $str_form = $this->parser->parse("report/reprint_pallet_tag", $parameter, TRUE);

        $this->parser->parse('workflow_template', array(
            'state_name' => 'Reprint Pallet Tag '
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmReceive"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')"  />'
        ));
    }
    
    public function product_tag_call() {
        $this->load->model("pallet_model_reprint","pallet_reprint");
        $this->load->model("searchmodel_reprint", "search_reprint");
     
        $search = $this->input->post();

        if(empty($search)){
            $search = $this->input->get();
        }

        if($search['pallet_list']){
            $search['doc_ext'] = $search['pallet_list'];
        }

        $str = str_replace('I',' I',$search['doc_ext']);
        $arr_doc = explode(" ",$str);
        array_shift($arr_doc);
        $pallet_code['doc_ext'] = $arr_doc;

        foreach ($pallet_code['doc_ext'] as $key => $pallet_code_list) {
           
            $pallet_tmp = $this->ctm->getPalletId($pallet_code_list);
            if(!empty($pallet_tmp)){
                $pallet_id[] = $pallet_tmp[0];
            }else{
                    $notFoundPallet.= '\n'.trim($pallet_code_list);

            }
        }
        // $notFoundPallet = substr($notFoundPallet, 0, -1);
        if(!empty($notFoundPallet)){
            echo '<script>alert("Pallet Not Found '.$notFoundPallet.'");</script>'; 
            echo '<script>window.close();</script>'; 
            exit;
        }

        $this->packingList_from_search($pallet_id);

    }

    // function m_sql(){
    //     $data = $this->ctm->get_database();
    //     p($data);
    //     exit;
    // }

    function packingList_from_search($pallet_list) {
        // p('COL');
        // p($pallet_id);
        // exit;
        $conf = $this->config->item('_xml'); // By ball : 20140707
        #load model

        $this->load->model("pallet_model_reprint","pallet_reprint");
        $this->load->model("searchmodel_reprint", "search_reprint");
        $this->load->library('mpdf/mpdf');

        #get data
        // $pallet_id = $this->input->get("pallet_id");
        // p($pallet_id);
        // exit;

        foreach ($pallet_list as $key => $pallet_id) {
        $pallet_id = $pallet_id['Pallet_Id'];
        $pallet_code = $this->pallet_reprint->get_pallet_detail($pallet_id);

        $pallet_code = $pallet_code[0]->Pallet_Code;

        $tmp = $this->search_reprint->get_search_pallet($pallet_code);
        #find all data in pallet and pallet detail
        $pallet_info = $this->pallet_reprint->get_pallet_detail_packing($pallet_id);
        $datas = $this->pallet_reprint->get_packingList_receive($pallet_id);
        $item_explode = $this->pallet_reprint->get_item_explode_from_pallet_id($pallet_id);
	    $pallet_data = $this->pallet_reprint->get_pallet_data_2($pallet_id);
	    $location_code = $pallet_data->Location_Code;
	    $doc_refer_ext = $pallet_data->Doc_Refer_Ext;
    //    p($pallet_data);exit;
//        p($item_explode->result());
        $explode_item = array();
        $last_date = '';
        foreach ($item_explode->result() as $key_explode => $explode):
            if (empty($explode_item[$explode->Item_Id_Receive])):
                $explode_item[$explode->Item_Id_Receive] = $explode->Confirm_Qty;
            else:
                $explode_item[$explode->Item_Id_Receive] += $explode->Confirm_Qty;
            endif;

            $chk_last_date = strtotime($explode->Activity_Date);
            if ($chk_last_date >= $last_date):
                $last_date = $chk_last_date;
            endif;
        endforeach;

        $order_info = array();

        $order_info['Document_No'] = array();
        $order_info['Doc_Refer_Ext'] = array();

        $item_lists = array();
        $sum_all_qty = 0;
        $check_same_unit = true;
        $check_unit = '';
    //    p($datas);
    //    p($explode_item);
    //    p($tmp['item_lists']);exit;

        $datas = $tmp['item_lists'];
        foreach ($datas as $key => $data):
            $datas[$key]['Confirm_Qty'] = $datas[$key]['Balance_Qty'];
	    $product_name = $data['Product_NameEN'];
	    $product_code = $data['Product_Code'];
            if (!in_array($data['Document_No'], $order_info['Document_No'])):
                array_push($order_info['Document_No'], $data['Document_No']);
            endif;

            if (!in_array($data['Doc_Refer_Ext'], $order_info['Doc_Refer_Ext'])):
                array_push($order_info['Doc_Refer_Ext'], $data['Doc_Refer_Ext']);
            endif;
            $item_lists[$data['Product_Code']] = $data['Product_NameEN'];
            $sum_all_qty += $datas[$key]['Confirm_Qty'];
            if ($key != 0):
                if ($check_unit != $data['Unit_Name']):
                    $check_same_unit = false;
                endif;
            endif;
            $check_unit = $data['Unit_Name'];

            $check_item = FALSE;
            foreach ($tmp['item_lists'] as $key_list => $list):
                if ($data['Item_Id'] == $list['Item_Id']):
                    $check_item = TRUE;
                endif;
            endforeach;

//            if(!$check_item):
//                unset($datas[$key]);
//            endif;

        endforeach;

    //    p($datas);
    //    exit;
        #get printby
        $detail = $this->search_reprint->getDetailByUserId($this->session->userdata('user_id'));
        // p($detail);
        // exit;
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        #get revision
        $revision = $this->revision;

        $view['company_code'] = $this->session->userdata('login_code');

        #add data in parameter for send view
	    $view['product_name'] = $product_name;
	    $view['product_code'] = $product_code;
        $view['order_info'] = $order_info;
        $view['pallet_info'] = $pallet_info; //p($pallet_info);exit();
        $view['datas'] = array_values($datas);
	    $view['location_code'] = $location_code;
	    $view['doc_refer_ext'] = $doc_refer_ext;
        $view['printBy'] = $printBy;
        $view['revision'] = $revision;
        $view['statusprice'] = $conf['price_per_unit'];
        $view['status_cont'] = $conf['container'];
        $view['pallet_id'] = $pallet_id;
        $view['item_lists'] = $item_lists;
        $view['sum_all_qty'] = $sum_all_qty;
        $view['check_unit'] = ($check_same_unit ? $check_unit : '');
        $view['version'] = $item_explode->num_rows;
        $view['last_date'] = (!empty($last_date) ? date("d/m/Y", $last_date) : "N/A");
        $view['old_location'] = $this->pallet_reprint->get_location_from_pallet_id($pallet_id);
        $view['server_print'] = (isset($conf['server_print']) ? $conf['server_print'] : FALSE);

        $view_list['list_pallet'][] = $view;
    }
// p($view); exit;
    $this->load->view("report/export_packing_list_2.php", $view_list);
       
    }

    public function countingForm() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $process_id = 3;
        $present_state = 0;

//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "counting");  // Button Permission. Add by Ton! 20140131
        $listMovement = $this->ctm->countingMovementList();
        $str_form = $this->parser->parse('form/countingForm', array(
            "parameter" => form_input('counting_form', 'counting_form')
            , "test_parse" => "test pass parse from controller"
            , "process_id" => $process_id
            , "present_state" => $present_state
            , 'listMovement' => $listMovement
            , 'withData' => "true"
            , 'total' => count($listMovement)
            , 'token' => ''
                ), TRUE);
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form

            // Add By Akkarapol, 23/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_receive"></i>'
            // END Add By Akkarapol, 23/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '" ONCLICK="cancel()">'
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_action' => $data_form->str_buttun
            , 'button_print_tag' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . Export . ' ' . Excel . ' ' . Pallet . '" ONCLICK="exportExcelPallet() ">'
            , 'user_login' => $this->session->userdata('username')
        ));
    }

    public function aasort(&$array, $key) {
        $sorter = array();
        $ret = array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        $array = $ret;
    }

    public function sort_by_length($arrays) {
        $lengths = array_map('count', $arrays);
        asort($lengths);
        $return = array();
        foreach (array_keys($lengths) as $k)
            $return[$k] = $arrays[$k];
        return $return;
    }

    public function assignManJobs($array, $count_array, &$return, $debug = 1) {

        //$return = array();
        $new_array = array();
        $index_flag = 0;

        foreach ($array as $idx => $val) {
            $num = count($val);
            $new_index = '';      // set default new index
            $flag_name = '';      // set default flag name
            $flag_save = false;     // flag for last loop
            $explode = explode('-', $idx); // explode current index
            // if last loop set flag to true for insert
            // else assign new index by explode current index and remove last child
            if (count($explode) == 1) {
                $flag_save = true;
            } else {
                for ($i = 0; $i < count($explode) - 1; $i++) {
                    $new_index .= $explode[$i] . '-';
                }
            }

            // if found child value
            if ($num >= 1) {

                // first let insert if have a group of content 
                $flag = false; // default flag for use if found number of jobs eq number of jobs's man
                foreach ($count_array as $c_index => $c_value) {
                    $c_num = count($c_value);
                    if ($num == $c_num && !$flag) {
                        //if ($flag_save) {
                        foreach ($val as $jindex => $jval) { // foreach data array
                            $return[$c_index][] = $jval;
                            $flag = true;
                            $flag_name = $idx;
                        }
                        //} else {
                        //$return[$c_index][] = $val;
                        //$flag = true;
                        //$flag_name = $idx;							
                        //}						
                        unset($count_array[$c_index]);
                    }
                }

                // if found last loop let insert by self
                if ($flag_save) {

                    foreach ($val as $jindex => $jval) { // foreach data array
                        $flag_save_man = false; // flag for save ont by one 

                        foreach ($count_array as $c_index => $c_value) { // foreach number of jobs per man
                            $man_index = count($c_value);

                            // reset it when empty 
                            if ($man_index == 0) {
                                unset($count_array[$c_index]);
                            }

                            if (!$flag_save_man && $man_index > 0) {

                                $flag_save_man = true;
                                $flag_name = $idx;
                                $return[$c_index][] = $jval;
                                unset($count_array[$c_index][$man_index - 1]);
                            } // end if
                        } // end foreach    					

                        $index_flag++;
                    } // end foreach value loop 
                }
            } // end num

            if ($flag_name == '' && !$flag_save) {
                foreach ($val as $ival => $vval) {
                    $new_array[substr($new_index, 0, -1)][] = $vval;
                }
            }
        }

        if (count($count_array) >= 1) {
            $this->assignManJobs($new_array, $count_array, $return, $debug + 1);
        }

        return $return;
    }

    public function openCountJob() {
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->session->userdata("user_id");
        $counting_type = $this->input->post("counting_type");
        $man_power = $this->input->post("manPower");
        $prod_list = $this->input->post("prod_list");
        $is_urgent = $this->input->post("is_urgent");   //add for ISSUE 3312 : by kik : 20140120\
        //add Urgent for ISSUE 3312 : by kik : 20140120
        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }
        //$work_load = intval(floor($this->input->post("workload")));
        $work_load = intval(ceil(count($prod_list) / $man_power));
        $counting = array(
            'Counting_Type' => $counting_type
            , 'Counting_Desc' => ''
            , 'Estimate_Action_Date' => date("Y-m-d H:i:s")//DAILY
            , 'Working_Day' => 1
            , 'Create_By' => $user_id
            , 'Modified_By' => $user_id
            , 'Create_Date' => date("Y-m-d H:i:s")
            , 'Is_urgent' => $is_urgent
        );

        // 
        // grouping product item by location
        $aorder_detail = array();
        $jobs_by_location = array();
        foreach ($prod_list as $index => $value) {
            $exp = explode(SEPARATOR, $value); // Edit By Akkarapol, 22/01/2014, เปลี่ยนการ explode จากที่เคยใช้ "," ให้เป็น SEPATATOR แทนเนื่องจาก "," นั้นเป็นแค่ text ธรรมดา หากมีการใช้งานตัวนี้จะทำให้การ explode นี้ผิดพลาดได้
            $index_name = '';
            //$jobs_by_location[str_replace('-','',$exp['3'])][] = $exp; // remove dash
            $jobs_by_location[$exp['3']][] = $exp;
            /* $location = explode('-', $exp['3']);
              for ($i = 0; $i < count($location); $i++) {
              $index_name .= $location[$i];
              }
              $jobs_by_location[$index_name . $value['0']][] = $exp; */
        }

        //$this->aasort($jobs_by_location, 'location');
        //Find maximum jobs per man
        $jobs_per_man = array();
        $max_index = 1;
        foreach ($prod_list as $index => $value) {
            $jobs_per_man[$max_index][] = true;
            ($max_index >= $man_power ? $max_index = 1 : $max_index++);
        }

        // Third version
        $man_jobs = array();
        //$man_jobs = $this->assignManJobs($jobs_by_location, $jobs_per_man, $man_jobs);
        //print_r($man_jobs);
        //exit();
        // End third version
        // assign jobs to man by number of job compare with location
        // 1, loop from jobs group
        // 2, loop from item in jobs group
        // 3, assign to man by number of handle jobs per man		
        // Start
        // Second version 

        $man_index = 1;
        foreach ($jobs_by_location as $index => $value) {
            foreach ($value as $jobs_index => $jobs_value) {
                $man_max_jobs = count($jobs_per_man[$man_index]);
                $countnum = isset($man_jobs[$man_index]) ? count($man_jobs[$man_index]) : 0;
                //echo $countnum . " = " . $man_max_jobs . " | " . $man_index . "<br/>";
                if ($countnum < ($man_max_jobs - 1)) {
                    $man_jobs[$man_index][] = $jobs_value;
                } else {
                    $man_jobs[$man_index][] = $jobs_value;
                    $man_index++;
                }
            }
        }
        // End
        // First version assign not include any rule
        // START
        /*
          foreach ($prod_list as $index => $value) {
          $aorder_detail[$max_index][] = $value;
          $jobs_per_man[$max_index][] = true;
          if ($max_index >= $man_power) {
          $max_index = 1;
          } else {
          $max_index++;
          }
          }
          // END
         */


        $flow_arr = array();
        //$tmp_counting = array(); // remove for temporary
        //print_r($man_jobs);
        //exit();
        foreach ($man_jobs as $index => $value) {
//            $docNo = $this->generateCountingDocNo($counting_type); //$doc_type[0]->Counting_Type,$doc_type[0]->Document_No
            $docNo = create_document_no_by_type("CD"); // Add by Ton! 20140428
            $counting['Document_No'] = $docNo; //'CT10000001'
//        	$list_flow_action = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $user_id, $counting);
            $list_flow_action = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $counting); //Edit by Ton! 20131021
            $flow_id = $list_flow_action[0];
            $action_id = $list_flow_action[1];
            $counting['Flow_Id'] = $flow_id;
            $counting_id = $this->stock->getCountingStock($counting); //$this->stock->getCountingStock($counting); //Call Fn to insert and return back counting_id
            //$tmp_counting[] = $counting_id;
            $flow_arr[] = $flow_id;
            foreach ($value as $index2 => $value2) {
                $product_mfd = (empty($value2['14']) ? "NULL" : "'" . $value2['14'] . "'");
                $product_exp = (empty($value2['15']) ? "NULL" : "'" . $value2['15'] . "'");
                $insert_select_query = "INSERT INTO 
        				STK_T_Counting_Detail(Order_Id, Product_Id, Product_Code, Actual_Location_Id, Product_Mfd, Product_Lot, Product_Serial, Reserv_Qty, Item_From, Item_From_Id, Product_Status, Product_Sub_Status, Product_License, Product_Exp, Unit_Id) 
        				Values('" . $counting_id . "', '" . $value2['5'] . "','" . $value2['1'] . "','" . $value2['8'] . "',{$product_mfd},'" . $value2['6'] . "','" . $value2['7'] . "','" . $value2['4'] . "','" . $value2['9'] . "','" . $value2['10'] . "','" . $value2['11'] . "','" . $value2['12'] . "','" . $value2['13'] . "',{$product_exp},'" . $value2['16'] . "')";
                $order_detail_result = $this->ctm->queryFromString($insert_select_query);
            }
        }

        foreach ($flow_arr as $key => $value) :
            $log_counting = array(
                'Man_Power' => $this->input->post("manPower")
                , 'Count_Id' => $value
                , 'Count_Type' => $counting_type
                , 'Workload' => $work_load
                , 'Work_Day' => 1
                , 'Date_Form' => date("Y-m-d H:i:s")
                , 'Date_To' => date("Y-m-d H:i:s")
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
    }

    public function confirmCount() {

        $token = $this->input->post("token");
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

//        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $user_id, $data);
        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021

        $json['status'] = "C002";
        $json['error_msg'] = "";
        echo json_encode($json);
    }

    public function approveCount() {

        $token = $this->input->post("token");
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
                return "CD" . date("Ymd") . sprintf("%05d", $running + 1);
            } else {
                return "CD" . date("Ymd") . sprintf("%05d", 1);
            }
        } else {
            return "CD" . date("Ymd") . sprintf("%05d", 1);
            exit();
        }
    }

    #ISSUE 3034 Reject Document 
    #DATE:2014-01-08
    #BY:KIK
    #เพิ่มในส่วนของ reject
    #START New Comment Code #ISSUE 3034 Reject Document 
    #add code for reject

    function rejectAction() {

        #add condition check data from list page or form page
        $token = $this->input->post("token");

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
                echo "<script>alert('Delete Daily Counting Complete.');</script>";
                redirect('counting', 'refresh');
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
                echo "<script>alert('Delete Daily Counting not complete. Please check?');</script>";
                redirect('counting', 'refresh');
                #if data send from form page When return data to form page
            } else {
                $json['status'] = "E001";
                $json['error_msg'] = "Incomplete Data";
            }
        }

        echo json_encode($json);
    }

    function rejectAndReturnAction() {

        $token = $this->input->post("token");
        $flow_id = $this->input->post("flow_id");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $flow_detail = $this->wf->getFlowDetailForCounting($flow_id);
        $process_id = $flow_detail[0]->Process_Id;
        $present_state = $flow_detail[0]->Present_State;
        $order_id = $flow_detail[0]->Order_Id;
        $data['Document_No'] = $flow_detail[0]->Document_No;

#------------------------------------------------------------
//        $this->db->query("SET TRANSACTION ISOLATION LEVEL SNAPSHOT");
//        $this->db->trans_start();
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

        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
//p($action_id);exit();
        if (!empty($action_id)) {

            #update data order detail 
            $where['Order_Id'] = $order_id;

            $detail_order['Actual_Action_Date'] = NULL;
            $detail_order['Count_By'] = NULL;
            $detail_order['Count_Date'] = NULL;

            $result_orders = $this->ctm->updateCounting($detail_order, $where);

            $detail['Confirm_Qty'] = NULL;
            $result_detail = $this->ctm->updateCounting_Detail($detail, $where);

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

}
