<?php

namespace App\Services;

use App\Models\LoanApplication;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFService
{
    /**
     * Generate loan application PDF
     */
    public function generateLoanApplicationPDF(LoanApplication $loanApplication)
    {
        $employee = $loanApplication->employee;
        $bankingDetails = $employee->bankingDetails;
        $loanType = $loanApplication->loanType;
        
        $data = [
            'loan' => $loanApplication,
            'employee' => $employee,
            'banking_details' => $bankingDetails,
            'loan_type' => $loanType,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ];
        
        $pdf = PDF::loadView('pdfs.loan_application', $data);
        
        return $pdf;
    }
    
    /**
     * Save PDF to storage
     */
    public function savePDF(LoanApplication $loanApplication)
    {
        $pdf = $this->generateLoanApplicationPDF($loanApplication);
        $fileName = 'loan_application_' . $loanApplication->loan_id . '.pdf';
        $path = 'pdfs/' . $fileName;
        
        // Save to storage
        \Storage::put($path, $pdf->output());
        
        return $path;
    }
}