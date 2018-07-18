<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Cli\Command;

use SensioLabs\Insight\Cli\Helper\DescriptorHelper;
use SensioLabs\Insight\Sdk\Api;
use SensioLabs\Insight\Sdk\Model\Analysis;
use SensioLabs\Insight\Sdk\Model\PreviousAnalysesReferences;
use SensioLabs\Insight\Sdk\Model\Violation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DiffCommand extends Command implements NeedConfigurationInterface
{
    /**
     * @var String
     */
    protected $projectUuid;

    /**
     * @var Api
     */
    protected $api;

    /**
     * @var Analysis
     */
    protected $lastAnalysis;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var String
     */
    protected $format;

    protected function configure()
    {
        $this
            ->setName('diff')
            ->addArgument('project-uuid', InputArgument::REQUIRED)
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'To output in other formats', 'txt')
            ->addOption('reference-base', null, InputOption::VALUE_REQUIRED, 'The git reference of base analyze')
            ->addOption('reference-head', null, InputOption::VALUE_REQUIRED, 'The git reference of head analyze')
            ->addOption('show-ignored-violations', null, InputOption::VALUE_NONE, 'Show ignored violations')
            ->addOption('fail-condition', null, InputOption::VALUE_REQUIRED, '')
            ->setDescription('Get diff of two analyze')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->projectUuid  = $input->getArgument('project-uuid');
        $this->format       = $input->getOption('format');
        $this->output       = $output;
        $this->api          = $this->getApplication()->getApi();
        $this->lastAnalysis = $this->api->getProject( $this->projectUuid)->getLastAnalysis();

        $analysisHead = $this->getAnalysisByReference($input->getOption('reference-head'));
        if ($analysisHead instanceof Analysis === false) {
            $this->output->writeln('<error>There are no analyses for '.$input->getOption('reference-head').' reference</error>');
            return 1;
        }

        $analysisBase = $this->getAnalysisByReference($input->getOption('reference-base'));
        if ($analysisBase instanceof Analysis === false) {
            $this->output->writeln('<error>There are no analyses for '.$input->getOption('reference-base').' reference</error>');
            return 1;
        }

        $ref = [];
        foreach ($analysisBase->getViolations() as $violation) {
            $ref[] = $violation->getMd5();
        }

        $analysisHead->getViolations()->filter(function (Violation $violation) use ($ref) {
            return in_array($violation->getMd5(),$ref) === false;
        });
        $analysisHead->getViolations()->sort();


        $helper = new DescriptorHelper( $this->api->getSerializer());
        $helper->describe($this->output, $analysisHead, $this->format, $input->getOption('show-ignored-violations'));

        if ('txt' === $this->format && OutputInterface::VERBOSITY_VERBOSE > $this->output->getVerbosity()) {
            $this->output->writeln('');
            $this->output->writeln('Re-run this command with <comment>-v</comment> option to get the full report');
        }

        if (!$expr = $input->getOption('fail-condition')) {
            return;
        }

        return $this->getHelperSet()->get('fail_condition')->evaluate($analysisHead, $expr);
    }

    /**
     * @param String $reference
     * @return Analysis
     */
    private function getAnalysisByReference($reference)
    {
        /** @var PreviousAnalysesReferences $data */
        $data =  $this->lastAnalysis->getPreviousAnalysesReferences();

        $analysisNumber = $data->findAnalysisNumberByReference($reference);
        if ($analysisNumber === null) {
            return $this->startAnalyse($reference);
        }
        return $this->api->getAnalysis( $this->projectUuid,$analysisNumber);
    }

    /**
     * @param $reference
     * @return bool|Analysis
     */
    protected function startAnalyse($reference)
    {
        $analysis =  $this->api->analyze($this->projectUuid, $reference);

        $chars = array('-', '\\', '|', '/');
        $noAnsiStatus = 'Analysis queued';
        $this->output->getErrorOutput()->writeln($noAnsiStatus);

        $position = 0;

        while (true) {
            // we don't check the status too often
            if (0 === $position % 2) {
                $analysis =  $this->api->getAnalysisStatus($this->projectUuid, $analysis->getNumber());
            }

            if ('txt' === $this->format) {
                if (!$this->output->isDecorated()) {
                    if ($noAnsiStatus !== $analysis->getStatusMessage()) {
                        $this->output->writeln($noAnsiStatus = $analysis->getStatusMessage());
                    }
                } else {
                    $this->output->write(sprintf("%s %-80s\r", $chars[$position % 4], $analysis->getStatusMessage()));
                }
            }

            if ($analysis->isFinished()) {
                break;
            }

            usleep(200000);

            ++$position;
        }

        $analysis =  $this->api->getAnalysis($this->projectUuid, $analysis->getNumber());
        if ($analysis->isFailed()) {
            $this->output->writeln(sprintf('There was an error: "%s"', $analysis->getFailureMessage()));
            return false;
        }
        return $analysis;
    }
}
