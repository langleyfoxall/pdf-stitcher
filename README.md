# PDF Stitcher

The PDF Stitcher library allows you to easily stitch together multiple PDFs into a single file.

## Installation

To install the PDF Stitcher package, run the following Composer command.

```bash
composer require langleyfoxall/pdf-stitcher
```

Please note that this package requires Ghostscript (`gs`) to be installed
on your server. If you are running Ubuntu, this can be installed with the
following command.

```bash
sudo apt install ghostscript
```

## Usage

See the following basic usage example.

```php
(new PdfStitcher)
    ->addPdf('firstDocument.pdf')
    ->addPdfs(['secondDocument.pdf', 'yetAnotherDocument.pdf'])
    ->save('destinationDocument.pdf');
```

This will take in three input PDFs, stitch them together, and save out
the result to `destinationDocument.pdf`. The documents will be stitched
together in the order they are added.
