<?php
date_default_timezone_set('Asia/Bangkok') ;
$settings = native_session::retrieve();
$mpdf = new mPDF('UTF-8');
$mpdf = new mPDF('th', 'A4-L', '13', '', 5, 5, 25, 30, 5, 5);
$mpdf->ignore_invalid_utf8 = true;

$document_no = Array();

foreach ( $document_list as $idx => $val ) :

    $document_no[$val->Doc_Refer_Int] = $val->Doc_Refer_Int;// . "(" . $order_mapping[$val->Document_No]['State_NameTh'] . "),";

endforeach;

$document_no = array_unique($document_no);
//$document_no = substr($document_no, 0, -1);



$header = '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
    <tr>
        <td height="30" width="300">
            <img style="width:auto;height:0.8cm;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
        </td>
        <td style="text-align: center;font-weight: bold; font-size: 12pt;">'.iconv("TIS-620","UTF-8", 'Picking Consolidation Report').'</td>
        <td width="300"> </td>
    </tr>
</table>
<table width="100%" align="left" border="0" style="font-size:10pt; font-family: arial; margin-top: 10px;">
    <tr>
        <td>'._lang('document_no') . ' : ' . key($document_no) . '</td>
    </tr>
</table>';

// Signature for Driver
$footer .= "<div style='width:100%; padding: 10px; margin-left: 72%; font-size:10pt; font-family: Arial;'>";
$footer .= "<div>Driver Signature: ..............................................</div>";
$footer .= "<div style='margin: 2px;'>(".nbs(70).")</div>";
$footer .= "<div> ................. / ............................... / ................. </div>";
$footer .= "</div>";
// END

$footer .= '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
<tr>
    <td width="50%" align="left">
        Print By : ' . iconv("TIS-620", "UTF-8", $printBy) . '  ,
        Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
    <td align="right">{PAGENO}/{nb}</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header,'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer,'E');

$head_table = '<style>
    ' . $set_css_for_show_column . '
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

