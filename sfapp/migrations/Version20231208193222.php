<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231208193222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
        CREATE TRIGGER before_delete_sa
        BEFORE DELETE
        ON sa FOR EACH ROW
            UPDATE experimentation
            SET sa_id = NULL
            WHERE sa_id = OLD.id;'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('
        DROP TRIGGER IF EXISTS before_delete_sa;
        ');
    }
}
