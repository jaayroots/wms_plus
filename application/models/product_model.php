<?php

class Product_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getProductAll($product_code = '', $query_column = '', $supplier_id = '', $limit_start = 0, $limit_max = 100, $s_search = '', $order_by = NULL, $optional = NULL) {
        if ("" == $query_column) :
            $this->db->select("STK_M_Product.*, d.CTL_M_UOM_Template_id, d.public_name");
        else:
            $this->db->select(" Product_Id as Id .d.*," . $query_column);
        endif;
        $this->db->from("STK_M_Product");
        $this->db->join("CTL_M_UOM_Template_Language d", "STK_M_Product.Standard_Unit_Id = d.CTL_M_UOM_Template_id AND d.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->where("STK_M_Product.Active", ACTIVE);

        if ("" != $product_code AND empty($s_search)) :
            $this->db->like("Product_Code", $product_code);
        endif;

        if ("" != $s_search) :
            $this->db->or_like("Product_Code", $s_search);
            $this->db->or_like("Product_NameEN", $s_search);
            $this->db->or_like("Product_NameTH", $s_search);
        endif;

        if ("" != $supplier_id) :
//            $this->db->where("Supplier_Id", $supplier_id);
        endif;

        if (!empty($order_by)):
            $this->db->order_by($order_by);
        else:
            $this->db->order_by("dbo.STK_M_Product.Product_Id");
        endif;

        $this->db->limit($limit_max, $limit_start);

        if (!empty($optional)):
            $sql = $this->db->compile_select() . $optional;
            $query = $this->db->query($sql);
        else :
            $query = $this->db->get();
        endif;

        return $query;
    }

    function getProductArray($Product_Id, $query_column = array()) {
        if (empty($query_column)) :
            $this->db->select("*");
        else :
//$this->db->select(" Product_Id as Id ," . $query_column); //COMMENT BY POR 2014-02-07 แก้ไขให้ใช้เป็น array
#ADD BY POR 2014-02-07
            $this->db->select(" Product_Id as Id");
            $this->db->select($query_column);
#END ADD
        endif;
        $this->db->from("STK_M_Product");

//  Edit By Akkarapol, 13/01/2014, เปลี่ยนจากการ join กับตาราง SYS_M_Domain เพื่อหา Unit Name เป็นการ join กับ  CTL_M_UOM_Template_Language แทน เพื่อรองรับกับการทำ UOM
// $this->db->join("SYS_M_Domain", "STK_M_Product.Standard_Unit_Id = SYS_M_Domain.Dom_Code AND Dom_Host_Code='PROD_UNIT' ", "LEFT");
        $this->db->join("CTL_M_UOM_Template_Language S1", "STK_M_Product.Standard_Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'");
// End Edit By Akkarapol, 13/01/2014, เปลี่ยนจากการ join กับตาราง SYS_M_Domain เพื่อหา Unit Name เป็นการ join กับ  CTL_M_UOM_Template_Language แทน เพื่อรองรับกับการทำ UOM

        $this->db->where_in("Product_Id", $Product_Id);
        $this->db->where("Active", ACTIVE);
        $query = $this->db->get();
        return $query;
    }

    function getProductArrayByProductCode($product_code, $supplier_id = '', $query_column = array()) {
        if (empty($query_column)) :
            $this->db->select("*");
        else :
            $this->db->select(" Product_Id as Id");
            $this->db->select($query_column);
        endif;
        $this->db->from("STK_M_Product");

// Edit By Akkarapol, 10/01/2014, เปลี่ยนจากการ join กับตาราง SYS_M_Domain เพื่อหา Unit Name เป็นการ join กับ  CTL_M_UOM_Template_Language แทน เพื่อรองรับกับการทำ UOM
//        $this->db->join("SYS_M_Domain", "STK_M_Product.Standard_Unit_Id = SYS_M_Domain.Dom_Code AND Dom_Host_Code='PROD_UNIT' ", "LEFT");
        $this->db->join("CTL_M_UOM_Template_Language", "STK_M_Product.Standard_Unit_Id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id AND CTL_M_UOM_Template_Language.language = '" . $this->config->item('lang3digit') . "'");
// END Edit By Akkarapol, 10/01/2014, เปลี่ยนจากการ join กับตาราง SYS_M_Domain เพื่อหา Unit Name เป็นการ join กับ  CTL_M_UOM_Template_Language แทน เพื่อรองรับกับการทำ UOM

        $this->db->where_in("Product_Code", $product_code);
        $this->db->where("Active", ACTIVE);

// Edit By Akkarapol, 17/09/2013, เพิ่ม if เข้าไปเช็คว่า ถ้า $supplier_id ที่ส่งเข้ามานั้นไม่มีค่า ก็ไม่ต้องเข้าไปทำการ where
        if (!empty($supplier_id)):
            $this->db->where("Supplier_Id", $supplier_id); // Add by Akkarapol, 27/08/2013, เพิ่ม supplier_id เพื่อที่จะ filter เฉพาะ product ของ supplier เท่านั้น
        endif;
// END Edit By Akkarapol, 17/09/2013, เพิ่ม if เข้าไปเช็คว่า ถ้า $supplier_id ที่ส่งเข้ามานั้นไม่มีค่า ก็ไม่ต้องเข้าไปทำการ where
        $query = $this->db->get();
        return $query;
    }

