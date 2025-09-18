<?php

namespace App\Console\Commands\Test;

use App\Models\AttendanceLog;
use App\Models\Device;
use App\Models\Member;            //  ← or  App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AttendanceLogSeederForTesting extends Command
{
    protected $signature   = 'attendance_log_seeder_for_testing';
    protected $description = 'Seed two attendance logs (IN / OUT) per member for testing';

    public function handle(): int
    {
        // AttendanceLog::truncate();
        // return 0;

        $deviceIds = Device::pluck('device_id')->toArray();
        if (empty($deviceIds)) {
            $this->error('No devices found – seed devices first.');
            return self::FAILURE;
        }

        // All members we will create logs for
        $userIds = Member::pluck('system_user_id')->toArray();   // or Employee::pluck(...)
        if (empty($userIds)) {
            $this->error('No members found – seed members first.');
            return self::FAILURE;
        }

        // Hour blocks so IN is always earlier than OUT
        $inHours  = ['09','10','11','12','13'];
        $outHours = ['14','15','16','17','18','19','20','21'];

        $today = today()->format('Y-m-d');

        $rows = [];

        foreach ($userIds as $userId) {

            $this->info($userId);

            // -------- First (IN) log --------
            $inHour    = Arr::random($inHours);
            $inMinute  = str_pad(random_int(0, 59), 2, '0', STR_PAD_LEFT);
            $inTime    = "{$today} {$inHour}:{$inMinute}:00";

            $rows[] = [
                'UserID'     => $userId,
                'LogTime'    => $inTime,
                'DeviceID'   => Arr::random($deviceIds),
                'company_id' => 1,
                'log_date'   => $today,
            ];

            // -------- Second (OUT) log --------
            $outHour   = Arr::random($outHours);
            $outMinute = str_pad(random_int(0, 59), 2, '0', STR_PAD_LEFT);
            $outTime   = "{$today} {$outHour}:{$outMinute}:00";

            $rows[] = [
                'UserID'     => $userId,
                'LogTime'    => $outTime,
                'DeviceID'   => Arr::random($deviceIds),
                'company_id' => 1,
                'log_date'   => $today,
            ];
        }

        // Sort globally just in case (optional; db will accept random order)
        usort($rows, fn ($a, $b) => strcmp($a['LogTime'], $b['LogTime']));

        AttendanceLog::insert($rows);

        $this->info('Attendance Log Seeder: ' . count($rows) . ' records inserted (2 per member).');

        return self::SUCCESS;
    }
}
