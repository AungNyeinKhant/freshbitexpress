<?php

namespace App\Models;

use App\Scopes\RestaurantScope;
use App\Scopes\ZoneScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AddOn extends Model
{
    protected $casts = [
        'price' => 'float',
        'img' => 'string',
        'restaurant_id' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }



    public function getNameAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'name') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    protected static function booted()
    {
        if(auth('vendor')->check() || auth('vendor_employee')->check())
        {
            static::addGlobalScope(new RestaurantScope);
        }
        static::addGlobalScope(new ZoneScope);

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function($query){
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }
}
