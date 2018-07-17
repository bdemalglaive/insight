<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Cli\Descriptor;

use SensioLabs\Insight\Sdk\Model\Analysis;
use SensioLabs\Insight\Sdk\Model\Violation;
use Symfony\Component\Console\Output\OutputInterface;

class MdDescriptor extends AbstractDescriptor
{
    protected function describeAnalysis(Analysis $analysis, array $options = array())
    {
        $output = $options['output'];
        if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $output->getVerbosity()) {
            $output->write(sprintf('Began at: *%s*', $analysis->getBeginAt()->format('Y-m-d h:i:s')));
        }
        if (!$analysis->isFinished()) {
            if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $output->getVerbosity()) {
                $output->writeln('');
            }
            $output->writeln('The analysis is not finished yet.');

            return;
        }
        if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $output->getVerbosity()) {
            $output->write(sprintf(' Ended at: *%s*', $analysis->getEndAt()->format('Y-m-d h:i:s')));
            $output->writeln(sprintf(' Real duration: *%s*.', $analysis->getEndAt()->format('Y-m-d h:i:s')));
            $output->writeln('');
        }
        $output->write(sprintf(
            'The project has **%d violations**, it got the *%s grade*.   ',
            $analysis->getNbViolations(),
            $analysis->getGrade()
        ));

        $grades = $analysis->getGrades();
        $bestGrade = end($grades);
        if ($bestGrade == $analysis->getGrade()) {
            $output->writeln('');

            return;
        }

        $output->writeln(sprintf(
            ' **%d hours** to get the **%s grade** and %d hours to get the %s grade   ',
            $analysis->getRemediationCostForNextGrade(),
            $analysis->getNextGrade(),
            $analysis->getRemediationCost(),
            $bestGrade
        ));
        $output->writeln('   ');

        if ($analysis->getViolations()) {

            $template = <<<EOL
    <tdody>
        <tr>
            <th>Severity</th><td>{{ severity }}</td>
            <th>Category</th><td>{{ category }}</td>
        </tr>
        <tr>
            <th>Title & Message</th><td colspan="3">{{ title }}</td>
        </tr>
        <tr>
            <th>Message</th><td colspan="3"> {{ message }}</td>
        </tr>
        <tr>
            <th>Resource</th><td colspan="3"><ul><li>{{ resources }}</li></ul></td>
        </tr>
        <tr><td colspan="4"> </td></tr>
    </tdody>
EOL;
            $ref = [];
            foreach ($analysis->getViolations() as $violation) {
                /** @var $violation Violation */
                $key = md5($violation->getTitle().$violation->getMessage());
                if (array_key_exists($key,$ref) === false) {
                    $severity = ucfirst($violation->getSeverity());
                    switch ($violation->getSeverity()) {
                        case Violation::SEVERITY_CRITICAL:
                            $severity = ' :boom: '.$severity;
                            break;
                        case Violation::SEVERITY_MAJOR:
                            $severity = ' :exclamation: '.$severity;
                            break;
                        case Violation::SEVERITY_MINOR:
                            $severity = ' :grey_exclamation: '.$severity;
                            break;
                    }

                    $ref[$key] = [
                        'severity'  => $severity,
                        'category'  => ucfirst($violation->getCategory()),
                        'title'     => $violation->getTitle(),
                        'message'   => $violation->getMessage(),
                        'resources' => []
                    ];
                }
                $ref[$key]['resources'][] = $violation->getResource().':'.$violation->getLine();
            }

            $output->writeln(sprintf(
                ' We find **%s new violation** in this pull request.   ',
                count($analysis->getViolations())
            ));
            $output->writeln('   ');

            $output->writeln("<table>");
            foreach ($ref as $violation) {
                $output->writeln(strtr($template, array(
                    '{{ resources }}'   => implode('</li><li>',$violation['resources']),
                    '{{ category }}'    => $violation['category'],
                    '{{ severity }}'    => $violation['severity'],
                    '{{ title }}'       => $violation['title'],
                    '{{ message }}'     => $violation['message'],
                )));
            }
            $output->writeln("</table>");
        } else {


            $output->writeln(sprintf(
                ' This pull request has no new violation, is great !! <3<3<3   ',
                count($analysis->getViolations())
            ));
            $output->writeln('   ');
        }

        foreach ($analysis->getLinks() as $link) {
            if ('self' == $link->getRel() && 'text/html' == $link->getType()) {
                $output->writeln(sprintf('You can get the full report [here](%s)', $link->getHref()));

                break;
            }
        }
    }
}
