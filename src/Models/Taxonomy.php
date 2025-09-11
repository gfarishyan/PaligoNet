<?php

namespace Gfarishyan\PaligoNet\Models;

class Taxonomy extends Model {
  /**
   * @var int id
   * The ID of the taxonomy.
   */
  protected $id;

  /**
   * @var string title
   * The title of the taxonomy.
   */
   protected $tite;

   /**
    * @var int color
    * The color of the taxonomy.
    */
   protected $color;

   /**
    * @var parent
    * The ID of this taxonomy parent.
    */

   protected $parent;


}
