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
		  <table width="100%" cellspacing="0" border-style="1px solid #666666;font-size:10px;" class="tborder">
			<thead>
				<tr style="border:1px solid #333333;" bgcolor="#B0C4DE">
					<th width="7%" height="35"> ASN DATE</th>
                    <th width="7%">' . iconv("TIS-620", "UTF-8", 'PO.NO') . '</th>
                    <th width="7%">' . iconv("TIS-620", "UTF-8", 'Document No') . '</th>
                    <th width="7%">' . iconv("TIS-620", "UTF-8", 'Document Refer Int') . '</th>
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

$page.=$head_table;
        $sumReservQty_all = 0;     
        $sumReceiveQty_all = 0;    
        $sumReservQty_not_reject = 0;     
        $sumReceiveQty_not_reject = 0;    
        $sumReservQty_reject = 0;     
        $sumReceiveQty_reject = 0;
        $have_reject = FALSE;
    foreach ($data as $key=>$value) {
        // p($value);exit;
        if ($j < $count_row) {
            $k = $j + 1;
            
            
            $set_tr_reject="";
            
            $page.='
					<tr>						            		
						<td align="center">' . $value['0']->Estimate_Action_Date . '</td>
                        <td>' . $value['0']->Doc_Refer_Ext . '</td>
                        <td>' . $value['0']->Document_No . '</td>
                        <td>' . $value['0']->Doc_Refer_Int . '</td>
						<td>' . iconv("TIS-620", "UTF-8", $value['0']->supplier) . '</td>
						<td align="center">' . $value['0']->Product_Code . '</td>
						<td>' . iconv("TIS-620", "UTF-8", $value['0']->Product_NameEN) . '</td>
						<td align="right">' . set_number_format($value['reserv_quan']) . '</td>
						<td align="center">' . @$value['0']->Actual_Action_Date . '</td>
						<td align="center">' . @$value['0']->Pending_Date . '</td>
						<td align="right">' . set_number_format(@$value['receive_quantity']) . '</td>
						<td>' . iconv("TIS-620", "UTF-8", @$value['remark_data']) . '</td>
					</tr>
					';
            if($value[0]->Is_reject == 'Y'):
                
                $set_tr_reject = 'style = "color:red;"';
                $have_reject = TRUE;
                
//            p($value);exit();
                $remark = (@$value['remark_data'] == "<br/>" || @$value['remark_data'] == " " || @$value['remark_data'] == "" || empty($value['remark_data']))?"Reject":@$value['remark_data'];
                $page.='
					<tr>						            		
						<td '.$set_tr_reject.' align="center">' . $value['0']->Estimate_Action_Date . '</td>
                        <td '.$set_tr_reject.'>  ' . $value['0']->Doc_Refer_Ext . '</td>
                        <td '.$set_tr_reject.'>  ' . $value['0']->Document_No . '</td>
                        <td '.$set_tr_reject.'>  ' . $value['0']->Doc_Refer_Int . '</td>
						<td '.$set_tr_reject.'> ' . iconv("TIS-620", "UTF-8", $value['0']->supplier) . '</td>
						<td '.$set_tr_reject.'  align="center">' . $value['0']->Product_Code . '</td>
						<td '.$set_tr_reject.'> ' . iconv("TIS-620", "UTF-8", $value['0']->Product_NameEN) . '</td>
						<td '.$set_tr_reject.' align="right">' . set_number_format(-$value['reserv_quan']) . '</td>
						<td '.$set_tr_reject.'  align="center">' . @$value['0']->Actual_Action_Date . '</td>
						<td '.$set_tr_reject.' align="center">' . @$value['0']->Pending_Date . '</td>
						<td '.$set_tr_reject.' align="right">' . set_number_format(-$value['receive_quantity']) . '</td>
						<td '.$set_tr_reject.' >' . iconv("TIS-620", "UTF-8",$remark ) . '</td>
					</tr>
					';
             
            
                $sumReservQty_reject+=@($value['reserv_quan']);
                $sumReceiveQty_reject+=@($value['receive_quantity']);
                
            else:
                
            $sumReservQty_not_reject+=@$value['reserv_quan'];   
            $sumReceiveQty_not_reject+=@$value['receive_quantity'];
            endif;
            
            
            $sumReservQty_all+=@$value['reserv_quan'];  
            $sumReceiveQty_all+=@$value['receive_quantity'];
            
        }
    }
    
    $text_total_reject = '';
    if($have_reject):
     $text_total_reject = '(Exclude Reject)';
    endif;
    
    $page.='<tr  bgcolor="#F0F0F0">
                <td colspan="7" align="center"><b>Total '.$text_total_reject.' </b></td>
                <td align="right"><b>'.set_number_format($sumReservQty_not_reject).'</b></td>
                <td colspan="2" align="center"></td>
                <td align="right"><b>'.set_number_format($sumReceiveQty_not_reject).'</b></td>
                <td></td>
            </tr>';
    //<!--Reject Area show Total - ในส่วนนี้จะแสดงผลเฉพาะ SKU ที่มีรายการ reject เกิดขึ้นเท่านั้น-->
    if($have_reject):
    $page.='<tr  bgcolor="#DEDEDE">
                <td colspan="7" align="center"><b>Reject</b></td>
                <td align="right"><b>'.set_number_format($sumReservQty_reject).'</b></td>
                <td colspan="2" align="center"></td>
                <td align="right"><b>'.set_number_format($sumReceiveQty_reject).'</b></td>
                <td></td>
            </tr>';
    $page.='<tr  bgcolor="#C9C9C9">
                <td colspan="7" align="center"><b>All Total</b>
                </td><td align="right"><b>'.set_number_format($sumReservQty_all).'</b></td>
                <td colspan="2" align="center"></td>
                <td align="right"><b>'.set_number_format($sumReceiveQty_all).'</b></td>        
                <td></td>
            </tr>';
    endif;
    //<!-- end Reject Area show Total - ในส่วนนี้จะแสดงผลเฉพาะ SKU ที่มีรายการ reject เกิดขึ้นเท่านั้น--> 
    
    
    $page.='</tbody>
			</table>';

//echo $page;exit();
$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
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