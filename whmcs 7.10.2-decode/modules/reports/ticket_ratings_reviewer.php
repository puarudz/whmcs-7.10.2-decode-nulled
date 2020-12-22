<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;
use WHMCS\View\Markup\Markup;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$rating = App::getFromRequest('rating');

if (!$rating) {
    $rating = '1';
}

$range = App::getFromRequest('range');
if (!$range) {
    $today = Carbon::today()->endOfDay();
    $lastWeek = Carbon::today()->subDays(6)->startOfDay();
    $range = $lastWeek->toAdminDateFormat() . ' - ' . $today->toAdminDateFormat();
}
$dateRange = Carbon::parseDateRangeValue($range);
$startdate = $dateRange['from'];
$enddate = $dateRange['to'];

$rsel[$rating] = ' selected="selected"';

$markup = new Markup;

$results = Capsule::table('tblticketreplies')
    ->select(Capsule::raw('tblticketreplies.*, tbltickets.tid AS ticketid'))
    ->join('tbltickets', 'tbltickets.id', '=', 'tblticketreplies.tid')
    ->where('tblticketreplies.admin', '!=', '')
    ->where('tblticketreplies.rating', '=', (int) $rating)
    ->whereBetween(
        'tblticketreplies.date',
        [
            $startdate->toDateString(),
            $enddate->toDateString(),
        ]
    )
    ->orderBy('date', 'desc')
    ->get();
$num_rows = count($results);

$reportdata["title"] = "Support Ticket Ratings Reviewer";
$reportdata["description"] = "This report is showing all $num_rows ticket replies rated $rating between"
    . " {$startdate->toAdminDateFormat()} & {$enddate->toAdminDateFormat()} for review";

$reportdata['headertext'] = '';
if (!$print) {
    $reportdata['headertext'] = <<<HTML
<form method="post" action="reports.php?report={$report}&currencyid={$currencyid}&calculate=true">
    <div class="report-filters-wrapper">
        <div class="inner-container">
            <h3>Filters</h3>
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label for="inputFilterRating">Rating</label>
                        <select id="inputFilterRating" name="rating" class="form-control">
                            <option{$rsel[1]}>1</option>
                            <option{$rsel[2]}>2</option>
                            <option{$rsel[3]}>3</option>
                            <option{$rsel[4]}>4</option>
                            <option{$rsel[5]}>5</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label for="inputFilterDate">{$dateRangeText}</label>
                        <div class="form-group date-picker-prepend-icon">
                            <label for="inputFilterDate" class="field-icon">
                                <i class="fal fa-calendar-alt"></i>
                            </label>
                            <input id="inputFilterDate"
                                   type="text"
                                   name="range"
                                   value="{$range}"
                                   class="form-control date-picker-search"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                {$aInt->lang('reports', 'generateReport')}
            </button>
        </div>
    </div>
</form>
HTML;
}

$reportdata["tableheadings"] = array(
    "Ticket #",
    "Date",
    "Message",
    "Admin",
    "Rating",
);

foreach ($results as $result) {
    $tid = $result->tid;
    $ticketid = $result->ticketid;
    $date = $result->date;
    $message = $result->message;
    $admin = $result->admin;
    $rating = $result->rating;
    $editor = $result->editor;

    $date = fromMySQLDate($date, true);

    $markupFormat = $markup->determineMarkupEditor('ticket_reply', $editor);
    $message = $markup->transform($message, $markupFormat);

    $reportdata["tablevalues"][] = array(
        '<a href="supporttickets.php?action=viewticket&id=' . $tid . '">' . $ticketid . '</a>',
        $date,
        '<div align="left">' . $message . '</div>',
        $admin,
        $rating,
    );
}
