<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Topic;
use App\Form\CommentFormType;
use App\Form\TopicFormType;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_USER')]
#[Route(path: '/topic', name: 'topic_')]
class TopicController extends AbstractController
{

    public function __construct(
        private SluggerInterface $slugger
    ){}

    #[Route('', name: 'index')]
    public function index(TopicRepository $topicRepository, CategoryRepository $categoryRepository): Response
    {
        $topics = $topicRepository->findBy([
            'status' => true
        ],[
            'publishedDate' => 'DESC'
        ]);

        $categories = $categoryRepository->findAll();

        return $this->render('topic/index.html.twig', [
            'topics' => $topics,
            'categories' => $categories
        ]);
    }

    #[Route('/show/{slug}', name: 'show')]
    public function topicShow(Topic $topic, Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        $isOwner = $topic->getUser() === $this->getUser();
        $isGranted = $this->isGranted('ROLE_ADMIN');

        $comments = $commentRepository->findBy([
            'topic' => $topic
        ],[
            'created_at' => 'DESC'
        ]);

        if ($this->getUser() && $form->isSubmitted() && $form->isValid() && $topic->isStatus()) {
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setUser($this->getUser());
            $comment->setTopic($topic);

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('topic_show',['slug' => $topic->getSlug()]);
        }
        return $this->render('topic/show.html.twig', [
            'commentForm' => $form->createView(),
            'topic' => $topic,
            'comments' => $comments,
            'isOwner' => $isOwner,
            'isGranted' => $isGranted
        ]);
    }

    #[Route('/category/{slug}', name: 'show_category')]
    public function categoryShow(Category $category): Response
    {
        return $this->render('topic/showCategory.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $topic = new Topic();
        $form = $this->createForm(TopicFormType::class, $topic);
        $form->handleRequest($request);

        if ($this->getUser() && $form->isSubmitted() && $form->isValid()) {
            $topic->setSlug($this->slugger->slug($topic->getTitle())->lower());
            $topic->setPublishedDate(new \DateTimeImmutable());
            $topic->setUser($this->getUser());

            $entityManager->persist($topic);
            $entityManager->flush();

            return $this->redirectToRoute('topic_show',['slug' => $topic->getSlug()]);
        }

        return $this->render('topic/create.html.twig', [
            'topicForm' => $form->createView(),
        ]);
    }

    #[Route('/toggle/{slug}', name: 'toggle')]
    public function toggle(Topic $topic, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() === $topic->getUser() || $this->isGranted('ROLE_ADMIN')) {
            $topic->setStatus(!$topic->isStatus());
        
            $entityManager->persist($topic);
            $entityManager->flush();
        }
        return $this->redirectToRoute('topic_show',['slug' => $topic->getSlug()]);
    }


    #[Route('/close/{slug}', name: 'close')]
    public function close(Topic $topic, EntityManagerInterface $entityManager): Response
    {
        $isOwner = $topic->getUser() === $this->getUser();
        $isGranted = $this->isGranted('ROLE_ADMIN');

        if ($isOwner || $isGranted) {
            $topic->setStatus(false);
            $entityManager->persist($topic);
            $entityManager->flush();
        }

        return $this->redirectToRoute('topic_show',['slug' => $topic->getSlug()]);
    }
}
