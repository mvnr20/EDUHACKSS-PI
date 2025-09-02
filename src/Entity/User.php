<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['phoneNumber'], message: 'This phone number is already taken.')]
#[UniqueEntity(fields: ['username'], message: 'This username is already taken.')]
#[UniqueEntity(fields: ['email'], message: 'This email is already taken.')]
#[UniqueEntity(fields: ['cin'], message: 'This CIN is already taken.')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    // #[Assert\NotBlank(message: "First name cannot be blank")]
    // #[Assert\Regex(
    //     pattern: '/^[A-Za-z _]+$/',
    //     message: "First name must contain only letters, spaces, or underscores"
    // )]
    // #[Assert\Length(
    //     min: 2,
    //     minMessage: 'Your first name must be at least {{ limit }} characters long',
    // )]
    #[ORM\Column(name: "First_name", type: "string", length: 255, nullable: true)]
    private ?string $firstName;

    // #[Assert\NotBlank(message: "Last name cannot be blank")]
    // #[Assert\Regex(
    //     pattern: '/^[A-Z][a-zA-Z]*$/',
    //     message: "Last name must start with an uppercase letter and contain only letters"
    // )]
    #[ORM\Column(name: "Last_name", type: "string", length: 255, nullable: true)]
    private ?string $lastName;

    // #[Assert\NotBlank(message: "Username cannot be blank")]
    #[ORM\Column(name: "Username", type: "string", length: 255, nullable: true)]
    private ?string $username;

    // #[Assert\NotBlank(message: "Email cannot be blank")]
    // #[Assert\Email(message: "Invalid email format")]
    #[ORM\Column(name: "Email", type: "string", length: 255, nullable: true)]
    private ?string $email;

    // #[Assert\NotBlank(message: "Password cannot be blank")]
    // #[Assert\Length(
    //     min: 8,
    //     minMessage: "Password must be at least 8 characters long"
    // )]
    // #[Assert\Regex(
    //     pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
    //     message: "Password must contain at least one uppercase letter, one lowercase letter, one number, and one symbol"
    // )]
    #[ORM\Column(name: "Password", type: "string", length: 255, nullable: true)]
    private ?string $password;

    // #[Assert\NotBlank(message: "Role cannot be blank")]
    // #[Assert\Choice(choices: ['Student', 'Teacher', 'Admin'], message: "Invalid role")]
    #[ORM\Column(name: "Role", type: "string", length: 255, nullable: true)]
    private ?string $role;


    #[Assert\Image(
        mimeTypesMessage: "Please upload a valid image file",
        minWidth: 10,
        maxWidth: 4000,
        minHeight: 10,
        maxHeight: 4000,
        allowPortrait: false,
        allowLandscape: false,
        allowSquare: true,
        maxSize: "5M",
        maxSizeMessage: "The file is too large ({{ size }} {{ suffix }}). Allowed maximum size is {{ limit }} {{ suffix }}.",
        groups: ["registration"]
    )]
    #[ORM\Column(name: "Pic", type: "string", length: 255, nullable: false)]
    private ?string $pic;

    // #[Assert\NotBlank(message: "Levels cannot be blank")]
    // #[Assert\Choice(choices: [1, 2, 3, 4, 5], message: "Invalid level")]
    #[ORM\Column(name: "Levels", type: "integer", nullable: true)]
    private ?int $levels;

    // #[Assert\NotBlank(message: "CIN cannot be blank")]
    // #[Assert\Length(
    //     exactMessage: "CIN must contain exactly {{ limit }} numbers",
    //     min: 8,
    //     max: 8
    // )]
    #[ORM\Column(name: "CIN", type: "integer", nullable: true)]
    private ?int $cin;

    // #[Assert\NotBlank(message: "Phone number cannot be blank")]
    // #[Assert\Length(
    //     exactMessage: "Phone number must contain exactly {{ limit }} numbers",
    //     min: 8,
    //     max: 8
    // )]
    #[ORM\Column(name: "Phone_number", type: "integer", nullable: true)]
    private ?int $phoneNumber;

    #[ORM\Column(name: "infraction_count", type: "integer", nullable: true)]
    private ?int $infractionCount = 0;

    #[ORM\Column(name: "banned", type: "boolean", nullable: true)]
    private ?bool $banned = false;

    #[ORM\Column(name: "ban_date", type: "date", nullable: true)]
    private ?\DateTimeInterface $banDate;

    #[ORM\Column(name: "ban_reason", type: "string", length: 255, nullable: true)]
    private ?string $banReason;

    #[ORM\Column(name: "status", type: "string", length: 255, nullable: true)]
    private ?string $status;

    #[ORM\Column(name: "resetPasswordToken", type: "string", length: 255, nullable: true)]
    private $resetPasswordToken;

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $resetPasswordToken): self
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }


    public function __construct()
    {
        // Set the default value for status property
        $this->status = 'Not accepted';
        // Initialize $role property with a default value
        $this->role = 'ROLE_USER'; // Default role
        $this->password = '';
    }

    public function getUserIdentifier(): string
    {
        return $this->username; // Change to the property you use for user identification
    }


    #[ORM\Column(type: "json")]
    private $roles = [];




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getPic(): ?string
    {
        return $this->pic;
    }

    public function setPic(?string $pic): static
    {
        $this->pic = $pic;

        return $this;
    }

    public function getLevels(): ?int
    {
        return $this->levels;
    }

    public function setLevels(?int $levels): static
    {
        $this->levels = $levels;

        return $this;
    }

    public function getCin(): ?int
    {
        return $this->cin;
    }

    public function setCin(?int $cin): static
    {
        $this->cin = $cin;

        return $this;
    }

    public function getPhoneNumber(): ?int
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?int $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getInfractionCount(): ?int
    {
        return $this->infractionCount;
    }

    public function setInfractionCount(?int $infractionCount): static
    {
        $this->infractionCount = $infractionCount;

        return $this;
    }

    public function isBanned(): ?bool
    {
        return $this->banned;
    }

    public function setBanned(?bool $banned): static
    {
        $this->banned = $banned;

        return $this;
    }

    public function getBanDate(): ?\DateTimeInterface
    {
        return $this->banDate;
    }

    public function setBanDate(?\DateTimeInterface $banDate): static
    {
        $this->banDate = $banDate;

        return $this;
    }

    public function getBanReason(): ?string
    {
        return $this->banReason;
    }

    public function setBanReason(?string $banReason): static
    {
        $this->banReason = $banReason;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER']; // Default role

        // Add custom roles based on the value of the 'role' attribute
        if ($this->role === 'ROLE_STUDENT') {
            $roles[] = 'ROLE_STUDENT';
        } elseif ($this->role === 'ROLE_TEACHER') {
            $roles[] = 'ROLE_TEACHER';
        } elseif ($this->role === 'ROLE_ADMIN') {
            $roles[] = 'ROLE_ADMIN';
        }

        return array_unique($roles);
    }


    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }


    public function isAdmin(): bool
    {
        // Add your logic to determine if the user is an admin
        // For example, you might check if the user has a specific role in your system
        return $this->role === 'admin';
    }



    // Implement the getSalt method
    public function getSalt()
    {
    }

    // Implement the eraseCredentials method
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // For example, if you store a plain-text password, you should clear it out
        // This method is called after the password has been encoded
        // This is not needed in most cases, so you can leave it empty
    }

    public function __toString()
    {
        return $this->firstName;
    }
}
