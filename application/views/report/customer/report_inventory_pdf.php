<?php

set_time_limit(0);
date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
//$mpdf = new mPDF('UTF-8');
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 30, 25, 5, 5);

#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
$header = '

		<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
			<tr>
				<td height="30" width="200">
                                    <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                                </td>
                                <td style="text-align: center;font-weight: bold;">STOCK BALANCE REPORT</td>
				<td width="200"> </td>
			</tr>
			<tr>
				<td align="center" height="10"></td>
			</tr>
		</table>

		<table width="100%" style="font-size: 14px;font-family: arial;">
			<tr>
				<td width="15%" height="25" align="right" style="font-family: garuda;">ผู้เช่าคลัง : </td>
				<td width="50%">' . $Company_NameEN . '</td>
				<td width="15%" align="right" style="font-family: garuda;">วันที่ : </td><td>' . date('d/m/Y') . '</td>
			</tr>
		</table>

		';

$footer = '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
					<tr>
                                        <td width="50%" align="left">
                                                Print By : ' . tis620_to_utf8($printBy) . '  ,
                                                Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
						<td align="right">{PAGENO}/{nb}</td>
					</tr>
					</table>';

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');

$page = '';
$count_row = count($datas);

# calculate page number
# max_line is number of table row in 1 page
########## start loop show page ########
# head_table is header of table
$head_table = '
		<style>
                        ' . $set_css_for_show_column . '
                            td,th {
                            font-family: garuda
                            }
}
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
			.tdData {
				font-size:10px;
				border-right:1px solid #333333;
				border-bottom:1px solid #333333;
				padding:5px 2px 5px 2px;
				text-align:center;
			}
			table.tborder thead tr th{
				border-bottom:1px solid #666666;
			}
		</style>

		  <table width="100%" cellspacing="0" style="border:1px solid #666666;font-size:10px;font-family: arial;" class="tborder">
		<thead>
                                        <tr style="border:1px solid #333333;" bgcolor="#B0C4DE">
                                        <th width="3%" height="30">' . _lang('no') . '</th>
                                        <div class="invoice"><th width="7%">Date In</th></div>
                                        <div class="product_name"><th width="10%">Material No.</th></div>
                                        <div class="product_name"><th width="12%">Description</th></div>
                                        <div class="product_name"><th width="9%">Doc Ext.</th></div>
                                        <div class="invoice"><th width="7%">Batch No.</th></div>
                                        <div class="lot"><th width="7%">Lot No.</th></div>                                      
                                        <div class="class"><th width="7%">Type</th></div>
                                        <div class="invoice"><th width="7%">' . _lang('invoice_no') . '</th></div>
                                        <div class="qty"><th  width="7%">Sum of Qty</th></div>
                                        <div class="qty"><th  width="9%">Dimension</th></div>
                                        <div class="qty"><th  width="7%">CBM</th></div>
                                        <div class="cbm"><th  width="7%">Sum of Total / CBM</th></div>
                                        <div class="unit"><th  width="7%">Unit</th></div>
                                         </tr>';
$head_table.='</thead>	<tbody>';

$page.=$head_table;

