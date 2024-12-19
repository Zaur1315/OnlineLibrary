<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Review;
use App\Form\BookType;
use App\Form\ReviewType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/book')]
class BookController extends AbstractController
{
    #[Route('/', name: 'app_book_index', methods: ['GET'])]
    public function index(BookRepository $bookRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search', '');
        $selectedAuthors = $request->query->all('authors');
        $selectedGenres = $request->query->all('genres');

        $queryBuilder = $entityManager->getRepository(Book::class)->createQueryBuilder('b');

        if ($search) {
            $queryBuilder->andWhere('b.title LIKE :search OR b.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($selectedAuthors)) {
            $queryBuilder->andWhere('b.author IN (:authors)')
                ->setParameter('authors', $selectedAuthors);
        }

        if (!empty($selectedGenres)) {
            $queryBuilder->andWhere('b.genre IN (:genres)')
                ->setParameter('genres', $selectedGenres);
        }

        $books = $queryBuilder->getQuery()->getResult();

        $authors = $entityManager->getRepository(Book::class)
            ->createQueryBuilder('b')
            ->select('DISTINCT b.author')
            ->where('b.author IS NOT NULL')
            ->getQuery()
            ->getResult();

        $genres = $entityManager->getRepository(Book::class)
            ->createQueryBuilder('b')
            ->select('DISTINCT b.genre')
            ->where('b.genre IS NOT NULL')
            ->getQuery()
            ->getResult();

        return $this->render('book/index.html.twig', [
            'books' => $books,
            'authors' => array_column($authors, 'author'),
            'genres' => array_column($genres, 'genre'),
            'selectedAuthors' => $selectedAuthors,
            'selectedGenres' => $selectedGenres,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_book_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('coverImage')->getData();

            if ($coverFile) {
                $originalFilename = pathinfo($coverFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$coverFile->guessExtension();

                try {
                    $coverFile->move(
                        $this->getParameter('covers_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {

                }

                $book->setCoverImage($newFilename);
            }

            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/new.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_book_show', methods: ['GET'])]
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_book_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_book_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/book/{id}/review', name: 'app_book_add_review', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function addReview(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        $review = new Review();
        $review->setBook($book);
        $review->setUser($this->getUser());
        $review->setCreatedAt(new \DateTime());

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($review);
            $entityManager->flush();

            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        return $this->render('review/new.html.twig', [
            'form' => $form->createView(),
            'book' => $book,
        ]);
    }
}
