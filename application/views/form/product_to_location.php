<script lang="javascript">
var allVals = new Array();
     $(document).ready(function(){
		//$('#category').hide();
		$('#product_status').val('');
		$('#zone').val('');
		$('#warehouse').val('');
		initProductTable();
		$('#warehouse').change(function(){
			var warehouse=$(this).val();
			//alert('warehouse='+warehouse);
			$('#product').html('Please select Warehouse, Zone and Product Status');
			$('#defDataTable2').dataTable().fnClearTable();
			initProductTable();
			if(warehouse!=""){
				$.get("<?php echo site_url();?>/putaway/getZoneSelect?w="+warehouse,function(data){
					$('div#zone').html(data);
				});
			}
			$('select#category').val('');
			getProduct($('select#warehouse').val(),'','',$('select#category').val(),$('#product_status').val());

		});

		$('#zone').change(function(){
			//alert(' zone change ');
			
			var zone=$('select#zone').val();
			var w=$('#warehouse').val();
			//$('#product').html('Please select Warehouse, Zone and Product Status');
			//$('#defDataTable2').datatable.rows.clear();
			$('#product').html('Please select Warehouse, Zone and Product Status');
			$('#defDataTable2').dataTable().fnClearTable();
			initProductTable();
			//$("#defDataTable2 tbody tr").remove();
			//alert('zone='+zone);
			if(zone!=""){
				//alert('zone='+zone);
				$.get("<?php echo site_url();?>/putaway/getZoneCategory?w="+w+"&zone="+zone,function(data){
					if(data==0){
						alert("Unavailable Location in this Zone");
						$('select#zone').val('');
						zone='';
					}
					else{
						$('div#zone_cate').html(data);
						$('#zone_cate').show();
						getProduct($('#warehouse').val(),zone,$('select#zone_cate').val(),$('select#category').val(),$('select#product_status').val());	
					}
					
				});
				
			}
			$('#product_status').show();
			//alert(zone);
			
			//$('#product_status').show();
		});

		$('#zone_cate').change(function(){
			//$('#product_status').show();
			//alert('zone cate change');
			$('#defDataTable2').dataTable().fnClearTable();
			$('#product').html('Please select Warehouse, Zone and Product Status');
			initProductTable();
			var zone=$('select#zone').val();
			var w=$('select#warehouse').val();
			var zone_cate=$('select#zone_cate').val();
			if(w!="" && zone!="" && zone_cate!=""){
				
				$.get("<?php echo site_url();?>/putaway/check_sub_zone?w="+w+"&zone="+zone+"&zone_cate="+zone_cate,function(data){
					if(data==0){
						alert("Unavailable Location in this Zone");
						$('select#zone_cate').val('');
						zone_cate='';

					}
					/*
					else{
						$('div#zone_cate').html(data);
						$('#zone_cate').show();
					}
					*/
				});
			}
			getProduct($('select#warehouse').val(),$('select#zone').val(),zone_cate,$('select#category').val(),$('select#product_status').val());
		});

		$('#product_status').change(function(){
			//$('#category').show();
			var status=$(this).val();
			var zone=$('select#zone').val();
			var warehouse=$('select#warehouse').val();
			var sub_zone=$('select#zone_cate').val();
			var cate=$('select#category').val();

			getProduct(warehouse,zone,sub_zone,cate,status);
			
		});
		$('#category').change(function(){
					
			getProduct($('select#warehouse').val(),$('select#zone').val(),$('select#zone_cate').val(),$(this).val(),$('select#product_status').val());
		});

		function getProduct(warehouse,zone,zone_cate,cate,status){
			//alert('w='+warehouse+'/z='+zone+'/c='+zone_cate+'/s='+status);
			
			if(warehouse!="" && zone!=""  && status!=""){
				   $('#product').html('<img src="<?php echo base_url()?>images/ajax-loader.gif" />');
					
					$.get("<?php echo site_url();?>/putaway/getProductList?warehouse="+warehouse+"&zone="+zone+"&zone_cate="+zone_cate+"&status="+status+"&cate="+cate,function(data){
						
						//$('#product').html(data);
						//alert(i+" "+data.code);
						var option='';
                                                
                                //            --#ISSUE 1756
                                //            --#DATE:2012-08-28
                                //            --#BY:KIK
                                //            --#ปัญหา:เมื่อเปลี่ยน Product Status ข้อมูล product ใน dataTable ขึ้นต่อกันไปเรื่อยๆ ไม่เคลียร์ของเก่า
                                //            --#สาเหตุ:ของเดิมใช้ ฟังก์ชั่น fnAddData การทำงานคือ เพิ่ม data ต่อเข้าไปเรื่อยๆ ใน table ทำให้แสดงข้อมูลทั้งเก่าและใหม่พร้อมๆ กัน
                                //            --#วิธีการแก้:ใช้ฟังก์ชั่น fnClearTable เพื่อ เคลียร์ data ใหม่ทุกครั้งก่อนที่จะ addData
                                //            -- START New Code ISSUE 1756
                                                $('#defDataTable2').dataTable().fnClearTable();	//เพิ่มฟังก์ชั่น fnClearTable เพื่อเคลียร์ข้อมูลทั้งหมดก่อนใส่ new data
                                 //            -- END New Code ISSUE 1756
                                                
                                                
						$.each(data,function(i,item){
							option='<input ID=chkBoxVal type=checkbox name=chkBoxVal[] value="'+item.id+'" id=chkBoxVal onClick="getCheckValue(this)">';
							$('#defDataTable2').dataTable().fnAddData([
								option
								,item.code
								,item.name
							]);
						});
						
						initProductTable();
						
						
				},"json");
			}
			else{
				$('#product').html('Please select Warehouse, Zone and Product Status');
			}
			
		}

		$('#select_all').click(function(){	
			var cdata=$('#defDataTable2').dataTable();
			allVals = new Array();
			$(cdata.fnGetNodes()).find(':checkbox').each(function () {
				$this = $(this);
				$this.attr('checked', 'checked');
				allVals.push($this.val());
			});
			//alert('select all '+allVals);
		});

		$('#deselect_all').click(function(){					
			var selected = new Array();
			var cdata=$('#defDataTable2').dataTable();
			$(cdata.fnGetNodes()).find(':checkbox').each(function () {
				$this = $(this);
				$this.attr('checked', false);
				selected.push($this.val());
				allVals.pop($this.val());
			});
			allVals = new Array();
		});
        
		$('#save').click(function(){
			var statusisValidateForm = validateForm();
			//alert(' status = '+statusisValidateForm);
			//alert(' val = '+allVals);
			var id=allVals;
			if(statusisValidateForm === true  ){
				//return true;
				var rowData = $('#defDataTable2').dataTable().fnGetData();
				var num_row = rowData.length;
				if(num_row<=0){
					alert("Please Select Product Order Detail");
					return false;
				}

				var data_form = $("#formPtoL").serialize();
				var oTable=$('#defDataTable2').dataTable();
				var sData = $('input', oTable.fnGetNodes()).serialize();
				
				//alert(' data = '+sData);
				if(sData==""){
					alert("Please Select Product Order Detail");
					return false;
				}
				var message = "";	
				 $('#save').attr('disabled','disabled');
				 $.post("<?php echo site_url(); ?>"+"/putaway/saveProductLocation",data_form+'&'+sData,function(data){ 
						switch (data.status){
							case 'C001':  message = "Save Product To Location Complete";     break;
							case 'C002':  message = "This zone not have location";  break;
							case 'C000':  message = "Please select Product";  break;

						}
						alert(message);
						url = "<?php echo site_url(); ?>/"+data.url;
						redirect(url);
				 },"json"); 
				 
				return false;
			}
			else{
				alert("Please Check Your Require Information (Red label).");
				return false;
			}  
			
		});
		/*
		$('#formPtoL').submit(function(){
			var statusisValidateForm = validateForm();
			alert(' status = '+statusisValidateForm);
			
			
			if(statusisValidateForm === true  ){
				return true;
				//return false;
			}
			else{
				alert("Please Check Your Require Information (Red label).");
				return false;
			}  
			
		});
		*/
		
	 });

	 function initProductTable(){
		var oTable=$('#defDataTable2').dataTable({
             "bJQueryUI": true,
			 "bSort": false,
			 "bRetrieve": true,
			 "bDestroy": true,
			 "oLanguage": {
				 "sLoadingRecords": "Please wait - loading..."
				 ,"sProcessing": "<img src=\"<?php echo base_url()?>images/ajax-loader.gif\" />"
			 },
			 "sPaginationType": "full_numbers",
			 "sDom": '<"H"lfr>t<"F"ip>'
		});
		$('#product').html('');
	 }

	 function getCheckValue(obj) {
			//alert(' check each input ');
			var isChecked = $(obj).attr("checked");
			if(isChecked){
				allVals.push($(obj).val());
			}else{
				allVals.pop($(obj).val());
			}
			//alert(' check each input '+allVals);
	}

