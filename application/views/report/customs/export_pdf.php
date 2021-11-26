<?php
date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$margin_foot = ($showfooter=='show')?33:25;
$mpdf = new mPDF('th', 'A4-L', '11', '', 10, 10, 45, $margin_foot, 10, 10);
$xml = $this->config->item("_xml");
list($fdate, $fmonth, $fyear) = explode("/", $from_date);
list($tdate, $tmonth, $tyear) = explode("/", $to_date);

$header = '
<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
    <tr>
        <td height="30" width="200">
            <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
        </td>
       
        <td style="text-align: center;font-weight: bold;">ชื่อลูกค้า '.$this->session->userdata['dept_name'].'</td>
        <td width="200"> </td>
    </tr>
    <tr>
        <td height="25" width="200"></td>
        <td style="text-align: center;font-weight: bold;font-size: 16px">รายงานการส่งของออก</td>
        <td width="200"> </td>
    </tr>
</table>
<table width="100%" style="font-family: arial;font-size: 14px">
<tr><td align="center" colspan="3"><b>ระหว่างวันที่ : </b>' . $fdate . ' ' . nameMonthTH($fmonth) . ' ' . ($fyear + 543) .  '&nbsp;&nbsp;&nbsp;&nbsp;<b>ถึงวันที่ : </b>' . $tdate . ' ' . nameMonthTH($tmonth) . ' ' . ($tyear + 543) .  '</td></tr>
<tr><td height="25" colspan="3" align="center"><img src="images/check.gif"> การส่งของออก &nbsp;&nbsp;&nbsp;&nbsp; <input type="checkbox" checked> การโอนออก &nbsp;&nbsp;&nbsp;&nbsp; <input type="checkbox" checked> ชำระภาษีเพื่อขาย/ใช้ในประเทศ</td></tr>
</table>
';

$footer .= '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
<tr>
<td width="50%" align="left">
        พิมพ์โดย : ' . iconv("TIS-620", "UTF-8", $printBy) . '  ,
        วันที่ : ' . date("d") . ' ' . nameMonthTH(date("m")) . ' ' . (date("Y") + 543) .  ' เวลา : ' . date("H:i:s") . '</td>
        <td align="right">WMSP:'.$revision.' , {PAGENO}/{nb}</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');

$head_table .= '
<style>
    '.$set_css_for_show_column.'
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

<table width="100%" cellspacing="0" style="font-family: arial;font-size:10px; border-right: 1px;" class="tborder">
    <thead>
        <tr style="border:1px solid #333333;">';
            $last = count($header_table);
            $idx = 1;
            foreach ($header_table as $head_t) {
              $border = ($idx == $last ? 'border-right: 1px solid;' : '');
		      $head_table .= '<th style="padding: 5px; '.$border.' ">'.$head_t.'</th>';
              $idx++;
            }
     $head_table .= '
        </tr>
    </thead>
    <tbody>';

$page = '';
$page.=$head_table;

if (!empty($datas)) {
        $i = 0;
    foreach ($datas as $keyProduct => $data) {


        //$page.='<tr style="font-weight:bold;"><td style="text-align: center;">Group by </td><td style="text-align: left;" colspan=8>CE No.'.$keyProduct.'</td></tr>';

        $sum_receive = 0;
        $sum_price = 0;

        foreach ($data as $key => $value) {
            $i++;

            $page.='
                    <tr>
                            <td height="25"  valign="top" align="center" style="padding: 3px;">' . $i . '</td>
                            <td valign="top" align="center" style="padding: 3px;" width=150>'.$value['Doc_Refer_CE'].'</td></div>
                            <td valign="top" align="center" style="padding: 3px;" width=150>'.$value['EOR_Name'].'</td></div>
                            <td valign="top" align="center" style="padding: 3px;">' . iconv("TIS-620","UTF-8",$value['Invoice_No']) . '</td></div>
                            <td valign="top" align="center" style="padding: 3px;">' . $value['Dispatch_Date'] . '</td></div>
                            <td valign="top" align="center" style="padding: 3px;">' . $value['Product_Code'] . '</td></div>
                            <td valign="top" align="left" style="padding: 3px;" width=200>' . iconv("TIS-620","UTF-8",$value['Product_NameEN']). '</td></div>
                            <td valign="top" align="right" style="padding: 3px;">' . set_number_format($value['Dispatch_Qty']) . '</td></div>
                            <td valign="top" align="center" style="padding: 3px;">' . iconv("TIS-620","UTF-8",@$value['unit']). '</td></div>
                            <td valign="top" align="right" width=120 style="padding: 3px; border-right: 1px solid;">' . set_number_format($value["price"])." ".$value["unit_price"] . '</td></div>
                    </tr>';

            $sum_receive+=$value["Dispatch_Qty"]; //รวม receive qty ของแต่ละ group
            $sum_price+=$value["price"]; //รวมราคาของแต่ละ group

            $all_sum_receive+=$value["Dispatch_Qty"]; //รวม receive qty ทั้งหมด
            $all_sum_price+=$value["price"]; //รวมราคาทั้งหมด
        }
        $page.='<tr><td colspan=6>&nbsp;</td><td align=center><b>รวม</b></td><td align=right><b>'.set_number_format($sum_receive).'</b></td><td>&nbsp;</td><td align=right style="border-right: 1px solid; font-weight: bold;"><b>'.set_number_format($sum_price).' '.$value["unit_price"].'</b></td></tr>';
    }
    $page.='<tr><td colspan=6>&nbsp;</td><td align=center><b>รวมทั้งหมด</b></td><td align=right><b>'.set_number_format($all_sum_receive).'</b></td><td>&nbsp;</td><td align=right style="border-right: 1px solid; font-weight: bold;"><b>'.set_number_format($all_sum_price).'</b></td></tr>';
}


$page.='</tbody>
    </table>';


$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($page);
//$mpdf->Output($strSaveName, 'I'); // *** file name ***//

$filename = 'Export-Customs-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F');
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName);

?>