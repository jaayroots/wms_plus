<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.datepicker.js" ?>"></script>-->
<script>
	var allVals = new Array();
    $(document).ready(function() {
		getStatus(); // get value for select list product status and pass value to status_list hidden input

        $("#est_receive_date").datepicker({
			startDate: '-0m'
		}).on('changeDate', function(ev){
			//$('#sDate1').text($('#datepicker').data('date'));
			//$('#est_receive_date').datepicker('hide');
		});

		//var oTable;
//Define button Get onClick
		$('#getBtn').click(function() {
			allVals = new Array();
            var product_code = $('#productCode').val();
			var dataSet = { post_val  : product_code } 
			
			$('#prdModalval').val(product_code);
			$.post('<?php echo site_url() . "/pre_receive/productList" ?>',dataSet,function(data){ 
					$(".modal-body").html(data);
					var oTable=$('#defDataTable').dataTable( {
						 "bJQueryUI": true,
						 "bSort"	: false,
						 "oSearch"	: {"sSearch": product_code},
						 "bRetrieve": true,
						 "bDestroy" : true,
						 "sPaginationType": "full_numbers"
					});	
					
			},"html");
            $('#mymodal').show();  // put your modal id 

        });

		//var cdate=$('#showProductTable').dataTable().fnGetData();
		$('#select_all').click(function(){	
			var cdata=$('#defDataTable').dataTable();
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
			var cdata=$('#defDataTable').dataTable();
			$(cdata.fnGetNodes()).find(':checkbox').each(function () {
				$this = $(this);
				$this.attr('checked', false);
				selected.push($this.val());
				allVals.pop($this.val());
			});
			allVals = new Array();
		});

		function getStatus(){
			$.post('<?php echo site_url();?>/pre_receive/genProductStatus',function(data){
				var tmp_data=data.split('#');
				//alert(tmp_data[0]);
				//alert(tmp_data[1]);
				$('#status_list').val(tmp_data[0]);
				$('#unit_list').val(tmp_data[1]);
				initProductTable();
			});
		}
		
		
//Define button Search onClick
        $('#search_submit').click(function() {
			var dataSet = {
					post_val  : allVals
			} 
			$.post('<?php echo site_url() . "/pre_receive/showProduct" ?>',dataSet,function(data){
						var pstatus="NORMAL";
						var qty = "";
						var lot = "";
						var serial = "";
						var remark = "";
						var mfd = "";
						var exp = "";
					$.each(data.product, function(i, item) {
						var del = "<a ONCLICK=\"removeItem(this)\" >"+'<?php echo img("css/images/icons/del.png"); ?>'+"</a>";
						$('#showProductTable').dataTable().fnAddData([
							"new"
							,item.Product_Code
							,item.Product_NameEN
							,pstatus
							,qty
							,item.Standard_Unit_Id
							,lot
							,serial
							,remark
							,mfd
							,exp
							,del]
						);
						
					});
					initProductTable();
			},"json");
            $('.modal.in').modal('hide');
			allVals = new Array();
		
        });		
		
    });

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


    $('#myModal').modal('toggle').css({
        // make width 90% of screen
        'width': function() {
            return ($(document).width() * .9) + 'px';
        },
        // center model
        'margin-left': function() {
            return -($(this).width() / 2);
        }
    });

	

	function initProductTable(){
			
			$('#showProductTable').dataTable( {
			 "bJQueryUI": true,
			 "bSort": false,
			 "bRetrieve": true,
			 "bDestroy": true,
			 "sPaginationType": "full_numbers",
			 "sDom": '<"H"lfr>t<"F"ip>'
			}).makeEditable({
				sUpdateURL: '<?php echo site_url()."/pre_receive/editDataTable";?>'
				, "aoColumns": [
                    			 null
								,null
								,null
								,{								
									indicator: 'Saving CSS Grade...',
                					tooltip: 'Click to select Status',
                					loadtext: 'loading...',
                					type: 'select',
                					onblur: 'submit',
                                    cssclass:'required',
									data: $('#status_list').val(),
                					sUpdateURL: function(value, settings){
                							return value;
                					}
								}
								,{ 
									  sSortDataType: "dom-text",
									  sType: "numeric",
									  type: 'text',
									  onblur:"submit",
									  event: 'click',
									  cssclass: "required number",
									  sUpdateURL: "<?php echo site_url() . "/pre_receive/editDataTable"; ?>"
								}
								,{								
									indicator: 'Saving CSS Grade...',
                					tooltip: 'Click to select Unit',
                					loadtext: 'loading...',
                					type: 'select',
                					onblur: 'submit',
                                    cssclass:'required',
									data: $('#unit_list').val(),
                					sUpdateURL: function(value, settings){
                							return value;
                					}
								}
								,{onblur: 'submit',cssclass:'required'}
								,{onblur: 'submit',cssclass:'required'}
								,{onblur: 'submit',type:'datepicker',cssclass:'required date'}
								,{onblur: 'submit',type:'datepicker',cssclass:'required'}
								,{onblur: 'submit'}
								]
			});
	}

	function removeItem(obj){
		var index = $(obj).closest("table tr").index();
		$('#showProductTable').dataTable().fnDeleteRow(index);
	}

	function deleteItem(obj){
		var index = $(obj).closest("table tr").index();
		var data = $('#showProductTable').dataTable().fnGetData( index );
		$('#showProductTable').dataTable().fnDeleteRow(index);
		var f = document.getElementById("form_receive");
		var prodDelItem	= document.createElement("input"); 
			prodDelItem.setAttribute('type',"hidden"); 
			prodDelItem.setAttribute('name',"prod_del_list[]"); 
			prodDelItem.setAttribute('value',data);
		    f.appendChild(prodDelItem);
	}

	function postRequestAction(module,sub_module,action_value,next_state, elm){
		curent_flow_action = $(elm).data('dialog');
		var statusisValidateForm = validateForm();
		 if(statusisValidateForm === true  ){
			var rowData = $('#showProductTable').dataTable().fnGetData();
			var num_row = rowData.length;
			if(num_row<=0){
				alert("Please Select Product Order Detail");
				return false;
			}

			for(i in rowData){
				qty = rowData[i][4];
				if(qty==""){
					alert('Please fill all Receive Qty');
					return false;
				}
				qty=parseInt(qty)
				if(qty<0){
					alert('Please fill only number');
					return false;
				}
			}
			
		/*	var cTable = $('#showProductTable').dataTable().fnGetNodes();
			var cells=[];
		    for(var i=0;i<cTable.length;i++)
			{
				// Get HTML of 3rd column (for example)
				var qty=$(cTable[i]).find("td:eq(4)").html();
				if(qty=='Click to edit'){
					cells.push($(cTable[i]).find("td:eq(4)").html()); 
				}
			}
			var all_qty=cells.length;
			if(all_qty!=0){
				alert('Please fill all Receive Qty');
				return false;
			}	
			*/
			
			if(confirm("Are you sure to action "+action_value+"?")){
				var f = document.getElementById("form_receive");
				var actionType	= document.createElement("input"); 
				actionType.setAttribute('type',"hidden"); 
				actionType.setAttribute('name',"action_type"); 
				actionType.setAttribute('value',action_value);
				f.appendChild(actionType);

				var toStateNo	= document.createElement("input"); 
				toStateNo.setAttribute('type',"hidden"); 
				toStateNo.setAttribute('name',"next_state"); 
				toStateNo.setAttribute('value',next_state);
				f.appendChild(toStateNo);

				var oTable = $('#showProductTable').dataTable().fnGetData();
				for(i in oTable){
					var prodItem	= document.createElement("input"); 
					prodItem.setAttribute('type',"hidden"); 
					prodItem.setAttribute('name',"prod_list[]"); 
					prodItem.setAttribute('value',oTable[i]);
					f.appendChild(prodItem);
				}

				var data_form = $("#form_receive").serialize();
				var message = "";


                 $.post("<?php echo site_url(); ?>"+"/"+module+"/"+sub_module,data_form,function(data){ 
						switch (data.status){
							case 'C001':  message = "Save Pre-Receive Complete";     break;
							case 'C002':  message = "Confirm Pre-Receive Complete";  break;
							case 'C003':  message = "Approve Pre-Receive Complete";  break;
							case 'E001':  message = "Save Pre-Receive Incomplete";	 break;
						}
						alert(message);
						url = "<?php echo site_url(); ?>/flow/flowPreReceiveList";
						redirect(url)
                 },"json");                   
			} // close confirm action
		}
		else{
            alert("Please Check Your Require Information (Red label).");
            return false;
        }  
	}

	function cancel(){
		if(confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")){
			url = "<?php echo site_url(); ?>/flow/flowPreReceiveList";
			redirect(url)
		}
	}

