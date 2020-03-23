<?php


namespace App\Message;


use App\Entity\Report;
use App\Entity\User;

class ReportFromPostgreSQL
{
    private $report;
    private $reporter;

    public function __construct(Report $report, string $reporter)
    {
        $this->report = $report;
        $this->reporter = $reporter;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    /**
     * @return mixed
     */
    public function getReporter(): string
    {
        return $this->reporter;
    }
}