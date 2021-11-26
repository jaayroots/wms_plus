<script>
    var show_per_page = 'All';
    var d = new Date();
    var month = d.getMonth()+1;
    var day = d.getDate();
    var current_date = (day<10 ? '0' : '') + day + '/' + (month<10 ? '0' : '') + month + '/' + d.getFullYear();

    $(document).ready(function() {

    $("#product").click(function(){
        $(this).val('');
        $("#product_id").val('');
        $("#product_name").val('');
    });

    $("#product").autocomplete({
        minLength: 0,
        search: function( event, ui ) {
            $('#highlight_productCode').attr("placeholder",'');
        },
        source: function( request, response ) {
          $.ajax({
              url: "<?php echo site_url("/report/ajax_show_product_list"); ?>",
              dataType: "json",
              type:'post',
              data: {
                text_search: $('#product').val()
              },
              success: function( val, data ) {
                  if(val != null){
                     var flag_set_product_id = true;
                     response( $.map( val, function( item ) {
                        if(flag_set_product_id){
                            $('#product_id').val(item.product_id);
                            $('#product_name').val(item.product_name);
                            flag_set_product_id = false;
                        }
                      return {
                        product_name: item.product_name,                          
                        label: item.product_code + ' ' + item.product_name,
                        value: item.product_id
                      }
                    }));
                  }
              },
          });
        },
        open: function( event, ui ) {
            var auto_h = $(window).innerHeight()-$('#table_of_productCode').position().top-50;
            $('.ui-autocomplete').css('max-height',auto_h);
        },
        focus: function( event, ui ) {
            $('#highlight_productCode').attr("placeholder", ui.item.label);
            return false;
        },
        select: function( event, ui ) {
            console.log(ui);
            $('#highlight_productCode').attr("placeholder",'');
            $('#product').val( ui.item.label ); //โชว์อะไรในช่อง
            $('#product_id').val(ui.item.value); //ค่าที่ต้องการ assign
            $('#product_name').val(ui.item.product_name);            
          return false;
        }
    });

        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });

        $('#search').click(function() {
            find_data();
        });

        $("#fdate").datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            onClose: function(selectedDate) {
                $("#tdate").datepicker("option", "minDate", selectedDate);
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

        $("#tdate").datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            onClose: function(selectedDate) {
                $("#fdate").datepicker("option", "maxDate", selectedDate);
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

        $('#pagination a').live('click', function() {
            var $this = $(this);
            return show_data_of_page($this.data('page'),$('#paginate_per_page').val());
        });


        $('#clear').click(function() {
            $('#fdate').val(current_date);
            $('#tdate').val(current_date);
            $('#product').val('');
            $('#product_id').val('');
            $('#product_name').val('');
            $('#ior').val('');
            $('#eor').val('');
            $('#ce_in').val('');
            $('#ce_out').val('');
            $('#report').html('Please click search');
            $("#pdfshow").hide();
            $("#excelshow").hide();
        });

    });

    function change_show_per_page(page,ipp){
        show_data_of_page(page,ipp);
    }

    function show_data_of_page(page,ipp){
        var show_per_page = ipp;
        var fdate = $('#fdate').val();
        var tdate = $('#tdate').val();
        var product_id = $('#product_id').val();
        var product_name = $('#product_name').val();
        var renter_id = $('#renter_id').val();
        var ior = $('#ior').val();
        var eor = $('#eor').val();
        var ce_in = $('#ce_in').val();
        var ce_out = $('#ce_out').val();
        
        ipp = show_per_page;

        $.ajax({
            type: 'get',
            url: '<?php echo site_url('/report_customs/movement_search'); ?>',
            data: 'fdate=' + fdate
                    + '&tdate=' + tdate
                    + '&product_id=' + product_id
                    + '&product_name=' + product_name
                    + '&renter_id=' + renter_id
                    + '&doc_type=' + 'Document_No'
                    + '&doc_value=' + ''
                    + '&page=' + page
                    + '&ipp=' + ipp
                    + '&w=' + $('#frmImportReport').width()
                    + '&ior=' + ior
                    + '&eor=' +eor
                    + '&ce_in=' +ce_in
                    + '&ce_out=' +ce_out,
            success: function(response) {
                $("#report").html(response);
                $("#pdfshow").show();
                $("#excelshow").show();
                //$('#pagination').html(pagination);
            },
            error: function() {
                alert('An error occurred');
            }
        });

        return false;
    }

    function find_data(){
        var fdate = $('#fdate').val();
        var tdate = $('#tdate').val();
        var product_id = $('#product_id').val();
        var product_name = $('#product_name').val();
        var renter_id = $('#renter_id').val();
        var ior = $('#ior').val();
        var eor = $('#eor').val();
        var ce_in = $('#ce_in').val();
        var ce_out = $('#ce_out').val();

        $('#report').html('<img src="<?php echo base_url('/images/ajax-loader.gif') ?>" />');
        $.ajax({
            type: 'get',
            url: '<?php echo site_url('/report_customs/movement_search'); ?>',
            data: 'fdate=' + fdate
                    + '&tdate=' + tdate
                    + '&product_id=' + product_id
                    + '&product_name=' + product_name
                    + '&renter_id=' + renter_id
                    + '&doc_type=' + 'Document_No'
                    + '&doc_value=' + ''
                    + '&page=' + 1
                    + '&ipp=' + show_per_page
                    + '&w=' + $('#frmImportReport').width()
                    + '&ior=' + ior
                    + '&eor=' +eor
                    + '&ce_in=' +ce_in
                    + '&ce_out=' +ce_out,
            success: function(data)
            {
                $("#report").html(data);
                $("#pdfshow").show();
                $("#excelshow").show();
            }
        });
    }

