<?php

class report_customs_model extends CI_Model {
    protected $db_connection = NULL;

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
    }

    //query behide search import report : ADD BY POR 2014-10-28
    function search_import_report($search) {
        $dataRows = array();
//p($search);
        $this->db->select("inb.Doc_Refer_CE,inv.Invoice_No,convert(varchar(10),inb.Receive_Date,103) as Receive_Date
        ,inb.Product_Code,product.Product_NameEN,unit.name as unit,sum(inb.Receive_Qty) as Receive_Qty
        ,sum(inb.Price_Per_Unit*inb.Receive_Qty) as price,domain.Dom_EN_Desc as unit_price,inb.Vendor_Id,comp.Company_NameEN Vendor_Name, com_renter.Company_NameEN Renter_Name");
        $this->db->from("STK_T_Inbound inb");
        $this->db->join("STK_T_Invoice inv","inb.Invoice_Id = inv.Invoice_Id","LEFT");
        $this->db->join("STK_M_Product product","inb.Product_Id = product.Product_Id","LEFT");
        $this->db->join("CTL_M_UOM_Template_Language unit", "inb.Unit_Id = unit.CTL_M_UOM_Template_id AND unit.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->join("SYS_M_Domain domain", "inb.Unit_Price_Id = domain.Dom_Code","LEFT");
        $this->db->join("STK_T_Workflow w","inb.Flow_Id = w.Flow_Id","LEFT");
        $this->db->join("CTL_M_Company comp","inb.Vendor_Id = comp.Company_Id","LEFT");
        $this->db->join("CTL_M_Company com_renter","inb.Renter_Id = com_renter.Company_Id","LEFT");
        
        if (!empty($search['fdate'])) {
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") {
                $tdate = convertDate($search['tdate'], "eng", "iso", "-");
                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)>='".$from_date."'");
                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)<='".$tdate."'");
            } else {
                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)>='".$from_date."'");
            }
        }
        
        if(!empty($search['customs_entry'])):
            $this->db->like("inb.Doc_Refer_CE",$search['customs_entry']);
        endif;
        
        if(!empty($search['ior'])):
            $this->db->like("comp.Company_NameEN",$search['ior']);
        endif;
        
        if(!empty($search['invoice'])):
            $this->db->like("inv.Invoice_No",$search['invoice']);
        endif;

        $groupby = array("inb.Doc_Refer_CE","inv.Invoice_No","convert(varchar(10),inb.Receive_Date,103)"
        ,"inb.Product_Code","product.Product_NameEN","unit.name" ,"domain.Dom_EN_Desc","inb.Vendor_Id","comp.Company_NameEN"
        ,"com_renter.Company_NameEN"
        );
        $this->db->group_by($groupby);
        unset($groupby);

        $this->db->where_in('w.Process_Id', array(1));
        $this->db->where_in('w.Present_State', array(5,6,-2));
        $this->db->order_by("CONVERT(VARCHAR(10), inb.Receive_Date, 103)", "asc");
        $this->db->order_by("inv.Invoice_No", "asc");

        $query=$this->db->get();
 //       p($this->db->last_query());
        $result=$query->result();

        foreach ($result as $value):
            $doc_refer = md5($value->Doc_Refer_CE."#".$value->Vendor_Id);

            if (isset($doc_refer, $dataRows[$doc_refer])) {
                $keyTmp = sizeof($dataRows[$doc_refer]);
            } else {
                $keyTmp = 0;
            }
            $dataRow['Doc_Refer_CE'] = $value->Doc_Refer_CE;
            $dataRow['Invoice_No'] = $value->Invoice_No;
            $dataRow['Receive_Date'] = $value->Receive_Date;
            $dataRow['Product_Code'] = $value->Product_Code;
            $dataRow['Product_NameEN'] = $value->Product_NameEN;
            $dataRow['unit'] = $value->unit;
            $dataRow['Receive_Qty'] = $value->Receive_Qty;
            $dataRow['price'] = $value->price;
            $dataRow['unit_price'] = $value->unit_price;
            $dataRow['Vendor_Name'] = $value->Vendor_Name;
            $dataRow['Renter_Name'] = $value->Renter_Name;

            $dataRows[$doc_refer][$keyTmp] = $dataRow;

            unset($dataRow);
            unset($doc_refer);
        endforeach;
