<?php
namespace Barcodes;

//============================================================+
// File name   : barcodes.php
// @author DNS
use Imagick;
use ImagickDraw;
use ImagickPixel;

class DNS2DBarcode
{
  /**
   * Array representation of barcode.
   * @protected
   */
  protected $barcode_array = false;
  /**
   *path to save png in getBarcodePNGPath
   * @var <type>
   */
  public $save_path;

  /**
   * Return an array representations of barcode.
   * @return bool
   */
  public function getBarcodeArray()
  {
    return $this->barcode_array;
  }

  /**
   * <li>$arrcode['code'] code to be printed on text label</li>
   * <li>$arrcode['num_rows'] required number of rows</li>
   * <li>$arrcode['num_cols'] required number of columns</li>
   * <li>$arrcode['bcode'][$r][$c] value of the cell is $r row and $c column (0 = transparent, 1 = black)</li></ul>
   * @param $code (string) code to print
   * @param $type (string) type of barcode: <ul><li>DATAMATRIX : Datamatrix (ISO/IEC 16022)</li><li>PDF417 : PDF417 (ISO/IEC 15438:2006)</li><li>PDF417,a,e,t,s,f,o0,o1,o2,o3,o4,o5,o6 : PDF417 with parameters: a = aspect ratio (width/height); e = error correction level (0-8); t = total number of macro segments; s = macro segment index (0-99998); f = file ID; o0 = File Name (text); o1 = Segment Count (numeric); o2 = Time Stamp (numeric); o3 = Sender (text); o4 = Addressee (text); o5 = File Size (numeric); o6 = Checksum (numeric). NOTES: Parameters t, s and f are required for a Macro Control Block, all other parametrs are optional. To use a comma character ',' on text options, replace it with the character 255: "\xff".</li><li>QRCODE : QRcode Low error correction</li><li>QRCODE,L : QRcode Low error correction</li><li>QRCODE,M : QRcode Medium error correction</li><li>QRCODE,Q : QRcode Better error correction</li><li>QRCODE,H : QR-CODE Best error correction</li><li>RAW: raw mode - comma-separad list of array rows</li><li>RAW2: raw mode - array rows are surrounded by square parenthesis.</li><li>TEST : Test matrix</li></ul>
   * Send barcode as SVG image object to the standard output.
   * @param $w (int) Width of a single rectangle element in user units.
   * @param $h (int) Height of a single rectangle element in user units.
   * @param $color (string) Foreground color (in SVG format) for bar elements (background is transparent).
   * @public
   */
  public function getBarcodeSVG($code, $type, $w = 3, $h = 3, $color = 'black')
  {
    //set barcode code and type
    $this->setBarcode($code, $type);
    // send headers
    $code = $this->getBarcodeSVGcode($w, $h, $color);
    header('Content-Type: application/svg+xml');
    header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
    header('Pragma: public');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Content-Disposition: inline; filename="' . md5($code) . '.svg";');
    //header('Content-Length: '.strlen($code));
    echo $code;
  }