</script>
<style>

#pagination { overflow: hidden; margin-bottom: 10px; text-align: center; }
#pagination a { display: inline-block; padding: 3px 5px; font-size: 14px; color: #333; border-radius: 3px; text-shadow: 0 0 1px #fff;  border: 1px solid #ccc;

    background: #ffffff;
    background: -moz-linear-gradient(top,  #ffffff 0%, #f6f6f6 47%, #ededed 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffffff), color-stop(47%,#f6f6f6), color-stop(100%,#ededed));
    background: -webkit-linear-gradient(top,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
    background: -o-linear-gradient(top,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
    background: -ms-linear-gradient(top,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
    background: linear-gradient(to bottom,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#ededed',GradientType=0 );
}
#pagination a:hover { border: 1px solid #333; }
#pagination a.current { color: #f00; }

</style>

<TR class="content" style='height:100%' valign="top">
    <TD>
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="frmExport" name="frmExport" >
            <fieldset style="margin:0px auto;">
                <legend>Search Criteria </legend>

                <table cellpadding="1" cellspacing="1" style="width:98%; margin:0px auto;" >
                     <tr>
                        <td><?php echo _lang("cus_ior");?></td>
                        <td><input type="text" placeholder="<?php echo _lang("cus_ior");?>" id="ior" name="ior" ></td>
                        <td><?php echo _lang("cus_eor");?></td>
                        <td><input type="text" placeholder="<?php echo _lang("cus_eor");?>" id="eor" name="eor" ></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><?php echo _lang("cus_customs_entry_inbound_short");?></td>
                        <td><input type="text" placeholder="<?php echo _lang("cus_customs_entry_inbound");?>" id="ce_in" name="ce_in" ></td>
                        <td><?php echo _lang("cus_customs_entry_outbound_short");?></td>
                        <td><input type="text" placeholder="<?php echo _lang("cus_customs_entry_outbound");?>" id="ce_out" name="ce_out" ></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td width="10%">Product Code :  </td>
                        <td width="35%">
                            <table id="table_of_productCode" cellspacing="0" cellpadding="0" style="float:left; height: 27px; padding: 0px; width: 100%;">
                                <div style="position: relative;">
                                    <?php echo form_input("product", "", "id='product' class='req_product' placeholder='"._lang('product_code')."' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 90%;  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                                    <?php echo form_input("highlight_productCode", "", "id='highlight_productCode' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 90%; position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?>
                                </div>
                            </table>
                            <input type="hidden" id="product_name" name="product_name" />
                            <input type="hidden" id="product_id" name="product_code" />
                        </td>
                        <td width="5%">From Date</td>
                        <td colspan="3">
                            <input type="text" placeholder="Date Format" id="fdate" name="fdate" value="<?php echo date("d/m/Y"); ?>" style="width: 100px;" >
                            To Date
                            <input type="text" placeholder="Date Format" id="tdate" name="tdate" value="<?php echo date("d/m/Y"); ?>" style="width: 100px;" >
                        </td>
                        
                        <td width="20%">
                            <input type="button" name="search" value="Search" id="search" class="button dark_blue" />
                            <input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" />
                        </td>
                    </tr>
                </table>

            </fieldset>
            <input type="hidden" name="renter_id" id="renter_id" value="<?php echo $renter_id?>" />
        </form>

        <fieldset>
            <legend>Search Result </legend>
            <div id="report" style="margin:10px;text-align: center;">
                Please click search
            </div>
        </fieldset>
    </td>
</tr>