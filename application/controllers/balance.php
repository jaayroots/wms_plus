<?php

# add by kik
# 02-10-2013
# calculate and check balance

class balance extends CI_Controller {

    public $settings;

    public function __construct() {
        parent::__construct();
        $this->load->model("stock_model", "stock");
        $this->load->model("inbound_model", "inbound");
        $this->load->model("product_model", "product");
        $this->load->model("re_location_model", "rl");

        $this->settings = native_session::retrieve();
    }

    public function _chkPDreservBeforeOpen($ci_reserv_qty, $ci_inbound_id, $prod_list, $delimiter) {

# by kik , 03-10-2013
#
# หลักการเช็คจำนวนที่เหลืออยู่ว่าพอกับจำนวนที่จะจองใหม่หรือไม่
# ตรวจสอบว่า จำนวนที่ต้องการจอง มีมากกว่า จำนวนที่เหลืออยู่ (Balance_Qty - PD_Reserv_Qty) หรือไม่
# ถ้าใช่ จะไม่สามารถ open ได้
# ถ้าไม่ใช่ ทำงานขั้นตอนต่อไปได้
#parameter
# 1. ตำแหน่งของ reserv_qty ในหน้า form (ตำแหน่งจาก dataTable)
# 2. ตำแหน่งของ inbound_id ในหน้า form (ตำแหน่งจาก dataTable)
# 3. ตำแหน่งของ item_id  ในหน้า form (ตำแหน่งจาก dataTable)
# 4. array item ของ order detail ที่รับมาจากหน้า form

        $remainQtyInbound = array();    //get Balance_Qty - PD_Reserv_Qty from inbound     (จำนวนที่เหลืออยู่)
        $reservQty = array();    //get data from form                                (จำนวนที่จะจองใหม่)
        $show_reservQty = array();
        $resultCompare = 1;

        if (!empty($prod_list)) {
            foreach ($prod_list as $key_rows => $rows) :

                $a_data = explode($delimiter, $rows);

                if (empty($remainQtyInbound[$a_data[$ci_inbound_id]])) {
                    $tmp = 0;

                    $tmp = $this->stock->getBalanceByInboundId($a_data[$ci_inbound_id]);
                    $remainQtyInbound[$a_data[$ci_inbound_id]] = $tmp[0]->remain_qty;
                }

                if (empty($reservQty[$a_data[$ci_inbound_id]])) {
                    $reservQty[$a_data[$ci_inbound_id]] = 0;
                }
                if (empty($show_reservQty[$a_data[$ci_inbound_id]][$key_rows])) {
                    $show_reservQty[$a_data[$ci_inbound_id]][$key_rows] = 0;
                }

                $res_qty = str_replace(",", "", $a_data[$ci_reserv_qty]); //+++++ADD BY POR 2013-11-29 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                $reservQty[$a_data[$ci_inbound_id]] += $res_qty;
                $show_reservQty[$a_data[$ci_inbound_id]][$key_rows] += $res_qty;

                $table_row[$a_data[$ci_inbound_id]][] = $key_rows;

            endforeach;

            foreach($show_reservQty as $key_sh_reserv => $val_sh_reserv):
                $show_reservQty[$key_sh_reserv] = array_values($val_sh_reserv);
            endforeach;

            $return = array();
            foreach ($remainQtyInbound as $key => $remainQty):
#ตรวจสอบว่า จำนวนที่จะจองใหม่ มีมากกว่า จำนวนที่เหลืออยู่ หรือไม่ ถ้าใช่ = 0
                if ($reservQty[$key] > $remainQty) :
//                    $resultCompare = 0;
                    foreach ($table_row[$key] as $key_tb_row => $tb_row):
                        $line_no = $tb_row + 1;

                        if ($show_reservQty[$key][$key_tb_row] > $remainQty) :
                            $set_return = array(
                                //                            'message' => "In line NO. '{$line_no}' Est.Balance QTY ({$remainQty}) <  Reserve QTY ({$reservQty[$key]}) Please check again. ",
                                'message' => "In line NO. '{$line_no}' Est.Balance QTY ({$remainQty}) <  Reserve QTY ({$show_reservQty[$key][$key_tb_row]}) Please check again. ",
                                'row' => (string) $tb_row,
                                'col' => (string) $ci_reserv_qty
                            );
                        else:
                            $set_return = array(
                                'message' => "In line NO. '{$line_no}' This product is 'Over Est.Balance', Please check other item again. ",
//                                'message' => "",
                                'row' => (string) $tb_row,
                                'col' => (string) $ci_reserv_qty
                            );
                        endif;
                        $return['critical'][] = $set_return;

                        $remainQty -= $show_reservQty[$key][$key_tb_row];
                        $remainQty = ($remainQty < 0 ? 0 : $remainQty);
                    endforeach;
                endif;
            endforeach;

//            p($return);
//            exit();
//
//            $return = "";
//
//            if ($resultCompare == 0) {
//                $return = "0"; // incomplete
//            } else {
//                $return = "1"; // complete
//            }

            return $return;
        }
    }

