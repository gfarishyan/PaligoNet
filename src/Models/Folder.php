<?php

namespace Gfarishyan\PaligoNet\Models;

class Folder extends Model {
  /**
   * @var int id
   * The ID of the folder.
   */
  protected int $id;

  /**
   * @var string name
   * The name of the folder.
   */
  protected string $name;

  /**
   * @var string uuid
   * The UUID of the folder.
   */
  protected ?string $uuid;

  /**
   * @var string $type
   * The resource type.
   */
  protected string $type;

  /**
   * @var array $children
   * The folder's child folders.
   */
  protected $children;

  public function hasChildren() {
    return (isset($this->children) && !empty($this->children));
  }
}