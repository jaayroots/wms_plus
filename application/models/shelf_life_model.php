<?php

class shelf_life_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function searchProduct($txt_search) {// Duplicate Function !!
        $this->db->select('Product_Id,Product_Code,Product_NameEN');
        $this->db->like('Product_Code', $txt_search);
        $this->db->or_like('Product_NameEN', $txt_search);
        $this->db->from('STK_M_Product');
        $query = $this->db->get();
        $i = 0;
        $data = array();
        foreach ($query->result() as $row) {
            $data[$i]['product_id'] = $row->Product_Id;
            $data[$i]['product_code'] = $row->Product_Code;
            $data[$i]['product_name'] = $row->Product_NameEN;
            $i++;
        }
        return $data;
    }

    function searchProductMovement($search) {
        #search have product, product_id,fdate,tdate

        $tmp_search = $this->getSearchDateSql($search['fdate'], $search['tdate']);

        $sql = "SELECT CONVERT(VARCHAR(20), tr.Actual_Action_Date, 103) AS Receive_Date
						, tr.Document_No AS receive_doc_no
						, tr.Doc_Refer_Ext AS receive_refer_ext
						,r.Confirm_Qty AS r_qty, r.Product_Serial,r.Product_Lot
						,l.Location_Code 
						,(SELECT Company_Code FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Source_Id) AS s_branch
						,(SELECT Company_Code FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Destination_Id) AS d_branch
						,tr.Process_Type
					FROM STK_T_Order_Detail r,STK_T_Order tr,STK_M_Location l
					WHERE r.Order_Id = tr.Order_Id AND r.Product_Id='" . $search['product_id'] . "'
						AND r.Actual_Location_Id = l.Location_Id " . $tmp_search . " 
						ORDER BY tr.Actual_Action_Date ASC
				  ";
        //echo ' sql = '.$sql;
        $balance = $this->getProductBalance($search['product_id'], $search['fdate'], $search['tdate']);
        //echo '<br> balance = '.$balance;
        $value[0] = $balance;

        $query = $this->db->query($sql);
        $result = $query->result();

        //p($result);
        $rows = array();
        $i = 0;
        foreach ($result as $row) {
            # case INBOUND
            if (trim($row->Process_Type) == "INBOUND") {
                $balance = $balance + $row->r_qty;
                $rows[$i]['receive_date'] = $row->Receive_Date;
                $rows[$i]['receive_doc_no'] = $this->conv->tis620_to_utf8($row->receive_doc_no);
                $rows[$i]['pay_doc_no'] = '';
                $rows[$i]['receive_refer_ext'] = $this->conv->tis620_to_utf8($row->receive_refer_ext);
                $rows[$i]['pay_refer_ext'] = '';
                $rows[$i]['r_qty'] = $row->r_qty;
                $rows[$i]['p_qty'] = '';
                $rows[$i]['Product_Serial'] = $this->conv->tis620_to_utf8($row->Product_Serial);
                $rows[$i]['Product_Lot'] = $this->conv->tis620_to_utf8($row->Product_Lot);
                $rows[$i]['Location_Code'] = $this->conv->tis620_to_utf8($row->Location_Code);
                $rows[$i]['Balance_Qty'] = $balance;
                $rows[$i]['branch'] = $row->s_branch . "/" . $row->d_branch;
            } else if (trim($row->Process_Type) == "OUTBOUND") {
                # case OUTBOUND
                $balance = $balance - $row->r_qty;
                $rows[$i]['receive_date'] = $row->Receive_Date;
                $rows[$i]['receive_doc_no'] = '';
                $rows[$i]['pay_doc_no'] = $this->conv->tis620_to_utf8($row->receive_doc_no);
                $rows[$i]['receive_refer_ext'] = '';
                $rows[$i]['pay_refer_ext'] = $this->conv->tis620_to_utf8($row->receive_refer_ext);
                $rows[$i]['r_qty'] = '';
                $rows[$i]['p_qty'] = $row->r_qty;
                $rows[$i]['Product_Serial'] = $this->conv->tis620_to_utf8($row->Product_Serial);
                $rows[$i]['Product_Lot'] = $this->conv->tis620_to_utf8($row->Product_Lot);
                $rows[$i]['Location_Code'] = $this->conv->tis620_to_utf8($row->Location_Code);
                $rows[$i]['Balance_Qty'] = $balance;
                $rows[$i]['branch'] = $row->s_branch . "/" . $row->d_branch;
            } else {
                
            }
            $rows[$i]['process_type'] = $row->Process_Type;
            $i++;
        }
        //p($rows);

        $value[1] = $rows;
        return $value;
        # result have  column "�ӴѺ","�ѹ���","�Ţ����Ѻ","�Ţ�����ҧ�ԧ�Ѻ","�Ţ������",
        #"�Ţ�����ҧ�ԧ����","�ӹǹ�Ѻ","�ӹǹ����","Serial/Lot","�ٹ��/�Ң�","Location","������͡
    }

    function searchProductMovementAllProduct($search) {
        $search_date = $this->getSearchDateSql($search['fdate'], $search['tdate']);

        $sql_find_product = "SELECT DISTINCT(Product_Id),Product_Code
						,(SELECT Product_NameEN FROM STK_M_Product p WHERE r.Product_Id=p.Product_Id ) AS Product_Name
						FROM STK_T_Order_Detail r,STK_T_Order tr
						WHERE r.Order_Id = tr.Order_Id " . $search_date;
        $query_product = $this->db->query($sql_find_product);
        //echo ' <br>sql = '.$sql_find_product;
        $product_result = $query_product->result();
        $countrow = $this->db->affected_rows();
        if ($countrow == 0) {
            $rows = array();
            return $rows;
        }

        foreach ($product_result as $p) {
            $product[$p->Product_Id]['id'] = $p->Product_Id;
            $product[$p->Product_Id]['code'] = $p->Product_Code;
            $product[$p->Product_Id]['name'] = $p->Product_Name;
        }
        //p($product);

        $product_code = "'" . implode("','", array_keys($product)) . "'";

        $sql = "SELECT r.Product_Id,r.Product_Code
						,(SELECT Product_NameEN FROM STK_M_Product p WHERE r.Product_Id=p.Product_Id ) AS Product_Name
					    ,CONVERT(VARCHAR(20), tr.Actual_Action_Date, 103) AS Receive_Date
						, tr.Document_No AS receive_doc_no
						, tr.Doc_Refer_Ext AS receive_refer_ext
						,r.Confirm_Qty AS r_qty, r.Product_Serial,r.Product_Lot
						,l.Location_Code
						,(SELECT Company_Code FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Source_Id) AS s_branch
						,(SELECT Company_Code FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Destination_Id) AS d_branch
						,tr.Process_Type
					FROM STK_T_Order_Detail r,STK_T_Order tr,STK_M_Location l
					WHERE r.Order_Id = tr.Order_Id AND r.Product_Id IN (" . $product_code . ")
						AND r.Actual_Location_Id = l.Location_Id " . $search_date . "
						ORDER BY r.Product_Id ASC,tr.Actual_Action_Date ASC
				  ";
        $query = $this->db->query($sql);
        $result = $query->result();

        //p($result);
        $rows = array();
        $i = 1;
        foreach ($result as $row) {

            $rows[$i]['product'] = $this->conv->tis620_to_utf8($row->Product_Code) . "/" . $this->conv->tis620_to_utf8($row->Product_Name);
            $rows[$i]['product_id'] = $row->Product_Id;
            $rows[$i]['receive_date'] = $this->conv->tis620_to_utf8($row->Receive_Date);
            $rows[$i]['receive_doc_no'] = $this->conv->tis620_to_utf8($row->receive_doc_no);
            $rows[$i]['pay_doc_no'] = '';
            $rows[$i]['receive_refer_ext'] = $this->conv->tis620_to_utf8($row->receive_refer_ext);
            $rows[$i]['pay_refer_ext'] = '';
            $rows[$i]['r_qty'] = $row->r_qty;
            $rows[$i]['p_qty'] = '';
            $rows[$i]['Product_Serial'] = $this->conv->tis620_to_utf8($row->Product_Serial);
            $rows[$i]['Product_Lot'] = $this->conv->tis620_to_utf8($row->Product_Lot);
            $rows[$i]['Location_Code'] = $this->conv->tis620_to_utf8($row->Location_Code);
            $rows[$i]['Balance_Qty'] = '';
            $rows[$i]['branch'] = $row->s_branch . "/" . $row->d_branch;
            $rows[$i]['process_type'] = trim($row->Process_Type);
            $i++;
        }
        //p($rows);
        return $rows;
    }

    function getSearchDateSql($f_date, $t_date) {
        $from_date = '';
        $to_date = '';
        $tmp_search = '';
        if ($f_date != "") {
            $tmp_date = explode("/", $f_date);
            $from_date = $tmp_date[2] . "-" . $tmp_date[1] . "-" . $tmp_date[0];
        }
        if ($t_date != "") {
            $tmp_date = explode("/", $t_date);
            $to_date = $tmp_date[2] . "-" . $tmp_date[1] . "-" . $tmp_date[0];
        }

        if ($from_date != "") {
            if ($to_date != "") {
                $tmp_search = " AND tr.Actual_Action_Date>='" . $from_date . " 00:00:00'
								AND tr.Actual_Action_Date<='" . $to_date . " 23:59:59'";
            } else {
                //CONVERT(VARCHAR(20), o.Dispatch_Date, 103)
                $tmp_search = " AND CONVERT(VARCHAR(20), tr.Actual_Action_Date, 103)='" . $f_date . "'";
            }
        } else {
            if ($t_date != "") {
                $tmp_search = " AND CONVERT(VARCHAR(20), tr.Actual_Action_Date, 103)='" . $t_date . "'";
            }
        }
        return $tmp_search;
    }

    function getProductDetailByCode($code) {// Duplicate Function !!
        $this->db->select('Product_NameEN');
        $this->db->where('Product_Code', $code);
        $this->db->from('STK_M_Product');
        $query = $this->db->get();
        $result = $query->result();
        return $result[0]->Product_NameEN;
    }

    function getProductDetailById($id) {// Duplicate Function !!
        $this->db->select('Product_NameEN');
        $this->db->where('Product_Id', $id);
        $this->db->from('STK_M_Product');
        $query = $this->db->get();
        $result = $query->result();
        return $result[0]->Product_NameEN;
    }

    function groupProductResult($data) {

        $index = 0;
        if (count($data) == 0) {
            return array();
        }
        foreach ($data as $r) {
            $value[$r['product']][] = $r;
            /*
              if($index==0){
              $addp=$r['product'];
              }
              $index++;
             */
        }
        return $value;
    }

    function getProductBalance($product_id, $f_date, $t_date) {
        //$balance=5000;

        $tmp_search = '';

        if ($f_date != "" && $f_date == date("d/m/Y")) {
            // search today
            $sql = "SELECT SUM(Receive_Qty-(Dispatch_Qty+Adjust_Qty)) AS sum_qty FROM STK_T_Inbound WHERE Product_Id=$product_id ";
            $query = $this->db->query($sql);
            $result = $query->result();
            $balance = $result[0]->sum_qty;
            return $balance;

            exit();
        } else if ($f_date != "" && $f_date != date("d/m/Y")) {
            $tmp_search = " AND CONVERT(VARCHAR(20), Available_Date, 103)='" . $f_date . "'";
        } else if ($t_date != "") {
            $tmp_search = " AND CONVERT(VARCHAR(20), Available_Date, 103)='" . $t_date . "'";
        }

        if ($tmp_search == "") {
            $sql = "SELECT SUM(Available_Qty) AS sum_qty FROM STK_T_Onhand_History WHERE Product_Id=$product_id
				  GROUP BY Available_Date ORDER BY Available_Date ASC";
        } else {
            $sql = "SELECT SUM(Available_Qty) AS sum_qty FROM STK_T_Onhand_History WHERE Product_Id=$product_id
			  " . $tmp_search;
        }
        //echo '<br> sql = '.$sql;
        $query = $this->db->query($sql);
        $result = $query->result();
        if (count($result) != 0) {
            $balance = $result[0]->sum_qty;
        } else {
            $balance = 0;
        }
        return $balance;
    }

    function getGRN() {
        $day = date("Y/m/d");
        //111 yyyy/mm/dd  CONVERT(VARCHAR(20), r.Estimate_Action_Date, 103)
        $sql = "SELECT CONVERT(VARCHAR(20), r.Estimate_Action_Date, 103) AS Estimate_Action_Date,r.Doc_Refer_Int 
					,(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Source_Id) AS supplier
					, d.Product_Code,p.Product_NameEN
					, d.Reserv_Qty, CONVERT(VARCHAR(20), r.Actual_Action_Date, 103) AS Actual_Action_Date
					, CONVERT(VARCHAR(20), t.Unlock_Pending_Date, 103) AS Pending_Date
					, d.Confirm_Qty, d.Remark
				FROM STK_T_Order_Detail d, STK_T_Order r,STK_M_Product  p,STK_T_Inbound t
				WHERE d.Order_Id=r.Order_Id 
					AND d.Product_Id=p.Product_Id
					AND r.Document_No=t.Document_No
					AND d.Product_Id=t.Product_Id
					AND r.Process_Type='INBOUND' 
					AND r.Estimate_Action_Date=DATEADD(dd, DATEDIFF(dd, 0, GETDATE()), 0)
					ORDER BY r.Estimate_Action_Date ASC,Source_Id ASC
			 ";
        $query = $this->db->query($sql);
        $result = $query->result();
        $rows = array();
        $i = 0;
        foreach ($result as $row) {
            $rows[] = $row;
        }
        //p($rows);
        return $rows;
    }

    # daily dispaching report

    function getDDR() {
        $sql = "SELECT CONVERT(VARCHAR(20), r.Estimate_Action_Date, 103) AS Estimate_Action_Date,r.Doc_Refer_Int 
					,(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Source_Id) AS supplier
					, d.Product_Code,p.Product_NameEN
					, d.Reserv_Qty, CONVERT(VARCHAR(20), r.Actual_Action_Date, 103) AS Actual_Action_Date
					, d.Confirm_Qty, d.Remark
				FROM STK_T_Order_Detail d, STK_T_Order  r,STK_M_Product  p
				WHERE d.Order_Id=r.Order_Id AND d.Product_Id=p.Product_Id
					AND r.Process_Type='OUTBOUND' AND r.Estimate_Action_Date=DATEADD(dd, DATEDIFF(dd, 0, GETDATE()), 0)
					ORDER BY r.Estimate_Action_Date ASC,Source_Id ASC
			 ";
        $query = $this->db->query($sql);
        $result = $query->result();
        $rows = array();
        $i = 0;
        foreach ($result as $row) {
            $rows[] = $row;
        }
        return $rows;
    }

    function searchAging($search) {
        $age_range = $this->getAgeRange($search['period'], $search['step']);
        if ($search['product_id'] == "") {
            $sql = "SELECT DISTINCT(i.Product_Id),p.Product_Code,p.Product_NameEN,ProductCategory_Id
						FROM STK_T_Inbound i,STK_M_Location l,STK_M_Product p
						WHERE Actual_Location_Id=l.Location_Id
						  AND i.Active='Y' 
						  AND i.Product_Id=p.Product_Id
				";

            if ($search['renter_id']) {
                $sql.=" AND i.Renter_Id=" . $search['renter_id'];
            }

            if ($search['warehouse_id']) {
                $sql.=" AND l.Warehouse_Id=" . $search['warehouse_id'];
            }

            if ($search['category_id']) {
                $sql.=" AND ProductCategory_Id='" . $search['category_id'] . "'";
            }
        } else {
            $sql = "SELECT DISTINCT(i.Product_Id),p.Product_Code,p.Product_NameEN,ProductCategory_Id
						FROM STK_T_Inbound i,STK_M_Location l,STK_M_Product p
						WHERE Actual_Location_Id=l.Location_Id
						  AND i.Active='Y' 
						  AND i.Product_Id=p.Product_Id
						  AND i.Product_Id=" . $search['product_id'] . "
				";
        }

        $query = $this->db->query($sql);
        $result = array();
        foreach ($query->result() as $row) {
            $r = array();
            $r['Product_Id'] = $row->Product_Id;
            $r['Product_Code'] = $row->Product_Code;
            $r['Product_Name'] = $row->Product_NameEN;
            $i = 0;
            foreach ($age_range as $key => $value) {
                $r['count'][$i] = $this->countProductExpired($row->Product_Id, $search['as_date'], $value);
                $i++;
            }
            //'Product_Code'=>$row->Product_Code,
            //'Product_Name'=>$row->Product_NameEN
            $result[] = $r;
        }
        //p($result);

        return $result;
    }

    function getAgeRange($period, $step) {
        if ($period == "") {
            $range = array(
                "N/A" => array(0, 0),
                "<=0" => array(0, -1),
                "0-30" => array(0, 30),
                "31-60" => array(31, 60),
                "61-90" => array(61, 90),
                ">90" => array(-1, 91)
            );
            return $range;
            exit();
        }

        $number_step = ceil($period / $step);
        $mod = $period % $step;
        //echo '<br> number of step = '.$number_step;
        for ($i = 1; $i <= $step; $i++) {
            if ($i == 1) {
                $range["0-" . $number_step] = array(0, $number_step);
            } else {
                $start = ($number_step * ($i - 1)) + 1;
                if ($mod != 0 && $i == $step) {
                    $range[">=" . $start] = array($start, 0);
                } else {
                    $stop = $i * $number_step;
                    $range[$start . "-" . $stop] = array($start, $stop);
                }
            }
        }
        return $range;
    }

    function countProductShelfLife($limit_flag=TRUE,$param, $limit_start = 0, $limit_max = 100, $is_report = FALSE) {
    	$param["as_date"] = convertDate($param["as_date"], "eng", "iso", "-"); //ADD BY POR 2013-11-28 แก้ไขให้แปลงวันที่ก่อนนำไป query 
        $this->db->select("a.Product_Code
		,b.Product_NameEN
		,e.Warehouse_NameEN
		,SUM(CASE WHEN DATEDIFF(MONTH, a.Receive_Date,'" . $param["as_date"] . "') IN (0,1,2,3) THEN Balance_Qty ELSE '' END ) as 'threeMonth'
		,SUM(CASE WHEN DATEDIFF(MONTH, a.Receive_Date,'" . $param["as_date"] . "') IN (4,5,6) THEN Balance_Qty ELSE '' END ) as 'sixMonth'
		,SUM(CASE WHEN DATEDIFF(MONTH, a.Receive_Date,'" . $param["as_date"] . "') IN (7,8,9) THEN Balance_Qty ELSE '' END ) as 'nineMonth'
		,SUM(CASE WHEN DATEDIFF(MONTH, a.Receive_Date,'" . $param["as_date"] . "') IN (10,11,12) THEN Balance_Qty ELSE '' END ) as 'oneYear'
		,SUM(CASE WHEN DATEDIFF(MONTH, a.Receive_Date,'" . $param["as_date"] . "') IN (13,14,15,16,17,18) THEN Balance_Qty ELSE '' END ) as 'oneHalfYear'
		,SUM(CASE WHEN DATEDIFF(MONTH, a.Receive_Date,'" . $param["as_date"] . "') BETWEEN 19 AND 24 THEN Balance_Qty ELSE '' END ) as 'twoYear'
		,SUM(CASE WHEN DATEDIFF(MONTH, a.Receive_Date,'" . $param["as_date"] . "') > 24 THEN Balance_Qty ELSE '' END ) as 'moreTwoYear'
		,SUM (Balance_Qty) as total");
    	$this->db->join("STK_M_Product b","a.Product_Id = b.Product_Id","LEFT");
    	$this->db->join("STK_M_Location d","a.Actual_Location_Id = d.Location_Id","LEFT OUTER");
    	$this->db->join("STK_M_Warehouse e","d.Warehouse_Id = e.Warehouse_Id","LEFT OUTER");
    	$this->db->group_by("e.Warehouse_Id,a.Product_Code,b.Product_NameEN,e.Warehouse_NameEN");
    	$this->db->having("SUM (Balance_Qty) <> 0"); //ADD BY POR 2014-03-19 แก้ไขไม่ให้แสดง record ที่มี balance = 0
        
        if($limit_flag): //ADD BY POR 2014-03-20 If condition $limit_flag = TRUE use limit
            $this->db->limit($limit_max, $limit_start);
        endif;
        
        $this->db->where("a.Active", ACTIVE); 
        
    	if ($param['renter_id'] != "") {
    		$this->db->where("a.Renter_Id", $param['renter_id']);
    	}
    	if ($param['warehouse_id'] != "") {
    		$this->db->where("d.Warehouse_Id", $param['warehouse_id']);
    	}
    	if ($param['category_id'] != "") {
    		$this->db->where("b.ProductCategory_Id", $param['category_id']);
    	}
    	if ($param['product_id'] != "") {
    		$this->db->where("a.Product_Id", $param['product_id']);
    	}
    	$this->db->order_by('a.Product_Code', 'ASC');
		$query = $this->db->get("STK_T_Inbound a");        
		$result = $query->result();
                
//                p($this->db->last_query());exit();
		return $result;
    }
    
    public function count_shelf_report($param)
    {
    	$this->db->select("a.Product_Code,b.Product_NameEN,e.Warehouse_NameEN");
    	$this->db->join("STK_M_Product b","a.Product_Id = b.Product_Id","LEFT");
    	$this->db->join("STK_M_Location d","a.Actual_Location_Id = d.Location_Id","LEFT OUTER");
    	$this->db->join("STK_M_Warehouse e","d.Warehouse_Id = e.Warehouse_Id","LEFT OUTER");
    	$this->db->group_by("e.Warehouse_Id,a.Product_Code,b.Product_NameEN,e.Warehouse_NameEN");
    	$this->db->having("SUM (Balance_Qty) <> 0"); //ADD BY POR 2014-03-19 แก้ไขไม่ให้แสดง record ที่มี balance = 0
        
    	if ($param['renter_id'] != "") {
    		$this->db->where("a.Renter_Id", $param['renter_id']);
    	}
    	if ($param['warehouse_id'] != "") {
    		$this->db->where("d.Warehouse_Id", $param['warehouse_id']);
    	}
    	if ($param['category_id'] != "") {
    		$this->db->where("b.ProductCategory_Id", $param['category_id']);
    	}
    	if ($param['product_id'] != "") {
    		$this->db->where("a.Product_Id", $param['product_id']);
    	}
        
        $this->db->where("a.Active",ACTIVE); //ADD BY POR 2014-02-28 เพิ่มเช็ค active ด้วย
    	$query = $this->db->get("STK_T_Inbound a");
    	$result = $query->result();
    	return count($result);
    	
    }

}
