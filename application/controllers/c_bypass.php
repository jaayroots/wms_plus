<?php

/**
 * Bypass
 * FILENAME : c_bypass.php
 * TYPE : Controller
 * AUTHOR : SDDF
 * DESCRIPTION :
 *
 */
if (!defined('BASEPATH')) :
    exit('No direct script access allowed');
endif;

class c_bypass extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->settings = native_session::retrieve();
//        $this->load->library('excel');
//        $this->load->library('validate_data');
//error_reporting(E_ALL);
        // Load Model        
//        $this->load->model("company_model", "company");
//        $this->load->model("inbound_model", "inbound");
//        $this->load->model("workflow_model", "flow");
//        $this->load->model("stock_model", "stock");
//        $this->load->model("product_model", "prod");
//        $this->load->model("im_ex_model", "imex");
//        $this->load->model("system_management_model", "sys");
//        $this->load->model("api_model", "api");
//        $this->load->model("mail_model", "mail");
        
        // Load Model  
         $this->load->model("bypass_model", "bypass");
         $this->load->model("workflow_model", "flow");
         $this->load->model("stock_model", "stock");
          $this->load->model("product_model", "prod");
//           $isUserLogin = $this->session->userdata();
           
    }

   public function replenishment(){
              //  error_reporting(E_ALL);
           $conf = $this->initConnection();
        //#### 1. check stock data in location pick_face 
            $fill_up_data = $this->check_stock_pick_face();
    //        p($fill_up_data);
            if(empty($fill_up_data)){
                exit();
            }

        //#### 2.clear process 20 , state = 0   
            $this->bypass->reject_state_zero();
            
            
        //#### 2.run create_relocation เพิ่อสร้าง order
            /// use loop for 1master 1 order
            foreach($fill_up_data as $key => $value){
//                $status[$key] = $this->create_relocation($value);
                $status[$key] = $this->create_relocation($value);
            }
    }
    
    //// cal from max in master
    public function check_stock_pick_face($id_master = null){
        // ดึง stock 
        $refill_data = null;
        $data_master = $this->bypass->get_master_tmp_($id_master)->result();

            foreach($data_master as $key => $value){
                    $temp = $this->bypass->get_stock($value->product_code,$value->location_code)->result();
                    if(empty($temp[0]->instock)){
                        $data_master[$key]->in_stock = 0;
                    }else{
                        $data_master[$key]->in_stock = $temp;
                    }
                    if($data_master[$key]->in_stock  <= $data_master[$key]->re_order_point ){
                        $refill_data[$key]->product_code = $data_master[$key]->product_code;
                        $refill_data[$key]->need = $data_master[$key]->max - $data_master[$key]->in_stock ;
                        $locat_id = $this->bypass->get_lo_id_by_code($data_master[$key]->location_code)->result();  
                        $refill_data[$key]->move_to =  $locat_id[0]->Location_Id;
//                        p($locat_id);
                    }
            }
            
         return $refill_data;  
    }
    
    //// get 1 pallet    
    public function check_stock_pick_face_pallet($id_master = null){
        // ดึง stock 
        $refill_data = null;
        $data_master = $this->bypass->get_master_tmp_($id_master)->result();

            foreach($data_master as $key => $value){
                    $temp = $this->bypass->get_stock($value->product_code,$value->location_code)->result();
                    if(empty($temp[0]->instock)){
                        $data_master[$key]->in_stock = 0;
                    }else{
                        $data_master[$key]->in_stock = $temp;
                    }
                    if($data_master[$key]->in_stock  <= $data_master[$key]->re_order_point ){
                        $refill_data[$key]->product_code = $data_master[$key]->product_code;
                        $refill_data[$key]->need = $data_master[$key]->max - $data_master[$key]->in_stock ;
                        $locat_id = $this->bypass->get_lo_id_by_code($data_master[$key]->location_code)->result();  
                        $refill_data[$key]->move_to =  $locat_id[0]->Location_Id;
//                        p($locat_id);
                    }
            }
            
         return $refill_data;  
    }
    
    public function call_for_refill_1pl(){
        $product_code = $this->input->get_post('product_code');
        $master_id = $this->bypass->get_master_by_product($product_code)->result();
        $master_id = $master_id[0]->id;
        $flow_id = $this->call_from_master_list($master_id);
        
//        echo json_encode($flow_id);
    }
    
    public function call_from_master_list($id_for_post = null){
//        error_reporting(E_ALL);
//        echo  $this->session->userdata('user_id') ;
//        echo  'sasdasd' ;
//        exit();
        $id_master = $this->input->get_post('id');
        
        if($id_for_post != null){
            $master_id = $id_for_post;
        }
//        $id_master = 24;
        
        $parameter = $this->check_stock_pick_face_forpl($id_master);
        if(!empty($parameter)){
            foreach($parameter as $key => $value){
                $order_id = $this->create_relocation($value);
            }        
            if(!empty($order_id)){
                $flow_id = $this->bypass->get_flow($order_id)->result();
                $flow_id = $flow_id[0]->Flow_Id;
            }
            
            if(!empty($flow_id)){
                
//                if($id_for_post != null){
//                    return $flow_id;
//                }
//                else{
                    echo json_encode($flow_id);
//                }
            }
            else{
                echo json_encode(FALSE);
            }
        }
        else{
            echo json_encode(FALSE);
        }
       
    }
    
    public function check_stock_pick_face_forpl($id_master = null){
        // ดึง stock 
        $refill_data = null;
        $data_master = $this->bypass->get_master_tmp_($id_master)->result();

            foreach($data_master as $key => $value){
                    $temp = $this->bypass->get_stock($value->product_code,$value->location_code)->result();
                    if(empty($temp[0]->instock)){
                        $data_master[$key]->in_stock = 0;
                    }else{
                        $data_master[$key]->in_stock = $temp;
                    }
//                    if($data_master[$key]->in_stock  <= $data_master[$key]->re_order_point ){
                        $refill_data[$key]->product_code = $data_master[$key]->product_code;
                        $refill_data[$key]->need = 1;//$data_master[$key]->max - $data_master[$key]->in_stock ;
                        $locat_id = $this->bypass->get_lo_id_by_code($data_master[$key]->location_code)->result();  
                        $refill_data[$key]->move_to =  $locat_id[0]->Location_Id;
//                        p($locat_id);
//                    }
            }
            
         return $refill_data;  
    }
    
    public function create_relocation($fill_up_data){
//    p($fill_up_data);  
//    exit();
//        $ar_mock_data[0]->product_code = 'R-173823';
//        $ar_mock_data[0]->need = 20;
//        $ar_mock_data[1]->product_code = 'R-178244';
//        $ar_mock_data[1]->need = 10;

        $ar_mock_data[] = $fill_up_data;
        $check_not_err = TRUE;
        $count_row = 0;
//        p($ar_mock_data);
//        exit();        
    $this->transaction_db->transaction_start(); 
        //## 1.gen doc and pre-data 2.create workflow
        if($check_not_err){
            $order_id = $this->insertOrder();
            if(empty($order_id)){
                $check_not_err = FALSE;
            }
        }
        
        
        if($check_not_err){
            foreach($ar_mock_data as $key => $value){
//                p($value);
                //## 3.find inbound 
                //## 4.create orderdetail reolo
                //## 5.book inbound
                 $item_id[$key] = $this->insertOrderDetail($order_id, $value->product_code, $value->need ,$value->move_to );
                 if(!empty($item_id[$key])){
                     $count_row = $count_row + 1 ;
                 }
                //## 6.add detail               
            }
       
            if($count_row == 0){
                // ไม่ได้สักแถว
                $check_not_err = false;
            }
            
        }
//    $this->transaction_db->transaction_rollback(); 
        if($check_not_err){
//            p('commit');
             $this->transaction_db->transaction_commit(); 
             return $order_id;
        }
        else{
//            p('rollback');
             $this->transaction_db->transaction_rollback(); 
             return false ;
        }
//        p('end');
    }   
    
    public function insertOrder($DocReferExt = NULL, $estimateDate = NULL, $remark = NULL, $Source_Id = NULL, $Destination_Id = NULL, $Doc_Type = NULL, $Shipper_Id = NULL) {

        $datestring = "%Y-%m-%d %H:%i:%s";
        $time = time();
        $this->load->helper("date");
        $human = mdate($datestring, $time);

        # generate GRN Number
        $document_no = create_document_no_by_type("RPM");  // RPM = Replenishment

        # create Workflow
        $process_id = 20;  /// reLocationProduct
        $present_state = 0;
        $action_type = "Quick Generate Replenishment";
//        $action_type = "Generate Replenishment";
        $next_state = 2;
        $data['Document_No'] = $document_no;
        list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data);

        $queryProcess = $this->flow->getProcessDetailByProcessId($process_id);
        $process_list = $queryProcess->result();
        if ($process_list != "") {
            foreach ($process_list as $processlist) {
                $ProcessType = $processlist->Process_Type;
            }
        }

        $order = array(
            'Flow_Id' => $flow_id
            , 'Doc_Relocate' => $document_no
//            , 'Doc_Refer_Ext' => iconv("UTF-8", "TIS-620//IGNORE", $DocReferExt)
//            , 'Doc_Refer_Int' => iconv("UTF-8", "TIS-620//IGNORE", $DocReferExt)
            , 'Process_Type' => $ProcessType   // normal = TRN-STOCK , key-in transfer = OUTBOUND
            , 'Doc_Type' => $Doc_Type
            , 'Owner_Id' => -1 // -1 wait check
//            , 'Owner_Id' => 5 //fixed Rajavithi //$Shipper_Id//$this->session->userdata('owner_id')   // w8 check
            , 'Renter_Id' => $Source_Id//$this->session->userdata('renter_id') // w8 check
            , 'Estimate_Action_Date' => $estimateDate
//            , 'Destination_Id' => $Destination_Id
//            , 'Source_Id' => $Shipper_Id
//            , 'Source_Type' => 'Supplier'
//            , 'Destination_Type' => 'Owner'
            , 'Create_By' => $this->session->userdata('user_id')
            , 'Create_Date' => $human
            , 'Remark' => $remark
            , 'Assigned_Id' => $this->session->userdata('user_id') 
        );

        $orderID = $this->bypass->addOrder($order);

        return $orderID;
    }
    
    
    function insertOrderDetail($orderID, $prodCode, $reservQty ,$move_to_lo_code) {
        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $item_id = 0;
        
        $query_inb = $this->bypass->find_inbound($prodCode);
        $order_detail = array();
        
        
       
        foreach ($query_inb->result() as $key_result => $result):
            $detail = array();
            $detail['Order_Id'] = $orderID;
//            $detail['Document_No'] = $orderID;
            $prodId = $this->prod->getProductIDByProdCode($prodCode);
            $unit_id = $this->prod->getUnitIdByProdID($prodId);
            $detail['Product_Id'] = $prodId;
            $detail['Unit_Id'] = $unit_id;
            $detail['Product_Code'] = $prodCode;
            $detail['Product_Lot'] = $result->Product_Lot;//(empty($prodLot) ? NULL : $prodLot);
            $detail['Product_Serial'] = (empty($prodSerial) ? NULL : $prodSerial);

            $detail['Document_No'] = $result->Document_No;
            $detail['Doc_Refer_Int'] = $result->Doc_Refer_Int;
            $detail['Doc_Refer_Ext'] = $result->Doc_Refer_Ext;
            $detail['Doc_Refer_Inv'] = $result->Doc_Refer_Inv;
            $detail['Doc_Refer_CE'] = $result->Doc_Refer_CE;
            $detail['Doc_Refer_BL'] = $result->Doc_Refer_BL;
            $detail['Doc_Refer_AWB'] = $result->Doc_Refer_AWB;
    
            $detail['Product_Status'] = $result->Product_Status;
            $detail['Product_Sub_Status'] = $result->Product_Sub_Status_sort;
            $detail['Product_Serial'] = $result->Product_Serial;
            $detail['Inbound_Item_Id'] = $result->Inbound_Id;
            $detail['Old_Location_Id'] = $result->Actual_Location_Id;
            $detail['Suggest_Location_Id'] = $move_to_lo_code;
            $detail['Actual_Location_Id'] = $move_to_lo_code;
//            Actual_Location_Id , Old_Location_Id
//            $detail['Cont_Id'] = isset($result->Cont_Id) ? $result->Cont_Id : NULL;
            $detail['Product_Mfd'] = $result->Product_Mfd_sort;
            $detail['Product_Exp'] = $result->Product_Exp_sort;
            $detail['Pallet_Id'] = $result->Pallet_Id;
            
            if ($conf_price_per_unit):
                $detail['Price_Per_Unit'] = $prodPrice;
                $detail['Unit_Price_Id'] = $prodUnitPrice;
                $detail['All_Price'] = $reservQty * $prodPrice;
            endif;

            $detail['Product_Status'] = "NORMAL";
            $detail['Product_Sub_Status'] = "SS000";
            $detail['Remark'] = NULL;
//            $detail['Present_State'] = 1;

            $detail['Unit_Price_Id'] = $this->prod->getUnitPriceCodeDefault();
            
            
//            if ($result->Est_Balance_Qty < $reservQty):
                $detail['Reserv_Qty'] = $result->Est_Balance_Qty;
//            else:
//                $detail['Reserv_Qty'] = $reservQty;
//            endif;
            $reservQty -= $detail['Reserv_Qty'];

//            $detail['Unit_Price_Id'] = $prodUnitPrice;
            $order_detail[] = $detail;
            $item_id = $this->bypass->addOrderDetailByOneRecord($detail);

            if ($item_id <= 0):
                $check_not_err = FALSE;
            endif;

            if ($reservQty == 0):
                break;
            endif;
        endforeach;
        
        //กรณีไม่พอ เท่าที่เหลือ
//        if ($reservQty != 0):
//            $return_text = 'Sorry, SKU:'.$prodCode.' Lot:'.$prodLot.' Stock Still Missing '.$reservQty.' Pcs';
//            return $return_text; 
//        endif;

        
////// open  book
        $result_PD_reserv_qty = $this->bypass->reservPDReservQtyArray($order_detail);
        if (!$result_PD_reserv_qty):
            $check_not_err = FALSE;
        endif;
////// open  book
        
        if ($item_id != "") {
            return $item_id;
        } else {
            return NULL;
        }
    }
    
    private function initConnection() {

		$conf_xml_name = 'ecolab.xml';
                $config_database = APPPATH . 'config/database/' . $conf_xml_name;
        
                // IF enable cryptography
                if ($this->config->item('cryptography')) :
                    $encode_data = read_file($config_database);
                    $decode_data = $this->cryptography->DecryptSource($encode_data);
                    $objXML = simplexml_load_string($decode_data);
                else :
                    $objXML = simplexml_load_file($config_database);
                endif;
        
                // read xml data
                $_xml = $this->xml2array($objXML);
                $xml = $_xml['body'];
        
                // Load Database Configuration
                $config['hostname'] = $xml['db']['hostname'];
                $config['username'] = $xml['db']['username'];
                $config['password'] = $xml['db']['password'];
                $config['database'] = $xml['db']['database'];
                $config['dbdriver'] = "mssql";
                $config['dbprefix'] = "";
                $config['pconnect'] = FALSE;
                $config['db_debug'] = TRUE;
                $config['cache_on'] = TRUE;
                $config['cachedir'] = "";
                $config['char_set'] = "tis620";
                $config['dbcollat'] = "Thai_CI_AS";
        
                $this->load->database($config);
                return $xml;
    }
    
    private function xml2array($xml) {
        $arr = array();
        foreach ($xml as $element) :
            $tag = $element->getName();
            $e = get_object_vars($element);
            if (!empty($e)) :
                $arr[$tag] = $element instanceof SimpleXMLElement ? $this->xml2array($element) : $e;
            else :
                if (trim($element) === "TRUE") :
                    $response = TRUE;
                elseif (trim($element) === "FALSE") :
                    $response = FALSE;
                else :
                    $response = trim($element);
                endif;
                $arr[$tag] = $response;
            endif;
        endforeach;
        return $arr;
    }
}
