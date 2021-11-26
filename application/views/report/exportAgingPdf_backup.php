<?php
date_default_timezone_set('Asia/Bangkok') ;
$mpdf=new mPDF('th','A4-L','11','',5,5,25,25,5,5); 
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
	$header = '
		<table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 16px; color: #000000;">
		
		<tr>
			<td align="center" height="30">'.$this->conv->tis620_to_utf8('Aging Report').' '.date("M Y").'</td>
		</tr>
		</table>
				
		';
		

		$footer = '<table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 9pt; color: #666666;">
					<tr>
						<td width="50%" align="left">Date:'.date("d M Y").' Time:'.date("H:i:s").'</td>
						<td align="right">{PAGENO}/{nb}</td>
					</tr>
					</table>';
		
		$header = iconv("TIS-620","UTF-8",$header);
		$footer = iconv("TIS-620","UTF-8",$footer);
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLHeader($header,'E');
		$mpdf->SetHTMLFooter($footer);
		$mpdf->SetHTMLFooter($footer,'E');
		
		# head_table is header of table
		$head_table='	
		  <style>
			.tborder{
				border:1px solid #333333;
			}

			.tborder td,
			.tborder th{
				border-top:1px solid #333333;
				border-left:1px solid #333333;
				/*border-bottom:1px solid #333333;*/
				font-size:13px;
			}
		  </style>
		  <table width="100%" cellspacing="0" border-style="1px solid #666666;font-size:12px;" class="tborder">
			<thead>
				<tr style="border:1px solid #333333;" bgcolor="#CCCCCC">
					<th  class="border-top">No</th>
					<th  width="100" class="border-top">Product Code</th>
					<th  width="200" class="border-top">Product Name</th>';
		$column_name=array_keys($range);
		$group_col=count($column_name);
		//p($group_col);
			$sum_cols=array();
				$ja=1;
			  foreach($column_name as $col){
				  if(!array_key_exists($ja, $sum_cols)){
						$sum_cols[$ja]=0;
					}
			
				$head_table.='<th class="line-bottom">'.$col.' '.$search_by.'</th>';
			 
					$ja++;
			  }
			
			$head_table.='<th class="border-top" >Total</th>';
			/*
		    $head_table.='
					<th width="7%" height="35">'.$this->conv->tis620_to_utf8('DATE/ วันแจ้งเข้าคลัง').'</th>
					<th width="7%">'.$this->conv->tis620_to_utf8('PO.NO').'</th>
					<th width="16%">'.$this->conv->tis620_to_utf8('Supplier').'</th>
					<th width="10%">'.$this->conv->tis620_to_utf8('Product Code').'</th>
					<th width="20%">'.$this->conv->tis620_to_utf8('Product Name').'</th>
					<th width="5%">'.$this->conv->tis620_to_utf8('Qty. PO.').'</th>
					<th width="7%">'.$this->conv->tis620_to_utf8('Arrival Date').'</th>
					<th width="7%">'.$this->conv->tis620_to_utf8('Receiving Date/Infurmed').'</th>
					<th width="5%">'.$this->conv->tis620_to_utf8('Qty. Receiv').'</th>
					<th width="10%">'.$this->conv->tis620_to_utf8('Remark').'</th>
				<tr>';
			*/
			$head_table.='</tr>
			</thead>
			<tbody>	';
		
		$page='';
		########## start loop show page ########
		$count_row=count($data);
 
		# calculate page number 
		# max_line is number of table row in 1 page
		$max_line=25;  
		$totalpage = (int) ($count_row / $max_line);
		if (($count_row % $max_line) != 0) {
				$totalpage += 1;
		}
			
		$name='counts';
		//p($data);
		for($i=1;$i<=$totalpage;$i++){
			$page.=$head_table;		
			# คำนวนหาตำแหน่งของ array ที่จะเอามาแสดงผลบรรทัดแรกในแต่ละหน้า
			# start คือตำแหน่งเริ่มต้น
			# stop คือตำแหน่งสิ้นสุดของข้อมูลที่จะแสดงในหน้านั้น ๆ
			if($i==1){
				$start=0;
				$stop=$max_line-1;
			}
			else{
				$start = $max_line * ($i - 1);
				$stop = ($max_line * $i) -1;
			}
			# เริ่มวนลูปแสดงข้อมูลแต่ละแถว
			for($j=$start;$j<=$stop;$j++){
				$sum_row=0;
				if($j<$count_row){
					$k=$j+1;
					$page.='<tr>';
					$page.='<td>'.$k.'</td>';
					$page.='<td>'.$data[$j]->Product_Code.'</td>';
					$page.='<td>'.$data[$j]->Product_NameEN.'</td>';
					for($jb=1;$jb<=$group_col;$jb++){
						$sum_row+=$data[$j]->{$name."_".$jb};
						$sum_cols[$jb]+=$data[$j]->{$name."_".$jb};
						$page.='<td  >'.$data[$j]->{$name."_".$jb}.'</td>';
					}
					$page.='<td>'.number_format($sum_row).'</td>';
					$page.='</tr>';
					$sum_all+=$sum_row;
					//$final_balance=$data[$j]['Balance_Qty'];
				}
				
			}
			$page.='<tr bgcolor="#CCCCCC">';
				$page.='<td colspan="3">Total</td>';
				foreach($sum_cols as $sc){
					$page.='<td>'.$sc.'</td>';
					//$sum_all+=$sc;
				}
			$page.='<td>'.$sum_all.'</td>';
			$page.='</tr>';
			$page.='</tbody>
			</table>';
			if($i!=$totalpage){
				$page.='<pagebreak />';
			}
			# end of show table 30 rows
			
		} // close loop page

		//echo $page;

        
		$this->load->helper('file');
		$stylesheet =  read_file('../libraries/pdf/style.css');
		$mpdf->WriteHTML($stylesheet,1);
		$page = iconv("TIS-620","UTF-8",$page);
		$mpdf->WriteHTML($page);

		$mpdf->Output($strSaveName,'I');	//file name
		
?>