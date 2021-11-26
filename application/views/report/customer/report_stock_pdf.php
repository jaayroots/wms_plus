<?php

set_time_limit(0);
date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
//$mpdf = new mPDF('UTF-8');
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 15, 25, 5, 5);

#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
$header = '

		<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
		  <tr>
		    <td height="5" width="200"></td>
                                       <td style="text-align: center;font-weight: bold;">STOCK REPORT</td>
		    <td width="200"> </td>
		  </tr>		
		</table>
		';

$footer = '';

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
                                        <th width="5%" height="30">' . _lang('no') . '</th>
                                        <th style="width: 12%;">Order ID</th>
                                        <th style="width: 8%;">Load Date</th>
                                        <th style="width: 12%;">' . _lang("container_no") . '</th>
                                        <th style="width: 8%;">' . _lang("container_size") . '</th>
                                        <th style="width: 12%;">' . _lang("product_code") . '</th>
                                        <th style="width: 12%;">' . _lang("product_name") . '</th>
                                        <th style="width: 12%;">' . _lang("lot") . '</th>
                                        <th style="width: 8%;">Date In</th>                      
                                        <th style="width: 8%;">Storage Days</th>                    
                                        <th style="width: 8%;">' . _lang("dispatch_qty") . '</th>         
                                         </tr>';
$head_table.='</thead>	<tbody>';

$page.=$head_table;

if (!empty($datas)) {

    foreach ($datas as $key => $data) {
        $sum_date = 0;
        $sum_qty = 0;
        $i = 0;

        $page.="<tr>";
        $page.= "<td colspan='11' style='text-align: left;background-color: #c9c9cb;font-weight: 700;'> SOE No. :" . $data["group_head"] . "</td>";
        $page.="</tr>";
        unset($data["group_head"]);
        foreach ($data as $index => $val) {
            $i++;
            $page.="<tr>";
            $page.='<td class="tdData" style="text-align:center">' . $i . '</td>';
            $page.='<td class="tdData" style="text-align:center">' . $val["Document_No"] . '&nbsp;</td>';
            $page.='<td class="tdData" style="text-align:center">' . $val["Real_Action_Date"] . '&nbsp;</td>';
            $page.='<td class="tdData" style="text-align:center">' . tis620_to_utf8($val["Cont_No"]) . '&nbsp;</td>';
            $page.='<td class="tdData" style="text-align:center">' . $val["Cont_Size"] . '&nbsp;</td>';
            $page.='<td class="tdData" style="text-align:center">' . $val["Product_Code"] . '&nbsp;</td>';
            $page.='<td class="tdData" style="text-align:center">' . tis620_to_utf8($val["Product_NameEN"]) . '&nbsp;</td>';
            $page.='<td class="tdData" style="text-align:center">' . tis620_to_utf8($val["Product_Lot"]) . '&nbsp;</td>';
            $page.='<td class="tdData" style="text-align:center">' . $val["Receive_Date"] . '&nbsp;</td>';
            $page.='<td class="tdData" style="text-align:right">' . number_format($val["Storage_Day"]) . '&nbsp;</td>';
            $page.='<td class="tdData" style="text-align:right">' . set_number_format($val["DP_Qty"]) . '&nbsp;</td>';
            $page.="</tr>";

            $sum_date += $val["Storage_Day"];
            $sum_qty += $val["DP_Qty"];
        }
        $page .= '<tr>'
                . '<td style="background-color: #E6F1F6; text-align:center;" colspan="9">Total</td>'
                . '<td style="background-color: #E6F1F6; text-align:right;padding-right:5px">' . number_format($sum_date) . '</td>'
                . '<td style="background-color: #E6F1F6; text-align:right;padding-right:5px">' . set_number_format($sum_qty) . '</td>'
                . '</tr>';
    }
}

$page.='</tr>';
$page.='</tbody>';

$page.='</table>';

$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');

$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($page);

$filename = 'Stock_Report-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F');
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName);
//
?>