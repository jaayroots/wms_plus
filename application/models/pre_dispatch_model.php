<?php

/**
 * Description of dispatch_model
 * -------------------------------------
 * Put this class on project at 22/04/2013
 * @author Pakkaphon P.(PHP PG)
 * Create by NetBean IDE 7.3
 * SWA WMS PLUS Project.
 * Use with dispatch module function
 * Use Codeinigter Framework with combination of css and js.
 * --------------------------------------
 */
class pre_dispatch_model extends CI_Model {

    //put your code here
    function __construct() {
        parent::__construct();
    }

    #add select Price_Per_Unit,Unit_Price_Id,All_Price : by kik : 2014-01-13
    function getPreDispatchAll() {
        $this->db->select("a.Order_Id as Id,a.Document_No,a.Doc_Type,c.Contact_Code,a.Delivery_Date
        ,b.Product_Code,b.Reserv_Qty,b.Confirm_Qty,b.Unit_Id,b.Price_Per_Unit,b.Unit_Price_Id,b.All_Price,b.Remark");
        $this->db->from("STK_T_Order a");
        $this->db->join("STK_T_Order_Detail b", "a.Order_Id = b.Order_Id ", "left outer");
        $this->db->join("CTL_M_Contact c", "a.Renter_Id = c.Contact_Id", "left");

        $query = $this->db->get();
        //  echo $this->db->last_query();
        return $query;
    }

    //comment by kik (08-10-2013)
    //
    #ISSUE 2772 Pre-Dispatch : Search Product by Status & Sub Status
    #DATE:10-09-2013
    #BY:KIK
    #เพิ่มการค้นหาด้วย product lot , product serial , product Mfd , product Exp
    #START New Comment Code #ISSUE 2772
//    function getPreDispatchByProdCode($product_code = "",$productStatus = "",$productSubStatus = "",$productLot="",$productSerial="",$productMfd="",$productExp="") {
//        $productCode = trim($product_code);
//        $sql_getProductSubStatus = '(select Location_Id
//                                    from STK_M_Location
//                                    where Storage_Id in (
//                                    select storage_id from STK_M_Storage where StorageType_Id in (
//                                    select storageType_Id from  STK_M_Storage_Type where StorageType_Code  NOT IN
//                                    (select DOM_CODE from SYS_M_Domain where Dom_Host_Code = "NODISP_STORAGE"))))';
//        $this->db->select("
//                STK_T_Inbound.Inbound_Id
//                , STK_T_Inbound.Product_Code
//                , STK_T_Inbound.Receive_Qty
//                , STK_T_Inbound.PD_Reserv_Qty
//                , STK_T_Inbound.Adjust_Qty
//                , STK_T_Inbound.Dispatch_Qty
//                , STK_T_Inbound.Balance_Qty
//                ,(STK_T_Inbound.Receive_Qty - STK_T_Inbound.PD_Reserv_Qty - STK_T_Inbound.Dispatch_Qty - STK_T_Inbound.Adjust_Qty) as Est_Balance_Qty
//                , STK_T_Inbound.Product_Status
//                , STK_M_Product.Product_NameEN
//                , STK_T_Inbound.Product_License
//                , STK_T_Inbound.Product_Lot
//                , STK_T_Inbound.Product_Serial
//                , CONVERT(varchar(10), STK_T_Inbound.Product_Mfd, 103) as Product_Mfd
//                , CONVERT(varchar(10), STK_T_Inbound.Product_Exp, 103) as Product_Exp
//                , CONVERT(varchar(10), STK_T_Inbound.Receive_Date, 103) as Receive_Date
//                , SYS_M_Domain.Dom_EN_Desc as Product_Sub_Status
//                , STK_M_Location.Location_Id
//                , STK_M_Location.Location_Code
//                , STK_M_Product.Product_Id
//                , STK_M_Product.Product_Code
//                , STK_M_Product.PutAway_Rule", false);
//        $this->db->from("STK_T_Inbound");
//        $this->db->join("STK_M_Product", "STK_T_Inbound.Product_Code = STK_M_Product.Product_Code", "LEFT OUTER");
//        $this->db->join("STK_M_Location", "STK_T_Inbound.Actual_Location_Id = STK_M_Location.Location_Id", "LEFT");
//        $this->db->join("STK_M_Storage", "STK_M_Storage.Storage_Id = STK_M_Location.Storage_Id");
//        $this->db->join("STK_M_Storage_Type", "STK_M_Storage.StorageType_Id = STK_M_Storage_Type.StorageType_Id");
//        $this->db->join("SYS_M_Domain", "STK_T_Inbound.Product_Sub_Status = SYS_M_Domain.Dom_Code");
//
//        $this->db->where("(STK_T_Inbound.Receive_Qty - STK_T_Inbound.Dispatch_Qty - STK_T_Inbound.Adjust_Qty - STK_T_Inbound.PD_Reserv_Qty) > ", 0);// Edit by kik (27-09-2013)
//        $this->db->where("STK_T_Inbound.Balance_Qty > ", 0);// Edit by kik ( 19-09-2013)
//        $this->db->where("STK_M_Location.Location_Id  IN ".$sql_getProductSubStatus);
//        $this->db->where("STK_T_Inbound.Active", 'Y');
//        $this->db->where("STK_M_Product.Active", 'Y');//Edit by kik (27-09-2013)
//
//        //Product Code / SKU
//        if ($productCode != "") {
//            $this->db->like('STK_T_Inbound.Product_Code', $productCode);
//        }
//
//        //Product Status
//        if($productStatus != ""){
//            $this->db->where("STK_T_Inbound.Product_Status", $productStatus);
//        }else{
//            $this->db->where("LOWER(STK_T_Inbound.Product_Status) != ", 'pending');
//        }
//
//        //Product Sub Status
//        if($productSubStatus != ""){
//            $this->db->where("STK_T_Inbound.Product_Sub_Status", $productSubStatus);
//        }
//
//        //Product Lot
//        if($productLot != ""){
//            $this->db->like("STK_T_Inbound.Product_Lot", $productLot);
//        }
//
//        //Product Serail
//        if($productSerial != ""){
//            $this->db->like("STK_T_Inbound.Product_Serial", $productSerial);
//        }
//
//        //Product Mfd
//        if($productMfd != ""){
//            $this->db->where("Product_Mfd", convertDate($productMfd,"eng","iso","-"));
//        }
//
//        //Product Exp
//        if($productExp != ""){
//            $this->db->where("Product_Exp", convertDate($productExp,"eng","iso","-"));
//        }
//
//        $this->db->order_by("(CASE PutAway_Rule
//                                WHEN 'FIFO' THEN Receive_Date
//                                WHEN 'FEFO' THEN Product_Exp
//                                ELSE 1
//                                END)");
//        $query = $this->db->get();
////          echo $this->db->last_query();
//        return $query;
//    }
    #End New Comment Code #ISSUE 2772 : 10-09-2013
//    comment by kik : 10-09-2013
//
//    function getPreDispatchByProdCode($product_code = "",$product_status = "",$product_subStatus = "") {
//        $val = trim($product_code);
//        $this->db->select("
//		a.Inbound_Id as Inbound_Id
//                ,a.Product_Code
//                ,b.Product_NameEN
//                ,a.Product_Status
//                ,f.Dom_EN_Desc as Product_Sub_Status
//                ,c.Location_Code
//                ,a.Product_License
//                ,a.Product_Lot
//                ,a.Product_Serial
//                ,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd'
//                ,CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp'
//		,a.Receive_Qty
//                ,a.PD_Reserv_Qty
//                ,a.Adjust_Qty
//                ,a.Dispatch_Qty
//		,a.Balance_Qty
//		,b.Product_Id
//		,b.Product_Code", false);
//        $this->db->from("STK_T_Inbound a");
//        $this->db->join("STK_M_Product b", "a.Product_Code = b.Product_Code", "left outer");
//        $this->db->join("STK_M_Location c", "a.Actual_Location_Id = c.Location_Id", "Left");
//        $this->db->join("STK_M_Storage d", "d.Storage_Id = c.Storage_Id");
//        $this->db->join("STK_M_Storage_Type e", "d.StorageType_Id = e.StorageType_Id");
//        $this->db->join("SYS_M_Domain f", "a.Product_Sub_Status = f.Dom_Code");
//
//        $this->db->where("(a.Receive_Qty - a.PD_Reserv_Qty - a.Adjust_Qty)>", 0);
////        $this->db->where("StorageType_Code != ", "ST05");
//        $this->db->where("a.Active", 'Y');
//
//        if($product_status != ""){
//            $this->db->where("a.Product_Status", $product_status);
//        }else{
//            $this->db->where("LOWER(a.Product_Status) != ", 'pending');
//        }
//
//        if($product_subStatus != ""){
//            $this->db->where("a.Product_Sub_Status", $product_subStatus);
//        }
//
//        if ($val != "") {
//            $this->db->like('a.Product_Code', $val);
//            $this->db->or_like('b.Product_NameEN', $val);
//            $this->db->or_like('a.Product_Serial', $val);
//        }
//
//        $query = $this->db->get();
////          echo $this->db->last_query();
//        return $query;
//    }
//    function getPreDispatchByProdCode($param = "") {
//        $val = trim($param);
//        $this->db->select("
//		a.Inbound_Id as Inbound_Id,a.Product_Code,b.Product_NameEN,a.Product_Status,c.Location_Code
//        ,a.Product_License,a.Product_Lot,a.Product_Serial,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd',CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp'
//		,(a.Receive_Qty - (a.PD_Reserv_Qty + a.Adjust_Qty)) as Est_Balance_Qty
//		,(a.Receive_Qty - (a.Dispatch_Qty + a.Adjust_Qty)) as Balance_Qty
//		,b.Product_Id
//		,b.Product_Code", false);
//        $this->db->from("STK_T_Inbound a");
//        $this->db->join("STK_M_Product b", "a.Product_Code = b.Product_Code", "left outer");
//        $this->db->join("STK_M_Location c", "a.Actual_Location_Id = c.Location_Id", "Left");
//        $this->db->join("STK_M_Storage d", "d.Storage_Id = c.Storage_Id");
//        $this->db->join("STK_M_Storage_Type e", "d.StorageType_Id = e.StorageType_Id");
//
//        $this->db->where("(a.Receive_Qty - (a.PD_Reserv_Qty + a.Adjust_Qty))>", 0);
//        $this->db->where("LOWER(a.Product_Status) != ", 'pending');
//        $this->db->where("StorageType_Code != ", "ST05");
//        $this->db->where("a.Active", 'Y');
//        if ($val != "") {
//            $this->db->like('a.Product_Code', $val);
//            $this->db->or_like('b.Product_NameEN', $val);
//            $this->db->or_like('a.Product_Serial', $val);
//        }
//
//        $query = $this->db->get();
//        //  echo $this->db->last_query();
//        return $query;
//    }

    function getListPreDispatchByProdCode($param = "") {
        $val = trim($param);
        $str = "SELECT a.Inbound_Id,a.product_code,b.Product_NameEN,a.Product_Status
                ,a.Product_License,a.Product_Lot,a.Product_Serial,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd',CONVERT(varchar(10),a.Product_Exp,105) as 'Product_Exp'
                ,a.Temp_Balance_Qty
                 FROM STK_T_Inbound a
                 LEFT OUTER JOIN STK_M_Product b ON a.Product_Code = b.Product_Code
                 WHERE a.Inbound_Id IN (" . $val . ")";
        $query = $this->db->query($str);
        return $query->result();
    }

    /**
     *
     * @param string $param
     * @return unknown
     */
    // Add Distinct By Ball 20130930
    // Add Est_Balance_Qty by kik 20131003
    // Add Unit_Price_Id,Price_Per_Unit,Dom_EN_Desc by kik 20140113
    /*
    function getListPreDispatchByProdCodeViaAjax($param = "") {
        $val = trim($param);

        #Load config
        $conf = $this->config->item('_xml');
        //$conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        //$conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        //edit format to CI : BY POR 2014-08-18
        $this->db->select("a.Inbound_Id
                ,a.Product_Id
                ,a.Product_Code
                ,b.Product_NameEN
                ,a.Product_Status
                ,a.Product_Sub_Status ,a.Unit_Id
                ,a.Product_License,a.Product_Lot,a.Product_Serial,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd',CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp'
                ,a.Balance_Qty
                ,(a.Receive_Qty - a.PD_Reserv_Qty - a.Dispatch_Qty - a.Adjust_Qty) as Est_Balance_Qty
                ,S1.public_name AS Unit_Value
                ,S2.Dom_Code   AS Status_Code
                ,S2.Dom_EN_Desc AS Status_Value
                ,S3.Dom_Code   AS Sub_Status_Code
                ,S3.Dom_EN_Desc AS Sub_Status_Value
                ,L1.Location_Code AS Suggest_Location
                ,L2.Location_Code AS Actual_Location
                ,a.Actual_Location_Id
                ,a.Receive_Qty
                ,a.PD_Reserv_Qty
                ,a.Dispatch_Qty
                ,a.Adjust_Qty
		,CONVERT(varchar(10), a.Receive_Date, 103) As Receive_Date_sort
		,CONVERT(varchar(10), a.Product_Exp, 103) As Product_Expire_sort");

        if($conf_price_per_unit):
            $this->db->select("a.Unit_Price_Id
                ,a.Price_Per_Unit
                ,S4.Dom_EN_Desc AS Unit_Price_value");
        endif;

        if($conf_pallet):
            $this->db->select("Pallet_Code");
        endif;

        $this->db->from("STK_T_Inbound a");
        $this->db->join("STK_M_Product b","a.Product_Code = b.Product_Code","LEFT OUTER");
        $this->db->join("CTL_M_UOM_Template_Language S1","a.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->join("SYS_M_Domain S2","a.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS' AND S2.Dom_Active = 'Y' ");
        $this->db->join("SYS_M_Domain S3","a.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS' AND S3.Dom_Active = 'Y'","LEFT");
        $this->db->join("SYS_M_Domain S4","a.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code='PRICE_UNIT' AND S4.Dom_Active = 'Y'","LEFT");
        $this->db->join("STK_M_Location L1","a.Suggest_Location_Id = L1.Location_Id","LEFT");
        $this->db->join("STK_M_Location L2","a.Actual_Location_Id = L2.Location_Id","LEFT");

        if($conf_pallet):
            $this->db->join("STK_T_Pallet","a.Pallet_Id=STK_T_Pallet.Pallet_Id","LEFT");
        endif;

        $this->db->where("(Receive_Qty - PD_Reserv_Qty - Dispatch_Qty - Adjust_Qty) > 0");
        $this->db->where("b.ACTIVE",'Y');
        $this->db->where("a.Inbound_Id IN (" . $val . ")");

        $this->db->order_by("CASE b.PutAway_Rule
                                WHEN 'FIFO'
                                    THEN CASE a.Receive_Date WHEN NULL THEN 0 ELSE a.Receive_Date END
                                WHEN 'FEFO'
                                    THEN CASE a.Product_Mfd WHEN NULL THEN 0 ELSE a.Product_Mfd END
                                ELSE a.Actual_Location_Id
                               END");

        /*  เปลี่ยนโครงสร้างการ query : COMMENT BY POR 2014-08-18
        $str = "SELECT DISTINCT a.Inbound_Id
                                ,a.Product_Id
                                ,a.Product_Code
                                ,b.Product_NameEN
                                ,a.Product_Status
				,a.Product_Sub_Status ,a.Unit_Id
                                ,a.Product_License,a.Product_Lot,a.Product_Serial,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd',CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp'
                                ,a.Balance_Qty
                                ,(a.Receive_Qty - a.PD_Reserv_Qty - a.Dispatch_Qty - a.Adjust_Qty) as Est_Balance_Qty
				,S1.public_name AS Unit_Value
				,S2.Dom_Code   AS Status_Code
				,S2.Dom_EN_Desc AS Status_Value
				,S3.Dom_Code   AS Sub_Status_Code
				,S3.Dom_EN_Desc AS Sub_Status_Value
				,L1.Location_Code AS Suggest_Location
				,L2.Location_Code AS Actual_Location
				,a.Actual_Location_Id
                                ,a.Unit_Price_Id
                                ,a.Price_Per_Unit
                                ,S4.Dom_EN_Desc AS Unit_Price_value
                                ,Pallet_Code
                                ,a.Receive_Qty
                                ,a.PD_Reserv_Qty
                                ,a.Dispatch_Qty
                                ,a.Adjust_Qty
                 FROM STK_T_Inbound a
                 LEFT OUTER JOIN STK_M_Product b ON a.Product_Code = b.Product_Code
                    JOIN CTL_M_UOM_Template_Language S1 ON a.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'
                    JOIN SYS_M_Domain S2 ON a.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS'

                    LEFT JOIN SYS_M_Domain S3 ON a.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS'
                    LEFT JOIN SYS_M_Domain S4 ON a.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code='PRICE_UNIT'
                    LEFT JOIN STK_M_Location L1 ON a.Suggest_Location_Id = L1.Location_Id
                    LEFT JOIN STK_M_Location L2 ON a.Actual_Location_Id = L2.Location_Id
                    LEFT JOIN STK_T_Pallet ON a.Pallet_Id=STK_T_Pallet.Pallet_Id
                 WHERE
                 (Receive_Qty - PD_Reserv_Qty - Dispatch_Qty - Adjust_Qty) > 0
                 AND b.ACTIVE ='Y'
                 AND a.Inbound_Id IN (" . $val . ")"; //Edit by kik (27-09-2013)
// Edit By Akkarapol, 10/01/2014, เปลี่ยนจากการ join กับตาราง SYS_M_Domain เพื่อหา Unit Name เป็นการ join กับ  CTL_M_UOM_Template_Language แทน เพื่อรองรับกับการทำ UOM
         */

        //$query = $this->db->query($str);
        //$query = $this->db->get();
        // echo $this->db->last_query();//exit();
        //return $query;
    //}

    function getListPreDispatchByProdCodeViaAjax($param = "", $putaway_rule = "FIFO", $est_balance_check = TRUE , $sort_full = FALSE) {
        $val = trim($param);

        $conf = $this->config->item('_xml');
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];
        
        $this->db->select("DISTINCT Product_Exp, a.Inbound_Id
                ,a.Product_Id
                ,a.Product_Code
                ,b.Product_NameEN
                ,a.Product_Status
                ,a.Product_Sub_Status ,a.Unit_Id
                ,a.Product_License,a.Product_Lot,a.Product_Serial,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd'
                ,CONVERT(varchar(10), Last_Mfd.Dispatch_Mfd, 103) AS 'Last_Mfd'
                ,CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp'
                ,a.Balance_Qty
                ,(a.Receive_Qty - a.PD_Reserv_Qty - a.Dispatch_Qty - a.Adjust_Qty) as Est_Balance_Qty
                ,S1.public_name AS Unit_Value
                ,S2.Dom_Code   AS Status_Code
                ,S2.Dom_EN_Desc AS Status_Value
                ,S3.Dom_Code   AS Sub_Status_Code
                ,S3.Dom_EN_Desc AS Sub_Status_Value
                ,L1.Location_Code AS Suggest_Location
                ,L2.Location_Code AS Actual_Location
                ,a.Actual_Location_Id
                ,a.Receive_Qty
                ,a.PD_Reserv_Qty
                ,a.Dispatch_Qty
                ,a.Adjust_Qty
                ,a.Receive_Date"
                );;

        if($conf_price_per_unit):
            $this->db->select("a.Unit_Price_Id
                ,a.Price_Per_Unit
                ,S4.Dom_EN_Desc AS Unit_Price_value");
        endif;

        if($conf_pallet):
            $this->db->select("Pallet_Code");
        endif;

        $this->db->from("STK_T_Inbound a");
        $this->db->join("STK_M_Product b","a.Product_Code = b.Product_Code","LEFT OUTER");
        $this->db->join("CTL_M_UOM_Template_Language S1","a.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->join("SYS_M_Domain S2","a.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS' AND S2.Dom_Active = 'Y' ");
        $this->db->join("SYS_M_Domain S3","a.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS' AND S3.Dom_Active = 'Y'","LEFT");
        $this->db->join("SYS_M_Domain S4","a.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code='PRICE_UNIT' AND S4.Dom_Active = 'Y'","LEFT");
        $this->db->join("STK_M_Location L1","a.Suggest_Location_Id = L1.Location_Id","LEFT");
        $this->db->join("STK_M_Location L2","a.Actual_Location_Id = L2.Location_Id","LEFT");

        if($conf_pallet):
            $this->db->join("STK_T_Pallet","a.Pallet_Id=STK_T_Pallet.Pallet_Id","LEFT");
        endif;

        //ADD Last MFD
        $this->db->join("(SELECT od.Product_Code,
                            max(od.Product_Mfd) AS Dispatch_Mfd
                            FROM STK_T_Order o
                            LEFT JOIN STK_T_Workflow w ON w.Flow_Id = o.Flow_Id
                            LEFT JOIN STK_T_Order_Detail od ON o.Order_Id = od.Order_Id
                            WHERE w.Process_Id = '2'
                            AND w.Present_State = '-2'
                            AND od.Active = 'Y'
                            GROUP BY od.Product_Code) AS Last_Mfd","a.Product_Code = Last_Mfd.Product_Code","LEFT");

        if ($est_balance_check) {
            $this->db->where("(Receive_Qty - PD_Reserv_Qty - Dispatch_Qty - Adjust_Qty) > 0");
        }

        $this->db->where("b.ACTIVE",'Y');
        $this->db->where("a.Inbound_Id IN (" . $val . ")");
//        p($sort_full);
        if ($putaway_rule == "FEFO") {
            //////simulate sort LOL^^
            if($sort_full == TRUE){

                 $this->db->order_by("Est_Balance_Qty", "DESC");
            }
            //////
            $this->db->order_by("cast(a.Product_Exp as datetime)", "ASC");
            $this->db->order_by("L2.Location_Priority", "ASC");
            $this->db->order_by("cast(a.Receive_Date as datetime)", "ASC");
        } else {
            $this->db->order_by("cast(a.Receive_Date as datetime)", "ASC");
        }

        $query = $this->db->get();
        
//        echo $this->db->last_query(); //exit;

        return $query;
    }    
    
    function getListPreDispatchByProdCodeViaAjaxLoose($param = "", $putaway_rule = "FIFO", $est_balance_check = TRUE) {
        $val = trim($param);
        $this->db->select("DISTINCT Product_Exp, a.Inbound_Id
                ,a.Product_Id
                ,a.Product_Code
                ,b.Product_NameEN
                ,a.Product_Status
                ,a.Product_Sub_Status ,a.Unit_Id
                ,a.Product_License,a.Product_Lot,a.Product_Serial,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd',CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp'
                ,a.Balance_Qty
                ,(a.Receive_Qty - a.PD_Reserv_Qty - a.Dispatch_Qty - a.Adjust_Qty) as Est_Balance_Qty
                ,S1.public_name AS Unit_Value
                ,S2.Dom_Code   AS Status_Code
                ,S2.Dom_EN_Desc AS Status_Value
                ,S3.Dom_Code   AS Sub_Status_Code
                ,S3.Dom_EN_Desc AS Sub_Status_Value
                ,L1.Location_Code AS Suggest_Location
                ,L2.Location_Code AS Actual_Location
                ,a.Actual_Location_Id
                ,a.Receive_Qty
                ,a.PD_Reserv_Qty
                ,a.Dispatch_Qty
                ,a.Adjust_Qty
                ,a.Receive_Date"
                );

        $this->db->select("Pallet_Code");

        $this->db->from("STK_T_Inbound a");
        $this->db->join("STK_M_Product b","a.Product_Code = b.Product_Code","LEFT OUTER");
        $this->db->join("CTL_M_UOM_Template_Language S1","a.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->join("SYS_M_Domain S2","a.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS' AND S2.Dom_Active = 'Y' ");
        $this->db->join("SYS_M_Domain S3","a.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS' AND S3.Dom_Active = 'Y'","LEFT");
        $this->db->join("SYS_M_Domain S4","a.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code='PRICE_UNIT' AND S4.Dom_Active = 'Y'","LEFT");
        $this->db->join("STK_M_Location L1","a.Suggest_Location_Id = L1.Location_Id","LEFT");
        $this->db->join("STK_M_Location L2","a.Actual_Location_Id = L2.Location_Id","LEFT");
        $this->db->join("STK_T_Pallet","a.Pallet_Id=STK_T_Pallet.Pallet_Id","LEFT");

        $this->db->where("b.ACTIVE",'Y');
        $this->db->where("a.Inbound_Id IN (" . $val . ")");
        $this->db->where("a.Receive_Qty > a.Balance_Qty");

        if ($putaway_rule == "FEFO") {
            $this->db->order_by("cast(Product_Exp as datetime)", "ASC");
            $this->db->order_by("cast(Receive_Date as datetime)", "ASC");
        } else {
            $this->db->order_by("cast(Receive_Date as datetime)", "ASC");
        }

        $query = $this->db->get();

        return $query;
    }
    
    
    public function selectWarehouse() {
        $sql = "SELECT * FROM STK_M_Warehouse;";
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function selectRenter() {
        $sql = "SELECT * FROM CTL_M_Contact WHERE IsRenter = 1;";
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function selectDispatchType() {
        $sql = "SELECT * FROM SYS_M_Domain WHERE Dom_Host_Code = 'DP_TYPE' AND Dom_Active = 'Y'; ";
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function queryFromString($str = "") {
        $query = $this->db->query($str);
        return $query;
    }

    public function queryDataFromFlowId($id) {
        $query = $this->db->query("SELECT
                                        Order_Id,Flow_Id,Document_No
                                        ,Doc_Refer_Ext,Doc_Refer_Int,Doc_Type
                                        ,Renter_Id,Vendor_Id
                                        ,Vendor_Driver_Name,Vendor_Car_No
										, CONVERT(VARCHAR(10),Estimate_Action_Date,101) as Estimate_Action_Date
										, CONVERT(VARCHAR(10),Actual_Action_Date,101) as Actual_Action_Date
										, CONVERT(VARCHAR(10),Delivery_Date,101) as Delivery_Date
										,Source_Id
                                        ,Destination_Id,Source_Type,Destination_Type
                                        ,Is_Pending,Create_By,Create_Date
                                        ,Modified_By,Modified_Date,Remark
                                    FROM STK_T_Order a WHERE Flow_Id = " . $id);

        return $query->result();
    }

    /** comment function by kik : 29140818 for midified function use GI dataBase Helper
    /*COMMENT BY POR 2013-12-06 ยกเลิก function นี้ไปเรียกใช้ query_from_OrderDetailId แทน เนื่องจากมีการเรียกชื่อผู้ทำรายการไม่ถูกต้อง
    // Add Distinct Data By Ball 20130930
    // Add Est_Balance_Qty and by kik 20131003
    public function queryDataFromOrderDetailId($id) {// Add a.Reason_Remark by Ton! 20130823
        $query = $this->db->query("SELECT DISTINCT
            a.Item_Id, b.Product_Code, b.Product_NameEN AS Full_Product_Name, SUBSTRING(b.Product_NameEN,0,30) AS Product_Name
            , a.Order_Id, a.Product_Id, a.Product_Code, a.Product_Status, a.Product_Sub_Status, a.Suggest_Location_Id, a.Actual_Location_Id
            , a.Old_Location_Id, a.Product_License, a.Product_Lot, a.Product_Serial, CONVERT(varchar(10),a.Product_Mfd,103) as 'Product_Mfd'
            , CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp'

            ,(c.Receive_Qty - c.PD_Reserv_Qty - c.Dispatch_Qty - c.Adjust_Qty) as Est_Balance_Qty

            ,c.Balance_Qty

            ,a.Reserv_Qty, a.Confirm_Qty, a.DP_Confirm_Qty, a.Unit_Id
            , a.Reason_Code, a.Remark, a.Reason_Remark, a.Inbound_Item_Id, a.Activity_Code, S1.Dom_EN_Desc AS Unit_Value, S2.Dom_Code   AS Status_Code
            , S2.Dom_EN_Desc AS Status_Value, S3.Dom_Code AS Sub_Status_Code, S3.Dom_EN_Desc AS Sub_Status_Value, L1.Location_Code AS Suggest_Location
            , L2.Location_Code AS Actual_Location, (Co.First_NameTH +' '+ Co.Last_NameTH) as Activity_By_Name
                FROM STK_T_Order_Detail a
                LEFT OUTER JOIN STK_M_Product b ON a.Product_Code = b.Product_Code AND b.Active='Y'
                LEFT OUTER JOIN STK_T_Inbound c ON a.Inbound_Item_Id = c.Inbound_Id
                LEFT OUTER JOIN SYS_M_Domain S1 ON a.Unit_Id = S1.Dom_Code AND S1.Dom_Host_Code='PROD_UNIT'
                JOIN SYS_M_Domain S2 ON a.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS'
                LEFT JOIN SYS_M_Domain S3 ON a.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS'
                LEFT JOIN STK_M_Location L1 ON a.Suggest_Location_Id = L1.Location_Id
                LEFT JOIN STK_M_Location L2 ON a.Actual_Location_Id = L2.Location_Id
                LEFT JOIN ADM_M_UserLogin U ON a.Activity_By = U.UserLogin_Id
                LEFT JOIN CTL_M_Contact Co ON U.Contact_Id = Co.Contact_Id
                WHERE a.Order_Id = " . $id . " AND b.Active = 'Y'
                AND a.Active = '".ACTIVE."'
");
        // Add By Akkarapol, 29/11/2013, เพิ่ม AND a.Active = '".ACTIVE."'  เข้าไปเพื่อเช็คเอาเฉพาะตัวที่ STK_T_Order_Detail.Active เป็น Y เท่านั้น
//        return $query->result(); // Comment By Akkarapol, 21/10/2013, อยากได้ return ที่มันเป็นค่า เพียวๆ เพื่อที่ว่า เวลาจะเรียกใช้ค่าแบบไหนก็สามารถทำได้ตามต้องการ
        return $query; // Add By Akkarapol, 21/10/2013, อยากได้ return ที่มันเป็นค่า เพียวๆ เพื่อที่ว่า เวลาจะเรียกใช้ค่าแบบไหนก็สามารถทำได้ตามต้องการ
    }
 */
    //ADD BY POR 2013-12-04 function ที่สร้างขึ้นมาแทน queryDataFromOrderDetailId เนื่องจากต้องการส่งค่า $state_edge เพิ่ม ถ้าแก้ไขเสร็จแล้ว ก็ให้ยกเลิกอันเก่าด้วยเด้อ
    //add select Price_Per_Unit,Unit_Price_Id,All_Price : by kik : 2013-13-11
//    public function query_from_OrderDetailId($id,$state_edge=0, $order_by = 'L1.Location_Code ASC') {// Add a.Reason_Remark by Ton! 20130823
//        /*
//         ADD BY POR 2013-12-04 เพิ่ม parameter 1 ตัวคือ $state_edge ไว้สำหรับบอกว่าต้องการทราบชื่อผู้ทำรายการในขั้นตอนไหน
//         เนื่องจากต้องนำข้อมูลมาจากตาราง STK_T_Logs_Action และข้อมูลในนั้นจะเก็บแต่ละขั้นตอนที่มีการ update flow เราจึงต้องรู้ว่า
//         ต้องการข้อมูลในขั้นตอนไหน ตัวแปร $state_edge สามารถดูค่าได้จากตาราง SYS_M_Stateedge หากไม่มีการส่งค่าเข้ามาระบบจะแสดง
//         ข้อมูลทุก step ซึ่งอาจทำให้ข้อมูลซ้ำกันก็แล้วแต่จุดประสงค์
//        */
//
//        if($state_edge==0):
//            $state="";
//        else:
//            $state = "AND activity_by.Edge_Id=$state_edge";
//        endif;
//        $query = $this->db->query("SELECT DISTINCT
//            a.Item_Id, b.Product_Code, b.Product_NameEN AS Full_Product_Name, SUBSTRING(b.Product_NameEN,0,30) AS Product_Name
//            , a.Order_Id, a.Product_Id, a.Product_Code, a.Product_Status, a.Product_Sub_Status, a.Suggest_Location_Id, a.Actual_Location_Id
//            , a.Old_Location_Id, a.Product_License, a.Product_Lot, a.Product_Serial, CONVERT(varchar(10),a.Product_Mfd,103) as 'Product_Mfd'
//            , CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp'
//            , a.Price_Per_Unit
//            , a.Unit_Price_Id
//            , a.All_Price
//            ,(c.Receive_Qty - c.PD_Reserv_Qty - c.Dispatch_Qty - c.Adjust_Qty) as Est_Balance_Qty
//
//            ,c.Balance_Qty
//
//            ,a.Reserv_Qty, a.Confirm_Qty, a.DP_Confirm_Qty, a.Unit_Id
//            , a.Reason_Code, a.Remark, a.Reason_Remark, a.Inbound_Item_Id, a.Activity_Code, S1.public_name AS Unit_Value, S2.Dom_Code   AS Status_Code
//            , S2.Dom_EN_Desc AS Status_Value, S3.Dom_Code AS Sub_Status_Code, S3.Dom_EN_Desc AS Sub_Status_Value, L1.Location_Code AS Suggest_Location
//            , L2.Location_Code AS Actual_Location
//
//            , a.Pallet_Id
//            , plt.Pallet_Code
//
//            , S4.Dom_EN_Desc AS Unit_Price_value
//            ,(activity_by.First_NameTH+' '+activity_by.Last_NameTH)  as Activity_By_Name
//            , a.DP_Type_Pallet
//                FROM STK_T_Order_Detail a
//                LEFT OUTER JOIN STK_M_Product b ON a.Product_Code = b.Product_Code AND b.Active='Y'
//                LEFT OUTER JOIN STK_T_Inbound c ON a.Inbound_Item_Id = c.Inbound_Id
//                LEFT OUTER JOIN CTL_M_UOM_Template_Language S1 ON a.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'
//                JOIN SYS_M_Domain S2 ON a.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS'
//                LEFT JOIN SYS_M_Domain S3 ON a.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS'
//                LEFT JOIN SYS_M_Domain S4 ON a.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code='PRICE_UNIT'
//                LEFT JOIN STK_M_Location L1 ON a.Suggest_Location_Id = L1.Location_Id
//                LEFT JOIN STK_M_Location L2 ON a.Actual_Location_Id = L2.Location_Id
//                LEFT JOIN vw_activity_by activity_by On activity_by.Order_Id = a.Order_Id AND activity_by.Item_Id=a.Item_Id ".$state."
//                LEFT JOIN STK_T_Pallet plt ON a.Pallet_Id = plt.Pallet_Id
//                WHERE a.Order_Id = " . $id . " AND b.Active = 'Y'
//                AND a.Active = '".ACTIVE."'
//
//                Order By {$order_by}
//
//            ");
//        // Edit By Akkarapol, 10/01/2014, เปลี่ยนจากการ join กับตาราง SYS_M_Domain เพื่อหา Unit Name เป็นการ join กับ  CTL_M_UOM_Template_Language แทน เพื่อรองรับกับการทำ UOM
//        // Add By Akkarapol, 29/11/2013, เพิ่ม AND a.Active = '".ACTIVE."'  เข้าไปเพื่อเช็คเอาเฉพาะตัวที่ STK_T_Order_Detail.Active เป็น Y เท่านั้น
////        return $query->result(); // Comment By Akkarapol, 21/10/2013, อยากได้ return ที่มันเป็นค่า เพียวๆ เพื่อที่ว่า เวลาจะเรียกใช้ค่าแบบไหนก็สามารถทำได้ตามต้องการ
////p($this->db->last_query()); exit();
//        return $query; // Add By Akkarapol, 21/10/2013, อยากได้ return ที่มันเป็นค่า เพียวๆ เพื่อที่ว่า เวลาจะเรียกใช้ค่าแบบไหนก็สามารถทำได้ตามต้องการ
//    }



    /**
     *
     * @param int $id
     * @param int $state_edge
     * @param string $order_by
     * @return array
     * @modified : 20140818 : by kik
     *
     */

    public function query_from_OrderDetailId($id, $order_by = 'L1.Location_Code ASC',$pallet_out = false,$sub_module=false,$module=false) {
//        echo $state_edge;exit();

        #Load config (add by kik : 20140708)
        $conf = $this->config->item('_xml'); // By ball : 20140707
        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        $conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];


        $state = "";
//        if($state_edge!=0):
//            $state = "AND activity_by.Edge_Id=$state_edge";
//        endif;

        if($sub_module!="" and $module !=""):
            $state = "AND activity_by.Sub_Module='$sub_module' AND activity_by.Module = '$module'";
        elseif($module !=""):
            $state = "AND activity_by.Module = '$module'";
        endif;

        /**
         * Select Zone
         */
        $this->db->select("DISTINCT a.Item_Id
                    , b.Product_Code
                    , b.Product_NameEN AS Full_Product_Name
                    , SUBSTRING(b.Product_NameEN,0,30) AS Product_Name
                    , a.Order_Id, a.Product_Id, a.Product_Code, a.Product_Status, a.Product_Sub_Status, a.Suggest_Location_Id, a.Actual_Location_Id
                    , a.Old_Location_Id, a.Product_License, a.Product_Lot, a.Product_Serial, CONVERT(varchar(10),a.Product_Mfd,103) as 'Product_Mfd'
                    , CONVERT(varchar(10), Last_Mfd.Dispatch_Mfd, 103) AS 'Last_Mfd',
                    , CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp'
                    , CAST ( (c.Receive_Qty - c.PD_Reserv_Qty - c.Dispatch_Qty - c.Adjust_Qty) AS decimal(10,3) ) as Est_Balance_Qty
                    ,c.Balance_Qty
                    ,a.Reserv_Qty, a.Confirm_Qty, a.DP_Confirm_Qty, a.Unit_Id
                    , a.Reason_Code, a.Remark, a.Reason_Remark, a.Inbound_Item_Id, a.Activity_Code, S1.public_name AS Unit_Value, S2.Dom_Code   AS Status_Code
                    , S2.Dom_EN_Desc AS Status_Value, S3.Dom_Code AS Sub_Status_Code, S3.Dom_EN_Desc AS Sub_Status_Value, L1.Location_Code AS Suggest_Location
                    , L2.Location_Code AS Actual_Location
                    , a.DP_Type_Pallet
         ");

        if(($sub_module!="" and $module !="") ||  ($sub_module != NULL  and $module != NULL)):
            $this->db->select("(CTL_M_Contact.First_NameTH+' '+CTL_M_Contact.Last_NameTH)  as Activity_By_Name");
        endif;

         #check query of Invoice (by kik : 20140708)
        if($conf_inv):
            $this->db->select("a.Invoice_Id
                ,STK_T_Invoice.Invoice_No");
        endif; // end of query of Invoice


        #check query of Container (by kik : 20140708)
        if($conf_cont):
            $this->db->select("a.Cont_Id
                ,CTL_M_Container.Cont_No
                ,CTL_M_Container_Size.Cont_Size_No
                ,CTL_M_Container_Size.Cont_Size_Unit_Code");
        endif; // end of query of Container


        #check query of Price per unit (by kik : 20140708)
        if($conf_price_per_unit):
            $this->db->select("a.Price_Per_Unit
            , a.Unit_Price_Id
            , a.All_Price
            , S4.Dom_EN_Desc AS Unit_Price_value");
        endif; // end of query of Price per unit


        #check query of Pallet (by kik : 20140708)
        if($conf_pallet):

            if($pallet_out):
                $this->db->select("a.Pallet_Id_Out
                , plt.Pallet_Code");
            else:
                $this->db->select("a.Pallet_Id
                , plt.Pallet_Code");
            endif;

        endif; // end of query of Pallet

        $this->db->select('0 as Uom_Qty');
        $this->db->select('b.Standard_Unit_In_Id,UOM_Temp_Prod.public_name AS Uom_Unit_Val');
        /**
         * From and Join Zone
         */
        $this->db->from("STK_T_Order_Detail a ");
        $this->db->join("STK_M_Product b", "a.Product_Code = b.Product_Code AND b.Active='Y'","LEFT OUTER");
        $this->db->join("STK_T_Inbound c", "a.Inbound_Item_Id = c.Inbound_Id","LEFT OUTER");
        $this->db->join("CTL_M_UOM_Template_Language S1", "a.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'","LEFT OUTER");
		$this->db->join("CTL_M_UOM_Template_Language UOM_Temp_Prod", "b.Standard_Unit_In_Id = UOM_Temp_Prod.CTL_M_UOM_Template_id AND UOM_Temp_Prod.language = '" . $this->config->item('lang3digit') . "'","LEFT OUTER");
        $this->db->join("SYS_M_Domain S2", "a.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS' AND S2.Dom_Active = 'Y'","LEFT OUTER");
        $this->db->join("SYS_M_Domain S3", "a.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS' AND S3.Dom_Active = 'Y' ","LEFT");
        $this->db->join("SYS_M_Domain S4", "a.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code='PRICE_UNIT' AND S4.Dom_Active = 'Y' ","LEFT");
        $this->db->join("STK_M_Location L1", "a.Suggest_Location_Id = L1.Location_Id","LEFT");
        $this->db->join("STK_M_Location L2", "a.Actual_Location_Id = L2.Location_Id ","LEFT");

        if(($sub_module!="" and $module !="") ||  ($sub_module != NULL  and $module != NULL)):
            $this->db->join("STK_T_Logs_Action activity_by", "activity_by.Order_Id = a.Order_Id AND activity_by.Item_Id=a.Item_Id ".$state."", "LEFT");
            $this->db->join("ADM_M_UserLogin", "ADM_M_UserLogin.UserLogin_Id = activity_by.Activity_By", "LEFT");
            $this->db->join("CTL_M_Contact", "CTL_M_Contact.Contact_Id = ADM_M_UserLogin.Contact_Id", "LEFT");
        endif;



        #check query of Pallet (by kik : 20140708)
        if($conf_pallet):
            if($pallet_out):
                $this->db->join("STK_T_Pallet plt", "a.Pallet_Id_Out = plt.Pallet_Id",'LEFT');
            else:
                $this->db->join("STK_T_Pallet plt", "a.Pallet_Id = plt.Pallet_Id",'LEFT');
            endif;

        endif; // end of query of Pallet


        #check query of Price per unit (by kik : 20140708)
        if($conf_price_per_unit):
            $this->db->join("SYS_M_Domain unitprice", "a.Unit_Price_Id = unitprice.Dom_Code  AND unitprice.Dom_Active = 'Y'", "LEFT");
        endif; // end of query of Price per unit



        #check query of Invoice (by kik : 20140708)
        if($conf_inv):
            $this->db->join("STK_T_Invoice", "STK_T_Invoice.Invoice_Id = a.Invoice_Id" ,"LEFT");
        endif; // end of query of Invoice



        #check query of Container  (by kik : 20140708)
        if($conf_cont):
            $this->db->join("CTL_M_Container", "CTL_M_Container.Cont_Id = a.Cont_Id" ,"LEFT");
            $this->db->join("CTL_M_Container_Size", "CTL_M_Container_Size.Cont_Size_Id = CTL_M_Container.Cont_Size_Id" ,"LEFT");
        endif; // end of query of Container

        //Last MFD 
        $this->db->join(" (select od.Product_Code,
                            max(od.Product_Mfd) as Dispatch_Mfd                
                            from STK_T_Order o
                            left join STK_T_Workflow w on w.Flow_Id = o.Flow_Id
                            left join STK_T_Order_Detail od on o.Order_Id = od.Order_Id
                            where w.Process_Id = '2' and w.Present_State = '-2' and od.Active = 'Y' 
                            group by  od.Product_Code) AS Last_Mfd", "a.Product_Code = Last_Mfd.Product_Code" ,"LEFT");



        /**
         * Where Zone
         */
        if ( is_array( $id ) ) :
            $this->db->where("a.Order_Id In (". implodeArrayQuote( $id ) .")");
        else :
            $this->db->where("a.Order_Id",$id);
        endif;
        $this->db->where("a.Active",ACTIVE);
        $this->db->where("b.Active",ACTIVE);

        $this->db->order_by($order_by);

        $query = $this->db->get();
        //echo $this->db->last_query();exit();
        return $query;
    }

    function getUnitInbound($inbound_id) {
        $this->db->select("Unit_Id");
        $this->db->from("STK_T_Inbound");
        $this->db->where("Inbound_Id", $inbound_id);
        $query = $this->db->get();
        $result = $query->result();
        return $result[0]->Unit_Id;
    }

    //START BY POR 2013-10-17 update ตาราง order_detail เพื่อให้มีค่า Inbound_Item_Id
    function process_update_Inbound_ItemId($order_id, $order_detail_data) {

        $query = $this->db->query("EXEC [dbo].[updateInboundItemId] " . $order_id . ",'" . $order_detail_data . "'");
    //   p($this->db->last_query());
    //   exit();

        return $query;
    }

    #START BY POR 2013-10-24 function สำหรับ update inbound_item_id ในตาราง order_detail โดย update เฉพาะ item_id ที่ยังไม่มี inbound_item_id เท่านั้น
    #comment by kik  : 2013-11-14
//    function updateInboundItemId($inbound_id, $item_id) {
//
//        $data['Inbound_Item_Id'] = $inbound_id;
//        $this->db->where('Item_Id', $item_id);
//        $this->db->where('Inbound_Item_Id IS NULL');
//        $this->db->update('STK_T_Order_Detail', $data);
//
//        $afftectedRows = $this->db->affected_rows();
//
//        if ($afftectedRows > 0) { //แสดงว่ามีการ update ข้อมูล มากกว่า 0 record
//            return 0; //Update success.
//        } else if ($afftectedRows == 0) { //ไม่ update ข้อมูลเนื่องจากไม่เข้าเงื่อนไข
//            return 1;
//        } else { //code error
//            return 2; //Update unsuccess.
//        }
//
//    }

    // Add by kik : 2013-11-14
     function updateInboundItemId($order_detail, $where , $where_detail=NULL) {
        $this->db->where($where);

        if($where_detail != NULL){
            $this->db->where($where_detail);
        }

        $this->db->update("STK_T_Order_Detail", $order_detail);
        // echo $this->db->last_query();
        $afftectedRows = $this->db->affected_rows(); // Add by Ton! 20130521
        if ($afftectedRows > 0) { //แสดงว่ามีการ update ข้อมูล มากกว่า 0 record
            return 0; //Update success.
        } else if ($afftectedRows == 0) { //ไม่ update ข้อมูลเนื่องจากไม่เข้าเงื่อนไข
            return 1;
        } else { //code error
            return 2; //Update unsuccess.
        }

    }

    public function get_document_property($flow_id) {
    	$this->db->select("*");
    	$this->db->from("STK_T_Workflow");
    	$this->db->join("STK_T_Order", "STK_T_Workflow.flow_id = STK_T_Order.flow_id", "INNER");
    	$this->db->where("STK_T_Workflow.flow_id", $flow_id);
    	$query = $this->db->get();
    	return $query->result();
    }

    public function get_document_data($order_id) {
    	$this->db->select("Item_Id, Product_Code, Product_Id, Suggest_Location_Id, Reserv_Qty");
    	$this->db->from("STK_T_Order_Detail");
    	$this->db->where("STK_T_Order_Detail.Order_Id", $order_id);
    	$query = $this->db->get();
    	return $query->result();
    }

    #END

}
