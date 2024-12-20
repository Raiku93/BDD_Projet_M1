<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241217174802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute une fonction hello_world() qui retourne "Hello, World!"';
    }

    public function up(Schema $schema): void
    {
        // Créer une fonction qui retourne 'Hello, World!'
        $this->addSql("
            CREATE FUNCTION hello_world() 
            RETURNS text AS
            \$\$ 
            BEGIN 
                RETURN 'Hello, World!'; =
            END;
            \$\$ LANGUAGE plpgsql;
        ");

    }

    public function down(Schema $schema): void
    {
        // Supprimer la fonction lors de l'annulation de la migration
        $this->addSql("DROP FUNCTION hello_world;");
    }
}
