<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
 */
class Client
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 58,
     *      minMessage = "Votre nom doit comporté au moins {{ limit }} caractères",
     *      maxMessage = "Votre nom doit comporté jusqu'à {{ limit }} caractères"
     * )
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 58,
     *      minMessage = "Votre nom doit comporté au moins {{ limit }} caractères",
     *      maxMessage = "Votre nom doit comporté jusqu'à {{ limit }} caractères"
     * )
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 80,
     *      minMessage = "Votre adresse doit comporté au moins {{ limit }} caractères",
     *      maxMessage = "Votre adresse doit comporté jusqu'à {{ limit }} caractères"
     * )
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 80,
     *      minMessage = "Votre ville doit comporté au moins {{ limit }} caractères",
     *      maxMessage = "Votre ville doit comporté jusqu'à {{ limit }} caractères"
     * )
     */
    private $ville;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/^[0-9]{5}/")
     * @Assert\Length(
     *      min = 5,
     *      max = 5,
     *     )
     */
    private $codePostal;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\Regex("/^[0-9]{10}/")
     * @Assert\Length(
     *      min = 10,
     *      max = 10,
     *     )
     */
    private $telephone;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *      min = 14,
     *      max = 99,
     *      minMessage = "Votre âge doit être au minimum de {{ limit }} ans",
     *      maxMessage = "Votre nom doit comporter jusqu'à {{ limit }} ans"
     * )
     */
    protected $age;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *      min = 25,
     *      max = 350,
     *      minMessage = "Votre poids doit être au minimum de {{ limit }} kg",
     *      maxMessage = "Votre poids doit comporter jusqu'à {{ limit }} kg"
     * )
     */
    private $poids;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *      min = 45,
     *      max = 300,
     *      minMessage = "Votre âge doit être au minimum de {{ limit }} cm",
     *      maxMessage = "Votre âge doit comporter jusqu'à {{ limit }} cm"
     * )
     */
    private $taille;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rdv", mappedBy="client")
     */
    private $rdvs;

    public function __construct()
    {
        $this->rdvs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getPoids(): ?int
    {
        return $this->poids;
    }

    public function setPoids(int $poids): self
    {
        $this->poids = $poids;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTaille(): ?string
    {
        return $this->taille;
    }

    public function setTaille(string $taille): self
    {
        $this->taille = $taille;

        return $this;
    }

    /**
     * @return Collection|Rdv[]
     */
    public function getRdvs(): Collection
    {
        return $this->rdvs;
    }

    public function addRdv(Rdv $rdv): self
    {
        if (!$this->rdvs->contains($rdv)) {
            $this->rdvs[] = $rdv;
            $rdv->setClient($this);
        }

        return $this;
    }

    public function removeRdv(Rdv $rdv): self
    {
        if ($this->rdvs->contains($rdv)) {
            $this->rdvs->removeElement($rdv);
            // set the owning side to null (unless already changed)
            if ($rdv->getClient() === $this) {
                $rdv->setClient(null);
            }
        }

        return $this;
    }
}
