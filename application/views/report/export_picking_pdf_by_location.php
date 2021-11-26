<?php
date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$mpdf = new mPDF('UTF-8');
$margin_top = (@$settings['show_barcode']['picking_job']['document_ext']?50:45);
$margin_bottom = (@$showfooter=='show'&&!empty($signature_report)?33:15);
$mpdf = new mPDF('th', 'A4-L', '11', '', 7, 7, $margin_top, $margin_bottom, 7, 7);
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)

$barcode_document_ext = '';
if(@$settings['show_barcode']['picking_job']['document_ext']):
    $barcode_document_ext = "<BR/><barcode code='{$data->Doc_Refer_Ext}' type='C128A' size='1' height='1.0' />";
endif;

$header = '
<table border="0" width="100%" style="vertical-align: bottom; font-family: arial; color: #000000;">
                    <tr>
                        <td height="30" width="300">
                            <img style="width:auto;height:0.8cm;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                        </td>
                        <td align="center">
                            <font style="font-size:16px;"><b>'.$text_header.'</b></font>
                        </td>
                        <td height="30" width="300" align="right">
                            '.$barcode_document_ext.'
                        </td>
                    </tr>
		</table>

                <table width="700" align="center" border="0" style="font-size:14px; font-family: arial;">

                    <tr>
                        <td width="100">
                            '._lang('renter').'
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="320">
                            ' . $data->Renter_Name . '
                        </td>
                        <td width="130">
                            '._lang('shipper').'
                        </td>
                        <td width="10">
                            :
                        </td>
                        <td width="300">
                            ' . $data->Source_Name . '
                        </td>
                        <td width="120">
                            '._lang('consignee').'
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
                            '._lang('document_no').'
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Document_No . '
                        </td>
                        <td>
                            '._lang('document_external').'
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Doc_Refer_Ext . '
                        </td>
                        <td>
                            '._lang('document_internal').'
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
                            '._lang('doc_refer_inv').'
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Doc_Refer_Inv . '
                        </td>
                        <td>
                            '._lang('doc_refer_ce').'
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Doc_Refer_CE . '
                        </td>
                        <td>
                            '._lang('doc_refer_bl').'
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
                            '._lang('dispatch_type').'
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Dispatch_Type . '
                        </td>
                        <td>
                            '._lang('est_dispatch_date').'
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            ' . $data->Est_Action_Date . '
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
                            '._lang('remark').'
                        </td>
                        <td>
                            :
                        </td>
                        <td colspan="7">
                            ' . $data->Remark . '
                        </td>
                    </tr>

                </table>

		';
//p($signature_report);
//exit();
//ADD BY POR 2013-12-18 แสดงส่วนท้าย(เซ็นชื่อ) ตอนกด Approve Receive
if($showfooter=='show'):

    $space = nbs(10);
    $for_find = array(
        '{space}'
    );
    $for_replace = array(
        $space
    );

    $footer .= "
        <div style='width:100%;font-size:12px; font-family: arial;margin:0 0 10px 0;'>
    ";

    foreach($signature_report as $key_sign => $sign):
        $footer .= "
            <div style='width: 23%;float:left; padding:10px;'>
            ";

        foreach($sign as $key_s => $s):
            $s = str_replace($for_find, $for_replace, $s);
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

$head_table = "<style>
{$set_css_for_show_column}
    td,th {
        font-family: garuda;
    }
    .tborder{
        border-top:1px solid #333333;
        border-right:1px solid #333333;
    }

    .tborder td,
    .tborder th{
        border-left:1px solid #333333;
        border-bottom:1px solid #333333;
        font-size:10px;
        padding:3px;
        font-family: garuda;
    }
    td.line {
        border-bottom: 3px solid #333333;
    }
</style>
                    <table border=1 width='100%' cellspacing='0' class='tborder'><thead><tr>";
$set_index_column = 0;
$last_of_column = count($column)-1;

foreach ($column as $keyH => $h) {
    $cssBorder = '';
    if ($set_index_column == $last_of_column):
        $cssBorder = 'border-right:1px solid black;border-radius-corner: 5px;';
    else:
        $cssBorder = 'border-left:1px solid black;';
    endif;

    $set_index_column += 1;
    $head_table.='<div class="'.$keyH.'" ><th style="border-bottom:1px solid black;border-top:1px solid black; border-left:1px solid black; ' . $cssBorder . '">' . $h . '</th></div>';
}
//exit();
$head_table.='</tr></thead>';
//echo $head_table;exit();
$page = '';


$page .= $head_table;


$page .= '<tbody>';


//$cssStyle = 'style="font-size:12px;border-bottom: 1px solid black;border-left: 1px solid black;"';
$cssStyleLast = 'style="font-size:12px;border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black;"';

$sum_reserve_qty = set_number_format(0);
$sum_confirm_qty = set_number_format(0);
$sum_uom_qty     = set_number_format(0);
$sumPriceUnit    = set_number_format(0);
$sumPrice        = set_number_format(0);

