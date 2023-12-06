<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231206080631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE donnees (id INT AUTO_INCREMENT NOT NULL, experimentation_id INT NOT NULL, date DATETIME NOT NULL, temperature DOUBLE PRECISION NOT NULL, humidite INT NOT NULL, tauxcarbone INT NOT NULL, INDEX IDX_7956F5B44D2F8FBF (experimentation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE donnees ADD CONSTRAINT FK_7956F5B44D2F8FBF FOREIGN KEY (experimentation_id) REFERENCES experimentation (id)');
        $this->addSql('ALTER TABLE experimentation ADD etat INT NOT NULL, CHANGE sa_id sa_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sa ADD disponible TINYINT(1) NOT NULL, CHANGE etat etat INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE donnees DROP FOREIGN KEY FK_7956F5B44D2F8FBF');
        $this->addSql('DROP TABLE donnees');
        $this->addSql('ALTER TABLE experimentation DROP etat, CHANGE sa_id sa_id INT NOT NULL');
        $this->addSql('ALTER TABLE sa DROP disponible, CHANGE etat etat VARCHAR(25) NOT NULL');
    }
}
