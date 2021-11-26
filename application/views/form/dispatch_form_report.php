<script>
    $(document).ready(function() {

       conf_change_dp_date = '<?php echo $conf_change_dp_date?>';

        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });

        $("#frm_date").datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            onClose: function(selectedDate) {
                $("#to_date").datepicker("option", "minDate", selectedDate);
            }
        }).on('changeDate', function(ev) {
            if ($(this).val() != '') {
                $(this).removeClass('required'); 
            } else {
                $(this).addClass('required');
            }
        }).keypress(function(event) {
            event.preventDefault();
        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        $("#to_date").datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            onClose: function(selectedDate) {
                $("#frm_date").datepicker("option", "maxDate", selectedDate);
            }
        }).on('changeDate', function(ev) {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }

        }).keypress(function(event) {
            event.preventDefault();
        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        $('#search').click(function() {
            //alert('search');
            var fdate = $('#frm_date').val();
            var tdate = $('#to_date').val();
            var doc_type = $('#doc_type').val();
            var doc_value = $('#doc_value').val();
            var type_dp_date_val = null; //add for select type of dispatch date between system dispatch date and real dispatch date : by kik : 20141209

            //check config can change dispatch date before get value : by kik : 20141209
            if(conf_change_dp_date){
                type_dp_date_val = $('#type_dp_date').val();
            }


            if (fdate == "" && tdate == "" && doc_type == "" && doc_value == "") {
                alert('Please select "From Date" or "To Date" or "Document" ');
                $('#report').html('Please select "From Date" or "To Date" or "Document" ');
            }
            else {

                $('#report').html('<img src="<?php echo base_url() ?>/images/ajax-loader.gif" />');

                $.ajax({
                    type: 'post',
                    url: '<?php echo site_url(); ?>/report/dispatchReport', // in here you should put your query
                    data: 'fdate=' + fdate + '&tdate=' + tdate + '&doc_type=' + doc_type
                            + '&doc_value='+ doc_value
                            + '&type_dp_date_val='+ type_dp_date_val,
                    success: function(data)
                    {
                        //alert(data);
                        $("#report").html(data);

                        //ADD BY POR 2013-11-05 กำหนดให้แสดงปุ่ม print report หลังจากได้ข้อมูลแล้ว
                        $("#pdfshow").show();
                        $("#excelshow").show();
                        //END ADD
                    }
                });
            }
        });

        $('#clear').click(function() {
            $('#frm_date').val('');
            $('#doc_type').val('');
            $('#to_date').val('');
            $('#doc_value').val('');

            $('#report').html('Please click search');

            //ADD BY POR 2013-11-05 กำหนดให้ซ่อนปุ่ม print report
            $("#pdfshow").hide();
            $("#excelshow").hide();
            //END ADD
        });

        $("#product").click(function() {
            $('#product').val('');
            $('#product_id').val('');
        });

        $("#period").keyup(function() {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });

        $("#step").keyup(function() {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });
    });


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
<TR class="content" style='height:100%' valign="top">
    <TD>
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="frmDispatch" name="frmDispatch" >
            <fieldset style="margin:0px auto;">
                <legend>Search Criteria </legend>

                <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >

                    <tr>
                        <td>From Date
                        </td>
                        <td>
                            <input type="text" placeholder="Date Format" id="frm_date" name="frm_date" value="<?php echo date("d/m/Y"); ?>" class="required" >
                        </td>
                        <td>To Date
                        </td>
                        <td>
                            <input type="text" placeholder="Date Format" id="to_date" name="to_date" value="<?php echo date("d/m/Y"); ?>" class="required" >
                        </td>
                        <td></td>
                        <td></td>
                        <td >

                        </td>
                    </tr>
                    <tr valign="top">
                        <td>Document Field
                        </td>
                        <td>
                            <select name="doc_type" id="doc_type">
                                <option value="Document_No">Document No.</option>
                                <option value="Doc_Refer_Ext">Refer External No.</option>
                                <option value="Doc_Refer_Int">Refer Internal No.</option>
                                <option value="Doc_Refer_Inv">Invoice No.</option>
                                <option value="Doc_Refer_CE">Customs Entry</option>
                                <option value="Doc_Refer_BL">BL No.</option>
                                <option value="Product_Serial">Serial</option>
                                <option value="Product_Lot">Lot</option>
                            </select>
                            <input id="doc_value" class="input-small" type="text" placeholder="VALUE" value="" name="doc_value">
                        </td>

                  <!--//add for select type of dispatch date between system dispatch date and real dispatch date : by kik : 20141209-->
                  <?php if($conf_change_dp_date): ?>
                        <td>Type of Date
                        </td>
                        <td>
                            <select name="type_dp_date" id="type_dp_date">
                                <option value="sys_dp_date">System Dispatch Date</option>
                                <option value="real_dp_date">Real Dispatch Date</option>
                            </select>
                        </td>

                  <?php else:?>
                         <td></td>
                         <td></td>
                  <?php endif;?>

                        <td></td>
                        <td></td>
                        <td >
                            <input type="button" name="search" value="Search" id="search" class="button dark_blue" />
                            <input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" />
                        </td>
                    </tr>
                </table>

            </fieldset>

            <input type="hidden" name="queryText" id="queryText" value=""/>
            <input type="hidden" name="search_param" id="search_param" value=""/>
        </form>

        <fieldset>
            <legend>Search Result </legend>
            <div id="report" style="margin:10px">
                Please click search
            </div>
        </fieldset>

    </TD>
</TR>