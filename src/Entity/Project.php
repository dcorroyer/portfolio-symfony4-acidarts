<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Cocur\Slugify\Slugify;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 * @UniqueEntity("title")
 * @Vich\Uploadable()
 */
class Project
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fileName;

    /**
     * @var File|null
     * @Assert\Image(
     *     mimeTypes="image/jpeg"
     * )
     * @Vich\UploadableField(mapping="project_image", fileNameProperty="fileName")
     */
    private $thumbFile;

    /**
     * @Assert\All({
     *     @Assert\Image(mimeTypes="image/jpeg")
     *     })
     */
    private $imageFiles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Picture", mappedBy="project", orphanRemoval=true, cascade={"persist"})
     */
    private $pictures;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->pictures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param  string|null $fileName
     * @return Project
     */
    public function setFileName(?string $fileName): Project
    {
        $this->fileName = $fileName;

        return $this;
    }


    /**
     * @return File|null
     */
    public function getThumbFile(): ?File
    {
        return $this->thumbFile;
    }

    /**
     * @param  File|null $thumbFile
     * @return Project
     * @throws Exception
     */
    public function setThumbFile(?File $thumbFile): Project
    {
        $this->thumbFile = $thumbFile;
        if ($this->thumbFile instanceof UploadedFile) {
            $this->updatedAt = new DateTime('now');
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImageFiles()
    {
        return $this->imageFiles;
    }

    /**
     * @param mixed $imageFiles
     * @return Project
     */
    public function setImageFiles($imageFiles): self
    {
        foreach ($imageFiles as $imageFile) {
            $picture = new Picture();
            $picture->setImageFile($imageFile);
            $this->addPicture($picture);
        }
        $this->imageFiles = $imageFiles;
        return $this;
    }

    /**
     * @return Collection|Picture[]
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures[] = $picture;
            $picture->setProject($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): self
    {
        if ($this->pictures->contains($picture)) {
            $this->pictures->removeElement($picture);
            if ($picture->getProject() === $this) {
                $picture->setProject(null);
            }
        }

        return $this;
    }

    public function getSlug(): string
    {
        return (new Slugify())->slugify($this->title);
    }

}
