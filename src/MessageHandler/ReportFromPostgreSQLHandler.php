<?php


namespace App\MessageHandler;

use App\Entity\Report;
use App\Entity\Result;
use App\Entity\User;
use App\Message\ReportFromPostgreSQL;
use Doctrine\ORM\EntityManagerInterface;
use PDO;
use Psr\Log\LoggerInterface;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ReportFromPostgreSQLHandler implements MessageHandlerInterface
{
    private $entityManager;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $messengerAuditLogger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $messengerAuditLogger;
    }

    public function __invoke(ReportFromPostgreSQL $reportFromPostgreSQL)
    {
        $reportRepository = $this->entityManager->getRepository(Report::class);
        $report = $reportRepository->find($reportFromPostgreSQL->getReport()->getId());
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['username'=>$reportFromPostgreSQL->getReporter()]);

        $report->setState("running");
        $this->entityManager->flush();

//        sleep(10);
//        $statement = $this->entityManager->getConnection('doctrine.dbal.destdb')->prepare($reportFromPostgreSQL->getReport()->getQuery());
//        $statement->execute();

        try{
            $connectionString = $_SERVER['DESTINATION_DATABASE_URL'] ?? $_ENV['DESTINATION_DATABASE_URL'] ?? getenv('DESTINATION_DATABASE_URL');
            $connection = new \PDO($connectionString);
            $statement = $connection->query($reportFromPostgreSQL->getReport()->getQuery());
            $result = new Result();
            $result->setOutcome(serialize($statement->fetchAll(PDO::FETCH_ASSOC)));
            $result->setRanAt(new \DateTime());
            $result->setReport($report);
            $result->setReporter($user);
            $this->entityManager->persist($result);
            $this->entityManager->flush();
        }catch(\Exception $e){
            $this->logger->error($e);
        } finally {
            $report->setState("idle");
            $this->entityManager->flush();
        }
    }
}