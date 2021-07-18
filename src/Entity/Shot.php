<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;

/**
 * @Entity
 */
class Shot extends Node
{
    /**
     * @ORM\OneToMany(targetEntity=File::class, mappedBy="shot")
     */
    protected $files;

    public function __construct()
    {
        parent::__construct();
        $this->files = new ArrayCollection();
    }

    /**
     * @return Collection|File[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
            $file->setShot($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getShot() === $this) {
                $file->setShot(null);
            }
        }

        return $this;
    }
}
