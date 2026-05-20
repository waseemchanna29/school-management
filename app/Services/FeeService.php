<?php

namespace App\Services;

use App\Helpers\CampusContext;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\StudentFee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FeeService
{
    /**
     * Assign a fee structure to a student.
     * Creates personal StudentFee rows (one per label) that can be edited independently.
     */
    public function assignStructureToStudent(Student $student, int $feeStructureId, string $academicYear): void
    {
        $structure = \App\Models\FeeStructure::with('items.feeLabel')->findOrFail($feeStructureId);

        foreach ($structure->items->where('is_active', true) as $item) {
            StudentFee::updateOrCreate(
                [
                    'student_id'    => $student->id,
                    'fee_label_id'  => $item->fee_label_id,
                    'academic_year' => $academicYear,
                ],
                [
                    'campus_id'             => $student->campus_id,
                    'fee_structure_item_id' => $item->id,
                    'amount'                => $item->amount,
                    'is_active'             => true,
                    'note'                  => null,
                ]
            );
        }
    }

    /**
     * Generate an invoice for a student for a given period.
     * period_type: monthly | yearly | one_time
     */
    public function generateInvoice(
        Student $student,
        string  $academicYear,
        string  $periodType,
        int     $year,
        ?int    $month = null,
        ?string $periodLabel = null,
        ?string $dueDate = null
    ): FeeInvoice {
        $fees = StudentFee::where('student_id', $student->id)
            ->where('academic_year', $academicYear)
            ->where('is_active', true)
            ->whereHas('feeLabel', fn($q) => $q->where('frequency', $periodType)->where('is_active', true))
            ->with('feeLabel')
            ->get();

        if ($fees->isEmpty()) {
            throw new \Exception('No active fees found for this student for the given period type.');
        }

        $total = $fees->sum('amount');

        $label = $periodLabel ?? match($periodType) {
            'monthly'  => Carbon::create()->month($month)->format('F') . ' ' . $year,
            'yearly'   => 'Annual ' . $year,
            'one_time' => 'One-Time ' . $year,
        };

        return DB::transaction(function () use ($student, $academicYear, $periodType, $year, $month, $label, $dueDate, $fees, $total) {
            $invoice = FeeInvoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'student_id'     => $student->id,
                'campus_id'      => $student->campus_id,
                'academic_year'  => $academicYear,
                'period_label'   => $label,
                'period_type'    => $periodType,
                'month'          => $month,
                'year'           => $year,
                'total_amount'   => $total,
                'discount'       => 0,
                'fine'           => 0,
                'net_amount'     => $total,
                'paid_amount'    => 0,
                'balance'        => $total,
                'status'         => 'unpaid',
                'due_date'       => $dueDate ?? now()->endOfMonth()->toDateString(),
            ]);

            foreach ($fees as $fee) {
                FeeInvoiceItem::create([
                    'fee_invoice_id' => $invoice->id,
                    'fee_label_id'   => $fee->fee_label_id,
                    'label_name'     => $fee->feeLabel->name,
                    'amount'         => $fee->amount,
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Record a payment against an invoice.
     */
    public function recordPayment(FeeInvoice $invoice, array $data): FeePayment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $payment = FeePayment::create([
                'receipt_number' => $this->generateReceiptNumber(),
                'fee_invoice_id' => $invoice->id,
                'student_id'     => $invoice->student_id,
                'campus_id'      => $invoice->campus_id,
                'amount'         => $data['amount'],
                'method'         => $data['method'],
                'payment_date'   => $data['payment_date'],
                'reference'      => $data['reference'] ?? null,
                'collected_by'   => $data['collected_by'] ?? null,
                'remarks'        => $data['remarks'] ?? null,
            ]);

            $invoice->recalculate();

            return $payment;
        });
    }

    private function generateInvoiceNumber(): string
    {
        $last = FeeInvoice::max('id') ?? 0;
        return 'INV-' . date('Y') . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }

    private function generateReceiptNumber(): string
    {
        $last = FeePayment::max('id') ?? 0;
        return 'RCP-' . date('Y') . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}