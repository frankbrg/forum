<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Topic;
use App\Repository\CategoryRepository;
use App\Repository\TopicRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/topic', name: 'topic_')]
class TopicController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(TopicRepository $topicRepository, CategoryRepository $categoryRepository): Response
    {
        $topics = $topicRepository->findBy([
            'status' => true
        ],[
            'publishedDate' => 'DESC'
        ]);

        $categories = $categoryRepository->findAll();

        dd($topics,$categories);
        return $this->render('topic/index.html.twig', [
            'topics' => $topics,
        ]);
    }

    #[Route('/{slug}', name: 'show')]
    public function topicShow(Topic $topic): Response
    {
        dd($topic);
        
        return $this->render('topic/show.html.twig', [
            'topic' => $topic,
        ]);
    }

    #[Route('/category/{slug}', name: 'show_category')]
    public function categoryShow(Category $category): Response
    {
        dd($category);
        
        return $this->render('topic/showCategory.html.twig', [
            'category' => $category,
        ]);
    }
}
