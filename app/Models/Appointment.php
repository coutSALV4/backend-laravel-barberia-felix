<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'barber_id',
        'receptionist_id',
        'status',
        'appointment_date',
        'start_time',
        'end_time',
        'notes',
        'total_price',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'total_price'      => 'decimal:2',
    ];

    // ── Relaciones ───────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function barber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'barber_id');
    }

    public function receptionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receptionist_id');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'appointment_services')
                    ->withPivot('price_at_time');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    // ── Scopes ───────────────────────────────────────────────

    /** Filtra por fecha exacta */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('appointment_date', $date);
    }

    /** Filtra por barbero */
    public function scopeForBarber($query, int $barberId)
    {
        return $query->where('barber_id', $barberId);
    }

    /** Solo citas activas (no canceladas ni no_show) */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'no_show']);
    }
}