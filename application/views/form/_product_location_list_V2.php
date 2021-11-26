<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<script>
    var count       = 1;
    $(document).ready(function() {
        var oTable=$('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "oLanguage": {
                "sLoadingRecords": "Please wait - loading..."
                ,"sProcessing": "<img src=\"<?php echo base_url() ?>images/ajax-loader.gif\" />"
            },
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>'
        });

    });

    function confirmDelete(id,location_id){
        if(confirm("Confirm delete this data?")==true){
            $.post("<?php echo site_url(); ?>/putaway/deleteProductToLocation",{id:id,location_id:location_id},function(data){ 
                switch (data.status){
                    case 'C000':  message = "Delete Product Complete";     							
                        alert(message);
                        url = "<?php echo site_url(); ?>/putaway";
                        redirect(url)
                        break;
                    case 'C001':  message = "Not Delete This Product";  
                        alert(message);
                        break;
                    }					
                },"json"); 
            }
        }  
                
        // <<<<<------ START ADD Body PAList by Ton! 20130902 ------>>>>>
        function getPAList(){
            $('#product_detail').html('<img src="<?php echo base_url() ?>images/ajax-loader.gif" />');
            $('#frmPAList').serialize()
            $.post('<?php echo site_url() . "/putaway/paDataList" ?>', $('#frmPAList').serialize(), function(data){
                $('#product_detail').html(data);
            });
        }
        // <<<<<------ END ADD Body PAList by Ton! 20130902 ------>>>>>
        
        // <<<<<------ START ADD DROPDOWN by Ton! 20130903 ------>>>>>
        function setZone(){
            $.post('<?php echo site_url() . "/putaway/getZoneByWarehouseID" ?>', $('#frmPAList').serialize(), function(html){
                $('#Zone_Id').html(html);   
            },'html');
            setCategory();
        }
    
        function setCategory(){
            $.post('<?php echo site_url() . "/putaway/getCategoryByZoneID" ?>', $('#frmPAList').serialize(), function(html){
                $('#Category_Id').html(html);   
            },'html'); 
        }
        // <<<<<------ END ADD DROPDOWN by Ton! 20130903 ------>>>>>
</script>
<style>
    #myModal {
        width: 1024px; /* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -512px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
    }
    #report{
        margin:5px;
        text-align:center;
    }

</style>
<TABLE width="100%">
    <FORM ID="frmPAList" NAME="frmPAList" METHOD='post'>
        <TR class="content" style='height:100%' valign="top">
            <TD>&nbsp;Warehouse&nbsp;</TD>
            <TD><?php echo $WHList; ?></TD>
            <TD>&nbsp;Zone&nbsp;</TD>
            <TD><?php echo $ZONEList; ?></TD>
            <TD>&nbsp;Category&nbsp;</TD>
            <TD><?php echo $CateList; ?></TD>
        </TR>
        <TR class="content" style='height:100%' valign="top">
            <TD>&nbsp;SKU.&nbsp;</TD>
            <TD><INPUT TYPE="text" ID="Product_Code" NAME="Product_Code" PLACEHOLDER="Input SKU."></TD>
            <TD>&nbsp;Status&nbsp;</TD>
            <TD><?php echo $StatusList; ?></TD>

            <TD colspan="2">
                <INPUT TYPE='button' class='button dark_blue' VALUE='<?php echo 'Search'; ?>' ONCLICK="getPAList()">
                <INPUT TYPE='button' class='button dark_blue' VALUE='<?php echo ADD; ?>' ONCLICK="openForm('user','putaway/form','A','')">
            </TD>            
        </TR>
    </FORM>
    <TR class="content" style='height:100%' valign="top">
        <TD colspan="10"><div id="product_detail" style="width:100%;"></div></TD>
    </TR>
</TABLE>


