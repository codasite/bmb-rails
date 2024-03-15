<?php
namespace WStrategies\BMB\Includes\Domain;

use DateTime;
use DateTimeImmutable;

abstract class PostBase implements CustomPostInterface {
  public ?int $id;
  public ?int $author;
  public string $title;
  public string $status;
  public string $slug;
  public string $author_display_name;
  public string $thumbnail_url;
  public string $url;
  protected string $content;

  /**
   * @var DateTimeImmutable|false
   */
  public $published_date;

  public function __construct(array $data) {
    $this->id = $data['id'] ?? null;
    $this->author = $data['author'] ?? null;
    $this->published_date = $data['published_date'] ?? false;
    $this->title = $data['title'] ?? '';
    $this->status = $data['status'] ?? 'publish';
    $this->slug = $data['slug'] ?? '';
    $this->author_display_name = $data['author_display_name'] ?? '';
    $this->thumbnail_url = $data['thumbnail_url'] ?? '';
    $this->url = $data['url'] ?? '';
    $this->content = $data['content'] ?? '';
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
      'post_content' => $this->content,
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
