<?php // Create by Ton! 20130422                           ?>
<?php

class storage_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

// Comment Out by Ton! Not Used. 20131031
//    function getStorageDetailByStorID($StorID = "") {// Add by Ton! 20130430
////        Select for Table STK_M_Storage
//        $this->db->select("Storage_Detail_Id, Storage_Code");
//        $this->db->from("STK_M_Storage_Detail");
//        if ($StorID != "") {
//            $this->db->where("STK_M_Storage_Detail.Storage_Id", $StorID);
//        }
//        $this->db->where("STK_M_Storage_Detail.Is_Full", 'N');
//        $this->db->order_by("STK_M_Storage_Detail.Storage_Detail_Id");
//        $query = $this->db->get();
//        return $query;
//    }
//    
//    function _get_storage_detail_by_stor_id($warehouse_id = "", $zone_id = "", $stor_Id = "") {
    function _get_storage_detail_by_stor_id($warehouse_id = "", $stor_Id = "") {// Edit by Ton! 20131031
        // Comment Out by Ton! 20130809
//        $sql="SELECT Storage_Detail_Id, Storage_Code
//            FROM STK_M_Storage_Detail
//            WHERE Is_Full='N'
//            AND Storage_Detail_Id NOT 
//                IN (SELECT Storage_Detail_Id FROM STK_M_Location 
//                WHERE Warehouse_Id='".$warehouse_id."' AND Zone_Id='".$zone_id."')
//            ORDER BY Storage_Detail_Id";
//            
        // Edit by Ton! 20130809
        $sql = "SELECT Storage_Detail_Id, Storage_Code
            FROM STK_M_Storage_Detail
            WHERE Storage_Detail_Id 
            IN (SELECT Storage_Detail_Id FROM STK_M_Storage_Detail  
            WHERE Warehouse_Id=" . $warehouse_id . " AND Storage_Id=" . $stor_Id . ")
                AND Storage_Detail_Id NOT IN (SELECT Storage_Detail_Id FROM STK_M_Location) 
                AND STK_M_Storage_Detail.Active = 'Y'
                ORDER BY Storage_Detail_Id";

        $query = $this->db->query($sql);
        return $query;
    }

    function get_storage_code_by_stor_detail_id($StorDetailID = "") {// Add by Ton! 20130430
//        Select for Table STK_M_Storage
        $this->db->select("Storage_Detail_Id, Storage_Code");
        $this->db->from("STK_M_Storage_Detail");
        if ($StorDetailID != "") :
            $this->db->where("STK_M_Storage_Detail.Storage_Detail_Id", $StorDetailID);
        endif;
        $this->db->where("STK_M_Storage_Detail.Is_Full", 'N');
        $this->db->order_by("STK_M_Storage_Detail.Storage_Detail_Id");
        $query = $this->db->get();
        return $query;
    }

    function getStorageByID($StorageID = "") {// get data for edit.
//        Select for Table STK_M_Storage
        $this->db->select("*");
        $this->db->from("STK_M_Storage");
        if ($StorageID != "") :
            $this->db->where("STK_M_Storage.Storage_Id", $StorageID);
        endif;
        $this->db->order_by("STK_M_Storage.Storage_Id");
        $query = $this->db->get();
        return $query;
    }

    function getStorageList($Warehouse_Id = NULL, $Zone_Id = NULL) {// Add by Ton! 20130430
//        Select for Table STK_M_Storage
        $this->db->select("STK_M_Storage.Storage_Id, STK_M_Storage.Storage_NameEn, STK_M_Storage.Storage_NameTh");
        $this->db->from("STK_M_Storage");
        $this->db->where("STK_M_Storage.Active", ACTIVE);
        if ($Warehouse_Id != NULL) :
            $this->db->where("STK_M_Storage.Warehouse_Id", $Warehouse_Id);
        endif;
        if ($Zone_Id != NULL):
            $this->db->where("STK_M_Storage.Zone_Id", $Zone_Id);
        endif;
        $this->db->order_by("STK_M_Storage.Storage_Id");
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function getStorageDetail($StorID = "") {// Add by Ton! 20130502
//        Select for Table STK_M_Storage_Detail
        $this->db->select("*");
        $this->db->from("STK_M_Storage_Detail");
        if ($StorID != '') :
            $this->db->where("STK_M_Storage_Detail.Storage_Id", $StorID);
        endif;
        $this->db->where("STK_M_Storage_Detail.Active", ACTIVE);
        $this->db->order_by("STK_M_Storage_Detail.Storage_Detail_Id");
        $query = $this->db->get();
        return $query;
    }

    function getStorageAll($All = TRUE) {// get all strorage for display list(datatable).
//        Select for Table STK_M_Storage, STK_M_Storage_Type, STK_M_Warehouse
        $this->db->select("dbo.STK_M_Storage.Storage_Id AS Id
            , dbo.STK_M_Warehouse.Warehouse_Code
            , dbo.STK_M_Storage_Type.StorageType_NameEn
            , dbo.STK_M_Storage.Storage_NameEn
            , dbo.STK_M_Storage.Storage_Height
            , dbo.STK_M_Storage.Storage_Width
            , dbo.STK_M_Storage.Storage_Lenght
            , dbo.STK_M_Storage.Storage_Row
            , dbo.STK_M_Storage.Storage_Column
            , dbo.STK_M_Storage.Storage_Level
            , dbo.STK_M_Storage.Location_Height            
            , dbo.STK_M_Storage.Location_Width
            , dbo.STK_M_Storage.Location_Lenght
            , dbo.STK_M_Storage.Max_Capacity
            , CASE WHEN STK_M_Storage.Active IN ('Y') THEN 'YES' ELSE 'NO' END AS Active");
//        $this->db->from("dbo.STK_M_Storage INNER JOIN dbo.STK_M_Storage_Type 
//            ON dbo.STK_M_Storage.StorageType_Id = dbo.STK_M_Storage_Type.StorageType_Id 
//            INNER JOIN dbo.STK_M_Warehouse ON dbo.STK_M_Storage.Warehouse_Id = dbo.STK_M_Warehouse.Warehouse_Id");
        $this->db->from("dbo.STK_M_Storage");
        $this->db->join("dbo.STK_M_Storage_Type", "dbo.STK_M_Storage.StorageType_Id = dbo.STK_M_Storage_Type.StorageType_Id", "INNER");
        $this->db->join("dbo.STK_M_Warehouse", "dbo.STK_M_Storage.Warehouse_Id = dbo.STK_M_Warehouse.Warehouse_Id AND dbo.STK_M_Warehouse.Active = '" . ACTIVE . "'", "INNER");
        if ($All == FALSE):
            $this->db->where("STK_M_Storage.Active", ACTIVE);
        endif;
        $this->db->order_by("STK_M_Storage.Storage_Id");
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query;
    }

    function saveDataStorage($type, $data, $where) {// save Storage (Insert, Update).
        if ($type == 'ist') :// Insert.
            $this->db->insert('STK_M_Storage', $data);
            $Storage_Id = $this->db->insert_id();
            if ($Storage_Id != 0):
                return $Storage_Id; // Save Success.
            else:
                return FALSE; // Save Unsuccess.
            endif;
        elseif ($type == 'upd') :// Update.
            $this->db->where($where);
            $this->db->update('STK_M_Storage', $data);
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        endif;
    }

    function save_STK_M_Storage_Detail($type, $data, $where) {// Add by Ton! 20140515
        if ($type == 'ist') :// Insert.
            $this->db->insert('STK_M_Storage_Detail', $data);
            $Storage_Detail_Id = $this->db->insert_id();
            if ($Storage_Detail_Id != 0):
                return $Storage_Detail_Id; // Save Success.
            else:
                return FALSE; // Save Unsuccess.
            endif;
        elseif ($type == 'upd') :// Update.
            $this->db->where($where);
            $this->db->update('STK_M_Storage_Detail', $data);
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        endif;
    }

    function save_create_storage_detail($data) {// Add by Ton! 20130429
        $this->db->insert('STK_M_Storage_Detail', $data);
        $storage_detail_id = $this->db->insert_id();
        if ($storage_detail_id != '' && $storage_detail_id > 0) :
            return $storage_detail_id; // Save Success.
        else :
            return NULL; // Save UnSuccess.
        endif;
    }

    function deleteDataStorage($data, $where) {// delete Storage. // Duplicate Function !!
        $this->db->where($where);
        $this->db->update('STK_M_Storage', $data);
        $afftectedRows = $this->db->affected_rows();
        if ($afftectedRows > 0) :
            return TRUE; // Save Success.
        else :
            return FALSE; // Save UnSuccess.
        endif;
    }

}

?>
