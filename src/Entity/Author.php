<?php

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_AUTHOR_NOM_PRENOM', columns: ['nom', 'prenom'])]
#[UniqueEntity(
    fields: ['nom', 'prenom'],
    message: 'Cet auteur existe déjà dans la base de données.',
    ignoreNull: false
)]
class Author
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom de l\'auteur ne peut pas être vide.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le prénom de l\'auteur ne peut pas être vide.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $prenom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bibliographie = null;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\OneToMany(targetEntity: Book::class, mappedBy: 'author')]
    private Collection $books;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * Définit le nom et le normalise automatiquement en MAJUSCULES
     * Exemple: "hugo" → "HUGO", "HUGO" → "HUGO", "Hugo" → "HUGO"
     */
    public function setNom(string $nom): static
    {
        $this->nom = mb_strtoupper(trim($nom));

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * Définit le prénom et le normalise automatiquement avec première lettre majuscule
     * Exemple: "victor" → "Victor", "VICTOR" → "Victor", "vIcToR" → "Victor"
     */
    public function setPrenom(string $prenom): static
    {
        // Convertit en minuscules puis met la première lettre en majuscule
        $this->prenom = mb_convert_case(trim($prenom), MB_CASE_TITLE, "UTF-8");

        return $this;
    }

    /**
     * Retourne le nom complet formaté: Prenom NOM
     * Exemple: "Victor HUGO"
     */
    public function getAllName(): ?string
    {
        if (!$this->prenom || !$this->nom) {
            return null;
        }
        return $this->prenom . ' ' . $this->nom;
    }

    public function getBibliographie(): ?string
    {
        return $this->bibliographie;
    }

    public function setBibliographie(?string $bibliographie): static
    {
        $this->bibliographie = $bibliographie !== null ? trim($bibliographie) : null;

        return $this;
    }

    /**
     * @return Collection<int, Book>
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): static
    {
        if (!$this->books->contains($book)) {
            $this->books->add($book);
            $book->setAuthor($this);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            // set the owning side to null (unless already changed)
            if ($book->getAuthor() === $this) {
                $book->setAuthor(null);
            }
        }

        return $this;
    }
}
