<?php
namespace WStrategies\BMB\Includes\Domain;

use DateTimeImmutable;

abstract class PostBase implements CustomPostInterface {
  /**
   * @var int
   */
  public $id;

  /**
   * @var string
   */
  public $title;

  /**
   * @var int
   *
   * ID of the user who created the bracket
   */
  public $author;

  /**
   * @var string
   */
  public $status;

  /**
   * @var DateTimeImmutable|false
   *
   * Date the bracket was published in UTC
   */
  public $published_date;

  /**
   * @var string
   *
   * Slug of the bracket
   */
  public $slug;

  /**
   * @var string
   *
   * Display name of the author
   */
  public $author_display_name;

  /**
   * @var string
   */
  public $thumbnail_url;

  /**
   * @var string
   */
  public $url;

  public function __construct(array $data) {
    $this->id = $data['id'] ?? null;
    $this->title = $data['title'] ?? '';
    $this->author = $data['author'] ?? null;
    $this->status = $data['status'] ?? 'publish';
    $this->published_date = $data['published_date'] ?? false;
    $this->slug = $data['slug'] ?? '';
    $this->author_display_name = $data['author_display_name'] ?? '';
    $this->thumbnail_url = $data['thumbnail_url'] ?? false;
    $this->url = $data['url'] ?? false;
  }

  abstract public static function get_post_type(): string;
  abstract public function get_post_meta(): array;

  public function get_post_data(): array {
    return [
      'import_id' => $this->id,
      'post_title' => $this->title,
      'post_author' => $this->author,
      'post_status' => $this->status,
      'post_name' => $this->slug,
      'post_type' => static::get_post_type(),
      'post_date_gmt' => $this->published_date,
    ];
  }

  /**
   * Only allow certain fields to be updated
   */
  public function get_update_post_data(): array {
    return [
      'ID' => $this->id,
      'post_title' => $this->title,
      'post_status' => $this->status,
      'post_author' => $this->author,
    ];
  }

  public function to_array(): array {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'author' => $this->author,
      'status' => $this->status,
      'published_date' => $this->published_date,
      'slug' => $this->slug,
      'author_display_name' => $this->author_display_name,
      'thumbnail_url' => $this->thumbnail_url,
      'url' => $this->url,
    ];
  }
}
