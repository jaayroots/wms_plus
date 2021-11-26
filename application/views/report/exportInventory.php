<?php
set_time_limit(0);
date_default_timezone_set('Asia/Bangkok') ;
$settings = native_session::retrieve();
$mpdf=new mPDF('th','A4-L','11','',5,5,30,25,5,5); 
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
	$header = '

		<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
			<tr>
				<td height="30" width="200">
                                    <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '"> 
                                </td>
                                <td style="text-align: center;font-weight: bold;">INVENTORY REPORT</td>
				<td width="200"> </td>
			</tr>
			<tr>
				<td align="center" height="10"></td>
			</tr>
		</table>

		<table width="100%" style="font-size: 14px;font-family: arial;">
			<tr>
				<td width="15%" height="25" align="right">ผุ้เช่าคลัง : </td>
				<td width="50%">'.$Company_NameEN.'</td>
				<td width="15%" align="right">วันที่ : </td><td>'.$search['as_date'].'</td>
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
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLHeader($header,'E');
		$mpdf->SetHTMLFooter($footer);
		$mpdf->SetHTMLFooter($footer,'E');

		$page='';
		$count_row=count($datas);

		# calculate page number 
		# max_line is number of table row in 1 page

        ########## start loop show page ########
		# head_table is header of table
		$head_table='	
		<style>
			.tborder{
				border:1px solid #333333;
			}

			.tborder td,
			.tborder th{
				font-size:13px;
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
					<th rowspan="2" width="3%" height="30">'._lang('no').'</th>
					<th rowspan="2" width="7%">'._lang('product_code').'</th>
					<th rowspan="2" width="15%">'._lang('product_name').'</th>
					<th rowspan="2" width="7%">'._lang('lot').'</th>
					<th rowspan="2" width="7%">'._lang('serial').'</th>
					<th rowspan="2" width="7%">'._lang('product_mfd').'</th>
					<th rowspan="2" width="7%">'._lang('product_exp').'</th>';
                                            
                                        if($this->config->item('build_pallet')):
                                            $head_table .= '<th rowspan="2" width="7%">'._lang('pallet_code').'</th>';
                                        endif;

                                        $head_table .= '<th rowspan="2" width="7%">'._lang('total').'</th>
			';

			foreach ($ranges as $range){
				$head_table.= '<th width="5%"  colspan="2">'.$range.'</th>';
			}
				$head_table.='</tr>';
				$head_table.='<tr bgcolor="#8B8B7A">';

				foreach ($ranges as $range){
					
					$head_table.= '<th bgcolor="#EEEED1"><b>BL </b></th><th bgcolor="#CDCDB4"><b>EST</b><//th>';
				}

				$head_table.='<tr>';
		
		$head_table.='
			</thead>
			<tbody>	';

		$page.=$head_table;	

		  if(!empty($datas)){

		  $sum_balance=array();
                  $sum_estimate=array();
                  
                  $sum_balance_all=0;
                  $sum_estimate_all=0;
		  $i=1;
				foreach($datas as $key=>$data){ 
				
					$page.="<tr>";
					$page.='<td height="23" valign="top" class="tdData" style="text-align:center">'.$i.'</td>';
					$page.='<td class="tdData" style="text-align:center">'.$data->Product_Code.'</td>';
					$page.='<td class="tdData" style="text-align:left">'.iconv("TIS-620","UTF-8",$data->Product_NameEN).'</td>';
					$page.='<td class="tdData" style="text-align:center">'.iconv("TIS-620","UTF-8",$data->Product_Lot).'&nbsp;</td>';
					$page.='<td class="tdData" style="text-align:center">'.iconv("TIS-620","UTF-8",$data->Product_Serial).'&nbsp;</td>';
					$page.='<td class="tdData" style="text-align:center">'.$data->Product_Mfd.'&nbsp;</td>';
					$page.='<td class="tdData" style="text-align:center">'.$data->Product_Exp.'&nbsp;</td>';
                                            
                                        if($this->config->item('build_pallet')):
                                            $page.='<td class="tdData" style="text-align:center">'.$data->Pallet_Code.'&nbsp;</td>';
                                        endif;
                                        
					$page.='<td class="tdData" style="text-align:right">'.set_number_format($data->totalbal).'&nbsp;</td>';
					$n=1;
					foreach($ranges as $key_index => $range){
						$balance='counts_'.$n;
                                                $estimate='estimate_'.$n;

						$sum_balance[$n]+=$data->{$balance};
                                                $sum_estimate[$n]+=$data->{$estimate};
                                                $sum_balance_all+=$data->{$balance}; //ADD BY POR 2013-11-12 เพิ่ม BALANCE ทุก product
                                                $sum_estimate_all+=$data->{$estimate}; //ADD BY POR 2013-11-12 เพิ่ม ESTIMATE ทุก product
                                                
                                                $num_balance=($data->{$balance}!=0)?set_number_format($data->{$balance}):'';
                                                $num_estimate=($data->{$estimate}!=0)?set_number_format($data->{$estimate}):'';
                                                
						$page.='<td class="tdData" style="text-align:right">'.$num_balance.'</td>';
						$page.='<td class="tdData" style="text-align:right">'.$num_estimate.'</td>';
						$n++;
					}

					$page.="</tr>";
					
					$i++;
				}
		  }
		
                                        $colspan = 7;
                                        if($this->config->item('build_pallet')):
                                            $colspan = 8;
                                        endif;
		$page.='
						<tr bgcolor="#F5F5F5">
							<td colspan="'.$colspan.'" class="tdData"><b>Total Balance</b></td>
                                                        <td colspan="1" align="right" class="tdData"><b>'.set_number_format($sum_balance_all).'</b></td>';
							
							foreach($sum_balance as $sb){
								
								$page.='<td colspan="2" class="tdData"  style="text-align:right"><b>'.set_number_format($sb).'</b></td>';
							}
							
						$page.='</tr>

						<tr bgcolor="#E5E5E5">
							<td colspan="'.$colspan.'" class="tdData"><b>Total Estimate</b></td>
                                                        <td colspan="1" align="right" class="tdData"><b>'.set_number_format($sum_estimate_all).'</b></td>';
							
							foreach($sum_estimate as $se){
								
								$page.='<td colspan="2" class="tdData"  style="text-align:right"><b>'.set_number_format($se).'</b></td>';
								
							}

				$page.='</tr>
					';
                    $page.='</tbody>';
			
		$page.='</table>';
                        
		$this->load->helper('file');
		$stylesheet =  read_file('../libraries/pdf/style.css');
		$mpdf->WriteHTML($stylesheet,1);
		$mpdf->WriteHTML($page);

                $filename = 'Inventory-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
                $strSaveName = $settings['uploads']['upload_path'] . $filename;
                $tmp = $mpdf->Output($strSaveName, 'F'); 
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=\"" . $filename . "\"");
                readfile($strSaveName);               
//                
?>