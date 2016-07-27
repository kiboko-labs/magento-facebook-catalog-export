<?php

namespace Kiboko\Component\FacebookCatalog\Command;

use Akeneo\Component\Batch\Job\ExitStatus;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Kiboko\Component\FacebookCatalog\Connector\ConnectorRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Batch command
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @author    Grégory Planchat <gregory@luni.fr>
 * @copyright 2016 Kiboko SAS (http://www.kiboko.fr)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class BatchCommand extends Command
{
    /**
     * @var ConnectorRegistry
     */
    private $connectorRegistry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConnectorRegistry $connectorRegistry,
        LoggerInterface $logger,
        $name = null
    ) {
        parent::__construct($name);

        $this->connectorRegistry = $connectorRegistry;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('kiboko:facebook:catalog')
            ->setDescription('Launch a registered job instance')
            ->addArgument('execution', InputArgument::OPTIONAL, 'Job execution id')
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Override job configuration (formatted as json. ie: ' .
                'php app/console akeneo:batch:job -c "{\"filePath\":\"/tmp/foo.csv\"}" acme_product_import)'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jobInstance = new JobInstance('Kiboko Facebook catalog stream', 'export', 'Catalog export');

        $job = $this->connectorRegistry->getJob($jobInstance);
        if ($job === null) {
            $output->writeln(
                sprintf(
                    '<error>The %s jobs for "%s" connector could not be loaded.</error>',
                    $jobInstance->getType(),
                    $jobInstance->getConnector()
                )
            );
            return -1;
        }
        $jobInstance->setJob($job);

        // Override job configuration
        if ($config = $input->getOption('config')) {
            $job->setConfiguration(
                $this->decodeConfiguration($config)
            );
        }

        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance($jobInstance);

        $jobExecution->setPid(getmypid());

        $job->execute($jobExecution);

        $job->getJobRepository()->updateJobExecution($jobExecution);

        if (ExitStatus::COMPLETED === $jobExecution->getExitStatus()->getExitCode()) {
            $output->writeln(
                sprintf(
                    '<info>%s %s has been successfully executed.</info>',
                    ucfirst($jobInstance->getType()),
                    $jobInstance->getCode()
                )
            );
        } else {
            $output->writeln(
                sprintf(
                    '<error>An error occured during the %s execution.</error>',
                    $jobInstance->getType()
                )
            );
            $verbose = $input->getOption('verbose');
            $this->writeExceptions($output, $jobExecution->getFailureExceptions(), $verbose);
            foreach ($jobExecution->getStepExecutions() as $stepExecution) {
                $this->writeExceptions($output, $stepExecution->getFailureExceptions(), $verbose);
            }
            return -1;
        }
    }

    /**
     * Writes failure exceptions to the output
     *
     * @param OutputInterface $output
     * @param array[]         $exceptions
     * @param boolean         $verbose
     */
    protected function writeExceptions(OutputInterface $output, array $exceptions, $verbose)
    {
        foreach ($exceptions as $exception) {
            $output->write(
                sprintf(
                    '<error>Error #%s in class %s: %s</error>',
                    $exception['code'],
                    $exception['class'],
                    strtr($exception['message'], $exception['messageParameters'])
                ),
                true
            );
            if ($verbose) {
                $output->write(sprintf('<error>%s</error>', $exception['trace']), true);
            }
        }
    }

    /**
     * @param string $data
     *
     * @return array
     */
    private function decodeConfiguration($data)
    {
        $config = json_decode($data, true);

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $error = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                return $config;
        }

        throw new \InvalidArgumentException($error);
    }
}
