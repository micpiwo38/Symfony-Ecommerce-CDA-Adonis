<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251218132216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produits_images DROP FOREIGN KEY `FK_710EA589CD11A2CF`');
        $this->addSql('ALTER TABLE produits_images DROP FOREIGN KEY `FK_710EA589D44F05E5`');
        $this->addSql('DROP TABLE produits_images');
        $this->addSql('ALTER TABLE images ADD produits_id INT NOT NULL');
        $this->addSql('ALTER TABLE images ADD CONSTRAINT FK_E01FBE6ACD11A2CF FOREIGN KEY (produits_id) REFERENCES produits (id)');
        $this->addSql('CREATE INDEX IDX_E01FBE6ACD11A2CF ON images (produits_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE produits_images (produits_id INT NOT NULL, images_id INT NOT NULL, INDEX IDX_710EA589CD11A2CF (produits_id), INDEX IDX_710EA589D44F05E5 (images_id), PRIMARY KEY (produits_id, images_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE produits_images ADD CONSTRAINT `FK_710EA589CD11A2CF` FOREIGN KEY (produits_id) REFERENCES produits (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produits_images ADD CONSTRAINT `FK_710EA589D44F05E5` FOREIGN KEY (images_id) REFERENCES images (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6ACD11A2CF');
        $this->addSql('DROP INDEX IDX_E01FBE6ACD11A2CF ON images');
        $this->addSql('ALTER TABLE images DROP produits_id');
    }
}
