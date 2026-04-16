<?php

namespace App\Command;

use App\Service\CompetitionLockService;
use App\Service\NotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-competition-locks',
    description: 'Lock/unlock competitions based on schedule proximity'
)]
class UpdateCompetitionLocksCommand extends Command
{
    public function __construct(
        private readonly CompetitionLockService $competitionLockService,
        private readonly NotificationService $notificationService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Mise à jour verrouillage compétitions');

        try {
            $result = $this->competitionLockService->updateCompetitionLocks();

            if (!empty($result['locked'])) {
                $io->info('Verrouillées: ' . implode(', ', $result['locked']));
            }
            if (!empty($result['unlocked'])) {
                $io->info('Déverrouillées: ' . implode(', ', $result['unlocked']));
            }
            if (empty($result['locked']) && empty($result['unlocked'])) {
                $io->info('Aucun changement');
            }

            if (!empty($result['locked']) || !empty($result['unlocked'])) {
                $msg = sprintf(
                    "%s - Verrou compétitions : %s - Déverrou compétitions : %s",
                    date('Y-m-d H:i'),
                    implode(', ', $result['locked']) ?: 'aucune',
                    implode(', ', $result['unlocked']) ?: 'aucune'
                );
                $this->notificationService->sendAdminNotification('[KPI-CRON] Verrou présences', $msg);
            }

            $io->success('Mise à jour terminée');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('Erreur verrouillage: ' . $e->getMessage());

            $this->notificationService->sendAdminNotification(
                '[KPI-CRON] Verrou présences - ERREUR',
                date('Y-m-d H:i') . ' - Erreur verrou: ' . $e->getMessage()
            );

            return Command::FAILURE;
        }
    }
}
