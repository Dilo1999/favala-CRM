<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingOption extends Model
{
    use HasFactory;

    // The nine configurable option lists (spec §7).
    public const LEAD_SOURCE = 'lead_source';

    public const CUSTOMER_TYPE = 'customer_type';

    public const LEAD_STATUS = 'lead_status';

    public const PRODUCT_CATEGORY = 'product_category';

    public const TASK_TYPE = 'task_type';

    public const REQUEST_SOURCE = 'request_source';

    public const ACTIVITY_OUTCOME = 'activity_outcome';

    public const QUERY_SOURCE = 'query_source';

    public const QUERY_TYPE = 'query_type';

    public const GROUPS = [
        self::LEAD_SOURCE => 'Lead Source Options',
        self::CUSTOMER_TYPE => 'Customer Type Options',
        self::LEAD_STATUS => 'Lead Status Options',
        self::PRODUCT_CATEGORY => 'Product Category Options',
        self::TASK_TYPE => 'Task Type Options',
        self::REQUEST_SOURCE => 'Request Source Options',
        self::ACTIVITY_OUTCOME => 'Activity Outcome Options',
        self::QUERY_SOURCE => 'Query Source Options',
        self::QUERY_TYPE => 'Query Type Options',
    ];

    protected $fillable = ['group', 'value', 'sort_order'];

    public static function options(string $group): array
    {
        return static::query()
            ->where('group', $group)
            ->orderBy('sort_order')
            ->orderBy('value')
            ->pluck('value', 'value')
            ->all();
    }
}
