<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\LoanApplication;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class AdminReportController extends Controller
{
    /**
     * Check if the current user is authorized to view admin reports
     */
    private function checkAuthorization()
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized access to admin reports');
        }
    }
    /**
     * Calculate appropriate date range based on period
     */
    private function calculateDateRange($period, $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $now = Carbon::now();
        
        switch ($period) {
            case 'daily':
                return [
                    $now->copy()->startOfDay(),
                    $now->copy()->endOfDay()
                ];
            case 'weekly':
                return [
                    $now->copy()->startOfWeek(),
                    $now->copy()->endOfWeek()
                ];
            case 'monthly':
            default:
                return [
                    $now->copy()->startOfMonth(),
                    $now->copy()->endOfMonth()
                ];
            case 'quarterly':
                return [
                    $now->copy()->startOfQuarter(),
                    $now->copy()->endOfQuarter()
                ];
            case 'yearly':
                return [
                    $now->copy()->startOfYear(),
                    $now->copy()->endOfYear()
                ];
            case 'custom':
                // Validate and parse custom date range
                $request->validate([
                    'start_date' => 'required|date',
                    'end_date' => 'required|date|after_or_equal:start_date'
                ]);
                return [
                    Carbon::parse($request->input('start_date'))->startOfDay(),
                    Carbon::parse($request->input('end_date'))->endOfDay()
                ];
                // Update validation for custom period
    if ($period === 'custom') {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ], [], [
            'start_date' => 'Start Date',
            'end_date' => 'End Date'
        ]);
        
        return [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ];
    }
                
            
        }
    }
    /**
     * Calculate previous period statistics for comparison
     */
    private function calculatePreviousPeriodStats($period, $currentDateRange, $branchId = null)
    {
        $previousDateRange = $this->getPreviousPeriodRange($period, $currentDateRange);

        $query = LoanApplication::query();
        
        if ($branchId) {
            $query->whereHas('employee', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $currentPeriodStats = $query->whereBetween('created_at', $currentDateRange)
            ->selectRaw('COUNT(DISTINCT employee_id) as employee_count, SUM(approved_amount) as total_disbursed')
            ->first();

        $previousPeriodStats = $query->whereBetween('created_at', $previousDateRange)
            ->selectRaw('COUNT(DISTINCT employee_id) as employee_count, SUM(approved_amount) as total_disbursed')
            ->first();

        return [
            'employee_change' => $this->calculatePercentageChange(
                $previousPeriodStats->employee_count, 
                $currentPeriodStats->employee_count
            ),
            'disbursed_change' => $this->calculatePercentageChange(
                $previousPeriodStats->total_disbursed, 
                $currentPeriodStats->total_disbursed
            )
        ];
    }
    /**
     * Helper method to calculate percentage change
     */
    private function calculatePercentageChange($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        return round((($newValue - $oldValue) / $oldValue) * 100, 2);
    }

    /**
     * Get previous period range
     */
    private function getPreviousPeriodRange($period, $currentDateRange)
    {
        $start = Carbon::parse($currentDateRange[0]);
        $end = Carbon::parse($currentDateRange[1]);
        $duration = $start->diffInDays($end) + 1;

        return [
            $start->copy()->subDays($duration),
            $end->copy()->subDays($duration)
        ];
    }

    /**
     * Get branch breakdown
     */
    private function getBranchBreakdown($dateRange, $specificBranchId = null)
    {
        $query = Branch::query();
        
        if ($specificBranchId) {
            $query->where('id', $specificBranchId);
        }

        return $query->withCount(['employees', 
            'loanApplications as total_loans' => function ($q) use ($dateRange) {
                $q->whereBetween('created_at', $dateRange);
            }
        ])->get()->map(function ($branch) {
            return [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'employee_count' => $branch->employees_count,
                'total_loans' => $branch->total_loans
            ];
        });
    }
    /**
     * Get user activity
     */
    private function getUserActivity($dateRange, $branchId = null)
    {
        $query = User::query();
        
        if ($branchId) {
            $query->whereHas('employee', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        return $query->withCount([
            'loanApplications as loan_applications' => function ($q) use ($dateRange) {
                $q->whereBetween('created_at', $dateRange);
            }
        ])->get()->map(function ($user) {
            return [
                'user_id' => $user->id,
                'username' => $user->username,
                'loan_applications' => $user->loan_applications
            ];
        });
    }
    /**
     * Calculate average processing time for loans
     */
    private function calculateAverageProcessingTime($branchId = null)
    {
        $query = LoanApplication::query();
        
        if ($branchId) {
            $query->whereHas('employee', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        return round($query->avg(DB::raw('DATEDIFF(updated_at, created_at)')), 2);
    }
    /**
     * Generate system-wide reports with comprehensive analytics
     */
    public function generateSystemReport(Request $request)
    {
        // Authorize admin access
        Gate::allows('view-reports');

        // Validate and parse reporting period
        $period = $request->input('period', 'monthly');
        $branchId = $request->input('branch_id');

        // Determine date range
        $dateRange = $this->calculateDateRange($period, $request);

        // Base query with optional branch filtering
        $baseQuery = LoanApplication::query();
        if ($branchId) {
            $baseQuery->whereHas('employee', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
        }

        // Loan application statistics
        $loanStats = $baseQuery->whereBetween('created_at', $dateRange)
            ->selectRaw('
                COUNT(*) as total_applications,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_applications,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_applications,
                SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_applications,
                AVG(DATEDIFF(updated_at, created_at)) as avg_processing_days,
                SUM(approved_amount) as total_amount_disbursed
            ')
            ->first();

        // Compare with previous period for change percentages
        $previousPeriodStats = $this->calculatePreviousPeriodStats($period, $dateRange, $branchId);

        // Branch breakdown
        $branchBreakdown = $this->getBranchBreakdown($dateRange, $branchId);

        // User activity
        $userActivity = $this->getUserActivity($dateRange, $branchId);

        // System-wide statistics
        $systemStats = [
            'total_employees' => Employee::when($branchId, function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })->count(),
            'total_users' => User::when($branchId, function ($query) use ($branchId) {
                return $query->whereHas('employee', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            })->count(),
            'active_branches' => Branch::count(), 
            
        ];

        // Merge all statistics
        $reportData = array_merge(
            $loanStats->toArray(),
            $systemStats,
            [
                'branch_breakdown' => $branchBreakdown,
                'user_activity' => $userActivity,
                'employee_change_percent' => $previousPeriodStats['employee_change'] ?? null,
                'disbursed_change_percent' => $previousPeriodStats['disbursed_change'] ?? null,
            ]
        );

        // Log the report generation
        $this->logReportGeneration($reportData);

        return response()->json($reportData);
    }

    /**
     * Get detailed branch statistics with admin authorization
     */
    public function getBranchStatistics(Request $request)
    {
        // Authorize admin access
        Gate::allows('view-reports');

        // Validate and parse reporting period
        $period = $request->input('period', 'monthly');
        $branchId = $request->input('branch_id');

        // Determine date range
        $dateRange = $this->calculateDateRange($period, $request);

        // Retrieve detailed branch statistics
        $branchStats = Branch::when($branchId, function ($query) use ($branchId) {
            return $query->where('id', $branchId);
        })
        ->withCount(['employees as user_count'])
        ->with(['employees' => function ($query) use ($dateRange) {
            $query->withCount([
                'loanApplications as total_loans' => function ($q) use ($dateRange) {
                    $q->whereBetween('created_at', $dateRange);
                },
                'loanApplications as approved_loans' => function ($q) use ($dateRange) {
                    $q->whereBetween('created_at', $dateRange)
                      ->where('status', 'approved');
                },
                'loanApplications as rejected_loans' => function ($q) use ($dateRange) {
                    $q->whereBetween('created_at', $dateRange)
                      ->where('status', 'rejected');
                }
            ]);
        }])
        ->get()
        ->map(function ($branch) {
            $totalAmountDisbursed = LoanApplication::whereHas('employee', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            })
            ->where('status', 'approved')
            ->sum('approved_amount');

            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'user_count' => $branch->user_count,
                'total_loans' => $branch->employees->sum('total_loans'),
                'approved_loans' => $branch->employees->sum('approved_loans'),
                'rejected_loans' => $branch->employees->sum('rejected_loans'),
                'amount_disbursed' => $totalAmountDisbursed,
                'avg_processing_days' => $this->calculateAverageProcessingTime($branch->id)
            ];
        });

        // Log report access
        AuditLog::log(
            'view',
            'BranchStatistics',
            null,
            'Accessed branch statistics report',
            null,
            ['branch_id' => $branchId, 'period' => $period]
        );

        return response()->json($branchStats);
    }

    /**
     * Export loan applications report with admin authorization
     */
    public function exportLoanApplicationsReport(Request $request)
    {
        // Authorize admin access
        Gate::allows('view-reports');

        // Validate input parameters
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'status' => 'in:all,approved,pending,rejected',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        // Build query
        $query = LoanApplication::with(['employee', 'employee.branch'])
            ->whereBetween('created_at', [
                Carbon::parse($request->start_date),
                Carbon::parse($request->end_date)
            ]);

        // Optional status filter
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Optional branch filter
        if ($request->branch_id) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }

        // Get filtered loan applications
        $loanApplications = $query->get();

        // Log export action
        AuditLog::log(
            'export',
            'LoanApplications',
            null,
            'Exported loan applications report',
            null,
            [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'branch_id' => $request->branch_id
            ]
        );

        // Generate export (CSV, Excel, etc.)
        // Example CSV export:
        return $this->exportToCsv($loanApplications);
    }

    /**
     * Private helper method to export loan applications to CSV
     */
    private function exportToCsv($loanApplications)
    {
        $fileName = 'loan_applications_' . now()->format('YmdHis') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $columns = [
            'Application ID', 'Employee', 'Branch', 'Status', 
            'Amount', 'Created At', 'Updated At'
        ];

        $callback = function() use ($loanApplications, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($loanApplications as $application) {
                fputcsv($file, [
                    $application->id,
                    $application->employee->name,
                    $application->employee->branch->name,
                    $application->status,
                    $application->approved_amount,
                    $application->created_at,
                    $application->updated_at
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ... [Rest of the previous implementation remains the same]

    /**
     * Log report generation for audit purposes
     */
    private function logReportGeneration($reportData)
    {
        AuditLog::log(
            'generate_report',
            'SystemReport',
            null,
            'Generated system-wide report',
            null,
            [
                'total_applications' => $reportData['total_applications'] ?? 0,
                'total_amount_disbursed' => $reportData['total_amount_disbursed'] ?? 0,
                'generated_by' => auth()->id(),
            ]
        );
    }
}