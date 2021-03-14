<?php


namespace tonimoreiraa\essentials\Traits;

use tonimoreiraa\essentials\Job;

trait Jobs
{
    /* Procura cargos deste objeto
     * @return array Array com cargos
     */
    public function getJobs(): array
    {
        $jobs_file = json_decode($this->jobs);

        $jobs = [];
        foreach ($jobs_file ?? [] as $job){
            $job = new Job();
            $job = $job->findById(intval($job->id));
            $jobs[] = $job;
        }

        return $jobs;
    }
}