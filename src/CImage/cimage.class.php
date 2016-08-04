<?php

class CImage {
    private $imgPath;
    private $cachePath;
    private $maxWidth = 2000;
    private $maxHeight = 2000;

    function __construct($imgPath, $cachePath) {
        $this->imgPath = $imgPath;
        $this->cachePath = $cachePath;
    }

    private function validateInputs($src, $pathToImage, $saveAs, $quality, $ignoreCache, $newWidth, $newHeight, $cropToFit, $sharpen, $gray) {
        is_dir($this->imgPath) or $this->errorMessage('The image dir is not a valid directory.');
        is_writable($this->cachePath) or $this->errorMessage('The cache dir is not a writable directory.');
        isset($src) or $this->errorMessage('Must set src-attribute.');
        preg_match('#^[a-z0-9A-Z-_\.\/]+$#', $src) or $this->errorMessage('Filename contains invalid characters.');
        substr_compare($this->imgPath, $pathToImage, 0, strlen($this->imgPath)) == 0 or $this->errorMessage('Security constraint: Source image is not directly below the directory IMG_PATH.');
        is_null($saveAs) or in_array($saveAs, array('png', 'jpg', 'jpeg')) or $this->errorMessage('Not a valid extension to save image as');
        is_null($quality) or (is_numeric($quality) and $quality > 0 and $quality <= 100) or $this->errorMessage('Quality out of range');
        is_null($newWidth) or (is_numeric($newWidth) and $newWidth > 0 and $newWidth <= $this->maxWidth) or $this->errorMessage('Width out of range');
        is_null($newHeight) or (is_numeric($newHeight) and $newHeight > 0 and $newHeight <= $this->maxHeight) or $this->errorMessage('Height out of range');
        is_null($cropToFit) or ($cropToFit and $newWidth and $newHeight) or $this->errorMessage('Crop to fit needs both width and height to work');
    }

    public function ProcessImage($src, $saveAs, $quality, $ignoreCache, $newWidth, $newHeight, $cropToFit, $sharpen, $gray, $verbose = false) {
        $pathToImage = realpath($this->imgPath . $src);
        $this->validateInputs($src, $pathToImage, $saveAs, $quality, $ignoreCache, $newWidth, $newHeight, $cropToFit, $sharpen, $gray);
        if($verbose) $this->printVerbose();
        
        //Calculate new width and height for the image
        $res = $this->calculateImageDimensions($pathToImage, $cropToFit, $newWidth, $newHeight, $verbose);
        $newWidth = $res['newWidth'];
        $newHeight = $res['newHeight'];
        $cropHeight = $res['cropHeight'];
        $cropWidth = $res['cropWidth'];

        //Check cache for image
        $cacheName = $this->createCacheName($src, $pathToImage, $saveAs, $quality, $cropToFit, $newHeight, $newWidth, $sharpen, $gray, $verbose); //Get cache name
        if(!$ignoreCache) {
            $cache = $this->cacheFileExistAndValid($pathToImage, $cacheName, $verbose); //Check cache folder
            if($cache) {//Does it exist
                $this->outputImage($cacheName, $verbose); //Show it (this exits the code)
            }
        }

        //Open original image from file
        $image = $this->openOriginalImage($pathToImage, $verbose);
        //Resize the image if needed
        $image = $this->resizeImage($image, $pathToImage, $cropToFit, $newWidth, $newHeight, $cropWidth, $cropHeight, $verbose);
        //Apply filters
        $image = $this->addFilters($image, $sharpen, $gray);

        $fileExtension = pathinfo($pathToImage)['extension'];
        $saveAs = is_null($saveAs) ? $fileExtension : $saveAs;
        $this->saveImage($image, $cacheName, $quality, $saveAs, $verbose, filesize($pathToImage));//Save image in cache with given extension
        $this->outputImage($cacheName, $verbose);//Show it (this exits the code)
    }

