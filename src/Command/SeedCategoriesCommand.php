<?php

namespace App\Command;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:seed:categories',
    description: 'Create default categories',
)]
class SeedCategoriesCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $categories = ['General', 'Technology', 'Lifestyle', 'Travel', 'Food', 'Other'];

        foreach ($categories as $name) {
            $existing = $this->em->getRepository(Category::class)->findOneBy(['name' => $name]);
            if (!$existing) {
                $cat = new Category();
                $cat->setName($name);
                $this->em->persist($cat);
            }
        }

        $this->em->flush();
        $output->writeln('<info>Categories seeded successfully!</info>');

        return Command::SUCCESS;
    }
}
