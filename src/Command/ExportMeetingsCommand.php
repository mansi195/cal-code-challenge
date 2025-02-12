<?php

namespace App\Command;

use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use App\Entity\Employee;
use App\Entity\ProjectPlanning;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(name: 'app:employee-meetings')]
class ExportMeetingsCommand extends Command
{
    private $entityManager;
    private $filesystem;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->filesystem = new Filesystem();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getOption('employee-email');
        $outputDir = $input->getOption('output-dir');
        $output->writeln('Exporting meetings to ical format!');

        // Check if the output directory exists, if not, create it
        if (!$this->filesystem->exists($outputDir)) {
            $this->filesystem->mkdir($outputDir);
        }

        // Retrieve meetings based on the email or all employees if no email is provided
        if ($email) {
            
            $employee = $this->entityManager->getRepository(Employee::class)->findOneBy(['email' => $email]);
            
            // if employee does not exist
            if (!$employee) {
                $output->writeln("<error>Employee not found!</error>");
                return Command::FAILURE;
            }

            // get meetings
            $meetings = $this->entityManager->getRepository(ProjectPlanning::class)->findBy(['employee' => $employee]);

        } 
        else {

            // Retrieve all employees if email is not provided
            $employees = $this->entityManager->getRepository(Employee::class)->findAll();
            $meetings = [];
            foreach ($employees as $employee) {
                $meetings[] = $this->entityManager->getRepository(ProjectPlanning::class)->findBy(['employee' => $employee]);
            }
        }

        // If no meetings are found, return success and show message
        if (empty($meetings)) {
            $output->writeln("<info>No meetings found to export.</info>");
            return Command::SUCCESS;
        }

        if ($email) {
            // Create the iCal calendar for the specific consultant
            $calendar = Calendar::create();
            $events = [];

            foreach ($meetings as $meeting) {
                
                // Create an Event for each meeting
                $startDate = $meeting->getStartDate();
                $endDate = $meeting->getEndDate();

                // Ensure the start and end dates are valid DateTime objects
                if (!$startDate instanceof \DateTime || !$endDate instanceof \DateTime) {
                    $output->writeln("<error>Invalid dates for meeting with name {$meeting->getDescription()}</error>");
                    continue;
                }

                // Create the event
                $event = Event::create($meeting->getDescription())
                                ->startsAt($startDate)
                                ->endsAt($endDate)
                                ->description($meeting->getNotes() ?? '')
                                ->uniqueIdentifier(uniqid()); 

                // Add event to the array
                $events[] = $event;
            }

            // Add events to calendar
            $calendar->event($events);

            // Save the iCal file
            $filename = $outputDir . '/' . ($email ?? 'all_meetings') . '.ics';
            try {
                file_put_contents($filename, $calendar->toString());
                $output->writeln("<info>iCal file generated: $filename</info>");
            } 
            catch (IOExceptionInterface $exception) {
                $output->writeln("<error>Failed to write to file: {$exception->getMessage()}</error>");
                return Command::FAILURE;
            }

        } 
        else {
            // If no email, generate one .ics file per consultant
            foreach ($employees as $employee) {
                
                // Retrieve meetings for each consultant
                $employeeMeetings = $this->entityManager->getRepository(ProjectPlanning::class)->findBy(['employee' => $employee]);
                if (empty($employeeMeetings)) {
                    continue; // Skip if no meetings found for this employee
                }

                // Create the iCal calendar for this employee
                $calendar = Calendar::create();
                $events = [];

                foreach ($employeeMeetings as $meeting) {
                    
                    // Create an Event for each meeting
                    $startDate = $meeting->getStartDate();
                    $endDate = $meeting->getEndDate();

                    // Ensure the start and end dates are valid DateTime objects
                    if (!$startDate instanceof \DateTime || !$endDate instanceof \DateTime) {
                        $output->writeln("<error>Invalid start or end date for meeting with ID {$meeting->getId()}</error>");
                        continue;
                    }

                    // Create the event
                    $event = Event::create($meeting->getDescription())
                                    ->startsAt($startDate)
                                    ->endsAt($endDate)
                                    ->description($meeting->getNotes() ?? '')
                                    ->uniqueIdentifier(uniqid()); 

                    // Add event to the array
                    $events[] = $event;
                }

                // Add events to the calendar
                $calendar->event($events);

                // Save the iCal file for this employee
                $filename = $outputDir . '/' . $employee->getEmail() . '.ics';
                try {
                    file_put_contents($filename, $calendar->toString());
                    $output->writeln("<info>iCal file generated for {$employee->getEmail()}: $filename</info>");
                } catch (IOExceptionInterface $exception) {
                    $output->writeln("<error>Failed to write to file for {$employee->getEmail()}: {$exception->getMessage()}</error>");
                    return Command::FAILURE;
                }
            }
        }

        return Command::SUCCESS;
    }


    
    protected function configure()
    {
        $this
        ->setDescription('Export meetings to an iCal formatted file for employees')
        ->addOption('employee-email', null, InputOption::VALUE_OPTIONAL, 'Employee Email')
        ->addOption('output-dir', null, InputOption::VALUE_OPTIONAL, 'Store ical generated file', 'output');
    }

}

?>
