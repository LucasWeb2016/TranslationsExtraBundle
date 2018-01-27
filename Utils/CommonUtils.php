<?php

namespace Lucasweb\TranslationsExtraBundle\Utils;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Yandex\Translate\Translator;
use Yandex\Translate\Exception;

class CommonUtils
{
    /*
     * File extensions allowed for every format
     */
    private $file_extensions = array(
        "xml" => ["xliff", "xlf", "xml"],
        "yaml" => ["yml", "yaml"],
        "php" => ["php"]
    );

    public function getFileExtensions()
    {
        return $this->file_extensions;
    }

    /*
     * get all config variables in an array
     */
    function getConfig($container)
    {
        $config = [];
        $config['default_format'] = $container->getParameter('translationsextra.default_format');
        $config['main_folder'] = $container->getParameter('translationsextra.main_folder');
        $config['default_locale'] = $container->getParameter('translationsextra.default_locale');
        $config['other_locales'] = $container->getParameter('translationsextra.other_locales');
        $config['domains'] = $container->getParameter('translationsextra.domains');
        $config['yandex_api_key'] = $container->getParameter('translationsextra.yandex_api_key');
        $config['fileextensions'] = $this->file_extensions;
        return $config;
    }

    /*
     * get all supossed to exist files (based on domains and locales configured) in any supported format/extension and check existence
     */
    function getFiles($container, $domain)
    {
        $filesystem = new Filesystem();
        $files = [];

        foreach ($this->file_extensions as $key => $value) {
            foreach($value as $extension) {
                if ($filesystem->exists($container->getParameter('translationsextra.main_folder') . '/' . $domain . '.' . $container->getParameter('translationsextra.default_locale') . '.' . $extension)) {
                    $files['default'] = $domain . '.' . $container->getParameter('translationsextra.default_locale') . '.' . $extension;
                    $files['path'] = $container->getParameter('translationsextra.main_folder') . '/' . $domain . '.' . $container->getParameter('translationsextra.default_locale') . '.' . $extension;
                    $files['others'] = [];
                    $files['locale'] = $container->getParameter('translationsextra.default_locale');
                    $files['format'] = $key;
                }
            }
        }

        if (!isset($files['default'])) {
            $files['default'] = $domain . '.' . $container->getParameter('translationsextra.default_locale') . '.' . $this->file_extensions[$container->getParameter('translationsextra.default_format')][0];
            $files['path'] = '';
            $files['others']=[];
            $files['locale']=$container->getParameter('translationsextra.default_locale');
            $files['format'] = '';
        }

        foreach ($container->getParameter('translationsextra.other_locales') as $locale) {
            $arraytemp = [];
            foreach ($this->file_extensions as $key => $value) {
                foreach($value as $extension) {
                    if ($filesystem->exists($container->getParameter('translationsextra.main_folder') . '/' . $domain . '.' . $locale . '.' . $extension)) {
                        $arraytemp['filename'] = $domain . '.' . $locale . '.' . $extension;
                        $arraytemp['path'] = $container->getParameter('translationsextra.main_folder') . '/' . $domain . '.' . $locale . '.' . $extension;
                        $arraytemp['locale'] = $locale;
                        $arraytemp['format'] = $key;
                        break;
                    }
                }
            }
            if (!isset($arraytemp['filename'])) {
                $arraytemp['filename'] = $domain . '.' . $locale . '.' . $this->file_extensions[$container->getParameter('translationsextra.default_format')][0];
                $arraytemp['path'] = '';
                $arraytemp['locale']=$locale;
                $arraytemp['format']='';
            }

            $files['others'][]=$arraytemp;
        }

        return $files;
    }

    /*
     * get array from file contents
     */
    function getArrayFromFile($filepath,$format){
        $result=[];
        switch ($format) {
            case 'xml':
                $xml = simplexml_load_file($filepath);
                $result = [];
                $count = 0;
                foreach ($xml->file->body[0] as $unit) {
                    $result[(string)$unit->source[0]]=(string)$unit->target[0];
                }
                ksort($result);
                break;
            case 'yaml':
                $result = Yaml::parse(file_get_contents($filepath));
                ksort($result);
                break;
            case 'php':
                $result=include $filepath;
                ksort($result);
        }
        return $result;
    }

    /*
     * save array in file
     */
    function putArrayInFile($filepath,$format,$data){
        ksort($data);
        switch ($format) {
            case 'xml':
                $result='<?xml version="1.0"?>
    <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
        <file source-language="%locale%" target-language="%locale%" datatype="plaintext" original="file.ext">
            <body>';
                $locale = explode("/",$filepath);
                $locale = explode(".",$locale[count($locale)-1]);
                $result = str_replace("%locale%", $locale[1] , $result);

                $template = '
                <trans-unit id="%key%">
                    <source>%key%</source>
                    <target>%value%</target>
                </trans-unit>';

                foreach($data as $key=>$value) {
                    $resulttemp = str_replace("%key%", $key, $template);
                    if($value != strip_tags($value)) {
                        $result .= str_replace("%value%", '<![CDATA['.$value.']]>', $resulttemp);
                    } else {
                        $result .= str_replace("%value%", $value, $resulttemp);
                    }

                }
                $result .= "
           </body>
        </file>
    </xliff>";
                file_put_contents($filepath, $result);
                break;
            case 'yaml':
                $result = Yaml::dump($data);
                file_put_contents($filepath, $result);
                break;
            case 'php':
                $template ="
    '%key%' => '%value%'";
                $result='<?php return array (';
                $i=0;
                foreach($data as $key=>$value) {
                    if($i==1){
                        $result.=',';
                    }
                    $resulttemp = str_replace("%key%", $key, $template);
                    $result.=str_replace("%value%", $value, $resulttemp);
                    $i=1;
                }
                $result.=');';
                file_put_contents($filepath, $result);
        }
    }

    /*
     * Get files to import from a Bundle folder
     */
    function GetImportFiles($folder,$domain,$config){
        $filesystem = new Filesystem();
        $files = [];
        $arraylocales[]=$config['default_locale'];
        $arraylocales=array_merge($arraylocales,$config['other_locales']);

        foreach($arraylocales as $locale) {
            foreach($this->file_extensions as $key => $value){

                foreach ($value as $extension) {

                    if ($filesystem->exists($folder . '/' . $domain . '.' . $locale . '.' . $extension)) {
                        $temp['filename'] = $domain . '.' . $locale . '.' . $extension;
                        $temp['path'] = $folder . '/' . $domain . '.' . $locale . '.' . $extension;
                        $temp['locale']=$locale;
                        $temp['format']=$key;
                        $files[]=$temp;
                    }
                }
            }
        }

        return $files;
    }

    function YandexTrans($message,$localeA,$localeB,$config,$output){
        try {
            $translator = new Translator($config['yandex_api_key']);
            $translation = $translator->translate($message, $localeA.'-'.$localeB);

            return (string)$translation;
        } catch (Exception $e) {
            $output->writeln('TRANS:YANDEX => ERROR : Error in translation, return empty!');
            return '';
        }
    }
}
