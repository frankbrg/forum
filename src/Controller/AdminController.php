<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\User;
use App\Form\CategoryFormType;
use App\Form\EditUserFormType;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(UserRepository $userRepository, CategoryRepository $categoryRepository): Response
    {
        $users = $userRepository->findAll();
        $categories = $categoryRepository->findAll();

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

    #[Route('/delete/{id}', name: 'delete_comment')]
    public function deleteComment(Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $topic = $comment->getTopic();
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('topic_show',['slug' => $topic->getSlug()]);
    }

    #[Route('/edit/{id}', name: 'edit_user')]
    public function editUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EditUserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/edit_user.html.twig', [
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
