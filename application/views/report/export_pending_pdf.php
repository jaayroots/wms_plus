<?php

date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$mpdf = new mPDF('UTF-8');
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 50, 25, 5, 5);
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
$header = '
		<table border="0" width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
                    <tr>			
                        <td height="30" width="450">
                            <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                        </td>
                        <td>
                            <font size="5"><b>' . $text_header . '</b></font>
                        </td>
                        <td width="450"> </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            &nbsp;
                        </td>
                    </tr>
		</table>
                
                <table width="700" align="center" border="0" style="font-family: arial;font-size:14px;">
                
                    <tr>
                        <td width="120">
                            ' . _lang('renter') . '
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="320">
                            ' . $renter_name . '
                        </td>
                        <td width="120">
                            ' . _lang('shipper') . '
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="320">
                            ' . $shipper_name . '
                        </td>
                        <td width="120">
                            ' . _lang('consignee') . '
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="320">
                            ' . $consignee_name . '
                        </td>
                    </tr>
                    
                    <tr>
                        <td width="120">
                            ' . _lang('document_no') . '
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="320">
                            ' . $document_no . '
                        </td>
                        <td width="120">
                            ' . _lang('document_ext') . '
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="320">
                            ' . $doc_refer_ext . '
                        </td>
                        <td width="120">
                            ' . _lang('document_internal') . '
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="320">
                            ' . $doc_refer_int . '
                        </td>
                    </tr>    
                    
                    <tr>
                        <td width="120">
                            ' . _lang('invoice_no') . '
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="320">
                            ' . $doc_refer_inv . '
                        </td>
                        <td width="120">
                            ' . _lang('customs_entry') . '
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="320">
                            ' . $doc_refer_ce . '
                        </td>
                        <td width="120">
                            ' . _lang('bl_no') . '
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="320">
                            ' . $doc_refer_bl . '
                        </td>
                    </tr>    
                    
                    <tr>
                        <td width="120">
                            ' . _lang('receive_type') . '
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="320">
                            ' . $receive_type_name . '
                        </td>
                        <td width="120">
                            ' . _lang('receive_date') . '
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="320">
                            ' . $receive_date . '
                        </td>
                        <td width="120">
                            ' . _lang('worker_name') . '
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="320">
                            ' . $worker_name . '
                        </td>
                    </tr>  
                    <tr>
                        <td width="120">
                            ' . _lang('remark') . '
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td colspan="6">
                            ' . $remark . '
                        </td>
                    </tr>
                    
                </table>
				
		';

//ADD BY POR 2013-12-18 แสดงส่วนท้าย(เซ็นชื่อ) ตอนกด Approve Receive
if($showfooter=='show'):
$footer .= '
<div style="width:100%;font-size:10px;">
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
						<td align="right">WMS:'.$revision.' , {PAGENO}/{nb}</td>
					</tr>
					</table>';

$header = iconv("TIS-620", "UTF-8", $header);
$footer = iconv("TIS-620","UTF-8",$footer);
$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');

# head_table is header of table
$head_table = '<table width="100%" cellspacing="0" border="0">
                    <tr>';
foreach ($column as $keyH => $h) {
    $cssBorder = '';
    if ($keyH == 0):
        $cssBorder = 'border-left:1px solid black;border-radius-corner: 5px;';
    elseif ($keyH == count($column) - 1):
        $cssBorder = 'border-right:1px solid black;border-radius-corner: 5px;';
    endif;
    $head_table.='<th style="border-bottom:1px solid black;border-top:1px solid black;' . $cssBorder . '">' . $h . '</th>';
}

$head_table.='</tr>';

$page = '';
########## start loop show page ########

