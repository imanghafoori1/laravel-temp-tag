<?php

namespace Imanghafoori\TempTagTests\Requirements\Stubs\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Imanghafoori\Tags\Traits\hasTempTags;

class User extends Authenticatable
{
    use hasTempTags;
    protected $guarded = [];
}
