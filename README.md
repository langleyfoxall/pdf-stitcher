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

### Where Ghostscript is in an unusual location

A path to a Ghostscript executable can be passed into the `PdfStitcher` constructor:

```php
new PdfStitcher('a/path/to/a/gs/executable')
```

### Including only specific pages from a PDF

You may optionally give an array of page indices when adding a PDF:

```php
(new PdfStitcher)
    ->addPdf('firstDocument.pdf', [0, 2, 3, 5])
    ->save('destinationDocument.pdf');
```

Only the page numbers you list will be included.

The following will be rejected:

- Non-integer page numbers.
- Page numbers less than zero.
- Duplicate page numbers (e.g. 1, 3, 3, 5, 7).
- Misordered page numbers (e.g. 1, 5, 3, 7).
