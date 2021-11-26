<?php

class location_history_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function locationHistoryReport($param) {
        $this->load->helper('date');
        //--#Comment 2013-09-06 BY POR #2235 DEFECT 370 เปลี่ยนเป็นเรียกใช้โค้ดใน PHP แทน stored procedure
        # Start --
        /*
          if($param["product_id"]!= ""){
          $product_id = $param["product_id"];
          }
          else{
          $product_id = "''";
          }
          if($param["doc_type"]!= ""){
          $doc_type = $param["doc_type"];
          }
          else{
          $doc_type = "'%%'";
          }
          if($param["serial"]!= ""){
          $serial = "'".$param["serial"]."'";
          }
          else{
          $serial = "'%%'";
          }
          if($param["ref_value"]!= ""){
          $ref_value = $param["ref_value"];
          }
          else{
          $ref_value = "'%%'";
          }
          if($param["date_from"]!= ""){
          $date_from = "'".$param["date_from"]."'";
          }
          else{
          $date_from = "''";
          }
          if($param["current_location"]!= ""){
          $current_location = "'".$param["current_location"]."'";
          }
          else{
          $current_location = "'%%'";
          }
          if($param["lot"]!= ""){
          $lot = "'".$param["lot"]."'";
          }
          else{
          $lot = "'%%'";
          }
          //echo $product_id;
          $str = "EXEC showLocationHistoryReport ".$product_id.",".$current_location.",".$lot
          .",".$serial.",".$ref_value.",".$doc_type.",".$date_from;
          //echo ">>>".$str;
         */
        #---END
        #2235 DEFECT 370
        #DATE:2013-09-06
        #BY:POR
        #-- START--
        //หา product code
        if ($param["product_id"]):
            $sql_product = "SELECT Product_Code FROM STK_M_Product WHERE Product_Id=" . $param["product_id"];
            $qryproduct = $this->db->query($sql_product);
            $resdata = $qryproduct->result();
            $product_code = $resdata[0]->Product_Code;
        endif;

        // Sub_Query has more than one rows => Fix by add top 1
        // By Ball            
        $str = "SELECT data_view.Product_Code,prod.Product_NameEN,data_view.Product_Lot,data_view.Product_Serial
            ,CASE '" . $param["doc_type"] . "'
                    WHEN 'Document_No' THEN data_view.Document_No 
                    WHEN 'Doc_Refer_Ext' THEN data_view.Doc_Refer_Ext
                    WHEN 'Doc_Refer_Int' THEN data_view.Doc_Refer_Int
                    WHEN 'Doc_Refer_Inv' THEN data_view.Doc_Refer_Inv
                    WHEN 'Doc_Refer_CE' THEN data_view.Doc_Refer_CE
                    WHEN 'Doc_Refer_BL' THEN data_view.Doc_Refer_BL
                    ELSE 'Document_no' END as doc_type
            ,data_view.Document_No
            ,data_view.*
            
            ,plt.Pallet_Code
            
            FROM
            (SELECT
            aa.Inbound_Id as currentid
            ,(SELECT Distinct Location_Code FROM STK_M_Location WHERE Location_ID = aa.Actual_Location_Id) as actual_now
            ,CONVERT(VARCHAR(20), aa.Putaway_Date, 103) as currentdate
            ,aa.Balance_Qty as current_qty
            ,aa.Price_Per_Unit as current_price
            , do_current.Dom_EN_SDesc AS current_Unit_price
            ,(aa.Receive_Qty * aa.Price_Per_Unit) AS current_All_price
            ,b.Inbound_Id as inbound_id4
            ,(SELECT Distinct Location_Code FROM STK_M_Location WHERE Location_ID = b.Actual_Location_Id) as actual4
            ,CONVERT(VARCHAR(20), b.Putaway_Date, 103) as date4
            ,b.Balance_Qty as qty4
            ,b.Price_Per_Unit as price4
            , do4.Dom_EN_SDesc AS Unit_price4
            ,(b.Receive_Qty * b.Price_Per_Unit) AS All_price4
            ,c.Inbound_Id as inbound_id3
            ,(SELECT Distinct Location_Code FROM STK_M_Location WHERE Location_ID = c.Actual_Location_Id) as actual3
            ,CONVERT(VARCHAR(20), c.Putaway_Date, 103) as date3
            ,c.Balance_Qty as qty3
            ,c.Price_Per_Unit as price3
            , do3.Dom_EN_SDesc AS Unit_price3
            ,(c.Receive_Qty * c.Price_Per_Unit) AS All_price3
            ,d.Inbound_Id as inbound_id2
            ,(SELECT Distinct Location_Code FROM STK_M_Location WHERE Location_ID = d.Actual_Location_Id) as actual2
            ,CONVERT(VARCHAR(20), d.Putaway_Date, 103) as date2
            ,d.Balance_Qty as qty2
            ,d.Price_Per_Unit as price2
            , do2.Dom_EN_SDesc AS Unit_price2
            ,(d.Receive_Qty * d.Price_Per_Unit) AS All_price2
            ,e.Inbound_Id as inbound_id1
            ,(SELECT Distinct Location_Code FROM STK_M_Location WHERE Location_ID = e.Actual_Location_Id) as actual1
            ,CONVERT(VARCHAR(20), e.Putaway_Date, 103) as date1
            ,e.Balance_Qty as qty1
            ,e.Price_Per_Unit as price1
            , do1.Dom_EN_SDesc AS Unit_price1
            ,(e.Receive_Qty * e.Price_Per_Unit) AS All_price1
            --,aa.Product_Code,aa.Product_Lot,aa.Product_Serial,aa.Document_No
            ,aa.*
            FROM
            (SELECT * FROM STK_T_Inbound a
            WHERE
            a.Active = 'Y' ) as aa
            LEFT JOIN SYS_M_Domain do_current ON aa.Unit_Price_Id = do_current.Dom_Code AND do_current.Dom_Host_Code ='PRICE_UNIT' AND do_current.Dom_Active = 'Y' 
            LEFT OUTER JOIN STK_T_Inbound b ON aa.History_Item_Id = b.Inbound_Id
            LEFT JOIN SYS_M_Domain do4 ON b.Unit_Price_Id = do4.Dom_Code AND do4.Dom_Host_Code ='PRICE_UNIT' AND do4.Dom_Active = 'Y'
            LEFT OUTER JOIN STK_T_Inbound c ON b.History_Item_Id = c.Inbound_Id
            LEFT JOIN SYS_M_Domain do3 ON c.Unit_Price_Id = do3.Dom_Code AND do3.Dom_Host_Code ='PRICE_UNIT' AND do3.Dom_Active = 'Y'
            LEFT OUTER JOIN STK_T_Inbound d ON c.History_Item_Id = d.Inbound_Id
            LEFT JOIN SYS_M_Domain do2 ON d.Unit_Price_Id = do2.Dom_Code AND do2.Dom_Host_Code ='PRICE_UNIT' AND do2.Dom_Active = 'Y'
            LEFT OUTER JOIN STK_T_Inbound e ON d.History_Item_Id = e.Inbound_Id
            LEFT JOIN SYS_M_Domain do1 ON e.Unit_Price_Id = do1.Dom_Code AND do1.Dom_Host_Code ='PRICE_UNIT' AND do1.Dom_Active = 'Y')
            as data_view
            LEFT OUTER JOIN dbo.STK_M_Product prod
            ON data_view.Product_Code = prod.Product_Code
            
