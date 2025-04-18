<?php

class UserModel {
    private $connexion;

    public function __construct() {
        // Ici, on récupère directement l'instance PDO retournée par config.php
        $this->connexion = require __DIR__ . '/../config/config.php';
    }

    public function getUsers($search, $page, $itemsPerPage) {
        $offset = ($page - 1) * $itemsPerPage;
    
        // Compter le nombre total d'utilisateurs
        $sql_count = "SELECT COUNT(id_compte) FROM utilisateur WHERE nom LIKE :search_nom OR prenom LIKE :search_prenom";
        $stmt_count = $this->connexion->prepare($sql_count);
        $stmt_count->bindValue(':search_nom', '%' . $search . '%', PDO::PARAM_STR);
        $stmt_count->bindValue(':search_prenom', '%' . $search . '%', PDO::PARAM_STR);
        $stmt_count->execute();
        $totalItems = $stmt_count->fetchColumn();
        $totalPages = ceil($totalItems / $itemsPerPage);
    
        // Récupérer les utilisateurs
        $sql = "SELECT id_compte, nom, prenom, mail, telephone 
                FROM utilisateur 
                WHERE nom LIKE :search_nom OR prenom LIKE :search_prenom 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindValue(':search_nom', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->bindValue(':search_prenom', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return [
            'users' => $utilisateurs,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages
        ];
    }

    public function get_user_type($userId) {
        // Vérifier si l'utilisateur est un étudiant
        $sql = "SELECT id_compte FROM etudiant WHERE id_compte = :userId LIMIT 1";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return 'etudiant';
        }
        
        // Vérifier si l'utilisateur est un pilote
        $sql = "SELECT id_compte FROM pilote WHERE id_compte = :userId LIMIT 1";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return 'pilote';
        }
        
        // Vérifier si l'utilisateur est un admin
        $sql = "SELECT id_compte FROM admin WHERE id_compte = :userId LIMIT 1";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return 'admin';
        }
        
        // Si aucun type n'est trouvé
        return 'inconnu';
    }
    
    public function getUserInfo($id_compte) {
        try {
            // 1. On récupère l'utilisateur (infos de base)
            $stmt = $this->connexion->prepare(
                "SELECT id_compte, nom, prenom, mail, telephone 
                 FROM utilisateur 
                 WHERE id_compte = :id"
            );
            $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$user) {
                return [
                    'user' => null,
                    'user_type' => null,
                    'specific_info' => []
                ];
            }
            // 2. On détermine le type d'utilisateur
            $user_type = get_user_type($id_compte);
            if ($user_type === 'inconnu') {
                return [
                    'user' => null,
                    'user_type' => null,
                    'specific_info' => []
                ];
            }
            $specific_info = [];
    
            if ($user_type === 'etudiant') {
                // On utilise des alias de paramètres pour la requête complexe
                $stmt = $this->connexion->prepare(
                    "SELECT 
                        o.id_offre, 
                        o.titre, 
                        e.nom AS entreprise_nom, 
                        GROUP_CONCAT(DISTINCT c.nom SEPARATOR ', ') AS competences, 
                        v.nom_ville,
                        CASE 
                            WHEN EXISTS (
                                SELECT 1 FROM postuler p 
                                WHERE p.id_compte = :id_postulant AND p.id_offre = o.id_offre
                            ) THEN 'Postulée'
                            ELSE 'Non-postulée'
                        END AS statut_postulation
                    FROM souhaiter s
                    JOIN offre o ON s.id_offre = o.id_offre
                    JOIN entreprise e ON o.id_entreprise = e.id_entreprise
                    JOIN adresse a ON e.id_adresse = a.id_adresse
                    JOIN ville v ON a.id_ville = v.id_ville
                    LEFT JOIN contenir co ON o.id_offre = co.id_offre
                    LEFT JOIN competence c ON co.id_competence = c.id_competence
                    WHERE s.id_compte = :id_souhait
                    GROUP BY o.id_offre"
                );
                $stmt->bindParam(':id_postulant', $id_compte, PDO::PARAM_STR);
                $stmt->bindParam(':id_souhait', $id_compte, PDO::PARAM_STR);
                $stmt->execute();
                $specific_info['wishlist'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                // Promotion de l'étudiant (actuelle)
                $stmt = $this->connexion->prepare(
                    "SELECT 
                        p.nom AS promotion_nom,
                        p.id_promotion,
                        c.nom_campus, 
                        a.debut, 
                        a.fin
                    FROM appartenir a
                    JOIN promotion p ON a.id_promotion = p.id_promotion
                    JOIN campus c ON p.id_campus = c.id_campus
                    WHERE a.id_compte = :id
                    AND (a.debut <= CURRENT_DATE() AND (a.fin >= CURRENT_DATE() OR a.fin IS NULL))
                    ORDER BY a.debut DESC
                    LIMIT 1"
                );
                $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
                $stmt->execute();
                $specific_info['promotion'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
            } elseif ($user_type === 'pilote') {
                $stmt = $this->connexion->prepare(
                    "SELECT 
                        p.id_promotion,
                        p.nom AS promotion_nom, 
                        c.nom_campus,
                        pi.debut,
                        pi.fin
                    FROM piloter pi
                    JOIN promotion p ON pi.id_promotion = p.id_promotion
                    JOIN campus c ON p.id_campus = c.id_campus
                    WHERE pi.id_compte = :id
                    AND (pi.debut <= CURRENT_DATE() AND (pi.fin >= CURRENT_DATE() OR pi.fin IS NULL))
                    ORDER BY pi.debut DESC"
                );
                $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
                $stmt->execute();
                $specific_info['promotions_pilotees'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
    
            return [
                'user' => $user,
                'user_type' => $user_type,
                'specific_info' => $specific_info
            ];
    
        } catch (PDOException $e) {
            error_log("ERREUR CRITIQUE dans getUserInfo(): " . $e->getMessage());
            return [
                'user' => "erreur",
                'user_type' => "erreur",
                'specific_info' => [
                    'error_message' => $e->getMessage()
                ]
            ];
        }
    }
    
    public function deleteUser($id) {
        try {
            $sql = "DELETE FROM utilisateur WHERE id_compte = :id";
            $stmt = $this->connexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }
}