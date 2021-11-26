<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<!--<script src="<?php // echo base_url() . "js/chart/Chart.js"                                                                             ?>"></script>-->
<!--<script src="//<?php // echo base_url() . "js/chart/utils.js"                                                                             ?>"></script>-->

<style>
    #myModal {
        width: 1024px; /* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -512px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
    }
    #report{
        margin:5px;
        text-align:center;
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

    .req_product{border: 1px solid #FF0000 !important;}
</style>

<script>
    var DataDetailList = {};
    $(document).ready(function () {
        var data_List = "";
        $('#myTab a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        $("#pdfshow").hide();
        $("#excelshow").hide();

        $("#search").click(function () {
            // hide Table Location 
            $("#div_show_Detail").css("display", "none");
            $("#report").removeAttr("style");

            var oTable = $('#defDataTable2').dataTable();
            if (oTable != null) {
                oTable.fnDestroy();
            }
            $("#defDataTable2").removeAttr("style");

            $('#report').html('<img src="<?php echo base_url() ?>/images/ajax-loader.gif" />');

            // Get Report Location remain all
            $.post("<?php echo site_url("report_checkLocationBalance/getDataTable") ?>", {"show_type": $("#show_type option:selected").val()}, function (data) {
                var newHTML = [];
                var i = 1;
                DataDetailList = data.data_detail;
                
                $.each(data.data_detail, function (key, value) {
                    var UsedPallet = 0;
                    var RemainPallet = 0;
                    var UsedItem = 0;
                    var RemainItem = 0;
                    var Balance_Qty_total = 0;
                    var company = [];
                    var uniqueArray;
                    var PalletCode = [];
                    var Palletunique;
                    // console.log(typeof value.Detail);
                    // return;
                    if (typeof value.Detail != "undefined") {
                        // console.log(value.Detail);
                        // console.log(value.Detail.Pallet_Code);
                            // return;
                        $.each(value.Detail, function (key1, value1) {
                            if (typeof value1.Company != "undefined") {
                                if (DataDetailList[key]["Detail"][key1].Company == value1.Company) {
                                    company.push(value1.Company);
                                } else {
                                    company.push(value1.Company);
                                }
                            }

                            if (jQuery.isEmptyObject(value1.Pallet_Code) != true) {
                                // UsedPallet += parseInt(value.Capacity_Max_Pallet);
                                // UsedPallet = UsedPallet + 1;
                                PalletCode.push(value1.Pallet_Code);
                                
                               // RemainPallet = 0;
                              //  UsedItem = 0;
                            //    RemainItem = value.Max_Capacity ;
                            //    consloe.log(RemainItem);
                            //    return;
                            } else {
                               // UsedPallet += 0;
                                RemainPallet = value.Capacity_Max_Pallet;
                                Balance_Qty_total = parseFloat(Balance_Qty_total) + parseFloat(value1.Balance_Qty);
                                UsedItem = Balance_Qty_total;
                                
                                // RemainItem = (parseFloat(value.Max_Capacity) - parseFloat(Balance_Qty_total)).toFixed(4);
                                // RemainItem = parseFloat(RemainItem);
                               
                                // consloe.log(RemainItem);
                            }
                        });
                        

                        Palletunique =  PalletCode.filter((v, i, a) => a.indexOf(v) === i);
                        UsedPallet = Palletunique.length;
                        // console.log(UsedPallet);
                        RemainItem =  (parseFloat(value.Max_Capacity) - parseFloat(UsedItem)).toFixed(4) ;
                        RemainItem = parseFloat(RemainItem);
                        RemainPallet = value.Capacity_Max_Pallet - UsedPallet;
                    } else {

                        
                        //company = "";
                        UsedPallet = 0;
                        // UsedPallet = 888;
                        RemainPallet = value.Capacity_Max_Pallet;
                        // RemainPallet = 666;
                        UsedItem = 0;
                        RemainItem = value.Max_Capacity;
                    }

                    uniqueArray = removeDuplicates(value.Detail, "Product_Code");
                    var str_product = "";
                    var str_un = "";

                    if (jQuery.isEmptyObject(uniqueArray) != true) {
                        str_product = uniqueArray["Product_code"].replace("undefined,", "");
                        str_un = uniqueArray["Un_code"].replace("undefined,", "");
                        // str_un = uniqueArray["ProductGroup_NameEN"].replace("undefined,", "");
                    }

                    var str_company = company.filter((v, i, a) => a.indexOf(v) === i);

                    var UsedPallet_Style = "";
                    var UsedItem_Style = "";

                    if (UsedPallet > value.Capacity_Max_Pallet) {
                        UsedPallet_Style = "color:red";
                    }

                    if (UsedItem > value.Max_Capacity) {
                        UsedItem_Style = "color:red";
                    }

                    //var str_company = company.substr(1);
                    newHTML.push("<tr>" +
                            "<td>" + value.Location_Code + "</td>" +
                            "<td style='word-break: break-all;'>" + str_product + "</td>" +
                            "<td style='word-break: break-all;'>" + str_un + "</td>" +
                            "<td style='background: rgba(124, 252, 0, .5)'>" + value.Capacity_Max_Pallet + "</td>" +
                            "<td style='background: rgba(124, 252, 0, .5);" + UsedPallet_Style + " '>" + UsedPallet + "</td>" +
                            "<td style='background: rgba(124, 252, 0, .5)'>" + RemainPallet + "</td>" +
                            "<td style='background: rgba(255, 215, 0, .5)'>" + value.Max_Capacity + "</td>" +
                            "<td style='background: rgba(255, 215, 0, .5); " + UsedItem_Style + "' >" + UsedItem + "</td>" +
                            "<td style='background: rgba(255, 215, 0, .5)'>" + RemainItem + "</td>" +
                            "<td>" + str_company + "</td>" +
                            "<td hidden><input type='hidden' value='" + value.Location_Code + "' class='btnlocation'> </td>" +
                            "</tr>"
                            );
                    i++;
                });
                $("#location_list").html(newHTML.join(""));

                $('#defDataTable2').DataTable({
                    "bJQueryUI": true,
                    "bSortable": false,
                    "order": [[1, 'asc']],
                    "paging": true,
                    "pagingType": "full_numbers"
                });

                // Show Table Location 
                $("#div_show_Detail").removeAttr("style");
                $("#report").css("display", "none");

                // Show Button PDF and Excel 
//                $("#pdfshow").show();
                $("#excelshow").show();

                // Function Cilck Detail
                $('#defDataTable2 tbody').on('click', 'tr', function () {
                    $("#small_locationcode").html(); // Clear Location Code 

                    // Clear DataTable 
                    var oTable = $('#modal_data_table').dataTable();
                    if (oTable != null) {
                        oTable.fnDestroy();
                    }
                    $("#modal_data_table").removeAttr("style");

                    // Get Location Id 
                    var Location_Code = $(this).closest('tr').find('.btnlocation').val();

                    // Set auto size modal
                    $('#myModal').modal('show').css({
                        'width': function () {
                            return ($(document).width() * .9) + 'px'; // make width 90% of screen
                        },
                        'margin-left': function () {
                            return -($(this).width() / 2); // center model
                        }
                    });

                    var newHTML = [];
                    var i = 1;
                    //console.log(DataDetailList);
                    $.each(DataDetailList, function (key, value) {
                        if (value.Location_Code == Location_Code) {
                            if (typeof value.Detail != "undefined") {
                                $.each(value.Detail, function (key1, value1) {
                                    var Pallet_Code = "";
                                    if (jQuery.isEmptyObject(value1.Pallet_Code) != true) {
                                        Pallet_Code = value1.Pallet_Code;
                                    } else {
                                        Pallet_Code = "";
                                    }

                                    newHTML.push("<tr>" +
                                            "<td>" + i + "</td>" +
                                            "<td>" + value1.Company + "</td>" +
                                            "<td>" + value1.Product_Code + "</td>" +
                                            "<td>" + value1.Product_Name + "</td>" +
                                            "<td>" + value1.Product_Status + "</td>" +
                                            "<td>" + value1.Product_Sub_Status + "</td>" +
                                            "<td>" + value1.Receive_Date + "</td>" +
                                            "<td>" + value1.Product_Lot + "</td>" +
                                            "<td>" + value1.Product_Serial + "</td>" +
                                            "<td>" + value1.Product_Mfd + "</td>" +
                                            "<td>" + value1.Product_Exp + "</td>" +
                                            "<td>" + value1.Balance_Qty + "</td>" +
                                            "<td>" + Pallet_Code + "</td>" +
                                            "</tr>"
                                            );
                                    i++;
                                });

                            }
                            $("#small_locationcode").html("Location code : " + value.Location_Code);
                        }
                    });
                    $("#detail-body").html(newHTML.join(""));
                    $('#modal_data_table').dataTable(
                            {
                                "bJQueryUI": true,
                                "bSortable": false,
                                "order": [[0, "desc"]]
                            }
                    );
                });

            }, "json");
        });

    }); // END Jquery Ready

    function removeDuplicates(originalArray, prop) {
        var newArray = [];
        var lookupObject = {};
        var list = [];

        for (var i in originalArray) {
            lookupObject[originalArray[i][prop]] = originalArray[i];
        }

        for (i in lookupObject) {
            newArray.push(lookupObject[i]);
        }

        $.each(lookupObject, function (index, value) {
            list["Product_code"] += "," + value.Product_Code;
            list["Un_code"] += "," + (value.UNno).toString();
            // list["ProductGroup_NameEN"] += "," + (value.ProductGroup_NameEN).toString();
//            if (list.length > 0) {
//                list["Product_code"] += "," + value.Product_Code;
//                list["Un_code"] += "," + (value.UNno).toString();
//            } else {
//                list["Product_code"] = value.Product_Code;
//                list["Un_code"] += (value.UNno).toString();
//            }
//            newArray.push(list);
//            newArray.push(lookupObject[index]);
//
//            str_product += value.Product_Code + ",";
//            str_un += value.UNno + ",";
        });

        return list;
    }


    function exportFileLocation(file) {
        var url = "<?php echo site_url("/report_checkLocationBalance/exportLocationRemain"); ?>" + "?show_type=" + $("#show_type option:selected").val() + "&FileType=" + file;
        var str_url = url.replace("%20", "");
        window.location.href = str_url, '_blank';
    }

</script>

<TR class="content" style='height:100%' >
    <TD>
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="frmLocationRemain" name="frmLocationRemain" >
            <fieldset style="margin:0px auto;">
                <legend>Search Criteria</legend>
                <table cellpadding="1" cellspacing="1" border="0" style="width:98%; margin:15px auto;" >
                    <tr>  
                        <td align="right">Show Type : </td>
                        <td>
                            <select name="show_type" id="show_type">
                                <option value="show_all" selected>All</option>
                                <option value="show_used">Used</option>
                                <option value="show_available">Available</option>                              
                            </select>                       
                        </td>
                        <td align="left">
                            <input type="submit" style="display: none;">
                            <input type="button" name="search" value="Search" id="search" class="button dark_blue" />                                          
                        </td>
                    </tr>                 
                </table>
<!--                <input type="hidden" name="queryText" id="queryText" value=""/>
                <input type="hidden" name="search_param" id="search_param" value=""/>-->
            </fieldset>
        </form>    

        <fieldset style="margin:0px auto;">
            <legend>Search Result</legend>
            <div id="report" style="margin:10px;">
                Please click search
            </div>       

            <div id="div_show_Detail" style="display: none;">

                <table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
                    <thead>
                        <tr>                                                             
                            <th rowspan="2" id="location_code">Location Code</th>
                            <th rowspan="2" id="product_name" style="width: 20%">Product Code</th>
                            <th rowspan="2" id="un_class">Material Group</th>
                            <th colspan="3" id="hd-Pallet">Pallet</th>
                            <th colspan="3"  id="hd-Item">Item</th>
                            <th rowspan="2" id="location_code" style="width: 20%">Company</th>
                            <th rowspan="2" id="location_code" hidden>location_id</th>
                        </tr>
                        <tr>
                            <th>Max Capacity</th>
                            <th>Used</th>
                            <th>Remain</th>
                            <th>Max Capacity</th>
                            <th>Used</th>
                            <th>Remain</th>
                        </tr>
                    </thead>
                    <tbody id="location_list">

                    </tbody>
                </table>

                <div class="modal fade" id="myModal" role="dialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                                <h3 class="modal-title">Location Detail <small id="small_locationcode"> </small></h3>
                            </div>
                            <div class="modal-body">
                                <table id="modal_data_table" cellpadding="0" cellspacing="0" border="0" aria-describedby="modal_data_table_info" class=" display dataTable well" style="max-width: none;">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Company.</th>
                                            <th>Product Code</th>
                                            <th>Product Name</th>
                                            <th>Product Status</th>
                                            <th>Product Sub Status</th>
                                            <th>Receive Date</th>
                                            <th>Batch</th>
                                            <th>Serial</th>
                                            <th>MFD</th>
                                            <th>EXP</th>
                                            <th>Balance Qty</th>
                                            <th>Pallet Code</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail-body">

                                    </tbody>
                                </table>
                            </div>                            
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>
                <!-- /.modal -->

            </div>
        </fieldset>
    </TD>
</TR>


