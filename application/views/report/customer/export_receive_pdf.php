<?php

date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$margin_foot = ($showfooter == 'show') ? 33 : 25;
$mpdf = new mPDF('th', 'A4-L', '11', '', 10, 10, 30, $margin_foot, 10, 10);
$xml = $this->config->item("_xml");

$from_date = convertDateString($from_date);

if (!empty($to_date)):
    $to_date = convertDateString($to_date);
endif;

// Prepare data
$result = array();

foreach ($datas as $data) :
    foreach ($data as $key => $value) :
        $hash = md5($value['Receive_Date'] . $value['Product_Name'] . $value['Doc_Refer_Ext'] . $value['Product_Lot'] . $value['Product_SerialLot'] . $value['TypeLicense'] . $value['Invoice_No'] . $value['Unit_Value']);

        if (array_key_exists($hash, $result)) {
            $result[$hash]['Receive_Date'] = $value['Receive_Date'];
            $result[$hash]['Product_Name'] = $value['Product_Name'];
            $result[$hash]['Doc_Refer_Ext'] = $value['Doc_Refer_Ext'];
            $result[$hash]['Product_Lot'] = $value['Product_Lot'];
            $result[$hash]['Product_SerialLot'] = $value['Product_SerialLot'];
            $result[$hash]['TypeLicense'] = $value['TypeLicense'];
            $result[$hash]['Invoice_No'] = $value['Invoice_No'];
            $result[$hash]['w'] = $value['w'];
            $result[$hash]['l'] = $value['l'];
            $result[$hash]['h'] = $value['h'];
            $result[$hash]['Receive_Qty'] += $value['Receive_Qty'];
            $result[$hash]['cbm'] = $value['cbm'];
            $result[$hash]['Unit_Value'] = $value['Unit_Value'];
        } else {
            $result[$hash]['Receive_Date'] = $value['Receive_Date'];
            $result[$hash]['Product_Name'] = $value['Product_Name'];
            $result[$hash]['Doc_Refer_Ext'] = $value['Doc_Refer_Ext'];
            $result[$hash]['Product_Lot'] = $value['Product_Lot'];
            $result[$hash]['Product_Serial'] = $value['Product_Serial'];
            $result[$hash]['TypeLicense'] = $value['TypeLicense'];
            $result[$hash]['Invoice_No'] = $value['Invoice_No'];
            $result[$hash]['w'] = $value['w'];
            $result[$hash]['l'] = $value['l'];
            $result[$hash]['h'] = $value['h'];
            $result[$hash]['Receive_Qty'] = $value['Receive_Qty'];
            $result[$hash]['cbm'] = $value['cbm'];
            $result[$hash]['Unit_Value'] = $value['Unit_Value'];
        }
    endforeach;
endforeach;
$header = '
<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
    <tr>
        <td height="30" width="200">
            <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
        </td>
        <td style="text-align: center;font-weight: bold;font-size: 16px">Stock In Report</td>
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

if ($showfooter == 'show'):
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
        <td align="right">WMSP:' . $revision . ' , {PAGENO}/{nb}</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');

$head_table = '
<style>
    ' . $set_css_for_show_column . '
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
            <div style="width: 10%;" class="receive_date"><th width="10%" class="receive_date">Date In</th></div>
            <div style="width: 20%;" class="product_name"><th width="20%" class="product_name">Description</th></div>
            <div style="width: 10%;" class="doc_refer_ext"><th width="10%" class="doc_refer_ext">Doc Ext.</th></div>
            <div style="width: 10%;" class="lot"><th width="10%" class="lot">Batch No.</th></div>
            <div style="width: 10%;" class="serial"><th width="10%" class="lot">Lot No.</th></div>
            <div style="width: 5%;" class="type"><th width="5%" class="qty">Type.</th></div>
            <div style="width: 10%;" class="invoice"><th width="10%" class="invoice">Invoice No.</th></div>
            <div style="width: 10%;" class="qty"><th width="10%" class="qty">Sum of Qty</th></div>
            <div style="width: 10%;" class="dimension"><th width="10%" class="qty">Dimension</th></div>
            <div style="width: 10%;" class="cbm"><th width="10%" class="qty">CBM</th></div>
            <div style="width: 10%;" class="sum_qty_cbm"><th width="10%" class="qty">Sum of Total/CBM</th></div>
            <div style="width: 10%;" class="unit"><th width="10%" class="unit">Type UOM</th></div>
        </tr>
    </thead>
    <tbody>';

$page = '';
$page.=$head_table;

