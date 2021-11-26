<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//Defect ID : 290 #1, Ak, ปัญหาของการใส่ remark จาก HH แล้วเก็บข้อมูลไม่ครบ เป็นเพราะว่าการส่งข้อมูลมันเป็นแบบ GET ผ่านตัว curl มันก็เลยเอาค่าไปใช้งานต่อไม่ได้ จึงใช้วิธี เปลี่ยน ช่องว่างให้เป็น __ ก่อนแล้วค่อยเปลี่ยนกลับอีกทีตอนก่อนเซฟ
class cross_site extends CI_Controller {

    public $settings;       //add by kik : 20140114

    function __construct() {
        parent::__construct();
        $this->settings = native_session::retrieve();   //add by kik : 20140114

        $this->load->model("location_model", "loc");
        $this->load->model("warehouse_model", "wh");
        $this->load->model("zone_model", "zone");
        $this->load->model("storage_model", "stor");
        $this->load->model("putaway_model", "put");
        $this->load->model("workflow_model", "flow");
        $this->load->model("contact_model", "contact");
        $this->load->model("system_management_model", "sys");
        $this->load->model("stock_model", "stock");
        $this->load->model("encoding_conversion", "encode");
    }

    function openAction() {
        $flow_id = $this->input->get_post('id');
        $table = $this->input->get_post('table');
        $flow_detail = $this->flow->getFlowDetail($flow_id, $table); // Edit by Ton! 20130723 Add $table
        $process_id = "";
        $present_state = "";
        foreach ($flow_detail as $value) :
            $process_id = $value->Process_Id;
            $present_state = $value->Present_State;
        endforeach;
//         echo $value->Process_Id; return;
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        echo json_encode($data_form);
    }

    function genGRNNO() {// Add by Ton! 20130625
//        $document_no = createGRNNo();
        $document_no = create_document_no_by_type("GRN"); // Add by Ton! 20140428
        echo json_encode($document_no);
    }

    function genRELNO() {// Add by Ton! 20130625
//        $document_no = createReLNo();
        $document_no = create_document_no_by_type("REL"); // Add by Ton! 20140428
        echo json_encode($document_no);
    }

    function findProcess() {
        $process_id = $this->input->get_post('process_id');
        $query = $this->flow->getProcessDetailByProcessId($process_id);
        $result = $query->result();
//        echo $result;
        echo json_encode($result);
    }

