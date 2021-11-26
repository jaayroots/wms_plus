<?php
date_default_timezone_set('Asia/Bangkok') ;
$settings = native_session::retrieve();
$mpdf=new mPDF('th',array(228.60,279.40),'11','',6.35,6.35,0,0,0,0); 
$page='';
$count_row=count($data);
$index_tag = 1;
$url_logo = './css/images/';
foreach($data as $key => $row): 
    $p = explode("-",$row['location']);
    $s = substr($p[0] , 0 , 1) . ltrim(substr($p[0] , 1) , 0);
    $m = ltrim($p[1] , 0);
    $l = ltrim($p[2] , 0);
 
    $html_data = "";
    $html_data .= '<br style=" line-height: 6.35mm;">';
    $html_data .='<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="1" style="font-size:4.5mm; font-family: arial;"> 
                             <tr>
                                <td >
                                    <table width="97%"  border="0" bordercolor="#ff0000" cellpadding="0" cellspacing="0" style=" height:25mm; border:0px solid #666666;" >
                                            <tr style="height:60%; ">
                                                <td style="width:78%; font-size:7mm; font-weight: bold;  text-align: center; vertical-align: top;"> STOCK COUNT TAG </td>
                                                 <td style="width:15%; text-align: right;" ><img src="'.$url_logo.'logo_j_group.png" style="width:35mm;height:10mm;"></td>
                                              
                                                 <td style="width:15%; text-align: right;" ><img src="'.$url_logo.'crane-worldwide.png" style="width:35mm;height:10mm;"></td>
                                            </tr>
                                            <tr>
                                                 <td  colspan="3">&nbsp;</td>
                                            </tr>
                                            <tr style="height:40%; vertical-align: top;">
                                                <td style="width:70%;">COUNT DATE :<u style="text-decoration-style: dotted;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></td>               
                                                <td  colspan="2" style="width:30%; text-align: right;">RUNNING NO.: '.$s.$m . '-' . $l .'<u style="text-decoration-style: dotted;">&nbsp;&nbsp;&nbsp;'.sprintf("%04d", $index_tag).'/'.sprintf("%04d", $count_row).'&nbsp;&nbsp;&nbsp;</u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                            </tr>
                                            <tr>
                                                 <td  colspan="3">&nbsp;</td>
                                            </tr>
                                   
                                    </table>
                                    
                                    <table width="97%" height="100%" border="0" bordercolor="#ff0000" cellpadding="0" cellspacing="0" style="table-layout: auto; border:0px solid #666666;" >
                                        <tr>
                                            <td>&nbsp;
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <table width="97%" height="100%" border="1" bordercolor="#ff0000" cellpadding="0" cellspacing="0" style="table-layout: fixed; border:0px solid #666666;" >
                                                <tr height="50%" style="text-align: center;">
                                                    <td width="13%" style="text-align: center;">LOCATION</td>
                                                    <td width="15%" style="text-align: center;">SKU</td>
                                                    <td width="42%" style="text-align: center;">DESCRIPTION</td>
                                                    <td width="10%" style="text-align: center;">COUNT 1</td>
                                                    <td width="10%" style="text-align: center;">COUNT 2</td>
                                                    <td width="10%" style="text-align: center;">UOM</td>
                                                </tr>
                                                <tr height="50%" style="text-align: center;">
                                                    <td style="text-align: center;">'.$row['location'].'</td>
                                                    <td style="text-align: center;">'.$row['sku'].'</td>
                                                    <td style="">'.substr($row['b_description'],0,22).'</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td style="text-align: center;">'. iconv("TIS-620", "UTF-8", $row['uom']).'</td>
                                                </tr>
                                    </table>
                                    
                                    <table width="97%" height="100%" border="0" bordercolor="#ff0000" cellpadding="0" cellspacing="0" style="table-layout: auto; border:0px solid #666666;" >
                                        <tr>
                                            <td>&nbsp;
                                            </td>
                                        </tr>
                                    </table>
                                    
                                 
                                    <table width="97%" height="100%" border="0" bordercolor="#ff0000" cellpadding="0" cellspacing="0" style="table-layout: auto; border:0px solid #666666; background-color: ffffff;" >
					<tr><td colspan="5">&nbsp;</td></tr>
                                        <tr height="33.33%" style="text-align: center;">
                                                    <td width="15%" style="text-align: right;">CHECK BY :</td>
                                                    <td width="20%" style="text-align: left;">
                                                        <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>
                                                    </td>
                                                    <td width="30%" style="text-align: center;">(JWD)</td>
                                                    <td width="15%" style="text-align: right;">DATE :</td>
                                                    <td width="20%" style="text-align: left;">
                                                        <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>                              
                                                    </td>
                                        </tr>
                                        <tr><td colspan="5">&nbsp;</td></tr>
					<tr><td colspan="5">&nbsp;</td></tr>
                                        <tr height="33.33%" style="text-align: center;">
                                                    <td  style="text-align: right;">VERIFIED BY :</td>
                                                    <td  style="text-align: left;">
                                                        <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>
                                                    </td>
                                                    <td width="30%" style="text-align: center;">(CUSTOMER)</td>
                                                    <td style="text-align: right;">DATE :</td>
                                                    <td  style="text-align: left;">
                                                        <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>
                                                    </td>
                                        </tr>
                                        <tr><td colspan="5">&nbsp;</td></tr>
					<tr><td colspan="5">&nbsp;</td></tr>
                                        <tr height="33.33%" style="text-align: center;">
                                                    <td  style="text-align: right;">VERIFIED BY :</td>
                                                    <td  style="text-align: left;">
                                                        <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>
                                                    </td>
                                                    <td width="30%" style="text-align: center;">(AUDITOR)</td>
                                                    <td style="text-align: right;">DATE :</td>
                                                    <td  style="text-align: left;">
                                                        <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>
                                                    </td>
                                        </tr>
										<tr><td colspan="5">&nbsp;</td></tr>
										<tr><td colspan="5">&nbsp;</td></tr>
										<tr><td colspan="5">&nbsp;</td></tr>
                                    </table>
                                </td>
                            </tr>
                    </table>        
                            ';              
    $html_data .= '<br style=" line-height: 6.35mm;">';
//    $html_data .= '<br style=" line-height: 6.35mm;">';
//    $html_data .= '<br style=" line-height: 6.35mm;">';
//    $html_data .= '<br style=" line-height: 6.35mm;">';

    $page.=$html_data;	
     
    $mpdf->WriteHTML($page);
    $page = '';
    $index_tag++;
endforeach; 
$this->load->helper('file');
$stylesheet =  read_file('../libraries/pdf/style.css');
$filename = $new_filename;//'Tag-Count-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$tmp = $mpdf->Output($strSaveName, 'F'); 
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");
readfile($strSaveName); 	
?>
