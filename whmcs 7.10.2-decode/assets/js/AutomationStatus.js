/*!
 * Automation Status Javascript.
 *
 * @copyright Copyright (c) WHMCS Limited 2005-2019
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */
$(document).ready(function(){
    $('#statsContainer').on('click', '.btn-viewing', function (e){
        e.preventDefault();
    });
    $('#graphContainer').on('click', '.graph-filter-metric a', function (e){
        e.preventDefault();
        $('.graph-filter-metric a').removeClass('active');
        $(this).addClass('active');
        refreshGraph();
    });
    $('#graphContainer').on('click', '.graph-filter-period a', function (e){
        e.preventDefault();
        $('.graph-filter-period a').removeClass('active');
        $(this).addClass('active');
        refreshGraph();
    });
});

function loadAutomationStatsForDate(date) {
    $('#statsContainer').css('opacity', '0.5');
        WHMCS.http.jqClient.post(
            "automationstatus.php",
            'action=stats&date=' + date,
            function(data) {
                $('.widgets-container').html(data.body);
                $('.day-selector').find('.btn-viewing').html(data.newDate);
            }
        ).fail(function() {
            jQuery.growl({ title: "", message: "Your session has expired. Please refresh to continue." });
        }).always(function() {
            $('#statsContainer').css('opacity', '1');
        });
}

function refreshGraph() {
    $('#graphContainer').css('opacity', '0.5');
        var jqxhr = WHMCS.http.jqClient.post( "automationstatus.php",'action=graph&metric=' + $('.graph-filter-metric a.active').attr('href') + '&period=' + $('.graph-filter-period a.active').attr('href'),
            function(data) {
                $('#graphContainer').html(data.body);
            }).fail(function() {
                jQuery.growl({ title: "", message: "Your session has expired. Please refresh to continue." });
            }).always(function() {
                $('#graphContainer').css('opacity', '1');
            });
}
