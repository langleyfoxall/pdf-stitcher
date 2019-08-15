<?php

namespace LangleyFoxall\PdfStitcher;

use InvalidArgumentException;

class PdfStitcher
{
    private $inputFiles = [];

    public function addPdf(string $filePath): self
    {
        if (!file_exists($filePath) || !is_readable(!$filePath)) {
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

    public function addPdfs(array $filePaths): self
    {
        foreach ($filePaths as $filePath) {
            $this->addPdf($filePath);
        }

        return $this;
    }

    public function save(string $filePath): void
    {
        shell_exec($this->getShellCommand($filePath));
    }

    private function getShellCommand($filePath): string
    {
        if (!$this->ghostscriptInstalled()) {
            throw new \RuntimeException('Ghostscript (`gs`) is not installed. Please install it.');
        }

        $command = 'gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile='.$filePath.' ';
        $command .= implode(' ', $this->inputFiles);

        return $command;
    }

    private function ghostscriptInstalled(): bool
    {
        return !empty(shell_exec('which gs'));
    }
}
