<?php

namespace App\Models\Accounts;

use App\Models\BankAccounts;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    public function upload()
    {
        return $this->belongsTo(Upload::class, 'upload_id', 'id');
    }

    public function head()
    {
        return $this->belongsTo(AccountHead::class, 'expense_head', 'id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccounts::class, 'bank_account_id', 'id');
    }
}
