<?php

namespace App\Services\Converter;

use App\Models\Files;
use Ramsey\Uuid\Uuid;
use Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileHandler
{
    private string $path;
    private string $fileNameWithExtension;
    private array $pages;
    private string $groupId;
    private string $urlPDFFile;

    public function __construct(
        private UploadedFile $file,
        private string $rawPages
    ) {
        $this->parsePages();
    }

    public function save()
    {
        $this->groupId               = Uuid::uuid4();
        $this->fileNameWithExtension = strip_tags($this->file->getClientOriginalName());
        $this->path                  = 'public/upload/' . $this->groupId . '/';
        $this->file->storeAs($this->path, $this->fileNameWithExtension);
        $this->file->storeAs($this->path, $this->fileNameWithExtension, 's3');
        $this->urlPDFFile = \Storage::disk('s3')->url($this->path . $this->fileNameWithExtension);
        $fileInstance     = new Files(
            [
                'group_id' => $this->groupId,
                'path'     => $this->urlPDFFile,
                'name'     => $this->fileNameWithExtension,
                'mime'     => $this->file->getClientMimeType(),
                'size'     => $this->file->getSize(),
            ]
        );
        $fileInstance->save();
    }

    private function parsePages(): void
    {
        if (Str::length($this->rawPages) === 1) {
            $this->pages = [
                'pages' => [(int)$this->rawPages]
            ];
        }
        if (Str::contains($this->rawPages, 'rand|')) {
            $numberOfPages = (int)Str::after($this->rawPages, 'rand|');
            $this->pages   = ['rand' => $numberOfPages];
        } elseif (Str::contains($this->rawPages, ',')) {
            $this->pages = [
                'pages' => array_map(
                    'intval',
                    explode(',', $this->rawPages)
                )
            ];
        } else {
            $this->pages = ['pages' => [1]];
        }
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPages(): array
    {
        return $this->pages;
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function getUrlPDFFile(): string
    {
        return $this->urlPDFFile;
    }

    public function getFileNameWithExtension(): string
    {
        return $this->fileNameWithExtension;
    }
}
