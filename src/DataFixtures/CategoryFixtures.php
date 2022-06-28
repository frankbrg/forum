<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(private SluggerInterface $slugger)
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $count = $this->faker->numberBetween(5, 6);
        for ($a = 1; $a <= $count; $a++) {
            $this->createCategory($manager);
        }

        $manager->flush();
    }

    public function createCategory(ObjectManager $manager): Category
    {
        static $total = 0; $slugs = [];

        do {
            $name = trim($this->faker->sentence(3));
            $slug = $this->slugger->slug($name)->lower();
        } while (in_array($slug, $slugs, true));
        $slugs[] = $slug;

        $category = new Category();
        $category->setName($name);
        $category->setSlug($slug);

        $manager->persist($category);
        $this->setReference('category-' . $total++, $category);

        return $category;
    }
}
