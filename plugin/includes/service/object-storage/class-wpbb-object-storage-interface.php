<?php
interface Wpbb_Object_Storage_Interface {

	/**
	 * Upload a file to the object storage.
	 * @param  string $file The path to the file to upload.
	 * @param  string $name The name to give the file in the object storage.
	 * @param  array  $args Optional. Additional arguments to pass to the object storage.
	 * 
	 * @return string The URL to the file in the object storage.
	 */
	public function upload($file, $name, $args = []): string;
}
