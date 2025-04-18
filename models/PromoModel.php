<?php
class PromoModel {
    private $connexion;
    public function __construct() {
        $this->connexion = require __DIR__ . '/../config/config.php';
    }

    public function getPromoStats($id_promotion) {
        // Promotion name
        $stmt = $this->connexion->prepare("SELECT nom FROM promotion WHERE id_promotion = :id_promotion");
        $stmt->bindValue(':id_promotion', $id_promotion, PDO::PARAM_INT);
        $stmt->execute();
        $promotion = $stmt->fetch(PDO::FETCH_ASSOC);

        // Number of students
        $stmt = $this->connexion->prepare("SELECT COUNT(*) AS nb_etudiants FROM appartenir WHERE id_promotion = :id_promotion");
        $stmt->bindValue(':id_promotion', $id_promotion, PDO::PARAM_INT);
        $stmt->execute();
        $nb_etudiants = $stmt->fetch(PDO::FETCH_ASSOC)['nb_etudiants'];

        // Pilote
        $stmt = $this->connexion->prepare("SELECT u.nom, u.prenom, u.id_compte FROM utilisateur u 
            INNER JOIN piloter p ON u.id_compte = p.id_compte 
            WHERE p.id_promotion = :id_promotion");
        $stmt->bindValue(':id_promotion', $id_promotion, PDO::PARAM_INT);
        $stmt->execute();
        $pilote = $stmt->fetch(PDO::FETCH_ASSOC);

        // Moyenne postulations
        $stmt = $this->connexion->prepare(
            "SELECT AVG(nb_postulations) AS moyenne_postulations FROM (
                SELECT COUNT(p.id_offre) AS nb_postulations FROM utilisateur u
                INNER JOIN appartenir a ON u.id_compte = a.id_compte
                LEFT JOIN postuler p ON u.id_compte = p.id_compte
                WHERE a.id_promotion = :id_promotion
                GROUP BY u.id_compte
            ) AS subquery"
        );
        $stmt->bindValue(':id_promotion', $id_promotion, PDO::PARAM_INT);
        $stmt->execute();
        $moyenne_postulations = $stmt->fetch(PDO::FETCH_ASSOC)['moyenne_postulations'];
        $moyenne_postulations = $moyenne_postulations ? number_format($moyenne_postulations, 2) : "0";

        // Etudiants sans postulation
        $stmt = $this->connexion->prepare(
            "SELECT u.nom, u.prenom FROM utilisateur u
            INNER JOIN appartenir a ON u.id_compte = a.id_compte
            LEFT JOIN postuler p ON u.id_compte = p.id_compte
            WHERE a.id_promotion = :id_promotion AND p.id_compte IS NULL"
        );
        $stmt->bindValue(':id_promotion', $id_promotion, PDO::PARAM_INT);
        $stmt->execute();
        $etudiants_sans_postulation = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return compact('promotion', 'nb_etudiants', 'pilote', 'moyenne_postulations', 'etudiants_sans_postulation');
    }
}