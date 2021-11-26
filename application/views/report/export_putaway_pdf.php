<?php
date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$mpdf = new mPDF('UTF-8');
$mpdf = new mPDF('th', 'A4-L', '11', '', 10, 10, 50, 33, 10, 10);
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)

$barcode_document_ext = '';
if(@$settings['show_barcode']['putaway_job']['document_ext']):
    $barcode_document_ext = "<barcode code='{$data->Doc_Refer_Ext}' type='C128A' size='1' height='1.0' />";
endif;

$header = '
<table border="0" width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
                    <tr>			
                        <td height="30" width="450">
                            <img style="width:150px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                        </td>
                        <td align="center">
                            <font style="font-size:16px;"><b>'.$text_header.'</b></font>
                        </td>
                        <td height="30" width="450" align="right">
                            '.$barcode_document_ext.'
                        </td>
                    </tr>                    
		</table>
                
                <table width="700" align="center" border="0" style="font-size:14px; font-family: arial;">
                
                    <tr>
                        <td width="100">
                            Renter
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="320">
                            ' . $data->Renter_Name . '
                        </td>
                        <td width="130">
                            Shipper
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="300">
                            ' . $data->Source_Name . '
                        </td>
                        <td width="120">
                            Consignee
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="300">
                            ' . $data->Consignee_Name . '
                        </td>
                    </tr> 
                    
                    <tr>
                        <td>
                            Document No.
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Document_No . '
                        </td>
                        <td>
                            Document External
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Doc_Refer_Ext . ' 
                        </td>
                        <td>
                            Document Internal
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Doc_Refer_Int . '
                        </td>
                    </tr>   
                    
                    <tr>
                        <td>
                            Invoice No.
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Doc_Refer_Inv . '
                        </td>
                        <td>
                            Customs Entry
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Doc_Refer_CE . '
                        </td>
                        <td>
                            BL No.
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Doc_Refer_BL . '
                        </td>
                    </tr>  
                    
                    <tr>
                        <td>
                            Receive Type
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Dispatch_Type . '
                        </td>
                        <td>
                            ASN.
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Est_Action_Date . '
                        </td>
                        <td>
                            Receive Date.
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Action_Date . '
                        </td>
                    </tr>
                    
                    <tr>
                        <td>
                            Vender
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Vendor_Name . '
                        </td>
                        <td>
                            Driver Name
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Vendor_Driver_Name . '
                        </td>
                        <td>
                            Car No.
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Vendor_Car_No . '
                        </td>
                    </tr>
                    
                    <tr>
                        <td>
                            
                        </td>
                        <td>
                            
                        </td>
                        <td>
                            <input type="checkbox" ' . ($data->Is_Pending=='Y'?'checked="checked"':'') . '>Pending
                                &nbsp;&nbsp;
                            <input type="checkbox" ' . ($data->Is_Repackage=='Y'?'checked="checked"':'') . '>Repackage
                        </td>
                        <td>
                            Remark
                        </td>
                        <td>
                            :
                        </td>
                        <td colspan="4">
                            ' . $data->Remark . '
                        </td>
                    </tr>   
                    
                </table>
				
		';

//ADD BY POR 2013-12-18 แสดงส่วนท้าย(เซ็นชื่อ) ตอนกด Approve Receive
if($showfooter=='show'):
    $footer .= '
    <div style="width:100%;font-size:12px; font-family: arial;">
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

		$header = iconv("TIS-620","UTF-8",$header);
//		$footer = iconv("TIS-620","UTF-8",$footer);
$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');

# head_table is header of table
$head_table = '<style>
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
                            font-size:12px;
                            padding:5px;
                            font-family:arial;
                    }
                    '.$set_css_for_show_column.'
		  </style>
                  <table width="100%" cellspacing="0" class="tborder"><thead><tr>';
foreach ($column as $keyH => $h) {
    $cssBorder = '';
    if ($keyH == 0):
//        $cssBorder = 'border-left:1px solid black;border-radius-corner: 5px;';
        $cssBorder = 'border-left:1px solid black;';
    elseif($keyH == count($column)-1):
        $cssBorder = 'border-right:1px solid black;border-radius-corner: 5px;';
    endif;
    $head_table.='<div class='.$keyH.'><th style="border-bottom:1px solid black;border-top:1px solid black; border-left:1px solid black; ' . $cssBorder . '">' . $h . '</th></div>';
}

$head_table.='</tr></thead>';

$page = '';

$page .= $head_table;

$page .= '<tbody>';


    $sum_reserve_qty = 0;
    $sum_confirm_qty = 0;
    $sumPriceUnit    = 0;
    $sumPrice        = 0;

//SET COLSPAN:ADD BY POR 2014-09-05
$all_column+=1;
$colspan1 = $colspan[1]+1;
//END SET COLSPAN

foreach($order_detail as $key_data_value => $data_value):
       
            $barcode_product_code = '';
            if(@$settings['show_barcode']['putaway_job']['product_code']):
                $barcode_product_code = "<BR/><barcode code='{$data_value['product_code']}' type='C128A' size='0.8' height='1' />";
            endif;
            $barcode_suggest_location = '';
            if(@$settings['show_barcode']['putaway_job']['suggest_location']):
                $barcode_suggest_location = "<BR/><barcode code='{$data_value['suggest_location']}' type='C128A' size='0.8' height='1' />";
            endif;

            $page .= '<tr>';
    
            $k = $key_data_value + 1;
            $bg = '';
            if ($data_value['to_location'] != $data_value['act_location']) {
                $bg = 'style="color:#FF0000"';
            }
