<?php

declare(strict_types = 1);

namespace Rush\Http\Message;

use Rush\Http\HttpException;

/**
 * Class UploadedFile
 * @package Rush\Http\Message
 */
class UploadFile
{
    /**
     * Temp Filename
     * @var string
     */
    protected string $path = '';

    /**
     * File Size
     * @var int
     */
    protected int $size = 0;

    /**
     * Upload Error
     * @var int
     */
    protected int $error = UPLOAD_ERR_OK;

    /**
     * Client File Name
     * @var string
     */
    protected string $name = '';

    /**
     * Client File type
     * @var string
     */
    protected string $type = '';

    /**
     * File Move Status
     * @var bool
     */
    protected bool $status = true;

    /**
     * UploadedFile constructor
     * @param string $path Temp filename.
     * @param string $name Client filename.
     * @param int $size File size.
     * @param string $type File media type.
     * @param int $error Upload error.
     */
    public function __construct(string $path, int $size, int $error, string $name, string $type)
    {
        $this->path = $path;
        $this->size = $size;
        $this->error = $error;
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Retrieve the filename sent by the client
     * @return string
     */
    public function getTempFilename(): string
    {
        return $this->path;
    }

    /**
     * Retrieve the file size
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file
     * @return int
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client
     * @return string
     */
    public function getClientFilename(): string
    {
        return $this->name;
    }

    /**
     * Retrieve the media type sent by the client
     * @return string
     */
    public function getClientMediaType(): string
    {
        return $this->type;
    }

    /**
     * Move the uploaded file to a new location
     * @param string $targetPath Path to which to move the uploaded file.
     * @return bool
     * @throws HttpException
     */
    public function moveTo(string $targetPath): bool
    {
        $dir = dirname($targetPath);
        if (is_dir($dir) === false && mkdir($dir, 0777, true) === false) {
            throw new HttpException("Fail to create directory({$dir})");
        }

        if ($this->status === false) {
            throw new HttpException("File has been moved");
        }

        if (rename($this->path, $targetPath) === false) {
            return false;
        }

        $this->status = false;

        return true;
    }

    /**
     * Check if the file has been moved
     * @return bool
     */
    public function isMoved(): bool
    {
        return $this->status === false;
    }

    /**
     * UploadFile destructor
     */
    public function __destruct()
    {
        if (file_exists($this->path) === true) unlink($this->path);
    }
}
