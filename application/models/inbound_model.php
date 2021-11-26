<?php

/**
 * Description of inbound_model
 * -------------------------------------
 * Put this class on project at 22/04/2013
 * @author Thida
 * Create by NetBean IDE 7.3.1
 * SWA WMS PLUS Project.
 * Use Codeinigter Framework with combination of css and js.
 * --------------------------------------
 */
class inbound_model extends CI_Model {

    //put your code here
    function __construct() {
        parent::__construct();
    }

    public function getProductDetails($product_code = "", $productStatus = "", $productSubStatus = "", $productLot = "", $productSerial = "", $productMfd = "", $productExp = "", $limit_start = 0, $limit_max = 100, $s_search = "", $palletCode_val = "",$chkPallet_val= "",$palletIsFull_val="",$palletDispatchType_val="", $order_by = NULL, $productFilter = NULL, $docRefExt = NULL) {
        $productCode = trim($product_code);

        if($palletDispatchType_val=='FULL'): //ADD BY POR 2014-02-18 กรณีเป็นแบบ Dispatch Full จะแสดงรายละเอียดเป็น pallet
            $this->db->select("STK_T_Pallet.Pallet_Id
                            ,Pallet_Code
                            ,SYS_M_Domain.Dom_EN_SDesc as Pallet_Type
                            ,Pallet_Name");
            $this->db->from("STK_T_Pallet");
            $this->db->join("vw_inbound",'STK_T_Pallet.Pallet_Id=vw_inbound.Pallet_Id', 'LEFT');
            $this->db->join("SYS_M_Domain d", "vw_inbound.Product_Status = d.Dom_Code and d.Dom_Host_Code ='prod_status' and d.Dom_Active ='Y' ", "LEFT");
            $this->db->where("Pallet_Type",$palletIsFull_val);
            $this->db->group_by("STK_T_Pallet.Pallet_Id,Pallet_Code,Pallet_Type,Pallet_Name,Product_Code");
            $this->db->order_by("STK_T_Pallet.Pallet_Id");
            if(!empty($order_by)):
                $this->db->order_by($order_by);
            endif;
        else:
            $this->db->select("vw_inbound.*,d.Dom_TH_Desc AS Dom_TH_Desc,STK_T_Pallet.Pallet_Code");
            $this->db->from("vw_inbound");
            $this->db->join('STK_T_Pallet','vw_inbound.Pallet_Id = STK_T_Pallet.Pallet_Id', 'LEFT');
            $this->db->join("SYS_M_Domain d", "vw_inbound.Product_Status = d.Dom_Code and d.Dom_Host_Code ='prod_status' and d.Dom_Active ='Y' ", "LEFT");            if(!empty($order_by)):
                $this->db->order_by($order_by);
            endif;
            $this->db->order_by("CASE vw_inbound.PutAway_Rule
                                WHEN 'FIFO' THEN vw_inbound.Receive_Date_sort
                                WHEN 'FEFO' THEN vw_inbound.Product_Exp_sort
                                ELSE vw_inbound.Actual_Location_Id
                                END");
        endif;

        //ADD BY POR 2014-02-11 กรณีเลือกค้นหาด้วย pallet
        if($chkPallet_val==1):
            //Pallet Code
            if ($palletCode_val != "") {
                $this->db->like('STK_T_Pallet.Pallet_Code', $palletCode_val);
            }

            $this->db->where("STK_T_Pallet.Pallet_Type", $palletIsFull_val);
            $this->db->where("vw_inbound.Pallet_Id IS NOT NULL");
        else:
            //$this->db->where("vw_inbound.Pallet_Id IS NULL"); comment by kik : 20140614 เพราะว่าถึงแม้จะไม่ได้ set config ให้เปิด pallet แต่ถ้า ใน database มีข้อมูลอยู่ แล้วเขียนโค้ดไว้แบบนี้จะทำให้กระทบ จึง comment ไว้ก่อน

        endif;
        //END ADD

        // Add By Akkarapol, 29/11/2013, เพิ่มการ filter จากช่อง Search ด้วยการใช้ like
        if ("" != $s_search) :

            $this->db->bracket('open', 'like', 'and'); //bracket closed

            $this->db->or_like("STK_T_Pallet.Pallet_Code", $s_search);
            $this->db->or_like("vw_inbound.Product_Code", $s_search);
            $this->db->or_like("vw_inbound.Product_NameEN", $s_search);
            $this->db->or_like("vw_inbound.Product_Status", $s_search);
            $this->db->or_like("vw_inbound.Product_Sub_Status", $s_search);
            $this->db->or_like("vw_inbound.Location_Code", $s_search);
            $this->db->or_like("vw_inbound.Product_Lot", $s_search);
            $this->db->or_like("vw_inbound.Product_Serial", $s_search);

            $this->db->bracket('close', 'like', 'and'); //bracket closed
            
        endif;
        //else :

            //Product Code / SKU
            if ($productCode != "") {
//                $this->db->like('vw_inbound.Product_Code', $productCode);
		$this->db->where('vw_inbound.Product_Code', $productCode);
            }

            //Product Status
            if ($productStatus != "") {
                $this->db->where("vw_inbound.Product_Status", $productStatus);
            }

            //Product Sub Status
            if ($productSubStatus != "") {
                $this->db->where("vw_inbound.Product_Sub_Status_sort", $productSubStatus);
            }

            //Product Lot
            if ($productLot != "") {
                $this->db->like("vw_inbound.Product_Lot", $productLot);
            }

            //Product Serail
            if ($productSerial != "") {
                $this->db->like("vw_inbound.Product_Serial", $productSerial);
            }

            //Product Mfd
            if ($productMfd != "") {
                $this->db->where("vw_inbound.Product_Mfd_sort", convertDate($productMfd, "eng", "iso", "-"));
            }

            //Product Exp
            if ($productExp != "") {
                $this->db->where("vw_inbound.Product_Exp_sort", convertDate($productExp, "eng", "iso", "-"));
            }

            //Document Refer Ext
            if ($docRefExt != "") {
                $this->db->where("vw_inbound.Doc_Refer_Ext", $docRefExt);
            }

            //Product Filter By ID
            if (!is_null($productFilter)) {
                $this->db->where_not_in("vw_inbound.Inbound_Id", explode(",",$productFilter));
            }

        //endif;

        $this->db->limit($limit_max, $limit_start); // Add By Akkarapol, 29/11/2013, เพิ่ม $this->db->limit($limit_max, $limit_start); สำหรับทำการโหลดเพจแบบ หน้าต่อหน้า
        $query = $this->db->get();
    //    p($this->db->last_query());

       //this yah product order
        return $query;
    }

    public function getDispatchProductDetails($params) {

        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];

        $productCode = trim($params->product_code);

        $xml = $this->native_session->get_xml_data();
        // p($xml);exit;
        $pd_status_allow = explode(",", $xml['pd_status_allow']);
        // $pd_status_allow = $this->status_from_sysDomain($pd_status_allow);

        $this->db->select("Dom_EN_Desc");
        $this->db->from("SYS_M_Domain");
        $this->db->where("Dom_Host_Code = 'prod_status'");
        $this->db->where("Dom_Active = 'Y'");
        $this->db->where_in("Dom_Code ",$pd_status_allow);
        $query_status = $this->db->get();
        $result = $query_status->result();
        $range = array();
        foreach ($result as $key => $row) {
            $range[] = $row->Dom_EN_Desc;
        }
        // p($range);
        // p($pd_status_allow);exit;
        // $pd_status_allow_domain =  $this->status_from_sysDomain($pd_status_allow);
        // p( $pd_status_allow_domain);
 

        if ($params->palletDispatchType_val == 'FULL'):

            $this->db->select("STK_T_Pallet.Pallet_Id");
            $this->db->select("Pallet_Code");
            $this->db->select("SYS_M_Domain.Dom_EN_SDesc as Pallet_Type");
            $this->db->select("Pallet_Name");
            $this->db->from("STK_T_Pallet");
            $this->db->join("SYS_M_Domain", "STK_T_Pallet.Pallet_Type = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code = 'PALLET_TYPE' AND SYS_M_Domain.Dom_Active = 'Y'", "LEFT");
            $this->db->join("vw_inbound_by_rule", 'STK_T_Pallet.Pallet_Id=vw_inbound_by_rule.Pallet_Id', 'LEFT');
            $this->db->where("Pallet_Type", $palletIsFull_val);
            $this->db->group_by("STK_T_Pallet.Pallet_Id,Pallet_Code,SYS_M_Domain.Dom_EN_SDesc,Pallet_Name");
            $this->db->order_by("STK_T_Pallet.Pallet_Id");

        else:

            $this->db->select("vw_inbound_by_rule.Inbound_Id");
            $this->db->select("vw_inbound_by_rule.Doc_Refer_Ext");
            $this->db->select("vw_inbound_by_rule.Product_NameEN");
            $this->db->select("vw_inbound_by_rule.Product_Id");
            $this->db->select("vw_inbound_by_rule.Product_Code");
            $this->db->select("vw_inbound_by_rule.Product_Status");
//            $this->db->select("vw_inbound_by_rule.Product_Sub_Status");
//            $this->db->select("vw_inbound_by_rule.User_Defined_6 AS Line");
            $this->db->select("vw_inbound_by_rule.Product_Lot");
            //$this->db->select("vw_inbound_by_rule.Product_Serial");
//            $this->db->select("vw_inbound_by_rule.User_Defined_3 AS Product_Serial");
            $this->db->select("vw_inbound_by_rule.Product_Mfd");
            $this->db->select("vw_inbound_by_rule.Product_Exp");
            $this->db->select("vw_inbound_by_rule.Receive_Date");
            $this->db->select("vw_inbound_by_rule.Unit_Id");
            $this->db->select("vw_inbound_by_rule.Location_Code");
            $this->db->select("vw_inbound_by_rule.Actual_Location_Id");
            $this->db->select("vw_inbound_by_rule.Product_Exp_sort");
            $this->db->select("vw_inbound_by_rule.PutAway_Rule");
            $this->db->select("vw_inbound_by_rule.Receive_Date_sort");
            $this->db->select("SUM(vw_inbound_by_rule.Est_Balance_Qty) As Est_Balance_Qty");
            $this->db->select("SUM(vw_inbound_by_rule.Balance_Qty) As Balance_Qty");
            $this->db->select("Pallet_Code");
            $this->db->select("vw_inbound_by_rule.Product_Sub_Status_sort");
             if($params->use_full == 'TRUE' ){
                    $this->db->select("vw_inbound_by_rule.sort_full_pallet");

             }
            if ($conf_inv):
                $this->db->select("STK_T_Invoice.Invoice_No");
            else:
                $this->db->select("'' as Invoice_No");
            endif;

            $this->db->from("vw_inbound_by_rule");
            $this->db->join('STK_T_Pallet', 'vw_inbound_by_rule.Pallet_Id = STK_T_Pallet.Pallet_Id', 'LEFT');

            if ($conf_inv):
                $this->db->join('STK_T_Invoice', 'vw_inbound_by_rule.Invoice_Id = STK_T_Invoice.Invoice_Id', 'LEFT');
            endif;

//            if (!empty($order_by)):
//                $this->db->order_by($order_by);
//            endif;

//            $this->db->order_by("CASE Product_Sub_Status_sort WHEN 'SS001' THEN 0 WHEN 'SS002' THEN 1 ELSE 2 END ASC , Product_Sub_Status_sort ASC");
            $this->db->order_by("CASE PutAway_Rule WHEN 'FIFO' THEN CASE Receive_Date_sort WHEN NULL THEN 0 ELSE Receive_Date_sort END
                WHEN 'FEFO' THEN CASE Product_Mfd_sort WHEN NULL THEN 0 ELSE Product_Mfd_sort END ELSE vw_inbound_by_rule.Actual_Location_Id END");
              if($params->use_full == 'TRUE' ){
//                    $this->db->select("vw_inbound_by_rule.sort_full_pallet");
                    $this->db->order_by("sort_full_pallet ASC");
             }
	$this->db->order_by("Location_Priority ASC");
        endif;

        if ($params->chkPallet_val == 1):
            if (trim($palletCode_val) != "") {
                $this->db->like('STK_T_Pallet.Pallet_Code', $palletCode_val);
            }
            
            $this->db->where("STK_T_Pallet.Pallet_Type", $palletIsFull_val);
            $this->db->where("vw_inbound_by_rule.Pallet_Id IS NOT NULL");
        else:

        endif;

        if ("" != $params->s_search) :
            $this->db->bracket('open', 'like');
            $this->db->or_like("vw_inbound_by_rule.Product_Code", $params->s_search);
            $this->db->or_like("vw_inbound_by_rule.Product_NameEN", $params->s_search);
            $this->db->or_like("vw_inbound_by_rule.Product_Status", $params->s_search);
            $this->db->or_like("vw_inbound_by_rule.Product_Sub_Status", $params->s_search);
            $this->db->or_like("vw_inbound_by_rule.Location_Code", $params->s_search);
            $this->db->or_like("vw_inbound_by_rule.Product_Lot", $params->s_search);
            //$this->db->or_like("vw_inbound_by_rule.Product_Serial", $params->s_search);
//            $this->db->or_like("vw_inbound_by_rule.User_Defined_3", $params->s_search);
            if ($params->chkPallet_val == 1):
                $this->db->or_like("STK_T_Pallet.Pallet_Code", $params->s_search);
            endif;

            if ($conf_inv and $palletDispatchType_val != 'FULL'):
                $this->db->or_like("STK_T_Invoice.Invoice_No", $params->s_search);
            endif;

            $this->db->bracket('close', 'like');

            if ($params->dp_type != "DP1"):
                if (!empty($pd_status_allow['0'])) :
                    // $dom_status = "select Dom_EN_Desc from SYS_M_Domain where Dom_Host_Code = 'prod_status' and Dom_Active = 'Y'and Dom_Code IN (".$pd_status_allow.")";
                    // $this->db->where_in("vw_inbound_by_rule.Product_Status", $pd_status_allow);
                    $this->db->where_in("vw_inbound_by_rule.Product_Status", $range);
                endif;
            endif;

        else:
            if ($productCode != "" AND empty($s_search)) {
                if (isset($params->exact_match) && $params->exact_match == TRUE) {
                    $this->db->where('vw_inbound_by_rule.Product_Code', $productCode);
                } else {
                    $this->db->like('vw_inbound_by_rule.Product_Code', $productCode);
                }
            }

            if (trim($params->productStatus) != "") {
                $this->db->where("vw_inbound_by_rule.Product_Status", $params->productStatus);
            } else {
                if ($params->dp_type != "DP1"):
                    if (!empty($pd_status_allow['0'])) :
                        $this->db->where_in("vw_inbound_by_rule.Product_Status", $range);
                    endif;
                endif;
            }

            if (trim($params->productSubStatus) != "") {
                $this->db->where("vw_inbound_by_rule.Product_Sub_Status_sort", $params->productSubStatus);
            }

            if (trim($params->productLot) != "") {
                $this->db->like("vw_inbound_by_rule.Product_Lot", $params->productLot);
            }

            if (trim($params->productSerial) != "") {
//                $this->db->like("vw_inbound_by_rule.Product_Serial", $params->productSerial);
//                $this->db->like("vw_inbound_by_rule.User_Defined_3", $params->productSerial);
            }

            if (trim($params->productMfd) != "") {
                $mfd = convertDate($params->productMfd, "eng", "iso", "-");
                $this->db->where("vw_inbound_by_rule.Product_Mfd_sort between '" . $mfd . " 00:00:00' AND '" . $mfd . " 23:59:59'");
            }

            if ($conf_inv == true and $invoiceNo != NULL and $palletDispatchType_val != 'FULL'):
                $this->db->like("STK_T_Invoice.Invoice_No", $invoiceNo);
            endif;

        endif;

        if (trim($params->productExp) != "") {
            $this->db->where("vw_inbound_by_rule.Product_Exp_sort", convertDate($params->productExp, "eng", "iso", "-"));
        }

        $this->db->group_by("vw_inbound_by_rule.Inbound_Id");
        $this->db->group_by("vw_inbound_by_rule.Doc_Refer_Ext");
        $this->db->group_by("vw_inbound_by_rule.Product_NameEN");
        $this->db->group_by("vw_inbound_by_rule.Product_Id");
        $this->db->group_by("vw_inbound_by_rule.Product_Code");
        $this->db->group_by("vw_inbound_by_rule.Product_Status");
//        $this->db->group_by("vw_inbound_by_rule.Product_Sub_Status");
//        $this->db->group_by("vw_inbound_by_rule.User_Defined_6");        =
        $this->db->group_by("vw_inbound_by_rule.Product_Lot");
        //$this->db->group_by("vw_inbound_by_rule.Product_Serial");
//        $this->db->group_by("vw_inbound_by_rule.User_Defined_3");
        $this->db->group_by("vw_inbound_by_rule.Product_Mfd");
	$this->db->group_by("vw_inbound_by_rule.Product_Mfd_Sort");
        $this->db->group_by("vw_inbound_by_rule.Product_Exp");
        $this->db->group_by("vw_inbound_by_rule.Receive_Date");
        $this->db->group_by("vw_inbound_by_rule.Unit_Id");
        $this->db->group_by("vw_inbound_by_rule.Location_Code");
        $this->db->group_by("vw_inbound_by_rule.Actual_Location_Id");
        $this->db->group_by("vw_inbound_by_rule.Product_Exp_sort");
        $this->db->group_by("vw_inbound_by_rule.PutAway_Rule");
        $this->db->group_by("vw_inbound_by_rule.Receive_Date_sort");
        $this->db->group_by("Pallet_Code");
        $this->db->group_by("vw_inbound_by_rule.Product_Sub_Status_sort");
	$this->db->group_by("vw_inbound_by_rule.Location_Priority");
        if($params->use_full == 'TRUE' ){
            $this->db->group_by("vw_inbound_by_rule.sort_full_pallet");

             }
        $this->db->limit($params->limit_max, $params->limit_start);

        $query = $this->db->get();
    //    p($this->db->last_query());exit;
        return $query;

    }    


   

    public function getDispatchProductDetails_FEFO($product_code = "", $productStatus = "", $productSubStatus = "", $productLot = "", $productSerial = "", $productMfd = "", $productExp = "", $limit_start = 0, $limit_max = 100, $s_search = "", $palletCode_val = "",$chkPallet_val= "",$palletIsFull_val="",$palletDispatchType_val="", $order_by = NULL, $dp_type = NULL) {
                // p('MODEL');
                // // p($stage);

                // exit;
                $productCode = trim($product_code);

                // Add xml fof fiter pre-dispatch
                $CI = & get_instance();
                $CI->load->library('native_session');
                $xml = $CI->native_session->get_xml_data();
                $pd_status_allow =explode(",",$xml['pd_status_allow']);
                $FEFO  =$xml['suggest_rule']['FEFO'];
                $FIFO  =$xml['suggest_rule']['FIFO'];
    
                // End
        
                if($palletDispatchType_val=='FULL'): //ADD BY POR 2014-02-18 กรณีเป็นแบบ Dispatch Full จะแสดงรายละเอียดเป็น pallet
                    $this->db->select("STK_T_Pallet.Pallet_Id
                                    ,Pallet_Code
                                    ,SYS_M_Domain.Dom_EN_SDesc as Pallet_Type
                                    ,Pallet_Name");
                    $this->db->from("STK_T_Pallet");
                    $this->db->join("vw_inbound",'STK_T_Pallet.Pallet_Id=vw_inbound.Pallet_Id', 'LEFT');
                    $this->db->where("Pallet_Type",$palletIsFull_val);
        
        //            // Condition for check pd
        //            // Check first array not empty
                   if (!empty($pd_status_allow['0'])) :
                   	$this->db->where_in("Product_Status", $pd_status_allow);
                   endif;
        
                    $this->db->group_by("STK_T_Pallet.Pallet_Id,Pallet_Code,SYS_M_Domain.Dom_EN_SDesc,Pallet_Name");
                    $this->db->order_by("STK_T_Pallet.Pallet_Id");
                else:
                    // select product in subStatus value Return and Repack only
                    $this->db->select("vw_inbound.*,d.Dom_TH_Desc AS Dom_TH_Desc,Pallet_Code");
                    $this->db->from("vw_inbound");
                    $this->db->join('STK_T_Pallet','vw_inbound.Pallet_Id = STK_T_Pallet.Pallet_Id', 'LEFT');
                    $this->db->join("SYS_M_Domain d", "vw_inbound.Product_Status = d.Dom_Code and d.Dom_Host_Code ='prod_status' and d.Dom_Active ='Y' ", "LEFT");
                    
        
        //            // Condition for check pd
        //            // Check first array not empty
        //            if (!empty($pd_status_allow['0'])) :
        //            	$this->db->where_in("Product_Status", $pd_status_allow);
        //            endif;
        
                    // if(!empty($order_by)):
                    //     $this->db->order_by($order_by);
                    // endif;
        
                    $this->db->order_by("CASE Product_Sub_Status_sort WHEN 'SS001' THEN 0 WHEN 'SS002' THEN 1 ELSE 2 END ASC , Product_Sub_Status_sort ASC"); // Add By Akkarapol, 28/11/2013, เพิ่มการ order โดยให้ SS001 ขึ้นก่อน ตามด้วย SS002 และก็ order ด้วย Dom_Code ต่ออีกที เพื่อให้ Return ขึ้นก่อนและตามด้วย Repackage จากนั้นก็ เรียงตาม Dom_Code ด้วยการใช้ CASE WHEN
        //            $this->db->order_by("CASE PutAway_Rule
        //                                WHEN 'FIFO' THEN CASE Receive_Date_sort WHEN NULL THEN 0 ELSE 1 END
        //                                WHEN 'FEFO' THEN CASE Product_Exp_sort WHEN NULL THEN 1 ELSE 0 END
        //                                ELSE vw_inbound.Actual_Location_Id
        //                                END");
                    $this->db->order_by("CASE PutAway_Rule
                                        WHEN  'FIFO'
                                            THEN CASE $FIFO WHEN NULL THEN 0 ELSE $FIFO END
                                        WHEN 'FEFO'
                                            THEN CASE $FEFO WHEN NULL THEN 0 ELSE $FEFO END
                                        ELSE vw_inbound.Actual_Location_Id
                                        END");
//		    $this->db->order_by("vw_inbound.sort_full_pallet ASC");
		    $this->db->order_by("Location_Priority ASC");
                endif;
        
                //ADD BY POR 2014-02-11 กรณีเลือกค้นหาด้วย pallet
                if($chkPallet_val==1):
                    //Pallet Code
                    if ($palletCode_val != "") {
                        $this->db->like('STK_T_Pallet.Pallet_Code', $palletCode_val);
                    }
        
                    $this->db->where("STK_T_Pallet.Pallet_Type", $palletIsFull_val);
        
        //            // Condition for check pd
        //            // Check first array not empty
        //            if (!empty($pd_status_allow['0'])) :
        //            	$this->db->where_in("Product_Status", $pd_status_allow);
        //            endif;
        
                    $this->db->where("vw_inbound.Pallet_Id IS NOT NULL");
                else:
                    //$this->db->where("vw_inbound.Pallet_Id IS NULL");comment by kik : 20140614 เพราะว่าถึงแม้จะไม่ได้ set config ให้เปิด pallet แต่ถ้า ใน database มีข้อมูลอยู่ แล้วเขียนโค้ดไว้แบบนี้จะทำให้กระทบ จึง comment ไว้ก่อน
                endif;
                //END ADD
        
        //        $this->db->where("Product_Sub_Status_sort IN ('SS001','SS002')"); // Comment By Akkarapol, 28/11/2013, คอมเม้นต์ทิ้งเพราะสามารถใช้การ query เดียวหาค่าด้วยการใช้ ORDER แบบ CASE WHEN มาใช้แทนการ query สองชุดมาต่อกันได้แล้ว
                // Add By Akkarapol, 29/11/2013, เพิ่มการ filter จากช่อง Search ด้วยการใช้ like
                if ("" != $s_search) :
                    $this->db->bracket('open','like'); //bracket closed
                    $this->db->or_like("vw_inbound.Product_Code", $s_search);
                    $this->db->or_like("vw_inbound.Product_NameEN", $s_search);
                    $this->db->or_like("vw_inbound.Product_Status", $s_search);
                    $this->db->or_like("vw_inbound.Product_Sub_Status", $s_search);
                    $this->db->or_like("vw_inbound.Location_Code", $s_search);
                    $this->db->or_like("vw_inbound.Product_Lot", $s_search);
                    $this->db->or_like("vw_inbound.Product_Serial", $s_search);
                    if($chkPallet_val==1):
                        $this->db->or_like("STK_T_Pallet.Pallet_Code", $s_search);
                    endif;
                    $this->db->bracket('close','like'); //bracket closed
        
                    if($dp_type != "DP0"):
                        if (!empty($pd_status_allow['0'])) :
                            $this->db->where_in("vw_inbound.Product_Status", $pd_status_allow);
                        endif;
                    endif;
        
                    // Akkarapol, 24/04/2014, เปลี่ยนจากที่ใช้การ or_like เป็น like เฉยๆ เพื่อให้ sql ที่ออกมาเป็น and เพราะตอนนี้ให้ modal นั้นดึงข้อมูลตามการ filter ที่เลือกตลอดเวลา เพื่อป้องกันการลักไก่ ที่จะเกิดขึ้นจากการดึงของที่ติดสถานะห้ามจ่าย เอามาจ่ายนั่นเอง
        //            $this->db->like("Product_Code", $s_search);
        //            $this->db->like("Product_NameEN", $s_search);
        //            $this->db->like("Product_Status", $s_search);
        //            $this->db->like("Product_Sub_Status", $s_search);
        //            $this->db->like("Location_Code", $s_search);
        //            $this->db->like("Product_Lot", $s_search);
        //            $this->db->like("Product_Serial", $s_search);
        
                else:
                    // Add By Akkarapol, 28/01/2014, ย้าย filter ต่างๆเข้ามาใน else ที่กรณีไม่มีค่าของ sSearch เพราะว่า หากมีการใช้ช่อง search ก็หมายความว่า ต้องการเซ็ตค่าของ filter ใหม่นั่นเอง
                    //Product Code / SKU
        
                    // $this->db->join("SYS_M_Domain d", "vw_inbound.Product_Status = d.Dom_Code and d.Dom_Host_Code ='prod_status' and d.Dom_Active ='Y' ", "LEFT");
                    if ($productCode != "" AND empty($s_search)) { // Edit By Akkarapol, 21/01/2014, add ' AND empty($s_search)' in if because search by $product_code when $s_search is empty
        //                $this->db->like('vw_inbound.Product_Code', $productCode); // 
                $this->db->where('vw_inbound.Product_Code', $productCode);
                    }
                    // p($productStatus);
                    //Product Status
                    if ($productStatus != "") {
                        $this->db->where("vw_inbound.Product_Status", $productStatus);
                    }else{
                        // Condition for check pd
                        // Check first array not empty
                        if($dp_type != "DP0"):
                            if (!empty($pd_status_allow['0'])) :
                                $this->db->where_in("vw_inbound.Product_Status", $pd_status_allow);
                     
                            endif;
                        endif;
        
                    }
                    
                    // Inbound Id
                    if(!empty($tmp_select_inbound_id[0])){
                        if ($stage == 1) {
                            foreach ($tmp_select_inbound_id as $key => $inbound) {
                                if($key == 0){
    
                                    $this->db->where("(vw_inbound.Inbound_id = $inbound and vw_inbound.Est_Balance_Qty - $arr_select_reserv_qty[$key]  > 0)");
                                }else{
                                    $this->db->or_where("(vw_inbound.Inbound_id = $inbound and vw_inbound.Est_Balance_Qty - $arr_select_reserv_qty[$key]  > 0)");
                                }
    
                            }
                        }
                    }
            
                    //Product Sub Status
                    if ($productSubStatus != "") {
                        $this->db->where("vw_inbound.Product_Sub_Status_sort", $productSubStatus);
                    }
        
                    //Product Lot
                    if ($productLot != "") {
                        $this->db->like("vw_inbound.Product_Lot", $productLot);
                    }
        
                    //Product Serail
                    if ($productSerial != "") {
                        $this->db->like("vw_inbound.Product_Serial", $productSerial);
                    }
        
                    //Product Mfd
                    if ($productMfd != "") {
                        $this->db->where("vw_inbound.Product_Mfd_sort", convertDate($productMfd, "eng", "iso", "-"));
                    }
                    // END Add By Akkarapol, 28/01/2014, ย้าย filter ต่างๆเข้ามาใน else ที่กรณีไม่มีค่าของ sSearch เพราะว่า หากมีการใช้ช่อง search ก็หมายความว่า ต้องการเซ็ตค่าของ filter ใหม่นั่นเอง
        
                endif;
                // END Add By Akkarapol, 29/11/2013, เพิ่มการ filter จากช่อง Search ด้วยการใช้ like
                //Product Exp
                if ($productExp != "") {
                    $this->db->where("vw_inbound.Product_Exp_sort", convertDate($productExp, "eng", "iso", "-"));
                }
                
               
        //        WHEN 'FIFO' THEN Receive_Date_sort
        //        WHEN 'FEFO' THEN Product_Exp_sort
                $this->db->limit($limit_max, $limit_start); // Add By Akkarapol, 28/11/2013, เพิ่ม $this->db->limit($limit_max, $limit_start); สำหรับทำการโหลดเพจแบบ หน้าต่อหน้า
                // echo $this->db->last_query();
                //this yah pre dispatch
                $query1 = $this->db->get();
//         p($query1); 
        // p('----------------------');
                // p($this->db->last_query()); 
                return $query1; // Add BY Akkarapol, 28/11/2013, เพื่อให้ function getDispatchProductDetails เป็นสากลมากขึ้น จึงทำให้ return ที่กลับมาเป็นแค่ db->get() เพื่อให้นำไปใช้ต่อยอดได้หลากหลายมากขึ้น
        //        $result1 = $query1->result();
        ////        =============================================================================================
        //        // select product in subStatus NOT IN value Return and Repack only
        //
        //        $this->db->select("*");
        //        $this->db->from("vw_inbound");
        //        $this->db->where("Product_Sub_Status_sort NOT IN ('SS001','SS002')");
        //
        //        //Product Code / SKU
        //        if ($productCode != "") {
        //            $this->db->like('Product_Code', $productCode);
        //        }
        //
        //        //Product Status
        //        if ($productStatus != "") {
        //            $this->db->where("Product_Status", $productStatus);
        //        }
        //
        //        //Product Sub Status
        //        if ($productSubStatus != "") {
        //            $this->db->where("Product_Sub_Status_sort", $productSubStatus);
        //        }
        //
        //        //Product Lot
        //        if ($productLot != "") {
        //            $this->db->like("Product_Lot", $productLot);
        //        }
        //
        //        //Product Serail
        //        if ($productSerial != "") {
        //            $this->db->like("Product_Serial", $productSerial);
        //        }
        //
        //        //Product Mfd
        //        if ($productMfd != "") {
        //            $this->db->where("Product_Mfd_sort", convertDate($productMfd, "eng", "iso", "-"));
        //        }
        //
        //        //Product Exp
        //        if ($productExp != "") {
        //            $this->db->where("Product_Exp_sort", convertDate($productExp, "eng", "iso", "-"));
        //        }
        //
        //        $this->db->order_by("(CASE PutAway_Rule
        //                                WHEN 'FIFO' THEN Receive_Date_sort
        //                                WHEN 'FEFO' THEN Product_Exp_sort
        //                                ELSE Actual_Location_Id
        //                                END)");
        //        $query2 = $this->db->get();
        //        $result2 = $query2->result();
        //          echo $this->db->last_query();
        //        $result = array_merge($result1, $result2);
        //
        //        $result = $result1;
        //
        //        return $result;
            }

    public function getProductDetailsByInboundId($inbound_id = null) {

        $this->db->select("*");
        $this->db->from("vw_inbound_productDetail");
        $this->db->where('Inbound_Item_Id', $inbound_id);
        $query = $this->db->get();
        return $query;
    }

    public function getvw_inb_ProductDetails($Product_Code, $Product_Lot, $Product_Serial, $Product_Mfd, $Product_Exp) {

        $this->db->select("*");
        $this->db->from("vw_inbound_productDetail");
        $this->db->where('Product_Code', $Product_Code);

        if ($Product_Lot == "") {
            $this->db->where('Product_Lot', NULL);
        } else {
            $this->db->where('Product_Lot', $Product_Lot);
        }

        if ($Product_Serial == "") {
            $this->db->where('Product_Serial', NULL);
        } else {
            $this->db->where('Product_Serial', $Product_Serial);
        }

        if ($Product_Mfd == "") {
            $this->db->where('Product_Mfd', NULL);
        } else {
            $this->db->where('Product_Mfd', $Product_Mfd);
        }

        if ($Product_Exp == "") {
            $this->db->where('Product_Exp', NULL);
        } else {
            $this->db->where('Product_Exp', $Product_Exp);
        }

        $query = $this->db->get();
//        p($this->db->last_query());
//        exit();
        return $query;
    }

    // Add By Akkarapol, 30/01/2014, เพิ่มฟังก์ชั่นสำหรับ หาค่า Est_Balance_Qty ของ product
    public function get_all_qty_of_product($product_code){
        $this->db->select("SUM(Est_Balance_Qty) as All_Balance_Qty");
        $this->db->from("vw_inbound");
        $this->db->where('Product_Code', $product_code);
        $query = $this->db->get();
        return $query;
    }
    // END Add By Akkarapol, 30/01/2014, เพิ่มฟังก์ชั่นสำหรับ หาค่า Est_Balance_Qty ของ product

    //ADD BY POR 2014-02-19 เพิ่ม function สำหรับหาข้อมูลในตาราง inbound ด้วย pellet_id
    public function get_inbound_by_palletID($palletID,$column=array()){
        if (empty($column)) {
            $this->db->select("*");
        } else {
            $this->db->select($column);
        }
        $this->db->from("STK_T_Inbound");
        $this->db->where("Pallet_Id",$palletID);
        $this->db->where("Active",ACTIVE);
        $query = $this->db->get();
        //p($this->db->last_query());
        return $query;
    }
    //END ADD


    /**
     * @function get inbound detail by filter $select and $where
     * @author Akkarapol : 20140516
     *
     * @param type $select
     * @param type $where
     * @return type
     */
    public function get_inbound_detail($select="*", $where="1=1"){
        $this->db->select($select);
        $this->db->from("STK_T_Inbound");
        $this->db->where($where);
        return $this->db->get();
    }

    public function getSubStatusByCode($status) {
        $this->db->select("Dom_Code AS Prod_Sub_Status");
        $this->db->where("Dom_Code", $status);
        $this->db->where("Dom_Host_Code", "SUB_STATUS");
        $this->db->where("Dom_Active", "Y");
        $query = $this->db->get("SYS_M_Domain");
        if ($query->num_rows()) :
            return $query->row()->Prod_Sub_Status;
        endif;
    }
    
    public function getStatusByCode($status) {
        $this->db->select("Dom_EN_Desc AS Prod_Status");
        $this->db->where("Dom_Code", $status);
        $this->db->where("Dom_Host_Code", "PROD_STATUS");
        $this->db->where("Dom_Active", "Y");
        $query = $this->db->get("SYS_M_Domain");
        if ($query->num_rows() > 0) :
            return $query->row()->Prod_Status;
        endif;
    }
    
     public function getfull_pallet($product_code){
         
//         select Unit_Per_Pallet
//from STK_M_Product
//where Product_Code 
        $this->db->select('Unit_Per_Pallet');
        $this->db->from("STK_M_Product");
        $this->db->where('Product_Code' ,$product_code);
        return $this->db->get();
    }

}

?>
