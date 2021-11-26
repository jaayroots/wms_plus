<?php

date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$mpdf = new mPDF('UTF-8');
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 20, 25, 5, 5);
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
$header = '
		<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
		
		<tr>
			<td height="30" width="200">
                        <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                    </td>
                    <td style="text-align: center;font-weight: bold;">' . iconv("TIS-620", "UTF-8", 'Daily Delivery Report') . ' ' . date("M Y") . '</td>
                        <td width="200"> </td>
		</tr>
		</table>
				
		';
//ADD BY POR 2013-12-18 แสดงส่วนท้าย(เซ็นชื่อ) ตอนกด Approve Receive
if ($showfooter == 'show'):
    $footer .= '
                <div style="width:100%;font-size:9px;">
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
						<td align="right">WMS:' . $revision . ' , {PAGENO}/{nb}</td>
					</tr>
					</table>';

//		$header = iconv("TIS-620","UTF-8",$header); // Comment By Akkarapol, 12/09/2013, คอมเม้นต์ทิ้งเพราะถ้าเอาไว้มันจะไม่สามารถ Generate PDF ได้
//		$footer = iconv("TIS-620","UTF-8",$footer); // Comment By Akkarapol, 12/09/2013, คอมเม้นต์ทิ้งเพราะถ้าเอาไว้มันจะไม่สามารถ Generate PDF ได้
$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');

# head_table is header of table
$head_table = '
		  <style>
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
				<tr style="border:1px solid #333333;">
					<th width="70" height="35">' . iconv("TIS-620", "UTF-8", _lang('asn_date')) . '</th>
                    <th width="100">' . iconv("TIS-620", "UTF-8", _lang('po_no')) . '</th>
                    <th width="100">' . iconv("TIS-620", "UTF-8", _lang('document_no')) . '</th>
                    <th width="100">' . iconv("TIS-620", "UTF-8", _lang('doc_refer_int')) . '</th>
					<th width="100">' . iconv("TIS-620", "UTF-8", _lang('consinee')) . '</th>
					<th width="95">' . iconv("TIS-620", "UTF-8", _lang('product_code')) . '</th>
					<th >' . iconv("TIS-620", "UTF-8", _lang('product_name')) . '</th>';

if (@$settings['invoice']):
    $head_table.='<th >' . iconv("TIS-620", "UTF-8", _lang('invoice_out')) . '</th>';
endif;
if (@$settings['container']):
    $head_table.='<th >' . iconv("TIS-620", "UTF-8", _lang('container_out')) . '</th>';
endif;

$head_table.='
					<th width="70">' . iconv("TIS-620", "UTF-8", _lang('receive_qty')) . '</th>
					<th width="70">' . iconv("TIS-620", "UTF-8", _lang('dispatch_date')) . '</th>
					<th width="70">' . iconv("TIS-620", "UTF-8", _lang('dispatch_qty')) . '</th>
                                        
					';
if ($price_per_unit == TRUE) {
    $head_table.='<th width="70">' . iconv("TIS-620", "UTF-8", _lang('price_per_unit')) . '</th>
                                        <th width="70">' . iconv("TIS-620", "UTF-8", _lang('unit_price')) . '</th>
                                        <th width="70">' . iconv("TIS-620", "UTF-8", _lang('all_price')) . '</th>';
}
if ($build_pallet == TRUE) {
    $head_table.='<th width="70">' . iconv("TIS-620", "UTF-8", _lang('pallet_code_out')) . '</th>';
}

$head_table.='<th width="100">' . iconv("TIS-620", "UTF-8", _lang('remark')) . '</th>
                                    </tr>
                            </thead>
                            <tbody>	';
                            

$page = '';
########## start loop show page ########
$count_row = count($data);

$page.=$head_table;

    $total_receive = 0;
    $total_dispatch = 0;
    $total_price_per_unit = 0;
    $total_all_price = 0;

foreach ($data as $j => $value) {
    if ($j < $count_row) {
        $k = $j + 1;

        $total_receive += $value->Reserv_Qty;
        $total_dispatch += $value->Confirm_Qty;
        $total_price_per_unit += $value->Price_Per_Unit;
        $total_all_price += $value->All_Price;

        

        $page.='<tr><td align="center">' . $value->Estimate_Action_Date . '</td>
            <td align="center">' . $value->Doc_Refer_Ext . '</td>
            <td align="center">' . $value->Document_No . '</td>
            <td align="center">' . $value->Doc_Refer_Int . '</td>
            <td >' . iconv("TIS-620", "UTF-8", $value->consignee) . '</td>
            <td align="center">' . $value->Product_Code . '</td>
            <td>' . iconv("TIS-620", "UTF-8", $value->Product_NameEN) . '</td>';

        if (@$settings['invoice']):
            $page.='<td>' . iconv("TIS-620", "UTF-8", $value->Invoice_No) . '</td>';
        endif;
        
        if (@$settings['container']):
            $page.='<td>' . iconv("TIS-620", "UTF-8", $value->Cont) . '</td>';
        endif;

        $page .='<td align="right">' . set_number_format($value->Reserv_Qty) . '</td>
            <td align="center">' . $value->Actual_Action_Date . '</td>
            <td align="right">' . set_number_format($value->Confirm_Qty) . '</td>';

        if ($price_per_unit == TRUE) {
            // p($price_per_unit);exit;
            $page.='<td align="right">' . set_number_format($value->Price_Per_Unit) . '</td>
            <td align="center">' . iconv("TIS-620", "UTF-8", $value->Unit_Price_value) . '</td>
            <td align="right">' . set_number_format($value->All_Price) . '</td>';
        }

        if ($build_pallet == TRUE) {
            $page.='<td align="center">' . $value->Pallet_Code . '</td>';
        }

        $page.= '<td>' . iconv("TIS-620", "UTF-8", $value->Remark) . '</td>
        </tr>';

    }
}

        $cols = 7;
        $page.='<tr>';

        if (@$settings['invoice']):
            $cols += 1;
        endif;

        if (@$settings['container']):
            $cols += 1;
        endif;

        $page.='<td align="center" colspan="'.$cols.'">Total</td>';
        $page.='<td align="right">'.set_number_format($total_receive).'</td>';
        $page.='<td></td>';
        $page.='<td align="right">'.set_number_format($total_dispatch).'</td>';
        $page.='<td align="right">'.set_number_format($total_price_per_unit).'</td>';
        // $page.='<td></td>';
        // $page.='<td align="right">'.set_number_format($total_all_price).'</td>';
        // $page.='<td colspan="2">&nbsp;</td>';

        $page.= '</tr>';


$page.='</tbody></table>';
// p($page);exit;

$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($page);

$filename = 'DDR-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F');
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName);
?>