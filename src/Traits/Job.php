<?php


namespace tonimoreiraa\essentials\Traits;

use CoffeeCode\DataLayer\DataLayer;
use tonimoreiraa\essentials\Job as Core;

trait Job
{
    /** Seta cargo
     * @param Core $job Um cargo válido
     * @return void
     */
    public function setJob(Core $job): void
    {
        $this->job_id = $job->id;
    }

    /** Pega cargo do usuário
     * @return Core|DataLayer
     */
    public function getJob(): Core|DataLayer
    {
        $job = new Core();
        $job = $job->findById($this->job_id);
        return $job;
    }

    public function hasJob(Core $job): bool
    {
        return $this->job_id == $job->id;
    }
}