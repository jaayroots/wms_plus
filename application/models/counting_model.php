<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of counting_model
 *
 * @author Pong-macbook
 */
class counting_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function countingMovementList($type) {
//        $str = "SELECT    i.Product_Code,i.Actual_Location_Id ,'IN' 
//                            ,l.Location_Id ,l.Warehouse_Id,w.Warehouse_NameEN 
//                            ,l.Storage_Id ,l.Zone_Id
//                    FROM STK_T_Inbound i , STK_M_Location l
//                    LEFT OUTER JOIN STK_M_Warehouse w 
//                    ON l.Warehouse_Id = w.Warehouse_Id
//                    WHERE DATEDIFF(Day,i.Receive_Date,DATEADD(Day,-1,getdate())) = 0 /*movement of Receive*/
//                    AND i.Actual_Location_Id = l.Location_Id
//                    
//                    UNION ALL 
//                    
//                    SELECT o.Product_Code,o.Actual_Location_Id ,'OUT' 
//                    ,l.Location_Id ,l.Warehouse_Id ,mw.Warehouse_NameEN
//                    , l.Storage_Id ,l.Zone_Id
//                    FROM STK_T_Outbound o , STK_M_Location l
//                    LEFT OUTER JOIN STK_M_Warehouse mw 
//                    ON l.Warehouse_Id = mw.Warehouse_Id
//                    
//                    WHERE DATEDIFF(Day,o.Dispatch_Date,DATEADD(Day,-1,getdate())) = 0 /*movement of Dispatch*/
//                    AND o.Actual_Location_Id = l.Location_Id 
//                    ORDER BY l.Warehouse_Id ,l.Storage_Id ,l.Zone_Id,l.Location_Id asc ";
        /*        $str = "SELECT 
          i.Product_Id as 'Id'
          ,i.Product_Code
          ,p.Product_NameEN
          ,l.Location_Code
          ,i.Product_Lot AS Lot
          ,i.Product_Serial AS Serial
          ,i.Balance_Qty AS QTY
          ,i.Actual_Location_Id as Actual_Location_Id
          FROM
          STK_T_Inbound i
          LEFT OUTER JOIN STK_M_Product p ON i.Product_Id = p.Product_Id
          LEFT OUTER JOIN STK_M_Location l ON i.Actual_Location_Id = l.Location_Id
          WHERE DATEDIFF(Day,i.Receive_Date,DATEADD(Day,-1,getdate())) = 0
          --WHERE DATEDIFF(Day,i.Receive_Date,DATEADD(Day,-2,getdate())) = 0

          UNION ALL

          SELECT
          o.Product_Id as 'Id'
          , o.Product_Code
          ,p.Product_NameEN
          ,l.Location_Code
          ,o.Product_Lot AS Lot
          ,o.Product_Serial AS Serial
          ,o.Dispatch_Qty AS QTY
          ,o.Actual_Location_Id as Actual_Location_Id

          FROM STK_T_Outbound o
          LEFT OUTER JOIN STK_M_Product p
          ON o.Product_Id = p.Product_Id
          LEFT OUTER JOIN STK_M_Location l
          ON o.Actual_Location_Id = l.Location_Id

          WHERE DATEDIFF(Day,o.Dispatch_Date,DATEADD(Day,-1,getdate())) = 0 "; */

        // Modify By Ball
        // p($type);
        // exit;

        $str = "SELECT DISTINCT
        i.Product_Id as 'Id'
        ,i.Product_Code
        ,p.Product_NameEN 
        ,l.Location_Code
        ,i.Product_Lot AS Lot
        ,i.Product_Serial AS Serial
        ,i.Balance_Qty AS QTY
        ,i.Actual_Location_Id as Actual_Location_Id
        ,l.Location_Code as Actual_Location_Code
        ,i.Flow_Id
        ,'INBOUND' as Item_From
        ,i.Inbound_Id as Item_From_Id
        ,i.Product_Status
        ,i.Product_Sub_Status
          ,i.Product_License
           ,CONVERT(VARCHAR, CAST(i.Product_Mfd AS DATE), 121) AS Product_Mfd
           ,CONVERT(VARCHAR, CAST(i.Product_Exp AS DATE), 121) AS Product_Exp
        ,i.Unit_Id
        ,pal.Pallet_Code
        ,loc.Location_Code
        ,SUBSTRING(loc.Location_Code, 1, 1) AS Room
        ,SUBSTRING(loc.Location_Code, 1, 3) AS Row
        ,SUBSTRING(loc.Location_Code, 5, 2) AS Colunm
        ,SUBSTRING(loc.Location_Code, 8, 1) AS Level

