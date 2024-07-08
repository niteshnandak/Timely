<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class PayrollBatchExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell
{
    protected $payrollBatch;
    protected $batchNumber;

    public function __construct($payrollBatch)
    {
        $this->payrollBatch = $payrollBatch;
        $this->batchNumber = $payrollBatch->first()->payroll_batch_number;
    }

    public function collection()
    {
        return $this->payrollBatch;
    }

    public function headings(): array
    {
        return [
            ['Payroll Batch Number: ' . $this->batchNumber],
            [
                'People Name',
                'Total Hours',
                'Gross Salary',
                'Margin',
                'Taxable Amount',
                'Expense Amount',
                'Total Payment Amount',
                'Employer Tax',
                'Employee Tax',
                'Total Tax Deduction',
                'Net Pay'
            ]
        ];
    }

    public function map($payrollDetail): array
    {
        return [
            $payrollDetail->people_name,
            $this->formatNumber($payrollDetail->total_hours),
            $this->formatNumber($payrollDetail->gross_salary),
            $this->formatNumber($payrollDetail->margin),
            $this->formatNumber($payrollDetail->taxable_amount),
            $this->formatNumber($payrollDetail->expense_amount),
            $this->formatNumber($payrollDetail->total_payment_amount),
            $this->formatNumber($payrollDetail->er_tax),
            $this->formatNumber($payrollDetail->ee_tax),
            $this->formatNumber($payrollDetail->total_tax_deduction),
            $this->formatNumber($payrollDetail->net_pay)
        ];
    }

    public function startCell(): string
    {
        return 'A2';
    }

    private function formatNumber($number)
    {
        if ($number < 0) {
            return '(' . $this->formatPositiveNumber(abs($number)) . ')';
        }
        return $this->formatPositiveNumber($number);
    }

    private function formatPositiveNumber($number)
    {
        return number_format($number, 2, '.', ',');
    }
}

// <?php
// namespace App\Exports;

// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\WithMapping;
// use Maatwebsite\Excel\Concerns\WithCustomStartCell;

// class PayrollBatchExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell
// {
//     protected $payrollBatch;
//     protected $batchNumber;

//     public function __construct($payrollBatch)
//     {
//         $this->payrollBatch = $payrollBatch;
//         $this->batchNumber = $payrollBatch->first()->payroll_batch_number;
//     }

//     public function collection()
//     {
//         return $this->payrollBatch;
//     }

//     public function headings(): array
//     {
//         return [
//             ['Payroll Batch Number: ' . $this->batchNumber],
//             [
//                 'People Name',
//                 'Total Hours',
//                 'Hourly Pay',
//                 'Gross Salary',
//                 'Margin',
//                 'Taxable Amount',
//                 'Expense Amount',
//                 'Total Payment Amount',
//                 'Employeer Tax',
//                 'Employee Tax',
//                 'Total Tax Deduction',
//                 'Net Pay'
//             ]
//         ];
//     }

//     public function map($payrollDetail): array
//     {
//         return [
//             $payrollDetail->people_name,
//             $payrollDetail->total_hours,
//             $payrollDetail->hourly_pay,
//             $payrollDetail->gross_salary,
//             $payrollDetail->margin,
//             $payrollDetail->taxable_amount,
//             $payrollDetail->expense_amount,
//             $payrollDetail->total_payment_amount,
//             $payrollDetail->er_tax,
//             $payrollDetail->ee_tax,
//             $payrollDetail->total_tax_deduction,
//             $payrollDetail->net_pay
//         ];
//     }

//     public function startCell(): string
//     {
//         return 'A2';
//     }
// }