<style>
    .col-left {
        float: left; text-align: center; padding: 20px; width: 45%; border: 1px solid #e7ecf1 !important; margin-left: 10px; display: none;
    }

    .col-right {
        float: left; text-align: center; padding: 10px 10px 10px 0; margin-left: 26px; width: 45%; min-height: 450px; border: 1px solid #e7ecf1 !important; display: none;
    }

    .report_printing {
        display: none;
    }

    #tblData_wrapper {
        width: 100%;        
    }

    .cl_left {
        text-align: left !important;
        text-indent: 20px;
    }

    #tblData_length , #tblData_filter {
        margin-bottom: 10px;
    }

    table.dataTable tr.odd {
        background-color: #EEE !important;
    }
    
    table.dataTable tr.even {
        background-color: #FFF !important;
    }

    /***
    Dashboard Stats 2
    ***/
    .dashboard-stat2 {
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        -ms-border-radius: 4px;
        -o-border-radius: 4px;
        border-radius: 4px;
        background: #fff;
        padding: 15px 7px 0px 7px;
        margin-bottom: 20px; }
    .dashboard-stat2.bordered {
        border: 1px solid #e7ecf1; }
    .dashboard-stat2 .display {
        margin-bottom: 20px; }
    .dashboard-stat2 .display:before, .dashboard-stat2 .display:after {
        content: " ";
        display: table; }
    .dashboard-stat2 .display:after {
        clear: both; }
    .dashboard-stat2 .display .number {
        float: left;
        display: inline-block; }
    .dashboard-stat2 .display .number h3 {
        margin: 0 0 2px 0;
        padding: 0;
        font-size: 30px;
        font-weight: 400; }
    .dashboard-stat2 .display .number h3 > small {
        font-size: 23px; }
    .dashboard-stat2 .display .number small {
        font-size: 14px;
        color: #AAB5BC;
        font-weight: 600;
        text-transform: uppercase; }
    .dashboard-stat2 .display .icon {
        display: inline-block;
        float: right;
        padding: 7px 0 0 0; }
    .dashboard-stat2 .display .icon > i {
        color: #cbd4e0;
        font-size: 26px; }
    .dashboard-stat2 .progress-info {
        clear: both; }
    .dashboard-stat2 .progress-info .progress {
        margin: 0;
        height: 4px;
        clear: both;
        display: block; }
    .dashboard-stat2 .progress-info .status {
        margin-top: 5px;
        font-size: 11px;
        color: #AAB5BC;
        font-weight: 600;
        text-transform: uppercase; }
    .dashboard-stat2 .progress-info .status .status-title {
        float: left;
        display: inline-block; }
    .dashboard-stat2 .progress-info .status .status-number {
        float: right;
        display: inline-block; }
    .col-md-3 {
        float: left;
        position: relative;
        min-height: 1px;
        padding-left: 6px;
        padding-right: 5px;
        width: 23%; }
    .col-md-3:first-child {padding-left: 12px;}
</style>
<script lang="javascript">


    var fS = false;
    var base_url = '<?php echo base_url($parameter['path']); ?>/';

    function secondsTimeSpanToHMS(s) {
        var h = Math.floor(s / 3600); //Get whole hours
        s -= h * 3600;
        var m = Math.floor(s / 60); //Get remaining minutes
        s -= m * 60;
        return h + ":" + (m < 10 ? '0' + m : m) + ":" + (s < 10 ? '0' + s : s); //zero padding on minutes and seconds
    }

    // Chart
    var pie_data = {
        labels: [
            "Inbound",
            "Outbound",
        ],
        datasets: [
            {
                data: [],
                backgroundColor: [
                    "#FF6384",
                    "#36A2EB"
                ],
                hoverBackgroundColor: [
                    "#FF6384",
                    "#36A2EB"
                ]
            }]
    };

    var graph_data = {
        labels: ["Open", "Receive", "Putaway", "Picking", "Dispatch", "Close"],
        datasets: [{
                label: 'Chart for average work hour',
                data: [],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                borderColor: [
                    'rgba(255,99,132,1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
    };

    $(document).ready(function () {

        var myPieChart = new Chart($("#chart_overall"), {
            type: 'pie',
            data: pie_data
        });

        var myChart = new Chart($("#chart_detail"), {
            type: 'bar',
            data: graph_data
        });

        // init datepicker.
        $("#from_date,#to_date").datepicker({format: 'yyyy-mm-dd'}).keypress(function (event) {
            event.preventDefault();
        }).on('changeDate', function (e) {
            e.preventDefault();
        }).bind("cut copy paste", function (e) {
            e.preventDefault();
        });

        $("#btn_clear").click(function () {

            swal({
                title: 'Please Confirm!',
                text: "Are you want to reset criteria to default?",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (confirm) {
                if (confirm) {
                    $("#from_date").val("");
                    $("#to_date").val("");
                    $("#inbound_process").val("");
                    $("#outbound_process").val("");
                }
            })
        });

        // Search
        $("#btn_search").click(function (e) {
            var params = {
                from_date: $("#from_date").val(),
                to_date: $("#to_date").val()
            };

            if (fS) { // If service are processing.
                alert('Processing, Please wait');
                return;
            }

            fS = true; // start flag for response.

            $.post("<?php echo base_url('index.php/report_kpi/search'); ?>", params, function (data) {

                fS = false; // complete response.
                $(".response").show();

                // variable set default
                var in_stat = 0;
                var out_stat = 0;
                var st_open = 0;
                var st_rcv = 0;
                var st_put = 0;
                var st_pick = 0;
                var st_dis = 0;
                var st_close = 0;

                var operation = $("#operation").val();

                // set header of table.
                var result = '<table class="well table table-bordered" id="tblData" style="width: 100%;">';
                result += '<thead>';
                result += '<tr>';
                result += '<th>Document</th>';
                result += '<th>Operation</th>';
                result += '<th>Start</th>';
                result += '<th>End</th>';
                result += '<th>Time</th>';
                result += '</thead>';
                result += '</tr>';
                result += '<tbody>';

                // loop result.
                $.each(data.RESPONSE, function (i, v) {

                    var type = v.Document_No.substr(0, 3);

                    if (type == "GRN" || type == "PAR") {

                        if (operation == 1 || operation == "") {
                            result += '<tr>';
                            result += '<td>' + v.Document_No + '</td>';
                            result += '<td class="cl_left">Open Container</td>';
                            result += '<td class="cl_left">' + v.Container_Start + '</td>';
                            result += '<td class="cl_left">' + v.Container_End + '</td>';
                            result += '<td>' + secondsTimeSpanToHMS(v.KPI_Container) + '</td>';
                            result += '</tr>';

                            if (v.KPI_Container != "null") {
                                st_open += v.KPI_Container;
                            }
                        }

                        if (operation == 2 || operation == "") {
                            result += '<tr>';
                            result += '<td>' + v.Document_No + '</td>';
                            result += '<td class="cl_left">Receive</td>';
                            result += '<td class="cl_left">' + v.Pallet_Start + '</td>';
                            result += '<td class="cl_left">' + v.Pallet_End + '</td>';
                            result += '<td>' + secondsTimeSpanToHMS(v.KPI_Receive) + '</td>';
                            result += '</tr>';
                            if (v.KPI_Receive != "null") {
                                st_rcv += v.KPI_Receive;
                            }
                        }

                        if (operation == 3 || operation == "") {
                            result += '<tr>';
                            result += '<td>' + v.Document_No + '</td>';
                            result += '<td class="cl_left">Put Away</td>';
                            result += '<td class="cl_left">' + v.Putaway_Start + '</td>';
                            result += '<td class="cl_left">' + v.Putaway_End + '</td>';
                            result += '<td>' + secondsTimeSpanToHMS(v.KPI_Putaway) + '</td>';
                            result += '</tr>';

                            if (v.KPI_Putaway != "null") {
                                st_put += v.KPI_Putaway;
                            }
                        }

                        in_stat++;

                    } else if (type == "DDR") {


                        if (operation == 4 || operation == "") {
                            result += '<tr>';
                            result += '<td>' + v.Document_No + '</td>';
                            result += '<td class="cl_left">Picking</td>';
                            result += '<td class="cl_left">' + v.Picking_Start + '</td>';
                            result += '<td class="cl_left">' + v.Picking_End + '</td>';
                            result += '<td>' + secondsTimeSpanToHMS(v.KPI_Picking) + '</td>';
                            result += '</tr>';

                            if (v.KPI_Picking != "null") {
                                st_pick += v.KPI_Picking;
                            }
                        }

                        if (operation == 5 || operation == "") {
                            result += '<tr>';
                            result += '<td>' + v.Document_No + '</td>';
                            result += '<td class="cl_left">Dispatch</td>';
                            result += '<td class="cl_left">' + v.Dispatch_Start + '</td>';
                            result += '<td class="cl_left">' + v.Dispatch_End + '</td>';
                            result += '<td>' + secondsTimeSpanToHMS(v.KPI_Dispatch) + '</td>';
                            result += '</tr>';

                            if (v.KPI_Dispatch != "null") {
                                st_dis += v.KPI_Dispatch;
                            }
                        }

                        if (operation == 6 || operation == "") {
                            result += '<tr>';
                            result += '<td>' + v.Document_No + '</td>';
                            result += '<td class="cl_left">Close Container</td>';
                            result += '<td class="cl_left">' + v.Container_Out_Start + '</td>';
                            result += '<td class="cl_left">' + v.Container_Out_End + '</td>';
                            result += '<td>' + secondsTimeSpanToHMS(v.KPI_Container_Out) + '</td>';
                            result += '</tr>';

                            if (v.KPI_Container_Out != "null") {
                                st_close += v.KPI_Container_Out;
                            }
                        }
                        out_stat++;

                    }
                });
                // end result.

                // close table.
                result += '</tbody>';
                result += '</table>';

                // PIECHART DATA
                var piechart = [];
                piechart.push(in_stat);
                piechart.push(out_stat);
                pie_data.datasets[0].data = piechart;
                myPieChart.update();
                // ===================

                // Graph Data
                var grchart = [];
                grchart.push(st_open);
                grchart.push(st_rcv);
                grchart.push(st_put);
                grchart.push(st_pick);
                grchart.push(st_dis);
                grchart.push(st_close);
                graph_data.datasets[0].data = grchart;
                myChart.update();
                // ==============================

                // init result to result div.
                $("#response").html(result);
                $("#inbound_stat").html(in_stat);
                $("#outbound_stat").html(out_stat);

                // AVG In
                var avg_inb_time = ((st_open + st_rcv + st_put) / in_stat);
                avg_inb_time = (isNaN(avg_inb_time) ? 0 : avg_inb_time);
                $("#avg_inb_time").html(avg_inb_time.toFixed(2));

                // AVG Out
                var avg_out_time = ((st_pick + st_dis + st_close) / out_stat);
                avg_out_time = (isNaN(avg_out_time) ? 0 : avg_out_time);
                $("#avg_out_time").html(avg_out_time.toFixed(2));

                // init datatable.
                $("#tblData").dataTable({
                    aLengthMenu: [[27, 108, 432, -1], [27, 108, 432, "All"]]
                    , iDisplayLength: 27
                    , bSort: false
                });
            }, "JSON");
        });

        // ==============
    });
</script>
<form class="form-horizontal" style="padding: 10px;">
    <fieldset style="padding: 10px; border: 1px solid #e7ecf1 !important;">
        <legend>Search Criteria</legend>
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span3">
                    <div class="control-group">
                        <label class="control-label">From Date : </label>
                        <div class="controls">
                            <input type="text" id="from_date" placeholder="Date Format">
                        </div>
                    </div>
                </div>
                <div class="span3">
                    <div class="control-group">
                        <label class="control-label">To Date : </label>
                        <div class="controls">
                            <input type="text" id="to_date" placeholder="Date Format">
                        </div>
                    </div>
                </div>
                <div class="span3">
                    <div class="control-group">
                        <label class="control-label">Operation : </label>
                        <div class="controls">
                            <select id="operation" name="operation" style="width: 100%;">
                                <option value="">All Process</option>
                                <option value="1">Open Container</option>
                                <option value="2">Receive</option>
                                <option value="3">Put Away</option>
                                <option value="4">Picking</option>
                                <option value="5">Dispatch</option>
                                <option value="6">Close Container</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="span3">
                </div>
            </div>
            <div class="row-fluid">
                <div class="span12" align="center">
                    <button type="button" class="btn btn-large btn-info" id="btn_search">Search</button>
                    <button type="button" class="btn btn-large btn-danger" id="btn_clear">Clear</button>
                </div>
            </div>
        </div>
    </fieldset>
</form>
<div style="height: 100%; min-height: 640px;">    
    <div id="response" class="response col-left" ></div>
    <div class="response col-right">
        <div>
            <div class="col-md-3">
                <div class="dashboard-stat2 bordered">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-green-sharp">
                                <span data-counter="counterup" id="inbound_stat"></span>
                                <small class="font-green-sharp"></small>
                            </h3>
                            <small>Inbound</small>
                        </div>
                        <div class="icon">
                            <i class="icon-pie-chart"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat2 bordered">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-green-sharp">
                                <span data-counter="counterup" id="outbound_stat"></span>
                                <small class="font-green-sharp"></small>
                            </h3>
                            <small>Outbound</small>
                        </div>
                        <div class="icon">
                            <i class="icon-pie-chart"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat2 bordered">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-green-sharp">
                                <span data-counter="counterup" id="avg_inb_time"></span>
                                <small class="font-green-sharp"></small>
                            </h3>
                            <small>AVG Inbound</small>
                        </div>
                        <div class="icon">
                            <i class="icon-pie-chart"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3" style="padding-right: 0;">
                <div class="dashboard-stat2 bordered">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-green-sharp">
                                <span data-counter="counterup" id="avg_out_time"></span>
                                <small class="font-green-sharp"></small>
                            </h3>
                            <small>AVG Outbound</small>
                        </div>
                        <div class="icon">
                            <i class="icon-pie-chart"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
        <div>
            <div style="width: 300px; height: 300px; float: left;">
                <canvas id="chart_overall"></canvas>
            </div>
            <div style="width: 500px; height: 300px; float: left;">
                <canvas id="chart_detail"></canvas>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.bundle.min.js"></script>