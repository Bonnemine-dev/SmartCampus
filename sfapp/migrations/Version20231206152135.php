<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231206152135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE experimentation DROP INDEX UNIQ_CF9EE40562CAE146, ADD INDEX IDX_CF9EE40562CAE146 (sa_id)');
        $this->addSql('ALTER TABLE experimentation DROP FOREIGN KEY FK_CF9EE405DC304035');
        $this->addSql('DROP INDEX UNIQ_CF9EE405DC304035 ON experimentation');
        $this->addSql('ALTER TABLE experimentation ADD etat INT NOT NULL, CHANGE salle_id salles_id INT NOT NULL');
        $this->addSql('ALTER TABLE experimentation ADD CONSTRAINT FK_CF9EE405B11E4946 FOREIGN KEY (salles_id) REFERENCES salle (id)');
        $this->addSql('CREATE INDEX IDX_CF9EE405B11E4946 ON experimentation (salles_id)');
        $this->addSql('ALTER TABLE sa ADD disponible TINYINT(1) NOT NULL, CHANGE etat etat INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE experimentation DROP INDEX IDX_CF9EE40562CAE146, ADD UNIQUE INDEX UNIQ_CF9EE40562CAE146 (sa_id)');
        $this->addSql('ALTER TABLE experimentation DROP FOREIGN KEY FK_CF9EE405B11E4946');
        $this->addSql('DROP INDEX IDX_CF9EE405B11E4946 ON experimentation');
        $this->addSql('ALTER TABLE experimentation ADD salle_id INT NOT NULL, DROP salles_id, DROP etat');
        $this->addSql('ALTER TABLE experimentation ADD CONSTRAINT FK_CF9EE405DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CF9EE405DC304035 ON experimentation (salle_id)');
        $this->addSql('ALTER TABLE sa DROP disponible, CHANGE etat etat VARCHAR(25) NOT NULL');
    }
}
