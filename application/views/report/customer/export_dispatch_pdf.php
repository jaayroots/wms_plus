<?php

date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 20, 25, 5, 5);
$mpdf->ignore_invalid_utf8 = true;

$from_date = convertDateString($fdate);

if (!empty($tdate)):
    $to_date = convertDateString($tdate);
endif;

$header = '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
    <tr>
        <td height="30" width="200">
            <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
        </td>
        <td style="text-align: center;font-weight: bold;">' . iconv("TIS-620", "UTF-8", 'Stock out report') . '<br/><b>From : </b>' . $from_date . '&nbsp;&nbsp;&nbsp;&nbsp;<b>To : </b>' . $to_date . '</td>
    <td width="200"> </td>
</tr>
</table>';

$footer = '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
<tr>
<td width="50%" align="left">
        Print By : ' . iconv("TIS-620", "UTF-8", $printBy) . '  ,
        Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
        <td align="right">{PAGENO}/{nb}</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');

$head_table = '
<style>
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
            font-size:10px;
    }
</style>

<table width="100%" cellspacing="0" style="border:1px solid #666666;font-size:10px;font-family: arial;" class="tborder">
    <thead>
        <tr style="border:1px solid #333333;" bgcolor="#B0C4DE">
            <th width="10%" height="35">Date In</th>
            <th width="10%" height="35">Date Load</th>
            <th width="20%" height="35">Description</th>
            <th width="10%" height="35">Doc Ext.</th>
            <th width="10%" height="35">Batch No.</th>
            <th width="10%" height="35">Lot No.</th>
            <th width="10%" height="35">Type.</th>
            <th width="10%" height="35">Invoice No.</th>
            <th width="10%" height="35">Sum of Q\'ty</th>
            <th width="10%" height="35">Dimension</th>
            <th width="10%" height="35">CBM</th>
            <th width="10%" height="35">Sum of Total/CBM</th>
            <th width="10%" height="35">Type UOM</th>';

$head_table.='</tr></thead><tbody>';

$page = '';
$page.=$head_table;

$total = 0;
$total_cbm = 0;
if (!empty($datas)) {
    $noPage = 1;
    $hash = "";
    $total_per_group = 0;
    $total_cbm_per_group = 0;
    $total_cbm_qty_per_group = 0;
    $sum_all_cbm = 0;
    $cbm_per_group = 0;
    $unit_per_group = "";

    foreach ($datas as $keydate => $value) {
        $iTemp = 1;


        if (!empty($hash) && $hash != $value->Dispatch_Date) {
            $page.= '<tr>'
                    . '<td style="background-color: #E6F1F6; height: 30px; text-align: center;" colspan="8">End of ' . $hash . '</td>'
                    . '<td style="background-color: #E6F1F6; height: 30px; text-align:right;">' . set_number_format($total_per_group) . '</td>'
                    . '<td style="background-color: #E6F1F6; height: 30px; text-align:right;"></td>'
                    . '<td style="background-color: #E6F1F6; height: 30px; text-align:right;">' . set_number_format($total_cbm_per_group) . '</td>'
                    . '<td style="background-color: #E6F1F6; height: 30px; text-align:right;">' . set_number_format($total_cbm_qty_per_group) . '</td>'
                    . '<td style="background-color: #E6F1F6; height: 30px; text-align:center;"></td>'
                    . '</tr>';
            $total_per_group = $value->Dispatch_Qty;
            $total_cbm_per_group = $value->cbm;
            $total_cbm_qty_per_group = ($value->Dispatch_Qty * $value->cbm);
            $cbm_per_group = $value->cbm;
            $unit_per_group = $value->Unit_Value;
        } else {
            $total_per_group += $value->Dispatch_Qty;
            $total_cbm_per_group += $value->cbm;
            $total_cbm_qty_per_group += ($value->Dispatch_Qty * $value->cbm);
            $cbm_per_group = $value->cbm;
            $unit_per_group = $value->Unit_Value;
        }
        $hash = $value->Dispatch_Date;

        $dimension = (!empty($value->w) && !empty($value->h) && !empty($value->l) ? $value->w . " x " . $value->l . " x " . $value->h : "");
        
        $page.='<tr>'
                . '<td valign="top" align="center">' . $value->Receive_Date . '</td>'
                . '<td valign="top" align="center">' . $value->Dispatch_Date . '</td>'
                . '<td valign="top" align="left">' . tis620_to_utf8($value->Product_Name) . '</td>'
                . '<td valign="top" align="center">' . tis620_to_utf8($value->Doc_Refer_Ext) . '</td>'
                . '<td valign="top" align="center">' . tis620_to_utf8($value->Product_Lot) . '</td>'
                . '<td valign="top" align="center">' . tis620_to_utf8($value->Product_Serial) . '</td>'
                . '<td valign="top" align="center">' . tis620_to_utf8($value->TypeLicense) . '</td>'
                . '<td valign="top">' . tis620_to_utf8($value->Invoice_No) . '</td>'
                . '<td valign="top" style="text-align:right;">' . set_number_format($value->Dispatch_Qty) . '</td>'
                . '<td valign="top" style="text-align:center;">' . $dimension . '</td>'
                . '<td valign="top" style="text-align:right;">' . $value->cbm . '</td>'
                . '<td valign="top" style="text-align:right;">' . set_number_format($value->Dispatch_Qty * $value->cbm) . '</td>'
                . '<td valign="top" align="center">' . tis620_to_utf8($value->Unit_Value) . '</td>';
        $page.='</tr>';
        $noPage++;
        $iTemp++;
        $total += $value->Dispatch_Qty;
        $total_cbm += $value->Dispatch_Qty * $value->cbm;
        $sum_all_cbm += $value->cbm;
    }

    // Case if have remain data
    if ($total_per_group > 0) {
        $page.= '<tr>'
                . '<td style="background-color: #E6F1F6; text-align: center; height: 30px;" colspan="8">End of ' . $hash . '</td>'
                . '<td style="background-color: #E6F1F6; text-align:right; height: 30px;">' . set_number_format($total_per_group) . '</td>'
                . '<td style="background-color: #E6F1F6; text-align:right; height: 30px;"></td>'
                . '<td style="background-color: #E6F1F6; text-align:right; height: 30px;">' . set_number_format($total_cbm_per_group) . '</td>'
                . '<td style="background-color: #E6F1F6; text-align:right; height: 30px;">' . set_number_format($total_cbm_qty_per_group) . '</td>'
                . '<td style="background-color: #E6F1F6;text-align:center; height: 30px;"></td>'
                . '</tr>';
    }
    // ================

    $page.='<tr>
        <td style="background-color: #d9edf7; height: 30px;" colspan="8" align="center">Grand Total</td>
        <td style="background-color: #d9edf7; height: 30px; text-align:right;" >' . set_number_format($total) . '</td>
        <td style="background-color: #d9edf7; height: 30px; text-align:right;" ></td>
        <td style="background-color: #d9edf7; height: 30px; text-align:right;" >' . set_number_format($sum_all_cbm) . '</td>
        <td style="background-color: #d9edf7; height: 30px; text-align:right;">' . set_number_format($total_cbm) . '</td>
        <td style="background-color: #d9edf7; height: 30px;" align="center"></td>
    </tr>';
}
$page.='</tbody></table>';

$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($page);

$filename = 'stock-out-report-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F');
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName);
