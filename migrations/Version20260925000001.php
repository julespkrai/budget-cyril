<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260925000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema: user, categorie, transaction, objectif, patrimoine_valeur';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            pseudo VARCHAR(100) DEFAULT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE categorie (
            id INT AUTO_INCREMENT NOT NULL,
            label VARCHAR(100) NOT NULL,
            groupe VARCHAR(20) NOT NULL,
            montant_prevu NUMERIC(10, 2) DEFAULT NULL,
            ordre INT NOT NULL DEFAULT 0,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE transaction (
            id INT AUTO_INCREMENT NOT NULL,
            categorie_id INT NOT NULL,
            montant NUMERIC(10, 2) NOT NULL,
            description VARCHAR(200) DEFAULT NULL,
            mois VARCHAR(7) NOT NULL,
            date DATE NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_723705D1BCF5E72D (categorie_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE objectif (
            id INT AUTO_INCREMENT NOT NULL,
            label VARCHAR(150) NOT NULL,
            montant_actuel NUMERIC(10, 2) NOT NULL DEFAULT 0,
            montant_cible NUMERIC(10, 2) NOT NULL DEFAULT 0,
            ordre INT NOT NULL DEFAULT 0,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE patrimoine_valeur (
            id INT AUTO_INCREMENT NOT NULL,
            label VARCHAR(100) NOT NULL,
            valeur NUMERIC(12, 2) NOT NULL DEFAULT 0,
            mois VARCHAR(7) NOT NULL,
            ordre INT NOT NULL DEFAULT 0,
            UNIQUE INDEX uniq_patrimoine_label_mois (label, mois),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_transaction_categorie FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_transaction_categorie');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE patrimoine_valeur');
        $this->addSql('DROP TABLE objectif');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE `user`');
    }
}
