<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

/******************************************************************************/
/* Configuración                                                              */
/******************************************************************************/

// errores
ini_set('display_errors', true);
error_reporting(E_ALL | E_STRICT | E_DEPRECATED);

// autenticación: si ambos son vacíos no se usa autenticación
$auth_user = '';
$auth_pass = '';

// autocarga composer
require('vendor/autoload.php');

/******************************************************************************/
/* Declaración de funciones                                                   */
/******************************************************************************/

// función que verifica las credenciales
function auth_check($user, $pass)
{
    if (!$user && !$pass) {
        return true;
    }
    $headers = apache_request_headers();
    if (empty($headers['Authorization'])) {
        return false;
    }
    list($basic, $Authorization) = explode(' ', $headers['Authorization']);
    list($u, $p) = explode(':', base64_decode($Authorization));
    return $u == $user && $p == $pass;
}

/******************************************************************************/
/* Declaración de clases                                            */
/******************************************************************************/

// clase base para la generación del PDF (no modificar, si se desea cambiar algo
// hacerlo en la clase PDF sobreescribiendo métodos de esta clase)
// Si no quiere usar TCPDF, considere escribir por completo la clase PDF, sin
// extender ni usar esta clase
abstract class PDF_Base extends \TCPDF {

    protected $options;

    public function setOptions($options)
    {
        $this->options = array_merge([
            'cedible' => 1,
            'papelContinuo' => 0,
            'compress' => false,
            'webVerificacion' => 'www.sii.cl',
            'copias_tributarias' => 1,
            'copias_cedibles' => 1,
        ], $options);
    }

    public function download($file)
    {
        $this->Output($file, 'I');
    }

    public function Header()
    {
    }

    public function Footer()
    {
    }

    protected function num($n)
    {
        return number_format($n, 0, ',', '.');
    }

    protected function rut($r)
    {
        list($rut, $dv) = explode('-', $r);
        return $this->num($rut).'-'.$dv;
    }

    protected function date($fecha)
    {
        list($Y, $m, $d) = explode('-', $fecha);
        return $d.'/'.$m.'/'.$Y;
    }

    protected function getTED(\DomDocument $dom)
    {
        $TED = $dom->getElementsByTagName('TED')->item(0);
        if (!$TED) {
            return '<TED/>'; // permite funcionar con cotizaciones de LibreDTE
        }
        $ted_dom = new DomDocument;
        $ted_dom->appendChild($ted_dom->importNode($TED, true));
        $ted_dom->documentElement->removeAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi');
        $ted_dom->documentElement->removeAttributeNS('http://www.sii.cl/SiiDte', '');
        $xml = $ted_dom->C14N();
        $xml = preg_replace("/\>\n\s+\</", '><', $xml);
        $xml = preg_replace("/\>\n\t+\</", '><', $xml);
        $xml = preg_replace("/\>\n+\</", '><', $xml);
        $xml = trim($xml);
        return mb_detect_encoding($xml, ['UTF-8', 'ISO-8859-1']) != 'ISO-8859-1' ? utf8_decode($xml) : $xml;
    }

}

// clase que genera el PDF, extiende la base, contiene sólo el método agregar
// que es el método que agrega el DTE al PDF. Este método agregar es el que debe ser modificado.
// Este es SÓLO un ejemplo, DEBE ser modificado
class PDF extends PDF_Base {

    protected $documentos = [
        33 => 'Factura electrónica',
    ];

