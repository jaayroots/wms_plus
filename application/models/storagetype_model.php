<?php // Create by Ton! 20130424  ?>
<?php

class Storagetype_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getstorageTypeList() {// get storageType for display in Dropdown list.
//        Select for Table STK_M_Storage_Type
        $this->db->select("STK_M_Storage_Type.StorageType_Id, STK_M_Storage_Type.StorageType_NameEn");
        $this->db->from("STK_M_Storage_Type");
        $this->db->where("STK_M_Storage_Type.Active", ACTIVE);
        $query = $this->db->get();
        return $query;
    }

    function getstorageTypeAll() {// get all storageType for display list(datatable).
//        Select for Table STK_M_StorageType
        $this->db->select("STK_M_Storage_Type.StorageType_Id AS Id, STK_M_Storage_Type.StorageType_NameEn
            , STK_M_Storage_Type.StorageType_NameTh, STK_M_Storage_Type.StorageType_DescEn
            , STK_M_Storage_Type.StorageType_DescTh");
        $this->db->from("STK_M_Storage_Type");
        $this->db->where("storage_Type.Active", ACTIVE);
        $query = $this->db->get();
        return $query;
    }

}

?>
