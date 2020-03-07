<?php

namespace App\Command;

use App\Entity\Order;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Calculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;

class CalculateCommand extends Command
{
    protected static $defaultName = 'app:calculate';
    private $calculator;
    private $repository;
    private $em;

    public function __construct(Calculator $calculator, ProductRepository $repository, EntityManagerInterface $em)
    {
        $this->calculator = $calculator;
        $this->repository = $repository;
        $this->em = $em;
        parent::__construct(null);
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $arg1 = $input->getArgument('arg1');

        $productNames = $this->calculator->convertArray($arg1);
        $occurrence   = $this->calculator->findOccurrence($arg1);
        $entities = $this->repository->findBy(['name' => $productNames]);

        $total = $this->calculator->calculate($arg1);

        $table = new Table($output);
        $table->setHeaders(['Product', 'Price']);

        $products = [];
        $total = 0;
        $order = new Order();

        /** @var Product $product */
        foreach($entities as $product) {

            $occurrenceTmp = $occurrence[$product->getName()];
            $price = $occurrenceTmp * $product->getPrice();
            $products[] = [
                $occurrenceTmp . "x" . $product->getName(),
                $price
            ];
            $total += $price;
            $order->addProduct($product);
        }

        $totalDiscounted = $this->calculator->calculate($arg1);
        $products[] = new TableSeparator();
        $products[] = ['Total Price', $total];
        $products[] = new TableSeparator();
        $products[] = ['Discounted Total Price', $totalDiscounted];
        $table->setRows($products);
        $table->render();

        // save to db
        $order->setTotal($totalDiscounted);
        $this->em->persist($order);
        $this->em->flush();

        return 0;
    }
}
