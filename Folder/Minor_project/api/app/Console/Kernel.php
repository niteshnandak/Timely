<?php


namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Timesheet;
use App\Models\TimesheetDetail;
use App\Models\SchedularLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\TaskSchedular;
use Carbon\Carbon;
use Exception;

use function PHPUnit\Framework\isEmpty;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            // Calculate start and end dates for the current week
                TimesheetDetail::where('invoice_status', false) // Only un-invoiced details
                    ->where('draft_status', false)
                    ->chunk(100, function ($details) {  // Process in chunks for efficiency
                        foreach ($details as $detail) {
                            $timesheet = $detail->timesheet; // Access Timesheet through relationship
                            if ($timesheet) { // Ensure timesheet is associated
                                try{
                                    DB::beginTransaction();
                                    $invoice = Invoice::firstOrCreate([
                                        'timesheet_id' => $timesheet->id,
                                        'worker_id'    => $detail->worker_id,
                                        'worker_name'  => $detail->worker_name,
                                        'organisation' => $detail->organisation
                                    ]);

                                    InvoiceDetail::create([
                                        'timesheet_id' => $timesheet->id,
                                        'timesheet_detail_id' => $detail->id,
                                        'invoice_id'   => $invoice->id,
                                        'worker_id'    => $detail->worker_id,
                                        'worker_name'  => $detail->worker_name,
                                        'hourly_pay' => $detail->hourly_pay,
                                        'hours_worked' => $detail->hours_worked,
                                        'invoice_date' => $detail->timesheet_detail_date,
                                        'total_amount' => $detail->hours_worked * $detail->hourly_pay,
                                        'organisation' => $detail->organisation,

                                    ]);

                                    $results = InvoiceDetail::where('timesheet_id', $timesheet->id)
                                    ->where('worker_id', $detail->worker_id)
                                    ->where('organisation', $detail->organisation)
                                    ->select('timesheet_id', 'worker_id', 'organisation')
                                    ->selectRaw('SUM(total_amount) as total_pay')
                                    ->groupBy('timesheet_id', 'worker_id', 'organisation')
                                    ->first();

                                    $invoice->update([
                                        'total_amount'=>$results->total_pay,
                                        'taxed_amount'=>$results->total_pay + $results->total_pay*0.18,
                                    ]);

                                    $detail->invoice_status = true;
                                    $detail->save();


                                    TaskSchedular::where('timesheet_detail_id', $detail->id)
                                    ->update(['status'=>'Succes', 'message'=>"success"]);
                                    DB::commit();
                                }catch (\PDOException $e){
                                    DB::rollBack();

                                    TaskSchedular::where('timesheet_detail_id', $detail->id)
                                    ->update(['status'=>'Failure', 'message'=>"task completed with error", 'exception'=>$e->getMessage()]);
                                }
                            }
                        }
                    });


        })->everyMinute(); // Run the scheduler weekly
    }



    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
