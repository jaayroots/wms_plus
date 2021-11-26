<?php

date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 40, 25, 5, 5);

list($fdate, $fmonth, $fyear) = explode("/", $from_date);

$header = '
        <table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
        <tr>
            <td height="30" width="200">
                                <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                            </td>
             <td style="text-align: center;font-weight: bold;">ชื่อลูกค้า ' . $this->session->userdata['dept_name'] . '</td>
            <td width="200"> </td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center;font-weight: bold;" height="30">รายงานของคงเหลือ</td>
        </tr>
        </table>
        <table width="100%" style="font-family: arial;font-size: 14px">
        <tr><td align="center" colspan="3"><b>ณ วันที่ : </b>' . $fdate . ' ' . nameMonthTH($fmonth) . ' ' . ($fyear + 543) . '</td></tr>
        <tr><td height="25" colspan="3" align="center"><img src="images/check.gif"> วัตถุดิบ &nbsp;&nbsp;&nbsp;&nbsp; <input type="checkbox" checked> สินค้าสำเร็จรูป &nbsp;&nbsp;&nbsp;&nbsp; <input type="checkbox" checked> เครื่องจักร/เครื่องมือ/อุปกรณ์</td></tr>        
</table>
        ';

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

$head_table .= '
<style>
    ' . $set_css_for_show_column . '
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

<table width="100%" cellspacing="0" style="font-size:10px;font-family: arial;"  class="tborder">
    <thead>
        <tr>';

foreach ($header_table as $head_t) {
    $head_table .= '<th>' . $head_t . '</th>';
}
$head_table .= '       
        </tr>
    </thead>
    <tbody>';

$page = '';
$page.=$head_table;

if (!empty($datas)) {
    $all_sum_balance = 0;
    $sum_all_price = 0;
    foreach ($datas as $keyProduct => $data) {

        $i = 0;
        //   $page.='<tr style="font-weight:bold;"><td style="background-color:#BBBBBB;text-align: left;" colspan=6>CE No.'.$keyProduct.' IOR: XXX </td></tr>';
        $page.='<tr style="font-weight:bold;"><td style="background-color:#BBBBBB;text-align: center;">#</td><td style="background-color:#BBBBBB;text-align: left;" colspan=6>CE No.' . $data[0]['Doc_Refer_CE'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IOR:' . $data[0]['Vendor_Name'] . '</td></tr>';
        $sum_balance = 0;
        $sum_price = 0;
        foreach ($data as $key => $value) {
            $i++;

            $page.='
                    <tr>
                            <td height="25"  valign="top" align="center">' . $i . '</td>
                            <td valign="top" align="center" width=150>&nbsp;</td>
                            <td valign="top" align="center">' . $value['Product_Code'] . '</td>
                            <td valign="top" align="left" width=250>' . iconv("TIS-620", "UTF-8", $value['Product_NameEN']) . '</td>
                            <td valign="top" align="right">' . set_number_format($value['Balance_Qty']) . '</td>
                            <td valign="top" align="center">' . iconv("TIS-620", "UTF-8", @$value['unit']) . '</td>
                            <td valign="top" width="10%" align="right">' . set_number_format($value["Price"]) . " " . $value["unit_price"] . '</td></div>
                    </tr>';

            $sum_balance+=$value["Balance_Qty"]; //รวม balance qty ของแต่ละ group
            $sum_price+=$value["Price"];
            $sum_all_price+=$value["Price"];
            $all_sum_balance+=$value["Balance_Qty"]; //รวม balance qty ทั้งหมด
        }
        $page.='<tr><td colspan=3>&nbsp;</td><td align=center><b>Total</b></td><td align=right>' . set_number_format($sum_balance) . '</td><td>&nbsp;</td><td align=right>' . set_number_format($sum_price) . ' ' . $value["unit_price"] . '</td></tr>';
    }
    $page.='<tr><td colspan=3>&nbsp;</td><td align=center><b>All Total</b></td><td align=right>' . set_number_format($all_sum_balance) . '</td><td>&nbsp;</td><td align=right>' . set_number_format($sum_all_price) . '</td></tr>';
}


$page.='</tbody>
    </table>';


$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($page);
//$mpdf->Output($strSaveName, 'I'); // *** file name ***//

$filename = 'Remain-Customs-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F');
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName);
?>