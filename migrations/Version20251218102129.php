<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251218102129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, categorie_nom VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE distributeurs (id INT AUTO_INCREMENT NOT NULL, distributeur_nom VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE images (id INT AUTO_INCREMENT NOT NULL, image_path VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE produits (id INT AUTO_INCREMENT NOT NULL, produit_nom VARCHAR(255) NOT NULL, produit_description LONGTEXT NOT NULL, produit_prix DOUBLE PRECISION NOT NULL, produit_slug VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, reference_id INT NOT NULL, categorie_id INT NOT NULL, UNIQUE INDEX UNIQ_BE2DDF8C1645DEA9 (reference_id), INDEX IDX_BE2DDF8CBCF5E72D (categorie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE produits_distributeurs (produits_id INT NOT NULL, distributeurs_id INT NOT NULL, INDEX IDX_3F2086E8CD11A2CF (produits_id), INDEX IDX_3F2086E89CE97DF1 (distributeurs_id), PRIMARY KEY (produits_id, distributeurs_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE produits_images (produits_id INT NOT NULL, images_id INT NOT NULL, INDEX IDX_710EA589CD11A2CF (produits_id), INDEX IDX_710EA589D44F05E5 (images_id), PRIMARY KEY (produits_id, images_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `references` (id INT AUTO_INCREMENT NOT NULL, reference_value VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE produits ADD CONSTRAINT FK_BE2DDF8C1645DEA9 FOREIGN KEY (reference_id) REFERENCES `references` (id)');
        $this->addSql('ALTER TABLE produits ADD CONSTRAINT FK_BE2DDF8CBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE produits_distributeurs ADD CONSTRAINT FK_3F2086E8CD11A2CF FOREIGN KEY (produits_id) REFERENCES produits (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produits_distributeurs ADD CONSTRAINT FK_3F2086E89CE97DF1 FOREIGN KEY (distributeurs_id) REFERENCES distributeurs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produits_images ADD CONSTRAINT FK_710EA589CD11A2CF FOREIGN KEY (produits_id) REFERENCES produits (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produits_images ADD CONSTRAINT FK_710EA589D44F05E5 FOREIGN KEY (images_id) REFERENCES images (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produits DROP FOREIGN KEY FK_BE2DDF8C1645DEA9');
        $this->addSql('ALTER TABLE produits DROP FOREIGN KEY FK_BE2DDF8CBCF5E72D');
        $this->addSql('ALTER TABLE produits_distributeurs DROP FOREIGN KEY FK_3F2086E8CD11A2CF');
        $this->addSql('ALTER TABLE produits_distributeurs DROP FOREIGN KEY FK_3F2086E89CE97DF1');
        $this->addSql('ALTER TABLE produits_images DROP FOREIGN KEY FK_710EA589CD11A2CF');
        $this->addSql('ALTER TABLE produits_images DROP FOREIGN KEY FK_710EA589D44F05E5');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE distributeurs');
        $this->addSql('DROP TABLE images');
        $this->addSql('DROP TABLE produits');
        $this->addSql('DROP TABLE produits_distributeurs');
        $this->addSql('DROP TABLE produits_images');
        $this->addSql('DROP TABLE `references`');
    }
}
