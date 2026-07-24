<?php

namespace App\Console\Commands;

use App\Services\ThongKeSnapshotService;
use Illuminate\Console\Command;

class RecalculateThongKe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thongke:recalculate {--key= : Tính lại 1 key cụ thể}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tính lại dữ liệu thống kê snapshot từ database gốc';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $service = app(ThongKeSnapshotService::class);
        $key = $this->option('key');

        if ($key) {
            $this->info("Đang tính lại key: {$key}...");
            $success = $service->recalculateKey($key);

            if ($success) {
                $this->info("✅ Đã tính lại [{$key}] thành công.");
            } else {
                $this->error("❌ Tính lại [{$key}] thất bại. Xem log để biết chi tiết.");
                return 1;
            }
        } else {
            $this->info('Đang tính lại tất cả ' . count(ThongKeSnapshotService::ALL_KEYS) . ' keys...');

            $bar = $this->output->createProgressBar(count(ThongKeSnapshotService::ALL_KEYS));
            $bar->start();

            $failed = [];
            foreach (ThongKeSnapshotService::ALL_KEYS as $k) {
                if (!$service->recalculateKey($k)) {
                    $failed[] = $k;
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            if (empty($failed)) {
                $this->info('✅ Tất cả keys đã tính lại thành công!');
            } else {
                $this->warn('⚠️ Một số keys thất bại: ' . implode(', ', $failed));
                return 1;
            }
        }

        return 0;
    }
}
