<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
  #1757
  เพิ่ม libraries/suggestLocation.php ใช้สำหรับดึงค่าของ stored procedure
  มี 2 function
  1.getSuggestLocation return ค่าเป็น object
  2.getSuggestLocationArray return ค่าเป็น array
  โดยในแต่ละ function จะมีเช็ค $type เพื่อแยกการใช้งาน
  1.suggestLocation แนะนำ location ที่จะวาง
  2.returnLocation  หา location ที่มีของวางในชั้นที่อายุการวางน้อยที่สุด และจำนวนคงเหลือที่สามารถวางได้
 */

class Suggest_location {

    function __construct() {
        
    }

    function getSuggestLocation($type, $product_code, $product_status, $used_rule) {
        $CI = & get_instance();
//        $CI->load->database();
        switch ($type) {
            case "suggestLocation":  // location ที่แนะนำให้วาง
                $sql = "EXEC sp_PA_suggestLocation ?,?,?";
                $parameter = array(
                    "@product_code" => $product_code
                    , "@product_status" => $product_status
                    , "@rule" => $used_rule
                );
                $query = $CI->db->query($sql, $parameter);
                $result = $query->result();
                //print '<pre>';
                //print_r($result);
                //print '</pre>';
                return $result;
                break;
            case "returnLocation":  // location ที่สามารถวางของกลับไปได้
                $sql = "EXEC sp_PA_suggestReturnLocation ?,?";
                $parameter = array(
                    "@product_code" => $product_code
                    , "@product_status" => $product_status
                );
                $query = $CI->db->query($sql, $parameter);
                $result = $query->result();
                //print '<pre>';
                //print_r($result);
                //print '</pre>';
                return $result;
                break;
            case "pickup":
                $sql = "EXEC sp_PK_suggestLocation ?,?";
                $parameter = array(
                    "@product_code" => $product_code
                    , "@product_status" => $product_status
                );
                $query = $CI->db->query($sql, $parameter);
                $result = $query->result();
                return $result;
                break;
        }
    }

    # add parameter $prodCate, $prodSubStatus, $prodQty by kik (16-09-2013)

    function getSuggestLocationArray($type, $product_code, $product_status, $used_rule, $prodCate = '', $prodSubStatus = '', $prodQty = 0, $id, $type_item = 1,$pallet_data =null) { // Edit By Akkarapol, 17/09/2013, ใส่ค่าของ $prodCate, $prodSubStatus, $prodQty ให้เป็น $prodCate='', $prodSubStatus='', $prodQty=0 เพราะมันเรียกใช้หลายที่ และแต่ละที่มันก็ส่งค่ามาโดยไม่มี 3 ตัวนี้ เพราะงั้นต้องเซ็ตค่า Default ให้มันด้วย 
        $prod_Qty = $prodQty;
        $CI = & get_instance();
//        $CI->load->database();
        switch ($type) {
            case "suggestLocation":
                /* COMMENT BY POR 2014-05-27 เปลี่ยนค่าตัวแปร
                $sql = "EXEC sp_PA_suggestLocation ?,?,?,?,?,?";
                $parameter = array(
                    "@product_code" => $product_code
                    , "@product_status" => $product_status
                    , "@rule" => $used_rule
                    , "@category" => $prodCate
                    , "@product_sub" => $prodSubStatus
                    , "@product_qty" => $prod_Qty
                );
                */
                
                //EDIT BY POR 2014-05-27 ส่งตัวแปรไปใหม่แค่ 2 ตัวคือ itemp_id ของ product และ type_item คือตารางที่ต้องการค้นหา itemp_id 1=order_detail,2=relocation_detail
                $sql = "EXEC sp_PA_suggestLocation_pallet ?";
                $parameter = array(
//                    "@id" => $id
//                    , "@type_item" => $type_item
                    "@Pallet_Id" => $pallet_data
                );
                
                
                $query = $CI->db->query($sql, $parameter);
                $result = $query->result();
//                echo $CI->db->last_query(); exit();
                $data = array();
                $i = 0;
                foreach ($result as $row) {
                    $data[$i]['Location_Id'] = $row->Location_Id;
                    $data[$i]['Location_Code'] = $row->Location_Code;
                    if ($used_rule == 1) {
                        $data[$i]['Qty'] = $row->Available_Qty;
                    } else {
                        $data[$i]['Qty'] = "-"; // Edit by Ton 20130627
                    }
                    $i++;
                }
                return $data;
                break;
            case "returnLocation":
                $sql = "EXEC sp_PA_suggestReturnLocation ?,?";
                $parameter = array(
                    "@product_code" => $product_code
                    , "@product_status" => $product_status
                );
                $query = $CI->db->query($sql, $parameter);
                $result = $query->result();
                $data = array();
                $i = 0;
                foreach ($result as $row) {
                    $data[$i]['Location_Id'] = $row->Location_Id;
                    $data[$i]['Location_Code'] = $row->Location_Code;
                    $data[$i]['Qty'] = $row->Available_Qty;
                    $i++;
                }
                //print_r($data);
                return $data;
                break;
            case "pickup":
                $sql = "EXEC sp_PK_suggestLocation ?,?";
                $parameter = array(
                    "@product_code" => $product_code
                    , "@product_status" => $product_status
                );
                $query = $CI->db->query($sql, $parameter);
                $result = $query->result();
                $data = array();
                $i = 0;
                foreach ($result as $row) {
                    $data[$i]['Inbound_Id'] = $row->Inbound_Id;
                    $data[$i]['Document_No'] = $row->Document_No;
                    $data[$i]['Product_Exp'] = $row->Product_Exp;
                    $data[$i]['Product_Id'] = $row->Product_Id;
                    $data[$i]['Product_Lot'] = $row->Product_Lot;
                    $data[$i]['Product_Serial'] = $row->Product_Serial;
                    $data[$i]['Product_Mfd'] = $row->Product_Mfd;

                    $data[$i]['Location_Id'] = $row->Location_Id;
                    $data[$i]['Location_Code'] = $row->Location_Code;
                    $data[$i]['Qty'] = $row->Qty;
                    $i++;
                }
                //print_r($data);
                return $data;
                break;
        }
    }

