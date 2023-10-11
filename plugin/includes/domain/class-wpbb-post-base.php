<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-custom-post-interface.php';

abstract class Wpbb_PostBase implements Wpbb_CustomPostInterface
{
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

	public function __construct(array $data) {
		$this->id = $data['id'] ?? null;
		$this->title = $data['title'] ?? '';
		$this->author = $data['author'] ?? null;
		$this->status = $data['status'] ?? 'publish';
		$this->published_date = $data['published_date'] ?? false;
		$this->slug = $data['slug'] ?? '';
		$this->author_display_name = $data['author_display_name'] ?? '';
	}

	abstract static public function get_post_type(): string;
	abstract public function get_post_meta(): array;

	public function get_post_data(): array {
		return [
			'post_title' => $this->title,
			'post_author' => $this->author,
			'post_status' => $this->status,
			'post_type' => static::get_post_type(),
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
		];
	}
}
