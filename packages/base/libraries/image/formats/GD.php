<?php
namespace packages\base\Image;

use packages\base\{Image, IO\File};

class GD extends Image {
	/** @var resource image GD resource */
	protected $image;

	/**
	 * Construct an image object with three ways:
	 * 	1. pass a file to {$param}
	 * 	2. pass other image to {$param}
	 * 	3. pass new image width to {$param}
	 *  4. pass a GD image resouce.
	 * 
	 * @param packages\base\IO\File|packages\base\Image|int|resouce
	 * @param int|null $height height of new image in third method
	 * @param packages\base\Image\Color $bg background color of new image in third method
	 * @throws packages\base\IO\NotFoundException if passed file cannot be found.
	 */
	public function __construct($param = null, ?int $height = null, ?Image\Color $bg = null){
		if (is_resource($param)) {
			$this->fromGDImage($param);
		} else {
			parent::__construct($param, $height, $bg);
		}
	}

	/**
	 * Save the image to a file.
	 * 
	 * @param packages\base\IO\File $file
	 * @param int $quality
	 * @return void
	 */
	public function saveToFile(File $file, int $quality = 75): void {
		imagegd($this->image, $file->getPath());
	}

	/**
	 * Get width of current image.
	 * 
	 * @return int in px
	 */
	public function getWidth(): int {
		return imagesx($this->image);
	}

	/**
	 * Get height of current image.
	 * 
	 * @return int in px
	 */
	public function getHeight(): int {
		return imagesy($this->image);
	}

	/**
	 * Resize the image to new width and height
	 * 
	 * @param int $width in px
	 * @param int $height in px
	 * @return packages\base\Image resized image
	 */
	public function resize(int $width, int $height): Image {
		$color = Color::fromRGBA(0,0,0,0);
		$new = new static($width, $height, $color);
		imagecopyresampled($new->image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		return $new;
	}

	/**
	 * Get color of specified pixel.
	 * 
	 * @param int $x
	 * @param int $y
	 * @return packages\base\Image\Color
	 */
	public function colorAt(int $x , int $y): Color {
		$rgb = imagecolorat($this->image, $x, $y);
		$colors = imagecolorsforindex($this->image, $rgb);
		$colors['alpha'] = round((127 -  $colors['alpha']) / 127);
		return Color::fromRGBA($colors['red'], $colors['green'], $colors['blue'], $colors['alpha']);
	}

	/**
	 * Set color of specified pixel.
	 * 
	 * @param int $x
	 * @param int $y
	 * @param packages\base\Image\Color $color
	 * @return void
	 */
	public function setColorAt(int $x, int $y, Color $color): void {
		$colors = $color->toRGBA();
		$colors[3] = round(127 - ($colors[3] * 127));
		$rgba = imagecolorallocatealpha($this->image, $colors[0], $colors[1], $colors[2], $colors[3]);
		imagesetpixel($this->image,$x, $y, $rgba);
	}
	
	/**
	 * Save the image to a file.
	 * 
	 * @param packages\base\IO\File $file
	 * @param int $quality
	 * @return void
	 */
	public function getExtension(): string {
		return 'gd';
	}

	/**
	 * Place anthor image on current image.
	 * 
	 * @param int $x x-coordinate of destination point.
	 * @param int $y y-coordinate of destination point. 
	 * @param float $opacity alpha between 0-1
	 * @return void
	 */
	public function paste(Image $image, int $x, int $y, float $opacity = 1): void {
		if (!($image instanceof GD)) {
			throw new Exception("non-GD images not supported");
		}
		$width = $image->getWidth();
		$height = $image->getHeight();
		if ($image instanceof PNG and $opacity < 1) {
			$cut = imagecreatetruecolor($width, $height);
			imagecopy($cut, $this->image, 0, 0, $x, $y, $width, $height);
			imagecopy($cut, $image->image, 0, 0, 0, 0, $width, $height);
			imagecopymerge($this->image, $cut, $x, $y, 0, 0, $width, $height, $opacity * 100);
		} else {
			imagecopy($this->image, $image->image, $x, $y, 0, 0, $width, $height);
		}
	}

	/**
	 * Copy a part of image starting at the x,y coordinates with a width and height.
	 * 
	 * @param int $x x-coordinate of point.
	 * @param int $y y-coordinate of point.
	 * @param int $width
	 * @param int $height
	 * @return Image
	 */
	public function copy(int $x, int $y, int $width, $height): Image {
		$new = new static($width, $height, Color::fromRGBA(0, 0, 0, 0));
		imagecopy($new->image, $this->image, 0, 0, $x, $y, $width, $height);
		return $new;
	}

	/**
	 * Rotate an image with a given angle
	 * The center of rotation is the center of the image, and the rotated image may have different dimensions than the original image.
	 * 
	 * @param float $angle Rotation angle, in degrees. The rotation angle is interpreted as the number of degrees to rotate the image anticlockwise.
	 * @param Image\Color $bg Specifies the color of the uncovered zone after the rotation.
	 * @return Image Rotated image
	 */
	public function rotate(float $angle, Image\Color $bg): Image {
		$colors = $bg->toRGBA();
		$colors[3] = round(127 - ($colors[3] * 127));
		$rgba = imagecolorallocatealpha($this->image, $colors[0], $colors[1], $colors[2], $colors[3]);
		$rotated = imagerotate($this->image, $angle, $rgba);
		return new static($rotated);
	}

	/**
	 * release the GD resource
	 * 
	 * @return void
	 */
	public function __destruct() {
		if (is_resource($this->image)) {
			imagedestroy($this->image);
		}
	}

	/**
	 * Read the image from constructor file.
	 * 
	 * @return void
	 */
	protected function fromFile(): void {
		$this->image = imagecreatefromgd($this->file->getPath());
	}

	/**
	 * Create new image with provided background color
	 * 
	 * @param int $width
	 * @param int $height
	 * @param packages\base\Image\Color $bg
	 * @return void
	 */
	protected function createBlank(int $width, int $height, Image\Color $bg): void {
		$this->image = imagecreatetruecolor($width, $height);
		$colors = $bg->toRGBA();
		$colors[3] = round(127 - ($colors[3] * 127));
		$rgba = imagecolorallocatealpha($this->image, $colors[0], $colors[1], $colors[2], $colors[3]);
		imagefilledrectangle($this->image, 0, 0, $width, $height, $rgba);
	}

	/**
	 * Copy anthor image to current image;
	 * 
	 * @param packages\base\Image $other source image
	 * @return void
	 */
	protected function fromImage(image $other): void {
		if ($other instanceof self) {
			$this->image = $other->image;
		} else {
			parent::fromImage($other);
		}
	}

	/**
	 * Construct a image from GD image resouce.
	 * 
	 * @param resource $image resource image
	 * @return void
	 */
	protected function fromGDImage($image): void {
		$this->image = $image;
	}
}
