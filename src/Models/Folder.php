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
  protected array $children;

  public function __construct(array $attributes = []) {
    $this->children = [];
    if (isset($attributes['children'])) {
      if (is_array($attributes['children'])) {
        //verify that each child converted to object
        foreach ($attributes['children'] as &$child) {
           if (is_array($child)) {
              if ($child['type'] == 'folder') {
                $child = new Folder($child);
              } else {
                $child = new Document($child);
              }
           }
        }
      }
    }
    parent::__construct($attributes);
  }

  public function getId() {
      return $this->id;
  }

  public function getName() {
      return $this->name;
  }

  public function getUuid() {
      return $this->uuid;
  }

  public function getType() {
      return $this->type;
  }

  public function getChildren() {
      if ($this->hasChildren()) {
          return $this->children;
      }

      return [];
  }

  public function hasChildren() {
    return (isset($this->children) && !empty($this->children));
  }
}