$last_product_and_status = '';
$set_data_for_show_total_by_item = array();

	$idx_key = 1;

	$sum_reserve_qty = 0;

	foreach ($order_detail as $k => $v) {

		$barcode_suggest_location = '';
                if(@$settings['show_barcode']['picking_job']['suggest_location']):
                    $barcode_suggest_location = " <barcode code='{$k}' type='C128A' size='0.8' height='1' />";
                endif;

		$page .= '<tr><td colspan="'.($colspan_total + $colspan_blank + 4).'" style="font-size:3em ;border-right:1px solid #333333;">  ' . $barcode_suggest_location . $k . '</td></tr>';

		$reserv_total = 0;
		$confirm_total = 0;
                $uom_total = 0;

		foreach ($v as $k2 => $v2) {

			$reserv_total += $v2['reserv_qty'];
			$confirm_total += $v2['confirm_qty'];
                        $uom_total += $v2['uom_qty'];

			// ADD BY POR 2014-03-26 step confirm not show 0 but show null (approve show 0) - Re-Add By Ball
			if($Process_Id==2):
				$confirm_qty =!empty($v2['confirm_qty'])?set_number_format($v2['confirm_qty']):'';
			else:
				$confirm_qty = set_number_format($v2['confirm_qty']);
			endif;
			//END ADD

                        $barcode_product_code = '';
                        if(@$settings['show_barcode']['picking_job']['product_code']):
                            $barcode_product_code = "<barcode code='{$v2['product_code']}' type='C128A' size='0.8' height='1' /><BR/>";
                        endif;
                        $barcode_product_lot = '';
                        if(@$settings['show_barcode']['picking_job']['lot']):
                            $lot = iconv("TIS-620", "UTF-8", $v2['product_lot']);
                            if($lot == '' || $lot == ' '):
                                $v2['product_lot'] = $lot = '%%ENTERKEY%%';
                            endif;
                            $barcode_product_lot = "<barcode code='{$lot}' type='C128A' size='0.8' height='1' /><BR/>";
                        endif;
                        $barcode_product_serial = '';
                        if(@$settings['show_barcode']['picking_job']['serial']):
                            $serial = iconv("TIS-620", "UTF-8", $v2['product_serial']);
                            if($serial == '' || $serial == ' '):
                                $v2['product_serial'] = $serial = '%%ENTERKEY%%';
                            endif;
                            $barcode_product_serial = "<barcode code='{$serial}' type='C128A' size='0.8' height='1' /><BR/>";
                        endif;

			$page.='<tr>
                                    <div class="no"><td class="no" align="center" width="30">' . $idx_key . '</td></div>
                                    <div class="product_code"><td class="product_code" align="center" width="90">' . $barcode_product_code . $v2['product_code'] . '</td></div>
                                    <div class="product_name"><td class="product_name" align="left" width="120">' . iconv("TIS-620", "UTF-8//IGNORE", $v2['product_name']) . '</td></div>
                                    <div class="product_status"><td class="product_status" align="left" width="90">' . $v2['product_status'] . '</td></div>
                                    <div class="product_sub_status"><td class="product_sub_status" align="left" width="90">' . $v2['product_sub_status'] . '</td></div>
                                    <div class="lot"><td class="lot" align="center" width="90">' . $barcode_product_lot . iconv("TIS-620", "UTF-8", $v2['product_lot']) . '</td></div>
                                    <div class="serial"><td class="serial" align="center" width="90">' . $barcode_product_serial . iconv("TIS-620", "UTF-8", $v2['product_serial']) . '</td></div>
                                    <div class="mfd"><td class="mfd" align="center" width="90">' . $v2['product_mfd'] . '</td></div>
                                    <div class="exp"><td class="exp" align="center" width="90">' . $v2['product_exp'] . '</td></div>
                                    <div class="reserve_qty"><td class="reserve_qty" align="center" width="60">' . set_number_format($v2['reserv_qty']) . '</td></div>
                                    <div class="confirm_qty"><td class="confirm_qty" align="center" width="60">' . $confirm_qty . '</td></div>
                                    <div class="unit"><td class="unit" align="center" width="60">' . tis620_to_utf8($v2['unit']) . '</td></div>
                                    <div class="uom_qty"><td class="uom_qty" align="center" width="60">' . set_number_format($v2['uom_qty']) . '</td></div>
                                    <div class="uom_unit_prod"><td class="uom_unit_prod" align="center" width="60">' . tis620_to_utf8($v2['uom_unit_prod']) . '</td></div>';

			#check if price_per_unit for show column Price / Unit,Unit Price,All Price - Re-Add By Ball
			if($price_per_unit == TRUE) :
				$page .= '
                                    <div class="price_per_unit"><td class="price_per_unit" align="center" width="60">' . set_number_format($v2['price_per_unit']) . '</td></div>
                                    <div class="unit_price"><td class="unit_price" align="center" width="60">' . $v2['unit_price_value'] . '</td></div>
                                    <div class="all_price"><td class="all_price" align="center" width="60">' . set_number_format($v2['all_price']) . '</td></div>';
			endif;

			$page .= '
                                    <div class="actual_location"><td class="actual_location" align="center" width="90">' . $v2['actual_location'] . '</td></div>';

			#ADD FOR ISSUE 3323: BY KIK 2014-01-30 เพิ่มการแสดงผล build pallet - Re-Add By Ball
			if($build_pallet == TRUE):
				$page .= '
                                    <div class="pallet_code"><td class="pallet_code" align="center" width="60">' . $v2['pallet_code'] . '</td></div>';
			endif;

			$page .= '
                                    <div class="pick_by"><td class="pick_by" align="center" width="90">' . iconv("TIS-620", "UTF-8", $v2['pick_by']) . '&nbsp;</td></div>
                                    <div class="remark"><td class="remark" align="center" style="border-right:1px solid #333333;" >' . iconv("TIS-620", "UTF-8", $v2['remark']) . '&nbsp;</td></div>';

			$page .= '</tr>';
			$idx_key++;

		}

		$sum_reserve_qty += $reserv_total;
		$sum_confirm_qty += $confirm_total;
                $sum_uom_qty     += $uom_total;

		// ================================
		// Show total per location
		$page.='<tr>';

		if($colspan_total != 0):
			$page.='<div><td align="center" colspan="'.$colspan_total.'"> </td></div>';
		endif;

		$page.='<div class="reserve_qty"><td class="reserve_qty" align="center"><b>' . set_number_format($reserv_total) . '</b></td></div>
            <div class="confirm_qty"><td class="confirm_qty" align="center"><b>' . set_number_format($confirm_total) . '</b></td></div>
            <div class="unit"><td class="unit" align="center"> </td></div>';

                $page.='<div class="uom_qty"><td class="uom_qty" align="center"><b>' . set_number_format($uom_total) . '</b></td></div>
            <div class="uom_unit_prod"><td class="uom_unit_prod" align="center"> </td></div>';


		//    #check if price_per_unit for show column Price / Unit,Unit Price,All Price
		if($price_per_unit == TRUE):
		$page.='<div class="price_per_unit"><td class="price_per_unit" align="center"> </td></div>
            <div class="unit_price"><td class="unit_price" align="center"> </td></div>
            <div class="all_price"><td class="all_price" align="center"> </td></div>';
		endif;

		if($colspan_blank > 0):
			$page.='<div><td align="center" colspan="'.$colspan_blank.'"> </td></div>';
		endif;

		$page .= '</tr>';

		// ==================================

	}
	/// Order by Location >> Ball

	// END