  /**
   * Return a SVG string representation of barcode.
   * <li>$arrcode['code'] code to be printed on text label</li>
   * <li>$arrcode['num_rows'] required number of rows</li>
   * <li>$arrcode['num_cols'] required number of columns</li>
   * <li>$arrcode['bcode'][$r][$c] value of the cell is $r row and $c column (0 = transparent, 1 = black)</li></ul>
   * @param $code (string) code to print
   * @param $type (string) type of barcode: <ul><li>DATAMATRIX : Datamatrix (ISO/IEC 16022)</li><li>PDF417 : PDF417 (ISO/IEC 15438:2006)</li><li>PDF417,a,e,t,s,f,o0,o1,o2,o3,o4,o5,o6 : PDF417 with parameters: a = aspect ratio (width/height); e = error correction level (0-8); t = total number of macro segments; s = macro segment index (0-99998); f = file ID; o0 = File Name (text); o1 = Segment Count (numeric); o2 = Time Stamp (numeric); o3 = Sender (text); o4 = Addressee (text); o5 = File Size (numeric); o6 = Checksum (numeric). NOTES: Parameters t, s and f are required for a Macro Control Block, all other parametrs are optional. To use a comma character ',' on text options, replace it with the character 255: "\xff".</li><li>QRCODE : QRcode Low error correction</li><li>QRCODE,L : QRcode Low error correction</li><li>QRCODE,M : QRcode Medium error correction</li><li>QRCODE,Q : QRcode Better error correction</li><li>QRCODE,H : QR-CODE Best error correction</li><li>RAW: raw mode - comma-separad list of array rows</li><li>RAW2: raw mode - array rows are surrounded by square parenthesis.</li><li>TEST : Test matrix</li></ul>
   * @param $w (int) Width of a single rectangle element in user units.
   * @param $h (int) Height of a single rectangle element in user units.
   * @param $color (string) Foreground color (in SVG format) for bar elements (background is transparent).
   * @return string SVG code.
   * @public
   */
  public function getBarcodeSVGcode($code, $type, $w = 3, $h = 3, $color = 'black')
  {
    //set barcode code and type
    $this->setBarcode($code, $type);
    // replace table for special characters
    $repstr = array("\0" => '', '&' => '&amp;', '<' => '&lt;', '>' => '&gt;');
    $svg = '<' . '?' . 'xml version="1.0" standalone="no"' . '?' . '>' . "\n";
    $svg .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' . "\n";
    $svg .= '<svg width="' . round(($this->barcode_array['num_cols'] * $w), 3) . '" height="' . round(
        ($this->barcode_array['num_rows'] * $h),
        3
      ) . '" version="1.1" xmlns="http://www.w3.org/2000/svg">' . "\n";
    $svg .= "\t" . '<desc>' . strtr($this->barcode_array['code'], $repstr) . '</desc>' . "\n";
    $svg .= "\t" . '<g id="elements" fill="' . $color . '" stroke="none">' . "\n";
    // print barcode elements
    $y = 0;
    // for each row
    for ($r = 0; $r < $this->barcode_array['num_rows']; ++$r) {
      $x = 0;
      // for each column
      for ($c = 0; $c < $this->barcode_array['num_cols']; ++$c) {
        if ($this->barcode_array['bcode'][$r][$c] == 1) {
          // draw a single barcode cell
          $svg .= "\t\t" . '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" />' . "\n";
        }
        $x += $w;
      }
      $y += $h;
    }
    $svg .= "\t" . '</g>' . "\n";
    $svg .= '</svg>' . "\n";
    return $svg;
  }

  /**
   * Return an HTML representation of barcode.
   * <li>$arrcode['code'] code to be printed on text label</li>
   * <li>$arrcode['num_rows'] required number of rows</li>
   * <li>$arrcode['num_cols'] required number of columns</li>
   * <li>$arrcode['bcode'][$r][$c] value of the cell is $r row and $c column (0 = transparent, 1 = black)</li></ul>
   * @param $code (string) code to print
   * @param $type (string) type of barcode: <ul><li>DATAMATRIX : Datamatrix (ISO/IEC 16022)</li><li>PDF417 : PDF417 (ISO/IEC 15438:2006)</li><li>PDF417,a,e,t,s,f,o0,o1,o2,o3,o4,o5,o6 : PDF417 with parameters: a = aspect ratio (width/height); e = error correction level (0-8); t = total number of macro segments; s = macro segment index (0-99998); f = file ID; o0 = File Name (text); o1 = Segment Count (numeric); o2 = Time Stamp (numeric); o3 = Sender (text); o4 = Addressee (text); o5 = File Size (numeric); o6 = Checksum (numeric). NOTES: Parameters t, s and f are required for a Macro Control Block, all other parametrs are optional. To use a comma character ',' on text options, replace it with the character 255: "\xff".</li><li>QRCODE : QRcode Low error correction</li><li>QRCODE,L : QRcode Low error correction</li><li>QRCODE,M : QRcode Medium error correction</li><li>QRCODE,Q : QRcode Better error correction</li><li>QRCODE,H : QR-CODE Best error correction</li><li>RAW: raw mode - comma-separad list of array rows</li><li>RAW2: raw mode - array rows are surrounded by square parenthesis.</li><li>TEST : Test matrix</li></ul>
   * @param $w (int) Width of a single rectangle element in pixels.
   * @param $h (int) Height of a single rectangle element in pixels.
   * @param $color (string) Foreground color for bar elements (background is transparent).
   * @return string HTML code.
   * @public
   */
  public function getBarcodeHTML($code, $type, $w = 10, $h = 10, $color = 'black')
  {
    //set barcode code and type
    $this->setBarcode($code, $type);
    $html = '<div style="font-size:0;position:relative;width:' . ($w * $this->barcode_array['num_cols']) . 'px;height:' . ($h * $this->barcode_array['num_rows']) . 'px;">' . "\n";
    // print barcode elements
    $y = 0;
    // for each row
    for ($r = 0; $r < $this->barcode_array['num_rows']; ++$r) {
      $x = 0;
      // for each column
      for ($c = 0; $c < $this->barcode_array['num_cols']; ++$c) {
        if ($this->barcode_array['bcode'][$r][$c] == 1) {
          // draw a single barcode cell
          $html .= '<div style="background-color:' . $color . ';width:' . $w . 'px;height:' . $h . 'px;position:absolute;left:' . $x . 'px;top:' . $y . 'px;">&nbsp;</div>' . "\n";
        }
        $x += $w;
      }
      $y += $h;
    }
    $html .= '</div>' . "\n";
    return $html;
  }

