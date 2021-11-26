<?php
?>

<script>
    $(document).ready(function() {
        $('#tbreport').dataTable({"bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            "sPaginationType": "full_numbers"});
	});

	function exportFile(file_type){
		if(file_type=='EXCEL'){
			$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/shelf_life_report/export_shelf_life_excel")
		}else{
			$("#form_report").attr('action',"<?php echo site_url(); ?>"+"/shelf_life_report/export_shelf_life_pdf")
		}
		$("#form_report").submit();
  }
</script>
<?php // p($data) ; echo count($data);?>
<form  method="post" target="_blank" id="form_report">
<input type="hidden" name="renter_id" value="<?php echo $search['renter_id'];?>" />
<input type="hidden" name="warehouse_id" value="<?php echo $search['warehouse_id'];?>" />
<input type="hidden" name="category_id" value="<?php echo $search['category_id'];?>" />
<input type="hidden" name="product_id" value="<?php echo $search['product_id'];?>" />
<input type="hidden" name="as_date" value="<?php echo $search['as_date'];?>" />
<table id="tbreport" aria-describedby="defDataTable_info">
	<thead>
		<tr>
			<th rowspan="2" class="border-top"><?php echo _lang('no'); ?></th>
			<th rowspan="2" width="100" class="border-top"><?php echo _lang('product_code'); ?></th>
			<th rowspan="2" width="200" class="border-top"><?php echo _lang('product_name'); ?></th>
			<th colspan="7" class="border-top"><?php echo _lang('shelf_life'); ?></th>
			<th rowspan="2" class="border-top"><?php echo _lang('total'); ?></th>
		</tr>
		<tr>
						<th>3M</th>
                        <th>6M</th>
                        <th>9M</th>
                        <th>1Y</th>
                        <th>1.5Y</th>
                        <th>2Y</th>
                        <th>>2Y</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$i=1;
		$sum_cols=array();
		$sum_all=0;
                //Product_Code
                //if(count($data)==1){  COMMENT BY POR 2013-10-10 เนื่องจากเรามีการนำ query ในส่วน Summary ออก
                if(count($data)==0){
                ?>
                    <div style="width:100%;">No Data Available.</div>
                <?php
                } else {
                //BY POR 2013-10-08 กำหนดตัวแปรเบื้องต้นสำหรับผลรวม
                //START
                    $sum_col1=0;
                    $sum_col2=0;
                    $sum_col3=0;
                    $sum_col4=0;
                    $sum_col5=0;
                    $sum_col6=0;
                    $sum_col7=0;
                    $sum_col8=0;
                //END
                foreach($data as $cols){
                    $cols->total = $cols->threeMonth+$cols->sixMonth+$cols->nineMonth+$cols->oneYear+$cols->oneHalfYear+$cols->twoYear+$cols->moreTwoYear;
                    ?>
                    <tr>
                            <!--<td ><?php if($cols->Product_NameEN=="Total"){echo "Summary";} else {echo $i;}?></td>-->
                            <td><?php echo $i;?></td>
                            <td ><?php echo $cols->Product_Code;?></td>
                            <!-- <td style="text-align: left;"><?php echo $this->conv->tis620_to_utf8($cols->Product_NameEN) ?></td> -->
                            <td style="text-align: left;"><?php echo $cols->Product_NameEN ?></td>
                            <td style="text-align:right;"><?php echo number_format((int) $cols->threeMonth) ?></td>
                            <td style="text-align:right;"><?php echo number_format((int) $cols->sixMonth) ?></td>
                            <td style="text-align:right;"><?php echo number_format((int) $cols->nineMonth) ?></td>
                            <td style="text-align:right;"><?php echo number_format((int) $cols->oneYear) ?></td>
                            <td style="text-align:right;"><?php echo number_format((int) $cols->oneHalfYear) ?></td>
                            <td style="text-align:right;"><?php echo number_format((int) $cols->twoYear) ?></td>
                            <td style="text-align:right;"><?php echo number_format((int) $cols->moreTwoYear) ?></td>
                            <td style="text-align:right;"><?php echo number_format((int) $cols->total) ?></td>
                    </tr>
                    <?php
                        //BY POR 2013-10-08 รวมของแต่ละเงื่อนไข
                        //START
                        $sum_col1+=$cols->threeMonth;
                        $sum_col2+=$cols->sixMonth;
                        $sum_col3+=$cols->nineMonth;
                        $sum_col4+=$cols->oneYear;
                        $sum_col5+=$cols->oneHalfYear;
                        $sum_col6+=$cols->twoYear;
                        $sum_col7+=$cols->moreTwoYear;
                        $sum_col8+=$cols->total;
                        //END
                        $i++;
                }
                ?>
                <!-- BY POR 2013-10-08 เพิ่มเติมผลรวมต่อท้าย START-->
                <tfoot>
                    <tr>
                        <th colspan="3">Summary</th>
                        <th style="text-align:right;"><?php echo number_format((int) $sum_col1); ?></th>
                        <th style="text-align:right;"><?php echo number_format((int) $sum_col2); ?></th>
                        <th style="text-align:right;"><?php echo number_format((int) $sum_col3); ?></th>
                        <th style="text-align:right;"><?php echo number_format((int) $sum_col4); ?></th>
                        <th style="text-align:right;"><?php echo number_format((int) $sum_col5); ?></th>
                        <th style="text-align:right;"><?php echo number_format((int) $sum_col6); ?></th>
                        <th style="text-align:right;"><?php echo number_format((int) $sum_col7); ?></th>
                        <th style="text-align:right;"><?php echo number_format((int) $sum_col8); ?></th>
                    </tr>
		</tfoot>
                <!-- END -->
                <?php
                }
                ?>
		</tbody>
                
</table>
<!--COMMENT BY POR 2013-11-05 ยกเลิกการใช้ปุ่มแสดง report หน้านี้ แต่ไป ให้ไปแสดงใน workflow_template แทน
<div align="center" style="margin-top:10px;">
	<input type="button" value="Export To PDF" class="button orange" onClick="exportFile('PDF')"  />
	 &emsp;&emsp;
	<input type="button" value="Export To Excel" class="button orange" onClick="exportFile('EXCEL')" />
</div>
-->
</form>