function validateForm(){
	 $("form").validate({
		rules: {
			warehouse			: {required: true}
			,zone			: {required: true}
			,product_status		: {required: true }
			//,doc_refer_ext		: {required: true }
			//,est_receive_date	: {required: true }
		}
	 });
	 return $("form").valid();
}

function scancel(){
		if(confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")){
			url = "<?php echo site_url(); ?>/putaway";
			redirect(url);
		}
	}
</script>
<FORM ACTION="" METHOD="POST" id="formPtoL">
<div class="well">

<fieldset>
	<legend>&nbsp;Add Product to Location&nbsp;</legend>
<TABLE width="100%">
  <TR>
	<TD width="200" valign="top">Warehouse</TD>
	<TD>
	<SELECT NAME="warehouse" ID="warehouse" class="required">
		<OPTION VALUE="">Please select Warehouse</OPTION>
		<?php
			foreach($data['selectWarehouse'] as $warehouse){
		?>
		<OPTION VALUE="<?php echo $warehouse['id'];?>"><?php echo $warehouse['name'];?></OPTION>
		<?php
			}
		?>
	</SELECT> *
	</TD>
	<TD valign="top">Zone</TD>
	<TD><div id="zone" >
	<SELECT NAME="zone" ID="zone"  class="required">
		<OPTION VALUE="">Please select Warehouse</OPTION>	
	</SELECT> *
		</div>
	</TD>	
  </TR>
  <?php
	//$value=$data['values'];
	//print_r($value);
  ?>
 
  <TR id="sub_zone">
	<TD valign="top">Product Status</TD>
	<TD><?php echo form_dropdown('product_status', $data['selectProductStatus'], '1', 'id=product_status  class="required"') ?>  *
	</TD>
	<TD valign="top">Zone Category</TD>
	<TD>
		<div id="zone_cate" >
		<SELECT NAME="zone_cate" ID="zone_cate">
			<OPTION VALUE="">Please select Zone Category</OPTION>	
		</SELECT>
		</div>	
	</TD>	
  </TR>
   
  <TR>
	<TD valign="top">Product Category</TD>
	<TD>
		<?php echo form_dropdown('category', $data['selectCategory'], '1', 'id=category') ?>  
	</TD>	
	<TD></TD>
	<TD></TD>
  </TR>
