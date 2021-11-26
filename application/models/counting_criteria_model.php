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
class counting_criteria_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    #comment o.Dispatch_Qty , add i.Balance_Qty as QTY
    function countingCriteriaMovementList($type, $value1, $value2, $value3) {
     

    	if ($type == "product_code") :    		
    		
    		if ($value1 != "" && $value2 != "") {
    			$where_condition = "And i.Product_Code Between '" . $value1 . "' AND '".$value2."' ";    			
    		} else {
    			$where_condition = "And i.Product_Code LIKE '%" . $value1 . "%'";
    		}
    		//product code calulation
    		$str = "SELECT DISTINCT i.Product_Code ,p.Product_NameEN ,l.Location_Code ,i.Actual_Location_Id as Actual_Location_Id ,i.Product_Id as Product_Id
                    FROM STK_T_Inbound i
                    LEFT OUTER JOIN STK_M_Product p ON i.Product_Id = p.Product_Id
                    LEFT OUTER JOIN STK_M_Location l ON i.Actual_Location_Id = l.Location_Id
                    WHERE i.Actual_Location_Id != '' ".$where_condition." AND i.Active = 'Y' AND i.Balance_Qty > 0 Order By l.Location_Code Asc, i.Product_Code Asc";

    	elseif ($type == "location_code") :
    		//location code calulation
    		if ($value1 != "" && $value2 != "") {
    			$where_condition = " l.Location_Code Between '" . $value1 . "' AND '".$value2."' ";
    		} else {
    			$where_condition = " l.Location_Code LIKE '%" . $value1 . "%'";
    		}    		
    		$str = "SELECT DISTINCT i.Product_Code ,p.Product_NameEN ,l.Location_Code ,i.Actual_Location_Id as Actual_Location_Id ,i.Product_Id as Product_Id
                    FROM STK_T_Inbound i
                    LEFT OUTER JOIN STK_M_Product p ON i.Product_Id = p.Product_Id
                    LEFT OUTER JOIN STK_M_Location l ON i.Actual_Location_Id = l.Location_Id
                    WHERE ".$where_condition." AND i.Active = 'Y' AND i.Balance_Qty > 0 Order By l.Location_Code Asc, i.Product_Code Asc";
    		    		
    	elseif ($type == "period_of_storage") :
    		//movement calulation
    		$str = "SELECT DISTINCT
                		Product_Code
                		,Product_NameEN
                		,Location_Code
                		,QTY
                		,Actual_Location_Id
                		,Product_Id
    				FROM (SELECT i.Inbound_Id  as 'Product_Id'
    				,i.Product_Code
                		,p.Product_NameEN
                		,l.Location_Code
						--,CASE WHEN i.Balance_Qty IS NULL THEN o.Dispatch_Qty ELSE i.Balance_Qty END as QTY
                                                ,i.Balance_Qty as QTY
						,i.Actual_Location_Id as Actual_Location_Id
						,DATEDIFF(Day,i.Receive_Date,getdate()) as Diff
						FROM STK_T_Inbound i
							LEFT OUTER JOIN STK_M_Product p
                            ON i.Product_Id = p.Product_Id
                            LEFT OUTER JOIN STK_M_Location l
                            ON i.Actual_Location_Id = l.Location_Id
                			WHERE i.Actual_Location_Id  != '' AND i.Active = 'Y'
						) a
					WHERE a.Diff BETWEEN " . $value1 . " AND " . $value2 . "
					AND QTY > 0";
    	    	
    	elseif ($type == "dead_stock") :
    	
    	//dead stock calulation
    	$str = "SELECT DISTINCT
                        i.Product_Code
                		,p.Product_NameEN
                		,l.Location_Code
                		,i.Actual_Location_Id as Actual_Location_Id
		            	,i.Product_Id as Product_Id
                    FROM STK_T_Inbound i
                    LEFT OUTER JOIN STK_M_Product p
                    ON i.Product_Id = p.Product_Id
                    LEFT OUTER JOIN STK_M_Location l
                    ON i.Actual_Location_Id = l.Location_Id
                    WHERE DATEDIFF(Day,i.Receive_Date,DATEADD(Day,0,getdate())) " . $value1 . " " . $value2 . " And i.Actual_Location_Id  != '' /*movement of Receive*/
                    AND i.Balance_Qty > 0 AND i.Active = 'Y'";
    		#add new again for fix bug : by kik : 20140124
    	    	
    	elseif ($type == "top_movement") :

    	//top movement calulation
    	$str = "SELECT TOP " . $value3 . " a.* FROM
                	(SELECT
                	i.Product_Code
                	,p.Product_NameEN
                	,l.Location_Code
                	,i.Actual_Location_Id as Actual_Location_Id
	            	,i.Product_Id as Product_Id
                    FROM STK_T_Inbound i
                    LEFT OUTER JOIN STK_M_Product p
                    ON i.Product_Id = p.Product_Id
                    LEFT OUTER JOIN STK_M_Location l
                    ON i.Actual_Location_Id = l.Location_Id
                    WHERE i.Actual_Location_Id  != ''
                    AND i.Balance_Qty > 0
                	AND i.Active = 'Y'
                    AND i.Receive_Date BETWEEN '" . date("Y-m-d", strtotime($value1)) . "'
                    AND '" . date("Y-m-d", strtotime($value2)) . "' ) AS a  /*movement of Dispatch*/";
    	    	
        elseif ($type == "receive_date") :
         
            list($d, $m, $y) = explode("/", $value1);
            $date_from = "$y-$m-$d";
            list($d, $m, $y) = explode("/", $value2);
            $date_to = "$y-$m-$d";   

                        $str = "SELECT
                                    CONVERT(VARCHAR(10),i.Receive_Date,103) as Receive_Date,
                                    i.Doc_Refer_Ext ,
                                    i.Product_Code,
                                    p.Product_NameEN,
                                    i.Product_Lot,
                                    CONVERT(VARCHAR(10),i.Product_Mfd,103) as Product_Mfd,
                                    CONVERT(VARCHAR(10),i.Product_Exp,103) as Product_Exp,
                                    pl.Pallet_Code,
                                    l.Location_Code,
                                    sum(i.Balance_Qty) AS Balance_Qty,
                                    uom.public_name as uom
                        from STK_T_Inbound i
                            left join STK_M_Product p on i.Product_Code = p.Product_Code
                            left join STK_M_Location l on i.Actual_Location_Id = l.Location_Id
                            left join STK_T_Pallet pl on i.Pallet_Id = pl.Pallet_Id
                            left join CTL_M_UOM_Template_Language uom on uom.CTL_M_UOM_Template_id = p.Standard_Unit_Id and uom.language='eng'	
                            
                            WHERE CONVERT(VARCHAR(10),i.Receive_Date,120)  BETWEEN '".date("Y-m-d", strtotime($date_from)) ."' AND '".date("Y-m-d", strtotime($date_to))."'

                              AND i.Active = 'Y'
                        GROUP BY CONVERT(VARCHAR(10),i.Receive_Date,103),
                                    i.Doc_Refer_Ext ,
                                    i.Product_Code,
                                    p.Product_NameEN,
                                    i.Product_Lot,
                                    CONVERT(VARCHAR(10),i.Product_Mfd,103),
                                    CONVERT(VARCHAR(10),i.Product_Exp,103),
                                    pl.Pallet_Code,
                                    l.Location_Code,
                                    uom.public_name
                        ORDER BY Receive_Date ASC"
					;
    	else :
    	$str = "SELECT DISTINCT i.Inbound_Id as 'Id',i.Product_Code,p.Product_NameEN ,l.Location_Code
                        ,i.Receive_Qty as QTY,i.Actual_Location_Id as Actual_Location_Id
                    FROM STK_T_Inbound i
                    LEFT OUTER JOIN STK_M_Product p
                    ON i.Product_Id = p.Product_Id
                    LEFT OUTER JOIN STK_M_Location l
                    ON i.Actual_Location_Id = l.Location_Id
                    WHERE i.Actual_Location_Id  != '' AND i.Balance_Qty > 0 AND i.Active = 'Y'";
    	endif;
    	
        //echo $str;
        $query = $this->db->query($str);
    //  p( $this->db->last_query()); 
        // P($query->result());
        return $query->result();
        //return $type;
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
        $str = "SELECT	 a.Item_Id
                        ,a.Product_Code
                        ,e.Product_NameEN 
                        ,d.Location_Code
                        ,a.Reserv_Qty
                       -- ,f.Receive_Qty as Reserv_Qty
                        ,a.Confirm_Qty
                        ,a.Product_Lot
                        ,f.Product_Serial
                        ,CONVERT(VARCHAR,i.Product_Mfd,103) as Product_Mfd
		    	        ,CONVERT(VARCHAR,i.Product_Exp,103) as Product_Exp
			FROM STK_T_Counting_Detail a
                        RIGHT JOIN STK_T_Counting b ON a.Order_Id = b.Order_Id
                        LEFT JOIN STK_T_Workflow c ON b.Flow_Id = c.Flow_Id 
                        LEFT JOIN STK_M_Location d ON d.Location_Id = a.Actual_Location_Id
                        LEFT JOIN STK_M_Product e ON e.Product_Code = a.Product_Code 
                        LEFT JOIN STK_T_Inbound f ON a.Product_Code = f.Product_Code 
                WHERE c.Flow_Id = " . $flow_id;
        $query = $this->db->query($str);
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

    function countingCriteriaMovementListStep2($value) {
        //$value = $_POST["selected_chk"];
        #ISSUE 2790 Revise Balance & Allowcate calculation.
        #DATE:2013-09-06
        #BY:KIK
        #แก้ qty จาก Receive_Qty เป็น Balance_Qty
        #START New Comment Code #ISSUE 2790



        $str = " 
            SELECT 
                i.Inbound_Id as 'Id'
                ,i.Product_Code
                ,p.Product_NameEN 
                ,l.Location_Code
                ,i.Balance_Qty as QTY
                ,i.Actual_Location_Id as Actual_Location_Id 
		    	,i.Product_Lot as Lot
		    	,i.Product_Serial as Serial
		    	,CONVERT(VARCHAR,i.Product_Mfd,103) as Product_Mfd
		    	,CONVERT(VARCHAR,i.Product_Exp,103) as Product_Exp
                        ,Pallet_Code
            FROM STK_T_Inbound i
                    LEFT OUTER JOIN STK_M_Product p ON i.Product_Id = p.Product_Id
                    LEFT OUTER JOIN STK_M_Location l ON i.Actual_Location_Id = l.Location_Id
                    LEFT OUTER JOIN STK_T_Pallet pallet ON i.Pallet_Id = pallet.Pallet_Id
            WHERE i.Inbound_Id IN (" . $value . ") ";

        $query = $this->db->query($str);
        return $query->result();
    }

    function countingCriteriaMovementListShow($value, $order_by = NULL) {
        
    	$order = "";
    	
        if (!is_null($order_by) && $order_by == "sku") {
        	$order = " Order By Product_Code ASC";
        } else if (!is_null($order_by) && $order_by == "location") {
        	$order = " Order By Location_Code ASC";
        }
        
    	$str = "SELECT DISTINCT 
        	a.Item_Id as Id
        	,a.Order_Id
        	,a.Product_Id
        	,a.Product_Code
        	,d.Product_NameEN
        	,e.Location_Code
        	,a.Reserv_Qty as QTY
        	,a.Confirm_Qty as Confirm_Qty
			,a.Actual_Location_Id
			,a.Confirm_Qty
			-- ,ib.Product_Status
            ,SYS_M_Domain.Dom_TH_Desc AS Product_Status
			,a.Inbound_Id
		    ,a.Product_Lot as Lot
		    ,a.Product_Serial as Serial
		    ,ISNULL((Select dispatch From vw_location_booked Where Inbound_Item_Id = a.Inbound_Id),0) as booked
		    ,ISNULL((Select dispatch From vw_location_dispatch Where Inbound_Item_Id = a.Inbound_Id),0) as dispatch		    
		    ,CONVERT(VARCHAR,a.Product_Mfd,103) as Product_Mfd
		    ,CONVERT(VARCHAR,a.Product_Exp,103) as Product_Exp    
                    ,pallet.Pallet_Code
			FROM dbo.STK_T_Counting_Detail  a 
			LEFT OUTER JOIN  dbo.STK_T_Counting b 
			ON a.Order_Id = b.Order_Id
			LEFT OUTER JOIN STK_T_Workflow c
			ON b.Flow_Id = c.Flow_Id
			LEFT OUTER JOIN STK_M_Product d
			ON a.Product_Id = d.Product_Id
			LEFT OUTER JOIN STK_M_Location e
			ON a.Actual_Location_Id = e.Location_Id
			LEFT OUTER JOIN STK_T_Inbound ib
			ON ib.Inbound_Id = a.Inbound_Id AND ib.Actual_Location_Id = a.Actual_Location_Id
                        LEFT OUTER JOIN STK_T_Pallet pallet
			ON pallet.Pallet_Id = ib.Pallet_Id
            LEFT JOIN SYS_M_Domain  
            ON ib.Product_Status = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code ='prod_status' AND SYS_M_Domain.Dom_Active ='Y' 
			WHERE c. Flow_Id = " . $value;
    	$str .= $order;
        $query = $this->db->query($str);
        // echo $this->db->last_query();
        // p($query->result());exit;
        return $query->result();
    }

    public function get_count_list() {
        $this->db->select("Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "COUNT_TYPE");
        $this->db->where("Dom_Active", "Y");
        $query = $this->db->get("SYS_M_Domain");
        return $query;
    }
    
    public function get_product_location ($product_code, $location_code)
    {
    	
		$this->db->distinct();    		
    	$this->db->select("i.Inbound_Id as 'Id'
    			,i.Product_Code
    			,p.Product_NameEN
    			,l.Location_Code
    			,i.Balance_Qty as QTY
    			,i.Actual_Location_Id as Actual_Location_Id
    			,'INBOUND' as Item_From
    			,i.Inbound_Id as Item_From_Id
    			,i.Product_Status
    			,i.Product_Sub_Status
    			,i.Product_License
                        ,CONVERT(VARCHAR,i.Product_Mfd,103) as Product_Mfd
                        ,CONVERT(VARCHAR,i.Product_Exp,103) as Product_Exp  
    			,i.Unit_Id
    			,i.Product_Lot AS Lot
    			,i.Product_Serial AS Serial
    			,i.Product_Id as Product_Id");
        // Edit select By Akkarapol, 22/01/2014, Change Product_Mfd and Product_Exp 
        // form     ,CONVERT(VARCHAR, CAST(i.Product_Mfd AS DATE), 121) AS Product_Mfd
        // to       ,CONVERT(VARCHAR,i.Product_Mfd,103) as Product_Mfd
        // and form ,CONVERT(VARCHAR, CAST(i.Product_Exp AS DATE), 121) AS Product_Exp
        // to       ,CONVERT(VARCHAR,i.Product_Exp,103) as Product_Exp  
        
    	$this->db->join("STK_M_Product p","i.Product_Id = p.Product_Id","LEFT OUTER");
    	$this->db->join("STK_M_Location l","i.Actual_Location_Id = l.Location_Id","LEFT OUTER");    	
    	$this->db->where("i.Product_Code = '".$product_code."'");    	
    	$this->db->where("l.Location_Code = '".$location_code."'");    	
    	$this->db->where("i.Active = 'Y'");    	
    	$this->db->where("i.Balance_Qty != 0");    	
    	$query = $this->db->get("STK_T_Inbound i");
//        echo $this->db->last_query();
    	//$sum = 0;
    	//$response = array();
    	
    	/*foreach ($query->result() as $k => $v) :
    		$sum += $v->QTY;
    	endforeach;

    	$r = $query->result();
    	$response['Product_Code'] = $r['0']->Product_Code;
    	$response['Product_NameEN'] = $r['0']->Product_NameEN;
    	$response['Location_Code'] = $r['0']->Location_Code;
    	$response['Actual_Location_Id'] = $r['0']->Actual_Location_Id;
    	$response['Inbound_Id'] = $r['0']->Id;
    	$response['Product_Status'] = $r['0']->Product_Status;
    	$response['Product_Sub_Status'] = $r['0']->Product_Sub_Status;
    	$response['Product_License'] = $r['0']->Product_License;
    	$response['Product_Mfd'] = $r['0']->Product_Mfd;
    	$response['Product_Exp'] = $r['0']->Product_Exp;
    	$response['Unit_Id'] = $r['0']->Unit_Id;
    	$response['Product_Lot'] = $r['0']->Lot;
    	$response['Product_Serial'] = $r['0']->Serial;
    	$response['Product_Id'] = $r['0']->Product_Id;
    	$response['Sum_Qty'] = $sum;*/

    	return $query;    	
    }

    /**
     * 
     * @param unknown $params
     */
    public function generateDataCounting($params) {
    	p($params);
    }
    

    function export_excel($from_date,$to_date){

        $str = "SELECT
                                    CONVERT(VARCHAR(10),i.Receive_Date,103) as Receive_Date,
                                    i.Doc_Refer_Ext ,
                                    i.Product_Code,
                                    p.Product_NameEN,
                                    i.Product_Lot,
                                    CONVERT(VARCHAR(10),i.Product_Mfd,103) as Product_Mfd,
                                    CONVERT(VARCHAR(10),i.Product_Exp,103) as Product_Exp,
                                    pl.Pallet_Code,
                                    l.Location_Code,
                                    sum(i.Balance_Qty) AS Balance_Qty,
                                    uom.public_name as uom
                        from STK_T_Inbound i
                            left join STK_M_Product p on i.Product_Code = p.Product_Code
                            left join STK_M_Location l on i.Actual_Location_Id = l.Location_Id
                            left join STK_T_Pallet pl on i.Pallet_Id = pl.Pallet_Id
                            left join CTL_M_UOM_Template_Language uom on uom.CTL_M_UOM_Template_id = p.Standard_Unit_Id and uom.language='eng'	
                        WHERE CONVERT(VARCHAR(10),i.Receive_Date,120)  BETWEEN '".date("Y-d-m", strtotime($from_date)) ."' AND '".date("Y-d-m", strtotime($to_date))."'
                              AND i.Active = 'Y'
                        GROUP BY CONVERT(VARCHAR(10),i.Receive_Date,103),
                                    i.Doc_Refer_Ext ,
                                    i.Product_Code,
                                    p.Product_NameEN,
                                    i.Product_Lot,
                                    CONVERT(VARCHAR(10),i.Product_Mfd,103),
                                    CONVERT(VARCHAR(10),i.Product_Exp,103),
                                    pl.Pallet_Code,
                                    l.Location_Code,
                                    uom.public_name
                        ORDER BY Receive_Date ASC" ;
                    $query = $this->db->query($str);
                    return $query->result();
                    //  p( $this->db->last_query()); 
                    //  P($query->result());exit;
    }
  
    
}

?>
