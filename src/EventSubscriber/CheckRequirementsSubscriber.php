<?php


namespace App\EventSubscriber;

use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;


class CheckRequirementsSubscriber implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
           
            ConsoleEvents::ERROR => 'handleConsoleError',
           
            KernelEvents::EXCEPTION => 'handleKernelException',
        ];
    }

    /**
* Cette méthode vérifie s'il y a eu une erreur dans une commande liée à
     * la base de données puis vérifie si l'extension PHP 'sqlite3' est activée
     * ou ne pas afficher un meilleur message d'erreur.
     */
    public function handleConsoleError(ConsoleErrorEvent $event): void
    {
        $commandNames = ['doctrine:fixtures:load', 'doctrine:database:create', 'doctrine:schema:create', 'doctrine:database:drop'];

        if ($event->getCommand() && \in_array($event->getCommand()->getName(), $commandNames, true)) {
            if ($this->isSQLitePlatform() && !\extension_loaded('sqlite3')) {
                $io = new SymfonyStyle($event->getInput(), $event->getOutput());
                $io->error('This command requires to have the "sqlite3" PHP extension enabled because, by default, the Symfony Demo application uses SQLite to store its information.');
            }
        }
    }

    /**
     * This method checks if the triggered exception is related to the database
     * and then, it checks if the required 'sqlite3' PHP extension is enabled.
     */
    public function handleKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        // Since any exception thrown during a Twig template rendering is wrapped
        // in a Twig_Error_Runtime, we must get the original exception.
        $previousException = $exception->getPrevious();

        // Driver exception may happen in controller or in twig template rendering
        $isDriverException = ($exception instanceof DriverException || $previousException instanceof DriverException);

        // Check if SQLite is enabled
        if ($isDriverException && $this->isSQLitePlatform() && !\extension_loaded('sqlite3')) {
            $event->setException(new \Exception('PHP extension "sqlite3" must be enabled because, by default, the Symfony Demo application uses SQLite to store its information.'));
        }
    }

    /**
     * Checks if the application is using SQLite as its database.
     */
    private function isSQLitePlatform(): bool
    {
        $databasePlatform = $this->entityManager->getConnection()->getDatabasePlatform();

        return $databasePlatform ? 'sqlite' === $databasePlatform->getName() : false;
    }
}