    function createNewWorkflow() {// Add by Ton! 20130625
//        $process_id ,$present_state ,$action_type ,$next_state  ,$user_id ,$data
        $process_id = $this->input->get_post('process_id');
        $present_state = $this->input->get_post('present_state');
        $action_type = $this->input->get_post('action_type');
        $next_state = $this->input->get_post('next_state');
//        $user_id = $this->input->get_post('user_id');
        $data['Document_No'] = $this->input->get_post('Document_No');
//        list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $user_id, $data);
        list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data); //Edit by Ton! 20131021
        $result = array('flow_id' => $flow_id, 'action_id' => $action_id);
        echo json_encode($result);
    }

    function updateWorkFlow() {// Add by Ton! 20130520
        $process_id = $this->input->get_post('process_id');
        $flow_id = $this->input->get_post('flow_id');
        $action_type = $this->input->get_post('action_type');
        $present_state = $this->input->get_post('present_state');
        $next_state = $this->input->get_post('next_state');
        $data = $this->input->get_post('data');
        $action_id = $this->flow->updateWorkFlowTrax($process_id, $flow_id, $action_type, $present_state, $next_state, $data);
        //echo json_encode($action_id);
    }

    function updateOrder() {
        $Order_Id = $this->input->get_post('Order_Id');
        $Estimate_Action_Date = $this->input->get_post('Estimate_Action_Date');
        $Actual_Action_Date = $this->input->get_post('Actual_Action_Date');
        $Table = $this->input->get_post('Table');
        $count_by = $this->input->get_post('count_by');
        $count_date = $this->input->get_post('count_date');

        $order = array();
        if ($Estimate_Action_Date != '') :
            $order['Estimate_Action_Date'] = $Estimate_Action_Date;
        endif;
        if ($Actual_Action_Date != '') :
            $order['Actual_Action_Date'] = $Actual_Action_Date;
        endif;
        if ($count_by != '') :
            $order['Count_By'] = $count_by;
        endif;
        if ($count_date != '') :
            $order['Count_Date'] = $count_date;
        endif;
        if ($Table == '') :
            $Table = 'STK_T_Order';
        endif;
        $where = array(
            'Order_Id' => $Order_Id
        );
        $result = $this->stock->_updateOrder($order, $where, $Table);
        echo json_encode($result);
    }

    /**
     * @function updateOrder_Detail use for update order detail from HH
     * @modified by kik : 201403 : for edit structure function from check $var != "" to if(isset($_REQUEST['Item_Id'])):
     */
    function updateOrder_Detail() {

        $order_detail = array();

        if (isset($_REQUEST['Item_Id'])):
            $Item_Id = $_REQUEST['Item_Id'];
        endif;

        if (isset($_REQUEST['Suggest_Location_Id'])):
            $Suggest_Location_Id = $_REQUEST['Suggest_Location_Id'];
            $order_detail['Suggest_Location_Id'] = $Suggest_Location_Id;
        endif;

        if (isset($_REQUEST['Actual_Location_Id'])):
            $Actual_Location_Id = $_REQUEST['Actual_Location_Id'];
            $order_detail['Actual_Location_Id'] = $Actual_Location_Id;
        endif;

        #add for defect 518 : by kik : 20131204
        if (isset($_REQUEST['Product_Status'])):
            $Product_Status = $_REQUEST['Product_Status'];
            $order_detail['Product_Status'] = $Product_Status;
        endif;

        #add for defect 518 : by kik : 20131204
        if (isset($_REQUEST['Product_Sub_Status'])):
            $Product_Sub_Status = $_REQUEST['Product_Sub_Status'];
            $order_detail['Product_Sub_Status'] = $Product_Sub_Status;
        endif;

        #add for defect 518 : by kik : 20131204
        if (isset($_REQUEST['Product_Lot'])):
            $Product_Lot = $_REQUEST['Product_Lot'];
            if ($Product_Lot != "") {
                $order_detail['Product_Lot'] = $Product_Lot;
            } else {
                $order_detail['Product_Lot'] = ""; // กำหนดให้ product lot ที่ส่งค่ามาเป็นช่องว่างให้เป็น "" ในฐานข้อมูล เพราะว่า ค่าที่ cross site ที่มาจาก HH ถ้าเป็นช่องว่างแล้วจะกลายเป็นศูนย์ : by kik : 20140322
            }
        endif;

        #add for defect 518 : by kik : 20131204
        if (isset($_REQUEST['Product_Serial'])):
            $Product_Serial = $_REQUEST['Product_Serial'];
            if ($Product_Serial != "") {
                $order_detail['Product_Serial'] = $Product_Serial;
            } else {
                $order_detail['Product_Serial'] = ""; // กำหนดให้ product serial ที่ส่งค่ามาเป็นช่องว่างให้เป็น "" ในฐานข้อมูล เพราะว่า ค่าที่ cross site ที่มาจาก HH ถ้าเป็นช่องว่างแล้วจะกลายเป็นศูนย์ : by kik : 20140322
            }
        endif;

        #add for defect 518 : by kik : 20131204
        if (isset($_REQUEST['Product_Mfd'])):
            $Product_Mfd = $_REQUEST['Product_Mfd'];
            if ($Product_Mfd != "") {
                $order_detail['Product_Mfd'] = $Product_Mfd;
            } else {
                $order_detail['Product_Mfd'] = NULL;
            }
        endif;

        #add for defect 518 : by kik : 20131204
        if (isset($_REQUEST['Product_Exp'])):
            $Product_Exp = $_REQUEST['Product_Exp'];
            if ($Product_Exp != "") {
                $order_detail['Product_Exp'] = $Product_Exp;
            } else {
                $order_detail['Product_Exp'] = NULL;
            }
        endif;

        if (isset($_REQUEST['Confirm_Qty'])):
            $Confirm_Qty = $_REQUEST['Confirm_Qty'];
            $order_detail['Confirm_Qty'] = str_replace(",", "", $Confirm_Qty);
        endif;

        if (isset($_REQUEST['DP_Confirm_Qty'])):
            $DP_Confirm_Qty = $_REQUEST['DP_Confirm_Qty']; //Add by Ton! 20130726
            $order_detail['DP_Confirm_Qty'] = str_replace(",", "", $DP_Confirm_Qty);
        endif;

        if (isset($_REQUEST['Reason_Code'])):
            $Reason_Code = $_REQUEST['Reason_Code'];
            $order_detail['Reason_Code'] = $Reason_Code;
        endif;

        if (isset($_REQUEST['Reason_remark'])):
            $Reason_remark = $_REQUEST['Reason_remark'];
            //        Defect ID : 290 #1
            //        DATE:2013-08-23
            //        BY : Ak
            //        Desc : เนื่องจากต้องการแก้ defect นี้ จำเป็นต้องเปลี่ยน ช่องว่างให้เป็น _ ก่อนเพื่อส่งค่ามาให้ได้ จากนั้นถึงต้องมา replace จาก _ มาเป็น ช่องว่างเหมือนเดิม ก่อนทำการเซฟข้อมูล
            $Reason_remark = str_replace('__', ' ', $Reason_remark);
            //        END Defect ID : 290 #1


            $order_detail['Reason_remark'] = $Reason_remark;
        endif;

        if (isset($_REQUEST['Inbound_Item_Id'])):
            $Inbound_Item_Id = $_REQUEST['Inbound_Item_Id'];
            $order_detail['Inbound_Item_Id'] = $Inbound_Item_Id;
        endif;

        if (isset($_REQUEST['Activity_Code'])):
            $Activity_Code = $_REQUEST['Activity_Code'];
            $order_detail['Activity_Code'] = $Activity_Code;
        endif;

        if (isset($_REQUEST['Activity_By'])):
            $Activity_By = $_REQUEST['Activity_By'];
            $order_detail['Activity_By'] = $Activity_By;
        endif;

        if (isset($_REQUEST['Activity_Date'])):

            $Activity_Date = $_REQUEST['Activity_Date'];
            //        Defect ID : 290 #1
            //        DATE:2013-08-23
            //        BY : Ak
            //        Desc : เนื่องจากต้องการแก้ defect นี้ จำเป็นต้องเปลี่ยน ช่องว่างให้เป็น _ ก่อนเพื่อส่งค่ามาให้ได้ จากนั้นถึงต้องมา replace จาก _ มาเป็น ช่องว่างเหมือนเดิม ก่อนทำการเซฟข้อมูล
            $Activity_Date = str_replace('__', ' ', $Activity_Date);
            //        END Defect ID : 290 #1

            $order_detail['Activity_Date'] = $Activity_Date;

        endif;


        //ADD BY KIK 2014-01-14 เพิ่มเกี่ยวกับราคา
        if (isset($_REQUEST['Price_Per_Unit'])):
            $Price_Per_Unit = $_REQUEST['Price_Per_Unit'];
            if ($Price_Per_Unit != ""):
                $order_detail['Price_Per_Unit'] = $Price_Per_Unit;
            endif;

        endif;

        if (isset($_REQUEST['Unit_Price_Id'])):
            $Unit_Price_Id = $_REQUEST['Unit_Price_Id'];
            if ($Price_Per_Unit != ""):
                $order_detail['Unit_Price_Id'] = $Unit_Price_Id;
            endif;

        endif;

        if (isset($_REQUEST['All_Price'])):
            $All_Price = $_REQUEST['All_Price'];
            $order_detail['All_Price'] = $All_Price;
        endif;
        //END ADD

        $where = array(
            'Item_Id' => $Item_Id
        );

        $result = $this->stock->updateOrderDetail($order_detail, $where);

        if ($result > 0) {
            echo json_encode("OK");
        } else {
            echo json_encode("NOT_OK");
        }
    }

    function findLocation() {
        $type = $this->input->get_post('type');
        $product_code = $this->input->get_post('Product_Code'); //echo $product_code;return;
        $product_status = $this->input->get_post('Product_Status');
        $location_id = $this->input->get_post('Location_Id');
        $OrderId = $this->input->get_post('Order_Id'); // Add by Ton! 20130918
        $Item_Id = $this->input->get_post('Item_Id'); // Add by Ton! 20130925

        $result = $this->suggest_location->getFindLocationArray($type, $product_code, $product_status, $location_id, $OrderId, $Item_Id); // Add parameter $OrderId by Ton! 20130918
//        echo $this->db->last_query();
        echo json_encode($result);
    }

    #add function updateSTK_T_InboundForPK for defect 518 : by kik : 20131204

    function updateSTK_T_InboundForPK() {
        $old_Inbound_Id = $this->input->get_post('old_Inbound_Id');
        $new_Inbound_Id = $this->input->get_post('new_Inbound_Id');
        $picking_qty = $this->input->get_post('qty');
        $case = $this->input->get_post('case');
        $result = $this->stock_lib->updateInboundForPK($old_Inbound_Id, $new_Inbound_Id, $picking_qty, $case);
//        echo 'test';
//        echo $result;exit();
        if ($result) {
            echo json_encode("OK");
        } else {
            echo json_encode("NOT_OK");
        }
    }

    public function update_relocate_workflow() {
        $flow_id = $this->input->get_post('flow_id');
        $process_id = $this->input->get_post('process_id');
        $present_state = $this->input->get_post('present_state');
        $affected = $this->flow->update_relocation_workflow($flow_id, $process_id, $present_state);
        if ($affected > 0) :
            echo json_encode(TRUE);
        else:
            echo json_encode(FALSE);
        endif;
    }

    public function get_config() {
        $config_database = APPPATH . 'config/database/' . $this->input->post('config');
        $objXML = simplexml_load_file($config_database);
        $_xml = $this->xml2array($objXML);
        $xml = $_xml['body'];
        echo json_encode($xml);
    }

    /**
     * convert simplexmlelement to array
     * @param unknown $xml
     * @return multitype:string Ambigous <multitype:, multitype:string multitype: >
     */
    public function xml2array($xml) {
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

    public function handleContainer() {
        $this->load->model("container_model", "container");
        $params = $this->input->post();

        $container = explode("_", $params['container']);

        $response_return = array();

        foreach ($container as $idx => $value) {

            $response_return[$value] = $this->container->update_time_container($value, "END");

            if ($response_return[$value] == FALSE) {
                die(json_encode("ERROR_WRONG_PARAMS"));
            }
        }

        die(json_encode($response_return));
    }

    public function handleImages() {
        $this->load->model("container_model", "container");

        $params = $this->input->post();
                
        $request_type = $params['request_type']; // new / edit / delete
        $type = $params['type']; // Container Or Item
        $id = (isset($params['id']) ? $params['id'] : "");
        $image_id = (isset($params['image_id']) ? $params['image_id'] : "");
        $data_image = (isset($params['image_data']) ? $params['image_data'] : "");
        $path = (isset($params['path']) ? $params['path'] : "");
//        $path = getcwd() . "/uploads/default/files/";
        $path = getcwd() . $path;
        
        $fileLog = $path . '/handleImagesLog.txt';
        $append = $params;
        unset($append['image_data']);
        $append = "########## " . date('Y-m-d H:i:s') . " ##########\n" . json_encode($append) . "\n#########################################\n\n";
        file_put_contents($fileLog, $append, FILE_APPEND | LOCK_EX);

        switch ($request_type) {

            case "new":
                if (!$id) {
                    die(json_encode(array("result" => "ERROR", "description" => "ID_NOT_FOUND")));
                }

                if (!$type) {
                    die(json_encode(array("result" => "ERROR", "description" => "TYPE_NOT_FOUND")));
                }

                if (!$data_image) {
                    die(json_encode(array("result" => "ERROR", "description" => "DATA_NOT_FOUND")));
                }

                $exp = explode("_", $id);

                $response_return = array();

                foreach ($exp as $idx => $value) {

                    $image_id = generateImageId($value, $type);

                    $tmp_path = "";

                    foreach ($image_id['PATH'] as $i => $v) {
                        $tmp_path .= $v . "/";
                    }

                    $path .= $tmp_path;

                    $data = base64_decode($data_image);

                    $name = $this->file_newname($path, $image_id['FULL'] . ".png");

                    file_put_contents($path . "/" . $name, $data);

                    // UPDATE TIME
                    
                    $this->container->update_time_container($value, "START");

                    $this->container->update_time_container($value, "END");

//                    if ($response == FALSE) {
//                        // ERROR
//                        die(json_encode("ERROR_WRONG_PARAMS"));
//                    }

                    // END UPDATE TIME

                    $response_return[] = str_replace(".png", "", $name);

                }

                echo json_encode(array("result" => "SUCCESS", "image_id" => $response_return));

                break;

            case "update":

                if (!$image_id) {
                    die(json_encode(array("result" => "ERROR", "description" => "IMAGE_ID_NOT_FOUND")));
                }

                if (!$data_image) {
                    die(json_encode(array("result" => "ERROR", "description" => "DATA_NOT_FOUND")));
                }

                $exp = explode("_", $image_id);

                $response_return = array();

                foreach ($exp as $idx => $value) {

                    $res = getImagePath($value);

                    $file_with_full_path = $path . $res['PATH'][0] . "/" . $res['PATH'][1] . "/" . $res['FULL'];

                    $data = base64_decode($data_image);

                    if (!file_put_contents($file_with_full_path, $data)) {

                        // UPDATE TIME

                        $response = $this->container->update_time_container($value, "END");

                        die(json_encode(array("result" => "ERROR", "description" => "DATA_NOT_FOUND")));
                    } else {
                        echo json_encode(array("result" => "SUCCESS", "image_id" => $image_id));
                    }
                    
                }

                break;

            case "delete":

                if (!$image_id) {
                    die(json_encode(array("result" => "ERROR", "description" => "IMAGE_ID_NOT_FOUND")));
                }

                $res = getImagePath($image_id);

                $file_with_full_path = $path . $res['PATH'][0] . "/" . $res['PATH'][1] . "/" . $res['FULL'];

                if (!file_exists($file_with_full_path)) {
                    die(json_encode(array("result" => "ERROR", "description" => "FILE_NOT_EXIST")));
                } else {
                    if (unlink($file_with_full_path)) {
                        echo json_encode(array("result" => "SUCCESS", "image_id" => $image_id));
                    } else {
                        die(json_encode(array("result" => "ERROR", "description" => "FILE_NOT_FOUND")));
                    }
                }

                break;

            case "view":

                if (!$id) {
                    die(json_encode(array("result" => "ERROR", "description" => "ID_NOT_FOUND")));
                }

                if (!$type) {
                    die(json_encode(array("result" => "ERROR", "description" => "TYPE_NOT_FOUND")));
                }

                $exp = explode("_", $id);

                $image_id = generateImageId($exp[0], $type);

                $tmp_path = "";

                foreach ($image_id['PATH'] as $i => $v) {
                    $tmp_path .= $v . "/";
                }

                $path .= $tmp_path . $image_id['FULL'];

                echo json_encode(array("result" => "SUCCESS", "data" => $this->getAllImageInDir($path)));

                break;

            default:
                die(json_encode(array("result" => "ERROR", "description" => "REQUEST_FAILED")));
                break;
        }
    }

    private function file_newname($path, $filename) {

        if ($pos = strrpos($filename, '.')) {
            $name = substr($filename, 0, $pos);
            $ext = substr($filename, $pos);
        } else {
            $name = $filename;
        }

        $list = glob($path . $name . "*.png");

        if (( $num = count($list) - 1 ) == -1) {

            $new_name = $name . "00" . $ext;
        } else {
            $last_file = basename($list[$num]);

            $tmp = explode(".", $last_file);

            $new_index = str_pad(str_replace($name, "", $tmp[0]) + 1, 2, 0, STR_PAD_LEFT);

            $new_name = $name . $new_index . $ext;
        }

        return $new_name;
    }

    private function getAllImageInDir($path) {

        $list = glob($path . "*.png");

        $response = array();

        foreach ($list as $idx => $val) {
            $path = $val;
            list($name, $ext) = explode(".", basename($val));
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $response[$name] = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        return $response;
    }

}
