<?php

class putaway_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getPutawayList($warehouseId = '', $zoneId = '', $CategoryId = '', $prodId = '', $Status = '') {
//    function getPutawayList() {
        #select all Product location
//        $sql = "SELECT Id,pl.Location_Id
//                ,(SELECT Warehouse_NameEN FROM STK_M_Warehouse WHERE location.Warehouse_Id=Warehouse_Id) AS warehouse
//                ,(SELECT Zone_NameEn FROM STK_M_Zone WHERE location.Zone_Id=Zone_Id) AS zone
//                ,location.Location_Code AS Location_Code
//                ,(SELECT Dom_EN_Desc FROM SYS_M_Domain WHERE Dom_Code=product.ProductCategory_Id AND Dom_Host_Code='PROD_CATE') AS category
//                ,product.Product_Code AS code,product.Product_NameEN AS name,Product_status
//                FROM STK_M_Product_Location as pl,STK_M_Product as product,STK_M_Location AS location
//                WHERE pl.Product_Code=product.Product_Code AND pl.Location_Id=location.Location_Id
//                AND pl.Active='" . ACTIVE . "'
//                ORDER BY pl.Product_Code ASC";
//        $query = $this->db->query($sql);
//        return $query;
//        
        // START Add by Ton! 20130902
        $this->db->select("Id,pl.Location_Id,(SELECT Warehouse_NameEN FROM STK_M_Warehouse WHERE location.Warehouse_Id=Warehouse_Id) AS warehouse
            ,(SELECT Zone_NameEn FROM STK_M_Zone WHERE location.Zone_Id=Zone_Id) AS zone
            ,location.Location_Code AS Location_Code
            ,(SELECT Dom_EN_Desc FROM SYS_M_Domain WHERE Dom_Code=product.ProductCategory_Id AND Dom_Host_Code='PROD_CATE' AND Dom_Active = 'Y') AS category
            ,product.Product_Code AS code,product.Product_NameEN AS name,Product_status");
        $this->db->from("STK_M_Product_Location as pl,STK_M_Product as product,STK_M_Location AS location");
        $this->db->where("pl.Product_Code=product.Product_Code AND pl.Location_Id=location.Location_Id AND pl.Active='" . ACTIVE . "'");
        if ($warehouseId != '') {
            $this->db->where("location.Warehouse_Id", $warehouseId);
        }
        if ($zoneId != '') {
            $this->db->where("location.Zone_Id", $zoneId);
        }
        if ($CategoryId != '') {
            $this->db->where("product.ProductCategory_Id", $CategoryId);
        }
        if ($prodId != '') {
            $this->db->where("product.Product_Id", $prodId);
        }
        if ($Status != '') {
            $this->db->where("pl.Product_status", $Status);
        }
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query;
        // END Add by Ton! 20130902
    }

    function getPutaway() {
        $this->db->select("Dom_ID AS Id,Dom_Code AS Name");
        $this->db->where("Dom_Host_Code", "*");
        $this->db->where("Dom_Active", "Y");
        $this->db->from("SYS_M_Domain");
        $query = $this->db->get();
        return $query;
    }

    function getWarehouseList() {
        $this->db->select("Warehouse_Id,Warehouse_NameEN");
        $this->db->where("Active", ACTIVE);
        $this->db->from("STK_M_Warehouse");
        $this->db->order_by("Warehouse_Id", "ASC");
        $query = $this->db->get();
        $rows = array();
        $i = 0;
        foreach ($query->result() as $row) {
            $rows[$i]['id'] = $row->Warehouse_Id;
            $rows[$i]['name'] = $row->Warehouse_NameEN;
            $i++;
        }
        return $rows;
    }

