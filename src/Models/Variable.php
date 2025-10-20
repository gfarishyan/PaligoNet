<?php
namespace Gfarishyan\PaligoNet\Models;

class Variable extends Model {
  protected int $variable_id;

  protected int $variable_set_id;

  protected int $variant_id;

  protected string $value;


  public function getId() : int {
      return $this->variable_id;
  }

  public function getSetId() : int {
      return $this->variable_set_id;
  }

  public function getVariantId() : int {
      return $this->variant_id;
  }

  public function getValue() : string {
      return $this->value;
  }

}