    public function _chkPDreservBeforeApprove($ci_reserv_qty, $ci_inbound_id, $ci_item_id, $orderid, $prod_list, $delimiter, $useTable = 'STK_T_Order_Detail') {
    
# by kik , 02-10-2013
#
# หลักการเช็ค PD_reserv หรือ จำนวนที่ถูกจองไว้ใน inbound ว่าเหลือพอที่ order ปัจจุบันจะใช้หรือไม่
# 1. วนแต่ละ item เพื่อหรือค่ามาตรวจสอบ ดังต่อไปนี้
# 2. หาค่า balance ของ inbound_id จาก inbound table
# 3. หาค่า PD_reserv ของ inbound_id จาก inbound table
# 4. หาค่า old reserv ของแต่ละ inbound_id จาก order Detail
# 5. หาค่า new reserv ของแต่ละ inbound_id จาก form ที่รับค่ามาใหม่
# 6. หาค่า จำนวนที่ถูกจองไว้โดย order อื่น
# 7. หาค่า จำนวนที่เหลือจากการจอง
# 8. เปรียบเทียบ จำนวนที่จองใหม่ กับ จำนวนที่เหลืออยู่
# 9. ถ้าค่าที่จองใหม่ มีน้อยกว่าหรือเท่ากับ จำนวนที่เหลืออยู่ ทำงานต่อไปได้
# 10. ถ้าค่าที่จองใหม่ มีมากกว่า จำนวนที่เหลืออยู่ จะไม่สามารถ approve ได้
#parameter
# 1. ตำแหน่งของ reserv_qty ในหน้า form (ตำแหน่งจาก dataTable)
# 2. ตำแหน่งของ inbound_id ในหน้า form (ตำแหน่งจาก dataTable)
# 3. ตำแหน่งของ item_id  ในหน้า form (ตำแหน่งจาก dataTable)
# 4. order id
# 5. array item ของ order detail ที่รับมาจากหน้า form

        $BalanceInbound = array();    //get Balance from inbound      (จำนวนที่มีอยู่จริง)
        $PDreservInbound = array();    //get PDreserv from inbound     (จำนวนทั้งหมดที่ถูกจองอยู่)
        $oldReserv = array();    //get data from order detail    (จำนวนเก่าที่จองไว้)
        $newReserv = array();    //get data from form            (จำนวนที่จะจองใหม่)
        $remainPDreserv = array();    //$PDreservInbound - $oldReserv (เป็นจำนวนที่ order อื่นจองเอาไว้ โดยหักจาก order ปัจจุบันไปแล้ว)
        $remainBalance = array();    //$balance - $remainPDreserv    (เป็นจำนวนที่เหลืออยู่ สามารถจองได้จริง โดยยังไม่รวมกับ order ปัจจุบัน)
        $resultCompare = 1;          //ผลลัพธ์จากการ compare ระหว่าง จำนวนที่เหลืออยู่ กับ จำนวนที่จะจองใหม่


        foreach ($prod_list as $key_rows => $rows) :
            if (is_object($rows)):  //in case quick approve:2015-01-22
                $a_data = object_to_array($rows);
                $order_id = $a_data[$orderid];
            else:
                $a_data = explode($delimiter, $rows);
                $order_id = $orderid;
            endif;

            if (empty($oldPD[$a_data[$ci_inbound_id]])) {

                if (empty($BalanceInbound[$a_data[$ci_inbound_id]])) {

                    $tmp1 = 0;
                    $tmp2 = 0;

                    $oldReserv[$a_data[$ci_inbound_id]] = 0;
                    $PDreservInbound[$a_data[$ci_inbound_id]] = 0;
                    $BalanceInbound[$a_data[$ci_inbound_id]] = 0;

                    if ($useTable == 'STK_T_Relocate_Detail') {
                        $tmp1 = $this->stock->getSumReservQtyRelocateDetailByOrderId($order_id, $a_data[$ci_inbound_id]);
                    } else if ($useTable == 'STK_T_Order_Detail') {
                        $tmp1 = $this->stock->getSumReservQtyOrderDetailByOrderId($order_id, $a_data[$ci_inbound_id]);
                    }

                    $tmp2 = $this->stock->getBalanceByInboundId($a_data[$ci_inbound_id]);

                    $oldReserv[$a_data[$ci_inbound_id]] = @$tmp1['sumReserv'];


                    $PDreservInbound[$a_data[$ci_inbound_id]] = ( isset($tmp2[0]) ? $tmp2[0]->PD_Reserv_Qty : 0 );
                    $BalanceInbound[$a_data[$ci_inbound_id]] = ( isset($tmp2[0]) ? $tmp2[0]->Balance_Qty : 0 );
                    
                }
            }

            if (empty($newReserv[$a_data[$ci_inbound_id]])) {
                $newReserv[$a_data[$ci_inbound_id]] = 0;
            }

            $res_qty = str_replace(",", "", $a_data[$ci_reserv_qty]); //+++++ADD BY POR 2013-12-02 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
            $newReserv[$a_data[$ci_inbound_id]] += $res_qty; //ADD BY POR 2013-12-02 เรียกใช้ตัวแปรที่สร้างใหม่

            $table_row[$a_data[$ci_inbound_id]] = $key_rows;

        endforeach;

        $return = array();
        foreach ($BalanceInbound as $key => $Balance):
            #หาจำนวนที่ถูก order อื่นจองไว้ โดยไม่รวม order ปัจจุบัน
            $remainPDreserv[$key] = $PDreservInbound[$key] - $oldReserv[$key];

            #หาจำนวนที่เหลืออยู่ ที่สามารถจองได้
            $remainBalance[$key] = $Balance - $remainPDreserv[$key];

            #ตรวจสอบว่า จำนวนที่จะจองใหม่ มีมากกว่า จำนวนที่เหลืออยู่ หรือไม่ ถ้าใช่ = 0
            if (number_format($newReserv[$key] , 3 , '.' , '') > number_format($remainBalance[$key] , 3 , '.' , '')) {
                $line_no = $table_row[$key] + 1;
                $set_return = array(
                    'message' => "In line NO. '{$line_no}' Est.Balance QTY ({$remainBalance[$key]}) <  Reserve QTY ({$newReserv[$key]}) Please check again. ",
                    'row' => (string) $table_row[$key],
                    'col' => (string) $ci_reserv_qty
                );

                $return['critical'][] = $set_return;
            }

        endforeach;

    //    $return = "";
//
    //    if ($resultCompare == 0) {
    //        $return = "0"; // incomplete
    //    } else {
    //        $return = "1"; // complete
    //    }
  
// p( $return);
        return $return;
    }

// Add By Akkarapol, 03/10/2013, เพิ่มฟังก์ชั่น เพื่อทำการ คำนวน qty ต่างๆและนำไป update ข้อมูลในตาราง inbound

