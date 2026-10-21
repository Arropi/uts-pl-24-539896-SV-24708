<?php

namespace App\Models;

use Database\Factories\CoralColonyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoralColony extends Model
{
    /** @use HasFactory<CoralColonyFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'coral_colonies';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kode_fragmen',
        'spesies_ilmiah',
        'zona_terumbu',
        'kedalaman_meter',
        'persentase_hidup',
        'status_kesehatan',
        'catatan_observasi',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kedalaman_meter' => 'float',
            'persentase_hidup' => 'integer',
        ];
    }
}
