<!--Original By POR 2013-11-14-->
<script>
    var show_per_page = 'All';

    $(document).ready(function () {

        $('.required').each(function () {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });

        $('#search').click(function () {
            find_data();
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

        $('#pagination a').live('click', function () {
            var $this = $(this);
            return show_data_of_page($this.data('page'), $('#paginate_per_page').val());
        });


        $('#clear').click(function () {
            $("#frm_date").val('');
            $("#to_date").val('');
            $("#report").html('Please click search');
            $("#pdfshow").hide();
            $("#excelshow").hide();
        });

    });

    function change_show_per_page(page, ipp) {
        show_data_of_page(page, ipp);
    }

    function show_data_of_page(page, ipp) {
        var show_per_page = ipp;

        var fdate = $('#frm_date').val();
        var tdate = $('#to_date').val();
        var doc_type = $('#doc_type').val();
        var doc_value = $('#doc_value').val();

        ipp = show_per_page;

        $.ajax({
            type: 'get',
            url: '<?php echo site_url('/report_jcs/receiveReport'); ?>',
            data: 'fdate=' + fdate
                    + '&tdate=' + tdate
                    + '&page=' + page
                    + '&ipp=' + ipp
                    + '&w=' + $('#frmReceive').width(),
            success: function (response) {
                $("#report").html(response);
                $("#pdfshow").show();
                $("#excelshow").show();
            },
            error: function () {
                alert('An error occurred');
            }
        });

        return false;
    }

    function find_data() {
        var fdate = $('#frm_date').val();
        var tdate = $('#to_date').val();
        var doc_type = $('#doc_type').val();
        var doc_value = $('#doc_value').val();

        $('#report').html('<img src="<?php echo base_url("/images/ajax-loader.gif") ?>" />');

        $.ajax({
            type: 'get',
            url: '<?php echo site_url('/report_jcs/receiveReport'); ?>',
            data: 'fdate=' + fdate
                    + '&tdate=' + tdate
                    + '&page=' + 1
                    + '&ipp=' + show_per_page
                    + '&w=' + $('#frmReceive').width(),
            success: function (data)
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

<tr class="content" style='height:100%' valign="top">
    <td>
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="frmReceive" name="frmReceive" >
            <fieldset style="margin:0px auto;">
                <legend>Search Criteria </legend>
                <table cellpadding="1" cellspacing="1" style="width:98%; margin:0px auto;" >
                    <tr>
                        <td>From Date</td>
                        <td>
                            <input type="text" placeholder="Date Format" id="frm_date" name="frm_date" value="<?php echo date("d/m/Y"); ?>" >
                        </td>
                        <td>To Date</td>
                        <td>
                            <input type="text" placeholder="Date Format" id="to_date" name="to_date" value="<?php echo date("d/m/Y"); ?>" >
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
        </form>    

        <fieldset>
            <legend>Search Result </legend>
            <div id="report" style="margin:10px;text-align: center;"> Please click search </div>
        </fieldset>
    </td>
</tr>