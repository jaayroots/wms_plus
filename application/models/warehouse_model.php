<?php // Create by Ton! 20130422                           ?>
<?php

class warehouse_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getWarehouseList() {// get warehouse for display in Dropdown list.
//        Select for Table STK_M_Warehouse
        $this->db->select("STK_M_Warehouse.Warehouse_Id
            , STK_M_Warehouse.Warehouse_Code
            , STK_M_Warehouse.Warehouse_NameEN
            , SYS_M_Domain.Dom_EN_Desc AS WH_Type
            , STK_M_Warehouse.Active");
        $this->db->from("STK_M_Warehouse");
        $this->db->join("SYS_M_Domain", "SYS_M_Domain.Dom_ID = STK_M_Warehouse.Warehouse_Type AND SYS_M_Domain.Dom_Active = 'Y'", "LEFT");
        $this->db->where("STK_M_Warehouse.Active", ACTIVE);
        $this->db->order_by("STK_M_Warehouse.Warehouse_Id");
        $query = $this->db->get();
        return $query;
    }

    function getWarehouseAll() {// get all warehouse (where Active) for display in datatable(list warehouse page) .
//        Select for Table STK_M_Warehouse, CTL_M_City
// CASE WHEN STK_M_Warehouse.Active IN ('Y') THEN 'YES' ELSE 'NO' END AS Active
        $this->db->select("STK_M_Warehouse.Warehouse_Id AS Id
            , STK_M_Warehouse.Warehouse_Code
            , STK_M_Warehouse.Warehouse_NameEN
            , STK_M_Warehouse.Warehouse_NameTH
            , STK_M_Warehouse.Warehouse_Desc
            , STK_M_Warehouse.Address
            , CTL_M_City.City_NameEN
            , STK_M_Warehouse.ZipCode
            , SYS_M_Domain.Dom_EN_Desc AS WH_Type
            ");
        $this->db->from("STK_M_Warehouse");
        $this->db->join("CTL_M_City", "STK_M_Warehouse.City_Id = CTL_M_City.City_Id", "LEFT");
        $this->db->join("SYS_M_Domain", "SYS_M_Domain.Dom_ID = STK_M_Warehouse.Warehouse_Type AND SYS_M_Domain.Dom_Active = 'Y'", "LEFT");
//        $this->db->where("STK_M_Warehouse.Active", ACTIVE);
        $this->db->order_by("STK_M_Warehouse.Warehouse_Id");
        $query = $this->db->get();
    //    echo $this->db->last_query(); exit();
        return $query;
    }

    function getWarehouseByID($WH_ID) {// get warehouse by id for edit.
        $this->db->select("*");
        $this->db->from("STK_M_Warehouse");
        $this->db->where("STK_M_Warehouse.Warehouse_Id", $WH_ID);
       $this->db->where("STK_M_Warehouse.Active", 1);
        $query = $this->db->get();
        return $query;
    }

    function getWarehouseByCode($WH_ID = NULL, $WH_CODE) {// For Check WH_Code. // Edit by Ton! 20130827
        $this->db->select("*");
        $this->db->from("STK_M_Warehouse");
        if (!empty($WH_ID)): // Add by Ton! 20130827
            $this->db->where("STK_M_Warehouse.Warehouse_Id <> " . $WH_ID);
        endif;
        if ($WH_CODE != '') :
            $this->db->where("STK_M_Warehouse.Warehouse_Code", $WH_CODE);
        endif;
        $this->db->where("STK_M_Warehouse.Active", ACTIVE);
        $query = $this->db->get();
//        echo $this->db->last_query(); exit();
        return $query;
    }

    function saveDataWarehouse($type = NULL, $data = NULL, $where = NULL) {// save warehouse(Add & Edit).
        if ($type == 'ist') :// Insert.
            $this->db->insert('STK_M_Warehouse', $data);
            $warehouse_id = $this->db->insert_id();
            if ($warehouse_id > 0) :
                return $warehouse_id; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;

        elseif ($type == 'upd') :// Update.
            $this->db->where($where);
            $this->db->update('STK_M_Warehouse', $data);
//            echo $this->db->last_query(); exit();
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows > 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        elseif ($type == 'del') :// Delete.
            $this->db->where($where);
            $this->db->delete('STK_M_Warehouse', $data);
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows > 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        endif;
    }

    # Comment Out by Ton! 20140211 Not Used.
//    function deleteDataWarehouse($data, $where) {// delete warehouse.
//        $this->db->where($where);
//        $query = $this->db->update('STK_M_Warehouse', $data);
//        $afftectedRows = $this->db->affected_rows();
//        if ($afftectedRows > 0) {
//            return TRUE; // Save Success.
//        } else {
//            return FALSE; // Save UnSuccess.
//        }
//    }

    function get_warehouse_type($WH_Type_Id = NULL, $WH_Type_Code = NULL) {// Add by Ton! 20140211

        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->from("SYS_M_Domain");
        $this->db->where("SYS_M_Domain.Dom_Host_Code", "WH_Type");
        if (!empty($WH_Type_Id)):
            $this->db->where("SYS_M_Domain.Dom_ID", $WH_Type_Id);
        endif;
        if (!empty($WH_Type_Code)):
            $this->db->where("SYS_M_Domain.Dom_Code", $WH_Type_Code);
        endif;
        $this->db->where("SYS_M_Domain.Dom_Active", 1);
        $this->db->order_by("SYS_M_Domain.Dom_ID");
        $query = $this->db->get();
        return $query;
    }

}

?>
