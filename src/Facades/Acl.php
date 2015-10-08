<?php
namespace Morilog\Acl\Services\Acl\Facades;

use Illuminate\Support\Facades\Facade;

class Acl extends Facade
{
    protected static function getFacadeAccessor() { return 'morilog.acl'; }
}