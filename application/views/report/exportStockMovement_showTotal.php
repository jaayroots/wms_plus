<?php
date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 40, 25, 5, 5);
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
$header = '
		<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
		<tr>
			<td height="30" width="200">
                            <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                       	</td>
			<td style="text-align: center;font-weight: bold;">STOCK MOVEMENT & BALANCE REPORT</td>
                        <td width="200"> </td>
		</tr>
		<tr>
			<td colspan="3" align="center" height="30" >รายงานการเคลื่อนไหวและยอดคงเหลือของสินค้า</td>
		</tr>
		</table>
		<table width="100%" style="font-size: 14px; font-family: arial;">
					<tr>
						<td width="15%" height="25" align="right">ผุ้เช่าคลัง : </td>
						<td width="50%">' . $Company_NameEN . '</td>
						<td width="15%" align="right">วันที่เริ่ม : </td><td>' . $search['fdate'] . '</td>
					</tr>
					<tr>
						<td  width="15%" height="25" align="right">รหัส/ชื่อสินค้า : </td>
						<td width="50%">' . $product_code . ' ' . iconv("TIS-620", "UTF-8", $product_name) . '</td>
						<td  width="15%" align="right">วันที่ถึง : </td><td>' . $search['tdate'] . '</td>
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


$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');


########## start loop show page ########
# head_table is header of table
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

		  <table width="100%" cellspacing="0" style="border:1px solid #666666;font-size:10px; font-family: arial;" class="tborder">
			<thead>
				<tr style="border:1px solid #333333;" bgcolor="#B0C4DE">
					<th width="3%" height="30">' . _lang('no') . '</th>
					<th width="15%">' . _lang('product_code') . '</th>
					<th width="35%">' . _lang('product_name') . '</th>
					<th width="10%">' . _lang('income_qty') . '</th>
					<th width="10%">' . _lang('receive_qty') . '</th>
					<th width="10%">' . _lang('dispatch_qty') . '</th>
					<th width="10%">' . _lang('balance') . '</th>
				</tr>
			</thead>
			<tbody>	';

$page = '';
$page.=$head_table;

if(!empty($datas['no_onhand'])){ 
    
    $page.='<tr bgcolor="#F0F8FF ">
                    <td align="center" colspan="14"><h1><font size="2" color="red">'.$datas['no_onhand'].'</font></h1><br></td>
            </tr>';
    
}else if (!empty($datas)) {
    $i = 1;
    $sumReceiveQty = 0;     //add $sumReservQty variable for calculate total Receive qty : by kik : 31-10-2013
    $sumDispatchQty = 0;    //add $sumReceiveQty variable for calculate total Dispatch qty : by kik : 31-10-2013
    $sumBalanceQty = 0;    //add $sumReceiveQty variable for calculate total Balance qty : by kik : 31-10-2013
    $sumStartBalanceQty = 0;    //add by kik : 2013-11-25
    

        foreach ($datas as $key => $value) {
            
            $balance_qty = ($value['incoming_qty']+$value['receive_qty'])-$value['dispatch_qty'];
            $page.=' 
					<tr>
						<td height="23"  align="center" valign="top">' . $i . '</td>
						<td valign="top" align="left">' . $value['Product_Code'] . '</td>
						<td valign="top" align="left">' . iconv("TIS-620", "UTF-8", $value['Product_NameEN']) . '</td>
                                                <td valign="top" align="right">' . set_number_format($value['incoming_qty']) . '</td>
						<td valign="top" align="right">' . set_number_format($value['receive_qty']) . '</td>
						<td valign="top" align="right">' . set_number_format($value['dispatch_qty']) . '</td>
						<td valign="top" align="right">' . set_number_format($balance_qty) . '</td>
					</tr>

					';
            
            // =================================================================
            // start add code for show total : by kik : 2013-11-25
            $sumReceiveQty+=@$value['receive_qty'];  
            $sumDispatchQty+=@$value['dispatch_qty'];    
            $sumStartBalanceQty+=@$value['incoming_qty'];
            $sumBalanceQty+=@$balance_qty; 
             
            $i++;
        }
        
            $page.=' 
                                            <tr bgcolor="#CDC9C9">
                                                    <td colspan="3" style="text-align: center;"><b>Total</b></td>
                                                    <td valign="top" align="right"><b>' .set_number_format($sumStartBalanceQty).'</b></td>
                                                    <td valign="top" align="right"><b>' . set_number_format($sumReceiveQty) . '</b></td>
                                                    <td valign="top" align="right"><b>' . set_number_format($sumDispatchQty) . '</b></td>
                                                    <td valign="top" align="right"><b>' . set_number_format($sumBalanceQty) . '</b></td>
                                            </tr>
                                            ';

            // end add code for show total : by kik : 2013-11-25
            // =================================================================
    
    
}


$page.='</tbody>
			</table>';

$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
//$page = iconv("TIS-620","UTF-8",$page);
$mpdf->WriteHTML($page);

//$mpdf->Output($strSaveName, 'I'); // *** file name ***//

    $filename = 'Stock-Movement-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
    $strSaveName = $settings['uploads']['upload_path'] . $filename;
    $tmp = $mpdf->Output($strSaveName, 'F'); 
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");
    readfile($strSaveName); 
?>