    function calculateImageDimensions($pathToImage, $cropToFit, $newWidth, $newHeight, $verbose = false) {
        $imgInfo = list($width, $height, $type, $attr) = getimagesize($pathToImage);
        !empty($imgInfo) or errorMessage("The file doesn't seem to be an image.");

        $aspectRatio = $width / $height;

        if($cropToFit && $newWidth && $newHeight) {
            $targetRatio = $newWidth / $newHeight;
            $cropWidth   = $targetRatio > $aspectRatio ? $width : round($height * $targetRatio);
            $cropHeight  = $targetRatio > $aspectRatio ? round($width  / $targetRatio) : $height;
            if($verbose) { $this->verbose("Crop to fit into box of {$newWidth}x{$newHeight}. Cropping dimensions: {$cropWidth}x{$cropHeight}."); }
        }
        else if($newWidth && !$newHeight) {
            $newHeight = round($newWidth / $aspectRatio);
            if($verbose) { $this->verbose("New width is known {$newWidth}, height is calculated to {$newHeight}."); }
        }
        else if(!$newWidth && $newHeight) {
            $newWidth = round($newHeight * $aspectRatio);
            if($verbose) { $this->verbose("New height is known {$newHeight}, width is calculated to {$newWidth}."); }
        }
        else if($newWidth && $newHeight) {
            $ratioWidth  = $width  / $newWidth;
            $ratioHeight = $height / $newHeight;
            $ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;
            $newWidth  = round($width  / $ratio);
            $newHeight = round($height / $ratio);
            if($verbose) { $this->verbose("New width & height is requested, keeping aspect ratio results in {$newWidth}x{$newHeight}."); }
        }
        else {
            $newWidth = $width;
            $newHeight = $height;
            if($verbose) { $this->verbose("Keeping original width & heigth."); }
        }

        return array(
                'newWidth' => $newWidth,
                'newHeight' => $newHeight,
                'cropWidth' => isset($cropWidth) ? $cropWidth : null,
                'cropHeight' => isset($cropWidth) ? $cropHeight : null
            );
    }

    private function createCacheName($src, $pathToImage, $saveAs, $quality, $cropToFit, $newHeight, $newWidth, $sharpen, $gray, $verbose = false) {
        $parts          = pathinfo($pathToImage);
        $fileExtension  = $parts['extension'];
        $saveAs         = is_null($saveAs) ? $fileExtension : $saveAs;
        $quality_       = is_null($quality) ? null : "_q{$quality}";
        $cropToFit_     = is_null($cropToFit) ? null : "_cf";
        $sharpen_       = is_null($sharpen) ? null : "_s";
        $gray_       = is_null($gray) ? null : "_g";
        $dirName        = preg_replace('/\//', '-', dirname($src));
        $cacheFileName = $this->cachePath . "-{$dirName}-{$parts['filename']}_{$newWidth}_{$newHeight}{$quality_}{$cropToFit_}{$sharpen_}{$gray_}.{$saveAs}";
        $cacheFileName = preg_replace('/^a-zA-Z0-9\.-_/', '', $cacheFileName);

        if($verbose) { $this->verbose("Cache file is: {$cacheFileName}"); }

        return $cacheFileName;
    }

    private function cacheFileExistAndValid($pathToImage, $cacheName, $verbose = false) {
        $imageModifiedTime = filemtime($pathToImage);
        $cacheModifiedTime = is_file($cacheName) ? filemtime($cacheName) : null;

        if((is_file($cacheName)) && $imageModifiedTime < $cacheModifiedTime) {
            if($verbose) { $this->verbose("Cache file is valid, output it."); }
            return true;
        }

        if($verbose) {$this->verbose("Cache is not valid, process image and create a cached version of it."); }
        return false;

    }

    /**
     * Display error message.
     *
     * @param string $message the error message to display.
     */
    private function errorMessage($message) {
        header("Status: 404 Not Found");
        die('img.php says 404 - ' . htmlentities($message));
    }

    /**
     * Display log message.
     *
     * @param string $message the log message to display.
     */
    private function verbose($message) {
        echo "<p>" . htmlentities($message) . "</p>";
        //$this->verboseOutput .= "<p>" . htmlentities($message) . "</p>";
    }

    private function printVerbose() {
        $query = array();
        parse_str($_SERVER['QUERY_STRING'], $query);
        unset($query['verbose']);
        $url = '?' . http_build_query($query);

        echo <<<EOD
        <html lang='en'>
        <meta charset='UTF-8'>
        <title>img.php verbose mode</title>
        <h1>Verbose mode</h1>
        <p><a href=$url><code>$url</code></a><br>
        <img src='{$url}'></p>
EOD;
    }

