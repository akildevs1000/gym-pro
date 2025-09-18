<?php
namespace App\Console\Commands\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_member_report {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = 1;

        $date = $this->argument("date", date("Y-m-d"));

        $ids = $this->getEmployeeIdsForNewLogsToRender($date);

        $this->info(showJson($ids));

        $this->info($this->render($id, $date, $ids));
    }

    public function getEmployeeIdsForNewLogsToRender($date)
    {
        return AttendanceLog::where("checked", false)
            ->where("LogTime", ">=", $date)
            ->where("LogTime", "<=", date("Y-m-d", strtotime($date . " +1 day")))
            ->whereNotIn('UserID', function ($query) {
                $query->select('system_user_id')
                    ->where('visit_from', "<=", date('Y-m-d'))
                    ->where('visit_to', ">=", date('Y-m-d'))

                    ->from('visitors');
            })
            ->distinct("UserID", "company_id")
            ->pluck('UserID');
    }

    public function render($id, $date, $UserIds)
    {
        $params = [
            "company_id" => $id,
            "date"       => $date,
            "UserIds"    => $UserIds,
        ];

        $logsEmployees = (new AttendanceLog)->getLogsForRenderNotAutoShift($params);

        $items = [];

        foreach ($logsEmployees as $key => $logs) {
            $logs     = $logs->toArray() ?? [];
            $firstLog = collect($logs)->first();
            $lastLog  = collect($logs)->last();

            $item = [
                "roster_id"     => 0,
                "total_hrs"     => "---",
                "in"            => $firstLog["time"] ?? "---",
                "out"           => "---",
                "ot"            => "---",
                "device_id_in"  => $firstLog["DeviceID"] ?? "---",
                "device_id_out" => "---",
                "date"          => $params["date"],
                "company_id"    => $params["company_id"],
                "member_id"     => $key,
                "status"        => "M",
            ];

            if ($lastLog && count($logs) > 1 && $firstLog["time"] !== $lastLog["time"]) {

                $item["device_id_out"] = $lastLog["DeviceID"] ?? "---";
                $item["out"]           = $lastLog["time"] ?? "---";

                if ($item["out"] !== "---") {
                    $item["total_hrs"] = $this->getTotalHrsMins($item["in"], $item["out"]);
                }
            }

            $items[] = $item;
        }

        if (! count($items)) {
            $message = '[' . $date . " " . date("H:i:s") . '] Single Shift: No data found';
            return $message;
        }

        try {

            DB::beginTransaction();
            $model = Attendance::query();
            $model->where("company_id", $id);
            $model->whereIn("member_id", array_column($items, "member_id"));
            $model->where("date", $date);
            $model->delete();
            DB::commit();
            $model->insert($items);
            $updatedIds = AttendanceLog::whereIn("UserID", $UserIds)->update(["checked" => true]);
            $message = "[" . $date . " " . date("H:i:s") . "] Single Shift.   Affected Ids: $updatedIds";
        } catch (\Throwable $e) {
            $message = "[" . $date . " " . date("H:i:s") . "] Single Shift. " . $e->getMessage();

            DB::rollback();
        }

        return $message;
    }

    public function getLogsForRenderNotAutoShift($params)
    {
        $days = 1;

        return AttendanceLog::with("visitor")->where("LogTime", ">=", $params["date"]) // Check for logs on or after the current date
            ->where("LogTime", "<=", date("Y-m-d", strtotime($params["date"] . " +" . $days . " day")))
        //->whereIn("UserID", $params["UserIds"])
            ->when($params["UserIds"] != null, function ($q) use ($params) {

                return $q->whereIn("UserID", $params["UserIds"]);
            })
            ->where("company_id", $params["company_id"])
            ->distinct("LogTime", "UserID", "company_id")
            ->orderBy("LogTime", "asc")
            ->get()
            ->load("device")

            ->groupBy(['UserID']);
    }

    public function getTotalHrsMins($first, $last)
    {
        $diff = abs(strtotime($last) - strtotime($first));

        $h = floor($diff / 3600);
        $m = floor(($diff % 3600) / 60);
        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
    }
}