  /**
   * Return a PNG image representation of barcode (requires GD or Imagick library).
   * <li>$arrcode['code'] code to be printed on text label</li>
   * <li>$arrcode['num_rows'] required number of rows</li>
   * <li>$arrcode['num_cols'] required number of columns</li>
   * <li>$arrcode['bcode'][$r][$c] value of the cell is $r row and $c column (0 = transparent, 1 = black)</li></ul>
   * @param $code (string) code to print
   * @param $type (string) type of barcode: <ul><li>DATAMATRIX : Datamatrix (ISO/IEC 16022)</li><li>PDF417 : PDF417 (ISO/IEC 15438:2006)</li><li>PDF417,a,e,t,s,f,o0,o1,o2,o3,o4,o5,o6 : PDF417 with parameters: a = aspect ratio (width/height); e = error correction level (0-8); t = total number of macro segments; s = macro segment index (0-99998); f = file ID; o0 = File Name (text); o1 = Segment Count (numeric); o2 = Time Stamp (numeric); o3 = Sender (text); o4 = Addressee (text); o5 = File Size (numeric); o6 = Checksum (numeric). NOTES: Parameters t, s and f are required for a Macro Control Block, all other parametrs are optional. To use a comma character ',' on text options, replace it with the character 255: "\xff".</li><li>QRCODE : QRcode Low error correction</li><li>QRCODE,L : QRcode Low error correction</li><li>QRCODE,M : QRcode Medium error correction</li><li>QRCODE,Q : QRcode Better error correction</li><li>QRCODE,H : QR-CODE Best error correction</li><li>RAW: raw mode - comma-separad list of array rows</li><li>RAW2: raw mode - array rows are surrounded by square parenthesis.</li><li>TEST : Test matrix</li></ul>
   * @param int $w (int) Width of a single rectangle element in pixels.
   * @param int $h (int) Height of a single rectangle element in pixels.
   * @param array $color (array) RGB (0-255) foreground color for bar elements (background is transparent).
   * @return void or false in case of error.
   * @throws \ImagickException
   * @public
   */
  public function getBarcodePNG($code, $type, $w = 3, $h = 3, $color = array(0, 0, 0))
  {
    //set barcode code and type
    $this->setBarcode($code, $type);
    // calculate image size
    $width = ($this->barcode_array['num_cols'] * $w);
    $height = ($this->barcode_array['num_rows'] * $h);
    if (function_exists('imagecreate')) {
      // GD library
      $imagick = false;
      $png = imagecreate($width, $height);
      $bgcol = imagecolorallocate($png, 255, 255, 255);
      imagecolortransparent($png, $bgcol);
      $fgcol = imagecolorallocate($png, $color[0], $color[1], $color[2]);
    } elseif (extension_loaded('imagick')) {
      $imagick = true;
      $bgcol = new imagickpixel('rgb(255,255,255');
      $fgcol = new imagickpixel('rgb(' . $color[0] . ',' . $color[1] . ',' . $color[2] . ')');
      $png = new Imagick();
      $png->newImage($width, $height, 'none', 'png');
      $bar = new imagickdraw();
      $bar->setFillColor($fgcol);
    } else {
      return false;
    }
    // print barcode elements
    $y = 0;
    // for each row
    for ($r = 0; $r < $this->barcode_array['num_rows']; ++$r) {
      $x = 0;
      // for each column
      for ($c = 0; $c < $this->barcode_array['num_cols']; ++$c) {
        if ($this->barcode_array['bcode'][$r][$c] == 1) {
          // draw a single barcode cell
          if ($imagick) {
            $bar->rectangle($x, $y, ($x + $w), ($y + $h));
          } else {
            imagefilledrectangle($png, $x, $y, ($x + $w), ($y + $h), $fgcol);
          }
        }
        $x += $w;
      }
      $y += $h;
    }
    // send headers
    header('Content-Type: image/png');
    header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
    header('Pragma: public');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    if ($imagick) {
      $png->drawImage($bar);
      echo $png;
    } else {
      imagepng($png);
      imagedestroy($png);
    }
  }

