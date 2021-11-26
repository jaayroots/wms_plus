<script>
    $(document).ready(function () {

        conf_change_dp_date = '<?php echo $conf_change_dp_date ?>';

        $('.required').each(function () {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });

        $("#frm_date").datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            onClose: function (selectedDate) {
                $("#to_date").datepicker("option", "minDate", selectedDate);
            }
        }).on('changeDate', function (ev) {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        }).keypress(function (event) {
            event.preventDefault();
        }).bind("cut copy paste", function (e) {
            e.preventDefault();
        });

        $("#to_date").datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            onClose: function (selectedDate) {
                $("#frm_date").datepicker("option", "maxDate", selectedDate);
            }
        }).on('changeDate', function (ev) {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }

        }).keypress(function (event) {
            event.preventDefault();
        }).bind("cut copy paste", function (e) {
            e.preventDefault();
        });

        $('#search').click(function () {
            //alert('search');
            var fdate = $('#frm_date').val();
            var tdate = $('#to_date').val();
            var doc_type = $('#doc_type').val();
            var doc_value = $('#doc_value').val();
            var type_dp_date_val = null;

            if (conf_change_dp_date) {
                type_dp_date_val = $('#type_dp_date').val();
            }


            if (fdate == "" && tdate == "" && doc_type == "" && doc_value == "") {
                alert('Please select "From Date" or "To Date" or "Document" ');
                $('#report').html('Please select "From Date" or "To Date" or "Document" ');
            } else {

                $('#report').html('<img src="<?php echo base_url("/images/ajax-loader.gif") ?>" />');

                $.ajax({
                    type: 'post',
                    url: '<?php echo site_url("/report_jcs/dispatchReport"); ?>',
                    data: 'fdate=' + fdate
                    + '&tdate=' + tdate
                    + '&type_dp_date_val=' + type_dp_date_val,
                    success: function (data)
                    {
                        $("#report").html(data);
                        $("#pdfshow").show();
                        $("#excelshow").show();
                    }
                });
            }
        });

        $('#clear').click(function () {
            $('#frm_date').val('');
            $('#doc_type').val('');
            $('#to_date').val('');
            $('#doc_value').val('');

            $('#report').html('Please click search');
            $("#pdfshow").hide();
            $("#excelshow").hide();
        });

        $("#product").click(function () {
            $('#product').val('');
            $('#product_id').val('');
        });

        $("#period").keyup(function () {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });

        $("#step").keyup(function () {
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
<tr class="content" style='height:100%' valign="top">
    <td>
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="frmDispatch" name="frmDispatch" >
            <fieldset style="margin:0px auto;">
                <legend>Search Criteria </legend>
                <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                    <tr>
                        <td>From Date</td>
                        <td>
                            <input type="text" placeholder="Date Format" id="frm_date" name="frm_date" value="<?php echo date("d/m/Y"); ?>" class="required" >
                        </td>
                        <td>To Date</td>
                        <td>
                            <input type="text" placeholder="Date Format" id="to_date" name="to_date" value="<?php echo date("d/m/Y"); ?>" class="required" >
                        </td>
                        <td >
                            <input type="button" name="search" value="Search" id="search" class="button dark_blue" />
                            <input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" />
                        </td>
                        <td></td>
                        <td ></td>
                    </tr>                    
                </table>
            </fieldset>
            <input type="hidden" name="queryText" id="queryText" value=""/>
            <input type="hidden" name="search_param" id="search_param" value=""/>
            <input type="hidden" name="type_dp_date" value="sys_dp_date"/>
        </form>
        <fieldset>
            <legend>Search Result </legend>
            <div id="report" style="margin:10px"> Please click search </div>
        </fieldset>
    </td>
</tr>