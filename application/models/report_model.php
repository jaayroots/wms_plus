<?php

class report_model extends CI_Model {

    protected $db_connection = NULL;

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
    }

    function searchProduct($txt_search, $limit_max = NULL, $limit_start = 0) {// Duplicate Function !!
        $this->db->select('Product_Id,Product_Code,Product_NameEN');
//        $this->db->like('Product_Code', $txt_search, 'after');
//        //$this->db->or_like('Product_NameEN',$txt_search);

        $this->db->bracket('open', 'like'); //bracket closed
        $this->db->or_like("Product_Code", $txt_search, 'after');
        $this->db->or_like("Product_NameEN", iconv("UTF-8", "TIS620//IGNORE", $txt_search));
        $this->db->bracket('close', 'like'); //bracket closed

        $this->db->from('STK_M_Product');
        $this->db->where('Active', 'Y'); //ADD BY POR 2013-10-28 เช็คเงื่อนไขเพิ่มเติมให้แสดงเฉพาะที่ยัง Active
        if (!empty($limit_max)):
            $this->db->limit($limit_max, $limit_start);
        endif;
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

    /**
     * @author kik
     * @param array $search
     * @return array
     * @created  20140409
     */
    function searchProductMovement_showItem($search) {
//        p($search);exit();
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

        if (empty($result_onhand)):

            #check transaction in from date
            $newFdateForTransaction = date('d/m/Y', mktime(0, 0, 0, $newDate[1], $newDate[0], $newDate[2]));

            $this->db->select('TOP 1 CONVERT(VARCHAR(20), Actual_Action_Date, 103) as trans_date');
            $this->db->from('STK_T_Order');
            $this->db->where('CONVERT(VARCHAR(20), Actual_Action_Date, 103) = ', $newFdateForTransaction);
            $query_order = $this->db->get();
            $result_order = $query_order->row();
//            p($result_order);
            # if not have transaction in from date check start onhand and alert
            if (empty($result_order)):

                #check onhand start date
                $this->db->select('TOP 1 CONVERT(VARCHAR(20), Available_Date, 103) as start_onhand_date
                    ,CONVERT(VARCHAR(20), (Available_Date+1), 103) as alert_onhand_date');
                $this->db->from('STK_T_Onhand_History');
                $this->db->order_by('Id', 'asc');
                $query_onhand_start = $this->db->get();
                $result_onhand_start = $query_onhand_start->row();

                if (empty($result_onhand_start)):

                    $dataRows['no_onhand'] = 'No have onhand history';

                else:

                    #check order start date
                    $this->db->select('TOP 1 CONVERT(VARCHAR(20), Actual_Action_Date, 103) as start_trans_date');
                    $this->db->from('STK_T_Order');
                    $this->db->where('Actual_Action_Date is not null');
                    $this->db->order_by('Actual_Action_Date', 'asc');
                    $query_order_start = $this->db->get();
                    $result_order_start = $query_order_start->row();
//                    p($result_onhand_start);
//                    p($result_order_start);

                    if (!empty($result_order_start)):

                        if ($result_order_start->start_trans_date <= $result_onhand_start->start_onhand_date):

                            #มี transaction ใน order ก่อนรัน onhand
                            $dataRows['no_onhand'] = 'No have order transaction. Please select from date : ' . $result_order_start->start_trans_date;
//                            echo '1';

                        else:


                            #มี onhand ก่อนมี transaction
                            $dataRows['no_onhand'] = 'No have onhand history. Please select from date : ' . $result_onhand_start->alert_onhand_date;
//                            echo '2';

                        endif;

                    else:

                        #ไม่มี transaction และ ไม่มี onhand
                        $dataRows['no_onhand'] = 'No have onhand history. Please select from date : ' . $result_onhand_start->alert_onhand_date;
//                        echo '3';

                    endif;

                endif;

                return $dataRows;

            endif;

        endif;
        #end check fdate
        #search have product, product_id,fdate,tdate
        $tmp_search = $this->getSearchDateSql($search['fdate'], $search['tdate'], 'STK_T_Order'); // add parameter STK_T_Order : by kik : 2013-11-21

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
        $this->db->select('STK_T_Workflow.Present_State');

        $this->db->from('STK_T_Order_Detail ');
        $this->db->join('STK_T_Order', 'STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id', 'LEFT');
        $this->db->join('STK_M_Location', 'STK_M_Location.Location_Id=STK_T_Order_Detail.Actual_Location_Id', 'LEFT');
        $this->db->join('STK_M_Product', 'STK_T_Order_Detail.Product_Id = STK_M_Product.Product_Id');
        $this->db->join('STK_T_Workflow', 'STK_T_Order.Flow_Id = STK_T_Workflow.Flow_Id');
        $this->db->join('SYS_M_Process', 'STK_T_Workflow.Process_Id = SYS_M_Process.Process_Id');
        $this->db->join('STK_T_Pallet', 'STK_T_Order_Detail.Pallet_Id = STK_T_Pallet.Pallet_Id', 'LEFT');

        $this->db->where('STK_T_Order_Detail.Product_Id', $search['product_id']);
        $this->db->where("(STK_T_Order.Process_Type='INBOUND' or STK_T_Order.Process_Type='OUTBOUND')");
        $this->db->where('STK_T_Order.Renter_Id', $search['renter_id']);
        $this->db->where('STK_T_Order_Detail.Active', 'Y');
        $this->db->where('STK_M_Product.Active', 'Y');
        $this->db->where('STK_T_Order_Detail.Activity_By  IS NOT NULL');
        $this->db->where("((STK_T_Workflow.Process_Id IN (1) AND STK_T_Workflow.Present_State in (-2,5,6))
                                 OR
                           (STK_T_Workflow.Process_Id IN (2) AND STK_T_Workflow.Present_State in (-2)))");

        if ($tmp_search != "") {
            $this->db->where($tmp_search);
        }

        //
        //ADD BY POR 2013-10-28 เพิ่มเงื่อนไขค้นหา document
        if ($search['doc_value'] != "") {
            $this->db->where("STK_T_Order." . $search['doc_type'] . " LIKE '%" . $this->db->escape_like_str(trim($search['doc_value'])) . "%' ");
        }
        //END ADD

        $this->db->order_by('STK_T_Order.Actual_Action_Date ASC');

        $query = $this->db->get();
        $result = $query->result();

    //    p($this->db->last_query());
    //    exit();
        //-------------------------- Reject query area --------------------------------//
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
        $this->db->select('STK_T_Workflow.Present_State');

        $this->db->from('STK_T_Order_Detail ');
        $this->db->join('STK_T_Order', 'STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id', 'LEFT');
        $this->db->join('STK_T_Inbound', 'STK_T_Inbound.Item_id = STK_T_Order_Detail.Item_id', 'INNER');
        $this->db->join('STK_M_Location', 'STK_M_Location.Location_Id=STK_T_Order_Detail.Actual_Location_Id', 'LEFT');
        $this->db->join('STK_T_Workflow', 'STK_T_Order.Flow_Id = STK_T_Workflow.Flow_Id');
        $this->db->join('SYS_M_Process', 'STK_T_Workflow.Process_Id = SYS_M_Process.Process_Id');
        $this->db->join('STK_T_Pallet', 'STK_T_Order_Detail.Pallet_Id = STK_T_Pallet.Pallet_Id', 'LEFT');

        $this->db->where('STK_T_Order_Detail.Product_Id', $search['product_id']);
        $this->db->where("(STK_T_Order.Process_Type='INBOUND' or STK_T_Order.Process_Type='OUTBOUND')");
        $this->db->where('STK_T_Order.Renter_Id', $search['renter_id']);
        $this->db->where('STK_T_Order_Detail.Activity_By  IS NOT NULL');
        $this->db->where("(STK_T_Workflow.Process_Id IN (1) AND STK_T_Workflow.Present_State in (-1))");

        if ($tmp_search != "") {
            $this->db->where($tmp_search);
        }

        //
        //ADD BY POR 2013-10-28 เพิ่มเงื่อนไขค้นหา document
        if ($search['doc_value'] != "") {
            $this->db->where("STK_T_Order." . $search['doc_type'] . " LIKE '%" . $this->db->escape_like_str(trim($search['doc_value'])) . "%' ");
        }
        //END ADD

        $this->db->order_by('STK_T_Order.Actual_Action_Date ASC');

        $query_reject = $this->db->get();
        $result_reject = $query_reject->result();
        //-------------------------- End Reject query area --------------------------------//

        $result_tmp = array_merge($result, $result_reject); // p($result_tmp);exit();
        $result_all = sort_arr_of_obj($result_tmp, 'Receive_Date_sort', 'asc');

        $dataRow = array();
        $start_balance = array();

//        p($result_all);
//        exit();

        $incom_bal = $this->getProductBalanceStockMovement_byProduct($search['product_id'], $newFdate);
        $balance = $incom_bal;
        $keyTmp = 0;
        foreach ($result_all as $value) {

            # case INBOUND
            if (trim($value->Process_Type) == "INBOUND") {

                $balance = $balance + $value->Confirm_Qty;
                $dataRow['pay_doc_no'] = '';
                $dataRow['receive_doc_no'] = $value->receive_doc_no;
                $dataRow['receive_refer_ext'] = $value->receive_refer_ext;
                $dataRow['pay_refer_ext'] = '';
                $dataRow['r_qty'] = $value->Confirm_Qty;
                $dataRow['p_qty'] = 0;
            } else if (trim($value->Process_Type) == "OUTBOUND") {

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
            $dataRow['start_balance'] = $incom_bal;
            $dataRow['Pallet_Code'] = $value->Pallet_Code;

            if (trim($value->Process_Type) == "INBOUND" && $value->Present_State == -1):
                $dataRow['Is_reject'] = 'Y';
            else:
                $dataRow['Is_reject'] = 'N';
            endif;

            $dataRows[$keyTmp] = $dataRow;
            unset($dataRow);
            $keyTmp++;


            //----------------------- reject area ---------------------------//
            if (trim($value->Process_Type) == "INBOUND" && $value->Present_State == -1):

                $balance = $balance - $value->Confirm_Qty;
                $dataRow_reject['pay_doc_no'] = '';
                $dataRow_reject['receive_doc_no'] = $value->receive_doc_no;
                $dataRow_reject['receive_refer_ext'] = $value->receive_refer_ext;
                $dataRow_reject['pay_refer_ext'] = '';
                $dataRow_reject['r_qty'] = -$value->Confirm_Qty;
                $dataRow_reject['p_qty'] = 0;
                $dataRow_reject['receive_date'] = $value->Receive_Date;
                $dataRow_reject['Product_NameEN'] = $value->Product_NameEN;
                $dataRow_reject['Product_SerLot'] = $value->Product_Serial . "/" . $value->Product_Lot;
                $dataRow_reject['Product_Mfd'] = $value->Product_Mfd;
                $dataRow_reject['Product_Exp'] = $value->Product_Exp;
                $dataRow_reject['Balance_Qty'] = $balance;
                $dataRow_reject['branch'] = $value->s_name . "/" . $value->d_name;
                $dataRow_reject['process_type'] = $value->Process_Type;
                $dataRow_reject['Location_Code'] = $value->Location_Code;
                $dataRow_reject['start_balance'] = $incom_bal;
                $dataRow_reject['Pallet_Code'] = $value->Pallet_Code;
                $dataRow_reject['Is_reject'] = 'Y';

                $dataRows[$keyTmp] = $dataRow_reject;

                unset($dataRow_reject);
                $keyTmp++;

            endif;
            //----------------------- end reject area ---------------------------//
        }

//        p($dataRows);exit();
        return $dataRows;
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
        // p($this->db->last_query()); exit;
//        p($result_onhand);

        if (empty($result_onhand)):

            #check transaction in from date
            $newFdateForTransaction = date('d/m/Y', mktime(0, 0, 0, $newDate[1], $newDate[0], $newDate[2]));

            $this->db->select('TOP 1 CONVERT(VARCHAR(20), Actual_Action_Date, 103) as trans_date');
            $this->db->from('STK_T_Order');
            $this->db->where('CONVERT(VARCHAR(20), Actual_Action_Date, 103) = ', $newFdateForTransaction);
            $query_order = $this->db->get();
            $result_order = $query_order->row();
//            p($result_order);
            # if not have transaction in from date check start onhand and alert
            if (empty($result_order)):

                #check onhand start date
                $this->db->select('TOP 1 CONVERT(VARCHAR(20), Available_Date, 103) as start_onhand_date
                    ,CONVERT(VARCHAR(20), (Available_Date+1), 103) as alert_onhand_date');
                $this->db->from('STK_T_Onhand_History');
                $this->db->order_by('Id', 'asc');
                $query_onhand_start = $this->db->get();
                $result_onhand_start = $query_onhand_start->row();

                if (empty($result_onhand_start)):

                    $dataRows['no_onhand'] = 'No have onhand history';

                else:

                    #check order start date
                    $this->db->select('TOP 1 CONVERT(VARCHAR(20), Actual_Action_Date, 103) as start_trans_date');
                    $this->db->from('STK_T_Order');
                    $this->db->where('Actual_Action_Date is not null');
                    $this->db->order_by('Actual_Action_Date', 'asc');
                    $query_order_start = $this->db->get();
                    $result_order_start = $query_order_start->row();
//                    p($result_onhand_start);
//                    p($result_order_start);

                    if (!empty($result_order_start)):

                        if ($result_order_start->start_trans_date <= $result_onhand_start->start_onhand_date):

                            #มี transaction ใน order ก่อนรัน onhand
                            $dataRows['no_onhand'] = 'No have order transaction. Please select from date : ' . $result_order_start->start_trans_date;
//                            echo '1';

                        else:


                            #มี onhand ก่อนมี transaction
                            $dataRows['no_onhand'] = 'No have onhand history. Please select from date : ' . $result_onhand_start->alert_onhand_date;
//                            echo '2';

                        endif;

                    else:

                        #ไม่มี transaction และ ไม่มี onhand
                        $dataRows['no_onhand'] = 'No have onhand history. Please select from date : ' . $result_onhand_start->alert_onhand_date;
//                        echo '3';

                    endif;

                endif;

                return $dataRows;

            endif;

        endif;
        #end check fdate

        $tmp_search = $this->getSearchDateSql($search['fdate'], $search['tdate'], 'STK_T_Order');
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
                           (STK_T_Workflow.Process_Id IN (2) AND STK_T_Workflow.Present_State in (-2)))");

        if ($tmp_search != "") {
            $this->db->where($tmp_search);
        }

        //ADD BY POR 2013-10-28 เพิ่มเงื่อนไขค้นหา document
        if ($search['doc_value'] != "") {
            $this->db->where("STK_T_Order." . $search['doc_type'] . " LIKE '%" . $this->db->escape_like_str(trim($search['doc_value'])) . "%' ");
        }
        //END ADD
        if ($this->config->item('build_pallet')):
            $this->db->order_by('STK_T_Order_Detail.Product_Id,STK_T_Order_Detail.Pallet_Id,STK_T_Order.Actual_Action_Date ASC');
        else:
            $this->db->order_by('STK_T_Order_Detail.Product_Id,STK_T_Order.Actual_Action_Date ASC');
        endif;

        $query = $this->db->get();
            // p($this->db->last_query()); exit;
        $result = $query->result();
//p($result);exit();
        $dataRows = array();
        $start_balance = array();
        $incom_balance = array();

        foreach ($result as $value) {
//            p($value);

            if (isset($value->Product_Code, $dataRows[$value->Product_Code])) {

                $keyTmp = sizeof($dataRows[$value->Product_Code]);
                $balance = $dataRows[$value->Product_Code][$keyTmp - 1]['Balance_Qty'];
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
            }

            // add Process_Type ADJ-STOCK : by kik : 2013-11-21
            else if (trim($value->Process_Type) == "OUTBOUND") {

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
            $dataRow['start_balance'] = $start_balance[$value->Product_Code];
            $dataRow['Pallet_Code'] = $value->Pallet_Code;

            $dataRows[$value->Product_Code][$keyTmp] = $dataRow;

            unset($dataRow);
        }

//      p($dataRows);//exit();
        return $dataRows;
    }

    function searchProductMovement_showTotal($search) {
//      p($search);exit();
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

        if (empty($result_onhand)):

            #check transaction in from date
            $newFdateForTransaction = date('d/m/Y', mktime(0, 0, 0, $newDate[1], $newDate[0], $newDate[2]));

            $this->db->select('TOP 1 CONVERT(VARCHAR(20), Actual_Action_Date, 103) as trans_date');
            $this->db->from('STK_T_Order');
            $this->db->where('CONVERT(VARCHAR(20), Actual_Action_Date, 103) = ', $newFdateForTransaction);
            $query_order = $this->db->get();
            $result_order = $query_order->row();
//            p($result_order);
            # if not have transaction in from date check start onhand and alert
            if (empty($result_order)):

                #check onhand start date
                $this->db->select('TOP 1 CONVERT(VARCHAR(20), Available_Date, 103) as start_onhand_date
                    ,CONVERT(VARCHAR(20), (Available_Date+1), 103) as alert_onhand_date');
                $this->db->from('STK_T_Onhand_History');
                $this->db->order_by('Id', 'asc');
                $query_onhand_start = $this->db->get();
                $result_onhand_start = $query_onhand_start->row();

                if (empty($result_onhand_start)):

                    $dataRows['no_onhand'] = 'No have onhand history';

                else:

                    #check order start date
                    $this->db->select('TOP 1 CONVERT(VARCHAR(20), Actual_Action_Date, 103) as start_trans_date');
                    $this->db->from('STK_T_Order');
                    $this->db->where('Actual_Action_Date is not null');
                    $this->db->order_by('Actual_Action_Date', 'asc');
                    $query_order_start = $this->db->get();
                    $result_order_start = $query_order_start->row();
//                    p($result_onhand_start);
//                    p($result_order_start);

                    if (!empty($result_order_start)):

                        if ($result_order_start->start_trans_date <= $result_onhand_start->start_onhand_date):

                            #มี transaction ใน order ก่อนรัน onhand
                            $dataRows['no_onhand'] = 'No have order transaction. Please select from date : ' . $result_order_start->start_trans_date;
//                            echo '1';

                        else:


                            #มี onhand ก่อนมี transaction
                            $dataRows['no_onhand'] = 'No have onhand history. Please select from date : ' . $result_onhand_start->alert_onhand_date;
//                            echo '2';

                        endif;

                    else:

                        #ไม่มี transaction และ ไม่มี onhand
                        $dataRows['no_onhand'] = 'No have onhand history. Please select from date : ' . $result_onhand_start->alert_onhand_date;
//                        echo '3';

                    endif;

                endif;

                return $dataRows;

            endif;

        endif;
        #end check fdate

        $tmp_search = $this->getSearchDateSql($search['fdate'], $search['tdate'], 'STK_T_Order');

        if ($search['doc_value'] != "") {
            $tmp_search.=" AND " . $search['doc_type'] . " LIKE '%" . $this->db->escape_like_str(trim($search['doc_value'])) . "%' ";
        }

        $this->db->select('distinct(inbound.Product_Code)');
        $this->db->select('(SELECT Product_NameEN FROM STK_M_Product p WHERE p.Product_Id=inbound.Product_Id) As Product_NameEN');

        $this->db->select('(select sum(isnull(Balance_Qty,0))
                            from STK_T_Onhand_History
                            where CONVERT(VARCHAR(20), Available_Date, 103) =  "' . $newFdate . '"
                            and Product_Id = inbound.Product_Id
                            ) as incoming_qty');

        $this->db->select('(select sum(isnull(Confirm_Qty,0))
                            from stk_t_order_detail
                            where Order_Id  in(
                                select order_id from STK_T_Order where  Document_No in (select  Document_No from stk_t_workflow
                                where
                                  Process_Id = 1
                                   AND  (Present_State = -2 or Present_State >=5 )
                                   AND ' . $tmp_search . '
                                   AND Renter_Id = ' . $search['renter_id'] . '
                                )
                            )
                            and active ="Y"
                            and Product_Id = inbound.Product_Id
                            ) as receive_qty');

        $this->db->select(' (select sum(isnull(Confirm_Qty,0))
                            from stk_t_order_detail
                            where Order_Id  in(
                                select order_id from STK_T_Order where  Document_No in (select  Document_No from stk_t_workflow
                                where
                                    Process_Id=2
                                     AND  Present_State = -2
                                     AND ' . $tmp_search . '
                                     AND Renter_Id = ' . $search['renter_id'] . '
                                )
                            )
                            and active ="Y"
                            and Product_Id = inbound.Product_Id
                            )  as dispatch_qty');


        $this->db->from('STK_T_Inbound inbound');
//        $this->db->join('STK_M_Product product' , "inbound.Product_Id = product.Product_Id" , "LEFT");
        $this->db->where('inbound.Renter_Id', $search['renter_id']);
        $this->db->where('inbound.Flow_Id NOT IN (select Flow_Id from STK_T_Workflow where Present_State = -1)');
        if ($search['product_id'] != "") {
            $this->db->where('inbound.Product_Id', $search['product_id']);
        }
//        $this->db->where('product.Active', 'Y');
        $query = $this->db->get();
//        p($this->db->last_query()); exit();
        $result = $query->result_array();
//        p($result);exit();
        return $result;
    }

    #ISSUE 2448 Batch OnHand
    #DATE:18-09-2013
    #BY:KIK
    #แก้ไข query ที่ผิดอยู่
    #START New Comment Code #ISSUE 2448

    function searchProductMovement($search) {

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

        if (empty($result_onhand)):

            #check transaction in from date
            $newFdateForTransaction = date('d/m/Y', mktime(0, 0, 0, $newDate[1], $newDate[0], $newDate[2]));

            $this->db->select('TOP 1 CONVERT(VARCHAR(20), Actual_Action_Date, 103) as trans_date');
            $this->db->from('STK_T_Order');
            $this->db->where('CONVERT(VARCHAR(20), Actual_Action_Date, 103) = ', $newFdateForTransaction);
            $query_order = $this->db->get();
            $result_order = $query_order->row();
//            p($result_order);
            # if not have transaction in from date check start onhand and alert
            if (empty($result_order)):

                #check onhand start date
                $this->db->select('TOP 1 CONVERT(VARCHAR(20), Available_Date, 103) as start_onhand_date
                    ,CONVERT(VARCHAR(20), (Available_Date+1), 103) as alert_onhand_date');
                $this->db->from('STK_T_Onhand_History');
                $this->db->order_by('Id', 'asc');
                $query_onhand_start = $this->db->get();
                $result_onhand_start = $query_onhand_start->row();

                if (empty($result_onhand_start)):

                    $dataRows['no_onhand'] = 'No have onhand history';

                else:

                    #check order start date
                    $this->db->select('TOP 1 CONVERT(VARCHAR(20), Actual_Action_Date, 103) as start_trans_date');
                    $this->db->from('STK_T_Order');
                    $this->db->where('Actual_Action_Date is not null');
                    $this->db->order_by('Actual_Action_Date', 'asc');
                    $query_order_start = $this->db->get();
                    $result_order_start = $query_order_start->row();
//                    p($result_onhand_start);
//                    p($result_order_start);

                    if (!empty($result_order_start)):

                        if ($result_order_start->start_trans_date <= $result_onhand_start->start_onhand_date):

                            #มี transaction ใน order ก่อนรัน onhand
                            $dataRows['no_onhand'] = 'No have order transaction. Please select from date : ' . $result_order_start->start_trans_date;
//                            echo '1';

                        else:


                            #มี onhand ก่อนมี transaction
                            $dataRows['no_onhand'] = 'No have onhand history. Please select from date : ' . $result_onhand_start->alert_onhand_date;
//                            echo '2';

                        endif;

                    else:

                        #ไม่มี transaction และ ไม่มี onhand
                        $dataRows['no_onhand'] = 'No have onhand history. Please select from date : ' . $result_onhand_start->alert_onhand_date;
//                        echo '3';

                    endif;

                endif;

                return $dataRows;

            endif;

        endif;
        #end check fdate
        #search have product, product_id,fdate,tdate
        $tmp_search = $this->getSearchDateSql($search['fdate'], $search['tdate'], 'STK_T_Order'); // add parameter STK_T_Order : by kik : 2013-11-21


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

        $this->db->where('STK_T_Order_Detail.Product_Id', $search['product_id']);
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

        //
        //ADD BY POR 2013-10-28 เพิ่มเงื่อนไขค้นหา document
        if ($search['doc_value'] != "") {
            $this->db->where("STK_T_Order." . $search['doc_type'] . " LIKE '%" . $this->db->escape_like_str(trim($search['doc_value'])) . "%' ");
        }
        //END ADD

        $this->db->order_by('STK_T_Order.Actual_Action_Date ASC');

        $query = $this->db->get();
        $result_order = $query->result();


//       ====================================================================================

        $tmp_search = $this->getSearchDateSql($search['fdate'], $search['tdate'], 'STK_T_Relocate');
        $this->db->select('STK_T_Relocate_Detail.Product_Id');
        $this->db->select('STK_T_Relocate_Detail.Product_Code');
        $this->db->select('CONVERT(VARCHAR(20), STK_T_Relocate.Actual_Action_Date, 103) AS Receive_Date');
        $this->db->select('CONVERT(VARCHAR(20), STK_T_Relocate.Actual_Action_Date, 120) AS Receive_Date_sort');
        $this->db->select('STK_T_Relocate.Actual_Action_Date');
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

        $this->db->where('STK_T_Relocate_Detail.Product_Id', $search['product_id']);
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

        $this->db->order_by('STK_T_Relocate_Detail.Product_Id,STK_T_Relocate.Actual_Action_Date ASC');

        $query = $this->db->get();
//             p($this->db->last_query()); //exit();
        $result_relocate = $query->result();

//        p($result_relocate);
//        ====================================================================================

        $result_tmp = array_merge($result_order, $result_relocate); // p($result_tmp);exit();
        $result = sort_arr_of_obj($result_tmp, 'Receive_Date_sort', 'asc');

        $dataRow = array();
        $start_balance = array();

        //p($result);
        //exit();
        foreach ($result as $value) {

            $SerLotMfdExpTmp = $value->Product_Serial . $value->Product_Lot . $value->Product_Mfd . $value->Product_Exp;

            if (isset($SerLotMfdExpTmp, $dataRows[$SerLotMfdExpTmp])) {
                $keyTmp = sizeof($dataRows[$SerLotMfdExpTmp]);
                $balance = $dataRows[$SerLotMfdExpTmp][$keyTmp - 1]['Balance_Qty'];
            } else {
                $keyTmp = 0;
//               $newDate = split('/', $search['fdate']);

                $balance = $this->getProductBalanceStockMovement($search['product_id'], $newFdate, $value->Product_Lot, $value->Product_Serial, $value->Product_Mfd, $value->Product_Exp);
                $start_balance[$SerLotMfdExpTmp] = $balance;  // add by kik : 2013-11-25
//               $endBalance = $this->getProductBalanceStockMovement($search['product_id'], $search['tdate'],$value->Product_Lot,$value->Product_Serial,$value->Product_Mfd,$value->Product_Exp);
//               echo "<br>";
            }

            if (
                    trim($value->Process_Type) == "RE-LOCATE1" ||
                    trim($value->Process_Type) == "RE-LOCATE2" ||
                    trim($value->Process_Type) == "RE-LOCATE3" ||
                    trim($value->Process_Type) == "RE-LOCATE4"):
//            if(trim($value->Process_Type) == "CH-STATUS" ||
//                    trim($value->Process_Type) == "RE-LOCATE1" ||
//                    trim($value->Process_Type) == "RE-LOCATE2" ||
//                    trim($value->Process_Type) == "RE-LOCATE3"):
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

                if (trim($value->Process_Type) == "RE-LOCATE1" || trim($value->Process_Type) == "RE-LOCATE2" || trim($value->Process_Type) == "RE-LOCATE3" || trim($value->Process_Type) == "RE-LOCATE4"):
                    $dataRow['Location_Code'] = $value->Old_Location_Code;
                else:
                    $dataRow['Location_Code'] = $value->Location_Code;
                endif;

                $dataRow['start_balance'] = $start_balance[$SerLotMfdExpTmp];             // add by kik : 2013-11-25

                $dataRow['Pallet_Code'] = $value->Pallet_Code;

                $dataRows[$SerLotMfdExpTmp][$keyTmp] = $dataRow;

                unset($dataRow);
                $keyTmp = sizeof($dataRows[$SerLotMfdExpTmp]);

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
                $dataRow['start_balance'] = $start_balance[$SerLotMfdExpTmp];             // add by kik : 2013-11-25

                $dataRow['Pallet_Code'] = $value->Pallet_Code;

                $dataRows[$SerLotMfdExpTmp][$keyTmp] = $dataRow;
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
                        trim($value->Process_Type) == "TRN-STOCK") {

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
                $dataRow['start_balance'] = $start_balance[$SerLotMfdExpTmp];             // add by kik : 2013-11-25

                $dataRow['Pallet_Code'] = $value->Pallet_Code;

                $dataRows[$SerLotMfdExpTmp][$keyTmp] = $dataRow;

                unset($dataRow);
                unset($SerLotMfdExpTmp);

            endif;
        }
        //p($dataRows);
        return $dataRows;
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

        if (empty($result_onhand)):

            #check transaction in from date
            $newFdateForTransaction = date('d/m/Y', mktime(0, 0, 0, $newDate[1], $newDate[0], $newDate[2]));

            $this->db->select('TOP 1 CONVERT(VARCHAR(20), Actual_Action_Date, 103) as trans_date');
            $this->db->from('STK_T_Order');
            $this->db->where('CONVERT(VARCHAR(20), Actual_Action_Date, 103) = ', $newFdateForTransaction);
            $query_order = $this->db->get();
            $result_order = $query_order->row();
//            p($result_order);
            # if not have transaction in from date check start onhand and alert
            if (empty($result_order)):

                #check onhand start date
                $this->db->select('TOP 1 CONVERT(VARCHAR(20), Available_Date, 103) as start_onhand_date
                    ,CONVERT(VARCHAR(20), (Available_Date+1), 103) as alert_onhand_date');
                $this->db->from('STK_T_Onhand_History');
                $this->db->order_by('Id', 'asc');
                $query_onhand_start = $this->db->get();
                $result_onhand_start = $query_onhand_start->row();

                if (empty($result_onhand_start)):

                    $dataRows['no_onhand'] = 'No have onhand history';

                else:

                    #check order start date
                    $this->db->select('TOP 1 CONVERT(VARCHAR(20), Actual_Action_Date, 103) as start_trans_date');
                    $this->db->from('STK_T_Order');
                    $this->db->where('Actual_Action_Date is not null');
                    $this->db->order_by('Actual_Action_Date', 'asc');
                    $query_order_start = $this->db->get();
                    $result_order_start = $query_order_start->row();
//                    p($result_onhand_start);
//                    p($result_order_start);

                    if (!empty($result_order_start)):

                        if ($result_order_start->start_trans_date <= $result_onhand_start->start_onhand_date):

                            #มี transaction ใน order ก่อนรัน onhand
                            $dataRows['no_onhand'] = 'No have order transaction. Please select from date : ' . $result_order_start->start_trans_date;
//                            echo '1';

                        else:


                            #มี onhand ก่อนมี transaction
                            $dataRows['no_onhand'] = 'No have onhand history. Please select from date : ' . $result_onhand_start->alert_onhand_date;
//                            echo '2';

                        endif;

                    else:

                        #ไม่มี transaction และ ไม่มี onhand
                        $dataRows['no_onhand'] = 'No have onhand history. Please select from date : ' . $result_onhand_start->alert_onhand_date;
//                        echo '3';

                    endif;

                endif;

                return $dataRows;

            endif;

        endif;
        #end check fdate

        $tmp_search = $this->getSearchDateSql($search['fdate'], $search['tdate'], 'STK_T_Order');
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
        if ($this->config->item('build_pallet')):
            $this->db->order_by('STK_T_Order_Detail.Product_Id,STK_T_Order_Detail.Pallet_Id,STK_T_Order.Actual_Action_Date ASC');
        else:
            $this->db->order_by('STK_T_Order_Detail.Product_Id,STK_T_Order.Actual_Action_Date ASC');
        endif;

        $query = $this->db->get();
//             p($this->db->last_query());// exit();
        $result_order = $query->result();
//p($result_order);
//        ====================================================================================

        $tmp_search = $this->getSearchDateSql($search['fdate'], $search['tdate'], 'STK_T_Relocate');
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

        if ($this->config->item('build_pallet')):
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
        $result = sort_arr_of_obj($result_tmp, 'Receive_Date_sort', 'asc');
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
                $start_balance[$value->Product_Code . $SerLotMfdExpTmp] = $balance;  // add by kik : 2013-11-25
            }

//            if(trim($value->Process_Type) == "CH-STATUS" ||
//                    trim($value->Process_Type) == "RE-LOCATE1" ||
//                    trim($value->Process_Type) == "RE-LOCATE2" ||
//                    trim($value->Process_Type) == "RE-LOCATE3"):
            if (
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

                if (trim($value->Process_Type) == "RE-LOCATE1" || trim($value->Process_Type) == "RE-LOCATE2" || trim($value->Process_Type) == "RE-LOCATE3" || trim($value->Process_Type) == "RE-LOCATE4"):
                    $dataRow['Location_Code'] = $value->Old_Location_Code;
                else:
                    $dataRow['Location_Code'] = $value->Location_Code;
                endif;

                $dataRow['start_balance'] = $start_balance[$value->Product_Code . $SerLotMfdExpTmp];             // add by kik : 2013-11-25

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
                $dataRow['start_balance'] = $start_balance[$value->Product_Code . $SerLotMfdExpTmp];             // add by kik : 2013-11-25

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
                        trim($value->Process_Type) == "TRN-STOCK") {

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
                $dataRow['start_balance'] = $start_balance[$value->Product_Code . $SerLotMfdExpTmp];             // add by kik : 2013-11-25

                $dataRow['Pallet_Code'] = $value->Pallet_Code;

                $dataRows[$value->Product_Code][$SerLotMfdExpTmp][$keyTmp] = $dataRow;

                //if($value->Product_Code == '03140237' && $value->receive_doc_no == 'DDR20140324-00023' && $value->Confirm_Qty = '3000'){
                //echo $start_balance[$value->Product_Code.$SerLotMfdExpTmp];
                //	p( $dataRow);
                //}
                unset($dataRow);
                unset($SerLotMfdExpTmp);

            endif;
        }

//      p($dataRows);//exit();
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
//        p($result);exit();
//       p($this->db->last_query());
        if (!empty($result[0]->sumBalance_Qty)) {
            return $result[0]->sumBalance_Qty;
        } else {
            return 0;
        }
    }

    // add parameter "use_table" : by kik : 21-11-2013
    function getSearchDateSql($from_date = NULL, $to_date = NULL, $use_table = 'STK_T_Order') {

        $tmp_search = '';

        if ($from_date != NULL) {
            $from_date = convertDate($from_date, "eng", "iso", "-");
        }
        if ($to_date != NULL) {
            $to_date = convertDate($to_date, "eng", "iso", "-");
        }

        if ($from_date != NULL) {
            if ($to_date != NULL) {
                $tmp_search = $use_table . ".Actual_Action_Date >= '" . $from_date . " 00:00:00'
				AND " . $use_table . ".Actual_Action_Date <= '" . $to_date . " 23:59:59'";
            } else {
                $tmp_search = $use_table . ".Actual_Action_Date>='" . $from_date . "'
                                AND " . $use_table . ".Actual_Action_Date<='" . $from_date . " 23:59:59'";
            }
        } else {
            if ($to_date != NULL) {
                $tmp_search = $use_table . ".Actual_Action_Date>='" . $to_date . "'
                                AND " . $use_table . ".Actual_Action_Date<='" . $to_date . " 23:59:59'";
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

        // Temp Fix By Ball
        // If same day collect from previous day (CurrentDate - 1)

        if ($f_date != "" && $f_date == date("d/m/Y")) {
            // search today
            // Remove By Ball and change to calculate to previous date 20130912
            //$sql = "SELECT SUM(Receive_Qty-(Dispatch_Qty+Adjust_Qty)) AS sum_qty FROM STK_T_Inbound WHERE Product_Id=$product_id AND Receive_Date < '".$f_date."' ";
            $sql = "SELECT coalesce(SUM(Receive_Qty-(Dispatch_Qty+Adjust_Qty)), 0) AS sum_qty FROM STK_T_Inbound WHERE Product_Id=$product_id And Receive_Date < GETDATE()";
            $query = $this->db->query($sql);
            $result = $query->result();
            $balance = $result[0]->sum_qty;
            return $balance;

            exit();
        } else if ($f_date != "" && $f_date != date("d/m/Y")) {
            //$from_date=convertDate($search['fdate'],"eng","iso","-");
            if ($t_date != "") {
                $from_date = convertDate($f_date, "eng", "iso", "-");
                $to_date = convertDate($t_date, "eng", "iso", "-");
                $tmp_search = " AND Available_Date>='" . $from_date . "'
							  AND Available_Date<='" . $to_date . " 23:59:59'
				";
            } else {
                $tmp_search = " AND CONVERT(VARCHAR(20), Available_Date, 103)='" . $f_date . "'";
            }
        } else if ($t_date != "") {
            $tmp_search = " AND CONVERT(VARCHAR(20), Available_Date, 103)='" . $t_date . "'";
        } else {
            
        }

        if ($tmp_search == "") {
            $sql = "SELECT SUM(Available_Qty) AS sum_qty FROM STK_T_Onhand_History WHERE Product_Id=$product_id
				  GROUP BY Available_Date ORDER BY Available_Date ASC";
        } else {
            $sql = "SELECT SUM(Available_Qty) AS sum_qty FROM STK_T_Onhand_History WHERE Product_Id=$product_id
			  " . $tmp_search;
        }

//        echo '<br> sql = '.$sql;
        $query = $this->db->query($sql);
        $result = $query->result();
        if (count($result) != 0) {
            $balance = $result[0]->sum_qty;
        } else {
            $balance = 0;
        }
        return $balance;
    }

    function getGRN($search, $limit_start = 0, $limit_max = 100, $report_flag = FALSE) {
        $option = '';
        if ($search['fdate'] != "") {
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") {
                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
                $option = " AND STK_T_Order.Estimate_Action_Date>='" . $from_date . "' AND STK_T_Order.Estimate_Action_Date<='" . $to_date . " 23:59:59'";
            } else {

                $option = " AND STK_T_Order.Estimate_Action_Date>='" . $from_date . "' AND STK_T_Order.Estimate_Action_Date<='" . $from_date . " 23:59:59'";
            }
        } else {
            if ($search['tdate'] != "") {
                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
                $option = " AND STK_T_Order.Estimate_Action_Date>='" . $to_date . "' AND STK_T_Order.Estimate_Action_Date<='" . $to_date . " 23:59:59'";
            }
        }

        // ADD By Ball
        if (!empty($search['document_no'])) :
            $option = " AND STK_T_Order.Document_No = '" . $search['document_no'] . "'";
        endif;
        // End

        $sql = ";WITH TopParents AS (
		    SELECT Flow_Id, Parent_Flow
		    FROM STK_T_Workflow
		    WHERE Parent_Flow IS NULL
		    UNION ALL
		    SELECT c.Flow_Id, ISNULL(p.Parent_Flow, p.Flow_Id) ParentCategoryID
		    FROM STK_T_Workflow c
		    JOIN TopParents p ON c.Parent_Flow = p.Flow_Id
		)

		SELECT
		CONVERT(VARCHAR(20),STK_T_Order.Estimate_Action_Date, 103) as Estimate_Action_Date,
                CONVERT(VARCHAR(20),STK_T_Order.Estimate_Action_Date, 120) as Estimate_Action_Date_sort,
		STK_T_Order.Doc_Refer_Ext,STK_T_Order.Doc_Refer_Int,
		(Select Company_NameEN from CTL_M_Company where Company_Id = STK_T_Order.Source_Id) as supplier,
		STK_T_Order_Detail.Product_Code,
		STK_M_Product.Product_NameEN,
		STK_T_Order_Detail.Reserv_Qty  as reserv_quan,
		CONVERT(VARCHAR(20),STK_T_Order.Actual_Action_Date, 103) as Actual_Action_Date ,
		CONVERT(VARCHAR(20),STK_T_Inbound.Unlock_Pending_Date, 103) as Pending_Date,
		SUM(STK_T_Order_Detail.Confirm_Qty) as Reserv_Qty,
		STK_T_Order_Detail.Inbound_Item_Id,
		STK_T_Order_Detail.Confirm_Qty,
                (SELECT Top 1 Remark
            		FROM STK_T_Relocate_Detail
            		WHERE Document_No = STK_T_Order.Document_No
            		AND Product_Code = STK_T_Order_Detail.Product_Code
            		AND Remark != '') AS trueRemark,
		STK_T_Order_Detail.Remark,
		TopParents.Flow_Id As FID,
		TopParents.Parent_Flow As PID,
		STK_T_Order.Flow_Id,
		STK_T_Workflow.Parent_Flow,
		SYS_M_Process.Process_NameEn,
		SYS_M_State.State_NameEn,
		STK_T_Order.Document_No,
		STK_T_Order.Order_Id,
		STK_T_Order_Detail.Product_Id,
		STK_T_Order_Detail.Split_From_Item_Id,
		STK_T_Order_Detail.Activity_Code,
			CASE
				WHEN TopParents.Parent_Flow Is Null THEN TopParents.Flow_Id
				WHEN TopParents.Parent_Flow Is Not Null THEN TopParents.Parent_Flow
				WHEN TopParents.Parent_Flow Is Not Null And STK_T_Order_Detail.Split_From_Item_Id Is Not Null THEN TopParents.Parent_Flow
				WHEN TopParents.Parent_Flow Is Not Null And STK_T_Order_Detail.Split_From_Item_Id Is Null THEN TopParents.Flow_Id
			END As SameDate,
                'Is_reject' = CASE
                    WHEN STK_T_Workflow.Present_State <> -1 THEN 'N'
                    WHEN  STK_T_Workflow.Present_State = -1 THEN 'Y'
                 END ,
                STK_T_Order_Detail.Active
		FROM STK_T_Workflow
		INNER JOIN SYS_M_Process on STK_T_Workflow.Process_Id = SYS_M_Process.Process_Id
		LEFT JOIN SYS_M_State on STK_T_Workflow.Process_Id = SYS_M_State.Process_Id and STK_T_Workflow.Present_State = SYS_M_State.State_No
		INNER JOIN STK_T_Order on STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id
		INNER JOIN STK_T_Order_Detail on STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id
		INNER JOIN STK_M_Product on STK_T_Order_Detail.Product_Code = STK_M_Product.Product_Code
		LEFT OUTER JOIN STK_T_Inbound on STK_T_Inbound.Document_No = STK_T_Order.Document_No and STK_T_Inbound.Active='Y' and STK_T_Inbound.Product_Code =STK_T_Order_Detail.Product_Code
		INNER JOIN TopParents On TopParents.Flow_Id = STK_T_Workflow.Flow_Id
		WHERE ((STK_T_Workflow.Process_Id =1 and STK_T_Workflow.Present_State in (-2,5,6,-1)) or (STK_T_Workflow.Process_Id = 7 and STK_T_Workflow.Present_State =2 ) ) " . $option . "
		GROUP BY
		STK_T_Order.Estimate_Action_Date,
		STK_T_Order.Doc_Refer_Ext,
		STK_T_Order.Source_Id,
		STK_T_Order_Detail.Product_Code,
		STK_M_Product.Product_NameEN,
		STK_T_Order_Detail.Reserv_Qty,
		STK_T_Order.Actual_Action_Date,
		STK_T_Inbound.Unlock_Pending_Date,
		STK_T_Order_Detail.Confirm_Qty,
		STK_T_Order_Detail.Remark,
		STK_T_Order.Flow_Id,
		STK_T_Workflow.Flow_Id,
		SYS_M_Process.Process_NameEn,
		SYS_M_State.State_NameEn,
		STK_T_Order.Document_No,
		STK_T_Order.Order_Id,
		STK_T_Order_Detail.Product_Id,
		STK_T_Workflow.Parent_Flow,
		STK_T_Order_Detail.Split_From_Item_Id,
		STK_T_Order_Detail.Activity_Code,
		STK_T_Order_Detail.Inbound_Item_Id,
                STK_T_Workflow.Present_State,
                STK_T_Order_Detail.Active,
		TopParents.Flow_Id,
		TopParents.Parent_Flow,STK_T_Order.Doc_Refer_Int";

        //# End Comment 2013-08-22 #1815
        //***************************************************//
        //
        //$query = $this->clientDb->query($sql);
        $query = $this->db->query($sql);
        // echo $this->db->last_query();exit();
        $result = $query->result();
//       
//        p($result);exit();
//        $sql_reject = ";WITH TopParents AS (
//		    SELECT Flow_Id, Parent_Flow
//		    FROM STK_T_Workflow
//		    WHERE Parent_Flow IS NULL
//		    UNION ALL
//		    SELECT c.Flow_Id, ISNULL(p.Parent_Flow, p.Flow_Id) ParentCategoryID
//		    FROM STK_T_Workflow c
//		    JOIN TopParents p ON c.Parent_Flow = p.Flow_Id
//		)
//
//		SELECT
//		CONVERT(VARCHAR(20),STK_T_Order.Estimate_Action_Date, 103) as Estimate_Action_Date,
//                CONVERT(VARCHAR(20),STK_T_Order.Estimate_Action_Date, 120) as Estimate_Action_Date_sort,
//		STK_T_Order.Doc_Refer_Ext,
//		(Select Company_NameEN from CTL_M_Company where Company_Id = STK_T_Order.Source_Id) as supplier,
//		STK_T_Order_Detail.Product_Code,
//		STK_M_Product.Product_NameEN,
//		STK_T_Order_Detail.Reserv_Qty  as reserv_quan,
//		CONVERT(VARCHAR(20),STK_T_Order.Actual_Action_Date, 103) as Actual_Action_Date ,
//		CONVERT(VARCHAR(20),STK_T_Inbound.Unlock_Pending_Date, 103) as Pending_Date,
//		SUM(STK_T_Order_Detail.Confirm_Qty) as Reserv_Qty,
//		STK_T_Order_Detail.Inbound_Item_Id,
//		STK_T_Order_Detail.Confirm_Qty,
//                (SELECT Top 1 Remark
//            		FROM STK_T_Relocate_Detail
//            		WHERE Document_No = STK_T_Order.Document_No
//            		AND Product_Code = STK_T_Order_Detail.Product_Code
//            		AND Remark != '') AS trueRemark,
//		STK_T_Order_Detail.Remark,
//		TopParents.Flow_Id As FID,
//		TopParents.Parent_Flow As PID,
//		STK_T_Order.Flow_Id,
//		STK_T_Workflow.Parent_Flow,
//		SYS_M_Process.Process_NameEn,
//		SYS_M_State.State_NameEn,
//		STK_T_Order.Document_No,
//		STK_T_Order.Order_Id,
//		STK_T_Order_Detail.Product_Id,
//		STK_T_Order_Detail.Split_From_Item_Id,
//		STK_T_Order_Detail.Activity_Code,
//			CASE
//				WHEN TopParents.Parent_Flow Is Null THEN TopParents.Flow_Id
//				WHEN TopParents.Parent_Flow Is Not Null THEN TopParents.Parent_Flow
//				WHEN TopParents.Parent_Flow Is Not Null And STK_T_Order_Detail.Split_From_Item_Id Is Not Null THEN TopParents.Parent_Flow
//				WHEN TopParents.Parent_Flow Is Not Null And STK_T_Order_Detail.Split_From_Item_Id Is Null THEN TopParents.Flow_Id
//			END As SameDate,
//                'Y' as Is_reject
//		FROM STK_T_Workflow
//		INNER JOIN SYS_M_Process on STK_T_Workflow.Process_Id = SYS_M_Process.Process_Id
//		LEFT JOIN SYS_M_State on STK_T_Workflow.Process_Id = SYS_M_State.Process_Id and STK_T_Workflow.Present_State = SYS_M_State.State_No
//		INNER JOIN STK_T_Order on STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id
//		INNER JOIN STK_T_Order_Detail on STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id
//		INNER JOIN STK_M_Product on STK_T_Order_Detail.Product_Code = STK_M_Product.Product_Code
//		LEFT OUTER JOIN STK_T_Inbound on STK_T_Inbound.Document_No = STK_T_Order.Document_No and STK_T_Inbound.Active='Y' and STK_T_Inbound.Product_Code =STK_T_Order_Detail.Product_Code
//		INNER JOIN TopParents On TopParents.Flow_Id = STK_T_Workflow.Flow_Id
//		WHERE ((STK_T_Workflow.Process_Id =1 and STK_T_Workflow.Present_State in (-1))  ) " . $option . "
//		GROUP BY
//		STK_T_Order.Estimate_Action_Date,
//		STK_T_Order.Doc_Refer_Ext,
//		STK_T_Order.Source_Id,
//		STK_T_Order_Detail.Product_Code,
//		STK_M_Product.Product_NameEN,
//		STK_T_Order_Detail.Reserv_Qty,
//		STK_T_Order.Actual_Action_Date,
//		STK_T_Inbound.Unlock_Pending_Date,
//		STK_T_Order_Detail.Confirm_Qty,
//		STK_T_Order_Detail.Remark,
//		STK_T_Order.Flow_Id,
//		STK_T_Workflow.Flow_Id,
//		SYS_M_Process.Process_NameEn,
//		SYS_M_State.State_NameEn,
//		STK_T_Order.Document_No,
//		STK_T_Order.Order_Id,
//		STK_T_Order_Detail.Product_Id,
//		STK_T_Workflow.Parent_Flow,
//		STK_T_Order_Detail.Split_From_Item_Id,
//		STK_T_Order_Detail.Activity_Code,
//		STK_T_Order_Detail.Inbound_Item_Id,
//		TopParents.Flow_Id,
//		TopParents.Parent_Flow";
//
//        //# End Comment 2013-08-22 #1815
//        //***************************************************//
//        //
//        //$query = $this->clientDb->query($sql);
//        $query_reject = $this->db->query($sql_reject);
//        $result_reject = $query_reject->result();
//
////        p($result_reject);exit();
//
//        $result_tmp = array_merge($result, $result_reject);
//        $result_all = sort_arr_of_obj($result_tmp,'Estimate_Action_Date_sort','asc');
//        p($result_all);exit();
        $rows = array();
        $i = 0;
        foreach ($result as $row) :
            $rows[] = $row;
        endforeach;

        if (!$report_flag) :
            $rows = $this->getGroupItem($rows);
        else :
            $tmp = $rows;
            $rows = array();
            $rows['report_data'] = $tmp;
        endif;

        return $rows;
    }

    /**
     * re-allocate data by group of item
     * @date 20130911
     * @creator Ball
     * @param array $data
     * @return array
     */
    public function getGroupItem($data) {
        $temp_array = array();
        $split_index = 1;

        foreach ($data as $index => $value) {//p($value);exit();
            if (($value->Is_reject == 'N' && $value->Active == 'Y') or ( $value->Is_reject == 'Y')):

                //if (is_null($value->Split_From_Item_Id)) {
                //$string_index = $value->SameDate . $value->Actual_Action_Date . $value->Product_Id;
                $string_index = $value->SameDate . $value->Estimate_Action_Date . $value->Product_Id;

                /* } else {org.netbeans.modules.php.editor.nav.DeclarationFinderImpl$AlternativeLocationImpl@16b0153d
                  $string_index = $split_index . $value->SameDate . $value->Actual_Action_Date . $value->Product_Id;
                  $split_index++;
                  } */
                $temp_array[$string_index][] = $value;
            endif;
        }

        foreach ($temp_array as $index => $value) {
            $receive_quantity = 0;
            $reserv_quantity = 0;
            $grn_flag = false;
            $remark_data = '';
            //$max_quantity = $this->getMaxQuantity($value['0']->Order_Id, $value['0']->Product_Id);
            //print_r($max_quantity);
            foreach ($value as $index2 => $value2) {
//				$remark_data .= $value[$index2]->Remark . '<br/>';  // Comment By Akkarpol, 25/09/2013, คอมเม้นต์ทิ้งเพราะถ้าใส่แบบนี้ แล้ว Remark ไม่มีค่า มันก็ไม่จำเป็นต้อง <br> หรอก ไม่งั้นถ้าเกิดทำมาแล้วได้ข้อมูลมาเยอะๆ หลายๆ row มันจะกลายเป็น ขึ้นบรรทัดใหม่เอาเองไรแบบนั้น
                $remark_data .= ($value[$index2]->Remark == " " ? "" : ($value[$index2]->Remark == "" ? "" : $value[$index2]->Remark . '<br/>')); // Add By Akkarapol, 25/09/2013, เช็คว่า ถ้ามันมีค่า ก็ค่อยให้ขึ้นบรรทัดใหม่ แต่ถ้ามันไม่มีค่าก็ไม่ต้องใส่ค่าอะไรเข้าไป
                $receive_quantity += $value2->Confirm_Qty;

                if (substr($value[$index2]->Document_No, 0, 3) == "GRN") {
                    //$temp_array[$index]['reserv_quan'] = $max_quantity->quantity;
                    $max_quantity = $this->getMaxQuantity($value[$index2]->Order_Id, $value[$index2]->Product_Id);
                    $grn_flag = true;
                } else {
                    // if partial use own value
                    //$temp_array[$index]['reserv_quan'] = $value[$index2]->reserv_quan;
                    $reserv_quantity += $value[$index2]->reserv_quan;
                }
            }
            $temp_array[$index]['reserv_quan'] = ($grn_flag ? $max_quantity->quantity : $reserv_quantity);
            $temp_array[$index]['remark_data'] = $remark_data;
            $temp_array[$index]['receive_quantity'] = $receive_quantity;
        }

        //print_r($temp_array);
        //exit();
        return $temp_array;
    }

    public function getMaxQuantity($order_id, $product_id) {
        // First Select From InActive First
        $sql = "SELECT SUM(Reserv_Qty) as quantity FROM STK_T_Order_Detail WHERE Order_Id = '" . $order_id . "' And Product_Id = '" . $product_id . "' AND Active = 'N' ";  // change to use SUM
        $query = $this->db->query($sql);
        $resInActive = $query->row();


        // Check If Empty Select From Active
        //if ($result->quantity == 0) :
        $sql = "SELECT SUM(Reserv_Qty) as quantity FROM STK_T_Order_Detail WHERE Order_Id = '" . $order_id . "' And Product_Id = '" . $product_id . "' ";  // change to use SUM
        $query = $this->db->query($sql);
        $resAll = $query->row();
        //endif;
        // Change to  use query all item and cut out with criteria Active = 'N'
        // Ball

        $resAll->quantity = $resAll->quantity - $resInActive->quantity;

        return $resAll;
    }

    # daily dispaching report
    # add select Price_Per_Unit,Unit_Price_value,All_Price : by kik : 20140114
    # set defalut $limit_max = NULL

    function getDDR($search, $limit_start = 0, $limit_max = NULL) {
        $this->db->select("CONVERT(VARCHAR(20), r.Estimate_Action_Date, 103) AS Estimate_Action_Date
                ,r.Document_No
				,r.Doc_Refer_Int
				,r.Doc_Refer_Ext
				,(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=r.Destination_Id) AS consignee
				,(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=r.Source_Id) AS supplier
				,d.Product_Code
				,(SELECT Product_NameEN FROM STK_M_Product p WHERE p.Product_Id=d.Product_Id) AS Product_NameEN
				,d.Reserv_Qty, CONVERT(VARCHAR(20), r.Actual_Action_Date, 103) AS Actual_Action_Date
				,d.Confirm_Qty, d.Remark

                                , d.Cont_Id
                                , d.Invoice_Id
                                , CAST(CTL_M_Container.Cont_No AS VARCHAR(50)) + ' '+ CAST(CTL_M_Container_Size.Cont_Size_No AS VARCHAR(5))+ ' '+CTL_M_Container_Size.Cont_Size_Unit_Code AS Cont
                                , STK_T_Invoice.Invoice_No
                ");

        #add for ISSUE 3302 : by kik :20140114
        $this->db->select("d.Price_Per_Unit");
        $this->db->select("domain.Dom_EN_Desc AS Unit_Price_value");
        $this->db->select("d.All_Price");
        #end add for ISSUE 3302 : by kik :20140114
        #add for ISSUE 3323 : by kik :20140130
        $this->db->select("d.Pallet_Id_Out");
        $this->db->select("plt.Pallet_Code");
        #end add for ISSUE 3323 : by kik :20140130

        $this->db->join("STK_T_Order_Detail d", "d.Order_Id = r.Order_Id");
//                LEFT JOIN SYS_M_Domain S4 ON a.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code='PRICE_UNIT'
        $this->db->join("SYS_M_Domain domain", "d.Unit_Price_Id = domain.Dom_Code AND domain.Dom_Active = 'Y'", "LEFT");
        $this->db->join("STK_T_Pallet plt", "d.Pallet_Id_Out = plt.Pallet_Id", "LEFT");

        $this->db->join("CTL_M_Container", "d.Cont_Id = CTL_M_Container.Cont_Id", "LEFT");
        $this->db->join("CTL_M_Container_Size", "CTL_M_Container.Cont_Size_Id = CTL_M_Container_Size.Cont_Size_Id", "LEFT");
        $this->db->join("STK_T_Invoice", "d.Invoice_Id = STK_T_Invoice.Invoice_Id", "LEFT");

        $this->db->where("r.Process_Type", "OUTBOUND");
        $this->db->where("Actual_Action_Date IS NOT NULL");
        $this->db->where("d.Active", 'Y');
        $this->db->order_by("r.Estimate_Action_Date ASC,r.Destination_Id ASC");

        if (!empty($search['document_no'])) :
            $this->db->where("r.Document_No", $search['document_no']);
        endif;

        if ($search['fdate'] != "") :
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") :
                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
                $this->db->where("r.Estimate_Action_Date>='" . $from_date . "' ");
                $this->db->where("r.Estimate_Action_Date<='" . $to_date . " 23:59:59'");
            else :
                $this->db->where("r.Estimate_Action_Date>='" . $from_date . "'");
                $this->db->where("r.Estimate_Action_Date<='" . $from_date . " 23:59:59'");
            endif;
        endif;

        if (isset($search['sSearch'])) :
            $this->db->like("(SELECT Product_NameEN FROM STK_M_Product p WHERE p.Product_Id=d.Product_Id)", utf8_to_tis620($search['sSearch']));
            $this->db->or_like("r.Doc_Refer_Int", utf8_to_tis620($search['sSearch']));
            $this->db->or_like("r.Doc_Refer_Ext", utf8_to_tis620($search['sSearch']));
            $this->db->or_like("d.Product_Code", utf8_to_tis620($search['sSearch']));
        endif;

        #check for limit_max not null
        if (!empty($limit_max)):
            $this->db->limit($limit_max, $limit_start);
        endif;

        $query = $this->db->get("STK_T_Order r");
        $result = $query->result();
    //    p($this->db->last_query());exit();
        return $result;
    }

    function count_ddr_report($search) {
        $this->db->select("CONVERT(VARCHAR(20), r.Estimate_Action_Date, 103) AS Estimate_Action_Date
				,r.Doc_Refer_Int
				,r.Doc_Refer_Ext
				,(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=r.Destination_Id) AS consignee
				,(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=r.Source_Id) AS supplier
				,d.Product_Code
				,(SELECT Product_NameEN FROM STK_M_Product p WHERE p.Product_Id=d.Product_Id) AS Product_NameEN
				,d.Reserv_Qty, CONVERT(VARCHAR(20), r.Actual_Action_Date, 103) AS Actual_Action_Date
				,d.Confirm_Qty, d.Remark");
        $this->db->join("STK_T_Order_Detail d", "d.Order_Id = r.Order_Id");
        $this->db->where("r.Process_Type", "OUTBOUND");
        $this->db->where("Actual_Action_Date IS NOT NULL");
        $this->db->order_by("r.Estimate_Action_Date ASC,r.Destination_Id ASC");

        if (!empty($search['document_no'])) :
            $this->db->where("r.Document_No", $search['document_no']);
        endif;

        if ($search['fdate'] != "") :
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") :
                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
                $this->db->where("r.Estimate_Action_Date>='" . $from_date . "' ");
                $this->db->where("r.Estimate_Action_Date<='" . $to_date . " 23:59:59'");
            else :
                $this->db->where("r.Estimate_Action_Date>='" . $from_date . "'");
                $this->db->where("r.Estimate_Action_Date<='" . $from_date . " 23:59:59'");
            endif;
        endif;

        $query = $this->db->get("STK_T_Order r");
        $result = $query->result();
        return count($result);
    }

    function searchAging($search) {
        if ($search['as_date'] == "") {
            $compare_date = date("Y-m-d");
        } else {
            $compare_date = convertDate($search['as_date'], "eng", "iso", "-");
        }
        $age_range = $this->getAgeRange($search['by'], $search['period'], $search['step'], $search['product_id']);

        $sum_command = $this->getSqlSearchBalance_swa($search, $age_range);

        //if($search['product_id']==""){
        //$sql="SELECT DISTINCT(i.Product_Id),p.Product_Code,p.Product_NameEN,ProductCategory_Id
        $sql = "SELECT DISTINCT(i.Product_Id),p.Product_Code,p.Product_NameEN
				 " . $sum_command . "
						FROM STK_T_Inbound i
						LEFT JOIN STK_M_Location l ON Actual_Location_Id=l.Location_Id
						LEFT JOIN STK_M_Product p ON i.Product_Id=p.Product_Id
						WHERE
						   i.Active='Y'
				";
        $sql.=$this->searchCondition($search, $compare_date);
        /* }
          else{
          //$sql="SELECT DISTINCT(i.Product_Id),p.Product_Code,p.Product_NameEN,ProductCategory_Id
          $sql="SELECT DISTINCT(i.Product_Id),p.Product_Code,p.Product_NameEN
          ".$sum_command."
          FROM STK_T_Inbound i
          LEFT JOIN STK_M_Location l ON Actual_Location_Id=l.Location_Id
          LEFT JOIN STK_M_Product p ON i.Product_Id=p.Product_Id
          WHERE
          i.Active='Y'
          AND i.Product_Id=".$search['product_id']."
          ";
          }
         */
        //$sql.=" GROUP BY i.Product_Id,p.Product_Code,p.Product_NameEN,ProductCategory_Id ORDER BY i.Product_Id";
        $sql.=" GROUP BY i.Product_Id,p.Product_Code,p.Product_NameEN ORDER BY i.Product_Id";
        //echo '<br> sql = '.$sql;
        $query = $this->db->query($sql);
        return $query->result();
        /*
          $result=array();
          foreach($query->result() as $row){
          $r=array();
          $r['Product_Id']=$row->Product_Id;
          $r['Product_Code']=$row->Product_Code;
          $r['Product_Name']=$row->Product_NameEN;
          $i=0;
          foreach($age_range as $key => $value){
          $r['count'][$i]=$this->countProductExpired($row->Product_Id,$search['as_date'],$value);
          $i++;
          }
          $result[]=$r;
          }
          //p($result);

          return $result;
         */
    }

    function getAgeRange($by, $period, $step, $criteria = NULL) {
        $range['N/A'] = array(0, 0);
        $range["<=0"] = array(0, -1);
        $this->db->select("MAX(DATEDIFF(DAY,Receive_Date, GETDATE())) as exp_day");
        $this->db->from("STK_T_Inbound");
        $this->db->where("Active", ACTIVE);
        if ($criteria != "") {
            $this->db->where("Product_Id", $criteria);
        }
        //echo $this->db->last_query();
        $query = $this->db->get();
        $result = $query->result();
        //echo ' max day = '.$result[0]->exp_day;
        $max_day = $result[0]->exp_day;

        /* if($period==""){
          if($by=="YEAR"){
          $step=round(($max_day/365)/5,0);
          $devise=365;
          }
          else if($by=="MONTH"){
          $step=round(($max_day/30)/5,0);
          $devise=30;
          }
          else{
          $step=round($max_day/5,0);
          $devise=1;
          }
          $period=round($result[0]->exp_day/$devise,0);
          }
          else{
          if($step==""){
          $step=round($period/5,0);
          }
          }
         */

        if ($by == "YEAR") {
            //$step=round(($max_day/365)/5,0);
            $devise = 365;
        } else if ($by == "MONTH") {
            //$step=round(($max_day/30)/5,0);
            $devise = 30;
        } else {
            //$step=round($max_day/5,0);
            $devise = 1;
        }

        if ($step == "") {
            if ($max_day > 0) {
                $step = ceil(($max_day / $devise) / 5);
            } else {
                $step = 1;
            }
        }

        $period = ceil($result[0]->exp_day / $devise);
        //echo $by.' period = '.$period.' / step = '.$step;
        //$number_step=ceil($period/$step);
        $mod = $period % $step;
        $nubmer_step = ceil($period / $step);
        //echo '<br> number of step = '.$nubmer_step;
        for ($i = 1; $i <= $nubmer_step; $i++) {
            //echo '<br> i = '.$i;
            if ($i == 1) {
                //$range["0-".$number_step]=array(0,$number_step);
                $range["1-" . $step] = array(1, $step);
            } else {
                //$start=($number_step*($i-1))+1;
                $start = ($step * ($i - 1)) + 1;
                //echo ' <br> - start = '.$start;
                /*
                  if($mod!=0 && $i==$step){
                  $range[">=".$start]=array($start,0);
                  }
                  else{
                  //$stop=$i * $number_step;
                  $stop=$i*$step;
                  $range[$start."-".$stop]=array($start,$stop);
                  }
                 */
                $stop = $i * $step;
                $range[$start . "-" . $stop] = array($start, $stop);
            }
        }
        //p($range);
        return $range;
    }

//        --#ISSUE 2236 / #Defect 299,329,333 aging report
//        --#DATE:2012-09-02
//        --#BY:KIK
//        --#เธ�เธฑเธ�เธซเธฒ:1. เน�เธชเธ”เธ�เธ�เน�เธญเธกเธนเธฅเน�เธกเน�เธ–เธนเธ�เธ•เน�เธญเธ�เน€เธกเธทเน�เธญเน�เธชเน� remain step เน�เธฅเธฐ Remain By 2.เธ•เน�เธญเธ�เน�เธชเธ”เธ�เธ�เน�เธญเธกเธนเธฅเน€เธกเธทเน�เธญเน�เธ”เน�เธฃเธฑเธ�เธ�เธฒเธฃ receive approved
//        --#เธชเธฒเน€เธซเธ•เธธ:เน€เธ�เธตเธขเธ� sql เน€เธ�เธทเน�เธญ query เธ�เน�เธญเธกเธนเธฅเน�เธ”เน�เน�เธกเน�เธชเธกเธ�เธนเธฃเธ“เน� เน�เธฅเธฐเธ�เธดเธ” step
//        --#เธงเธดเธ�เธตเธ�เธฒเธฃเน�เธ�เน�:เน€เธ�เธตเธขเธ� sql เน�เธซเธกเน�เธ—เธฑเน�เธ�เธซเธกเธ” เน�เธ”เธขเธ”เธถเธ�เน�เธ�เธฃเธ�เธชเธฃเน�เธฒเธ�เธกเธฒเธ�เธฒเธ�เธ�เธญเธ�เน€เธ”เธดเธก เน€เธ�เธทเน�เธญเน�เธซเน�เน�เธ�เน�เธ�เธฒเธ�เน�เธ”เน�เธญเธขเน�เธฒเธ�เธ–เธนเธ�เธ•เน�เธญเธ�
//
//
//        -- START New Comment Code #ISSUE 2236 / #Defect 299,329,333 aging report

    function searchAgingReceiveApp($search) {
        if ($search['as_date'] == "") {
            $compare_date = date("Y-m-d");
        } else {
            $compare_date = convertDate($search['as_date'], "eng", "iso", "-");
        }


        //$age_range = $this->getAgeRange($search['by'], $search['period'], $search['step']);
        $age_range = $this->getAgeRange($search['by'], $search['period'], $search['step'], $search['product_id']);

        $sum_command = $this->get_range_receive($search, $age_range);

        $sql = "SELECT
                            DISTINCT(STK_T_Inbound.Product_Id)
                            , STK_T_Inbound.Product_Code
                            , STK_M_Product.Product_NameEN
                            " . $sum_command . "
                        FROM
                            STK_T_Inbound
                            INNER JOIN STK_M_Product ON STK_T_Inbound.Product_Id = STK_M_Product.Product_Id
                            LEFT JOIN STK_M_Location ON STK_T_Inbound.Actual_Location_Id=STK_M_Location.Location_Id

                        WHERE STK_T_Inbound.Active='Y' ";

        $sql.=$this->searchCondition($search, $compare_date);

        $sql.=" GROUP BY
                            STK_T_Inbound.Product_Id
                            ,STK_T_Inbound.Product_Code
                            ,STK_M_Product.Product_NameEN
                        ORDER BY STK_T_Inbound.Product_Id ";

        $query = $this->db->query($sql);
//        echo $this->db->last_query();exit();
        return $query->result();
    }

    function search_aging_detail($search) {

        if ($search['as_date'] == "") {
            $compare_date = date("Y-m-d");
        } else {
            $compare_date = convertDate($search['as_date'], "eng", "iso", "-");
        }

        $age_range = $this->getAgeRange($search['by'], $search['period'], $search['step'], $search['product_id']);
        $sum_command = $this->get_range_receive($search, $age_range);

        //Edit by por 2013-10-10 แก้ไข Zone_Id ให้เรียกให้ถูกต้อง ซึ่งเดิมเรียกจาก STK_M_Location แต่ตอนนี้ได้ปรับให้อยู่ใน STK_M_Putaway แล้ว จึงต้องเปลี่ยนวิธีเรียกใหม่
        $sql = "SELECT
                            DISTINCT(STK_T_Inbound.Product_Id)
                            , STK_T_Inbound.Product_Code
                            , STK_M_Product.Product_NameEN
                            , STK_T_Inbound.Product_Lot
                            , STK_T_Inbound.Product_Serial
                            , STK_T_Inbound.Product_Status
                            ,(SELECT Warehouse_Code FROM STK_M_Warehouse WHERE STK_M_Warehouse.Warehouse_Id=STK_M_Location.Warehouse_Id) AS Warehouse
                            ,(SELECT Zone_Code FROM STK_M_Zone WHERE STK_M_Zone.Zone_Id=STK_M_Putaway.Zone_Id) AS Zone
                            , STK_M_Location.Location_Code
                            " . $sum_command . "
                        FROM
                            STK_T_Inbound
                            INNER JOIN STK_M_Product ON STK_T_Inbound.Product_Id = STK_M_Product.Product_Id
                            LEFT JOIN STK_M_Location ON STK_T_Inbound.Actual_Location_Id=STK_M_Location.Location_Id
                            LEFT JOIN STK_M_Putaway ON STK_M_Location.Putaway_Id=STK_M_Putaway.Id
                        WHERE STK_T_Inbound.Active='Y' ";

        $sql.=$this->searchCondition($search, $compare_date);

        $sql.=" GROUP BY
                            STK_T_Inbound.Product_Id
                            ,STK_T_Inbound.Product_Code
                            ,STK_M_Product.Product_NameEN
                            ,STK_T_Inbound.Product_Lot
                            ,STK_T_Inbound.Product_Serial
                            ,STK_T_Inbound.Product_Status
                            ,STK_M_Location.Warehouse_Id
                            ,STK_M_Putaway.Zone_Id
                            ,STK_M_Location.Location_Code
                        ORDER BY STK_T_Inbound.Product_Id ";

        $query = $this->db->query($sql);
        //return $query->result();

        $result = array();
        foreach ($query->result() as $row) {
            $r = array();
            $r['Product_Code'] = $row->Product_Code;
            $r['Product_NameEN'] = $row->Product_NameEN;
            $r['Product_Status'] = $row->Product_Status;
            $r['Product_Lot'] = $row->Product_Lot;
            $r['Product_Serial'] = $row->Product_Serial;
            $r['Warehouse'] = $row->Warehouse;
            $r['Zone'] = $row->Zone;
            $r['Location_Code'] = $row->Location_Code;

            $i = 1;
            foreach ($age_range as $key => $value) {
                $r['count_' . $i] = $row->{'counts_' . $i};
                $i++;
            }
            $result[$row->Product_Code . ' ' . $row->Product_NameEN][] = $r;
        }
        return $result;
    }

    function getSqlSearchReceiveApp($search, $rang) {
        $condition = '';
        $i = 1;
        foreach ($rang as $key => $value) {
            $condition.=$this->sqlProductExpiredReceiveApp($search['by'], $search['as_date'], $value, $i);
            $i++;
        }
        return $condition;
    }

    function sqlProductExpiredReceiveApp($by, $compare_date, $age, $index) {
        if ($compare_date == "") {
            $compare_date = date("Y-m-d");
        } else {
            $compare_date = convertDate($compare_date, "eng", "iso", "-");
        }
        $sql_option = '';
        if ($by == "YEAR") {
            $number = 365;
        } else if ($by == "MONTH") {
            $number = 30;
        } else {
            $number = 1;
        }



        if ($age[0] == "0" && $age[1] == "0") {
            $sql_option = ", SUM(CASE WHEN STK_T_Inbound.Product_Exp IS NULL THEN (isnull(STK_T_Inbound.Receive_Qty,0) - (isnull(STK_T_Inbound.Dispatch_Qty,0) - isnull(STK_T_Inbound.Adjust_Qty,0))) ELSE 0 END) AS counts_" . $index . "";
        } else if ($age[0] == -1 && $age[1] != 0) {
            $limit = $age[1] * $number;
            $sql_option = ", SUM(CASE WHEN DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp)>" . $limit . "
						  THEN (isnull(STK_T_Inbound.Receive_Qty,0)-isnull(STK_T_Inbound.Dispatch_Qty,0) - isnull(STK_T_Inbound.Adjust_Qty,0)) ELSE 0 END) AS counts_" . $index;
        } else if ($age[0] == 0 && $age[1] == -1) {

            $sql_option = ", SUM(CASE WHEN DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp)<=0
						  THEN (isnull(STK_T_Inbound.Receive_Qty,0)-isnull(STK_T_Inbound.Dispatch_Qty,0) - isnull(STK_T_Inbound.Adjust_Qty,0)) ELSE 0 END) AS counts_" . $index;
            //echo $sql_option . " <br/>";
        } else if ($age[0] != 0 && $age[1] == "0") {
            $limit = $age[0] * $number;
            $sql_option = ", SUM(CASE WHEN DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp)<=" . $limit . "
						  THEN (isnull(STK_T_Inbound.Receive_Qty,0)-isnull(STK_T_Inbound.Dispatch_Qty,0) - isnull(STK_T_Inbound.Adjust_Qty,0)) ELSE 0 END) AS counts_" . $index;
        } else {
            $limit = (($age[0] - 1) * $number) + 1;
            if ($age[0] == $age[1]) {
                if ($by == "MONTH") {
                    $limit2 = ($age[1]) * $number;
                } else {
                    $limit2 = ($age[1] + 1) * $number;
                }
                //Ball
                //echo $limit . " - " . $limit2 . "<br/>";

                $sql_option = ", SUM(CASE WHEN DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp)>=" . $limit . "
							AND  DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp)<" . $limit2 . "
							THEN (isnull(STK_T_Inbound.Receive_Qty,0)-isnull(STK_T_Inbound.Dispatch_Qty,0) - isnull(STK_T_Inbound.Adjust_Qty,0)) ELSE 0 END) AS counts_" . $index;
            } else {
                $limit2 = $age[1] * $number;
                $sql_option = ", SUM(CASE WHEN DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp)>=" . $limit . "
							AND  DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp)<=" . $limit2 . "
							THEN (isnull(STK_T_Inbound.Receive_Qty,0)-isnull(STK_T_Inbound.Dispatch_Qty,0) -  isnull(STK_T_Inbound.Adjust_Qty,0)) ELSE 0 END) AS counts_" . $index;
            }
        }
        return $sql_option;
    }

    /**
     * get sql for aging range
     * @param unknown $search
     * @param unknown $rang
     * @return string
     */
    function get_range_receive($search, $rang) {
        $condition = '';
        $i = 1;
        foreach ($rang as $key => $value) {
            $condition.=$this->gen_range_receive($search['by'], $search['as_date'], $value, $i);
            $i++;
        }
        return $condition;
    }

    /**
     * generate sql for aging report
     * @param varchar $by
     * @param date $compare_date
     * @param int $age
     * @param int $index
     * @return string (sql)
     */
    function gen_range_receive($by, $compare_date, $age, $index) {

        if ($compare_date == "") {
            $compare_date = date("Y-m-d");
        } else {
            $compare_date = convertDate($compare_date, "eng", "iso", "-");
        }

        $sql_option = '';

        if ($by == "YEAR") {
            $number = 365;
        } else if ($by == "MONTH") {
            $number = 30;
        } else {
            $number = 1;
        }

        if ($age[0] == "0" && $age[1] == "0") {
            $sql_option = ", SUM(CASE WHEN STK_T_Inbound.Receive_Date IS NULL THEN (isnull(STK_T_Inbound.Receive_Qty,0) - (isnull(STK_T_Inbound.Dispatch_Qty,0) - isnull(STK_T_Inbound.Adjust_Qty,0))) ELSE 0 END) AS counts_" . $index . "";
        } else if ($age[0] == -1 && $age[1] != 0) {
            $limit = $age[1] * $number;
            $sql_option = ", SUM(CASE WHEN ABS(DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Receive_Date))>" . $limit . "
						  THEN (isnull(STK_T_Inbound.Receive_Qty,0)-isnull(STK_T_Inbound.Dispatch_Qty,0) - isnull(STK_T_Inbound.Adjust_Qty,0)) ELSE 0 END) AS counts_" . $index;
        } else if ($age[0] == 0 && $age[1] == -1) {

            $sql_option = ", SUM(CASE WHEN ABS(DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Receive_Date))<=0
						  THEN (isnull(STK_T_Inbound.Receive_Qty,0)-isnull(STK_T_Inbound.Dispatch_Qty,0) - isnull(STK_T_Inbound.Adjust_Qty,0)) ELSE 0 END) AS counts_" . $index;
            //echo $sql_option . " <br/>";
        } else if ($age[0] != 0 && $age[1] == "0") {
            $limit = $age[0] * $number;
            $sql_option = ", SUM(CASE WHEN ABS(DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Receive_Date))<=" . $limit . "
						  THEN (isnull(STK_T_Inbound.Receive_Qty,0)-isnull(STK_T_Inbound.Dispatch_Qty,0) - isnull(STK_T_Inbound.Adjust_Qty,0)) ELSE 0 END) AS counts_" . $index;
        } else {
            $limit = (($age[0] - 1) * $number) + 1;
            if ($age[0] == $age[1]) {
                if ($by == "MONTH") {
                    $limit2 = ($age[1]) * $number;
                } else {
                    $limit2 = ($age[1] + 1) * $number;
                }
                //Ball
                //echo $limit . " - " . $limit2 . "<br/>";

                $sql_option = ", SUM(CASE WHEN ABS(DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Receive_Date))>=" . $limit . "
							AND  ABS(DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Receive_Date))<" . $limit2 . "
							THEN (isnull(STK_T_Inbound.Receive_Qty,0)-isnull(STK_T_Inbound.Dispatch_Qty,0) - isnull(STK_T_Inbound.Adjust_Qty,0)) ELSE 0 END) AS counts_" . $index;
            } else {
                $limit2 = $age[1] * $number;
                $sql_option = ", SUM(CASE WHEN ABS(DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Receive_Date))>=" . $limit . "
							AND  ABS(DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Receive_Date))<=" . $limit2 . "
							THEN (isnull(STK_T_Inbound.Receive_Qty,0)-isnull(STK_T_Inbound.Dispatch_Qty,0) -  isnull(STK_T_Inbound.Adjust_Qty,0)) ELSE 0 END) AS counts_" . $index;
            }
        }
        return $sql_option;
    }

    function show_expire($limit_flag = TRUE, $search, $limit_start = 0, $limit_max = 100, $export = FALSE) {

        if (empty($search['as_date'])) :
            $compare_date = date("Y-m-d");
        else :
            $compare_date = convertDate($search['as_date'], "eng", "iso", "-");
        endif;

        // Add By Ball
        $select_export = "";
        $group_by_export = "";
        if ($export) {
            $select_export = ",(SELECT Warehouse_Code FROM STK_M_Warehouse WHERE STK_M_Warehouse.Warehouse_Id=STK_M_Location.Warehouse_Id) AS Warehouse
        		,(SELECT Zone_Code FROM STK_M_Zone WHERE STK_M_Zone.Zone_Id=STK_M_Putaway.Zone_Id) AS Zone
				,STK_M_Location.Location_Code";

            $group_by_export = ",STK_M_Location.Warehouse_Id,STK_M_Location.Location_Code,STK_M_Putaway.Zone_Id";
        }

        $this->db->select("DISTINCT STK_T_Inbound.Product_Id
				,STK_T_Inbound.Product_Code
				,STK_T_Inbound.Product_Lot
				,STK_T_Inbound.Product_Serial
				" . $select_export . "
				,(SELECT Product_NameEN FROM STK_M_Product WHERE STK_M_Product.Product_Id=STK_T_Inbound.Product_Id) AS Product_NameEN
				,CONVERT(VARCHAR(20), STK_T_Inbound.Product_Exp, 103) AS Product_Exp
				,DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp) As Remain_day
				,SUM(STK_T_Inbound.Receive_Qty - STK_T_Inbound.Dispatch_Qty - STK_T_Inbound.Adjust_Qty) As Balance

                                ,STK_T_Inbound.Pallet_Id
                                ,STK_T_Pallet.Pallet_Code

                        ");
        $this->db->join("STK_M_Location", "STK_T_Inbound.Actual_Location_Id=STK_M_Location.Location_Id", "LEFT");

        // Add By Akkarapol, 03/02/2014, เพิ่มการ join เข้าไปที่ STK_T_Pallet เพื่อเอา Pallet_Code มาใช้งาน
        $this->db->join("STK_T_Pallet", "STK_T_Inbound.Pallet_Id = STK_T_Pallet.Pallet_Id", 'LEFT');
        // END Add By Akkarapol, 03/02/2014, เพิ่มการ join เข้าไปที่ STK_T_Pallet เพื่อเอา Pallet_Code มาใช้งาน

        $this->db->join("STK_M_Putaway", "STK_M_Location.Putaway_Id=STK_M_Putaway.Id", 'LEFT');

        if ($search['category_id'] != "") :
            $this->db->join("STK_M_Product", "STK_T_Inbound.Product_Id=STK_M_Product.Product_Id");
        endif;

        $this->db->where("STK_T_Inbound.Active", "Y");

        if ($search['renter_id'] != "") :
            $this->db->where("STK_T_Inbound.Renter_Id", $search['renter_id']);
        endif;

        if ($search['warehouse_id'] != "") :
            $this->db->where("STK_M_Location.Warehouse_Id", $search['warehouse_id']);
        endif;

        if ($search['category_id'] != "") :
            $this->db->where("STK_M_Product.ProductCategory_Id", $search['category_id']);
        endif;

        if ($search['product_id'] != "") :
            $this->db->where("STK_T_Inbound.Product_Id", $search['product_id']);
        endif;

        if ($search['sSearch'] != "") :
            $this->db->like("(SELECT Product_NameEN FROM STK_M_Product WHERE STK_M_Product.Product_Id=STK_T_Inbound.Product_Id)", utf8_to_tis620($search['sSearch']));
            $this->db->or_like("STK_T_Inbound.Product_Code", utf8_to_tis620($search['sSearch']));
            $this->db->or_like("STK_T_Inbound.Product_Lot", utf8_to_tis620($search['sSearch']));
            $this->db->or_like("STK_T_Inbound.Product_Serial", utf8_to_tis620($search['sSearch']));
            $this->db->or_like("CONVERT(VARCHAR(20), STK_T_Inbound.Product_Exp, 103)", utf8_to_tis620($search['sSearch']));
        //$this->db->or("DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp)", utf8_to_tis620($search['sSearch']));
        //$this->db->having("SUM(STK_T_Inbound.Receive_Qty - STK_T_Inbound.Dispatch_Qty - STK_T_Inbound.Adjust_Qty) = '" . utf8_to_tis620($search['sSearch']) . "'");
        endif;

        if (array_key_exists('remain_day', $search) && $search['remain_day'] != "") :
            $this->db->where(" DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp)<=" . $search['remain_day'] . "");
        endif;

        $this->db->group_by("STK_T_Inbound.Product_Id,STK_T_Inbound.Product_Code,STK_T_Inbound.Product_Lot,STK_T_Inbound.Product_Serial,STK_T_Inbound.Product_Exp,DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp),STK_T_Inbound.Pallet_Id,STK_T_Pallet.Pallet_Code" . $group_by_export);
        $this->db->having("SUM(STK_T_Inbound.Receive_Qty - STK_T_Inbound.Dispatch_Qty - STK_T_Inbound.Adjust_Qty)<>0");  //Add by por 2014-03-19 เพิ่มให้เช็คแสดงเฉพาะค่าที่ไม่เท่ากับ 0
        $this->db->order_by("Product_Code , Product_Lot ,Product_Serial", "ASC");

        if ($limit_flag): //ADD BY POR 2014-03-20 If condition $limit_flag = TRUE use limit
            $this->db->limit($limit_max, $limit_start);
        endif;

        $query = $this->db->get("STK_T_Inbound");

        $result = $query->result();

//        echo $this->db->last_query();exit();
        return $result;
    }

    function count_expire_report($search) {

        if (empty($search['as_date'])) :
            $compare_date = date("Y-m-d");
        else :
            $compare_date = convertDate($search['as_date'], "eng", "iso", "-");
        endif;

        $this->db->select("STK_T_Inbound.Product_Id
				,STK_T_Inbound.Product_Code
				,STK_T_Inbound.Product_Lot
				,STK_T_Inbound.Product_Serial
				,(SELECT Product_NameEN FROM STK_M_Product WHERE STK_M_Product.Product_Id=STK_T_Inbound.Product_Id) AS Product_NameEN
				,CONVERT(VARCHAR(20), STK_T_Inbound.Product_Exp, 103) AS Product_Exp
				,DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp) As Remain_day
				,SUM(STK_T_Inbound.Receive_Qty - STK_T_Inbound.Dispatch_Qty - STK_T_Inbound.Adjust_Qty) As Balance");
        $this->db->join("STK_M_Location", "STK_T_Inbound.Actual_Location_Id=STK_M_Location.Location_Id", "LEFT");
        if ($search['category_id'] != "") :
            $this->db->join("STK_M_Product", "STK_T_Inbound.Product_Id=STK_M_Product.Product_Id");
        endif;

        $this->db->where("STK_T_Inbound.Active", "Y");

        if ($search['renter_id'] != "") :
            $this->db->where("STK_T_Inbound.Renter_Id", $search['renter_id']);
        endif;

        if ($search['warehouse_id'] != "") :
            $this->db->where("STK_M_Location.Warehouse_Id", $search['warehouse_id']);
        endif;

        if ($search['category_id'] != "") :
            $this->db->where("STK_M_Product.ProductCategory_Id", $search['category_id']);
        endif;

        if ($search['product_id'] != "") :
            $this->db->where("STK_T_Inbound.Product_Id", $search['product_id']);
        endif;

        if (array_key_exists('remain_day', $search) && $search['remain_day'] != "") :
            $this->db->where(" DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp)<=" . $search['remain_day'] . "");
        endif;

        $this->db->group_by("STK_T_Inbound.Product_Id,STK_T_Inbound.Product_Code,STK_T_Inbound.Product_Lot,STK_T_Inbound.Product_Serial,STK_T_Inbound.Product_Exp,DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp)");
        $this->db->having("SUM(STK_T_Inbound.Receive_Qty - STK_T_Inbound.Dispatch_Qty - STK_T_Inbound.Adjust_Qty)<>0");  //Add by por 2014-03-19 เพิ่มให้เช็คแสดงเฉพาะค่าที่ไม่เท่ากับ 0
        $this->db->order_by("Product_Code , Product_Lot ,Product_Serial", "ASC");
        $query = $this->db->get("STK_T_Inbound");
        $result = $query->result();
        return count($result);
    }

    function show_expire_detail($search) {
        /*
          $sqlJoinProduct = "";
          if ($search['as_date'] == "") {
          $compare_date = date("Y-m-d");
          } else {
          $compare_date = convertDate($search['as_date'], "eng", "iso", "-");
          }

          if ($search['category_id'] != "") {
          $sqlJoinProduct = "INNER JOIN  STK_M_Product on STK_T_Inbound.Product_Id=STK_M_Product.Product_Id";
          }

          //Edit by por 2013-10-10 แก้ไข Zone_Id ให้เรียกให้ถูกต้อง ซึ่งเดิมเรียกจาก STK_M_Location แต่ตอนนี้ได้ปรับให้อยู่ใน STK_M_Putaway แล้ว จึงต้องเปลี่ยนวิธีเรียกใหม่
          $sql = "SELECT
          STK_T_Inbound.Product_Id
          ,STK_T_Inbound.Product_Code
          ,STK_T_Inbound.Product_Lot
          ,STK_T_Inbound.Product_Serial
          ,(SELECT Warehouse_Code FROM STK_M_Warehouse WHERE STK_M_Warehouse.Warehouse_Id=STK_M_Location.Warehouse_Id) AS Warehouse
          ,(SELECT Zone_Code FROM STK_M_Zone WHERE STK_M_Zone.Zone_Id=STK_M_Putaway.Zone_Id) AS Zone
          ,STK_M_Location.Location_Code
          ,(SELECT Product_NameEN FROM STK_M_Product WHERE STK_M_Product.Product_Id=STK_T_Inbound.Product_Id) AS Product_NameEN
          ,CONVERT(VARCHAR(20), STK_T_Inbound.Product_Exp, 103) AS Product_Exp
          ,DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp) As Remain_day
          ,SUM(STK_T_Inbound.Receive_Qty - STK_T_Inbound.Dispatch_Qty - STK_T_Inbound.Adjust_Qty) As Balance

          ,STK_T_Inbound.Pallet_Id
          ,STK_T_Pallet.Pallet_Code

          ";
          $sql.=" FROM
          STK_T_Inbound
          LEFT JOIN  STK_M_Location on STK_T_Inbound.Actual_Location_Id=STK_M_Location.Location_Id
          LEFT JOIN STK_M_Putaway ON STK_M_Location.Putaway_Id=STK_M_Putaway.Id
          LEFT JOIN STK_T_Pallet ON STK_T_Inbound.Pallet_Id = STK_T_Pallet.Pallet_Id
          " . $sqlJoinProduct . " ";

          $sql.=" WHERE STK_T_Inbound.Active='Y' ";
          if (array_key_exists('remain_day', $search) && $search['remain_day'] != "") :
          $sql.= " AND DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp)<=" . $search['remain_day'] . "";
          endif;

          $sql.=$this->searchCondition($search, $compare_date);

          $sql.=" GROUP BY
          STK_T_Inbound.Product_Id
          ,STK_T_Inbound.Product_Code
          ,STK_T_Inbound.Product_Lot
          ,STK_T_Inbound.Product_Serial
          ,STK_M_Location.Warehouse_Id
          ,STK_M_Putaway.Zone_Id
          ,STK_M_Location.Location_Code
          ,STK_T_Inbound.Product_Exp
          ,DATEDIFF(DAY,'" . $compare_date . "',STK_T_Inbound.Product_Exp)
          ,STK_T_Inbound.Pallet_Id
          ,STK_T_Pallet.Pallet_Code
          ";
          $sql.=" ORDER BY Product_Code , Product_Lot ,Product_Serial ASC";

          $query = $this->db->query($sql);
         */
        $query = $this->show_expire(FALSE, $search, 0, 999999, TRUE);

        $result = array();
        foreach ($query as $rows) {
            $row = array();
            $row['Product_Id'] = $rows->Product_Id;
            $row['Product_Code'] = $rows->Product_Code;
            $row['Product_NameEN'] = $rows->Product_NameEN;
            $row['Product_Exp'] = $rows->Product_Exp;
            $row['Warehouse'] = $rows->Warehouse;
            $row['Zone'] = $rows->Zone;
            $row['Location_Code'] = $rows->Location_Code;
            $row['Product_Lot'] = ($rows->Product_Lot == "") ? '&nbsp;' : $rows->Product_Lot;
            $row['Product_Serial'] = ($rows->Product_Serial == "") ? '&nbsp;' : $rows->Product_Serial;
            $row['Remain_day'] = $rows->Remain_day;
            $row['Pallet_Code'] = $rows->Pallet_Code;
            $row['Balance'] = $rows->Balance;
            $result[$rows->Product_Code . ' ' . $rows->Product_NameEN][] = $row;
        }
//		p($result);exit();
        return $result;
    }

    function searchCondition($search, $compare_date) {
        $sql = '';
        if ($search['renter_id'] != "") {
            $sql.=" AND STK_T_Inbound.Renter_Id=" . $search['renter_id'];
        }

        if ($search['warehouse_id'] != "") {
            $sql.=" AND STK_M_Location.Warehouse_Id=" . $search['warehouse_id'];
        }

        if ($search['category_id'] != "") {
            $sql.=" AND STK_M_Product.ProductCategory_Id='" . $search['category_id'] . "'";
        }

        if ($search['product_id'] != "") {
            $sql.=" AND STK_T_Inbound.Product_Id='" . $search['product_id'] . "'";
        }

        if (array_key_exists('remain_day', $search) && $search['remain_day'] != "") {
            // Change to use receive_date for compare aging report
            $sql.=" AND DATEDIFF(DAY,STK_T_Inbound.Receive_Date,'" . $compare_date . "')<='" . $search['remain_day'] . "'";
        }
        return $sql;
    }

    function show_pt_location($search) {
        if ($search['activity'] == "Putaway") {
            //INBOUND
            $result = $this->show_location_putaway($search);
        } else if ($search['activity'] == "Picking") {
            //OUTBOUND
            $result = $this->show_location_picking($search);
        } else if ($search['activity'] == "Relocation") {
            //RE-LOCATE1 RE-LOCATE2 RE-LOCATE3 RE-LOCATE4
            $result = $this->show_location_relocation($search);
        } else if ($search['activity'] == "Actual") {
            //Actual Location
            $result = $this->show_location_actual($search);
        }

        //return $result;
        if (empty($result)) {
            return array();
        }
        $value = array();
        foreach ($result as $r) {
            $value[$r->Act_Date][$r->Document_No][] = $r;
        }
        return $value;
    }

    function show_pt_location_export($search) {
        if ($search['activity'] == "Putaway") {
            //INBOUND
            $result = $this->show_location_putaway($search);
        } else if ($search['activity'] == "Picking") {
            //OUTBOUND
            $result = $this->show_location_picking($search);
        } else if ($search['activity'] == "Relocation") {
            //RE-LOCATE1 RE-LOCATE2 RE-LOCATE3 RE-LOCATE4
            $result = $this->show_location_relocation($search);
        }


        if (count($result) == 0) {
            return array();
        }
        return $result;
        /*
          foreach($result as $r){
          $value[$r->Act_Date][$r->Document_No][]=$r;
          }
          return $value;
         */
    }

    #fix dupicate data in report : edit from o.Suggest_Location_Id to i.Suggest_Location_Id : by kik : 20140123

    function show_location_putaway($search) {
        //INBOUND
        #Comment 2013-08-30 By POR
        #1671 Comment case Remark is Reason_Code+Reason_Remark
        /* ---Start
          $sql="SELECT DISTINCT(o.Order_Id),i.Document_No
          ,CONVERT(VARCHAR(20), i.Putaway_Date, 103)  AS Act_Date,i.Putaway_Date
          ,i.Product_Id,i.Product_Code
          ,(SELECT Product_NameEN FROM STK_M_Product WHERE Product_Id=i.Product_Id) As Product_NameEN
          ,i.Product_Lot,i.Product_Serial,i.Product_Status
          ,i.Receive_Qty As qty,i.Unit_Id
          ,(SELECT Location_Code FROM STK_M_Location WHERE Location_Id=i.Suggest_Location_Id) as Suggest_Location_Id
          ,(SELECT Location_Code FROM STK_M_Location WHERE Location_Id=i.Actual_Location_Id) as Actual_Location_Id
          ,(SELECT (Reason_Code+' '+Reason_Remark) as Remark FROM STK_T_Order_Detail
          WHERE Order_Id=o.Order_Id
          AND Product_Id=i.Product_Id
          AND Product_Status=i.Product_Status
          AND Product_Lot=i.Product_Lot
          AND Product_Serial=i.Product_Serial
          ) AS Remark
          ,(SELECT (First_NameTH+' '+Last_NameTH) as name FROM CTL_M_Contact c,ADM_M_UserLogin u
          WHERE c.Contact_Id=u.Contact_Id
          AND u.UserLogin_Id=i.Putaway_By
          ) as Put_by
          FROM STK_T_Inbound i,STK_T_Order o
          WHERE i.Document_No=o.Document_No
          AND o.Process_Type='INBOUND'
          AND i.Active='".ACTIVE."'
          ";
          ----End---- */

        #1671 Change Remark from Reason_Code to Dom_EN_Desc
        #DATE : 2013-08-30
        #BY : POR
        #---------START
        $sql = "SELECT DISTINCT(o.Order_Id),i.Document_No
                    ,i.Doc_Refer_Int
					,CONVERT(VARCHAR(20), i.Putaway_Date, 103)  AS Act_Date,i.Putaway_Date
					,i.Product_Id,i.Product_Code
					,CONVERT(VARCHAR(20), i.Receive_Date, 103)  AS Receive_Date
					,CONVERT(VARCHAR(20), i.Product_Mfd, 103)  AS Product_Mfd
					,CONVERT(VARCHAR(20), i.Product_Exp, 103)  AS Product_Exp
					,i.Flow_Id
					,i.Doc_Refer_Ext
					,(SELECT DISTINCT Company_NameEN FROM CTL_M_Company WHERE Company_Id=i.Renter_Id) Renter_Id
					,(SELECT DISTINCT Product_NameEN FROM STK_M_Product WHERE Product_Id=i.Product_Id) As Product_NameEN
					,i.Product_Lot,i.Product_Serial,i.Product_Status
					,i.Receive_Qty As qty,i.Unit_Id
					,(SELECT DISTINCT Location_Code FROM STK_M_Location WHERE Location_Id=i.Suggest_Location_Id) as Suggest_Location_Id
					,(SELECT DISTINCT Location_Code FROM STK_M_Location WHERE Location_Id=i.Actual_Location_Id) as Actual_Location_Id
					,(SELECT DISTINCT TOP 1 (isnull(Dom_EN_Desc,'')+' '+ isnull(Reason_Remark,'')) -- Temp fix for select only one row
					FROM STK_T_Order_Detail a
					Left join SYS_M_Domain b on a.Reason_Code=b.Dom_Code
					WHERE a.Order_Id=o.Order_Id
					AND a.Product_Id=i.Product_Id
					AND a.Product_Status=i.Product_Status
					AND a.Product_Lot=i.Product_Lot
					AND a.Product_Serial=i.Product_Serial
					AND b.Dom_Host_Code='PA_REASON') AS Remark
					,(contact.First_NameTH+' '+contact.Last_NameTH) as Put_by

                                        , S1.public_name AS Unit_Value

                                        , i.Pallet_Id
                                        , STK_T_Pallet.Pallet_Code
                                        , i.Price_Per_Unit
                                        , SYS_M_Domain.Dom_EN_SDesc AS Unit_price
                                        , (Receive_Qty * i.Price_Per_Unit) AS All_price

					FROM STK_T_Inbound i
					INNER JOIN STK_T_Order o On i.Document_No=o.Document_No
					INNER JOIN STK_T_Order_Detail d On d.Order_Id = o.Order_Id AND d.Product_Id = i.Product_Id AND d.Active='" . ACTIVE . "'
                                        LEFT JOIN STK_T_Logs_Action log_act On log_act.Order_Id = o.Order_Id AND log_act.Item_Id = d.Item_Id AND log_act.Edge_Id = 8
                                        LEFT JOIN ADM_M_UserLogin usr_login ON usr_login.UserLogin_Id = log_act.Activity_By
                                        LEFT JOIN CTL_M_Contact contact ON contact.Contact_Id = usr_login.Contact_Id
                                        LEFT JOIN CTL_M_UOM_Template_Language S1 ON i.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'

                                        LEFT JOIN STK_T_Pallet ON i.Pallet_Id = STK_T_Pallet.Pallet_Id
                                        LEFT JOIN SYS_M_Domain ON i.Unit_Price_Id = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code ='PRICE_UNIT' AND SYS_M_Domain.Dom_Active = 'Y'
					WHERE o.Process_Type='INBOUND'
					AND i.Active='" . ACTIVE . "' AND i.Receive_Qty <> 0
			";
        #---------END
        if ($search['product_id'] != "") {
            $sql.=" AND i.Product_Id=" . $search['product_id'];
        }

        if ($search['fdate'] != "") {
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") {
                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
                $sql.=" AND i.Putaway_Date>='" . $from_date . "' AND i.Putaway_Date<='" . $to_date . " 23:59:59'";
            } else {
                $sql.=" AND i.Putaway_Date>='" . $from_date . "' AND i.Putaway_Date<='" . $from_date . " 23:59:59'";
            }
        }

        if ($search['doc_value'] != "") {
            $sql.=" AND o." . $search['doc_type'] . " LIKE '%" . $this->db->escape_like_str(trim($search['doc_value'])) . "%'";
        }

        // BALL - Add check state after confirm by -2
        $sql .= 'AND (SELECT Present_State From STK_T_Workflow Where STK_T_Workflow.Flow_Id = i.Flow_Id) = -2';

        $sql.=" ORDER BY i.Putaway_Date ASC,i.Document_No ASC";

        // echo '<br> sql = '.$sql;exit;
        $query = $this->db->query($sql);
    //    echo $this->db->last_query();exit();
        $result = $query->result();

        return $result;
    }

    function show_location_picking($search) {
        //OUTBOUND
        //Start Again by kik : 20141004  for โดยเปลี่ยนจากการ check Edge_id เป็น Sub_module และ Module แทน
        $sql = "SELECT DISTINCT(o.Order_Id),o.Document_No,o.Doc_Refer_Int
					,CONVERT(VARCHAR(20), d.Activity_Date, 103) AS Act_Date, d.Activity_Date
					,d.Product_Id,d.Product_Code
					,CONVERT(VARCHAR(20), (SELECT CASE o.Create_Date WHEN '' THEN o.Modified_By ELSE o.Create_Date END as Receive_Date), 103)  AS Receive_Date
					,CONVERT(VARCHAR(20), d.Product_Mfd, 103)  AS Product_Mfd
					,CONVERT(VARCHAR(20), d.Product_Exp, 103)  AS Product_Exp
					,CONVERT(VARCHAR(20), o.Create_Date, 103)  AS Receive_Date
					,o.Flow_Id
					,o.Doc_Refer_Ext
					,(SELECT Company_NameEN FROM CTL_M_Company WHERE Company_Id=o.Renter_Id) Renter_Id,(SELECT Product_NameEN FROM STK_M_Product WHERE Product_Id=d.Product_Id) As Product_NameEN
					,d.Product_Status
					,d.Product_Lot,d.Product_Serial
					,d.Confirm_Qty AS qty,d.Unit_Id
					,(SELECT Top 1 Location_Code FROM STK_M_Location WHERE Location_Id=d.Suggest_Location_Id) as Suggest_Location_Id
					,(SELECT Top 1 Location_Code FROM STK_M_Location WHERE Location_Id=d.Actual_Location_Id) as Actual_Location_Id
					,(isnull(p.Dom_EN_Desc,'')+' '+isnull(d.Reason_Remark,'')) AS Remark
					,d.Activity_Date

                                        , S1.public_name AS Unit_Value

                                        , d.Pallet_Id
                                        , STK_T_Pallet.Pallet_Code
                                        , d.Price_Per_Unit
                                        , SYS_M_Domain.Dom_EN_SDesc AS Unit_price
                                        , (Confirm_Qty * d.Price_Per_Unit) AS All_price
                                        ,(select top 1 (contact.First_NameTH+' '+contact.Last_NameTH)
                                            FROM STK_T_Logs_Action log_act
                                            --LEFT JOIN STK_T_Logs_Action log_act On log_act.Order_Id = o.Order_Id AND log_act.Item_Id = d.Item_Id AND log_act.Edge_Id = 8
                                            LEFT JOIN ADM_M_UserLogin usr_login ON usr_login.UserLogin_Id = log_act.Activity_By
                                            LEFT JOIN CTL_M_Contact contact ON contact.Contact_Id = usr_login.Contact_Id
                                            where d.Item_Id = log_act.Item_Id and log_act.module = 'picking'
                                            order by log_act.Id desc
                                        ) as Put_by
				FROM STK_T_Order_Detail d
                                LEFT JOIN STK_T_Order o ON d.Order_Id=o.Order_Id
                                INNER JOIN STK_T_Workflow wf ON o.Flow_Id=wf.Flow_Id
                                LEFT JOIN SYS_M_Domain p ON d.Reason_Code=p.Dom_Code AND p.Dom_Host_Code='PK_REASON'
                                LEFT JOIN CTL_M_UOM_Template_Language S1 ON d.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'

                                LEFT JOIN STK_T_Pallet ON d.Pallet_Id = STK_T_Pallet.Pallet_Id
                                LEFT JOIN SYS_M_Domain ON d.Unit_Price_Id = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code ='PRICE_UNIT' AND SYS_M_Domain.Dom_Active = 'Y'
                                WHERE	o.Process_Type='OUTBOUND'
					AND d.Active='" . ACTIVE . "' AND d.Confirm_Qty <> 0
					AND (UPPER(d.Activity_Code)='PICKING' OR UPPER(d.Activity_Code)='DISPATCH')
					AND d.Actual_Location_Id IS NOT NULL
			";
        //end Again by kik : 20141004  for โดยเปลี่ยนจากการ check Edge_id เป็น Sub_module และ Module แทน

        if ($search['product_id'] != "") {
            $sql.=" AND d.Product_Id=" . $search['product_id'];
        }

        if ($search['fdate'] != "") {
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") {
                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
                $sql.=" AND d.Activity_Date>='" . $from_date . "' AND d.Activity_Date<='" . $to_date . " 23:59:59'";
            } else {
                $sql.=" AND d.Activity_Date>='" . $from_date . "' AND d.Activity_Date<='" . $from_date . " 23:59:59'";
            }
        }

        if ($search['doc_value'] != "") {
            $sql.=" AND o." . $search['doc_type'] . " LIKE '%" . $this->db->escape_like_str(trim($search['doc_value'])) . "%'";
        }

        $sql.=" ORDER BY d.Activity_Date ASC,o.Document_No ASC";
        $query = $this->db->query($sql);
        $result = $query->result();
        //echo $this->db->last_query(); exit;
        return $result;
    }

    function show_location_relocation($search) {
        //RE-LOCATE1 RE-LOCATE2 RE-LOCATE3 RE-LOCATE4
        #Comment 2013-08-30 By POR
        #1671 Comment case Remark is Reason_Code+Reason_Remark
        /* ---Start
          $sql="SELECT DISTINCT(d.Order_Id),i.Document_No
          ,CONVERT(VARCHAR(20), i.Putaway_Date, 103)  AS Act_Date,i.Putaway_Date
          ,i.Product_Id
          ,i.Product_Code
          ,(SELECT Product_NameEN FROM STK_M_Product WHERE Product_Id=i.Product_Id) As Product_NameEN
          ,i.Product_Lot,i.Product_Serial,i.Product_Status
          ,i.Receive_Qty As qty,i.Unit_Id
          ,(SELECT Location_Code FROM STK_M_Location WHERE Location_Id=i.Suggest_Location_Id) as Suggest_Location_Id
          ,(SELECT Location_Code FROM STK_M_Location WHERE Location_Id=i.Actual_Location_Id) as Actual_Location_Id
          ,(d.Reason_Code+' '+d.Reason_Remark) as Remark
          ,(SELECT (First_NameTH+' '+Last_NameTH) as name FROM CTL_M_Contact c,ADM_M_UserLogin u
          WHERE c.Contact_Id=u.Contact_Id
          AND u.UserLogin_Id=i.Putaway_By
          ) as Put_by
          FROM STK_T_Inbound i,STK_T_Relocate_Detail d
          WHERE i.History_Item_Id=d.Inbound_Item_Id
          AND i.Active='".ACTIVE."'
          ";
          ------End */


        //Start Comment by kik : 20141004 โดยเปลี่ยนจากการ check Edge_id เป็น Sub_module และ Module แทน เ
        #1671 Change Remark from Reason_Code to Dom_EN_Desc
        #DATE : 2013-08-30
        #BY : POR
        #---------START
//        $sql = "SELECT DISTINCT(d.Order_Id),i.Document_No
//					,CONVERT(VARCHAR(20), i.Putaway_Date, 103)  AS Act_Date,i.Putaway_Date
//					,i.Product_Id
//					,i.Product_Code
//					,i.Flow_Id
//					,i.Doc_Refer_Ext
//					,CONVERT(VARCHAR(20), i.Receive_Date, 103)  AS Receive_Date
//					,CONVERT(VARCHAR(20), i.Product_Mfd, 103)  AS Product_Mfd
//					,CONVERT(VARCHAR(20), i.Product_Exp, 103)  AS Product_Exp
//					,(SELECT DISTINCT Company_NameEN FROM CTL_M_Company WHERE Company_Id=i.Renter_Id) Renter_Id
//					,(SELECT DISTINCT Product_NameEN FROM STK_M_Product WHERE Product_Id=i.Product_Id) As Product_NameEN
//					,i.Product_Lot,i.Product_Serial,i.Product_Status
//					,i.Receive_Qty As qty,i.Unit_Id
//					,(SELECT DISTINCT Location_Code FROM STK_M_Location WHERE Location_Id=i.Suggest_Location_Id) as Suggest_Location_Id
//					,(SELECT DISTINCT Location_Code FROM STK_M_Location WHERE Location_Id=i.Actual_Location_Id) as Actual_Location_Id
//					,(isnull(p.Dom_EN_Desc,'')+' '+isnull(d.Remark,'')) as Remark
//					,(activity_by.First_NameTH+' '+activity_by.Last_NameTH) as Put_by
//                                        , S1.public_name AS Unit_Value
//
//                                        , i.Pallet_Id
//                                        , STK_T_Pallet.Pallet_Code
//
//                                        , (SELECT Distinct Location_Code FROM STK_M_Location WHERE Location_ID = i2.Actual_Location_Id) as history_location
//                                        , i.Price_Per_Unit
//                                        , SYS_M_Domain.Dom_EN_SDesc AS Unit_price
//                                        , (i.Receive_Qty * i.Price_Per_Unit) AS All_price
//				FROM STK_T_Inbound i
//                                INNER JOIN STK_T_Relocate_Detail d ON i.History_Item_Id=d.Inbound_Item_Id
//                                LEFT JOIN SYS_M_Domain p ON d.Reason_Code=p.Dom_Code AND p.Dom_Host_Code='PA_REASON'
//                                LEFT JOIN vw_activity_by activity_by On activity_by.Item_Id=d.Item_Id AND activity_by.Edge_Id in (28,32,52,129)
//                                LEFT JOIN STK_T_Pallet ON i.Pallet_Id = STK_T_Pallet.Pallet_Id
//
//                                LEFT JOIN CTL_M_UOM_Template_Language S1 ON i.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'
//
//                                LEFT OUTER JOIN STK_T_Inbound i2
//                                ON i.History_Item_Id = i2.Inbound_Id
//
//                                LEFT JOIN SYS_M_Domain ON i.Unit_Price_Id = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code ='PRICE_UNIT'
//
//				WHERE  i.Active='" . ACTIVE . "' AND i.Receive_Qty <> 0
//			";
//        #-----------END
//        #
        //End  Comment by kik : 20141004 โดยเปลี่ยนจากการ check Edge_id เป็น Sub_module และ Module แทน
        //Start Again by kik : 20141004  for โดยเปลี่ยนจากการ check Edge_id เป็น Sub_module และ Module แทน
        $sql = "SELECT
                        i.Document_No
                        ,i.Doc_Refer_Int
                        ,CONVERT(VARCHAR(20), i.Putaway_Date, 103)  AS Act_Date,i.Putaway_Date
                        ,i.Product_Id
                        ,i.Product_Code
                        ,i.Flow_Id
                        ,i.Doc_Refer_Ext
                        ,CONVERT(VARCHAR(20), i.Receive_Date, 103)  AS Receive_Date
                        ,CONVERT(VARCHAR(20), i.Product_Mfd, 103)  AS Product_Mfd
                        ,CONVERT(VARCHAR(20), i.Product_Exp, 103)  AS Product_Exp
                        ,(SELECT DISTINCT Company_NameEN FROM CTL_M_Company WHERE Company_Id=i.Renter_Id) Renter_Id
                        ,(SELECT DISTINCT Product_NameEN FROM STK_M_Product WHERE Product_Id=i.Product_Id) As Product_NameEN
                        ,i.Product_Lot,i.Product_Serial,i.Product_Status
                        ,i.Receive_Qty As qty,i.Unit_Id
                        ,(SELECT DISTINCT Location_Code FROM STK_M_Location WHERE Location_Id=i.Suggest_Location_Id) as Suggest_Location_Id
                        ,(SELECT DISTINCT Location_Code FROM STK_M_Location WHERE Location_Id=i.Actual_Location_Id) as Actual_Location_Id
                        ,(contact.First_NameTH+' '+contact.Last_NameTH) as Put_by
                        , S1.public_name AS Unit_Value
                        , i.Pallet_Id
                        , STK_T_Pallet.Pallet_Code
                        , (SELECT Distinct Location_Code FROM STK_M_Location WHERE Location_ID = i2.Actual_Location_Id) as history_location
                        , i.Price_Per_Unit
                        , SYS_M_Domain.Dom_EN_SDesc AS Unit_price
                        , (i.Receive_Qty * i.Price_Per_Unit) AS All_price
                        , (SELECT Distinct TOP 1 (isnull(SYS_M_Domain.Dom_EN_Desc,'')+' '+isnull(STK_T_Relocate_Detail.Remark,'')) -- Temp fix
                            FROM STK_T_Relocate_Detail
                            LEFT JOIN SYS_M_Domain on STK_T_Relocate_Detail.Reason_Code=SYS_M_Domain.Dom_Code
                            AND SYS_M_Domain.Dom_Host_Code='PA_REASON' AND SYS_M_Domain.Dom_Active = 'Y'
                            WHERE i.History_Item_Id=STK_T_Relocate_Detail.Inbound_Item_Id ) as Remark
                FROM STK_T_Inbound i
                INNER JOIN STK_T_Workflow wf ON i.Flow_Id=wf.Flow_Id
                LEFT JOIN STK_T_Logs_Action log_act On log_act.Item_Id=i.Item_Id and
                    CASE
                       WHEN wf.Process_Id IN (5) AND log_act.Module = 'reLocation' AND log_act.Sub_Module = 'confirmReLocation'  THEN 1
                       WHEN wf.Process_Id IN (6) AND log_act.Module = 'relocate'AND log_act.Sub_Module = 'hhConfirmAction'  THEN 1
                       WHEN wf.Process_Id IN (10) AND log_act.Module = 'reLocationProduct' AND log_act.Sub_Module = 'confirmRLProduct' THEN 1
                       ELSE 0
                       END = 1
                LEFT JOIN ADM_M_UserLogin usr_login ON usr_login.UserLogin_Id = log_act.Activity_By
                LEFT JOIN CTL_M_Contact contact ON contact.Contact_Id = usr_login.Contact_Id
                LEFT JOIN STK_T_Pallet ON i.Pallet_Id = STK_T_Pallet.Pallet_Id
                LEFT JOIN CTL_M_UOM_Template_Language S1 ON i.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'

                LEFT OUTER JOIN STK_T_Inbound i2
                ON i.History_Item_Id = i2.Inbound_Id

                LEFT JOIN SYS_M_Domain ON i.Unit_Price_Id = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code ='PRICE_UNIT' AND SYS_M_Domain.Dom_Active = 'Y'

                WHERE  i.Active='" . ACTIVE . "' AND i.Receive_Qty <> 0
        ";
        //End Add by kik : 20141004  for โดยเปลี่ยนจากการ check Edge_id เป็น Sub_module และ Module แทน
        //AND o.Process_Type='INBOUND'
        if ($search['product_id'] != "") {
            $sql.=" AND i.Product_Id=" . $search['product_id'];
        }

        if ($search['fdate'] != "") {
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") {
                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
                $sql.=" AND i.Putaway_Date>='" . $from_date . "' AND i.Putaway_Date<='" . $to_date . " 23:59:59'";
            } else {
                $sql.=" AND i.Putaway_Date>='" . $from_date . "' AND i.Putaway_Date<='" . $from_date . " 23:59:59'";
            }
        }

        if ($search['doc_value'] != "") {
            $sql.=" AND i." . $search['doc_type'] . " LIKE '%" . $this->db->escape_like_str(trim($search['doc_value'])) . "%'";
        }

        // BALL - Add check state after confirm by -2
        $sql .= 'AND (SELECT Present_State From STK_T_Workflow Where STK_T_Workflow.Flow_Id = i.Flow_Id) = -2';
        $sql .= 'AND (SELECT Process_Id From STK_T_Workflow Where STK_T_Workflow.Flow_Id = i.Flow_Id) != 9';
        // END

        $sql.=" ORDER BY i.Putaway_Date ASC,i.Document_No ASC";
