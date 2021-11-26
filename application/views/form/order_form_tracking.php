<!--Original By Joke 10/10/57-->
<script>
    var show_per_page = 'All';

    $(document).ready(function () {

        // เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
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
            //การตรวจสอบ ตอนที่ changeDate ใน datePicker ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
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
            //การตรวจสอบ ตอนที่ changeDate ใน datePicker ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
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
            
            $('#doc_type').val('');
            
            $('#doc_value').val('');

            $('#report').html('Please click search');

            //กำหนดให้ซ่อนปุ่ม print report 
            $("#pdfshow").hide();
            $("#excelshow").hide();
        });

    });

    function change_show_per_page(page, ipp) {
        show_data_of_page(page, ipp);
    }

    function show_data_of_page(page, ipp) {
        var show_per_page = ipp;

       
        var doc_type = $('#doc_type').val();
        var doc_value = $('#doc_value').val();

        ipp = show_per_page; // I am returning 30 results per page, change to what you want

        $.ajax({
            type: 'get',
            url: '<?php echo site_url(); ?>/order_tracking/OrderTracking', // in here you should put your query 
            data: 
                    '&doc_type=' + doc_type
                    + '&doc_value=' + doc_value
                   
                    + '&w=' + $('#frmReceive').width(),
            success: function (response) {
                $("#report").html(response);

                //ADD BY POR 2013-11-05 กำหนดให้แสดงปุ่ม print report หลังจากได้ข้อมูลแล้ว
                $("#pdfshow").show();
                $("#excelshow").show();
                //END ADD
                // pagination
//                    $('#pagination').html(pagination);             
            },
            error: function () {
                alert('An error occurred');
            }
        });

        return false;
    }

    function find_data() {
       
        var doc_type = $('#doc_type').val();
        var doc_value = $('#doc_value').val();

        $('#report').html('<img src="<?php echo base_url() ?>/images/ajax-loader.gif" />');

        $.ajax({
            type: 'get',
            url: '<?php echo site_url(); ?>/order_tracking/get_OrderTracking', // in here you should put your query 
            data: 
                     '&doc_type=' + doc_type
                    + '&doc_value=' + doc_value
                   
                    + '&w=' + $('#frmReceive').width(),
            success: function (data)
            {
                $("#report").html(data);

                //ADD BY POR 2013-11-05 กำหนดให้แสดงปุ่ม print report หลังจากได้ข้อมูลแล้ว
                $("#pdfshow").show();
                $("#excelshow").show();
                //END ADD
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
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="frmReceive" name="frmReceive" >
            <fieldset style="margin:0px auto;">
                <legend>Search Criteria </legend>

                <table cellpadding="1" cellspacing="1" style="width:98%; margin:0px auto;" >

                    <tr>
                        <td>Document Field
                        </td>
                        <td colspan="2" style="padding-bottom: 10px">
                            <select name="doc_type" id="doc_type" style="width: 25%; ">
                                <option value="Document_No">Document No.</option>
                                <option value="Doc_Refer_Ext">Refer External No.</option>
                                <option value="Doc_Refer_Int">Refer Internal No.</option>
                                <option value="Doc_Refer_Inv">Invoice No.</option>
                                <option value="Doc_Refer_CE">Customs Entry</option>
                                <option value="Doc_Refer_BL">BL No.</option>
                            </select>
                            <input id="doc_value" class="input-small" type="text" placeholder="Value" value="" name="doc_value" style="width: 40%">
                        </td>
                        <td style="margin: 0px auto; padding-bottom: 15px ;">
                            <input type="button" name="search" value="Search" id="search" class="button dark_blue" />
                            <input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" />
                        </td>
                       
                        <td>
                        </td>
                    </tr>
                </table>

            </fieldset>

            <input type="hidden" name="queryText" id="queryText" value=""/>
            <input type="hidden" name="search_param" id="search_param" value=""/>
        </form>    

        <fieldset>
            <legend>Search Result </legend>
            <div id="report" style="margin:10px;text-align: center;">
                Please click search
            </div>
        </fieldset>

    </TD>
</TR>