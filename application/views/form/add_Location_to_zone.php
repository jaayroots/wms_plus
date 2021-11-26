<?php // Create by Ton! 20130430 ?>
<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<SCRIPT>
	var selected = new Array();
    $(document).ready(function(){  
        $('#Warehouse_Id option[value=""]').attr('selected','selected');
        $('#Zone_Id option[value=""]').attr('selected','selected');
        $('#Category_Id option[value=""]').attr('selected','selected');
        $('#Storage_Id option[value=""]').attr('selected','selected');
		
		
    });
    
    function validation(){
        if ($('#Warehouse_Id option:selected').val()=="") {
            alert("Please enter the warehouse.");
            $('#Warehouse_Id').focus();
            return false;   
        }
        if ($('#Zone_Id option:selected').val()=="") {
            alert("Please enter the zone.");
            $('#Zone_Id').focus();
            return false;   
        }
//        if ($('#Category_Id option:selected').val()=="") {
//            alert("Please enter the category.");
//            $('#Category_Id').focus();
//            return false;   
//        }
        if ($('#Storage_Id option:selected').val()=="") {
            alert("Please enter the storage.");
            $('#Storage_Id').focus();
            return false;   
        }
        if (validationCheckBox()==false) {
            alert("Please select the storage code.");
            return false;   
        }
        submitFrm();
    }
    function validationCheckBox() {
        //alert(' check checked ');
        var oTable=$('#defDataTable2').dataTable();
        var sData = $('input', oTable.fnGetNodes()).serialize();
        if(sData==""){
            //alert("Please Select Product Order Detail");
            return false;
        }
		/*
        var checkvar=document.frmAddLocToZone.elements['storage_detail_id[]'];
        var checked=false;
        for (var i=0; i<checkvar.length; i++) {
            if (checkvar[i].checked==true){
                checked = true;
            }
        }
        if (checked==true){
            return true;
        } else {
            return false;
        }
		*/
    }
        
    function submitFrm(){// save location to zone.
        var data_form = $("#frmAddLocToZone").serialize();
        var oTable=$('#defDataTable2').dataTable();
        var sData = $('input', oTable.fnGetNodes()).serialize();
	    //alert(' val = '+sData);
	   
        $.post('<?php echo site_url()."/location/saveLocationToZone"?>', data_form+'&'+sData, function(data){
            if(data=="1"){
                if (confirm("Save successfully.")) {
                    window.location ="<?php echo site_url()?>/location";
                }
                return true;
            }else{
                alert("Save unsuccessfully.");
                return false;
            }
        }, "html");
    }
    
    function clearData(){// define input = "".
        $('#Warehouse_Id option[value=""]').attr('selected','selected');
        setZone();
        setCategory();
        setStorage();
    }
    
    function backToMenu(){// back to menu page.
        window.location ="<?php echo site_url()?>/location";
    }
    
    function addStorage(){
        window.location ="<?php echo site_url()?>/storage";
    }

    function goToAddZone(){// Add by Ton! 20130814
        window.location ="<?php echo site_url()?>/zone";
    }
    
    function showStorageDetailList(){
        if ($('#Warehouse_Id').val()==""|| !$('#Warehouse_Id').val()) {
         alert("Please select Warehouse.");
         $('#Warehouse_Id').focus();
         return false;
        }
        if ($('#Zone_Id').val()==""|| !$('#Zone_Id').val()) {
         alert("Please select Zone.");
         $('#Zone_Id').focus();
         return false;
        }

        $('#storage_detail').html('<img src="<?php echo base_url()?>images/ajax-loader.gif" />');
        $('#frmAddLocToZone').serialize()
        $.post('<?php echo site_url()."/location/showListStorageDetail"?>', $('#frmAddLocToZone').serialize(), function(data){
            $('#storage_detail').html(data);
        });   
    }
    
    function setZone(){
        $.post('<?php echo site_url()."/location/get_zone_by_warehouse_ID"?>', $('#frmAddLocToZone').serialize(), function(html){
            $('#Zone_Id').html(html);   
        },'html');
        setCategory();
        setStorage();
    }
    
    function setCategory(){
        $.post('<?php echo site_url()."/location/getCategoryByZoneID"?>', $('#frmAddLocToZone').serialize(), function(html){
            $('#Category_Id').html(html);   
        },'html'); 
        setStorage();
    }
    
    function setStorage(){
        $.post('<?php echo site_url()."/location/getStorageByWarehouseID"?>', $('#frmAddLocToZone').serialize(), function(html){
            $('#Storage_Id').html(html);   
        },'html');
    }
</SCRIPT>
<HTML>
 <HEAD>
  <TITLE>Add Location To Zone</TITLE>
 </HEAD>
 <BODY>
     <FORM class="form-horizontal" id="frmAddLocToZone" name="frmAddLocToZone" method='post'>   
         <TABLE width="90%">
             <TR>
                 <TD>Warehouse </TD>
                 <TD><?php echo $WHList; ?></TD>
                 <TD>Zone </TD>
                 <TD><?php echo $ZONEList; ?></TD>
                 <TD><INPUT TYPE="button" VALUE="Add Zone" ID="addZone" ONCLICK="goToAddZone()"></TD>
             </TR>
             <TR>
                 <TD>Category </TD>
                 <TD><?php echo $CATEList; ?></TD>
                 <TD>Storage </TD>
                 <TD><?php echo $STORList; ?></TD>
                 <TD><INPUT TYPE="button" VALUE="Add Strorage" ID="addStor" ONCLICK="addStorage()"></TD>
             </TR>
             <TR>
                 <TD colspan="5">
                     <div id="storage_detail" style="width:100%;"></div>
                 </TD>
             </TR>
         </TABLE>
     </FORM>
 </BODY>
</HTML>
