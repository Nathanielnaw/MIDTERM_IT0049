<?php
namespace Tests\Unit;

use App\Libraries\PosImages;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;
use RuntimeException;

final class PosImagesTest extends CIUnitTestCase
{
    public function testRejectsDisguisedImage(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pos');
        file_put_contents($path, '<?php echo "unsafe";');
        $file = $this->getMockBuilder(UploadedFile::class)->setConstructorArgs([$path, 'fake.jpg', 'image/jpeg', filesize($path), UPLOAD_ERR_OK])->onlyMethods(['isValid'])->getMock();
        $file->method('isValid')->willReturn(true);
        try {
            $this->expectException(RuntimeException::class);
            (new PosImages())->store($file);
        } finally {
            unlink($path);
        }
    }

    public function testReencodesAndResizesImageWithSafeName(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD required for image processing test.');
        }
        $path = tempnam(sys_get_temp_dir(), 'pos');
        $image = imagecreatetruecolor(1200, 600);
        imagepng($image, $path);
        imagedestroy($image);
        $file = $this->getMockBuilder(UploadedFile::class)->setConstructorArgs([$path, '../../unsafe.png', 'image/png', filesize($path), UPLOAD_ERR_OK])->onlyMethods(['isValid'])->getMock();
        $file->method('isValid')->willReturn(true);
        $name = null;
        try {
            $name = (new PosImages())->store($file);
            $this->assertMatchesRegularExpression('/^[a-f0-9]{32}\.jpg$/', $name);
            $info = getimagesize(FCPATH . 'uploads/pos/' . $name);
            $this->assertSame([800, 400, IMAGETYPE_JPEG], array_slice($info, 0, 3));
        } finally {
            unlink($path);
            if ($name !== null) {
                unlink(FCPATH . 'uploads/pos/' . $name);
            }
        }
    }
}