</TABLE>
</fieldset>
<fieldset>
	<legend>&nbsp;Select Product&nbsp;</legend>
	<div id="product" style="margin:5px auto;width:98%;text-align:center;">
			Please select Warehouse, Zone and Product Status
	</div>
	<div style="margin:10px auto;padding:0px 5px; width:98%;">
	<input type="button" name="select_all" id="select_all" value="Select All" class="btn red" data-toggle="modal" role="button" />
	<input type="button" name="deselect_all" id="deselect_all" value="Unselect All" class="btn red"" data-toggle="modal" role="button" />
	</div>
	<div style="margin:10px auto;padding:0px 5px; width:98%;">
	<table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info" >
		<thead>
			<tr>
				<th width="100">Select All</th>
				<th width="150"><?php echo _lang('product_code'); ?></th>
				<th><?php echo _lang('product_name'); ?></th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
	</div>
	
</fieldset>
<input type="hidden" name="edit_id" value="" >
<input type="hidden" name="do_action" value="<?php echo $data['action'];?>" >


</div>
<div align="center" style="text-align:center;margin:2px auto;">
<INPUT TYPE="button" class="button dark_blue"	VALUE="BACK" id="cancel" name="cancel" ONCLICK="scancel();" />
<INPUT TYPE="button" class="button dark_blue"	VALUE="SAVE"  id="save">
</div>
</FORM>
<?php //p($data); ?>