    function getFirstProductArrayByProductCode($product_code, $supplier_id = '', $query_column = array(), $order_by = null) {
        if (empty($query_column)) :
            $this->db->select("*");
        else :
            $this->db->select(" Product_Id as Id");
            $this->db->select($query_column);
        endif;
        $this->db->from("STK_M_Product");
        $this->db->join("CTL_M_UOM_Template_Language", "STK_M_Product.Standard_Unit_Id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id AND CTL_M_UOM_Template_Language.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->like("Product_Code", $product_code);
        $this->db->where("Active", ACTIVE);

        if (!empty($supplier_id)):
            $this->db->where("Supplier_Id", $supplier_id); // Add by Akkarapol, 27/08/2013, เพิ่ม supplier_id เพื่อที่จะ filter เฉพาะ product ของ supplier เท่านั้น
        endif;


        if (!empty($order_by)):
            $this->db->order_by($order_by);
        endif;

        $query = $this->db->get();
        return $query;
    }

// Add By Akkarapol, 16/09/2013, เพิ่มสำหรับ query ข้อมูลของ Product ด้วย Product_Code โดยไม่สน Supplier_Id
    function getProductDetailByProductCode($product_code, $query_column = "") {
        if ("" == $query_column) :
            $this->db->select("*
                , CASE Min_Aging WHEN NULL THEN 0 ELSE Min_Aging END AS Min_Aging
                , CASE Min_ShelfLife WHEN NULL THEN 0 ELSE Min_ShelfLife END AS Min_ShelfLife
                ");
        else :
            $this->db->select(" Product_Id as Id ," . $query_column);
        endif;
        $this->db->from("STK_M_Product");
        $this->db->where_in("Product_Code", $product_code);
        $query = $this->db->get();
//       echo $this->db->last_query();
        return $query;
    }

// END Add By Akkarapol, 16/09/2013, เพิ่มสำหรับ query ข้อมูลของ Product ด้วย Product_Code โดยไม่สน Supplier_Id

    function getProductCodeByProdID($prodID) {
        $this->db->select("*");
        $this->db->from("STK_M_Product");
        $this->db->where_in("Product_Id", $prodID);
        $query = $this->db->get();
        return $query;
    }

    function getProductData($data_where, $data_select) {
        $this->db->select($data_select);
        $this->db->from("STK_M_Product");
        $this->db->where($data_where);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    function getProductIDByProdCode($prodCode) {
        $this->db->select("Product_Id");
        $this->db->from("STK_M_Product");
        $this->db->where("Active", ACTIVE);
        $this->db->where("Product_Code", $prodCode);
        $query = $this->db->get();
        $result = $query->result();
//        p($this->db->last_query());
        if ($query->num_rows > 0) :
            return $result[0]->Product_Id;
        else :
            return NULL;
        endif;
    }

    function selectProductStatus($is_pending = NULL, $Dom_Code = '') { // Edit by kik : 03-09-2013 กำหนดค่าให้ $is_pending = null เพราะเมื่อเวลาเรียกใช้ฟังก์ชั่น selectProductStatus แล้วไม่ได้ส่ง parameter มา จะได้ไม่มีปัญหา
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "PROD_STATUS");
        $this->db->where("Dom_Active", ACTIVE);
        if (ACTIVE == $is_pending) :
            $this->db->where("Dom_Code", "PENDING");
        elseif ($is_pending == NULL) :
            $this->db->where("Dom_Code <>", "PENDING");
        endif;
        if ($Dom_Code != '') :
            $this->db->where("Dom_Code", $Dom_Code);
        endif;
        $this->db->from("SYS_M_Domain");
        $this->db->order_by("Sequence", "ASC");
        $query = $this->db->get();
    //    p($this->db->last_query());exit();
        return $query;
    }

    function selectProductUnitName() {
//        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
//        $this->db->where("Dom_Host_Code", "PROD_UNIT");
//        $this->db->where("Dom_Active", ACTIVE);
//        $this->db->from("SYS_M_Domain");
//        $this->db->order_by("Sequence", "ASC");
// Add By Akkarapol, 10/01/2014, เปลี่ยนการ get Unit ต่างๆจาก SYS_M_Domain เป็นการ get จาก CTL_M_UOM_Template เพื่อให้รองรับกับการทำ UOM/BOM
        $this->db->select('CTL_M_UOM_Template.id AS Dom_Code, CTL_M_UOM_Template_Language.public_name AS Dom_EN_Desc, CTL_M_UOM_Template_Language.description');
        $this->db->from("CTL_M_UOM_Template");
        $this->db->join("CTL_M_UOM_Template_Language", "CTL_M_UOM_Template.id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id AND CTL_M_UOM_Template_Language.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->join("CTL_M_UOM_Select", "CTL_M_UOM_Template.CTL_M_UOM_Select_id = CTL_M_UOM_Select.id");
        $this->db->join("CTL_M_UOM", "CTL_M_UOM_Select.type_id = CTL_M_UOM.id AND CTL_M_UOM.code = 'STD_UNIT' AND CTL_M_UOM.active = '" . ACTIVE . "'");
        $this->db->where("CTL_M_UOM_Template.active", ACTIVE);

// END Add By Akkarapol, 10/01/2014, เปลี่ยนการ get Unit ต่างๆจาก SYS_M_Domain เป็นการ get จาก CTL_M_UOM_Template เพื่อให้รองรับกับการทำ UOM/BOM

        $query = $this->db->get();
        return $query;
    }

//ADD  BY POR 2014-01-09 เพิ่มให้เรียก unit ของราคา
    function selectPriceUnitName() {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "PRICE_UNIT");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->from("SYS_M_Domain");
        $this->db->order_by("Sequence", "ASC");
        $query = $this->db->get();
        return $query;
    }

//END ADD

    function selectSubStatus($Dom_Code = '') {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "SUB_STATUS");
        if ($Dom_Code != '') :
            $this->db->where("Dom_Code", $Dom_Code);
        endif;
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->from("SYS_M_Domain");
        $this->db->order_by("Sequence", "ASC");
        $query = $this->db->get();
        return $query;
    }

    function getUnitIdByDomENDesc($Dom_EN_Desc) {
        $this->db->select("Dom_Code");
        $this->db->where("Dom_Host_Code", "PROD_UNIT");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->where("Dom_EN_Desc", $Dom_EN_Desc);
        $this->db->from("SYS_M_Domain");
        $query = $this->db->get();
        $result = $query->result();
        if ($query->num_rows > 0) :
            return $result[0]->Dom_Code;
        else :
            return NULL;
        endif;
    }

    function getUnitIdByProdID($ProdID) {
//        $this->db->select("Standard_Unit_Id");
        $this->db->select("Standard_Unit_In_Id"); // Edit By Akkarapol, 10/02/2014, เปลี่ยนจาก select ค่าของ Standard_Unit_Id เป็น Standard_Unit_In_Id เพราะตอนนี้มีการแบ่ง Standard Unit เป็นสองฝั่งคือ In และ Out ซึ่งฟังก์ชั่น getUnitIdByProdID นี้มีใช้แค่ในการ Import ฝั่ง pre-receive เท่านั้น จึงใส่ select ด้วยตัว Standard_Unit_In_Id เข้าไปเลย
        $this->db->where("Product_Id", $ProdID);
        $this->db->from("STK_M_Product");
        $query = $this->db->get();
        $result = $query->result();
        if ($query->num_rows > 0) :
//            return $result[0]->Standard_Unit_Id;
            return $result[0]->Standard_Unit_In_Id; // Edit By Akkarapol, 10/02/2014, เปลี่ยนจาก select ค่าของ Standard_Unit_Id เป็น Standard_Unit_In_Id เพราะตอนนี้มีการแบ่ง Standard Unit เป็นสองฝั่งคือ In และ Out ซึ่งฟังก์ชั่น getUnitIdByProdID นี้มีใช้แค่ในการ Import ฝั่ง pre-receive เท่านั้น จึงใส่ select ด้วยตัว Standard_Unit_In_Id เข้าไปเลย
        else :
            return NULL;
        endif;
    }

#ISSUE 2233 Stock  Adjustment  : Search Product by Status & Sub Status
#DATE:2013-09-12
#BY:KIK
#เพิ่มการค้นหาด้วย Status & Sub Status
#START New Comment Code #ISSUE 2233
#=======================================================================================

    function getProductForChStatus($product_code = "", $product_status = "", $product_subStatus = "") {

        $sql_getProductSubStatus = '(select Location_Id
                                    from STK_M_Location
                                    where Storage_Id in (
                                    select storage_id from STK_M_Storage where StorageType_Id in (
                                    select storageType_Id from  STK_M_Storage_Type where StorageType_Code  NOT IN
                                    (select DOM_CODE from SYS_M_Domain where Dom_Host_Code = "NODISP_STORAGE"))))';

        $this->db->select("
                        STK_T_Inbound.*
                        ,STK_T_Inbound.Actual_Location_Id AS Location_Id
                        , CONVERT(VARCHAR(20), STK_T_Inbound.Product_Mfd, 103) AS Product_Mfd
                        , CONVERT(VARCHAR(20), STK_T_Inbound.Product_Exp, 103) AS Product_Exp
                        ,STK_M_Location.Location_Code
                        ,STK_M_Product.Product_NameEN
			,S1.Dom_EN_Desc AS Unit_Value
			,S2.Dom_Code   AS Status_Code
			,S2.Dom_EN_Desc AS Status_Value
			,S3.Dom_Code   AS Sub_Status_Code
			,S3.Dom_EN_Desc AS Sub_Status_Value
		");

        $this->db->from("STK_T_Inbound");
        $this->db->join("STK_M_Location", "STK_T_Inbound.Actual_Location_Id=STK_M_Location.Location_Id", "left");
        $this->db->join("STK_M_Product", "STK_T_Inbound.Product_Id=STK_M_Product.Product_Id", "left");
        $this->db->join("STK_M_Storage", "STK_M_Location.Storage_Id = STK_M_Storage.Storage_Id");
        $this->db->join("STK_M_Storage_Type", "STK_M_Storage.StorageType_Id = STK_M_Storage_Type.StorageType_Id");
        $this->db->join("SYS_M_Domain S1", "STK_T_Inbound.Unit_Id = S1.Dom_Code AND S1.Dom_Host_Code='PROD_UNIT' AND S1.Dom_Active = 'Y'");
        $this->db->join("SYS_M_Domain S2", "STK_T_Inbound.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS' AND S2.Dom_Code <> 'PENDING' AND S2.Dom_Active = 'Y' ");
        $this->db->join("SYS_M_Domain S3", "STK_T_Inbound.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS' AND S3.Dom_Active = 'Y'", "LEFT");

//        $this->db->where("StorageType_Code != ", "ST05");
        $this->db->where("STK_T_Inbound.Active", ACTIVE);
        $this->db->where("STK_T_Inbound.Balance_Qty > 0");
        $this->db->where("STK_M_Location.Location_Id  IN " . $sql_getProductSubStatus);

        if ($product_code != "") :
            $this->db->where("STK_T_Inbound.Product_Code", $product_code);
        endif;

        if ($product_status != "") :
            $this->db->where("STK_T_Inbound.Product_Status", $product_status);
        else :
            $this->db->where("STK_T_Inbound.Product_Status!=", "PENDING");
        endif;

        if ($product_subStatus != "") :
            $this->db->where("STK_T_Inbound.Product_Sub_Status", $product_subStatus);
        endif;

        $this->db->order_by("STK_T_Inbound.Inbound_Id", "ASC");
        $query = $this->db->get();
        return $query;
    }

#End New Comment Code #ISSUE 2233 (12-09-2013)
#=======================================================================================
#ISSUE 2233 Stock  Adjustment  : Search Product by Status & Sub Status
#DATE:2013-09-04
#BY:KIK
#เพิ่มการค้นหาด้วย Status & Sub Status ในส่วนของ get product detail และแก้ส่วนที่ยังผิดพลาดอยู่
#START New Comment Code #ISSUE 2233
#=======================================================================================
// Add Distinct By Ball

    function getProductStock($product_code = "", $product_status = "", $product_subStatus = "") {
        $this->db->select("DISTINCT
                        i.*
                        ,i.Actual_Location_Id AS Location_Id
                        , CONVERT(VARCHAR(20), i.Product_Mfd, 103) AS Product_Mfd
                        , CONVERT(VARCHAR(20), i.Product_Exp, 103) AS Product_Exp
                        ,l.Location_Code
                        ,p.Product_NameEN
			,S1.Dom_EN_Desc AS Unit_Value
			,S2.Dom_Code   AS Status_Code
			,S2.Dom_EN_Desc AS Status_Value
			,S3.Dom_Code   AS Sub_Status_Code
			,S3.Dom_EN_Desc AS Sub_Status_Value
            ,(i.Receive_Qty - i.PD_Reserv_Qty - i.Dispatch_Qty - i.Adjust_Qty) as Allocate
		");

        $this->db->from("STK_T_Inbound i");
        $this->db->join("STK_M_Location l", "i.Actual_Location_Id=l.Location_Id", "left");
        $this->db->join("STK_M_Product p", "i.Product_Id=p.Product_Id", "left");
        $this->db->join("STK_M_Storage d", "l.Storage_Id = d.Storage_Id");
        $this->db->join("STK_M_Storage_Type e", "d.StorageType_Id = e.StorageType_Id");
        $this->db->join("SYS_M_Domain S1", "i.Unit_Id = S1.Dom_Code AND S1.Dom_Host_Code='PROD_UNIT'");
        $this->db->join("SYS_M_Domain S2", "i.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS' AND S2.Dom_Code <> 'PENDING' ");
        $this->db->join("SYS_M_Domain S3", "i.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS'", "LEFT");

        $this->db->where("StorageType_Code != ", "ST05");
        $this->db->where("i.Active", ACTIVE);
//$this->db->where("i.Balance_Qty > 0");
        $this->db->where("(i.Receive_Qty - i.PD_Reserv_Qty - i.Dispatch_Qty - i.Adjust_Qty) > 0"); // Add where allocate must more than 0

        if ($product_code != "") :
            $this->db->where("i.Product_Code", $product_code);
        endif;

        if ($product_status != "") :
            $this->db->where("i.Product_Status", $product_status);
        else :
            $this->db->where("i.Product_Status!=", "PENDING");
        endif;

        if ($product_subStatus != "") :
            $this->db->where("i.Product_Sub_Status", $product_subStatus);
        endif;

        $this->db->order_by("i.Inbound_Id", "DESC");
        $query = $this->db->get();
        return $query;
    }

#End New Comment Code #ISSUE 2233
#=======================================================================================
#Start Old Comment Code #ISSUE 2233
#=======================================================================================
//    function getProductStock($product_code = "") {
//
////            --#ISSUE 2233
////            --#DATE:2013-08-28
////            --#BY:KIK
////            --#ปัญหา:Product_Mfd และ Product_Exp แสดงผิดรูปแบบ
////            --#สาเหตุ:Product_Mfd และ Product_Exp ขึ้นใน format ที่ดึงมากจากฐานข้อมูลโดยตรง ไม่ได้ convert
////            --#วิธีการแก้:ทำการ convert date ทั้งสองตัว ตามโค้ด
////            -- START Old Comment Code ISSUE 2233
////
//
////              $this->db->select("i.*,i.Actual_Location_Id AS Location_Id,l.Location_Code,p.Product_NameEN
////			,S1.Dom_EN_Desc AS Unit_Value
////			,S2.Dom_Code   AS Status_Code
////			,S2.Dom_EN_Desc AS Status_Value
////			,S3.Dom_Code   AS Sub_Status_Code
////			,S3.Dom_EN_Desc AS Sub_Status_Value
////		");						,i.Product_Status,i.Product_Lot,i.Product_Serial,(Receive_Qty-(Dispatch_Qty+Adjust_Qty)) AS Balance_Qty,i.Unit_Id,i.Product_Mfd,i.Product_Exp");
//
//
////            -- END Old Comment Code ISSUE 2233
////            -- START New Code ISSUE 2233
//
//                 $this->db->select("
//                        i.*
//                        ,i.Actual_Location_Id AS Location_Id
//                        , CONVERT(VARCHAR(20), i.Product_Mfd, 103) AS Product_Mfd
//                        , CONVERT(VARCHAR(20), i.Product_Exp, 103) AS Product_Exp
//                        ,l.Location_Code
//                        ,p.Product_NameEN
//			,S1.Dom_EN_Desc AS Unit_Value
//			,S2.Dom_Code   AS Status_Code
//			,S2.Dom_EN_Desc AS Status_Value
//			,S3.Dom_Code   AS Sub_Status_Code
//			,S3.Dom_EN_Desc AS Sub_Status_Value
//		");
//
// //            -- END New Code ISSUE 2233
//
//
//        $this->db->from("STK_T_Inbound i");
//        $this->db->join("STK_M_Location l", "i.Actual_Location_Id=l.Location_Id", "left");
//        $this->db->join("STK_M_Product p", "i.Product_Id=p.Product_Id", "left");
//        $this->db->join("STK_M_Storage d", "l.Storage_Id = d.Storage_Id");
//        $this->db->join("STK_M_Storage_Type e", "d.StorageType_Id = e.StorageType_Id");
//        $this->db->join("SYS_M_Domain S1", "i.Unit_Id = S1.Dom_Code AND S1.Dom_Host_Code='PROD_UNIT'");
//        $this->db->join("SYS_M_Domain S2", "i.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS' AND S2.Dom_Code <> 'PENDING' ");
//        $this->db->join("SYS_M_Domain S3", "i.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS'", "LEFT");
//
//        $this->db->where("StorageType_Code != ", "ST05");
//        $this->db->where("i.Product_Status!=", "PENDING");
//        $this->db->where("i.Active", ACTIVE);
//        $this->db->where("i.Balance_Qty > 0");
////        $this->db->where("(Receive_Qty - (Dispatch_Qty+Adjust_Qty))>0"); comment by kik : 04-09-2013
//        /*
//          if($product_code!=""){
//          $this->db->like("i.Product_Code",$product_code,'after');
//          }
//         */
//        $this->db->order_by("i.Inbound_Id", "ASC");
//        $query = $this->db->get();
//        $result = $query->result();
////        p($result);
//        return $query;
//    }
#End Old Comment Code #ISSUE 2233
#=======================================================================================

    function productCategory() {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "PROD_CATE");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->from("SYS_M_Domain");
        $query = $this->db->get();
        return $query;
    }

    function getProductUnit() {// Add by Ton! 20130709
//        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
//        $this->db->where("Dom_Host_Code", "PROD_UNIT");
//        $this->db->where("Dom_Active", ACTIVE);
//        $this->db->from("SYS_M_Domain");
// Add By Akkarapol, 10/01/2014, เปลี่ยนการ get Unit ต่างๆจาก SYS_M_Domain เป็นการ get จาก CTL_M_UOM_Template เพื่อให้รองรับกับการทำ UOM/BOM
        $this->db->select('CTL_M_UOM_Template.id AS Dom_Code, CTL_M_UOM_Template_Language.public_name AS Dom_EN_Desc, CTL_M_UOM_Template_Language.description');
        $this->db->from("CTL_M_UOM_Template");
        $this->db->join("CTL_M_UOM_Template_Language", "CTL_M_UOM_Template.id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id AND CTL_M_UOM_Template_Language.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->where("CTL_M_UOM_Template.active", ACTIVE);
// END Add By Akkarapol, 10/01/2014, เปลี่ยนการ get Unit ต่างๆจาก SYS_M_Domain เป็นการ get จาก CTL_M_UOM_Template เพื่อให้รองรับกับการทำ UOM/BOM

        $query = $this->db->get();
        return $query;
    }

    function getSTDWeightUnit() {// Add by Ton! 20130711
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "Weight_Unit");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->from("SYS_M_Domain");
        $query = $this->db->get();
        return $query;
    }

    function getDimensionUnit() {// Add by Ton! 20130711
//        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
//        $this->db->where("Dom_Host_Code", "Dimension_Unit");
//        $this->db->where("Dom_Active", ACTIVE);
//        $this->db->from("SYS_M_Domain");
// Add By Akkarapol, 2014/08/09, เปลี่ยนการ get Unit ต่างๆจาก SYS_M_Domain เป็นการ get จาก CTL_M_UOM_Template เพื่อให้รองรับกับการทำ UOM/BOM
        $this->db->select('CTL_M_UOM_Template.id AS Dom_Code, CTL_M_UOM_Template_Language.public_name AS Dom_EN_Desc, CTL_M_UOM_Template_Language.description');
        $this->db->from("CTL_M_UOM_Template");
        $this->db->join("CTL_M_UOM_Template_Language", "CTL_M_UOM_Template.id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id AND CTL_M_UOM_Template_Language.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->join("CTL_M_UOM_Select", "CTL_M_UOM_Template.CTL_M_UOM_Select_id = CTL_M_UOM_Select.id");
        $this->db->join("CTL_M_UOM", "CTL_M_UOM_Select.type_id = CTL_M_UOM.id AND CTL_M_UOM.code = 'STD_DIMENSION_UNIT'");
        $this->db->where("CTL_M_UOM_Template.active", ACTIVE);
// END Add By Akkarapol, 2014/08/09, เปลี่ยนการ get Unit ต่างๆจาก SYS_M_Domain เป็นการ get จาก CTL_M_UOM_Template เพื่อให้รองรับกับการทำ UOM/BOM

        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function getFG_LICSE() {// Add by Ton! 20130912
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "FG_LICSE");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->from("SYS_M_Domain");
        $query = $this->db->get();
        return $query;
    }

# Used for Product Master by Ton! 20130709 ===============================================================================================

    function getPrductMasterList($limit_start = "", $limit_max = "") {// get data for display list. Add by Ton! 20130709
        $this->db->select("DISTINCT STK_M_Product.Product_Id AS Id
            , STK_M_Product.Product_Code
            , STK_M_Product.Product_NameEN
            ,ISNULL(STK_M_Product.Min_Aging, 0) AS Min_Aging
            ,ISNULL(STK_M_Product.Min_ShelfLife, 0) AS Min_ShelfLife
            ,d.Dom_EN_Desc AS prodcate
            ,prodGroup.ProductGroup_NameEN AS prodgroup
            , CTL_M_UOM_Template_Language.public_name As UOM
            , pick.Dom_EN_Desc AS pickRule
            , CASE WHEN STK_M_Product.Active IN ('Y') THEN 'YES' ELSE 'NO' END AS Active
	    , STK_M_Product.Width
		, STK_M_Product.Length
		, STK_M_Product.Height
		, STK_M_Product.User_Defined_1 As CL
		, STK_M_Product.User_Defined_2 AS LP
		, STK_M_Product.User_Defined_3 AS IMS
		, STK_M_Product.User_Defined_4 AS pType
		, STK_M_Product.Unit_Per_Pallet AS PerPallet
		, STK_M_Product.STD_Weight
		, ISNULL(STD_Weight,0) * ISNULL(NULLIF(User_Defined_1, 0), 0) * ISNULL(NULLIF(User_Defined_2, 0), 0) AS NetWeight
		, ISNULL(STD_Weight,0) * ISNULL(NULLIF(User_Defined_1, 0), 0) * ISNULL(NULLIF(User_Defined_2, 0), 0) + 20 AS GrossWeight
		, Cubic_Meters
		, IsFG
		, IsRawMat
");
        $this->db->from("STK_M_Product");

//        $this->db->join("SYS_M_Domain AS prodCate", "STK_M_Product.ProductCategory_Id = prodCate.Dom_Code
//            AND prodCate.Dom_Host_Code = 'PROD_CATE' AND prodCate.Dom_Active = '" . ACTIVE . "'", "left");

        $this->db->join("CTL_M_ProductGroup AS prodGroup", "STK_M_Product.ProductGroup_Id = prodGroup.ProductGroup_Id AND prodGroup.Active = '1'", "left");
       
        $this->db->join("SYS_M_Domain AS pick", "STK_M_Product.PickUp_Rule = pick.Dom_Code
            AND pick.Dom_Host_Code = 'PIKING' AND pick.Dom_Active = '" . ACTIVE . "'", "left");
        $this->db->join("SYS_M_Domain  d", "STK_M_Product.ProductCategory_Id = d.Dom_Code and d.Dom_Host_Code='PROD_CATE' and d.Dom_Active='Y'", "left");
        $this->db->join("CTL_M_UOM_Template_Language", "STK_M_Product.Standard_Unit_Id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id AND CTL_M_UOM_Template_Language.language = '" . $this->config->item('lang3digit') . "'");

        $this->db->order_by("STK_M_Product.Product_Id");
        if (($limit_start !== "") && ($limit_max !== "")):
            $this->db->limit($limit_max, $limit_start);
        endif;

        $query = $this->db->get();
    //    echo $this->db->last_query();exit();
        return $query;
    }

    function getProductMaster($prodId = NULL, $prodCode = NULL, $Active = FALSE , $internalBarcode = NULL) {// Add by Ton! 20130709
        $this->db->select("DISTINCT STK_M_Product.*
            , STK_M_Product.Product_Id AS Id
            , prodCate.Dom_EN_Desc AS prodCate
            , prod_unit.public_name AS unit
            , pick.Dom_EN_Desc AS pickRule
            , put.Dom_EN_Desc AS putRule");
        $this->db->from("STK_M_Product");
        $this->db->join("SYS_M_Domain AS prodCate", "STK_M_Product.ProductCategory_Id = prodCate.Dom_Code "
                . "AND prodCate.Dom_Host_Code = 'PROD_CATE' AND prodCate.Dom_Active = '" . ACTIVE . "'", "left");
        $this->db->join("SYS_M_Domain AS unit", "STK_M_Product.Standard_Unit_Id = unit.Dom_Code "
                . "AND unit.Dom_Host_Code = 'PROD_UNIT' AND unit.Dom_Active = '" . ACTIVE . "'", "left");

        $this->db->join("CTL_M_UOM_Template_Language prod_unit", "STK_M_Product.Standard_Unit_Id = prod_unit.CTL_M_UOM_Template_id AND prod_unit.language = '" . $this->config->item('lang3digit') . "'");

        $this->db->join("SYS_M_Domain AS pick", "STK_M_Product.PickUp_Rule = pick.Dom_Code "
                . "AND unit.Dom_Host_Code = 'PIKING' AND unit.Dom_Active = '" . ACTIVE . "'", "left");
        $this->db->join("SYS_M_Domain AS put", "STK_M_Product.PutAway_Rule = put.Dom_Code "
                . "AND unit.Dom_Host_Code = 'PUTAWAY' AND unit.Dom_Active = '" . ACTIVE . "'", "left");
        if ($Active === TRUE):
            $this->db->where("STK_M_Product.Active", ACTIVE);
        endif;
        if (!empty($prodId)):
            $this->db->where_in("STK_M_Product.Product_Id", $prodId);
        endif;

        if (!empty($prodCode)):
            $this->db->where_in("STK_M_Product.Product_Code", $prodCode);
        endif;
        
        if (!empty($internalBarcode)):
            $this->db->where("(STK_M_Product.Internal_Barcode1 ='". $internalBarcode ."' OR STK_M_Product.Internal_Barcode2 ='". $internalBarcode ."')", NULL, FALSE);
        endif;
        
        $this->db->order_by("STK_M_Product.Product_Id");
        $query = $this->db->get();
        return $query;
    }

    function checkInbound($prodId = "", $prodCode = "") {// Check product at STK_T_Inbound before delete. Add by Ton! 20130709
//        $this->db->select("STK_T_Inbound.*, Receive_Qty - (Dispatch_Qty + Adjust_Qty) AS Balance_Qty");
//        $this->db->from("STK_T_Inbound");
//        if ($prodId != "") {
//            $this->db->where_in("STK_T_Inbound.Product_Id", $prodId);
//        }
//        if ($prodCode != "") {
//            $this->db->where_in("STK_T_Inbound.Product_Code", $prodCode);
//        }
//        $this->db->where("STK_T_Inbound.Active", ACTIVE);
//        $this->db->order_by("STK_T_Inbound.Product_Id");
//
        // Edit by Ton! 20131029
        $this->db->select("vw_inbound.*, vw_inbound.Est_Balance_Qty AS Balance_Qty");
        $this->db->from("vw_inbound");
        if ($prodId != "") :
            $this->db->where_in("vw_inbound.Product_Id", $prodId);
        endif;
        if ($prodCode != "") :
            $this->db->where_in("vw_inbound.Product_Code", $prodCode);
        endif;
        $this->db->order_by("vw_inbound.Product_Id");

        $query = $this->db->get();
        return $query;
    }

//    Comment Out by Ton! Not Used. 20131209
//    function deleteProductMaster($data, $where) {// delete data Product Master. Add by Ton! 20130709
//        $this->db->where($where);
//        $this->db->update('STK_M_Product', $data);
//        $afftectedRows = $this->db->affected_rows();
//        if ($afftectedRows > 0) {
//            return TRUE; // Save Success.
//        } else {
//            return FALSE; // Save UnSuccess.
//        }
//    }

    function saveProductMaster($type = NULL, $data = NULL, $where = NULL) {// save Product (Insert, Update). Add by Ton! 20130709
        if ($type == 'ist') :// Insert.
            $this->db->insert('STK_M_Product', $data);
            $prodId = $this->db->insert_id();
//            echo $this->db->last_query();exit();
//            $this->save_re_order_point('ist', $re_order_point, $prodId);
            if ($prodId > 0) :
                return $prodId;
            else:
                return FALSE;
            endif;
        elseif ($type == 'upd') :// Update.
            $this->db->where($where);
            $this->db->update('STK_M_Product', $data);
            $afftectedRows = $this->db->affected_rows();
//            echo $this->db->last_query();exit();
//            $this->save_re_order_point('upd', $re_order_point, $where['Product_Id']);
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        endif;
    }

    function updateFileProduct($Path_FileName, $Product_Id) { // Add By Joke 20/6/2016 13:48
        $data = array("SafetyDataSheet" => $Path_FileName);
        $this->db->where("Product_Id", $Product_Id);
        $this->db->update('STK_M_Product', $data);
//        echo $this->db->last_query();exit();
        if ($afftectedRows >= 0) :
            return TRUE; // Save Success.
        else :
            return FALSE; // Save UnSuccess.
        endif;
    }

    function DeleteFileProduct($Product_Id) { // Add By Joke 20/6/2016 13:48
        $data = array("SafetyDataSheet" => 'NULL');
        $this->db->where($Product_Id);
        $this->db->update('STK_M_Product', $data);
        if ($afftectedRows >= 0) :
            return TRUE; // Save Success.
        else :
            return FALSE; // Save UnSuccess.
        endif;
    }

    function getPickUpRule() {// Add by Ton! 20130709
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "PIKING");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->from("SYS_M_Domain");
        $query = $this->db->get();
        return $query;
    }

    function getPutAwayRule() {// Add by Ton! 20130709
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "PUTAWAY");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->from("SYS_M_Domain");
        $query = $this->db->get();
        return $query;
    }

//    ----- START #2429 Add by Ton! 20130822 -----
    function saveProdToLocate($data) {
//        $this->db->insert_batch('STK_M_Product_Location', $data);
        $this->db->insert('STK_M_Product_Location', $data);
//        $afftectedRows=$this->db->affected_rows();
        if ($this->db->insert_id() > 0) :
            return TRUE;
        else :
            return FALSE;
        endif;
    }

//    ----- END #2429 Add by Ton! 20130822 -----
//    Add by Akkarapol, 27/08/2013, ทำ autocomplete ในส่วนของ Product Code และเมื่อคลิกที่ list product ดังกล่าว ก็จะให้ auto input to datatable
    function searchProduct($txt_search, $supplier_id = NULL, $limit_max = NULL, $limit_start = 0, $order_by = NULL) {
        $this->db->select('Product_Id,Product_Code,Product_NameEN');

        $this->db->bracket('open', 'like'); //bracket closed
        $this->db->or_like("Product_Code", $txt_search, 'after');
        $this->db->or_like("Product_NameEN", iconv("UTF-8", "TIS620//IGNORE", $txt_search));
        $this->db->bracket('close', 'like'); //bracket closed

        $this->db->from('STK_M_Product');
        $this->db->where("Active", "Y");
        if (!empty($supplier_id)):
//            $this->db->where("Supplier_Id", $supplier_id);
        endif;
        if (!empty($limit_max)):
            $this->db->limit($limit_max, $limit_start);
        endif;
        if (!empty($order_by)):
            $this->db->order_by($order_by);
        endif;
        $query = $this->db->get();
//        echo $this->db->last_query();
        $i = 0;
        $data = array();
        foreach ($query->result() as $row) :
            $data[$i]['product_id'] = $row->Product_Id;
            $data[$i]['product_code'] = $row->Product_Code;
            $data[$i]['product_name'] = $row->Product_NameEN;
            $i++;
        endforeach;
        return $data;
    }

//       END Add by Akkarapol, 27/08/2013, ทำ autocomplete ในส่วนของ Product Code และเมื่อคลิกที่ list product ดังกล่าว ก็จะให้ auto input to datatable

    function get_re_order_point($column = '*', $where = '1=1', $order_by = null) {
        $this->db->select($column);
        $this->db->from('STK_M_Re_Order_Point');
        $this->db->where($where);
        if (!empty($order_by)):
            $this->db->order_by($order_by);
        endif;
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function _save_re_order_point($type = NULL, $data = NULL, $prod_id = NULL) {// Add by Ton! 20140305
        $save_status = 0;
        if ($type == 'ist') :// Insert.
            if (!empty($data)):
                foreach ($data as $index => $value):
                    unset($value['id']);
                    $value['product_id'] = $prod_id;
                    $this->db->insert('STK_M_Re_Order_Point', $value);
                    $Id = $this->db->insert_id();
                    if ($Id <= 0) :
                        $save_status++;
                    endif;
                endforeach;
            endif;
        elseif ($type == 'upd') :// Update.
            $now_use_id = array();
            if (!empty($data)):
                foreach ($data as $index => $value):
                    if (!empty($value['id'])):
                        $now_use_id[] = $value['id'];
                    endif;
                endforeach;
                if (!empty($now_use_id)):
                    $this->db->where_not_in('STK_M_Re_Order_Point.id', $now_use_id);
                endif;
                $this->db->where('STK_M_Re_Order_Point.product_id', $prod_id);
                $this->db->delete('STK_M_Re_Order_Point');
//                $delete_afftectedRows = $this->db->affected_rows();
//                if ($delete_afftectedRows <= 0):
//                    $save_status++;
//                endif;

                foreach ($data as $index => $value):
                    if (empty($value['id'])):
                        unset($value['id']);
                        if (!in_array("", $value)):
                            $value['product_id'] = $prod_id;
                            $this->db->insert('STK_M_Re_Order_Point', $value);
                        endif;
                    else:
                        $value['product_id'] = $prod_id;
                        $this->db->where('STK_M_Re_Order_Point.id', $value['id']);
                        unset($value['id']);
                        $this->db->update('STK_M_Re_Order_Point', $value);
                        $update_afftectedRows = $this->db->affected_rows();
                        if ($update_afftectedRows < 0):
                            $save_status++;
                        endif;
                    endif;
                endforeach;
            endif;
        elseif ($type == 'del') :// Update.
            $value["active"] = INACTIVE;
            $this->db->where('STK_M_Re_Order_Point.product_id', $prod_id);
            $this->db->update('STK_M_Re_Order_Point', $value);
            $update_afftectedRows = $this->db->affected_rows();
            if ($update_afftectedRows < 0):
                $save_status++;
            endif;
        endif;

        if ($save_status > 0):
            return FALSE;
        else:
            return TRUE;
        endif;
    }

//    function save_re_order_point($type, $datas, $prod_id) {// Add By Akkarapol, 18/02/2014, save Re Order Point (Insert, Update).
//        if ($type == 'ist') :// Insert.
//            if (!empty($datas)):
//                foreach ($datas as $key_data => $data):
//                    $data['product_id'] = $prod_id;
//                    $this->db->insert('STK_M_Re_Order_Point', $data);
//                endforeach;
//            endif;
//        elseif ($type == 'upd') :// Update.
//            $now_use_id = array();
//            if (!empty($datas)):
//                foreach ($datas as $key_data => $data):
//                    if (!empty($data['id'])):
//                        $now_use_id[] = $data['id'];
//                    endif;
//                endforeach;
//            endif;
//
//            if (!empty($now_use_id)):
//                $this->db->where_not_in('STK_M_Re_Order_Point.id', $now_use_id);
//            endif;
//            $this->db->where('STK_M_Re_Order_Point.product_id', $prod_id);
//            $this->db->delete('STK_M_Re_Order_Point');
//
//            if (!empty($datas)):
//                foreach ($datas as $key_data => $data):
//                    if (empty($data['id'])):
//                        unset($data['id']);
//                        if (!in_array("", $data)):
//                            $data['product_id'] = $prod_id;
//                            $this->db->insert('STK_M_Re_Order_Point', $data);
//                        endif;
//                    else:
//                        $data['product_id'] = $prod_id;
//                        $this->db->where('STK_M_Re_Order_Point.id', $data['id']);
//                        unset($data['id']);
//                        $this->db->update('STK_M_Re_Order_Point', $data);
//                    endif;
//                endforeach;
//            endif;
//        endif;
//    }
    //ADD BY POR 2014-06-03 หา default
    function getUnitPriceCodeDefault() {
        $this->db->select("TOP 1 Dom_Code");
        $this->db->from("SYS_M_Domain");
        $this->db->where("Dom_Host_Code", 'PRICE_UNIT');
        $this->db->where("Dom_Active", 'Y');
        $this->db->order_by("Sequence", 'DESC');
        $query = $this->db->get();
        $result = $query->result();
        if ($query->num_rows > 0) :
            return $result[0]->Dom_Code;
        else :
            return NULL;
        endif;
    }

    public function find_product($criteria) {
        $this->db->select("*");
        $this->db->from("STK_M_Product");
        $this->db->where("Product_NameEN Like '%" . $criteria . "%' Or Product_NameTH Like '%" . $criteria . "%' ");
        $this->db->order_by("Product_Id", "ASC");
        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        $result = $query->result();
        if ($query->num_rows > 0) :
            return $result;
        else :
            return NULL;
        endif;
    }

    public function generate_product_code() {
        $this->db->select(" MAX(CAST((CASE isnumeric(Product_Code) WHEN 0 THEN 0  ELSE CAST(Product_Code AS DECIMAL(16, 0)) END) AS BIGINT)) AS Product_Code");
        $this->db->from("STK_M_Product");
        $this->db->order_by("Product_Code", "DESC");
        $query = $this->db->get();
        $result = $query->row();

        $num_code = intval($result->Product_Code);
        $format_str = $num_code + 1;
        $data_code = strval($format_str);

        if ($query->num_rows > 0) :
            return $data_code;
        else :
            return NULL;
        endif;
    }

    public function searchProductForGenBarcode($params) {
        $this->db->select("STK_M_Product.Product_Barcode , STK_M_Product.Product_NameEN , STK_T_Order_Detail.Product_Lot , STK_T_Order_Detail.Product_Serial");

        if ($params['single_print'] == 1) {
            $this->db->select("1 As Reserv_Qty");
        } else {
            $this->db->select("STK_T_Order_Detail.Reserv_Qty");
        }
        
        $this->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id");
        $this->db->join("STK_M_Product", "STK_M_Product.Product_Id = STK_T_Order_Detail.Product_Id");
        $this->db->where("STK_T_Order.Document_No", $params['d']);
        $this->db->where("STK_T_Order_Detail.Active = 'Y'");
        $this->db->order_by("STK_T_Order_Detail.Item_Id", "ASC");

        $limit_start = ($params['start'] ? $params['start'] : 0);
        $limit_max = ($params['max'] ? $params['max'] : 99999);
        
        //$this->db->limit($limit_max, $limit_start);

        $query = $this->db->get("STK_T_Order");
      // echo $this->db->last_query(); exit;
        $result = $query->result();
        if ($query->num_rows > 0) :
            return $result;
        else :
            return NULL;
        endif;
    }

    public function searchProductCodeForGenBarcode($params) {
        $this->db->select("STK_M_Product.Product_Barcode , STK_M_Product.Product_NameEN");
        $this->db->where("STK_M_Product.Product_Code", $params['product_code']);
        $query = $this->db->get("STK_M_Product");
     
        $result = $query->result();
        if ($query->num_rows > 0) :
            return $result;
        else :
            return NULL;
        endif;
    }

    function get_product_rule( $prodId){
        $this->db->select("PutAway_Rule,Min_Aging");
        $this->db->from("STK_M_Product");
        $this->db->where("Product_Id", $prodId);
        $query = $this->db->get();
        // echo $this->db->last_query();exit;
        $result = $query->result();
        return $result;

    }
    
      public function check_alternate_barcode_v1($barcode = NULL , $pid = NULL) {
        // p($barcode); p($pid);
        if (strlen(trim($barcode)) == 0) {
            return 0; 
        } else {
            $this->db->select("COUNT(product_id) As num");
            $this->db->from("STK_M_Product");
           
            //  if (!empty($pid)):
            //     $this->db->where_in("STK_M_Product.Product_Id", $pid);
            // endif;
            
            $this->db->where("(STK_M_Product.Internal_Barcode1 ='". $barcode ."' OR 
                               STK_M_Product.Internal_Barcode2 ='". $barcode ."' OR 
                               STK_M_Product.Product_Barcode ='". $barcode ."') AND
                               STK_M_Product.Product_Id !='". $pid ."'");
            // End Add
            
            $query = $this->db->get()->row();
            // p( $query); 
            // p( $query->num); 
            // p( $this->db->last_query()); 
            return $query->num;
        }

    }
}