    /**
     * Output an image together with last modified header.
     *
     * @param string $file as path to the image.
     * @param boolean $verbose if verbose mode is on or off.
     */
    function outputImage($file, $verbose = false) {
        $imgInfo = list($width, $height, $type, $attr) = getimagesize($file);
        !empty($imgInfo) or $this->errorMessage("The file doesn't seem to be an image.");
        $mime   = $imgInfo['mime'];

        $lastModified = filemtime($file);
        $gmdate = gmdate("D, d M Y H:i:s", $lastModified);

        if($verbose) {
            $filesize = filesize($file);
            $this->verbose("Image file: {$file}");
            $this->verbose("Image information: " . print_r($imgInfo, true));
            $this->verbose("Image width x height (type): {$width} x {$height} ({$type}).");
            $this->verbose("Image file size: {$filesize} bytes.");
            $this->verbose("Image mime type: {$mime}.");

            $this->verbose("Memory peak: " . round(memory_get_peak_usage() /1024/1024) . "M");
            $this->verbose("Memory limit: " . ini_get('memory_limit'));
            $this->verbose("Time is {$gmdate} GMT.");
        }

        


        if(!$verbose) {
            header('Last-Modified: ' . $gmdate . ' GMT');
            header("Cache-Control: max-age=3600, must-revalidate");
            $ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", $lastModified + 3600) . " GMT";
            Header($ExpStr);
        }

        if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified) {
            if($verbose) { $this->verbose("Would send header 304 Not Modified, but its verbose mode."); exit; }
            header('HTTP/1.0 304 Not Modified');
        } else {  
            if($verbose) { $this->verbose("Would send header to deliver image with modified time: {$gmdate} GMT, but its verbose mode."); exit; }
            header('Content-type: ' . $mime);
            readfile($file);
        }
        exit;
    }

    private function addFilters($image, $sharpen = false, $gray = false) {
        echo("woop");
        if($sharpen) $image = $this->sharpenImage($image);
        if($gray) $image = $this->grayImage($image);
        return $image;
    }
    /**
     * Sharpen image
     */
    function sharpenImage($image) {
        $matrix = array(
            array(-1,-1,-1,),
            array(-1,16,-1,),
            array(-1,-1,-1,)
        );
        $divisor = 8;
        $offset = 0;
        imageconvolution($image, $matrix, $divisor, $offset);
        return $image;
    }

    function grayImage($image) {
        imagefilter($image, IMG_FILTER_GRAYSCALE);
        return $image;
    }

    function resizeImage($image, $pathToImage, $cropToFit, $newWidth, $newHeight, $cropWidth, $cropHeight, $verbose = false) {
        $imgInfo = list($width, $height, $type, $attr) = getimagesize($pathToImage);

        if($cropToFit) {
            if($verbose) { $this->verbose("Resizing, crop to fit."); }
            $cropX = round(($width - $cropWidth) / 2);
            $cropY = round(($height - $cropHeight) / 2);
            $imageResized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($imageResized, $image, 0, 0, $cropX, $cropY, $newWidth, $newHeight, $cropWidth, $cropHeight);
            $image = $imageResized;
            $width = $newWidth;
            $height = $newHeight;
        }
        else if(!($newWidth == $width && $newHeight == $height)) {
            if($verbose) { $this->verbose("Resizing, new height and/or width."); }
            $imageResized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($imageResized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $image  = $imageResized;
            $width  = $newWidth;
            $height = $newHeight;
        }
        return $image;
    }

    private function openOriginalImage($pathToImage, $verbose = false) {
        $fileExtension = pathinfo($pathToImage)['extension'];

        if($verbose) { $this->verbose("File extension is: {$fileExtension}"); }

        switch($fileExtension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($pathToImage);
                if($verbose) { $this->verbose("Opened the image as a JPEG image."); }
            break;

            case 'png':
                $image = imagecreatefrompng($pathToImage);
                if($thisverbose) { $this->verbose("Opened the image as a PNG image."); }
            break;

            default: $this->errorMessage('No support for this file extension.');
        }
        return $image;
    }

    private function saveImage($image, $cacheFileName, $quality, $saveAs, $verbose = false, $filesize = INF) {
        switch($saveAs) {
            case 'jpeg':
            case 'jpg':
                if($verbose) { $this->verbose("Saving image as JPEG to cache using quality = {$quality}."); }
                imagejpeg($image, $cacheFileName, $quality);
                break;

            case 'png':
                if($verbose) { $this->verbose("Saving image as PNG to cache."); }
                imagepng($image, $cacheFileName);
                break;

            default:
                $this->errorMessage('No support to save as this file extension.');
                break;
        }

        if($verbose) {
            clearstatcache();
            $cacheFilesize = filesize($cacheFileName);
            $this->verbose("File size of cached file: {$cacheFilesize} bytes.");
            $this->verbose("Cache file has a file size of " . round($cacheFilesize/$filesize*100) . "% of the original size.");
        }
    }

    public function uploadImage($files, $dir) {
        $pathToImage = realpath($this->imgPath . $dir);
        foreach($files as $file) {
            if($file['error'] == UPLOAD_ERR_OK) {
                $tmp_name = $file['tmp_name'];
                $name = $file['name'];
                move_uploaded_file($tmp_name, "$pathToImage/$name");
            }
            else
                die("UploadImage: Something went wrong!");
        }
    }
}