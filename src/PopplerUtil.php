<?php
/**
 * Php-PDF-Suite
 *
 * Author:  Chukwuemeka Nwobodo (jcnwobodo@gmail.com)
 * Date:    10/12/2016
 * Time:    11:59 AM
 **/

namespace NcJoes\PhpPdfSuite;

use NcJoes\PhpPdfSuite\Constants as C;
use NcJoes\PhpPdfSuite\Exceptions\FileNotFoundException;

abstract class PopplerUtil
{
    private   $binary_dir;
    private   $flags              = [];
    private   $options            = [];
    private   $source_pdf;
    private   $output_dir;
    protected $bin_file;
    private   $output_file_name;
    protected $output_file_extension;
    protected $require_output_dir = true;
    protected $require_file_name  = false;

    public function __construct($pdfFile = '', array $options = [])
    {
        if ($pdfFile != '') {
            $poppler_util = $this;
            if (!empty($options)) {
                array_walk($options, function ($value, $key) use ($poppler_util) {
                    $poppler_util->setOption($key, $value);
                });
            }

            return $this->open($pdfFile);
        }

        return $this;
    }

    public function open($pdfFile)
    {
        $real_path = realpath($pdfFile);
        if (is_file($real_path)) {
            $this->source_pdf = $real_path;

            return $this->outputDir(dirname($pdfFile));
        }
        throw new FileNotFoundException($pdfFile);
    }

    public function setOption($key, $value)
    {
        $util_options = $this->utilOptions();

        if (array_key_exists($key, $util_options) and $util_options[ $key ] == gettype($value))
            $this->options[ $key ] = $value;

        return $this;
    }

    public function unsetOption($key)
    {
        if ($this->hasOption($key))
            $this->options = array_except($this->options, $key);

        return $this;
    }

    public function setFlag($key)
    {
        $util_flags = $this->utilFlags();

        if (array_key_exists($key, $util_flags))
            $this->flags[ $key ] = $key;

        return $this;
    }

    public function unsetFlag($key)
    {
        if ($this->hasFlag($key))
            $this->flags = array_except($this->flags, $key);

        return $this;
    }

    public function getOption($key)
    {
        return $this->hasOption($key) ? $this->options[ $key ] : null;
    }

    public function getFlag($key)
    {
        return $this->hasFlag($key) ? $this->flags[ $key ] : null;
    }

    public function hasOption($key)
    {
        return array_key_exists($key, $this->options);
    }

    public function hasFlag($key)
    {
        return array_key_exists($key, $this->flags);
    }

    public function binDir($dir = '')
    {
        $real_path = realpath($dir);
        if (!empty($dir) and is_dir($real_path)) {
            $this->binary_dir = $real_path;
            Config::set('poppler.bin_dir', $real_path);

            return $this;
        }

        return Config::get('poppler.bin_dir', realpath(dirname(__FILE__).'\..\vendor\bin\poppler'));
    }

    public function outputDir($dir = '')
    {
        $real_path = realpath($dir);
        if (!empty($dir) and is_dir($real_path)) {
            $this->output_dir = $real_path;
            Config::set('poppler.output_dir', $real_path);

            return $this;
        }

        return Config::get('poppler.output_dir', realpath(dirname(__FILE__).'\..\tests\results'.uniqid('output-')));
    }

    public function outputFilename($name = '')
    {
        if (!empty($name) and is_string($name)) {
            $this->output_file_name = basename($name);
            $this->require_file_name = true;

            Config::set('poppler.output_name', $this->output_file_name);

            return $this;
        }

        elseif(empty($this->output_file_name) and $this->require_file_name) {
            $base = basename($this->source_pdf);
            $arr = explode('.', $base);
            $extension = $arr[sizeof($arr)-1];
            $default_name = str_replace($extension, '', $base) ?: '';

            return Config::get('poppler.output_name', $default_name);
        }
        else{
            return $this->output_file_name;
        }
    }

    public function outputFileExtension()
    {

    }
    protected function shellExec()
    {
        $command = $this->makeShellCommand();

        return shell_exec($command);
    }

    private function makeShellCommand()
    {
        $q = PHP_OS === 'WINNT' ? "\"" : "'";
        $options = $this->makePopplerOptions();

        $command[] = $q.$this->binDir().C::DS.$this->bin_file.$q;

        if ($options != ''){
            $command[] = $options;
        }

        $command[] = $q.$this->source_pdf.$q;

        if ($this->require_output_dir) {
            $output_path = $this->outputDir();

            if ($this->require_file_name) {
                $output_path .= C::DS.$this->outputFilename();
            }

            $command[] = $q.$output_path.$q;
        }


        return implode(' ', $command);
    }

    private function makePopplerOptions()
    {
        $generated = [];
        array_walk($this->options, function ($value, $key) use (&$generated) {
            $generated[] = $key.' '.$value;
        });

        array_walk($this->flags, function ($value) use (&$generated) {
            $generated[] = $value;
        });

        return implode(' ', $generated);
    }

    public function previewShellCommand()
    {
        return $this->makeShellCommand();
    }

    public function previewPopplerOptions()
    {
        return $this->makePopplerOptions();
    }

    public function clearOutputDirectory()
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->output_dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            $path = (string)$file;
            $basename = basename($path);
            if ($basename != '..' && $basename != ".gitignore") {
                if (is_file($path) && file_exists($path))
                    unlink($path);
                elseif (is_dir($path) && file_exists($path))
                    rmdir($path);
            }
        }

        return $this;
    }

    abstract public function utilOptions();

    abstract public function utilOptionRules();

    abstract public function utilFlags();

    abstract public function utilFlagRules();
}