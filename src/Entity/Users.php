<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ArticleRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass:UsersRepository::class)]
class Users
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $userId = null;


    #[ORM\Column(length:255)]
     private ?string $name = null;


     #[ORM\Column(length:255)]
     private ?string $role = null;


}
