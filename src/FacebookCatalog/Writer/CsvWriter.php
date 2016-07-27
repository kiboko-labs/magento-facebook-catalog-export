<?php

namespace Kiboko\Component\FacebookCatalog\Writer;


use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Job\RuntimeErrorException;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Kiboko\Component\Connector\ConfigurationAwareTrait;
use Kiboko\Component\Connector\NameAwareTrait;
use Kiboko\Component\Connector\StepExecutionAwareTrait;

class CsvWriter
    extends AbstractConfigurableStepElement
    implements ItemWriterInterface, StepExecutionAwareInterface
{
    use NameAwareTrait;
    use ConfigurationAwareTrait;
    use StepExecutionAwareTrait;

    private $headers;
    private $buffer;
    private $delimiter = ',';
    private $enclosure = '"';

    public function getConfigurationFields()
    {
        return [];
    }

    public function initialize()
    {
        parent::initialize();

        $this->headers = [];
    }

    public function write(array $items)
    {
        foreach ($items as $current) {
            $this->addToHeaders(array_keys($current));
            $this->buffer[] = $current;
        }
    }

    public function flush()
    {
        $csvFile = $this->createCsvFile();

        $this->writeToCsvFile($csvFile, $this->headers);

        $hollowItem = array_fill_keys($this->headers, '');
        foreach ($this->buffer as $incompleteItem) {
            $item = array_replace($hollowItem, $incompleteItem);
            $this->writeToCsvFile($csvFile, $item);

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }

        fclose($csvFile);
    }

    private function getPath()
    {
        return getcwd() . '/output.csv';
    }

    /**
     * Add the specified keys to the list of headers
     *
     * @param array $keys
     */
    protected function addToHeaders(array $keys)
    {
        $headers = array_merge($this->headers, $keys);
        $headers = array_unique($headers);

        $identifier = array_shift($headers);
        natsort($headers);
        array_unshift($headers, $identifier);

        $this->headers = $headers;
    }

    /**
     * Create the file to write to and return its pointer
     *
     * @throws RuntimeErrorException
     *
     * @return resource
     */
    protected function createCsvFile()
    {
        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            mkdir($exportDirectory);
        }

        if (false === $file = fopen($this->getPath(), 'w')) {
            throw new RuntimeErrorException(
                'Failed to open file %path%',
                [
                    '%path%' => $this->getPath()
                ]
            );
        }

        return $file;
    }

    /**
     * Write a csv formatted line into the specified file. If an error occurs the file is closed and an exception is
     * thrown.
     *
     * @param resource $csvFile
     * @param array    $data
     *
     * @throws RuntimeErrorException
     */
    protected function writeToCsvFile($csvFile, array $data)
    {
        if (false === fputcsv($csvFile, $data, $this->delimiter, $this->enclosure)) {
            fclose($csvFile);
            throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
        }
    }
}