//        echo $sql;
        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }

    /**
     * search actual
     * @param array $search
     * @return result object
     */
    public function show_location_actual($search) {
        $sql = "SELECT DISTINCT(o.Order_Id),i.Document_No,i.Doc_Refer_Int
					,CONVERT(VARCHAR(20), i.Putaway_Date, 103)  AS Act_Date,i.Putaway_Date
					,i.Product_Id,i.Product_Code
					,CONVERT(VARCHAR(20), i.Receive_Date, 103)  AS Receive_Date
					,CONVERT(VARCHAR(20), i.Product_Mfd, 103)  AS Product_Mfd
					,CONVERT(VARCHAR(20), i.Product_Exp, 103)  AS Product_Exp
					,i.Flow_Id
					,i.Doc_Refer_Ext
					,(SELECT DISTINCT Company_NameEN FROM CTL_M_Company WHERE Company_Id=i.Renter_Id) Renter_Id
					,(SELECT DISTINCT Product_NameEN FROM STK_M_Product WHERE Product_Id=i.Product_Id) As Product_NameEN
					,i.Product_Lot,i.Product_Serial,i.Product_Status
					,i.Receive_Qty As qty,i.Unit_Id
					,(SELECT DISTINCT Location_Code FROM STK_M_Location WHERE Location_Id=i.Suggest_Location_Id) as Suggest_Location_Id
					,(SELECT DISTINCT Location_Code FROM STK_M_Location WHERE Location_Id=i.Actual_Location_Id) as Actual_Location_Id
					,(SELECT DISTINCT (isnull(Dom_EN_Desc,'')+' '+ isnull(Reason_Remark,''))
					FROM STK_T_Order_Detail a
					Left join SYS_M_Domain b on a.Reason_Code=b.Dom_Code
					WHERE a.Order_Id=o.Order_Id
					AND a.Product_Id=i.Product_Id
					AND a.Product_Status=i.Product_Status
					AND a.Product_Lot=i.Product_Lot
					AND a.Product_Serial=i.Product_Serial
					AND b.Dom_Host_Code='PA_REASON') AS Remark
					,(SELECT (First_NameTH+' '+Last_NameTH) as name FROM CTL_M_Contact c,ADM_M_UserLogin u
					WHERE c.Contact_Id=u.Contact_Id
					AND u.UserLogin_Id=i.Putaway_By
					) as Put_by

                                        , S1.public_name AS Unit_Value
                                        , i.Price_Per_Unit
                                        , SYS_M_Domain.Dom_EN_SDesc AS Unit_price
                                        , (Receive_Qty * i.Price_Per_Unit) AS All_price
					FROM STK_T_Inbound i
					INNER JOIN STK_T_Order o On i.Document_No=o.Document_No

                                        LEFT JOIN CTL_M_UOM_Template_Language S1 ON i.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'
                                        LEFT JOIN SYS_M_Domain ON i.Unit_Price_Id = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code ='PRICE_UNIT' AND SYS_M_Domain.Dom_Active = 'Y'
					WHERE o.Process_Type='INBOUND'
					AND i.Active='" . ACTIVE . "' i.Receive_Qty <> 0
			";
        #---------END
        if ($search['product_id'] != "") {
            $sql.=" AND i.Product_Id=" . $search['product_id'];
        }

        if ($search['fdate'] != "") {
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") {
                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
                $sql.=" AND i.Putaway_Date>='" . $from_date . "' AND i.Putaway_Date<='" . $to_date . " 23:59:59'";
            } else {
                $sql.=" AND i.Putaway_Date>='" . $from_date . "' AND i.Putaway_Date<='" . $from_date . " 23:59:59'";
            }
        }

        if ($search['doc_value'] != "") {
            $sql.=" AND o." . $search['doc_type'] . " LIKE '%" . $this->db->escape_like_str(trim($search['doc_value'])) . "%'";
        }

        // BALL - Add check state after confirm by -2
        $sql .= 'AND (SELECT Present_State From STK_T_Workflow Where STK_T_Workflow.Flow_Id = i.Flow_Id) = -2';

        $sql.=" ORDER BY i.Putaway_Date ASC,i.Document_No ASC";

        $query = $this->db->query($sql);

        $result = $query->result();

        return $result;
    }

    function search_dispatch($search, $type = "") {
        $type_of_date = 'Dispatch_Date';
        if ($search['type_dp_date_val'] == 'real_dp_date'):
            $type_of_date = 'Real_Action_Date';
        endif;
        $sql = "
            select (o.Document_No) ,o.Doc_Refer_Ext , o.Doc_Refer_Int ,o.Product_Code
        	,r.Remark as Doc_Remark
            ,o.Product_Mfd
            ,(SELECT Product_NameEN FROM STK_M_Product WHERE Product_Id=o.Product_Id) Product_Name
            ,o.Product_Status ,o.Product_Lot ,o.Product_Serial ,o.Unit_Id ,sum(o.Dispatch_Qty) Dispatch_Qty
            ,CONVERT(VARCHAR(20), o." . $type_of_date . ", 103) as Dispatch_Date
            ,(SELECT Company_NameEN FROM CTL_M_Company WHERE Company_Id=o.Renter_Id) From_sup
	        ,(SELECT Company_NameEN FROM CTL_M_Company WHERE Company_Id=r.Destination_Id) To_sup
            ,isnull((Select TOP 1 CAST(stuff((Select case t.Remark WHEN '' THEN '' WHEN NULL THEN '' ELSE ',' + t.Remark END from STK_T_Order_Detail t where t.Order_Id=r.Order_Id AND t.Product_Id=o.Product_Id AND t.Inbound_Item_Id=o.Inbound_Item_Id order by Item_Id for xml path('')),1,1,'') as nvarchar(1000) ) as myRemark from STK_T_Order_Detail ),'') Remark
            ,S1.public_name AS Unit_Value
            ,o.Pallet_Id
            ,STK_T_Pallet.Pallet_Code
	        , pd.Cubic_Meters * sum(o.Dispatch_Qty) As CBM
            ,r.Destination_Code
            ,r.Destination_Name
            from STK_T_Outbound o
            left join STK_T_Order r on o.Document_No=r.Document_No
            LEFT JOIN CTL_M_UOM_Template_Language S1 ON o.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'
            LEFT JOIN STK_T_Pallet ON o.Pallet_Id_Out = STK_T_Pallet.Pallet_Id
	    LEFT JOIN STK_M_Product pd ON pd.Product_Id = o.Product_Id
            where 1=1
        ";
        //END
        if ($search['fdate'] != "") {
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") {
                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
                $sql.=" AND o." . $type_of_date . ">='" . $from_date . "' AND o." . $type_of_date . "<='" . $to_date . " 23:59:59'";
            } else {
                $sql.=" AND o." . $type_of_date . ">='" . $from_date . "' AND o." . $type_of_date . "<='" . $from_date . " 23:59:59' ";
            }
        }

        if ($search['doc_value'] != "") {
            $sql.=" AND o." . $search['doc_type'] . " LIKE '%" . $this->db->escape_like_str($search['doc_value']) . "%' ";
        }

        //START BY POR 2013-10-09 query ข้อมูลโดยถ้า Document ใดมีหลาย record จะแสดงเพียง record เดียว และ remark จะนำมารวมกันเป็นรายการเดียว โดยใช้ comma คั่น
        $sql.="group by
            (o.Document_No) ,o.Doc_Refer_Ext , o.Doc_Refer_Int ,o.Product_Code
            ,o.Product_Id
            ,o.Product_Status ,o.Product_Lot ,o.Product_Serial ,o.Unit_Id
            ,CONVERT(VARCHAR(20), o." . $type_of_date . ", 103)
            ,o.Owner_Id
            ,o.Renter_Id
            ,o.Product_Mfd
            ,r.Order_Id
	    ,r.Destination_Id
            ,o.Inbound_Item_Id
            ,S1.public_name
            ,o.Pallet_Id
            ,STK_T_Pallet.Pallet_Code
            ,r.Remark
            ,r.Destination_Code
            ,r.Destination_Name
            ,r.Destination_Id
	        ,pd.Cubic_Meters
            ";
            $sql.= "order by  CONVERT(VARCHAR(20), o.Dispatch_Date, 103),o.Doc_Refer_Ext,o.Product_Code,o.Product_Lot,o.Product_Mfd ASC";
        //END
        //echo '<br> sql = '.$sql;
        $query = $this->db->query($sql);
        // echo $this->db->last_query(); exit;
        if ($type == 'PDF') {
            $result = array();
            $row = array();
            $dataRows = array();
            foreach ($query->result() as $rows) {

                $disdate = $rows->Dispatch_Date; //วันที่ที่ต้องการให้แสดงเป็น head หลัก

                if (isset($disdate, $dataRows[$rows->Dispatch_Date][$disdate])) {
                    $keyTmp = sizeof($dataRows[$rows->Dispatch_Date][$disdate]);
                } else {
                    $keyTmp = 0;
                }

                $row['Document_No'] = $rows->Document_No;
                $row['Doc_Refer_Ext'] = $rows->Doc_Refer_Ext;
                $row['Doc_Refer_Int'] = $rows->Doc_Refer_Int;
                $row['Product_Code'] = $rows->Product_Code;
                $row['Product_Name'] = $rows->Product_Name;
                $row['Product_Lot'] = $rows->Product_Lot;
                $row['Product_Mfd'] = $rows->Product_Mfd;
                $row['Product_Serial'] = $rows->Product_Serial;
                $row['Dispatch_Qty'] = $rows->Dispatch_Qty;
                $row['Unit_Id'] = $rows->Unit_Id;
                $row['Unit_Value'] = $rows->Unit_Value; // Add By Akkarapol, 13/01/2013, เพิ่มการเซ็ตค่า Unit_Value ให้กับตัวแปร เพื่อนำไปแสดงผล
                $row['From_sup'] = $rows->From_sup;
                $row['To_sup'] = $rows->To_sup;
                $row['Pallet_Code'] = $rows->Pallet_Code;
                $row['Remark'] = $rows->Remark;
		        $row['CBM'] = $rows->CBM;
		        $row['Destination_Code'] = $rows->Destination_Code;
		        $row['Destination_Name'] = $rows->Destination_Name;

                $dataRows[$rows->Dispatch_Date][$disdate][$keyTmp] = $row;
                unset($row);
                unset($disdate);
            }
            $result = $dataRows;
        } else {
            $result = $query->result();
        }

        return $result;
    }

    function locationHistoryReport($param) {
        $this->load->helper('date');
        if ($param["product_id"] != "") {
            $product_id = $param["product_id"];
        } else {
            $product_id = "''";
        }
        if ($param["doc_type"] != "") {
            $doc_type = $param["doc_type"];
        } else {
            $doc_type = "'%%'";
        }
        if ($param["serial"] != "") {
            $serial = "'" . $param["serial"] . "'";
        } else {
            $serial = "'%%'";
        }
        if ($param["ref_value"] != "") {
            $ref_value = $param["ref_value"];
        } else {
            $ref_value = "'%%'";
        }
        if ($param["date_from"] != "") {
            $date_from = "'" . $param["date_from"] . "'";
        } else {
            $date_from = "''";
        }
        if ($param["current_location"] != "") {
            $current_location = "'" . $param["current_location"] . "'";
        } else {
            $current_location = "'%%'";
        }
        if ($param["lot"] != "") {
            $lot = "'" . $param["lot"] . "'";
        } else {
            $lot = "'%%'";
        }
        //echo $product_id;
        $str = "EXEC showLocationHistoryReport " . $product_id . "," . $current_location . "," . $lot
                . "," . $serial . "," . $ref_value . "," . $doc_type . "," . $date_from;
        // echo ">>>".$str;
        $query = $this->db->query($str);
        //    p($this->db->last_query()); exit;
        //    $query=$this->db->get();
                //    p($this->db->last_query()); exit;
        $result = $query->result();
        return $result; //[0]->count_product;
    }

    function locationHistoryReportSWA($param) {

        $this->load->helper('date');
        $product_id = ($param["product_id"] != "" ? $param["product_id"] : "''");
        $doc_type = ($param["doc_type"] != "" ? $param["doc_type"] : "'%%'");
        $serial = ($param["serial"] != "" ? "'" . $param["serial"] . "'" : "'%%'");
        $ref_value = ($param["ref_value"] != "" ? $param["ref_value"] : "'%%'");
        $date_from = ($param["date_from"] != "" ? "'" . $param["date_from"] . "'" : "''");
        $current_location = ($param["current_location"] != "" ? "'" . $param["current_location"] . "'" : "'%%'");
        $lot = ($param["lot"] != "" ? "'" . $param["lot"] . "'" : "'%%'");

        //echo $product_id;
        $str = "EXEC showLocationHistoryReport " . $product_id . "," . $current_location . "," . $lot
                . "," . $serial . "," . $ref_value . "," . $doc_type . "," . $date_from;
        //echo ">>>".$str;
        $query = $this->db->query($str);
        //            echo $this->db->last_query();
        //            $query=$this->db->get();
        $result = $query->result();
        return $result; //[0]->count_product;
    }

    function showLocationAll() {
        //$location_code=strip_tags($location_code);
        $this->db->select("Location_Id,Location_Code");
        $this->db->from("STK_M_Location l");
        $this->db->join("STK_M_Storage_Detail s", "l.Storage_Detail_Id=s.Storage_Detail_Id", "left");
        //$this->db->where("Location_Code!=",$location_code);
        //$this->db->where("l.Active",ACTIVE);
        //$this->db->where("Is_Full",'N');
        $sug_query = $this->db->get();
        $sug_result = $sug_query->result();
        //p($sug_result);
        return $sug_result;
    }

    //=================Inventory Report====================
    function count_inventory($search) {
        $status_range = $this->getStatusRange($search['status_id']);
        $sql = "SELECT STK_T_Onhand_History.Product_Code
        		,Product_NameEN
        		,STK_T_Onhand_History.Product_Status
        		,STK_T_Onhand_History.Product_Lot
        		,STK_T_Onhand_History.Product_Serial
        		,convert(varchar(10),STK_T_Onhand_History.Product_Mfd,103) Product_Mfd
        		,convert(varchar(10),STK_T_Onhand_History.Product_Exp,103) Product_Exp
			FROM STK_T_Onhand_History
			LEFT JOIN STK_M_Location ON STK_T_Onhand_History.Actual_Location_Id=STK_M_Location.Location_Id
			LEFT JOIN STK_M_Product ON STK_T_Onhand_History.Product_Id=STK_M_Product.Product_Id
			WHERE 1=1";
        $sql .=$this->searchConditionInventory($search, $status_range);
        $sql .=" GROUP BY STK_T_Onhand_History.Product_Code,Product_NameEN,STK_T_Onhand_History.Product_Status,STK_T_Onhand_History.Product_Lot,STK_T_Onhand_History.Product_Serial,STK_T_Onhand_History.Product_Mfd,STK_T_Onhand_History.Product_Exp";
        $query = $this->db->query($sql);
      
        return count($query->result());
    }

    //By Por 2013-09-20 inventory report
    //======START
    function searchInventory($search, $limit_start = 0, $limit_max = 100) {
        //แสดง header ในส่วน Product Status
        $status_range = $this->getStatusRange($search['status_id']);

        //แสดง จำนวน balance
        $sum_balance = $this->getSqlSearchBalance($status_range);

        // BALL
        //แสดง จำนวน column balance
        $column_balance = $this->get_column_balance($status_range);

//        Start comment : by kik : 2013-11-14
//
//        $sql = "SELECT STK_T_Onhand_History.Product_Code
//        		,Product_NameEN
//        		,STK_T_Onhand_History.Product_Status
//        		,STK_T_Onhand_History.Product_Lot
//        		,STK_T_Onhand_History.Product_Serial
//        		,convert(varchar(10),STK_T_Onhand_History.Product_Mfd,103) Product_Mfd
//        		,convert(varchar(10),STK_T_Onhand_History.Product_Exp,103) Product_Exp " . $sum_balance . "
//        		FROM STK_T_Onhand_History
//			LEFT JOIN STK_M_Location ON STK_T_Onhand_History.Actual_Location_Id=STK_M_Location.Location_Id
//			LEFT JOIN STK_M_Product ON STK_T_Onhand_History.Product_Id=STK_M_Product.Product_Id
//			WHERE 1=1";
//        $sql .=$this->searchConditionInventory($search, $status_range);
//        $sql .=" GROUP BY STK_T_Onhand_History.Product_Code,Product_NameEN,STK_T_Onhand_History.Product_Status,STK_T_Onhand_History.Product_Lot,STK_T_Onhand_History.Product_Serial,STK_T_Onhand_History.Product_Mfd,STK_T_Onhand_History.Product_Exp";
        //$sql .= " WHERE Results.RowNumber Between ".$limit_start." AND ".$limit_max;
//        $query = $this->db->query($sql);
//
//       end comment : by kik : 2013-11-14
//       Start new modify code : by kik : 2013-11-14
        //
        $this->db->select('STK_T_Onhand_History.Product_Code');
        $this->db->select('Product_NameEN');
//        $this->db->select('STK_T_Onhand_History.Product_Status');
        $this->db->select('STK_T_Onhand_History.Product_Lot');
        $this->db->select('STK_T_Onhand_History.Product_Serial');
        $this->db->select('convert(varchar(10),STK_T_Onhand_History.Product_Mfd,103) Product_Mfd');
        $this->db->select('convert(varchar(10),STK_T_Onhand_History.Product_Exp,103) Product_Exp');

        $this->db->select('STK_T_Pallet.Pallet_Code');

        $this->db->select('sum(Balance_Qty) as totalbal');
        $this->db->select($sum_balance);

        $this->db->from('STK_T_Onhand_History');
        $this->db->join('STK_M_Location', 'STK_T_Onhand_History.Actual_Location_Id=STK_M_Location.Location_Id', 'LEFT');
        $this->db->join('STK_M_Product', 'STK_T_Onhand_History.Product_Id=STK_M_Product.Product_Id', 'LEFT');

        // Add By Akkarapol, 03/02/2014, เพิ่มการ join เข้าไปที่ STK_T_Pallet เพื่อเอา Pallet_Code มาใช้งาน
        $this->db->join("STK_T_Pallet", "STK_T_Onhand_History.Pallet_Id = STK_T_Pallet.Pallet_Id", 'LEFT');
        // END Add By Akkarapol, 03/02/2014, เพิ่มการ join เข้าไปที่ STK_T_Pallet เพื่อเอา Pallet_Code มาใช้งาน

        $this->db->where('1', 1);
        $this->db->where($this->searchConditionInventory($search, $status_range));
        $this->db->group_by(array('STK_T_Onhand_History.Product_Code', 'Product_NameEN', 'STK_T_Onhand_History.Product_Lot', 'STK_T_Onhand_History.Product_Serial', 'STK_T_Onhand_History.Product_Mfd', 'STK_T_Onhand_History.Product_Exp', 'STK_T_Pallet.Pallet_Code'));
        $this->db->having("sum(Balance_Qty)<>0"); //ADD BY POR 2014-03-12 ให้แสดงเฉพาะ balance มากกว่า 0

        $query = $this->db->get();
//     end new modify code : by kik : 2013-11-14
	//p($this->db->last_query()); exit;
        return $query->result();
    }

    function searchInventoryToday($search, $limit_start = 0, $limit_max = 100) {
        //แสดง header ในส่วน Product Status
        $status_range = $this->getStatusRange($search['status_id']);

        //แสดง จำนวน balance
        $sum_balance = $this->getSqlSearchBalance($status_range, 'STK_T_Inbound');

        // BALL
        //แสดง จำนวน column balance
//        $column_balance = $this->get_column_balance($status_range);

        $this->db->select('STK_T_Inbound.Product_Code');
        $this->db->select('Product_NameEN');
//        $this->db->select('Product_Status');
        $this->db->select('STK_T_Inbound.Product_Lot');
        $this->db->select('STK_T_Inbound.Product_Serial');
        $this->db->select('convert(varchar(10),STK_T_Inbound.Product_Mfd,103) Product_Mfd');
        $this->db->select('convert(varchar(10),STK_T_Inbound.Product_Exp,103) Product_Exp');

        $this->db->select('STK_T_Pallet.Pallet_Code');

        $this->db->select('sum(Balance_Qty) as totalbal');
        $this->db->select($sum_balance);

        $this->db->from('STK_T_Inbound');
        $this->db->join('STK_M_Location', 'STK_T_Inbound.Actual_Location_Id=STK_M_Location.Location_Id', 'LEFT');
        $this->db->join('STK_M_Product', 'STK_T_Inbound.Product_Id=STK_M_Product.Product_Id', 'LEFT');

        // Add By Akkarapol, 03/02/2014, เพิ่มการ join เข้าไปที่ STK_T_Pallet เพื่อเอา Pallet_Code มาใช้งาน
        $this->db->join("STK_T_Pallet", "STK_T_Inbound.Pallet_Id = STK_T_Pallet.Pallet_Id", 'LEFT');
        // END Add By Akkarapol, 03/02/2014, เพิ่มการ join เข้าไปที่ STK_T_Pallet เพื่อเอา Pallet_Code มาใช้งาน

        $this->db->where('STK_T_Inbound.Active', ACTIVE);
        $this->db->where($this->searchConditionInventory($search, $status_range, 'STK_T_Inbound'));

        $this->db->group_by(array('STK_T_Inbound.Product_Code', 'Product_NameEN', 'STK_T_Inbound.Product_Lot', 'STK_T_Inbound.Product_Serial', 'STK_T_Inbound.Product_Mfd', 'STK_T_Inbound.Product_Exp', 'STK_T_Pallet.Pallet_Code'));
        $this->db->having("sum(Balance_Qty)<>0"); //ADD BY POR 2014-03-12 ให้แสดงเฉพาะ balance มากกว่า 0
        $query = $this->db->get();
        //echo $this->db->last_query(); exit();
        return $query->result();
    }

    //สำหรับหาสถานะสินค้าที่สนใจ สำหรับเป็น header
    function getStatusRange($status_id) {
        // p($status_id);
        $this->db->select("Dom_EN_Desc");
        $this->db->from("SYS_M_Domain");
        $this->db->where("Dom_Host_Code", 'PROD_STATUS');
        $this->db->where("Dom_Active", 'Y');

        if ($status_id != "") {
            $this->db->where("Dom_Code", $status_id);
        }

        $query = $this->db->get();
        $result = $query->result();

        $range = array();
        
        //เก็บสถานะที่ได้ใน array
        foreach ($result as $row) {
            $range[$row->Dom_EN_Desc] = $row->Dom_EN_Desc;
        }
     
        // echo $this->db->last_query(); 
        // echo $range[$row->Dom_EN_Desc];exit();
        return $range;
    }

    //สำหรับ เรียกเงื่อนไขในการแสดงค่า balance ของแต่ละสถานะที่สนใจ
    #add parameter $usetable : by kik : 2013-11-14
    function getSqlSearchBalance($rang, $usetable = 'STK_T_Onhand_History') {
        $condition = '';
        $i = 1;
        foreach ($rang as $key => $value) {
            $condition.=$this->sqlInventoryBalance($value, $i, $usetable);
            $i++;
        }
        return $condition;
    }

    //สำหรับ เรียกเงื่อนไขในการแสดงค่า balance ของแต่ละสถานะที่สนใจ
    private function get_column_balance($rang) {
        $column = '';
        $i = 1;
        foreach ($rang as $key => $value) {
            $column .= ",counts_" . $i;
            $column .= ",estimate_" . $i;
            $i++;
        }
        return $column;
    }

    //คำสั่ง query ค่า balance/Estimate_qty
    #add parameter $useTable : by kik : 2013-11-14
    function sqlInventoryBalance($status, $index, $useTable) {
        $sql_option = '';

//        start comment code by kik : 2013-11-14
//        $sql_option = ",isnull(SUM(case when STK_T_Onhand_History.Product_Status='$status' then Balance_Qty end),0) as counts_" . $index . ",
//        isnull(SUM(case when STK_T_Onhand_History.Product_Status='$status' then Estimate_qty end),0)
//        as estimate_" . $index . "";
//        end comment code by kik : 2013-11-14
//       start modify new code by kik : 2013-11-14

        if ($useTable == 'STK_T_Inbound'):
            $sql_option = ",isnull(SUM(case when " . $useTable . ".Product_Status='$status' then Balance_Qty end),0) as counts_" . $index . ",
            isnull(SUM(case when " . $useTable . ".Product_Status='$status' then (Receive_Qty-PD_Reserv_Qty-Dispatch_Qty-Adjust_Qty) end),0)
            as estimate_" . $index . "";
        else:
            $sql_option = ",isnull(SUM(case when " . $useTable . ".Product_Status='$status' then Balance_Qty end),0) as counts_" . $index . ",
            isnull(SUM(case when " . $useTable . ".Product_Status='$status' then Estimate_qty end),0)
            as estimate_" . $index . "";
        endif;

    //    echo $this->db->last_query(); exit();
//       end modify new code by kik : 2013-11-14

        return $sql_option;
    }

    //สำหรับเงื่อนไขในการแสดงรายการ
    #add parameter $useTable : by kik : 2013-11-14
    function searchConditionInventory($search, $rang, $useTable = 'STK_T_Onhand_History') {
        //แปลง date ให้อยู่ในรูป yyyy-mm-dd ครับ
     

        if (array_key_exists('as_date', $search)) :
            $compare_date = convertDate($search['as_date'], "eng", "iso", "-");
        endif;

        $sql = ' 1=1 '; //add by kik : แก้ปัญหาเมื่อไม่ได้เลือก renter_id เข้ามา จะทำให้โค้ดโปรแกรม error จึงได้เพิ่มเงื่อนไขดังกล่าวเข้าไปตามคำแนะนำของพี่ปอ

        if ($search['renter_id'] != "") {
            $sql.="AND " . $useTable . ".Renter_Id=" . $search['renter_id'];
        }

        # add condition $useTable != "STK_T_Inbound" : by kik : 2013-11-14
        if (array_key_exists('as_date', $search)) :
            if ($search['as_date'] != "" && $useTable != "STK_T_Inbound") {
                $sql.=" AND convert(varchar(10),Available_Date,120) ='" . $compare_date . "'";
            }
        endif;

        if ($search['product_id'] != "") {
            $sql.=" AND " . $useTable . ".Product_Id='" . $search['product_id'] . "'";
        }
      //---------------------------------------
        if(count($rang) == 1){
            foreach ($rang as $row) {
                $status_code = $row;
            } 
          
            $sqli ="SELECT Dom_Code FROM SYS_M_Domain 
                   WHERE Dom_Host_Code = 'PROD_STATUS'
                   AND Dom_Active = 'Y'";
                   if ($status_code != "") {
                        $sqli.="AND Dom_EN_Desc ='" . $status_code . "'";

                   }
                   $query = $this->db->query($sqli);

                   $result = $query->result();
                   $rang = array();
                foreach ($result as $row) {
                        $rang[$row->Dom_Code] = $row->Dom_Code;
                }
                
          $sql.=" AND " . $useTable . ".Product_Status IN ('" . implode("','", $rang) . "')";
          
        }
        if($search['status_id'] == "" ){
            $sqli ="SELECT Dom_Code FROM SYS_M_Domain 
                   WHERE Dom_Host_Code = 'PROD_STATUS'
                   AND Dom_Active = 'Y'";
                   $query = $this->db->query($sqli);

                   $result = $query->result();
                   $rang = array();
                foreach ($result as $row) {
                        $rang[$row->Dom_Code] = $row->Dom_Code;
                }
                
          $sql.=" AND " . $useTable . ".Product_Status IN ('" . implode("','", $rang) . "')";
        }
        //----------------------------------

        $sql.=" AND " . $useTable . ".Product_Status IN ('" . implode("','", $rang) . "')";
        // p($sql);exit;
        return $sql;
    }

    //==END
    //+++++ADD BY POR 2013-11-14 เพิ่ม function สำหรับหาข้อมูล receive
    //=====query หาข้อมูลหลังจากกด search
    #ลบ DISTINCT document no ออก เนื่องจากมันทำให้ข้อมูลขึ้นผิดพลาดได้ : kik : 20140528
    function search_receive($search) {

        #Load config (add by kik : 20140723)
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

        /**         * *******************************************************************************
         * Document ปกติ
         * ********************************************************************************* */
        /**
         * ====================================================================================================
         * Select Zone
         */
        $this->db->select("
             inb.Document_No ,inb.Doc_Refer_Ext ,inb.Product_Code
            ,(SELECT Product_NameEN FROM STK_M_Product WHERE Product_Id=inb.Product_Id) Product_Name
            ,d.Dom_TH_Desc ,inb.Product_Lot ,inb.Product_Serial
            ,CONVERT(VARCHAR(20), inb.Product_Mfd,103) as Product_Mfd
            ,CONVERT(VARCHAR(20), inb.Product_Exp,103) as Product_Exp
            ,inb.Unit_Id ,sum(inb.Receive_Qty) Receive_Qty
            ,CONVERT(VARCHAR(20), inb.Receive_Date, 103) as Receive_Date
            ,CONVERT(VARCHAR(20), inb.Receive_Date, 103) as Receive_Date_sort
            ,(SELECT Company_NameEN FROM CTL_M_Company WHERE Company_Id=inb.Owner_Id) To_sup
            ,(SELECT Company_NameEN FROM CTL_M_Company WHERE Company_Id=inb.Vendor_Id) From_sup
            ,isnull((Select TOP 1 CAST(stuff((Select case t.Remark WHEN '' THEN '' WHEN NULL THEN '' ELSE ',' + t.Remark END from STK_T_Order_Detail t where t.Order_Id=r.Order_Id AND t.Product_Id=inb.Product_Id AND t.Inbound_Item_Id=inb.Inbound_Id order by Item_Id for xml path('')),1,1,'') as nvarchar(1000) ) as myRemark from STK_T_Order_Detail ),'') Remark
            ,inb.Doc_Refer_Int
            ,inb.Doc_Refer_Inv
            ,inb.Doc_Refer_CE
            ,inb.Doc_Refer_BL
            ,S1.public_name AS Unit_Value
            ,'Is_reject' = CASE
               WHEN w.Present_State <> -1 THEN 'N'
               WHEN  w.Present_State = -1 THEN 'Y'
            END
            ");

	$this->db->select("pd.Cubic_Meters * sum(inb.Receive_Qty) As CBM");
        #check query of Invoice (by kik : 20140708)
        if ($conf_inv):
            $this->db->select("inb.Invoice_Id
                ,STK_T_Invoice.Invoice_No");
        endif; // end of query of Invoice
        #check query of Container (by kik : 20140708)
        if ($conf_cont):
            $this->db->select("inb.Cont_Id
                ,CTL_M_Container.Cont_No
                ,CTL_M_Container_Size.Cont_Size_No
                ,CTL_M_Container_Size.Cont_Size_Unit_Code");
        endif; // end of query of Container
        #check query of Price per unit (by kik : 20140708)
        if ($conf_price_per_unit):
            $this->db->select("inb.Price_Per_Unit
                , SYS_M_Domain.Dom_EN_SDesc AS Unit_price
                , (Receive_Qty * inb.Price_Per_Unit) AS All_price");
        endif; // end of query of Price per unit
        #check query of Pallet (by kik : 20140708)
        if ($conf_pallet):
            $this->db->select("pall.Pallet_Code,inb.Pallet_Id");
        endif; // end of query of Pallet


        /**
         * ====================================================================================================
         * From/Join Zone
         */
        $this->db->join("STK_T_Order r", "inb.Document_No=r.Document_No", "LEFT");
        $this->db->join("STK_T_Workflow w", "inb.Flow_Id = w.Flow_Id", "LEFT");
        // Add By Akkarapol, 13/01/2014, เพิ่มการ join กับตาราง CTL_M_UOM_Template_Language เพื่อหาชื่อของ Unit มาแสดง
        $this->db->join("CTL_M_UOM_Template_Language S1", "inb.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'");
        // End Add By Akkarapol, 13/01/2014, เพิ่มการ join กับตาราง CTL_M_UOM_Template_Language เพื่อหาชื่อของ Unit มาแสดง
        #check query of Pallet (by kik : 20140708)
        if ($conf_pallet):
            // Add By Akkarapol, 31/01/2014, เพิ่มการ join เข้าไปที่ STK_T_Pallet เพื่อเอา Pallet_Code มาใช้งาน
            $this->db->join("STK_T_Pallet pall", "inb.Pallet_Id = pall.Pallet_Id", 'LEFT');
        // END Add By Akkarapol, 31/01/2014, เพิ่มการ join เข้าไปที่ STK_T_Pallet เพื่อเอา Pallet_Code มาใช้งาน
        endif; // end of query of Pallet
        #check query of Price per unit (by kik : 20140708)
        if ($conf_price_per_unit):
            //ADD BY POR 2014-06-18 เพิ่มการ join หน่วยของราคาด้วย
            $this->db->join("SYS_M_Domain", "inb.Unit_Price_Id = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code ='PRICE_UNIT' AND SYS_M_Domain.Dom_Active = 'Y'", "LEFT");
        //END ADD
        endif; // end of query of Price per unit
        #check query of Invoice (by kik : 20140708)
        if ($conf_inv):
            $this->db->join("STK_T_Invoice", "STK_T_Invoice.Invoice_Id = inb.Invoice_Id", "LEFT");
        endif; // end of query of Invoice
        #check query of Container  (by kik : 20140708)
        if ($conf_cont):
            $this->db->join("CTL_M_Container", "CTL_M_Container.Cont_Id = inb.Cont_Id", "LEFT");
            $this->db->join("CTL_M_Container_Size", "CTL_M_Container_Size.Cont_Size_Id = CTL_M_Container.Cont_Size_Id", "LEFT");
        endif; // end of query of Container


    $this->db->join("STK_M_Product pd", "pd.Product_Id = inb.Product_Id ", "LEFT");
    $this->db->join("SYS_M_Domain d", "inb.Product_Status = d.Dom_Code and d.Dom_Host_Code ='prod_status' and d.Dom_Active ='Y' ", "LEFT");
        $this->db->from("STK_T_Inbound inb");



        /**
         * ====================================================================================================
         * Group Zone
         */
        $groupby = array("inb.Document_No"
            , "inb.Doc_Refer_Ext", "inb.Product_Code"
            , "inb.Product_Id", "d.Dom_TH_Desc"
            , "inb.Product_Lot", "inb.Product_Serial"
            , "CONVERT(VARCHAR(20), inb.Product_Mfd,103)"
            , "CONVERT(VARCHAR(20), inb.Product_Exp,103)"
            , "inb.Unit_Id"
            , "CONVERT(VARCHAR(20), inb.Receive_Date, 103)"
            , "CAST(inb.Receive_Date AS date)"
            , "inb.Owner_Id"
//	    , "inb.Renter_Id"
	    , "inb.Vendor_Id"
            , "r.Order_Id", "inb.Inbound_Id"
            , "inb.Doc_Refer_Int", "inb.Doc_Refer_Inv"
            , "inb.Doc_Refer_CE", "inb.Doc_Refer_BL"
            , "S1.public_name"
            , "w.Present_State"
   	    , "pd.Cubic_Meters"
        );

        #check query of Invoice (by kik : 20140708)
        if ($conf_inv):
            array_push($groupby, "inb.Invoice_Id");
            array_push($groupby, "STK_T_Invoice.Invoice_No");
        endif; // end of query of Invoice
        #check query of Container (by kik : 20140708)
        if ($conf_cont):
            array_push($groupby, "inb.Cont_Id");
            array_push($groupby, "CTL_M_Container.Cont_No");
            array_push($groupby, "CTL_M_Container_Size.Cont_Size_No");
            array_push($groupby, "CTL_M_Container_Size.Cont_Size_Unit_Code");

        endif; // end of query of Container
        #check query of Price per unit (by kik : 20140708)
        if ($conf_price_per_unit):
            array_push($groupby, "inb.Price_Per_Unit");
            array_push($groupby, "SYS_M_Domain.Dom_EN_SDesc");
            array_push($groupby, "(Receive_Qty * inb.Price_Per_Unit)");

        endif; // end of query of Price per unit
        #check query of Pallet (by kik : 20140708)
        if ($conf_pallet):
            array_push($groupby, "inb.Pallet_Id");
            array_push($groupby, "pall.Pallet_Code");
        endif; // end of query of Pallet

        $this->db->group_by($groupby);
        unset($groupby);

        /**
         * ====================================================================================================
         * Where Zone
         */
        //$this->db->where("inb.Active",'Y'); // Edit By Akkarapol, 31/01/2014, เปลี่ยนจาก where 'Active' เป็น 'inb.Active' เพราะจำเป็นต้องบอกให้ query รู้ว่า เราต้องการเช็คทื่ตาราง STK_T_Inbound inb นะ
        if ($search['fdate'] != "") {
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") {
                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)>='" . $from_date . "'");
                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)<='" . $to_date . "'");
            } else {
                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)>='" . $from_date . "'");
            }
        }

        if ($search['doc_value'] != "") {
            $this->db->like("inb." . $search['doc_type'], $this->db->escape_like_str($search['doc_value']));
        }

        // Add validate document no 2014-06-11
        if (isset($search['Document_No'])) {
            $this->db->where("inb.Document_No", $search['Document_No']);
        }
//        $this->db->where("inb.Active", 'Y');
        $this->db->where_in('w.Process_Id', array(1));
        $this->db->where_in('w.Present_State', array(5, 6, -2, -1));

        if ($search["active"] == 1) {
            $this->db->where_in('inb.Active', 'Y'); // condition Active Y only -- If check active = Y problem is when relocation until active = N it can't get old data.
        }

        $this->db->order_by("CAST(inb.Receive_Date AS date)", "asc");
        $this->db->order_by("inb.Document_No", "asc");
        $this->db->order_by("inb.Product_Code", "asc");

        $query_normal = $this->db->get();
        // p($this->db->last_query());
        $result = $query_normal->result();

	// echo $this->db->last_query(); exit();
//        p($result);exit();

        return $result;
    }

    # COMMENT FUNCTION BY KIK : 20141105