    /**
     * @funtion updatePDReservQty for work update inbound qty when cut-off balance
     * @author Akkarapol, 03/10/2013
     * @param int $ci_reserv_qty
     * @param int $ci_inbound_id
     * @param int $ci_item_id
     * @param int $order_id
     * @param array $prod_list
     * @param string $delimiter
     * @return boolean
     *
     * @last_modified kik 20140307
     *
     */
    public function updatePDReservQty($ci_reserv_qty, $ci_inbound_id, $ci_item_id, $order_id, $prod_list, $delimiter = NULL) {

        $return_val = TRUE;
        foreach ($prod_list as $rows) :

            if ($delimiter == NULL) :
                $a_data = explode(SEPARATOR, $rows);
            else :
                $a_data = explode($delimiter, $rows);
            endif;

            $inboundId = $a_data[$ci_inbound_id];
            $qty = str_replace(",", "", $a_data[$ci_reserv_qty]); //+++++ADD BY POR 2013-11-29 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก

            if (!empty($ci_inbound_id) && $ci_inbound_id != ""):

                $balance = $this->stock->getBalanceByInboundId($a_data[$ci_inbound_id]);

                if (!empty($balance)):
                    $setDataForUpdateInbound = array();
                    $tmp_reserv_qty = 0;
                    $tmp_balance_qty = 0;

                    $tmp_reserv_qty = $balance[0]->PD_Reserv_Qty - $qty;
                    $tmp_balance_qty = $balance[0]->Balance_Qty - $qty;

                    $setDataForUpdateInbound['PD_Reserv_Qty'] = (($tmp_reserv_qty <= 0) ? 0 : $tmp_reserv_qty);
                    $setDataForUpdateInbound['Dispatch_Qty'] = $balance[0]->Dispatch_Qty + $qty;
                    $setDataForUpdateInbound['Balance_Qty'] = (($tmp_balance_qty <= 0) ? 0 : $tmp_balance_qty);

                    #add by kik : for active N when balance = 0 : 20140331
                    if ($tmp_balance_qty <= 0):
                        $setDataForUpdateInbound['Active'] = 'N';
                    endif;

                    $setWhereForUpdateInbount['Inbound_Id'] = $inboundId;

                    $result = $this->stock->updateInboundDetail($setDataForUpdateInbound, $setWhereForUpdateInbount);

                    if (!$result):
                        $return_val = FALSE;
                    endif;

                    unset($tmp_balance_qty);
                    unset($tmp_reserv_qty);
                    unset($setDataForUpdateInbound);
                endif;

            else:

                $return_val = FALSE;

            endif;

        endforeach;

        return $return_val;
    }

// END Add By Akkarapol, 03/10/2013, เพิ่มฟังก์ชั่น เพื่อทำการ คำนวน qty ต่างๆและนำไป update ข้อมูลในตาราง inbound
// Add By Ball, 17/10/2013, เพิ่มฟังก์ชั่น เพื่อทำการ คำนวน qty ต่างๆและนำไป update ข้อมูลในตาราง inbound
    public function updateOpenPDReservQty($ci_reserv_qty, $ci_inbound_id, $prod_list, $delimiter = NULL) {
        $check_not_err = TRUE; //กำหนดค่าเริ่มต้นไม่ให้มี error
        foreach ($prod_list as $rows) :

            if ($delimiter == NULL) :
                $a_data = explode(SEPARATOR, $rows);
            else :
                $a_data = explode($delimiter, $rows);
            endif;

            $inboundId = $a_data[$ci_inbound_id];
            $qty = str_replace(",", "", $a_data[$ci_reserv_qty]); //+++++ADD BY POR 2013-11-29 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก

            $balance = $this->stock->getBalanceByInboundId($a_data[$ci_inbound_id]);
            $setDataForUpdateInbound['PD_Reserv_Qty'] = $balance[0]->PD_Reserv_Qty + $qty;

            $setWhereForUpdateInbount['Inbound_Id'] = $inboundId;
            $status_update = $this->stock->updateInboundDetail($setDataForUpdateInbound, $setWhereForUpdateInbount);
            if (!$status_update): //ถ้าไม่มีการ update แสดงว่าไม่พบข้อมูล หรือข้อมูลไม่ถูกต้อง
                $check_not_err = FALSE;
                break;
            endif;
        endforeach;

        return $check_not_err;
    }

// END Add By Ball, 17/10/2013, เพิ่มฟังก์ชั่น เพื่อทำการ คำนวน qty ต่างๆและนำไป update ข้อมูลในตาราง inbound
// Add By Ball, 17/10/2013,
// re-update confirm pd
    public function updateConfirmPDReservQty($ci_reserv_qty, $ci_inbound_id, $order_id, $prod_list, $delimiter = NULL) {

        foreach ($prod_list as $rows) :

            if ($delimiter == NULL) :
                $a_data = explode(SEPARATOR, $rows);
            else :
                $a_data = explode($delimiter, $rows);
            endif;

            $inboundId = $a_data[$ci_inbound_id];
            $qty = str_replace(",", "", $a_data[$ci_reserv_qty]); //+++++ADD BY POR 2013-11-29 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก

            $balance = $this->stock->getBalanceByInboundId($a_data[$ci_inbound_id]);
            $relocate = $this->stock->getSumReservQtyRelocateDetailByOrderId($order_id, $a_data[$ci_inbound_id]);

            $setDataForUpdateInbound['PD_Reserv_Qty'] = (($balance[0]->PD_Reserv_Qty - $relocate['sumReserv']) + $qty);

            $setWhereForUpdateInbount['Inbound_Id'] = $inboundId;
            $status_update = $this->stock->updateInboundDetail($setDataForUpdateInbound, $setWhereForUpdateInbount);

        endforeach;

        return $status_update;
    }

