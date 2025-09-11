<?php

namespace Gfarishyan\PaligoNet\Models;

class Model {
    public function get($name) {
      if (!property_exists($this, $name)) {
          throw new \Exception("Property $name does not exist");
      }
      return $this->$name ?? null;
    }

    public function set($name, $value) :Model {
      $this->$name = $value;
      return $this;
    }
}