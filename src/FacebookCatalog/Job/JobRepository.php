<?php

namespace Kiboko\Component\FacebookCatalog\Job;

use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;

class JobRepository implements JobRepositoryInterface
{
    public function createJobExecution(JobInstance $jobInstance)
    {
        if (null !== $jobInstance->getId()) {
            $jobInstance = $this->jobManager->merge($jobInstance);
        } else {
            $this->jobManager->persist($jobInstance);
        }

        $jobExecution = new $this->jobExecutionClass();
        $jobExecution->setJobInstance($jobInstance);

        $this->updateJobExecution($jobExecution);

        return $jobExecution;
    }

    public function updateJobExecution(JobExecution $jobExecution)
    {
        // TODO: Implement updateJobExecution() method.
    }

    public function updateStepExecution(StepExecution $stepExecution)
    {
        // TODO: Implement updateStepExecution() method.
    }
}