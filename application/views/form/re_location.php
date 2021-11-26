<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.datepicker.js" ?>"></script>-->
<script>
    var present_state = '<?php echo $present_state ?>';
    var flag_actual_location = false;
    var separator = "<?php echo SEPARATOR; ?>"; // Add By Akkarapol, 22/01/2013, Add Separator for use in Page
    var data_index = 0;
    var allVals = new Array();
    var nowTemp = new Date();
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

    // Read config from controller
    var conf_pallet = '<?php echo ($conf_pallet) ? true : false; ?>';
    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Re-Location'; // เปลี่ยนให้เป็นตาม Flow ที่ทำอยู่เช่น Pre-Dispatch, Adjust Stock, Stock Transfer เป็นต้น
    var data_table_id_class = '#showLocationTable'; // เปลี่ยนให้เป็นตาม id หรือ class ของ data table โดยถ้าเป็น id ก็ใส่ # เพิ่มไป หรือถ้าเป็น class ก็ใส่ . เพิ่มไปเหมือนการเรียกใช้ด้วย javascript ปกติ
    var redirect_after_save = site_url + "/reLocation"; // เปลี่ยนให้เป็นตาม url ของหน้าที่ต้องการจะให้ redirect ไป โดยปกติจะเป็นหน้า list ของ flow นั้นๆ
    var global_module = '';
    var global_sub_module = '';
    var global_action_value = '';
    var global_next_state = '';
    var global_data_form = '';

    var ci_item_id          = 0;
    var ci_location_from    = 1;
    var ci_suggest_loc      = 2;
    var ci_actual_loc       = 3;
    var ci_remark           = 4;
    var ci_old_loc_name = 5;
    var ci_sug_location_name =6;
    var ci_act_loc_name = 7;

    var ci_list = [
        {name: 'ci_item_id', value: ci_item_id},
        {name: 'ci_location_from', value: ci_location_from},
        {name: 'ci_suggest_loc', value: ci_suggest_loc},
        {name: 'ci_actual_loc', value: ci_actual_loc},
        {name: 'ci_remark', value: ci_remark},
        {name: 'ci_old_loc_name', value: ci_old_loc_name},
        {name: 'ci_sug_location_name', value: ci_sug_location_name},
        {name: 'ci_act_loc_name', value: ci_act_loc_name}

    ]

    var ci_prod_no = 0;
    var ci_prod_document = 1;
    var ci_prod_product_code = 2;
    var ci_prod_name = 3;
    //var ci_prod_status = 4;
    //var ci_prod_sub_status = 5;
    var ci_prod_rec_date = 6;
    var ci_prod_lot = 7;
    var ci_prod_sel = 8;
    var ci_prod_mfd = 9;
    var ci_prod_exp = 10;
    var ci_prod_pallet = 11;



    $(document).ready(function() {

        // Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง


        // Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        $('[name="worker_id"]').change(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');

            }
        });
        // END Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง


        // By Ball

        // Bind Data First
        actualLocation(); // bind event

        $('#locationCode').blur(function() {
            setTimeout(function() {
                $('#suggestions').hide();
            }, 200);
        });

        $('#select_warehouse').change(function(e)
        {
            var _this = this;
            if ($(this).val() > 0)
            {
                $.post("<?php echo site_url('/reLocation/showZoneList'); ?>", {warehouse_id: $(this).val()}, function(data)
                {
                    $('#zoneId option').remove();

                    var op = $('<option>').attr('value', 0).html('Select All');
                    $('#zoneId').append(op);

                    $.each(data, function(index, value)
                    {
                        var op = $('<option>').attr('value', value.Id).html(value.Zone_Name);
                        $('#zoneId').append(op);
                    });
                });
            } else {
                $('#zoneId option').remove();
                var op = $('<option>').attr('value', 0).html('Please select warehouse');
                $('#zoneId').append(op);
            }
        });

        /**
         Bind click when search for all location
         */
        function actualLocation() {
            $('.actual_location').on('keyup focus', function(e) {
                var _this = this;
                $('#suggestion_div').css({top: ($(this).offset().top + 30), left: $(this).offset().left}).show();
                $.post("<?php echo site_url('/reLocation/getLocationAll'); ?>", {criteria: $(this).val()}, function(response)
                {
                    $('#suggestionLocationList').html(''); // clear old list
                    $.each(response, function(index, value)
                    {
                        var lists = $('<li>').attr('class', 'actual_location_id').attr('data-id', value.Location_Code).html(value.Location_Code);
                        $('#suggestionLocationList').append(lists);
                    });
                    actualLocationClick(_this);
                });
            }).blur(function() {
            	var _this = this;
                console.log($(_this).val());
                setTimeout(function() {
                    $.get("<?php echo site_url(); ?>" + "/location/check_exist_location", {location_code: $(_this).val()}, function(data)
                        {
                            if (data == "null" && $(_this).val() != "")  //EDIT BY POR 2014-06-13 แก้ไขให้เตือนว่า location ไม่ถูกต้องกรณีที่ไม่มีอยู่จริง
                            {
                                alert('Sorry!!, This actual location not found, Please select another location.');
//                                $(_this).val('');
                                $(_this).val($(_this).data('act_location'));
                            }else{
                                $(_this).data('act_location', $(_this).val());
                            }
                        });

                        if ($(_this).val() != "") {
                        $.get("<?php echo site_url(); ?>" + "/reLocation/get_location", {location_code: $(_this).val()}, function(result)
                        {
                            if (result != "null")  
                            {
                                alert('Sorry!!, Can not choose this location, Please select another location.'); 
                                $(_this).val($(_this).data(''));
                                error_location = -1; 
                            } 
                            else {
                                error_count = 0;
                                error_location = 0;
                                $(_this).data('', $(_this).val());
                            }
                          });
                    }



                    $('#suggestion_div').hide();
                }, 200);
            });
        }

        function actualLocationClick(selector)
        {
            $('.actual_location_id').on('click', function(e)
            {
                var elm = $(selector);
                $(selector).attr('value', $(this).text()).removeClass('required');

                // START Create Temp [draft version]
                var input = '<input class="' + elm.attr('class') + '" placeholder="' + elm.attr('placeholder') + '" value="' + $(this).text() + '" data-act_location="' + $(this).text() + '" />';
                var oTable = $('#showLocationTable').dataTable(); // get datatables
                var indexOfRow = oTable.fnGetPosition($(selector).closest('tr').get(0)); // find rows index
                oTable.fnUpdate(input, indexOfRow, ci_actual_loc); // update new data to datatables

                actualLocation(); // re-bind event again
                // END

            });
        }

        function suggesLocationClick(selector)
        {
            $('.suggest_location_id').on('click', function(e)
            {
                var elm = $(selector);
                $(selector).attr('value', $(this).text()).removeClass('required');

                // START Create Temp [draft version]
                var input = '<input class="' + elm.attr('class') + '" placeholder="' + elm.attr('placeholder') + '" value="' + $(this).text() + '" data-location="' + elm.data('location') + '" data-status="' + elm.data('status') + '" data-sub_status="' + elm.data('sub_status') + '" data-category="' + elm.data('category') + '">';
                var oTable = $('#showLocationTable').dataTable(); // get datatables
                var indexOfRow = oTable.fnGetPosition($(selector).closest('tr').get(0)); // find rows index
                oTable.fnUpdate(input, indexOfRow, ci_suggest_loc); // update new data to datatables
                suggestLocation(); // bind event again
                // END

            });
        }

        function suggestLocation()
        {
            $('.suggest_location').on('keyup focus', function(e)
            {
                var _this = this;
                if ($(_this).val().length > 0)
                {
                        $(_this).removeClass('required');
                } else
                {
                        $(_this).addClass('required');
                }

                $('#suggestion_div').css({top: ($(this).offset().top + 30), left: $(this).offset().left}).show();
                $.post("<?php echo site_url('/reLocation/show_suggest_list_by_location'); ?>", {criteria: $(this).val(), location_id: $(this).data('location')}, function(response)
                {
                    // alert(response); exit();
                    // if(response ==){
                    //     alert('Sorry!!, Can not choose this location, Please select another location.'); 
                    // }else{
                    $('#suggestionLocationList').html(''); // clear old list
                    $.each(response, function(index, value)
                    {
                        var lists = $('<li>').attr('class', 'suggest_location_id').attr('data-id', value.Id).html(value.Location_Code);
                        $('#suggestionLocationList').append(lists);
                    });
                    suggesLocationClick(_this);
                });
            }).blur(function() {
            	var _this = this;
                setTimeout(function() {
                    $('#suggestion_div').hide();
         
                    if ($(_this).val() != "")
                    {
                        $.get("<?php echo site_url(); ?>" + "/reLocation/get_location", {location_code: $(_this).val()}, function(result)
                        {
                        //    alert(result);exit();
                            if (result != "null")  
                            {
                                alert('Sorry!!, Can not choose this location, Please select another location.'); 
                                $(_this).val($(_this).data(''));
                                error_location = -1; 
                            } 
                            else {
                                error_count = 0;
                                error_location = 0;
                                $(_this).data('', $(_this).val());
                            }
                          });
                    }





//                    $.get("<?php echo site_url(); ?>" + "/location/check_exist_location", {location_code: $(_this).val()}, function(data)
//					{
//						if (data == "null")
//						{
//							alert('Sorry!!, This suggest location not found, Please select another location.');
//							$(_this).val('');
//						}
//					});
                }, 200);
            });
        }

        $('#locationCode').on('keyup focus', function(e) {
            var evt = e || window.event;
            var ev_key = (evt.keyCode ? evt.keyCode : evt.charCode);
            var minChar = 2;
            if ($(this).val().length >= minChar) {
                $.post("<?php echo site_url('/reLocation/showLocationList'); ?>", {location_id: $('#locationCode').val(), warehouse_id: $('#select_warehouse').val(), zone_id: $('#zoneId').val()}, function(val) {
                    if (val.length > 0) {
                        var data = '';
                        $.each(val, function(index, value) {
                            data += '<li class="selected_item" value="' + value.Id + '" data-location_code="' + value.Location_Code + '" data-status="' + value.Product_Status + '"  data-sub_status="' + value.Product_Sub_Status + '" data-category="' + value.Category + '">' + value.Location_Code + '</li>';
                        });
                        $('#suggestions').show();
                        $('#autoSuggestionsList').html(data);

                        $('.selected_item').click(function() {

                            $('#showLocationTable').dataTable().fnAddData([
                                (data_index + 1)
                                        , $(this).data('location_code') + ' <a href="javascript:;" ONCLICK="showProduct(' + $(this).val() + ',\'' + $(this).data('location_code') + '\')"><?php echo img("css/images/icons/view.png"); ?></a>'
                                        , '<input type="text" class="required suggest_location" name="suggestion[]" placeholder="Search here.." value="" data-location="' + $(this).val() + '" data-status="' + $(this).data('status') + '" data-sub_status="' + $(this).data('sub_status') + '" data-category="' + $(this).data('Product_Category_Id') + '" />' //'<span>Click to edit</span>'

                                        , ''
                                        , ''
                                        , ''
                                        , ''
                                        , ''
                                        , "<a onclick=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>"]
                                    );
                            data_index += 1;
                            suggestLocation();
                            initProductTable();
                        });

                        if (ev_key == 13 && val.length == 1) {
                            $('.selected_item').trigger('click');
                            $('#locationCode').val('');
                            $('#suggestions').hide();
                        } else if (ev_key == 13 && val.length == 0) {
                            alert('not found any location !!');
                        } else if (ev_key == 13 && val.length > 1) {
                            $('#getBtn').trigger('click');
                        } else if (ev_key == 13 && val.length < 0) {
                            alert('Not found anythings');
                        }

                    }
                });
            }
        });
        // End By Ball

        $('#est_relocate_date').datepicker({onRender: function(date) {
                return date.valueOf() < now.valueOf() ? 'disabled' : '';
            }}).keypress(function(event) {
            event.preventDefault();
        }).on('changeDate', function(ev) {
            //$('#est_relocate_date').datepicker('hide');

            // Add By Akkarapol, 21/09/2013, เพิ่ม การเช็คว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
            if ($(this).val() != '') {
                $(this).removeClass('required');
                $(this).removeClass('error');// Add By Akkarapol, 08/10/2013, เพิ่มเพื่อว่า กรณีที่ มันเป็น textbox สีแดง แล้วเลือกวันที่ที่ต้องการ แล้ว ช่องแดงไม่หายไป เนื่องจาก class error มันไม่ถูกลบออก
            } else {
                $(this).addClass('required');
            }
            // Add By Akkarapol, 21/09/2013, เพิ่ม การเช็คว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง

        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        //$("#relocate_date").datepicker();
        $('#relocate_date').datepicker({onRender: function(date) {
                return date.valueOf() < now.valueOf() ? 'disabled' : '';
            }}).keypress(function(event) {
            event.preventDefault();
        }).on('changeDate', function(ev) {
            //$('#relocate_date').datepicker('hide');

            // Add By Akkarapol, 21/09/2013, เพิ่ม การเช็คว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
            if ($(this).val() != '') {
                $(this).removeClass('required');
                $(this).removeClass('error'); // Add By Akkarapol, 08/10/2013, เพิ่มเพื่อว่า กรณีที่ มันเป็น textbox สีแดง แล้วเลือกวันที่ที่ต้องการ แล้ว ช่องแดงไม่หายไป เนื่องจาก class error มันไม่ถูกลบออก
            } else {
                $(this).addClass('required');
            }
            // Add By Akkarapol, 21/09/2013, เพิ่ม การเช็คว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
            //
            //turn date.valueOf() < now.valueOf() ? 'disabled' : '';
        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        getSelectLocationOption();

        $('#getBtn').click(function() {
            allVals = new Array();
            var location_code = $('#locationCode').val();
            var warehouse = $('#select_warehouse').val();
            var dataSet = {post_val: location_code, warehouse: warehouse}

            $('#prdModalval').val(location_code);

            $.post('<?php echo site_url() . "/reLocation/locationList" ?>', dataSet, function(data) {
                $("#myModal .modal-body").html(data);
                var oTable = $('#defDataTable').dataTable({
                    "bJQueryUI": true,
                    "bSort": false,
                    "oSearch": {"sSearch": location_code},
                    "bRetrieve": true,
                    "bDestroy": true,
                    "sPaginationType": "full_numbers"
                });
            }, "html");
            //alert('popup');
            $('#myModal').show();  // put your modal id

        });

        $('#select_all').click(function() {
            var cdata = $('#defDataTable').dataTable();
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
            var cdata = $('#defDataTable').dataTable();
            $(cdata.fnGetNodes()).find(':checkbox').each(function() {
                $this = $(this);
                $this.attr('checked', false);
                selected.push($this.val());
                allVals.pop($this.val());
            });
            allVals = new Array();
        });

        $('#search_submit').click(function() {
            var dataSet = {
                post_val: allVals
            }
            $.post('<?php echo site_url() . "/reLocation/showLocationChecked" ?>', dataSet, function(data) {
                var suggest_location = "";
                var actual_location = "";
                var remark = "";
                var oTable = $('#showLocationTable').dataTable();
                var totalRows = oTable.fnSettings().fnRecordsTotal() + 1;
                $.each(data.locations, function(i, item) {
                    console.log(item);
                    var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";
                    oTable.fnAddData([
                        (totalRows + i)
                                , item.Location_Code + ' <a href="javascript:;" ONCLICK="showProduct(' + item.Location_Id + ',\'' + item.Location_Code + '\')"><?php echo img("css/images/icons/view.png"); ?></a>'
                                , '<input type="text" class="required suggest_location" name="suggestion[]" placeholder="Search here.." value="" data-location="' + item.Location_Id + '" data-status="' + item.Product_Status + '" data-sub_status="' + item.Product_Sub_Status + '" data-category="' + item.Dom_ID + '" />' //'<span>Click to edit</span>'
                                , actual_location
                                , remark
                                , ""
                                , ""
                                , ""
                                , del]
                            );
                });

                suggestLocation();
                initProductTable();
            }, "json");
            $('.modal.in').modal('hide');
            allVals = new Array();

        });

 		// Add By Ball
 		// Prevent unwant redirect pages
        window.onbeforeunload = function() {
            return "You have not yet saved your work.Do you want to continue? Doing so, may cause loss of your work?";
        };

        // If found key F5 remove unload event!!
        $(document).on('keydown keyup', function(e) {

            if (e.which === 116) {
                window.onbeforeunload = null;
            }

            if (e.which === 82 && e.ctrlKey) { // F5 with Ctrl
                window.onbeforeunload = null;
            }

        });
        // Add By Ball


    });

    function ValidateDate(dtValue)
    {
        var dtRegex = new RegExp(/\b\d{1,2}[\/-]\d{1,2}[\/-]\d{4}\b/);
        return dtRegex.test(dtValue);
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

    function getSelectLocationOption() {
        $.post('<?php echo site_url(); ?>/reLocation/genLocationSelectOption', function(data) {
            $('#location_list').val(data);
            initProductTable();
        });
    }

    function showProduct(location_id, location_code) {
        var dataSet = {location_id: location_id, location_code: location_code};
        var tmp = '';
        $('#prdModalval').val(location_id);
        $.post('<?php echo site_url() . "/reLocation/showProductInLocation" ?>', dataSet, function(data) {
            $('#boxDetail #myModalLabel').html(location_code + ' Product Detail ');
            $("#boxDetail .modal-body").html(data);

            $('#boxDetail .modal-body #defDataTable').dataTable({
                "bJQueryUI": true,
                "bAutoWidth": false,
                "bSort": false,
                "oSearch": {},
                "bRetrieve": true,
                "bDestroy": true,
                "sPaginationType": "full_numbers",
                 "aoColumnDefs": [
                             {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
                             {"sWidth": "7%", "sClass": "center", "aTargets": [1]},
                             {"sWidth": "5%", "sClass": "center", "aTargets": [2]},
                             {"sWidth": "20%", "sClass": "left_text", "aTargets": [3]},
                             {"sWidth": "7%", "sClass": "left_text", "aTargets": [4]},
                             {"sWidth": "7%", "sClass": "left_text", "aTargets": [5]},
                             {"sWidth": "7%", "sClass": "center", "aTargets": [6]},
                             {"sWidth": "7%", "sClass": "left_text", "aTargets": [7]},
                             {"sWidth": "7%", "sClass": "left_text", "aTargets": [8]},
                             {"sWidth": "7%", "sClass": "center", "aTargets": [9]},
                             {"sWidth": "7%", "sClass": "center", "aTargets": [10]},
                             {"sWidth": "7%", "sClass": "center", "aTargets": [11]},
                             {"sWidth": "5%", "sClass": "right_text", "aTargets": [12]}
                ]
            });

            if(!conf_pallet){ // check config built_pallet if It's false then hide a column Pallet Code
                $('#defDataTable').dataTable().fnSetColumnVis(ci_prod_pallet, false);
            }

            $('#boxDetail').modal('show');
        }, "html");


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

    function initProductTable() {

        $('#showLocationTable').dataTable({
            "bJQueryUI": true,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>'
        }).makeEditable({
            sUpdateURL: '<?php echo site_url() . "/reLocation/editDataTable"; ?>'
                    , "aoColumns": [
                null
                        , null
                        , null
                        , null
                        , {
                    onblur: 'submit',
                    event: 'click', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Remark นี้ โดยการ คลิกเพียง คลิกเดียว
                }
            ]
        });

        $("#showLocationTable tr td span").click(function(e) {
            var column = $(this).parent().index();
            var row = $(this).closest("tr")[0].rowIndex;
            if (column == ci_suggest_loc || column == ci_actual_loc) {
                var value = $(this).text();
                var code = $("#showLocationTable tr:eq(" + row + ") td:eq(1)").text();
                $('#showLocationTable tr td select').each(function() {
                    var other = $(this).val();
                    if (other == "") {
                        $(this).parent().html('<span>Click to edit</span>');
                    }
                    else
                    {
                        var new_value = '<span>' + other + '</span> <a href="javascript:;" ONCLICK="showProduct(\'\',\'' + other + '\')"><?php echo img("css/images/icons/view.png"); ?></a>';
                        $(this).parent().html(new_value);

                    }
                    initProductTable();
                });

                if (value == "Click to edit") {
                    var select_id = '';
                }
                else {
                    var select_id = value;
                }

                if (column == ci_actual_loc) {
                    var path = 'getSuggestLocationAll';
                }
                else if (column == ci_suggest_loc2) {
                    var path = 'getSuggestLocation';
                }

                $.post("<?php echo site_url(); ?>/reLocation/" + path, {location_id: '', location_code: code, select_id: select_id}, function(data) {
                    if (data != "") {
                        var new_data = '<select name="suggest_location" id="suggest_location" onchange="showValue(' + row + ',' + column + ',this.value);" class="required" >' + data + '</select>'
                        $("#showLocationTable tr:eq(" + row + ") td:eq(" + column + ")").html(new_data);
                    }

                });
            }

        });
        $('#showLocationTable').dataTable().fnSetColumnVis(ci_old_loc_name, false);
        $('#showLocationTable').dataTable().fnSetColumnVis(ci_sug_location_name, false);
        $('#showLocationTable').dataTable().fnSetColumnVis(ci_act_loc_name, false);
    }

    function showValue(row, cols, value) {
        if (value != "") {
            var new_value = '<span>' + value + '</span> <a href="javascript:;" ONCLICK="showProduct(\'\',\'' + value + '\')"><?php echo img("css/images/icons/view.png"); ?></a>';
        }
        else {
            var new_value = '<span>Click to edit<span>';
        }
        var cTable = $('#showLocationTable').dataTable();
        var new_r = row - 1;
        cTable.fnUpdate(new_value, new_r, cols);
        $("#showLocationTable tr:eq(" + row + ") td:eq(" + cols + ")").html(new_value);
        initProductTable();
    }

    function removeItem(obj) {
        var index = $(obj).closest("table tr").index();
        $('#showLocationTable').dataTable().fnDeleteRow(index);
        data_index -= 1;
    }

    function deleteItem(obj) {
        var index = $(obj).closest("table tr").index();
        var data = $('#showLocationTable').dataTable().fnGetData(index);
        $('#showLocationTable').dataTable().fnDeleteRow(index);
        var f = document.getElementById("form_receive");
        var prodDelItem = document.createElement("input");
        prodDelItem.setAttribute('type', "hidden");
        prodDelItem.setAttribute('name', "prod_del_list[]");
        prodDelItem.setAttribute('value', data);
        f.appendChild(prodDelItem);
    }

    function postRequestAction(module, sub_module, action_value, next_state, elm) {
        global_module = module;
	global_sub_module = sub_module;
	global_action_value = action_value;
	global_next_state = next_state;
	curent_flow_action = $(elm).data('dialog');

        if (sub_module == 'rejectAction' || sub_module == 'rejectAndReturnAction') {
             var statusisValidateForm = true;
        } else {
             var statusisValidateForm = validateForm();
        }

        if (statusisValidateForm === true) {

            //================================= Check Validation Form ==================================
            var rowData = $('#showLocationTable').dataTable().fnGetData();
            var num_row = rowData.length;
            if (num_row <= 0) {
                alert("Please Select Location Detail");
                return false;
            }

            $('#showLocationTable tr td select').each(function() {
                var other = $(this).val();
                $(this).parent().html('<span>Click to edit</span>');
                initProductTable();
            });

            var cTable = $('#showLocationTable').dataTable().fnGetNodes();
            var cells = [];
            var cells_act = [];
            for (var i = 0; i < cTable.length; i++)
            {
                // Get HTML of 3rd column (for example)
                var qty = $(cTable[i]).find("td:eq("+ci_suggest_loc+")").html();
                if (qty.indexOf("Click to edit") !== -1) {
                    cells.push($(cTable[i]).find("td:eq("+ci_suggest_loc+")").html());
                }

                var qty2 = $(cTable[i]).find("td:eq("+ci_actual_loc+")").html();
                if (qty2.indexOf("Click to edit") !== -1) {
                    cells_act.push($(cTable[i]).find("td:eq("+ci_actual_loc+")").html());
                }

                // Add By Akkarapol, 20/09/2013, เช็คว่า ถ้า Suggess Location กับ Actual Location ไม่ตรงกัน ให้กรอก Remark ด้วย
                var chk_if_remark = '<?= @$chk_if_remark ?>';
                if (chk_if_remark) {
                    if (qty != qty2) {
                        var chk_remark = $(cTable[i]).find("td:eq("+ci_remark+")").html();
                        if (chk_remark.indexOf("Click to edit") !== -1) {
                            alert('Please Check Your Information Remark');
                            return false;
                        }
                    }
                }
                // END Add By Akkarapol, 20/09/2013, เช็คว่า ถ้า Suggess Location กับ Actual Location ไม่ตรงกัน ให้กรอก Remark ด้วย

            }

            var all_location = cells.length;
            if (all_location != 0) {
                alert('Please select Suggest Location');
                return false;
            }

           if(present_state != 0){
                    if (cells_act.length != 0) {
                    alert('Please select Actual Location');
                    return false;
                }
           }

            //------------------------ End check validate Form ---------------------------------

            //============================ Start data_form ====================================
            var f = document.getElementById("form_receive");

            $('input[name="prod_list[]"]').remove();

            var actionType = document.createElement("input");
            actionType.setAttribute('type', "hidden");
            actionType.setAttribute('name', "action_type");
            actionType.setAttribute('value', action_value);
            f.appendChild(actionType);

            var toStateNo = document.createElement("input");
            toStateNo.setAttribute('type', "hidden");
            toStateNo.setAttribute('name', "next_state");
            toStateNo.setAttribute('value', next_state);
            f.appendChild(toStateNo);

            var oTable = $('#showLocationTable').dataTable().fnGetData();

            var flag = false;

            var error_time = 0;

            var suggest = $(".suggest_location");

            var actual = $(".actual_location");

            for (i in oTable) {

                var dataArray = new Array();
                var location_from = oTable[i][ci_location_from].split(" ");

                if (present_state == 0) {
                    var location_suggest = $(suggest[i]).val();
                } else {
                    var location_suggest = oTable[i][ci_suggest_loc].split(" ");
                    location_suggest = location_suggest['0'];
                }

                if (present_state == 2) {
                    var location_actual = oTable[i][ci_actual_loc]; // convert to jquery obj
                } else if (present_state == 0) {
                    location_actual = "";
                } else {
                    var location_actual = $(actual[i]).val();
                }

                var remark = oTable[i][ci_remark];

                dataArray[ci_item_id] = oTable[i][ci_item_id];
                dataArray[ci_location_from] = location_from['0'];
                dataArray[ci_suggest_loc] = location_suggest;
                dataArray[ci_actual_loc] = location_actual;
                dataArray[ci_remark] = remark;

                dataArray = dataArray.join(separator); // Add By Akkarapol, 22/01/2014, Set dataArray to join with SEPARATOR

                if (sub_module != 'rejectAction' && sub_module != 'rejectAndReturnAction') {    // add for reject document : by kik : 20131226
                    if (present_state > 0 && location_actual == "") {
                        alert('Please specific actual location!!');
                        return false;
                    }


                    if ((location_suggest.toUpperCase() != location_actual.toUpperCase()) && remark == "" && present_state > 0) {
                        alert('Please specific remark, because actual location and suggest location miss match');
                        return false;
                    } else {
                        var prodItem = document.createElement("input");
                        prodItem.setAttribute('type', "hidden");
                        prodItem.setAttribute('name', "prod_list[]");
                        prodItem.setAttribute('value', dataArray);
                        f.appendChild(prodItem);
                    }
                }// end check sub module for reject return

                //add for set index in dataTable : by kik : 20140115
                $.each(ci_list, function(i, obj) {
                    var ci_item = document.createElement("input");
                    ci_item.setAttribute('type', "hidden");
                    ci_item.setAttribute('name', obj.name);
                    ci_item.setAttribute('value', obj.value);
                    f.appendChild(ci_item);
                });
                //end add for set index in dataTable : by kik : 20140115
            }

            global_data_form  = $("#form_receive").serialize();
            //---------------------------------- End data_form ---------------------------------

            //======================== Start check validate Data and save data ===============================
            var mess = '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>';
            $('#div_for_alert_message').html(mess);
            $('#div_for_modal_message').modal('show').css({
                'margin-left': function() {
                                return ($(window).width() - $(this).width()) / 2;
                        }
             });
             //---------------------------------- End check validate Data and save data ---------------------------------

//            if (confirm("Are you sure to action " + action_value + "?")) {
//
//                var message = "";
//                var timeout = setTimeout(function(){
//                    $.post("<?php echo site_url(); ?>" + "/" + module + "/" + sub_module, global_data_form, function(data) {
//                        switch (data.status) {
//                            case 'C001':
//                                message = "Save Re-Location Complete";
//                                break;
//                            case 'C002':
//                                message = "Confirm Re-Location Complete";
//                                break;
//                            case 'C003':
//                                message = "Approve Re-Location Complete";
//                                break;
//                            case 'C004':        // add by kik : 07-01-2013
//                                window.onbeforeunload = null;
//                                message = "Reject Re-Location Complete";
//                                break;
//                            case 'C005':        // add by kik : 07-01-2013
//                                window.onbeforeunload = null;
//                                message = "Reject and Return Re-Location Complete";
//                                break;
//                            case 'E001':
//                                message = "Save Re-Location Incomplete";
//                                break;
//                        }
//                        alert(message);
//                        url = "<?php echo site_url(); ?>/reLocation";
//                        redirect(url)
//                    }, "json");
//                }, error_time);
//
//
//            } // close confirm action
        }
        else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
    }

    function remove_require ()
    {
		$(".required").on("focusout", function(){
			console.log($(this).length);
		});
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
        	window.onbeforeunload = null;
            url = "<?php echo site_url(); ?>/reLocation";
            redirect(url)
        }
    }

    function validateForm() {
        //validate engine
        var status;
        $("form").each(function() {
            $(this).validate();
            $(this).valid();
            status = $(this).valid();
        });
        return status;
        //end of validate engine
    }

   // Add By Akkarapol, 16/10/2013, เพิ่มฟังก์ชั่นสำหรับการ Print Relocation Job (Location,Product)
   function exportFile(file_type) {
        if (file_type == 'PDF') {
            //ADD BY POR 2014-03-11  เพิ่มให้ส่งค่าไปแบบ realtime
            var backupForm = document.getElementById('form_flow_id').innerHTML;
            var f = document.getElementById('form_flow_id');

            var oTable = $('#showLocationTable').dataTable().fnGetData();

            // get actual location by class
            var actual_location = $(".actual_location");

            for (i in oTable) {

                //กรณี confirm
                if($(actual_location[i]).val()!==undefined){
                    // read actual_location by index row
                    oTable[i][ci_act_loc_name] = $(actual_location[i]).val();
                }

                var prod_data = oTable[i].join(separator);
                var prodItem = document.createElement("input");
                prodItem.setAttribute('type', "hidden");
                prodItem.setAttribute('name', "prod_list[]");
                prodItem.setAttribute('value', prod_data);
                f.appendChild(prodItem);

            }

            //Worker Name
            var worker_id = document.createElement("input");
            worker_id.setAttribute('type', "hidden");
            worker_id.setAttribute('name', "worker_id");
            worker_id.setAttribute('value', $('[name="worker_id"]').val());
            f.appendChild(worker_id);

            //Est.Re-Locate date
            var est_relocate_date = document.createElement("input");
            est_relocate_date.setAttribute('type', "hidden");
            est_relocate_date.setAttribute('name', "est_relocate_date");
            est_relocate_date.setAttribute('value', $('#est_relocate_date').val());
            f.appendChild(est_relocate_date);

            //Re-Locate date
            var relocate_date = document.createElement("input");
            relocate_date.setAttribute('type', "hidden");
            relocate_date.setAttribute('name', "relocate_date");
            relocate_date.setAttribute('value', $('#relocate_date').val());
            f.appendChild(relocate_date);

            //Re-Locate No
            var relocation_no = document.createElement("input");
            relocation_no.setAttribute('type', "hidden");
            relocation_no.setAttribute('name', "relocation_no");
            relocation_no.setAttribute('value', $('#relocation_no').val());
            f.appendChild(relocation_no);

            // Flow ID
            var flow_id = document.createElement("input");
            flow_id.setAttribute('type', "hidden");
            flow_id.setAttribute('name', "flow_id");
            flow_id.setAttribute('value', $('input[name="flow_id"]').val());
            f.appendChild(flow_id);

            //key
            $.each(ci_list, function(i, obj) {
                var ci_item = document.createElement("input");
                ci_item.setAttribute('type', "hidden");
                ci_item.setAttribute('name', obj.name);
                ci_item.setAttribute('value', obj.value);
                f.appendChild(ci_item);
            });

            $("#form_flow_id").attr('action', "<?php echo site_url(); ?>" + "/report/exportReLocationPDF")
        }
        $("#form_flow_id").submit();
        document.getElementById("form_flow_id").innerHTML = backupForm;


    }
    // END Add By Akkarapol, 16/10/2013, เพิ่มฟังก์ชั่นสำหรับการ Print Relocation Job (Location,Product)

</script>
<style>
    #myModal,
    #boxDetail{
        width: 85%!important;	/* SET THE WIDTH OF THE MODAL */
        top:43%!important;
        margin-left: -42.5%!important;

    }


    .suggestionsBox {
        position: absolute;
        width: 200px;
        background-color: #f2f2f2;
        -moz-border-radius: 2px;
        -webkit-border-radius: 2px;
        border: 1px solid #333;
        color:#333;
        /*margin-top: 10px;*/
        margin-top:1px;
        margin-right: 0px;
        margin-bottom: 0px;
        margin-left: 0px;
        padding-right: 2px;
        padding-left: 2px;
        font-size:11px;
        z-index:100;
    }

    .suggestionList {
        margin: 0px;
        padding-top: 0px;
        padding-right: 2px;
        padding-bottom: 2px;
        padding-left: 2px;
        height:200px;
        overflow:scroll;
    }

    .suggestionList ul {
        list-style:none;
    }

    .suggestionList li {
        margin: 0px 0px 3px 0px;
        padding: 3px;
        cursor: pointer;
        list-style-type: none;
        /*background-color: #E8E8E8;*/
        color:#000000;
    }

    .suggestionList li:hover {
        background-color: #659CD8;
    }

    #suggestion_div {
        width: 220px;
        height: 170px;
        display: none;
        z-index: 2000;
        position: absolute;
        background-color: #FFF;
        overflow-y:hidden;
    }
</style>

<div class="well">
    <form id='form_flow_id' method='post' action="" target="_blank" > </form>
    <form id="form_receive" method=post action="" class="">
        <input type="hidden" name="location_list" id="location_list" />
        <fieldset>
            <legend>&nbsp;Order&nbsp;</legend>
            <?php
            $act_re = '';
            if (isset($flow_id)) {
                echo form_hidden('flow_id', $flow_id);
                $act_re = 'class="required" ';
            } else {
                $act_re = ' disabled';
            }

            if (!isset($owner_id)) {
                $owner_id = "";
            }
            if (!isset($renter_id)) {
                $renter_id = "";
            }
            if (!isset($assigned_id)) {
                $assigned_id = "";
            }
            if (!isset($doc_type)) {
                $doc_type = "";
            }
            if (!isset($document_no)) {
                $document_no = "";
            }
            if (!isset($doc_relocate)) {
                $doc_relocate = "";
            }
            if (!isset($est_action_date)) {
                $est_action_date = date("d/m/Y");
            }
            if (!isset($act_action_date)) {
                $act_action_date = "";
            }
            if (!isset($process_type)) {
                $process_type = $data_form['process_type'];
            }

             if ((!isset($is_urgent)) || ($is_urgent != 'Y')) {
                $is_urgent = false;
            } else {
                $is_urgent = true;
            }


            //echo ' order id = '.$order_id;
            ?>
            <?php echo form_hidden('process_id', $process_id); ?>
            <?php echo form_hidden('present_state', $present_state); ?>
            <?php echo form_hidden('process_type', $process_type); ?>
            <?php echo form_hidden('user_id', $user_id); ?>
            <?php echo form_hidden('owner_id', $owner_id);  # add by  kik : 2013-12-12 ?>
            <?php echo form_hidden('renter_id', $renter_id);# add by  kik : 2013-12-12 ?>

            <table width="98%">
                <tr>
                    <td align="right">Est.Re-Locate date</td>
                    <td align="left">
                        <?php echo form_input('est_relocate_date', $est_action_date, 'id="est_relocate_date" placeholder="Est.Re-Locate date" class="required" '); ?>
                    </td>
                    <td align="right">Re-Locate date</td>
                    <td align="left">
                        <?php echo form_input('relocate_date', $act_action_date, 'id="relocate_date" placeholder="Action Re-Locate date" ' . $act_re); ?>
                    </td>
                    <td align="right"></td>
                    <td align="left"></td>
                </tr>

                <tr valign='top'>
                    <td align="right" >Re-Locate No.</td>
                    <td align="left" >
                        <input type="text" placeholder="Auto Generate" id="relocation_no" name="relocation_no" disabled value="<?php echo $doc_relocate; ?>" />
                        <br>
                        <!--//add for ISSUE 3312 : by kik : 20140120-->
                        <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font></td>
                    </td>
                    <td align="right">Worker Name</td>
                    <td align="left" ><?php echo form_dropdown('worker_id', $worker_list, $worker_id, 'class="required" '); ?></td>
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
        <legend>&nbsp;Order Detail&nbsp;</legend>
        <?php
        if ($present_state == 0) {
            ?>
            <div style="height: 30px; width: 100%; margin: 10px 0;">
                <div style="float: left; width: 85px; height: 30px; line-height: 28px; margin-left: 10px;">Warehouse : </div>
                <div style="float: left; width: 240px; height: 30px;">
                    <select name="select_warehouse" id="select_warehouse" >
                        <?php
                        foreach ((array) $warehouse_list as $index => $value) :
                            echo "<option value='" . $index . "'>" . $value . "</option>";
                        endforeach;
                        ?>
                    </select></div>
                <div style="float: left; width: 100px; height: 30px; line-height: 28px; display: none;">Zone : </div>
                <div style="float: left; width: 240px; height: 30px; display: none;">
                    <select id="zoneId" name="zoneId">
                        <option>Please select warehouse</option>
                    </select>&nbsp;
                </div>
                <div style="float: left; width: 100px; height: 30px; line-height: 28px; margin-left: 10px;">Location Code : </div>
                <div style="float: left; width: 240px; height: 30px;">
                    <?php echo form_input("locationCode", "", "id='locationCode' placeholder='Search By Location' style='text-transform:uppercase;'"); ?>&nbsp;
                    <div class="suggestionsBox" id="suggestions" style="display:none;">
                        <div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div>
                    </div>
                </div>
                <div style="float: left; width: 100px; height: 30px;"><a href="#myModal" role="button" class="btn success" data-toggle="modal" id="getBtn">Get Detail</a></div>
            </div>
            <?php
        }
        ?>
        <table width="100%">
            <tr align="center" >
                <td align="center" colspan="6" id="showDataTable" >
                    <table width="100%"  align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showLocationTable" >
                        <thead>
                            <?php
                            $str_header = "";
                            //echo 'state = '.$present_state;
                            foreach ($show_column as $column) {
                                if ($column == "Delete") {
                                    if ($present_state == 0) {
                                        $str_header .= "<th>" . $column . "</th>";
                                    }
                                } else {
                                    //echo '<br> else ';
                                    $str_header .= "<th>" . $column . "</th>";
                                }
                            }
                            ?>
                            <tr><?php echo $str_header; ?></tr>
                        </thead>
                        <?php
                        if (isset($order_detail)) {
                            //p($order_detail);
                            $str_body = "";
                            $i = 1;
                            foreach ($order_detail as $order_column) {
                                //$order_id = $order_column->Order_Id;

                                if (!empty($order_column['act_location'])) {
                                    $act_loc = $order_column['act_location'];
                                } else {
                                    $act_loc = "<input type=\"text\" class=\"required actual_location\" placeholder=\"Search here..\" value=\"\" data-act_location='' />";
                                }

                                // Add By Ball
                                if (!empty($order_column['to_location'])) {
                                    $to_location = $order_column['to_location'] . " <a href=\"javascript:;\" ONCLICK=\"showProduct('','" . $order_column['to_location'] . "')\">" . img("css/images/icons/view.png") . "</a>";
                                } else {
                                    $to_location = "";
                                }

                                $str_body .= "<tr>";
                                $str_body .= "<td>" . $i . "</td>";
                                $str_body .= "<td>" . $order_column['from_location'] . " <a href=\"javascript:;\" ONCLICK=\"showProduct('','" . $order_column['from_location'] . "')\">" . img("css/images/icons/view.png") . "</a></td>";
                                $str_body .= "<td>" . $to_location . "</td>";
                                $str_body .= "<td>" . $act_loc . "</td>";
                                $str_body .= "<td>" . $order_column['remark'] . "</td>";
                                $str_body .= "<td>" . $order_column['from_location']. "</td>";
                                $str_body .= "<td>" . $order_column['to_location']. "</td>";
                                $str_body .= "<td>" . $order_column['act_location']. "</td>";
                                if ($present_state == 0) {
                                    $str_body .= "<td><a ONCLICK=\"deleteItem(this)\" >" . img("css/images/icons/del.png") . "</a></td>";
                                }

                                $str_body .= "</tr>";
                                $i++;
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
        <h3 id="myModalLabel">Location Detail</h3>
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

<div style="min-height:500px;padding:5px 10px;" id="boxDetail" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
        <h3 id="myModalLabel">Product Detail</h3>

    </div>
    <div class="modal-body"></div>
    <div class="modal-footer">
        <div style="float:left;">
        </div>
        <div style="float:right;">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
    </div>
</div>
<div id="suggestion_div">
    <div class="suggestionList" id="suggestionLocationList"></div>
</div>

<!--call element Show modal message alert modal : add by kik : 06-03-2014-->
<?php $this->load->view('element_modal_message_alert'); ?>