function validateForm(){
	 $("form").validate({
		rules: {
			renter_id			: {required: true}
			,shipper_id			: {required: true}
			,consignee_id		: {required: true }
			,doc_refer_ext		: {required: true }
			,est_receive_date	: {required: true }
		}
	 });
	 return $("form").valid();
	}
        
function _validateForm() {
        
        //validate engine 
            var status;
            $("form").each(function () {
                $(this).validate();
                $(this).valid();
                status = $(this).valid();
            });
            return status;            
       //end of validate engine
    }
</script>
<style>
    #myModal {
        width: 900px;	/* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -450px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
    }
</style>
<div class="well">
<form id="form_receive" method=post action="" class="">
	<input type="hidden" name="status_list" id="status_list" />
	<input type="hidden" name="unit_list" id="unit_list" />
 	<fieldset>
	<legend>&nbsp;Add Order Pre-Receive&nbsp;</legend>
		<?php 
			if(isset($flow_id)){
				echo form_hidden('flow_id', $flow_id);
			}
			if(!isset($owner_id)){$owner_id = ""; }
			if(!isset($renter_id)){$renter_id = ""; }
			if(!isset($shipper_id)){$shipper_id = ""; }
			if(!isset($consignee_id)){$consignee_id = ""; }
			if(!isset($receive_type)){$receive_type = "Normal"; }
			if(!isset($est_receive_date)){$est_receive_date = ""; }
			if(!isset($remark)){$remark = ""; }
			if(!isset($process_type)){$process_type =  $data_form['process_type']; }
			if(!isset($document_no)){$document_no = ""; }
			if(!isset($doc_refer_int)){$doc_refer_int = ""; }
			if(!isset($doc_refer_ext)){$doc_refer_ext = ""; }
			if(!isset($doc_refer_inv)){$doc_refer_inv = ""; }
			if(!isset($doc_refer_ce)){$doc_refer_ce = ""; }
			if(!isset($doc_refer_bl)){$doc_refer_bl = ""; }
			if(!isset($receive_date)){$receive_date = ""; }
			
			if((!isset($is_pending)) || ($is_pending!='Y')){
				$is_pending = false; 
			}else{
				$is_pending = true; 
				echo form_hidden('old_pending',$is_pending);
			}
		?>
		<?php echo form_hidden('process_id',$process_id); ?>
		<?php echo form_hidden('present_state',$present_state); ?>
		<?php echo form_hidden('user_id', $user_id); ?>
		<?php echo form_hidden('process_type',$process_type); ?>
		<?php echo form_hidden('owner_id',$owner_id); ?>
		
	  <table width="98%">
		  <tr>
			<td align="right">Renter : </td>
			<td align="left"><?php echo form_dropdown('renter_id', $renter_list,$renter_id,' class="required" ');  ?></td>
			<td align="right">Shipper : </td>
			<td align="left"><?php echo form_dropdown('shipper_id', $shipper_list,$shipper_id,'class="required" ');  ?></td>
			<td align="right">Consignee : </td>
			<td align="left"><?php echo form_dropdown('consignee_id', $consignee_list,$consignee_id,'class="required"');  ?></td>
		 </tr>
		  <tr>
			<td align="right">Document No. : </td>
			<td align="left"><?php echo form_input('document_no' ,$document_no,'placeholder="Auto Generate GRN" disabled class="required" style="text-transform: uppercase"'); ?></td>
			<td align="right">Document External : </td>
			<td align="left"><?php echo form_input('doc_refer_ext' ,$doc_refer_ext,'placeholder="�Ţ�����ҧ�ԧ�Ѻ" class="required" style="text-transform: uppercase"'); ?></td>
			<td align="right">Document Internal : </td>
			<td align="left"><?php echo form_input('doc_refer_int' ,$doc_refer_int,'placeholder="�Ţ������觫����Թ��� (PO)"  style="text-transform: uppercase"'); ?></td>
		  </tr>
		  <tr>
			<td align="right">Invoice No. : </td>
			<td align="left"><?php echo form_input('doc_refer_inv' ,$doc_refer_inv ,'placeholder="�����Ţ㺡ӡѺ�Թ���"  style="text-transform: uppercase"'); ?></td>
			<td align="right">Customs Entry	: </td>
			<td align="left"><?php echo form_input('doc_refer_ce' ,$doc_refer_ce ,'placeholder="�����Ţ㺢��Թ���"  style="text-transform: uppercase"'); ?></td>
			<td align="right">BL No. : </td>
			<td align="left"><?php echo form_input('doc_refer_bl' ,$doc_refer_bl,'placeholder="㺵�����Թ��ҷҧ����"  style="text-transform: uppercase"'); ?></td>
		  </tr>
		  <tr>
			<td align="right">Receive Type : </td>
			<td align="left"><?php echo form_dropdown('receive_type', $receive_list,$receive_type,''); ?></td>
			<td align="right">ASN. : </td>
			<td align="left"><?php echo form_input('est_receive_date' ,$est_receive_date ,'id="est_receive_date" placeholder="Advance Shiptment Notice" '); ?></td>
			<td align="right">Receive Date. : </td>
			<td align="left"><?php echo form_input('receive_date' ,$receive_date ,'id="receive_date" placeholder="Receive Date" disabled '); ?></td>
		  </tr>
		  <tr>
			<td align="right">Remark : </td>
			<td align="left" colspan="3">
				<TEXTAREA ID="remark" NAME="remark" ROWS="2" COLS="4" style="width:90%" placeholder="Remark..."><?php echo $remark; ?></TEXTAREA>
			</td>
			<td align="right"></td>
			<td align="left"><?php echo form_checkbox('is_pending',ACTIVE,$is_pending);?>&nbsp;Is Pending</td>
		  </tr>
		  <tr>
			<td align="right"></td>
			<td align="left"></td>
			<td align="right"></td>
			<td align="left"></td>
			<td align="right"></td>
			<td align="left"></td>
		  </tr>
		  <tr>
			<td align="right"></td>
			<td align="left"></td>
			<td align="right"></td>
			<td align="left"></td>
			<td align="right"></td>
			<td align="left"></td>
		  </tr>
	  </table>
	</fieldset>
		<input type="hidden" name="token" value="<?php echo $token?>" />
        </form>
	<br>
	<fieldset>
	<legend>&nbsp;Product Detail&nbsp;</legend>
	  <table width="98%">
		  <tr>
			<td align="right"><?php echo _lang('product_code') ?></td>
			<td align="left"><?php echo form_input("productCode" ,"" ,"id='productCode' placeholder='Search By "._lang('product_code')."' "); ?>&nbsp;<a href="#myModal" role="button" class="btn success" data-toggle="modal" id="getBtn">Get Detail</a>
			</td>
			<td align="right"></td>
			<td align="left">&nbsp;</td>
			<td align="right">&nbsp;</td>
			<td align="left">&nbsp;</td>
		  </tr>
		  <tr align="center" >
			<td align="center" colspan="6" id="showDataTable" >
				<table align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showProductTable" >
					<thead>
						<?php 
							$str_header = "";
							foreach($show_column as $column){
								$str_header .= "<td>".$column."</td>";
							}
						?>
						<tr><?php echo $str_header;?></tr>
					</thead>
					 <?php 
						if(isset($order_deatil)){
							$str_body = "";
							foreach($order_deatil as $order_column){
								$order_id = $order_column->Order_Id;
								$str_body .= "<tr>";
								$str_body .= "<td>".$order_column->Item_Id."</td>";
								$str_body .= "<td>".$order_column->Product_Code."</td>";
								$str_body .= "<td>".$order_column->Product_Name."</td>";
								$str_body .= "<td>".$order_column->Product_Status."</td>";
								$str_body .= "<td>".$order_column->Reserv_Qty."</td>";
								$str_body .= "<td>".$order_column->Unit_Id."</td>";
								$str_body .= "<td>".$order_column->Product_Lot."</td>";
								$str_body .= "<td>".$order_column->Product_Serial."</td>";
								$str_body .= "<td>".$order_column->Product_Mfd."</td>";
								$str_body .= "<td>".$order_column->Product_Exp."</td>";
								$str_body .= "<td>".$order_column->Remark."</td>";
								$str_body .= "<td><a ONCLICK=\"deleteItem(this)\" >".img("css/images/icons/del.png")."</a></td>";
								$str_body .= "</tr>";
							}
							echo $str_body;
						}
					 ?>
					<tbody></tbody>
				</table>
			</td>
		  </tr>
	  </table>
	</fieldset>
</div>
<!-- Modal -->
<div style="min-height:500px;padding:5px 10px;" id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
        <h3 id="myModalLabel">Product Detail</h3>
        <input  value="" type="hidden" name="prdModalval" id="prdModalval" >
    </div>
    <div class="modal-body"><!-- working area--></div>
    <div class="modal-footer">
        <div style="float:left;">
            <input class="btn red" value="Select All" type="button" id="select_all">
            <input class="btn red" value="Deselect All" type="button" id="deselect_all">
        </div>
        <div style="float:right;">
            <input class="btn btn-primary" value="Select" type="submit" id="search_submit">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        </div>
    </div>
</div>