    FROM 
        STK_T_Inbound i
        LEFT OUTER JOIN STK_M_Product p ON i.Product_Id = p.Product_Id
        LEFT OUTER JOIN STK_M_Location l ON i.Actual_Location_Id = l.Location_Id
        LEFT JOIN STK_T_Order io_tb On i.Flow_Id = io_tb.Flow_Id
        LEFT JOIN STK_T_Pallet pal ON i.Pallet_Id = pal.Pallet_Id
        LEFT JOIN STK_M_Location loc ON i.Actual_Location_Id = loc.Location_Id

    WHERE 1=1 ";
        if(isset($type)){
            $str .="AND i.Pallet_Id IS NOT NULL";
        }
    $str .="
    
    and DATEDIFF(Day,i.Receive_Date,DATEADD(Day,-1,getdate())) = 0 
    And i.Active = 'Y' AND i.Balance_Qty != 0 And i.Inbound_Id Not In 
    --(Select Item_From_Id From STK_T_Counting_Detail Where Item_From = 'INBOUND')
    (Select Item_From_Id
        From STK_T_Counting_Detail 
        INNER JOIN STK_T_Counting counting On counting.Order_Id = STK_T_Counting_Detail.Order_Id
        INNER JOIN STK_T_Workflow flow On flow.Flow_Id = counting.Flow_Id
        Where Item_From = 'INBOUND' AND flow.Present_State != -1) 
                         
    UNION ALL 
     
    SELECT DISTINCT
    o.Product_Id as 'Id'
    ,o.Product_Code
    ,p.Product_NameEN
    ,l.Location_Code
    ,o.Product_Lot AS Lot
    ,o.Product_Serial AS Serial        		
    ,o.Dispatch_Qty AS QTY
    ,o.Actual_Location_Id as Actual_Location_Id
    ,l.Location_Code as Actual_Location_Code
    ,o.Flow_Id
    ,'OUTBOUND' as Item_From
    ,o.Outbound_Id as Item_From_Id
       ,o.Product_Status
      ,o.Product_Sub_Status
       ,o.Product_License
       ,CONVERT(VARCHAR, CAST(o.Product_Mfd AS DATE), 121) AS Product_Mfd
       ,CONVERT(VARCHAR, CAST(o.Product_Exp AS DATE), 121) AS Product_Exp
       ,o.Unit_Id
    ,pal.Pallet_Code
    ,loc.Location_Code
    ,SUBSTRING(loc.Location_Code, 1, 1) AS Room
    ,SUBSTRING(loc.Location_Code, 1, 3) AS Row
    ,SUBSTRING(loc.Location_Code, 5, 2) AS Colunm
    ,SUBSTRING(loc.Location_Code, 8, 1) AS Level

    FROM STK_T_Outbound o 
        LEFT OUTER JOIN STK_M_Product p ON o.Product_Id = p.Product_Id
        LEFT OUTER JOIN STK_M_Location l ON o.Actual_Location_Id = l.Location_Id
        LEFT JOIN STK_T_Order oo_tb On o.Flow_Id = oo_tb.Flow_Id       
        LEFT JOIN STK_T_Pallet pal ON o.Pallet_Id = pal.Pallet_Id
        LEFT JOIN STK_M_Location loc ON o.Actual_Location_Id = loc.Location_Id

    WHERE DATEDIFF(Day,o.Dispatch_Date,DATEADD(Day,-1,getdate())) = 0 
    ";
    if(isset($type)){
        $str .="AND o.Pallet_Id IS NOT NULL";
    }
   
