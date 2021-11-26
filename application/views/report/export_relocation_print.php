<?php

date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 28, 25, 5, 5);
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
$header = '
    <table border="0" width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
        <tr>			
            <td height="30" width="450">
                <img style="width:150px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
            </td>
            <td align="center">
                <font size="5"><b>'.$text_header.'</b></font>
            </td>
            <td width="450"> </td>
        </tr>
    </table>

    <table width="700" align="center" border="0" style="font-family: arial; font-size:14px;">
        <tr>
            <td width="150">
                Est.Re-Locate date
            </td>
            <td width="10">
                :
            </td>
            <td width="320">
                ' . $est_relocate_date . '
            </td>
            <td width="100">
                Re-Locate date
            </td>
            <td width="10">
                :
            </td>
            <td >
                ' . ($relocate_date==''?'_______________':$relocate_date) . '
            </td>
        </tr>
        <tr>
            <td width="120">
                Re-Locate No.
            </td>
            <td width="10">
                :
            </td>
            <td width="320">
                ' . $relocation_no . '
            </td>
            <td width="250">
                Worker Name
            </td>
            <td width="10">
                :
            </td>
            <td >
                ' . $workerName . '
            </td>
        </tr>
    </table>

';

$footer .= '
                        <div style="width:100%;font-size:15px; font-family: arial;font-weight:bold;">
                                    <div style="width: 25%;float:right; padding:10px;">
                                        <div>Forklift:........................................................</div>
                                        <br>
                                        <div>....................../......................./......................</div> 
                                    </div>  
                        </div>
                        <table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
            <tr>
                <td width="50%" align="left">
                    Print By : ' . iconv("TIS-620", "UTF-8", $printBy) . '  , 
                    Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
                <td align="right">{PAGENO}/{nb}</td>
            </tr>
           </table>
                        ';




$header = iconv("TIS-620","UTF-8",$header);
//		$footer = iconv("TIS-620","UTF-8",$footer);
$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');
# head_table is header of table

$head_table = "
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
                    padding:3px;
                    font-family:arial;
            }

            {$set_css_for_show_column}
        </style>
        
        <table width='100%' cellspacing='0' class='tborder'><thead><tr>";
$set_index_column = 0;
$last_of_column = count($column)-1;
foreach ($column as $keyH => $h) {
//    $cssBorder = '';
//    if ($keyH == 0):
//        $cssBorder = 'border-left:1px solid black;border-radius-corner: 5px;';
//    elseif($keyH == count($column)-1):
//        $cssBorder = 'border-right:1px solid black;border-radius-corner: 5px;';
//    endif;
    $cssBorder = '';
    if ($set_index_column == $last_of_column):
        $cssBorder = 'border-right:1px solid black;border-radius-corner: 5px;';
    else:
        $cssBorder = 'border-left:1px solid black;'; 
    endif;
    
    $set_index_column += 1;
    $head_table.='<div class="'.$keyH.'" ><th style="border-bottom:1px solid black;border-top:1px solid black; border-left:1px solid black; ' . $cssBorder . '">' . $h . '</th></div>';
}

$head_table.='</tr></thead>';

$page = '';

$page.=$head_table;


$page.= '<tbody>';

