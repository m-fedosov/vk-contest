<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/comment/{id}'
        ),
        new Post(
            uriTemplate: '/comment',
            openapi: new Model\Operation(
                summary: 'Create a new comment',
                description: '**Input parameters:** comment text, parent comment ID (if any), user ID</br>**Output:** ID of created comment.</br></br>*When requesting through an authorised user, user_id and created_at are automatically added*',
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'text' => ['type' => 'string'],
                                    'parent_id' => ['type' => '?int'],
                                    'created_at' => ['type' => '?|\DateTimeImmutable'],
                                    'user_id' => ['type' => '?int']
                                ]
                            ],
                            'example' => [
                                'text' => 'Слышали про Чистые пруды? Я помыл'
                            ]
                        ]
                    ])
                )
            )
        ),
        new Put(
            uriTemplate: '/comment/{id}',
            openapi: new Model\Operation(
                summary: 'Edit an existing comment',
                description: '**Input parameters:** comment ID, comment text, user ID</br>**Output:** ID of created comment.</br></br>*User_id is automatically added*',
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'text' => ['type' => 'string']
                                ]
                            ],
                            'example' => [
                                'text' => 'Продаю гараж. 89772504581'
                            ]
                        ]
                    ])
                )
            )
        ),
        new Delete(
            uriTemplate: '/comment/{id}',
            openapi: new Model\Operation(
                summary: 'Delete a comment',
                description: '**Input parameters:** comment ID</br>**Output:** ID of deleted comment.</br></br>*User_id is automatically added*',
            )
        )
    ]
)]
#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Table(name:'comment')]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $text = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?int $user_id = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $parent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