    $str .="
           And o.Outbound_Id Not In 
           --(Select Item_From_Id From STK_T_Counting_Detail Where Item_From = 'OUTBOUND')
           (Select Item_From_Id From STK_T_Counting_Detail 
           INNER JOIN STK_T_Counting counting On counting.Order_Id = STK_T_Counting_Detail.Order_Id 
           INNER JOIN STK_T_Workflow flow On flow.Flow_Id = counting.Flow_Id 
           Where Item_From = 'OUTBOUND'  AND flow.Present_State != -1) ";
           
        $query = $this->db->query($str);
        // p($this->db->last_query());
        // exit;
        return $query->result();
    }

    public function queryFromString($str = "") {
        $query = $this->db->query($str);
        return $query;
    }

    public function saveCountingOrder($str = "") {
        $this->db->insert("STK_L_Counting_Order", $str);
        return $this->db->insert_id();
    }

    public function getCountingDetailByOrderId($id = "") {
        $str = "SELECT * FROM STK_T_Counting_Detail WHERE Order_Id = " . $id;
        $query = $this->db->query($str);
        return $query->result();
    }

    public function getCountingFlowData($flow_id) {
        //$str = "SELECT * FROM STK_T_Counting_Detail WHERE order_id = ".$id;
        // Add DISTINCT @ Ball
        $str = "SELECT DISTINCT a.Item_Id
                        ,a.Product_Code
                        ,e.Product_NameEN 
                        ,d.Location_Code
                        ,a.Reserv_Qty
                        --,f.Receive_Qty as Reserv_Qty /* Remove By Ball because */
                        ,a.Confirm_Qty
                        ,a.Product_Lot
                        ,a.Product_Serial /* change to use counting a */
                        ,CONVERT(VARCHAR,a.Product_Mfd,103) as Product_Mfd
                        ,CONVERT(VARCHAR,a.Product_Exp,103) as Product_Exp
			FROM STK_T_Counting_Detail a
                        RIGHT JOIN STK_T_Counting b ON a.Order_Id = b.Order_Id
                        LEFT JOIN STK_T_Workflow c ON b.Flow_Id = c.Flow_Id 
                        LEFT JOIN STK_M_Location d ON d.Location_Id = a.Actual_Location_Id
                        LEFT JOIN STK_M_Product e ON a.Product_Code = e.Product_Code 
                        --LEFT JOIN STK_T_Inbound f ON a.Product_Code = f.Product_Code AND a.Actual_Location_Id = f.Actual_Location_Id /* Remove By Ball because */
                WHERE e.Active = 'Y' AND c.Flow_Id = " . $flow_id; //Add e.Active='Y' @ Ball 2013-10-25
        //echo $str;
        $query = $this->db->query($str);
        //echo $this->db->last_query();
        return $query->result();
    }

    public function checkIsWarehouseAdmin($user_id = 0) {

        $str = "SELECT b.UserLogin_Id 
                FROM (SELECT a.UserLogin_Id FROM ADM_M_UserLogin a 
                        LEFT JOIN ADM_R_UserGroupMembers b
                        ON a.UserLogin_Id = b.UserLogin_Id
                        JOIN ADM_M_UserGroup c 
                        ON b.UserGroup_Id = c.UserGroup_Id
                        WHERE b.UserGroup_Id = 6 
                        AND a.UserLogin_Id = " . $user_id . ") 
                     as b";
        $query = $this->db->query($str);
        return $query->result();
    }

    public function checkCountingDocumentType() {
        $str = "SELECT TOP 1 Document_No FROM STK_T_Workflow ORDER BY Flow_Id DESC";
        $query = $this->db->query($str);
        return $query->result();
    }

    public function searchCounting($search) {
        /*
          [product] =>
          [product_id] =>
          [fdate] => 04/07/2013
          [tdate] =>
          [lot] =>
          [count_type] => 3
         */
        
        /*Edit By Por 2013-10-25 แก้ไข Count_By ให้เรียกใช้ function
        โดยได้ลบ โค้ดนี้ออก
        
         ,(SELECT (First_NameTH+' '+Last_NameTH) as name FROM CTL_M_Contact cont,ADM_M_UserLogin u
								WHERE cont.Contact_Id=u.Contact_Id
								AND u.UserLogin_Id=c.Count_By
							 ) as Count_By
         */
        $this->db->select("ct.Item_Id
                                                    ,CONVERT(VARCHAR(20), c.Create_Date, 103) AS Actual_Action_Date
                                                    ,ct.Product_Id
                                                    ,(SELECT Top 1 Product_Code FROM STK_M_Product p WHERE p.Product_Id=ct.Product_Id) AS Product_Code
                                                    ,(SELECT Top 1 Product_NameEN FROM STK_M_Product p WHERE p.Product_Id=ct.Product_Id) AS Product_NameEN
                                                    ,ct.Reserv_Qty,ct.Confirm_Qty
                                                    ,(SELECT Top 1 Balance_Qty FROM STK_T_Inbound
                                                    WHERE Product_Id=ct.Product_Id AND Active='" . ACTIVE . "') AS Actual_Qty
                                                    ,(select dbo.[fNameUser](c.Count_By,1)) as Count_By
                                                    ");
        $this->db->from("STK_T_Counting_Detail AS ct");
        $this->db->join("STK_T_Counting AS c", "ct.Order_Id=c.Order_Id", "left");
        $this->db->join("STK_T_Workflow AS f", "c.Flow_Id=f.Flow_Id", "left");
        //$this->db->join("STK_M_Product AS p","ct.Product_Id=p.Product_Id","left");
        if ($search['count_type'] != "") {
            $this->db->where("f.Process_Id", $search['count_type']);
            $this->db->where("f.Present_State", -2);
        }

        if ($search['fdate'] != "") {
            //Create_Date change condition by kik
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") {
                $to_date = convertDate($search['tdate'], "eng", "iso", "-") . " 23:59:59";
                $this->db->where("c.Create_Date >=", $from_date);
                $this->db->where("c.Create_Date<=", $to_date);
            } else {
                $this->db->where("c.Create_Date>=", $from_date);
                $this->db->where("c.Create_Date<=", $from_date . " 23:59:59");
            }
        }

        if ($search['product_id'] != "") {
            $this->db->where("ct.Product_Id", $search['product_id']);
        }

        if ($search['lot'] != "") {
            $this->db->where("ct.Product_Lot", $search['lot']);
        }

        $query = $this->db->get();
 		//echo '<br>'.$this->db->last_query();
        return $query->result();
    }
    
    function updateCounting($data ,$where ) {
        $this->db->where($where);
        $this->db->update('STK_T_Counting', $data);
        $afftectedRows = $this->db->affected_rows();
        if ($afftectedRows >= 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }
    }
    
    function updateCounting_Detail($data, $where) {
        $this->db->where($where);
        $this->db->update('STK_T_Counting_Detail', $data);
        $afftectedRows = $this->db->affected_rows();
        if ($afftectedRows >= 0) {
             return TRUE; //Update success.
         } else {
             return FALSE; //Update unsuccess.
         }
    }

    function getPalletId($pallet_code_list){
        $this->db->select("pal.Pallet_Id");
        $this->db->from("STK_T_Inbound inb");
        $this->db->join("STK_T_Pallet pal","inb.Pallet_Id = pal.Pallet_Id");
        $this->db->where("pal.Pallet_Code", trim($pallet_code_list));
        $this->db->where("inb.Active","Y");
        $query = $this->db->get();

        return $query->result_array();
    }
    // public function get_database() {

    //     $this->db->select("DB_NAME(database_id) AS DatabaseName");
    //     $this->db->select("Name AS Logical_Name");
    //     $this->db->select("Physical_Name");
    //     $this->db->select("(size * 8) / 1024 SizeMB");
    //     $this->db->from("sys.master_files");
    //         $this->db->where("DB_NAME(database_id) IN (SELECT name
    //         FROM sys.sysdatabases)");
    //         $this->db->order_by('DatabaseName DESC');
    //     $query = $this->db->get();
    //     return $query->result_array();
    // }

}

?>
