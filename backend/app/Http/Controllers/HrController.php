<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LoanApplication;
use Illuminate\Http\Request;
use App\Models\LoanType;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Log;

class HrController extends Controller
{
    public function loanApplications(Request $request)
    {
        $query = LoanApplication::with([
            'employee', 
            'loanType',
            'processedBy'
        ])->orderBy('application_date', 'desc');

        // Advanced filtering
        $filters = $request->only(['status', 'department', 'loan_type', 'date_from', 'date_to']);
        
        if ($request->has('status')) {
            $query->where('status', $filters['status']);
        }
        
        if ($request->has('department')) {
            $query->whereHas('employee', function($q) use ($filters) {
                $q->where('department', $filters['department']);
            });
        }
        
        if ($request->has('loan_type')) {
            $query->whereHas('loanType', function($q) use ($filters) {
                $q->where('loan_type_id', $filters['loan_type']);
            });
        }
        
        if ($request->has(['date_from', 'date_to'])) {
            $query->whereBetween('application_date', [
                Carbon::parse($filters['date_from'])->startOfDay(),
                Carbon::parse($filters['date_to'])->endOfDay()
            ]);
        }

        return $query->paginate($request->get('per_page', 10));
    }

    public function showHrReports()
    {
        return view('admin.reports.hr_reports');
    }
    public function updateApplication(Request $request, $id)
    {
        $currentUser = Auth::user();
        
        if (Auth::user()->role !== 'hr') {
            abort(403);
        }

        // Log current user details
    Log::info('Current User Details', [
        'user_id' => $currentUser->id,
        'username' => $currentUser->username,
        'employee_id' => $currentUser->employee_id,
        'employee_relationship' => $currentUser->employee ? $currentUser->employee->toArray() : 'No employee linked'
    ]);
    
        $request->validate([
            'status' => 'required|in:Recommended,Not Recommended',
            'review_notes' => 'nullable|string|max:500'
        ]);

        $loan = LoanApplication::findOrFail($id);
        
        $loan->update([
            'status' => $request->status,
            'review_notes' => $request->review_notes,
            'processed_by' => $currentUser->user_id,
            'processed_date' => now()
        ]);
        
        // Log loan update details
        Log::info('Loan Update Details', [
            'loan_id' => $loan->id,
            'processed_by' => $loan->processed_by,
            'processed_by_user' => $loan->processedBy ? $loan->processedBy->toArray() : 'No processed by user found',
            'current_user_id' => $currentUser->user_id,
    'current_user_name' => $currentUser->username
        ]);

        // Add notification logic here
        return response()->json([
            'message' => 'Application updated successfully',
            'loan' => $loan->fresh(['employee', 'loanType', 'processedBy'])
        ]);
    }
    public function getLoanDetails($id)
{
    try {
        $loan = LoanApplication::with([
            'employee', 
            'loanType', 
            'processedBy'
        ])->findOrFail($id);
        
        // Add detailed logging to diagnose relationship issues
        Log::info('Loan details retrieved:', [
            'loan_id' => $loan->loan_id,
            'employee_id' => $loan->employee_id,
            'loan_type_id' => $loan->loan_type_id,
            'employee_exists' => !is_null($loan->employee),
            'loan_type_exists' => !is_null($loan->loanType),
            'processed_by_exists' => !is_null($loan->processedBy)
        ]);
        
        // If relationships are still null, try to fetch them directly
        if (is_null($loan->employee) && !is_null($loan->employee_id)) {
            $employee = Employee::find($loan->employee_id);
            if ($employee) {
                Log::info('Employee found directly:', ['employee_id' => $employee->employee_id]);
                // You can attach it manually if needed
                // $loan->setRelation('employee', $employee);
            }
        }
        
        if (is_null($loan->loanType) && !is_null($loan->loan_type_id)) {
            $loanType = LoanType::find($loan->loan_type_id);
            if ($loanType) {
                Log::info('Loan type found directly:', ['loan_type_id' => $loanType->loan_type_id]);
                // You can attach it manually if needed
                // $loan->setRelation('loanType', $loanType);
            }
        }
        
        return response()->json($loan);
    } catch (\Exception $e) {
        Log::error('Error retrieving loan details:', [
            'loan_id' => $id, 
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => 'Failed to retrieve loan details',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function loans(Request $request) 
    {
        return $this->loanApplications($request);
    }
    
    public function reports(Request $request) 
    {
        return $this->generateReport($request);
    }

    public function employees(Request $request)
    {
        $query = Employee::with(['branch', 'loanApplications'])
            ->withCount(['loanApplications as pending_loans' => function($q) {
                $q->where('status', 'Pending');
            }])
            ->orderBy('full_name');

        if ($request->has('department')) {
            $query->where('department', 'like', '%'.$request->department.'%');
        }

        return $query->paginate($request->get('per_page', 10));
    }

    public function employeeDetails($id)
    {
        return Employee::with([
            'bankingDetails',
            'loanApplications' => function($q) {
                $q->orderBy('application_date', 'desc')
                  ->with('loanType');
            },
            'branch'
        ])->where('employee_id', $id)
            ->orWhere('employee_id', str_pad($id, 2, '0', STR_PAD_LEFT))
            ->first();
    }

    public function generateReport(Request $request)
    {
        $period = $this->resolvePeriod($request);
    
        try {
            // Basic report data
            $reportData = [
                'total_applications' => LoanApplication::period($period)->count(),
                'approved_applications' => LoanApplication::period($period)->approved()->count(),
                'rejected_applications' => LoanApplication::period($period)->rejected()->count(),
                'pending_applications' => LoanApplication::period($period)->pending()->count(),
                'average_loan_amount' => LoanApplication::period($period)->avg('amount'),
                
                // Calculate average processing time for processed loans
                'avg_processing_days' => LoanApplication::period($period)
                    ->whereNotNull('processed_date')
                    ->whereNotNull('application_date')
                    ->get()
                    ->avg(function($loan) {
                        $applicationDate = is_string($loan->application_date) 
                           ? Carbon::parse($loan->application_date) 
                           : $loan->application_date;
                        $processedDate = is_string($loan->processed_date)
                           ? Carbon::parse($loan->processed_date)
                           : $loan->processed_date;

                        return $applicationDate->diffInDays($processedDate);
                    }) ?? 0,
                    
                // Department breakdown with more detailed information
                'department_breakdown' => Employee::whereHas('loanApplications', function($q) use ($period) {
                    $q->period($period);
                })
                ->with(['loanApplications' => function($q) use ($period) {
                    $q->period($period);
                }])
                ->get()
                ->mapWithKeys(function($employee) {
                    return [
                        $employee->department => $employee->loanApplications->count()
                    ];
                })
                ->filter(),
                
                // Most common loan type
                'most_common_loan_type' => LoanApplication::period($period)
                    ->select('loan_type_id')
                    ->groupBy('loan_type_id')
                    ->orderByRaw('COUNT(*) DESC')
                    ->with('loanType')
                    ->first()?->loanType
            ];
    
            return response()->json($reportData);
        } catch (\Exception $e) {
            Log::error('Report Generation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate report',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // Add new endpoint to get departments for reports
    public function getDepartments()
    {
        return Employee::select('department')
            ->distinct()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->pluck('department');
    }
    

    private function resolvePeriod($request)
    {
        switch($request->period) {
            case 'weekly':
                return [now()->startOfWeek(), now()->endOfWeek()];
            case 'monthly':
                return [now()->startOfMonth(), now()->endOfMonth()];
            case 'quarterly':
                return [now()->startOfQuarter(), now()->endOfQuarter()];
            case 'yearly':
                return [now()->startOfYear(), now()->endOfYear()];
            case 'custom':
                return [$request->start_date, $request->end_date];
            default:
                return [now()->subMonth(), now()];
        }
    }
}