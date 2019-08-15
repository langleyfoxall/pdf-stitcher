<?php

namespace LangleyFoxall\PdfStitcher;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class PdfStitcher
 * @package LangleyFoxall\PdfStitcher
 */
class PdfStitcher
{
    /**
     * Array of input PDF files to be stitched together.
     *
     * @var array
     */
    private $inputFiles = [];

    /**
     * Add a PDF to the list of files to be stitched together.
     *
     * @param string $filePath
     * @return PdfStitcher
     */
    public function addPdf(string $filePath): self
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new InvalidArgumentException('Specified file does not exist or can not be read: '.$filePath);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        if ($mimeType !== 'application/pdf') {
            throw new InvalidArgumentException('Specified file is not a PDF: '.$filePath);
        }

        $this->inputFiles[] = $filePath;

        return $this;
    }

    /**
     * Adds an array of PDFs to the list of files to be stitched together.
     *
     * @param array $filePaths
     * @return PdfStitcher
     */
    public function addPdfs(array $filePaths): self
    {
        foreach ($filePaths as $filePath) {
            $this->addPdf($filePath);
        }

        return $this;
    }

    /**
     * Save out the stitched together PDF.
     *
     * @param string $filePath
     */
    public function save(string $filePath): void
    {
        if (file_exists(dirname($filePath))) {
            throw new InvalidArgumentException('Specified file\'s directory does not exist: '.$filePath);
        }

        shell_exec($this->getShellCommand($filePath));
    }

    /**
     * Build and returns a Ghostscript command to stitch together the input PDFs
     * and save the result to specified output file path.
     *
     * @param $filePath
     * @return string
     */
    private function getShellCommand($filePath): string
    {
        if (!$this->ghostscriptInstalled()) {
            throw new RuntimeException('Ghostscript (`gs`) is not installed. Please install it.');
        }

        $command = 'gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile='.$filePath.' ';
        $command .= implode(' ', $this->inputFiles);

        return $command;
    }

    /**
     * Checks if the the Ghostscript (`gs`) command line tool is installed.
     *
     * @return bool
     */
    private function ghostscriptInstalled(): bool
    {
        return !empty(shell_exec('command -v gs'));
    }
}