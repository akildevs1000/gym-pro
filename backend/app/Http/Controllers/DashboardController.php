<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Device;
use App\Models\Expense;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboardCounts()
    {
        $activeMember  = Member::where("status", 1)->count();
        $expiredMember = Member::where("status", 0)->count();

        $offlinedDevices = Device::where("status", Device::InActive)->count();

        $pendingPayments = Payment::where("balance", ">", 0)->sum("balance");

        $income = Payment::where("balance", 0)->sum("total");

        $expense = Expense::sum("total");

        $date = date("Y-m-d");

        $attendanceCheckIn  = Attendance::whereDate("date", $date)->where("out", "---")->count();
        $attendanceCheckOut = Attendance::whereDate("date", $date)->whereNot("out", "---")->count();
        $remaingCount       = $attendanceCheckOut - $attendanceCheckIn;

        $accessLogs = AttendanceLog::whereDate("LogTime", $date)->count();

        $mostUsedPlanId = Membership::select('plan_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('plan_id')
            ->groupBy('plan_id')
            ->orderByDesc('total')
            ->value('plan_id');

        $mostUsedPlan = Plan::find($mostUsedPlanId)?->name ?? 'N/A';

        $data = [
            [
                "title"    => "Total Members",
                "subtitle" => "Active: $activeMember / Expired: $expiredMember",
                "icon"     => "mdi-home",
                "color"    => "#7E57C2",
            ],
            [
                "title"    => "Today's Attendance",
                "subtitle" => "Check In: $attendanceCheckIn / Check Out: $remaingCount",
                "icon"     => "mdi-calendar-check",
                "color"    => "#66BB6A",
            ],
            [
                "title"    => "Most Used Plan",
                "subtitle" => $mostUsedPlan,
                "icon"     => "mdi-clipboard-text",
                "color"    => "#FFA726",
            ],
            [
                "title"    => "Offline Device",
                "subtitle" => $offlinedDevices,
                "icon"     => "mdi-cellphone-information",
                "color"    => "grey",
            ],
            [
                "title"    => "Pending Payments",
                "subtitle" => number_format($pendingPayments, 2),
                "icon"     => "mdi-credit-card-outline",
                "color"    => "#EF5350",
            ],
            [
                "title"    => "Access Logs",
                "subtitle" => $accessLogs,
                "icon"     => "mdi-door",
                "color"    => "#26C6DA",
            ],
            [
                "title"    => "Income",
                "subtitle" => number_format($income, 2),
                "icon"     => "mdi-currency-usd",
                "color"    => "#AB47BC",
            ],
            [
                "title"    => "Expense",
                "subtitle" => number_format($expense, 2),
                "icon"     => "mdi-cash-minus",
                "color"    => "#42A5F5",
            ],
        ];

        return $data;

    }
}
