<?php

namespace Lucasweb\TranslationsExtraBundle\Command;

use Lucasweb\TranslationsExtraBundle\Utils\CommonUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransEditCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('trans:edit')
            ->setDescription('Edit translations messages from your catalogue files.')
            ->setHelp('Documentation available at https://github.com/LucasWeb2016/TranslationsExtraBundle')
            ->addArgument('id', InputArgument::REQUIRED, 'Translation Message ID (Ej."label.home").')
            ->addArgument('domain', InputArgument::REQUIRED, 'Translation domain name (Ej."messages"). Required.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('TRANS:EDIT => INFO : Starting Edit Process ...');
        $helper = $this->getHelper('question');
        QuestionHelper::disableStty();
        $filesystem = new Filesystem();

        // Get Configurations and Files to process
        $common = new CommonUtils();
        $config = $common->getConfig($this->getContainer());
        $domainfiles = $common->getFiles($this->getContainer(), $input->getArgument('domain'));

        //Previous checks
        if (!in_array($input->getArgument('domain'), $config['domains'])) {
            $output->writeln('TRANS:EDIT => ERROR : "' . $input->getArgument('domain') . '" is not one of the configured domains.');
            die();
        } else if (!$filesystem->exists($config['main_folder'])) {
            $output->writeln('TRANS:EDIT => ERROR : Main folder not found. Bad configuration?');
            die();
        } else if (count($config['other_locales']) == 0) {
            $output->writeln('TRANS:EDIT => WARNING : No locales configured except default. Process will only work with default locale translations.');
        }

        // Process
        if ($domainfiles['path'] == '') {
            $output->writeln('TRANS:EDIT => ERROR : Default locale file "' . $domainfiles['default'] . '" not found. Run "trans:create ' . $input->getArgument('domain') . '" command to solve it. Process will continue without working in this file.');
        } else {
            $defaultdata = $common->getArrayFromFile($domainfiles['path'], $domainfiles['format']);
            if (!is_array($defaultdata)) {
                $output->writeln('TRANS:EDIT => WARNING : Default locale file "' . $domainfiles['default'] . '" cant´t be opened. Incorrect format?. Process will continue without working in this file.');
            } else {
                if (!isset($defaultdata[$input->getArgument('id')])) {
                    $output->writeln('TRANS:EDIT => INFO : ID="' . $input->getArgument('id') . '" not found in file "' . $domainfiles['default'] . '".');
                } else {
                    $output->writeln('TRANS:EDIT => INFO : ID="' . $input->getArgument('id') . '" found in file "' . $domainfiles['default'] . '".');
                    $a = 0;
                    $replytarget = 0;
                    while ($a <= 0) {
                        $question = new Question('TRANS:EDIT => QUESTION : New value for ID=' . $input->getArgument('id') . ' in default file ' . $domainfiles['default'] . ' (Press enter for current : "' . $defaultdata[$input->getArgument('id')] . '") : ', $defaultdata[$input->getArgument('id')]);
                        $replytarget = $helper->ask($input, $output, $question);
                        if (!$replytarget == '' && is_string($replytarget)) {
                            $a = 1;
                        }
                    }
                    $replytarget=$common->Sanitize($replytarget);
                    $defaultdata[$input->getArgument('id')] = $replytarget;
                    $common->putArrayInFile($domainfiles['path'], $domainfiles['format'], $defaultdata);
                }
            }
        }
        if (isset($domainfiles['others'])) {
            foreach ($domainfiles['others'] as $other) {
                if ($other['path'] == '') {
                    $output->writeln('TRANS:EDIT => WARNING : File "' . $other['filename'] . '" not found. Run "trans:create ' . $input->getArgument('domain') . '" command to solve it. Process will continue without working in this file.');
                } else {
                    $otherdata = $common->getArrayFromFile($other['path'], $other['format']);
                    if (!is_array($otherdata)) {
                        $output->writeln('TRANS:EDIT => WARNING : File "' . $other['filename'] . '" cant´t be opened. Incorrect format?. Process will continue without working in this file.');
                    } else {
                        if (!isset($otherdata[$input->getArgument('id')])) {
                            $output->writeln('TRANS:EDIT => INFO : ID="' . $input->getArgument('id') . '" not found in file "' . $other['filename'] . '".');
                        } else {
                            $output->writeln('TRANS:EDIT => INFO : ID="' . $input->getArgument('id') . '" found in file "' . $other['filename'] . '".');
                            $a = 0;
                            $replytarget = 0;
                            while ($a <= 0) {
                                if ($config['yandex_api_key'] != '' && isset($defaultdata)) {
                                    $trans = $common->YandexTrans($defaultdata[$input->getArgument('id')], $domainfiles['locale'], $other['locale'], $config, $output);
                                    if ($trans == '') {
                                        $yandex = '';
                                    } else {
                                        $yandex = ' (Yandex Translation: ' . $trans . ' )';
                                    }
                                } else {
                                    $yandex = '';
                                }
                                $question = new Question('TRANS:EDIT => QUESTION : New value for ID=' . $input->getArgument('id') . ' in file ' . $other['filename'] . ' (Press enter for current : "' . $otherdata[$input->getArgument('id')] . '")' . $yandex . ' : ', $otherdata[$input->getArgument('id')]);
                                $replytarget = $helper->ask($input, $output, $question);
                                if (!$replytarget == '' && is_string($replytarget)) {
                                    $a = 1;
                                }
                            }
                            $replytarget=$common->Sanitize($replytarget);
                            $otherdata[$input->getArgument('id')] = $replytarget;
                            $common->putArrayInFile($other['path'], $other['format'], $otherdata);
                        }
                    }
                }
            }
        }
        $output->writeln('TRANS:EDIT => SUCCESS : Translation message edited and saved!');
    }


}