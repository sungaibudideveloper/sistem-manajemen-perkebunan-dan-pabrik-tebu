<?php
// =====================================================
// FILE: app/Models/MasterData/Approval.php
// =====================================================
namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $table = 'approval';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'category',
        'activitygroup',
        'jumlahapproval',
        'idjabatanapproval1',
        'idjabatanapproval2',
        'idjabatanapproval3',
        'createdat',
        'updatedat'
    ];

    protected $casts = [
        'jumlahapproval' => 'integer',
        'idjabatanapproval1' => 'integer',
        'idjabatanapproval2' => 'integer',
        'idjabatanapproval3' => 'integer',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];
}