  /**
   * Return a .png file path which create in server
   * <li>$arrcode['code'] code to be printed on text label</li>
   * <li>$arrcode['num_rows'] required number of rows</li>
   * <li>$arrcode['num_cols'] required number of columns</li>
   * <li>$arrcode['bcode'][$r][$c] value of the cell is $r row and $c column (0 = transparent, 1 = black)</li></ul>
   * @param $code (string) code to print
   * @param $type (string) type of barcode: <ul><li>DATAMATRIX : Datamatrix (ISO/IEC 16022)</li><li>PDF417 : PDF417 (ISO/IEC 15438:2006)</li><li>PDF417,a,e,t,s,f,o0,o1,o2,o3,o4,o5,o6 : PDF417 with parameters: a = aspect ratio (width/height); e = error correction level (0-8); t = total number of macro segments; s = macro segment index (0-99998); f = file ID; o0 = File Name (text); o1 = Segment Count (numeric); o2 = Time Stamp (numeric); o3 = Sender (text); o4 = Addressee (text); o5 = File Size (numeric); o6 = Checksum (numeric). NOTES: Parameters t, s and f are required for a Macro Control Block, all other parametrs are optional. To use a comma character ',' on text options, replace it with the character 255: "\xff".</li><li>QRCODE : QRcode Low error correction</li><li>QRCODE,L : QRcode Low error correction</li><li>QRCODE,M : QRcode Medium error correction</li><li>QRCODE,Q : QRcode Better error correction</li><li>QRCODE,H : QR-CODE Best error correction</li><li>RAW: raw mode - comma-separad list of array rows</li><li>RAW2: raw mode - array rows are surrounded by square parenthesis.</li><li>TEST : Test matrix</li></ul>
   * @param $w (int) Width of a single rectangle element in pixels.
   * @param $h (int) Height of a single rectangle element in pixels.
   * @param $color (array) RGB (0-255) foreground color for bar elements (background is transparent).
   * @return path of image whice created
   * @public
   */
  public function getBarcodePNGPath($code, $type, $w = 3, $h = 3, $color = array(0, 0, 0))
  {

    //set barcode code and type
    $this->setBarcode($code, $type);
    // calculate image size
    $width = ($this->barcode_array['num_cols'] * $w);
    $height = ($this->barcode_array['num_rows'] * $h);
    if (function_exists('imagecreate')) {
      // GD library
      $imagick = false;
      $png = imagecreate($width, $height);
      $bgcol = imagecolorallocate($png, 255, 255, 255);
      imagecolortransparent($png, $bgcol);
      $fgcol = imagecolorallocate($png, $color[0], $color[1], $color[2]);
    } elseif (extension_loaded('imagick')) {
      $imagick = true;
      $bgcol = new imagickpixel('rgb(255,255,255');
      $fgcol = new imagickpixel('rgb(' . $color[0] . ',' . $color[1] . ',' . $color[2] . ')');
      $png = new Imagick();
      $png->newImage($width, $height, 'none', 'png');
      $bar = new imagickdraw();
      $bar->setFillColor($fgcol);
    } else {
      return false;
    }
    // print barcode elements
    $y = 0;
    // for each row
    for ($r = 0; $r < $this->barcode_array['num_rows']; ++$r) {
      $x = 0;
      // for each column
      for ($c = 0; $c < $this->barcode_array['num_cols']; ++$c) {
        if ($this->barcode_array['bcode'][$r][$c] == 1) {
          // draw a single barcode cell
          if ($imagick) {
            $bar->rectangle($x, $y, ($x + $w), ($y + $h));
          } else {
            imagefilledrectangle($png, $x, $y, ($x + $w), ($y + $h), $fgcol);
          }
        }
        $x += $w;
      }
      $y += $h;
    }

    $save_file = $this->checkfile($this->save_path . ".png");

    if ($imagick) {
      $png->drawImage($bar);
      //echo $png;
    }
    if (imagepng($png, $save_file)) {
      imagedestroy($png);
      return $save_file;
    } else {
      imagedestroy($png);
      return $code;
    }
  }

