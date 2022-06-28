<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Topic;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\String\Slugger\SluggerInterface;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct(private SluggerInterface $slugger)
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $count = $this->faker->numberBetween(30, 50);
        for ($a = 0; $a < $count; $a++) {
            $randomTopicId = $this->faker->numberBetween(0, 9);
            /** @var Topic $randomTopic */
            $randomTopic = $this->getReference('topic-' . $randomTopicId);

            $this->createComment($manager, $randomTopic);
        }

        $manager->flush();
    }

    public function createComment(ObjectManager $manager, Topic $topic)
    {
        $randomUserId = $this->faker->numberBetween(0, 4);
        /** @var User $randomUser */
        $randomUser = $this->getReference('user-' . $randomUserId);
        $minDate = $topic->getPublishedDate()->format('c');

        $date = \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween($minDate, 'now'));

        $comment = new Comment();
        $comment->setUser($randomUser);
        $comment->setContent($this->faker->realText());
        $comment->setTopic($topic);

        $manager->persist($comment);
        return $comment;
    }

    public function getDependencies() : array
    {
        return [
            UserFixtures::class,
            TopicFixtures::class,
        ];
    }
}