    //ADD BY POR 2014-06-16 function สำหรับ update PD ใน inbound ด้วยค่าที่ Confirm
    public function updateaInboundPDReservQty($ci_reserv_qty, $ci_inbound_id, $order_id, $prod_list, $delimiter = NULL) {

        foreach ($prod_list as $rows) :

            if ($delimiter == NULL) :
                $a_data = explode(SEPARATOR, $rows);
            else :
                $a_data = explode($delimiter, $rows);
            endif;

            $inboundId = $a_data[$ci_inbound_id];
            $qty = str_replace(",", "", $a_data[$ci_reserv_qty]); //+++++ADD BY POR 2013-11-29 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก

            $balance = $this->stock->getBalanceByInboundId($a_data[$ci_inbound_id]);
            $relocate = $this->stock->getSumConfirmQtyRelocateDetailByOrderId($order_id, $a_data[$ci_inbound_id]);

            $setDataForUpdateInbound['PD_Reserv_Qty'] = (($balance[0]->PD_Reserv_Qty - $relocate['sumReserv']) + $qty);

            $setWhereForUpdateInbount['Inbound_Id'] = $inboundId;
            $status_update = $this->stock->updateInboundDetail($setDataForUpdateInbound, $setWhereForUpdateInbount);

        endforeach;

        return $status_update;
    }

    //END ADD
// END Add By Ball, 17/10/2013, re-update confirm pd
#add by kik : 15-10-2013
    public function showProductEstInbound($inbound_id = null) {
        $inboundId = $this->input->post("inbound_id");
        $query = $this->inbound->getProductDetailsByInboundId($inboundId);

#########################################
        $result = $query->result();
        $action = array();
        $action_module = "";
        $column = array(
            "Id"
            , "Process"
            , "State"
            , "Qty");
        $data_list = new ArrayObject(); // add by kik : 2013-11-12
        if (is_array($result) && count($result)) {
            foreach ($result as $key => $rows) {
                $data['Id'] = $key + 1;
                $data['Process_NameEn'] = $rows->Process_NameEn;
                $data['State_NameEn'] = $rows->State_NameEn;
                $data['Qty'] = set_number_format($rows->Reserv_Qty);
                $data_list[] = (object) $data;
            }
        }
//        p($data_list);
        $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);

#########################################
        echo $datatable;
    }

