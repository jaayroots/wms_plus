<?php

date_default_timezone_set('Asia/Bangkok');
$settings = native_session::retrieve();
$mpdf = new mPDF('th', 'A4-L', '11', '', 5, 5, 28, 25, 5, 5);
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
$header = '
    <table border="0" width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
        <tr>			
            <td height="30" width="400">
                <img style="width:150px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
            </td>
            <td align="center">
                <font size="5"><b>'.$text_header.'</b></font>
            </td>
            <td width="400"> </td>
        </tr>
    </table>

    <table width="700" align="center" border="0" style="font-family: arial; font-size:14px;">
        <tr>
            <td width="120">
                Est.Action Date
            </td>
            <td width="10">
                :
            </td>
            <td width="420">
                ' . $est_action_date . '
            </td>
            <td width="120">
                Action Date
            </td>
            <td width="10">
                :
            </td>
            <td >
                ' . ($action_date==''?'_______________':$action_date) . '
            </td>
        </tr>
        <tr>
            <td width="120">
                Document No.
            </td>
            <td width="10">
                :
            </td>
            <td width="420">
                ' . $document_no . '
            </td>
            <td width="120">
                Worker Name
            </td>
            <td width="10">
                :
            </td>
            <td >
                ' . $worker_name . '
            </td>
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

$header = iconv("TIS-620","UTF-8",$header);
//		$footer = iconv("TIS-620","UTF-8",$footer);
$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header, 'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer, 'E');
# head_table is header of table

$head_table = "
        <style>
            '.$set_css_for_show_column.'
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
    $cssBorder = '';
    if ($set_index_column == $last_of_column):
        $cssBorder = 'border-right:1px solid black;border-radius-corner: 5px;';
    else:
        $cssBorder = 'border-left:1px solid black;'; 
    endif;
    
    $set_index_column += 1;
    $head_table.='<div class="'.$keyH.'" ><th bgcolor="#B0C4DE" style="border-bottom:1px solid black;border-top:1px solid black; border-left:1px solid black; ' . $cssBorder . '" class="'.$keyH.'">' . $h . '</th></div>';
}

$head_table.='</tr></thead>';

$page = '';

$page.=$head_table;

$sum_reserve_qty = 0;
$sum_confirm_qty = 0;
$sumPriceUnit    = 0;
$sumPrice        = 0;
//    p($order_detail);
//    exit();

$sum_all = array();
foreach($order_detail as $j => $detail):
    $k = $j + 1;
    $bg = '';
    if ($order_detail[$j]['to_location'] != $order_detail[$j]['act_location']) :
        $bg = 'style="color:#FF0000"';
    endif;
    $cssStyle = 'style="font-family:arial;font-size:9px;border-bottom: 1px solid black;border-left: 1px solid black;"';
    $cssStyleLast = 'style="font-family:arial;font-size:9px;border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black;"';

    $sum_all['change_qty']['total']  = @$sum_all['change_qty']['total'] + $order_detail[$j]['reserv_qty'];
    $sum_all['confirm_qty']['total']  = @$sum_all['confirm_qty']['total'] + $order_detail[$j]['confirm_qty'];
    $sum_all['price_per_unit']['total']  = @$sum_all['price_per_unit']['total'] + $order_detail[$j]['Price_Per_Unit'];
    $sum_all['all_price']['total']  = @$sum_all['all_price']['total'] + $order_detail[$j]['All_Price'];
    
