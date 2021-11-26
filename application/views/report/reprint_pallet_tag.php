<!--Original By POR 2013-11-14-->
<style>
textarea {
    resize: none;
}
</style>
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
            $('#frm_date').val('');
            $('#doc_type').val('');
            $('#to_date').val('');
            $('#doc_value').val('');
            $('#product_code').val('');
            $('#product_lot').val('');
            $('#doc_ext').val('');
            $('#renter_id').val('-1');

            $('#report').html('Please click search');

            //กำหนดให้ซ่อนปุ่ม print report 
            $("#pdfshow").hide();
            // $("#excelshow").hide();
        });

    });

    function change_show_per_page(page, ipp) {
        show_data_of_page(page, ipp);
    }

    // $('#search').click(function () {
    //         var d = new Date();

    //         var doc_ext = $('#doc_ext').val();
    
    //         $('#report').html('<img src="<?php echo base_url() ?>/images/ajax-loader.gif" />');

    //         $.ajax({
    //             type: 'post',
    //             url: '<?php echo site_url(); ?>/reprint_5copy_by_order/para_call', 
    //             data: 'dod_ext='+doc_ext 
    //             success: function (data)
    //             {
    //                 $("#report").html(data);
    //                 $("#pdfshow").show();
    //                 $("#excelshow").show();
    //             }
    //         });
    //     });

    // function show_data_of_page(page, ipp) {
    //     var show_per_page = ipp;
    //     var fdate = $('#frm_date').val();
    //     var tdate = $('#to_date').val();
    //     var doc_type = $('#doc_type').val();
    //     var doc_value = $('#doc_value').val();
    //     var checkbokActive = $("#ckb_active:checked").val() ? 1 : 0;
    //     ipp = show_per_page;


    //     $.ajax({
    //         type: 'get',
    //         url: '<?php echo site_url(); ?>/reprint_5copy_by_order/para_call', // in here you should put your query 
    //         data: 'fdate=' + fdate
    //                 + '&tdate=' + tdate
    //                 + '&doc_type=' + doc_type
    //                 + '&doc_value=' + doc_value
    //                 + '&page=' + page
    //                 + '&ipp=' + ipp
    //                 + '&w=' + $('#frmReceive').width()
    //                 + '&ckbActive=' + checkbokActive,
    //         success: function (response) {
    //             $("#report").html(response);
    //             $("#pdfshow").show();
    //             // $("#excelshow").show();
    //         },
    //         error: function () {
    //             alert('An error occurred');
    //         }
    //     });

    //     return false;
    // }

    // function find_data() {
    //     var fdate = $('#frm_date').val();
    //     var tdate = $('#to_date').val();
    //     var doc_type = $('#doc_type').val();
    //     var doc_value = $('#doc_value').val();
    //     var product_code = $('#product_code').val();
    //     var product_lot = $('#product_lot').val();
    //     var owner_id = $('#owner_id').val();
    //     var from_document = $('#from_document').val();
    //     var to_document = $('#to_document').val();
    //     var from_product = $('#from_product').val();
    //     var to_product = $('#to_product').val();
    //     var doc_ext = $('#doc_ext').val();

    //     var checkbokActive = $("#ckb_active:checked").val() ? 1 : 0;
    //     $('#report').html('<img src="<?php echo base_url() ?>/images/ajax-loader.gif" />');

    //     window.location.href = "<?php echo site_url(); ?>/reprint_5copy_by_order/para_call?"+doc_ext;

        // $.ajax({
        //     type: 'get',
        //     url: '<?php echo site_url(); ?>/reprint_5copy_by_order/para_call', // in here you should put your query 
        //     data: 'doc_ext=' + doc_ext,
        //             // + '&tdate=' + tdate
        //             // + '&page=' + 1
        //             // + '&w=' + $('#frmReceive').width()
        //             // + '&ckbActive=' + checkbokActive
        //             // + '&product_code=' + product_code
        //             // + '&product_lot=' + product_lot
        //             // + '&from_document=' + from_document
        //             // + '&to_document=' + to_document
        //             // + '&from_product=' + from_product
        //             // + '&to_product=' + to_product
        //             // + '&doc_ext=' + doc_ext
        //             // + '&owner_id=' + owner_id,
        //     success: function (data)
        //     {
        //         $("#report").html(data);

        //         //ADD BY POR 2013-11-05 กำหนดให้แสดงปุ่ม print report หลังจากได้ข้อมูลแล้ว
        //         // $("#pdfshow").show();
        //         // $("#excelshow").show();
        //         //END ADD
        //     }
        // });
    // }

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
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="product_tag_call" id="frmReceive" name="frmReceive" target="_blank" >
            <fieldset style="margin:0px auto;">
                <legend>Search Criteria </legend>

                <table border="0" cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                    <tr>
                        <td>Pallet Code List :</td>
                        <td><textarea rows="15" cols="50" placeholder="Pallet Code" id="doc_ext" name="doc_ext" value=""></textarea></td>

                        <td>
                            <input type="submit" name="search" value="Print Pallet Tag" id="search" class="button dark_blue" />
                            <input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" />
                        </td>                        
                    </tr>
                </table>
            </fieldset>
            <!-- <input type="hidden" name="queryText" id="queryText" value=""/>
            <input type="hidden" name="search_param" id="search_param" value=""/> -->
        </form>    

        <!-- <fieldset>
            <legend>Search Result </legend>
            <div id="report" style="margin:10px;text-align: center;">
                Please click search
            </div>
        </fieldset> -->

    </TD>
</TR>