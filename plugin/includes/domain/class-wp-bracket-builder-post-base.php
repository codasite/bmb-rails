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

	/**
	 * @var string
	 * 
	 * Display name of the author
	 */
	public $author_display_name;

	public function __construct(array $data) {
		$this->id = isset($data['id']) ? $data['id'] : null;
		$this->title = isset($data['title']) ? $data['title'] : '';
		$this->author = isset($data['author']) ? $data['author'] : null;
		$this->status = isset($data['status']) ? $data['status'] : 'draft';
		$this->date = isset($data['date']) ? $data['date'] : false;
		$this->date_gmt = isset($data['date_gmt']) ? $data['date_gmt'] : false;
		$this->slug = isset($data['slug']) ? $data['slug'] : '';
		$this->author_display_name = isset($data['author_display_name']) ? $data['author_display_name'] : '';
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
			'author_display_name' => $this->author_display_name,
		];
	}
}
