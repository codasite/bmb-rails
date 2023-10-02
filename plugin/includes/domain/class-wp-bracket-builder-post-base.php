<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-custom-post-interface.php';

abstract class Wp_Bracket_Builder_Post_Base implements Wp_Bracket_Builder_Custom_Post_Interface {
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
	 * Date the bracket was created
	 */
	public $date;

	/**
	 * @var DateTimeImmutable|false
	 * 
	 * Date the bracket was created in GMT
	 */
	public $date_gmt;

	/**
	 * @var string
	 * 
	 * Slug of the bracket
	 */
	public $slug;

	public function __construct(
		int $id = null,
		string $title = '',
		int $author = null,
		string $status = 'draft',
		DateTimeImmutable|false $date = false,
		DateTimeImmutable|false $date_gmt = false,
		string $slug = '',
	) {
		$this->id = $id;
		$this->title = $title;
		$this->author = $author;
		$this->status = $status;
		$this->date = $date;
		$this->date_gmt = $date_gmt;
		$this->slug = $slug;
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
			'date' => $this->date,
			'date_gmt' => $this->date_gmt,
			'slug' => $this->slug,
		];
	}
}
