<?php

declare(strict_types=1);

namespace App\Model\Entity;

final class Comment
{
    private int $id;
    private string $pseudo;
    private string $text;
    private int $idPost;
    private int $idUser;
    private string $status;
    private string $CreatedDate;
    private string $ValidationDate;
    private string $SuppresionDate;

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

    public function getPseudo(): string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;
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
    public function getCreatedDate(): string
    {
        return $this->CreatedDate;
    }
    public function setCreatedDate(string $CreatedDate): self
    {
        $this->CreatedDate = $CreatedDate;
        return $this;
    }

    public function getValidationDate(): string
    {
        return $this->ValidationDate;
    }
    public function setValidationDate(string $ValidationDate): self
    {
        $this->ValidationDate = $ValidationDate;
        return $this;
    }

    public function getSuppresionDate(): string
    {
        return $this->SuppresionDate;
    }
    public function setSuppresionDate(string $SuppresionDate): self
    {
        $this->SuppresionDate = $SuppresionDate;
        return $this;
    }
}
