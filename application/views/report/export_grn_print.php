<?php
date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 20, 25, 5, 5);
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
$header = '
		<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
                <tr>			
                    <td height="30" width="200">
                        <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                    </td>
                    <td style="text-align: center;font-weight: bold;">
                        Daily Report Goods Receiving Note ' . date("M Y") . '
                    </td>
                    <td width="200"> </td>
		</tr>
		</table>
				
		';

$footer = '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
					<tr>
                                        <td width="50%" align="left">
                                                Print By : '.iconv("TIS-620", "UTF-8", $printBy).'  , 
                                                Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
						<td align="right">{PAGENO}/{nb}</td>
					</tr>
					</table>';

$header = iconv("TIS-620", "UTF-8", $header);
$footer = iconv("TIS-620", "UTF-8", $footer);
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
					<th width="7%" height="35"> ASN DATE</th>
					<th width="7%">' . iconv("TIS-620", "UTF-8", 'PO.NO') . '</th>
					<th width="16%">' . iconv("TIS-620", "UTF-8", 'Supplier') . '</th>
					<th width="10%">' . iconv("TIS-620", "UTF-8", 'Item') . '</th>
					<th width="20%">' . iconv("TIS-620", "UTF-8", 'Product Name') . '</th>
					<th width="5%">' . iconv("TIS-620", "UTF-8", 'Qty. PO.') . '</th>
					<th width="7%">' . iconv("TIS-620", "UTF-8", 'Arrival Date') . '</th>
					<th width="7%">' . iconv("TIS-620", "UTF-8", 'Receiving Date/Informed') . '</th>
					<th width="5%">' . iconv("TIS-620", "UTF-8", 'Qty. Receive') . '</th>
					<th width="10%">' . iconv("TIS-620", "UTF-8", 'Remark') . '</th>
				</tr>
			</thead>
			<tbody>	';


$page = '';
########## start loop show page ########
$count_row = count($data);


# calculate page number 
# max_line is number of table row in 1 page
//$max_line = 30;
//$totalpage = (int) ($count_row / $max_line);
//if (($count_row % $max_line) != 0) {
//    $totalpage += 1;
//}
$page.=$head_table;
//for ($i = 1; $i <= $totalpage; $i++) {
//    
//    if ($i == 1) {
//        $start = 0;
//        $stop = $max_line - 1;
//    } else {
//        $start = $max_line * ($i - 1);
//        $stop = ($max_line * $i) - 1;
//    }

foreach ($data['report_data'] as $key=>$value) {
//        if($data[$j]->Module == "putaway"){
//            $Actual_Action_Date = $data[$j]->Actual_Action_Date;
//            $Receive_Date_Informed = $data[$j]->Receive_Date_Informed;
//            $Confirm_Qty = $data[$j]->Confirm_Qty;
//            $Remark = $data[$j]->Remark;
//        }else{
//            $Actual_Action_Date = "";
//            $Receive_Date_Informed = "";
//            $Confirm_Qty = "";
//            $Remark = "";
//        }
        if ($j < $count_row) {
            $k = $j + 1;
            $page.='
					<tr>						            		
						<td align="center">' . $value->Estimate_Action_Date . '</td>
						<td>' . $value->Doc_Refer_Ext . '</td>
						<td>' . iconv("TIS-620", "UTF-8", $value->supplier) . '</td>
						<td align="center">' . $value->Product_Code . '</td>
						<td>' . iconv("TIS-620", "UTF-8", $value->Product_NameEN) . '</td>
						<td align="right">' . number_format((int)$value->reserv_quan) . '</td>
						<td align="center">' . @$value->Actual_Action_Date . '</td>
						<td align="center">' . @$value->Pending_Date . '</td>
						<td align="right">' . number_format((int) @$value->Confirm_Qty) . '</td>
						<td>' . iconv("TIS-620", "UTF-8", @$value->Remark) . '</td>
					</tr>
					';
            // Orginal <td>' . iconv("TIS-620", "UTF-8", ($value['0']->Activity_Code == "RECEIVE" ? @$value['remark_data'] : "")) . '</td>            
            //$final_balance=$data[$j]['Balance_Qty'];
        }
    }
    $page.='</tbody>
			</table>';
//    if ($i != $totalpage) {
//        $page.='<pagebreak />';
//    }
    # end of show table 30 rows
//} // close loop page


$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
//		$page = iconv("TIS-620","UTF-8",$page);
$mpdf->WriteHTML($page);

//$mpdf->Output($strSaveName,'I');	// *** file name ***//

$filename = 'GRN-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F'); 
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName); 

//$strSaveName = 'file/GRN-Report-' . date('Ymd') . '-' . generateRandomString(3) . '.pdf'; // Edit By Akkarapol, 28/08/2013, ปรับเพิ่มการสร้างชื่อไฟล์ของ pdf โดยรันเป็น วันที่ และต่อด้วย แรนด้อมสตริง
//$tmp = $mpdf->Output($strSaveName, 'F'); // *** file name ***//
//
//header('Content-Type: application/octet-stream');
//header("Content-Transfer-Encoding: Binary");
//header("Content-disposition: attachment; filename=\"" . $strSaveName . "\"");
//readfile($strSaveName);
?>