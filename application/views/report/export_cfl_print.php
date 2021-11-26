<?php

date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$mpdf = new mPDF('UTF-8');
$mpdf = new mPDF('th', 'A4', '11', '', 5, 5, 50, 25, 5, 5);
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
$header = '
		<table border="0" width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
                    <tr>			
                        <td height="30" width="300">
                            <img style="width:150px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                        </td>
                        <td>
                            <font size="5"><b>รายงานยืนยัน Location</b></font>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            &nbsp;
                        </td>
                    </tr>
		</table>
                
                <table width="700" align="center" border="0" style="font-family: arial; font-size: 14px;">
                    <tr>
                        <td width="90">
                            เลขที่อ้างอิง
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="450">
                            ' . $data[0]->Flow_Id . '
                        </td>
                        <td width="30">
                            วันที่
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td >
                            ' . $Receive_Date . '
                        </td>
                    </tr>
                    <tr>
                        <td>
                            เลขที่ PO No.
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data[0]->Doc_Refer_Ext . '
                        </td>
                        <td>

                        </td>
                        <td>

                        </td>
                        <td>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            เลขที่ GRN
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data[0]->Document_No . '
                        </td>
                        <td>

                        </td>
                        <td>

                        </td>
                        <td>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            ผู้เช่าคลัง
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data[0]->Renter_Id . '
                        </td>
                        <td>

                        </td>
                        <td>

                        </td>
                        <td>

                        </td>
                    </tr>
                </table>
				
		';


$footer = '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
					<tr>
                                        <td width="50%" align="left">
                                                Print By : ' . iconv("TIS-620", "UTF-8", $printBy) . '  , 
                                                Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
						<td align="right">{PAGENO}/{nb}</td>
					</tr>
					</table>';

//		$header = iconv("TIS-620","UTF-8",$header);
//		$footer = iconv("TIS-620","UTF-8",$footer);
$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');

# head_table is header of table
$head_table = '<table width="100%" cellspacing="0" style="font-family: arial;font-size:10px;">
                    <tr>';
foreach ($column as $keyH => $h) {
    $cssBorder = '';
    if ($keyH == 0):
        $cssBorder = 'border-left:1px solid black;border-radius-corner: 5px;';
    elseif($keyH == count($column)-1):
        $cssBorder = 'border-right:1px solid black;border-radius-corner: 5px;';
    endif;
    $head_table.='<th style="border-bottom:1px solid black;border-top:1px solid black;' . $cssBorder . '">' . $h . '</th>';
}

$head_table.='</tr>';

$page = '';
########## start loop show page ########
$count_row = count($data);

# calculate page number 
# max_line is number of table row in 1 page
$max_line = 30;
$totalpage = (int) ($count_row / $max_line);
if (($count_row % $max_line) != 0) {
    $totalpage += 1;
}
$page.=$head_table;
$sum_all = 0;
for ($i = 1; $i <= $totalpage; $i++) {

    # �ӹǹ�ҵ��˹觢ͧ array ����������ʴ��ź�÷Ѵ�á�����˹��
    # start ��͵��˹��������
    # stop ��͵��˹�����ش�ͧ�����ŷ����ʴ��˹�ҹ�� �
    if ($i == 1) {
        $start = 0;
        $stop = $max_line - 1;
    } else {
        $start = $max_line * ($i - 1);
        $stop = ($max_line * $i) - 1;
    }
    # �����ǹ�ٻ�ʴ�������������
    for ($j = $start; $j <= $stop; $j++) {
        if ($j < $count_row) {
            $k = $j + 1;
            $bg = '';
            if ($data[$j]->Suggest_Location_Id != $data[$j]->Actual_Location_Id) {
                $bg = 'style="color:#FF0000"';
            }
            $sum_all = $sum_all + $data[$j]->qty;
            $cssStyle = 'style="font-size:10px;border-bottom: 1px solid black;"';
            $page.='
					<tr>
						<td align="right" width="10" ' . $cssStyle . '>' . $k . '</td>
						<td align="center" width="100" ' . $cssStyle . '>' . $data[$j]->Product_Code . '</td>
						<td align="left" width="200" ' . $cssStyle . '>' . iconv("TIS-620", "UTF-8", $data[$j]->Product_NameEN) . '</td>
						<td align="center" ' . $cssStyle . '>' . $data[$j]->Product_Serial . ' / ' . $data[$j]->Product_Lot . '</td>
						<td align="center" width="70" ' . $cssStyle . '>' . $data[$j]->Product_Mfd . '</td>
						<td align="center" width="70" ' . $cssStyle . '>' . $data[$j]->Product_Exp . '</td>
						<td align="right" width="50" ' . $cssStyle . '>' . number_format((int) $data[$j]->qty) . '</td>
						<td ' . $bg . ' align="center" width="75" ' . $cssStyle . '>' . $data[$j]->Actual_Location_Id . '</td>
					</tr>
					';

            //$final_balance=$data[$j]['Balance_Qty'];
        }
    }

    if ($i != $totalpage) {
        //$page.='<pagebreak />';
    }
    # end of show table 30 rows
} // close loop page
$page.='
                        <tr>
                            <td colspan="6" align="right" ' . $cssStyle . '>
                                <b>รวม : </b>
                            </td>
                            <td align="center" ' . $cssStyle . '>
                                ' . $sum_all . '
                            </td>
                            <td ' . $cssStyle . '>
                                &nbsp;
                            </td>
                        </tr>
			</table>';
//echo $page;


$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
//		$page = iconv("TIS-620","UTF-8",$page);

$mpdf->WriteHTML($page);

//$mpdf->Output($strSaveName, 'I'); // *** file name

    $filename = 'Confirm-Location-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
    $strSaveName = $settings['uploads']['upload_path'] . $filename;
    $tmp = $mpdf->Output($strSaveName, 'F'); 
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");
    readfile($strSaveName); 
?>