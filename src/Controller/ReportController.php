<?php

namespace App\Controller;

use App\Entity\Report;
use App\Form\ReportType;
use App\Message\ReportFromPostgreSQL;
use App\Repository\ReportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/report")
 */
class ReportController extends AbstractController
{
    /**
     * @Route("/", name="report_index", methods={"GET"})
     */
    public function index(ReportRepository $reportRepository): Response
    {
        return $this->render('report/index.html.twig', [
            'reports' => $reportRepository->findBy([],['id'=>'ASC'])
        ]);
    }

    /**
     * @param ReportRepository $reportRepository
     * @return Response
     * @Route("/all", name="report_index_api", methods={"GET"})
     */
    public function index_api(ReportRepository $reportRepository): Response
    {
//        $reports = $reportRepository->findBy([], ['id'=>'ASC']);
        $statement = $this->getDoctrine()->getConnection()->prepare('SELECT report.*, tempTable.result_id FROM (SELECT report_id,MAX(id) as result_id FROM result GROUP BY report_id ORDER BY report_id ) AS tempTable RIGHT JOIN report ON report.id=temptable.report_id');
        $statement->execute();
        return $this->json($statement->fetchAll());
    }

    /**
     * @Route("/new", name="report_new", methods={"GET"})
     */
    public function new(): Response
    {
        return $this->render('report/new.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/new", name="report_new_post", methods={"POST"})
     */
    public function new_post(Request $request): Response
    {
        $theToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('new_report', $theToken)) {
            $submittedReport = $request->request->get('report');
            $report = new Report();
            $report->setName($submittedReport['name']);
            $report->setState('idle');
            $report->setRecursion($submittedReport['recursion']);
            $report->setQuery($submittedReport['query']);
            $this->getDoctrine()->getManager()->persist($report);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('report_index');
        }
        return $this->render('report/new.html.twig', ['message'=>['type'=>'error', 'data'=>'Please submit the form correctly!']]);
    }

    /**
     * @Route("/{id}", name="report_show", methods={"GET"})
     */
    public function show(Report $report): Response
    {
        return $this->render('report/show.html.twig', [
            'report' => $report,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="report_edit", methods={"POST"})
     */
    public function edit(Request $request, Report $report): Response
    {
        $theToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('edit_report', $theToken)) {
            $submittedReport = $request->request->get('report');
            $report->setName($submittedReport['name']);
            $report->setRecursion($submittedReport['recursion']);
            $report->setQuery($submittedReport['query']);
            $this->getDoctrine()->getManager()->persist($report);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('report_show', ['id'=>$report->getId()]);
        }
        return $this->render('report/new.html.twig', ['message'=>['type'=>'error', 'data'=>'Please submit the form correctly!']]);
    }

    /**
     * @Route("/{id}", name="report_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Report $report): Response
    {
        if ($this->isCsrfTokenValid('delete'.$report->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($report);
            $entityManager->flush();
        }

        return $this->redirectToRoute('report_index');
    }


    /**
     * @Route("/{id}/run", name="report_run", methods={"GET"})
     * @param MessageBusInterface $messageBus
     * @param Report $report
     * @return Response
     */
    public function run(MessageBusInterface $messageBus, Report $report): Response
    {
        if ($report->getState()=="idle"){
            $report->setState("wait");
            $this->getDoctrine()->getManager()->flush();
            $me = $this->getUser();
            $messageBus->dispatch(new ReportFromPostgreSQL($report, $me->getUsername()));
            return $this->redirectToRoute('report_index', [
                'message' => [
                    'type' => 'info',
                    'data' => '"'.$report->getName().'" pushed to the queue.'
                ]
            ]);
        }
        return $this->redirectToRoute('report_index', [
            'message'=>[
                'type'=>'warning',
                'data'=>'"'.$report->getName().'" is currently on the queue!'
            ]]);
    }

    /**
     * @param Report $report
     * @return Response
     * @Route("/{id}/result", name="report_result", methods={"GET"})
     */
    public function lastResult(Report $report): Response
    {
        return $this->json($report);
    }

    /**
     * @param Report $report
     * @return Response
     * @Route("/{id}/results", name="report_results", methods={"GET"})
     */
    public function lastResults(Report $report): Response
    {
        return $this->json($report);
    }
}
