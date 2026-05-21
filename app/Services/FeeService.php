<?php

namespace App\Services;

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
     * Creates a personal StudentFee row per structure item.
     * Existing rows for the same structure are overwritten;
     * rows from other structures are left untouched.
     */
    public function assignStructureToStudent(
        Student $student,
        int     $feeStructureId,
        string  $academicYear
    ): void {
        $structure = \App\Models\FeeStructure::with('items.feeLabel')
            ->findOrFail($feeStructureId);

        DB::transaction(function () use ($student, $structure, $academicYear) {
            foreach ($structure->items->where('is_active', true) as $item) {
                StudentFee::updateOrCreate(
                    [
                        'student_id'          => $student->id,
                        'fee_label_id'        => $item->fee_label_id,
                        'fee_structure_id'    => $structure->id,
                        'academic_year'       => $academicYear,
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
        });
    }

    /**
     * Generate an invoice for one student for a given period.
     * Pulls from student_fees filtered by the structure type.
     */
    public function generateInvoice(
        Student $student,
        string  $academicYear,
        string  $type,           // one_time | monthly | yearly
        int     $year,
        ?int    $month      = null,
        ?string $periodLabel = null,
        ?string $dueDate    = null
    ): FeeInvoice {

        // Get active student fees whose source structure matches the type
        $fees = StudentFee::where('student_id', $student->id)
            ->where('academic_year', $academicYear)
            ->where('is_active', true)
            ->whereHas('feeStructure', fn($q) => $q->where('type', $type))
            ->with(['feeLabel', 'feeStructure'])
            ->get();

        if ($fees->isEmpty()) {
            throw new \Exception(
                "No active {$type} fee lines found for student {$student->full_name} in {$academicYear}."
            );
        }

        // Build period label automatically if not provided
        $label = $periodLabel ?? match($type) {
            'monthly'  => Carbon::create()->month($month)->format('F') . ' ' . $year,
            'yearly'   => 'Annual ' . $year,
            'one_time' => $fees->first()->feeStructure->name . ' ' . $year,
        };

        // Check duplicate for monthly invoices
        if ($type === 'monthly' && $month !== null) {
            $exists = FeeInvoice::where('student_id', $student->id)
                ->where('period_type', 'monthly')
                ->where('month', $month)
                ->where('year', $year)
                ->exists();

            if ($exists) {
                throw new \Exception(
                    "Monthly invoice for {$label} already exists for {$student->full_name}."
                );
            }
        }

        $total = $fees->sum('amount');

        return DB::transaction(function () use (
            $student, $academicYear, $type, $year, $month, $label, $dueDate, $fees, $total
        ) {
            $invoice = FeeInvoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'student_id'     => $student->id,
                'campus_id'      => $student->campus_id,
                'academic_year'  => $academicYear,
                'period_label'   => $label,
                'period_type'    => $type,
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
     * Generate monthly invoices for ALL active students in a campus.
     * Called from the admin dashboard button.
     * Returns summary: ['generated' => N, 'skipped' => N, 'errors' => [...]]
     */
    public function generateMonthlyInvoicesForCampus(
        int    $campusId,
        string $academicYear,
        int    $month,
        int    $year,
        string $dueDate
    ): array {
        $students = Student::where('campus_id', $campusId)
            ->where('status', 'active')
            ->get();

        $generated = 0;
        $skipped   = 0;
        $errors    = [];

        foreach ($students as $student) {
            try {
                $this->generateInvoice(
                    $student,
                    $academicYear,
                    'monthly',
                    $year,
                    $month,
                    null,
                    $dueDate
                );
                $generated++;
            } catch (\Exception $e) {
                // "already exists" = skip silently
                if (str_contains($e->getMessage(), 'already exists')) {
                    $skipped++;
                } elseif (str_contains($e->getMessage(), 'No active')) {
                    // Student has no monthly fees — skip silently
                    $skipped++;
                } else {
                    $errors[] = $student->full_name . ': ' . $e->getMessage();
                }
            }
        }

        return compact('generated', 'skipped', 'errors');
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
                'reference'      => $data['reference']    ?? null,
                'collected_by'   => $data['collected_by'] ?? null,
                'remarks'        => $data['remarks']      ?? null,
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