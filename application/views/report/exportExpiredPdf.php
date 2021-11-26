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
                    <td style="text-align: center;font-weight: bold;">Expire Report '.date("M Y").'</td>
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
					</table>';

//		$header = iconv("TIS-620","UTF-8",$header);
//		$footer = iconv("TIS-620","UTF-8",$footer);
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLHeader($header,'E');
		$mpdf->SetHTMLFooter($footer);
		$mpdf->SetHTMLFooter($footer,'E');

                $css_td_pallet_code = ($this->config->item('build_pallet')?' .td_pallet_code{display:none;} ':' ');


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

                        '.$css_td_pallet_code.'

		  </style>
		  <table width="100%" cellspacing="0" style="border:1px solid #666666;font-size:10px;font-family: arial;" class="tborder">
			<thead>
				<tr style="border:1px solid #333333;">
                                        <th width="7%" height="35">'._lang('no').'</th>
					<th width="10%">'._lang('product_code').'</th>
					<th width="20%">'._lang('product_name').'</th>
                                        <th width="16%">'._lang('lot_serial').'</th>
					<!--
                                        <th width="7%">'._lang('lot').'</th>
					<th width="9%">'._lang('serial').'</th>
					<th width="5%">'._lang('warehouse').'</th>
					<th width="7%">'._lang('zone').'</th>
					<th width="10%">'._lang('location').'</th>-->
					<th width="10%">'._lang('expire_date').'</th>
					<th width="7%">'._lang('remain_days').'</th>
					<div class="td_pallet_code"><th width="7%">'._lang('pallet_code').'</th></div>
					<th width="7%">'._lang('qty').'</th>
				</tr>
			</thead>
			<tbody>	';

		$page='';
		########## start loop show page ########
		$count_row=count($data);

		# calculate page number
		# max_line is number of table row in 1 page
//		$max_line=25;
//
//		$num_row=0;
//		$index=0;
		$page.=$head_table;

		foreach($data as $key => $row){


			//echo ' page= '.$show_page;
//			if($index==0){
				//$page.=$head_table;
//			}
//			$index++;

//			$page.='<tr>';
//			$page.='<td colspan="10" align="center" bgcolor="#EAEAEA">'.iconv("TIS-620","UTF-8",$key).'</td>';
//			$page.='</tr>';
			//$num_row++;

			foreach($row as $column){


                                    if($column[Product_Lot] != " "){
                                       $productLot =  $column[Product_Lot];
                                    }else{
                                       $productLot = "-";
                                    }

                                    if($column[Product_Serial] != " "){
                                       $productSerial =  $column[Product_Serial];
                                    }else{
                                       $productSerial = "-";
                                    }
				$page.='<tr>';
                                $page.='<td align="center">'.($index+1).'</td>';
				$page.='<td align="center">'.$column[Product_Code].'</td>';
				$page.='<td>'.iconv("TIS-620", "UTF-8", $column[Product_NameEN]).'</td>';
                                $page.='<td align="center">'.iconv("TIS-620", "UTF-8", $productLot)."/".iconv("TIS-620", "UTF-8", $productSerial).'</td>';

//                              #defect 336 เพิ่ม column เพื่อแสดง Lot/Serial และเอา  Warehouse,Zone,Location ออกเพื่อให้แสดงผลเหมือนกับหน้าเว็บ | by kik (02-09-2013)
//				$page.='<td>'.$column[Product_Lot].'</td>';
//				$page.='<td>'.$column[Product_Serial].'</td>';
//				$page.='<td>'.$column[Warehouse].'</td>';
//				$page.='<td>'.$column[Zone].'</td>';
//				$page.='<td>'.$column[Location_Code].'</td>';
				$page.='<td align="center">'.$column[Product_Exp].'</td>';
				$page.='<td style="text-align:center;">'.$column[Remain_day].'</td>';
				$page.='<div class="td_pallet_code"><td style="text-align:center;">'.$column[Pallet_Code].'</td></div>';
				$page.='<td style="text-align:right;">'.set_number_format($column[Balance]).'</td>';
				$page.='</tr>';
				$index++;
			}

			//$cal_page=$num_row%$max_line;

//			if($index==$max_line){
//				/*
//				$page.='
//					 <tr><td colspan="10"> page </td></tr>
//					</tbody>
//				</table>';
//				*/
////				$page.='<pagebreak />';
////				$index=0;
//			}

			# end of show table 30 rows

		} // close loop product code


		if($index!=0){
		   $page.='
				</tbody>
			</table>';
		}
		//echo $page;

		$this->load->helper('file');
		$stylesheet =  read_file('../libraries/pdf/style.css');
		$mpdf->WriteHTML($stylesheet,1);
//		$page = iconv("TIS-620","UTF-8",$page);
		$mpdf->WriteHTML($page);

//		$mpdf->Output($strSaveName,'I');	//file name

                $filename = 'Expire-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
                $strSaveName = $settings['uploads']['upload_path'] . $filename;
                $tmp = $mpdf->Output($strSaveName, 'F');
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=\"" . $filename . "\"");
                readfile($strSaveName);

?>