//    //+++++ADD BY POR 2013-11-14 เพิ่ม function สำหรับหาข้อมูล receive
//    //=====query หาข้อมูลหลังจากกด search
//    #ลบ DISTINCT document no ออก เนื่องจากมันทำให้ข้อมูลขึ้นผิดพลาดได้ : kik : 20140528
//    function search_receive($search) {
//
//        #Load config (add by kik : 20140723)
//        $conf = $this->config->item('_xml');
//        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
//        $conf_cont = empty($conf['container'])?false:@$conf['container'];
//        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
//        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];
//
//        /** ********************************************************************************
//         * Document ปกติ
//         ***********************************************************************************/
//
//        /**
//         * ====================================================================================================
//         * Select Zone
//         */
//        $this->db->select("
//             inb.Document_No ,inb.Doc_Refer_Ext ,inb.Product_Code
//            ,(SELECT Product_NameEN FROM STK_M_Product WHERE Product_Id=inb.Product_Id) Product_Name
//            ,inb.Product_Status ,inb.Product_Lot ,inb.Product_Serial
//            ,CONVERT(VARCHAR(20), inb.Product_Mfd,103) as Product_Mfd
//            ,CONVERT(VARCHAR(20), inb.Product_Exp,103) as Product_Exp
//            ,inb.Unit_Id ,sum(inb.Receive_Qty) Receive_Qty
//            ,CONVERT(VARCHAR(20), inb.Receive_Date, 103) as Receive_Date
//            ,CONVERT(VARCHAR(20), inb.Receive_Date, 120) as Receive_Date_sort
//            ,(SELECT Company_NameEN FROM CTL_M_Company WHERE Company_Id=inb.Owner_Id) To_sup
//            ,(SELECT Company_NameEN FROM CTL_M_Company WHERE Company_Id=inb.Renter_Id) From_sup
//            ,isnull((Select TOP 1 CAST(stuff((Select case t.Remark WHEN '' THEN '' WHEN NULL THEN '' ELSE ',' + t.Remark END from STK_T_Order_Detail t where t.Order_Id=r.Order_Id AND t.Product_Id=inb.Product_Id AND t.Inbound_Item_Id=inb.Inbound_Id order by Item_Id for xml path('')),1,1,'') as nvarchar(1000) ) as myRemark from STK_T_Order_Detail ),'') Remark
//            ,inb.Doc_Refer_Int
//            ,inb.Doc_Refer_Inv
//            ,inb.Doc_Refer_CE
//            ,inb.Doc_Refer_BL
//            ,'N' as Is_reject
//            ,S1.public_name AS Unit_Value
//            ");
//
//        #check query of Invoice (by kik : 20140708)
//        if($conf_inv):
//            $this->db->select("inb.Invoice_Id
//                ,STK_T_Invoice.Invoice_No");
//        endif; // end of query of Invoice
//
//
//        #check query of Container (by kik : 20140708)
//        if($conf_cont):
//            $this->db->select("inb.Cont_Id
//                ,CTL_M_Container.Cont_No
//                ,CTL_M_Container_Size.Cont_Size_No
//                ,CTL_M_Container_Size.Cont_Size_Unit_Code");
//        endif; // end of query of Container
//
//
//        #check query of Price per unit (by kik : 20140708)
//        if($conf_price_per_unit):
//            $this->db->select("inb.Price_Per_Unit
//                , SYS_M_Domain.Dom_EN_SDesc AS Unit_price
//                , (Receive_Qty * inb.Price_Per_Unit) AS All_price");
//        endif; // end of query of Price per unit
//
//
//        #check query of Pallet (by kik : 20140708)
//        if($conf_pallet):
//            $this->db->select("pall.Pallet_Code,inb.Pallet_Id");
//        endif; // end of query of Pallet
//
//
//        /**
//         * ====================================================================================================
//         * From/Join Zone
//         */
//        $this->db->join("STK_T_Order r","inb.Document_No=r.Document_No","LEFT");
//        $this->db->join("STK_T_Workflow w","inb.Flow_Id = w.Flow_Id","LEFT");
//        // Add By Akkarapol, 13/01/2014, เพิ่มการ join กับตาราง CTL_M_UOM_Template_Language เพื่อหาชื่อของ Unit มาแสดง
//        $this->db->join("CTL_M_UOM_Template_Language S1", "inb.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'");
//        // End Add By Akkarapol, 13/01/2014, เพิ่มการ join กับตาราง CTL_M_UOM_Template_Language เพื่อหาชื่อของ Unit มาแสดง
//
//
//        #check query of Pallet (by kik : 20140708)
//        if($conf_pallet):
//            // Add By Akkarapol, 31/01/2014, เพิ่มการ join เข้าไปที่ STK_T_Pallet เพื่อเอา Pallet_Code มาใช้งาน
//            $this->db->join("STK_T_Pallet pall", "inb.Pallet_Id = pall.Pallet_Id",'LEFT');
//            // END Add By Akkarapol, 31/01/2014, เพิ่มการ join เข้าไปที่ STK_T_Pallet เพื่อเอา Pallet_Code มาใช้งาน
//        endif; // end of query of Pallet
//
//
//        #check query of Price per unit (by kik : 20140708)
//        if($conf_price_per_unit):
//            //ADD BY POR 2014-06-18 เพิ่มการ join หน่วยของราคาด้วย
//            $this->db->join("SYS_M_Domain","inb.Unit_Price_Id = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code ='PRICE_UNIT'","LEFT");
//            //END ADD
//        endif; // end of query of Price per unit
//
//
//        #check query of Invoice (by kik : 20140708)
//        if($conf_inv):
//            $this->db->join("STK_T_Invoice", "STK_T_Invoice.Invoice_Id = inb.Invoice_Id" ,"LEFT");
//        endif; // end of query of Invoice
//
//
//        #check query of Container  (by kik : 20140708)
//        if($conf_cont):
//            $this->db->join("CTL_M_Container", "CTL_M_Container.Cont_Id = inb.Cont_Id" ,"LEFT");
//            $this->db->join("CTL_M_Container_Size", "CTL_M_Container_Size.Cont_Size_Id = CTL_M_Container.Cont_Size_Id" ,"LEFT");
//        endif; // end of query of Container
//
//
//        $this->db->from("STK_T_Inbound inb");
//
//
//
//        /**
//         * ====================================================================================================
//         * Group Zone
//         */
//        $groupby = array("inb.Document_No"
//            ,"inb.Doc_Refer_Ext","inb.Product_Code"
//            ,"inb.Product_Id","inb.Product_Status"
//            ,"inb.Product_Lot","inb.Product_Serial"
//            ,"CONVERT(VARCHAR(20), inb.Product_Mfd,103)"
//            ,"CONVERT(VARCHAR(20), inb.Product_Exp,103)"
//            ,"inb.Unit_Id"
//            ,"CONVERT(VARCHAR(20), inb.Receive_Date, 103)"
//            ,"CONVERT(VARCHAR(20), inb.Receive_Date, 120)"
//            ,"inb.Owner_Id","inb.Renter_Id"
//            ,"r.Order_Id","inb.Inbound_Id"
//            ,"inb.Doc_Refer_Int","inb.Doc_Refer_Inv"
//            ,"inb.Doc_Refer_CE","inb.Doc_Refer_BL"
//            ,"S1.public_name"
//            );
//
//        #check query of Invoice (by kik : 20140708)
//        if($conf_inv):
//             array_push($groupby, "inb.Invoice_Id");
//             array_push($groupby, "STK_T_Invoice.Invoice_No");
//        endif; // end of query of Invoice
//
//
//        #check query of Container (by kik : 20140708)
//        if($conf_cont):
//            array_push($groupby, "inb.Cont_Id");
//            array_push($groupby, "CTL_M_Container.Cont_No");
//            array_push($groupby, "CTL_M_Container_Size.Cont_Size_No");
//            array_push($groupby, "CTL_M_Container_Size.Cont_Size_Unit_Code");
//
//        endif; // end of query of Container
//
//
//        #check query of Price per unit (by kik : 20140708)
//        if($conf_price_per_unit):
//            array_push($groupby, "inb.Price_Per_Unit");
//            array_push($groupby, "SYS_M_Domain.Dom_EN_SDesc");
//            array_push($groupby, "(Receive_Qty * inb.Price_Per_Unit)");
//
//        endif; // end of query of Price per unit
//
//
//        #check query of Pallet (by kik : 20140708)
//        if($conf_pallet):
//            array_push($groupby, "inb.Pallet_Id");
//            array_push($groupby, "pall.Pallet_Code");
//        endif; // end of query of Pallet
//
//        $this->db->group_by($groupby);
//        unset($groupby);
//
//        /**
//         * ====================================================================================================
//         * Where Zone
//         */
//         //$this->db->where("inb.Active",'Y'); // Edit By Akkarapol, 31/01/2014, เปลี่ยนจาก where 'Active' เป็น 'inb.Active' เพราะจำเป็นต้องบอกให้ query รู้ว่า เราต้องการเช็คทื่ตาราง STK_T_Inbound inb นะ
//        if ($search['fdate'] != "") {
//            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
//            if ($search['tdate'] != "") {
//                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
//                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)>='".$from_date."'");
//                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)<='".$to_date."'");
//            } else {
//                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)>='".$from_date."'");
//            }
//        }
//
//        if ($search['doc_value'] != "") {
//            $this->db->like("inb." . $search['doc_type'],$this->db->escape_like_str($search['doc_value']));
//        }
//
//        // Add validate document no 2014-06-11
//        if (isset($search['Document_No'])) {
//        	$this->db->where("inb.Document_No",$search['Document_No']);
//        }
//        $this->db->where_in('w.Process_Id', array(1));
//        $this->db->where_in('w.Present_State', array(5,6,-2)); // add by kik : 20140526 check present_state
//        $this->db->order_by("CONVERT(VARCHAR(20), inb.Receive_Date, 103)", "asc");
//        $this->db->order_by("inb.Document_No", "asc");
//
//        $query_normal=$this->db->get();
//        //p($this->db->last_query());
//        $result_normal=$query_normal->result();
//
//        /** ********************************************************************************
//         * Reject Document
//         ***********************************************************************************/
//        $this->db->select("
//             inb.Document_No ,inb.Doc_Refer_Ext ,inb.Product_Code
//            ,(SELECT Product_NameEN FROM STK_M_Product WHERE Product_Id=inb.Product_Id) Product_Name
//            ,inb.Product_Status ,inb.Product_Lot ,inb.Product_Serial
//            ,CONVERT(VARCHAR(20), inb.Product_Mfd,103) as Product_Mfd
//            ,CONVERT(VARCHAR(20), inb.Product_Exp,103) as Product_Exp
//            ,inb.Unit_Id ,sum(inb.Receive_Qty) Receive_Qty
//            ,CONVERT(VARCHAR(20), inb.Receive_Date, 103) as Receive_Date
//            ,CONVERT(VARCHAR(20), inb.Receive_Date, 120) as Receive_Date_sort
//            ,(SELECT Company_NameEN FROM CTL_M_Company WHERE Company_Id=inb.Owner_Id) To_sup
//            ,(SELECT Company_NameEN FROM CTL_M_Company WHERE Company_Id=inb.Renter_Id) From_sup
//            ,isnull((Select TOP 1 CAST(stuff((Select case t.Remark WHEN '' THEN '' WHEN NULL THEN '' ELSE ',' + t.Remark END from STK_T_Order_Detail t where t.Order_Id=r.Order_Id AND t.Product_Id=inb.Product_Id AND t.Inbound_Item_Id=inb.Inbound_Id order by Item_Id for xml path('')),1,1,'') as nvarchar(1000) ) as myRemark from STK_T_Order_Detail ),'') Remark
//            ,inb.Doc_Refer_Int
//            ,inb.Doc_Refer_Inv
//            ,inb.Doc_Refer_CE
//            ,inb.Doc_Refer_BL
//            ,'Y' as Is_reject
//            ,S1.public_name AS Unit_Value
//            ");
//
//        #check query of Invoice (by kik : 20140708)
//        if($conf_inv):
//            $this->db->select("inb.Invoice_Id
//                ,STK_T_Invoice.Invoice_No");
//        endif; // end of query of Invoice
//
//
//        #check query of Container (by kik : 20140708)
//        if($conf_cont):
//            $this->db->select("inb.Cont_Id
//                ,CTL_M_Container.Cont_No
//                ,CTL_M_Container_Size.Cont_Size_No
//                ,CTL_M_Container_Size.Cont_Size_Unit_Code");
//        endif; // end of query of Container
//
//
//        #check query of Price per unit (by kik : 20140708)
//        if($conf_price_per_unit):
//            $this->db->select("inb.Price_Per_Unit
//                , SYS_M_Domain.Dom_EN_SDesc AS Unit_price
//                , (Receive_Qty * inb.Price_Per_Unit) AS All_price");
//        endif; // end of query of Price per unit
//
//
//        #check query of Pallet (by kik : 20140708)
//        if($conf_pallet):
//            $this->db->select("pall.Pallet_Code,inb.Pallet_Id");
//        endif; // end of query of Pallet
//
//
//        /**
//         * ====================================================================================================
//         * From/Join Zone
//         */
//        $this->db->join("STK_T_Order r","inb.Document_No=r.Document_No","LEFT");
//        $this->db->join("STK_T_Workflow w","inb.Flow_Id = w.Flow_Id","LEFT");
//
//        // Add By Akkarapol, 13/01/2014, เพิ่มการ join กับตาราง CTL_M_UOM_Template_Language เพื่อหาชื่อของ Unit มาแสดง
//        $this->db->join("CTL_M_UOM_Template_Language S1", "inb.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'");
//        // End Add By Akkarapol, 13/01/2014, เพิ่มการ join กับตาราง CTL_M_UOM_Template_Language เพื่อหาชื่อของ Unit มาแสดง
//
//
//        #check query of Pallet (by kik : 20140708)
//        if($conf_pallet):
//            // Add By Akkarapol, 31/01/2014, เพิ่มการ join เข้าไปที่ STK_T_Pallet เพื่อเอา Pallet_Code มาใช้งาน
//            $this->db->join("STK_T_Pallet pall", "inb.Pallet_Id = pall.Pallet_Id",'LEFT');
//            // END Add By Akkarapol, 31/01/2014, เพิ่มการ join เข้าไปที่ STK_T_Pallet เพื่อเอา Pallet_Code มาใช้งาน
//        endif; // end of query of Pallet
//
//
//        #check query of Price per unit (by kik : 20140708)
//        if($conf_price_per_unit):
//            //ADD BY POR 2014-06-18 เพิ่มการ join หน่วยของราคาด้วย
//            $this->db->join("SYS_M_Domain","inb.Unit_Price_Id = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code ='PRICE_UNIT'","LEFT");
//            //END ADD
//        endif; // end of query of Price per unit
//
//
//        #check query of Invoice (by kik : 20140708)
//        if($conf_inv):
//            $this->db->join("STK_T_Invoice", "STK_T_Invoice.Invoice_Id = inb.Invoice_Id" ,"LEFT");
//        endif; // end of query of Invoice
//
//
//        #check query of Container  (by kik : 20140708)
//        if($conf_cont):
//            $this->db->join("CTL_M_Container", "CTL_M_Container.Cont_Id = inb.Cont_Id" ,"LEFT");
//            $this->db->join("CTL_M_Container_Size", "CTL_M_Container_Size.Cont_Size_Id = CTL_M_Container.Cont_Size_Id" ,"LEFT");
//        endif; // end of query of Container
//
//
//        $this->db->from("STK_T_Inbound inb");
//
//
//
//        /**
//         * ====================================================================================================
//         * Group Zone
//         */
//        $groupby = array("inb.Document_No"
//            ,"inb.Doc_Refer_Ext","inb.Product_Code"
//            ,"inb.Product_Id","inb.Product_Status"
//            ,"inb.Product_Lot","inb.Product_Serial"
//            ,"CONVERT(VARCHAR(20), inb.Product_Mfd,103)"
//            ,"CONVERT(VARCHAR(20), inb.Product_Exp,103)"
//            ,"inb.Unit_Id"
//            ,"CONVERT(VARCHAR(20), inb.Receive_Date, 103)"
//            ,"CONVERT(VARCHAR(20), inb.Receive_Date, 120)"
//            ,"inb.Owner_Id","inb.Renter_Id"
//            ,"r.Order_Id","inb.Inbound_Id"
//            ,"inb.Doc_Refer_Int","inb.Doc_Refer_Inv"
//            ,"inb.Doc_Refer_CE","inb.Doc_Refer_BL"
//            ,"S1.public_name"
//            );
//
//        #check query of Invoice (by kik : 20140708)
//        if($conf_inv):
//             array_push($groupby, "inb.Invoice_Id");
//             array_push($groupby, "STK_T_Invoice.Invoice_No");
//        endif; // end of query of Invoice
//
//
//        #check query of Container (by kik : 20140708)
//        if($conf_cont):
//            array_push($groupby, "inb.Cont_Id");
//            array_push($groupby, "CTL_M_Container.Cont_No");
//            array_push($groupby, "CTL_M_Container_Size.Cont_Size_No");
//            array_push($groupby, "CTL_M_Container_Size.Cont_Size_Unit_Code");
//
//        endif; // end of query of Container
//
//
//        #check query of Price per unit (by kik : 20140708)
//        if($conf_price_per_unit):
//            array_push($groupby, "inb.Price_Per_Unit");
//            array_push($groupby, "SYS_M_Domain.Dom_EN_SDesc");
//            array_push($groupby, "(Receive_Qty * inb.Price_Per_Unit)");
//
//        endif; // end of query of Price per unit
//
//
//        #check query of Pallet (by kik : 20140708)
//        if($conf_pallet):
//            array_push($groupby, "inb.Pallet_Id");
//            array_push($groupby, "pall.Pallet_Code");
//        endif; // end of query of Pallet
//
//        $this->db->group_by($groupby);
//
//
//        /**
//         * ====================================================================================================
//         * Where Zone
//         */
//         //$this->db->where("inb.Active",'Y'); // Edit By Akkarapol, 31/01/2014, เปลี่ยนจาก where 'Active' เป็น 'inb.Active' เพราะจำเป็นต้องบอกให้ query รู้ว่า เราต้องการเช็คทื่ตาราง STK_T_Inbound inb นะ
//        if ($search['fdate'] != "") {
//            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
//            if ($search['tdate'] != "") {
//                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
//                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)>='".$from_date."'");
//                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)<='".$to_date."'");
//            } else {
//                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)>='".$from_date."'");
//            }
//        }
//
//        if ($search['doc_value'] != "") {
//            $this->db->like("inb." . $search['doc_type'],$this->db->escape_like_str($search['doc_value']));
//        }
//
//        // Add validate document no 2014-06-11
//        if (isset($search['Document_No'])) {
//        	$this->db->where("inb.Document_No",$search['Document_No']);
//        }
//        $this->db->where_in('w.Process_Id', array(1));
//        $this->db->where_in('w.Present_State', array(-1)); // add by kik : 20140526 check present_state
//        $this->db->order_by("CONVERT(VARCHAR(20), inb.Receive_Date, 103)", "asc");
//        $this->db->order_by("inb.Document_No", "asc");
//
//        $query_reject=$this->db->get();
//        $result_reject=$query_reject->result();
//
//
//        $result_tmp = array_merge($result_normal, $result_reject);// p($result_tmp);exit();
//        $result = sort_arr_of_obj($result_tmp,'Receive_Date_sort','asc');
//
////	echo $this->db->last_query(); exit();
////        p($result);exit();
//
//        return $result;
//    }
//
//
    /**
     *
     * @param type array(document_no)
     * @return array
     */

    function search_receive_pdf($search) {


        #Load config (add by kik : 20140723)
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];

        #+++++++++++++++++++++++ Add new code for query search_receive function
        $result_query = $this->search_receive($search);
        // p($result_query); exit;
        $dataRows = array();
        $dataRow = array();
        $dataRow_reject = array();

        foreach ($result_query as $value) {
            // $head = $value->Flow_Id . $value->Doc_Refer_Ext . $value->Document_No . $value->Doc_Refer_BL;
            $head = $value->Flow_Id . $value->Doc_Refer_Ext . $value->Document_No . $value->Doc_Refer_Int;
            if (isset($head, $dataRows[$head])) {
                $keyTmp = sizeof($dataRows[$head]);
            } else {
                $keyTmp = 0;
            }
            $dataRow['Receive_Date'] = $value->Receive_Date;
            $dataRow['Document_No'] = $value->Document_No;
            $dataRow['Doc_Refer_Ext'] = $value->Doc_Refer_Ext;
            $dataRow['Doc_Refer_Int'] = $value->Doc_Refer_Int;
            $dataRow['Doc_Refer_BL'] = $value->Doc_Refer_BL; //ADD BY POR 2013-12-09 แสดงข้อมูลเพิ่มเติม
            $dataRow['Product_Code'] = $value->Product_Code;
            $dataRow['Product_Name'] = $value->Product_Name;
            $dataRow['Product_Status'] = $value->Dom_TH_Desc;
            $dataRow['Product_SerialLot'] = $value->Product_Serial . '/' . $value->Product_Lot;
            $dataRow['Product_Mfd'] = $value->Product_Mfd;
            $dataRow['Product_Exp'] = $value->Product_Exp;
            $dataRow['Receive_Qty'] = $value->Receive_Qty;
            $dataRow['Unit_Value'] = $value->Unit_Value;

            if ($conf_price_per_unit):
                $dataRow['Price_Per_Unit'] = $value->Price_Per_Unit;
                $dataRow['Unit_price'] = $value->Unit_price;
                $dataRow['All_price'] = $value->All_price;
            endif;

            if ($conf_inv):
                $dataRow['Invoice_No'] = $value->Invoice_No;
            endif;

            if ($conf_pallet):
                $dataRow['Pallet_Code'] = $value->Pallet_Code;
            endif;

            if ($conf_cont):
                $dataRow['Cont_No'] = $value->Cont_No;
                $dataRow['Cont_Size_No'] = $value->Cont_Size_No;
                $dataRow['Cont_Size_Unit_Code'] = $value->Cont_Size_Unit_Code;
            endif;

            $dataRow['From_sup'] = $value->From_sup;
            $dataRow['To_sup'] = $value->To_sup;
            $dataRow['Flow_Id'] = $value->Flow_Id;

            $dataRow['Remark'] = $value->Remark;
            $dataRow['Is_reject'] = $value->Is_reject;

            $dataRows[$head][$keyTmp] = $dataRow;


            if ($value->Is_reject == 'Y'):
                $dataRow_reject['Receive_Date'] = $value->Receive_Date;
                $dataRow_reject['Document_No'] = $value->Document_No;
                $dataRow_reject['Doc_Refer_Ext'] = $value->Doc_Refer_Ext;
                $dataRow_reject['Doc_Refer_Int'] = $value->Doc_Refer_Int;
                $dataRow_reject['Doc_Refer_BL'] = $value->Doc_Refer_BL; //ADD BY POR 2013-12-09 แสดงข้อมูลเพิ่มเติม
                $dataRow_reject['Product_Code'] = $value->Product_Code;
                $dataRow_reject['Product_Name'] = $value->Product_Name;
                $dataRow_reject['Product_Status'] = $value->Dom_TH_Desc;
                $dataRow_reject['Product_SerialLot'] = $value->Product_Serial . '/' . $value->Product_Lot;
                $dataRow_reject['Product_Mfd'] = $value->Product_Mfd;
                $dataRow_reject['Product_Exp'] = $value->Product_Exp;
                $dataRow_reject['Receive_Qty'] = -$value->Receive_Qty;
                $dataRow_reject['Unit_Value'] = $value->Unit_Value;

                if ($conf_price_per_unit):
                    $dataRow['Price_Per_Unit'] = $value->Price_Per_Unit;
                    $dataRow['Unit_price'] = $value->Unit_price;
                    $dataRow['All_price'] = $value->All_price;
                endif;

                if ($conf_inv):
                    $dataRow['Invoice_No'] = $value->Invoice_No;
                endif;

                if ($conf_pallet):
                    $dataRow['Pallet_Code'] = $value->Pallet_Code;
                endif;

                if ($conf_cont):
                    $dataRow['Cont_No'] = $value->Cont_No;
                    $dataRow['Cont_Size_No'] = $value->Cont_Size_No;
                    $dataRow['Cont_Size_Unit_Code'] = $value->Cont_Size_Unit_Code;
                endif;

                $dataRow_reject['From_sup'] = $value->From_sup;
                $dataRow_reject['To_sup'] = $value->To_sup;
                $dataRow_reject['Flow_Id'] = $value->Flow_Id;
                $dataRow_reject['Pallet_Code'] = $value->Pallet_Code;
                $dataRow['Is_reject'] = $value->Is_reject;
                $dataRow_reject['Remark'] = ($value->Remark == "" || $value->Remark == " " || empty($value->Remark)) ? 'Reject' : $value->Remark;

                $dataRows[$head][$keyTmp + 1] = $dataRow_reject;

            endif;

            //  p($dataRows[$head][$keyTmp]);exit();

            unset($dataRow);
            unset($head);
        }

        $result = $dataRows;
