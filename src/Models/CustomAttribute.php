<?php

namespace Gfarishyan\PaligoNet\Models;

class CustomAttribute extends Model {
    /**
     * @var string name
     * The name of the attribute.
     */
    protected string $name;

    /**
     * @var string value
     * The value of the attribute.
     */
    protected ?string $value;
}
