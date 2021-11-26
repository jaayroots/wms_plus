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
class transfer_stock_model extends CI_Model {

    //put your code here
    function __construct() {
        parent::__construct();
    }

    function getTransferStockAll() {
        $this->db->select("a.Order_Id as Id,a.Document_No,a.Doc_Type,c.Contact_Code,a.Delivery_Date
        ,b.Product_Code,b.Reserv_Qty,b.Confirm_Qty,b.Unit_Id,b.Remark");
        $this->db->from("STK_T_Order a");
        $this->db->join("STK_T_Order_detail b", "a.Order_Id = b.Order_Id ", "left outer");
        $this->db->join("CTL_M_Contact c", "a.Renter_Id = c.Contact_Id", "left");
        $query = $this->db->get();
        return $query;
    }

    //comment by kik (08-10-2013)
//    function getTransferStockByProdCode($product_code = "",$productStatus = "",$productSubStatus = "",$productLot="",$productSerial="",$productMfd="",$productExp="") {
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
//        
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
//    
//    function getTransferStockByProdCode($param="",$productStatus = "",$productSubStatus = ""){
//            $val = trim($param);
//            $sql_getProductSubStatus = '(select Location_Id 
//                                    from STK_M_Location 
//                                    where Storage_Id in (
//                                    select storage_id from STK_M_Storage where StorageType_Id in (
//                                    select storageType_Id from  STK_M_Storage_Type where StorageType_Code  NOT IN
//                                    (select DOM_CODE from SYS_M_Domain where Dom_Host_Code = "NODISP_STORAGE"))))';
//            $this->db->select("
//                    STK_T_Inbound.Inbound_id as Inbound_Id
//                    ,STK_T_Inbound.product_code
//                    ,STK_M_Product.Product_NameEN
//                    ,STK_T_Inbound.Product_Status
//                    ,STK_M_Location.Location_Code
//                    ,STK_M_Location.Location_Id
//                    ,STK_T_Inbound.Product_License
//                    ,STK_T_Inbound.Product_lot
//                    ,STK_T_Inbound.Product_Serial
//                    ,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd'
//                    ,CONVERT(varchar(10),STK_T_Inbound.Product_Exp,103) as 'Product_Exp' 
//                    ,(STK_T_Inbound.Receive_Qty - STK_T_Inbound.PD_Reserv_Qty - STK_T_Inbound.Dispatch_Qty - STK_T_Inbound.Adjust_Qty) as Est_Balance_Qty
//                    ,STK_T_Inbound.Balance_Qty 
//                    ,STK_M_Product.Product_Id
//                    ,STK_M_Product.Product_Code
//                    ,SYS_M_Domain.Dom_EN_Desc as Product_Sub_Status
//                    ",false);// Edit by Ton! 20130808 *add c.Location_Id
//            
//            $this->db->from("STK_T_Inbound");
//            $this->db->join("STK_M_Product","STK_T_Inbound.Product_Code = STK_M_Product.Product_Code","left outer");
//            $this->db->join("STK_M_Location","STK_T_Inbound.Actual_Location_Id = STK_M_Location.Location_Id","Left");
//            $this->db->join("STK_M_Storage","STK_M_Storage.Storage_Id = STK_M_Location.Storage_Id");
//            $this->db->join("STK_M_Storage_Type","STK_M_Storage.StorageType_Id = STK_M_Storage_Type.StorageType_Id");
//            $this->db->join("SYS_M_Domain", "STK_T_Inbound.Product_Sub_Status = SYS_M_Domain.Dom_Code");
//
//            $this->db->where("STK_T_Inbound.Balance_Qty > ",0);
//            $this->db->where("LOWER(STK_T_Inbound.Product_Status) != ",'pending');
//            $this->db->where("STK_M_Location.Location_Id  IN ".$sql_getProductSubStatus);
//            $this->db->where("STK_T_Inbound.Active",'Y' );
//            $this->db->where("STK_M_Product.Active",'Y' );
//                //Product Status
//                if($productStatus != ""){
//                    $this->db->where("STK_T_Inbound.Product_Status", $productStatus);
//                }else{
//                    $this->db->where("LOWER(STK_T_Inbound.Product_Status) != ", 'pending');
//                }
//
//                //Product Sub Status
//                if($productSubStatus != ""){
//                    $this->db->where("STK_T_Inbound.Product_Sub_Status", $productSubStatus);
//                }
//        
//                //Product Code
//		if($val!=""){
//			$this->db->like('STK_T_Inbound.Product_Code', $val);
//			$this->db->or_like('STK_M_Product.Product_NameEN', $val); 
//			$this->db->or_like('STK_T_Inbound.Product_Serial', $val); 
//        }
//
//        $query = $this->db->get();
//      //  echo $this->db->last_query();  
//        return $query;
//    }
//    comment by kik (08-10-2013)
//    function getTransferStockByProdCode($param="",$productStatus = "",$productSubStatus = ""){
//            $val = trim($param);
//            $sql_getProductSubStatus = '(select Location_Id 
//                                    from STK_M_Location 
//                                    where Storage_Id in (
//                                    select storage_id from STK_M_Storage where StorageType_Id in (
//                                    select storageType_Id from  STK_M_Storage_Type where StorageType_Code  NOT IN
//                                    (select DOM_CODE from SYS_M_Domain where Dom_Host_Code = "NODISP_STORAGE"))))';
//            $this->db->select("
//                    STK_T_Inbound.Inbound_id as Inbound_Id
//                    ,STK_T_Inbound.product_code
//                    ,STK_M_Product.Product_NameEN
//                    ,STK_T_Inbound.Product_Status
//                    ,STK_M_Location.Location_Code
//                    ,STK_M_Location.Location_Id
//                    ,STK_T_Inbound.Product_License
//                    ,STK_T_Inbound.Product_lot
//                    ,STK_T_Inbound.Product_Serial
//                    ,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd'
//                    ,CONVERT(varchar(10),STK_T_Inbound.Product_Exp,103) as 'Product_Exp' 
//                    ,(STK_T_Inbound.Receive_Qty - STK_T_Inbound.PD_Reserv_Qty - STK_T_Inbound.Dispatch_Qty - STK_T_Inbound.Adjust_Qty) as Est_Balance_Qty
//                    ,STK_T_Inbound.Balance_Qty 
//                    ,STK_M_Product.Product_Id
//                    ,STK_M_Product.Product_Code
//                    ,SYS_M_Domain.Dom_EN_Desc as Product_Sub_Status
//                    ",false);// Edit by Ton! 20130808 *add c.Location_Id
//            
//            $this->db->from("STK_T_Inbound");
//            $this->db->join("STK_M_Product","STK_T_Inbound.Product_Code = STK_M_Product.Product_Code","left outer");
//            $this->db->join("STK_M_Location","STK_T_Inbound.Actual_Location_Id = STK_M_Location.Location_Id","Left");
//            $this->db->join("STK_M_Storage","STK_M_Storage.Storage_Id = STK_M_Location.Storage_Id");
//            $this->db->join("STK_M_Storage_Type","STK_M_Storage.StorageType_Id = STK_M_Storage_Type.StorageType_Id");
//            $this->db->join("SYS_M_Domain", "STK_T_Inbound.Product_Sub_Status = SYS_M_Domain.Dom_Code");
//
//            $this->db->where("STK_T_Inbound.Balance_Qty > ",0);
//            $this->db->where("LOWER(STK_T_Inbound.Product_Status) != ",'pending');
//            $this->db->where("STK_M_Location.Location_Id  IN ".$sql_getProductSubStatus);
//            $this->db->where("STK_T_Inbound.Active",'Y' );
//            $this->db->where("STK_M_Product.Active",'Y' );
//                //Product Status
//                if($productStatus != ""){
//                    $this->db->where("STK_T_Inbound.Product_Status", $productStatus);
//                }else{
//                    $this->db->where("LOWER(STK_T_Inbound.Product_Status) != ", 'pending');
//                }
//
//                //Product Sub Status
//                if($productSubStatus != ""){
//                    $this->db->where("STK_T_Inbound.Product_Sub_Status", $productSubStatus);
//                }
//        
//                //Product Code
//		if($val!=""){
//			$this->db->like('STK_T_Inbound.Product_Code', $val);
//			$this->db->or_like('STK_M_Product.Product_NameEN', $val); 
//			$this->db->or_like('STK_T_Inbound.Product_Serial', $val); 
//        }
//
//        $query = $this->db->get();
//      //  echo $this->db->last_query();  
//        return $query;
//    }
//    
//    function getTransferStockByProdCode($param=""){
//              $val = trim($param);
//        $this->db->select("
//		a.Inbound_id as Inbound_Id,a.product_code,b.Product_NameEN,a.Product_Status,c.Location_Code, c.Location_Id
//        ,a.Product_License,a.Product_lot,a.Product_Serial,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd',CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp' 
//		,(a.Receive_Qty - (a.PD_Reserv_Qty + a.Adjust_Qty)) as Est_Balance_Qty
//		,a.Balance_Qty 
//		,b.Product_Id
//		,b.Product_Codez",false);// Edit by Ton! 20130808 *add c.Location_Id
//        $this->db->from("STK_T_Inbound a");
//        $this->db->join("STK_M_Product b","a.Product_Code = b.Product_Code","left outer");
//        $this->db->join("STK_M_Location c","a.Actual_Location_Id = c.Location_Id","Left");
//        $this->db->join("STK_M_Storage d","d.Storage_Id = c.Storage_Id");
//        $this->db->join("STK_M_Storage_Type e","d.StorageType_Id = e.StorageType_Id");
//
//		$this->db->where("(a.Receive_Qty - (a.PD_Reserv_Qty + a.Adjust_Qty))>",0);
//        $this->db->where("LOWER(a.Product_Status) != ",'pending');
//        $this->db->where("StorageType_Code != ","ST05");
//        $this->db->where("a.Active",'Y' );
//		if($val!=""){
//			$this->db->like('a.Product_Code', $val);
//			$this->db->or_like('b.Product_NameEN', $val); 
//			$this->db->or_like('a.Product_Serial', $val); 
//        }
//
//        $query = $this->db->get();
//      //  echo $this->db->last_query();  
//        return $query;
//    }

    function getListTransferStockByProdCode($param = "") {
        $val = trim($param);
        $str = "SELECT a.Inbound_id,a.product_code,b.Product_NameEN,a.Product_Status 
                ,a.Product_License,a.Product_lot,a.Product_Serial,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd',CONVERT(varchar(10),a.Product_Exp,105) as 'Product_Exp' 
                ,a.Temp_Balance_Qty
                 FROM STK_T_Inbound a 
                 LEFT OUTER JOIN STK_M_Product b ON a.Product_Code = b.Product_Code
                 WHERE a.Inbound_id IN (" . $val . ")";
        $query = $this->db->query($str);
        return $query->result();
    }

    //Edit by kik ( 16-09-2013)
    function getListTransferStockByProdCodeViaAjax($param = "") {
        $val = trim($param);
        $str = "SELECT 
            a.Inbound_Id
            ,a.product_code
            ,b.Product_NameEN
            ,a.Product_Status 
            ,SYS_M_Domain.Dom_EN_Desc as Product_Sub_Status
            ,a.Product_License
            ,a.Product_lot
            ,a.Product_Serial
            ,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd'
            ,CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp' 
            ,a.Balance_Qty 
            ,(a.Receive_Qty - a.PD_Reserv_Qty - a.Dispatch_Qty - a.Adjust_Qty) as Est_Balance_Qty
            , c.Location_Id
            , c.Location_Code
            ,SYS_M_Domain.Dom_Code   AS Sub_Status_Code
            ,a.Unit_Id -- add by kik (27-09-2013)
            ,a.Unit_Price_Id
            ,a.Price_Per_Unit
            ,domain2.Dom_EN_Desc AS Unit_Price_value

            ,STK_T_Pallet.Pallet_Code

            FROM STK_T_Inbound a 
                 LEFT OUTER JOIN STK_M_Product b ON a.Product_Code = b.Product_Code and b.Active='Y'
                 LEFT JOIN STK_M_Location c ON a.Actual_Location_Id = c.Location_Id
                 JOIN SYS_M_Domain ON a.Product_Sub_Status = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Active = 'Y'
                 LEFT JOIN SYS_M_Domain as domain2 ON domain2.Dom_Code=a.Unit_Price_Id AND domain2.Dom_Host_Code='PRICE_UNIT' AND domain2.Dom_Active = 'Y'
                 
                 LEFT JOIN STK_T_Pallet ON a.Pallet_Id = STK_T_Pallet.Pallet_Id
                 
            WHERE a.Inbound_id IN (" . $val . ")"; // Edit by Ton! 20130808 *add LEFT JOIN STK_M_Location c ON a.Actual_Location_Id = c.Location_Id
        $query = $this->db->query($str);

        return $query;
    }

    #comment by kik 16-09-2013
//    function getListTransferStockByProdCodeViaAjax($param=""){
//        $val = trim($param);
//        $str = "SELECT a.Inbound_Id,a.product_code,b.Product_NameEN,a.Product_Status 
//                ,a.Product_License,a.Product_lot,a.Product_Serial,CONVERT(varchar(10),Product_Mfd,103) as 'Product_Mfd',CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp' 
//               -- ,a.Temp_Balance_Qty 
//               ,a.Balance_Qty , c.Location_Id, c.Location_Code
//                 FROM STK_T_Inbound a 
//                 LEFT OUTER JOIN STK_M_Product b ON a.Product_Code = b.Product_Code
//                 LEFT JOIN STK_M_Location c ON a.Actual_Location_Id = c.Location_Id
//                 WHERE a.Inbound_id IN (".$val.")";// Edit by Ton! 20130808 *add LEFT JOIN STK_M_Location c ON a.Actual_Location_Id = c.Location_Id
//        $query = $this->db->query($str);
//        return $query;
//    }

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

    public function selectDispatchType() {// Duplicate Function !!
        $sql = "SELECT * FROM SYS_M_Domain WHERE Dom_Host_Code = 'DP_TYPE'; ";
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

    #add by kik (16-09-2013)
    #add Inbound_Id by kik (07-10-2013)
    #join STK_M_Location add query STK_M_Location.Location_Code by kik (08-10-2013)
    # Add Est_Balance_Qty and by kik 20131008
    # Add select Price_Per_Unit,Unit_Price_Id,All_Price,Unit_Price_value by kik : 20140116
    public function queryDataFromOrderDetailId($id) {
        $query = $this->db->query("SELECT  
                        a.Item_Id
                        ,b.Product_Code 
                        ,b.Product_NameEN
                        ,a.Order_Id
                        ,a.Product_Code
                        ,a.Product_Status
                        ,SYS_M_Domain.Dom_EN_Desc as Product_Sub_Status
                        ,SYS_M_Domain.Dom_Code   AS Sub_Status_Code
                        ,a.Suggest_Location_Id
                        ,a.Actual_Location_Id
                        ,a.Old_Location_Id
                        ,a.Product_License
                        ,a.Product_Lot
                        ,a.Product_Serial
                        ,CONVERT(varchar(10),a.Product_Mfd,103) as 'Product_Mfd'
                        ,CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp'
                        ,c.Balance_Qty
                        ,(c.Receive_Qty - c.PD_Reserv_Qty - c.Dispatch_Qty - c.Adjust_Qty) as Est_Balance_Qty
                        ,a.Reserv_Qty
                        ,a.Confirm_Qty
                        ,a.Unit_Id
                        ,a.Reason_Code
                        ,a.Remark 
                        ,a.Price_Per_Unit    
                        ,a.Unit_Price_Id 
                        ,a.All_Price 
                        ,c.Inbound_Id
                        ,STK_M_Location.Location_Code
                        ,domain2.Dom_EN_Desc AS Unit_Price_value
                FROM STK_T_Order_Detail a 
                    LEFT JOIN STK_M_Product b ON a.Product_Code = b.Product_Code and b.Active='Y'
                    LEFT JOIN STK_T_Inbound c ON a.Inbound_Item_Id = c.Inbound_Id
                    LEFT JOIN SYS_M_Domain ON a.Product_Sub_Status = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Active = 'Y'
                    LEFT JOIN SYS_M_Domain as domain2 ON a.Unit_Price_Id = domain2.Dom_Code  AND domain2.Dom_Host_Code='PRICE_UNIT' AND domain2.Dom_Active = 'Y'
                    LEFT JOIN STK_M_Location ON c.Actual_Location_Id = STK_M_Location.Location_Id   
                WHERE a.Order_Id = " . $id);

//        return $query->result(); // Comment By Akkarapol, 21/10/2013, อยากได้ return ที่มันเป็นค่า เพียวๆ เพื่อที่ว่า เวลาจะเรียกใช้ค่าแบบไหนก็สามารถทำได้ตามต้องการ
        return $query; // Add By Akkarapol, 21/10/2013, อยากได้ return ที่มันเป็นค่า เพียวๆ เพื่อที่ว่า เวลาจะเรียกใช้ค่าแบบไหนก็สามารถทำได้ตามต้องการ
    }

    #end add by kik (16-09-2013)
    #commeny by kik (16-09-2013)
//        public function queryDataFromOrderDetailId($id){
//       $query = $this->db->query("SELECT  
//                        a.Item_Id
//                        ,b.Product_Code 
//                        ,b.Product_NameEN
//                        ,a.Order_Id
//                        ,a.Product_Code
//                        ,a.Product_Status
//                        ,a.Suggest_Location_Id
//                        ,a.Actual_Location_Id
//                        ,a.Old_Location_Id
//                        ,a.Product_License
//                        ,a.Product_Lot
//                        ,a.Product_Serial
//                        ,CONVERT(varchar(10),a.Product_Mfd,103) as 'Product_Mfd'
//                        ,CONVERT(varchar(10),a.Product_Exp,103) as 'Product_Exp'
//                        ,c.Balance_Qty
//                        ,a.Reserv_Qty
//                        ,a.Confirm_Qty
//                        ,a.Unit_Id
//                        ,a.Reason_Code
//                        ,a.Remark FROM STK_T_Order_Detail a 
//                LEFT JOIN STK_M_Product b ON a.Product_Code = b.Product_Code 
//                LEFT JOIN STK_T_Inbound c ON a.Inbound_Item_Id = c.Inbound_Id
//                WHERE a.Order_Id = ".$id);
//        
//        return $query->result();
//    }

    public function addNewLocation($order) {
        $this->db->insert("STK_T_Inbound", $order);
        //echo $this->db->last_query();
        $newProdId = $this->db->insert_id();
        if ($newProdId > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

}
