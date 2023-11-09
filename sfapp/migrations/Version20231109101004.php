<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231109101004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE batiment (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, description VARCHAR(300) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE experimentation (id INT AUTO_INCREMENT NOT NULL, salle_id INT NOT NULL, sa_id INT NOT NULL, datedemande DATETIME NOT NULL, dateinstallation DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_CF9EE405DC304035 (salle_id), UNIQUE INDEX UNIQ_CF9EE40562CAE146 (sa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sa (id INT AUTO_INCREMENT NOT NULL, numero INT NOT NULL, nom VARCHAR(50) NOT NULL, etat VARCHAR(25) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE salle (id INT AUTO_INCREMENT NOT NULL, batiment_id INT NOT NULL, nom VARCHAR(50) NOT NULL, etage INT NOT NULL, numero INT NOT NULL, orientation VARCHAR(10) DEFAULT NULL, nb_fenetres INT DEFAULT NULL, nb_ordis INT DEFAULT NULL, INDEX IDX_4E977E5CD6F6891B (batiment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE experimentation ADD CONSTRAINT FK_CF9EE405DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('ALTER TABLE experimentation ADD CONSTRAINT FK_CF9EE40562CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('ALTER TABLE salle ADD CONSTRAINT FK_4E977E5CD6F6891B FOREIGN KEY (batiment_id) REFERENCES batiment (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE experimentation DROP FOREIGN KEY FK_CF9EE405DC304035');
        $this->addSql('ALTER TABLE experimentation DROP FOREIGN KEY FK_CF9EE40562CAE146');
        $this->addSql('ALTER TABLE salle DROP FOREIGN KEY FK_4E977E5CD6F6891B');
        $this->addSql('DROP TABLE batiment');
        $this->addSql('DROP TABLE experimentation');
        $this->addSql('DROP TABLE sa');
        $this->addSql('DROP TABLE salle');
    }
}
