<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(private UserPasswordHasherInterface $passwordEncoder,
    private SluggerInterface $slugger)
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $count = $this->faker->numberBetween(5, 20);
        for ($a = 0; $a < $count; $a++) {
            $this->createUser($manager);
        }

        $this->createUser($manager, [
            'username' => 'frankbrg',
            'email' => 'frank.brg@icloud.com',
            'firstname' => 'Frank',
            'lastname' => 'Bergé',
            'address' => '21 rue de la pomme',
            'postalCode' => '31300',
            'city' => 'Bordeaux',
            'phoneNumber' => '0607641568',
            'password' => 'frankberge',
            'roles' => ['ROLE_ADMIN'],
        ]);

        $manager->flush();
    }

    public function createUser(ObjectManager $manager, array $data = [])
    {
        static $index = 0;

        $data = array_replace(
            [
                'username' => $this->slugger->slug($this->faker->firstName().$this->faker->lastName())->lower(),
                'email' => $this->faker->email(),
                'firstname' => $this->faker->firstName(),
                'lastname' => $this->faker->lastName(),
                'password' => $this->faker->password(),
                'address' => $this->faker->address(),
                'postalCode' => $this->faker->postcode(),
                'city' => $this->faker->city(),
                'phoneNumber' => $this->faker->phoneNumber(),
                'roles' => ['ROLE_USER'],
            ],
            $data,
        );
        $user = (new User())
            ->setUsername($data['username'])
            ->setEmail($data['email'])
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setAddress($data['address'])
            ->setPostalCode($data['postalCode'])
            ->setCity($data['city'])
            ->setPhoneNumber($data['phoneNumber'])
            ->setRoles($data['roles']);

        $user->setPassword($this->passwordEncoder->hashPassword($user, $data['password']));
        $manager->persist($user);
        $this->setReference('user-' . $index++, $user);
    }
}
