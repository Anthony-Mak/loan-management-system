<?php  

namespace App\Models;  

use Illuminate\Database\Eloquent\Model; 
use App\Traits\AuditLogTrait;  

class LoanType extends Model 
{     
    use AuditLogTrait;       

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'loan_type_id';     

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'interest_rate',
        'max_amount',
        'max_term'
    ];     

    /**
     * Get the loan applications for this loan type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function applications()     
    {         
        return $this->hasMany(LoanApplication::class);     
    } 

    /**
     * Additional custom audit logging method specific to LoanType.
     *
     * @param string $actionType
     * @param string $description
     * @param array|null $data
     */
    public function logLoanTypeAction($actionType, $description, $data = null)
    {
        $this->logCustomAction(
            $actionType, 
            $description, 
            $data ?? [
                'name' => $this->name,
                'interest_rate' => $this->interest_rate,
                'max_amount' => $this->max_amount
            ]
        );
    }
}