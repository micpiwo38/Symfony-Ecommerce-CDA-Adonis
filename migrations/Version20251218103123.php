<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251218103123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produits ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE produits ADD CONSTRAINT FK_BE2DDF8C1645DEA9 FOREIGN KEY (reference_id) REFERENCES `references` (id)');
        $this->addSql('ALTER TABLE produits ADD CONSTRAINT FK_BE2DDF8CBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE produits ADD CONSTRAINT FK_BE2DDF8CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_BE2DDF8CA76ED395 ON produits (user_id)');
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
        $this->addSql('ALTER TABLE produits DROP FOREIGN KEY FK_BE2DDF8CA76ED395');
        $this->addSql('DROP INDEX IDX_BE2DDF8CA76ED395 ON produits');
        $this->addSql('ALTER TABLE produits DROP user_id');
        $this->addSql('ALTER TABLE produits_distributeurs DROP FOREIGN KEY FK_3F2086E8CD11A2CF');
        $this->addSql('ALTER TABLE produits_distributeurs DROP FOREIGN KEY FK_3F2086E89CE97DF1');
        $this->addSql('ALTER TABLE produits_images DROP FOREIGN KEY FK_710EA589CD11A2CF');
        $this->addSql('ALTER TABLE produits_images DROP FOREIGN KEY FK_710EA589D44F05E5');
    }
}
