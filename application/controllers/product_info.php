<?php

// Create by Ton! 20130614
//Location: ./application/controllers/product_info.php
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class product_info extends CI_Controller {

    public $pallet;

    function __construct() {
        parent::__construct();
        $this->load->model("location_model", "loc");
        $this->load->model("warehouse_model", "wh");
        $this->load->model("zone_model", "zone");
        $this->load->model("storage_model", "stor");
        $this->load->model("putaway_model", "put");
        $this->load->model("stock_model", "stock");
        $this->load->model("product_info_model", "info");
        $this->load->model("encoding_conversion", "encode");
        $this->load->model("inbound_model", "inbound");  // add by kik(16-12-2013)
        $this->load->model("system_management_model", "sys");//add by kik (20140312)
        $this->load->model("product_model", "product");

        $this->load->controller('balance', 'balance'); // add by kik(16-12-2013)

        $this->pallet = $this->config->item('build_pallet'); //ADD BY POR 2014-12-17 เพิ่มให้เรียกใช้ว่ามีการประกาศใช้ pallet หรือไม่
    }

    function getDataProd() {
        // <<<<<------ START COMMENT OUT by Ton! 20130903 ------>>>>>
//        $order_Id = $this->input->post('orderId');
//        $prodCode = $this->input->post('prodCode');
//
//        $querySplit = $this->info->getProdSplitInfo($order_Id, $prodCode);
        // <<<<<------ START COMMENT OUT by Ton! 20130903 ------>>>>>
        // <<<<<------ START ADD by Ton! 20130903 ------>>>>>
        $itemId = $this->input->post('itemId');
        $querySplit = $this->info->getDetailSplitInfo($itemId);
        // <<<<<------ END ADD by Ton! 20130903 ------>>>>>

        $split_list = $querySplit->result();
        echo json_encode($split_list);
    }

    function splitProd($itemId = "") {

        if ("" == $itemId) {
            $itemId = $this->input->get_post('itemId');
        }
        $queryProd = $this->info->getSplitInfo($itemId);
        $prod_list = $queryProd->result();

        $order_Id = "";
        $DocumentNo = "";
        $DocReferExt = "";
        $ReservType = ""; //???
        $ProductCode = "";
        $ProductNameEN = "";
        $ConfirmQty = ""; // EDIT by Ton! 20130903

         /**
         * fix Split : Receive Type display as CODE : by kik : 20140312
         * #Get Receive Type list
         */
        $r_receive_type = $this->sys->getReceiveType();
        $receive_list = genOptionDropdown($r_receive_type, "SYS", TRUE, TRUE);


        if ($prod_list != "") {
            foreach ($prod_list as $prodList) {
            	$order_Id = $prodList->Order_Id;
                $DocumentNo = $prodList->Document_No;
                $DocReferExt = $prodList->Doc_Refer_Ext;
                $ReservType = $prodList->Doc_Type; //???
                $ProductCode = $prodList->Product_Code;
                $ProductNameEN = $prodList->Product_NameEN;
				$PutAwayRule = $prodList->PutAway_Rule;
                $ConfirmQty = $prodList->Confirm_Qty; // EDIT by Ton! 20130903
                $ReservType = $receive_list[$ReservType]; // fix Split : Receive Type display as CODE : by kik : 20140312
            }
        }

        $this->load->helper('form');
//        $str_form=form_fieldset('Product Information & Split');
        $str_form = $this->parser->parse('form/split_product', array("prod_list" => $prod_list, "orderId" => $order_Id, "itemId" => $itemId, "DocumentNo" => $DocumentNo, "DocReferExt" => $DocReferExt
            , "ReservType" => $ReservType, "ProductCode" => $ProductCode, "ProductNameEN" => $ProductNameEN, "ConfirmQty" => $ConfirmQty, "PutAwayRule" => $PutAwayRule), TRUE); //"ReservQty" => $ReservQty // COMMENT OUT & EDIT by Ton! 20130903

        $this->parser->parse('form_modal_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Product Information & Split'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => ''
//            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="Close" ONCLICK="closePage()" ID="close">' // comment by kik : 08-11-2013
            , 'button_clear' => ''      // add by kik : 08-11-2013
            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="submitFrm()" ID="save">'
        ));
    }

    function validationProdSpilt() {// Add by Ton! 20130715
        $data = $this->input->post();
        $i = 0;
        foreach ($data['lot'] as $lot) {
            if ($lot == "") {
                if ($data['lot_serial'][$i] == "") {
//                    echo json_encode("NOT_OK");// COMMENT OUT by Ton! 20130903
                    echo json_encode("LS");
                    return;
                }
            }
            $i++;
        }
        // <<<<<------ END ADD Check by Ton! 20130903 ------>>>>>
        //
        // <<<<<------ START COMMENT OUT by Ton! 20130903 ------>>>>>
//        foreach ($data['lot'] as $lot) {
//            if ($lot == "") {
//                echo json_encode("NOT_OK");
//                return;
//            }
//        }
//
//        foreach ($data['lot_serial'] as $serial) {
//            if ($serial == "") {
//                echo json_encode("NOT_OK");
//                return;
//            }
//        }
        // <<<<<------ END COMMENT OUT by Ton! 20130903 ------>>>>>

        foreach ($data['lot_qty'] as $qty) {
            if ($qty == "") {
                echo json_encode("QTY"); // ADD by Ton! 20130903
                return;
            }
        }

        foreach ($data['lot_prod_mfd'] as $mfd) {

        	// 2014-06-11 not require.
        	/*if ($mfd == "") {
                echo json_encode("MFD");
                return;
            }*/

        	/*
            if ($data['PutAwayRule'] == 'FIFO'):
                $chk = $this->chkMFDDateOfProduct($data['ProductCode'], $mfd);
                if ($chk != 'Ok') :
                    echo json_encode(iconv("TIS-620", "UTF-8", $chk));
                    return;
                endif;

            endif;
            */

            // END Add By Akkarapol, 29/01/2014, เพิ่มการตรวจสอบว่า Product MFD. ที่เลือกมานั้น มี ShelfLife ตามที่เซ็ตค่าใน Master หรือไม่
        }

        foreach ($data['lot_prod_exp'] as $exp) {

            if ($data['PutAwayRule'] == 'FEFO'):
                if ($exp == "") {
    //                echo json_encode("NOT_OK");// COMMENT OUT by Ton! 20130903
                    echo json_encode("EXP");
                    return;
                }


                $chk = $this->chkExpDateOfProduct($data['ProductCode'], $exp);
                if ($chk != 'Ok') :
                    echo json_encode(iconv("TIS-620", "UTF-8", $chk));
                    return;
                endif;

            endif;
            // END Add By Akkarapol, 29/01/2014, เพิ่มการตรวจสอบว่า Product Exp. ที่เลือกมานั้น มี Aging ตามที่เซ็ตค่าใน Master หรือไม่
        }
        echo json_encode("OK");
        return;
    }

    function saveProdSplit() {// Add by Ton! 20130620
        $data = $this->input->post();

        $datestring = "%Y-%m-%d %h:%i:%s";
        $time = time();
        $this->load->helper("date");
        $human = mdate($datestring, $time);

//        get product Information
        // <<<<<------ START COMMENT OUT by Ton! 20130903 ------>>>>>
//        $query = $this->info->getProdSplitInfo($data['order_Id'], $data['ProductCode']);\
        // <<<<<------ END COMMENT OUT by Ton! 20130903 ------>>>>>
        // <<<<<------ START ADD Check by Ton! 20130903 ------>>>>>
        $query = $this->info->getDetailSplitInfo($data['item_Id']);
        // <<<<<------ END ADD Check by Ton! 20130903 ------>>>>>

        $prod_list = $query->result(); //P($prod_list);exit();
//        update order detail (INACTIVE)
        $sDetail = array();
        $swhere = array();
        foreach ($prod_list as $p) {
            $sDetail['Active'] = INACTIVE;
            $swhere['Item_Id'] = $p->Item_Id;

            $this->stock->updateOrderDetail($sDetail, $swhere);
        }

//        add new order detail split
        $i = 0;
        $order_detail = array();
        foreach ($data['lot_qty'] as $sQtyLot) {
            $detail = array();
            foreach ($prod_list as $pList) {
                $detail['Order_Id'] = $pList->Order_Id;
                $detail['Product_Id'] = $pList->Product_Id;
                $detail['Product_Code'] = $pList->Product_Code;
                $detail['Product_Status'] = $pList->Product_Status;
                $detail['Product_Sub_Status'] = $pList->Product_Sub_Status;
                $detail['Suggest_Location_Id'] = $pList->Suggest_Location_Id;
                $detail['Actual_Location_Id'] = $pList->Actual_Location_Id;
                $detail['Old_Location_Id'] = $pList->Old_Location_Id;
                $detail['Pallet_Id'] = $pList->Pallet_Id;
                $detail['Product_License'] = $pList->Product_License;
                $detail['Product_Lot'] = $data['lot'][$i];
                $detail['Product_Serial'] = $data['lot_serial'][$i];
                $detail['Product_Mfd'] = convertDate($data['lot_prod_mfd'][$i], "eng", "iso", "-");
                $detail['Product_Exp'] = convertDate($data['lot_prod_exp'][$i], "eng", "iso", "-");
                $detail['Reserv_Qty'] = str_replace(",", "", $sQtyLot);//add str_replace by kik : 2014-3-22
                $detail['Confirm_Qty'] = str_replace(",", "", $sQtyLot);//add str_replace by kik : 2014-3-22
                $detail['Unit_Id'] = $pList->Unit_Id;
                $detail['Reason_Code'] = $pList->Reason_Code;
                $detail['Reason_remark'] = $pList->Reason_Remark;
                $detail['Remark'] = $pList->Remark;
                $detail['Inbound_Item_Id'] = $pList->Inbound_Item_Id;
                $detail['Split_From_Item_Id'] = $pList->Item_Id;
                $detail['Active'] = ACTIVE;
                $detail['Activity_Code'] = $pList->Activity_Code;
                $detail['Activity_By'] = $this->session->userdata('user_id'); //$this->data['UserLogin_Id'];
                $detail['Activity_Date'] = $human;
                $detail['Pallet_From_Item_Id'] = $pList->Pallet_From_Item_Id;   //add by kik : 20140722 duplicate data into new record
                $detail['Partial_From_Item_Id'] = $pList->Partial_From_Item_Id; //add by kik : 20140722 duplicate data into new record
                $detail['Invoice_Id'] = $pList->Invoice_Id;                     //add by kik : 20140722 duplicate data into new record
                $detail['Cont_Id'] = $pList->Cont_Id;                           //add by kik : 20140722 duplicate data into new record
                $detail['Price_Per_Unit'] = $pList->Price_Per_Unit;             //add by kik : 20140801 add price per unit for new record
                $detail['Unit_Price_Id'] = $pList->Unit_Price_Id;               //add by kik : 20140722 duplicate data into new record
                $detail['All_Price'] = $pList->Price_Per_Unit*str_replace(",", "", $sQtyLot);                           //add by kik : 20140722 duplicate data into new record
            }
            $order_detail[] = $detail;
            $i++;
        }
        $result = $this->stock->addOrderDetail($order_detail);
        if ($result > 0) {
            echo json_encode("OK");
        } else {
            echo json_encode("NOT_OK");
        }
        return;
    }

    #fix defect 519-520 : add by kik : 2013-12-16

    public function getSelectProductModal() {
        $data = $this->input->get();
        $productCode = $data["productCode_val"];
        $productStatus = $data["productStatus_val"];
        $productSubStatus = $data["productSubStatus_val"];
        $productLot = $data["productLot_val"];
        $productSerial = $data["productSerial_val"];
        $productMfd = $data["productMfd_val"];
        $productExp = $data["productExp_val"];
        $productFilter = $data["productFilter_val"];
        $docRefExt = $data["docRefExt_val"];
        $limit_max = ($data['iDisplayLength'] < 0 ? '999999' : $data['iDisplayLength']);
        $limit_start = $data['iDisplayStart'];

        $palletCode_val = "";
        $palletIsFull_val = "";
        $palletDispatchType_val = "NULL";
        $chkPallet_val = "";
        //ADD BY POR เพิ่มเติมในการรับค่ากรณี filter ด้วย pallet
        if ($this->pallet == TRUE && !empty($data["chkPallet_val"])):
            $palletCode_val = trim($data["palletCode_val"]); //pallet code
            $palletIsFull_val = $data["palletIsFull_val"]; //pallet type
            $palletDispatchType_val = $data["palletDispatchType_val"]; //pallet type
            $chkPallet_val = $data["chkPallet_val"]; //ตัวเลือกว่าเลือก filter ด้วย pallet หรือไม่
        endif;

        //END ADD

        $s_search = iconv("UTF-8", "TIS-620", $data['sSearch']);
        $query = $this->inbound->getProductDetails($productCode, $productStatus, $productSubStatus, $productLot, $productSerial, $productMfd, $productExp, $limit_start, $limit_max, $s_search, $palletCode_val, $chkPallet_val, $palletIsFull_val, $palletDispatchType_val, 'Product_Code ASC', $productFilter , $docRefExt);
// p($query);exit;
        $count_result = $query->num_rows();
        $result = $query->result();
//         p($result);
       
//        exit();
// //
// p($this->db->lest_query()); exit;
        #########################################
        $response = array();

        foreach ($result as $k => $val) :
            // p($val->Product_Exp);
            
            if ($val->Product_Exp != "" && $val->Product_Exp != NULL){
                $Product_Exp = explode('/', $val->Product_Exp);
                $agingDate = (mktime(0, 0, 0, $Product_Exp[1], $Product_Exp[0], $Product_Exp[2]) - mktime(0, 0, 0)) / ( 60 * 60 * 24 );
            } else {
                $agingDate = 'N/A';
            }
            //p($agingDate);


            $arr_inbound = array(); 
            if ($palletDispatchType_val == 'FULL'): //ADD BY POR 2014-02-18 กรณีเลือก dispatch full จะแสดงแบบ pallet
                //หาว่า pallet ที่เลือก มี inbound_id อะไรบ้างเพื่อส่งค่ากลับไป
                $column["Inbound_Id"] = "Inbound_Id";
                $qry_inbound = $this->inbound->get_inbound_by_palletID($val->Pallet_Id, $column);
                $result_inbound = $qry_inbound->result_array();
                foreach ($result_inbound as $key => $inboundid):
                    //p($inboundid);exit();
                    array_push($arr_inbound, $inboundid["Inbound_Id"]);
                endforeach;

                $arr_inbound = json_encode($arr_inbound);

                $response[] = array("<input CLASS=chkBoxValClass type=checkbox name=chkBoxVal[] value='" . $val->Pallet_Id . "' id='chkBoxVal" . $val->Pallet_Id . "' onClick=getCheckValue(this,'" . $palletDispatchType_val . "'," . $arr_inbound . ")>"
                    , $val->Pallet_Code
                    , $val->Pallet_Type
                    , thai_json_encode($val->Pallet_Name)
                    , $agingDate
                );
            else:
                $arr_inbound = json_encode($arr_inbound);
                $response[] = array("<input CLASS=chkBoxValClass type=checkbox name=chkBoxVal[] value='" . $val->Inbound_Id . "' id='chkBoxVal" . $val->Inbound_Id . "' onClick=getCheckValue(this,'" . $palletDispatchType_val . "'," . $arr_inbound . ")>"
                    , $val->Doc_Refer_Ext
                    , $val->Product_Code
                    , thai_json_encode($val->Product_NameEN)
                    , $val->Dom_TH_Desc
                    // , $val->Product_Status
                    , $val->Product_Sub_Status
                    , $val->Location_Code
                    , thai_json_encode($val->Product_Lot)
                    , thai_json_encode($val->Product_Serial)
                    , $val->Receive_Date
                    , $val->Product_Mfd
                    , $val->Product_Exp
                    , $agingDate
                    , set_number_format($val->Est_Balance_Qty)
                    , set_number_format($val->Balance_Qty)
                    , $val->Pallet_Code
                );
                
            endif;
            //  p($response);

        endforeach;
        // p($arr_inbound);
      
        $total_filter_product = $query = $this->inbound->getProductDetails($productCode, $productStatus, $productSubStatus, $productLot, $productSerial, $productMfd, $productExp, '0', '999999', $s_search, $palletCode_val, $chkPallet_val, $palletIsFull_val, $palletDispatchType_val)->num_rows(); // Add By Akkarapol, 27/11/2013, เพิ่มโค๊ดนับจำนวนทั้งหมดของ product ที่ query จาก filter ที่ส่งมา
        $total_product = $query = $this->inbound->getProductDetails('', '', '', '', '', '', '', '0', '999999', $s_search, $palletCode_val, $chkPallet_val, $palletIsFull_val, $palletDispatchType_val)->num_rows(); // Add By Akkarapol, 27/11/2013, เพิ่มโค๊ดนับจำนวน product ทั้งหมด
        // p($total_filter_product);
       
        $output = array(
            "sEcho" => (int) $data['sEcho'],
            "iTotalRecords" => $total_product,
            "iTotalDisplayRecords" => $total_filter_product,
            "aaData" => $response
        );
       
    //    p($total_product);exit;
    // p($response);
        echo json_encode($output);
    }

    // Add By Akkarapol, 16/09/2013, เพิ่มฟังก์ชั่น สำหรับ ตรวจสอบว่า Product MFD. ที่เลือกมานั้น มี ShelfLife ตามที่เซ็ตค่าใน Master หรือไม่
    function chkMFDDateOfProduct($chk_prod_code, $chk_mfd) {
        if (strlen($chk_mfd) == 0) {
        	return "Please input Product Mfd."; exit();
        }
    	$this->load->model("location_model", "sys");
        $result = $this->product->getProductDetailByProductCode($chk_prod_code);
        $result = $result->row_array();
        $spl_date_mfd = preg_split('/\//', $chk_mfd); // Edit By Akkarapol, 16/09/2013, เปลี่ยนจากการเรียกใช้ ฟังก์ชั่น split เป็น preg_split เนื่องจาก PHP V 5.3.0+ ไม่รองรับ ฟังก์ชั่น split แล้ว
        $mktime = (mktime(0, 0, 0) - mktime(0, 0, 0, $spl_date_mfd[1], $spl_date_mfd[0], $spl_date_mfd[2])) / ( 60 * 60 * 24 );

        if ($mktime >= $result['Min_ShelfLife']):
            return 'Ok';
        else:
            return 'Min ShelfLife of "' . $result['Product_NameEN'] . '" = ' . (int) $result['Min_ShelfLife'] . ' but you choose day = ' . $mktime;
        endif;
    }

    // END Add By Akkarapol, 16/09/2013, เพิ่มฟังก์ชั่น สำหรับ ตรวจสอบว่า Product MFD. ที่เลือกมานั้น มี ShelfLife ตามที่เซ็ตค่าใน Master หรือไม่
    // Add By Akkarapol, 16/09/2013, เพิ่มฟังก์ชั่น สำหรับ ตรวจสอบว่า Product Exp. ที่เลือกมานั้น มี Aging ตามที่เซ็ตค่าใน Master หรือไม่
    function chkExpDateOfProduct($chk_prod_code, $chk_exp) {
    	if (strlen($chk_exp) == 0) {
    		return "Please input Product Exp."; exit();
    	}
        $this->load->model("location_model", "sys");
        $result = $this->product->getProductDetailByProductCode($chk_prod_code);
        $result = $result->row_array();
        $spl_date_exp = preg_split('/\//', $chk_exp); // Edit By Akkarapol, 16/09/2013, เปลี่ยนจากการเรียกใช้ ฟังก์ชั่น split เป็น preg_split เนื่องจาก PHP V 5.3.0+ ไม่รองรับ ฟังก์ชั่น split แล้ว
        $mktime = (mktime(0, 0, 0, $spl_date_exp[1], $spl_date_exp[0], $spl_date_exp[2]) - mktime(0, 0, 0)) / ( 60 * 60 * 24 );

        if ($mktime >= $result['Min_Aging']):
            return 'Ok';
        else:
            return 'Min Aging of "' . $result['Product_NameEN'] . '" = ' . (int) $result['Min_Aging'] . ' but you choose day = ' . $mktime;
        endif;
    }

    // END Add By Akkarapol, 16/09/2013, เพิ่มฟังก์ชั่น สำหรับ ตรวจสอบว่า Product Exp. ที่เลือกมานั้น มี Aging ตามที่เซ็ตค่าใน Master หรือไม่


    function ajax_show_product_list() {
        $text_search = $this->input->post('text_search');
        $product = $this->product->searchProduct($text_search, '', 100, 0, 'Product_Code ASC');
        $list = array();
        foreach ($product as $key_p => $p) {
            $list[$key_p]['product_id'] = $p['product_id'];
            $list[$key_p]['product_code'] = $p['product_code'];
//            $list[$key_p]['product_name'] = '';
            $list[$key_p]['product_name'] = thai_json_encode($p['product_name']);
        }
        echo json_encode($list);
    }

    function ajax_show_location_list() {
    	$text_search = $this->input->post('text_search');
    	$locations = $this->loc->searchLocation($text_search, '', 100);
    	$list = array();
    	foreach ($locations as $key => $location) {
    		$list[$key]['Location_Id'] = $location->Location_Id;
    		$list[$key]['Location_Code'] = $location->Location_Code;
    	}
    	echo json_encode($list);
    }


}
