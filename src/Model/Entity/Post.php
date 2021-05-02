<?php

declare(strict_types=1);

namespace App\Model\Entity;

final class Post
{
    private int $id;
    private string $title;
    private string $text;
    private string $standFirst;
    private int $userId;
    private ?string $file_attached = "";

    public function __construct(?array $datas = [])
    {
        if (!empty($datas)) {
            $this->hydrate($datas);
        }
    }

    public function hydrate(array $datas): void
    {
        foreach ($datas as $key => $value) {
            if ($key === "id") {
                $value = (int)($value);
            }
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
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
    public function getStandFirst(): string
    {
        return $this->standFirst;
    }

    public function setStandFirst(string $standFirst): self
    {
        $this->standFirst = $standFirst;
        return $this;
    }
    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }
    public function getFile_attached(): ?string
    {
        return $this->file_attached;
    }

    public function setFile_attached(string $file): self
    {
        $this->file_attached = $file;
        return $this;
    }
}
