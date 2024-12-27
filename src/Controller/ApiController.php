<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Review;
use App\Repository\BookRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    #[Route('/books', name: 'api_books', methods: ['GET'])]
    public function getBooks(BookRepository $bookRepository): JsonResponse
    {
        $books = $bookRepository->findAll();

        $data = array_map(function (Book $book) {
            return [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'genre' => $book->getGenre(),
                'description' => $book->getDescription(),
                'coverImage' => $book->getCoverImage(),
            ];
        }, $books);

        return $this->json($data);
    }

    #[Route('/books/{id}', name: 'api_book_detail', methods: ['GET'])]
    public function getBookDetail(Book $book): JsonResponse
    {
        return $this->json([
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'genre' => $book->getGenre(),
            'description' => $book->getDescription(),
            'coverImage' => $book->getCoverImage(),
        ]);
    }

    #[Route('/reviews', name: 'api_create_review', methods: ['POST'])]
    public function createReview(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        // Проверка типа пользователя
        if (!$user instanceof \App\Entity\User) {
            return $this->json(['error' => 'Invalid user type'], 400);
        }

        $data = json_decode($request->getContent(), true);
        $bookId = $data['book_id'] ?? null;
        $rating = $data['rating'] ?? null;
        $comment = $data['comment'] ?? null;

        if (!$bookId || !$rating || !$comment) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $book = $entityManager->getRepository(Book::class)->find($bookId);
        if (!$book) {
            return $this->json(['error' => 'Book not found'], 404);
        }

        $review = new Review();
        $review->setBook($book);
        $review->setUser($user);
        $review->setRating($rating);
        $review->setComment($comment);
        $review->setCreatedAt(new \DateTime());

        $entityManager->persist($review);
        $entityManager->flush();

        return $this->json(['success' => 'Review created']);
    }
}