    function getProductCategoryList($Dom_Code = '') {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "PROD_CATE");
        if ($Dom_Code != '') :
            $this->db->where("Dom_Code", $Dom_Code);
        endif;
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->from("SYS_M_Domain");
        $query = $this->db->get();
        return $query;
        /*

          $this->db->select("Dom_Code AS Name");
          $this->db->where("Dom_Host_Code","PROD_CATE");
          $this->db->where("Dom_Active","1");
          $this->db->from("SYS_M_Domain");
          $this->db->order_by("Dom_Code","ASC");
          $query = $this->db->get();
          $rows=array();
          $i=0;
          foreach ($query->result() as $row){
          $rows[$i]=$row->Name;
          $i++;
          }
          return $rows;
         */
    }

    function getProductStatus() {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "PROD_STATUS");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->from("SYS_M_Domain");
        //$this->db->order_by("Zone_Id","ASC");
        $query = $this->db->get();
        return $query;
    }

    function getZoneList($w) {
        $this->db->select("Zone_Id,Zone_NameEn");
        $this->db->where("Warehouse_Id", $w);
        $this->db->where("Active", ACTIVE);
        $this->db->from("STK_M_Zone");
        $this->db->order_by("Zone_Id", "ASC");
        $query = $this->db->get();
        $rows = array();
        $i = 0;
        foreach ($query->result() as $row) {
            $rows[$i]['id'] = $row->Zone_Id;
            $rows[$i]['name'] = $row->Zone_NameEn;
            $i++;
        }

        return $rows;
    }

    function getProductFromCategory($category, $warehouse, $zone, $zone_cate, $status) {

        //������������Ѻ�ó�����ͧ������ insert �Թ��ҫ��������ǡѹ
        $location = $this->getLocation($warehouse, $zone, $zone_cate);
        if (count($location) != 0) {
            $sql = "SELECT Product_Id,Product_Code,Product_NameEN,Product_NameTH FROM STK_M_Product 
				WHERE Product_Code NOT IN 
					(SELECT Product_Code FROM STK_M_Product_Location WHERE Product_status='" . $status . "' AND Location_Id IN (" . implode(",", $location) . "))
					AND Active='" . ACTIVE . "'";
        } else {
            //$sql="SELECT Product_Id,Product_Code,Product_NameEN,Product_NameTH FROM STK_M_Product WHERE 
            //	Product_status='".$status."' AND Active=1";
            $sql = "SELECT Product_Id,Product_Code,Product_NameEN,Product_NameTH FROM STK_M_Product 
					WHERE Product_Code NOT IN 
					(SELECT Product_Code FROM STK_M_Product_Location WHERE Product_status='" . $status . "' )
					AND Active='" . ACTIVE . "'";
        }

        if ($category != "") {
            $sql.=" AND ProductCategory_Id='" . $category . "'";
        }
        //echo 'sql='.$sql;
        $query = $this->db->query($sql);

        $rows = array();
        $i = 0;
        foreach ($query->result() as $row) {
            $rows[$i]['id'] = $row->Product_Id;
            $rows[$i]['code'] = $row->Product_Code;
            $rows[$i]['name'] = $row->Product_NameEN;
            $i++;
        }

        return $rows;
    }

    function getProductOnLocation($location_id, $status, $category) {
        $sql = "SELECT Product_Id,Product_Code,Product_NameEN,Product_NameTH FROM STK_M_Product
				WHERE Active='" . ACTIVE . "' AND Product_Code NOT IN
					(SELECT Product_Code FROM STK_M_Product_Location WHERE Product_status='" . $status . "'
						AND Location_Id='" . $location_id . "')";
        if ($category != "") {
            $sql.=" AND ProductCategory_Id='" . $category . "'";
        }
        $query = $this->db->query($sql);

        $rows = array();
        $i = 0;
        foreach ($query->result() as $row) {
            $rows[$i]['id'] = $row->Product_Id;
            $rows[$i]['code'] = $row->Product_Code;
            $rows[$i]['name'] = thai_json_encode($row->Product_NameEN);

            $i++;
        }

        return $rows;
    }

    function saveProductToLocation($value) {

        $location = $this->getLocation($value['warehouse'], $value['zone'], $value['zone_cate']);

        if (!array_key_exists('chkBoxVal', $value) || count($value['chkBoxVal']) == 0) {
            //check not have product code 
            return '0';
        } else {

            $row_insert = array();
            $index = 0;
            //echo ' count location = '.count($location);
            $product = array_unique($value['chkBoxVal']);
            //p($product);
            if (count($location) != 0) {
                /* $insert='INSERT INTO STK_M_Product_Location
                  (Product_Id,Product_Code, Product_status, Location_Id, Active) VALUES '; */
                $insert = 'INSERT INTO STK_M_Product_Location
							(Product_Id,Product_Code, Product_status, Location_Id, Active) ';

                $unioun = "";
                foreach ($product as $id) {
                    foreach ($location as $location_id) {
                        /*
                          $index++;
                          if($index!=1){
                          $insert.=',';
                          }
                          $insert.=' SELECT "'.$this->getProductIdByCode($code).'","'.$code.'","'.$value['product_status'].'"
                          ,"'.$location_id.'","'.ACTIVE.'")';
                         */
                        $code = $this->getProductCodeById($id);
                        $insert .= $unioun . " SELECT " . $id . ",'" . $code . "'
												,'" . $value['product_status'] . "','" . $location_id . "','" . ACTIVE . "'";
                        $unioun = " \n UNION ALL ";
                    }
                }
                //echo '<br> insert = '.$insert;
                $this->db->query($insert);
                //$this->db->insert_batch('STK_M_Product_Location', $row_insert); 
                return '1';
            } else {
                return '2';
            }
        }
    }

    function saveToLocation($value) {
        /* save many product to 1 location , value data 
          Array
          (
          [location_id] => 2
          [product_status] => PENDING
          [category] => CATE01
          [chkBoxVal] => Array
          (
          [0] => 1234567890987
          [1] => 1234567890xxx
          )

          )
         */
        if (count($value['chkBoxVal']) != 0) {
            $row_insert = array();
            $index = 0;
            $insert = 'INSERT INTO STK_M_Product_Location
						(Product_Id,Product_Code, Product_status, Location_Id, Active)  ';
            $insert2 = '';
            $product = array_unique($value['chkBoxVal']);
            $unioun = "";
            foreach ($product as $product_id) {
                if ($this->checkProductInLocationByDetail($value['location_id'], $value['product_status'], $product_id) == 0) {
                    $index++;
                    if ($index != 1) {
                        $insert2.=',';
                    }
                    $code = $this->getProductCodeById($product_id);
                    //$insert2.='("'.$product_id.'","'.$code.'","'.$value['product_status'].'","'.$value['location_id'].'","'.ACTIVE.'")';
                    $insert .= $unioun . " SELECT " . $product_id . ",'" . $code . "'
												,'" . $value['product_status'] . "','" . $value['location_id'] . "','" . ACTIVE . "'";
                    $unioun = " \n UNION ALL ";
                }
            }
            $this->db->query($insert);
            /*
              if($insert2!=""){
              $command_insert=$insert.$insert2;
              $this->db->query($command_insert);
              }

              $query_product_location=$this->db->query('SELECT Id FROM STK_M_Product_Location
              WHERE Product_Code="'.$value['chkBoxVal'][0].'"
              AND Product_status="'.$value['product_status'].'"
              AND Location_Id='.$value['location_id']);
              $result_p_l=$query_product_location->result();
              return $result_p_l[0]->Id;
             */
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function checkProductInLocationByDetail($location, $status, $product_id) {
        // check this product have in STK_M_Product_Location
        $this->db->select("Id");
        $this->db->where("Location_Id", $location);
        $this->db->where("Product_status", $status);
        $this->db->where("Product_Id", $product_id);
        $this->db->from("STK_M_Product_Location");
        $query = $this->db->get();
        $result = $query->result();
//        $countrow = $this->db->affected_rows();
        //echo ' count = '.$countrow;
        if (count($result) != 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function getLocation($warehouse_id = '', $zone_id = '', $zone_cate = '') {
        $this->db->select("Location_Id");
        if ($warehouse_id != "") :
            $this->db->where("Warehouse_Id", $warehouse_id);
        endif;
        if ($zone_id != "") :
            $this->db->where("Zone_Id", $zone_id);
        endif;
        if ($zone_cate != "") :
            $this->db->where("Category_Id", $zone_cate);
        endif;
        $this->db->where("Active", ACTIVE);
        $this->db->from("STK_M_Location");
        $query = $this->db->get();
        $rows = array();
        $i = 0;
        foreach ($query->result() as $row) :
            $rows[$i] = $row->Location_Id;
            $i++;
        endforeach;
        return $rows;
    }

//    ----- Comment Out by Ton! Not Used. 20131030 -----
//    function checkProductInLocationById($id) {
//        $location = $this->put->getLocationFromProductLocation($id);
//        $this->db->select("*");
//        $this->db->where("Actual_Location_Id", $location['id']);
//        $this->db->where("Product_Code", $location['product_code']);
//        $this->db->where("Product_Status", $location['product_status']);
//        $this->db->where("(Receive_Qty-(Dispatch_Qty+Adjust_Qty))!=", "0");
//        $this->db->from("STK_T_Inbound");
//        $this->db->get();
//        $countrow = $this->db->affected_rows();
//        if ($countrow > 0) {
//            return TRUE;
//        } else {
//            return FALSE;
//        }
//    }

    function checkProductInLocation($Location_Id = NULL) {
//        $this->db->select("*");
//        $this->db->from("STK_T_Inbound");
//        $this->db->where_in("Actual_Location_Id", $Location_Id);
//        $this->db->where("Balance_Qty!=0");

//        $this->db->select("COUNT(vw_inbound.Inbound_Id) AS Count_Items");
//        $this->db->from("vw_inbound");
//        if (!empty($Location_Id)):
//            $this->db->where_in("vw_inbound.Actual_Location_Id", $Location_Id);
//        endif;
//        $this->db->where("vw_inbound.Est_Balance_Qty > 0");
        
        $this->db->select("COUNT(STK_T_Inbound.Inbound_Id) AS Count_Items");
        $this->db->from("STK_T_Inbound");
        $this->db->where_in("STK_T_Inbound.Actual_Location_Id", $Location_Id);
        $this->db->where("STK_T_Inbound.Balance_Qty > 0");
        
//        $this->db->get();
////        echo $this->db->last_query();exit();
//        $countrow = $this->db->affected_rows();
//        if ($countrow > 0) :
//            return TRUE;
//        else:
//            return FALSE;
//        endif;

        $result = $this->db->get()->result();
        return $result[0]->Count_Items;
    }

    function deleteProductLocation($id = NULL) {// ----- Not Used. -----
        if (!empty($id)):
            $check_product = $this->checkProductInLocation($id);
            if ($check_product == FALSE) :
                $this->db->delete('STK_M_Product_Location', array('Id' => $id));
                return TRUE;
            else :
                return FALSE;
            endif;
        else:
            return FALSE;
        endif;
    }

    function locationInfo($Location_Id) {// Duplicate Function !!
        $this->db->select("Location_Code");
        $this->db->from("STK_M_Location");
        $this->db->where("Location_Id", $Location_Id);
        $query = $this->db->get();
        $result = $query->result();
        return $result[0]->Location_Code;
    }

//    ----- Comment Out by Ton! Not Used. 20131030 -----
//    function getLocationFromProductLocation($id) {
//        $this->db->select("STK_M_Product_Location.Location_Id AS Location_Id,STK_M_Product_Location.Product_Code AS Product_Code,STK_M_Location.Location_Code AS Location_Code,STK_M_Product_Location.Product_status AS Product_status");
//        $this->db->where("STK_M_Product_Location.Id", $id);
//        $this->db->where("STK_M_Product_Location.Location_Id=STK_M_Location.Location_Id");
//        $this->db->from("STK_M_Product_Location,STK_M_Location");
//        $query = $this->db->get();
//        $data = $query->result();
//        //p($data);
//        return array('id' => $data[0]->Location_Id
//            , 'product_code' => $data[0]->Product_Code
//            , 'location_code' => $data[0]->Location_Code
//            , 'product_status' => $data[0]->Product_status
//        );
//    }

    function getWarehouseFromLocation($location_id) {
        $this->db->select("STK_M_Location.Warehouse_Id AS warehouse_id,STK_M_Location.Zone_Id AS zone_id,STK_M_Warehouse.Warehouse_NameEN As warehouse,STK_M_Zone.Zone_NameEn As zone");
        $this->db->where("Location_Id", $location_id);
        $this->db->where("STK_M_Location.Warehouse_Id=STK_M_Warehouse.Warehouse_Id");
        $this->db->where("STK_M_Location.Zone_Id=STK_M_Zone.Zone_Id");
        $this->db->from("STK_M_Location,STK_M_Warehouse,STK_M_Zone");
        $query = $this->db->get();
        $result = $query->result();
        return array('warehouse_id' => $result[0]->warehouse_id
            , 'warehouse' => $result[0]->warehouse
            , 'zone_id' => $result[0]->zone_id
            , 'zone' => $result[0]->zone
        );
    }

    function getProductInbound($action, $location_id = '', $product_code = '') {
        if ($action == "DEL") :
//            $this->db->select("Inbound_Id AS Id,Product_Code,Product_Status,Balance_Qty");
//            $this->db->where("Actual_Location_Id", $location_id);
//            $this->db->where("Product_Code", $product_code);
//            $this->db->where("Balance_Qty!=", "0");
//            $this->db->from("STK_T_Inbound");
            // Edit by Ton! 20131030
            $this->db->select("vw_inbound.Inbound_Id AS Id, vw_inbound.Product_Code, vw_inbound.Product_Status, vw_inbound.Est_Balance_Qty AS Balance_Qty");
            $this->db->from("vw_inbound");
            if ($location_id != ''):
                $this->db->where_in("vw_inbound.Actual_Location_Id", $location_id);
            endif;
            if ($product_code):
                $this->db->where_in("vw_inbound.Product_Code", $product_code);
            endif;
            $this->db->where("vw_inbound.Est_Balance_Qty > 0");
            $result = $this->db->get();
        //$result=$q->result();
        else :
            $sql = "SELECT STK_M_Product_Location.Id AS Id,STK_M_Product_Location.Product_Code
					,STK_M_Product_Location.Product_status,Product_NameEN,SUM(Receive_Qty-(Dispatch_Qty+Adjust_Qty)) as Balance_Qty
				  FROM STK_M_Product_Location 
				    LEFT JOIN STK_M_Product ON STK_M_Product_Location.Product_Code=STK_M_Product.Product_Code
				    LEFT JOIN STK_T_Inbound ON STK_M_Product_Location.Location_Id=STK_T_Inbound.Actual_Location_Id
												AND STK_M_Product_Location.Product_Code=STK_T_Inbound.Product_Code
												AND STK_M_Product_Location.Product_status=STK_T_Inbound.Product_Status
				  WHERE STK_M_Product_Location.Location_Id=$location_id 
				  GROUP BY STK_M_Product_Location.Id 
					,STK_M_Product_Location.Product_Code
					,STK_M_Product_Location.Product_status
					,Product_NameEN
				  ORDER BY Id ASC";
            $result = $this->db->query($sql);
        endif;
        return $result;
    }

    function searchFreeLocation($warehouse_id = NULL, $zone_id = NULL, $cate_id = NULL, $data_location = NULL, $putaway_id = 0, $limit_start = 0, $limit_max = 100) {

        $result_selected = array();
        // Add By Akkarapol, 12/03/2014, เพิ่ม query สำหรับ get ค่าที่ถูกเลือกไว้แล้ว ให้ขึ้นมาแสดงด้วย เพราะถ้าเกิดไอ้ที่เลือกไว้มันไม่ได้อยู่ใน Max Limit มันจะไม่ขึ้นแสดง จึงจำเป็นต้อง query มันขึ้นมาก่อน แล้วค่อยไป merge กับ result ปกติต่อไป
//        if(!empty($data_location)):
//            $this->db->select("DISTINCT STK_M_Location.Location_Id, STK_M_Location.Location_Code");
//            $this->db->from("STK_M_Location");
//            $this->db->join("STK_M_Zone", "STK_M_Zone.Zone_Id = STK_M_Location.Zone_Id AND STK_M_Zone.Active = '" . ACTIVE . "'", "LEFT");
//            $this->db->join("STK_M_Zone_Category", "STK_M_Zone_Category.Zone_Id = STK_M_Zone.Zone_Id AND STK_M_Zone_Category.Active = '" . ACTIVE . "'", "LEFT");
//            $this->db->where_in("STK_M_Location.Location_Id", $data_location);      
//            $this->db->order_by("STK_M_Location.Location_Id");
//            $result_selected = $this->db->get()->result();
//            $this->db->flush_cache();
//        endif;
        // END Add By Akkarapol, 12/03/2014, เพิ่ม query สำหรับ get ค่าที่ถูกเลือกไว้แล้ว ให้ขึ้นมาแสดงด้วย เพราะถ้าเกิดไอ้ที่เลือกไว้มันไม่ได้อยู่ใน Max Limit มันจะไม่ขึ้นแสดง จึงจำเป็นต้อง query มันขึ้นมาก่อน แล้วค่อยไป merge กับ result ปกติต่อไป
        
        ### Edit by Ton! 20140128 ###
        $this->db->select("DISTINCT STK_M_Location.Location_Id, STK_M_Location.Location_Code, STK_M_Location.Putaway_Id");
        $this->db->from("STK_M_Location");
        $this->db->join("STK_M_Zone", "STK_M_Zone.Zone_Id = STK_M_Location.Zone_Id AND STK_M_Zone.Active = '" . ACTIVE . "'", "LEFT");
        $this->db->join("STK_M_Zone_Category", "STK_M_Zone_Category.Zone_Id = STK_M_Zone.Zone_Id AND STK_M_Zone_Category.Active = '" . ACTIVE . "'", "LEFT");
        if (!empty($warehouse_id)) :
            $this->db->where("STK_M_Location.Warehouse_Id", $warehouse_id);
        endif;
        if (!empty($zone_id)) :
            $this->db->where("STK_M_Zone.Zone_Id", $zone_id);
        endif;
        if (!empty($cate_id)) :
            $this->db->where("STK_M_Zone_Category.Category_Id", $cate_id);
        endif;
        $this->db->where("STK_M_Location.Putaway_Id IS NULL ", NULL, FALSE);
//        $this->db->where("STK_M_Location.Putaway_Id IS NULL");
        
        if ($putaway_id > 0) :
            $this->db->bracket('open','where','OR');
            $this->db->where("STK_M_Location.Active", ACTIVE);
            $this->db->where("STK_M_Location.Putaway_Id", $putaway_id);
            $this->db->bracket('close');
        else:
            $this->db->where("STK_M_Location.Active", ACTIVE);
        endif;
        $this->db->order_by("STK_M_Location.Location_Id");

        $this->db->limit($limit_max, $limit_start);
        
        $this->db->order_by('STK_M_Location.Putaway_Id','DESC');

// Comment By Akkarapol, 12/03/2014, Comment การใช้งาน Memcached ทิ้งเพราะว่า ไม่ต้องใช้ Memcached เนื่องจากข้อมูลตรงนี้ต้องใช้แบบ real-time และไปมีผลทำให้ไม่แสดงใน Multi Select ที่ทำ Append ไว้ แล้วจะทำให้ดูเหมือนว่าตัว Multi Select ทำงานผิดพลาด        
//        $query = $this->db->get();
//        $sql = $this->db->return_query(FALSE);
//        $key = md5($sql);
//        $cache = $this->cache->memcached->get($key);
//        if (!$cache) :
//            $result = $this->db->query($sql)->result();
//            $this->cache->memcached->save($key, $result, NULL, 600);
//        else:
//            $result = $cache;
//        endif;
//
// END Comment By Akkarapol, 12/03/2014, Comment การใช้งาน Memcached ทิ้งเพราะว่า ไม่ต้องใช้ Memcached เนื่องจากข้อมูลตรงนี้ต้องใช้แบบ real-time และไปมีผลทำให้ไม่แสดงใน Multi Select ที่ทำ Append ไว้ แล้วจะทำให้ดูเหมือนว่าตัว Multi Select ทำงานผิดพลาด  
        
        $result = $this->db->get()->result();
        $result = array_merge($result_selected,$result);
        $rows = array();
        $i = 0;
        foreach ($result as $row) :
            $rows[$i]['location_id'] = $row->Location_Id;
            $rows[$i]['location_code'] = $row->Location_Code;
            if (in_array($row->Location_Id, $data_location)) :
                $rows[$i]['selected'] = TRUE;
            else :
                $rows[$i]['selected'] = FALSE;
            endif;
            $i++;
        endforeach;
        return $rows;
    }

    function getLocationCode($id) {// Duplicate Function !!
        $this->db->select("Location_Code");
        $this->db->where("Location_Id", $id);
        $this->db->from("STK_M_Location");
        $query = $this->db->get();
        $result = $query->result();
        return $result[0]->Location_Code;
    }

    function getProductIdByCode($code) {// Duplicate Function !!
        $this->db->select("Product_Id");
        $this->db->where("Product_Code", $code);
        $this->db->from("STK_M_Product");
        $query = $this->db->get();
        $result = $query->result();
        return $result[0]->Product_Id;
    }

    function getProductCodeById($id) {// Duplicate Function !!
        $this->db->select("Product_Code");
        $this->db->from("STK_M_Product");
        $this->db->where("Active", ACTIVE);
        $this->db->where("Product_Id", $id);
        $query = $this->db->get();
        $result = $query->result();
        if ($query->num_rows > 0) :
            return $result[0]->Product_Code;
        else :
            return NULL;
        endif;
    }

    function save_STK_M_PUTAWAY($type = NULL, $data = NULL, $where = NULL) {// save PutAway (Insert, Update). Add by Ton! 20131024
        if ($type == 'ist')://insert
            $this->db->insert('STK_M_PUTAWAY', $data);
            $putaway_id = $this->db->insert_id();
            if ($putaway_id > 0):
                return $putaway_id;
            else:
                return FALSE;
            endif;
        elseif ($type == 'upd')://update
            $this->db->where($where);
            $this->db->update('STK_M_PUTAWAY', $data);
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows > 0):
                return TRUE;
            else :
                return FALSE;
            endif;
        endif;
    }

    function update_PUTAWAY_ID($locate_id, $putaway_id) {// update Putaway_Id at Table STK_M_Location. Add by Ton! 20131024
        $this->db->set("Putaway_Id", $putaway_id);
        $this->db->where("Location_Id", $locate_id);
        $this->db->update("STK_M_Location");
        $afftectedRows = $this->db->affected_rows();
        if ($afftectedRows > 0):
            return TRUE;
        else :
            return FALSE;
        endif;
    }

    public function get_putaway_list($Id = NULL, $column = NULL) {
        /*
         * 
          $this->db->select("Id
          ,PROD.Dom_TH_Desc as Status
          ,SUB.Dom_TH_Desc as Sub_Status
          ,Product_Category_Id
          ,CATE.Dom_TH_Desc as cate_name
          ,STK_M_PUTAWAY.Active
          ,CAST(Remarks AS TEXT) AS Remarks
          ,STK_M_Zone.Zone_NameTh as Zone_Name
          ,PutAway_Name,(SELECT Warehouse_NameEN FROM STK_M_Warehouse WHERE STK_M_Zone.Warehouse_Id=Warehouse_Id) AS warehouse
          ");
          $this->db->join("STK_M_Zone", "STK_M_Zone.Zone_Id = STK_M_PUTAWAY.Zone_Id");
          $this->db->join("SYS_M_Domain as PROD", "PROD.Dom_ID = STK_M_PUTAWAY.Product_Status_Id AND PROD.Dom_Host_Code = 'PROD_STATUS' AND PROD.Dom_Active = '" . ACTIVE . "'");
          $this->db->join("SYS_M_Domain as SUB", "SUB.Dom_ID = STK_M_PUTAWAY.Product_Sub_Status_Id AND SUB.Dom_Host_Code = 'SUB_STATUS' AND SUB.Dom_Active = '" . ACTIVE . "'");
          $this->db->join("SYS_M_Domain as CATE", "CATE.Dom_ID = STK_M_PUTAWAY.Product_Category_Id", "LEFT"); //ADD BY POR 2013-11-12 เพิ่มเติมให้หาชื่อ category
          $this->db->where("STK_M_PUTAWAY.Active", ACTIVE);
          $query = $this->db->get("STK_M_PUTAWAY");
          //               p($this->db->last_query()); exit();
          return $query;
         * 
         */

        # Edit by Ton! 20140129 STK_M_PUTAWAY
        $this->db->select("PA.Id AS Id 
             , PA.PutAway_Name
             , ZONE.Warehouse_Id
             , PA.Zone_Id
             , PA.Product_Status_Id
             , PROD.Dom_TH_Desc AS Product_Status
             , PA.Product_Sub_Status_Id
             , PROD_SUB.Dom_TH_Desc AS Product_Sub_Status
             , PA.Product_Category_Id
             , PROD_CATE.Dom_TH_Desc AS Product_Category
             , CAST(PA.Remarks AS TEXT) AS Remarks
             , PA.Active AS Active_Status
             , CASE WHEN PA.Active IN ('Y') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("STK_M_PUTAWAY AS PA");
        $this->db->join("SYS_M_Domain AS PROD", "PROD.Dom_ID = PA.Product_Status_Id AND PROD.Dom_Host_Code = 'PROD_STATUS' AND PROD.Dom_Active = '" . ACTIVE . "'", "LEFT");
        $this->db->join("SYS_M_Domain AS PROD_SUB", "PROD_SUB.Dom_ID = PA.Product_Sub_Status_Id AND PROD_SUB.Dom_Host_Code = 'SUB_STATUS' AND PROD_SUB.Dom_Active = '" . ACTIVE . "'", "LEFT");
        $this->db->join("SYS_M_Domain AS PROD_CATE", "PROD_CATE.Dom_ID = PA.Product_Category_Id AND PROD_CATE.Dom_Host_Code = 'PROD_CATE' AND PROD_CATE.Dom_Active = '" . ACTIVE . "'", "LEFT");
        $this->db->join("STK_M_Zone AS ZONE", "ZONE.Zone_Id = PA.Zone_Id AND ZONE.Active = '" . ACTIVE . "'", "LEFT");
        if ($Id !== NULL):
            $this->db->where("PA.Id", $Id);
        endif;
        $this->db->where("PA.Active", ACTIVE);
        $this->db->order_by("PA.Id");
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query;
    }

    //ADD BY POR 2013-11-11 function สำหรับดึงข้อมูลจาก id ของตาราง putaway
    function getAllDataEdit($id) {
        $this->db->select("
         Warehouse_Id   
        ,STK_M_Putaway.Zone_Id
        ,Product_Status_Id
        ,status.Dom_Code as pro_status
        ,Product_Sub_Status_Id
        ,substatus.Dom_Code as pro_substatus
        ,Product_Category_Id
        ,catestatus.Dom_Code as pro_catestatus
        ,STK_M_Putaway.Active
        ,CAST(Remarks as TEXT) as Remarks
        ,PutAway_Name");
        $this->db->where("Id", $id);
        $this->db->join("STK_M_Zone", "STK_M_Putaway.Zone_Id=STK_M_Zone.Zone_Id");
        $this->db->join("SYS_M_Domain as status", "STK_M_Putaway.Product_Status_Id=status.Dom_ID AND status.Dom_Active = 'Y'");
        $this->db->join("SYS_M_Domain as substatus", "STK_M_Putaway.Product_Sub_Status_Id=substatus.Dom_ID AND substatus.Dom_Active = 'Y'");
        $this->db->join("SYS_M_Domain as catestatus", "STK_M_Putaway.Product_Category_Id=catestatus.Dom_ID AND catestatus.Dom_Active = 'Y'", "LEFT");
        $query = $this->db->get("STK_M_Putaway");
        return $query;
    }

    //function สำหรับดึง location ขึ้นมาจาก putaway_id ที่ต้องการ
    function getLocationByPutaway($id) {
        $this->db->select("Location_Id,Location_Code");
        $this->db->where("Putaway_Id", $id);
        $this->db->where("Active", ACTIVE);
        $query = $this->db->get("STK_M_Location");
        //p($this->db->last_query());
        return $query;
    }

    public function update_to_null($putaway_id = 0) {
        if ($putaway_id > 0) :
            $this->db->set("Putaway_Id", NULL);
            $this->db->where("Putaway_Id", $putaway_id);
            $this->db->where("Active", ACTIVE);
            $this->db->update("STK_M_Location");
            $afftectedRows = $this->db->affected_rows();
            //if ($afftectedRows > 0):
            return TRUE;
        //else :
        //return FALSE;
        //endif;
        else:
            return FALSE;
        endif;
    }

    public function check_before_delete($putaway_id) {
        $this->db->select("COUNT(Location_Id) as countlocation");
        $this->db->where("Putaway_Id", $putaway_id);
        $this->db->where("Location_Id in (select Actual_location_id from STK_T_Inbound)");
        $this->db->from("STK_M_Location");
        $query = $this->db->get();
        return $query;
    }

    public function update_putaway_null($putaway_id) {
        $this->db->set("Active", INACTIVE);
        $this->db->where("Id", $putaway_id);
        $this->db->update("STK_M_Putaway");
        $affected = $this->db->affected_rows();
        if ($affected > 0) :
            return TRUE;
        else :
            return FALSE;
        endif;
    }

    public function rePallet() {

	$array = array(
'633',
'632',
'631',
'630',
'629',
'597',
'602',
'626',
'625',
'605',
'624',
'606',
'623',
'608',
'622',
'609',
'621',
'610',
'618',
'611',
'612',
'417',
'581',
'580',
'579',
'578',
'411',
'574',
'572',
'593',
'643',
'644',
'519',
'595',
'710',
'715',
'743',
'785',
'484',
'473',
'501',
'778',
'524',
'532',
'502',
'698',
'503',
'716',
'711',
'768',
'442',
'438',
'700',
'701',
'504',
'590',
'586',
'703',
'772',
'696',
'567',
'561',
'588',
'769',
'692',
'666',
'773',
'655',
'516',
'523',
'702',
'466',
'645',
'658',
'654',
'707',
'781',
'744',
'727',
'538',
'577',
'505',
'718',
'520',
'528',
'506',
'507',
'777',
'728',
'534',
'647',
'665',
'697',
'784',
'536',
'739',
'646',
'758',
'782',
'439',
'512',
'651',
'783',
'764',
'510',
'746',
'759',
'767',
'652',
'724',
'747',
'455',
'719',
'704',
'760',
'720',
'693',
'522',
'530',
'440',
'172',
'800',
'770',
'541',
'518',
'774',
'434',
'766',
'535',
'450',
'801',
'788',
'799',
'500',
'802',
'725',
'765',
'761',
'726',
'712',
'763',
'545',
'713',
'540',
'775',
'575',
'776',
'573',
'533',
'531',
'803',
'604',
'709',
'762',
'804',
'805',
'443',
'435',
'453',
'514',
'521',
'436',
'452',
'780',
'659',
'437',
'729',
'603',
'721',
'736',
'730',
'722',
'745',
'731',
'499',
'732',
'547',
'741',
'705',
'714',
'529',
'699',
'576',
'706',
'649',
'691',
'656',
'657',
'650',
'648',
'742'
	);
	//$pallet_id = 525;
	foreach ($array as $pallet_id) {
		$query = $this->db->query("EXEC [dbo].[sp_PA_suggestLocation_Pallet] " . $pallet_id);	
		$r = $query->row();
		print_r($r->Location_Id);
	echo "<br>";
		$this->db->query("UPDATE STK_T_Inbound SET Actual_Location_Id = '".$r->Location_Id."'  WHERE Pallet_Id = '".$pallet_id."' AND Active = 'Y' ");
	}
    }

}

?>