#end add by kik : 15-10-2013
#add by kik : 15-10-2013

    public function showProductEstInboundForInventory() {

        $Product_Code = $this->input->post("Product_Code");
        $Product_Lot = $this->input->post("Product_Lot");
        $Product_Serial = $this->input->post("Product_Serial");
        $Product_Mfd = $this->input->post("Product_Mfd");
        $Product_Exp = $this->input->post("Product_Exp");

        $query = $this->inbound->getvw_inb_ProductDetails($Product_Code, $Product_Lot, $Product_Serial, $Product_Mfd, $Product_Exp);

#########################################
        $result = $query->result(); //p($query);exit();
        $action = array();
        $action_module = "";
        $column = array(
            "Id"
            , "Product_Status"
            , "Process"
            , "State"
            , "Qty");
        $data_list = new ArrayObject(); // add by kik : 2013-11-12
        if (is_array($result) && count($result)) {
            foreach ($result as $key => $rows) {
                $data['Id'] = $key + 1;
                $data['Product_Status'] = $rows->Product_Status;
                $data['Process_NameEn'] = $rows->Process_NameEn;
                $data['State_NameEn'] = $rows->State_NameEn;
                $data['Qty'] = set_number_format($rows->Reserv_Qty);
                $data_list[] = (object) $data;
            }
        }
//        p($data_list);
        $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);

#########################################

        echo $datatable;
    }

#end add by kik : 15-10-2013
// Add By Akkarapol, 30/01/2014, เพิ่มฟังก์ชั่นสำหรับเช็คค่าของ Re Order Point กับ Qty ที่ต้องการว่าสามารถให้ทำการ dispatch ได้หรือไม่

    public function chk_re_order_point($ci_reserv_qty, $ci_prod_code, $prod_list, $delimiter) {

        /**
         * set Variable
         */
        $return = array();

        /**
         * chk config re_order_point
         */
        if ($this->settings['re_order_point']['active']):
            if (!empty($prod_list)) :
                $how_many_product = array();

                foreach ($prod_list as $key => $rows) :
                    $a_data = explode($delimiter, $rows);
                    if (empty($how_many_product[$a_data[$ci_prod_code]])):
                        $how_many_product[$a_data[$ci_prod_code]] = (float) str_replace(",", "", $a_data[$ci_reserv_qty]);
                    else:
                        $how_many_product[$a_data[$ci_prod_code]] = $how_many_product[$a_data[$ci_prod_code]] + (float) str_replace(",", "", $a_data[$ci_reserv_qty]);
                    endif;

                    $table_row[$a_data[$ci_prod_code]] = $key;

                endforeach;

                foreach ($how_many_product as $key_per_product => $per_product):

                    $re_order_point = $this->product->getProductDetailByProductCode($key_per_product, 'Re_Order_Point')->row();

                    if (!empty($re_order_point)): //check empty :BY POR 2014-11-26
                        if ($re_order_point->Re_Order_Point == ACTIVE):
                            $all_qty_of_product = $this->inbound->get_all_qty_of_product($key_per_product)->row();
                            $all_qty_of_product = $all_qty_of_product->All_Balance_Qty;

                            $product_id = $this->product->getProductDetailByProductCode($key_per_product, 'Product_Id')->row();

                            $filter_re_order_point = array(
                                'column' => array(
                                    //                            'warning_text',
                                    //                            'qty',
                                    //                            'pass'
                                    '*'
                                ),
                                'where' => array(
                                    'product_id' => $product_id->Product_Id
                                ),
                                'order_by' => 'STK_M_Re_Order_Point.qty ASC'
                            );

                            $get_re_order_point = $this->product->get_re_order_point($filter_re_order_point['column'], $filter_re_order_point['where'], $filter_re_order_point['order_by'])->result_array();

                            if (empty($get_re_order_point)):
                                $get_re_order_point = $this->settings['re_order_point']['object'];
                                ksort($get_re_order_point);
                            endif;

                            foreach ($get_re_order_point as $key_re_order_point => $re_order_point):
                                if (empty($re_order_point['active'])):
                                    $re_order_point['active'] = ACTIVE;
                                endif;
                                if ($re_order_point['active'] == ACTIVE):
                                    $all_of_reserv = $all_qty_of_product - $re_order_point['qty'];
                                    $cal = $all_qty_of_product - $per_product;

                                    $cal = (float) str_replace(',', '', set_number_format($cal));
                                    $Re_Order_Point = (float) str_replace(',', '', set_number_format($re_order_point['qty']));

                                    // Set variable for use Dynamic warning text
                                    $product_code = $key_per_product;
                                    $total_qty = $all_qty_of_product;
                                    $selected_qty = $all_of_reserv;
                                    $remain_qty = $cal;

                                    $for_find = array(
                                        '{product_code}',
                                        '{re_order_point}',
                                        '{total_qty}',
                                        '{selected_qty}',
                                        '{remain_qty}'
                                    );
                                    $for_replace = array(
                                        $product_code,
                                        $Re_Order_Point,
                                        $total_qty,
                                        $selected_qty,
                                        $remain_qty
                                    );

                                    if ($cal < $Re_Order_Point):

                                        $warning_text = str_replace($for_find, $for_replace, $re_order_point['warning_text']);
                                        //                            $return['message'][$re_order_point['pass']][] = 'Re Order Point of product "' . $key_per_product . '" = "' . $re_order_point['qty'] . '" , But remain = "' . $cal . '" , Now in stock have = "' . $all_qty_of_product . '" You can Reserv Qty = "' . $all_of_reserv . '" please, check this.';
                                        //                                    $return['message'][$re_order_point['pass']][] = $warning_text;
                                        //                                    if ($re_order_point['pass'] == INACTIVE):
                                        //                                        $return['pass'] = INACTIVE;
                                        //                                        break;
                                        //                                    endif;

                                        $set_return['message'] = $warning_text;
                                        $set_return['row'] = (string) $table_row[$key_per_product];
                                        $set_return['col'] = (string) $ci_reserv_qty;

                                        $return[$re_order_point['type']][] = $set_return;

                                        break;

                                    endif;
                                endif;

                            endforeach;

                        endif;
                    endif;
                endforeach;


            endif;
//        else:
//            $return = array(
//                'pass' => ACTIVE
//            );
//            return $return;
        endif;

        return $return;
    }

