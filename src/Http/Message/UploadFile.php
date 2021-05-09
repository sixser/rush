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
     * @return void
     * @throws HttpException
     */
    public function moveTo(string $targetPath): void
    {
        $dir = dirname($targetPath);

        ! is_dir($dir) && ! mkdir($dir, 0777, true) &&
        throw new HttpException("Failed to move file, an error occurred while making $dir.");

        false === $this->status &&
        throw new HttpException("Failed to move file, $this->path has been moved.");

        ! rename($this->path, $targetPath) &&
        throw new HttpException("Failed to move file, an error occurred while moving to $targetPath.");

        $this->status = false;
    }

    /**
     * Check if the file has been moved
     * @return bool
     */
    public function isMoved(): bool
    {
        return false === $this->status;
    }

    /**
     * UploadFile destructor
     */
    public function __destruct()
    {
        file_exists($this->path) && unlink($this->path);
    }
}
