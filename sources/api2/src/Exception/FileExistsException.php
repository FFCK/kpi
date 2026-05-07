<?php

namespace App\Exception;

class FileExistsException extends \RuntimeException
{
    public function __construct(
        private readonly string $filename,
        private readonly string $archiveName
    ) {
        parent::__construct("File '$filename' already exists.");
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getArchiveName(): string
    {
        return $this->archiveName;
    }
}
