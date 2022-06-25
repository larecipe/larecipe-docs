<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @return string
     */
    public function path()
    {
        return route('packages.show', [$this->packagist_namespace , $this->packagist_project_name]);
    }
}
