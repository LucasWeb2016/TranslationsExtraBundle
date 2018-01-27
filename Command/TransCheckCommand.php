<?php

namespace Lucasweb\TranslationsExtraBundle\Command;

use Lucasweb\TranslationsExtraBundle\Utils\CommonUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\Reader\TranslationReaderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class TransCheckCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('trans:check')
            ->setDescription('Check translation catalogue files.')
            ->setHelp('Documentation available at https://github.com/LucasWeb2016/symfony-TranslationExtraBundle')
            ->addArgument('domain', InputArgument::REQUIRED, 'Translation domain name (Ej."messages"). Required.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('TRANS:CHECK => INFO : Starting Check Process ...');
        $filesystem = new Filesystem();
        $otherdata = [];
        $defaultdata = [];

        // Get Configurations and Files to process
        $common = new CommonUtils();
        $config = $common->getConfig($this->getContainer());
        $domainfiles = $common->getFiles($this->getContainer(), $input->getArgument('domain'));

        //Previous checks
        if (!in_array($input->getArgument('domain'), $config['domains'])) {
            $output->writeln('TRANS:CHECK => ERROR : "' . $input->getArgument('domain') . '" is not one of the configured domains.');
            die();
        } else if (!$filesystem->exists($config['main_folder'])) {
            $output->writeln('TRANS:CHECK => ERROR : Main folder not found. Bad configuration?');
            die();
        } else if (count($config['other_locales']) == 0) {
            $output->writeln('TRANS:CHECK => WARNING : No locales configured except default. Process will only work with default locale translations.');
        }

        if ($domainfiles['path'] == '') {
            $output->writeln('TRANS:CHECK => WARNING : Default locale file "' . $domainfiles['default'] . '" required and not found. Run "trans:create ' . $input->getArgument('domain') . '" command to help you solve it.');
        } else {
            $defaultdata = $common->getArrayFromFile($domainfiles['path'], $domainfiles['format']);
            $output->writeln('TRANS:CHECK => INFO : Default file ' . $domainfiles['default'] . ' has ' . count($defaultdata) . ' translation messages.');

            $output->writeln('TRANS:CHECK => INFO : Checking "' . $domainfiles['default'] . '" for repeated translations with different ID');
            $countrepeated = array_count_values($defaultdata);
            $count = 0;
            foreach ($countrepeated as $key => $value) {
                if ($value > 1) {
                    $count++;
                } else {
                    unset($countrepeated[$key]);
                }
            }
            if ($count >= 1) {
                $output->writeln('TRANS:CHECK => WARNING : Repeated translations found in "' . $domainfiles['default'] . '" : ');
                foreach ($countrepeated as $key => $value) {
                    $output->writeln('TRANS:CHECK => WARNING : Value "' . $key . '" appears ' . $value . ' times in "' . $domainfiles['default'] . '" : ');
                    $output->writeln('TRANS:CHECK => Ocurrences: ');
                    foreach ($defaultdata as $k => $v) {

                        if ($key == $v) {
                            $output->writeln('                  ID="' . $k . '" VALUE="' . $v . '"');
                        }
                    }
                }

            }

        }


        if (isset($domainfiles['others'])) {
            foreach ($domainfiles['others'] as $other) {

                if ($other['path'] == '') {
                    $output->writeln('TRANS:CHECK => WARNING : File "' . $other['filename'] . '" not found. Run "trans:create ' . $input->getArgument('domain') . '" command to solve it.');
                } else {
                    $otherdata = $common->getArrayFromFile($other['path'], $other['format']);
                    $output->writeln('TRANS:CHECK => INFO : File ' . $other['filename'] . ' has ' . count($otherdata) . ' translation messages.');
                }

                if (count($defaultdata) < count($otherdata)) {
                    $output->writeln('TRANS:CHECK => WARNING : File ' . $other['filename'] . ' has more translations than default. Run "trans:sync ' . $input->getArgument('domain') . '" command to solve it.');
                } else if (count($defaultdata) > count($otherdata)) {
                    $output->writeln('TRANS:CHECK => WARNING : File ' . $other['filename'] . ' has less translations than default. Run "trans:sync ' . $input->getArgument('domain') . '" command to solve it.');
                }

                $output->writeln('TRANS:CHECK => INFO : Checking "' . $other['filename'] . '" for repeated translations with different ID');
                $countrepeated = array_count_values($otherdata);
                $count = 0;
                foreach ($countrepeated as $key => $value) {
                    if ($value > 1) {
                        $count++;
                    } else {
                        unset($countrepeated[$key]);
                    }
                }
                if ($count >= 1) {
                    $output->writeln('TRANS:CHECK => WARNING : Repeated translations found in "' . $other['filename'] . '" : ');
                    foreach ($countrepeated as $key => $value) {
                        $output->writeln('TRANS:CHECK => WARNING : Value "' . $key . '" appears ' . $value . ' times in "' . $other['filename'] . '" : ');
                        $output->writeln('TRANS:CHECK => Ocurrences: ');
                        foreach ($otherdata as $k => $v) {

                            if ($key == $v) {
                                $output->writeln('                  ID="' . $k . '" VALUE="' . $v . '"');
                            }
                        }

                    }

                }
            }
        }

        $output->writeln('TRANS:CHECK => SUCCESS : Check files done!');
    }
}