if (!empty($result)) {
    $sum_not_reject = 0;
    $sum_cbm = 0;
    $sum_cbm_new = 0;

    $total_per_group = 0;
    $total_cbm_per_group = 0;
    $total_cbm_per_group_new = 0;
    $date = "";

    foreach ($result as $key => $value) :

        $set_tr_reject = "";

        if ($date != "" && $date != $value['Receive_Date']) {
            $page.= '<tr>';
            $page.='<td colspan="7" align="center" style="background-color: #E6F1F6; height: 30px;"><b>End of ' . $date . '</b></td>';
            $page.= '<td align="right" style="background-color: #E6F1F6; height: 30px;">' . set_number_format($total_per_group) . '</td>';
            $page.= '<td align="right" style="background-color: #E6F1F6; height: 30px;"></td>';
            $page.= '<td align="right" style="background-color: #E6F1F6; height: 30px;">' . set_number_format($total_cbm_per_group_new) . '</td>';
            $page.= '<td align="right" style="background-color: #E6F1F6; height: 30px;">' . set_number_format($total_cbm_per_group) . '</td>';
            $page.= '<td style="background-color: #E6F1F6; height: 30px;"></td>';
            $page.= '</tr>';
            $total_per_group = $value['Receive_Qty'];
            $total_cbm_per_group = ($value['Receive_Qty'] * $value['cbm']);
            $total_cbm_per_group_new = $value['cbm'];
        } else {
            $total_per_group += $value['Receive_Qty'];
            $total_cbm_per_group += ($value['Receive_Qty'] * $value['cbm']);
            $total_cbm_per_group_new += $value['cbm'];
        }

        $dimension = (!empty($value['w']) && !empty($value['h']) && !empty($value['l']) ? $value['w'] . " x " . $value['l'] . " x " . $value['h'] : "");

        $page.='<tr >'
                . '<div class="receive_date"><td class="receive_date" ' . $set_tr_reject . ' valign="top" align="center">' . $value['Receive_Date'] . '</td></div>'
                . '<div class="product_name"><td  class="product_name" ' . $set_tr_reject . ' valign="top">' . tis620_to_utf8($value['Product_Name']) . '</td></div>'
                . '<div class="doc_refer_ext"><td  class="doc_refer_ext" ' . $set_tr_reject . ' valign="top">' . tis620_to_utf8($value['Doc_Refer_Ext']) . '</td></div>'
                . '<div class="lot"><td  class="lot" ' . $set_tr_reject . ' valign="top" align="center">' . tis620_to_utf8($value['Product_Lot']) . '</td></div>'
                . '<div class="serial"><td  class="serial" ' . $set_tr_reject . ' valign="top" align="center">' . tis620_to_utf8($value['Product_Serial']) . '</td></div>'
                . '<div class="type"><td  class="type" ' . $set_tr_reject . ' valign="top" align="center">' . tis620_to_utf8($value['TypeLicense']) . '</td></div>'
                . '<div class="invoice"><td  class="invoice" ' . $set_tr_reject . ' valign="top">' . tis620_to_utf8($value['Invoice_No']) . '</td></div>'
                . '<div class="qty"><td class="qty" ' . $set_tr_reject . ' valign="top" align="right">' . set_number_format($value['Receive_Qty']) . '</td></div>'
                . '<div class="dimension"><td class="qty" ' . $set_tr_reject . ' valign="top" align="center">' . $dimension . '</td></div>'
                . '<div class="cbm"><td class="qty" ' . $set_tr_reject . ' valign="top" align="right">' . $value['cbm'] . '</td></div>'
                . '<div class="sum_qty_cbm"><td class="qty" ' . $set_tr_reject . ' valign="top" align="right">' . set_number_format($value['Receive_Qty'] * $value['cbm']) . '</td></div>'
                . '<div class="unit"><td class="unit" ' . $set_tr_reject . ' valign="top" align="left">' . tis620_to_utf8($value['Unit_Value']) . '</td></div>'
                . '</tr>';

        $date = $value['Receive_Date'];

        $sum_not_reject += $value['Receive_Qty'];
        $sum_cbm += ($value['Receive_Qty'] * $value['cbm']);
        $sum_cbm_new += $value['cbm'];
    endforeach;

    if ($total_per_group != 0) {
        $page .= '<tr>';
        $page .= '<td colspan="7" align="center" style="background-color: #E6F1F6; height: 30px;"><b>End of ' . $date . '</b></td>';
        $page .= '<td align="right" style="background-color: #E6F1F6; height: 30px;">' . set_number_format($total_per_group) . '</td>';
        $page .= '<td align="right" style="background-color: #E6F1F6; height: 30px;"></td>';
        $page .= '<td align="right" style="background-color: #E6F1F6; height: 30px;">' . set_number_format($total_cbm_per_group_new) . '</td>';
        $page .= '<td align="right" style="background-color: #E6F1F6; height: 30px;">' . set_number_format($total_cbm_per_group) . '</td>';
        $page .= '<td style="background-color: #E6F1F6; height: 30px;"></td>';
        $page .= '</tr>';
    }

    $column = $xml['show_column_report']['object']['receiving_pdf'];
    $page .= '<tr bgcolor="#F0F0F0">';
    $page .= '<td style=" height: 30px;" colspan="7" align="center"><b>Grand Total ' . $text_total_reject . '</b></td>';
    $page .= '<td style=" height: 30px;" align="right"><b>' . set_number_format($sum_not_reject) . '</b></td>';
    $page .= '<td style=" height: 30px;" align="right"><b></b></td>';
    $page .= '<td style=" height: 30px;" align="right"><b>' . set_number_format($sum_cbm_new) . '</b></td>';
    $page .= '<td style=" height: 30px;" align="right"><b>' . set_number_format($sum_cbm) . '</b></td>';
    $page .= '<td style=" height: 30px;"></td>';
    $page .= '</tr>';
}

$page.='</tbody></table>';

$this->load->helper('file');
$stylesheet = read_file('../libraries/mpdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($page);
$filename = 'stock-in-report-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F');
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName);
