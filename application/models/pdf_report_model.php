<?php

class pdf_report_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_data_tag($Zone_Id = NULL , $Storage_val = NULL) {
//            select lo.Location_Code as location
//	,inb.Product_Code as sku
//	,mp.Product_NameEN as b_description
//	,inb.Product_Lot as lot_number
//	,sum(isnull(inb.Balance_Qty,0)) as qty
//	,uom.name as uom
//from STK_T_Inbound inb
//join STK_M_Location lo on lo.Location_Id = inb.Actual_Location_Id
//join STK_M_Product mp on mp.Product_Id = inb.Product_Id
//left join CTL_M_UOM_Template_Language uom on uom.CTL_M_UOM_Template_id = inb.Unit_Id
//											and uom.language = 'eng'
//where 1=1
//and inb.Active = 'Y'
//and inb.Product_Code = '102-101900'
//group by lo.Location_Code
//		,inb.Product_Code
//		,mp.Product_NameEN
//		,inb.Product_Lot
//		,uom.name
        
        
        
        
        $this->db->select("  lo.Location_Code as location
                            ,inb.Product_Code as sku
                            ,mp.Product_NameEN as b_description
                            ,inb.Product_Lot as lot_number
                            ,sum(isnull(inb.Balance_Qty,0)) as qty
                            ,uom.name as uom");
        $this->db->from("STK_T_Inbound inb");
        $this->db->join("STK_M_Location lo", "lo.Location_Id = inb.Actual_Location_Id");
        $this->db->join("STK_M_Product mp", "mp.Product_Id = inb.Product_Id");
        $this->db->join("CTL_M_UOM_Template_Language uom", "uom.CTL_M_UOM_Template_id = inb.Unit_Id and uom.language = 'eng'", "LEFT");
        $this->db->where("inb.Active", 'Y');
        if($Zone_Id != NULL){
            $this->db->where("lo.Zone_Id",$Zone_Id );
        }
        if($Storage_val != NULL){
            $this->db->where("lo.Storage_Id",$Storage_val );
        }
        $this->db->group_by("lo.Location_Code
                            ,inb.Product_Code
                            ,mp.Product_NameEN
                            ,inb.Product_Lot
                            ,uom.name");
        $this->db->order_by("lo.Location_Code
                            ,inb.Product_Code
                            ,mp.Product_NameEN
                            ,inb.Product_Lot
                            ,uom.name");
        $query = $this->db->get();
 
        return $query;
    }
    
    function getZone($zoneID = NULL, $zoneCode = NULL) {
//        $this->db->select("*");
        $this->db->select("STK_M_Zone.Zone_Id, STK_M_Zone.Zone_NameEn");
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
    
    function getStorage($Warehouse_Id = NULL, $Zone_Id = NULL) {

        $this->db->select("STK_M_Storage.Storage_Id, STK_M_Storage.Storage_NameEn");
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

        return $query;
    }
    
    function getLocationList($Zone_Id = NULL , $Storage_val = NULL) {
        $this->db->select("STK_M_Location.Location_Id, STK_M_Location.Location_Code");
        $this->db->from("STK_M_Location");
        $this->db->where("STK_M_Location.Active", ACTIVE);
        if($Zone_Id != NULL){
            $this->db->where("STK_M_Location.Zone_Id",$Zone_Id );
        }
        if($Storage_val != NULL){
            $this->db->where("STK_M_Location.Storage_Id",$Storage_val );
        }
        $this->db->order_by("STK_M_Location.Location_Id");
        $query = $this->db->get();
        return $query;
    }
}

?>
