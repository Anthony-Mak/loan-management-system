<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LoanApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Log;

class HrController extends Controller
{
    public function loanApplications(Request $request)
    {
        $query = LoanApplication::with(['employee', 'loanType'])
            ->orderBy('application_date', 'desc');

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

    public function updateApplication(Request $request, $id)
    {
        $currentUser = Auth::user();
        
        if (!Auth::user()->hasRole('hr')) {
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
            'status' => 'required|in:Approved,Rejected',
            'review_notes' => 'nullable|string|max:500'
        ]);

        $loan = LoanApplication::findOrFail($id);
        
        $loan->update([
            'status' => $request->status,
            'review_notes' => $request->review_notes,
            'processed_by' => Auth::user()->employee_id,
            'processed_date' => now()
        ]);
         // Log loan update details
    Log::info('Loan Update Details', [
        'loan_id' => $loan->id,
        'processed_by' => $loan->processed_by,
        'processed_by_user' => $loan->processedBy ? $loan->processedBy->toArray() : 'No processed by user found'
    ]);

        // Add notification logic here
        
        return response()->json([
            'message' => 'Application updated successfully',
            'loan' => $loan->fresh(['employee', 'loanType', 'processedBy'])
        ]);
    }
    public function getLoanDetails($id)
{
    $loan = LoanApplication::with([
        'employee', 
        'loanType', 
        'processedBy'  // Add this for comprehensive details
    ])->findOrFail($id);

    return response()->json($loan);
}
    public function loans(Request $request)  // Instead of loanApplications
    {
        return $this->loanApplications($request);
    }
    
    public function reports(Request $request)  // Add this method
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
        ])->findOrFail($id);
    }

    public function generateReport(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $request->validate([
            'period' => 'in:weekly,monthly,quarterly,yearly,custom',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
        ]);

        $period = $this->resolvePeriod($request);

        try {
            $reportData = [
                'total_applications' => LoanApplication::period($period)->count(),
                'approved_applications' => LoanApplication::period($period)->approved()->count(),
                'rejected_applications' => LoanApplication::period($period)->rejected()->count(),
                'pending_applications' => LoanApplication::period($period)->pending()->count(),
                'average_loan_amount' => LoanApplication::period($period)->avg('amount'),
                'department_breakdown' => Employee::whereHas('loanApplications', function($q) use ($period) {
                    $q->period($period);
                })
                ->with(['loanApplications' => function($q) use ($period) {
                    $q->period($period);
                }])
                ->get()
                ->mapWithKeys(function($employee) {
                    // Safely handle potential null or missing data
                    if (!$employee || !$employee->department) {
                        return [];
                    }
                    return [
                        $employee->department => $employee->loanApplications->count()
                    ];
                })
                ->filter(), // Remove any empty entries
                'trend_data' => LoanApplication::period($period)
                    ->selectRaw("DATE_FORMAT(application_date, '%Y-%m') as month, 
                                COUNT(*) as total,
                                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                                SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected")
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
            ];

            return response()->json($reportData);
        } catch (\Exception $e) {
            // Log the full error for debugging
            Log::error('Report Generation Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to generate report',
                'message' => $e->getMessage()
            ], 500);
        }
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