<table width="100%" cellspacing="0" style="font-family: arial;font-size:10px; border-right: 1px solid #333;" class="tborder">
    <thead>
        <tr style="border:1px solid #333333;" bgcolor="#B0C4DE">
            <div class="product_code" width="10%"><th class="product_code" width="10%">'._lang('product_code').'</th></div>
            <div class="product_name" width="20%"><th class="product_name" width="20%">'._lang('product_name').'</th></div>
            <div class="status" width="7%"><th class="status" width="7%">'._lang('status').'</th></div>
            <div class="lot" width="7%"><th class="lot" width="7%">'._lang('lot').'</th></div>
            <div class="mfd" width="7%"><th class="mfd" width="7%">'._lang('mfd').'</th></div>
            <div class="exp" width="9%"><th class="exp" width="9%">'._lang('exp').'</th></div>
            <div class="reserv_qty" width="5%"><th class="qty" width="5%">'._lang('Reserv Qty').'</th></div>
            <div class="qty" width="5%"><th class="qty" width="5%">'._lang('qty').'</th></div>
            <div class="unit" width="5%"><th class="unit" width="5%">'._lang('unit').'</th></div>
            <div class="suggest" width="5%"><th class="suggest" width="5%">'._lang('Suggest').'</th></div>
            <div class="actual" width="5%"><th class="actual" width="5%">'._lang('Actual').'</th></div>
            <div class="remark" width="5%"><th class="remark" width="5%">'._lang('invoice').'</th></div>
        </tr>
    </thead>
    <tbody>';

    $page ='';
    $page.=$head_table;

    if(!empty($data)){
        $sum_all=0;
        $sum_price = 0;
        $sum_all_price = 0;
        $is_start_new = FALSE;
        $is_start_sum = 0;
        $is_start_sum_reserv = 0;
        $is_end_rec = 1;
        foreach($data as $idx => $value){

            $iTemp = 1;

            if ( $is_start_new != $value['product_code'] && $is_start_new != FALSE) :

                $is_start_sum_reserv_total = ( (int) $is_start_sum_reserv == 0 ) ? "" : set_number_format( $is_start_sum_reserv );
                $is_start_sum_total = ( (int) $is_start_sum == 0 ) ? "" : set_number_format( $is_start_sum );

                $page.='<tr style="background-color: #EEE;">
                <td colspan="'.( $all_column ).'" align="right"><b>Sub Total</b></td>
                <td align="right" style="font-size: 2em;"><b>'.$is_start_sum_reserv_total.'</b></td>
                <td align="right"><b>'.$is_start_sum_total.'</b></td>
                <td colspan="4"></td>
                </tr>';
                $is_start_new = $value['product_code'];
                $is_start_sum = $value['total'];
                $is_start_sum_reserv = $value['total_reserv'];
            else :
                $is_start_new = $value['product_code'];
                $is_start_sum += $value['total'];
                $is_start_sum_reserv += $value['total_reserv'];
            endif;

            $total = ( (int) $value['total'] == 0 ) ? "" : set_number_format( $value['total'] );

            $page.='
                <tr style="border-bottom: 3px #000 solid;">
                    <div class="product_code"><td class="product_code" valign="top" align="center" style="font-size: 2em;">'.$value['product_code'].'</td></div>
                    <div class="product_name"><td  class="product_name" valign="top">'.iconv("TIS-620","UTF-8",$value['product_name']).'</td></div>
                    <div class="status"><td  class="lot" valign="top" align="center">'.$value['status'].'</td></div>
                    <div class="lot"><td  class="lot" valign="top" align="center" style="font-size: 2em;">'.$value['lot'].'</td></div>
                    <div class="mfd"><td  class="mfd" valign="top" align="center">'.$value['mfd'].'</td></div>
                    <div class="exp"><td  class="exp" valign="top" align="center">'.$value['exp'].'</td></div>
                    <div class="qty"><td class="qty" valign="top" align="right" style="font-size: 2em;">'.set_number_format($value['total_reserv']).'</td></div>
                    <div class="reserve_qty"><td class="reserve_qty" valign="top" align="right">'.$total.'</td></div>
                    <div class="unit"><td class="unit" valign="top" align="left">'.$value['unit'].'</td></div>
                    <div class="suggest"><td class="suggest" valign="top" align="left" style="font-size: 2em;">'.$value['suggest'].'</td></div>
                    <div class="actual"><td class="actual" valign="top" align="left">'.$value['actual'].'</td></div>
                    <div class="remark"><td class="remark" valign="top" align="left">'.implode(", ",$value['remark']).'</td></div>
                </tr>';

            $iTemp++;
            $sum_all += @$value['total'];
            $sum_all_reserv += @$value['total_reserv'];
        }

        $page.='<tr  bgcolor="#F0F0F0">
        <div><td colspan="'. ( $all_column ) .'" align="center"><b>Total</b></td></div>
        <div><td align="right" style="font-size: 2em;"><b>' . set_number_format($sum_all_reserv) . '</b></td></div>
        <div><td align="right"><b>' . set_number_format($sum_all) . '</b></td></div>
        <div class="unit"><td class="unit"></td></div>
        <div class="suggest"><td class="suggest"></td></div>
        <div class="actual"><td class="unit"></td></div>
        <div class="remark"><td class="remark"></td></div>';
    }
    $page.='</tbody></table>';

    //print_r( $page ); exit;

    $this->load->helper('file');
    $stylesheet =  read_file('../libraries/pdf/style.css');
    $mpdf->WriteHTML($stylesheet,1);
    $mpdf->WriteHTML($page);
    $filename = 'Picking-Consolidation-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
    $strSaveName = $settings['uploads']['upload_path'] . $filename;
    $tmp = $mpdf->Output($strSaveName, 'F');
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");
    readfile($strSaveName);