$page .= '</tbody>';
$page .= '<tfooter>';
$page .='<tr>';


    if($colspan_total != 0):
        $page.='<div><td align="center" colspan="'.$colspan_total.'"> <b>Total</b> </td></div>';
    endif;

    $page.='

                <div class="reserve_qty"><td class="reserve_qty" align="center"><b>' . set_number_format($sum_reserve_qty) . '</b></td></div>
                <div class="confirm_qty"><td class="confirm_qty" align="center"><b>' . (set_number_format($sum_confirm_qty)==0?'':set_number_format($sum_confirm_qty)) . '</b></td></div>
                <div class="unit"><td class="unit" align="center"> </td></div>
                <div class="uom_qty"><td class="uom_qty" align="center"><b>' . (set_number_format($sum_uom_qty)==0?'':set_number_format($sum_uom_qty)) . '</b></td></div>
                <div class="uom_unit_prod"><td class="uom_unit_prod" align="center"> </td></div>
            ';
//
//
//    #check if price_per_unit for show column Price / Unit,Unit Price,All Price

    if($price_per_unit == TRUE){
        $page.='
                <div class="price_per_unit"><td class="price_per_unit" align="center">' . set_number_format($sumPriceUnit) . '</td></div>
                <div class="unit_price"><td class="unit_price" align="center"> </td></div>
                <div class="all_price"><td class="all_price" align="center">' . set_number_format($sumPrice) . '</td></div>
                ';
    }
    if($colspan_blank > 0):
        $page.='<div><td align="center" colspan="'.$colspan_blank.'"> </td></div>';
    endif;


    $page .= '</tr>';
	$page .= '</tfooter>';



$page .= '</table>';

//$text = '<BR><BR><BR><barcode code="'.$data->Doc_Refer_Ext.'" type="C128A" size="1" height="1.0" />';
//$text .= '<BR><BR><BR><barcode code="'.$data->Doc_Refer_Ext.'" type="C128" size="1" height="2.0" />';
//$text .= '<BR><BR><BR><barcode code="'.$data->Doc_Refer_Ext.'" type="C128" size="1" height="2.0" />';

//p($page);exit();

$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
//		$page = iconv("TIS-620","UTF-8",$page);
$mpdf->WriteHTML($page);
//$mpdf->Output($strSaveName, 'I'); // *** file name

$filename = "PickingJob(" . url_title(str_replace('/', '_', $data->Doc_Refer_Ext), '_') . ")-" . date('Ymd') . "-" . date('His') . ".pdf";
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F');
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName);
