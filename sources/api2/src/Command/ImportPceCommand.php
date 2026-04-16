<?php

namespace App\Command;

use App\Service\NotificationService;
use App\Service\PceImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-pce',
    description: 'Import PCE license file from FFCK extranet'
)]
class ImportPceCommand extends Command
{
    public function __construct(
        private readonly PceImportService $pceImportService,
        private readonly NotificationService $notificationService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Import PCE FFCK');

        try {
            $result = $this->pceImportService->importPce();

            $io->success(sprintf(
                "Import terminé en %ds (dl=%ds)\n- %d licenciés (%d req.)\n- %d arbitres (%d req.)\n- %d surclassements (%d req.)\n- Saison: %s",
                $result['totalTime'], $result['downloadTime'],
                $result['nbLicencies'], $result['nbReqLicencies'],
                $result['nbArbitres'], $result['nbReqArbitres'],
                $result['nbSurclassements'], $result['nbReqSurclassements'],
                $result['season']
            ));

            $msg = sprintf(
                "%s - PCE Import: %d licenciés (%d req.), %d arbitres (%d req.), %d surclassements (%d req.) - %ds",
                date('Y-m-d H:i'),
                $result['nbLicencies'], $result['nbReqLicencies'],
                $result['nbArbitres'], $result['nbReqArbitres'],
                $result['nbSurclassements'], $result['nbReqSurclassements'],
                $result['totalTime']
            );
            $this->notificationService->sendAdminNotification('[KPI-CRON] Import PCE', $msg);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('Erreur import PCE: ' . $e->getMessage());

            $this->notificationService->sendAdminNotification(
                '[KPI-CRON] Import PCE - ERREUR',
                date('Y-m-d H:i') . ' - Erreur import PCE: ' . $e->getMessage()
            );

            return Command::FAILURE;
        }
    }
}
