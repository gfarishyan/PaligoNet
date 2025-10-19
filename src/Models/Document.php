<?php

namespace Gfarishyan\PaligoNet\Models;

use XMLReader;

class Document extends Model {

    /**
     * @var int id
     *   The id of a document.
     */
    protected ?int $id;

    /**
     * @var string $name
     * The name of document.
     */
    protected string $name;

    /**
     * @var string uuid
     * the uuid of document
     */
    protected string $uuid;

    /**
     * @var string type
     * The type of resource
     */
    protected string $type;

    /**
     * Document subtype
     * @var string
     */
    protected ?string $subtype;

    /**
     * @var int creator
     * the creator
     */
    protected $creator;

    /**
     * @var int owner
     * Owner of document.
     */
    protected $owner;

    /**
     * @var int author
     * Author of document.
     */

    protected $author;

    /**
     * @var int created_at
     * The creation date of document in unix timestamp
     */
    protected $created_at;

    /**
     * @var int modified_at
     * The modified date of document in unix timestamp.
     */
    protected $modified_at;

    /**
     * @var bool checkout
     * The checkout status of document.
     */
    protected $checkout;

    /**
     * @var int checkout_user
     * User that has checked out document
     */
    protected $checkout_user;

    /**
     * @var int  parent_resource
     * The ID of the document's parent resource.
     */
    protected $parent_resource;

    /**
     * @var string content
     * the content of document
     */

    protected $content;

    /**
     * @var array languages
     * A list of all the documents translations.
     */
    protected $languages;

    /**
     * @var taxonomies
     * The document's taxonomies.
     */
    protected $taxonomies;

    /**
     * @var custom_attributes
     * A list of custom attributes on the document.
     */

    protected $custom_attributes;


    protected $release_status;
    /**
     * Cleanup content from xml and return original
     */
    public function getOriginalContent() {
      if (empty($this->content)) {
        return null;
      }

      try {
        $reader = XmlReader::XML($this->content);
        //most probably this is a simple screen
        if (!$reader->isValid()) {
            $reader->close();
            return $this->content;
        }

        //let's loop over xml document and create domdocument


      } catch (\Exception $e) {

      }
      return $this->content;
    }
}
