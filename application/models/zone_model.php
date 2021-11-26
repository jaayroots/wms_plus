<?php // Create by Ton! 20130422                                                                                    ?>
<?php

class zone_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getAllCategoryList($cate_code = NULL) {// get Category // Duplicate Function !!
//        Select for Table SYS_M_Domain (Dom_Host_Code=PROD_CATE)
        $this->db->select("Dom_ID
            , Dom_Code
            , Dom_TH_Desc
            , Dom_EN_Desc");
        $this->db->from("SYS_M_Domain");
        $this->db->where("SYS_M_Domain.Dom_Host_Code", "PROD_CATE");
        if (!empty($cate_code)):
            $this->db->where("SYS_M_Domain.Dom_Code", $cate_code);
        endif;
        $this->db->where("SYS_M_Domain.Dom_Active", ACTIVE);
        $this->db->order_by("SYS_M_Domain.Dom_ID");
        $query = $this->db->get();
        return $query;
    }

    function getZoneCategoryList($zoneID = NULL) {// get Zone_Category for display in Dropdown list. // Duplicate Function !!
//        Select for Table STK_M_Zone_Category, SYS_M_Domain
//        
//        Comment Out by Ton! 20131021
//        $this->db->select("
//		CAST(STK_M_Zone_Category.Id AS INT) as Id 
//		,CAST(STK_M_Zone_Category.Zone_Id AS INT) as Zone_Id 
//		,CAST(STK_M_Zone_Category.Category_Id AS TEXT) as Category_Id 
//		,CAST(SYS_M_Domain.Dom_Host_Code AS TEXT) as Dom_Host_Code 
//		,CAST(SYS_M_Domain.Dom_Code AS TEXT) as Dom_Code 
//		,CAST(SYS_M_Domain.Dom_TH_SDesc AS TEXT) as Dom_TH_SDesc 
//		,CAST(SYS_M_Domain.Dom_EN_Desc AS TEXT) as Dom_EN_Desc 
//		");
//        // $this->db->from("SYS_M_Domain INNER JOIN STK_M_Zone_Category ON SYS_M_Domain.Dom_Code = STK_M_Zone_Category.Category_Id");
//        $this->db->from("SYS_M_Domain");
//        $this->db->join("STK_M_Zone_Category", "SYS_M_Domain.Dom_Code = STK_M_Zone_Category.Category_Id", "left");
//        if ($zoneID != '') {
//            $this->db->where("STK_M_Zone_Category.Zone_Id", $zoneID);
//        }
//        $this->db->where("SYS_M_Domain.Dom_Host_Code", 'PROD_CATE');
//        $this->db->where("STK_M_Zone_Category.Active", ACTIVE);
//        $this->db->order_by("STK_M_Zone_Category.Zone_Id");
        // Edit by Ton! 20131021
        $this->db->select("SYS_M_Domain.Dom_ID
            , SYS_M_Domain.Dom_Code
            , SYS_M_Domain.Dom_TH_Desc
            , SYS_M_Domain.Dom_EN_Desc");
        $this->db->from("SYS_M_Domain");
        $this->db->join("STK_M_Zone_Category", "STK_M_Zone_Category.Category_Id = SYS_M_Domain.Dom_Id", "LEFT");
        $this->db->where("SYS_M_Domain.Dom_Host_Code", 'PROD_CATE');
        $this->db->where("SYS_M_Domain.Dom_Active", ACTIVE);
        $this->db->where("STK_M_Zone_Category.Active", ACTIVE);
        if (!empty($zoneID)) :
            $this->db->where("STK_M_Zone_Category.Zone_Id", $zoneID);
        endif;
        $this->db->order_by("STK_M_Zone_Category.Zone_Id, SYS_M_Domain.Dom_ID");

        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function getZone($zoneID = NULL, $zoneCode = NULL) {// get data for edit.
//        Select for Table STK_M_Zone
        $this->db->select("*");
        $this->db->from("STK_M_Zone");
        if (!empty($zoneID)) :
            $this->db->where("STK_M_Zone.Zone_Id", $zoneID);
        endif;
        if (!empty($zoneCode)) :
            $this->db->where("STK_M_Zone.Zone_Code", $zoneCode);
        endif;
        $query = $this->db->get();
        return $query;
    }

    function check_zone($zone_code = NULL, $zone_id = NULL, $Warehouse_Id = NULL, $Active = FALSE) {// Edit by Ton! 20130814
        $this->db->select("Zone_Id");
        $this->db->from("STK_M_Zone");
        if (!empty($Warehouse_Id)) :
            $this->db->where("Warehouse_Id", $Warehouse_Id);
        endif;
        if (!empty($zone_code)) :
            $this->db->where("Zone_Code", $zone_code);
        endif;
        if (!empty($zone_id)):
            $this->db->where("Zone_Id!=", $zone_id);
        endif;
        if ($Active == TRUE):
            $this->db->where("Active", ACTIVE);
        endif;
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        $result = $query->result();
        if (count($result) > 0) :
            return TRUE;
        else :
            return FALSE;
        endif;
    }

    function getZoneListByWarehouseID($Warehouse_Id = NULL) {// get Zone for display in Dropdown list. // Add by Ton! 20130430// Duplicate Function !!
//        Select for Table STK_M_Zone
        $this->db->select("STK_M_Zone.Zone_Id
            , STK_M_Zone.Zone_NameEn");
        $this->db->from("STK_M_Zone");
        $this->db->where("STK_M_Zone.Active", ACTIVE);
        if (!empty($Warehouse_Id)) :
            $this->db->where("STK_M_Zone.Warehouse_Id", $Warehouse_Id);
        endif;
        $this->db->order_by("STK_M_Zone.Zone_Id");
        $query = $this->db->get();
        return $query;
    }

    function getZoneList() {// get Zone for display in Dropdown list.// Duplicate Function !!
//        Select for Table STK_M_Zone
        $this->db->select("STK_M_Zone.Zone_Id
            , STK_M_Zone.Zone_Code
            , STK_M_Zone.Zone_NameEn");
        $this->db->from("STK_M_Zone");
        $this->db->where("STK_M_Zone.Active", ACTIVE);
        $this->db->order_by("STK_M_Zone.Zone_Id");
        $query = $this->db->get();
        return $query;
    }

    function getZoneAll() {// get data for display list.
//        Select for Table STK_M_Zone, STK_M_Warehouse
        $this->db->select("STK_M_Zone.Zone_Id AS Id
            , STK_M_Warehouse.Warehouse_Code
            , STK_M_Zone.Zone_Code
            , STK_M_Zone.Zone_NameEn
            , STK_M_Zone.Zone_NameTh
            , STK_M_Zone.Zone_Desc
            , CASE WHEN STK_M_Zone.Active IN ('Y') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("STK_M_Zone");
        $this->db->join("STK_M_Warehouse", "STK_M_Zone.Warehouse_Id = STK_M_Warehouse.Warehouse_Id AND STK_M_Warehouse.Active = '" . ACTIVE . "'", "left");
//        $this->db->where("STK_M_Zone.Active", ACTIVE);
        $this->db->order_by("STK_M_Zone.Warehouse_Id", "STK_M_Zone.Zone_Id");
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query;
    }

    function saveDataZoneCategory($Zone_Id = NULL, $value = NULL) {// save Zone_Category 20130426, // add $id=zone_id Edit by Ton! 20130429
        //ADD BY POR 2013-11-13 กำหนดให้ category เป็น null ทั้งหมดก่อน แล้วค่อย update หรือ insert ตามขั้นตอนต่อไป
        $this->db->set("Active", INACTIVE);
        $this->db->where("Zone_Id", $Zone_Id);
        $this->db->update("STK_M_Zone_Category");
        //END ADD
        $result = TRUE;
        if (!empty($value['category_code'])) {
            foreach ($value['category_code'] as $CateId) {// loop save Zone_Category
                $query = $this->db->query("SELECT * FROM STK_M_Zone_Category WHERE Zone_Id = " . $Zone_Id . " AND Category_Id = " . $CateId . "");

                if ($query->num_rows > 0):
                    $this->db->query("UPDATE STK_M_Zone_Category SET Active='" . ACTIVE . "' WHERE Zone_Id = " . $Zone_Id . " AND Category_Id = " . $CateId . "");
                    $afftectedRows = $this->db->affected_rows();
                    if ($afftectedRows <= 0) :
                        $result = FALSE;
                    endif;
                else:
                    $this->db->query("INSERT INTO STK_M_Zone_Category (Zone_Id, Category_Id, Active) VALUES(" . $Zone_Id . ", " . $CateId . ", '" . ACTIVE . "') ");
                    $Id = $this->db->insert_id();
                    if ($Id <= 0) :
                        $result = FALSE;
                    endif;
                endif;
            }
        }

        return $result;
    }

    function saveDataZone($type = NULL, $data = NULL, $where = NULL) {// save Zone (Insert, Update). // Duplicate Function !!
        if ($type == 'ist') :// Insert.
            $this->db->insert('STK_M_Zone', $data);
            $zone_id = $this->db->insert_id();
//            return $zone_id;
//            $query=$this->db->insert('STK_M_Zone',$data);
            if ($zone_id > 0) :
                return $zone_id; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        elseif ($type == 'upd') :// Update.
            $this->db->where($where);
            $this->db->update('STK_M_Zone', $data);
//            echo $this->db->last_query();exit();
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows > 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        endif;
    }

    # Comment Out by Ton! 20140213 Not Used.
//    function deleteDataZone($data, $where) {// delete Zone. // Duplicate Function !!
//        $this->db->where($where);
//        $query = $this->db->update('STK_M_Zone', $data);
//        $afftectedRows = $this->db->affected_rows();
//        if ($afftectedRows >= 0) :
//            return TRUE; // Save Success.
//        else :
//            return FALSE; // Save UnSuccess.
//        endif;
//    }

    function checkZoneLocation($warehouse_id = NULL, $zone_id = NULL) {
//        $this->db->select("Location_Id");
//        $this->db->where("Warehouse_Id", $warehouse_id);
//        $this->db->where("Zone_Id", $zone_id);
//        $this->db->where("Active", ACTIVE);
//        $this->db->from("STK_M_Location");
        // Edit by Ton! 20131021
        $this->db->select("STK_M_Location.*
            , STK_M_Zone.Zone_Code
            , STK_M_Zone.Zone_NameEn");
        $this->db->from("STK_M_Location");
        $this->db->join("STK_M_Zone", "STK_M_Zone.Warehouse_Id = STK_M_Location.Warehouse_Id");
        if (!empty($warehouse_id)) :
            $this->db->where("STK_M_Zone.Warehouse_Id", $warehouse_id);
        endif;
        if (!empty($zone_id)) :
            $this->db->where("STK_M_Zone.Zone_Id", $zone_id);
        endif;
        $this->db->where("STK_M_Zone.Active", ACTIVE);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    function checkZoneCateLocation($warehouse_id = NULL, $zone_id = NULL, $zone_cate = NULL) {
        $this->db->select("Location_Id");
        $this->db->from("STK_M_Location");
        if (!empty($warehouse_id)):
            $this->db->where("Warehouse_Id", $warehouse_id);
        endif;
        if (!empty($zone_id)):
            $this->db->where("Zone_Id", $zone_id);
        endif;
        if (!empty($zone_cate)):
            $this->db->where("Category_Id", $zone_cate);
        endif;
        $this->db->where("Active", ACTIVE);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

}

?>