//p($dataRows);
        return $dataRows;
    }

    function search_export_report($search) {
        $dataRows = array();

        $this->db->select("ISNULL(CONVERT(VARCHAR(20), o.Real_Action_Date, 103),CONVERT(VARCHAR(20), o.Dispatch_Date, 103))  AS Estimate_Action_Date
            , o.Doc_Refer_CE, o.Product_Code
            , product.Product_NameEN
            , SUM(o.Dispatch_Qty) as Dispatch_Qty
            , STK_T_Invoice.Invoice_No
            , SUM(d.Price_Per_Unit * d.Confirm_Qty) as price
            , unit.name as unit
            , domain.Dom_EN_Desc as unit_price
            , od.Source_Id EOR_Id,comp.Company_NameEN EOR_Name
            , com_renter.Company_NameEN Renter_Name
        ");

        $this->db->join("STK_T_Order_Detail d", "d.Item_Id = o.Item_Id");
        $this->db->join("SYS_M_Domain domain", "d.Unit_Price_Id = domain.Dom_Code" ,"LEFT");
        $this->db->join("STK_T_Pallet plt", "d.Pallet_Id_Out = plt.Pallet_Id" ,"LEFT");
        $this->db->join("CTL_M_Container", "d.Cont_Id = CTL_M_Container.Cont_Id" ,"LEFT");
        $this->db->join("CTL_M_Container_Size", "CTL_M_Container.Cont_Size_Id = CTL_M_Container_Size.Cont_Size_Id" ,"LEFT");
        $this->db->join("STK_T_Invoice", "d.Invoice_Id = STK_T_Invoice.Invoice_Id" ,"LEFT");
        $this->db->join("STK_M_Product product","product.Product_Id=d.Product_Id","LEFT");
        $this->db->join("CTL_M_UOM_Template_Language unit", "d.Unit_Id = unit.CTL_M_UOM_Template_id AND unit.language = '" . $this->config->item('lang3digit') . "'");
        #เพิ่มให้แสดง EOR
        $this->db->join("STK_T_Order od", "d.Order_Id = od.Order_Id");
        $this->db->join("CTL_M_Company comp","od.Source_Id = comp.Company_Id","LEFT");
        $this->db->join("CTL_M_Company com_renter","od.Renter_Id = com_renter.Company_Id","LEFT");
        
        //$this->db->where("r.Process_Type", "OUTBOUND");
        $this->db->where("o.Dispatch_Date IS NOT NULL");
        $this->db->order_by("o.Dispatch_Date", "asc");
        $this->db->order_by("STK_T_Invoice.Invoice_No", "asc");

        $groupby = array("o.Doc_Refer_CE","STK_T_Invoice.Invoice_No","o.Dispatch_Date","o.Product_Code","product.Product_NameEN","unit.name" ,"domain.Dom_EN_Desc"
                    , "od.Source_Id","comp.Company_NameEN","com_renter.Company_NameEN","o.Real_Action_Date");

        $this->db->group_by($groupby);

        if ($search['fdate'] != "") :
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") :
                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
                $this->db->where("o.Dispatch_Date>='" . $from_date . "' ");
                $this->db->where("o.Dispatch_Date<='" . $to_date . " 23:59:59'");
            else :
                $this->db->where("o.Dispatch_Date>='" . $from_date . "'");
                $this->db->where("o.Dispatch_Date<='" . $from_date . " 23:59:59'");
            endif;
        endif;
        
        if(!empty($search['customs_entry'])):
            $this->db->like("o.Doc_Refer_CE",$search['customs_entry']);
        endif;
        
        if(!empty($search['eor'])):
            $this->db->like("comp.Company_NameEN",$search['eor']);
        endif;
        
        if(!empty($search['invoice'])):
            $this->db->like("STK_T_Invoice.Invoice_No",$search['invoice']);
        endif;

        #check for limit_max not null
        if(!empty($limit_max)):
            $this->db->limit($limit_max, $limit_start);
        endif;

        $query = $this->db->get("STK_T_Outbound o");
        //p($this->db->last_query()); exit();
        $result = $query->result();

        foreach ($result as $value):
            $doc_refer = md5($value->Doc_Refer_CE."#".$value->EOR_Id);

            if (isset($doc_refer, $dataRows[$doc_refer])) {
                $keyTmp = sizeof($dataRows[$doc_refer]);
            } else {
                $keyTmp = 0;
            }

            $dataRow['Doc_Refer_CE'] = $value->Doc_Refer_CE;
            $dataRow['Invoice_No'] = $value->Invoice_No;
            $dataRow['Dispatch_Date'] = $value->Estimate_Action_Date;
            $dataRow['Product_Code'] = $value->Product_Code;
            $dataRow['Product_NameEN'] = $value->Product_NameEN;
            $dataRow['unit'] = $value->unit;
            $dataRow['Dispatch_Qty'] = $value->Dispatch_Qty;
            $dataRow['price'] = $value->price;
            $dataRow['unit_price'] = $value->unit_price;
            $dataRow['EOR_Name'] = $value->EOR_Name;
            $dataRow['Renter_Name'] = $value->Renter_Name;
            
            $dataRows[$doc_refer][$keyTmp] = $dataRow;

            unset($dataRow);
            unset($doc_refer);
        endforeach;

        return $dataRows;
    }

    public function search_movement_report ($search) {
        $dataRows = array();
        $newDate = preg_split('/\//', $search['fdate']);
        $newFdate = date('d/m/Y', mktime(0, 0, 0, $newDate[1], $newDate[0] - 1, $newDate[2]));
        //$newFdate = convertDate($search['fdate'], "eng", "iso", "-");

        $this->db->select('TOP 1 CONVERT(VARCHAR(20), Available_Date, 103)');
        $this->db->from('STK_T_Onhand_History');
        $this->db->where('CONVERT(VARCHAR(20), Available_Date, 103) = ', $newFdate);
        $query_onhand = $this->db->get();
        $result_onhand = $query_onhand->result();

        if(empty($result_onhand)):
            $this->db->select('TOP 1 CONVERT(VARCHAR(20), Available_Date, 103) as start_onhand_date');
            $this->db->from('STK_T_Onhand_History');
            $this->db->order_by('Id','asc');
            $query_onhand_start = $this->db->get();
            $result_onhand_start = $query_onhand_start->row();

            if(empty($result_onhand_start)):
                $dataRows['no_onhand'] = 'No have onhand history';
            else:
                $dataRows['no_onhand'] = 'No have onhand history. Please select from date more : '.$result_onhand_start->start_onhand_date;
            endif;

            return $dataRows;
        endif;
        #end check fdate

        $tmp_search = $this->getSearchDateSql($search['fdate'], $search['tdate'] ,'STK_T_Order');

        if (isset($search['doc_value']) && $search['doc_value'] != "") {
            $tmp_search.=" AND " . $search['doc_type'] . " LIKE '%" . $this->db->escape_like_str(trim($search['doc_value'])) . "%' ";
        }

        $this->db->select('distinct(inbound.Product_Code)');
        $this->db->select('(SELECT Product_NameEN FROM STK_M_Product p WHERE p.Product_Id=inbound.Product_Id) As Product_NameEN');

        $this->db->select('(select sum(isnull(Balance_Qty,0))
                            from STK_T_Onhand_History
                            where CONVERT(VARCHAR(20), Available_Date, 103) =  "'.$newFdate.'"
                            and Product_Code = inbound.Product_Code
                            ) as incoming_qty');

        $this->db->select('(select sum(isnull(Confirm_Qty,0))
                            from stk_t_order_detail
                            where Order_Id  in(
                                select order_id from STK_T_Order where  Document_No in (select  Document_No from stk_t_workflow
                                where
                                  Process_Id = 1
                                   AND  (Present_State = -2 or Present_State >=5 )
                                   AND '.$tmp_search.'
                                   AND Renter_Id = '.$search['renter_id'].'
                                )
                            )
                            and active ="Y"
                            and Product_Code = inbound.Product_Code
                            ) as receive_qty');

        $this->db->select(' (select sum(isnull(Confirm_Qty,0))
                            from stk_t_order_detail
                            where Order_Id  in(
                                select order_id from STK_T_Order where  Document_No in (select  Document_No from stk_t_workflow
                                where
                                    Process_Id=2
                                     AND  Present_State = -2
                                     AND '.$tmp_search.'
                                     AND Renter_Id = '.$search['renter_id'].'
                                )
                            )
                            and active ="Y"
                            and Product_Code = inbound.Product_Code
                            )  as dispatch_qty');


        $this->db->from('STK_T_Inbound inbound');
        $this->db->where('inbound.Renter_Id', $search['renter_id']);
        $this->db->where('inbound.Flow_Id NOT IN (select Flow_Id from STK_T_Workflow where Present_State = -1)');
        if (isset($search['product_id']) && $search['product_id'] != "") {
            $this->db->where('inbound.Product_Id', $search['product_id']);
        }

        $query = $this->db->get();
        echo $this->db->last_query();
        $result = $query->result_array();
        return $result;
    }

    // add parameter "use_table" : by kik : 21-11-2013
    function getSearchDateSql($from_date = NULL, $to_date = NULL , $use_table = 'STK_T_Order') {

        $tmp_search = '';

        if ($from_date != NULL) {
            $from_date = convertDate($from_date, "eng", "iso", "-");
        }
        if ($to_date != NULL) {
            $to_date = convertDate($to_date, "eng", "iso", "-");
        }

        if ($from_date != NULL) {
            if ($to_date != NULL) {
                $tmp_search = $use_table.".Actual_Action_Date >= '" . $from_date . " 00:00:00'
                AND ".$use_table.".Actual_Action_Date <= '" . $to_date . " 23:59:59'";
            } else {
                $tmp_search = $use_table.".Actual_Action_Date>='" . $from_date . "'
                                AND ".$use_table.".Actual_Action_Date<='" . $from_date . " 23:59:59'";
            }
        } else {
            if ($to_date != NULL) {
                $tmp_search = $use_table.".Actual_Action_Date>='" . $to_date . "'
                                AND ".$use_table.".Actual_Action_Date<='" . $to_date . " 23:59:59'";
            }
        }
        return $tmp_search;
    }

    function searchProductMovementAllProduct_showItem($search) {

        $dataRows = array();
        /**
         * find date in STK_T_Onhand_History before query data , for not show qty negative
         * kik : 20140314
         */
        $newDate = preg_split('/\//', $search['fdate']);
        $newFdate = date('d/m/Y', mktime(0, 0, 0, $newDate[1], $newDate[0] - 1, $newDate[2]));

        $this->db->select('TOP 1 CONVERT(VARCHAR(20), Available_Date, 103)');
        $this->db->from('STK_T_Onhand_History');
        $this->db->where('CONVERT(VARCHAR(20), Available_Date, 103) = ', $newFdate);
        $query_onhand = $this->db->get();
        $result_onhand = $query_onhand->result();

        if(empty($result_onhand)):

            #check transaction in from date
            $newFdateForTransaction = date('d/m/Y', mktime(0, 0, 0, $newDate[1], $newDate[0] , $newDate[2]));

            $this->db->select('TOP 1 CONVERT(VARCHAR(20), Actual_Action_Date, 103) as trans_date');
            $this->db->from('STK_T_Order');
            $this->db->where('CONVERT(VARCHAR(20), Actual_Action_Date, 103) = ', $newFdateForTransaction);
            $query_order = $this->db->get();
            $result_order = $query_order->row();
//            p($result_order);

            # if not have transaction in from date check start onhand and alert
            if(empty($result_order)):

                #check onhand start date
                $this->db->select('TOP 1 CONVERT(VARCHAR(20), Available_Date, 103) as start_onhand_date
                    ,CONVERT(VARCHAR(20), (Available_Date+1), 103) as alert_onhand_date');
                $this->db->from('STK_T_Onhand_History');
                $this->db->order_by('Id','asc');
                $query_onhand_start = $this->db->get();
                $result_onhand_start = $query_onhand_start->row();

                if(empty($result_onhand_start)):

                    $dataRows['no_onhand'] = 'No have onhand history';

                else:

                    #check order start date
                    $this->db->select('TOP 1 CONVERT(VARCHAR(20), Actual_Action_Date, 103) as start_trans_date');
                    $this->db->from('STK_T_Order');
                    $this->db->where('Actual_Action_Date is not null');
                    $this->db->order_by('Actual_Action_Date','asc');
                    $query_order_start = $this->db->get();
                    $result_order_start = $query_order_start->row();
//                    p($result_onhand_start);
//                    p($result_order_start);

                    if(!empty($result_order_start)):

                        if($result_order_start->start_trans_date <= $result_onhand_start->start_onhand_date):

                            #มี transaction ใน order ก่อนรัน onhand
                            $dataRows['no_onhand'] = 'No have order transaction. Please select from date : '.$result_order_start->start_trans_date;
//                            echo '1';

                        else:


                            #มี onhand ก่อนมี transaction
                            $dataRows['no_onhand'] = 'No have onhand history. Please select from date : '.$result_onhand_start->alert_onhand_date;
//                            echo '2';

                        endif;

                    else:

                        #ไม่มี transaction และ ไม่มี onhand
                        $dataRows['no_onhand'] = 'No have onhand history. Please select from date : '.$result_onhand_start->alert_onhand_date;
//                        echo '3';

                    endif;

                endif;

                return $dataRows;

            endif;

        endif;
        #end check fdate

        $tmp_search = $this->getSearchDateSql($search['fdate'], $search['tdate'] ,'STK_T_Order');
        $this->db->select('STK_T_Order_Detail.Product_Id');
        $this->db->select('STK_T_Order_Detail.Product_Code');
        $this->db->select('CONVERT(VARCHAR(20), STK_T_Order.Actual_Action_Date, 103) AS Receive_Date');
        $this->db->select('CONVERT(VARCHAR(20), STK_T_Order.Actual_Action_Date, 120) AS Receive_Date_sort');
        $this->db->select('STK_T_Order.Document_No AS receive_doc_no');
        $this->db->select('STK_T_Order.Doc_Refer_Ext AS receive_refer_ext');
        $this->db->select('STK_T_Order_Detail.Confirm_Qty');
        $this->db->select('(SELECT Product_NameEN FROM STK_M_Product p WHERE p.Product_Id=STK_T_Order_Detail.Product_Id) As Product_NameEN');
        $this->db->select('STK_T_Order_Detail.Product_Serial');
        $this->db->select('STK_T_Order_Detail.Product_Lot');
        $this->db->select('CONVERT(VARCHAR(20), STK_T_Order_Detail.Product_Mfd, 103) AS Product_Mfd');
        $this->db->select('CONVERT(VARCHAR(20), STK_T_Order_Detail.Product_Exp, 103) AS Product_Exp');
        $this->db->select('STK_M_Location.Location_Code ');
        $this->db->select('(SELECT Company_Code FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Source_Id) AS s_branch');
        $this->db->select('(SELECT Company_Code FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Destination_Id) AS d_branch');
        $this->db->select('(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Source_Id) AS s_name');
        $this->db->select('(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Destination_Id) AS d_name');
        $this->db->select('STK_T_Order.Process_Type');
        $this->db->select('STK_T_Pallet.Pallet_Code');
        $this->db->select('STK_T_Workflow.Flow_Id');
        $this->db->select('STK_T_Order.Vendor_Id IOR_Id,comp_ior.Company_NameEN IOR_Name');
        $this->db->select('STK_T_Order.Source_Id EOR_Id,comp_eor.Company_NameEN EOR_Name');
        $this->db->select('STK_T_Order.Doc_Refer_CE');

        $this->db->from('STK_T_Order_Detail ');
        $this->db->join('STK_T_Order', 'STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id', 'LEFT');
        $this->db->join('STK_M_Location', 'STK_M_Location.Location_Id=STK_T_Order_Detail.Actual_Location_Id', 'LEFT');
        $this->db->join('STK_T_Workflow', 'STK_T_Order.Flow_Id = STK_T_Workflow.Flow_Id');
        $this->db->join('SYS_M_Process', 'STK_T_Workflow.Process_Id = SYS_M_Process.Process_Id');
        $this->db->join('STK_T_Pallet', 'STK_T_Order_Detail.Pallet_Id = STK_T_Pallet.Pallet_Id', 'LEFT');
        $this->db->join("CTL_M_Company comp_ior","STK_T_Order.Vendor_Id = comp_ior.Company_Id","LEFT");  //ior
        $this->db->join("CTL_M_Company comp_eor","STK_T_Order.Source_Id = comp_eor.Company_Id","LEFT");  //eor

        $this->db->where("(STK_T_Order.Process_Type='INBOUND' or STK_T_Order.Process_Type='OUTBOUND' or STK_T_Order.Process_Type = 'TRN-STOCK' or STK_T_Order.Process_Type = 'ADJ-STOCK')");
        $this->db->where('STK_T_Order.Renter_Id', $search['renter_id']);
        $this->db->where('STK_T_Order_Detail.Active', 'Y');
        $this->db->where('STK_T_Order_Detail.Activity_By  IS NOT NULL');
        $this->db->where("((STK_T_Workflow.Process_Id IN (1) AND STK_T_Workflow.Present_State in (-2,5,6))
                                 OR
                           (STK_T_Workflow.Process_Id IN (2) AND STK_T_Workflow.Present_State in (-2)))");

        if ($tmp_search != "") {
            $this->db->where($tmp_search);
        }

        if ($search['product_id'] != "") :
            $this->db->where("STK_T_Order_Detail.Product_Id", utf8_to_tis620($search['product_id']));
        endif;
        
        if(!empty($search['ior'])):
            $this->db->like("comp_ior.Company_NameEN",$search['ior']);
        endif;
        
        if(!empty($search['eor'])):
            $this->db->like("comp_eor.Company_NameEN",$search['eor']);
        endif;
        
        if(!empty($search['ce_in'])):
            $this->db->like("STK_T_Order.Doc_Refer_CE",$search['ce_in']);
            $this->db->where("STK_T_Order.Process_Type",'INBOUND');
        endif;

        if(!empty($search['ce_out'])):
            $this->db->like("STK_T_Order.Doc_Refer_CE",$search['ce_out']);
            $this->db->where("STK_T_Order.Process_Type",'OUTBOUND');
        endif;

        if($this->config->item('build_pallet')):
            $this->db->order_by('STK_T_Order_Detail.Product_Id,STK_T_Order.Actual_Action_Date,STK_T_Order_Detail.Pallet_Id ASC');
        else:
            $this->db->order_by('STK_T_Order_Detail.Product_Id,STK_T_Order.Actual_Action_Date ASC');
        endif;

        $query = $this->db->get();
        //p($this->db->last_query());// exit();
        $result = $query->result();
        //p($result);exit();
        $dataRows = array();
        $start_balance = array();
        $incom_balance = array();

        foreach ($result as $value) {
            
            //$idx_key = md5($value->Product_Code . $value->IOR_Id);
            $idx_key = md5($value->Product_Code);

            //echo $value->receive_doc_no . " " . $value->Product_Code . " " . $value->IOR_Id . "<br/>";
            
            if (isset($idx_key, $dataRows[$idx_key])) {

                $keyTmp = sizeof($dataRows[$idx_key]);
                $balance = $dataRows[$idx_key][$keyTmp - 1]['Balance_Qty'];
            } else {

                $keyTmp = 0;
                $incom_balance = $this->getProductBalanceStockMovement_byProduct($value->Product_Id, $newFdate);
                $start_balance[$value->Product_Code] = $incom_balance;
                $balance = $incom_balance;

            }
            
                # case INBOUND
                if (trim($value->Process_Type) == "INBOUND") {

                    $balance = $balance + $value->Confirm_Qty;
                    $dataRow['pay_doc_no'] = '';
                    $dataRow['receive_doc_no'] = $value->receive_doc_no;
                    $dataRow['receive_refer_ext'] = $value->receive_refer_ext;
                    $dataRow['pay_refer_ext'] = '';
                    $dataRow['r_qty'] = $value->Confirm_Qty;
                    $dataRow['p_qty'] = 0;
                    $dataRow['EOR_Name'] = ""; //EOR
                    $dataRow['CE_IN'] = $value->Doc_Refer_CE; //CE_Inbound
                    $dataRow['CE_OUT'] = ""; //CE_Outbound
                    $dataRow['receive_date_excel'] = $value->Receive_Date; 
                    $dataRow['dispatch_date_excel'] = ""; 
                }

                // add Process_Type ADJ-STOCK : by kik : 2013-11-21
                else if (trim($value->Process_Type) == "OUTBOUND" ) {

                    $balance = $balance - $value->Confirm_Qty;
                    $dataRow['pay_doc_no'] = $value->receive_doc_no;
                    $dataRow['receive_doc_no'] = '';
                    $dataRow['receive_refer_ext'] = '';
                    $dataRow['pay_refer_ext'] = $value->receive_refer_ext;
                    $dataRow['r_qty'] = 0;
                    $dataRow['p_qty'] = $value->Confirm_Qty;
                    $dataRow['EOR_Name'] = $value->EOR_Name; //EOR
                    $dataRow['CE_IN'] = ""; //CE_Inbound
                    $dataRow['CE_OUT'] = $value->Doc_Refer_CE; //CE_Outbound
                    $dataRow['receive_date_excel'] = ""; 
                    $dataRow['dispatch_date_excel'] = $value->Receive_Date;

                }

                $dataRow['receive_date'] = $value->Receive_Date;
                $dataRow['Product_Code'] = $value->Product_Code;
                $dataRow['Product_NameEN'] = $value->Product_NameEN;
                $dataRow['Product_SerLot'] = $value->Product_Serial . "/" . $value->Product_Lot;
                $dataRow['Product_Mfd'] = $value->Product_Mfd;
                $dataRow['Product_Exp'] = $value->Product_Exp;
                $dataRow['Balance_Qty'] = $balance;
                $dataRow['branch'] = $value->s_name . "/" . $value->d_name;
                $dataRow['process_type'] = $value->Process_Type;
                $dataRow['Location_Code'] = $value->Location_Code;
                $dataRow['start_balance'] = $start_balance[$value->Product_Code];
                $dataRow['Pallet_Code'] = $value->Pallet_Code;
                
                $dataRow['IOR_Name'] = $value->IOR_Name; //IOR
                

                $dataRows[$idx_key][$keyTmp] = $dataRow;

                unset($dataRow);

        }

        return $dataRows;
    }

    function getProductBalanceStockMovement_byProduct($productId = "", $Sdate = "") {


        $this->db->select('sum(Balance_Qty) as sumBalance_Qty');
        $this->db->where('Product_Id', $productId);

        $this->db->where('CONVERT(VARCHAR(20), Available_Date, 103) = ', $Sdate);

        $this->db->from('STK_T_Onhand_History');
        $this->db->group_by(array('Product_Id'));
        $query = $this->db->get();
        $result = $query->result();
//        p($result);exit();
//       p($this->db->last_query());
        if (!empty($result[0]->sumBalance_Qty)) {
            return $result[0]->sumBalance_Qty;
        } else {
            return 0;
        }
    }

    function searchProductMovementAllProduct($search) {

        $dataRows = array();
        /**
         * find date in STK_T_Onhand_History before query data , for not show qty negative
         * kik : 20140314
         */
        $newDate = preg_split('/\//', $search['fdate']);
        $newFdate = date('d/m/Y', mktime(0, 0, 0, $newDate[1], $newDate[0] - 1, $newDate[2]));

        $this->db->select('TOP 1 CONVERT(VARCHAR(20), Available_Date, 103)');
        $this->db->from('STK_T_Onhand_History');
        $this->db->where('CONVERT(VARCHAR(20), Available_Date, 103) = ', $newFdate);
        $query_onhand = $this->db->get();
        $result_onhand = $query_onhand->result();
//        echo $this->db->last_query();
//        p($result_onhand);
        
        if(empty($result_onhand)):

            #check transaction in from date
            $newFdateForTransaction = date('d/m/Y', mktime(0, 0, 0, $newDate[1], $newDate[0] , $newDate[2]));

            $this->db->select('TOP 1 CONVERT(VARCHAR(20), Actual_Action_Date, 103) as trans_date');
            $this->db->from('STK_T_Order');
            $this->db->where('CONVERT(VARCHAR(20), Actual_Action_Date, 103) = ', $newFdateForTransaction);
            $query_order = $this->db->get();
            $result_order = $query_order->row();
//            p($result_order);

            # if not have transaction in from date check start onhand and alert
            if(empty($result_order)):

                #check onhand start date
                $this->db->select('TOP 1 CONVERT(VARCHAR(20), Available_Date, 103) as start_onhand_date
                    ,CONVERT(VARCHAR(20), (Available_Date+1), 103) as alert_onhand_date');
                $this->db->from('STK_T_Onhand_History');
                $this->db->order_by('Id','asc');
                $query_onhand_start = $this->db->get();
                $result_onhand_start = $query_onhand_start->row();

                if(empty($result_onhand_start)):

                    $dataRows['no_onhand'] = 'No have onhand history';

                else:

                    #check order start date
                    $this->db->select('TOP 1 CONVERT(VARCHAR(20), Actual_Action_Date, 103) as start_trans_date');
                    $this->db->from('STK_T_Order');
                    $this->db->where('Actual_Action_Date is not null');
                    $this->db->order_by('Actual_Action_Date','asc');
                    $query_order_start = $this->db->get();
                    $result_order_start = $query_order_start->row();
//                    p($result_onhand_start);
//                    p($result_order_start);

                    if(!empty($result_order_start)):

                        if($result_order_start->start_trans_date <= $result_onhand_start->start_onhand_date):

                            #มี transaction ใน order ก่อนรัน onhand
                            $dataRows['no_onhand'] = 'No have order transaction. Please select from date : '.$result_order_start->start_trans_date;
//                            echo '1';

                        else:


                            #มี onhand ก่อนมี transaction
                            $dataRows['no_onhand'] = 'No have onhand history. Please select from date : '.$result_onhand_start->alert_onhand_date;
//                            echo '2';

                        endif;

                    else:

                        #ไม่มี transaction และ ไม่มี onhand
                        $dataRows['no_onhand'] = 'No have onhand history. Please select from date : '.$result_onhand_start->alert_onhand_date;
//                        echo '3';

                    endif;

                endif;

                return $dataRows;

            endif;

        endif;
        #end check fdate

        $tmp_search = $this->getSearchDateSql($search['fdate'], $search['tdate'] ,'STK_T_Order');
        $this->db->select('STK_T_Order_Detail.Product_Id');
        $this->db->select('STK_T_Order_Detail.Product_Code');
        $this->db->select('CONVERT(VARCHAR(20), STK_T_Order.Actual_Action_Date, 103) AS Receive_Date');
        $this->db->select('CONVERT(VARCHAR(20), STK_T_Order.Actual_Action_Date, 120) AS Receive_Date_sort');
        $this->db->select('STK_T_Order.Document_No AS receive_doc_no');
        $this->db->select('STK_T_Order.Doc_Refer_Ext AS receive_refer_ext');
        $this->db->select('STK_T_Order_Detail.Confirm_Qty');
        $this->db->select('(SELECT Product_NameEN FROM STK_M_Product p WHERE p.Product_Id=STK_T_Order_Detail.Product_Id) As Product_NameEN');
        $this->db->select('STK_T_Order_Detail.Product_Serial');
        $this->db->select('STK_T_Order_Detail.Product_Lot');
        $this->db->select('CONVERT(VARCHAR(20), STK_T_Order_Detail.Product_Mfd, 103) AS Product_Mfd');
        $this->db->select('CONVERT(VARCHAR(20), STK_T_Order_Detail.Product_Exp, 103) AS Product_Exp');
        $this->db->select('STK_M_Location.Location_Code ');
        $this->db->select('(SELECT Company_Code FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Source_Id) AS s_branch');
        $this->db->select('(SELECT Company_Code FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Destination_Id) AS d_branch');
        $this->db->select('(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Source_Id) AS s_name');
        $this->db->select('(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=Destination_Id) AS d_name');
        $this->db->select('STK_T_Order.Process_Type');

        $this->db->select('STK_T_Pallet.Pallet_Code');
        $this->db->select('STK_T_Workflow.Flow_Id');

        $this->db->from('STK_T_Order_Detail ');
        $this->db->join('STK_T_Order', 'STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id', 'LEFT');
        $this->db->join('STK_M_Location', 'STK_M_Location.Location_Id=STK_T_Order_Detail.Actual_Location_Id', 'LEFT');
        $this->db->join('STK_T_Workflow', 'STK_T_Order.Flow_Id = STK_T_Workflow.Flow_Id');
        $this->db->join('SYS_M_Process', 'STK_T_Workflow.Process_Id = SYS_M_Process.Process_Id');

        $this->db->join('STK_T_Pallet', 'STK_T_Order_Detail.Pallet_Id = STK_T_Pallet.Pallet_Id', 'LEFT');

        $this->db->where("(STK_T_Order.Process_Type='INBOUND' or STK_T_Order.Process_Type='OUTBOUND' or STK_T_Order.Process_Type = 'TRN-STOCK' or STK_T_Order.Process_Type = 'ADJ-STOCK')");
        $this->db->where('STK_T_Order.Renter_Id', $search['renter_id']);
        $this->db->where('STK_T_Order_Detail.Active', 'Y');
        $this->db->where('STK_T_Order_Detail.Activity_By  IS NOT NULL');
        $this->db->where("((STK_T_Workflow.Process_Id IN (1) AND STK_T_Workflow.Present_State in (-2,5,6))
                                 OR
                           (STK_T_Workflow.Process_Id IN (2,11,9) AND STK_T_Workflow.Present_State in (-2)))"); // add process_id 9  : by kik : 2013-11-21

        if ($tmp_search != "") {
            $this->db->where($tmp_search);
        }

        //ADD BY POR 2013-10-28 เพิ่มเงื่อนไขค้นหา document
        if ($search['doc_value'] != "") {
            $this->db->where("STK_T_Order." . $search['doc_type'] . " LIKE '%" . $this->db->escape_like_str(trim($search['doc_value'])) . "%' ");
        }
        //END ADD
        if($this->config->item('build_pallet')):
            $this->db->order_by('STK_T_Order_Detail.Product_Id,STK_T_Order_Detail.Pallet_Id,STK_T_Order.Actual_Action_Date ASC');
        else:
            $this->db->order_by('STK_T_Order_Detail.Product_Id,STK_T_Order.Actual_Action_Date ASC');
        endif;

        $query = $this->db->get();
//             p($this->db->last_query());// exit();
        $result_order = $query->result();
//p($result_order);

//        ====================================================================================

        $tmp_search = $this->getSearchDateSql($search['fdate'], $search['tdate'] ,'STK_T_Relocate');
        $this->db->select('STK_T_Relocate_Detail.Product_Id');
        $this->db->select('STK_T_Relocate_Detail.Product_Code');
        $this->db->select('CONVERT(VARCHAR(20), STK_T_Relocate.Actual_Action_Date, 103) AS Receive_Date');
        $this->db->select('CONVERT(VARCHAR(20), STK_T_Relocate.Actual_Action_Date, 120) AS Receive_Date_sort');
        $this->db->select('STK_T_Relocate.Doc_Relocate AS receive_doc_no');
        $this->db->select('STK_T_Relocate_Detail.Doc_Refer_Ext AS receive_refer_ext');
        $this->db->select('STK_T_Relocate_Detail.Confirm_Qty');
        $this->db->select('STK_T_Relocate_Detail.Reserv_Qty');
        $this->db->select('(SELECT Product_NameEN FROM STK_M_Product p WHERE p.Product_Id=STK_T_Relocate_Detail.Product_Id) As Product_NameEN');
        $this->db->select('STK_T_Relocate_Detail.Product_Serial');
        $this->db->select('STK_T_Relocate_Detail.Product_Lot');
        $this->db->select('CONVERT(VARCHAR(20), STK_T_Relocate_Detail.Product_Mfd, 103) AS Product_Mfd');
        $this->db->select('CONVERT(VARCHAR(20), STK_T_Relocate_Detail.Product_Exp, 103) AS Product_Exp');
        $this->db->select('STK_M_Location.Location_Code ');
        $this->db->select('STK_T_Relocate.Owner_Id ');
        $this->db->select('(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=STK_T_Relocate.Owner_Id) s_name');
        $this->db->select('(SELECT Location_Code FROM STK_M_Location WHERE STK_M_Location.Location_Id=Old_Location_Id) Old_Location_Code');
        $this->db->select('STK_T_Relocate.Process_Type');

        $this->db->select('STK_T_Pallet.Pallet_Code');
        $this->db->select('STK_T_Workflow.Flow_Id');

        $this->db->from('STK_T_Relocate_Detail ');
        $this->db->join('STK_T_Relocate', 'STK_T_Relocate_Detail.Order_Id = STK_T_Relocate.Order_Id', 'LEFT');
        $this->db->join('STK_M_Location', 'STK_M_Location.Location_Id=STK_T_Relocate_Detail.Actual_Location_Id', 'LEFT');
        $this->db->join('STK_T_Workflow', 'STK_T_Relocate.Flow_Id = STK_T_Workflow.Flow_Id');
        $this->db->join('SYS_M_Process', 'STK_T_Workflow.Process_Id = SYS_M_Process.Process_Id');

        $this->db->join('STK_T_Pallet', 'STK_T_Relocate_Detail.Pallet_Id = STK_T_Pallet.Pallet_Id', 'LEFT');

        $this->db->where("(STK_T_Relocate.Process_Type='CH-STATUS' or STK_T_Relocate.Process_Type='RE-LOCATE1' or STK_T_Relocate.Process_Type='RE-LOCATE2' or STK_T_Relocate.Process_Type='RE-LOCATE3' or STK_T_Relocate.Process_Type='RE-LOCATE4')");
        $this->db->where('STK_T_Relocate.Renter_Id', $search['renter_id']);
//        $this->db->where("((STK_T_Workflow.Process_Id IN (8) AND STK_T_Workflow.Present_State in (3,4,-2))
//                            OR
//                          (STK_T_Workflow.Process_Id IN (10,6,5) AND STK_T_Workflow.Present_State in (-2)))  ");

        $this->db->where("(STK_T_Workflow.Process_Id IN (14,10,6,5) AND STK_T_Workflow.Present_State in (-2))  ");

        if ($tmp_search != "") {
            $this->db->where($tmp_search);
        }

        //ADD BY POR 2013-10-28 เพิ่มเงื่อนไขค้นหา document
        if ($search['doc_value'] != "") {
            $this->db->where("STK_T_Relocate.Doc_Relocate LIKE '%" . $this->db->escape_like_str(trim($search['doc_value'])) . "%' ");
        }
        //END ADD

        if($this->config->item('build_pallet')):
            $this->db->order_by('STK_T_Relocate_Detail.Pallet_Id');
        endif;

        $this->db->order_by('STK_T_Relocate_Detail.Product_Id,STK_T_Relocate.Actual_Action_Date ASC');

        $query = $this->db->get();
//             p($this->db->last_query()); //exit();
        $result_relocate = $query->result();

//        p($result_relocate);
//        ====================================================================================
        //$result = array_merge($result_order, $result_relocate);

        $result_tmp = array_merge($result_order, $result_relocate);
        $result = sort_arr_of_obj($result_tmp,'Receive_Date_sort','asc');
        //p($result);

        $dataRow = array();
        $start_balance = array();
//        p($result);
        foreach ($result as $value) {

            $SerLotMfdExpTmp = $value->Product_Serial . $value->Product_Lot . $value->Product_Mfd . $value->Product_Exp;

            if (isset($SerLotMfdExpTmp, $dataRows[$value->Product_Code][$SerLotMfdExpTmp])) {
                $keyTmp = sizeof($dataRows[$value->Product_Code][$SerLotMfdExpTmp]);
                $balance = $dataRows[$value->Product_Code][$SerLotMfdExpTmp][$keyTmp - 1]['Balance_Qty'];
            } else {
                $keyTmp = 0;

                $balance = $this->getProductBalanceStockMovement($value->Product_Id, $newFdate, $value->Product_Lot, $value->Product_Serial, $value->Product_Mfd, $value->Product_Exp);
                $start_balance[$value->Product_Code.$SerLotMfdExpTmp] = $balance;  // add by kik : 2013-11-25

            }

//            if(trim($value->Process_Type) == "CH-STATUS" ||
//                    trim($value->Process_Type) == "RE-LOCATE1" ||
//                    trim($value->Process_Type) == "RE-LOCATE2" ||
//                    trim($value->Process_Type) == "RE-LOCATE3"):
            if(
                    trim($value->Process_Type) == "RE-LOCATE1" ||
                    trim($value->Process_Type) == "RE-LOCATE2" ||
                    trim($value->Process_Type) == "RE-LOCATE3"):

                    #จ่ายออก
                    #----------------------------------------
                    $balance = $balance - $value->Reserv_Qty;
                    $dataRow['pay_doc_no'] = $value->receive_doc_no;
                    $dataRow['receive_doc_no'] = '';
                    $dataRow['receive_refer_ext'] = '';
                    $dataRow['pay_refer_ext'] = $value->receive_refer_ext;
                    $dataRow['r_qty'] = 0;
                    $dataRow['p_qty'] = $value->Reserv_Qty;

                    $dataRow['receive_date'] = $value->Receive_Date;
                    $dataRow['Product_NameEN'] = $value->Product_NameEN;
                    $dataRow['Product_SerLot'] = $value->Product_Serial . "/" . $value->Product_Lot;
                    $dataRow['Product_Mfd'] = $value->Product_Mfd;
                    $dataRow['Product_Exp'] = $value->Product_Exp;
                    $dataRow['Balance_Qty'] = $balance;
                    $dataRow['branch'] = $value->s_name;
                    $dataRow['process_type'] = $value->Process_Type;

                    if(trim($value->Process_Type) == "RE-LOCATE1" || trim($value->Process_Type) == "RE-LOCATE2" || trim($value->Process_Type) == "RE-LOCATE3" || trim($value->Process_Type) == "RE-LOCATE4"):
                        $dataRow['Location_Code'] = $value->Old_Location_Code   ;
                    else:
                        $dataRow['Location_Code'] = $value->Location_Code;
                    endif;

                    $dataRow['start_balance'] = $start_balance[$value->Product_Code.$SerLotMfdExpTmp];             // add by kik : 2013-11-25

                    $dataRow['Pallet_Code'] = $value->Pallet_Code;

                    $dataRows[$value->Product_Code][$SerLotMfdExpTmp][$keyTmp] = $dataRow;

                    unset($dataRow);
                    $keyTmp = sizeof($dataRows[$value->Product_Code][$SerLotMfdExpTmp]);

                    #รับเข้าใหม่
                    #----------------------------------------
                    $balance = $balance + $value->Reserv_Qty;
                    $dataRow['pay_doc_no'] = '';
                    $dataRow['receive_doc_no'] = $value->receive_doc_no;
                    $dataRow['receive_refer_ext'] = $value->receive_refer_ext;
                    $dataRow['pay_refer_ext'] = '';
                    $dataRow['r_qty'] = $value->Reserv_Qty;
                    $dataRow['p_qty'] = 0;

                    $dataRow['receive_date'] = $value->Receive_Date;
                    $dataRow['Product_NameEN'] = $value->Product_NameEN;
                    $dataRow['Product_SerLot'] = $value->Product_Serial . "/" . $value->Product_Lot;
                    $dataRow['Product_Mfd'] = $value->Product_Mfd;
                    $dataRow['Product_Exp'] = $value->Product_Exp;
                    $dataRow['Balance_Qty'] = $balance;
                    $dataRow['branch'] = $value->s_name;
                    $dataRow['process_type'] = $value->Process_Type;
                    $dataRow['Location_Code'] = $value->Location_Code;
                    $dataRow['start_balance'] = $start_balance[$value->Product_Code.$SerLotMfdExpTmp];             // add by kik : 2013-11-25

                    $dataRow['Pallet_Code'] = $value->Pallet_Code;

                    $dataRows[$value->Product_Code][$SerLotMfdExpTmp][$keyTmp] = $dataRow;

                    unset($dataRow);
                    unset($SerLotMfdExpTmp);

            else:
                # case INBOUND
                if (trim($value->Process_Type) == "INBOUND") {

                    $balance = $balance + $value->Confirm_Qty;
                    $dataRow['pay_doc_no'] = '';
                    $dataRow['receive_doc_no'] = $value->receive_doc_no;
                    $dataRow['receive_refer_ext'] = $value->receive_refer_ext;
                    $dataRow['pay_refer_ext'] = '';
                    $dataRow['r_qty'] = $value->Confirm_Qty;
                    $dataRow['p_qty'] = 0;
                }

                // add Process_Type ADJ-STOCK : by kik : 2013-11-21
                else if (trim($value->Process_Type) == "OUTBOUND" ||
                        trim($value->Process_Type) == "ADJ-STOCK" ||
                        trim($value->Process_Type) == "TRN-STOCK"  ) {

                    $balance = $balance - $value->Confirm_Qty;
                    $dataRow['pay_doc_no'] = $value->receive_doc_no;
                    $dataRow['receive_doc_no'] = '';
                    $dataRow['receive_refer_ext'] = '';
                    $dataRow['pay_refer_ext'] = $value->receive_refer_ext;
                    $dataRow['r_qty'] = 0;
                    $dataRow['p_qty'] = $value->Confirm_Qty;

                }

                $dataRow['receive_date'] = $value->Receive_Date;
                $dataRow['Product_NameEN'] = $value->Product_NameEN;
                $dataRow['Product_SerLot'] = $value->Product_Serial . "/" . $value->Product_Lot;
                $dataRow['Product_Mfd'] = $value->Product_Mfd;
                $dataRow['Product_Exp'] = $value->Product_Exp;
                $dataRow['Balance_Qty'] = $balance;
                $dataRow['branch'] = $value->s_name . "/" . $value->d_name;
                $dataRow['process_type'] = $value->Process_Type;
                $dataRow['Location_Code'] = $value->Location_Code;
                $dataRow['start_balance'] = $start_balance[$value->Product_Code.$SerLotMfdExpTmp];             // add by kik : 2013-11-25

                $dataRow['Pallet_Code'] = $value->Pallet_Code;

                $dataRows[$value->Product_Code][$SerLotMfdExpTmp][$keyTmp] = $dataRow;

                //if($value->Product_Code == '03140237' && $value->receive_doc_no == 'DDR20140324-00023' && $value->Confirm_Qty = '3000'){
                //echo $start_balance[$value->Product_Code.$SerLotMfdExpTmp];
                //  p( $dataRow);
                //}
                unset($dataRow);
                unset($SerLotMfdExpTmp);

            endif;

        }

//      p($dataRows);//exit();
        return $dataRows;
    }

    function getProductBalanceStockMovement($productId = "", $Sdate = "", $productLot = "", $productSel = "", $productMfd = NULL, $productExp = NULL) {


        $this->db->select('sum(Balance_Qty) as sumBalance_Qty');
        $this->db->where('Product_Id', $productId);

        //START BY POR 2013-10-24 แก้ไขจาก = NULL เป็น IS NULL
        if ($productLot == NULL) {
            $this->db->where('Product_Lot IS NULL');
        } else {
            $this->db->where('Product_Lot', $productLot);
        }

        if ($productSel == NULL) {
            $this->db->where('Product_Serial IS NULL');
        } else {
            $this->db->where('Product_Serial', $productSel);
        }
        //END

        $this->db->where('CONVERT(VARCHAR(20), Available_Date, 103) = ', $Sdate);

        if ($productMfd == NULL) {
            $this->db->where('CONVERT(VARCHAR(20), Product_Mfd, 103) IS NULL'); //BY POR 2013-10-24 แก้ไขจาก = NULL เป็น IS NULL
        } else {
            $this->db->where('CONVERT(VARCHAR(20), Product_Mfd, 103) = ', $productMfd);
        }
        if ($productExp == NULL) {
            $this->db->where('CONVERT(VARCHAR(20), Product_Exp, 103) IS NULL'); //BY POR 2013-10-24 แก้ไขจาก = NULL เป็น IS NULL
        } else {
            $this->db->where('CONVERT(VARCHAR(20), Product_Exp, 103) = ', $productExp);
        }

        $this->db->from('STK_T_Onhand_History');
        $this->db->group_by(array('Product_Id', 'Product_Lot', 'Product_Serial', 'CONVERT(VARCHAR(20), Available_Date, 103)', 'Product_Mfd', 'Product_Exp'));
        $query = $this->db->get();
        $result = $query->result();
        if (!empty($result[0]->sumBalance_Qty)) {
            return $result[0]->sumBalance_Qty;
        } else {
            return 0;
        }
    }



    //query behide search remain report : ADD BY POR 2014-10-28
    function search_remain_report($search) {
         $dataRows = array();

         //ตรวจสอบว่าเป็นวันที่ปัจจุบันหรือไม่ ถ้าใช่ให้ดึงข้อมูลใน inbound ถ้าไม่ใช่ให้ดึงข้อมูลจาก onhand
        if ($search['fdate'] == date("d/m/Y")) {
            $this->db->select("Doc_Refer_CE
            ,inb.Product_Code
            ,sku.Product_NameEN
            ,sum(Balance_Qty) as Balance_Qty
            ,uom.name as unit
            ,inb.Vendor_Id
            ,comp.Company_NameEN Vendor_Name
            ,sum(inb.Price_Per_Unit*inb.Receive_Qty) as price
            ,domain.Dom_EN_Desc as unit_price");
            $this->db->from("STK_T_Inbound inb");
            $this->db->join("STK_M_Product sku","inb.Product_Id = sku.Product_Id","LEFT");
            $this->db->join("CTL_M_UOM_Template_Language uom", "inb.Unit_Id = uom.CTL_M_UOM_Template_id AND uom.language = '" . $this->config->item('lang3digit') . "'");
            $this->db->join("CTL_M_Company comp","inb.Vendor_Id = comp.Company_Id","LEFT");
            $this->db->join("SYS_M_Domain domain", "inb.Unit_Price_Id = domain.Dom_Code","LEFT");
            $this->db->where("inb.Active",'Y');
            
            if(!empty($search['customs_entry'])):
                $this->db->like("inb.Doc_Refer_CE",$search['customs_entry']);
            endif;

            if(!empty($search['ior'])):
                $this->db->like("comp.Company_NameEN",$search['ior']);
            endif;
            
            $groupby = array("Doc_Refer_CE","inb.Product_Code","sku.Product_NameEN","uom.name","inb.Vendor_Id","comp.Company_NameEN","domain.Dom_EN_Desc");
            $this->db->group_by($groupby);
            unset($groupby);

        } else {
            //แปลง date ให้อยู่ในรูป yyyy-mm-dd
            if (array_key_exists('fdate', $search)) :
                $compare_date = convertDate($search['fdate'], "eng", "iso", "-");
            endif;

            $this->db->select("Doc_Refer_CE,onhand.Product_Code, sku.Product_NameEN, sum(onhand.Balance_Qty) as Balance_Qty, uom.name as unit,inb.Vendor_Id,comp.Company_NameEN Vendor_Name,sum(inb.Price_Per_Unit*inb.Receive_Qty) as price,domain.Dom_EN_Desc as unit_price");
            $this->db->from("STK_T_Onhand_History onhand");
            $this->db->join("STK_T_Inbound inb","onhand.Inbound_Id = inb.Inbound_Id","LEFT");
            $this->db->join("STK_M_Product sku","inb.Product_Id = sku.Product_Id","LEFT");
            $this->db->join("CTL_M_UOM_Template_Language uom", "sku.Standard_Unit_Id = uom.CTL_M_UOM_Template_id AND uom.language = '" . $this->config->item('lang3digit') . "'");
            $this->db->join("CTL_M_Company comp","inb.Vendor_Id = comp.Company_Id","LEFT");
            $this->db->join("SYS_M_Domain domain", "inb.Unit_Price_Id = domain.Dom_Code","LEFT");
            $this->db->where("convert(varchar(10),Available_Date,120)",$compare_date);
            
            if(!empty($search['customs_entry'])):
                $this->db->like("inb.Doc_Refer_CE",$search['customs_entry']);
            endif;

            if(!empty($search['ior'])):
                $this->db->like("comp.Company_NameEN",$search['ior']);
            endif;

            $groupby = array("Doc_Refer_CE","onhand.Product_Code", "sku.Product_NameEN", "uom.name","inb.Vendor_Id","comp.Company_NameEN","domain.Dom_EN_Desc");
            $this->db->group_by($groupby);
            unset($groupby);
        }

        $query=$this->db->get();
        //p($this->db->last_query());
        $result=$query->result();

        foreach ($result as $value):
            $doc_refer = md5($value->Doc_Refer_CE."#".$value->Vendor_Id);

            if (isset($doc_refer, $dataRows[$doc_refer])) {
                $keyTmp = sizeof($dataRows[$doc_refer]);
            } else {
                $keyTmp = 0;
            }
            $dataRow['Doc_Refer_CE'] = $value->Doc_Refer_CE;
            $dataRow['Product_Code'] = $value->Product_Code;
            $dataRow['Product_NameEN'] = $value->Product_NameEN;
            $dataRow['unit'] = $value->unit;
            $dataRow['Balance_Qty'] = $value->Balance_Qty;
            $dataRow['Vendor_Name'] = $value->Vendor_Name;
            $dataRow['Price'] = $value->price;
            $dataRow['unit_price'] = $value->unit_price;
            
            $dataRows[$doc_refer][$keyTmp] = $dataRow;

            unset($dataRow);
            unset($doc_refer);
        endforeach;

        return $dataRows;
    }

    function search_borrow_report($search,$type = "") {
         $dataRows = array();
         
         //ดึงข้อมูลตั้งต้นและ detail 
         $this->db->select("CONVERT(varchar(10), rel.Actual_Action_Date, 103) out_date, rel.Custom_Doc_Ref, reld.Product_Code, pro.Product_NameEN as Product_Name
        , SUM(reld.Confirm_Qty) as Confirm_Qty, SUM(reld.Price_Per_Unit * reld.Confirm_Qty) as all_price, CASE WHEN rel.Remark IS NULL OR rel.Remark = '' THEN '' ELSE rel.Remark END AS Remark
        , inb.Inbound_Id,CONVERT(varchar(10), inb_his.importdate, 103) importdate,inb_his.Receive_Qty,inb_his.All_Price
        , sum(inb.Receive_Qty - (inb.PD_Reserv_Qty + inb.Dispatch_Qty + inb.Adjust_Qty)) as remain_qty
        , DATEDIFF(day,rel.Actual_Action_Date,ISNULL(inb_his.importdate, GETDATE() ) ) date_diff,inb_his.Inbound_Id Inbound_Id_His");
        $this->db->from("STK_T_Relocate rel");
        $this->db->join("STK_T_Relocate_Detail reld","rel.Order_Id = reld.Order_Id","LEFT");
        $this->db->join("STK_M_Product pro","reld.Product_Id = pro.Product_Id","LEFT");
        $this->db->join("STK_T_Workflow flow", "rel.Flow_Id = flow.Flow_Id","LEFT");
        $this->db->join("STK_T_Inbound inb", "reld.Inbound_Item_Id = inb.History_Item_Id AND reld.Item_Id = inb.Item_Id","LEFT");
        $this->db->join("(select inb_his.Inbound_Id,rel.Actual_Action_Date as importdate
        ,sum(inb_his.Receive_Qty) Receive_Qty,sum(inb_his.Receive_Qty * inb_his.Price_Per_Unit) as All_Price,History_Item_Id
        from STK_T_Inbound inb_his
        left join STK_T_Relocate_Detail rel_detail on inb_his.History_Item_Id = rel_detail.Inbound_Item_Id and rel_detail.Product_Status = 'NORMAL' AND inb_his.Item_Id=rel_detail.Item_Id
        left join STK_T_Relocate rel on rel_detail.Order_Id = rel.Order_Id
        left join STK_T_Workflow flow on rel.Flow_Id = flow.Flow_Id 
        where 
        inb_his.Product_Status = 'NORMAL'  
        and Process_Id = 8 and Present_State = -2
        group by inb_his.Inbound_Id,rel.Actual_Action_Date,History_Item_Id) inb_his","inb.Inbound_Id = inb_his.History_Item_Id","LEFT");
        
        if (!empty($search['fdate'])) {
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") {
                $tdate = convertDate($search['tdate'], "eng", "iso", "-");
                $this->db->where("CONVERT(varchar(10),rel.Actual_Action_Date,120)>='".$from_date."'");
                $this->db->where("CONVERT(varchar(10),rel.Actual_Action_Date,120)<='".$tdate."'");
            } else {
                $this->db->where("CONVERT(varchar(10),rel.Actual_Action_Date,120)>='".$from_date."'");
            }
        }
        
        if(!empty($search['custom_doc_ref'])):
            $this->db->like("rel.Custom_Doc_Ref",$search['custom_doc_ref']);
        endif;

        if(!empty($search['ior'])):
            $this->db->like("comp.Company_NameEN",$search['ior']);
        endif;

        $this->db->where("reld.Product_Status",'BORROW');
        $this->db->where("Process_Id",8);
        $this->db->where("Present_State",-2);

        $groupby = array("rel.Actual_Action_Date", "rel.Custom_Doc_Ref", "reld.Product_Code", "pro.Product_NameEN","rel.Remark","inb.Inbound_Id","inb_his.importdate","inb_his.Receive_Qty","inb_his.All_Price","inb_his.Inbound_Id");
        $this->db->group_by($groupby);
        $this->db->order_by("rel.Actual_Action_Date,reld.Product_Code", "asc");

        $query=$this->db->get();
        // p($this->db->last_query()); exit();
        $result=$query->result_array();
        
        if($type == 'EXCEL'):
            $data = array();
            $index = 0;
            foreach ($result as $key => $value):
                if(array_key_exists ($value['Inbound_Id'], $data)):
                    #กรณีที่เอามาคืนแล้วจะอยู่ในส่วนนี้       
                    $index +=1;
                    $remain = $remain - $value['Receive_Qty'];
                    #กรณีเป็น group เดียวกันในส่วน borrow ไม่ต้องแสดงค่าซ้ำ
                    $data[$value['Inbound_Id']][$index]['out_date'] = ""; //วันที่ยืม
                    $data[$value['Inbound_Id']][$index]['import_date'] = $value['importdate']; //วันที่คืน
                    $data[$value['Inbound_Id']][$index]['date_diff'] = $value['date_diff']; //จำนวนวันที่ยืมไป
                    $data[$value['Inbound_Id']][$index]['Custom_Doc_Ref'] = $value['Custom_Doc_Ref'];
                    $data[$value['Inbound_Id']][$index]['Product_Code'] = $value['Product_Code'];
                    $data[$value['Inbound_Id']][$index]['Product_Name'] = $value['Product_Name'];
                    $data[$value['Inbound_Id']][$index]['Confirm_Qty'] = "";  //จำนวนที่ยืม
                    $data[$value['Inbound_Id']][$index]['import_Receive_Qty'] = $value['Receive_Qty']; //จำนวนที่คืน
                    $data[$value['Inbound_Id']][$index]['remain_qty'] = $remain; //จำนวนคงเหลือ
                    $data[$value['Inbound_Id']][$index]['all_price'] = ""; //ราคารวมของจำนวนที่ยืม
                    $data[$value['Inbound_Id']][$index]['import_All_Price'] = $value['All_Price'];  //ราคารวมของจำนวนที่คืน
                    $data[$value['Inbound_Id']][$index]['Remark'] = ""; //เหตุผลที่ยืม
                else:
                    #ค่าตั้งต้น หรือค่าที่ยืมไป
                    $index = 0;         
                    $remain = $value['Confirm_Qty'];

                    $data[$value['Inbound_Id']][$index]['out_date'] = $value['out_date'];  //วันที่ยืม
                    $data[$value['Inbound_Id']][$index]['import_date'] = "";  //วันที่คืน
                    $data[$value['Inbound_Id']][$index]['date_diff'] = ""; //จำนวนวันที่ยืมไป
                    $data[$value['Inbound_Id']][$index]['Custom_Doc_Ref'] = $value['Custom_Doc_Ref'];
                    $data[$value['Inbound_Id']][$index]['Product_Code'] = $value['Product_Code'];
                    $data[$value['Inbound_Id']][$index]['Product_Name'] = $value['Product_Name'];
                    $data[$value['Inbound_Id']][$index]['Confirm_Qty'] = $value['Confirm_Qty']; //จำนวนที่ยืม
                    $data[$value['Inbound_Id']][$index]['import_Receive_Qty'] = ""; //จำนวนที่คืน
                    $data[$value['Inbound_Id']][$index]['remain_qty'] = $remain; //กำหนดให้เป็นค่าตั้งต้นเพราะยังไม่มีการคืน
                    $data[$value['Inbound_Id']][$index]['all_price'] = $value['all_price']; //ราคารวมของจำนวนที่ยืม
                    $data[$value['Inbound_Id']][$index]['import_All_Price'] = ""; //ราคารวมของจำนวนที่คืน
                    $data[$value['Inbound_Id']][$index]['Remark'] = $value['Remark']; //เหตุผลที่ยืม
                    
                    #กรณีที่มีการคืน qty ต้องมากกว่า 0 
                    if($value['Receive_Qty'] > 0):
                        $index +=1;
                        $remain = $remain - $value['Receive_Qty'];

                        $data[$value['Inbound_Id']][$index]['out_date'] = ""; //วันที่ยืม
                        $data[$value['Inbound_Id']][$index]['import_date'] = $value['importdate']; //วันที่คืน
                        $data[$value['Inbound_Id']][$index]['date_diff'] = $value['date_diff']; //จำนวนวันที่ยืมไป
                        $data[$value['Inbound_Id']][$index]['Custom_Doc_Ref'] = $value['Custom_Doc_Ref'];
                        $data[$value['Inbound_Id']][$index]['Product_Code'] = $value['Product_Code'];
                        $data[$value['Inbound_Id']][$index]['Product_Name'] = $value['Product_Name'];
                        $data[$value['Inbound_Id']][$index]['Confirm_Qty'] = "";  //จำนวนที่ยืม
                        $data[$value['Inbound_Id']][$index]['import_Receive_Qty'] = $value['Receive_Qty']; //จำนวนที่คืน
                        $data[$value['Inbound_Id']][$index]['remain_qty'] = $remain; //จำนวนคงเหลือ
                        $data[$value['Inbound_Id']][$index]['all_price'] = ""; //ราคารวมของจำนวนที่ยืม
                        $data[$value['Inbound_Id']][$index]['import_All_Price'] = $value['All_Price'];  //ราคารวมของจำนวนที่คืน
                        $data[$value['Inbound_Id']][$index]['Remark'] = ""; //เหตุผลที่ยืม
                    endif;
                endif;    
            endforeach;
        else:
            //กรณีไม่ใช่ excel
            #จัดค่าใน array ก่อนนำแสดงหน้า view
            $data = array();
            foreach ($result as $key => $value):
                if(array_key_exists ($value['Inbound_Id'], $data)):
                    #กรณีเป็น group เดียวกันในส่วน borrow ไม่ต้องแสดงค่าซ้ำ
                    $data[$value['Inbound_Id']][$key]['out_date'] = "";
                    $data[$value['Inbound_Id']][$key]['Custom_Doc_Ref'] = "";
                    $data[$value['Inbound_Id']][$key]['Product_Code'] = "";
                    $data[$value['Inbound_Id']][$key]['Product_Name'] = "";
                    $data[$value['Inbound_Id']][$key]['Confirm_Qty'] = "";
                    $data[$value['Inbound_Id']][$key]['all_price'] = "";
                    $data[$value['Inbound_Id']][$key]['remain_qty'] = "";
                    $data[$value['Inbound_Id']][$key]['Remark'] = "";
                else:
                    $data[$value['Inbound_Id']][$key]['out_date'] = $value['out_date'];
                    $data[$value['Inbound_Id']][$key]['Custom_Doc_Ref'] = $value['Custom_Doc_Ref'];
                    $data[$value['Inbound_Id']][$key]['Product_Code'] = $value['Product_Code'];
                    $data[$value['Inbound_Id']][$key]['Product_Name'] = $value['Product_Name'];
                    $data[$value['Inbound_Id']][$key]['Confirm_Qty'] = $value['Confirm_Qty'];
                    $data[$value['Inbound_Id']][$key]['all_price'] = $value['all_price'];
                    $data[$value['Inbound_Id']][$key]['remain_qty'] = $value['remain_qty'];
                    $data[$value['Inbound_Id']][$key]['Remark'] = $value['Remark'];
                endif;

                #กำหนดให้ค่า Product code กับ Product name เป็นค่าเดียวกันกับตอน borrow
                $data[$value['Inbound_Id']][$key]['import_Product_Code'] = $value['Product_Code'];
                $data[$value['Inbound_Id']][$key]['import_Product_Name'] = $value['Product_Name'];

                #กรณีที่ยังไม่ได้คืนสินค้าให้ Product code กับ Product name ไม่ต้องแสดงค่า
                if(empty($value['Inbound_Id_His'])):
                     $data[$value['Inbound_Id']][$key]['import_Product_Code'] = "";
                     $data[$value['Inbound_Id']][$key]['import_Product_Name'] = "";
                endif;

                $data[$value['Inbound_Id']][$key]['Inbound_Id_His'] = $value['Inbound_Id_His'];
                $data[$value['Inbound_Id']][$key]['import_date'] = $value['importdate'];
                $data[$value['Inbound_Id']][$key]['import_Receive_Qty'] = $value['Receive_Qty'];
                $data[$value['Inbound_Id']][$key]['import_All_Price'] = $value['All_Price'];
                $data[$value['Inbound_Id']][$key]['date_diff'] = $value['date_diff'];
                $data[$value['Inbound_Id']][$key]['Remark'] = $value['Remark'];
            endforeach;
        endif;

        return $data;
    }
}
