<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\ThongKeService::class);

echo "=== TEST PAGINATE CHI TIET PHONG WITH FILTER ===\n";
$resPhong = $service->paginateChiTietPhong(['co_so_id' => 1], 10);
echo "Tong quan phong co_so_id=1: " . json_encode($resPhong['tong_quan']) . "\n";
echo "Total items in paginator: " . $resPhong['paginator']->total() . "\n";
echo "Bieu do loai count: " . count($resPhong['bieu_do_loai']) . "\n\n";

echo "=== TEST PAGINATE CHI TIET THIET BI WITH FILTER ===\n";
$resTB = $service->paginateChiTietThietBi(['co_so_id' => 1], 10);
echo "Tong quan thiet bi co_so_id=1: " . json_encode($resTB['tong_quan']) . "\n";
echo "Total items in paginator: " . $resTB['paginator']->total() . "\n";
echo "Bieu do loai count: " . count($resTB['bieu_do_loai']) . "\n";
