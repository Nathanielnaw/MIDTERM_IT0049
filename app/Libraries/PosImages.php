<?php
namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

class PosImages
{
    public function store(?UploadedFile $file): ?string
    {
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (! $file->isValid() || $file->getSize() > 2 * 1024 * 1024) {
            throw new RuntimeException('Choose an image no larger than 2 MB.');
        }
        $info = @getimagesize($file->getTempName());
        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)
            || $info[0] > 4000 || $info[1] > 4000 || $info[0] * $info[1] > 8000000
            || ! in_array(strtolower($file->getClientExtension()), ['jpg', 'jpeg', 'png'], true)) {
            throw new RuntimeException('Use a JPEG or PNG up to 4000 pixels per side and 8 megapixels.');
        }
        if (! extension_loaded('gd')) {
            throw new RuntimeException('Image uploads require the PHP GD extension. You can save without an image.');
        }
        $source = $info[2] === IMAGETYPE_JPEG ? @imagecreatefromjpeg($file->getTempName()) : @imagecreatefrompng($file->getTempName());
        if ($source === false) {
            throw new RuntimeException('The image could not be decoded.');
        }
        $scale = min(1, 800 / max($info[0], $info[1]));
        $target = imagecreatetruecolor(max(1, (int) ($info[0] * $scale)), max(1, (int) ($info[1] * $scale)));
        imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255));
        imagecopyresampled($target, $source, 0, 0, 0, 0, imagesx($target), imagesy($target), $info[0], $info[1]);
        $directory = FCPATH . 'uploads/pos';
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('The upload directory is unavailable.');
        }
        $name = bin2hex(random_bytes(16)) . '.jpg';
        $saved = imagejpeg($target, $directory . '/' . $name, 85);
        imagedestroy($source);
        imagedestroy($target);
        if (! $saved) {
            throw new RuntimeException('The image could not be saved.');
        }
        return $name;
    }
}
