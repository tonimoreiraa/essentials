<?php


namespace tonimoreiraa\essentials;


class Job extends DataLayer
{
    public function __construct()
    {
        parent::__construct('jobs', ['name', 'completename']);
    }
}