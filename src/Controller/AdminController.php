<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\User;
use App\Form\CategoryFormType;
use App\Form\UserFormType;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Security\Authenticator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(UserRepository $userRepository, CategoryRepository $categoryRepository): Response
    {
        $users = $userRepository->findAll();
        $categories = $categoryRepository->findAll();

        dd($users, $categories);
        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'categories' => $categories,
        ]);
    }

    #[Route('/toggle/{id}', name: 'toggle')]
    public function toggle(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setStatus(!$user->isStatus());
        
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('admin_index');
    }

    #[Route('/toggle/{id}', name: 'delete_comment')]
    public function deleteComment(Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('topic_index');
    }

    #[Route('/edit/{id}', name: 'edit_user')]
    public function editUser(User $user, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('security/edit.html.twig', [
            'userForm' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/edit/category/{id}', name: 'edit_category')]
    public function editCategory(Category $category, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/edit_category.html.twig', [
            'categoryForm' => $form->createView(),
            'category' => $category,
        ]);
    }

}
