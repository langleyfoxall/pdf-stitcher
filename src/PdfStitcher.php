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
     * Array of arguments to be given to "gs".
     *
     * @var array
     */
    private $arguments = [];

    /**
     * A path to a Ghostscript executable to use instead of the default "gs".
     * 
     * @var ?string
     */
    private $overriddenGhostscriptExecutablePath = null;

    private $extraGhostscriptArguments = null;

    /**
     * Creates a new instance of the PDF stitcher.
     * 
     * @param ?string $overriddenGhostscriptExecutablePath A path to a Ghostscript executable to use instead of the default "gs".
     * @param ?string $extraGhostscriptArguments Extra Ghostscript arguments to be included in any commands executed.  No escaping will be performed.
     */
    public function __construct(
        $overriddenGhostscriptExecutablePath = null,
        $extraGhostscriptArguments = null
    )
    {
        $this->overriddenGhostscriptExecutablePath = $overriddenGhostscriptExecutablePath;
        $this->extraGhostscriptArguments = $extraGhostscriptArguments;
    }

    /**
     * Add a PDF to the list of files to be stitched together.
     *
     * @param string $filePath
     * @param ?array $pageIndices
     * @return PdfStitcher
     */
    public function addPdf(string $filePath, ?array $pageIndices = null): self
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

        if ($pageIndices !== null) {
            if (count($pageIndices) > 0) {
                $previous = -1;

                foreach ($pageIndices as $pageIndex) {
                    if (! ctype_digit(strval($pageIndex)) ) {
                        throw new InvalidArgumentException('Invalid page index "'.$pageIndex.'".');
                    }

                    if ($pageIndex === $previous) {
                        throw new InvalidArgumentException('Duplicate page index "'.$pageIndex.'".');
                    }

                    if ($pageIndex < $previous) {
                        throw new InvalidArgumentException('Misordered page index "'.$pageIndex.'".');
                    }

                    $previous = $pageIndex;
                }

                $this->arguments[] = '-sPageList='.implode(',', array_map(fn (int $pageIndex) => $pageIndex + 1, $pageIndices));
                $this->arguments[] = Utils::quote($filePath);
            }
        } else {
            $this->arguments[] = '-sPageList=1-';
            $this->arguments[] = Utils::quote($filePath);
        }

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
        if (!file_exists(dirname($filePath))) {
            throw new InvalidArgumentException('Specified file\'s directory does not exist: '.$filePath);
        }

        $this->runShellCommand($this->getShellCommand($filePath));
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
        if ($this->overriddenGhostscriptExecutablePath === null) {
            if (!$this->ghostscriptInstalled()) {
                throw new RuntimeException('Ghostscript (`gs`) is not installed. Please install it.');
            }

            $command = 'gs';
        } else {
            $command = $this->overriddenGhostscriptExecutablePath;
        }

        if ($this->extraGhostscriptArguments !== null) {
            $command .= ' '.$this->extraGhostscriptArguments;
        }

        $command .= ' -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile='.Utils::quote($filePath).' ';
        $command .= implode(' ', $this->arguments);

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

    private function runShellCommand(string $command): void
    {
      $process = proc_open(
        $command,
        [
          ['pipe', 'r'],
          ['pipe', 'w'],
          ['pipe', 'w'],
        ],
        $pipes
      );
  
      if ($process === false) {
        throw new RuntimeException('Failed to open a process to stitch PDFs.');
      }
  
      // STDIN.
      fclose($pipes[0]);
  
      $stdout = stream_get_contents($pipes[1]);
      fclose($pipes[1]);
  
      $stderr = stream_get_contents($pipes[2]);
      fclose($pipes[2]);
  
      $exitCode = proc_close($process);
  
      if ($exitCode !== 0 || $stderr !== '') {
        throw new RuntimeException('Failed to run shell command "'.$command.'"; exit code '.$exitCode.', stdout "'.$stdout.'", stderr "'.$stderr.'".');
      }
    }
}
