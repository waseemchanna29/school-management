<?php

namespace App\Services;

use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\StudentScheduler;
use Illuminate\Support\Facades\DB;

class FeeService
{
    /**
     * Assign a scheduler to a student.
     * Replaces any existing scheduler and creates a fresh personal copy of items.
     */
    public function assignScheduler(Student $student, int $schedulerId, string $assignedDate): void
    {
        DB::transaction(function () use ($student, $schedulerId, $assignedDate) {
            $scheduler = \App\Models\FeeScheduler::with('items')->findOrFail($schedulerId);

            // Remove old assignment and personal items
            \App\Models\StudentScheduler::where('student_id', $student->id)->delete();
            \App\Models\StudentSchedulerItem::where('student_id', $student->id)->delete();

            // Create new assignment
            StudentScheduler::create([
                'student_id'       => $student->id,
                'campus_id'        => $student->campus_id,
                'fee_scheduler_id' => $scheduler->id,
                'assigned_date'    => $assignedDate,
            ]);

            // Copy items as personal editable lines
            foreach ($scheduler->items as $i => $item) {
                \App\Models\StudentSchedulerItem::create([
                    'student_id'       => $student->id,
                    'campus_id'        => $student->campus_id,
                    'fee_scheduler_id' => $scheduler->id,
                    'label'            => $item->label,
                    'amount'           => $item->amount,
                    'is_active'        => true,
                    'sort_order'       => $i,
                    'note'             => null,
                ]);
            }
        });
    }

    /**
     * Generate invoice — now uses academic_year_id instead of billing_year string.
     */
    public function generateInvoice(
        Student $student,
        int     $month,
        int     $academicYearId,     // ← was: int $year
        string  $dueDate,
        float   $outstanding = 0,
        float   $fine        = 0,
        float   $discount    = 0,
        ?string $remarks     = null
    ): FeeInvoice {
        $academicYear = \App\Models\AcademicYear::findOrFail($academicYearId);

        // Check for duplicate
        $exists = FeeInvoice::where('student_id', $student->id)
            ->where('billing_month', $month)
            ->where('academic_year_id', $academicYearId)
            ->exists();

        if ($exists) {
            throw new \Exception(
                "Invoice for {$student->full_name} for " .
                    date('F', mktime(0, 0, 0, $month, 1)) .
                    " ({$academicYear->name}) already exists."
            );
        }

        // Get student scheduler for this year
        $assignment = \App\Models\StudentScheduler::where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->first();

        // Fall back to any active scheduler if year-specific not found
        if (!$assignment) {
            $assignment = \App\Models\StudentScheduler::where('student_id', $student->id)
                ->first();
        }

        if (!$assignment) {
            throw new \Exception("{$student->full_name} has no fee scheduler assigned.");
        }

        $items = \App\Models\StudentSchedulerItem::where('student_id', $student->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($items->isEmpty()) {
            throw new \Exception("{$student->full_name} has no active fee items.");
        }

        $subtotal  = $items->sum('amount');
        $netAmount = $subtotal + $outstanding + $fine - $discount;

        return DB::transaction(function () use (
            $student,
            $month,
            $academicYearId,
            $academicYear,
            $dueDate,
            $outstanding,
            $fine,
            $discount,
            $remarks,
            $assignment,
            $items,
            $subtotal,
            $netAmount
        ) {
            $invoice = FeeInvoice::create([
                'invoice_number'       => $this->nextInvoiceNumber(),
                'student_id'           => $student->id,
                'campus_id'            => $student->campus_id,
                'fee_scheduler_id'     => $assignment->fee_scheduler_id,
                'academic_year_id'     => $academicYearId,     // ← FK
                'billing_month'        => $month,
                'billing_period_label' => date('F', mktime(0, 0, 0, $month, 1))
                    . ' ' . $academicYear->name,
                'subtotal'             => $subtotal,
                'outstanding'          => $outstanding,
                'fine'                 => $fine,
                'discount'             => $discount,
                'net_amount'           => $netAmount,
                'paid_amount'          => 0,
                'balance'              => $netAmount,
                'status'               => 'unpaid',
                'due_date'             => $dueDate,
                'remarks'              => $remarks,
            ]);

            foreach ($items as $i => $item) {
                \App\Models\FeeInvoiceItem::create([
                    'fee_invoice_id' => $invoice->id,
                    'label'          => $item->label,
                    'amount'         => $item->amount,
                    'sort_order'     => $i,
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Bulk generate — now uses academic_year_id.
     */
    public function bulkGenerate(
        int    $campusId,
        int    $month,
        int    $academicYearId,     // ← was: int $year
        string $dueDate,
        float  $outstanding = 0,
        float  $fine        = 0,
        float  $discount    = 0
    ): array {
        $students = Student::where('campus_id', $campusId)
            ->where('status', 'active')
            ->whereHas('schedulerAssignment')
            ->get();

        $generated = 0;
        $skipped   = 0;
        $errors    = [];

        foreach ($students as $student) {
            try {
                $this->generateInvoice(
                    $student,
                    $month,
                    $academicYearId,
                    $dueDate,
                    $outstanding,
                    $fine,
                    $discount
                );
                $generated++;
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'already exists')) {
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
                'receipt_number' => $this->nextReceiptNumber(),
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

    private function nextInvoiceNumber(): string
    {
        $last = FeeInvoice::max('id') ?? 0;
        return 'INV-' . date('Y') . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }

    private function nextReceiptNumber(): string
    {
        $last = FeePayment::max('id') ?? 0;
        return 'RCP-' . date('Y') . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