$sum_reserve_qty = 0;
$sum_confirm_qty = 0;
$sumPriceUnit    = set_number_format(0);
$sumPrice        = set_number_format(0);
//    p($order_detail);
//    exit();
foreach($order_detail as $j => $detail):
    $k = $j + 1;
    $bg = '';
    if ($order_detail[$j]['to_location'] != $order_detail[$j]['act_location']) :
        $bg = 'style="color:#FF0000"';
    endif;
    $cssStyle = 'style="font-family:arial;font-size:9px;border-bottom: 1px solid black;border-left: 1px solid black;"';
    $cssStyleLast = 'style="font-family:arial;font-size:9px;border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black;"';

    $sum_reserve_qty = $sum_reserve_qty + $order_detail[$j]['reserv_qty'];
    $sum_confirm_qty = $sum_confirm_qty + $order_detail[$j]['confirm_qty'];
    $sumPriceUnit = $sumPriceUnit + $order_detail[$j]['Price_Per_Unit'];
    $sumPrice = $sumPrice + $order_detail[$j]['All_Price'];
    
    $page.='
        <tr>
                <div class="no"><td align="center" width="50" ' . $cssStyle . '>' . $k . '</td></div>
                <div class="product_code"><td align="center" width="90" ' . $cssStyle . '>' . $order_detail[$j]['product_code'] . '&nbsp;</td></div>
                <div class="product_name"><td align="left" width="150" ' . $cssStyle . '>' . iconv("TIS-620", "UTF-8", $order_detail[$j]['product_name']) . '&nbsp;</td></div>
                <div class="product_status"><td align="left" width="90" ' . $cssStyle . '>' . $order_detail[$j]['product_status'] . '&nbsp;</td></div>
                <div class="product_sub_status"><td align="left" width="90" ' . $cssStyle . '>' . $order_detail[$j]['Product_Sub_Status'] . '&nbsp;</td></div>
                <div class="lot"><td align="center" width="90" ' . $cssStyle . '>' . iconv("TIS-620", "UTF-8", $order_detail[$j]['product_lot']) . '&nbsp;</td></div>
                <div class="serial"><td align="center" width="90" ' . $cssStyle . '>' . iconv("TIS-620", "UTF-8", $order_detail[$j]['product_serial']) . '&nbsp;</td></div>
                <div class="move_qty"><td align="right" width="60" ' . $cssStyle . '>' . set_number_format($order_detail[$j]['reserv_qty']) . '&nbsp;</td></div>
                <div class="confirm_qty"><td align="right" width="60" ' . $cssStyle . '>' . set_number_format($order_detail[$j]['confirm_qty']) . '&nbsp;</td></div>
    ';

    #check if price_per_unit for show column Price / Unit,Unit Price,All Price
    if($price_per_unit == TRUE):
    $page.='
        <div class="price_per_unit"><td align="right" width="60" ' . $cssStyle . '>' . set_number_format($order_detail[$j]['Price_Per_Unit']) . '&nbsp;</td></div>
        <div class="unit_price"><td align="center" width="60" ' . $cssStyle . '>' . $order_detail[$j]['Unit_Price_value'] . '&nbsp;</td></div>
        <div class="all_price"><td align="right" width="60" ' . $cssStyle . '>' . set_number_format($order_detail[$j]['All_Price']) . '&nbsp;</td></div>
    ';
    endif;

    $page.='
           <div class="location_form"><td align="center" width="90" ' . $cssStyle . '>' . $order_detail[$j]['from_location'] . '&nbsp;</td></div>
           <div class="suggest_location"><td align="center" width="90" ' . $cssStyle . '>' . $order_detail[$j]['to_location'] . '&nbsp;</td></div>
           <div class="actual_location"><td align="center" width="90" ' . $cssStyle . '>' . $order_detail[$j]['act_location'] . '&nbsp;</td></div>
           <div class="remark"><td align="center" ' . $cssStyleLast . '>' . iconv("TIS-620", "UTF-8", $order_detail[$j]['remark']) . '&nbsp;</td></div>
       </tr>
    ';

endforeach;
//
//$page .= '</tbody>';

//$page .= '<tfoot>';

$page .= '<tr>';

    if($count_colspan > 0):
        $page .= '<div><td align="center" colspan="'.$count_colspan.'" ' . $cssStyle . '> <b>Total</b>&nbsp;</td></div>';
    endif;
    
        $page .='
        <div class="move_qty"><td class="move_qty" align="right" ' . $cssStyle . '><b>' . set_number_format($sum_reserve_qty) . '&nbsp;</b></td>
        <div class="confirm_qty"><td class="confirm_qty" align="right" ' . $cssStyle . '><b>' . set_number_format($sum_confirm_qty) . '&nbsp;</b></td>';
    #check if price_per_unit for show column Price / Unit,Unit Price,All Price
if($price_per_unit == TRUE){
    $page.='
        <div class="price_per_unit"><td class="price_per_unit" align="right">' . set_number_format($sumPriceUnit) . '&nbsp;</td>
        <div class="unit_price"><td class="unit_price" align="center"> </td>
        <div class="all_price"><td class="all_price" align="right">' . set_number_format($sumPrice) . '&nbsp;</td>';
}
           
if($count_colspan_after_sum > 0):
//    $page.='<div><td align="center" colspan="'.$count_colspan_after_sum.'" ' . $cssStyleLast . '> </td></div>';
    $page.='<div><td align="center" colspan="'.$count_colspan_after_sum.'" ' . $cssStyle . '>&nbsp;</td></div>';
endif;


$page .= '</tr>';

//$page .= '</tfoot>';
$page .= '</tbody>';

$page .= '</table>';


// echo $footer;
// exit();

$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
//		$page = iconv("TIS-620","UTF-8",$page);

$mpdf->WriteHTML($page);

//$mpdf->Output($strSaveName, 'I'); // *** file name

$filename = 'Relocation-Job-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F'); 
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName); 
?>