<?php

namespace App\DataFixtures;

use App\Factory\MailFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        MailFactory::createMany(60);

        $manager->flush();
    }
}