$count_row = count($pending_detail);

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

    $sum_reserve_qty = 0;
    $sum_confirm_qty = 0;

    for ($j = $start; $j <= $stop; $j++) {
        if ($j < $count_row) {
            $k = $j + 1;
            $bg = '';
//            if ($pending_detail[$j]['to_location'] != $pending_detail[$j]['act_location']) {
//                $bg = 'style="color:#FF0000"';
//            }
            $cssStyle = 'style="font-size:12px;border-bottom: 1px solid black;"';
            /* COMMENT BY POR 2013-12-18 เปลี่ยนรูปแบบการแสดงผลแบบ array
            $sum_reserve_qty = $sum_reserve_qty + $pending_detail[$j]->Reserv_Qty;
            $sum_confirm_qty = $sum_confirm_qty + $pending_detail[$j]->Confirm_Qty;

            $page.='
					<tr>
						<td align="center" width="50" ' . $cssStyle . '>' . $k . '</td>
						<td align="center" width="90" ' . $cssStyle . '>' . $pending_detail[$j]->Product_Code . '</td>
						<td align="left" width="150" ' . $cssStyle . '>' . iconv("TIS-620", "UTF-8", $pending_detail[$j]->Full_Product_Name) . '</td>
						<td align="left" width="80" ' . $cssStyle . '>' . $pending_detail[$j]->Status_Value . '</td>
						<td align="left" width="80" ' . $cssStyle . '>' . $pending_detail[$j]->Sub_Status_Value . '</td>
						<td align="center" width="90" ' . $cssStyle . '>' . $pending_detail[$j]->Product_Lot . '</td>
						<td align="center" width="90" ' . $cssStyle . '>' . $pending_detail[$j]->Product_Serial . '</td>
						<td align="center" width="90" ' . $cssStyle . '>' . $pending_detail[$j]->Product_Mfd . '</td>
						<td align="center" width="90" ' . $cssStyle . '>' . $pending_detail[$j]->Product_Exp . '</td>
						<td align="center" width="60" ' . $cssStyle . '>' . $pending_detail[$j]->Reserv_Qty . '</td>
						<td align="center" width="60" ' . $cssStyle . '>' . $pending_detail[$j]->Confirm_Qty . '</td>
						<td align="center" width="40" ' . $cssStyle . '>' . $pending_detail[$j]->Unit_Id . '</td>
						<td align="center" width="60" ' . $cssStyle . '>' . $pending_detail[$j]->Old_Location . '</td>
						<td align="center" width="60" ' . $cssStyle . '>' . $pending_detail[$j]->Suggest_Location . '</td>
						<td align="center" width="60" ' . $cssStyle . '>' . $pending_detail[$j]->Actual_Location . '</td>
						<td align="left" width="150" ' . $cssStyle . '>' . iconv("TIS-620", "UTF-8", $pending_detail[$j]->Remark) . '</td>
					</tr>
					
					';
             END COMMENT*/
            
            //ADD BY POR 2013-12-18 แสดงผลแบบ array ใหม่ที่ส่งมา
            $sum_reserve_qty = $sum_reserve_qty + str_replace(",", "", $pending_detail[$j]["Reserv_Qty"]);
            $sum_confirm_qty = $sum_confirm_qty + str_replace(",", "", $pending_detail[$j]["Confirm_Qty"]);

            $page.='
					<tr>
						<td align="center" width="50" ' . $cssStyle . '>' . $k . '</td>
						<td align="center" width="90" ' . $cssStyle . '>' . $pending_detail[$j]["Product_Code"] . '</td>
						<td align="left" width="150" ' . $cssStyle . '>' . iconv("TIS-620", "UTF-8", $pending_detail[$j]["Full_Product_Name"]) . '</td>
						<td align="left" width="80" ' . $cssStyle . '>' . $pending_detail[$j]["Status_Value"] . '</td>
						<td align="left" width="80" ' . $cssStyle . '>' . $pending_detail[$j]["Sub_Status_Value"] . '</td>
						<td align="center" width="90" ' . $cssStyle . '>' . $pending_detail[$j]["Product_Lot"] . '</td>
						<td align="center" width="90" ' . $cssStyle . '>' . $pending_detail[$j]["Product_Serial"] . '</td>
						<td align="center" width="90" ' . $cssStyle . '>' . $pending_detail[$j]["Product_Mfd"] . '</td>
						<td align="center" width="90" ' . $cssStyle . '>' . $pending_detail[$j]["Product_Exp"] . '</td>
						<td align="center" width="60" ' . $cssStyle . '>' . set_number_format($pending_detail[$j]["Reserv_Qty"]) . '</td>
						<td align="center" width="60" ' . $cssStyle . '>' . set_number_format($pending_detail[$j]["Confirm_Qty"]) . '</td>
						<td align="center" width="40" ' . $cssStyle . '>' . $pending_detail[$j]["Unit_Value"] . '</td>
						<td align="center" width="60" ' . $cssStyle . '>' . $pending_detail[$j]["Old_Location"] . '</td>
						<td align="center" width="60" ' . $cssStyle . '>' . $pending_detail[$j]["Suggest_Location"] . '</td>
						<td align="center" width="60" ' . $cssStyle . '>' . $pending_detail[$j]["Actual_Location"] . '</td>
						<td align="left" width="150" ' . $cssStyle . '>' . iconv("TIS-620", "UTF-8", $pending_detail[$j]["Remark"]) . '</td>
					</tr>
					
					';
            //END ADD
        }
    }

    if ($i != $totalpage) {
        $page.='<pagebreak />';
    }
    # end of show table 30 rows
} // close loop page
$page.='
                        <tr>
                            <td align="center" ' . $cssStyle . '> Total </td>
                            <td align="center" ' . $cssStyle . '> </td>
                            <td align="center" ' . $cssStyle . '> </td>
                            <td align="center" ' . $cssStyle . '> </td>
                            <td align="center" ' . $cssStyle . '> </td>
                            <td align="center" ' . $cssStyle . '> </td>
                            <td align="center" ' . $cssStyle . '> </td>
                            <td align="center" ' . $cssStyle . '> </td>
                            <td align="center" ' . $cssStyle . '> </td>
                            <td align="center" ' . $cssStyle . '>' . set_number_format($sum_reserve_qty) . '</td>
                            <td align="center" ' . $cssStyle . '>' . set_number_format($sum_confirm_qty) . '</td>
                            <td align="center" ' . $cssStyle . '> </td>
                            <td align="center" ' . $cssStyle . '> </td>
                            <td align="center" ' . $cssStyle . '> </td>
                            <td align="center" ' . $cssStyle . '> </td>
                            <td align="center" ' . $cssStyle . '> </td>
                        </tr>
			</table>';
//echo $page;


$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
//		$page = iconv("TIS-620","UTF-8",$page);

$mpdf->WriteHTML($page);

//$mpdf->Output($strSaveName, 'I'); // *** file name

$filename = 'Pending-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F'); 
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName); 
?>