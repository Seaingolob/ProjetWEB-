<?php
// models/UserModel.php
class UserModel {
    private $connexion;
    
    public function __construct() {
        // Inclure config et se connecter à la BD
        require_once __DIR__ . '/../config/config.php';
        $this->connexion = $connexion; // Supposant que $connexion vient de config.php
    }
    
    public function getUsers($search, $page, $itemsPerPage) {
        $offset = ($page - 1) * $itemsPerPage;
        
        // Compter le nombre total d'utilisateurs
        $sql_count = "SELECT COUNT(id_compte) FROM utilisateur WHERE nom LIKE :search OR prenom LIKE :search";
        $stmt_count = $this->connexion->prepare($sql_count);
        $stmt_count->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $stmt_count->execute();
        $totalItems = $stmt_count->fetchColumn();
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        // Récupérer les utilisateurs
        $sql = "SELECT id_compte, nom, prenom, mail, telephone 
                FROM utilisateur 
                WHERE nom LIKE :search OR prenom LIKE :search 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'users' => $utilisateurs,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages
        ];
    }
    
    public function getUserInfo($id_compte) {
        try {
            // Détecter le type d'utilisateur (étudiant, admin ou pilote)
            $stmt = $this->connexion->prepare("SELECT 
                                CASE 
                                    WHEN EXISTS (SELECT 1 FROM etudiant WHERE id_compte = :id) THEN 'etudiant'
                                    WHEN EXISTS (SELECT 1 FROM admin WHERE id_compte = :id) THEN 'admin'
                                    WHEN EXISTS (SELECT 1 FROM pilote WHERE id_compte = :id) THEN 'pilote'
                                    ELSE 'inconnu'
                                END AS user_type");
            $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
            $stmt->execute();
            $user_type_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_type = $user_type_result['user_type'];

            // Récupérer les informations de base de l'utilisateur
            $stmt = $this->connexion->prepare("SELECT u.id_compte, u.nom, u.prenom, u.mail, u.telephone 
                                FROM utilisateur u 
                                WHERE u.id_compte = :id");
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

            // Informations spécifiques selon le type d'utilisateur
            $specific_info = [];

            if ($user_type === 'etudiant') {
                // Récupérer la wishlist de l'étudiant avec statut de postulation
                $stmt = $this->connexion->prepare("SELECT 
                                                o.id_offre, 
                                                o.titre, 
                                                e.nom as entreprise_nom, 
                                                GROUP_CONCAT(DISTINCT c.nom SEPARATOR ', ') as competences, 
                                                v.nom_ville,
                                                CASE 
                                                    WHEN EXISTS (SELECT 1 FROM postuler p WHERE p.id_compte = :id AND p.id_offre = o.id_offre) THEN 'Postulée'
                                                    ELSE 'Non-postulée'
                                                END AS statut_postulation
                                            FROM souhaiter s 
                                            JOIN offre o ON s.id_offre = o.id_offre 
                                            JOIN entreprise e ON o.id_entreprise = e.id_entreprise 
                                            JOIN adresse a ON e.id_adresse = a.id_adresse 
                                            JOIN ville v ON a.id_ville = v.id_ville 
                                            LEFT JOIN contenir co ON o.id_offre = co.id_offre 
                                            LEFT JOIN competence c ON co.id_competence = c.id_competence 
                                            WHERE s.id_compte = :id 
                                            GROUP BY o.id_offre");
                $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
                $stmt->execute();
                $specific_info['wishlist'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Récupérer les informations sur la promotion de l'étudiant
                $stmt = $this->connexion->prepare("SELECT 
                    p.nom as promotion_nom,
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
                    LIMIT 1");

                $stmt->bindParam(':id', $id_compte, PDO::PARAM_STR);
                $stmt->execute();
                $specific_info['promotion'] = $stmt->fetch(PDO::FETCH_ASSOC);
            } elseif ($user_type === 'pilote') {
                // Récupérer les promotions pilotées
                $stmt = $this->connexion->prepare("SELECT 
                    p.id_promotion,
                    p.nom as promotion_nom, 
                    c.nom_campus,
                    pi.debut,
                    pi.fin
                    FROM piloter pi
                    JOIN promotion p ON pi.id_promotion = p.id_promotion
                    JOIN campus c ON p.id_campus = c.id_campus
                    WHERE pi.id_compte = :id
                    AND (pi.debut <= CURRENT_DATE() AND (pi.fin >= CURRENT_DATE() OR pi.fin IS NULL))
                    ORDER BY pi.debut DESC");
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
            error_log("Erreur lors de la récupération des informations utilisateur: " . $e->getMessage());
            return [
                'user' => null,
                'user_type' => null,
                'specific_info' => []
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