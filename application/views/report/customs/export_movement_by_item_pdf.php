<?php
date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 40, 25, 5, 5);
list($fdate, $fmonth, $fyear) = explode("/", $form_value['fdate']);
list($tdate, $tmonth, $tyear) = explode("/", $form_value['tdate']);

$header = '
        <table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
        <tr>
            <td height="30" width="200"><img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '"></td>
            <td style="text-align: center;font-weight: bold;"> </td>
            <td width="200"> </td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center;font-weight: bold;" height="30">รายงานการเคลื่อนไหวและยอดคงเหลือสินค้า</td>
        </tr>
        </table>
        <table width="100%" style="font-family: arial;font-size: 14px">
        <tr>
            <td align="right"><b>ผู้เช่าคลัง : </b></td><td> '.$this->session->userdata('dept_name').'</td>
            <td align="right"><b>ระหว่างวันที่ : </b></td><td>' . $fdate . ' ' . nameMonthTH($fmonth) . ' ' . ($fyear + 543) .  '</td>
        </tr>
        <tr>
            <td align="right"><b>รหัส/ชื่อสินค้า : </b></td><td>' . ($form_value['product_id'] != "" ? iconv("TIS-620", "UTF-8", $form_value['product_name']) : " - ") . '</td>
            <td align="right"><b>ถึงวันที่ : </b></td><td>' . $tdate . ' ' . nameMonthTH($tmonth) . ' ' . ($tyear + 543) .  '</td>
        </tr>
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

$head_table = '

        <style>
            .tborder{
                border:1px solid #333333;
            }

            .tborder td,
            .tborder th{
                font-size:10px;
                border-right:1px solid #333333;
                border-bottom:1px solid #333333;
                padding:5px 2px 5px 2px;
            }

            table.tborder thead tr th{
                border-bottom:1px solid #666666;
            }
        </style>

        <table width="100%" cellspacing="0" style="font-size:10px;font-family: arial;"  class="tborder">
            <thead>
                <tr>
                    <th  width="5%" height="30">ลำดับ</th>
                    <th  width="8%">วันที่</th>
                    <th  width="8%">เลขที่อ้างอิงใบรับเข้า</th>
                    <th  width="8%">เลขที่ใบรับเข้า</th>
                    <th  width="8%">เลขที่ใบขนรับเข้า</th>
                    <th  width="8%">เลขที่อ้างอิงใบนำออก</th>
                    <th  width="8%">เลขที่ใบนำออก</th>
                    <th  width="8%">เลขที่ใบขนนำออก</th>
                    <th  width="8%">EOR</th>
                    <th  width="5%">ปริมาณรับเข้า</th>
                    <th  width="5%">ปริมาณนำออก</th>
                    <th  width="8%">ล็อต/ซีเรียล</th>
                    <th  width="8%">วันผลิต</th>
                    <th  width="8%">วันหมดอายุ</th>
                    <th  width="20%">จาก/ถึง</th>
                    <th  width="8%">พื้นที่ของสินค้า</th>
                    <th  width="8%">ปริมาณ</th>
                </tr>
            </thead>
            <tbody>';


$page = '';
//$page.=$header_page;
$page.=$head_table;

# $data have many product
# show each product แสดงรายการเคลื่อนไหวของสินค้าแต่ละชิ้น

