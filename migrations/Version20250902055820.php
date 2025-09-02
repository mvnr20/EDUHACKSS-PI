<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250902055820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer (id INT AUTO_INCREMENT NOT NULL, question_id INT DEFAULT NULL, predefans VARCHAR(50) NOT NULL, INDEX IDX_DADD4A251E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, categid INT DEFAULT NULL, title VARCHAR(255) NOT NULL, body VARCHAR(255) NOT NULL, author_id INT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, views INT NOT NULL, likes INT NOT NULL, dislikes INT NOT NULL, INDEX IDX_23A0E665748BCCD (categid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bad_words (word_id INT AUTO_INCREMENT NOT NULL, word VARCHAR(255) NOT NULL, PRIMARY KEY(word_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categories (categ_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(categ_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comments (idcom INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, content VARCHAR(255) NOT NULL, likes INT DEFAULT 0 NOT NULL, dislikes INT DEFAULT 0 NOT NULL, INDEX IDX_5F9E962A7294869C (article_id), PRIMARY KEY(idcom)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) NOT NULL, course VARCHAR(30) NOT NULL, Created DATETIME NOT NULL, idTeacher INT DEFAULT NULL, idSubject INT DEFAULT NULL, INDEX IDX_169E6FB9A04FCD27 (idTeacher), INDEX IDX_169E6FB9EB775588 (idSubject), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE form (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) NOT NULL, type VARCHAR(30) NOT NULL, status VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formsubmitted (submission_id INT AUTO_INCREMENT NOT NULL, form_id INT DEFAULT NULL, question_id INT DEFAULT NULL, answer_id INT DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_8DFC2DFE5FF69B7D (form_id), INDEX IDX_8DFC2DFE1E27F6BF (question_id), INDEX IDX_8DFC2DFEAA334807 (answer_id), INDEX IDX_8DFC2DFEA76ED395 (user_id), PRIMARY KEY(submission_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE infraction (user_id INT NOT NULL, Infraction_id INT AUTO_INCREMENT NOT NULL, infraction_date DATE NOT NULL, infraction_type VARCHAR(255) NOT NULL, INDEX IDX_C1A458F5A76ED395 (user_id), PRIMARY KEY(Infraction_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, form_id INT DEFAULT NULL, text VARCHAR(70) NOT NULL, INDEX IDX_B6F7494E5FF69B7D (form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question_evaluation (idquestion INT AUTO_INCREMENT NOT NULL, subject VARCHAR(255) NOT NULL, question VARCHAR(255) NOT NULL, answer VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, option1 VARCHAR(255) NOT NULL, option2 VARCHAR(255) NOT NULL, option3 VARCHAR(255) NOT NULL, quizId INT DEFAULT NULL, INDEX IDX_74F73AFD34A2147A (quizId), PRIMARY KEY(idquestion)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz (idquiz INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, NbQuestion INT NOT NULL, DateCreated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(idquiz)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz_result (id INT AUTO_INCREMENT NOT NULL, userid INT NOT NULL, quizsubmitted INT NOT NULL, score DOUBLE PRECISION NOT NULL, questionnumber VARCHAR(255) NOT NULL, INDEX IDX_FE2E314AEA0251FF (quizsubmitted), INDEX IDX_FE2E314AF132696E (userid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subject (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, First_name VARCHAR(255) DEFAULT NULL, Last_name VARCHAR(255) DEFAULT NULL, Username VARCHAR(255) DEFAULT NULL, Email VARCHAR(255) DEFAULT NULL, Password VARCHAR(255) DEFAULT NULL, Role VARCHAR(255) DEFAULT NULL, Pic VARCHAR(255) NOT NULL, Levels INT DEFAULT NULL, CIN INT DEFAULT NULL, Phone_number INT DEFAULT NULL, infraction_count INT DEFAULT NULL, banned TINYINT(1) DEFAULT NULL, ban_date DATE DEFAULT NULL, ban_reason VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, resetPasswordToken VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (user_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E665748BCCD FOREIGN KEY (categid) REFERENCES categories (categ_id)');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9A04FCD27 FOREIGN KEY (idTeacher) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9EB775588 FOREIGN KEY (idSubject) REFERENCES subject (id)');
        $this->addSql('ALTER TABLE formsubmitted ADD CONSTRAINT FK_8DFC2DFE5FF69B7D FOREIGN KEY (form_id) REFERENCES form (id)');
        $this->addSql('ALTER TABLE formsubmitted ADD CONSTRAINT FK_8DFC2DFE1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE formsubmitted ADD CONSTRAINT FK_8DFC2DFEAA334807 FOREIGN KEY (answer_id) REFERENCES answer (id)');
        $this->addSql('ALTER TABLE formsubmitted ADD CONSTRAINT FK_8DFC2DFEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE infraction ADD CONSTRAINT FK_C1A458F5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E5FF69B7D FOREIGN KEY (form_id) REFERENCES form (id)');
        $this->addSql('ALTER TABLE question_evaluation ADD CONSTRAINT FK_74F73AFD34A2147A FOREIGN KEY (quizId) REFERENCES quiz (idquiz)');
        $this->addSql('ALTER TABLE quiz_result ADD CONSTRAINT FK_FE2E314AEA0251FF FOREIGN KEY (quizsubmitted) REFERENCES quiz (idquiz)');
        $this->addSql('ALTER TABLE quiz_result ADD CONSTRAINT FK_FE2E314AF132696E FOREIGN KEY (userid) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E665748BCCD');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962A7294869C');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9A04FCD27');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9EB775588');
        $this->addSql('ALTER TABLE formsubmitted DROP FOREIGN KEY FK_8DFC2DFE5FF69B7D');
        $this->addSql('ALTER TABLE formsubmitted DROP FOREIGN KEY FK_8DFC2DFE1E27F6BF');
        $this->addSql('ALTER TABLE formsubmitted DROP FOREIGN KEY FK_8DFC2DFEAA334807');
        $this->addSql('ALTER TABLE formsubmitted DROP FOREIGN KEY FK_8DFC2DFEA76ED395');
        $this->addSql('ALTER TABLE infraction DROP FOREIGN KEY FK_C1A458F5A76ED395');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E5FF69B7D');
        $this->addSql('ALTER TABLE question_evaluation DROP FOREIGN KEY FK_74F73AFD34A2147A');
        $this->addSql('ALTER TABLE quiz_result DROP FOREIGN KEY FK_FE2E314AEA0251FF');
        $this->addSql('ALTER TABLE quiz_result DROP FOREIGN KEY FK_FE2E314AF132696E');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE bad_words');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE form');
        $this->addSql('DROP TABLE formsubmitted');
        $this->addSql('DROP TABLE infraction');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE question_evaluation');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE quiz_result');
        $this->addSql('DROP TABLE subject');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