// END Add By Akkarapol, 30/01/2014, เพิ่มฟังก์ชั่นสำหรับเช็คค่าของ Re Order Point กับ Qty ที่ต้องการว่าสามารถให้ทำการ dispatch ได้หรือไม่
    // Add By Akkarapol, 30/01/2014, เพิ่มฟังก์ชั่นสำหรับเช็คค่าของ Re Order Point กับ Qty ที่ต้องการว่าสามารถให้ทำการ dispatch ได้หรือไม่ ในขั้นตอนของการ confirm และ approve
    public function chk_re_order_point_before_approve($ci_reserv_qty, $ci_prod_code, $ci_item_id, $orderid, $prod_list, $delimiter, $useTable = 'STK_T_Order_Detail') {

        /**
         * set Variable
         */
        $return = array();

        if ($this->settings['re_order_point']['active']):

            if (!empty($prod_list)) :

                $BalanceInbound = array();    //get Balance from inbound      (จำนวนที่มีอยู่จริง)
                $PDreservInbound = array();    //get PDreserv from inbound     (จำนวนทั้งหมดที่ถูกจองอยู่)
                $oldReserv = array();    //get data from order detail    (จำนวนเก่าที่จองไว้)
                $newReserv = array();    //get data from form            (จำนวนที่จะจองใหม่)
                $remainPDreserv = array();    //$PDreservInbound - $oldReserv (เป็นจำนวนที่ order อื่นจองเอาไว้ โดยหักจาก order ปัจจุบันไปแล้ว)
                $remainBalance = array();    //$balance - $remainPDreserv    (เป็นจำนวนที่เหลืออยู่ สามารถจองได้จริง โดยยังไม่รวมกับ order ปัจจุบัน)
                $resultCompare = 1;          //ผลลัพธ์จากการ compare ระหว่าง จำนวนที่เหลืออยู่ กับ จำนวนที่จะจองใหม่

                foreach ($prod_list as $key => $rows) :
                    if (is_object($rows)):  //in case quick approve:2015-01-22
                        $a_data = object_to_array($rows);
                        $order_id = $a_data[$orderid];
                    else:
                        $a_data = explode($delimiter, $rows);
                        $order_id = $orderid;
                    endif;

                    if (empty($oldPD[$a_data[$ci_prod_code]])) {

                        if (empty($BalanceInbound[$a_data[$ci_prod_code]])) {

                            $tmp1 = 0;
                            $tmp2 = 0;

                            $oldReserv[$a_data[$ci_prod_code]] = 0;
                            $PDreservInbound[$a_data[$ci_prod_code]] = 0;
                            $BalanceInbound[$a_data[$ci_prod_code]] = 0;

                            if ($useTable == 'STK_T_Relocate_Detail') {
                                $tmp1 = $this->stock->getSumReservQtyRelocateDetailByProductCode($order_id, $a_data[$ci_prod_code])->row_array();
                            } else if ($useTable == 'STK_T_Order_Detail') {
                                $tmp1 = $this->stock->getSumReservQtyOrderDetailByProductCode($order_id, $a_data[$ci_prod_code])->row_array();
                            }

                            $tmp2 = $this->stock->getBalanceByProductCode($a_data[$ci_prod_code])->row();

                            $oldReserv[$a_data[$ci_prod_code]] = @$tmp1['sumReserv'];
                            $PDreservInbound[$a_data[$ci_prod_code]] = $tmp2->PD_Reserv_Qty;
                            $BalanceInbound[$a_data[$ci_prod_code]] = $tmp2->Balance_Qty;
                        }
                    }

                    if (empty($newReserv[$a_data[$ci_prod_code]])) {
                        $newReserv[$a_data[$ci_prod_code]] = 0;
                    }

                    $res_qty = str_replace(",", "", $a_data[$ci_reserv_qty]);
                    $newReserv[$a_data[$ci_prod_code]] += $res_qty;

                    $table_row[$a_data[$ci_prod_code]] = $key;
                endforeach;


                foreach ($BalanceInbound as $key => $Balance):

                    $re_order_point = $this->product->getProductDetailByProductCode($key, 'Re_Order_Point')->row();

                    if (!empty($re_order_point)): //check empty :BY POR 2014-11-26
                        if ($re_order_point->Re_Order_Point == ACTIVE):

                            $product_id = $this->product->getProductDetailByProductCode($key, 'Product_Id')->row();

                            $filter_re_order_point = array(
                                'column' => array(
                                    //                            'warning_text',
                                    //                            'qty',
                                    //                            'pass'
                                    '*'
                                ),
                                'where' => array(
                                    'product_id' => $product_id->Product_Id
                                ),
                                'order_by' => 'STK_M_Re_Order_Point.qty ASC'
                            );

                            $get_re_order_point = $this->product->get_re_order_point($filter_re_order_point['column'], $filter_re_order_point['where'], $filter_re_order_point['order_by'])->result_array();

                            if (empty($get_re_order_point)):
                                $get_re_order_point = $this->settings['re_order_point']['object'];
                                ksort($get_re_order_point);
                            endif;

                            foreach ($get_re_order_point as $key_re_order_point => $re_order_point):
                                if (empty($re_order_point['active'])):
                                    $re_order_point['active'] = ACTIVE;
                                endif;
                                if ($re_order_point['active'] == ACTIVE):

                                    $remainPDreserv[$key] = $PDreservInbound[$key] - $oldReserv[$key];
                                    $remainBalance[$key] = $Balance - $remainPDreserv[$key];
                                    $all_of_reserv = $remainBalance[$key] - $re_order_point['qty'];
                                    $cal = $remainBalance[$key] - $newReserv[$key];
                                    $all_qty_of_product = $remainBalance[$key];

                                    $cal = (float) str_replace(',', '', set_number_format($cal));
                                    $Re_Order_Point = (float) str_replace(',', '', set_number_format($re_order_point['qty']));

                                    // Set variable for use Dynamic warning text
                                    $product_code = $key;
                                    $total_qty = $all_qty_of_product;
                                    $selected_qty = $all_of_reserv;
                                    $remain_qty = $cal;

                                    $for_find = array(
                                        '{product_code}',
                                        '{re_order_point}',
                                        '{total_qty}',
                                        '{selected_qty}',
                                        '{remain_qty}'
                                    );
                                    $for_replace = array(
                                        $product_code,
                                        $Re_Order_Point,
                                        $total_qty,
                                        $selected_qty,
                                        $remain_qty
                                    );
                                    if ($cal < $Re_Order_Point):
                                        //                                    $warning_text = str_replace($for_find, $for_replace, $re_order_point['warning_text']);
                                        //                                    $return['message'][$re_order_point['pass']][] = $warning_text;
                                        //                                    if ($re_order_point['pass'] == INACTIVE):
                                        //                                        $return['pass'] = INACTIVE;
                                        //                                        break;
                                        //                                    endif;


                                        $warning_text = str_replace($for_find, $for_replace, $re_order_point['warning_text']);
                                        //                            $return['message'][$re_order_point['pass']][] = 'Re Order Point of product "' . $key_per_product . '" = "' . $re_order_point['qty'] . '" , But remain = "' . $cal . '" , Now in stock have = "' . $all_qty_of_product . '" You can Reserv Qty = "' . $all_of_reserv . '" please, check this.';
                                        //                                    $return['message'][$re_order_point['pass']][] = $warning_text;
                                        //                                    if ($re_order_point['pass'] == INACTIVE):
                                        //                                        $return['pass'] = INACTIVE;
                                        //                                        break;
                                        //                                    endif;

                                        $set_return['message'] = $warning_text;
                                        $set_return['row'] = (string) $table_row[$product_code];
                                        $set_return['col'] = (string) $ci_reserv_qty;

                                        $return[$re_order_point['type']][] = $set_return;

                                        break;

                                    endif;

                                endif;
                            endforeach;
                        endif;
                    endif;
                endforeach;
                return $return;
            endif;
        else:
            $return = array(
                'pass' => ACTIVE
            );
            return $return;
        endif;
    }