if (!empty($datas)) {
//                  p($datas);exit();
    $sum_balance = array();
    $i = 1;
    $sum_reject = 0;
    $sum_reject_cbm = 0;
    $sum_reject_cbm_all = 0;

    $sum_totalbal = 0;
    $sum_cbm_all = 0;
    $sum_cbm = 0;

    $hash = "";
    $total_per_group = 0;
    $cbm_all_per_group = 0;
    $cbm_per_group = 0;

    $sum_balance_all = 0;
    $sum_cbm_total = 0;
    $sum_cbm_all_total = 0;

    $Unit = "";
    $Unit_Value = "";
    foreach ($datas as $key => $data) {
        $set_tr_reject = "";
        if ($data->totalbal < 0):
            $set_tr_reject = 'style = "color:red;"';
            $sum_reject+=@(-$data->totalbal);
            $sum_reject_cbm_all+=@(-$data->CBM);
            $sum_reject_cbm+=@(-$data->CBM);
        else:
            $sum_totalbal+=@$data->totalbal;
            $sum_cbm+= @$data->CBM;
            $sum_cbm_all+=@$data->CBM;
        endif;
        $sum_cbm = @$data->CBM;
        $sum_cbm_all = $data->totalCBM_All;

        if (!empty($hash) && $hash != md5($data->Product_NameEN . $data->Unit_Value)) {
            $page.= '<tr>'
                    . '<td style="background-color: #E6F1F6; text-align:center;" colspan="' . ($colspan) . '">Total</td>'
                    . '<td style="background-color: #E6F1F6; text-align:right;padding-right:5px" >' . set_number_format($total_per_group) . '</td>'
                    . '<td style="background-color: #E6F1F6; text-align:right;padding-right:5px" ></td>'
                    . '<td style="background-color: #E6F1F6; text-align:right;padding-right:5px" >' . set_number_format($cbm_per_group) . '</td>'
                    . '<td style="background-color: #E6F1F6; text-align:right;padding-right:5px">' . set_number_format($cbm_all_per_group) . '</td>'
                    . '<td style="background-color: #E6F1F6; text-align:center;"></td>'
                    . '</tr>';
            $total_per_group = $data->totalbal;
            $cbm_per_group = $sum_cbm;
            $cbm_all_per_group = $sum_cbm_all;
            $Unit = tis620_to_utf8($data->Unit_Value);
        } else {
            $total_per_group += $data->totalbal;
            $cbm_per_group += $sum_cbm;
            $cbm_all_per_group += $sum_cbm_all;
            $Unit = tis620_to_utf8($data->Unit_Value);
        }
        $hash = md5($data->Product_NameEN . $data->Unit_Value);
        $page.="<tr>";
        $page.='<td height="23" valign="top" class="tdData" style="text-align:center">' . $i . '</td>';
        $page.='<div class="date_in"><td class="tdData" style="text-align:center">' . $data->Receive_Date . '&nbsp;</td></div>';
        $page.='<div class="product_name"><td class="tdData" style="text-align:left">' . $data->Product_Code . '</td></div>';
        $page.='<div class="product_name"><td class="tdData" style="text-align:left">' . $data->Product_NameEN . '</td></div>';
        $page.='<div class="doc_refer_ext"><td class="tdData" style="text-align:center">' . $data->Doc_Refer_Ext . '&nbsp;</td></div>';
        $page.='<div class="lot"><td class="tdData" style="text-align:center">' . $data->Product_Lot . '&nbsp;</td></div>';
        $page.='<div class="serial"><td class="tdData" style="text-align:center">' . $data->Product_Serial . '&nbsp;</td></div>';
        $page.='<div class="class"><td class="tdData" style="text-align:center">' . $data->DIW_Class . '&nbsp;</td></div>';
        $page.='<div class="invoice"><td class="tdData" style="text-align:left">' . $data->Invoice_No . '&nbsp;</td></div>';
        $page.='<div class="qty"><td class="tdData" style="text-align:right">' . $data->totalbal . '&nbsp;</td></div>';
        $page.='<div class="cbm"><td class="tdData" style="text-align:center">' . $data->Dimension . '&nbsp;</td></div>';
        $page.='<div class="cbm"><td class="tdData" style="text-align:right">' . $data->CBM . '&nbsp;</td></div>';
        $page.='<div class="cbm"><td class="tdData" style="text-align:right">' . $data->totalCBM_All . '&nbsp;</td></div>';
        $page.='<div class="unit"><td class="tdData" style="text-align:center">' . $data->Unit_Value . '&nbsp;</td></div>';
        $page.="</tr>";

        $i++;
        $iTemp++;

        $sum_balance_all += $data->totalbal;
        $sum_cbm_total += $sum_cbm;
        $sum_cbm_all_total += $sum_cbm_all;

        $Unit = tis620_to_utf8($data->Unit_Value);
        $Unit_Value = tis620_to_utf8($data->Unit_Value);
    }

    // Case if have remain data
    if ($total_per_group > 0) {
        $page .= '<tr>'
                . '<td style="background-color: #E6F1F6; text-align:center;" colspan="' . ($colspan) . '">Total</td>'
                . '<td style="background-color: #E6F1F6; text-align:right;padding-right:5px">' . set_number_format($total_per_group) . '</td>'
                . '<td style="background-color: #E6F1F6; text-align:right;padding-right:5px"></td>'
                . '<td style="background-color: #E6F1F6; text-align:right;padding-right:5px">' . set_number_format($cbm_per_group) . '</td>'
                . '<td style="background-color: #E6F1F6; text-align:right;padding-right:5px">' . set_number_format($cbm_all_per_group) . '</td>'
                . '<td style="background-color: #E6F1F6; text-align:center;"></td>'
                . '</tr>';
    }
    // ================
}


$page.='<tr bgcolor="#F5F5F5"><td colspan="' . ($colspan) . '" class="tdData"  align="left"><b>Grand Total</b></td>';
$page.='<td  align="right" class="tdData" style="padding-right:5px"><b>' . set_number_format($sum_balance_all) . '</b></td>
            <td  align="right" class="tdData" style="padding-right:5px"><b></b></td>
            <td  align="right" class="tdData" style="padding-right:5px"><b>' . set_number_format($sum_cbm_total) . '</b></td>
             <td  align="right" class="tdData" style="padding-right:5px"><b>' . set_number_format($sum_cbm_all_total) . '</b></td>
              <td  align="right" class="tdData"></td> ';

$page.='</tr>';
$page.='</tbody>';

$page.='</table>';
//echo $page;
//exit();

$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');

$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($page);

$filename = 'Stock_Balance_Report-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F');
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName);
//
?>