  /**
   * Set the barcode.
   * @param $code (string) code to print
   * @param $type (string) type of barcode: <ul><li>DATAMATRIX : Datamatrix (ISO/IEC 16022)</li><li>PDF417 : PDF417 (ISO/IEC 15438:2006)</li><li>PDF417,a,e,t,s,f,o0,o1,o2,o3,o4,o5,o6 : PDF417 with parameters: a = aspect ratio (width/height); e = error correction level (0-8); t = total number of macro segments; s = macro segment index (0-99998); f = file ID; o0 = File Name (text); o1 = Segment Count (numeric); o2 = Time Stamp (numeric); o3 = Sender (text); o4 = Addressee (text); o5 = File Size (numeric); o6 = Checksum (numeric). NOTES: Parameters t, s and f are required for a Macro Control Block, all other parametrs are optional. To use a comma character ',' on text options, replace it with the character 255: "\xff".</li><li>QRCODE : QRcode Low error correction</li><li>QRCODE,L : QRcode Low error correction</li><li>QRCODE,M : QRcode Medium error correction</li><li>QRCODE,Q : QRcode Better error correction</li><li>QRCODE,H : QR-CODE Best error correction</li><li>RAW: raw mode - comma-separad list of array rows</li><li>RAW2: raw mode - array rows are surrounded by square parenthesis.</li><li>TEST : Test matrix</li></ul>
   * @return void
   */
  public function setBarcode($code, $type)
  {
    $mode = explode(',', $type);
    $qrtype = strtoupper($mode[0]);
    switch ($qrtype) {
      case 'DATAMATRIX':
        { // DATAMATRIX (ISO/IEC 16022)
          require_once(dirname(__FILE__) . '/Datamatrix.php');
          $qrcode = new Datamatrix($code);
          $this->barcode_array = $qrcode->getBarcodeArray();
          $this->barcode_array['code'] = $code;
          break;
        }
      case 'PDF417':
        { // PDF417 (ISO/IEC 15438:2006)
          require_once(dirname(__FILE__) . '/PDF417.php');
          if (!isset($mode[1]) or ($mode[1] === '')) {
            $aspectratio = 2; // default aspect ratio (width / height)
          } else {
            $aspectratio = floatval($mode[1]);
          }
          if (!isset($mode[2]) or ($mode[2] === '')) {
            $ecl = -1; // default error correction level (auto)
          } else {
            $ecl = intval($mode[2]);
          }
          // set macro block
          $macro = array();
          if (isset($mode[3]) and ($mode[3] !== '') and isset($mode[4]) and ($mode[4] !== '') and isset($mode[5]) and ($mode[5] !== '')) {
            $macro['segment_total'] = intval($mode[3]);
            $macro['segment_index'] = intval($mode[4]);
            $macro['file_id'] = strtr($mode[5], "\xff", ',');
            for ($i = 0; $i < 7; ++$i) {
              $o = $i + 6;
              if (isset($mode[$o]) and ($mode[$o] !== '')) {
                // add option
                $macro['option_' . $i] = strtr($mode[$o], "\xff", ',');
              }
            }
          }
          $qrcode = new PDF417($code, $ecl, $aspectratio, $macro);
          $this->barcode_array = $qrcode->getBarcodeArray();
          $this->barcode_array['code'] = $code;
          break;
        }
      case 'QRCODE':
        { // QR-CODE
          require_once(dirname(__FILE__) . '/QRcode.php');
          if (!isset($mode[1]) or (!in_array($mode[1], array('L', 'M', 'Q', 'H')))) {
            $mode[1] = 'L'; // Ddefault: Low error correction
          }
          $qrcode = new QRcode($code, strtoupper($mode[1]));
          $this->barcode_array = $qrcode->getBarcodeArray();
          $this->barcode_array['code'] = $code;
          break;
        }
      case 'RAW':
      case 'RAW2':
        { // RAW MODE
          // remove spaces
          $code = preg_replace('/[\s]*/si', '', $code);
          if (strlen($code) < 3) {
            break;
          }
          if ($qrtype == 'RAW') {
            // comma-separated rows
            $rows = explode(',', $code);
          } else { // RAW2
            // rows enclosed in square parentheses
            $code = substr($code, 1, -1);
            $rows = explode('][', $code);
          }
          $this->barcode_array['num_rows'] = count($rows);
          $this->barcode_array['num_cols'] = strlen($rows[0]);
          $this->barcode_array['bcode'] = array();
          foreach ($rows as $r) {
            $this->barcode_array['bcode'][] = str_split($r, 1);
          }
          $this->barcode_array['code'] = $code;
          break;
        }
      default:
        {
          $this->barcode_array = false;
        }
    }
  }

  public function checkfile($path)
  {
    if (file_exists($path)) {
      $base_name = pathinfo($path, PATHINFO_BASENAME);
      return $this->checkfile(str_replace($base_name, rand(0, 9999) . $base_name, $path));
    } else {
      return $path;
    }
  }
}

// end of class
//============================================================+