//            $cssStyle = 'style="font-size:12px;border-bottom: 1px solid black;"';
            
            $sum_reserve_qty = $sum_reserve_qty + $data_value['reserv_qty'];
            $sum_confirm_qty = $sum_confirm_qty + $data_value['confirm_qty'];
            $sumPriceUnit = $sumPriceUnit + $data_value['price_per_unit'];
            $sumPrice = $sumPrice + $data_value['all_price'];
            
            
            $page.='		
                    <div class="no"><td align="center" width="50">' . $k . '</td></div>
                    <div class="product_code"><td align="center" width="90">' . $data_value['product_code'] . $barcode_product_code . '</td></div>
                    <div class="product_name"><td align="left" width="150">' . iconv("TIS-620", "UTF-8", $data_value['product_name']) . '</td></div>
                    <div class="product_status"><td align="left" width="90">' . $data_value['product_status'] . '</td></div>
                    <div class="product_sub_status"><td align="left" width="90">' . $data_value['product_sub_status'] . '</td></div>
                    <div class="lot"><td align="center" width="50">' . iconv("TIS-620", "UTF-8", $data_value['product_lot']) . '</td></div>
                    <div class="serial"><td align="center" width="50">' . iconv("TIS-620", "UTF-8", $data_value['product_serial']) . '</td></div>
                    <div class="mfd"><td align="center" width="50">' . $data_value['product_mfd'] . '</td></div>
                    <div class="exp"><td align="center" width="50">' . $data_value['product_exp'] . '</td></div>';
            
            
            if($conf_inv):
                $page.='
                    <div class="invoice"><td align="left" width="90">' . iconv("TIS-620", "UTF-8", $data_value['invoice_no']) . '</td></div>';  
            endif;
            
            
            if($conf_cont):
                $page.='
                    <div class="container"><td align="left" width="90">' . iconv("TIS-620", "UTF-8", $data_value['cont_no']) . '</td></div>';
            endif;
            
            
            $page.='<div class="reserve_qty"><td align="right" width="60">' . set_number_format($data_value['reserv_qty']) . '</td></div>
                    <div class="confirm_qty"><td align="right" width="60">' . set_number_format($data_value['confirm_qty']) . '</td></div>
                    <div class="unit"><td align="center" width="60">' . tis620_to_utf8($data_value['unit']) . '</td></div>
                    ';
            
           #ADD BY POR 2014-01-14 เพิ่ม price per unit
            if($statusprice):
                    $page.='
                    <div class="price_per_unit"><td align="right" width="60">' . set_number_format($data_value['price_per_unit']) . '</td></div>
                    <div class="unit_price"><td align="center" width="60">' . $data_value['unit_price_value'] . '</td></div>
                    <div class="all_price"><td align="right" width="60">' . set_number_format($data_value['all_price']) . '</td></div>
                    ';
            endif;

                $page.='
                    <div class="suggest_location"><td align="center" width="90">' . $data_value['suggest_location'] . $barcode_suggest_location . '</td></div>
                    <div class="actual_location"><td align="center" width="120">' . $data_value['actual_location'] . '</td></div>
                     ';

            #ADD FOR ISSUE 3323: BY KIK 2014-01-30 เพิ่มการแสดงผล build pallet
            
            if($conf_pallet):
                    $page.='
                    <div class="pallet_code"><td align="center" width="60">' . $data_value['pallet_Code'] . '</td></div>';
            endif;

                $page.='
                    <div class="pick_by"><td align="center" width="90">' . iconv("TIS-620", "UTF-8", $data_value['pick_by']) . '</td></div>
                    <div class="remark"><td align="center">' . iconv("TIS-620", "UTF-8", $data_value['remark']) . '</td></div>
                   
                ';
                
                $page .= '</tr>';
    
    endforeach;
    
    
$page .= '</tbody>';


$page .= '<tfooter>';

$page.='            
        <tr>
            <div><td align="center" colspan="'.$colspan[1].'"> <b>Total</b> </td></div>
            <div class="reserve_qty"><td align="right">' . set_number_format($sum_reserve_qty) . '</td></div>
            <div class="confirm_qty"><td align="right" class="confirm_qty">' . set_number_format($sum_confirm_qty) . '</td></div>
            
       ';

            
    #ADD BY POR 2014-01-14 เพิ่ม price per unit
    if($statusprice == TRUE):
        $page.='
                <div class="price_per_unit"><td align="right">' . set_number_format($sumPriceUnit) . '</td></div>
                <div class="unit_price"><td align="center"> </td></div>
                <div class="all_price"><td align="right">' . set_number_format($sumPrice) . '</td></div>
               ';
    endif;
                
                
    $page.='
                <td align="center" colspan="'.@$colspan[count(@$colspan)].'"> </td>
            </tr>';

$page.='</tfooter>';


$page .= '</table>';
//echo $page;exit();

$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
//		$page = iconv("TIS-620","UTF-8",$page);

$mpdf->WriteHTML($page);

//$mpdf->Output($strSaveName, 'I'); // *** file name


$filename = 'Putaway-Job-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F'); 
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName); 

?>
