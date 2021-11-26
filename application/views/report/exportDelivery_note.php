<?php

$page ='';
date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
// $mpdf = new mPDF('UTF-8');
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 25, 23, 5, 5);
// p($datas);exit;


$first_page = 0;
foreach ( $datas as $idx => $va ):
 
    $page ='';
    $footer ='';

$Doc_Int = $va[0]->Doc_Refer_Int;
$header = '
		<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
                    <tr>
                        <td height="30" width="300">
                            <img style="width:auto;height:0.8cm;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                        </td>
                        <td style="text-align: center;font-weight: bold;">' . iconv("TIS-620", "UTF-8", _lang('dn_report')) . '</td>
                        <td style="text-align: right; padding-right: 40px;" width="300"><b>DN</b> :'.($Doc_Int).'</td>
                    </tr>
        </table>';

       
        if ($showfooter == 'show'):
       
            $footer .= '
                        <div style="width:100%;font-size:12px; font-family: arial;margin:0 0 10px 0;">
                            <div style="width: 100%;float:right; padding:10px;"></div>
                                <div style="width: 100%;float:right; padding:10px;"></div>

                                    <div style="width: 20%;float:right; padding:2px;">
                                        <div>Driver:..................................</div>
                                        
                                        <div>........./..................../..............</div> 
                                    </div>

                                    <div style="width: 20%;float:right; padding:2px;">
                                        <div>COA:.........................................</div>
                                        
                                        <div>........./..................../.....................</div> 
                                    </div>

                                    <div style="width: 20%;float:right; padding:2px;">
                                        <div>Dispatch Checker:.......................</div>

                                        <div>........./..................../..................</div> 
                                    </div>
                        </div>
                        ';
        endif;

// exit;
$settings ['container'] = 1;
$footer .= '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
					<tr>
						<td width="50%" align="left">Print By : ' . tis620_to_utf8($printBy) . '  , Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
						<td align="right">WMS:' . $revision . ' , {PAGENO}/{nb}</td>
					</tr>
					</table>';


$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header,'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer,'E');
// p($footer);exit;

if($first_page != 0){
    $mpdf->AddPage();
}

$head_table = '
<style>
    ' . $set_css_for_show_column . '
    .tborder{
            border-top:1px solid #333333;
            border-right:1px solid #333333;
    }

    .tborder td,
    .tborder th{
            border-left:1px solid #333333;
            border-bottom:1px solid #333333;
            font-size:12px;
            font-family : garuda;
    }
</style>

