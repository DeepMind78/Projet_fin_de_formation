<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CoachRepository")
 * @Vich\Uploadable
 */
class Coach
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
     *      minMessage = "Votre nom doit comporter au moins {{ limit }} caractères",
     *      maxMessage = "Votre nom doit comporter jusqu'à {{ limit }} caractères"
     * )
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 58,
     *      minMessage = "Votre prénom doit comporter au moins {{ limit }} caractères",
     *      maxMessage = "Votre prénom doit comporter jusqu'à {{ limit }} caractères"
     * )
     */
    private $prenom;

//   ** PHOTO **
    /**
     * @var string|null
     * @ORM\Column(type="string", length=255)
     */
    private $filename;
    /**
     * @var  File|null
     * @Vich\UploadableField(mapping="property_image", fileNameProperty="filename")
     * @Assert\NotBlank(message="Veuillez mettre un fichier s'il vous plait.")
     * @Assert\File(
     *     mimeTypes = {"image/jpeg", "image/png"},
     *     mimeTypesMessage = "S'il vous plait, loader un fichier type jpeg ou png."
     * )
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 80,
     *      minMessage = "Votre adresse doit comporter au moins {{ limit }} caractères",
     *      maxMessage = "Votre adresse doit comporter jusqu'à {{ limit }} caractères"
     * )
     */
    private $adresse;

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
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 80,
     *      minMessage = "Votre ville doit comporter au moins {{ limit }} caractères",
     *      maxMessage = "Votre ville doit comporter jusqu'à {{ limit }} caractères"
     * )
     */
    private $ville;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *      min = 14,
     *      max = 99,
     *      minMessage = "Votre âge doit être au minimum de {{ limit }} ans",
     *      maxMessage = "Votre nom doit comporter jusqu'à {{ limit }} ans"
     * )
     */
    private $age;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\Regex("/^[0-9]{10}/")
     * @Assert\Length(
     *      min = 10,
     *      max = 10,
     *     )
     */
    private $telephone;

//    ** DIPLOME **
    /**
     * @var string|null
     * @ORM\Column(type="string", length=255)
     */
    private $diplomename;
    /**
     * @var  File|null
     * @Vich\UploadableField(mapping="property_diplome", fileNameProperty="diplomename")
     * @Assert\NotBlank(message="Veuillez mettre un fichier s'il vous plait.")
     * @Assert\File(
     *     maxSize = "5M",
     *     maxSizeMessage="Le fichier ne doit pas dépasser les 5 Mo.",
     *     mimeTypes = {"application/pdf", "application/x-pdf"},
     *     mimeTypesMessage = "S'il vous plait pouvez-vous mettre un PDF valide ?"
     * )
     */
    private $diplomeFile;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 150,
     *      minMessage = "Votre domaine doit comporter au moins {{ limit }} caractères",
     *      maxMessage = "Votre domaine doit comporter jusqu'à {{ limit }} caractères"
     * )
     */
    private $domaine;

    /**
     * @ORM\Column(type="integer")
     */
    private $prix;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", cascade={"persist", "remove"})
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rdv", mappedBy="coach")
     */
    private $rdvs;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotNull
     */
    private $description_coach;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotNull
     */
    private $description_seance;

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

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): self
    {
        $this->codePostal = $codePostal;

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

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

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

    public function getDomaine(): ?string
    {
        return $this->domaine;
    }

    public function setDomaine(string $domaine): self
    {
        $this->domaine = $domaine;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $User): self
    {
        $this->user = $User;

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
            $rdv->setCoach($this);
        }

        return $this;
    }

    public function removeRdv(Rdv $rdv): self
    {
        if ($this->rdvs->contains($rdv)) {
            $this->rdvs->removeElement($rdv);
            // set the owning side to null (unless already changed)
            if ($rdv->getCoach() === $this) {
                $rdv->setCoach(null);
            }
        }
        return $this;
    }

    //   ** PHOTO **
    /**
     * @return null|string
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }
    /**
     * @param null|string $filename
     * @return Coach
     * @throws \Exception
     */
    public function setFilename(?string $filename): Coach
    {
        $this->filename = $filename;
        return $this;
    }
    /**
     * @return null|File
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }
    /**
     * @param File|null $imageFile
     * @return Coach
     * @throws \Exception
     */
    public function setImageFile(?File $imageFile): Coach
    {
        $this->imageFile = $imageFile;
        if ($this->imageFile instanceof UploadedFile) {
            $this->updated_at = new \DateTime('now');
        }
            return $this;
    }
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }
    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

//   ** DIPLOME **
    /**
     * @return null|string
     */
    public function getDiplomename(): ?string
    {
        return $this->diplomename;
    }
    /**
     * @param null|string $diplomename
     * @return Coach
     * @throws \Exception
     */
    public function setDiplomename(?string $diplomename): Coach
    {
        $this->diplomename = $diplomename;
        return $this;
    }
    /**
     * @return null|File
     */
    public function getDiplomeFile(): ?File
    {
        return $this->diplomeFile;
    }
    /**
     * @param File|null $diplomeFile
     * @return Coach
     * @throws \Exception
     */
    public function setDiplomeFile(?File $diplomeFile): Coach
    {
        $this->diplomeFile = $diplomeFile;
        if ($this->diplomeFile instanceof UploadedFile) {
            $this->updated_at = new \DateTime('now');
        }
        return $this;
    }

    public function getDescriptionCoach(): ?string
    {
        return $this->description_coach;
    }

    public function setDescriptionCoach(string $description_coach): self
    {
        $this->description_coach = $description_coach;

        return $this;
    }

    public function getDescriptionSeance(): ?string
    {
        return $this->description_seance;
    }

    public function setDescriptionSeance(string $description_seance): self
    {
        $this->description_seance = $description_seance;

        return $this;
    }


}
