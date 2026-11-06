<?php
namespace App\Services;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
final class ReportExportService
{
    public function excel(array $report): string
    {
        $book = new Spreadsheet(); $sheet = $book->getActiveSheet(); $sheet->setTitle('Nhap-Xuat-Ton');
        $sheet->fromArray(['Mã thuốc','Tên thuốc','Tồn đầu','Nhập','Hoàn','Xuất đơn','Xuất hủy','Tồn cuối'], null, 'A1');
        $row = 2; foreach ($report['stock']['rows'] as $item) { $sheet->fromArray([$item['medicine_code'],$item['medicine_name'],$item['opening'],$item['imports'],$item['restores'],$item['prescription_exports'],$item['disposal_exports'],$item['closing']], null, 'A'.$row++); }
        $sheet->getStyle('A1:H1')->getFont()->setBold(true); foreach (range('A','H') as $column) $sheet->getColumnDimension($column)->setAutoSize(true);
        $path = tempnam(sys_get_temp_dir(), 'pims-report-'); (new Xlsx($book))->save($path); $content = file_get_contents($path); unlink($path); return $content;
    }
    public function pdf(array $report): string
    {
        $rows = ''; foreach ($report['stock']['rows'] as $item) { $rows .= '<tr><td>'.htmlspecialchars((string)$item['medicine_code']).'</td><td>'.htmlspecialchars($item['medicine_name']).'</td><td>'.$item['opening'].'</td><td>'.$item['imports'].'</td><td>'.$item['prescription_exports'].'</td><td>'.$item['disposal_exports'].'</td><td>'.$item['closing'].'</td></tr>'; }
        $html = '<meta charset="UTF-8"><style>body{font-family:DejaVu Sans;font-size:11px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #777;padding:5px}th{background:#ddd}</style><h2>Báo cáo nhập - xuất - tồn</h2><p>Từ '.$report['from'].' đến '.$report['to'].'</p><table><thead><tr><th>Mã</th><th>Thuốc</th><th>Đầu kỳ</th><th>Nhập</th><th>Xuất đơn</th><th>Xuất hủy</th><th>Cuối kỳ</th></tr></thead><tbody>'.$rows.'</tbody></table>';
        $pdf = new Dompdf(); $pdf->loadHtml($html, 'UTF-8'); $pdf->setPaper('A4', 'landscape'); $pdf->render(); return $pdf->output();
    }
}
