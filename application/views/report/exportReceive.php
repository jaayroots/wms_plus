<?php

//Origianl BY POR 2013-11-14
//+++++ADD BY POR 2013-11-14 เพิ่มรายงาน receiving report ให้ออกรายงาน PDF ได้
date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$margin_foot = ($showfooter=='show')?33:25;
$mpdf = new mPDF('th', 'A4-L', '11', '', 10, 10, 30, $margin_foot, 10, 10);
$xml = $this->config->item("_xml");

$from_date = convertDateString($from_date);

if (!empty($to_date)):
    $to_date = convertDateString($to_date);
endif;

$header = '
<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
    <tr>
        <td height="30" width="200">
            <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
        </td>
        <td style="text-align: center;font-weight: bold;font-size: 16px">Receiving Report</td>
        <td width="200"> </td>
    </tr>
</table>

<table width="100%" style="font-family: arial;font-size: 14px">
<tr><td align="center"><b>FROM : </b>' . $from_date . '&nbsp;&nbsp;&nbsp;&nbsp;<b>TO : </b>' . $to_date . '</td></tr>
<tr>
        <td height="25"></td>
</tr>
</table>
';

//ADD BY POR 2013-12-18 แสดงส่วนท้าย(เซ็นชื่อ) ตอนกด Approve Receive
if($showfooter=='show'):
$footer .= '
<div style="width:100%;font-size:12px;">
<div style="width: 25%;float:left;">
    <div>Shipper:......................................</div>
    <div style="padding:2px 0 2px 10px;">
    (
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    )
    </div>
    <div>............../............../.....................</div>
</div>
<div style="width: 25%;float:left;">
    <div>Car Plate No..............................</div>
    <div style="padding:5px 0">Driver:.........................................</div>
</div>
<div style="width: 30%;float:left;">
    <div>Auditor:......................................</div>
    <div style="padding:2px 0 2px 10px;">
    (
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    )
    </div>
    <div>............../............../.....................</div>
</div>
<div style="width: 20%;float:left;">
    <div>Approve:......................................</div>
    <div style="padding:2px 0 2px 10px;">
    (
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    )
    </div>
    <div>............../............../.....................</div>
</div>
</div>
';
endif;
//END IF

$footer .= '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
<tr>
<td width="50%" align="left">
        Print By : ' . iconv("TIS-620", "UTF-8", $printBy) . '  ,
        Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
        <td align="right">WMSP:'.$revision.' , {PAGENO}/{nb}</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');

$head_table = '
<style>
    '.$set_css_for_show_column.'
    td,th {
        font-family: garuda;
    }
    .tborder{
        /*border:1px solid #333333;*/
        border-top:1px solid #333333;
        border-right:1px solid #333333;
    }
    .tborder td,
    .tborder th{
        /*border-top:1px solid #333333;*/
        border-left:1px solid #333333;
        border-bottom:1px solid #333333;
        border-right:1px solid #333333;
        font-size:10px;
        padding:3px;
        font-family: garuda;
    }
</style>

<table width="100%" cellspacing="0" style="font-family: arial;font-size:10px; border-right: 0;" class="tborder">
    <thead>
        <tr style="border:1px solid #333333;" bgcolor="#B0C4DE">
            <th height="35">' . _lang('no') . '</th>
            <div class="receive_date"><th class="receive_date">' . _lang('receive_date') . '</th></div>
            <div class="product_code"><th class="product_code">' . _lang('product_code') . '</th></div>
            <div class="product_name"><th class="product_name">' . _lang('product_name') . '</th></div>
            <div class="product_status"><th class="product_status">' . _lang('product_status') . '</th></div>
            <div class="lot"><th class="lot">' . _lang('Serial_lot') . '</th></div>
            <div class="product_mfd"><th class="product_mfd">' . _lang('product_mfd') . '</th></div>
            <div class="product_exp"><th class="product_exp">' . _lang('product_exp') . '</th></div>
            <div class="invoice"><th class="invoice">' . _lang('invoice_no') . '</th></div>
            <div class="container"><th class="container">' . _lang('container') . '</th></div>
            <div class="qty"><th class="qty">' . _lang('receive_qty') . '</th></div>
            <div class="unit"><th class="unit">' . _lang('unit') . '</th></div>
            <div class="price_per_unit"><th class="price_per_unit">' . _lang('price_per_unit') . '</th></div>
            <div class="unit_price"><th class="unit_price">' . _lang('unit_price') . '</th></div>
            <div class="all_price"><th class="all_price">' . _lang('all_price') . '</th></div>
            <div class="from"><th class="from">' . _lang('from') . '</th></div>
            <div class="to"><th class="to">' . _lang('to') . '</th></div>
            <div class="pallet_code"><th class="pallet_code">' . _lang('pallet_code') . '</th></div>
            <div class="remark"><th style="border-right: 1px solid #666666;" class="remark">' . _lang('remark') . '</th></div>
        </tr>
    </thead>
    <tbody>';

$page = '';
$page.=$head_table;

