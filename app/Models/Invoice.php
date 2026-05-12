<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'client_id', 'invoice_number', 'status',
        'issue_date', 'due_date', 'subtotal', 'tax_rate',
        'tax_amount', 'total', 'notes'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public static function generateNumber(): string
    {
        $year  = now()->year;
        $last  = static::whereYear('created_at', $year)->count() + 1;
        return 'INV-' . $year . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}