//        p($result);exit();

        return $result;
    }

    //+++++END ADD
    // Add By Akkarapol, 21/01/2013, เพิ่มฟังก์ชั่นสำหรับ query Report Stock Transfer
    function get_stock_trasfer($search, $limit_start = 0, $limit_max = 100) {
        $this->db->select("CONVERT(VARCHAR(20), r.Estimate_Action_Date, 103) AS Estimate_Action_Date
				,r.Doc_Refer_Int
				,r.Doc_Refer_Ext
				,(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=r.Destination_Id) AS consignee
				,(SELECT Company_NameEN FROM CTL_M_Company WHERE CTL_M_Company.Company_Id=r.Source_Id) AS supplier
				,d.Product_Code
				,(SELECT Product_NameEN FROM STK_M_Product p WHERE p.Product_Id=d.Product_Id) AS Product_NameEN
				,d.Reserv_Qty, CONVERT(VARCHAR(20), r.Actual_Action_Date, 103) AS Actual_Action_Date
				,d.Confirm_Qty, d.Remark

                                ,d.Pallet_Id
                                ,plt.Pallet_Code

                                ");

        #add for ISSUE 3302 : by kik :20140114
        $this->db->select("d.Price_Per_Unit");
        $this->db->select("domain.Dom_EN_Desc AS Unit_Price_value");
        $this->db->select("d.All_Price");
        #end add for ISSUE 3302 : by kik :20140114


        $this->db->join("STK_T_Order_Detail d", "d.Order_Id = r.Order_Id");
//                LEFT JOIN SYS_M_Domain S4 ON a.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code='PRICE_UNIT'

        $this->db->join("STK_T_Pallet plt", "d.Pallet_Id = plt.Pallet_Id", 'LEFT');

        $this->db->join("SYS_M_Domain domain", "d.Unit_Price_Id = domain.Dom_Code AND domain.Dom_Active = 'Y'", "LEFT");
        $this->db->where("r.Process_Type", "OUTBOUND");
        $this->db->where("r.Doc_Type", "TF1");
        $this->db->where("Actual_Action_Date IS NOT NULL");
        $this->db->order_by("r.Estimate_Action_Date ASC,r.Destination_Id ASC");

        if (!empty($search['document_no'])) :
            $this->db->where("r.Document_No", $search['document_no']);
        endif;

        if ($search['fdate'] != "") :
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") :
                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
                $this->db->where("r.Estimate_Action_Date>='" . $from_date . "' ");
                $this->db->where("r.Estimate_Action_Date<='" . $to_date . " 23:59:59'");
            else :
                $this->db->where("r.Estimate_Action_Date>='" . $from_date . "'");
                $this->db->where("r.Estimate_Action_Date<='" . $from_date . " 23:59:59'");
            endif;
        endif;

        if (isset($search['sSearch'])) :
            $this->db->like("(SELECT Product_NameEN FROM STK_M_Product p WHERE p.Product_Id=d.Product_Id)", utf8_to_tis620($search['sSearch']));
            $this->db->or_like("r.Doc_Refer_Int", utf8_to_tis620($search['sSearch']));
            $this->db->or_like("r.Doc_Refer_Ext", utf8_to_tis620($search['sSearch']));
            $this->db->or_like("d.Product_Code", utf8_to_tis620($search['sSearch']));
        endif;

        $this->db->limit($limit_max, $limit_start);
        $query = $this->db->get("STK_T_Order r");
        $result = $query->result();
//        echo $this->db->last_query();
        return $result;
    }

    // END Add By Akkarapol, 21/01/2013, เพิ่มฟังก์ชั่นสำหรับ query Report Stock Transfer

    /**
     * @function get_packingList_receive
     * @param int $pallet_id
     * @return array
     *
     *
     * @ISSUE2492 Build Pallet
     *
     * @author : Kik 20140707
     *
     * @modified :
     *
     */
    function get_packingList_receive($pallet_id) {

        $this->db->select('pallet_detail.*');
        $this->db->select('order_detail.Product_Code
            ,order_detail.Product_Status
            ,order_detail.Product_Sub_Status
            ,order_detail.Product_Lot
            ,order_detail.Product_Serial
            ,CONVERT(varchar(10),order_detail.Product_Mfd,103) as Product_Mfd
            ,CONVERT(varchar(10),order_detail.Product_Exp,103) as Product_Exp
            ,order_detail.Price_Per_Unit
            ,order_detail.Unit_Price_Id
            ,order_detail.All_Price
            ,order_detail.Unit_Id');
        $this->db->select('STK_T_Order.Document_No,STK_T_Order.Doc_Refer_Ext');
        $this->db->select('product.Product_NameEN');
        $this->db->select('S1.public_name AS Unit_Value');
        $this->db->select('S2.Dom_Code AS Status_Code,S2.Dom_EN_Desc AS Status_Value');
        $this->db->select('S3.Dom_Code AS Sub_Status_Code, S3.Dom_EN_Desc AS Sub_Status_Value');
        $this->db->select('S4.Dom_EN_Desc AS Unit_Price_value');
        $this->db->from('STK_T_Pallet_Detail pallet_detail');
        $this->db->join('STK_T_Order_Detail order_detail', 'pallet_detail.Item_Id = order_detail.Item_Id');
        $this->db->join('STK_T_Order', 'order_detail.Order_Id = STK_T_Order.Order_Id');
        $this->db->join('STK_M_Product product', 'order_detail.Product_Code = product.Product_Code', 'LEFT');
        $this->db->join('CTL_M_UOM_Template_Language S1', 'order_detail.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = "' . $this->config->item('lang3digit') . '"', 'LEFT');
        $this->db->join('SYS_M_Domain S2', 'order_detail.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code="PROD_STATUS" AND S2.Dom_Active = "Y" ', 'LEFT');
        $this->db->join('SYS_M_Domain S3', 'order_detail.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code="SUB_STATUS" AND S3.Dom_Active = "Y" ', 'LEFT');
        $this->db->join('SYS_M_Domain S4', 'order_detail.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code="PRICE_UNIT" AND S4.Dom_Active = "Y" ', 'LEFT');

        $this->db->where('pallet_detail.Pallet_Id', $pallet_id);
        $this->db->where('pallet_detail.Parent_Id IS NULL');
        $this->db->where('order_detail.Active', 'Y');
        $this->db->where('product.Active', 'Y');
        $this->db->where('pallet_detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
        $this->db->order_by('pallet_detail.Id asc');
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }

//end of get_packingList_receive
    #add for ISSUE3323 Build Pallet6 (print PDF,excel) : 20140203 : by kik

    function get_packingList_putaway($pallet_id) {

        $this->db->select('pallet_detail.*');
        $this->db->select('order_detail.Product_Code
            ,order_detail.Product_Status
            ,order_detail.Product_Sub_Status
            ,order_detail.Product_Lot
            ,order_detail.Product_Serial
            ,CONVERT(varchar(10),order_detail.Product_Mfd,103) as Product_Mfd
            ,CONVERT(varchar(10),order_detail.Product_Exp,103) as Product_Exp
            ,order_detail.Price_Per_Unit
            ,order_detail.Unit_Price_Id
            ,order_detail.All_Price
            ,order_detail.Unit_Id');
        $this->db->select('STK_T_Order.Document_No,STK_T_Order.Doc_Refer_Ext');
        $this->db->select('product.Product_NameEN');
        $this->db->select('S1.public_name AS Unit_Value');
        $this->db->select('S2.Dom_Code AS Status_Code,S2.Dom_EN_Desc AS Status_Value');
        $this->db->select('S3.Dom_Code AS Sub_Status_Code, S3.Dom_EN_Desc AS Sub_Status_Value');
        $this->db->select('S4.Dom_EN_Desc AS Unit_Price_value');
        $this->db->from('STK_T_Pallet_Detail pallet_detail');
        $this->db->join('STK_T_Order_Detail order_detail', 'pallet_detail.Item_Id = order_detail.Item_Id');
        $this->db->join('STK_T_Order', 'order_detail.Order_Id = STK_T_Order.Order_Id');
        $this->db->join('STK_M_Product product', 'order_detail.Product_Code = product.Product_Code', 'LEFT');
        $this->db->join('CTL_M_UOM_Template_Language S1', 'order_detail.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = "' . $this->config->item('lang3digit') . '"', 'LEFT');
        $this->db->join('SYS_M_Domain S2', 'order_detail.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code="PROD_STATUS" AND S2.Dom_Active = "Y" ', 'LEFT');
        $this->db->join('SYS_M_Domain S3', 'order_detail.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code="SUB_STATUS" AND S3.Dom_Active = "Y" ', 'LEFT');
        $this->db->join('SYS_M_Domain S4', 'order_detail.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code="PRICE_UNIT" AND S4.Dom_Active = "Y" ', 'LEFT');

        $this->db->where('pallet_detail.Pallet_Id', $pallet_id);
        $this->db->where('pallet_detail.Parent_Id IS NULL');
        $this->db->where('order_detail.Active', 'Y');
        $this->db->where('product.Active', 'Y');
        $this->db->where('pallet_detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
        $this->db->order_by('pallet_detail.Id asc');
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }

    #add for ISSUE3323 Build Pallet6 (print PDF,excel) : 20140203 : by kik

    function get_packingList_picking($pallet_id) {

        $this->db->select('pallet_detail.*,
                            ,CONVERT(varchar(10),pallet_detail.Create_Date,103) as Create_Date');
        $this->db->from('STK_T_Pallet_Detail pallet_detail');
        $this->db->where('pallet_detail.Pallet_Id', $pallet_id);
        $this->db->where('pallet_detail.Parent_Id IS NOT NULL');
        $this->db->where('pallet_detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
        $this->db->order_by('pallet_detail.Id asc');
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }

    #add for ISSUE3323 Build Pallet6 (print PDF,excel) : 20140203 : by kik
    /*     * edit show/hide column : by kik : 20140910 */

    function get_packingList_dispatch($pallet_id) {

        $this->db->select('pallet_detail.*');
        $this->db->select('CONVERT(varchar(10),pallet.Create_Date,103) as Pallet_date
            ,pallet.Pallet_Code');
        $this->db->select('order_detail.Product_Code
            ,order_detail.Product_Status
            ,order_detail.Product_Sub_Status
            ,order_detail.Product_Lot
            ,order_detail.Product_Serial
            ,CONVERT(varchar(10),order_detail.Product_Mfd,103) as Product_Mfd
            ,CONVERT(varchar(10),order_detail.Product_Exp,103) as Product_Exp
            ,order_detail.Price_Per_Unit
            ,order_detail.Unit_Price_Id
            ,order_detail.All_Price
            ,order_detail.Unit_Id');

        $this->db->select('t_order.Order_Id');
        $this->db->select('product.Product_NameEN');
        $this->db->select('S1.public_name AS Unit_Value');
        $this->db->select('S2.Dom_Code AS Status_Code,S2.Dom_EN_Desc AS Status_Value');
        $this->db->select('S3.Dom_Code AS Sub_Status_Code, S3.Dom_EN_Desc AS Sub_Status_Value');
        $this->db->select('S4.Dom_EN_Desc AS Unit_Price_value');

        $this->db->from('STK_T_Pallet_Detail pallet_detail');
        $this->db->join('STK_T_Pallet pallet', 'pallet.Pallet_Id= pallet_detail.Pallet_Id AND pallet.Build_Type = "OUTBOUND" ');
        $this->db->join('STK_T_Order_Detail order_detail', 'pallet_detail.Item_Id = order_detail.Item_Id');
        $this->db->join('STK_T_Order t_order', 'order_detail.Order_Id = t_order.Order_Id');
        $this->db->join('STK_M_Product product', 'order_detail.Product_Code = product.Product_Code', 'LEFT');
        $this->db->join('CTL_M_UOM_Template_Language S1', 'order_detail.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = "' . $this->config->item('lang3digit') . '"', 'LEFT');
        $this->db->join('SYS_M_Domain S2', 'order_detail.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code="PROD_STATUS" AND S2.Dom_Active = "Y" ', 'LEFT');
        $this->db->join('SYS_M_Domain S3', 'order_detail.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code="SUB_STATUS" AND S3.Dom_Active = "Y" ', 'LEFT');
        $this->db->join('SYS_M_Domain S4', 'order_detail.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code="PRICE_UNIT" AND S4.Dom_Active = "Y" ', 'LEFT');

//        $this->db->where('pallet_detail.Pallet_Id',$pallet_id);
        $this->db->where('pallet_detail.Parent_Id IS NULL');
        $this->db->where('order_detail.Active', 'Y');
        $this->db->where('product.Active', 'Y');
        $this->db->where('pallet_detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
        $this->db->order_by('pallet_detail.Id asc');
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }

    /**
     * @function searchInventoryToday_swa สำหรับ ค้นหา balance , booked , dispatch realtime ของ swa เท่านั้น!
     * @param type $search
     * @param type $limit_start
     * @param type $limit_max
     * @return type
     */
    function searchInventoryToday_swa($search, $limit_start = 0, $limit_max = 100) {

        #Load config (add by kik : 20140723)
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $inventory_report = empty($conf['show_column_report']['object']['inventory_swa_report']) ? false : @$conf['show_column_report']['object']['inventory_swa_report'];


        $conf_lot = ($inventory_report['lot']) ? true : false;
        $conf_sel = ($inventory_report['serial']) ? true : false;
        $conf_mfd = ($inventory_report['product_mfd']) ? true : false;
        $conf_exp = ($inventory_report['product_exp']) ? true : false;
        $conf_uom_qty = ($inventory_report['uom_qty']['value']) ? true : false;
        $conf_uom_unit_prod = ($inventory_report['uom_unit_prod']['value']) ? true : false;
        $conf_unit = ($inventory_report['unit']['value']) ? true : false;

        if (!empty($inventory_report)):
            $conf_inv = ($conf_inv && $inventory_report['invoice']) ? true : false;
            $conf_cont = ($conf_cont && $inventory_report['container']) ? true : false;
            $conf_pallet = ($conf_pallet && $inventory_report['pallet_code']) ? true : false;
        endif;

        $groupby = array("STK_T_Inbound.Product_Code"
            , "STK_M_Product.PKG"
            , "Product_NameEN"
	    , "Doc_Refer_Ext"
            , "Inbound_Id");


        //แสดง header ในส่วน Product Status
        $status_range = $this->getStatusRange($search['status_id']);
// p($status_range);exit;

        if (array_key_exists('PENDING', $status_range)):
            if (array_key_exists('NORMAL', $status_range)):
               
                $status_range = array_swap_assoc('NORMAL', 'PENDING', $status_range);
                
            endif;
        endif;
        // p($status_range);exit;
        //แสดง จำนวน balance
        $sum_balance = $this->getSqlSearchBalance_swa($status_range, 'STK_T_Inbound');
        $groupBy_qty = 'Product_Code';
        $where_qty = ' (vw.Inbound_Item_Id = dbo.STK_T_Inbound.Inbound_Id) AND (Product_Code = dbo.STK_T_Inbound.Product_Code)  ';


        /**
         * ====================================================================================================
         * Select Zone
         */
        $this->db->select('STK_T_Inbound.Product_Code');
        $this->db->select('Product_NameEN');
	$this->db->select('Doc_Refer_Ext');

        if ($conf_lot):
            $this->db->select('STK_T_Inbound.Product_Lot');
            $groupBy_qty.=',Product_Lot';
            $where_qty.=" AND (Product_Lot = ISNULL(dbo.STK_T_Inbound.Product_Lot,'')) ";
            array_push($groupby, "STK_T_Inbound.Product_Lot");
        endif;

        if ($conf_sel):
            $this->db->select('STK_T_Inbound.Product_Serial');
            $groupBy_qty.=',Product_Serial';
            $where_qty.=" AND (Product_Serial = ISNULL(STK_T_Inbound.Product_Serial,'')) ";
            array_push($groupby, "STK_T_Inbound.Product_Serial");
        endif;

        if ($conf_mfd):
            $this->db->select('convert(varchar(10),STK_T_Inbound.Product_Mfd,103) Product_Mfd');
            $groupBy_qty.=',Product_Mfd';
            $where_qty.=" AND ((CASE WHEN vw.Product_Mfd_sort IS NULL THEN '' ELSE vw.Product_Mfd_sort END)   = (CASE WHEN STK_T_Inbound.Product_Mfd IS NULL THEN '' ELSE STK_T_Inbound.Product_Mfd END)) ";
            array_push($groupby, "STK_T_Inbound.Product_Mfd");
        endif;

        if ($conf_exp):
            $this->db->select('convert(varchar(10),STK_T_Inbound.Product_Exp,103) Product_Exp');
            $groupBy_qty.=',Product_Exp';
            $where_qty.=" AND ((CASE WHEN vw.Product_Exp_sort IS NULL THEN '' ELSE vw.Product_Exp_sort END) = (CASE WHEN STK_T_Inbound.Product_Exp IS NULL THEN '' ELSE STK_T_Inbound.Product_Exp END)) ";
            array_push($groupby, "STK_T_Inbound.Product_Exp");
        endif;


        #check query of Invoice (by kik : 20140708)
        if ($conf_inv):
            $this->db->select("STK_T_Inbound.Invoice_Id,STK_T_Invoice.Invoice_No");
            $groupBy_qty.=',Invoice_Id';
            $where_qty.=' AND (Invoice_Id = STK_T_Inbound.Invoice_Id) ';
            array_push($groupby, "STK_T_Inbound.Invoice_Id");
            array_push($groupby, "STK_T_Invoice.Invoice_No");
        endif; // end of query of Invoice
        #check query of Container (by kik : 20140708)
        if ($conf_cont):
            $this->db->select("STK_T_Inbound.Cont_Id,CAST(CTL_M_Container.Cont_No AS VARCHAR(50)) + ' '+ CAST(CTL_M_Container_Size.Cont_Size_No AS VARCHAR(5))+ ' '+CTL_M_Container_Size.Cont_Size_Unit_Code AS Cont");
            $groupBy_qty.=',Cont_Id';
            $where_qty.=' AND (Cont_Id = STK_T_Inbound.Cont_Id) ';
            array_push($groupby, "STK_T_Inbound.Cont_Id");
            array_push($groupby, "CTL_M_Container.Cont_No");
            array_push($groupby, "CTL_M_Container_Size.Cont_Size_No");
            array_push($groupby, "CTL_M_Container_Size.Cont_Size_Unit_Code");
        endif; // end of query of Container
        #check query of Pallet (by kik : 20140708)
        if ($conf_pallet):
            $this->db->select("STK_T_Pallet.Pallet_Code,STK_T_Inbound.Pallet_Id");
            $groupBy_qty.=',Pallet_Id';
            $where_qty.=' AND (Pallet_Id = STK_T_Inbound.Pallet_Id) ';
            array_push($groupby, "STK_T_Inbound.Pallet_Id");
            array_push($groupby, "STK_T_Pallet.Pallet_Code");
        endif; // end of query of Pallet

  
        $this->db->select($sum_balance);
        $this->db->select("CONVERT(varchar(10),STK_M_Product.PKG * sum(Balance_Qty))+' '+ UOM_Temp.public_name AS Root_Unit");
        $this->db->select('sum(Balance_Qty) as totalbal');
        $this->db->select("ISNULL
                          ((SELECT     SUM(Reserv_Qty) AS total_allow
                              FROM         dbo.vw_inbound_productDetail_booked AS vw
                              WHERE " . $where_qty . "
                              GROUP BY " . $groupBy_qty . "), 0) AS Booked");

        $this->db->select('STK_M_Product.PKG');

        $this->db->select("ISNULL
                          ((SELECT     SUM(Reserv_Qty) AS total_allow
                              FROM         dbo.vw_inbound_productDetail_dispatch AS vw
                              WHERE " . $where_qty . "
                              GROUP BY " . $groupBy_qty . "), 0) AS Dispatch");

        if ($conf_unit):
            $this->db->select('STK_T_Inbound.Unit_Id,UOM_Temp.public_name AS Unit_Value');
            $groupBy_qty.=',Unit_Id';
            $where_qty.=' AND (Unit_Id = STK_T_Inbound.Unit_Id) ';
            array_push($groupby, "STK_T_Inbound.Unit_Id");
            array_push($groupby, "UOM_Temp.public_name");
        endif;


        $this->db->select('0 as Uom_Qty');

        if ($conf_uom_unit_prod):
            $this->db->select('STK_M_Product.Standard_Unit_In_Id,UOM_Temp_Prod.public_name AS Uom_Unit_Val');
            array_push($groupby, "STK_M_Product.Standard_Unit_In_Id");
            array_push($groupby, "UOM_Temp_Prod.public_name");
        endif;
        // End of Select

        /**
         * ====================================================================================================
         * From and Join Zone
         */
        $this->db->from('STK_T_Inbound');
        $this->db->join('STK_M_Location', 'STK_T_Inbound.Actual_Location_Id=STK_M_Location.Location_Id', 'LEFT');
        $this->db->join('STK_M_Product', 'STK_T_Inbound.Product_Id=STK_M_Product.Product_Id', 'LEFT');

        if ($conf_uom_qty || $conf_uom_unit_prod):
            $this->db->join("CTL_M_UOM_Template_Language UOM_Temp_Prod", "STK_M_Product.Standard_Unit_In_Id = UOM_Temp_Prod.CTL_M_UOM_Template_id AND UOM_Temp_Prod.language = '" . $this->config->item('lang3digit') . "'");
        endif;

        if ($conf_unit):
            $this->db->join("CTL_M_UOM_Template_Language UOM_Temp", "STK_T_Inbound.Unit_Id = UOM_Temp.CTL_M_UOM_Template_id AND UOM_Temp.language = '" . $this->config->item('lang3digit') . "'");
        endif;

        #check query of Pallet (by kik : 20140708)
        if ($conf_pallet):
            $this->db->join("STK_T_Pallet", "STK_T_Inbound.Pallet_Id = STK_T_Pallet.Pallet_Id", 'LEFT');
        endif; // end of query of Pallet
        #check query of Invoice (by kik : 20140708)
        if ($conf_inv):
            $this->db->join("STK_T_Invoice", "STK_T_Invoice.Invoice_Id = STK_T_Inbound.Invoice_Id", "LEFT");
        endif; // end of query of Invoice
        #check query of Container  (by kik : 20140708)
        if ($conf_cont):
            $this->db->join("CTL_M_Container", "CTL_M_Container.Cont_Id = STK_T_Inbound.Cont_Id", "LEFT");
            $this->db->join("CTL_M_Container_Size", "CTL_M_Container_Size.Cont_Size_Id = CTL_M_Container.Cont_Size_Id", "LEFT");
        endif; // end of query of Container
        // end of from and join query

        /**
         * ====================================================================================================
         * Where Zone
         */
        $this->db->where('STK_T_Inbound.Active', ACTIVE);
        // p($status_range);exit;
        $this->db->where($this->searchConditionInventory($search, $status_range, 'STK_T_Inbound'));


        /**
         * ====================================================================================================
         * Group Zone
         */
        $this->db->group_by($groupby);
        unset($groupby);


        $this->db->having("sum(Balance_Qty)<>0"); //ADD BY POR 2014-03-12 ให้แสดงเฉพาะ balance มากกว่า 0
        $query = $this->db->get();
        // echo $this->db->last_query();   
        return $query->result();
    }

    function convert_uom($from_uom = NULL, $from_qty = NULL, $to_uom = NULL) {

        if ($from_uom != NULL and $from_qty != NULL and $to_uom != NULL):

            $sql_from = "
                WITH rootChild AS
                    (
                        SELECT *
                        FROM CTL_M_UOM_Template
                        WHERE id = {$from_uom}
                        UNION ALL
                        SELECT ut.*
                        FROM CTL_M_UOM_Template ut
                        JOIN rootChild ON ut.id = rootChild.child_id
                   )
                 SELECT *
                 FROM rootChild
            ";
            $results_from = $this->db->query($sql_from)->result();

            $sql_to = "
                WITH rootChild AS
                    (
                        SELECT *
                        FROM CTL_M_UOM_Template
                        WHERE id = {$to_uom}
                        UNION ALL
                        SELECT ut.*
                        FROM CTL_M_UOM_Template ut
                        JOIN rootChild ON ut.id = rootChild.child_id
                   )
                 SELECT *
                 FROM rootChild
            ";
            $results_to = $this->db->query($sql_to)->result();

            $cal_from = 1;
            $cal_to = 1;

            foreach ($results_from as $key_result => $result):
                if (!empty($result->child_id)):
                    $cal_from *= $result->quantity;
                endif;
            endforeach;
            foreach ($results_to as $key_result => $result):
                if (!empty($result->child_id)):
                    $cal_to *= $result->quantity;
                endif;
            endforeach;

//            p($cal_from);
//            p($cal_to);

            return ($cal_from / $cal_to) * $from_qty;
//
//            p($quotient);

        endif;

        return 0;
    }

    /**
     * สำหรับ เรียกเงื่อนไขในการแสดงค่า balance ของแต่ละสถานะที่สนใจ
     * @author  kik : 2014-04-29
     * @param type $rang
     * @param type $usetable
     * @return type
     */
    function getSqlSearchBalance_swa($rang, $usetable = 'STK_T_Onhand_History') {
        // p($rang);
        $condition = '';
        $i = 1;
        foreach ($rang as $key => $value) {
            // p($value);
            $condition.=$this->sqlInventoryBalance_swa($value, $i, $usetable);
            $i++;
        }
        // p($condition);exit;
        return $condition;
    }

    /**
     * @function sqlInventoryBalance_swa use for gen sql each status for swa only
     * @author  kik:2014-04-29
     * @param type $status
     * @param type $index
     * @param type $useTable
     * @return string
     */
    function sqlInventoryBalance_swa($status_id, $index, $useTable) {
        $sql_option = '';
        
        $this->db->select("Dom_Code");
        $this->db->from("SYS_M_Domain");
        $this->db->where("Dom_Host_Code", 'PROD_STATUS');
        $this->db->where("Dom_Active", 'Y');

        if ($status_id != "") {
            $this->db->where("Dom_EN_Desc", $status_id);
        }

        $query = $this->db->get();
        $result = $query->result();

        $range = array();
        foreach ($result as $row) {
            $range[$row->Dom_Code] = $row->Dom_Code;
        }
 
        $status = $range['NORMAL'];
        
        //this here//

        if ($useTable == 'STK_T_Inbound'):
            $sql_option = ",isnull(SUM(case when " . $useTable . ".Product_Status='$status' then Balance_Qty end),0) as counts_" . $index . "";
        else:
            $sql_option = ",isnull(SUM(case when " . $useTable . ".Product_Status='$status' then Balance_Qty end),0) as counts_" . $index . "";
        endif;

        return $sql_option;
    }


    function inventory_onhand($search, $limit = null, $start = null){
// p($search); exit;

	$where = "";

	/** Search Condition ***/

        //show_cond
        if ($search['show_cond'] == 'cond_prod_code' && $search['txt_keyword'] != '' ) :
            $where .= " AND inventory.Product_Code = '".$search['txt_keyword']."'";
        elseif ($search['show_cond'] == 'cond_prod_name' && $search['txt_keyword'] != '' ) :
            $where .= " AND inventory.Product_NameEN = '".$search['txt_keyword']."' ";
        elseif ($search['show_cond'] == 'cond_doc_ext' && $search['txt_keyword'] != '' ) :
            $where .= " AND inventory.Doc_Refer_Ext = '".$search['txt_keyword']."' ";
        elseif ($search['show_cond'] == 'cond_loc_no' && $search['txt_keyword'] != '' ) :
            $where .= " AND inventory.Location = '".$search['txt_keyword']."' ";
        endif;

        if ($search['show_cond'] != 'cond_all') :

            //กรณีเป็นการใช้ keywords แบบปกติไม่ใช่ from to
            if ($search['cond_deatil'] == 'search_general') :
                //ถ้าเป็น lot serial จะแตกต่างจากกรณีอื่น เนื่องจากสามารถใช้ or ได้
                if ($search['show_cond'] == 'cond_lot_sel') :
                    $where .= " AND (inventory.Product_Lot like '%" . utf8_to_tis620($search['txt_keyword']) . "%' or inventory.Product_Serial like '%" . utf8_to_tis620($search['txt_keyword']) . "%')";
                elseif ($search['show_cond'] == 'cond_rcv_date') :
                    list($d, $m, $y) = explode("/", $search['txt_keyword']);
                    $date_search = "$y-$m-$d";
                    $where .= " AND inventory.Receive_Date like '%" . $search['txt_keyword'] . "%'";
                endif;
            else :

                //ถ้าเป็น lot serial จะแตกต่างจากกรณีอื่น เนื่องจากสามารถใช้ or ได้
                if ($search['show_cond'] == 'cond_lot_sel') :
                    $where .= " AND ((inventory.Product_Lot between '" . utf8_to_tis620($search['txt_from']) . "' And '" . utf8_to_tis620($search['txt_to']) . "') or (inventory.Product_Serial between '" . utf8_to_tis620($search['txt_from']) . "' AND '" . utf8_to_tis620($search['txt_to']) . "'))";
                elseif ($search['show_cond'] == 'cond_rcv_date') :
                    $where .= " AND inventory.Receive_Date between '" . $search['txt_from'] . " 00:00:00  ' and '" . $search['txt_to'] . " 23:59:59'";
                endif;
            endif;
        endif;


	/*** End Search Condition ***/

	/*** Order Condition ***/

        //sort by
        if ($search['sort_by'] == 'sort_prod') :
            $sort_by = ' Order By inventory.Product_Code, inventory.Product_Mfd, inventory.Product_Lot , inventory.location';
        else :
            $sort_by = ' Order By inventory.Location DESC';
        endif;

	/*** End Order Condition ***/



        $sql = " SELECT Inventory.Doc_Refer_Ext,Inventory.Receive_Date,Inventory.Product_Code,Inventory.Product_NameEN,Inventory.Product_Lot,Inventory.Product_Lot,Inventory.Product_Serial,Inventory.Product_Mfd,Inventory.Product_Exp,Inventory.Product_Status,Inventory.Prod_Type,Inventory.location,Inventory.remain,Inventory.Pallet_Code,Inventory.Remark,Inventory.Unit_Value,Inventory.Doc_Refer_Int
        FROM (
         select inb.Doc_Refer_Ext, convert(varchar(10), inb.Receive_Date, 103) Receive_Date, inb.Product_Code, prod.Product_NameEN, inb.Product_Lot, inb.Product_Serial, convert(varchar(10), inb.Product_Mfd, 103) Product_Mfd, convert(varchar(10), inb.Product_Exp, 103) Product_Exp, SYS_M_Domain.Dom_TH_Desc AS Product_Status, prod.User_Defined_4 As Prod_Type, (select location_code from STK_M_Location location where location.Location_Id = inb.Actual_Location_Id) AS location, inb.Balance_Qty - (isnull ((SELECT dispatch FROM vw_location_booked WHERE Inbound_Item_Id = inb.Inbound_Id), 0) + isnull ((SELECT dispatch FROM vw_location_dispatch WHERE Inbound_Item_Id = inb.Inbound_Id), 0)) AS remain, STK_T_Pallet.Pallet_Code, inb.Remark, inb.Unit_Id, 0 as Uom_Qty, S1.public_name AS Unit_Value, prod.Standard_Unit_In_Id, UOM_Temp_Prod.Public_Name AS Uom_Unit_Val, inb.Inbound_Id, '' AS Doc_Refer_Int
         from STK_T_Inbound inb
         LEFT JOIN SYS_M_Domain ON inb.Product_Status = SYS_M_Domain.Dom_Code and SYS_M_Domain.Dom_Host_Code = 'PROD_STATUS' and SYS_M_Domain.Dom_Active = 'Y'
         JOIN STK_M_Product prod ON inb.Product_Id = prod.Product_Id
         JOIN STK_M_Location loc ON inb.Actual_Location_Id = loc.Location_Id
         LEFT OUTER JOIN CTL_M_UOM_Template_Language UOM_Temp_Prod ON prod.Standard_Unit_In_Id = UOM_Temp_Prod.CTL_M_UOM_Template_id AND UOM_Temp_Prod.language = 'eng'
         LEFT OUTER JOIN CTL_M_UOM_Template_Language S1 ON inb.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = 'eng'
         LEFT JOIN STK_T_Pallet ON inb.Pallet_Id = STK_T_Pallet.Pallet_Id
         where inb.Balance_Qty <> 0 and inb.PD_Reserv_Qty >= 0
         AND (inb.Balance_Qty - (isnull ((SELECT dispatch FROM vw_location_booked WHERE Inbound_Item_Id = inb.Inbound_Id), 0) + isnull ((SELECT dispatch FROM vw_location_dispatch WHERE Inbound_Item_Id = inb.Inbound_Id), 0))) > 0
         AND SYS_M_Domain.Dom_Host_Code = 'PROD_STATUS'
         AND inb.active = 'Y'
         
         
         UNION 
         

         select inb.Doc_Refer_Ext, convert(varchar(10), inb.Receive_Date, 103) Receive_Date, inb.Product_Code, prod.Product_NameEN, inb.Product_Lot, inb.Product_Serial, convert(varchar(10), inb.Product_Mfd, 103) Product_Mfd, convert(varchar(10), inb.Product_Exp, 103) Product_Exp, 'RSV' AS Product_Status, prod.User_Defined_4 As Prod_Type, (select location_code from STK_M_Location location where location.Location_Id = inb.Actual_Location_Id) AS location, rsv.qty remain, STK_T_Pallet.Pallet_Code, inb.Remark, inb.Unit_Id, 0 as Uom_Qty, S1.public_name AS Unit_Value, prod.Standard_Unit_In_Id, UOM_Temp_Prod.Public_Name AS Uom_Unit_Val, inb.Inbound_Id, rsv.Doc_Refer_Int AS Doc_Refer_Int
         from STK_T_Inbound inb
         LEFT JOIN SYS_M_Domain ON inb.Product_Status = SYS_M_Domain.Dom_Code and SYS_M_Domain.Dom_Host_Code = 'PROD_STATUS' and SYS_M_Domain.Dom_Active = 'Y'
         JOIN STK_M_Product prod ON inb.Product_Id = prod.Product_Id
         JOIN STK_M_Location loc ON inb.Actual_Location_Id = loc.Location_Id
         LEFT OUTER JOIN CTL_M_UOM_Template_Language UOM_Temp_Prod ON prod.Standard_Unit_In_Id = UOM_Temp_Prod.CTL_M_UOM_Template_id AND UOM_Temp_Prod.language = 'eng'
         LEFT OUTER JOIN CTL_M_UOM_Template_Language S1 ON inb.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = 'eng'
         LEFT JOIN STK_T_Pallet ON inb.Pallet_Id = STK_T_Pallet.Pallet_Id
         LEFT JOIN ( select o.Doc_Refer_Int, Inbound_Item_Id, sum(od.Reserv_Qty) qty
         from STK_T_Order o left join STK_T_Order_Detail od on o.Order_Id = od.Order_Id
         left join STK_T_Workflow w on o.Flow_Id = w.Flow_Id
         where w.Process_Id = '2'
         and w.Present_State in (3, 4, 5, 6)
         and o.Document_No like 'DDR%'
         and od.Active = 'Y'
         group by o.Doc_Refer_Int, Inbound_Item_Id ) RSV on inb.Inbound_Id = rsv.Inbound_Item_Id
         where inb.Balance_Qty <> 0 and inb.PD_Reserv_Qty >= 0
         AND rsv.qty > 0
         AND SYS_M_Domain.Dom_Host_Code = 'PROD_STATUS'
         AND inb.active = 'Y'
         ) Inventory
         WHERE 1=1
	 ".$where."
	 ".$sort_by."
	 ";
//	 echo $sql; exit;
         $this->db->limit($limit, $start);
         $query = $this->db->query($sql);
         $result = $query->result();
        //  p($this->db->last_query() ); exit;
         	//  echo $result; exit;
         return $result;

  
    }

    public function compile_select() {
        return $this->_compile_select();
    }

    function searchLocation_swa($search, $limit = null, $start = null){
        // p($search);
        // exit;

        if($search['inv'] == 'inv'){
            $booking = "and inb.PD_Reserv_Qty = 0";
        }
        elseif($search['inv'] == 'inv_book'){
            $booking = "and inb.PD_Reserv_Qty >= 0";
        
        }
        elseif($search['inv'] == 'book'){
            $booking = "  and inb.PD_Reserv_Qty > 0";
            // p('book');
            // exit;
                
        }
        // p($booking);
        // exit;
        $this->db->select('inb.Doc_Refer_Ext');
        $this->db->select('convert(varchar(10),inb.Receive_Date,103) Receive_Date');
        $this->db->select('inb.Product_Code');
        $this->db->select('prod.Product_NameEN');
        $this->db->select('inb.Product_Lot');
        $this->db->select('inb.Product_Serial');
        $this->db->select('convert(varchar(10),inb.Product_Mfd,103) Product_Mfd');
        $this->db->select('convert(varchar(10),inb.Product_Exp,103) Product_Exp');
        // $this->db->select('inb.Product_Status');
        $this->db->select('SYS_M_Domain.Dom_TH_Desc AS Product_Status' );
	    $this->db->select('prod.User_Defined_4 As Prod_Type');
        $this->db->select('(SELECT     location_code
                            FROM          STK_M_Location location
                            WHERE      location.Location_Id = inb.Actual_Location_Id) AS location');

        $this->db->select('inb.Balance_Qty');
        $this->db->select('isnull
                          ((SELECT     dispatch
                              FROM         vw_location_booked booked
                              WHERE     booked.Inbound_Item_Id = inb.Inbound_Id), 0) AS booked');
        $this->db->select('isnull
                          ((SELECT     dispatch
                              FROM         vw_location_dispatch dispatch
                              WHERE     dispatch.Inbound_Item_Id = inb.Inbound_Id), 0) AS dispatch');
        $this->db->select('inb.Balance_Qty -
                                      (isnull
                          ((SELECT     dispatch
                              FROM         vw_location_booked
                              WHERE     Inbound_Item_Id = inb.Inbound_Id), 0) +
                                      isnull
                          ((SELECT     dispatch
                              FROM         vw_location_dispatch
                              WHERE     Inbound_Item_Id = inb.Inbound_Id), 0)) AS remain');

        if ($this->config->item('build_pallet')) :
            $this->db->select('STK_T_Pallet.Pallet_Code');
        endif;

        $this->db->select('inb.Remark');

        $this->db->select('inb.Unit_Id');

        $this->db->select('0 as Uom_Qty');
        $this->db->select('S1.public_name AS Unit_Value');
        $this->db->select('prod.Standard_Unit_In_Id,UOM_Temp_Prod.Public_Name AS Uom_Unit_Val');


        $this->db->from('STK_T_Inbound inb');
        $this->db->join("SYS_M_Domain", "inb.Product_Status = SYS_M_Domain.Dom_Code and SYS_M_Domain.Dom_Host_Code = 'PROD_STATUS' and SYS_M_Domain.Dom_Active = 'Y'","LEFT");
      //  $this->db->join("SYS_M_Domain","inb.Product_Status = SYS_M_Domain.Dom_Code and SYS_M_Domain.Dom_Host_Code = 'PROD_STATUS' and SYS_M_Domain.Dom_Active = Y","LEFT");
        $this->db->join('STK_M_Product prod', 'inb.Product_Id = prod.Product_Id');
        $this->db->join('STK_M_Location loc', 'inb.Actual_Location_Id = loc.Location_Id');
//        $this->db->join('STK_T_Order od', 'od.Flow_Id = inb.Flow_Id');
//        $this->db->join('STK_T_Workflow wf', 'od.Flow_Id = wf.Flow_Id');
        $this->db->join("CTL_M_UOM_Template_Language UOM_Temp_Prod", "prod.Standard_Unit_In_Id = UOM_Temp_Prod.CTL_M_UOM_Template_id AND UOM_Temp_Prod.language = '" . $this->config->item('lang3digit') . "'", "LEFT OUTER");
        $this->db->join("CTL_M_UOM_Template_Language S1", "inb.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'", "LEFT OUTER");

        if ($this->config->item('build_pallet')) :
            $this->db->join('STK_T_Pallet', 'inb.Pallet_Id = STK_T_Pallet.Pallet_Id', "Left");
        endif;

        $this->db->where('inb.Balance_Qty <> 0 '.$booking.'');
        $this->db->where('SYS_M_Domain.Dom_Host_Code','PROD_STATUS'); //---//
        $this->db->where('inb.active', ACTIVE);
        // $this->db->where($booking);

        //+++++ADD BY POR 2014-05-06 condition search
        //renter_id
        $this->db->where('inb.Renter_Id', $search['renter_id']);
        // $this->db->where($booking);

        //sort by
        if ($search['sort_by'] == 'sort_prod') :
            $sort_by = 'inb.Product_Code, inb.Product_Mfd, inb.Product_Lot ,loc.location_code';
        else :
            $sort_by = 'loc.Location_Code desc';
        endif;
                // $this->db->where($booking);
         $this->db->order_by($sort_by);
        // $this->db->order_by($sort_by, "desc");

        //show_cond
        $column = "";
        if ($search['show_cond'] == 'cond_prod_code') :
            $column = "inb.Product_Code";
        elseif ($search['show_cond'] == 'cond_prod_name') :
            $column = "prod.Product_NameEN";
        elseif ($search['show_cond'] == 'cond_doc_ext') :
            $column = "inb.Doc_Refer_Ext";
        elseif ($search['show_cond'] == 'cond_loc_no') :
            $column = "loc.Location_Code";
        endif;

        //cond_deatil
        $condition = "";
        if ($search['show_cond'] != 'cond_all') :

            //กรณีเป็นการใช้ keywords แบบปกติไม่ใช่ from to
            if ($search['cond_deatil'] == 'search_general') :
                //ถ้าเป็น lot serial จะแตกต่างจากกรณีอื่น เนื่องจากสามารถใช้ or ได้
                if ($search['show_cond'] == 'cond_lot_sel') :
                    $condition = "(inb.Product_Lot like '%" . utf8_to_tis620($search['txt_keyword']) . "%' or inb.Product_Serial like '%" . utf8_to_tis620($search['txt_keyword']) . "%')";
                elseif ($search['show_cond'] == 'cond_rcv_date') :
                    list($d, $m, $y) = explode("/", $search['txt_keyword']);
                    $date_search = "$y-$m-$d";
                    $condition = "convert(varchar(10),inb.Receive_Date,120) like '%" . $date_search . "%'";
                else :
                    $condition = $column . " like '%" . $search['txt_keyword'] . "%'";
                endif;
            else :

                //ถ้าเป็น lot serial จะแตกต่างจากกรณีอื่น เนื่องจากสามารถใช้ or ได้
                if ($search['show_cond'] == 'cond_lot_sel') :
                    $condition = "((inb.Product_Lot between '" . utf8_to_tis620($search['txt_from']) . "' and '" . utf8_to_tis620($search['txt_to']) . "') or (inb.Product_Serial between '" . utf8_to_tis620($search['txt_from']) . "' and '" . utf8_to_tis620($search['txt_to']) . "'))";
                elseif ($search['show_cond'] == 'cond_rcv_date') :
                    //กรณีเป็นการใช้ keywords แบบ from to
                    list($d, $m, $y) = explode("/", $search['txt_from']);
                    $date_from = "$y-$m-$d";

                    list($d, $m, $y) = explode("/", $search['txt_to']);
                    $date_to = "$y-$m-$d";

                    $condition = "convert(varchar(19),inb.Receive_Date,120) between '" . $date_from . " 00:00:00  ' and '" . $date_to . " 23:59:59'";
                else :
                    // $condition = $column . " between '" . $search['txt_from'] . "' and '" . $search['txt_to'] . "'";
                endif;
            endif;
        endif;

        if ($condition != "") :
            $this->db->where($condition);
            // $this->db->where($booking);
        endif;
        //END ADD BY POR

        $this->db->limit($limit, $start);

        $query = $this->db->get();
        $sql = $this->db->last_query();
            //   p($sql);exit;
    //    p($this->db->last_query());
    //    exit;   
        return $query->result();
    }

    //ADD BY POR 2014-05-07 query for auto complete in location history report samwa
    function search_auto_inbound($show_cond, $txt_search, $limit_max = NULL, $limit_start = 0) {
        $value_select = "inb.Product_Code";
        if ($show_cond == 'cond_prod_code'): //กรณีค้นด้วย Product_Code
            $this->db->where("inb.Product_Code like '%" . $txt_search . "%'");
            $value_select = "inb.Product_Code";
        elseif ($show_cond == 'cond_prod_name'):
            $this->db->where("pro.Product_NameEN like '%" . utf8_to_tis620($txt_search) . "%'");
            $value_select = "pro.Product_NameEN";
        elseif ($show_cond == 'cond_doc_ext'):
            $this->db->where("inb.Doc_Refer_Ext like '%" . $txt_search . "%'");
            $value_select = "inb.Doc_Refer_Ext";
        elseif ($show_cond == 'cond_loc_no'):
            $this->db->where("loc.Location_Code like '%" . $txt_search . "%'");
            $value_select = "loc.Location_Code";
        endif;

        $this->db->select("$value_select as value_search");
        $this->db->join('STK_M_Product pro', 'inb.Product_Code = pro.Product_Code', 'left');
        $this->db->join('STK_M_Location loc', 'inb.Actual_Location_Id = loc.Location_Id', 'left');

        $this->db->from('STK_T_Inbound inb');
        $this->db->group_by(array($value_select));
        if (!empty($limit_max)):
            $this->db->limit($limit_max, $limit_start);
        endif;

        $query = $this->db->get();

        $i = 0;
        $data = array();
        foreach ($query->result() as $row) {
            $data[$i]['value_select'] = $row->value_search;
            $i++;
        }
        return $data;
    }

    //กรณีแสดง auto complete ที่ต้องการแสดงทั้ง lot และ serial ใน column เดียวกัน
    function search_auto_lot_serial($show_cond, $txt_search, $limit_max = NULL, $limit_start = 0) {
        $this->db->select("value_search");
        $this->db->from("(SELECT  Product_Lot as value_search
	FROM STK_T_Inbound inb
	LEFT JOIN STK_M_Product pro ON inb.Product_Code = pro.Product_Code
	LEFT JOIN STK_M_Location loc ON inb.Actual_Location_Id = loc.Location_Id
	WHERE Product_Lot is not null and Product_Lot not in ('-','','--')

	UNION

	SELECT  Product_Serial
	FROM STK_T_Inbound inb
	LEFT JOIN STK_M_Product pro ON inb.Product_Code = pro.Product_Code
	LEFT JOIN STK_M_Location loc ON inb.Actual_Location_Id = loc.Location_Id
	WHERE Product_Serial is not null and Product_Serial not in ('-','','--')) as tableb ");

        $this->db->where("value_search like '%" . $txt_search . "%'");
        if (!empty($limit_max)):
            $this->db->limit($limit_max, $limit_start);
        endif;

        $query = $this->db->get();

        $i = 0;
        $data = array();
        foreach ($query->result() as $row) {
            $data[$i]['value_select'] = $row->value_search;
            $i++;
        }
        return $data;
    }

    function searchTallySheet($search) {

        //#Load config by Ken :20150810
        $conf = $this->config->item('_xml');
        $inventory_report = empty($conf['show_column_report']['object']['inventory_swa_report']) ? false : @$conf['show_column_report']['object']['inventory_swa_report'];

        $conf_uom_qty = ($inventory_report['uom_qty']['value']) ? true : false;
        $conf_uom_unit_prod = ($inventory_report['uom_unit_prod']['value']) ? true : false;
        $conf_unit = ($inventory_report['unit']['value']) ? true : false;
    // p($inventory_report, true);

        $this->db->select("distinct pallet.Order_Id
        ,pallet.Cont_Id,cont.Cont_No
        ,o_detail.Product_Id,product.Product_Code,product.Product_NameEN
        ,SUM(pallet_detail.Confirm_Qty) as qty
        ,pallet.Pallet_Code, cont_size.Cont_Size_Unit,Cont_Size_No,Cont_Size_Unit_Code");

        //Add by Ken: 20150810
        $groupby = array();
        $this->db->select("o_detail.Product_Code,o_detail.Product_Lot,o_detail.Product_Serial,o_detail.Unit_Id ,S1.public_name AS Unit_Value");
        $this->db->select('0 as Uom_Qty');
        if ($conf_uom_unit_prod):
            $this->db->select('STK_M_Product.Standard_Unit_In_Id,UOM_Temp_Prod.public_name AS Uom_Unit_Val');
            array_push($groupby, "STK_M_Product.Standard_Unit_In_Id");
            array_push($groupby, "UOM_Temp_Prod.public_name");
            $this->db->group_by($groupby);
        endif;


        $this->db->from("STK_T_Pallet_Detail  pallet_detail");
        $this->db->join('STK_T_Pallet pallet', 'pallet_detail.Pallet_Id = pallet.Pallet_Id', 'left');
        $this->db->join('STK_T_Order_Detail o_detail', 'pallet_detail.Item_Id = o_detail.Item_Id', 'left');
        $this->db->join('STK_M_Product product', 'o_detail.Product_Id = product.Product_Id', 'left');
        $this->db->join('CTL_M_Container cont', 'pallet.Cont_Id = cont.Cont_Id', 'left');
        $this->db->join('CTL_M_Container_Size cont_size', 'cont.Cont_Size_Id = cont_size.Cont_Size_Id', 'left');

        //Add by Ken: 20150810
        $this->db->join("CTL_M_UOM_Template_Language S1", "o_detail.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->join('STK_M_Product', 'o_detail.Product_Id=STK_M_Product.Product_Id', 'LEFT');
        if ($conf_uom_qty || $conf_uom_unit_prod):
            $this->db->join("CTL_M_UOM_Template_Language UOM_Temp_Prod", "STK_M_Product.Standard_Unit_In_Id = UOM_Temp_Prod.CTL_M_UOM_Template_id AND UOM_Temp_Prod.language = '" . $this->config->item('lang3digit') . "'");
        endif;


        $this->db->where("pallet.Cont_Id <> 0");
        $this->db->where('pallet_detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'

        if (!empty($search['order_id'])):
            $this->db->where("pallet.Order_Id", $search['order_id']);
        endif;
        $this->db->group_by(array("pallet.Order_Id", "pallet.Cont_Id", "cont.Cont_No", "o_detail.Product_Id", "product.Product_Code", "product.Product_NameEN", "pallet.Pallet_Code", "cont_size.Cont_Size_Unit", "Cont_Size_No", "Cont_Size_Unit_Code"));

        //Add by Ken: 20150810

        $this->db->group_by(array("o_detail.Product_Code", "o_detail.Product_Lot", "o_detail.Product_Serial", "o_detail.Unit_Id", "S1.public_name"));
        $this->db->order_by("o_detail.Product_Code", "ASC");
        $this->db->order_by("o_detail.Product_Lot", "ASC");
        $this->db->order_by("o_detail.Unit_Id", "ASC");

        $query = $this->db->get();

    //p($this->db->last_query()); exit();
        return $query;
    }

    function searchTallySheet_palletType($search) {
        $this->db->select("count(pallet_detail.Id) as qty_item_in_pallet,pallet.Pallet_Code");
        $this->db->from("STK_T_Pallet_Detail  pallet_detail");
        $this->db->join('STK_T_Pallet pallet', 'pallet_detail.Pallet_Id = pallet.Pallet_Id', 'left');
        $this->db->where("pallet.Cont_Id <> 0");
        $this->db->where('pallet_detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'

        if (!empty($search['order_id'])):
            $this->db->where("pallet.Order_Id", $search['order_id']);
        endif;
        $this->db->group_by(array("pallet.Pallet_Code"));

        $query = $this->db->get();

        // p($this->db->last_query()); exit();
        return $query;
    }

    function searchProductInPallet($chk_pallet_mix = array()) {

        $this->db->select("product.Product_Code ,pallet.Pallet_Code");
        $this->db->from("STK_T_Pallet_Detail  pallet_detail");
        $this->db->join('STK_T_Pallet pallet', 'pallet_detail.Pallet_Id = pallet.Pallet_Id', 'left');
        $this->db->join('STK_T_Order_Detail o_detail', 'pallet_detail.Item_Id = o_detail.Item_Id', 'left');
        $this->db->join('STK_M_Product product', 'o_detail.Product_Id = product.Product_Id', 'left');
        $this->db->where_in("pallet.Pallet_Code", $chk_pallet_mix);
        $this->db->where('pallet_detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'

        $query = $this->db->get();

        // p($this->db->last_query()); exit();
        return $query;
    }

    //Add new funciton :20150810
    function getRemark($order_id = "") {
//        p($order_id,true);
        if (!empty($order_id)) {
            $this->db->select("distinct Product_Id,Product_Lot,Unit_Id , Remark ");
            $this->db->select("(CAST(Product_Id as varchar)+'_'+Product_Lot+'_'+CAST(Unit_Id as varchar)) as GroupProductUnit");
            $this->db->from("STK_T_Order_Detail");
            $this->db->where("Order_Id", $order_id);
            $this->db->order_by("Product_Id", "ASC");
            $this->db->order_by("Product_Lot", "ASC");
            $this->db->order_by("Unit_Id", "ASC");
            $this->db->order_by("Remark", "ASC");
            $result = $this->db->get()->result_array();
            if (!empty($result)) {
                $remark = array();
                foreach ($result as $k => $val) {
                    if (!empty($val['Remark'])) {
                        $remark[$val['GroupProductUnit']][] = $val['Remark'];
                    }
                }
                return $remark;
            }
        }
        return false;
    }
    function searchBooking($search, $limit_start = 0, $limit_max = 100)
    {

        #Load config (add by kik : 20140723)
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $inventory_report = empty($conf['show_column_report']['object']['inventory_swa_report']) ? false : @$conf['show_column_report']['object']['inventory_swa_report'];


        $conf_lot = ($inventory_report['lot']) ? true : false;
        $conf_sel = ($inventory_report['serial']) ? true : false;
        $conf_mfd = ($inventory_report['product_mfd']) ? true : false;
        $conf_exp = ($inventory_report['product_exp']) ? true : false;
        $conf_uom_qty = ($inventory_report['uom_qty']['value']) ? true : false;
        $conf_uom_unit_prod = ($inventory_report['uom_unit_prod']['value']) ? true : false;
        $conf_unit = ($inventory_report['unit']['value']) ? true : false;

        if (!empty($inventory_report)) :
            $conf_inv = ($conf_inv && $inventory_report['invoice']) ? true : false;
            $conf_cont = ($conf_cont && $inventory_report['container']) ? true : false;
            $conf_pallet = ($conf_pallet && $inventory_report['pallet_code']) ? true : false;
        endif;

        $groupby = array(
            "STK_T_Order.Doc_Refer_Ext","STK_T_Inbound.Product_Code", "Product_NameEN", "Inbound_Id"
        );


        //แสดง header ในส่วน Product Status
        $status_range = $this->getStatusRange($search['status_id']);

        if (array_key_exists('PENDING', $status_range)) :
            if (array_key_exists('NORMAL', $status_range)) :
                $status_range = array_swap_assoc('NORMAL', 'PENDING', $status_range);
            endif;
        endif;
        //แสดง จำนวน balance
        $sum_balance = $this->getSqlSearchBalance_swa($status_range, 'STK_T_Inbound');
        $groupBy_qty = 'Product_Code';
        $where_qty = ' (vw.Inbound_Item_Id = dbo.STK_T_Inbound.Inbound_Id) AND (Product_Code = dbo.STK_T_Inbound.Product_Code)  ';


        /**
         * ====================================================================================================
         * Select Zone
         */
        $this->db->select('STK_T_Order.Doc_Refer_Ext');
        $this->db->select('STK_T_Inbound.Product_Code');
        $this->db->select('Product_NameEN');


        if ($conf_lot) :
            $this->db->select('STK_T_Inbound.Product_Lot');
            $groupBy_qty .= ',Product_Lot';
            $where_qty .= " AND (Product_Lot = ISNULL(dbo.STK_T_Inbound.Product_Lot,'')) ";
            array_push($groupby, "STK_T_Inbound.Product_Lot");
        endif;

        if ($conf_sel) :
            $this->db->select('STK_T_Inbound.Product_Serial');
            $groupBy_qty .= ',Product_Serial';
            $where_qty .= " AND (Product_Serial = ISNULL(STK_T_Inbound.Product_Serial,'')) ";
            array_push($groupby, "STK_T_Inbound.Product_Serial");
        endif;

        if ($conf_mfd) :
            $this->db->select('convert(varchar(10),STK_T_Inbound.Product_Mfd,103) Product_Mfd');
            $groupBy_qty .= ',Product_Mfd';
            $where_qty .= " AND ((CASE WHEN vw.Product_Mfd_sort IS NULL THEN '' ELSE vw.Product_Mfd_sort END)   = (CASE WHEN STK_T_Inbound.Product_Mfd IS NULL THEN '' ELSE STK_T_Inbound.Product_Mfd END)) ";
            array_push($groupby, "STK_T_Inbound.Product_Mfd");
        endif;

        if ($conf_exp) :
            $this->db->select('convert(varchar(10),STK_T_Inbound.Product_Exp,103) Product_Exp');
            $groupBy_qty .= ',Product_Exp';
            $where_qty .= " AND ((CASE WHEN vw.Product_Exp_sort IS NULL THEN '' ELSE vw.Product_Exp_sort END) = (CASE WHEN STK_T_Inbound.Product_Exp IS NULL THEN '' ELSE STK_T_Inbound.Product_Exp END)) ";
            array_push($groupby, "STK_T_Inbound.Product_Exp");
        endif;


        #check query of Invoice (by kik : 20140708)
        if ($conf_inv) :
            $this->db->select("STK_T_Inbound.Invoice_Id,STK_T_Invoice.Invoice_No");
            $groupBy_qty .= ',Invoice_Id';
            $where_qty .= ' AND (Invoice_Id = STK_T_Inbound.Invoice_Id) ';
            array_push($groupby, "STK_T_Inbound.Invoice_Id");
            array_push($groupby, "STK_T_Invoice.Invoice_No");
        endif; // end of query of Invoice
        #check query of Container (by kik : 20140708)
        if ($conf_cont) :
            $this->db->select("STK_T_Inbound.Cont_Id,CAST(CTL_M_Container.Cont_No AS VARCHAR(50)) + ' '+ CAST(CTL_M_Container_Size.Cont_Size_No AS VARCHAR(5))+ ' '+CTL_M_Container_Size.Cont_Size_Unit_Code AS Cont");
            $groupBy_qty .= ',Cont_Id';
            $where_qty .= ' AND (ISNULL(Cont_Id , 0) = ISNULL(STK_T_Inbound.Cont_Id, 0)) ';
            array_push($groupby, "STK_T_Inbound.Cont_Id");
            array_push($groupby, "CTL_M_Container.Cont_No");
            array_push($groupby, "CTL_M_Container_Size.Cont_Size_No");
            array_push($groupby, "CTL_M_Container_Size.Cont_Size_Unit_Code");
        endif; // end of query of Container
        #check query of Pallet (by kik : 20140708)
        if ($conf_pallet) :
            $this->db->select("STK_T_Pallet.Pallet_Code,STK_T_Inbound.Pallet_Id");
            $groupBy_qty .= ',Pallet_Id';
            $where_qty .= ' AND (ISNULL(Pallet_Id, 0) = ISNULL(STK_T_Inbound.Pallet_Id, 0)) ';
            array_push($groupby, "STK_T_Inbound.Pallet_Id");
            array_push($groupby, "STK_T_Pallet.Pallet_Code");
        endif; // end of query of Pallet


        $this->db->select($sum_balance);

        $this->db->select('sum(Balance_Qty) as totalbal');
        $this->db->select("ISNULL
                          ((SELECT     SUM(Reserv_Qty) AS total_allow
                              FROM         dbo.vw_inbound_productDetail_booked AS vw
                              WHERE " . $where_qty . "
                              GROUP BY " . $groupBy_qty . "), 0) AS Booked");

        $this->db->select("ISNULL
                          ((SELECT     SUM(Reserv_Qty) AS total_allow
                              FROM         dbo.vw_inbound_productDetail_dispatch AS vw
                              WHERE " . $where_qty . "
                              GROUP BY " . $groupBy_qty . "), 0) AS Dispatch");

        if ($conf_unit) :
            $this->db->select('STK_T_Inbound.Unit_Id,UOM_Temp.public_name AS Unit_Value');
            $groupBy_qty .= ',Unit_Id';
            $where_qty .= ' AND (Unit_Id = STK_T_Inbound.Unit_Id) ';
            array_push($groupby, "STK_T_Inbound.Unit_Id");
            array_push($groupby, "UOM_Temp.public_name");
        endif;


        $this->db->select('0 as Uom_Qty');
        $this->db->select('STK_T_Inbound.Remark As Remark');
        array_push($groupby, "STK_T_Inbound.Remark");

        if ($conf_uom_unit_prod) :
            $this->db->select('STK_M_Product.Standard_Unit_In_Id,UOM_Temp_Prod.public_name AS Uom_Unit_Val');
            array_push($groupby, "STK_M_Product.Standard_Unit_In_Id");
            array_push($groupby, "UOM_Temp_Prod.public_name");
        endif;
        // End of Select

        /**
         * ====================================================================================================
         * From and Join Zone
         */
        $this->db->from('STK_T_Inbound');
        $this->db->join('STK_T_Order', 'STK_T_Inbound.Inbound_Id=STK_T_Order.Order_Id', 'LEFT');
        $this->db->join('STK_M_Location', 'STK_T_Inbound.Actual_Location_Id=STK_M_Location.Location_Id', 'LEFT');
        $this->db->join('STK_M_Product', 'STK_T_Inbound.Product_Id=STK_M_Product.Product_Id', 'LEFT');

        if ($conf_uom_qty || $conf_uom_unit_prod) :
            $this->db->join("CTL_M_UOM_Template_Language UOM_Temp_Prod", "STK_M_Product.Standard_Unit_In_Id = UOM_Temp_Prod.CTL_M_UOM_Template_id AND UOM_Temp_Prod.language = '" . $this->config->item('lang3digit') . "'");
        endif;

        if ($conf_unit) :
            $this->db->join("CTL_M_UOM_Template_Language UOM_Temp", "STK_T_Inbound.Unit_Id = UOM_Temp.CTL_M_UOM_Template_id AND UOM_Temp.language = '" . $this->config->item('lang3digit') . "'");
        endif;

        #check query of Pallet (by kik : 20140708)
        if ($conf_pallet) :
            $this->db->join("STK_T_Pallet", "STK_T_Inbound.Pallet_Id = STK_T_Pallet.Pallet_Id", 'LEFT');
        endif; // end of query of Pallet
        #check query of Invoice (by kik : 20140708)
        if ($conf_inv) :
            $this->db->join("STK_T_Invoice", "STK_T_Invoice.Invoice_Id = STK_T_Inbound.Invoice_Id", "LEFT");
        endif; // end of query of Invoice
        #check query of Container  (by kik : 20140708)
        if ($conf_cont) :
            $this->db->join("CTL_M_Container", "CTL_M_Container.Cont_Id = STK_T_Inbound.Cont_Id", "LEFT");
            $this->db->join("CTL_M_Container_Size", "CTL_M_Container_Size.Cont_Size_Id = CTL_M_Container.Cont_Size_Id", "LEFT");
        endif; // end of query of Container
        // end of from and join query

        /**
         * ====================================================================================================
         * Where Zone
         */
        $this->db->where('STK_T_Inbound.Active', ACTIVE);
        // $this->db->where($this->searchConditionInventory($search, $status_range, 'STK_T_Inbound'));
        $this->db->where("1=1 AND STK_T_Inbound.Renter_Id=2 AND STK_T_Inbound.Product_Status IN ('PENDING','NORMAL','DAMAGE','REPACK','BORROW')");
        // p($this->searchConditionInventory($search, $status_range, 'STK_T_Inbound'));
        // exit;

        /**
         * ====================================================================================================
         * Group Zone
         */
        $this->db->group_by($groupby);
        unset($groupby);


        $this->db->having("sum(Balance_Qty)<>0"); //ADD BY POR 2014-03-12 ให้แสดงเฉพาะ balance มากกว่า 0
        $query = $this->db->get();
        $sql = $this->db->last_query();
        // p($sql);
        // exit;
        return $query->result();
    }
    function get_expired_ecolab($search){
 
        $this->db->select("convert(varchar(10), inb.Receive_Date, 103) Receive_Date ,
        DATEDIFF(DAY, inb.Receive_Date, GETDATE())+1 Aging ,
        ISNULL(STK_T_Pallet.Pallet_Code, '') Pallet_Code ,
        inb.Product_Code,
        prod.Product_NameEN,
        inb.Product_Lot ,
        ISNULL((DATEDIFF(DAY, GETDATE(), inb.Product_Exp)-1) - ((CONVERT(INT,ISNULL(prod.Min_Aging, 0)))-(CONVERT(INT,ISNULL(prod.Min_Aging, 0)*0.8))), '') Remain_Shelf_life ,
        ISNULL(DATEDIFF(DAY, GETDATE(), inb.Product_Exp)-1, '') Remain_Product_life ,
        ISNULL(CONVERT(varchar(10), inb.Product_Mfd, 103), '') MFD_Date ,
        ISNULL(CONVERT(varchar(10), inb.Product_Exp, 103), '') EXP_DATE ,
        ISNULL(CONVERT(INT,ISNULL(prod.Min_Aging, 0)*0.8), '') Shelf_life ,
        ISNULL(CONVERT(INT,ISNULL(prod.Min_Aging, 0)), '') Life ,
        inb.Doc_Refer_Ext INVOICENO ,
        inb.Receive_Qty BeginQty ,
        inb.Balance_Qty - (isnull ((SELECT dispatch FROM vw_location_booked WHERE Inbound_Item_Id = inb.Inbound_Id), 0) + isnull ((SELECT dispatch FROM vw_location_dispatch WHERE Inbound_Item_Id = inb.Inbound_Id), 0)) AS BalQty ,
        (SELECT Company_NameEN   FROM CTL_M_Company Com   WHERE Com.Company_Id = inb.Owner_Id) AS Location_Alias ,
        dom.Dom_TH_Desc SubInventoryName ,
        prod.User_Defined_4 AS Prod_Type ,
        (SELECT location_code   FROM STK_M_Location LOCATION   WHERE location.Location_Id = inb.Actual_Location_Id) AS LOCATION ,
        UOM_Temp_Prod.Public_Name AS Uom_Unit_Val ,
        (CASE
             WHEN ISNULL((DATEDIFF(DAY, GETDATE(), inb.Product_Exp)-1),0) > 180 THEN 'Life > 180 Days'
             WHEN ISNULL((DATEDIFF(DAY, GETDATE(), inb.Product_Exp)-1),0) >= 120 THEN '120 - 180 Days'
             WHEN ISNULL((DATEDIFF(DAY, GETDATE(), inb.Product_Exp)-1),0) > 0 THEN '0 - 120 Days'
             ELSE 'Expired'
         END) Product_Life_Status
        ,inb.Inbound_Id");
        $this->db->from("STK_T_Inbound inb");
        $this->db->join("STK_M_Product prod","inb.Product_Id = prod.Product_Id");
        $this->db->join("STK_M_Location loc","inb.Actual_Location_Id = loc.Location_Id");
        $this->db->join("CTL_M_UOM_Template_Language UOM_Temp_Prod","prod.Standard_Unit_In_Id = UOM_Temp_Prod.CTL_M_UOM_Template_id AND UOM_Temp_Prod.language = 'eng'","left outer");
        $this->db->join("CTL_M_UOM_Template_Language S1","inb.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = 'eng'","left outer");
        $this->db->join("STK_T_Pallet","inb.Pallet_Id = STK_T_Pallet.Pallet_Id","LEFT");
        $this->db->join("SYS_M_Domain dom","inb.Product_Status = dom.Dom_Code AND dom.Dom_Host_Code = 'PROD_STATUS' AND dom.Dom_Active = 'Y'","LEFT");
        $this->db->where("inb.Balance_Qty <> 0");
        $this->db->where("inb.active","Y");
        $this->db->where("inb.Renter_Id","1");
        $this->db->where("(inb.Balance_Qty - (isnull ((SELECT dispatch FROM vw_location_booked WHERE Inbound_Item_Id = inb.Inbound_Id), 0) + isnull ((SELECT dispatch FROM vw_location_dispatch WHERE Inbound_Item_Id = inb.Inbound_Id), 0))) > 0");
        $query1 = $this->db->return_query(FALSE);


        $this->db->select("convert(varchar(10), inb.Receive_Date, 103) Receive_Date ,
        DATEDIFF(DAY, inb.Receive_Date, GETDATE())+1 Aging ,
        ISNULL(STK_T_Pallet.Pallet_Code, '') Pallet_Code ,
        inb.Product_Code,
        prod.Product_NameEN,
        inb.Product_Lot ,
        ISNULL((DATEDIFF(DAY, GETDATE(), inb.Product_Exp)-1) - ((CONVERT(INT,ISNULL(prod.Min_Aging, 0)))-(CONVERT(INT,ISNULL(prod.Min_Aging, 0)*0.8))), '') Remain_Shelf_life ,
        ISNULL(DATEDIFF(DAY, GETDATE(), inb.Product_Exp)-1, '') Remain_Product_life ,
        ISNULL(CONVERT(varchar(10), inb.Product_Mfd, 103), '') MFD_Date ,
        ISNULL(CONVERT(varchar(10), inb.Product_Exp, 103), '') EXP_DATE ,
        ISNULL(CONVERT(INT,ISNULL(prod.Min_Aging, 0)*0.8), '') Shelf_life ,
        ISNULL(CONVERT(INT,ISNULL(prod.Min_Aging, 0)), '') Life ,
        inb.Doc_Refer_Ext INVOICENO ,
        inb.Receive_Qty BeginQty ,
        rsv.Reserv_Qty AS BalQty ,
        (SELECT Company_NameEN   FROM CTL_M_Company Com   WHERE Com.Company_Id = inb.Owner_Id) AS Location_Alias ,
        'RSV' SubInventoryName ,
        prod.User_Defined_4 AS Prod_Type ,
        (SELECT location_code   FROM STK_M_Location LOCATION   WHERE location.Location_Id = inb.Actual_Location_Id) AS LOCATION ,
        UOM_Temp_Prod.Public_Name AS Uom_Unit_Val ,
        (CASE
             WHEN ISNULL((DATEDIFF(DAY, GETDATE(), inb.Product_Exp)-1),0) > 180 THEN 'Life > 180 Days'
             WHEN ISNULL((DATEDIFF(DAY, GETDATE(), inb.Product_Exp)-1),0) >= 120 THEN '120 - 180 Days'
             WHEN ISNULL((DATEDIFF(DAY, GETDATE(), inb.Product_Exp)-1),0) > 0 THEN '0 - 120 Days'                                 
             ELSE 'Expired'
         END) Product_Life_Status
        ,inb.Inbound_Id");
        $this->db->from("STK_T_Inbound inb");
        $this->db->join("STK_M_Product prod","inb.Product_Id = prod.Product_Id");
        $this->db->join("STK_M_Location loc","inb.Actual_Location_Id = loc.Location_Id");
        $this->db->join("CTL_M_UOM_Template_Language UOM_Temp_Prod","prod.Standard_Unit_In_Id = UOM_Temp_Prod.CTL_M_UOM_Template_id AND UOM_Temp_Prod.language = 'eng'","left outer");
        $this->db->join("CTL_M_UOM_Template_Language S1","inb.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = 'eng'","left outer");
        $this->db->join("STK_T_Pallet","inb.Pallet_Id = STK_T_Pallet.Pallet_Id","LEFT");
        $this->db->join("SYS_M_Domain dom","inb.Product_Status = dom.Dom_Code AND dom.Dom_Host_Code = 'PROD_STATUS' AND dom.Dom_Active = 'Y'","LEFT");
        $this->db->join("( select STK_T_Order_Detail.Inbound_Item_Id,  SUM(STK_T_Order_Detail.Reserv_Qty) AS Reserv_Qty
                        from STK_T_Order_Detail 
                        join STK_T_Order ON STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id
                        join STK_T_Workflow ON STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id 
                        where 1=1
                        AND STK_T_Workflow.Process_Id = '2'
                        AND STK_T_Workflow.Present_State IN ('3','4','5','6')
                        AND STK_T_Order.Document_No like 'DDR%'
                        AND STK_T_Order_Detail.Active = 'Y'
                        group by STK_T_Order_Detail.Inbound_Item_Id,
                        STK_T_Order_Detail.Reserv_Qty) AS rsv "," inb.Inbound_Id = rsv.Inbound_Item_Id","LEFT");
        $this->db->where("inb.Balance_Qty <> 0");
        $this->db->where("inb.PD_Reserv_Qty >= 0");
        $this->db->where("RSV.Reserv_Qty > 0");
        $this->db->where("inb.active","Y");
        $query2 = $this->db->return_query(FALSE);
    
       //  p($query1);exit;

                if (!empty($search['product_id'])){
                    $product_id = $search['product_id'];
                    $where .= " AND prodd.Product_Id = '$product_id' ";
                }

                    $query = $this->db->query("SELECT Expired.Receive_Date,
                                            Expired.Aging, 
                                            Expired.Pallet_Code,
                                            Expired.Product_Code,
                                            Expired.Product_NameEN,
                                            Expired.Product_Lot ,
                                            Expired.Remain_Shelf_life,
                                            Expired.Remain_Product_life,
                                            Expired.MFD_Date,
                                            Expired.EXP_DATE,
                                            Expired.Shelf_life,
                                            Expired.Life,
                                            Expired.INVOICENO,
                                            Expired.BeginQty,
                                            Expired.BalQty,
                                            Expired.Location_Alias,
                                            Expired.SubInventoryName,
                                            Expired.Product_Life_Status FROM ($query1 UNION ALL $query2) AS Expired
                                            JOIN STK_M_Product prodd ON prodd.Product_Code = Expired.Product_Code
                                            WHERE 1=1"
                                            .$where.
                                            "ORDER BY Expired.Product_Code,Expired.MFD_Date,Expired.Product_Lot,Expired.Location_Alias");


                // // if(!empty($search['product_status'])){
                // //     $product_status = $search['product_status'];

                // //     if($product_status == 'NORMAL'){
                // //         $sql .= "AND Expired.SubInventoryName IN ('AVB','RSV')";
                // //     }else{
                // //         $sql .= "AND dom.Dom_Code =  '$product_status'";
                // //     // $sql .= "AND inb.Product_Status =  '$product_status'";
                // //     }
                // // }
                // $query = $this->db->query($sql);
                // $query = $this->db->get();
                // $sql = $this->db->last_query();
                // p($this->db->last_query()); exit;
                // $qe = $this->db->last_query();
                $result = $query->result();
                return $result;
    }


}
