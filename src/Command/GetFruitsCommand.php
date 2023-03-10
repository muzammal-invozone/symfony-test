<?php

namespace App\Command;
use App\Entity\Fruits;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Connection;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'get:fruits',
    description: 'This command get the fruits, insert it in our database and sent the mail.',
)]
class GetFruitsCommand extends Command implements ContainerAwareInterface
{

    use ContainerAwareTrait;


    private $entityManager;
    private $connection;
    private $mailer;

    public function __construct(EntityManagerInterface $entityManager , Connection $connection , MailerInterface $mailer)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->connection = $connection;
        $this->mailer = $mailer;
    }

    protected function getFruitsData()
    {
        $fruitsAPIURL = $_ENV['FRUITS_API_URL'];

        $client = HttpClient::create();

        $response = $client->request('GET', $fruitsAPIURL. '/api/fruit/all');

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        return $content;
    }
    // This is Method1 for inserting Products
    private function insertFruitsMethod1($data){
        $fruit_names = [];

        foreach ($data as $row) {
            $fruit = $this->entityManager->getRepository(Fruits::class)->findOneBy(['name' => $row->name]);
            if (!$fruit) {
                array_push($fruit_names, $row->name);
                $fruit = new Fruits();
                $fruit->setName($row->name);
                $fruit->setGenus($row->genus);
                $fruit->setFamily($row->family);
                $fruit->setFruitOrder($row->order);
                $fruit->setNutritions(json_encode($row->nutritions));
                $this->entityManager->persist($fruit);
            }
        }

        $this->entityManager->flush();
        return $fruit_names;
    }

    // This is Method2 for inserting Products
    private function insertFruitsMethod2($data){
        $fruit_names = [];
        $values = [];
        foreach ($data as $row) {
            $fruit = $this->entityManager->getRepository(Fruits::class)->findOneBy(['name' => $row->name]);
            if(!$fruit){
                array_push($fruit_names, $row->name);
                $values[] = "('" . $row->name . "', '" . $row->genus . "', '" . $row->family . "', '" . $row->order . "', '" . json_encode($row->nutritions) . "')";
            }
        }
        if(count($fruit_names) > 0){
            $insertQuery = "INSERT INTO fruits (`name`, genus , family , `fruit_order` , nutritions) VALUES " . implode(",", $values);
            $this->connection->executeQuery($insertQuery);
        }
        
        return $fruit_names;
    }

    private function sendEmail($fruit_names){
        $email = (new TemplatedEmail())
        ->from($_ENV['EMAIL_FROM'])
        ->to($_ENV['EMAIL_TO'])
        ->subject('Fruits Added Notification')
        ->htmlTemplate('emails/fruits.html.twig')
        ->context([
            'fruit_names' => $fruit_names,
        ]);

        $this->mailer->send($email);

    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $fruitsData = $this->getFruitsData();
        
        $insertFruitsMethod1= $this->insertFruitsMethod1(json_decode($fruitsData));
        
        // ================This is method 2 code , comment for now becuase method 1 code is working===========
        // $insertFruitsMethod2= $this->insertFruitsMethod2(json_decode($fruitsData));

        if(count($insertFruitsMethod1) > 0){
            $sendEmail = $this->sendEmail($insertFruitsMethod1);
        }
        
        $text = '';
        if(count($insertFruitsMethod1) === 0){
            $text = 'May be same data already exist in the database.';
        }

        $io->success(count($insertFruitsMethod1).' fruits are added.'.$text);

        return Command::SUCCESS;
    }
}