if (!empty($datas)) {
    $i = 1;
    $sum_not_reject = 0;
    //ADD BY POR 2014-06-18 เพิ่มให้แสดง price_per_unit
    $sum_not_reject_price = 0;
    $sum_not_reject_all_price = 0;
    //END ADD

    $sum_reject = 0;
    //ADD BY POR 2014-06-18 เพิ่มให้แสดง price_per_unit
    $sum_reject_price = 0;
    $sum_reject_all_price = 0;
    //END ADD

    $sum_all = 0;
    //ADD BY POR 2014-06-18 เพิ่มให้แสดง price_per_unit
    $sum_price = 0;
    $sum_all_price = 0;
    //END ADD

    $have_reject = FALSE;

//    $num = 2; //ADD BY POR 2014-06-18 สำหรับลบค่าออกจาก colspan เพื่อแสดงผลรวม เดิมเป็น 2 แต่เปลี่ยน เป็นใส่ตั่วแปรแทน
//    if($this->config->item('build_pallet')):
//        $colspan = 12;
//        if($statusprice):
//            $colspan = 15;
//            $num = 5;
//        endif;
//    elseif($statusprice):
//        $colspan = 14;
//        $num = 5;
//    else:
//        $colspan = 11;
//    endif;

    $all_column+=1;
    $colspan1 = $colspan[3]+1;
    // p($colspan1); exit;
    foreach ($datas as $keyRow => $data) {

        $iTemp = 1;

        foreach ($data as $key => $value) { //p($value);exit();
            /**
             * Add calculate reject document by kik : 20140603
             */
            $set_tr_reject="";
            if($value['Receive_Qty'] < 0):

                $set_tr_reject = 'style = "color:red;"';

                $sum_reject+=@(-$value['Receive_Qty']);

                //ADD BY POR 2014-06-18 price per unit
                $sum_reject_price+=@(-$value['Price_Per_Unit']);
                $sum_reject_all_price+=@(-$value['All_price']);
                //END ADD

                $have_reject = TRUE;

            else:
                if($value['Is_reject'] != "Y"):
                    $sum_not_reject+=@$value['Receive_Qty'];

                    //ADD BY POR 2014-06-18 price per unit
                    $sum_not_reject_price+=@$value['Price_Per_Unit'];
                    $sum_not_reject_all_price+=@$value['All_price'];
                    //END ADD
                endif;

                $sum_all+=@$value['Receive_Qty'];
                $sum_price+=@$value['Price_Per_Unit'];
                $sum_all_price+=@$value['All_price'];

            endif;
// p($all_column); exit;
            if ($iTemp == 1) {
                $page .= '<tr bgcolor="#F0F8FF "><td  style="border-right: 1px solid #666666;" colspan="'.$all_column.'" align="left" height="30"><b>Ref.No. ' . $value['Flow_Id'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; PO No. ' . $value['Doc_Refer_Ext'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; GRN No. ' . $value['Document_No'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Doc Refer Int.' .$value['Doc_Refer_Int'].'</b></td></tr>';
            }
            $page.='
					<tr >
						<td'.$set_tr_reject.' height="25"  valign="top" align="center">' . $i . '</td>
						<div class="receive_date"><td class="receive_date" '.$set_tr_reject.' valign="top" align="center">' . $value['Receive_Date'] . '</td></div>
						<div class="product_code"><td class="product_code" '.$set_tr_reject.' valign="top" align="center">' . $value['Product_Code'] . '</td></div>
                        <div class="product_name"><td  class="product_name" '.$set_tr_reject.' valign="top">' . iconv("TIS-620", "UTF-8", $value['Product_Name']) . '</td></div>
                        <div class="product_status"><td  class="product_status" '.$set_tr_reject.' valign="top">' . iconv("TIS-620", "UTF-8", $value['Product_Status']) . '</td></div>
						<div class="lot"><td  class="lot" '.$set_tr_reject.' valign="top" align="center">' . iconv("TIS-620", "UTF-8", $value['Product_SerialLot']) . '</td></div>
						<div class="product_mfd"><td  class="product_mfd" '.$set_tr_reject.' valign="top" align="center">' . $value['Product_Mfd'] . '</td></div>
						<div class="product_exp"><td  class="product_exp" '.$set_tr_reject.' valign="top" align="center">' . $value['Product_Exp'] . '</td></div>
                                                <div class="invoice"><td  class="invoice" '.$set_tr_reject.' valign="top" align="center">' . @$value['Invoice_No'] . '</td></div>
                                                <div class="container"><td  class="container" '.$set_tr_reject.' valign="top" align="center">' . iconv("TIS-620", "UTF-8", @$value['Cont_No'])." ".@$value['Cont_Size_No']."".@$value['Cont_Size_Unit_Code'] . '</td></div>
                                                <div class="qty"><td class="qty" '.$set_tr_reject.' valign="top" align="right">' . set_number_format($value['Receive_Qty']) . '</td></div>
                                                <div class="unit"><td class="unit" '.$set_tr_reject.' valign="top" align="left">' . tis620_to_utf8($value['Unit_Value']) . '</td></div>
                                                <div class="price_per_unit"><td class="price_per_unit" '.$set_tr_reject.' valign="top" align="right">' . set_number_format(@$value['Price_Per_Unit']) . '</td></div>
                                                <div class="unit_price"><td class="unit_price" '.$set_tr_reject.' valign="top" align="center">' . @$value['Unit_price'] . '</td></div>
                                                <div class="all_price"><td class="all_price" '.$set_tr_reject.' valign="top" align="right">' . set_number_format(@$value['All_price']) . '</td></div>
						<div class="from"><td  class="from" '.$set_tr_reject.' valign="top">' . iconv("TIS-620", "UTF-8", $value['From_sup']) . '</td></div>
						<div class="to"><td  class="to" '.$set_tr_reject.' valign="top">' . iconv("TIS-620", "UTF-8", $value['To_sup']) . '</td></div>
                                                <div class="pallet_code"><td class="pallet_code" '.$set_tr_reject.' valign="top" align="center">' . @$value['Pallet_Code'] . '</td></div>
                                                <div class="remark"><td  style="border-right: 1px solid #666666;" class="remark" '.$set_tr_reject.' valign="top" align="right">' . iconv("TIS-620", "UTF-8", $value['Remark']). '&nbsp;</td></div>
					</tr>

					';
            $i++;
            $iTemp++;

        }
    }
    $text_total_reject = '';
    if($have_reject):
     $text_total_reject = '(Exclude Reject)';
    endif;

    //echo count($colspan);
    //echo "<br/>" . $colspan[count($colspan)];
    $column = $xml['show_column_report']['object']['receiving_pdf'];
    $unit_column = ($column['unit'] == TRUE ? "<div class=\"unit\"><td></td></div>" : "");
    $price_per_unit_column = ($column['price_per_unit']['value'] == TRUE ? "<div class=\"price_per_unit\"><td align=\"right\"><b>".set_number_format($sum_not_reject_price)."</b></td></div>" : "");
    $unit_price_column = ($column['unit_price'] == TRUE ? "<div class=\"unit_price\"><td></td></div>" : "");
    $all_price_column = ($column['all_price']['value'] == TRUE ? "<div class=\"all_price\"><td align=\"right\"><b>".set_number_format($sum_not_reject_all_price)."</b></td></div>" : "");
    $page.='<tr  bgcolor="#F0F0F0"><td colspan="'. $colspan1.'" align="center"><b>Total '.$text_total_reject.'</b></td><td align="right"><b>'.set_number_format($sum_not_reject).'</b></td>';
    $page.=$unit_column;
    $page.=$price_per_unit_column;
    $page.=$unit_price_column;
    $page.=$all_price_column;
    $page.='<td style="border-right: 1px solid #666666;" colspan="'.($colspan[count($colspan)] - 1).'" ></td></tr>';

    //<!--Reject Area show Total - ในส่วนนี้จะแสดงผลเฉพาะ SKU ที่มีรายการ reject เกิดขึ้นเท่านั้น-->
    if($have_reject):
    $page.='<tr  bgcolor="#DEDEDE"><td colspan="'. $colspan1.'" align="center"><b>Reject</b></td><td align="right"><b>'.set_number_format($sum_reject).'</b></td>
                <div class="unit"><td></td></div>
                <div class="price_per_unit"><td align="right"><b>'.set_number_format($sum_reject_price).'</b></td></div>
                <div class="unit_price"><td></td></div>
                <div class="all_price"><td align="right"><b>'.set_number_format($sum_reject_all_price).'</b></td></div>
                <td  style="border-right: 1px solid #666666;" colspan="'.$colspan[count($colspan)].'" ></td>
             </tr>';

    $page.='<tr  bgcolor="#C9C9C9"><td colspan="'. $colspan1.'" align="center"><b>All Total</b></td><td align="right"><b>'.set_number_format($sum_all).'</b></td>
                <div class="unit"><td></td></div>
                <div class="price_per_unit"><td align="right"><b>'.set_number_format($sum_price).'</b></td></div>
                <div class="unit_price"><td></td></div>
                <div class="all_price"><td align="right"><b>'.set_number_format($sum_all_price).'</b></td></div>
                <td  style="border-right: 1px solid #666666;" colspan="'.$colspan[count($colspan)].'" ></td>
            </tr>';
    endif;
    //<!-- end Reject Area show Total - ในส่วนนี้จะแสดงผลเฉพาะ SKU ที่มีรายการ reject เกิดขึ้นเท่านั้น-->
}


$page.='</tbody>
    </table>';
//echo $all_column;exit();
//echo $colspan[count($colspan)];exit();
// p($page);exit;
$this->load->helper('file');
$stylesheet = read_file('../libraries/mpdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($page);
//$mpdf->Output($strSaveName, 'I'); // *** file name ***//

$filename = 'Receiving-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F');
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName);

?>