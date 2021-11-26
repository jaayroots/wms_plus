<?php
date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 40, 25, 5, 5);
list($fdate, $fmonth, $fyear) = explode("/", $form_value['fdate']);
list($tdate, $tmonth, $tyear) = explode("/", $form_value['tdate']);
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
            <td colspan="3" style="text-align: center;font-weight: bold;" height="30">รายงานการนำของออกเพื่อการอื่นเป็นการชั่วคราวและนำกลับ</td>
        </tr>
        </table>
        <table width="100%" style="font-family: arial;font-size: 14px">
        <tr><td align="center" colspan="3"><b>ระหว่างวันที่ : </b>' . $fdate . ' ' . nameMonthTH($fmonth) . ' ' . ($fyear + 543) .  '&nbsp;&nbsp;&nbsp;&nbsp;<b>ถึงวันที่ : </b>' . $tdate . ' ' . nameMonthTH($tmonth) . ' ' . ($tyear + 543) .  '</td></tr>
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
                    <th colspan="7">รายการนำของออกเพื่อการอื่นเป็นการชั่วคราว</th>
                    <th colspan="5">รายการนำของกลับเข้ามาในเขตปลอดฯ</th>
                    <th colspan="1"></th>
                    
                    <th rowspan="2" width="50px">ปริมาณ/หน่วยคงเหลือ</th>
                    <th rowspan="2" width="100px">หมายเหตุนำกลับภายใน</th>
                </tr>
                <tr>
                    <th  width="35px" height="30">ลำดับ</th>
                    <th  width="100px">วันที่ยืมของออก</th>
                    <th  width="150px">เลขที่คำร้อง</th>
                    <th  width="150px">รหัสสินค้า/วัตถุดิบ</th>
                    <th  width="215px">ชนิดของ</th>
                    <th  width="50px">ปริมาณ/หน่วยที่นำออก</th>
                    <th  width="50px">มูลค่า(บาท)</th>
                    <th  width="100px">วันที่ตรวจรับกลับคืน</th>
                    <th  width="150px">รหัสสินค้า/วัตถุดิบ</th>
                    <th  width="215px">ชนิดของ</th>
                    <th  width="50px">ปริมาณ/หน่วยที่นำกลับ</th>
                    <th  width="50px">มูลค่า(บาท)</th>
                    <th  width="100px">จำนวนวันที่ยืม</th>
                    
                </tr>
            </thead>
            <tbody>';


$page = '';
//$page.=$header_page;
$page.=$head_table;

# $data have many product
# show each product แสดงรายการเคลื่อนไหวของสินค้าแต่ละชิ้น

if(empty($datas)):
    $page.='<tr bgcolor="#F0F8FF ">
                    <td align="center" colspan="15"><h1><font size="2" color="red">No Data Available.</font></h1><br></td>
            </tr>';
else:
    $i=0;
    foreach ($datas as $key => $data) :
        $rowspan = count($data);
        $i++;
        foreach ($data as $keys => $value):
            $confirm_qty = set_number_format($value["Confirm_Qty"]);
            $all_price = set_number_format($value["all_price"]);
            $num = $i;
            if(empty($value["out_date"])):
                $num="";
                $confirm_qty = "";
                $all_price = "";
            endif;

            $import_Receive_Qty = set_number_format($value["import_Receive_Qty"]);
            $import_All_Price = set_number_format($value["import_All_Price"]);
            if(empty($value["Inbound_Id_His"])):
                $import_Receive_Qty = "";
                $import_All_Price = "";
            endif;
            
            $page.='<tr style="height: 30px;" valign="top">';
            
            #ให้แสดงเฉพาะรายการแรก กรณีที่มีรายการซ้ำ
            if(!empty($rowspan)):
                $page.='<td width="35px" align="center" valign="top" rowspan='.$rowspan.'>'.$num.'</td>
                    <td width="100px" align=center valign="top" rowspan='.$rowspan.'>'.$value["out_date"].'</td>
                    <td width="150px" align="left" valign="top" rowspan='.$rowspan.'>'.$value["Custom_Doc_Ref"].'</td>
                    <td width="150px" valign="top" rowspan='.$rowspan.'>'.$value["Product_Code"].'</td>
                    <td width="215px" align="left" valign="top" rowspan='.$rowspan.'>'.$value["Product_Name"].'</td>
                    <td width="100px" align="right" valign="top" rowspan='.$rowspan.'>'.$confirm_qty.'</td>
                    <td width="100px" align="right" valign="top" rowspan='.$rowspan.'>'.$all_price.'</td>';
            endif;
            
            $page.='
                <td width="100px" align="center" valign="top">'.$value["import_date"].'</td>
                <td width="150px" valign="top">'.$value["import_Product_Code"].'</td>
                <td width="215px" align="left" valign="top">'.$value["import_Product_Name"].'</td>
                <td width="100px" align="right" valign="top">'.$import_Receive_Qty.'</td>
                <td width="100px" align="right" valign="top">'.$import_All_Price.'</td>
                <td width="50px" align="right" valign="top">'.$value["date_diff"].'</td>';
            
            if(!empty($rowspan)):
                $page.='<td width="100px" valign="top" align="right" rowspan='.$rowspan.'>'.set_number_format($value["remain_qty"]).'</td>';
                $page.='<td width="100px" align="left" valign="top" rowspan='.$rowspan.'>'.iconv("TIS-620","UTF-8",$value["Remark"]).'</td>';
                if($rowspan > 1):
                    $rowspan = "";
                endif;
            endif;
            
            $page.='</tr>';
        endforeach;          
    endforeach;
    
endif;    
        

$page.='</tbody></table>';

//$page.='<pagebreak />';
//p($datas);
//echo $page;exit();

$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($page);

    $filename = 'Borrow-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
    $strSaveName = $settings['uploads']['upload_path'] . $filename;
    $tmp = $mpdf->Output($strSaveName, 'F');
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");
    readfile($strSaveName);
?>