<table width="100%" cellspacing="0" style="font-family: arial;font-size:12px; border-right: 0;" class="tborder">
    <thead>
        <tr style="border:1px solid #333333;" bgcolor="#B0C4DE">
            <div class="ddr_date">      <th class="ddr_date" width="10%"  height="35">' . iconv("TIS-620", "UTF-8", _lang('ddr_date')) . '</th></div>
            <div class="document_no">   <th class="document_no" width="10%">' . iconv("TIS-620", "UTF-8", _lang('document_no_out')) . '</th></div>
            <div class="product_code">  <th class="product_code" width="10%">' . iconv("TIS-620", "UTF-8", _lang('product_code')) . '</th></div>
            <div class="product_name">  <th class="product_name" width="25%">' . iconv("TIS-620", "UTF-8", _lang('product_name')) . '</th></div>
            <div class="invoice">  <th class="invoice" width="10%">' . iconv("TIS-620", "UTF-8", _lang('Lot No')) . '</th></div>
            <div class="reserve_qty">   <th class="reserve_qty" width="10%">' . _lang('reserve_qty') . '</th></div>
            <div class="dispatch_qty">  <th class="dispatch_qty" width="10%">' . _lang('dispatch_qty') . '</th></div>
            <div class="pallet_code">  <th class="pallet_code_out" width="10%">' . _lang('UOM') . '</th></div>
            <div class="remark">        <th class="remark" width="5%">' . _lang('remark') . '</th></div>
        </tr>
    </thead>
    <tbody>';


   
    $page.=$head_table;

    if(!empty($va)){
    //    p($va);exit;
        $sumReceiveQty = 0;
        $sumDispatchQty = 0;
        $sumPrice_Per_Unit = 0;
        $sumAllPrice = 0;
        $sumRec = 0;
        $temp = "";

       
        foreach ($va as $j => $value) {
           
            // foreach ($valuee as $j => $value):
                //  p($value);exit;
            if ($value->Doc_Refer_Ext != $temp && $temp != "") {
                    $page .= '<tr  bgcolor="#F0F0F0">
                    <div><td colspan="5" align="center"><b>Sub Total</b></td></div>
                    <div class="reserve_qty"><td align="right" class="reserve_qty"><b>' . set_number_format($sumRec) . '</b></td></div>
                    <div class="dispatch_qty"><td class="dispatch_qty" align="right"><b></b></td></div>
                    <div><td style="border-right: 1px solid #666666;" colspan="2" ></td></div></tr>';
            $sumRec = 0;
            }
    
            $page .= '
                <tr >
                    <div class="ddr_date"><td class="ddr_date" height="25"  valign="middle" align="center">' . $value->Estimate_Action_Date . '</td></div>
                    <div class="document_no"><td class="document_no" valign="middle" align="left">' . $value->Doc_Refer_Ext . '</td></div>
                    <div class="product_code"><td  class="product_code" valign="middle" align="left">' . $value->Product_Code . '</td></div>
                    <div class="product_name"><td  class="product_name" valign="middle" align="left">' . iconv("TIS-620", "UTF-8", $value->Product_NameEN) .'</td></div>
                    <div class="container"><td class="container" valign="middle" align="left">' . iconv("TIS-620", "UTF-8", $value->Product_Lot) . '</td></div>
                    <div class="reserve_qty"><td class="reserve_qty" valign="middle" align="right">' . set_number_format($value->Reserv_Qty) . '</td></div>
                    <div class="dispatch_qty"><td class="dispatch_qty" valign="middle" align="right"></td></div>
                    <div class="pallet_code"><td class="pallet_code" valign="middle" align="center">' . $value->Unit . '</td></div>
                    <div class="remark"><td  style="border-right: 1px solid #666666;" class="remark" valign="middle" align="left">' . iconv("TIS-620", "UTF-8", $value->Remark) . '&nbsp;</td></div>
                </tr>
            ';

            $temp = $value->Doc_Refer_Ext;

            $sumRec += $value->Reserv_Qty;
                $sumReceiveQty += @$value->Reserv_Qty; // Add $sumReceiveQty for calculate total qty : ADD BY POR 2015-01-14
                $sumDispatchQty += @$value->Confirm_Qty; // Add $sumDispatchQty for calculate total qty : ADD BY POR 2015-01-14
                $sumPrice_Per_Unit += @$value->Price_Per_Unit;
                $sumAllPrice += @$value->All_Price;

            // endforeach; 
        }

       
    
    $page .= '<tr  bgcolor="#F0F0F0">
    <div><td colspan="5" align="center"><b>Sub Total</b></td></div>
    <div class="reserve_qty"><td align="right" class="reserve_qty"><b>' . set_number_format($sumRec) . '</b></td></div>
    <div class="dispatch_qty"><td class="dispatch_qty" align="right"><b></b></td></div>
    <div><td style="border-right: 1px solid #666666;" colspan="2" ></td></div></tr>';


    $last_colspan = $colspan [count($colspan)];
    $page .= '<tr  bgcolor="#F0F0F0">
        <div><td colspan="5" align="center"><b>Total</b></td></div>
        <div class="reserve_qty"><td align="right" class="reserve_qty"><b>' . set_number_format($sumReceiveQty) . '</b></td></div>
        <div class="dispatch_qty"><td class="dispatch_qty" align="right"><b></b></td></div>
        <div><td style="border-right: 1px solid #666666;" colspan="2" ></td></div></tr>
        ';
    }
    $page .= '</tbody></table>';

    $mpdf->WriteHTML($stylesheet, 1);
    $mpdf->WriteHTML($page);
    $first_page++;
    
    $mpdf->SetHTMLFooter($footer);
    $mpdf->SetHTMLFooter($footer,'E');
    // p($footer);exit;
endforeach;

    $this->load->helper('file');
    $stylesheet = read_file('../libraries/pdf/style.css');
    $filename = 'DN-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
    $strSaveName = $settings ['uploads'] ['upload_path'] . $filename;
    $tmp = $mpdf->Output($strSaveName, 'F');
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");
    readfile($strSaveName);

?> 
   
    