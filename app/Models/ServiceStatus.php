<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceStatus extends Model
{
    protected $fillable = ['name', 'description', 'status', 'sort_order'];

    public const STATUSES = ['operational', 'degraded', 'outage', 'maintenance'];

    /** A single aggregate label for the whole status page. */
    public static function overallStatus(): string
    {
        $statuses = static::pluck('status');
        if ($statuses->contains('outage'))      return 'outage';
        if ($statuses->contains('degraded'))    return 'degraded';
        if ($statuses->contains('maintenance')) return 'maintenance';
        return 'operational';
    }
}
