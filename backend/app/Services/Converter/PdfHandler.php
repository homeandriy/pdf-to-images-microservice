<?php

namespace App\Services\Converter;

use App\Models\Files;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Exceptions\InvalidFormat;
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf;

class PdfHandler
{
    private array $urls = [];
    private array $pages;
    private Pdf $pdfInstance;

    public function __construct(
        private readonly string $groupId,
        private readonly string $path,
        private readonly string $fileNameWithExtension,
        private readonly array $numberOfPages
    ) {
        $this->createInstance();
        $this->prepareNumberOfPages();
    }

    /**
     * @throws PdfDoesNotExist
     * @throws InvalidFormat
     */
    private function createInstance(): void
    {
        $this->pdfInstance = new Pdf(storage_path('app/' . $this->path . $this->fileNameWithExtension));
        $this->pdfInstance->setOutputFormat('jpg');
    }

    /**
     * @throws PageDoesNotExist
     */
    public function convert(): array
    {
        $this->convertToImage();

        return $this->getUrls();
    }

    /**
     * @throws PageDoesNotExist
     */
    public function convertToImage(): void
    {
        $fileNamePrefix = pathinfo($this->fileNameWithExtension, PATHINFO_FILENAME);
        foreach ($this->pages as $page) {
            $fullFileName = $fileNamePrefix . '_' . $page . '.jpg';
            $this
                ->pdfInstance
                ->setPage($page)
                ->saveImage(storage_path('app/' . $this->path . $fullFileName));
            $image = \Storage::get($this->path . $fullFileName);
            \Storage::disk('s3')->put($this->path . $fullFileName, $image);
            $imageUrl     = Storage::disk('s3')->url($this->path . $fullFileName);
            $this->urls[] = $imageUrl;
            $fileInstance = new Files(
                [
                    'group_id' => $this->groupId,
                    'path'     => $imageUrl,
                    'name'     => $this->path . $fullFileName,
                    'mime'     => Storage::mimeType($this->path . $fullFileName),
                    'size'     => Storage::size($this->path . $fullFileName),
                ]
            );
            $fileInstance->save();
        }
    }

    public function getUrls(): array
    {
        return $this->urls;
    }

    public function clear(): void
    {
        Storage::deleteDirectory($this->path);
    }

    /**
     * @throws PageDoesNotExist
     */
    private function prepareNumberOfPages(): void
    {
        if (isset($this->numberOfPages['pages'])) {
            $this->pages = $this->numberOfPages['pages'];
        }
        if (isset($this->numberOfPages['rand'])) {
            $maxPages = $this->pdfInstance->getNumberOfPages();
            if ($maxPages < $this->numberOfPages['rand']) {
                throw new PageDoesNotExist("The PDF has only {$maxPages} pages.");
            }
            $pages    = [1];
            do {
                $page = mt_rand(2, $maxPages);
                if ( ! in_array($page, $pages, true)) {
                    $pages[] = $page;
                }
            } while (count($pages) < $this->numberOfPages['rand']);
            $this->pages = $pages;
        }
    }
}
