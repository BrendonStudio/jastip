<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['key', 'value'];

    public static function put(string $key, $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