// END Add By Akkarapol, 30/01/2014, เพิ่มฟังก์ชั่นสำหรับเช็คค่าของ Re Order Point กับ Qty ที่ต้องการว่าสามารถให้ทำการ dispatch ได้หรือไม่ ในขั้นตอนของการ confirm และ approve
    #ADD BY POR : 2015-09-07 สำหรับจัดการ กรณี relo pallet เพื่อให้ update location ใน pallet ให้ถูกต้อง
    public function updatePalletLocation($order_id){

        #ดึง detail ทั้งหมดออกมา
        $result = $this->rl->GetRelocateDetail($order_id)->result();

        #หา balance ของ inbound ทั้งหมดที่อยู่ใน รายการนี้
        $inbound_qty = array();
        if(!empty($result) && is_array($result)):
            #หาว่ามี inbound อะไรบ้าง
            foreach ($result as $val_inb) :
                if(is_null($inbound_qty[$val_inb->Inbound_Item_Id]['Confirm_Qty']) || empty($inbound_qty[$val_inb->Inbound_Item_Id]['Confirm_Qty'])):
                    $inbound_qty[$val_inb->Inbound_Item_Id]['Confirm_Qty'] = 0;
                endif;

                $inbound_qty[$val_inb->Inbound_Item_Id]['Inbound_Id'] = $val_inb->Inbound_Item_Id;
                $inbound_qty[$val_inb->Inbound_Item_Id]['Confirm_Qty'] +=  $val_inb->Confirm_Qty;
                $inbound_qty[$val_inb->Inbound_Item_Id]['Remain_Qty'] = $val_inb->Remain_Qty;
                $inbound_qty[$val_inb->Inbound_Item_Id]['Item_Id'] = $val_inb->Item_Id;
                $inbound_qty[$val_inb->Inbound_Item_Id]['Actual_Location_Id'] = $val_inb->Actual_Location_Id;
                $inbound_qty[$val_inb->Inbound_Item_Id]['Pallet_Id'] = $val_inb->Pallet_Id;
            endforeach;
        endif;

        if(!empty($inbound_qty) && is_array($inbound_qty)):
            $remain = 0;
            foreach ($inbound_qty as $key => $val) :
                #ตรวจสอบว่า inbound ถูก relocation ทั้งหมดหรือไม่
                $remain = $val['Remain_Qty'] - $val['Confirm_Qty'];

                #ถ้า remain = 0 แสดงว่าเลือก relocation ทั้ง pallet ให้ทำการ update location ในตาราง Pallet ตาม item สุดท้าย ของ inbound นั้น ส่วน Item ที่เหลือถือว่าไม่ได้อยู่ใน pallet แล้ว ให้ update เป็น Null
                if($remain == 0):
                    #update ทุกรายการให้เป็น NULL
                    $data_pallet['Pallet_Id'] = NULL;
                    $where_pallet['Order_Id'] = $order_id;
                    $where_pallet['Inbound_Item_Id'] = $val['Inbound_Id'];
                    $update_relocation_notpallet = $this->util_query_db->update_table("STK_T_Relocate_Detail",$data_pallet,$where_pallet);
                    if(!$update_relocation_notpallet):
                        return FALSE;
                    endif;

                    unset($data_pallet);
                    unset($where_pallet);

                    #update รายการสุดท้ายให้คง pallet ไว้
                    $data_pallet['Pallet_Id'] = $val['Pallet_Id'];
                    $where_pallet['Item_Id'] = $val['Item_Id'];
                    $where_pallet['Order_Id'] = $order_id;
                    $update_relocation_pallet = $this->util_query_db->update_table("STK_T_Relocate_Detail",$data_pallet,$where_pallet);
                    if(!$update_relocation_pallet):
                        return FALSE;
                    endif;

                    unset($data_pallet);
                    unset($where_pallet);

                    #update ตาราง Pallet ให้เป็น Location ตาม Item สุดท้าย
                    $data_pallet['Actual_Location_Id'] = $val['Actual_Location_Id'];
                    $where_pallet['Pallet_Id'] = $val['Pallet_Id'];
                    $update_pallet = $this->util_query_db->update_table("STK_T_Pallet",$data_pallet,$where_pallet);
                    if(!$update_pallet):
                        return FALSE;
                    endif;

                    unset($data_pallet);
                    unset($where_pallet);
                else: #กรณี remain ไม่เท่ากับ 0 แสดงว่า item ทั้งหมดถูกดึงออกจาก pallet ดังนั้นให้ update Pallet_Id ให้เป็น NULL
                    #update ทุกรายการให้เป็น NULL
                    $data_pallet['Pallet_Id'] = NULL;
                    $where_pallet['Order_Id'] = $order_id;
                    $where_pallet['Inbound_Item_Id'] = $val['Inbound_Id'];
                    $update_relocation_notpallet = $this->util_query_db->update_table("STK_T_Relocate_Detail",$data_pallet,$where_pallet);
                    if(!$update_relocation_notpallet):
                        return FALSE;
                    endif;
                    unset($data_pallet);
                    unset($where_pallet);
                endif;

            endforeach;
        endif;

        return TRUE;
    }

    public function checkRelocationHH($ci_reserv_qty, $ci_inbound_id, $ci_item_id, $orderid, $prod_list, $delimiter, $useTable = 'STK_T_Relocate_Detail') {

        $this->load->library('stock_lib');

        $return = array();

        $pre_dispatch_area_id = $this->stock_lib->getPreDispatchArea();

        foreach ($prod_list as $key_rows => $rows) :

            if (is_object($rows)):  //in case quick approve:2015-01-22
                $a_data = object_to_array($rows);
                $order_id = $a_data[$orderid];
            else:
                $a_data = explode($delimiter, $rows);
                $order_id = $orderid;
            endif;

            $inbound_id = $a_data[$ci_inbound_id];
            $validHH = $this->stock->validateReservQtyRelocateDetailByOrderId($order_id, $inbound_id, $pre_dispatch_area_id);

            $line_no = $key_rows + 1;

            if ($validHH['sumReserv'] == 0) {
                $set_return = array(
                    'message' => "In line NO. '{$line_no}' We found your stock in process Relocation HH, Please finish your work first. ",
                    'row' => (string) $line_no,
                    'col' => (string) $ci_reserv_qty
                );

                $return['critical'][] = $set_return;
            }

        endforeach;

        return $return;
    }

}

?>