    public function agregar(\DomDocument $dom)
    {
        $this->AddPage();
        // logo de la empresa
        $h_img = 20;
        $this->Image('img/logo.png', $this->getX(), $this->getY(), null, $h_img);
        $this->setY($this->y + $h_img + 5);
        // datos emisor
        $this->Cell(0, 0, $dom->getElementsByTagName('RznSoc')->item(0)->textContent);
        $this->Ln();
        $this->Cell(0, 0, 'RUT: '.$this->rut($dom->getElementsByTagName('RUTEmisor')->item(0)->textContent));
        $this->Ln();
        $this->Cell(0, 0, 'Giro: '.$dom->getElementsByTagName('GiroEmis')->item(0)->textContent);
        $this->Ln();
        $this->Cell(0, 0, 'Dirección: '.$dom->getElementsByTagName('DirOrigen')->item(0)->textContent.', '.$dom->getElementsByTagName('CmnaOrigen')->item(0)->textContent);
        $this->Ln();
        $this->Ln();
        // datos DTE
        $this->Cell(0, 0, $this->documentos[$dom->getElementsByTagName('TipoDTE')->item(0)->textContent].' N° '.$dom->getElementsByTagName('Folio')->item(0)->textContent);
        $this->Ln();
        $this->Cell(0, 0, 'Fecha emisión: '.$this->date($dom->getElementsByTagName('FchEmis')->item(0)->textContent));
        $this->Ln();
        $this->Ln();
        // datos receptor
        $this->Cell(0, 0, 'Receptor: '.$dom->getElementsByTagName('RznSocRecep')->item(0)->textContent);
        $this->Ln();
        $this->Cell(0, 0, 'RUT: '.$this->rut($dom->getElementsByTagName('RUTRecep')->item(0)->textContent));
        $this->Ln();
        $this->Cell(0, 0, 'Giro: '.$dom->getElementsByTagName('GiroRecep')->item(0)->textContent);
        $this->Ln();
        $this->Cell(0, 0, 'Dirección: '.$dom->getElementsByTagName('DirRecep')->item(0)->textContent.', '.$dom->getElementsByTagName('CmnaRecep')->item(0)->textContent);
        $this->Ln();
        $this->Ln();
        // detalle
        $Detalle = $dom->getElementsByTagName('Detalle');
        foreach ($Detalle as $d) {
            $item = '';
            $item .= $this->num($d->getElementsByTagName('QtyItem')->item(0)->textContent).' x ';
            $item .= $d->getElementsByTagName('NmbItem')->item(0)->textContent;
            $item .= ' ('.$this->num($d->getElementsByTagName('PrcItem')->item(0)->textContent).' c/u)';
            $item .= ': $'.$this->num($d->getElementsByTagName('MontoItem')->item(0)->textContent);
            $this->Cell(0, 0, $item);
            $this->Ln();
        }
        $this->Ln();
        // totales
        $this->Cell(0, 0, 'Neto: $'.$this->num($dom->getElementsByTagName('MntNeto')->item(0)->textContent));
        $this->Ln();
        $this->Cell(0, 0, 'IVA: $'.$this->num($dom->getElementsByTagName('IVA')->item(0)->textContent));
        $this->Ln();
        $this->Cell(0, 0, 'Total: $'.$this->num($dom->getElementsByTagName('MntTotal')->item(0)->textContent));
        $this->Ln();
        $this->Ln();
        // timbre
        $ted = $this->getTED($dom);
        $style = [
            'border' => false,
            'padding' => 0,
            'hpadding' => 0,
            'vpadding' => 0,
            'module_width' => 1, // width of a single module in points
            'module_height' => 1, // height of a single module in points
            'fgcolor' => [0,0,0],
            'bgcolor' => false, // [255,255,255]
            'position' => 'S',
        ];
        $ecl = version_compare(phpversion(), '7.0.0', '<') ? -1 : 5;
        $this->write2DBarcode($ted, 'PDF417,,'.$ecl, $this->getX(), $this->getY(), 70, 0, $style, 'B');
        $this->Ln();
        $this->setFont('', 'B', 8);
        $this->Cell(0, 0, 'Timbre Electrónico SII');
        $this->Ln();
        $this->Cell(0, 0, 'Resolución '.$dom->getElementsByTagName('NroResol')->item(0)->textContent.' de '.explode('-', $dom->getElementsByTagName('FchResol')->item(0)->textContent)[0]);
        $this->Ln();
        $this->Cell(0, 0, 'Verifique documento: www.sii.cl');
        $this->Ln();
        $this->Ln();
        // imagen con pie de página de la factura
        $this->Image('img/footer.png', $this->getX(), $this->getY(), 190, 'PNG');
    }

}

/******************************************************************************/
/* Programa principal (main)                                                  */
/******************************************************************************/

// verificar si el usuario corresponde
if (!auth_check($auth_user, $auth_pass)) {
    die('Usuario no autorizado');
}

// extraer datos y crear crear DomDocument
$datos = json_decode(file_get_contents('php://input'), true);
$xml = base64_decode($datos['xml']);
unset($datos['xml']);
$dom = new \DomDocument();
$dom->loadXML($xml);

// crear PDF
$pdf = new PDF();
$pdf->setOptions($datos);
$pdf->agregar($dom);
$pdf->download('dte.pdf');
