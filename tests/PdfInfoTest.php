<?php

use NcJoes\PhpPdfSuite\Config;
use NcJoes\PhpPdfSuite\PdfInfo;

class PdfInfoTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        Config::set('poppler.bin_dir', realpath(dirname(__FILE__).'\..\vendor\bin\poppler'));
    }

    public function testGetInfo()
    {
        $file = realpath(dirname(__FILE__).'\source\test1.pdf');
        $pdf_info = new PdfInfo($file);

        //print_r($pdf_info->getInfo());
        $this->assertArrayHasKey('pages', $pdf_info->getInfo());
        $this->addToAssertionCount(sizeof($pdf_info->getInfo()));
    }

    public function testGetters()
    {
        $file = dirname(__FILE__).'\source\test1.pdf';
        $pdf_info = new PdfInfo($file);

        $info = [
            'Authors'           => $pdf_info->getAuthors(),
            'Creation Date'     => $pdf_info->getCreationDate(),
            'Creator'           => $pdf_info->getCreator(),
            'File Size'         => $pdf_info->getFileSize(),
            'Modification Date' => $pdf_info->getModificationDate(),
            'Num. of Pages'     => $pdf_info->getNumOfPages(),
            'Page Rot'          => $pdf_info->getPageRot(),
            'Page Size'         => $pdf_info->getPageSize(),
            'PDF Version'       => $pdf_info->getPdfVersion(),
            'Producer'          => $pdf_info->getProducer(),
            'Is Tagged?'        => (int)$pdf_info->isTagged(),
            'Is Optimized'      => (int)$pdf_info->isOptimized(),
            'Page Width'        => $pdf_info->getPageWidth(),
            'Page Height'       => $pdf_info->getPageHeight(),
            'Unit'              => $pdf_info->getSizeUnit()
        ];

        //print_r($info);
        $this->addToAssertionCount(sizeof($info));
    }
}