            LEFT JOIN dbo.STK_T_Pallet plt
            ON data_view.Pallet_Id = plt.Pallet_Id
            
            WHERE current_qty <> 0";
        if ($param["product_id"] != ""):
            $str.=" AND data_view.Product_Code LIKE '%" . $product_code . "%'";
        endif;

        if ($param["current_location"] != ""):
            $str.=" AND data_view.actual_now LIKE '%" . $param["current_location"] . "%'";
        endif;

        if ($param["lot"] != ""):
            $str.=" AND data_view.Product_Lot LIKE '%" . $param["lot"] . "%'";
        endif;

        if ($param["serial"] != ""):
            $str.=" AND data_view.Product_Serial LIKE '%" . $param["serial"] . "%'";
        endif;

        if ($param["ref_value"] != ""):
            $str.=" AND " . $param["doc_type"] . " LIKE '%" . $param["ref_value"] . "%'";
        endif;

        if ($param["date_from"] != ""):
            $date_from = convertDate($param['date_from'], "eng", "iso", "-");
            $str.=" AND data_view.Putaway_Date BETWEEN '" . $param['date_from'] . " 00:00:00 ' AND '" . date('Y-m-d') . " 23:59:59 '";
        endif;

        
        if ($param["pallet_code"] != ""):
            $str.=" AND plt.Pallet_Code LIKE '%" . $param["pallet_code"] . "%'";
        endif;
        
        #--END--

        $query = $this->db->query($str);
        //    echo $this->db->last_query();exit();
        //    $query=$this->db->get();
        //    p( $this->db->last_query());exit;
        $result = $query->result();
        return $result; //[0]->count_product;
    }

    function showLocationAll($txt_search='') {
        //$location_code=strip_tags($location_code);
        //--#Comment 2013-08-29 #Edit data duplicate case search Current Location in 'Location History'
        # Start By POR--
        /* $this->db->select("Location_Id,Location_Code"); */
        # End Comment 2013-08-29 #Edit data duplicate case search Current Location in 'Location History' --
        #Edit data duplicate case search Current Location in 'Location History'
        #DATE:2013-08-29
        #BY:POR
        #--Start--
        $this->db->select("Location_Code");
        #--End Edit data duplicate case search Current Location in 'Location History'--

        $this->db->from("STK_M_Location l");
        $this->db->join("STK_M_Storage_Detail s", "l.Storage_Detail_Id=s.Storage_Detail_Id", "left");
        
        if($txt_search!=""):
            $this->db->where("Location_Code LIKE '%".$txt_search."%'");
        endif;
        //$this->db->where("Location_Code!=",$location_code);
        //$this->db->where("l.Active",ACTIVE);
        //$this->db->where("Is_Full",'N');
        #Edit data duplicate case search Current Location in 'Location History'
        #DATE:2013-08-29
        #BY:POR
        #--Start--
        $this->db->group_by("Location_Code");
        #--End Edit data duplicate case search Current Location in 'Location History'--

        $sug_query = $this->db->get();
        
        
        //ADD BY POR 2013-10-29 กำหนดเพิ่มเติมเนื่องจากไปไว้ใช้ตอนค้นหา location ในเมนู Location History (ต้องส่งค่าแบบ array กลับไป)
        if($txt_search!=""):
            $i = 0;
            $sug_result = array();
            foreach ($sug_query->result() as $row) {
                $sug_result[$i]['Location_Code'] = $row->Location_Code;
                $i++;
            }
        else:
            $sug_result = $sug_query->result();
        endif;
        //p($sug_result);
        return $sug_result;
    }

    
    function select_vw_location_history_new(){
        return $this->db->query("SELECT * FROM vw_location_history_new ORDER BY CASE Product_Status WHEN 'booked' THEN 0 WHEN 'dispatch' THEN 1 ELSE 2 END ASC");
    }
    
}
