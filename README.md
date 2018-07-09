php-barcodes
============

This is a library to generate php barcodes. It is compatible with HL7 Barcode messages.


# Example

```php
$d = new DNS2DBarcode;
$d->save_path = '/temp/test'; //outputs temp/test.png
$path = $d2->getBarcodePNGPath('HL7 message', 'pdf417,1', 6, 3);
```



2D-3D-Barcodes-Generator
========================

Generator ,QrCode , PDF417,C39, C39+,C39E,C39E+,C93,S25,S25+,I25,I25+,C128,C128A,C128B,C128C,2-Digits UPC-Based Extention,5-Digits UPC-Based Extention,EAN 8,EAN 13,UPC-A,UPC-E,MSI (Variation of Plessey code) also use for Codeigniter Barcode Generator