    #add function getFindLocationArray again for defect 518 : by kik :2013-12-09
//    function getFindLocationArray($type, $product_code=NULL, $product_status=NULL, $product_sub_status=NULL, $lot_serial=0, $location_id,$qty,$limit=0,$inbound_id,$item_id) { //comment by por #1671 ส่งตัวแปร $orderId เพิ่ม 2013-09-17
    function getFindLocationArray($type, $product_code=NULL, $product_status=NULL, $product_sub_status=NULL, $lot=0, $serial=0, $location_id,$qty,$limit=0,$inbound_id,$item_id) { //comment by por #1671 ส่งตัวแปร $orderId เพิ่ม 2013-09-17
        $CI = & get_instance();
//        $CI->load->database();
        switch ($type) {
            case "picking":
                $sql = "EXEC sp_PK_findNewLocation ?,?,?,?,?,?,?,?,?,?"; 
                $parameter = array(
                    "@product_code" => $product_code
                    , "@product_status" => $product_status
                    , "@product_sub_status" => $product_sub_status
//                    , "@lot_serial" => $lot_serial 
                    , "@lot" => $lot
                    , "@serial" => $serial 
                    , "@location_id" => $location_id
                    , "@qty" => $qty            
                    , "@limit" => $limit  
                    , "@inbound_item_id" => $inbound_id 
                    , "@item_id" => $item_id 
                );
                $query = $CI->db->query($sql, $parameter);
//                p($CI->db->last_query());exit();
                $result = $query->result();
                $data = array();
                $i = 0;
                foreach ($result as $row) {
                    $data[$i]['Inbound_Id'] = $row->Inbound_Id;
                    $data[$i]['Product_Exp'] = $row->Product_Exp;
                    $data[$i]['Product_Id'] = $row->Product_Id;
                    $data[$i]['Product_Lot'] = $row->Product_Lot;
                    $data[$i]['Product_Serial'] = $row->Product_Serial;
                    $data[$i]['Product_Mfd'] = $row->Product_Mfd;
                    $data[$i]['Location_Id'] = $row->Location_Id;
                    $data[$i]['Location_Code'] = $row->Location_Code;
                    $data[$i]['Product_Status'] = $row->Product_Status;
                    $data[$i]['Product_Sub_Status'] = $row->Product_Sub_Status;
                    $data[$i]['Qty'] = $row->Qty;
                    $i++;
                }
                return $data;
                break;
        }
        
        #Start comment for defect 518 : by kik  : 2013-12-09
//        function getFindLocationArray($type, $product_code, $product_status, $location_id, $orderId, $Item_Id = '',$limit=0) { //comment by por #1671 ส่งตัวแปร $orderId เพิ่ม 2013-09-17
//        $CI = & get_instance();
//        $CI->load->database();
//        switch ($type) {
//            case "picking":
//                $sql = "EXEC sp_PK_findLocation ?,?,?,?,?,?"; //comment by por #1672 ส่งตัวแปร $orderId เพิ่ม 2013-09-17
//                $parameter = array(
//                    "@product_code" => $product_code
//                    , "@product_status" => $product_status
//                    , "@location_id" => $location_id
//                    , "@orderId" => $orderId //comment by por #1672 ส่งตัวแปร $orderId เพิ่ม 2013-09-17
//                    , "@itemId" => $Item_Id// Add by Ton! 20130925
//                    , "@limit" => $limit            #add for defect 518 : by kik : 20131204
//                );
//                $query = $CI->db->query($sql, $parameter);
//                //p($CI->db->last_query());exit();
//                $result = $query->result();
//                $data = array();
//                $i = 0;
//                foreach ($result as $row) {
//                    $data[$i]['Inbound_Id'] = $row->Inbound_Id;
//                    $data[$i]['Product_Exp'] = $row->Product_Exp;
//                    $data[$i]['Product_Id'] = $row->Product_Id;
//                    $data[$i]['Product_Lot'] = $row->Product_Lot;
//                    $data[$i]['Product_Serial'] = $row->Product_Serial;
//                    $data[$i]['Product_Mfd'] = $row->Product_Mfd;
//                    $data[$i]['Location_Id'] = $row->Location_Id;
//                    $data[$i]['Location_Code'] = $row->Location_Code;
//                    $data[$i]['Qty'] = $row->Qty;
//                    $i++;
//                }
//                return $data;
//                break;
//        }
        #end comment for defect 518 : by kik  : 2013-12-09
    }

}