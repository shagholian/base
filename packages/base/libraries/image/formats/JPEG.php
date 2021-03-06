<?php
namespace packages\base\Image;

use packages\base\IO\File;

class JPEG extends GD {

	/**
	 * Save the image to a file.
	 * 
	 * @param packages\base\IO\File $file
	 * @param int $quality
	 * @return void
	 */
	public function saveToFile(File $file, int $quality = 75): void {
		imagejpeg($this->image, $file->getPath(), $quality);
	}

	/**
	 * Get format of current image.
	 * 
	 * @return string
	 */
	public function getExtension(): string {
		return 'jpg';
	}

	/**
	 * Read the image from constructor file.
	 * 
	 * @return void
	 */
	protected function fromFile(): void {
		$this->image = imagecreatefromjpeg($this->file->getPath());
	}
}
