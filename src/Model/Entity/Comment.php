<?php

declare(strict_types=1);

namespace App\Model\Entity;

use DateTime;

final class Comment
{
    private int $id;
    private string $text;
    private int $idPost;
    private int $idUser;
    private string $status;
    private DateTime $createdDate;
    private DateTime $validationDate;
    private DateTime $suppresionDate;

    public function __construct(?array $datas = [])
    {
        if (!empty($datas)) {
            $this->hydrate($datas);
        }
    }

    public function hydrate(array $datas): void
    {
        foreach ($datas as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }
    public function getId(): int
    {
        return $this->id;
    }
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getIdPost(): int
    {
        return $this->idPost;
    }

    public function setIdPost(int $idPost): self
    {
        $this->idPost = $idPost;
        return $this;
    }

    public function getIdUser(): int
    {
        return $this->idUser;
    }

    public function setIdUser(int $idUser): self
    {
        $this->idUser = $idUser;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }
    public function getCreatedDate(): DateTime
    {
        return $this->createdDate;
    }
    public function setCreatedDate(DateTime $createdDate): self
    {
        $this->createdDate = $createdDate;
        return $this;
    }

    public function getValidationDate(): DateTime
    {
        return $this->validationDate;
    }
    public function setValidationDate(DateTime $validationDate): self
    {
        $this->validationDate = $validationDate;
        return $this;
    }

    public function getSuppresionDate(): DateTime
    {
        return $this->suppresionDate;
    }
    public function setSuppresionDate(DateTime $suppresionDate): self
    {
        $this->suppresionDate = $suppresionDate;
        return $this;
    }
}
