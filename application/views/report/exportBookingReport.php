<?php
date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$margin_foot = ($showfooter == 'show') ? 33 : 25;
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 30, $margin_foot, 5, 5);
$xml = $this->config->item("_xml");

$from_date = convertDateString($from_date);

if (!empty($to_date)):
    $to_date = convertDateString($to_date);
endif;

$header = '<table width="100%" style="vertical-align: bottom; font-family: garuda; font-size: 16px; color: #000000;">
    <tr>
        <td height="30" width="200">
            <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
        </td>
        <td style="text-align: center;font-weight: bold;">'.iconv("TIS-620","UTF-8",'Aging Report').' '.date("M Y").'</td>
        <td height="30" class="label_text">' . _lang('renter') . ' : </td>  
        <td height="30" class="description_text">' . iconv("TIS-620", "UTF-8", $renter_name) . '</td>
        <td height="30" class="label_text pull-right">' .$doc_type . ' : </td>  
        <td class="description_text">' . $doc_value . '</td>
    </tr>
    </table>';

if ($showfooter == 'show'):
    $space = nbs(10);
    $for_find = array(
        '{space}'
    );

    $for_replace = array(
        $space
    );

    $for_fine_prepared = array(
        '{prepared_by}'
    );
    $for_replace_prepared = array(
        $Prepared_by
    );

    $footer .= "
        <div style='width:100%; margin:0 0 10px 0;'>
    ";

    foreach ($signature_report as $key_sign => $sign):
        $footer .= "
            <div style='width: 23%;float:left; padding:10px;'>
            ";

        foreach ($sign as $key_s => $s):
            $s = str_replace($for_find, $for_replace, $s);
            $s = str_replace($for_fine_prepared, $for_replace_prepared, $s); 

            $footer .= "
                <div style='padding:2px;'>{$s}</div>
            ";
        endforeach;

        $footer .= "
            </div>
        ";
    endforeach;

    $footer .= "
        </div>
    ";
endif;
//END IF

$footer .= '<table width="100%" style="vertical-align: bottom; color: #666666;">
<tr>
<td width="50%" align="left">
        Print By : ' . iconv("TIS-620", "UTF-8", $printBy) . '  ,
        Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
        <td align="right">WMSP:' . $revision . ' , {PAGENO}/{nb}</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');

$head_table = '
<style>
    ' . $set_css_for_show_column . '
    .tborder{
            /*border:1px solid #333333;*/
            border-top:1px solid #333333;
            border-right:1px solid #333333;

    }
</style>
<table  cellspacing="0" class="tborder">
    <thead>
        <tr class="table-header">
            <div class="">  <th class=" text-size-8" >Row</th></div>
            <div class="">  <th class=" text-size-8" >Product Code</th></div>
            <div class="">  <th class=" text-size-8" >Product Name</th></div>
            <div class="">  <th class=" text-size-8" >Product Status</th></div>
            <div class="">  <th class=" text-size-8" >Product subStatus</th></div>
            <div class="">  <th class=" text-size-8" >Receive Date</th></div>
			      <div class="">  <th class=" text-size-8" >Customs Entry</th></div>
            <div class="">  <th class=" text-size-8" >Customs Sequence</th></div>
            <div class="">  <th class=" text-size-8" >HS Code</th></div>
            <div class="">  <th class=" text-size-8" >Product Lot</th></div>
            <div class="">  <th class=" text-size-8" >Product Serial</th></div>
            <div class="">  <th class=" text-size-8" >Invoice No</th></div>
            <div class="">  <th class=" text-size-8" >PD Reserv Qty</th></div>
            <div class="">  <th class=" text-size-8" >Unit</th></div>
	    <div class="">  <th class=" text-size-8" >Price</th></div>
            <div class="">  <th class=" text-size-8" >Pallet Code</th></div>
            <div class="">  <th class=" text-size-8" style="border-right: 1px solid #666666;">remark</th></div>
        </tr>
    </thead>
    <tbody>';

$page = '';
$page.=$head_table;

if (!empty($datas)) {
    $i = 1;
	
    foreach ($datas as $keyRow => $data) {

        $iTemp = 1;
       
        foreach ($data as $key => $value) { 
            
            $page.='
                <tr>
                        <div class="row"><td  class="row" style="font-size: 9pt;"  valign="top" align="center">' . $i . '</td></div>
                        <div class="product_code"><td  class="product_code" style="font-size: 9pt;"  valign="top" align="center">' . $value['Product_Code'] . '</td></div>
                        <div class="product_nameEN"><td  class="product_nameEN" style="font-size: 9pt;"  valign="top" align="center">' . $value['Product_NameEN'] . '</td></div>
                        <div class="product_status"><td  class="product_status" style="font-size: 9pt;"  valign="top" align="center">' . $value['Product_Status'] . '</td></div>
                        <div class="product_sub_status"><td  class="product_sub_status" style="font-size: 9pt;"  valign="top" align="center">' . $value['Product_Sub_Status'] . '</td></div>
                        <div class="receive_date"><td  class="receive_date" style="font-size: 9pt;"  valign="top" align="center">' . $value['Receive_Date'] . '</td></div>
						<div class="customs_sequence"><td  class="customs_sequence" style="font-size: 9pt;"  valign="top" align="center">' . $value['Customs_Entry'] . '</td></div>
                        <div class="customs_sequence"><td  class="customs_sequence" style="font-size: 9pt;"  valign="top" align="center">' . $value['Customs_Sequence'] . '</td></div>
                        <div class="hs_code"><td  class="hs_code" style="font-size: 9pt;"  valign="top" align="center">' . $value['HS_Code'] . '</td></div>
                        <div class="product_lot"><td  class="product_lot" style="font-size: 9pt;"  valign="top" align="center">' . $value['Product_Lot'] . '</td></div>
                        <div class="product_serial"><td  class="product_serial" style="font-size: 9pt;"  valign="top" align="center">' . $value['Product_Serial'] . '</td></div>
                        <div class="invoice_no"><td  class="invoice_no" style="font-size: 9pt;"  valign="top" align="center">' . $value['Invoice_No'] . '</td></div>
                        <div class="pd_reserv_qty"><td  class="pd_reserv_qty" style="font-size: 9pt;"  valign="top" align="center">' . $value['PD_Reserv_Qty'] . '</td></div>
                        <div class="unit"><td  class="unit" style="font-size: 9pt;"  valign="top" align="center">' . $value['name'] . '</td></div>
			<div class="price"><td  class="price" style="font-size: 9pt;"  valign="top" align="center">' . $value['PD_Reserv_Qty'] * $value['Price_Per_Unit']  . '</td></div>
                        <div class="pallet_code"><td  class="pallet_code" style="font-size: 9pt;"  valign="top" align="center">' . $value['Pallet_Code'] . '</td></div>
                        <div class="remark"><td  style="border-right: 1px solid #666666;" class="remark" style="font-size: 9pt;"  valign="top" align="right">' .  $value['remark'] . '&nbsp;</td></div>
                </tr>';
            $i++;
            $iTemp++;
        }
    }
}


$page.='</tbody>'
        . '</table>';

$this->load->helper('file');
$stylesheet = file_get_contents(APPPATH . '/libraries/mpdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($page); 
$mpdf->Output($strSaveName, 'I');
