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
		<table width="100%" style="font-size: 14px">
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

//$header = iconv("TIS-620","UTF-8",$header);
//$footer = iconv("TIS-620","UTF-8",$footer);
$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');

//$page = '';
//$count_row = count($datas);

# calculate page number 
# max_line is number of table row in 1 page
//$max_line = 30;
//$totalpage = (int) ($count_row / $max_line);
//if (($count_row % $max_line) != 0) {
//    $totalpage += 1;
//}

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

		  <table width="100%" cellspacing="0" style="border:1px solid #666666;font-size:10px;font-family: arial;" class="tborder">
			<thead>
				<tr style="border:1px solid #333333;" bgcolor="#B0C4DE">
					<th width="3%" height="30">' . _lang('no') . '</th>
					<th width="7%">' . _lang('date') . '</th>
					<th width="12%">' . _lang('number_received') . '</th>
					<th width="15%">' . _lang('Reference_received') . '</th>
					<th width="12%">' . _lang('Number_dispatch') . '</th>
					<th width="15%">' . _lang('Reference_dispatch') . '</th>
					<th width="7%">' . _lang('receive_qty') . '</th>
					<th width="7%">' . _lang('dispatch_qty') . '</th>
					<th width="10%">' . _lang('serial') . "/" . _lang('lot') . '</th>
					<th width="7%">' . _lang('product_mfd') . '</th>
					<th width="7%">' . _lang('product_exp') . '</th>
					<th width="17%">' . _lang('branch') . '</th>
					<th width="7%">' . _lang('location') . '</th>
					<th width="7%">' . _lang('balance') . '</th>
				</tr>
			</thead>
			<tbody>	';

$page = '';
$page.=$head_table;

//for($i=1;$i<=$totalpage;$i++){
# คำนวนหาตำแหน่งของ array ที่จะเอามาแสดงผลบรรทัดแรกในแต่ละหน้า
# start คือตำแหน่งเริ่มต้น
# stop คือตำแหน่งสิ้นสุดของข้อมูลที่จะแสดงในหน้านั้น	 ๆ
//if($i==1){
//	$start=0;
//	$stop=$max_line-1;
//}
//else{
//	$start = $max_line * ($i - 1);
//	$stop = ($max_line * $i) -1;
//}
# เริ่มวนลูปแสดงข้อมูลแต่ละแถ

/* for($j=$start;$j<=$stop;$j++){
  if($j<$count_row){
  $k=$j+1;
  $page.='
  <tr>
  <td height="23"  valign="top">'.$k.'</td>
  <td valign="top" align="center">'.$data[$j]['receive_date'].'</td>
  <td valign="top">'.$data[$j]['receive_doc_no'].'</td>
  <td valign="top">'.$data[$j]['receive_refer_ext'].'</td>
  <td valign="top">'.$data[$j]['pay_doc_no'].'</td>
  <td valign="top">'.$data[$j]['pay_refer_ext'].'</td>
  <td valign="top" align="center">'.number_format($data[$j]['r_qty']).'</td>
  <td valign="top" align="center">'.number_format($data[$j]['p_qty']).'</td>
  <td valign="top">'.$data[$j]['Product_SerLot'].'</td>
  <td valign="top">'.$data[$j]['branch'].'</td>
  <td valign="top">'.$data[$j]['Location_Code'].'</td>
  <td valign="top" align="right">'.number_format($data[$j]['Balance_Qty']).'</td>
  </tr>
  ';
  $final_balance=$data[$j]['Balance_Qty'];
  }
  } */


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
    
    foreach ($datas as $keyRow => $data) {
        $iTemp = 1;
        $sumReceiveQty_inRow = 0;    // add by kik : 25-11-2013
        $sumDispatchQty_inRow = 0;   // add by kik : 25-11-2013

        foreach ($data as $key => $value) {
            if ($iTemp == 1) {
                $page .= '
						<tr bgcolor="#F0F8FF ">
							<td colspan="14" align="left">Lot/Serial : ' . $value['Product_SerLot'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mfd : ' . $value['Product_Mfd'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Exp : ' . $value['Product_Exp'] . '</td>
						</tr>';
            }
            $page.=' 
					<tr>
						<td height="23"  valign="top">' . $i . '</td>
						<td valign="top" align="center">' . $value['receive_date'] . '</td>
						<td valign="top" align="center">' . $value['receive_doc_no'] . '</td>
						<td valign="top" align="center">' . $value['receive_refer_ext'] . '</td>
						<td valign="top" align="center">' . $value['pay_doc_no'] . '</td>
						<td valign="top" align="center">' . $value['pay_refer_ext'] . '</td>
						<td valign="top" align="right">' . set_number_format($value['r_qty']) . '</td>
						<td valign="top" align="right">' . set_number_format($value['p_qty']) . '</td>
						<td valign="top" align="center">' . $value['Product_SerLot'] . '</td>
						<td valign="top" align="center">' . $value['Product_Mfd'] . '</td>
						<td valign="top" align="center">' . $value['Product_Exp'] . '</td>>
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
            
            if($iTemp == sizeof($data)){
                    $sumStartBalanceQty+=@$value['start_balance'];
                    $sumBalanceQty+=$value['Balance_Qty'];     // Add $sumBalanceQty for calculate total qty : by kik : 31-10-2013
                    
                    
                         $page.=' 
							<tr bgcolor="#F8F8FF ">
								<td colspan="6" style="text-align: center;"><b>Incoming Balance : ' .set_number_format($value['start_balance']).'</b></td>
								<td valign="top" align="right"><b>' . set_number_format($sumReceiveQty_inRow) . '</b></td>
								<td valign="top" align="right"><b>' . set_number_format($sumDispatchQty_inRow) . '</b></td>
								<td colspan="5"></td>
								<td valign="top" align="right"><b>' . set_number_format($value['Balance_Qty']) . '</b></td>
							</tr>
							';
                }
                
                
            $i++;
            $iTemp++;
        }
    }
    
    $page.=' 
							<tr bgcolor="#CDC9C9">
								<td colspan="6" style="text-align: center;"><b>Total Incoming Balance : ' .set_number_format($sumStartBalanceQty).'</b></td>
								<td valign="top" align="right"><b>' . set_number_format($sumReceiveQty) . '</b></td>
								<td valign="top" align="right"><b>' . set_number_format($sumDispatchQty) . '</b></td>
								<td colspan="5"></td>
								<td valign="top" align="right"><b>' . set_number_format($sumBalanceQty) . '</b></td>
							</tr>
							';
                        
            // end add code for show total : by kik : 2013-11-25
            // =================================================================
    
    
}


$page.='</tbody>
			</table>';
//if($i!=$totalpage){
//	$page.='<pagebreak />';
//}
# end of show table 30 rows
# if last page show summary 
//if($i==$totalpage){
//	$page.='
//	<table width="100%" style="font-size:14px;border-top:1px solid #000000;border-bottom:2px solid #000000;">
//		<tr>
//			<td height="35" width="60%" align="right">'.iconv("TIS-620","UTF-8",'จำนวนตั้งต้น').'</td>
//			<td width="15%">'.number_format($balance).'</td>
//			<td width="10%" align="right">'.iconv("TIS-620","UTF-8",'จำนวนคงเหลือ').'</td>
//			<td width="15%" align="right">'.number_format($final_balance).'</td>
//		</tr>
//	</table>
//	';
//}
//} // close loop page


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