if(!empty($datas['no_onhand'])){

    $page.='<tr bgcolor="#F0F8FF ">
                    <td align="center" colspan="17"><h1><font size="2" color="red">'.$datas['no_onhand'].'</font></h1><br></td>
            </tr>';

}else if (!empty($datas)) {

    $noPage = 1;
    $sumReceiveQty = 0;     //add $sumReservQty variable for calculate total Receive qty : by kik : 31-10-2013
    $sumDispatchQty = 0;    //add $sumReceiveQty variable for calculate total Dispatch qty : by kik : 31-10-2013
    $sumBalanceQty = 0;    //add $sumReceiveQty variable for calculate total Balance qty : by kik : 31-10-2013
    $sumStartBalanceQty = 0;    //add by kik : 2013-11-25
    $i=1;
    foreach ($datas as $keyProCode => $values) {
        $iTemp = 1;
         $sumReceiveQty_inRow = 0;    // add by kik : 25-11-2013
         $sumDispatchQty_inRow = 0;   // add by kik : 25-11-2013

            $iTotal = 1;

            foreach ($values as $key => $value) {
                if ($iTemp == 1) {
                    $page .= '
                                <tr>
                                    <td colspan="17" align="left">IOR : '.iconv("TIS-620", "UTF-8", $value['IOR_Name']).', '.$value['Product_Code'] . ' / ' . iconv("TIS-620", "UTF-8", $value['Product_NameEN']) . '</td>
                                </tr>';
                }

                $page.='
                            <tr>
                                <td height="23"  valign="top">'  .$i . '</td>
                                <td valign="top" align="center">' . $value['receive_date'] . '</td>
                                <td valign="top" align="center">' . $value['receive_doc_no'] . '</td>
                                <td valign="top" align="center">' . $value['receive_refer_ext'] . '</td>
                                <td valign="top" align="center">' . $value['CE_IN'] . '</td>
                                <td valign="top" align="center">' . $value['pay_doc_no'] . '</td>
                                <td valign="top" align="center">' . $value['pay_refer_ext'] . '</td>
                                <td valign="top" align="center">' . $value['CE_OUT'] . '</td>
                                <td valign="top" align="center">' . $value['EOR_Name'] . '</td>
                                <td valign="top" align="right">' . set_number_format($value['r_qty']) . '</td>
                                <td valign="top" align="right">' . set_number_format($value['p_qty']) . '</td>
                                <td valign="top" align="center">' . iconv("TIS-620", "UTF-8", $value['Product_SerLot']) . '</td>
                                <td valign="top" align="center">' . $value['Product_Mfd'] . '</td>
                                <td valign="top" align="center">' . $value['Product_Exp'] . '</td>
                                <td valign="top" align="center">' . iconv("TIS-620", "UTF-8", $value['branch']) . '</td>
                                <td valign="top" align="center">' . $value['Location_Code'] . '</td>
                                <td valign="top" align="right">' . set_number_format($value['Balance_Qty']) . '</td>
                            </tr>
                            ';



                $final_balance = $value['Balance_Qty'];

                // =================================================================
                // start add code for show total : by kik : 2013-11-25
                $sumReceiveQty+=$value['r_qty'];     // add by kik : 25-11-2013
                $sumDispatchQty+=$value['p_qty'];    // add by kik : 25-11-2013

                $sumReceiveQty_inRow+=$value['r_qty']; // add by kik : 25-11-2013
                $sumDispatchQty_inRow+=$value['p_qty']; // add by kik : 25-11-2013

                if($iTotal == sizeof($values)){

                    $sumStartBalanceQty+=@$value['start_balance'];
                    $sumBalanceQty+=$value['Balance_Qty'];     // Add $sumBalanceQty for calculate total qty : by kik : 31-10-2013


                         $page.='
                            <tr>
                                <td colspan="9" style="text-align: center;"><b>ปริมาณตั้งต้นทั้งหมด: ' .set_number_format($value['start_balance']).'</b></td>
                                <td valign="top" align="right"><b>' . set_number_format($sumReceiveQty_inRow) . '</b></td>
                                <td valign="top" align="right"><b>' . set_number_format($sumDispatchQty_inRow) . '</b></td>
                                <td colspan="5"> </td>
                                <td valign="top" align="right"><b>' . set_number_format($value['Balance_Qty']) . '</b></td>
                            </tr>
                            ';
                }

                $noPage++;
                $iTemp++;
                $iTotal++;
                $i++;

            }

    } // close loop show product_name

                        $page.='
                            <tr>
                                <td colspan="9" style="text-align: center;"><b>ปริมาณทั้งหมด : ' .set_number_format($sumStartBalanceQty).'</b></td>
                                <td valign="top" align="right"><b>' . set_number_format($sumReceiveQty) . '</b></td>
                                <td valign="top" align="right"><b>' . set_number_format($sumDispatchQty) . '</b></td>
                                <td colspan="5"></td>
                                <td valign="top" align="right"><b>' . set_number_format($sumBalanceQty) . '</b></td>
                            </tr>
                            ';

                        // end add code for show total : by kik : 2013-11-25
                        // =================================================================


}//} // end check have value in datas
$page.='</tbody></table>';

//$page.='<pagebreak />';
//p($datas);
//echo $page;exit();

$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($page);

    $filename = 'Stock-Movement-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
    $strSaveName = $settings['uploads']['upload_path'] . $filename;
    $tmp = $mpdf->Output($strSaveName, 'F');
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");
    readfile($strSaveName);
?>