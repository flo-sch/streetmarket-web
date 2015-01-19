<?php

namespace Fsb\StreetMarket\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Furniture
 *
 * @ORM\Table(name="furnitures")
 * @ORM\Entity(repositoryClass="Fsb\StreetMarket\CoreBundle\Entity\FurnitureRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Furniture
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="removed_at", type="datetime")
     */
    private $removedAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_hidden", type="boolean")
     */
    private $isHidden;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="took_at", type="datetime")
     * @Assert\NotNull()
     */
    private $tookAt;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="decimal")
     * @Assert\NotNull()
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="decimal")
     * @Assert\NotNull()
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="picture_path", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $picturePath;

    /**
     * @Assert\File(maxSize="6291456")
     * @Assert\NotBlank()
     */
    private $picture;

    private $temporaryPath;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Furniture
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Furniture
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set removedAt
     *
     * @param \DateTime $removedAt
     * @return Furniture
     */
    public function setRemovedAt($removedAt)
    {
        $this->removedAt = $removedAt;

        return $this;
    }

    /**
     * Get removedAt
     *
     * @return \DateTime
     */
    public function getRemovedAt()
    {
        return $this->removedAt;
    }

    /**
     * Set tookAt
     *
     * @param \DateTime $tookAt
     * @return Furniture
     */
    public function setTookAt($tookAt)
    {
        $this->tookAt = $tookAt;

        return $this;
    }

    /**
     * Get tookAt
     *
     * @return \DateTime
     */
    public function getTookAt()
    {
        return $this->tookAt;
    }

    /**
     * Set isHidden
     *
     * @param boolean $isHidden
     * @return Furniture
     */
    public function setIsHidden($isHidden)
    {
        $this->isHidden = $isHidden;

        return $this;
    }

    /**
     * Get isHidden
     *
     * @return boolean
     */
    public function getIsHidden()
    {
        return $this->isHidden;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     * @return Furniture
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     * @return Furniture
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Furniture
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set picturePath
     *
     * @param string $picturePath
     * @return Furniture
     */
    public function setPicturePath($picturePath)
    {
        $this->picturePath = $picturePath;

        return $this;
    }

    /**
     * Get picturePath
     *
     * @return string
     */
    public function getPicturePath()
    {
        return $this->picturePath;
    }

    /**
     * Set picture.
     *
     * @param UploadedFile $picture
     */
    public function setPicture(UploadedFile $picture = null)
    {
        $this->picture = $picture;

        // check if we have an old image path
        if (isset($this->picturePath)) {
            // store the old name to delete after the update
            $this->temporaryPath = $this->getPicturePath();
            $this->setPicturePath(null);
        } else {
            $this->setPicturePath('initial');
        }
    }

    /**
     * Get picture.
     *
     * @return UploadedFile
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Get picture absolute path
     *
     * @return string
     */
    public function getAbsolutePath()
    {
        return is_null($this->getPicturePath()) ? null : $this->getUploadRootDir() . '/' . $this->getPicturePath();
    }

    /**
     * Get picture web path
     *
     * @return string
     */
    public function getWebPath()
    {
        return is_null($this->getPicturePath()) ? null : $this->getUploadDir() . '/' . $this->getPicturePath();
    }

    /**
     * Get upload root dir
     *
     * @return string
     */
    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../../web/' . $this->getUploadDir();
    }

    /**
     * Get upload dir
     *
     * @return string
     */
    protected function getUploadDir()
    {
        // when displaying uploaded doc/image in the view.
        return 'uploads/furnitures';
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (!is_null($this->getPicture())) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->setPicturePath($filename . '.' . $this->getPicture()->guessExtension());
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (!is_null($this->getPicture())) {
            // if there is an error when moving the file, an exception will
            // be automatically thrown by move(). This will properly prevent
            // the entity from being persisted to the database on error
            $this->getPicture()->move($this->getUploadRootDir(), $this->getPicturePath());

            // check if we have an old image
            if (isset($this->temporaryPath)) {
                // delete the old image
                unlink($this->getUploadRootDir() . '/' . $this->temporaryPath);

                // clear the temporary image path
                $this->temporaryPath = null;
            }

            $this->picture = null;
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        $picture = $this->getAbsolutePath();

        if ($picture) {
            unlink($picture);
        }
    }
}
