<?php
date_default_timezone_set('Asia/Bangkok') ;
$settings = native_session::retrieve();
$mpdf=new mPDF('th','A4-L','11','',5,5,20,25,5,5); 
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)

$header = '
    <table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
    <tr>
        <td height="30" width="200">
            <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
        </td>
        <td style="text-align: center;font-weight: bold;">'.iconv("TIS-620","UTF-8",'Aging Report').' '.date("M Y").'</td>
        <td width="200"> </td>
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
    </table>
';
		
$header = iconv("TIS-620","UTF-8",$header);
$footer = iconv("TIS-620","UTF-8",$footer);
$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header,'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer,'E');
$column_name=array_keys($range);
$group_col=count($column_name);

# head_table is header of table
$head_table='	
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
<table width="100%" cellspacing="0" style="border:1px solid #666666;font-size:10px;font-family: arial;" class="tborder">
<thead>
    <tr style="border:1px solid #333333;">
        <th width="40" height="35">No.</th>
        <th width="100">'.iconv("TIS-620","UTF-8",_lang('product_code')).'</th>
        <th>'.iconv("TIS-620","UTF-8",_lang('product_name')).'</th>
        <!--<th width="7%">'._lang('product_status').'</th>
        <th width="7%">'._lang('lot').'</th>
        <th width="9%">'._lang('serial').'</th>
        <th width="5%">'._lang('warehouse').'</th>
        <th width="5%">'._lang('zone').'</th>
        <th width="10%">'._lang('location').'</th>-->
';

$sum_cols=array();
$ja=1;
foreach($column_name as $col):
    if(!array_key_exists($ja, $sum_cols)):
        $sum_cols[$ja]=0;
    endif;

    $head_table.='<th width="70">'.$col.' '.$search_by.'</th>';
    $ja++;
endforeach;

$head_table.='
    <th width="70">Total</th>
</tr>
</thead>
<tbody>';
		
$page='';

########## start loop show page ########
$count_row=count($data);
 
$page.=$head_table;	

$sum = array();
                                
foreach($data as $key => $row):  
    $html_data = "";
    $html_data.='<tr>';
    $html_data.='<td align="center">'.($index+1).'</td>';
    $html_data.='<td align="center">'.$row->Product_Code.'</td>';
    $html_data.='<td>'.iconv("TIS-620", "UTF-8", $row->Product_NameEN).'</td>';
                                
    $name='counts';
    $sum_in_row = 0;
    
    for($j=1;$j<=$group_col;$j++):
        $pointer = $name.'_'.$j; // Add BY Akkarapol, 24/10/2013,เซ็ตตัวแปรเป็นชื่อของ Pointer เพื่อเปลี่ยนการเรียกใช้ช่องของ Pointer จาก $row["count_".$j] เป็น $row->$pointer เพราะมันเป็น Var แบบ Pointer ไม่ใช่แบบ Array
        $sum_in_row = $sum_in_row+$row->$pointer;  
        
        $value_cols=$row->$pointer!=0?set_number_format($row->$pointer):''; //ADD BY POR 2014-03-19 เพิ่มให้ตรวจสอบว่า ถ้ามีค่าเท่ากับ 0 ไม่ต้องแสดงค่า
        $html_data.='<td style="text-align:right;">'.$value_cols.'</td>';	// Add By Akkarapol, 24/10/2013,   เปลี่ยนการเรียกใช้ช่องของ Pointer จาก $row["count_".$j] เป็น $row->$pointer เพราะมันเป็น Var แบบ Pointer ไม่ใช่แบบ Array		

        $sum[$pointer] = $sum[$pointer]+$row->$pointer;
    endfor;
    
    $html_data.='<td style="text-align:right;">'.set_number_format($sum_in_row).'</td>';

    $html_data.='</tr>';
    
    if($sum_in_row !=0):
        $page .=$html_data;
        $index++;
    endif;
    		
endforeach; // close loop product code
		
if($index!=0):
    $page.='<tr>';
    $page.='<th align="center" colspan="3">Total</th>';
    
    $total_sum = 0;
    foreach($sum as $key_s => $s):
        $page.='<th style="text-align:right;">'.set_number_format($s).'</th>';
        $total_sum = $total_sum + $s;
    endforeach;  
    
    $page.='<th style="text-align:right;">'.set_number_format($total_sum).'</th>';
    $page.='</tr>';

   $page.='
        </tbody>
    </table>';
endif;	
        
$this->load->helper('file');
$stylesheet =  read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet,1);
// $page = iconv("TIS-620","UTF-8",$page);
$mpdf->WriteHTML($page);
           
// $mpdf->Output($strSaveName,'I');	//file name	

    $filename = 'Aging-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
    $strSaveName = $settings['uploads']['upload_path'] . $filename;
    $tmp = $mpdf->Output($strSaveName, 'F'); 
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");
    readfile($strSaveName); 	
?>