//    $sum_reserve_qty = $sum_reserve_qty + $order_detail[$j]['reserv_qty'];
//    $sum_confirm_qty = $sum_confirm_qty + $order_detail[$j]['confirm_qty'];
//    $sumPriceUnit = $sumPriceUnit + $order_detail[$j]['Price_Per_Unit'];
//    $sumPrice = $sumPrice + $order_detail[$j]['All_Price'];

    $page.='
        <tr>
                <div class="no"><td align="center" width="50" ' . $cssStyle . ' class="no">' . $k . '</td></div>
                <div class="product_code"><td class="product_code" align="center" width="90" ' . $cssStyle . '>' . $order_detail[$j]['product_code'] . '</td></div>
                <div class="product_name"><td class="product_name" align="left" width="150" ' . $cssStyle . '>' . iconv("TIS-620", "UTF-8", $order_detail[$j]['product_name']) . '</td></div>
                <div class="product_status"><td class="product_status" align="left" width="90" ' . $cssStyle . '>' . $order_detail[$j]['product_status'] . '</td></div>
                <div class="product_sub_status"><td class="product_sub_status" align="left" width="90" ' . $cssStyle . '>' . $order_detail[$j]['Product_Sub_Status'] . '</td></div>
                <div class="lot"><td class="lot" align="center" width="90" ' . $cssStyle . '>' . iconv("TIS-620", "UTF-8", $order_detail[$j]['product_lot']) . '</td></div>
                <div class="serial"><td class="serial" align="center" width="90" ' . $cssStyle . '>' . iconv("TIS-620", "UTF-8", $order_detail[$j]['product_serial']) . '</td></div>
                <div class="mfd"><td class="mfd" align="left" width="90" ' . $cssStyle . '>' . $order_detail[$j]['product_mfd'] . '</td></div>
                <div class="exp"><td class="exp" align="left" width="90" ' . $cssStyle . '>' . $order_detail[$j]['product_exp'] . '</td></div>
                <div class="change_qty"><td class="change_qty" align="right" width="60" ' . $cssStyle . '>' . set_number_format($order_detail[$j]['reserv_qty']) . '</td></div>
                <div class="confirm_qty"><td class="confirm_qty" align="right" width="60" ' . $cssStyle . '>' . set_number_format($order_detail[$j]['confirm_qty']) . '</td></div>
                <div class="price_per_unit"><td class="price_per_unit" align="right" width="60" ' . $cssStyle . '>' . set_number_format($order_detail[$j]['Price_Per_Unit']) . '</td></div>
                <div class="unit_price"><td class="unit_price" align="center" width="60" ' . $cssStyle . '>' . $order_detail[$j]['Unit_Price_value'] . '</td></div>
                <div class="all_price"><td class="all_price" align="right" width="60" ' . $cssStyle . '>' . set_number_format($order_detail[$j]['All_Price']) . '</td></div>
                <div class="location_form"><td class="location_form" align="center" width="90" ' . $cssStyle . '>' . $order_detail[$j]['from_location'] . '</td></div>
                <div class="suggest_location"><td class="suggest_location" align="center" width="90" ' . $cssStyle . '>' . $order_detail[$j]['to_location'] . '</td></div>
                <div class="actual_location"><td class="actual_location" align="center" width="90" ' . $cssStyle . '>' . $order_detail[$j]['act_location'] . '</td></div>
                <div class="remark"><td class="remark" align="center" ' . $cssStyleLast . '>' . iconv("TIS-620", "UTF-8", $order_detail[$j]['remark']) . '</td></div>
            </tr>';
endforeach;

//$page .= '<tfoot>';
$page .='<tr  bgcolor="#C9C9C9">';
        if(!empty($setColspan)):
            $num = 0;
            foreach($setColspan as $col => $value):
                $colspan = ( $value['colspan']>1)? "colspan='". $value['colspan'] ."'" : "";
                 if($num){
                    $show = !empty($sum_all[$col]['total'])? set_number_format($sum_all[$col]['total']) : "";
                    $style = "style='text-align:right;'";
                }else{
                    $show =  "<b>"._lang('total')."</b>";
                    $style = "style='text-align:right;'";
                }

                if(($num+1) == count($setColspan)):
                    $style="style='border-right: 1px solid #666666;'";
                endif;

                $page.= "<div {$colspan}><td {$colspan}  {$style}>{$show}</td></div>";
                $num++;
            endforeach;
        endif;
        $page .= '</tr>';
        
$page .= '</tbody>';
$page .= '</table>';


//p($page);
//exit();

$this->load->helper('file');
$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
//		$page = iconv("TIS-620","UTF-8",$page);

$mpdf->WriteHTML($page);

//$mpdf->Output($strSaveName, 'I'); // *** file name

$filename = 'ChangeProductStatus-Job-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F'); 
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName); 
?>