<script lang="javascript">
    var allVals = new Array();
    $(document).ready(function() {
        $('#product_status').val('');
        $('#category').val('');
        initProductTable();
        $('#product_status').change(function() {
            //$('#category').show();
            var status = $(this).val();
            var cate = $('#category').val();
            getProduct(<?php echo $data['Location_Id']; ?>, cate, status);
        });

        $('#category').change(function() {
            getProduct(<?php echo $data['Location_Id']; ?>, $(this).val(), $('#product_status').val());
        });

        function getProduct(location_id, cate, status) {
            //alert('w='+warehouse+'/z='+zone+'/c='+cate+'/s='+status);

            if (status != "") {
                $('#product').html('<img src="<?php echo base_url() ?>images/ajax-loader.gif" />');
                //alert('category='+cate);
                $.get("<?php echo site_url(); ?>/putaway/getProductListByLocation?location_id=" + location_id + "&status=" + status + "&cate=" + cate, function(data) {
                    //$('#product').html(data);
                    var option = '';
                    $.each(data, function(i, item) {
                        option = '<input ID=chkBoxVal type=checkbox name=chkBoxVal[] value="' + item.id + '" id=chkBoxVal onClick="getCheckValue(this)">';
                        $('#defDataTable2').dataTable().fnAddData([
                            option
                                    , item.code
                                    , item.name
                        ]);
                    });

                    initProductTable();
                    $('#product').html('');
                }, "json");
            }
            else {
                $('#product').html('Please select Product Status');
            }
        }

        $('#select_all').click(function() {
            var cdata = $('#defDataTable2').dataTable();
            allVals = new Array();
            $(cdata.fnGetNodes()).find(':checkbox').each(function() {
                $this = $(this);
                $this.attr('checked', 'checked');
                allVals.push($this.val());
            });
            //alert('select all '+allVals);
        });

        $('#deselect_all').click(function() {
            var selected = new Array();
            var cdata = $('#defDataTable2').dataTable();
            $(cdata.fnGetNodes()).find(':checkbox').each(function() {
                $this = $(this);
                $this.attr('checked', false);
                selected.push($this.val());
                allVals.pop($this.val());
            });
            allVals = new Array();
        });

        $('#save').click(function() {
            var statusisValidateForm = validateForm();
            //alert(' status = '+statusisValidateForm);
            //alert(' val = '+allVals);
            var id = allVals;
            if (statusisValidateForm === true) {
                //alert( 'ok ');
                var data_form = $("#formPtoL").serialize();
                var oTable = $('#defDataTable2').dataTable();
                var sData = $('input', oTable.fnGetNodes()).serialize();

                //alert(' data = '+sData);
                if (sData == "") {
                    alert("Please Select Product Order Detail");
                    return false;
                }
                var message = "";
                $('#save').attr('disabled', 'disabled');
                $.post("<?php echo site_url(); ?>" + "/putaway/saveProductByLocation", data_form + '&' + sData, function(data) {
                    switch (data.status) {
                        case 'C001':
                            message = "Save Product To Location Complete";
                            break;
                        case 'C002':
                            message = "This zone not have location";
                            break;
                        case 'C000':
                            message = "Please select Product";
                            break;

                    }
                    alert(message);
                    url = "<?php echo site_url(); ?>/" + data.url;
                    redirect(url);
                }, "json");
                return false;
            }
            else {
                alert("Please Check Your Require Information (Red label).");
                return false;
            }
        });

    });

    function initProductTable() {
        var oTable = $('#defDataTable2').dataTable({
            "bJQueryUI": true,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "oLanguage": {
                "sLoadingRecords": "Please wait - loading..."
                , "sProcessing": "<img src=\"<?php echo base_url() ?>images/ajax-loader.gif\" />"
            },
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>'
        });
        //$('#product').html('Please select Product Status');
    }

    function getCheckValue(obj) {
        //alert(' check each input ');
        var isChecked = $(obj).attr("checked");
        if (isChecked) {
            allVals.push($(obj).val());
        } else {
            allVals.pop($(obj).val());
        }
        //alert(' check each input '+allVals);
    }

    function validateForm() {
        $("form").validate({
            rules: {
                product_status: {required: true}
            }
        });
        return $("form").valid();
    }

    function scancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/putaway/editProductToLocation?id=<?php echo $data['Location_Id']; ?>";
                        redirect(url);
                    }
                }
</script>
</script>
<?php
$location_info = $data['location_info'];
?>
<FORM ACTION="" METHOD="POST" id="formPtoL">
    <div class="well">

        <fieldset>
            <legend>&nbsp;Add Product to Location&nbsp;</legend>
            <TABLE width="100%">
                <TR>
                    <TD width="200" align="right">Warehouse :</TD>
                    <TD>
                        <?php echo $location_info['warehouse']; ?>
                    </TD>
                    <TD align="right">Zone :</TD>
                    <TD><?php echo $location_info['zone']; ?>
                    </TD>
                    <TD align="right" >Location :</TD>
                    <TD width="200"><?php echo $data['Location_Code']; ?>
                        <input type="hidden" name="location_id" value="<?php echo $data['Location_Id']; ?>" />
                        <input type="hidden" name="product_location_id" value="<?php echo $data['Product_Location_Id']; ?>" />
                    </TD>
                </TR>
                <TR>
                    <TD align="right" valign="top">Product Status</TD>
                    <TD  valign="top">
                        <?php echo form_dropdown('product_status', $data['selectProductStatus'], '1', 'id=product_status class="required"') ?>  
                    </TD>	
                    <TD align="right"  valign="top">Product Category</TD>
                    <TD  valign="top">
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
                Please select Product Status
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

    </div>
    <div align="center" style="text-align:center;margin:2px auto;">
        <INPUT TYPE="button" class="button dark_blue"	VALUE="BACK" ONCLICK="scancel();"> <INPUT TYPE="button" class="button dark_blue"	VALUE="SAVE" ONCLICK="" id="save">
    </div>
</FORM>
<?php
//p($data); ?>